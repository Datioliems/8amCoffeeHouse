<?php

namespace App\Http\Controllers;

use App\Mail\StaffCredentialsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NhanVienController extends Controller
{
    /** Tất cả vai trò đăng nhập trong hệ thống (khách không có tài khoản). */
    private const ROLES = [
        'superadmin' => 'Chủ chuỗi (toàn hệ thống)',
        'admin'      => 'Quản lý chi nhánh',
        'nhan_vien'  => 'Nhân viên',
    ];

    /** Thứ bậc vai trò (số lớn = quyền cao). */
    private const RANK = ['nhan_vien' => 1, 'admin' => 2, 'superadmin' => 3];

    private function isSuperAdmin(): bool
    {
        return session('chuc_vu') === 'superadmin';
    }

    /** Vai trò mà người dùng hiện tại được phép gán cho người khác. */
    private function allowedRoles(): array
    {
        if ($this->isSuperAdmin()) {
            // Chủ chuỗi tạo được quản lý chi nhánh và nhân viên (không tạo superadmin qua UI)
            return ['admin' => self::ROLES['admin'], 'nhan_vien' => self::ROLES['nhan_vien']];
        }
        // Quản lý chi nhánh chỉ tạo/sửa nhân viên trong chi nhánh mình
        return ['nhan_vien' => self::ROLES['nhan_vien']];
    }

    public function index()
    {
        $q = DB::table('TAI_KHOAN')
            ->join('NHAN_VIEN', 'TAI_KHOAN.ma_nv', '=', 'NHAN_VIEN.ma_nv')
            ->select(
                'TAI_KHOAN.ma_tai_khoan', 'TAI_KHOAN.ten_tk', 'TAI_KHOAN.chuc_vu', 'TAI_KHOAN.trang_thai',
                'NHAN_VIEN.ma_nv', 'NHAN_VIEN.ten_nv', 'NHAN_VIEN.sdt', 'NHAN_VIEN.email', 'NHAN_VIEN.ma_chi_nhanh'
            )
            ->orderBy('NHAN_VIEN.ma_chi_nhanh')
            ->orderBy('TAI_KHOAN.ma_tai_khoan');

        if (! $this->isSuperAdmin()) {
            $q->where('NHAN_VIEN.ma_chi_nhanh', session('ma_chi_nhanh'));
        }

        return view('staff.nhanvien-list', [
            'accounts'     => $q->get(),
            'branches'     => DB::table('CHI_NHANH')->orderBy('ma_chi_nhanh')->get(),
            'roles'        => $this->allowedRoles(),
            'roleLabels'   => self::ROLES,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'myBranch'     => session('ma_chi_nhanh'),
        ]);
    }

    public function store(Request $request)
    {
        $allowed = array_keys($this->allowedRoles());

        $data = $request->validate([
            'ten_nv'       => 'required|string|max:100',
            'sdt'          => 'nullable|string|max:15',
            'email'        => 'required|email|max:150',
            'chuc_vu'      => ['required', Rule::in($allowed)],
            'ma_chi_nhanh' => 'required|exists:CHI_NHANH,ma_chi_nhanh',
        ], [], [
            'ten_nv'  => 'họ tên',
            'email'   => 'email',
            'chuc_vu' => 'vai trò',
        ]);

        // Quản lý chi nhánh bị khoá cứng vào chi nhánh của mình
        $branch = $this->isSuperAdmin() ? $data['ma_chi_nhanh'] : session('ma_chi_nhanh');

        $maNv   = $this->nextId('NHAN_VIEN', 'ma_nv', 'NV');
        $maTk   = $this->nextId('TAI_KHOAN', 'ma_tai_khoan', 'TK');
        $tenTk  = $this->nextStaffUsername();            // staff0001, staff0002…
        $matKhau = $this->randomPassword();              // mật khẩu tạm, gửi qua email

        DB::transaction(function () use ($data, $branch, $maNv, $maTk, $tenTk, $matKhau) {
            DB::table('NHAN_VIEN')->insert([
                'ma_nv'        => $maNv,
                'ten_nv'       => $data['ten_nv'],
                'sdt'          => $data['sdt'] ?? null,
                'email'        => $data['email'],
                'ma_chi_nhanh' => $branch,
            ]);
            DB::table('TAI_KHOAN')->insert([
                'ma_tai_khoan' => $maTk,
                'ten_tk'       => $tenTk,
                'mat_khau'     => Hash::make($matKhau),
                'chuc_vu'      => $data['chuc_vu'],
                'trang_thai'   => 'active',
                'ma_nv'        => $maNv,
            ]);
        });

        $sent = $this->sendCredentials($data['email'], $data['ten_nv'], $tenTk, $matKhau);

        $msg = "Đã tạo tài khoản “{$tenTk}” (mã {$maTk}). "
             . ($sent ? "Mật khẩu đã gửi tới {$data['email']}." : "Chưa gửi được email — mật khẩu tạm: {$matKhau}");

        return back()->with('success', $msg);
    }

    public function update(Request $request, string $maTaiKhoan)
    {
        $acc = $this->findAccount($maTaiKhoan);

        // Không cho tự chỉnh sửa chính mình
        abort_if($maTaiKhoan === session('tai_khoan_id'), 403, 'Không thể tự chỉnh sửa tài khoản đang đăng nhập.');

        $this->authorizeManage($acc);

        $allowed = array_keys($this->allowedRoles());

        $data = $request->validate([
            'chuc_vu'      => ['required', Rule::in($allowed)],
            'trang_thai'   => 'required|in:active,inactive',
            'reset_mat_khau' => 'nullable|boolean',
            'ma_chi_nhanh' => 'nullable|exists:CHI_NHANH,ma_chi_nhanh',
        ]);

        $upd = [
            'chuc_vu'    => $data['chuc_vu'],
            'trang_thai' => $data['trang_thai'],
        ];

        // Chỉ superadmin được đặt lại mật khẩu của nhân viên cấp thấp
        $newPassword = null;
        if (! empty($data['reset_mat_khau'])) {
            abort_unless($this->isSuperAdmin(), 403, 'Chỉ chủ chuỗi mới được đặt lại mật khẩu.');
            $newPassword = $this->randomPassword();
            $upd['mat_khau'] = Hash::make($newPassword);
        }

        DB::table('TAI_KHOAN')->where('ma_tai_khoan', $maTaiKhoan)->update($upd);

        // Chỉ superadmin được chuyển nhân viên sang chi nhánh khác
        if ($this->isSuperAdmin() && ! empty($data['ma_chi_nhanh'])) {
            DB::table('NHAN_VIEN')->where('ma_nv', $acc->nv)->update(['ma_chi_nhanh' => $data['ma_chi_nhanh']]);
        }

        $msg = "Đã cập nhật tài khoản {$maTaiKhoan}.";
        if ($newPassword) {
            $sent = $this->sendCredentials($acc->email, $acc->ten_nv, $acc->ten_tk, $newPassword);
            $msg .= $sent ? " Mật khẩu mới đã gửi tới {$acc->email}." : " Mật khẩu mới: {$newPassword}";
        }

        return back()->with('success', $msg);
    }

    public function destroy(string $maTaiKhoan)
    {
        $acc = $this->findAccount($maTaiKhoan);
        abort_if($maTaiKhoan === session('tai_khoan_id'), 403, 'Không thể tự xóa tài khoản đang đăng nhập.');
        $this->authorizeManage($acc);

        DB::transaction(function () use ($acc) {
            DB::table('TAI_KHOAN')->where('ma_tai_khoan', $acc->ma_tai_khoan)->delete();
            DB::table('NHAN_VIEN')->where('ma_nv', $acc->nv)->delete();
        });

        return back()->with('success', "Đã xóa tài khoản {$maTaiKhoan}.");
    }

    // ── Helpers ──────────────────────────────────────────────

    private function findAccount(string $maTaiKhoan)
    {
        $acc = DB::table('TAI_KHOAN')
            ->join('NHAN_VIEN', 'TAI_KHOAN.ma_nv', '=', 'NHAN_VIEN.ma_nv')
            ->where('ma_tai_khoan', $maTaiKhoan)
            ->select('TAI_KHOAN.*', 'NHAN_VIEN.ma_chi_nhanh', 'NHAN_VIEN.ma_nv as nv', 'NHAN_VIEN.ten_nv', 'NHAN_VIEN.email')
            ->first();
        abort_unless($acc, 404);
        return $acc;
    }

    /** Áp luật phân cấp: không sửa người ngang/cao hơn; quản lý chỉ thao tác nhân viên trong chi nhánh mình. */
    private function authorizeManage($acc): void
    {
        $myRank     = self::RANK[session('chuc_vu')] ?? 0;
        $targetRank = self::RANK[$acc->chuc_vu] ?? 0;

        // Không được thao tác người có vai trò ngang hoặc cao hơn mình
        abort_if($targetRank >= $myRank, 403, 'Bạn không thể thao tác trên nhân viên có vai trò ngang hoặc cao hơn.');

        if (! $this->isSuperAdmin()) {
            abort_if($acc->ma_chi_nhanh !== session('ma_chi_nhanh'), 403, 'Bạn chỉ quản lý nhân viên trong chi nhánh của mình.');
        }
    }

    /** Gửi email thông tin đăng nhập; trả về true nếu gửi thành công. */
    private function sendCredentials(?string $email, string $tenNv, string $tenTk, string $matKhau): bool
    {
        if (! $email) {
            return false;
        }
        try {
            Mail::to($email)->send(new StaffCredentialsMail($tenNv, $tenTk, $matKhau));
            return true;
        } catch (\Throwable $e) {
            Log::warning('Không gửi được email tài khoản nhân viên: ' . $e->getMessage());
            return false;
        }
    }

    private function randomPassword(): string
    {
        return Str::password(10, symbols: false);
    }

    /** Sinh tên đăng nhập staff + số đệm 4 chữ số tăng dần (staff0001…). */
    private function nextStaffUsername(): string
    {
        $max = DB::table('TAI_KHOAN')
            ->where('ten_tk', 'like', 'staff%')
            ->selectRaw("MAX(CAST(SUBSTRING(ten_tk, 6) AS UNSIGNED)) AS m")
            ->value('m');

        return 'staff' . str_pad(((int) $max) + 1, 4, '0', STR_PAD_LEFT);
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
