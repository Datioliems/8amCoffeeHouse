<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class NhanVienController extends Controller
{
    /** Tất cả vai trò trong hệ thống. */
    private const ROLES = [
        'admin'     => 'Chủ chuỗi (toàn quyền)',
        'quan_ly'   => 'Quản lý chi nhánh',
        'bartender' => 'Pha chế',
        'nhan_vien' => 'Nhân viên phục vụ',
    ];

    private function isAdmin(): bool
    {
        return session('chuc_vu') === 'admin';
    }

    /** Vai trò mà người dùng hiện tại được phép gán cho người khác. */
    private function allowedRoles(): array
    {
        if ($this->isAdmin()) {
            return self::ROLES;                       // chủ chuỗi gán mọi vai trò
        }
        // quản lý chỉ tạo/sửa cấp dưới trong chi nhánh của mình
        return [
            'bartender' => self::ROLES['bartender'],
            'nhan_vien' => self::ROLES['nhan_vien'],
        ];
    }

    public function index()
    {
        $q = DB::table('TAI_KHOAN')
            ->join('NHAN_VIEN', 'TAI_KHOAN.ma_nv', '=', 'NHAN_VIEN.ma_nv')
            ->select(
                'TAI_KHOAN.ma_tai_khoan', 'TAI_KHOAN.ten_tk', 'TAI_KHOAN.chuc_vu', 'TAI_KHOAN.trang_thai',
                'NHAN_VIEN.ma_nv', 'NHAN_VIEN.ten_nv', 'NHAN_VIEN.sdt', 'NHAN_VIEN.ma_chi_nhanh'
            )
            ->orderBy('NHAN_VIEN.ma_chi_nhanh')
            ->orderBy('TAI_KHOAN.ma_tai_khoan');

        if (! $this->isAdmin()) {
            $q->where('NHAN_VIEN.ma_chi_nhanh', session('ma_chi_nhanh'));
        }

        return view('staff.nhanvien-list', [
            'accounts'   => $q->get(),
            'branches'   => DB::table('CHI_NHANH')->orderBy('ma_chi_nhanh')->get(),
            'roles'      => $this->allowedRoles(),
            'roleLabels' => self::ROLES,
            'isAdmin'    => $this->isAdmin(),
            'myBranch'   => session('ma_chi_nhanh'),
        ]);
    }

    public function store(Request $request)
    {
        $allowed = array_keys($this->allowedRoles());

        $data = $request->validate([
            'ten_nv'       => 'required|string|max:100',
            'sdt'          => 'nullable|string|max:15',
            'ten_tk'       => 'required|string|max:50|unique:TAI_KHOAN,ten_tk',
            'mat_khau'     => 'required|string|min:6|max:100',
            'chuc_vu'      => ['required', Rule::in($allowed)],
            'ma_chi_nhanh' => 'required|exists:CHI_NHANH,ma_chi_nhanh',
        ], [], [
            'ten_nv'   => 'họ tên',
            'ten_tk'   => 'tên đăng nhập',
            'mat_khau' => 'mật khẩu',
            'chuc_vu'  => 'vai trò',
        ]);

        // Quản lý bị khoá cứng vào chi nhánh của mình
        $branch = $this->isAdmin() ? $data['ma_chi_nhanh'] : session('ma_chi_nhanh');

        $maNv = $this->nextId('NHAN_VIEN', 'ma_nv', 'NV');
        $maTk = $this->nextId('TAI_KHOAN', 'ma_tai_khoan', 'TK');

        DB::transaction(function () use ($data, $branch, $maNv, $maTk) {
            DB::table('NHAN_VIEN')->insert([
                'ma_nv'        => $maNv,
                'ten_nv'       => $data['ten_nv'],
                'sdt'          => $data['sdt'] ?? null,
                'ma_chi_nhanh' => $branch,
            ]);
            DB::table('TAI_KHOAN')->insert([
                'ma_tai_khoan' => $maTk,
                'ten_tk'       => $data['ten_tk'],
                'mat_khau'     => Hash::make($data['mat_khau']),
                'chuc_vu'      => $data['chuc_vu'],
                'trang_thai'   => 'active',
                'ma_nv'        => $maNv,
            ]);
        });

        return back()->with('success', "Đã tạo tài khoản “{$data['ten_tk']}” (mã {$maTk}).");
    }

    public function update(Request $request, string $maTaiKhoan)
    {
        $acc = DB::table('TAI_KHOAN')
            ->join('NHAN_VIEN', 'TAI_KHOAN.ma_nv', '=', 'NHAN_VIEN.ma_nv')
            ->where('ma_tai_khoan', $maTaiKhoan)
            ->select('TAI_KHOAN.*', 'NHAN_VIEN.ma_chi_nhanh', 'NHAN_VIEN.ma_nv as nv')
            ->first();
        abort_unless($acc, 404);

        // Không cho tự khoá / tự hạ quyền chính mình
        abort_if($maTaiKhoan === session('tai_khoan_id'), 403, 'Không thể tự chỉnh sửa tài khoản đang đăng nhập.');

        // Quản lý: chỉ sửa cấp dưới trong chi nhánh mình
        if (! $this->isAdmin()) {
            abort_if($acc->ma_chi_nhanh !== session('ma_chi_nhanh'), 403);
            abort_if(in_array($acc->chuc_vu, ['admin', 'quan_ly']), 403, 'Bạn không thể chỉnh sửa quản lý hoặc chủ chuỗi.');
        }

        $allowed = array_keys($this->allowedRoles());

        $data = $request->validate([
            'chuc_vu'      => ['required', Rule::in($allowed)],
            'trang_thai'   => 'required|in:active,inactive',
            'mat_khau'     => 'nullable|string|min:6|max:100',
            'ma_chi_nhanh' => 'nullable|exists:CHI_NHANH,ma_chi_nhanh',
        ]);

        $upd = [
            'chuc_vu'    => $data['chuc_vu'],
            'trang_thai' => $data['trang_thai'],
        ];
        if (! empty($data['mat_khau'])) {
            $upd['mat_khau'] = Hash::make($data['mat_khau']);
        }
        DB::table('TAI_KHOAN')->where('ma_tai_khoan', $maTaiKhoan)->update($upd);

        // Chỉ admin được chuyển nhân viên sang chi nhánh khác
        if ($this->isAdmin() && ! empty($data['ma_chi_nhanh'])) {
            DB::table('NHAN_VIEN')->where('ma_nv', $acc->nv)->update(['ma_chi_nhanh' => $data['ma_chi_nhanh']]);
        }

        return back()->with('success', "Đã cập nhật tài khoản {$maTaiKhoan}.");
    }

    /** Sinh mã kế tiếp dạng PREFIX + số đệm 3 chữ số (NV001, TK012…). */
    private function nextId(string $table, string $col, string $prefix): string
    {
        $len = strlen($prefix) + 1;
        $max = DB::table($table)
            ->where($col, 'like', $prefix.'%')
            ->selectRaw("MAX(CAST(SUBSTRING($col, $len) AS UNSIGNED)) AS m")
            ->value('m');

        return $prefix.str_pad(((int) $max) + 1, 3, '0', STR_PAD_LEFT);
    }
}
