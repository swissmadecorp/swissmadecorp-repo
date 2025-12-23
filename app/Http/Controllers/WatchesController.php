<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\DiscountRule;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use AmazonPay\Client;
use HosseinHezami\LaravelGemini\Facades\Gemini;
use Carbon\Carbon;
use DB;

class WatchesController extends Controller
{
    private $title = "Brand new, pre-owned, luxury, casual, and dress watches for men and women";

    public function __invoke($page) {
        return view($page,['sidebar'=>'false','fullpage'=>true]);
    }

    public function test2() {
        // Simple prompt
        // $response = Gemini::text()
        //     ->prompt('Write a tagline for a developer blog.')
        //     ->generate();

        // echo $response->content();

        // Chat with history
        // $history = [
        //     ['role' => 'user', 'parts' => [['text' => 'Hello!']]],
        //     ['role' => 'model', 'parts' => [['text' => 'Hi there! How can I help?']]]
        // ];

        // $response = Gemini::text()
        //     ->prompt('What’s the weather like in my area by zip code 10036, in Manhattan?')
        //     ->model('gemini-2.5-flash') // Use a fast model for streaming
        //     ->history($history)
        //     ->temperature(0.7)
        //     ->generate();

        // dd( $response->content());

        return view('test2');
    }

    private function loadFilteredProducts(Request $request, $id='',$name='',$models='') {
        if ($id == 'watches') $id=$name;

        // if (count($request->all())==0) {
        //     $products = Product::with('categories')->where('p_qty','>',0)->paginate(20);
        //     $name = 'All Brands';
        // }

        $products = $this->filter($id,$request,$models);

        // if (!$products->isEmpty())
        //     $name=$products->first()->categories->category_name;

        if ($id=="watches")
            $name='';
        elseif ($id)
            if (is_numeric($id) && $id > 0) {
                $category = Category::find($id);
                if ($category)
                    $name = $category->category_name;
                else $name = "";
            } else
                return abort(404, 'Unauthorized action.');

        return ['products'=>$products,'name'=>$name];
    }

    protected function loadColors() {
        $colors = DB::table('products')->selectRaw("id,count(p_color) c_amount,p_color")
        ->where('p_qty','>',0)
        ->groupBy('p_color')
        ->get();

        return $colors;
    }

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = \App\Models\DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();

