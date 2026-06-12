@extends('layouts/contentNavbarLayout')

@php
    $role = auth()->user()->role;
    $routePrefix = $role . '.tasks.';
@endphp

<script>
    window.__app__ = {
        rooms: @json($data['rooms']),
        user: @json($data['user']),
        emojis: @json($data['emojis']),
        appName: @json($data['appName']),
        confettiWords: @json($data['confettiWords']),
    };
</script>

@section('title', 'Room')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div style="height:550px" class="card mb-6">
                    <div class="container mt-5">
                        <div id="room-app" > </div>
                    </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="bs_toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>
@endsection
