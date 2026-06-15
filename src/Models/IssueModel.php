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

    public function validate($data)
    {
        $errors = [];

        if (empty($data['titolo'])) {
            $errors[] = "Il campo Titolo è obbligatorio.";
        } else if (strlen($data['titolo']) > 150) {
            $errors[] = "Il titolo non può superare i 150 caratteri.";
        }

        if (empty($data['priorita'])) {
            $errors[] = "Il campo Priorità è obbligatorio.";
        } else if (!in_array($data['priorita'], ['bassa', 'media', 'alta'])) {
            $errors[] = "Valore di Priorità non valido.";
        }

        if (empty($data['room_id'])) {
            $errors[] = "Il campo Room è obbligatorio.";
        } else if (!$this->find_room($data['room_id'])) {
            $errors[] = "L'room selezionata non esiste.";
        }

        if (empty($data['descrizione'])) {
            $errors[] = "Il campo Descrizione è obbligatorio.";
        } elseif (strlen($data['descrizione']) > 1000) {
            $errors[] = "La descrizione non può superare i 1000 caratteri.";
        }

        if (empty($data['utente_id'])) {
            $errors[] = "Utente non autenticato.";
        } else if (!$this->find_utente($data['utente_id'])) {
            $errors[] = "Utente non valido.";
        }

        return $errors;
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
                r.name AS room_name,
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

}