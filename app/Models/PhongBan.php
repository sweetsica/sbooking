<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhongBan extends Model
{
    protected $table = 'phong_ban';

    protected $fillable = ['co_so_id', 'ten', 'ma'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(User::class, 'phong_ban_id');
    }
}
