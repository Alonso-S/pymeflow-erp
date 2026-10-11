<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Acceso a Sistema' }} - PymeFlow ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji"; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased bg-slate-100 text-slate-800">
    <div class="w-full max-w-md">
        @yield('content')
    </div>
</body>
</html>
