<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TodoApi\Database;
use TodoApi\Todo;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'])  ->notEmpty();
$dotenv->required('DB_PASSWORD');

// Set the content type to JSON
header('Content-Type: application/json');

// Get the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

//extract api path from the request URI
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = '/todo-api/public'; 
$Path = substr($path, strlen($basePath));

//check api version
if (!preg_match('#^/api/v1/tasks(?:/([0-9]+))?/?$#', $Path, $matches)) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint Not Found']);
    exit;
}

//Task ID if present in the URL, otherwise set to null
$id = isset($matches[1]) ? (int)$matches[1] : null;

//Read JSON request body
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

//initialize classes
try {
    $db = new Database();
    $todo = new Todo($db);
    switch ($method) {
        case 'GET':
            //retrieves a specific task if ID is provided, otherwise retrieves all tasks with optional filters
            if ($id !== null) {
                $task = $todo->getById($id);

                if ($task === false) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task Not Found']);
                    break;
                } 
                               
                    echo json_encode($task);
                
            } else {
                $tasks = $todo->getAll($_GET);
                echo json_encode($tasks);


if (empty($tasks)) {
    echo json_encode([
        'message' => 'No tasks found matching the given filters',
        'data' => []
    ]);
} else {
    echo json_encode([
        'message' => 'Tasks retrieved successfully',
        'count' => count($tasks),
        'data' => $tasks
    ]);
}

            }
            break;

        case 'POST':
          if ($id !== null) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID should not be provided for creation']);
                break;
            }

            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid JSON body');
            }

            $taskId = $todo->create($data);

            http_response_code(201);
            echo json_encode(['message' => 'Task Created',
             'task_id' => $taskId]);
            break;

            case 'PUT':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID is required for update']);
                break;
            }

           if (!is_array($data)) {
                throw new \InvalidArgumentException('
                Invalid JSON body'
                );
            }
            //check if task exists
            if ($todo->getById($id) === false) {
                http_response_code(404);
                echo json_encode(['error' => 'Task Not Found']);
                break;
            }

            //update the task
            $todo->update($id, $data);
            echo json_encode(['message' => 'Task Updated']);
            break;


        case 'DELETE':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['error' => 'Task ID is required for deletion']);
                break;
            }

            $deleted = $todo->delete($id);
            if ($deleted) {
                echo json_encode(['message' => 'Task Deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task Not Found']);
            }
            break;

        default:
            header('Allow: GET, POST, PUT, DELETE');
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
    }


} catch (InvalidArgumentException $e) {
    http_response_code(400);

    echo json_encode(['error' => $e->getMessage()]);


} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode(['error' => 'Internal Server Error']);
    exit;
}