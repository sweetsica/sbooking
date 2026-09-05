# Longevity Booking — Changelog

Format: mỗi lần chốt tạo 1 block `## vX.Y.Z — YYYY-MM-DD` + bullets. Mới nhất ở trên cùng.

## v0.17.0 — 2026-09-04

- **Phase 6.26 — Sale làm bên SCRM, sbooking chỉ read-only cho sale**:
  - `POST /api/bookings/{id}/trang-thai-khach` + `/trang-thai-tiep-don` (SCRM push, guard `sbooking_user_id === tiep_don_user_id | sale_id`, log actor SCRM). `/comments` đã có sẵn từ Phase C1.f — reuse.
  - Blade `partials/trang-thai-lich-hen.blade.php`: ẩn 3 nút trạng thái khách + toggle tiếp đón + ô comment với user role sale (chuc_danh HC/SHC/CM/DM, không admin). Admin giữ fallback.
  - `POST /api/v1/users/{id}/toggle-busy` — SCRM push flip `users.dung_nhan_lead` khi sale toggle "Bận" bên SCRM.
  - `topnav.blade.php`: nút toggle busy đổi thành badge read-only (tooltip nhắc bấm bên SCRM). Route `/dung-nhan-lead` giữ cho admin fallback nếu cần.
  - `show.blade.php`: badge amber `· Sale hiện đang bận` cạnh tên `tiepDonUser` khi `dung_nhan_lead=true` — admin biết chủ động phân người khác lúc check-in.
- **Modal Duyệt — gợi ý UPS + cảnh báo "Chưa chốt UPS list"**:
  - Nguồn MKT chưa có `tiep_don_user_id`: `/api/sales-in-cosolow` trả `source='ups'` → auto-fill dropdown ưu tiên bucket A rảnh > A bận > B/C rảnh. Note xanh "💡 Gợi ý UPS".
  - `source='local|error'` → banner amber "⚠ Chưa chốt UPS list ngày DD/MM" + admin bắt buộc chọn tay.
  - `source='future_all_users'` giữ hành vi cũ (booking tương lai, all users cơ sở).
- **Widget dashboard carry filter `loai/nhom/ngay/q`** — trước bấm widget "Lịch chờ duyệt" ở tab Tư vấn nhảy về Lịch khám (mất `loai`).
- **Fix `nhat-ky-thong-bao`** `stdClass::$data->event` — pluck('data->event') qua Query Builder trả stdClass literal property; extract JSON path ở PHP layer.
- **`deploy/cron/queue-drain.sh`** cho sbooking — trước queue worker không chạy 24/7 → LichNotification (ShouldQueue) tồn 5 ngày. Host add cron mỗi phút.

## v0.16.2 — 2026-09-04

- **Dashboard thêm widget "Đã từ chối (7 ngày)"** — booking `tu_choi` updated_at trong 7 ngày qua, tab `?tab=rejected`. Grid dashboard giãn từ 5 → 6 cột. Trước đây booking từ chối chỉ hiện trong `/danh-sach` chung → admin/sale dễ miss.

## v0.16.1 — 2026-09-04

- **CHẶN tạo booking trực tiếp bên sbooking** — mọi lịch mới phải qua Datasource để lead/KPI/report đồng bộ.
  - `create` + `createDichVu` render trang info "Chuyển sang Datasource" + CTA link `AppSetting::scrm_url`.
  - `store` + `storeDichVu` abort 410 (defense-in-depth với form cache/duplicate submit).
  - Redirect root cho vai trò `nhan_vien` + AuthController sau login → về `/danh-sach` thay vì trang tạo.
  - API `POST /api/bookings` (datasource push) KHÔNG đụng — route riêng.
  - Luồng `/dat-lich-tu-van` (LichHen) chưa chặn — tư vấn là model riêng.

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
