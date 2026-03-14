<?php
class LostReportController {
    private function getBranches() {
        $db=getDB(); $scope=getBranchScope();
        if ($scope) return $db->query("SELECT * FROM branches WHERE id=$scope AND status='active'")->fetch_all(MYSQLI_ASSOC);
        return $db->query("SELECT * FROM branches WHERE status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }
    private function getCategories() { return getDB()->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC); }

    public function index() {
        $db=getDB(); $branchScope=getBranchScope();
        $perPage=12; $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*$perPage;
        $where=['1=1'];
        if ($branchScope) $where[]="r.branch_id=$branchScope";
        if (!empty($_GET['guest']))       $where[]="u.role='guest'";
        if (!empty($_GET['status']))      $where[]="r.status='".$db->real_escape_string($_GET['status'])."'";
        if (!empty($_GET['branch_id'])&&!$branchScope) $where[]="r.branch_id=".(int)$_GET['branch_id'];
        if (!empty($_GET['category_id'])) $where[]="r.category_id=".(int)$_GET['category_id'];
        if (!empty($_GET['q']))           { $q=$db->real_escape_string($_GET['q']); $where[]="(r.item_name LIKE '%$q%' OR r.reporter_name LIKE '%$q%')"; }
        if (!empty($_GET['date_from']))   $where[]="r.lost_date>='".$db->real_escape_string($_GET['date_from'])."'";
        if (!empty($_GET['date_to']))     $where[]="r.lost_date<='".$db->real_escape_string($_GET['date_to'])."'";
        $whereStr=implode(' AND ',$where);
        $total=$db->query("SELECT COUNT(*) c FROM lost_reports r JOIN branches b ON b.id=r.branch_id LEFT JOIN categories c ON c.id=r.category_id JOIN users u ON u.id=r.logged_by WHERE $whereStr")->fetch_assoc()['c'];
        $reports=$db->query("SELECT r.*,b.name AS branch_name,c.name AS category_name,u.full_name AS logged_by_name,u.role AS logged_by_role FROM lost_reports r JOIN branches b ON b.id=r.branch_id LEFT JOIN categories c ON c.id=r.category_id JOIN users u ON u.id=r.logged_by WHERE $whereStr ORDER BY r.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
        $branches=$this->getBranches(); $categories=$this->getCategories();
        $pagination=paginate($total,$perPage,$page,'?page=lost-reports&'.http_build_query(array_diff_key($_GET,['page'=>''])));
        require_once __DIR__.'/../views/items/lost_index.php';
    }

    public function view() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $report=$db->query("SELECT r.*,b.name AS branch_name,c.name AS category_name,u.full_name AS logged_by_name FROM lost_reports r JOIN branches b ON b.id=r.branch_id LEFT JOIN categories c ON c.id=r.category_id JOIN users u ON u.id=r.logged_by WHERE r.id=$id")->fetch_assoc();
        if (!$report||!canAccessBranch($report['branch_id'])) { flash('danger','Not found.'); redirect(BASE_URL.'/index.php?page=lost-reports'); }
        $matches=$db->query("SELECT m.*,f.item_name AS found_name,f.photo,f.found_date,f.found_location,u.full_name AS matched_by_name FROM item_matches m JOIN found_items f ON f.id=m.found_item_id JOIN users u ON u.id=m.matched_by WHERE m.lost_report_id=$id")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/lost_view.php';
    }

    public function create() {
        $report=null; $branches=$this->getBranches(); $categories=$this->getCategories();
        require_once __DIR__.'/../views/items/lost_form.php';
    }

    public function store() {
        $db=getDB();
        $branchId=(int)($_POST['branch_id']??0);
        if (!canAccessBranch($branchId)) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=lost-reports'); }
        $catId=(!empty($_POST['category_id'])&&$_POST['category_id']!=='')?(int)$_POST['category_id']:null;
        $uid=(int)$_SESSION['user_id'];
        $rn=$_POST['reporter_name']??''; $rc=$_POST['reporter_contact']??''; $re=$_POST['reporter_email']??'';
        $n=$_POST['item_name']??''; $desc=$_POST['description']??''; $col=$_POST['color']??'';
        $br=$_POST['brand']??''; $ld=$_POST['lost_date']??date('Y-m-d'); $ll=$_POST['lost_location']??''; $nt=$_POST['notes']??'';
        $stmt=$db->prepare("INSERT INTO lost_reports (branch_id,logged_by,category_id,reporter_name,reporter_contact,reporter_email,item_name,description,color,brand,lost_date,lost_location,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iiissssssssss',$branchId,$uid,$catId,$rn,$rc,$re,$n,$desc,$col,$br,$ld,$ll,$nt);
        $stmt->execute();
        $newId=$db->insert_id;
        logActivity('LOST_REPORT_LOGGED',"Logged lost report ID $newId");
        flash('success','Lost report logged!');
        redirect(BASE_URL.'/index.php?page=lost-reports&action=view&id='.$newId);
    }

    public function edit() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $report=$db->query("SELECT * FROM lost_reports WHERE id=$id")->fetch_assoc();
        if (!$report||!canAccessBranch($report['branch_id'])) { flash('danger','Not found.'); redirect(BASE_URL.'/index.php?page=lost-reports'); }
        $branches=$this->getBranches(); $categories=$this->getCategories();
        require_once __DIR__.'/../views/items/lost_form.php';
    }

    public function update() {
        $id=(int)($_POST['id']??0); $db=getDB();
        $report=$db->query("SELECT * FROM lost_reports WHERE id=$id")->fetch_assoc();
        if (!$report||!canAccessBranch($report['branch_id'])) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=lost-reports'); }
        $catId=(!empty($_POST['category_id'])&&$_POST['category_id']!=='')?(int)$_POST['category_id']:null;
        $rn=$_POST['reporter_name']??''; $rc=$_POST['reporter_contact']??''; $re=$_POST['reporter_email']??'';
        $n=$_POST['item_name']??''; $desc=$_POST['description']??''; $col=$_POST['color']??'';
        $br=$_POST['brand']??''; $ld=$_POST['lost_date']??''; $ll=$_POST['lost_location']??'';
        $st=$_POST['status']??'open'; $nt=$_POST['notes']??'';
        $stmt=$db->prepare("UPDATE lost_reports SET category_id=?,reporter_name=?,reporter_contact=?,reporter_email=?,item_name=?,description=?,color=?,brand=?,lost_date=?,lost_location=?,status=?,notes=? WHERE id=?");
        $stmt->bind_param('isssssssssssi',$catId,$rn,$rc,$re,$n,$desc,$col,$br,$ld,$ll,$st,$nt,$id);
        $stmt->execute();
        logActivity('LOST_REPORT_UPDATED',"Updated lost report ID $id");
        flash('success','Report updated!');
        redirect(BASE_URL.'/index.php?page=lost-reports&action=view&id='.$id);
    }

    public function delete() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $report=$db->query("SELECT * FROM lost_reports WHERE id=$id")->fetch_assoc();
        if (!$report||!canAccessBranch($report['branch_id'])) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=lost-reports'); }
        $db->query("DELETE FROM lost_reports WHERE id=$id");
        logActivity('LOST_REPORT_DELETED',"Deleted lost report ID $id");
        flash('success','Report deleted.');
        redirect(BASE_URL.'/index.php?page=lost-reports');
    }
}
