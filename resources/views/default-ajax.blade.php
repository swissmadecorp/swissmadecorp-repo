<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('mata-description')">
    <meta name="author" content="Edward Babekov">
    <meta name="keywords" content="@yield('mata-keywords')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="pmS4GiEdRtpNip934PUq3pxOhDkTP3OProe9h4MDDck" />

    <title>@yield('title') - Swiss Made Corp...</title>

    @include("google-analytics")

    <!-- Bootstrap Core CSS -->
    <!--<link href="css/bootstrap.min.css" rel="stylesheet"> -->
    
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="/css/algolia-min.css" integrity="sha256-HB49n/BZjuqiCtQQf49OdZn63XuKFaxcIHWf0HNKte8=" crossorigin="anonymous">
    <link href="/mmenu/mmenu.css" rel="stylesheet">
    <link href="/css/mega-menu.css" rel="stylesheet">
    <link href="/css/dropzone.css" rel="stylesheet">
    <link href="/js/jquery-confirm/jquery-confirm.min.css" rel="stylesheet">    
    
    <!-- Custom CSS -->
    
    <!--<link href="css/shop-homepage.css" rel="stylesheet"> -->
    <link href="/css/default.css" rel="stylesheet">
    <link href="/css/algolia.css" rel="stylesheet">
    
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

<body>

<div class="modal"><!-- Place at bottom of page --></div>
<div id="menu-wrapper">
    <div id="o-wrapper" class="o-wrapper">
        <div class="mobile-menu">
            <div class="list-group">
                <div class="list-group-list">
                    <button class="c-menu__close"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                    <h2 class="title-category-dropdown"><span>Menu</span></h2>
                    <ul>
                    <li class="ui-menu-item level0"><a href="/">{{ e("Home") }}</a></li>
                        <li class="ui-menu-item level0"><a href="/watches">{{ e("Watches") }}</a></li>
                        <li class="ui-menu-item level0"><a href="/contact-us">{{ e("Contact Us") }}</a></li>
                        <li class="ui-menu-item level0"><a href="/about-us">{{ e("About Us") }}</a></li>
                        <hr>
                        <li class="ui-menu-item level0 account"><a href="/account">{{ e("My Orders") }}</a></li>
                        {{-- @if (Auth::guard('customer')->check()) --}}
                        <!-- <li class="ui-menu-item level0 login"><a href="/logout">{{-- e("Logout") --}}</a></li> -->
                        {{-- @else --}}
                        <!-- <li class="ui-menu-item level0 login"><a href="/login">{{-- e("Login") --}}</a></li> -->
                        {{-- @endif --}}
                        <li class="ui-menu-item level0 cart"><a href="/cart">Cart</a> <i class="fas fa-shopping-cart"></i></li>
                    </ul>
                </div>
            </div>
        </div>

        @inject('incart', 'App\Libs\_Cart')
        <div class="header" style="border-bottom: 1px solid #efefef;">
            <div class="container-fluid header-mobile">
                <div class="row">
                    <div class="top-bar-left col-md-12 col-sm-12 col-xl-12">
                        <div class="top-contact">
                            <span class="phone"><i class="fas fa-phone"></i> <a aria-label="Our phone number is, 212-840-8463" href="tel:212 697 9477" autofocus> 212 840 8463</a></span><span class="separator">/</span>
                            <span class="email"><a aria-label="Our email address is, info@swissmadecorp.com" href="mailto:info@swissmadecorp.com">info@swissmadecorp.com</a></span>
                        </div>
                        <div class="top-account">
                            <span class="account"><a href="/account">{{ e("my orders") }}</a></span>
                            <!-- <span class="separator">/</span> -->
                            {{-- @if (Auth::guard('customer')->check()) --}}
                            <!-- <span class="logout"><a href="#">{{-- e("logout") --}}</a></span> -->
                            {{-- @else --}}
                            <!-- <span class="login"><a href="#">{{-- e("login") --}}</a></span> -->
                            {{-- @endif --}}
                            <span class="separator">/</span>
                            <div class="inline-block exchange-rate">
                                
                                @include ('exchange-rate')    
                            </div>
                            <span class="separator">/</span>
                            <div class="total-cart-pro inline-block">
                            <span class="cart"><a href="/cart">{!! $incart->get() !!}cart <i class="fas fa-shopping-cart"></i></a></span>
                            @include ('totalcart')
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>  
        </div>

        <div class="header-banner">
            <div class="img-logo">
                <a href="<?= URL::to('/') ?>"><img src="/images/logo.png" aria-label="swiss made corp logo" alt="Swiss Made Corp." /></a>
            </div>
        </div>

        @include ('mega-menu')
        <div id="c-mask" class="c-mask"></div><!-- /c-mask -->
        
        <div class="mobile-filter-menu">
            <div class="mh-head">
                <a href="#my-menu"><span></span></a>
                <button type="button" class="c-button btn btn-secondary" id="my-button" ><i class="fas fa-bars" aria-hidden="true"></i></button>
            </div>
            @include ("layouts.sidebar-mobile") 
        </div>

        <div class="container-fluid mainBk">
            <div class="row">
                <!-- <div class="col-md-3 col-sm-12 col-xl-2">
                    <div class="img-logo">
                        <div>
                        <a href="<?= URL::to('/') ?>"><img src="/images/logo.png" alt="Swiss Made Corp." /></a>
                        </div>
                    </div>
                </div> -->
                <!-- <div class="col-md-6 col-sm-5 hidden-xs"> -->
                    {{-- @include("menus") --}}
                <!-- </div> -->

                <div class="sell-your-watch" >
                    <button class="btn btn-secondary btn-sm">Sell your watch</button>
                </div>
            </div>
        </div>


        <!-- <div class="banner"></div> -->
        <div class="header-control header-nav">
            @if (isset($product_details))
            <div class="container">
            @else
            <div class="container-fluid">
            @endif
                <div class="row">

                    <div class="col-xs-6 col-sm-3 col-md-3 col-xl-2 hidden-xs custom-menu">
                        @if(!isset($sidebar))
                            @include ("layouts.sidebar-ajax")
                        @endif
                    </div>

                    <div class="col-xs-5 col-sm-12 col-md-9 col-xl-10">
                        @if (Route::getCurrentRoute())
                            @if (Route::getCurrentRoute()->uri() != '/')
                            <div class="searchcontainer">
                                <input class="searchbar" type="text" placeholder="Search for products, categories, ...">
                                <button class="btn btn-secondary btn-sm" aria-label="Delete text" style="display: none">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="m_bottom_25" style=" margin-top: 15px">
                                @include('filters')
                                @yield('content')
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (Route::getCurrentRoute())
            @if (Route::getCurrentRoute()->uri() == '/')
            @yield('top-banner')
            
            
            <div class="container-fluid m_bottom_14">
                <div class="row">
                    <div class="col-md-12">
                        @include('filters')
                        @yield('content')
                    </div>
                </div>
            </div>
            @endif    
        @endif   

        @include ("layouts.footer")
    </div>
