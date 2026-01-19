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

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_tags') {
        $stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action === 'add_tag') {
        $name = trim($_POST['name'] ?? '');
        if (!$name) exit(json_encode(['error' => 'Empty name']));

        $stmt = $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
        $stmt->execute([$name]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_tag') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}