<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class RoomModel extends Model {

    protected $table = 'room';

    public function get_aule_by_building($building_id) {
        $sql = "SELECT * FROM room WHERE building_id = ?";
        return $this->fetchAll($sql, [$building_id]);
    }

    public function find_room($room_id) {
        $sql = "SELECT * FROM room WHERE id = ?";
        return $this->fetchOne($sql, [$room_id]);
    }

}