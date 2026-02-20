<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    {{-- Navigation --}}
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">
                        {{ config('app.name') }}
                    </a>
                    @auth
                        <div class="hidden md:flex space-x-4">
                            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('posts.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('posts.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Posts
                            </a>
                            <a href="{{ route('schedules.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('schedules.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Schedules
                            </a>
                            <a href="{{ route('social-accounts.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('social-accounts.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Accounts
                            </a>
                            @if(auth()->user()->subscriptionPlan()?->can_upload_files)
                                <a href="{{ route('data-files.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('data-files.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                                    Data Files
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('subscription.plans') }}" class="text-sm text-gray-600 hover:text-gray-900">Plans</a>
                        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Sign Up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md mb-4">
                {{ session('warning') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-500 text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
