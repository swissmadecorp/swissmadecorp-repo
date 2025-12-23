@extends ("layouts.default-chrono24")

@section ('header')
<link href="{{ asset('/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

@section('title', isset($title) ? $title : 'Brand new, pre-owned, luxury, causal, and dress watches for men and women')

@section("content")

@if (!$products->isEmpty())
<div class="m_bottom_13 mobile-filter-menu">
    <div class="mh-head">
        <a href="#my-menu"><span></span></a>
        <input type="button" class="btn btn-success btn-sm" id="my-button" value="filters" />
    </div>
    
    
        @include ("layouts.sidebar-mobile")
    
</div>

@include('toolbar')
@endif
<div class="row">
    @if (!$products->isEmpty())
    <?php $i=0 ?>
    @foreach ($products as $product)
    <div class="col-sm-6 col-md-4 col-lg-4 col-xl-2 custom-item-width">
        <div class="thumbnail" data-id="{{ $product->id }}">
            
            @if (strpos(url()->current(),'chrono24')>0)
                <?php $path = 'chrono24/watches/'.$product->slug ?>
            @elseif ($product->p_condition==3) 
                <?php $path = 'certified-pre-owned-watches/'.$product->slug ?>
            @else
                <?php $path = $product->slug ?>
            @endif

            @if (@count($product->images))
            <?php $image = $product->images->first() ?>
            @if (!file_exists(base_path(). '/public/images/thumbs/' . $image->location) || strpos($image->location,'snapshot') > 0)
            
                <a href="/{{$path}}"><img style="width: 225px" src="/images/no-image.jpg" alt=""></a>
                @else
                <a href="/{{$path}}"><img style="width: 225px" title="{{ $product->title }}" alt="{{ $product->title }}" src="{{ URL::to('/images/thumbs') .  '/' . $image->location }}" alt=""></a>
                @endif
            @else
                <a href="/{{$path}}"><img style="width: 225px" src="/images/no-image.jpg" alt=""></a>
            @endif
            
            <?php 
                if ($product->p_qty==0) {
                    $status = 'SOLD';
                    $color = "red;font-weight:bold";
                } elseif ($product->p_status==3) {
                    $status = 'Available';
                    $color = 'green';
                } else {
                    $status = Status()->get($product->p_status );
                    $color = ($product->p_status ==0 ? 'green' : '#000;font-weight:600');
                }
            ?>
            <span class="sticker-wrapper top-left">Status: <span class="sticker new" style="color:{{$color}}">{{ $status }}</span></span>
            <hr>
            <button class="btn btn-secondary btn-sm" onclick="window.location.href='/{{$path}}'" title="View details about {{ $product->title }}" aria-pressed="false" autocomplete="off" style="width: 100%">View Details</button>
            
            <div class="caption">
                <?php if (isset($product->categories->category_name)) { ?>
                    <a href="/{{$path}}">{{$product->title}}</a>
                <?php } else { ?>
                    <a href="/{{$path}}">{{$product->p_model . ' ' . $product->p_reference}}</a>
                <?php } ?>
            </div>
            <table class="table" style="margin-bottom: .1rem">
                <tr>
                    <th>Price:</th>
                    <td style="text-align: right">@if (isset($lpath) && $lpath=="withmarkups")
                        @if ($product->p_price3P>0)
                                        <span class="price">${{ number_format($product->p_price3P,2) }}</span>
                                    @else <span class="price">Call Us</span>
                                    @endif
                                @else
                                    @if ($product->p_newprice>0)
                                        @if (Auth::guard('customer')->check())
                                        <span class="price">${{ number_format($product->p_newprice,2) }}</span>
                                        @else
                                            @php $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin())) @endphp
                                            <span class="price">${{ number_format($webprice,2) }}</span>
                                        @endif
                                    @else
                                        <span class="price" style="color:red">Call Us</span>
                                    @endif
                                @endif</td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
    @else
        <div style="text-align:center">No products found in this category</div>
    @endif
</div>
@if (!$products->isEmpty())
@include('toolbar')
@endif
    
    
@endsection

@section ("canonicallink")
    @if (isset($product) && isset($product->categories->category_name))
        <link rel="canonical" href="{{ config('app.url').$product->categories->id }}" />
    @endif
@endsection


@section ('footer')
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="{{ asset('fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('js/parsley.js') }}"></script>
    <script src="{{ asset(production().'js/filters/filters.js') }}"></script>
@endsection
@section ("jquery")
<script>
    $(document).ready( function() {
        var files = new Array();

        $('.nextpage1').click( function() {
            e.preventDefault();
            $.ajax ( {
                    type: 'post',
                    dataType: 'json',
                    url: $('.inquiry-form').attr('action'),
                    
                    success: function(response) {
                        // if ($.isEmptyObject(response.success)) {
                        if (response.error=='success') {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>Your inquiry has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                                type: 'html',
                            });
                        } else {
                            alert (response.error)
                        }
                        
                    }
                })
        })

        Parsley.on('form:submit', function(event) {
            if (event.element.className == "sell-watch-form") {
                return false;
            } else {
                $.ajax ( {
                    type: 'post',
                    dataType: 'json',
                    url: $('.inquiry-form').attr('action'),
                    data: {inquiry: $('.inquiry-form').serialize(),_token: "{{csrf_token()}}"},
                    success: function(response) {
                        // if ($.isEmptyObject(response.success)) {
                        if (response.error=='success') {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>Your inquiry has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                                type: 'html',
                            });
                        } else {
                            alert (response.error)
                        }
                        
                    }
                })
                return false;
            }
        })
    })
</script>
@endsection