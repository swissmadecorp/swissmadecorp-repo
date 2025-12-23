<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @stack('meta-title')
    @stack('meta-description')
    <meta name="author" content="Ephraim Babekov">
    @stack('meta-keywords')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="pmS4GiEdRtpNip934PUq3pxOhDkTP3OProe9h4MDDck" />

    <title>@yield('title') - Swiss Made Corp.</title>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link href="/css/default-new.css" rel="stylesheet">


  <style>
    /* Custom CSS for transition */
    .transition-height {
      transition: height 0.3s ease, padding 0.3s ease;
    }
    .transition-content {
      transition: transform 0.3s ease;
    }

    /* Custom CSS for multi-line truncation */
   .truncate-4-lines {
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      line-clamp: 4; /* Standard syntax, not widely supported yet */
      max-height: calc(2rem * 4); /* Adjust to match the line height */
   }

    .header-large {
      height: 80px; /* Default larger height */
      padding: 2rem 1rem; /* Default larger padding */
    }
    .header-shrink {
      height: 80px; /* Smaller height when scrolled */
      padding-right: 1rem; /* Smaller padding when scrolled */
    }
    .shrink-image {
      transform: scale(0.8); /* Adjust scale for image when header is shrunk */
    }
  </style>

    <!-- Bootstrap Core CSS -->
    <!--<link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="/images/favicons/apple-touch-icon.png" />
    <link rel="apple-touch-icon" sizes="57x57" href="/images/favicons/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/images/favicons/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="/images/favicons/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/images/favicons/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="/images/favicons/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/images/favicons/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="/images/favicons/apple-touch-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicons/apple-touch-icon-180x180.png" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script> -->
    <!-- <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script> -->
    <!-- <script src="/js/fileupload/jquery.ui.widget.js"></script> -->

    @vite(['resources/css/app.css','resources/js/app.js'])
    @yield('styles')
    @yield('header')
    @yield("canonicallink")
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="dark:bg-gray-800">
    @livewire('header')

    <?php $style='';
        $f = request()->header('User-Agent');
        $s = preg_match('/Mobile|Android|iPhone/', request()->header('User-Agent'));
    ?>

    <div x-data="{ isSmallScreen: window.innerWidth < 640 }" x-init="
            isSmallScreen = window.innerWidth < 640;
            window.addEventListener('resize', () => {
                isSmallScreen = window.innerWidth < 640;
            });
        ">
        @if (request()->is('unsubscribe/*') || request()->is('product-details*') || request()->is('sell-your-watches') || request()->is('aboutus') || request()->is('checkout')  || request()->is('credit-card-processor*') || request()->is('blogs/*') || request()->is('contactus') || request()->is('termsconditions') || request()->is('privacypolicy') || request()->is('thankyou') || request()->is('blogs*') || request()->is('rolex-serial-numbers'))
            <?php $style='md:pt-[0.5rem] 640-787:pt-[0.5rem] pt-[4.2rem]'; ?>
        @elseif (request()->is('watch-products*') )
            <?php $style="sm:ml-[16rem] md:pt-[0.5rem] 640-787:pt-[0.5rem] pt-[4.2rem] "; ?>
            @livewire('sidebar')

        @endif

        <div x-show="isSmallScreen" style="display: none;">

        </div>
    </div>
    <div class="{{$style}} bg-white">
        @if (!request()->is('/'))
        <?php $location = 'mt-[2.8rem]' ?>
        @else
        <?php $location = 'mt-[6.6rem]' ?>
        @endif
        <div class="border-gray-200 rounded-lg dark:border-gray-700 md:mt-[8.5rem] {{$location}} sm:mt-[8.4rem]">
            @yield('content')
        </div>
    </div>


