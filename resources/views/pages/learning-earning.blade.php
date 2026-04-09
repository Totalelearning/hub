@extends('layouts.learninguiux')

@section('title', 'Learning AdminUIUX - Bootstrap HTML Admin template - adminuiux.com')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-bs-spy="scroll" data-bs-target="#list-example" data-bs-smooth-scroll="true" tabindex="0" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
<!-- Pageloader -->
<div class="pageloader">
    <div class="container h-100">
        <div class="row justify-content-center align-items-center text-center h-100">
            <div class="col-12 mb-auto pt-4"></div>
            <div class="col-auto">
                <img src="{{ asset('vendor/learninguiux/img/logo.svg') }}" alt="" class="height-60 mb-3">
                <p class="h6 mb-0">AdminUIUX</p>
                <p class="h3 mb-4">Learning</p>
                <div class="loader11 mb-2 mx-auto"></div>
            </div>
            <div class="col-12 mt-auto pb-4">
                <p class="text-secondary">Please wait we are preparing awesome things to preview...</p>
            </div>
        </div>
    </div>
</div>
      <!-- standard header -->
<header class="adminuiux-header">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">

            <!-- main sidebar toggle -->
            <button class="btn btn-link btn-square sidebar-toggler" type="button" onclick="initSidebar()">
                <i class="sidebar-svg" data-feather="menu"></i>
            </button>

            <!-- logo -->
            <a class="navbar-brand" href="{{ route('preview.learning-dashboard') }}">
                <img data-bs-img="light" src="{{ asset('vendor/learninguiux/img/logo-light.svg') }}" alt="">
                <img data-bs-img="dark" src="{{ asset('vendor/learninguiux/img/logo.svg') }}" alt="">
                <div class="">
                    <span class="h4">Learning<span class="fw-bold">UI</span><span>UX</span></span>
                    <p class="company-tagline">AdminUIUX HTML template</p>
                </div>
            </a>

            <!-- search -->
            <div class="flex-grow-1 px-3 justify-content-center">
                <div class="input-group input-group-md rounded search-wrap maxwidth-300 mx-auto d-none d-lg-flex shadow-sm">
                    <span class="input-group-text border-0 bg-none">
                        <i class="bi bi-search"></i>
                    </span>
                    <input class="form-control border-0 bg-none" type="search" placeholder="Search here..." id="searchglobal">
                </div>
            </div>

            <!-- right icons button -->
            <div class="ms-auto">
                <!-- global search toggle -->
                <button class="btn btn-link btn-square btn-icon btn-link-header d-lg-none" type="button" onclick="openSearch()">
                    <i data-feather="search"></i>
                </button>

                <!-- dark mode -->
                <button class="btn btn-link btn-square btnsunmoon btn-link-header" id="btn-layout-modes-dark-page">
                    <i class="sun mx-auto" data-feather="sun"></i>
                    <i class="moon mx-auto" data-feather="moon"></i>
                </button>

                <!-- application list dropdown -->
                <div class="dropdown d-none d-sm-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-feather="grid"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end width-300 pt-0 px-0 overflow-hidden">
                        <div class="py-3 mb-2 dropdown-dontclose text-center">
                            <h5 class="mb-0">Applications</h5>
                            <p class="text-secondary small">Make your app innovative</p>
                        </div>
                        <div class="px-2">
                            <div class="row g-2 text-center mb-2">
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-bank fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Finance</p>
                                        <p class="fs-12 opacity-50 mb-2">Accounting</p>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-globe fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Network</p>
                                        <p class="fs-12 opacity-50 mb-2">Stabilize</p>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-box fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Inventory</p>
                                        <p class="fs-12 opacity-50 mb-2">Assuring</p>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-folder fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Project</p>
                                        <p class="fs-12 opacity-50 mb-2">Management</p>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-people fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Social</p>
                                        <p class="fs-12 opacity-50 mb-2">Tracking</p>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="dropdown-item square-item position-relative" href="#">
                                        <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                                            <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                        </div>
                                        <div class="avatar avatar-40 rounded mb-2">
                                            <i class="bi bi-journal-bookmark fs-4 mx-0"></i>
                                        </div>
                                        <p class="mb-0">Learning</p>
                                        <p class="fs-12 opacity-50 mb-2">Make-easy</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <a class="btn btn-link text-center" href="{{ route('preview.components') }}">View all
                                <i class="bi bi-arrow-right fs-14"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- language dropdown -->
                <div class="dropdown d-none d-sm-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false"> <i class="bi bi-translate"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item active" data-value="EN">EN - English</a></li>
                        <li><a class="dropdown-item" data-value="FR">FR - French</a></li>
                        <li><a class="dropdown-item" data-value="CH">CH - Chinese</a></li>
                        <li><a class="dropdown-item" data-value="HI">HI - Hindi</a></li>
                    </ul>
                </div>

                <!-- notification dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="btn btn-link btn-square btn-icon btn-link-header dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-feather="bell"></i>
                        <span class="position-absolute top-0 end-0 badge rounded-pill bg-danger p-1">
                            <small>9+</small>
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dd sm-mi-95px">
                        <li>
                            <a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-pink">
                                            <i class="bi bi-gift text-white"></i>
                                        </figure>
                                    </div>
                                    <div class="col">
                                        <p class="mb-2 small">Congratulation! Your admission <span class="fw-bold">#H10215</span> has been processed successfully.</p>
                                        <span class="row">
                                            <span class="col"><span class="badge badge-light rounded-pill text-bg-warning small">Directory</span></span>
                                            <span class="col-auto small opacity-75">1:00 am</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-success">
                                            <i class="bi bi-patch-check text-white"></i>
                                        </figure>
                                    </div>
                                    <div class="col">
                                        <p class="mb-2 small">Your assignment class 10<sup>th</sup> with IS <span class="fw-bold">#H10215</span> checked now.</p>
                                        <span class="row">
                                            <span class="col"><span class="badge badge-light rounded-pill text-bg-primary small">System</span></span>
                                            <span class="col-auto small opacity-75">1:00 am</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item p-2" href="#">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-info">
                                            <i class="bi bi-clipboard-check text-white"></i>
                                        </figure>
                                    </div>
                                    <div class="col">
                                        <p class="mb-2 small">User <span class="fw-bold">Rahana</span> has updated <span class="fw-bold">#H10215</span> <b>Harshita</b>'s Profile.</p>
                                        <span class="row">
                                            <span class="col"><span class="badge badge-light rounded-pill text-bg-success small">team</span></span>
                                            <span class="col-auto small opacity-75">1:00 am</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-item p-2">
                                <div class="row gx-3">
                                    <div class="col-auto">
                                        <figure class="avatar avatar-40 rounded-circle bg-warning ">
                                            <i class="bi bi-bell text-white"></i>
                                        </figure>
                                    </div>
                                    <div class="col">
                                        <p class="mb-2 small">Your subscription going to expire soon. Please <a href="{{ route('preview.learning-mysubscription') }}">upgrade</a> to get service interrupt
                                            free.</p>
                                        <p class="opacity-75 small">4 days ago</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="text-center">
                            <button class="btn btn-link text-center">
                                View all <i class="bi bi-arrow-right fs-14"></i>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- profile dropdown -->
                <div class="dropdown d-inline-block">
                    <a class="dropdown-toggle btn btn-link btn-square btn-link-header style-none no-caret px-0" id="userprofiledd" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="row gx-0 d-inline-flex">
                            <div class="col-auto align-self-center">
                                <figure class="avatar avatar-28 rounded-circle coverimg align-middle">
                                    <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="" id="userphotoonboarding2">
                                </figure>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end width-300 px-0 sm-mi-45px " aria-labelledby="userprofiledd">
                        <div class="px-2">
                            <a href="{{ route('preview.learning-myprofile') }}" class="dropdown-item">
                                <div class="row gx-3">
                                    <div class="col-auto ">
                                        <figure class="avatar avatar-50 rounded-circle coverimg align-middle">
                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="">
                                        </figure>
                                    </div>
                                    <div class="col align-self-center ">
                                        <h5 class="mb-1">AdminUIUX</h5>
                                        <p class="small"><i class="bi bi-trophy me-2"></i> 3 Courses</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="px-2">
                            <div>
                                <a class="dropdown-item" href="{{ route('preview.learning-myprofile') }}"><i data-feather="user" class="avatar avatar-18 me-1"></i> My Profile</a>
                            </div>
                            <div>
                                <a class="dropdown-item" href="{{ route('preview.learning-dashboard') }}">
                                    <div class="row g-0">
                                        <div class="col align-self-center"><i data-feather="layout" class="avatar avatar-18 me-1"></i>
                                            My Dashboard
                                        </div>
                                        <div class="col-auto avatar-group">
                                            <figure class="avatar avatar-20 coverimg rounded-circle">
                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-1.jpg') }}" alt="">
                                            </figure>
                                            <figure class="avatar avatar-20 coverimg rounded-circle">
                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-2.jpg') }}" alt="">
                                            </figure>
                                            <figure class="avatar avatar-20 coverimg rounded-circle">
                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-4.jpg') }}" alt="">
                                            </figure>
                                            <div class="avatar avatar-20 bg-theme-1 rounded-circle text-center align-middle">
                                                <small class="fs-10 align-middle">9+</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a class="dropdown-item" href="{{ route('preview.learning-earning') }}">
                                    <i data-feather="dollar-sign" class="avatar avatar-18 me-1"></i> Earning
                                </a>
                            </div>
                            <div>
                                <a class="dropdown-item" href="{{ route('preview.learning-mysubscription') }}">
                                    <div class="row">
                                        <div class="col"><i data-feather="gift" class="avatar avatar-18 me-1"></i>
                                            Subscription</div>
                                        <div class="col-auto">
                                            <p class="small text-success">Upgrade</p>
                                        </div>
                                        <div class="col-auto"><span class="arrow bi bi-chevron-right"></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="dropdown open-left dropdown-dontclose">
                                <a class="dropdown-item" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                                    <div class="row">
                                        <div class="col"><i class="bi bi-translate avatar avatar-18 me-1"></i> Language
                                        </div>
                                        <div class="col-auto"><small class="vm">EN - English</small> <i class="bi bi-translate"></i></div>
                                        <div class="col-auto"><span class="arrow bi bi-chevron-right"></span></div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div><a class="dropdown-item active" data-value="EN">EN - English</a></div>
                                    <div><a class="dropdown-item" data-value="FR">FR - French</a></div>
                                    <div><a class="dropdown-item" data-value="CH">CH - Chinese</a></div>
                                    <div><a class="dropdown-item" data-value="HI">HI - Hindi</a></div>
                                </div>
                            </div>
                            <div>
                                <a class="dropdown-item" href="{{ route('preview.learning-settings') }}">
                                    <i data-feather="settings" class="avatar avatar-18 me-1"></i> Account Setting
                                </a>
                            </div>
                            <div>
                                <a class="dropdown-item theme-red" href="{{ route('preview.learning-login') }}">
                                    <i data-feather="power" class="avatar avatar-18 me-1"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <!-- search global wrap -->
    <div class="adminuiux-search-full">
        <div class="row gx-2 align-items-center">
            <div class="col-auto">
                <!-- close global search toggle -->
                <button class="btn btn-link btn-square " type="button" onclick="closeSearch()">
                    <i data-feather="arrow-left"></i>
                </button>
            </div>
            <div class="col">
                <input class="form-control pe-0 border-0" type="search" placeholder="Type something here...">
            </div>
            <div class="col-auto">

                <!-- filter dropdown -->
                <div class="dropdown input-group-text border-0 p-0">
                    <button class="dropdown-toggle btn btn-link btn-square no-caret" type="button" id="searchfilter2" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-feather="sliders"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-dontclose width-300">
                        <ul class="nav adminuiux-nav" id="searchtab2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="searchall-tab2" data-bs-toggle="tab" data-bs-target="#searchall2" type="button" role="tab" aria-controls="searchall2" aria-selected="true">All</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="searchorders-tab2" data-bs-toggle="tab" data-bs-target="#searchorders2" type="button" role="tab" aria-controls="searchorders2" aria-selected="false" tabindex="-1">Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="searchcontacts-tab2" data-bs-toggle="tab" data-bs-target="#searchcontacts2" type="button" role="tab" aria-controls="searchcontacts2" aria-selected="false" tabindex="-1">Contacts</button>
                            </li>
                        </ul>
                        <div class="tab-content py-3" id="searchtabContent">
                            <div class="tab-pane fade active show" id="searchall2" role="tabpanel" aria-labelledby="searchall-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Search apps</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch1">
                                                    <label class="form-check-label" for="searchswitch1"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Include Pages</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch2" checked="">
                                                    <label class="form-check-label" for="searchswitch2"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Internet resource</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch3" checked="">
                                                    <label class="form-check-label" for="searchswitch3"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">News and Blogs</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch4">
                                                    <label class="form-check-label" for="searchswitch4"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="searchorders2" role="tabpanel" aria-labelledby="searchorders-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Show order ID</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch5">
                                                    <label class="form-check-label" for="searchswitch5"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">International Order</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch6" checked="">
                                                    <label class="form-check-label" for="searchswitch6"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Taxable Product</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch7" checked="">
                                                    <label class="form-check-label" for="searchswitch7"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Published Product</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch8">
                                                    <label class="form-check-label" for="searchswitch8"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="searchcontacts2" role="tabpanel" aria-labelledby="searchcontacts-tab2">
                                <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Have email ID</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch9">
                                                    <label class="form-check-label" for="searchswitch9"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Have phone number</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch10" checked="">
                                                    <label class="form-check-label" for="searchswitch10"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Photo available</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch11" checked="">
                                                    <label class="form-check-label" for="searchswitch11"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col">Referral</div>
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="searchswitch12">
                                                    <label class="form-check-label" for="searchswitch12"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="">
                            <div class="row">
                                <div class="col"><button class="btn btn-link">Reset</button></div>
                                <div class="col-auto">
                                    <button class="btn btn-theme">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

        <!-- page wrapper -->
        <div class="adminuiux-wrap">

          <!-- Standard sidebar -->
          <!-- Standard sidebar -->
