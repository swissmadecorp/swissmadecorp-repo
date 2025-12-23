<div>
    {{-- The Master doesn't talk, he acts. --}}
    @section ('jquery')
    <script src="/js/jquery.easy-ticker.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.myWrapper').easyTicker({
        direction: 'up',
        easing: 'swing',
        speed: 'slow',
        interval: 3000,
        height: 'auto',
        visible: 7,
        mousePause: true,
        autoplay: true,
        controls: {
            up: 'Up',
            down: 'Down',
            toggle: '',
            playText: 'Play',
            stopText: 'Stop'
        },
        callbacks: {
            before: false,
            after: false,
            finish: false
        }
    });
    });
    </script>

    @stop

    <div class="myWrapper">
        <ul>
            @foreach ($products as $product)
            <li class="">
                <div class="p-1">
                    <div class="p-1 border flex gap-4 hover:bg-blue-100 dark:text-gray-100 dark:hover:text-gray-900 cursor-pointer transition-colors duration-300 ease-in-out">
                        <?php
                        if (isset($product->images[0])) {
                            $countermage = $product->images[0];
                            $path = '/images/thumbs/'.$product->images[0]->location;
                            $path = '<a href="/product-details/'.$product->slug.'"class="w-20" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$path.'"></a>';
                        } else {
                            $countermage="/images/no-image.jpg";
                            $path = '<a href="/product-details/' . $product->slug . '" class="w-20" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$countermage.'"></a>';
                        } ?>

                        {!! $path !!}
                        <div class="flex items-center">
                            {{$product->title}}
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
</div>

</div>  
