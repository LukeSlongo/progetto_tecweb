<?php
namespace App\Core;

class Auth {

    public static function isLogged() {
        return isset($_SESSION['user']);
    }

    private static function getRole() {
        if (!self::isLogged()) return null;
        return $_SESSION['user']['role'] ?? $_SESSION['user']['ruolo'] ?? null;
    }

    public static function isStudent() {
        return self::getRole() === 'student';
    }

    public static function isTechnician() {
        return self::getRole() === 'technician';
    }

    public static function isAdmin() {
        return self::getRole() === 'admin';
    }

    public static function isOwner($username) {
        if (!self::isLogged()) return false;
        return $_SESSION['user']['username'] === $username;
    }

    public static function getUser() {
        return $_SESSION['user'] ?? null;
    }

    public static function getHeaderLinks() {
        if (self::isAdmin()) {
            return '<a href="/users" lang="en">Gestione Utenti</a>'; // Aggiornato con la rotta che abbiamo appena creato
        } else if (self::isLogged()) {
            return '<a href="/profilo">Profilo</a>';
        } else {
            return '<a href="/login" lang="en">Login</a>';
        }
    }

    public static function getFooterLinks() {
        if (self::isAdmin()) {
            return 
            '<a href="/users" lang="en"><span class="icon" aria-hidden="true"><img src="/img/icone/admin.webp" alt=""></span>Gestione Utenti</a>' . 
            '<a href="/logout" lang="en"><span class="icon" aria-hidden="true"><img src="/img/icone/logout.webp" alt=""></span>Logout</a>';
        
        } elseif (self::isLogged()) {
            return 
            '<a href="/profilo"><span class="icon" aria-hidden="true"><img src="/img/icone/user.webp" alt=""></span>Profilo</a>' . 
            '<a href="/logout" lang="en"><span class="icon" aria-hidden="true"><img src="/img/icone/logout.webp" alt=""></span>Logout</a>';
        
        } else {
            return 
            '<a href="/login" lang="en"><span class="icon" aria-hidden="true"><img src="/img/icone/login.webp" alt=""></span>Login</a>' . 
            '<a href="/register"><span class="icon" aria-hidden="true"><img src="/img/icone/register.webp" alt=""></span>Registrati</a>';
        }
    }
}