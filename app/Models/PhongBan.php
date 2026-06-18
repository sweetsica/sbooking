<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhongBan extends Model
{
    protected $table = 'phong_ban';

    protected $fillable = ['ten', 'ma'];

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(User::class, 'phong_ban_id');
    }
}
