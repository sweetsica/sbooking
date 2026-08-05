<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\CoSo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ScrmConnectionController extends Controller
{
    public function edit(CoSo $co_so)
    {
        // Phase B integration 2026-08-01: hiển thị token dạng masked (không lộ giá trị thật).
        $tokenEnc = AppSetting::get('scrm_api_token');
        $tokenSet = ! empty($tokenEnc);
        $tokenMasked = $tokenSet ? '••••••••••••' . substr($this->tryDecrypt($tokenEnc) ?? '', -4) : '';

        return view('longevity.settings.scrm-connection', [
            'coSo' => $co_so,
            'hosts' => AppSetting::get('scrm_callback_hosts', implode("\n", (array) config('services.scrm.callback_hosts', []))),
            'scrmUrl' => AppSetting::get('scrm_url', (string) (config('services.crm.url') ?? env('CRM_URL', ''))),
            'tokenSet' => $tokenSet,
            'tokenMasked' => $tokenMasked,
            'envTokenSet' => ! empty(env('SCRM_API_TOKEN')),
        ]);
    }

    public function update(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'hosts' => ['nullable', 'string', 'max:2000'],
            // 2026-08-05: thêm URL scrm — sbooking cần biết push callback tới đâu (trước phụ thuộc env CRM_URL).
            'scrm_url' => ['nullable', 'url', 'max:255'],
            'scrm_api_token' => ['nullable', 'string', 'min:16', 'max:255'],
        ], [
            'scrm_api_token.min' => 'Token phải ít nhất 16 ký tự.',
            'scrm_url.url' => 'URL SCRM phải hợp lệ (VD http://lara-datasource.test:81).',
        ]);

        // Chuẩn hoá host: mỗi dòng 1 host, bỏ dòng trống, lowercase.
        $lines = array_values(array_filter(array_map(fn ($l) => strtolower(trim($l)), preg_split('/[\r\n]+/', (string) ($data['hosts'] ?? ''))), fn ($l) => $l !== ''));
        AppSetting::set('scrm_callback_hosts', implode("\n", $lines));

        if (array_key_exists('scrm_url', $data)) {
            AppSetting::set('scrm_url', rtrim((string) $data['scrm_url'], '/'));
        }

        // Token: chỉ ghi khi user nhập giá trị mới (bỏ trống = giữ nguyên); encrypt trước khi lưu.
        if (! empty($data['scrm_api_token'])) {
            AppSetting::set('scrm_api_token', Crypt::encryptString($data['scrm_api_token']));
        }

        return back()->with('ok', 'Đã lưu cấu hình SCRM.');
    }

    public function clearToken(CoSo $co_so)
    {
        AppSetting::set('scrm_api_token', null);
        return back()->with('ok', 'Đã xoá token DB. Middleware sẽ fallback về env SCRM_API_TOKEN.');
    }

    private function tryDecrypt(?string $enc): ?string
    {
        if (! $enc) return null;
        try {
            return Crypt::decryptString($enc);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
