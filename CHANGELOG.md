# Longevity Booking — Changelog

Format: mỗi lần chốt tạo 1 block `## vX.Y.Z — YYYY-MM-DD` + bullets. Mới nhất ở trên cùng.

## v0.16.0 — 2026-09-04

- **Rename URL đặt lịch** cho đồng bộ với `/dat-lich-dich-vu`:
  - `/{co_so}/tao-moi` → `/{co_so}/dat-lich-tham-kham` (+ sub `/khung-gio`, `/check-sdt`, `/check-ktv`, `/check-bac-si`).
  - `/{co_so}/dat-kham` → `/{co_so}/dat-lich-tu-van` (+ sub `/ca-kham`, `/check-sdt`).
  - Không giữ 301 redirect — staff dùng menu, bookmark trực tiếp URL cũ sẽ 404.
- **Dropdown "Nhân sự hỗ trợ" chỉ hiện KTV/Điều dưỡng** (loại BS/Y sĩ/GĐCM) theo prefix `chuc_danh`. API `check-bac-si` trả thêm field `chuc_danh` để frontend filter.
  - Fallback: nếu danh sách BS của DV không có KTV/DD nào → cho chọn tất (tránh dropdown rỗng khi PKD chưa gán nhân sự hỗ trợ cụ thể).
- **Seed DN**: `BacSiKtvDdSeeder` thêm cơ sở Đà Nẵng — Mai Tấn Mẫn (Giám đốc chuyên môn) + Nguyễn Thị Phượng (KTV Xét nghiệm). `DnDichVuSeeder` (mới) tạo Phòng Xét nghiệm - T2 + DV "Thăm khám lâm sàng (trừ tim mạch)" 5' + pivot BS/phòng.
- **Seed tư vấn phụ**: `TuVanExtraSeeder` (mới) — 4 DV `khong_can_phong=1` cho HCM+DN ("Tư vấn - đọc kết quả" + "Tư vấn", mỗi cơ sở 2 dv, 30').

## v0.15.2 — 2026-08-17

- **Fix TDZ form tạo lịch** (`/{co_so}/tao-moi`): defer initial `loadSlots()` + `applyLoaiChinh()` sang `queueMicrotask` để `const bacSi/ktvSel/bsCoLich` khai báo phía dưới kịp init. Trước đây dropdown Bác sĩ bị đứng ở "-- Chọn khung giờ trước --", giờ populate đúng khi đổi phòng/khung giờ.

## v0.15.1 — 2026-08-16

- **Duyệt lịch thống nhất**: bỏ auto-duyệt cho `phong_kham`. Mọi booking mới (từ SCRM đẩy sang lẫn tạo trực tiếp) đều vào trạng thái `cho_duyet` — Admin vận hành sbooking bấm duyệt mới chốt lịch.

## v0.14.0 — 2026-08-13

- Thêm bộ **Changelog / Version** (trang `/changelog` + chip version ở topnav).
- Login: thêm nút "Hướng dẫn sử dụng" + nút "Chuyển sang Datasource" (gạch phân tách).
- Command `AutoCancelOverdueBookings` — auto huỷ booking quá hạn không check-in.
- `CrmPushService` bổ sung xử lý push sync.
