<?php

require_once __DIR__ . '/../database/database.php';

class Task {
    private $pdo;

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'] ?? 'pending'
        ]);

        return $this->find($this->pdo->lastInsertId());
    }

    public function findAll($status = null) {
        $sql = "SELECT * FROM tasks";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM tasks WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function update($id, $data) {
        // Build the query dynamically based on the provided data
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            // Only allow specific fields to be updated
            if (in_array($key, ['title', 'description', 'status'])) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false; // Nothing to update
        }

        // Add updated_at timestamp and ID to parameters
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;

        $sql = "UPDATE tasks SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->find($id);
    }

    public function delete($id) {
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}