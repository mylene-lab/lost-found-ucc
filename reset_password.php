<?php
/**
 * Password Reset Utility
 * Run this in browser: http://localhost/lost-found/reset_password.php
 * DELETE this file after use!
 */

// Basic protection - only allow localhost
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
    die('Access denied. This utility is only accessible from localhost.');
}

require_once __DIR__ . '/config/database.php';

$message = '';
$users = [];

// Load users
$db = getDB();
$result = $db->query("SELECT id, full_name, email, role FROM users ORDER BY role, full_name");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (strlen($newPassword) < 6) {
        $message = ['type' => 'danger', 'text' => 'Password must be at least 6 characters.'];
    } elseif ($newPassword !== $confirmPassword) {
        $message = ['type' => 'danger', 'text' => 'Passwords do not match.'];
    } else {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, $userId);
        if ($stmt->execute()) {
            $user = $db->query("SELECT full_name, email FROM users WHERE id = $userId")->fetch_assoc();
            $message = ['type' => 'success', 'text' => "Password updated for {$user['full_name']} ({$user['email']})"];
        } else {
            $message = ['type' => 'danger', 'text' => 'Failed to update password.'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Utility</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{font-family:sans-serif;background:#f1f5f9;} .card{border-radius:1rem;border:1px solid #e2e8f0;}</style>
</head>
<body class="p-4">
<div class="container" style="max-width:520px">
    <div class="card shadow-sm p-4">
        <div class="alert alert-warning py-2 small mb-3">
            ⚠️ <strong>Security Warning:</strong> Delete <code>reset_password.php</code> after use!
        </div>
        <h5 class="fw-bold mb-3">🔐 Password Reset Utility</h5>

        <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-500">Select User</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Choose a user --</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>) — <?= ucfirst(str_replace('_', ' ', $u['role'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Min 6 characters">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>

        <hr>
        <h6 class="fw-bold">Quick Reset — Set All Default Passwords</h6>
        <p class="small text-muted">Click to instantly reset all users to their default passwords:</p>
        <form method="POST" action="?bulk=1">
            <?php if (isset($_GET['bulk'])): ?>
            <?php
            $defaults = [
                ['superadmin', 'Admin@1234'],
                ['branch_manager', 'Manager@1234'],
                ['staff', 'Staff@1234'],
            ];
            $updated = 0;
            foreach ($defaults as [$role, $pass]) {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
                $db->query("UPDATE users SET password = '$hash' WHERE role = '$role'");
                $updated += $db->affected_rows;
            }
            echo "<div class='alert alert-success'>✅ Reset $updated user(s) to default passwords.</div>";
            ?>
            <?php else: ?>
            <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                Reset All to Defaults (Admin@1234 / Manager@1234 / Staff@1234)
            </button>
            <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>
