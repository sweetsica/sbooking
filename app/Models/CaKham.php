<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaKham extends Model
{
    protected $table = 'ca_kham';

    protected $fillable = ['bac_si_id', 'gio_bat_dau', 'gio_ket_thuc', 'thu_tu'];

    public function bacSi(): BelongsTo
    {
        return $this->belongsTo(BacSi::class, 'bac_si_id');
    }

    public function getNhanAttribute(): string
    {
        return substr($this->gio_bat_dau, 0, 5) . ' - ' . substr($this->gio_ket_thuc, 0, 5);
    }
}
