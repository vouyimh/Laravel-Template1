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
    function showToast(title, message) {
        toastMsg.value = { title, message };
        const toastEl = document.getElementById("bs_toast");
        const toastBootstrap = Toast.getOrCreateInstance(toastEl);
        toastBootstrap.show();
    }
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
    @endsection
