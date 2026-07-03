<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ktv extends Model
{
    protected $table = 'ktv';

    protected $fillable = ['co_so_id', 'ten', 'gio_bat_dau', 'gio_ket_thuc', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }
}
