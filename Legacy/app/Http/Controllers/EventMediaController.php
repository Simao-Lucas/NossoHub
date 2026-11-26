<?php

namespace App\Http\Controllers;

use App\Models\EventMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventMediaController extends Controller
{
    public function show(EventMedia $media): StreamedResponse
    {
        $disk = Storage::disk($media->disk ?: 'public');

        if ($media->path === '' || ! $disk->exists($media->path)) {
            throw new NotFoundHttpException('Arquivo de mídia não encontrado.');
        }

        return $disk->response(
            $media->path,
            $media->original_name,
            [
                'Content-Type' => $media->mime_type ?: $disk->mimeType($media->path),
                'Cache-Control' => 'public, max-age=604800',
            ],
        );
    }
}
