<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="">
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
    <title>SwissMade - {{ isset($pagename) ? $pagename : "Admin Page"}}</title>
    <!-- Bootstrap core CSS -->
    <!--<link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">-->

    @if(request()->route()->uri != "admin/test")
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    @endif
    <!-- <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link href="/js/jquery-confirm/jquery-confirm.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="/css/dashboard.css" rel="stylesheet">
    @yield('header')
    @if (isset($includeDataTableCss))
      @includeWhen($includeDataTableCss, 'layouts.datatableCss')
    @endif
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script>
      var globals = {
        "json": {
            "webMargin": {{CCMargin()}},
          }
        }
    </script>
  </head>
  <body>

@if( Auth::user())

  @include('layouts.admin-nav')
    <div class="container-fluid" id="app">
      <div class="row">
        @include ('layouts.admin-sidebar')
        <main class="offset-sm-3 col-10 offset-md-2 pt-3">
          @if (isset($pagename))
          <?php
            $status='';
            if (strpos($pagename,"UnPaid")>0)
              $status='background: #fb5f5f61"';
            elseif (strpos($pagename,"Returned")>0)
              $status='background: #fb5f5f61"';
            //else $status=3;
          ?>
          <h1 class="page-header" style='{{ $status }}'>{{ $pagename }}</h1>
          @endif
          @yield('content')
          <div class="pt-2 placeholders" style="clear:both">
            <!-- will be used to show any messages -->
            @if (Session::has('message'))
              <div class="alert alert-info">{{ Session::get('message') }}</div>
            @endif
          </div>
        </main>
      </div>
    </div>
@else
  <div class="container-fluid">
    <div class="row">
      <nav class="navbar navbar-toggleable-md navbar-inverse fixed-top bg-inverse">
        <button class="navbar-toggler navbar-toggler-right hidden-lg-up" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="<?= URL::to('/')?>/admin/login">Login</a>
      </nav>
      @yield('content')
    </div>
  </div>
@endif
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!--<script src="{{ asset('/js/bootstrap.min.js') }}"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script src="/js/customers/jquery.customer.js"></script>
    <script src="/js/general.js"></script>
    <script src="/js/jquery-confirm/jquery-confirm.min.js"></script>
    @if (isset($includeDataTableJs))
      @includeWhen($includeDataTableJs, 'layouts.datatableJs')
    @endif
    @yield('footer')
    <div class="modal"><!-- Place at bottom of page --></div>
    <script>
        $(document).ready( function() {
          $body = $("body");
          $(document).on({
            ajaxStart: function() {
              $body.addClass("loading");
            },
            ajaxStop: function() { $body.removeClass("loading");}
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
      });
    </script>
    @yield('jquery')

  </body>
</html>
