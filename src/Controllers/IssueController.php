<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;
use App\Helpers\ComponentHelper;
use \App\Helpers\BreadcrumbHelper;
use App\Core\Auth;
use \App\Models\BuildingModel;
use \App\Models\RoomModel;

class IssueController extends Controller
{

    private $Issue;

    public function __construct()
    {
        $this->Issue = new IssueModel();
        $this->scriptPathList[] = 'issue';
        BreadcrumbHelper::reset();
        BreadcrumbHelper::add('Home', '/');
    }

    public function viewIssueForm()
    {
        $this->page_title = "Nuova Segnalazione - UniFix";
        $this->page_description = "Compila il modulo per creare una nuova segnalazione di guasto o problema in un'aula.";

        $buildingModel = new BuildingModel();
        $roomModel = new RoomModel();

        $buildings = $buildingModel->findAll();
        $rooms = $roomModel->findAll();

        $buildingsHtml = '';
        foreach ($buildings as $building) {
            $buildingsHtml .= '<option value="' . $building['id'] . '">' . htmlspecialchars($building['name'], ENT_QUOTES, 'UTF-8') . '</option>';
        }

        $roomsHtml = '';
        foreach ($buildings as $building) {
            $roomsHtml .= '<optgroup label="' . htmlspecialchars($building['name'], ENT_QUOTES, 'UTF-8') . '" data-building-id="' . $building['id'] . '">';
            foreach ($rooms as $room) {
                if ($room['building_id'] == $building['id']) {
                    $roomsHtml .= '<option value="' . $room['id'] . '">' . htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') . '</option>';
                }
            }
            $roomsHtml .= '</optgroup>';
        }
        BreadcrumbHelper::add('Nuova segnalazione');
        $this->render('issueFormPage', [
            'BUILDING_OPTIONS' => $buildingsHtml,
            'ROOM_OPTIONS' => $roomsHtml
        ]);
    }

    public function saveIssue()
    {
        $room_id = $this->post('room_id');
        $title = $this->post('issue_title');
        $description = $this->post('issue_description');

        if (empty($room_id) || empty($title) || empty($description)) {
            $_SESSION['flash_error'] = "Tutti i campi (Aula, Titolo, Descrizione) sono obbligatori.";
            $this->redirect('/issues/new');
            return;
        }

        $user = Auth::getUser();
        if (!$user) {
            $_SESSION['flash_error'] = "Sessione scaduta, effettua nuovamente il login.";
            $this->redirect('/login');
            return;
        }

        $issueModel = new IssueModel();

        try {
            $issueModel->registerIssue($user['id'], $room_id, $title, $description);

            $_SESSION['flash_success'] = "Segnalazione inviata con successo!";
            $this->redirect('/');

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Errore durante l'invio della segnalazione. Riprova.";
            $this->redirect('/issues/new');
        }
    }

