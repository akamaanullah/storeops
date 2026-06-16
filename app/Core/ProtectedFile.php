<?php
/**
 * Authenticated delivery for files stored under public/uploads.
 */

namespace App\Core;

use PDO;

class ProtectedFile extends Model {
    private const ALLOWED_PREFIXES = ['/uploads/jobs/', '/uploads/comments/', '/uploads/w9/'];

    private const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const ALLOWED_DOC_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public static function url(string $relativePath): string {
        $normalized = self::normalizeRelativePath($relativePath);
        if ($normalized === null) {
            return '';
        }

        return rtrim(BASE_URL, '/') . '/files/serve?p=' . rawurlencode(ltrim($normalized, '/'));
    }

    public static function stream(string $rawPath): void {
        $normalized = self::normalizeRelativePath($rawPath);
        if ($normalized === null || !self::isRegisteredInDatabase($normalized)) {
            http_response_code(404);
            die('File not found.');
        }

        $uploadsRoot  = realpath(ROOT_PATH . '/public/uploads');
        $absolutePath = realpath(ROOT_PATH . '/public' . $normalized);

        if ($uploadsRoot === false || $absolutePath === false || !is_file($absolutePath)) {
            http_response_code(404);
            die('File not found.');
        }

        $uploadsPrefix = rtrim(str_replace('\\', '/', $uploadsRoot), '/') . '/';
        $resolved      = str_replace('\\', '/', $absolutePath);
        if (!str_starts_with($resolved, $uploadsPrefix)) {
            http_response_code(404);
            die('File not found.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($absolutePath) ?: 'application/octet-stream';

        $isImage = in_array($mime, self::ALLOWED_IMAGE_MIMES, true);
        $isDoc   = in_array($mime, self::ALLOWED_DOC_MIMES, true);

        if (!$isImage && !$isDoc) {
            http_response_code(403);
            die('File type not allowed.');
        }

        // For documents, force inline viewing (PDF opens in browser, Word downloads)
        $disposition = ($mime === 'application/pdf') ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absolutePath));
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($absolutePath) . '"');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($absolutePath);
        exit;
    }

    private static function normalizeRelativePath(string $path): ?string {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return null;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        // Allow jobs, comments, and w9 paths
        if (!preg_match('#^/uploads/(jobs|comments|w9)/[0-9]+/[a-zA-Z0-9._-]+$#', $path)) {
            return null;
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        return null;
    }

    private static function isRegisteredInDatabase(string $webPath): bool {
        $db = (new self())->getDB();

        if (self::existsInTable($db, 'SELECT 1 FROM job_pictures WHERE file_path = ? LIMIT 1', $webPath)) {
            return true;
        }

        if (self::existsInTable($db, 'SELECT 1 FROM comment_pictures WHERE file_path = ? LIMIT 1', $webPath)) {
            return true;
        }

        if (self::existsInTable($db, 'SELECT 1 FROM comments WHERE picture_path = ? LIMIT 1', $webPath)) {
            return true;
        }

        // W9 form paths are stored on the jobs table
        return self::existsInTable($db, 'SELECT 1 FROM jobs WHERE w9_form_path = ? LIMIT 1', $webPath);
    }

    private static function existsInTable(PDO $db, string $sql, string $webPath): bool {
        $stmt = $db->prepare($sql);
        $stmt->execute([$webPath]);
        return (bool)$stmt->fetchColumn();
    }
}
