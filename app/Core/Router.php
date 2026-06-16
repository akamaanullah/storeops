<?php
/**
 * Routing Engine for Custom PHP MVC
 */

namespace App\Core;

class Router {
    private array $routes = [];

    /**
     * Define a GET route
     */
    public function get(string $uri, string $controllerAction): void {
        $this->addRoute('GET', $uri, $controllerAction);
    }

    /**
     * Define a POST route
     */
    public function post(string $uri, string $controllerAction): void {
        $this->addRoute('POST', $uri, $controllerAction);
    }

    /**
     * Register route entry internally
     */
    private function addRoute(string $method, string $uri, string $controllerAction): void {
        // Convert route pattern {id} to a regex pattern
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[0-9a-zA-Z_-]+)', $uri);
        $pattern = '#^' . trim($pattern, '/') . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controllerAction' => $controllerAction,
        ];
    }

    /**
     * Resolve the request and execute the target controller action
     */
    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Auto-detect base folder path (highly robust for Apache subdirectories and PHP built-in servers)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($scriptDir !== '/') {
            // Remove /public from base path detection if it's there
            $basePath = str_replace('/public', '', $scriptDir);
            
            // Subtract base path from request URI
            if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
                $requestUri = substr($requestUri, strlen($basePath));
            }
            // Subtract public suffix from request URI if accessed via public subfolder
            if (strpos($requestUri, '/public') === 0) {
                $requestUri = substr($requestUri, 7);
            }
        }

        $requestUri = trim($requestUri, '/');
        define('CURRENT_ROUTE', $requestUri);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                // Keep only index parameters matching dynamic path values
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Split controller and method (e.g. JobController@show)
                list($controllerName, $action) = explode('@', $route['controllerAction']);

                $controllerClass = "App\\Controllers\\" . $controllerName;
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        // Call controller method matching parameter inputs
                        call_user_func_array([$controller, $action], $params);
                        return;
                    } else {
                        die("Method '{$action}' does not exist on '{$controllerClass}'");
                    }
                } else {
                    die("Class '{$controllerClass}' not found.");
                }
            }
        }

        // Return a clean 404 response
        http_response_code(404);
        echo "<h1>404 Page Not Found</h1><p>The requested route '/$requestUri' with method '$requestMethod' could not match any endpoints.</p>";
        exit;
    }
}
