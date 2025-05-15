<?php
/*
* 
*/
//Appel de l'esp\ce de nom du Controllers
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
ob_end_clean();

class Fonctionnel extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }

  //fonction index
  public function index()
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    //get data chapitre
    $bind_division = $this->getBindParms('DIVISION_ID,LIBELLE_DIVISION','class_fonctionnelle_division','1','LIBELLE_DIVISION ASC');
    $data['division']= $this->ModelPs->getRequete($callpsreq,$bind_division);

    //get data institution
    $bind_institution = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions', '1','INSTITUTION_ID DESC');
    $data['institution']= $this->ModelPs->getRequete($callpsreq,$bind_institution);

    //get data trimestre
    $trimestre = $this->getBindParms('DESC_TRIMESTRE,TRIMESTRE_ID','trimestre','1','TRIMESTRE_ID ASC');
    $data['trimestre'] = $this->ModelPs->getRequete($callpsreq,$trimestre);

    //annee budgetaire: mettre par défaut année en cours
    $anne_id=$this->get_annee_budgetaire();
    //$anne_id=2;
    $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID<='.$anne_id,'ANNEE_BUDGETAIRE_ID ASC');
    $data['ANNEE_BUDGETAIRE_ID']=$anne_id;
    $data['anne_budgetaire'] = $this->ModelPs->getRequete($callpsreq, $bindparams_anne);
    return view('App\Modules\ihm\Views\Fonctionnel_Views',$data);
  }

  //fonction listing
  public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $DIVISION_ID=$this->request->getPost('DIVISION_ID');
    $GROUPE_ID=$this->request->getPost('GROUPE_ID');
    $CLASSE_ID=$this->request->getPost('CLASSE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    if(!empty($DATE_FIN)){
      $DATE_FIN = $DATE_FIN . ' 23:59:59';
    }

    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    if(!empty($DATE_DEBUT)){
      $DATE_DEBUT = $DATE_DEBUT . ' 00:00:00';
    }
    
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';$group='';
    $order_column = array(1,'LIBELLE_DIVISION','LIBELLE_GROUPE','LIBELLE_CLASSE',1,1,1,1,1,1,1,1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY PTBA_ID  ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (LIBELLE_DIVISION LIKE "%'.$var_search.'%" OR LIBELLE_GROUPE LIKE "%' . $var_search . '%" OR LIBELLE_CLASSE LIKE "%' . $var_search . '%")') : '';
    $group=' ';

    $critere="";
    $critere_tranche="";
    $critere_anne="";
    $critere_date="";
    $ann=$this->get_annee_budgetaire();
    //$ann=2;
    $critere_fonctionnel="";
    $crit_fonc="";
    if(!empty($DIVISION_ID))
    {
      
      $critere_fonctionnel.=' AND ptba_tache.DIVISION_ID='.$DIVISION_ID.'';
      $crit_fonc.=' AND DIVISION_ID='.$DIVISION_ID.'';

      if(!empty($GROUPE_ID))
      {
        $critere_fonctionnel.=' AND ptba_tache.GROUPE_ID='.$GROUPE_ID.'';
        $crit_fonc.=' AND GROUPE_ID='.$GROUPE_ID.'';

        if(!empty($CLASSE_ID))
        {
          $critere_fonctionnel.=' AND ptba_tache.CLASSE_ID='.$CLASSE_ID.'';
          $crit_fonc.=' AND CLASSE_ID='.$CLASSE_ID.'';
        }
      }
    }

    $critere_tranche = " ";
    if(!empty($TRIMESTRE_ID))
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if(!empty($TRIMESTRE_ID))
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    $critere_anne_ptba = '';
    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND exec.DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_DEBUT) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND exec.DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    // Condition pour la requêthttp://localhost:8080/ihm/Fonctionnel#e principale
    $conditions=$critere_fonctionnel.' '.$critere_anne_ptba.'  '.$search.' '.$group.' '.$order_by.' '.$limit;
      // Condition pour la requête de filtre
    $conditionsfilter=$critere_fonctionnel.' '.$search.' '.$group;

    $requetedebase="SELECT DISTINCT classe.CLASSE_ID ,division.DIVISION_ID,division.LIBELLE_DIVISION,grp.GROUPE_ID,grp.LIBELLE_GROUPE,classe.LIBELLE_CLASSE,classe.CODE_CLASSE FROM ptba_tache JOIN class_fonctionnelle_division division ON division.DIVISION_ID=ptba_tache.DIVISION_ID JOIN class_fonctionnelle_groupe grp ON grp.GROUPE_ID=ptba_tache.GROUPE_ID JOIN class_fonctionnelle_classe classe ON classe.CLASSE_ID=ptba_tache.CLASSE_ID WHERE 1 ";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase.' '.$conditions;

    // print_r($requetedebases);die();
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row)
    {

      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total";
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total";
      }else{
        $montant_total="SUM(BUDGET_ANNUEL) AS total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','CLASSE_ID='.$row->CLASSE_ID.' '.$critere_anne_ptba,'PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_division=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE=floatval($total_division['total']);

      $params_infos=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND CLASSE_ID='.$row->CLASSE_ID.' '.$critere_tranche.' '.$critere_anne.' '.$critere_date,'1');

      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.CLASSE_ID='.$row->CLASSE_ID.' '.$tranch_transf,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.CLASSE_ID='.$row->CLASSE_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $sub_array = array();
      
      if(mb_strlen($row->LIBELLE_DIVISION) > 8)
      {
        $LIBELLE_DIVISION =  mb_substr($row->LIBELLE_DIVISION, 0, 7).'...<a class="btn-sm" title="'.$row->LIBELLE_DIVISION.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $LIBELLE_DIVISION =  !empty($row->LIBELLE_DIVISION) ? $row->LIBELLE_DIVISION : 'N/A';
      }

      if(mb_strlen($row->LIBELLE_GROUPE) > 8)
      {
        $LIBELLE_GROUPE =  mb_substr($row->LIBELLE_GROUPE, 0, 7).'...<a class="btn-sm" title="'.$row->LIBELLE_GROUPE.'"><i class="fa fa-eye"></i></a>';
      }else
      {
        $LIBELLE_GROUPE =  !empty($row->LIBELLE_GROUPE) ? $row->LIBELLE_GROUPE : 'N/A';
      }

      if(mb_strlen($row->LIBELLE_CLASSE) > 8)
      {
        $LIBELLE_CLASSE =  mb_substr($row->LIBELLE_CLASSE, 0, 7).'...<a class="btn-sm" title="'.$row->LIBELLE_CLASSE.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $LIBELLE_CLASSE =  !empty($row->LIBELLE_CLASSE) ? $row->LIBELLE_CLASSE : 'N/A';
      }

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['CLASSE_ID']==$mont_recep['CLASSE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_RECEPTION;
        $CREDIT_APRES_TRANSFERT_CLASSE = floatval($BUDGET_VOTE) + (floatval($MONTANT_RECEPTION) - floatval($MONTANT_TRANSFERT));
      }
      
      $ENG_BUDGETAIRE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $DECAISSEMENT=!empty($infos_sup['MONTANT_DECAISSEMENT']) ? $infos_sup['MONTANT_DECAISSEMENT'] :'0';

      $sub_array[] = $LIBELLE_DIVISION;
      $sub_array[] = $LIBELLE_GROUPE;
      $sub_array[] = $LIBELLE_CLASSE;
      $sub_array[] = number_format($BUDGET_VOTE,0,","," ");
      $sub_array[] = number_format($TRANSFERTS_CREDITS,0,","," ");
      $sub_array[] = number_format($CREDIT_APRES_TRANSFERT,0,","," ");
      $sub_array[] = number_format($ENG_BUDGETAIRE,0,","," ");
      $sub_array[] = number_format($JURIDIQUE,0,","," ");
      $sub_array[] = number_format($LIQUIDATION,0,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,0,","," ");
      $sub_array[] = number_format($PAIEMENT,0,","," ");
      $sub_array[] = number_format($DECAISSEMENT,0,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal=$this->ModelPs->datatable("CALL `getTable`('".$requetedebase."')");
    $recordsFiltered=$this->ModelPs->datatable(" CALL `getTable`('".$requetedebasefilter."')");
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //function pour exporter le Rapport de suivie evaluation dans excel
  function exporter($DIVISION_ID=0,$GROUPE_ID=0,$CLASSE_ID=0,$TRIMESTRE_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $critere_div='';
    $critere_group='';
    $critere_classe='';
    $critere_anne="";
    $critere_tranche='';
    $critere_date="";

    if($DATE_FIN > 0){
      $DATE_FIN = $DATE_FIN . ' 23:59:59';
    }

    if($DATE_DEBUT > 0){
      $DATE_DEBUT = $DATE_DEBUT . ' 00:00:00';
    }

    $ann=$this->get_annee_budgetaire();
    //$ann=2;

    if($DIVISION_ID>0)
    {
      
      $critere_div=' AND ptba_tache.DIVISION_ID='.$DIVISION_ID.'';
      if($GROUPE_ID>0)
      {
        
        $critere_group.=' AND ptba_tache.GROUPE_ID='.$GROUPE_ID.'';
        if($CLASSE_ID>0)
        {
          $critere_classe.=' AND ptba_tache.CLASSE_ID='.$CLASSE_ID.'';
        }
      }
    }

    $critere_tranche = " ";
    if($TRIMESTRE_ID>0)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if($TRIMESTRE_ID>0)
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    

    $critere_anne_ptba = '';
    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;

      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ann;
      
      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ann,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }

    //filtre date debut et date fin
    if($DATE_DEBUT>0 AND $DATE_FIN==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if($DATE_FIN>0 AND $DATE_DEBUT==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    {
      $critere_date=' AND exec.DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    
    $getRequete='SELECT DISTINCT division.DIVISION_ID,division.CODE_DIVISION,division.LIBELLE_DIVISION FROM ptba_tache JOIN class_fonctionnelle_division division ON division.DIVISION_ID=ptba_tache.DIVISION_ID WHERE 1 '.$critere_div.' '.$critere_anne_ptba.' ORDER BY division.DIVISION_ID ASC';
    $getData=$this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','CIRCUIT DES DEPENSES');
    $sheet->setCellValue('A2','Classification fonctionnelle');
    $sheet->setCellValue('A3','Exercice '.$exercice);
    
    //Date debut selon le trimestre
    
    if($TRIMESTRE_ID==0)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/06/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==1)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/09/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==2)
    {
      $date_start= '01/10/'.substr($exercice,0,4);
      $date_fin= '31/12/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==3)
    {
      $date_start= '01/01/'.substr($exercice, 5);
      $date_fin= '31/03/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==4)
    {
      $date_start= '01/04/'.substr($exercice, 5);
      $date_fin= '30/06/'.substr($exercice,5);
    }

    
    //Période
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    { 
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $sheet->setCellValue('G3','Période du '.$formatDateDebut.' au '.$formatDateFin);
    }
    elseif ($DATE_DEBUT>0 AND $DATE_FIN==0) 
    {
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
     $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $sheet->setCellValue('G3','Période du '.$formatDateDebut.' au '.$formatDateFin);
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN>0) 
    {
      $formatDateDebut = $date_start;
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $sheet->setCellValue('G3','Période du '.$formatDateDebut.' au '.$formatDateFin);
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN==0)
    { 
      $formatDateDebut = $date_start;
      $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $sheet->setCellValue('G3','Période du '.$formatDateDebut.' au '.$formatDateFin);
    }


    //Merge cells for the titles
    $sheet->mergeCells('A1:J1');
    $sheet->mergeCells('A2:J2');
    $sheet->mergeCells('A3:D3');
    $sheet->mergeCells('G3:J3');

    //Set the title formatting
    $titleCellStyle = $sheet->getStyle('A1:J1');
    $titleCellStyle->getFont()->setBold(true);
    $titleCellStyle->getFont()->setSize(16);
    $titleCellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //Set the subtitle formatting
    $subtitleCellStyle = $sheet->getStyle('A2:J2');
    $subtitleCellStyle->getFont()->setBold(true);
    $subtitleCellStyle->getFont()->setSize(11);
    $subtitleCellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


    // Set the phrase formatting
    $firstPhraseCellStyle = $sheet->getStyle('A3:D3');
    $firstPhraseCellStyle->getFont()->setBold(true);
    $firstPhraseCellStyle->getFont()->setSize(11);
    $firstPhraseCellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

    $secondPhraseCellStyle = $sheet->getStyle('G3:J3');
    $secondPhraseCellStyle->getFont()->setBold(true);
    $secondPhraseCellStyle->getFont()->setSize(11);
    $secondPhraseCellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    $sheet->setCellValue('A6','LIBELLE');
    $sheet->setCellValue('B6','PROGRAMMATION FINANCIERE BIF');
    $sheet->setCellValue('C6','TRANSFERTS CREDITS');
    $sheet->setCellValue('D6','CREDIT APRES TRANSFERT');
    $sheet->setCellValue('E6','ENGAGEMENT BUDGETAIRE');       
    $sheet->setCellValue('F6','ENGAGEMENT JURIDIQUE');       
    $sheet->setCellValue('G6','LIQUIDATION');       
    $sheet->setCellValue('H6','ORDONNANCEMENT');       
    $sheet->setCellValue('I6','PAIEMENT');       
    $sheet->setCellValue('J6','DECAISSEMENT');           
    $rows = 8;

    $montant_trim=0;
    //boucle pour les divisions
    foreach ($getData as $key)
    {
      $get_div = $this->getBindParms("LIBELLE_DIVISION","class_fonctionnelle_division","DIVISION_ID=".$key->DIVISION_ID."",'1');
      $get_div=str_replace("\'","'",$get_div);
      $division=$this->ModelPs->getRequeteOne($callpsreq,$get_div);

      $params_infos_division=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN  execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
      $params_infos_division=str_replace('\"','"',$params_infos_division);
      $infos_sup_division=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_division);

      $montant_total="SUM(BUDGET_ANNUEL) AS total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total";
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.'','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_division=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE_ACTION=floatval($total_division['total']);

      //Montant transferé
      $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION_ACTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $MONTANT_TRANSFERT_RESTE = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

      if($MONTANT_TRANSFERT_RESTE >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['DIVISION_ID']==$mont_recep['DIVISION_ID'])
      {
        $MONTANT_TRANSFERT = $MONTANT_RECEPTION_ACTION;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
      }


      $MONTANT_ENGAGE=!empty($infos_sup_division['MONTANT_ENGAGE']) ? $infos_sup_division['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup_division['MONTANT_JURIDIQUE']) ? $infos_sup_division['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup_division['MONTANT_LIQUIDATION']) ? $infos_sup_division['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup_division['MONTANT_ORDONNANCEMENT']) ? $infos_sup_division['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup_division['PAIEMENT']) ? $infos_sup_division['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT=!empty($infos_sup_division['MONTANT_DECAISSEMENT']) ? $infos_sup_division['MONTANT_DECAISSEMENT'] :'0';

      $sheet->setCellValue('A'.$rows,$key->CODE_DIVISION);
      $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_ACTION);
      $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT);
      $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT);
      $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE);
      $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE);
      $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION);
      $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT);
      $sheet->setCellValue('I'.$rows,$PAIEMENT);
      $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT);
      $rows++;
      $sheet->setCellValue('A'.$rows,'    '.$division['LIBELLE_DIVISION']);
      $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_ACTION);
      $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT);
      $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT);
      $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE);
      $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE);
      $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION);
      $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT);
      $sheet->setCellValue('I'.$rows,$PAIEMENT);
      $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT);

      //export des groupe par rapport à leur division

      $params_grp=$this->getBindParms('DISTINCT groupe.GROUPE_ID,groupe.CODE_GROUPE,groupe.LIBELLE_GROUPE','ptba_tache JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=ptba_tache.GROUPE_ID','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.$critere_group,'1');
      $params_grp=str_replace('\"','"',$params_grp);
      $groupes= $this->ModelPs->getRequete($callpsreq,$params_grp);

      foreach ($groupes as $key_grp)
      {
        $rows++;
        $params_total_vote=$this->getBindParms($montant_total,'ptba_tache','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' ','PTBA_TACHE_ID ASC');
        $params_total_vote=str_replace('\"','"',$params_total_vote);
        $total_groupe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_vote);
        $BUDGET_VOTE_GROUP=intval($total_groupe['total']);
        $params_intitule_grp=$this->getBindParms('LIBELLE_GROUPE','class_fonctionnelle_groupe','GROUPE_ID='.$key_grp->GROUPE_ID,'1');
        $params_intitule_grp=str_replace('\"','"',$params_intitule_grp);
        $intitule_groupe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_grp);
        $params_infos_group=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
        $params_infos_group=str_replace('\"','"',$params_infos_group);
        $infos_sup_group=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_group);

        //Montant transferé
        $param_mont_trans_grp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_trans_grp=str_replace('\"','"',$param_mont_trans_grp);
        $mont_transf_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_grp);
        $MONTANT_TRANSFERT_G=floatval($mont_transf_grp['MONTANT_TRANSFERT']);

        //Montant receptionn
        $param_mont_recep_grp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_recep_grp=str_replace('\"','"',$param_mont_recep_grp);
        $mont_recep_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_grp);
        $MONTANT_RECEPTION_G=floatval($mont_recep_grp['MONTANT_RECEPTION']);


        $MONTANT_TRANSFERT_GROUP_RESTE = (floatval($MONTANT_TRANSFERT_G) - floatval($MONTANT_RECEPTION_G));

        if($MONTANT_TRANSFERT_GROUP_RESTE >= 0)
        {
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE;
        }else{
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE*(-1);
        }

        $CREDIT_APRES_TRANSFERT_GROUP=(floatval($BUDGET_VOTE_GROUP) - floatval($MONTANT_TRANSFERT_G)) + floatval($MONTANT_RECEPTION_G);

        if($CREDIT_APRES_TRANSFERT_GROUP < 0){
          $CREDIT_APRES_TRANSFERT_GROUP = $CREDIT_APRES_TRANSFERT_GROUP*(-1);
        }

        if($mont_transf_grp['GROUPE_ID']==$mont_recep_grp['GROUPE_ID'])
        {
          $MONTANT_TRANSFERT_GROUP = $MONTANT_RECEPTION_G;
          $CREDIT_APRES_TRANSFERT_GROUP = floatval($BUDGET_VOTE_GROUP) + (floatval($MONTANT_RECEPTION_G) - floatval($MONTANT_TRANSFERT_G));
        }

        $MONTANT_ENGAGE_GROUP=!empty($infos_sup_group['MONTANT_ENGAGE']) ? $infos_sup_group['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_GROUP=!empty($infos_sup_group['MONTANT_JURIDIQUE']) ? $infos_sup_group['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_GROUP=!empty($infos_sup_group['MONTANT_LIQUIDATION']) ? $infos_sup_group['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_GROUP=!empty($infos_sup_group['MONTANT_ORDONNANCEMENT']) ? $infos_sup_group['MONTANT_ORDONNANCEMENT'] : '0';
        $PAIEMENT_GROUP=!empty($infos_sup_group['PAIEMENT']) ? $infos_sup_group['PAIEMENT'] : '0';
        $MONTANT_DECAISSEMENT_GROUP=!empty($infos_sup_group['MONTANT_DECAISSEMENT']) ? $infos_sup_group['MONTANT_DECAISSEMENT'] :'0';
        $sheet->setCellValue('A'.$rows,'       '.$key_grp->CODE_GROUPE);
        $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_GROUP);
        $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT_GROUP);
        $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT_GROUP);
        $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE_GROUP);
        $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE_GROUP);
        $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION_GROUP);
        $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT_GROUP);
        $sheet->setCellValue('I'.$rows,$PAIEMENT_GROUP);
        $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT_GROUP);
        $rows++;
        $sheet->setCellValue('A'.$rows, '           '.$intitule_groupe['LIBELLE_GROUPE']);
        $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_GROUP);
        $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT_GROUP);
        $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT_GROUP);
        $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE_GROUP);
        $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE_GROUP);
        $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION_GROUP);
        $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT_GROUP);
        $sheet->setCellValue('I'.$rows,$PAIEMENT_GROUP);
        $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT_GROUP);

        //export classe par  rapport au group
        $params_class=$this->getBindParms('DISTINCT classe.CLASSE_ID,classe.CODE_CLASSE','ptba_tache JOIN class_fonctionnelle_classe classe ON classe.CLASSE_ID=ptba_tache.CLASSE_ID','ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.$critere_classe,'classe.CLASSE_ID ASC');
        $params_class=str_replace('\"','"',$params_class);
        $classes= $this->ModelPs->getRequete($callpsreq,$params_class);

        foreach ($classes as $key_classe)
        {
          $rows++;
          $params_total_classe_vote=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.' AND GROUPE_ID='.$key_grp->GROUPE_ID.' AND CLASSE_ID='.$key_classe->CLASSE_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_classe_vote=str_replace('\"','"',$params_total_classe_vote);
          $total_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_classe_vote);
          $BUDGET_VOTE_CLASSE=intval($total_classe['total']);
          $params_intitule_class=$this->getBindParms('LIBELLE_CLASSE','class_fonctionnelle_classe','CODE_CLASSE='.$key_classe->CODE_CLASSE,'1');
          $params_intitule_class=str_replace('\"','"',$params_intitule_class);
          $intitule_classe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_class);
          $params_infos_classe=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba_tache.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
          $params_infos_classe=str_replace('\"','"',$params_infos_classe);
          $infos_sup_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_classe);

          //Montant transferé
          $param_mont_trans_cl = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_trans_cl=str_replace('\"','"',$param_mont_trans_cl);
          $mont_transf_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_cl);
          $MONTANT_TRANSFERT_CL=floatval($mont_transf_cl['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_cl = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_recep_cl=str_replace('\"','"',$param_mont_recep_cl);
          $mont_recep_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_cl);
          $MONTANT_RECEPTION_CL=floatval($mont_recep_cl['MONTANT_RECEPTION']);


          $MONTANT_TRANSFERT_CLASSE_RESTE = (floatval($MONTANT_TRANSFERT_CL) - floatval($MONTANT_RECEPTION_CL));

          if($MONTANT_TRANSFERT_CLASSE_RESTE >= 0)
          {
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE;
          }else{
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE*(-1);
          }

          $CREDIT_APRES_TRANSFERT_CLASSE=(floatval($BUDGET_VOTE_CLASSE) - floatval($MONTANT_TRANSFERT_CL)) + floatval($MONTANT_RECEPTION_CL);

          if($CREDIT_APRES_TRANSFERT_CLASSE < 0){
            $CREDIT_APRES_TRANSFERT_CLASSE = $CREDIT_APRES_TRANSFERT_CLASSE*(-1);
          }

          if($mont_transf_cl['CLASSE_ID']==$mont_recep_cl['CLASSE_ID'])
          {
            $MONTANT_TRANSFERT_CLASSE = $MONTANT_RECEPTION_CL;
            $CREDIT_APRES_TRANSFERT_CLASSE = floatval($BUDGET_VOTE_CLASSE) + (floatval($MONTANT_RECEPTION_CL) - floatval($MONTANT_TRANSFERT_CL));
          }


          $MONTANT_ENGAGE_CLASSE=!empty($infos_sup_classe['MONTANT_ENGAGE']) ? $infos_sup_classe['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_CLASSE=!empty($infos_sup_classe['MONTANT_JURIDIQUE']) ? $infos_sup_classe['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_CLASSE=!empty($infos_sup_classe['MONTANT_LIQUIDATION']) ? $infos_sup_classe['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_ORDONNANCEMENT']) ? $infos_sup_classe['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_CLASSE=!empty($infos_sup_classe['PAIEMENT']) ? $infos_sup_classe['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_DECAISSEMENT']) ? $infos_sup_classe['MONTANT_DECAISSEMENT'] :'0';

          $sheet->setCellValue('A'.$rows,'                '.$key_classe->CODE_CLASSE);
          $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_CLASSE);
          $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT_CLASSE);
          $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT_CLASSE);
          $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE_CLASSE);
          $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE_CLASSE);
          $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION_CLASSE);
          $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT_CLASSE);
          $sheet->setCellValue('I'.$rows,$PAIEMENT_CLASSE);
          $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT_CLASSE);
          $rows++;
          $sheet->setCellValue('A'.$rows,'                     '.$intitule_classe['LIBELLE_CLASSE']);
          $sheet->setCellValue('B'.$rows,$BUDGET_VOTE_CLASSE);
          $sheet->setCellValue('C'.$rows,$MONTANT_TRANSFERT_CLASSE);
          $sheet->setCellValue('D'.$rows,$CREDIT_APRES_TRANSFERT_CLASSE);
          $sheet->setCellValue('E'.$rows,$MONTANT_ENGAGE_CLASSE);
          $sheet->setCellValue('F'.$rows,$MONTANT_JURIDIQUE_CLASSE);
          $sheet->setCellValue('G'.$rows,$MONTANT_LIQUIDATION_CLASSE);
          $sheet->setCellValue('H'.$rows,$MONTANT_ORDONNANCEMENT_CLASSE);
          $sheet->setCellValue('I'.$rows,$PAIEMENT_CLASSE);
          $sheet->setCellValue('J'.$rows,$MONTANT_DECAISSEMENT_CLASSE);

        }
      }

      $rows++; 
    }

    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(30);
    $sheet->getColumnDimension('G')->setWidth(30);
    $sheet->getColumnDimension('H')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(30);
    $sheet->getColumnDimension('J')->setWidth(30);

    $writer = new Xlsx($spreadsheet);
    $writer->save('fonctionnelle.xlsx');
    return $this->response->download('fonctionnelle.xlsx',null)->setFileName('Rapport classification fonctionnelle.xlsx');
    return redirect('ihm/Fonctionnel');
  }

  public function get_dep()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $DIVISION_ID=$this->request->getPost('DIVISION_ID');
    $GROUPE_ID=$this->request->getPost('GROUPE_ID');
    $CLASSE_ID=$this->request->getPost('CLASSE_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $ACTION_ID=$this->request->getPost('ACTION_ID');

    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $prog= '<option value="">'.$input_select.'</option>';
    $act= '<option value="">'.$input_select.'</option>';
    $groupe= '<option value="">'.$input_select.'</option>';
    $classe= '<option value="">'.$input_select.'</option>';

    //get filtre programme via id Institution
    if(!empty($INSTITUTION_ID))
    {
      $bind_programme = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME','inst_institutions_programmes prog','prog.INSTITUTION_ID='.$INSTITUTION_ID,'INTITULE_PROGRAMME ASC');
      $programme= $this->ModelPs->getRequete($callpsreq,$bind_programme);

      foreach($programme as $progra)
      {
        if (!empty($PROGRAMME_ID))
        {
          if($PROGRAMME_ID==$progra->PROGRAMME_ID)
          {
            $prog.="<option value='".$progra->PROGRAMME_ID."' selected>".$progra->INTITULE_PROGRAMME."</option>";
          }
          else
          {
            $prog.= "<option value ='".$progra->PROGRAMME_ID."'>".$progra->INTITULE_PROGRAMME."</option>";
          }
        }
        else
        {
          $prog.= "<option value ='".$progra->PROGRAMME_ID."'>".$progra->INTITULE_PROGRAMME."</option>";
        }
      }

       //get filtre  action via id Programme
      if(!empty($PROGRAMME_ID))
      {
        $bind_action = $this->getBindParms('ACTION_ID,LIBELLE_ACTION,CODE_ACTION','inst_institutions_actions act','act.PROGRAMME_ID='.$PROGRAMME_ID,'ACTION_ID ASC');
        $action = $this->ModelPs->getRequete($callpsreq,$bind_action); 
        foreach ($action as $value)
        {
          if (!empty($ACTION_ID))
          {
            if ($ACTION_ID==$value->CODE_ACTION)
            {
              $act.="<option value ='".$value->ACTION_ID."' selected>".$value->LIBELLE_ACTION."</option>";
            }
            else
            {
              $act.= "<option value ='".$value->ACTION_ID."'>".$value->LIBELLE_ACTION."</option>";
            }
          }
          else
          {
            $act.= "<option value ='".$value->ACTION_ID."'>".$value->LIBELLE_ACTION."</option>";
          }
        }
      }
    }

    //get filtre groupement via id Division
    if(!empty($DIVISION_ID))
    {
      $bind_groupe=$this->getBindParms('GROUPE_ID,LIBELLE_GROUPE','class_fonctionnelle_groupe','DIVISION_ID='.$DIVISION_ID, 'LIBELLE_GROUPE ASC');
      $groupes= $this->ModelPs->getRequete($callpsreq,$bind_groupe); 
      foreach ($groupes as $keys)
      {
        if(!empty($GROUPE_ID))
        {
          if ($GROUPE_ID==$keys->GROUPE_ID)
          {
            $groupe.= "<option value ='".$keys->GROUPE_ID."' selected>".$keys->LIBELLE_GROUPE."</option>";
          }
          else
          {
            $groupe.= "<option value ='".$keys->GROUPE_ID."'>".$keys->LIBELLE_GROUPE."</option>";
          }
        }
        else
        {
          $groupe.= "<option value ='".$keys->GROUPE_ID."'>".$keys->LIBELLE_GROUPE."</option>";
        } 
      }

      //get filtre classe via id Groupement
      if (!empty($GROUPE_ID))
      {
        $bind_classe = $this->getBindParms('CLASSE_ID,LIBELLE_CLASSE','class_fonctionnelle_classe', 'GROUPE_ID='.$GROUPE_ID, 'LIBELLE_CLASSE ASC');
        $classes= $this->ModelPs->getRequete($callpsreq,$bind_classe); 
        foreach ($classes as $keys)
        {
          if (!empty($CLASSE_ID))
          {
            if ($CLASSE_ID==$keys->CLASSE_ID)
            {
              $classe.= "<option value ='".$keys->CLASSE_ID."' selected>".$keys->LIBELLE_CLASSE."</option>";
            }
            else
            {
              $classe.= "<option value ='".$keys->CLASSE_ID."'>".$keys->LIBELLE_CLASSE."</option>";
            }
          }
          else
          {
            $classe.= "<option value ='".$keys->CLASSE_ID."'>".$keys->LIBELLE_CLASSE."</option>";
          }
        }
      }
    }

    $output = array("prog"=>$prog,"act"=>$act,"groupe"=>$groupe,"classe"=>$classe);
    return $this->response->setJSON($output);
  }

  //get institution
  public function getinstution($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $columnselect='TYPE_INSTITUTION_ID';
    $table='inst_institutions';
    $where="INSTITUTION_ID=".$INSTITUTION_ID." ";
    $orderby=' INSTITUTION_ID DESC';

    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $type = $this->ModelPs->getRequeteOne($callpsreq,$bindparams);
    $TYPE_INSTITUTION_ID = $type['TYPE_INSTITUTION_ID'];

    echo json_encode(array('TYPE_INSTITUTION_ID'=>$TYPE_INSTITUTION_ID));
  }

  public function getBindParms($columnselect,$table,$where,$orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  //export dans word
  function exporter_word($DIVISION_ID=0,$GROUPE_ID=0,$CLASSE_ID=0,$TRIMESTRE_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $critere_div='';
    $critere_group='';
    $critere_classe='';
    $critere_anne="";
    $critere_tranche='';
    $critere_date="";

    if($DATE_FIN > 0){
      $DATE_FIN = $DATE_FIN . ' 23:59:59';
    }

    if($DATE_DEBUT > 0){
      $DATE_DEBUT = $DATE_DEBUT . ' 00:00:00';
    }

    $ann=$this->get_annee_budgetaire();

    if($DIVISION_ID>0)
    {
      
      $critere_div=' AND ptba_tache.DIVISION_ID='.$DIVISION_ID.'';
      if($GROUPE_ID>0)
      {
        $critere_group.=' AND ptba_tache.GROUPE_ID='.$GROUPE_ID.'';
        if($CLASSE_ID>0)
        {
          $critere_classe.=' AND ptba_tache.CLASSE_ID='.$CLASSE_ID.'';
        }
      }
    }


    $critere_tranche = " ";
    if($TRIMESTRE_ID>0)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if(!empty($TRIMESTRE_ID))
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $critere_anne_ptba = "";
    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;

      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete,$bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ann;
      
      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ann,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete,$bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }

    //filtre date debut et date fin
    if($DATE_DEBUT>0 AND $DATE_FIN==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if($DATE_FIN>0 AND $DATE_DEBUT==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    {
      $critere_date=' AND exec.DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    // Assuming $data contains the data you want to export
    $getRequete='SELECT DISTINCT division.DIVISION_ID,division.CODE_DIVISION,division.LIBELLE_DIVISION FROM ptba_tache JOIN class_fonctionnelle_division division ON division.DIVISION_ID=ptba_tache.DIVISION_ID WHERE 1 '.$critere_div.' '.$critere_anne_ptba.' ORDER BY division.DIVISION_ID ASC';
    $getData=$this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");
    // Create a new PhpWord object
    $phpWord = new PhpWord();


    // Define section style for landscape orientation and margin
    $sectionStyle = [
      'orientation' => 'landscape',
      'marginTop' => 600,
    ];

    // Add a section with the defined style
    $section = $phpWord->addSection($sectionStyle);

    // Set header text
    $headerText = 'CIRCUIT DES DEPENSES';
    $section->addText($headerText, ['bold' => true, 'size' => 12], ['align' => 'center']);

    // Set subtitle text
    $subtitleText = 'Classification fonctionnelle';
    $section->addText($subtitleText, ['bold' => true, 'size' => 10], ['align' => 'center']);

    // Set phrase values
    $phrase1 = 'Exercice ' . $exercice;

    if($TRIMESTRE_ID==0)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/06/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==1)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/09/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==2)
    {
      $date_start= '01/10/'.substr($exercice,0,4);
      $date_fin= '31/12/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==3)
    {
      $date_start= '01/01/'.substr($exercice, 5);
      $date_fin= '31/03/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==4)
    {
      $date_start= '01/04/'.substr($exercice, 5);
      $date_fin= '30/06/'.substr($exercice,5);
    }


    //Période
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    { 
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT>0 AND $DATE_FIN==0) 
    {
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
      $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN>0) 
    {
      $formatDateDebut = $date_start;
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN==0)
    { 
      $formatDateDebut = $date_start;
      $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }


    // Add the combined phrases on the same line
    $combinedPhrase = $phrase1 . "\t\t\t\t\t" . $phrase2;
    $section->addText($combinedPhrase, ['bold' => true, 'size' => 9], ['align' => 'center']);

    // Create a table
    $table = $section->addTable('myTable');

    // Define table style with cell borders
    $tableStyle = [
      'borderSize' => 6,
    ];

    $phpWord->addTableStyle('myTable', $tableStyle);

    // Add header row with bold text and cell borders
    $table->addRow();
    $table->addCell(1500)->addText('LIBELLE', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(3000)->addText('PROGRAMMATION', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2000)->addText('TRANSFERTS CREDITS', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2000)->addText('CREDIT APRES TRANSFERT', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2800)->addText('ENGAGEMENT BUDGETAIRE', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2800)->addText('ENGAGEMENT JURIDIQUE', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2000)->addText('LIQUIDATION', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(3000)->addText('ORDONNANCEMENT', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(2000)->addText('PAIEMENT', ['bold' => true, 'size' => 6], ['align' => 'center']);
    $table->addCell(3000)->addText('DECAISSEMENT', ['bold' => true, 'size' => 6], ['align' => 'center']);
    // Add data rows
    foreach ($getData as $key)
    {
      $get_div = $this->getBindParms("LIBELLE_DIVISION","class_fonctionnelle_division","DIVISION_ID=".$key->DIVISION_ID."",'1');
      $get_div=str_replace("\'","'",$get_div);
      $division=$this->ModelPs->getRequeteOne($callpsreq,$get_div);
      $params_infos_division=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
      $params_infos_division=str_replace('\"','"',$params_infos_division);
      $infos_sup_division=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_division);

      $montant_total="SUM(BUDGET_ANNUEL) AS total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total";
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.'','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_division=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE_ACTION=floatval($total_division['total']);


      //Montant transferé
      $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION_ACTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $MONTANT_TRANSFERT_RESTE = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

      if($MONTANT_TRANSFERT_RESTE >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['DIVISION_ID']==$mont_recep['DIVISION_ID'])
      {
        $MONTANT_TRANSFERT = $MONTANT_RECEPTION_ACTION;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
      }

      $MONTANT_ENGAGE=!empty($infos_sup_division['MONTANT_ENGAGE']) ? $infos_sup_division['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup_division['MONTANT_JURIDIQUE']) ? $infos_sup_division['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup_division['MONTANT_LIQUIDATION']) ? $infos_sup_division['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup_division['MONTANT_ORDONNANCEMENT']) ? $infos_sup_division['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup_division['PAIEMENT']) ? $infos_sup_division['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT=!empty($infos_sup_division['MONTANT_DECAISSEMENT']) ? $infos_sup_division['MONTANT_DECAISSEMENT'] :'0';

      $table->addRow();
      $table->addCell(3000)->addText($key->CODE_DIVISION,['size' => 6]);
      $table->addCell(3000)->addText(number_format($BUDGET_VOTE_ACTION,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT,0,","," "),['size' => 6]);
      $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE,0,","," "),['size' => 6]);
      $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION,0,","," "),['size' => 6]);
      $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($PAIEMENT,0,","," "),['size' => 6]);
      $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT,0,","," "),['size' => 6]);
      $table->addRow();
      $table->addCell(3000, ['cellMargin' => 30])->addText('  '.$division['LIBELLE_DIVISION'],['size' => 6]);
      $table->addCell(3000)->addText(number_format($BUDGET_VOTE_ACTION,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT,0,","," "),['size' => 6]);
      $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE,0,","," "),['size' => 6]);
      $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION,0,","," "),['size' => 6]);
      $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT,0,","," "),['size' => 6]);
      $table->addCell(2000)->addText(number_format($PAIEMENT,0,","," "),['size' => 6]);
      $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT,0,","," "),['size' => 6]);
      // Set borders for data rows if needed

      $params_grp=$this->getBindParms('DISTINCT groupe.GROUPE_ID,groupe.CODE_GROUPE,groupe.LIBELLE_GROUPE','ptba_tache JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=ptba_tache.GROUPE_ID','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.$critere_group,'1');
      $params_grp=str_replace('\"', '"',$params_grp);
      $groupes= $this->ModelPs->getRequete($callpsreq,$params_grp);

      foreach ($groupes as $key_grp)
      {
        $params_total_vote=$this->getBindParms($montant_total,'ptba_tache','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.'','PTBA_TACHE_ID ASC');
        $params_total_vote=str_replace('\"','"',$params_total_vote);

        $total_groupe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_vote);
        
        $BUDGET_VOTE_GROUP=intval($total_groupe['total']);

        $params_intitule_grp=$this->getBindParms('LIBELLE_GROUPE','class_fonctionnelle_groupe','GROUPE_ID='.$key_grp->GROUPE_ID,'1');
        $params_intitule_grp=str_replace('\"', '"',$params_intitule_grp);
        $intitule_groupe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_grp);

        $params_infos_group=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
        $params_infos_group=str_replace('\"','"',$params_infos_group);
        $infos_sup_group=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_group);

        //Montant transferé
        $param_mont_trans_grp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_trans_grp=str_replace('\"','"',$param_mont_trans_grp);
        $mont_transf_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_grp);
        $MONTANT_TRANSFERT_G=floatval($mont_transf_grp['MONTANT_TRANSFERT']);

        //Montant receptionn
        $param_mont_recep_grp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_recep_grp=str_replace('\"','"',$param_mont_recep_grp);
        $mont_recep_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_grp);
        $MONTANT_RECEPTION_G=floatval($mont_recep_grp['MONTANT_RECEPTION']);


        $MONTANT_TRANSFERT_GROUP_RESTE = (floatval($MONTANT_TRANSFERT_G) - floatval($MONTANT_RECEPTION_G));

        if($MONTANT_TRANSFERT_GROUP_RESTE >= 0)
        {
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE;
        }else{
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE*(-1);
        }

        $CREDIT_APRES_TRANSFERT_GROUP=(floatval($BUDGET_VOTE_GROUP) - floatval($MONTANT_TRANSFERT_G)) + floatval($MONTANT_RECEPTION_G);

        if($CREDIT_APRES_TRANSFERT_GROUP < 0){
          $CREDIT_APRES_TRANSFERT_GROUP = $CREDIT_APRES_TRANSFERT_GROUP*(-1);
        }

        if($mont_transf_grp['GROUPE_ID']==$mont_recep_grp['GROUPE_ID'])
        {
          $MONTANT_TRANSFERT_GROUP = $MONTANT_RECEPTION_G;
          $CREDIT_APRES_TRANSFERT_GROUP = floatval($BUDGET_VOTE_GROUP) + (floatval($MONTANT_RECEPTION_G) - floatval($MONTANT_TRANSFERT_G));
        }


        $MONTANT_ENGAGE_GROUP=!empty($infos_sup_group['MONTANT_ENGAGE']) ? $infos_sup_group['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_GROUP=!empty($infos_sup_group['MONTANT_JURIDIQUE']) ? $infos_sup_group['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_GROUP=!empty($infos_sup_group['MONTANT_LIQUIDATION']) ? $infos_sup_group['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_GROUP=!empty($infos_sup_group['MONTANT_ORDONNANCEMENT']) ? $infos_sup_group['MONTANT_ORDONNANCEMENT'] : '0';
        $PAIEMENT_GROUP=!empty($infos_sup_group['PAIEMENT']) ? $infos_sup_group['PAIEMENT'] : '0';
        $MONTANT_DECAISSEMENT_GROUP=!empty($infos_sup_group['MONTANT_DECAISSEMENT']) ? $infos_sup_group['MONTANT_DECAISSEMENT'] :'0';

        $table->addRow();
        $table->addCell(3000, ['cellMargin' => 60])->addText($key_grp->CODE_GROUPE,['size' => 6]);
        $table->addCell(3000)->addText(number_format($BUDGET_VOTE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION_GROUP,0,","," "),['size' => 6]);
        $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($PAIEMENT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_GROUP,0,","," "),['size' => 6]);
        $table->addRow();
        $table->addCell(3000, ['cellMargin' => 70])->addText('  '.$intitule_groupe['LIBELLE_GROUPE'],['size' => 6]);
        $table->addCell(3000)->addText(number_format($BUDGET_VOTE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION_GROUP,0,","," "),['size' => 6]);
        $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(2000)->addText(number_format($PAIEMENT_GROUP,0,","," "),['size' => 6]);
        $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_GROUP,0,","," "),['size' => 6]);

        //export classe par  rapport au group
        $params_class=$this->getBindParms('DISTINCT classe.CLASSE_ID,classe.CODE_CLASSE','ptba_tache JOIN class_fonctionnelle_classe classe ON classe.CLASSE_ID=ptba_tache.CLASSE_ID','ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.$critere_classe,'classe.CLASSE_ID ASC');
        $params_class=str_replace('\"', '"',$params_class);
        $classes= $this->ModelPs->getRequete($callpsreq,$params_class);

        foreach ($classes as $key_classe)
        {
          $params_total_classe_vote=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.' AND GROUPE_ID='.$key_grp->GROUPE_ID.' AND CLASSE_ID='.$key_classe->CLASSE_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_classe_vote=str_replace('\"','"',$params_total_classe_vote);

          $total_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_classe_vote);
          
          $BUDGET_VOTE_CLASSE=intval($total_classe['total']);

          $params_intitule_class=$this->getBindParms('LIBELLE_CLASSE','class_fonctionnelle_classe','CODE_CLASSE='.$key_classe->CODE_CLASSE,'1');
          $params_intitule_class=str_replace('\"', '"',$params_intitule_class);
          $intitule_classe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_class);

          $params_infos_classe=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba_tache.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
          $params_infos_classe=str_replace('\"','"',$params_infos_classe);
          $infos_sup_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_classe);

          
          //Montant transferé
          $param_mont_trans_cl = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_trans_cl=str_replace('\"','"',$param_mont_trans_cl);
          $mont_transf_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_cl);
          $MONTANT_TRANSFERT_CL=floatval($mont_transf_cl['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_cl = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_recep_cl=str_replace('\"','"',$param_mont_recep_cl);
          $mont_recep_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_cl);
          $MONTANT_RECEPTION_CL=floatval($mont_recep_cl['MONTANT_RECEPTION']);


          $MONTANT_TRANSFERT_CLASSE_RESTE = (floatval($MONTANT_TRANSFERT_CL) - floatval($MONTANT_RECEPTION_CL));

          if($MONTANT_TRANSFERT_CLASSE_RESTE >= 0)
          {
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE;
          }else{
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE*(-1);
          }

          $CREDIT_APRES_TRANSFERT_CLASSE=(floatval($BUDGET_VOTE_CLASSE) - floatval($MONTANT_TRANSFERT_CL)) + floatval($MONTANT_RECEPTION_CL);

          if($CREDIT_APRES_TRANSFERT_CLASSE < 0){
            $CREDIT_APRES_TRANSFERT_CLASSE = $CREDIT_APRES_TRANSFERT_CLASSE*(-1);
          }

          if($mont_transf_cl['CLASSE_ID']==$mont_recep_cl['CLASSE_ID'])
          {
            $MONTANT_TRANSFERT_CLASSE = $MONTANT_RECEPTION_CL;
            $CREDIT_APRES_TRANSFERT_CLASSE = floatval($BUDGET_VOTE_CLASSE) + (floatval($MONTANT_RECEPTION_CL) - floatval($MONTANT_TRANSFERT_CL));
          }

          $MONTANT_ENGAGE_CLASSE=!empty($infos_sup_classe['MONTANT_ENGAGE']) ? $infos_sup_classe['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_CLASSE=!empty($infos_sup_classe['MONTANT_JURIDIQUE']) ? $infos_sup_classe['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_CLASSE=!empty($infos_sup_classe['MONTANT_LIQUIDATION']) ? $infos_sup_classe['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_ORDONNANCEMENT']) ? $infos_sup_classe['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_CLASSE=!empty($infos_sup_classe['PAIEMENT']) ? $infos_sup_classe['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_DECAISSEMENT']) ? $infos_sup_classe['MONTANT_DECAISSEMENT'] :'0';


          $table->addRow();
          $table->addCell(3000, ['cellMargin' => 100])->addText($key_classe->CODE_CLASSE,['size' => 6]);
          $table->addCell(3000)->addText(number_format($BUDGET_VOTE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($PAIEMENT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_CLASSE,0,","," "),['size' => 6]);
          $table->addRow();
          $table->addCell(3000, ['cellMargin' => 120])->addText('  '.$intitule_classe['LIBELLE_CLASSE'],['size' => 6]);
          $table->addCell(3000)->addText(number_format($BUDGET_VOTE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($MONTANT_TRANSFERT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($CREDIT_APRES_TRANSFERT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2800)->addText(number_format($MONTANT_ENGAGE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2800)->addText(number_format($MONTANT_JURIDIQUE_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($MONTANT_LIQUIDATION_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(2000)->addText(number_format($PAIEMENT_CLASSE,0,","," "),['size' => 6]);
          $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_CLASSE,0,","," "),['size' => 6]);

        }
      } 
    }
    
    // Save the document
    $filename = 'Rapport Classification fonctionnelle.docx';
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($filename);

    // Force download the Word file
    return $this->response->download($filename, null)->setFileName($filename);
  }

  //export dans pdf
  function exporter_pdf($DIVISION_ID=0,$GROUPE_ID=0,$CLASSE_ID=0,$TRIMESTRE_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $critere_div='';
    $critere_group='';
    $critere_classe='';
    $critere_anne="";
    $critere_tranche='';
    $critere_date="";

    if($DATE_FIN > 0){
      $DATE_FIN = $DATE_FIN . ' 23:59:59';
    }

    if($DATE_DEBUT > 0){
      $DATE_DEBUT = $DATE_DEBUT . ' 00:00:00';
    }

    $ann=$this->get_annee_budgetaire();

    // Chargez les options de Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    // Définir l'orientation paysage (landscape)
    $options->set('defaultPaperOrientation', 'landscape');

    // Instanciez Dompdf avec les options
    $dompdf = new Dompdf($options);



    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $critere_anne_ptba = "";
    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;

      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete,$bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
      $critere_anne_ptba .=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ann;
      
      //annee budgetaire: mettre par défaut année en cours
      $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ann,'ANNEE_BUDGETAIRE_ID');
      $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete,$bindparams_anne);

      $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];
      $exercice = str_replace('-', '/', $exercice);
    }

    if($TRIMESTRE_ID==0)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/06/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==1)
    {
      $date_start= '01/07/'.substr($exercice,0,4);
      $date_fin= '30/09/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==2)
    {
      $date_start= '01/10/'.substr($exercice,0,4);
      $date_fin= '31/12/'.substr($exercice,0,4);
    }
    if($TRIMESTRE_ID==3)
    {
      $date_start= '01/01/'.substr($exercice, 5);
      $date_fin= '31/03/'.substr($exercice,5);
    }
    if($TRIMESTRE_ID==4)
    {
      $date_start= '01/04/'.substr($exercice, 5);
      $date_fin= '30/06/'.substr($exercice,5);
    }


    //Période
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    { 
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT>0 AND $DATE_FIN==0) 
    {
      $formatDateDebut = date('d/m/Y', strtotime($DATE_DEBUT));
     $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN>0) 
    {
      $formatDateDebut = $date_start;
      $formatDateFin = date('d/m/Y', strtotime($DATE_FIN));
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }
    elseif ($DATE_DEBUT==0 AND $DATE_FIN==0)
    { 
      $formatDateDebut = $date_start;
      $formatDateFin = ($ann!=$ANNEE_BUDGETAIRE_ID) ? $date_fin : date('d/m/Y');
      $phrase2 = 'Période du ' . $formatDateDebut . ' au ' . $formatDateFin;
    }

    // Définir la largeur du tableau
    $tableWidth = '100%';

    if($DIVISION_ID>0)
    {
      
      $critere_div=' AND ptba_tache.DIVISION_ID='.$DIVISION_ID.'';
      if($GROUPE_ID>0)
      {
        $critere_group.=' AND ptba_tache.GROUPE_ID='.$GROUPE_ID.'';
        if($CLASSE_ID>0)
        {
          $critere_classe.=' AND ptba_tache.CLASSE_ID='.$CLASSE_ID.'';
        }
      }
    }

    $critere_tranche = " ";
    if($TRIMESTRE_ID>0)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if(!empty($TRIMESTRE_ID))
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }



    //filtre date debut et date fin
    if($DATE_DEBUT>0 AND $DATE_FIN==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if($DATE_FIN>0 AND $DATE_DEBUT==0)
    {
      $critere_date=' AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if ($DATE_DEBUT>0 AND $DATE_FIN>0) 
    {
      $critere_date=' AND exec.DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    // Définir le contenu du tableau 
    $getRequete='SELECT DISTINCT division.DIVISION_ID,division.CODE_DIVISION,division.LIBELLE_DIVISION FROM ptba_tache JOIN class_fonctionnelle_division division ON division.DIVISION_ID=ptba_tache.DIVISION_ID WHERE 1 '.$critere_div.' '.$critere_anne_ptba.' ORDER BY division.DIVISION_ID ASC';
    $getData=$this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");

    // Ajouter le tableau au HTML
    $table = '<table style="border-collapse: collapse; width: '.$tableWidth.'" border="1">';
    $table.='<tr>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">LIBELLE</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">VOTE</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">TRANSFERTS</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">CREDIT APRES TRANSFERT</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">BUDGETAIRE</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">JURIDIQUE</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">LIQUIDA TION</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">ORDONNANCE MENT</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">PAIEMENT</th>
    <th style="border: 1px solid #000; text-align: center; font-size: 10px;">DECAISSE MENT</th>
    </tr>';
    foreach ($getData as $key)
    {
      $get_div = $this->getBindParms("LIBELLE_DIVISION","class_fonctionnelle_division","DIVISION_ID=".$key->DIVISION_ID."",'1');
      $get_div=str_replace("\'","'",$get_div);
      $division=$this->ModelPs->getRequeteOne($callpsreq,$get_div);
      $params_infos_division=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
      $params_infos_division=str_replace('\"','"',$params_infos_division);
      $infos_sup_division=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_division);

      $montant_total="SUM(BUDGET_ANNUEL) AS total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total";
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total";
      }
      $params_div=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.'','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_division=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE_ACTION=floatval($total_division['total']);


      //Montant transferé
      $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.DIVISION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION_ACTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $MONTANT_TRANSFERT_RESTE = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

      if($MONTANT_TRANSFERT_RESTE >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['DIVISION_ID']==$mont_recep['DIVISION_ID'])
      {
        $MONTANT_TRANSFERT = $MONTANT_RECEPTION_ACTION;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
      }

      $MONTANT_ENGAGE=!empty($infos_sup_division['MONTANT_ENGAGE']) ? $infos_sup_division['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup_division['MONTANT_JURIDIQUE']) ? $infos_sup_division['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup_division['MONTANT_LIQUIDATION']) ? $infos_sup_division['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup_division['MONTANT_ORDONNANCEMENT']) ? $infos_sup_division['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup_division['PAIEMENT']) ? $infos_sup_division['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT=!empty($infos_sup_division['MONTANT_DECAISSEMENT']) ? $infos_sup_division['MONTANT_DECAISSEMENT'] :'0';

      $table .= '<tr>';
      $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $key->CODE_DIVISION . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($BUDGET_VOTE_ACTION,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_TRANSFERT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ENGAGE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_JURIDIQUE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_LIQUIDATION,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($PAIEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT,0,","," ") . '</td>';
      $table .= '</tr>';
      $table.='<tr>';
      $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $division['LIBELLE_DIVISION'] . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($BUDGET_VOTE_ACTION,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_TRANSFERT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ENGAGE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_JURIDIQUE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_LIQUIDATION,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($PAIEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT,0,","," ") . '</td>';
      $table .= '</tr>';

      $params_grp=$this->getBindParms('DISTINCT groupe.GROUPE_ID,groupe.CODE_GROUPE,groupe.LIBELLE_GROUPE','ptba_tache JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=ptba_tache.GROUPE_ID','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.$critere_group,'1');
      $params_grp=str_replace('\"', '"',$params_grp);
      $groupes= $this->ModelPs->getRequete($callpsreq,$params_grp);


      foreach ($groupes as $key_grp)
      {
        $params_total_vote=$this->getBindParms($montant_total,'ptba_tache','ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' ','PTBA_TACHE_ID ASC');
        $params_total_vote=str_replace('\"','"',$params_total_vote);

        $total_groupe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_vote);
        
        $BUDGET_VOTE_GROUP=intval($total_groupe['total']);

        $params_intitule_grp=$this->getBindParms('LIBELLE_GROUPE','class_fonctionnelle_groupe','GROUPE_ID='.$key_grp->GROUPE_ID,'1');
        $params_intitule_grp=str_replace('\"', '"',$params_intitule_grp);
        $intitule_groupe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_grp);

        $params_infos_group=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
        $params_infos_group=str_replace('\"','"',$params_infos_group);
        $infos_sup_group=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_group);


        //Montant transferé
        $param_mont_trans_grp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_trans_grp=str_replace('\"','"',$param_mont_trans_grp);
        $mont_transf_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_grp);
        $MONTANT_TRANSFERT_G=floatval($mont_transf_grp['MONTANT_TRANSFERT']);

        //Montant receptionn
        $param_mont_recep_grp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.GROUPE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' '.$tranch_transf,'1');
        $param_mont_recep_grp=str_replace('\"','"',$param_mont_recep_grp);
        $mont_recep_grp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_grp);
        $MONTANT_RECEPTION_G=floatval($mont_recep_grp['MONTANT_RECEPTION']);


        $MONTANT_TRANSFERT_GROUP_RESTE = (floatval($MONTANT_TRANSFERT_G) - floatval($MONTANT_RECEPTION_G));

        if($MONTANT_TRANSFERT_GROUP_RESTE >= 0)
        {
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE;
        }else{
          $MONTANT_TRANSFERT_GROUP=$MONTANT_TRANSFERT_GROUP_RESTE*(-1);
        }

        $CREDIT_APRES_TRANSFERT_GROUP=(floatval($BUDGET_VOTE_GROUP) - floatval($MONTANT_TRANSFERT_G)) + floatval($MONTANT_RECEPTION_G);

        if($CREDIT_APRES_TRANSFERT_GROUP < 0){
          $CREDIT_APRES_TRANSFERT_GROUP = $CREDIT_APRES_TRANSFERT_GROUP*(-1);
        }

        if($mont_transf_grp['GROUPE_ID']==$mont_recep_grp['GROUPE_ID'])
        {
          $MONTANT_TRANSFERT_GROUP = $MONTANT_RECEPTION_G;
          $CREDIT_APRES_TRANSFERT_GROUP = floatval($BUDGET_VOTE_GROUP) + (floatval($MONTANT_RECEPTION_G) - floatval($MONTANT_TRANSFERT_G));
        }

        $MONTANT_ENGAGE_GROUP=!empty($infos_sup_group['MONTANT_ENGAGE']) ? $infos_sup_group['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_GROUP=!empty($infos_sup_group['MONTANT_JURIDIQUE']) ? $infos_sup_group['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_GROUP=!empty($infos_sup_group['MONTANT_LIQUIDATION']) ? $infos_sup_group['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_GROUP=!empty($infos_sup_group['MONTANT_ORDONNANCEMENT']) ? $infos_sup_group['MONTANT_ORDONNANCEMENT'] : '0';
        $PAIEMENT_GROUP=!empty($infos_sup_group['PAIEMENT']) ? $infos_sup_group['PAIEMENT'] : '0';
        $MONTANT_DECAISSEMENT_GROUP=!empty($infos_sup_group['MONTANT_DECAISSEMENT']) ? $infos_sup_group['MONTANT_DECAISSEMENT'] :'0';

        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $key_grp->CODE_GROUPE . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($BUDGET_VOTE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_TRANSFERT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ENGAGE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_JURIDIQUE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_LIQUIDATION_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($PAIEMENT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT_GROUP,0,","," ") . '</td>';
        $table .= '</tr>';

        $table.='<tr>';
        $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $intitule_groupe['LIBELLE_GROUPE'] . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($BUDGET_VOTE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_TRANSFERT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ENGAGE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_JURIDIQUE_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_LIQUIDATION_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($PAIEMENT_GROUP,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000;  font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT_GROUP,0,","," ") . '</td>';
        $table .= '</tr>';

        //export classe par  rapport au group
        $params_class=$this->getBindParms('DISTINCT classe.CLASSE_ID,classe.CODE_CLASSE','ptba_tache JOIN class_fonctionnelle_classe classe ON classe.CLASSE_ID=ptba_tache.CLASSE_ID','ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.$critere_classe,'classe.CLASSE_ID ASC');
        $params_class=str_replace('\"', '"',$params_class);
        $classes= $this->ModelPs->getRequete($callpsreq,$params_class);

        foreach ($classes as $key_classe)
        {

          $params_total_classe_vote=$this->getBindParms($montant_total,'ptba_tache','DIVISION_ID='.$key->DIVISION_ID.' AND GROUPE_ID='.$key_grp->GROUPE_ID.' AND CLASSE_ID='.$key_classe->CLASSE_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_classe_vote=str_replace('\"','"',$params_total_classe_vote);

          $total_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_total_classe_vote);
          
          $BUDGET_VOTE_CLASSE=intval($total_classe['total']);

          $params_intitule_class=$this->getBindParms('LIBELLE_CLASSE','class_fonctionnelle_classe','CODE_CLASSE='.$key_classe->CODE_CLASSE,'1');
          $params_intitule_class=str_replace('\"', '"',$params_intitule_class);
          $intitule_classe= $this->ModelPs->getRequeteOne($callpsreq,$params_intitule_class);

          $params_infos_classe=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.DIVISION_ID='.$key->DIVISION_ID.' AND ptba_tache.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba_tache.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$critere_tranche.''.$critere_anne.''.$critere_date,'1');
          $params_infos_classe=str_replace('\"','"',$params_infos_classe);
          $infos_sup_classe=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_classe);

          
          //Montant transferé
          $param_mont_trans_cl = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_trans_cl=str_replace('\"','"',$param_mont_trans_cl);
          $mont_transf_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_cl);
          $MONTANT_TRANSFERT_CL=floatval($mont_transf_cl['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_cl = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CLASSE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.DIVISION_ID='.$key->DIVISION_ID.' AND ptba.GROUPE_ID='.$key_grp->GROUPE_ID.' AND ptba.CLASSE_ID='.$key_classe->CLASSE_ID.' '.$tranch_transf,'1');
          $param_mont_recep_cl=str_replace('\"','"',$param_mont_recep_cl);
          $mont_recep_cl=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_cl);
          $MONTANT_RECEPTION_CL=floatval($mont_recep_cl['MONTANT_RECEPTION']);


          $MONTANT_TRANSFERT_CLASSE_RESTE = (floatval($MONTANT_TRANSFERT_CL) - floatval($MONTANT_RECEPTION_CL));

          if($MONTANT_TRANSFERT_CLASSE_RESTE >= 0)
          {
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE;
          }else{
            $MONTANT_TRANSFERT_CLASSE=$MONTANT_TRANSFERT_CLASSE_RESTE*(-1);
          }

          $CREDIT_APRES_TRANSFERT_CLASSE=(floatval($BUDGET_VOTE_CLASSE) - floatval($MONTANT_TRANSFERT_CL)) + floatval($MONTANT_RECEPTION_CL);

          if($CREDIT_APRES_TRANSFERT_CLASSE < 0){
            $CREDIT_APRES_TRANSFERT_CLASSE = $CREDIT_APRES_TRANSFERT_CLASSE*(-1);
          }

          if($mont_transf_cl['CLASSE_ID']==$mont_recep_cl['CLASSE_ID'])
          {
            $MONTANT_TRANSFERT_CLASSE = $MONTANT_RECEPTION_CL;
            $CREDIT_APRES_TRANSFERT_CLASSE = floatval($BUDGET_VOTE_CLASSE) + (floatval($MONTANT_RECEPTION_CL) - floatval($MONTANT_TRANSFERT_CL));
          }
          
          $MONTANT_ENGAGE_CLASSE=!empty($infos_sup_classe['MONTANT_ENGAGE']) ? $infos_sup_classe['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_CLASSE=!empty($infos_sup_classe['MONTANT_JURIDIQUE']) ? $infos_sup_classe['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_CLASSE=!empty($infos_sup_classe['MONTANT_LIQUIDATION']) ? $infos_sup_classe['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_ORDONNANCEMENT']) ? $infos_sup_classe['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_CLASSE=!empty($infos_sup_classe['PAIEMENT']) ? $infos_sup_classe['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_CLASSE=!empty($infos_sup_classe['MONTANT_DECAISSEMENT']) ? $infos_sup_classe['MONTANT_DECAISSEMENT'] :'0';

          $table .= '<tr>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $key_classe->CODE_CLASSE . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($BUDGET_VOTE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_TRANSFERT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_ENGAGE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_JURIDIQUE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_LIQUIDATION_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($PAIEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '</tr>';

          $table .= '<tr>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . $intitule_classe['LIBELLE_CLASSE'] . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($BUDGET_VOTE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_TRANSFERT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($CREDIT_APRES_TRANSFERT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_ENGAGE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_JURIDIQUE_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_LIQUIDATION_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_ORDONNANCEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($PAIEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 10px;">' . number_format($MONTANT_DECAISSEMENT_CLASSE,0,","," ") . '</td>';
          $table .= '</tr>';
        }
      }
    }
    $table .= '</table>';

    $html = '<h3><center>CIRCUIT DES DEPENSES</center></h3>';
    $html .= '<h4><center>Classification fonctionnelle</center></h4>';
    $html .= '<h5><center>Exercice ' . $exercice . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $phrase2 . '</center></h5>' . $table;

    $dompdf->loadHtml($html);

    // Rendre le PDF (par défaut, il sera généré dans le répertoire système temporaire)
    $dompdf->render();

    // Télécharger le PDF
    $dompdf->stream('fonctionnelle.pdf', ['Attachment' => 'Classification fonctionnelle']);
  }

  function get_date_limit()
  {
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');

    $ann=$this->get_annee_budgetaire();

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    //annee budgetaire: mettre par défaut année en cours
    $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams_anne);
    $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];

    if($ann==$ANNEE_BUDGETAIRE_ID)
    {
      if(empty($TRIMESTRE_ID) || $TRIMESTRE_ID==5)
      {
        $date_start= substr($exercice,0,4).'-07-01';
        $date_fin= date('Y-m-d');

      }
      elseif(!empty($TRIMESTRE_ID) && $TRIMESTRE_ID!=5)
      {

        $tranche="SELECT TRIMESTRE_ID,DATE_DEBUT as debut,DATE_FIN as fin FROM trimestre WHERE TRIMESTRE_ID=".$TRIMESTRE_ID;
        $getTranchee = 'CALL getTable("'.$tranche.'");';

        $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

        $annes = ($TRIMESTRE_ID == 1 || $TRIMESTRE_ID == 2) ? substr($exercice,0,4) : substr($exercice, 5);
        $datedebut= $annes.'-'.$getTranche['debut'];
        $dateFin= $annes.'-'.$getTranche['fin'];
        $date_start=$datedebut;
        $date_fin=$dateFin;
      }
    }
    else
    {
      if(empty($TRIMESTRE_ID) || $TRIMESTRE_ID==5)
      {
        $date_start= substr($exercice,0,4).'-07-01';
        $date_fin= substr($exercice,5).'-06-30';
      }
      elseif(!empty($TRIMESTRE_ID) && $TRIMESTRE_ID!=5)
      {

        $tranche="SELECT TRIMESTRE_ID,DATE_DEBUT as debut,DATE_FIN as fin FROM trimestre WHERE TRIMESTRE_ID=".$TRIMESTRE_ID;
        $getTranchee = 'CALL getTable("'.$tranche.'");';

        $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

        $annes = ($TRIMESTRE_ID == 1 || $TRIMESTRE_ID == 2) ? substr($exercice,0,4) : substr($exercice, 5);
        $datedebut= $annes.'-'.$getTranche['debut'];
        $dateFin= $annes.'-'.$getTranche['fin'];
        $date_start=$datedebut;
        $date_fin=$dateFin;
      }
      
    }

    $output = array('status' => TRUE ,'datedebut' => $date_start , 'datefin' => $date_fin);
    return $this->response->setJSON($output);

  }
}
?>