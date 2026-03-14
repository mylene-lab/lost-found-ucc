<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Item #<?= $item['id'] ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #eff6ff; min-height: 100vh; }
        .topbar { background:#fff; border-bottom:3px solid #2563eb; padding:.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 1px 8px rgba(0,0,0,.06); }
        .topbar .brand-icon { width:36px; height:36px; background:#2563eb; border-radius:.5rem; display:flex; align-items:center; justify-content:center; }
        .content { padding:2rem 1rem; max-width:760px; margin:0 auto; }
        .detail-card { background:#fff; border-radius:1rem; border:1px solid #bfdbfe; padding:1.75rem; box-shadow:0 2px 12px rgba(37,99,235,.08); }
        .detail-row { display:flex; gap:.5rem; margin-bottom:.5rem; font-size:.875rem; }
        .detail-label { color:#6b7280; min-width:130px; font-weight:500; }
        .status-badge { display:inline-block; padding:.3em .8em; border-radius:20px; font-size:.8rem; font-weight:600; }
        .status-unclaimed { background:#dbeafe; color:#1e40af; }
        .status-matched   { background:#fef3c7; color:#92400e; }
        .status-claimed   { background:#d1fae5; color:#065f46; }
        .status-disposed  { background:#fee2e2; color:#991b1b; }
        .match-card { background:#fefce8; border:1px solid #fde68a; border-radius:.875rem; padding:1rem 1.25rem; }
        .item-thumb { width:64px; height:64px; object-fit:cover; border-radius:.625rem; border:2px solid #bfdbfe; }
        @media(max-width:576px){ .content{ padding:1rem .75rem; } .detail-row{ flex-direction:column; gap:.1rem; } }
    </style>
</head>
<body>

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <div class="brand-icon" style="overflow:hidden;"><img src="<?= BASE_URL ?>/public/ucc-logo.png" alt="UCC Logo" style="width:100%;height:100%;object-fit:contain;"></div>
        <span style="font-weight:700;color:#1e3a8a"><?= APP_NAME ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="?page=guest-portal&tab=found" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>My Found Items</a>
        <a href="?page=logout" class="btn btn-sm btn-outline-danger" onclick="return confirm('Log out?')"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>

<div class="content">

    <!-- Status Banner -->
    <?php
    $banners = [
        'unclaimed' => ['bg:#dbeafe;border:1px solid #93c5fd','fa-clock text-primary','Your found item report is <strong>active</strong>. Staff will review and try to find the owner.'],
        'matched'   => ['bg:#fef3c7;border:1px solid #fde68a','fa-link text-warning','🎉 <strong>Match found!</strong> This item has been linked to a lost report.'],
        'claimed'   => ['bg:#d1fae5;border:1px solid #6ee7b7','fa-check-circle text-success','✅ This item has been <strong>claimed</strong> by its owner. Thank you for turning it in!'],
        'disposed'  => ['bg:#fee2e2;border:1px solid #fca5a5','fa-trash text-danger','This item has been <strong>disposed</strong> of per policy.'],
    ];
    [$bannerStyle,$bannerIcon,$bannerMsg] = $banners[$item['status']] ?? ['bg:#f1f5f9;border:1px solid #e2e8f0','fa-info-circle text-muted','Status: '.ucfirst($item['status'])];
    ?>
    <div style="<?= $bannerStyle ?>;border-radius:.875rem;padding:1rem 1.25rem;margin-bottom:1.25rem">
        <div class="d-flex align-items-center gap-2">
            <i class="fas <?= $bannerIcon ?> fa-lg"></i>
            <div><?= $bannerMsg ?></div>
        </div>
    </div>

    <div class="detail-card mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex gap-3 align-items-start">
                <?php if(!empty($item['photo'])): ?>
                <img src="<?= UPLOAD_URL . e($item['photo']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:.75rem;border:2px solid #bfdbfe;flex-shrink:0;">
                <?php endif; ?>
                <div>
                    <h5 class="fw-700 mb-0"><?= e($item['item_name']) ?></h5>
                    <small class="text-muted">Found Item #<?= $item['id'] ?> · Submitted <?= formatDateTime($item['created_at']) ?></small>
                    <div class="mt-1"><span class="status-badge status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="fw-600 small text-muted mb-2 text-uppercase" style="letter-spacing:.04em">Item Details</div>
                <div class="detail-row"><span class="detail-label">Category</span><span><?= e($item['category_name'] ?? '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Brand</span><span><?= e($item['brand'] ?: '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Color</span><span><?= e($item['color'] ?: '—') ?></span></div>
            </div>
            <div class="col-md-6">
                <div class="fw-600 small text-muted mb-2 text-uppercase" style="letter-spacing:.04em">Where &amp; When</div>
                <div class="detail-row"><span class="detail-label">Date Found</span><span><?= formatDate($item['found_date']) ?></span></div>
                <div class="detail-row"><span class="detail-label">Location Found</span><span><?= e($item['found_location'] ?: '—') ?></span></div>
                <div class="detail-row"><span class="detail-label">Branch</span><span><?= e($item['branch_name']) ?></span></div>
            </div>
        </div>

        <?php if($item['description']): ?>
        <div class="mt-3 pt-3 border-top">
            <div class="fw-600 small text-muted mb-1">DESCRIPTION</div>
            <div class="small"><?= nl2br(e($item['description'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if($item['notes']): ?>
        <div class="mt-2">
            <div class="fw-600 small text-muted mb-1">NOTES</div>
            <div class="small"><?= nl2br(e($item['notes'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Matches -->
    <?php if(!empty($matches)): ?>
    <div class="fw-700 mb-3" style="color:#1e40af;font-size:1rem">
        <i class="fas fa-link me-2 text-warning"></i>Matched Lost Report<?= count($matches)>1?'s':'' ?>
    </div>
    <?php foreach($matches as $m): ?>
    <div class="match-card mb-3">
        <div class="fw-600 mb-2"><?= e($m['lost_name']) ?></div>
        <div class="row g-2">
            <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Lost Date</div><div class="small fw-500"><?= formatDate($m['lost_date']) ?></div></div>
            <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Lost Location</div><div class="small fw-500"><?= e($m['lost_location'] ?: '—') ?></div></div>
            <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Color</div><div class="small fw-500"><?= e($m['lost_color'] ?: '—') ?></div></div>
            <div class="col-6 col-md-3"><div class="text-muted" style="font-size:.72rem">Branch</div><div class="small fw-500"><?= e($m['branch_name']) ?></div></div>
        </div>
        <div class="mt-2 p-2 rounded" style="background:#fff;border:1px solid #fde68a;font-size:.8rem">
            <i class="fas fa-info-circle text-warning me-1"></i>
            The owner will be contacted. Thank you for turning this in!
        </div>
    </div>
    <?php endforeach; ?>
    <?php elseif($item['status'] === 'unclaimed'): ?>
    <div class="detail-card text-center py-4 text-muted" style="border-color:#bfdbfe">
        <i class="fas fa-search fa-2x mb-2 d-block text-primary"></i>
        <div class="fw-600">Searching for the owner...</div>
        <small>Staff is reviewing your report and looking for a matching lost report.</small>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
