<?php

namespace App\Livewire\Events;

use App\Enums\MediaType;
use App\Models\Event;
use App\Services\EventService;
use Livewire\Component;

class EventForm extends Component
{
    public ?int $eventId = null;

    public string $title = '';

    public string $description = '';

    public string $occurred_at = '';

    public string $location = '';

    /** @var list<array{immich_asset_id: string, type: string}> */
    public array $media = [];

    public string $newAssetId = '';

    public string $newAssetType = 'photo';

    public function mount(?Event $event = null): void
    {
        if ($event?->exists) {
            $event = app(EventService::class)->find($event->id);
            $this->eventId = $event->id;
            $this->title = $event->title;
            $this->description = (string) $event->description;
            $this->occurred_at = $event->occurred_at->format('Y-m-d');
            $this->location = (string) $event->location;
            $this->media = $event->media
                ->map(fn ($m) => [
                    'immich_asset_id' => $m->immich_asset_id,
                    'type' => $m->type->value,
                ])
                ->values()
                ->all();
        } else {
            $this->occurred_at = now()->format('Y-m-d');
        }
    }

    public function addMedia(): void
    {
        $assetId = trim($this->newAssetId);

        if ($assetId === '') {
            $this->addError('newAssetId', 'Informe o ID do asset Immich.');

            return;
        }

        foreach ($this->media as $item) {
            if ($item['immich_asset_id'] === $assetId) {
                $this->addError('newAssetId', 'Este asset já foi adicionado.');

                return;
            }
        }

        $this->media[] = [
            'immich_asset_id' => $assetId,
            'type' => $this->newAssetType,
        ];

        $this->newAssetId = '';
        $this->resetErrorBag('newAssetId');
    }

    public function removeMedia(int $index): void
    {
        unset($this->media[$index]);
        $this->media = array_values($this->media);
    }

    public function save(EventService $events)
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array'],
            'media.*.immich_asset_id' => ['required', 'string', 'max:64'],
            'media.*.type' => ['required', 'in:photo,video'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'occurred_at' => $validated['occurred_at'],
            'location' => $validated['location'] ?: null,
            'cover_immich_asset_id' => null,
            'media' => $validated['media'] ?? [],
        ];

        if ($this->eventId) {
            $event = $events->update($events->find($this->eventId), $payload);
            session()->flash('success', 'Evento atualizado com sucesso.');
        } else {
            $event = $events->create($payload);
            session()->flash('success', 'Evento criado com sucesso.');
        }

        return redirect()->route('events.show', $event);
    }

    public function render()
    {
        return view('livewire.events.form', [
            'mediaTypes' => MediaType::cases(),
        ]);
    }
}
