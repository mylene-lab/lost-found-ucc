<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh; margin: 0;
            background: linear-gradient(135deg, #14532d 0%, #166534 50%, #16a34a 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #fff; border-radius: 1.25rem; padding: 2.5rem;
            width: 100%; max-width: 420px; box-shadow: 0 25px 60px rgba(0,0,0,.3);
        }
        .login-logo {
            width: 80px; height: 80px; border-radius: 1rem;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;
        }
        .form-control { border-radius: .625rem; padding: .65rem 1rem; border-color: #d1d5db; }
        .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .btn-login { background: #16a34a; border: none; border-radius: .625rem; padding: .75rem; font-weight: 600; width: 100%; transition: background .15s; }
        .btn-login:hover { background: #15803d; }
        .input-group-text { background: #f8fafc; border-color: #d1d5db; border-radius: .625rem 0 0 .625rem; }
        .input-group .form-control { border-radius: 0 .625rem .625rem 0; }
        .demo-creds { background: #f8fafc; border-radius: .75rem; padding: 1rem; font-size: .8rem; }
        .demo-creds .badge { font-size: .72rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo">
            <img src="<?= BASE_URL ?>/public/ucc-logo.png" alt="UCC Logo" style="width:100%;height:100%;object-fit:contain;">
        </div>
        <h4 class="fw-700 mb-1" style="color:#14532d"><?= APP_NAME ?></h4>
        <p class="text-muted small mb-0">Multi-Branch Lost &amp; Found Management</p>
    </div>

    <?php $flash=getFlash(); if($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> py-2 small"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if(isset($_GET['register'])): ?>
    <!-- Guest Registration Form -->
    <div class="mb-3 p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
        <div class="fw-600 mb-1" style="color:#15803d"><i class="fas fa-user-plus me-1"></i>Create Guest Account</div>
        <small class="text-muted">Register to report a lost item and track its status.</small>
    </div>
    <form method="POST" action="?page=login&register=1">
        <input type="hidden" name="register" value="1">
        <div class="mb-3">
            <label class="form-label fw-500 small">Full Name</label>
            <input type="text" name="reg_name" class="form-control" placeholder="Your full name" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-500 small">Email Address</label>
            <input type="email" name="reg_email" class="form-control" placeholder="your@email.com" required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-500 small">Password</label>
            <input type="password" name="reg_password" class="form-control" placeholder="Min 6 characters" required minlength="6">
        </div>
        <button type="submit" class="btn btn-success w-100 fw-600">
            <i class="fas fa-user-plus me-2"></i>Register as Guest
        </button>
        <div class="text-center mt-2">
            <small><a href="?page=login" class="text-muted">Back to Login</a></small>
        </div>
    </form>
    <?php else: ?>
    <form method="POST" action="?page=login">
        <div class="mb-3">
            <label class="form-label fw-500 small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope text-muted small"></i></span>
                <input type="email" name="email" class="form-control" placeholder="your@email.com"
                    value="<?= e($_POST['email']??'') ?>" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-500 small">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock text-muted small"></i></span>
                <input type="password" name="password" id="pwd" class="form-control" placeholder="••••••••" required>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePwd()" style="border-radius:0 .625rem .625rem 0;border-color:#d1d5db">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-login btn-primary text-white">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>
    </form>
    <?php endif; ?>

    <hr class="my-3">
    <div class="demo-creds">
        <div class="fw-600 mb-2 text-muted" style="font-size:.75rem">DEMO ACCOUNTS</div>
        <div class="d-flex flex-column gap-1">
            <div><span class="badge bg-danger me-1">Super Admin</span> superadmin@lostandfound.com / Admin@1234</div>
            <div><span class="badge bg-warning text-dark me-1">Manager</span> main.manager@lostandfound.com / Manager@1234</div>
            <div><span class="badge bg-info me-1">Staff</span> main.staff@lostandfound.com / Staff@1234</div>
            <div><span class="badge bg-success me-1">Guest</span> guest@lostandfound.com / Guest@1234</div>
        </div>
    </div>
    <div class="text-center mt-3">
        <small class="text-muted">Lost something? <a href="?page=login&register=1" class="text-success fw-600">Create a Guest Account</a></small>
    </div>
    <div class="text-center mt-2">
        <small class="text-muted">← <a href="<?= BASE_URL ?>/index.php" class="text-muted">Back to Public Portal</a></small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
    const p=document.getElementById('pwd');
    const i=document.getElementById('eyeIcon');
    if(p.type==='password'){p.type='text';i.className='fas fa-eye-slash';}
    else{p.type='password';i.className='fas fa-eye';}
}
</script>
</body>
</html>
