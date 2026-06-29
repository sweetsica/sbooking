<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichLamViecChiTiet extends Model
{
    protected $table = 'lich_lam_viec_chi_tiet';

    protected $fillable = [
        'lich_lam_viec_id', 'loai', 'doi_tuong_id', 'phong_id', 'ngay', 'ca', 'ten',
    ];

    protected $casts = [
        'ngay' => 'date',
    ];

    public function lichLamViec(): BelongsTo
    {
        return $this->belongsTo(LichLamViec::class, 'lich_lam_viec_id');
    }

    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    public function nguoi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doi_tuong_id');
    }
}
