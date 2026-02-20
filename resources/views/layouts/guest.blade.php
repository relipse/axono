<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Welcome')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="pf-auth-wrapper">
        <div class="pf-auth-brand">
            <a href="{{ route('home') }}">{{ config('app.name') }}</a>
        </div>
        <h2 class="pf-auth-heading">@yield('heading')</h2>

        <div class="pf-auth-card">
            @if($errors->any())
                <div class="pf-alert pf-alert-danger pf-mb-6">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
