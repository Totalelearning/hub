@extends('layouts.learninguiux')

@section('title', 'Parent Portal - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .parent-hero-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.10);
        background: rgba(255, 255, 255, 0.97);
    }

    .parent-summary-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(43, 82, 138, 0.08);
        background: #fff;
    }
</style>
@endpush

@section('content')
@include('app.partials.parent-header')

<div class="adminuiux-wrap">
    @include('app.partials.parent-sidebar', ['active' => 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">

            {{-- Hero --}}
            <div class="card parent-hero-card mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Parent Dashboard</div>
                            <h1 class="fs-2 fw-bold mb-1">Welcome, <span class="text-theme-1">{{ explode(' ', $user->name)[0] }}</span></h1>
                            <p class="text-secondary mb-0">Your school has assigned the training below. Complete each course at your own pace.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card parent-summary-card">
                        <div class="card-body p-3 text-center">
                            <div class="fs-3 fw-bold text-primary">{{ $summary['total'] }}</div>
                            <div class="small text-secondary">Total Courses</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card parent-summary-card">
                        <div class="card-body p-3 text-center">
                            <div class="fs-3 fw-bold text-warning">{{ $summary['outstanding'] }}</div>
                            <div class="small text-secondary">Outstanding</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card parent-summary-card">
                        <div class="card-body p-3 text-center">
                            <div class="fs-3 fw-bold text-info">{{ $summary['in_progress'] }}</div>
                            <div class="small text-secondary">In Progress</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card parent-summary-card">
                        <div class="card-body p-3 text-center">
                            <div class="fs-3 fw-bold text-success">{{ $summary['completed'] }}</div>
                            <div class="small text-secondary">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Courses --}}
            <div class="card parent-hero-card mb-4">
                <div class="card-body p-4 p-lg-5">
                    <h4 class="fw-bold mb-1">Your Courses</h4>
                    <p class="text-secondary mb-4">Complete these courses assigned by your child's school.</p>

                    <div class="row">
                        @forelse ($courses as $course)
                            @include('app.partials.feed-course-card', ['course' => $course])
                        @empty
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-check-circle text-success" style="font-size:3rem;"></i>
                                <h5 class="mt-3 text-secondary">No courses assigned yet</h5>
                                <p class="text-secondary mb-0">When your school assigns training, it will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
