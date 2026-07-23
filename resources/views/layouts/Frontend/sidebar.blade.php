<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('books.index') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <i class="bx bx-book-open text-primary fs-2"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">كتبي</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">الأرشيف</span>
        </li>
        <li class="menu-item {{ request()->routeIs('books.index', 'books.show', 'books.edit') ? 'active' : '' }}">
            <a href="{{ route('books.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-library"></i>
                <div>جميع الكتب</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('books.create') || request()->boolean('create') ? 'active' : '' }}">
            <a href="{{ route('books.index', ['create' => 1]) }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-plus-circle"></i>
                <div>إضافة كتاب</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('books.trash') ? 'active' : '' }}">
            <a href="{{ route('books.trash') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trash"></i>
                <div>سلة المحذوفات</div>
            </a>
        </li>
    </ul>
</aside>
