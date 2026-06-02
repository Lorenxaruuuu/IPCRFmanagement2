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
            font-size: 14px;
            line-height: 1.6;
            max-width: 400px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Right Panel - Form Section */
        .right-panel {
            width: 40%;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
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
                    <p>Sign Up to your DSWD account to continue</p>
                </div>

             <!-- IPCRF Registration Form -->
<form id="registerForm">

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
        <label for="email">DSWD Email</label>
        <input type="text" id="email" name="email" placeholder="e.g. employee@dswd.gov.ph" required>
    </div>

    <div class="form-group">
        <label for="position_id">Position / Designation *</label>
        <select id="position_id" name="position_id" required>
            <option value="" disabled selected>Select your position</option>
            @foreach(\App\Models\Position::active()->orderBy('name')->get() as $pos)
            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" placeholder="e.g. RPMO">
        </div>
        <div class="form-group">
            <label for="office">Office / Division</label>
            <input type="text" id="office" name="office" placeholder="e.g. Finance Division">
        </div>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn-primary">Sign Up</button>

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
        e.preventDefault();

        const posEl  = document.getElementById('position_id');
        const formData = {
            lastname:              document.getElementById('lastname').value.trim(),
            firstname:             document.getElementById('firstname').value.trim(),
            email:                 document.getElementById('email').value.trim(),
            password:              document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value,
            position_id:           posEl ? posEl.value : '',
            department:            (document.getElementById('department') || {value:''}).value.trim(),
            office:                (document.getElementById('office') || {value:''}).value.trim(),
        };

        if (!formData.lastname || !formData.firstname || !formData.email || !formData.password) {
            showModal('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }
        if (formData.password !== formData.password_confirmation) {
            showModal('error', 'Validation Error', 'Passwords do not match');
            return;
        }

        try {
            const response = await fetch('{{ route("register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showModal('success', 'Registration Successful!', result.message);
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
