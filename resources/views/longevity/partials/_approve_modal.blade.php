{{-- B5c (2026-08-14): modal duyệt lịch với edit sale/giờ/note.
     2026-08-18: hiển thị source info + owner (creator) + option "Thêm sale hỗ trợ".
     Nguồn SA/BA/MKT_BR: field Sale tiếp đón bị lock (fix = creator), admin chỉ được thêm sale hỗ trợ. --}}
<div id="approve-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
        <form id="approve-form" method="POST" action="">
            @csrf @method('PATCH')
            <div class="p-5 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <h3 class="text-headline-md font-headline-md text-on-surface">Duyệt lịch hẹn</h3>
            </div>
            <div class="p-5 space-y-3">
                <p class="text-body-sm text-on-surface-variant">Lịch của <span id="approve-name" class="font-semibold text-on-surface"></span>.</p>

                {{-- 2026-08-18 — Info nguồn / người tạo / người tele (highlight khi SA/BA/MKT_BR) --}}
                <div id="approve-source-info" class="hidden p-3 rounded-lg border border-amber-300 bg-amber-50 text-body-sm">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="material-symbols-outlined text-amber-700 text-[18px]">verified</span>
                        <span class="font-bold text-amber-900">Lead tự tạo</span>
                        <span id="approve-source-badge" class="ml-1 px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 text-[11px] font-semibold uppercase"></span>
                    </div>
                    <div class="text-amber-900">Người tạo / Tele / Tiếp đón gốc: <b id="approve-creator-name">—</b></div>
                    <div class="text-[11px] text-amber-800/80 mt-1">Sale gốc được fix cứng để bảo vệ quyền sở hữu. Admin có thể thêm Sale hỗ trợ đón kèm khi bận.</div>
                </div>

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
                    <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">
                        Sale tiếp đón <span class="text-red-500">*</span>
                        <span id="approve-sale-lock-badge" class="hidden ml-1 px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-bold uppercase">Fix cứng</span>
                    </label>
                    <select name="tiep_don_user_id" id="approve-sale-select" required
                            class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                        <option value="">— chọn sale phụ trách —</option>
                    </select>
                    <p class="text-[11px] text-on-surface-variant/70 mt-0.5">Bắt buộc — Sale tiếp đón khi khách tới. Danh sách sale trong cùng cơ sở.</p>
                </div>

                {{-- 2026-08-18 — Sale hỗ trợ optional --}}
                <div class="pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="approve-ho-tro-toggle" class="rounded border-outline-variant">
                        <span class="text-body-sm font-semibold text-on-surface">Thêm sale hỗ trợ tiếp đón</span>
                    </label>
                    <div id="approve-ho-tro-wrap" class="hidden mt-2">
                        <select name="tiep_don_ho_tro_id" id="approve-ho-tro-select"
                                class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                            <option value="">— chọn sale hỗ trợ —</option>
                        </select>
                        <p class="text-[11px] text-on-surface-variant/70 mt-0.5">Sale gốc giữ nguyên sở hữu. Cả 2 đều được ghi nhận · report 0.5 mỗi người.</p>
                    </div>
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
    var selHT = document.getElementById('approve-ho-tro-select');
    var htToggle = document.getElementById('approve-ho-tro-toggle');
    var htWrap = document.getElementById('approve-ho-tro-wrap');
    var srcInfo = document.getElementById('approve-source-info');
    var srcBadge = document.getElementById('approve-source-badge');
    var creatorName = document.getElementById('approve-creator-name');
    var lockBadge = document.getElementById('approve-sale-lock-badge');

    // Nguồn "tự tạo" — sale tiếp đón chính bị fix cứng = creator, chỉ được thêm hỗ trợ.
    var SELF_OWNED = ['sa', 'ba', 'mkt_br'];

    function loadSales(coSoId){
        return fetch('/api/sales-in-cosolow?co_so_id=' + coSoId + '&_=' + Date.now(),
                     {headers:{Accept:'application/json'}, cache:'no-store'})
            .then(r => r.ok ? r.json() : {data:[]})
            .then(j => j.data || []).catch(() => []);
    }
    function optionFor(u){
        var opt = document.createElement('option');
        var label = u.name + (u.chuc_danh ? ' — ' + u.chuc_danh : '');
        if (u.bucket) label += ' · UPS ' + u.bucket + (u.busy ? ' (đang bận)' : ' (rảnh)');
        opt.value = u.id; opt.textContent = label;
        return opt;
    }
    function fillOptions(select, list, placeholder, currentId){
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        (list || []).forEach(function(u){
            var opt = optionFor(u);
            if (currentId && Number(currentId) === Number(u.id)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    htToggle.addEventListener('change', function(){
        htWrap.classList.toggle('hidden', ! htToggle.checked);
        if (! htToggle.checked) selHT.value = '';
    });

    /**
     * openApprove(id, name, coSoId, gStart, gEnd, tiepDonId, ghiChu, opts)
     *   opts = { source_group, creator_id, creator_name } — optional, dùng cho self-owned lock.
     */
    window.openApprove = function(id, name, coSoId, gStart, gEnd, tiepDonId, ghiChu, opts){
        opts = opts || {};
        f.action = base + id;
        document.getElementById('approve-name').textContent = name || 'khách';
        f.gio_thuc_hien.value = gStart || '';
        f.gio_ket_thuc.value = gEnd || '';
        f.ghi_chu.value = ghiChu || '';
        htToggle.checked = false; htWrap.classList.add('hidden'); selHT.value = '';

        var srcLower = (opts.source_group || '').toLowerCase();
        var isSelfOwned = SELF_OWNED.indexOf(srcLower) !== -1 && opts.creator_id;

        // Show/hide source info banner
        if (isSelfOwned) {
            srcInfo.classList.remove('hidden');
            srcBadge.textContent = srcLower.toUpperCase().replace('_', ' ');
            creatorName.textContent = opts.creator_name || '#' + opts.creator_id;
            lockBadge.classList.remove('hidden');
        } else {
            srcInfo.classList.add('hidden');
            lockBadge.classList.add('hidden');
        }

        fillOptions(sel, [], '— chọn sale phụ trách —', tiepDonId);
        fillOptions(selHT, [], '— chọn sale hỗ trợ —', null);

        loadSales(coSoId).then(function(list){
            // Nếu self-owned: đảm bảo creator có trong list dù không check-in UPS (fix cứng cho sale gốc).
            if (isSelfOwned && ! list.some(function(u){ return Number(u.id) === Number(opts.creator_id); })) {
                list.unshift({id: opts.creator_id, name: opts.creator_name || ('#' + opts.creator_id), chuc_danh: 'Sale gốc', bucket: null, busy: false});
            }
            fillOptions(sel, list, '— chọn sale phụ trách —', isSelfOwned ? opts.creator_id : tiepDonId);
            fillOptions(selHT, list, '— chọn sale hỗ trợ —', null);

            // Với self-owned: khoá dropdown chính = creator (readonly). Admin chỉ đổi dropdown hỗ trợ.
            if (isSelfOwned) {
                sel.value = opts.creator_id;
                sel.setAttribute('readonly', 'readonly');
                // <select> không hỗ trợ readonly, dùng disabled + hidden input để submit vẫn có giá trị.
                sel.style.pointerEvents = 'none';
                sel.style.background = '#fef3c7';
                ensureHiddenTiepDon(opts.creator_id);
            } else {
                sel.removeAttribute('readonly');
                sel.style.pointerEvents = '';
                sel.style.background = '';
                removeHiddenTiepDon();
            }
        });

        m.classList.remove('hidden'); m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };
    function ensureHiddenTiepDon(id){
        removeHiddenTiepDon();
        var h = document.createElement('input');
        h.type = 'hidden'; h.name = 'tiep_don_user_id'; h.value = id;
        h.id = '__hidden_tiep_don';
        f.appendChild(h);
        sel.name = 'tiep_don_user_id_display'; // tách khỏi submit
    }
    function removeHiddenTiepDon(){
        var h = document.getElementById('__hidden_tiep_don');
        if (h) h.remove();
        sel.name = 'tiep_don_user_id';
    }
    window.closeApprove = function(){
        m.classList.add('hidden'); m.classList.remove('flex');
        document.body.style.overflow = '';
    };
    m.addEventListener('click', function(e){ if(e.target === this) closeApprove(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeApprove(); });
})();
</script>
