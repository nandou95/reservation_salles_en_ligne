<?php

namespace App\Modules\dashboard\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/* @author KWIZERA.edmond@mediabox.bi (71407706)
* Dashbord des evolution global
* le 29/08/2023 au 19 
*/

/* @author charbel@mediabox.bi (76887837)
* Ajout de liste et exportation
* le 26/02/2024 
*/

/* @author claude@mediabox.bi (69641375)
* Adaptation au nouveau format ptba Version3
* le 05/12/2024 
*/
//Appel de l'espace de nom du Controllers
class Dashboard_Performence_Excution extends BaseController
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
  // DESC_PAP_ACTIVITE

  public function index()
 	{  
 		$db=db_connect();
 		$data=$this->urichk();
 		$session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_EXECUTION')!=1)
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
 		$data['TYPE_INSTITUTION_ID']=$this->request->getPost('');
 		$date_select=date('m');
 		if ($date_select=='01' OR $date_select=='02' OR $date_select=='03') {
 			$date_ch='';
 			$date_ch1='';
 			$date_ch2='checked';
 			$date_ch3='';
 		}else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06') {
 			$date_ch='';
 			$date_ch1='';
 			$date_ch2='';
 			$date_ch3='checked';
 		}else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' ) {
 			$date_ch='checked';
 			$date_ch1='';
 			$date_ch2='';
 			$date_ch3='';
 		}else{
 			$date_ch='';
 			$date_ch1='checked';
 			$date_ch2='';
 			$date_ch3='';	
 		}
 		$data['ch']=$date_ch;				
 		$data['ch1']=$date_ch1;
 		$data['ch2']=$date_ch2;
 		$data['ch3']=$date_ch3;
	
    //L'id de l'année budgétaire actuelle
    $data['ann_actuel_id'] = $this->get_annee_budgetaire();
    //Selection de l'année budgétaire
    $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1  ORDER BY ANNEE_DEBUT ASC"; 
    $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
 		return view('App\Modules\dashboard\Views\Dashboard_Performence_Excution_View',$data);
 	}

  # fonction pour la liste
  public function listing_dash_perform_exec() 
  {
    $data=$this->urichk();
    $db = db_connect();
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $ACTION_ID=$this->request->getPost('ACTION_ID');
    $ACTIVITE=$this->request->getPost('ACTIVITE');
    $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
   
    $IS_DOUBLE_COMMANDE=$this->request->getPost('IS_DOUBLE_COMMANDE');
    $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
    $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
    if ($IS_PRIVATE==1) {
      $cond_trim=" AND ( exec.TRIMESTRE_ID=1)" ;
    }else if ($IS_PRIVATE==2) {
      $cond_trim=" AND exec.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3) {
      $cond_trim=" AND exec.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
      $cond_trim=" AND exec.TRIMESTRE_ID=4" ;
    }else {
      $cond_trim=" " ;
    }
    $cond='';
    if(! empty($TYPE_INSTITUTION_ID))
    {
      $cond.=' AND inst.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    }
    if(!empty($INSTITUTION_ID))
    {
      $cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
    }
    if(!empty($PROGRAMME_ID))
    {
      if ($TYPE_INSTITUTION_ID==2) {
        $cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
      }
    }

    // si l'action est selectionnée
    if(!empty($ACTION_ID))
    {
     
      $cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'"; 
    }
  
    

    if(!empty($LIGNE_BUDGETAIRE))
    {
      $cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE.""; 
    }

    if(!empty($PAP_ACTIVITE_ID))
    {
      $cond.=" AND ptba.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID.""; 
    }
   
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="SELECT inst.DESCRIPTION_INSTITUTION AS  INTITULE_MINISTERE, programme.INTITULE_PROGRAMME,actions.`LIBELLE_ACTION`,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,exec.LIQUIDATION,exec.DECAISSEMENT,exec.ENG_JURIDIQUE,exec.ORDONNANCEMENT,exec.PAIEMENT,ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID  JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID LEFT JOIN execution_budgetaire_tache_detail detail ON detail.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID  WHERE 1 ".$cond." ".$cond_trim."";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column = array(1,'inst.DESCRIPTION_INSTITUTION ','programme.INTITULE_PROGRAMME','actions.LIBELLE_ACTION','DESC_PAP_ACTIVITE','DESCRIPTION_GRANDE_MASSE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec.LIQUIDATION','exec.ORDONNANCEMENT','exec.PAIEMENT','exec.DECAISSEMENT');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.INSTITUTION_ID ASC';
    $search = !empty($_POST['search']['value']) ? ("AND (inst.DESCRIPTION_INSTITUTION  LIKE '%$var_search%' OR programme.INTITULE_PROGRAMME LIKE '%$var_search%' OR actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%')") : '';
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
      $INSTITUTION = (mb_strlen($row->INTITULE_MINISTERE) > 32) ? (mb_substr($row->INTITULE_MINISTERE, 0, 32) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_MINISTERE;
      $PROGRAMME = (mb_strlen($row->INTITULE_PROGRAMME) > 32) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 32) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;
      $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;
      $ACTION = (mb_strlen($retVal) > 32) ? (mb_substr($retVal, 0, 32) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>') : $retVal;
      $engagement[]=$INSTITUTION;
      $engagement[]=$PROGRAMME;
      $engagement[]=$ACTION;
      $engagement[]=$ACTIVITES;
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
  function exporter($TYPE_INSTITUTION_ID='',$INSTITUTION_ID='',$PROGRAMME_ID='',$ACTION_ID='',$IS_PRIVATE='',$LIGNE_BUDGETAIRE='',$PAP_ACTIVITE_ID='')
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
    $PAP_ACTIVITE_ID=$PAP_ACTIVITE_ID;
    $LIGNE_BUDGETAIRE=$LIGNE_BUDGETAIRE;
    if ($IS_PRIVATE==1) {
    $cond_trim=" AND (exec.TRIMESTRE_ID=1 or exec.TRIMESTRE_ID=0 )" ;
    }else if ($IS_PRIVATE==2) {
    $cond_trim=" AND exec.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3) {
    $cond_trim=" AND exec.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
      $cond_trim=" AND exec.TRIMESTRE_ID=4" ;
    }else {
      $cond_trim=" " ;
    }
    $cond='';
    if(! empty($TYPE_INSTITUTION_ID) )
    {
      $cond.=" AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID;
    }
    if($INSTITUTION_ID!=0)
    {
      $cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
    }
    if($PROGRAMME_ID!=0)
    {
      if ($TYPE_INSTITUTION_ID==2) 
      {
        $cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
      }
    }
    if($ACTION_ID!=0)
    {
      $cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'"; 
    }
    if($LIGNE_BUDGETAIRE !=0)
    {
      $cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
    }

   

     if($PAP_ACTIVITE_ID!=0)
     {
       $cond.=" AND ptba.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
     }
    $getRequete=" SELECT inst.DESCRIPTION_INSTITUTION AS  INTITULE_MINISTERE, programme.INTITULE_PROGRAMME,actions.`LIBELLE_ACTION`,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,exec.LIQUIDATION,exec.DECAISSEMENT,exec.ENG_JURIDIQUE,exec.ORDONNANCEMENT,exec.PAIEMENT,ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID LEFT JOIN execution_budgetaire_tache_detail detail ON detail.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID  WHERE 1 ".$cond." ".$cond_trim."";  


    $getData = $this->ModelPs->datatable('CALL getTable("' . $getRequete . '")');
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'INSTITUTION');
    $sheet->setCellValue('B1', 'PROGRAMME');
    $sheet->setCellValue('C1', 'ACTION');
    $sheet->setCellValue('D1', 'ACTIVITE');
    $sheet->setCellValue('E1', 'GRANDE MASSE');
    $sheet->setCellValue('F1', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G1', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('H1', 'LIQUIDATION');
    $sheet->setCellValue('I1', 'ORDONNANCEMENT');
    $sheet->setCellValue('J1', 'PAIEMENT');
    $sheet->setCellValue('K1', 'DECAISSEMENT');
    $rows = 3;
    //boucle pour les institutions
    foreach ($getData as $key)
    {
      $sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
      $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
      $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
      $sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
      $sheet->setCellValue('E' . $rows, $key->DESCRIPTION_GRANDE_MASSE);
      $sheet->setCellValue('F' . $rows, $key->ENG_BUDGETAIRE);
      $sheet->setCellValue('G' . $rows, $key->ENG_JURIDIQUE);
      $sheet->setCellValue('H' . $rows, $key->LIQUIDATION);
      $sheet->setCellValue('I' . $rows, $key->ORDONNANCEMENT);
      $sheet->setCellValue('J' . $rows, $key->PAIEMENT);
      $sheet->setCellValue('K' . $rows, $key->DECAISSEMENT);
      $rows++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('budget execute par grande masse.xlsx');
    return redirect('dashboard/Dashboard_Performence_Excution');
  }
  public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
  {
    $db = db_connect();
    $columnselect = str_replace("\'", "'", $columnselect);
    $table = str_replace("\'", "'", $table);
    $where = str_replace("\'", "'", $where);
    $orderby = str_replace("\'", "'", $orderby);
    $Limit = str_replace("\'", "'", $Limit);
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
    $bindparams = str_replace('\"', '"', $bindparams);
    return $bindparams;
  }
# fonction pour les details
 	function detail_Performence_Excution() 
 	{
 		$data=$this->urichk();
 		$db = db_connect(); 
 		$KEY=$this->request->getPost('key');
 		$KEY2=$this->request->getPost('key2');
 		$KEY3=$this->request->getPost('key3');
 		$KEY4=$this->request->getPost('key4');

 		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
 		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
 		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
 		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
 		$ACTION_ID=$this->request->getPost('ACTION_ID');
 		$ACTIVITE=$this->request->getPost('ACTIVITE');
 		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
    $IS_DOUBLE_COMMANDE=$this->request->getPost('IS_DOUBLE_COMMANDE');
    $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
    $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
 		if ($IS_PRIVATE==1) {
 			$totaux='BUDGET_T1';
 			$cond_trim=" AND ( exec.TRIMESTRE_ID=1 or exec.TRIMESTRE_ID=0 )" ;
 		}else if ($IS_PRIVATE==2) {
 			$totaux='BUDGET_T2';
 			$cond_trim=" AND exec.TRIMESTRE_ID=2" ;
 		}else if ($IS_PRIVATE==3){
 			$totaux='BUDGET_T3';
 			$cond_trim=" AND exec.TRIMESTRE_ID=3" ;
 		}else if ($IS_PRIVATE==4){
 			$totaux='BUDGET_T4';
 			$cond_trim=" AND exec.TRIMESTRE_ID=4" ;
 	 	}else {
 			$totaux=' BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4';
 			$cond_trim=" " ;
 		}
 		$cond='';
 		if(! empty($TYPE_INSTITUTION_ID))
 		{
 		$cond.=' AND inst.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
 		}
 		if(! empty($INSTITUTION_ID))
 		{
 		$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
 		}
 		if (! empty($SOUS_TUTEL_ID))
    {
 			$cond.=" AND ptba.SOUS_TUTEL_ID='".$SOUS_TUTEL_ID."'";
 		}
 		if(! empty($PROGRAMME_ID))
 		{
 			if ($TYPE_INSTITUTION_ID==2)
      {
 				$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
 			}
 		}
 		if(!empty($ACTION_ID))
 		{
 			$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'"; 
 		}
    // SI LA LIGNE BUDGETAIRE EST SELECTIONNEE
 		if($LIGNE_BUDGETAIRE !='')
 		{
 			$cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
 		}
     // SI L ANNEE BUDGETAIRE EST SELECTIONNEE
    // if(!empty($ANNEE_BUDGETAIRE_ID))
    // {
    //   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
    // }

    if(!empty($PAP_ACTIVITE_ID))
    {
      $cond.=" AND ptba.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID.""; 
    }

    
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");

 		$query_principal="SELECT ligne service,".$totaux." AS total,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,programme.`INTITULE_PROGRAMME`,actions.`LIBELLE_ACTION`, pap_activites.DESC_PAP_ACTIVITE, pap_activites.RESULTAT_PAP_ACTIVITE, exec.ENG_BUDGETAIRE, exec.LIQUIDATION, exec.DECAISSEMENT, exec.ENG_JURIDIQUE,exec.ORDONNANCEMENT,exec.PAIEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID  WHERE 1 ".$cond." ".$cond_trim."";

 		$limit='LIMIT 0,10';
 		if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column = array(1,'DESC_PAP_ACTIVITE','RESULTAT_PAP_ACTIVITE','actions.LIBELLE_ACTION','programme.`INTITULE_PROGRAMME`','inst.DESCRIPTION_INSTITUTION',1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.PTBA_TACHE_ID ASC';


 		$search = !empty($_POST['search']['value']) ? ("AND ( inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR programme.`INTITULE_PROGRAMME` LIKE '%$var_search%' OR actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%')") : '';
 		if ( ! empty($KEY4)) {
 			if ($KEY4==7)
 			{
 				$critere=" AND GRANDE_MASSE_ID  IN(6,7,8)";
 			}else{
 				$critere="  AND GRANDE_MASSE_ID=".$KEY4."";
 			}
 		}else{
 			$critere=' ';
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
 			$engagement=array();
 			$Services=(" SELECT DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE CODE_SOUS_TUTEL=".$row->service."");
 			$Services_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$Services.'")');
 			$retVal = (! empty($Services_req['DESCRIPTION_SOUS_TUTEL'])) ? $Services_req['DESCRIPTION_SOUS_TUTEL'] : 'N/A' ;

 			if ($KEY2==1) {
 				$mona_de=number_format($row->ENG_BUDGETAIRE,0,',',' ');
 			}else if ($KEY2==2) {
 				$mona_de=number_format($row->LIQUIDATION,0,',',' ');
 			}else if ($KEY2==3) {
 				$mona_de=number_format($row->DECAISSEMENT,0,',',' ');
 			}else if ($KEY2==4) {
 				$mona_de=number_format($row->ENG_JURIDIQUE,0,',',' ');
 			}else if ($KEY2==5) {
 				$mona_de=number_format($row->ORDONNANCEMENT,0,',',' ');	 
 			}else {
 				$mona_de=number_format($row->PAIEMENT,0,',',' ');	 
 			}
 			$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
 			$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
 			if (strlen($row->DESC_PAP_ACTIVITE) > 23){
 				$engagement[] = mb_substr($row->DESC_PAP_ACTIVITE, 0,21) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
 			}else{
 				$engagement[] ='<font color="#000000" size=2><label>'.$row->DESC_PAP_ACTIVITE.'</label></font>';
 			}
    	if (strlen($row->RESULTAT_PAP_ACTIVITE) > 28){
 				$engagement[] = mb_substr($row->RESULTAT_PAP_ACTIVITE, 0,26) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
 			}else{
 				$engagement[] ='<font color="#000000" size=2><label>'.$row->RESULTAT_PAP_ACTIVITE.'</label></font>';
 			}

 				if (strlen($retVal) > 23){
 				$engagement[] = mb_substr($retVal, 0,21) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>';
 			}else{
 				$engagement[] ='<font color="#000000" size=2><label>'.$retVal.'</label></font>';
 			}

 			if (strlen($row->INTITULE_PROGRAMME) > 32){
 				$engagement[] = mb_substr($row->INTITULE_PROGRAMME, 0,32) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
 			}else{
 				$engagement[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
 			}

 			if (strlen($row->INTITULE_MINISTERE) > 32){
 				$engagement[] = mb_substr($row->INTITULE_MINISTERE, 0,32) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
 			}else{
 				$engagement[] ='<font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font>';
 			}

 			$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
 			$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->total,0,',',' ').'</label></font> </center>';

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
 		echo json_encode($output);
 	}
		 //Fonction pou appel des series et hichart
 	public function get_rapport()
  {
   $data=$this->urichk();
   $db = db_connect(); 
   $TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
   $INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
   $PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
   $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
   $ACTION_ID=$this->request->getVar('ACTION_ID');
   $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
   $ACTIVITE=$this->request->getVar('ACTIVITE');
   $inst_conn=$this->request->getVar('inst_conn');

   $IS_DOUBLE_COMMANDE=$this->request->getVar('IS_DOUBLE_COMMANDE');
   $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
   $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
   $user_id=$inst_conn;
   $name_table= " JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID";
   $cond_inst="";
   $cond_affectations1="";
   if (!empty($user_id))
   {

    $user_affectation=("SELECT inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
    $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
    $nombre=count($user_affectations);


    if ($nombre>0) {
      if ($nombre==1) {
        foreach ($user_affectations as $value) {
         $cond_inst=" AND INSTITUTION_ID= ".$value->INSTITUTION_ID;
         $cond_affectations1=" AND ptba.INSTITUTION_ID= ".$value->INSTITUTION_ID;
       }
     }else if ($nombre>1){
      $inst="(";
      foreach ($user_affectations as $value) {
       $inst.=$value->INSTITUTION_ID.",";

     }
     $inst = substr($inst, 0, -1);
     $inst=$inst.")";
     $cond_inst.=" AND INSTITUTION_ID IN ".$inst;
     $cond_affectations1.=" AND ptba.INSTITUTION_ID IN ".$inst;

   }
 }else{
  return redirect('Login_Ptba');

}

}else{
  return redirect('Login_Ptba');
}

if ($IS_PRIVATE==1) {
  $totaux='SUM(BUDGET_T1)';
  $cond_trim=" AND exec.TRIMESTRE_ID=1" ;

}else if ($IS_PRIVATE==2) {
  $totaux='SUM(BUDGET_T2)';
  $cond_trim=" AND exec.TRIMESTRE_ID=2" ;

}else if ($IS_PRIVATE==3) {
  $totaux='SUM(BUDGET_T3)';
  $cond_trim=" AND exec.TRIMESTRE_ID=3" ;
}else if ($IS_PRIVATE==4){
  $totaux='SUM(BUDGET_T4)';
  $cond_trim=" AND exec.TRIMESTRE_ID=4" ;
}else {
  $totaux='SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
  $cond_trim=" " ;
}
$name_table= "  JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes programme ON programme.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID";
$cond='';
if(! empty($TYPE_INSTITUTION_ID))
{

  $cond.=' AND inst.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
}

if(! empty($INSTITUTION_ID))
{

  $cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";

}

if (! empty($SOUS_TUTEL_ID)) {
  $cond.=" AND ptba.SOUS_TUTEL_ID='".$SOUS_TUTEL_ID."'";
}


if(! empty($PROGRAMME_ID))
{
  if ($TYPE_INSTITUTION_ID==2) {

  
    $cond.=" AND programme.PROGRAMME_ID='".$PROGRAMME_ID."'";
    
 }
}

if(!empty($ACTION_ID))
{
  $cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'"; 
}

// Si la ligne budgétaire est choisie
if($ACTIVITE !='')
  {
  $cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$ACTIVITE.""; 
  }

if(!empty($LIGNE_BUDGETAIRE))
{
  $cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE.""; 
}

if(!empty($PAP_ACTIVITE_ID))
{
  $cond.=" AND ptba.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID.""; 
}




$data_engage='';
$data_liquide='';
$data_decaissement='';
$data_jurdique='';
$data_ordonence=''; 
$data_paie='';

$engage=("SELECT SUM(exec.ENG_BUDGETAIRE) as engage FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond." ");


$liquide=("SELECT SUM(exec.LIQUIDATION) as liquide FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID  ".$name_table."  WHERE 1 ".$cond_trim." ".$cond." ");
$decaissement=("SELECT SUM(exec.DECAISSEMENT) as decaissement FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ");
$jurdique=("SELECT SUM(exec.ENG_JURIDIQUE) as jurdique FROM  execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ");
$ordonence=("SELECT SUM(exec.ORDONNANCEMENT) as ordonence FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID  ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ");
$paiement=("SELECT SUM(exec.PAIEMENT) as paie FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID  ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ");
$tautaux=("SELECT ".$totaux." AS pourc FROM ptba_tache ptba ".$name_table." WHERE 1 ".$cond." ");
$engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$engage.'")');
$tautaux_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tautaux.'")');
$liquide_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$liquide.'")');
$decaissement_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$decaissement.'")');
$jurdique_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$jurdique.'")');
$ordonence_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$ordonence.'")');
$paiement_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$paiement.'")');
$engage_sum= ($engage_req['engage']>0) ? $engage_req['engage'] : 0 ;
$liquide_sum= ($liquide_req['liquide']>0) ? $liquide_req['liquide'] : 0 ;
$decaissement_sum= ($decaissement_req['decaissement']>0) ? $decaissement_req['decaissement'] : 0 ;
$jurdique_sum= ($jurdique_req['jurdique']>0) ? $jurdique_req['jurdique'] : 0 ;
$paie_sum= ($paiement_req['paie']>0) ? $paiement_req['paie'] : 0 ;
$ordonence_sum= ($ordonence_req['ordonence']>0) ? $ordonence_req['ordonence'] : 0 ;
$pourc_taux = ($tautaux_req['pourc'] > 0) ? $tautaux_req['pourc'] : 1 ;
$data_engage="{name:'".lang("messages_lang.labelle_eng_budget").": (".number_format($engage_sum,0,',',' ')." FBU)', y:(".$engage_sum/$pourc_taux.")*100,key2:1,key3:".$IS_PRIVATE.",color:'#00FFFF'},";
$data_liquide="{name:'".lang("messages_lang.labelle_liquidation").": (".number_format($liquide_sum,0,',',' ')." FBU)', y:(".$liquide_sum/$pourc_taux.")*100,key2:2,key3:".$IS_PRIVATE.",color:'#00008B'},";
$data_decaissement="{name:'".lang("messages_lang.labelle_decaisse")." : (".number_format($decaissement_sum,0,',',' ')." FBU)', y:(".$decaissement_sum/$pourc_taux.")*100,key2:3,key3:".$IS_PRIVATE.",color:'#808000'},";
$data_jurdique="{name:'".lang("messages_lang.labelle_liquidation").": (".number_format($jurdique_sum,0,',',' ')." FBU)', y:(".$jurdique_sum/$pourc_taux.")*100,key2:4,key3:".$IS_PRIVATE.",color:'#800080'},";
$data_ordonence="{name:'".lang("messages_lang.labelle_ordonan")." : (".number_format($ordonence_sum,0,',',' ')." FBU) ', y:(".$ordonence_sum/$pourc_taux.")*100,key2:5,key3:".$IS_PRIVATE.",color:'#800000'},";
$data_paie="{name:'".lang("messages_lang.labelle_paiement").": (".number_format($paie_sum,0,',',' ')." FBU) ', y:(".$paie_sum/$pourc_taux.")*100,key2:6,key3:".$IS_PRIVATE.",color:'#800000'},";

