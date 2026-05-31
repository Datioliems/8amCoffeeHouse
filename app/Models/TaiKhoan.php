<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaiKhoan extends Model
{
    protected $table      = 'TAI_KHOAN';
    protected $primaryKey = 'ma_tai_khoan';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_tai_khoan','ten_tk','mat_khau','chuc_vu','trang_thai','ma_nv'];

    protected $hidden = ['mat_khau'];

    public function nhanVien() { return $this->belongsTo(NhanVien::class, 'ma_nv', 'ma_nv'); }
}
