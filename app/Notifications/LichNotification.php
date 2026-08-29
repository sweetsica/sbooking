<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\LichHen;
use App\Support\LichEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification cho tất cả event lịch (Booking + LichHen).
 *
 * Channels:
 *  - database  : luôn (cho chuông in-app)
 *  - mail      : nếu user có email
 *
 * Payload trong $data (database channel):
 *  - event        : LichEvent::*
 *  - lich_type    : 'booking' | 'lich_hen'
 *  - lich_id      : id
 *  - tieu_de      : tiêu đề ngắn
 *  - noi_dung     : nội dung 1-2 dòng
 *  - link         : url xem chi tiết
 *  - khach_hang   : tên khách
 *  - thoi_gian    : "29/06 10:00 - 11:00"
 *  - co_so_slug   : slug cơ sở (để build URL)
 *  - ghi_chu      : ly_do_tu_choi nếu có
 */
class LichNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking|LichHen $lich,
        public string $event,
        public ?string $actorName = null, // người gây ra event (admin duyệt, sale tạo, ...)
    ) {}

    public function via(object $notifiable): array
    {
        // 2026-08-28: bỏ channel mail — chỉ dùng chuông in-app (database).
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $p = $this->payload();
        $line2 = $p['thoi_gian'] ? "Thời gian: {$p['thoi_gian']}" : '';
        $line3 = $p['khach_hang'] ? "Khách hàng: {$p['khach_hang']}" : '';

        $mail = (new MailMessage)
            ->subject("[Longevity Booking] {$p['tieu_de']}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line($p['noi_dung']);

        if ($line2) $mail->line($line2);
        if ($line3) $mail->line($line3);
        if (! empty($p['ghi_chu'])) {
            $mail->line('Ghi chú: '.$p['ghi_chu']);
        }
        if (! empty($p['actor'])) {
            $mail->line('Người thực hiện: '.$p['actor']);
        }

        $mail->action('Xem chi tiết', url($p['link']))
            ->salutation('Hệ thống Longevity Booking');

        return $mail;
    }

    protected function payload(): array
    {
        $isBooking = $this->lich instanceof Booking;
        $type      = $isBooking ? 'booking' : 'lich_hen';

        $khach = $this->lich->khachHang?->ho_ten ?? 'khách';
        $coSoSlug = $this->lich->coSo?->slug ?? '';

        if ($isBooking) {
            /** @var Booking $b */
            $b = $this->lich;
            $thoiGian = $this->formatTimeRange(
                $b->ngay_dat?->format('d/m/Y') ?? '',
                $b->gio_thuc_hien ? substr($b->gio_thuc_hien, 0, 5) : null,
                $b->gio_ket_thuc  ? substr($b->gio_ket_thuc, 0, 5)  : null,
            );
            // Trỏ về trang CHI TIẾT (chỉ đọc) — hiện đủ thông tin + lý do từ chối,
            // và không đòi quyền 'sua_booking' (người chỉ có xem/duyệt vẫn mở được).
            $link = $coSoSlug ? "/{$coSoSlug}/xem-dat-phong/{$b->id}" : '#';
        } else {
            /** @var LichHen $l */
            $l = $this->lich;
            $ngay = $l->ngay_hen?->format('d/m/Y') ?? '';
            $gio  = $l->caKham ? substr($l->caKham->gio_bat_dau, 0, 5).'-'.substr($l->caKham->gio_ket_thuc, 0, 5) : '';
            $thoiGian = trim($ngay.' '.$gio);
            $link = $coSoSlug ? "/{$coSoSlug}/xem-tu-van/{$l->id}" : '#';
        }

        [$tieuDe, $noiDung] = $this->messageFor($this->event, $type, $khach, $thoiGian);

        return [
            'event'      => $this->event,
            'lich_type'  => $type,
            'lich_id'    => $this->lich->id,
            'tieu_de'    => $tieuDe,
            'noi_dung'   => $noiDung,
            'link'       => $link,
            'khach_hang' => $khach,
            'thoi_gian'  => $thoiGian,
            'co_so_slug' => $coSoSlug,
            'ghi_chu'    => $isBooking ? ($this->lich->ly_do_tu_choi ?? null) : null,
            'actor'      => $this->actorName,
        ];
    }

    protected function messageFor(string $event, string $type, string $khach, string $thoiGian): array
    {
        $loai = $type === 'booking' ? 'lịch đặt phòng' : 'lịch tư vấn';

        return match ($event) {
            LichEvent::TAO_MOI => [
                "Có {$loai} mới cần duyệt",
                "Khách \"{$khach}\" vừa đặt {$loai} ({$thoiGian}). Vui lòng vào duyệt.",
            ],
            LichEvent::DUYET => [
                ucfirst($loai).' đã được duyệt',
                "Bạn được phân công {$loai} cho khách \"{$khach}\" ({$thoiGian}).",
            ],
            LichEvent::TU_CHOI => [
                ucfirst($loai).' đã bị từ chối',
                "{$loai} của khách \"{$khach}\" ({$thoiGian}) đã bị từ chối.",
            ],
            LichEvent::CAP_NHAT => [
                ucfirst($loai).' vừa được cập nhật',
                "{$loai} của khách \"{$khach}\" ({$thoiGian}) vừa được cập nhật. Kiểm tra lại thông tin.",
            ],
            LichEvent::HUY => [
                ucfirst($loai).' đã bị hủy',
                "{$loai} của khách \"{$khach}\" ({$thoiGian}) đã bị xoá khỏi hệ thống.",
            ],
            LichEvent::NHAC_HEN => [
                "Nhắc hẹn sắp tới — {$khach}",
                "Bạn có {$loai} với khách \"{$khach}\" lúc {$thoiGian} (sắp diễn ra).",
            ],
            default => ['Thông báo lịch', "Cập nhật {$loai} của khách \"{$khach}\""],
        };
    }

    protected function formatTimeRange(string $ngay, ?string $bd, ?string $kt): string
    {
        $time = $bd ? ($kt ? "{$bd}-{$kt}" : $bd) : '';
        return trim("{$ngay} {$time}");
    }
}
