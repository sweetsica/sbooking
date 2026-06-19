<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BacSiTuVan extends Model
{
    protected $table = 'bac_si_tu_van';

    protected $fillable = ['co_so_id', 'ten', 'chuc_danh', 'thoi_gian_kham', 'gio_bat_dau', 'gio_ket_thuc', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function caKhams(): HasMany
    {
        return $this->hasMany(CaKham::class, 'bac_si_tu_van_id');
    }

    public function lichHens(): HasMany
    {
        return $this->hasMany(LichHen::class, 'bac_si_tu_van_id');
    }

    public function getTenDayDuAttribute(): string
    {
        return trim(($this->chuc_danh ? $this->chuc_danh . ' ' : '') . $this->ten);
    }

    /**
     * Xóa ca cũ, tạo slot mới từ gio_bat_dau → gio_ket_thuc mỗi thoi_gian_kham phút.
     * Ca cuối lẻ (không đủ thời gian) → loại bỏ.
     */
    public function taoCaKham(): void
    {
        $this->caKhams()->delete();

        $start = strtotime($this->gio_bat_dau);
        $end = strtotime($this->gio_ket_thuc);
        $duration = (int) $this->thoi_gian_kham * 60;
        $order = 0;

        while ($start + $duration <= $end) {
            $this->caKhams()->create([
                'gio_bat_dau' => date('H:i:00', $start),
                'gio_ket_thuc' => date('H:i:00', $start + $duration),
                'thu_tu' => $order++,
            ]);
            $start += $duration;
        }
    }
}
