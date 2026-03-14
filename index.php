<?php
require_once __DIR__ . '/config/config.php';

// ═══════════════════════════════════════════════════════════════════
//  API CALLS  — handle JSON requests before any HTML output
// ═══════════════════════════════════════════════════════════════════
$apiAction = $_GET['api'] ?? '';

if ($apiAction) {
    header('Content-Type: application/json');

    // ── LOGIN ──────────────────────────────────────────────────────
    if ($apiAction === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode(['success'=>false,'message'=>'Email and password are required.']);
            exit;
        }

        $db   = getDB();
        $stmt = $db->prepare("
            SELECT u.*, b.name AS branch_name
            FROM users u
            LEFT JOIN branches b ON b.id = u.branch_id
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            echo json_encode(['success'=>false,'message'=>'No account found with that email.']);
            exit;
        }
        if ($user['status'] !== 'active') {
            echo json_encode(['success'=>false,'message'=>'Your account is inactive. Contact the administrator.']);
            exit;
        }
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success'=>false,'message'=>'Incorrect password.']);
            exit;
        }

        // Start PHP session for this user
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_name']   = $user['full_name'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['role']        = $user['role'];
        $_SESSION['branch_id']   = $user['branch_id'];
        $_SESSION['branch_name'] = $user['branch_name'] ?? 'All Branches';
        $db->query("UPDATE users SET last_login=NOW() WHERE id={$user['id']}");
        logActivity('LOGIN','User logged in');

        echo json_encode([
            'success'  => true,
            'message'  => 'Login successful!',
            'role'     => $user['role'],
            'redirect' => BASE_URL . '/index.php?page=dashboard',
            'user'     => [
                'id'       => $user['id'],
                'fullName' => $user['full_name'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ]
        ]);
        exit;
    }

    // ── SIGNUP ─────────────────────────────────────────────────────
    if ($apiAction === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $fullName = trim($body['fullName'] ?? '');
        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';

        if (!$fullName || !$email || !$password) {
            echo json_encode(['success'=>false,'message'=>'All fields are required.']);
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success'=>false,'message'=>'Please enter a valid email address.']);
            exit;
        }

        $db    = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success'=>false,'message'=>'An account with that email already exists.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>10]);
        $stmt = $db->prepare("INSERT INTO users (full_name,email,password,role,status,created_at) VALUES (?,?,?,'guest','active',NOW())");
        $stmt->bind_param('sss', $fullName, $email, $hash);
        if (!$stmt->execute()) {
            echo json_encode(['success'=>false,'message'=>'Registration failed. Please try again.']);
            exit;
        }

        $newId = $db->insert_id;
        // Auto-login after signup
        $_SESSION['user_id']     = $newId;
        $_SESSION['user_name']   = $fullName;
        $_SESSION['user_email']  = $email;
        $_SESSION['role']        = 'guest';
        $_SESSION['branch_id']   = null;
        $_SESSION['branch_name'] = 'All Branches';

        echo json_encode([
            'success'  => true,
            'message'  => 'Account created successfully!',
            'role'     => 'guest',
            'redirect' => BASE_URL . '/index.php?page=dashboard',
            'user'     => ['id'=>$newId,'fullName'=>$fullName,'email'=>$email,'role'=>'guest'],
        ]);
        exit;
    }

    // ── PUBLIC: get found items for React browse ───────────────────
    if ($apiAction === 'found-items') {
        $db = getDB();
        $q  = isset($_GET['q']) && $_GET['q'] !== ''
              ? '%'.$db->real_escape_string($_GET['q']).'%'
              : '%';
        $rows = $db->query("
            SELECT f.id, f.item_name, f.description, f.color, f.brand,
                   f.found_date, f.found_location, f.photo, f.status,
                   c.name AS category, b.name AS branch
            FROM found_items f
            LEFT JOIN categories c ON c.id = f.category_id
            LEFT JOIN branches   b ON b.id = f.branch_id
            WHERE (f.item_name LIKE '$q' OR f.description LIKE '$q' OR COALESCE(f.brand,'') LIKE '$q')
            ORDER BY f.created_at DESC LIMIT 60
        ");
        $items = $rows ? $rows->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['success'=>true,'items'=>$items]);
        exit;
    }

    // ── SUBMIT LOST REPORT (logged-in guest) ───────────────────────
    if ($apiAction === 'report-lost' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isLoggedIn()) {
            echo json_encode(['success'=>false,'message'=>'Please log in to submit a report.']);
            exit;
        }
        // multipart/form-data (with optional photo upload)
        $db      = getDB();
        $branch  = $db->query("SELECT id FROM branches WHERE status='active' ORDER BY id LIMIT 1")->fetch_assoc();
        if (!$branch) {
            echo json_encode(['success'=>false,'message'=>'No active branch found.']);
            exit;
        }
        $bId      = $branch['id'];
        $uId      = (int)$_SESSION['user_id'];
        $rName    = $db->real_escape_string($_SESSION['user_name'] ?? '');
        $rContact = $db->real_escape_string($_POST['contact']     ?? '');
        $iName    = $db->real_escape_string($_POST['itemName']    ?? '');
        $desc     = $db->real_escape_string($_POST['description'] ?? '');
        $loc      = $db->real_escape_string($_POST['location']    ?? '');
        $color    = $db->real_escape_string($_POST['color']       ?? '');
        $brand    = $db->real_escape_string($_POST['brand']       ?? '');
        $date     = date('Y-m-d');

        if (!$iName || !$rContact) {
            echo json_encode(['success'=>false,'message'=>'Item name and contact info are required.']);
            exit;
        }

        // Handle photo upload
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            // Add photo column to lost_reports if not exists (migration safety)
            $db->query("ALTER TABLE lost_reports ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL");
            $result = uploadPhoto($_FILES['photo']);
            if (is_array($result) && isset($result['error'])) {
                echo json_encode(['success'=>false,'message'=>$result['error']]);
                exit;
            }
            $photo = $db->real_escape_string($result);
        }

        $photoSql = $photo ? "'$photo'" : 'NULL';
        $db->query("INSERT INTO lost_reports
            (branch_id,logged_by,reporter_name,reporter_contact,item_name,description,color,brand,lost_location,lost_date,photo,status)
            VALUES ($bId,$uId,'$rName','$rContact','$iName','$desc','$color','$brand','$loc','$date',$photoSql,'open')");
        echo json_encode(['success'=>true,'message'=>'Lost report submitted successfully!']);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Unknown API action.']);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  PHP BACKEND  — for logged-in sessions
// ═══════════════════════════════════════════════════════════════════
$page   = $_GET['page']   ?? '';
$action = $_GET['action'] ?? 'index';

if (isLoggedIn() && $page !== '') {

    if ($page === 'logout') {
        logActivity('LOGOUT','User logged out');
        session_destroy();
        redirect(BASE_URL . '/index.php');
    }

    // Already logged in → skip login page
    if ($page === 'login' || $page === 'guest-login') {
        redirect(BASE_URL . '/index.php?page=dashboard');
    }

    // Guest: only their own portal
    if ($_SESSION['role'] === 'guest') {
        require_once __DIR__ . '/app/controllers/GuestController.php';
        $ctrl = new GuestController();
        match($action) {
            'store'       => $ctrl->store(),
            'store-found' => $ctrl->storeFound(),
            'view'        => $ctrl->view(),
            'view-found'  => $ctrl->viewFound(),
            default       => $ctrl->index(),
        };
        exit;
    }

    // Staff / Admin routes
    switch ($page) {
        case 'dashboard':
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            (new DashboardController())->index();
            break;

        case 'found-items':
            require_once __DIR__ . '/app/controllers/FoundItemController.php';
            $ctrl = new FoundItemController();
            match($action) {
                'create' => $ctrl->create(),
                'store'  => $ctrl->store(),
                'edit'   => $ctrl->edit(),
                'update' => $ctrl->update(),
                'delete' => $ctrl->delete(),
                'view'   => $ctrl->view(),
                default  => $ctrl->index(),
            };
            break;

        case 'lost-reports':
            require_once __DIR__ . '/app/controllers/LostReportController.php';
            $ctrl = new LostReportController();
            match($action) {
                'create' => $ctrl->create(),
                'store'  => $ctrl->store(),
                'edit'   => $ctrl->edit(),
                'update' => $ctrl->update(),
                'delete' => $ctrl->delete(),
                'view'   => $ctrl->view(),
                default  => $ctrl->index(),
            };
            break;

        case 'matches':
            require_once __DIR__ . '/app/controllers/MatchController.php';
            $ctrl = new MatchController();
            match($action) {
                'create'    => $ctrl->create(),
                'store'     => $ctrl->store(),
                'confirm'   => $ctrl->confirm(),
                'reject'    => $ctrl->reject(),
                'auto-run'  => $ctrl->autoRun(),
                default     => $ctrl->index(),
            };
            break;

        case 'claims':
            require_once __DIR__ . '/app/controllers/ClaimController.php';
            $ctrl = new ClaimController();
            match($action) {
                'create' => $ctrl->create(),
                'store'  => $ctrl->store(),
                'view'   => $ctrl->view(),
                default  => $ctrl->index(),
            };
            break;

        case 'branches':
            requireRole('superadmin');
            require_once __DIR__ . '/app/controllers/BranchController.php';
            $ctrl = new BranchController();
            match($action) {
                'create' => $ctrl->create(),
                'store'  => $ctrl->store(),
                'edit'   => $ctrl->edit(),
                'update' => $ctrl->update(),
                'delete' => $ctrl->delete(),
                default  => $ctrl->index(),
            };
            break;

        case 'users':
            requireRole('superadmin','branch_manager');
            require_once __DIR__ . '/app/controllers/UserController.php';
            $ctrl = new UserController();
            match($action) {
                'create' => $ctrl->create(),
                'store'  => $ctrl->store(),
                'edit'   => $ctrl->edit(),
                'update' => $ctrl->update(),
                'delete' => $ctrl->delete(),
                default  => $ctrl->index(),
            };
            break;

        case 'reports':
            require_once __DIR__ . '/app/controllers/ReportController.php';
            $ctrl = new ReportController();
            match($action) {
                'export-pdf' => $ctrl->exportPdf(),
                'export-csv' => $ctrl->exportCsv(),
                default      => $ctrl->index(),
            };
            break;

        default:
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            (new DashboardController())->index();
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  REACT FRONTEND  — public landing page (not logged in)
// ═══════════════════════════════════════════════════════════════════
$baseUrl   = rtrim(BASE_URL, '/');
$uploadUrl = UPLOAD_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC Lost &amp; Found System</title>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --primary:#1a472a;--primary-light:#2d5a3f;--accent:#f4a261;--accent-hover:#e76f51;
            --bg:#fafaf9;--card-bg:#fff;--text:#1a1a1a;--text-light:#666;
            --border:#e5e5e5;--success:#2a9d8f;--shadow:0 2px 8px rgba(0,0,0,.08);
            --shadow-lg:0 8px 24px rgba(0,0,0,.12);
        }
        body{font-family:'DM Sans',sans-serif;background:linear-gradient(135deg,#fafaf9 0%,#f0f0ef 100%);color:var(--text);line-height:1.6;min-height:100vh;}
        .btn{padding:.6rem 1.2rem;border-radius:8px;border:none;cursor:pointer;font-weight:600;font-size:.875rem;transition:all .2s;font-family:'DM Sans',sans-serif;}
        .btn-primary{background:var(--accent);color:#fff;}
        .btn-primary:hover{background:var(--accent-hover);transform:translateY(-1px);}
        .btn-secondary{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);}
        .btn-secondary:hover{background:rgba(255,255,255,.25);}
        .btn-ghost{background:transparent;color:var(--text-light);}
        .btn-ghost:hover{background:var(--border);}

        /* Header */
        .header{background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);color:#fff;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--shadow-lg);}
        .header-brand{display:flex;align-items:center;gap:1rem;cursor:pointer;}
        .header-logo{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;}
        .header-title{font-weight:700;font-size:1.1rem;}
        .header-subtitle{font-size:.75rem;opacity:.7;}
        .header-nav{display:flex;gap:.5rem;align-items:center;}

        /* Landing */
        .landing-hero{background:linear-gradient(135deg,var(--primary) 0%,#2d6a4f 50%,#52b788 100%);color:#fff;padding:5rem 2rem;text-align:center;position:relative;overflow:hidden;}
        .landing-hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.05) 0%,transparent 60%);animation:pulse-bg 8s ease-in-out infinite;}
        .landing-hero-content{position:relative;z-index:1;max-width:700px;margin:0 auto;}
        .landing-logo{margin-bottom:1.5rem;display:flex;justify-content:center;}
        .landing-hero h1{font-size:clamp(1.8rem,5vw,3rem);font-weight:700;margin-bottom:1rem;}
        .landing-hero p{font-size:1.15rem;opacity:.85;margin-bottom:2.5rem;}
        .landing-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
        .landing-features{padding:4rem 2rem;background:#fff;}
        .landing-features-content{max-width:1200px;margin:0 auto;}
        .landing-features h2{text-align:center;font-size:2rem;font-weight:700;margin-bottom:3rem;color:var(--primary);}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;}
        .feature-card{padding:1.5rem;border-radius:12px;border:1px solid var(--border);transition:all .2s;}
        .feature-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg);border-color:var(--primary);}
        .feature-icon{font-size:2.5rem;margin-bottom:1rem;display:block;}
        .feature-title{font-weight:700;margin-bottom:.5rem;color:var(--primary);}
        .feature-desc{color:var(--text-light);font-size:.9rem;line-height:1.6;}
        .landing-stats{padding:3rem 2rem;background:var(--primary);color:#fff;}
        .stats-content{max-width:800px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:2rem;text-align:center;}
        .stat-number{font-size:2.5rem;font-weight:700;font-family:'Space Mono',monospace;}
        .stat-label{opacity:.7;font-size:.9rem;margin-top:.25rem;}
        .landing-cta-section{padding:4rem 2rem;text-align:center;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);}
        .landing-cta-section h2{font-size:2rem;color:var(--primary);margin-bottom:1rem;}
        .landing-cta-section p{color:var(--text-light);margin-bottom:2rem;font-size:1.05rem;}

        /* Browse */
        .browse-page{padding:2rem;max-width:1200px;margin:0 auto;}
        .search-bar{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;}
        .search-input{flex:1;min-width:200px;padding:.75rem 1rem;border:2px solid var(--border);border-radius:10px;font-size:.95rem;font-family:'DM Sans',sans-serif;}
        .search-input:focus{outline:none;border-color:var(--primary);}
        .items-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;}
        .item-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:var(--shadow);border:1px solid var(--border);transition:all .2s;cursor:pointer;}
        .item-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg);}
        .item-img{width:100%;height:180px;object-fit:cover;}
        .item-img-placeholder{width:100%;height:180px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:3rem;}
        .item-body{padding:1rem;}
        .item-name{font-weight:700;font-size:1rem;margin-bottom:.25rem;}
        .item-meta{font-size:.8rem;color:var(--text-light);margin-bottom:.4rem;}
        .item-status{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:600;}
        .s-unclaimed{background:#dcfce7;color:#15803d;}
        .s-claimed{background:#f3f4f6;color:#6b7280;}
        .s-matched{background:#fef3c7;color:#d97706;}
        .empty-state{text-align:center;padding:4rem 2rem;color:var(--text-light);}
        .empty-icon{font-size:4rem;margin-bottom:1rem;}

        /* Modal */
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem;}
        .modal-box{background:#fff;border-radius:20px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;}
        .modal-header{padding:1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
        .modal-body{padding:1.5rem;}
        .detail-row{margin-bottom:1rem;}
        .detail-label{font-size:.8rem;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;}
        .detail-value{font-size:.95rem;}

        /* Login */
        .login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(135deg,var(--primary) 0%,#2d6a4f 100%);position:relative;overflow:hidden;}
        .login-page::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 60%);animation:pulse-bg 8s ease-in-out infinite;}
        .login-box{background:#fff;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,.15);max-width:450px;width:100%;position:relative;z-index:1;overflow:hidden;}
        .login-head{padding:2.5rem 2.5rem 1.5rem;text-align:center;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;}
        .login-logo{width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;overflow:hidden;}
        .login-head h2{font-size:1.5rem;font-weight:700;margin-bottom:.25rem;}
        .login-head p{opacity:.8;font-size:.9rem;}
        .login-body{padding:2rem 2.5rem;}
        .login-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;background:#f5f5f5;padding:.25rem;border-radius:10px;}
        .login-tab{flex:1;padding:.5rem;border-radius:8px;border:none;background:transparent;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--text-light);transition:all .2s;font-family:'DM Sans',sans-serif;}
        .login-tab.active{background:#fff;color:var(--primary);box-shadow:0 1px 4px rgba(0,0,0,.1);}
        .form-group{margin-bottom:1rem;}
        .form-label{display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;}
        .form-input{width:100%;padding:.7rem 1rem;border:2px solid var(--border);border-radius:8px;font-size:.9rem;font-family:'DM Sans',sans-serif;transition:border-color .2s;}
        .form-input:focus{outline:none;border-color:var(--primary);}
        .error-msg{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.6rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1rem;}
        .info-box{background:#f0fdf4;border:1px solid #bbf7d0;padding:.85rem 1rem;border-radius:10px;font-size:.875rem;color:#15803d;margin-bottom:1rem;}
        .login-btn{width:100%;padding:.875rem;background:var(--primary);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;margin-top:.5rem;display:flex;align-items:center;justify-content:center;gap:.5rem;}
        .login-btn:hover:not(:disabled){background:var(--primary-light);transform:translateY(-1px);}
        .login-btn:disabled{opacity:.75;cursor:not-allowed;}
        .login-foot{padding:1.25rem 2.5rem 2rem;text-align:center;background:#fafaf9;font-size:.85rem;color:var(--text-light);}
        .login-foot a{color:var(--primary);font-weight:600;text-decoration:none;}
        .back-link{color:var(--text-light);font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;margin-top:.75rem;}
        .back-link:hover{color:var(--primary);}
        .verify-box{margin-top:1rem;padding:.75rem 1rem;background:rgba(26,71,42,.06);border-radius:8px;border:1px solid rgba(26,71,42,.15);display:flex;align-items:center;gap:.6rem;font-size:.88rem;color:var(--primary);font-weight:500;}

        /* Spinner */
        .spinner{width:18px;height:18px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;flex-shrink:0;}
        .spinner-sm{width:14px;height:14px;border:2px solid rgba(26,71,42,.2);border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite;flex-shrink:0;}
        .toast{position:fixed;bottom:2rem;right:2rem;background:#1a472a;color:#fff;padding:1rem 1.5rem;border-radius:12px;box-shadow:var(--shadow-lg);z-index:9999;font-weight:600;max-width:320px;}

        @keyframes spin{to{transform:rotate(360deg);}}
        @keyframes pulse-bg{0%,100%{transform:scale(1);opacity:.5;}50%{transform:scale(1.1);opacity:.8;}}

        @media(max-width:640px){
            .header{padding:.75rem 1rem;}
            .header-subtitle{display:none;}
            .landing-hero{padding:3rem 1rem;}
            .browse-page{padding:1rem;}
            .login-body,.login-head,.login-foot{padding:1.5rem;}
        }
    </style>
</head>
<body>
<div id="root"></div>

<script type="text/babel">
    const { useState, useEffect, useCallback } = React;
    const BASE_URL   = "<?php echo $baseUrl; ?>";
    const UPLOAD_URL = "<?php echo $uploadUrl; ?>";

    function Toast({ message, onClose }) {
        useEffect(() => { const t = setTimeout(onClose, 3500); return () => clearTimeout(t); }, []);
        return <div className="toast">✅ {message}</div>;
    }

    function Header({ onNavigate }) {
        return (
            <header className="header">
                <div className="header-brand" onClick={() => onNavigate('landing')}>
                    <div className="header-logo"><img src={`${BASE_URL}/public/ucc-logo.png`} alt="UCC Logo" style={{width:'36px',height:'36px',objectFit:'contain',borderRadius:'6px'}} /></div>
                    <div>
                        <div className="header-title">Lost &amp; Found System</div>
                        <div className="header-subtitle">University of Caloocan City</div>
                    </div>
                </div>
                <nav className="header-nav">
                    <button className="btn btn-secondary" onClick={() => onNavigate('browse')}>🔍 Browse Items</button>
                    <button className="btn btn-primary"   onClick={() => onNavigate('login')}>🔐 Login</button>
                </nav>
            </header>
        );
    }

    function LandingPage({ onNavigate, stats }) {
        return (
            <div>
                <div className="landing-hero">
                    <div className="landing-hero-content">
                        <div className="landing-logo"><img src={`${BASE_URL}/public/ucc-logo.png`} alt="UCC Logo" style={{width:'120px',height:'120px',objectFit:'contain'}} /></div>
                        <h1>Lost &amp; Found Assistance System</h1>
                        <p>AI-Powered Item Recovery for University of Caloocan City Campus</p>
                        <div className="landing-cta">
                            <button className="btn btn-primary"   onClick={() => onNavigate('login')}>🔐 Login to System</button>
                            <button className="btn btn-secondary" onClick={() => onNavigate('browse')}>🔍 Browse Found Items</button>
                        </div>
                    </div>
                </div>

                <div className="landing-features">
                    <div className="landing-features-content">
                        <h2>How It Works</h2>
                        <div className="features-grid">
                            {[
                                {icon:'🤖',title:'AI Recognition',   desc:'Advanced AI automatically matches lost items with found items based on descriptions and photos.'},
                                {icon:'📸',title:'Photo Uploads',    desc:'Upload photos of lost or found items for better matching accuracy and faster recovery.'},
                                {icon:'🔔',title:'Instant Alerts',   desc:'Get instant alerts when potential matches are found or when your items are claimed.'},
                                {icon:'🗄️',title:'Centralized DB',  desc:'All lost and found items organized in one secure, searchable database accessible 24/7.'},
                                {icon:'🔒',title:'Secure Claims',    desc:'Secure claim process with manual staff verification to ensure items reach rightful owners.'},
                                {icon:'🏫',title:'Multi-Campus',    desc:'Covers all UCC campuses — report from any branch and track status in real time.'},
                            ].map(f => (
                                <div className="feature-card" key={f.title}>
                                    <span className="feature-icon">{f.icon}</span>
                                    <h3 className="feature-title">{f.title}</h3>
                                    <p className="feature-desc">{f.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {stats && (
                    <div className="landing-stats">
                        <div className="stats-content">
                            <div><div className="stat-number">{stats.totalFound}</div><div className="stat-label">Items Logged</div></div>
                            <div><div className="stat-number">{stats.totalClaimed}</div><div className="stat-label">Items Returned</div></div>
                            <div><div className="stat-number">{stats.totalBranches}</div><div className="stat-label">Campus Branches</div></div>
                            <div><div className="stat-number">{stats.totalUnclaimed}</div><div className="stat-label">Awaiting Claim</div></div>
                        </div>
                    </div>
                )}

                <div className="landing-cta-section">
                    <h2>Ready to find your lost items?</h2>
                    <p>Login or create a free guest account to report a lost item and track its status.</p>
                    <button className="btn btn-primary" onClick={() => onNavigate('login')}
                        style={{fontSize:'1.1rem',padding:'1rem 3rem'}}>
                        🚀 Get Started Now
                    </button>
                </div>
            </div>
        );
    }

    function BrowsePage({ onNavigate }) {
        const [items,        setItems]        = useState([]);
        const [loading,      setLoading]      = useState(true);
        const [searchQuery,  setSearchQuery]  = useState('');
        const [selectedItem, setSelectedItem] = useState(null);

        const fetchItems = useCallback(async (q = '') => {
            setLoading(true);
            try {
                const res  = await fetch(`${BASE_URL}/index.php?api=found-items&q=${encodeURIComponent(q)}`);
                const data = await res.json();
                setItems(data.items || []);
            } catch { setItems([]); }
            setLoading(false);
        }, []);

        useEffect(() => { fetchItems(); }, []);

        const handleSearch = (e) => { e.preventDefault(); fetchItems(searchQuery); };

        const imgSrc = (item) => item.photo ? `${UPLOAD_URL}${item.photo}` : null;

        const statusCls = (s) => s === 'unclaimed' ? 's-unclaimed' : s === 'matched' ? 's-matched' : 's-claimed';
        const statusLbl = (s) => s === 'unclaimed' ? '✅ Available' : s === 'matched' ? '🔗 Matched' : '✔ Claimed';

        return (
            <div className="browse-page">
                <div style={{marginBottom:'1.5rem'}}>
                    <h1 style={{fontSize:'1.75rem',fontWeight:700,color:'var(--primary)'}}>Browse Found Items</h1>
                    <p style={{color:'var(--text-light)',marginTop:'.25rem'}}>
                        Items currently held at UCC campuses. Log in to report a lost item or claim one.
                    </p>
                </div>

                <form className="search-bar" onSubmit={handleSearch}>
                    <input className="search-input" type="text"
                        placeholder="Search by name, brand, description..."
                        value={searchQuery} onChange={e => setSearchQuery(e.target.value)} />
                    <button type="submit" className="btn btn-primary">🔍 Search</button>
                    {searchQuery && (
                        <button type="button" className="btn btn-ghost"
                            onClick={() => { setSearchQuery(''); fetchItems(''); }}>Clear</button>
                    )}
                </form>

                {loading ? (
                    <div className="empty-state">
                        <div className="empty-icon">⏳</div>
                        <p>Loading items from database...</p>
                    </div>
                ) : items.length === 0 ? (
                    <div className="empty-state">
                        <div className="empty-icon">📭</div>
                        <p>No items found. Try a different search or check back later.</p>
                    </div>
                ) : (
                    <>
                        <p style={{color:'var(--text-light)',fontSize:'.85rem',marginBottom:'1rem'}}>
                            Showing {items.length} item{items.length !== 1 ? 's' : ''}
                        </p>
                        <div className="items-grid">
                            {items.map(item => (
                                <div className="item-card" key={item.id} onClick={() => setSelectedItem(item)}>
                                    {imgSrc(item)
                                        ? <img src={imgSrc(item)} alt={item.item_name} className="item-img" />
                                        : <div className="item-img-placeholder">📦</div>
                                    }
                                    <div className="item-body">
                                        <div className="item-name">{item.item_name}</div>
                                        <div className="item-meta">
                                            {item.category && <span>📁 {item.category} · </span>}
                                            📍 {item.found_location || 'Unknown location'}
                                        </div>
                                        <div className="item-meta">🏫 {item.branch} · 📅 {item.found_date}</div>
                                        <span className={`item-status ${statusCls(item.status)}`}>{statusLbl(item.status)}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                )}

                {selectedItem && (
                    <div className="modal-overlay" onClick={() => setSelectedItem(null)}>
                        <div className="modal-box" onClick={e => e.stopPropagation()}>
                            <div className="modal-header">
                                <h3 style={{fontWeight:700}}>{selectedItem.item_name}</h3>
                                <button className="btn btn-ghost" onClick={() => setSelectedItem(null)}>✕</button>
                            </div>
                            <div className="modal-body">
                                {imgSrc(selectedItem) && (
                                    <img src={imgSrc(selectedItem)} alt={selectedItem.item_name}
                                         style={{width:'100%',borderRadius:10,marginBottom:'1rem',maxHeight:250,objectFit:'cover'}} />
                                )}
                                {[
                                    ['Item Name',      selectedItem.item_name],
                                    ['Category',       selectedItem.category],
                                    ['Description',    selectedItem.description],
                                    ['Color',          selectedItem.color],
                                    ['Brand',          selectedItem.brand],
                                    ['Found Location', selectedItem.found_location],
                                    ['Found Date',     selectedItem.found_date],
                                    ['Branch/Campus',  selectedItem.branch],
                                    ['Status',         selectedItem.status],
                                ].filter(([,v]) => v).map(([l,v]) => (
                                    <div className="detail-row" key={l}>
                                        <div className="detail-label">{l}</div>
                                        <div className="detail-value">{v}</div>
                                    </div>
                                ))}
                                {selectedItem.status === 'unclaimed' && (
                                    <button className="btn btn-primary" style={{width:'100%',marginTop:'1rem'}}
                                        onClick={() => { setSelectedItem(null); onNavigate('login'); }}>
                                        🔐 Login to Claim This Item
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    }

    function LoginPage({ onNavigate }) {
        const [isSignup,   setIsSignup]   = useState(false);
        const [form,       setForm]       = useState({fullName:'',email:'',password:''});
        const [error,      setError]      = useState('');
        const [verifying,  setVerifying]  = useState(false);
        const [verifyStep, setVerifyStep] = useState('');

        const setField = (e) => { setForm(p => ({...p,[e.target.name]:e.target.value})); setError(''); };

        const doLogin = async (e) => {
            e.preventDefault();
            if (!form.email || !form.password) { setError('Please fill in all fields.'); return; }
            setVerifying(true); setError('');
            try {
                setVerifyStep('Connecting to server...');
                await new Promise(r => setTimeout(r, 400));
                setVerifyStep('Verifying your credentials...');
                const res  = await fetch(`${BASE_URL}/index.php?api=login`, {
                    method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({email:form.email, password:form.password})
                });
                setVerifyStep('Checking account status...');
                await new Promise(r => setTimeout(r, 300));
                const data = await res.json();
                if (data.success) {
                    setVerifyStep('✅ Login successful! Redirecting to dashboard...');
                    await new Promise(r => setTimeout(r, 800));
                    window.location.href = data.redirect;
                } else {
                    setError(data.message || 'Login failed. Please try again.');
                }
            } catch { setError('Could not reach the server. Please try again.'); }
            finally { setVerifying(false); setVerifyStep(''); }
        };

        const doSignup = async (e) => {
            e.preventDefault();
            if (!form.fullName || !form.email || !form.password) { setError('All fields are required.'); return; }
            if (form.password.length < 6) { setError('Password must be at least 6 characters.'); return; }
            setVerifying(true); setError('');
            try {
                setVerifyStep('Validating your information...');
                await new Promise(r => setTimeout(r, 400));
                setVerifyStep('Checking if email is available...');
                const res  = await fetch(`${BASE_URL}/index.php?api=signup`, {
                    method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({fullName:form.fullName, email:form.email, password:form.password})
                });
                setVerifyStep('Creating your account...');
                await new Promise(r => setTimeout(r, 400));
                const data = await res.json();
                if (data.success) {
                    setVerifyStep('✅ Account created! Welcome to UCC Lost & Found...');
                    await new Promise(r => setTimeout(r, 900));
                    window.location.href = data.redirect;
                } else {
                    setError(data.message || 'Sign up failed.');
                }
            } catch { setError('Could not reach the server. Please try again.'); }
            finally { setVerifying(false); setVerifyStep(''); }
        };

        return (
            <div className="login-page">
                <div className="login-box">
                    <div className="login-head">
                        <div className="login-logo"><img src={`${BASE_URL}/public/ucc-logo.png`} alt="UCC Logo" style={{width:'50px',height:'50px',objectFit:'contain'}} /></div>
                        <h2>{isSignup ? 'Create Account' : 'Welcome Back'}</h2>
                        <p>{isSignup ? 'Register to track your lost items' : 'Sign in to your account'}</p>
                    </div>

                    <div className="login-body">
                        {error && <div className="error-msg">⚠️ {error}</div>}

                        <form onSubmit={isSignup ? doSignup : doLogin}>
                            {isSignup && (
                                <div className="form-group">
                                    <label className="form-label">Full Name *</label>
                                    <input name="fullName" type="text" className="form-input"
                                        placeholder="Enter your full name" value={form.fullName} onChange={setField} autoFocus />
                                </div>
                            )}
                            <div className="form-group">
                                <label className="form-label">Email Address *</label>
                                <input name="email" type="email" className="form-input"
                                    placeholder="your.email@ucc.edu.ph" value={form.email} onChange={setField}
                                    autoFocus={!isSignup} />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Password *</label>
                                <input name="password" type="password" className="form-input"
                                    placeholder="••••••••" value={form.password} onChange={setField} />
                            </div>

                            <button type="submit" className="login-btn" disabled={verifying}>
                                {verifying
                                    ? <><span className="spinner"></span> Verifying...</>
                                    : (isSignup ? '✨ Create Account' : '🔐 Sign In')}
                            </button>

                            {verifying && (
                                <div className="verify-box">
                                    <span className="spinner-sm"></span>
                                    {verifyStep}
                                </div>
                            )}
                        </form>
                    </div>

                    <div className="login-foot">
                        {isSignup ? (
                            <p>Already have an account?{' '}
                                <a href="#" onClick={e=>{e.preventDefault();setIsSignup(false);setError('');setForm({fullName:'',email:'',password:''});}}>Login here</a>
                            </p>
                        ) : (
                            <p>No account yet?{' '}
                                <a href="#" onClick={e=>{e.preventDefault();setIsSignup(true);setError('');setForm({fullName:'',email:'',password:''});}}>Sign up for free</a>
                            </p>
                        )}
                        <a href="#" className="back-link" onClick={e=>{e.preventDefault();onNavigate('landing');}}>
                            ← Back to Home
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    function App() {
        const [page,  setPage]  = useState((new URLSearchParams(window.location.search)).get('page') === 'guest-login' ? 'login' : 'landing');
        const [stats, setStats] = useState(null);
        const [toast, setToast] = useState('');

        useEffect(() => {
            fetch(`${BASE_URL}/index.php?api=found-items`)
                .then(r => r.json())
                .then(data => {
                    const items = data.items || [];
                    const branches = [...new Set(items.map(i => i.branch).filter(Boolean))];
                    setStats({
                        totalFound:    items.length,
                        totalClaimed:  items.filter(i => i.status === 'claimed').length,
                        totalUnclaimed:items.filter(i => i.status === 'unclaimed').length,
                        totalBranches: branches.length || '—',
                    });
                }).catch(() => {});
        }, []);

        const navigate = (p) => { setPage(p); window.scrollTo(0,0); };

        return (
            <>
                {toast && <Toast message={toast} onClose={() => setToast('')} />}
                {page !== 'login' && <Header onNavigate={navigate} />}
                {page === 'landing' && <LandingPage onNavigate={navigate} stats={stats} />}
                {page === 'browse'  && <BrowsePage  onNavigate={navigate} />}
                {page === 'login'   && <LoginPage   onNavigate={navigate} />}

            </>
        );
    }

    ReactDOM.render(<App />, document.getElementById('root'));
</script>
</body>
</html>
