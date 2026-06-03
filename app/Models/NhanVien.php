<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    protected $table      = 'NHAN_VIEN';
    protected $primaryKey = 'ma_nv';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_nv','ten_nv','sdt','email','cccd','dia_chi','ma_chi_nhanh'];

    // PII mã hóa khi lưu, tự giải mã khi đọc qua Eloquent.
    protected $casts = [
        'sdt'     => 'encrypted',
        'email'   => 'encrypted',
        'cccd'    => 'encrypted',
        'dia_chi' => 'encrypted',
    ];

    public function chiNhanh()  { return $this->belongsTo(ChiNhanh::class, 'ma_chi_nhanh', 'ma_chi_nhanh'); }
    public function taiKhoan()  { return $this->hasOne(TaiKhoan::class, 'ma_nv', 'ma_nv'); }
}
