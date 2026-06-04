<?php

// =========================================================================
// 1. IL CONTROLLER FANTASMA
// Usiamo i namespace con le parentesi graffe per creare una classe finta
// esattamente dove il Router si aspetta di trovarla (App\Controllers)
// =========================================================================
namespace App\Controllers {
    class DummyController {
        // Usiamo una variabile statica per memorizzare cosa il Router ci passa
        public static $parametriRicevuti = [];

        public function testMetodoSemplice() {
            self::$parametriRicevuti = ['chiamato_senza_parametri'];
        }

        public function testMetodoConParametri($id, $nome = null) {
            self::$parametriRicevuti = func_get_args(); // Salva tutti i parametri passati
        }
    }
}

// =========================================================================
// 2. I TEST DEL ROUTER (Spazio dei nomi globale)
// =========================================================================
namespace {

    use PHPUnit\Framework\TestCase;
    use App\Core\Router;
    use App\Exceptions\NotFoundException;
    use App\Exceptions\ForbiddenException;

    class RouterTest extends TestCase
    {
        protected function setUp(): void
        {
            // Puliamo sessione e controller fantasma prima di ogni test
            $_SESSION = [];
            \App\Controllers\DummyController::$parametriRicevuti = [];
        }

        public function test_dispatch_lancia_404_se_rotta_inesistente()
        {
            $router = new Router();
            
            // Diciamo a PHPUnit che ci aspettiamo un 404
            $this->expectException(NotFoundException::class);
            $this->expectExceptionMessage("Pagina non trovata");

            // Proviamo a navigare in una rotta che non abbiamo mai registrato
            $router->dispatch('/pagina-segreta', 'GET');
        }

        public function test_dispatch_trova_rotta_semplice_e_chiama_controller()
        {
            $router = new Router();
            $router->get('/home', 'DummyController', 'testMetodoSemplice');

            $router->dispatch('/home', 'GET');

            // Verifichiamo che il router abbia istanziato il controller e chiamato il metodo!
            $this->assertEquals(
                ['chiamato_senza_parametri'], 
                \App\Controllers\DummyController::$parametriRicevuti
            );
        }

        public function test_dispatch_estrae_i_parametri_regex_dalla_url()
        {
            $router = new Router();
            // Registriamo una rotta dinamica (es. /utente/123/modifica/mario)
            $router->post('/utente/{id:num}/modifica/{nome:alpha}', 'DummyController', 'testMetodoConParametri');

            $router->dispatch('/utente/456/modifica/luigi', 'POST');

            // Verifichiamo che il router abbia estratto '456' e 'luigi' e li abbia passati al metodo
            $this->assertEquals(
                ['456', 'luigi'], 
                \App\Controllers\DummyController::$parametriRicevuti
            );
        }

        public function test_middleware_admin_blocca_utente_normale()
        {
            $router = new Router();
            $router->get('/admin-dashboard', 'DummyController', 'testMetodoSemplice', ['admin']);

            // Simuliamo un utente normale loggato (is_admin = 0)
            $_SESSION['user'] = ['username' => 'studente1', 'is_admin' => 0];

            $this->expectException(ForbiddenException::class);
            $this->expectExceptionMessage("Non hai i permessi da amministratore");

            $router->dispatch('/admin-dashboard', 'GET');
        }

        public function test_middleware_tecnico_blocca_studente()
        {
            $router = new Router();
            $router->get('/gestione-guasti', 'DummyController', 'testMetodoSemplice', ['tecnico']);

            // Simuliamo un utente loggato ma con ruolo studente
            $_SESSION['user'] = ['username' => 'mario', 'ruolo' => 'studente'];

            $this->expectException(ForbiddenException::class);
            $this->expectExceptionMessage("Accesso riservato ai tecnici");

            $router->dispatch('/gestione-guasti', 'GET');
        }

        public function test_middleware_tecnico_permette_accesso_a_tecnici_e_admin()
        {
            $router = new Router();
            $router->get('/gestione-guasti', 'DummyController', 'testMetodoSemplice', ['tecnico']);

            // TEST 1: Entra il Tecnico
            $_SESSION['user'] = ['username' => 'tecnico1', 'ruolo' => 'tecnico'];
            $router->dispatch('/gestione-guasti', 'GET');
            $this->assertEquals(['chiamato_senza_parametri'], \App\Controllers\DummyController::$parametriRicevuti);

            // Puliamo l'array fantasma per il secondo test
            \App\Controllers\DummyController::$parametriRicevuti = [];

            // TEST 2: Entra l'Admin (che il tuo codice autorizza giustamente)
            $_SESSION['user'] = ['username' => 'admin1', 'ruolo' => 'admin'];
            $router->dispatch('/gestione-guasti', 'GET');
            $this->assertEquals(['chiamato_senza_parametri'], \App\Controllers\DummyController::$parametriRicevuti);
        }
    }
}