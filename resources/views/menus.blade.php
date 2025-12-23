<div style="margin-bottom: 20px">
<nav class="navbar navbar-toggleable-md navbar-light custom-toggler  navbar-styled" style="">
  <!-- <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button> -->
   <!-- <a class="navbar-brand" href="#">Navbar</a>  -->

  <div class="navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item {{ (Request::is('/')  ? 'active' : '') }}">
        <a class="nav-link" href="{{ URL::to('/watches') }}">Home</a>
      </li>
      <li class="nav-item {{ (Request::is('watches/*') || Request::is('watches') ? 'active' : '') }}">
        <a class="nav-link" href="{{ URL::to('/watches') }}">Watches</a>
      </li>
      <li class="nav-item {{ (Request::is('contact-us') ? 'active' : '') }}">
        <a class="nav-link" href="{{ URL::to('/contact-us') }}">Contact Us</a>
      </li>
      <li class="nav-item {{ (Request::is('about-us') ? 'active' : '') }}">
        <a class="nav-link" href="{{ URL::to('/about-us') }}">About Us</a>
      </li>
      <li class="nav-item {{ (Request::is('blog') ? 'active' : '') }}">
        <a class="nav-link" href="{{ URL::to('/blog') }}">Blog</a>
      </li>
    </ul>
  </div>
</nav>
</div>