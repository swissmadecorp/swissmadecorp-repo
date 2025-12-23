<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\DiscountRule;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;

class ProductDetails extends Component
{
    public $slug;
    public $breadcrumbs = [];
    // public $totalcart;
    public $discount;
    public $product;
    public $custom_columns;
    public $mainImage;
    public $allImages;
    public $productStatus = [];
    public $p_status;

    public $brand = '';
    public $sizes = '';
    public $condition = '';
    public $casesize = '';
    public $model = null;
    public $gender = '';

    #[Url(keep: true)]
    public $search = "";

    protected $queryString = [
        'search' => ['except' => ''],
        'brand' => ['except' => ''],
        'model' => ['except' => ''],
        'condition' => ['except' => ''],
        'sizes' => ['except' => ''],
        'gender' => ['except' => ''],
        'casesize' => ['except' => ''],

    ];

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();

        return $discountRule;
    }

    public function setBread($key) {
        $breadcrumbs = $this->breadcrumbs;

        $totalBreadcrumbs = count($breadcrumbs);
        foreach (array_reverse($breadcrumbs) as $breadcrumb) {
            // return  $request['key'] . ' ' . $key;
            if ($key == $totalBreadcrumbs) {
                break;
            }
            $breadcrumbs[$totalBreadcrumbs] = "";
            $totalBreadcrumbs -= 1;
        }

        $this->breadcrumbs = array_filter($breadcrumbs);

        $breadcrumbs = $this->breadcrumbs;
        if (count($breadcrumbs) == 1)
            $this->brand = $breadcrumbs[0];
        elseif (count($breadcrumbs) == 2) {
            $this->brand = $breadcrumbs[0];
            $this->model = $breadcrumbs[1];
        } elseif (count($breadcrumbs) == 3) {
            $this->brand = $breadcrumbs[0];
            $this->model = $breadcrumbs[1];
            $this->condition = $breadcrumbs[2];
        } elseif (count($breadcrumbs) == 4) {
            $this->brand = $breadcrumbs[0];
            $this->model = $breadcrumbs[1];
            $this->condition = $breadcrumbs[2];
            $this->gender = $breadcrumbs[3];
        }

        return redirect()->route('watch.products',
            [
                'search' => $this->search,
                'brand' => $this->brand,
                'model' => $this->model,
                'condition' => $this->condition,
                'gender' => $this->gender
            ]
        );
    }

    #[On('delete-from-cart-message')]
    public function dispatchRefresh($id) {
        $this->productStatus[$id] = 0;
        // $this->dispatch('refresh-cart-count');
    }

    public function BuyNow($id) {
        $this->dispatch('add-to-cart',$id,'buynow');
        $this->productStatus[$id] = 2;
    }

    public function AddToCart($id) {
        $this->productStatus[$id] = 2;
        $this->dispatch('add-to-cart',$id);
        $this->dispatch('refresh-cart-count');
    }

    public function mount()
    {
        $this->slug = Request::route('slug');

        $custom_columns = getCustomColumns();

        $product = Product::with('images','categories')->where('slug',$this->slug)
            ->whereNotIn('p_status',[4,7,9])
            ->first();

        $this->product = $product;
        if ($product) {
            $this->breadcrumbs = [
                $product->categories->category_name,
                $product->p_model,
                Conditions()->get($product->p_condition),
                $product->p_gender,
                $product->p_casesize,
            ];

            // $this->totalcart = Cart::products();
            $this->discount = $this->discountRule();
            // $this->breadcrumbs = $this->breadcrumbs;

            $imageMain=$product->images()->first();
            if ($imageMain) {
                $this->mainImage = "/images/$imageMain->location";
                $this->allImage = $product->images->pluck('location')->map(fn($location) => '/images/' . $location);
            } else {
                $this->mainImage = 'images/no-image.jpg';;
            }
        }

        // You can also load product details based on the slug here
        // $this->product = Product::where('slug', $slug)->first();
    }

    public function setMainImage($imagePath) {
        $this->mainImage = "/images/$imagePath";
    }

    public function render() {
        if ($this->product)
            $this->p_status = array_key_exists($this->product->id, $this->productStatus) ? $this->productStatus[$this->product->id] : 0;

        return view('livewire.product-details', ['breadcrumbs' => $this->breadcrumbs]);
    }
}
