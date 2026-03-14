<?php
class DashboardController {
    public function index() {
        $role = $_SESSION['role'] ?? 'staff';

        if ($role === 'guest') {
            $this->guestDashboard();
        } elseif ($role === 'staff') {
            $this->staffDashboard();
        } else {
            $this->adminDashboard(); // superadmin + branch_manager
        }
    }

    // ─── GUEST DASHBOARD ─────────────────────────────────────────────────────
    private function guestDashboard() {
        $db     = getDB();
        $userId = (int)$_SESSION['user_id'];

        // Guest's own reports
        $myReports = $db->query("
            SELECT r.*, b.name AS branch_name, c.name AS category_name,
                (SELECT COUNT(*) FROM item_matches m WHERE m.lost_report_id = r.id AND m.status != 'rejected') AS match_count
            FROM lost_reports r
            JOIN branches b ON b.id = r.branch_id
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.logged_by = $userId
            ORDER BY r.created_at DESC
        ")->fetch_all(MYSQLI_ASSOC);

        // Guest stats
        $totalReports  = count($myReports);
        $openReports   = count(array_filter($myReports, fn($r) => $r['status'] === 'open'));
        $matchedReports= count(array_filter($myReports, fn($r) => $r['status'] === 'matched'));
        $closedReports = count(array_filter($myReports, fn($r) => $r['status'] === 'closed'));

        // Recent matches for this guest
        $myMatches = $db->query("
            SELECT m.*, f.item_name AS found_name, f.photo, f.found_date, f.found_location,
                   f.color AS found_color, f.brand AS found_brand,
                   b.name AS branch_name, r.item_name AS report_name
            FROM item_matches m
            JOIN found_items f ON f.id = m.found_item_id
            JOIN lost_reports r ON r.id = m.lost_report_id
            JOIN branches b ON b.id = f.branch_id
            WHERE r.logged_by = $userId AND m.status != 'rejected'
            ORDER BY m.match_date DESC
            LIMIT 5
        ")->fetch_all(MYSQLI_ASSOC);

        // Recent unclaimed found items (public info to help guest identify their item)
        $recentFound = $db->query("
            SELECT f.*, b.name AS branch_name, c.name AS category_name
            FROM found_items f
            JOIN branches b ON b.id = f.branch_id
            LEFT JOIN categories c ON c.id = f.category_id
            WHERE f.status = 'unclaimed'
            ORDER BY f.created_at DESC
            LIMIT 6
        ")->fetch_all(MYSQLI_ASSOC);

        require_once __DIR__ . '/../views/dashboard/guest.php';
    }

    // ─── STAFF DASHBOARD ─────────────────────────────────────────────────────
    private function staffDashboard() {
        $db          = getDB();
        $branchScope = getBranchScope();
        $bf          = $branchScope ? "AND f.branch_id = $branchScope" : '';
        $bfr         = $branchScope ? "AND branch_id = $branchScope"   : '';
        $userId      = (int)$_SESSION['user_id'];

        // Stats scoped to staff's branch
        $stats = [
            'total_found'     => $db->query("SELECT COUNT(*) c FROM found_items f WHERE 1 $bf")->fetch_assoc()['c'],
            'unclaimed'       => $db->query("SELECT COUNT(*) c FROM found_items f WHERE f.status='unclaimed' $bf")->fetch_assoc()['c'],
            'claimed_today'   => $db->query("SELECT COUNT(*) c FROM found_items f WHERE f.status='claimed' AND DATE(f.updated_at)=CURDATE() $bf")->fetch_assoc()['c'],
            'open_reports'    => $db->query("SELECT COUNT(*) c FROM lost_reports WHERE status='open' $bfr")->fetch_assoc()['c'],
            'pending_matches' => $db->query("SELECT COUNT(*) c FROM item_matches m JOIN found_items f ON f.id=m.found_item_id WHERE m.status='pending' $bf")->fetch_assoc()['c'],
            'logged_by_me'    => $db->query("SELECT COUNT(*) c FROM found_items WHERE logged_by=$userId")->fetch_assoc()['c'],
        ];

        // My recent activity (items I logged)
        $myItems = $db->query("
            SELECT f.*, b.name AS branch_name, c.name AS category_name
            FROM found_items f
            JOIN branches b ON b.id = f.branch_id
            LEFT JOIN categories c ON c.id = f.category_id
            WHERE f.logged_by = $userId
            ORDER BY f.created_at DESC LIMIT 5
        ")->fetch_all(MYSQLI_ASSOC);

        // Pending matches needing action
        $pendingMatches = $db->query("
            SELECT m.*, f.item_name AS found_name, f.photo,
                   r.reporter_name, r.item_name AS report_name, r.reporter_contact,
                   b.name AS branch_name
            FROM item_matches m
            JOIN found_items f ON f.id = m.found_item_id
            JOIN lost_reports r ON r.id = m.lost_report_id
            JOIN branches b ON b.id = f.branch_id
            WHERE m.status = 'pending' $bf
            ORDER BY m.match_date DESC LIMIT 5
        ")->fetch_all(MYSQLI_ASSOC);

        // Recent lost reports (open)
        $recentReports = $db->query("
            SELECT r.*, b.name AS branch_name, c.name AS category_name
            FROM lost_reports r
            JOIN branches b ON b.id = r.branch_id
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE r.status = 'open' $bfr
            ORDER BY r.created_at DESC LIMIT 5
        ")->fetch_all(MYSQLI_ASSOC);

        // Monthly trend for chart (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));
            $found   = $db->query("SELECT COUNT(*) c FROM found_items f WHERE DATE_FORMAT(f.created_at,'%Y-%m')='$month' $bf")->fetch_assoc()['c'];
            $claimed = $db->query("SELECT COUNT(*) c FROM found_items f WHERE DATE_FORMAT(f.updated_at,'%Y-%m')='$month' AND f.status='claimed' $bf")->fetch_assoc()['c'];
            $monthlyTrend[] = ['label' => $label, 'found' => $found, 'claimed' => $claimed];
        }

        require_once __DIR__ . '/../views/dashboard/staff.php';
    }

    // ─── ADMIN / BRANCH MANAGER DASHBOARD ────────────────────────────────────
    private function adminDashboard() {
        $db          = getDB();
        $branchScope = getBranchScope();
        $branchFilter      = $branchScope ? "AND f.branch_id = $branchScope" : '';
        $branchFilterR     = $branchScope ? "AND branch_id = $branchScope"   : '';

        // Summary Stats
        $stats = [];
        $stats['total_found']     = $db->query("SELECT COUNT(*) c FROM found_items f WHERE 1 $branchFilter")->fetch_assoc()['c'];
        $stats['unclaimed']       = $db->query("SELECT COUNT(*) c FROM found_items f WHERE f.status='unclaimed' $branchFilter")->fetch_assoc()['c'];
        $stats['claimed']         = $db->query("SELECT COUNT(*) c FROM found_items f WHERE f.status='claimed' $branchFilter")->fetch_assoc()['c'];
        $stats['open_reports']    = $db->query("SELECT COUNT(*) c FROM lost_reports WHERE status='open' $branchFilterR")->fetch_assoc()['c'];
        $stats['total_reports']   = $db->query("SELECT COUNT(*) c FROM lost_reports WHERE 1 $branchFilterR")->fetch_assoc()['c'];
        $stats['pending_matches'] = $db->query("SELECT COUNT(*) c FROM item_matches m JOIN found_items f ON f.id=m.found_item_id WHERE m.status='pending' $branchFilter")->fetch_assoc()['c'];

        // Branch breakdown (superadmin only)
        $branchStats = [];
        if (!$branchScope) {
            $res = $db->query("
                SELECT b.name,
                    SUM(CASE WHEN f.status='unclaimed' THEN 1 ELSE 0 END) AS unclaimed,
                    SUM(CASE WHEN f.status='claimed'   THEN 1 ELSE 0 END) AS claimed,
                    COUNT(f.id) AS total_found
                FROM branches b
                LEFT JOIN found_items f ON f.branch_id = b.id
                GROUP BY b.id, b.name ORDER BY b.name
            ");
            while ($row = $res->fetch_assoc()) $branchStats[] = $row;
        }

        // Monthly trend
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));
            $found   = $db->query("SELECT COUNT(*) c FROM found_items f WHERE DATE_FORMAT(f.created_at,'%Y-%m')='$month' $branchFilter")->fetch_assoc()['c'];
            $reports = $db->query("SELECT COUNT(*) c FROM lost_reports WHERE DATE_FORMAT(created_at,'%Y-%m')='$month' $branchFilterR")->fetch_assoc()['c'];
            $monthlyTrend[] = ['label' => $label, 'found' => $found, 'reports' => $reports];
        }

        // Items by category
        $categoryData = [];
        $catBranchFilter = $branchScope ? "AND f.branch_id=$branchScope" : '';
        $res = $db->query("
            SELECT c.name, COUNT(f.id) as total
            FROM categories c
            LEFT JOIN found_items f ON f.category_id = c.id $catBranchFilter
            GROUP BY c.id, c.name ORDER BY total DESC LIMIT 8
        ");
        while ($row = $res->fetch_assoc()) $categoryData[] = $row;

        // Recent found items
        $recentFound = [];
        $res = $db->query("
            SELECT f.*, b.name AS branch_name, c.name AS category_name, u.full_name AS logged_by_name
            FROM found_items f
            JOIN branches b ON b.id = f.branch_id
            LEFT JOIN categories c ON c.id = f.category_id
            JOIN users u ON u.id = f.logged_by
            WHERE 1 $branchFilter
            ORDER BY f.created_at DESC LIMIT 5
        ");
        while ($row = $res->fetch_assoc()) $recentFound[] = $row;

        // Recent lost reports
        $recentReports = [];
        $res = $db->query("
            SELECT r.*, b.name AS branch_name, c.name AS category_name
            FROM lost_reports r
            JOIN branches b ON b.id = r.branch_id
            LEFT JOIN categories c ON c.id = r.category_id
            WHERE 1 $branchFilterR
            ORDER BY r.created_at DESC LIMIT 5
        ");
        while ($row = $res->fetch_assoc()) $recentReports[] = $row;

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
