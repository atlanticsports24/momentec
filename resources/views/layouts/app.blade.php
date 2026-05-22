<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Momentec') | Momentec</title>
    <meta name="description" content="@yield('meta_description', 'Shop top sports apparel brands. Browse products by brand, category, color and size.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    @hasSection('og_tags')
        @yield('og_tags')
    @else
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('title', 'Momentec') | Momentec">
        <meta property="og:description" content="@yield('meta_description', 'Shop top sports apparel brands at Momentec.')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Momentec">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @hasSection('schema_json')
        @yield('schema_json')
    @endif

    @stack('head')
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    @include('components.header')

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    @include('components.footer')
    @include('components.back-to-top')

    @stack('scripts')
</body>
</html>
