
@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
@section('title', isset($title) ? $title : 'Brand new, pre-owned, luxury, causal, and dress watches for men and women')

<div class="row">
@if ($discount)
    <?php $productDiscount=unserialize($discount->product); ?>
@endif
</div>

<div class="row">
    <div id="loading"></div>
    <div class="filter-container">
        <div class="ais-InstantSearch">
            <div class="left-panel">
                <div id="clear-refinements"></div>

                <div class="refinement">
                    <h5>Brands</h5>
                    <div id="brand-list"></div>
                </div>

                <div class="refinement">
                    <h5>Case Size</h5>
                    <div id="case_size"></div>
                </div>

                <div class="refinement">
                    <h5>Condtion</h5>
                    <div id="condition"></div>
                </div>

                <div class="refinement">
                    <h5>Gender</h5>
                    <div id="gender"></div>
                </div>
            </div>

            <div class="right-panel">
                @if ($discount)
                @include ('announcement',['discount'=>$discount])
                @endif
                <div id="searchbox"></div>
                <div id="hits"></div>
                <div id="pagination"></div>
            </div>
        </div>
        <!-- <ul>
            <li>
                <a href="#">Condition</a>
                <div class="filters">
                    <ul class="filter-drop">
                        <li><a href="#">New/Unworn</a></li>
                        <li><a href="#">Pre-owned</a></li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#">Gender</a>
                <div class="filters">
                    <ul class="filter-drop">
                        <li><a href="/search?p=womens">Women's</a></li>
                        <li><a href="/search?p=mens">Men's</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="&brand">Brand</a></li>
            <li><a href="&size">Size</a></li>
            <li><a href="&dial_color">Dial Color</a></li>
            <li><a href="&case_material">Case Material</a></li>
        </ul> -->
   </div>
</div>

<!-- <div class="row" id="product-items"> -->
{{-- @if (!$products->isEmpty()) --}}
    <?php //$productDiscount = array() ?>
    {{-- @include ('pagination_child',['productDiscount'=>$productDiscount]) --}}
    {{-- @else --}}
        <!-- <div style="text-align:center">No products found in this category</div> -->
        {{--@endif --}}
<!-- </div>  -->
    
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
                data: {'status': 'ajax'},
                cache: false,
                success: function(data){
                    $('#product-items').html(data);
                },
                error: function(data) {
                }
            })
        }

        $(window).on("popstate", function(e) {
            var url = $('.previouspage').attr('href');
            history.pushState(history.state, null, url);

            if (url === undefined) return
            followUrl(url);
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
