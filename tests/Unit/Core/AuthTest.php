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
        // 1. Nessun utente loggato
        $this->assertFalse(Auth::isStudent());

        // 2. Loggato come tecnico (non deve essere studente)
        $_SESSION['user'] = ['ruolo' => 'tecnico'];
        $this->assertFalse(Auth::isStudent());

        // 3. Loggato come studente
        $_SESSION['user'] = ['ruolo' => 'studente'];
        $this->assertTrue(Auth::isStudent());
    }

    public function test_isAdmin_verifica_correttamente_il_flag()
    {
        // Utente normale (senza flag admin)
        $_SESSION['user'] = ['username' => 'mario', 'is_admin' => 0];
        $this->assertFalse(Auth::isAdmin());

        // Utente Admin
        $_SESSION['user'] = ['username' => 'admin_super', 'is_admin' => 1];
        $this->assertTrue(Auth::isAdmin());
    }

    public function test_isOwner_verifica_la_corrispondenza_dello_username()
    {
        $_SESSION['user'] = ['username' => 'luigi.verdi'];

        // È il proprietario? (Sì)
        $this->assertTrue(Auth::isOwner('luigi.verdi'));

        // È il proprietario? (No, stiamo chiedendo di un altro utente)
        $this->assertFalse(Auth::isOwner('mario.rossi'));
    }

    public function test_getHeaderLinks_ritorna_html_corretto_per_ospiti()
    {
        // Nessuna sessione attiva
        $html = Auth::getHeaderLinks();
        $this->assertStringContainsString('href="/login"', $html);
        $this->assertStringContainsString('Login', $html);
    }

    public function test_getHeaderLinks_ritorna_html_corretto_per_utenti_loggati()
    {
        // Simuliamo un utente standard
        $_SESSION['user'] = ['username' => 'studente1'];
        
        $html = Auth::getHeaderLinks();
        $this->assertStringContainsString('href="/profilo"', $html);
        $this->assertStringNotContainsString('Admin', $html); // Non deve esserci il link admin
    }

    public function test_getHeaderLinks_ritorna_html_corretto_per_admin()
    {
        // Simuliamo un admin
        $_SESSION['user'] = ['username' => 'admin1', 'is_admin' => 1];
        
        $html = Auth::getHeaderLinks();
        $this->assertStringContainsString('href="/admin"', $html);
    }
}