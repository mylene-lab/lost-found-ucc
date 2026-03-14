<?php $pageTitle='Claims'; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Claims</li></ol></nav>
    <a href="?page=claims&action=create" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Process Claim</a>
</div>
<div class="table-card shadow-sm">
    <div class="card-header"><h6 class="mb-0 fw-600">Claim Records <span class="badge bg-success ms-1"><?= count($claims) ?></span></h6></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Found Item</th><th>Claimed By</th><th>Contact</th><th>ID Presented</th><th>Branch</th><th>Processed By</th><th>Claim Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php if(empty($claims)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-handshake fa-2x d-block mb-2"></i>No claims yet</td></tr>
            <?php else: foreach($claims as $c): ?>
            <tr>
                <td class="text-muted small"><?= $c['id'] ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if($c['photo']): ?><img src="<?= UPLOAD_URL.e($c['photo']) ?>" class="item-thumb" alt=""><?php else: ?><div class="item-thumb-placeholder"><i class="fas fa-image fa-sm"></i></div><?php endif; ?>
                        <span class="fw-500 small"><?= e($c['found_name']) ?></span>
                    </div>
                </td>
                <td class="fw-500 small"><?= e($c['claimed_by_name']) ?></td>
                <td class="small"><?= e($c['claimed_by_contact']) ?></td>
                <td class="small"><?= e($c['id_presented']?:'-') ?></td>
                <td class="small"><?= e($c['branch_name']) ?></td>
                <td class="small"><?= e($c['processed_by_name']) ?></td>
                <td class="small"><?= formatDateTime($c['claim_date']) ?></td>
                <td><a href="?page=claims&action=view&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
