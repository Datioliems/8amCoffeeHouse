@extends('layouts.app')

@section('title', isset($mon) ? 'Sửa món' : 'Thêm món')
@section('page-title', isset($mon) ? 'Sửa món' : 'Thêm món')

@section('content')
<div class="max-w-xl">
    <form method="POST"
          action="{{ isset($mon) ? route('menu.update', $mon->ma_mon) : route('menu.store') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        @csrf
        @if(isset($mon))
            @method('PUT')
        @endif

        @if(!isset($mon))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mã món</label>
            <input type="text" name="ma_mon" value="{{ old('ma_mon') }}" placeholder="VD: MON031"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
            @error('ma_mon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên món</label>
            <input type="text" name="ten_mon" value="{{ old('ten_mon', $mon->ten_mon ?? '') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
            @error('ten_mon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                <select name="ma_danh_muc"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
                    @foreach($danhMucs as $danhMuc)
                    <option value="{{ $danhMuc->ma_danh_muc }}" {{ old('ma_danh_muc', $mon->ma_danh_muc ?? '') === $danhMuc->ma_danh_muc ? 'selected' : '' }}>
                        {{ $danhMuc->ten_danh_muc }}
                    </option>
                    @endforeach
                </select>
                @error('ma_danh_muc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Đơn giá</label>
                <input type="number" name="don_gia" min="1000" step="1000"
                       value="{{ old('don_gia', $mon->don_gia ?? '') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
                @error('don_gia') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
            <textarea name="mo_ta" rows="3"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">{{ old('mo_ta', $mon->mo_ta ?? '') }}</textarea>
            @error('mo_ta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Đường dẫn hình ảnh</label>
            <input type="text" name="hinh_anh" value="{{ old('hinh_anh', $mon->hinh_anh ?? '') }}"
                   placeholder="Tùy chọn"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
            @error('hinh_anh') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select name="trang_thai"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
                @foreach(['active' => 'Đang bán', 'het_hang' => 'Hết hàng', 'an' => 'Ẩn'] as $value => $label)
                <option value="{{ $value }}" {{ old('trang_thai', $mon->trang_thai ?? 'active') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            @error('trang_thai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                {{ isset($mon) ? 'Cập nhật' : 'Thêm mới' }}
            </button>
            <a href="{{ route('menu.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-5 py-2">Hủy</a>
        </div>
    </form>
</div>
@endsection
