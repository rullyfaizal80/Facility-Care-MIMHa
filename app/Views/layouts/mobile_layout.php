<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'App' ?> - Facility Care MIMHa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (Ikon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/custom.css') ?>">
</head>
<body class="mobile-body"> <!-- Class ini memberi padding bawah -->

    <!-- Top App Bar (Header Simpel) -->
    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 text-primary fw-bold">Facility Care MIMHa</span>
            <!-- Ikon notifikasi (opsional) -->
            <a href="#" class="text-secondary"><i class="fas fa-bell fs-5"></i></a>
        </div>
    </nav>

    <!-- Main Content Area (Berada di tengah) -->
    <div class="container mt-3">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <?= $this->renderSection('bottom_nav') ?>
    </nav>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>