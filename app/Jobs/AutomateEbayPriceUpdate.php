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
 * rebuilding the rest of the listing. Livewire sends this job to the normal
 * queue so saving a product does not wait for eBay's API response.
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

        $identifier = $listing && ! empty($listing->listitem)
            ? '<ItemID>'.$this->xml($listing->listitem).'</ItemID>'
            : '<SKU>'.$this->xml($productId).'</SKU><InventoryTrackingMethod>SKU</InventoryTrackingMethod>';

        $ebay = new eBayMain;
        $token = $this->xml($ebay->getToken());
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials><eBayAuthToken>{$token}</eBayAuthToken></RequesterCredentials>
  <ErrorLanguage>en_US</ErrorLanguage>
  <Item>
    {$identifier}
    <ListingDetails><MinimumBestOfferPrice>{$bestOffer}</MinimumBestOfferPrice></ListingDetails>
    <StartPrice currencyID="USD">{$price}</StartPrice>
  </Item>
  <WarningLevel>High</WarningLevel>
</ReviseFixedPriceItemRequest>
XML;

        \Log::info('eBay price update started.', [
            'product_id' => $productId,
            'item_id' => $listing?->listitem,
            'price' => $price,
        ]);

        try {
            $response = $ebay->sendHeaders($xmlRequest, 'ReviseFixedPriceItem');
            $xmlResponse = $this->xmlResponse($response);
            $ack = $this->firstXPathValue($xmlResponse, '//e:Ack');
            $errors = $this->xpathValues($xmlResponse, '//e:Errors/e:LongMessage');
            $errorMessage = implode(' ', $errors);

            if (! in_array($ack, ['Success', 'Warning'], true)) {
                throw new RuntimeException($errorMessage ?: "eBay returned acknowledgement '{$ack}'.");
            }

            $itemId = $this->firstXPathValue($xmlResponse, '//e:ItemID') ?: $listing?->listitem;

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
