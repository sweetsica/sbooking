<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\CrmPushService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * #10 (2026-08-12) — Auto hủy booking `cho_duyet` sau 10 phút quá giờ hẹn mà Admin chưa duyệt.
 * Set trang_thai='huy' + ly_do_tu_choi='Auto ...' và bắn callback về SCRM để đồng bộ 2 bên.
 * Chạy mỗi 5 phút (routes/console.php).
 */
class AutoCancelOverdueBookings extends Command
{
    protected $signature = 'bookings:auto-cancel-overdue {--minutes=10 : Số phút quá giờ hẹn coi là quá hạn duyệt}';

    protected $description = 'Auto hủy booking cho_duyet quá X phút giờ hẹn mà chưa được Admin duyệt.';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $now = Carbon::now();
        $count = 0;

        Booking::query()
            ->where('trang_thai', 'cho_duyet')
            ->whereNotNull('ngay_dat')
            ->whereNotNull('gio_thuc_hien')
            ->whereRaw("STR_TO_DATE(CONCAT(ngay_dat, ' ', gio_thuc_hien), '%Y-%m-%d %H:%i:%s') <= ?", [
                $now->copy()->subMinutes($minutes)->toDateTimeString(),
            ])
            ->chunkById(100, function ($bookings) use (&$count) {
                foreach ($bookings as $b) {
                    $reason = 'Auto hủy: quá ' . $this->option('minutes') . ' phút giờ hẹn mà Admin chưa duyệt.';
                    DB::transaction(function () use ($b, $reason) {
                        $b->update([
                            'trang_thai' => 'huy',
                            'da_duyet' => false,
                            'ly_do_tu_choi' => $reason,
                        ]);
                    });
                    // Bắn về SCRM để BookingLog.sync_status = 'canceled' + sync_error = lý do.
                    CrmPushService::pushStatusAsync($b->fresh(), 0);
                    $count++;
                }
            });

        $this->info("Đã auto hủy {$count} booking quá hạn duyệt.");
        return self::SUCCESS;
    }
}
