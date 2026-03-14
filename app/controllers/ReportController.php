<?php
class ReportController {
    private function getReportData($type, $filters) {
        $db=getDB(); $branchScope=getBranchScope();
        $where=['1=1'];
        if($branchScope) $where[]="f.branch_id=$branchScope";
        if(!empty($filters['branch_id'])&&!$branchScope) $where[]="f.branch_id=".(int)$filters['branch_id'];
        if(!empty($filters['status'])) $where[]="f.status='".$db->real_escape_string($filters['status'])."'";
        if(!empty($filters['date_from'])) $where[]="f.found_date>='".$db->real_escape_string($filters['date_from'])."'";
        if(!empty($filters['date_to'])) $where[]="f.found_date<='".$db->real_escape_string($filters['date_to'])."'";
        $whereStr=implode(' AND ',$where);
        if($type==='found') {
            return $db->query("SELECT f.id,f.item_name,c.name AS category,f.color,f.brand,f.found_date,f.found_location,b.name AS branch,f.status,f.storage_location,u.full_name AS logged_by,f.created_at FROM found_items f JOIN branches b ON b.id=f.branch_id LEFT JOIN categories c ON c.id=f.category_id JOIN users u ON u.id=f.logged_by WHERE $whereStr ORDER BY f.found_date DESC")->fetch_all(MYSQLI_ASSOC);
        }
        // lost reports
        $where2=['1=1'];
        if($branchScope) $where2[]="r.branch_id=$branchScope";
        if(!empty($filters['branch_id'])&&!$branchScope) $where2[]="r.branch_id=".(int)$filters['branch_id'];
        if(!empty($filters['status'])) $where2[]="r.status='".$db->real_escape_string($filters['status'])."'";
        if(!empty($filters['date_from'])) $where2[]="r.lost_date>='".$db->real_escape_string($filters['date_from'])."'";
        if(!empty($filters['date_to'])) $where2[]="r.lost_date<='".$db->real_escape_string($filters['date_to'])."'";
        $where2Str=implode(' AND ',$where2);
        return $db->query("SELECT r.id,r.reporter_name,r.reporter_contact,r.item_name,c.name AS category,r.color,r.brand,r.lost_date,r.lost_location,b.name AS branch,r.status,u.full_name AS logged_by,r.created_at FROM lost_reports r JOIN branches b ON b.id=r.branch_id LEFT JOIN categories c ON c.id=r.category_id JOIN users u ON u.id=r.logged_by WHERE $where2Str ORDER BY r.lost_date DESC")->fetch_all(MYSQLI_ASSOC);
    }

    public function index() {
        $db=getDB(); $branchScope=getBranchScope();
        $branches=$branchScope?$db->query("SELECT * FROM branches WHERE id=$branchScope")->fetch_all(MYSQLI_ASSOC):$db->query("SELECT * FROM branches WHERE status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__.'/../views/reports/index.php';
    }

    public function exportCsv() {
        $type=$_GET['type']??'found';
        $filters=$this->buildFilters();
        $data=$this->getReportData($type,$filters);
        $filename=($type==='found'?'found_items':'lost_reports').'_'.date('Ymd_His').'.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        $out=fopen('php://output','w');
        if(!empty($data)) { fputcsv($out,array_keys($data[0])); foreach($data as $row) fputcsv($out,$row); }
        fclose($out);
        exit();
    }

    private function buildFilters() {
        $filters=['branch_id'=>$_GET['branch_id']??'','status'=>$_GET['status']??'','date_from'=>$_GET['date_from']??'','date_to'=>$_GET['date_to']??''];
        // Handle period shortcut
        $period=$_GET['period']??'';
        if($period && !$filters['date_from']) {
            $today=date('Y-m-d');
            if($period==='this_week') {
                $mon=date('Y-m-d',strtotime('monday this week'));
                $sun=date('Y-m-d',strtotime('sunday this week'));
                $filters['date_from']=$mon; $filters['date_to']=$sun;
            } elseif($period==='this_month') {
                $filters['date_from']=date('Y-m-01'); $filters['date_to']=date('Y-m-t');
            } elseif($period==='last_month') {
                $filters['date_from']=date('Y-m-01',strtotime('first day of last month'));
                $filters['date_to']=date('Y-m-t',strtotime('last day of last month'));
            }
        }
        return $filters;
    }

    public function exportPdf() {
        $type=$_GET['type']??'found';
        $filters=$this->buildFilters();
        $data=$this->getReportData($type,$filters);
        $title=($type==='found'?'Found Items Report':'Lost Reports');
        $branchName=$_SESSION['branch_name']??'All Branches';
        $generatedBy=$_SESSION['user_name']??'System';
        $period=$_GET['period']??'';
        $periodLabel=['this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month'];
        $dateRange='';
        if($period && isset($periodLabel[$period])) $dateRange=$periodLabel[$period];
        elseif(!empty($filters['date_from'])||!empty($filters['date_to'])) $dateRange='From '.($filters['date_from']?:'*').' To '.($filters['date_to']?:'*');

        require_once __DIR__.'/../views/reports/pdf.php';
    }
}
