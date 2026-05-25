<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


require_once __DIR__ . '/../db_connect.php';

try {

    // Get count of users with staff role
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_staff
        FROM users
        WHERE role = 'staff'
    ");

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => (int)$result['total_staff']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
