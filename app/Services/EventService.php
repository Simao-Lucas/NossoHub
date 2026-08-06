<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Event;
use App\Models\EventMedia;
use App\Repositories\EventRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventService
{
    public function __construct(
        private readonly EventRepository $events,
    ) {}

    public function timeline(bool $ascending = false): Collection
    {
        return $this->events->allChronological($ascending);
    }

    public function find(int $id): Event
    {
        return $this->events->find($id);
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     occurred_at: string,
     *     uploads?: list<UploadedFile>
     * }  $payload
     */
    public function create(array $payload): Event
    {
        return DB::transaction(function () use ($payload): Event {
            $uploads = $payload['uploads'] ?? [];
            unset($payload['uploads'], $payload['media']);

            $event = $this->events->create($payload);
            $this->storeUploads($event, $uploads);

            return $this->events->find($event->id);
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     occurred_at: string,
     *     uploads?: list<UploadedFile>,
     *     removed_media_ids?: list<int>
     * }  $payload
     */
    public function update(Event $event, array $payload): Event
    {
        return DB::transaction(function () use ($event, $payload): Event {
            $uploads = $payload['uploads'] ?? [];
            $removed = $payload['removed_media_ids'] ?? [];
            unset($payload['uploads'], $payload['removed_media_ids'], $payload['media']);

            $this->events->update($event, $payload);
            $this->deleteMediaByIds($event, $removed);
            $this->storeUploads($event, $uploads);

            return $this->events->find($event->id);
        });
    }

    public function delete(Event $event): void
    {
        DB::transaction(function () use ($event): void {
            $event = $this->events->find($event->id);

            foreach ($event->media as $media) {
                $this->deleteMediaFile($media);
            }

            Storage::disk('public')->deleteDirectory("events/{$event->id}");
            $this->events->delete($event);
        });
    }

    /**
     * @return array{event: Event, photos: list<array<string, mixed>>, videos: list<array<string, mixed>>}
     */
    public function present(Event $event): array
    {
        $photos = [];
        $videos = [];

        foreach ($event->media as $media) {
            $item = [
                'id' => $media->id,
                'type' => $media->type->value,
                'url' => $media->url,
                'original_name' => $media->original_name,
                'mime_type' => $media->mime_type,
            ];

            if ($media->type === MediaType::Video) {
                $videos[] = $item;
            } else {
                $photos[] = $item;
            }
        }

        return [
            'event' => $event,
            'photos' => $photos,
            'videos' => $videos,
        ];
    }

    /**
     * @param  list<UploadedFile>  $uploads
     */
    private function storeUploads(Event $event, array $uploads): void
    {
        $sort = $this->events->nextSortOrder($event);

        foreach ($uploads as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $mime = (string) $file->getMimeType();
            $type = str_starts_with($mime, 'video/')
                ? MediaType::Video->value
                : MediaType::Photo->value;

            $path = $file->store("events/{$event->id}", 'public');

            $this->events->addMedia($event, [
                'path' => $path,
                'disk' => 'public',
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'type' => $type,
                'sort_order' => $sort++,
            ]);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    private function deleteMediaByIds(Event $event, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        foreach ($this->events->mediaByIds($event, $ids) as $media) {
            $this->deleteMediaFile($media);
            $media->delete();
        }
    }

    private function deleteMediaFile(EventMedia $media): void
    {
        if ($media->path !== '') {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }
    }
}
