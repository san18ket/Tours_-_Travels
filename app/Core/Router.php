<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware = []): self
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    /**
     * Dispatch the current request through middleware and handler
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = $request->getPath();

        $matchedRoute = null;
        $params = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                if ($route['method'] !== $method) {
                    continue;
                }

                $matchedRoute = $route;
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        if (!$matchedRoute) {
            // Check if route exists with different method (405)
            foreach ($this->routes as $route) {
                if (preg_match($route['pattern'], $path)) {
                    return Response::make(
                        View::render('errors/405', ['message' => 'Method Not Allowed'], 'layouts/error'),
                        405
                    );
                }
            }

            // Otherwise 404
            return Response::make(
                View::render('errors/404', ['message' => 'The requested page could not be found.'], 'layouts/error'),
                404
            );
        }

        // Run middleware pipeline
        $middlewareResponse = $this->runMiddleware($matchedRoute['middleware'], $request);
        if ($middlewareResponse instanceof Response) {
            return $middlewareResponse;
        }

        // Execute route handler
        $handler = $matchedRoute['handler'];

        try {
            if (is_callable($handler)) {
                $result = call_user_func_array($handler, [$request, $params]);
            } elseif (is_array($handler) && count($handler) === 2) {
                [$controllerClass, $action] = $handler;
                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller {$controllerClass} does not exist.");
                }
                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    throw new \RuntimeException("Action {$action} not found on controller {$controllerClass}.");
                }
                $result = call_user_func_array([$controller, $action], [$request, $params]);
            } else {
                throw new \RuntimeException("Invalid route handler defined.");
            }

            if ($result instanceof Response) {
                return $result;
            } elseif (is_string($result)) {
                return Response::make($result);
            } elseif (is_array($result)) {
                return Response::json($result);
            }

            return Response::make('', 204);
        } catch (\Throwable $e) {
            Logger::error("Request execution failed on route {$path}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->isJson() || str_starts_with($path, '/api/')) {
                return Response::json([
                    'success' => false,
                    'error'   => 'An unexpected server error occurred. Please contact support.',
                    'ref'     => uniqid('err_')
                ], 500);
            }

            return Response::make(
                View::render('errors/500', [
                    'message' => 'An internal system error occurred. Our engineers have been notified.',
                    'ref'     => uniqid('err_')
                ], 'layouts/error'),
                500
            );
        }
    }

    /**
     * Process middlewares
     */
    private function runMiddleware(array $middlewares, Request $request): ?Response
    {
        foreach ($middlewares as $mw) {
            $parts = explode(':', $mw, 2);
            $name = $parts[0];
            $arg = $parts[1] ?? null;

            switch ($name) {
                case 'csrf':
                    if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                        $token = $request->input('_csrf_token') ?? $request->input('csrf_token') ?? $request->input('_token') ?? $request->getHeader('X-CSRF-TOKEN');
                        if (!Csrf::validate($token)) {
                            Logger::security('CSRF token validation failed', [
                                'ip' => $request->getClientIp(),
                                'path' => $request->getPath()
                            ]);

                            if ($request->isJson()) {
                                return Response::json(['error' => 'Invalid or expired CSRF security token.'], 403);
                            }

                            Session::setFlash('danger', 'Security verification failed or your session expired. Please refresh the page.');
                            return Response::redirect(url('/login'));
                        }
                    }
                    break;

                case 'auth':
                    if (!Session::isLoggedIn()) {
                        if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                            return Response::json(['error' => 'Authentication required.'], 401);
                        }
                        Session::set('redirect_after_login', $request->getPath());
                        Session::setFlash('info', 'Please sign in to access this feature.');
                        return Response::redirect(url('/login'));
                    }
                    break;

                case 'role':
                    if (!Session::isLoggedIn()) {
                        return Response::redirect(url('/login'));
                    }
                    $allowedRoles = explode(',', $arg ?? '');
                    $userRole = Session::getUserRole();
                    if (!in_array($userRole, $allowedRoles, true)) {
                        Logger::security('Unauthorized role access attempt', [
                            'user_id' => Session::getUserId(),
                            'role' => $userRole,
                            'required' => $allowedRoles,
                            'path' => $request->getPath()
                        ]);

                        if ($request->isJson()) {
                            return Response::json(['error' => 'Permission denied. Role insufficient.'], 403);
                        }

                        return Response::make(
                            View::render('errors/403', ['message' => 'You do not have administrative permission to view this resource.'], 'layouts/error'),
                            403
                        );
                    }
                    break;

                case 'throttle':
                    $throttleType = $arg ?? 'api';
                    $ip = $request->getClientIp();
                    $key = "throttle:{$throttleType}:{$ip}";
                    $max = 60;
                    $decay = 60;

                    if ($throttleType === 'login') {
                        $username = $request->input('login') ?? $request->input('username') ?? $request->input('email') ?? '';
                        $key = "throttle:login:{$ip}:" . hash('sha256', strtolower(trim((string)$username)));
                        $max = Config::get('security.rate_limits.login.max_attempts', 5);
                        $decay = (Config::get('security.rate_limits.login.decay_minutes', 15)) * 60;
                    } elseif ($throttleType === 'gps') {
                        $driverId = Session::getUserId() ?? $ip;
                        $key = "throttle:gps:{$driverId}";
                        $max = Config::get('security.rate_limits.gps_submissions.max_attempts', 60);
                        $decay = 60;
                    }

                    if (RateLimiter::tooManyAttempts($key, $max)) {
                        $seconds = RateLimiter::availableIn($key);
                        Logger::security("Rate limit exceeded for {$key}", ['ip' => $ip]);

                        if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                            return Response::json([
                                'error' => "Too many requests. Please wait {$seconds} seconds before trying again.",
                                'retry_after' => $seconds
                            ], 429)->setHeader('Retry-After', (string)$seconds);
                        }

                        Session::setFlash('danger', "Too many failed requests. Please wait {$seconds} seconds.");
                        return Response::make(
                            View::render('errors/429', ['seconds' => $seconds], 'layouts/error'),
                            429
                        );
                    }

                    RateLimiter::hit($key, $decay);
                    break;
            }
        }

        return null;
    }
}
