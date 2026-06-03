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
            die("ERRORE FORM: I dati non arrivano al PHP. Hai cambiato l'attributo name in name='username' nel file registerPage.html?");
    
            //$_SESSION['flash_error'] = "Tutti i campi sono obbligatori.";
            $this->redirect('/register');
        }

        $user_model = new UserModel();

        try
        {
            $user_model->register($username, $password, 'student');
            // reindirizziamo a login per il primo accesso
            //$this->redirect('/login');
        }
        catch (Exception $e)
        {
            die("ERRORE SQL REALE: " . $e->getMessage());
            //$_SESSION['flash_error'] = "Errore durante la registrazione. Il nome utente potrebbe essere già in uso.";
            $this->redirect('/register');
        }
    }
}