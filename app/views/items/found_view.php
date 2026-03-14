<?php $pageTitle = 'Found Item #'.$item['id']; require_once __DIR__.'/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="?page=found-items">Found Items</a></li>
        <li class="breadcrumb-item active">#<?= $item['id'] ?></li>
    </ol></nav>
    <div class="d-flex gap-2">
        <a href="?page=found-items&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="?page=matches&action=create" class="btn btn-sm btn-info text-white"><i class="fas fa-link me-1"></i>Match</a>
        <a href="?page=claims&action=create" class="btn btn-sm btn-success"><i class="fas fa-handshake me-1"></i>Process Claim</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="form-card text-center mb-3">
            <?php if($item['photo']): ?>
            <img src="<?= UPLOAD_URL.e($item['photo']) ?>" class="img-fluid rounded" style="max-height:250px;object-fit:cover;width:100%">
            <?php else: ?>
            <div style="background:#f1f5f9;height:200px;border-radius:.75rem;display:flex;align-items:center;justify-content:center;color:#94a3b8">
                <div><i class="fas fa-image fa-3x d-block mb-2"></i><small>No photo</small></div>
            </div>
            <?php endif; ?>
            <div class="mt-3"><?= statusBadge($item['status']) ?></div>
            <h5 class="mt-2 mb-0"><?= e($item['item_name']) ?></h5>
            <small class="text-muted"><?= e($item['category_name']??'Uncategorized') ?></small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-card mb-3">
            <h6 class="fw-600 border-bottom pb-2 mb-3">Item Details</h6>
            <div class="row g-2">
                <?php $details=[
                    ['Brand/Make',$item['brand']],['Color',$item['color']],
                    ['Found Date',formatDate($item['found_date'])],['Found Location',$item['found_location']],
                    ['Storage Location',$item['storage_location']],['Branch',$item['branch_name']],
                    ['Logged By',$item['logged_by_name']],['Logged At',formatDateTime($item['created_at'])],
                ]; foreach($details as [$label,$val]): ?>
                <div class="col-md-6">
                    <div class="text-muted small"><?= $label ?></div>
                    <div class="fw-500 small"><?= e($val?:'-') ?></div>
                </div>
                <?php endforeach; ?>
                <?php if($item['description']): ?>
                <div class="col-12 mt-2">
                    <div class="text-muted small">Description</div>
                    <div class="small"><?= nl2br(e($item['description'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if($item['notes']): ?>
                <div class="col-12">
                    <div class="text-muted small">Notes</div>
                    <div class="small"><?= nl2br(e($item['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!empty($matches)): ?>
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-link me-2 text-info"></i>Match History</h6>
            <?php foreach($matches as $m): ?>
            <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                <div>
                    <div class="fw-500 small"><?= e($m['report_name']) ?></div>
                    <div class="text-muted small">Reporter: <?= e($m['reporter_name']) ?> · <?= e($m['reporter_contact']) ?></div>
                    <div class="text-muted small">Matched by <?= e($m['matched_by_name']) ?> on <?= formatDateTime($m['match_date']) ?></div>
                </div>
                <?= statusBadge($m['status']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
