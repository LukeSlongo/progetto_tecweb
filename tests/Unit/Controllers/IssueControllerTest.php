<?php

namespace Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\IssueController;
use PDOStatement;
use PDO;

class IssueControllerTest extends TestCase
{
    /**
     * Falsifica il database per query che restituiscono una lista (fetchAll)
     */
    protected function mockDatabase($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetchAll')->willReturn($risultatoAtteso);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('query')->willReturn($mockStmt);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $mockDb = new class ($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;

            public function __construct($pdo, $stmt)
            {
                $this->pdo = $pdo;
                $this->stmt = $stmt;
            }

            public function getConnection()
            {
                return $this->pdo; 
            }
            
            public function prepare($sql = null)
            {
                return $this->stmt; 
            }
            
            public function query($sql = null)
            {
                return $this->stmt; 
            }
        };

        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, $mockDb);
    }

    /**
     * Falsifica il database per query di scrittura (INSERT/UPDATE/DELETE)
     * e permette di simulare un'eccezione
     */
    protected function mockDatabaseInsert($simulaErrore = false, $fetchResult = null)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn($fetchResult);

        $mockPdo = $this->createMock(PDO::class);

        if ($simulaErrore) {
            $mockPdo->method('prepare')->willThrowException(new \Exception("Errore DB"));
        } else {
            $mockPdo->method('prepare')->willReturn($mockStmt);
        }

        $mockDb = new class ($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;
            public function __construct($pdo, $stmt)
            {
                $this->pdo = $pdo;
                $this->stmt = $stmt;
            }
            public function getConnection()
            {
                return $this->pdo; 
            }
            public function prepare($sql = null)
            {
                return $this->pdo->prepare($sql); 
            }
            public function query($sql = null)
            {
                return $this->stmt; 
            }
        };

        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, $mockDb);
    }

    protected function tearDown(): void
    {
        // 1. Pulizia Database
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);

        // 2. Pulizia dell'array globale GET tra un test e l'altro
        $_GET = [];

        // 3. Pulizia della sessione
        $_SESSION = [];
    }

    public function test_searchIssues_passa_correttamente_i_dati_dal_model()
    {
        // 1. Prepariamo dei dati finti che ci aspettiamo dal DB
        $segnalazioniFinte = [
            ['issue_id' => 1, 'issue_title' => 'Vetro rotto', 'issue_status' => 'open']
        ];
        $this->mockDatabase($segnalazioniFinte);

        // 2. Istanziamo il controller (senza mock, vogliamo testare la logica reale)
        $controller = new IssueController();

        // 3. Eseguiamo la ricerca
        $risultato = $controller->searchIssues('open');

        // 4. Verifichiamo che il Controller abbia fatto da tramite perfetto
        $this->assertIsArray($risultato);
        $this->assertEquals('Vetro rotto', $risultato[0]['issue_title']);
    }

    public function test_viewIssueList_senza_filtro_seleziona_il_radio_tutti()
    {
        // DB Vuoto per isolare il test alla logica dei bottoni
        $this->mockDatabase([]);

        // Mockiamo SOLO il render
        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Ci aspettiamo che, senza parametro GET, "CHECKED_ALL" sia "checked"
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueListPage'),
                $this->callback(function ($dati) {
                    $tuttiSelezionato = $dati['CHECKED_ALL'] === 'checked';
                    $apertiVuoto = $dati['CHECKED_OPEN'] === '';

                    return $tuttiSelezionato && $apertiVuoto;
                })
            );

        // Chiamata senza aver impostato $_GET['status']
        $controller->viewIssueList();
    }

    public function test_viewIssueList_con_filtro_seleziona_il_radio_corretto()
    {
        $this->mockDatabase([]);

        // Simuliamo l'utente che clicca sul radio button "in_progress"
        $_GET['status'] = 'in_progress';

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Ci aspettiamo che SOLO "CHECKED_IN_PROGRESS" sia a "checked"
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueListPage'),
                $this->callback(function ($dati) {
                    $inLavorazioneSelezionato = $dati['CHECKED_IN_PROGRESS'] === 'checked';
                    $tuttiVuoto = $dati['CHECKED_ALL'] === '';

                    return $inLavorazioneSelezionato && $tuttiVuoto;
                })
            );

        $controller->viewIssueList();
    }

    // =================================================================
    // TEST: viewIssueForm
    // =================================================================

    public function test_viewIssueForm_renderizza_pagina_con_optgroup_html()
    {
        // 1. Usiamo la tua funzione esistente: BuildingModel e RoomModel 
        // chiameranno fetchAll() e riceveranno questo array.
        $datiFinti = [
            ['id' => 1, 'name' => 'Edificio/Aula Test', 'building_id' => 1]
        ];
        $this->mockDatabase($datiFinti);

        // 2. Mock del Controller per intercettare il render
        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // 3. Verifichiamo che i dati siano passati correttamente nel layout
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueFormPage'),
                $this->callback(function ($data) {
                    // 1. Verifichiamo che i vecchi JSON non esistano più
                    $this->assertArrayNotHasKey('BUILDINGS_JSON', $data);
                    $this->assertArrayNotHasKey('ROOMS_JSON', $data);

                    // 2. Verifichiamo che le chiavi HTML siano presenti
                    $this->assertArrayHasKey('BUILDING_OPTIONS', $data);
                    $this->assertArrayHasKey('ROOM_OPTIONS', $data);

                    // 3. Verifichiamo che ROOM_OPTIONS contenga i nuovi tag nativi per l'accessibilità
                    $this->assertStringContainsString('<optgroup label=', $data['ROOM_OPTIONS']);

                    return true;
                })
            );

        // Eseguiamo la funzione
        $controller->viewIssueForm();
    }

    // =================================================================
    // TEST: saveIssue
    // =================================================================

    public function test_saveIssue_fallisce_con_campi_vuoti()
    {
        // 1. Inseriamo un DB finto per non far crashare il costruttore dell'IssueController
        $this->mockDatabase([]);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        // Simuliamo l'assenza di dati
        $controller->method('post')->willReturn('');

        $controller->expects($this->once())->method('redirect')->with('/issues/new');

        $controller->saveIssue();

        $this->assertEquals("Tutti i campi (Aula, Titolo, Descrizione) sono obbligatori.", $_SESSION['flash_error']);
    }

    public function test_saveIssue_fallisce_se_utente_non_loggato()
    {
        // La sessione è vuota (pulita dal tearDown)

        // 1. Inseriamo un DB finto per non far crashare il costruttore
        $this->mockDatabase([]);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        // Passiamo dati validi nel form, così superiamo il primo controllo sui campi vuoti
        $controller->method('post')->willReturnMap([
            ['room_id', null, '3'],
            ['issue_title', null, 'Titolo Test'],
            ['issue_description', null, 'Descrizione Test']
        ]);

        // Se l'utente non è loggato, il controller deve fare redirect al login
        $controller->expects($this->once())
            ->method('redirect')
            ->with('/login');

        $controller->saveIssue();

        $this->assertEquals("Sessione scaduta, effettua nuovamente il login.", $_SESSION['flash_error']);
    }

    public function test_saveIssue_salva_correttamente_e_reindirizza_alla_home()
    {
        // 1. Simuliamo l'utente loggato
        $_SESSION['user'] = ['id' => 5, 'username' => 'studente'];

        // 2. Usiamo la nuova funzione per simulare il DB che salva con successo
        $this->mockDatabaseInsert(false);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        $controller->method('post')->willReturnMap([
            ['room_id', null, '3'],
            ['issue_title', null, 'Titolo Test'],
            ['issue_description', null, 'Descrizione Test']
        ]);

        $controller->expects($this->once())->method('redirect')->with('/');

        $controller->saveIssue();

        $this->assertEquals("Segnalazione inviata con successo!", $_SESSION['flash_success']);
    }

    public function test_saveIssue_cattura_eccezione_dal_database()
    {
        // 1. Utente loggato
        $_SESSION['user'] = ['id' => 5, 'username' => 'studente'];

        // 2. Simuliamo che il DB "esploda" (es. constraint violation)
        $this->mockDatabaseInsert(true);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['post', 'redirect'])
            ->getMock();

        $controller->method('post')->willReturnMap([
            ['room_id', null, '999'],
            ['issue_title', null, 'Titolo'],
            ['issue_description', null, 'Descrizione']
        ]);

        // Deve rimandare indietro al form con l'errore
        $controller->expects($this->once())->method('redirect')->with('/issues/new');

        $controller->saveIssue();

        $this->assertEquals("Errore durante l'invio della segnalazione. Riprova.", $_SESSION['flash_error']);
    }

    // =================================================================
    // TEST: takeIssue (Prendi in carico)
    // =================================================================

    public function test_takeIssue_fallisce_se_utente_non_tecnico()
    {
        // 1. Mettiamo in sessione un utente loggato ma con ruolo "student"
        $_SESSION['user'] = ['id' => 3, 'username' => 'studente', 'role' => 'student'];

        // AGGIUNTA FONDAMENTALE: Inseriamo il db finto per non far crashare il costruttore!
        $this->mockDatabaseInsert(false);

        // 2. Mockiamo il controller per intercettare il redirect
        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        // 3. Ci aspettiamo che venga bloccato e rimbalzato sulla pagina della issue
        $controller->expects($this->once())->method('redirect')->with('/issues/10');

        $controller->takeIssue(10);

        // 4. Verifichiamo che il messaggio di errore sia stato impostato correttamente
        $this->assertEquals("Azione non consentita. Solo i tecnici possono prendere in carico le segnalazioni.", $_SESSION['flash_error']);
    }

    public function test_takeIssue_esegue_con_successo_per_tecnico()
    {
        // 1. Utente loggato con ruolo "technician"
        $_SESSION['user'] = ['id' => 2, 'username' => 'tecnico', 'role' => 'technician'];

        // 2. Prepariamo il DB a rispondere "OK" (senza errori)
        $this->mockDatabaseInsert(false);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        // Deve fare redirect alla stessa pagina per ricaricare i dati aggiornati
        $controller->expects($this->once())->method('redirect')->with('/issues/10');

        $controller->takeIssue(10);

        // Verifichiamo il messaggio di successo
        $this->assertEquals("Hai preso in carico la segnalazione con successo.", $_SESSION['flash_success']);
    }

    // =================================================================
    // TEST: closeIssue (Risolvi)
    // =================================================================

    public function test_closeIssue_fallisce_se_utente_non_tecnico()
    {
        // 1. Sessione vuota (simula utente sloggato) oppure utente base
        $_SESSION['user'] = ['id' => 3, 'username' => 'studente', 'role' => 'student'];

        // AGGIUNTA FONDAMENTALE: Inseriamo il db finto per non far crashare il costruttore!
        $this->mockDatabaseInsert(false);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $controller->expects($this->once())->method('redirect')->with('/issues/10');

        $controller->closeIssue(10);

        $this->assertEquals("Azione non consentita. Solo i tecnici possono risolvere le segnalazioni.", $_SESSION['flash_error']);
    }

    public function test_closeIssue_esegue_con_successo_per_tecnico()
    {
        $_SESSION['user'] = ['id' => 2, 'username' => 'tecnico', 'role' => 'technician'];

        $this->mockDatabaseInsert(false, ['technician_id' => 2]);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $controller->expects($this->once())->method('redirect')->with('/issues/10');

        $controller->closeIssue(10);

        $this->assertEquals("Segnalazione risolta e chiusa con successo.", $_SESSION['flash_success']);
    }
}
