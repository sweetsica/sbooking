<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacSi extends Model
{
    protected $table = 'bac_si';

    protected $fillable = ['co_so_id', 'ten', 'chuc_danh', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    // Tên hiển thị kèm chức danh: "BS. Nguyễn Văn A"
    public function getTenDayDuAttribute(): string
    {
        return trim(($this->chuc_danh ? $this->chuc_danh . ' ' : '') . $this->ten);
    }
}
