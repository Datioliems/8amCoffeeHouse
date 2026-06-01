@extends('layouts.app')

@section('title', 'Nhân viên & Phân quyền - 8AM')
@section('page-title', 'Nhân viên & phân quyền')

@php
    $roleBadge = [
        'admin'     => 'bg-[#1A1A1A] text-white',
        'quan_ly'   => 'bg-[#E82C2A] text-white',
        'bartender' => 'bg-amber-100 text-amber-800',
        'nhan_vien' => 'bg-sky-100 text-sky-800',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc space-y-0.5 pl-5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Form tạo tài khoản mới ────────────────────────────── --}}
    <div class="rounded-3xl border border-[#522C25]/10 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-base font-semibold">Tạo tài khoản nhân viên mới</h2>
        <p class="mb-4 text-sm text-[#522C25]/60">
            @if($isAdmin)
                Chủ chuỗi có thể tạo tài khoản cho bất kỳ chi nhánh và vai trò nào.
            @else
                Bạn chỉ có thể tạo tài khoản Pha chế / Phục vụ cho chi nhánh {{ $myBranch }}.
            @endif
        </p>

        <form method="POST" action="{{ route('nhanvien.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Họ tên</label>
                <input name="ten_nv" value="{{ old('ten_nv') }}" required
                       class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm" placeholder="Nguyễn Văn A">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Số điện thoại</label>
                <input name="sdt" value="{{ old('sdt') }}"
                       class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm" placeholder="090...">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Vai trò</label>
                <select name="chuc_vu" required class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm">
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('chuc_vu')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Tên đăng nhập</label>
                <input name="ten_tk" value="{{ old('ten_tk') }}" required autocomplete="off"
                       class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm" placeholder="staff02">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Mật khẩu</label>
                <input name="mat_khau" type="text" required autocomplete="new-password"
                       class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm" placeholder="ít nhất 6 ký tự">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#522C25]/60">Chi nhánh</label>
                @if($isAdmin)
                <select name="ma_chi_nhanh" required class="w-full rounded-xl border border-[#522C25]/15 px-3 py-2 text-sm">
                    @foreach($branches as $b)
                        <option value="{{ $b->ma_chi_nhanh }}" @selected(old('ma_chi_nhanh')===$b->ma_chi_nhanh)>{{ $b->ten_chi_nhanh }}</option>
                    @endforeach
                </select>
                @else
                <input value="{{ $myBranch }}" disabled class="w-full rounded-xl border border-[#522C25]/15 bg-[#F2F2F2] px-3 py-2 text-sm text-[#522C25]/70">
                <input type="hidden" name="ma_chi_nhanh" value="{{ $myBranch }}">
                @endif
            </div>
            <div class="md:col-span-3">
                <button class="rounded-xl bg-[#1A1A1A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-black">
                    + Tạo tài khoản
                </button>
            </div>
        </form>
    </div>

    {{-- ── Danh sách tài khoản ───────────────────────────────── --}}
    <div class="rounded-3xl border border-[#522C25]/10 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#522C25]/10 px-6 py-4">
            <h2 class="text-base font-semibold">Danh sách tài khoản ({{ $accounts->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#F8F6F5] text-left text-xs uppercase tracking-wide text-[#522C25]/60">
                    <tr>
                        <th class="px-4 py-3">Mã / Đăng nhập</th>
                        <th class="px-4 py-3">Họ tên</th>
                        <th class="px-4 py-3">Chi nhánh</th>
                        <th class="px-4 py-3">Vai trò</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Phân quyền / Khoá</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#522C25]/8">
                    @foreach($accounts as $a)
                    @php
                        $isSelf  = $a->ma_tai_khoan === session('tai_khoan_id');
                        // quản lý không được sửa admin/quan_ly
                        $locked  = $isSelf || (! $isAdmin && in_array($a->chuc_vu, ['admin','quan_ly']));
                    @endphp
                    <tr class="align-middle">
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $a->ten_tk }}</p>
                            <p class="text-xs text-[#522C25]/50">{{ $a->ma_tai_khoan }} · {{ $a->ma_nv }}</p>
                        </td>
                        <td class="px-4 py-3">
                            {{ $a->ten_nv }}
                            @if($a->sdt)<p class="text-xs text-[#522C25]/50">{{ $a->sdt }}</p>@endif
                        </td>
                        <td class="px-4 py-3">{{ $a->ma_chi_nhanh }}</td>

                        @if($locked)
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleBadge[$a->chuc_vu] ?? 'bg-gray-100' }}">{{ $roleLabels[$a->chuc_vu] ?? $a->chuc_vu }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $a->trang_thai==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $a->trang_thai==='active' ? 'Đang dùng' : 'Đã khoá' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-[#522C25]/40">
                                {{ $isSelf ? 'Tài khoản của bạn' : '—' }}
                            </td>
                        @else
                            <form method="POST" action="{{ route('nhanvien.update', $a->ma_tai_khoan) }}" class="contents">
                                @csrf
                                @method('PUT')
                                <td class="px-4 py-3">
                                    <select name="chuc_vu" class="rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs">
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" @selected($a->chuc_vu===$key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <select name="trang_thai" class="rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs">
                                        <option value="active"   @selected($a->trang_thai==='active')>Đang dùng</option>
                                        <option value="inactive" @selected($a->trang_thai!=='active')>Khoá</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($isAdmin)
                                        <select name="ma_chi_nhanh" title="Chuyển chi nhánh" class="rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs">
                                            @foreach($branches as $b)
                                                <option value="{{ $b->ma_chi_nhanh }}" @selected($a->ma_chi_nhanh===$b->ma_chi_nhanh)>{{ $b->ma_chi_nhanh }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                        <input name="mat_khau" type="text" placeholder="MK mới (tùy chọn)" autocomplete="new-password"
                                               class="w-32 rounded-lg border border-[#522C25]/15 px-2 py-1.5 text-xs">
                                        <button class="rounded-lg bg-[#1A1A1A] px-3 py-1.5 text-xs font-semibold text-white hover:bg-black">Lưu</button>
                                    </div>
                                </td>
                            </form>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
