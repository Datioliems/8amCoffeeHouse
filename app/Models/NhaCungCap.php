<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaCungCap extends Model
{
    protected $table      = 'NHA_CUNG_CAP';
    protected $primaryKey = 'ma_ncc';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_ncc','ten_ncc','dia_chi','sdt','email'];

    public function phieuNhapKhos() { return $this->hasMany(PhieuNhapKho::class, 'ma_ncc', 'ma_ncc'); }
}