<footer class="{{$style}} dark:bg-gray-900 ">
    <div class="mx-auto w-full bg-gray-900">
      <div class="grid grid-cols-2 gap-8 px-8 py-6 lg:py-8 md:grid-cols-4 w-full">
        <div>
            <h2 class="mb-6 text-sm font-semibold text-white uppercase dark:text-white">Company</h2>
            <ul class="text-gray-400 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="/aboutus" class=" hover:underline">About</a>
                </li>
                <li class="mb-4">
                    <a href="/contactus" class="hover:underline">Contact Us</a>
                </li>
                <li class="mb-4">
                    <a href="/blog" class="hover:underline">Blog</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-white uppercase dark:text-white">Legal</h2>
            <ul class="text-gray-400 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="/privacypolicy" class="hover:underline">Privacy Policy</a>
                </li>
                <li class="mb-4">
                    <a href="/termsconditions" class="hover:underline">Terms and Conditions</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-white uppercase dark:text-white">Trusted Seller</h2>
            <ul class="text-gray-400 dark:text-gray-400 font-medium flex">
                <li class="mb-4">
                  <a target="_blank" href="https://www.chrono24.com/dealer/212swissmade/index.htm" style="">
                     <img width="70" alt="Chrono24 Trusted Seller" src="/images/trusted-seller-icon.png"></a>
                </li>
                <li class="mb-4">
                  <a target="_blank" href="https://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&amp;userid=swissmadecorp" style="">
                     <img width="50" alt="Chrono24 Trusted Seller" src="/images/ebay_logo.png"></a>
                </li>
                <li class="mb-4">
                  <a target="_blank" href="http://www.iwjg.com/" style="">
                     <img height="100" width="100" alt="Chrono24 Trusted Seller" src="/images/iwjg.jpg"></a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-white uppercase dark:text-white">Our address</h2>
            <h3 class="mb-2 text-xs font-semibold text-red-400 uppercase dark:text-white">By appointments only</h3>
            <ul class="text-gray-400 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    15 W 47th Street, Ste 503<br>
                    New York, NY 10036<br>
                    P: 212-697-9477
                    F: 212-391-8463
                </li>
            </ul>
        </div>
    </div>

    <div class="px-4 py-6 dark:bg-gray-700 md:flex md:items-center md:justify-between flex-col">
        <span class="text-sm text-gray-400 dark:text-gray-300 sm:text-center">
            <a href="https://swissmadecorp.com/">Copyright &copy; Swiss Made Corp. 2017 - {{ date('Y') }}</a>. All Rights Reserved.
        </span>
        <div class="flex mt-4 sm:justify-center md:mt-0 space-x-5 rtl:space-x-reverse">
            <a href="https://www.facebook.com/p/Swiss-Made-Corp-100064226371392/" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg height="15px" width="15px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path style="fill:#9ca3af;" d="M134.941,272.691h56.123v231.051c0,4.562,3.696,8.258,8.258,8.258h95.159 c4.562,0,8.258-3.696,8.258-8.258V273.78h64.519c4.195,0,7.725-3.148,8.204-7.315l9.799-85.061c0.269-2.34-0.472-4.684-2.038-6.44 c-1.567-1.757-3.81-2.763-6.164-2.763h-74.316V118.88c0-16.073,8.654-24.224,25.726-24.224c2.433,0,48.59,0,48.59,0 c4.562,0,8.258-3.698,8.258-8.258V8.319c0-4.562-3.696-8.258-8.258-8.258h-66.965C309.622,0.038,308.573,0,307.027,0 c-11.619,0-52.006,2.281-83.909,31.63c-35.348,32.524-30.434,71.465-29.26,78.217v62.352h-58.918c-4.562,0-8.258,3.696-8.258,8.258 v83.975C126.683,268.993,130.379,272.691,134.941,272.691z"></path> </g></svg>
                  <span class="sr-only">Facebook page</span>
            </a>
            <a href="https://www.pinterest.com/swiss_made_corp/" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
              <svg height="15px" width="15px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 511.998 511.998" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path style="fill:#9ca3af;" d="M405.017,52.467C369.774,18.634,321.001,0,267.684,0C186.24,0,136.148,33.385,108.468,61.39 c-34.114,34.513-53.675,80.34-53.675,125.732c0,56.993,23.839,100.737,63.76,117.011c2.68,1.098,5.377,1.651,8.021,1.651 c8.422,0,15.095-5.511,17.407-14.35c1.348-5.071,4.47-17.582,5.828-23.013c2.906-10.725,0.558-15.884-5.78-23.353 c-11.546-13.662-16.923-29.817-16.923-50.842c0-62.451,46.502-128.823,132.689-128.823c68.386,0,110.866,38.868,110.866,101.434 c0,39.482-8.504,76.046-23.951,102.961c-10.734,18.702-29.609,40.995-58.585,40.995c-12.53,0-23.786-5.147-30.888-14.121 c-6.709-8.483-8.921-19.441-6.222-30.862c3.048-12.904,7.205-26.364,11.228-39.376c7.337-23.766,14.273-46.213,14.273-64.122 c0-30.632-18.832-51.215-46.857-51.215c-35.616,0-63.519,36.174-63.519,82.354c0,22.648,6.019,39.588,8.744,46.092 c-4.487,19.01-31.153,132.03-36.211,153.342c-2.925,12.441-20.543,110.705,8.618,118.54c32.764,8.803,62.051-86.899,65.032-97.713 c2.416-8.795,10.869-42.052,16.049-62.495c15.817,15.235,41.284,25.535,66.064,25.535c46.715,0,88.727-21.022,118.298-59.189 c28.679-37.02,44.474-88.618,44.474-145.282C457.206,127.983,438.182,84.311,405.017,52.467z"></path> </g></svg>
                    <span class="sr-only">Pinterest</span>
            </a>
            <a href="https://www.instagram.com/swiss_made_corp/?hl=en" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg width="15px" height="15px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18ZM12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" fill="#9ca3af"></path> <path d="M18 5C17.4477 5 17 5.44772 17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5Z" fill="#9ca3af"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M1.65396 4.27606C1 5.55953 1 7.23969 1 10.6V13.4C1 16.7603 1 18.4405 1.65396 19.7239C2.2292 20.8529 3.14708 21.7708 4.27606 22.346C5.55953 23 7.23969 23 10.6 23H13.4C16.7603 23 18.4405 23 19.7239 22.346C20.8529 21.7708 21.7708 20.8529 22.346 19.7239C23 18.4405 23 16.7603 23 13.4V10.6C23 7.23969 23 5.55953 22.346 4.27606C21.7708 3.14708 20.8529 2.2292 19.7239 1.65396C18.4405 1 16.7603 1 13.4 1H10.6C7.23969 1 5.55953 1 4.27606 1.65396C3.14708 2.2292 2.2292 3.14708 1.65396 4.27606ZM13.4 3H10.6C8.88684 3 7.72225 3.00156 6.82208 3.0751C5.94524 3.14674 5.49684 3.27659 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3.27659 5.49684 3.14674 5.94524 3.0751 6.82208C3.00156 7.72225 3 8.88684 3 10.6V13.4C3 15.1132 3.00156 16.2777 3.0751 17.1779C3.14674 18.0548 3.27659 18.5032 3.43597 18.816C3.81947 19.5686 4.43139 20.1805 5.18404 20.564C5.49684 20.7234 5.94524 20.8533 6.82208 20.9249C7.72225 20.9984 8.88684 21 10.6 21H13.4C15.1132 21 16.2777 20.9984 17.1779 20.9249C18.0548 20.8533 18.5032 20.7234 18.816 20.564C19.5686 20.1805 20.1805 19.5686 20.564 18.816C20.7234 18.5032 20.8533 18.0548 20.9249 17.1779C20.9984 16.2777 21 15.1132 21 13.4V10.6C21 8.88684 20.9984 7.72225 20.9249 6.82208C20.8533 5.94524 20.7234 5.49684 20.564 5.18404C20.1805 4.43139 19.5686 3.81947 18.816 3.43597C18.5032 3.27659 18.0548 3.14674 17.1779 3.0751C16.2777 3.00156 15.1132 3 13.4 3Z" fill="#9ca3af"></path> </g></svg>
                  <span class="sr-only">Instagram</span>
            </a>
        </div>
      </div>
    </div>
