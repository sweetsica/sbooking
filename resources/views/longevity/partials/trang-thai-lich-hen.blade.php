{{-- Phase C1.b rev8 2026-08-01: block "Trạng thái lịch hẹn" — dùng chung cho show + edit form.
     Include kèm biến: booking, coSo, canTrangThai, canBinhLuan, isAdmin.
     Data đẩy về CRM (data source) qua CrmPushService khi bấm nút. --}}
@php
    $canTrangThai = $canTrangThai ?? false;
    $canBinhLuan  = $canBinhLuan ?? false;
    $isAdmin      = $isAdmin ?? false;
    $ttk = $booking->trang_thai_khach;
    $ttkNhan = ['da_toi' => 'Khách đã tới', 'toi_tre' => 'Khách tới trễ', 'huy' => 'Khách hủy'];
    $done = $booking->trang_thai === 'da_xong';
@endphp
<div class="mb-6 rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm overflow-hidden">
<div class="flex items-center gap-2 px-5 py-3 border-b border-outline-variant bg-surface-container-low/50">
<span class="material-symbols-outlined text-primary">rate_review</span>
<h3 class="text-headline-md font-headline-md">Trạng thái lịch hẹn</h3>
@if ($ttk)<span class="ml-1 px-2.5 py-0.5 rounded-full text-body-sm font-semibold {{ $ttk === 'huy' ? 'bg-red-100 text-red-700' : ($ttk === 'toi_tre' ? 'bg-amber-100 text-amber-700' : 'bg-tertiary-fixed-dim/40 text-on-tertiary-container') }}">{{ $ttkNhan[$ttk] }}</span>@endif
</div>

{{-- Phần 1: nút trạng thái --}}
@if ($canTrangThai)
<div class="px-5 py-4 flex flex-wrap gap-2 border-b border-outline-variant">
@php
    $hasCrmLink = ! empty($booking->crm_khach_ma);
    $confirmSuffix = $hasCrmLink
        ? ' Trạng thái này sẽ được đẩy sang CRM khách hàng ' . $booking->crm_khach_ma . '.'
        : '';
@endphp
@foreach (['da_toi' => ['Khách đã tới', 'how_to_reg'], 'toi_tre' => ['Khách tới trễ', 'schedule'], 'huy' => ['Khách hủy', 'person_off']] as $val => $meta)
<form method="POST" action="/{{ $coSo->slug }}/trang-thai-khach/{{ $booking->id }}"
      onsubmit="return confirm('Xác nhận đổi trạng thái sang: {{ $meta[0] }}?{{ $confirmSuffix }}');">
@csrf @method('PATCH')
<input type="hidden" name="trang_thai_khach" value="{{ $val }}"/>
<button type="submit" class="h-[38px] px-4 rounded-lg font-semibold text-body-sm flex items-center gap-1.5 border transition-colors {{ $ttk === $val ? ($val === 'huy' ? 'bg-red-600 text-white border-red-600' : 'bg-secondary text-on-secondary border-secondary') : 'border-outline text-on-surface-variant hover:bg-surface-container-high' }}">
<span class="material-symbols-outlined text-[18px]">{{ $meta[1] }}</span> {{ $meta[0] }}
</button>
</form>
@endforeach
<form method="POST" action="/{{ $coSo->slug }}/xong-dat-phong/{{ $booking->id }}"
      onsubmit="return confirm('Xác nhận: {{ $done ? 'Bỏ trạng thái Đã xong' : 'Đánh dấu Đã xong' }}?{{ $confirmSuffix }}');">
@csrf @method('PATCH')
<button type="submit" class="h-[38px] px-4 rounded-lg font-semibold text-body-sm flex items-center gap-1.5 border transition-colors {{ $done ? 'bg-primary text-on-primary border-primary' : 'border-outline text-on-surface-variant hover:bg-surface-container-high' }}">
<span class="material-symbols-outlined text-[18px]">task_alt</span> {{ $done ? 'Đã xong ✓' : 'Đã xong' }}
</button>
</form>
</div>
@endif

{{-- Phần 2: danh sách bình luận --}}
<div class="px-5 py-4 space-y-3 max-h-72 overflow-y-auto">
@forelse ($booking->binhLuans as $bl)
<div class="flex items-start gap-2 group">
<span class="material-symbols-outlined text-on-surface-variant/60 text-[20px] mt-0.5">chat_bubble</span>
<div class="flex-1">
<div class="text-body-md text-on-surface whitespace-pre-line">{{ $bl->noi_dung }}</div>
<div class="text-[11px] text-on-surface-variant mt-0.5">
{{ $bl->nguoiDung?->vaiTro?->ten ?? 'Người dùng' }}: <span class="font-semibold">{{ $bl->nguoiDung?->name ?? '—' }}</span>
· {{ $bl->created_at?->format('d/m/Y H:i') }}
</div>
</div>
@if ($isAdmin)
<form method="POST" action="/{{ $coSo->slug }}/binh-luan/{{ $booking->id }}/{{ $bl->id }}" onsubmit="return confirm('Xóa bình luận này?')">
@csrf @method('DELETE')
<button type="submit" class="opacity-0 group-hover:opacity-100 p-1 text-on-surface-variant hover:text-error rounded transition-all" title="Xóa"><span class="material-symbols-outlined text-[18px]">delete</span></button>
</form>
@endif
</div>
@empty
<p class="text-body-sm text-on-surface-variant text-center py-2">— Chưa có bình luận nào —</p>
@endforelse
</div>

{{-- Phần 3: ô nhập bình luận --}}
@if ($canBinhLuan)
<form method="POST" action="/{{ $coSo->slug }}/binh-luan/{{ $booking->id }}" class="px-5 py-4 border-t border-outline-variant flex items-end gap-2">
@csrf
<textarea name="noi_dung" rows="2" required placeholder="Nhập bình luận / phản ánh của khách..." class="flex-1 px-3 py-2 rounded-lg text-body-md border border-outline-variant bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all resize-none">{{ old('noi_dung') }}</textarea>
<button type="submit" class="h-[42px] px-4 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-1.5 hover:opacity-90 transition-opacity text-body-sm">
<span class="material-symbols-outlined text-[18px]">send</span> Gửi
</button>
</form>
@endif
</div>
