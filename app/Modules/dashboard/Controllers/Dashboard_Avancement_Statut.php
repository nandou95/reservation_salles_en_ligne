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
 /* Tableau de bord d'exécution par phase
 * claude@mediabox.bi
 * le 18/09/2023
 * Amelioré par ninette@mediabox.bi
 * Le 25/10/2023
 * Adapté à la version 3 par Claude claude@mediabox.bi le 04/12/2024
 */ 
  //Appel de l'espace de nom du Controllers
 class Dashboard_Avancement_Statut extends BaseController
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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
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

   $date_select=date('m');
      if($date_select=='01' OR $date_select=='02' OR $date_select=='03'){
        $date_ch='';
        $date_ch1='';
        $date_ch2='checked';
        $date_ch3='';
        $date_ch4='';
      }else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06') {
        $date_ch='';
        $date_ch1='';
        $date_ch2='';
        $date_ch3='checked';
        $date_ch4='';
      }else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' ){
        $date_ch='checked';
        $date_ch1='';
        $date_ch2='';
        $date_ch3='';
        $date_ch4='';
      }else if ($date_select=='10' OR $date_select=='11' OR $date_select=='12' ){
        $date_ch='';
        $date_ch1='checked';
        $date_ch2='';
        $date_ch3='';
        $date_ch4='';
      }else{
        $date_ch='';
        $date_ch1='';
        $date_ch2='';
        $date_ch3='';
        $date_ch4='checked';  
      }
      $data['ch']=$date_ch;       
      $data['ch1']=$date_ch1;
      $data['ch2']=$date_ch2;
      $data['ch3']=$date_ch3;
      $data['ch4']=$date_ch4;

  return view('App\Modules\dashboard\Views\Dashboard_Avancement_Statut_View',$data);
}

 # fonction pour la liste
  public function listing() 
  {
    $data=$this->urichk();
    $db = db_connect();
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $ACTION_ID=$this->request->getPost('ACTION_ID');
    $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
    $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
    $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
    $cond='';
    if(! empty($TYPE_INSTITUTION_ID))
    {
      $cond.=' AND inst.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    }
    if(! empty($INSTITUTION_ID))
    {
      $cond.=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    if(! empty($SOUS_TUTEL_ID))
    {
      $cond.=" AND ptba.SOUS_TUTEL_ID='".$SOUS_TUTEL_ID."'";
    }
    if(! empty($PROGRAMME_ID))
    {
      if ($TYPE_INSTITUTION_ID==2) {

        $cond.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
      }
    }
    if(! empty($ACTION_ID))
    {
      $cond.=" AND ptba.ACTION_ID=".$ACTION_ID; 
    }
    if($LIGNE_BUDGETAIRE !='')
    {
      $cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
    }
    
    $totaux='';
    if ($IS_PRIVATE==1) {
    $cond_trim=" AND BUDGET_T1>0" ;
    $totaux='COALESCE(BUDGET_T1, 0)';
      }else if ($IS_PRIVATE==2) {
    $cond_trim=" AND BUDGET_T2>0" ;
    $totaux='COALESCE(BUDGET_T2, 0)';

    }else if ($IS_PRIVATE==3) {

    $cond_trim=" AND BUDGET_T3>0" ;
    $totaux='COALESCE(BUDGET_T3, 0)';
    }else if ($IS_PRIVATE==4){
      $cond_trim=" AND BUDGET_T4>0" ;
    $totaux='COALESCE(BUDGET_T4, 0)';
    }else{
    $cond_trim=" " ;
    $totaux='COALESCE(BUDGET_T1, 0)+COALESCE(BUDGET_T2, 0)+COALESCE(BUDGET_T3, 0)+COALESCE(BUDGET_T4, 0)';
    }
    if(!empty($PAP_ACTIVITE_ID))
    {
      $cond.=' AND ptba_tache.PAP_ACTIVITE_ID='.$PAP_ACTIVITE_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="SELECT ptba.INSTITUTION_ID,".$totaux." AS VOTE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION,exec.ORDONNANCEMENT,exec.PAIEMENT,exec.DECAISSEMENT,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,pnd_pilier.DESCR_PILIER,pnd_axe.DESCR_AXE_PND,ptba.DESC_TACHE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,programme.`INTITULE_PROGRAMME`,actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE, pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID LEFT JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID LEFT JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID =ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID LEFT JOIN inst_institutions_actions actions ON actions.ACTION_ID = ptba.ACTION_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID  JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID  JOIN pnd_pilier ON pnd_pilier.ID_PILIER=ptba.ID_PILIER  JOIN pnd_axe ON pnd_axe.AXE_PND_ID=ptba.AXE_PND_ID WHERE 1 ".$cond." ".$cond_trim." ";

    $limit='LIMIT 0,10';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column=array(1,'inst.DESCRIPTION_INSTITUTION','programme.`INTITULE_PROGRAMME`','actions.LIBELLE_ACTION','DESC_PAP_ACTIVITE',1,'ligne.CODE_NOMENCLATURE_BUDGETAIRE');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : '';

    $search = !empty($_POST['search']['value']) ? ("AND (
      inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR programme.`INTITULE_PROGRAMME` LIKE '%$var_search%' OR actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' )") : '';
    $conditions=$query_principal.'  '.$search.' '.$order_by.'   '.$limit;
    $query_filter=$query_principal.' '.$search;
    $query_secondaire = 'CALL `getTable`("' . $conditions . '");';
    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $u=0;
    $data = array();
    foreach ($fetch_data as $row) 
    {
      $u++;
      $engagement=array();
      $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
      $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
      $INSTITUTION = (mb_strlen($row->INTITULE_MINISTERE) > 12) ? (mb_substr($row->INTITULE_MINISTERE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_MINISTERE;

      $AXES = (mb_strlen($row->DESCR_AXE_PND) > 12) ? (mb_substr($row->DESCR_AXE_PND, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESCR_AXE_PND.'"><i class="fa fa-eye"></i></a>') : $row->DESCR_AXE_PND;

      $PROGRAMME = (mb_strlen($row->INTITULE_PROGRAMME) > 12) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;

      $ACTION = (mb_strlen($retVal) > 12) ? (mb_substr($retVal, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>') : $retVal;
      $LIGNE_BUDGETAIR= (mb_strlen($row->CODE_NOMENCLATURE_BUDGETAIRE) > 30) ? (mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 30) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 8) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

      $TACHES = (mb_strlen($row->DESC_TACHE) > 8) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;
      $engagement[]=$INSTITUTION;
      $engagement[]=$AXES;
      $engagement[]=$PROGRAMME;
      $engagement[]=$ACTION;
      $engagement[]=$LIGNE_BUDGETAIR;
      $engagement[]=$ACTIVITES;
      $engagement[]=$TACHES;
      $engagement[]=number_format(floatval($row->VOTE),0,',',' ');
      $engagement[]=number_format(floatval($row->ENG_BUDGETAIRE),0,',',' ');
      $engagement[]=number_format(floatval($row->ENG_JURIDIQUE),0,',',' ');
      $engagement[]=number_format(floatval($row->LIQUIDATION),0,',',' ');
      $engagement[]=number_format(floatval($row->ORDONNANCEMENT),0,',',' ');
      $engagement[]=number_format(floatval($row->PAIEMENT),0,',',' ');
      $engagement[]=number_format(floatval($row->DECAISSEMENT),0,',',' ');
      $data[] = $engagement;        
    }
    $recordsTotal ="CALL `getTable`('" . $query_principal . "');";
    $recordsFiltered ="CALL `getTable`('" . $query_filter . "');";
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($this->ModelPs->datatable($query_principal)),
      "recordsFiltered" =>count($this->ModelPs->datatable($query_filter)),
      "data" => $data
    );
    return $this->response->setJSON($output);
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
          $cond_affectations=" AND inst.INSTITUTION_ID= ".$value->INSTITUTION_ID;
        }
      }else if ($nombre>1){
        $inst="(";
        foreach ($user_affectations as $value) {
          $inst.=$value->INSTITUTION_ID.",";
        }
     //Enlever la dernier virgule
        $inst = substr($inst, 0, -1);
        $inst=$inst.")";
        $cond_affectations.=" AND inst.INSTITUTION_ID IN ".$inst;
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
   if ($IS_PRIVATE==1){
        $cond_trim=" AND exec.TRIMESTRE_ID=1" ;
          }elseif ($IS_PRIVATE==2){
        $cond_trim=" AND exec.TRIMESTRE_ID=2" ;
         }else if ($IS_PRIVATE==3){
        $cond_trim=" AND exec.TRIMESTRE_ID=3" ;
         }else if ($IS_PRIVATE==4){
        $cond_trim=" AND exec.TRIMESTRE_ID=4" ;
         }else{
        $cond_trim=" " ;
         }
     $cond="";
     $cond=" AND inst.INSTITUTION_ID=".$INSTITUTION_ID;
     $critere1="";
  if(!empty($ANNEE_BUDGETAIRE_ID))
  {
   $critere1=' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
  }

    $engage11=("SELECT inst.INSTITUTION_ID as ID,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.ENG_BUDGETAIRE>0 ".$critere1." ".$cond_trim.") ENGAGEMENT_budgetaire FROM inst_institutions  WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
    $engage_req11=$this->ModelPs->getRequete(' CALL getTable("'.$engage11.'")');

      $data_engage_total=0;
      $data_engager_req1='';
      $categorie="";
      foreach ($engage_req11 as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_req1.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ENGAGEMENT_budgetaire.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_total=$data_engage_total+$value->ENGAGEMENT_budgetaire;
         }
       $engage_jurique=("SELECT inst.INSTITUTION_ID as ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.ENG_JURIDIQUE>0 ".$critere1." ".$cond_trim.") ENGAGEMENT_JURIDIQUE FROM inst WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_jurique=$this->ModelPs->getRequete(' CALL getTable("'.$engage_jurique.'")');
      $data_engage_jurique_total=0;
      $data_engager_juridique='';
      foreach ($req_engage_jurique as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_juridique.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ENGAGEMENT_JURIDIQUE.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_jurique_total=$data_engage_jurique_total+$value->ENGAGEMENT_JURIDIQUE;
         }


      $engage_liquidation=("SELECT inst.INSTITUTION_ID as ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.LIQUIDATION>0 ".$critere1." ".$cond_trim.") LIQUIDATION FROM inst_institutions inst WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_liquidation=$this->ModelPs->getRequete(' CALL getTable("'.$engage_liquidation.'")');
      $data_engage_liquidation_total=0;
      $data_engager_liquidation='';
      foreach ($req_engage_liquidation as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_liquidation.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->LIQUIDATION.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";

        $data_engage_liquidation_total=$data_engage_liquidation_total+$value->LIQUIDATION;
         }


      $engage_ordonnancement=("SELECT inst.INSTITUTION_ID as ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.ORDONNANCEMENT>0 ".$critere1." ".$cond_trim.") ORDONNANCEMENT FROM inst_institutions inst  WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_ordonnament=$this->ModelPs->getRequete(' CALL getTable("'.$engage_ordonnancement.'")');
      $data_engage_ordonnancement_total=0;
      $data_engager_ordonnancement='';
      foreach ($req_engage_ordonnament as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_ordonnancement.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ORDONNANCEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_ordonnancement_total=$data_engage_ordonnancement_total+$value->ORDONNANCEMENT;
         }

    
     $engage_paiement=("SELECT inst.INSTITUTION_ID as ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.PAIEMENT>0 ".$critere1." ".$cond_trim.") PAIEMENT FROM inst_institutions inst WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");

      $req_engage_paiement=$this->ModelPs->getRequete(' CALL getTable("'.$engage_paiement.'")');

      $data_engage_paiement_total=0;
      $data_engager_paiement='';
      foreach ($req_engage_paiement as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_paiement.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->PAIEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_paiement_total=$data_engage_paiement_total+$value->PAIEMENT;
         }
      
      $engage_decaissement=("SELECT inst.INSTITUTION_ID as ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,(SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire exec  JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID left JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID=ID AND exec.DECAISSEMENT>0 ".$critere1." ".$cond_trim.") DECAISSEMENT FROM inst_institutions inst  WHERE inst.TYPE_INSTITUTION_ID=2 GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_decaissement=$this->ModelPs->getRequete(' CALL getTable("'.$engage_decaissement.'")');
      $data_engage_decaissement_total=0;
      $data_engager_decaissement='';
      foreach ($req_engage_decaissement as $value)
         {
        $categorie.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie.= $rappel."',";        
        $data_engager_decaissement.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->DECAISSEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_decaissement_total=$data_engage_decaissement_total+$value->DECAISSEMENT;

         }

      $rapp="<script type=\"text/javascript\">
      Highcharts.chart('container', {
        chart: {
          type: 'column'  
          },
          title: {
            text: 'Avancement des engagements par Ministère',
            },  
            subtitle: {
              text: ''
              },
              xAxis: {
                categories: [".$categorie."],
                crosshair: true
                },
                yAxis: {
                  min: 0,
                  title: {
                    text: ''
                  }
                  },
                  tooltip: {
                    pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}',
                    footerFormat: '</table>',
                    shared: false,
                    useHTML: true
                    },
                    plotOptions: {
                      column: {
                        pointPadding: 0.01,
                        borderWidth: 0.7,
                        stacking:'normal',
                        depth: 100,
                        cursor:'pointer',
                        point:{
                          events: {
                            click: function(){
                              if(this.key3==1){
                                $(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_budget')."\");
                                }else if(this.key3==3){
                                  $(\"#idpro\").html(\"".lang('messages_lang.labelle_liquidation')."\");
                                  }else if(this.key3==6){
                                    $(\"#idpro\").htmsl(\"  ".lang('messages_lang.labelle_decaisse')."\");
                                    }else if(this.key3==2){
                                      $(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_jud')."\");
                                      }else if(this.key3==5){
                                        $(\"#idpro\").html(\"".lang('messages_lang.labelle_paiement')."\");
                                        }else{
                                          $(\"#idpro\").html(\"".lang('messages_lang.labelle_ordonan')."\");  
                                        }

                                        $(\"#titre\").html(\"".lang('messages_lang.list_activites')." \" +this.series.name);
                                        $(\"##myModal\").modal('show');
                                        var row_count ='1000000';
                                        $(\"#mytable\").DataTable({
                                          \"processing\":true,
                                          \"serverSide\":true,
                                          \"bDestroy\": true,
                                          \"ajax\":{
                                          url:\"".base_url('dashboard/Dashboard_Avancement_Statut/detail_tcd_taux_vote')."\",
                                            type:\"POST\",
                                            data:{
                                         key:this.key,
                                         key2:this.key2,
                                         key3:this.key3,
                                         INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                         TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                         SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                                         IS_PRIVATE:$('#IS_PRIVATE').val(),
                                         ACTIVITE:$('#ACTIVITE').val(),
                                         IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
                                         LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                         PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),
                                            }
                                            },
                                            lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
                                            pageLength: 5,
                                            \"columnDefs\":[{
                                              \"targets\":[],
                                              \"orderable\":false
                                              }],
                                              dom: 'Bfrtlip',
                                              buttons: [
                                              'copy', 'csv', 'excel', 'pdf', 'print'
                                              ],
                                              language: {
                                                \"sProcessing\":     \"".lang('messages_lang.labelle_et_traitement')."...\",
                                                \"sSearch\":         \"".lang('messages_lang.search_button')."&nbsp;:\",
                                                \"sLengthMenu\":     \"".lang('messages_lang.labelle_et_afficher')." _MENU_ ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfo\":           \"".lang('messages_lang.labelle_et_affichage_element')." _START_ ".lang('messages_lang.labelle_et_a')." _END_ sur _TOTAL_ ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_ ".lang('messages_lang.labelle_et_elementtotal').")\",
                                                \"sInfoPostFix\":    \"\",
                                                \"sLoadingRecords\": \"".lang('messages_lang.labelle_et_chargement')."...\",
                                                \"sZeroRecords\":    \"".lang('messages_lang.labelle_et_aucun_element')."\",
                                                \"sEmptyTable\":     \"".lang('messages_lang.labelle_et_aucun_donnee_disponible')."\",
                                                \"oPaginate\": {
                                                  \"sFirst\":      \"".lang('messages_lang.labelle_et_premier')."\",
                                                  \"sPrevious\":   \"".lang('messages_lang.labelle_et_precedent')."\",
                                                  \"sNext\":       \"".lang('messages_lang.labelle_et_suivant')."\",
                                                  \"sLast\":       \"".lang('messages_lang.labelle_et_dernier')."\"
                                                  },
                                                  \"oAria\": {
                                                    \"sSortAscending\":  \": ".lang('messages_lang.labelle_et_trier_colone')."\",
                                                    \"sSortDescending\": \": ".lang('messages_lang.labelle_et_trier_activer_trier')."\"
                                                  }
                                                }

                                                });
                                              }
                                            }
                                            },
                                            dataLabels: {
                                              enabled: true,
                                              format: '{point.y:,.0f}'
                                              },
                                              showInLegend: true
                                            }
                                            }, 
                                            credits: {
                                              enabled: true,
                                              href: \"\",
                                              text: \"Mediabox\"
                                              },

                                          series: [

                                               {
                                              name:'".lang('messages_lang.labelle_eng_budget')." :: ".number_format($data_engage_total,0,',',' ')."',
                                               data: [".$data_engager_req1."]
                                                },
                                                {
                                                name:'".lang('messages_lang.labelle_eng_jud')." :: ".number_format($data_engage_jurique_total,0,',',' ')." ',
                                                data: [".$data_engager_juridique."]
                                                },
                                                {
                                               name:'".lang('messages_lang.labelle_liquidation')." :: ".number_format($data_engage_liquidation_total,0,',',' ')."',
                                               data: [".$data_engager_liquidation."]
                                                },
                                                {
                                             name:'".lang('messages_lang.labelle_ordonan')." :: ".number_format($data_engage_ordonnancement_total,0,',',' ')."',
                                             data: [".$data_engager_ordonnancement."]
                                                 },
                                               {
                                              name:'".lang('messages_lang.labelle_paiement')." :: ".number_format($data_engage_paiement_total,0,',',' ')."',
                                              data: [".$data_engager_paiement."]
                                               },
                                              {
                                              name:'".lang('messages_lang.labelle_decaisse')." :: ".number_format($data_engage_decaissement_total,0,',',' ')."',
                                              data: [".$data_engager_decaissement."]
                                              }
                                              ]
                                          });
                                        </script>
                                        ";


    $engage12=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.ENG_BUDGETAIRE>0 ".$critere1." ".$cond_trim.") ENGAGEMENT_budgetaire FROM inst_institutions  WHERE inst_institutions.TYPE_INSTITUTION_ID=1 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
    $engage_req12=$this->ModelPs->getRequete(' CALL getTable("'.$engage12.'")');

      $data_engage_total2=0;
      $data_engager_req12='';
      $categorie2="";
      foreach ($engage_req12 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_req12.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ENGAGEMENT_budgetaire.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_total2=$data_engage_total2+$value->ENGAGEMENT_budgetaire;
         }
       $engage_jurique2=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.ENG_JURIDIQUE>0 ".$critere1." ".$cond_trim.") ENGAGEMENT_JURIDIQUE FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=1 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_jurique2=$this->ModelPs->getRequete(' CALL getTable("'.$engage_jurique2.'")');
      $data_engage_jurique_total2=0;
      $data_engager_juridique2='';
      foreach ($req_engage_jurique2 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_juridique2.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ENGAGEMENT_JURIDIQUE.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_jurique_total2=$data_engage_jurique_total2+$value->ENGAGEMENT_JURIDIQUE;
         }


      $engage_liquidation2=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.LIQUIDATION>0 ".$critere1." ".$cond_trim.") LIQUIDATION FROM inst_institutions  WHERE inst_institutions.TYPE_INSTITUTION_ID=1 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_liquidation2=$this->ModelPs->getRequete(' CALL getTable("'.$engage_liquidation2.'")');
      $data_engage_liquidation_total2=0;
      $data_engager_liquidation2='';
      foreach ($req_engage_liquidation2 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_liquidation2.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->LIQUIDATION.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_liquidation_total2=$data_engage_liquidation_total2+$value->LIQUIDATION;
         }


      $engage_ordonnancement2=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.ORDONNANCEMENT>0 ".$critere1." ".$cond_trim.") ORDONNANCEMENT FROM inst_institutions  WHERE inst_institutions.TYPE_INSTITUTION_ID=1 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_ordonnament2=$this->ModelPs->getRequete(' CALL getTable("'.$engage_ordonnancement2.'")');
      $data_engage_ordonnancement_total2=0;
      $data_engager_ordonnancement2='';
      foreach ($req_engage_ordonnament2 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_ordonnancement2.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->ORDONNANCEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_ordonnancement_total2=$data_engage_ordonnancement_total2+$value->ORDONNANCEMENT;
         }

    
     $engage_paiement2=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.PAIEMENT>0 ".$critere1."  ".$cond_trim.") PAIEMENT FROM inst_institutions  WHERE inst_institutions.TYPE_INSTITUTION_ID=2 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");

      $req_engage_paiement2=$this->ModelPs->getRequete(' CALL getTable("'.$engage_paiement2.'")');
      $data_engage_paiement_total2=0;
      $data_engager_paiement2='';
      foreach ($req_engage_paiement2 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_paiement2.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->PAIEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";
        $data_engage_paiement_total2=$data_engage_paiement_total2+$value->PAIEMENT;
         }
      
      $engage_decaissement2=("SELECT inst_institutions.INSTITUTION_ID as ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,(SELECT COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire WHERE execution_budgetaire.INSTITUTION_ID=ID AND execution_budgetaire.DECAISSEMENT>0 ".$critere1." ".$cond_trim.") DECAISSEMENT FROM inst_institutions  WHERE inst_institutions.TYPE_INSTITUTION_ID=2 GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY ID ASC");
      $req_engage_decaissement2=$this->ModelPs->getRequete(' CALL getTable("'.$engage_decaissement2.'")');
      $data_engage_decaissement_total2=0;
      $data_engager_decaissement2='';
      foreach ($req_engage_decaissement2 as $value)
         {
        $categorie2.="'";
        $name = (!empty($value->DESCRIPTION_INSTITUTION)) ? $value->DESCRIPTION_INSTITUTION : "Autres";
        $rappel=$this->str_replacecatego($name);
        $categorie2.= $rappel."',";        
        $data_engager_decaissement2.="{name:'".$this->str_replacecatego($value->DESCRIPTION_INSTITUTION)."', y:".$value->DECAISSEMENT.",key:'".$this->str_replacecatego($value->ID)."',key3:1},";

        $data_engage_decaissement_total2=$data_engage_decaissement_total2+$value->DECAISSEMENT;
         }

      $rapp2="<script type=\"text/javascript\">
      Highcharts.chart('container2', {
        chart: {
          type: 'column'  
          },
          title: {
            text: 'Avancement des engagements par Institution',
            },  
            subtitle: {
              text: ''
              },
              xAxis: {
                categories: [".$categorie2."],
                crosshair: true
                },
                yAxis: {
                  min: 0,
                  title: {
                    text: ''
                  }
                  },
                  tooltip: {
                    pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}',
                    footerFormat: '</table>',
                    shared: false,
                    useHTML: true
                    },
                    plotOptions: {
                      column: {
                        pointPadding: 0.05,
                        borderWidth: 0.7,
                        stacking:'normal',
                        depth: 40,
                        cursor:'pointer',
                        point:{
                          events: {
                            click: function(){
                              if(this.key3==1){
                                $(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_budget')."\");
                                }else if(this.key3==3){
                                  $(\"#idpro\").html(\"".lang('messages_lang.labelle_liquidation')."\");
                                  }else if(this.key3==6){
                                    $(\"#idpro\").htmsl(\"  ".lang('messages_lang.labelle_decaisse')."\");
                                    }else if(this.key3==2){
                                      $(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_jud')."\");
                                      }else if(this.key3==5){
                                        $(\"#idpro\").html(\"".lang('messages_lang.labelle_paiement')."\");
                                        }else{
                                          $(\"#idpro\").html(\"".lang('messages_lang.labelle_ordonan')."\");  
                                        }

                                        $(\"#titre\").html(\"".lang('messages_lang.list_activites')." \" +this.series.name);
                                        $(\"##myModal\").modal('show');
                                        var row_count ='1000000';
                                        $(\"##mytable\").DataTable({
                                          \"processing\":true,
                                          \"serverSide\":true,
                                          \"bDestroy\": true,
                                          \"ajax\":{
                                            url:\"".base_url('dashboard/Dashboard_Avancement_Statut/detail_tcd_taux_vote')."\",
                                            type:\"POST\",
                                            data:{
                                         key:this.key,
                                         key2:this.key2,
                                         key3:this.key3,
                                         INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                         TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                         SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                                         IS_PRIVATE:$('#IS_PRIVATE').val(),
                                         ACTIVITE:$('#ACTIVITE').val(),
                                         IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
                                         LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                         PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),
                                            }
                                            },
                                            lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
                                            pageLength: 5,
                                            \"columnDefs\":[{
                                              \"targets\":[],
                                              \"orderable\":false
                                              }],
                                              dom: 'Bfrtlip',
                                              buttons: [
                                              'copy', 'csv', 'excel', 'pdf', 'print'
                                              ],
                                              language: {
                                                \"sProcessing\":     \"".lang('messages_lang.labelle_et_traitement')."...\",
                                                \"sSearch\":         \"".lang('messages_lang.search_button')."&nbsp;:\",
                                                \"sLengthMenu\":     \"".lang('messages_lang.labelle_et_afficher')." _MENU_ ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfo\":           \"".lang('messages_lang.labelle_et_affichage_element')." _START_ ".lang('messages_lang.labelle_et_a')." _END_ sur _TOTAL_ ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                                                \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_ ".lang('messages_lang.labelle_et_elementtotal').")\",
                                                \"sInfoPostFix\":    \"\",
                                                \"sLoadingRecords\": \"".lang('messages_lang.labelle_et_chargement')."...\",
                                                \"sZeroRecords\":    \"".lang('messages_lang.labelle_et_aucun_element')."\",
                                                \"sEmptyTable\":     \"".lang('messages_lang.labelle_et_aucun_donnee_disponible')."\",
                                                \"oPaginate\": {
                                                  \"sFirst\":      \"".lang('messages_lang.labelle_et_premier')."\",
                                                  \"sPrevious\":   \"".lang('messages_lang.labelle_et_precedent')."\",
                                                  \"sNext\":       \"".lang('messages_lang.labelle_et_suivant')."\",
                                                  \"sLast\":       \"".lang('messages_lang.labelle_et_dernier')."\"
                                                  },
                                                  \"oAria\": {
                                                    \"sSortAscending\":  \": ".lang('messages_lang.labelle_et_trier_colone')."\",
                                                    \"sSortDescending\": \": ".lang('messages_lang.labelle_et_trier_activer_trier')."\"
                                                  }
                                                }

                                                });
                                              }
                                            }
                                            },
                                            dataLabels: {
                                              enabled: true,
                                              format: '{point.y:,.0f}'
                                              },
                                              showInLegend: true
                                            }
                                            }, 
                                            credits: {
                                              enabled: true,
                                              href: \"\",
                                              text: \"Mediabox\"
                                              },

                                          series: [

                                               {
                                              name:'".lang('messages_lang.labelle_eng_budget')." :: ".number_format($data_engage_total2,0,',',' ')."',
                                               data: [".$data_engager_req12."]
                                                },
                                                {
                                                name:'".lang('messages_lang.labelle_eng_jud')." :: ".number_format($data_engage_jurique_total2,0,',',' ')." ',
                                                data: [".$data_engager_juridique2."]
                                                },
                                                {
                                               name:'".lang('messages_lang.labelle_liquidation')." :: ".number_format($data_engage_liquidation_total2,0,',',' ')."',
                                               data: [".$data_engager_liquidation2."]
                                                },
                                                {
                                             name:'".lang('messages_lang.labelle_ordonan')." :: ".number_format($data_engage_ordonnancement_total2,0,',',' ')."',
                                             data: [".$data_engager_ordonnancement2."]
                                                 },
                                               {
                                              name:'".lang('messages_lang.labelle_paiement')." :: ".number_format($data_engage_paiement_total2,0,',',' ')."',
                                              data: [".$data_engager_paiement2."]
                                               },
                                              {
                                              name:'".lang('messages_lang.labelle_decaisse')." :: ".number_format($data_engage_decaissement_total2,0,',',' ')."',
                                              data: [".$data_engager_decaissement2."]
                                              }
                                              ]
                                          });
                                        </script>
                                        ";

                echo json_encode(array('rapp'=>$rapp,'rapp2'=>$rapp2));
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
        $catego= preg_replace('/[^a-zA-Z0-9À-ÿ ]/u', '',$catego);
        return $catego;
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


  // exporter dans un fichier excel
  function exporter($TYPE_INSTITUTION_ID='',$INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='',$LIGNE_BUDGETAIRE='',$IS_PRIVATE='' ,$PAP_ACTIVITE_ID='')
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $db = db_connect();
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $criteres="";
    $TYPE_INSTITUTION_ID=$TYPE_INSTITUTION_ID;
    $INSTITUTION_ID=$INSTITUTION_ID;
    $PROGRAMME_ID=$PROGRAMME_ID;
    $ACTION_ID=$ACTION_ID;
    $LIGNE_BUDGETAIRE=$LIGNE_BUDGETAIRE;
    $IS_PRIVATE=$IS_PRIVATE;
    $SOUS_TUTEL_ID=$SOUS_TUTEL_ID;
    $PAP_ACTIVITE_ID=$PAP_ACTIVITE_ID;
    $cond='';
    $cond1='';
    $cond2='';
    $cond3='';
    $cond4='';
    $cond5='';
    $cond6='';
    $cond7='';
    $cond8='';
    if(! empty($TYPE_INSTITUTION_ID) )
    {
      $cond1='AND inst.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    }

    if($INSTITUTION_ID!=0)
    {
      $cond2="AND ptba.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    
    if($PROGRAMME_ID!=0)
    {
      if ($TYPE_INSTITUTION_ID==2) 
      {
        $cond4="AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
      }
    }

    if($ACTION_ID!=0)
    {
    $cond5=" AND ptba.ACTION_ID='".$ACTION_ID."'"; 
    }
    if($LIGNE_BUDGETAIRE !=0)
    {
    $cond6=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
    }
    $totaux='';
    if ($IS_PRIVATE==1) {
    $cond_trim=" AND BUDGET_T1>0" ;
    $totaux='COALESCE(BUDGET_T1, 0)';
      }else if ($IS_PRIVATE==2) {
    $cond_trim=" AND BUDGET_T2>0" ;
    $totaux='COALESCE(BUDGET_T2, 0)';

    }else if ($IS_PRIVATE==3) {

    $cond_trim=" AND BUDGET_T3>0" ;
    $totaux='COALESCE(BUDGET_T3, 0)';
    }else if ($IS_PRIVATE==4){
      $cond_trim=" AND BUDGET_T4>0" ;
    $totaux='COALESCE(BUDGET_T4, 0)';
    }else{
    $cond_trim=" " ;
    $totaux='COALESCE(BUDGET_T1, 0)+COALESCE(BUDGET_T2, 0)+COALESCE(BUDGET_T3, 0)+COALESCE(BUDGET_T4, 0)';
    }
    if(!empty($PAP_ACTIVITE_ID))
    {
      $cond.=' AND ptba.PAP_ACTIVITE_ID='.$PAP_ACTIVITE_ID;
    }
    
    if($PAP_ACTIVITE_ID !=0)
    {
    $cond8=" AND ptba.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
    }
    $cond=$cond1.' '.$cond2.' '.$cond3.' '.$cond4.' '.$cond5.' '.$cond6.' '.$cond7.' '.$cond8;
    $getRequete="SELECT ptba.INSTITUTION_ID,".$totaux." AS VOTE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION,exec.ORDONNANCEMENT,exec.PAIEMENT,exec.DECAISSEMENT,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,pnd_pilier.DESCR_PILIER,pnd_axe.DESCR_AXE_PND,ptba.DESC_TACHE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,programme.`INTITULE_PROGRAMME`,actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache  exec_tache  ON exec.EXECUTION_BUDGETAIRE_ID= exec_tache.EXECUTION_BUDGETAIRE_ID  JOIN ptba_tache ptba ON exec_tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID JOIN pnd_pilier ON pnd_pilier.ID_PILIER=ptba.ID_PILIER JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pnd_axe ON pnd_axe.AXE_PND_ID=ptba.AXE_PND_ID WHERE 1 ";
    
    $getData = $this->ModelPs->datatable('CALL getTable("' . $getRequete . '")');
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'INSTITUTION');
    $sheet->setCellValue('B1', 'AXES');
    $sheet->setCellValue('C1', 'PROGRAMME');
    $sheet->setCellValue('D1', 'ACTION');
    $sheet->setCellValue('E1', 'LIGNE BIDGETAIRE');
    $sheet->setCellValue('F1', 'ACTIVITE');
    $sheet->setCellValue('G1', 'TACHE');
    $sheet->setCellValue('H1', 'BUDGET VOTE');
    $sheet->setCellValue('I1', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('J1', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('K1', 'LIQUIDATION');
    $sheet->setCellValue('L1', 'ORDONNANCEMENT');
    $sheet->setCellValue('M1', 'PAIEMENT');
    $sheet->setCellValue('N1', 'PAIEMENT');
    $rows = 3;
    //boucle pour les institutions
    foreach ($getData as $key)
      {
      $sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
      $sheet->setCellValue('B' . $rows, $key->DESCR_AXE_PND);
      $sheet->setCellValue('C' . $rows, $key->INTITULE_PROGRAMME);
      $sheet->setCellValue('D' . $rows, $key->LIBELLE_ACTION);
      $sheet->setCellValue('E' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      $sheet->setCellValue('F' . $rows, $key->DESC_PAP_ACTIVITE);
      $sheet->setCellValue('G' . $rows, $key->DESC_TACHE);
      $sheet->setCellValue('H' . $rows, $key->VOTE);
      $sheet->setCellValue('I' . $rows, $key->ENG_BUDGETAIRE);
      $sheet->setCellValue('J' . $rows, $key->ENG_JURIDIQUE);
      $sheet->setCellValue('K' . $rows, $key->LIQUIDATION);
      $sheet->setCellValue('L' . $rows, $key->ORDONNANCEMENT);
      $sheet->setCellValue('M' . $rows, $key->PAIEMENT);
      $sheet->setCellValue('L' . $rows, $key->DECAISSEMENT);
      $rows++;
      }
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Avancement des statuts.xlsx');
    return redirect('dashboard/Dashboard_Avancement_Statut');
  }
              
                    }
                    ?>

