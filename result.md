# lara-sbooking — Nhật ký kết quả

## 2026-09-04 — Booking: thêm "Nhân sự hỗ trợ" (KTV/DD) 🚧

Nhánh `eighteenth`. Trạng thái: Phase 1 (backend + schema) + Phase 2 (form create) done. Còn: form edit populate old value + Phase 3 notification + Phase 4 sync SCRM + Phase 5 report.

### Design chốt (v3)
- Không dùng pivot, chỉ thêm 1 cột `booking.ho_tro_id` nullable (FK → `bac_si`).
- 1 người chính (BS/KTV — cột `bac_si_id` hiện tại) + 1 người hỗ trợ (KTV/DD — cột mới `ho_tro_id`). Không quá 2 người/ca.
- `ho_tro_id` optional cả về schema lẫn UI — dv nào cần thì admin cơ sở tự chọn.

### Phase 1 — Schema + Model + Controller ✅
- Migration [2026_09_04_100000_add_ho_tro_id_to_booking.php](database/migrations/2026_09_04_100000_add_ho_tro_id_to_booking.php) — cột `booking.ho_tro_id` nullable + FK.
- [Booking.php](app/Models/Booking.php) — fillable + relationship `hoTro()`.
- [BookingController.php](app/Http/Controllers/BookingController.php) — 2 chỗ validate (store+update) `different:bac_si_id` + exists; 2 chỗ save; 2 chỗ capacity check gọi lại `checkBacSiCapacity` cho `ho_tro_id`.
- [BookingFields.php](app/Support/BookingFields.php) — thêm key `ho_tro_id` "Nhân sự hỗ trợ (KTV/DD)".

### Phase 2 — Form create ✅
- [longevity/create.blade.php:406-413](resources/views/longevity/create.blade.php) — thêm `<select id="ho_tro" name="ho_tro_id">` ngay dưới dropdown BS.
- JS `syncHoTro()`: mirror danh sách từ `bac_si` sau mỗi `loadBacSi()`, exclude BS đã chọn. Reactive khi đổi BS chính.
- `trackable` array cập nhật để `ho_tro_id` chịu phân quyền field-level.

### TODO còn lại
- **Form edit**: populate `data-old="{{ $booking->ho_tro_id }}"` để giữ giá trị cũ khi mở edit. Chờ merge với template edit.
- **Phase 3 notification**: `LichNotification` gửi in-app cho `ho_tro` (nếu có).
- **Phase 4 sync SCRM**: mirror `ho_tro_id` → `sb_bookings`/`booking_logs` bên scrm (migration + payload).
- **Phase 5 report**: cột nhân sự hỗ trợ trong export xlsx (nếu cần).

---

## 2026-09-03 — Reset phòng + dịch vụ HCM theo bản chốt PKD + pivot dv↔BS ✅

Nhánh `seventeenth`. Data HN + ĐN không đụng.

### Migration
- [database/migrations/2026_09_03_140000_reset_hcm_phong_dich_vu.php](database/migrations/2026_09_03_140000_reset_hcm_phong_dich_vu.php)
  - Tạo bảng pivot `dich_vu_bac_si` (`dich_vu_id, bac_si_id, unique`) — map dv ↔ nhân sự thực hiện.
  - **BREAKING**: xoá toàn bộ `booking` HCM (co_so_id=2, cả done lẫn chưa done), `phong_bac_si` HCM, `dich_vu_phong` HCM, `chi_tiet_phan_cong` phòng HCM, `ngay_nghi` phòng HCM, rồi xoá `phong` + `dich_vu` HCM. User đã chốt được clear + đã backup DB trước khi chạy.

### Seeders mới
- [database/seeders/BacSiKtvDdSeeder.php](database/seeders/BacSiKtvDdSeeder.php) — 15 nhân sự HCM (8 BS gồm Bsi Quỳnh + Y sĩ Thuận, 4 KTV, 3 DD) + 7 BS HN. `chuc_danh` viết đầy đủ ("Kỹ thuật viên"/"Điều dưỡng").
- [database/seeders/HcmPhongResetSeeder.php](database/seeders/HcmPhongResetSeeder.php) — 14 phòng HCM có suffix tầng (T1..T6), `phut_moi_khach` khớp dv chính, `loai='kham'`.
- [database/seeders/HcmDichVuResetSeeder.php](database/seeders/HcmDichVuResetSeeder.php) — 23 dv + pivot `dich_vu_phong` (27 dòng). Đổi tên BJR/PRP/HA/PRF thành "Tiêm ... (1 khớp)". Split EAQ (1 vùng) thành 2 dv (1 vùng) + (toàn bộ).
- [database/seeders/HcmDichVuBacSiSeeder.php](database/seeders/HcmDichVuBacSiSeeder.php) — 45 dòng pivot dv↔nhân sự HCM theo mapping ảnh PKD.

