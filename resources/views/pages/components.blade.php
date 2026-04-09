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

                        <!-- content -->

                        <div class="container mt-4" id="main-content">
                            <div class="text-center mb-4">
                                <h1>Components</h1>
                                <p class="text-secondary">UI components are essential elements for building interactive interfaces.<br>They can be classified into various categories based on their functionality: action, containment, communication, navigation, selection, and text input.</p>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                                    <div class="position-sticky" style="top: 5.5rem;">
                                        <div id="list-example" class="list-group adminuiux-list-group bg-none">
                                            <a class="list-group-item list-group-item-action" href="#list-item-1">Actions</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-2">Informative</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-3">Contents</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-4">Navigation</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-5">Selection</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-6">Form Control</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-7">Icons</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-8">Third Party</a>
                                            <a class="list-group-item list-group-item-action" href="#list-item-9">Utility</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-8 col-md-9  col-lg-10">
                                    <div class="row" id="list-item-1">
                                        <div class="col-12 mb-4">
                                            <p class="h4">Actions</p>
                                            <p class="text-secondary">Dropdowns, Icon buttons, Buttons and button groups are key elements for triggering actions in HTML templates. They enhance user interaction by providing clear, accessible ways to perform tasks like submitting forms or navigating interfaces.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-buttons') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-buttons.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Buttons</p>
                                                            <p class="text-secondary">Common buttons for actions</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-button-groups') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-button-groups.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Button Groups</p>
                                                            <p class="text-secondary">Grouped button for toggle choices</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-icon-buttons') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-icon-buttons.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Icon Buttons</p>
                                                            <p class="text-secondary">Icon button & square buttons</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-dropdowns') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-dropdown.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Dropdowns</p>
                                                            <p class="text-secondary">Space saving list with button click</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-pagination') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-pagination.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Pagination</p>
                                                            <p class="text-secondary">Navigating to specific list of pages</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-2">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Informative</p>
                                            <p class="text-secondary">These components, such as tooltips, loaders, progress and alerts, provide users with essential information and feedback. They enhance the user experience by delivering context-specific details and guidance.
                                            </p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-alerts') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-alert.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Alerts</p>
                                                            <p class="text-secondary">Notify error, alert, information in a box</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-badges') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-badge.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Badges</p>
                                                            <p class="text-secondary">Live states, new item or numbers</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-popovers') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-popover.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Popovers</p>
                                                            <p class="text-secondary">Toggle introduction of item or Tips</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-progress') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-progressbar.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Progress</p>
                                                            <p class="text-secondary">Show progress status and how much</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-toasts') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-toast.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Toasts</p>
                                                            <p class="text-secondary">Notify minor action status updates</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-tooltips') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-tooltip.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Tooltips</p>
                                                            <p class="text-secondary">Identify element or have help text</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-spinners-loaders') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-loaders.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Spinners Loaders</p>
                                                            <p class="text-secondary">Show something is in progress</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-riskometer') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-riskometer.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Riskometer</p>
                                                            <p class="text-secondary">Show risk factor from low to high</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-3">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Contents</p>
                                            <p class="text-secondary">These components, like cards, lists, and tables, organize and present information clearly. They enhance readability and help users quickly find and understand content.</p>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-accordions') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-accordion.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Accordions</p>
                                                            <p class="text-secondary">More information access in space</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-cards') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-card.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Cards</p>
                                                            <p class="text-secondary">Group data: title, details & actions</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-collapse') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-collapse.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Collapse</p>
                                                            <p class="text-secondary">Toggle what you need to know</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-list-groups') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-list-group.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">List Group</p>
                                                            <p class="text-secondary">Well manner list of items</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-modal-dialogues') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-modal.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Modal Dialog</p>
                                                            <p class="text-secondary">Quick look and take action</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-off-canvas') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-off-canvas.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Off Canvas</p>
                                                            <p class="text-secondary">Data preview, settings, information</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-tables') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-tables.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Tables</p>
                                                            <p class="text-secondary">Column wise data grouping</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-pricing') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-pricing.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Pricing</p>
                                                            <p class="text-secondary">Subscription Plan and Pricing</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-4">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Navigation</p>
                                            <p class="text-secondary">These components, like menus, breadcrumbs, and pagination, guide users through the interface. They enhance usability by providing clear paths to different sections and content.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-header') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-header.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Header</p>
                                                            <p class="text-secondary">Different styles and content</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-nav') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-navigation.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Navs</p>
                                                            <p class="text-secondary">Make it easy to navigate</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-tabs') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-tabs.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Tabs</p>
                                                            <p class="text-secondary">Switch between information groups</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-breadcrumbs') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-breadcrumb.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Breadcrumb</p>
                                                            <p class="text-secondary">Know where you are and go back</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-scrollspy') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-scrollspy.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Scrollspy</p>
                                                            <p class="text-secondary">Smoothly reach to content section</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-sidebars') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-sidebar.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Sidebars</p>
                                                            <p class="text-secondary">Switch between information groups</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-5">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Selections</p>
                                            <p class="text-secondary">These components, such as radio buttons, checkboxes, and switches, allow users to make selections. They enhance interactivity by providing clear options for user input.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-radios') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-radio.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Radio</p>
                                                            <p class="text-secondary">Choice when one of it required</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-checkboxes') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-checkbox.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Checkbox</p>
                                                            <p class="text-secondary">Choices of multiple selection</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-switches') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-switch.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Switch</p>
                                                            <p class="text-secondary">Get quick 0/1, yes/no decision </p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-sliders') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-range.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Sliders</p>
                                                            <p class="text-secondary">Is to increase-decrease value</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-6">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Form Controls</p>
                                            <p class="text-secondary">These components, including input fields, select, and input validation, facilitate user data entry. They ensure efficient and accurate data collection within forms.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-inputs') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-inputs.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Inputs</p>
                                                            <p class="text-secondary">Common fields used for forms</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-selects') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-select.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Select</p>
                                                            <p class="text-secondary">Dropdown for item selection</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-input-groups') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-input-groups.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Input Group</p>
                                                            <p class="text-secondary">Group suffix, prefix with inputs</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-floating-label') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-floating-label.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Floating Labels</p>
                                                            <p class="text-secondary">Stylish and intuitive inputs</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-validation') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-validation.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Validations</p>
                                                            <p class="text-secondary">Show status of data by validating</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-7">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Icons</p>
                                            <p class="text-secondary">Icons visually represent actions, statuses, or categories, enhancing user interface clarity. They improve navigation and provide intuitive visual cues for users.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-bootstrap-icons') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-bootstrap-icons.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Bootstrap Icons</p>
                                                            <p class="text-secondary">v 1.11.x includes 2000+ icons</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-feather-icons') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-feather-icons.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Feather Icons</p>
                                                            <p class="text-secondary">v 4.29.1 includes 280+ icons</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row pt-5" id="list-item-8">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Third Party Plugins</p>
                                            <p class="text-secondary">These components, sourced from external libraries, enhance functionality and design. They integrate seamlessly to provide advanced features and improve development efficiency. We have modified styles to best match with our templates.</p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-chartjs') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-chartjs.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Chart Js</p>
                                                            <p class="text-secondary">Beautiful different charts library</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-datatable') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-datatable.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">DataTable</p>
                                                            <p class="text-secondary">Make simple table more useful</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-daterangepicker') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-daterangepicker.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">DateRange Picker</p>
                                                            <p class="text-secondary">Select date range or date</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-dragula') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-dragula.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Dragula</p>
                                                            <p class="text-secondary">Drag and drop DOM in columns</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-full-calendar') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-full-calendar.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Full Calendar</p>
                                                            <p class="text-secondary">Display event with ease</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-dropzone') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-dropzone.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Dropzone</p>
                                                            <p class="text-secondary">Upload with just drag and drop</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-progressbar-js') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-progressbar.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Progressbar js</p>
                                                            <p class="text-secondary">Circular progress made easy</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-smartwizard') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-smartwizard.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">SmartWizard</p>
                                                            <p class="text-secondary">Get steps form filled easily</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-swiper-carousel') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-swiper-carousel.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Swiper Carousel</p>
                                                            <p class="text-secondary">Awesome content and banners</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                    </div>
                                    <div class="row pt-5" id="list-item-9">
                                        <div class="col-12 mb-4 mt-5">
                                            <p class="h4">Utility</p>
                                            <p class="text-secondary">Utilities provide essential helper functions like spacing, alignment, and sizing control. They enhance the flexibility and customization of components, ensuring a consistent and responsive design.</p>
                                            <p class="text-secondary">There are more from Bootstrap framework <a href="https://getbootstrap.com/docs/5.3/components/accordion/" target="_blank">Checkout official document</a></p>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-avatar') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-avatar.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Avatar</p>
                                                            <p class="text-secondary">Different sizes of avatars</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-colors') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-color.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Colors</p>
                                                            <p class="text-secondary">Color schemes available</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-heights-widths') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-heights.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Height & Widths</p>
                                                            <p class="text-secondary">Customized heights and widths</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="{{ route('preview.component-margin-padding') }}" class="card adminuiux-card style-none mb-4">
                                                <div class="coverimg height-220 card-img-top">
                                                    <img class="mw-100" src="{{ asset('vendor/learninguiux/img/components/admin-ui-ux-bootstrap-html-template-margin.png') }}" alt="">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <p class="h5 mb-2">Padding and Margins</p>
                                                            <p class="text-secondary">Make it more spacious & separate</p>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="bi bi-box-arrow-up-right text-theme-accent-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>

                <!-- standard index footer -->
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



