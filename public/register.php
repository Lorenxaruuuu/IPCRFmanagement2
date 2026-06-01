<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db_connect.php';

try {
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (empty($data['lastname']) || empty($data['firstname']) || empty($data['email']) || 
        empty($data['password']) || empty($data['role'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($data['password'] !== $data['password_confirmation']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    $email = $data['email'];
    if (!str_contains($email, '@')) {
        $email .= '@dswd.gov.ph';
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $employee_id = explode('@', $email)[0];
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (lastname, firstname, name, email, employee_id, password, role, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $name = $data['firstname'] . ' ' . $data['lastname'];
    
    $stmt->execute([
        $data['lastname'],
        $data['firstname'],
        $name,
        $email,
        $employee_id,
        $hashedPassword,
        $data['role']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Registration successful! Your account is pending superadmin approval. You can log in once approved.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>