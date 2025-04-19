<?php

class Database {
    private $pdo;

    public function __construct($path) {
        $this->pdo = new PDO("sqlite:" . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function Execute($sql) {
        return $this->pdo->exec($sql);
    }

    public function Fetch($sql) {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Create($table, $data) {
        $cols = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));
        $stmt = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function Read($table, $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function Update($table, $id, $data) {
        $sets = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = :$k";
        }
        $setList = implode(',', $sets);
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("UPDATE $table SET $setList WHERE id = :id");
        return $stmt->execute($data);
    }

    public function Delete($table, $id) {
        $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function Count($table) {
        $stmt = $this->pdo->query("SELECT COUNT(*) AS cnt FROM $table");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['cnt'];
    }
}
