<?php
namespace App\Models;
use App\Core\Model;

class IssueModel extends Model
{

    protected $table = 'issue';


    public function find_issue($issue_id)
    {
        $sql = "SELECT * FROM issue WHERE id = ?";
        return $this->fetchOne($sql, [$issue_id]);
    }

    public function getIssuesByStatus($status = null)
    {
        if ($status) {
            $sql = "SELECT 
                    i.id AS issue_id,
                    i.title AS issue_title,         
                    r.name AS issue_room,
                    i.opened_at AS opened_at,      
                    i.status AS issue_status        
                FROM issue i
                JOIN room r ON i.room_id = r.id
                WHERE i.status = ?      
                ORDER BY i.opened_at DESC";
            return $this->fetchAll($sql, [$status]);
        }

        //Se non c'è nessun filtro, mostra tutte le segnalazioni
        $sql = "SELECT 
                i.id AS issue_id,
                i.title AS issue_title,         
                r.name AS issue_room,
                i.opened_at AS opened_at,      
                i.status AS issue_status        
            FROM issue i
            JOIN room r ON i.room_id = r.id
            ORDER BY i.opened_at DESC";

        return $this->fetchAll($sql);
    }
    public function getIssuesByRoom($room_id)
    {
        $sql = "SELECT
                i.id AS issue_id,
                i.title AS issue_title,
                i.opened_at AS opened_at,
                i.status AS issue_status
                FROM issue i
                WHERE room_id = ?
                ";
        return $this->fetchAll($sql, [$room_id]);
    }

    public function getIssueDetails($issue_id)
    {
        $sql = "SELECT 
                i.id AS issue_id,
                i.title AS issue_title,
                i.description AS issue_description,
                i.status AS issue_status,
                i.opened_at AS opened_at,
                i.closed_at AS closed_at,     
                b.name AS building_name,
                b.id AS building_id,      
                r.name AS room_name,
                r.id AS room_id,
                u.id AS reporter_id,
                u.username AS reporter_name, 
                t.id AS technician_id,  
                t.username AS technician_name
            FROM issue i
            JOIN room r ON i.room_id = r.id
            JOIN building b ON r.building_id = b.id
            LEFT JOIN `user` u ON i.user_id = u.id
            LEFT JOIN `user` t ON i.technician_id = t.id
            WHERE i.id = ?";

        return $this->fetchOne($sql, [$issue_id]);
    }

    public function takeIssue($issue_id, $technician_id)
    {
        $this->checkTable();
        $sql = "UPDATE {$this->table} SET status = 'in_progress', technician_id = ? WHERE id = ?";
        return $this->query($sql, [$technician_id, $issue_id]);
    }

    public function closeIssue($issue_id)
    {
        $this->checkTable();
        $sql = "UPDATE {$this->table} SET status = 'closed', closed_at = CURRENT_DATE WHERE id = ?";
        return $this->query($sql, [$issue_id]);
    }

    public function getIssuesByUser($user_id)
    {
        $sql = "SELECT 
                    i.id AS issue_id,
                    i.title AS issue_title,
                    i.status AS issue_status,
                    r.name AS room_name
                FROM issue i
                JOIN room r ON i.room_id = r.id
                WHERE i.user_id = ?
                ORDER BY i.opened_at DESC";

        return $this->fetchAll($sql, [$user_id]);
    }

    public function registerIssue($user_id, $room_id, $title, $description)
    {
        $this->checkTable();

        $sql = "INSERT INTO {$this->table} 
                (user_id, room_id, title, description) 
                VALUES (?, ?, ?, ?)";

        return $this->query($sql, [$user_id, $room_id, $title, $description]);
    }

    public function updateIssue($issue_id, $room_id, $title, $description)
    {
        $this->checkTable();
        $sql = "UPDATE {$this->table} SET room_id = ?, title = ?, description = ? WHERE id = ?";
        return $this->query($sql, [$room_id, $title, $description, $issue_id]);
    }
    public function getIssuesByTechnician($technician_id)
    {
        $sql = "SELECT 
                    i.id AS issue_id,
                    i.title AS issue_title,
                    i.status AS issue_status,
                    r.name AS room_name
                FROM issue i
                JOIN room r ON i.room_id = r.id
                WHERE i.technician_id = ? AND i.status = 'in_progress'
                ORDER BY i.opened_at DESC";

        return $this->fetchAll($sql, [$technician_id]);
    }

}