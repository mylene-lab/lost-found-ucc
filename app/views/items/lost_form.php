<?php $isEdit=isset($report); $pageTitle=$isEdit?'Edit Lost Report':'New Lost Report'; require_once __DIR__.'/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="?page=lost-reports">Lost Reports</a></li>
        <li class="breadcrumb-item active"><?= $pageTitle ?></li>
    </ol></nav>
</div>

<form method="POST" action="?page=lost-reports&action=<?= $isEdit?'update':'store' ?>">
    <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $report['id'] ?>"><?php endif; ?>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-user me-2 text-primary"></i>Reporter Information</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Reporter Name <span class="text-danger">*</span></label>
                        <input type="text" name="reporter_name" class="form-control" required value="<?= e($report['reporter_name']??'') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="reporter_contact" class="form-control" required value="<?= e($report['reporter_contact']??'') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="reporter_email" class="form-control" value="<?= e($report['reporter_email']??'') ?>">
                    </div>
                </div>
            </div>
            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Lost Item Details</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required value="<?= e($report['item_name']??'') ?>" placeholder="e.g. Black Leather Wallet...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($report['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" value="<?= e($report['brand']??'') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Color</label><input type="text" name="color" class="form-control" value="<?= e($report['color']??'') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Lost Date <span class="text-danger">*</span></label><input type="date" name="lost_date" class="form-control" required value="<?= e($report['lost_date']??date('Y-m-d')) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Lost Location</label><input type="text" name="lost_location" class="form-control" value="<?= e($report['lost_location']??'') ?>"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($report['description']??'') ?></textarea></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= e($report['notes']??'') ?></textarea></div>
                    <?php if($isEdit): ?>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach(['open','matched','closed'] as $s): ?><option value="<?= $s ?>" <?= $report['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-building me-2 text-info"></i>Branch</h6>
                <select name="branch_id" class="form-select" required>
                    <option value="">Select Branch</option>
                    <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= ($report['branch_id']??$_SESSION['branch_id'])==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i><?= $isEdit?'Update Report':'Submit Report' ?></button>
                <a href="?page=lost-reports" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
