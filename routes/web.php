<?php

/*
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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WatchesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\EstimatesController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Orders;
use App\Livewire\EbayToken;
use App\Livewire\CTheShow;

// use App\Livewire\GetGlobalPrices;
use App\Http\Middleware\BlockIpMiddleware;

//Route::any('{query}',function() { return redirect('/'); })->where('query', '.*');

//Route::auth();
Auth::routes();

Route::group(['middleware' => ['web',BlockIpMiddleware::class]], function () {

    Route::permanentRedirect('/watches', '/watch-products');
    Route::permanentRedirect('/cart', '/checkout');
    Route::permanentRedirect('/cart/checkout', '/checkout');

    Route::get('credit-card-processor/{id}/{hash}', [WatchesController::class,'creditCardProcessor'])->name('credit.card.processor');

    Route::get('whatsappverify', [ProductsController::class,"whatsappVerify"]);
    // Route::get('test1', [WatchesController::class,'applepay']);
    Route::get('home', [WatchesController::class,'test']);
    Route::get('watch-products', [WatchesController::class,'products'])->name('watch.products');

    Route::get('new-arrival', [WatchesController::class,'newarrivals'])->name('new.arrival');
    Route::get('checkout', [WatchesController::class,'checkout'])->name('checkout');
    Route::get('product-details/{slug}', [WatchesController::class,'Details'])->name('product.details');
    Route::get('sell-your-watches', [WatchesController::class,'sellyourwatch']);
    Route::post('charge', [WatchesController::class,'charge']);
    Route::get('breadcrumbs', [WatchesController::class,'breadcrumbs'])->name('get.breadcrumbs');

    // Route::get('cart', 'App\Http\Controllers\CartController@cart')->name('cart');
    // Route::post('cart/checkout', 'App\Http\Controllers\CartController@checkout')->name('cart.checkout');
    // Route::get('payment/thankyou', 'App\Http\Controllers\CartController@finalizePurchase')->name('cart.finalize.order');

    Route::get('thankyou', function () {
        // foreach (App\Models\Cart::products() as $product) {
        //     App\Models\Cart::Remove($product['id']);
        // }

        // if (session()->has('discount'))
        //     session()->forget('discount');

        // if (session()->has('customer'))
        //     session()->forget('customer');

        // if (session()->has('order'))
        //     session()->forget('order');

        return view('payment/thankyouorder');
    })->name('finalize.checkout');

    //Route::get('payment/thankyou', 'App\Http\Controllers\CartController@Thankyou')->name('cart.thankyou');
    Route::get('cart/unsuccessful', 'App\Http\Controllers\CartController@Unsuccessful')->name('cart.unsuccessful');

    Route::get('unsubscribe/{email?}','App\Http\Controllers\NewsletterController@unsubscribe');
    Route::post('unsubscribe/success','App\Http\Controllers\NewsletterController@success')->name('unsubscribe.success');

    // blogroll to show all blog posts
    Route::get('blog/{category?}', "App\Http\Controllers\PostsController@blog");
    Route::get('blogs/{category?}', "App\Http\Controllers\PostsController@blogs");

    Route::post('cart/payment', 'App\Http\Controllers\CartController@checkoutpayment')->name('cart.payment');

    Route::post('cart/product/remove','App\Http\Controllers\CartController@remove')->name('cart.product.remove');
    Route::post('cart/product/testremove','App\Http\Controllers\CartController@testremove')->name('cart.test.remove');
    Route::post('cart/promo','App\Http\Controllers\CartController@promo')->name('cart.promo');
    Route::post('cart/update','App\Http\Controllers\CartController@CartUpdate')->name('cart.update');

    Route::get('getCountries','App\Http\Controllers\Api\CountryApiController@getCountries')->name('get.countries');
    Route::get('getStates','App\Http\Controllers\Api\CountryApiController@getStates')->name('get.states');
    Route::get('getAStateFromCountry','App\Http\Controllers\Api\CountryApiController@getAStateFromCountry')->name('state.from.country');
    Route::get('feed', 'App\Http\Controllers\RssFeedController@feed');
    Route::get('cart/wirepayment','App\Http\Controllers\CartController@WirePayment')->name('wire.payment');
    Route::get('cart/producttimeleft', 'App\Http\Controllers\CartController@ProductTotalTimeLeft')->name('product.time.left');
    Route::post('cart/releasehold', 'App\Http\Controllers\CartController@ReleaseHold')->name('product.release.hold');

    Route::post('bookings/store', 'App\Http\Controllers\BookingController@store')->name('bookings.store');
    Route::get('homepage', [WatchesController::class,'homepage']);
    Route::get('newarrival/{id?}/{name?}', [WatchesController::class,'newarrival']);
    Route::post('amazon-login', [WatchesController::class,'AmazonLogin'])->name('amazon.login');
    Route::post('addtocart/{id?}','App\Http\Controllers\CartController@addToCart')->name('add.to.cart');
    Route::post('pagination/fetch', [WatchesController::class,'fetch'])->name('pagination.fetch');
    Route::get('category/filter', [WatchesController::class,'CategoryFilter'])->name('category.filter');

    Route::post('member/login', "App\Http\Controllers\MemberLoginController@loginMember")->name('member.login');
    Route::get('member/logout', "App\Http\Controllers\MemberLoginController@logout")->name('member.logout');
    Route::post('ajaxinquiry', "App\Http\Controllers\InquiryController@store")->name('watch.inquiry');
    Route::post('ajaxpriceoffer', "App\Http\Controllers\InquiryController@priceOffer")->name('price.offer');
    Route::post('sellyourwatch', "App\Http\Controllers\InquiryController@SellYourWatch")->name('sell.your.watch');
    Route::put('admin/updateorderstatus', "App\Http\Controllers\OrdersController@UpdateOrderStatus")->name('update.order.status');
    Route::post('uploadcustomerfiles','App\Http\Controllers\DropzoneController@uploadCustomerFiles')->name('upload.customer.image');
    Route::post('paypal/checkout', 'App\Http\Controllers\PayPalController@PayPalCheckout')->name('paypal.checkout');

    Route::post('amazon/details', 'App\Http\Controllers\AmazonPaymentController@getCartDetails')->name('amazon.details');
    Route::post('amazon/payment', 'App\Http\Controllers\AmazonPaymentController@processAmazonPayment')->name('amazon.process.payment');
    Route::post('amazon/clearsession', 'App\Http\Controllers\AmazonPaymentController@ClearSession')->name('amazon.clear.session');
    Route::get('finilize/amazon', 'App\Http\Controllers\AmazonPaymentController@finilizeAmazon')->name('finilize.amazon.payment');

    Route::post('payment/checkout', 'App\Http\Controllers\PayPalController@Checkout')->name('payment.checkout');
    //Route::get('payment/thankyou', 'App\Http\Controllers\PayPalController@Thankyou')->name('payment.thankyou');
    Route::get('payment/success', 'App\Http\Controllers\PayPalController@SuccessPage')->name('payment.success');
    Route::get('payment/cancel', 'App\Http\Controllers\PayPalController@CancelPage')->name('payment.cancel');
    Route::post('orders/find', 'App\Http\Controllers\CustomerOrderController@find')->name('orders.find');
    Route::get('account/order', 'App\Http\Controllers\CustomerOrderController@AccountOrder');
    Route::post('ipn/notify','App\Http\Controllers\PayPalController@postNotify')->name('ipn.notify'); // Change it accordingly in your application

    Route::get('getStateFromCountry', "App\Http\Controllers\CountriesController@getStateFromCountry")->name('get.state.from.country');
    Route::get('/{page}', 'App\Http\Controllers\WatchesController')
        ->name('page')
        ->where('page','contact-us|contactus|account|about-us|aboutus|privacy-policy|terms-conditions|privacypolicy|new-arrival|termsconditions|wire-transfer-guide|blogs|rolex-serial-numbers|rolexserialnumbers');


    Route::post('switchRates',"App\Http\Controllers\ExchangeRateController@switchRates")->name('switch.currency.rate');
    Route::resource('testimonies', 'App\Http\Controllers\TestimonyController');
    // Route::get('/',[WatchesController::class,'homepage'])->name('homepage');

    Route::get('/',[WatchesController::class,'test'])->name('homepage');

    Route::get('chrono24/withmarkups/{id?}/{name?}', [WatchesController::class,'chrono24page']);
    //Route::get('chrono24/withmarkups/{id?}/{name?}', [WatchesController::class,'testshow']);
    Route::get('search/{id?}/{name?}', [WatchesController::class,'search'])->name('search');

    Route::get('watches/{id?}/{name?}/{name2?}', [WatchesController::class,'show'])->name('show.watches');
    Route::get('new-unworn-certified-pre-owned-watches/{slug}', "App\Http\Controllers\WatchesController@ProductDetails");
    Route::get('chrono24/watches/{slug?}',[WatchesController::class,'ProductDetails']);
    Route::get('chrono24/watches/certified-pre-owned-watches/{slug?}',[WatchesController::class,'ProductDetails']);

    Route::get('verify','App\Http\Controllers\GoogleRecapchaController@verify')->name('google.verify');
    Route::get('searches', function() {
        $query = ''; // <-- Change the query for testing.

        $products = App\Product::where('p_qty', 1)
            ->get();
            return $products;
        $products->searchable();
        return $products;
    });
    // Route::get('{slug}', [WatchesController::class,'ProductDetails']);
});


// ===============================================
// ADMIN SECTION =================================
// ===============================================
Route::group(['prefix' => 'admin','middleware'=>['auth']], function()
{
    // main page for the admin section (app/views/admin/dashboard.blade.php)

    Route::get('test2', [WatchesController::class,'test2']);
    Route::get('/', [DashboardController::class,'index'])->name('index');
    // Route::get('lvproducts', [ProductsController::class,'lvproducts']);
    Route::get('lvopenai', [ProductsController::class,'lvopenai']);
    Route::get('lvreports', [ProductsController::class,'lvreports']);
    Route::get('lvreminders', [ProductsController::class,'lvreminders']);
    // Route::get('lvinvoices', [OrdersController::class,'lvinvoices']);
    // Route::get('lvorders', [EstimatesController::class,'lvorders']);
    Route::get('/orders', App\Livewire\Orders::class);
    Route::get('/ebayToken', App\Livewire\EbayToken::class);
    Route::get('/invoices', App\Livewire\Invoices::class);
    Route::get('/products', App\Livewire\Products::class);
    Route::get('lvcustomers', [CustomersController::class,'lvcustomers']);
    Route::get('lvinventory', [ProductsController::class,'inventory']);
    Route::get('lvtheshow', [ProductsController::class,'theshow']);
    Route::get('lvinvoicepayments', [ProductsController::class,'invoicepayments']);
    Route::get('lvexport', [ProductsController::class,'exportToExcel']);
    Route::get('printTag/{ids}', [ProductsController::class,'printTag'])->name('print.tag');
    Route::get('printlabel', [ProductsController::class,'printLabel']);

    // Route::get('global-prices', getGlobalPrices::class);

    Route::get('whatsapp', "App\Http\Controllers\ProductsController@scraper1");
    Route::get('getgoogletoken', 'App\Http\Controllers\CartController@getGoogleToken')->name('get.google.token');
    Route::resource('categories', "App\Http\Controllers\CategoriesController");
    Route::get('categories', "App\Http\Controllers\CategoriesController@index");
    Route::get('categories/{id}/edit', "App\Http\Controllers\CategoriesController@edit");
    Route::patch('categories/{id}/update', ['as'=>'category.update','uses'=>"App\Http\Controllers\CategoriesController@update"]);
    Route::get('categories/{id}/destroy', "App\Http\Controllers\CategoriesController@destroy");

    Route::get('exports/excel', 'App\Http\Controllers\ExportsController@Excel')->name('export.to.excel');
    Route::get('exports/chronoexport', 'App\Http\Controllers\ExportsController@Chrono24XMLExport')->name('chrono24.export');
    Route::get('exports/getajaxproducts','App\Http\Controllers\ExportsController@getAjaxProducts')->name('export.products');
    Route::get('ebay/getajaxebayproducts','App\Http\Controllers\EbayController@getAjaxEbayProducts')->name('ebay.products');
    Route::get('ebay/getajaxlisting','App\Http\Controllers\EbayController@getAjaxListing')->name('ebay.listing');
    Route::get('ebay/loadItems','App\Http\Controllers\EbayController@loadItems')->name('ebay.load.items');
    Route::post('ebay/synchronize','App\Http\Controllers\EbayController@Synchronize')->name('ebay.syncronize');

    Route::get('exports', 'App\Http\Controllers\ExportsController@index');

    Route::get('facebookToken', "App\Http\Controllers\ProductsController@facebookToken");

    Route::get('products/{id}/print', 'App\Http\Controllers\ProductsController@print');
    Route::get('products/{id}/printreturn', 'App\Http\Controllers\ProductsController@PrintReturn');
    Route::get('products/{id?}/duplicate', "App\Http\Controllers\ProductsController@duplicate");

    Route::get('products/jewelrycreate', 'App\Http\Controllers\ProductsController@jewelryCreate');
    Route::post('products/storejewelry', 'App\Http\Controllers\ProductsController@storeJewelry');

    Route::post('products/ebay/resubmit', 'App\Http\Controllers\ProductsController@EbayResubmit')->name('ebay.resubmit');
    Route::get('products/excel', ['as'=>'excel', 'uses'=>'App\Http\Controllers\ProductsController@Excel']);
    Route::get('products/globalPriceChange', 'App\Http\Controllers\ProductsController@globalPriceChange')->name('global.price.change');
    Route::post('products/createNewColumn', 'App\Http\Controllers\ProductsController@createNewColumn')->name('new.column');
    Route::get('products/globalProperties', 'App\Http\Controllers\ProductsController@globalProperties')->name('change.global.properties');
    Route::get('products/bezelcreate', 'App\Http\Controllers\ProductsController@bezelCreate');
    Route::get('products/{id}/bezeledit', 'App\Http\Controllers\ProductsController@bezelEdit');
    Route::get('products/{id}/jewelryedit', 'App\Http\Controllers\ProductsController@jewelryEdit');
    Route::post('products/storebezel', 'App\Http\Controllers\ProductsController@storeBezel');
    Route::patch('products/{id}/updatebezel', ['as'=>'product.updatebezel', 'uses' => 'App\Http\Controllers\ProductsController@updateBezel']);

    Route::post('products/{id?}/storeduplicate', "App\Http\Controllers\ProductsController@storeDuplicate")->name('duplicate.product');
    Route::get('products/{id}/destroy', "App\Http\Controllers\ProductsController@destroy")->name('delete.product');
    Route::get('products/{id}/restore', "App\Http\Controllers\ProductsController@restore")->name('restore.product');
    // Route::get('products', "App\Http\Controllers\ProductsController@index")->middleware('role:superuser|viewer');
    // Route::resource('products', "App\Http\Controllers\ProductsController");

    // Ajax Controller Calls
    Route::get('getProductsByCategory','App\Http\Controllers\MailMassController@getProductsByCategory')->name('mail.product.by.category');
    Route::get('startmassmail','App\Http\Controllers\MailMassController@startMassMail')->name('mail.start.massmail');
    Route::get('loadTemplate','App\Http\Controllers\MailMassController@loadTemplate')->name('mail.load.template');

    Route::post('imageupload','App\Http\Controllers\DropzoneController@uploadFiles')->name('upload.image');
    Route::post('imagedelete','App\Http\Controllers\DropzoneController@deleteImage')->name('delete.image');
    Route::post('imagecapture','App\Http\Controllers\DropzoneController@capturedImage')->name('capture.image');
    Route::post('imagecustomerdelete','App\Http\Controllers\DropzoneController@deleteCustomerImage')->name('delete.customer.image');
    Route::post('imagedeletepost','App\Http\Controllers\DropzoneController@deleteImageFromPost')->name('delete.post.image');
    Route::post('addImageById','App\Http\Controllers\ProductsController@addImageById')->name('add.image.by.id');
    Route::get('ajaxproducts','App\Http\Controllers\ProductsController@ajaxProducts')->name('ajax.products');
    Route::get('ajaxestimatedproducts','App\Http\Controllers\ProductsController@ajaxEstimatedProducts')->name('estimated.products');
    Route::get('ajaxsavecustomer','App\Http\Controllers\OrdersController@ajaxSaveCustomer')->name('save.customer');
    Route::get('ajaxgetproduct','App\Http\Controllers\ProductsController@ajaxgetProduct')->name('ajax.product');
    Route::get('getrelatedproducts','App\Http\Controllers\ProductsController@getRelatedProducts')->name('related.products');
    Route::get('ajaxcustomer','App\Http\Controllers\CustomersController@ajaxCustomer')->name('get.customer.byID');
    Route::get('ajaxgetcustomer','App\Http\Controllers\CustomersController@ajaxgetCustomer')->name('get.customer.info');
    Route::get('ajaxsupplier','App\Http\Controllers\SuppliersController@ajaxSupplier')->name('supplier');
    Route::get('ajaxgetsupplier','App\Http\Controllers\SuppliersController@ajaxgetSupplier')->name('get.supplier');
    Route::get('ajaxreturnitem','App\Http\Controllers\ReturnsController@ajaxReturnItem')->name('return.item');
    Route::get('ajaxcustomers','App\Http\Controllers\CustomersController@ajaxCustomers')->name('get.customers');
    Route::get('ajaxreturnall','App\Http\Controllers\ReturnsController@ajaxReturnAll')->name('return.all.items');
    Route::get('ajaxorderstatus','App\Http\Controllers\OrdersController@ajaxOrderStatus')->name('ajax.orders');
    Route::get('ajax/discountrules', 'App\Http\Controllers\DiscountRuleController@getAllDiscountRules')->name('discount.rules');
    Route::post('wirediscount', 'App\Http\Controllers\ProductsController@WireDiscount')->name('wire.discount');

    Route::get('admin/products/updatePrice',['as' => 'updatePrice', 'uses' => 'App\Http\Controllers\ProductsController@updatePrice']);
    Route::get('admin/products/updateQty',['as' => 'updateQty', 'uses' => 'App\Http\Controllers\ProductsController@updateQty']);
    Route::get('admin/products/getAll','App\Http\Controllers\ProductsController@getAll')->name("get.all.products");
    Route::get('admin/products/ajaxgetproductsfororder','App\Http\Controllers\ProductsController@ajaxGetProductsForOrder')->name("get.products.for.order");
    Route::get('admin/products/getAllDeleted',['as' => 'getAllDeleted', 'uses' => 'App\Http\Controllers\ProductsController@getAllDeleted']);
    Route::post('products/ajaxOnHold','App\Http\Controllers\ProductsController@ajaxOnHold')->name('set.onhold');

    Route::get('destroyproduct', 'App\Http\Controllers\OrdersController@destroyproduct')->name('destroy.product');
    Route::get('destroyrepairproduct', 'App\Http\Controllers\RepairsController@DestroyRepairProduct')->name('delete.repair.product');
    Route::get('destroyestimatedproduct', 'App\Http\Controllers\EstimatesController@destroyestimatedproduct')->name('delete.estimate.product');
    Route::get('ajaxCreateEmptyRowForInvoice','App\Http\Controllers\ProductsController@ajaxCreateEmptyRowForInvoice')->name('new.invoice.row');
    Route::get('ajaxFindProduct','App\Http\Controllers\ProductsController@ajaxFindProduct')->name('find.product');
    Route::get('repairstatus','App\Http\Controllers\ProductsController@checkRepairStatus')->name('repair.status');
    Route::post('updaterepair','App\Http\Controllers\ProductsController@UpdateRepair')->name('repair.update');

    // Customers Controller
    Route::resource('customers', 'App\Http\Controllers\CustomersController');
    Route::get('combinecustomers', 'App\Http\Controllers\CustomersController@combineCustomers')->name('combine.duplicate.customers');
    Route::get('customers/{id}/destroy', 'App\Http\Controllers\CustomersController@destroy');

    Route::resource('estimates', 'App\Http\Controllers\EstimatesController');
    Route::get('estimates/{id}/destroy', 'App\Http\Controllers\EstimatesController@destroy');
    Route::post('estimates/store', 'App\Http\Controllers\OrdersController@store')->name('estimateorder.store');
    Route::get('estimates/print/{id}', 'App\Http\Controllers\EstimatesController@print');
    Route::get('estimates/print/commercial/{id}', 'App\Http\Controllers\EstimatesController@print');
    Route::get('/orders/print/{id}', Orders::class . '@print')->name('print.order');

    Route::get('estimates/{id}/invoice/create', ['as'=>'invoice.create', 'uses' => 'App\Http\Controllers\EstimatesController@createFromEstimate']);
    Route::post('estimates/{id}/storeinvoicetoorder', ['as'=>'invoice.store', 'uses' => 'App\Http\Controllers\EstimatesController@storeInvoiceFromOrder']);

    Route::resource('inquiries', 'App\Http\Controllers\InquiryController');
    Route::get('inquiries/{id}/destroy', 'App\Http\Controllers\InquiryController@destroy');

    Route::get('ebay/{id}/create', 'App\Http\Controllers\EbayController@create')->name('ebay.create');
    Route::get('ebay/{id}/edit/{ebayItemId?}', 'App\Http\Controllers\EbayController@edit')->name('ebay.edit');
    Route::get('ebay/loadItemSpecificsFromDB', 'App\Http\Controllers\EbayController@loadItemSpecificsFromDB')->name('ebay.item.specifics.from.database');
    Route::get('ebay/templates/template', 'App\Http\Controllers\EbayController@template')->name('ebay.template');
    Route::get('admin/ebay/loadItems',['as' => 'loadItems', 'uses' => 'App\Http\Controllers\EbayController@loadItems']);
    Route::get('admin/ebay/loadTemplate','App\Http\Controllers\EbayController@loadTemplate')->name('ebay.load.template');
    Route::post('admin/ebay/saveSpecificField','App\Http\Controllers\EbayController@saveSpecificField')->name('save.specific.field');
    Route::post('admin/ebay/saveTemplate','App\Http\Controllers\EbayController@saveTemplate')->name('ebay.save.template');
    Route::get('admin/ebay/deleteTemplate','App\Http\Controllers\EbayController@deleteTemplate')->name('ebay.delete.template');
    Route::post('ebay/addItem','App\Http\Controllers\EbayController@addItem')->name('ebay.add.item');
    Route::get('ebay/automateItem','App\Http\Controllers\EbayController@automateAddItem')->name('ebay.automate.item');
    Route::get('admin/ebay/GetItemSpecifics','App\Http\Controllers\EbayController@GetItemSpecifics')->name('ebay.item.specifics');
    Route::get('ebay/getSpecificsFromURL','App\Http\Controllers\EbayController@getSpecificsFromURL')->name('ebay.specifics.from.url');
    Route::get('ebay/getSettings', 'App\Http\Controllers\EbayController@getSettings')->name('ebay.settings');
    Route::get('ebay/get_eBayToken','App\Http\Controllers\EbayController@get_eBayToken')->name('ebay.token');
    Route::get('ebay/accepturl','App\Http\Controllers\EbayController@accepturl')->name('ebay.accept.url');
    Route::get('ebay/fetchToken', 'App\Http\Controllers\EbayController@fetchToken')->name('ebay.fetch.token');
    Route::post('ebay/enditem', 'App\Http\Controllers\EbayController@EndOneItem')->name('ebay.end.item');
    Route::get('ebay/loadOriginalTemplate', 'App\Http\Controllers\EbayController@loadOriginalTemplate')->name('load.original.template');

    Route::post('admin/ebay/saveSettings','App\Http\Controllers\EbayController@saveSettings')->name('ebay.save.settings');
    Route::post('ebay/linkSpecifics','App\Http\Controllers\EbayController@linkSpecifics')->name('ebay.link.specifics');
    Route::get('ebay/getSpecificForCategory', 'App\Http\Controllers\EbayController@getSpecificForCategory')->name('ebay.specific.category');
    Route::get('ebay/itemLoadTemplate', 'App\Http\Controllers\EbayController@itemLoadTemplate')->name('ebay.item.load.template');
    Route::get('admin/ebay/getCategoryNode','App\Http\Controllers\EbayController@getCategoryNode')->name('ebay.category.node');
    Route::get('admin/ebay/SetStoreCategories','App\Http\Controllers\EbayController@SetStoreCategories')->name('ebay.set.store.categories');
    Route::get('admin/ebay/getStoreCategories','App\Http\Controllers\EbayController@getStoreCategories')->name('ebay.get.store.categories');
    Route::get('admin/ebay/loadImages','App\Http\Controllers\EbayController@loadImages')->name('ebay.load.images');
    Route::get('admin/ebay/GetCategorySpecifics','App\Http\Controllers\EbayController@GetCategorySpecifics')->name('ebay.get.specific.category');
    Route::get('admin/ebay/deleteImage','App\Http\Controllers\EbayController@deleteImage')->name('ebay.delete.image');
    Route::get('ebay/endlisting', 'App\Http\Controllers\EbayController@ebayEndListings')->name('ebay.end.listings');
    Route::get('ebay/listings', 'App\Http\Controllers\EbayController@ebayListings')->name('ebay.listings');
    Route::get('ebay/relistitem','App\Http\Controllers\EbayController@RelistItem')->name('ebay.relist.item');

    Route::get('reminders/setReadStatus','App\Http\Controllers\RemindersController@setReadStatus')->name('set.read.status');
    Route::get('reminders/loadProperties','App\Http\Controllers\RemindersController@loadProperties');
    Route::get('reminders/loadReminder','App\Http\Controllers\RemindersController@loadReminder')->name('load.reminder');
    Route::resource('reminders', 'App\Http\Controllers\RemindersController');
    Route::get('reminders/{id}/destroy', 'App\Http\Controllers\RemindersController@destroy');

    Route::get('orders/getContact24', 'App\Http\Controllers\OrdersController@getContact24');
    Route::get('orders/tracking','App\Http\Controllers\OrdersController@tracking')->name('tracking');
    Route::get('orders/addressfromzip','App\Http\Controllers\OrdersController@addressFromZip')->name('address.from.zip');
    Route::get('orders/pinterest','App\Http\Controllers\OrdersController@pinterest');
    Route::get('orders/facebook','App\Http\Controllers\OrdersController@facebook');

    // Route::resource('orders', 'App\Http\Controllers\OrdersController');

    Route::get('orders/{id}/destroy', 'App\Http\Controllers\OrdersController@destroy')->name('delete.order');
    Route::get('orders/{id}/print/{output?}', 'App\Http\Controllers\OrdersController@print')->name('print.order');
    Route::get('orders/{id}/printmulti', 'App\Http\Controllers\OrdersController@printmulti')->name('print.multi.statement');
    Route::get('orders/{id}/{status}/printstatement', 'App\Http\Controllers\OrdersController@printStatement')->name('print.statement');

    Route::get('payments', 'App\Http\Controllers\PaymentsController@index');
    Route::get('payments/getinvoicepayments', 'App\Http\Controllers\PaymentsController@getInvoicePayments')->name('get.invoice.payments');
    Route::get('orders/{order}/payments/print', 'App\Http\Controllers\PaymentsController@print')->name('payments.print');
    Route::get('orders/{id}/payments/create', 'App\Http\Controllers\PaymentsController@create')->name('payments.create');
    Route::get('orders/{id}/memotransfer', 'App\Http\Controllers\OrdersController@memotransfer')->name('memo.transfer');
    Route::patch('orders/{id}/memotransfer/update', 'App\Http\Controllers\OrdersController@memoStore')->name('memotransfer.update');

    Route::post('payments/store', 'App\Http\Controllers\PaymentsController@store')->name('payments.store');
    Route::get('payments/{id}/edit', 'App\Http\Controllers\PaymentsController@edit')->name('payments.edit');

    Route::get('payments/{id}/show', 'App\Http\Controllers\PaymentsController@show');
    Route::patch('payments/{id}/update', ['as'=>'payments.update', 'uses' => 'App\Http\Controllers\PaymentsController@update']);
    Route::get('orders/{id}/payments/{payment}/destroy', 'App\Http\Controllers\PaymentsController@destroy');

    Route::get('orders/{id}/returns/create', 'App\Http\Controllers\ReturnsController@create')->name('returns.create');
    Route::get('users/{id}/destroy', 'App\Http\Controllers\UsersController@destroy');

    Route::get('reports/print/paidwithproducts/{param?}', 'App\Http\Controllers\ReportsController@printSalesWithProducts')->name('report.product.sales');
    Route::get('reports/print/{param}', 'App\Http\Controllers\ReportsController@printSales')->name('report.by.sales');
    Route::get('reports/printmemos', 'App\Http\Controllers\ReportsController@printMemos')->name('report.memo');;
    Route::get('reports/by/{criteria}', 'App\Http\Controllers\ReportsController@byCriteria')->name('report.by.criteria');
    Route::get('reports/byproduct', 'App\Http\Controllers\ReportsController@byProduct')->name('report.by.product');
    Route::get('reports/bycompany', 'App\Http\Controllers\ReportsController@byCompany')->name('report.by.company');
    Route::get('reports/bypaid', 'App\Http\Controllers\ReportsController@byPaid')->name('report.by.paid');
    Route::get('reports/bysupplier', 'App\Http\Controllers\ReportsController@bySupplier')->name('report.by.supplier');

    Route::post('theshow/ajaxstore', 'App\Http\Controllers\TheshowController@ajaxStore')->name('show.save');
    Route::post('theshow/ajaxupdate', 'App\Http\Controllers\TheshowController@ajaxUpdate')->name('show.update');
    Route::post('theshow/ajaxremoveproduct', 'App\Http\Controllers\TheshowController@ajaxRemoveProduct')->name('show.delete');
    Route::get('theshow', 'App\Http\Controllers\TheshowController@index');
    Route::get('theshow/print', 'App\Http\Controllers\TheshowController@print')->name('show.print');
    Route::get('/the-show/print', CTheShow::class . '@print')->name('print.theshow');

    Route::get('theshow/ajaxgetproduct', 'App\Http\Controllers\TheshowController@ajaxgetProduct')->name('show.add');

    Route::post('inventory/ajaxstore', 'App\Http\Controllers\InventoryController@ajaxStore')->name('inventory.save');
    Route::post('inventory/ajaxupdate', 'App\Http\Controllers\InventoryController@ajaxUpdate')->name('inventory.update');
    Route::post('inventory/ajaxremoveproduct', 'App\Http\Controllers\InventoryController@ajaxRemoveProduct')->name('inventory.delete');
    Route::get('inventory/print', 'App\Http\Controllers\InventoryController@print')->name('inventory.print');
    Route::get('inventory/refreshinventory', 'App\Http\Controllers\InventoryController@refreshInventory')->name('inventory.refresh');
    Route::get('inventory/ajaxgetproduct', 'App\Http\Controllers\InventoryController@ajaxgetProduct')->name('inventory.product');

    Route::get('import','App\Http\Controllers\ImportController@index');

    // Walmart Controller
    //Route::get('walmart/create/{sku}','App\Http\Controllers\WalmartController@walmart');
    //Route::get('walmart/inventory/{sku}','App\Http\Controllers\WalmartController@updateInventory');
    Route::get('walmart/getactiveitems', 'App\Http\Controllers\WalmartController@getActiveItems')->name('walmart.get.active.items');
    Route::get('walmart/walmartlisting', 'App\Http\Controllers\WalmartController@walmartListing')->name('walmart.listings');
    Route::get('walmart/getAjaxProducts','App\Http\Controllers\WalmartController@getAjaxProducts')->name('walmart.products');
    Route::post('walmart/submitProduct','App\Http\Controllers\WalmartController@submitProduct')->name('submit.product');
    Route::post('walmart/retireProduct','App\Http\Controllers\WalmartController@retireProduct')->name('retire.product');

    // Amazon Controller
    Route::get('amazon/{id}/create','App\Http\Controllers\AmazonController@create');
    Route::get('amazon/displayListItems',['as'=>'displayListItems', 'uses'=>'App\Http\Controllers\AmazonController@displayListItems']);
    Route::get('amazon/getAjaxAmazonProducts',['as' => 'getAjaxAmazonProducts', 'uses' => 'App\Http\Controllers\AmazonController@getAjaxAmazonProducts']);
    Route::get('amazon/getAjaxListing',['as' => 'getAjaxListing', 'uses' => 'App\Http\Controllers\AmazonController@getAjaxListing']);
    Route::get('amazon/amazonendlisting', 'App\Http\Controllers\AmazonController@amazonEndListings');
    Route::get('amazon/amazonlistings', 'App\Http\Controllers\AmazonController@amazonListings');
    Route::get('amazon/getSimilarProductByName', 'App\Http\Controllers\AmazonController@getSimilarProductByName');
    Route::get('amazon/{$submissionId}/verify', 'App\Http\Controllers\AmazonController@verify');
    Route::post('amazon/submitProduct',['as'=>'amazon.submitProduct', 'uses' => 'App\Http\Controllers\AmazonController@submitProduct']);
    Route::post('amazon/removeProduct',['as'=>'amazon.removeProduct', 'uses' => 'App\Http\Controllers\AmazonController@removeProduct']);

    Route::get('repairs/{id}/{customer}/print', 'App\Http\Controllers\RepairsController@print')->name('repair.print');
    Route::get('repairs/{id}/destroy', 'App\Http\Controllers\RepairsController@destroy')->name('repair.delete');

    Route::post('import/upload',['as'=>'import.upload', 'uses' => 'App\Http\Controllers\ImportController@upload']);
    Route::resources(
        [
            'discountrules' =>'App\Http\Controllers\DiscountRuleController',
            'audits' => 'App\Http\Controllers\AuditsController',
            'roles' => 'App\Http\Controllers\RolesController',
            'permissions' => 'App\Http\Controllers\PermissionsController',
            'users' => 'App\Http\Controllers\UsersController',
            'returns' => 'App\Http\Controllers\ReturnsController',
            'repairs' => 'App\Http\Controllers\RepairsController',
            'reports' => 'App\Http\Controllers\ReportsController',
            'announcements' => 'App\Http\Controllers\AnnouncementsController',
            'inventory' => 'App\Http\Controllers\InventoryController',
            'posts' => 'App\Http\Controllers\PostsController',
            'suppliers' => 'App\Http\Controllers\SuppliersController',
            'amazon' => 'App\Http\Controllers\AmazonController',
            'ebay' => 'App\Http\Controllers\EbayController',
            'walmart' => 'App\Http\Controllers\WalmartController',
            'massmail' => 'App\Http\Controllers\MailMassController',
            'rates' => 'App\Http\Controllers\ExchangeRateController',
        ]
    );

});


// Route::get('chrono24/watches/{slug?}', function($slug='')
// {
//     $categories = App\Category::whereHas('products', function ($query){
//         $query->where('p_qty','>',0);
//     })->orderByRaw('category_name="Rolex" desc, category_name')->get();

//     $product = \App\Product::with('images')->where('slug',$slug)
//         ->where('p_status','<>',3)
//         ->first();

//     if ($product) {
//         $paths = explode('/',url()->current());
//         foreach ($paths as $path) {
//             if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
//                 $routes[] = $path;
//         }

//         return View::make('product-details',['categories' => $categories,'product'=>$product,'inludefilter'=>false,'routes'=>$routes,'lpath'=>'withmarkups']);
//     } else
//         abort(404, 'Unauthorized action.');
// });


// ===============================================
// 404 ===========================================
// ===============================================

//App::missing(function($exception)
//{

    // shows an error page (app/views/error.blade.php)
    // returns a page not found error
  //  return Response::view('error', [), 404);
//});

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
