@php
    $type = $f['type'] ?? 'text';
    $req = ! empty($f['required']);
    // Trường bắt buộc: viền đỏ nhạt + focus đỏ để nổi bật.
    $border = $req
        ? 'border-red-300 bg-red-50/40 focus:border-red-500 focus:ring-1 focus:ring-red-500/20'
        : 'border-outline bg-surface focus:border-secondary focus:ring-1 focus:ring-secondary/20';
    $ic = "w-full px-3 py-2 rounded-lg text-body-md outline-none transition-all {$border}";
@endphp
@if ($type === 'toggle')
<label class="inline-flex items-center gap-2 cursor-pointer">
<input type="hidden" name="{{ $name }}" value="0"/>
<input type="checkbox" name="{{ $name }}" value="1" @checked($value) class="w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"/>
<span class="text-body-sm text-on-surface-variant">{{ $f['label'] }}</span>
</label>
@elseif ($type === 'select')
<select name="{{ $name }}" class="{{ $ic }}">
@foreach ($f['options'] as $ov => $ol)
<option value="{{ $ov }}" @selected((string) $value === (string) $ov)>{{ $ol }}</option>
@endforeach
</select>
@elseif ($type === 'hour')
<select name="{{ $name }}" class="{{ $ic }} font-time-slot">
@for ($h = 6; $h <= 22; $h++)
@php $hv = sprintf('%02d:00', $h); @endphp
<option value="{{ $hv }}" @selected(substr($value ?? '', 0, 5) === $hv)>{{ $hv }}</option>
@endfor
</select>
@elseif ($type === 'number')
<input type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $f['min'] ?? 1 }}" max="{{ $f['max'] ?? 99 }}" class="{{ $ic }}"/>
@elseif ($type === 'multiselect')
@php $sel = collect(is_array($value) ? $value : (array) $value)->map(fn ($v) => (string) $v); @endphp
<div class="w-full flex flex-col gap-1 max-h-40 overflow-y-auto rounded-lg border border-outline bg-surface p-2">
@forelse ($f['options'] as $ov => $ol)
<label class="inline-flex items-center gap-2 cursor-pointer text-body-sm">
<input type="checkbox" name="{{ $name }}[]" value="{{ $ov }}" @checked($sel->contains((string) $ov)) class="w-4 h-4 rounded border-outline text-secondary focus:ring-secondary"/>
<span>{{ $ol }}</span>
</label>
@empty
<span class="text-[11px] text-on-surface-variant/70">Chưa có bác sĩ nào trong danh mục.</span>
@endforelse
</div>
@elseif ($type === 'password')
<input type="password" name="{{ $name }}" value="" autocomplete="new-password" placeholder="{{ $f['placeholder'] ?? '••••••' }}" class="{{ $ic }}"/>
@else
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $ic }}"/>
@endif
