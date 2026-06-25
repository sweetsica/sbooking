<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DichVu extends Model
{
    protected $table = 'dich_vu';

    protected $fillable = ['co_so_id', 'ten', 'thoi_gian_phut', 'thuoc_nhom', 'la_dich_vu', 'active'];

    protected $casts = ['active' => 'boolean', 'la_dich_vu' => 'boolean', 'thoi_gian_phut' => 'integer'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }
}
