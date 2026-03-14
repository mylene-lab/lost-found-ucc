<?php $pageTitle='Users'; require_once __DIR__.'/../layouts/header.php'; $me=currentUser(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Users</li></ol></nav>
    <a href="?page=users&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add User</a>
</div>
<div class="table-card shadow-sm">
    <div class="card-header"><h6 class="mb-0 fw-600">Users <span class="badge bg-primary ms-1"><?= count($users) ?></span></h6></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($users)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No users found</td></tr>
            <?php else: foreach($users as $u): ?>
            <tr>
                <td class="text-muted small"><?= $u['id'] ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;background:<?= ['superadmin'=>'#fee2e2','branch_manager'=>'#fef3c7','staff'=>'#dbeafe'][$u['role']]??'#f1f5f9' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;color:#374151">
                            <?= strtoupper(substr($u['full_name'],0,1)) ?>
                        </div>
                        <span class="fw-500 small"><?= e($u['full_name']) ?></span>
                    </div>
                </td>
                <td class="small"><?= e($u['email']) ?></td>
                <td><?= statusBadge($u['role']) ?></td>
                <td class="small"><?= e($u['branch_name']??'All Branches') ?></td>
                <td><?= statusBadge($u['status']) ?></td>
                <td class="small text-muted"><?= $u['last_login']?formatDateTime($u['last_login']):'Never' ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="?page=users&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                        <?php if($u['id']!=$me['id']): ?>
                        <a href="?page=users&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Deactivate this user?"><i class="fas fa-ban"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
