@extends('layouts.customer')
@section('title', 'Thực đơn - :am Coffee')

@push('head')
@if($maOrder)
<meta name="ma-order" content="{{ $maOrder }}">
@endif
@endpush

@section('content')
<section class="grid gap-5 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
    <div class="overflow-hidden rounded-[1.75rem] bg-[#1A1A1A] text-white am-shadow">
        <div class="relative min-h-72 p-6 md:min-h-96 md:p-8">
            <img src="{{ asset('images/cafe_bg.jpg') }}" alt="Không gian 8AM Coffee" class="absolute inset-0 h-full w-full object-cover opacity-50">
            <div class="absolute inset-0 bg-gradient-to-t from-[#1A1A1A] via-[#1A1A1A]/45 to-transparent"></div>
            <div class="relative flex h-full min-h-60 flex-col justify-between md:min-h-80">
                <div class="flex items-center justify-between gap-3">
                    <span class="rounded-full bg-white/15 px-4 py-2 text-xs font-semibold backdrop-blur">
                        Bàn {{ str_pad($ban->so_ban, 2, '0', STR_PAD_LEFT) }} · {{ $ban->vi_tri }}
                    </span>
                    <span class="rounded-full bg-[#E82C2A] px-4 py-2 text-xs font-semibold">Đơn mới</span>
                </div>
                <div>
                    <p class="mb-3 text-xs uppercase tracking-[0.2em] text-white/70">quầy sáng 8am</p>
                    <h1 class="am-display max-w-xl text-5xl leading-none md:text-7xl">Chào buổi sáng!</h1>
                    <p class="mt-4 max-w-md text-sm leading-6 text-white/80 md:text-base">Chọn món tại bàn, nhân viên xác nhận ngay trên hệ thống. Ảnh món dùng từ thư viện hiện có của quán.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[1.75rem] bg-white p-5 ring-1 ring-[#522C25]/10 am-shadow">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <p class="am-mono text-xs uppercase tracking-[0.16em] text-[#522C25]/60">Sơ đồ quán</p>
                <h2 class="am-headline mt-1 text-xl font-semibold">Khu bàn hôm nay</h2>
            </div>
            <span class="rounded-full bg-[#CADCAC]/70 px-3 py-1.5 text-xs font-semibold text-[#3D4C27]">Sẵn sàng 3D</span>
        </div>
        <div class="am-store-stage h-64 overflow-hidden rounded-3xl bg-[#F2F2F2]">
            <div class="am-store-floor relative mx-auto mt-6 h-72 w-72 rounded-[2rem] border border-[#522C25]/10">
                <div class="absolute left-8 top-8 h-24 w-20 rounded-xl bg-[#522C25] shadow-xl"></div>
                <div class="absolute right-8 top-10 h-16 w-16 rounded-full bg-[#E82C2A] shadow-xl"></div>
                <div class="am-table-3d absolute bottom-16 left-16 h-16 w-16 rounded-2xl bg-white ring-4 ring-[#E82C2A]"></div>
                <div class="am-chair-3d absolute bottom-10 left-20 h-8 w-10 rounded-full bg-[#CADCAC]"></div>
                <div class="am-chair-3d absolute bottom-36 left-20 h-8 w-10 rounded-full bg-[#CADCAC]"></div>
                <div class="absolute bottom-14 right-14 grid grid-cols-2 gap-3">
                    <span class="h-12 w-12 rounded-xl bg-white shadow"></span>
                    <span class="h-12 w-12 rounded-xl bg-white shadow"></span>
                    <span class="h-12 w-12 rounded-xl bg-white shadow"></span>
                    <span class="h-12 w-12 rounded-xl bg-white shadow"></span>
                </div>
            </div>
        </div>
        <p class="mt-4 text-sm leading-6 text-[#522C25]/65">Khối này đang là minh họa nhẹ bằng CSS. Khi có mô hình AI 3D, bạn có thể thay bằng canvas Three.js và file `.glb` của quán.</p>
    </div>
</section>

<nav class="sticky top-16 z-30 -mx-4 mt-6 overflow-x-auto border-y border-[#522C25]/10 bg-[#FCFAFA]/95 px-4 py-3 backdrop-blur scrollbar-hide md:mx-0 md:rounded-full md:border md:px-3">
    <div class="flex w-max gap-2">
        <button @click="activeCategory = null"
                :class="activeCategory === null ? 'bg-[#E82C2A] text-white' : 'bg-white text-[#522C25] ring-1 ring-[#522C25]/10'"
                class="am-mono rounded-full px-5 py-2.5 text-sm transition">
            Tất cả
        </button>
        @foreach($danhMucs as $dm)
            @if($dm->mons->count() > 0)
            <button @click="activeCategory = '{{ $dm->ma_danh_muc }}'"
                    :class="activeCategory === '{{ $dm->ma_danh_muc }}' ? 'bg-[#E82C2A] text-white' : 'bg-white text-[#522C25] ring-1 ring-[#522C25]/10'"
                    class="am-mono whitespace-nowrap rounded-full px-5 py-2.5 text-sm transition">
                {{ $dm->ten_danh_muc }}
            </button>
            @endif
        @endforeach
    </div>
</nav>

<div class="mt-7 space-y-10">
@forelse($danhMucs as $dm)
    @if($dm->mons->count() > 0)
    <section x-show="activeCategory === null || activeCategory === '{{ $dm->ma_danh_muc }}'"
             x-transition.opacity
             class="scroll-mt-32">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <p class="am-mono text-xs uppercase tracking-[0.16em] text-[#522C25]/55">Danh mục</p>
                <h2 class="am-display mt-1 text-4xl leading-none text-[#1A1A1A]">{{ $dm->ten_danh_muc }}</h2>
            </div>
            <span class="rounded-full bg-[#F2F2F2] px-3 py-1 text-xs font-semibold text-[#522C25]/70">{{ $dm->mons->count() }} món</span>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($dm->mons as $mon)
                <x-menu-item-card :mon="$mon" :order="$maOrder ?? null" />
            @endforeach
        </div>
    </section>
    @endif
@empty
    <div class="rounded-3xl border border-dashed border-[#522C25]/20 bg-white py-16 text-center text-sm text-[#522C25]/60">
        Thực đơn đang được cập nhật.
    </div>
@endforelse
</div>
@endsection
