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
        roomId: @json($roomId)
    };
</script>

@section('title', 'Message')

@push('scripts')
<style>
.chat-main-container {
    height: 550px;
    overflow: hidden;
}
@media (max-width: 991px) {
    /* On mobile/tablet: fill the visible viewport minus navbar (~60px) and page padding (~70px) */
    .chat-main-container {
        height: calc(100dvh - 140px);
    }
}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="chat-main-container">
                <div id="chat-app" style="height:100%;"></div>
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
