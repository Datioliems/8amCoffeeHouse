<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model
{
    protected $table      = 'KHACH_HANG';
    protected $primaryKey = 'ma_kh';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_kh','ten_kh','sdt','ngay_tao'];
}
