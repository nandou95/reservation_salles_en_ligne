<?php
namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use App\Controllers\Login_Ptba;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 /* Rapport d'allocation du budget par institution
 * claude@mediabox.bi
 * le 18/09/2023
 * Amelioré par ninette@mediabox.bi
 * Le 25/10/2023
 */ 
  //Appel de l'espace de nom du Controllers
 class Proportion_allocation_institution extends BaseController
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
  /**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilise
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
   */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }


  public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
      {
       $db = db_connect();
       $columnselect = str_replace("\'", "'", $columnselect);
       $table = str_replace("\'", "'", $table);
       $where = str_replace("\'", "'", $where);
       $orderby = str_replace("\'", "'", $orderby);
       $Limit = str_replace("\'", "'", $Limit);
       $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), 
         $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
         $bindparams = str_replace('\"', '"', $bindparams);
         return $bindparams;
       }

  public function index()
  { 
    $db=db_connect();
    $data=$this->urichk();
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $requete_type="SELECT  TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Institution','Ministere') as Name FROM `inst_institutions` WHERE 1 group by TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Institution','Ministere')   ";
    $user_id ='';
    $type_connect ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_user_req=("SELECT `PROFIL_ID` FROM `user_users` WHERE USER_ID=".$user_id." AND `IS_ACTIVE`=1");
      $profil_user=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user_req.'")');
      $user_affectation=("SELECT user_affectaion.`INSTITUTION_ID` FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
      $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
      $nombre=count($user_affectations);
      $cond_affectations="";
      if ($nombre>0) {
        if ($nombre==1) {
          foreach ($user_affectations as $value) {
           $cond_affectations=" AND INSTITUTION_ID= ".$value->INSTITUTION_ID;
         }
       }else if ($nombre>1){
        $inst="(";
      foreach ($user_affectations as $value) {
          $inst.=$value->INSTITUTION_ID.",";
        }
       //Enlever la dernier virgule
        $inst = substr($inst, 0, -1);
        $inst=$inst.")";
        $cond_affectations.=" AND INSTITUTION_ID IN ".$inst;
      }
    }else{
    return redirect('Login_Ptba');

    }
  }   
  else
  {
    return redirect('Login_Ptba');
  }
  $inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';
  $data['inst_connexion']=$inst_connexion;
    //L'id de l'année budgétaire actuelle
  $data['ann_actuel_id'] = $this->get_annee_budgetaire();

    //Selection de l'année budgétaire
  $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID>=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
  $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');

  return view('App\Modules\dashboard\Views\Proportion_allocation_institution_View',$data);
}

