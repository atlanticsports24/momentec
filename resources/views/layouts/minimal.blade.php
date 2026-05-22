<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Momentec') | Momentec</title>
    <meta name="description" content="@yield('meta_description', 'Momentec sports apparel catalog.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white py-4">
        <div class="mx-auto max-w-[1280px] px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-xl font-bold text-primary">Momentec</a>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    @include('components.footer')

    @stack('scripts')
</body>
</html>
