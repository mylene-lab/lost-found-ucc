<?php $isEdit=isset($user); $pageTitle=$isEdit?'Edit User':'Add User'; require_once __DIR__.'/../layouts/header.php'; $me=currentUser(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="?page=users">Users</a></li><li class="breadcrumb-item active"><?= $pageTitle ?></li></ol></nav>
</div>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-user me-2 text-primary"></i><?= $pageTitle ?></h6>
            <form method="POST" action="?page=users&action=<?= $isEdit?'update':'store' ?>">
                <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $user['id'] ?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="full_name" class="form-control" required value="<?= e($user['full_name']??'') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required value="<?= e($user['email']??'') ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Password <?= $isEdit?'<small class="text-muted">(leave blank to keep)</small>':'' ?> <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" <?= $isEdit?'':'required' ?> minlength="6" placeholder="Min 6 characters">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <?php if($me['role']==='superadmin'): ?><option value="superadmin" <?= ($user['role']??'')==='superadmin'?'selected':'' ?>>Super Admin</option><?php endif; ?>
                            <option value="branch_manager" <?= ($user['role']??'')==='branch_manager'?'selected':'' ?>>Branch Manager</option>
                            <option value="staff" <?= ($user['role']??'staff')==='staff'?'selected':'' ?>>Staff</option>
                            <option value="guest" <?= ($user['role']??'')==='guest'?'selected':'' ?>>Guest</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <?php if($me['role']==='superadmin'): ?><option value="">All Branches (Super Admin)</option><?php endif; ?>
                            <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= ($user['branch_id']??'')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($user['status']??'active')==='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= ($user['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit?'Update User':'Create User' ?></button>
                    <a href="?page=users" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
