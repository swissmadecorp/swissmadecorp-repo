<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use DB;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {

        // $orders = Order::selectRaw('date_format(created_at,"%Y-%m-%d") date, sum(subtotal) total')
        // ->groupBy('date')
        // ->get();

        $orders = Order::selectRaw('created_at as date, sum(subtotal) total')
            ->whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])
            
        ->groupBy('date')
        ->get();

        $from = date('Y-m-d',strtotime("-30 days"));
        
        $invoices = Order::where("status",0)
            // ->withTrashed()
            ->where('created_at', '<=', $from)
            ->get();
        
        // $products = DB::table('order_product')->selectRaw("count(category_name) c,product_id,category_name,p_model,count(qty) q")
        // ->join('products','id','=','product_id')
        // ->join('categories','categories.id','=','category_id')
        // ->groupBy('category_name')
        // ->get();

        return view('admin.dashboard',['pagename'=>'Dashboard', 'orders'=>$orders,'invoices'=>$invoices]);

    }
}
