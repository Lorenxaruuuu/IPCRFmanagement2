<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db_connect.php';

try {

    // Fetch ipcrf records
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            province,
            municipality,
            scanned_file_name,
            scanned_file_path,
            evaluated_file_path,
            status,
            zapier_upload_status,
            google_drive_file_id,
            google_drive_link,
            submitted_at,
            created_at,
            updated_at
        FROM ipcrf
        ORDER BY created_at DESC
    ");

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total' => count($records),
        'data' => $records
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
