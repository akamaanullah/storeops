<?php
/**
 * Zip archive helper for attachment downloads
 */

namespace App\Core;

use ZipArchive;

class AttachmentZip {
    /**
     * @param array<int, array{path: string, name: string}> $files Absolute paths with archive entry names
     */
    public static function download(array $files, string $zipName): void {
        if (empty($files)) {
            http_response_code(404);
            die('No attachments found to download.');
        }

        if (!class_exists(ZipArchive::class)) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
            die(
                '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Download unavailable</title></head><body style="font-family:sans-serif;max-width:36rem;margin:3rem auto;padding:0 1rem;">'
                . '<h1>ZIP download unavailable</h1>'
                . '<p>PHP <strong>zip</strong> extension is disabled on this server. Enable <code>extension=zip</code> in <code>php.ini</code>, then restart Apache.</p>'
                . '<p><a href="javascript:history.back()">Go back</a></p>'
                . '</body></html>'
            );
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'jt_zip_');
        if ($tempFile === false) {
            http_response_code(500);
            die('Unable to prepare download archive.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::OVERWRITE) !== true) {
            @unlink($tempFile);
            http_response_code(500);
            die('Unable to create download archive.');
        }

        $added = 0;
        foreach ($files as $file) {
            if (!is_file($file['path'])) {
                continue;
            }
            $zip->addFile($file['path'], $file['name']);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tempFile);
            http_response_code(404);
            die('Attachment files are missing on the server.');
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $zipName) ?: 'attachments.zip';
        if (!str_ends_with(strtolower($safeName), '.zip')) {
            $safeName .= '.zip';
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Cache-Control: no-store, no-cache, must-revalidate');

        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }

    public static function publicPath(string $relativePath): string {
        return ROOT_PATH . '/public' . $relativePath;
    }
}
