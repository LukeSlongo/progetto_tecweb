<?php

use PHPUnit\Framework\TestCase;
use App\Core\Model;

// stub
class TestableModel extends Model
{
    protected $table = 'tabella_di_prova';

    // Sovrascriviamo il costruttore per NON chiamare Database::getInstance()!
    public function __construct() {}

    // Metodo speciale solo per il test: ci permette di iniettare un finto database
    public function setDb($fakeDb)
    {
        $this->db = $fakeDb;
    }
}

// Fantoccio B: Un modello "rotto" senza la tabella definita
class BrokenModel extends Model
{
    // Manca volutamente protected $table!
    public function __construct() {}
}


// =========================================================================
// 2. I TEST VERI E PROPRI
// =========================================================================
class ModelTest extends TestCase
{
    public function test_checkTable_lancia_eccezione_se_tabella_mancante()
    {
        $model = new BrokenModel();

        // Ci aspettiamo che chiamando findAll, il checkTable interno faccia esplodere tutto
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Proprietà \$table non definita nel Model figlio.");

        $model->findAll();
    }

    public function test_query_cattura_eccezioni_pdo_e_le_converte()
    {
        // 1. Creiamo un finto Database (PDO)
        $mockDb = $this->createMock(PDO::class);
        
        // Diciamo al finto DB di lanciare un errore fatale quando si prova a fare una query
        $mockDb->method('prepare')->willThrowException(new PDOException("Connessione persa!"));

        $model = new TestableModel();
        $model->setDb($mockDb);

        // Ci aspettiamo la tua eccezione personalizzata
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Errore Database nella tabella 'tabella_di_prova': Connessione persa!");

        $model->query("SELECT * FROM tabella_di_prova");
    }

    public function test_findAll_esegue_query_corretta_e_ritorna_tutti_i_dati()
    {
        // I dati finti che vogliamo ricevere dal database
        $datiFinti = [
            ['id' => 1, 'nome' => 'Mario'],
            ['id' => 2, 'nome' => 'Luigi']
        ];

        // 1. Mockiamo lo Statement (il risultato della query)
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())->method('execute')->with([]); // Nessun parametro passato
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn($datiFinti);

        // 2. Mockiamo il Database
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())
               ->method('prepare')
               ->with("SELECT * FROM tabella_di_prova")
               ->willReturn($mockStmt);

        // 3. Eseguiamo
        $model = new TestableModel();
        $model->setDb($mockDb);
        $risultati = $model->findAll();

        $this->assertCount(2, $risultati);
        $this->assertEquals('Mario', $risultati[0]['nome']);
    }

    public function test_findById_esegue_query_corretta_e_ritorna_un_singolo_record()
    {
        $datoFinto = ['id' => 5, 'nome' => 'Peach'];

        // Mock Statement
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())->method('execute')->with([5]); // Si aspetta l'ID 5!
        $mockStmt->expects($this->once())->method('fetch')->willReturn($datoFinto);

        // Mock Database
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())
               ->method('prepare')
               ->with("SELECT * FROM tabella_di_prova WHERE id = ?")
               ->willReturn($mockStmt);

        $model = new TestableModel();
        $model->setDb($mockDb);
        $risultato = $model->findById(5);

        $this->assertEquals('Peach', $risultato['nome']);
    }

    public function test_delete_esegue_query_corretta()
    {
        // Mock Statement
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())->method('execute')->with([99]); // Si aspetta l'ID 99

        // Mock Database
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())
               ->method('prepare')
               ->with("DELETE FROM tabella_di_prova WHERE id = ?")
               ->willReturn($mockStmt);

        $model = new TestableModel();
        $model->setDb($mockDb);
        
        // Eseguiamo e ci assicuriamo che non dia errori e che chiami lo statement
        $risultato = $model->delete(99);
        $this->assertSame($mockStmt, $risultato);
    }
}