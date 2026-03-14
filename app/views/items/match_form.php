<?php $pageTitle='Create Match'; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="?page=matches">Matches</a></li><li class="breadcrumb-item active">Create Match</li></ol></nav>
</div>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-link me-2 text-info"></i>Match Found Item with Lost Report</h6>
            <form method="POST" action="?page=matches&action=store">
                <div class="mb-3">
                    <label class="form-label fw-500">Found Item <span class="text-danger">*</span></label>
                    <select name="found_item_id" class="form-select" required id="foundSelect">
                        <option value="">-- Select Found Item --</option>
                        <?php foreach($foundItems as $f): ?>
                        <option value="<?= $f['id'] ?>" data-branch="<?= e($f['branch_name']) ?>">
                            #<?= $f['id'] ?> - <?= e($f['item_name']) ?> (<?= e($f['branch_name']) ?>) [<?= formatDate($f['found_date']) ?>]
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(empty($foundItems)): ?><small class="text-danger">No unclaimed found items available.</small><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Lost Report <span class="text-danger">*</span></label>
                    <select name="lost_report_id" class="form-select" required>
                        <option value="">-- Select Lost Report --</option>
                        <?php foreach($lostReports as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            #<?= $r['id'] ?> - <?= e($r['item_name']) ?> | Reporter: <?= e($r['reporter_name']) ?> [<?= formatDate($r['lost_date']) ?>]
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(empty($lostReports)): ?><small class="text-danger">No open lost reports available.</small><?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Describe why these items match..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-info text-white" <?= (empty($foundItems)||empty($lostReports))?'disabled':'' ?>>
                        <i class="fas fa-link me-1"></i>Create Match
                    </button>
                    <a href="?page=matches" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