    public function viewIssueList()
    {
        $this->page_title = "Elenco Segnalazioni - UniFix";
        $this->page_description = "Visualizza tutte le segnalazioni presenti nel sistema UniFix.";
        $this->scriptPathList = ["issue"];

        $status = $this->get('status');
        $issues = $this->searchIssues($status);

       if (empty($issues)) {
            $messaggio = "Nessuna segnalazione trovata per i criteri selezionati.";
            $items_html = '<tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-gray);"><span role="status">' . $messaggio . '</span></td></tr>';
        } else {
            $status_translations = [
                'open' => 'Aperto',
                'in_progress' => 'In lavorazione',
                'closed' => 'Chiuso',
                'resolved' => 'Risolto'
            ];

            foreach ($issues as &$issue) {
                $status_en = $issue['issue_status'] ?? '';
                if (isset($status_translations[$status_en])) {
                    $issue['issue_status'] = $status_translations[$status_en];
                }

                if (isset($issue['opened_at'])) {
                    $timestamp = strtotime($issue['opened_at']);
                    $issue['DATETIME_ATTR'] = date('Y-m-d', $timestamp);
                    $issue['OPENED_AT'] = date('d/m/Y', $timestamp);
                }
            }
            unset($issue);

            $items_html = ComponentHelper::renderList('issueListItem', $issues);
        }

        BreadcrumbHelper::add('Segnalazioni', '/issues');
        
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
        $this->page_title = "Dettaglio Segnalazione - UniFix";
        $this->scriptPathList = ["issue"];
        
        $issue = $this->Issue->getIssueDetails($id);

        $clean_title = htmlspecialchars(strip_tags($issue['issue_title']), ENT_QUOTES, 'UTF-8');
        $this->page_description = "Dettaglio della segnalazione " . $clean_title . " - UniFix";

        $role = $_SESSION['user']['role'] ?? '';
        $has_privileges = $role === 'admin' || $role === 'technician';
        $is_owner = Auth::isLogged() && Auth::isOwner($issue['reporter_id']);

        $delete_issue_button = (Auth::isAdmin() || $is_owner)
            ? '<form action="/issues/' . $issue['issue_id'] . '/delete" method="POST" class="delete-issue-form">'
            . '<button class="btn-danger" type="submit">Elimina segnalazione</button>'
            . '</form>'
            : '';

        $takeIssueButton = '';
        $closeIssueButton = '';

        if ($role === 'technician') {
            if ($issue['issue_status'] === 'open') {
                $takeIssueButton = '<form action="/issues/' . $issue['issue_id'] . '/take" method="POST" class="take-issue-form">'
                    . '<button class="btn-primary" type="submit">Prendi in carico</button>'
                    . '</form>';
            } elseif ($issue['issue_status'] === 'in_progress') {
                // Rimosso onsubmit, aggiunta classe
                $closeIssueButton = '<form action="/issues/' . $issue['issue_id'] . '/close" method="POST" class="close-issue-form">'
                    . '<button class="btn-success" type="submit">Risolvi</button>'
                    . '</form>';
            }
        }

        $status_translations = [
            'open' => 'Aperto',
            'in_progress' => 'In lavorazione',
            'closed' => 'Chiuso',
            'resolved' => 'Risolto'
        ];
        $status_en = $issue['issue_status'] ?? '';
        $status_it = $status_translations[$status_en] ?? ucfirst($status_en);


        $open_timestamp = strtotime($issue['opened_at']);
        $open_datetime = date('Y-m-d\TH:i', $open_timestamp);
        $open_date_human = date('d/m/Y H:i', $open_timestamp);

        if (!empty($issue['closed_at'])) {
            $close_timestamp = strtotime($issue['closed_at']);
            $close_datetime = date('Y-m-d\TH:i', $close_timestamp);
            $close_date_human = date('d/m/Y H:i', $close_timestamp);
            $close_date_html = '<time datetime="' . $close_datetime . '">' . $close_date_human . '</time>';
        } else {
            $close_date_html = 'Non ancora chiusa';
        }

    
        $reporter_id = $issue['reporter_id'] ?? 'Utente eliminato';
        $reporter_html = ($has_privileges)
            ? '<dt>Id utente segnalatore:</dt> <dd>' . $reporter_id . '</dd>'
            : '';

        BreadcrumbHelper::add('Segnalazioni', '/issues');
        BreadcrumbHelper::add($issue['issue_title']);

        $this->render('issueDetailPage', [
            'ISSUE_TITLE' => $issue['issue_title'],
            'STATUS' => $status_it,
            'ISSUE_DESCRIPTION' => $issue['issue_description'],
            'BUILDING_NAME' => $issue['building_name'],
            'ROOM_NAME' => $issue['room_name'],

            'OPEN_DATETIME' => $open_datetime,
            'OPEN_DATE' => $open_date_human,
            'CLOSE_DATE_HTML' => $close_date_html,

            'TECHNICIAN_ID' => $issue['technician_id'] ?? 'Nessuno',
            'REPORTER_HTML' => $reporter_html,

            'DELETE_ISSUE_BUTTON' => $delete_issue_button,
            'TAKE_ISSUE_BUTTON' => $takeIssueButton,
            'CLOSE_ISSUE_BUTTON' => $closeIssueButton
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

    public function takeIssue($id)
    {
        $user = Auth::getUser();

        // Controllo di sicurezza: solo i tecnici possono prendere in carico le issue
        if (!$user || $user['role'] !== 'technician') {
            $_SESSION['flash_error'] = "Azione non consentita. Solo i tecnici possono prendere in carico le segnalazioni.";
            $this->redirect("/issues/{$id}");
            return;
        }

        try {
            $this->Issue->takeIssue($id, $user['id']);
            $_SESSION['flash_success'] = "Hai preso in carico la segnalazione con successo.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Errore durante la presa in carico della segnalazione.";
        }

        $this->redirect("/issues/{$id}");
    }

    public function closeIssue($id)
    {
        $user = Auth::getUser();

        // Controllo di sicurezza: solo i tecnici possono chiudere le issue
        if (!$user || $user['role'] !== 'technician') {
            $_SESSION['flash_error'] = "Azione non consentita. Solo i tecnici possono risolvere le segnalazioni.";
            $this->redirect("/issues/{$id}");
            return;
        }

        try {
            $this->Issue->closeIssue($id);
            $_SESSION['flash_success'] = "Segnalazione risolta e chiusa con successo.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Errore durante la chiusura della segnalazione.";
        }

        $this->redirect("/issues/{$id}");
    }
}