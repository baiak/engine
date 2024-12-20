<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livewire Test</title>
    @livewireStyles
    @filamentStyles
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
<div class="container mx-auto">
    @yield('content')
</div>
@livewireScripts
@filamentScripts
</body>
</html>
