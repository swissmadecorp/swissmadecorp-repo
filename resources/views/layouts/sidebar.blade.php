@if (isMobile()==false)
<div class="list-group">
    
    <div class="list-group-list">
    <ul class="categories">

        @foreach ($categories as $category)
        
            <?php $totalItems = count($category->products->where('p_qty','>',0)) ?>
            @if ($totalItems)
            <?php $cat = URL::to('watches').'/'.$category->id.'/'.strtolower($category->location) ?>
            <?php if (strpos($cat,' ') !==false) {
                $cat = str_replace(' ','-',$cat);
            } ?>

            <li class="ui-menu-item level0">
                <div class="open-children-toggle"></div>
                <a href="<?php echo $cat ?>/" class="level-top">
                    <span>{{ $category->category_name }}</span>
                    <span>{{ ' ('.$totalItems.')' }}</span>
                </a>
            </li>
            @endif
            
        @endforeach
        
    </ul>
    </div>

    @if (isset($products))
    <div id="product-filters">       
        <div class="list-group-item">Face Color</div>
        <div class="list-group-list _facecolor">
            <ul>
                @foreach ($colors as $color)
                    @if ($color->c_amount > 0)
                    <li class="filter">
                        <a href="javascript:void(0)" data-filter="color={{ $color->p_color }}">{{ $color->p_color }} ({{$color->c_amount}})</a>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div class="list-group-item">Condition</div>
        <div class="list-group-list _condition">
            <ul>
                @foreach (Conditions()->take(4)->splice(1)->all() as $condition)
                    <li class="filter">
                        <a href="javascript:void(0)" data-filter="condition={{ $condition }}">{{ $condition }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="list-group-item">Status</div>
        <div class="list-group-list _status">
            <ul>
                @foreach (Status() as $status)
                    @if ($status!='Hidden')
                    <li class="filter">
                        <a href="javascript:void(0)" data-filter="status={{ $status }}">{{ $status }}</a>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <!-- <div class="list-group-item">Price</div>
        <div class="list-group-list">
            <div class="price">
                <div class="col">
                    From <input data-filter="pfrom" type="text" style="width: 100%" name="pfrom">
                </div>
                <div class="col">
                    To <input data-filter="pto" type="text" style="width: 100%" name="pto">
                </div>
                <div class="col">
                    <button type="submit" style="width:100%;margin:13px 0" class="btn btn-primary uploadPhoto">Search</button>
                </div>
            </div>
        </div> -->
    </div>
    @endif
</div>
@endif