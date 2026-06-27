<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CoSo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Xoá toàn bộ lịch ĐẶT PHÒNG KHÁM + ĐẶT DỊCH VỤ (bảng `booking`) — KHÔNG đụng tới
 * cơ cấu nhân sự / phòng ban / phòng / dịch vụ / khung giờ.
 *
 *   php artisan booking:clear                # tất cả cơ sở (hỏi xác nhận)
 *   php artisan booking:clear --coso=59ntn   # chỉ 1 cơ sở
 *   php artisan booking:clear --force        # không hỏi
 */
class ClearBookings extends Command
{
    protected $signature = 'booking:clear
                            {--coso= : Slug cơ sở cần xoá (bỏ trống = tất cả cơ sở)}
                            {--force : Xoá luôn, không hỏi xác nhận}';

    protected $description = 'Xoá hết lịch đặt phòng khám + dịch vụ, giữ nguyên nhân sự/phòng ban/dịch vụ';

    public function handle(): int
    {
        $slug = $this->option('coso');
        $coSo = null;

        if ($slug) {
            $coSo = CoSo::where('slug', $slug)->first();
            if (! $coSo) {
                $this->error("Không tìm thấy cơ sở có slug '{$slug}'.");
                return self::FAILURE;
            }
        }

        $query = Booking::query()->when($coSo, fn ($q) => $q->where('co_so_id', $coSo->id));
        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('Không có lịch nào để xoá.');
            return self::SUCCESS;
        }

        $phamVi = $coSo ? "cơ sở {$coSo->ten}" : 'TẤT CẢ cơ sở';
        if (! $this->option('force') && ! $this->confirm("Xoá {$count} lịch đặt của {$phamVi}? (nhân sự/phòng ban/dịch vụ giữ nguyên)")) {
            $this->comment('Đã huỷ.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($query, $coSo) {
            // Gỡ pivot booking_menu trước (khoá ngoại) rồi mới xoá booking.
            $ids = (clone $query)->pluck('id');
            DB::table('booking_menu')->whereIn('booking_id', $ids)->delete();
            (clone $query)->delete();
        });

        $this->info("Đã xoá {$count} lịch đặt của {$phamVi}.");
        $this->line('Giữ nguyên: tài khoản nhân sự, phòng ban, phòng, dịch vụ, khung giờ, khách hàng.');

        return self::SUCCESS;
    }
}
