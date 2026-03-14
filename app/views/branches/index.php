<?php $pageTitle='Branches'; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Branches</li></ol></nav>
    <a href="?page=branches&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Branch</a>
</div>
<div class="row g-3">
    <?php if(empty($branches)): ?>
    <div class="col-12"><div class="table-card p-4 text-center text-muted">No branches found.</div></div>
    <?php else: foreach($branches as $b): ?>
    <div class="col-md-4">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div style="background:#ede9fe;width:40px;height:40px;border-radius:.625rem;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-building text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-600"><?= e($b['name']) ?></div>
                            <?= statusBadge($b['status']) ?>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="?page=branches&action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                        <a href="?page=branches&action=delete&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Deactivate this branch?"><i class="fas fa-ban"></i></a>
                    </div>
                </div>
                <?php if($b['address']): ?><div class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= e($b['address']) ?></div><?php endif; ?>
                <?php if($b['contact']): ?><div class="small text-muted mb-1"><i class="fas fa-phone me-1"></i><?= e($b['contact']) ?></div><?php endif; ?>
                <?php if($b['email']): ?><div class="small text-muted mb-2"><i class="fas fa-envelope me-1"></i><?= e($b['email']) ?></div><?php endif; ?>
                <div class="d-flex gap-3 mt-2 pt-2 border-top">
                    <div class="text-center"><div class="fw-600 text-primary"><?= $b['user_count'] ?></div><div class="text-muted" style="font-size:.7rem">Users</div></div>
                    <div class="text-center"><div class="fw-600 text-success"><?= $b['found_count'] ?></div><div class="text-muted" style="font-size:.7rem">Found Items</div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
