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

    protected $fillable = ['ma_nv','ten_nv','sdt','cccd','dia_chi','ma_chi_nhanh'];

    public function chiNhanh()  { return $this->belongsTo(ChiNhanh::class, 'ma_chi_nhanh', 'ma_chi_nhanh'); }
    public function taiKhoan()  { return $this->hasOne(TaiKhoan::class, 'ma_nv', 'ma_nv'); }
}
