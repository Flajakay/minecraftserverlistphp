<?php

use App\Core\System\MigrationRunner;

$installLockPath = __DIR__ . '/storage/installed.lock';

if (file_exists($installLockPath)) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $siteUrl = $_POST['site_url'] ?? '';
    $siteTitle = $_POST['site_title'] ?? 'Minecraft Server List';

    try {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $statements = explode(';', $schema);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                if (strpos($statement, 'INSERT INTO `users`') !== false) {
                    $adminPasswordHash = password_hash('admin', PASSWORD_ARGON2ID);
                    $statement = str_replace(
                        "'password_here'",
                        "'" . $adminPasswordHash . "'",
                        $statement
                    );
                }
                $pdo->exec($statement);
            }
        }

        $runner = new MigrationRunner($pdo, __DIR__ . '/database/migrations');
        $runner->runAllPending();

        $config = "<?php\n\nreturn [\n";
        $config .= "    'name' => '" . addslashes($siteTitle) . "',\n";
        $config .= "    'url' => '" . addslashes($siteUrl) . "',\n";
        $config .= "    'timezone' => 'America/New_York',\n";
        $config .= "    'db' => [\n";
        $config .= "        'host' => '" . addslashes($dbHost) . "',\n";
        $config .= "        'username' => '" . addslashes($dbUser) . "',\n";
        $config .= "        'password' => '" . addslashes($dbPass) . "',\n";
        $config .= "        'database' => '" . addslashes($dbName) . "'\n";
        $config .= "    ]\n";
        $config .= "];\n";

        file_put_contents(__DIR__ . '/config/app.php', $config);

        if (!is_dir(__DIR__ . '/storage')) {
            mkdir(__DIR__ . '/storage', 0755, true);
        }
        file_put_contents($installLockPath, 'installed');

        $pdo->exec("UPDATE settings SET title = '" . addslashes($siteTitle) . "', url = '" . addslashes($siteUrl) . "' WHERE id = 1");

        $success = true;
        $successMessage = 'Installation completed successfully!';
        echo '<script>window.installationComplete = true; window.siteUrl = "' . addslashes($siteUrl) . '";</script>';

    } catch (Exception $e) {
        $error = 'Installation failed: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Server List - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --border-radius: 0.75rem;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
        }
        
        .install-header {
            background: var(--primary-gradient);
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            border-right: none;
        }
        
        .input-group .form-control {
            border-radius: 0 0.5rem 0.5rem 0;
        }
        
        .btn {
            border-radius: 0.5rem;
            font-weight: 600;
            padding: 0.75rem 2rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #0d6efd, #0056b3);
            border: none;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
        }
        
        .progress-bar {
            background: var(--primary-gradient);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="container flex-grow-1 d-flex align-items-center py-4">
        <div class="row justify-content-center w-100">
            <div class="col-md-10 col-lg-8">
                <!-- Header Section -->
                <div class="install-header text-white p-4 rounded-top text-center">
                    <div class="mb-3">
                        <i class="bi bi-controller" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-2">Minecraft Server List</h2>
                    <p class="mb-0 opacity-90">Installation Wizard</p>
                </div>

                <div class="card rounded-top-0 border-top-0">
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="installForm">
                            <!-- Database Configuration Section -->
                            <div class="mb-4">
                                <h5 class="fw-semibold text-dark mb-3">
                                    <i class="bi bi-database text-primary me-2"></i>Database Configuration
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="db_host" class="form-label fw-semibold">Database Host</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-server text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="db_host" 
                                               name="db_host" 
                                               value="localhost" 
                                               placeholder="localhost"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_name" class="form-label fw-semibold">Database Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-collection text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="db_name" 
                                               name="db_name" 
                                               placeholder="minecraft_servers"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="db_user" class="form-label fw-semibold">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-person text-muted"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="db_user" 
                                                   name="db_user" 
                                                   placeholder="Database username"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="db_pass" class="form-label fw-semibold">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-lock text-muted"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="db_pass" 
                                                   name="db_pass"
                                                   placeholder="Database password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Site Configuration Section -->
                            <div class="mb-4">
                                <h5 class="fw-semibold text-dark mb-3">
                                    <i class="bi bi-globe text-primary me-2"></i>Site Configuration
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="site_url" class="form-label fw-semibold">Site URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-link text-muted"></i>
                                        </span>
                                        <input type="url" 
                                               class="form-control" 
                                               id="site_url" 
                                               name="site_url" 
                                               value="<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '\\/') . '/' ?>" 
                                               required>
                                    </div>
                                    <small class="text-muted">Include the trailing slash (/)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_title" class="form-label fw-semibold">Site Title</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-card-text text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="site_title" 
                                               name="site_title" 
                                               value="Minecraft Server List" 
                                               placeholder="Your Server List Name"
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Installation Progress (hidden initially) -->
                            <div id="installProgress" class="mb-4" style="display: none;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-gear-fill text-primary me-2"></i>
                                    <span class="fw-semibold">Installing...</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 100%"></div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 fw-semibold" id="installBtn">
                                <i class="bi bi-rocket-takeoff me-2"></i>Start Installation
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-footer bg-light text-center border-0 rounded-bottom">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Make sure your database exists and PHP has write permissions to the config directory
                        </small>
                    </div>
                </div>
                
                <!-- Requirements Check -->
                <div class="mt-4 p-3 bg-white rounded shadow-sm">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-check-square text-success me-2"></i>System Requirements
                    </h6>
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <span>PHP <?= PHP_VERSION ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-<?= extension_loaded('pdo_mysql') ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                                <span>MySQL PDO <?= extension_loaded('pdo_mysql') ? '✓' : '✗' ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-<?= is_writable(__DIR__ . '/config') ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                                <span>Config writable <?= is_writable(__DIR__ . '/config') ? '✓' : '✗' ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-<?= function_exists('curl_init') ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                                <span>cURL <?= function_exists('curl_init') ? '✓' : '✗' ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-<?= extension_loaded('sockets') ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                                <span>Sockets <?= extension_loaded('sockets') ? '✓' : '✗' ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-<?= extension_loaded('gd') ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                                <span>GD <?= extension_loaded('gd') ? '✓' : '✗' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-5">
                    <!-- Success Animation -->
                    <div class="mb-4">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; animation: scaleIn 0.5s ease-out;">
                            <i class="bi bi-check-lg text-white" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-success fw-bold mb-2">Installation Complete!</h3>
                    <p class="text-muted mb-4">Your Minecraft Server List has been successfully installed and is ready to use.</p>
                    
                    <!-- Important Information -->
                    <div class="row justify-content-center g-4 mb-4">
                        <div class="col-md-10">
                            <div class="p-4">
                                
                                <div class="row g-3 text-start">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <div>
                                                <h6 class="fw-semibold mb-1">Installer Locked</h6>
                                                <p class="mb-0 text-muted small">
                                                    The installer is now automatically disabled after setup using
                                                    <code class="bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">storage/installed.lock</code>.
                                                    To run the installer again, remove the lock file.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <div>
                                                <h6 class="fw-semibold mb-1">Administrator Access</h6>
                                                <p class="mb-1 text-muted small">Default admin credentials:</p>
                                                <div class="d-flex gap-2">
                                                    <code class="bg-success bg-opacity-10 text-success px-2 py-1 rounded">admin</code>
                                                    <span class="text-muted">/</span>
                                                    <code class="bg-success bg-opacity-10 text-success px-2 py-1 rounded">admin</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        You can now close this window and access your new Minecraft Server List website.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.8);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('installForm').addEventListener('submit', function() {
            document.getElementById('installBtn').innerHTML = '<i class="bi bi-gear-fill me-2"></i>Installing...';
            document.getElementById('installBtn').disabled = true;
            document.getElementById('installProgress').style.display = 'block';
        });
        
        // Check if installation was completed
        if (window.installationComplete) {
            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        }
    </script>
</body>
</html>
