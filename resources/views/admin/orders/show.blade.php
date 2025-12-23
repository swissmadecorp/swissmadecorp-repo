@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<div id="pstatus" style="display: none">
    <select class="custom-select form-control" name="p_status">
        <option></option>
        @foreach (orderStatus() as $key => $status)
        <option value="{{ $key }}">{{ $status }}</option>
        @endforeach
    </select>
</div>

<?php $payments_options = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120']; ?>
<p>Purchase Date: <b>{{ $order->created_at->format('m/d/Y') }}</b></p>
<p>Purchased from:  
    <?php if (!empty($order->purchased_from)) { ?>
        <?php if ($order->purchased_from==1) { ?>
            <?php $noImage = '../no-image.jpg'; ?>
            <span style="font-weight: bold">Swiss Made</span>
        <?php } else { ?>
            <?php $noImage = '../no-image-st.jpg'; ?>
            <span style="font-weight: bold">Signature Time</span>
        <?php } ?>
    <?php } ?>
</p>
<div class="form-group row">
    <div class="col-6">
        <label for="po-input" class="col-form-label">PO Number</label>
        <span class="form-control" name="po-input"><?= $order->po ? $order->po : "&nbsp;" ?></span>
    </div>    
    <div class="col-6">
        <label for="payment-input" class="col-form-label">Payment Method</label>
        <span class="form-control">{{ $order->method }}</span>
        
        <label for="payment-input" class="col-form-label">Payment Option</label>
        <span class="form-control">{{ PaymentsOptions()->get($order->payment_options) }}</span>
        
    </div>    
</div>

<div class="row">
    <div class="col-md-6 order-group billing" >
        <div class="group-title">Billing Address</div>
        <div class="p-1">
        @if ($order->b_firstname)
        <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_firstname }}</span>
                
            </div>
        </div>
        @endif

        @if ($order->b_lastname)
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_lastname }}</span>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_company }}</span>
            </div>
        </div>
        @if ($order->b_address1)
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_address1 }}</span>
            </div>
        </div>
        @endif
        @if ($order->b_address2)
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_address2 }}</span>
            </div>
        </div>
        @endif
        @if ($order->b_phone)
        <div class="form-group row">
            <label for="b-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_phone }}</span>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                @inject('countries','App\Libs\Countries')
                <span class="form-control">{{ $countries->getCountry($order->b_country) }}</span>
            </div>
        </div>
        
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getStateFromCountry($order->b_state) }}</span>
            </div>
        </div>
        
        @if ($order->b_city)
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_city }}</span>
            </div>
        </div>
        @endif

        @if ($order->b_zip)
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_zip }}</span>
            </div>
        </div>
        @endif

        @if ($order->email)
        <div class="form-group row">
            <label for="b-email-input" class="col-3 col-form-label">Email</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->email }}</span>
            </div>
        </div>
        @endif
        </div>
    </div>

    <div class="order-group col-md-6 shipping">
        <div class="group-title">Shipping Address</div>
        <div class="p-1">
            @if ($order->s_firstname)
            <div class="form-group row">
                <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
                <div class="col-9 input-group">
                    <span id="s-firstname-input" class="form-control">{{ $order->s_firstname }}</span>
                    
                </div>
            </div>
            @endif
        @if ($order->s_lastname)
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span id="s-lastname-input" class="form-control">{{ $order->s_lastname }}</span>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span id="s-company-input" class="form-control">{{ $order->s_company }}</span>
            </div>
        </div>
        @if ($order->s_address1)
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span id="s-address1-input" class="form-control">{{ $order->s_address1 }}</span>
            </div>
        </div>
        @endif
        @if ($order->s_address2)
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span id="s-address2-input" class="form-control">{{ $order->s_address2 }}</span>
            </div>
        </div>
        @endif
        @if ($order->s_phone)
        <div class="form-group row">
            <label for="s-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span id="s-phone-input" class="form-control">{{ $order->s_phone }}</span>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                <span id="s-country-input" class="form-control">{{ $countries->getCountry($order->s_country) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span id="s-state-input" class="form-control">{{ $countries->getStateFromCountry($order->s_state) }}</span>
            </div>
        </div>
        @if ($order->s_city)
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span id="s-city-input" class="form-control">{{ $order->s_city }}</span>
            </div>
        </div>
        @endif

        @if ($order->s_zip)
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span id="s-zip-input" class="form-control">{{ $order->s_zip }}</span>
            </div>
        </div>
        @endif
        </div>
    </div>
