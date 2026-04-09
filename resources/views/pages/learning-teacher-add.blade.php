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

                            <div class="container mt-4">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md">
                                        <h5>Add Teacher</h5>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-3 mb-md-0">
                                                <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">Dashboard</a></li>
                                                <li class="breadcrumb-item active bi" aria-current="page">Add/Edit Teacher</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-6 col-md-auto">

                                    </div>
                                </div>
                            </div>

                            <!-- content -->
                            <div class="container mt-4" id="main-content">

                                <!-- add teacher wizards -->
                                <div class="card adminuiux-card shadow-sm overflow-hidden mb-4" id="smartwizard2">
                                    <ul class="nav mt-4">
                                        <li class="nav-item">
                                            <a class="nav-link" href="#step-1">
                                                <div class="num">1</div>
                                                <div>
                                                    <p class="h5 mb-0">Teacher Details</p>
                                                    <p class="opacity-75 small">Name and Details</p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#step-2">
                                                <div class="num">2</div>
                                                <div>
                                                    <p class="h5 mb-0">Address</p>
                                                    <p class="opacity-75 small">Guide to reach you</p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#step-3">
                                                <div class="num">3</div>
                                                <div>
                                                    <p class="h5 mb-0">Education</p>
                                                    <p class="opacity-75 small">Your Education Experience</p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="#step-4">
                                                <div class="num">4</div>
                                                <div>
                                                    <p class="h5 mb-0">Lectures/Class</p>
                                                    <p class="opacity-75 small">Add at least 1 service</p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="card-body pb-0">
                                        <div class="tab-content">
                                            <!-- details -->
                                            <div id="step-11" class="tab-pane px-0 " role="tabpanel" aria-labelledby="step-11">
                                                <div class="row">
                                                    <div class="col-12 col-lg-3 text-center mb-3">
                                                        <figure class="maxwidth-250 height-260 coverimg rounded position-relative mx-auto mb-3">
                                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-1.jpg') }}" alt="">
                                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                                <button class="btn btn-square btn-accent rounded-circle" onclick="$(this).next().click()">
                                                                    <i class="bi bi-upload"></i>
                                                                </button>
                                                            </div>
                                                            <input type="file" class="d-none">
                                                        </figure>
                                                        <p class="h5">Kim San</p>
                                                        <p class="small text-secondary">kimsan1852@tgmails.com</p>
                                                    </div>
                                                    <div class="col">
                                                        <!-- details -->
                                                        <p class="h6 py-2 mb-2">Basic Details</p>
                                                        <div class="row ">
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="namef" placeholder="Enter First Name" value="Kim">
                                                                    <label for="namef">First Name</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="namem" placeholder="Enter Middle Name" value="">
                                                                    <label for="namem">Middle Name (Optional)</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="namel" placeholder="Enter Last Name " value="San">
                                                                    <label for="namel">Last Name</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text text-secondary" id="calendarpciker" onclick="$(this).next().find('input').click()"> <i data-feather="calendar"></i> </span>
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="datepicker" placeholder="Enter Last Name " value="11/05/2001">
                                                                        <label for="namel">Date of Birth</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="phoneon2" placeholder="Enter phone" value="+91 4152 21A45488004">
                                                                    <label for="phoneon2">Enter Phone</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="email" class="form-control" id="emailaddresson1" placeholder="Enter Email Address" value="kimsan1852@tgmails.com" disabled>
                                                                    <label for="emailaddresson1">Email Address</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- about teacher -->
                                                        <p class="h6 py-2 mb-2">About Teacher</p>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-floating mb-3">
                                                                    <textarea class="form-control height-100" id="describe3" placeholder="Enter description about you"></textarea>
                                                                    <label for="describe3">Short description about you</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- specialities -->
                                                        <p class="h6 py-2 mb-2">Specialities/Skillset</p>
                                                        <div class="row align-items-center">
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="tags1" placeholder="Enter Skill 1" value="Online Education">
                                                                    <label for="tags1">Skill 1</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <button class="btn btn-link btn-square mb-3"><i class="bi bi-plus-lg"></i></button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Address -->
                                            <div id="step-21" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-21">
                                                <div class="row">
                                                    <div class="col-12 col-lg-3 text-center">
                                                        <p class="h6 py-2 mb-2">Locate on Map</p>
                                                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d788.4385190507815!2d-122.4278138198206!3d37.772364180178094!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1712990839970!5m2!1sen!2sin" height="280" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="w-100 rounded mb-3"></iframe>
                                                    </div>
                                                    <div class="col">
                                                        <div class="row align-items-center mb-2">
                                                            <div class="col">
                                                                <p class="h6 py-2">Your Address</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <button class="btn btn-link"><i data-feather="plus"></i> Add Another</button>
                                                            </div>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="house1" placeholder="Enter branch name">
                                                            <label for="house1">Branch Name</label>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="house3" placeholder="Enter Shop/Door number" value="12">
                                                                    <label for="house3">Shop/Door Number</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="street" placeholder="Enter Street" value="Feather stone Street">
                                                                    <label for="street">Street</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="locality" placeholder="Enter locality" value="Ward">
                                                                    <label for="locality">Locality</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="town" placeholder="Enter Town" value="London">
                                                                    <label for="town">Town</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="city" placeholder="Enter City" value="London">
                                                                    <label for="city">City</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="postcode" placeholder="Enter Postcode" value="NG25 5AY">
                                                                    <label for="postcode">Postcode</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="country" placeholder="Enter Country" value="United Kingdom">
                                                                    <label for="country">Country</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="step-31" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-31">

                                                <div class="card mb-4">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <span class="avatar avatar-50 rounded coverimg" style="background-image: url(&quot;{{ asset('vendor/learninguiux/img/logo-512.png&quot;') }});">
                                                                    <img src="{{ asset('vendor/learninguiux/img/logo-512.png') }}" alt="" style="display: none;">
                                                                </span>
                                                            </div>
                                                            <div class="col col-lg ">
                                                                <div class="row align-items-center">
                                                                    <div class="col-12 col-lg">
                                                                        <h6 class="mb-0">The University of Kalinga</h6>
                                                                        <p>Master Degree in Social Science</p>
                                                                    </div>
                                                                    <div class="col col-lg-auto">
                                                                        <p class="text-secondary mb-0">2007-2011</p>
                                                                    </div>
                                                                    <div class="col-auto col-lg-auto text-end">
                                                                        <a href="#" class="btn btn-link">Credential <i class="bi bi-paperclip"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-2">
                                                    <div class="col">
                                                        <p class="h6 py-2">Add Experience</p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button class="btn btn-link"><i data-feather="plus"></i> Add Another</button>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12 col-md-6 col-lg col-xl mb-3">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" placeholder="Enter University" id="uni-1a">
                                                            <label for="uni-1a">University</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-lg col-xl mb-3">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" placeholder="Enter Course/Degree" id="course-1a">
                                                            <label for="course-1a">Course/Degree</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-sm-5 col-lg-2 col-xl-2 mb-3">
                                                        <div class="form-floating">
                                                            <select class="form-select" id="start-year-1a">
                                                                <option>2011</option>
                                                                <option>2012</option>
                                                                <option>2013</option>
                                                                <option>2014</option>
                                                                <option>2015</option>
                                                                <option>2016</option>
                                                                <option>2017</option>
                                                                <option>2018</option>
                                                                <option>2019</option>
                                                                <option>2020</option>
                                                                <option>2021</option>
                                                                <option>2022</option>
                                                                <option>2023</option>
                                                                <option>2024</option>
                                                                <option>2025</option>
                                                            </select>
                                                            <label for="start-year-1a">Start Year</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-sm-5 col-lg-2 col-xl-2 mb-3">
                                                        <div class="form-floating">
                                                            <select class="form-select" id="end-year-1a">
                                                                <option>2011</option>
                                                                <option>2012</option>
                                                                <option>2013</option>
                                                                <option>2014</option>
                                                                <option>2015</option>
                                                                <option>2016</option>
                                                                <option>2017</option>
                                                                <option>2018</option>
                                                                <option>2019</option>
                                                                <option>2020</option>
                                                                <option>2021</option>
                                                                <option>2022</option>
                                                                <option>2023</option>
                                                                <option>2024</option>
                                                                <option>2025</option>
                                                            </select>
                                                            <label for="end-year-1a">End Year</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" placeholder="Enter Credential URL" id="url-1a">
                                                            <label for="url-1a">Credential URL</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="step-41" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-41">

                                                <div class="row mb-3">
                                                    <!-- course summary -->
                                                    <div class="col-12 col-md-12 col-lg-4">
                                                        <div class="card mb-2">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto mb-3">
                                                                        <div class="avatar avatar-50 coverimg rounded">
                                                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/tree-6.jpg') }}" alt="">
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

                                                <div class="row align-items-center mb-2">
                                                    <div class="col">
                                                        <p class="h6 py-2">Add Lecture/Class</p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button class="btn btn-link"><i data-feather="plus"></i> Save</button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 col-lg-3 text-center mb-3">
                                                        <figure class="mw-100 height-200 coverimg rounded position-relative mx-auto mb-3">
                                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/pet-3.jpg') }}" alt="">
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
                                                                    <input type="text" class="form-control" placeholder="Enter subject name" value="Language">
                                                                    <label>Name</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-4">
                                                                <div class="form-floating mb-3">
                                                                    <select class="form-select">
                                                                        <option>E-Course</option>
                                                                        <option>Part-Time Course</option>
                                                                        <option selected>Lecture</option>
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
                                                                        <option selected>4th Standard</option>
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
                                                                        <option disabled>Sunday</option>
                                                                        <option>Monday</option>
                                                                        <option selected>Tuesday</option>
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
                                        </div>
                                    </div>
                                    <!-- Include optional progressbar HTML -->
                                    <div class="progress bg-theme-1-subtle rounded-0">
                                        <div class="progress-bar bg-theme-accent-l-gradient h-100 rounded-0" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
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





