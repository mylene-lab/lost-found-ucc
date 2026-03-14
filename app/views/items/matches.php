<?php $pageTitle='Item Matches'; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Item Matches</li></ol></nav>
    <div class="d-flex gap-2">
        <a href="?page=matches&action=auto-run" class="btn btn-warning btn-sm" data-confirm="Run auto-matching on all open items?">
            <i class="fas fa-robot me-1"></i>Run Auto-Match
        </a>
        <a href="?page=matches&action=create" class="btn btn-info text-white btn-sm"><i class="fas fa-plus me-1"></i>Create Match</a>
    </div>
</div>
<div class="table-card shadow-sm">
    <div class="card-header"><h6 class="mb-0 fw-600">All Matches <span class="badge bg-info text-white ms-1"><?= count($matches) ?></span></h6></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Found Item</th><th>Lost Report</th><th>Reporter</th><th>Branch</th><th>Matched By</th><th>Score</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($matches)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-link fa-2x d-block mb-2"></i>No matches yet</td></tr>
            <?php else: foreach($matches as $m): ?>
            <tr>
                <td class="text-muted small"><?= $m['id'] ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if($m['photo']): ?><img src="<?= UPLOAD_URL.e($m['photo']) ?>" class="item-thumb" alt=""><?php else: ?><div class="item-thumb-placeholder"><i class="fas fa-image fa-sm"></i></div><?php endif; ?>
                        <div class="fw-500 small"><?= e($m['found_name']) ?></div>
                    </div>
                </td>
                <td class="small fw-500"><?= e($m['report_name']) ?></td>
                <td class="small"><?= e($m['reporter_name']) ?><br><small class="text-muted"><?= e($m['reporter_contact']) ?></small></td>
                <td class="small"><?= e($m['branch_name']) ?></td>
                <td class="small"><?= e($m['matched_by_name']) ?><?php if(!empty($m['auto_matched'])): ?><span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">AUTO</span><?php endif; ?></td>
                <td class="text-center">
                    <?php if(!empty($m['match_score'])): ?>
                    <?php $sc=$m['match_score']; $cl=$sc>=90?'success':($sc>=60?'warning':'danger'); ?>
                    <div class="d-flex align-items-center gap-1">
                        <div style="width:40px;background:#e2e8f0;border-radius:4px;height:6px">
                            <div style="width:<?= $sc ?>%;background:var(--bs-<?= $cl ?>);height:6px;border-radius:4px"></div>
                        </div>
                        <span class="badge bg-<?= $cl ?>" style="font-size:.7rem"><?= $sc ?>%</span>
                    </div>
                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                </td>
                <td class="small"><?= formatDateTime($m['match_date']) ?></td>
                <td><?= statusBadge($m['status']) ?></td>
                <td>
                    <?php if($m['status']==='pending'): ?>
                    <div class="d-flex gap-1">
                        <a href="?page=matches&action=confirm&id=<?= $m['id'] ?>" class="btn btn-sm btn-success" data-confirm="Confirm this match?"><i class="fas fa-check"></i></a>
                        <a href="?page=matches&action=reject&id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Reject this match?"><i class="fas fa-times"></i></a>
                    </div>
                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