<div class="adminuiux-sidebar shadow-sm">
    <div class="adminuiux-sidebar-inner">

        <ul class="nav flex-column menu-active-line">
            <!-- learning sidebar -->
            <li class="nav-item">
                <a href="{{ route('preview.learning-dashboard') }}" class="nav-link">
                    <i class="menu-icon bi bi-columns-gap"></i>
                    <span class="menu-name">Dashboard</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="javascrit:void(0)" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="menu-icon bi bi-mortarboard"></i>
                    <span class="menu-name">Students</span>
                </a>
                <div class="dropdown-menu">
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-student-home') }}" class="nav-link">
                            <i class="menu-icon bi bi-house-gear"></i>
                            <span class="menu-name">Academic</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-attendance') }}" class="nav-link">
                            <i class="menu-icon bi bi-clipboard-check"></i>
                            <span class="menu-name">Attendance</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-student-all') }}" class="nav-link">
                            <i class="menu-icon bi bi-people"></i>
                            <span class="menu-name">All Students</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-student-profile') }}" class="nav-link">
                            <i class="menu-icon bi bi-people"></i>
                            <span class="menu-name">Profile <i class="bi bi-star-fill text-warning fs-14"></i></span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a href="javascrit:void(0)" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="menu-icon bi bi-journals"></i>
                    <span class="menu-name">Courses</span>
                </a>
                <div class="dropdown-menu">
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-courses-in-school') }}" class="nav-link">
                            <i class="menu-icon bi bi-book"></i>
                            <span class="menu-name">In-School</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-courses-e-courses') }}" class="nav-link">
                            <i class="menu-icon bi bi-play-btn"></i>
                            <span class="menu-name">e-Courses</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('preview.learning-courses-part-time') }}" class="nav-link">
                            <i class="menu-icon bi bi-clock-history"></i>
                            <span class="menu-name">Part-Time</span>
                        </a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-calendar') }}" class="nav-link">
                    <i class="menu-icon bi bi-calendar2-range"></i>
                    <span class="menu-name">Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-fees') }}" class="nav-link">
                    <i class="menu-icon bi bi-receipt-cutoff"></i>
                    <span class="menu-name">Fees</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-teachers') }}" class="nav-link">
                    <i class="menu-icon bi bi-people"></i>
                    <span class="menu-name">Teachers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-settings') }}" class="nav-link">
                    <i class="menu-icon" data-feather="settings"></i>
                    <span class="menu-name">Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-aboutus') }}" class="nav-link">
                    <i class="menu-icon bi bi-building"></i>
                    <span class="menu-name">About School</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-pages') }}" class="nav-link">
                    <i class="menu-icon bi bi-layers"></i>
                    <span class="menu-name">Pages</span>
                    <span class="badge text-bg-primary mx-2">50+</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('preview.learning-personalization') }}" class="nav-link">
                    <i class="menu-icon bi bi-palette h4"></i>
                    <span class="menu-name">Personalize ❤️</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('preview.components') }}">
                    <i class="menu-icon bi bi-cpu"></i>
                    <span class="menu-name">Components</span>
                </a>
            </li>
        </ul>

        <div class=" mt-auto "></div>

        <!-- teacher -->
        <ul class="nav flex-column menu-active-line mb-2">
            <li class="nav-item">
                <a href="{{ route('preview.learning-chat-call') }}" class="nav-link">
                    <div class="col-auto">
                        <div class="avatar avatar-30 coverimg rounded d-block align-top">
                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-5.jpg') }}" alt="">
                        </div>
                    </div>
                    <div class="col px-2 menu-name text-start not-iconic">
                        <!-- limit name character-->
                        <p class="mb-0 fs-14 lh-20">Alice Johanson <br><small class="opacity-50">Teacher</small></p>
                    </div>
                    <div class="col-auto not-iconic">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                </a>
            </li>
        </ul>

        <!-- quick links -->
        <div class="px-3 not-iconic">
            <div class="card border-0">
                <div class="card-body p-2">
                    <div class="row gx-2">
                        <div class="col-12 d-flex justify-content-between">
                            <a href="{{ route('preview.learning-courses-e-courses') }}" class="btn btn-square btn-link">
                                <span class="position-relative">
                                    <i data-feather="heart"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success rounded-circle">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                </span>
                            </a>
                            <a href="{{ route('preview.learning-calendar') }}" class="btn btn-square btn-link">
                                <span class="position-relative">
                                    <i data-feather="calendar"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-warning rounded-circle">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                </span>
                            </a>
                            <a href="{{ route('preview.learning-inbox') }}" class="btn btn-square btn-link">
                                <i data-feather="inbox"></i>
                            </a>
                            <a href="{{ route('preview.learning-help-center') }}" class="btn btn-square btn-link">
                                <i data-feather="help-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

            <main class="adminuiux-content has-sidebar" onclick="contentClick()">
              <!-- body content of pages -->

              <!-- breadcrumb  -->
              <div class="container mt-4">
                <div class="row gx-3 align-items-center">
                  <div class="col-12 col-sm">
                    <h5>Earning</h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-myprofile') }}">My Profile</a></li>
                        <li class="breadcrumb-item active bi" aria-current="page">My Earning</li>
                      </ol>
                    </nav>
                  </div>
                  <div class="col-auto py-1">
                    <div class="alert alert-success py-2 mb-0">
                      <p class="small">$1500.00 withdraw in-process</p>
                    </div>
                  </div>
                  <div class="col-auto py-1">
                    <button class="btn btn-theme">
                      <i class="bi bi-bank me-0 me-md-1"></i>
                      <span class="d-none d-md-inline-block">Withdraw</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- content -->
              <div class="container mt-4" id="main-content">

                <!-- Title-->
                <div class="row align-items-center">
                  <div class="col-auto mb-4">
                    <h5>My Earning</h5>
                  </div>
                  <div class="col"></div>
                  <div class="col-auto col-lg-auto ms-auto mb-4">
                    <input type="text" class="form-control d-inline-block w-auto align-middle mx-1 border-0 bg-none" id="daterangepickerranges">
                    <button class="btn btn-square btn-link d-inline-block" onclick="$(this).prev().click()">
                      <i data-feather="calendar"></i>
                    </button>
                  </div>
                  <div class="col-auto mb-4">
                    <button class="btn btn-theme">
                      <i class="bi bi-box-arrow-in-down me-0 me-md-1 align-middle"></i>
                      <span class="">Export</span>
                    </button>
                  </div>
                </div>

                <!-- summary -->
                <div class="row">
                  <!-- Current Balance-->
                  <div class="col-6 col-lg-6 col-xxl-3 mb-4">
                    <div class="card adminuiux-card shadow-sm">
                      <div class="card-body">
                        <div class="avatar avatar-60 text-center rounded bg-info-subtle text-info-emphasis mb-3">
                          <i class="bi bi-wallet h4"></i>
                        </div>
                        <h4 class="mb-0">$ 980.00</h4>
                        <p class="text-secondary small">Current Account Balance</p>
                      </div>
                    </div>
                  </div>

                  <!-- Total earning -->
                  <div class="col-6 col-lg-6 col-xxl-3 mb-4">
                    <div class="card adminuiux-card shadow-sm">
                      <div class="card-body">
                        <div class="avatar avatar-60 text-center rounded bg-success-subtle text-success-emphasis mb-3">
                          <i class="bi bi-cash-stack h4"></i>
                        </div>
                        <h4 class="mb-0">$ 2820.00</h4>
                        <p class="text-secondary small">Total Total Earning
                          <span class="text-success">4% <i class="bi bi-arrow-up"></i>
                          </span>
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Pending payment -->
                  <div class="col-6 col-lg-6 col-xxl-3 mb-4">
                    <div class="card adminuiux-card shadow-sm">
                      <div class="card-body">
                        <div class="avatar avatar-60 text-center rounded bg-warning-subtle text-warning-emphasis mb-3">
                          <i class="bi bi-hourglass-bottom h4"></i>
                        </div>
                        <h4 class="mb-0">$ 645.00</h4>
                        <p class="text-secondary small">Payment Pending</p>
                      </div>
                    </div>
                  </div>

                  <!-- Payout -->
                  <div class="col-6 col-lg-6 col-xxl-3 mb-4">
                    <div class="card adminuiux-card shadow-sm">
                      <div class="card-body">
                        <div class="avatar avatar-60 text-center rounded bg-primary-subtle text-primary-emphasis mb-3">
                          <i class="bi bi-calendar-week h4"></i>
                        </div>
                        <h4 class="mb-0">$ 980.00</h4>
                        <p class="text-secondary small">Next Payout on 2 July 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Chart and zones-->
                <div class=" row">
                  <div class="col-12 col-lg-9 mb-3 mb-lg-0">
                    <div class="card adminuiux-card shadow-sm overflow-hidden mb-4">
                      <div class="card-header">
                        <h6 class="mb-0">Earning Statistics</h6>
                        <p class="small"><span class="text-secondary">Total money received as income source</span></p>
                      </div>
                      <div class="card-body">
                        <!-- chart area -->
                        <div class="height-300 mb-3">
                          <canvas id="earningbyday"></canvas>
                        </div>
                        <div class="row align-items-center">
                          <div class="col-6 col-md-6 col-lg-4 col-xl-3 mb-1">
                            <h4 class="fw-medium mb-1">$5560.50</h4>
                            <p class="small text-secondary">Earning <span class="text-success"><i class="bi bi-arrow-up"></i> 11.5%</span></p>
                          </div>
                          <div class="col-6 col-md-6 col-lg-4 col-xl-3 mb-1">
                            <h4 class="fw-medium mb-1">$4146.50</h4>
                            <p class="small text-secondary">Last Duration <span class="text-success"><i class="bi bi-arrow-up"></i> 3.65%</span></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Zones -->
                  <div class="col-12 col-lg-3">
                    <div class="card adminuiux-card shadow-sm overflow-hidden mb-4">
                      <div class="card-header">
                        <h6 class="mb-0">Expense by Category</h6>
                        <p class="small"><span class="text-secondary">Payments for bills and salaries</span></p>
                      </div>
                      <div class="card-body pb-0">
                        <div class="semidoughnutchart">
                          <div class="expensedatasemidoughnut">
                            <h4 class="mb-0">$ 4154.00</h4>
                            <p class="text-secondary small">Zonal Earning</p>
                          </div>
                          <canvas id="semidoughnutchart" class="mx-auto width-260 height-260" width="240" height="240" style="display: block; box-sizing: border-box; height: 240px; width: 240px;"></canvas>
                        </div>

                        <div class="row">
                          <div class="col-6 col-md-6 mb-4">
                            <p class="text-secondary small mb-2">
                              <span class="me-1 avatar avatar-10 rounded bg-green"></span>
                              Salary
                            </p>
                            <h5 class="ps-3 fw-medium">$ 165.52k <span class="text-success fs-14 fw-normal"><i class="bi bi-caret-up-fill me-1 fs-14"></i> 25.30%</span></h5>
                          </div>
                          <div class="col-6 col-md-6 mb-4">
                            <p class="text-secondary small mb-2">
                              <span class="me-1 avatar avatar-10 rounded bg-yellow"></span>
                              Electricity
                            </p>
                            <h5 class="ps-3 fw-medium">$ 265.85k <span class="text-success fs-14 fw-normal"><i class="bi bi-caret-up-fill me-1"></i> 21.42%</span></h5>
                          </div>
                          <div class="col-6 col-md-6 mb-4">
                            <p class="text-secondary small mb-2">
                              <span class="me-1 avatar avatar-10 rounded bg-orange"></span>
                              Food
                            </p>
                            <h5 class="ps-3 fw-medium">$ 356.26k <span class="text-success fs-14 fw-normal"><i class="bi bi-caret-up-fill me-1"></i> 20.18%</span></h5>
                          </div>
                          <div class="col-6 col-md-6 mb-4">
                            <p class="text-secondary small mb-2">
                              <span class="me-1 avatar avatar-10 rounded bg-purple"></span>
                              Maintanance
                            </p>
                            <h5 class="ps-3 fw-medium">$ 185.65 <span class="text-success fs-14 fw-normal"><i class="bi bi-caret-up-fill me-1"></i> 15.65%</span></h5>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>

                <!-- earning transaction list -->
                <div class="card adminuiux-card shadow-sm mb-4">
                  <div class="card-header">
                    <h6>Earning Transactions</h6>
                  </div>
                  <div class="card-body">
                    <table class="table mb-0" data-show-toggle="true" id="dataTable">
                      <thead>
                        <tr>
                          <th class="">ID</th>
                          <th>Time & Date</th>
                          <th>Amount</th>
                          <th data-breakpoints="xs">Made by</th>
                          <th data-breakpoints="xs sm">Contact info</th>
                          <th data-breakpoints="xs">Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>2054ID</td>
                          <td>
                            <p class="mb-0 fw-medium">9:10 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 130.00</p>
                            <p class="text-secondary small">Net Banking</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-7.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">David Warner</p>
                                <p class="text-secondary small">United Kingdom</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">david@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-yellow">Pending</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>105ID</td>
                          <td>
                            <p class="mb-0 fw-medium">10:30 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 95.00</p>
                            <p class="text-secondary small">Credit Card</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-8.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Winnie John</p>
                                <p class="text-secondary small">Australia</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">winnie@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-blue">Waiting</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>058ID</td>
                          <td>
                            <p class="mb-0 fw-medium">11:30 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-danger">- $ 1500.00</p>
                            <p class="text-secondary small">Withdraw</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/logo-512.png') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">BANK: 89566332559848</p>
                                <p class="text-secondary small">Company Account</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">alicia@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-green">Complete</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>500ID</td>
                          <td>
                            <p class="mb-0 fw-medium">11:55 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 150.00</p>
                            <p class="text-secondary small">Cash</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-2.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Jr. Chen Li</p>
                                <p class="text-secondary small">United Kingdom</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">cheli@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-red">Rejected</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>2054ID</td>
                          <td>
                            <p class="mb-0 fw-medium">12:15 PM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 250.00</p>
                            <p class="text-secondary small">Cash</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-3.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">David Warner</p>
                                <p class="text-secondary small">United Kingdom</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">david@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-yellow">Pending</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>105ID</td>
                          <td>
                            <p class="mb-0 fw-medium">1:30 PM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 180.00</p>
                            <p class="text-secondary small">Cash</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-4.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Winnie John</p>
                                <p class="text-secondary small">Australia</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">winnie@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-blue">Waiting</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>058ID</td>
                          <td>
                            <p class="mb-0 fw-medium">2:20 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 90.00</p>
                            <p class="text-secondary small">Cash</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-5.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Alicia Smith</p>
                                <p class="text-secondary small">United States</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">alicia@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-green">Complete</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>500ID</td>
                          <td>
                            <p class="mb-0 fw-medium">3:30 PM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 190.00</p>
                            <p class="text-secondary small">Cash</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Jr. Chen Li</p>
                                <p class="text-secondary small">United Kingdom</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">cheli@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-red">Rejected</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>2054ID</td>
                          <td>
                            <p class="mb-0 fw-medium">9:10 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 130.00</p>
                            <p class="text-secondary small">Net Banking</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-7.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">David Warner</p>
                                <p class="text-secondary small">United Kingdom</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">david@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-yellow">Pending</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>105ID</td>
                          <td>
                            <p class="mb-0 fw-medium">10:30 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-success">$ 95.00</p>
                            <p class="text-secondary small">Credit Card</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-8.jpg') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">Winnie John</p>
                                <p class="text-secondary small">Australia</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">winnie@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-blue">Waiting</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td>058ID</td>
                          <td>
                            <p class="mb-0 fw-medium">11:30 AM</p>
                            <p class="text-secondary small">9 June 2024</p>
                          </td>
                          <td>
                            <p class="mb-0 fw-medium text-danger">- $ 1500.00</p>
                            <p class="text-secondary small">Withdraw</p>
                          </td>
                          <td>
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <figure class="avatar avatar-40 mb-0 coverimg rounded-circle">
                                  <img src="{{ asset('vendor/learninguiux/img/logo-512.png') }}" alt="">
                                </figure>
                              </div>
                              <div class="col ps-0">
                                <p class="mb-0">BANK: 89566332559848</p>
                                <p class="text-secondary small">Company Account</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="mb-0">alicia@sales..core.com</p>
                            <p class="text-secondary small">+44 8466585****1154</p>
                          </td>
                          <td>
                            <span class="badge badge-sm light bg-green">Complete</span>
                          </td>
                          <td>
                            <div class="dropdown d-inline-block">
                              <a class="btn btn-link no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Receipt</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Email </a></li>
                                <li><a class="dropdown-item theme-red" href="javascript:void(0)">Dispute</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </main>

        </div>

        <!-- theming offcanvas-->
