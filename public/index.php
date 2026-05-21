<?php
header('Content-Type: text/html; charset=utf-8');

session_start();

require_once __DIR__ . '/../config.php';


spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;


    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';


    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\SegnalazioneController;
use App\Controllers\ErrorController;
use App\Controllers\DipartimentoController;
use App\Controllers\EdificioController;
use App\Controllers\AulaController;



set_exception_handler(function ($e) {

    $errorController = new ErrorController();

    if ($e instanceof \App\Exceptions\NotFoundException) {
        $errorController->index(404, '404_notfound', $e->getMessage());
    } 
    elseif ($e instanceof \App\Exceptions\ForbiddenException) {
        $errorController->index(403, '403_forbidden');
    } 
    else {
        // Logga l'errore vero per te
        error_log($e->getMessage());
        // Mostra pagina 500 generica
        $errorController->index(500, '500_internalerror', $e->getMessage());
    }
    
    exit;
});

    $router = new Router();


    $router->add('/', HomeController::class, 'visualizza_home');

    $router->add('/nuova_segnalazione', SegnalazioneController::class, 'nuova_segnalazione');
    $router->add('/api/segnalazione/dipartimenti', DipartimentoController::class, 'get_all_dipartimenti');
    $router->add('/api/segnalazione/edifici', EdificioController::class, 'get_edifici_by_dipartimento');
    $router->add('/api/segnalazione/aule', AulaController::class, 'get_aule_by_edificio');


    $router->dispatch($_SERVER['REQUEST_URI']);

?>