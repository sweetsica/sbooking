# Tính năng: Lịch trực bác sĩ/KTV theo ngày + Duyệt

> File bàn giao (handoff) cho người/agent tiếp quản. Đọc hết trước khi sửa.
> Trạng thái: **Backend + UI quản lý lịch ĐÃ XONG & test. Còn 1 bước: nối vào form đặt lịch (xem mục 8).**

---

## 1. Tính năng làm gì

Hàng tháng, mỗi cơ sở upload một file Excel **lịch trực** dạng lưới:
- Hàng = (Phòng × ca **Sáng/Chiều**), cột = từng ngày trong tháng.
- Mỗi ô = **username** của bác sĩ/KTV trực phòng đó, ca đó, ngày đó.
- **Ô trống = đóng phòng** ca/ngày đó (không có người trực).

Luồng: **Upload Excel → Xem trước (sửa/khớp lại) → Lưu nháp → Gửi duyệt → Duyệt → bản `da_duyet` thành lịch HIỆU LỰC**. Form đặt lịch (booking) sẽ đọc lịch hiệu lực để biết phòng/ca nào có bác sĩ.

Đặc điểm: **riêng từng cơ sở** (đa cơ sở theo slug `/{slug}/...`), **theo tháng**, gate bằng **quyền tùy chỉnh** (không phải middleware admin).

**Ca cố định**: Sáng `08:30–12:00`, Chiều `13:30–18:00` (hằng số `LichLamViec::CA`).

> ⚠️ LƯU Ý LỊCH SỬ: bản đầu hiểu sai mô hình (giờ làm việc cố định/người + số giường + thời gian ca đặt). Sau khi user đưa file thật mới biết là **lưới phân công theo ngày**. Đã rebuild. Cột cũ `co_so.gio_mo_cua/gio_dong_cua/thoi_gian_ca_phut` (migration 000001) còn trong DB nhưng **KHÔNG dùng** — để lại vô hại, có thể drop sau.

---

## 2. Cơ sở dữ liệu

3 migration (đã chạy `php artisan migrate`):

- `database/migrations/2026_07_01_000001_create_lich_lam_viec_tables.php`
  - Thêm cột `co_so`: `gio_mo_cua`, `gio_dong_cua`, `thoi_gian_ca_phut` (**không còn dùng**).
  - Bảng **`lich_lam_viec`** (header mỗi cơ sở × tháng):
    `co_so_id`, `thang` (date, ngày đầu tháng vd 2026-07-01), `trang_thai` enum `nhap|cho_duyet|da_duyet|tu_choi`, `nguoi_tao_id`, `nguoi_duyet_id`, `ly_do_tu_choi`, `file_goc` (path file Excel gốc), `ghi_chu`, `applied_at`. **unique(co_so_id, thang)**.
  - Bảng **`lich_lam_viec_chi_tiet`** (bản đầu, sau bị reshape).
- `2026_07_01_000002_seed_lich_lam_viec_perms.php` — seed 2 quyền cho vai trò `quan_tri_van_hanh`.
- `2026_07_01_000003_reshape_chi_tiet_phan_cong.php` — **reshape** `lich_lam_viec_chi_tiet` sang mô hình phân công:
  - Drop cột cũ (gio_bat_dau, gio_ket_thuc, so_giuong, thoi_gian_phut).
  - Thêm: `phong_id`, `ngay` (date), `ca` (string `sang|chieu`). Index `(phong_id, ngay, ca)`.
  - Cột giữ lại: `loai` (`bac_si|ktv`), `doi_tuong_id` (user id), `ten` (snapshot tên người).

**1 dòng chi_tiet = 1 ô trong lưới** = (loai, phong_id, ngay, ca) → doi_tuong_id.

---

## 3. Models

