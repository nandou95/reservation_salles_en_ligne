<?php
/*
* @author NIYONGABO Emery
* emery@mediabox.bi
* Tableau de bord «dashbord general»
* le 12/09/2023
*/
/*
* @author charbel@mediabox.bi (76887837)
* Ajout de liste et exportation
* le 26/02/2024 au 19 
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
class Dashbord_General_Ptba extends BaseController
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
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
	
	//fonction index
	public function index($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_BUDGET')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$user_id="";
		$requete_cat="SELECT  DISTINCT TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Administrations personnalisées','Ministères') as Name FROM `inst_institutions` WHERE 1  "; 
		$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_cat.'")');
		$inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';
		$data['TYPE_INSTITUTION_ID']=$this->request->getPost('');
		$data['inst_connexion']=$inst_connexion;
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


		//L'id de l'année budgétaire actuelle
		$data['ann_actuel_id'] = $this->get_annee_budgetaire();

       //Selection de l'année budgétaire
		$get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1  ORDER BY ANNEE_DEBUT ASC"; 

		$data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
		

		return view('App\Modules\dashboard\Views\Dashbord_General_Ptba_View',$data);
	}

	# fonction pour la liste
	public function listing_budget() 
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
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
			$cond.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
		}
		if(! empty($SOUS_TUTEL_ID))
		{
			$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
		}
		if(! empty($PROGRAMME_ID))
		{
			if ($TYPE_INSTITUTION_ID==2) {

				$cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
			}
		}
		if(! empty($ACTION_ID))
		{
			$cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID; 
		}
		if($LIGNE_BUDGETAIRE !='')
		{
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
		}
		
		// if(!empty($ANNEE_BUDGETAIRE_ID))
		// {
		// 	$cond.=' AND ptba_tache.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		// }
		if(!empty($PAP_ACTIVITE_ID))
		{
			$cond.=' AND ptba_tache.PAP_ACTIVITE_ID='.$PAP_ACTIVITE_ID;
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
		$query_principal="SELECT ptba_tache.INSTITUTION_ID,`BUDGET_T1`,`BUDGET_T2`,`BUDGET_T3`,`BUDGET_T4`,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.`INTITULE_PROGRAMME`,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,ptba_tache.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 ".$cond." ";

		// print_r($query_principal);die();
		$limit='LIMIT 0,10';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column=array(1,'inst_institutions.DESCRIPTION_INSTITUTION','inst_institutions_programmes.`INTITULE_PROGRAMME`','inst_institutions_actions.LIBELLE_ACTION','DESC_PAP_ACTIVITE',1,'BUDGET_T1','BUDGET_T2','BUDGET_T3','BUDGET_T4');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.DESCRIPTION_INSTITUTION ASC';
		$search = !empty($_POST['search']['value']) ? ("AND (
			inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.`INTITULE_PROGRAMME` LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%' )") : '';
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
			$PROGRAMME = (mb_strlen($row->INTITULE_PROGRAMME) > 12) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;
			$ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 8) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;
			$ACTION = (mb_strlen($retVal) > 12) ? (mb_substr($retVal, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>') : $retVal;
			$engagement[]=$INSTITUTION;
			$engagement[]=$PROGRAMME;
			$engagement[]=$ACTION;
			$engagement[]=$ACTIVITES;
			$engagement[]=$row->DESCRIPTION_GRANDE_MASSE;
			$engagement[]=number_format(floatval($row->BUDGET_T1),0,',',' ');
			$engagement[]=number_format(floatval($row->BUDGET_T2),0,',',' ');
			$engagement[]=number_format(floatval($row->BUDGET_T3),0,',',' ');
			$engagement[]=number_format(floatval($row->BUDGET_T4),0,',',' ');
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
			$cond1='AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}

		if($INSTITUTION_ID!=0)
		{
			$cond2="AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
		}
		

		if($PROGRAMME_ID!=0)
		{
			if ($TYPE_INSTITUTION_ID==2) 
			{
				$cond4="AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
			}
		}

		if($ACTION_ID!=0)
		{
			$cond5=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'"; 
		}
		if($LIGNE_BUDGETAIRE !=0)
		{
		$cond6=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
		}
    
    if($PAP_ACTIVITE_ID !=0)
		{
		$cond8=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
		}
		$cond=$cond1.' '.$cond2.' '.$cond3.' '.$cond4.' '.$cond5.' '.$cond6.' '.$cond7.' '.$cond8;

		$getRequete="SELECT ptba_tache.INSTITUTION_ID,`BUDGET_T1`,`BUDGET_T2`,`BUDGET_T3`,`BUDGET_T4`,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.`INTITULE_PROGRAMME`,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,ptba_tache.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID = ptba_tache.GRANDE_MASSE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 ".$cond." ";
		
		$getData = $this->ModelPs->datatable('CALL getTable("' . $getRequete . '")');
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'INSTITUTION');
		$sheet->setCellValue('B1', 'PROGRAMME');
		$sheet->setCellValue('C1', 'ACTION');
		$sheet->setCellValue('D1', 'ACTIVITE');
		$sheet->setCellValue('E1', 'GRANDE MASSE');
		$sheet->setCellValue('F1', 'TRIMESTRE 1');
		$sheet->setCellValue('G1', 'TRIMESTRE 2');
		$sheet->setCellValue('H1', 'TRIMESTRE 3');
		$sheet->setCellValue('I1', 'TRIMESTRE 4');
		$rows = 3;
		//boucle pour les institutions
		foreach ($getData as $key)
		{
			$sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
			$sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
			$sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
			$sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
			$sheet->setCellValue('E' . $rows, $key->DESCRIPTION_GRANDE_MASSE);
			$sheet->setCellValue('F' . $rows, $key->BUDGET_T1);
			$sheet->setCellValue('G' . $rows, $key->BUDGET_T2);
			$sheet->setCellValue('H' . $rows, $key->BUDGET_T3);
			$sheet->setCellValue('I' . $rows, $key->BUDGET_T4);
			$rows++;
		}
		$writer = new Xlsx($spreadsheet);
		$writer->save('world.xlsx');
		return $this->response->download('world.xlsx', null)->setFileName('budget execute par grande masse.xlsx');
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
		$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
		$inst_conn=$this->request->getVar('inst_conn');
		$PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');

		$user_id =$inst_conn;
		$inst_connect ='';
		$prof_connect ='';
		$type_connect ='';
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$cond_affectations="";
		$cond_affectations1="";
		if ($IS_PRIVATE==1){
			$totaux='SUM(BUDGET_T1)';
			$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
		}else if ($IS_PRIVATE==2){
			$totaux='SUM(BUDGET_T1)';
			$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
		}else if ($IS_PRIVATE==3){
			$totaux='SUM(BUDGET_T3)';
			$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
		}else if ($IS_PRIVATE==4){
			$totaux='SUM(BUDGET_T4)';
			$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
		}else{
			$totaux='SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
			$cond_trim=" " ;
		}
		$cond1='';
		$cond='';
		$cond2='';
		$KEY2=1;
		$cond_program='';
		$titr_deux=''.lang("messages_lang.par_type_institution").'';
		$titr_deux2=''.lang("messages_lang.par_type_institution").'';
		$id_decl= 'TYPE_INSTITUTION_ID'; 
		$name_decl= "if(TYPE_INSTITUTION_ID=1,'Administrations personnalisées','Ministères')";
		$format=" {point.y:.3f} %";
		$type="column";
		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
		if(! empty($TYPE_INSTITUTION_ID))
		{
			$titr_deux=''.lang("messages_lang.par_type_institution").'';
			$titr_deux2=''.lang("messages_lang.par_type_institution").'';
			$id_decl= 'inst_institutions.INSTITUTION_ID'; 
			$name_decl= "DESCRIPTION_INSTITUTION";

			$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
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
			$name_table= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_sous_tutel ON inst_institutions_sous_tutel.CODE_SOUS_TUTEL=SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)";
			$format=" {point.y:.2f} %";
			$type="column";
			$titr_deux=''.lang("messages_lang.par_service").'';
			$titr_deux2=''.lang("messages_lang.par_service").'';
			$KEY2=5;
			$cond_sy=("SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID='".$INSTITUTION_ID."' ");
			$cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
			if (! empty($cond_sy_req['INSTITUTION_ID'])) {
				$cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
				$cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
				$cond2= ' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
			}
			$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
		}
		if(! empty($SOUS_TUTEL_ID))
		{
			$id_decl= 'program.PROGRAMME_ID'; 
			$name_decl= "INTITULE_PROGRAMME";
			$name_table= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes program ON program.PROGRAMME_ID=ptba_tache.PROGRAMME_ID";

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
			$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID";
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
  $name_table= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID =inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";
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
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID";
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
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
  $cond333.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
  $type="column";
  $titr_deux=''.lang("messages_lang.par_activite").'';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;
   }
		
		
	 $budget=("SELECT ".$name_decl." AS Name,".$id_decl." AS ID,".$totaux." AS enga FROM ptba_tache ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1." GROUP BY ".$name_decl.",".$id_decl." ORDER BY ".$id_decl." ASC ");
		$activites_exec=("SELECT ".$name_decl." AS Name,".$id_decl." AS ID,count(PTBA_TACHE_ID) AS enga FROM ptba_tache ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1." GROUP BY ".$name_decl.",".$id_decl." ORDER BY ".$id_decl." ASC ");
		$budget_req=$this->ModelPs->getRequete(' CALL getTable("'.$budget.'")');
		$activite_req=$this->ModelPs->getRequete(' CALL getTable("'.$activites_exec.'")');
		$data_budget_req='';
		$data_total=0;
		foreach ($budget_req as $value)
		{
			$pourcent=0;
			$taux=("SELECT ".$totaux." AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_affectations1." ");
			$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
			if ($taux1['taux']>0) {
				$pourcent=($value->enga/$taux1['taux'])*100;
			}
			$data_budget_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ')." BIF)', y:".$pourcent.",color:'#000080',key:".$value->ID.",key2:".$KEY2."},";
			$data_total=$data_total+$value->enga;
		} 
###########
		$data_activite_req='';
		$data_activite_total=0;
		foreach ($activite_req as $value)
		{
			$pourcent=0;
			$taux=("SELECT count(PTBA_TACHE_ID) AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ");

			$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
			if ($taux1['taux']>0) {
				$pourcent=($value->enga/$taux1['taux'])*100;
			}
			$data_activite_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ').")', y:".$value->enga.",color:'#2E8B57',key:".$value->ID.",key2:".$KEY2."},";
			$data_activite_total=$data_activite_total+$value->enga;
		} 

		///monta par trimestre////

		$data_trimestre1='';
		$data_trimestre2='';
		$data_trimestre3='';
		$data_trimestre4='';
		$trimestre1=("SELECT SUM(BUDGET_T1) AS trm1 FROM `ptba_tache` ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1." ");
		$trimestre2=("SELECT SUM(BUDGET_T2) AS trm2 FROM `ptba_tache` ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1." ");
		$trimestre3=("SELECT SUM(BUDGET_T3) AS trm3 FROM `ptba_tache` ".$name_table."  WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1."  ");
		$trimestre4=("SELECT SUM(BUDGET_T4) AS trm4 FROM `ptba_tache` ".$name_table." WHERE 1 ".$cond." ".$cond2." ".$cond_affectations1."");
		$trimestre1_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre1.'")');
		$trimestre2_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre2.'")');
		$trimestre3_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre3.'")');
		$trimestre4_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$trimestre4.'")');
		$pourcentT1=0;
		$pourcentT2=0;
		$pourcentT3=0;
		$pourcentT4=0;
		$tauxt=("SELECT ".$totaux." AS taux FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ");
		$tauxt1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$tauxt.'")');
		$trimes1= ($trimestre1_req['trm1']>0) ? $trimestre1_req['trm1'] : 0 ;
		$trimes2= ($trimestre2_req['trm2']>0) ? $trimestre2_req['trm2'] : 0 ;
		$trimes3= ($trimestre3_req['trm3']>0) ? $trimestre3_req['trm3'] : 0 ;
		$trimes4= ($trimestre4_req['trm4']>0) ? $trimestre4_req['trm4'] : 0 ;
		$psex=0;$psex2=0;$psex3=0;$psex4=0;
		if ($tauxt1['taux']>0){
			$pourcentT1=($trimes1/$tauxt1['taux'])*100;
			$psex=number_format($pourcentT1,3,',',' ');
			$pourcentT2=($trimes2/$tauxt1['taux'])*100;
			$psex2=number_format($pourcentT2,3,',',' ');
			$pourcentT3=($trimes3/$tauxt1['taux'])*100;
			$psex3=number_format($pourcentT3,3,',',' ');
			$pourcentT4=($trimes4/$tauxt1['taux'])*100;
			$psex4=number_format($pourcentT4,3,',',' ');
		}
		$data_trimestre1.="{name:'".lang("messages_lang.trimestre1")." (".$psex." %)', y:".$trimes1.",key:1,key2:100},";
		$data_trimestre2.="{name:'".lang("messages_lang.trimestre2")." (".$psex2." %)', y:".$trimes2.",key:2,key2:200},";
		$data_trimestre3.="{name:'".lang("messages_lang.trimestre3")." (".$psex3." %)', y:".$trimes3.",key:3,key2:300},";
		$data_trimestre4.="{name:'".lang("messages_lang.trimestre4")." (".$psex4." %)', y:".$trimes4.",key:4,key2:400},";
		//print_r($data_budget_req);die();
		$rapp="<script type=\"text/javascript\">
		Highcharts.chart('container', {
			chart: {
				type: 'column'
				},
				title: {
					text: '".lang("messages_lang.budget_vote")." <br> ".number_format($data_total,0,',',' ')." BIF',
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
															text: '".lang("messages_lang.nbre_activite")." : ".number_format($data_activite_total,0,',',' ')." ',
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
																						b0,
																						depth: 40,
																						cursor:'pointer',
																						point:{
																						events: {
																						click: function(){
																						if(this.key2==2){
																					$(\"#idpro\").html(\" Actions \");
																			$(\"#idcod\").html(\" Objctif&nbspde&nbspl\'action \");
																				$(\"#idobj\").html(\"Programme\");
																				$(\"#titre\").html(\"Détails activités\");
																				}else if(this.key2==3){
																		$(\"#idpro\").html(\" activités\");
																$(\"#idcod\").html(\" Actions\");
																$(\"#idobj\").html(\" Programme \");
																$(\"#titre\").html(\"Détails activités\");
																}else if(this.key2==5){
																$(\"#idpro\").html(\" Activités\");
																$(\"#idcod\").html(\" Actions\");
																																					$
															}else if(this.key2==6){
															$(\"#idpro\").html(\" Activités\");
															$(\"#idcod\").html(\" Actions\");
															$(\"#idobj\").html(\" Programme \");
																}else if(this.key2==1){
																$(\"#idpro\").html(\" Activités\");
																$(\"#idcod\").html(\" Actions\");
																$(\"#idobj\").html(\" Programme \");
																}else{
																$(\"#idpro\").html(\" Programmes  \");
																$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
																$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
																	}
																$(\"#titre\").html(\"".lang("messages_lang.detail_activite")."\");
																$(\"#myModal\").modal('show');
																var row_count ='1000000';
																$(\"#mytable\").DataTable({
																\"processing\":true,
																\"serverSide\":true,
																\"bDestroy\": true,
																\"ajax\":{
															url:\"".base_url('dashboard/Dashbord_General_Ptba/detail_general_activite_vote')."\",
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
												format: '{point.y:,f}'
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
									name:'activités',
									data: [".$data_activite_req."]
									}
									]
									});
								</script>
									";

###### rapport des budgets vote par grande masse
  $grande_masse="SELECT IF(inst_grande_masse.GRANDE_MASSE_ID in(6,7,8),7,inst_grande_masse.GRANDE_MASSE_ID) as ID, inst_grande_masse.DESCRIPTION_GRANDE_MASSE as NAME,".$totaux." as MONTANT FROM ptba_tache JOIN inst_grande_masse ON ptba_tache.GRANDE_MASSE_ID=inst_grande_masse.GRANDE_MASSE_ID LEFT JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_affectations1." GROUP BY ID,DESCRIPTION_GRANDE_MASSE ORDER BY MONTANT DESC";

	 $nbre_grande_masse=$this->ModelPs->getRequete(' CALL getTable("'.$grande_masse.'")');
	 $donnees="";
	 $total=0;
	foreach ($nbre_grande_masse  as $key) 
	{ 
	$total+=$key->MONTANT;
	$MONTANT=$key->MONTANT >0  ? $key->MONTANT : '0';
	$pourcent=0;
	$taux=("SELECT ".$totaux." AS taux FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ");
	$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
	if ($taux1['taux']>0) {
	$pourcent=($MONTANT/$taux1['taux'])*100;
	}
	$name = (!empty($key->NAME)) ? $key->NAME : "Autres" ;
	$donnees.="{name:'".trim(str_replace("'", "\'", $name))." (".number_format($MONTANT,0,',',' ')." BIF)',y:".$pourcent.",key:".$key->ID."},";
	}

// print_r($donnees);die();
$rapp_gde_masse="<script type=\"text/javascript\">
Highcharts.chart('container_gde_masse', { 
chart: {
type: 'bar'
},
title: {
text: '<b> Budget voté par grande masse</b>'
},
subtitle: {
text: '<b>".number_format($total,0,',',' ')." BIF</b>'
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
$(\"#titre_masse\").html(\"Détails activités\");
var row_count ='1000000';
$(\"#mytable_masse\").DataTable({
\"processing\":true,
\"serverSide\":true,
\"bDestroy\": true,

\"ajax\":{
url:\"".base_url('dashboard/Dashbord_General_Ptba/detail_ptba_Gdemasse')."\",
type:\"POST\",
data:{
key:this.key,
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
dataLabels:{
enabled: true,
format: '{point.y:,.3f} %'
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
			name:'Grande masse',
			data: [".$donnees."],
			marker: {
				lineWidth: 2,
				lineColor: Highcharts.getOptions().colors[3],
				fillColor: 'white'
			}
		}
		]
		})
		</script>
		";



#######rapport des budget transfert par activite

		$inst= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
		if (!empty($TYPE_INSTITUTION_ID))
		{
			$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION,inst_institutions.INSTITUTION_ID FROM inst_institutions JOIN ptba_tache  ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_affectations1.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

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
		$soustutel= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
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



		$program= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
		if (!empty($PROGRAMME_ID))
		{
			$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC ';

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


		$program= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
		if ($SOUS_TUTEL_ID != '')
		{
			$program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.PROGRAMME_ID=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."' ".$cond_program."  ORDER BY inst_institutions_programmes.CODE_PROGRAMME ASC";

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
		$actions= '<option selected="" disabled="">'.lang('messages_lang.selection_message').'</option>';
		if ($PROGRAMME_ID != '')
		{
			$actions_sect='SELECT `ACTION_ID`, `PROGRAMME_ID`, `CODE_ACTION`, `LIBELLE_ACTION`FROM `inst_institutions_actions` WHERE PROGRAMME_ID='.$PROGRAMME_ID.' ORDER BY LIBELLE_ACTION ASC';
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


		echo json_encode(array('rapp'=>$rapp,'rapp_activ'=>$rapp_activ,'rapp_gde_masse'=>$rapp_gde_masse,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires,'ligne_activite'=>$ligne_activite));
	}


###detail du rapport des projets vs montant par axe stratégique 
	function detail_comparaisons_vote() 
	{
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
		$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');

		$cond='';
		$cond11='';

		if(! empty($TYPE_INSTITUTION_ID))
		{
		$KEY2=1;
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
		$KEY2=2;
		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
		}
		if(! empty($INSTITUTION_ID))
		{
		$KEY2=5;
		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
		$cond11.=" AND inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'";
		}
		if(! empty($PROGRAMME_ID))
		{
		if ($TYPE_INSTITUTION_ID==2){
		$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
		$KEY2=3;
			}else{
		$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
		$KEY2=4;
			}
		}
		if(! empty($ACTION_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";  
		}

		if(! empty($LIGNE_BUDGETAIRE))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";  
		}

		if(! empty($PAP_ACTIVITE_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";  
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
		$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS `INTITULE_MINISTERE`,inst_institutions_programmes.`INTITULE_PROGRAMME`,inst_institutions_actions.`LIBELLE_ACTION`, `ACTIVITES`,`RESULTATS_ATTENDUS`, date_format(det.DATE_ENGAGEMENT_BUDGETAIRE,'%d-%m-%Y') as dat,  REPLACE(RTRIM(ptba_tache.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM ptba_tache LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN execution_budgetaire_raccrochage_activite_new ON execution_budgetaire_raccrochage_activite_new.PTBA_ID=ptba_tache.PTBA_ID LEFT JOIN execution_budgetaire_raccrochage_activite_detail det ON det.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=execution_budgetaire_raccrochage_activite_new.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID WHERE 1 ".$cond." ";


		$limit='LIMIT 0,10';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';

		$order_column=array(1,1,1,1,1,1,1,1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba_tache.INSTITUTION_ID ASC';
		$search = !empty($_POST['search']['value']) ? ("AND (
			inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%')") : '';

		$search = str_replace("'","\'",$search);

		$critere=" AND inst_institutions.TYPE_INSTITUTION_ID='".$KEY."'";
		if ($KEY2==1)
		{
			$critere=" AND INSTITUTION_ID='".$KEY."'";
		}
		if($KEY2==2)
		{
			$critere=" AND PROGRAMME_ID='".$KEY."'";
		}
		if ($KEY2==3)
		{
			$critere=" AND ACTION_ID='".$KEY."'";
		}
		if ($KEY2==4)
		{
			$critere=" AND PTBA_ID='".$KEY."'";
		}
		if($KEY2==5)
		{
			$critere=" AND PROGRAMME_ID='".$KEY."'";
		}
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
		$query_filter=$query_principal.' '.$critere.'  '.$search;
		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row)
		{
			$u++;
			$sub_array=array();
			if (strlen($row->ACTIVITES) > 8){ 
				$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
				$sub_array[] = mb_substr($row->ACTIVITES, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->ACTIVITES.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->RESULTATS_ATTENDUS, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTATS_ATTENDUS.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->retVal, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
			} else{

				$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
			}
			$sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->name,0,',',' ').'</label></font> </center>';

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

	function detail_comparaisons_execution()
	{
		$data=$this->urichk();
		$db = db_connect(); 
		$session  = \Config\Services::session();
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
		$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');

		$cond1='';
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

			$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
			$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');

			$nombre=count($user_connect_req);
			if ($nombre>1) {
				$cond1.=" ";

			}else{
				$cond1.='';  
			}
		}
		else{
			return redirect('Login_Ptba');
		}

		$cond='';
		if(! empty($TYPE_INSTITUTION_ID))
		{
			$KEY2=1;
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}

		if(! empty($INSTITUTION_ID))
		{
			$KEY2=2;

			$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";

		}


		if(! empty($PROGRAMME_ID))
		{
			if ($TYPE_INSTITUTION_ID==2) {

				$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
				$KEY2=3;
			}else{
				$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
				$KEY2=3;
			}
		}
		if(! empty($ACTION_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";  
		}

		if(! empty($LIGNE_BUDGETAIRE))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";  
		}

		if(! empty($PAP_ACTIVITE_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";  
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

		if ($KEY2==2) 
		{
			$query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.DESCRIPTION_INSTITUTION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministère') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba_tache.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID  WHERE 1 ".$cond." ".$cond1."  ";
		}else if ($KEY2==3) {
			$query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.DESCRIPTION_INSTITUTION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba_tache.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ".$cond1." ";
		}else if ($KEY2==4) {
			$query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions.DESCRIPTION_INSTITUTION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,ptba_activite.DESCRIPTION_ACTIVITE,inst_institutions_actions.LIBELLE_ACTION,proc_exec_budgetaire_phase_administrative_detail.PROGRAMME_ID,MONTANT_ENGAGE FROM `proc_exec_budgetaire_phase_administrative_detail` LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=proc_exec_budgetaire_phase_administrative_detail.PROGRAMME_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_exec_budgetaire_phase_administrative_detail.INSTITUTION_ID LEFT JOIN inst_institutions_actions ON `inst_institutions_actions`.`ACTION_ID`=proc_exec_budgetaire_phase_administrative_detail.ACTION_ID LEFT JOIN ptba_activite ON `ptba_activite`.`ACTIVITE_ID`=proc_exec_budgetaire_phase_administrative_detail.ACTIVITE_ID  WHERE 1 ".$cond." ".$cond1." ";
		}else if ($KEY2==1) {
			$query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.DESCRIPTION_INSTITUTION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba_tache.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ".$cond1." ";
		}else{
			$query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.DESCRIPTION_INSTITUTION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba_tache.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ".$cond1." ";
		}

		$limit='LIMIT 0,10';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';

		$order_column=array(1,1,1,1,1,1,1,1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.INSTITUTION_ID ASC';


		if ($KEY2==1) {
			$add_search=" OR inst_institutions_programmes.CODE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_programmes.OBJECTIF_DU_PROGRAMME LIKE '%$var_search%'";
		}
		if ($KEY2==2){
			$add_search=" OR inst_institutions_actions.OBJECTIF_ACTION LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
		}
		if ($KEY2==3) { 
			$add_search=" OR ptba_activite.DESCRIPTION_ACTIVITE LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
		}if ($KEY2==4) {
			$add_search=" OR ptba_activite.DESCRIPTION_ACTIVITE LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
		}


		$search = !empty($_POST['search']['value']) ? ("AND (
			inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') LIKE '%$var_search%' ".$add_search." OR MONTANT_ENGAGE LIKE '%$var_search%')") : '';
		$critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
		if($KEY2==1)
		{
			$critere=" AND ptba.INSTITUTION_ID='".$KEY."'";
		}
		if ($KEY2==2)
		{
			$cond.=" AND ptba.PROGRAMME_ID='".$KEY."'";
		}
		if ($KEY2==3) 
		{
			$critere=" AND PTBA_ID='".$KEY."'";
		}
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
		$query_filter=$query_principal.' '.$critere.'  '.$search;

		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row) 
		{
			$u++;
			$engagement=array();
			if ($KEY2==0) {
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->OBJECTIF_DU_PROGRAMME).'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->CODE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
			}
			if ($KEY2==1){
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->OBJECTIF_DU_PROGRAMME).'</label></font> </center>';

				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->CODE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
			}else if($KEY2==2){
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->OBJECTIF_ACTION.'</label></font> </center>';

				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
			}else if($KEY2==3){
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_ACTIVITE.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';

				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
			}
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

	function detail_generals_phase()
	{
		$data=$this->urichk();
		$db = db_connect(); 
		$session  = \Config\Services::session();
		$KEY=$this->request->getPost('key');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
    $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
		$cond1='';
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
			$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
			$nombre=count($user_connect_req);
			if ($nombre>1) {
				$cond1.=" ";
			}else{
				$cond1.='';  
			}
		}
		else{
			return redirect('Login_Ptba');
		}
		$cond="";
		if(! empty($TYPE_INSTITUTION_ID))
		{
			$KEY2=1;
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
			$KEY2=2;
			$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
		}
		if(! empty($PROGRAMME_ID))
		{
			if ($TYPE_INSTITUTION_ID==2) {

				$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
				$KEY2=3;
			}else{
				$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
				$KEY2=3;
			}
		}
		if(! empty($ACTION_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.CODE_ACTION='".$ACTION_ID."'";  
		}

		if(! empty($LIGNE_BUDGETAIRE))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";  
		}

		if(! empty($PAP_ACTIVITE_ID))
		{
			$KEY2=4;
			$cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";  
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

		$query_principal="SELECT detail.MONTANT_LIQUIDATION,detail.MONTANT_TITRE_DECAISSEMENT,detail.MONTANT_JURIDIQUE,detail.MONTANT_ORDONNANCE,detail.MONTANT_ENGAGE,inst_institutions_programmes.INTITULE_PROGRAMME,ptba.RESULTATS_ATTENDUS,ptba.LIBELLE_ACTION,ptba.ACTIVITES,`MONTANT_FACTURE`,`DESCRIPTION_INSTITUTION`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution FROM proc_demande_exec_budgetaire as admini LEFT JOIN proc_demande_exec_budgetaire_details as detail ON detail.EXEC_BUDG_PHASE_ADMIN_ID=admini.DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID LEFT JOIN ptba ON ptba.PTBA_ID=admini.PTBA_ID LEFT JOIN  inst_institutions ON ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID LEFT JOIN  proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=admini.MOUVEMENT_DEPENSE_ID WHERE 1  ".$cond." ".$cond1." ";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}
		$order_by='';
		if($_POST['order']['0']['column']!=0)
		{
			$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
		}
		$search = !empty($_POST['search']['value']) ? ("AND (
			`DESCRIPTION_INSTITUTION` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';
		$search = str_replace("'","\'",$search);

		$critere=' ';
		if ($KEY==1) {
			$critere=' AND detail.MONTANT_ENGAGE > 0';
		}else if ($KEY==2) {
			$critere=' AND detail.MONTANT_LIQUIDATION > 0';
		}else if ($KEY==3) {
			$critere=' AND detail.MONTANT_ORDONNANCE > 0';
		}else if ($KEY==5) {
			$critere=' AND detail.MONTANT_JURIDIQUE > 0';
		}else{
			$critere=' AND detail.MONTANT_TITRE_DECAISSEMENT > 0';
		}
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
		$query_filter=$query_principal.' '.$critere.'  '.$search;
		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row) 
		{
			$u++;
			$ptba=array();
			$montant='';
			if ($KEY==1) {
				$montant=number_format($row->MONTANT_ENGAGE,0,',',' ');
			}else if ($KEY==2) {
				$montant=number_format($row->MONTANT_LIQUIDATION,0,',',' ');
			}else if ($KEY==3) {
				$montant=number_format($row->MONTANT_ORDONNANCE,0,',',' ');
			}else if ($KEY==5) {
				$montant=number_format($row->MONTANT_JURIDIQUE,0,',',' ');
			}else{
				$montant=number_format($row->MONTANT_TITRE_DECAISSEMENT,0,',',' ');
			}
			$ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
			$ptba[] ='<center><font color="#000000" size=2><label>'.$montant.'</label></font> </center>';
			$data[] = $ptba;        
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

	function detail_generals_vote() 
	{
		$data=$this->urichk();
		$db = db_connect(); 
		$session  = \Config\Services::session();
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
		$cond1='';
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
			$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
			$nombre=count($user_connect_req);
			if ($nombre>1) {
				$cond1.=" ";
			}else{
				$cond1.='';  
			}
		}
		else{
			return redirect('Login_Ptba');
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
			
			$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";

		}

		if(! empty($ACTION_ID))
		{
			
			$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'"; 
		}

		if(! empty($LIGNE_BUDGETAIRE))
		{
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";  
		}

		
		$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.`INTITULE_PROGRAMME`,inst_institutions_actions.LIBELLE_ACTION, ptba_tache.Q_TOTAL,ptba_tache.UNITE,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE, date_format(execution_budgetaire_execution_tache.DATE_DEMANDE,'%d-%m-%Y') as dat, ptba_tache.BUDGET_ANNUEL AS name FROM ptba_tache LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire ON execution_budgetaire.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID   WHERE 1  ".$cond." ".$cond1." ";
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
		$limit='LIMIT 0,10';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column=array(1,'inst_institutions.DESCRIPTION_INSTITUTION ','DESC_PAP_ACTIVITE','ptba_tache.Q_TOTAL','RESULTAT_PAP_ACTIVITE','inst_institutions_actions.LIBELLE_ACTION','inst_institutions_programmes.INTITULE_PROGRAMME',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY PTBA_TACHE_ID  ASC';

		$search = !empty($_POST['search']['value']) ? ("AND ( OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%')") : '';
		$critere=" ";
		if ($KEY2==6){
			$critere=" AND ptba_tache.INSTITUTION_ID=".$KEY."";
		}else if ($KEY2==5){
			$critere=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";
		}else if ($KEY2==3){
			$critere=" AND ptba_tache.ACTION_ID='".$KEY."'";
		}else if ($KEY2==4){
			$critere=" AND ptba_tache.PTBA_TACHE_ID='".$KEY."'";
		}else if ($KEY2==2){
		  $critere=" AND ptba_tache.PROGRAMME_ID='".$KEY."'";
		} else if ($KEY2==100 OR $KEY2==200 OR $KEY2==300 OR $KEY2==400 ){
			$critere=" ";
		}else{
		 $critere=" AND TYPE_INSTITUTION_ID='".$KEY."'";	
		}
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'  '.$limit;

		$query_filter=$query_principal.' '.$critere.'  '.$search;
		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row) 
		{
			$u++;
			$sub_array=array();
			if ($KEY2==100) {
				$mona_de=number_format(floatval($row->BUDGET_T1),0,',',' ');
			}else if ($KEY2==200) {
				$mona_de=number_format(floatval($row->BUDGET_T2),0,',',' ');
			}else if ($KEY2==300) {
				$mona_de=number_format(floatval($row->BUDGET_T3),0,',',' ');
			}else if ($KEY2==400) {
				$mona_de=number_format(floatval($row->BUDGET_T4),0,',',' ');
			}else{
				$mona_de=number_format(floatval($row->name),0,',',' ');	
			}
			$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
			if (strlen($row->DESC_PAP_ACTIVITE) > 12){ 
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->DESC_PAP_ACTIVITE, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->Q_TOTAL.'</label></font> </center>';
				$sub_array[] = mb_substr($row->RESULTAT_PAP_ACTIVITE, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($retVal, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
			}else{
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_PAP_ACTIVITE.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->Q_TOTAL.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->RESULTAT_PAP_ACTIVITE.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				$sub_array[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
			}
			$data[]=$sub_array;        
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
#########
	function detail_generals_activite_vote() 
	{
		$data=$this->urichk();
		$db = db_connect(); 
		$session  = \Config\Services::session();
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		
		$cond1='';
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

			$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
			$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');

			$nombre=count($user_connect_req);
			if ($nombre>1) {
				$cond1.=" ";

			}else{
				$cond1.='';  
			}
		}
		else{
			return redirect('Login_Ptba');
		}
		$cond='';
		if(! empty($TYPE_INSTITUTION_ID))
		{
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
			$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
		}
		if(! empty($PROGRAMME_ID))
		{
			$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
		}
		if(! empty($ACTION_ID))
		{
			$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
		}
		// if(! empty($ANNEE_BUDGETAIRE_ID))
		// {
		// 	$cond.=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";  
		// }
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
		$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.UNITE,`ACTIVITES`,`RESULTATS_ATTENDUS`, date_format(det.DATE_ENGAGEMENT_BUDGETAIRE,'%d-%m-%Y') as dat, REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM ptba_tache  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID LEFT JOIN execution_budgetaire_raccrochage_activite_new ON execution_budgetaire_raccrochage_activite_new.PTBA_ID=ptba.PTBA_ID  LEFT JOIN execution_budgetaire_raccrochage_activite_detail det ON det.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=execution_budgetaire_raccrochage_activite_new.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID  WHERE 1 ".$cond." ".$cond1." ";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}
		$order_by='';
		if($_POST['order']['0']['column']!=0) {

			$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
																																																											}
	$search = !empty($_POST['search']['value']) ? ("AND (ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR INTITULE_MINISTERE LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%')") : '';

	$critere=" ";
	if ($KEY2==6) {
		$critere=" AND ptba.INSTITUTION_ID='".$KEY."'";

	} else if ($KEY2==5) {
		$critere=" AND SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";

	} else if ($KEY2==3) {
		$critere=" AND ptba.ACTION_ID='".$KEY."'";


	} else if ($KEY2==4) {
		$critere=" AND ptba.PTBA_ID='".$KEY."'";

	} else if ($KEY2==2) {
		$critere=" AND ptba.PROGRAMME_ID='".$KEY."'";

	} else if ($KEY2==100 OR $KEY2==200 OR $KEY2==300 OR $KEY2==400 ) {
		$critere=" ";
	}else{
		$critere=" AND TYPE_INSTITUTION_ID='".$KEY."'";	
	}
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
		$u++;
		$sub_array=array();
		if ($KEY2==100) {
			$mona_de=number_format($row->T1,0,',',' ');
		}else if ($KEY2==200) {
			$mona_de=number_format($row->T2,0,',',' ');
		}else if ($KEY2==300) {
			$mona_de=number_format($row->T3,0,',',' ');
		}else if ($KEY2==400) {
			$mona_de=number_format($row->T4,0,',',' ');
		}else{
			$mona_de=number_format($row->name,0,',',' ');	
		}
		$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;

		if (strlen($row->ACTIVITES) > 12){ 
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
			$sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($row->ACTIVITES, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->ACTIVITES.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'</label></font> </center>';
			$sub_array[] = mb_substr($row->RESULTATS_ATTENDUS, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTATS_ATTENDUS.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($retVal, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
		}else{
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'</label> </font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
		}
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
function detail_generals_decaissement()
{
	$KEY=$this->request->getPost('key');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$cond="";
	if(! empty($TYPE_INSTITUTION_ID))
	{
		$KEY2=1;
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	{
		$KEY2=2;
		$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}
	if(! empty($PROGRAMME_ID))
	{
		if ($TYPE_INSTITUTION_ID==2) {

			$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		}else{
			$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		}
	}
	if(! empty($ACTION_ID))
	{
		$KEY2=4;
		$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
	$query_principal="SELECT   detail.MONTANT_LIQUIDATION,detail.MONTANT_TITRE_DECAISSEMENT,detail.MONTANT_JURIDIQUE,detail.MONTANT_ORDONNANCE,detail.MONTANT_ENGAGE,inst_institutions_programmes.INTITULE_PROGRAMME,ptba.RESULTATS_ATTENDUS,inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES,`MONTANT_FACTURE`,`DESCRIPTION_INSTITUTION`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution FROM proc_demande_exec_budgetaire as admini LEFT JOIN proc_demande_exec_budgetaire_details as detail ON detail.EXEC_BUDG_PHASE_ADMIN_ID=admini.DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID LEFT JOIN ptba ON ptba.PTBA_ID=admini.PTBA_ID LEFT JOIN  inst_institutions ON ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID  LEFT JOIN  proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=admini.MOUVEMENT_DEPENSE_ID WHERE 1  ".$cond." ";
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
		`DESCRIPTION_INSTITUTION` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';
	$critere=' ';
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
		$u++;
		$ptba=array();
		$ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$MONTANT_TITRE_DECAISSEMENT.'</label></font> </center>';
		$data[] = $ptba;        
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
function detail_ptba_Gdemasses()
{
	$data=$this->urichk();
	$db = db_connect(); 
	$session  = \Config\Services::session();
	$KEY=$this->request->getPost('key');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	if(!empty($TYPE_INSTITUTION_ID))
	{
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
	$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION, ptba_tache.BUDGET_ANNUEL,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE, inst_grande_masse.DESCRIPTION_GRANDE_MASSE FROM ptba_tache LEFT JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID=ptba_tache.GRANDE_MASSE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID LEFT JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 ".$cond." ".$cond11." "; 
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba_tache.INSTITUTION_ID  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? (' AND (inst_institutions.DESCRIPTION_INSTITUTION  LIKE "%$var_search%")') : '';
	$critere='';
	if ($KEY==7){
	$critere=' AND inst_grande_masse.GRANDE_MASSE_ID in (6,7,8)';
	}else{
	$critere=' AND inst_grande_masse.GRANDE_MASSE_ID ='.$KEY;
	}
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.' '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire="CALL `getTable`('".$conditions."')";
	$fetch_res= $this->ModelPs->datatable($query_secondaire);

	$data = array();		
	$u=0;
	foreach ($fetch_res as $row) {
		$u++;
		$sub_array = array();
		$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
		if (strlen($row->INTITULE_MINISTERE) > 10){ 
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
			$sub_array[] = mb_substr($row->INTITULE_MINISTERE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($row->RESULTAT_PAP_ACTIVITE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($retVal, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = mb_substr($row->DESC_PAP_ACTIVITE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
			$sub_array[] = $row->BUDGET_ANNUEL;
			$sub_array[] = mb_substr($row->DESCRIPTION_GRANDE_MASSE, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_GRANDE_MASSE.'"><i class="fa fa-eye"></i></a>';

		}else{
			$sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
			$sub_array[] = $row->INTITULE_MINISTERE;
			$sub_array[] = $row->INTITULE_PROGRAMME; 
			$sub_array[] = $row->RESULTAT_PAP_ACTIVITE;
			$sub_array[] = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
			$sub_array[] = $row->DESC_PAP_ACTIVITE;
			$sub_array[] = $row->BUDGET_ANNUEL;
			$sub_array[] = $row->DESCRIPTION_GRANDE_MASSE;
		}
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
#####detail du rappport du budget transfert
function detail_ptba_transferts()
{
	$KEY=$this->request->getPost('key');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
	$cond="";
	if(! empty($TYPE_INSTITUTION_ID))
	  {
		$KEY2=1;
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	   }
	if(! empty($INSTITUTION_ID))
	   {
		$KEY2=2;
		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
	   }
	if(! empty($PROGRAMME_ID))
	    {
		if ($TYPE_INSTITUTION_ID==2) {
			$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		   }else{
			$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		    }
	  }
	if(! empty($ACTION_ID))
	   {
		$KEY2=4;
		$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";  
	   }
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
	$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES,ptba.RESULTATS_ATTENDUS,`T1`,`T2`,`T3`,`T4`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution,proc_demande_exec_budgetaire.MONTANT_TRANSFERT FROM ptba JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID WHERE 1 ".$cond." ";
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
		`ACTIVITES` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%')") : '';
	$critere=' AND ptba.PTBA_ID ='.$KEY;
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
		$u++;
		$ptba=array();
		$ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.number_format($row->T1+$row->T2+$row->T3+$row->T4,0,',',' ').'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->MONTANT_TRANSFERT.'</label></font> </center>';
		$data[] = $ptba;        
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

########
function detail_ptba_recus()
{
	$KEY=$this->request->getPost('key');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
	$cond="";
	if(! empty($TYPE_INSTITUTION_ID))
	{
		$KEY2=1;
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	{
		$KEY2=2;
		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}
	if(! empty($PROGRAMME_ID))
	{
		if ($TYPE_INSTITUTION_ID==2)
		{
			$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		}else{
			$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
			$KEY2=3;
		}
	}
	if(! empty($ACTION_ID))
	{
		$KEY2=4;
		$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";  
	}

	if(! empty($LIGNE_BUDGETAIRE))
	   {
		$KEY2=4;
		$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";  
	    }
    if(! empty($PAP_ACTIVITE_ID))
	   {
		$KEY2=4;
		$cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";  
	    }

	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
	$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES,ptba.RESULTATS_ATTENDUS,`T1`,`T2`,`T3`,`T4`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution,proc_demande_exec_budgetaire.MONTANT_TRANSFERT FROM ptba JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_IDWHERE 1  ".$cond." ";

	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
		`ACTIVITES` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%')") : '';
	$critere=' AND ptba.PTBA_ID ='.$KEY;
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
		$u++;
		$ptba=array();
		$ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.number_format($row->T1+$row->T2+$row->T3+$row->T4,0,',',' ').'</label></font> </center>';
		$ptba[] ='<center><font color="#000000" size=2><label>'.$row->MONTANT_TRANSFERT.'</label></font> </center>';
		$data[] = $ptba;        
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
