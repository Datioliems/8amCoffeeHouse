@extends('layouts.app')

@section('title', 'Chi tiết phiếu nhập kho')
@section('page-title', 'Chi tiết phiếu nhập kho')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('inventory.import.index') }}" class="text-gray-400 hover:text-gray-600">Quay lại</a>
        <span class="text-xs px-2 py-1 rounded-full font-medium
            {{ $import->trang_thai === 'cho_duyet' ? 'bg-amber-100 text-amber-700' : '' }}
            {{ $import->trang_thai === 'da_duyet' ? 'bg-green-100 text-green-700' : '' }}
            {{ $import->trang_thai === 'da_huy' ? 'bg-red-100 text-red-700' : '' }}">
            {{ ['cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'da_huy' => 'Đã hủy'][$import->trang_thai] ?? $import->trang_thai }}
        </span>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Mã phiếu</p>
                <p class="font-mono font-medium">{{ $import->ma_pnk }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Nhà cung cấp</p>
                <p class="font-medium">{{ $import->nhaCungCap->ten_ncc ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Ngày nhập</p>
                <p class="font-medium">{{ $import->ngay_nk }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs mb-0.5">Tổng giá trị</p>
                <p class="font-bold text-amber-600">{{ number_format($import->tong_gia_tri ?? 0, 0, ',', '.') }}đ</p>
            </div>
        </div>
        @if($import->ghi_chu)
        <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-500">
            <span class="font-medium text-gray-600">Ghi chú:</span> {{ $import->ghi_chu }}
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-gray-100 font-medium text-gray-700 text-sm">Danh sách nguyên liệu</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-2 text-left">Nguyên liệu</th>
                    <th class="px-5 py-2 text-right">Số lượng</th>
                    <th class="px-5 py-2 text-right">Đơn giá</th>
                    <th class="px-5 py-2 text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($import->chiTietNhapKhos as $ct)
                <tr>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $ct->nguyenLieu->ten_nl ?? $ct->ma_nl }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $ct->so_luong }} {{ $ct->nguyenLieu->don_vi ?? '' }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($ct->don_gia, 0, ',', '.') }}đ</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">{{ number_format($ct->so_luong * $ct->don_gia, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($import->trang_thai === 'cho_duyet')
    <div class="flex gap-3">
        <form action="{{ route('inventory.import.approve', $import->ma_pnk) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Duyệt phiếu
            </button>
        </form>
        <form action="{{ route('inventory.import.cancel', $import->ma_pnk) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" onclick="return confirm('Hủy phiếu nhập này?')"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-5 py-2 rounded-lg transition">
                Hủy phiếu
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
