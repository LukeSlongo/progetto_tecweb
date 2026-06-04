<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use Exception;

class UserController extends Controller
{
    public function viewRegister()
    {
        $this->requireGuest();
        $this->page_title = "Registrazione - UniFix";
        $this->render('registerPage');
    }

    public function register()
    {
        $this->requireGuest();
        $username = $this->post('username'); 
        $password = $this->post('password');

        if (empty($username) || empty($password))
        {
            $_SESSION['flash_error'] = "Tutti i campi sono obbligatori.";
            $this->redirect('/login');
        }

        $user_model = new UserModel();

        try
        {
            $user_model->register($username, $password, 'student');
            // reindirizziamo a login per il primo accesso
            $this->redirect('/login');
        }
        catch (Exception $e)
        {
            $_SESSION['flash_error'] = "Errore durante la registrazione. Il nome utente potrebbe essere già in uso.";
            $this->redirect('/register');
        }
    }

    public function logout(){
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }
}