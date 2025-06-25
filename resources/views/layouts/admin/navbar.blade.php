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
            <li class="menu-item {{ Request::segment(2) == 'user-management' ? 'active' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div>User Management</div>
                </a>
                <ul class="menu-sub">
                    <!-- Permission -->
                    <li class="menu-item {{ Request::segment(3) == 'user' ? 'active' : '' }}">
                        <a href="{{ route('user-management.user') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-user-circle"></i>
                            <div>User</div>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Event Management -->
            <li class="menu-item {{ Request::segment(2) == 'event-organizer' ? 'active' : '' }}">
                <a href="{{ route('event-organizer') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-building-minus"></i>
                    <div>Event Organizer</div>
                </a>
            </li>

            <li class="menu-item {{ Request::segment(2) == 'event' ? 'active' : '' }}">
                <a href="{{ route('event') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-ticket"></i>
                    <div>Event</div>
                </a>
            </li>

            <!-- Merchandise Management -->
            <li class="menu-item {{ Request::segment(2) == 'merchandise' ? 'active' : '' }}">
                <a href="{{ url('merchandise.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-shopping-bag"></i>
                    <div>Merchandise</div>
                </a>
            </li>


            <!-- Review Moderation -->
            <li class="menu-item {{ Request::segment(2) == 'review' ? 'active' : '' }}">
                <a href="{{ url('review.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-message-check"></i>
                    <div>Review Moderation</div>
                </a>
            </li>


            <!-- Order Monitoring -->
            <li class="menu-item {{ Request::segment(2) == 'order' ? 'active' : '' }}">
                <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-receipt"></i>
                    <div>Orders</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Request::is('admin/order') ? 'active' : '' }}">
                        <a href="{{ url('order.index') }}" class="menu-link">
                            <div>Event Orders</div>
                        </a>
                    </li>

                    <li class="menu-item {{ Request::is('admin/merch-order') ? 'active' : '' }}">
                        <a href="{{ url('merchandise.order.index') }}" class="menu-link">
                            <div>Merchandise Orders</div>
                        </a>
                    </li>

                </ul>
            </li>

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
                    <li class="menu-item {{ Request::segment(3) == 'payment-method' ? 'active' : '' }}">
                        <a href="{{ route('config.payment-method') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-cash-banknote"></i>
                            <div>Payment Method</div>
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
