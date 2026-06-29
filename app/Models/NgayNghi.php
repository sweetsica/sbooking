<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgayNghi extends Model
{
    protected $table = 'ngay_nghi';

    protected $fillable = [
        'co_so_id', 'loai', 'doi_tuong_id', 'tu_ngay', 'den_ngay', 'ca', 'ly_do', 'nguoi_tao_id',
    ];

    protected $casts = [
        'tu_ngay'  => 'date',
        'den_ngay' => 'date',
    ];

    public const LOAI = [
        'co_so'  => 'Cơ sở',
        'phong'  => 'Phòng',
        'bac_si' => 'Bác sĩ',
        'ktv'    => 'KTV',
    ];

    public const CA = [
        'ca_ngay' => 'Cả ngày',
        'sang'    => 'Sáng',
        'chieu'   => 'Chiều',
    ];

    /**
     * Các giá trị `ca` của bản ghi nghỉ áp dụng được cho 1 booking có ca $bookingCa.
     * - Nghỉ cả ngày (`ca_ngay`) áp dụng cho mọi booking.
     * - Nghỉ đúng ca (sang/chieu) chỉ áp dụng khi booking rơi đúng ca đó.
     * - Booking giờ nghỉ trưa ($bookingCa = null) chỉ bị chặn bởi nghỉ cả ngày.
     *
     * @return array<int,string>
     */
    private static function caApDung(?string $bookingCa): array
    {
        $cas = ['ca_ngay'];
        if ($bookingCa) {
            $cas[] = $bookingCa;
        }

        return $cas;
    }

    /** Query cơ bản: bản ghi nghỉ phủ ngày $date và ca tương ứng cho 1 cơ sở. */
    private static function phuNgay(int $coSoId, string $date, ?string $bookingCa): Builder
    {
        return static::where('co_so_id', $coSoId)
            ->whereDate('tu_ngay', '<=', $date)
            ->whereDate('den_ngay', '>=', $date)
            ->whereIn('ca', static::caApDung($bookingCa));
    }

    /** Cơ sở đóng cửa vào ngày/ca này? (chặn cứng) */
    public static function coSoDong(int $coSoId, string $date, ?string $bookingCa): bool
    {
        return static::phuNgay($coSoId, $date, $bookingCa)->where('loai', 'co_so')->exists();
    }

    /** Phòng đóng cửa vào ngày/ca này? (chặn cứng) */
    public static function phongDong(int $coSoId, int $phongId, string $date, ?string $bookingCa): bool
    {
        return static::phuNgay($coSoId, $date, $bookingCa)
            ->where('loai', 'phong')
            ->where('doi_tuong_id', $phongId)
            ->exists();
    }

    /**
     * Danh sách user_id (bác sĩ/KTV) đang nghỉ vào ngày/ca này — dùng cho cảnh báo mềm.
     *
     * @return \Illuminate\Support\Collection<int,int>
     */
    public static function nguoiNghiIds(int $coSoId, string $date, ?string $bookingCa): \Illuminate\Support\Collection
    {
        return static::phuNgay($coSoId, $date, $bookingCa)
            ->whereIn('loai', ['bac_si', 'ktv'])
            ->whereNotNull('doi_tuong_id')
            ->pluck('doi_tuong_id');
    }

    public function coSo(): BelongsTo
    {
        return $this->belongsTo(CoSo::class, 'co_so_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function tenLoai(): string
    {
        return self::LOAI[$this->loai] ?? $this->loai;
    }

    public function tenCa(): string
    {
        return self::CA[$this->ca] ?? $this->ca;
    }
}
