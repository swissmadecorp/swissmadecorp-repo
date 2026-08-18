<?php

namespace App\Jobs;

use App\Libs\eBayMain;
use App\Models\EbayListing;
use App\Models\Product;
use App\Services\EbayPriceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

/**
 * Perform a lightweight eBay price revision without uploading pictures or
 * rebuilding the rest of the listing. Livewire runs this job after sending
 * the HTTP response, avoiding both browser delay and a queue-worker dependency.
 */
class AutomateEbayPriceUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected array $productIds;

    public function __construct($request)
    {
        if ($request instanceof \Illuminate\Http\Request) {
            $request = $request->all();
        }

        if (is_numeric($request)) {
            $productIds = [(int) $request];
        } elseif (is_array($request)) {
            $productIds = $request['ids'] ?? [];

            if (! $productIds && isset($request['product_id'])) {
                $productIds = [$request['product_id']];
            }
        } else {
            $productIds = [];
        }

        $this->productIds = array_values(array_filter(array_map('intval', (array) $productIds)));

        if (! $this->productIds) {
            throw new \InvalidArgumentException('AutomateEbayPriceUpdate requires at least one product ID.');
        }
    }

    public function handle(EbayPriceService $pricing): void
    {
        \Log::info('AutomateEbayPriceUpdate execution started.', [
            'product_ids' => $this->productIds,
            'attempt' => $this->attempts(),
        ]);

        foreach ($this->productIds as $productId) {
            $this->updateProduct($productId, $pricing);
        }
    }

    protected function updateProduct(int $productId, EbayPriceService $pricing): void
    {
        $product = Product::with('categories')->findOrFail($productId);
        $listing = EbayListing::where('product_id', $productId)->first();
        $price = $pricing->listingPrice($product);
        $bestOffer = $pricing->minimumBestOfferPrice($product, $price);

        $ebay = new eBayMain;
        $itemId = $listing?->listitem;

        \Log::info('eBay price update started.', [
            'product_id' => $productId,
            'item_id' => $itemId,
            'price' => $price,
        ]);

        try {
            if (empty($itemId)) {
                $itemId = $this->findActiveItemIdBySku($ebay, $productId);
            }

            if (empty($itemId)) {
                EbayListing::updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'listitem' => null,
                        'status' => 'inactive',
                        'errors' => null,
                    ]
                );

                $product->updateQuietly(['platform' => 0]);

                \Log::warning('No active eBay listing was found; submitting the product for listing instead.', [
                    'product_id' => $productId,
                    'sku' => (string) $productId,
                    'price' => $price,
                ]);

                AutomateEbayPost::dispatch(['ids' => [$productId]]);

                \Log::info('Product submitted for a new eBay listing after no active listing was found.', [
                    'product_id' => $productId,
                    'sku' => (string) $productId,
                ]);

                return;
            }

            try {
                [$xmlResponse, $ack] = $this->revisePrice($ebay, $itemId, $price, $bestOffer);
            } catch (Throwable $exception) {
                // A stored ItemID can become stale after a listing is relisted.
                // Recover the current active ItemID by SKU and retry once.
                $recoveredItemId = $this->findActiveItemIdBySku($ebay, $productId);

                if (empty($recoveredItemId) || (string) $recoveredItemId === (string) $itemId) {
                    throw $exception;
                }

                \Log::warning('Retrying eBay price update with recovered ItemID.', [
                    'product_id' => $productId,
                    'old_item_id' => $itemId,
                    'recovered_item_id' => $recoveredItemId,
                ]);

                $itemId = $recoveredItemId;
                [$xmlResponse, $ack] = $this->revisePrice($ebay, $itemId, $price, $bestOffer);
            }

            $responseItemId = $this->firstXPathValue($xmlResponse, '//e:ItemID');
            $itemId = $responseItemId ?: $itemId;

            EbayListing::updateOrCreate(
                ['product_id' => $productId],
                [
                    'listitem' => $itemId,
                    'listprice' => $price,
                    'status' => 'active',
                    'errors' => null,
                ]
            );

            $product->updateQuietly(['platform' => 1]);

            \Log::info('eBay price update completed.', [
                'product_id' => $productId,
                'item_id' => $itemId,
                'price' => $price,
                'ack' => $ack,
            ]);
        } catch (Throwable $exception) {
            EbayListing::updateOrCreate(
                ['product_id' => $productId],
                ['errors' => $exception->getMessage()]
            );

            \Log::error('eBay price update failed.', [
                'product_id' => $productId,
                'item_id' => $listing?->listitem,
                'price' => $price,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function revisePrice(
        eBayMain $ebay,
        string $itemId,
        string $price,
        string $bestOffer
    ): array {
        $token = $this->xml($ebay->getToken());
        $itemId = $this->xml($itemId);
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials><eBayAuthToken>{$token}</eBayAuthToken></RequesterCredentials>
  <ErrorLanguage>en_US</ErrorLanguage>
  <Item>
    <ItemID>{$itemId}</ItemID>
    <ListingDetails><MinimumBestOfferPrice>{$bestOffer}</MinimumBestOfferPrice></ListingDetails>
    <StartPrice currencyID="USD">{$price}</StartPrice>
  </Item>
  <WarningLevel>High</WarningLevel>
</ReviseFixedPriceItemRequest>
XML;

        $response = $ebay->sendHeaders($xmlRequest, 'ReviseFixedPriceItem');
        $xmlResponse = $this->xmlResponse($response);
        $ack = $this->firstXPathValue($xmlResponse, '//e:Ack');
        $errors = $this->xpathValues($xmlResponse, '//e:Errors/e:LongMessage');

        if (! in_array($ack, ['Success', 'Warning'], true)) {
            $errorMessage = implode(' ', $errors);
            throw new RuntimeException($errorMessage ?: "eBay returned acknowledgement '{$ack}'.");
        }

        return [$xmlResponse, $ack];
    }

    protected function findActiveItemIdBySku(eBayMain $ebay, int $productId): ?string
    {
        $token = $this->xml($ebay->getToken());
        $sku = $this->xml($productId);
        $endTimeFrom = now('UTC')->addSecond()->format('Y-m-d\TH:i:s.000\Z');
        $endTimeTo = now('UTC')->addDays(119)->format('Y-m-d\TH:i:s.000\Z');
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials><eBayAuthToken>{$token}</eBayAuthToken></RequesterCredentials>
  <DetailLevel>ReturnAll</DetailLevel>
  <EndTimeFrom>{$endTimeFrom}</EndTimeFrom>
  <EndTimeTo>{$endTimeTo}</EndTimeTo>
  <Pagination><EntriesPerPage>10</EntriesPerPage><PageNumber>1</PageNumber></Pagination>
  <SKUArray><SKU>{$sku}</SKU></SKUArray>
</GetSellerListRequest>
XML;

        $response = $ebay->sendHeaders($xmlRequest, 'GetSellerList');
        $xmlResponse = $this->xmlResponse($response);
        $ack = $this->firstXPathValue($xmlResponse, '//e:Ack');

        if (! in_array($ack, ['Success', 'Warning'], true)) {
            $errors = $this->xpathValues($xmlResponse, '//e:Errors/e:LongMessage');
            throw new RuntimeException(implode(' ', $errors) ?: 'eBay could not look up the listing by SKU.');
        }

        foreach ($xmlResponse->xpath('//e:Item') ?: [] as $item) {
            $item->registerXPathNamespace('e', 'urn:ebay:apis:eBLBaseComponents');
            $itemSku = $this->firstXPathValue($item, './e:SKU');
            $listingStatus = $this->firstXPathValue($item, './e:SellingStatus/e:ListingStatus');
            $itemId = $this->firstXPathValue($item, './e:ItemID');

            if ($itemSku === (string) $productId && $listingStatus === 'Active' && $itemId !== '') {
                EbayListing::updateOrCreate(
                    ['product_id' => $productId],
                    ['listitem' => $itemId, 'status' => 'active', 'errors' => null]
                );

                \Log::info('Recovered active eBay ItemID by SKU.', [
                    'product_id' => $productId,
                    'item_id' => $itemId,
                ]);

                return $itemId;
            }
        }

        return null;
    }

    protected function xmlResponse($response): SimpleXMLElement
    {
        if ($response instanceof SimpleXMLElement) {
            $xml = $response;
        } elseif (is_string($response)) {
            $xml = simplexml_load_string($response);
        } elseif (is_array($response)) {
            throw new RuntimeException($response['ErrorMessage'] ?? 'eBay rejected the price update.');
        } else {
            $xml = false;
        }

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('eBay returned an invalid response to the price update.');
        }

        $xml->registerXPathNamespace('e', 'urn:ebay:apis:eBLBaseComponents');

        return $xml;
    }

    protected function firstXPathValue(SimpleXMLElement $xml, string $path): string
    {
        return $this->xpathValues($xml, $path)[0] ?? '';
    }

    protected function xpathValues(SimpleXMLElement $xml, string $path): array
    {
        return array_values(array_filter(array_map('strval', $xml->xpath($path) ?: [])));
    }

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('AutomateEbayPriceUpdate job failed.', [
            'product_ids' => $this->productIds,
            'error' => $exception->getMessage(),
        ]);
    }
}
