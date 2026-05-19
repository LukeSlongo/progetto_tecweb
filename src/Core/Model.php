<?php 
namespace App\Core;

use App\Core\Database;

abstract class Model{

    protected $db;
    protected $table;

    public function __construct(){
        $this->db = Database::getInstance();
    }

    protected function query($sql, $params = []){
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;

    }

    protected function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }


    protected function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function get_all(){
        $sql = "SELECT * FROM " . $this->table;
        return $this->fetchAll($sql);
    }

    public function get_rand($limit = 15) {
        $limit = (int)$limit;
        $sql = "SELECT * FROM " . $this->table . " ORDER BY RAND() LIMIT $limit";
        return $this->fetchAll($sql);
    }

    public function insert($data) {
        if (empty($this->table)) {
            throw new \Exception("Nessuna tabella definita nel model.");
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $values = array_values($data);

        try {
            $this->query($sql, $values);
            return true;
        } catch (\Exception $e) {
            error_log("Errore insert generico: " . $e->getMessage());
            return false;
        }
    }
    
}
