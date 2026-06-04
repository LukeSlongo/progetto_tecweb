<?php

use PHPUnit\Framework\TestCase;
use App\Core\Template;

class TemplateTest extends TestCase
{
    // Il nome del nostro file finto per i test
    private $testPageName = 'dummy_test_page';
    
    // Conterrà il percorso assoluto in cui andremo a scrivere il file
    private $testFilePath;

    protected function setUp(): void
    {
        // __DIR__ in questo file punta a "tests/Unit/Core". 
        // Dobbiamo risalire fino alla root e scendere in "src/Views/"
        $this->testFilePath = __DIR__ . '/../../../src/Views/' . $this->testPageName . '.html';

        // Creiamo la cartella se per caso non dovesse esistere
        if (!is_dir(dirname($this->testFilePath))) {
            mkdir(dirname($this->testFilePath), 0777, true);
        }

        // Creiamo il nostro finto file HTML con dei placeholder di prova
        $htmlDiProva = "<h1>Benvenuto ##UTENTE##!</h1><p>Il tuo ruolo è: ##RUOLO##</p>";
        file_put_contents($this->testFilePath, $htmlDiProva);
    }

    protected function tearDown(): void
    {
        // PULIZIA: Dopo ogni singolo test, eliminiamo il file finto
        // Così non inquiniamo la tua vera cartella src/Views!
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function test_costruttore_lancia_eccezione_se_file_non_trovato()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/File template non trovato:/');

        // Proviamo ad aprire un file che sappiamo non esistere
        new Template('pagina_che_nessuno_ha_mai_creato_123');
    }

    public function test_setPageData_sostituisce_correttamente_i_dati()
    {
        $template = new Template($this->testPageName);

        // Passiamo solo l'utente, ignoriamo volutamente il ruolo
        $template->setPageData(['UTENTE' => 'Gigi']);

        // Chiediamo il testo mantenendo i placeholder intatti (true)
        $risultato = $template->getPage(true);

        // L'utente deve essere stato sostituito
        $this->assertStringContainsString('<h1>Benvenuto Gigi!</h1>', $risultato);
        
        // Il ruolo NON è stato sostituito, quindi il placeholder ci deve ancora essere
        $this->assertStringContainsString('##RUOLO##', $risultato);
    }

    public function test_setPageData_ignora_input_non_array()
    {
        $template = new Template($this->testPageName);

        // Proviamo a passare una stringa al posto di un array (dovrebbe bloccarsi in modo sicuro grazie al tuo if)
        $template->setPageData("ciao, non sono un array");
        $risultato = $template->getPage(true);

        $this->assertStringContainsString('##UTENTE##', $risultato);
    }

    public function test_getPage_rimuove_i_placeholder_rimasti_vuoti_di_default()
    {
        $template = new Template($this->testPageName);
        $template->setPageData(['UTENTE' => 'Gigi']);

        // Comportamento standard: getPage() chiama la regex preg_replace
        $risultato = $template->getPage();

        // Gigi c'è
        $this->assertStringContainsString('<h1>Benvenuto Gigi!</h1>', $risultato);
        
        // La scritta ##RUOLO## deve essere stata vaporizzata dalla regex!
        $this->assertStringNotContainsString('##RUOLO##', $risultato);
        
        // Il paragrafo sarà rimasto vuoto alla fine
        $this->assertStringContainsString('<p>Il tuo ruolo è: </p>', $risultato);
    }
}