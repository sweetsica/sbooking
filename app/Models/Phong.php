<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phong extends Model
{
    protected $table = 'phong';

    protected $fillable = ['co_so_id', 'ten', 'loai', 'so_slot_toi_da', 'trang_thai'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function khungGios(): HasMany
    {
        return $this->hasMany(KhungGio::class, 'phong_id')->orderBy('thu_tu');
    }
}
