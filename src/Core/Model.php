<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;
abstract class Model
{

    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function checkTable()
    {
        if (!$this->table) {
            throw new Exception("Proprietà \$table non definita nel Model figlio.");
        }
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Errore Database nella tabella '{$this->table}': " . $e->getMessage());
        }
    }

    protected function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    protected function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function findAll()
    {
        $this->checkTable();
        $sql = "SELECT * FROM " . $this->table;
        return $this->fetchAll($sql);
    }

    public function findById($id)
    {
        $this->checkTable();
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->fetchOne($sql, [$id]);
    }

    public function delete($id)
    {
        $this->checkTable();
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->query($sql, [$id]);
    }

}
