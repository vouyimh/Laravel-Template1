@extends('layouts/contentNavbarLayout')

@php
    $role = auth()->user()->role;
    $routePrefix = $role . '.tasks.';
@endphp

<script>
    window.__app__ = {
        rooms: @json($rooms ?? [['id' => 1]]),
        user: @json(auth()->user()),
        emojis: @json($emojis ?? []),
        appName: "My App",
        confettiWords: ["wow", "nice", "good"],
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

@section('title', 'Message')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div style="height:550px" class="card mb-6">
                    <div class="container mt-5">
                        <div id="chat-app" data-room-id="{{ $roomId }}"> </div>
                    </div>
            </div>
        </div>
    @endsection
