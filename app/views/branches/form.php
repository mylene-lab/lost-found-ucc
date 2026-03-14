<?php
$isEdit    = isset($branch) && $branch !== null;
$pageTitle = $isEdit ? 'Edit Campus/Branch' : 'Add Campus/Branch';
require_once __DIR__.'/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="?page=branches">Campuses / Branches</a></li>
        <li class="breadcrumb-item active"><?= $pageTitle ?></li>
    </ol></nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm" style="border-radius:1rem;border:1px solid #e2e8f0">
            <div class="card-header bg-white border-bottom" style="border-radius:1rem 1rem 0 0">
                <h6 class="mb-0 fw-600"><i class="fas fa-university me-2 text-success"></i><?= $pageTitle ?></h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="?page=branches&action=<?= $isEdit ? 'update' : 'store' ?>">
                    <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $branch['id'] ?>"><?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-500">Branch / Campus Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            value="<?= e($branch['name'] ?? '') ?>" placeholder="e.g. Main Campus, North Branch">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Address</label>
                        <textarea name="address" class="form-control" rows="2"
                            placeholder="Full address"><?= e($branch['address'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Contact Number</label>
                            <input type="text" name="contact" class="form-control"
                                value="<?= e($branch['contact'] ?? '') ?>" placeholder="+1-555-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= e($branch['email'] ?? '') ?>" placeholder="branch@example.com">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-500">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   <?= ($branch['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update' : 'Create' ?> Branch
                        </button>
                        <a href="?page=branches" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
