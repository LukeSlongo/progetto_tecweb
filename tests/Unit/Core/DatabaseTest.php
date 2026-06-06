<?php

use PHPUnit\Framework\TestCase;
use App\Core\Database;

class DatabaseTest extends TestCase
{

    public function test_impossibile_clonare_il_singleton()
    {
        // Ci aspettiamo un Error nativo di PHP perché il metodo __clone è private
        $this->expectException(Error::class);

        // Usiamo la Reflection per creare una classe senza chiamare il costruttore
        $reflection = new \ReflectionClass(Database::class);
        $istanza = $reflection->newInstanceWithoutConstructor();

        // Tentiamo di clonarla: qui PHP deve bloccarci!
        $clonata = clone $istanza;
    }

    public function test_impossibile_deserializzare_il_singleton()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Non è possibile deserializzare un singleton.");

        $reflection = new \ReflectionClass(Database::class);
        $istanza = $reflection->newInstanceWithoutConstructor();

        // Chiamiamo il tuo metodo __wakeup
        $istanza->__wakeup();
    }

    public function test_get_instance_cattura_eccezione_pdo()
    {
        // Visto che il nostro MySQL non è configurato per i test, 
        // ci aspettiamo che PDO fallisca e che la tua classe catturi l'errore
        // e rilanci la tua Eccezione personalizzata.
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Errore di connessione al database/');

        // Invochiamo il metodo vero
        Database::getInstance();
        
        // Puliamo l'istanza alla fine per non sporcare altri test futuri
        $reflection = new \ReflectionClass(Database::class);
        $proprieta = $reflection->getProperty('instance');
        $proprieta->setAccessible(true);
        $proprieta->setValue(null, null);
    }
}