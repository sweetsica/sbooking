{{-- Bubble "?" gửi ticket hỗ trợ (bên sbooking). Chỉ hiện khi đã đăng nhập. --}}
@php $u = auth()->user(); @endphp
{{-- 2026-08-26 fix: dashboard.blade.php không nạp Alpine → x-show/x-cloak không chạy, popup luôn hiện.
     Nạp Alpine + CSS x-cloak ở đây, guard trùng bằng cờ window để layout khác đã có Alpine không nạp lại. --}}
@once
<style>[x-cloak]{display:none!important}</style>
<script>
    if (!window.__alpine_loaded__) {
        window.__alpine_loaded__ = true;
        var s = document.createElement('script');
        s.defer = true;
        s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
        document.head.appendChild(s);
    }
</script>
@endonce
<div x-data="{ open: false }" class="fixed bottom-5 right-5 z-[9999]">
    <div class="flex items-center gap-2">
        <a href="/ho-tro" title="Danh sách ticket"
           class="w-10 h-10 rounded-full bg-white border border-slate-300 text-slate-700 shadow flex items-center justify-center hover:bg-slate-50">
            <span class="material-symbols-outlined text-[20px]">list</span>
        </a>
        <button type="button" @click="open = true"
                title="Gửi phản hồi / yêu cầu hỗ trợ"
                class="w-14 h-14 rounded-full bg-slate-900 hover:bg-slate-800 text-white shadow-lg flex items-center justify-center text-2xl font-bold transition-transform hover:scale-110">
            ?
        </button>
    </div>

    <div x-show="open" x-cloak @click.self="open = false"
         class="fixed inset-0 z-[10000] bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold">Phản hồi / Yêu cầu hỗ trợ</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">×</button>
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
                    <button type="button" @click="open = false"
                            class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-50">Hủy</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold">Gửi</button>
                </div>
            </form>
        </div>
    </div>
</div>
