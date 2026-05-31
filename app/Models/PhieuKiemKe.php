<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuKiemKe extends Model
{
    protected $table      = 'PHIEU_KIEM_KE';
    protected $primaryKey = 'ma_pkk';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_pkk','ngay_kk','ma_chi_nhanh','ma_nv','trang_thai','ghi_chu'];

    public function nhanVien()       { return $this->belongsTo(NhanVien::class, 'ma_nv', 'ma_nv'); }
    public function chiTietKiemKes() { return $this->hasMany(ChiTietKiemKe::class, 'ma_pkk', 'ma_pkk'); }
}
