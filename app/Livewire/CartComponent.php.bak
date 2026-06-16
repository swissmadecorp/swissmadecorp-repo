<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\DiscountRule;
use App\Models\Cart;
use App\Models\Product;
use Carbon\Carbon;

class CartComponent extends Component
{

    public int $countCart = 0;
    public int $productStatus = 2;

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();

        return $discountRule;
    }

    #[On('add-to-cart')]
    public function AddToCart($id,$action="") {
        $product = Product::find($id);

        if ($product) {
            $p_image = $product->images->toArray();
            if (!empty($p_image)) {
                if (file_exists(base_path().'/public/images/thumbs/'.$p_image[0]['location']))
                    $image='images/thumbs/'.$p_image[0]['location'];
                else $image = 'images/no-image.jpg';
            } else $image = 'images/no-image.jpg';

            if ($product->web_price==0) {
                $wp = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
            } else $wp = $product->web_price;

            $wire = $product->p_newprice;

            $discount = $this->discountRule();
            if ($discount) {
                if ($discount->action == 5)
                    $productDiscount=unserialize($discount->product);

                if ($discount && $discount->action == 4) {
                    $wp = ceil($wp - ($wp * ($discount->amount/100)));
                } elseif ($discount->action == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount)) {
                    $wp = ceil($wp - ($wp * ($discount->amount/100)));
                }
            }

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s')); //subDays(3)->addMinutes(15));

            if (session()->has('customer')) {
                $customer = session()->get('customer');

                if (!empty($customer['payment']) && $customer['payment'] == 1) {
                    $temp = $wp;
                    $wp = $wire;
                    $wire = $temp;
                }

            }

            $qty = isset($qty) ? $qty : 1;
            $cartProducts= array(
                'id' => $product->id,
                'cost' => $product->p_price,
                'serial' => $product->p_serial,
                'sku' => $product->id,
                'qty' => $qty,
                'onhand' => $product->p_qty,
                'webprice' => $wp,
                'iswire' => $product->wire_discount,
                'wireprice' => $wire,
                'condition' => Conditions()->get($product->p_condition),
                'slug' => $product->slug,
                'product_name' => $product->title,
                'image' => $image,
                'reserve_time' => $dt,
                'reserve_for' => "Shopping Cart"
            );

            if (Cart::products()) {
                $cart = Cart::insert($cartProducts,$qty);
                if (session()->has('discount')) {
                    $promoCode = session()->get('discount');
                    $this->discount($promoCode['promocode'],$promoCode['original_amount'],$promoCode['action']);
                }
            } else
                $cart = Cart::add($cartProducts);


            //return $dt;
            $product->update([
                'reserve_amount' => $cartProducts['webprice'],
                "reserve_for" => 'Shopping Cart',
                "reserve_date" => $dt,
                "p_status" => '2'
            ]);

            $this->countCart = Cart::count();

            if ($action == "buynow") {
                $this->dispatch('dispatched-message',['msg' =>'buynow', 'id' => $product->id]);
                return redirect()->route('checkout');
            } else
                $this->dispatch('dispatched-message',['msg' =>'createproduct', 'id' => $product->id]);
        }
    }

    #[On('refresh-cart')]
    public function deleteCart() {
        $this->dispatch('dispatched-refresh-cart');
    }

    public function deleteFromCart($productId) {
        $cart = Cart::Remove($productId);

        $product = Product::find($productId);
        if ($product) {
            $product->update([
                'p_status' => 0,
                'reserve_for' => null,
                'reserve_amount' => null,
                'reserve_date' => null,
            ]);
        }

        if (Cookie::has('cookie_cart')) {
            Cookie::queue(Cookie::forget('cookie_cart'));
        }

        if (session()->has('discount')) {
            $promoCode = session()->get('discount');
            $this->discount($promoCode['promocode'],$promoCode['original_amount'],$promoCode['action']);
            if ($cart == 0)
                session()->forget('discount');
        }

        $this->countCart = Cart::count();
        if ($this->countCart == 0)
            session()->forget('customer');

        $this->dispatch('dispatched-message',['msg' =>'deleteproduct', 'id'=>$product->id]); // despatch message to this blade component.
        $this->dispatch('refresh-cart-count');
        $this->dispatch('remove-from-checkout-page',$product->id);
        $this->dispatch('delete-from-cart-message',$product->id); // despatch to ProductDetails component
    }

    public function render()
    {
        return view('livewire.cart',['cartproducts' => Cart::products()]);
    }
}
