<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phong extends Model
{
    protected $table = 'phong';

    protected $fillable = ['co_so_id', 'ten', 'loai', 'kieu_phong', 'so_slot_toi_da', 'phut_moi_khach', 'ktv_mac_dinh_id', 'trang_thai'];

    protected $casts = [
        'phut_moi_khach' => 'integer',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function khungGios(): HasMany
    {
        return $this->hasMany(KhungGio::class, 'phong_id')->orderBy('thu_tu');
    }

    public function ktvMacDinh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ktv_mac_dinh_id');
    }
}