        return $discountRule;
    }

    private function discountRule2() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = \App\Models\DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->get();

        return $discountRule;
    }

    private function discountProducts() {
        $rules = $this->discountRule2();
        $products = array();

        foreach ($rules as $rule) {
            if (unserialize($rule->product)) {
                foreach (unserialize($rule->product) as $product) {
                    $products[] = array('item'=>$product,'action'=>$rule->action,'amount'=>$rule->amount);
                }
            }
        }

        return $products;
    }

    public function AmazonLogin(Request $request) {
        return $request;

    }

    public function homepage (Request $request) {


        $title = $this->title;
        //\Log::info('Product Details: '.getClientIP());
        return view('homepage', ['currentPage'=> "Home Page",'title' => $title,'totalcart' => Cart::products()]);

    }

    public function iphone() {
        return view('iphoneapp', ['currentPage' => "iPhone"]);
    }

    public function newarrival (Request $request, $id='',$name='',$status='') {

        // Get the Authorization response from the charge method
        // $response = $client->charge($requestParameters);

        // dd($response);

        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            //$query->where('p_status','<>',7);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();


        $now = (date('Y-m-d',strtotime(now())));
        $then = (date('Y-m-d',strtotime("-4 days")));

        $products = Product::where('p_status','<>',4)
            ->where('p_status','<>',7)
            ->where('p_qty','>',0)
            ->where('created_at','<=',$now)
            ->where('created_at','>=',$then)
            ->orderBy('updated_at','desc')
            ->latest()->paginate(20);


        $paths = explode('/',url()->current());
        $routes = array();
        foreach ($paths as $path) {
            if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                $routes[] = $path;
        }

        $title = $this->title;

        //$meta_description = '';
        $discount = $this->discountProducts();
        $lpath = '';
        if ($path=='withmarkups') $lpath = 'withmarkups';

        if ($request['status']=='ajax') {
            return view('pagination_child', compact('products','discount'))->render();
        }else{
            return view('newarrival',
                [
                    'showcategories' => false,
                    'categories' => $categories,
                    'products' => $products,
                    'currentPage'=>$name,
                    'routes'=>$routes,
                    'title' => $title,
                    'lpath'=>$lpath,
                    'totalcart' => Cart::products(),
                    'discount' => $discount

                    //'meta' => $meta_description
                ]
            );
        }

    }

    public function chrono24page (Request $request, $id='',$name='',$status='') {

        $filtered = $this->loadFilteredProducts($request, $id, $name);

        $paths = explode('/',url()->current());
        $routes = array();
        foreach ($paths as $path) {
            if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                $routes[] = $path;
        }

        if (count($routes)>2)
            $title = "Brand new, pre-owned, luxury, causal, and dress watches for men and women";
        else $title = 'Brand new and pre-owned '.$filtered['name'] .' Watches';

        //$meta_description = '';
        $lpath = '';
        if ($path=='withmarkups') $lpath = 'withmarkups';

        return view('products.product-chrono24',
            [
                'products' => $filtered['products'],
                'currentPage'=>$name,
                'routes'=>$routes,
                'title' => $title,
                'lpath'=>$lpath,
                'totalcart' => Cart::products()

                //'meta' => $meta_description
            ]
        );

    }

    public function search(Request $request) {
        $term = str_replace('=','',$request['p']);
        $discount = $this->discountProducts();

        if (strpos($term,'sale')!==false) {
            if ($discount) {
                $products = Product::whereIn('id',array_column($discount,'item'))
                    ->whereIn('p_status',[0,1,2,3,5])
                    ->where('p_qty','>',0)
                    ->orderBy('created_at','desc')
                    ->paginate(20);
            } else
                $products = Product::where('p_qty','>',1)
                    ->whereIn('p_status',[0,1,2,3,5])
                    ->orderBy('created_at','desc')->paginate(20);
        } elseif (strpos($term,'new') !==false) {
            $searches = explode(' ',$term);
            foreach ($searches as $search) {
                $criteria[] = "keyword_build like '%$search%'";
            }

            $terms = implode(" AND ", $criteria);
            $products = Product::whereIn('p_status',[0,1,2,3,5])
                ->where('p_qty','>',0)
                ->whereRaw($terms)
                ->orderBy('created_at','desc')
                ->paginate(20);

        } else {
            if ($term) {

                $words = explode(' ', $term);
                $searchTerm = "";
                $searchWords = "";

                $columns = ['title','id','p_model'];

                if ($term) {
                    $searchWords = "(";
                    foreach($words as $word) {
                        foreach ($columns as $key => $column) {
                            $searchWords .= $column.' LIKE "%'.$word .'%" OR ';
                        }

                        $searchWords = substr($searchWords,0,-4) . ") AND (";
                        $searchTerm .= $searchWords;
                        $searchWords = "";
                    }
                }

                $searchTerm = substr($searchTerm,0,-6);

                $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                    $query->whereRaw($searchTerm);
                })
                ->where('p_qty', ">", 0)
                ->whereIn('p_status',[0,1,2,3,5])
                ->orderBy('created_at','desc')
                ->paginate(20);



            } else $products = Product::where('p_qty','>',0)
                ->whereIn('p_status',[0,1,2,3,5])
                ->orderBy('created_at','desc')
                ->paginate(20);

        }

        //\Log::info('Search: '. ' ' . print_r($term,true)  . ': '.getClientIP());
        $casesizes = Product::select('p_casesize')->where('p_qty','>',0)->orderBy('p_casesize','asc')->groupBy('p_casesize')->get();

        if ($request->ajax()) {
            return view('pagination_child', compact('products','discount','casesizes'))->render();
        } else {
            $categories = Category::whereHas('products',function($query) {
                $query->where('p_qty','>',0);
                $query->whereIn('p_status',array(0,1,2,5));
            })->orderBy('category_name')->get();

            //$build="(MATCH(keyword_build) AGAINST('+". str_replace(' ',' +',$term) ."' IN BOOLEAN MODE) or p_reference LIKE '%".$term."%' or p_model LIKE '%".$term."%')";

            $paths = explode('/',url()->current());
            foreach ($paths as $path) {
                if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                    $routes[] = $path;
            }

            if (count($routes)>2)
                $title = $this->title;
            else $title = 'Brand new and pre-owned Watches';


            return view('search',[
                'products' => $products,
                'currentPage'=>'Results for: '.$term,
                'categories' => $categories,
                'routes'=>$routes,
                'discount' => $discount,
                'casesizes' => $casesizes
                ]);

        }
    }

    public function CategoryFilter(Request $request) {
        $catId = $request['catId'];

        $products = $this->filter($catId,$request);
        $discount = $this->discountRule();
        return view('pagination_child', compact('products','discount'))->render();
    }

    public function charge() {
        $ch = curl_init('https://apitest.authorize.net/xml/v1/request.api');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $data = curl_exec($ch);
        curl_close($ch);
    }

    public function charge1(Request $request) {
        $seed = "abcdefghijklmnop";
        $apikey = config('usaepay.key');;
        $apipin = config('usaepay.pin');
        $prehash = $apikey . $seed . $apipin;
        $apihash = 's2/'. $seed . '/' . hash('sha256', $prehash);
        $authKey = base64_encode($apikey . ':' . $apihash);

        $headers = array(
            'Content-Type:application/json',
            'Authorization: Basic ' . $authKey
        );

        // //Do the POST
        $post = array(
            "command" => "cc:sale",
            "payment_key" => $request['payment_key'],
            "amount" => "60.48",
            "amount_detail" => array(
                "tax" => "5.49",
                "shipping" => "5.00",
                "subtotal" => "54.49"
            ),
            "billing_address" => array(
                "firstname" => "Edward",
                "lastname" => "Kurayev",
                "street" => "15 W 47th Street",
                "street2" => "Ste 503",
                "city" => "New York",
                "state" => "NY",
                "postalcode" => "10036",
                "country" => "USA",
                "phone" => "9176569494",
                "email" => "watch613@gmail.com"
            ),
        "custemailaddr" => "watch613@gmail.com"
        );

        $ch = curl_init();
        if(!is_resource($ch))
        {
            return "Libary Error: Unable to initialize CURL ($ch)";
        }

        curl_setopt($ch, CURLOPT_URL,'https://usaepay.com/api/v2/transactions');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch,CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function sellyourwatch() {
        return view('lvsellwatch');
    }

    public function test() {
        $now = (date('Y-m-d',strtotime(now())));
        $then = (date('Y-m-d',strtotime("-14 days")));

        $products = Product::where('p_status','<>',4)
            ->where('p_status','<>',7)
            ->where('p_qty','>',0)
            ->where('created_at','<=',$now)
            ->where('created_at','>=',$then)
            ->orderBy('updated_at','desc')
            ->latest()->take(4)->get();

        $discount = $this->discountRule();

        return view('home',['products' => $products,'discount'=>$discount]);
    }

    public function newarrivals() {
        return view('new-arrival');
    }

    // loads watch-products blade
    public function products(Request $request, $catId='',$name='',$models='') {

        $discount = $this->discountRule();
        $categoryimage=null;
        $categoryimageHTML = null;

        //$criteria = $request['filter'];
        $models = null;

        $criteria = $request['filter'];
        if ($catId) {
            $products = Product::with('categories')
                ->where('p_status','<>',4)
                ->where('p_status','<>',7)
                ->where('p_qty','>',0)
                ->where('category_id',$catId)
                ->orderBy('updated_at','desc');


                // whereHas("categories",function($query) use ($catId) {
                //     $query->where('id',$catId);
                // })->
            $models = Product::select('p_model','categories.category_name','category_id')
                ->join('categories','categories.id','=','products.category_id')
                ->where('category_id',$catId)
                ->where('p_qty','>',0)
                ->groupBy('p_model')
                ->orderBy('p_model','asc')
                ->get();


            if (strpos($request['cat'],"/") !== false) {
                $model = explode("/",$request['cat']);
                $products = $products->where('slug',"LIKE","%".$model[1]."%");
            }

            $categoryimage=Category::find($catId);

            if ($categoryimage && $categoryimage->image_name) {
                ob_start();
                ?>

                    <div class="gap-8 items-center md:flex p-2">
                        <div class="flex items-center justify-center">
                            <img class="w-96" src="/images/categories/<?= $categoryimage->image_name ?>" />
                        </div>
                        <div class="w-full">
                            <h2 class="text-2xl"><?= strtoupper($categoryimage->category_title) ?></h2>
                            <p class="pt-2 pb-2"><?= $products->count() ?> MATCHES FOUND</p>
                            <div class="description"><?= $categoryimage->category_description ?></div>
                        </div>
                    </div>


                <?php
                $categoryimageHTML = ob_get_clean();
            }

            $products = $products->latest()->paginate(20);

        } else {
            if ($criteria)
                $products = Product::where('p_qty','>',0)->orderBy('created_at','desc')->search($criteria)->paginate(20);
            else {
                $filtered = $this->loadFilteredProducts($request, $catId, $name);
                $products = $filtered['products'];
            }
        }

        return view('watches',
            [
                'models' => $models,
                'categoryimage' => $categoryimage,
                'products' => $products,
                'currentPage'=>$name,
                'categoryimageHTML' =>$categoryimageHTML,
                'discount' => $discount,

                //'meta' => $meta_description
            ]
        );
    }

    public function wproducts(Request $request, $catId='',$name='',$models='') {

        $discount = $this->discountRule();
        $categoryimage=null;
        $categoryimageHTML = null;

        //$criteria = $request['filter'];
        $models = null;

        $criteria = $request['filter'];
        if ($catId) {
            $products = Product::with('categories')
                ->where('p_status','<>',4)
                ->where('p_status','<>',7)
                ->where('p_qty','>',0)
                ->where('category_id',$catId)
                ->orderBy('updated_at','desc');


                // whereHas("categories",function($query) use ($catId) {
                //     $query->where('id',$catId);
                // })->
            $models = Product::select('p_model','categories.category_name','category_id')
                ->join('categories','categories.id','=','products.category_id')
                ->where('category_id',$catId)
                ->where('p_qty','>',0)
                ->groupBy('p_model')
                ->orderBy('p_model','asc')
                ->get();


            if (strpos($request['cat'],"/") !== false) {
                $model = explode("/",$request['cat']);
                $products = $products->where('slug',"LIKE","%".$model[1]."%");
            }

            $categoryimage=Category::find($catId);

            if ($categoryimage && $categoryimage->image_name) {
                ob_start();
                ?>

                    <div class="gap-8 items-center md:flex p-2">
                        <div class="flex items-center justify-center">
                            <img class="w-96" src="/images/categories/<?= $categoryimage->image_name ?>" />
                        </div>
                        <div class="w-full">
                            <h2 class="text-2xl"><?= strtoupper($categoryimage->category_title) ?></h2>
                            <p class="pt-2 pb-2"><?= $products->count() ?> MATCHES FOUND</p>
                            <div class="description"><?= $categoryimage->category_description ?></div>
                        </div>
                    </div>


                <?php
                $categoryimageHTML = ob_get_clean();
            }

            $products = $products->latest()->paginate(20);

        } else {
            if ($criteria)
                $products = Product::where('p_qty','>',0)->orderBy('created_at','desc')->search($criteria)->paginate(20);
            else {
                $filtered = $this->loadFilteredProducts($request, $catId, $name);
                $products = $filtered['products'];
            }
        }

        return view('watches-test',
            [
                'models' => $models,
                'categoryimage' => $categoryimage,
                'products' => $products,
                'currentPage'=>$name,
                'categoryimageHTML' =>$categoryimageHTML,
                'discount' => $discount,

                //'meta' => $meta_description
            ]
        );
    }

    public function applepay() {
        // $seed = "abcdefghijklmnop";
        // $apikey = "_TRj9Izy00iVk8PW3b56J2j8a0W891BX";
        // $apipin = "1234";
        // $prehash = $apikey . $seed . $apipin;
        // $apihash = 's2/'. $seed . '/' . hash('sha256', $prehash);
        // $authKey = base64_encode($apikey . ':' . $apihash);

        // $headers = array(
        //     'Content-Type:application/json',
        //     'Authorization: Basic ' . $authKey
        // );


        // // //Do the POST
        // $ch = curl_init('https://sandbox.usaepay.com/api/v2/publickey');
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // $response = curl_exec($ch);
        // curl_close($ch);

        // dd($response);


        return view('test1');
    }

    public function testshow (Request $request, $id='',$name='',$status='') {

        $filtered = $this->loadFilteredProducts($request, $id, $name);
        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();

        $paths = explode('/',url()->current());
        $routes = array();
        foreach ($paths as $path) {
            if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                $routes[] = $path;
        }

        if (count($routes)>2)
            $title = $this->title;
        else $title = 'Brand new and pre-owned '.$filtered['name'] .' Watches';

        //$meta_description = '';
        $lpath = '';
        if ($routes[0]=='chrono24') $lpath = 'withmarkups';

        $products = $filtered['products'];
        $discount = $this->discountRule();

        if ($request['status']=='ajax') {
            return view('pagination_child', compact('products','discount'))->render();
        }else{
            return view('products.product-ajax',
                [
                    'categories' => $categories,
                    'products' => $products,
                    'currentPage'=>$name,
                    'routes'=>$routes,
                    'title' => $title,
                    'lpath'=>$lpath,
                    'totalcart' => Cart::products(),
                    'discount' => $discount,

                    //'meta' => $meta_description
                ]
            );
        }
    }

    public function show (Request $request, $id='',$name='',$models='') {

        $casesizes = Product::select('p_casesize')->where('p_qty','>',0)->orderBy('p_casesize','asc')->groupBy('p_casesize')->get();
        $discount = $this->discountRule();
        $categoryimage=null;
        $categoryimageHTML = null;

        if ($request->ajax()) {
            $criteria = $request['filter'];
            $models = "";

            if (!empty($request['catId'])) {
                $catId = $request['catId'];
                $products = Product::with('categories')
                    ->where('p_status','<>',4)
                    ->where('p_status','<>',7)
                    ->where('p_qty','>',0)
                    ->where('category_id',$catId)
                    ->orderBy('updated_at','desc');


                    // whereHas("categories",function($query) use ($catId) {
                    //     $query->where('id',$catId);
                    // })->
                $productModels = Product::select('p_model','categories.category_name','category_id')
                    ->join('categories','categories.id','=','products.category_id')
                    ->where('category_id',$catId)
                    ->where('p_qty','>',0)
                    ->groupBy('p_model')
                    ->orderBy('p_model','asc')
                    ->get();


                if (strpos($request['cat'],"/") !== false) {
                    $model = explode("/",$request['cat']);
                    $products = $products->where('slug',"LIKE","%".$model[1]."%");
                }

                $categoryimage=Category::find($catId);

                if ($categoryimage && $categoryimage->image_name) {
                    ob_start();
                    ?>

                        <div class="row">
                            <div class="col-md-4 photo">
                                <img src="/images/categories/<?= $categoryimage->image_name ?>" />
                            </div>
                            <div class="col-md-8">
                                <h2><?= strtoupper($categoryimage->category_title) ?></h2>
                                <p><?= $products->count() ?> MATCHES FOUND</p>
                                <div class="description"><?= $categoryimage->category_description ?></div>
                            </div>
                        </div>


                    <?php
                    $categoryimageHTML = ob_get_clean();
                }

                $products = $products->latest()->paginate(20);

                if ($productModels) {
                    ob_start();
                    ?>
                    <h5>Models</h5>
                    <!-- <button>Show more models</button> -->
                    <div id="models">
                        <div class="ais-RefinementList">
                            <ul class="ais-RefinementList-list">
                                <?php foreach ($productModels as $p_model) { ?>
                                    <?php $cat = "/watches/$catId/".str_replace('/','-',strtolower($p_model->category_name)) ?>
                                    <?php if (strpos($cat,' ') !==false) {
                                        $cat = str_replace(' ','-',$cat);
                                    } ?>

                                        <li class="ais-RefinementList-item">
                                            <a href="<?php echo $cat . '/'.strtolower(str_replace(' ','-',$p_model->p_model)) ?>" class="level-top">
                                                <span><?= $p_model->p_model ?></span>
                                            </a>
                                            <hr>
                                        </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <?php
                    $models = ob_get_clean();
                }

            } else {
                if ($criteria)
                    $products = Product::where('p_qty','>',0)->orderBy('created_at','desc')->search($criteria)->paginate(20);
                else {
                    $filtered = $this->loadFilteredProducts($request, $id, $name);
                    $products = $filtered['products'];
                }
            }
            $view = view('pagination_child', compact('products','discount','casesizes','models'))->render();
            return [$view,$models,$categoryimageHTML];
        } else {
            $paths = explode('/',url()->current());
            $routes = array();
            foreach ($paths as $path) {
                if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && $path!='http:' && !is_numeric($path))
                    $routes[] = $path;
            }

            $filtered = $this->loadFilteredProducts($request, $id, $name,$models);
            $categories = Category::whereHas('products',function($query) {
                $query->where('p_qty','>',0);
                $query->whereIn('p_status',array(0,1,2,5));
            })->orderBy('category_name')->get();

            $models = null;
            if (count($routes)>1) {

                $title = "Brand new, pre-owned, luxury, casual, and dress watches for men and women";
                if ($routes) {
                    $models = Product::select('p_model','categories.category_name','category_id')
                    ->join('categories','categories.id','=','products.category_id')
                    ->where('category_id',$id)
                    ->where('p_qty','>',0)
                    ->groupBy('p_model')
                    ->orderBy('p_model','asc')
                    ->get();

                }

            } else $title = 'Brand new and pre-owned '.$filtered['name'] .' Watches';

            //$meta_description = '';
            $lpath = '';
            if ($routes[0]=='chrono24') $lpath = 'withmarkups';

            $products = $filtered['products'];
            $categoryimage=Category::find($id);

            if ($request->ajax()) {
                return view('pagination_child', compact('products','discount'))->render();
            }else{
                return view('products.product-ajax',
                    [
                        'casesizes' => $casesizes,
                        'categories' => $categories,
                        'models' => $models,
                        'categoryimage' => $categoryimage,
                        'products' => $products,
                        'currentPage'=>$name,
                        'routes'=>$routes,
                        'title' => $title,
                        'lpath'=>$lpath,
                        'totalcart' => Cart::products(),
                        'discount' => $discount,

                        //'meta' => $meta_description
                    ]
                );
            }
        }
    }

    public function show1 (Request $request, $id='',$name='',$status='') {

        $filtered = $this->loadFilteredProducts($request, $id, $name);
        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();

        $paths = explode('/',url()->current());
        $routes = array();
        foreach ($paths as $path) {
            if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                $routes[] = $path;
        }

        if (count($routes)>2)
            $title = $this->title;
        else $title = 'Brand new and pre-owned '.$filtered['name'] .' Watches';

        //$meta_description = '';
        $lpath = '';
        if ($routes[0]=='chrono24') $lpath = 'withmarkups';

        $products = $filtered['products'];
        $discount = $this->discountRule();

        //if ($lpath == 'withmarkups') {
        //    return view('pagination_child', compact('products','discount'))->render();
        //}else{
            return view('products.product',
                [
                    'categories' => $categories,
                    'products' => $products,
                    'currentPage'=>$name,
                    'routes'=>$routes,
                    'title' => $title,
                    'lpath'=>$lpath,
                    'totalcart' => Cart::products(),
                    'discount' => $discount,

                    //'meta' => $meta_description
                ]
            );
        //}

    }

    // public function welcome (Request $request, $id='',$name='',$status='') {
    //     $categories = Category::all();
    //     $filtered = $this->loadFilteredProducts($request, $id,$name);

    //     if ($id == 'watches') {
    //         $category = Category::find($name);
    //         $name = $category->category_name;

    //         return view('products.product', ['products' => $filtered['products'],'currentPage'=>$name,'categories' => $categories]);
    //     }

    //     return view('welcome', ['products' => $filtered['products'],'currentPage'=>'All Brands','categories' => $categories]);

    // }

    private function RelatedProducts($id) {
        return \App\Models\RelatedProducts::with(array('product' => function($query) use($id) {
            $query->addSelect('id','title','slug','p_newprice');
            $query->where('p_qty','>','0');
        }))->where('parent_id',$id)->get();
    }

    public function breadcrumbs(Request $request) {
        $breadcrumbs = explode(',',html_entity_decode($request['breadcrumbs']));
        $totalBreadcrumbs = count($breadcrumbs);
        foreach (array_reverse($breadcrumbs) as $breadcrumb) {
            // return  $request['key'] . ' ' . $key;
            if ($request['key'] == $totalBreadcrumbs) {
                break;
            }
            $breadcrumbs[$totalBreadcrumbs] = "";
            $totalBreadcrumbs -= 1;
        }

        $breadcrumbs = array_filter($breadcrumbs);
        return $breadcrumbs;
    }

    public function Details(Request $request, $slug='') {
        return view('productdetails');

        $custom_columns = getCustomColumns();

        $product = Product::with('images','categories')->where('slug',$slug)
            ->where('p_status','<>',7)
            ->first();

        $breadcrumbs = [
            $product->categories->category_name,
            $product->p_model,
            Conditions()->get($product->p_condition),
            $product->p_gender,
            $product->p_casesize,
            ];

        if ($product) {
            $relatedProducts = $this->RelatedProducts($product->id);
            $discount = $this->discountRule();

            return view(
                'productdetails',
                compact('product',
                    'custom_columns',
                    'relatedProducts',
                    'breadcrumbs',
                    'discount',
                )
            );
        }

        // return view('productdetails');
    }

    public function checkout() {
        return view('lvcheckout');
    }

    public function creditCardProcessor() {
        return view('creditcardprocessor');
    }

    public function ProductDetails(Request $request, $slug='') {

        //$categories = Category::sidebar(); // gets this from Category model
        //dd($request);
        $custom_columns = getCustomColumns();

        $product = Product::with('images')->where('slug',$slug)
        ->where('p_status','<>',7)
        ->first();

        if ($product) {
            //\Log::info('Product Details: '. $product->title . ': '. getClientIP());
            $paths = explode('/',url()->current());
            foreach ($paths as $path) {
                if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                    $routes[] = $path;
            }

            $relatedProducts = $this->RelatedProducts($product->id);
            $totalcart = Cart::products();
            $discount = $this->discountRule();

            $product_details=true;
            $lpath = '';

            if ($routes[0]=='chrono24') $lpath = 'withmarkups';

            return view(
                'product-details',
                compact('product',
                    'routes',
                    'custom_columns',
                    'relatedProducts',
                    'totalcart',
                    'lpath',
                    'discount',
                    'product_details'
                )
            );
        } elseif ($slug == "admin") {
            $orders = Order::selectRaw('created_at as date, sum(subtotal) total')
            ->whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])
            ->groupBy('date')
            ->get();

            $from = date('Y-m-d',strtotime("-30 days"));

            $invoices = Order::where("status",0)
                ->where('created_at', '<=', $from)
                ->get();

            return view('admin.dashboard',['pagename'=>'Dashboard', 'orders'=>$orders,'invoices'=>$invoices]);
        } else
           abort(404, 'Unauthorized action.');
    }

    public function search1 (Request $request, $id='',$name='',$status='') {

        // $categories = Category::all();
        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();

        $term = $request['p'];
        $products = Product::search($term)->paginate(20);

        $paths = explode('/',url()->current());
        foreach ($paths as $path) {
            if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
                $routes[] = $path;
        }

        if (count($routes)>2)
            $title = $this->title;
        else $title = 'Brand new and pre-owned Watches';
        $discount = $this->discountRule();

        return view('search',['products' => $products,'currentPage'=>'Results for: '.$term,'categories' => $categories,'routes'=>$routes, 'discount' => $discount]);

    }

    private function getCondition($condition) {

        foreach (Conditions() as $key => $_condition) {
            if (strtolower($_condition) == $condition) {
                return $key;
            }
        }

        return '';
    }

    private function getStatus($status) {
        foreach (status() as $key => $_status) {
            $status = str_replace('-',' ',$status);
            if (strtolower($_status) == strtolower($status)) {

                return $key;
            }
        }

        return '';
    }

    private function getPrices($price) {
        $prices = explode("-",$price);
        if (!$prices[1]){
            $prices[1] = '9999999999999';
            return $prices ;
        }elseif (!$prices[0]){
            $prices[0] = '0';
            return $prices;
        }else return $prices;
    }

    public function fetch(Request $request) {
        $products = $this->filter('',$request);
        $next_page_url = $products->next_page_url;
        return view('pagination_child', compact('products','next_page_url'))->render();
    }

    private function filter($id='',$request=null,$models='') {

        $products = Product::with('categories')
            //->inRandomOrder()
            ->where('p_status','<>',9)
            ->where('p_status','<>',4)
            ->where('p_status','<>',7)
            ->orderBy('updated_at','desc');

        //$products->searchable();
        if (isset($request['condition'])) {
            $condition = $request['condition'];
            $condition_key = $this->getCondition($condition);
            if ($condition_key==2)
                $products = $products->whereIn('p_condition',[1,2]);
            else
                $products = $products->where('p_condition',$condition_key);
        }

        if ($id)
            $products = $products->where('category_id',$id);

        if (isset($request['price'])) {
            $price = $request['price'];
            $prices = $this->getPrices($price);
            $products = $products->whereBetween('p_price',$prices);
        }

        if (isset($request['status'])) {
            $status = $request['status'];
            $status = $this->getStatus($status);

            $products = $products->where('p_status',$status);
        }

        if (isset($request['color'])) {
            $color = str_replace('-',' ',$request['color']);

            $products = $products->where('p_color',$color);
        }

        if ($models) {
            $products = $products->where('slug',"LIKE","%".$models."%");
        }

        $products = $products->where('p_qty','>',0)->latest()->paginate(20);
        //$products = $products->inRandomOrder();
        //$product->searchable();
        return $products;
    }

    public function TotalCart() {
        return Cart::products();
    }
}
