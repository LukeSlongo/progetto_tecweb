<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;
use App\Helpers\ComponentHelper;
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
        //BreadcrumbHelper::reset();
    }

    public function viewIssueForm()
    {
        $this->page_title = "Nuova Segnalazione - UniFix";

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

        $issueModel = new \App\Models\IssueModel();

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
        $this->page_title = "Issue List - UniFix";
        $this->scriptPathList = ["issue"];

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


        $role = $_SESSION['user']['role'] ?? '';
        $has_privileges = $role === 'admin' || $role === 'technician';
        $is_owner = Auth::isLogged() && Auth::isOwner($issue['reporter_id']);

        // mostra il bottone se ha i permessi o se è il proprietario
        $delete_issue_button = (Auth::isAdmin() || $is_owner)
            ? '<form action="/issues/' . $issue['issue_id'] . '/delete" method="POST" onsubmit="return confirm(\'Vuoi eliminare questa segnalazione?\')">'
            . '<button class="btn-danger" type="submit">Elimina segnalazione</button>'
            . '</form>'
            : '';

        // ricava il reporter id se esist
        $reporter_id = $issue['reporter_id'] ?? 'Utente eliminato';
        $reporter = ($has_privileges)
            ? '<li>Id utente segnalatore:' . $reporter_id . '</li>'
            : '';

        $takeIssueButton = '';
        $closeIssueButton = '';

        if ($role === 'technician') {
            if ($issue['issue_status'] === 'open') {
                $takeIssueButton = '<form action="/issues/' . $issue['issue_id'] . '/take" method="POST">'
                    . '<button class="btn-primary" type="submit">Prendi in carico</button>'
                    . '</form>';
            } elseif ($issue['issue_status'] === 'in_progress') {
                $closeIssueButton = '<form action="/issues/' . $issue['issue_id'] . '/close" method="POST" onsubmit="return confirm(\'Confermi di aver risolto il problema e voler chiudere la segnalazione?\')">'
                    . '<button class="btn-success" type="submit">Risolvi</button>'
                    . '</form>';
            }
        }

        $this->render('issueDetailPage', [
            'ISSUE_TITLE' => $issue['issue_title'],
            'STATUS' => ucfirst(str_replace('_', ' ', $issue['issue_status'])),

            //informazione sulla direzione
            'BUILDING_NAME' => $issue['building_name'],
            'ROOM_NAME' => $issue['room_name'],
            'OPEN_DATE' => date('d/m/Y', strtotime($issue['opened_at'])),
            'CLOSE_DATE' => $issue['closed_at'] ? date('d/m/Y', strtotime($issue['closed_at'])) : 'Non ancora chiusa',
            'TECHNICIAN_ID' => $issue['technician_id'] ?? 'Nessun tecnico assegnato',
            'REPORTER_ID' => $reporter,
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