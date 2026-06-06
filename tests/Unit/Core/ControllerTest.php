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

    public function test_abort_lancia_eccezione_404_not_found()
    {
        $controller = new ConcreteController();

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