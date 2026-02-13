<!DOCTYPE html>
<html>
<head>
    <title>Two Factor Authentication</title>
</head>
<body>

<h2>Two Factor Authentication Setup</h2>

@if(isset($qrCode))
    <p>Scan this QR code with Google Authenticator:</p>
    <div>{!! $qrCode !!}</div> <!-- Render raw HTML -->
@endif

<form method="POST" action="{{ url('/2fa-verify') }}">
    @csrf
    <input type="text" name="token" maxlength="6" placeholder="Enter 6-digit code">
    <button type="submit">Verify</button>
</form>

@if($errors->any())
    <div style="color:red;">
        {{ $errors->first() }}
    </div>
@endif


</body>
</html>



