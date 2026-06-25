# CHECKLIST QA THỦ CÔNG - HỆ THỐNG ĐẶT LỊCH

> Chuẩn bị: 1 tài khoản **Admin**, 1 tài khoản **có quyền hạn chế** (chỉ vài field), 1 tài khoản **không có quyền**. Ít nhất 2 cơ sở, mỗi cơ sở có 2 phòng (1 phòng `so_slot_toi_da=1`, 1 phòng `so_slot_toi_da=2`), 1 bác sĩ thuộc cơ sở + 1 bác sĩ `is_tu_van=true` (global), 1 KTV, 1 sale.

Cột **KQ**: ✅ Pass / ❌ Fail / ⚠️ Có vấn đề / ⬜ Chưa test

---

## A. ĐẶT PHÒNG CÔNG KHAI — `/{co_so}/tao-moi`

### A1. Truy cập & phân quyền
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| A1.1 | Mở `/lh-quan-1/tao-moi` khi đã logout | Vẫn hiển thị form (route công khai) | ⬜ | |
| A1.2 | Đăng nhập user **không có quyền `them_booking`**, vào form và submit | Trả về 403 | ⬜ | |
| A1.3 | Mở `/co-so-khong-ton-tai/tao-moi` | 404 | ⬜ | |
| A1.4 | Vào `/` khi không có cơ sở `active` nào | 404 "Chưa có cơ sở nào" | ⬜ | |

### A2. Validation form
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| A2.1 | Submit form trống | Hiện lỗi: họ tên, SĐT, phòng, khung giờ, dịch vụ, sale | ⬜ | |
| A2.2 | Email = `abcxyz` (sai format) | Lỗi email | ⬜ | |
| A2.3 | Nhập `gio_thuc_hien = 09:15` (DevTools sửa input) | Lỗi "Phút thực hiện chỉ được là 00 hoặc 30" | ⬜ | |
| A2.4 | DevTools đổi `phong_id` sang phòng của cơ sở khác | Lỗi exists | ⬜ | |
| A2.5 | SĐT nhập `0912 345 678` (có space) | Lưu thành `0912345678`, không tạo khách trùng | ⬜ | |
| A2.6 | Ngày đặt = chuỗi không phải date (`abc`) | Lỗi date | ⬜ | |

### A3. Khung giờ & trùng lịch
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| A3.1 | Phòng A (slot max=2), đã có 1 booking khung 09:00 ngày X → đặt thêm 1 khác | Tạo thành công | ⬜ | |
| A3.2 | Tiếp tục đặt booking thứ 3 cùng khung 09:00 phòng A ngày X | Lỗi "đã được đặt kín" | ⬜ | |
| A3.3 | Phòng B (slot max=1), đã có 1 booking khung 10:00 → đặt thêm | Lỗi "đã được đặt kín" | ⬜ | |
| A3.4 | Mở DevTools Network khi đổi phòng/ngày | API `/tao-moi/khung-gio` trả `capacity`, `booked`, `full` đúng | ⬜ | |
| A3.5 | Chọn KTV X đã có lịch khung 09:00 ngày Y → đặt KTV X khung 09:00 ngày Y phòng khác | Lỗi "KTV đã được đặt" | ⬜ | |
| A3.6 | Bác sĩ Z có lịch khung 09:00 phòng A → đặt bác sĩ Z khung 09:00 phòng B cùng ngày | Lưu **thành công** + flash warning "đã có lịch lúc ... — lịch vẫn được lưu" | ⬜ | Đây là cảnh báo, không chặn |
| A3.7 | Bác sĩ Z có lịch 09:00-10:00, đặt mới 09:30-10:30 (khác phòng) | Có cảnh báo trùng giờ | ⬜ | |
| A3.8 | Bác sĩ Z có lịch 09:00-10:00, đặt mới 10:00-11:00 | KHÔNG cảnh báo (sát giờ, không chồng) | ⬜ | |

### A4. Khách hàng (tự động tạo/reuse)
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| A4.1 | SĐT mới `0900000001` ở cơ sở 1 | Tạo khách mới | ⬜ | |
| A4.2 | Đặt lại với SĐT `0900000001` ở cơ sở 1, đổi tên thành "Tên Mới" | Reuse khách cũ, `ho_ten` cập nhật "Tên Mới" | ⬜ | |
| A4.3 | SĐT `0900000001` ở **cơ sở 2** | Tạo khách mới (scope theo `co_so_id`) | ⬜ | |
| A4.4 | Gọi `/tao-moi/check-sdt?sdt=0900000001` (cơ sở 1) | `{ton_tai: true, ho_ten: "..."}` | ⬜ | |
| A4.5 | Gọi check-sdt với SĐT chưa từng có | `{ton_tai: false}` | ⬜ | |

