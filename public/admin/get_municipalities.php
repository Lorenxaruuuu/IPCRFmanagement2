
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


require_once __DIR__ . '/../db_connect.php';

try {

    // read request body (expects JSON with province_id)
    $input = json_decode(file_get_contents('php://input'), true);
    $provinceId = $input['province_id'] ?? null;

    if (!$provinceId) {
        echo json_encode([ 'success' => false, 'message' => 'province_id missing' ]);
        exit;
    }

    // Fetch municipalities for the given province
    $stmt = $pdo->prepare(
        "SELECT id, name, province_id, code
         FROM municipalities
         WHERE province_id = :pid
         ORDER BY name ASC"
    );
    $stmt->execute(['pid' => $provinceId]);
    $municipalities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $municipalities
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>