<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class AssetController extends Controller
{
    public function public(string $path): Response
    {
        return $this->serveFile(public_path($path), public_path());
    }

    public function media(string $path): Response
    {
        $normalizedPath = ltrim($path, '/');

        foreach (site_public_media_candidates($normalizedPath) as [$candidatePath, $allowedRoot]) {
            if (is_file($candidatePath)) {
                return $this->serveFile($candidatePath, $allowedRoot);
            }
        }

        abort(404);
    }

    private function serveFile(string $candidatePath, string $allowedRoot): Response
    {
        $realAllowedRoot = realpath($allowedRoot);
        $realCandidatePath = realpath($candidatePath);

        abort_unless(
            $realAllowedRoot !== false
            && $realCandidatePath !== false
            && str_starts_with($realCandidatePath, $realAllowedRoot . DIRECTORY_SEPARATOR)
            && is_file($realCandidatePath),
            404
        );

        $mimeType = mime_content_type($realCandidatePath) ?: 'application/octet-stream';

        return response(file_get_contents($realCandidatePath), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
        ]);
    }
}
