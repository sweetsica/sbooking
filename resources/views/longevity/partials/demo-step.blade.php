<div class="flex gap-3 rounded-xl border border-outline-variant bg-surface-container-lowest p-4">
  <div class="shrink-0 w-9 h-9 rounded-full bg-secondary text-on-secondary flex items-center justify-center font-bold font-[Manrope]">{{ $n }}</div>
  <div class="min-w-0">
    <div class="flex items-center gap-2 font-semibold text-body-md">
      <span class="material-symbols-outlined text-secondary text-[20px]">{{ $icon }}</span>{{ $title }}
    </div>
    <p class="text-body-sm text-on-surface-variant mt-1 leading-relaxed">{!! $body !!}</p>
  </div>
</div>
