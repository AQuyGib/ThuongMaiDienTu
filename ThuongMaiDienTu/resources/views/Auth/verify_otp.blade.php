<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh OTP - DienMayPro Security</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Include Vite Assets -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    
    <style>
        body { 
            margin: 0; padding: 0; font-family: 'Inter', sans-serif; 
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover; background-position: center; background-attachment: fixed;
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        }
    </style>
</head>
<body>
    @php
        $props = [
            'email' => $email ?? session('email'),
            'actionUrl' => route('password.verify.post'),
            'csrfToken' => csrf_token(),
            'errorMsg' => $errors->first(),
            'successMsg' => session('success'),
            'resendUrl' => route('password.request')
        ];
    @endphp

    <div id="verify-otp-app" data-props="{{ json_encode($props) }}"></div>
</body>
</html>
