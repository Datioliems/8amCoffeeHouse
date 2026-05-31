<?php

namespace App\Http\Controllers;

use App\Models\NguyenLieu;
use Illuminate\Http\Request;

class NguyenLieuController extends Controller
{
    public function index()
    {
        $nguyenLieus = NguyenLieu::orderBy('ten_nl')->paginate(20);

        return view('inventory.nguyen-lieu-list', compact('nguyenLieus'));
    }

    public function create()
    {
        return view('inventory.nguyen-lieu-form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_nl' => 'required|string|max:10|unique:NGUYEN_LIEU,ma_nl',
            'ten_nl' => 'required|string|max:100|unique:NGUYEN_LIEU,ten_nl',
            'don_vi' => 'required|in:gram,kg,ml,lit,cai,goi,hop,chai,tui',
        ]);

        NguyenLieu::create($validated);

        return redirect()->route('inventory.materials.index')
            ->with('success', 'Đã thêm nguyên liệu: '.$validated['ten_nl']);
    }

    public function edit(string $material)
    {
        $nguyenLieu = NguyenLieu::findOrFail($material);

        return view('inventory.nguyen-lieu-form', compact('nguyenLieu'));
    }

    public function update(Request $request, string $material)
    {
        $nguyenLieu = NguyenLieu::findOrFail($material);

        $validated = $request->validate([
            'ten_nl' => 'required|string|max:100|unique:NGUYEN_LIEU,ten_nl,'.$material.',ma_nl',
            'don_vi' => 'required|in:gram,kg,ml,lit,cai,goi,hop,chai,tui',
        ]);

        $nguyenLieu->update($validated);

        return redirect()->route('inventory.materials.index')
            ->with('success', 'Đã cập nhật: '.$nguyenLieu->ten_nl);
    }

    public function destroy(string $material)
    {
        NguyenLieu::findOrFail($material)->delete();

        return redirect()->route('inventory.materials.index')
            ->with('success', 'Đã xóa nguyên liệu.');
    }
}
