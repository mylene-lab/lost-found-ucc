<?php
class ClaimController {
    public function index() {
        $db=getDB(); $branchScope=getBranchScope();
        $bf=$branchScope?"AND f.branch_id=$branchScope":'';
        $result=$db->query("SELECT cl.*,f.item_name AS found_name,f.photo,b.name AS branch_name,u.full_name AS processed_by_name FROM claims cl JOIN found_items f ON f.id=cl.found_item_id JOIN branches b ON b.id=f.branch_id JOIN users u ON u.id=cl.processed_by WHERE 1 $bf ORDER BY cl.claim_date DESC");
        if(!$result){flash('danger','DB error: '.$db->error);require_once __DIR__.'/../views/items/claims.php';return;}
        $claims=$result->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/claims.php';
    }
    public function create() {
        $db=getDB(); $branchScope=getBranchScope();
        $bfFound=$branchScope?"AND f.branch_id=$branchScope":'';
        $bfLost=$branchScope?"AND r.branch_id=$branchScope":'';
        $foundResult=$db->query("SELECT f.*,b.name AS branch_name FROM found_items f JOIN branches b ON b.id=f.branch_id WHERE f.status IN ('unclaimed','matched') $bfFound ORDER BY f.item_name");
        if(!$foundResult){flash('danger','DB error (found_items): '.$db->error);redirect(BASE_URL.'/index.php?page=claims');}
        $foundItems=$foundResult->fetch_all(MYSQLI_ASSOC);
        $lostResult=$db->query("SELECT r.*,b.name AS branch_name FROM lost_reports r JOIN branches b ON b.id=r.branch_id WHERE r.status IN ('open','matched') $bfLost ORDER BY r.item_name");
        if(!$lostResult){flash('danger','DB error (lost_reports): '.$db->error);redirect(BASE_URL.'/index.php?page=claims');}
        $lostReports=$lostResult->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/claim_form.php';
    }
    public function store() {
        $db=getDB();
        $foundId=(int)$_POST['found_item_id'];
        $found=$db->query("SELECT * FROM found_items WHERE id=$foundId")->fetch_assoc();
        if(!$found||!canAccessBranch($found['branch_id'])){flash('danger','Access denied.');redirect(BASE_URL.'/index.php?page=claims');}
        $lostId=$_POST['lost_report_id']?:(null);

        // Build notes including verification answers
        $verNotes = $_POST['notes'] ?? '';
        $verQs = ['q_color_desc'=>'Color/Appearance','q_location'=>'Lost Location','q_date'=>'Lost Date','q_contents'=>'Contents/Marks','q_serial'=>'Serial/Model','q_unique_marks'=>'Unique Marks'];
        $verAnswers = [];
        foreach($verQs as $k=>$label) { if(!empty($_POST[$k])) $verAnswers[] = "$label: ".$_POST[$k]; }
        if(!empty($verAnswers)) $verNotes = "=VERIFICATION=\n".implode("\n",$verAnswers).($verNotes?"\n\n=NOTES=\n$verNotes":'');
        $proofType = $_POST['proof_type'] ?? '';
        if($proofType) $verNotes = "Proof Type: $proofType\n".$verNotes;

        $stmt=$db->prepare("INSERT INTO claims (found_item_id,lost_report_id,claimed_by_name,claimed_by_contact,claimed_by_email,id_presented,processed_by,notes) VALUES (?,?,?,?,?,?,?,?)");
        $lostIdBind=$lostId?(int)$lostId:null;
        $stmt->bind_param('iissssis',$foundId,$lostIdBind,$_POST['claimed_by_name'],$_POST['claimed_by_contact'],$_POST['claimed_by_email'],$_POST['id_presented'],$_SESSION['user_id'],$verNotes);
        $stmt->execute();
        $db->query("UPDATE found_items SET status='claimed' WHERE id=$foundId");
        if($lostId) $db->query("UPDATE lost_reports SET status='closed' WHERE id=$lostId");
        logActivity('ITEM_CLAIMED',"Item $foundId claimed by ".$_POST['claimed_by_name']);
        flash('success','Item marked as claimed!');
        redirect(BASE_URL.'/index.php?page=claims');
    }
    public function view() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $claim=$db->query("SELECT cl.*,f.item_name AS found_name,f.photo,f.found_date,f.found_location,b.name AS branch_name,u.full_name AS processed_by_name FROM claims cl JOIN found_items f ON f.id=cl.found_item_id JOIN branches b ON b.id=f.branch_id JOIN users u ON u.id=cl.processed_by WHERE cl.id=$id")->fetch_assoc();
        if(!$claim||!canAccessBranch($claim['branch_id']??0)){flash('danger','Not found.');redirect(BASE_URL.'/index.php?page=claims');}
        require_once __DIR__.'/../views/items/claim_view.php';
    }
}
