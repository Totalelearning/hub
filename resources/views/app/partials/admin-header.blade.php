{{-- Page Loader --}}
<div class="pageloader">
    <div class="container h-100">
        <div class="row justify-content-center align-items-center text-center h-100">
            <div class="col-12 mb-auto pt-4"></div>
            <div class="col-auto">
                <img src="{{ asset('vendor/learninguiux/img/nfc-logo.png') }}" alt="TotaleLearning Hub" class="height-60 mb-3">
                <p class="h3 mb-4">Loading...</p>
                <div class="loader11 mb-2 mx-auto"></div>
            </div>
            <div class="col-12 mt-auto pb-4">
                <p class="text-secondary">Please wait...</p>
            </div>
        </div>
    </div>
</div>

<header class="adminuiux-header admin-refined-header">
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">

            {{-- Sidebar toggle --}}
            <button class="btn btn-link btn-square sidebar-toggler" type="button" onclick="initSidebar()">
                <i class="sidebar-svg" data-feather="menu"></i>
            </button>

            {{-- Logo --}}
            <a class="navbar-brand" href="{{ route('app.admin.assignments') }}">
                <img src="{{ asset('vendor/learninguiux/img/nfc-logo.png') }}" alt="TotaleLearning Hub" style="height:36px;width:auto;">
                <div>
                    <span class="h4">Totale<span class="fw-bold">Learning</span> <span>Hub</span></span>
                    <p class="company-tagline">{{ auth()->user()->systemRoleLabel() }}</p>
                </div>
            </a>

            {{-- Search bar (desktop) --}}
            <div class="flex-grow-1 px-3 justify-content-center">
                <div class="input-group input-group-md rounded search-wrap maxwidth-300 mx-auto d-none d-lg-flex shadow-sm">
                    <span class="input-group-text border-0 bg-none">
                        <i class="bi bi-search"></i>
                    </span>
                    <input class="form-control border-0 bg-none" type="search" placeholder="Search here..." id="searchglobal">
                </div>
            </div>

            {{-- Right side icons --}}
            <div class="ms-auto d-flex align-items-center">

                {{-- Mobile search toggle --}}
                <button class="btn btn-link btn-square btn-icon btn-link-header d-lg-none" type="button" onclick="openSearch()">
                    <i data-feather="search"></i>
                </button>

                {{-- Dark mode toggle (hidden) --}}

                {{-- Learner view button removed — use profile dropdown instead --}}

                {{-- Profile dropdown --}}
                @auth
                <div class="dropdown d-inline-block">
                    <a class="dropdown-toggle btn btn-link btn-square btn-link-header style-none no-caret px-0"
                       id="adminHeaderProfileDd" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <figure class="avatar avatar-28 rounded-circle coverimg align-middle">
                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="">
                        </figure>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end width-300 px-0 sm-mi-45px" aria-labelledby="adminHeaderProfileDd">
                        <div class="px-3 py-2 border-bottom">
                            <p class="mb-0 fw-semibold">{{ auth()->user()->name }}</p>
                            <p class="mb-0 small text-secondary">{{ auth()->user()->email }}</p>
                        </div>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i data-feather="user" class="avatar avatar-18 me-1"></i> My Profile
                        </a>
                        <a class="dropdown-item" href="{{ route('app.feed') }}">
                            <i data-feather="layout" class="avatar avatar-18 me-1"></i> My Learning
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item theme-red">
                                <i data-feather="power" class="avatar avatar-18 me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
                @endauth

            </div>
        </div>
    </nav>

    {{-- Global search overlay (mobile) --}}
    <div class="adminuiux-search-full">
        <div class="row gx-2 align-items-center">
            <div class="col-auto">
                <button class="btn btn-link btn-square" type="button" onclick="closeSearch()">
                    <i data-feather="arrow-left"></i>
                </button>
            </div>
            <div class="col">
                <input class="form-control pe-0 border-0" type="search" placeholder="Search modules, paths...">
            </div>
        </div>
    </div>
</header>
