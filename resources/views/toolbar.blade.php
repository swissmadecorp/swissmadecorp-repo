@if (isset($products))
<div class="container-fluid toolbar-container">    
        <div class="row">
            @if ($products->perpage() > count($products))
                <?php $page = count($products) ?>
            @else
                <?php $page = $products->perpage() ?>
            @endif

            <div class="col-sm-6 col-lg-6 col-md-6 center-block" style="line-height: 45px;">
                Results {{ $products->currentPage(). ' - ' . $page .' of ' . $products->total()}}
            </div>
            
            <div class="col-sm-6 col-lg-6 col-md-6">
            @include('pagination-ajax', ['paginator' => $products])
            </div>
        </div>
</div>
@endif