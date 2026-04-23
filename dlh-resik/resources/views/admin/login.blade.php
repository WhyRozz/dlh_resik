<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin Simpelsi</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo_simpelsi.png') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .header {
            position: fixed; top: 0; left: 0; width: 100%;
            background: #2e8b57; padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 10; color: white; min-height: 60px;
        }
        .header-logo { width: 36px; height: 36px; margin-right: 8px; border-radius: 50%; overflow: hidden; }
        .header-title { font-size: 1.3em; font-weight: bold; }
        .header-title span { font-size: 0.75em; font-weight: normal; display: block; }
        .exit-btn {
            background: white; color: #2e8b57; padding: 6px 12px;
            border-radius: 5px; font-weight: bold; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            transition: all 0.2s ease; white-space: nowrap;
        }
        .exit-btn:hover { background: #e6ffe6; transform: scale(1.03); }
        .login-card {
            width: 100%; max-width: 600px; background: white;
            border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden; display: flex; margin-top: 80px;
        }
        .login-form-section { flex: 1; padding: 40px 30px; display: flex; flex-direction: column; justify-content: center; }
        .login-image-section {
            flex: 1; min-width: 250px; background: #f8fdf9;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .login-image-section img {
            max-width: 100%; max-height: 280px; border-radius: 16px;
            object-fit: cover; box-shadow: 0 8px 20px rgba(46,139,87,0.15);
        }
        .login-header { text-align: center; margin-bottom: 25px; }
        .login-title { font-size: 24px; font-weight: 800; color: #2e8b57; margin-bottom: 5px; }
        .login-subtitle { font-size: 18px; color: #226b42; font-weight: 600; }
        .form-group { text-align: left; margin-bottom: 20px; position: relative; display: flex; flex-direction: column; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #2e8b57; font-size: 14px; line-height: 1.2; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0;
            border-radius: 10px; font-size: 16px; transition: all 0.3s ease; box-sizing: border-box;
        }
        input:focus { outline: none; border-color: #2e8b57; box-shadow: 0 0 0 3px rgba(46,139,87,0.2); }
        .eye-icon {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            cursor: pointer; width: 20px; height: 20px; z-index: 10;
        }
        .eye-icon img { width: 100%; height: 100%; object-fit: contain; }
        .btn-login {
            background: #2e8b57; color: white; border: none; width: 100%;
            padding: 12px; border-radius: 10px; font-size: 16px; font-weight: 700;
            cursor: pointer; margin-bottom: 10px; transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(46,139,87,0.2);
        }
        .btn-login:hover { background: #226b42; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(46,139,87,0.3); }
        .btn-reset {
            background: transparent; color: #2e8b57; border: 2px solid #2e8b57;
            width: 100%; padding: 10px; border-radius: 10px; font-size: 14px;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-reset:hover { background: rgba(46,139,87,0.05); }
        .alert {
            padding: 10px; border-radius: 8px; margin-top: 15px;
            font-weight: 600; background: #fff3cd; color: #856404;
            border: 1px solid #ffeaa7; animation: fadeInUp 0.5s ease;
        }
        .popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5); display: flex;
            justify-content: center; align-items: center; z-index: 1000; display: none;
        }
        .popup-content {
            background: white; padding: 20px; border-radius: 10px;
            width: 300px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: popIn 0.3s ease;
        }
        .popup-content.error { border-left: 5px solid #dc3545; }
        .popup-content h3 { margin: 0 0 10px 0; font-size: 18px; }
        .popup-content p { margin: 0; color: #555; }
        .popup-btn {
            margin-top: 15px; padding: 8px 16px; background: #2e8b57;
            color: white; border: none; border-radius: 5px; cursor: pointer;
        }
        .popup-btn:hover { background: #226b42; }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) {
            .login-card { margin-top: 100px; flex-direction: column; }
            .login-form-section { padding: 30px 20px; }
            .login-image-section { padding: 15px; order: -1; }
            .login-image-section img { max-height: 220px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('assets/logo.jpg') }}" alt="Logo Simpelsi" class="header-logo">
            <div class="header-title">
                Selamat Datang Di<br><span>SimpelSi</span>
            </div>
        </div>
        <a href="{{ route('landing') }}" class="exit-btn">← KELUAR</a>
    </div>

    <div class="login-card">
        <div class="login-form-section">
            <div class="login-header">
                <div class="login-title">LOGIN ADMIN</div>
                <div class="login-subtitle">Simpelsi</div>
            </div>

            <form method="POST" action="{{ route('admin.login.post') }}" id="loginForm" novalidate>
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="password">Sandi</label>
                    <div style="position: relative; width: 100%;">
                        <input type="password" id="password" name="password" />
                        <span class="eye-icon" onclick="togglePasswordVisibility()">
                            <img src="{{ asset('assets/hide.png') }}" alt="Hide" id="eyeIcon">
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn-login">Login</button>
                <button type="button" class="btn-reset" onclick="resetForm()">Kosongkan Kolom</button>
            </form>
        </div>

        <div class="login-image-section">
            <img src="{{ asset('assets/Login.jpg') }}" alt="Login SIMPELSI">
        </div>
    </div>

    <div id="popup" class="popup-overlay">
        <div class="popup-content">
            <h3 id="popup-title">Judul Popup</h3>
            <p id="popup-message">Pesan popup</p>
            <button class="popup-btn" onclick="closePopup()">Tutup</button>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById('loginForm').reset();
            document.getElementById('email').focus();
        }

        function showPopup(title, message) {
            document.getElementById('popup-title').textContent = title;
            document.getElementById('popup-message').textContent = message;
            document.getElementById('popup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        function togglePasswordVisibility() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (password.type === "password") {
                password.type = "text";
                eyeIcon.src = "{{ asset('assets/show.png') }}";
            } else {
                password.type = "password";
                eyeIcon.src = "{{ asset('assets/hide.png') }}";
            }
        }

        // Validasi & submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email) return showPopup('Kesalahan!', 'Email wajib diisi.');
            if (!password) return showPopup('Kesalahan!', 'Sandi wajib diisi.');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return showPopup('Kesalahan!', 'Format email tidak valid.');
            if (password.length < 8) return showPopup('Kesalahan!', 'Sandi minimal 8 karakter.');
            if (password.length > 50) return showPopup('Kesalahan!', 'Sandi maksimal 50 karakter.');
            if (!/^[a-zA-Z0-9\s]+$/.test(password)) return showPopup('Kesalahan!', 'Sandi tidak boleh mengandung karakter spesial.');

            this.submit();
        });

        // Tampilkan error Laravel
        @if ($errors->any())
            showPopup('Gagal Login!', '{{ addslashes($errors->first()) }}');
        @endif
    </script>
</body>
</html>
