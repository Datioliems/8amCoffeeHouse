<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mon extends Model
{
    protected $table      = 'MON';
    protected $primaryKey = 'ma_mon';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_mon','ten_mon','don_gia','mo_ta','hinh_anh','ma_danh_muc','trang_thai'];

    public function danhMuc()  { return $this->belongsTo(DanhMuc::class, 'ma_danh_muc', 'ma_danh_muc'); }
    public function dinhMucs() { return $this->hasMany(DinhMuc::class, 'ma_mon', 'ma_mon'); }
    public function options()  { return $this->hasMany(MonOption::class, 'ma_mon', 'ma_mon'); }
}
