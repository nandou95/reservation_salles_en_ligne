<?php
/*
* @author NIYONGABO Emery
*emery@mediabox.bi
* Tableau de bord «dashbord des execution budgetaire» le 12/09/2023
*/
/*
* @author NDERAGAKURA Alain Charbel
* charbel@mediabox.bi
* Ajout du liste + exportation» le 26/03/2024
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
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
class Dashbord_General_Execution extends BaseController
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

  /*
  * fonction pour retourner le tableau des parametre pour le PS pour les selection
  * @param string  $columnselect //colone A selectionner
  * @param string  $table        //table utilisE
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

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_EXECUTION_BUDGETAIRE')!=1)
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

      if ($nombre>0) 
      {
        if ($nombre==1) 
        {
          foreach ($user_affectations as $value) 
          {
           $cond_affectations=" AND INSTITUTION_ID= ".$value->INSTITUTION_ID;
         }
       }else if ($nombre>1)
       {
        $inst="(";
        foreach ($user_affectations as $value) 
        {
          $inst.=$value->INSTITUTION_ID.",";
        }
            //Enlever la dernier virgule
        $inst = substr($inst, 0, -1);
        $inst=$inst.")";
        $cond_affectations.=" AND INSTITUTION_ID IN ".$inst;
      }
    }else
    {
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
    if($date_select=='01' OR $date_select=='02' OR $date_select=='03')
    {
      $date_ch='';
      $date_ch1='';
      $date_ch2='checked';
      $date_ch3='';
      $date_ch4='';
    }else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06')
    {
      $date_ch='';
      $date_ch1='';
      $date_ch2='';
      $date_ch3='checked';
      $date_ch4='';
    }else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' )
    {
     $date_ch='checked';
     $date_ch1='';
     $date_ch2='';
     $date_ch3='';
     $date_ch4='';
   }else if ($date_select=='10' OR $date_select=='11' OR $date_select=='12' )
   {
     $date_ch='';
     $date_ch1='checked';
     $date_ch2='';
     $date_ch3='';
     $date_ch4='';
   }else
   {
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

      //L'id de l'année budgétaire actuelle
   $data['ann_actuel_id'] = $this->get_annee_budgetaire();

      //Selection de l'année budgétaire
   $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID<=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
   $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');


   return view('App\Modules\dashboard\Views\Dashbord_General_Execution_View',$data);
 }
    # fonction pour la liste
 public function listing_execution() 
 {
  $data=$this->urichk();
  $db = db_connect();
  $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
  $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
  $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
  $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
  $ACTION_ID=$this->request->getPost('ACTION_ID');
  $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
  $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
  $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
  // $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
 
  if ($IS_PRIVATE==1) {
    $cond_trim=" AND ( execution_budgetaire.TRIMESTRE_ID=1 or execution_budgetaire.TRIMESTRE_ID=0 )" ;
  }else if ($IS_PRIVATE==2) {
    $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
  }else if ($IS_PRIVATE==3) {
    $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
  }else if ($IS_PRIVATE==4){
    $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
  }else {
    $cond_trim=" ";
  }
  $cond='';
  if(! empty($TYPE_INSTITUTION_ID))
  {
    $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
  }
  if(! empty($INSTITUTION_ID))
  {
    $cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
  }
  if(! empty($SOUS_TUTEL_ID))
  {
    $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
  }
  if(! empty($PROGRAMME_ID))
  {
    if ($TYPE_INSTITUTION_ID==2) {
      $cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    }
  }

  if(! empty($ACTION_ID))
  {
    $cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'"; 
  }
  if($LIGNE_BUDGETAIRE !='')
  {
    $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
  }

  if($PAP_ACTIVITE_ID !='')
  {
    $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";
  }

  // if(!empty($ANNEE_BUDGETAIRE_ID))
  // {
  //   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
  // }

  $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

  $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,`DESC_TACHE`,`RESULTAT_ATTENDUS_TACHE`,activite.ENG_BUDGETAIRE,activite.LIQUIDATION,activite.DECAISSEMENT,pap_activites.DESC_PAP_ACTIVITE,activite.ENG_JURIDIQUE,activite.ORDONNANCEMENT,activite.PAIEMENT,ptba_tache.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire activite JOIN ptba_tache ON activite.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID LEFT JOIN execution_budgetaire_tache_detail detail ON detail.EXECUTION_BUDGETAIRE_ID = activite.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=activite.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=activite.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond_trim."";

  $limit='LIMIT 0,10';
  if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';

  $order_column=array(1,'inst_institutions.DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE','DESC_TACHE','DESCRIPTION_GRANDE_MASSE','activite.ENG_BUDGETAIRE','activite.ENG_JURIDIQUE','activite.LIQUIDATION','activite.ORDONNANCEMENT','activite.PAIEMENT','activite.DECAISSEMENT');

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';
  $search = !empty($_POST['search']['value']) ? (" AND ( inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%')") : '';
  $conditions=$query_principal." ".$search." ".$order_by." ".$limit;
  $query_filter=$query_principal." ".$search;
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
    $INSTITUTION = (mb_strlen($row->INTITULE_MINISTERE) > 10) ? (mb_substr($row->INTITULE_MINISTERE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_MINISTERE;
    $PROGRAMME = (mb_strlen($row->INTITULE_PROGRAMME) > 10) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;

    $ACTION = (mb_strlen($retVal) > 10) ? (mb_substr($retVal, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>') : $retVal;

    $CODE = (mb_strlen($row->CODE_NOMENCLATURE_BUDGETAIRE) > 30) ? (mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 30) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->CODE_NOMENCLATURE_BUDGETAIRE;

    $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 10) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;


     $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 10) ? (mb_substr($row->DESC_TACHE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

    
    $engagement[]=$INSTITUTION;
    $engagement[]=$PROGRAMME;
    $engagement[]=$ACTION;
    $engagement[]=$CODE;
    $engagement[]=$ACTIVITES;
    $engagement[]=$DESC_TACHE;
    $engagement[]=$row->DESCRIPTION_GRANDE_MASSE;
    $engagement[]=number_format($row->ENG_BUDGETAIRE,0,',',' ');
    $engagement[]=number_format($row->ENG_JURIDIQUE,0,',',' ');
    $engagement[]=number_format($row->LIQUIDATION,0,',',' ');
    $engagement[]=number_format($row->ORDONNANCEMENT,0,',',' ');
    $engagement[]=number_format($row->PAIEMENT,0,',',' ');
    $engagement[]=number_format($row->DECAISSEMENT,0,',',' ');
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
      return $this->response->setJSON($output);//echo json_encode($output);
    }

    function exporter($TYPE_INSTITUTION_ID='',$INSTITUTION_ID='',$PROGRAMME_ID='',$ACTION_ID='',$IS_PRIVATE='')
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
      $IS_PRIVATE=$IS_PRIVATE;
      if ($IS_PRIVATE==1) {
        $cond_trim=" AND ( activite.TRIMESTRE_ID=1 or activite.TRIMESTRE_ID=0 )" ;
      }else if ($IS_PRIVATE==2) {
        $cond_trim=" AND activite.TRIMESTRE_ID=2" ;
      }else if ($IS_PRIVATE==3) {
        $cond_trim=" AND activite.TRIMESTRE_ID=3" ;
      }else if ($IS_PRIVATE==4){
        $cond_trim=" AND activite.TRIMESTRE_ID=4" ;
      }else {
        $cond_trim=" " ;
      }
      $cond='';
      $cond1='';
      $cond2='';
      $cond4='';
      $cond5='';
      $cond6='';
      $cond7='';
      if(! empty($TYPE_INSTITUTION_ID) )
      {
        $cond1='AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
      }

      if($INSTITUTION_ID!=0)
      {
        $cond2="AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
      }

      if($PROGRAMME_ID!=0)
      {
        if ($TYPE_INSTITUTION_ID==2) 
        {
          $cond4="AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
        }
      }

      if($ACTION_ID!=0)
      {
        $cond5=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'"; 
      }

      // if($ANNEE_BUDGETAIRE_ID!=0)
      // {
      //   $cond7.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
      // }

      $cond=$cond1.' '.$cond2.' '.$cond4.' '.$cond5.' '.$cond6.' '.$cond7;
      $getRequete="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,`DESC_TACHE`,`RESULTAT_ATTENDUS_TACHE`,activite.ENG_BUDGETAIRE,activite.LIQUIDATION,activite.DECAISSEMENT,pap_activites.DESC_PAP_ACTIVITE,activite.ENG_JURIDIQUE,activite.ORDONNANCEMENT,activite.PAIEMENT,ptba_tache.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire activite JOIN ptba_tache ON activite.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID LEFT JOIN execution_budgetaire_tache_detail detail ON detail.EXECUTION_BUDGETAIRE_ID = activite.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=activite.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=activite.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond_trim." ORDER BY DESCRIPTION_INSTITUTION ASC";
      
      $getData = $this->ModelPs->datatable('CALL getTable("' . $getRequete . '")');
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'INSTITUTION');
      $sheet->setCellValue('B1', 'PROGRAMME');
      $sheet->setCellValue('C1', 'ACTION');
      $sheet->setCellValue('D1', 'CODE NOMENCLATURE');
      $sheet->setCellValue('E1', 'ACTIVITE');
      $sheet->setCellValue('F1', 'TACHE');
      $sheet->setCellValue('G1', 'GRANDE MASSE');
      $sheet->setCellValue('H1', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('I1', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('J1', 'LIQUIDATION');
      $sheet->setCellValue('K1', 'ORDONNANCEMENT');
      $sheet->setCellValue('L1', 'PAIEMENT');
      $sheet->setCellValue('M1', 'DECAISSEMENT');
      $rows = 3;
      //boucle pour les institutions
      foreach ($getData as $key)
      {
        $sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
        $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
        $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
        $sheet->setCellValue('F' . $rows, $key->DESC_TACHE);
        $sheet->setCellValue('G' . $rows, $key->DESCRIPTION_GRANDE_MASSE);
        $sheet->setCellValue('H' . $rows, $key->ENG_BUDGETAIRE);
        $sheet->setCellValue('I' . $rows, $key->ENG_JURIDIQUE);
        $sheet->setCellValue('J' . $rows, $key->LIQUIDATION);
        $sheet->setCellValue('K' . $rows, $key->ORDONNANCEMENT);
        $sheet->setCellValue('L' . $rows, $key->PAIEMENT);
        $sheet->setCellValue('M' . $rows, $key->DECAISSEMENT);
        $rows++;
      }
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('budget execute.xlsx');
      return redirect('dashboard/Dashboard_Performence_Excution');
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
    $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
    $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
    $inst_conn=$this->request->getVar('inst_conn');
    // $ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');
    
    $user_id =$inst_conn;
    $cond_affectations="";
    $cond_affectations1="";
    if (!empty($user_id)) {
      $profil_user_req=("SELECT `PROFIL_ID` FROM `user_users` WHERE USER_ID=".$user_id." AND `IS_ACTIVE`=1");
      $profil_user=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user_req.'")');
      $user_affectation=("SELECT inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
      $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
      $nombre=count($user_affectations);
      if ($nombre>0) {
        if ($nombre==1) {
          foreach ($user_affectations as $value) {
           $cond_affectations=" AND inst_institutions.INSTITUTION_ID= ".$value->INSTITUTION_ID;
           $cond_affectations1=" AND ptba_tache.INSTITUTION_ID= ".$value->INSTITUTION_ID;
         }
       }else if ($nombre>1){
        $inst="(";
        foreach ($user_affectations as $value) {
         $inst.=$value->INSTITUTION_ID.",";
       }
       $inst = substr($inst, 0, -1);
       $inst=$inst.")";
       $cond_affectations.=" AND inst_institutions.INSTITUTION_ID IN ".$inst;
       $cond_affectations1.=" AND ptba_tache.INSTITUTION_ID IN ".$inst;
     }
   }else{
    return redirect('Login_Ptba');
  }
}else{
  return redirect('Login_Ptba');
}
// $criteres TRIMESTRE
if($IS_PRIVATE==1)
{
  $totaux='SUM(BUDGET_T1)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
}else if($IS_PRIVATE==2)
{
  $totaux='SUM(BUDGET_T2)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
}else if ($IS_PRIVATE==3)
{
  $totaux='SUM(BUDGET_T3)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
}else if ($IS_PRIVATE==4)
{
  $totaux='SUM(BUDGET_T4)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
}else
{
  $totaux='SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID IN(1,2,3,4)";
}
$cond1='';
$cond='';
$cond2='';
$KEY2=1;
$cond_program='';
$titr_deux=' '.lang("messages_lang.par_type_institution").'';
$titr_deux2=''.lang("messages_lang.par_type_institution").'';
$id_decl= 'inst_institutions.INSTITUTION_ID';
$name_decl= "DESCRIPTION_INSTITUTION";
$name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
$format=" {point.y:.3f} %";
$type="column";
if(!empty($TYPE_INSTITUTION_ID))
{
  $titr_deux=' '.lang("messages_lang.par_type_institution").'';

  $titr_deux2=''.lang("messages_lang.par_type_institution").'';
  $id_decl= 'inst_institutions.INSTITUTION_ID';
  $name_decl= "DESCRIPTION_INSTITUTION";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
  $cond2='';
  $type="column";
  $format=" {point.y:.3f} %";
  $KEY2=6;
}
if(! empty($INSTITUTION_ID))
{
  $name_decl= "DESCRIPTION_SOUS_TUTEL";
  $id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_sous_tutel ON inst_institutions_sous_tutel.CODE_SOUS_TUTEL=SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)";
  $format=" {point.y:.3f} %";
  $type="column";
  $titr_deux=''.lang("messages_lang.par_service").'';
  $titr_deux2=''.lang("messages_lang.par_service").'';
  $KEY2=5;
  $cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");
  $cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
  if (! empty($cond_sy_req['INSTITUTION_ID']))
  {
   $cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
   $cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
   $cond2= ' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
 }
 $cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
}
if(! empty($SOUS_TUTEL_ID))
{
  $id_decl= 'ptba_tache.PROGRAMME_ID';
  $name_decl= "inst_institutions_programmes.INTITULE_PROGRAMME";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID";
  $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
  $titr_deux=''.lang("messages_lang.par_programme").'';
  $titr_deux2=''.lang("messages_lang.par_programme").'';
  $type="column";
  $cond2='';
  $KEY2=2;
}
$cond33='';
$cond333="";
$cond3333="";
if(! empty($PROGRAMME_ID))
{
  $id_decl= 'inst_institutions_actions.ACTION_ID';
  $name_decl= "inst_institutions_actions.LIBELLE_ACTION";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID";
  $cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $cond33.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $cond3333.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $type="column";
  $format=" {point.y:.3f} %";
  $titr_deux=''.lang("messages_lang.par_action").'';
  $titr_deux2=''.lang("messages_lang.par_action").'';
  $cond2='';
  $KEY2=3;
}
if(! empty($ACTION_ID))
{
  $id_decl= "inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";
  $name_decl= "LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID =inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";
  $cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID."";
  $cond333.=" AND ptba_tache.ACTION_ID=".$ACTION_ID."";
  $type="column";
  $titr_deux=''.lang("messages_lang.par_activite").'';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;
}
if(!empty($LIGNE_BUDGETAIRE))
  {
  $id_decl= "pap_activites.PAP_ACTIVITE_ID";
  $name_decl= "pap_activites.DESC_PAP_ACTIVITE";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID";
  $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
  $cond333.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
  $type="column";
  $titr_deux=''.lang("messages_lang.par_activite").'';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;
}

if(!empty($PAP_ACTIVITE_ID))
  {
  $id_decl= "ptba_tache.PTBA_TACHE_ID";
  $name_decl= "ptba_tache.DESC_TACHE";
  $name_table= " JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
  $cond333.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
  $type="column";
  $titr_deux=''.lang("messages_lang.par_activite").'';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;
}

// if(!empty($ANNEE_BUDGETAIRE_ID))
// {
//   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";   
// }

$budget=("SELECT ".$name_decl." AS Name,".$id_decl." AS ID,SUM(ENG_BUDGETAIRE) AS enga FROM `execution_budgetaire` ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_trim." ".$cond_affectations." GROUP BY ".$name_decl.",".$id_decl." ORDER BY ".$name_decl." ASC");
$activites_exec=("SELECT ".$name_decl." AS Name,".$id_decl." AS ID,count(execution_budgetaire.PTBA_TACHE_ID) AS enga FROM `execution_budgetaire` ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_trim." ".$cond_affectations." GROUP BY ".$name_decl.",".$id_decl." ORDER BY ".$name_decl." ASC ");




$budget_req=$this->ModelPs->getRequete(' CALL getTable("'.$budget.'")');
$activite_req=$this->ModelPs->getRequete(' CALL getTable("'.$activites_exec.'")');
$data_budget_req='';
$data_total=0;
foreach ($budget_req as $value)
{
  $pourcent=0;
  $taux=("SELECT SUM(ENG_BUDGETAIRE) AS taux FROM `execution_budgetaire` JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ");
  $taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
  if ($taux1['taux']>0)
  {
   $pourcent=($value->enga/$taux1['taux'])*100;
 }
 $data_budget_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ')."BIF)', y:".$pourcent.",color:'#000080',key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2."},";
 $data_total=$data_total+$value->enga;
}
$data_activite_req='';
$data_activite_total=0;
foreach ($activite_req as $value)
{
 $pourcent=0;
 $taux=("SELECT count(execution_budgetaire.PTBA_TACHE_ID) AS taux FROM `execution_budgetaire`  JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ".$cond_affectations1." ");
 $taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');if ($taux1['taux']>0)
 {
  $pourcent=($value->enga/$taux1['taux'])*100;
}
$data_activite_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ').")', y:".$value->enga.",color:'#FF7F50',key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2."},";
$data_activite_total=$data_activite_total+$value->enga;
}
$data_trimestre1='';
$data_trimestre2='';
$data_trimestre3='';
$data_trimestre4='';
$trimestre1=("SELECT SUM(`BUDGET_T1`) AS trm1 FROM `ptba_tache`  WHERE 1  ");
$trimestre2=("SELECT SUM(`BUDGET_T2`) AS trm2 FROM `ptba_tache`  WHERE 1  ");
$trimestre3=("SELECT SUM(`BUDGET_T3`) AS trm3 FROM `ptba_tache`   WHERE 1  ");
$trimestre4=("SELECT SUM(`BUDGET_T4`) AS trm4 FROM `ptba_tache`  WHERE 1  ");
$trimestre1_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre1.'")');
$trimestre2_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre2.'")');
$trimestre3_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre3.'")');
$trimestre4_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre4.'")');
$pourcentBUDGET_T1=0;
$pourcentBUDGET_T2=0;
$pourcentBUDGET_T3=0;
$pourcentBUDGET_T4=0;

$tauxt=("SELECT SUM(ENG_BUDGETAIRE) AS taux FROM `execution_budgetaire`  JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ".$cond_affectations1." ");

$tauxBUDGET_T1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tauxt.'")');$trimes1= ($trimestre1_req['trm1']>0) ? $trimestre1_req['trm1'] : 0 ;
$trimes2= ($trimestre2_req['trm2']>0) ? $trimestre2_req['trm2'] : 0 ;
$trimes3= ($trimestre3_req['trm3']>0) ? $trimestre3_req['trm3'] : 0 ;
$trimes4= ($trimestre4_req['trm4']>0) ? $trimestre4_req['trm4'] : 0 ;
$psex=0;$psex2=0;$psex3=0;$psex4=0;
if ($tauxBUDGET_T1['taux']>0)
   {
   $pourcentBUDGET_T1=($trimes1/$tauxBUDGET_T1['taux'])*100;
   $psex=number_format($pourcentBUDGET_T1,3,',',' ');
   $pourcentBUDGET_T2=($trimes2/$tauxBUDGET_T1['taux'])*100;
   $psex2=number_format($pourcentBUDGET_T2,3,',',' ');
   $pourcentBUDGET_T3=($trimes3/$tauxBUDGET_T1['taux'])*100;
   $psex3=number_format($pourcentBUDGET_T3,3,',',' ');
   $pourcentBUDGET_T4=($trimes4/$tauxBUDGET_T1['taux'])*100;
   $psex4=number_format($pourcentBUDGET_T4,3,',',' ');
  }
$data_trimestre1.="{name:'Trimestre 1 (".$psex." %)', y:".$trimes1.",key:1,key2:100},";
$data_trimestre2.="{name:'Trimestre 2 (".$psex2." %)', y:".$trimes2.",key:2,key2:200},";
$data_trimestre3.="{name:'Trimestre 3  (".$psex3." %)', y:".$trimes3.",key:3,key2:300},";
$data_trimestre4.="{name:'Trimestre 4  (".$psex4." %)', y:".$trimes4.",key:4,key2:400},";

  
$rapp="<script type=\"text/javascript\">
Highcharts.chart('container', {

  chart: {
   type: 'column'
   },
   title: {
    text: '".lang("messages_lang.budget_exec")."   ".$titr_deux."<br> ".number_format($data_total,0,',',' ')." BIF',
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
                  $(\"#idcod\").html(\" ".lang("messages_lang.detail_budget_exec")."\");
                  $(\"#idobj\").html(\"".lang("messages_lang.labelle_programme")."\");
                  (\"#titre\").html(\"".lang("messages_lang.action_list")."\");
                  }else if(this.key2==3){
                    (\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                    $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                    $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");

                    $(\"#titre\").html(\"".lang("messages_lang.detail_budget_exec")."\");
                    }else if(this.key2==5){
                      $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                      $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                      $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                      $(\"#titre\").html(\"".lang("messages_lang.detail_budget_exec")."\");
                      }else if(this.key2==6){
                       $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                       $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                       $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                       $(\"#titre\").html(\"".lang("messages_lang.detail_budget_exec")."\");
                       }else if(this.key2==1){
                        $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                        $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                        $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                        $(\"#titre\").html(\"".lang("messages_lang.detail_budget_exec")."\");
                        }else{
                         $(\"#idpro\").html(\" ".lang("messages_lang.labelle_programme")."  \");
                         $(\"#idcod\").html(\"".lang("messages_lang.objectif_programm")."\");
                         $(\"#idobj\").html(\" ".lang("messages_lang.code_programm")."\");
                         $(\"#titre\").html(\"".lang("messages_lang.detail_budget_exec")."\");
                       }
                       $(\"#Budget\").html(\" ".lang("messages_lang.budget_total")."\");
                       $(\"#myModal\").modal('show');
                       var row_count ='1000000';
                       $(\"#mytable\").DataTable({
                         \"processing\":true,
                         \"serverSide\":true,
                         \"bDestroy\": true,
                         \"ajax\":{
                          url:\"".base_url('dashboard/Dashbord_General_Execution/detail_general_execution')."\",
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
                           IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
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
                           'copy', 'csv', 'excel', 'pdf', 'print'
                           ],
                           language: {
                            \"sProcessing\":     \"".lang('messages_lang.labelle_et_traitement')."...\",
                            \"sSearch\":         \"".lang('messages_lang.search_button')."&nbsp;:\",
                            \"sLengthMenu\":     \"".lang('messages_lang.labelle_et_afficher')." _MENU_ ".lang('messages_lang.labelle_et_element')."\",
                            \"sInfo\":           \"".lang('messages_lang.labelle_et_affichage_element')." _START_ ".lang('messages_lang.labelle_et_a')." _END_ ".lang('messages_lang.labelle_et_sur')." _TOTAL_ ".lang('messages_lang.labelle_et_element')."\",
                            \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 ".lang('messages_lang.labelle_et_sur')." 0 ".lang('messages_lang.labelle_et_element')."\",
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
                          format: '{point.y:,.2f} %'
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
                           name:'".lang("messages_lang.th_projets")."',
                           data: [".$data_budget_req."]
                         }
                         ]
                         });
                         </script>
                         ";
                         $rapp_activ="<script type=\"text/javascript\">
                         Highcharts.chart('container_active', {
                           chart: {
                            type: 'column'
                            },
                            title: {
                              text: ' ".lang("messages_lang.activite_exec")." ".$titr_deux2."<br> ".number_format($data_activite_total,0,',',' ')." ".strtolower(lang("messages_lang.table_activite"))."',
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
                                           $(\"#idcod\").html(\" Objctif&nbspde&nbspl\'action \");
                                           $(\"#idobj\").html(\"".lang("messages_lang.labelle_programme")."\");
                                           $(\"#titre\").html(\"".lang("messages_lang.detail_activite")."\");
                                           }else if(this.key2==3){
                                            $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                            $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                            $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                            $(\"#titre\").html(\"".lang("messages_lang.detail_activite")."\");
                                            }else if(this.key2==5){
                                             $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                             $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                             $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                             }else if(this.key2==6){
                                              $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                              $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                              $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                              }else if(this.key2==1){
                                               $(\"#idpro\").html(\" ".lang("messages_lang.label_droit_activite")."\");
                                               $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                               $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                               }else{
                                                $(\"#idpro\").html(\" ".lang("messages_lang.labelle_programme")."  \");
                                                $(\"#idcod\").html(\"".lang("messages_lang.objectif_programm")."\");
                                                $(\"#idobj\").html(\" ".lang("messages_lang.code_programm")." \");
                                              }
                                              $(\"#titre\").html(\"".lang("messages_lang.detail_activite")."\");
                                              $(\"#myModal\").modal('show');
                                              var row_count ='1000000';
                                              $(\"#mytable\").DataTable({
                                                \"processing\":true,
                                                \"serverSide\":true,
                                                \"bDestroy\": true,
                                                \"ajax\":{
                                                 url:\"".base_url('dashboard/Dashbord_General_Execution/detail_general_execution')."\",
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
                                       
                                          IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),

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
                                                  'copy', 'csv', 'excel', 'pdf', 'print'
                                                  ],
                                                  language: {
                                                    \"sProcessing\":     \"".lang('messages_lang.labelle_et_traitement')."...\",
                                                    \"sSearch\":         \"".lang('messages_lang.search_button')."&nbsp;:\",
                                                    \"sLengthMenu\":     \"".lang('messages_lang.labelle_et_afficher')." _MENU_ ".lang('messages_lang.labelle_et_element')."\",
                                                    \"sInfo\":           \"".lang('messages_lang.labelle_et_affichage_element')." _START_ ".lang('messages_lang.labelle_et_a')." _END_ ".lang('messages_lang.labelle_et_sur')." _TOTAL_ ".lang('messages_lang.labelle_et_element')."\",
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
                                                  format: '{point.y:,1f}'
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
                                                   name:'".lang("messages_lang.label_droit_activite")."',
                                                   data: [".$data_activite_req."]
                                                 }
                                                 ]
                                                 });
                                                 </script>
                                                 ";


                   $rapp1="<script type=\"text/javascript\">
                   Highcharts.chart('container1', {

                    chart: {
                     type: 'pie'
                     },
                     title: {
                      text: ' Budget exécuté par tranche <br> ".number_format($trimes1+$trimes2+$trimes3+$trimes4,0,',',' ')." BIF',
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
                          pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
                          shared: true,
                          useHTML: true
                          },
                          plotOptions: {
                            pie: {
                              pointPadding: 0.2,
                              borderWidth: 0,
                              depth: 40,
                              cursor:'pointer',
                              point:{
                               events: {
                                click: function(){

                                 if(this.key2==100){

                                  $(\"#Budget\").html(\" Budget&nbspdu&nbsp1er&nbsptrimestre  \");
                                  $(\"#idcod\").html(\"Objctif&nbspde&nbspl\'action\");
                                  $(\"#idobj\").html(\"".lang("messages_lang.labelle_programme")."\");


                                  }else if(this.key2==200){
                                   $(\"#Budget\").html(\" Budget&nbspdu&nbsp2e&nbsptrimestre  \");
                                   $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                   $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                   $(\"#titre\").html(\"".lang("messages_lang.titre_budget_exec")."\");
                                   }else if(this.key2==300){
                                    $(\"#Budget\").html(\"".lang("messages_lang.budget_troisieme")."\");
                                    $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                    $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                    $(\"#titre\").html(\"".lang("messages_lang.titre_budget_exec")."\");
                                    }else{
                                     $(\"#idpro\").html(\" ".lang("messages_lang.labelle_programme")."s  \");
                                     $(\"#idcod\").html(\"".lang("messages_lang.objectif_programm")."\");
                                     $(\"#idobj\").html(\" ".lang("messages_lang.code_programm")." \");
                                     $(\"#Budget\").html(\" ".lang("messages_lang.budget_quatrieme")."\"); 
                                   }
                                   $(\"#titre\").html(\"".lang("messages_lang.titre_budget_exec")."\");
                                   $(\"#myModal\").modal('show');
                                   var row_count ='1000000';
                                   $(\"#mytable\").DataTable({
                                     \"processing\":true,
                                     \"serverSide\":true,
                                     \"bDestroy\": true,
                                     \"order\":[],
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
                                     lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
                                        \"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 ".lang('messages_lang.labelle_et_sur')." 0 ".lang('messages_lang.labelle_et_element')."\",
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
                                            }                   }

                                            });
                                          }
                                        }
                                        },
                                        dataLabels: {
                                         enabled: true,
                                         format: '{point.name} : {point.y:,1f} BIF'
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
                                           name:'".lang('messages_lang.labelle_montant')."',
                                           data: [".$data_trimestre1.$data_trimestre2.$data_trimestre3.$data_trimestre4."]
                                         }
                                         ]
                                         });
                                         </script>";

####rapport des budgets vote par grande masse
             $grande_masse="SELECT if(inst_grande_masse.GRANDE_MASSE_ID in (6,7,8),7,inst_grande_masse.GRANDE_MASSE_ID) as ID, if(inst_grande_masse.GRANDE_MASSE_ID in (6,7,8),'transferts et subsides',inst_grande_masse.DESCRIPTION_GRANDE_MASSE) as NAME,SUM(`ENG_BUDGETAIRE`) as MONTANT FROM execution_budgetaire  JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_grande_masse ON ptba_tache.GRANDE_MASSE_ID=inst_grande_masse.GRANDE_MASSE_ID   JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ".$cond_affectations1." GROUP BY ID,NAME ORDER BY MONTANT DESC";
             $nbre_grande_masse=$this->ModelPs->getRequete(' CALL getTable("'.$grande_masse.'")');
             $donnees="";
             $total=0;
             foreach ($nbre_grande_masse  as $key) 
             { 
              $total+=$key->MONTANT;
              $MONTANT=$key->MONTANT >0  ? $key->MONTANT : '0';
              $pourcent=0;
              $taux2=("SELECT SUM(ENG_BUDGETAIRE) AS tau FROM `execution_budgetaire` JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ".$cond_affectations1." ");
              $taux3=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
              if ($taux3['tau']>0){
               $pourcent=($MONTANT/$taux3['tau'])*100;
             }
             $donnees.="{name:'".trim(str_replace("'", "\'", $key->NAME))."',y:".$pourcent.",key:".$key->ID."},";
           }

               $rapp_gde_masse="<script type=\"text/javascript\">
                                       Highcharts.chart('container_gde_masse', 
                                       { 
                                         chart: {
                                           type: 'bar'
                                           },
                                           title: {
                                            text: '<b> ".lang("messages_lang.tb_grande_masse")."</b>'
                                            },
                                            subtitle: {
                                              text: '<b>Le ".strftime('%d-%m-%Y',strtotime(Date('Y-m-d')))."</b> <br>  ".number_format($total,0,',',' ')." BIF'
                                              },
                                              xAxis: {
                                               type: 'category',

                                               },
                                               yAxis: {
                                                min: 0,
                                                title: {
                                                 text: ''
                                               }
                                               },
                                               tooltip: {
                                                 headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
                                                 pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
                                                 shared: true,
                                                 useHTML: true
                                                 },
                                                 plotOptions: {
                                                   bar: {
                                                    pointPadding: 0.2,
                                                    borderWidth: 0,
                                                    depth: 40,
                                                    cursor:'pointer',
                                                    point:{
                                                      events: {
                                                       click: function(){
                                                        $(\"#myModal_masse\").modal('show');
                                                        $(\"#titre_masse\").html(\"".lang("messages_lang.titr_detail_budget_exec")."\");
                                                        var row_count ='1000000';
                                                        $(\"#mytable_masse\").DataTable({
                                                         \"processing\":true,
                                                         \"serverSide\":true,
                                                         \"bDestroy\": true,
                                                         \"oreder\":[],
                                                         \"ajax\":{
                                                          url:\"".base_url('dashboard/Dashbord_General_Execution/detail_execution_Gdemasse')."\",
                                                          type:\"POST\",
                                                          data:{
                                                           key:this.key,
                                                    TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                                    INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                                    SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                                                  PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                                  ACTION_ID:$('#ACTION_ID').val(),
                                                   LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                                   PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),
                                                   IS_PRIVATE:$('#IS_PRIVATE').val(),
                                                         }
                                                         },
                                                         lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
                                                         pageLength:5,
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
                                                        dataLabels:{
                                                         enabled: true,
                                                         format: '{point.y:.1f} %'
                                                         },

                                                         showInLegend: false
                                                       }
                                                       }, 
                                                       credits: {
                                                         enabled: true,
                                                         href: \"\",
                                                         text: \"Mediabox\"
                                                         },
                                                         labels: {
                                                          items: [{
                                                           html: '',
                                                           style: {
                                                            left: '50px',
                                                            top: '18px',
                                                            color: ( // theme
                                                            Highcharts.defaultOptions.title.style &&
                                                            Highcharts.defaultOptions.title.style.color
                                                            ) || 'black'
                                                          }
                                                          }]
                                                          },
                                                          series: [
                                                          {
                                                            name:'".lang("messages_lang.labelle_grandes_masses")."',
                                                            data: [".$donnees."],

                                                          }
                                                          ]
                                                          })
                                                          </script>
                                                          ";
####### rapport des budget par mouvement dépense
        $mouvement_depense="SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as engage, SUM(execution_budgetaire.ENG_JURIDIQUE) as juridiq, SUM(execution_budgetaire.LIQUIDATION) as liquid, SUM(execution_budgetaire.ORDONNANCEMENT) as ordonnance, SUM(execution_budgetaire.PAIEMENT) as paiement, SUM(execution_budgetaire.DECAISSEMENT) as decaisse FROM execution_budgetaire LEFT JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_trim." ".$cond_affectations1." ";
        $nbre_mvt_depense=$this->ModelPs->getRequeteOne(' CALL getTable("'.$mouvement_depense.'")');
        $tauxx=("SELECT SUM(ENG_BUDGETAIRE+ENG_JURIDIQUE+LIQUIDATION+ORDONNANCEMENT+PAIEMENT+DECAISSEMENT) AS taux FROM `execution_budgetaire` JOIN ptba_tache on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ");
        $taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tauxx.'")');

        $donnees_mvt="";
        $total_mouvement=0;

        $pourcent=0;
        if ($taux1['taux']>0)
        {
          $pourcent=($nbre_mvt_depense['engage']/$taux1['taux'])*100;
        }
        $pourcenBUDGET_T1=0;
        $pourcenBUDGET_T2=0;
        $pourcenBUDGET_T3=0;
        $pourcenBUDGET_T4=0;
        if ($taux1['taux']>0)
        {
          $pourcenBUDGET_T1=($nbre_mvt_depense['juridiq']/$taux1['taux'])*100;
          $pourcenBUDGET_T2=($nbre_mvt_depense['liquid']/$taux1['taux'])*100;
          $pourcenBUDGET_T3=($nbre_mvt_depense['ordonnance']/$taux1['taux'])*100;
          $pourcenBUDGET_T4=($nbre_mvt_depense['paiement']/$taux1['taux'])*100;
          $pourcent5=($nbre_mvt_depense['decaisse']/$taux1['taux'])*100;
        }
        $engagee= ($nbre_mvt_depense['engage']>0) ? $nbre_mvt_depense['engage'] : 0 ;
        $juridique= ($nbre_mvt_depense['juridiq']>0) ? $nbre_mvt_depense['juridiq'] : 0 ;
        $liquidat= ($nbre_mvt_depense['liquid']>0) ? $nbre_mvt_depense['liquid'] : 0 ;
        $ordonance= ($nbre_mvt_depense['ordonnance']>0) ? $nbre_mvt_depense['ordonnance'] : 0 ; 
        $paiementt= ($nbre_mvt_depense['paiement']>0) ? $nbre_mvt_depense['paiement'] : 0 ;
        $decais= ($nbre_mvt_depense['decaisse']>0) ? $nbre_mvt_depense['decaisse'] : 0 ;
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_eng_budget")."', y:".$pourcent.",key:0},";
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_eng_jud")."', y:".$pourcenBUDGET_T1.",key:1},";
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_liquidation")."', y:".$pourcenBUDGET_T2.",key:2},";
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_ordonan")."', y:".$pourcenBUDGET_T3.",key:3},";
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_paiement").":', y:".$pourcenBUDGET_T4.",key:4},";
        $donnees_mvt.="{name:'".lang("messages_lang.labelle_decaisse")."', y:".$pourcent5.",key:5},";
        $total_mouvement=$engagee+$juridique+$liquidat+$ordonance+$paiementt+$decais;

              $rapp_mouvement="<script type=\"text/javascript\">
              Highcharts.chart('container_mouvement', {

               chart: {
                 type: 'pie'
                 },
                 title: {
                   text: '".lang("messages_lang.tbd_budget_exec_mvt_depense")."<br>', 
                   },  
                   subtitle: {
                    text: '<b>Le ".strftime('%d-%m-%Y',strtotime(Date('Y-m-d')))."</b> <br>  ".number_format($total_mouvement,0,',',' ')." BIF'
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
                         pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
                         shared: true,
                         useHTML: true
                         },
                         plotOptions: {
                           pie: {
                             pointPadding: 0.2,
                             borderWidth: 0,
                             depth: 40,
                             cursor:'pointer',
                             point:{
                               events: {
                                 click: function(){
                                   if(this.key2==100){
                                     $(\"#Budget\").html(\"".lang("messages_lang.budget_premier")." \");
                                     $(\"#idcod\").html(\" ".lang("messages_lang.objectif_action")." \");
                                     $(\"#idobj\").html(\"".lang("messages_lang.labelle_programme")."\");
                                     }else if(this.key2==200){
                                       $(\"#Budget\").html(\" ".lang("messages_lang.budget_deuxieme")."  \");
                                       $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                       $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                       $(\"#titre\").html(\"".lang("messages_lang.titre_detail_exec_mvt_depense")."\");
                                       }else if(this.key2==300){
                                         $(\"#Budget\").html(\"".lang("messages_lang.budget_troisieme")."\");
                                         $(\"#idcod\").html(\" ".lang("messages_lang.Actions")."\");
                                         $(\"#idobj\").html(\" ".lang("messages_lang.labelle_programme")." \");
                                         $(\"#titre\").html(\"".lang("messages_lang.titre_detail_exec_mvt_depense")."\");
                                         }else{
                                           $(\"#idpro\").html(\" ".lang("messages_lang.labelle_programme")."  \");
                                           $(\"#idcod\").html(\"".lang("messages_lang.objectif_programm")." \");
                                           $(\"#idobj\").html(\" ".lang("messages_lang.code_programm")." \");
                                           $(\"#Budget\").html(\" ".lang("messages_lang.budget_quatrieme")."\"); 
                                         }
                                         $(\"#titre_mvt\").html(\"".lang("messages_lang.titre_detail_exec_mvt_depense")."\");
                                         $(\"#myModal_mvt\").modal('show');
                                         var row_count ='1000000';
                                         $(\"#mytable_mvt\").DataTable({
                                           \"processing\":true,
                                           \"serverSide\":true,
                                           \"bDestroy\": true,
                                           \"oreder\":[],
                                           \"ajax\":{
                                             url:\"".base_url('dashboard/Dashbord_General_Execution/detail_execution_mouvement')."\",
                                             type:\"POST\",
                                             data:{
                                              key:this.key,
                                              TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                                              INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                              SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
                                              PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                              ACTION_ID:$('#ACTION_ID').val(),
                                              LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
                                              PAP_ACTIVITE_ID:$('#PAP_ACTIVITE_ID').val(),
                                              IS_PRIVATE:$('#IS_PRIVATE').val(),
                                            }
                                            },
                                            lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
                                           format: '{point.name} : {point.y:.1f} %'
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
                                             name:'".lang("messages_lang.labelle_montant")."',
                                             data: [".$donnees_mvt."]
                                           }
                                           ]
                                           });
                                           </script>
                                           ";

                       $inst= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                       if (!empty($TYPE_INSTITUTION_ID))
                       {
                         $inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID as CODE_INSTITUTION  FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_affectations.' group BY DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';
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



                       $soustutel= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                       if ($INSTITUTION_ID != '')
                       {
                         $soustutel_sect="SELECT DISTINCT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel   WHERE 1 ".$cond1." ORDER BY inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL ASC ";
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
                       $program= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                       if (!empty($PROGRAMME_ID))
                       {
                         $inst_sect='SELECT DISTINCT inst_institutions.INSTITUTION_ID, inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION  ORDER BY DESCRIPTION_INSTITUTION ASC ';
                         $inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
                         foreach ($inst_sect_req as $key)
                         {
                           if (!empty($INSTITUTION_ID))
                           { 
                             if ($INSTITUTION_ID==$key->INSTITUTION_ID) 
                             {
                               $inst.= "<option value ='".$key->INSTITUTION_ID."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                             }
                             else
                             {
                               $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                             }
                           }
                           else
                           {
                             $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                           }
                         }
                       }
                       $program= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                       if ($SOUS_TUTEL_ID != '')
                       {
                        $program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME ,inst_institutions_programmes.PROGRAMME_ID FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.PROGRAMME_ID=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."' ".$cond_program."  ORDER BY inst_institutions_programmes.PROGRAMME_ID ASC";

                        $program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
                        foreach ($program_sect_req as $key)
                        {
                         if (!empty($PROGRAMME_ID))
                         {  
                           if ($PROGRAMME_ID==$key->PROGRAMME_ID) 
                           {
                             $program.= "<option value ='".$key->PROGRAMME_ID."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
                           }
                           else
                           {
                             $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                           }
                         }
                         else
                         {
                           $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                         }
                       }
                     }
                     $actions= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                     if ($PROGRAMME_ID != '')
                     {
                       $actions_sect='SELECT `ACTION_ID`, `PROGRAMME_ID`, `CODE_ACTION`, `LIBELLE_ACTION` FROM `inst_institutions_actions` WHERE 1 AND PROGRAMME_ID='.$PROGRAMME_ID;
                       $actions_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$actions_sect.'")');
                       foreach ($actions_sect_req as $key)
                       {
                         if (!empty($ACTION_ID))
                         {  
                           if ($ACTION_ID==$key->ACTION_ID) 
                           {
                             $actions.= "<option value ='".$key->ACTION_ID."' selected>".trim($key->LIBELLE_ACTION)."</option>";
                           }
                           else
                           {
                             $actions.= "<option value ='".$key->ACTION_ID."'>".trim($key->LIBELLE_ACTION)."</option>";
                           }
                         }
                         else
                         {
                           $actions.= "<option value ='".$key->ACTION_ID."'>".trim($key->LIBELLE_ACTION)."</option>";
                         }
                       }
                     }

                $ligne_budgetaires= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                     if ($ACTION_ID != '')
                     {
                  $ligne_budgetaire_sect='SELECT DISTINCT inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID FROM  ptba_tache RIGHT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ptba_tache.ACTION_ID='.$ACTION_ID;

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

                
             echo json_encode($arrayName = array('rapp' =>$rapp ,'rapp_gde_masse'=>$rapp_gde_masse,'rapp_mouvement'=>$rapp_mouvement,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires,'ligne_activite'=>$ligne_activite,'inst'=>$inst, 'soustutel'=>$soustutel));
                 }

              function detail_general_executions() 
                   {
                $data=$this->urichk();
                $db = db_connect(); 
                $session  = \Config\Services::session();
                $KEY=$this->request->getPost('key');
                $KEY2=$this->request->getPost('key2');
                $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
                $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
                $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
                $ACTION_ID=$this->request->getPost('ACTION_ID');
                $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
                $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
                $cond1='';
                $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
                $inst_conn=$this->request->getVar('inst_conn');
                $user_id =$inst_conn;
                $cond_affectations="";
                $cond_affectations1="";
                  if (!empty($user_id)) {
                    $profil_user_req=("SELECT `PROFIL_ID` FROM `user_users` WHERE USER_ID=".$user_id." AND `IS_ACTIVE`=1");
                    $profil_user=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user_req.'")');
                    $user_affectation=("SELECT inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
                    $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
                    $nombre=count($user_affectations);
                    if ($nombre>0) {
                      if ($nombre==1) {
                        foreach ($user_affectations as $value) {
                         $cond_affectations=" AND inst_institutions.INSTITUTION_ID= ".$value->CODE_INSTITUTION;
                         $cond_affectations1=" AND ptba_tache.INSTITUTION_ID= ".$value->CODE_INSTITUTION;
                       }
                     }else if ($nombre>1){
                      $inst="(";
                      foreach ($user_affectations as $value) {
                       $inst.=$value->CODE_INSTITUTION.",";
                     }
                       //Enlever la dernier virgule
                     $inst = substr($inst, 0, -1);
                     $inst=$inst.")";
                     $cond_affectations.=" AND inst_institutions.INSTITUTION_ID IN ".$inst;
                     $cond_affectations1.=" AND ptba_tache.INSTITUTION_ID IN ".$inst;
                   }
                 }      	
               }

               $cond='';
               if(! empty($TYPE_INSTITUTION_ID))
               {
                 $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
               }

               if(! empty($INSTITUTION_ID))
               {
                 $cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
               }
               if(! empty($PROGRAMME_ID))
               {
                 $cond.=" AND ptba_tache.INSTITUTION_ID='".$PROGRAMME_ID."'";
               }

               if(!empty($ACTION_ID))
               {
                 $cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";  
               }

               if(!empty($LIGNE_BUDGETAIRE))
               {
                 $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE='".$LIGNE_BUDGETAIRE."'";  
               }

              //  if(!empty($ANNEE_BUDGETAIRE_ID))
              //  {
              //   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";  
              // }

              if(!empty($PAP_ACTIVITE_ID))
               {
                $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";  
              }

              $cond_trim='';
              if ($IS_PRIVATE==1){
                $totaux='SUM(BUDGET_BUDGET_T1)';
                $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
              }else if ($IS_PRIVATE==2){
                $totaux='SUM(BUDGET_BUDGET_T2)';
                $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
              }else if ($IS_PRIVATE==3){
                $totaux='SUM(BUDGET_BUDGET_T3)';
                $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
              }else if ($IS_PRIVATE==4){
                $totaux='SUM(BUDGET_BUDGET_T4)';
                $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
              }else{
                $totaux='SUM(BUDGET_BUDGET_T1+BUDGET_BUDGET_T2+BUDGET_BUDGET_T3+BUDGET_BUDGET_T4)';
                $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID IN(1,2,3,4)" ;
              }

              $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
            $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,execution_budgetaire.QTE_RACCROCHE, `DESC_TACHE`, `RESULTAT_ATTENDUS_TACHE`, date_format(execution_budgetaire.DATE_DEMANDE,'%d-%m-%Y')as datee, REPLACE(RTRIM(execution_budgetaire.`ENG_BUDGETAIRE`),' ','') AS ENG_BUDGETAIRE FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN execution_budgetaire on execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID  WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ";

              $limit='LIMIT 0,10';
              if($_POST['length'] != -1)
              {
                $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
              }
              $order_by='';
              if($_POST['order']['0']['column']!=0) {
               $order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC'; 
             }
             $search = !empty($_POST['search']['value']) ? (" AND ( inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%' OR execution_budgetaire.ENG_BUDGETAIRE LIKE '%$var_search%')") : '';
             $critere=" ";
             if ($KEY2==6)
             {
               $critere=" AND ptba_tache.INSTITUTION_ID='".$KEY."'";
             }else if ($KEY2==5){
               $critere=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";

             }else if ($KEY2==3)
             {
               $critere=" AND ptba_tache.ACTION_ID='".$KEY."'";
             }else if ($KEY2==4)
             {
              $critere=" AND ptba_tache.PTBA_TACHE_ID='".$KEY."'";
            } else if ($KEY2==2) {
             $critere=" AND ptba_tache.PROGRAMME_ID='".$KEY."'";

           } else if ($KEY2==100 OR $KEY2==200 OR $KEY2==300 OR $KEY2==400 ) {
             $critere=" ";
           }else{
             $critere=" AND ptba_tache.INSTITUTION_ID='".$KEY."'";	
           }
           $conditions=$query_principal." ".$critere." ".$search." ".$order_by." ".$limit;
           $query_filter=$query_principal." ".$critere." ".$search;
           $query_secondaire = 'CALL `getTable`("' . $conditions . '");';
           $fetch_data = $this->ModelPs->datatable($query_secondaire);
           $u=0;
           $data = array();
           foreach ($fetch_data as $row) 
           {
             $u++;
             $sub_array=array();
             $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
             if (strlen($row->DESC_TACHE) > 10){ 
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
              $sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
              $sub_array[] = mb_substr($row->DESC_TACHE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
              $sub_array[] = mb_substr($row->LIBELLE_ACTION, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>';
              $sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
            }else{
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>'; 	
            }
            $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->datee.'</label></font> </center>';
            $data[] = $sub_array;   
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
  function detail_execution_Gdemasses(){
          $data=$this->urichk();
          $db = db_connect(); 
          $session  = \Config\Services::session();
          $KEY=$this->request->getPost('key');
          $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
          $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
          $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
          $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
          $ACTION_ID=$this->request->getPost('ACTION_ID');
          $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
          $cond1='';
          $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
          $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
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
        $cond='';
        if(!empty($TYPE_INSTITUTION_ID)){
          $cond.=" AND inst_institutions.`TYPE_INSTITUTION_ID`=".$TYPE_INSTITUTION_ID; 
        }
        if(! empty($INSTITUTION_ID))
        {
         $cond.=' AND ptba_tache.INSTITUTION_ID='.$INSTITUTION_ID;
      }
      if(! empty($PROGRAMME_ID))
      {
        $cond.=' AND ptba_tache.PROGRAMME_ID='.$PROGRAMME_ID;
      }
      if(! empty($ACTION_ID))
      {
        $cond.=' AND ptba_tache.ACTION_ID='.$ACTION_ID;
      }

      // if(!empty($ANNEE_BUDGETAIRE_ID))
      //          {
      // $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";  
      //         }
        if(!empty($PAP_ACTIVITE_ID))
            {
        $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";  
          }

      $cond_trim='';
      if ($IS_PRIVATE==1){
        $totaux='SUM(BUDGET_BUDGET_T1)';
        $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
      }else if ($IS_PRIVATE==2){
      $totaux='SUM(BUDGET_BUDGET_T2)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3){
      $totaux='SUM(BUDGET_BUDGET_T3)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
        $totaux='SUM(BUDGET_BUDGET_T4)';
        $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
      }else{
        $totaux='SUM(BUDGET_BUDGET_T1+BUDGET_BUDGET_T2+BUDGET_BUDGET_T3+BUDGET_BUDGET_T4)';
        $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID IN(1,2,3,4)" ;
      }

      $cond11='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
        $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
        $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
        $nombre=count($user_connect_req);
          if ($nombre>1) {
          $cond11.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";
        }else{
          $cond11.='';	
        }
      }
        $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
        $var_search = str_replace("'", "\'", $var_search);
        $var_search = addcslashes($var_search,"'"); 
        $query_principal="SELECT inst_grande_masse.GRANDE_MASSE_ID,inst_grande_masse.DESCRIPTION_GRANDE_MASSE, inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba_tache.DESC_TACHE,execution_budgetaire.ENG_BUDGETAIRE,execution_budgetaire.QTE_RACCROCHE,ptba_tache.RESULTAT_ATTENDUS_TACHE, inst_grande_masse.DESCRIPTION_GRANDE_MASSE,execution_budgetaire.DATE_DEMANDE as datee FROM ptba_tache JOIN execution_budgetaire ON execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID=ptba_tache.GRANDE_MASSE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond11." ".$cond1." ".$cond_trim.""; 
       

          $limit='LIMIT 0,10';
            if ($_POST['length'] != -1)
              {
              $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
                }
            $order_by = '';
            $order_column=array(1,1,1,1,1,1,1,1);
            $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';
            $search = !empty($_POST['search']['value']) ? (" AND ( inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESCRIPTION_GRANDE_MASSE LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION  LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' )") : '';
            $critere='';
              if ($KEY==7) {
             $critere=' AND inst_grande_masse.GRANDE_MASSE_ID in (6,7,8)';
                }else{
                $critere=' AND inst_grande_masse.GRANDE_MASSE_ID='.$KEY;
               }
              $conditions=$query_principal." ".$critere." ".$search." ".$order_by." ".$limit;
               $query_filter=$query_principal." ".$critere." ".$search;
               $query_secondaire='CALL `getTable`("'.$conditions.'")';
               $fetch_res= $this->ModelPs->datatable($query_secondaire);
               $data = array();		
               $u=1;
               foreach ($fetch_res as $row) {
              $u++;
              $sub_array=array();
              $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
                 if (strlen($row->DESC_TACHE) > 10){ 
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
              $sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
              
              $sub_array[] = mb_substr($row->DESC_TACHE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
              $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
              $sub_array[] = mb_substr($row->LIBELLE_ACTION, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>';
                   $sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
                 }else{
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
                   

                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
                   $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
                 }
                 $sub_array[] ='<center><font color="#000000" size=2><label>'.date("d-m-Y",strtotime($row->datee)).'</label></font> </center>';
                 $data[] = $sub_array;
               }
               $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("'.$query_principal.'")');
               $recordsFiltered = $this->ModelPs->datatable(' CALL `getTable`("'.$query_filter.'")');

               $output = array(
                 "draw" => intval($_POST['draw']),
                 "recordsTotal" =>count($recordsTotal),
                 "recordsFiltered" =>count($recordsFiltered),
                 "data" => $data
               );
               echo json_encode($output);
             }

//Details du rapport Budget executé par mouvement de dépense

         function detail_execution_mouvements(){
          $data=$this->urichk();
          $db = db_connect(); 
          $session  = \Config\Services::session();
          $KEY=$this->request->getPost('key');
          $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
          $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
          $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
          $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
          $ACTION_ID=$this->request->getPost('ACTION_ID');
          $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
          $cond1='';
          $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
          $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
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
        $cond='';
        if(!empty($TYPE_INSTITUTION_ID)){
         $cond.=" AND inst_institutions.`TYPE_INSTITUTION_ID`=".$TYPE_INSTITUTION_ID; 
       }
       if(! empty($INSTITUTION_ID))
       {
        $cond.=' AND ptba_tache.INSTITUTION_ID='.$INSTITUTION_ID;
      }

      if(! empty($PROGRAMME_ID))
      {
        $cond.=' AND ptba_tache.PROGRAMME_ID='.$PROGRAMME_ID;
      }

      if(! empty($ACTION_ID))
      {
        $cond.=' AND ptba_tache.ACTION_ID='.$ACTION_ID;
      }
      if(! empty($LIGNE_BUDGETAIRE))
      {
        $cond.=' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$LIGNE_BUDGETAIRE;
      }

      if(! empty($PAP_ACTIVITE_ID))
      {
      $cond.=' AND ptba_tache.PAP_ACTIVITE_ID='.$PAP_ACTIVITE_ID;
      }
      $cond_trim='';
      if ($IS_PRIVATE==1){
       $totaux='SUM(BUDGET_BUDGET_T1)';
       $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
     }else if ($IS_PRIVATE==2){
      $totaux='SUM(BUDGET_BUDGET_T2)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3){
      $totaux='SUM(BUDGET_BUDGET_T3)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
      $totaux='SUM(BUDGET_BUDGET_T4)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
    }else{
      $totaux='SUM(BUDGET_BUDGET_T1+BUDGET_BUDGET_T2+BUDGET_BUDGET_T3+BUDGET_BUDGET_T4)';
      $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID IN(1,2,3,4)" ;
    }
    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION, ptba_tache.DESC_TACHE,execution_budgetaire.ENG_BUDGETAIRE,execution_budgetaire.ENG_JURIDIQUE,execution_budgetaire.LIQUIDATION,execution_budgetaire.ORDONNANCEMENT,execution_budgetaire.PAIEMENT,execution_budgetaire.DECAISSEMENT,execution_budgetaire.QTE_RACCROCHE,ptba_tache.RESULTAT_ATTENDUS_TACHE,execution_budgetaire.DATE_DEMANDE as datee FROM ptba_tache JOIN execution_budgetaire ON execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." "; 

       
    $limit='LIMIT 0,10';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column=array(1,1,1,1,1,1,1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';
    $var_search=str_replace("'", " ", $var_search);
    $search = !empty($_POST['search']['value']) ? ('AND ( inst_institutions.DESCRIPTION_INSTITUTION LIKE "%$var_search%"  OR DESC_TACHE LIKE "%$var_search%" OR inst_institutions_actions.LIBELLE_ACTION LIKE "%$var_search%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%$var_search%" OR RESULTAT_ATTENDUS_TACHE LIKE "%$var_search%" OR DESC_TACHE LIKE "%$var_search%")') : '' ;
    $critere='';
    $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
    $query_filter=$query_principal.' '.$critere.'  '.$search;
    $query_secondaire="CALL `getTable`('".$conditions."')";
    $fetch_res= $this->ModelPs->datatable($query_secondaire);

    $data = array();		
    $u=1;
    foreach ($fetch_res as $row) {
     $u++;
     $sub_array=array();
     $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
     $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
     if (strlen($this->str_replacecatego($row->DESC_TACHE)) > 10){ 
       $sub_array[] =$this->str_replacecatego(mb_substr($row->INTITULE_MINISTERE, 0, 10)) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->INTITULE_MINISTERE).'"><i class="fa fa-eye"></i></a>';
       $sub_array[] = $this->str_replacecatego(mb_substr($row->DESC_TACHE, 0, 10)) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_JURIDIQUE,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->LIQUIDATION,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ORDONNANCEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->PAIEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->DECAISSEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
       $sub_array[] = $this->str_replacecatego(mb_substr($row->LIBELLE_ACTION, 0, 10)) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>';
       $sub_array[] = $this->str_replacecatego(mb_substr($row->INTITULE_PROGRAMME, 0, 10)) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
     }else{
    
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->INTITULE_MINISTERE).'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_BUDGETAIRE,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ENG_JURIDIQUE,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->LIQUIDATION,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->ORDONNANCEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->PAIEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->DECAISSEMENT,0,',',' ').'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QTE_RACCROCHE.'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($retVal).'</label></font> </center>';
       $sub_array[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'</label></font> </center>';
     }
     $sub_array[] ='<center><font color="#000000" size=2><label>'.date("d-m-Y",strtotime($row->datee)).'</label></font> </center>';
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

  }
  ?>
