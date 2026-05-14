<?php
namespace App\Core;

class Auth {

    public static function isLogged() {
        return isset($_SESSION['user']);
    }

    public static function isUser() {
        return self::isLogged() && empty($_SESSION['user']['is_admin']);
    }

    public static function isAdmin() {
        if (!self::isLogged()) return false;
        return !empty($_SESSION['user']['is_admin']);
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
            $user = self::getUser();
            return '<a href="/admin" lang="en">Admin</a>';
        } elseif (self::isUser()) {
            $user = self::getUser();
            return '<a href="/profilo'. '">Profilo</a>';
        } else {
            return '<a href="/login" lang="en">Login</a>';
        }
    }

    public static function getFooterLinks() {
        if (self::isAdmin()) {
            return 
            '<a href="/admin" lang="en"><span class="icon" aria-hidden="true"><img src="/img/icone/admin.webp" alt=""></span>Admin</a>' . 
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