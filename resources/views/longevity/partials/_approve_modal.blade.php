{{-- B5c (2026-08-14): modal duyệt lịch với edit sale/giờ/note.
     2026-08-18: hiển thị source info + owner (creator) + option "Thêm sale hỗ trợ".
     Nguồn SA/BA/MKT_BR: field Sale tiếp đón bị lock (fix = creator), admin chỉ được thêm sale hỗ trợ.
     2026-08-18 (rev): hiện info nguồn/người tạo/tele cho MỌI source (banner khác màu theo SELF_OWNED vs còn lại).
     Booking mai/kia: loadSales bỏ qua UPS, trả all sale cơ sở (Admin chọn tay). --}}
<div id="approve-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
        <form id="approve-form" method="POST" action="">
            @csrf @method('PATCH')
            <div class="p-5 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <h3 class="text-headline-md font-headline-md text-on-surface">Duyệt lịch hẹn</h3>
            </div>
            <div class="p-5 space-y-3">
                <p class="text-body-sm text-on-surface-variant">Lịch của khách hàng: <span id="approve-name" class="font-semibold text-on-surface"></span>.</p>

                {{-- 2026-08-18 — Info nguồn / người tạo / tele phụ trách.
                     Amber (SELF_OWNED = SA/BA/MKT_BR): sale gốc fix cứng, admin chỉ thêm hỗ trợ.
                     Blue (còn lại — MKT/BDM/BOD/Walk-in): tele ≠ tiếp đón, admin chọn tay Sale tiếp đón. --}}
                <div id="approve-source-info" class="hidden p-3 rounded-lg border text-body-sm">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span id="approve-source-icon" class="material-symbols-outlined text-[18px]">info</span>
                        <span id="approve-source-title" class="font-bold"></span>
                        <span id="approve-source-badge" class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase"></span>
                    </div>
                    <div id="approve-creator-row" class="hidden"><span class="text-on-surface-variant">Người nhập lead:</span> <b id="approve-creator-name">—</b></div>
                    <div id="approve-tele-row" class="hidden"><span class="text-on-surface-variant">Tele phụ trách:</span> <b id="approve-tele-name">—</b></div>
                    <div id="approve-source-note" class="text-[11px] opacity-80 mt-1"></div>
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

                {{-- 2026-09-04: Admin duyệt có thể sửa Bác sĩ + Hỗ trợ y tế (danh mục bac_si). --}}
                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-outline-variant/50">
                    <div>
                        <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Bác sĩ chính</label>
                        <select name="bac_si_id" id="approve-bs-select"
                                class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                            <option value="">— giữ nguyên —</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label-caps font-label-caps text-on-surface-variant block mb-1">Nhân sự hỗ trợ</label>
                        <select name="ho_tro_id" id="approve-ho-tro-yte-select"
                                class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                            <option value="">— không có —</option>
                        </select>
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
    var srcIcon = document.getElementById('approve-source-icon');
    var srcTitle = document.getElementById('approve-source-title');
    var srcBadge = document.getElementById('approve-source-badge');
    var srcNote = document.getElementById('approve-source-note');
    var creatorRow = document.getElementById('approve-creator-row');
    var creatorName = document.getElementById('approve-creator-name');
    var teleRow = document.getElementById('approve-tele-row');
    var teleName = document.getElementById('approve-tele-name');
    var lockBadge = document.getElementById('approve-sale-lock-badge');
    // 2026-09-04: BS + Hỗ trợ y tế (danh mục bac_si).
    var selBs = document.getElementById('approve-bs-select');
    var selHtYte = document.getElementById('approve-ho-tro-yte-select');
    function fillBacSi(select, list, placeholder, currentId, excludeId){
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        (list || []).forEach(function(bs){
            if (excludeId && Number(excludeId) === Number(bs.id)) return;
            var opt = document.createElement('option');
            var label = (bs.chuc_danh ? bs.chuc_danh + ' ' : '') + bs.ten;
            opt.value = bs.id; opt.textContent = label;
            if (currentId && Number(currentId) === Number(bs.id)) opt.selected = true;
            select.appendChild(opt);
        });
    }
    function loadBacSiYte(coSoId){
        return fetch('/api/bac-si-in-coso?co_so_id=' + coSoId + '&_=' + Date.now(),
            {headers:{Accept:'application/json'}, cache:'no-store'})
            .then(r => r.ok ? r.json() : {data:[]})
            .then(j => j.data || []).catch(() => []);
    }
    if (selBs) selBs.addEventListener('change', function(){
        // Đổi BS chính → xóa Hỗ trợ nếu trùng.
        if (selHtYte.value && selHtYte.value === selBs.value) selHtYte.value = '';
        // Re-render selHtYte để hide option trùng.
        var currentHt = selHtYte.value;
        var list = Array.from(selBs.options).slice(1).map(o => ({id: o.value, ten: o.textContent, chuc_danh: ''}));
        fillBacSi(selHtYte, list, '— không có —', currentHt, selBs.value);
    });

    // Nguồn "tự tạo" — sale tiếp đón chính bị fix cứng = creator, chỉ được thêm hỗ trợ.
    var SELF_OWNED = ['sa', 'ba', 'mkt_br'];

    // Palette 2 tone: amber (self-owned lock) vs blue (còn lại, admin chọn tay).
    var THEME_AMBER = 'border-amber-300 bg-amber-50 text-amber-900';
    var THEME_BLUE  = 'border-sky-300 bg-sky-50 text-sky-900';

    // 2026-09-04 (Phase 6.26.d): trả cả object (source + fallback_reason) để cảnh báo UPS chưa chốt.
    function loadSales(coSoId, ngayDat){
        var url = '/api/sales-in-cosolow?co_so_id=' + coSoId + '&_=' + Date.now();
        if (ngayDat) url += '&ngay_dat=' + encodeURIComponent(ngayDat);
        return fetch(url, {headers:{Accept:'application/json'}, cache:'no-store'})
            .then(r => r.ok ? r.json() : {data:[], source:'error'})
            .catch(() => ({data:[], source:'error'}));
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
     *   opts = { source_group, creator_id, creator_name, tele_owner_name, ngay_dat }
     *     - source_group ∈ SELF_OWNED (SA/BA/MKT_BR) → sale tiếp đón lock cứng = creator, admin chỉ thêm hỗ trợ.
     *     - source_group còn lại (MKT/BDM/BOD/Walk-in) → admin chọn tay Sale tiếp đón + hỗ trợ.
     *     - ngay_dat = ngày booking. Nếu > hôm nay → sales-in-cosolow trả all sale cơ sở (bỏ UPS).
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
        // 2026-08-19: self-owned = nhận diện theo SOURCE thôi (không đòi creator_id).
        //   Booking cũ CRM chưa push nguoi_tao_id → vẫn hiện banner "Lead tự tạo".
        //   Fallback creator DISPLAY = tele (MKT_BR/SA/BA: cùng 1 người).
        var isSelfOwned = SELF_OWNED.indexOf(srcLower) !== -1;
        if (isSelfOwned && ! opts.creator_name && opts.tele_owner_name) {
            opts.creator_name = opts.tele_owner_name;
        }
        // Lock dropdown chỉ khi có sbooking-side user id hợp lệ (nguoi_tao_id hoặc tiep_don_user_id).
        // Nếu cả 2 đều null (booking cũ mapping hụt) → hiện banner nhưng để admin chọn tay.
        var lockToSbookingId = null;
        if (isSelfOwned) {
            if (opts.creator_id) lockToSbookingId = opts.creator_id;
            else if (tiepDonId)  lockToSbookingId = tiepDonId;
        }
        var hasCreator = !! opts.creator_name || !! opts.creator_id;
        var hasTele    = !! opts.tele_owner_name;

        // 2026-08-19 — luôn hiện banner (3 field: Nguồn / Người nhập lead / Tele phụ trách),
        // thiếu data thì fallback "—" để booking cũ vẫn thấy đủ khung thông tin.
        srcInfo.classList.remove('hidden');
        srcInfo.className = 'p-3 rounded-lg border text-body-sm ' + (isSelfOwned ? THEME_AMBER : THEME_BLUE);
        srcBadge.className = 'ml-1 px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase ' +
            (isSelfOwned ? 'bg-amber-200 text-amber-900' : 'bg-sky-200 text-sky-900');
        srcBadge.textContent = srcLower ? srcLower.toUpperCase().replace('_', ' ') : '—';

        if (isSelfOwned) {
            srcIcon.textContent = 'verified';
            srcTitle.textContent = 'Lead tự tạo — sale gốc = tiếp đón';
            srcNote.textContent = lockToSbookingId
                ? 'Sale gốc được fix cứng để bảo vệ quyền sở hữu. Admin có thể thêm Sale hỗ trợ đón kèm khi bận.'
                : 'Booking cũ thiếu mapping sale — Admin chọn tay Sale tiếp đón. Booking mới sẽ tự lock.';
        } else {
            srcIcon.textContent = 'info';
            srcTitle.textContent = srcLower ? 'Lead do team chia' : 'Thông tin nguồn';
            srcNote.textContent = srcLower
                ? 'Tele phụ trách khác với Sale tiếp đón. Admin chọn tay Sale tiếp đón theo lịch cơ sở.'
                : 'Booking chưa có thông tin nguồn (dữ liệu cũ). Admin chọn tay Sale tiếp đón.';
        }

        creatorRow.classList.remove('hidden');
        creatorName.textContent = opts.creator_name || (opts.creator_id ? '#' + opts.creator_id : '—');
        teleRow.classList.remove('hidden');
        teleName.textContent = opts.tele_owner_name || '—';

        lockBadge.classList.toggle('hidden', ! lockToSbookingId);

        fillOptions(sel, [], '— chọn sale phụ trách —', tiepDonId);
        fillOptions(selHT, [], '— chọn sale hỗ trợ —', null);

        // 2026-09-04: load BS + Ho_tro y tế cho cơ sở.
        if (selBs) {
            fillBacSi(selBs, [], '— giữ nguyên —', null);
            fillBacSi(selHtYte, [], '— không có —', null);
            loadBacSiYte(coSoId).then(function(bsList){
                fillBacSi(selBs, bsList, '— giữ nguyên —', opts.bac_si_id);
                fillBacSi(selHtYte, bsList, '— không có —', opts.ho_tro_id, opts.bac_si_id);
            });
        }

        loadSales(coSoId, opts.ngay_dat).then(function(resp){
            var list = (resp && resp.data) || [];
            var respSource = resp && resp.source;
            // 2026-08-19: self-owned mà thiếu id (booking cũ) → tra theo TÊN creator/tele trong list sale
            //   để vẫn lock được. Match không phân biệt hoa thường và khoảng trắng thừa.
            if (isSelfOwned && ! lockToSbookingId) {
                var wantedName = (opts.creator_name || opts.tele_owner_name || '').trim().toLowerCase().replace(/\s+/g, ' ');
                if (wantedName) {
                    var hit = (list || []).find(function(u){
                        return (u.name || '').trim().toLowerCase().replace(/\s+/g, ' ') === wantedName;
                    });
                    if (hit) lockToSbookingId = hit.id;
                }
            }

            // Đảm bảo option lock xuất hiện dù không check-in UPS.
            if (lockToSbookingId && ! list.some(function(u){ return Number(u.id) === Number(lockToSbookingId); })) {
                list.unshift({id: lockToSbookingId, name: opts.creator_name || opts.tele_owner_name || ('#' + lockToSbookingId), chuc_danh: 'Sale gốc', bucket: null, busy: false});
            }
            fillOptions(sel, list, '— chọn sale phụ trách —', lockToSbookingId || tiepDonId);
            fillOptions(selHT, list, '— chọn sale hỗ trợ —', null);

            if (lockToSbookingId) {
                sel.value = lockToSbookingId;
                sel.setAttribute('readonly', 'readonly');
                sel.style.pointerEvents = 'none';
                sel.style.background = '#fef3c7';
                ensureHiddenTiepDon(lockToSbookingId);
                lockBadge.classList.remove('hidden');
                // Cập nhật lại note (đã set trước khi tra tên).
                if (isSelfOwned) srcNote.textContent = 'Sale gốc được fix cứng để bảo vệ quyền sở hữu. Admin có thể thêm Sale hỗ trợ đón kèm khi bận.';
            } else {
                sel.removeAttribute('readonly');
                sel.style.pointerEvents = '';
                sel.style.background = '';
                removeHiddenTiepDon();

                // Phase 6.26.d (2026-09-04): nguồn UPS-based (MKT) — auto-gợi ý sale bucket A rảnh
                // hoặc cảnh báo "Chưa chốt UPS list" nếu response không phải source='ups'.
                var isUpsSource = (srcLower === 'mkt');
                if (isUpsSource && !tiepDonId) {
                    if (respSource === 'ups') {
                        // Ưu tiên bucket A rảnh, fallback A bận, fallback B/C rảnh, fallback bất cứ.
                        var pick = list.find(function(u){ return u.bucket === 'A' && !u.busy; })
                                || list.find(function(u){ return u.bucket === 'A'; })
                                || list.find(function(u){ return (u.bucket === 'B' || u.bucket === 'C') && !u.busy; })
                                || list.find(function(u){ return !!u.bucket; });
                        if (pick) {
                            sel.value = pick.id;
                            srcNote.textContent = '💡 Gợi ý UPS: ' + pick.name + (pick.bucket ? ' (bucket ' + pick.bucket + (pick.busy ? ' · đang bận' : ' · rảnh') + ')' : '') + '. Admin có thể đổi tay nếu cần.';
                            srcInfo.className = 'p-3 rounded-lg border text-body-sm border-sky-300 bg-sky-50 text-sky-900';
                        }
                    } else if (respSource === 'local' || respSource === 'error') {
                        // UPS chưa chốt hôm nay — cảnh báo, buộc admin chọn tay.
                        var dateLbl = (opts.ngay_dat || '').split('-').reverse().slice(0,2).join('/') || 'hôm nay';
                        srcNote.textContent = '⚠ Chưa chốt UPS list ngày ' + dateLbl + ' — vui lòng chọn tay Sale tiếp đón bên dưới.';
                        srcInfo.className = 'p-3 rounded-lg border text-body-sm border-amber-300 bg-amber-50 text-amber-900';
                        srcIcon.textContent = 'warning';
                    }
                    // future_all_users: booking tương lai, giữ note mặc định (admin chọn tay).
                }
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
