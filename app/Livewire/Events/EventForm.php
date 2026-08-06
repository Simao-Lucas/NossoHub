<?php

namespace App\Livewire\Events;

use App\Models\Event;
use App\Services\EventService;
use Livewire\Component;
use Livewire\WithFileUploads;

class EventForm extends Component
{
    use WithFileUploads;

    public ?int $eventId = null;

    public string $title = '';

    public string $description = '';

    public string $occurred_at = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    /** @var list<array{id: int, type: string, url: string, original_name: ?string}> */
    public array $existingMedia = [];

    /** @var list<int> */
    public array $removedMediaIds = [];

    public function mount(?Event $event = null): void
    {
        if ($event?->exists) {
            $event = app(EventService::class)->find($event->id);
            $this->eventId = $event->id;
            $this->title = $event->title;
            $this->description = (string) $event->description;
            $this->occurred_at = $event->occurred_at->format('Y-m-d');
            $this->existingMedia = $event->media
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'type' => $m->type->value,
                    'url' => $m->url,
                    'original_name' => $m->original_name,
                ])
                ->values()
                ->all();
        } else {
            $this->occurred_at = now()->format('Y-m-d');
        }
    }

    public function removeExisting(int $id): void
    {
        $this->removedMediaIds[] = $id;
        $this->existingMedia = collect($this->existingMedia)
            ->reject(fn (array $item) => $item['id'] === $id)
            ->values()
            ->all();
    }

    public function removeUpload(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function save(EventService $events)
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'occurred_at' => ['required', 'date'],
            'uploads' => ['nullable', 'array', 'max:40'],
            'uploads.*' => ['file', 'max:102400', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm'],
        ], [
            'uploads.*.max' => 'Cada arquivo pode ter no máximo 100 MB.',
            'uploads.*.mimetypes' => 'Envie apenas imagens (JPEG, PNG, WebP, GIF) ou vídeos (MP4, MOV, WebM).',
        ]);

        $payload = [
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'occurred_at' => $this->occurred_at,
            'uploads' => $this->uploads,
            'removed_media_ids' => array_values(array_unique($this->removedMediaIds)),
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
        return view('livewire.events.form');
    }
}
