@extends('layouts.app')

@section('title', 'Novo compromisso')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                Novo compromisso
            </h1>
        </div>

        <div class="animate-fade-up mt-10 w-full" style="animation-delay: 120ms">
            <livewire:calendar.appointment-form />
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('calendar.index') }}" class="nh-btn-ghost">Calendário</a>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
