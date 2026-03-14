<?php $pageTitle='Process Claim'; require_once __DIR__.'/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="?page=claims">Claims</a></li><li class="breadcrumb-item active">Process Claim</li></ol></nav>
</div>

<!-- Proof Requirements Notice -->
<div class="alert alert-info border-0 mb-3" style="background:#eff6ff;border-left:4px solid #3b82f6!important;border-radius:.75rem">
    <div class="fw-600 mb-1"><i class="fas fa-shield-alt me-2 text-primary"></i>Proof of Ownership Required</div>
    <div class="small text-muted">Before processing, ensure the claimant presents at least one of the following:
        <span class="fw-500 text-dark"> Valid Government ID · Student/Employee ID · Receipt or Purchase Proof · Serial Number · Photo Evidence · Unique Description Match</span>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-handshake me-2 text-success"></i>Process Item Claim</h6>
            <form method="POST" action="?page=claims&action=store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-500">Found Item <span class="text-danger">*</span></label>
                        <select name="found_item_id" class="form-select" required>
                            <option value="">-- Select Found Item --</option>
                            <?php foreach($foundItems as $f): ?><option value="<?= $f['id'] ?>">#<?= $f['id'] ?> - <?= e($f['item_name']) ?> (<?= e($f['branch_name']) ?>)</option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link to Lost Report <span class="text-muted small">(optional)</span></label>
                        <select name="lost_report_id" class="form-select">
                            <option value="">-- None / Walk-in Claim --</option>
                            <?php foreach($lostReports as $r): ?><option value="<?= $r['id'] ?>">#<?= $r['id'] ?> - <?= e($r['item_name']) ?> (<?= e($r['reporter_name']) ?>)</option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-1"><div class="fw-600 small text-muted mb-1"><i class="fas fa-user me-1"></i>CLAIMANT INFORMATION</div></div>
                    <div class="col-md-4"><label class="form-label">Claimant Name <span class="text-danger">*</span></label><input type="text" name="claimed_by_name" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Contact <span class="text-danger">*</span></label><input type="text" name="claimed_by_contact" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="claimed_by_email" class="form-control"></div>
                    <div class="col-md-6">
                        <label class="form-label">ID Presented <span class="text-danger">*</span></label>
                        <input type="text" name="id_presented" class="form-control" required placeholder="e.g. Driver's License No. 12345, Student ID...">
                        <div class="form-text">Must present valid ID before releasing item.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Proof of Ownership</label>
                        <select name="proof_type" class="form-select">
                            <option value="">-- Select Proof Type --</option>
                            <option value="photo_evidence">Photo Evidence</option>
                            <option value="serial_number">Serial/Model Number Match</option>
                            <option value="receipt">Receipt / Purchase Proof</option>
                            <option value="unique_description">Unique Description Match</option>
                            <option value="witness">Witness / Referral</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-1"><div class="fw-600 small text-muted mb-1"><i class="fas fa-question-circle me-1"></i>VERIFICATION QUESTIONS <span class="text-muted fw-400">(ask claimant to confirm)</span></div></div>

                    <!-- Verification Checklist -->
                    <div class="col-12">
                        <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="small fw-600 mb-2 text-dark">Staff must ask and record claimant answers:</div>
                            <div class="row g-2">
                                <?php $verificationQs = [
                                    'q_color_desc'   => 'What is the exact color and appearance of the item?',
                                    'q_location'     => 'Where exactly did you lose the item?',
                                    'q_date'         => 'When approximately did you lose it?',
                                    'q_contents'     => 'Describe any contents or special markings inside/on the item.',
                                    'q_serial'       => 'Can you provide a serial number, model, or receipt?',
                                    'q_unique_marks' => 'Are there any scratches, stickers, or unique marks?',
                                ]; foreach($verificationQs as $qName => $qText): ?>
                                <div class="col-12">
                                    <label class="form-label small fw-500 mb-1"><?= $qText ?></label>
                                    <input type="text" name="<?= $qName ?>" class="form-control form-control-sm" placeholder="Record claimant's answer...">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Staff Notes / Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional observations or notes about this claim..."></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRelease" required>
                            <label class="form-check-label small fw-500" for="confirmRelease">
                                I confirm that the claimant has presented valid proof of ownership and identity, and I authorize the release of this item.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Process & Release Claim</button>
                    <a href="?page=claims" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
