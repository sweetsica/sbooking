# Longevity Booking — Changelog

Format: mỗi lần chốt tạo 1 block `## vX.Y.Z — YYYY-MM-DD` + bullets. Mới nhất ở trên cùng.

## v0.15.2 — 2026-08-17

- **Fix TDZ form tạo lịch** (`/{co_so}/tao-moi`): defer initial `loadSlots()` + `applyLoaiChinh()` sang `queueMicrotask` để `const bacSi/ktvSel/bsCoLich` khai báo phía dưới kịp init. Trước đây dropdown Bác sĩ bị đứng ở "-- Chọn khung giờ trước --", giờ populate đúng khi đổi phòng/khung giờ.

## v0.15.1 — 2026-08-16

- **Duyệt lịch thống nhất**: bỏ auto-duyệt cho `phong_kham`. Mọi booking mới (từ SCRM đẩy sang lẫn tạo trực tiếp) đều vào trạng thái `cho_duyet` — Admin vận hành sbooking bấm duyệt mới chốt lịch.

## v0.14.0 — 2026-08-13

- Thêm bộ **Changelog / Version** (trang `/changelog` + chip version ở topnav).
- Login: thêm nút "Hướng dẫn sử dụng" + nút "Chuyển sang Datasource" (gạch phân tách).
- Command `AutoCancelOverdueBookings` — auto huỷ booking quá hạn không check-in.
- `CrmPushService` bổ sung xử lý push sync.
