<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .logout-button { background: #e74c3c; color: #fff; padding: 8px 16px; border: none; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>

    <h1>Client Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }}!</p>

    <p>Your role: <strong>{{ auth()->user()->role }}</strong></p>

    <!-- Logout Form -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-button">Logout</button>
    </form>

</body>
</html>
