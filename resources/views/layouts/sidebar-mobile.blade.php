<nav id="my-menu">
    <ul class="group-mobile-filters">
        <li><span><a href="/watches">{{ e("Watches") }}</a></span></li>
        <li><span><a href="/contact-us">{{ e("Contact Us") }}</a></span></li>
        <li><span><a href="/account">{{ e("My Orders") }}</a></span></li>
        {{-- @if (Auth::guard('customer')->check()) --}}
        <!-- <li><span class="logout">Logout</span></li> -->
        {{-- @else --}}
        <!-- <span class="login"><a href="#">{{ e("Login") }}</a></span> -->
        {{-- @endif --}}
        <li><span><a href="/cart">Cart <i class="fas fa-shopping-cart"></i></a></span></li>
    </ul>
</nav>