</footer>

@yield('footer')
@yield('jquery')


<script>

    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenus = document.querySelectorAll('.mobile-menu');

        const hideMobileMenusOnResize = () => {
            if (window.innerWidth <= 768  && window.location.pathname != '/watch-products') {
                mobileMenus.forEach(menu => menu.classList.add('hidden'));
            }
        };

        // Initially hide the mobile menu if the screen is mobile-sized
        hideMobileMenusOnResize();

        // Add a listener for screen size changes
        window.addEventListener('resize', hideMobileMenusOnResize);
    });

    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    confirm('Your page has expired. It will be refreshed.')
                    preventDefault()
                    location.reload()
                }
            })
        })
    })

    // Function to handle new nodes
    function handleNewNodes(nodes) {
        nodes.forEach(node => {
            // Check if the node is an element and has the desired attribute
            if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('drawer-backdrop')) {
                //console.log('New drawer-backdrop div found:', node);
                // Perform actions on the new div here
                node.style.zIndex = 10
            }
        });
    }

    // Create a MutationObserver instance
    const observer = new MutationObserver(mutationsList => {
        mutationsList.forEach(mutation => {
            // Check if new nodes were added
            if (mutation.addedNodes.length) {
                handleNewNodes(mutation.addedNodes);
            }
        });
    });

    // $.scrollUp({
    //     scrollText: '<i class="fas fa-angle-up"></i>',
    //     easingType: 'linear',
    //     scrollSpeed: 900,
    //     animation: 'fade'
    // });

    // Configuration for the observer
    const config = {
        childList: true, // Observe direct children
        subtree: true    // Observe all descendants
    };

    // Start observing the document body (or a specific element)
    observer.observe(document.body, config);

  </script>

    <a id="scrollUp" href="#top" style="position: fixed; z-index: 2147483647;"><i class="fas fa-angle-up"></i></a>
    <!-- <script type="text/javascript" id="zsiqchat">var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode: "7bedf951a5141c64dc64d6bd0940481f7088a3d5e42b38e8828312f559ba91e3d0b83ca79b71cef3344a11a55d437c94", values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.com/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);</script> -->

</body>

</html>
