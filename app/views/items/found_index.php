<?php $pageTitle='Found Items'; require_once __DIR__.'/../layouts/header.php'; $user=currentUser(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Found Items</li></ol></nav>
    <a href="?page=found-items&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Log Found Item</a>
</div>

<!-- Filters -->
<div class="form-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="page" value="found-items">
        <div class="col-md-3">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search item name, brand..." value="<?= e($_GET['q']??'') ?>">
        </div>
        <?php if($user['role']==='superadmin'): ?>
        <div class="col-md-2">
            <select name="branch_id" class="form-select form-select-sm">
                <option value="">All Branches</option>
                <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= ($_GET['branch_id']??'')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <?php foreach(['unclaimed','matched','claimed','disposed'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
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
        <div class="col-md-1"><input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from']??'') ?>"></div>
        <div class="col-md-1"><input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($_GET['date_to']??'') ?>"></div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            <a href="?page=found-items" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

<div class="table-card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-600">Found Items <span class="badge bg-primary ms-1"><?= number_format($total) ?></span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th><th>Photo</th><th>Item</th><th>Category</th>
                    <?php if($user['role']==='superadmin'): ?><th>Branch</th><?php endif; ?>
                    <th>Found Date</th><th>Location</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($items)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No found items recorded</td></tr>
            <?php else: $rowNum = ($page-1)*$perPage + 1; foreach($items as $i): ?>
            <tr>
                <td class="text-muted small fw-600"><?= $rowNum++ ?></td>
                <td>
                    <?php if($i['photo']): ?>
                    <img src="<?= UPLOAD_URL.e($i['photo']) ?>" class="item-thumb" alt="">
                    <?php else: ?><div class="item-thumb-placeholder"><i class="fas fa-image fa-sm"></i></div><?php endif; ?>
                </td>
                <td>
                    <div class="fw-500"><?= e($i['item_name']) ?></div>
                    <?php if($i['brand']): ?><small class="text-muted"><?= e($i['brand']) ?></small><?php endif; ?>
                </td>
                <td><small><?= e($i['category_name']??'—') ?></small></td>
                <?php if($user['role']==='superadmin'): ?><td><small><?= e($i['branch_name']) ?></small></td><?php endif; ?>
                <td><small><?= formatDate($i['found_date']) ?></small></td>
                <td><small><?= e($i['found_location']?:'—') ?></small></td>
                <td><?= statusBadge($i['status']) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="?page=found-items&action=view&id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                        <a href="?page=found-items&action=edit&id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="?page=found-items&action=delete&id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="Delete this item?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($pagination): ?>
    <div class="card-footer bg-white d-flex justify-content-end"><?= $pagination ?></div>
    <?php endif; ?>
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
