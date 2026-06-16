<?php
/**
 * Secure file upload validation
 */

namespace App\Core;

class Upload {
    private const ALLOWED_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp'],
    ];

    private const ALLOWED_DOC_MIMES = [
        'application/pdf'    => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
    ];

    /** Max W9 document size: 10 MB */
    private const MAX_DOC_SIZE = 10 * 1024 * 1024;

    /**
     * Validate and move a single uploaded image. Returns relative public path or null.
     */
    public static function storeImage(array $file, string $uploadDir, string $relativePrefix, string $namePrefix = 'img_'): ?string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_UPLOAD_EXTENSIONS, true)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!$mime || !isset(self::ALLOWED_MIMES[$mime]) || !in_array($ext, self::ALLOWED_MIMES[$mime], true)) {
            return null;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid($namePrefix, true) . '.' . $ext;
        $destPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        return rtrim($relativePrefix, '/') . '/' . $newFileName;
    }

    /**
     * Validate and move multiple uploaded images from $_FILES field.
     *
     * @return string[] Relative public paths
     */
    public static function storeMultipleImages(array $files, string $uploadDir, string $relativePrefix, int $maxFiles = 10): array {
        $stored = [];
        if (!isset($files['name']) || !is_array($files['name']) || empty($files['name'][0])) {
            return $stored;
        }

        $count = min(count($files['name']), max(1, $maxFiles));
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $path = self::storeImage($file, $uploadDir, $relativePrefix);
            if ($path !== null) {
                $stored[] = $path;
            }
        }

        return $stored;
    }

    /**
     * Validate and store a W9 document (PDF, DOC, DOCX, JPG, PNG).
     * Returns relative public path or null on failure.
     */
    public static function storeDocument(array $file, string $uploadDir, string $relativePrefix, string $namePrefix = 'w9_'): ?string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > self::MAX_DOC_SIZE || ($file['size'] ?? 0) === 0) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed_exts, true)) {
            return null;
        }

        // For images, also verify real MIME type
        if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!$mime || !isset(self::ALLOWED_DOC_MIMES[$mime]) || !in_array($ext, self::ALLOWED_DOC_MIMES[$mime], true)) {
                return null;
            }
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid($namePrefix, true) . '.' . $ext;
        $destPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        return rtrim($relativePrefix, '/') . '/' . $newFileName;
    }
}
