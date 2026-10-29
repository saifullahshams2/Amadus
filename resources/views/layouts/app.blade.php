<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AMADEUS GDS SIM')</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #f8fafc;
            --foreground: #0f172a;
        }
        [x-cloak] { display: none !important; }
        html, body {
            min-height: 100%;
            color: var(--foreground);
            background: var(--background);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-overflow-scrolling: touch;
        }
        .crt-screen::before {
            content: " ";
            display: block;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.15) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 20;
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#f8fafc] text-slate-800 antialiased selection:bg-blue-600 selection:text-white @yield('body_class')">
    @yield('content')
    @include('partials.dynamic-popup')
</body>
</html>
