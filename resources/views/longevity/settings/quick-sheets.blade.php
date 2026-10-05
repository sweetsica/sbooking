@extends('longevity.settings.layout')
@section('title', 'Quick Sheets')

@section('content')
    @livewireStyles
    <livewire:settings.quick-sheets :co-so-id="$coSo->id" />
    @livewireScripts
@endsection