### A5. Sau khi tạo
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| A5.1 | Tạo booking thành công | Redirect `/danh-sach`, flash `ok` | ⬜ | |
| A5.2 | Trạng thái booking mới | `trang_thai = cho_duyet`, `da_duyet = false` | ⬜ | |
| A5.3 | Chọn nhiều `menu_ids` | Pivot có đủ bản ghi | ⬜ | |
| A5.4 | Tick `ket_hop_medical`, `co_tu_van`, `co_kham_cls` | Các cờ lưu đúng `true/false` | ⬜ | |

---

## B. SỬA / XÓA / DUYỆT BOOKING — cần đăng nhập

### B1. Quyền theo field (`allowedFields`)
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| B1.1 | User chỉ có quyền `sua_booking_ghi_chu` → vào form sửa | Chỉ field "Ghi chú" enable, các field khác disabled | ⬜ | |
| B1.2 | Cùng user trên, DevTools bỏ disabled, đổi `phong_id` rồi submit | Chỉ `ghi_chu` được update, `phong_id` giữ nguyên | ⬜ | |
| B1.3 | User KHÔNG có `sua_booking` → mở URL `/sua-dat-phong/{id}` | 403 | ⬜ | |
| B1.4 | Booking thuộc cơ sở 2, URL gắn `/lh-quan-1/sua-dat-phong/{id}` | 404 | ⬜ | |
| B1.5 | User có quyền sửa `so_dien_thoai`+`ho_ten` → đổi SĐT sang số mới | Tạo khách mới với SĐT mới, booking trỏ sang | ⬜ | |

### B2. Update & conflict
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| B2.1 | Sửa booking sang khung giờ đã kín slot | Lỗi "đã được đặt kín" | ⬜ | |
| B2.2 | Mở form sửa và submit không đổi gì | Lưu thành công, không báo trùng với chính nó | ⬜ | |
| B2.3 | Đổi KTV sang người đang bận khung giờ đó | Lỗi "KTV đã được đặt" | ⬜ | |
| B2.4 | Sửa khiến bác sĩ trùng giờ ở phòng khác | Lưu OK + flash warning | ⬜ | |

### B3. Duyệt / Từ chối / Xong
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| B3.1 | User không có `duyet_booking` bấm Duyệt | 403 | ⬜ | |
| B3.2 | Booking `cho_duyet` → bấm Duyệt | `da_duyet=true`, `trang_thai=da_duyet` | ⬜ | |
| B3.3 | Booking `da_duyet` → bấm Duyệt lần nữa | Toggle về `cho_duyet`, `da_duyet=false` | ⬜ | |
| B3.4 | Bấm Từ chối, không nhập lý do | Lỗi required | ⬜ | |
| B3.5 | Từ chối có lý do "Khách hủy" | `trang_thai=tu_choi`, `ly_do_tu_choi="Khách hủy"` | ⬜ | |
| B3.6 | Booking đã từ chối → bấm Duyệt | `trang_thai=da_duyet`, `ly_do_tu_choi` bị xóa null | ⬜ | |
| B3.7 | Booking `da_duyet` → bấm Xong | `trang_thai=da_xong` | ⬜ | |
| B3.8 | Booking `da_xong` → bấm Xong lần nữa | Về `da_duyet` (KHÔNG về `cho_duyet`) | ⬜ | |

### B4. Xóa
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| B4.1 | User không có `xoa_booking` bấm Xóa | 403 | ⬜ | |
| B4.2 | Xóa booking có gắn nhiều menu | Booking biến mất + pivot detach hết | ⬜ | |
| B4.3 | Sau xóa, khung giờ cũ trống slot | Vào lại form đặt được cùng khung giờ | ⬜ | |

---

## C. LỊCH TƯ VẤN — `/{co_so}/dat-kham`

### C1. Create công khai
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| C1.1 | Submit form trống | Lỗi: họ tên, SĐT, bác sĩ, ca khám, sale | ⬜ | |
| C1.2 | Chọn bác sĩ `is_tu_van=true` thuộc cơ sở khác | Vẫn hiển thị trong dropdown, đặt được | ⬜ | |
| C1.3 | Ca khám đã có 1 lịch (cùng bác sĩ + ngày) → đặt tiếp | Lỗi "Ca khám này đã có người đặt" | ⬜ | |
| C1.4 | Gọi `/dat-kham/ca-kham?bac_si_id=X&ngay=Y` | Ca đã có lịch → `full=true` | ⬜ | |
| C1.5 | Tạo thành công | Redirect `/ds-tu-van`, `trang_thai=cho_duyet` | ⬜ | |
| C1.6 | API `/dat-kham/check-sdt?sdt=...` | Trả đúng `ton_tai`/`ho_ten` | ⬜ | |

