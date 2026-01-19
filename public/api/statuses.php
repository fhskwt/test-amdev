<?php
session_start();
require_once '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
use App\config;

header('Content-Type: application/json');

try {
    $pdo = Config::getPDO();
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_statuses') {
        $stmt = $pdo->query("SELECT id, name FROM statuses ORDER BY id ASC");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($action === 'add_status') {
        $name = trim($_POST['name'] ?? '');
        if (!$name) exit(json_encode(['error' => 'Empty name']));

        $stmt = $pdo->prepare("INSERT IGNORE INTO statuses (name) VALUES (?)");
        $stmt->execute([$name]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_status') {
        $id = $_POST['id'];

        // Проверка: нельзя удалять статус, если он используется в тикетах
        $check = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Нельзя удалить статус, который используется в тикетах!']);
            exit;
        }

        $pdo->prepare("DELETE FROM statuses WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}