</div>

<div class='table-responsive'>
<table class="table order-products">
    <thead>
        <tr>
        <th>Image</th>
        <th>ID</th>
        <th>Product Name</th>
        <th>Serial#</th>
        <th>Qty</th>
        <th>Cost</th>
        <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php $is_partial = 0;$partial = 0; $total = 0;?>
        @foreach ($order->products as $product)
        <?php 
            $total = $order->total;
            $is_partial = count($order->payments);
            $partial = 0;

            foreach ($order->payments as $payment) {
                $total -= $payment->amount;
                $partial += $payment->amount;
            }

            $p_image = $product->images->toArray();
            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';;
        ?>
        <tr>
            <td><img style="width: 70px" src="{{ '/images/thumbs/'.$image }}" />
                <input type="hidden" name="product_id" value="{{$product->id}}">
            </td>
            <td>{{$product->id}}</td>
            <td><a href="/admin/products/{{$product->id}}/{{$product->group_id==2 ? 'bezeledit' : 'edit'}}">{{ !$product->pivot->product_name ? $product->title : $product->pivot->product_name}}</a></td>
            <td>{{ $product->pivot->serial }} </td>
            <td>{{ $product->pivot->qty }} </td>
            <td style="text-align: right; width: 92px" ><span class="hide">{{ $product->pivot->cost>0 ? number_format($product->pivot->cost,2) : number_format($product->p_price,2) }}</span></td>
            <td style="text-align: right">{{ number_format($product->pivot->price*$product->pivot->qty,2) }} </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right" colspan="6"><b>Total Profit</b></td>
            <td style="text-align: right"><span class="hide">{{ number_format($total - $order->products->sum('p_price'),2) }}</span></td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="6"><b>Sub Total</b></td>
            <td style="text-align: right">{{ number_format($order->subtotal,2) }}</td>
        </tr>
        @if ($order->customers()->first()->cgroup==1 && $order->taxable>0)
        <tr>
            <td style="text-align: left;" colspan="4">
                <b>Tax Exempt</b>
                <input type="checkbox" name="taxexempt" disabled value="{{$order->taxexempt}}" <?= $order->taxexempt ? 'checked' : '' ?> class="checkbox" style="width: 30px">
            </td>
            <td style="text-align: right" colspan="2"><b>Tax</b></td>
            <td style="text-align: right">{{ number_format($order->taxable,3) }}</td>
        </tr>
        @endif
        @if ($is_partial && $partial < $order->total)
        <tr>
            <td style="text-align: right" colspan="6"><b>Partial Payment</b></td>
            <td style="text-align: right;color: red">-{{ number_format($partial,2) }}</td>
        </tr>
        @elseif ($partial == $order->total)
        <tr>
            <td style="text-align: right" colspan="6"><b>Payment</b></td>
            <td style="text-align: right;color: red">-{{ number_format($partial,2) }}</td>
        </tr>
        @endif
        @if ($order->freight>0)
        <tr>
            <td style="text-align: right" colspan="6"><b>Freight</b></td>
            <td style="text-align: right">{{ number_format($order->freight,2) }}</td>
        </tr>
        @endif
        @if ($order->discount > 0)
        <tr>
            <td style="text-align: right" colspan="6"><b>Discount</b></td>
            <td style="text-align: right">${{number_format($order->discount,2)}}</td>
        </tr>
        @endif
        @if ($total > 0)
        <tr>
            <td style="text-align: right" colspan="6"><b>Grand Total</b></td>
            <td style="text-align: right">${{number_format($total,2)}}</td>
        </tr>
        @endif
    </tfoot>
</table>
</div>

