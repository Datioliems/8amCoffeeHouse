@props(['mon', 'order'])
@php
$imgMap = [
    'MON001'=>'espresso.jpg',    'MON002'=>'americano.jpg',  'MON003'=>'latte.jpg',
    'MON004'=>'cam_em.jpg',      'MON005'=>'salted_caramel.jpg', 'MON006'=>'ca_phe_muoi.jpg',
    'MON007'=>'ca_phe_trung.png','MON008'=>'lady_sweet.jpg', 'MON009'=>'ginger_latte.jpg',
    'MON010'=>'v60.jpg',         'MON011'=>'origami.jpg',    'MON012'=>'cold_brew.jpg',
    'MON013'=>'cold_brew_mo.png','MON014'=>'cold_brew.jpg',  'MON015'=>'tonic.jpg',
    'MON016'=>'nhiet_doi.jpg',   'MON017'=>'ca_phe_den.webp','MON018'=>'ca_phe_nau.jpg',
    'MON019'=>'bac_xiu.jpg',     'MON020'=>'sua_chua_ca_phe.jpg','MON021'=>'ca_cao.jpg',
    'MON022'=>'chanh_xi_muoi.jpg','MON023'=>'chanh_leo.png', 'MON024'=>'tra_oi_hong.jpg',
    'MON025'=>'tra_chanh_dao.jpg','MON026'=>'banh_sung_bo.jpg','MON027'=>'banh_sung_bo_socola.jpg',
    'MON028'=>'hat_sen_say.jpg',
];
// Ưu tiên hinh_anh từ DB, fallback về imgMap
$imgFile = $mon->hinh_anh ?? ($imgMap[$mon->ma_mon] ?? null);
$imgUrl  = $imgFile ? asset('images/' . $imgFile) : null;
$hetHang = $mon->trang_thai === 'het_hang';
@endphp

<article class="group overflow-hidden rounded-2xl bg-white ring-1 ring-[#522C25]/10 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#522C25]/10 {{ $hetHang ? 'opacity-60' : '' }}">

    <div class="aspect-[4/3] overflow-hidden bg-[#F2F2F2]">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $mon->ten_mon }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                 onerror="this.parentNode.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-stone-300\'><svg class=\'w-6 h-6\' fill=\'currentColor\' viewBox=\'0 0 20 20\'><path fill-rule=\'evenodd\' d=\'M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z\' clip-rule=\'evenodd\'/></svg></div>'">
        @else
            <div class="flex h-full w-full items-center justify-center text-stone-300">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
            </div>
        @endif
    </div>

    <div class="flex min-h-36 flex-col p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="am-headline text-base font-semibold leading-tight text-[#1A1A1A]">
                    {{ $mon->ten_mon }}
                </p>
                @if($hetHang)
                    <span class="mt-1 inline-flex rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-medium text-[#BB0011]">Hết hàng</span>
                @endif
            </div>
            <p class="am-mono shrink-0 text-sm font-bold text-[#E82C2A]">
                {{ number_format($mon->don_gia, 0, ',', '.') }}đ
            </p>
        </div>
        @if($mon->mo_ta)
            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-[#522C25]/65">{{ $mon->mo_ta }}</p>
        @endif

        <div class="mt-auto pt-4">
        @if(!$hetHang)
            <button @click="addToCart(@js([
                        'ma_mon' => $mon->ma_mon,
                        'ten_mon' => $mon->ten_mon,
                        'don_gia' => $mon->don_gia,
                        'mo_ta' => $mon->mo_ta,
                        'category' => $mon->danhMuc?->ten_danh_muc,
                        'image_url' => $imgUrl,
                    ]))"
                    class="am-headline flex w-full items-center justify-between rounded-full bg-[#E82C2A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#BB0011] active:scale-[0.98]">
                <span>Thêm món</span>
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15">+</span>
            </button>
        @else
            <button disabled class="flex w-full cursor-not-allowed items-center justify-center rounded-full bg-[#F2F2F2] px-4 py-2.5 text-sm font-semibold text-[#522C25]/40">
                Tạm hết
            </button>
        @endif
        </div>
    </div>
</article>
