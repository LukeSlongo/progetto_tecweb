<?php

use PHPUnit\Framework\TestCase;
use App\Core\Auth;

class AuthTest extends TestCase
{
    // Il metodo setUp() viene eseguito AUTOMATICAMENTE prima di ogni singolo test.
    // Lo usiamo per "pulire" la sessione, così i test non si influenzano a vicenda.
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_isLogged_ritorna_falso_se_nessun_utente_in_sessione()
    {
        $this->assertFalse(Auth::isLogged());
    }

    public function test_isLogged_ritorna_vero_se_utente_in_sessione()
    {
        $_SESSION['user'] = ['username' => 'mario.rossi'];
        $this->assertTrue(Auth::isLogged());
    }

   public function test_isStudent_funziona_correttamente()
    {
        $_SESSION['user'] = ['username' => 'test', 'role' => 'student'];
        $this->assertTrue(Auth::isStudent());
    }

    public function test_isAdmin_verifica_correttamente_il_flag()
    {
        $_SESSION['user'] = ['username' => 'admin', 'role' => 'admin'];
        $this->assertTrue(Auth::isAdmin());
    }

    public function test_isOwner_verifica_la_corrispondenza_dell_id()
    {
        // Simuliamo la sessione con un ID utente
        $_SESSION['user'] = ['id' => 5];

        // Verifichiamo che riconosca l'ID corretto (sia come intero che come stringa del DB)
        $this->assertTrue(Auth::isOwner(5));
        $this->assertTrue(Auth::isOwner('5')); 

        // Verifichiamo che blocchi gli estranei
        $this->assertFalse(Auth::isOwner(99));
    }

    public function test_getHeaderLinks_ritorna_html_corretto_per_admin()
    {
        $_SESSION['user'] = ['username' => 'admin', 'role' => 'admin'];
        $html = Auth::getHeaderLinks();
        $this->assertStringContainsString('href="/users"', $html);
    }

    public function test_getHeaderLinks_ritorna_html_corretto_per_utenti_loggati()
    {
        // Simuliamo un utente standard
        $_SESSION['user'] = ['username' => 'studente1'];
        
        $html = Auth::getHeaderLinks();
        $this->assertStringContainsString('href="/profilo"', $html);
        $this->assertStringNotContainsString('Admin', $html); // Non deve esserci il link admin
    }
}