@extends('layouts.app')
@section('page-title', 'Tổng quan tồn kho')

@section('content')
@php
    $healthyStock = max(0, $totalMaterials - $outOfStock - $lowStock);
    $stockRate = $totalMaterials > 0 ? round(($healthyStock / $totalMaterials) * 100) : 0;
@endphp

<div class="max-w-7xl space-y-6">
    <section class="rounded-[2rem] bg-[#1A1A1A] p-5 text-white shadow-[0_24px_70px_rgba(26,26,26,0.18)] sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/55">Inventory control</p>
                <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">Tồn kho nguyên liệu</h2>
                <p class="mt-2 max-w-2xl text-sm text-white/65">
                    Theo dõi mức tồn hiện tại, tạo phiếu nhập và kiểm kê nguyên liệu từ cùng một màn hình.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.materials.create') }}"
                   class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[#1A1A1A] transition hover:bg-[#FFF7E8]">
                    Thêm nguyên liệu mới
                </a>
                <a href="{{ route('inventory.import.create') }}"
                   class="rounded-xl bg-[#8B5A2B] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6F4621]">
                    Tạo phiếu nhập
                </a>
                <a href="{{ route('inventory.stockcheck.create') }}"
                   class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                    Tạo phiếu kiểm kê
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10 transition hover:-translate-y-0.5 hover:shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#522C25]/45">Nguyên liệu</p>
            <p class="mt-3 text-3xl font-bold text-[#1A1A1A]">{{ $totalMaterials }}</p>
            <p class="mt-1 text-sm text-[#522C25]/60">Đang liên kết dữ liệu kho</p>
        </div>
        <div class="rounded-2xl bg-white p-5 ring-1 ring-[#522C25]/10 transition hover:-translate-y-0.5 hover:shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#522C25]/45">Ổn định</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">{{ $healthyStock }}</p>
            <p class="mt-1 text-sm text-[#522C25]/60">{{ $stockRate }}% nguyên liệu đủ dùng</p>
        </div>
        <a href="{{ route('inventory.alert') }}" class="rounded-2xl bg-[#FFF7E8] p-5 ring-1 ring-[#E8C37D]/50 transition hover:-translate-y-0.5 hover:shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#8B5A2B]/70">Sắp hết</p>
            <p class="mt-3 text-3xl font-bold text-[#8B5A2B]">{{ $lowStock }}</p>
            <p class="mt-1 text-sm text-[#8B5A2B]/75">Cần lên kế hoạch nhập</p>
        </a>
        <div class="rounded-2xl bg-red-50 p-5 ring-1 ring-red-100 transition hover:-translate-y-0.5 hover:shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-red-500/70">Hết hàng</p>
            <p class="mt-3 text-3xl font-bold text-red-600">{{ $outOfStock }}</p>
            <p class="mt-1 text-sm text-red-500/75">Cần xử lý ngay</p>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="overflow-hidden rounded-[2rem] bg-white ring-1 ring-[#522C25]/10">
            <div class="flex items-center justify-between border-b border-[#522C25]/10 p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Import history</p>
                    <h3 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Lịch sử nhập kho</h3>
                </div>
                <a href="{{ route('inventory.import.index') }}" class="text-sm font-semibold text-[#8B5A2B] hover:text-[#6F4621]">Xem tất cả</a>
            </div>
            <div class="divide-y divide-[#522C25]/10">
                @forelse($recentImports as $import)
                    @php
                        $statusClass = match($import->trang_thai) {
                            'cho_duyet' => 'bg-amber-100 text-amber-700',
                            'da_duyet' => 'bg-green-100 text-green-700',
                            'da_huy' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $statusLabel = ['cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'da_huy' => 'Đã hủy'][$import->trang_thai] ?? $import->trang_thai;
                    @endphp
                    <a href="{{ route('inventory.import.show', $import->ma_pnk) }}" class="grid gap-3 p-4 transition hover:bg-[#FAF7F2] sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-mono text-sm font-semibold text-[#1A1A1A]">{{ $import->ma_pnk }}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-[#522C25]/60">
                                {{ $import->nhaCungCap->ten_ncc ?? 'Nhà cung cấp' }} · {{ $import->chiTietNhapKhos->count() }} nguyên liệu · {{ $import->ngay_nk }}
                            </p>
                        </div>
                        <p class="text-left text-sm font-bold text-[#8B5A2B] sm:text-right">{{ number_format($import->tong_gia_tri ?? 0, 0, ',', '.') }}đ</p>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-[#522C25]/55">Chưa có phiếu nhập kho.</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] bg-white ring-1 ring-[#522C25]/10">
            <div class="flex items-center justify-between border-b border-[#522C25]/10 p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Stock check</p>
                    <h3 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Lịch sử kiểm kê</h3>
                </div>
                <a href="{{ route('inventory.stockcheck.index') }}" class="text-sm font-semibold text-[#8B5A2B] hover:text-[#6F4621]">Xem tất cả</a>
            </div>
            <div class="divide-y divide-[#522C25]/10">
                @forelse($recentChecks as $check)
                    @php
                        $statusClass = match($check->trang_thai) {
                            'nhap' => 'bg-gray-100 text-gray-600',
                            'da_xac_nhan' => 'bg-green-100 text-green-700',
                            'da_huy' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $statusLabel = ['nhap' => 'Nháp', 'da_xac_nhan' => 'Đã xác nhận', 'da_huy' => 'Đã hủy'][$check->trang_thai] ?? $check->trang_thai;
                    @endphp
                    <a href="{{ route('inventory.stockcheck.show', $check->ma_pkk) }}" class="grid gap-3 p-4 transition hover:bg-[#FAF7F2] sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-mono text-sm font-semibold text-[#1A1A1A]">{{ $check->ma_pkk }}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-[#522C25]/60">
                                {{ $check->nhanVien->ten_nv ?? 'Nhân viên' }} · {{ $check->chiTietKiemKes->count() }} nguyên liệu · {{ $check->ngay_kk }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-[#8B5A2B]">Chi tiết</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-[#522C25]/55">Chưa có phiếu kiểm kê.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="overflow-hidden rounded-[2rem] bg-white ring-1 ring-[#522C25]/10">
            <div class="flex flex-col gap-2 border-b border-[#522C25]/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Stock levels</p>
                    <h3 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Mức tồn hiện tại</h3>
                </div>
                <a href="{{ route('inventory.materials.index') }}" class="text-sm font-semibold text-[#8B5A2B] hover:text-[#6F4621]">
                    Xem dữ liệu nguyên liệu
                </a>
            </div>

            <div class="divide-y divide-[#522C25]/10">
                @forelse($items as $item)
                    @php
                        $ton = (float) ($item['ton'] ?? 0);
                        $nguong = (float) ($item['nguong'] ?? 0);
                        $percent = $nguong > 0 ? min(100, round(($ton / $nguong) * 100)) : ($ton > 0 ? 100 : 0);
                        $status = $ton <= 0 ? 'Hết hàng' : ($nguong > 0 && $ton <= $nguong ? 'Sắp hết' : 'Đủ hàng');
                        $statusClass = $status === 'Hết hàng'
                            ? 'bg-red-50 text-red-600'
                            : ($status === 'Sắp hết' ? 'bg-[#FFF7E8] text-[#8B5A2B]' : 'bg-emerald-50 text-emerald-600');
                        $barClass = $status === 'Hết hàng'
                            ? 'bg-red-500'
                            : ($status === 'Sắp hết' ? 'bg-[#D7952A]' : 'bg-emerald-500');
                    @endphp
                    <div class="grid gap-3 p-4 transition hover:bg-[#FAF7F2] md:grid-cols-[1fr_160px] md:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold text-[#1A1A1A]">{{ $item['ten'] }}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#EFE7DD]">
                                <div class="h-full rounded-full {{ $barClass }} transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-[#522C25]/55">Ngưỡng cảnh báo: {{ rtrim(rtrim(number_format($nguong, 2, ',', '.'), '0'), ',') }} {{ $item['don_vi'] }}</p>
                        </div>
                        <div class="rounded-xl bg-[#FAF7F2] px-4 py-3 text-left md:text-right">
                            <p class="text-xs font-medium text-[#522C25]/55">Tồn hiện tại</p>
                            <p class="mt-1 text-xl font-bold text-[#522C25]">{{ rtrim(rtrim(number_format($ton, 2, ',', '.'), '0'), ',') }} {{ $item['don_vi'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-sm text-[#522C25]/55">
                        Chưa có dữ liệu nguyên liệu. Hãy thêm nguyên liệu mới để bắt đầu quản lý tồn kho.
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-4">
            <div class="rounded-[2rem] bg-white p-5 ring-1 ring-[#522C25]/10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Quick actions</p>
                <h3 class="mt-1 text-lg font-semibold text-[#1A1A1A]">Thao tác kho</h3>
                <div class="mt-4 grid gap-3">
                    <a href="{{ route('inventory.import.create') }}" class="rounded-2xl border border-[#522C25]/10 bg-[#FAF7F2] p-4 transition hover:border-[#8B5A2B]/35 hover:bg-[#FFF7E8]">
                        <p class="font-semibold text-[#1A1A1A]">Tạo phiếu nhập nguyên liệu</p>
                        <p class="mt-1 text-sm text-[#522C25]/60">Mở form nhập kho và cập nhật tồn sau khi duyệt.</p>
                    </a>
                    <a href="{{ route('inventory.stockcheck.create') }}" class="rounded-2xl border border-[#522C25]/10 bg-[#FAF7F2] p-4 transition hover:border-[#8B5A2B]/35 hover:bg-[#FFF7E8]">
                        <p class="font-semibold text-[#1A1A1A]">Tạo phiếu kiểm kê</p>
                        <p class="mt-1 text-sm text-[#522C25]/60">Ghi nhận chênh lệch thực tế và đồng bộ lại dữ liệu kho.</p>
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] bg-[#FAF7F2] p-5 ring-1 ring-[#522C25]/10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#8B5A2B]/70">Dữ liệu</p>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('inventory.materials.index') }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#522C25] transition hover:text-[#8B5A2B]">
                        Danh mục nguyên liệu
                        <span>→</span>
                    </a>
                    <a href="{{ route('inventory.import.index') }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#522C25] transition hover:text-[#8B5A2B]">
                        Danh sách phiếu nhập
                        <span>→</span>
                    </a>
                    <a href="{{ route('inventory.stockcheck.index') }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#522C25] transition hover:text-[#8B5A2B]">
                        Danh sách kiểm kê
                        <span>→</span>
                    </a>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