@if ($order->tracking)
<div class="form-group row">
    <div class="col-12">
        <label for="tracking-input" class="col-form-label">Tracking No.</label>
        <div id="tracking-input" class="form-control">
            @if (strpos($order->tracking,',') > 0)
                @php
                    $trackings = explode(",",$order->tracking);
                @endphp

                @foreach ($trackings as $tracking)
                <a target="_blank" href="https://www.fedex.com/fedextrack/?trknbr={{ $tracking }}" class="btn btn-primary btn-sm">{{ $tracking }}</a></button>
                @endforeach
            @else 
            <a target="_blank" href="https://www.fedex.com/fedextrack/?trknbr={{ $order->tracking }}" class="btn btn-primary btn-sm">{{ $order->tracking }}</a></button>
            @endif
        </div>
    </div>    
</div>
@endif

@if ($order->chrono_order_id)
<div class="form-group row">
    <div class="col-12">
        <label for="chrono-order-id-input" class="col-form-label">Chrono24 Order Id</label>
        <div id="tracking-input" class="form-control">
            <a target="_blank" href="https://www.chrono24.com/dealer-area/checkout/checkout-detail.htm?checkoutId={{ $order->chrono_order_id }}&processStep=Shipping" class="btn btn-primary btn-sm">{{ $order->chrono_order_id }}</a></button>
        </div>
    </div>    
</div>
@endif

@if ($order->comments)
<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Comments</label>
        <span id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{!! nl2br ($order->comments) !!}</span>
    </div>    
</div>
@endif

@if ($order->code)
<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Order Status</label>
        <span id="comments-input" class="form-control">{!! nl2br ($order->cc_status) !!}</span>
    </div>    
</div>
@endif

<div class="alert-info clearfix">
    <div class="float-right mr-3">
    <a href="{{ URL::to('admin/orders/'.$order->id.'/print') }}" class="btn btn-primary print">Print</a>
    </div>
    @if ($order->s_country != 231)
    <div class="float-right mr-3">
    <a href="{{ URL::to('admin/orders/'.$order->id.'/print/commercial') }}" class="btn btn-primary print_commercial">Print Commercial</a>
    </div>
    @endif

    @if ($order->method != 'On Memo' && $order->method != 'On Hold')
    <div class="float-right mr-3">
    <a href="{{ route('payments.create', array('id' => $order->id)) }}" class="btn btn-primary">Go to Payments</a>
    </div>
    @endif
    <div class="float-right mr-3">
    <a href="{{ URL::to('admin/orders/'.$order->id.'/returns/create') }}" class="btn btn-primary">Go to Returns</a>
    </div>

    @if ($order->method == 'On Memo' || $order->method == 'On Hold')
    <div class="float-right mr-3">
        <a href="{{$order->id}}/memotransfer" class="btn btn-primary">Transfer to Order</a>
    </div>
    @endif

    <div class="float-right mr-3">
    <a href="{{ URL::to('admin/orders/'.$order->id.'/edit') }}" class="btn btn-danger pull-left">Edit</a>
    </div>
</div>

@endsection

@section ('footer')
<script src="{{ asset('/fancybox/jquery.fancybox.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {

        <?php if (!isMobile()) {?>
            $(document).on('mouseenter', 'span.hide', function () {
                $(this).css('opacity',1)
            }).on('mouseleave', 'span.hide', function () {
                $(this).css('opacity',0)
            })
        <?php } ?>

        $('.print').click( function(e) {
            e.preventDefault();
            var printWindow = window.open("/admin/orders/"+{{$order->id}}+"/print", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=0,left=500,width=600,height=800");
            var printAndClose = function() {
                if (printWindow.document.readyState == 'complete') {
                    printWindow.print();
                    clearInterval(sched);
                }
            }
            var sched = setInterval(printAndClose, 1000);
        })

        $('.page-header').click(function() {
            $('#pstatus').show();
        })

        $('.custom-select').change(function() {
            if (!$('.custom-select option:selected').text()) return
            $.ajax({
                type: "PUT",
                url: "{{route('update.order.status')}}",
                data: { 
                    orderid: {{$order->id}},
                    status: $('.custom-select option:selected').val()
                },
                success: function (result) {
                    location.reload();
                }
            })
        })
    })    
</script>
@endsection