<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class RoomModel extends Model
{

    protected $table = 'room';

    public function find_room($room_id)
    {
        $sql = "SELECT * FROM room WHERE id = ?";
        return $this->fetchOne($sql, [$room_id]);
    }

    public function searchRoomsWithCount($search = '')
    {
        $sql = "SELECT 
                r.id AS room_id, 
                r.name AS room_name,
                (SELECT COUNT(*) FROM issue i WHERE i.room_id = r.id) AS num_reports
            FROM room r
            WHERE r.name LIKE ?";

        return $this->fetchAll($sql, ["%$search%"]);
    }

    public function getRoomWithBuilding($room_id)
    {
        $sql = "SELECT 
                r.id AS room_id, 
                r.name AS room_name, 
                b.id AS building_id, 
                b.name AS building_name, 
                b.address AS building_address
                FROM room r
                JOIN building b ON b.id = r.building_id
                WHERE r.id = ?";

        return $this->fetchOne($sql, [$room_id]);
    }

    public function getFavoritesByUser($user_id)
    {
        $sql = "SELECT 
                    r.id AS room_id,
                    r.name AS room_name,
                    b.name AS building_name,
                    (SELECT COUNT(*) FROM issue i WHERE i.room_id = r.id AND i.status IN ('open', 'in_progress')) as active_issues
                FROM room r
                JOIN building b ON r.building_id = b.id
                JOIN favorite f ON f.room_id = r.id
                WHERE f.user_id = ?";

        return $this->fetchAll($sql, [$user_id]);
    }
}