<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacSi extends Model
{
    protected $table = 'bac_si';

    protected $fillable = [
        'co_so_id', 'ten', 'chuc_danh', 'xuat_hien_moi_co_so',
        'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls',
        'gio_bat_dau', 'gio_ket_thuc', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'xuat_hien_moi_co_so' => 'boolean',
        'nhan_tu_van' => 'boolean',
        'nhan_kham_ls' => 'boolean',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    // Tên hiển thị kèm chức danh: "BS. Nguyễn Văn A"
    public function getTenDayDuAttribute(): string
    {
        return trim(($this->chuc_danh ? $this->chuc_danh . ' ' : '') . $this->ten);
    }
}
