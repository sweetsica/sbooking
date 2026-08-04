@extends('longevity.settings.layout')
@section('title', 'Sơ đồ tổ chức')

@section('content')
<div class="flex items-center gap-3 mb-6">
<div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-on-secondary-container flex items-center justify-center">
<span class="material-symbols-outlined">account_tree</span>
</div>
<div>
<h2 class="text-headline-lg font-headline-lg">Sơ đồ tổ chức</h2>
<p class="text-body-sm text-on-surface-variant">Cấu trúc phòng ban, đội nhóm và nhân sự theo cơ sở.</p>
</div>
</div>

<div x-data="{ openNodes: {} }" class="space-y-4">

{{-- Ban điều hành (global users, không thuộc cơ sở nào) --}}
@if($global->isNotEmpty())
<div class="rounded-xl border border-outline-variant bg-surface-container-lowest overflow-hidden">
<button @click="openNodes['global'] = !openNodes['global']"
    class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant transition-transform duration-200"
    :class="openNodes['global'] ? 'rotate-90' : ''">chevron_right</span>
<span class="material-symbols-outlined text-[22px] text-secondary">corporate_fare</span>
<span class="font-headline-md text-headline-md">Ban điều hành</span>
<span class="ml-auto text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">{{ $global->count() }} người</span>
</button>
<div x-show="openNodes['global']">
<div class="border-t border-outline-variant">
@foreach($global as $u)
<div class="flex items-center gap-3 px-5 py-3 {{ !$loop->last ? 'border-b border-outline-variant/50' : '' }} hover:bg-surface-container-low/50 transition-colors">
<div class="w-8 h-8 rounded-full flex items-center justify-center text-body-sm font-semibold shrink-0
    {{ $u->is_admin ? 'bg-error-container text-on-error-container' : 'bg-secondary-container/50 text-on-secondary-container' }}">
    {{ mb_substr($u->name, 0, 1) }}
</div>
<div class="min-w-0 flex-1">
<div class="flex items-center gap-2">
<span class="text-body-md font-semibold truncate">{{ $u->name }}</span>
@if($u->chuc_danh)<span class="text-label-caps font-label-caps text-on-surface-variant">· {{ $u->chuc_danh }}</span>@endif
</div>
<div class="text-body-sm text-on-surface-variant flex items-center gap-2">
<span>{{ $u->username }}</span>
<span>·</span>
<span>{{ $vaiTros[$u->vai_tro_id] ?? '—' }}</span>
</div>
</div>
@if($u->is_admin)
<span class="text-label-caps font-label-caps text-error px-2 py-0.5 rounded bg-error-container/50">ADMIN</span>
@endif
</div>
@endforeach
</div>
</div>
</div>
@endif

{{-- Từng cơ sở --}}
@foreach($tree as $node)
@php $cs = $node['coSo']; $phongBans = $node['phongBans']; $unassigned = $node['unassigned']; @endphp
<div class="rounded-xl border border-outline-variant bg-surface-container-lowest overflow-hidden">
{{-- Header cơ sở --}}
<button @click="openNodes['cs{{ $cs->id }}'] = !openNodes['cs{{ $cs->id }}']"
    class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px] text-on-surface-variant transition-transform duration-200"
    :class="openNodes['cs{{ $cs->id }}'] ? 'rotate-90' : ''">chevron_right</span>
<span class="material-symbols-outlined text-[22px] text-secondary">location_city</span>
<span class="font-headline-md text-headline-md">{{ $cs->ten }}</span>
@php $totalUsers = $phongBans->sum(fn($pb) => $pb->nguoiDungs->count()) + $unassigned->count(); @endphp
<span class="ml-auto text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">{{ $totalUsers }} người · {{ $phongBans->count() }} đội</span>
</button>

<div x-show="openNodes['cs{{ $cs->id }}']">
<div class="border-t border-outline-variant">

