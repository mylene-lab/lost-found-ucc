<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> - <?= APP_NAME ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #16a34a;
            --primary-dark: #15803d;
            --sidebar-bg: #14532d;
            --sidebar-width: 260px;
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width); background: var(--sidebar-bg);
            z-index: 1000; overflow-y: auto; transition: transform .3s ease;
        }
        .sidebar .brand {
            padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar .brand h5 { color: #fff; font-weight: 700; margin: 0; font-size: .95rem; }
        .sidebar .brand small { color: rgba(255,255,255,.5); font-size: .75rem; }
        .sidebar .nav-section { padding: .75rem 1.25rem .25rem; color: rgba(255,255,255,.35); font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; }
        .sidebar .nav-link {
            color: rgba(255,255,255,.65); padding: .6rem 1.25rem; border-radius: .375rem;
            margin: .1rem .75rem; display: flex; align-items: center; gap: .75rem;
            font-size: .875rem; transition: all .15s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,.1); color: #fff;
        }
        .sidebar .nav-link.active { background: var(--primary); color: #fff; }
        .sidebar .nav-link i { width: 18px; text-align: center; }

        /* Main */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: .875rem 1.5rem; position: sticky; top: 0; z-index: 999;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar .page-title { font-weight: 600; color: #1e293b; margin: 0; font-size: 1.1rem; }
        .content-area { padding: 1.5rem; }

        /* Cards */
        .stat-card { border: none; border-radius: 1rem; overflow: hidden; transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-icon { width: 52px; height: 52px; border-radius: .75rem; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        /* Tables */
        .table-card { background: #fff; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden; }
        .table-card .table { margin: 0; }
        .table-card .table th { background: #f8fafc; font-size: .8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e2e8f0; }
        .table-card .table td { vertical-align: middle; font-size: .875rem; }
        .table-card .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; }

        /* Photo thumbnail */
        .item-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: .5rem; border: 2px solid #e2e8f0; }
        .item-thumb-placeholder { width: 44px; height: 44px; background: #f1f5f9; border-radius: .5rem; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8; }

        /* Forms */
        .form-card { background: #fff; border-radius: 1rem; border: 1px solid #e2e8f0; padding: 1.5rem; }
        .form-label { font-size: .875rem; font-weight: 500; color: #374151; }
        .form-control, .form-select { border-radius: .5rem; border-color: #d1d5db; font-size: .875rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }

        /* Breadcrumb */
        .breadcrumb { font-size: .8rem; }
        .breadcrumb-item.active { color: #64748b; }

        /* Badge tweaks */
        .badge { font-size: .72rem; font-weight: 500; padding: .35em .65em; }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>
<?php $user = currentUser(); $currentPage = $_GET['page'] ?? 'dashboard'; ?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:40px;height:40px;border-radius:.5rem;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <img src="<?= BASE_URL ?>/public/ucc-logo.png" alt="UCC Logo" style="width:100%;height:100%;object-fit:contain;">
            </div>
            <div>
                <h5><?= APP_NAME ?></h5>
                <small><?= defined('APP_SUBTITLE') ? APP_SUBTITLE : '' ?> &mdash; <?= e($user['branch_name']) ?></small>
            </div>
        </div>
    </div>

    <nav class="mt-2 pb-4">

        <?php if($user['role'] === 'guest'): ?>
        <!-- ── GUEST MENU ── -->
        <div class="nav-section">My Account</div>
        <a href="?page=dashboard" class="nav-link <?= $currentPage==='dashboard'?'active':'' ?>">
            <i class="fas fa-home"></i> My Dashboard
        </a>
        <a href="?page=guest-portal" class="nav-link <?= $currentPage==='guest-portal'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> My Reports
        </a>
        <a href="?page=guest-portal&action=create" class="nav-link">
            <i class="fas fa-plus-circle"></i> Report Lost Item
        </a>

        <?php elseif($user['role'] === 'staff'): ?>
        <!-- ── STAFF MENU ── -->
        <div class="nav-section">Main</div>
        <a href="?page=dashboard" class="nav-link <?= $currentPage==='dashboard'?'active':'' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-section">Items</div>
        <a href="?page=found-items" class="nav-link <?= $currentPage==='found-items'?'active':'' ?>">
            <i class="fas fa-box-open"></i> Found Items
        </a>
        <a href="?page=lost-reports" class="nav-link <?= $currentPage==='lost-reports'?'active':'' ?>">
            <i class="fas fa-exclamation-circle"></i> Lost Reports
            <?php
            $guestCount = getDB()->query("SELECT COUNT(*) c FROM lost_reports r JOIN users u ON u.id=r.logged_by WHERE u.role='guest' AND r.status='open'")->fetch_assoc()['c'];
            if($guestCount > 0): ?>
            <span class="badge bg-warning text-dark ms-auto"><?= $guestCount ?></span>
            <?php endif; ?>
        </a>
        <a href="?page=matches" class="nav-link <?= $currentPage==='matches'?'active':'' ?>">
            <i class="fas fa-link"></i> Item Matches
            <?php
            $pendingCount = getDB()->query("SELECT COUNT(*) c FROM item_matches m JOIN found_items f ON f.id=m.found_item_id WHERE m.status='pending'" . (getBranchScope() ? " AND f.branch_id=".getBranchScope() : ""))->fetch_assoc()['c'];
            if($pendingCount > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="?page=claims" class="nav-link <?= $currentPage==='claims'?'active':'' ?>">
            <i class="fas fa-handshake"></i> Claims
        </a>

        <div class="nav-section">Reports</div>
        <a href="?page=reports" class="nav-link <?= $currentPage==='reports'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Reports & Export
        </a>

        <?php else: ?>
        <!-- ── ADMIN / BRANCH MANAGER MENU ── -->
        <div class="nav-section">Main</div>
        <a href="?page=dashboard" class="nav-link <?= $currentPage==='dashboard'?'active':'' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>

        <div class="nav-section">Items</div>
        <a href="?page=found-items" class="nav-link <?= $currentPage==='found-items'?'active':'' ?>">
            <i class="fas fa-box-open"></i> Found Items
        </a>
        <a href="?page=lost-reports" class="nav-link <?= $currentPage==='lost-reports'?'active':'' ?>">
            <i class="fas fa-exclamation-circle"></i> Lost Reports
            <?php
            $guestCount = getDB()->query("SELECT COUNT(*) c FROM lost_reports r JOIN users u ON u.id=r.logged_by WHERE u.role='guest' AND r.status='open'")->fetch_assoc()['c'];
            if($guestCount > 0): ?>
            <span class="badge bg-warning text-dark ms-auto"><?= $guestCount ?></span>
            <?php endif; ?>
        </a>
        <a href="?page=matches" class="nav-link <?= $currentPage==='matches'?'active':'' ?>">
            <i class="fas fa-link"></i> Item Matches
        </a>
        <a href="?page=claims" class="nav-link <?= $currentPage==='claims'?'active':'' ?>">
            <i class="fas fa-handshake"></i> Claims
        </a>

        <div class="nav-section">Reports</div>
        <a href="?page=reports" class="nav-link <?= $currentPage==='reports'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Reports & Export
        </a>

        <div class="nav-section">Management</div>
        <a href="?page=users" class="nav-link <?= $currentPage==='users'?'active':'' ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <?php if($user['role']==='superadmin'): ?>
        <a href="?page=branches" class="nav-link <?= $currentPage==='branches'?'active':'' ?>">
            <i class="fas fa-university"></i> Campuses / Branches
        </a>
        <?php endif; ?>

        <?php endif; ?>

        <div class="nav-section mt-2">Account</div>
        <div class="nav-link" style="cursor:default;opacity:.7">
            <i class="fas fa-user-circle"></i>
            <div>
                <div style="font-size:.8rem;font-weight:600;color:#fff"><?= e($user['name']) ?></div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.4)"><?= ucfirst(str_replace('_',' ',$user['role'])) ?></div>
            </div>
        </div>
        <a href="?page=logout" class="nav-link" onclick="return confirm('Log out?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<!-- Main -->
<div class="main-wrapper">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h6 class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border"><i class="fas fa-building me-1"></i><?= e($user['branch_name']) ?></span>
            <span class="badge" style="background:var(--primary)"><?= ucfirst(str_replace('_',' ',$user['role'])) ?></span>
        </div>
    </div>

    <div class="content-area">
        <?php $flash=getFlash(); if($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
