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

    /**
     * Funzione di supporto per falsificare il database per l'estrazione SINGOLA (fetch / fetchOne)
     */
    protected function mockDatabaseFetch($risultatoAtteso)
    {
        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->method('fetch')->willReturn($risultatoAtteso);

        $mockPdo = $this->createMock(\PDO::class);
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

    // --- I TEST PER IL DETTAGLIO ISSUE ---

    public function test_getIssueDetails_ritorna_dati_completi_se_esiste()
    {
        // 1. Prepariamo un pacchetto di dati "perfetto" che simula il risultato di tutte le JOIN
        $issueFinta = [
            'issue_id' => 10,
            'issue_title' => 'Proiettore bruciato',
            'issue_description' => 'Si sente odore di fumo dalla presa',
            'issue_status' => 'in_progress',
            'opened_at' => '2026-06-11 18:00:00',
            'closed_at' => null,
            'building_name' => 'Edificio A',
            'room_name' => 'Aula Magna',
            'reporter_id' => 3,
            'reporter_name' => 'mario.rossi',
            'technician_id' => 2,
            'technician_name' => 'tecnico.luigi'
        ];

        // 2. Iniettiamo i dati finti per la risposta di fetchOne
        $this->mockDatabaseFetch($issueFinta);

        // 3. Eseguiamo il metodo
        $model = new \App\Models\IssueModel();
        $risultato = $model->getIssueDetails(10);

        // 4. Verifichiamo che i dati delle tabelle collegate siano stati estratti e impacchettati bene
        $this->assertIsArray($risultato);
        $this->assertEquals('Proiettore bruciato', $risultato['issue_title']);

        // Verifiche sulle JOIN di Edifici e Aule
        $this->assertEquals('Edificio A', $risultato['building_name']);
        $this->assertEquals('Aula Magna', $risultato['room_name']);

        // Verifiche sulle LEFT JOIN degli utenti
        $this->assertEquals(3, $risultato['reporter_id']);
        $this->assertEquals('tecnico.luigi', $risultato['technician_name']);
    }

    public function test_getIssueDetails_ritorna_false_se_id_non_esiste()
    {
        // 1. Se PDO non trova nessuna corrispondenza per quell'ID, restituisce false
        $this->mockDatabaseFetch(false);

        // 2. Cerchiamo una issue inesistente
        $model = new \App\Models\IssueModel();
        $risultato = $model->getIssueDetails(999);

        // 3. Ci assicuriamo che il Model propaghi il "false", fondamentale per far scattare il 404 nel Controller!
        $this->assertFalse($risultato);
    }
}