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
    function showToast(title, message) {
    toastMsg.value = {
    title,
    message,
  };
  const toastEl = document.getElementById("bs_toast");

  const toastBootstrap = Toast.getOrCreateInstance(toastEl);
  toastBootstrap.show();
}
</script>
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
    @endsection
