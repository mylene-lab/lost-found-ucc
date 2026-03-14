<?php
class FoundItemController {
    private function getBranches() {
        $db = getDB(); $scope = getBranchScope();
        if ($scope) return $db->query("SELECT * FROM branches WHERE id=$scope AND status='active'")->fetch_all(MYSQLI_ASSOC);
        return $db->query("SELECT * FROM branches WHERE status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }
    private function getCategories() {
        return getDB()->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }

    public function index() {
        $db = getDB(); $branchScope = getBranchScope();
        $perPage = 12; $page = max(1,(int)($_GET['page']??1)); $offset = ($page-1)*$perPage;
        $where = ['1=1'];
        if ($branchScope) $where[] = "f.branch_id=$branchScope";
        if (!empty($_GET['status']))      $where[] = "f.status='".$db->real_escape_string($_GET['status'])."'";
        if (!empty($_GET['branch_id']) && !$branchScope) $where[] = "f.branch_id=".(int)$_GET['branch_id'];
        if (!empty($_GET['category_id'])) $where[] = "f.category_id=".(int)$_GET['category_id'];
        if (!empty($_GET['q']))           { $q=$db->real_escape_string($_GET['q']); $where[]="(f.item_name LIKE '%$q%' OR f.description LIKE '%$q%' OR f.brand LIKE '%$q%')"; }
        if (!empty($_GET['date_from']))   $where[] = "f.found_date>='".$db->real_escape_string($_GET['date_from'])."'";
        if (!empty($_GET['date_to']))     $where[] = "f.found_date<='".$db->real_escape_string($_GET['date_to'])."'";
        $whereStr = implode(' AND ',$where);
        $total = $db->query("SELECT COUNT(*) c FROM found_items f WHERE $whereStr")->fetch_assoc()['c'];
        $items = $db->query("SELECT f.*,b.name AS branch_name,c.name AS category_name,u.full_name AS logged_by_name FROM found_items f JOIN branches b ON b.id=f.branch_id LEFT JOIN categories c ON c.id=f.category_id JOIN users u ON u.id=f.logged_by WHERE $whereStr ORDER BY f.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
        $branches = $this->getBranches(); $categories = $this->getCategories();
        $pagination = paginate($total,$perPage,$page,'?page=found-items&'.http_build_query(array_diff_key($_GET,['page'=>''])));
        require_once __DIR__.'/../views/items/found_index.php';
    }

    public function view() {
        $id=$_GET['id']??0; $db=getDB();
        $item=$db->query("SELECT f.*,b.name AS branch_name,c.name AS category_name,u.full_name AS logged_by_name FROM found_items f JOIN branches b ON b.id=f.branch_id LEFT JOIN categories c ON c.id=f.category_id JOIN users u ON u.id=f.logged_by WHERE f.id=$id")->fetch_assoc();
        if (!$item||!canAccessBranch($item['branch_id'])) { flash('danger','Item not found.'); redirect(BASE_URL.'/index.php?page=found-items'); }
        $matches=$db->query("SELECT m.*,r.reporter_name,r.item_name AS report_name,r.reporter_contact,u.full_name AS matched_by_name FROM item_matches m JOIN lost_reports r ON r.id=m.lost_report_id JOIN users u ON u.id=m.matched_by WHERE m.found_item_id=$id")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/items/found_view.php';
    }

    public function create() {
        $item=null; $branches=$this->getBranches(); $categories=$this->getCategories();
        require_once __DIR__.'/../views/items/found_form.php';
    }

    public function store() {
        $db=getDB();
        $branchId=(int)($_POST['branch_id']??0);
        if (!canAccessBranch($branchId)) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=found-items'); }
        $photo=null;
        if (!empty($_FILES['photo']['name'])) {
            $result=uploadPhoto($_FILES['photo']);
            if (is_array($result)&&isset($result['error'])) { flash('danger',$result['error']); redirect(BASE_URL.'/index.php?page=found-items&action=create'); }
            $photo=$result;
        }
        $uid=(int)$_SESSION['user_id'];
        $catId=(!empty($_POST['category_id'])&&$_POST['category_id']!=='')?(int)$_POST['category_id']:null;
        $n=$_POST['item_name']??''; $desc=$_POST['description']??''; $col=$_POST['color']??'';
        $br=$_POST['brand']??''; $fd=$_POST['found_date']??date('Y-m-d'); $fl=$_POST['found_location']??'';
        $sl=$_POST['storage_location']??''; $nt=$_POST['notes']??'';
        $stmt=$db->prepare("INSERT INTO found_items (branch_id,logged_by,category_id,item_name,description,color,brand,found_date,found_location,photo,storage_location,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iiisssssssss',$branchId,$uid,$catId,$n,$desc,$col,$br,$fd,$fl,$photo,$sl,$nt);
        $stmt->execute();
        $newId=$db->insert_id;
        logActivity('FOUND_ITEM_LOGGED',"Logged found item ID $newId: $n");
        flash('success','Found item logged successfully!');
        redirect(BASE_URL.'/index.php?page=found-items&action=view&id='.$newId);
    }

    public function edit() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $item=$db->query("SELECT * FROM found_items WHERE id=$id")->fetch_assoc();
        if (!$item||!canAccessBranch($item['branch_id'])) { flash('danger','Item not found.'); redirect(BASE_URL.'/index.php?page=found-items'); }
        $branches=$this->getBranches(); $categories=$this->getCategories();
        require_once __DIR__.'/../views/items/found_form.php';
    }

    public function update() {
        $id=(int)($_POST['id']??0); $db=getDB();
        $item=$db->query("SELECT * FROM found_items WHERE id=$id")->fetch_assoc();
        if (!$item||!canAccessBranch($item['branch_id'])) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=found-items'); }
        $photo=$item['photo'];
        if (!empty($_FILES['photo']['name'])) {
            $result=uploadPhoto($_FILES['photo']);
            if (is_array($result)&&isset($result['error'])) { flash('danger',$result['error']); redirect(BASE_URL.'/index.php?page=found-items&action=edit&id='.$id); }
            if ($photo&&file_exists(UPLOAD_PATH.$photo)) unlink(UPLOAD_PATH.$photo);
            $photo=$result;
        }
        $catId=(!empty($_POST['category_id'])&&$_POST['category_id']!=='')?(int)$_POST['category_id']:null;
        $n=$_POST['item_name']??''; $desc=$_POST['description']??''; $col=$_POST['color']??'';
        $br=$_POST['brand']??''; $fd=$_POST['found_date']??''; $fl=$_POST['found_location']??'';
        $sl=$_POST['storage_location']??''; $st=$_POST['status']??'unclaimed'; $nt=$_POST['notes']??'';
        $stmt=$db->prepare("UPDATE found_items SET category_id=?,item_name=?,description=?,color=?,brand=?,found_date=?,found_location=?,photo=?,storage_location=?,status=?,notes=? WHERE id=?");
        $stmt->bind_param('issssssssssi',$catId,$n,$desc,$col,$br,$fd,$fl,$photo,$sl,$st,$nt,$id);
        $stmt->execute();
        logActivity('FOUND_ITEM_UPDATED',"Updated found item ID $id");
        flash('success','Item updated successfully!');
        redirect(BASE_URL.'/index.php?page=found-items&action=view&id='.$id);
    }

    public function delete() {
        $id=(int)($_GET['id']??0); $db=getDB();
        $item=$db->query("SELECT * FROM found_items WHERE id=$id")->fetch_assoc();
        if (!$item||!canAccessBranch($item['branch_id'])) { flash('danger','Access denied.'); redirect(BASE_URL.'/index.php?page=found-items'); }
        if ($item['photo']&&file_exists(UPLOAD_PATH.$item['photo'])) unlink(UPLOAD_PATH.$item['photo']);
        $db->query("DELETE FROM found_items WHERE id=$id");
        logActivity('FOUND_ITEM_DELETED',"Deleted found item ID $id");
        flash('success','Item deleted.');
        redirect(BASE_URL.'/index.php?page=found-items');
    }
}