$excute_grande=("SELECT if(inst_grande_masse.GRANDE_MASSE_ID in (6,7,8),7,inst_grande_masse.GRANDE_MASSE_ID) as GRANDE_MASSE_ID,if(inst_grande_masse.GRANDE_MASSE_ID in (6,7,8),'transferts et subsides',DESCRIPTION_GRANDE_MASSE) as name FROM `inst_grande_masse` JOIN ptba_tache ON ptba_tache.GRANDE_MASSE_ID=inst_grande_masse.GRANDE_MASSE_ID JOIN execution_budgetaire ON execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID WHERE 1 GROUP BY name,inst_grande_masse.GRANDE_MASSE_ID ORDER BY name ASC ");
$excute_grande_req=$this->ModelPs->getRequete(' CALL getTable("'.$excute_grande.'")');
$data_engage1='';
$data_liquide1='';
$data_jurdik1='';
$data_ordo1='';
$data_decai1='';
$data_paie1='';
$total_engage1=0;
$total_liquide1=0;
$total_jurdik1=0;
$total_ordo1=0;
$total_decai1=0;
$total_paie1=0;
$cond_gr='';
foreach ($excute_grande_req as $value)
{
  if ($value->GRANDE_MASSE_ID==7) {
   $cond_gr=' AND ptba.GRANDE_MASSE_ID  IN(6,7,8)';
 }else{
   $cond_gr= ' AND ptba='.$value->GRANDE_MASSE_ID;	
 }
 $engage1=("SELECT SUM(exec.ENG_BUDGETAIRE) as engage FROM execution_budgetaire JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ".$cond_gr."");

 $liquide1=("SELECT SUM(exec.LIQUIDATION) as liquide FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ".$cond_gr."");
 $decaissement1=("SELECT SUM(execution_budgetaire.DECAISSEMENT) as decaissement FROM  execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ".$cond_gr." ");
 $jurdique1=("SELECT SUM(exec.ENG_JURIDIQUE) as jurdique FROM  execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ".$cond_gr."");
 $ordonence1=("SELECT SUM(exec.ORDONNANCEMENT) as ordonence FROM  execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."   ".$cond_gr."");
 $paiement1=("SELECT SUM(exec.PAIEMENT) as paie FROM  execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table."  WHERE 1 ".$cond_trim." ".$cond."  ".$cond_gr."");

 $tautaux1=("SELECT ".$totaux." AS pourc FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_gr." ".$cond."");

 $engage_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$engage1.'")');
 $liquide_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$liquide1.'")');
 $decaissement_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$decaissement1.'")');
 $tautaux_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tautaux1.'")');
 $jurdique_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$jurdique1.'")');
 $ordonence_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$ordonence1.'")');
 $paiement_req1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$paiement1.'")');
 $engage_sum1= ($engage_req1['engage']>0) ? $engage_req1['engage'] : 0 ;
 $liquide_sum1= ($liquide_req1['liquide']>0) ? $liquide_req1['liquide'] : 0 ;
 $decaissement_sum1= ($decaissement_req1['decaissement']>0) ? $decaissement_req1['decaissement'] : 0 ;
 $jurdique_sum1= ($jurdique_req1['jurdique']>0) ? $jurdique_req1['jurdique'] : 0 ;
 $paie_sum1= ($paiement_req1['paie']>0) ? $paiement_req1['paie'] : 0 ;
 $ordo_sum1= ($ordonence_req1['ordonence']>0) ? $ordonence_req1['ordonence'] : 0 ;
 $pourc_taux1 = ($tautaux_req1['pourc'] > 0) ? $tautaux_req1['pourc'] : 1 ;
 $data_engage1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$engage_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:1,key3:".$IS_PRIVATE."},";
 $data_liquide1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$liquide_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:2,key3:".$IS_PRIVATE."},";
 $data_jurdik1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$jurdique_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:4,key3:".$IS_PRIVATE."},";
 $data_ordo1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$ordo_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:5,key3:".$IS_PRIVATE."},";
 $data_decai1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$decaissement_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:3,key3:".$IS_PRIVATE."},";
 $data_paie1.="{name:'".$this->str_replacecatego($value->name)."', y:(".$paie_sum1/$pourc_taux1.")*100,key4:".$value->GRANDE_MASSE_ID.",key2:6,key3:".$IS_PRIVATE."},";

 $total_engage1+=$engage_req1['engage'];
 $total_liquide1=$total_liquide1+$liquide_req1['liquide'];
 $total_jurdik1=$total_jurdik1+$jurdique_req1['jurdique'];
 $total_ordo1=$total_ordo1+$ordonence_req1['ordonence'];
 $total_decai1=$total_decai1+$decaissement_req1['decaissement'];
 $total_paie1=$total_paie1+$paiement_req1['paie'];

 		}
 		$rapp="<script type=\"text/javascript\">
 		Highcharts.chart('container', {

 			chart: {
 				type: 'pie'
 				}, 
 				title: {
 					text: '<b>".lang("messages_lang.performance_exec")."</b>',
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
 										pie: {
 											pointPadding: 0.2,
 											borderWidth: 0,
 											depth: 40,
 											cursor:'pointer',
 											point:{
 												events: {
 													click: function(){
								   if(this.key2==1){
								 $(\"#idpro\").html(\"".lang("messages_lang.labelle_eng_budget")."\");
								  }else if(this.key2==2){
								 $(\"#idpro\").html(\"".lang("messages_lang.labelle_liquidation")."\");
								}else if(this.key2==3){
							  $(\"#idpro\").html(\" ".lang("messages_lang.labelle_decaisse")."\");
								}else if(this.key2==4){
								$(\"#idpro\").html(\"".lang("messages_lang.labelle_eng_jud")."\");
								}else if(this.key2==6){
									$(\"#idpro\").html(\"".lang("messages_lang.labelle_paiement")."\");
										}else{
										$(\"#idpro\").html(\"".lang("messages_lang.labelle_ordonan")."\");	
										}
									if(this.key3==1){
						  	$(\"#trim\").html(\"".lang("messages_lang.budget_troisieme")."\");
								}else if(this.key3==2){
					     $(\"#trim\").html(\"".lang("messages_lang.budget_quatrieme")."\");
 										}else if(this.key3==3){
 								$(\"#trim\").html(\"".lang("messages_lang.budget_premier")."\");
 								}else if(this.key3==5){
 								$(\"#trim\").html(\"".lang("messages_lang.budget_tous")."\");
 								}else{
 							$(\"#trim\").html(\"".lang("messages_lang.budget_deuxieme")."\");
 								}
 							$(\"#titre\").html(\"".lang("messages_lang.link_detail")."\");
 					$(\"#myModal\").modal('show');
 					var row_count ='1000000';
 					$(\"#mytable\").DataTable({
 					\"processing\":true,
 					\"serverSide\":true,
 					\"bDestroy\": true,
 					\"ajax\":{
			url:\"".base_url('dashboard/Dashboard_Performence_Excution/detail_Performence_Excution')."\",
					type:\"POST\",
 						data:{
 							key:this.key,
 							key2:this.key2,
 							key3:this.key3,
 							INSTITUTION_ID:$('#INSTITUTION_ID').val(),
 							TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
 							PROGRAMME_ID:$('#PROGRAMME_ID').val(),
 							ACTION_ID:$('#ACTION_ID').val(),
 							SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
 							IS_PRIVATE:$('#IS_PRIVATE').val(),
 							ACTIVITE:$('#ACTIVITE').val(),
              IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
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
 								'excel', 'print','pdf'
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
										format: '{point.name} :
                      {point.y:,.3f} %'
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
 										 name:'Etape',
								     data: [".$data_engage.$data_liquide.$data_paie.$data_decaissement.$data_jurdique.$data_ordonence."]
 																	}
 										]
 										});
 										</script>
 										";
              
 									$rapp1="<script type=\"text/javascript\">
 									Highcharts.chart('container1', {

 												chart: {
 													type: 'column'
 													},
 											title: {
 											text: '<b>".lang("messages_lang.tb_grande_masse")." </b>',
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
																'<td style=\"padding:0\"><b>{point.y:,.3f} </b></td></tr>',
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
													if(this.key2==1){
													$(\"#idpro\").html(\"".lang("messages_lang.labelle_eng_budget")."\");
													}else if(this.key2==2){
													$(\"#idpro\").html(\"".lang("messages_lang.labelle_liquidation")."\");
													}else if(this.key2==3){
													$(\"#idpro\").html(\" ".lang("messages_lang.labelle_decaisse")."\");
												}else if(this.key2==4){
												$(\"#idpro\").html(\"".lang("messages_lang.labelle_eng_jud")."\");
   						          }else{
 												$(\"#idpro\").html(\"".lang("messages_lang.labelle_ordonan")."\");	
 												}
 												if(this.key3==1){
 													$(\"#trim\").html(\"".lang("messages_lang.budget_troisieme")."\");
 														}else if(this.key3==2){
 													$(\"#trim\").html(\"".lang("messages_lang.budget_quatrieme")."\");
 													}else if(this.key3==3){
 													$(\"#trim\").html(\"".lang("messages_lang.budget_premier")."\");
 													}else if(this.key3==5){
 													$(\"#trim\").html(\"".lang("messages_lang.budget_tous")."\");

											}else{
											$(\"#trim\").html(\"".lang("messages_lang.budget_deuxieme")."\");
											}
									$(\"#titre\").html(\"".lang("messages_lang.link_detail")."\");
										$(\"#myModal\").modal('show');
										var row_count ='1000000';
 										$(\"#mytable\").DataTable({
 											\"processing\":true,
 											\"serverSide\":true,
 											\"bDestroy\": true,
 										\"ajax\":{url:\"".base_url('dashboard/Dashboard_Performence_Excution/detail_Performence_Excution')."\",
 							type:\"POST\",
 							data:{
 							key:this.key,
 							key2:this.key2,
 							key3:this.key3,
 							key4:this.key4,
							INSTITUTION_ID:$('#INSTITUTION_ID').val(),
							TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
							PROGRAMME_ID:$('#PROGRAMME_ID').val(),
						ACTION_ID:$('#ACTION_ID').val(),
							SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
							IS_PRIVATE:$('#IS_PRIVATE').val(),
							ACTIVITE:$('#ACTIVITE').val(),
              IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
								}
								},
 								lengthMenu: [[5,10,50,100, row_count], [5,10,50,100, \"All\"]],
 								pageLength: 5,
 							\"columnDefs\":[{
 							\"targets\":[],
 							\"orderable\":false
 								}],
 							dom: 'Bfrtlip',
 							buttons: [
 							'excel', 'print','pdf'
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
 										format: '{point.y:.3f}'
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
                      name:'Engagement&nbspbudgétaire',
                      data: [".$data_engage1."]
                      }, 
                      {
                       name:'Liquidation',
                       data: [".$data_liquide1."]
                       },
                       {
                        name:'Engagement&nbspjurdique ',
                        data: [".$data_jurdik1."]
                        },
                        {
                          name:'Ordonnancement ',
                          data: [".$data_ordo1."]
                          },
                          {
                           name:'Paiement ',
                           data: [".$data_paie1."]
                           },
                           {
                             name:'Décaissement',
                             data: [".$data_decai1."]
                             },
                             ]
                             });
                             </script>
                ";
                $inst= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                if (!empty($TYPE_INSTITUTION_ID))
                {
                 $inst_sect='SELECT INSTITUTION_ID,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION` FROM `inst_institutions` WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_inst.' ORDER BY DESCRIPTION_INSTITUTION ASC ';
	           

                 $inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
                 foreach ($inst_sect_req as $key)
                 {

                   if (!empty($INSTITUTION_ID)){ 
                     if ($INSTITUTION_ID==$key->INSTITUTION_ID) {
                      $inst.= "<option value ='".$key->INSTITUTION_ID."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                    }
                    else	{
                     $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                   }
                 }
                 else
                 {
                   $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                 }
               }
             }
             $soustutel= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
             if ($INSTITUTION_ID != ''){
              {

               $inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE INSTITUTION_ID='".$INSTITUTION_ID."'  ");
               $inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');

               $soustutel_sect="SELECT `CODE_SOUS_TUTEL`,`DESCRIPTION_SOUS_TUTEL` FROM `inst_institutions_sous_tutel` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY DESCRIPTION_SOUS_TUTEL ASC ";

               $soustutel_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$soustutel_sect.'")');
             }
             foreach ($soustutel_sect_req as $key)
             {
              if (!empty($SOUS_TUTEL_ID))	{  
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
        if ($INSTITUTION_ID != '')
        {
          $inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE INSTITUTION_ID='".$INSTITUTION_ID."'  ");
          $inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');
          $program_sect="SELECT PROGRAMME_ID, `CODE_PROGRAMME`,`INTITULE_PROGRAMME` FROM `inst_institutions_programmes` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY INTITULE_PROGRAMME ASC";
          $program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
          foreach ($program_sect_req as $key)
          {
            if (!empty($PROGRAMME_ID))
            {  
             if ($PROGRAMME_ID==$key->PROGRAMME_ID) 
             {
              $program.= "<option value ='".$key->PROGRAMME_ID."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
            }
            else	{
              $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
            }
          }
          else
          {
           $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
         }
       }
     }
               $actions= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
                  if ($PROGRAMME_ID != '')
                        {
                   $actions_sect='SELECT DISTINCT inst_institutions_actions.ACTION_ID AS CODE_ACTION,inst_institutions_actions.LIBELLE_ACTION FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID where 1 AND inst_institutions_actions.PROGRAMME_ID='.$PROGRAMME_ID.'  ORDER BY inst_institutions_actions.ACTION_ID ASC';
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
echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1,'inst'=>$inst,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires,'ligne_activite'=>$ligne_activite));
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
}
?>