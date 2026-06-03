<?php
namespace App\Core;


Class Router {
    
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($uri, $controller, $method, $middleware = []) {
        $this->add('GET', $uri, $controller, $method, $middleware);
    }

    public function post($uri, $controller, $method, $middleware = []) {
        $this->add('POST', $uri, $controller, $method, $middleware);
    }

    public function add($uri, $controller, $method, $middleware = []){

        $this->routes[$uri] = [
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public function dispatch($requestedUri, $httpMethod){

        $uri = parse_url($requestedUri, PHP_URL_PATH);
        
        $matchedRoute = null;
        $params = [];
        $routesToSearch = $this->routes[$httpMethod] ?? [];

        foreach($this->routes as $routePath => $routeData){
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


    private function getNamedParams($routePath, $matches) {
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

    private function handleMiddleware($middleware, $params) {
        // Controllo Login Generico
        if (in_array('auth', $middleware)) {
            if (!\App\Core\Auth::isLogged()) {
                header('Location: /login');
                exit;
            }
        }

        // Controllo Ruolo Admin
        if (in_array('admin', $middleware)) {
            $currentUser = \App\Core\Auth::getUser()['is_admin'] ?? null;

            if (!$currentUser) {
                throw new \App\Exceptions\ForbiddenException("Non hai i permessi da amministratore");
            }
        }

        // Controllo Ruolo Tecnico
        if (in_array('tecnico', $middleware)) {
            $user = \App\Core\Auth::getUser();
            if (!$user || ($user['ruolo'] !== 'tecnico' && $user['ruolo'] !== 'admin')) {
                throw new \App\Exceptions\ForbiddenException("Accesso riservato ai tecnici");
            }
        }
    }
} 

/** 
 * aggiungo le pagine su index.php con $controller->add(<url>, <controller>, <metodo> )
 * 
 * - url:          puo` essere qualcosa del tipo: /canzone, /canzone/{id:num}, /canzone/{nome_canzone:alphanum}, /canzone/{nome_canzone:aplhanum}/download, /utente/{nome_utente:alphanum}/playlist/{id:num}.
 *                [consiglio di usare il tipo del paramentre messo con :<tipo>. se non si mette e` alphanum]
 * 
 * - controller:   e' la classe del controller utilizzato
 * 
 * - metodo:       metodo della classe controller che viene invocato quando viene visitata la pagina. I parametri vengono passati come array.
 * 
 * 
 * avvio la pagina con $router->dispatch($_SERVER['REQUEST_URI']);
 * 
 */