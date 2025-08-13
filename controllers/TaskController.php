<?php

require_once __DIR__ . '/../models/Task.php';

class TaskController {
    private $taskModel;

    public function __construct() {
        $this->taskModel = new Task();
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function validateTaskData($data, $isUpdate = false) {
        $errors = [];
        $allowedStatuses = ['pending', 'in-progress', 'completed'];
        
        if (!$isUpdate) {
            // For creation, title is mandatory
            if (empty($data['title'])) {
                $errors[] = 'title is required';
            }
        }
        
        // Status is optional but must be valid if provided
        if (isset($data['status']) && !in_array($data['status'], $allowedStatuses)) {
            $errors[] = 'status must be one of: ' . implode(', ', $allowedStatuses);
        }

        return $errors;
    }

    public function getAllTasks() {
        $status = $_GET['status'] ?? null;
        $tasks = $this->taskModel->findAll($status);
        $this->sendJsonResponse($tasks);
    }

    public function getTask($id) {
        $task = $this->taskModel->find($id);

        if (!$task) {
            $this->sendJsonResponse(['error' => 'Task not found'], 404);
        }

        $this->sendJsonResponse($task);
    }

    public function createTask() {
        $input = json_decode(file_get_contents('php://input'), true);
        $errors = $this->validateTaskData($input);

        if (!empty($errors)) {
            $this->sendJsonResponse(['errors' => $errors], 400);
        }

        $newTask = $this->taskModel->create($input);
        $this->sendJsonResponse($newTask, 201);
    }

    public function updateTask($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $errors = $this->validateTaskData($input, true);

        if (!empty($errors)) {
            $this->sendJsonResponse(['errors' => $errors], 400);
        }

        $existingTask = $this->taskModel->find($id);
        if (!$existingTask) {
            $this->sendJsonResponse(['error' => 'Task not found'], 404);
        }

        $updatedTask = $this->taskModel->update($id, $input);
        
        if (!$updatedTask) {
             $this->sendJsonResponse(['message' => 'No fields to update'], 200);
        }

        $this->sendJsonResponse($updatedTask);
    }

    public function deleteTask($id) {
        $existingTask = $this->taskModel->find($id);
        if (!$existingTask) {
            $this->sendJsonResponse(['error' => 'Task not found'], 404);
        }

        $this->taskModel->delete($id);
        $this->sendJsonResponse(['message' => 'Task deleted successfully'], 200);
    }
}