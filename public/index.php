<?php
header('Content-Type: text/html; charset=utf-8');

session_start();

require_once __DIR__ . '/../config.php';


spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';


    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;


    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';


    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Controllers\ErrorController;



set_exception_handler(function ($e) {

    $errorController = new ErrorController();

    if ($e instanceof \App\Exceptions\NotFoundException) {
        $errorController->index(404, '404_notfound', $e->getMessage());
    } elseif ($e instanceof \App\Exceptions\ForbiddenException) {
        $errorController->index(403, '403_forbidden');
    } else {
        // Logga l'errore vero per te
        error_log($e->getMessage());
        // Mostra pagina 500 generica
        $errorController->index(500, '500_internalerror', $e->getMessage());
    }

    exit;
});

// ... [codice precedente invariato fino alla creazione del Router] ...

$router = new Router();


$router->get('/login', 'UserController', 'viewLogin', ['guest']);
$router->post('/login', 'UserController', 'login', ['guest']);
$router->get('/register', 'UserController', 'viewRegister', ['guest']);
$router->post('/register', 'UserController', 'register', ['guest']);
$router->post('/logout', 'UserController', 'logout', ['auth']);

// Home
$router->get('/', 'UserController', 'viewHome', ['auth']);

// Aule
$router->get('/rooms', 'RoomController', 'viewRoomList', ['auth']);
$router->get('/rooms/{id:num}', 'RoomController', 'viewRoomDetail', ['auth']);

// Segnalazioni
$router->get('/issues/new', 'IssueController', 'viewIssueForm', ['auth']);
$router->post('/issues', 'IssueController', 'saveIssue', ['auth']);
$router->get('/issues', 'IssueController', 'viewIssueList', ['auth', 'technician']);
$router->get('/issues/{id:num}', 'IssueController', 'viewIssueDetail', ['auth']);
$router->post('/issues/{id:num}/delete', 'IssueController', 'deleteIssue', ['auth', 'owner:issue']);

// Utenti (solo admin)
$router->get('/users', 'UserController', 'viewUserList', ['auth', 'admin']);
$router->post('/users/{id:num}/delete', 'UserController', 'deleteUser', ['auth', 'admin']);

// Rotte API
$router->post('/api/favorites/{room_id:num}/add', 'UserController', 'addFavorite', ['auth']);
$router->post('/api/favorites/{room_id:num}/remove', 'UserController', 'removeFavorite', ['auth']);
$router->post('/api/issues/{issue_id:num}/take', 'IssueController', 'takeIssue', ['auth', 'technician']);
$router->post('/api/issues/{issue_id:num}/close', 'IssueController', 'closeIssue', ['auth', 'technician']);

// Dispatch della rotta
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);