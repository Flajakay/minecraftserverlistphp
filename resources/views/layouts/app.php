<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf() ?>">
    <?= renderMetaTags() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('css/custom.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/server-row.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <main class="flex-grow-1">
        <div class="container-fluid px-4 py-3">
            <?php include __DIR__ . '/../partials/alerts.php'; ?>
            <?= $content ?? '' ?>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
