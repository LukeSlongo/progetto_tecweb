<?php
namespace App\Core;

class Router
{

    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($uri, $controller, $method, $middleware = [])
    {
        $this->add('GET', $uri, $controller, $method, $middleware);
    }

    public function post($uri, $controller, $method, $middleware = [])
    {
        $this->add('POST', $uri, $controller, $method, $middleware);
    }


    public function add($httpMethod, $uri, $controller, $method, $middleware = [])
    {
        $this->routes[$httpMethod][$uri] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function dispatch($requestedUri, $httpMethod)
    {

        $uri = parse_url($requestedUri, PHP_URL_PATH);

        $matchedRoute = null;
        $params = [];
        $routesToSearch = $this->routes[$httpMethod] ?? [];

        foreach ($routesToSearch as $routePath => $routeData) {
            $pattern = preg_replace('/\{[a-zA-Z0-9-_]+:num\}/', '([0-9]+)', $routePath);
            $pattern = preg_replace('/\{[a-zA-Z0-9-_]+:alpha\}/', '([a-zA-Z-_]+)', $pattern);
            $pattern = preg_replace('/\{[a-zA-Z0-9-_]+:alphanum\}/', '([a-zA-Z0-9-_]+)', $pattern);
            $pattern = preg_replace('/\{[a-zA-Z0-9-_]+\}/', '([a-zA-Z0-9-_]+)', $pattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $matchedRoute = $routeData;
                $matchedRoute['pattern_originale'] = $routePath;
                $params = $matches;
                break;
            }
        }

        if (!$matchedRoute) {
            throw new \App\Exceptions\NotFoundException("Pagina non trovata");
        }

        $namedParams = $this->getNamedParams($matchedRoute['pattern_originale'], $params);

        $this->handleMiddleware($matchedRoute['middleware'], $namedParams);

        $controllerName = "App\\Controllers\\" . $matchedRoute['controller'];
        $controller = new $controllerName();
        call_user_func_array([$controller, $matchedRoute['method']], $params);
    }

    private function getNamedParams($routePath, $matches)
    {
        $namedParams = [];
        if (!empty($matches)) {
            preg_match_all('/\{([a-zA-Z0-9-_]+)(?::\w+)?\}/', $routePath, $paramKeys);
            $keys = $paramKeys[1];
            if (count($keys) === count($matches)) {
                $namedParams = array_combine($keys, $matches);
            }
        }
        return $namedParams;
    }

    private function handleMiddleware($middleware, $params)
    {
        foreach ($middleware as $mw) {

            // utente non loggato
            if ($mw === 'guest') {
                if (\App\Core\Auth::isLogged()) {
                    header('Location: /');
                    exit;
                }
            }

            // utente loggato
            if ($mw === 'auth') {
                if (!\App\Core\Auth::isLogged()) {
                    header('Location: /login');
                    exit;
                }
            }

            // amministratore
            if ($mw === 'admin') {
                if (!\App\Core\Auth::isAdmin()) {
                    throw new \App\Exceptions\ForbiddenException("Non hai i permessi da amministratore");
                }
            }

            // tecnico (o admin)
            if ($mw === 'technician') {
                $user = \App\Core\Auth::getUser();
                if (!$user || ($user['role'] !== 'technician' && $user['role'] !== 'admin')) {
                    throw new \App\Exceptions\ForbiddenException("Accesso riservato ai tecnici");
                }
            }

            // owner o admin
            if (strpos($mw, 'owner:') === 0) {
                if (\App\Core\Auth::isAdmin()) {
                    continue;
                }

                $user = \App\Core\Auth::getUser();
                if (!$user) {
                    header('Location: /login');
                    exit;
                }

                // item posseduto
                $parts = explode(':', $mw);
                $table = $parts[1];

                $resourceId = $params['id'] ?? $params['issue_id'] ?? null;

                if ($resourceId) {
                    // si chiama l'owner ricavandolo direttamente dal db
                    $db = \App\Core\Database::getInstance();
                    $stmt = $db->prepare("SELECT user_id FROM {$table} WHERE id = :id");
                    $stmt->execute(['id' => $resourceId]);
                    $ownerId = $stmt->fetchColumn();
                    if (!$ownerId || $ownerId != $user['id']) {
                        throw new \App\Exceptions\ForbiddenException("Azione negata: non sei il proprietario di questa segnalazione.");
                    }
                } else {
                    throw new \App\Exceptions\ForbiddenException("Impossibile verificare il proprietario: ID mancante.");
                }
            }
        }
    }
}