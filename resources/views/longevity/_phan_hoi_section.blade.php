@php
    // Section "Phản hồi sau khi sử dụng dịch vụ" — dùng chung cho show + edit.
    // - Ai vào được trang đều đọc được (status + list note).
    // - Chỉ user có quyền 'ghi_chu_phan_hoi' mới thấy radio đổi status + form thêm note.
    // - Chỉ tác giả (hoặc admin) mới thấy nút xóa note của mình.
    $canPhanHoi = $canPhanHoi ?? false;
    $tt = $booking->trang_thai_khach;
    $ttOptions = [
        'dung_gio' => ['Khách đến đúng giờ', 'emerald'],
        'muon'     => ['Khách đến muộn',      'amber'],
        'huy'      => ['Khách hủy',           'red'],
    ];
    $ttBadgeColor = [
        'dung_gio' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
        'muon'     => 'bg-amber-100 text-amber-700 border-amber-300',
        'huy'      => 'bg-red-100 text-red-700 border-red-300',
    ];
    $authId = auth()->id();
    $isAdmin = auth()->user()?->is_admin;
@endphp
<div class="mb-6 p-5 rounded-xl bg-surface-container-lowest border border-outline-variant">
<div class="flex items-center gap-2 pb-2 mb-4 border-b border-outline-variant">
<span class="material-symbols-outlined text-secondary">rate_review</span>
<h3 class="text-headline-md font-headline-md">Phản hồi sau khi sử dụng dịch vụ</h3>
@unless ($canPhanHoi)
<span class="ml-auto text-[11px] px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant uppercase font-label-caps">Chỉ đọc</span>
@endunless
</div>

{{-- Trạng thái khách --}}
<div class="mb-5">
<label class="block text-body-sm font-semibold text-on-surface-variant mb-2">Trạng thái</label>
@if ($canPhanHoi)
<form method="POST" action="/{{ $coSo->slug }}/trang-thai-khach/{{ $booking->id }}" class="flex flex-wrap gap-2" id="tt-khach-form-{{ $booking->id }}">
@csrf @method('PATCH')
@foreach ($ttOptions as $val => [$label, $c])
<label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition-all
    {{ $tt === $val
        ? "bg-{$c}-100 border-{$c}-400 text-{$c}-800 font-semibold"
        : 'bg-surface border-outline-variant text-on-surface-variant hover:bg-surface-container-low' }}">
<input type="radio" name="trang_thai_khach" value="{{ $val }}" @checked($tt === $val)
    onchange="document.getElementById('tt-khach-form-{{ $booking->id }}').submit()"
    class="w-3.5 h-3.5 text-{{ $c }}-500"/>
<span class="w-2.5 h-2.5 rounded-full bg-{{ $c }}-500"></span>
<span class="text-body-sm">{{ $label }}</span>
</label>
@endforeach
@if ($tt)
<button type="submit" name="trang_thai_khach" value="" class="px-3 py-2 text-body-sm text-on-surface-variant hover:bg-surface-container-low rounded-lg" title="Xóa trạng thái">
<span class="material-symbols-outlined text-[18px] align-middle">close</span> Bỏ chọn
</button>
@endif
</form>
@else
@if ($tt && isset($ttOptions[$tt]))
<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border {{ $ttBadgeColor[$tt] }} text-body-sm font-semibold">
<span class="w-2.5 h-2.5 rounded-full bg-{{ $ttOptions[$tt][1] }}-500"></span>
{{ $ttOptions[$tt][0] }}
</span>
@else
<span class="text-body-sm text-on-surface-variant italic">— Chưa cập nhật —</span>
@endif
@endif
</div>

{{-- Danh sách ghi chú phản hồi --}}
<div>
<label class="block text-body-sm font-semibold text-on-surface-variant mb-2">Note ý kiến của khách</label>
@if ($booking->phanHois && $booking->phanHois->count())
<div class="border border-outline-variant rounded-lg divide-y divide-outline-variant/50 bg-surface mb-3 overflow-hidden">
@foreach ($booking->phanHois as $ph)
@php
    $u = $ph->nguoiDung;
    $roleLabel = $u?->vaiTro?->ten ?: ($u?->chuc_danh ?: ($u?->phongBan?->ten ?: ''));
    $canDelete = $u && ($isAdmin || ($authId && $u->id === $authId));
@endphp
<div class="grid grid-cols-12 gap-3 px-4 py-2.5 hover:bg-surface-container-low/50 text-body-sm">
<div class="col-span-3 sm:col-span-2 text-on-surface-variant font-time-slot">{{ $ph->created_at?->format('d/m/Y') }}</div>
<div class="col-span-9 sm:col-span-6 text-on-surface whitespace-pre-line break-words">{{ $ph->noi_dung }}</div>
<div class="col-span-11 sm:col-span-3 text-on-surface-variant text-right sm:text-left">
{{ $roleLabel ? $roleLabel.': ' : '' }}<span class="font-semibold text-on-surface">{{ $u?->name ?? 'Ẩn danh' }}</span>
</div>
<div class="col-span-1 flex justify-end">
@if ($canPhanHoi && $canDelete)
<form method="POST" action="/{{ $coSo->slug }}/xoa-phan-hoi/{{ $booking->id }}/{{ $ph->id }}" onsubmit="return confirm('Xóa ghi chú này?')">
@csrf @method('DELETE')
<button type="submit" title="Xóa ghi chú" class="p-1 rounded hover:bg-error-container text-on-surface-variant hover:text-error">
<span class="material-symbols-outlined text-[16px]">delete</span>
</button>
</form>
@endif
</div>
</div>
@endforeach
</div>
@else
<div class="border border-dashed border-outline-variant rounded-lg px-4 py-3 mb-3 text-body-sm text-on-surface-variant italic">— Chưa có ghi chú phản hồi nào —</div>
@endif

{{-- Form thêm note: chỉ hiện với user có quyền --}}
@if ($canPhanHoi)
<form method="POST" action="/{{ $coSo->slug }}/them-phan-hoi/{{ $booking->id }}" class="space-y-2">
@csrf
<textarea name="noi_dung" required rows="2" maxlength="2000" placeholder="Ghi ý kiến của khách sau buổi hẹn..." class="w-full px-3 py-2 rounded-lg text-body-md border border-outline-variant bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all"></textarea>
<div class="flex justify-end">
<button type="submit" class="px-4 py-2 bg-primary text-on-primary font-semibold rounded-lg flex items-center gap-2 hover:opacity-90 transition-opacity text-body-sm">
<span class="material-symbols-outlined text-[18px]">add_comment</span> Thêm phản hồi
</button>
</div>
</form>
@endif
</div>
</div>
