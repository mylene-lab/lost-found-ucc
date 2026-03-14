<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Portal - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0fdf4; min-height: 100vh; }
        .topbar { background:#fff; border-bottom:3px solid #16a34a; padding:.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 1px 8px rgba(0,0,0,.06); }
        .topbar .brand-icon { width:40px; height:40px; background:#16a34a; border-radius:.625rem; display:flex; align-items:center; justify-content:center; }
        .topbar .brand-name { font-weight:700; color:#14532d; font-size:1rem; }
        .topbar .brand-sub  { font-size:.72rem; color:#6b7280; }
        .content { padding:2rem 1rem; max-width:1100px; margin:0 auto; }
        .form-card { background:#fff; border-radius:1rem; border:1px solid #bbf7d0; padding:1.75rem; box-shadow:0 2px 12px rgba(22,163,74,.08); }
        .report-card { background:#fff; border-radius:.875rem; border:1px solid #e2e8f0; padding:1.25rem; transition:box-shadow .15s; }
        .report-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
        .form-control,.form-select { border-radius:.5rem; border-color:#d1d5db; font-size:.875rem; }
        .form-control:focus,.form-select:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.12); }
        .btn-submit { background:#16a34a; border:none; border-radius:.625rem; padding:.75rem 2rem; font-weight:600; color:#fff; transition:background .15s; width:100%; }
        .btn-submit:hover { background:#15803d; color:#fff; }
        .btn-submit-blue { background:#2563eb; border:none; border-radius:.625rem; padding:.75rem 2rem; font-weight:600; color:#fff; transition:background .15s; width:100%; }
        .btn-submit-blue:hover { background:#1d4ed8; color:#fff; }
        .status-badge { display:inline-block; padding:.3em .8em; border-radius:20px; font-size:.75rem; font-weight:600; }
        .status-open,.status-unclaimed { background:#dbeafe; color:#1e40af; }
        .status-matched { background:#fef3c7; color:#92400e; }
        .status-closed,.status-claimed { background:#d1fae5; color:#065f46; }
        .status-disposed { background:#fee2e2; color:#991b1b; }
        .section-title { font-size:1.05rem; font-weight:700; color:#14532d; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
        .match-alert { background:#fefce8; border:1px solid #fde68a; border-radius:.75rem; padding:.875rem 1rem; font-size:.875rem; }
        .step-badge { width:28px; height:28px; background:#16a34a; color:#fff; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; }
        /* Portal tabs */
        .portal-tabs { display:flex; gap:.5rem; background:#fff; border-radius:1rem; padding:.4rem; border:1px solid #bbf7d0; box-shadow:0 2px 8px rgba(22,163,74,.06); margin-bottom:1.5rem; }
        .portal-tab { flex:1; text-align:center; padding:.65rem 1rem; border-radius:.75rem; font-weight:600; font-size:.9rem; cursor:pointer; border:none; background:transparent; color:#6b7280; transition:all .2s; }
        .portal-tab.active-lost  { background:#dcfce7; color:#15803d; }
        .portal-tab.active-found { background:#dbeafe; color:#1e40af; }
        .portal-tab:hover:not(.active-lost):not(.active-found) { background:#f9fafb; }
        /* Photo drop zone */
        .photo-drop { border:2px dashed #d1d5db; border-radius:.625rem; padding:1.25rem; text-align:center; cursor:pointer; transition:all .2s; background:#fafafa; }
        .photo-drop:hover { border-color:#16a34a; background:#f0fdf4; }
        .photo-drop.has-photo { border-color:#16a34a; background:#f0fdf4; }
        .photo-drop.has-photo-blue { border-color:#2563eb; background:#eff6ff; }
        @media(max-width:576px){ .content{ padding:1rem .75rem; } .portal-tabs { flex-direction:column; } }
    </style>
</head>
<body>

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <div class="brand-icon" style="overflow:hidden;"><img src="<?= BASE_URL ?>/public/ucc-logo.png" alt="UCC Logo" style="width:100%;height:100%;object-fit:contain;"></div>
        <div>
            <div class="brand-name"><?= APP_NAME ?></div>
            <div class="brand-sub">Guest Portal</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block">
            <div style="font-size:.85rem;font-weight:600;color:#14532d"><?= e($_SESSION['user_name']) ?></div>
            <div style="font-size:.72rem;color:#6b7280">Guest Account</div>
        </div>
        <a href="?page=logout" class="btn btn-sm btn-outline-danger" onclick="return confirm('Log out?')">
            <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
    </div>
</div>

<div class="content">

    <?php $flash = getFlash(); if($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?> me-2"></i>
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- How It Works -->
    <div class="form-card mb-4" style="background:linear-gradient(135deg,#f0fdf4,#fff)">
        <div class="section-title"><i class="fas fa-info-circle text-success"></i>How It Works</div>
        <div class="row g-3">
            <?php foreach([
                ['Lost something?','Submit a lost report with details & photo. We\'ll search our found items.'],
                ['Found something?','Report a found item with photo so we can match it to an owner.'],
                ['We Match','Staff reviews reports and links lost & found items that match.'],
                ['Claim at Branch','Visit the branch with a valid ID to claim your item.'],
            ] as $i => [$title,$desc]): ?>
            <div class="col-6 col-md-3">
                <div class="d-flex gap-2">
                    <div class="step-badge mt-1"><?= $i+1 ?></div>
                    <div><div class="fw-600 small"><?= $title ?></div><div class="text-muted" style="font-size:.78rem"><?= $desc ?></div></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="portal-tabs">
        <button class="portal-tab <?= $activeTab==='lost' ? 'active-lost' : '' ?>" onclick="switchTab('lost')">
            <i class="fas fa-exclamation-circle me-2 text-danger"></i>Report Lost Item
        </button>
        <button class="portal-tab <?= $activeTab==='found' ? 'active-found' : '' ?>" onclick="switchTab('found')">
            <i class="fas fa-box-open me-2 text-primary"></i>Report Found Item
        </button>
    </div>

    <div class="row g-4">

        <!-- ══ LEFT: Forms ══ -->
        <div class="col-lg-6">

            <!-- LOST FORM -->
            <div id="lostFormPanel" class="form-card" style="<?= $activeTab!=='lost' ? 'display:none' : '' ?>">
                <div class="section-title"><i class="fas fa-plus-circle text-success"></i>Report a Lost Item</div>
                <form method="POST" action="?page=guest-portal&action=store" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500 small">Branch Where You Lost It <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                <?php foreach($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="reporter_name" class="form-control" required value="<?= e($_SESSION['user_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="reporter_contact" class="form-control" required placeholder="+1-555-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Email</label>
                            <input type="email" name="reporter_email" class="form-control" value="<?= e($_SESSION['user_email']) ?>">
                        </div>
                        <div class="col-12"><hr class="my-1"><div class="fw-600 small text-muted mb-1">ITEM DETAILS</div></div>
                        <div class="col-md-8">
                            <label class="form-label fw-500 small">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required placeholder="e.g. Black Leather Wallet">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500 small">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— None —</option>
                                <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Brand / Make</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Nike">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g. Black, Brown">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Date Lost <span class="text-danger">*</span></label>
                            <input type="date" name="lost_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Location Lost</label>
                            <input type="text" name="lost_location" class="form-control" placeholder="e.g. Lobby, Floor 2">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Distinguishing features, contents, serial numbers..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">📸 Photo <span class="text-muted fw-400">(helps with matching!)</span></label>
                            <div class="photo-drop" id="lostDrop" onclick="document.getElementById('lostPhoto').click()">
                                <div id="lostPlaceholder">
                                    <i class="fas fa-camera fa-2x text-muted d-block mb-2"></i>
                                    <div class="fw-500 small text-muted">Click to upload a photo</div>
                                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.25rem">JPG, PNG, GIF, WEBP — max 5MB</div>
                                </div>
                                <div id="lostPreviewWrap" style="display:none">
                                    <img id="lostPreview" src="" style="max-height:140px;max-width:100%;border-radius:.5rem;object-fit:cover;">
                                    <div class="text-success fw-600 small mt-2">✅ Photo selected — click to change</div>
                                </div>
                            </div>
                            <input type="file" id="lostPhoto" name="photo" accept="image/*" style="display:none">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any other info that may help..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>Submit Lost Item Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- FOUND FORM -->
            <div id="foundFormPanel" class="form-card" style="border-color:#bfdbfe; <?= $activeTab!=='found' ? 'display:none' : '' ?>">
                <div class="section-title" style="color:#1e40af"><i class="fas fa-box-open" style="color:#2563eb"></i>Report a Found Item</div>
                <form method="POST" action="?page=guest-portal&action=store-found" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500 small">Branch Where You Found It <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" style="border-color:#93c5fd" required>
                                <option value="">-- Select Branch --</option>
                                <?php foreach($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-500 small">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required placeholder="e.g. Black Leather Wallet">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500 small">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— None —</option>
                                <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Brand / Make</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Nike">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g. Black, Brown">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Date Found <span class="text-danger">*</span></label>
                            <input type="date" name="found_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500 small">Location Found <span class="text-danger">*</span></label>
                            <input type="text" name="found_location" class="form-control" required placeholder="e.g. Library, Room 301">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the item — color, markings, contents..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">📸 Photo <span class="text-muted fw-400">(very important for matching!)</span></label>
                            <div class="photo-drop" id="foundDrop" onclick="document.getElementById('foundPhoto').click()">
                                <div id="foundPlaceholder">
                                    <i class="fas fa-camera fa-2x text-muted d-block mb-2"></i>
                                    <div class="fw-500 small text-muted">Click to upload a photo</div>
                                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.25rem">JPG, PNG, GIF, WEBP — max 5MB</div>
                                </div>
                                <div id="foundPreviewWrap" style="display:none">
                                    <img id="foundPreview" src="" style="max-height:140px;max-width:100%;border-radius:.5rem;object-fit:cover;">
                                    <div class="fw-600 small mt-2" style="color:#2563eb">✅ Photo selected — click to change</div>
                                </div>
                            </div>
                            <input type="file" id="foundPhoto" name="photo" accept="image/*" style="display:none">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500 small">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Where did you turn it in? Any other details..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-submit-blue">
                            <i class="fas fa-paper-plane me-2"></i>Submit Found Item Report
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- ══ RIGHT: My Reports ══ -->
        <div class="col-lg-6">

            <!-- My Lost Reports -->
            <div id="lostListPanel" style="<?= $activeTab!=='lost' ? 'display:none' : '' ?>">
                <div class="section-title"><i class="fas fa-list-alt text-success"></i>My Lost Reports <span class="badge bg-success ms-1"><?= count($myReports) ?></span></div>

                <?php if(empty($myReports)): ?>
                <div class="form-card text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted d-block mb-3"></i>
                    <div class="fw-600 text-muted">No lost reports yet</div>
                    <small class="text-muted">Submit your first lost item report using the form.</small>
                </div>
                <?php else: foreach($myReports as $r): ?>
                <div class="report-card mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex gap-2 align-items-start">
                            <?php if(!empty($r['photo'])): ?>
                            <img src="<?= UPLOAD_URL . e($r['photo']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:.5rem;border:2px solid #e2e8f0;flex-shrink:0;">
                            <?php else: ?>
                            <div style="width:48px;height:48px;background:#f0fdf4;border-radius:.5rem;border:2px solid #bbf7d0;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-box text-success" style="font-size:.8rem"></i></div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-600"><?= e($r['item_name']) ?></div>
                                <div class="text-muted small"><?= e($r['category_name'] ?? 'Uncategorized') ?> · <?= e($r['branch_name']) ?></div>
                            </div>
                        </div>
                        <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                    </div>
                    <?php if($r['color']||$r['brand']): ?>
                    <div class="mb-2"><small class="text-muted">
                        <?= $r['brand']?'<i class="fas fa-tag me-1"></i>'.e($r['brand']).' ':'' ?>
                        <?= $r['color']?'<i class="fas fa-circle me-1" style="font-size:.6rem"></i>'.e($r['color']):''; ?>
                    </small></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-calendar me-1"></i>Lost: <?= formatDate($r['lost_date']) ?></small>
                        <small class="text-muted">Reported: <?= formatDate($r['created_at']) ?></small>
                    </div>
                    <?php if($r['match_count'] > 0): ?>
                    <div class="match-alert mt-2">
                        <i class="fas fa-bell text-warning me-1"></i>
                        <strong>Possible match found!</strong> A found item may match your report. Please contact the branch.
                    </div>
                    <?php endif; ?>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">#<?= $r['id'] ?></small>
                        <a href="?page=guest-portal&action=view&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- My Found Items -->
            <div id="foundListPanel" style="<?= $activeTab!=='found' ? 'display:none' : '' ?>">
                <div class="section-title" style="color:#1e40af"><i class="fas fa-box-open" style="color:#2563eb"></i>My Found Item Reports <span class="badge bg-primary ms-1"><?= count($myFoundItems) ?></span></div>

                <?php if(empty($myFoundItems)): ?>
                <div class="form-card text-center py-5" style="border-color:#bfdbfe">
                    <i class="fas fa-inbox fa-3x text-muted d-block mb-3"></i>
                    <div class="fw-600 text-muted">No found items reported yet</div>
                    <small class="text-muted">If you found something, report it here so we can find the owner!</small>
                </div>
                <?php else: foreach($myFoundItems as $f): ?>
                <div class="report-card mb-3" style="border-color:#bfdbfe">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex gap-2 align-items-start">
                            <?php if(!empty($f['photo'])): ?>
                            <img src="<?= UPLOAD_URL . e($f['photo']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:.5rem;border:2px solid #bfdbfe;flex-shrink:0;">
                            <?php else: ?>
                            <div style="width:48px;height:48px;background:#eff6ff;border-radius:.5rem;border:2px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-box-open" style="font-size:.8rem;color:#2563eb"></i></div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-600"><?= e($f['item_name']) ?></div>
                                <div class="text-muted small"><?= e($f['category_name'] ?? 'Uncategorized') ?> · <?= e($f['branch_name']) ?></div>
                            </div>
                        </div>
                        <span class="status-badge status-<?= $f['status'] ?>"><?= ucfirst($f['status']) ?></span>
                    </div>
                    <?php if($f['color']||$f['brand']): ?>
                    <div class="mb-2"><small class="text-muted">
                        <?= $f['brand']?'<i class="fas fa-tag me-1"></i>'.e($f['brand']).' ':'' ?>
                        <?= $f['color']?'<i class="fas fa-circle me-1" style="font-size:.6rem"></i>'.e($f['color']):''; ?>
                    </small></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-calendar me-1"></i>Found: <?= formatDate($f['found_date']) ?></small>
                        <small class="text-muted">Submitted: <?= formatDate($f['created_at']) ?></small>
                    </div>
                    <?php if($f['status'] === 'matched'): ?>
                    <div class="match-alert mt-2" style="background:#eff6ff;border-color:#93c5fd">
                        <i class="fas fa-link text-primary me-1"></i>
                        <strong>Matched!</strong> This item has been matched to a lost report.
                    </div>
                    <?php endif; ?>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">#<?= $f['id'] ?></small>
                        <a href="?page=guest-portal&action=view-found&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tab switching
function switchTab(tab) {
    ['lost','found'].forEach(t => {
        document.getElementById(t+'FormPanel').style.display  = t===tab ? '' : 'none';
        document.getElementById(t+'ListPanel').style.display  = t===tab ? '' : 'none';
        document.querySelectorAll('.portal-tab').forEach(el => {
            el.classList.remove('active-lost','active-found');
        });
    });
    document.querySelectorAll('.portal-tab')[tab==='lost'?0:1].classList.add('active-'+tab);
    history.replaceState(null,'','?page=guest-portal&tab='+tab);
}

// Photo preview helper
function setupPhotoPreview(inputId, previewId, wrapId, placeholderId, dropId, colorClass) {
    document.getElementById(inputId).addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) { alert('Photo must be under 5MB.'); this.value=''; return; }
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(placeholderId).style.display = 'none';
            document.getElementById(wrapId).style.display = 'block';
            document.getElementById(dropId).classList.add(colorClass);
        };
        reader.readAsDataURL(file);
    });
}
setupPhotoPreview('lostPhoto','lostPreview','lostPreviewWrap','lostPlaceholder','lostDrop','has-photo');
setupPhotoPreview('foundPhoto','foundPreview','foundPreviewWrap','foundPlaceholder','foundDrop','has-photo-blue');

// Auto-dismiss alerts
setTimeout(()=>{ document.querySelectorAll('.alert').forEach(a=>new bootstrap.Alert(a).close()) }, 5000);
</script>
</body>
</html>
