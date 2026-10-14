{{--
  Phase 6.27 (2026-10-15): icon ⚠️ cạnh tên khách khi khach_hang.missing_info=true.
  Dùng ngay sau tên khách ở mọi view hiển thị khách.
  Tham số: $kh (KhachHang | null)
--}}
@if(! empty($kh) && ! empty($kh->missing_info))
<span class="inline-flex items-center align-middle ml-1 cursor-help select-none"
      title="Khách thiếu SĐT — cần cập nhật"
      aria-label="Khách thiếu SĐT — cần cập nhật">
    <span class="text-amber-600 text-sm leading-none">⚠️</span>
</span>
@endif
