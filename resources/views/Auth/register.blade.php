<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSWD - Purchase Request Tracking System</title>
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
            font-size: 14px;
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
            max-width: 500px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        input[type="password"],
        input[type="email"],
        select {
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
        input[type="password"]:focus,
        input[type="email"]:focus,
        select:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
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
            margin-top: 10px;
            text-transform: capitalize;
        }

        .btn-primary:hover {
            background: #2a4a73;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30,58,95,0.3);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .form-footer a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid transparent;
            transition: border-color 0.3s;
        }

        .form-footer a:hover {
            border-bottom-color: #1a1a1a;
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
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
            max-width: 400px;
            width: 90%;
            animation: slideDown 0.3s ease;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            color: #2d3748;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-body p {
            margin: 10px 0;
            color: #666;
            line-height: 1.6;
        }

        .modal-body ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #666;
        }

        .modal-body li {
            margin: 8px 0;
        }

        .modal.success .modal-header h2 {
            color: #22863a;
        }

        .modal.success .modal-icon {
            color: #22863a;
        }

        .modal.error .modal-header h2 {
            color: #cb2431;
        }

        .modal.error .modal-icon {
            color: #cb2431;
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-btn-primary {
            background-color: #1e3a5f;
            color: white;
        }

        .modal-btn-primary:hover {
            background-color: #2a4a73;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
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
                    <p>Sign Up to your DSWD account to continue</p>
                </div>

             <!-- Change form to not use Laravel routing -->
<form id="registerForm">
    <!-- Keep all your existing form fields exactly the same, just remove @csrf -->
    
    <div class="form-row">
        <div class="form-group">
            <label for="lastname">Lastname</label>
            <input type="text" id="lastname" name="lastname" required>
        </div>
        <div class="form-group">
            <label for="firstname">Firstname</label>
            <input type="text" id="firstname" name="firstname" required>
        </div>
    </div>

    <div class="form-group">
        <label for="employee_id">Employee ID</label>
        <input type="text" id="employee_id" name="employee_id" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="" disabled selected>Select your role</option>
            <option value="admin">Administrator</option>
            <option value="staff">Staff</option>
            <option value="encoder">Encoder</option>
            <option value="viewer">Viewer</option>
        </select>
    </div>

    <button type="submit" class="btn-primary">Approve to Admin</button>

    <div class="form-footer">
        <p>You have an account? <a href="/login">SIGN IN</a></p>
    </div>
</form>
            </div>
        </div>
    </div>

    <!-- Modal for Success/Error Messages -->
    <div id="responseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon" id="modalIcon">ℹ️</div>
                <h2 id="modalTitle">Message</h2>
            </div>
            <div class="modal-body" id="modalBody">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-primary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
    // Show modal with message
    function showModal(type, title, message) {
        const modal = document.getElementById('responseModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalIcon = document.getElementById('modalIcon');
        const modalMessage = document.getElementById('modalMessage');

        modalTitle.textContent = title;
        modalMessage.innerHTML = message;

        modal.classList.remove('success', 'error');
        
        if (type === 'success') {
            modal.classList.add('success');
            modalIcon.textContent = '✓';
        } else if (type === 'error') {
            modal.classList.add('error');
            modalIcon.textContent = '✕';
        }

        modal.classList.add('show');
    }

    // Close modal
    function closeModal() {
        const modal = document.getElementById('responseModal');
        modal.classList.remove('show');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('responseModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Handle form submission with JavaScript
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // Stop normal form submission
        
        // Get form data
        const formData = {
            lastname: document.getElementById('lastname').value.trim(),
            firstname: document.getElementById('firstname').value.trim(),
            employee_id: document.getElementById('employee_id').value.trim(),
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value,
            role: document.getElementById('role').value
        };

        // Basic validation
        if (!formData.lastname || !formData.firstname || !formData.employee_id || 
            !formData.password || !formData.role) {
            showModal('error', 'Validation Error', 'Please fill in all fields');
            return;
        }

        if (formData.password !== formData.password_confirmation) {
            showModal('error', 'Validation Error', 'Passwords do not match');
            return;
        }

        try {
            // Send data to PHP backend
            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showModal('success', 'Registration Successful!', result.message);
                // Clear form
                document.getElementById('registerForm').reset();
            } else {
                showModal('error', 'Registration Failed', result.message);
            }

        } catch (error) {
            showModal('error', 'Error', 'Could not connect to server. Please try again.');
            console.error('Error:', error);
        }
    });
</script>
</body>
</html>

