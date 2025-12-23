@if (isMobile()==false)
<nav class="navigation verticalmenu side-verticalmenu c-menu c-menu--push-left height-100" id="c-menu--push-left">
    <button class="c-menu__close"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
    <h2 class="title-category-dropdown">
        <span>
            <a href="/watches">Categories</a>
        </span>
    </h2>
    <ul class="togge-menu list-category-dropdown">
    <div class="list-group">
    
        <div class="list-group-list">
            <ul class="categories">

                @foreach ($categories as $category)
                
                    
                    <?php $cat = 'watches/'.$category->id ?>
                    <?php if (strpos($cat,' ') !==false) {
                        $cat = str_replace(' ','-',$cat);
                    } ?>

                    <li class="ui-menu-item level0">
                        <div class="open-children-toggle"></div>
                        
                        @if ($category->category_name == 'Rolex')
                        <a href="<?php echo $cat ?>/" class="level-top">
                            <span>{{ $category->category_name }}</span>
                            <!-- <span class="totalcategories">{{-- ' ('.$totalItems.')' --}}</span> -->
                        </a>
                        <div class="extender">
                            <ul>
                                <li><a href="{{$cat}}/new-unworn-rolex-watches-new-york/?condition=unworn">Unworn</a></li>
                                <li><a href="{{$cat}}/certified-pre-owned-rolex-waches-new-york/?condition=pre-owned">Pre-owned</a></li>
                            </ul>
                        </div>
                        @else
                        <a href="<?php echo $cat . '/'.strtolower($category->location) ?>" class="level-top">
                            <span>{{ $category->category_name }}</span>
                            <!-- <span class="totalcategories">{{-- ' ('.$totalItems.')' --}}</span> -->
                        </a>
                        @endif
                    </li>
                    
                    
                    
                @endforeach
                
            </ul>
        </div>
    </div>

    <script>
  
        $(document).ready( function() {
            //$('.title-category-dropdown').click(function() {
              //  $('.navigation').css('transform', 'translateX(-425px)');
                //$('.col-xl-10').removeClass('col-xl-10').addClass('col-xl-12');
                //$('.col-xl-10').css({flex: '0 0 100%', maxWidth: '100%'});

                //setTimeout(function() {
                //    $('.hidden-sm').css('display','none');
                    
                //},200)

            //})

            $('.level-top').click( function (e) {
                if ($(this).find('span').first(0).text() != "Rolex") {
                    return 
                }

                e.preventDefault();
                e.stopPropagation();

                $('.categories li div').each( function () {
                    if ($(this).css('display')=='block') {
                        $(this).animate({
                            opacity: 1,
                            height: "toggle",
                        }, 600, function() {
                            //collapsed = null;
                        });
                    }

                    return false
                })

                $(this).next().animate({
                    opacity: 1,
                    left: "+=50",
                    height: "toggle",
                }, 600, function() {
                    //$(this).addClass('collapsed')
                    //collapsed = $(this)
                });

            })
        })
    </script>
    </ul>
</nav>

@endif