<div class="offcanvas offcanvas-end shadow border-0" tabindex="-1" id="theming" data-bs-scroll="true" data-bs-backdrop="false" aria-labelledby="theminglabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title" id="theminglabel">Personalize</h5>
            <p class="text-secondary small">Make it more like your own</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <h6 class="offcanvas-title">Colors</h6>
        <p class="text-secondary small mb-4">Change colors of templates</p>

        <div class="row mb-4 theme-select">
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default">
                        <i class="bi bi-arrow-clockwise"></i>
                    </span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-blue">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-blue"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-indigo">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-indigo"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-purple">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-purple"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-pink">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-pink"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-red">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-red"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-orange">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-orange"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-yellow">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-yellow"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-green">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-green"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-teal">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-teal"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-cyan">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-cyan"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-grey">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-grey"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-brown">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-brown"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-chocolate">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-chocolate"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="theme-black">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-dark"></span>
                </div>
            </div>
        </div>

        <h6 class="offcanvas-title">Backgrounds</h6>
        <p class="text-secondary small mb-4">Change color for background</p>
        <div class="row mb-4 theme-background">
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-default">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default"><i class="bi bi-arrow-clockwise"></i></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-white">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-white"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-r-gradient">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-r-gradient"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-1">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-1"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-2">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-2"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-3">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-3"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-4">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-4"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-5">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-5"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-6">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-6"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-7">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-7"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-8">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-8"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-9">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-9"></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="gradient-box text-center mb-2" data-title="bg-gradient-10">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-gradient-10"></span>
                </div>
            </div>
        </div>

        <h6 class="offcanvas-title">Sidebar Layout</h6>
        <p class="text-secondary small mb-4">Change sidebar layout style</p>

        <div class="row mb-4 sidebar-layout">
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="adminuiux-sidebar-standard" data-bs-toggle="tooltip" title="None">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default">
                        <i class="bi bi-arrow-clockwise"></i>
                    </span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="adminuiux-sidebar-iconic" data-bs-toggle="tooltip" title="Iconic">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default">
                        <i class="bi bi-bezier h4"></i>
                    </span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="adminuiux-sidebar-boxed" data-bs-toggle="tooltip" title="Boxed">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default">
                        <i class="bi bi-box h5"></i>
                    </span>
                </div>
            </div>
            <div class="col-auto">
                <div class="select-box text-center mb-2" data-title="adminuiux-sidebar-boxed adminuiux-sidebar-iconic" data-bs-toggle="tooltip" title="Iconic+Boxed">
                    <span class="avatar avatar-40 rounded-circle mb-2 bg-default">
                        <i class="bi bi-bounding-box h5"></i>
                    </span>
                </div>
            </div>

        </div>

        <div class="text-center mb-4">
            <a href="{{ route('preview.learning-personalization') }}" class="btn btn-sm btn-outline-theme">More options <i class="bi bi-arrow-right-short"></i></a>
        </div>
    </div>
