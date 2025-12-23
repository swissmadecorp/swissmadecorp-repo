@extends('layouts.default1')

@section('title', $title)

@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<link href="/css/mega-menu.css" rel="stylesheet">
<link href="lightslider/css/lightslider.css" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@endsection

@section ('content')

<!-- <div class="searchcontainer">
    <input class="searchbar" type="text" placeholder="Search for products, categories, ...">
    <button class="btn btn-secondary btn-sm" style="display: none">
        <i class="fas fa-times"></i>
    </button>
</div> -->

<div class="container home-page">
    <div class="row d-flex justify-content-center">
        <div class="col-md-12 position-relative home-banner">
            <div style="background: rgba(241, 212, 193,.3);"><span>New Arrivals</span></div>
            <a href="newarrival" class="position-absolute">View All <i class="fas fa-arrow-circle-right"></i></a>
            <img src="/assets/new_arrivals.jpg" style="width: 100%" alt="New Watch Arrivals">
        </div>
    
        <div class="home-watches pt-3">
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li style='font-size: 25px'><a href="/search?p=men's">Men's Watches</a></li>
                        <li style='font-size: 18px'><a href="/search?p=men's%20rolex">Rolex</a></li>
                        <li style='font-size: 18px'><a href="/search?p=men's%20breitling">Breitling</a></li>
                        <li style='font-size: 18px'><a href="/search?p=men's%20franck%20muller">Franck Muller</a></li>
                        <li style='font-size: 18px'><a href="/search?p=men's%20patek%philippe">Patek Philippe</a></li>
                        <li style='font-size: 18px'><a href="/search?p=men's%20Jaeger-LeCoultre">Jaeger-LeCoultre</a></li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <ul>
                        <li style='font-size: 25px'><a href="/search?p=women">Ladie's Watches</a></li>
                        <li style='font-size: 18px'><a href="/search?p=rolex%20women">Rolex</a></li>
                        <li style='font-size: 18px'><a href="/search?p=women%20barthelay">Barthelay</a></li>
                        <li style='font-size: 18px'><a href="/search?p=women%20chopard">Chopard</a></li>
                        <li style='font-size: 18px'><a href="/search?p=women%27s%20chanel">Chanel</a></li>
                        <li style='font-size: 18px'><a href="/search?p=women%27s%20cartier">Cartier</a></li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="col-md-12  pt-3 position-relative home-banner">
            <div style="background: rgba(0,25,105,.4)"><span style="color: #b9b9b9">Pre-owned</span></div>
            <a href="/search?p=pre-owned" class="position-absolute" style="color: #e2e2e2">Pre-owned Watches <i class="fas fa-arrow-circle-right"></i></a>
            <img src="/assets/romain-jerome-sale.jpg" style="width: 100%" alt="Pre-Owned Watches">
        </div>

        <!-- <div class="pt-3 col-md-12" style="z-index: 0">
            <div class="row specials">
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-3">
                <a href="/search/59/romain-jerome-brand-new-certified-pre-owned-watches-new-york?products%5BrefinementList%5D%5Bcategory%5D%5B0%5D=Romain%20Jerome" class="lazy atmiddle long" style="height: 293px;display: block;background-image: url('/images/sale/romain_jerome.jpg');"><span>Sale<br>
                    Romain Jerome</span><i class="icon-right-dir after"></i></a>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-3">
                <a href="discount-fine-watches.html" class="lazy atmiddle long" style="height: 293px;display: block;background-image: url(&quot;https://sep.yimg.com/ty/cdn/movadobaby/flash-12132020-main-links.jpg&quot;);"><span>Watch Sale<br>Up to 85% Off</span><i class="icon-right-dir after"></i></a>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-3">
                <a href="authenticwatches-specials.html" class="lazy atmiddle long" style="height: 293px; display: block; background-image: url(&quot;https://sep.yimg.com/ty/cdn/movadobaby/holiday-main-links-10.jpg&quot;);"><span>Holiday <br>Sale </span><i class="icon-right-dir after"></i></a>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-3">
                <a href="specials.html" class="lazy atmiddle long" style="height: 293px; display: block; background-image: url(&quot;https://sep.yimg.com/ty/cdn/movadobaby/in-stock-11022020-main-links.jpg&quot;);"><span>In Stock <br>
                    Deals</span><i class="icon-right-dir after"></i></a>
                </div>
            </div>
        </div> -->

        
    
    </div>
    

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
        

        // $('.searchbar').keyup(function(e) {
        //     var txt = $('.searchbar').val();

        //     if (e.keyCode == 13) {
        //         window.location = "/search?p="+txt;
        //     }
            
        // })

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
