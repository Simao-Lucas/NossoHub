@extends('layouts.app')

@section('title', 'Nossa Galeria')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-6xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                Nossa Galeria
            </h1>
        </div>

        <div class="animate-fade-up mt-10 w-full" style="animation-delay: 120ms">
            <livewire:gallery.gallery-index />
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
