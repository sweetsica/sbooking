<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\CoSo;
use Illuminate\Http\Request;

class ScrmConnectionController extends Controller
{
    public function edit(CoSo $co_so)
    {
        return view('longevity.settings.scrm-connection', [
            'coSo' => $co_so,
            'hosts' => AppSetting::get('scrm_callback_hosts', implode("\n", (array) config('services.scrm.callback_hosts', []))),
        ]);
    }

    public function update(CoSo $co_so, Request $request)
    {
        $data = $request->validate([
            'hosts' => ['nullable', 'string', 'max:2000'],
        ]);

        // Chuẩn hoá: mỗi dòng 1 host, bỏ dòng trống, lowercase.
        $lines = array_values(array_filter(array_map(fn ($l) => strtolower(trim($l)), preg_split('/[\r\n]+/', (string) ($data['hosts'] ?? ''))), fn ($l) => $l !== ''));
        AppSetting::set('scrm_callback_hosts', implode("\n", $lines));

        return back()->with('ok', 'Đã lưu whitelist host callback (' . count($lines) . ' host).');
    }
}
