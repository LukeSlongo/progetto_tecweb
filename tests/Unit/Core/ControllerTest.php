<?php

use PHPUnit\Framework\TestCase;
use App\Core\Controller;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;

// classe STUB
class ConcreteController extends Controller
{
    public $redirectUrl = null;

    public function redirect($url)
    {
        $this->redirectUrl = $url;
    }
    public function callPost($key, $default = null) { return $this->post($key, $default); }
    public function callGet($key, $default = null) { return $this->get($key, $default); }
    public function callRequireLogin() { $this->requireLogin(); }
    public function callRequireGuest() { $this->requireGuest(); }
    public function callCheckAdmin() { $this->checkAdmin(); }
    public function callAbort($code = 404, $message = "") { $this->abort($code, $message); }
}

// classi di test
class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        // pulizia variabili globali
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    public function test_get_e_post_recuperano_dati_con_trim_e_default()
    {
        $controller = new ConcreteController();

        $_POST['username'] = '  mario_rossi  ';
        $_GET['page'] = '  2  ';

        $this->assertEquals('mario_rossi', $controller->callPost('username'));
        $this->assertEquals('2', $controller->callGet('page'));

        $this->assertEquals('sconosciuto', $controller->callPost('chiave_finta', 'sconosciuto'));
        $this->assertEquals(1, $controller->callGet('chiave_finta', 1));
    }

    public function test_requireLogin_reindirizza_se_utente_non_loggato()
    {
        $controller = new ConcreteController();
        
        // Non impostiamo nessuna $_SESSION['user'], quindi non siamo loggati
        $controller->callRequireLogin();

        // Verifichiamo che il redirect sia scattato e che l'errore sia in sessione
        $this->assertEquals('/login', $controller->redirectUrl);
        $this->assertEquals("Non sei loggato. Accedi per visualizzare il profilo!", $_SESSION['flash_error']);
    }

    public function test_requireGuest_reindirizza_alla_home_se_utente_gia_loggato()
    {
        $controller = new ConcreteController();
        
        // Simuliamo di essere già loggati
        $_SESSION['user'] = ['username' => 'mario.rossi'];

        $controller->callRequireGuest();

        // Un utente già loggato che prova ad andare nel login/register deve essere cacciato!
        $this->assertEquals('/', $controller->redirectUrl);
        $this->assertEquals("Disconettiti dal tuo account per continuare", $_SESSION['flash_error']);
    }

    public function test_checkAdmin_reindirizza_se_utente_normale()
    {
        $controller = new ConcreteController();
        
        // Loggato, ma non admin
        $_SESSION['user'] = ['username' => 'studente1', 'is_admin' => 0];

        $controller->callCheckAdmin();

        $this->assertEquals('/login', $controller->redirectUrl);
        $this->assertEquals("Non hai il permesso, esegui l'accesso come amministratore!", $_SESSION['flash_error']);
    }

    public function test_abort_lancia_eccezione_404_not_found()
    {
        $controller = new ConcreteController();

        // Diciamo a PHPUnit: "Attento, mi aspetto che la prossima riga faccia esplodere un'eccezione!"
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Pagina inesistente");

        $controller->callAbort(404, "Pagina inesistente");
    }

    public function test_abort_lancia_eccezione_403_forbidden()
    {
        $controller = new ConcreteController();

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage("Accesso negato");

        $controller->callAbort(403, "Accesso negato");
    }

    public function test_abort_lancia_eccezione_generica_500_per_default()
    {
        $controller = new ConcreteController();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Errore del server");

        // Se passiamo 500 (o qualsiasi numero diverso da 404 e 403), lancia un'eccezione base
        $controller->callAbort(500, "Errore del server");
    }
}