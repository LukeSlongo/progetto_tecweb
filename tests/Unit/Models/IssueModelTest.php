<?php

namespace Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\IssueModel;
use PDO;
use PDOStatement;

class IssueModelTest extends TestCase
{
    /**
     * Funzione di supporto per falsificare il database
     */
    protected function mockDatabase($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetchAll')->willReturn($risultatoAtteso);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('query')->willReturn($mockStmt);

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
     * Falsifica il database per query che restituiscono una singola riga (fetchOne/fetch)
     */
    protected function mockDatabaseFetchOne($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetch')->willReturn($risultatoAtteso);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('query')->willReturn($mockStmt);

        $mockDb = new class ($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;
            public function __construct($pdo, $stmt)
            {
                $this->pdo = $pdo;
                $this->stmt = $stmt; }
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
     */
    protected function mockDatabaseExecute()
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $mockDb = new class ($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;
            public function __construct($pdo, $stmt)
            {
                $this->pdo = $pdo;
                $this->stmt = $stmt; }
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

    protected function tearDown(): void
    {
        // Pulizia: svuotiamo il database finto dopo ogni test
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);
    }

    public function test_getIssuesByRoom_ritorna_lista_segnalazioni_se_esistono()
    {
        // 1. Prepariamo i dati finti che il database dovrebbe restituire
        $segnalazioniFinte = [
            [
                'issue_id' => 1,
                'issue_title' => 'Proiettore non funzionante',
                'opened_at' => '2026-06-06',
                'issue_status' => 'open'
            ],
            [
                'issue_id' => 2,
                'issue_title' => 'Sedie rotte',
                'opened_at' => '2026-06-06',
                'issue_status' => 'in_progress'
            ]
        ];

        // 2. Iniettiamo i dati finti
        $this->mockDatabase($segnalazioniFinte);

        // 3. Istanziamo il Model e chiamiamo la funzione
        $model = new IssueModel();
        $risultato = $model->getIssuesByRoom(1); // L'ID non importa davvero perché il DB è finto

        // 4. Verifichiamo che i dati tornino esattamente come ce li aspettiamo
        $this->assertIsArray($risultato);
        $this->assertCount(2, $risultato);

        // Controlliamo il contenuto del primo elemento
        $this->assertEquals(1, $risultato[0]['issue_id']);
        $this->assertEquals('Proiettore non funzionante', $risultato[0]['issue_title']);
        $this->assertEquals('open', $risultato[0]['issue_status']);
    }

    public function test_getIssuesByRoom_ritorna_array_vuoto_se_non_ci_sono_segnalazioni()
    {
        // 1. Se la stanza non ha issue, fetchAll restituisce un array vuoto
        $this->mockDatabase([]);

        // 2. Istanziamo il Model e chiamiamo la funzione (es. una stanza appena creata)
        $model = new IssueModel();
        $risultato = $model->getIssuesByRoom(99);

        // 3. Verifichiamo che il risultato sia un array vuoto, e non false o null
        $this->assertIsArray($risultato);
        $this->assertEmpty($risultato);
    }

    public function test_getIssuesByStatus_senza_parametro_ritorna_tutte_le_segnalazioni()
    {
        // 1. Prepariamo un mix di segnalazioni finte (es. una aperta e una chiusa)
        $segnalazioniMiste = [
            ['issue_id' => 1, 'issue_title' => 'Computer rotto', 'issue_status' => 'open', 'issue_room' => 'Aula 1'],
            ['issue_id' => 2, 'issue_title' => 'Luce guasta', 'issue_status' => 'closed', 'issue_room' => 'Aula 2']
        ];

        // 2. Iniettiamo i dati nel database finto
        $this->mockDatabase($segnalazioniMiste);

        // 3. Chiamiamo il metodo SENZA passare nulla
        $model = new IssueModel();
        $risultato = $model->getIssuesByStatus(); // o getIssuesByStatus(null)

        // 4. Verifichiamo che ci torni esattamente tutto il blocco intero
        $this->assertIsArray($risultato);
        $this->assertCount(2, $risultato);
        $this->assertEquals('Computer rotto', $risultato[0]['issue_title']);
    }

    public function test_getIssuesByStatus_con_parametro_ritorna_solo_quelle_richieste()
    {
        // 1. Prepariamo solo segnalazioni "in_progress"
        $segnalazioniFiltrate = [
            ['issue_id' => 3, 'issue_title' => 'Sedia rotta', 'issue_status' => 'in_progress', 'issue_room' => 'Aula 3']
        ];

        // 2. Iniettiamo i dati
        $this->mockDatabase($segnalazioniFiltrate);

        // 3. Chiamiamo il metodo PASSANDO il filtro
        $model = new IssueModel();
        $risultato = $model->getIssuesByStatus('in_progress');

        // 4. Verifiche
        $this->assertIsArray($risultato);
        $this->assertCount(1, $risultato);

        // Verifichiamo che il dato passato mantenga la struttura corretta
        $this->assertEquals('in_progress', $risultato[0]['issue_status']);
        $this->assertEquals('Aula 3', $risultato[0]['issue_room']);
    }


    // =================================================================
    // TEST: find_issue
    // =================================================================

    public function test_find_issue_ritorna_i_dati_corretti()
    {
        $datiFinti = ['id' => 5, 'title' => 'Segnalazione 5', 'status' => 'open'];
        $this->mockDatabaseFetchOne($datiFinti);

        $model = new IssueModel();
        $risultato = $model->find_issue(5);

        $this->assertIsArray($risultato);
        $this->assertEquals('Segnalazione 5', $risultato['title']);
    }

    public function test_find_issue_ritorna_false_se_non_esiste()
    {
        // Se il DB non trova nulla, fetch() restituisce false
        $this->mockDatabaseFetchOne(false);

        $model = new IssueModel();
        $risultato = $model->find_issue(999);

        $this->assertFalse($risultato);
    }

    // =================================================================
    // TEST: getIssueDetails
    // =================================================================

    public function test_getIssueDetails_recupera_tutte_le_informazioni_delle_join()
    {
        $dettagliFinti = [
            'issue_id' => 10,
            'issue_title' => 'Problema gravissimo',
            'room_name' => 'Aula Magna',
            'building_name' => 'Edificio A',
            'reporter_name' => 'mario.rossi'
        ];
        $this->mockDatabaseFetchOne($dettagliFinti);

        $model = new IssueModel();
        $risultato = $model->getIssueDetails(10);

        $this->assertIsArray($risultato);
        $this->assertEquals('Problema gravissimo', $risultato['issue_title']);
        $this->assertEquals('Aula Magna', $risultato['room_name']);
        $this->assertEquals('mario.rossi', $risultato['reporter_name']);
    }

    // =================================================================
    // TEST: registerIssue
    // =================================================================

    public function test_registerIssue_esegue_inserimento_con_successo()
    {
        // Prepariamo il DB finto per rispondere "OK" a un'esecuzione (execute)
        $this->mockDatabaseExecute();

        $model = new IssueModel();

        // Passiamo i dati finti: user_id=1, room_id=2, titolo, descrizione
        $risultato = $model->registerIssue(1, 2, 'Computer bruciato', 'Puzza di fumo');

        // Nel tuo Model base, la funzione query() restituisce l'oggetto PDOStatement su successo
        $this->assertInstanceOf(PDOStatement::class, $risultato);
    }
}