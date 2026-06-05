<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <!-- Global Page Loader -->
    <div id="global-loader" style="position: fixed; inset: 0; background-color: #f8fafc; z-index: 99999; display: flex; align-items: center; justify-content: center; transition: opacity 0.5s ease; flex-direction: column; gap: 1rem;">
        <div style="width: 4rem; height: 4rem; border: 4px solid #bfdbfe; border-top-color: #2563eb; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
        <p style="color: #475569; font-weight: 500; font-family: sans-serif; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">Loading Dashboard...</p>
        <style>@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }</style>
    </div>
    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('global-loader');
            if(loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.remove(), 500);
            }
        });
    </script>
    @yield('content')
    
    @stack('scripts')
</body>

</html>