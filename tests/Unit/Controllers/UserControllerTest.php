<?php

namespace Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Core\Database;
use PDO;
use PDOStatement;
use Exception;

class UserControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];

        // Resettiamo eventuali dati precedenti e iniettiamo 
        // un database finto e innocuo di default per tutti i test!
        $this->resetDatabaseSingleton();
        $mockPdo = $this->createMock(PDO::class);
        $this->injectMockDatabase($mockPdo);
    }

    protected function tearDown(): void
    {
        $this->resetDatabaseSingleton();
    }

    /**
     * Funzione di supporto per iniettare un PDO finto nel Singleton del Database
     */
    private function injectMockDatabase($mockPdo)
    {
        $reflection = new \ReflectionClass(Database::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $mockPdo);
    }

    /**
     * Funzione di supporto per pulire il Singleton
     */
    private function resetDatabaseSingleton()
    {
        $reflection = new \ReflectionClass(Database::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function test_viewRegister_renderizza_pagina()
    {
        // 1. Creiamo un mock parziale del Controller per intercettare solo render()
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // 2. Diciamo che ci aspettiamo venga chiamato render('registerPage')
        $controller->expects($this->once())->method('render')->with('registerPage');

        // 3. Eseguiamo
        $controller->viewRegister();
    }

    public function test_register_fallisce_con_campi_vuoti()
    {
        // Mock parziale per controllare gli input (post) e bloccare il cambio pagina (redirect)
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        // Simuliamo l'utente che non inserisce niente
        $controller->method('post')->willReturn('');
        $controller->expects($this->once())->method('redirect')->with('/register');

        // Eseguiamo
        $controller->register();

        // Assicuriamoci che l'errore sia stato settato in sessione
        $this->assertEquals("Tutti i campi sono obbligatori.", $_SESSION['flash_error']);
    }

    public function test_register_salva_utente_e_reindirizza()
    {
        // 1. Dobbiamo falsificare il Database per il UserModel interno
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        // Iniettiamo il DB finto! Quando UserModel farà Database::getInstance(), riceverà questo.
        $this->injectMockDatabase($mockPdo);

        // 2. Prepariamo il Controller
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        // Simuliamo l'input dell'utente
        $controller->method('post')->willReturnMap([
            ['username', null, 'nuovo_utente'],
            ['password', null, 'Password123!']
        ]);

        // Ci aspettiamo il redirect al login alla fine
        $controller->expects($this->once())->method('redirect')->with('/login');

        // 3. Eseguiamo
        $controller->register();

        // Se arriva fin qui senza errori e tenta il redirect, il test è un successo!
        $this->assertArrayNotHasKey('flash_error', $_SESSION);
    }

    public function test_register_cattura_eccezione_se_utente_gia_esiste()
    {
        // 1. Falsifichiamo il Database facendolo fallire
        $mockPdo = $this->createMock(PDO::class);
        // Simuliamo l'errore tipico di MySQL per una chiave duplicata
        $mockPdo->method('prepare')->willThrowException(new Exception("Duplicate entry"));

        $this->injectMockDatabase($mockPdo);

        // 2. Prepariamo il Controller
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        $controller->method('post')->willReturnMap([
            ['username', null, 'utente_clonato'],
            ['password', null, 'Pass123!']
        ]);

        // Ci aspettiamo il redirect a /register per fargli riprovare
        $controller->expects($this->once())->method('redirect')->with('/register');

        // 3. Eseguiamo
        $controller->register();

        // 4. Asserzione
        $this->assertEquals(
            "Errore durante la registrazione. Il nome utente potrebbe essere già in uso.",
            $_SESSION['flash_error']
        );
    }

    public function test_viewLogin_renderizza_pagina()
    {
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())->method('render')->with('loginPage');

        $controller->viewLogin();
    }

    public function test_login_successo_crea_sessione_e_redirige()
    {
        $passwordInChiaro = "Password123";
        $hash = password_hash($passwordInChiaro, PASSWORD_DEFAULT);

        $utenteFinto = [
            'id' => 1,
            'username' => 'studente_test',
            'role' => 'student',
            'password' => $hash
        ];

        // Mock del Model che restituisce l'utente
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetch')->willReturn($utenteFinto);
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $this->injectMockDatabase($mockPdo);

        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        $controller->method('post')->willReturnMap([
            ['username', null, 'studente_test'],
            ['password', null, $passwordInChiaro]
        ]);

        $controller->expects($this->once())->method('redirect')->with('/');

        $controller->login();

        $this->assertEquals('studente_test', $_SESSION['user']['username']);
    }

    public function test_logout_distrugge_sessione()
    {
        $_SESSION['user'] = ['username' => 'test'];

        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $controller->expects($this->once())->method('redirect')->with('/login');

        $controller->logout();

        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function test_viewHome_mostra_nome_utente_se_loggato()
    {
        // Simuliamo un utente loggato in sessione
        $_SESSION['user'] = ['username' => 'mario_rossi'];

        $controllerMock = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('homePage'),
                $this->callback(function ($data) {
                    // 1. Verifica fondamentale: il nome utente c'è ed è corretto?
                    $this->assertEquals('mario_rossi', $data['NOME_UTENTE']);

                    // 2. Verifichiamo che i nuovi componenti della UI siano stati passati
                    $this->assertArrayHasKey('SEARCH_BANNER', $data);
                    $this->assertArrayHasKey('CREATE_ISSUE_BANNER', $data);
                    $this->assertArrayHasKey('STUDENT_SECTION', $data);

                    return true;
                })
            );

        $controllerMock->viewHome();
    }

    public function test_viewUserList_recupera_utenti_e_renderizza_lista()
    {
        // 1. Prepariamo un array di utenti finti che simula la risposta del database
        $utentiFinti = [
            ['id' => 1, 'username' => 'mario.rossi', 'role' => 'student'],
            ['id' => 2, 'username' => 'luigi.verdi', 'role' => 'tecnico']
        ];

        // 2. Mockiamo il Database: il Model base findAll() di solito usa fetchAll()
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetchAll')->willReturn($utentiFinti);

        $mockPdo = $this->createMock(PDO::class);
        // Intercettiamo sia query() che prepare() perché non sappiamo esattamente come
        // è implementato findAll() nel tuo Model.php base
        $mockPdo->method('query')->willReturn($mockStmt);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $this->injectMockDatabase($mockPdo);

        // 3. Prepariamo il Controller (senza checkAdmin!)
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // 4. Verifichiamo che il render riceva l'HTML generato dall'Helper
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('userListPage'),
                // Usiamo una callback per analizzare l'HTML che il ComponentHelper ha prodotto
                $this->callback(function ($datiPassati) {
                    if (!isset($datiPassati['USER_LIST_ITEMS'])) {
                        return false;
                    }

                    $html = $datiPassati['USER_LIST_ITEMS'];

                    // Se l'Helper ha funzionato bene, i nomi degli utenti finti 
                    // devono essere stati "stampati" dentro la stringa HTML
                    $trovatoMario = strpos($html, 'mario.rossi') !== false;
                    $trovatoLuigi = strpos($html, 'luigi.verdi') !== false;

                    return $trovatoMario && $trovatoLuigi;
                })
            );

        // 5. Eseguiamo l'azione
        $controller->viewUserList();
    }

    public function test_api_addFavorite_blocca_utente_non_loggato()
    {
        $controller = new UserController();

        // La sessione è già vuota grazie al setUp(), quindi non c'è nessun utente loggato

        ob_start(); // Inizia a registrare quello che l'API stampa a schermo
        $controller->addFavorite(3);
        $output = ob_get_clean(); // Ferma la registrazione e salva il testo

        $json = json_decode($output, true);

        $this->assertArrayHasKey('error', $json);
        $this->assertEquals('Devi essere loggato', $json['error']);
    }

    public function test_api_addFavorite_restituisce_json_di_successo()
    {
        $_SESSION['user'] = ['id' => 5, 'username' => 'mario_rossi', 'role' => 'student'];

        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('query')->willReturn($mockStmt);
        $this->injectMockDatabase($mockPdo);

        $controller = new UserController();

        ob_start();
        // L'operatore @ silenzia il Warning degli header scatenato da PHPUnit
        @$controller->addFavorite(3);
        $output = ob_get_clean();

        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $output = substr($output, $jsonStart);
        }

        $json = json_decode($output, true);

        $this->assertTrue($json['success']);
        $this->assertEquals('added', $json['action']);
    }

    public function test_api_removeFavorite_restituisce_json_di_successo()
    {
        $_SESSION['user'] = ['id' => 5, 'username' => 'mario_rossi', 'role' => 'student'];

        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('query')->willReturn($mockStmt);
        $this->injectMockDatabase($mockPdo);

        $controller = new UserController();

        ob_start();
        @$controller->removeFavorite(3);
        $output = ob_get_clean();

        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $output = substr($output, $jsonStart);
        }

        $json = json_decode($output, true);

        $this->assertTrue($json['success']);
        $this->assertEquals('removed', $json['action']);
    }

    public function test_api_isFavorite_restituisce_stato_corretto()
    {
        $_SESSION['user'] = ['id' => 5, 'username' => 'mario_rossi', 'role' => 'student'];

        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        // IL SUPER-MOCK: Qualsiasi colonna cerchi il Model (count, is_favorite, o indice 0), noi ce l'abbiamo e vale 1 (true)!
        $fintoRisultato = ['room_id' => 3, 'count' => 1, 'COUNT(*)' => 1, 0 => 1, 'is_favorite' => 1];

        $mockStmt->method('fetch')->willReturn($fintoRisultato);
        $mockStmt->method('fetchAll')->willReturn([$fintoRisultato]);
        $mockStmt->method('fetchColumn')->willReturn(1);
        $mockStmt->method('rowCount')->willReturn(1);

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('query')->willReturn($mockStmt);
        $this->injectMockDatabase($mockPdo);

        $controller = new UserController();

        ob_start();
        @$controller->isFavorite(3);
        $output = ob_get_clean();

        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $output = substr($output, $jsonStart);
        }

        $json = json_decode($output, true);

        $this->assertArrayHasKey('isFavorite', $json);
        $this->assertTrue($json['isFavorite']);
    }

}