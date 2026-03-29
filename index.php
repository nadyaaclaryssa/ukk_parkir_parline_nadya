<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Parline</title>
    <link rel="icon" href="../assets/images/parline.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #3b82f6; 
            --royal-blue: #1e40af;
            
            /* Perubahan: Warna background luar digelapkan agar kontras dengan kotak putih */
            --bg-gradient: linear-gradient(135deg, #e2e8f0 0%, #dbeafe 100%); 
            
            --text-main: #1e293b;
            --white: #ffffff;
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            margin: 0; 
            background: var(--bg-gradient); 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh;
        }

        .login-container {
            width: 100%; max-width: 900px; 
            background: var(--white); border-radius: 40px;
            display: flex; overflow: hidden;
            
            /* Penyesuaian shadow agar terlihat lebih elegan di background gelap */
            box-shadow: 0 30px 60px -12px rgba(30, 64, 175, 0.2); 
            
            margin: 20px;
        }

        /* Sisi Kiri - Logo & Branding */
        .brand-side {
            flex: 1; background: #fcfdfe;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 60px; border-right: 1px solid #f1f5f9;
        }

        .brand-side img { width: 180px; margin-bottom: 25px; }
        .brand-side h2 { color: var(--text-main); font-weight: 800; margin: 0; font-size: 24px; }
        .brand-side p { color: #64748b; font-size: 14px; margin-top: 10px; }

        /* Sisi Kanan - Form Login */
        .form-side {
            flex: 1; padding: 60px;
            display: flex; flex-direction: column; justify-content: center;
        }

        .form-side h1 { font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 10px; }
        .form-side p { color: #64748b; font-size: 14px; margin-bottom: 35px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        
        .input-group input {
            width: 100%; padding: 14px 20px; border-radius: 16px;
            border: 2px solid #f1f5f9; background: #f8fafc;
            font-size: 14px; color: var(--text-main); transition: 0.3s;
        }

        .input-group input:focus {
            outline: none; border-color: var(--primary-blue); background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-login {
            width: 100%; padding: 16px; border-radius: 16px;
            background: var(--primary-blue); color: white; border: none;
            font-size: 16px; font-weight: 700; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .btn-login:hover { background: var(--royal-blue); transform: translateY(-2px); }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container { flex-direction: column; max-width: 450px; }
            .brand-side { padding: 40px; border-right: none; border-bottom: 1px solid #f1f5f9; }
            .form-side { padding: 40px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="brand-side">
             <img src="../assets/images/parline.png" alt="Logo">
            <h2>PARLINE</h2>
            <p>Smart Parking Solution</p>
        </div>

        <div class="form-side">
            <h1>Selamat Datang</h1>
            <p>Silakan login untuk mengelola Website Parline</p>

            <form action="auth/proses_login.php" method="POST">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="•••••" required>
                </div>

                <button type="submit" name="login" class="btn-login">Masuk</button>
            </form>
        </div>
    </div>

</body>
</html>