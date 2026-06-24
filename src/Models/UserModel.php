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

    public function addFavorite($room_id, $user_id)
    {
        $sql = "INSERT INTO favorite (user_id, room_id) VALUES (?, ?)";
        $this->query($sql, [$user_id, $room_id]);
    }

    public function removeFavorite($room_id, $user_id)
    {
        $sql = "DELETE FROM favorite WHERE user_id = ? AND room_id = ?";
        $this->query($sql, [$user_id, $room_id]);
    }

    public function isFavorite($room_id, $user_id)
    {
        $sql = "SELECT COUNT(*) as count FROM favorite WHERE user_id = ? AND room_id = ?";
        $result = $this->fetchOne($sql, [$user_id, $room_id]);
        if ($result == null) {
            return false;
        }
        return $result['count'] > 0;
    }
}