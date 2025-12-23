
@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

@section ("canonicallink")
    <link rel="canonical" href="{{config('app.url').'/watch-products' }}" />
@endsection

@section('title', isset($title) ? $title : 'Brand new, pre-owned, luxury, causal, and dress watches for men and women')

@if ($discount)
@include ('announcement',['discount'=>$discount])
@endif

<div class="page_title catalog_highlight" style="<?php !isset($categoryimage) ? '' : "display: none" ?>">
    @if (isset($categoryimage) && $categoryimage->image_name)
    <div class="row">
        <div class="col-md-4 photo">
            <img src="/images/categories/{{$categoryimage->image_name}}" />
        </div>
        <div class="col-md-8">
            <h2>{{strtoupper($categoryimage->category_title)}}</h2>
            <p>{{$products->total()}} MATCHES FOUND</p>
            <div class="description">{{ $categoryimage->category_description}}</div>
        </div>
    </div>
    @endif
</div>

<div id="product-items">
     @if (!$products->isEmpty())
         @include ('pagination_child')
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
    </script>
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/js/parsley.js"></script>
    <script src="/js/filters/filters.js"></script>

@endsection
@section ("jquery")
<script>
    
    $(document).ready( function() {
        var files = new Array();

        function QueryString (criteria, paramsArray) {
            let params = {};

            for (let i = 0; i < criteria.length; ++i)
            {
                let param = criteria[i]
                    .split('=', 2);

                if (param.length !== 2)
                    continue;

                params[param[0]] = decodeURIComponent(param[1].replace(/\+/g, " "));
            }

            return params;
        }

        $('body').on('click', '.pagination a', function(e) {
            
            e.preventDefault();
            $('html,body').animate({ scrollTop: 0 }, 'slow');
            //var url = $(this).attr('href');

            splt = $(this).attr('href').substr($(this).attr('href').indexOf('page')).split('&');
            page = QueryString (splt, 'page')

            const url = new URL(window.location);
            url.searchParams.set('page', page["page"]);
            debugger;
            history.pushState({}, '', url);

            //history.pushState(location.href, '', url);
            //url = removeURLParam(url,'status')
            //url = removeURLParam(url,'_')
            // url = removeURLParam(url,'is_filter')
            
            followUrl(url)
        })

        function followUrl(url) {
            $.ajax({
                url: url,
                cache: false,
                success: function(data){
                    $('#product-items').html(data);
                },
                error: function(data) {
                }
            })
        }

        function startSearch(txt) {
            $.ajax({
                url: "{{route('search')}}",
                data: {p: txt},
                cache: false,
                success: function(data){
                    $('#product-items').html(data);
                    if (txt == '')
                        $('.searchcontainer button').hide()

                    return false
                },
                error: function(data) {
                }
            })
        }

        $('.searchcontainer button').click( function() {
            history.pushState(history.state.prev, null, "/watches");
            $('.searchbar').val('');
            startSearch('');
            $('.searchbar').focus();
        })

        var timeoutId = 0;
        $('.searchbar').keyup(function(e) {
            var txt = $('.searchbar').val();

            if (e.keyCode != 37 && e.keyCode != 38
                    && e.keyCode != 40 && e.keyCode != 39 
                    && e.keyCode != 46 && e.keyCode != 32
                    && e.keyCode != 36 && e.keyCode != 35 ) {

                clearTimeout(timeoutId); // doesn't matter if it's 0
                timeoutId = setTimeout(() => {
                    if (!$('.searchcontainer button').is(':visible'))
                        $('.searchcontainer button').show()
                    
                    if (!txt) 
                        history.pushState({prev: '/watches'}, null, "/watches");
                    else
                        history.pushState({prev: '/watches'}, null, "/search?p="+txt);

                    startSearch(txt)
                }, 500);
            }
            
        })

        var pathName = document.location.pathname;
        window.onbeforeunload = function () {
            var scrollPosition = 0;
            sessionStorage.setItem("scrollPosition_" + pathName, scrollPosition.toString());
        }
        if (sessionStorage["scrollPosition_" + pathName]) {
            //$(document).scrollTop(sessionStorage.getItem("scrollPosition_" + pathName));
            $('html,body').animate({ scrollTop: 0 }, 'slow');
        }

        window.addEventListener('popstate', (event) => {
            location.reload()
//   console.log(`location: ${document.location}, state: ${JSON.stringify(event.state)}`);
        });

        // $(window).on("popstate", function(e) {
        //     var url = $('.previouspage').attr('href');
        //     debugger;
        //     history.pushState(history.state.prev, null, url);

        //     if (url === undefined) return
        //     followUrl(url);
        // });

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
