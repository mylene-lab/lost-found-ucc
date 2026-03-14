<?php
class MatchController {
    public function index() {
        $db=getDB(); $branchScope=getBranchScope();
        $branchFilter=$branchScope?"AND f.branch_id=$branchScope":'';
        $matches=$db->query("SELECT m.*,f.item_name AS found_name,f.photo,f.branch_id,r.reporter_name,r.item_name AS report_name,r.reporter_contact,b.name AS branch_name,u.full_name AS matched_by_name FROM item_matches m JOIN found_items f ON f.id=m.found_item_id JOIN lost_reports r ON r.id=m.lost_report_id JOIN branches b ON b.id=f.branch_id JOIN users u ON u.id=m.matched_by WHERE 1 $branchFilter ORDER BY m.match_date DESC")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/matches.php';
    }
    public function create() {
        $db=getDB(); $branchScope=getBranchScope();
        $bf=$branchScope?"AND branch_id=$branchScope":'';
        $foundItems=$db->query("SELECT f.*,b.name AS branch_name FROM found_items f JOIN branches b ON b.id=f.branch_id WHERE f.status='unclaimed' $bf ORDER BY f.item_name")->fetch_all(MYSQLI_ASSOC);
        $lostReports=$db->query("SELECT r.*,b.name AS branch_name FROM lost_reports r JOIN branches b ON b.id=r.branch_id WHERE r.status='open' $bf ORDER BY r.item_name")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/match_form.php';
    }
    public function store() {
        $db=getDB();
        $foundId=(int)$_POST['found_item_id']; $lostId=(int)$_POST['lost_report_id'];
        $found=$db->query("SELECT * FROM found_items WHERE id=$foundId")->fetch_assoc();
        if(!$found||!canAccessBranch($found['branch_id'])){flash('danger','Access denied.');redirect(BASE_URL.'/index.php?page=matches');}
        $stmt=$db->prepare("INSERT INTO item_matches (found_item_id,lost_report_id,matched_by,notes) VALUES (?,?,?,?)");
        $stmt->bind_param('iiis',$foundId,$lostId,$_SESSION['user_id'],$_POST['notes']);
        $stmt->execute();
        $db->query("UPDATE found_items SET status='matched' WHERE id=$foundId");
        $db->query("UPDATE lost_reports SET status='matched' WHERE id=$lostId");
        logActivity('MATCH_CREATED',"Matched found item $foundId with lost report $lostId");
        flash('success','Items matched successfully!');
        redirect(BASE_URL.'/index.php?page=matches');
    }
    public function confirm() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $match=$db->query("SELECT m.*,f.branch_id FROM item_matches m JOIN found_items f ON f.id=m.found_item_id WHERE m.id=$id")->fetch_assoc();
        if(!$match||!canAccessBranch($match['branch_id'])){flash('danger','Access denied.');redirect(BASE_URL.'/index.php?page=matches');}
        $db->query("UPDATE item_matches SET status='confirmed' WHERE id=$id");
        logActivity('MATCH_CONFIRMED',"Confirmed match ID $id");
        flash('success','Match confirmed!');
        redirect(BASE_URL.'/index.php?page=matches');
    }
    public function reject() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $match=$db->query("SELECT m.*,f.branch_id,m.found_item_id,m.lost_report_id FROM item_matches m JOIN found_items f ON f.id=m.found_item_id WHERE m.id=$id")->fetch_assoc();
        if(!$match||!canAccessBranch($match['branch_id'])){flash('danger','Access denied.');redirect(BASE_URL.'/index.php?page=matches');}
        $db->query("UPDATE item_matches SET status='rejected' WHERE id=$id");
        $db->query("UPDATE found_items SET status='unclaimed' WHERE id={$match['found_item_id']}");
        $db->query("UPDATE lost_reports SET status='open' WHERE id={$match['lost_report_id']}");
        logActivity('MATCH_REJECTED',"Rejected match ID $id");
        flash('info','Match rejected, items returned to open status.');
        redirect(BASE_URL.'/index.php?page=matches');
    }
    public function autoRun() {
        $results = autoMatch($_SESSION['user_id']);
        $count   = count($results);
        if ($count > 0) {
            flash('success', "Auto-match complete! Found $count new match" . ($count===1?'':'es') . ". Matches with 90%+ confidence were auto-confirmed.");
        } else {
            flash('info', 'Auto-match complete. No new matches found.');
        }
        redirect(BASE_URL.'/index.php?page=matches');
    }
}
