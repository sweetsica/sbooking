@extends('longevity.settings.layout')
@section('title', 'Cấu hình qua Excel')

@section('content')
<div class="mb-6">
    <h1 class="text-headline-lg mb-1">Cấu hình qua Excel</h1>
    <p class="text-body-md text-on-surface-variant">Xuất/nhập cấu hình <b>Phòng</b>, <b>Dịch vụ</b>, <b>Bác sĩ</b> của <b>toàn bộ 4 cơ sở</b> qua file Excel 3 sheet. Cột <code>co_so_id</code> phân biệt cơ sở.</p>
</div>

@if (session('import_errors') && count(session('import_errors')))
    <div class="mb-6 px-4 py-3 rounded-xl bg-error-container/60 text-on-error-container">
        <p class="font-semibold flex items-center gap-2"><span class="material-symbols-outlined">warning</span> Có {{ count(session('import_errors')) }} dòng bị bỏ qua:</p>
        <ul class="list-disc list-inside text-body-sm mt-1 max-h-64 overflow-auto">
            @foreach (session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Xuất --}}
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-secondary">download</span>
            <h2 class="text-headline-md">Xuất Excel</h2>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-4">Tải file <code>.xlsx</code> chứa 3 sheet: <code>Phong</code>, <code>DichVu</code>, <code>BacSi</code>. Chỉnh xong, quay lại đây nhập lên.</p>
        <a href="{{ route('settings.cauhinh.xuat', ['co_so' => $coSo->slug]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary text-on-secondary text-body-md font-semibold hover:opacity-90">
            <span class="material-symbols-outlined text-[18px]">file_download</span> Xuất Excel cấu hình
        </a>
    </div>

    {{-- Nhập --}}
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-tertiary-fixed-dim">upload</span>
            <h2 class="text-headline-md">Nhập Excel</h2>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-4">
            Row có <code>id</code> → cập nhật theo id. Không có <code>id</code> → tạo mới, phải có <code>co_so_id</code> hợp lệ. <b>Không xóa.</b>
        </p>
        <form method="POST" action="{{ route('settings.cauhinh.nhap', ['co_so' => $coSo->slug]) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls" required
                   class="block w-full text-body-sm file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary text-on-primary text-body-md font-semibold hover:opacity-90">
                <span class="material-symbols-outlined text-[18px]">file_upload</span> Nhập Excel
            </button>
        </form>
    </div>
</div>

<div class="mt-6 text-body-sm text-on-surface-variant bg-surface-container-low rounded-xl p-4">
    <p class="font-semibold mb-2">Ghi chú cột:</p>
    <ul class="list-disc list-inside space-y-1">
        <li><b>Phòng</b>: <code>so_slot_toi_da</code> = số slot/khách tối đa; <code>phut_moi_khach</code> = phút/khách; <code>kieu_phong</code> ∈ (<code>phong_kham</code>, <code>phong_dich_vu</code>); <code>trang_thai</code> ∈ (<code>hoat_dong</code>, <code>tam_dung</code>).</li>
        <li><b>Dịch vụ</b>: <code>thoi_gian_phut</code> = độ dài dịch vụ (phút); <code>thuoc_nhom</code> ∈ (<code>kham_ls</code>, <code>tu_van</code>, <code>khac</code>).</li>
        <li><b>Bác sĩ</b>: <code>phut_tu_van</code>/<code>phut_kham_ls</code> = phút/ca; <code>gio_bat_dau</code>/<code>gio_ket_thuc</code> định dạng HH:MM.</li>
        <li>Trường boolean nhận <code>1/0</code>, <code>true/false</code>, <code>x</code>.</li>
    </ul>
</div>
@endsection
