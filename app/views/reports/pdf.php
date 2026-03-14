<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= e($title) ?></title>
<style>
    * { font-family: Arial, sans-serif; font-size: 11px; }
    body { margin: 20px; color: #333; }
    .header { border-bottom: 3px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 20px; color: #16a34a; }
    .header .meta { color: #666; font-size: 10px; margin-top: 4px; }
    .info-row { display: flex; gap: 20px; margin-bottom: 15px; }
    .info-box { background: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; }
    .info-box .label { color: #6b7280; font-size: 9px; text-transform: uppercase; }
    .info-box .value { font-weight: bold; font-size: 14px; color: #1e293b; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #16a34a; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
    td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: bold; }
    .badge-unclaimed { background: #fef3c7; color: #92400e; }
    .badge-claimed { background: #d1fae5; color: #065f46; }
    .badge-matched { background: #dbeafe; color: #1e40af; }
    .badge-disposed { background: #f1f5f9; color: #475569; }
    .badge-open { background: #dbeafe; color: #1e40af; }
    .badge-closed { background: #f1f5f9; color: #475569; }
    .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; color: #9ca3af; font-size: 9px; display: flex; justify-content: space-between; }
    @media print { .no-print { display: none; } }
</style>
</head>
<body>

<div class="no-print" style="background:#16a34a;padding:8px 15px;margin:-20px -20px 20px;display:flex;justify-content:space-between;align-items:center">
    <span style="color:white;font-weight:bold"><?= e($title) ?></span>
    <button onclick="window.print()" style="background:white;color:#16a34a;border:none;padding:5px 14px;border-radius:4px;cursor:pointer;font-weight:bold">🖨 Print / Save PDF</button>
</div>

<div class="header">
    <h1><?= APP_NAME ?> <small style="font-size:13px;color:#15803d;font-weight:400"><?= defined('APP_SUBTITLE')?'— '.APP_SUBTITLE:'' ?></small></h1>
    <div class="meta">
        <strong><?= e($title) ?></strong> &nbsp;|&nbsp;
        Branch: <?= e($branchName) ?> &nbsp;|&nbsp;
        <?php if($dateRange): ?>Date Range: <?= e($dateRange) ?> &nbsp;|&nbsp;<?php endif; ?>
        Generated: <?= date('M d, Y h:i A') ?> &nbsp;|&nbsp;
        By: <?= e($generatedBy) ?>
    </div>
</div>

<div class="info-row">
    <div class="info-box"><div class="label">Total Records</div><div class="value"><?= count($data) ?></div></div>
</div>

<?php if(empty($data)): ?>
<p style="text-align:center;color:#999;padding:30px">No records found for the selected filters.</p>
<?php elseif($type==='found'): ?>
<table>
    <thead><tr><th>#</th><th>Item Name</th><th>Category</th><th>Color</th><th>Brand</th><th>Found Date</th><th>Location</th><th>Branch</th><th>Status</th><th>Storage</th><th>Logged By</th></tr></thead>
    <tbody>
    <?php foreach($data as $row): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= e($row['item_name']) ?></td>
        <td><?= e($row['category']??'-') ?></td>
        <td><?= e($row['color']??'-') ?></td>
        <td><?= e($row['brand']??'-') ?></td>
        <td><?= formatDate($row['found_date']) ?></td>
        <td><?= e($row['found_location']??'-') ?></td>
        <td><?= e($row['branch']) ?></td>
        <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
        <td><?= e($row['storage_location']??'-') ?></td>
        <td><?= e($row['logged_by']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<table>
    <thead><tr><th>#</th><th>Reporter</th><th>Contact</th><th>Item Name</th><th>Category</th><th>Color</th><th>Brand</th><th>Lost Date</th><th>Location</th><th>Branch</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($data as $row): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= e($row['reporter_name']) ?></td>
        <td><?= e($row['reporter_contact']) ?></td>
        <td><?= e($row['item_name']) ?></td>
        <td><?= e($row['category']??'-') ?></td>
        <td><?= e($row['color']??'-') ?></td>
        <td><?= e($row['brand']??'-') ?></td>
        <td><?= formatDate($row['lost_date']) ?></td>
        <td><?= e($row['lost_location']??'-') ?></td>
        <td><?= e($row['branch']) ?></td>
        <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="footer">
    <span><?= APP_NAME ?> &copy; <?= date('Y') ?></span>
    <span>Printed on <?= date('M d, Y h:i A') ?></span>
</div>

</body>
</html>
