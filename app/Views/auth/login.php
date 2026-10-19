<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Facility Care MIMHa</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Facility Care">
    <!-- Sesuaikan path icon iOS ini dengan nama file logo Anda -->
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/logo.png') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f7f6; 
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 35px 20px;
            text-align: center;
        }
        .input-group-text {
            border-right: none;
            background-color: transparent;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-radius: 0.375rem;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #86b7fe;
        }
    </style>
</head>
<body>

    <div class="container px-3 d-flex justify-content-center">
        <div class="card login-card">
            
            <div class="login-header">
                <i class="fas fa-tools fa-3x mb-3 shadow-sm rounded-circle p-3 bg-white text-primary"></i>
                <h4 class="mb-0 fw-bold">Facility Care MIMHa</h4>
            </div>
            
            <div class="card-body p-4 bg-white">
                
                <?php if(session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger d-flex align-items-center p-2 mb-4 small border-0 bg-danger text-white rounded-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div><?= session()->getFlashdata('error') ?></div>
                    </div>
                <?php endif; ?>
                <?php if(session()->getFlashdata('success')): ?>
                    <div class="alert alert-success p-2 mb-4 small border-0 rounded-3 text-center" role="alert">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login/process') ?>" method="post">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-uppercase shadow-sm">
                        Masuk <i class="fas fa-sign-in-alt ms-2"></i>
                    </button>
                    
                </form>
            </div>
            
            <div class="card-footer bg-light border-0 text-center py-3">
                <small class="text-muted fw-semibold">&copy; <?= date('Y') ?> Facility Care MIMHa</small>
            </div>
            
        </div>
    </div>

    <!-- PWA Install Banner -->
    <div id="install-banner" class="alert alert-primary alert-dismissible fade show d-none shadow" role="alert" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 400px; z-index: 9999;">
        <div class="d-flex align-items-center">
            <!-- Menampilkan gambar logo jika ada, jika tidak muncul bisa ganti pakai icon fa -->
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" class="me-3 rounded" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.outerHTML='<i class=\'fas fa-mobile-alt fa-2x me-3 text-primary\'></i>'">
            <div>
                <strong class="text-primary">Instal Aplikasi!</strong><br>
                <span class="small text-muted">Tambahkan ke layar utama HP untuk akses cepat.</span>
            </div>
        </div>
        <button type="button" id="btn-install" class="btn btn-sm btn-primary mt-3 w-100 fw-bold rounded-pill shadow-sm">Instal Sekarang</button>
        <button type="button" class="btn-close" id="btn-close-banner" aria-label="Close"></button>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script PWA -->
    <script>
        // Mendaftarkan Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((registration) => {
                    console.log('ServiceWorker terdaftar dengan scope: ', registration.scope);
                }).catch((err) => {
                    console.log('ServiceWorker pendaftaran gagal: ', err);
                });
            });
        }

        let deferredPrompt;
        const installBanner = document.getElementById('install-banner');
        const btnInstall = document.getElementById('btn-install');
        const btnClose = document.getElementById('btn-close-banner');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // Tampilkan banner
            installBanner.classList.remove('d-none');
        });

        btnInstall.addEventListener('click', async () => {
            if (deferredPrompt) {
                installBanner.classList.add('d-none');
                deferredPrompt.prompt();
                
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`Pilihan user: ${outcome}`);
                deferredPrompt = null;
            }
        });

        btnClose.addEventListener('click', () => {
            installBanner.classList.add('d-none');
        });

        window.addEventListener('appinstalled', () => {
            installBanner.classList.add('d-none');
            deferredPrompt = null;
            console.log('Aplikasi sukses diinstal');
        });
    </script>
</body>
</html>