<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSWD - Purchase Request Tracking System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Left Panel - Blue Section */
        .left-panel {
            width: 45%;
            background: linear-gradient(135deg, #0066cc 0%, #004499 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
        }

        .logo-section {
            position: absolute;
            top: 40px;
            left: 60px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .logo svg {
            width: 45px;
            height: 45px;
        }

        .logo-text {
            color: white;
        }

        .logo-text .republic {
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .logo-text .dswd {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            line-height: 1;
        }

        .logo-text .dept {
            font-size: 11px;
            opacity: 0.9;
            color: #ffd700;
        }

        .hero-content {
            z-index: 1;
            margin-top: 80px;
        }

        .hero-content h1 {
            color: white;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .hero-content .highlight {
            color: #ffd700;
        }

        .hero-content .subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            line-height: 1.6;
            max-width: 400px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Right Panel - Form Section */
        .right-panel {
            width: 55%;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
        }

        .form-header {
            margin-bottom: 35px;
        }

        .form-header .secure-tag {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-header h2 {
            font-size: 36px;
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 11px;
            color: #444;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
            color: #333;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            margin-top: -5px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #0066cc;
        }

        .remember-me label {
            margin: 0;
            font-size: 11px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            font-weight: 600;
        }

        .forgot-password {
            font-size: 12px;
            color: #1e3a5f;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #0066cc;
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: #1e3a5f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: capitalize;
        }

        .btn-primary:hover {
            background: #2a4a73;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30,58,95,0.3);
        }

        .signup-prompt {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .signup-prompt a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }

        .signup-prompt a:hover {
            color: #0066cc;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #888;
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ccc;
        }

        .divider span {
            padding: 0 15px;
            white-space: nowrap;
        }

        .support-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .support-icon {
            width: 40px;
            height: 40px;
            background: #ffeaea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .support-icon svg {
            width: 20px;
            height: 20px;
            fill: #e74c3c;
        }

        .support-info h4 {
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .support-info p {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .left-panel {
                display: none;
            }
            .right-panel {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .form-options {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }

        /* Loading Spinner */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            color: white;
            margin-top: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div style="text-align: center;">
            <div class="spinner"></div>
            <div class="loading-text">Signing in...</div>
        </div>
    </div>

    <div class="container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="logo-section">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="DSWD Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="logo-text">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="dswd">DSWD</div>
                    <div class="dept">Dept. of Social Welfare and Development</div>
                </div>
            </div>
            
            <div class="hero-content">
                <h1><br><span class="highlight">IPCRF</span><br>Management System</h1>
                <p class="subtitle">A Pantawid Pamilyang Pilipino Program and Holy Cross of Davao College Initiative</p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="form-container">
                <div class="form-header">
                    <div class="secure-tag">Secure Access Portal</div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to your DSWD account to continue</p>
                </div>

               <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-primary">Sign In to System</button>

                    <div class="signup-prompt">
                        <p>Don't have an account? <a href="{{ route('register') }}">SIGN UP</a></p>
                    </div>
                </form>
                <div class="divider">
                    <span>Need assistance ?</span>
                </div>

                <div class="support-box">
                    <div class="support-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-2.2 2.2a15.057 15.057 0 01-6.59-6.59l2.2-2.21c.28-.26.36-.65.25-1.01A11.36 11.36 0 018.59 3.99c0-.55-.45-1-1-1H4.08c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1zM5.03 5h1.5c.07.88.22 1.75.45 2.58l-1.2 1.21c-.4-1.21-.66-2.47-.75-3.79zM19 18.97c-1.32-.09-2.6-.35-3.8-.76l1.2-1.2c.85.24 1.72.39 2.6.45v1.51z"/>
                        </svg>
                    </div>
                    <div class="support-info">
                        <h4>Contact IT Support</h4>
                        <p>For account issues, call ext. 2100 or email<br>itsupport@dswd.gov.ph</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <script>
    // Load stored user info on page load
    window.addEventListener('DOMContentLoaded', function() {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try {
                const user = JSON.parse(storedUser);
                // Auto-fill employee ID if remember me was checked
                if (user && user.employee_id) {
                    document.getElementById('employee_id').value = user.employee_id;
                    document.getElementById('remember').checked = true;
                }
            } catch (e) {
                console.error('Error parsing stored user data:', e);
            }
        }
    });

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading spinner
        document.getElementById('loadingOverlay').classList.add('active');
        
        const employee_id = document.getElementById('employee_id').value.trim();
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;

        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    employee_id: employee_id,
                    password: password
                })
            });

            const result = await response.json();
            
            // Hide loading spinner
            document.getElementById('loadingOverlay').classList.remove('active');

            if (result.success) {
                // Save user data to localStorage
                const userData = {
                    employee_id: result.user.employee_id,
                    name: result.user.name,
                    firstname: result.user.firstname,
                    lastname: result.user.lastname,
                    email: result.user.email,
                    role: result.user.role,
                    timestamp: new Date().toISOString()
                };
                
                if (remember) {
                    localStorage.setItem('user', JSON.stringify(userData));
                    localStorage.setItem('rememberMe', 'true');
                } else {
                    sessionStorage.setItem('user', JSON.stringify(userData));
                    localStorage.removeItem('user');
                    localStorage.removeItem('rememberMe');
                }
                
                // Redirect based on response
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 500);
            } else {
                alert(result.message || 'Login failed. Please try again.');
            }

        } catch (error) {
            document.getElementById('loadingOverlay').classList.remove('active');
            alert('Connection error. Please check your internet connection.');
            console.error('Error:', error);
        }
    });

    // Function to get stored user data
    window.getStoredUser = function() {
        const stored = localStorage.getItem('user') || sessionStorage.getItem('user');
        if (stored) {
            try {
                return JSON.parse(stored);
            } catch (e) {
                return null;
            }
        }
        return null;
    };

    // Function to clear stored user data
    window.clearStoredUser = function() {
        localStorage.removeItem('user');
        localStorage.removeItem('rememberMe');
        sessionStorage.removeItem('user');
    };
</script>
</body>
</html>