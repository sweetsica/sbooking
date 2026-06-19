@extends('longevity.settings.layout')
@section('title', 'Thiết lập')

@section('content')
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-secondary text-[28px]">settings</span>
<h2 class="text-headline-lg font-headline-lg">Thiết lập hệ thống</h2>
</div>
<p class="text-body-md text-on-surface-variant mb-8">Cấu hình phòng, nhân sự, dịch vụ, menu và phân quyền cho <strong>{{ $coSo->ten }}</strong>.</p>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach ($sections as $key => [$ten, $icon, $mota])
<a href="/{{ $coSo->slug }}/thiet-lap/{{ $key }}"
   class="group bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-lg hover:border-secondary/40 transition-all">
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center group-hover:scale-110 transition-transform shrink-0">
<span class="material-symbols-outlined">{{ $icon }}</span>
</div>
<div>
<h3 class="text-headline-md font-headline-md mb-1">{{ $ten }}</h3>
<p class="text-body-sm text-on-surface-variant">{{ $mota }}</p>
</div>
</div>
</a>
@endforeach
</div>
@endsection
