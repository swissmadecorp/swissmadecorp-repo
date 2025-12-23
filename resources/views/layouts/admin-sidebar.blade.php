<nav class="col-sm-3 col-md-2 hidden-xs-down bg-faded sidebar">
    @role('superadmin')
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {{ (Request::is('admin') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin">Overview<span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Inventory</a>
            <div class="dropdown-menu">
                <a class="dropdown-item  {{ (Request::is('admin/products') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/lvproducts">Products</a>
                <a class="dropdown-item {{ (Request::is('admin/categories') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/categories">Categories</a>
                <a class="dropdown-item {{ (Request::is('admin/exports') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/exports">Export to Excel</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item {{ (Request::is('admin/inventory') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/inventory">Inventory Adjuster</a>
                <a class="dropdown-item {{ (Request::is('admin/theshow') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/theshow">Products at the Show</a>
                <a class="dropdown-item {{ (Request::is('admin/inquiries') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/inquiries">Inquiries</a>
                <!-- <div class="dropdown-divider"></div> -->
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Invoices</a>
            <div class="dropdown-menu">
                <a class="dropdown-item {{ (Request::is('admin/invoices') ? 'active' : '') }}" onclick="javascript:localStorage.removeItem('currentorderstatus');" href="<?= URL::to('/') ?>/admin/lvinvoices">Invoices</a>
                <a class="dropdown-item {{ (Request::is('admin/estimates') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/estimates">Orders</a>
                <a class="dropdown-item {{ (Request::is('admin/reports') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/reports">Reports</a>
                <a class="dropdown-item {{ (Request::is('admin/payments') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/payments">Payments</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item {{ (Request::is('admin/customers') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/customers">Customers</a>
                <a class="dropdown-item {{ (Request::is('admin/returns') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/returns">Returns</a>
                <a class="dropdown-item {{ (Request::is('admin/repairs') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/repairs">Repairs</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item {{ (Request::is('admin/discountrules') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/discountrules">Discount Rules</a>
                <!-- <div class="dropdown-divider"></div> -->
            </div>
        </li>
    </ul>
    <hr>
    @endrole

    @role('superadmin')
    <ul class="nav nav-pills flex-column">
        <li class="nav-item dropdown">
            <a class="nav-link {{ (Request::is('admin/lvreminders') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/lvreminders">Reminders</a>
        </li>
    </ul>
    <ul class="nav nav-pills flex-column">
        <li class="nav-item dropdown">
            <a class="nav-link {{ (Request::is('admin/ebayToken') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/ebayToken">eBay Token</a>
        </li>
    </ul>
    <hr>
    @endrole

    @role('superadmin')
    <!-- <ul class="nav nav-pills flex-column">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">3rd Party Lister</a>
            <div class="dropdown-menu">
                <a class="dropdown-item {{ (Request::is('admin/ebay') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/ebay">Ebay</a>
                <a class="dropdown-item {{ (Request::is('admin/amazon') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/amazon">Amazon</a>
                <a class="dropdown-item {{ (Request::is('admin/walmart') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/walmart">Walmart</a>
            </div>
        </li>
    </ul>
    <hr> -->
    @endrole

    @role('superadmin')
    <ul class="nav nav-pills flex-column">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Credentials</a>
            <div class="dropdown-menu">
                <a class="dropdown-item {{ (Request::is('admin/users') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/users">Users</a>
                <a class="dropdown-item {{ (Request::is('admin/roles') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/roles">Roles</a>
                <a class="dropdown-item {{ (Request::is('admin/permissions') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/permissions">Permissions</a>
            </div>
        </li>
    </ul>
    <hr>
    @endrole

    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {{ (Request::is('admin/posts') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/posts">Posts/Blog</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ (Request::is('admin/massmail') ? 'active' : '') }}" href="<?= URL::to('/') ?>/admin/massmail">Mass Mail</a>
        </li>
    </ul>
</nav>