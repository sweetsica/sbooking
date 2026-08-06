@extends('longevity.settings.layout')

@section('title', 'Kết nối SCRM')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-2">Kết nối SCRM</h1>
    <p class="text-sm text-gray-600 mb-6">
        Cấu hình kết nối server-to-server giữa <b>lara-sbooking</b> và <b>lara-scrm</b>. Bao gồm token xác thực + whitelist host callback (chống open-redirect).
    </p>

    @if (session('ok'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('ok') }}</div>
    @endif

    {{-- 2026-08-07: form xóa token phải ĐỨNG NGOÀI form update (không nested form). Button trỏ vào bằng attribute form="scrm-clear-token-form". --}}
    <form id="scrm-clear-token-form" method="POST" action="{{ route('settings.scrm-connection.clear-token', $coSo) }}">@csrf</form>

    <form method="POST" action="{{ route('settings.scrm-connection.update', $coSo) }}" class="space-y-6">
        @csrf

        {{-- 2026-08-05: URL scrm — bắt buộc để sbooking push callback (duyệt/tu chối/check-in) về scrm. --}}
        <fieldset class="border border-gray-200 rounded-md p-4">
            <legend class="px-2 text-sm font-semibold text-gray-700">URL SCRM (nhận callback)</legend>
            <p class="text-xs text-gray-500 mb-3">
                Origin của lara-scrm (VD <code>http://lara-datasource.test:81</code> — dev; <code>https://crm.longevity.com.vn</code> — prod).
                Sbooking push callback tới <code>{scrm_url}/api/leads/{ma}/booking-event</code> khi có thay đổi (duyệt/xong/xoá/edit/comment).
                Bỏ trống → fallback env <code>CRM_URL</code>.
            </p>
            <input type="url" name="scrm_url" value="{{ old('scrm_url', $scrmUrl ?? '') }}"
                   placeholder="http://lara-datasource.test:81"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono">
            @error('scrm_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </fieldset>

        {{-- Token xác thực (Phase B 2026-08-01) --}}
        <fieldset class="border border-gray-200 rounded-md p-4">
            <legend class="px-2 text-sm font-semibold text-gray-700">Token API xác thực (server-to-server)</legend>
            <p class="text-xs text-gray-500 mb-3">
                Token được SCRM gửi kèm mỗi request vào <code>/api/*</code> (header <code>Authorization: Bearer {token}</code>). Lưu encrypt trong DB.
            </p>

            @if ($tokenSet)
                <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800 flex items-center gap-2">
                    <span>✅ Đang dùng token DB: <code>{{ $tokenMasked }}</code></span>
                    {{-- 2026-08-07: submit trực tiếp bằng formaction để không nested form (HTML invalid). --}}
                    <button type="submit"
                            form="scrm-clear-token-form"
                            onclick="return confirm('Xoá token khỏi DB? Middleware sẽ fallback về env SCRM_API_TOKEN.');"
                            class="ml-auto text-xs text-red-600 hover:text-red-800">[Xoá token DB]</button>
                </div>
            @elseif ($envTokenSet)
                <div class="mb-2 p-2 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800">
                    ⚠️ Đang dùng token từ <code>.env SCRM_API_TOKEN</code> (chưa lưu DB). Nhập bên dưới để chuyển sang DB.
                </div>
            @else
                <div class="mb-2 p-2 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                    ❌ Chưa có token (cả DB và env đều trống). API sẽ trả 401.
                </div>
            @endif

            <label class="block text-sm font-semibold mb-1">
                {{ $tokenSet ? 'Nhập token mới để thay thế' : 'Nhập token' }}
                <span class="text-gray-400 text-xs">(bỏ trống = giữ nguyên)</span>
            </label>
            <input type="password" name="scrm_api_token" autocomplete="new-password"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono"
                   placeholder="Ít nhất 16 ký tự">
            @error('scrm_api_token')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </fieldset>

        {{-- Whitelist callback hosts (đã có từ trước) --}}
        <fieldset class="border border-gray-200 rounded-md p-4">
            <legend class="px-2 text-sm font-semibold text-gray-700">Host được phép callback</legend>
            <p class="text-xs text-gray-500 mb-3">
                Mỗi dòng 1 host. Chỉ những host trong danh sách này mới được chấp nhận trong tham số <code>return_url</code> khi đặt lịch xong redirect về SCRM. Không cần scheme (http/https). Có thể ghi kèm port (VD <code>localhost:8000</code>). Bỏ trống → dùng biến env <code>SCRM_CALLBACK_HOSTS</code>.
            </p>
            <textarea name="hosts" rows="6" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono"
                      placeholder="lara-scrm.test&#10;crm.longevity.com.vn&#10;localhost:8000">{{ old('hosts', $hosts) }}</textarea>
            @error('hosts')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </fieldset>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md">Lưu cấu hình</button>
    </form>
</div>
@endsection
