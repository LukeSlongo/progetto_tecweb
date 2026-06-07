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

        $mockDb = new class($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;
            
            public function __construct($pdo, $stmt) {
                $this->pdo = $pdo;
                $this->stmt = $stmt;
            }
            
            public function getConnection() { return $this->pdo; }
            public function prepare($sql = null) { return $this->stmt; }
            public function query($sql = null) { return $this->stmt; }
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
}