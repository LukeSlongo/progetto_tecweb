<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class AulaModel extends Model {

    protected $table = 'aula';

    public function get_aule_by_edificio($edificio_id) {
        $sql = "SELECT * FROM aula WHERE edificio_id = ?";
        return $this->fetchAll($sql, [$edificio_id]);
    }

    public function find_aula($aula_id) {
        $sql = "SELECT * FROM aula WHERE id = ?";
        return $this->fetchOne($sql, [$aula_id]);
    }

}