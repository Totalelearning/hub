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

                            <!-- breadcrumb -->
                            <div class="container mt-4">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md">
                                        <h5>School Corses</h5>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-3 mb-md-0">
                                                <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">Dashboard</a></li>
                                                <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-student-home') }}">Students</a></li>
                                                <li class="breadcrumb-item active bi" aria-current="page">School Corses</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-12 col-md-auto">

                                    </div>
                                </div>
                            </div>

                            <!-- content -->
                            <div class="container mt-4" id="main-content">
                                <div class="swiper swipernav">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="row mb-4">
                                                <div class="col-12 col-sm-6 col-lg-5 py-3 py-lg-4 px-lg-4 align-self-center">
                                                    <h1 class="text-theme-1">#1 Best School Courses</h1>
                                                    <h4 class="mb-3">In-School Education Provider</h4>
                                                    <p class="text-secondary">With Personal Coaching, admission, exam preparation, student overall growth, exercise, creative activities and more...</p>
                                                </div>
                                                <div class="col"></div>
                                                <div class="col-12 col-sm-5 col-lg-4 align-self-end">   
                                                    <img src="{{ asset('vendor/learninguiux/img/learning/banner-2.png') }}" alt="" class="mw-100 rounded mt-0 mt-sm-4">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="row mb04">
                                                <div class="col-12 col-sm-6 col-lg-5 py-3 py-lg-4 px-lg-4 align-self-center">
                                                    <h1 class="text-theme-1">Design that inspires</h1>
                                                    <h4 class="mb-3">You are at the best place</h4>
                                                    <p class="text-secondary">With Personal Subscription, you get access to 10,000+ of our top courses in industry, business, and more...</p>
                                                    <a href="#" class="btn btn-theme">Learn More</a>
                                                </div>
                                                <div class="col"></div>
                                                <div class="col-12 col-sm-5 col-lg-4 align-self-end">
                                                    <img src="{{ asset('vendor/learninguiux/img/learning/banner-2.png') }}" alt="" class="mw-100 rounded mt-0 mt-sm-4">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <h5>Explore Schooling Courses</h5>
                                <p class="text-secondary">We have wide range of courses from standard 1-12</p>
                                <!-- courses in school-->
                                <div class="card adminuiux-card shadow-sm mb-4">
                                    <div class="card-body pb-0">
                                        <div class="row">
                                            <!-- academic -->
                                            <div class="col-12 col-md-4 col-lg-3 mb-3">
                                                <ul class="nav nav-tabs adminuiux-tabs nav-fill flex-column" id="myTab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="1-tab" data-bs-toggle="tab" data-bs-target="#1-tab-pane" type="button" role="tab" aria-controls="1-tab-pane" aria-selected="true">Pre-School</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="2-tab" data-bs-toggle="tab" data-bs-target="#2-tab-pane" type="button" role="tab" aria-controls="2-tab-pane" aria-selected="false">Lower School</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="3-tab" data-bs-toggle="tab" data-bs-target="#3-tab-pane" type="button" role="tab" aria-controls="3-tab-pane" aria-selected="false">Middle School</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="4-tab" data-bs-toggle="tab" data-bs-target="#4-tab-pane" type="button" role="tab" aria-controls="4-tab-pane" aria-selected="false">High School</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="5-tab" data-bs-toggle="tab" data-bs-target="#5-tab-pane" type="button" role="tab" aria-controls="5-tab-pane" aria-selected="false">Fine Art</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="6-tab" data-bs-toggle="tab" data-bs-target="#6-tab-pane" type="button" role="tab" aria-controls="6-tab-pane" aria-selected="false">Sports</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="7-tab" data-bs-toggle="tab" data-bs-target="#7-tab-pane" type="button" role="tab" aria-controls="7-tab-pane" aria-selected="false">Languages</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="8-tab" data-bs-toggle="tab" data-bs-target="#8-tab-pane" type="button" role="tab" aria-controls="8-tab-pane" aria-selected="false">Perfomance</button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-12 col-md-8 col-lg-9">
                                                <div class="tab-content" id="myTabContent">
                                                    <div class="tab-pane fade show active" id="1-tab-pane" role="tabpanel" aria-labelledby="1-tab" tabindex="0">
                                                        <!-- couses -->
                                                        <div class="row">

                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-people"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Parent</h6>
                                                                                <p class="small">Guiding for child growth</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-puzzle"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Puzzleroom</h6>
                                                                                <p class="small">Improve thinking power</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-house"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Indoor Learning</h6>
                                                                                <p class="small">Teaching indoor activity</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-houses"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Outdoor</h6>
                                                                                <p class="small">Teaching outdoor activities</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-person-gear"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Self-orientation</h6>
                                                                                <p class="small">Improve in self</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- course overview -->
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="card mb-4">
                                                                    <div class="card-body">
                                                                        <div class="row align-items-center mb-3">
                                                                            <div class="col-auto">
                                                                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4">
                                                                                    <i class="bi bi-hospital"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <h6 class="mb-0">Indoor Learning</h6>
                                                                                <p class="small">Teaching indoor activity</p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-secondary small mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae.</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="2-tab-pane" role="tabpanel" aria-labelledby="2-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="3-tab-pane" role="tabpanel" aria-labelledby="3-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="4-tab-pane" role="tabpanel" aria-labelledby="4-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="5-tab-pane" role="tabpanel" aria-labelledby="5-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="6-tab-pane" role="tabpanel" aria-labelledby="6-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="7-tab-pane" role="tabpanel" aria-labelledby="7-tab" tabindex="0">...</div>
                                                    <div class="tab-pane fade" id="8-tab-pane" role="tabpanel" aria-labelledby="8-tab" tabindex="0">...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5>Our features</h5>
                                <p class="text-secondary">Facilities that provide with our in school courses</p>
                                <!-- feature -->

                                <div class="row">
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-eye avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-red"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Quality Education</h6>
                                                        <p class="small text-secondary">Reliable and High Quality Education</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-puzzle avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-orange"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Puzzleroom</h6>
                                                        <p class="small text-secondary">World Class Problem Solving Environment</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-play-btn avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-green"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Video Lectures</h6>
                                                        <p class="small text-secondary">Video Lectures and Self-Learning</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-easel avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-skyblue"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Presenation</h6>
                                                        <p class="small text-secondary">Weekly Presentation and Orientation</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-clipboard-check avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-yellow"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Result Oriented</h6>
                                                        <p class="small text-secondary">Exam and Test along with Preparation</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-book avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-teal h4"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Progress Tracking</h6>
                                                        <p class="small text-secondary">Notes and Maintain Syllabus Progress</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-people avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-purple h4"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Parent Orientation</h6>
                                                        <p class="small text-secondary">Guiding Parents with Latest Teaching Methods</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- feature card -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <i class="bi bi-phone avatar avatar-50 h4 rounded-circle bg-theme-1 text-white theme-brown h4"></i>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">Transparency</h6>
                                                        <p class="small text-secondary">100% Tracking with Mobile Application</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-2">Much more with your school life</h5>
                                <p class="text-secondary">Inspired by your journey to the learning <span class="badge badge-sm badge-light text-bg-success">New</span></p>
                                <!-- Courses swiper carousel -->
                                <div class="row">

                                    <!-- academic -->
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="card adminuiux-card shadow-sm height-160 bg-theme-1 theme-orange mb-4">
                                                    <div class="card-body position-relative">
                                                        <h5 class="mb-0">Integrity</h5>
                                                        <p class="opacity-75 mb-4">Learn from childhood</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card adminuiux-card shadow-sm height-160 bg-theme-1 theme-yellow mb-4">
                                                    <div class="card-body position-relative">
                                                        <h5 class="mb-0">Creativity</h5>
                                                        <p class="opacity-75 mb-4">Problem solver mindset</p>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card adminuiux-card shadow-sm height-170 bg-theme-1 theme-skyblue mb-4">
                                                    <div class="card-body position-relative">
                                                        <h5 class="mb-0">Faithful</h5>
                                                        <p class="opacity-75 mb-4">Build community of trust.</p>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card adminuiux-card shadow-sm height-170 bg-theme-accent-1 mb-4">
                                                    <div class="card-body position-relative">
                                                        <h5 class="mb-0">Effective</h5>
                                                        <p class="opacity-75 mb-4">Communicate with the strength</p>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-9">
                                        <div class="swiper swipernavpagination">
                                            <div class="swiper-wrapper mb-3">
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4 theme-green">
                                                        <div class="card-body position-relative">
                                                            <span class="ribbon bg-theme-1 position-absolute top-0 end-0 z-index-1 mt-4">Best Seller</span>
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-5.jpg') }}" alt="">
                                                            </div>

                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">Fundamental of UX Design process</h6>
                                                                <p class="small text-secondary">By Walter Smith</p>
                                                            </a>
                                                            <p class="text-secondary"><b>4.5</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-half small text-warning"></i>
                                                            </p>

                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 75.00 <s class="opacity-50 fs-14">$ 110.00</s></h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-link btn-square rounded-circle">
                                                                        <i class="bi bi-cart"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4">
                                                        <div class="card-body position-relative">
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/bus-3.jpg') }}" alt="">
                                                            </div>
                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">UX Research and Wireframing</h6>
                                                                <p class="small text-secondary">By Will Gill</p>
                                                            </a>
                                                            <p class="text-secondary"><b>4.0</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star small text-warning"></i>
                                                            </p>
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 80.00 <s class="opacity-50 fs-14">$ 120.00</s></h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-link btn-square rounded-circle active">
                                                                        <i class="bi bi-cart"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4">
                                                        <div class="card-body position-relative">
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/tree-7.jpg') }}" alt="">
                                                            </div>
                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">Digital Design at the edge</h6>
                                                                <p class="small text-secondary">By Max Barter</p>
                                                            </a>
                                                            <p class="text-secondary"><b>4.5</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-half small text-warning"></i>
                                                            </p>
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 80.00</h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <div class="input-group input-group-sm">
                                                                        <button class="btn btn-link btn-square rounded-circle">
                                                                            <i class="bi bi-dash"></i>
                                                                        </button>
                                                                        <input type="text" class="border-0 form-control bg-none text-center width-30 px-0 " value="1">
                                                                        <button class="btn btn-link btn-square rounded-circle">
                                                                            <i class="bi bi-plus"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4">
                                                        <div class="card-body position-relative">
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/lion-5.jpg') }}" alt="">
                                                            </div>
                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">Design with McGrow</h6>
                                                                <p class="small text-secondary">By McGrow and Hills</p>
                                                            </a>
                                                            <p class="text-secondary"><b>5.0</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                            </p>
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 115.00 <s class="opacity-50 fs-14">$ 150.00</s></h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-link btn-square rounded-circle">
                                                                        <i class="bi bi-cart"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4">
                                                        <div class="card-body position-relative">
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/kids-5.jpg') }}" alt="">
                                                            </div>
                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">UX Universal Risk</h6>
                                                                <p class="small text-secondary">By Will Gill</p>
                                                            </a>
                                                            <p class="text-secondary"><b>4.0</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star small text-warning"></i>
                                                            </p>
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 80.00 <s class="opacity-50 fs-14">$ 120.00</s></h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-link btn-square rounded-circle">
                                                                        <i class="bi bi-cart"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide width-250">
                                                    <div class="card adminuiux-card shadow-sm mb-4">
                                                        <div class="card-body position-relative">
                                                            <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                                                                <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/team-sq-2.jpg') }}" alt="">
                                                            </div>
                                                            <a href="{{ route('preview.learning-course-details') }}" class="style-none d-block mb-2">
                                                                <h6 class="mb-0 text-truncated">Design for Good and People</h6>
                                                                <p class="small text-secondary">By Max Johnson</p>
                                                            </a>
                                                            <p class="text-secondary"><b>5.0</b>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                                <i class="bi bi-star-fill small text-warning"></i>
                                                            </p>
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <h5>$ 90.00 <s class="opacity-50 fs-14">$ 150.00</s></h5>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-link btn-square rounded-circle active">
                                                                        <i class="bi bi-cart"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-2">Frequently Asked Questions</h5>
                                <p class="text-secondary">Get more details with general questions and answers</p>
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center mb-4">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-60 bg-theme-1-subtle text-theme-1 rounded"><i class="bi bi-person-circle h4"></i></div>
                                                    </div>
                                                    <div class="col">
                                                        <h5>Login and Logout</h5>
                                                        <p class="text-secondary small">Get correct way to use</p>
                                                    </div>
                                                </div>
                                                <div class="list-group adminuiux-list-group list-group-flush"><a href="#" class="list-group-item list-group-item-action">Login purpose </a><a href="#" class="list-group-item list-group-item-action">What are the Login options?</a> <a href="#" class="list-group-item list-group-item-action">How to reset password?</a> <a href="#" class="list-group-item list-group-item-action">How to change password?</a> <a href="#"
                                                        class="list-group-item list-group-item-action">Where from I can
                                                        logout from?</a></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center mb-4">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-60 bg-theme-1-subtle text-theme-1 rounded"><i class="bi bi-palette h4"></i></div>
                                                    </div>
                                                    <div class="col">
                                                        <h5>Customization &amp; Settings</h5>
                                                        <p class="text-secondary small">Make it more like yours</p>
                                                    </div>
                                                </div>
                                                <div class="list-group adminuiux-list-group list-group-flush"><a href="#" class="list-group-item list-group-item-action">How to change color scheme? </a><a href="#" class="list-group-item list-group-item-action">Personalize background images?</a> <a href="#" class="list-group-item list-group-item-action">Update colors in theme colors?</a> <a href="#" class="list-group-item list-group-item-action">How to create new theme colors set?</a> <a href="#"
                                                        class="list-group-item list-group-item-action">Purpose of the crowd assets?</a></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card adminuiux-card shadow-sm mb-4">
                                            <div class="card-body">
                                                <div class="row align-items-center mb-4">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-60 bg-theme-1-subtle text-theme-1 rounded"><i class="bi bi-award h4"></i></div>
                                                    </div>
                                                    <div class="col">
                                                        <h5>License and Usage</h5>
                                                        <p class="text-secondary small">Read more about usage &amp; licenses</p>
                                                    </div>
                                                </div>
                                                <div class="list-group adminuiux-list-group list-group-flush"><a href="#" class="list-group-item list-group-item-action">What is mean bt free license?</a> <a href="#" class="list-group-item list-group-item-action">What is business license?</a> <a href="#" class="list-group-item list-group-item-action">How is PRO different from free license?</a> <a href="#" class="list-group-item list-group-item-action">Benefits of PRO license?</a> <a href="#"
                                                        class="list-group-item list-group-item-action">Personal use limitation and restrictions?</a></div>
                                            </div>
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





