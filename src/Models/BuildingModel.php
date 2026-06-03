<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class BuildingModel extends Model {

    protected $table = 'building';

    public function get_edifici_by_dipartimento($dipartimento_id) {
        $sql = "SELECT * FROM building WHERE dipartimento_id = ?";
        return $this->fetchAll($sql, [$dipartimento_id]);
    }

    public function find_building($building_id) {
        $sql = "SELECT * FROM building WHERE id = ?";
        return $this->fetchOne($sql, [$building_id]);
    }

}