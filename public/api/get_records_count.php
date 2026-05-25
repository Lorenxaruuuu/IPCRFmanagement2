<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db_connect.php';

try {
    // Get count of both tables combined
    $stmt = $pdo->prepare("
        SELECT (SELECT COUNT(*) FROM ipcrf_records) + (SELECT COUNT(*) FROM ipcrfs) as total_records
    ");

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => (int)$result['total_records']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
