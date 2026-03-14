<?php $pageTitle='Lost Report #'.$report['id']; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="?page=lost-reports">Lost Reports</a></li><li class="breadcrumb-item active">#<?= $report['id'] ?></li></ol></nav>
    <div class="d-flex gap-2">
        <a href="?page=lost-reports&action=edit&id=<?= $report['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="?page=matches&action=create" class="btn btn-sm btn-info text-white"><i class="fas fa-link me-1"></i>Find Match</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="form-card text-center mb-3">
            <?php if(!empty($report['photo'])): ?>
            <img src="<?= UPLOAD_URL . e($report['photo']) ?>" alt="Lost item photo"
                 style="width:100%;height:180px;object-fit:cover;border-radius:.75rem;">
            <?php else: ?>
            <div style="background:#fff3f3;height:140px;border-radius:.75rem;display:flex;align-items:center;justify-content:center">
                <div><i class="fas fa-exclamation-circle fa-3x text-danger d-block mb-1"></i><small class="text-muted">Lost Item</small></div>
            </div>
            <?php endif; ?>
            <div class="mt-3"><?= statusBadge($report['status']) ?></div>
            <h5 class="mt-2 mb-0"><?= e($report['item_name']) ?></h5>
            <small class="text-muted"><?= e($report['category_name']??'Uncategorized') ?></small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-card mb-3">
            <h6 class="fw-600 border-bottom pb-2 mb-3">Report Details</h6>
            <div class="row g-2">
                <?php $details=[
                    ['Reporter',$report['reporter_name']],['Contact',$report['reporter_contact']],
                    ['Email',$report['reporter_email']],['Brand',$report['brand']],
                    ['Color',$report['color']],['Lost Date',formatDate($report['lost_date'])],
                    ['Lost Location',$report['lost_location']],['Branch',$report['branch_name']],
                    ['Logged By',$report['logged_by_name']],['Logged At',formatDateTime($report['created_at'])],
                ]; foreach($details as [$l,$v]): ?>
                <div class="col-md-6"><div class="text-muted small"><?= $l ?></div><div class="fw-500 small"><?= e($v?:'-') ?></div></div>
                <?php endforeach; ?>
                <?php if($report['description']): ?><div class="col-12"><div class="text-muted small">Description</div><div class="small"><?= nl2br(e($report['description'])) ?></div></div><?php endif; ?>
            </div>
        </div>
        <?php if(!empty($matches)): ?>
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-link me-2 text-info"></i>Match History</h6>
            <?php foreach($matches as $m): ?>
            <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                <div>
                    <div class="fw-500 small"><?= e($m['found_name']) ?> <?= $m['photo']?"<img src='".UPLOAD_URL.e($m['photo'])."' style='width:30px;height:30px;object-fit:cover;border-radius:4px'>":'' ?></div>
                    <div class="text-muted small">Found: <?= formatDate($m['found_date']) ?> at <?= e($m['found_location']) ?></div>
                    <div class="text-muted small">Matched by <?= e($m['matched_by_name']) ?></div>
                </div>
                <?= statusBadge($m['status']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
