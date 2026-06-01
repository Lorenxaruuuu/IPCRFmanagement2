<?php
session_start();
header('Content-Type: application/json');

// Load .env variables so getenv() works in this standalone file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

require_once __DIR__ . '/db_connect.php';

try {
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both DSWD Email and Password']);
        exit;
    }
    
    $raw_input = $data['email'] ?? '';
    if (!str_contains($email, '@')) {
        $email .= '@dswd.gov.ph';
    }

    // Verify Google reCAPTCHA
    $recaptchaResponse = $data['g_recaptcha_response'] ?? '';
    $recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY');
    if (empty($recaptchaResponse) || empty($recaptchaSecret)) {
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
        exit;
    }
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $captchaResult = json_decode($verifyResponse);
    if (!$captchaResult->success) {
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
        exit;
    }
    
    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR email = ? OR employee_id = ? OR employee_id = ?");
    $stmt->execute([$email, $raw_input, $email, $raw_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid DSWD Email or Password']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid DSWD Email or Password']);
        exit;
    }


    // Check approval status (unless superadmin)
    if ($user['role'] !== 'superadmin' && !$user['approved']) {
        echo json_encode(['success' => false, 'message' => 'Your account is pending superadmin approval.']);
        exit;
    }
    
    // Store user data in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['firstname'] = $user['firstname'];
    $_SESSION['lastname'] = $user['lastname'];
    $_SESSION['email'] = $user['email'];
    
    // Determine redirect URL based on role
    $redirectUrl = '/home'; // Default redirect
    
    switch($user['role']) {
        case 'superadmin':
            $redirectUrl = '/superadmin/dashboard2';
            break;
        case 'encoder':
            $redirectUrl = '/encoder';
            break;
        case 'admin':
            $redirectUrl = '/admins';
            break;
        case 'staff':
            $redirectUrl = '/home';
            break;
        case 'viewer':
            $redirectUrl = '/home';
            break;
        default:
            $redirectUrl = '/home';
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful!',
        'redirect' => $redirectUrl,
        'role' => $user['role'],
        'user' => [
            'id' => $user['id'],
            'employee_id' => $user['employee_id'],
            'name' => $user['firstname'] . ' ' . $user['lastname'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>