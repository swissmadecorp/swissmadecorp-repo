@if (isMobile()==false)
        
<div class="refinement models" style="<?= count($routes) > 1 ? '' : "display: none" ?>">

    @if (count($routes)>1)
    <h5>Models</h5>
    <!-- <button>Show more models</button> -->
    <div id="models">
        <div class="ais-RefinementList">
            <ul class="ais-RefinementList-list">
                @if (!empty($models))
                @foreach ($models as $model)
                    <?php $cat = "/watches/$model->category_id/".strtolower($model->category_name) ?>
                    <?php if (strpos($cat,' ') !==false) {
                        $cat = str_replace(' ','-',$cat);
                    } ?>
                    
                    <li class="ais-RefinementList-item">
                        <a href="<?php echo $cat . '/'.strtolower(str_replace(' ','-',$model->p_model)) ?>" class="level-top">
                            <span>{{ $model->p_model }}</span>
                        </a>
                        <hr>
                    </li>
                @endforeach
                @endif
            </ul>
        </div>
    </div>
    @endif
</div>

<div class="refinement brands">
    <h5>Brands</h5>
    <button>Show more brands</button>
    <div id="brands">
        <div class="ais-RefinementList">
            <ul class="ais-RefinementList-list">
                @foreach ($categories as $category)
                    <?php $cat = '/watches/'.$category->id ?>
                    <?php if (strpos($cat,' ') !==false) {
                        $cat = str_replace(' ','-',$cat);
                    } ?>
                    
                    <li class="ais-RefinementList-item">
                        <a href="<?php echo $cat . '/'.strtolower($category->location) ?>" class="level-top">
                            <span>{{ $category->category_name }}</span>
                        </a>
                        <hr>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="refinement case_size">
    <h5>Case Size</h5>
    <button>Show more sizes</button>
    <div id="sizes">
        <div class="ais-RefinementList">
            <ul class="ais-RefinementList-list">
                @foreach ($casesizes as $casesize)
                    <?php $cat = '/search?p='.str_replace(' ','',htmlspecialchars($casesize->p_casesize)) ?>
                    
                    <li class="ais-RefinementList-item">
                        <a href="{{$cat}}" class="level-top">
                            <span>{{ $casesize->p_casesize }}</span>
                        </a>
                        <hr>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="refinement gender">
    <h5>Gender</h5>
    <div id="sizes">
        <div class="ais-RefinementList">
            <ul class="ais-RefinementList-list">
                @foreach (Gender() as $gender)
                    <?php $cat = '/search?p='.$gender ?>
                    
                    <li class="ais-RefinementList-item">
                        <a href="{{$cat}}" class="level-top">
                            <span>{{ $gender }}</span>
                        </a>
                        <hr>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="refinement condition">
    <h5>Condition</h5>
    <div id="condition">
        <div class="ais-RefinementList">
            <ul class="ais-RefinementList-list">
                @foreach (Conditions() as $condition)
                    @if ($condition != 'Select condition for this product' && $condition != 'New (old stock)')
                    <?php $cat = '/search?p='.htmlspecialchars($condition) ?>
                    
                    <li class="ais-RefinementList-item">
                        <a href="{{strtolower($cat)}}" class="level-top">
                            <span>{{ $condition }}</span>
                        </a>
                        <hr>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>

    $(document).ready( function() {

        $(document).on('click', '#brands a, #models a', function(e) {
            e.preventDefault();
            href = $(this).attr('href').substr(9);
            title = 'New and Pre owned '+$(this).children(0).text()+' watches. - Swiss Made Corp.'
            history.pushState({}, '',  $(this).attr('href'));
            document.title = title;
            var catId = href.substr(0,href.indexOf('/'))
            var catName = href.substr(href.indexOf('/')+1,href.length-href.indexOf('/'));
            // content.innerHTML = $(this).attr('href')
            $.ajax ( {
                type: 'get',
                url: '{{route("show.watches")}}',
                data: {catId: catId,cat: catName},
                success: function(data) {
                    $('#product-items').html(data[0]);
                    if (data[1]) {
                        $('.refinement.models').html(data[1]);
                        $('.refinement.models').show();
                    } 
                    
                    if (data[2]) {
                        
                            $('.catalog_highlight').html(data[2]);
                            $('.catalog_highlight').show();  
                         
                    } else {
                            $('.catalog_highlight').hide();
                    }
                    $('html,body').animate({ scrollTop: 0 }, 'slow');
                }
            })    
        })

        $('#sizes a,#condition a').click (function(e) {
            e.preventDefault();
            url = $(this).attr('href');
            history.pushState({}, '', url);
            filter = url.substr(9);
            $.ajax ( {
                type: 'get',
                url: '{{route("search")}}',
                data: {p: filter},
                success: function(data) {
                    $('#product-items').html(data);
                    $('.catalog_highlight').html('');
                    $('.refinement.models').html('');
                    $('.refinement.models').hide();
                    $('.catalog_highlight').hide();
                    $('html,body').animate({ scrollTop: 0 }, 'slow');
                }
            }) 
        })


        /* Function to animate height: auto */
        function autoHeightAnimate(element, time){
            var curHeight = element.height(), // Get Default Height
                autoHeight = element.css('height', 'auto').height()+31; // Get Auto Height
                element.height(curHeight); // Reset to Default Height
                element.stop().animate({ height: autoHeight }, time); // Animate to Auto Height
        }

        $('.refinement button').click( function() {
            if ($(this).parent().css('overflow') == 'hidden') {
                $(this).parent().css({overflow: 'visible'})
                autoHeightAnimate($(this).parent(), 300);
                newtext = $(this).text().replace('Show','Hide');
                $(this).text(newtext)
            } else {
                $(this).parent().css('overflow', 'hidden');
                newtext = $(this).text().replace('Hide','Show');
                $(this).text(newtext)
                $(this).parent().stop().animate({ height: '350px' }, 300); // Animate to Auto Height
            }
        })

        // $('.level-top').click( function (e) {
        //     if ($(this).find('span').first(0).text() != "Rolex") {
        //         return 
        //     }

        //     e.preventDefault();
        //     e.stopPropagation();

        //     $('.categories li div').each( function () {
        //         if ($(this).css('display')=='block') {
        //             $(this).animate({
        //                 opacity: 1,
        //                 height: "toggle",
        //             }, 600, function() {
        
        //             });
        //         }

        //         return false
        //     })

        //     $(this).next().animate({
        //         opacity: 1,
        //         left: "+=50",
        //         height: "toggle",
        //     }, 600, function() {
        
        //     });

        // })
    })
</script>

@endif