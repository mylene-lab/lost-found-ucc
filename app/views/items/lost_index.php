<?php $pageTitle='Lost Reports'; require_once __DIR__.'/../layouts/header.php'; $user=currentUser(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Lost Reports</li></ol></nav>
    <div class="d-flex gap-2">
        <a href="?page=lost-reports&guest=1" class="btn btn-warning btn-sm <?= isset($_GET['guest'])?'active':'' ?>">
            <i class="fas fa-user me-1"></i>Guest Reports
            <?php $gc=getDB()->query("SELECT COUNT(*) c FROM lost_reports r JOIN users u ON u.id=r.logged_by WHERE u.role='guest' AND r.status='open'")->fetch_assoc()['c']; if($gc): ?><span class="badge bg-dark ms-1"><?= $gc ?></span><?php endif; ?>
        </a>
        <a href="?page=lost-reports&action=create" class="btn btn-danger btn-sm"><i class="fas fa-plus me-1"></i>New Lost Report</a>
    </div>
</div>

<div class="form-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="page" value="lost-reports">
        <div class="col-md-3"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search item, reporter..." value="<?= e($_GET['q']??'') ?>"></div>
        <?php if($user['role']==='superadmin'): ?>
        <div class="col-md-2">
            <select name="branch_id" class="form-select form-select-sm"><option value="">All Branches</option>
            <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= ($_GET['branch_id']??'')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm"><option value="">All Status</option>
            <?php foreach(['open','matched','closed'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="period" class="form-select form-select-sm" onchange="applyPeriod(this.value)">
                <option value="">All Time</option>
                <option value="this_week" <?= ($_GET['period']??'')==='this_week'?'selected':'' ?>>This Week</option>
                <option value="this_month" <?= ($_GET['period']??'')==='this_month'?'selected':'' ?>>This Month</option>
                <option value="last_month" <?= ($_GET['period']??'')==='last_month'?'selected':'' ?>>Last Month</option>
            </select>
        </div>
        <div class="col-auto d-flex gap-1">
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from']??'') ?>" placeholder="From">
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($_GET['date_to']??'') ?>" placeholder="To">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-search"></i></button>
            <a href="?page=lost-reports" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

<div class="table-card shadow-sm">
    <div class="card-header"><h6 class="mb-0 fw-600">Lost Reports <span class="badge bg-danger ms-1"><?= number_format($total) ?></span></h6></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Item</th><th>Reporter</th><th>Contact</th>
            <?php if($user['role']==='superadmin'): ?><th>Branch</th><?php endif; ?>
            <th>Lost Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($reports)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No lost reports yet</td></tr>
            <?php else: $rowNum = ($page-1)*$perPage + 1; foreach($reports as $r): ?>
            <tr>
                <td class="text-muted small fw-600"><?= $rowNum++ ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                    <?php if(!empty($r['photo'])): ?>
                    <img src="<?= UPLOAD_URL . e($r['photo']) ?>" class="item-thumb" alt="photo">
                    <?php else: ?>
                    <div class="item-thumb-placeholder"><i class="fas fa-question"></i></div>
                    <?php endif; ?>
                    <div class="fw-500"><?= e($r['item_name']) ?></div>
                    </div>
                    <small class="text-muted"><?= e($r['category_name']??'—') ?></small>
                </td>
                <td class="small"><?= e($r['reporter_name']) ?> <?= ($r['logged_by_role']??'')==='guest'?'<span class="badge bg-success" style="font-size:.65rem">Guest</span>':'' ?></td>
                <td class="small"><?= e($r['reporter_contact']) ?></td>
                <?php if($user['role']==='superadmin'): ?><td class="small"><?= e($r['branch_name']) ?></td><?php endif; ?>
                <td class="small"><?= formatDate($r['lost_date']) ?></td>
                <td><?= statusBadge($r['status']) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="?page=lost-reports&action=view&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        <a href="?page=lost-reports&action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                        <a href="?page=lost-reports&action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this report?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($pagination): ?><div class="card-footer bg-white d-flex justify-content-end"><?= $pagination ?></div><?php endif; ?>
</div>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
<script>
function applyPeriod(val) {
    const today = new Date();
    let from = '', to = '';
    if (val === 'this_week') {
        const day = today.getDay(), diff = today.getDate() - day + (day===0?-6:1);
        from = new Date(today.setDate(diff)).toISOString().slice(0,10);
        to = new Date(new Date().setDate(new Date(from).getDate()+6)).toISOString().slice(0,10);
    } else if (val === 'this_month') {
        from = today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-01';
        to = new Date(today.getFullYear(), today.getMonth()+1, 0).toISOString().slice(0,10);
    } else if (val === 'last_month') {
        const lm = new Date(today.getFullYear(), today.getMonth()-1, 1);
        from = lm.toISOString().slice(0,10);
        to = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().slice(0,10);
    }
    if (from) { document.querySelector('[name=date_from]').value = from; document.querySelector('[name=date_to]').value = to; }
}
</script>
