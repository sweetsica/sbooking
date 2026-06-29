<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LichLamViec extends Model
{
    protected $table = 'lich_lam_viec';

    protected $fillable = [
        'co_so_id', 'thang', 'trang_thai',
        'nguoi_tao_id', 'nguoi_duyet_id',
        'ly_do_tu_choi', 'file_goc', 'ghi_chu', 'applied_at',
    ];

    protected $casts = [
        'thang' => 'date',
        'applied_at' => 'datetime',
    ];

    public const TRANG_THAI = [
        'nhap'      => 'Nháp',
        'cho_duyet' => 'Chờ duyệt',
        'da_duyet'  => 'Đã duyệt',
        'tu_choi'   => 'Từ chối',
    ];

    /** Ca làm việc cố định (Sáng / Chiều). */
    public const CA = [
        'sang'  => ['ten' => 'Sáng',  'bd' => '08:30', 'kt' => '12:00'],
        'chieu' => ['ten' => 'Chiều', 'bd' => '13:30', 'kt' => '18:00'],
    ];

    /**
     * Map giờ bắt đầu (HH:MM) của một khung booking sang ca trực.
     * < 12:00 → sáng; >= 13:30 → chiều; khoảng 12:00–13:30 (nghỉ trưa) → null (đóng).
     */
    public static function caTheoGio(?string $gioBatDau): ?string
    {
        if (! $gioBatDau || ! preg_match('/^(\d{2}):(\d{2})$/', $gioBatDau, $m)) {
            return null;
        }
        $phut = (int) $m[1] * 60 + (int) $m[2];
        if ($phut < 12 * 60) {
            return 'sang';
        }
        if ($phut >= 13 * 60 + 30) {
            return 'chieu';
        }

        return null; // 12:00–13:30: ngoài giờ trực
    }

    /**
     * Bản lịch ĐANG HIỆU LỰC (da_duyet) của 1 cơ sở cho tháng chứa $date.
     */
    public static function dangHieuLuc(int $coSoId, string $date): ?self
    {
        $thang = date('Y-m-01', strtotime($date));

        return static::where('co_so_id', $coSoId)
            ->whereDate('thang', $thang)
            ->where('trang_thai', 'da_duyet')
            ->first();
    }

    /**
     * Danh sách bác sĩ / KTV được phân công trực 1 phòng, 1 ngày, 1 ca
     * theo lịch đang hiệu lực. Trả về Collection user_id => ten.
     */
    public static function bacSiTruc(int $coSoId, int $phongId, string $date, string $ca): \Illuminate\Support\Collection
    {
        $lich = static::dangHieuLuc($coSoId, $date);
        if (! $lich) {
            return collect();
        }

        return $lich->chiTiets()
            ->where('phong_id', $phongId)
            ->whereDate('ngay', $date)
            ->where('ca', $ca)
            ->whereNotNull('doi_tuong_id')
            ->get()
            ->pluck('ten', 'doi_tuong_id');
    }

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_duyet_id');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(LichLamViecChiTiet::class, 'lich_lam_viec_id');
    }

    public function tenTrangThai(): string
    {
        return self::TRANG_THAI[$this->trang_thai] ?? $this->trang_thai;
    }
}
