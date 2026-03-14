<?php $pageTitle='Claim #'.$claim['id']; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="?page=claims">Claims</a></li><li class="breadcrumb-item active">#<?= $claim['id'] ?></li></ol></nav>
</div>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="form-card">
            <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:#f0fdf4;border-radius:.75rem">
                <?php if($claim['photo']): ?><img src="<?= UPLOAD_URL.e($claim['photo']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:.5rem" alt=""><?php endif; ?>
                <div>
                    <h6 class="mb-0"><?= e($claim['found_name']) ?></h6>
                    <small class="text-muted"><?= e($claim['branch_name']) ?> · Found <?= formatDate($claim['found_date']) ?></small>
                </div>
                <span class="ms-auto badge bg-success">Claimed</span>
            </div>
            <div class="row g-3">
                <?php $details=[
                    ['Claimed By',$claim['claimed_by_name']],['Contact',$claim['claimed_by_contact']],
                    ['Email',$claim['claimed_by_email']],['ID Presented',$claim['id_presented']],
                    ['Processed By',$claim['processed_by_name']],['Claim Date',formatDateTime($claim['claim_date'])],
                ]; foreach($details as [$l,$v]): ?>
                <div class="col-md-6"><div class="text-muted small"><?= $l ?></div><div class="fw-500"><?= e($v?:'-') ?></div></div>
                <?php endforeach; ?>
                <?php if($claim['notes']): ?><div class="col-12"><div class="text-muted small">Notes</div><div><?= nl2br(e($claim['notes'])) ?></div></div><?php endif; ?>
            </div>
            <div class="mt-3"><a href="?page=claims" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div>
        </div>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
