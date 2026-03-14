<?php
class GuestController {

    private function getCategories() {
        return getDB()->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }

    private function getBranches() {
        return getDB()->query("SELECT * FROM branches WHERE status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }

    public function index() {
        $db = getDB();
        $userId = (int)$_SESSION['user_id'];

        $myReports = $db->query("
            SELECT r.*, b.name AS branch_name, c.name AS category_name,
                (SELECT COUNT(*) FROM item_matches m WHERE m.lost_report_id = r.id AND m.status != 'rejected') AS match_count
            FROM lost_reports r
            JOIN branches b ON b.id = r.branch_id
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.logged_by = $userId
            ORDER BY r.created_at DESC
        ")->fetch_all(MYSQLI_ASSOC);

        $myFoundItems = $db->query("
            SELECT f.*, b.name AS branch_name, c.name AS category_name
            FROM found_items f
            JOIN branches b ON b.id = f.branch_id
            LEFT JOIN categories c ON c.id = f.category_id
            WHERE f.logged_by = $userId
            ORDER BY f.created_at DESC
        ")->fetch_all(MYSQLI_ASSOC);

        $categories = $this->getCategories();
        $branches   = $this->getBranches();
        $activeTab  = $_GET['tab'] ?? 'lost';

        require_once __DIR__ . '/../views/guest/portal.php';
    }

    public function store() {
        $db = getDB();
        $branchId = (int)($_POST['branch_id'] ?? 0);

        if (!$branchId) {
            flash('danger', 'Please select a branch.');
            redirect(BASE_URL . '/index.php?page=guest-portal');
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $db->query("ALTER TABLE lost_reports ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL");
            $result = uploadPhoto($_FILES['photo']);
            if (is_array($result) && isset($result['error'])) {
                flash('danger', $result['error']);
                redirect(BASE_URL . '/index.php?page=guest-portal');
            }
            $photo = $result;
        }

        $catId = $_POST['category_id'] ?: null;
        $stmt  = $db->prepare("
            INSERT INTO lost_reports
                (branch_id, logged_by, category_id, reporter_name, reporter_contact, reporter_email,
                 item_name, description, color, brand, lost_date, lost_location, notes, photo)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param('iiisssssssssss',
            $branchId,
            $_SESSION['user_id'],
            $catId,
            $_POST['reporter_name'],
            $_POST['reporter_contact'],
            $_POST['reporter_email'],
            $_POST['item_name'],
            $_POST['description'],
            $_POST['color'],
            $_POST['brand'],
            $_POST['lost_date'],
            $_POST['lost_location'],
            $_POST['notes'],
            $photo
        );
        $stmt->execute();
        $newId = $db->insert_id;

        logActivity('GUEST_LOST_REPORT', "Guest submitted lost report ID $newId: " . $_POST['item_name']);

        // Auto-match: check if any existing found item matches this new lost report
        $systemUser = getDB()->query("SELECT id FROM users WHERE role='superadmin' LIMIT 1")->fetch_assoc();
        if ($systemUser) autoMatch($systemUser['id'], null, $newId);

        flash('success', 'Your lost item report has been submitted! Our staff will review it shortly.');
        redirect(BASE_URL . '/index.php?page=guest-portal');
    }

    public function storeFound() {
        $db = getDB();
        $branchId = (int)($_POST['branch_id'] ?? 0);

        if (!$branchId) {
            flash('danger', 'Please select a branch.');
            redirect(BASE_URL . '/index.php?page=guest-portal&tab=found');
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $result = uploadPhoto($_FILES['photo']);
            if (is_array($result) && isset($result['error'])) {
                flash('danger', $result['error']);
                redirect(BASE_URL . '/index.php?page=guest-portal&tab=found');
            }
            $photo = $result;
        }

        $catId     = $_POST['category_id'] ?: null;
        $foundDate = $_POST['found_date'] ?: date('Y-m-d');

        $stmt = $db->prepare("
            INSERT INTO found_items
                (branch_id, logged_by, category_id, item_name, description,
                 color, brand, found_date, found_location, photo, status, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,'unclaimed',?)
        ");
        $stmt->bind_param('iiissssssss',
            $branchId,
            $_SESSION['user_id'],
            $catId,
            $_POST['item_name'],
            $_POST['description'],
            $_POST['color'],
            $_POST['brand'],
            $foundDate,
            $_POST['found_location'],
            $photo,
            $_POST['notes']
        );
        $stmt->execute();
        $newId = $db->insert_id;

        logActivity('GUEST_FOUND_REPORT', "Guest reported found item ID $newId: " . $_POST['item_name']);

        // Auto-match: check if any existing lost report matches this new found item
        $systemUser = getDB()->query("SELECT id FROM users WHERE role='superadmin' LIMIT 1")->fetch_assoc();
        if ($systemUser) autoMatch($systemUser['id'], $newId, null);

        flash('success', 'Found item reported! Staff will review and try to find the owner. Thank you!');
        redirect(BASE_URL . '/index.php?page=guest-portal&tab=found');
    }

    public function view() {
        $id     = (int)($_GET['id'] ?? 0);
        $db     = getDB();
        $userId = (int)$_SESSION['user_id'];

        $report = $db->query("
            SELECT r.*, b.name AS branch_name, c.name AS category_name
            FROM lost_reports r
            JOIN branches b ON b.id = r.branch_id
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.id = $id AND r.logged_by = $userId
        ")->fetch_assoc();

        if (!$report) {
            flash('danger', 'Report not found.');
            redirect(BASE_URL . '/index.php?page=guest-portal');
        }

        $matches = $db->query("
            SELECT m.*, f.item_name AS found_name, f.photo, f.found_date, f.found_location,
                   f.color AS found_color, f.brand AS found_brand, b.name AS branch_name
            FROM item_matches m
            JOIN found_items f ON f.id = m.found_item_id
            JOIN branches b ON b.id = f.branch_id
            WHERE m.lost_report_id = $id AND m.status != 'rejected'
        ")->fetch_all(MYSQLI_ASSOC);

        require_once __DIR__ . '/../views/guest/view.php';
    }

    public function viewFound() {
        $id     = (int)($_GET['id'] ?? 0);
        $db     = getDB();
        $userId = (int)$_SESSION['user_id'];

        $item = $db->query("
            SELECT f.*, b.name AS branch_name, c.name AS category_name
            FROM found_items f
            JOIN branches b ON b.id = f.branch_id
            LEFT JOIN categories c ON c.id = f.category_id
            WHERE f.id = $id AND f.logged_by = $userId
        ")->fetch_assoc();

        if (!$item) {
            flash('danger', 'Found item record not found.');
            redirect(BASE_URL . '/index.php?page=guest-portal&tab=found');
        }

        $matches = $db->query("
            SELECT m.*, r.item_name AS lost_name, r.description AS lost_desc,
                   r.color AS lost_color, r.brand AS lost_brand,
                   r.lost_date, r.lost_location, r.reporter_name, r.reporter_contact,
                   b.name AS branch_name
            FROM item_matches m
            JOIN lost_reports r ON r.id = m.lost_report_id
            JOIN branches b ON b.id = r.branch_id
            WHERE m.found_item_id = $id AND m.status != 'rejected'
        ")->fetch_all(MYSQLI_ASSOC);

        require_once __DIR__ . '/../views/guest/view_found.php';
    }
}
