<!DOCTYPE html>
<html>
<head>
    <title>Two Factor Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
            color: #333;
        }

        p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .qr-wrapper {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        input[type="text"] {
            /* width: 100%; */
            padding: 12px;
            margin-top: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            text-align: center;
            transition: 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78,115,223,0.15);
        }

        input[type="text"]:focus::placeholder {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        button {
            /* width: 100%; */
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background: #4e73df;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #2e59d9;
        }

        .error-message {
            margin-top: 15px;
            padding: 10px;
            background: #ffe3e3;
            color: #d90429;
            border-radius: 6px;
            font-size: 14px;
        }

        @media(max-width: 480px){
            .container{
                margin: 20px;
                padding: 25px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔐 Two-Factor Authentication</h2>

    @if(isset($qrCode))
        <p>Scan this QR code using Google Authenticator</p>
        <div class="qr-wrapper">
            {!! $qrCode !!}
        </div>
    @endif

    <form method="POST" action="{{ url('/2fa-verify') }}">
        @csrf
        <input type="text" name="token" maxlength="6" placeholder="Enter 6-digit code" required>
        <button type="submit">Verify Code</button>
    </form>

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif
</div>

</body>
</html>
