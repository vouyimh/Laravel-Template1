@extends('layouts/contentNavbarLayout')

@php
    $role = auth()->user()->role ?? null;
@endphp

@section('title', $role === 'staff' ? __('My Tasks') : __('Daily Tasks Manager'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-6">
                <div class="container mt-5">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="text-center mb-4">
                                @if ($role === 'staff')
                                    {{ __('My Assigned Tasks') }}
                                @else
                                    {{ __('Daily Tasks Manager') }}
                                @endif
                            </h1>
                            <p class="text-center">
                                @if ($role === 'staff')
                                    {{ __('Start a task to capture your location, upload proof, and mark it complete.') }}
                                @else
                                    {{ __('Here you can view, create, edit, and delete your daily tasks.') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($role === 'staff')
                        @include('tasks._staff_board', ['tasks' => $tasks])
                    @else
                        @include('tasks._admin_table', ['tasks' => $tasks])
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
