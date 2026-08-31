<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsCsvExport implements FromQuery, WithCustomCsvSettings, WithHeadings, WithMapping
{
    public function query(): Builder
    {
        return Product::query()
            ->with(['categories', 'images'])
            ->where('p_qty', '>', 0)
            ->where('group_id', 0)
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'stockId',
            'brand',
            'modelNumber',
            'serialNumber',
            'condition',
            'boxIncluded',
            'papersIncluded',
            'notes',
            'wholesalePriceUSD',
            'paperDate',
            'dialType',
            'braceletType',
            'braceletLinkCount',
            'primaryImage',
            'image2',
            'image3',
            'image4',
            'image5',
            'enableOffers',
            'enableOrders',
            'minimumOffer',
            'shippingMethod',
            'paymentMethod',
        ];
    }

    /**
     * @param Product $product
     */
    public function map($product): array
    {
        $images = $product->images
            ->take(5)
            ->map(fn ($image) => $this->imageUrl($image->location))
            ->values()
            ->pad(5, '');

        return [
            $product->id,
            strtoupper(str_replace(' ','_',$product->categories?->category_name)) ?? '',
            $product->p_reference ?? '',
            $product->p_serial ?? '',
            Conditions()->get($product->p_condition, ''),
            $this->booleanValue($product->p_box),
            $this->booleanValue($product->p_papers),
            $product->p_comments ?? '',
            $product->p_newprice - ($product->p_newprice * 0.04) ?? '',
            $product->p_year ?? '',
            DialStyle()->get($product->p_dial_style, ''),
            Strap()->get($product->p_strap, ''),
            $product->bracelet_link_count ?? '',
            ...$images->all(),
            'true',
            'true',
            '',
            'SELLER_PAYS',
            'PREWIRE',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\n",
            'use_bom' => true,
        ];
    }

    private function booleanValue(mixed $value): string
    {
        return (int) $value === 1 ? 'true' : 'false';
    }

    private function imageUrl(?string $location): string
    {
        if (blank($location)) {
            return '';
        }

        return rtrim(config('app.url'), '/').'/images/'.ltrim($location, '/');
    }
}
