<?php $pageTitle = 'My Dashboard'; require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Welcome Banner -->
<div class="alert mb-4 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);border:none;border-radius:1rem;color:#fff">
    <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-user fa-xl"></i>
    </div>
    <div>
        <div class="fw-700" style="font-size:1.1rem">Hello, <?= e($_SESSION['user_name']) ?>!</div>
        <div style="opacity:.85;font-size:.85rem">Track your lost item reports and see if anything has been matched.</div>
    </div>
    <div class="ms-auto">
        <a href="?page=guest-portal&action=create" class="btn btn-light btn-sm fw-600" style="color:#0284c7">
            <i class="fas fa-plus me-1"></i>Report Lost Item
        </a>
    </div>
</div>

<!-- Guest Stats -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Reports',    'value'=>$totalReports,   'icon'=>'fa-file-alt',       'color'=>'#0ea5e9','bg'=>'rgba(14,165,233,.1)'],
        ['label'=>'Open Reports',     'value'=>$openReports,    'icon'=>'fa-search',          'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)'],
        ['label'=>'Possible Matches', 'value'=>$matchedReports, 'icon'=>'fa-link',            'color'=>'#16a34a','bg'=>'rgba(22,163,74,.1)'],
        ['label'=>'Resolved',         'value'=>$closedReports,  'icon'=>'fa-check-circle',   'color'=>'#6b7280','bg'=>'rgba(107,114,128,.1)'],
    ];
    foreach($cards as $c): ?>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm" style="border-radius:.875rem;border:1px solid #e2e8f0">
            <div class="card-body p-3">
                <div style="width:38px;height:38px;background:<?= $c['bg'] ?>;border-radius:.625rem;display:flex;align-items:center;justify-content:center;margin-bottom:.75rem">
                    <i class="fas <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>"></i>
                </div>
                <div class="fw-700" style="font-size:1.6rem;color:<?= $c['color'] ?>;line-height:1"><?= $c['value'] ?></div>
                <div class="text-muted mt-1" style="font-size:.78rem"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if(!empty($myMatches)): ?>
<!-- Possible Matches Alert -->
<div class="alert d-flex align-items-start gap-3 mb-4" style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:1rem">
    <i class="fas fa-bell text-success mt-1" style="font-size:1.2rem;flex-shrink:0"></i>
    <div>
        <div class="fw-600 text-success">Possible matches found for your reports!</div>
        <div class="text-muted small">Our staff have found items that may belong to you. Please review them below and visit the office to claim.</div>
    </div>
</div>

<!-- My Matches -->
<div class="card shadow-sm mb-4" style="border-radius:1rem;border:1px solid #e2e8f0">
    <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
        <h6 class="mb-0 fw-600"><i class="fas fa-link me-2 text-success"></i>Possible Matches for Your Reports</h6>
    </div>
    <div class="card-body p-0">
        <?php foreach($myMatches as $m): ?>
        <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <?php if($m['photo']): ?>
            <img src="<?= UPLOAD_URL.e($m['photo']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:.75rem;flex-shrink:0" alt="">
            <?php else: ?>
            <div style="width:60px;height:60px;background:#f1f5f9;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-box fa-lg text-muted"></i></div>
            <?php endif; ?>
            <div class="flex-grow-1">
                <div class="fw-600"><?= e($m['found_name']) ?></div>
                <div class="text-muted small">Found: <?= formatDate($m['found_date']) ?> &nbsp;·&nbsp; <?= e($m['found_location']) ?></div>
                <div class="text-muted small">Branch: <?= e($m['branch_name']) ?></div>
                <div class="small mt-1">Your report: <span class="fw-500"><?= e($m['report_name']) ?></span></div>
            </div>
            <div class="text-end flex-shrink-0">
                <span class="badge bg-success mb-2 d-block">Possible Match</span>
                <small class="text-muted d-block">Visit the office<br>to claim this item</small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <!-- My Reports -->
    <div class="col-md-7">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-file-alt me-2 text-primary"></i>My Lost Item Reports</h6>
                <a href="?page=guest-portal" class="btn btn-sm btn-outline-primary" style="font-size:.72rem">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($myReports)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-search fa-3x mb-3 d-block" style="color:#d1d5db"></i>
                    <div class="fw-500">No reports yet</div>
                    <div class="small mb-3">Lost something? Let us help you find it.</div>
                    <a href="?page=guest-portal&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Report a Lost Item</a>
                </div>
                <?php else: foreach($myReports as $r): ?>
                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                    <div style="width:40px;height:40px;background:rgba(14,165,233,.1);border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-search" style="color:#0ea5e9"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <a href="?page=guest-portal&action=view&id=<?= $r['id'] ?>" class="fw-500 text-dark text-decoration-none"><?= e($r['item_name']) ?></a>
                        <div class="text-muted small"><?= e($r['branch_name']) ?> &nbsp;·&nbsp; <?= formatDate($r['lost_date']) ?></div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <?= statusBadge($r['status']) ?>
                        <?php if($r['match_count'] > 0): ?>
                        <div class="mt-1"><span class="badge bg-success" style="font-size:.65rem"><i class="fas fa-link me-1"></i><?= $r['match_count'] ?> match<?= $r['match_count']>1?'es':'' ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Recently Found Items -->
    <div class="col-md-5">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-box-open me-2 text-success"></i>Recently Found Items</h6>
                <small class="text-muted d-block mt-1" style="font-size:.72rem">Does any of these look like yours?</small>
            </div>
            <div class="card-body p-0">
                <?php if(empty($recentFound)): ?>
                <div class="text-center text-muted py-4 small">No unclaimed items at the moment.</div>
                <?php else: foreach($recentFound as $f): ?>
                <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                    <?php if($f['photo']): ?>
                    <img src="<?= UPLOAD_URL.e($f['photo']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;flex-shrink:0" alt="">
                    <?php else: ?>
                    <div style="width:44px;height:44px;background:#f1f5f9;border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-image text-muted fa-sm"></i></div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <div class="fw-500 small text-truncate"><?= e($f['item_name']) ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= e($f['branch_name']) ?> &nbsp;·&nbsp; <?= formatDate($f['found_date']) ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= e($f['category_name'] ?? 'Uncategorized') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="p-3 text-center">
                    <small class="text-muted">Think one of these is yours? <a href="?page=guest-portal&action=create" class="text-primary fw-500">Submit a report</a> and our staff will match it.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card mt-3 shadow-sm" style="border-radius:1rem;background:#fffbeb;border:1.5px solid #fde68a">
            <div class="card-body p-3">
                <div class="fw-600 mb-2" style="color:#92400e"><i class="fas fa-question-circle me-2"></i>How It Works</div>
                <div class="d-flex flex-column gap-2" style="font-size:.8rem;color:#78350f">
                    <div><span class="badge bg-warning text-dark me-2">1</span>Submit a lost item report below</div>
                    <div><span class="badge bg-warning text-dark me-2">2</span>Staff reviews and searches for matches</div>
                    <div><span class="badge bg-warning text-dark me-2">3</span>You get notified of a possible match here</div>
                    <div><span class="badge bg-warning text-dark me-2">4</span>Visit the LabTech Office with your ID to claim</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