- `app/Models/LichLamViec.php`
  - `$fillable`, casts `thang`/`applied_at`.
  - `const TRANG_THAI`, `const CA` (Sáng/Chiều + giờ bd/kt).
  - Quan hệ: `coSo()`, `nguoiTao()`, `nguoiDuyet()`, `chiTiets()`.
  - **`static dangHieuLuc(int $coSoId, string $date): ?self`** — bản `da_duyet` của cơ sở cho tháng chứa `$date`.
  - **`static bacSiTruc(int $coSoId, int $phongId, string $date, string $ca): Collection`** — trả `[user_id => ten]` được phân công trực phòng+ngày+ca theo lịch hiệu lực. **Dùng cho bước nối form đặt lịch (mục 8).**
- `app/Models/LichLamViecChiTiet.php` — fillable (loai, doi_tuong_id, phong_id, ngay, ca, ten), cast `ngay`, quan hệ `phong()`, `nguoi()`.
- `app/Models/CoSo.php` — thêm `lichLamViecs()`.

---

## 4. Quyền (phân quyền theo vai trò — hệ `phan_quyen`)

Trong `app/Support/BookingFields.php`, nhóm **"Quyền lịch làm việc"**:
- `quyen_lich_lam_viec` — Tạo / upload / gửi duyệt / xóa.
- `duyet_lich_lam_viec` — Duyệt & áp dụng / từ chối.

Tự xuất hiện trong **Thiết lập → Quyền** (ma trận theo vai trò). Admin (`is_admin`) luôn vượt qua. Đã seed sẵn cho vai trò `quan_tri_van_hanh`.

Gate trong controller qua trait `App\Http\Controllers\Concerns\AuthorizesByPhanQuyen` (`authorizePerm()` / `hasPerm()`).

---

## 5. Routes

`routes/web.php`, trong nhóm `{co_so:slug}` → `middleware('auth')` (KHÔNG nằm trong `thiet-lap`/admin):

```
GET    /{slug}/lich-lam-viec                         llv.index
GET    /{slug}/lich-lam-viec/mau                      llv.mau      (tải Excel mẫu)
POST   /{slug}/lich-lam-viec/preview                  llv.preview  (B1: upload → xem trước)
POST   /{slug}/lich-lam-viec                          llv.store    (B2: xác nhận → lưu)
GET    /{slug}/lich-lam-viec/{lich_lam_viec}          llv.show
POST   /{slug}/lich-lam-viec/{id}/gui-duyet           llv.guiduyet
PATCH  /{slug}/lich-lam-viec/{id}/duyet               llv.duyet
PATCH  /{slug}/lich-lam-viec/{id}/tu-choi             llv.tuchoi
DELETE /{slug}/lich-lam-viec/{id}                     llv.destroy
```

> ⚠️ `mau` và `preview` PHẢI khai báo TRƯỚC route `{lich_lam_viec}` (đã đúng) — nếu không sẽ bị model-binding nuốt.

---

## 6. Controller, Import, Export

### `app/Http/Controllers/LichLamViecController.php`
- `index()` — danh sách bản lịch theo tháng.
- `show()` — chi tiết 1 bản; dựng lưới hiển thị bằng `gridFromChiTiet()` (đọc từ DB).
- `mau()` — tải Excel mẫu.
- **`preview()` (B1)** — validate tháng + file; chặn nếu tháng đã `da_duyet`; đọc file (`LichLamViecImport`) → `parseSchedule()`; **lưu file vào disk `lich-lam-viec/`**; render `preview.blade` (lưới + danh sách username chưa khớp để map).
- **`store()` (B2)** — validate; **ĐỌC LẠI** `file_goc` từ disk + áp `map[usernameRaw => user_id]` (overrides) → `parseSchedule()` → lưu `lich_lam_viec` (nháp) + `chiTiets()`. KHÔNG carry lưới qua form (chỉ thang/file_goc/ghi_chu/map).
- `guiDuyet()` / `duyet()` / `tuChoi()` / `destroy()` — chuyển trạng thái. **`duyet()` chỉ set `da_duyet` + `applied_at`** (không ghi đè gì vào hệ thống — lịch hiệu lực được đọc trực tiếp qua `bacSiTruc()`).
- **`parseSchedule(CoSo, array $data, string $thang, array $overrides=[])`** — lõi parser. Trả:
  ```
  [
    'days' => [1,2,...,N],
    'sheets' => ['bac_si'=>[...], 'ktv'=>[...]],   // mỗi phòng: ['phong_id','ten','sang'=>[day=>cell],'chieu'=>[...]]
    'unmatched' => [usernameRaw => count],          // ô có giá trị nhưng chưa khớp user
    'assignments' => [['loai','phong_id','ngay'(Y-m-d),'ca','uid','ten'], ...],  // ô đã khớp
  ]
  ```
  cell = `['raw'=>, 'uid'=>(null nếu chưa khớp), 'name'=>]`.
