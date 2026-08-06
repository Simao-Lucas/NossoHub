<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(): View
    {
        return view('events.index', [
            'events' => $this->events->timeline(),
        ]);
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = $this->events->create($request->validated());

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Evento criado com sucesso.');
    }

    public function show(Event $event): View
    {
        $presentation = $this->events->present($this->events->find($event->id));

        return view('events.show', $presentation);
    }

    public function edit(Event $event): View
    {
        return view('events.edit', [
            'event' => $this->events->find($event->id),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->events->update($event, $request->validated());

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->events->delete($event);

        return redirect()
            ->route('timeline')
            ->with('success', 'Evento removido.');
    }
}
