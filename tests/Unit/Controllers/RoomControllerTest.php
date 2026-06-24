<?php

namespace Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\RoomController;
use App\Exceptions\NotFoundException;
use PDOStatement;
use PDO;

class RoomControllerTest extends TestCase
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

    /**
     * Falsifica il database per Controller che usano sia fetch() che fetchAll()
     */
    protected function mockDatabaseFetchAndAll($risultatoFetchSingolo, $risultatoFetchAllLista)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        
        // Risposta per estrazione singola (es. getRoomWithBuilding)
        $mockStmt->method('fetch')->willReturn($risultatoFetchSingolo);
        
        // Risposta per estrazione multipla (es. getIssuesByRoom)
        $mockStmt->method('fetchAll')->willReturn($risultatoFetchAllLista);

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
        // 1. Pulizia Database
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);

        // 2. Pulizia variabili globali per la ricerca
        $_GET = [];
    }

    public function test_viewRoomList_renderizza_aule()
    {
        $auleFinte = [
            [
                'ROOM_ID' => '1',
                'ROOM_NAME' => 'Aula Magna',
                'BUILDING' => 'Edificio A',
                'NUM_REPORTS' => '0'
            ]
        ];
        $this->mockDatabase($auleFinte);

        // Mockiamo SOLO il render
        $controller = $this->getMockBuilder(RoomController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Controllo Render
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('roomListPage'),
                $this->callback(function ($dati) {
                    return isset($dati['ROOM_LIST_ITEMS']) && strpos($dati['ROOM_LIST_ITEMS'], 'Aula Magna') !== false;
                })
            );

        $controller->viewRoomList();
    }

    public function test_viewRoomList_con_ricerca_gestisce_il_parametro_get()
    {
        // Simuliamo la ricerca
        $_GET['search'] = 'Informatica';

        $auleFinteFiltrate = [
            [
                'ROOM_ID' => '2', 
                'ROOM_NAME' => 'Laboratorio Informatica', 
                'BUILDING' => 'Edificio B', 
                'NUM_REPORTS' => '3'
            ]
        ];
        $this->mockDatabase($auleFinteFiltrate);

        // Mockiamo SOLO il render
        $controller = $this->getMockBuilder(RoomController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Verifichiamo il render filtrato
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('roomListPage'),
                $this->callback(function ($dati) {
                    $haRisultatoCorretto = strpos($dati['ROOM_LIST_ITEMS'], 'Laboratorio Informatica') !== false;
                    $nonHaAltriRisultati = strpos($dati['ROOM_LIST_ITEMS'], 'Aula Magna') === false;
                    
                    return $haRisultatoCorretto && $nonHaAltriRisultati;
                })
            );

        $controller->viewRoomList();
    }

    public function test_viewRoomDetail_recupera_dati_e_renderizza_pagina()
    {
        // 1. Prepariamo l'aula finta (Risposta singola)
        $aulaFinta = [
            'room_id' => 5,
            'room_name' => 'Laboratorio Chimica',
            'building_name' => 'Edificio C',
            'building_address' => 'Via delle Scienze'
        ];

        // 2. Prepariamo le segnalazioni finte (Risposta lista)
        $segnalazioniFinte = [
            ['issue_id' => 1, 'issue_title' => 'Tubo rotto', 'opened_at' => '2026-06-06', 'issue_status' => 'open']
        ];

        // Iniettiamo entrambe le risposte!
        $this->mockDatabaseFetchAndAll($aulaFinta, $segnalazioniFinte);

        // 3. Prepariamo il Controller intercettando SOLO render
        $controller = $this->getMockBuilder(RoomController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // 4. Verifichiamo il pacchetto dati
        $controller->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('roomDetailPage'),
                $this->callback(function ($dati) {
                    $nomeAulaOk = $dati['ROOM_NAME'] === 'Laboratorio Chimica';
                    $edificioOk = $dati['BUILDING_NAME'] === 'Edificio C';
                    $htmlIssuesOk = strpos($dati['ISSUES_LIST'], 'Tubo rotto') !== false;

                    return $nomeAulaOk && $edificioOk && $htmlIssuesOk;
                })
            );

        // 5. Eseguiamo
        $controller->viewRoomDetail(5);
    }

    public function test_viewRoomDetail_lancia_404_se_aula_inesistente()
    {
        // 1. Il database non trova niente: fetch() restituirà false
        // Non ci servono segnalazioni finte, tanto si fermerà prima!
        $this->mockDatabaseFetchAndAll(false, []);

        // 2. Istanziamo il controller senza mockare il render
        $controller = new RoomController();

        // 3. Ci aspettiamo che il controller tiri il freno a mano e lanci l'eccezione 404
        // (Assicurati di aver messo l'if (!$room_data) nel tuo Controller come dicevamo prima!)
        $this->expectException(NotFoundException::class);

        // 4. Eseguiamo con un ID inesistente
        $controller->viewRoomDetail(999);
    }
}