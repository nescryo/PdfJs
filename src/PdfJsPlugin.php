<?php

namespace PdfJs;

use App\Classes\Plugin;
use App\Facades\Hook;

class PdfJsPlugin extends Plugin
{
    public function boot()
    {
        Hook::add('Frontend::PaperGalley', function ($hookName, $galley, &$returner) {
            if (! $galley || ! method_exists($galley, 'isPdf') || ! $galley->isPdf()) {
                return;
            }

            // Security Check: Only stream PDF if the submission is published
            $submission = $galley->submission;
            if (! $submission) {
                return;
            }

            $isPublished = method_exists($submission, 'isPublished')
                ? $submission->isPublished()
                : in_array(data_get($submission, 'status'), [3, 'published', 'Published', \App\Enums\SubmissionStatus::Published ?? null], true);

            if (! $isPublished) {
                return;
            }

            $file = method_exists($galley, 'file') ? $galley->file : ($galley->submissionFile ?? null);
            $media = $file?->media;
            if (! $media || ! file_exists($media->getPath())) {
                return;
            }

            $fileName = $media->file_name ?? $media->name ?? 'paper.pdf';

            $returner = response()
                ->file($media->getPath(), [
                    'Content-Type' => $media->mime_type ?? 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.addslashes($fileName).'"',
                    'Content-Length' => $media->size ?? filesize($media->getPath()),
                    'Content-Transfer-Encoding' => 'binary',
                    'Accept-Ranges' => 'bytes',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
        });
    }
}
