{{-- B5c (2026-08-14): modal duyệt lịch với edit sale/giờ/note. --}}
<div id="approve-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
        <form id="approve-form" method="POST" action="">
            @csrf @method('PATCH')
            <div class="p-5 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <h3 class="text-headline-md font-headline-md text-on-surface">Duyệt lịch hẹn</h3>
            </div>
            <div class="p-5 space-y-3">
                <p class="text-body-sm text-on-surface-variant">Lịch của <span id="approve-name" class="font-semibold text-on-surface"></span>. Có thể chỉnh sale tiếp đón / giờ / ghi chú trước khi duyệt.</p>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Giờ bắt đầu</label>
                        <input type="time" name="gio_thuc_hien" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                    </div>
                    <div>
                        <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Giờ kết thúc</label>
                        <input type="time" name="gio_ket_thuc" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                    </div>
                </div>
                <div>
                    <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Sale tiếp đón <span class="text-red-500">*</span></label>
                    <select name="tiep_don_user_id" id="approve-sale-select" required
                            class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                        <option value="">— chọn sale phụ trách —</option>
                    </select>
                    <p class="text-[11px] text-on-surface-variant/70 mt-0.5">Bắt buộc — Sale tiếp đón khi khách tới. Danh sách sale trong cùng cơ sở.</p>
                </div>
                <div>
                    <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Ghi chú</label>
                    <textarea name="ghi_chu" rows="2" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"></textarea>
                </div>
            </div>
            <div class="p-4 bg-surface-container-low/50 border-t border-outline-variant flex justify-end gap-2">
                <button type="button" onclick="closeApprove()" class="px-4 py-2 text-on-surface-variant font-semibold rounded-lg hover:bg-surface-container-high transition-colors">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-semibold rounded-lg flex items-center gap-2 hover:bg-emerald-700 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">check</span> Duyệt
                </button>
            </div>
        </form>
    </div>
</div>
<script>
(function(){
    var base = "/{{ $coSo->slug }}/duyet-dat-phong/";
    var m = document.getElementById('approve-modal');
    var f = document.getElementById('approve-form');
    var sel = document.getElementById('approve-sale-select');
    // 2026-08-18: bỏ cache — fetch fresh mỗi lần mở modal để cập nhật trạng thái busy/rảnh
    //   ngay khi sale bấm "Đang tiếp đón" (is_busy=true) bên UPS.
    function loadSales(coSoId, currentId){
        return fetch('/api/sales-in-cosolow?co_so_id=' + coSoId + '&_=' + Date.now(),
                     {headers:{Accept:'application/json'}, cache:'no-store'})
            .then(r => r.ok ? r.json() : {data:[]})
            .then(j => j.data || [])
            .catch(() => []);
    }
    function fillSaleOptions(list, currentId){
        // 2026-08-18: bắt buộc chọn — placeholder chỉ ra "chọn sale phụ trách".
        // Hiển thị bucket UPS + trạng thái busy nếu có (source=ups từ CRM).
        sel.innerHTML = '<option value="">— chọn sale phụ trách —</option>';
        (list || []).forEach(function(u){
            var opt = document.createElement('option');
            var label = u.name + (u.chuc_danh ? ' — ' + u.chuc_danh : '');
            if (u.bucket) label += ' · UPS ' + u.bucket + (u.busy ? ' (đang bận)' : ' (rảnh)');
            opt.value = u.id; opt.textContent = label;
            if (currentId && Number(currentId) === Number(u.id)) opt.selected = true;
            sel.appendChild(opt);
        });
    }
    window.openApprove = function(id, name, coSoId, gStart, gEnd, tiepDonId, ghiChu){
        f.action = base + id;
        document.getElementById('approve-name').textContent = name || 'khách';
        f.gio_thuc_hien.value = gStart || '';
        f.gio_ket_thuc.value = gEnd || '';
        f.ghi_chu.value = ghiChu || '';
        fillSaleOptions([], tiepDonId);
        loadSales(coSoId, tiepDonId).then(function(list){ fillSaleOptions(list, tiepDonId); });
        m.classList.remove('hidden'); m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };
    window.closeApprove = function(){
        m.classList.add('hidden'); m.classList.remove('flex');
        document.body.style.overflow = '';
    };
    m.addEventListener('click', function(e){ if(e.target === this) closeApprove(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeApprove(); });
})();
</script>