{{-- Từng phòng ban / team --}}
@foreach($phongBans as $pb)
<div class="border-b border-outline-variant/50 last:border-b-0">
<button @click="openNodes['pb{{ $pb->id }}'] = !openNodes['pb{{ $pb->id }}']"
    class="w-full flex items-center gap-3 pl-10 pr-5 py-3 text-left hover:bg-surface-container-low/70 transition-colors">
<span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform duration-200"
    :class="openNodes['pb{{ $pb->id }}'] ? 'rotate-90' : ''">chevron_right</span>
<span class="material-symbols-outlined text-[20px] text-on-surface-variant">groups</span>
<span class="text-body-md font-semibold">{{ $pb->ten }}</span>
<span class="ml-auto text-label-caps font-label-caps text-on-surface-variant">{{ $pb->nguoiDungs->count() }}</span>
</button>
<div x-show="openNodes['pb{{ $pb->id }}']">
@foreach($pb->nguoiDungs as $u)
<div class="flex items-center gap-3 pl-20 pr-5 py-2.5 {{ !$loop->last ? 'border-b border-outline-variant/30' : '' }} hover:bg-surface-container-low/40 transition-colors">
<div class="w-7 h-7 rounded-full flex items-center justify-center text-[12px] font-semibold shrink-0
    @if(in_array($u->vai_tro_id, [3, 4])) bg-tertiary-container text-on-tertiary-container
    @else bg-surface-container-high text-on-surface-variant @endif">
    {{ mb_substr($u->name, 0, 1) }}
</div>
<div class="min-w-0 flex-1">
<div class="flex items-center gap-2">
<span class="text-body-md font-medium truncate">{{ $u->name }}</span>
@if($u->chuc_danh)<span class="text-body-sm text-on-surface-variant">· {{ $u->chuc_danh }}</span>@endif
</div>
<div class="text-body-sm text-on-surface-variant">{{ $u->username }} · {{ $vaiTros[$u->vai_tro_id] ?? '—' }}</div>
</div>
</div>
@endforeach
</div>
</div>
@endforeach

{{-- Nhân sự không thuộc phòng ban nào (tài khoản dùng chung) --}}
@if($unassigned->isNotEmpty())
<div class="border-b border-outline-variant/50 last:border-b-0">
<button @click="openNodes['ua{{ $cs->id }}'] = !openNodes['ua{{ $cs->id }}']"
    class="w-full flex items-center gap-3 pl-10 pr-5 py-3 text-left hover:bg-surface-container-low/70 transition-colors">
<span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform duration-200"
    :class="openNodes['ua{{ $cs->id }}'] ? 'rotate-90' : ''">chevron_right</span>
<span class="material-symbols-outlined text-[20px] text-on-surface-variant">person_pin</span>
<span class="text-body-md font-semibold text-on-surface-variant">Tài khoản dùng chung</span>
<span class="ml-auto text-label-caps font-label-caps text-on-surface-variant">{{ $unassigned->count() }}</span>
</button>
<div x-show="openNodes['ua{{ $cs->id }}']">
@foreach($unassigned as $u)
<div class="flex items-center gap-3 pl-20 pr-5 py-2.5 {{ !$loop->last ? 'border-b border-outline-variant/30' : '' }} hover:bg-surface-container-low/40 transition-colors">
<div class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center text-[12px] font-semibold shrink-0">
    {{ mb_substr($u->name, 0, 1) }}
</div>
<div class="min-w-0 flex-1">
<div class="flex items-center gap-2">
<span class="text-body-md font-medium truncate">{{ $u->name }}</span>
</div>
<div class="text-body-sm text-on-surface-variant">{{ $u->username }} · {{ $vaiTros[$u->vai_tro_id] ?? '—' }}</div>
</div>
</div>
@endforeach
</div>
</div>
@endif

</div>
</div>
</div>
@endforeach

</div>
@endsection
