<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table      = 'ORDERS';
    protected $primaryKey = 'ma_order';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = ['ma_order','ma_ban','ma_kh','ma_chi_nhanh','trang_thai','ngay_order','gio_order','ghi_chu'];

    protected $casts = [
        'ngay_order' => 'date',
        'gio_order'  => 'datetime:H:i',
    ];

    public function ban()          { return $this->belongsTo(Ban::class, 'ma_ban', 'ma_ban'); }
    public function khachHang()    { return $this->belongsTo(KhachHang::class, 'ma_kh', 'ma_kh'); }
    public function chiNhanh()     { return $this->belongsTo(ChiNhanh::class, 'ma_chi_nhanh', 'ma_chi_nhanh'); }
    public function chiTietOrders(){ return $this->hasMany(ChiTietOrder::class, 'ma_order', 'ma_order'); }
    public function hoaDon()       { return $this->hasOne(HoaDon::class, 'ma_order', 'ma_order'); }
}
