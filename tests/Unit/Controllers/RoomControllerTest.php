<?php

namespace Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\RoomController;
use PDOStatement;

class RoomControllerTest extends TestCase
{
    protected function mockDatabase($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetchAll')->willReturn($risultatoAtteso);

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('query')->willReturn($mockStmt);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $mockDb = new class($mockPdo, $mockStmt) {
            private $pdo;
            private $stmt;
            
            public function __construct($pdo, $stmt) {
                $this->pdo = $pdo;
                $this->stmt = $stmt;
            }
            
            // Copriamo tutte le possibili funzioni che il tuo Model potrebbe chiamare
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
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);
    }

    public function test_viewRoomList_richiede_login_e_renderizza_aule()
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

        // Controller con requireLogin e render mockati
        $controller = $this->getMockBuilder(RoomController::class)
            ->onlyMethods(['requireLogin', 'render'])
            ->getMock();

        // Controllo Login
        $controller->expects($this->once())->method('requireLogin');

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
}