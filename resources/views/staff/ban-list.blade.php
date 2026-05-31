@extends('layouts.app')

@section('title', 'Quản lý bàn - 8AM Coffee')
@section('page-title', 'Sơ đồ bàn')

@section('content')
<div class="max-w-6xl">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($bans as $ban)
        @php
            $statusLabel = [
                'trong' => ['Trống', 'text-green-600'],
                'co_khach' => ['Có khách', 'text-amber-600'],
                'dat_truoc' => ['Đặt trước', 'text-blue-600'],
                'dong' => ['Đóng', 'text-gray-400'],
            ][$ban->trang_thai] ?? ['?', 'text-gray-500'];
        @endphp
        <div class="rounded-xl border-2 p-4 text-center
            {{ $ban->trang_thai === 'trong' ? 'border-green-300 bg-green-50' : '' }}
            {{ $ban->trang_thai === 'co_khach' ? 'border-amber-400 bg-amber-50' : '' }}
            {{ $ban->trang_thai === 'dat_truoc' ? 'border-blue-300 bg-blue-50' : '' }}
            {{ $ban->trang_thai === 'dong' ? 'border-gray-300 bg-gray-50' : '' }}">
            <div class="text-3xl font-bold text-gray-700 mb-1">{{ $ban->so_ban }}</div>
            <div class="text-xs text-gray-500 mb-2">{{ $ban->vi_tri ?? 'Tầng 1' }}</div>
            <span class="text-xs font-medium {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>

            @if(($orderCounts[$ban->ma_ban] ?? 0) > 0)
            <div class="mt-1 text-xs text-amber-600">{{ $orderCounts[$ban->ma_ban] }} order</div>
            @endif

            <div class="mt-3">
                <a href="{{ route('ban.qr', $ban->ma_ban) }}"
                   target="_blank"
                   class="inline-block text-xs bg-gray-800 text-white px-3 py-1 rounded-lg hover:bg-gray-700 transition">
                    QR Code
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
