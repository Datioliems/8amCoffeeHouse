@extends('layouts.app')
@section('page-title', 'Phiếu nhập kho')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('inventory.import.create') }}"
       class="bg-amber-500 text-white text-sm px-4 py-2 rounded-xl">
        + Tạo phiếu nhập
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium">Mã phiếu</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium">Ngày nhập</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium">Nhà cung cấp</th>
                <th class="text-right px-4 py-3 text-xs text-gray-400 font-medium">Tổng giá trị</th>
                <th class="text-center px-4 py-3 text-xs text-gray-400 font-medium">Trạng thái</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($imports as $import)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">{{ $import->ma_pnk }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $import->ngay_nk }}</td>
                <td class="px-4 py-3">{{ $import->nhaCungCap->ten_ncc }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($import->tong_gia_tri, 0, ',', '.') }}đ</td>
                <td class="px-4 py-3 text-center">
                    @if($import->trang_thai === 'cho_duyet')
                        <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">Chờ duyệt</span>
                    @elseif($import->trang_thai === 'da_duyet')
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Đã duyệt</span>
                    @else
                        <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Đã hủy</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('inventory.import.show', $import->ma_pnk) }}"
                       class="text-xs text-gray-400 hover:text-gray-700">Chi tiết</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Chưa có phiếu nhập nào</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $imports->links() }}
@endsection
