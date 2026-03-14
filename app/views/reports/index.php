<?php $pageTitle='Reports & Export'; require_once __DIR__.'/../layouts/header.php'; $user=currentUser(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li><li class="breadcrumb-item active">Reports</li></ol></nav>
</div>

<!-- Quick Period Buttons -->
<div class="form-card mb-3 py-2">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small fw-600 text-muted me-1"><i class="fas fa-calendar-alt me-1"></i>Quick Print:</span>
        <a href="?page=reports&action=export-pdf&type=found&period=this_week" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-print me-1"></i>Found — This Week</a>
        <a href="?page=reports&action=export-pdf&type=found&period=this_month" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-print me-1"></i>Found — This Month</a>
        <a href="?page=reports&action=export-pdf&type=lost&period=this_week" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-print me-1"></i>Lost — This Week</a>
        <a href="?page=reports&action=export-pdf&type=lost&period=this_month" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-print me-1"></i>Lost — This Month</a>
    </div>
</div>

<div class="row g-3">
    <!-- Found Items Report -->
    <div class="col-md-6">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-box-open me-2 text-success"></i>Found Items Report</h6>
            <form method="GET" action="?page=reports&action=export-pdf" target="_blank" id="foundPdfForm">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="action" value="export-pdf">
                <input type="hidden" name="type" value="found">
                <div class="row g-2">
                    <?php if($user['role']==='superadmin'): ?>
                    <div class="col-md-6">
                        <label class="form-label small">Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">All Branches</option>
                            <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label small">Period</label>
                        <select name="period" class="form-select form-select-sm" onchange="setPeriodDates(this,'found')">
                            <option value="">Custom Range</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach(['unclaimed','matched','claimed','disposed'] as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label small">Date From</label><input type="date" name="date_from" class="form-control form-control-sm" id="found_date_from"></div>
                    <div class="col-md-6"><label class="form-label small">Date To</label><input type="date" name="date_to" class="form-control form-control-sm" id="found_date_to"></div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                    <button type="button" onclick="exportCsv('found',this.closest('form'))" class="btn btn-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lost Reports Export -->
    <div class="col-md-6">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Lost Reports Export</h6>
            <form method="GET" action="?page=reports&action=export-pdf" target="_blank" id="lostPdfForm">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="action" value="export-pdf">
                <input type="hidden" name="type" value="lost">
                <div class="row g-2">
                    <?php if($user['role']==='superadmin'): ?>
                    <div class="col-md-6">
                        <label class="form-label small">Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">All Branches</option>
                            <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label small">Period</label>
                        <select name="period" class="form-select form-select-sm" onchange="setPeriodDates(this,'lost')">
                            <option value="">Custom Range</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach(['open','matched','closed'] as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label small">Date From</label><input type="date" name="date_from" class="form-control form-control-sm" id="lost_date_from"></div>
                    <div class="col-md-6"><label class="form-label small">Date To</label><input type="date" name="date_to" class="form-control form-control-sm" id="lost_date_to"></div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                    <button type="button" onclick="exportCsv('lost',this.closest('form'))" class="btn btn-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Code for Guest Portal (Hosted Link) -->
    <div class="col-12">
        <div class="form-card">
            <h6 class="fw-600 border-bottom pb-2 mb-3"><i class="fas fa-qrcode me-2 text-dark"></i>Guest Portal QR Code <small class="text-muted fw-400">— Share with students/staff to report lost items</small></h6>
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div id="qrcode" style="padding:8px;background:#fff;border:2px solid #e2e8f0;border-radius:.75rem;display:inline-block"></div>
                </div>
                <div class="col">
                    <div class="fw-600 mb-1">Guest Portal URL</div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <code id="portalUrl" class="bg-light px-2 py-1 rounded small" style="word-break:break-all"><?= BASE_URL ?>/index.php?page=guest-login</code>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyUrl()"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="small text-muted mb-2">Scan this QR code or share the link so guests can report lost items and track their reports online.</div>
                    <button class="btn btn-sm btn-dark" onclick="printQr()"><i class="fas fa-print me-1"></i>Print QR Code</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $extraJs='<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function exportCsv(type, form) {
    const data = new FormData(form);
    let url = "?page=reports&action=export-csv&type="+type;
    for(let [k,v] of data.entries()) {
        if(k!=="type"&&k!=="action"&&k!=="page") url += "&"+k+"="+encodeURIComponent(v);
    }
    window.location.href = url;
}
function getPeriodDates(val) {
    const today = new Date();
    let from = "", to = "";
    if (val === "this_week") {
        const day = today.getDay(), diff = today.getDate() - day + (day===0?-6:1);
        const mon = new Date(today); mon.setDate(diff);
        from = mon.toISOString().slice(0,10);
        const sun = new Date(mon); sun.setDate(mon.getDate()+6);
        to = sun.toISOString().slice(0,10);
    } else if (val === "this_month") {
        from = today.getFullYear()+"-"+String(today.getMonth()+1).padStart(2,"0")+"-01";
        to = new Date(today.getFullYear(), today.getMonth()+1, 0).toISOString().slice(0,10);
    } else if (val === "last_month") {
        const lm = new Date(today.getFullYear(), today.getMonth()-1, 1);
        from = lm.toISOString().slice(0,10);
        to = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().slice(0,10);
    }
    return {from, to};
}
function setPeriodDates(sel, prefix) {
    const {from, to} = getPeriodDates(sel.value);
    if (from) { document.getElementById(prefix+"_date_from").value = from; document.getElementById(prefix+"_date_to").value = to; }
}
const portalUrl = document.getElementById("portalUrl").textContent.trim();
new QRCode(document.getElementById("qrcode"), {
    text: portalUrl,
    width: 120,
    height: 120,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});
function copyUrl() {
    navigator.clipboard.writeText(portalUrl);
    const btn = event.target.closest("button");
    btn.innerHTML = "<i class=\"fas fa-check\"></i>"; setTimeout(()=>btn.innerHTML="<i class=\"fas fa-copy\"></i>", 1500);
}
function printQr() {
    const canvas = document.querySelector("#qrcode canvas");
    const img = document.querySelector("#qrcode img");
    const qrSrc = canvas ? canvas.toDataURL() : (img ? img.src : "");
    const qrImg = qrSrc ? "<img src=\""+qrSrc+"\" style=\"width:160px;height:160px\">" : "";
    const w = window.open("","_blank","width=400,height=500");
    w.document.write("<html><body style=\"text-align:center;font-family:Arial;padding:30px\"><h2>LabTech Office</h2><h4>Lost &amp; Found \u2014 Guest Portal</h4>"+qrImg+"<p style=\"word-break:break-all;font-size:12px\">"+portalUrl+"</p><p style=\"color:#666;font-size:11px\">Scan QR Code to report lost items</p></body></html>");
    w.document.close(); w.print();
}
</script>';
require_once __DIR__.'/../layouts/footer.php'; ?>
