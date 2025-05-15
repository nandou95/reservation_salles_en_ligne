<?php
/**
* @author maniratunga.eric@mediabox.bi
* Tableau de bord «grande masse»
le 30/09/2023
*/
//Appel de l'esp\ce de nom du Controllers
namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  ###declaration d'une classe controlleur
class Dashboard_Grande_Masse extends BaseController
{
  protected $session;
  protected $ModelPs;
  ###fonction constructeur
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }
  //fonction qui retourne les couleurs
  public function getcolor() 
  {
    $chars = 'ABCDEF0123456789';
    $color = '#';
    for ( $i= 0; $i < 6; $i++ )
    {
      $color.= $chars[rand(0, strlen($chars) -1)];
    }
    return $color;
  }

  /**
  * fonction pour retourner le tableau des parametre pour le PS pour les selection
  * @param string  $columnselect //colone A selectionner
  * @param string  $table        //table utilisE
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

  //fonction index
   public function index($value='')
   {
    $data=$this->urichk();
    $session  = \Config\Services::session();
    $user_id ='';
    $inst_connect ='';
    $prof_connect ='';
    $type_connect ='';
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_GRANDE_MASSE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

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

$requete_cat="SELECT  DISTINCT TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'".lang("messages_lang.admin_perso")."','".lang("messages_lang.minister")."') as Name FROM `inst_institutions` WHERE 1 ".$cond_affectations." "; 

  $data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_cat.'")');
  $inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';

  $data['TYPE_INSTITUTION_ID']=$this->request->getPost('');

  $data['inst_connexion']=$inst_connexion;

  $date_select='';
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
###L'id de l'année budgétaire actuelle
$data['ann_actuel_id'] = $this->get_annee_budgetaire();
$get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID>=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
$data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
return view('App\Modules\dashboard\Views\Dashboard_Grande_Masse_View',$data);
}


//listing
public function listing($value = 0)
{

  $db = db_connect();
  $session  = \Config\Services::session();
  $TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
  $PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
  $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
  $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
  $ACTION_ID=$this->request->getVar('ACTION_ID');
  $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
  $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
  $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
  $critere1="";
  $critere2="";
  $critere3="";
  $critere4="";
  $critere5="";
  $critere6="";
  $critere7="";
  $critere8="";
  $critere9="";
  $critere10="";
  if(!empty($TYPE_INSTITUTION_ID))
  {
    $critere1="AND inst_institutions.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID;
  }

  if(!empty($INSTITUTION_ID))
  {
    $critere2="AND p.INSTITUTION_ID=".$INSTITUTION_ID;
  }

  if(!empty($SOUS_TUTEL_ID))
  {
    $critere3=" AND SUBSTRING(p.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
  }

  if(!empty($PROGRAMME_ID))
  {
    $critere4="AND p.PROGRAMME_ID=".$PROGRAMME_ID;
  }
  if(!empty($ACTION_ID))
  {
    $critere5="AND p.ACTION_ID='".$ACTION_ID."'";
  }
  if(!empty($LIGNE_BUDGETAIRE))
  {
    $critere6=" AND p.CODE_NOMENCLATURE_BUDGETAIRE=".$LIGNE_BUDGETAIRE."";
  }
  if(!empty($IS_PRIVATE) && $IS_PRIVATE != 5)
  {
    $critere7=" AND racc.TRIMESTRE_ID=".$IS_PRIVATE."";
  }
  // if(!empty($ANNEE_BUDGETAIRE_ID))
  // {
  //   $critere8=" AND p.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
  // }

if(!empty($LIGNE_BUDGETAIRE))
  {
    $critere9=" AND p.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
  }
if(!empty($PAP_ACTIVITE_ID))
  {
    $critere10=" AND p.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
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

  $order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','DESC_TACHE',1,1,1,1,1,1,'gmas.DESCRIPTION_GRANDE_MASSE');

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.CODE_INSTITUTION ASC';

  $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%" OR gmas.DESCRIPTION_GRANDE_MASSE LIKE "%' . $var_search . '%")') : '';

  $critaire = $critere1.' '.$critere2.' '.$critere3.' '.$critere4.' '.$critere5.' '.$critere6.' '.$critere7.' '.$critere8.' '.$critere9.' '.$critere10;
  $conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
  $conditionsfilter = $critaire.' '.$search.' '.$group;

  $requetedebase = 'SELECT racc.EXECUTION_BUDGETAIRE_ID,inst_institutions.DESCRIPTION_INSTITUTION as INTITULE_MINISTERE,inst_institutions.CODE_INSTITUTION,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID AS CODE_PROGRAMME,pap_activites.DESC_PAP_ACTIVITE,
  inst_institutions_actions.LIBELLE_ACTION,inst_institutions_actions.ACTION_ID AS CODE_ACTION,p.DESC_TACHE,racc.TRIMESTRE_ID,inst_institutions.TYPE_INSTITUTION_ID,racc.ENG_BUDGETAIRE,racc.ENG_JURIDIQUE,racc.LIQUIDATION,racc.ORDONNANCEMENT,racc.PAIEMENT,racc.DECAISSEMENT,
  gmas.DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire  racc JOIN ptba_tache p ON racc.PTBA_TACHE_ID=p.PTBA_TACHE_ID JOIN inst_institutions  ON p.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_grande_masse gmas 
  ON p.GRANDE_MASSE_ID=gmas.GRANDE_MASSE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PTBA_TACHE_ID WHERE 1 '.$critaire.'';

  
  $requetedebases = $requetedebase . ' ' . $conditions;
  $requetedebases = str_replace("'", "\'", $requetedebases);
  $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
  $requetedebasefilter = str_replace("'", "\'", $requetedebasefilter);
  $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
  $fetch_projets = $this->ModelPs->datatable($query_secondaire);
  $data = array();
  $u = 1;
  $stat ='';
  foreach ($fetch_projets as $row)
  {

    $institution = (mb_strlen($row->INTITULE_MINISTERE) > 4) ? (mb_substr($row->INTITULE_MINISTERE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_MINISTERE;

    $programme = (mb_strlen($row->INTITULE_PROGRAMME) > 4) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;

    $action = (mb_strlen($row->LIBELLE_ACTION) > 10) ? (mb_substr($row->LIBELLE_ACTION, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_ACTION;

    $taches = (mb_strlen($row->DESC_TACHE) > 10) ? (mb_substr($row->DESC_TACHE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

    $activites = (mb_strlen($row->DESC_PAP_ACTIVITE) > 10) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

      $code = (mb_strlen($row->CODE_NOMENCLATURE_BUDGETAIRE) > 30) ? (mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 30) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->CODE_NOMENCLATURE_BUDGETAIRE;


    $mont_budg = floatval($row->ENG_BUDGETAIRE);
    $mont_jur = floatval($row->ENG_JURIDIQUE);
    $mont_liq = floatval($row->LIQUIDATION);
    $mont_ordo = floatval($row->ORDONNANCEMENT);
    $mont_paie = floatval($row->PAIEMENT);
    $mont_decais = floatval($row->DECAISSEMENT);
    $sub_array = array();
    $sub_array[] = $u++;
    $sub_array[] = $institution;
    $sub_array[] = $programme;
    $sub_array[] = $action;
    $sub_array[] = $code;
    $sub_array[] = $activites;
    $sub_array[] = $taches;
    $sub_array[] = number_format($mont_budg,2,","," ");
    $sub_array[] = number_format($mont_jur,2,","," ");
    $sub_array[] = number_format($mont_liq,2,","," ");
    $sub_array[] = number_format($mont_ordo,2,","," ");
    $sub_array[] = number_format($mont_paie,2,","," ");
    $sub_array[] = number_format($mont_decais,2,","," ");
    $sub_array[] = $row->DESCRIPTION_GRANDE_MASSE;
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
//Exportation de la liste
function exporter($TYPE_INSTITUTION_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$PROGRAMME_ID,$ACTION_ID,$LIGNE_BUDGETAIRE,$IS_PRIVATE,$PAP_ACTIVITE_ID)
{
  $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
  if(empty($USER_IDD))
  {
    return redirect('Login_Ptba/do_logout');
  }
  $db = db_connect();
  $cond="";
  $critere1="";
  $critere2="";
  $critere3="";
  $critere4="";
  $critere5="";
  $critere6="";
  $critere7="";
  $critere8="";

  if(!empty($TYPE_INSTITUTION_ID))
  {
    $critere1="AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID."";
  }

  if(!empty($INSTITUTION_ID))
  {
    $critere2=" AND p.INSTITUTION_ID='".$INSTITUTION_ID."'";
  }

  if(!empty($SOUS_TUTEL_ID))
  {
    $critere3=" AND SUBSTRING(p.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
  }

  if(!empty($PROGRAMME_ID))
  {
    $critere4="AND p.PROGRAMME_ID='".$PROGRAMME_ID."'";
  }

  if(!empty($ACTION_ID))
  {
    $critere5="AND p.ACTION_ID='".$ACTION_ID."'";
  }

  if(!empty($LIGNE_BUDGETAIRE))
  {
    $critere6=" AND p.CODE_NOMENCLATURE_BUDGETAIRE=".$LIGNE_BUDGETAIRE."";
  }

  if(!empty($IS_PRIVATE) && $IS_PRIVATE != 5)
  {
    $critere7="AND racc.TRIMESTRE_ID=".$IS_PRIVATE."";
  }

  // if(!empty($ANNEE_BUDGETAIRE_ID))
  // {
  //   $critere8.=" AND p.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";
  // }

  if(!empty($LIGNE_BUDGETAIRE))
  {
    $critere8.=" AND p.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
  }



  $callpsreq = "CALL getRequete(?,?,?,?);";

  $cond = $critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere5." ".$critere6." ".$critere7." ".$critere8;

  $getRequete="SELECT racc.EXECUTION_BUDGETAIRE_ID,inst_institutions.DESCRIPTION_INSTITUTION as INTITULE_MINISTERE,inst_institutions.CODE_INSTITUTION,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID AS CODE_PROGRAMME,pap_activites.DESC_PAP_ACTIVITE,
  inst_institutions_actions.LIBELLE_ACTION,inst_institutions_actions.ACTION_ID AS CODE_ACTION,p.DESC_TACHE,racc.TRIMESTRE_ID,inst_institutions.TYPE_INSTITUTION_ID,racc.ENG_BUDGETAIRE,racc.ENG_JURIDIQUE,racc.LIQUIDATION,racc.ORDONNANCEMENT,racc.PAIEMENT,racc.DECAISSEMENT,
  gmas.DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire  racc JOIN ptba_tache p ON racc.PTBA_TACHE_ID=p.PTBA_TACHE_ID JOIN inst_institutions  ON p.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_grande_masse gmas 
  ON p.GRANDE_MASSE_ID=gmas.GRANDE_MASSE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PTBA_TACHE_ID WHERE 1 ".$cond." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
  $getRequete = str_replace("'", "\'", $getRequete);
  $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'INSTITUTION');
  $sheet->setCellValue('B1', 'PROGRAMME');
  $sheet->setCellValue('C1', 'ACTION');
  $sheet->setCellValue('D1', 'NOMENCLATURE BUDGETAIRE');
  $sheet->setCellValue('E1', 'ACTIVITE');
  $sheet->setCellValue('F1', 'TACHES');
  $sheet->setCellValue('G1', 'ENGAGEMENT BUDGETAIRE');
  $sheet->setCellValue('H1', 'ENGAGEMENT JURIDIQUE');
  $sheet->setCellValue('I1', 'LIQUIDATION');
  $sheet->setCellValue('J1', 'ORDONNANCEMENT');
  $sheet->setCellValue('K1', 'PAIEMENT');
  $sheet->setCellValue('L1', 'DECAISSEMENT');
  $sheet->setCellValue('m1', 'GRANDE MASSE');
  $rows = 3;
  foreach ($getData as $key)
  {
    $mont_budg = floatval($key->ENG_BUDGETAIRE);
    $mont_jur = floatval($key->ENG_JURIDIQUE);
    $mont_liq = floatval($key->LIQUIDATION);
    $mont_ordo = floatval($key->ORDONNANCEMENT);
    $mont_paie = floatval($key->PAIEMENT);
    $mont_decais = floatval($key->DECAISSEMENT);
    $sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
    $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
    $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
    $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
    $sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
    $sheet->setCellValue('F' . $rows, $key->DESC_TACHE);
    $sheet->setCellValue('G' . $rows, $mont_budg);
    $sheet->setCellValue('H' . $rows, $mont_jur);
    $sheet->setCellValue('I' . $rows, $mont_liq);
    $sheet->setCellValue('J' . $rows, $mont_ordo);
    $sheet->setCellValue('K' . $rows, $mont_paie);
    $sheet->setCellValue('L' . $rows, $mont_decais);
    $sheet->setCellValue('M' . $rows, $key->DESCRIPTION_GRANDE_MASSE);
    $rows++;
  } 
  $writer = new Xlsx($spreadsheet);
  $writer->save('world.xlsx');
  return $this->response->download('world.xlsx', null)->setFileName('Budget Exécuté Par Grande Masse.xlsx');
  return redirect('dashboard/Dashboard_Grande_Masse');
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


##fonction get_rapport qui permet d'afficher le rapport et appel des filtres qui dependent des autres
public function get_rapport()
{
  $data=$this->urichk();
  $db = db_connect(); 
  $session  = \Config\Services::session();

  $TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
  $PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
  $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
  $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
  $ACTION_ID=$this->request->getVar('ACTION_ID');
  $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
  $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
  $inst_conn=$this->request->getVar('inst_conn');
  // $ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');
  $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
  $user_id=$inst_conn;
  $cond_inst="";
  if (empty($user_id)) {
    return redirect('Login_Ptba');
  }else 
  $cond_tr='';
  if ($IS_PRIVATE==1){
    $totaux='SUM(BUDGET_T1)';
    $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=1";
  }else if ($IS_PRIVATE==2){
    $totaux='SUM(BUDGET_T2)';
    $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=2";
  
  }else if ($IS_PRIVATE==3){
    $totaux='SUM(BUDGET_T3)';
    $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=3";
 
  }else if ($IS_PRIVATE==4){
    $totaux='SUM(BUDGET_T4)';
    $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=4";
   
  }else{
    $totaux='SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
    $cond_tr=" ";
   
  }

  $cond1='';
  $cond='';
  $cond2='';
  $KEY2=1;
  $jointure= " ";
  if(!empty($TYPE_INSTITUTION_ID))
  {
    $titr_deux=''.lang("messages_lang.par_institution").'';
    $titr_deux2=''.lang("messages_lang.par_institution").'';
    $id_decl= 'INSTITUTION_ID'; 
    $name_decl= "DESCRIPTION_INSTITUTION";
    $jointure= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    $cond2='';
    $KEY2=6;
  }

  if(! empty($INSTITUTION_ID))
  {
    $jointure= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    $cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");

    $cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";

    $KEY2=5;
    $cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');

    if (! empty($cond_sy_req['INSTITUTION_ID'])) 
    {
      $cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
      $cond2= ' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
    }
  }
  if(! empty($SOUS_TUTEL_ID))
  {
    $jointure=" JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";

    $cond2='';
    $KEY2=2;
  }

  $cond33='';
  $cond333="";
  $cond3333="";
  if(! empty($PROGRAMME_ID))
  {  
    $cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $cond33.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $cond3333.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $cond2='';
    $KEY2=3;
  }


  if(! empty($ACTION_ID))
  {
    $jointure= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    $cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
    $cond333.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
    $KEY2=4;  
  }

    if(!empty($LIGNE_BUDGETAIRE))
        {
        $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
         
        }

        if(!empty($PAP_ACTIVITE_ID))
        {
        $cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
        
        }

  // if(!empty($ANNEE_BUDGETAIRE_ID))
  //       {
  //     $cond.=' AND ptba_tache.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
  //      }

   $cond_exec="";
  $Tot_GM_VOTE="SELECT  ".$totaux." AS bug_vote FROM ptba_tache JOIN execution_budgetaire  ex_budg_raccr ON ptba_tache.PTBA_TACHE_ID=ex_budg_raccr.PTBA_TACHE_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID =ptba_tache.GRANDE_MASSE_ID ".$jointure." WHERE 1 ".$cond." ".$cond_exec." ";
  $Get_Tot_GM_VOTE=$this->ModelPs->getRequeteOne('CALL getTable("'.$Tot_GM_VOTE.'")');

  $Tota_GM_VOTE=($Get_Tot_GM_VOTE['bug_vote']>0) ? $Get_Tot_GM_VOTE['bug_vote'] : 1;

##requete qui retoune le rapport grande masse voté
  $Req_budg_vote="SELECT   if(inst_grande_masse.GRANDE_MASSE_ID IN (6,7,8),7,inst_grande_masse.GRANDE_MASSE_ID) AS id,if(inst_grande_masse.GRANDE_MASSE_ID IN (6,7,8),'transferts et subsides',DESCRIPTION_GRANDE_MASSE) AS gde_masse,".$totaux." AS bug_vote FROM ptba_tache JOIN execution_budgetaire  ex_budg_raccr ON ptba_tache.PTBA_TACHE_ID=ex_budg_raccr.PTBA_TACHE_ID JOIN inst_grande_masse 
  ON inst_grande_masse.GRANDE_MASSE_ID =ptba_tache.GRANDE_MASSE_ID ".$jointure." WHERE 1 ".$cond." ".$cond_exec." GROUP BY id,gde_masse ORDER BY bug_vote DESC";

  $budg_vote=$this->ModelPs->getRequete('CALL getTable("'.$Req_budg_vote.'")');
  $data_budg_vote='';
  $Tot_budg_vot=0;
  $categorie_vote='';
  foreach ($budg_vote as  $value)
  {
    $color=$this->getcolor();
    $categorie_vote.="'";
    $mont=($value->bug_vote>0) ? $value->bug_vote : 0;
    $name=!empty($value->gde_masse) ? $value->gde_masse : 'Autre';
    $gde_masse=trim(str_replace("'", "\'",$name));
    $Tot_budg_vot+=$mont;
    $categorie_vote.=$gde_masse."',";
    $data_budg_vote.="{y:".number_format(($mont*100)/$Tota_GM_VOTE,2,'.','').",key:".$value->id.",key2:".$KEY2.",color:'".$color."'},";
  }

##script qui permet l'affiche le rapport grande masse exécuté
  $rapp="<script type=\"text/javascript\">

  Highcharts.chart('container', {                    
    chart: {
      type: 'column'
      },
      title: {
        text: '<b>".lang('messages_lang.budget_vote_gde_masse')."<br>'
        },
        subtitle: {
          text: 'le ".date('d-m-Y')."'
          },

          xAxis: {
            categories: [".$categorie_vote."],
            crosshair: true
            },
            yAxis: {
              min: 0,
              title: {
                text: ' '
              }
              },
              tooltip: {
                headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
                pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
                '<td style=\"padding:0\"><b>{point.y:.f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
                },
                plotOptions: {
                  column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    cursor:'pointer', 
                    point:{
                   events: {
                   click: function(){
                  $(\"#titre\").html(\"".lang('messages_lang.titre_detail_vote_gde_masse')."\");
                  $(\"#myModal\").modal('show');
                  var row_count ='1000000';
                  $(\"#mytable\").DataTable({
                  \"processing\":true,
                  \"serverSide\":true,
                  \"bDestroy\": true,
                  \"ajax\":{
                   url:\"".base_url('dashboard/Dashboard_Grande_Masse/detail_GMV')."\",
                    type:\"POST\",
                    data:{
                    key:this.key,
                    key2:this.key2,
                    IS_PRIVATE:$('input[type=radio][name=IS_PRIVATE]:checked').val(),
                    INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                    TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                    PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                    ACTION_ID:$('#ACTION_ID').val(),
                    SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                    inst_conn:$('#inst_conn').val(),
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
                    \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                    \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')."_MAX_".lang('messages_lang.labelle_et_elementtotal').")\",
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
                }

                });
              }
            }
            },
            dataLabels: {
            enabled: true,
            format: '{point.y:,.1f} %'
            },
          showInLegend: true
          }
        },
      credits: {
            enabled: true,
            href: \"\",
            text: \"Mediabox\"
            },
            series: [{
              name:' ".lang('messages_lang.labelle_grandes_masses')." (".number_format($Tot_budg_vot,0,',',' ')." BIF</b>)',
               data:[".$data_budg_vote."]
             }
          ]
        });
 </script>"; 

##Calcul du montant total du montant exécuté
 $req_Tot_Exec="SELECT SUM(ex_budg_raccr.ENG_BUDGETAIRE) AS bug_exec FROM ptba_tache JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID JOIN execution_budgetaire  ex_budg_raccr ON ex_budg_raccr.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID ".$jointure." WHERE 1 ".$cond_tr." ".$cond."";
$Get_Tot_Exec=$this->ModelPs->getRequeteOne('CALL getTable("'.$req_Tot_Exec.'")');
$Tot_Exec=($Get_Tot_Exec['bug_exec'] > 0) ? $Get_Tot_Exec['bug_exec'] : 1;
##requete qui retoune le rapport grande masse exécuté
$Req_gde_masse_ex="SELECT IF(inst_grande_masse.GRANDE_MASSE_ID IN(6, 7, 8),7, inst_grande_masse.GRANDE_MASSE_ID) AS id,IF(inst_grande_masse.GRANDE_MASSE_ID IN(6, 7, 8),'transferts et subsides',inst_grande_masse.DESCRIPTION_GRANDE_MASSE) AS gde_masse,SUM(ex_budg_raccr.ENG_BUDGETAIRE) AS bug_exec FROM ptba_tache JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID JOIN execution_budgetaire  ex_budg_raccr ON ex_budg_raccr.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID WHERE 1 GROUP BY id, gde_masse ORDER BY bug_exec DESC";
$budg_ex=$this->ModelPs->getRequete('CALL getTable("'.$Req_gde_masse_ex.'")');

$data_budg_ex='';
$Tot_budg_ex=0;
$categorie_exec='';
foreach ($budg_ex as  $value)
{
  $color=$this->getcolor();
  $categorie_exec.="'";
  $mont=($value->bug_exec>0) ? $value->bug_exec : 0;
  $name=!empty($value->gde_masse) ? $value->gde_masse : 'Autre';
  $gde_masse_ex=trim(str_replace("'", "\'",$name));
  $Tot_budg_ex+=$mont;
  $categorie_exec.=$gde_masse_ex."',";
  $data_budg_ex.="{y:".number_format(($mont*100)/$Tot_Exec,2,'.','').",key:".$value->id.",key2:".$KEY2.",color:'".$color."'},";
}

##script qui permet l'affiche le rapport grande masse exécuté
$rapp1="<script type=\"text/javascript\">
Highcharts.chart('container1', {                    
  chart: {
    type: 'column'
    },
    title: {
       text: '<b>".lang('messages_lang.tbd_budget_exec_gde_masse')."'
      },
      subtitle: {
        text: 'Le ".date('d-m-Y')."'
        },
      xAxis: {
        categories: [".$categorie_exec."],
        crosshair: true
         },
        yAxis: {
          min: 0,
           title: {
            text: ' '
           }
          },
           tooltip: {
    headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
    pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
    '<td style=\"padding:0\"><b>{point.y:.f}</b></td></tr>',
    footerFormat: '</table>',
    shared: true,
    useHTML: true
           },
          plotOptions: {
             column: {
               pointPadding: 0.2,
               borderWidth: 0,
               cursor:'pointer', 
               point:{
                events: {
                 click: function(){
                   $(\"#titre1\").html(\"".lang('messages_lang.titre_budget_exec')."\");
                   $(\"#myModal1\").modal('show');
                   var row_count ='1000000';
                  $(\"#mytable1\").DataTable({
                   \"processing\":true,
                   \"serverSide\":true,
                   \"bDestroy\": true,
                   \"ajax\":{
                    url:\"".base_url('dashboard/Dashboard_Grande_Masse/detail_GME')."\",
                    type:\"POST\",
                    data:{
                     key:this.key,
                     IS_PRIVATE:$('input[type=radio][name=IS_PRIVATE]:checked').val(),
                     key2:this.key2,
                     INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                     TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                     PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                     ACTION_ID:$('#ACTION_ID').val(),
                     SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                     inst_conn:$('#inst_conn').val(),
                     LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                     PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),                    
                      }
                      },
                      lengthMenu: [[5,10,50, 100, row_count], [10,50, 100, \"All\"]],
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
                        \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                        \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_  ".lang('messages_lang.labelle_et_elementtotal').")\",
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
                      format: '{point.y:,.1f} %'
                      },
                      showInLegend: true
                   }
                    },
                  credits: {
                    enabled: true,
                    href: \"\",
                    text: \"Mediabox\"
                    },
                  series: [{
                  name:'".lang('messages_lang.labelle_grandes_masses')." (".number_format($Tot_budg_ex,0,',',' ')."BIF </b>)',
                  data:[".$data_budg_ex."]
                  }]
                  });
                  </script>";
                $data_budget="{name:'".lang('messages_lang.labelle_montant')." ".lang('messages_lang.labelle_vote')." (<> BIF)',data:[";
                $data_total=0;

                foreach ($budg_vote as $value)
                {
                  $color=$this->getcolor();
                  $mont=($value->bug_vote>0) ? $value->bug_vote : 0;
                  $data_total+=$mont;
                  $data_budget.="{name:'".trim(str_replace("'","\'", $value->gde_masse))."',y:".number_format(($mont*100)/$Tota_GM_VOTE,2,'.','').",key:'".$value->id."',key1:1,key2:".$KEY2.",color:'".$color."'},"; 
                }
               $data_total1=0;
              $data_budget.="]},{name:'".lang('messages_lang.labelle_montant')." ".lang('messages_lang.labelle_execution')."(@ BIF)',data:[";
              foreach($budg_ex as $value){
                $color=$this->getcolor();
                $mont=$value->bug_exec>0 ? $value->bug_exec: 0;
                $data_total1=$data_total1+$mont;
                $data_budget.="{name:'".trim(str_replace("'","\'", $value->gde_masse))."',y:".number_format(($mont*100)/$Tot_Exec,2,'.','').",key:'".$value->id."',key1:2,color:'".$color."'},";
               }
              $data_budget.="]}";
              $data_budget=str_replace('<>',number_format($data_total,0,'.',' '),$data_budget);
              $data_budget=str_replace('@',number_format($data_total1,0,'.',' '),$data_budget);
              $tot_gen=$data_total+$data_total1;

              ###script qui retourne le rapport de comparaison grand masse voté et grande masse exécuté
               $rapp2=" <script type=\"text/javascript\">
                    Highcharts.chart('container2', {
                      chart: {
                         type: 'column'
                        },
                         title: {
                           text: ' ".lang('messages_lang.tbd_budget_vote_exec_gde_masse')."'

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
                          pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
                          '<td style=\"padding:0\"><b>{point.y:.f} </b></td></tr>',
                          footerFormat: '</table>',
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
                                if(this.key1==1){
                                  $(\"#titre\").html(\"".lang('messages_lang.titre_detail_budget')." \");
                                  $(\"#myModal\").modal('show');
                                  var row_count ='1000000';
                                  $(\"#mytable\").DataTable({
                                    \"processing\":true,
                                    \"serverSide\":true,
                                    \"bDestroy\": true,
                                    \"ajax\":{
                                      url:\"".base_url('dashboard/Dashboard_Grande_Masse/detail_GMV')."\",
                                      type:\"POST\",
                                      data:{
                                         key:this.key,
                                        key2:this.key2,
                                        IS_PRIVATE:$('input[type=radio][name=IS_PRIVATE]:checked').val(),
                                        INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                        TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                        PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                        ACTION_ID:$('#ACTION_ID').val(),
                                         SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                                        inst_conn:$('#inst_conn').val(),
                                       
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
                                    \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                                    \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_".lang('messages_lang.labelle_et_elementtotal').")\",
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

                                else
                                {
                              $(\"#titre1\").html(\" ".lang('messages_lang.titre_detail_budget')."\");
                              $(\"#myModal1\").modal('show');
                               var row_count ='1000000';
                               $(\"#mytable1\").DataTable({
                                \"processing\":true,
                                 \"serverSide\":true,
                                 \"bDestroy\": true,
                                 \"order\":[],
                                 \"ajax\":{
                                  url:\"".base_url('dashboard/Dashboard_Grande_Masse/detail_GME')."\",
                                 type:\"POST\",
                                 data:{
                                   key:this.key,
                                   key1:this.key1,
                                   IS_PRIVATE:$('input[type=radio][name=IS_PRIVATE]:checked').val(),
                                   INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                   TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                   PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                   ACTION_ID:$('#ACTION_ID').val(),
                                   inst_conn:$('#inst_conn').val(),
                                   
                                   LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                   PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),                                    
                                  }
                                  },
                                  lengthMenu: [[5,10,50, 100, row_count], [10,50, 100, \"All\"]],
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
                                  \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
                                  \"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_".lang('messages_lang.labelle_et_elementtotal').")\",
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
                          }
                          },

                          dataLabels: {
                            enabled: true,
                            format: '{point.y:,.1f} %'
                            },
                            showInLegend: true
                          }
                          }, 
                          credits: {
                          enabled: true,
                          href: \"\",
                          text: \"Mediabox\"
                          },
                          series: [".$data_budget."]

                          });
                          </script>
                          ";

                    $inst= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
                     if (!empty($TYPE_INSTITUTION_ID))
                    {
                      $inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID AS CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

                      $inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
                      foreach ($inst_sect_req as $key)
                      {
                        if (!empty($INSTITUTION_ID))
                        { 

                          if ($INSTITUTION_ID==$key->CODE_INSTITUTION) 
                          {
                            $inst.= "<option value ='".$key->CODE_INSTITUTION."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                          }
                          else
                          {
                            $inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                                }
                              }
                              else
                              {
                                $inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                              }
                            }
                          }
                          $soustutel= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';

                           if ($INSTITUTION_ID != '')
                          {
                            $soustutel_sect="SELECT DISTINCT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel   WHERE 1 ".$cond1." ORDER BY inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL ASC";
                            $soustutel_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$soustutel_sect.'")');

                            foreach ($soustutel_sect_req as $key)
                            {
                              if (!empty($SOUS_TUTEL_ID))
                              {  
                                if ($SOUS_TUTEL_ID==$key->CODE_SOUS_TUTEL) 
                                {
                                  $soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."' selected>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
                                }
                                else
                                {
                                  $soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."'>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
                                }
                              }
                              else
                              {
                                $soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."'>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
                              }
                            }
                          }
                      $program= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
                      if (!empty($PROGRAMME_ID))
                      {
                    $inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID as  CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

                    $inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
                       foreach ($inst_sect_req as $key)
                        {
                          if (!empty($INSTITUTION_ID))
                          { 

                            if ($INSTITUTION_ID==$key->CODE_INSTITUTION) 
                            {
                             $inst.= "<option value ='".$key->CODE_INSTITUTION."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                            }
                            else
                            {
                              $inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                            }
                             }
                          else
                          {
                           $inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                          }
                        }
                      }
                    $program= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
                           if ($SOUS_TUTEL_ID != '')
                               {
                        $program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID AS CODE_PROGRAMME FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.PROGRAMME_ID=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'  ORDER BY inst_institutions_programmes.PROGRAMME_ID ASC";

                              $program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
                              foreach ($program_sect_req as $key)
                              {
                                if (!empty($PROGRAMME_ID))
                                {  
                                  if ($PROGRAMME_ID==$key->CODE_PROGRAMME) 
                                  {
                                    $program.= "<option value ='".$key->CODE_PROGRAMME."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
                                   }
                                  else
                                  {
                                    $program.= "<option value ='".$key->CODE_PROGRAMME."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                                  }
                                }
                              else
                              {
                                $program.= "<option value ='".$key->CODE_PROGRAMME."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                              }
                            }
                          }
                          $actions= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
                          if ($PROGRAMME_ID != '')
                          {
                        $actions_sect='SELECT DISTINCT inst_institutions_actions.ACTION_ID AS CODE_ACTION,inst_institutions_actions.LIBELLE_ACTION FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID where 1 '.$cond33.'  ORDER BY inst_institutions_actions.ACTION_ID ASC';
                         $actions_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$actions_sect.'")');
                         foreach ($actions_sect_req as $key)
                         {
                           if (!empty($ACTION_ID))
                           {  
                             if ($ACTION_ID==$key->CODE_ACTION) 
                             {
                               $actions.= "<option value ='".$key->CODE_ACTION."' selected>".trim($key->LIBELLE_ACTION)."</option>";
                             }
                             else
                             {
                               $actions.= "<option value ='".$key->CODE_ACTION."'>".trim($key->LIBELLE_ACTION)."</option>";
                              }
                           }
                           else
                          {
                            $actions.= "<option value ='".$key->CODE_ACTION."'>".trim($key->LIBELLE_ACTION)."</option>";
                          }
                        }
                      }

               $ligne_budgetaires= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
             if ($ACTION_ID != '')
                  {
            $ligne_budgetaire_sect='SELECT DISTINCT inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID FROM  ptba_tache RIGHT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND inst_institutions_ligne_budgetaire.ACTION_ID='.$ACTION_ID;

              $ligne_budgetaire_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$ligne_budgetaire_sect.'")');
                  foreach ($ligne_budgetaire_sect_req as $key)
                    {
                  if (!empty($LIGNE_BUDGETAIRE))
                      {  
                    if ($LIGNE_BUDGETAIRE==$key->CODE_NOMENCLATURE_BUDGETAIRE_ID) 
                          {
                    $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."' selected>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                          }
                        else
                          {
                    $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."'>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                           }
                         }
                         else
                         {
                    $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."'>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                         }
                       }
                     }
                   ////filtre des activites 
              $ligne_activite= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                if ($LIGNE_BUDGETAIRE != '')
                     {
              $ligne_activites_sect='SELECT DISTINCT  pap_activites.PAP_ACTIVITE_ID,pap_activites.DESC_PAP_ACTIVITE FROM  ptba_tache RIGHT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 AND pap_activites.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$LIGNE_BUDGETAIRE;

                  $ligne_activites_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$ligne_activites_sect.'")');
                       foreach ($ligne_activites_sect_req as $key)
                       {
                      if (!empty($PAP_ACTIVITE_ID))
                        {  
                    if ($PAP_ACTIVITE_ID==$key->PAP_ACTIVITE_ID) 
                          {
                    $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."' selected>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                          }
                        else
                          {
                    $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."'>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                           }
                         }
                         else
                         {
                    $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."'>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                         }
                       }
                     }
              echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1,'rapp2'=>$rapp2,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires,'ligne_activite'=>$ligne_activite));
       }

//detail du rapport grande masse voté
       function detail_GMV()
       {
        $data=$this->urichk();
        $db=db_connect(); 
        $session  = \Config\Services::session();

        $KEY=$this->request->getPost('key'); 
        $KEY2=$this->request->getPost('key2');
        $TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
        $PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
        $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
        $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
        $ACTION_ID=$this->request->getVar('ACTION_ID');
        $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
        $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
        $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
        $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
        $inst_conn=$this->request->getVar('inst_conn');
        $cond1='';
        $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
        if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
        {
          $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
          $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
          $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');

          $nombre=count($user_connect_req);
          if ($nombre>1) {
           $cond1.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";

         }else{
           $cond1.='';  
         }
       }
       else{
        return redirect('Login_Ptba');
      }
      if ($IS_PRIVATE==1){
        $totaux='ptba_tache.BUDGET_T1';
      }else if ($IS_PRIVATE==2){
        $totaux='ptba_tache.BUDGET_T2';
      }else if ($IS_PRIVATE==3){
       $totaux='ptba_tache.BUDGET_T3';
     }else if ($IS_PRIVATE==4){
      $totaux='ptba_tache.BUDGET_T4';
    }else{
      $totaux='(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
    }
    $cond='';
    if(! empty($TYPE_INSTITUTION_ID))
    {
      $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    }

    if(! empty($INSTITUTION_ID))
    {
      $cond.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID."";
    }
    if(! empty($SOUS_TUTEL_ID))
    {
     $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)=".$SOUS_TUTEL_ID."";
   }
   if(! empty($PROGRAMME_ID))
   {  
    $cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID."";
  }
  if(! empty($ACTION_ID))
  {
    $cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID."";
  }
  // if(!empty($ANNEE_BUDGETAIRE_ID))
  // {
  //   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";
  // }  

  if(!empty($LIGNE_BUDGETAIRE))
  {
    $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
  } 

  if(!empty($PAP_ACTIVITE_ID))
  {
    $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";
  } 

  $query_principal='SELECT DESCRIPTION_GRANDE_MASSE,'.$totaux.' as budg_vote,DESCRIPTION_INSTITUTION as  INSTITUTION, date_format(det.DATE_ENGAGEMENT_BUDGETAIRE,"%d-%m-%Y") as dateee, inst_institutions_programmes.`INTITULE_PROGRAMME` as PROGRAMME, `DESC_TACHE` FROM `ptba_tache` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID= ptba_tache.INSTITUTION_ID LEFT JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID=ptba_tache.GRANDE_MASSE_ID LEFT JOIN execution_budgetaire  act ON act.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire det ON det.EXECUTION_BUDGETAIRE_ID=act.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID WHERE 1 '.$cond.' '.$cond1.' ';
  $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

  $limit='LIMIT 0,5';
  $draw = isset($_POST['draw']);
  if(isset($_POST["length"]) && $_POST["length"] != -1)
  {
    $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
  }
  $order_by='';
  $group="";
  $order_column=array( 1,'DESCRIPTION_INSTITUTION','DESCRIPTION_GRANDE_MASSE',1,'inst_institutions_programmes.INTITULE_PROGRAMME','DESC_TACHE',1);
  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY INSTITUTION_ID ASC';

  $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESCRIPTION_GRANDE_MASSE LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%")') : '';

  $critere = ($KEY==7) ? ' AND ptba_tache.GRANDE_MASSE_ID in(6,7,8)' : ' AND ptba_tache.GRANDE_MASSE_ID = '.$KEY.'' ;
  $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
  $query_filter=$query_principal.' '.$critere.'  '.$search;
  $query_secondaire="CALL `getTable`('".$conditions."')";
  $fetch_res= $this->ModelPs->datatable($query_secondaire);

  $data = array();    
  $u=1;
  foreach ($fetch_res as $row) 
  {
    $sub_array = array();
    $sub_array[]=$u++;
    if(strlen($row->INSTITUTION) > 10){
      $sub_array[] =(strlen($row->INSTITUTION) > 10) ? mb_substr($row->INSTITUTION, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INSTITUTION.'"><i class="fa fa-eye"></i></a>' : $row->INSTITUTION;

      $sub_array[] =(strlen($row->DESCRIPTION_GRANDE_MASSE) > 18) ? mb_substr($row->DESCRIPTION_GRANDE_MASSE, 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_GRANDE_MASSE.'"><i class="fa fa-eye"></i></a>' : $row->DESCRIPTION_GRANDE_MASSE;
      $sub_array[] ="<center>".number_format($row->budg_vote,0,'.',' ')."<center>";

      $sub_array[] =(strlen($row->PROGRAMME) > 10) ? mb_substr($row->PROGRAMME, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->PROGRAMME.'"><i class="fa fa-eye"></i></a>' : $row->PROGRAMME;
      $sub_array[] =(strlen($row->DESC_TACHE) > 10) ? mb_substr($row->DESC_TACHE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>' : $row->DESC_TACHE;
    }else{
      $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
      $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INSTITUTION.'</label></font> </center>';
      $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_GRANDE_MASSE.'</label></font> </center>';
      $sub_array[] ="<center>".number_format($row->budg_vote,0,'.',' ')."<center>";
      $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->PROGRAMME.'</label></font> </center>';
      $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
    }
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->dateee.'</label></font> </center>';
    $data[] = $sub_array;
  }
  $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$query_principal."')");
  $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$query_filter."')");

  $output = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" =>count($recordsTotal),
    "recordsFiltered" =>count($recordsFiltered),
    "data" => $data
  );
  echo json_encode($output);
}

 //detail du rapport grande masse éxecutée

        function detail_GME()
        {
          $KEY=$this->request->getPost('key'); 
          $KEY2=$this->request->getPost('key2'); 
          $TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
          $PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
          $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
          $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
          $ACTION_ID=$this->request->getVar('ACTION_ID');            
          $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
          $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
          $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
          $inst_conn=$this->request->getVar('inst_conn');
          
                     ###filtre des tranches ###################                            
          if ($IS_PRIVATE==1)                                    
          {                                      
            $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=1";
          }else if ($IS_PRIVATE==2){                   
            $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=2";
          }else if ($IS_PRIVATE==3){
            $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=3";
          }else if ($IS_PRIVATE==4){
            $cond_tr=" AND ex_budg_raccr.TRIMESTRE_ID=4";
          }else{                                 
            $cond_tr=" ";                                    
          }
                    #####filtre pour les institutions######
          $cond='';
          if(! empty($TYPE_INSTITUTION_ID))
          {
            $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
          }

          if(! empty($INSTITUTION_ID))
          {
            $cond.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID."";
          }
          if(! empty($SOUS_TUTEL_ID))
          {
            $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)=".$SOUS_TUTEL_ID."";
          }
          if(! empty($PROGRAMME_ID))
          {  
            $cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID."";
          }
          if(! empty($ACTION_ID))
          {
            $cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID."";
          }

          

          if(!empty($LIGNE_BUDGETAIRE))
          {
            $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
          }

          if(!empty($PAP_ACTIVITE_ID))
          {
            $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";
          }
          $query_principal='SELECT DESCRIPTION_GRANDE_MASSE,ex_budg_raccr.ENG_BUDGETAIRE as budg_ex,DESCRIPTION_INSTITUTION as  INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME as PROGRAMME, `DESC_TACHE`, date_format(det.DATE_DEMANDE,"%d-%m-%Y") as dat FROM execution_budgetaire  ex_budg_raccr LEFT JOIN ptba_tache ON  ptba_tache.PTBA_TACHE_ID=ex_budg_raccr.PTBA_TACHE_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID LEFT JOIN execution_budgetaire det ON det.EXECUTION_BUDGETAIRE_ID=ex_budg_raccr.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID WHERE 1  '.$cond_tr.'  '.$cond.' ';
          $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
          $limit='LIMIT 0,5';
          $draw = isset($_POST['draw']);
          if(isset($_POST["length"]) && $_POST["length"] != -1)
          {
            $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
          }
          $order_by='';
          $group="";
          $order_column=array( 1,'DESCRIPTION_GRANDE_MASSE','DESCRIPTION_INSTITUTION',1,'inst_institutions_programmes.INTITULE_PROGRAMME','DESC_TACHE',1);
          $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.CODE_INSTITUTION ASC';

          $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESCRIPTION_GRANDE_MASSE LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%")') : '';
          $critere = ($KEY==7) ? ' AND ptba_tache.GRANDE_MASSE_ID in(6,7,8)' : ' AND ptba_tache.GRANDE_MASSE_ID = '.$KEY.'' ;
          $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
          $query_filter=$query_principal.' '.$critere.'  '.$search;
          $query_secondaire="CALL `getTable`('".$conditions."')";
          $fetch_res= $this->ModelPs->datatable($query_secondaire);

          $data = array();    
          $u=1;
          foreach ($fetch_res as $row) 
          {
            $sub_array = array();
            $sub_array[]=$u++;
            $sub_array[] =(strlen($row->INSTITUTION) > 10) ? mb_substr($row->INSTITUTION, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INSTITUTION.'"><i class="fa fa-eye"></i></a>' : $row->INSTITUTION;
            $sub_array[] =(strlen($row->DESCRIPTION_GRANDE_MASSE) > 20) ? mb_substr($row->DESCRIPTION_GRANDE_MASSE, 0, 20) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_GRANDE_MASSE.'"><i class="fa fa-eye"></i></a>' : $row->DESCRIPTION_GRANDE_MASSE;
            $sub_array[] = "<center>".number_format($row->budg_ex,0,'.',' ')."</center>";

            $sub_array[] =(strlen($row->PROGRAMME) > 10) ? mb_substr($row->PROGRAMME, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->PROGRAMME.'"><i class="fa fa-eye"></i></a>' : $row->PROGRAMME;
            $sub_array[] =(strlen($row->DESC_TACHE) > 10) ? mb_substr($row->DESC_TACHE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>' : $row->DESC_TACHE;
            $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->dat.'</label></font> </center>';
            $data[] = $sub_array;
          }

          $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$query_principal."')");
          $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$query_filter."')");

          $output = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" =>count($recordsTotal),
            "recordsFiltered" =>count($recordsFiltered),
            "data" => $data
          );
          echo json_encode($output);
        }

      }
      ?>
