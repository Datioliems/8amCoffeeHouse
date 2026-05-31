<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '8AM Coffee - Backend')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chivo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F3F2] text-[#1A1A1A]" x-data="{ mobileNav: false }">

<aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-[#522C25]/10 bg-[#FCFAFA] lg:flex">
    <div class="flex h-20 items-center gap-3 border-b border-[#522C25]/10 px-5">
        <img src="{{ asset('images/logo8am.jpg') }}" alt="8AM Coffee" class="h-11 w-11 rounded-xl object-cover ring-1 ring-[#522C25]/10">
        <div>
            <p class="font-semibold leading-none">8am.coffee</p>
            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-[#522C25]/60">business</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('dashboard') ? 'bg-[#1A1A1A] text-white' : 'text-[#522C25] hover:bg-[#F2F2F2]' }}">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white/15' : 'bg-white' }}">⌂</span>
            Tổng quan
        </a>
        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('orders.*') || request()->routeIs('payment.*') ? 'bg-[#1A1A1A] text-white' : 'text-[#522C25] hover:bg-[#F2F2F2]' }}">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('orders.*') || request()->routeIs('payment.*') ? 'bg-white/15' : 'bg-white' }}">□</span>
            Đơn hàng
        </a>
        <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('inventory.*') ? 'bg-[#1A1A1A] text-white' : 'text-[#522C25] hover:bg-[#F2F2F2]' }}">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('inventory.*') ? 'bg-white/15' : 'bg-white' }}">▣</span>
            Kho hàng
        </a>
        @if(session('chuc_vu') === 'quan_ly')
        <a href="{{ route('menu.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('menu.*') ? 'bg-[#1A1A1A] text-white' : 'text-[#522C25] hover:bg-[#F2F2F2]' }}">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('menu.*') ? 'bg-white/15' : 'bg-white' }}">☕</span>
            Thực đơn
        </a>
        @endif
        <a href="{{ route('ban.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ request()->routeIs('ban.*') ? 'bg-[#1A1A1A] text-white' : 'text-[#522C25] hover:bg-[#F2F2F2]' }}">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('ban.*') ? 'bg-white/15' : 'bg-white' }}">◇</span>
            Bàn & QR
        </a>
    </nav>

    <div class="border-t border-[#522C25]/10 p-4">
        <div class="mb-3 rounded-2xl bg-[#F2F2F2] p-3">
            <p class="text-sm font-semibold">{{ session('ten_nv', 'Nhân viên') }}</p>
            <p class="mt-1 text-xs text-[#522C25]/60">{{ session('chuc_vu', 'staff') }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-xl px-3 py-2 text-left text-sm text-[#BB0011] hover:bg-red-50">
                Đăng xuất
            </button>
        </form>
    </div>
</aside>

<main class="min-h-screen lg:pl-64">
    <header class="sticky top-0 z-30 flex h-16 items-center border-b border-[#522C25]/10 bg-[#FCFAFA]/95 px-4 backdrop-blur md:px-6">
        <button class="mr-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white text-[#522C25] ring-1 ring-[#522C25]/10 lg:hidden" @click="mobileNav = !mobileNav" aria-label="Mở menu">
            ☰
        </button>
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-[#522C25]/60">8am operations</p>
            <h1 class="mt-0.5 text-lg font-semibold">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="ml-auto hidden items-center gap-3 md:flex">
            <span class="rounded-full bg-white px-3 py-1.5 text-xs text-[#522C25] ring-1 ring-[#522C25]/10">{{ now()->format('d/m/Y') }}</span>
            <span class="rounded-full bg-[#E82C2A] px-3 py-1.5 text-xs font-semibold text-white">{{ session('chuc_vu', '') }}</span>
        </div>
    </header>

    <div x-show="mobileNav" @click.outside="mobileNav = false" class="fixed left-4 right-4 top-20 z-50 rounded-2xl bg-white p-3 shadow-xl ring-1 ring-[#522C25]/10 lg:hidden" style="display: none;">
        <a href="{{ route('dashboard') }}" class="block rounded-xl px-3 py-2 text-sm">Tổng quan</a>
        <a href="{{ route('orders.index') }}" class="block rounded-xl px-3 py-2 text-sm">Đơn hàng</a>
        <a href="{{ route('inventory.index') }}" class="block rounded-xl px-3 py-2 text-sm">Kho hàng</a>
        @if(session('chuc_vu') === 'quan_ly')
            <a href="{{ route('menu.index') }}" class="block rounded-xl px-3 py-2 text-sm">Thực đơn</a>
        @endif
        <a href="{{ route('ban.index') }}" class="block rounded-xl px-3 py-2 text-sm">Bàn & QR</a>
    </div>

    <section class="p-4 md:p-6">
        @if(session('success'))
            <x-alert-toast type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert-toast type="error" :message="session('error')" />
        @endif
        @yield('content')
    </section>
</main>

</body>
</html>
