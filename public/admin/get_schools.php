<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db_connect.php';

try {

    // read request body (expects JSON with municipality_id)
    $input = json_decode(file_get_contents('php://input'), true);
    $municipalityId = $input['municipality_id'] ?? null;

    if (!$municipalityId) {
        echo json_encode([ 'success' => false, 'message' => 'municipality_id missing' ]);
        exit;
    }

    // Fetch schools for the given municipality
    $stmt = $pdo->prepare(
        "SELECT id, name, municipality_id, school_id
         FROM schools
         WHERE municipality_id = :mid
         ORDER BY name ASC"
    );
    $stmt->execute(['mid' => $municipalityId]);
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $schools
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>