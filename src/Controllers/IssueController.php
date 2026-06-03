<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;

class IssueController extends Controller {

    private $Issue;

    public function __construct()
    {
        $this->Issue = new IssueModel();
        $this->scriptPathList[] = 'issue';
        //BreadcrumbHelper::reset();
    }

    public function nuova_issue() {
        $this->page_title = "Nuova Issue";
        $this->page_description = "Crea una nuova issue di guasto o problema.";
        $this->render('new_issue');
    }

    public function salva_issue() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titolo' => trim($_POST['titolo'] ?? ''),
                'priorita' => trim($_POST['priorita'] ?? null),
                'room_id' => trim($_POST['room'] ?? null),
                'descrizione' => trim($_POST['descrizione'] ?? ''),
                'utente_id' => $_SESSION['user_id'] ?? null
            ];

            $errors = $this->Issue->validate($data);

            if (!empty($errors)) {
                // Se ci sono errori, reindirizza indietro con messaggi di errore
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $data;
                header('Location: /nuova_issue');
                exit;
            }

            // Salva la issue nel database
            $this->Issue->insert($data);

            // Reindirizza alla home o a una pagina di successo
            header('Location: /');
            exit;
        } else {
            // Se non è POST, reindirizza alla form
            header('Location: /nuova_issue');
            exit;
        }
    }

}