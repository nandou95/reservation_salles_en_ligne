<?php
/*
*NDERAGAKURA Alain Charbel
*Titre: Rapport classification economique
*WhatsApp: +25776887837
*Email pro: charbel@mediabox.bi
*Date: 28 juin 2024
*/

namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
//use PhpOffice\PhpWord\Writer\PDF;
use Dompdf\Dompdf;

ini_set('max_execution_time', 4000);
ini_set('memory_limit','2048M'); 

class Classification_Economique_deux extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
		$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_IDD))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		//get data institution
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID','inst_institutions','1','CODE_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete,$institution);

		//get data op_tranches
		$op_tranches = $this->getBindParms(' TRIMESTRE_ID,DESC_TRIMESTRE','trimestre','1','TRIMESTRE_ID ASC');
		$data['op_tranches'] = $this->ModelPs->getRequete($psgetrequete,$op_tranches);
		//get annee budgetaire en cours
		$data['annee_budgetaire_en_cours']=$this->get_annee_budgetaire();
		//Sélectionner les annees budgetaires
		$get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID<='.$data['annee_budgetaire_en_cours'],'ANNEE_BUDGETAIRE_ID ASC');
		$data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);

		$get_rap_trim = $this->getBindParms('TRIMESTRE_RAPPORT_ID,CODE_RAP_TRIMESTRE,DESC_RAP_TRIMESTRE','trimestre_rapport','1','TRIMESTRE_RAPPORT_ID ASC');
		$data['rap_trim'] = $this->ModelPs->getRequete($psgetrequete, $get_rap_trim);
		return view('App\Modules\ihm\Views\Classification_Economique_deux_View',$data);   
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function get_dep()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');

		//Declaration des labels pour l'internalisation
		$input_select = lang("messages_lang.labelle_selecte");
		$prog= '<option value="">'.$input_select.'</option>';
		$gm= '<option value="">'.$input_select.'</option>';

		//get filtre programme via id Institution
		if(!empty($INSTITUTION_ID))
		{
			$bind_programme = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME','inst_institutions_programmes prog','prog.INSTITUTION_ID='.$INSTITUTION_ID,'INTITULE_PROGRAMME ASC');
			$programme= $this->ModelPs->getRequete($callpsreq, $bind_programme);
			foreach($programme as $progra)
			{
				if(!empty($PROGRAMME_ID))
				{
					if($PROGRAMME_ID==$progra->PROGRAMME_ID)
					{
						$prog.= "<option value ='".$progra->PROGRAMME_ID."' selected>".$progra->INTITULE_PROGRAMME."</option>";
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
		}

		// get filtre grande masse par programme
		if(!empty($PROGRAMME_ID))
		{
			$bind_gm = $this->getBindParms('DISTINCT gm.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON ptba.GRANDE_MASSE_ID=gm.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$PROGRAMME_ID.'','gm.GRANDE_MASSE_ID ASC');
			$grande_masse= $this->ModelPs->getRequete($callpsreq, $bind_gm);
			foreach($grande_masse as $val)
			{			
				$gm.= "<option value ='".$val->GRANDE_MASSE_ID."'>".$val->DESCRIPTION_GRANDE_MASSE."</option>";						
			}
		}

		$output = array("prog"=>$prog,"grande_masse"=>$gm);
		return $this->response->setJSON($output);
	}

  //get institution
	public function getinstution($value='')
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$db = db_connect();
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$columnselect='TYPE_INSTITUTION_ID';
		$table='inst_institutions';
		$where="INSTITUTION_ID=".$INSTITUTION_ID;
		$orderby=' INSTITUTION_ID DESC';
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$type = $this->ModelPs->getRequeteOne($callpsreq,$bindparams);
		$TYPE_INSTITUTION_ID = $type['TYPE_INSTITUTION_ID'];
		echo json_encode(array('TYPE_INSTITUTION_ID'=>$TYPE_INSTITUTION_ID));
	}

  //get liste de classification economique
	public function listing($value = 0)
	{
		$session = \Config\Services::session();
		$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_IDD))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
		$DATE_FIN = $this->request->getPost('DATE_FIN');
		$DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
		$ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
		$GRANDE_MASSE_ID=$this->request->getPost('GRANDE_MASSE_ID');
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$critere_date="";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('ptba.DESC_TACHE',1,1,1,1,1,1,1,1,1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba.DESC_TACHE  ASC';

		$search = !empty($_POST['search']['value']) ? (' AND ( ptba.DESC_TACHE LIKE "%'.$var_search.'%")') : '';

		$critere_tranche = '';

		/*get filtre institution*/
		if(!empty($INSTITUTION_ID))
		{
			if($INSTITUTION_ID>0)
			{	
				$critere.="AND inst.INSTITUTION_ID=".$INSTITUTION_ID;
			}

			if(!empty($PROGRAMME_ID))
			{
				if($PROGRAMME_ID>0)
				{	
					$critere.= " AND prog.PROGRAMME_ID=".$PROGRAMME_ID;
				}

				if(!empty($GRANDE_MASSE_ID))
				{
					if($GRANDE_MASSE_ID>0)
					{	
						$critere.= " AND ptba.GRANDE_MASSE_ID=".$GRANDE_MASSE_ID;
					}
				}
			}
		}

		//filtre annee budgetaire
		$critere_annee=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
		//critere date
		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere_date=" AND DATE_BON_ENGAGEMENT >= '".$DATE_DEBUT."'";
		}

		if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
		{
			$critere_date=" AND DATE_BON_ENGAGEMENT <= '".$DATE_FIN."'";
		}

		if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
		{
			$critere_date=" AND DATE_BON_ENGAGEMENT BETWEEN '".$DATE_DEBUT."' AND '".$DATE_FIN."'";
		}

		// Condition pour la requête principale
		$conditions=$critere.' '.$critere_annee.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		// Condition pour la requête de filtre
		$conditionsfilter=$critere.' '.$search.' '.$group;
		$requetedebase= "SELECT  ptba.PTBA_TACHE_ID,ptba.DESC_TACHE,ptba.BUDGET_ANNUEL as MONTANT_VOTE FROM ptba_tache ptba JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_grande_masse gm ON gm.GRANDE_MASSE_ID=ptba.GRANDE_MASSE_ID WHERE 1";
		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';
		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		// print_r($requetedebasefilter);die();
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;
		foreach($fetch_actions as $row)
		{
			$sub_array = array();
			//execution par ptba
			$get_exec = "SELECT exec.PTBA_TACHE_ID,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND PTBA_TACHE_ID=".$row->PTBA_TACHE_ID." ".$critere_date;
			$get_exec='CALL getTable("'.$get_exec.'")';
			$executes = $this->ModelPs->getRequeteOne( $get_exec);
			$CREDIT_VOTE=!empty($row->MONTANT_VOTE) ?$row->MONTANT_VOTE : '0';

			//Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($CREDIT_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['PTBA_TACHE_ID']==$mont_recep['PTBA_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($CREDIT_VOTE);
      }

			$DESC_TACHE=$row->DESC_TACHE;
			if(mb_strlen($row->DESC_TACHE) > 7)
			{
				$DESC_TACHE = mb_substr($row->DESC_TACHE, 0, 6).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
			}

			$MONTANT_ENGAGE=!empty($executes['MONTANT_ENGAGE']) ? $executes['MONTANT_ENGAGE']:0;
			$MONTANT_JURIDIQUE=!empty($executes['MONTANT_JURIDIQUE']) ? $executes['MONTANT_JURIDIQUE']:0;
			$MONTANT_LIQUIDATION=!empty($executes['MONTANT_LIQUIDATION']) ? $executes['MONTANT_LIQUIDATION']:0;
			$MONTANT_ORDONNANCEMENT=!empty($executes['MONTANT_ORDONNANCEMENT']) ? $executes['MONTANT_ORDONNANCEMENT']:0;
			$PAIEMENT=!empty($executes['PAIEMENT']) ? $executes['PAIEMENT']:0;
			$MONTANT_DECAISSEMENT=!empty($executes['MONTANT_DECAISSEMENT'])?$executes['MONTANT_DECAISSEMENT']:0;

			$sub_array[] = $DESC_TACHE;
			$sub_array[] = number_format($CREDIT_VOTE,0,","," ");
			$sub_array[] = number_format($TRANSFERTS_CREDITS,0,","," ");
			$sub_array[] = number_format($CREDIT_APRES_TRANSFERT,0,","," ");
			$sub_array[] = number_format($MONTANT_ENGAGE,0,","," ");//engag budgetaie
			$sub_array[] = number_format($MONTANT_JURIDIQUE,0,","," ");//engag juridik
			$sub_array[] = number_format($MONTANT_LIQUIDATION,0,","," ");//liquidat
			$sub_array[] = number_format($MONTANT_ORDONNANCEMENT,0,","," ");//ordonancem
			$sub_array[] = number_format($PAIEMENT,0,","," ");//paiement
			$sub_array[] = number_format($MONTANT_DECAISSEMENT,0,","," ");
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$requetedebases."')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$requetedebasefilter."')");
		$output = array(
			"draw" => floatval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);
	}

	//function pour exporter le Rapport de classification economique dans excel
	function exporter($INSTITUTION_ID=0,$PROGRAMME_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0,$GRANDE_MASSE_ID=0,$is_action=0)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_IDD))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$critere_inst = '';
		$critere_date="";
		$critere_prog='';
		$critere_gm="";
		$critere_annee='';

		if($INSTITUTION_ID>0)
		{
			$critere_inst = ' AND ptba.INSTITUTION_ID='.$INSTITUTION_ID;		
		}
		if($PROGRAMME_ID>0)
		{
			$critere_prog=' AND ptba.PROGRAMME_ID ='.$PROGRAMME_ID;
		}

		$critere_anne_ptba = '';
		if ($ANNEE_BUDGETAIRE_ID>0) 
		{
			$critere_annee="AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
			$critere_anne_ptba .=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
		}

		if ($GRANDE_MASSE_ID>0) 
		{
			$critere_gm="AND ptba.GRANDE_MASSE_ID=".$GRANDE_MASSE_ID;
		}

		$ann_eco=$this->get_annee_budgetaire();

		//filtre date debut et date fin
		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere_date=" AND DATE_DEMANDE >='".$DATE_DEBUT."'";
		}
		if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
		{
			$critere_date=" AND DATE_DEMANDE <= '".$DATE_FIN."'";
		}
		if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
		{
			$critere_date=" AND DATE_DEMANDE BETWEEN '".$DATE_DEBUT."' AND '".$DATE_FIN."'";
		}

		$getRequete='SELECT DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1'.$critere_inst.' '.$critere_anne_ptba.' ORDER BY CODE_INSTITUTION ASC';
		$getData = $this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");

		//get annee budgetaire
		$get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
		$annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
		$annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

		$p_deb = '01/07/'.substr($annee_dexcr, 0, 4);

		$periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
		$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) :date("d/m/Y");

		if($ann_eco != $ANNEE_BUDGETAIRE_ID)
		{
			$p_fin = '30/06/'.substr($annee_dexcr,5);
			$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
		}
		

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A3', 'CIRCUIT DES DEPENSES');
		$sheet->setCellValue('A4', 'CLASSIFICATION ECONOMIQUE');
		$sheet->setCellValue('A5', 'EXERCICE: '.$annee_dexcr.'     N° BUDGET: 1');
		$sheet->setCellValue('A6', 'PERIODE DU '.$periode_debut.' AU '.$periode_fin.'');
		//titre debut et titre fin
		if(!empty($INSTITUTION_ID))
		{
			$getInstit='SELECT CODE_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID='.$INSTITUTION_ID;
			$getInst = $this->ModelPs->getRequeteOne("CALL `getTable`('".$getInstit."')");
			$sheet->setCellValue('A7', 'Titre Budgétaire :'.$getInst['CODE_INSTITUTION'].'         Source Financement: 11');
		}
		else
		{
			$getInstit='SELECT MIN(CODE_INSTITUTION) AS first_code, MAX(CODE_INSTITUTION) AS last_code FROM inst_institutions WHERE 1 ORDER BY CODE_INSTITUTION ASC';
			$getInst = $this->ModelPs->getRequeteOne("CALL `getTable`('".$getInstit."')");
			$sheet->setCellValue('A7', 'Titre Budgétaire Début: '.$getInst['first_code'].' et Titre Budgétaire Fin: '.$getInst['last_code'].'         Source Financement: 11');
		}
		
		$sheet->setCellValue('A11', 'LIBELLE');
		$sheet->setCellValue('B11', 'BUDGET VOTE');
		$sheet->setCellValue('C11', 'TRANSFERT CREDITS');
		$sheet->setCellValue('D11', 'CREDIT APRES TRANSFERT');
		$sheet->setCellValue('E11', 'ENGAGEMENT BUDGETAIRE');
		$sheet->setCellValue('F11', 'ENGAGEMENT JURIDIQUE');
		$sheet->setCellValue('G11', 'LIQUIDATION');
		$sheet->setCellValue('H11', 'ORDONNANCEMENT');
		$sheet->setCellValue('I11', 'PAIEMENT');
		$sheet->setCellValue('J11', 'DECAISSEMENT');
		$rows = 12;

		//boucle pour les institutions 
		foreach ($getData as $key)
		{
			// Debut du gestion des institution dans excel

			// Debut montant vote institution ------------------------------------
			$columnselectactionptba="SUM(BUDGET_ANNUEL) AS somme";
			$params_inst_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' ',' ptba.INSTITUTION_ID ASC');
			$params_inst_ptba=str_replace('\"','"',$params_inst_ptba);
			$infos_sup_inst_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_inst_ptba);		
			$BUDGET_VOTE_INST=floatval($infos_sup_inst_ptba['somme']) ? $infos_sup_inst_ptba['somme'] : '0';
			// Fin montant vote institution --------------------------------------

			//Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);
 
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_INST=(floatval($BUDGET_VOTE_INST) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_INST < 0){
        $CREDIT_APRES_TRANSFERT_INST = $CREDIT_APRES_TRANSFERT_INST*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
      	$MONTANT_TRANSFERT = floatval($MONTANT_RECEPTION_INST);
      	$CREDIT_APRES_TRANSFERT_INST = floatval($BUDGET_VOTE_INST) + (floatval($MONTANT_RECEPTION_INST) - floatval($MONTANT_TRANSFERT_INST));
      }

			// Debut execution institution --------------------------------------------------
			$params_infos_inst='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_date.' '.$critere_annee.' GROUP BY ptba.INSTITUTION_ID';
			$infos_sup_inst=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_inst.'")');

			$MONTANT_ENGAGE_INST=!empty($infos_sup_inst['MONTANT_ENGAGE'])?$infos_sup_inst['MONTANT_ENGAGE']:0;
			$MONTANT_JURIDIQUE_INST=!empty($infos_sup_inst['MONTANT_JURIDIQUE'])?$infos_sup_inst['MONTANT_JURIDIQUE']:0;
			$MONTANT_LIQUIDATION_INST=!empty($infos_sup_inst['MONTANT_LIQUIDATION']) ? $infos_sup_inst['MONTANT_LIQUIDATION']:0;
			$MONTANT_ORDONNANCEMENT_INST=!empty($infos_sup_inst['MONTANT_ORDONNANCEMENT']) ? $infos_sup_inst['MONTANT_ORDONNANCEMENT']:0;
			$MONTANT_PAIEMENT_INST=!empty($infos_sup_inst['MONTANT_PAIEMENT']) ? $infos_sup_inst['MONTANT_PAIEMENT']:0;
			$MONTANT_DECAISSEMENT_INST=!empty($infos_sup_inst['MONTANT_DECAISSEMENT'])?$infos_sup_inst['MONTANT_DECAISSEMENT']:0;


			// Fin execution institution --------------------------------------------------

			$sheet->setCellValue('A'.$rows, $key->CODE_INSTITUTION.' '.$key->DESCRIPTION_INSTITUTION);
			$sheet->setCellValue('B'.$rows, $BUDGET_VOTE_INST);
			$sheet->setCellValue('C'.$rows, $MONTANT_TRANSFERT);
			$sheet->setCellValue('D'.$rows, $CREDIT_APRES_TRANSFERT_INST);
			$sheet->setCellValue('E'.$rows, $MONTANT_ENGAGE_INST);
			$sheet->setCellValue('F'.$rows, $MONTANT_JURIDIQUE_INST);
			$sheet->setCellValue('G'.$rows, $MONTANT_LIQUIDATION_INST);
			$sheet->setCellValue('H'.$rows, $MONTANT_ORDONNANCEMENT_INST);
			$sheet->setCellValue('I'.$rows, $MONTANT_PAIEMENT_INST);
			$sheet->setCellValue('J'.$rows, $MONTANT_DECAISSEMENT_INST);

		  // Fin du gestion d'institution dans excel

		  // Debut du gestion des programme d'une institution dans excel
		  $bindprogr=$this->getBindParms('DISTINCT ptba.PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME','inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.''.$critere_prog.'','CODE_PROGRAMME ASC');
		  $bindprogr=str_replace('\"','"',$bindprogr);
		  $progr= $this->ModelPs->getRequete($callpsreq,$bindprogr);
			foreach($progr as $valueprog)
			{
				// Debut gestion du programme -------------------------------------------
				$rows++;
				// debut execution programme ------------------------------------------------------
				$params_infos_prog='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_PROG,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_PROG,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_PROG,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_PROG,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_PROG,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_PROG FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID= '.$valueprog->PROGRAMME_ID.' '.$critere_date.' '.$critere_annee;
				$infos_sup_prog=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_prog.'")');

				$MONTANT_ENGAGE_PROG=!empty($infos_sup_prog['MONTANT_ENGAGE_PROG'])?$infos_sup_prog['MONTANT_ENGAGE_PROG']:'0';
				$MONTANT_JURIDIQUE_PROG=!empty($infos_sup_prog['MONTANT_JURIDIQUE_PROG'])?$infos_sup_prog['MONTANT_JURIDIQUE_PROG'] : '0';
				$MONTANT_LIQUIDATION_PROG=!empty($infos_sup_prog['MONTANT_LIQUIDATION']) ? $infos_sup_prog['MONTANT_LIQUIDATION'] : '0';
				$MONTANT_ORDONNANCEMENT_PROG=!empty($infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG']) ? $infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG'] : '0';
				$PAIEMENT_PROG=!empty($infos_sup_prog['MONTANT_PAIEMENT_PROG']) ? $infos_sup_prog['MONTANT_PAIEMENT_PROG'] : '0';
				$MONTANT_DECAISSEMENT_PROG=!empty($infos_sup_prog['MONTANT_DECAISSEMENT_PROG'])?$infos_sup_prog['MONTANT_DECAISSEMENT_PROG']:'0';
		  	// fin execution programme ------------------------------------------------------

				$params_vote_prog=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID="'.$valueprog->PROGRAMME_ID.'"','PTBA_TACHE_ID ASC');
				$params_vote_prog=str_replace('\"','"',$params_vote_prog);
				$get_vote_prog=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_prog);

				$BUDGET_VOTE_PROG=floatval($get_vote_prog['somme']) ? $get_vote_prog['somme'] : '0';

        //Montant transferé
	      $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);
	      $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
	      $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

	      //Montant receptionné
	      $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
	      $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
	      $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
	      
	      $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

	      if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
	      {
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG;
	      }else{
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
	      }

	      $CREDIT_APRES_TRANSFERT_PROG=(floatval($BUDGET_VOTE_PROG) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

	      if($CREDIT_APRES_TRANSFERT_PROG < 0){
	        $CREDIT_APRES_TRANSFERT_PROG = $CREDIT_APRES_TRANSFERT_PROG*(-1);
	      }

	      if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
	      {
	      	$MONTANT_TRANSFERT_PRG = $MONTANT_RECEPTION_PROG;
	      	$CREDIT_APRES_TRANSFERT_PROG = floatval($BUDGET_VOTE_PROG) + (floatval($MONTANT_RECEPTION_PROG) - floatval($MONTANT_TRANSFERT_PROG));
	      }


				$sheet->setCellValue('A'.$rows, '     '.$valueprog->CODE_PROGRAMME.' '.$valueprog->INTITULE_PROGRAMME);
				$sheet->setCellValue('B'.$rows, $BUDGET_VOTE_PROG);
				$sheet->setCellValue('C'.$rows, $MONTANT_TRANSFERT_PRG);
				$sheet->setCellValue('D'.$rows, $CREDIT_APRES_TRANSFERT_PROG);
				$sheet->setCellValue('E'.$rows, $MONTANT_ENGAGE_PROG);
				$sheet->setCellValue('F'.$rows, $MONTANT_JURIDIQUE_PROG);
				$sheet->setCellValue('G'.$rows, $MONTANT_LIQUIDATION_PROG);
				$sheet->setCellValue('H'.$rows, $MONTANT_ORDONNANCEMENT_PROG);
				$sheet->setCellValue('I'.$rows, $PAIEMENT_PROG);
				$sheet->setCellValue('J'.$rows, $MONTANT_DECAISSEMENT_PROG);

				// Fin gestion du programme -------------------------------------------

				//si on veut inclure les actions
				if($is_action==1)
				{
					$bindaction=$this->getBindParms('DISTINCT ptba.ACTION_ID,CODE_ACTION,LIBELLE_ACTION','inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID ',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'CODE_ACTION ASC');
					$bindaction=str_replace('\"','"',$bindaction);
					$actions= $this->ModelPs->getRequete($callpsreq,$bindaction);
					foreach ($actions as $key_action) 
					{
						$rows++;
			  		// debut execution action ------------------------------------------------------
						$params_infos_action='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_ACTIO,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_ACTIO,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_ACTIO,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_ACTIO,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_ACTIO,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_ACTIO FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' '.$critere_date.' '.$critere_annee;
						$infos_sup_action=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_action.'")');

						$params_vote_action=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,'ptba.PTBA_TACHE_ID ASC');
						$params_vote_action=str_replace('\"','"',$params_vote_action);
						$get_vote_action=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_action);
            $BUDGET_VOTE_ACTION=!empty($get_vote_action['somme']) ? $get_vote_action['somme'] : '0';

            //Montant transferé
			      $param_mont_trans_action = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_trans_action=str_replace('\"','"',$param_mont_trans_action);
			      $mont_transf_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_action);
			      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf_action['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_action = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_recep_action=str_replace('\"','"',$param_mont_recep_action);
			      $mont_recep_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_action);
			      $MONTANT_RECEPTION_ACTION=floatval($mont_recep_action['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_ACTIO = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

			      if($MONTANT_TRANSFERT_RESTE_ACTIO >= 0)
			      {
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO;
			      }else{
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_ACTIO=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

			      if($CREDIT_APRES_TRANSFERT_ACTIO < 0){
			        $CREDIT_APRES_TRANSFERT_ACTIO = $CREDIT_APRES_TRANSFERT_ACTIO*(-1);
			      }

			      if($mont_transf_action['ACTION_ID']==$mont_recep_action['ACTION_ID'])
			      {
			      	$MONTANT_TRANSFERT_ACTIO = $MONTANT_RECEPTION_ACTION;
			      	$CREDIT_APRES_TRANSFERT_ACTIO = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
			      }


						$MONTANT_ENGAGE_ACTIO=!empty($infos_sup_action['MONTANT_ENGAGE_ACTIO'])?$infos_sup_action['MONTANT_ENGAGE_ACTIO']:0;
						$MONTANT_JURIDIQUE_ACTIO=!empty($infos_sup_action['MONTANT_JURIDIQUE_ACTIO'])?$infos_sup_action['MONTANT_JURIDIQUE_ACTIO']:0;
						$MONTANT_LIQUIDATION_ACTIO=!empty($infos_sup_action['MONTANT_LIQUIDATION_ACTIO']) ? $infos_sup_action['MONTANT_LIQUIDATION_ACTIO']:0;
						$MONTANT_ORDONNANCEMENT_ACTIO=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']:0;
						$MONTANT_PAIEMENT_ACTIO=!empty($infos_sup_action['MONTANT_PAIEMENT_ACTIO']) ? $infos_sup_action['MONTANT_PAIEMENT_ACTIO']:0;
						$MONTANT_DECAISSEMENT_ACTIO=!empty($infos_sup_action['MONTANT_DECAISSEMENT_ACTIO'])?$infos_sup_action['MONTANT_DECAISSEMENT_ACTIO']:0;
						// debut execution action ------------------------------------------------------

						$sheet->setCellValue('A'.$rows, '     '.$key_action->CODE_ACTION.' '.$key_action->LIBELLE_ACTION);
						$sheet->setCellValue('B'.$rows, $BUDGET_VOTE_ACTION);
						$sheet->setCellValue('C'.$rows, $MONTANT_TRANSFERT_ACTIO);
						$sheet->setCellValue('D'.$rows, $CREDIT_APRES_TRANSFERT_ACTIO);
						$sheet->setCellValue('E'.$rows, $MONTANT_ENGAGE_ACTIO);
						$sheet->setCellValue('F'.$rows, $MONTANT_JURIDIQUE_ACTIO);
						$sheet->setCellValue('G'.$rows, $MONTANT_LIQUIDATION_ACTIO);
						$sheet->setCellValue('H'.$rows, $MONTANT_ORDONNANCEMENT_ACTIO);
						$sheet->setCellValue('I'.$rows, $MONTANT_PAIEMENT_ACTIO);
						$sheet->setCellValue('J'.$rows, $MONTANT_DECAISSEMENT_ACTIO);

			  		//Debut Gestion des grandes masses
						$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,' ptba.GRANDE_MASSE_ID ASC');
						$bindgm=str_replace('\"','"',$bindgm);
						$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
						foreach($grandmasse as $masses)
						{
							$rows++;
				  		//debut gestion du montant vote par grande masse
							$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ',' ptba.PTBA_TACHE_ID ASC');
							$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
							$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
							$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
							//fin du montant vote par grande masse

							//debut gestion des execution par grande masse
							$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

							$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');
							
							//Montant transferé
				      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
				      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
				      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

				      //Montant receptionné
				      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
				      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
				      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
				      
				      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

				      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
				      {
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
				      }else{
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
				      }

				      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

				      if($CREDIT_APRES_TRANSFERT_GM < 0){
				        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
				      }

				      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
				      {
				      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
				      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
				      }

							$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
							$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
							$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
							$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
							$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
							$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
				  		//fin gestion des execution par grande masse

							$sheet->setCellValue('A'.$rows, '           '.$masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE);
							$sheet->setCellValue('B'.$rows, $BUDGET_VOTE_GM);
							$sheet->setCellValue('C'.$rows, $MONTANT_TRANSFERT_GMS);
							$sheet->setCellValue('D'.$rows, $CREDIT_APRES_TRANSFERT_GM);
							$sheet->setCellValue('E'.$rows, $MONTANT_ENGAGE_GM);
							$sheet->setCellValue('F'.$rows, $MONTANT_JURIDIQUE_GM);
							$sheet->setCellValue('G'.$rows, $MONTANT_LIQUIDATION_GM);
							$sheet->setCellValue('H'.$rows, $MONTANT_ORDONNANCEMENT_GM);
							$sheet->setCellValue('I'.$rows, $MONTANT_PAIEMENT_GM);
							$sheet->setCellValue('J'.$rows, $MONTANT_DECAISSEMENT_GM);
						}
					}
				}
				else
				{
					//Debut Gestion des grandes masses
					$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm,' ptba.GRANDE_MASSE_ID ASC');
					$bindgm=str_replace('\"','"',$bindgm);
					$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
					foreach($grandmasse as $masses)
					{
						$rows++;
			  		//debut gestion du montant vote par grande masse
						$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ','ptba.PTBA_TACHE_ID ASC');
						$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
						$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
						$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
						//fin du montant vote par grande masse

						//debut gestion des execution par grande masse
						$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

						$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');

						//Montant transferé
			      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
			      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
			      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
			      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
			      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

			      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
			      {
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
			      }else{
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

			      if($CREDIT_APRES_TRANSFERT_GM < 0){
			        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
			      }

			      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
			      {
			      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
			      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
			      }

						$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
						$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
						$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
						$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
						$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
						$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
			  		//fin gestion des execution par grande masse

						$sheet->setCellValue('A'.$rows, '           '.$masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE);
						$sheet->setCellValue('B'.$rows, $BUDGET_VOTE_GM);
						$sheet->setCellValue('C'.$rows, $MONTANT_TRANSFERT_GMS);
						$sheet->setCellValue('D'.$rows, $CREDIT_APRES_TRANSFERT_GM);
						$sheet->setCellValue('E'.$rows, $MONTANT_ENGAGE_GM);
						$sheet->setCellValue('F'.$rows, $MONTANT_JURIDIQUE_GM);
						$sheet->setCellValue('G'.$rows, $MONTANT_LIQUIDATION_GM);
						$sheet->setCellValue('H'.$rows, $MONTANT_ORDONNANCEMENT_GM);
						$sheet->setCellValue('I'.$rows, $MONTANT_PAIEMENT_GM);
						$sheet->setCellValue('J'.$rows, $MONTANT_DECAISSEMENT_GM);
					}
				}
		  //Fin Gestion des grandes masses
			}
		// Fin du gestion des programme d'une institution dans excel
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
		$writer->save('world.xlsx');
		return $this->response->download('world.xlsx', null)->setFileName('classification économique.xlsx');
		return redirect('ihm/Classification_Economique');
	}

	//function pour exporter le Rapport de classification economique dans excel
	function export_pdf($INSTITUTION_ID=0,$PROGRAMME_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0,$GRANDE_MASSE_ID=0,$is_action=0)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_IDD))
		{
			return redirect('Login_Ptba/do_logout');
		}
		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$critere_inst = '';
		$critere_date="";
		$critere_prog='';
		$critere_gm="";
		$critere_annee='';

		$ann_eco=$this->get_annee_budgetaire();

		if($INSTITUTION_ID>0)
		{
			$critere_inst = ' AND ptba.INSTITUTION_ID='.$INSTITUTION_ID;		
		}
		if($PROGRAMME_ID>0)
		{
			$critere_prog=' AND ptba.PROGRAMME_ID ='.$PROGRAMME_ID;
		}

		$critere_anne_ptba = '';
		if ($ANNEE_BUDGETAIRE_ID>0) 
		{
			$critere_annee="AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
			$critere_anne_ptba .=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
		}

		if ($GRANDE_MASSE_ID>0) 
		{
			$critere_gm="AND ptba.GRANDE_MASSE_ID=".$GRANDE_MASSE_ID;
		}

		//filtre date debut et date fin
		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere_date=" AND DATE_DEMANDE >='".$DATE_DEBUT."'";
		}
		if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
		{
			$critere_date=" AND DATE_DEMANDE <= '".$DATE_FIN."'";
		}
		if(!empty($DATE_FIN) AND !empty($DATE_FIN)) 
		{
			$critere_date=" AND DATE_DEMANDE BETWEEN '".$DATE_DEBUT."' AND '".$DATE_FIN."'";
		}

		$getRequete='SELECT DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1'.$critere_inst.' '.$critere_anne_ptba.' ORDER BY CODE_INSTITUTION ASC';
		$getData = $this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");

		$get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
		$annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
		$annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

		$p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
		$periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
		$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) :date("d/m/Y");

		if($ann_eco != $ANNEE_BUDGETAIRE_ID)
		{
			$p_fin = '30/06/'.substr($annee_dexcr,5);
			$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
		}

		//titre du document word
		$dompdf = new Dompdf();
			//Définir la largeur du tableau
		$tableWidth = '100%';
		$html = "<html>";
		$html.= "<body>";
		//Ne renomme pas ces collones
		$html.='<h3><center>CIRCUIT DES DEPENSES</center></h3>';
		$html.='<h4><center>CLASSIFICATION ECONOMIQUE</center></h4>';
		$html.='<h5><center>Exercice '.$annee_dexcr.', N° Budgtet: 0 &nbsp;&nbsp;&nbsp;&nbsp;Période du '.$periode_debut.' au '.$periode_fin.'</center><h5>';
		if(!empty($INSTITUTION_ID))
		{
			$getInstit='SELECT CODE_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID='.$INSTITUTION_ID;
			$getInst = $this->ModelPs->getRequeteOne("CALL `getTable`('".$getInstit."')");
			$html.='<h4>Titre '.$getInst['CODE_INSTITUTION'].'<h4>';
		}else
		{
			$getInstit='SELECT MIN(CODE_INSTITUTION) AS first_code, MAX(CODE_INSTITUTION) AS last_code FROM inst_institutions WHERE 1 ORDER BY CODE_INSTITUTION ASC';
			$getInst = $this->ModelPs->getRequeteOne("CALL `getTable`('".$getInstit."')");
			$html.='<h4>Titres '.$getInst['first_code'].' au '.$getInst['last_code'].'<h4>';
		}
		
		$html.='<h4>Source Financement 11<h4>';		

		$html.= '<table style="border-collapse: collapse;margin-left:-15px; width: '.$tableWidth.'"  font-size: 12px; border="1">';
		$html.='<tr>
		<th style="border: 1px solid #000; width: 15%;font-size:10px ">LIBELLE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">CREDIT VOTE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">TRANSFERTS CREDITS</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">CREDIT APRES TRANSFERT</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">ENG BUDG.</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">ENG JURID.</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">LIQU.</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">ORDON.</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">PAIEMENT</th>
		<th style="border: 1px solid #000; width: 10%;font-size:10px ">DEC</th>
		</tr>';
		//boucle pour les institutions 
		foreach ($getData as $key)
		{
			// Debut du gestion des institution dans pdf

			// Debut montant vote institution ------------------------------------
			$columnselectactionptba="SUM(BUDGET_ANNUEL) AS somme";
			$params_inst_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' ',' ptba.INSTITUTION_ID ASC');
			$params_inst_ptba=str_replace('\"','"',$params_inst_ptba);
			$infos_sup_inst_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_inst_ptba);
			$BUDGET_VOTE_INST=floatval($infos_sup_inst_ptba['somme']);
			// Fin montant vote institution --------------------------------------

			// Debut execution institution --------------------------------------------------
			$params_infos_inst='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_date.' '.$critere_annee.' GROUP BY ptba.INSTITUTION_ID';
			$infos_sup_inst=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_inst.'")');

			$MONTANT_ENGAGE_INST=!empty($infos_sup_inst['MONTANT_ENGAGE'])?$infos_sup_inst['MONTANT_ENGAGE']:0;
			$MONTANT_JURIDIQUE_INST=!empty($infos_sup_inst['MONTANT_JURIDIQUE'])?$infos_sup_inst['MONTANT_JURIDIQUE']:0;
			$MONTANT_LIQUIDATION_INST=!empty($infos_sup_inst['MONTANT_LIQUIDATION']) ? $infos_sup_inst['MONTANT_LIQUIDATION']:0;
			$MONTANT_ORDONNANCEMENT_INST=!empty($infos_sup_inst['MONTANT_ORDONNANCEMENT']) ? $infos_sup_inst['MONTANT_ORDONNANCEMENT']:0;
			$MONTANT_PAIEMENT_INST=!empty($infos_sup_inst['MONTANT_PAIEMENT']) ? $infos_sup_inst['MONTANT_PAIEMENT']:0;
			$MONTANT_DECAISSEMENT_INST=!empty($infos_sup_inst['MONTANT_DECAISSEMENT'])?$infos_sup_inst['MONTANT_DECAISSEMENT']:0;

			//Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);

      
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_INST=(floatval($BUDGET_VOTE_INST) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_INST < 0){
        $CREDIT_APRES_TRANSFERT_INST = $CREDIT_APRES_TRANSFERT_INST*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
      	$MONTANT_TRANSFERT = floatval($MONTANT_RECEPTION_INST);
      	$CREDIT_APRES_TRANSFERT_INST = floatval($BUDGET_VOTE_INST) + (floatval($MONTANT_RECEPTION_INST) - floatval($MONTANT_TRANSFERT_INST));
      }
			// Fin execution institution --------------------------------------------------

			$html.='<tr>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.$key->CODE_INSTITUTION." ".$key->DESCRIPTION_INSTITUTION.'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($BUDGET_VOTE_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_INST,0,","," ") .'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_PAIEMENT_INST,0,","," ").'</td>';
			$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_INST,0,","," ") .'</td>';
			$html.='</tr>';			

			// Fin du gestion d'action dans word

			// Debut du gestion des programme d'une institution dans excel
			$bindprogr=$this->getBindParms('DISTINCT ptba.PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME','inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.''.$critere_prog.'','CODE_PROGRAMME ASC');
			$bindprogr=str_replace('\"','"',$bindprogr);
			$progr= $this->ModelPs->getRequete($callpsreq,$bindprogr);
			foreach($progr as $valueprog)
			{
			  // debut execution programme ------------------------------------------------------
				$params_infos_prog='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_PROG,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_PROG,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_PROG,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_PROG,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_PROG,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_PROG FROM execution_budgetaire exec LEFT JOIN transfert_historique_transfert histo ON histo.PTBA_TACHE_ID_TRANSFERT=exec.PTBA_TACHE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID= '.$valueprog->PROGRAMME_ID.' '.$critere_date.' '.$critere_annee;
				$infos_sup_prog=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_prog.'")');

				$MONTANT_ENGAGE_PROG=!empty($infos_sup_prog['MONTANT_ENGAGE_PROG'])?$infos_sup_prog['MONTANT_ENGAGE_PROG']:'0';
				$MONTANT_JURIDIQUE_PROG=!empty($infos_sup_prog['MONTANT_JURIDIQUE_PROG'])?$infos_sup_prog['MONTANT_JURIDIQUE_PROG'] : '0';
				$MONTANT_LIQUIDATION_PROG=!empty($infos_sup_prog['MONTANT_LIQUIDATION']) ? $infos_sup_prog['MONTANT_LIQUIDATION'] : '0';
				$MONTANT_ORDONNANCEMENT_PROG=!empty($infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG']) ? $infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG'] : '0';
				$PAIEMENT_PROG=!empty($infos_sup_prog['MONTANT_PAIEMENT_PROG']) ? $infos_sup_prog['MONTANT_PAIEMENT_PROG'] : '0';
				$MONTANT_DECAISSEMENT_PROG=!empty($infos_sup_prog['MONTANT_DECAISSEMENT_PROG'])?$infos_sup_prog['MONTANT_DECAISSEMENT_PROG']:'0';
				$transfert_montant_prog=!empty($infos_sup_prog['MONTANT_TRANSFERT_PROG'])?$infos_sup_prog['MONTANT_TRANSFERT_PROG']:0;
		  	// fin execution programme ------------------------------------------------------

				$params_vote_prog=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID="'.$valueprog->PROGRAMME_ID.'"','PTBA_TACHE_ID ASC');
				$params_vote_prog=str_replace('\"','"',$params_vote_prog);
				$get_vote_prog=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_prog);
				$BUDGET_VOTE_PROG=floatval($get_vote_prog['somme']) ? $get_vote_prog['somme'] : '0';

				//Montant transferé
	      $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);
	      $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
	      $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

	      //Montant receptionné
	      $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
	      $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
	      $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
	      
	      $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

	      if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
	      {
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG;
	      }else{
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
	      }

	      $CREDIT_APRES_TRANSFERT_PROG=(floatval($BUDGET_VOTE_PROG) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

	      if($CREDIT_APRES_TRANSFERT_PROG < 0){
	        $CREDIT_APRES_TRANSFERT_PROG = $CREDIT_APRES_TRANSFERT_PROG*(-1);
	      }

	      if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
	      {
	      	$MONTANT_TRANSFERT_PRG = $MONTANT_RECEPTION_PROG;
	      	$CREDIT_APRES_TRANSFERT_PROG = floatval($BUDGET_VOTE_PROG) + (floatval($MONTANT_RECEPTION_PROG) - floatval($MONTANT_TRANSFERT_PROG));
	      }

				$html.='<tr>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.$valueprog->CODE_PROGRAMME.' '.$valueprog->INTITULE_PROGRAMME.'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($BUDGET_VOTE_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_PRG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_PROG,0,","," ").'</td>';
				$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_PROG,0,","," ").'</td>';
				$html.='</tr>';

				//si on veut inclure les actions
				if($is_action==1)
				{
					$bindaction=$this->getBindParms('DISTINCT ptba.ACTION_ID,CODE_ACTION,LIBELLE_ACTION','inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID ',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'CODE_ACTION ASC');
					$bindaction=str_replace('\"','"',$bindaction);
					$actions= $this->ModelPs->getRequete($callpsreq,$bindaction);
					foreach ($actions as $key_action) 
					{
						// debut execution action ------------------------------------------------------
						$params_infos_action='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_ACTIO,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_ACTIO,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_ACTIO,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_ACTIO,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_ACTIO,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_ACTIO FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' '.$critere_date.' '.$critere_annee;
						$infos_sup_action=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_action.'")');

						$params_vote_act=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,'ptba.PTBA_TACHE_ID ASC');
						$params_vote_act=str_replace('\"','"',$params_vote_act);
						$get_vote_act=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_act);
						$BUDGET_VOTE_ACTION=!empty($get_vote_act['somme']) ? $get_vote_act['somme'] : '0';

						//Montant transferé
			      $param_mont_trans_action = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_trans_action=str_replace('\"','"',$param_mont_trans_action);
			      $mont_transf_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_action);
			      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf_action['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_action = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_recep_action=str_replace('\"','"',$param_mont_recep_action);
			      $mont_recep_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_action);
			      $MONTANT_RECEPTION_ACTION=floatval($mont_recep_action['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_ACTIO = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

			      if($MONTANT_TRANSFERT_RESTE_ACTIO >= 0)
			      {
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO;
			      }else{
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_ACTIO=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

			      if($CREDIT_APRES_TRANSFERT_ACTIO < 0){
			        $CREDIT_APRES_TRANSFERT_ACTIO = $CREDIT_APRES_TRANSFERT_ACTIO*(-1);
			      }

			      if($mont_transf_action['ACTION_ID']==$mont_recep_action['ACTION_ID'])
			      {
			      	$MONTANT_TRANSFERT_ACTIO = $MONTANT_RECEPTION_ACTION;
			      	$CREDIT_APRES_TRANSFERT_ACTIO = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
			      }

						$MONTANT_ENGAGE_ACTIO=!empty($infos_sup_action['MONTANT_ENGAGE_ACTIO'])?$infos_sup_action['MONTANT_ENGAGE_ACTIO']:0;
						$MONTANT_JURIDIQUE_ACTIO=!empty($infos_sup_action['MONTANT_JURIDIQUE_ACTIO'])?$infos_sup_action['MONTANT_JURIDIQUE_ACTIO']:0;
						$MONTANT_LIQUIDATION_ACTIO=!empty($infos_sup_action['MONTANT_LIQUIDATION_ACTIO']) ? $infos_sup_action['MONTANT_LIQUIDATION_ACTIO']:0;
						$MONTANT_ORDONNANCEMENT_ACTIO=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']:0;
						$MONTANT_PAIEMENT_ACTIO=!empty($infos_sup_action['MONTANT_PAIEMENT_ACTIO']) ? $infos_sup_action['MONTANT_PAIEMENT_ACTIO']:0;
						$MONTANT_DECAISSEMENT_ACTIO=!empty($infos_sup_action['MONTANT_DECAISSEMENT_ACTIO'])?$infos_sup_action['MONTANT_DECAISSEMENT_ACTIO']:0;
						// debut execution action ------------------------------------------------------
						$html.='<tr>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.$key_action->CODE_ACTION.' '.$key_action->LIBELLE_ACTION.'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($BUDGET_VOTE_ACTION,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_PAIEMENT_ACTIO,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_ACTIO,0,","," ").'</td>';
						$html.='</tr>';	

						//Debut Gestion des grandes masses
						$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,' ptba.GRANDE_MASSE_ID ASC');
						$bindgm=str_replace('\"','"',$bindgm);
						$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
						foreach($grandmasse as $masses)
						{
							//debut gestion du montant vote par grande masse
							$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ',' ptba.PTBA_TACHE_ID ASC');
							$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
							$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
							$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
							//fin du montant vote par grande masse

							//debut gestion des execution par grande masse
							$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

							$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');

							//Montant transferé
				      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
				      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
				      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

				      //Montant receptionné
				      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
				      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
				      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
				      
				      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

				      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
				      {
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
				      }else{
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
				      }

				      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

				      if($CREDIT_APRES_TRANSFERT_GM < 0){
				        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
				      }

				      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
				      {
				      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
				      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
				      }

							$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
							$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
							$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
							$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
							$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
							$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
				  		//fin gestion des execution par grande masse

							$html.='<tr>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.$masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE.'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($BUDGET_VOTE_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_GMS,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_PAIEMENT_GM,0,","," ").'</td>';
							$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_GM,0,","," ").'</td>';
							$html.='</tr>';
						}					
					}
				}		
				else
				{
					//Debut Gestion des grandes masses
					$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm,'ptba.GRANDE_MASSE_ID ASC');
					$bindgm=str_replace('\"','"',$bindgm);
					$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
					foreach($grandmasse as $masses)
					{
						//debut gestion du montant vote par grande masse
						$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ','ptba.PTBA_TACHE_ID ASC');
						$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
						$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
						$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
						//fin du montant vote par grande masse

						//debut gestion des execution par grande masse
						$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

						$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');

						//Montant transferé
			      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
			      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
			      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
			      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
			      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

			      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
			      {
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
			      }else{
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

			      if($CREDIT_APRES_TRANSFERT_GM < 0){
			        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
			      }

			      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
			      {
			      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
			      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
			      }

						$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
						$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
						$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
						$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
						$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
						$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
			  		//fin gestion des execution par grande masse

						$html.='<tr>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.$masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE.'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($BUDGET_VOTE_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_GMS,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_PAIEMENT_GM,0,","," ").'</td>';
						$html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_GM,0,","," ").'</td>';
						$html.='</tr>';
					}
				}
				//Debut Gestion des natures economique
			}
			// Fin du gestion d'un article d'un action dans word
		}
		$html.= "</body>";
		$html.='</table>';
		$html.='</html>';
		// Charger le contenu HTML
		$dompdf->loadHtml($html);
		// Définir la taille et l'orientation du papier
		$dompdf->setPaper('A4', 'landscape');
		// Rendre le HTML en PDF
		$dompdf->render();
		$name_file = 'Classification_economique'.uniqid().'.pdf';
		// Envoyer le fichier PDF en tant que téléchargement
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="Classification_economique.pdf"');
		echo $dompdf->output();
	}

	//function pour exporter le Rapport de classification economique dans word
	function export_word($INSTITUTION_ID=0,$PROGRAMME_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0,$GRANDE_MASSE_ID=0,$is_action=0)
	{
		$session  = \Config\Services::session();
		if(empty($session->get("SESSION_SUIVIE_PTBA_USER_ID")))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$critere_inst = '';
		$critere_date="";
		$critere_prog='';
		$critere_gm="";
		$critere_annee='';

		$ann_eco=$this->get_annee_budgetaire();

		if($INSTITUTION_ID>0)
		{
			$critere_inst = ' AND ptba.INSTITUTION_ID='.$INSTITUTION_ID;		
		}

		if($PROGRAMME_ID>0)
		{
			$critere_prog=' AND ptba.PROGRAMME_ID ='.$PROGRAMME_ID;
		}

		$critere_anne_ptba = '';
		if ($ANNEE_BUDGETAIRE_ID>0) 
		{
			$critere_annee="AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
			$critere_anne_ptba .=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
		}

		if ($GRANDE_MASSE_ID>0) 
		{
			$critere_gm="AND ptba.GRANDE_MASSE_ID=".$GRANDE_MASSE_ID;
		}
		
		//filtre date debut et date fin
		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere_date=" AND DATE_DEMANDE >='".$DATE_DEBUT."'";
		}

		if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
		{
			$critere_date=" AND DATE_DEMANDE <= '".$DATE_FIN."'";
		}

		if(!empty($DATE_FIN) AND !empty($DATE_FIN)) 
		{
			$critere_date=" AND DATE_DEMANDE BETWEEN '".$DATE_DEBUT."' AND '".$DATE_FIN."'";
		}

		$getRequete='SELECT DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1'.$critere_inst.' '.$critere_anne_ptba.' ORDER BY CODE_INSTITUTION ASC';
		$getData = $this->ModelPs->datatable("CALL `getTable`('".$getRequete."')");

		//get annee budgetaire
		$get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
		$annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
		$annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);
		$p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
		$periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb;
		$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) :date("d/m/Y");

		if($ann_eco != $ANNEE_BUDGETAIRE_ID)
		{
			$p_fin = '30/06/'.substr($annee_dexcr,5);
			$periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
		}

		$phpWord = new PhpWord();
		
		// Définir la section en mode paysage
		$sectionStyle = array(
			'orientation' => 'landscape',
			'marginTop' => 600,
		'marginRight' => 600, // Marge droite en twips
	  'marginLeft' => 600, // Marge gauche en twips
	  'colsNum' => 1,
	  );
		$section = $phpWord->addSection($sectionStyle);
		
		// creation du tableau avec bordure
		$tableStyle = [
			'borderSize' => 6,
		];	
		$phpWord->addTableStyle('myTable',$tableStyle);

	  //titre du document word
		// $section->addText('RAPPORT CLASSIFICATION ECONOMIQUE', ['bold' => true, 'underline' => 'single', 'size' => 16, 'align' => 'center']);
		$section->addText('CIRCUIT DES DEPENSES');
		$section->addText('CLASSIFICATION ECONOMIQUE');
		$section->addText('Exercice '.utf8_encode($annee_dexcr).',     N° Budgtet: 0 Période du '.utf8_encode($periode_debut).' au '.utf8_encode($periode_fin).'');

		$table = $section->addTable('myTable');
		$table->addRow();

		$table->addCell(1500)->addText('LIBELLE',['bold'=>true,'size'=>5], ['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('BUDGET VOTE',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('TRANSFERT CREDITS',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('CREDIT APRES TRANSFERT',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('ENGAGEMENT BUDGETAIRE',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('ENGAGEMENT JURIDIQUE',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('LIQUIDATION',['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('ORDONNANCEMENT', ['bold'=>true,'size'=>5], ['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('PAIEMENT', ['bold'=>true,'size'=>5], ['align'=>'center'],['border'=>2],['bordersize'=>6]);
		$table->addCell(3000)->addText('DECAISSEMENT', ['bold'=>true,'size'=>5],['align'=>'center'],['border'=>2],['bordersize'=>6]);

	  //boucle pour les institutions 
		foreach ($getData as $key)
		{
			// Debut du gestion des institution dans excel

			// Debut montant vote institution ------------------------------------
			$columnselectactionptba="SUM(BUDGET_ANNUEL) AS somme";
			$params_inst_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' ',' ptba.INSTITUTION_ID ASC');
			$params_inst_ptba=str_replace('\"','"',$params_inst_ptba);
			$infos_sup_inst_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_inst_ptba);			
			// Fin montant vote institution --------------------------------------

			// Debut execution institution --------------------------------------------------
			$params_infos_inst='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_date.' '.$critere_annee.' GROUP BY ptba.INSTITUTION_ID';
			$infos_sup_inst=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_inst.'")');

			$MONTANT_ENGAGE_INST=!empty($infos_sup_inst['MONTANT_ENGAGE'])?$infos_sup_inst['MONTANT_ENGAGE']:0;
			$MONTANT_JURIDIQUE_INST=!empty($infos_sup_inst['MONTANT_JURIDIQUE'])?$infos_sup_inst['MONTANT_JURIDIQUE']:0;
			$MONTANT_LIQUIDATION_INST=!empty($infos_sup_inst['MONTANT_LIQUIDATION']) ? $infos_sup_inst['MONTANT_LIQUIDATION']:0;
			$MONTANT_ORDONNANCEMENT_INST=!empty($infos_sup_inst['MONTANT_ORDONNANCEMENT']) ? $infos_sup_inst['MONTANT_ORDONNANCEMENT']:0;
			$MONTANT_PAIEMENT_INST=!empty($infos_sup_inst['MONTANT_PAIEMENT']) ? $infos_sup_inst['MONTANT_PAIEMENT']:0;
			$MONTANT_DECAISSEMENT_INST=!empty($infos_sup_inst['MONTANT_DECAISSEMENT'])?$infos_sup_inst['MONTANT_DECAISSEMENT']:0;

			//Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba_tache.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);

      
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_INST=(floatval($BUDGET_VOTE_INST) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_INST < 0){
        $CREDIT_APRES_TRANSFERT_INST = $CREDIT_APRES_TRANSFERT_INST*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
      	$MONTANT_TRANSFERT = floatval($MONTANT_RECEPTION_INST);
      	$CREDIT_APRES_TRANSFERT_INST = floatval($BUDGET_VOTE_INST) + (floatval($MONTANT_RECEPTION_INST) - floatval($MONTANT_TRANSFERT_INST));
      }
			// Fin execution institution --------------------------------------------------

			$table->addRow();
			$table->addCell(1500)->addText($key->CODE_INSTITUTION." ".$key->DESCRIPTION_INSTITUTION,['size' => 6]);
			$table->addCell(3000)->addText(number_format($BUDGET_VOTE_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_PAIEMENT_INST,0,","," "),['size' => 6]);
			$table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_INST,0,","," "),['size' => 6]);

			// Fin du gestion d'institution dans word

			// Debut du gestion des programme d'une institution dans word
			$bindprogr=$this->getBindParms('DISTINCT ptba.PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME','inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.''.$critere_prog.'','CODE_PROGRAMME ASC');
			$bindprogr=str_replace('\"','"',$bindprogr);
			$progr= $this->ModelPs->getRequete($callpsreq,$bindprogr);
			foreach($progr as $valueprog)
			{
				// debut execution programme ------------------------------------------------------
				$params_infos_prog='SELECT SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT_PROG,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_PROG,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_PROG,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_PROG,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_PROG,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_PROG,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_PROG FROM execution_budgetaire exec LEFT JOIN transfert_historique_transfert histo ON histo.PTBA_TACHE_ID_TRANSFERT=exec.PTBA_TACHE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID= '.$valueprog->PROGRAMME_ID.' '.$critere_date.' '.$critere_annee;
				$infos_sup_prog=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_prog.'")');

				$MONTANT_ENGAGE_PROG=!empty($infos_sup_prog['MONTANT_ENGAGE_PROG'])?$infos_sup_prog['MONTANT_ENGAGE_PROG']:'0';
				$MONTANT_JURIDIQUE_PROG=!empty($infos_sup_prog['MONTANT_JURIDIQUE_PROG'])?$infos_sup_prog['MONTANT_JURIDIQUE_PROG'] : '0';
				$MONTANT_LIQUIDATION_PROG=!empty($infos_sup_prog['MONTANT_LIQUIDATION']) ? $infos_sup_prog['MONTANT_LIQUIDATION'] : '0';
				$MONTANT_ORDONNANCEMENT_PROG=!empty($infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG']) ? $infos_sup_prog['MONTANT_ORDONNANCEMENT_PROG'] : '0';
				$PAIEMENT_PROG=!empty($infos_sup_prog['MONTANT_PAIEMENT_PROG']) ? $infos_sup_prog['MONTANT_PAIEMENT_PROG'] : '0';
				$MONTANT_DECAISSEMENT_PROG=!empty($infos_sup_prog['MONTANT_DECAISSEMENT_PROG'])?$infos_sup_prog['MONTANT_DECAISSEMENT_PROG']:'0';
				$transfert_montant_prog=!empty($infos_sup_prog['MONTANT_TRANSFERT_PROG'])?$infos_sup_prog['MONTANT_TRANSFERT_PROG']:0;
		  	// fin execution programme ------------------------------------------------------

				$params_vote_prog=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID="'.$valueprog->PROGRAMME_ID.'"','PTBA_TACHE_ID ASC');
				$params_vote_prog=str_replace('\"','"',$params_vote_prog);
				$get_vote_prog=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_prog);
				$BUDGET_VOTE_PROG=floatval($get_vote_prog['somme']) ? $get_vote_prog['somme'] : '0';

				//Montant transferé
	      $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);
	      $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
	      $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

	      //Montant receptionné
	      $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'1');
	      $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
	      $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
	      $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
	      
	      $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

	      if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
	      {
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG;
	      }else{
	        $MONTANT_TRANSFERT_PRG=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
	      }

	      $CREDIT_APRES_TRANSFERT_PROG=(floatval($BUDGET_VOTE_PROG) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

	      if($CREDIT_APRES_TRANSFERT_PROG < 0){
	        $CREDIT_APRES_TRANSFERT_PROG = $CREDIT_APRES_TRANSFERT_PROG*(-1);
	      }

	      if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
	      {
	      	$MONTANT_TRANSFERT_PRG = $MONTANT_RECEPTION_PROG;
	      	$CREDIT_APRES_TRANSFERT_PROG = floatval($BUDGET_VOTE_PROG) + (floatval($MONTANT_RECEPTION_PROG) - floatval($MONTANT_TRANSFERT_PROG));
	      }
				

				$table->addRow();
				$table->addCell(1500, ['cellMargin' => 40])->addText($valueprog->CODE_PROGRAMME.' '.$valueprog->INTITULE_PROGRAMME,['size' => 6]);
				$table->addCell(3000)->addText(number_format($BUDGET_VOTE_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_PRG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($PAIEMENT_PROG,0,","," "),['size' => 6]);
				$table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_PROG,0,","," "),['size' => 6]);
				// Fin gestion du programme -------------------------------------------

				//si on veut inclure les actions
				if($is_action==1)
				{
					$bindaction=$this->getBindParms('DISTINCT ptba.ACTION_ID,CODE_ACTION,LIBELLE_ACTION','inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID ',' ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID,'CODE_ACTION ASC');
					$bindaction=str_replace('\"','"',$bindaction);
					$actions= $this->ModelPs->getRequete($callpsreq,$bindaction);
					foreach ($actions as $key_action) 
					{
						
			  		// debut execution action ------------------------------------------------------
						$params_infos_action='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_ACTIO,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_ACTIO,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_ACTIO,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_ACTIO,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_ACTIO,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_ACTIO FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' '.$critere_date.' '.$critere_annee;
						$infos_sup_action=$this->ModelPs->getRequeteOne('CALL getTable("'.$params_infos_action.'")');

						$params_vote_act=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,'ptba.PTBA_TACHE_ID ASC');
						$params_vote_act=str_replace('\"','"',$params_vote_act);
						$get_vote_act=$this->ModelPs->getRequeteOne($callpsreq,$params_vote_act);
						$BUDGET_VOTE_ACTION=!empty($get_vote_act['somme']) ? $get_vote_act['somme'] : '0';

						//Montant transferé
			      $param_mont_trans_action = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_trans_action=str_replace('\"','"',$param_mont_trans_action);
			      $mont_transf_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_action);
			      $MONTANT_TRANSFERT_ACTION=floatval($mont_transf_action['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_action = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID,'1');
			      $param_mont_recep_action=str_replace('\"','"',$param_mont_recep_action);
			      $mont_recep_action=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_action);
			      $MONTANT_RECEPTION_ACTION=floatval($mont_recep_action['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_ACTIO = (floatval($MONTANT_TRANSFERT_ACTION) - floatval($MONTANT_RECEPTION_ACTION));

			      if($MONTANT_TRANSFERT_RESTE_ACTIO >= 0)
			      {
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO;
			      }else{
			        $MONTANT_TRANSFERT_ACTIO=$MONTANT_TRANSFERT_RESTE_ACTIO*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_ACTIO=(floatval($BUDGET_VOTE_ACTION) - floatval($MONTANT_TRANSFERT_ACTION)) + floatval($MONTANT_RECEPTION_ACTION);

			      if($CREDIT_APRES_TRANSFERT_ACTIO < 0){
			        $CREDIT_APRES_TRANSFERT_ACTIO = $CREDIT_APRES_TRANSFERT_ACTIO*(-1);
			      }

			      if($mont_transf_action['ACTION_ID']==$mont_recep_action['ACTION_ID'])
			      {
			      	$MONTANT_TRANSFERT_ACTIO = $MONTANT_RECEPTION_ACTION;
			      	$CREDIT_APRES_TRANSFERT_ACTIO = floatval($BUDGET_VOTE_ACTION) + (floatval($MONTANT_RECEPTION_ACTION) - floatval($MONTANT_TRANSFERT_ACTION));
			      }

						$MONTANT_ENGAGE_ACTIO=!empty($infos_sup_action['MONTANT_ENGAGE_ACTIO'])?$infos_sup_action['MONTANT_ENGAGE_ACTIO']:0;
						$MONTANT_JURIDIQUE_ACTIO=!empty($infos_sup_action['MONTANT_JURIDIQUE_ACTIO'])?$infos_sup_action['MONTANT_JURIDIQUE_ACTIO']:0;
						$MONTANT_LIQUIDATION_ACTIO=!empty($infos_sup_action['MONTANT_LIQUIDATION_ACTIO']) ? $infos_sup_action['MONTANT_LIQUIDATION_ACTIO']:0;
						$MONTANT_ORDONNANCEMENT_ACTIO=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT_ACTIO']:0;
						$MONTANT_PAIEMENT_ACTIO=!empty($infos_sup_action['MONTANT_PAIEMENT_ACTIO']) ? $infos_sup_action['MONTANT_PAIEMENT_ACTIO']:0;
						$MONTANT_DECAISSEMENT_ACTIO=!empty($infos_sup_action['MONTANT_DECAISSEMENT_ACTIO'])?$infos_sup_action['MONTANT_DECAISSEMENT_ACTIO']:0;
						// debut execution action ------------------------------------------------------

						$table->addRow();
						$table->addCell(1500, ['cellMargin' => 40])->addText($key_action->CODE_ACTION.' '.$key_action->LIBELLE_ACTION,['size' => 6]);
						$table->addCell(3000)->addText(number_format($BUDGET_VOTE_ACTION,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_PAIEMENT_ACTIO,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_ACTIO,0,","," "),['size' => 6]);

						//Debut Gestion des grandes masses
						$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm.' AND ptba.ACTION_ID='.$key_action->ACTION_ID,' ptba.GRANDE_MASSE_ID ASC');
						$bindgm=str_replace('\"','"',$bindgm);
						$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
						foreach($grandmasse as $masses)
						{
							//debut gestion du montant vote par grande masse
							$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ',' ptba.PTBA_TACHE_ID ASC');
							$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
							$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
							$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
							//fin du montant vote par grande masse

							//debut gestion des execution par grande masse
							$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN transfert_historique_transfert histo ON histo.PTBA_TACHE_ID_TRANSFERT=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

							$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');

							//Montant transferé
				      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
				      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
				      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

				      //Montant receptionné
				      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
				      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
				      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
				      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
				      
				      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

				      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
				      {
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
				      }else{
				        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
				      }

				      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

				      if($CREDIT_APRES_TRANSFERT_GM < 0){
				        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
				      }

				      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
				      {
				      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
				      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
				      }	

							$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
							$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
							$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
							$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
							$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
							$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
				  		//fin gestion des execution par grande masse
							$table->addRow();

							$table->addCell(1500, ['cellMargin' => 80])->addText($masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE,['size' => 6]);
							$table->addCell(3000)->addText(number_format($BUDGET_VOTE_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_GMS,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_PAIEMENT_GM,0,","," "),['size' => 6]);
							$table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_GM,0,","," "),['size' => 6]);
						}
					}
				}
				else
				{
					//Debut Gestion des grandes masses
					$bindgm=$this->getBindParms('DISTINCT ptba.GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse gm JOIN ptba_tache ptba ON gm.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' '.$critere_gm,' ptba.GRANDE_MASSE_ID ASC');
					$bindgm=str_replace('\"','"',$bindgm);
					$grandmasse= $this->ModelPs->getRequete($callpsreq,$bindgm);
					foreach($grandmasse as $masses)
					{
			  		//debut gestion du montant vote par grande masse
						$params_gm_ptba=$this->getBindParms($columnselectactionptba,'ptba_tache ptba','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$valueprog->PROGRAMME_ID.' AND ptba.GRANDE_MASSE_ID='.$masses->GRANDE_MASSE_ID.' ','ptba.PTBA_TACHE_ID ASC');
						$params_gm_ptba=str_replace('\"','"',$params_gm_ptba);					
						$infos_sup_gm_ptba=$this->ModelPs->getRequeteOne($callpsreq,$params_gm_ptba);
						$BUDGET_VOTE_GM=floatval($infos_sup_gm_ptba['somme']);
						//fin du montant vote par grande masse

						//debut gestion des execution par grande masse
						$params_infos_gm="SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE_GM,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE_GM,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION_GM,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT_GM,SUM(exec.PAIEMENT) AS MONTANT_PAIEMENT_GM,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT_GM FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13) AND ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$valueprog->PROGRAMME_ID." AND ptba.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID." ".$critere_annee."".$critere_date;

						$infos_sup_gm=$this->ModelPs->getRequeteOne('CALL `getTable`("'.$params_infos_gm.'")');

						//Montant transferé
			      $param_mont_trans_gm = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_trans_gm=str_replace('\"','"',$param_mont_trans_gm);
			      $mont_transf_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_gm);
			      $MONTANT_TRANSFERT_GM=floatval($mont_transf_gm['MONTANT_TRANSFERT']);

			      //Montant receptionné
			      $param_mont_recep_gm = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION, ptba_tache.GRANDE_MASSE_ID','transfert_historique_transfert JOIN ptba_tache ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba_tache.PTBA_TACHE_ID','ptba_tache.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba_tache.PROGRAMME_ID='.$valueprog->PROGRAMME_ID." AND ptba_tache.GRANDE_MASSE_ID=".$masses->GRANDE_MASSE_ID,'1');
			      $param_mont_recep_gm=str_replace('\"','"',$param_mont_recep_gm);
			      $mont_recep_gm=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_gm);
			      $MONTANT_RECEPTION_GM=floatval($mont_recep_gm['MONTANT_RECEPTION']);
			      
			      $MONTANT_TRANSFERT_RESTE_GM = (floatval($MONTANT_TRANSFERT_GM) - floatval($MONTANT_RECEPTION_GM));

			      if($MONTANT_TRANSFERT_RESTE_GM >= 0)
			      {
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM;
			      }else{
			        $MONTANT_TRANSFERT_GMS=$MONTANT_TRANSFERT_RESTE_GM*(-1);
			      }

			      $CREDIT_APRES_TRANSFERT_GM=(floatval($BUDGET_VOTE_GM) - floatval($MONTANT_TRANSFERT_GM)) + floatval($MONTANT_RECEPTION_GM);

			      if($CREDIT_APRES_TRANSFERT_GM < 0){
			        $CREDIT_APRES_TRANSFERT_GM = $CREDIT_APRES_TRANSFERT_GM*(-1);
			      }

			      if($mont_transf_gm['GRANDE_MASSE_ID']==$mont_recep_gm['GRANDE_MASSE_ID'])
			      {
			      	$MONTANT_TRANSFERT_GMS = $MONTANT_RECEPTION_GM;
			      	$CREDIT_APRES_TRANSFERT_GM = floatval($BUDGET_VOTE_GM) + (floatval($MONTANT_RECEPTION_GM) - floatval($MONTANT_TRANSFERT_GM));
			      }


						$MONTANT_ENGAGE_GM=!empty($infos_sup_gm['MONTANT_ENGAGE_GM'])?$infos_sup_gm['MONTANT_ENGAGE_GM']:'0';
						$MONTANT_JURIDIQUE_GM=!empty($infos_sup_gm['MONTANT_JURIDIQUE_GM'])?$infos_sup_gm['MONTANT_JURIDIQUE_GM'] : '0';
						$MONTANT_LIQUIDATION_GM=!empty($infos_sup_gm['MONTANT_LIQUIDATION_GM']) ? $infos_sup_gm['MONTANT_LIQUIDATION_GM'] : '0';
						$MONTANT_ORDONNANCEMENT_GM=!empty($infos_sup_gm['MONTANT_ORDONNANCEMENT_GM']) ? $infos_sup_gm['MONTANT_ORDONNANCEMENT_GM'] : '0';
						$MONTANT_PAIEMENT_GM=!empty($infos_sup_gm['MONTANT_PAIEMENT_GM']) ? $infos_sup_gm['MONTANT_PAIEMENT_GM'] : '0';
						$MONTANT_DECAISSEMENT_GM=!empty($infos_sup_gm['MONTANT_DECAISSEMENT_GM'])?$infos_sup_gm['MONTANT_DECAISSEMENT_GM']:'0';
			  		//fin gestion des execution par grande masse
						$table->addRow();

						$table->addCell(1500, ['cellMargin' => 80])->addText($masses->GRANDE_MASSE_ID.' '.$masses->DESCRIPTION_GRANDE_MASSE,['size' => 6]);
						$table->addCell(3000)->addText(number_format($BUDGET_VOTE_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_GMS,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_PAIEMENT_GM,0,","," "),['size' => 6]);
						$table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_GM,0,","," "),['size' => 6]);
					}
				}
				//Debut Gestion des natures economique
			}
			// Fin du gestion d'un article d'un action dans word
		}
		$filename = 'Rapport_classification_economique.docx';
		$objWriter=IOFactory::createWriter($phpWord, 'Word2007');
		$objWriter->save($filename);

		return $this->response->download($filename,null)->setFileName($filename);
	}

	function get_date_limit()
  {
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');

    $ann=$this->get_annee_budgetaire();

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    //annee budgetaire: mettre par défaut année en cours
    $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams_anne);
    $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];

    if($ann==$ANNEE_BUDGETAIRE_ID)
    {
      $date_start= substr($exercice,0,4).'-07-01';
      $date_fin= date('Y-m-d');
    }
    else
    {
      $date_start= substr($exercice,0,4).'-07-01';
      $date_fin= substr($exercice,5).'-06-30';
    }

    $output = array('status' => TRUE ,'datedebut' => $date_start , 'datefin' => $date_fin);
    return $this->response->setJSON($output);
  }	
}
?>