### Seeder cũ dọn
- [LongevitySeeder.php:404](database/seeders/LongevitySeeder.php) — block seed 3 BS HCM cũ ("Hoàng Văn Đông", "Lê Huy Thư", "Đặng Công Danh" không prefix) đã gỡ + thêm vào danh sách delete để migrate:fresh không tạo dòng trùng.

### Kết quả prod (sbooking.sweetsica.com)
- 14 phòng HCM ✓
- 23 dv HCM + 27 pivot dv-phòng + 45 pivot dv-BS ✓
- 15 BS HCM (đã dọn 3 dòng cũ id 8/9/10) ✓

### Đi kèm bên scrm
- `php artisan sb:sync-bac-si` → 25 BS mirror về `sb_bac_si` (15 mới + 10 update).

---

## 2026-08-10 — Booking trễ + Dừng nhận lead + tách nút tiếp tân/sale + nút quay lại phase 3 ✅

Nhánh `tenth`. Đi kèm patch bên [lara-scrm](../lara-scrm/result.md) cùng ngày (final-01).

### T1 — Tách nút "Đang tiếp đón" + "Đã xong" sang phải
- **[partials/trang-thai-lich-hen.blade.php](resources/views/longevity/partials/trang-thai-lich-hen.blade.php)** — bọc nút tiếp tân ("Khách đã tới" / "Tới trễ" / "Hủy") vào group trái; nút sale ("Booking trễ" / "Đang tiếp đón" / "Đã xong") vào group `ml-auto` bên phải.

### T3 — Cột `booking_tre` + button + filter
- Migration [2026_08_10_130000_add_booking_tre_to_bookings.php](database/migrations/2026_08_10_130000_add_booking_tre_to_bookings.php) — cột `booking.booking_tre` bool default 0 + index. Bảng tên là `booking` (không `bookings`).
- [Booking.php](app/Models/Booking.php) — fillable + cast.
- [BookingController::toggleBookingTre](app/Http/Controllers/BookingController.php) — endpoint `PATCH /{co_so}/booking-tre/{booking}`, gate `is_admin || vaiTro.ma ∈ [admin_co_so, quan_tri_van_hanh]` (không log lịch sử theo yêu cầu).
- Nút "Booking trễ" trong khối trạng thái detail (chỉ hiện với role đúng).
- Filter checkbox "Chỉ booking trễ" trong [longevity/bookings.blade.php](resources/views/longevity/bookings.blade.php), [PageController::bookings](app/Http/Controllers/PageController.php) áp `->where('booking_tre', true)` khi `request->boolean('booking_tre')`.

### T5 — Nút "Dừng nhận lead" trên topbar
- Migration [2026_08_10_120000_add_dung_nhan_lead_to_users.php](database/migrations/2026_08_10_120000_add_dung_nhan_lead_to_users.php) — cột `users.dung_nhan_lead` + `dung_nhan_lead_since`.
- [User.php](app/Models/User.php) — fillable + cast.
- [AuthController::toggleDungNhanLead](app/Http/Controllers/AuthController.php) + route `POST /dung-nhan-lead` — toggle local, push scrm cùng lúc qua [CrmPushService::pushDungNhanLead](app/Services/CrmPushService.php) (POST scrm `/api/ups/pause` or `/resume`).
- [partials/topnav.blade.php](resources/views/partials/topnav.blade.php) — nút toggle chỉ hiện cho sale (`chuc_danh ∈ [HC, SHC, CM, DM]`, không admin). Label đổi theo state: `Nhận lead` (emerald) ↔ `Đang tạm dừng` (slate).
- Log rõ hơn khi `pushTiepDon` fail (dump `reason` từ scrm response) — giúp debug bug user báo "bấm Đang tiếp đón mà vẫn Sẵn sàng".

### Điều khác biệt logic
- `is_busy` (đang tiếp đón): dispatcher scrm skip bình thường, nhưng wrap-around (`includeBusy=true`) vẫn chia.
- `dung_nhan_lead` (dừng nhận lead): dispatcher scrm skip **tuyệt đối** kể cả wrap-around.
- Sale đang tiếp đón vẫn có thể bấm "Dừng nhận lead" (2 flag độc lập).

### QA
- `migrate:fresh --seed` OK cả 2 bên.
- Route mới hiện đủ trong `route:list`.
- Chưa QA browser tay — cần login 1 sale HC/SHC bên booking để verify nút topbar + push scrm hoạt động.

### T2 (bên scrm)
- Cấp thêm `ups.view/checkin/override/confirm_daily` cho role Admin cơ sở qua `AdminCoSoSeeder`. Đồng bộ quyền với tài khoản duyệt bên booking.

### T4 (bên scrm)
- Nút `↩ Tạo booking khác cho khách này` trong phase 4 (Check-in) của `⚡lead-form.blade.php`, gọi `markReturning(3)` để sale quay về phase 3 tạo booking tiếp.
