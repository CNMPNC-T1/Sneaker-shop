<div class="left-side-menu left-side-menu-detached">

    <div class="leftbar-user">
        <a href="javascript: void(0);">
            <img src="{{ asset('assets_admin/images/users/avatar-1.jpg') }}" alt="user-image" height="42"
                class="rounded-circle shadow-sm">
            {{-- <span class="leftbar-user-name" style="text-transform: capitalize;">{{ Auth::user()->name }}</span> --}}
        </a>
    </div>

    <!--- Sidemenu -->
    <ul class="metismenu side-nav">

        <li class="side-nav-title side-nav-item">Navigation</li>

        <li class="side-nav-item">
            <a href="/admin" class="side-nav-link">
                <i class="uil-home-alt"></i>
                <span> Dashboards </span>
            </a>
        </li>

        <li class="side-nav-title side-nav-item">Apps</li>

        <li class="side-nav-item {{ request()->routeIs('admin.chat') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.chat') }}" class="side-nav-link active">
                <i class="uil-comments-alt"></i>
                <span> Chat </span>
            </a>
        </li>
        <li class="side-nav-item">
            <a href="{{ route('admin.bill.index') }}" class="side-nav-link">
                <i class="mdi mdi-cart"></i>
                <span> Bills </span>
            </a>
        </li>
        <li class="side-nav-item">
            <a href="{{ route('admin.GoodsReceipt.index') }}" class="side-nav-link">
                <i class="mdi mdi-cart"></i>
                <span> Orders </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="javascript: void(0);" class="side-nav-link">
                <i class="uil-store"></i>
                <span> Management </span>
                <span class="menu-arrow"></span>
            </a>
            <ul class="side-nav-second-level" aria-expanded="false">
                <li>
                    <a href="{{ route('admin.brand.index') }}">Brands</a>
                </li>

                <li>
                    <a href="{{ route('admin.category.index') }}">Categories</a>
                </li>

                <li>
                    <a href="{{ route('admin.product.index') }}">Products</a>
                </li>

                <li>
                    <a href="{{ route('admin.user.index') }}">Users</a>
                </li>

                <li>
                    <a href="{{ route('admin.provider.index') }}">Providers</a>
                </li>

                <li>
                    <a href="{{ route('admin.provide.index') }}">Provide</a>
                </li>

            </ul>
        </li>

    </ul>


    <!-- End Sidebar -->

    <div class="clearfix"></div>
    <!-- Sidebar -left -->

</div>