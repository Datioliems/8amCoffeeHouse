<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\DanhMuc;
use App\Models\DinhMuc;
use App\Models\Mon;
use App\Models\NguyenLieu;
use App\Services\MenuAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function __construct(private MenuAvailabilityService $availabilityService) {}

    public function customerMenu(string $maBan)
    {
        $ban = Ban::findOrFail($maBan);
        $maChiNhanh = $ban->ma_chi_nhanh;

        $danhMucs = DanhMuc::with(['mons' => function ($query) {
            $query->with([
                'danhMuc',
                'dinhMucs.nguyenLieu.tonKhos',
            ])->where('trang_thai', 'active')->orderBy('ten_mon');
        }])->orderBy('ten_danh_muc')->get();

        $danhMucs->each(fn($danhMuc) => $this->availabilityService->annotate($danhMuc->mons, $maChiNhanh));

        $maOrder = request('ma_order');

        return view('customer.menu', compact('ban', 'danhMucs', 'maOrder'));
    }

    public function index()
    {
        $query = Mon::with(['danhMuc', 'dinhMucs.nguyenLieu.tonKhos']);

        if (request('category')) {
            $query->where('ma_danh_muc', request('category'));
        }

        $mons = $query->orderBy('ma_danh_muc')
            ->orderBy('ten_mon')
            ->paginate(20)
            ->withQueryString();

        $this->availabilityService->annotate($mons->getCollection(), session('ma_chi_nhanh'));

        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();

        return view('staff.menu-list', compact('mons', 'danhMucs'));
    }

    public function create()
    {
        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();
        $nguyenLieus = $this->getNguyenLieusWithStock();
        $dinhMucRows = old('dinh_muc', [['ma_nl' => '', 'so_luong_dung' => '', 'mo_ta' => '']]);

        return view('staff.menu-form', compact('danhMucs', 'nguyenLieus', 'dinhMucRows'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_mon' => 'required|string|max:10|unique:MON,ma_mon',
            'ten_mon' => 'required|string|max:100',
            'don_gia' => 'required|integer|min:1000',
            'mo_ta' => 'nullable|string|max:500',
            'hinh_anh' => 'nullable|string|max:255',
            'hinh_anh_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'ma_danh_muc' => 'required|exists:DANH_MUC,ma_danh_muc',
            'trang_thai' => 'required|in:active,het_hang,an',
            'dinh_muc' => 'nullable|array|max:50',
            'dinh_muc.*.ma_nl' => 'nullable|exists:NGUYEN_LIEU,ma_nl',
            'dinh_muc.*.so_luong_dung' => 'nullable|numeric|min:0.01|max:999999',
            'dinh_muc.*.mo_ta' => 'nullable|string|max:200',
        ]);

        unset($validated['hinh_anh_file']);
        $dinhMucs = $validated['dinh_muc'] ?? [];
        unset($validated['dinh_muc']);
        if ($request->hasFile('hinh_anh_file')) {
            $validated['hinh_anh'] = $this->storeMenuImage($request, $validated['ma_mon']);
        }

        DB::transaction(function () use ($validated, $dinhMucs) {
            Mon::create($validated);
            $this->syncDinhMucs($validated['ma_mon'], $dinhMucs);
        });

        return redirect()->route('menu.index')
            ->with('success', 'Đã thêm món: '.$validated['ten_mon']);
    }

    public function edit(string $maMon)
    {
        $mon = Mon::with('dinhMucs.nguyenLieu')->findOrFail($maMon);
        $danhMucs = DanhMuc::orderBy('ten_danh_muc')->get();
        $nguyenLieus = $this->getNguyenLieusWithStock();
        $dinhMucRows = old('dinh_muc', $mon->dinhMucs->map(fn($dinhMuc) => [
            'ma_nl' => $dinhMuc->ma_nl,
            'so_luong_dung' => $dinhMuc->so_luong_dung,
            'mo_ta' => $dinhMuc->mo_ta,
        ])->values()->all());

        if (empty($dinhMucRows)) {
            $dinhMucRows = [['ma_nl' => '', 'so_luong_dung' => '', 'mo_ta' => '']];
        }

        return view('staff.menu-form', compact('mon', 'danhMucs', 'nguyenLieus', 'dinhMucRows'));
    }

    public function update(Request $request, string $maMon)
    {
        $mon = Mon::findOrFail($maMon);

        $validated = $request->validate([
            'ten_mon' => 'required|string|max:100',
            'don_gia' => 'required|integer|min:1000',
            'mo_ta' => 'nullable|string|max:500',
            'hinh_anh' => 'nullable|string|max:255',
            'hinh_anh_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'ma_danh_muc' => 'required|exists:DANH_MUC,ma_danh_muc',
            'trang_thai' => 'required|in:active,het_hang,an',
            'dinh_muc' => 'nullable|array|max:50',
            'dinh_muc.*.ma_nl' => 'nullable|exists:NGUYEN_LIEU,ma_nl',
            'dinh_muc.*.so_luong_dung' => 'nullable|numeric|min:0.01|max:999999',
            'dinh_muc.*.mo_ta' => 'nullable|string|max:200',
        ]);

        unset($validated['hinh_anh_file']);
        $dinhMucs = $validated['dinh_muc'] ?? [];
        unset($validated['dinh_muc']);
        if ($request->hasFile('hinh_anh_file')) {
            $validated['hinh_anh'] = $this->storeMenuImage($request, $mon->ma_mon);
        }

        DB::transaction(function () use ($mon, $validated, $dinhMucs) {
            $mon->update($validated);
            $this->syncDinhMucs($mon->ma_mon, $dinhMucs);
        });

        return redirect()->route('menu.index')
            ->with('success', 'Đã cập nhật món: '.$mon->ten_mon);
    }

    private function storeMenuImage(Request $request, string $maMon): string
    {
        $file = $request->file('hinh_anh_file');
        $extension = $file->getClientOriginalExtension();
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: Str::lower($maMon);
        $fileName = $maMon . '-' . $baseName . '-' . now()->format('YmdHis') . '.' . $extension;

        File::ensureDirectoryExists(public_path('images/menu'));
        $file->move(public_path('images/menu'), $fileName);

        return 'menu/' . $fileName;
    }

    private function getNguyenLieusWithStock()
    {
        $maChiNhanh = session('ma_chi_nhanh');

        return NguyenLieu::with(['tonKhos' => function ($query) use ($maChiNhanh) {
            if ($maChiNhanh) {
                $query->where('ma_chi_nhanh', $maChiNhanh);
            }
        }])->orderBy('ten_nl')->get();
    }

    private function syncDinhMucs(string $maMon, array $rows): void
    {
        $cleanRows = collect($rows)
            ->filter(fn($row) => !empty($row['ma_nl']) && isset($row['so_luong_dung']) && (float) $row['so_luong_dung'] > 0)
            ->groupBy('ma_nl')
            ->map(function ($items, $maNl) {
                $first = $items->first();
                return [
                    'ma_nl' => $maNl,
                    'so_luong_dung' => $items->sum(fn($item) => (float) $item['so_luong_dung']),
                    'mo_ta' => $first['mo_ta'] ?? null,
                ];
            })
            ->values();

        DinhMuc::where('ma_mon', $maMon)->delete();

        foreach ($cleanRows as $row) {
            DinhMuc::create([
                'ma_mon' => $maMon,
                'ma_nl' => $row['ma_nl'],
                'so_luong_dung' => $row['so_luong_dung'],
                'mo_ta' => $row['mo_ta'],
            ]);
        }
    }

    public function destroy(string $maMon)
    {
        $mon = Mon::findOrFail($maMon);
        $mon->update(['trang_thai' => 'an']);

        return redirect()->route('menu.index')
            ->with('success', 'Đã ẩn món: '.$mon->ten_mon);
    }
}
