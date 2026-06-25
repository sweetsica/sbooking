<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $table = 'booking';

    protected $fillable = [
        'co_so_id', 'loai_dat_lich', 'khach_hang_id', 'phong_id', 'khung_gio_id', 'dich_vu_id',
        'bac_si_user_id', 'ktv_user_id', 'sale_id', 'ngay_dat', 'gio_thuc_hien', 'gio_ket_thuc',
        'so_lieu_trinh', 'nguon', 'ket_hop_medical', 'co_tu_van', 'co_kham_cls',
        'ghi_chu', 'trang_thai', 'ly_do_tu_choi', 'da_duyet',
    ];

    protected $casts = [
        'ngay_dat' => 'date',
        'ket_hop_medical' => 'boolean',
        'co_tu_van' => 'boolean',
        'co_kham_cls' => 'boolean',
        'da_duyet' => 'boolean',
    ];

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    public function khungGio(): BelongsTo
    {
        return $this->belongsTo(KhungGio::class, 'khung_gio_id');
    }

    public function dichVu(): BelongsTo
    {
        return $this->belongsTo(DichVu::class, 'dich_vu_id');
    }

    public function bacSi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bac_si_user_id');
    }

    public function ktv(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ktv_user_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'booking_menu', 'booking_id', 'menu_id');
    }
}
