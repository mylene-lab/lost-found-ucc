<?php
class BranchController {
    public function index() {
        $db = getDB();
        $branches = $db->query("
            SELECT b.*,
                (SELECT COUNT(*) FROM users WHERE branch_id=b.id) AS user_count,
                (SELECT COUNT(*) FROM found_items WHERE branch_id=b.id) AS found_count
            FROM branches b ORDER BY b.name
        ")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/branches/index.php';
    }

    public function create() {
        $branch = null;
        require_once __DIR__.'/../views/branches/form.php';
    }

    public function store() {
        $db   = getDB();
        $name = $_POST['name'] ?? '';
        $addr = $_POST['address'] ?? '';
        $con  = $_POST['contact'] ?? '';
        $em   = $_POST['email'] ?? '';
        $st   = $_POST['status'] ?? 'active';
        $stmt = $db->prepare("INSERT INTO branches (name, address, contact, email, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss', $name, $addr, $con, $em, $st);
        $stmt->execute();
        logActivity('BRANCH_CREATED', "Created branch: $name");
        flash('success', 'Campus/Branch created!');
        redirect(BASE_URL.'/index.php?page=branches');
    }

    public function edit() {
        $id     = (int)($_GET['id'] ?? 0);
        $db     = getDB();
        $branch = $db->query("SELECT * FROM branches WHERE id=$id")->fetch_assoc();
        if (!$branch) { flash('danger','Not found.'); redirect(BASE_URL.'/index.php?page=branches'); }
        require_once __DIR__.'/../views/branches/form.php';
    }

    public function update() {
        $id   = (int)($_POST['id'] ?? 0);
        $db   = getDB();
        $name = $_POST['name'] ?? '';
        $addr = $_POST['address'] ?? '';
        $con  = $_POST['contact'] ?? '';
        $em   = $_POST['email'] ?? '';
        $st   = $_POST['status'] ?? 'active';
        $stmt = $db->prepare("UPDATE branches SET name=?, address=?, contact=?, email=?, status=? WHERE id=?");
        $stmt->bind_param('sssssi', $name, $addr, $con, $em, $st, $id);
        $stmt->execute();
        logActivity('BRANCH_UPDATED', "Updated branch ID $id");
        flash('success', 'Campus/Branch updated!');
        redirect(BASE_URL.'/index.php?page=branches');
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        getDB()->query("UPDATE branches SET status='inactive' WHERE id=$id");
        logActivity('BRANCH_DEACTIVATED', "Deactivated branch ID $id");
        flash('success', 'Branch deactivated.');
        redirect(BASE_URL.'/index.php?page=branches');
    }
}
