<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;
use App\Helpers\ComponentHelper;

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

    public function viewIssueList() {
    $this->requireLogin(); 
    
    // Controlla se l'utente ha il ruolo di admin o technician
    $role = $_SESSION['user']['role'] ?? ''; 
    
    if ($role !== 'admin' && $role !== 'technician') {
        $this->redirect('/'); 
        return;
    }
        $this->page_title = "Issue List - UniFix";

        $issues = $this->searchIssues();
        $items_html = ComponentHelper::renderList('issueListItem', $issues);

        $this->render('issueListPage', ['ISSUE_LIST_ITEMS' => $items_html]);
    }

    public function searchIssues()
    {
    $issue_model = new IssueModel();
    $issueList = $issue_model->getActiveIssues();
    return $issueList;
    }
}