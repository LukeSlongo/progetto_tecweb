<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class EdificioModel extends Model {

    protected $table = 'edificio';

    public function get_edifici_by_dipartimento($dipartimento_id) {
        $sql = "SELECT * FROM edificio WHERE dipartimento_id = ?";
        return $this->fetchAll($sql, [$dipartimento_id]);
    }

    public function find_edificio($edificio_id) {
        $sql = "SELECT * FROM edificio WHERE id = ?";
        return $this->fetchOne($sql, [$edificio_id]);
    }

}