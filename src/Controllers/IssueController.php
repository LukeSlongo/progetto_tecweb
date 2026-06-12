<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;
use App\Helpers\ComponentHelper;
use App\Core\Auth;

class IssueController extends Controller
{

    private $Issue;

    public function __construct()
    {
        $this->Issue = new IssueModel();
        $this->scriptPathList[] = 'issue';
        //BreadcrumbHelper::reset();
    }

    public function nuova_issue()
    {
        $this->page_title = "Nuova Issue";
        $this->page_description = "Crea una nuova issue di guasto o problema.";
        $this->render('new_issue');
    }

    public function salva_issue()
    {
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

    public function viewIssueList()
    {
        $this->page_title = "Issue List - UniFix";

        $status = $this->get('status');
        $issues = $this->searchIssues($status);

        $items_html = ComponentHelper::renderList('issueListItem', $issues);
        $this->render('issueListPage', [
            'ISSUE_LIST_ITEMS' => $items_html,
            'CHECKED_ALL' => empty($status) ? 'checked' : '',
            'CHECKED_OPEN' => $status === 'open' ? 'checked' : '',
            'CHECKED_IN_PROGRESS' => $status === 'in_progress' ? 'checked' : '',
            'CHECKED_CLOSED' => $status === 'closed' ? 'checked' : ''
        ]);
    }

    public function searchIssues($status)
    {
        $issue_model = new IssueModel();
        $issueList = $issue_model->getIssuesByStatus($status);
        return $issueList;
    }

    public function viewIssueDetail($id)
    {
        $this->page_title = "Dettaglio Issue - UniFix";
        $issue = $this->Issue->getIssueDetails($id);

        if (!$issue) {
            $this->abort(404, "La segnalazione richiesta non esiste.");
        }

        // creazione delle variabili per controllare i permessi
        $role = $_SESSION['user']['role'] ?? '';
        $has_privileges = $role === 'admin' || $role === 'technician';
        $is_owner = Auth::isLogged() && Auth::isOwner($issue['reporter_id']);

        // mostra il bottone se ha i permessi o se è il proprietario
        $delete_issue_button = ($has_privileges || $is_owner)
            ? '<form action="/issues/' . $issue['issue_id'] . '/delete" method="POST" onsubmit="return confirm(\'Vuoi eliminare questa segnalazione?\')">'
            . '<button class="btn btn-cta" type="submit">Elimina segnalazione</button>'
            . '</form>'
            : '';

        // ricava il reporter id se esist
        $reporter_id = $issue['reporter_id'] ?? 'Utente eliminato';
        $reporter = ($has_privileges)
            ? '<li>Id utente segnalatore:' . $reporter_id . '</li>'
            : '';

        $this->render('issueDetailPage', [
            'ISSUE_TITLE' => $issue['issue_title'],
            'ISSUE_DESCRIPTION' => $issue['issue_description'],
            'STATUS' => ucfirst(str_replace('_', ' ', $issue['issue_status'])),
            'BUILDING_NAME' => $issue['building_name'],
            'ROOM_NAME' => $issue['room_name'],
            'OPEN_DATE' => date('d/m/Y H:i', strtotime($issue['opened_at'])),
            'CLOSE_DATE' => $issue['closed_at'] ? date('d/m/Y H:i', strtotime($issue['closed_at'])) : 'Non ancora chiusa',
            'TECHNICIAN_ID' => $issue['technician_id'] ?? 'Nessun tecnico assegnato',
            'REPORTER_ID' => $reporter,
            'DELETE_ISSUE_BUTTON' => $delete_issue_button,
        ]);
    }

    public function deleteIssue($id)
    {
        if ($this->Issue->find_issue($id) === false) {
            throw new \App\Exceptions\NotFoundException("Segnalazione non trovata.");
        }
        $this->Issue->delete($id);
        $this->redirect('/issues');
    }
}