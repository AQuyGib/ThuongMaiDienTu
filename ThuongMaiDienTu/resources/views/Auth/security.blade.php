<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm Bảo mật - DienMayPro</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Include Vite Assets -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .dark body { background-color: #020617; }
    </style>
</head>
<body class="{{ session('theme', 'light') }}">
    
    <!-- Mount React Component Here -->
    <div id="security-settings-app" data-props="{{ json_encode(compact('user', 'sessions', 'score', 'details', 'securityTier', 'tierColor')) }}"></div>

</body>
</html>
