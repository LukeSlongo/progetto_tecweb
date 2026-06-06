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

        foreach($routesToSearch as $routePath => $routeData){
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
        if (in_array('auth', $middleware)) {
            if (!\App\Core\Auth::isLogged()) {
                header('Location: /login');
                exit;
            }
        }

        if (in_array('admin', $middleware)) {
            if (!\App\Core\Auth::isAdmin()) {
                throw new \App\Exceptions\ForbiddenException("Non hai i permessi da amministratore");
            }
        }

        if (in_array('technician', $middleware)) {
            $user = \App\Core\Auth::getUser();
            if (!$user || ($user['role'] !== 'technician' && $user['role'] !== 'admin')) {
                throw new \App\Exceptions\ForbiddenException("Accesso riservato ai tecnici");
            }
        }
    }
}