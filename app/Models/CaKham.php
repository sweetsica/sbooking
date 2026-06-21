<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaKham extends Model
{
    protected $table = 'ca_kham';

    protected $fillable = ['user_id', 'gio_bat_dau', 'gio_ket_thuc', 'thu_tu'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNhanAttribute(): string
    {
        return substr($this->gio_bat_dau, 0, 5) . ' - ' . substr($this->gio_ket_thuc, 0, 5);
    }
}
