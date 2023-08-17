<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
  
    <title>{{ config('app.name', 'Laravel') }}</title>
  
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="/css/app.css">
</head>
<body data-bs-theme="dark">
    <div class="d-flex flex-column h-100">
        @include('includes.header', ['items' => $header])
        <div class="row ms-sidebar">
            @yield('main')
        </div>
    </div>

    <script>
        // Function to check window size and toggle content
        function toggleContentBasedOnWindowSize() {
            if (window.innerWidth < 768) {
                document.getElementById('mobile-navbar').style = 'display:block !important';
                document.getElementById('desktop-navbar').style = 'display:none !important';
            } else {
                document.getElementById('mobile-navbar').style = 'display:none !important';
                document.getElementById('desktop-navbar').style = 'display:flex !important';
            }
        }
    
        // Initial check on page load
        toggleContentBasedOnWindowSize();
    
        // Event listener for window resize
        window.addEventListener('resize', toggleContentBasedOnWindowSize);
    </script>
</body>
</html>