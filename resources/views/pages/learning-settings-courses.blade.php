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

              <!-- breadcrumb -->
              <div class="container mt-4">
                <div class="row gx-3 align-items-center">
                  <div class="col-12 col-sm">
                    <h5>Setting Users</h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-settings') }}">Settings</a></li>
                        <li class="breadcrumb-item active bi" aria-current="page">Timing</li>
                      </ol>
                    </nav>
                  </div>
                </div>
              </div>

              <!-- content -->
              <div class="container mt-4" id="main-content">

                <!-- cover-->
                <div class="card adminuiux-card shadow-sm overflow-hidden z-index-0 mb-0 pt-5">
                  <figure class="coverimg start-0 top-0 w-100 h-100 z-index-0 position-absolute overlay-gradiant">
                    <div class="position-absolute top-0 end-0 m-2"><button class="btn btn-theme" onclick="$(this).next().click()"><i class="bi bi-camera"></i> Change Cover</button> <input type="file" class="d-none"></div>
                    <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/pet-1.jpg') }}" class="mw-100" alt="">
                  </figure>
                  <div class="card-body text-center text-white z-index-1">
                    <div class="d-inline-block position-relative w-auto mx-auto my-3">
                      <figure class="avatar avatar-150 coverimg rounded-circle">
                        <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="">
                      </figure>
                      <div class="position-absolute bottom-0 end-0 z-index-1 h-auto">
                        <button class="btn btn-lg btn-theme btn-square" onclick="$(this).next().click()">
                          <i class="bi bi-camera"></i>
                        </button>
                        <input type="file" class="d-none">
                      </div>
                    </div>
                    <h4>Learning UIUX</h4>
                    <p class="opacity-75 mb-3">guest@adminuiux.com</p>
                  </div>
                </div>

                <!-- navigation -->
                <div class="position-sticky z-index-1" style="top:5.5rem">
                  <div class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-body p-0 overflow-x-auto">
                      <ul class="nav nav-pills adminuiux-nav-pills flex-nowrap">
                        <li class="nav-item"><a class="nav-link" href="{{ route('preview.learning-settings') }}">
                            <div class="avatar avatar-28 icon">
                              <i data-feather="user"></i>
                            </div>
                            <div class="col text-truncated">
                              <p class="h6 mb-0">About School</p>
                              <p class="small opacity-75">School Details</p>
                            </div>
                          </a></li>
                        <li class="nav-item">
                          <a class="nav-link active" aria-current="page" href="{{ route('preview.learning-settings-courses') }}">
                            <div class="avatar avatar-28 icon"><i class="bi bi-people fs-4"></i></div>
                            <div class="col text-truncated">
                              <p class="h6 mb-0">Courses</p>
                              <p class="small opacity-75">School and E-Courses</p>
                            </div>
                          </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('preview.learning-settings-timing') }}">
                            <div class="avatar avatar-28 icon">
                              <i data-feather="clock"></i>
                            </div>
                            <div class="col text-truncated">
                              <p class="h6 mb-0">School Timing</p>
                              <p class="small opacity-75">Operating hours</p>
                            </div>
                          </a></li>
                        <li class="nav-item">
                          <a class="nav-link" href="{{ route('preview.learning-settings-payment') }}">
                            <div class="avatar avatar-28 icon"><i class="bi bi-cash-stack fs-4"></i></div>
                            <div class="col text-truncated">
                              <p class="h6 mb-0">Payment</p>
                              <p class="small opacity-75">Online, Devices, Cash</p>
                            </div>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- settings -->
                <div class="card adminuiux-card mb-4" data-bs-target="#navbarpill" data-bs-spy="scroll">
                  <div class="card-body pb-0">
                    <h6 class="mb-3">Courses</h6>

                    <div class="row mb-3">
                      <!-- course summary -->
                      <div class="col-12 col-md-12 col-lg-4">
                        <div class="card mb-2">
                          <div class="card-body">
                            <div class="row align-items-center">
                              <div class="col-auto mb-3">
                                <div class="avatar avatar-50 coverimg rounded" style="background-image: url(&quot;{{ asset('vendor/learninguiux/img/modern-ai-image/tree-6.jpg&quot;') }});">
                                  <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/tree-6.jpg') }}" alt="" style="display: none;">
                                </div>
                              </div>
                              <div class="col mb-3">
                                <h6 class="mb-0">Bio Management</h6>
                                <p class="small">Prof. Kim San</p>
                              </div>
                            </div>
                            <p class="text-secondary small mb-0">Standard 9<sup>th</sup></p>
                            <p class="text-secondary small">In 2hours, Duration 45min</p>
                            <h6 class="mb-1"><i class="bi bi-clock text-secondary me-1"></i> 1:30pm, Today</h6>
                          </div>
                        </div>
                      </div>
                      <!-- course summary -->
                      <div class="col-12 col-md-12 col-lg-4">
                        <div class="card mb-2">
                          <div class="card-body">
                            <div class="row align-items-center">
                              <div class="col-auto">
                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4 mb-3">
                                  <i class="bi bi-journal-check"></i>
                                </div>
                              </div>
                              <div class="col mb-3">
                                <h6 class="mb-0">Assignment 3</h6>
                                <p class="small">IT Data Modal</p>
                              </div>
                            </div>
                            <p class="text-secondary small mb-0">Standard 8<sup>th</sup></p>
                            <p class="text-secondary small">In 3 hours, Duration 30min</p>
                            <h6 class="mb-1"><i class="bi bi-clock text-secondary me-1"></i> 3:30pm, Today</h6>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center mb-3">
                      <div class="col">
                        <p class="h6">Add Lecture/Class</p>
                      </div>
                      <div class="col-auto">
                        <a href="{{ route('preview.learning-teacher-add') }}" class="btn btn-link me-1">
                          <i data-feather="plus"></i> Teacher
                        </a>
                        <button class="btn btn-link">
                          <i data-feather="save"></i> Course
                        </button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-lg-3 text-center mb-3">
                        <figure class="mw-100 height-200 coverimg rounded position-relative mx-auto mb-3">
                          <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/pet-3.jpg') }}" alt="" style="display: none;">
                          <div class="position-absolute top-50 start-50 translate-middle">
                            <button class="btn btn-square btn-accent rounded-circle" onclick="$(this).next().click()">
                              <i class="bi bi-upload"></i>
                            </button>
                          </div>
                          <input type="file" class="d-none">
                        </figure>
                      </div>
                      <div class="col">
                        <div class="row ">
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating mb-3">
                              <select class="form-select">
                                <option>Max Ally</option>
                                <option>Kim San</option>
                                <option selected="">Kiara Alex</option>
                                <option>John Doe</option>
                                <option>Genelia Smith</option>
                              </select>
                              <label>Select Teacher</label>
                            </div>
                          </div>
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating mb-3">
                              <select class="form-select">
                                <option>E-Course</option>
                                <option>Part-Time Course</option>
                                <option selected="">Lecture</option>
                              </select>
                              <label>Class Type</label>
                            </div>
                          </div>
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating mb-3">
                              <select class="form-select">
                                <option>1st Standard</option>
                                <option>2nd Standard</option>
                                <option>3rd Standard</option>
                                <option selected="">4th Standard</option>
                                <option>5th Standard</option>
                                <option>6th Standard</option>
                                <option>7th Standard</option>
                                <option>8th Standard</option>
                                <option>9th Standard</option>
                                <option>10th Standard</option>
                                <option>11th Standard</option>
                                <option>12th Standard</option>
                              </select>
                              <label>Standard</label>
                            </div>
                          </div>
                        </div>
                        <p class="h6 py-2">Schedule Class</p>
                        <div class="row align-items-center">
                          <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating mb-3">
                              <select class="form-select">
                                <option>Same for all days</option>
                                <option disabled="">Sunday</option>
                                <option>Monday</option>
                                <option selected="">Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                                <option>Saturday</option>
                              </select>
                              <label>Select Day(s)</label>
                            </div>
                          </div>
                          <div class="col-12 col-md mb-3">
                            <div class="border rounded px-2">
                              <p class="small text-secondary mb-0">Start time</p>
                              <div class="row align-items-center gx-1">
                                <div class="col">
                                  <input type="number" class="form-control border-0" value="11">
                                </div>
                                <div class="col-auto">:</div>
                                <div class="col">
                                  <input type="number" class="form-control border-0" value="32">
                                </div>
                                <div class="col-auto">:</div>
                                <div class="col-5">
                                  <select class="form-select border-0">
                                    <option selected="">AM</option>
                                    <option>PM</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-12 col-md mb-3">
                            <div class="border rounded px-2">
                              <p class="small text-secondary mb-0">End time</p>
                              <div class="row align-items-center gx-1">
                                <div class="col">
                                  <input type="number" class="form-control border-0" value="5">
                                </div>
                                <div class="col-auto">:</div>
                                <div class="col">
                                  <input type="number" class="form-control border-0" value="12">
                                </div>
                                <div class="col-auto">:</div>
                                <div class="col-5">
                                  <select class="form-select border-0">
                                    <option>AM</option>
                                    <option selected="">PM</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-auto">
                            <button class="btn btn-square btn-link mb-3"><i class="bi bi-plus-lg"></i></button>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="card-footer">
                    <div class="row">
                      <div class="col">
                        <button class="btn btn-theme">Save</button>
                      </div>
                      <div class="col-auto">
                        <button class="btn btn-link theme-red">Cancel</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- add user  -->
              <div class="modal adminuiux-modal fade" id="createuser" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <nav>
                        <div class="nav nav-pills" id="nav-tab-user-1" role="tablist">
                          <button class="nav-link active" id="nav-basic-tab-user-1" data-bs-toggle="tab" data-bs-target="#nav-basic-user-1" type="button" role="tab" aria-controls="nav-basic-user-1" aria-selected="true">Basic Info</button>
                          <button class="nav-link" id="nav-prof-tab-user-2" data-bs-toggle="tab" data-bs-target="#nav-prof-user-2" type="button" role="tab" aria-controls="nav-prof-user-2" aria-selected="false">Professional</button>
                          <button class="nav-link" id="nav-timing-tab-user-2" data-bs-toggle="tab" data-bs-target="#nav-timing-user-2" type="button" role="tab" aria-controls="nav-timing-user-2" aria-selected="false">Availability</button>
                        </div>
                      </nav>
                      <button type="button" class="btn btn-link btn-square ms-auto" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="modal-body pt-0">
                      <div class="card adminuiux-card overflow-hidden mb-4 pt-5">
                        <figure class="coverimg start-0 top-0 w-100 h-100 z-index-0 position-absolute overlay-gradiant" style="background-image: url(&quot;{{ asset('vendor/learninguiux/img/modern-ai-image/flamingo-3.jpg&quot;') }});">
                          <div class="position-absolute top-0 end-0 m-2">
                            <button class="btn btn-theme" onclick="$(this).next().click()">
                              <i class="bi bi-camera"></i> Change Cover
                            </button>
                            <input type="file" class="d-none">
                          </div>
                          <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/flamingo-3.jpg') }}" class="mw-100" alt="" style="display: none;">
                        </figure>

                        <div class="card-body text-center text-white z-index-1">
                          <div class="d-inline-block position-relative w-auto mx-auto my-3">
                            <figure class="avatar avatar-150 coverimg rounded-circle" style="background-image: url(&quot;{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg&quot;') }});">
                              <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg') }}" alt="" style="display: none;">
                            </figure>
                            <div class="position-absolute bottom-0 end-0 z-index-1 h-auto">
                              <button class="btn btn-lg btn-theme btn-square" onclick="$(this).next().click()">
                                <i class="bi bi-camera"></i>
                              </button>
                              <input type="file" class="d-none">
                            </div>
                          </div>
                          <h4>AdminUIUX</h4>
                          <p class="opacity-75 mb-3">guest@adminuiux.com</p>
                        </div>
                      </div>

                      <div class="tab-content" id="nav-tab-user-content-1">
                        <div class="tab-pane show active fade" id="nav-basic-user-1" role="tabpanel" aria-labelledby="nav-basic-tab-user-1" tabindex="0">

                          <h6 class="mb-3">Basic Details</h6>
                          <div class="row mb-2">
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="First Name" value="adminuiux" required="" class="form-control is-valid">
                                <label>First Name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Last Name" value="" required="" class="form-control">
                                <label>Last Name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating">
                                <input type="email" placeholder="Email Address" value="guest@adminuiux.com" disabled="" required="" class="form-control">
                                <label>Email Address</label>
                              </div>
                              <div class="invalid-feedback mb-3">Add .com at last to insert valid data </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Birth Date" value="26-04-1982" required="" class="form-control datepicker">
                                <label>Birth date</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <select class="form-select">
                                  <option>A</option>
                                  <option>A+</option>
                                  <option>B</option>
                                  <option selected>B+</option>
                                  <option>B-</option>
                                  <option>O</option>
                                  <option>O-</option>
                                </select>
                                <label>Blood group</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Weight" value="52" class="form-control">
                                <label>Weight (kg)</label>
                              </div>
                              <div class="invalid-feedback mb-3">Add .com at last to insert valid data </div>
                            </div>
                          </div>
                          <h6 class="mb-3">Address Details</h6>
                          <div class="row mb-2">
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Address Line 1" value="" required="" class="form-control">
                                <label>Address Line 1</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="email" placeholder="Address Line 2" required="" class="form-control">
                                <label>Address Line 2</label>
                              </div>
                              <div class="invalid-feedback mb-3">Add insert valid data </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Landmark" required="" class="form-control">
                                <label>Landmark</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="email" placeholder="Street" required="" class="form-control">
                                <label>Street</label>
                              </div>
                              <div class="invalid-feedback mb-3">Add insert valid data </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Country" value="" required="" class="form-control">
                                <label>Country</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Pincode" value="" required="" class="form-control">
                                <label>Pincode</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="State" value="" required="" class="form-control">
                                <label>State</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="City" value="" required="" class="form-control  is-invalid">
                                <label>City</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="nav-prof-user-2" role="tabpanel" aria-labelledby="nav-prof-tab-user-2" tabindex="0">
                          <h6 class="mb-3">Professional bio</h6>
                          <div class="mb-4">
                            <textarea class="FroalaEditor"></textarea>
                          </div>

                          <h6 class="mb-3">Academic qualification</h6>
                          <div class="row  mb-2">
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Degree" value="M.B.B.S" class="form-control">
                                <label>Degree</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="College/University" value="" class="form-control">
                                <label>College/University</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-auto">
                              <button class="btn btn-link mt-2">
                                <i class="bi bi-plus"></i> Add
                              </button>
                            </div>
                          </div>

                          <h6 class="mb-3">Specialized treatment</h6>
                          <div class="row  mb-2">
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Treatment name" value="Skin whitening" class="form-control">
                                <label>Treatment name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Treatment name" value="Skin problem" class="form-control">
                                <label>Treatment name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Treatment name" value="Skin decease" class="form-control">
                                <label>Treatment name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Treatment name" value="Lesser treatment" class="form-control">
                                <label>Treatment name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                              <div class="form-floating mb-3">
                                <input type="text" placeholder="Treatment name" value="Hair problem" class="form-control">
                                <label>Treatment name</label>
                              </div>
                              <div class="invalid-feedback">Please enter valid input</div>
                            </div>
                            <div class="col-auto">
                              <button class="btn btn-link mt-2">
                                <i class="bi bi-plus"></i> Add
                              </button>
                            </div>
                          </div>

                          <h6 class="mb-3">Public Visibility</h6>
                          <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                              <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="profileswitch1" checked="">
                                <label class="form-check-label" for="profileswitch1">Show profile publicly</label>
                              </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                              <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="profileswitch2" checked="">
                                <label class="form-check-label" for="profileswitch2">Show availability</label>
                              </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                              <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="profileswitch3">
                                <label class="form-check-label" for="profileswitch3">Show tagline in profile</label>
                              </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                              <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="profileswitch4">
                                <label class="form-check-label" for="profileswitch4">Make profile inactive</label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="nav-timing-user-2" role="tabpanel" aria-labelledby="nav-timing-tab-user-2" tabindex="0">
                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Monday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="monday">
                                <label class="form-check-label" for="monday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="h6  mb-3">Tuesday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="tuesday">
                                <label class="form-check-label" for="tuesday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Wednesday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="wednesday">
                                <label class="form-check-label" for="wednesday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Thursday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="thursday">
                                <label class="form-check-label" for="thursday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Friday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="friday">
                                <label class="form-check-label" for="friday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Saturday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="saturday" checked>
                                <label class="form-check-label" for="saturday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating opacity-50 mb-3">
                                <input type="time" value="09:00" class="form-control" disabled>
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating opacity-50 mb-3">
                                <input type="time" value="20:00" class="form-control" disabled>
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <button class="btn btn-link mb-3" disabled><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>

                          <div class="row mb-2">
                            <div class="col-6 col-md-3 col-xl-2 pt-3">
                              <h6 class="mb-3">Sunday</h6>
                            </div>
                            <div class="col-auto pt-3">
                              <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="sunday">
                                <label class="form-check-label" for="sunday">Full Day</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="09:00" class="form-control">
                                <label>Start Time</label>
                              </div>
                            </div>
                            <div class="col-auto">
                              <div class="form-floating mb-3">
                                <input type="time" value="20:00" class="form-control">
                                <label>End Time</label>
                              </div>
                            </div>
                            <div class="col-auto pt-2">
                              <button class="btn btn-link mb-3"><i class="bi bi-plus"></i> Add Shift</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <div class="col"><button type="button" class="btn btn-theme">Create User</button></div>
                      <div class="col-auto"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button></div>
                    </div>
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





