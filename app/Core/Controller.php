<?php
/**
 * Base Controller Class for Custom PHP MVC
 */

namespace App\Core;

abstract class Controller {
    /**
     * Render a view file with extracted variables.
     */
    protected function render(string $viewPath, array $data = []): void {
        // Extract variables to local scope
        extract($data);

        $currentUser = Auth::user();
        $viewFile = '';
        $resolved = false;

        if ($currentUser && isset($currentUser['role'])) {
            $rolePath = str_replace('.', '/', $viewPath);
            $roleViewFile = dirname(__DIR__, 2) . "/views/" . $currentUser['role'] . "/" . $rolePath . ".php";
            if (file_exists($roleViewFile)) {
                $viewFile = $roleViewFile;
                $resolved = true;
            }
        }

        if (!$resolved) {
            $viewFile = dirname(__DIR__, 2) . "/views/" . str_replace('.', '/', $viewPath) . ".php";
        }

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            die("View file not found: {$viewPath}");
        }
    }

    /**
     * Terminate program and send a JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL path.
     */
    protected function redirect(string $path): void {
        if (strpos($path, 'http://') !== 0 && strpos($path, 'https://') !== 0) {
            $path = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
        }
        header("Location: " . $path);
        exit;
    }

    protected function wantsJson(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }

    protected function requestHeader(string $name): ?string {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $headerName => $value) {
                if (strcasecmp($headerName, $name) === 0) {
                    return $value;
                }
            }
        }
        return null;
    }
}
