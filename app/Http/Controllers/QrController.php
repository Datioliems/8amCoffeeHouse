<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /** Hiển thị trang QR cho khách */
    public function scan(string $maBan)
    {
        $ban = Ban::findOrFail($maBan);
        return view('customer.scan', compact('ban'));
    }

    /** Tạo QR SVG cho màn hình quản lý bàn, không phụ thuộc Imagick. */
    public function generate(string $maBan)
    {
        $ban = Ban::findOrFail($maBan);
        $url = route('customer.scan', $maBan);

        $qr = QrCode::format('svg')
                    ->size(300)
                    ->margin(1)
                    ->generate($url);

        return response($qr)->header('Content-Type', 'image/svg+xml');
    }
}
