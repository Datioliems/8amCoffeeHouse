<?php

namespace App\Http\Controllers;

use App\Models\NhaCungCap;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $nhaCungCaps = NhaCungCap::orderBy('ten_ncc')->paginate(15);

        return view('inventory.supplier-list', compact('nhaCungCaps'));
    }

    public function create()
    {
        return view('inventory.supplier-form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_ncc' => 'required|string|max:10|unique:NHA_CUNG_CAP,ma_ncc',
            'ten_ncc' => 'required|string|max:100|unique:NHA_CUNG_CAP,ten_ncc',
            'sdt' => 'nullable|string|max:15',
            'dia_chi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
        ]);

        NhaCungCap::create($validated);

        return redirect()->route('inventory.supplier.index')
            ->with('success', 'Đã thêm nhà cung cấp: '.$validated['ten_ncc']);
    }

    public function edit(string $supplier)
    {
        $nhaCungCap = NhaCungCap::findOrFail($supplier);

        return view('inventory.supplier-form', compact('nhaCungCap'));
    }

    public function update(Request $request, string $supplier)
    {
        $nhaCungCap = NhaCungCap::findOrFail($supplier);

        $validated = $request->validate([
            'ten_ncc' => 'required|string|max:100|unique:NHA_CUNG_CAP,ten_ncc,'.$supplier.',ma_ncc',
            'sdt' => 'nullable|string|max:15',
            'dia_chi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
        ]);

        $nhaCungCap->update($validated);

        return redirect()->route('inventory.supplier.index')
            ->with('success', 'Đã cập nhật: '.$nhaCungCap->ten_ncc);
    }

    public function destroy(string $supplier)
    {
        NhaCungCap::findOrFail($supplier)->delete();

        return redirect()->route('inventory.supplier.index')
            ->with('success', 'Đã xóa nhà cung cấp.');
    }
}
