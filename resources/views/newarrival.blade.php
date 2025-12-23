@extends('layouts.default1')

@section('title', 'New Arrival')

@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section('title', isset($title) ? $title : 'Brand new, pre-owned, luxury, causal, and dress watches for men and women')

@section ('content')
<div class="row">
@if ($discount)
    <?php $productDiscount=unserialize($discount->product); ?>
    @include ('announcement',['discount'=>$discount])
@endif
</div>

<div id="product-items"> <!-- class="row" id="product-items"> -->
 @if (!$products->isEmpty())
    <?php $productDiscount = array() ?>
     @include ('pagination_child',['productDiscount'=>$productDiscount])
     @else
        <div style="text-align:center">No products found in this category</div>
    @endif
</div> 

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

        $(document).ready( function() {
        var files = new Array();

        $('.toggled-menu').click(function(){
            $('.exo-menu').toggleClass('display');
            
        });
        $('body').on('click', '.pagination a', function(e) {
            
            e.preventDefault();
            
            var url = $(this).attr('href');
            url = removeURLParam(url,'status')
            url = removeURLParam(url,'_')
            history.replaceState({}, '', url);

            followUrl(url)
        })

        function followUrl(url) {
            $.ajax({
                url: url,
                cache: false,
                data: {'status': 'ajax'},
                success: function(data){
                    $('#product-items').html(data);
                },
                error: function(data) {
                }
            })
        }
        
        var relatedSlider = $('#relatedSlider').lightSlider({
            item:5,
            slideMove:1,
            enableTouch:true,
            responsive : [
                {
                    breakpoint:768,
                    settings: {
                        item:3,
                        slideMove:1,
                        slideMargin:6,
                    }
                },
                {
                    breakpoint:480,
                    settings: {
                        item:2,
                        slideMove:1
                    }
                }
            ]
        });

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
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/js/parsley.js"></script>
    <script src="/js/filters/filters.js"></script>
    <script src="/lightslider/js/lightslider.js"></script>
    
@endsection
