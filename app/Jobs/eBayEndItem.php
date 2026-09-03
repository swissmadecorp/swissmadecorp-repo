<?php

namespace App\Jobs;

use App\Libs\eBayMain;
use App\Models\EbayListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use SimpleXMLElement;

class eBayEndItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $products;

    private $beginTime;

    private $endTime;

    public $tries = 3;

    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($products, $beginTime = null, $endTime = null)
    {
        // Call sites use both a single product ID and an array of IDs.
        $this->products = is_array($products) ? array_values($products) : [$products];
        $this->beginTime = $beginTime;
        $this->endTime = $endTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $requestedProductIds = array_values(array_unique(array_map('strval', $this->products)));
            $localListings = EbayListing::whereIn('product_id', $requestedProductIds)
                ->whereNotNull('listitem')
                ->where('listitem', '<>', '')
                ->get(['product_id', 'listitem']);

            $products = $localListings
                ->map(fn ($listing) => [(string) $listing->listitem, (string) $listing->product_id])
                ->all();

            // Older versions of this job deleted the local mapping before eBay
            // confirmed the removal. Recover those listings through the API.
            $mappedProductIds = $localListings->pluck('product_id')->map(fn ($id) => (string) $id)->all();
            $missingProductIds = array_values(array_diff($requestedProductIds, $mappedProductIds));

            if (count($missingProductIds)) {
                $products = array_merge($products, $this->getItemBySku($missingProductIds));
            }

            $products = collect($products)
                ->unique(fn ($product) => $product[0])
                ->values()
                ->all();

            cache()->forget('ebay_end');
            if (count($products)) {
                $this->endEbayItem($products);
                $result = 'Ebay Listings Ended Successfully';
                cache()->put('ebay_end', [$result, 'success'], 600);
                \Log::info('Ebay listings ended successfully');
            } else {
                $result = 'No Active Ebay Listings Found';
                cache()->put('ebay_end', [$result, 'notfound'], 600);
                \Log::info('No active ebay listings found to end');
            }

        } catch (\Throwable $e) {
            \Log::error('Failed to end eBay item: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            // dd('Failed to end eBay item: ' . $e->getMessage());
            cache()->put('ebay_end', ['Failed to end eBay item: '.$e->getMessage(), 'error'], 600);

            // Let Laravel retry the job and eventually record it as failed.
            throw $e;
        }
    }

    private function getItemBySku(array $productIds)
    {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $skuList = '';
        foreach ($productIds as $product) {
            $sku = htmlspecialchars((string) $product, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $skuList .= "<SKU>$sku</SKU>";
        }

        $dateRange = '';
        if ($this->endTime && $this->beginTime) {
            $dateRange = "<StartTimeFrom>{$this->endTime}</StartTimeFrom>
                <StartTimeTo>{$this->beginTime}</StartTimeTo>";
        }

        $products = [];
        $page = 1;
        $totalPages = 1;

        do {
            $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
            <GetSellerListRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                <RequesterCredentials>
                    <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                </RequesterCredentials>
                $dateRange
                <Pagination>
                    <EntriesPerPage>100</EntriesPerPage>
                    <PageNumber>$page</PageNumber>
                </Pagination>
                <DetailLevel>ReturnAll</DetailLevel>
                <SKUArray>
                    $skuList
                </SKUArray>
        </GetSellerListRequest>";

            $response = $ebayMain->sendHeaders($xmlRequest, 'GetSellerList');
            $xml = $this->successfulResponse($response, 'GetSellerList');

            foreach ($xml->ItemArray->Item ?? [] as $item) {
                $sku = (string) $item->SKU;
                $listingStatus = (string) $item->SellingStatus->ListingStatus;

                if ($listingStatus == 'Active' && in_array($sku, $productIds, true)) {
                    $itemID = (string) $item->ItemID;
                    $products[] = [$itemID, $sku];
                }
            }

            $totalPages = max(1, (int) ($xml->PaginationResult->TotalNumberOfPages ?? 1));
            $page++;
        } while ($page <= $totalPages);

        return $products;
    }

    private function endListing($reason, $itemId, $productId)
    {
        $ebayMain = new eBayMain;
        $EndingReason = $reason;
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <EndItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                             <RequesterCredentials>
                        <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                        </RequesterCredentials>
                        <EndingReason>$EndingReason</EndingReason>
                        <ItemID>$itemId</ItemID>
                    </EndItemRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest, 'EndItem');
        $this->successfulResponse($response, 'EndItem', $itemId);

        // Delete the local mapping only after eBay confirms the listing ended.
        EbayListing::where('product_id', $productId)
            ->where('listitem', $itemId)
            ->delete();

    }

    private function endEbayItem($products)
    {
        foreach ($products as $product) {
            $this->endListing('NotAvailable', $product[0], $product[1]);
        }
    }

    private function successfulResponse($response, string $operation, ?string $itemId = null): SimpleXMLElement
    {
        if (is_string($response)) {
            $response = simplexml_load_string($response, SimpleXMLElement::class, LIBXML_NOCDATA);
        }

        if (is_array($response)) {
            throw new RuntimeException($response['ErrorMessage'] ?? "$operation failed.");
        }

        if (! $response instanceof SimpleXMLElement) {
            throw new RuntimeException("$operation returned an invalid response.");
        }

        $ack = (string) $response->Ack;
        if (! in_array($ack, ['Success', 'Warning'], true)) {
            $messages = [];
            foreach ($response->Errors ?? [] as $error) {
                $messages[] = (string) ($error->LongMessage ?: $error->ShortMessage);
            }

            $context = $itemId ? " for eBay item $itemId" : '';
            throw new RuntimeException(
                "$operation failed$context: ".(count($messages) ? implode(' | ', $messages) : "Ack=$ack")
            );
        }

        return $response;
    }
}
