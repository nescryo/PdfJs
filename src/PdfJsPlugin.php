<?php

namespace PdfJs;

use App\Classes\Plugin;
use App\Facades\Hook;

class PdfJsPlugin extends Plugin
{
    public function boot()
    {
        Hook::add('Frontend::PaperGalley', function ($hookName, $galley, &$returner) {
            if (! $galley || ! $galley->isPdf()) {
                return;
            }

            // Security Check: Only stream PDF if the submission is published
            if (! $galley->submission || ! $galley->submission->isPublished()) {
                return;
            }

            $media = $galley->file?->media;
            if (! $media || ! file_exists($media->getPath())) {
                return;
            }

            $returner = response()
                ->file($media->getPath(), [
                    'Content-Type' => $media->mime_type ?? 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.addslashes($media->file_name).'"',
                    'Content-Length' => $media->size,
                    'Content-Transfer-Encoding' => 'binary',
                    'Accept-Ranges' => 'bytes',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
        });
    }
}
