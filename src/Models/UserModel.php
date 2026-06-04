<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected $table = 'user';

    public function register($username, $password, $role = 'student')
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO `user` (username, password, role) VALUES (?, ?, ?)";
        $this->query($sql, [$username, $hashed_password, $role]);
    }

    public function find_user($username)
    {
        $sql = "SELECT * FROM user WHERE username = ?";
        return $this->fetchOne($sql, [$username]);
    }
}