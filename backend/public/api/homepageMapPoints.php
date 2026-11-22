<?php
declare(strict_types=1);

require __DIR__ . '/../../src/db.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required'], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo          = db();
    $disease      = isset($_GET['disease']) ? trim((string)$_GET['disease']) : '';
    $disease_type = isset($_GET['disease_type']) ? trim((string)$_GET['disease_type']) : '';

    
    $where  = ["(status IS NULL OR status = 'approved')"];
    $params = [];

    if ($disease !== '') {
        $where[]  = 'disease = ?';
        $params[] = $disease;
    }

    if ($disease_type !== '') {
        $where[]  = 'disease_type = ?';
        $params[] = $disease_type;
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    
    $stmt = $pdo->prepare("
        SELECT
            id,
            disease,
            state,
            county,
            cases,
            report_date,
            created_at,
            disease_type,
            mortality_status
        FROM outbreaks
        $whereSQL
        ORDER BY report_date DESC, id DESC
    ");

    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['id']    = (int)$row['id'];
        $row['cases'] = (int)$row['cases'];
    }
    unset($row);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);

    $is_local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

    if ($is_local) {
        echo json_encode([
            'error'  => 'Server error',
            'detail' => $e->getMessage(),
            'file'   => $e->getFile(),
            'line'   => $e->getLine(),
        ], JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(['error' => 'Server error'], JSON_UNESCAPED_SLASHES);
    }
}