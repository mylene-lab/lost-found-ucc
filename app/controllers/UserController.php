<?php
class UserController {
    private function getBranches() {
        $db = getDB();
        if ($_SESSION['role'] === 'superadmin')
            return $db->query("SELECT * FROM branches WHERE status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        $bid = (int)$_SESSION['branch_id'];
        return $db->query("SELECT * FROM branches WHERE id=$bid AND status='active'")->fetch_all(MYSQLI_ASSOC);
    }

    public function index() {
        $db = getDB();
        $branchScope = getBranchScope();
        $bf = $branchScope ? "AND u.branch_id=$branchScope" : '';
        $users = $db->query("SELECT u.*, b.name AS branch_name FROM users u LEFT JOIN branches b ON b.id=u.branch_id WHERE 1 $bf ORDER BY u.full_name")->fetch_all(MYSQLI_ASSOC);
        $branches = $this->getBranches();
        require_once __DIR__.'/../views/users/index.php';
    }

    public function create() {
        $user = null;
        $branches = $this->getBranches();
        require_once __DIR__.'/../views/users/form.php';
    }

    public function store() {
        $db = getDB();
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 6) { flash('danger','Password must be at least 6 characters.'); redirect(BASE_URL.'/index.php?page=users&action=create'); }
        $branchId = ($_POST['branch_id'] !== '') ? (int)$_POST['branch_id'] : null;
        $role     = $_POST['role'];
        if ($_SESSION['role'] === 'branch_manager' && $role === 'superadmin') { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=users'); }
        $hash   = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $name   = $_POST['full_name'];
        $email  = $_POST['email'];
        $status = $_POST['status'];
        $stmt   = $db->prepare("INSERT INTO users (branch_id, full_name, email, password, role, status) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('isssss', $branchId, $name, $email, $hash, $role, $status);
        $stmt->execute();
        logActivity('USER_CREATED', "Created user: $email");
        flash('success', 'User created!');
        redirect(BASE_URL.'/index.php?page=users');
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();
        $user = $db->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
        if (!$user) { flash('danger','Not found.'); redirect(BASE_URL.'/index.php?page=users'); }
        if ($_SESSION['role'] === 'branch_manager' && (int)$user['branch_id'] !== (int)$_SESSION['branch_id']) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=users'); }
        $branches = $this->getBranches();
        require_once __DIR__.'/../views/users/form.php';
    }

    public function update() {
        $id   = (int)($_POST['id'] ?? 0);
        $db   = getDB();
        $user = $db->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
        if (!$user) { flash('danger','Not found.'); redirect(BASE_URL.'/index.php?page=users'); }
        $branchId = ($_POST['branch_id'] !== '') ? (int)$_POST['branch_id'] : null;
        $role     = $_POST['role'];
        $name     = $_POST['full_name'];
        $email    = $_POST['email'];
        $status   = $_POST['status'];
        if ($_SESSION['role'] === 'branch_manager' && $role === 'superadmin') { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=users'); }
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) { flash('danger','Password too short.'); redirect(BASE_URL.'/index.php?page=users&action=edit&id='.$id); }
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
        }
        $stmt = $db->prepare("UPDATE users SET branch_id=?, full_name=?, email=?, role=?, status=? WHERE id=?");
        $stmt->bind_param('issssi', $branchId, $name, $email, $role, $status, $id);
        $stmt->execute();
        logActivity('USER_UPDATED', "Updated user ID $id");
        flash('success', 'User updated!');
        redirect(BASE_URL.'/index.php?page=users');
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id === (int)$_SESSION['user_id']) { flash('danger','Cannot delete yourself.'); redirect(BASE_URL.'/index.php?page=users'); }
        $db = getDB();
        $db->query("UPDATE users SET status='inactive' WHERE id=$id");
        logActivity('USER_DEACTIVATED', "Deactivated user ID $id");
        flash('success', 'User deactivated.');
        redirect(BASE_URL.'/index.php?page=users');
    }
}
