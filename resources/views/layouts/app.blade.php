<html data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinAnalysis</title>
    <script src="{{ asset('js/script.js') }}"></script>
    {{-- <script src="{{ asset('js/pico.js') }}"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<svg width="0" height="0" style="position: absolute;">
    <defs>
        <filter id="glass-distortion" x="0%" y="0%" width="100%" height="100%">
            <feTurbulence type="fractalNoise" baseFrequency="0.008 0.008" numOctaves="2" seed="92"
                result="noise" />
            <feGaussianBlur in="noise" stdDeviation="2" result="blurred" />
            <feDisplacementMap in="SourceGraphic" in2="blurred" scale="149" xChannelSelector="R"
                yChannelSelector="G" />
        </filter>
    </defs>
</svg>

<body>
    <main class="container">
        <article class="card">
            @yield('content')
        </article>
    </main>
</body>

</html>
