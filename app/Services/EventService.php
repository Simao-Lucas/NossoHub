<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function __construct(
        private readonly EventRepository $events,
        private readonly ImmichService $immich,
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
     *     location?: string|null,
     *     cover_immich_asset_id?: string|null,
     *     media?: list<array{immich_asset_id: string, type: string}>
     * }  $payload
     */
    public function create(array $payload): Event
    {
        return DB::transaction(function () use ($payload): Event {
            $media = $payload['media'] ?? [];
            unset($payload['media']);

            $event = $this->events->create($payload);
            $this->syncMedia($event, $media);

            return $this->events->find($event->id);
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     occurred_at: string,
     *     location?: string|null,
     *     cover_immich_asset_id?: string|null,
     *     media?: list<array{immich_asset_id: string, type: string}>
     * }  $payload
     */
    public function update(Event $event, array $payload): Event
    {
        return DB::transaction(function () use ($event, $payload): Event {
            $media = $payload['media'] ?? null;
            unset($payload['media']);

            $this->events->update($event, $payload);

            if (is_array($media)) {
                $this->syncMedia($event, $media);
            }

            return $this->events->find($event->id);
        });
    }

    public function delete(Event $event): void
    {
        $this->events->delete($event);
    }

    /**
     * Enrich event media with Immich metadata and thumbnail URLs.
     *
     * @return array{event: Event, photos: list<array<string, mixed>>, videos: list<array<string, mixed>>}
     */
    public function present(Event $event): array
    {
        $photos = [];
        $videos = [];

        foreach ($event->media as $media) {
            $asset = $this->immich->getAssetById($media->immich_asset_id);
            $item = [
                'id' => $media->id,
                'immich_asset_id' => $media->immich_asset_id,
                'type' => $media->type->value,
                'thumbnail_url' => $this->immich->appThumbnailUrl($media->immich_asset_id),
                'original_url' => $this->immich->appOriginalUrl($media->immich_asset_id),
                'asset' => $asset,
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
     * @param  list<array{immich_asset_id: string, type: string}>  $media
     */
    private function syncMedia(Event $event, array $media): void
    {
        $normalized = [];

        foreach ($media as $index => $item) {
            $assetId = trim((string) ($item['immich_asset_id'] ?? ''));
            $type = (string) ($item['type'] ?? MediaType::Photo->value);

            if ($assetId === '') {
                continue;
            }

            if (! in_array($type, [MediaType::Photo->value, MediaType::Video->value], true)) {
                throw ValidationException::withMessages([
                    'media' => "Tipo de mídia inválido: {$type}",
                ]);
            }

            $normalized[] = [
                'immich_asset_id' => $assetId,
                'type' => $type,
                'sort_order' => $index,
            ];
        }

        $this->events->syncMedia($event, $normalized);
    }
}
