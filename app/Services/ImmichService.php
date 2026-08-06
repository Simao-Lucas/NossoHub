<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ImmichService
{
    private function client(): PendingRequest
    {
        $apiKey = (string) config('immich.api_key');

        if ($apiKey === '') {
            Log::warning('Immich API key is not configured.');
        }

        return Http::baseUrl((string) config('immich.base_url'))
            ->timeout((int) config('immich.timeout', 30))
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => $apiKey,
            ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAssets(int $page = 1, int $size = 50): array
    {
        $response = $this->client()->post('/api/search/metadata', [
            'page' => $page,
            'size' => $size,
            'withExif' => false,
        ]);

        if (! $response->successful()) {
            $this->logFailure('listAssets', $response->status(), $response->body());

            return [];
        }

        return $this->extractAssets($response->json());
    }

    /**
     * @param  array{query?: string|null, takenAfter?: string|null, takenBefore?: string|null, albumIds?: list<string>}  $filters
     * @return list<array<string, mixed>>
     */
    public function searchAssets(array $filters = [], int $page = 1, int $size = 50): array
    {
        $payload = [
            'page' => $page,
            'size' => $size,
            'withExif' => false,
        ];

        if (! empty($filters['query'])) {
            $payload['query'] = $filters['query'];
        }

        if (! empty($filters['takenAfter'])) {
            $payload['takenAfter'] = $filters['takenAfter'];
        }

        if (! empty($filters['takenBefore'])) {
            $payload['takenBefore'] = $filters['takenBefore'];
        }

        if (! empty($filters['albumIds'])) {
            $payload['albumIds'] = array_values($filters['albumIds']);
        }

        $endpoint = ! empty($filters['query'])
            ? '/api/search/smart'
            : '/api/search/metadata';

        $response = $this->client()->post($endpoint, $payload);

        if (! $response->successful()) {
            $this->logFailure('searchAssets', $response->status(), $response->body());

            return [];
        }

        return $this->extractAssets($response->json());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAssetById(string $assetId): ?array
    {
        if ($assetId === '') {
            return null;
        }

        $response = $this->client()->get("/api/assets/{$assetId}");

        if (! $response->successful()) {
            $this->logFailure('getAssetById', $response->status(), $response->body());

            return null;
        }

        return $response->json();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAlbums(): array
    {
        $response = $this->client()->get('/api/albums');

        if (! $response->successful()) {
            $this->logFailure('listAlbums', $response->status(), $response->body());

            return [];
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    public function thumbnailUrl(string $assetId, string $size = 'thumbnail'): string
    {
        $base = (string) config('immich.base_url');

        return "{$base}/api/assets/{$assetId}/thumbnail?size={$size}";
    }

    public function originalUrl(string $assetId): string
    {
        $base = (string) config('immich.base_url');

        return "{$base}/api/assets/{$assetId}/original";
    }

    public function videoPlaybackUrl(string $assetId): string
    {
        $base = (string) config('immich.base_url');

        return "{$base}/api/assets/{$assetId}/video/playback";
    }

    public function isConfigured(): bool
    {
        return filled(config('immich.base_url')) && filled(config('immich.api_key'));
    }

    /**
     * Proxy helper for authenticated thumbnail requests from the app.
     */
    /**
     * @return array{body: string, content_type: string}
     */
    public function fetchThumbnail(string $assetId, string $size = 'thumbnail'): array
    {
        $response = $this->client()
            ->withHeaders(['Accept' => 'image/*'])
            ->get("/api/assets/{$assetId}/thumbnail", ['size' => $size]);

        if (! $response->successful()) {
            throw new RuntimeException("Unable to fetch Immich thumbnail for asset {$assetId}");
        }

        return [
            'body' => $response->body(),
            'content_type' => $response->header('Content-Type') ?: 'image/jpeg',
        ];
    }

    /**
     * @return array{body: string, content_type: string}
     */
    public function fetchOriginal(string $assetId): array
    {
        $response = $this->client()
            ->withHeaders(['Accept' => '*/*'])
            ->get("/api/assets/{$assetId}/original");

        if (! $response->successful()) {
            throw new RuntimeException("Unable to fetch Immich original for asset {$assetId}");
        }

        return [
            'body' => $response->body(),
            'content_type' => $response->header('Content-Type') ?: 'application/octet-stream',
        ];
    }

    public function appThumbnailUrl(string $assetId, string $size = 'thumbnail'): string
    {
        return route('immich.thumbnail', ['assetId' => $assetId, 'size' => $size], absolute: false);
    }

    public function appOriginalUrl(string $assetId): string
    {
        return route('immich.original', ['assetId' => $assetId], absolute: false);
    }

    public function appPreviewUrl(string $assetId): string
    {
        return $this->appThumbnailUrl($assetId, 'preview');
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return list<array<string, mixed>>
     */
    private function extractAssets(?array $payload): array
    {
        if ($payload === null) {
            return [];
        }

        $assets = $payload['assets']['items']
            ?? $payload['items']
            ?? $payload['assets']
            ?? $payload;

        return is_array($assets) ? array_values($assets) : [];
    }

    private function logFailure(string $method, int $status, string $body): void
    {
        Log::warning("ImmichService::{$method} failed", [
            'status' => $status,
            'body' => str($body)->limit(500)->toString(),
        ]);
    }
}
