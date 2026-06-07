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
        // 1. Pulizia Database
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);

        // 2. Pulizia dell'array globale GET tra un test e l'altro
        $_GET = [];
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
}