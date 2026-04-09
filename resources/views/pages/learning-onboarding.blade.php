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

            <!-- page wrapper -->
            <div class="adminuiux-wrap">

                <main class="flex-shrink-0 pt-0 h-100">
                    <div class="container-fluid">
                        <div class="auth-wrapper">
                            <!--Page body-->

                            <!-- Content  -->
                            <div class="container mt-4" id="main-content">

                                <div class="d-flex flex-column height-dynamic" style="--h-dynamic: calc(100vh - 1.5rem)">

                                    <!-- Onboarding banner -->
                                    <div class="row align-items-center mb-4">
                                        <div class="col-12 col-sm py-3">
                                            <h2>Getting started is easy & quick!</h2>
                                            <p class="text-secondary">Provide your information to quick start appointment bookings.</p>
                                        </div>
                                        <div class="col-auto py-3">
                                            <a class="d-inline-flex w-auto mx-auto style-none" href="{{ route('preview.learning-dashboard') }}">
                                                <img data-bs-img="light" src="{{ asset('vendor/learninguiux/img/logo-light.svg') }}" alt="" class="mx-3">
                                                <img data-bs-img="dark" src="{{ asset('vendor/learninguiux/img/logo.svg') }}" alt="" class="mx-3">
                                                <div>
                                                    <p class="h5 mb-0">Learning<b>UIUX</b></p>
                                                    <p class="text-secondary small">HTML template</p>
                                                </div>
                                            </a>
                                        </div>

                                    </div>

                                    <div>
                                        <!-- SmartWizard html -->
                                        <div class="card adminuiux-card overflow-hidden mb-4" id="smartwizard">
                                            <ul class="nav">
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#step-1">
                                                        <div class="num">1</div>
                                                        <div>
                                                            <p class="h5 mb-0">Details</p>
                                                            <p class="opacity-75 small">Name and description</p>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#step-2">
                                                        <div class="num">2</div>
                                                        <div>
                                                            <p class="h5 mb-0">Contact</p>
                                                            <p class="opacity-75 small">Parents and address</p>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link " href="#step-3">
                                                        <div class="num">3</div>
                                                        <div>
                                                            <p class="h5 mb-0">Interest Areas</p>
                                                            <p class="opacity-75 small">Let us know what you like</p>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="card-body pb-0">
                                                <div class="tab-content">
                                                    <!-- details -->
                                                    <div id="step-1" class="tab-pane px-0" role="tabpanel" aria-labelledby="step-1">
                                                        <div class="row">
                                                            <div class="col-12 col-lg-3 text-center mb-3">
                                                                <figure class="maxwidth-250 height-260 coverimg rounded position-relative mx-auto mb-3">
                                                                    <img src="{{ asset('vendor/learninguiux/img/modern-ai-image/user-3.jpg') }}" alt="">
                                                                    <div class="position-absolute end-0 bottom-0 m-3">
                                                                        <button class="btn btn-square btn-accent rounded-circle" onclick="$(this).next().click()">
                                                                            <i class="bi bi-upload"></i>
                                                                        </button>
                                                                        <input type="file" class="d-none">
                                                                    </div>
                                                                </figure>
                                                                <p class="h5">Adminuiux Smith</p>
                                                                <p class="small text-secondary">kimsan1852@tgmails.com</p>
                                                            </div>
                                                            <div class="col">
                                                                <!-- details -->
                                                                <p class="h6 py-2 mb-2">Basic Details</p>
                                                                <div class="row ">
                                                                    <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                        <div class="form-floating mb-3">
                                                                            <input type="text" class="form-control" id="namef" placeholder="Enter First Name" value="Adminuiux">
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
                                                                            <input type="text" class="form-control" id="namel" placeholder="Enter Last Name " value="Smith">
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
                                                                <div class="row align-items-center mb-2">
                                                                    <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                                                        <div class="form-floating mb-3">
                                                                            <input type="text" class="form-control" id="tags1" placeholder="Enter Skill 1" value="Badminton">
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
                                                    <div id="step-2" class="tab-pane px-0" role="tabpanel" aria-labelledby="step-2">
                                                        <div class="row align-items-center mb-2">
                                                            <div class="col">
                                                                <p class="h6 py-2">Parent Contact</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <button class="btn btn-link"><i data-feather="plus"></i> Add Another</button>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="namefull" placeholder="Enter Full Name" value="Alice Johnson">
                                                                    <label for="namefull">Parent Full Name</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="phoneon2" placeholder="Enter phone" value="+91 4152 21A45488004">
                                                                    <label for="phoneon2">Enter Phone</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                                                <div class="form-floating mb-3">
                                                                    <input type="email" class="form-control" id="emailaddresson1" placeholder="Enter Email Address" value="kimsan1852@tgmails.com" disabled>
                                                                    <label for="emailaddresson1">Email Address</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12 col-lg-3">
                                                                <p class="h6 py-2 mb-2">Locate on Map</p>
                                                                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d788.4385190507815!2d-122.4278138198206!3d37.772364180178094!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1712990839970!5m2!1sen!2sin" height="280" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="w-100 rounded mb-3"></iframe>
                                                            </div>
                                                            <div class="col">
                                                                <div class="row align-items-center mb-2">
                                                                    <div class="col">
                                                                        <p class="h6 py-2">Your Address</p>
                                                                    </div>
                                                                    <div class="col-auto">

                                                                    </div>
                                                                </div>
                                                                <div class="form-floating mb-3">
                                                                    <input type="text" class="form-control" id="house1" placeholder="Enter House Owner Name">
                                                                    <label for="house1">House Owner Name</label>
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

                                                    <div id="step-3" class="tab-pane px-0" role="tabpanel" aria-labelledby="step-3">
                                                        <div class="row my-2">
                                                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                                                <div class="card h-100 theme-blue selectable anyone">
                                                                    <div class="card-body">
                                                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3">
                                                                            <i class="bi bi-house h4"></i>
                                                                        </div>
                                                                        <h5 class="text-theme-1">Schooling</h5>
                                                                        <p class="text-secondary">I am more interested in schooling with great environment and learning experience.</p>
                                                                        <p class="text-secondary"><i class="bi bi-check-circle me-1"></i> Select</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                                                <div class="card h-100 theme-purple selectable anyone">
                                                                    <div class="card-body">
                                                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3">
                                                                            <i class="bi bi-people h4"></i>
                                                                        </div>
                                                                        <h5 class="text-theme-1">Creativity</h5>
                                                                        <p class="text-secondary">I love to draw and doing creative activity. It gives me freedom to do new things.</p>
                                                                        <p class="text-secondary"><i class="bi bi-check-circle me-1"></i> Select</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 col-md-3 col-lg-3 col-xl-3 mb-4">
                                                                <div class="card h-100 theme-red selectable anyone">
                                                                    <div class="card-body">
                                                                        <div class="avatar avatar-60 rounded bg-theme-1 mb-3">
                                                                            <i class="bi bi-mortarboard h4"></i>
                                                                        </div>
                                                                        <h5 class="text-theme-1">Innovation</h5>
                                                                        <p class="text-secondary">I like to develop new ideas and always keen to see it live in action in real world.</p>
                                                                        <p class="text-secondary"><i class="bi bi-check-circle me-1"></i> Select</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-3 col-lg-3 col-xl-3 mb-4">
                                                                <div class="card adminuiux-card position-relative overflow-hidden bg-theme-1 h-100">
                                                                    <div class="position-absolute top-0 start-0 h-100 w-100 z-index-0 coverimg opacity-50">
                                                                        <img src="{{ asset('vendor/learninguiux/img/learning/bg-overlay-1.png') }}" alt="">
                                                                    </div>
                                                                    <div class="card-body z-index-1">
                                                                        <h5 class="fw-normal mb-3">What is your desire to be?</h5>
                                                                        <p class="opacity-75">Here, your desires should be what you dreamed to be in future.</p>
                                                                        <div class="form-floating text-dark mb-3">
                                                                            <input type="text" class="form-control" id="desire" placeholder="Enter desire">
                                                                            <label for="desire">Your Desire</label>
                                                                        </div>
                                                                        <p class="opacity-75 small">*This is just for our reference</p>
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
                                    <div class="mt-auto">
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
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </main>


                <!-- Concatenated plugins -->
                <script src="{{ asset('vendor/learninguiux/js/app.js') }}"></script>
@endsection

@push('scripts')
<script src="{{ asset('vendor/learninguiux/js/learning/learning-onboarding.js') }}"></script>
@endpush





