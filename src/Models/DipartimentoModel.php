<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class DipartimentoModel extends Model {

    protected $table = 'dipartimento';

    public function get_all_dipartimenti() {
        return $this->get_all();
    }

    public function find_dipartimento($dipartimento_id) {
        $sql = "SELECT * FROM dipartimento WHERE id = ?";
        return $this->fetchOne($sql, [$dipartimento_id]);
    }

}