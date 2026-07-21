<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'booking';

    protected $fillable = [
        'ma_booking',
        'co_so_id', 'loai_dat_lich', 'khach_hang_id', 'phong_id', 'khung_gio_id', 'dich_vu_id',
        'bac_si_id', 'ktv_user_id', 'sale_id', 'ngay_dat', 'gio_thuc_hien', 'gio_ket_thuc',
        'so_lieu_trinh', 'so_luong_lo', 'dung_tich_lo', 'nguon', 'ket_hop_medical', 'co_tu_van', 'co_kham_cls',
        'ghi_chu', 'trang_thai', 'trang_thai_khach', 'ly_do_tu_choi', 'phan_hoi_khach', 'da_duyet',
        'nguoi_tao_id',
    ];

    protected $casts = [
        'ngay_dat' => 'date',
        'nguoi_tao_id' => 'integer',
        'ket_hop_medical' => 'boolean',
        'co_tu_van' => 'boolean',
        'co_kham_cls' => 'boolean',
        'da_duyet' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Tự sinh ma_booking sau khi tạo (cần id). Format: BKG-yymmdd-{id 6 số}.
        static::created(function (self $b): void {
            if (! $b->ma_booking) {
                $b->ma_booking = sprintf('BKG-%s-%06d', ($b->created_at ?? now())->format('ymd'), $b->id);
                $b->saveQuietly();
            }
        });
    }

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
        return $this->belongsTo(BacSi::class, 'bac_si_id');
    }

    public function ktv(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ktv_user_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'booking_menu', 'booking_id', 'menu_id');
    }

    public function binhLuans(): HasMany
    {
        return $this->hasMany(BookingBinhLuan::class, 'booking_id')->latest();
    }

    /**
     * Booking đang GIỮ CHỖ (chiếm slot): không bị từ chối và khách không hủy.
     * Dùng cho mọi tính toán occupancy để "khách hủy" trả slot về kho.
     */
    public function scopeGiuCho(Builder $q): Builder
    {
        return $q->where('trang_thai', '!=', 'tu_choi')
            ->where(fn ($qq) => $qq->whereNull('trang_thai_khach')->orWhere('trang_thai_khach', '!=', 'huy'));
    }

    /**
     * Giới hạn booking theo quyền xem của $user (3 mức, tăng dần):
     * - 'xem_booking'            → toàn bộ trong cơ sở (không giới hạn thêm).
     * - 'xem_booking_phong_ban'  → booking do người CÙNG PHÒNG BAN tạo (gồm cả mình).
     * - (không mức nào)          → chỉ booking do chính mình tạo (nguoi_tao_id = id).
     * $user null → không thấy gì.
     */
    public function scopeVisibleTo(Builder $q, ?User $user): Builder
    {
        $table = $q->getModel()->getTable();

        // Mức cao nhất: xem tất cả trong cơ sở.
        if ($user && $user->coQuyen('xem_booking')) {
            return $q;
        }

        // Mức nhánh con: người cùng phòng ban tạo. Cần có phong_ban_id mới xác định được nhánh.
        if ($user && $user->phong_ban_id && $user->coQuyen('xem_booking_phong_ban')) {
            return $q->whereIn($table . '.nguoi_tao_id', function ($sub) use ($user) {
                $sub->select('id')->from('users')->where('phong_ban_id', $user->phong_ban_id);
            });
        }

        // Mặc định: chỉ booking mình tạo.
        return $q->where($table . '.nguoi_tao_id', $user?->id ?? 0);
    }
}
