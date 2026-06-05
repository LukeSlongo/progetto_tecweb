<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\UserModel;
use \App\Helpers\ComponentHelper;
use Exception;

class UserController extends Controller
{
    public function viewRegister()
    {
        $this->requireGuest();
        $this->page_title = "Registrazione - UniFix";
        $this->render('registerPage', [], 'auth');
    }

    public function register()
    {
        $this->requireGuest();
        $username = $this->post('username');
        $password = $this->post('password');

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Tutti i campi sono obbligatori.";
            $this->redirect('/login');
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

    public function viewLogin() {
        $this->requireGuest();
        $this->page_title = "Login - UniFix";
        $this->render('loginPage', [], 'auth');
    }

    public function login()
    {
        $this->requireGuest();
        
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
                'ruolo' => $user['role'],
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
        $this->checkAdmin();

        $user_model = new UserModel();
        $users = $user_model->findAll();

        $items_html = ComponentHelper::renderList('userListItem', $users);

        $this->page_title = "Gestione Utenti - UniFix";
        $this->render('userListPage', [
            'USER_LIST_ITEMS' => $items_html
    public function viewHome()
    {
        $this->requireLogin();

        $this->page_title = "Home - UniFix";
        $utente = Auth::getUser();

        $this->render('homePage', [
            'NOME_UTENTE' => $utente['username']
        ]);
    }

    public function deleteUser($user_id)
    {
        $this->checkAdmin();
        $user_model = new UserModel();
        $user_model->delete($user_id);
    }
}