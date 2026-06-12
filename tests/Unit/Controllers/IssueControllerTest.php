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
                return $this->pdo; }
            public function prepare($sql = null)
            {
                return $this->stmt; }
            public function query($sql = null)
            {
                return $this->stmt; }
        };

        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, $mockDb);
    }

    /**
     * Falsifica il database per query di scrittura (INSERT/UPDATE/DELETE)
     * e permette di simulare un'eccezione
     */
    protected function mockDatabaseInsert($simulaErrore = false)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

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
                return $this->pdo; }
            public function prepare($sql = null)
            {
                return $this->pdo->prepare($sql); }
            public function query($sql = null)
            {
                return $this->stmt; }
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

    /**
     * Funzione di supporto per falsificare il database per l'estrazione SINGOLA (fetch)
     */
    protected function mockDatabaseFetch($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetch')->willReturn($risultatoAtteso);

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
                return $this->pdo; }
            public function prepare($sql = null)
            {
                return $this->stmt; }
            public function query($sql = null)
            {
                return $this->stmt; }
        };

        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, $mockDb);
    }

    // --- I TUOI NUOVI TEST PER IL DETTAGLIO ---

    public function test_viewIssueDetail_lancia_404_se_segnalazione_non_esiste()
    {
        // 1. Il database risponde con false (nessuna issue trovata)
        $this->mockDatabaseFetch(false);

        $controller = new IssueController();

        // 2. Ci aspettiamo l'eccezione 404
        $this->expectException(\App\Exceptions\NotFoundException::class);

        // 3. Eseguiamo
        $controller->viewIssueDetail(999);
    }

    public function test_viewIssueDetail_admin_vede_tutto_bottone_e_reporter()
    {
        // 1. Simuliamo un Admin loggato
        // NOTA: adatta $_SESSION in base a come funziona la tua classe Auth
        $_SESSION['user'] = ['role' => 'admin'];
        $_SESSION['user_id'] = 1; // ID diverso dal reporter

        // 2. Dati finti dal DB
        $issueFinta = [
            'issue_id' => 10,
            'issue_title' => 'Test',
            'issue_description' => 'Test desc',
            'issue_status' => 'open',
            'building_name' => 'Edificio',
            'room_name' => 'Aula 1',
            'opened_at' => '2026-06-11 10:00:00',
            'closed_at' => null,
            'technician_id' => null,
            'reporter_id' => 5 // La issue è di un altro utente (id 5)
        ];
        $this->mockDatabaseFetch($issueFinta);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueDetailPage'),
                $this->callback(function ($dati) {
                    // Admin deve vedere il bottone (has_privileges)
                    $vedeBottone = strpos($dati['DELETE_ISSUE_BUTTON'], '<form') !== false;
                    // Admin deve vedere l'ID del reporter
                    $vedeReporter = strpos($dati['REPORTER_ID'], 'Id utente segnalatore:5') !== false;

                    return $vedeBottone && $vedeReporter;
                })
            );

        $controller->viewIssueDetail(10);
    }

    public function test_viewIssueDetail_owner_vede_bottone_ma_non_reporter_id()
    {
        // 1. Simuliamo la sessione (DEVE AVERE id => 5)
        $_SESSION['user'] = [
            'role' => 'student',
            'id' => 5 // <--- L'ID dell'utente loggato
        ];

        // 2. Dati finti dal DB (DEVE AVERE reporter_id => 5)
        $issueFinta = [
            'issue_id' => 10,
            'issue_title' => 'Test',
            'issue_description' => 'Test desc',
            'issue_status' => 'open',
            'building_name' => 'Edificio',
            'room_name' => 'Aula 1',
            'opened_at' => '2026-06-11 10:00:00',
            'closed_at' => null,
            'technician_id' => null,
            'reporter_id' => 5, // <--- L'ID del proprietario della issue
            'reporter_name' => 'studente.creatore'
        ];

        $this->mockDatabaseFetch($issueFinta);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueDetailPage'),
                $this->callback(function ($dati) {
                    $vedeBottone = strpos($dati['DELETE_ISSUE_BUTTON'], '<form') !== false;
                    $nascondeReporter = $dati['REPORTER_ID'] === '';
                    return $vedeBottone && $nascondeReporter;
                })
            );

        $controller->viewIssueDetail(10);
    }
    public function test_viewIssueDetail_studente_non_owner_non_vede_nulla_di_privato()
    {
        // 1. Simuliamo uno studente "ficcanaso" che guarda una issue non sua
        $_SESSION['user'] = ['role' => 'student'];
        $_SESSION['user_id'] = 99; // ID diverso dal reporter_id!

        $issueFinta = [
            'issue_id' => 10,
            'issue_title' => 'Test',
            'issue_description' => 'Test desc',
            'issue_status' => 'open',
            'building_name' => 'Edificio',
            'room_name' => 'Aula 1',
            'opened_at' => '2026-06-11 10:00:00',
            'closed_at' => null,
            'technician_id' => null,
            'reporter_id' => 5 // La issue è di un altro
        ];
        $this->mockDatabaseFetch($issueFinta);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('issueDetailPage'),
                $this->callback(function ($dati) {
                    // Controlliamo che il controller nasconda tutto correttamente (stringhe vuote)
                    $nessunReporter = $dati['REPORTER_ID'] === '';
                    $nessunTastoElimina = $dati['DELETE_ISSUE_BUTTON'] === '';
                    $nessunTastoPrendi = $dati['TAKE_ISSUE_BUTTON'] === '';
                    $nessunTastoChiudi = $dati['CLOSE_ISSUE_BUTTON'] === '';

                    return $nessunReporter && $nessunTastoElimina && $nessunTastoPrendi && $nessunTastoChiudi;
                })
            );

        $controller->viewIssueDetail(10);
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

        $this->mockDatabaseInsert(false);

        $controller = $this->getMockBuilder(IssueController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $controller->expects($this->once())->method('redirect')->with('/issues/10');

        $controller->closeIssue(10);

        $this->assertEquals("Segnalazione risolta e chiusa con successo.", $_SESSION['flash_success']);
    }
}