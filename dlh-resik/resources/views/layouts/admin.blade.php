<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin - SIMPELSI')</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo_simpelsi.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">

    @stack('styles')
</head>

<body class="fade-in">
    {{-- Include Header --}}
    @include('admin.partials.header')

    {{-- Include Sidebar --}}
    @include('admin.partials.sidebar')

    {{-- Main Content --}}
    <div class="main-content" id="mainContent">
        @yield('content')
    </div>

    {{-- Include Scripts --}}
    @include('admin.partials.scripts')

    @stack('scripts')
</body>

</html>
