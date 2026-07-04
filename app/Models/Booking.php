<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'booking';

    protected $fillable = [
        'co_so_id', 'loai_dat_lich', 'khach_hang_id', 'phong_id', 'khung_gio_id', 'dich_vu_id',
        'bac_si_user_id', 'ktv_user_id', 'sale_id', 'nguoi_tao_id', 'ngay_dat', 'gio_thuc_hien', 'gio_ket_thuc',
        'so_lieu_trinh', 'nguon', 'ket_hop_medical', 'lan_dau', 'khach_tang', 'khach_tang_ghi_chu',
        'co_tu_van', 'co_kham_cls',
        'ghi_chu', 'trang_thai', 'trang_thai_khach', 'ly_do_tu_choi', 'phan_hoi_khach', 'da_duyet',
    ];

    protected $casts = [
        'ngay_dat' => 'date',
        'ket_hop_medical' => 'boolean',
        'lan_dau' => 'boolean',
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

    /**
     * User có liên quan tới booking này không? Dùng cho quyền "sửa booking liên quan":
     * là người tạo, bác sĩ, KTV, hoặc sale phụ trách.
     */
    public function laLienQuan(?\App\Models\User $user): bool
    {
        if (! $user) return false;
        return in_array($user->id, array_filter([
            $this->nguoi_tao_id,
            $this->bac_si_user_id,
            $this->ktv_user_id,
            $this->sale_id,
        ]), true);
    }

    public function phanHois(): HasMany
    {
        return $this->hasMany(BookingPhanHoi::class, 'booking_id')->latest('id');
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
        return $this->belongsTo(Ktv::class, 'ktv_user_id');
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
