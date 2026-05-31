@extends('layouts.app')

@section('title', 'Phiếu kiểm kê')
@section('page-title', 'Phiếu kiểm kê tồn kho')

@section('content')
<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <div></div>
        <a href="{{ route('inventory.stockcheck.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Tạo phiếu kiểm kê
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Mã phiếu</th>
                    <th class="px-4 py-3 text-left">Ngày kiểm kê</th>
                    <th class="px-4 py-3 text-left">Nhân viên</th>
                    <th class="px-4 py-3 text-left">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($checks as $check)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-500">{{ $check->ma_pkk }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $check->ngay_kk }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $check->nhanVien->ten_nv ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $check->trang_thai === 'nhap' ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700' }}">
                            {{ $check->trang_thai === 'nhap' ? 'Nháp' : 'Đã xác nhận' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($check->trang_thai === 'nhap')
                        <form action="{{ route('inventory.stockcheck.confirm', $check->ma_pkk) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                    onclick="return confirm('Xác nhận kiểm kê? Tồn kho thực tế sẽ được cập nhật.')"
                                    class="text-green-500 hover:underline text-xs">
                                Xác nhận
                            </button>
                        </form>
                        @else
                        <span class="text-gray-300 text-xs">Không có</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Chưa có phiếu kiểm kê nào.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $checks->links() }}</div>
    </div>
</div>
@endsection
