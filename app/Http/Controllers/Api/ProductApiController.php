<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Category;
//use App\Http\Controllers\API\APIBaseController as APIBaseController;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Models\Product;
use DOMDocument;
use DOMElement;

class ProductApiController extends Controller
{
    public function sendResponse($result, $categories, $message, ?Request $request = null)
    {
        $response = [
            'data'    => $result,
            'categories' => $categories,
            'message' => $message,
            'total' => count($result)
        ];

        $request ??= request();

        if ($this->wantsXml($request)) {
            return response($this->toXml($response), 200)
                ->header('Content-Type', 'application/xml; charset=UTF-8');
        }

        return response()->json($response, 200);
    }

    private function wantsXml(Request $request): bool
    {
        $preferredType = strtolower($request->getAcceptableContentTypes()[0] ?? '');

        return strtolower((string) $request->query('format')) === 'xml'
            || str_ends_with($request->path(), '.xml')
            || $preferredType === 'application/xml'
            || $preferredType === 'text/xml'
            || str_ends_with($preferredType, '+xml');
    }

    private function toXml(array $response): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;
        $root = $document->createElement('response');
        $document->appendChild($root);

        $this->appendXml($document, $root, $response);

        return $document->saveXML();
    }

    private function appendXml(DOMDocument $document, DOMElement $parent, array $values): void
    {
        foreach ($values as $key => $value) {
            $name = is_int($key) ? $this->xmlItemName($parent->tagName) : $key;
            $element = $document->createElement($name);
            $parent->appendChild($element);

            // The existing JSON represents images and categories as one-value arrays.
            // Flatten those wrappers so the XML remains natural and easy to consume.
            if (is_array($value) && array_is_list($value) && count($value) === 1 && !is_array($value[0])) {
                $value = $value[0];
            }

            if (is_array($value)) {
                $this->appendXml($document, $element, $value);
            } elseif ($value !== null) {
                $element->appendChild($document->createTextNode((string) $value));
            }
        }
    }

    private function xmlItemName(string $parentName): string
    {
        return match ($parentName) {
            'data' => 'product',
            'categories' => 'category',
            'images' => 'image',
            default => 'item',
        };
    }


    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::select("products.id","title","movement","p_casesize","p_model","p_reference","p_box","p_papers","p_material","p_condition","p_retail","p_newprice","web_price","p_status","p_gender","p_strap","slug",'category_name','products.created_at')
            ->join('categories','category_id','=','categories.id')
            ->where('p_qty', '>', 0)
            ->where('p_newprice', '>', 0)
            ->whereIn('p_status', [0,1,5])
            ->orderBy('created_at','desc') //->limit(10)
            ->get();

        $brands = $this->getCategories();

        $item = [];

        foreach ($products as $product) {
            $images = array();

            foreach ($product->images as $image) {
                $images[] = array("https://swissmadecorp.com/images/thumbs/".$image->location);
            }

            if (count($images) == 0) {
                $images[] = array("https://swissmadecorp.com/images/no-image.jpg");
            }

            // if ($product->p_status == 6)
            //     $status = "Available";
            // else
            $status = Status()->get($product->p_status);

            $item[] = array(
                "id" => (string) $product->id,
                "title" => $product->title,
                "model" => $product->p_model,
                'category' => $product->category_name,
                "reference" => $product->p_reference,
                "box" => $product->p_box == 0 ? 'No' : 'Yes',
                "papers" => $product->p_papers == 0 ? 'No' : 'Yes',
                "material" => Materials()->get($product->p_material),
                "condition" => Conditions()->get($product->p_condition),
                "retail" => $product->p_retail,
                "price" => $product->p_newprice,
                "webprice" => $product->web_price,
                "movement" => Movement()->get($product->movement),
                "case_size" => $product->p_casesize,
                "status" => $status,
                'year' => $product->p_year,
                "gender" => $product->p_gender,
                "strap" => Strap()->get($product->p_strap),
                "slug" => $product->slug,
                "images" => $images,
            );
        }


        return $this->sendResponse($item, $brands, 'Retrieved successfully.', $request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();
        if ($user->tokenCan('send:message')) {
            ProductResource::withoutWrapping();
            $product =  new ProductResource(Product::with('images','categories')->find($id));
            return response()->json($product);
        } else return response()->json('The credentials are incorrect.');

    }

    private function getCategories() {
        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            $query->where('p_newprice', '>', 0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();

        $brands[] = array("All categories");
        foreach ($categories as $category) {
            $brands[] = array($category->category_name);
        }

        return $brands;
    }

    public function byCategory($category_name)
    {

        $products = Product::select("products.id","title","movement","p_casesize","p_model","p_reference","p_box","p_papers","p_material","p_condition","p_retail","p_newprice","p_status","p_gender","p_strap","slug",'category_name')
        ->join('categories','category_id','=','categories.id')
        ->where('p_qty', '>', 0)
        ->where('p_newprice', '>', 0)
        ->whereIn('p_status', [0,5])
        ->where('categories.category_name',$category_name)
        ->get();

        $brands = $this->getCategories();

    foreach ($products as $product) {
        $images = array();

        foreach ($product->images as $image) {
            $images[] = array("https://swissmadecorp.com/public/images/thumbs/".$image->location);
        }

        if (count($images) == 0) {
            $images[] = array("https://swissmadecorp.com/public/assets/logo-swissmade.jpg");
        }

        $item[] = array(
            "id" => (string) $product->id,
            "title" => $product->title,
            "model" => $product->p_model,
            'category' => $product->category_name,
            "reference" => $product->p_reference,
            "box" => $product->p_box == 0 ? 'No' : 'Yes',
            "papers" => $product->p_papers == 0 ? 'No' : 'Yes',
            "material" => Materials()->get($product->p_material),
            "condition" => Conditions()->get($product->p_condition),
            "retail" => $product->p_retail,
            "price" => $product->p_newprice,
            "movement" => Movement()->get($product->movement),
            "case_size" => $product->p_casesize,
            "status" => Status()->get($product->p_status),
            "gender" => $product->p_gender,
            "strap" => Strap()->get($product->p_strap),
            "slug" => $product->slug,
            "images" => $images
        );
    }


        return $this->sendResponse($item, $brands, 'Retrieved successfully.');
    }

    public function remove(Request $request) {
        // \Session::forget('product_'.$request['id']);

        return \Session::get('product_'.$request['id']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
