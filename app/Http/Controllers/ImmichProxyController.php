<?php

namespace App\Http\Controllers;

use App\Services\ImmichService;
use Illuminate\Http\Response;
use Throwable;

class ImmichProxyController extends Controller
{
    public function __construct(
        private readonly ImmichService $immich,
    ) {}

    public function thumbnail(string $assetId): Response
    {
        try {
            $size = (string) request('size', 'thumbnail');
            if (! in_array($size, ['thumbnail', 'preview', 'fullsize'], true)) {
                $size = 'thumbnail';
            }
            $file = $this->immich->fetchThumbnail($assetId, $size);
        } catch (Throwable) {
            abort(404);
        }

        return response($file['body'], 200, [
            'Content-Type' => $file['content_type'],
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function original(string $assetId): Response
    {
        try {
            $file = $this->immich->fetchOriginal($assetId);
        } catch (Throwable) {
            abort(404);
        }

        return response($file['body'], 200, [
            'Content-Type' => $file['content_type'],
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