//listing
public function listing($value = 0)
{
  $db = db_connect();
  $session  = \Config\Services::session();
  $TRIMESTRE= $this->request->getPost('TRIMESTRE');
  $ANNEE_BUDGETAIRE_ID= $this->request->getPost('ANNEE_BUDGETAIRE_ID');
  $critere1="";
  $critere2="";
  if(!empty($ANNEE_BUDGETAIRE_ID))
  {
    $critere1=' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
  }
  $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
  $var_search = str_replace("'", "\'", $var_search);
  $var_search=$this->str_replacecatego($var_search);
  $group = "";
  $critaire = "";
  $limit = 'LIMIT 0,1000';
  if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';

  $order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','DESC_TACHE',1);

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_MINISTERE ASC';

  $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR inst_institutions_actions.LIBELLE_ACTION LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%")') : '';

  $critaire = $critere1.' '.$critere2;
  $conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
  $conditionsfilter = $critaire.' '.$search.' '.$group;
  $requetedebase = 'SELECT PTBA_TACHE_ID,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,DESC_TACHE,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID  JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire on inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 '.$critere1.'';
  $requetedebase = str_replace("'", "\'", $requetedebase);
  $requetedebases = $requetedebase . ' ' . $conditions;
  $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
  $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
  $fetch_projets = $this->ModelPs->datatable($query_secondaire);
  $data = array();
  $u = 1;
  $stat ='';
  foreach ($fetch_projets as $row)
  {
    $institution = (mb_strlen($row->DESCRIPTION_INSTITUTION) > 10) ? (mb_substr($row->DESCRIPTION_INSTITUTION, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>') : $row->DESCRIPTION_INSTITUTION;
    $programme = (mb_strlen($row->INTITULE_PROGRAMME) > 10) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;
    $action = (mb_strlen($row->LIBELLE_ACTION) > 10) ? (mb_substr($row->LIBELLE_ACTION, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_ACTION;

    $taches = (mb_strlen($row->DESC_TACHE) > 10) ? (mb_substr($row->DESC_TACHE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

     $activites = (mb_strlen($row->DESC_PAP_ACTIVITE) > 10) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

     $codes = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 10) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->PTBA_TACHE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

    if($TRIMESTRE == 1)
    {
      $mont_vote = floatval($row->BUDGET_T1);
    }
    elseif($TRIMESTRE == 2)
    {
      $mont_vote = floatval($row->BUDGET_T2);
    }
    elseif($TRIMESTRE == 3)
    {
      $mont_vote = floatval($row->BUDGET_T3);
    }
    elseif($TRIMESTRE == 4)
    {
      $mont_vote = floatval($row->BUDGET_T4);
    }
    else{

      $mont_vote = floatval($row->BUDGET_T1) + floatval($row->BUDGET_T2) + floatval($row->BUDGET_T3) + floatval($row->BUDGET_T4);
    }
    $sub_array = array();
    $sub_array[] = $u++;
    $sub_array[] = $institution;
    $sub_array[] = $programme;
    $sub_array[] = $action;
    $sub_array[] = $codes;
    $sub_array[] = $activites;
    $sub_array[] = $taches;
    $sub_array[] = number_format($mont_vote,2,","," ");
    $data[] = $sub_array;
  }
  $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
  $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
  $output = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => count($recordsTotal),
    "recordsFiltered" => count($recordsFiltered),
    "data" => $data,
  );
  return $this->response->setJSON($output);
}

function detail_proportion()
{
  $data=$this->urichk();
  $db=db_connect(); 
  $session  = \Config\Services::session();
  $KEY=$this->request->getPost('key');
  $TRIMESTRE=$this->request->getPost('TRIMESTRE');
  $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
  $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
  $cond1='';
  $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
  if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
  {
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
    $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
  }
  else
  {
    return redirect('Login_Ptba');
  }
  $trimestres='`BUDGET_T1` +`BUDGET_T2` + `BUDGET_T3` + `BUDGET_T4`';
  $cond='';
  if(!empty($ANNEE_BUDGETAIRE_ID))
  {
    $cond=' AND ptba_tache.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
  }
  $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
  $var_search =str_replace("'","",$var_search);
  $query_principal="SELECT inst_institutions.`INSTITUTION_ID`,inst_institutions_programmes.INTITULE_PROGRAMME ,DESCRIPTION_INSTITUTION ,`BUDGET_T1`,`BUDGET_T2`,`BUDGET_T3`,`BUDGET_T4`,DESC_TACHE, date_format(act.DATE_DEMANDE,'%d-%m-%Y') as dat FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID LEFT JOIN execution_budgetaire act ON act.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=act.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID WHERE 1 ".$cond." ".$cond1." ";
  $limit='LIMIT 0,10';
  if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';
  $order_column=array(1,'INTITULE_PROGRAMME','DESC_TACHE',1,'act.DATE_DEMANDE');
  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY INTITULE_PROGRAMME ASC';
 $add_search=" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%'";
 $search = !empty($_POST['search']['value']) ? (" AND ( inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%')") : '';
 $critere=" AND  inst_institutions.INSTITUTION_ID=".$KEY;
 $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
 $query_filter=$query_principal.' '.$critere.'  '.$search;
 $query_secondaire = 'CALL `getTable`("' . $conditions . '");';
 $fetch_data = $this->ModelPs->datatable($query_secondaire);
 $u=0;
 $data = array();
 foreach ($fetch_data as $row) 
 {
   $u++;
   $intrant=array();
   $budget=0;
   if (!empty($TRIMESTRE)) {
    if ($TRIMESTRE==1) {
      $budget=$row->BUDGET_T1;
    }elseif ($TRIMESTRE==2) {
      $budget=$row->BUDGET_T2;
    }elseif($TRIMESTRE==3){
      $budget=$row->BUDGET_T3;
    }elseif($TRIMESTRE==4){
     $budget=$row->BUDGET_T4;
   }elseif ($TRIMESTRE==5) {
    $budget=$row->BUDGET_T1+$row->BUDGET_T2+$row->BUDGET_T3+$row->BUDGET_T4;
  }
}
$intrant[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
if (strlen($row->INTITULE_PROGRAMME) > 37){
  $intrant[] = mb_substr($row->INTITULE_PROGRAMME, 0,37) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
   }else{
  $intrant[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
 }
if (strlen($row->DESC_TACHE) > 32){
  $intrant[] = mb_substr($row->DESC_TACHE, 0,32) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
 }else{
  $intrant[] ='<font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font>';
}
$intrant[] ='<center><font color="#000000" size=2><label>'.number_format($budget,0,',',' ').'</label></font> </center>';
$data[] = $intrant;        
}
$recordsTotal ="CALL `getTable`('" . $query_principal . "');";
$recordsFiltered ="CALL `getTable`('" . $query_filter . "');";
$output = array(
 "draw" => intval($_POST['draw']),
 "recordsTotal" =>count($this->ModelPs->datatable($query_principal)),
 "recordsFiltered" =>count($this->ModelPs->datatable($query_filter)),
 "data" => $data
);
echo json_encode($output);
}
 //Fonction pou appel des series et hichart
public function get_rapport()
{
  $data=$this->urichk();
  $db = db_connect();
  $session  = \Config\Services::session();
  $TRIMESTRE=$this->request->getVar('TRIMESTRE');
  $NIVEAU_VISION=$this->request->getVar('NIVEAU_VISION');
  $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
  $inst_conn=$this->request->getVar('inst_conn');
  $ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');
  $user_id =$inst_conn;
  $prof_connect ='';
  $type_connect ='';
  $cond_affectations="";
  if(!empty($user_id))
  {
    $profil_user_req=("SELECT `PROFIL_ID` FROM `user_users` WHERE USER_ID=".$user_id." AND `IS_ACTIVE`=1");
    $profil_user=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user_req.'")');
    $user_affectation=("SELECT user_affectaion.`INSTITUTION_ID` FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
    $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
    $nombre=count($user_affectations);
    if($nombre>0) {
      if ($nombre==1) {
        foreach ($user_affectations as $value) {
          $cond_affectations=" AND inst_institutions.INSTITUTION_ID= ".$value->INSTITUTION_ID;
        }
      }else if ($nombre>1){
        $inst="(";
        foreach ($user_affectations as $value) {
          $inst.=$value->INSTITUTION_ID.",";
        }
     //Enlever la dernier virgule
        $inst = substr($inst, 0, -1);
        $inst=$inst.")";
        $cond_affectations.=" AND inst_institutions.INSTITUTION_ID IN ".$inst;
      }
    }else{
      return redirect('Login_Ptba');
    }
  }   
  else
  {
    return redirect('Login_Ptba');
  }
  $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
  $trimestres='`BUDGET_T1` +`BUDGET_T2` + `BUDGET_T3` + `BUDGET_T4`';
  if (!empty($TRIMESTRE)){
    if ($TRIMESTRE==1) {
      $trimestres='`BUDGET_T1`';
    }elseif ($TRIMESTRE==2) {
      $trimestres='`BUDGET_T2`';
    }elseif($TRIMESTRE==3){
      $trimestres='`BUDGET_T3`';
    }elseif($TRIMESTRE==4){
      $trimestres='`BUDGET_T4`';
    }
  }
  $cond="";
  $cond=" AND inst_institutions.INSTITUTION_ID=".$INSTITUTION_ID;
  $critere1="";
  if(!empty($ANNEE_BUDGETAIRE_ID))
  {
   $critere1=' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
  }
  $alloc_budget=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.DESCRIPTION_INSTITUTION as NAME,  SUM(".$trimestres.") AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond_affectations." ".$critere1." GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY inst_institutions.INSTITUTION_ID ASC");

    $tot=("SELECT SUM(".$trimestres.") AS TOT FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$critere1.""); 
      $budget_alloc=$this->ModelPs->getRequete(' CALL getTable("'.$alloc_budget.'")');
      $bg_tot=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tot.'")');
      $data_budget='';
      $data_total=0;
      $px=0;
      foreach ($budget_alloc as $value)
      {
      if ($bg_tot['TOT']>0) {
      $px=($value->taux)*100/$bg_tot['TOT'];
       }
      $data_budget.="{name:'".$this->str_replacecatego($value->NAME)."', y:".$value->taux.",color:'#2E8B57',key:".$value->ID.",key2:2},";

       $data_total=$data_total+$value->taux;
     } 
    

    $rapp="<script type=\"text/javascript\">
    Highcharts.chart('container', {
      chart: {
        type: 'column'
        },
        title: {
        text: '".lang("messages_lang.titre_proportion_allocation1")." <br> ".number_format($data_total,0,',',' ')." BIF',
             },  
          subtitle: {
              text: ''
               },
            xAxis: {
              type: 'category',
              crosshair: true
              },
              yAxis: {
                min: 0,
                title: {
                  text: ''
                }
                },
                tooltip: {
                  headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
                  pointFormat: '<tr><td style=\"color:{series.color};padding:0\">',
                  shared: true,
                  useHTML: true
                  },
                  plotOptions: {
                    column: {
                      pointPadding: 0.2,
                      borderWidth: 0,
                      depth: 40,
                      cursor:'pointer',
                      point:{
                        events: {
                          click: function(){
                            if(this.key2==2){
                              $(\"#idpro\").html(\" ".lang("messages_lang.Actions")." \");
                              $(\"#idcod\").html(\" ".lang("messages_lang.budget_vote_detail")."\");
                              $(\"#idobj\").html(\"".lang("messages_lang.labelle_programme")."\");
                              $(\"#titre\").html(\"".lang("messages_lang.action_list")."\");
                              }else if(this.key2==3){
                                $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                $(\"#titre\").html(\"".lang("messages_lang.budget_vote_detail")."\");
                                }else if(this.key2==5){
                                  $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                  $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                  $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                  $(\"#titre\").html(\"".lang("messages_lang.budget_vote_detail")."\");
                                  }else if(this.key2==6){
                                    $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                    $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                    $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                    $(\"#titre\").html(\"".lang("messages_lang.budget_vote_detail")."\");
                                    }else if(this.key2==1){
                                      $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                      $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                      $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                      $(\"#titre\").html(\"".lang("messages_lang.budget_vote_detail")."\");
                                      }else{
                                        $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                        $(\"#idcod\").html(\"".lang("messages_lang.objectif_programm")."\");
                                        $(\"#idobj\").html(\" ".lang("messages_lang.code_programm")." \");
                                        $(\"#titre\").html(\"".lang("messages_lang.budget_vote_detail")."\");
                                      }
                                      $(\"#Budget\").html(\" ".lang("messages_lang.budget_total")."\");
                                      $(\"#myModal\").modal('show');
                                      var row_count ='1000000';
                                      $(\"#mytable\").DataTable({
                                        \"processing\":true,
                                        \"serverSide\":true,
                                        \"bDestroy\": true,
                                        \"ajax\":{
                                          url:\"".base_url('dashboard/Dashbord_General_Ptba/detail_general_vote')."\",
                                          type:\"POST\",
                                          data:{
                                        key:this.key,
                                        key2:this.key2,
                                        INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                        TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                        PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                        ACTION_ID:$('#ACTION_ID').val(),
                                        LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                        PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),
                                          }
                                          },
                                          lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
                                          pageLength:5,
                                          \"columnDefs\":[{
                                            \"targets\":[],
                                            \"orderable\":false
                                            }],
                                            dom: 'Bfrtlip',
                                            buttons: [
                                            'excel','pdf'
                                            ],
                                            language: {
                                              \"sProcessing\":     \"Traitement en cours...\",
                                              \"sSearch\":         \"Rechercher&nbsp;:\",
                                              \"sLengthMenu\":     \"Afficher _MENU_ &eacute;l&eacute;ments\",
                                              \"sInfo\":           \"Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments\",
                                              \"sInfoEmpty\":      \"Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment\",
                                              \"sInfoFiltered\":   \"(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)\",
                                              \"sInfoPostFix\":    \"\",
                                              \"sLoadingRecords\": \"Chargement en cours...\",
                                              \"sZeroRecords\":    \"Aucun &eacute;l&eacute;ment &agrave; afficher\",
                                              \"sEmptyTable\":     \"Aucune donn&eacute;e disponible dans le tableau\",
                                              \"oPaginate\": {
                                                \"sFirst\":      \"Premier\",
                                                \"sPrevious\":   \"Pr&eacute;c&eacute;dent\",
                                                \"sNext\":       \"Suivant\",
                                                \"sLast\":       \"Dernier\"
                                                },
                                                \"oAria\": {
                                                  \"sSortAscending\":  \": activer pour trier la colonne par ordre croissant\",
                                                  \"sSortDescending\": \": activer pour trier la colonne par ordre d&eacute;croissant\"
                                                }
                                              }
                                              });
                                            }
                                          }
                                          },
                                          dataLabels: {
                                            enabled: true,
                                            format: '{point.y:.3f} %'  
                                            },
                                            showInLegend: false
                                          }
                                          }, 
                                          credits: {
                                            enabled: true,
                                            href: \"\",
                                            text: \"Mediabox\"
                                            },
                                            series: [
                                            {
                                              colorByPoint: true,
                                              name:'".lang("messages_lang.allocation_budget")."',
                                              data: [".$data_budget."]
                                            }
                                            ]
                                            });
                                            </script>
                                            ";
     
                echo json_encode(array('rapp'=>$rapp));
               }
         //Fonction pour stringer
        function str_replacecatego($name)
            {
          $catego=str_replace("'"," ",$name);
          $catego=str_replace("  "," ",$catego);
          $catego=str_replace("\n"," ",$catego);
          $catego=str_replace("\t"," ",$catego);
          $catego=str_replace("\r"," ",$catego);
          $catego=str_replace("@"," ",$catego);
          $catego=str_replace("&"," ",$catego);
          $catego=str_replace(">"," ",$catego);
          $catego=str_replace("   "," ",$catego);
          $catego=str_replace("?"," ",$catego);
          $catego=str_replace("#"," ",$catego);
          $catego=str_replace("%"," ",$catego);
          $catego=str_replace("%!"," ",$catego);
          $catego=str_replace(""," ",$catego);
          return $catego;
           }
          
              function exporter($TRIMESTRE,$ANNEE_BUDGETAIRE_ID)
                    {
                  $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
                    if(empty($USER_IDD))
                      {
                     return redirect('Login_Ptba/do_logout');
                        }
                      $cond='';
                      if($ANNEE_BUDGETAIRE_ID!=0)
                        {
                      $cond=' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
                        }
                      $db = db_connect();
                      $callpsreq = "CALL getRequete(?,?,?,?);";
                      $getRequete=" SELECT PTBA_TACHE_ID,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,DESC_TACHE,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID  JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire on inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1  ".$cond."";
                        $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
                        $spreadsheet = new Spreadsheet();
                        $sheet = $spreadsheet->getActiveSheet();
                        $sheet->setCellValue('A1', 'INSTITUTION');
                        $sheet->setCellValue('B1', 'PROGRAMME');
                        $sheet->setCellValue('C1', 'ACTION');
                        $sheet->setCellValue('D1', 'NOMENCLATURE BUDGETAIRE');
                        $sheet->setCellValue('E1', 'ACTIVITE');
                        $sheet->setCellValue('F1', 'TACHE');
                        $sheet->setCellValue('G1', 'BUDGET VOTE');
                        $rows = 3;
                        foreach ($getData as $key)
                        {
                          if($TRIMESTRE == 1)
                          {
                            $mont_vote = floatval($key->BUDGET_T1);
                          }
                          elseif($TRIMESTRE == 2)
                          {
                            $mont_vote = floatval($key->BUDGET_T2);
                          }
                        elseif($TRIMESTRE == 3)
                          {
                        $mont_vote = floatval($key->BUDGET_T3);
                          }
                          elseif($TRIMESTRE == 4)
                          {
                         $mont_vote = floatval($key->BUDGET_T4);
                           }
                          else{
                        $mont_vote = floatval($key->BUDGET_T1) + floatval($key->BUDGET_T2) + floatval($key->BUDGET_T3) + floatval($key->BUDGET_T4);
                          }
                        $sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
                        $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
                        $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
                        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
                        $sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
                        $sheet->setCellValue('F' . $rows, $key->DESC_TACHE);
                        $sheet->setCellValue('G' . $rows, $mont_vote);
                        $rows++;
                        } 
                       $writer = new Xlsx($spreadsheet);
                       $writer->save('world.xlsx');
                        return $this->response->download('world.xlsx', null)->setFileName('Allocation Budgetaire Par Institution.xlsx');
                        return redirect('dashboard/Proportion_allocation_institution');
                      }
                    }
                    ?>

