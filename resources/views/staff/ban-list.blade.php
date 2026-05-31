@extends('layouts.app')

@section('title', 'Quản lý bàn & QR - 8AM Coffee')
@section('page-title', 'Bàn & QR')

@section('content')
@php
    $stats = [
        'co_khach' => $bans->where('trang_thai', 'co_khach')->count(),
        'trong' => $bans->where('trang_thai', 'trong')->count(),
        'dat_truoc' => $bans->where('trang_thai', 'dat_truoc')->count(),
        'dong' => $bans->where('trang_thai', 'dong')->count(),
    ];
    $totalSeats = $bans->sum(fn($ban) => $ban->so_ghe ?? 4);
    $occupiedSeats = $bans->where('trang_thai', 'co_khach')->sum(fn($ban) => $ban->so_ghe ?? 4);
    $positions = [
        ['top' => '18%', 'left' => '16%'], ['top' => '32%', 'left' => '38%'],
        ['top' => '18%', 'left' => '66%'], ['top' => '52%', 'left' => '20%'],
        ['top' => '56%', 'left' => '55%'], ['top' => '72%', 'left' => '76%'],
        ['top' => '74%', 'left' => '34%'], ['top' => '38%', 'left' => '78%'],
    ];
    $statusMeta = [
        'trong' => ['label' => 'Trống', 'dot' => 'bg-[#CADCAC]', 'pill' => 'bg-green-100 text-green-800'],
        'co_khach' => ['label' => 'Có khách', 'dot' => 'bg-[#E82C2A]', 'pill' => 'bg-[#ffdad4] text-[#93000b]'],
        'dat_truoc' => ['label' => 'Đặt trước', 'dot' => 'bg-[#80534a]', 'pill' => 'bg-[#ffc4b9] text-[#653c34]'],
        'dong' => ['label' => 'Đóng', 'dot' => 'bg-[#916f6b]', 'pill' => 'bg-gray-100 text-gray-500'],
    ];
@endphp

