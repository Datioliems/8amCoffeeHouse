@extends('layouts.app')

@section('title', 'Nguyên liệu')
@section('page-title', 'Nguyên liệu')

@section('content')
<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <div></div>
        <a href="{{ route('inventory.materials.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Thêm nguyên liệu
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Mã NL</th>
                    <th class="px-4 py-3 text-left">Tên nguyên liệu</th>
                    <th class="px-4 py-3 text-left">Đơn vị</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($nguyenLieus as $nl)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-500">{{ $nl->ma_nl }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $nl->ten_nl }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $nl->don_vi }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('inventory.materials.edit', $nl->ma_nl) }}"
                           class="text-blue-500 hover:underline mr-3">Sửa</a>
                        <form action="{{ route('inventory.materials.destroy', $nl->ma_nl) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('Xóa nguyên liệu này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:underline">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Chưa có nguyên liệu nào.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $nguyenLieus->links() }}</div>
    </div>
</div>
@endsection