</div>

<!-- <div class="divlogin" style="display: none"> -->
{{-- Form::open(array('url' => 'member/login', 'class' => 'formLogin')) --}}
    <!-- <div class="form-group">
        <label>Enter your username here</label>
        <input type="text" placeholder="your email" class="form-control" name="name" required />
        <label>Enter your password here</label>
        <input type="password" placeholder="your password" name="password" class="form-control" required />
    </div> -->
{{-- Form::close() --}}
<!-- </div> -->

@include ('sell-your-watch')

    <!-- jQuery -->
    <!--<script src="js/jquery.js"></script> -->
    
    <!-- <script src="{{ asset('js/herbyCookie.min.js') }}"></script> -->
    <script src="/js/general.js"></script>
    <script src="/mmenu/mmenu.js"></script>
    <script src="/js/dropzone.js"></script>
    <script src="/js/jquery.scrollUp.js"></script>
    <script src="https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.min.js"></script>
    <script src="js/jquery-confirm/jquery-confirm.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <!--<script src="js/bootstrap.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/instantsearch.js@4.8.3/dist/instantsearch.production.min.js" integrity="sha256-LAGhRRdtVoD6RLo2qDQsU2mp+XVSciKRC8XPOBWmofM=" crossorigin="anonymous"></script>
    
    @yield('footer')
    @yield('jquery')

    <script>

        $(document).ready( function() {
            $('.phone').focus();
            $('.del-icon').click( function() {
                _this = $(this);
                $.ajax({
                    type: "post",
                    dataType: 'json',
                    url: "{{ route('cart.product.remove') }}",
                    data: {id: $(this).attr('data-id')},
                    success: function(data){
                        if (data>0)
                            $(_this).parents('.single-cart').remove()
                        else
                            $('.total-cart').remove();
                            $('.incart').remove();
                    },
                    error: function(data) {
                        $.alert({
                            title: 'Cart',
                            content: 'Failed to delete this product from your cart.'
                        });
                    }
                })
            })

            $(document).on('click','.currency-selection a', function (e) {
                e.preventDefault();
                var _this = this;
                $.post("{{ route('switch.currency.rate') }}",{code: $('img',_this).attr('data-id')}, function(data)  {
                    $('.active-currency img').attr(
                        {
                            'data-id': $('img',_this).attr('data-id'),
                            'src': $('img',_this).attr('src'),
                            'alt': $('img',_this).attr('alt')
                        }
                    )
                    
                    $('.currency-selection').html(data);
                    $('.currency-selection a').css('margin-right', '3px');
                    $.ajax ( {
                        type: 'get',
                        url: '{{route("show.watches")}}',
                        success: function(data) {
                            $('#product-items').html(data);
                        }
                    })

                })
            })

            $('.ais-Pagination-link').mousedown( function(e) {
                e.preventDefault();
                
            })

            // $(document).herbyCookie({
            //    style: "dark",
            //    policyText: "Price policy.",
            //    btnText: "Got it!",
            //    text: "Prices on this website are for watch dealers only! Please visit the page to read more regarding our ",
            //    scroll: false,
            //    expireDays: 30,
            //    link: "/price-policy"
            // });

            $('#c-button--push-left').click(function(e) {
                e.preventDefault;
                $('.mobile-menu').addClass('is-active')
                $('.c-mask').addClass('is-active')
                document.ontouchmove = function(e){ e.preventDefault(); }
            });

            $('.c-menu__close').click(function(e) {
                e.preventDefault;
                $('.mobile-menu').removeClass('is-active')
                $('.c-mask').removeClass('is-active')
                document.ontouchmove = function(e){ return true; }
            });

            $('.c-mask').click(function(e) {
                $('.c-menu__close').click();
            })

            $("nav#my-menu").mmenu({
                
                navbar: {
                    content : [ "prev", "searchfield", "close" ],
                    title: "Filters"
                },
                setSelected: true,
                searchfield: {
                    resultsPanel: true
                }
            }, {
                // configuration
                offCanvas: {
                    pageSelector: "#menu-wrapper"
                }
            });

            // $('.login').click(  function(e) {
            //     e.preventDefault();
            //     LoginUser();
            // })

            // $('.logout').click(  function(e) {
            //     e.preventDefault();
            //     window.location.href = "{{ url('member/logout') }}"
            // })

            // function LoginUser() {
            //     $.confirm({
            //         title: 'Dealer Login',
            //         content: $('.divlogin').html(),
            //         buttons: {
            //             formSubmit: {
            //                 text: 'Submit',
            //                 btnClass: 'btn-blue',
            //                 action: function () {
                                
            //                     $.ajax({
            //                         type: "post",
            //                         dataType: 'json',
            //                         url: "{{ url('member/login') }}",
            //                         data: $('.formLogin').serialize(),
            //                         cache: false,
            //                         success: function(data){
            //                             window.location.href = data.redirect
            //                         },
            //                         error: function(data) {
            //                             $.alert({
            //                                 title: 'Login',
            //                                 content: 'Failed to login. Please check your credentials and try again.'
            //                             });
            //                         }
            //                     })
            //                 }
            //             },
            //             cancel: function () {
            //                 //close
            //             },
            //         },
            //         onContentReady: function () {
            //             // bind to events
            //             var jc = this;
            //             this.$content.find('.formName').on('submit', function (e) {
            //                 // if the user submits the form by pressing enter in the field.
            //                 e.preventDefault();
            //                 jc.$$formSubmit.trigger('click'); // reference the button and click it
            //             });
            //         }
            //     });
            // }

            $body = $("body");
            
            $(document).on({
                ajaxStart: function() { 
                    $body.addClass("loading");
                },
                ajaxStop: function() { $body.removeClass("loading");}
            });

            $.ajaxSetup({
                beforeSend: function(xhr, type) {
                    if (!type.crossDomain) {
                        xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
                    }
                },
            });

            $.scrollUp({
		      scrollText: '<i class="fas fa-angle-up"></i>',
		      easingType: 'linear',
		      scrollSpeed: 900,
		      animation: 'fade'
	        }); 

            var $menu = $("#my-menu").mmenu();

            var API = $("#my-menu").data('mmenu');
            if (API != undefined) {
                $("#my-button").click(function() {
                    $('.mobile-menu').hide()
                    $('.c-menu').hide()
                    API.open();
                });

                API.bind('close:finish', function () {
                    $('.mobile-menu').show()
                    $('.c-menu').show()
                });
            }
        })
    </script>
    <a id="scrollUp" href="#top" style="position: fixed; z-index: 2147483647;"><i class="fas fa-angle-up"></i></a>
    <script type="text/javascript" id="zsiqchat">var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode: "7bedf951a5141c64dc64d6bd0940481f7088a3d5e42b38e8828312f559ba91e3d0b83ca79b71cef3344a11a55d437c94", values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.com/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);</script>
    <!-- Website built by Ephraim Babekov - 718-614-7678 -->

</body>

</html>
