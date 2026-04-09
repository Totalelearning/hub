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
</header> <div class="adminuiux-wrap">
        <main class="adminuiux-content" onclick="contentClick()">
            <!-- breadcrumb -->
            <div class="container-fluid mt-3">
                <div class="bg-theme-1-subtle rounded px-3 py-3">
                    <div class="row gx-3 align-items-center">
                        <div class="col col-sm mb-2 mb-sm-0">
                            <p class="h5">Breadcrumb</p>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item bi"><a href="{{ route('preview.learning-dashboard') }}">App Demo</a></li>
                                    <li class="breadcrumb-item bi"><a href="{{ route('preview.components') }}">Components</a></li>
                                    <li class="breadcrumb-item active bi" aria-current="page">Breadcrumb</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-auto ">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Content  -->
            <div class="container mt-4">
                <div class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <p class="h6">Standard Breadcrumb</p>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-theme btn-square" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false">
                                    <i class="bi bi-code-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'/'">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" aria-current="page">Home</li>
                            </ol>
                        </nav>
                        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'/'">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Library</li>
                            </ol>
                        </nav>
                        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'&#x21E2;'">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="#">Library</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Data</li>
                            </ol>
                        </nav>
                        <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'&#x27BA;'">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="#">Library</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Data</li>
                            </ol>
                        </nav>
                        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Library</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="collapse" id="collapse1">
                        <div class="card-footer border-top">
                            <div class="bg-dark text-white p-2 rounded my-2">
                                <pre class="mb-2"><code class="code rounded language-html">
            &lt;nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'/'"&gt;
                &lt;ol class="breadcrumb"&gt;
                    &lt;li class="breadcrumb-item active" aria-current="page"&gt;Home&lt;/li&gt;
                &lt;/ol&gt;
            &lt;/nav&gt;
                                
            &lt;nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'/'"&gt;
                &lt;ol class="breadcrumb"&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item active" aria-current="page"&gt;Library&lt;/li&gt;
                &lt;/ol&gt;
            &lt;/nav&gt;
                                
            &lt;nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'&#x21E2;'"&gt;
                &lt;ol class="breadcrumb"&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Library&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item active" aria-current="page"&gt;Data&lt;/li&gt;
                &lt;/ol&gt;
            &lt;/nav&gt;
                                
            &lt;nav aria-label="breadcrumb" style="--bs-breadcrumb-divider:'&#x27BA;'"&gt;
                &lt;ol class="breadcrumb"&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Library&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item active" aria-current="page"&gt;Data&lt;/li&gt;
                &lt;/ol&gt;
            &lt;/nav&gt;
                                
                                
            &lt;nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb"&gt;
                &lt;ol class="breadcrumb"&gt;
                    &lt;li class="breadcrumb-item"&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                    &lt;li class="breadcrumb-item active" aria-current="page"&gt;Library&lt;/li&gt;
                &lt;/ol&gt;
            &lt;/nav&gt;</code></pre>
                                <button type="button" class="btn btn-outline-light  btn-square copycode"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- component footer -->
                <div class="bg-theme-1-subtle rounded px-3 py-2 mb-4">
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('preview.component-tabs') }}" class="btn btn-accent me-3 my-2"><i class="bi bi-arrow-left mr-2"></i> Tabs</a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('preview.component-scrollspy') }}" class="btn btn-theme my-2">ScrollSpy <i class="bi bi-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div> <!-- standard index footer -->
<footer class="adminuiux-footer mt-auto">
    <div class="container-fluid text-center">
        <span class="small">Copyright @2024, Creatively designed by
            <a href="https://adminuiux.com" target="_blank">LearningUIUX - Adminuiux</a> on Earth ❤️
        </span>
    </div>
</footer>


<!-- code highlighter -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/base16/circus.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    document.querySelectorAll('.code').forEach(el => {
        // then highlight each
        hljs.highlightElement(el);
    });
</script>
@endsection



