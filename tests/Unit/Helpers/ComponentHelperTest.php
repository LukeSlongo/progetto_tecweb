<?php

namespace Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\ComponentHelper;

class ComponentHelperTest extends TestCase
{
    private string $componentsPath;
    private string $dummyComponentName = 'test_dummy_component';

    protected function setUp(): void
    {
        $this->componentsPath = __DIR__ . '/../../../src/Views/components';
        
        if (!is_dir($this->componentsPath)) {
            mkdir($this->componentsPath, 0777, true);
        }
        $contenutoFinto = "<div class='user'>Nome: ##NOME## - Ruolo: ##ROLE##</div>\n";
        
        file_put_contents($this->componentsPath . '/' . $this->dummyComponentName . '.html', $contenutoFinto);
    }

    protected function tearDown(): void
    {
        // Spazziamo via il file finto per non lasciare sporcizia nel progetto
        $file = $this->componentsPath . '/' . $this->dummyComponentName . '.html';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function test_renderList_ritorna_stringa_vuota_se_dati_vuoti()
    {
        $risultato = ComponentHelper::renderList($this->dummyComponentName, []);
        $this->assertEquals("", $risultato);
    }

    public function test_renderList_ritorna_stringa_vuota_se_file_non_esiste()
    {
        // Chiamiamo un componente che sicuramente non abbiamo creato
        $risultato = ComponentHelper::renderList('componente_che_non_esiste', [['id' => 1]]);
        $this->assertEquals("", $risultato);
    }

    public function test_renderList_sostituisce_correttamente_i_placeholder()
    {
        $dati = [
            ['nome' => 'Mario', 'role' => 'admin'],
            ['nome' => 'Luigi', 'role' => 'student']
        ];

        $risultato = ComponentHelper::renderList($this->dummyComponentName, $dati);

        $atteso = "<div class='user'>Nome: Mario - Ruolo: admin</div>\n" .
                  "<div class='user'>Nome: Luigi - Ruolo: student</div>\n";
        
        $this->assertEquals($atteso, $risultato);
    }

    public function test_renderList_sanifica_i_dati_per_evitare_xss()
    {
        // Simuliamo un attacco hacker in cui un utente ha inserito script JS nel nome
        $dati = [
            ['nome' => '<script>alert("Hacked!")</script>', 'roole' => 'Student']
        ];

        $risultato = ComponentHelper::renderList($this->dummyComponentName, $dati);

        // Assicuriamoci che i tag pericolosi < e > siano stati convertiti in &lt; e &gt; da htmlspecialchars
        $this->assertStringContainsString('&lt;script&gt;', $risultato);
        $this->assertStringNotContainsString('<script>', $risultato);
    }
}