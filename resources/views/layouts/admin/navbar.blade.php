<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
    <div class="container-fluid d-flex h-100">
        <ul class="menu-inner">
            <li class="menu-item {{ Request::is('~admin-panel') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link menu-active">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
            <!-- Dashboards -->
            <li class="menu-item">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i>
                    <div data-i18n="Dashboards">Setting</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="index.html" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-chart-pie-2"></i>
                            <div data-i18n="Analytics">Analytics</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="dashboards-crm.html" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-3d-cube-sphere"></i>
                            <div data-i18n="CRM">CRM</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="dashboards-ecommerce.html" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-atom-2"></i>
                            <div data-i18n="eCommerce">eCommerce</div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>