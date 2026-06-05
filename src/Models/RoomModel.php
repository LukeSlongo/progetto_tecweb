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

public function searchRoomsWithCount($search = '') {
    $sql = "SELECT 
                r.id AS room_id, 
                r.name AS room_name, 
                b.name AS building_name, 
                (SELECT COUNT(*) FROM issue i WHERE i.room_id = r.id) AS num_reports
            FROM room r
            JOIN building b ON r.building_id = b.id
            WHERE r.name LIKE ?";
            
    return $this->fetchAll($sql, ["%$search%"]);
}

}