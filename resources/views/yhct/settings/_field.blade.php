@php
    $type = $f['type'] ?? 'text';
    $ic = 'w-full px-3 py-2 bg-surface border border-outline rounded-lg text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary/20 outline-none transition-all';
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
<option value="{{ $hv }}" @selected($value === $hv)>{{ $hv }}</option>
@endfor
</select>
@elseif ($type === 'number')
<input type="number" name="{{ $name }}" value="{{ $value }}" min="1" max="99" class="{{ $ic }}"/>
@elseif ($type === 'password')
<input type="password" name="{{ $name }}" value="" autocomplete="new-password" placeholder="{{ $f['placeholder'] ?? '••••••' }}" class="{{ $ic }}"/>
@else
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $ic }}"/>
@endif
