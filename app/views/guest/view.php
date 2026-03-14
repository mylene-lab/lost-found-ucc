<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?= $report['id'] ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0fdf4; min-height: 100vh; }
        .topbar { background:#fff; border-bottom:3px solid #16a34a; padding:.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 1px 8px rgba(0,0,0,.06); }
        .topbar .brand-icon { width:36px; height:36px; background:#16a34a; border-radius:.5rem; display:flex; align-items:center; justify-content:center; }
        .content { padding:2rem 1rem; max-width:760px; margin:0 auto; }
        .detail-card { background:#fff; border-radius:1rem; border:1px solid #e2e8f0; padding:1.75rem; box-shadow:0 2px 12px rgba(0,0,0,.06); }
        .status-banner { border-radius:.875rem; padding:1rem 1.25rem; margin-bottom:1.25rem; }
        .status-open { background:#dbeafe; border:1px solid #93c5fd; }
        .status-matched { background:#fef3c7; border:1px solid #fde68a; }
        .status-closed { background:#d1fae5; border:1px solid #6ee7b7; }
        .detail-row { display:flex; gap:.5rem; margin-bottom:.5rem; font-size:.875rem; }
        .detail-label { color:#6b7280; min-width:130px; font-weight:500; }
        .match-card { background:#fffbeb; border:1px solid #fde68a; border-radius:.875rem; padding:1rem 1.25rem; }
        .item-thumb { width:64px; height:64px; object-fit:cover; border-radius:.625rem; border:2px solid #e2e8f0; }
        .item-thumb-ph { width:64px; height:64px; background:#f1f5f9; border-radius:.625rem; border:2px solid #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8; }
        @media(max-width:576px){ .content{ padding:1rem .75rem; } .detail-row{ flex-direction:column; gap:.1rem; } }
    </style>
</head>
<body>

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <div class="brand-icon" style="overflow:hidden;"><img src="<?= BASE_URL ?>/public/ucc-logo.png" alt="UCC Logo" style="width:100%;height:100%;object-fit:contain;"></div>
        <span style="font-weight:700;color:#14532d"><?= APP_NAME ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="?page=guest-portal" class="btn btn-sm btn-outline-success"><i class="fas fa-arrow-left me-1"></i>My Reports</a>
        <a href="?page=logout" class="btn btn-sm btn-outline-danger" onclick="return confirm('Log out?')"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>

<div class="content">

    <!-- Status Banner -->
    <div class="status-banner status-<?= $report['status'] ?>">
        <div class="d-flex align-items-center gap-2">
            <?php
            $icons  = ['open'=>'fa-clock text-primary','matched'=>'fa-link text-warning','closed'=>'fa-check-circle text-success'];
            $msgs   = [
                'open'    => 'Your report is <strong>open</strong>. Our staff is actively searching for your item.',
                'matched' => '🎉 <strong>Possible match found!</strong> A found item may be yours. Please contact the branch as soon as possible.',
                'closed'  => '✅ This report has been <strong>closed</strong>. We hope you got your item back!',
            ];
            ?>
            <i class="fas <?= $icons[$report['status']] ?> fa-lg"></i>
            <div><?= $msgs[$report['status']] ?? 'Status: '.ucfirst($report['status']) ?></div>
        </div>
    </div>

    <div class="detail-card mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex gap-3 align-items-start">
                <?php if(!empty($report['photo'])): ?>
                <img src="<?= UPLOAD_URL . e($report['photo']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:.75rem;border:2px solid #bbf7d0;flex-shrink:0;">
                <?php endif; ?>
                <div>
                    <h5 class="fw-700 mb-0"><?= e($report['item_name']) ?></h5>
                    <small class="text-muted">Report #<?= $report['id'] ?> · Submitted <?= formatDateTime($report['created_at']) ?></small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="fw-600 small text-muted mb-2 text-uppercase" style="letter-spacing:.04em">Your Details</div>
                <div class="detail-row"><span class="detail-label">Reporter Name</span><span><?= e($report['reporter_name']) ?></span></div>
                <div class="detail-row"><span class="detail-label">Contact</span><span><?= e($report['reporter_contact']) ?></span></div>
                <?php if($report['reporter_email']): ?>
                <div class="detail-row"><span class="detail-label">Email</span><span><?= e($report['reporter_email']) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="fw-600 small text-muted mb-2 text-uppercase" style="letter-spacing:.04em">Item Details</div>
                <div class="detail-row"><span class="detail-label">Category</span><span><?= e($report['category_name'] ?? '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Brand</span><span><?= e($report['brand'] ?: '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Color</span><span><?= e($report['color'] ?: '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Date Lost</span><span><?= formatDate($report['lost_date']) ?></span></div>
                <div class="detail-row"><span class="detail-label">Location Lost</span><span><?= e($report['lost_location'] ?: '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Branch</span><span><?= e($report['branch_name']) ?></span></div>
            </div>
        </div>

        <?php if($report['description']): ?>
        <div class="mt-3 pt-3 border-top">
            <div class="fw-600 small text-muted mb-1">DESCRIPTION</div>
            <div class="small"><?= nl2br(e($report['description'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if($report['notes']): ?>
        <div class="mt-2">
            <div class="fw-600 small text-muted mb-1">NOTES</div>
            <div class="small"><?= nl2br(e($report['notes'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Matches -->
    <?php if(!empty($matches)): ?>
    <div class="fw-700 mb-3" style="color:#14532d;font-size:1rem"><i class="fas fa-link me-2 text-warning"></i>Possible Match<?= count($matches)>1?'es':'' ?> Found</div>
    <?php foreach($matches as $m): ?>
    <div class="match-card mb-3">
        <div class="d-flex gap-3 align-items-start">
            <?php if($m['photo']): ?>
            <img src="<?= UPLOAD_URL . e($m['photo']) ?>" class="item-thumb" alt="Found item photo">
            <?php else: ?>
            <div class="item-thumb-ph"><i class="fas fa-image"></i></div>
            <?php endif; ?>
            <div class="flex-grow-1">
                <div class="fw-600"><?= e($m['found_name']) ?></div>
                <div class="row g-2 mt-1">
                    <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Found Date</div><div class="small fw-500"><?= formatDate($m['found_date']) ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Location</div><div class="small fw-500"><?= e($m['found_location'] ?: '—') ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Color</div><div class="small fw-500"><?= e($m['found_color'] ?: '—') ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Branch</div><div class="small fw-500"><?= e($m['branch_name']) ?></div></div>
                </div>
                <div class="mt-2 p-2 rounded" style="background:#fff;border:1px solid #fde68a;font-size:.8rem">
                    <i class="fas fa-info-circle text-warning me-1"></i>
                    To claim this item, please visit <strong><?= e($m['branch_name']) ?></strong> with a valid government-issued ID.
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php elseif($report['status'] === 'open'): ?>
    <div class="detail-card text-center py-4 text-muted">
        <i class="fas fa-search fa-2x mb-2 d-block"></i>
        <div class="fw-600">Still searching...</div>
        <small>Our staff is reviewing your report. We'll update the status if we find a match.</small>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
