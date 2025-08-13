<?php

require_once __DIR__ . '/../controllers/TaskController.php';

$taskController = new TaskController();

switch ($method) {
    case 'GET':
        // Handle GET /tasks and GET /tasks/{id}
        if (preg_match('/^tasks\/(\d+)$/', $uri, $matches)) {
            $taskController->getTask($matches[1]);
        } elseif ($uri === 'tasks') {
            $taskController->getAllTasks();
        }
        break;

    case 'POST':
        // Handle POST /tasks
        if ($uri === 'tasks') {
            $taskController->createTask();
        }
        break;
        
    case 'PUT':
        // Handle PUT /tasks/{id}
        if (preg_match('/^tasks\/(\d+)$/', $uri, $matches)) {
            $taskController->updateTask($matches[1]);
        }
        break;
        
    case 'DELETE':
        // Handle DELETE /tasks/{id}
        if (preg_match('/^tasks\/(\d+)$/', $uri, $matches)) {
            $taskController->deleteTask($matches[1]);
        }
        break;

    default:
        // Handle other methods or unknown routes
        http_response_code(405);
        header('Allow: GET, POST, PUT, DELETE');
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}