- Khớp: phòng theo **Mã phòng (id)** (validate thuộc cơ sở); người theo **username** (lowercase) → fallback **name** → override map; ca theo chữ "Sáng/Chiều" (dùng `Str::ascii`); cột ngày = header số 1..31 (≤ số ngày trong tháng).
- Helpers: `normalizeCa()`, `parseInt()`, `dsNguoi()` (danh sách BS/KTV cho dropdown map), `gridFromChiTiet()`.

### `app/Imports/LichLamViecImport.php` + `LichLamViecSheet.php`
- `WithMultipleSheets`, đọc **sheet index 0 = Bác sĩ, 1 = KTV** (positional, KHÔNG dùng heading-row vì slug tiếng Việt không ổn định). Sheet 2,3 (DS tra cứu) bỏ qua.
- `LichLamViecSheet` (`ToArray`) gom raw rows vào `$import->data['bac_si'/'ktv']` (row 0 = tiêu đề chứa số ngày).

### `app/Exports/LichLamViecMauExport.php` + `LichLamViecSheetExport.php`
- File mẫu 4 sheet THEO THỨ TỰ: **0 Bác sĩ** (phòng `kieu_phong=phong_kham`), **1 KTV** (`phong_dich_vu`), **2 DS Bác sĩ**, **3 DS KTV** (tra cứu username).
- Lưới: cột `Mã phòng | Vị trí | Ca | 1..31`, mỗi phòng 2 dòng (Sáng/Chiều), ô ngày trống để điền.
- `LichLamViecSheetExport` = 1 sheet generic (title + headings + rows).

---

## 7. Views

`resources/views/longevity/lich-lam-viec/`:
- `index.blade.php` — bảng theo tháng + badge trạng thái; modal **Upload** (chọn `type=month` + file, POST → `preview`, có overlay `#llv-loading` "Đang đọc dữ liệu tải lên"); modal **Từ chối**; nút Gửi duyệt/Duyệt/Từ chối/Xóa theo quyền + trạng thái.
- `preview.blade.php` — lưới xem trước (ô **xanh** = khớp, **đỏ** = chưa khớp); mục **"khớp lại"** username chưa nhận diện (dropdown BS/KTV, `name="map[<raw>]"`); thanh hành động cố định Hủy / Xác nhận & lưu. Form POST → `store`.
- `show.blade.php` — lưới read-only + nút duyệt/từ chối.

Topnav `resources/views/partials/topnav.blade.php`: thêm mục **"Lịch làm việc"** (icon `event_available`), hiện khi `$canLichLamViec` (có 1 trong 2 quyền).

Mọi view dùng Tailwind CDN (KHÔNG npm/Vite — dự án không dùng build). Head/tokens copy y hệt các trang khác.

---

## 8. ❗ VIỆC CÒN LẠI: nối vào form đặt lịch (booking)

**Yêu cầu user**: khi đặt booking, dropdown bác sĩ chỉ hiện người được phân công đúng **phòng + ngày + ca**; nếu ô trống (không ai trực) → hiển thị **"Không có bác sĩ"** = đóng phòng ca đó. User nói "tạm thời đóng phòng (hiển thị trong form chọn là - Không có bác sĩ)".

**Đã có sẵn**: `LichLamViec::bacSiTruc($coSoId, $phongId, $date, $ca)` trả `[user_id => ten]`.

