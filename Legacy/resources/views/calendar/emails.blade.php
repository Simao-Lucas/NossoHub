@extends('layouts.app')

@section('title', 'E-mails do calendário')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                E-mails
            </h1>
            <p class="mx-auto mt-4 max-w-md text-sm text-[var(--color-muted)]">
                Contas do Google Calendar que recebem os compromissos ao enviar.
            </p>
        </div>

        <div class="animate-fade-up mt-12 w-full" style="animation-delay: 120ms">
            <livewire:calendar.calendar-emails />
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('calendar.index') }}" class="nh-btn-ghost">Calendário</a>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
