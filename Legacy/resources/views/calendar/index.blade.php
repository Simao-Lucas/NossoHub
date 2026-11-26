@extends('layouts.app')

@section('title', 'Nosso Calendário')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-5xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                Nosso Calendário
            </h1>
        </div>

        <div class="animate-fade-up mt-8 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 80ms">
            <a href="{{ route('calendar.create') }}" class="nh-btn-primary">Novo compromisso</a>
            <a href="{{ route('calendar.emails') }}" class="nh-btn-ghost">Gerenciar e-mails</a>
        </div>

        <div class="animate-fade-up mt-10 w-full" style="animation-delay: 120ms">
            <livewire:calendar.calendar-month />
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
