<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\DanhMuc;
use App\Models\Mon;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function customerMenu(string $maBan)
    {
        $ban = Ban::findOrFail($maBan);

        $danhMucs = DanhMuc::with(['mons' => function ($query) {
            $query->where('trang_thai', 'active')->orderBy('ten_mon');
        }])->orderBy('ten_danh_muc')->get();

        $maOrder = request('ma_order');

        return view('customer.menu', compact('ban', 'danhMucs', 'maOrder'));
    }

    public function index()
    {
        $query = Mon::with('danhMuc');

        if (request('category')) {
            $query->where('ma_danh_muc', request('category'));
        }

        $mons = $query->orderBy('ma_danh_muc')
            ->orderBy('ten_mon')
            ->paginate(20)
            ->withQueryString();

        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();

        return view('staff.menu-list', compact('mons', 'danhMucs'));
    }

    public function create()
    {
        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();

        return view('staff.menu-form', compact('danhMucs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_mon' => 'required|string|max:10|unique:MON,ma_mon',
            'ten_mon' => 'required|string|max:100',
            'don_gia' => 'required|integer|min:1000',
            'mo_ta' => 'nullable|string|max:500',
            'hinh_anh' => 'nullable|string|max:255',
            'ma_danh_muc' => 'required|exists:DANH_MUC,ma_danh_muc',
            'trang_thai' => 'required|in:active,het_hang,an',
        ]);

        Mon::create($validated);

        return redirect()->route('menu.index')
            ->with('success', 'Đã thêm món: '.$validated['ten_mon']);
    }

    public function edit(string $maMon)
    {
        $mon = Mon::findOrFail($maMon);
        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();

        return view('staff.menu-form', compact('mon', 'danhMucs'));
    }

    public function update(Request $request, string $maMon)
    {
        $mon = Mon::findOrFail($maMon);

        $validated = $request->validate([
            'ten_mon' => 'required|string|max:100',
            'don_gia' => 'required|integer|min:1000',
            'mo_ta' => 'nullable|string|max:500',
            'hinh_anh' => 'nullable|string|max:255',
            'ma_danh_muc' => 'required|exists:DANH_MUC,ma_danh_muc',
            'trang_thai' => 'required|in:active,het_hang,an',
        ]);

        $mon->update($validated);

        return redirect()->route('menu.index')
            ->with('success', 'Đã cập nhật món: '.$mon->ten_mon);
    }

    public function destroy(string $maMon)
    {
        $mon = Mon::findOrFail($maMon);
        $mon->update(['trang_thai' => 'an']);

        return redirect()->route('menu.index')
            ->with('success', 'Đã ẩn món: '.$mon->ten_mon);
    }
}
