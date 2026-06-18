<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhanQuyen extends Model
{
    protected $table = 'phan_quyen';

    protected $fillable = ['phong_ban_id', 'truong'];

    public function phongBan(): BelongsTo
    {
        return $this->belongsTo(PhongBan::class, 'phong_ban_id');
    }
}
