<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
    <div class="container-fluid d-flex h-100">
        <ul class="menu-inner">
            <!-- Dashboard -->
            <li class="menu-item {{ Request::is('~admin-panel') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link menu-active">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <!-- Config Section -->
            <li class="menu-item {{ Request::segment(2) == 'config' ? 'active' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i>
                    <div>Config</div>
                </a>
                <ul class="menu-sub">
                    <!-- Permission -->
                    <li class="menu-item {{ Request::segment(3) == 'permission' ? 'active' : '' }}">
                        <a href="{{ route('config.permission') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-key"></i>
                            <div>Permission</div>
                        </a>
                    </li>

                    <!-- Assign Permission -->
                    <li class="menu-item {{ Request::segment(3) == 'assign-permission' ? 'active' : '' }}">
                        <a href="{{ route('config.assign') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-user-cog"></i>
                            <div>Assign Permission</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::segment(3) == 'setting' ? 'active' : '' }}">
                        <a href="{{ route('config.setting') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-settings"></i>
                            <div>Setting</div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>