<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Shop') — Momentec</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
<header class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="{{ route('store.shop') }}" class="text-xl font-semibold tracking-tight">Momentec</a>
        <nav class="flex gap-6 text-sm">
            <a href="{{ route('store.shop') }}" class="hover:text-blue-600">Shop</a>
            <a href="{{ route('store.cart') }}" class="hover:text-blue-600">Cart</a>
            <a href="{{ url('/admin') }}" class="text-gray-500 hover:text-blue-600">Admin</a>
        </nav>
    </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-8">
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
