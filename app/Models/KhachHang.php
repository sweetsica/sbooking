<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhachHang extends Model
{
    protected $table = 'khach_hang';

    protected $fillable = ['co_so_id', 'ho_ten', 'so_dien_thoai', 'email'];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'khach_hang_id');
    }

    public function lichHens(): HasMany
    {
        return $this->hasMany(LichHen::class, 'khach_hang_id');
    }
}
