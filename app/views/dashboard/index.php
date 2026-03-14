<?php $pageTitle='Dashboard'; require_once __DIR__.'/../layouts/header.php'; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <?php
    $cards=[
        ['label'=>'Total Found','value'=>$stats['total_found'],'icon'=>'fa-box-open','color'=>'#16a34a','bg'=>'rgba(22,163,74,.1)'],
        ['label'=>'Unclaimed Items','value'=>$stats['unclaimed'],'icon'=>'fa-clock','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)'],
        ['label'=>'Items Claimed','value'=>$stats['claimed'],'icon'=>'fa-check-circle','color'=>'#15803d','bg'=>'rgba(21,128,61,.1)'],
        ['label'=>'Open Lost Reports','value'=>$stats['open_reports'],'icon'=>'fa-exclamation-circle','color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
        ['label'=>'Total Lost Reports','value'=>$stats['total_reports'],'icon'=>'fa-file-alt','color'=>'#16a34a','bg'=>'rgba(22,163,74,.08)'],
        ['label'=>'Pending Matches','value'=>$stats['pending_matches'],'icon'=>'fa-link','color'=>'#166534','bg'=>'rgba(22,101,52,.1)'],
    ];
    foreach($cards as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card h-100 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stat-icon" style="background:<?= $c['bg'] ?>">
                        <i class="fas <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>"></i>
                    </div>
                </div>
                <div class="fw-700" style="font-size:1.6rem;color:<?= $c['color'] ?>;line-height:1"><?= number_format($c['value']) ?></div>
                <div class="text-muted mt-1" style="font-size:.78rem"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Trend (Last 6 Months)</h6>
            </div>
            <div class="card-body p-3">
                <canvas id="trendChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-tags me-2 text-warning"></i>By Category</h6>
            </div>
            <div class="card-body p-3 d-flex align-items-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($branchStats)): ?>
<!-- Branch Stats (Super Admin) -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-building me-2 text-info"></i>Branch Overview</h6>
            </div>
            <div class="card-body p-3">
                <canvas id="branchChart" height="60"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Items Tables -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600"><i class="fas fa-box-open me-2 text-success"></i>Recent Found Items</h6>
                <a href="?page=found-items" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Item</th><th>Branch</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if(empty($recentFound)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No items yet</td></tr>
                    <?php else: foreach($recentFound as $i): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if($i['photo']): ?>
                                <img src="<?= UPLOAD_URL.e($i['photo']) ?>" class="item-thumb" alt="">
                                <?php else: ?>
                                <div class="item-thumb-placeholder"><i class="fas fa-image fa-sm"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-500 small"><?= e($i['item_name']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem"><?= e($i['category_name']??'Uncategorized') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="small"><?= e($i['branch_name']) ?></td>
                        <td class="small"><?= formatDate($i['found_date']) ?></td>
                        <td><?= statusBadge($i['status']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="table-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Recent Lost Reports</h6>
                <a href="?page=lost-reports" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Item</th><th>Reporter</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if(empty($recentReports)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No reports yet</td></tr>
                    <?php else: foreach($recentReports as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-500 small"><?= e($r['item_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= e($r['category_name']??'Uncategorized') ?></div>
                        </td>
                        <td class="small"><?= e($r['reporter_name']) ?></td>
                        <td class="small"><?= formatDate($r['lost_date']) ?></td>
                        <td><?= statusBadge($r['status']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$trendLabels     = json_encode(array_values(array_column($monthlyTrend, 'label')))    ?: '[]';
$trendFound      = json_encode(array_values(array_column($monthlyTrend, 'found')))    ?: '[]';
$trendReports    = json_encode(array_values(array_column($monthlyTrend, 'reports')))  ?: '[]';
$catLabels       = json_encode(array_values(array_column($categoryData, 'name')))     ?: '[]';
$catValues       = json_encode(array_values(array_column($categoryData, 'total')))    ?: '[]';
$branchLabels    = json_encode(array_values(array_column($branchStats,  'name')))     ?: '[]';
$branchUnclaimed = json_encode(array_values(array_column($branchStats,  'unclaimed')))?: '[]';
$branchClaimed   = json_encode(array_values(array_column($branchStats,  'claimed')))  ?: '[]';

ob_start(); ?>
<script>
// Trend Chart
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= $trendLabels ?>,
        datasets: [
            { label: 'Found Items',  data: <?= $trendFound ?>,   borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.08)',  tension:.4, fill:true, pointRadius:4, pointBackgroundColor:'#16a34a' },
            { label: 'Lost Reports', data: <?= $trendReports ?>, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.08)', tension:.4, fill:true, pointRadius:4, pointBackgroundColor:'#ef4444' }
        ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});

// Category Doughnut
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $catLabels ?>,
        datasets: [{ data: <?= $catValues ?>, backgroundColor: ['#16a34a','#15803d','#166534','#4ade80','#86efac','#bbf7d0','#22c55e','#14532d'], borderWidth:2, borderColor:'#fff' }]
    },
    options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } } }
});

<?php if (!empty($branchStats)): ?>
// Branch Chart
new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: {
        labels: <?= $branchLabels ?>,
        datasets: [
            { label: 'Unclaimed', data: <?= $branchUnclaimed ?>, backgroundColor: 'rgba(245,158,11,.8)', borderRadius:4 },
            { label: 'Claimed',   data: <?= $branchClaimed ?>,   backgroundColor: 'rgba(22,163,74,.8)',  borderRadius:4 }
        ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});
<?php endif; ?>
</script>
<?php
$extraJs = ob_get_clean();
require_once __DIR__.'/../layouts/footer.php';
?>
