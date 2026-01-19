<?php
session_start();
require_once '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
use App\config;

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) exit(json_encode(['error' => 'Unauthorized']));

try {
    $pdo = Config::getPDO();
    $userId = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $isAdmin = (isset($_GET['is_admin']) && ($_SESSION['role'] ?? '') === 'admin');
        $ticketId = $_GET['id'] ?? null;

        if ($ticketId) {
            $sql = "SELECT t.*, u.username as client_name, s.name as status_name,
                    (SELECT GROUP_CONCAT(tg.name) FROM ticket_tags tt 
                     JOIN tags tg ON tt.tag_id = tg.id WHERE tt.ticket_id = t.id) as tags
                    FROM tickets t 
                    JOIN users u ON t.client_id = u.id
                    JOIN statuses s ON t.status_id = s.id
                    WHERE t.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticketId]);
            echo json_encode($stmt->fetch());
            exit;
        }

        // Список тикетов
        $statusFilter = $_GET['status'] ?? null;
        $sort = ($_GET['sort'] === 'asc') ? 'ASC' : 'DESC';

        $sql = "SELECT t.*, s.name as status_name, u.username as client_name,
                (SELECT GROUP_CONCAT(tg.name) FROM ticket_tags tt 
                 JOIN tags tg ON tt.tag_id = tg.id WHERE tt.ticket_id = t.id) as tags
                FROM tickets t 
                LEFT JOIN statuses s ON t.status_id = s.id 
                LEFT JOIN users u ON t.client_id = u.id";

        $params = [];
        if (!$isAdmin) {
            $sql .= " WHERE t.client_id = ?";
            $params[] = $userId;
        } else {
            $sql .= " WHERE 1=1";
        }

        if ($statusFilter) {
            $sql .= " AND t.status_id = ?";
            $params[] = (int)$statusFilter;
        }

        $sql .= " ORDER BY t.created_at $sort";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') {
        $stmt = $pdo->prepare("INSERT INTO tickets (client_id, status_id, title, description) VALUES (?, 1, ?, ?)");
        $stmt->execute([$userId, $_POST['title'], $_POST['description']]);
        echo json_encode(['success' => true]);
    }

    elseif ($action === 'update_admin') {
        $tid = $_POST['ticket_id'];
        $status = $_POST['status_id'];
        $reply = $_POST['reply'];
        $tags = $_POST['tags'] ?? [];

        $stmt = $pdo->prepare("UPDATE tickets SET status_id = ?, admin_answer = ? WHERE id = ?");
        $stmt->execute([$status, $reply, $tid]);

        $pdo->prepare("DELETE FROM ticket_tags WHERE ticket_id = ?")->execute([$tid]);

        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            $stmt = $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
            $stmt->execute([$tagName]);

            $pdo->prepare("INSERT INTO ticket_tags (ticket_id, tag_id) SELECT ?, id FROM tags WHERE name = ?")
                ->execute([$tid, $tagName]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}