@extends('longevity.settings.layout')

@section('title', 'Kết nối SCRM')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-2">Kết nối SCRM</h1>
    <p class="text-sm text-gray-600 mb-6">
        Cấu hình host được phép nhận callback (redirect trở lại SCRM sau khi đặt lịch). Chỉ những host trong danh sách này mới được chấp nhận trong tham số <code>return_url</code>. Ngăn open-redirect sang site ngoài.
    </p>

    @if (session('ok'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded">{{ session('ok') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.scrm-connection.update', request()->route('co_so')) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">Host được phép callback (mỗi dòng 1 host)</label>
            <textarea name="hosts" rows="6" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono"
                      placeholder="lara-scrm.test&#10;crm.longevity.com.vn&#10;localhost:8000">{{ old('hosts', $hosts) }}</textarea>
            @error('hosts')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-500 mt-1">
                Không cần scheme (http/https). Có thể ghi kèm port (VD <code>localhost:8000</code>). Bỏ trống → dùng biến env <code>SCRM_CALLBACK_HOSTS</code>.
            </p>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md">Lưu</button>
    </form>
</div>
@endsection
