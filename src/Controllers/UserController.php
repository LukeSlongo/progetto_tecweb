<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Template;
use App\Models\UserModel;
use App\Models\IssueModel;
use App\Models\RoomModel;
use \App\Helpers\ComponentHelper;
use \App\Helpers\BreadcrumbHelper;
use Exception;

class UserController extends Controller
{
    public function viewRegister()
    {
        $this->page_title = "Registrazione - UniFix";
        $this->page_description = "Crea un nuovo account su UniFix per accedere alle funzionalità di segnalazione e monitoraggio.";
        $this->render('registerPage', [], 'auth');
    }

    public function register()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Tutti i campi sono obbligatori.";
            $this->redirect('/register');
            return;
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['flash_error'] = "Il nome utente deve avere tra 3 e 50 caratteri.";
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = "La password deve contenere almeno 8 caratteri.";
            $this->redirect('/register');
            return;
        }

        $user_model = new UserModel();

        try {
            $user_model->register($username, $password, 'student');
            // reindirizziamo a login per il primo accesso
            $this->redirect('/login');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Errore durante la registrazione. Il nome utente potrebbe essere già in uso.";
            $this->redirect('/register');
        }
    }

    public function viewLogin()
    {
        $this->page_title = "Login - UniFix";
        $this->page_description = "Accedi al tuo account UniFix per gestire le segnalazioni e le aule.";
        $this->render('loginPage', [], 'auth');
    }

    public function login()
    {

        $username = $this->post('username');
        $password = $this->post('password');

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Inserisci username e password.";
            $this->redirect('/login');
            return;
        }

        $user_model = new UserModel();
        $user = $user_model->find_user($username);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user'] = [
                'id' => $user['id'] ?? null,
                'username' => $user['username'],
                'role' => $user['role'],
            ];

            $this->redirect('/');
            return;

        } else {
            $_SESSION['flash_error'] = "Credenziali non valide.";
            $this->redirect('/login');
            return;
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }

    public function viewUserList()
    {
        $user_model = new UserModel();
        $users = $user_model->findAll();

        $role_translations = [
            'admin' => 'Amministratore',
            'technician' => 'Tecnico',
            'student' => 'Studente'
        ];
        foreach ($users as &$user) {
            $ruolo_inglese = $user['role'];

            if (isset($role_translations[$ruolo_inglese])) {
                $user['role'] = $role_translations[$ruolo_inglese];
            }
        }
        unset($user);

        $items_html = ComponentHelper::renderList('userListItem', $users);

        $this->page_title = "Gestione Utenti - UniFix";
        $this->page_description = "Visualizza l'elenco degli utenti registrati al sistema UniFix e i loro ruoli.";
        BreadcrumbHelper::reset();
        BreadcrumbHelper::add('Home', '/');
        BreadcrumbHelper::add('Utenti');
        $this->render('userListPage', [
            'USER_LIST_ITEMS' => $items_html
        ]);
    }

    public function viewHome()
    {
        $this->page_title = "Home - UniFix";
        $this->page_description = "UniFix è il portale ufficiale dell'Università di Padova per la segnalazione, il monitoraggio e la risoluzione dei guasti nelle aule e negli edifici.";
        $utente = Auth::getUser();
        $role = $utente['role'] ?? 'guest';

        $student_section_html = '';
        $technician_section_html = '';

        if ($role === 'student') {
            $room_model = new RoomModel();
            $issue_model = new IssueModel();

            // 1. Aule Preferite
            $favorites = $room_model->getFavoritesByUser($utente['id']);
            $fav_data = [];
            foreach ($favorites as $fav) {
                $fav['ROOM_STATUS_CLASS'] = ($fav['active_issues'] > 0) ? 'status-warning' : 'status-ok';
                $fav['ROOM_STATUS_TEXT'] = ($fav['active_issues'] > 0) ? 'Guasta (' . $fav['active_issues'] . ' attive)' : 'Ok (Nessun problema)';

                $fav_data[] = $fav;
            }

            $favorites_html = empty($fav_data)
                ? '<p style="color: var(--text-gray);">Non hai ancora aggiunto nessuna aula ai preferiti.</p>'
                : ComponentHelper::renderList('favoriteRoomCard', $fav_data);

            $my_issues = $issue_model->getIssuesByUser($utente['id']);
            $iss_data = [];
            foreach ($my_issues as $issue) {
                $issue['STATUS_FORMATTED'] = ucfirst(str_replace('_', ' ', $issue['issue_status']));

                $iss_data[] = $issue;
            }

            $my_issues_html = empty($iss_data)
                ? '<p style="color: var(--text-gray);">Non hai aperto nessuna segnalazione.</p>'
                : ComponentHelper::renderList('issueCard', $iss_data);

            $section = new Template('components/studentHomeSection');
            $section->setPageData([
                'FAVORITES_CAROUSEL' => $favorites_html,
                'MY_ISSUES_CAROUSEL' => $my_issues_html,
            ]);
            $student_section_html = $section->getPage();

        } elseif ($role === 'technician') {
            $issue_model = new IssueModel();

            $my_tasks = $issue_model->getIssuesByTechnician($utente['id']);
            $tasks_data = [];
            foreach ($my_tasks as $issue) {
                $issue['STATUS_FORMATTED'] = 'In lavorazione';
                $tasks_data[] = $issue;
            }

            $my_tasks_html = empty($tasks_data)
                ? '<p style="color: var(--text-gray);">Ottimo lavoro! Non hai segnalazioni in carico al momento.</p>'
                : ComponentHelper::renderList('issueCard', $tasks_data);

            $open_issues = $issue_model->getIssuesByStatus('open');
            $open_issues = array_slice($open_issues, 0, 5);
            $open_data = [];
            foreach ($open_issues as $issue) {
                $issue['STATUS_FORMATTED'] = 'Aperto';
                $open_data[] = $issue;
            }

            $open_issues_html = empty($open_data)
                ? '<p style="color: var(--text-gray);">Nessuna nuova segnalazione in attesa.</p>'
                : ComponentHelper::renderList('issueCard', $open_data);

            $section = new Template('components/technicianHomeSection');
            $section->setPageData([
                'MY_TASKS_CAROUSEL' => $my_tasks_html,
                'OPEN_ISSUES_CAROUSEL' => $open_issues_html,
            ]);
            $technician_section_html = $section->getPage();
        }


        $search_banner_temp = new Template('components/searchBanner');
        $search_banner = $search_banner_temp->getPage();

        $create_banner_temp = new Template('components/createIssueBanner');
        $create_banner = $create_banner_temp->getPage();
        $this->scriptPathList[] = 'home';


        BreadcrumbHelper::reset();
        BreadcrumbHelper::add('Home', '/');


        $this->render('homePage', [
            'NOME_UTENTE' => htmlspecialchars($utente['username']),
            'SEARCH_BANNER' => $search_banner,
            'CREATE_ISSUE_BANNER' => $create_banner,
            'STUDENT_SECTION' => $student_section_html,
            'TECHNICIAN_SECTION' => $technician_section_html
        ]);
    }

    public function isFavorite($room_id)
    {
        $user = Auth::getUser();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Devi essere loggato']);
            return;
        }

        $user_model = new UserModel();
        $is_favorite = $user_model->isFavorite($room_id, $user['id']);

        header('Content-Type: application/json');
        echo json_encode(['isFavorite' => (bool) $is_favorite]);
    }

    public function addFavorite($room_id)
    {
        $user = Auth::getUser();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Devi essere loggato']);
            return;
        }

        $user_model = new UserModel();
        $user_model->addFavorite($room_id, $user['id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => 'added']);
    }

    public function removeFavorite($room_id)
    {
        $user = Auth::getUser();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Devi essere loggato']);
            return;
        }

        $user_model = new UserModel();
        $user_model->removeFavorite($room_id, $user['id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => 'removed']);
    }

    public function deleteUser($user_id)
    {
        $user_model = new UserModel();
        $user_model->delete($user_id);
        $this->redirect('/users');
    }
}