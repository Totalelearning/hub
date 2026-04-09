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
            <!-- logo -->
            <a class="navbar-brand" href="{{ route('preview.learning-dashboard') }}">
                <img data-bs-img="light" src="{{ asset('vendor/learninguiux/img/logo-light.svg') }}" alt="">
                <img data-bs-img="dark" src="{{ asset('vendor/learninguiux/img/logo.svg') }}" alt="">
                <div class="">
                    <span class="h4">Learning<span class="fw-bold">UI</span><span>UX</span></span>
                    <p class="company-tagline">AdminUIUX HTML template</p>
                </div>
            </a>

            <div class="collapse navbar-collapse justify-content-center" id="header-navbar">
                <ul class="navbar-nav mx-lg-3 mb-2 mb-md-0 ">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('preview.learning-dashboard') }}">LearningUIUX</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('preview.components') }}" aria-current="page">Components</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('preview.documentation') }}">Documentation</a>
                    </li>
                </ul>
            </div>

            <div class=" ms-auto "></div>
            <!-- right icons button -->
            <div class="ms-auto">
                <!-- dark mode -->
                <button class="btn btn-link btn-square btnsunmoon btn-link-header" id="btn-layout-modes-dark-page">
                    <i class="sun mx-auto" data-feather="sun"></i>
                    <i class="moon mx-auto" data-feather="moon"></i>
                </button>
                <!-- Buy now -->
                <a href="https://themeforest.net/user/maxartkiller/portfolio" class="btn btn-theme btn-link-header">
                    Buy Now
                </a>
            </div>
        </div>
    </nav>
</header>

<div class="adminuiux-wrap">
    <main class="adminuiux-content" onclick="contentClick()">

        <!-- breadcrumb -->
        <div class="container-fluid mt-4">
            <div class="row gx-3 align-items-center">
                <div class="col-12 col-sm">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}"><i class="bi bi-house-door me-1 fs-14"></i> Dashboard</a></li>
                            <li class="breadcrumb-item active bi" aria-current="page">Blank</li>
                        </ol>
                    </nav>
                    <h5>Blank</h5>
                </div>
                <div class="col-12 col-sm-auto text-end py-3 py-sm-0">

                </div>
            </div>
        </div>

        <!-- content -->
        <div class="container mt-4" id="main-content">
        </div>
    </main>
</div>

<!-- standard index footer -->
<footer class="adminuiux-footer mt-auto">
    <div class="container-fluid text-center">
        <span class="small">Copyright @2024, Creatively designed by
            <a href="https://adminuiux.com" target="_blank">LearningUIUX - Adminuiux</a> on Earth
        </span>
    </div>
</footer>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/base16/circus.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    document.querySelectorAll('.code').forEach(el => {
        hljs.highlightElement(el);
    });
</script>
@endpush
















