<?php

/*
Berdvaye
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::resource('category','CategoryController');


    // ===============================================
    // STATIC PAGES ==================================
    // ===============================================

use Illuminate\Http\Request;
use App\Models\Dealer;
use App\Models\UserTracker;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Orders;
use App\Livewire\Sculptures;
use App\Livewire\ProductDetails;
use App\Livewire\InvoicePayments;
use App\Livewire\Invoices;
use App\Livewire\Credentials;
use App\Livewire\Products;
use App\Livewire\Customers;
use App\Livewire\Dealers;
// use App\Livewire\Reports;
use App\Livewire\Models;

//Route::any('{query}',function() { return redirect('/'); })->where('query', '.*');

//Route::auth();
Auth::routes();

Route::get('ajaxgooglemarkers','App\Http\Controllers\GoogleMarkersController@ajaxGoogleMarkers')->name('google.maker');
Route::get('ajaxgetbygooglemarker','App\Http\Controllers\GoogleMarkersController@ajaxGetByGoogleMarker')->name("get.google.maker");
Route::get('getStateFromCountry', "App\Http\Controllers\CountriesController@getStateFromCountry")->name('get.state.from.country');
Route::get('getStateByCountry', "App\Http\Controllers\CountriesController@getStateByCountry");
Route::post('ajaxInquiry', "App\Http\Controllers\InquiryController@ajaxInquiry")->name('ajax.inquiry');

Route::group(['middleware' => 'web'], function () {
    Route::get('/google-merchant.xml', function () {
        $products = \App\Models\ProductRetail::where('is_active', true)->get();

        return response()
            ->view('google-merchant', compact('products'))
            ->header('Content-Type', 'application/xml');
    });

    Route::get('payment/thankyou', 'App\Http\Controllers\CartController@Thankyou')->name('payment.thankyou');
    Route::get('payment/alldone', 'App\Http\Controllers\CartController@alldone')->name('all.done');
    Route::get('get/tax', 'App\Http\Controllers\CartController@getTax')->name('get.tax.value');
    Route::post('payment/{orderID}/capture', 'App\Http\Controllers\CartController@Capture')->name('payment.capture');
    Route::post('payment/order', 'App\Http\Controllers\CartController@order')->name('payment.order');

    Route::get('/sculptures', Sculptures::class);
    Route::get('/sculptures/{slug}', ProductDetails::class);

    Route::get('cart/unsuccessful', 'App\Http\Controllers\CartController@Unsuccessful');
    Route::post('cart/payment', 'App\Http\Controllers\CartController@checkoutpayment');
    Route::post('cart/product/remove','App\Http\Controllers\CartController@remove')->name('cart.remove');
    Route::post('cart/product/testremove','App\Http\Controllers\CartController@testremove');
    Route::post('cart/promo','App\Http\Controllers\CartController@promo');
    Route::post('addtocart/{id?}','App\Http\Controllers\CartController@addToCart')->name('add.to.cart');
});

Route::get('/', function()
{
    if (isset($_SERVER["HTTP_REFERER"])) {
        if ($_SERVER['HTTP_REFERER'] != 'http://berdvaye.com') {
            UserTracker::create([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'referrer' => $_SERVER['HTTP_REFERER'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']

            ]);
        }
        session()->put('referrer', $_SERVER["HTTP_REFERER"]);
    }

    return View::make('index',['activePage' =>'home']);
});

Route::get('dealers', function()
{
    $dealers = Dealer::orderBy('customer','asc')->get();
    return View::make('dealers',['dealers'=>$dealers,'activePage' =>'dealers']);

});

Route::get('getretailprice','App\Http\Controllers\ProductRetailController@getRetailPrice')->name('retail.price');

Route::get('/{page}', 'App\Http\Controllers\ProductsController')
    ->name('page')
    ->where('page',
    'process-finishing|process-deconstruction|deconstruction|design|forming|process|process-design|media|privacy-policy|terms-and-conditions|about|contact');


// ===============================================
// ADMIN SECTION =================================
// ===============================================
Route::group(['prefix' => 'admin', 'middleware'=>['auth']], function()
{
    // main page for the admin section (app/views/admin/dashboard.blade.php)

    // subpage for the posts found at /admin/posts (app/views/admin/products.blade.php)

    Route::get('/', 'App\Http\Controllers\DashboardController@index');
    //$this->get('login', 'Auth\LoginController@showLoginForm',['pagename','Login']);

    Route::get('/', [DashboardController::class,'index'])->name('index');
    // Route::get('lvproducts', [ProductsController::class,'lvproducts']);
    Route::get('/orders', Orders::class);
    Route::get('/invoice-payments', InvoicePayments::class);
    // Route::get('lvreports', [ProductsController::class,'lvreports']);
    //Route::get('lvmodels', [ProductsController::class,'lvmodels']);
    // Route::get('lvinvoices', [OrdersController::class,'lvinvoices']);
    Route::get('/invoices', Invoices::class);
    Route::get('/products', Products::class);
    Route::get('/customers', Customers::class);
    Route::get('credentials', App\Livewire\Credentials::class);
    // Route::get('/reports', Reports::class);
    Route::get('/models', Models::class);
    Route::get('/dealers', Dealers::class);

  // subpage for the posts found at /admin/posts (app/views/admin/products.blade.php)
    Route::resource('productretail', "App\Http\Controllers\ProductRetailController");
    Route::get('productretail/{id}/destroy', "App\Http\Controllers\ProductRetailController@destroy");
    // Route::resource('products', "App\Http\Controllers\ProductsController");
    // Route::get('products/{id}/printinventory/{params}/{date?}', 'App\Http\Controllers\ProductsController@printInventory');
    // Route::get('products/{id}/print', 'App\Http\Controllers\ProductsController@print');
    Route::get('products/{model}/printmissingproducts', 'App\Http\Controllers\ProductsController@printMissingProducts');
    // Route::get('products/create', "App\Http\Controllers\ProductsController@create");
    // Route::get('products/{id}/duplicate', "App\Http\Controllers\ProductsController@duplicate");
    // Route::post('products/store', "App\Http\Controllers\ProductsController@store");
    // Route::post('products/{id?}/storeduplicate', "App\Http\Controllers\ProductsController@storeDuplicate");
    // Route::get('products/{id}/destroy', "App\Http\Controllers\ProductsController@destroy");
    Route::get('products/1/export', 'App\Http\Controllers\ProductsController@export'); // for some reason if I dont's specify 1, it doesn't work. I guess it's looking for a number. Something I have to fix in the future.

    // Ajax Controller Calls

    Route::post('imageupload','App\Http\Controllers\DropzoneController@uploadFiles')->name('upload.files');
    Route::post('imagedelete','App\Http\Controllers\DropzoneController@deleteImage');
    Route::post('imagecustomerdelete','App\Http\Controllers\DropzoneController@deleteCustomerImage');
    Route::post('imagedealerdelete','App\Http\Controllers\DropzoneController@deleteDealerImage')->name('delete.files');
    Route::post('imagedeletepost','App\Http\Controllers\DropzoneController@deleteImageFromPost')->name('delete.image.from.post');
    Route::post('deletefromproductretail','App\Http\Controllers\DropzoneController@deleteImageFromProductRetail')->name('delete.productretail.image');
    Route::post('createFromBackorder','App\Http\Controllers\OrdersController@createFromBackorder')->name('create.backorder.product');
    Route::post('ajaxSetItemShipped','App\Http\Controllers\OrdersController@ajaxSetItemShipped')->name('set.item.shipped');
    Route::post('ajaxSetItemAsRepair','App\Http\Controllers\OrdersController@ajaxSetItemAsRepair')->name('set.item.repair');
    Route::post('ajaxRecieveItemBackFromRepair','App\Http\Controllers\OrdersController@ajaxRecieveItemBackFromRepair')->name('receive.item.from.repair');

    Route::get('ajaxproducts','App\Http\Controllers\ProductsController@ajaxProducts')->name('ajax.products');
    Route::get('ajaxFindProduct','App\Http\Controllers\ProductsController@ajaxFindProduct')->name('find.product');
    Route::get('ajaxCreateEmptyRowForInvoice','App\Http\Controllers\ProductsController@ajaxCreateEmptyRowForInvoice')->name('new.invoice.row');
    Route::get('ajaxGetBackorders','App\Http\Controllers\OrdersController@ajaxGetBackorders')->name('get.backorders');
    Route::get('ajaxGetRepairs','App\Http\Controllers\OrdersController@ajaxGetRepairs')->name('get.repairs');

    Route::get('ajaxgetretailproducts','App\Http\Controllers\ProductsController@ajaxGetRetailProducts')->name('get.retailproducts');
    Route::get('ajaxgetproductimages','App\Http\Controllers\ProductsController@ajaxGetProductImages')->name('get.product.images');

    Route::get('ajaxgetcredit','App\Http\Controllers\ReturnsController@ajaxGetCredit');
    Route::get('ajaxestimatedproducts','App\Http\Controllers\ProductsController@ajaxEstimatedProducts')->name('ajax.estimated.products');
    Route::get('ajaxgetproduct','App\Http\Controllers\ProductsController@ajaxgetProduct');
    Route::get('ajaxsavecustomer','App\Http\Controllers\OrdersController@ajaxSaveCustomer')->name('ajax.save.customer');
    Route::get('ajaxcustomer','App\Http\Controllers\CustomersController@ajaxCustomer')->name('get.customer.byID');
    Route::get('ajaxselectproduct','App\Http\Controllers\ProductsController@ajaxSelectFoundProduct')->name('select.found.product');
    Route::get('ajaxcustomerL','App\Http\Controllers\CustomersController@ajaxCustomerL')->name('ajax.customer');
    Route::get('ajaxdealer','App\Http\Controllers\CustomersController@ajaxDealer')->name('ajax.dealer');
    Route::get('ajaxgetcustomer','App\Http\Controllers\CustomersController@ajaxgetCustomer')->name('get.customer.info');
    Route::get('ajaxgetCustomerL','App\Http\Controllers\CustomersController@ajaxgetCustomerL')->name('ajax.get.customer');
    Route::get('ajaxsupplier','App\Http\Controllers\SuppliersController@ajaxSupplier');
    Route::get('ajaxgetsupplier','App\Http\Controllers\SuppliersController@ajaxgetSupplier');
    Route::get('ajaxreturnitem','App\Http\Controllers\ReturnsController@ajaxReturnItem')->name('ajax.return.item');
    Route::get('ajaxreturnall','App\Http\Controllers\ReturnsController@ajaxReturnAll')->name('ajax.return.all');
    Route::get('destroyproduct', 'App\Http\Controllers\OrdersController@destroyproduct')->name('destroy.product');
    Route::get('destroyestimatedproduct', 'App\Http\Controllers\EstimatesController@destroyestimatedproduct')->name('destroy.estimated.product');
    Route::get('admin/products/getAll',['as' => 'getAll', 'uses' => 'App\Http\Controllers\ProductsController@getAll']);
    Route::get('ajaxorderstatus','App\Http\Controllers\OrdersController@ajaxOrderStatus')->name('ajax.order.status');
    Route::post('wirediscount', 'App\Http\Controllers\ProductsController@WireDiscount')->name('wire.discount');

    // Customers Controller
    // Route::resource('customers', 'App\Http\Controllers\CustomersController');
    // Route::get('customers/{id}/destroy', 'App\Http\Controllers\CustomersController@destroy');

    // Route::resource('dealers', 'App\Http\Controllers\DealersController');

    Route::resource('sculptureorders', 'App\Http\Controllers\SculptureOrdersController');
    //Route::resource('estimates', 'App\Http\Controllers\EstimatesController');
    Route::get('estimates/{id}/destroy', 'App\Http\Controllers\EstimatesController@destroy');
    Route::get('estimates/{id}/print', 'App\Http\Controllers\EstimatesController@print');
    Route::get('estimates/{id}/printstatement', 'App\Http\Controllers\EstimatesController@printStatement');
    //Route::get('estimates/{id}/invoice/create', ['as'=>'invoice.create', 'uses' => 'App\Http\Controllers\EstimatesController@createFromEstimate']);
    Route::get('orders/{id}/create', ['as'=>'invoice.create', 'uses' => 'App\Http\Controllers\OrdersController@createFromEstimate']);
    //Route::post('estimates/{id}/storeinvoicetoorder', ['as'=>'invoice.store', 'uses' => 'App\Http\Controllers\EstimatesController@storeInvoiceFromOrder']);
    Route::post('orders/{id}/store', ['as'=>'invoice.store', 'uses' => 'App\Http\Controllers\OrdersController@storeInvoiceFromOrder']);
    Route::get('orders/addressfromzip','App\Http\Controllers\OrdersController@addressFromZip')->name('address.from.zip');

    Route::get('orders/{id}/payments/create', ['as'=>'payments.create', 'uses' => 'App\Http\Controllers\CreditsController@create']);
    Route::get('credits/{id}/create', ['as'=>'credits.create', 'uses' => 'App\Http\Controllers\CreditsController@create']);
    Route::post('credits/payment', ['as'=>'credit_payment', 'uses'=>'App\Http\Controllers\CreditsController@CreditPayment']);
    Route::post('orders/destroy/{id?}', 'App\Http\Controllers\CreditsController@deletePayment')->name('destroy.payment');
    Route::resource('credits', 'App\Http\Controllers\CreditsController');

    // Route::get('orders/markAsRead', 'App\Http\Controllers\OrdersController@markAsRead');
    // Route::resource('orders', 'App\Http\Controllers\OrdersController');
    // Route::get('orders/{id}/destroy', 'App\Http\Controllers\OrdersController@destroy');
    Route::get('orders/{id}/print/{output?}', 'App\Http\Controllers\OrdersController@print');
    Route::get('orders/{id}/{status}/printstatement', 'App\Http\Controllers\OrdersController@printStatement');
    Route::get('printstatementsdue', 'App\Http\Controllers\OrdersController@printStatementsDue');
    // Route::post('orders/{id}/payments/store', 'App\Http\Controllers\PaymentsController@store')->name('payments.store');
    // Route::get('orders/{id}/memotransfer', 'App\Http\Controllers\OrdersController@memotransfer');
    // Route::patch('orders/{id}/memotransfer/update', ['as'=>'memotransfer.update', 'uses' => 'App\Http\Controllers\OrdersController@memoStore']);
    // Route::get('orders/{id}/copyinvoice', 'App\Http\Controllers\OrdersController@copyInvoice');
    // Route::patch('orders/{id}/copyinvoice/update', ['as'=>'copyinvoice.update', 'uses' => 'App\Http\Controllers\OrdersController@storeInvoice']);

    Route::get('payments', 'App\Http\Controllers\PaymentsController@index');
    //Route::get('orders/{id}/payments/create', ['as'=>'payments.create', 'uses' => 'App\Http\Controllers\PaymentsController@create']);
    //Route::post('payments/store', 'App\Http\Controllers\PaymentsController@store');
    Route::get('payments/{id}/show', 'App\Http\Controllers\PaymentsController@show');
    Route::patch('payments/{id}/update', ['as'=>'payments.update', 'uses' => 'App\Http\Controllers\PaymentsController@update']);
    //Route::get('orders/{id}/payments/{payment}/destroy', 'App\Http\Controllers\PaymentsController@destroy');

    //Route::get('returns', 'App\Http\Controllers\ReturnsController@index');
    //Route::post('returns/store', 'App\Http\Controllers\ReturnsController@store')->name();
    Route::get('returns/{id}', 'App\Http\Controllers\ReturnsController@show');
    //Route::get('orders/{id}/returns/create', ['as'=>'returns.create', 'uses' => 'App\Http\Controllers\ReturnsController@create']);
    Route::get('orders/{id}/returns/print', ['as'=>'returns.print', 'uses' => 'App\Http\Controllers\ReturnsController@print']);

    Route::resource('users', 'App\Http\Controllers\UsersController');
    Route::get('users/{id}/destroy', 'App\Http\Controllers\UsersController@destroy');

    Route::resource('roles', 'App\Http\Controllers\RolesController');
    Route::resource('permissions', 'App\Http\Controllers\PermissionsController');

    // Route::get('reports', 'App\Http\Controllers\ReportsController@index');
    // Route::get('reports/print/{param?}/{date?}', 'App\Http\Controllers\ReportsController@printSales');
    // Route::get('reports/printsales/{param?}/{date?}/{company?}', 'App\Http\Controllers\ReportsController@printSales1');

    // Route::get('reports/printUnpaid/{criteria?}', 'App\Http\Controllers\ReportsController@printUnpaid');
    // Route::get('reports/printmemos', 'App\Http\Controllers\ReportsController@printMemos');
    Route::get('reports/printowned', 'App\Http\Controllers\ReportsController@printOwed')->name('print.all.owed');
    Route::resource('imagecollections','App\Http\Controllers\ImageCollectionController');
    Route::resource('posts','App\Http\Controllers\PostsController');
    Route::get('imagecollections/{id}/destroy','App\Http\Controllers\ImageCollectionController@destroy');

    Route::resources(
        [

            'returns' => 'App\Http\Controllers\ReturnsController',
            'estimates' => 'App\Http\Controllers\EstimatesController'

        ]
    );
});