<div class="max-w-7xl space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] text-[#522C25]/55">Table map</p>
            <h2 class="mt-1 text-2xl font-semibold text-[#1A1A1A]">Quản lý sơ đồ bàn và mã QR</h2>
            <p class="mt-1 text-sm text-[#522C25]/65">Cấu hình vị trí, số ghế, trạng thái và QR gọi món cho từng bàn.</p>
        </div>
        <div class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#522C25] ring-1 ring-[#522C25]/10">
            Tổng sức chứa: {{ $totalSeats }} ghế
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_360px]">
        <div class="relative min-h-[500px] overflow-hidden rounded-[25px] border border-[#e6bdb8]/40 bg-[#fcf9f8] p-6 shadow-sm">
            <div class="absolute left-6 top-6 z-10 flex flex-wrap gap-2">
                <span class="rounded-full border border-[#E82C2A]/20 bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#E82C2A] backdrop-blur">
                    Live floor plan
                </span>
                <span class="rounded-full border border-[#522C25]/10 bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#522C25]/60 backdrop-blur">
                    Placeholder isometric
                </span>
            </div>

            <div class="flex h-full min-h-[452px] items-center justify-center rounded-[25px] bg-white p-6 ring-1 ring-[#522C25]/10">
                <div class="relative aspect-square w-full max-w-2xl overflow-hidden rounded-[28px] border border-dashed border-[#916f6b]/35 bg-[#F6F3F2]">
                    <div class="absolute inset-8 rounded-[24px] border border-[#522C25]/10 bg-[#FCFAFA]"></div>
                    <div class="absolute left-[12%] top-[12%] h-[76%] w-[18%] rounded-2xl bg-[#522C25]/10"></div>
                    <div class="absolute right-[10%] top-[16%] h-[18%] w-[28%] rounded-full bg-[#E82C2A]/10"></div>
                    <div class="absolute bottom-[12%] right-[12%] grid grid-cols-2 gap-3">
                        <span class="h-14 w-14 rounded-xl bg-white shadow-sm"></span>
                        <span class="h-14 w-14 rounded-xl bg-white shadow-sm"></span>
                        <span class="h-14 w-14 rounded-xl bg-white shadow-sm"></span>
                        <span class="h-14 w-14 rounded-xl bg-white shadow-sm"></span>
                    </div>
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(82,44,37,0.06)_25%,transparent_25%,transparent_50%,rgba(82,44,37,0.06)_50%,rgba(82,44,37,0.06)_75%,transparent_75%,transparent)] bg-[length:28px_28px] opacity-40"></div>

                    @foreach($bans->take(8) as $index => $ban)
                    @php
                        $pos = $positions[$index % count($positions)];
                        $meta = $statusMeta[$ban->trang_thai] ?? $statusMeta['dong'];
                    @endphp
                    <a href="#table-{{ $ban->ma_ban }}"
                       class="absolute flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-white text-[11px] font-bold text-[#1A1A1A] shadow-lg transition hover:scale-110 {{ $meta['dot'] }}"
                       style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};">
                        B{{ $ban->so_ban }}
                    </a>
                    @endforeach

                    <div class="absolute bottom-5 left-5 right-5 rounded-2xl bg-white/90 p-4 text-sm text-[#522C25]/65 backdrop-blur">
                        Khu vực này là placeholder cho bản đồ isometric. Khi có asset thật, có thể thay trực tiếp phần nền và giữ hotspot bàn hiện tại.
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[25px] bg-[#F2F2F2] p-6">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-[0.16em] text-[#522C25]/65">Capacity overview</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-white p-4">
                        <span class="text-sm text-[#522C25]/70">Có khách</span>
                        <span class="font-mono text-2xl font-bold text-[#E82C2A]">{{ $stats['co_khach'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-white p-4">
                        <span class="text-sm text-[#522C25]/70">Trống</span>
                        <span class="font-mono text-2xl font-bold text-[#52613B]">{{ $stats['trong'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-white p-4">
                        <span class="text-sm text-[#522C25]/70">Đặt trước</span>
                        <span class="font-mono text-2xl font-bold text-[#80534a]">{{ $stats['dat_truoc'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-white p-4">
                        <span class="text-sm text-[#522C25]/70">Ghế đang dùng</span>
                        <span class="font-mono text-2xl font-bold text-[#1A1A1A]">{{ $occupiedSeats }}/{{ $totalSeats }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[25px] bg-[#1A1A1A] p-6 text-white">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#FFB4AB]">Vận hành</p>
                <p class="mt-3 text-sm leading-6 text-white/70">Bàn có order đang xử lý sẽ hiển thị số lượng order trong bảng bên dưới. QR mở trực tiếp link gọi món theo từng bàn.</p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[25px] border border-[#e6bdb8]/35 bg-white">
        <div class="flex flex-col gap-3 border-b border-[#522C25]/10 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-[#522C25]/55">Table directory</p>
                <h3 class="mt-1 text-lg font-semibold">Danh sách bàn</h3>
            </div>
            <div class="rounded-full bg-[#F2F2F2] px-4 py-2 text-sm font-semibold text-[#522C25]">
                {{ $bans->count() }} bàn
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-[#F2F2F2]/70 text-xs uppercase tracking-[0.08em] text-[#522C25]/60">
                    <tr>
                        <th class="px-6 py-4">Bàn</th>
                        <th class="px-6 py-4">Vị trí</th>
                        <th class="px-6 py-4">Số ghế</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4">Order</th>
                        <th class="px-6 py-4">QR</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#522C25]/10">
                    @foreach($bans as $ban)
                    @php $meta = $statusMeta[$ban->trang_thai] ?? $statusMeta['dong']; @endphp
                    <tr id="table-{{ $ban->ma_ban }}" class="transition hover:bg-[#F6F3F2]">
                        <form method="POST" action="{{ route('ban.update', $ban->ma_ban) }}">
                            @csrf
                            @method('PUT')
                            <td class="px-6 py-4 font-mono text-base font-bold text-[#1A1A1A]">B-{{ str_pad($ban->so_ban, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4">
                                <input type="text" name="vi_tri" value="{{ old('vi_tri', $ban->vi_tri) }}"
                                       class="w-40 rounded-xl border-0 bg-[#F2F2F2] px-3 py-2 text-sm focus:ring-2 focus:ring-[#E82C2A]/20">
                            </td>
                            <td class="px-6 py-4">
                                <input type="number" name="so_ghe" min="1" max="20" value="{{ old('so_ghe', $ban->so_ghe ?? 4) }}"
                                       class="w-20 rounded-xl border-0 bg-[#F2F2F2] px-3 py-2 text-sm focus:ring-2 focus:ring-[#E82C2A]/20">
                            </td>
                            <td class="px-6 py-4">
                                <select name="trang_thai" class="w-36 rounded-xl border-0 bg-[#F2F2F2] px-3 py-2 text-sm focus:ring-2 focus:ring-[#E82C2A]/20">
                                    @foreach(['trong' => 'Trống', 'co_khach' => 'Có khách', 'dat_truoc' => 'Đặt trước', 'dong' => 'Đóng'] as $value => $label)
                                        <option value="{{ $value }}" {{ $ban->trang_thai === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="ml-2 rounded-full px-2.5 py-1 text-xs font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-[#522C25]/65">{{ $orderCounts[$ban->ma_ban] ?? 0 }} đang xử lý</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('ban.qr', $ban->ma_ban) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#1A1A1A] p-1">
                                    <img src="{{ route('ban.qr', $ban->ma_ban) }}" alt="QR bàn {{ $ban->so_ban }}" class="h-8 w-8 rounded-sm bg-white">
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="rounded-full bg-[#E82C2A] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#BB0011]">
                                    Lưu
                                </button>
                            </td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
