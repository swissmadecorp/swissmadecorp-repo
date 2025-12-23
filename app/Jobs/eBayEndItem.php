<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\EbayController;
use App\Models\EbayListing;
use App\Libs\eBayMain;
use App\Models\Product;

class eBayEndItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $products;
    private $beginTime;
    private $endTime;

        /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($products, $beginTime = null, $endTime = null)
    {
        $this->products = $products;
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
            $products = $this->getItemBySku();

            cache()->forget('ebay_end');
            if (count($products)) {
                $this->endEbayItem($products);
                $result = "Ebay Listings Ended Successfully";
                cache()->put('ebay_end', [$result,'success'], 600);
                \Log::info('Ebay listings ended successfully');
            } else {
                $result = "No Active Ebay Listings Found";
                cache()->put('ebay_end', [$result,'notfound'], 600);
                \Log::info('No active ebay listings found to end');
            }

        } catch (\Throwable $e) {
            \Log::error('Failed to end eBay item: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            cache()->put('ebay_end', ['Failed to end eBay item: ' . $e->getMessage(), 'error'], 600);
            // Optionally you can fail the job manually
            $this->fail($e);
        }
    }

    private function getItemBySku() {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $skuList = '';
        foreach($this->products as $product) {
            $skuList .= "<SKU>$product</SKU>";
        }

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
            <GetSellerListRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                <RequesterCredentials>
                    <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                </RequesterCredentials>
                    <StartTimeFrom>$this->endTime</StartTimeFrom>
                    <StartTimeTo>$this->beginTime</StartTimeTo>
                <Pagination>
                    <EntriesPerPage>100</EntriesPerPage>
                    <PageNumber>1</PageNumber>
                </Pagination>
                <DetailLevel>ReturnAll</DetailLevel>
                <SKUArray>
                    $skuList
                </SKUArray>
        </GetSellerListRequest>";

        // \Log::error($xmlRequest);
        $xml = $ebayMain->sendHeaders($xmlRequest,'GetSellerList');

        $namespaces = $xml->getNamespaces(true);
        $products = [];

        // Loop through items to find the SKU
        foreach ($xml->ItemArray->Item as $item) {
            $sku = (string)$item->SKU;
            $listingStatus = (string)$item->SellingStatus->ListingStatus;

            if ($listingStatus == 'Active') {
                if (in_array($sku, $this->products)) {
                    $itemID = (string)$item->ItemID;
                    $products[] = [$itemID, $sku];
                }
            }
        }

        return $products;
    }

    private function EndListing($reason, $ItemID) {
        $listings = EbayListing::whereIn('product_id',$this->products);
        $ebayListings = $listings->get();

        $ebayMain = new eBayMain;
        $EndingReason = $reason;
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <EndItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                             <RequesterCredentials>
                        <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                        </RequesterCredentials>
                        <EndingReason>$EndingReason</EndingReason>
                        <ItemID>$ItemID</ItemID>
                    </EndItemRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'EndItem');

        if ($response == 'Success')  {
            $listings = EbayListing::where('listitem',$ItemID);

            $item = $listings->first();
            if ($item) {
                Product::find($item->product_id)->update([
                    'p_status' => 0
                ]);
            }
            $listings->delete();
        }

    }

    private function endEbayItem($products)
    {
        // Remove all sold items from eBay.
        $listings = EbayListing::whereIn('product_id',array_column($products, 1));
        $ebayListings = $listings->get();

        if ($ebayListings->count() > 0) {
            $listings->delete();
        }

        foreach ($products as $product) {
            $this->EndListing('NotAvailable',$product[0]);
        }
    }
}
