<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DichVu extends Model
{
    protected $table = 'dich_vu';

    protected $fillable = ['co_so_id', 'ten', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }
}
