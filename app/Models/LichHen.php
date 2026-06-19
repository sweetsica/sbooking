<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHen extends Model
{
    protected $table = 'lich_hen';

    protected $fillable = [
        'co_so_id', 'khach_hang_id', 'bac_si_tu_van_id', 'ca_kham_id',
        'sale_id', 'ngay_hen', 'nguon', 'ghi_chu', 'trang_thai',
    ];

    protected $casts = [
        'ngay_hen' => 'date',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function bacSiTuVan(): BelongsTo
    {
        return $this->belongsTo(BacSiTuVan::class, 'bac_si_tu_van_id');
    }

    public function caKham(): BelongsTo
    {
        return $this->belongsTo(CaKham::class, 'ca_kham_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sale_id');
    }
}
