<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


require_once __DIR__ . '/../db_connect.php';

try {

    // Get count of active notices
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_notices
        FROM notices
        WHERE is_active = 1
    ");

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => (int)$result['total_notices']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
