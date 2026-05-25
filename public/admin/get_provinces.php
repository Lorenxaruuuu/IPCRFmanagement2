<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


require_once __DIR__ . '/../db_connect.php';

try {

    // Fetch provinces
    $stmt = $pdo->prepare("
        SELECT id, name, code, region
        FROM provinces
        ORDER BY name ASC
    ");

    $stmt->execute();
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $provinces
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>