<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EventRepository
{
    public function allChronological(): Collection
    {
        return Event::query()
            ->withCount([
                'media as photos_count' => fn ($q) => $q->where('type', 'photo'),
                'media as videos_count' => fn ($q) => $q->where('type', 'video'),
            ])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): Event
    {
        return Event::query()
            ->with(['media'])
            ->withCount([
                'media as photos_count' => fn ($q) => $q->where('type', 'photo'),
                'media as videos_count' => fn ($q) => $q->where('type', 'video'),
            ])
            ->findOrFail($id);
    }

    public function create(array $data): Event
    {
        return Event::query()->create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->refresh();
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }

    /**
     * @param  list<array{immich_asset_id: string, type: string, sort_order?: int}>  $mediaItems
     */
    public function syncMedia(Event $event, array $mediaItems): void
    {
        DB::transaction(function () use ($event, $mediaItems): void {
            $event->media()->delete();

            foreach ($mediaItems as $index => $item) {
                $event->media()->create([
                    'immich_asset_id' => $item['immich_asset_id'],
                    'type' => $item['type'],
                    'sort_order' => $item['sort_order'] ?? $index,
                ]);
            }
        });
    }
}
