<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        // Get JSON or form data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['province']) || empty($data['municipality'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit;
        }

        // Insert into ipcrfs table
        $stmt = $pdo->prepare("
            INSERT INTO ipcrfs (
                name,
                province,
                municipality,
                evaluated_file_path,
                scanned_file_path,
                status,
                created_at,
                updated_at
            ) VALUES (
                :name,
                :province,
                :municipality,
                :evaluated_file_path,
                :scanned_file_path,
                :status,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':province' => $data['province'],
            ':municipality' => $data['municipality'],
            ':evaluated_file_path' => 'Pending',
            ':scanned_file_path' => $data['scanned_file_name'] ?? 'Pending',
            ':status' => 'Sent to Zapier'
        ]);

        $id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'IPCRF record created successfully'
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
}
?>
