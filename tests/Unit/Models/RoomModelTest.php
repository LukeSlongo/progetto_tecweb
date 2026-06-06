<?php

namespace Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\RoomModel;
use PDO;
use PDOStatement;

class RoomModelTest extends TestCase
{
    /**
     * Funzione di supporto per falsificare il database per richieste SINGOLE (fetch)
     */
    protected function mockDatabaseFetch($risultatoAtteso)
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        // Attenzione: qui mockiamo 'fetch' e non 'fetchAll' perché ci aspettiamo 1 solo record!
        $mockStmt->method('fetch')->willReturn($risultatoAtteso);

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
        // Pulizia del database finto
        $reflection = new \ReflectionClass(\App\Core\Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);
    }

    public function test_getRoomWithBuilding_ritorna_dati_corretti_se_aula_esiste()
    {
        // 1. Prepariamo i dati finti di una singola aula unita all'edificio (come farebbe la JOIN)
        $aulaFinta = [
            'room_id' => 1,
            'room_name' => 'Aula Magna',
            'building_id' => 1,
            'building_name' => 'Edificio A',
            'building_address' => 'Via Roma 10, Campus Centrale'
        ];

        // 2. Iniettiamo i dati finti nel database
        $this->mockDatabaseFetch($aulaFinta);

        // 3. Eseguiamo il metodo
        $model = new RoomModel();
        $risultato = $model->getRoomWithBuilding(1);

        // 4. Verifiche
        $this->assertIsArray($risultato);
        $this->assertEquals('Aula Magna', $risultato['room_name']);
        $this->assertEquals('Edificio A', $risultato['building_name']);
        
        // Verifichiamo che non sia un array multidimensionale (non deve esserci l'indice [0])
        $this->assertArrayHasKey('building_address', $risultato);
    }

    public function test_getRoomWithBuilding_ritorna_false_se_aula_non_esiste()
    {
        // 1. Se PDO non trova nessuna riga, la funzione fetch() nativa restituisce 'false'
        $this->mockDatabaseFetch(false);

        // 2. Cerchiamo un'aula inesistente (es. id 999)
        $model = new RoomModel();
        $risultato = $model->getRoomWithBuilding(999);

        // 3. Verifichiamo che il Model ci restituisca esattamente false
        // Questo è fondamentale perché il nostro Controller usa proprio "if (!$room_data)" per mostrare l'errore 404!
        $this->assertFalse($risultato);
    }
}