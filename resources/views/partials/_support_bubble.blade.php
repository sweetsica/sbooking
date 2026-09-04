{{-- Bubble "?" hỗ trợ (bên sbooking) — chỉ hiện khi đã đăng nhập.
     2026-08-26: bỏ Alpine (dashboard không nạp → modal hụt reactivity). Vanilla JS.
     2026-09-04: gộp 2 icon (list + ?) thành 1 — click "?" mở popover có "Tạo ticket" + "Danh sách ticket". --}}
@php $u = auth()->user(); @endphp
<div class="fixed bottom-5 right-5 z-[9999]">
    <div class="relative">
        {{-- Popover 2 mục — mặc định ẩn, toggle bằng vanilla JS --}}
        <div id="support-menu" style="display:none"
             class="absolute bottom-16 right-0 min-w-[220px] bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
            <button type="button" onclick="supportOpenModal()"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 text-left">
                <span class="material-symbols-outlined text-[20px] text-slate-600">edit_note</span>
                <span class="text-sm text-slate-800">Tạo ticket hỗ trợ</span>
            </button>
            <a href="/ho-tro"
               class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 border-t border-slate-100">
                <span class="material-symbols-outlined text-[20px] text-slate-600">list</span>
                <span class="text-sm text-slate-800">Danh sách ticket</span>
            </a>
        </div>

        {{-- Nút "?" duy nhất — click toggle popover --}}
        <button type="button" onclick="supportToggleMenu()"
                title="Hỗ trợ"
                class="w-14 h-14 rounded-full bg-slate-900 hover:bg-slate-800 text-white shadow-lg flex items-center justify-center text-2xl font-bold transition-transform hover:scale-110">
            ?
        </button>
    </div>

    {{-- Modal tạo ticket — reuse như trước, chỉ khác trigger --}}
    <div id="support-modal" style="display:none"
         onclick="if(event.target===this) this.style.display='none'"
         class="fixed inset-0 z-[10000] bg-black/40 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold">Phản hồi / Yêu cầu hỗ trợ</h3>
                <button type="button" onclick="document.getElementById('support-modal').style.display='none'"
                        class="text-slate-400 hover:text-slate-700 text-2xl leading-none">×</button>
            </div>
            <form method="POST" action="/ho-tro" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Họ tên <span class="text-red-600">*</span></label>
                    <input type="text" name="name" required value="{{ $u?->name }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Cơ sở</label>
                    <input type="text" name="co_so" placeholder="HN / HCM / DN..."
                           value="{{ optional($u?->coSo)->ten }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Thông tin liên hệ</label>
                    <input type="text" name="contact" placeholder="Email hoặc SĐT" value="{{ $u?->email }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Mô tả vấn đề <span class="text-red-600">*</span></label>
                    <textarea name="description" rows="5" required minlength="5"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('support-modal').style.display='none'"
                            class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-50">Hủy</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold">Gửi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function supportToggleMenu() {
        const m = document.getElementById('support-menu');
        m.style.display = m.style.display === 'none' ? 'block' : 'none';
    }
    function supportOpenModal() {
        document.getElementById('support-menu').style.display = 'none';
        document.getElementById('support-modal').style.display = 'flex';
    }
    // Click ngoài popover → đóng.
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('support-menu');
        const bubble = e.target.closest('.fixed.bottom-5.right-5');
        if (!bubble && menu.style.display !== 'none') menu.style.display = 'none';
    });
</script>