### C2. Edit / Update
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| C2.1 | User không có `sua_lich_tu_van` mở `/sua-tu-van/{id}` | 403 | ⬜ | |
| C2.2 | Mở form sửa, submit không đổi ca khám | Lưu OK (không tính trùng với chính nó) | ⬜ | |
| C2.3 | Đổi sang ca khám đã có lịch khác | Lỗi "đã có người đặt" | ⬜ | |
| C2.4 | Đổi SĐT khách sang số mới | Khách mới tạo, lịch trỏ sang | ⬜ | |

### C3. Duyệt / Xóa
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| C3.1 | User không có `duyet_tu_van` bấm Duyệt | 403 | ⬜ | |
| C3.2 | `cho_duyet` → Duyệt | `trang_thai=da_duyet` | ⬜ | |
| C3.3 | `da_duyet` → Duyệt lần nữa | Toggle về `cho_duyet` | ⬜ | |
| C3.4 | User không có `xoa_lich_tu_van` bấm Xóa | 403 | ⬜ | |
| C3.5 | Admin (`is_admin=true`) → mọi thao tác | Bypass mọi quyền | ⬜ | |

### C4. Trang `manage` (timeline bác sĩ)
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| C4.1 | `/lich-tu-van?loai=tham_kham` | Hiển thị bác sĩ vai trò `bac_si` | ⬜ | |
| C4.2 | `/lich-tu-van` (mặc định) | Hiển thị `bac_si_tu_van` | ⬜ | |
| C4.3 | Stats hiển thị | `total/approved/pending` khớp với DB | ⬜ | |
| C4.4 | Có 1 lịch `tu_choi` trong ngày | KHÔNG tính vào `booked` của bác sĩ | ⬜ | |
| C4.5 | Đổi ngày `?ngay=2026-07-01` | Timeline load đúng ngày đó | ⬜ | |

### C5. Trang `list` & filter
| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| C5.1 | Filter `ngay_tu` + `ngay_den` | Chỉ lịch trong khoảng | ⬜ | |
| C5.2 | Filter `bac_si_id` | Chỉ lịch của bác sĩ đó | ⬜ | |
| C5.3 | Filter `nguon`, `trang_thai` | Khớp đúng | ⬜ | |
| C5.4 | Sang trang 2 của pagination | Filter giữ nguyên trong URL | ⬜ | |

---

## D. AUTH & PHÂN QUYỀN CHUNG

| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| D1 | Chưa login truy cập `/lh-quan-1/lich-hen` | Redirect `/login` | ⬜ | |
| D2 | Login sai password | Ở lại login + báo lỗi | ⬜ | |
| D3 | Đổi mật khẩu thành công → logout → login bằng pass mới | OK | ⬜ | |
| D4 | User thường truy cập `/lh-quan-1/thiet-lap` | 403 | ⬜ | |
| D5 | Admin truy cập `/thiet-lap` | Vào được | ⬜ | |
| D6 | User chỉ có `phong_ban_id` (không có `vai_tro_id`) gắn quyền | Quyền vẫn match đúng | ⬜ | |
| D7 | User chỉ có `vai_tro_id` (không có `phong_ban_id`) | Quyền vẫn match đúng | ⬜ | |

---

## E. EXCEL IMPORT / EXPORT

| # | Bước | Kết quả mong đợi | KQ | Ghi chú |
|---|---|---|---|---|
| E1 | Bấm "Xuất booking" | File `.xlsx` tải về, đúng cột, đúng cơ sở | ⬜ | |
| E2 | Import file đúng format | Insert thành công, hiện số bản ghi | ⬜ | |
| E3 | Import file sai cột | Báo lỗi, không insert gì | ⬜ | |
| E4 | Import file có SĐT đã tồn tại | Reuse khách, không trùng key | ⬜ | |
| E5 | Tương tự cho lịch tư vấn (E1-E4) | OK | ⬜ | |

---

## F. SMOKE TEST CROSS-FLOW (chạy nhanh trước khi release)

| # | Kịch bản end-to-end | KQ |
|---|---|---|
| F1 | Tạo booking công khai → admin duyệt → đánh dấu xong → xuất Excel | ⬜ |
| F2 | Tạo lịch tư vấn → search bằng SĐT → mở chi tiết → duyệt | ⬜ |
| F3 | User quyền hạn chế đăng nhập → chỉ thấy menu được phép → không xem được trang không có quyền | ⬜ |
| F4 | Đặt 2 booking cùng phòng/khung giờ với phòng `slot_max=2` → đặt booking thứ 3 bị chặn | ⬜ |
| F5 | Bác sĩ tư vấn `is_tu_van` global → xuất hiện ở dropdown cả 2 cơ sở | ⬜ |

---

**Ghi chú khi gặp lỗi:**
- Chụp màn hình (kèm URL trên thanh địa chỉ)
- Note giờ chạy test
- Note user nào đang đăng nhập (vai trò + cơ sở)
- Copy log từ `storage/logs/laravel.log` nếu có 500
