@extends('layouts.app')
@section('page-title', 'Tổng quan tồn kho')

@section('content')
{{-- Stats row --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Tổng nguyên liệu</p>
        <p class="text-2xl font-medium">{{ $totalMaterials }}</p>
    </div>
    <div class="bg-red-50 rounded-xl border border-red-100 p-4">
        <p class="text-xs text-red-400 mb-1">Hết hàng</p>
        <p class="text-2xl font-medium text-red-600">{{ $outOfStock }}</p>
    </div>
    <div class="bg-yellow-50 rounded-xl border border-yellow-100 p-4">
        <p class="text-xs text-yellow-500 mb-1">Sắp hết</p>
        <p class="text-2xl font-medium text-yellow-600">{{ $lowStock }}</p>
    </div>
</div>

{{-- Stock level bars --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h2 class="text-sm font-medium mb-4">Mức tồn kho hiện tại</h2>
    <div class="space-y-3">
        @foreach($items as $item)
        <x-stock-level-bar :item="$item" />
        @endforeach
    </div>
</div>
@endsection
