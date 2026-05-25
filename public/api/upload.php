<?php

/**
 * IPCRF Upload API Endpoint
 * Handles file uploads and data saving directly with PDO
 * POST /api/upload.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db_connect.php';

try {

    // Validate required fields
    $required = ['role', 'employee_name', 'employee_id', 'province_id', 'municipality_id', 'school_name', 'semester', 'school_year'];
    foreach ($required as $field) {
        if (empty($_POST[$field] ?? null)) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Validate file upload
    if (empty($_FILES['file'] ?? null)) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['file'];
    $allowedExtensions = ['pdf', 'xlsx', 'xls', 'doc', 'docx'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExtensions)) {
        throw new Exception("File type not allowed. Allowed: " . implode(', ', $allowedExtensions));
    }

    if ($file['size'] > 10485760) { // 10MB
        throw new Exception('File size exceeds 10MB limit');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }

    // Validate foreign keys exist
    $stmt = $pdo->prepare("SELECT id FROM provinces WHERE id = ?");
    $stmt->execute([$_POST['province_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid province selected');
    }

    $stmt = $pdo->prepare("SELECT id FROM municipalities WHERE id = ? AND province_id = ?");
    $stmt->execute([$_POST['municipality_id'], $_POST['province_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid municipality selected');
    }

    // Dynamic School Find-or-Create
    $schoolName = trim($_POST['school_name']);
    $stmt = $pdo->prepare("SELECT id FROM schools WHERE name = ? AND municipality_id = ?");
    $stmt->execute([$schoolName, $_POST['municipality_id']]);
    $schoolRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($schoolRow) {
        $schoolId = $schoolRow['id'];
    } else {
        // Generate a clean school code
        $schoolCode = 'SCH-' . strtoupper(bin2hex(random_bytes(3)));
        $insSchool = $pdo->prepare("INSERT INTO schools (name, municipality_id, code, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $insSchool->execute([$schoolName, $_POST['municipality_id'], $schoolCode]);
        $schoolId = $pdo->lastInsertId();
    }

    // Dynamic Employee Find-or-Create
    $empId = trim($_POST['employee_id']);
    $empName = trim($_POST['employee_name']);
    $role = trim($_POST['role']);

    $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ?");
    $stmt->execute([$empId]);
    $employeeRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employeeRow) {
        $employeeDbId = $employeeRow['id'];
        
        // update school and role if changed
        $upStmt = $pdo->prepare("UPDATE employees SET school_id = ?, role = ? WHERE id = ?");
        $upStmt->execute([$schoolId, $role, $employeeDbId]);
    } else {
        $parts = explode(' ', $empName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';
        
        $insStmt = $pdo->prepare("INSERT INTO employees (employee_id, first_name, last_name, school_id, role, email, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $insStmt->execute([$empId, $firstName, $lastName, $schoolId, $role, $empId . '@example.com']);
        $employeeDbId = $pdo->lastInsertId();
    }

    // Check for existing duplicate record in ipcrf_records
    $dupStmt = $pdo->prepare(
        "SELECT id FROM ipcrf_records
         WHERE employee_id = ? AND semester = ? AND school_year = ? AND role = ?
         LIMIT 1"
    );
    $dupStmt->execute([$employeeDbId, $_POST['semester'], $_POST['school_year'], $role]);
    if ($dupStmt->fetch()) {
        throw new Exception('A record for this employee and period already exists');
    }

    // Create storage directory if not exists
    $storagePath = __DIR__ . '/../../storage/app/private/ipcrf_records';
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0755, true);
    }

    // Generate unique filename
    $fileName = uniqid('ipcrf_') . '.' . $fileExt;
    $filePath = $storagePath . '/' . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save file');
    }

    // Save to database
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        "INSERT INTO ipcrf_records (
            employee_id, uploaded_by, file_path, file_name, semester, school_year, role, status, uploaded_at, created_at, updated_at
        ) VALUES (
            :employee_id, :uploaded_by, :file_path, :file_name, :semester, :school_year, :role, :status, :uploaded_at, NOW(), NOW()
        )"
    );

    $result = $stmt->execute([
        ':employee_id' => $employeeDbId,
        ':uploaded_by' => null, // Set to null to prevent foreign key violations if user ID 1 doesn't exist
        ':file_path' => 'ipcrf_records/' . $fileName,
        ':file_name' => $file['name'],
        ':semester' => $_POST['semester'],
        ':school_year' => $_POST['school_year'],
        ':role' => $role,
        ':status' => 'Sent to Zapier',
        ':uploaded_at' => $now
    ]);

    if (!$result) {
        // Delete uploaded file if database insert fails
        unlink($filePath);
        throw new Exception('Failed to save record to database');
    }

    $insertId = $pdo->lastInsertId();

    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'IPCRF uploaded successfully',
        'data' => [
            'id' => $insertId,
            'employee_name' => $_POST['employee_name'],
            'file_name' => $file['name'],
            'uploaded_at' => $now
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);

    // Clean up file if there was an error
    if (isset($filePath) && file_exists($filePath)) {
        @unlink($filePath);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    // Clean up file if there was an error
    if (isset($filePath) && file_exists($filePath)) {
        @unlink($filePath);
    }
}
?>
