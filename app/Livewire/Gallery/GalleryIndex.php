<?php

namespace App\Livewire\Gallery;

use App\Services\ImmichService;
use Livewire\Attributes\Url;
use Livewire\Component;

class GalleryIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $albumId = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public int $page = 1;

    public ?string $lightboxAssetId = null;

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedAlbumId(): void
    {
        $this->page = 1;
    }

    public function updatedDateFrom(): void
    {
        $this->page = 1;
    }

    public function updatedDateTo(): void
    {
        $this->page = 1;
    }

    public function loadMore(): void
    {
        $this->page++;
    }

    public function openLightbox(string $assetId): void
    {
        $this->lightboxAssetId = $assetId;
    }

    public function closeLightbox(): void
    {
        $this->lightboxAssetId = null;
    }

    public function render(ImmichService $immich)
    {
        $filters = [
            'query' => $this->search ?: null,
            'takenAfter' => $this->dateFrom ? $this->dateFrom.'T00:00:00.000Z' : null,
            'takenBefore' => $this->dateTo ? $this->dateTo.'T23:59:59.999Z' : null,
            'albumIds' => $this->albumId !== '' ? [$this->albumId] : [],
        ];

        $assets = [];

        for ($page = 1; $page <= $this->page; $page++) {
            $chunk = $immich->searchAssets($filters, $page, 48);
            $assets = array_merge($assets, $chunk);

            if (count($chunk) < 48) {
                break;
            }
        }

        $normalized = collect($assets)->map(function (array $asset) use ($immich) {
            $id = (string) ($asset['id'] ?? '');

            return [
                'id' => $id,
                'type' => strtolower((string) ($asset['type'] ?? 'image')),
                'originalFileName' => $asset['originalFileName'] ?? null,
                'localDateTime' => $asset['localDateTime'] ?? $asset['fileCreatedAt'] ?? null,
                'thumbnail_url' => $id !== '' ? $immich->appThumbnailUrl($id) : null,
                'original_url' => $id !== '' ? $immich->appOriginalUrl($id) : null,
            ];
        })->filter(fn (array $a) => $a['id'] !== '')->values();

        return view('livewire.gallery.index', [
            'assets' => $normalized,
            'albums' => $immich->listAlbums(),
            'configured' => $immich->isConfigured(),
            'lightboxUrl' => $this->lightboxAssetId
                ? $immich->appOriginalUrl($this->lightboxAssetId)
                : null,
        ]);
    }
}
