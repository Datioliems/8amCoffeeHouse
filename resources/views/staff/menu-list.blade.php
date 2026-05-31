@extends('layouts.app')
@section('title', 'Thực đơn - :am Coffee')
@section('page-title', 'Quản lý thực đơn')

@section('content')
@php
    $stockWarnings = $mons->getCollection()
        ->filter(fn($mon) => ($mon->trang_thai === 'active') && (bool) ($mon->het_hang_theo_kho ?? false));
@endphp

<div class="max-w-6xl">
    @if($stockWarnings->isNotEmpty())
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-800">Có món đang hết nguyên liệu</p>
                    <p class="mt-1 text-sm text-amber-700">
                        Nhân viên có thể ẩn món để khách không thấy món này trong thực đơn.
                    </p>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $stockWarnings->count() }} món cần kiểm tra
                </span>
            </div>
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                @foreach($stockWarnings->take(4) as $mon)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ $mon->ten_mon }}</p>
                            <p class="mt-0.5 truncate text-xs text-amber-700">
                                Hết: {{ collect($mon->nguyen_lieu_het ?? [])->implode(', ') }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('menu.destroy', $mon->ma_mon) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg bg-[#1A1A1A] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#522C25]">
                                Ẩn món
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2 overflow-x-auto">
            <a href="{{ route('menu.index') }}"
               class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-medium
                      {{ !request('category') ? 'bg-amber-100 text-amber-800' : 'border border-gray-200 bg-white text-gray-600 hover:border-amber-300' }}">
                Tất cả ({{ $mons->total() }})
            </a>
            @foreach($danhMucs as $dm)
            <a href="{{ route('menu.index', ['category' => $dm->ma_danh_muc]) }}"
               class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-medium
                      {{ request('category') === $dm->ma_danh_muc ? 'bg-amber-100 text-amber-800' : 'border border-gray-200 bg-white text-gray-600 hover:border-amber-300' }}">
                {{ $dm->ten_danh_muc }}
            </a>
            @endforeach
        </div>
        <a href="{{ route('menu.create') }}"
           class="flex shrink-0 items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-amber-600">
            + Thêm món
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach($mons as $mon)
        @php
            $imgUrl = $mon->image_url;
            $stockOut = (bool) ($mon->het_hang_theo_kho ?? false);
            $sCls = match(true) {
                $mon->trang_thai === 'active' && $stockOut => 'bg-red-100 text-red-700',
                $mon->trang_thai === 'active' => 'bg-green-100 text-green-700',
                $mon->trang_thai === 'het_hang' => 'bg-yellow-100 text-yellow-700',
                default => 'bg-gray-100 text-gray-500',
            };
            $sLabel = match(true) {
                $mon->trang_thai === 'active' && $stockOut => 'Hết nguyên liệu',
                $mon->trang_thai === 'active' => 'Đang bán',
                $mon->trang_thai === 'het_hang' => 'Hết hàng',
                default => 'Ẩn',
            };
        @endphp

        <div class="group overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:border-amber-300 hover:shadow-sm {{ $mon->trang_thai === 'an' ? 'opacity-60' : '' }}">
            <div class="relative aspect-square overflow-hidden bg-stone-100">
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $mon->ten_mon }}" loading="lazy"
                         class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                         onerror="this.parentNode.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-stone-100 text-stone-300\'><svg class=\'w-8 h-8\' fill=\'currentColor\' viewBox=\'0 0 20 20\'><path fill-rule=\'evenodd\' d=\'M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z\' clip-rule=\'evenodd\'/></svg></div>'">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-stone-100 text-stone-300">
                        <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
                <span class="absolute right-1.5 top-1.5 rounded-full px-1.5 py-0.5 text-[10px] font-medium {{ $sCls }}">
                    {{ $sLabel }}
                </span>
            </div>

            <div class="p-3">
                <p class="mb-0.5 text-[10px] uppercase tracking-wider text-gray-400">
                    {{ $mon->danhMuc?->ten_danh_muc }}
                </p>
                <p class="line-clamp-1 text-sm font-semibold text-gray-800">{{ $mon->ten_mon }}</p>
                @if($stockOut)
                    <p class="mt-1 line-clamp-1 text-xs text-red-600">
                        Thiếu: {{ collect($mon->nguyen_lieu_het ?? [])->implode(', ') }}
                    </p>
                @elseif($mon->mo_ta)
                    <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-gray-400">{{ $mon->mo_ta }}</p>
                @endif
                <p class="mt-1.5 text-sm font-bold text-amber-600">
                    {{ number_format($mon->don_gia, 0, ',', '.') }}đ
                </p>

                <div class="mt-2.5 flex gap-1.5 border-t border-gray-100 pt-2.5">
                    <a href="{{ route('menu.edit', $mon->ma_mon) }}"
                       class="flex-1 rounded-lg bg-gray-50 py-1.5 text-center text-xs font-medium text-gray-600 transition hover:bg-amber-50 hover:text-amber-700">
                        Sửa
                    </a>
                    <form method="POST" action="{{ route('menu.destroy', $mon->ma_mon) }}"
                          onsubmit="return confirm('Ẩn món {{ addslashes($mon->ten_mon) }}?')"
                          class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-lg bg-gray-50 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600">
                            Ẩn
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($mons->isEmpty())
    <div class="rounded-xl border border-gray-200 bg-white py-16 text-center">
        <p class="text-sm text-gray-500">Chưa có món nào.</p>
        <a href="{{ route('menu.create') }}" class="mt-2 inline-block text-sm text-amber-600 hover:underline">
            Thêm món đầu tiên
        </a>
    </div>
    @endif

    <div class="mt-4">{{ $mons->withQueryString()->links() }}</div>
</div>
@endsection
