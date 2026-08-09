<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phong extends Model
{
    protected $table = 'phong';

    protected $fillable = ['co_so_id', 'ten', 'loai', 'kieu_phong', 'duoc_dat_tu_van', 'so_slot_toi_da', 'phut_moi_khach', 'ktv_mac_dinh_id', 'trang_thai'];

    protected $casts = [
        'phut_moi_khach' => 'integer',
        'duoc_dat_tu_van' => 'boolean',
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

    public function bacSis(): BelongsToMany
    {
        return $this->belongsToMany(BacSi::class, 'phong_bac_si', 'phong_id', 'bac_si_id');
    }
}
