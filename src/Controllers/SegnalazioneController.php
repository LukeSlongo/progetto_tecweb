<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\SegnalazioneModel;

class SegnalazioneController extends Controller {

    private $Segnalazione;

    public function __construct()
    {
        $this->Segnalazione = new SegnalazioneModel();
        $this->scriptPathList[] = 'segnalazione';
        //BreadcrumbHelper::reset();
    }

    public function nuova_segnalazione() {
        $this->page_title = "Nuova Segnalazione";
        $this->page_description = "Crea una nuova segnalazione di guasto o problema.";
        $this->render('new_report');
    }

    public function salva_segnalazione() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titolo' => trim($_POST['titolo'] ?? ''),
                'priorita' => trim($_POST['priorita'] ?? null),
                'aula_id' => trim($_POST['aula'] ?? null),
                'descrizione' => trim($_POST['descrizione'] ?? ''),
                'utente_id' => $_SESSION['user_id'] ?? null
            ];

            $errors = $this->Segnalazione->validate($data);

            if (!empty($errors)) {
                // Se ci sono errori, reindirizza indietro con messaggi di errore
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $data;
                header('Location: /nuova_segnalazione');
                exit;
            }

            // Salva la segnalazione nel database
            $this->Segnalazione->insert($data);

            // Reindirizza alla home o a una pagina di successo
            header('Location: /');
            exit;
        } else {
            // Se non è POST, reindirizza alla form
            header('Location: /nuova_segnalazione');
            exit;
        }
    }

}