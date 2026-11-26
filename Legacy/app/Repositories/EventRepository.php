<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    public function allChronological(bool $ascending = false): Collection
    {
        return Event::query()
            ->withCount([
                'media as photos_count' => fn ($q) => $q->where('type', 'photo'),
                'media as videos_count' => fn ($q) => $q->where('type', 'video'),
            ])
            ->when(
                $ascending,
                fn ($q) => $q->orderBy('occurred_at')->orderBy('id'),
                fn ($q) => $q->orderByDesc('occurred_at')->orderByDesc('id'),
            )
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

    public function addMedia(Event $event, array $attributes): EventMedia
    {
        return $event->media()->create($attributes);
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, EventMedia>
     */
    public function mediaByIds(Event $event, array $ids): Collection
    {
        return $event->media()->whereIn('id', $ids)->get();
    }

    public function nextSortOrder(Event $event): int
    {
        return ((int) $event->media()->max('sort_order')) + 1;
    }
}
