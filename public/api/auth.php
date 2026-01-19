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

    if ($action === 'me') {
        echo json_encode(['authorized' => isset($_SESSION['user_id']), 'user_id' => $_SESSION['user_id'] ?? null, 'role' => $_SESSION['role'] ?? 'client']);
        exit;
    }

    elseif ($action === 'register' || $action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) throw new Exception("Заполните поля");

        if ($action === 'register') {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hash]);
                $userId = $pdo->lastInsertId();

                $defaultRole = 'client';
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = $defaultRole;
            }
            catch (PDOException $e) {
                // Код ошибки 23000 — это общее нарушение ограничений (Integrity constraint violation)
                // Внутри него MySQL отдает код 1062 для дубликатов
                if ($e->errorInfo[1] == 1062) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Этот логин уже занят, выберите другой']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Ошибка БД: ' . $e->getMessage()]);
                }
                exit;
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Неверный логин/пароль");
            }

            $userId = $user['id'];
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $user['role']; // КРИТИЧЕСКИ ВАЖНО
        }

        echo json_encode(['success' => true, 'role' => $_SESSION['role']]);
        exit;
    }

    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}