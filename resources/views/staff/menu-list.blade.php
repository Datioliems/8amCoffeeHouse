@extends('layouts.app')
@section('title', 'Thực đơn — :am Coffee')
@section('page-title', 'Quản lý thực đơn')

@section('content')
<div class="max-w-6xl">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div class="flex gap-2 overflow-x-auto">
            <a href="{{ route('menu.index') }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap
                      {{ !request('category') ? 'bg-amber-100 text-amber-800' : 'bg-white border border-gray-200 text-gray-600 hover:border-amber-300' }}">
                Tất cả ({{ $mons->total() }})
            </a>
            @foreach($danhMucs as $dm)
            <a href="{{ route('menu.index', ['category' => $dm->ma_danh_muc]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap
                      {{ request('category') === $dm->ma_danh_muc ? 'bg-amber-100 text-amber-800' : 'bg-white border border-gray-200 text-gray-600 hover:border-amber-300' }}">
                {{ $dm->ten_danh_muc }}
            </a>
            @endforeach
        </div>
        <a href="{{ route('menu.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium
                  px-4 py-2 rounded-xl transition flex items-center gap-1.5 flex-shrink-0">
            + Thêm món
        </a>
    </div>

    {{-- Grid ảnh --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-6">
        @foreach($mons as $mon)
        @php
        $imgMap = [
            'MON001'=>'espresso.jpg',    'MON002'=>'americano.jpg',  'MON003'=>'latte.jpg',
            'MON004'=>'cam_em.jpg',      'MON005'=>'salted_caramel.jpg','MON006'=>'ca_phe_muoi.jpg',
            'MON007'=>'ca_phe_trung.png','MON008'=>'lady_sweet.jpg', 'MON009'=>'ginger_latte.jpg',
            'MON010'=>'v60.jpg',         'MON011'=>'origami.jpg',    'MON012'=>'cold_brew.jpg',
            'MON013'=>'cold_brew_mo.png','MON014'=>'cold_brew.jpg',  'MON015'=>'tonic.jpg',
            'MON016'=>'nhiet_doi.jpg',   'MON017'=>'ca_phe_den.webp','MON018'=>'ca_phe_nau.jpg',
            'MON019'=>'bac_xiu.jpg',     'MON020'=>'sua_chua_ca_phe.jpg','MON021'=>'ca_cao.jpg',
            'MON022'=>'chanh_xi_muoi.jpg','MON023'=>'chanh_leo.png', 'MON024'=>'tra_oi_hong.jpg',
            'MON025'=>'tra_chanh_dao.jpg','MON026'=>'banh_sung_bo.jpg',
            'MON027'=>'banh_sung_bo_socola.jpg','MON028'=>'hat_sen_say.jpg',
        ];
        $imgFile = $mon->hinh_anh ?? ($imgMap[$mon->ma_mon] ?? null);
        $imgUrl  = $imgFile ? asset('images/' . $imgFile) : null;
        $sCls = match($mon->trang_thai) {
            'active'   => 'bg-green-100 text-green-700',
            'het_hang' => 'bg-yellow-100 text-yellow-700',
            default    => 'bg-gray-100 text-gray-500',
        };
        $sLabel = match($mon->trang_thai) {
            'active'   => 'Đang bán',
            'het_hang' => 'Hết hàng',
            default    => 'Ẩn',
        };
        @endphp

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden
                    hover:border-amber-300 hover:shadow-sm transition group
                    {{ $mon->trang_thai === 'an' ? 'opacity-60' : '' }}">

            {{-- Ảnh --}}
            <div class="aspect-square bg-stone-100 overflow-hidden relative">
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $mon->ten_mon }}" loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         onerror="this.parentNode.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-stone-100 text-stone-300\'><svg class=\'w-8 h-8\' fill=\'currentColor\' viewBox=\'0 0 20 20\'><path fill-rule=\'evenodd\' d=\'M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z\' clip-rule=\'evenodd\'/></svg></div>'">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-stone-100 text-stone-300">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
                <span class="absolute top-1.5 right-1.5 text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $sCls }}">
                    {{ $sLabel }}
                </span>
            </div>

            {{-- Info --}}
            <div class="p-3">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-0.5">
                    {{ $mon->danhMuc?->ten_danh_muc }}
                </p>
                <p class="text-sm font-semibold text-gray-800 line-clamp-1">{{ $mon->ten_mon }}</p>
                @if($mon->mo_ta)
                <p class="text-xs text-gray-400 mt-0.5 line-clamp-2 leading-relaxed">{{ $mon->mo_ta }}</p>
                @endif
                <p class="text-sm font-bold text-amber-600 mt-1.5">
                    {{ number_format($mon->don_gia, 0, ',', '.') }}đ
                </p>

                {{-- Actions --}}
                <div class="flex gap-1.5 mt-2.5 pt-2.5 border-t border-gray-100">
                    <a href="{{ route('menu.edit', $mon->ma_mon) }}"
                       class="flex-1 text-center text-xs font-medium py-1.5 rounded-lg
                              bg-gray-50 hover:bg-amber-50 text-gray-600 hover:text-amber-700 transition">
                        Sửa
                    </a>
                    <form method="POST" action="{{ route('menu.destroy', $mon->ma_mon) }}"
                          onsubmit="return confirm('Ẩn món {{ addslashes($mon->ten_mon) }}?')"
                          class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full text-xs font-medium py-1.5 rounded-lg
                                       bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 transition">
                            Ẩn
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($mons->isEmpty())
    <div class="py-16 text-center bg-white rounded-xl border border-gray-200">
        <div class="text-3xl mb-2">☕</div>
        <p class="text-sm text-gray-500">Chưa có món nào.</p>
        <a href="{{ route('menu.create') }}" class="mt-2 inline-block text-sm text-amber-600 hover:underline">
            Thêm món đầu tiên →
        </a>
    </div>
    @endif

    <div class="mt-4">{{ $mons->withQueryString()->links() }}</div>
</div>
@endsection
