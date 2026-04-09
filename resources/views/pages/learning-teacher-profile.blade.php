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
</div> <!-- standard header -->
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
</header> <div class="adminuiux-wrap">
        <!-- Standard sidebar --> <!-- Standard sidebar -->
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
</div> <main class="adminuiux-content has-sidebar" onclick="contentClick()">
            <div class="container mt-4">
                <div class="row align-items-center">
                    <div class="col-12 col-md">
                        <h5>Teacher Profile</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-3 mb-md-0">
                                <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-teachers') }}">Teachers</a></li>
                                <li class="breadcrumb-item active bi" aria-current="page">Teacher Profile</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto col-md-auto">
                        <a href="{{ route('preview.learning-teacher-add') }}" class="btn btn-theme">
                            <i data-feather="edit" class="me-1"></i> Edit </a>
                    </div>
                </div>
            </div>
            <!-- content -->
            <div class="container mt-4" id="main-content">
                <div class="row">
                    <!-- profile cards -->
                    <div class="col-12 col-md-5 col-lg-4 col-xl-3">
                        <div class="height-300 w-100 rounded coverimg position-relative mb-3">
                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-1.jpg') }}" alt="">
                        </div>
                        <div class="row gx-3 align-items-center justify-content-center mb-3">
                            <div class="col-auto">
                                <button class="btn btn-square rounded-circle btn-outline-theme theme-green" data-bs-toggle="modal" data-bs-target="#callmodal">
                                    <i class="bi bi-telephone"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-square rounded-circle btn-outline-theme" data-bs-toggle="modal" data-bs-target="#callmodal">
                                    <i class="bi bi-camera-reels"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-square rounded-circle btn-outline-theme theme-orange" data-bs-toggle="modal" data-bs-target="#chatmodal">
                                    <i class="bi bi-chat-left-text"></i>
                                </button>
                            </div>
                        </div>
                        <h5 class="mb-0">Kim San</h5>
                        <p class="text-secondary mb-2">Senior Teacher - Language</p>
                        <p>
                            <span class="badge badge-light text-bg-theme-1 theme-green"><i class="bi bi-check-all me-1"></i>Present</span>
                            <span class="badge badge-light text-bg-theme-1 theme-orange"><i class="bi bi-check-all me-1"></i>In-Class</span>
                        </p>
                        <!-- contacts-->
                        <p class="text-secondary">
                            <i class="bi bi-envelope me-1"></i> test@kimsamtest.com
                        </p>
                        <p class="text-secondary">
                            <i class="bi bi-telephone me-1"></i> +44A479895411O
                        </p>
                        <p class="text-secondary">
                            <i class="bi bi-geo-alt me-1"></i>Malbourne, Australia
                        </p>
                        <button class="btn btn-theme w-100"> Book Class <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                    <!-- profile details cards -->
                    <div class="col-12 col-md-7 col-lg-8 col-xl-9">
                        <!-- courses upcoming -->
                        <div class="row mb-3">
                            <!-- course summary -->
                            <div class="col-12 col-md-12 col-lg-4">
                                <div class="card adminuiux-card shadow-sm bg-theme-1 mb-2">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto mb-3">
                                                <div class="avatar avatar-50 coverimg rounded">
                                                    <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/taxi-1.jpg') }}" alt="">
                                                </div>
                                            </div>
                                            <div class="col mb-3">
                                                <h6 class="mb-0">IT Data Modal</h6>
                                                <p class="small">Prof. John Jackson</p>
                                            </div>
                                        </div>
                                        <p class="opacity-75 small mb-0">Standard 10<sup>th</sup></p>
                                        <p class="opacity-75 small">Id <b>Live</b>, Duration 45min</p>
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <p class="small">12:30pm</p>
                                            </div>
                                            <div class="col text-center opacity-75">
                                                <p class="small"><i class="bi bi-clock me-1"></i>12/25 min</p>
                                            </div>
                                            <div class="col-auto">
                                                <p class="small">12:55pm</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-dynamic bg-theme-1 theme-black" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="--h-dynamic: 5px">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-theme-1 theme-teal" style="width: 60%"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- course summary -->
                            <div class="col-12 col-md-12 col-lg-4">
                                <div class="card adminuiux-card shadow-sm mb-2">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto mb-3">
                                                <div class="avatar avatar-50 coverimg rounded">
                                                    <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/tree-6.jpg') }}" alt="">
                                                </div>
                                            </div>
                                            <div class="col mb-3">
                                                <h6 class="mb-0">Bio Management</h6>
                                                <p class="small">Prof. Jinjo Min</p>
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
                                <div class="card adminuiux-card shadow-sm mb-2">
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
                        <!-- attendance statistics -->
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-auto mb-3 col-sm-0 ">
                                        <span class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                            <i class="bi bi-person"></i>
                                        </span>
                                    </div>
                                    <div class="col  mb-3 col-sm-0">
                                        <h6 class="mb-0">Attendance Statistics</h6>
                                        <p class="small text-secondary">Average in/Out time</p>
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select">
                                            <option>2022</option>
                                            <option>2023</option>
                                            <option>2024</option>
                                            <option>2025</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-0">
                                <div class="height-280 mb-3">
                                    <canvas id="summarychart"></canvas>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-auto col-xl-3 col-xxl-2 mb-3">
                                        <h5 class="mb-0">11.56 AM</h5>
                                        <p class="text-secondary small">Average In-Time</p>
                                    </div>
                                    <div class="col-auto col-xl-3 col-xxl-2 mb-3">
                                        <h5 class="mb-0">5.15 PM</h5>
                                        <p class="text-secondary small">Average Out-Time</p>
                                    </div>
                                    <div class="col-auto col-xl-3 col-xxl-2 mb-3">
                                        <h5 class="mb-0">0.80 Sec</h5>
                                        <p class="text-secondary small">Authentication Time</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- about -->
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i class="bi bi-person"></i></span>
                                    </div>
                                    <div class="col">
                                        <h6>About Teacher</h6>
                                    </div>
                                </div>
                                <p class="text-secondary"> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer cursus blandit dolor, nec rutrum urna facilisis vitae. </p>
                                <span class="badge badge-light me-1 mb-2 text-bg-theme-1 ">Teaching</span>
                                <span class="badge badge-light me-1 mb-2 text-bg-theme-1 ">Online Education</span>
                                <span class="badge badge-light me-1 mb-1 text-bg-theme-1 ">School Education</span>
                                <span class="badge badge-light me-1 mb-1 text-bg-theme-1 ">Language</span>
                            </div>
                        </div>
                        <!-- education -->
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-header z-index-1">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i class="bi bi-book"></i></span>
                                    </div>
                                    <div class="col">
                                        <h6>Education</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pb-0">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <span class="avatar avatar-50 rounded coverimg">
                                            <img src="{{ asset('vendor/learninguiux/img/logo-512.png') }}" alt="">
                                        </span>
                                    </div>
                                    <div class="col col-lg px-0">
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
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <span class="avatar avatar-50 rounded coverimg">
                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/shopping-2.jpg') }}" alt="">
                                        </span>
                                    </div>
                                    <div class="col col-lg px-0">
                                        <div class="row align-items-center">
                                            <div class="col-12 col-lg">
                                                <h6 class="mb-0">The Oxford University </h6>
                                                <p>Graduation in Social Science</p>
                                            </div>
                                            <div class="col col-lg-auto">
                                                <p class="text-secondary mb-0">2005-2007</p>
                                            </div>
                                            <div class="col-auto col-lg-auto text-end">
                                                <a href="#" class="btn btn-link">Credential <i class="bi bi-paperclip"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- my courses -->
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i class="bi bi-play-btn"></i></span>
                                    </div>
                                    <div class="col">
                                        <h6>My e-Courses</h6>
                                    </div>
                                    <div class="col-auto px-0">
                                        <select class="form-select form-select-sm">
                                            <option selected="selected">All Course</option>
                                            <option>Completed</option>
                                            <option>In-Progress</option>
                                            <option>With Result</option>
                                        </select>
                                    </div>
                                    <div class="col-auto"><button class="btn btn-sm btn-square btn-link"><i class="bi bi-arrow-clockwise"></i></button></div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <table class="table mb-0" data-show-toggle="true" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th class="">Chapter</th>
                                            <th data-breakpoints="xs sm">Duration</th>
                                            <th data-breakpoints="xs sm">Scheduled</th>
                                            <th data-breakpoints="xs">Status</th>
                                            <th data-breakpoints="xs">Chat</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-40 coverimg rounded">
                                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/flamingo-1.jpg') }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-0">Eco-Sensativity </h6>
                                                        <p class="text-secondary small">Jenelia</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-0">30 hours 45min</p>
                                                <p class="small text-secondary">12 Chapters</p>
                                            </td>
                                            <td>
                                                <p class="mb-0">30 Days</p>
                                                <p class="small text-secondary">11-02-2025 - 18-03-2025</p>
                                            </td>
                                            <td>
                                                <p class="mb-2">15%, 2/<span class="text-secondary">12</span></p>
                                                <div class="progress me-2 height-dynamic" role="progressbar" aria-label="Basic example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="--h-dynamic: 5px">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 45%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">10</span> <i class="bi bi-chat-text align-middle"></i></button>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">12</span> <i class="bi bi-paperclip align-middle"></i></button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger">Stop</button>
                                                <div class="dropdown d-inline-block">
                                                    <a class="btn btn-sm btn-link btn-square no-caret" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Favorite</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">View Details</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Events</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-40 coverimg rounded">
                                                            <img src="{{ asset('vendor/learninguiux/img/background-image/background-image-11.jpg') }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-0">IT Data Modal 1 </h6>
                                                        <p class="text-secondary small">Prof. John Jackson</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-0">25 hours 15min</p>
                                                <p class="small text-secondary">10 Chapters</p>
                                            </td>
                                            <td>
                                                <p class="mb-0">25 Days</p>
                                                <p class="small text-secondary">18-02-2025 - 18-03-2025</p>
                                            </td>
                                            <td>
                                                <div class="badge badge-light text-bg-success">Completed</div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">11</span> <i class="bi bi-chat-text align-middle"></i></button>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">8</span> <i class="bi bi-paperclip align-middle"></i></button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success">Go Live!</button>
                                                <div class="dropdown d-inline-block">
                                                    <a class="btn btn-sm btn-link btn-square no-caret" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Favorite</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">View Details</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Events</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="avatar avatar-40 coverimg rounded">
                                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-3.jpg') }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-0">Grow Human Kind </h6>
                                                        <p class="text-secondary small">Vantalia Atalia</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-0">10 hours</p>
                                                <p class="small text-secondary">4 Chapters</p>
                                            </td>
                                            <td>
                                                <p class="mb-0">45 Days</p>
                                                <p class="small text-secondary">11-01-2025 - 18-03-2025</p>
                                            </td>
                                            <td>
                                                <p class="mb-2">60%, 2<span class="text-secondary">4</span></p>
                                                <div class="progress me-2 height-dynamic" role="progressbar" aria-label="Basic example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="--h-dynamic: 5px">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-theme-1 theme-teal" style="width: 60%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">5</span> <i class="bi bi-chat-text align-middle"></i></button>
                                                <button class="btn btn-sm btn-link"><span class="align-middle">2</span> <i class="bi bi-paperclip align-middle"></i></button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success">Publish</button>
                                                <div class="dropdown d-inline-block">
                                                    <a class="btn btn-sm btn-link btn-square no-caret" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Favorite</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">View Details</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)">Events</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div> <!-- theming offcanvas-->
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
</div> <!-- standard footer -->
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
</div> <!-- call modal -->
    <div class="modal adminuiux-modal fade" id="callmodal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-body p-0 position-relative">
                    <video src="{{ asset('vendor/learninguiux/img/learning/5200355-hd_720_1280_50fps.mp4') }}" autoplay="" loop="" class="top-0 start-0 w-100 h-100 z-index-0 d-block rounded"></video>
                    <div class="position-absolute bottom-0 start-0 end-0 m-3 m-lg-4 rounded z-index-1">
                        <div class="row justify-content-center">
                            <div class="col-auto mb-3 position-relative">
                                <button type="button" class="btn btn-light btn-square rounded-circle position-absolute top-0 end-0 mt-1 mx-3"><i class="bi bi-mic h-auto w-auto"></i></button>
                                <video src="{{ asset('vendor/learninguiux/img/learning/8342934-hd_720_1280_25fps.mp4') }}" autoplay="" loop="" class="top-0 start-0 width-100 h-auto z-index-0 d-block rounded border border-2 shadow"></video>
                            </div>
                            <div class="col-auto mb-3 position-relative"><button type="button" class="btn btn-light btn-square rounded-circle position-absolute top-0 end-0 mt-1 mx-3"><i class="bi bi-mic h-auto w-auto"></i></button>
                                <video src="{{ asset('vendor/learninguiux/img/learning/6935820-hd_720_1280_30fps.mp4') }}" autoplay="" loop="" class="top-0 start-0 width-100 h-auto z-index-0 d-block rounded border border-2 shadow"></video>
                            </div>
                        </div>
                        <div class="row gx-3 align-items-end justify-content-center mx-0 overlay-option p-3 rounded">
                            <div class="col-auto"><button type="button" class="btn btn-light avatar avatar-60 rounded-circle"><i class="bi bi-mic-mute h-auto w-auto fs-4"></i></button></div>
                            <div class="col-auto"><button type="button" class="btn btn-accent avatar avatar-60 rounded-circle"><i class="bi bi-camera-video h-auto w-auto fs-4"></i></button></div>
                            <div class="col-auto"><button type="button" class="btn btn-light avatar avatar-60 rounded-circle"><i class="bi bi-volume-up h-auto w-auto fs-4"></i></button></div>
                            <div class="col-auto"><button type="button" class="btn btn-danger avatar avatar-60 rounded-circle" data-bs-dismiss="modal"><i class="bi bi-telephone-x h-auto w-auto fs-4"></i></button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- chat modal -->
    <div class="modal adminuiux-modal fade" id="chatmodal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-auto">
                        <figure class="avatar avatar-40 coverimg rounded-circle">
                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-1.jpg') }}" alt="">
                        </figure>
                    </div>
                    <div class="col px-3 align-self-center">
                        <p class="h6 mb-0"><span class="position-relative">Kim San <span class="position-absolute top-50 start-100 translate-middle p-1 bg-success rounded-circle mx-2"><span class="visually-hidden">New alerts</span></span></span></p>
                        <p class="text-secondary small">Online</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-link btn-square rounded-circle theme-red" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body overflow-y-auto chat-list">
                    <div class="row no-margin left-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">
                                        <p class="small mb-2"><a href="" class="style-none">Alex Smith</a></p>
                                        <p>Hi!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-secondary small time"><i class="bi bi-check"></i> 9:00 pm</p>
                        </div>
                    </div>
                    <div class="row no-margin right-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">Hi!<br>Yes please tell us your query.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <p class="text-secondary small time"><i class="bi bi-check"></i> 9:10 pm</p>
                        </div>
                    </div>
                    <div class="row justify-content-center text-center">
                        <div class="col-auto text-center">
                            <div class="alert alert-light mx-auto py-1 mb-3">26 November 2024</div>
                        </div>
                    </div>
                    <div class="row no-margin left-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">
                                        <p class="small mb-2"><a href="" class="style-none">Mr. Jack Mario</a></p>Adminuiux is amazing and we thank you. How can we buy?
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-secondary small time"><i class="bi bi-check-all text-primary"></i> 8:00 pm</p>
                        </div>
                    </div>
                    <div class="row no-margin right-chat first">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">
                                        <div class="position-relative mb-2 figure">
                                            <div class="position-absolute end-0 top-0">
                                                <button class="btn btn-square btn-theme m-2"><i class="bi bi-download"></i></button>
                                            </div>
                                            <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/bus-1.jpg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row no-margin right-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">Thank you too. You can buy it from preview page and click on buy now.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <p class="text-secondary small time"><i class="bi bi-check-all text-primary"></i> 8:00 pm</p>
                        </div>
                    </div>
                    <div class="row justify-content-center text-center">
                        <div class="col-auto text-center">
                            <div class="alert alert-light mx-auto py-1 mb-3">27 November 2024</div>
                        </div>
                    </div>
                    <div class="row no-margin left-chat first">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">
                                        <p class="small mb-2"><a href="" class="style-none">Ms. Jully CTO</a></p>
                                        <div class="position-relative figure">
                                            <video src="https://adminuiux.com/website/adminuiux.mp4" controls="" preload="none"></video>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row no-margin left-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">We also love this small presentation.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <p class="text-secondary small time"><i class="bi bi-check-all text-primary"></i> 8:00 pm</p>
                            </div>
                        </div>
                    </div>
                    <div class="row no-margin right-chat">
                        <div class="col-12">
                            <div class="chat-block">
                                <div class="row">
                                    <div class="col">
                                        <p>Ohh... Thats great. Adminuiux is HTML template can be used in various business domains like SAAS, CRM, Content Management, Manufacturing, inventory, IT, administration etc.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <p class="text-secondary small time"><i class="bi bi-check-all text-primary"></i> 8:00 pm</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="input-group">
                        <button class="btn btn-square btn-link"><i class="bi bi-emoji-smile"></i></button>
                        <button class="btn btn-square btn-link"><i class="bi bi-paperclip"></i></button>
                        <input class="form-control border-0" placeholder="Type your message... " value="">
                        <button class="btn btn-square btn-link" type="button"><i class="bi bi-send"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/learninguiux/js/learning/learning-teacher-profile.js') }}"></script>
@endpush

















