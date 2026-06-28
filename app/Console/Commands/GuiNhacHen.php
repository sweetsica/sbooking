<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\LichHen;
use App\Notifications\LichNotification;
use App\Services\NotificationRecipientResolver;
use App\Support\LichEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Quét các lịch sắp diễn ra trong 60-70 phút tới và chưa nhắc, gửi thông báo NHAC_HEN
 * cho BS/KTV liên quan. Đánh dấu nhac_hen_luc để tránh nhắc trùng.
 *
 * Chạy mỗi 10 phút (xem routes/console.php).
 */
class GuiNhacHen extends Command
{
    protected $signature = 'lich:nhac-hen
                            {--minutes=60 : Nhắc bao nhiêu phút trước giờ thực hiện}
                            {--window=15  : Cửa sổ dung sai (phút) — quét lịch trong [minutes, minutes+window]}';

    protected $description = 'Gửi thông báo nhắc hẹn cho BS/KTV trước giờ lịch diễn ra.';

    public function handle(NotificationRecipientResolver $resolver): int
    {
        $minutes = (int) $this->option('minutes');
        $window  = (int) $this->option('window');

        $now = Carbon::now();
        $from = $now->copy()->addMinutes($minutes);
        $to   = $from->copy()->addMinutes($window);

        $countBooking = $this->processBookings($resolver, $from, $to);
        $countLichHen = $this->processLichHens($resolver, $from, $to);

        $this->info("Done. Booking: {$countBooking}, LichHen: {$countLichHen}");
        return self::SUCCESS;
    }

    protected function processBookings(NotificationRecipientResolver $resolver, Carbon $from, Carbon $to): int
    {
        $count = 0;
        $bookings = Booking::query()
            ->whereNull('nhac_hen_luc')
            ->where('trang_thai', 'da_duyet')
            ->whereDate('ngay_dat', '>=', $from->toDateString())
            ->whereDate('ngay_dat', '<=', $to->toDateString())
            ->whereNotNull('gio_thuc_hien')
            ->with(['khachHang', 'coSo'])
            ->get();

        foreach ($bookings as $b) {
            $start = $this->bookingStartAt($b);
            if (! $start) continue;
            if ($start->lt($from) || $start->gt($to)) continue;

            $recipients = $resolver->forBooking($b, LichEvent::NHAC_HEN);
            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new LichNotification($b, LichEvent::NHAC_HEN));
                $count++;
            }
            $b->update(['nhac_hen_luc' => now()]);
        }

        return $count;
    }

    protected function processLichHens(NotificationRecipientResolver $resolver, Carbon $from, Carbon $to): int
    {
        $count = 0;
        $lichs = LichHen::query()
            ->whereNull('nhac_hen_luc')
            ->where('trang_thai', 'da_duyet')
            ->whereDate('ngay_hen', '>=', $from->toDateString())
            ->whereDate('ngay_hen', '<=', $to->toDateString())
            ->with(['khachHang', 'coSo', 'caKham'])
            ->get();

        foreach ($lichs as $lh) {
            $start = $this->lichHenStartAt($lh);
            if (! $start) continue;
            if ($start->lt($from) || $start->gt($to)) continue;

            $recipients = $resolver->forLichHen($lh, LichEvent::NHAC_HEN);
            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new LichNotification($lh, LichEvent::NHAC_HEN));
                $count++;
            }
            $lh->update(['nhac_hen_luc' => now()]);
        }

        return $count;
    }

    protected function bookingStartAt(Booking $b): ?Carbon
    {
        if (! $b->ngay_dat || ! $b->gio_thuc_hien) return null;
        $time = substr($b->gio_thuc_hien, 0, 5);
        return Carbon::parse($b->ngay_dat->toDateString().' '.$time);
    }

    protected function lichHenStartAt(LichHen $lh): ?Carbon
    {
        if (! $lh->ngay_hen || ! $lh->caKham) return null;
        $time = substr($lh->caKham->gio_bat_dau, 0, 5);
        return Carbon::parse($lh->ngay_hen->toDateString().' '.$time);
    }
}
