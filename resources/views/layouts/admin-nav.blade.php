<nav class="navbar navbar-toggleable-md navbar-inverse fixed-top bg-inverse" id="admin_menu">
    <button class="navbar-toggler navbar-toggler-right hidden-lg-up" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="<?= URL::to('/')?>/admin/categories">Dashboard</a>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <div class="mr-auto">
            <ul class="navbar-nav mobile-nav">
                <li class="nav-item active">
                <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/products">Products</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/customers">Customers</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/orders">Invoices</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/returns">Returns</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/repairs">Repairs</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/reports">Reports</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/payments">Payments</a>
                </li>
            </ul>

            <hr>

            <ul class="navbar-nav mobile-nav">
                <li class="nav-item">
                <a class="nav-link" href="/admin/inquiries">Inquiries</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/theshow">Show</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/admin/inventory">Inventory</a>
                </li>                
            </ul>
        </div>

    <ul class="navbar-nav">
        <li class="nav-item active">
            <span class="nav-link">Welcome {{ Auth::user()->name}}</span>
        </li>
        <li class="nav-item active">
            <a class="nav-link btn btn-primary" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                Logout
            </a>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
    
    <!--<form class="form-inline mt-2 mt-md-0">
        <input class="form-control mr-sm-2" type="text" placeholder="Search">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>-->
    </div>
</nav>