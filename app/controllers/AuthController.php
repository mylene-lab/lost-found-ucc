<?php
require_once __DIR__ . '/../../config/database.php';

class AuthController {
    public function login() {
        if (isLoggedIn()) {
            if ($_SESSION['role'] === 'guest') redirect(BASE_URL . '/index.php?page=dashboard');
            redirect(BASE_URL . '/index.php?page=dashboard');
        }

        // Handle guest self-registration
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
            $name     = trim($_POST['reg_name'] ?? '');
            $email    = trim($_POST['reg_email'] ?? '');
            $password = $_POST['reg_password'] ?? '';

            if (empty($name) || empty($email) || strlen($password) < 6) {
                flash('danger', 'All fields are required and password must be at least 6 characters.');
                redirect(BASE_URL . '/index.php?page=login&register=1');
            }

            $db = getDB();
            $check = $db->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param('s', $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                flash('danger', 'Email already registered. Please log in.');
                redirect(BASE_URL . '/index.php?page=login');
            }

            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $role = 'guest';
            $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, 'guest', 'active')");
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();

            flash('success', 'Account created! Please log in.');
            redirect(BASE_URL . '/index.php?page=login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                flash('danger', 'Email and password are required.');
            } else {
                $db   = getDB();
                $stmt = $db->prepare("
                    SELECT u.*, b.name AS branch_name
                    FROM users u
                    LEFT JOIN branches b ON b.id = u.branch_id
                    WHERE u.email = ? AND u.status = 'active'
                    LIMIT 1
                ");
                if (!$stmt) {
                    flash('danger', 'Database error: ' . $db->error . '. Make sure the database is imported correctly.');
                    require_once __DIR__ . '/../views/auth/login.php';
                    return;
                }
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']     = $user['id'];
                    $_SESSION['user_name']   = $user['full_name'];
                    $_SESSION['user_email']  = $user['email'];
                    $_SESSION['role']        = $user['role'];
                    $_SESSION['branch_id']   = $user['branch_id'];
                    $_SESSION['branch_name'] = $user['branch_name'] ?? 'All Branches';

                    $db->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
                    logActivity('LOGIN', 'User logged in');

                    flash('success', 'Welcome back, ' . $user['full_name'] . '!');

                    // Guests go to their own portal
                    if ($user['role'] === 'guest') {
                        redirect(BASE_URL . '/index.php?page=dashboard');
                    }
                    redirect(BASE_URL . '/index.php?page=dashboard');
                } else {
                    flash('danger', 'Invalid email or password.');
                }
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        logActivity('LOGOUT', 'User logged out');
        session_destroy();
        redirect(BASE_URL . '/index.php?page=login');
    }
}