**Cần làm**:
1. Đọc luồng chọn bác sĩ hiện tại: `BookingController@checkBacSi` (route `/{slug}/tao-moi/check-bac-si`) + `resources/views/longevity/create.blade.php` (JS dựng dropdown bác sĩ).
2. Map **khung giờ booking → ca**: giờ < 12:00 → `sang`, ≥ 12:00 (hoặc ≥ 13:00) → `chieu`. (Hoặc chốt lại quy ước với user; ca Sáng kết thúc 12:00, Chiều bắt đầu 13:30.)
3. Trong `checkBacSi` (và validate `store`): ứng viên bác sĩ = giao của (logic hiện tại) ∩ `bacSiTruc(...)`. Nếu `bacSiTruc` rỗng → trả danh sách rỗng + cờ "đóng phòng"; UI hiện "Không có bác sĩ" và chặn chọn.
4. (Tuỳ chọn) Hiển thị trạng thái "đóng" trên timeline `/lich-hen`.

**Lưu ý**: chỉ áp dụng khi có bản `da_duyet` cho tháng đó. Nếu cơ sở CHƯA có lịch hiệu lực cho tháng → nên fallback về hành vi cũ (không chặn) để không phá luồng đang chạy. Cần xác nhận quy ước này với user.

---

## 9. Cách test (đã dùng, không đụng dữ liệu thật)

Pattern: viết script PHP vào scratchpad, chạy bằng `php artisan tinker --execute="require '<path>';"`. Test ghi DB thì bọc `DB::beginTransaction()` ... `DB::rollBack()`.

- Round-trip Excel: `Excel::store(new LichLamViecMauExport($cs), 'x.xlsx')` → sửa ô bằng PhpSpreadsheet `IOFactory` → `Excel::import(new LichLamViecImport, path)` → gọi `parseSchedule` (reflection vì private).
- Render view: tạo user admin tạm (`is_admin=true`) → `Auth::login` → gọi `$ctrl->preview($cs, Request::create(...,['file'=>UploadedFile]))` → `->render()`.
- **Luôn**: tạo admin tạm thì `forceDelete()` sau khi xong; xoá file test ở `storage/app/private/` và `storage/app/private/lich-lam-viec/`.

> ⚠️ KHÔNG đụng tài khoản admin thật: username `admin` (name "Admin Hệ thống"). Khi cần test HTTP, tạo admin tạm rồi xoá.
> Storage default disk (Laravel 11+) = `storage/app/private`.

---

## 10. Deploy

- Chạy `php artisan migrate` (3 migration 000001/000002/000003).
- Không cần build assets (Tailwind CDN).
- Phân quyền: vào **Thiết lập → Quyền**, tick "Quyền lịch làm việc" cho vai trò/phòng ban cần.

---

## 11. Quyết định thiết kế đã chốt (nếu cần đổi, hỏi user)

- Duyệt = áp dụng NGAY (bản `da_duyet` có hiệu lực ngay), chưa có "kích hoạt đúng đầu tháng".
- Preview chỉ cho **map username chưa khớp**, KHÔNG cho sửa từng ô. Muốn đổi phân công → sửa Excel & tải lại.
- Khớp người theo **username** (user nói "ghi tên bằng username cho nhanh gọn"); fallback theo name.
- 2 ca cố định Sáng/Chiều (giờ cắt 12h–13h30).

---

## 12. Bối cảnh dự án (tóm tắt)

`lara-sbooking` = Laravel 12 + Tailwind Play CDN (KHÔNG npm/Vite). Hệ đặt lịch khám đa cơ sở theo slug `/{slug}/...`. Model tên tiếng Việt (CoSo, Phong, Booking, User...). Phân quyền theo `vai_tro` qua bảng `phan_quyen` + `BookingFields`. Có 4 cơ sở: 59ntn, 207nvt, lo23tdn, 137nct. Excel dùng package `maatwebsite/excel`. Xem thêm bộ nhớ agent (nếu có) hoặc các file controller/booking để hiểu luồng booking.
