<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin RESIK')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    @stack('styles')
</head>
<body>
    @include('admin.partials.sidebar')

    <div class="admin-wrapper" style="margin-left: 260px; padding: 20px;">
        {{-- Navbar bisa ditaruh di sini --}}
        @include('admin.partials.navbar')

        <main>
            @yield('content')
        </main>
    </div>

    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
    @stack('scripts')
</body>
</html>
