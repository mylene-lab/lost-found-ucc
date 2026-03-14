<?php $pageTitle = 'Dashboard'; require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Welcome Banner -->
<div class="alert mb-4 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#16a34a,#15803d);border:none;border-radius:1rem;color:#fff">
    <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-user-shield fa-xl"></i>
    </div>
    <div>
        <div class="fw-700" style="font-size:1.1rem">Welcome back, <?= e($_SESSION['user_name']) ?>!</div>
        <div style="opacity:.85;font-size:.85rem"><i class="fas fa-building me-1"></i><?= e($_SESSION['branch_name']) ?> &nbsp;·&nbsp; <i class="fas fa-id-badge me-1"></i>Staff</div>
    </div>
    <div class="ms-auto text-end d-none d-md-block">
        <div style="opacity:.75;font-size:.78rem">Today</div>
        <div class="fw-600"><?= date('l, M d Y') ?></div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Found Items',   'value'=>$stats['total_found'],     'icon'=>'fa-box-open',        'color'=>'#16a34a','bg'=>'rgba(22,163,74,.1)',   'link'=>'?page=found-items'],
        ['label'=>'Unclaimed Items',      'value'=>$stats['unclaimed'],       'icon'=>'fa-clock',           'color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)', 'link'=>'?page=found-items&status=unclaimed'],
        ['label'=>'Claimed Today',        'value'=>$stats['claimed_today'],   'icon'=>'fa-check-circle',    'color'=>'#0ea5e9','bg'=>'rgba(14,165,233,.1)', 'link'=>'?page=claims'],
        ['label'=>'Open Lost Reports',    'value'=>$stats['open_reports'],    'icon'=>'fa-exclamation-circle','color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)', 'link'=>'?page=lost-reports&status=open'],
        ['label'=>'Pending Matches',      'value'=>$stats['pending_matches'], 'icon'=>'fa-link',            'color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.1)','link'=>'?page=matches'],
        ['label'=>'Items I Logged',       'value'=>$stats['logged_by_me'],    'icon'=>'fa-user-edit',       'color'=>'#166534','bg'=>'rgba(22,101,52,.1)', 'link'=>'?page=found-items'],
    ];
    foreach($cards as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="<?= $c['link'] ?>" class="text-decoration-none">
        <div class="card stat-card h-100 shadow-sm" style="border-radius:.875rem;border:1px solid #e2e8f0;transition:transform .15s,box-shadow .15s" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="card-body p-3">
                <div class="stat-icon mb-2" style="width:38px;height:38px;background:<?= $c['bg'] ?>;border-radius:.625rem;display:flex;align-items:center;justify-content:center">
                    <i class="fas <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>"></i>
                </div>
                <div class="fw-700" style="font-size:1.6rem;color:<?= $c['color'] ?>;line-height:1"><?= number_format($c['value']) ?></div>
                <div class="text-muted mt-1" style="font-size:.75rem"><?= $c['label'] ?></div>
            </div>
        </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-body p-3">
                <div class="fw-600 mb-3 small text-muted text-uppercase" style="letter-spacing:.05em">Quick Actions</div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="?page=found-items&action=create" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Log Found Item</a>
                    <a href="?page=lost-reports&action=create" class="btn btn-danger btn-sm"><i class="fas fa-plus me-1"></i>New Lost Report</a>
                    <a href="?page=claims&action=create" class="btn btn-primary btn-sm"><i class="fas fa-handshake me-1"></i>Process Claim</a>
                    <a href="?page=matches&action=create" class="btn btn-info text-white btn-sm"><i class="fas fa-link me-1"></i>Create Match</a>
                    <a href="?page=found-items&status=unclaimed" class="btn btn-outline-warning btn-sm"><i class="fas fa-clock me-1"></i>View Unclaimed</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Monthly Chart -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-chart-bar me-2 text-success"></i>My Branch — Found vs Claimed (Last 6 Months)</h6>
            </div>
            <div class="card-body p-3">
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Pending Matches -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-link me-2 text-purple" style="color:#8b5cf6"></i>Pending Matches</h6>
                <a href="?page=matches" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($pendingMatches)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>
                    <small>No pending matches!</small>
                </div>
                <?php else: foreach($pendingMatches as $m): ?>
                <div class="d-flex align-items-start gap-2 p-3 border-bottom">
                    <?php if($m['photo']): ?>
                    <img src="<?= UPLOAD_URL.e($m['photo']) ?>" style="width:36px;height:36px;object-fit:cover;border-radius:.5rem;flex-shrink:0" alt="">
                    <?php else: ?>
                    <div style="width:36px;height:36px;background:#f1f5f9;border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-box fa-sm text-muted"></i></div>
                    <?php endif; ?>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-500 small text-truncate"><?= e($m['found_name']) ?></div>
                        <div class="text-muted" style="font-size:.72rem">↔ <?= e($m['report_name']) ?></div>
                        <div class="d-flex gap-1 mt-1">
                            <a href="?page=matches&action=confirm&id=<?= $m['id'] ?>" class="btn btn-success btn-sm py-0 px-2" style="font-size:.7rem" data-confirm="Confirm this match?"><i class="fas fa-check"></i></a>
                            <a href="?page=matches&action=reject&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm py-0 px-2" style="font-size:.7rem" data-confirm="Reject this match?"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Items I Logged -->
    <div class="col-md-6">
        <div class="table-card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0;overflow:hidden">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-user-edit me-2 text-success"></i>Items I Logged</h6>
                <a href="?page=found-items" class="btn btn-sm btn-outline-primary" style="font-size:.72rem">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Item</th><th>Category</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if(empty($myItems)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No items logged yet</td></tr>
                    <?php else: foreach($myItems as $i): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if($i['photo']): ?>
                                <img src="<?= UPLOAD_URL.e($i['photo']) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:.4rem" alt="">
                                <?php else: ?>
                                <div style="width:32px;height:32px;background:#f1f5f9;border-radius:.4rem;display:flex;align-items:center;justify-content:center"><i class="fas fa-image fa-xs text-muted"></i></div>
                                <?php endif; ?>
                                <a href="?page=found-items&action=view&id=<?= $i['id'] ?>" class="fw-500 small text-dark text-decoration-none"><?= e($i['item_name']) ?></a>
                            </div>
                        </td>
                        <td class="small text-muted"><?= e($i['category_name'] ?? '—') ?></td>
                        <td class="small"><?= formatDate($i['found_date']) ?></td>
                        <td><?= statusBadge($i['status']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Open Lost Reports -->
    <div class="col-md-6">
        <div class="table-card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0;overflow:hidden">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Open Lost Reports</h6>
                <a href="?page=lost-reports&status=open" class="btn btn-sm btn-outline-danger" style="font-size:.72rem">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Item</th><th>Reporter</th><th>Lost Date</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if(empty($recentReports)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-smile fa-2x d-block mb-2 text-success"></i>No open reports!</td></tr>
                    <?php else: foreach($recentReports as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-500 small"><?= e($r['item_name']) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= e($r['category_name'] ?? 'Uncategorized') ?></div>
                        </td>
                        <td class="small"><?= e($r['reporter_name']) ?></td>
                        <td class="small"><?= formatDate($r['lost_date']) ?></td>
                        <td>
                            <a href="?page=lost-reports&action=view&id=<?= $r['id'] ?>" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:.15rem .4rem"><i class="fas fa-eye"></i></a>
                            <a href="?page=matches&action=create" class="btn btn-xs btn-outline-info" style="font-size:.7rem;padding:.15rem .4rem"><i class="fas fa-link"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$trendLabels  = json_encode(array_values(array_column($monthlyTrend, 'label')))   ?: '[]';
$trendFound   = json_encode(array_values(array_column($monthlyTrend, 'found')))   ?: '[]';
$trendClaimed = json_encode(array_values(array_column($monthlyTrend, 'claimed'))) ?: '[]';
ob_start(); ?>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: <?= $trendLabels ?>,
        datasets: [
            { label: 'Found',   data: <?= $trendFound ?>,   backgroundColor: 'rgba(22,163,74,.75)',  borderRadius: 5 },
            { label: 'Claimed', data: <?= $trendClaimed ?>, backgroundColor: 'rgba(14,165,233,.75)', borderRadius: 5 }
        ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});
</script>
<?php
$extraJs = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php';
?>
