@extends('layouts.app')

@section('title', isset($nguyenLieu) ? 'Sửa nguyên liệu' : 'Thêm nguyên liệu')
@section('page-title', isset($nguyenLieu) ? 'Sửa nguyên liệu' : 'Thêm nguyên liệu')

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ isset($nguyenLieu)
        ? route('inventory.materials.update', $nguyenLieu->ma_nl)
        : route('inventory.materials.store') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        @csrf
        @if(isset($nguyenLieu))
            @method('PUT')
        @endif

        @if(!isset($nguyenLieu))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mã nguyên liệu</label>
            <input type="text" name="ma_nl" value="{{ old('ma_nl') }}" placeholder="VD: NL001"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
            @error('ma_nl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên nguyên liệu</label>
            <input type="text" name="ten_nl" value="{{ old('ten_nl', $nguyenLieu->ten_nl ?? '') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
            @error('ten_nl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Đơn vị tính</label>
            <select name="don_vi" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
                @foreach(['gram','kg','ml','lit','cai','goi','hop','chai','tui'] as $donVi)
                <option value="{{ $donVi }}" {{ old('don_vi', $nguyenLieu->don_vi ?? '') === $donVi ? 'selected' : '' }}>
                    {{ $donVi }}
                </option>
                @endforeach
            </select>
            @error('don_vi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                {{ isset($nguyenLieu) ? 'Cập nhật' : 'Thêm mới' }}
            </button>
            <a href="{{ route('inventory.materials.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-5 py-2">Hủy</a>
        </div>
    </form>
</div>
@endsection
