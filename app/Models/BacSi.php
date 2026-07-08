<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BacSi extends Model
{
    protected $table = 'bac_si';

    protected $fillable = [
        'co_so_id', 'ten', 'chuc_danh', 'active', 'xuat_hien_moi_co_so',
        'nhan_tu_van', 'phut_tu_van', 'nhan_kham_ls', 'phut_kham_ls',
        'gio_bat_dau', 'gio_ket_thuc',
    ];

    protected $casts = [
        'active' => 'boolean',
        'xuat_hien_moi_co_so' => 'boolean',
        'nhan_tu_van' => 'boolean',
        'nhan_kham_ls' => 'boolean',
        'phut_tu_van' => 'integer',
        'phut_kham_ls' => 'integer',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function phongs(): BelongsToMany
    {
        return $this->belongsToMany(Phong::class, 'phong_bac_si', 'bac_si_id', 'phong_id');
    }

    public function caKhams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CaKham::class, 'bac_si_id');
    }

    /** Sinh lại các ca khám (tư vấn) theo giờ làm + phút mỗi ca (phut_tu_van). */
    public function taoCaKham(): void
    {
        $this->caKhams()->delete();

        $start = strtotime($this->gio_bat_dau ?: '08:00');
        $end = strtotime($this->gio_ket_thuc ?: '17:00');
        $duration = (int) ($this->phut_tu_van ?: 30) * 60;
        if ($duration <= 0) {
            return;
        }
        // Nghỉ trưa mặc định 12:00–13:30: ca chạm khoảng này bị bỏ, nhảy tới 13:30.
        $nghiTruaBd = strtotime(date('Y-m-d', $start) . ' 12:00');
        $nghiTruaKt = strtotime(date('Y-m-d', $start) . ' 13:30');
        $order = 0;
        while ($start + $duration <= $end) {
            if ($start < $nghiTruaKt && ($start + $duration) > $nghiTruaBd) {
                $start = $nghiTruaKt; // nhảy qua giờ nghỉ trưa
                continue;
            }
            $this->caKhams()->create([
                'gio_bat_dau' => date('H:i:00', $start),
                'gio_ket_thuc' => date('H:i:00', $start + $duration),
                'thu_tu' => $order++,
            ]);
            $start += $duration;
        }
    }

    // Tên hiển thị kèm chức danh: "BS. Nguyễn Văn A"
    public function getTenDayDuAttribute(): string
    {
        return trim(($this->chuc_danh ? $this->chuc_danh . ' ' : '') . $this->ten);
    }
}
