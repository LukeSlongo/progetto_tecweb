<?php
namespace App\Models;
use App\Core\Model;
use App\Core\Auth;

class SegnalazioneModel extends Model {

    protected $table = 'segnalazione';


    public function find_segnalazione($segnalazione_id) {
        $sql = "SELECT * FROM segnalazione WHERE id = ?";
        return $this->fetchOne($sql, [$segnalazione_id]);
    }

}