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

    public function test_viewRegister_reindirizza_se_loggato_altrimenti_renderizza()
    {
        // 1. Creiamo un mock parziale del Controller per intercettare render() e requireGuest()
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['requireGuest', 'render'])
            ->getMock();

        // 2. Diciamo che ci aspettiamo venga chiamato requireGuest() e poi render('registerPage')
        $controller->expects($this->once())->method('requireGuest');
        $controller->expects($this->once())->method('render')->with('registerPage');

        // 3. Eseguiamo
        $controller->viewRegister();
    }

    public function test_register_fallisce_con_campi_vuoti()
    {
        // Mock parziale per controllare gli input (post) e bloccare il cambio pagina (redirect)
        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['requireGuest', 'post', 'redirect'])
            ->getMock();

        $controller->expects($this->once())->method('requireGuest');

        // Simuliamo l'utente che non inserisce niente
        $controller->method('post')->willReturn('');

        // Verifichiamo che il redirect avvenga verso /login
        $controller->expects($this->once())->method('redirect')->with('/login');

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
            ->onlyMethods(['requireGuest', 'post', 'redirect'])
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
            ->onlyMethods(['requireGuest', 'post', 'redirect'])
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
            ->onlyMethods(['requireGuest', 'render'])
            ->getMock();

        $controller->expects($this->once())->method('requireGuest');
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
            ->onlyMethods(['requireGuest', 'post', 'redirect'])
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

        $controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['requireLogin', 'render'])
            ->getMock();

        $controller->expects($this->once())->method('requireLogin');
        $controller->expects($this->once())
            ->method('render')
            ->with('homePage', ['NOME_UTENTE' => 'mario_rossi']);

        $controller->viewHome();
    }
}