</div>
          <!-- page footer -->
          <!-- standard footer -->
<footer class="adminuiux-footer has-adminuiux-sidebar mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md col-lg py-2">
                <span class="small">Copyright @2024, Creatively designed by
                    <a href="https://adminuiux.com" target="_blank">LearningUIUX - Adminuiux</a> on Earth ❤️
                </span>
            </div>
            <div class="col-12 col-md-auto col-lg-auto align-self-center">
                <ul class="nav small">
                    <li class="nav-item"><a class="nav-link" href="{{ route('preview.learning-help-center') }}">Help</a></li>
                    <li class="nav-item">|</li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('preview.learning-terms-of-use') }}">Terms of Use</a></li>
                    <li class="nav-item">|</li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('preview.learning-privacy-policy') }}">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- theming action-->
<div class="position-fixed bottom-0 end-0 m-3 z-index-5">
    <button class="btn btn-square btn-theme shadow rounded-circle" type="button" data-bs-toggle="offcanvas" data-bs-target="#theming" aria-controls="theming"><i class="bi bi-palette"></i></button>
    <br>
    <button class="btn btn-theme btn-square shadow mt-2 d-none rounded-circle" id="backtotop"><i class="bi bi-arrow-up"></i></button>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/learninguiux/js/learning/learning-earning.js') }}"></script>
@endpush





