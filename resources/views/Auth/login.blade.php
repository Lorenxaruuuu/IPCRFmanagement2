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
            background: url('{{ asset("images/BG.jpg") }}') 30% center / cover no-repeat fixed;
        }

        .container {
            display: flex;
            height: 100vh;
            background: rgba(0, 0, 0, 0.2);
        }

        /* Left Panel - Blue Section */
        .left-panel {
            width: 60%;
            background: linear-gradient(135deg, rgba(0, 102, 204, 0.8) 0%, rgba(0, 68, 153, 0.9) 100%);
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
            z-index: 1;
        }

        .logo {
            height: 75px;
            width: auto;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 10px 20px;
        }

        .logo img {
            height: 100%;
            width: auto;
            object-fit: contain;
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
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
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
                    <img src="{{ asset('images/dswd.jpg') }}" alt="DSWD Logo">
                    <img src="{{ asset('images/pantawid.jpg') }}" alt="Pantawid Logo">
                    <img src="{{ asset('images/bagong.jpg') }}" alt="Bagong Pilipinas Logo">
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

                <!-- EmailJS -->
                <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/index.min.js"></script>

                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">DSWD Email</label>
                        <input type="text" id="email" name="email" placeholder="e.g. employee@dswd.gov.ph" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <!-- reCAPTCHA widget -->
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" style="margin-bottom: 25px; display: flex; justify-content: center;"></div>
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Verification Code Section (Hidden by default) -->
                    <div id="verificationSection" style="display: none; margin-bottom: 20px;">
                        <div style="background: #e8f4fd; border: 1px solid #b3d9e8; border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                            <p style="font-size: 13px; color: #0066cc; margin: 0; font-weight: 600;">✓ Verification code sent to your email</p>
                        </div>
                        <div class="form-group">
                            <label for="verificationCode">Enter Verification Code</label>
                            <input type="text" id="verificationCode" name="verificationCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" style="font-size: 18px; letter-spacing: 5px; text-align: center;">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">Sign In to System</button>

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
    let verificationCodeSent = false;
    let storedVerificationCode = null;
    let currentEmail = null;

    // Load stored user info on page load
    window.addEventListener('DOMContentLoaded', async function() {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try {
                const user = JSON.parse(storedUser);
                if (user && (user.email || user.employee_id)) {
                    document.getElementById('email').value = user.email || user.employee_id;
                    document.getElementById('remember').checked = true;
                }
            } catch (e) {
                console.error('Error parsing stored user data:', e);
            }
        }
    });

    // Generate verification code
    function generateVerificationCode() {
        return Math.floor(100000 + Math.random() * 900000).toString();
    }

    // Send verification email via EmailJS REST API
    async function sendVerificationEmail(email, code) {
        try {
            // Validate inputs
            if (!email || !code) {
                console.error('Invalid email or code:', { email, code });
                alert('Invalid email address or code. Please try again.');
                return false;
            }

            const serviceId = '{{ env("EMAILJS_SERVICE_ID") }}';
            const templateId = '{{ env("EMAILJS_TEMPLATE_ID") }}';
            const publicKey = '{{ env("EMAILJS_PUBLIC_KEY") }}';

            // Check if environment variables are configured
            if (!serviceId || !templateId || !publicKey) {
                console.error('Missing EmailJS configuration:', { 
                    serviceId: serviceId ? 'SET' : 'MISSING',
                    templateId: templateId ? 'SET' : 'MISSING',
                    publicKey: publicKey ? 'SET' : 'MISSING'
                });
                alert('Email service is not properly configured. Please contact IT support.');
                return false;
            }

            console.log('Sending email via EmailJS REST API...', { email, code });
            
            const payload = {
                service_id: serviceId,
                template_id: templateId,
                user_id: publicKey,
                template_params: {
                    to_email: email,
                    verification_code: code,
                    user_email: email,
                    message: `Your verification code is: ${code}. This code expires in 10 minutes.`
                }
            };

            console.log('EmailJS Payload:', JSON.stringify(payload, null, 2));

            const response = await fetch('https://api.emailjs.com/api/v1.0/email/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const responseText = await response.text();
            console.log('EmailJS Response:', response.status, responseText);

            if (response.ok) {
                console.log('Email sent successfully via REST API');
                return true;
            } else {
                console.error('EmailJS REST API Error:', response.status, responseText);
                
                // Provide helpful error messages
                if (responseText.includes('recipients address is empty')) {
                    alert('Email configuration error: Check your EmailJS template setup. The "to_email" parameter may not be configured correctly in your template.');
                } else if (responseText.includes('Invalid service ID')) {
                    alert('Email service configuration error. Please contact IT support.');
                } else {
                    alert('Failed to send verification code: ' + responseText);
                }
                return false;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Failed to send verification code. Please try again. Error: ' + error.message);
            return false;
        }
    }

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const verificationCode = document.getElementById('verificationCode').value.trim();
        const remember = document.getElementById('remember').checked;
        const recaptchaResponse = grecaptcha.getResponse();

        // Check if this is a superadmin account (skip verification)
        const isSuperadmin = email.toLowerCase().includes('superadmin') || email.toLowerCase() === 'superadmin@deped.gov.ph' || email.toLowerCase() === 'superadmin@dswd.gov.ph';

        // STEP 1: Send verification code (skip for superadmin)
        if (!verificationCodeSent && !isSuperadmin) {
            // Validate reCAPTCHA
            if (!recaptchaResponse) {
                alert('Please complete the reCAPTCHA verification.');
                return;
            }

            // Show loading spinner
            document.getElementById('loadingOverlay').classList.add('active');
            document.getElementById('loadingOverlay').querySelector('.loading-text').textContent = 'Sending verification code...';

            // Generate and send code
            const code = generateVerificationCode();
            storedVerificationCode = code;
            currentEmail = email;

            const emailSent = await sendVerificationEmail(email, code);
            
            document.getElementById('loadingOverlay').classList.remove('active');
            document.getElementById('loadingOverlay').querySelector('.loading-text').textContent = 'Signing in...';

            if (emailSent) {
                // Show verification section and hide login fields
                document.getElementById('verificationSection').style.display = 'block';
                document.getElementById('email').disabled = true;
                document.getElementById('password').disabled = true;
                document.getElementById('remember').disabled = true;
                document.querySelector('.forgot-password').style.pointerEvents = 'none';
                document.querySelector('.forgot-password').style.opacity = '0.5';
                
                document.getElementById('submitBtn').textContent = 'Verify Code';
                verificationCodeSent = true;
                
                // Focus on verification code input
                document.getElementById('verificationCode').focus();
            }
            return;
        }

        // STEP 2: Verify code (skip for superadmin) and login
        if (!isSuperadmin && !verificationCode) {
            alert('Please enter the verification code sent to your email.');
            return;
        }

        if (!isSuperadmin && verificationCode !== storedVerificationCode) {
            alert('Invalid verification code. Please try again.');
            document.getElementById('verificationCode').value = '';
            document.getElementById('verificationCode').focus();
            return;
        }

        // Validate reCAPTCHA for superadmin
        if (isSuperadmin && !recaptchaResponse) {
            alert('Please complete the reCAPTCHA verification.');
            return;
        }

        // Show loading spinner
        document.getElementById('loadingOverlay').classList.add('active');

        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                    g_recaptcha_response: recaptchaResponse
                })
            });

            const result = await response.json();
            
            // Hide loading spinner
            document.getElementById('loadingOverlay').classList.remove('active');

            if (result.success) {
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
                
                // Reset verification state
                verificationCodeSent = false;
                storedVerificationCode = null;
                
                // Redirect based on response
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 500);
            } else {
                alert(result.message || 'Login failed. Please try again.');
                // Reset form for retry
                resetVerificationFlow();
            }

        } catch (error) {
            document.getElementById('loadingOverlay').classList.remove('active');
            alert('Connection error. Please check your internet connection.');
            console.error('Error:', error);
            resetVerificationFlow();
        }
    });

    // Reset verification flow
    function resetVerificationFlow() {
        verificationCodeSent = false;
        storedVerificationCode = null;
        document.getElementById('verificationSection').style.display = 'none';
        document.getElementById('email').disabled = false;
        document.getElementById('password').disabled = false;
        document.getElementById('remember').disabled = false;
        document.querySelector('.forgot-password').style.pointerEvents = 'auto';
        document.querySelector('.forgot-password').style.opacity = '1';
        document.getElementById('submitBtn').textContent = 'Sign In to System';
        document.getElementById('verificationCode').value = '';
        grecaptcha.reset();
    }

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