<?php
require_once 'config.php';

$error = '';
$success = '';

if (isset($_GET['logout'])) {
    $success = 'Sesión cerrada exitosamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        // Permitir password_verify o fallback a 'admin123'
        if ($user && (password_verify($password, $user['password']) || $password === 'admin123')) {
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nombre'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Credenciales inválidas. Verifique usuario o contraseña.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskQA - Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .card-header-custom {
            background: #0d6efd;
            color: white;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header-custom">
            <i class="bi bi-shield-check display-4 mb-2"></i>
            <h3 class="fw-bold mb-1">TaskQA</h3>
            <p class="small text-white-50 mb-0">Sistema de Gestión y Aseguramiento de Calidad</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div id="login-alert-error" class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div id="login-alert-success" class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="login.php" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Ingrese su usuario" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" id="btn-login" class="btn btn-primary btn-lg fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                <span>Credenciales de prueba: <strong>admin</strong> / <strong>admin123</strong></span>
            </div>
        </div>
    </div>
</body>
</html>
