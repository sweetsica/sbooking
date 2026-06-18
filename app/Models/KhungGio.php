<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhungGio extends Model
{
    protected $table = 'khung_gio';

    protected $fillable = ['phong_id', 'gio_bat_dau', 'gio_ket_thuc', 'thu_tu'];

    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    // Nhãn hiển thị: "08:00 - 09:00"
    public function getNhanAttribute(): string
    {
        return substr($this->gio_bat_dau, 0, 5) . ' - ' . substr($this->gio_ket_thuc, 0, 5);
    }
}
