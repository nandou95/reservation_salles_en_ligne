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
	 /* @author KWIZERA.edmond@mediabox.bi (71407706)
	 * Dashbord des evolution global
	 le 29/08/2023 au 19 
	 Amelioré par ninette@mediabox.bi
	 Le 24/10/2023
	 */
	  //Appel de l'espace de nom du Controllers
	 ini_set('max_execution_time', 2000);
	 ini_set('memory_limit','2048M');
	 class Dashboard_Comparaison_Budget extends BaseController
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
	 		$data=$this->urichk();
	 		$session  = \Config\Services::session();
	 		$user_id =0;


	 		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
	 		{
	 			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

	 			$profil_user_req=("SELECT `PROFIL_ID` FROM `user_users` WHERE USER_ID=".$user_id." AND `IS_ACTIVE`=1");
	 			$profil_user=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user_req.'")');
       //institutions auxquelles  la personne connectée est affetée 
	 			$user_affectation=("SELECT inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
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
           //Enlever la derniere virgule
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
		    $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID>=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
		    $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');

	 			return view('App\Modules\dashboard\Views\Dashboard_Comparaison_Budget_View',$data);
	 		}
	 		
  		//listing
	 		public function listing($value = 0)
	 		{

	 			$db = db_connect();
	 			$session  = \Config\Services::session();
	 			$TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
	 			$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
	 			$ACTION_ID=$this->request->getVar('ACTION_ID');
	 			$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
	 			$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
	 			$ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');
	 			$IS_DOUBLE_COMMANDE=$this->request->getVar('IS_DOUBLE_COMMANDE');
	 			$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
        $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
        $cond="";
	 			$critere1="";
	 			$critere2="";
	 			$critere3="";
	 			$critere4="";
	 			$critere5="";
	 			$critere6="";
	 			$critere7="";

	 			if(!empty($TYPE_INSTITUTION_ID))
	 			{
	 				$critere1="AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID."";
	 			}

	 			if(!empty($INSTITUTION_ID))
	 			{
	 				$critere2="AND p.INSTITUTION_ID='".$INSTITUTION_ID."'";
	 			}

	 			
	 			if(!empty($PROGRAMME_ID))
	 			{
	 				$critere3="AND p.PROGRAMME_ID='".$PROGRAMME_ID."'";
	 			}

	 			if(!empty($ACTION_ID))
	 			{
	 				$critere4="AND p.ACTION_ID='".$ACTION_ID."'";
	 			}

	 			if(!empty($IS_PRIVATE) && $IS_PRIVATE != 5)
	 			{
	 				$critere5="AND racc.TRIMESTRE_ID=".$IS_PRIVATE."";
	 			}

	 		

	 			if(!empty($IS_DOUBLE_COMMANDE))
	 			{
	 				$critere7=" AND racc.IS_DOUBLE_COMMANDE=".$IS_DOUBLE_COMMANDE."";
	 			}
	 			
	 			 if($PAP_ACTIVITE_ID !='')
        {
        $cond.=" AND p.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";
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

	 			$order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','ACTIVITES','racc.ENG_BUDGETAIRE','racc.ENG_JURIDIQUE','racc.LIQUIDATION','racc.ORDONNANCEMENT','racc.PAIEMENT','racc.DECAISSEMENT');

	 			$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst.CODE_INSTITUTION ASC';

	 			$search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%")') : '';

	 			//Les conditions
	 			$critaire = $critere1.' '.$critere2.' '.$critere3.' '.$critere4.' '.$critere5.' '.$critere6.' '.$critere7;

	 			$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;

	 			$conditionsfilter = $critaire.' '.$search.' '.$group;

	 			$requetedebase = 'SELECT racc.EXECUTION_BUDGETAIRE_ID,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,pap_activites.DESC_PAP_ACTIVITE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_actions.LIBELLE_ACTION,inst_institutions_actions.CODE_ACTION,p.DESC_TACHE,racc.TRIMESTRE_ID,inst.TYPE_INSTITUTION_ID,racc.ENG_BUDGETAIRE,racc.ENG_JURIDIQUE,racc.LIQUIDATION,racc.ORDONNANCEMENT,racc.PAIEMENT,racc.DECAISSEMENT FROM execution_budgetaire racc JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache p ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=p.PTBA_TACHE_ID JOIN inst_institutions inst ON p.INSTITUTION_ID=inst.INSTITUTION_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1';

	 			$requetedebase = str_replace("'", "\'", $requetedebase);
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

	 				$institution = (mb_strlen($row->DESCRIPTION_INSTITUTION) > 4) ? (mb_substr($row->DESCRIPTION_INSTITUTION, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>') : $row->DESCRIPTION_INSTITUTION;

	 				$programme = (mb_strlen($row->INTITULE_PROGRAMME) > 4) ? (mb_substr($row->INTITULE_PROGRAMME, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $row->INTITULE_PROGRAMME;

	 				$action = (mb_strlen($row->LIBELLE_ACTION) > 4) ? (mb_substr($row->LIBELLE_ACTION, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_ACTION;

	 				$taches = (mb_strlen($row->DESC_TACHE) > 10) ? (mb_substr($row->DESC_TACHE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

	 				$codes = (mb_strlen($row->CODE_NOMENCLATURE_BUDGETAIRE) > 30) ? (mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 30) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->CODE_NOMENCLATURE_BUDGETAIRE;

	 				$activites = (mb_strlen($row->DESC_PAP_ACTIVITE) > 10) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;
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
	 				$sub_array[] = $codes;
	 				$sub_array[] = $activites;
	 				$sub_array[] = $taches;
	 				$sub_array[] = number_format($mont_budg,2,","," ");
	 				$sub_array[] = number_format($mont_jur,2,","," ");
	 				$sub_array[] = number_format($mont_liq,2,","," ");
	 				$sub_array[] = number_format($mont_ordo,2,","," ");
	 				$sub_array[] = number_format($mont_paie,2,","," ");
	 				$sub_array[] = number_format($mont_decais,2,","," ");
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
	 		function exporter($TYPE_INSTITUTION_ID,$INSTITUTION_ID,$PROGRAMME_ID,$ACTION_ID,$IS_PRIVATE,$PAP_ACTIVITE_ID,$LIGNE_BUDGETAIRE)
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

	 			if(!empty($TYPE_INSTITUTION_ID))
	 			{
	 				$critere1=" AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID."";
	 			}

	 			if(!empty($INSTITUTION_ID))
	 			{
	 				$critere2=" AND p.INSTITUTION_ID='".$INSTITUTION_ID."'";
	 			}

	 			
	 			if(!empty($PROGRAMME_ID))
	 			{
	 				$critere3=" AND p.PROGRAMME_ID='".$PROGRAMME_ID."'";
	 			}

	 			if(!empty($ACTION_ID))
	 			{
	 				$critere4=" AND p.ACTION_ID='".$ACTION_ID."'";
	 			}

	 			if(!empty($IS_PRIVATE) && $IS_PRIVATE != 5)
	 			{
	 				$critere5.=" AND racc.TRIMESTRE_ID=".$IS_PRIVATE."";
	 			}

	 	

	 			if(!empty($IS_DOUBLE_COMMANDE))
	 			{
	 				$critere5.=" AND racc.IS_DOUBLE_COMMANDE=".$IS_DOUBLE_COMMANDE."";
	 			}

	 		if($PAP_ACTIVITE_ID !='')
          {
       $cond.=" AND p.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID."";
         }

         if(!empty($LIGNE_BUDGETAIRE))
           {
        $cond.=" AND p.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE.""; 
            }

	 			$callpsreq = "CALL getRequete(?,?,?,?);";
	 			$cond = $critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere5."";
	 			$getRequete="SELECT racc.EXECUTION_BUDGETAIRE_ID,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,pap_activites.DESC_PAP_ACTIVITE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_actions.LIBELLE_ACTION,inst_institutions_actions.CODE_ACTION,p.DESC_TACHE,racc.TRIMESTRE_ID,inst.TYPE_INSTITUTION_ID,racc.ENG_BUDGETAIRE,racc.ENG_JURIDIQUE,racc.LIQUIDATION,racc.ORDONNANCEMENT,racc.PAIEMENT,racc.DECAISSEMENT FROM execution_budgetaire racc JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache p ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=p.PTBA_TACHE_ID JOIN inst_institutions inst ON p.INSTITUTION_ID=inst.INSTITUTION_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID LEFT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1  ".$cond."  ORDER BY inst.CODE_INSTITUTION ASC";



	 			$getRequete = str_replace("'", "\'", $getRequete);

	 			$getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");


	 			$spreadsheet = new Spreadsheet();
	 			$sheet = $spreadsheet->getActiveSheet();
	 			$sheet->setCellValue('A1', 'INSTITUTION');
	 			$sheet->setCellValue('B1', 'PROGRAMME');
	 			$sheet->setCellValue('C1', 'ACTION');
	 			$sheet->setCellValue('D1', 'CODE BUDGETAIRE');
	 			$sheet->setCellValue('E1', 'ACTIVITE');
	 			$sheet->setCellValue('F1', 'TACHE');
	 			$sheet->setCellValue('G1', 'ENGAGEMENT BUDGETAIRE');
	 			$sheet->setCellValue('H1', 'ENGAGEMENT JURIDIQUE');
	 			$sheet->setCellValue('I1', 'LIQUIDATION');
	 			$sheet->setCellValue('J1', 'ORDONNANCEMENT');
	 			$sheet->setCellValue('K1', 'PAIEMENT');
	 			$sheet->setCellValue('L1', 'DECAISSEMENT');
	 			$rows = 3;
	 			foreach ($getData as $key)
	 			{
	 				$mont_budg = floatval($key->ENG_BUDGETAIRE);
	 				$mont_jur = floatval($key->ENG_JURIDIQUE);
	 				$mont_liq = floatval($key->LIQUIDATION);
	 				$mont_ordo = floatval($key->ORDONNANCEMENT);
	 				$mont_paie = floatval($key->PAIEMENT);
	 				$mont_decais = floatval($key->DECAISSEMENT);
	 				$sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
	 				$sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
	 				$sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
	 				$sheet->setCellValue('D' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
	 				$sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
	 				$sheet->setCellValue('F' . $rows, $key->DESC_TACHE);
	 				$sheet->setCellValue('G' . $rows, $mont_budg);
	 				$sheet->setCellValue('H' . $rows, $mont_jur);
	 				$sheet->setCellValue('I' . $rows, $mont_liq);
	 				$sheet->setCellValue('J' . $rows, $mont_ordo);
	 				$sheet->setCellValue('K' . $rows, $mont_paie);
	 				$sheet->setCellValue('L' . $rows, $mont_decais);
	 				$rows++;

	 			} 
	 			$writer = new Xlsx($spreadsheet);
	 			$writer->save('world.xlsx');
	 			return $this->response->download('world.xlsx', null)->setFileName('Budget Vote Vs Budget Execute.xlsx');
	 			return redirect('dashboard/Dashboard_Comparaison_Budget');
	 		}

     	# fonction pour les details
	 		function detail_Comparaison_Budget() 
	 		{
	 			$data=$this->urichk();
	 			$db = db_connect(); 
	 			$session  = \Config\Services::session();
	 			$KEY=$this->request->getPost('key');
	 			$KEY2=$this->request->getPost('key2');
	 			$KEY3=$this->request->getPost('key3');
	 			$KEY4=$this->request->getPost('key4');
	 			$KEY7=$this->request->getPost('key7');
	 			$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	 			$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	 			$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	 			$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
	 			$ACTION_ID=$this->request->getPost('ACTION_ID');
	 			$ACTIVITE=$this->request->getPost('ACTIVITE');
	 			$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
	 			$inst_conn=$this->request->getVar('inst_conn');
	 			$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
	 			// $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
	 			$IS_DOUBLE_COMMANDE=$this->request->getPost('IS_DOUBLE_COMMANDE');
	 			$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
	 			$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	 			$user_id=$inst_conn;
	 			if (empty($user_id)){
	 				redirect('Login_Ptba');
	 			}
	 			$cond1='';
	 			$cond='';
	 			$totaux='';
	 			if ($KEY3==1) {
	 				$cond_trim=" AND raccro.TRIMESTRE_ID=1" ;
	 				$totaux='COALESCE(BUDGET_T1, 0)';
	 			}else if ($KEY3==2) {

	 				$cond_trim=" AND raccro.TRIMESTRE_ID=2" ;
	 				$totaux='COALESCE(BUDGET_T2, 0)';

	 			}else if ($KEY3==3) {

	 				$cond_trim=" AND raccro.TRIMESTRE_ID=3" ;
	 				$totaux='COALESCE(BUDGET_T3, 0)';

	 			}else if ($KEY3==4){
	 				$cond_trim=" AND raccro.TRIMESTRE_ID=4" ;
	 				$totaux='COALESCE(BUDGET_T4, 0)';
	 			}else {
	 				$cond_trim=" " ;
	 				$totaux='COALESCE(BUDGET_T1, 0)+COALESCE(BUDGET_T2, 0)+COALESCE(BUDGET_T3, 0)+COALESCE(BUDGET_T4, 0)';
	 			}


	 			$cr_key='';
	 			if ($KEY2==13 OR $KEY2==14) {
	 				$cr_key=" AND inst.TYPE_INSTITUTION_ID=".$KEY."" ;
	 			}

	 			if(!empty($TYPE_INSTITUTION_ID)){
	 				$cond.=" AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID."";
	 				$id_decl= 'p.INSTITUTION_ID';

	 				$cr_key='';
	 				if ($KEY2==13 OR $KEY2==14) {
	 					$cr_key=" AND p.INSTITUTION_ID='".$KEY."'" ;
	 				} 
	 				$name_decl= "DESCRIPTION_INSTITUTION";	
	 			}
	 			if(! empty($INSTITUTION_ID))
	 			{
	 				$cond.=" AND p.INSTITUTION_ID='".$INSTITUTION_ID."'";
	 				$id_decl= 'p.PROGRAMME_ID'; 
	 				$cr_key='';
	 				if ($KEY2==13 OR $KEY2==14) {
	 					$cr_key=" AND p.PROGRAMME_ID='".$KEY."'" ;
	 				} 
	 				$name_decl= "INTITULE_PROGRAMME";
	 			}

	 			if(! empty($PROGRAMME_ID))
	 			{
	 				$cond.=" AND p.PROGRAMME_ID='".$PROGRAMME_ID."'";
	 				$id_decl= 'p.ACTION_ID'; 

	 				$cr_key='';
	 				if ($KEY2==13 OR $KEY2==14) {
	 					$cr_key=" AND p.ACTION_ID='".$KEY."'" ;
	 				} 
	 				$name_decl= "LIBELLE_ACTION";
	 			}

	 			if(! empty($ACTION_ID))
	 			{
	 				$cond.=" AND p.ACTION_ID='".$ACTION_ID."'";
	 				$cr_key='';
	 				if ($KEY2==13 OR $KEY2==14) {
	 					$cr_key=" AND p.ACTION_ID='".$KEY."'" ;
	 				} 
	 				$name_decl= "LIBELLE_ACTION";
	 				$id_decl= 'p.ACTION_ID'; 
	 				$name_decl= "LIBELLE_ACTION"; 
	 			}

	 			// if(!empty($ANNEE_BUDGETAIRE_ID))
	 			// {
	 			// $cond.=" AND p.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";
	 			// }

	 			if(!empty($IS_DOUBLE_COMMANDE))
	 			{
	 				$cond.=" AND raccro.IS_DOUBLE_COMMANDE=".$IS_DOUBLE_COMMANDE."";
	 			}

	 			if(!empty($LIGNE_BUDGETAIRE))
	 			{
	 				$cond.=" AND raccro.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE."";
	 			}

	 			

	 			$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
	 			if ($KEY2==11) {
	 				$query_principal="SELECT p.PTBA_TACHE_ID as ID,".$totaux."-if((SELECT  COALESCE(raccro.ENG_BUDGETAIRE, 0) AS total  FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID   JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID WHERE p.PTBA_TACHE_ID=ID ".$cond_trim." ".$cond1." ".$cond.")>=0,(SELECT  COALESCE(raccro.ENG_BUDGETAIRE, 0) AS total  FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID   JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID WHERE p.PTBA_TACHE_ID=ID ".$cond_trim." ".$cond." ".$cond1."),0) AS non_realise,inst.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,p.DESC_TACHE, date_format(raccro.DATE_DEMANDE,'%d-%m-%Y') as dat  FROM ptba_tache AS p JOIN execution_budgetaire_execution_tache ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID LEFT JOIN execution_budgetaire AS raccro ON raccro.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID WHERE 1 ".$cond." ".$cond1."";
	 			}else if ($KEY2==12 OR $KEY2==13) {
	 				$query_principal="SELECT  p.PTBA_TACHE_ID as ID, ".$totaux." AS monta,DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,p.DESC_TACHE, date_format(raccro.DATE_DEMANDE,'%d-%m-%Y') as dat FROM  ptba_tache p JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=p.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID LEFT JOIN execution_budgetaire raccro ON raccro.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID WHERE 1 ".$cond." ".$cond1." ".$cr_key."  ";
	 			}else{
	 				$query_principal="SELECT p.PTBA_TACHE_ID as ID,COALESCE(raccro.ENG_BUDGETAIRE, 0) AS total ,DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,p.DESC_TACHE, date_format(raccro.DATE_DEMANDE,'%d-%m-%Y') as dat FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID WHERE 1 ".$cond."  ".$cond1."  ".$cr_key." ".$cond_trim." ";
	 			}

	 			$limit='LIMIT 0,5';
	 			if ($_POST['length'] != -1)
      	{
        	$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
	      }
	      $order_by = '';

	      $order_column=array(1,1,1,1,1,1,1);
	      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY p.DESC_TACHE ASC';

	 			$search = !empty($_POST['search']['value']) ? ("AND (
	 				DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR p.DESC_TACHE LIKE '%$var_search%')") : '';

	 			if ($KEY2==10 or $KEY2==11) {
	 				$critere='';	
	 			}else{
	 				if($KEY2==1 OR $KEY2==12){
	 					if ($KEY==7) {
	 						$critere=" AND p.GRANDE_MASSE_ID  IN(6,7,8)";
	 					}else{
	 						$critere="  AND p.GRANDE_MASSE_ID=".$KEY."";
	 					}
	 				}
	 				$critere='';
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

	 				if ($KEY2==12 OR $KEY2==13) {
	 					$mona_de=number_format($row->monta,0,',',' ');
	 				}else if ($KEY2==11) {
	 					$mona_de=number_format($row->non_realise,0,',',' ');
	 				}else {
	 					$mona_de=number_format($row->total,0,',',' ');	 
	 				}

	 				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';

	 				if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
	 					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
	 				}else{
	 					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font> </center>';
	 				}
	 				if (strlen($row->DESC_TACHE) < 13){
	 					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
	 				}else{
	 					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_TACHE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
	 				}

	 				if (strlen($row->LIBELLE_ACTION) < 13){
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
	 				    }else{
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font> </center>';
	 				    }

	 				if (strlen($row->INTITULE_PROGRAMME) < 13){
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
	 				    }else{
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
	 				    }
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
	 				$engagement[] ='<center><font color="#000000" size=2><label>'.$row->dat.'</label></font> </center>';
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
	 			$session  = \Config\Services::session();
	 			$TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
	 			$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
	 			$SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
	 			$ACTION_ID=$this->request->getVar('ACTION_ID');
	 			$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
	 			$ACTIVITE=$this->request->getVar('ACTIVITE');
	 			$inst_conn=$this->request->getVar('inst_conn');
	 			$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
	 		
	 			$IS_DOUBLE_COMMANDE=$this->request->getVar('IS_DOUBLE_COMMANDE');
        $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
        $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');

	 			$user_id =$inst_conn;
	 			$inst_connect ='';
	 			$prof_connect ='';
	 			$type_connect ='';


	 			$cond_inst="";
	 			$cond_affectations1="";

	 			if (!empty($user_id)) {

	 				$user_affectation=("SELECT INSTITUTION_ID AS CODE_INSTITUTION FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") ");
	 				$user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
	 				$nombre=count($user_affectations);


	 				if ($nombre>0) {


	 					if ($nombre==1) {
	 						foreach ($user_affectations as $value) {
	 							$cond_inst=" AND CODE_INSTITUTION= ".$value->CODE_INSTITUTION;
	 							$cond_affectations1=" AND ptba.CODE_MINISTERE= ".$value->CODE_INSTITUTION;
	 						}
	 					}else if ($nombre>1){
	 						$inst="(";
	 						foreach ($user_affectations as $value) {
	 							$inst.=$value->CODE_INSTITUTION.",";

	 						}
           		//Enlever la dernier virgule
	 						$inst = substr($inst, 0, -1);
	 						$inst=$inst.")";
	 						$cond_inst.=" AND CODE_INSTITUTION IN ".$inst;
	 						$cond_affectations1.=" AND ptba.CODE_MINISTERE IN ".$inst;


	 					}
	 				}else{
	 					return redirect('Login_Ptba');

	 				}

	 			}else{
	 				return redirect('Login_Ptba');
	 			}
	 			$id_decl= 'TYPE_INSTITUTION_ID'; 
	 			$name_decl= "if(TYPE_INSTITUTION_ID=1,'Administration personnalisée','Ministère')";
	 			$totaux='';
	 			if ($IS_PRIVATE==1) {
	 				$cond_trim=" AND raccro.TRIMESTRE_ID=1" ;
	 				$totaux='SUM(COALESCE(BUDGET_T1, 0))';

	 			}else if ($IS_PRIVATE==2) {

	 				$cond_trim=" AND raccro.TRIMESTRE_ID=2" ;
	 				$totaux='SUM(COALESCE(BUDGET_T2, 0))';

	 			}else if ($IS_PRIVATE==3) {

	 				$cond_trim=" AND raccro.TRIMESTRE_ID=3" ;
	 				$totaux='SUM(COALESCE(BUDGET_T3, 0))';
	 			}else if ($IS_PRIVATE==4){

	 				$cond_trim=" AND raccro.TRIMESTRE_ID=4" ;
	 				$totaux='SUM(COALESCE(BUDGET_T4, 0))';
	 			}else {

	 				$cond_trim=" " ;
	 				$totaux='SUM(COALESCE(BUDGET_T1, 0)+COALESCE(BUDGET_T2, 0)+COALESCE(BUDGET_T3, 0)+COALESCE(BUDGET_T4, 0))';
	 			}
	 			$cond='';
	 			$cond1='';
	 			if(!empty($TYPE_INSTITUTION_ID)){
	 				$cond.=" AND inst.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID."";
	 				$id_decl= 'p.INSTITUTION_ID'; 
	 				$name_decl= "DESCRIPTION_INSTITUTION";	
	 			}
	 			if(! empty($INSTITUTION_ID))
	 			{
	 				$cond.=" AND p.INSTITUTION_ID='".$INSTITUTION_ID."'";
	 				$id_decl= 'inst_institutions_programmes.PROGRAMME_ID'; 
	 				$name_decl= "inst_institutions_programmes.INTITULE_PROGRAMME";
	 			}

	 			if(! empty($PROGRAMME_ID))
	 			{

	 				$cond.=" AND p.PROGRAMME_ID='".$PROGRAMME_ID."'";
	 				$id_decl= 'inst_institutions_actions.ACTION_ID'; 
	 				$name_decl= "inst_institutions_actions.LIBELLE_ACTION";
	 			}

	 			if(!empty($ACTION_ID))
	 			{
	 				$cond.=" AND p.ACTION_ID='".$ACTION_ID."'";
	 				$id_decl= 'inst_institutions_actions.ACTION_ID'; 
	 				$name_decl= "inst_institutions_actions.LIBELLE_ACTION"; 

	 			}

	 			if(!empty($LIGNE_BUDGETAIRE))
	 			{
	 				$cond.=" AND p.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
	 				$id_decl= "inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";
          $name_decl= "LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE";

	 			}

	 			if(!empty($PAP_ACTIVITE_ID))
	 			{
	 				$cond.=" AND p.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
	 				$id_decl= "pap_activites.PAP_ACTIVITE_ID";
          $name_decl= "pap_activites.DESC_PAP_ACTIVITE"; 
	 			}

	 			// if(!empty($ANNEE_BUDGETAIRE_ID))
	 			// {
	 			//   $cond.=" AND p.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";
	 			// }

	 			$cond_budg="";
	 			$data_real='';
	 			$data_realnon='';
	 			$realisation_non=("SELECT p.PTBA_TACHE_ID as ID,COALESCE(raccro.ENG_BUDGETAIRE, 0) AS realise ,DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,p.DESC_TACHE, date_format(raccro.DATE_DEMANDE,'%d-%m-%Y') as dat FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID WHERE 1 ".$cond." ".$cond_budg." ".$cond_inst." ".$cond_trim."");

	 			$compare=("SELECT ".$totaux." AS comp FROM ptba_tache p JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID WHERE 1 ".$cond." ".$cond_inst." ");

	 			$realisation_non_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$realisation_non.'")');
	 			$compare_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$compare.'")');
	 			$real= ($realisation_non_req['realise'] >0) ? $realisation_non_req['realise'] : 0 ;
	 			$non_rel = ($compare_req['comp'] > $real) ? $compare_req['comp'] - $real : 0 ;

	 			$data_real="{name:'".lang("messages_lang.label_realise")." ', y:".$real.",key:10,key2:10,key3:".$IS_PRIVATE.",color:'#00FFFF'},";
	 			$data_realnon="{name:'".lang("messages_lang.label_non_realise")."', y:".$non_rel.",key:11,key2:11,key3:".$IS_PRIVATE.",color:'#00008B'},";

	 			$excute_grande=("SELECT if(gra.GRANDE_MASSE_ID in (6,7,8),7,gra.GRANDE_MASSE_ID) as ID,if(gra.GRANDE_MASSE_ID in (6,7,8),'transferts et subsides',DESCRIPTION_GRANDE_MASSE) as name FROM `inst_grande_masse` gra JOIN ptba_tache p ON p.GRANDE_MASSE_ID=gra.GRANDE_MASSE_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID  WHERE 1 GROUP BY if(gra.GRANDE_MASSE_ID in (6,7,8),7,gra.GRANDE_MASSE_ID) ,if(gra.GRANDE_MASSE_ID in (6,7,8),'transferts et subsides',DESCRIPTION_GRANDE_MASSE)  ");
	 			$excute_grande_req=$this->ModelPs->getRequete(' CALL getTable("'.$excute_grande.'")');

	 			$data_execution='';
	 			$data_vote_gr='';
	 			$total_execution=0;
	 			$total_vote_gr=0;

	 			foreach ($excute_grande_req as $value)
	 			{
	 				if ($value->ID==7) {
	 					$cond_gr=' AND p.GRANDE_MASSE_ID  IN(6,7,8)';
	 				}else{
	 					$cond_gr= ' AND p.GRANDE_MASSE_ID='.$value->ID;	
	 				}

	 				$vote_gr=("SELECT ".$totaux." as vote FROM `ptba_tache` p JOIN `inst_grande_masse` gra ON p.GRANDE_MASSE_ID=gra.GRANDE_MASSE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID WHERE 1 ".$cond_gr." ".$cond." ".$cond_inst."");

	 				$execution=("SELECT SUM(COALESCE(`ENG_BUDGETAIRE`, 0)) AS liquide FROM `execution_budgetaire` AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID   JOIN ptba_tache p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID WHERE 1 ".$cond_budg."  ".$cond_gr." ".$cond." ".$cond_trim." ".$cond_inst." ");
	 				$vote_gr_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$vote_gr.'")');
	 				$execution_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$execution.'")');

	 				$vote_gr_sum= ($vote_gr_req['vote']>0) ? $vote_gr_req['vote'] : 0 ;
	 				$execution_sum= ($execution_req['liquide']>0) ? $execution_req['liquide'] : 0 ;

	 				$data_execution.="{name:'".$this->str_replacecatego($value->name)."', y:".$execution_sum.",key:".$value->ID.",key2:1,key3:".$IS_PRIVATE."},";
	 				$data_vote_gr.="{name:'".$this->str_replacecatego($value->name)."', y:".$vote_gr_sum.",key:".$value->ID.",key2:12,key3:".$IS_PRIVATE."},";
	 				$total_execution=$total_execution+$execution_req['liquide'];
	 				$total_vote_gr=$total_vote_gr+$vote_gr_req['vote'];
	 			}
	 			$monta_compare=("SELECT ".$id_decl." AS ID,".$name_decl." AS name,".$totaux." AS vote,COUNT(PTBA_TACHE_ID)ptb_vote,(SELECT SUM(COALESCE(raccro.ENG_BUDGETAIRE, 0)) AS realise FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID   JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID WHERE ".$id_decl."=ID ".$cond." ".$cond_inst." ".$cond_trim.") AS exc,(SELECT COUNT(p.PTBA_TACHE_ID) FROM execution_budgetaire AS raccro JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=raccro.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache  p ON p.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID WHERE ".$id_decl."=ID ".$cond." ".$cond_budg." ".$cond_inst." ".$cond_trim.") AS ptb_exc FROM `ptba_tache` AS p JOIN inst_institutions inst ON inst.INSTITUTION_ID=p.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=p.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=p.ACTION_ID JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=p.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=p.PAP_ACTIVITE_ID WHERE 1 ".$cond_inst." ".$cond."  GROUP BY ".$id_decl.",".$name_decl." ORDER BY vote DESC");

	 			$monta_compare_req=$this->ModelPs->getRequete(' CALL getTable("'.$monta_compare.'")');
	 			$data_monta_vote='';
	 			$data_monta_exc='';
	 			$data_ptb_exc='';
	 			$data_ptb_vote='';

	 			$total_monta_vote=0;
	 			$total_monta_exc=0;
	 			$total_ptb_exc=0;
	 			$total_ptb_vote=0;

	 			foreach ($monta_compare_req as $value)
	 			{
	 			$monta_vote=($value->vote > 0) ? $value->vote : 0 ;
	 			$monta_exc=($value->exc > 0) ? $value->exc : 0 ;
	 			$data_monta_vote.="{name:'".$this->str_replacecatego($value->name)."', y:".$monta_vote.",key:'".$value->ID."',key2:13,key3:".$IS_PRIVATE.",color:'#0B0B61'},";
	 			$data_monta_exc.="{name:'".$this->str_replacecatego($value->name)."', y:".$monta_exc.",key:'".$value->ID."',key2:14,key3:".$IS_PRIVATE.",color:'#B45F04'},";
	 			$data_ptb_vote.="{name:'".$this->str_replacecatego($value->name)."', y:".$value->ptb_vote.",key:'".$value->ID."',key2:13,key3:".$IS_PRIVATE.",color:'#086A87'},";	
	 			$data_ptb_exc.="{name:'".$this->str_replacecatego($value->name)."', y:".$value->ptb_exc.",key:'".$value->ID."',key2:14,key3:".$IS_PRIVATE.",color:'#8A0886'},";
	 				$total_monta_vote=$total_monta_vote+$value->vote;
	 				$total_monta_exc=$total_monta_exc+$value->exc;
	 				$total_ptb_exc=$total_ptb_exc+$value->ptb_exc;
	 				$total_ptb_vote=$total_ptb_vote+$value->ptb_vote;
	 }


	 $rapp="<script type=\"text/javascript\">
		Highcharts.chart('container', {

		chart: {
			type: 'pie'
		}, 
		title: {
			text: '<b>Réalisés vs non réalisés</b><br>',
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
		
		if(this.key==1){
			$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
			}else if(this.key==2){
			$(\"#idpro\").html(\"Engagement&nbspjurdique\");
			}else if(this.key==10){
			$(\"#idpro\").html(\" Liquidation\");
			}else if(this.key==11){
			$(\"#idpro\").html(\"Non&nbspréalisé\");
			}else if(this.key2==6){
			$(\"#idpro\").html(\"Paiement\");
			}else{
			$(\"#idpro\").html(\"Décaissement\");	
		 }
		$(\"#titre\").html(\"Détails comparaison budget\");
		$(\"#myModal\").modal('show');
		var row_count ='1000000';
		$(\"#mytable\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"ajax\":{
		url:\"".base_url('dashboard/Dashboard_Comparaison_Budget/detail_Comparaison_Budget')."\",
		type:\"POST\",
		data:{
			key:this.key,
			key2:this.key2,
			key3:this.key3,
			INSTITUTION_ID:$('#INSTITUTION_ID').val(),
			PROGRAMME_ID:$('#PROGRAMME_ID').val(),
			ACTION_ID:$('#ACTION_ID').val(),
			SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
			IS_PRIVATE:$('#IS_PRIVATE').val(),
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
			format: '{point.name} : {point.y:,f} BIF'
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
			name:'Réalisations',
			data: [".$data_real.$data_realnon."]
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
			text: '<b>Budget exécuté par grande masse  </b><br>',
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
			if(this.key2==1){
			$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
			}else if(this.key2==2){
			$(\"#idpro\").html(\"Liquidation\");
			}else if(this.key2==3){
			$(\"#idpro\").html(\" Décaissement\");
			}else if(this.key2==4){
			$(\"#idpro\").html(\"Engagement&nbspjurdique\");
			}else{
			$(\"#idpro\").html(\"Ordonnancement\");	
		  }
		  if(this.key3==1){
			$(\"#trim\").html(\"Budget&nbspdu&nbsp1er&nbsptrimestre\");
			}else if(this.key3==2){
			$(\"#trim\").html(\"Budget&nbspdu&nbsp2ème&nbsptrimestre\");
			}else if(this.key3==3){
			$(\"#trim\").html(\"Budget&nbspdu&nbsp3ème&nbsptrimestre\");
			}else if(this.key3==5){
			$(\"#trim\").html(\"Budget&nbsp&nbspannuel\");
			}else{
			$(\"#trim\").html(\"Budget&nbspdu&nbsp4ème&nbsptrimestre\");
		}
		$(\"#titre\").html(\"Détails budget exécuté par grande masse\");
		$(\"#myModal\").modal('show');
		var row_count ='1000000';
		$(\"#mytable\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"ajax\":{
		url:\"".base_url('dashboard/Dashboard_Comparaison_Budget/detail_Comparaison_Budget')."\",
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
			name:'Voté (".number_format($total_vote_gr,0,',',' ')." BIF)',
			data: [".$data_vote_gr."]
		},
		{
			name:'Exécution (".number_format($total_execution,0,',',' ')." BIF)',
			data: [".$data_execution."]
		}
		
		]
		});
		</script>
		";

        $rapp2="<script type=\"text/javascript\">
		Highcharts.chart('container2', {

		chart: {
			type: 'column'
		},
		title: {
			text: '<b>Budget voté vs budget réalisé</b><br>',
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
			
			if(this.key2==1){
				$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
				}else if(this.key2==2){
				$(\"#idpro\").html(\"Liquidation\");
				}else if(this.key2==3){
				$(\"#idpro\").html(\" Décaissement\");
				}else if(this.key2==4){
				$(\"#idpro\").html(\"Engagement&nbspjurdique\");
				}else{
				$(\"#idpro\").html(\"Ordonnancement\");	
			}
			if(this.key3==1){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp1er&nbsptrimestre\");
				}else if(this.key3==2){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp2ème&nbsptrimestre\");
				}else if(this.key3==3){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp3ème&nbsptrimestre\");
				}else if(this.key3==5){
				$(\"#trim\").html(\"Budget&nbsp&nbspannuel\");
				}else{
				$(\"#trim\").html(\"Budget&nbspdu&nbsp4ème&nbsptrimestre\");
			}
			$(\"#titre\").html(\"Détails budget voté vs budget réalisé\");
			$(\"#myModal\").modal('show');
			var row_count ='1000000';
			$(\"#mytable\").DataTable({
			\"processing\":true,
			\"serverSide\":true,
			\"bDestroy\": true,
			\"ajax\":{
			url:\"".base_url('dashboard/Dashboard_Comparaison_Budget/detail_Comparaison_Budget')."\",
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
			name:'Voté (".number_format($total_monta_vote,0,',',' ')." BIF)',
			cocolor:'#0B0B61',
			data: [".$data_monta_vote."]
		  },
		{
			name:'Exécution (".number_format($total_monta_exc,0,',',' ')." BIF)',
			color:'#B45F04',
			data: [".$data_monta_exc."]
		   }
		 ]
		});
		</script>
		";

		$rapp3="<script type=\"text/javascript\">
		Highcharts.chart('container3', {

		chart: {
			type: 'column'
		},
		title: {
			text: '<b>Activités votées vs activités exécutées </b><br>',
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
			if(this.key2==1){
				$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
				}else if(this.key2==2){
				$(\"#idpro\").html(\"Liquidation\");
				}else if(this.key2==3){
				$(\"#idpro\").html(\" Décaissement\");
				}else if(this.key2==4){
				$(\"#idpro\").html(\"Engagement&nbspjurdique\");
			}else{
			$(\"#idpro\").html(\"Ordonnancement\");	
		   }
			if(this.key3==1){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp1er&nbsptrimestre\");
				}else if(this.key3==2){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp2ème&nbsptrimestre\");
				}else if(this.key3==3){
				$(\"#trim\").html(\"Budget&nbspdu&nbsp3ème&nbsptrimestre\");
				}else if(this.key3==5){
				$(\"#trim\").html(\"Budget&nbsp&nbspannuel\");
			}else{
			$(\"#trim\").html(\"Budget&nbspdu&nbsp4ème&nbsptrimestre\");
		}
		$(\"#titre\").html(\"Détails activités votées vs activités exécutées\");
		$(\"#myModal\").modal('show');
		var row_count ='1000000';
		$(\"#mytable\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"ajax\":{
		url:\"".base_url('dashboard/Dashboard_Comparaison_Budget/detail_Comparaison_Budget')."\",
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
			name:'Voté (".number_format($total_ptb_vote,0,',',' ')." activités)',
			cocolor:'#086A87',
			data: [".$data_ptb_vote."]
		},
		{
			name:'Exécution (".number_format($total_ptb_exc,0,',',' ')." activités)',
			color:'#8A0886',
			data: [".$data_ptb_exc."]
		}
		
		]
		});
		</script>
		";


		$inst= '<option selected="" disabled="">sélectionner</option>';
		if (!empty($TYPE_INSTITUTION_ID))
		{
		$inst_sect='SELECT DISTINCT inst.INSTITUTION_ID as ID,DESCRIPTION_INSTITUTION FROM `ptba_tache` JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_inst.' ORDER BY ID ASC ';

		$inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
		foreach ($inst_sect_req as $key)
		{
			if (!empty($INSTITUTION_ID))
			{ 

				if ($INSTITUTION_ID==$key->ID) 
				{
					$inst.= "<option value ='".$key->ID."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
				else
				{
					$inst.= "<option value ='".$key->ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
			}
			else
			{
				$inst.= "<option value ='".$key->ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
			}
		 }
		}

		$soustutel= '<option selected="" disabled="">sélectionner</option>';
		if ($INSTITUTION_ID != '')
		{
		//print_r($INSTITUTION_ID);die();
		$inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'  ");
		$inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');
		$soustutel_sect="SELECT `CODE_SOUS_TUTEL`,`DESCRIPTION_SOUS_TUTEL` FROM `inst_institutions_sous_tutel` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY DESCRIPTION_SOUS_TUTEL ASC ";

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
		$program= '<option selected="" disabled="">sélectionner</option>';
		if ($INSTITUTION_ID != '')
		{
		$inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'  ");
		$inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');
		$program_sect="SELECT inst_institutions_programmes.PROGRAMME_ID as CODE_PROGRAMME,`INTITULE_PROGRAMME` FROM `inst_institutions_programmes` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY INTITULE_PROGRAMME ASC";

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

		$actions= '<option selected="" disabled="">sélectionner</option>';

		if (!empty($PROGRAMME_ID))
		{
		$progr_id=("SELECT PROGRAMME_ID FROM `inst_institutions_programmes` WHERE PROGRAMME_ID='".$PROGRAMME_ID."'  ");
		$progr_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$progr_id.'")');

		$actions_sect="SELECT DISTINCT ACTION_ID as CODE_ACTION,`LIBELLE_ACTION` FROM `inst_institutions_actions` WHERE PROGRAMME_ID='".$PROGRAMME_ID."' ORDER BY LIBELLE_ACTION ASC";

 

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

		echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1,'rapp2'=>$rapp2,'rapp3'=>$rapp3,'inst'=>$inst,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires,'ligne_activite'=>$ligne_activite));
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
		$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS `INTITULE_MINISTERE`,inst_institutions_programmes.`INTITULE_PROGRAMME`,inst_institutions_actions.`LIBELLE_ACTION`,RESULTAT_ATTENDUS_TACHE, date_format(execution_budgetaire_execution_tache.DATE_DEMANDE,'%d-%m-%Y') as dat,  REPLACE(RTRIM(ptba_tache.BUDGET_RESTANT_T4),' ','') AS name FROM ptba_tache LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID LEFT JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID WHERE 1 ".$cond." ";


		$limit='LIMIT 0,10';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';

		$order_column=array(1,1,1,1,1,1,1,1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba_tache.INSTITUTION_ID ASC';
		$search = !empty($_POST['search']['value']) ? ("AND (
			inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%')") : '';

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
			if (strlen($row->RESULTAT_ATTENDUS_TACHE) > 8){ 
				$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
				$sub_array[] = mb_substr($row->RESULTAT_ATTENDUS_TACHE, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_ATTENDUS_TACHE.'"><i class="fa fa-eye"></i></a>';
				$sub_array[] = mb_substr($row->RESULTAT_ATTENDUS_TACHE, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_ATTENDUS_TACHE.'"><i class="fa fa-eye"></i></a>';
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
	 			
	 