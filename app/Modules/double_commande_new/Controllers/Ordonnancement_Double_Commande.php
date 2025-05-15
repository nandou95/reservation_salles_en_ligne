<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Ordonnancement Double Commande
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 14 sept 2023
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Ordonnancement_Double_Commande extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		define("DOMPDF_ENABLE_REMOTE", true);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//récupération du sous tutelle par rapport à l'institution
	public function getSousTutel()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

		$sql_institution='SELECT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel JOIN user_affectaion ON inst_institutions_sous_tutel.SOUS_TUTEL_ID=user_affectaion.SOUS_TUTEL_ID WHERE 1 AND user_affectaion.INSTITUTION_ID ='.$INSTITUTION_ID.' AND IS_SOUS_TUTEL=1 ';
		$sous_tutel_data = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");

		if (!empty($sous_tutel_data)) {
			$sous_tutel = $sous_tutel_data;
		}else{
			$sql_institution='SELECT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
			$sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");
		}

		$tutel="<option value=''>--".lang('messages_lang.selection_message')."--</option>";
		foreach ($sous_tutel as $key)
		{
			$tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	// changer les nombres du menu
	public function change_count()
	{
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
		$ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
		$DU=$this->request->getPost('DATE_DEBUT');
		$AU=$this->request->getPost('DATE_FIN');
		$data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU,$ANNEE_BUDGETAIRE_ID);
		$output['get_etape_reject_ordo']="<span>".$data_menu['get_etape_reject_ordo']."</span>";
		$output['get_ordon_Afaire']="<span>".$data_menu['get_ordon_Afaire']."</span>";
		$output['get_ordon_Afaire_sup']="<span>".$data_menu['get_ordon_Afaire_sup']."</span>";
		$output['get_ordon_deja_fait']="<span>".$data_menu['get_ordon_deja_fait']."</span>";
		$output['get_bord_spe']="<span>".$data_menu['get_bord_spe']."</span>";
		$output['get_bord_deja_spe']="<span>".$data_menu['get_bord_deja_spe']."</span>";
		$output['get_ordon_AuCabinet']="<span>".$data_menu['get_ordon_AuCabinet']."</span>";
		$output['get_ordon_BorderCabinet']="<span>".$data_menu['get_ordon_BorderCabinet']."</span>";
		$output['get_ordon_BonCED']="<span>".$data_menu['get_ordon_BonCED']."</span>";

		return $this->response->setJSON($output);
	}

	//view ordo a faire
	public function get_ordon_Afaire($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
	    $user_inst_res = 'CALL getTable("'.$user_inst.'");';
	    $institutions_user = $this->ModelPs->getRequete($user_inst_res);

	    $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
	    $SOUS_TUTEL_ID = 0;
	    $DU = 0;
	    $AU = 0;
	    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');

		$data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU,$ANNEE_BUDGETAIRE_ID);
		$data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];
		$data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
		$data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
		$data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
		$data['get_bord_spe']=$data_menu['get_bord_spe'];
		$data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
		$data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
		$data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
		$data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];

		$data['institutions_user']=$institutions_user;
		$data['first_element_id'] = $INSTITUTION_ID;
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Afaire_List',$data);   
	}

	function listing_ordon_Afaire($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_profil="";
		if ($profil_id!=1) 
		{
			$cond_profil="AND prof.PROFIL_ID=".$profil_id;
		}

		// $institution=' AND tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$institution = ' ';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
		$DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
		$DATE_FIN=$this->request->getPost('DATE_FIN');

		if(!empty($INSTITUTION_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.')';
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.')';
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
	    {
	      $institution.=" AND DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
	    }

	    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
	    {
	      $institution.=" AND DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
	    }
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array('NUMERO_BON_ENGAGEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','act.DESC_PAP_ACTIVITE','1', 'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_BON_ENGAGEMENT LIKE "%'.$var_search.'%" OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.COMMENTAIRE LIKE "%'.$var_search.'%" OR dev.DESC_DEVISE_TYPE LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.ENG_JURIDIQUE LIKE "%'.$var_search.'%" OR execdet.MONTANT_LIQUIDATION LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE_DEVISE LIKE "%'.$var_search.'%"	OR exec.ENG_JURIDIQUE_DEVISE LIKE "%'.$var_search.'%"	OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE "%'.$var_search.'%")'):'';

		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";
    	// Condition pour la requête principale
		$conditions = $search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $search;
		$requetedebase="SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,
							   execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,
							   
							   exec.NUMERO_BON_ENGAGEMENT,
							   exec.COMMENTAIRE,
							   exec.ENG_BUDGETAIRE,
							   exec.ENG_BUDGETAIRE_DEVISE,
							   exec.ENG_JURIDIQUE,
							   exec.ENG_JURIDIQUE_DEVISE,
							   execdet.MONTANT_LIQUIDATION LIQUIDATION,
                           	   execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,
							   inst.CODE_NOMENCLATURE_BUDGETAIRE,
							   ebtd.ETAPE_DOUBLE_COMMANDE_ID,
							   dev.DEVISE_TYPE_ID,
							   dev.DESC_DEVISE_TYPE 
						FROM execution_budgetaire_titre_decaissement ebtd 
						JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID
						JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID  
						JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID 
						JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID 
						JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID 
						JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
						WHERE 1 ".$cond_profil." AND dc.MOUVEMENT_DEPENSE_ID=4 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID=14 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution;

		$var_search=!empty($this->request->getPost('search')['value'])?$this->request->getPost('search')['value']:null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase .' '.$conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE IS_SALAIRE=0 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
			$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

			if(!empty($getProfil))
			{
				foreach ($getProfil as $value)
				{

					if ($prof_id == $value->PROFIL_ID || $prof_id==1)
					{
						$number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

						$bouton= "<a class='btn btn-primary btn-sm' title='Ordonnancement' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
					}
				}
			}

			//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");
			}
			$action='';
			$sub_array = array();
			$sub_array[] = $number;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
			$action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
			$sub_array[] = $action;
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

	// Ordonnancement niveau cabinet montant liquidation superieur ou egal 500.000.000
	public function get_ordon_Afaire_sup($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
	    $user_inst_res = 'CALL getTable("'.$user_inst.'");';
	    $institutions_user = $this->ModelPs->getRequete($user_inst_res);

	    $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
	    $SOUS_TUTEL_ID = 0;
	    $DU = 0;
	    $AU = 0;
	    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');

		$data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU,$ANNEE_BUDGETAIRE_ID);
		$data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];
		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
		$data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
		$data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
		$data['get_bord_spe']=$data_menu['get_bord_spe'];
		$data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
		$data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
		$data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
		$data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];

		$data['first_element_id'] = $INSTITUTION_ID;
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Afaire_sup_List',$data);   
	}

	function listing_ordon_Afaire_sup($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_profil="";
		if ($profil_id!=1) 
		{
			$cond_profil="AND prof.PROFIL_ID=".$profil_id;
		}

		// $institution=' AND tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$institution = "";

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.')';
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.')';
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array('NUMERO_BON_ENGAGEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','1','1', 'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_BON_ENGAGEMENT LIKE "%'.$var_search.'%" OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.COMMENTAIRE LIKE "%'.$var_search.'%" OR dev.DESC_DEVISE_TYPE LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.ENG_JURIDIQUE LIKE "%'.$var_search.'%" OR execdet.MONTANT_LIQUIDATION LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE_DEVISE LIKE "%'.$var_search.'%"	OR exec.ENG_JURIDIQUE_DEVISE LIKE "%'.$var_search.'%"	OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE "%'.$var_search.'%")'):'';

		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";

    	// Condition pour la requête principale
		$conditions = $search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $search;
		$requetedebase="SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID), exec.EXECUTION_BUDGETAIRE_ID,
		execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,
		exec.NUMERO_BON_ENGAGEMENT,
		exec.COMMENTAIRE,
		exec.ENG_BUDGETAIRE,
		exec.ENG_BUDGETAIRE_DEVISE,
		exec.ENG_JURIDIQUE,
		exec.ENG_JURIDIQUE_DEVISE,
		execdet.MONTANT_LIQUIDATION LIQUIDATION,
        execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,
		inst.CODE_NOMENCLATURE_BUDGETAIRE,
		ebtd.ETAPE_DOUBLE_COMMANDE_ID,
		dev.DEVISE_TYPE_ID,
		dev.DESC_DEVISE_TYPE 
		FROM execution_budgetaire_titre_decaissement ebtd 
		JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID
		JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID
		JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID 
		JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID 
		JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID 
		JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
		WHERE 1 ".$cond_profil." AND dc.MOUVEMENT_DEPENSE_ID=4 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID=15 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution;

		$var_search=!empty($this->request->getPost('search')['value'])?$this->request->getPost('search')['value']:null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase .' '.$conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE  IS_SALAIRE=0 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
			}

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
			$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

			if(!empty($getProfil))
			{
				foreach ($getProfil as $value)
				{

					if ($prof_id == $value->PROFIL_ID || $prof_id==1)
					{
						$number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

						$bouton= "<a class='btn btn-primary btn-sm' title='Ordonnancement' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
					}
				}
			}

			//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");
			}
			$action='';
			$sub_array = array();
			$sub_array[] = $number;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
			$action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
			$sub_array[] = $action;
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

	//vieux liquidation deja fait
	public function get_ordon_deja_fait($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}else{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$annee_budgetaire_en_cours=$this->get_annee_budgetaire();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
    	$get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_budgetaire_en_cours,'ANNEE_BUDGETAIRE_ID ASC');
		$data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);

		$user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
	    $user_inst_res = 'CALL getTable("'.$user_inst.'");';
	    $institutions_user = $this->ModelPs->getRequete($user_inst_res);

	    $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
	    $SOUS_TUTEL_ID = 0;
	    $DU = 0;
	    $AU = 0;
	    $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();

		$data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU);
		$data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];
		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
		$data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
		$data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
		$data['get_bord_spe']=$data_menu['get_bord_spe'];
		$data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
		$data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
		$data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
		$data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];

		$data['first_element_id'] = $INSTITUTION_ID;
		$data['annee_actuel'] = $ANNEE_BUDGETAIRE_ID;
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Deja_Fait_List',$data);
	}

	function listing_ordon_deja_fait($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_profil="";
		if ($profil_id!=1) 
		{
			$cond_profil="AND prof.PROFIL_ID=".$profil_id;
		}

		// $institution=' AND ptba.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$institution = ' ';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
		$DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
		$DATE_FIN=$this->request->getPost('DATE_FIN');
		$ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.')';
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.')';
		}

		if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$institution.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$institution.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$institution.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'" AND execdet.DATE_ORDONNANCEMENT <= "'.$DATE_FIN.'"';
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array('NUMERO_BON_ENGAGEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','1','1', 'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_BON_ENGAGEMENT LIKE "%'.$var_search.'%" OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.COMMENTAIRE LIKE "%'.$var_search.'%" OR dev.DESC_DEVISE_TYPE LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE LIKE "%'.$var_search.'%" OR exec.ENG_JURIDIQUE LIKE "%'.$var_search.'%" OR execdet.MONTANT_LIQUIDATION LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE_DEVISE LIKE "%'.$var_search.'%"	OR exec.ENG_JURIDIQUE_DEVISE LIKE "%'.$var_search.'%"	OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE "%'.$var_search.'%")'):'';

		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";
    	// Condition pour la requête principale
		$conditions = $search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $search;
		$requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID, exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE ORDONNANCEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,ebtd.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID>14 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>15 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution;

		$var_search=!empty($this->request->getPost('search')['value'])?$this->request->getPost('search')['value']:null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase .' '.$conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
			}

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
			$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

			if(!empty($getProfil))
			{
				foreach ($getProfil as $value)
				{

					if ($prof_id == $value->PROFIL_ID || $prof_id==1)
					{
						
					}
				}
			}

			//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			$ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);
			$ORDONNANCEMENT=number_format($ORDONNANCEMENT,0,","," ");


			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");

				$ORDONNANCEMENT=floatval($row->ORDONNANCEMENT_DEVISE);
				$ORDONNANCEMENT=number_format($ORDONNANCEMENT,4,","," ");
			}
			$action='';
			$sub_array = array();
			$sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$sub_array[] = $ORDONNANCEMENT;
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			$action .="<li>
			<a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
			</li>";

			$action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 16) ? "
			<li>
			<a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='green'>Correction</font></label></a>
			</li>" : "";

			$action .="
			<div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
			<center>
			<h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
			</h5>
			</center>
			</div>
			<div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
			<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
			".lang('messages_lang.quiter_action')."
			</button>
			<a href='".base_url("double_commande_new/Ordonnancement_Double_Commande/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."/".$ORDONNANCEMENT."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."</a>
			</div>";

			$sub_array[] = $action;
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

    // Fichier excel des Ordonnancement deja fait
	function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT='',$DATE_FIN='',$ANNEE_BUDGETAIRE_ID=0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

  		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

		// $critere=' AND ptba.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$critere = "";

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_user="";
		if ($prof_id!=1) 
		{
			$cond_user="AND USER_ID=".$user_id;
		}
		// $cond_user='';
		// if (condition) {

		// 	AND USER_ID=".$user_id."
		// }
	 
      	$nom_institution='';
		if($INSTITUTION_ID>0)
		{
			$critere.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
			$inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
            $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

            $nom_institution="Institution : ".$instt['DESCRIPTION_INSTITUTION'];
		}
        $nom_sous_titre='';
		if($SOUS_TUTEL_ID>0)
		{
			$critere.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
			 $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
              $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

              $nom_sous_titre="Sous titre : ".$instt['DESCRIPTION_SOUS_TUTEL'];
		}

		if($ANNEE_BUDGETAIRE_ID>0)
		{
			$critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$critere.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'" AND execdet.DATE_ORDONNANCEMENT <= "'.$DATE_FIN.'"';
		}

		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";
		$requetedebase= "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID, exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,ebtd.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID>14 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>15 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$critere;
		$getData = $this->ModelPs->datatable("CALL getTable('" . $requetedebase . "')"); 
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('B2', ''.$nom_institution.'');
		$sheet->setCellValue('B3', ''.$nom_sous_titre.'');

		$sheet->setCellValue('A5', '#');
		$sheet->setCellValue('B5', 'BON ENGAGEMENT');
		$sheet->setCellValue('C5', 'IMPUTATION');
		$sheet->setCellValue('D5', 'TACHE');
		// $sheet->setCellValue('E5', 'OBJET ENGAGEMENT');
		$sheet->setCellValue('E5', 'DEVISE');
		$sheet->setCellValue('F5', 'ENGAGEMENT BUDGETAIRE');
		$sheet->setCellValue('G5', 'ENGAGEMENT JURIDIQUE');
		$sheet->setCellValue('H5', 'LIQUDATION');
		$sheet->setCellValue('I5', 'ORDONNANCEMENT');
        
		$rows = 6;
		$i=0;
		foreach ($getData as $key)
		{
			//get les taches
			$get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
			$get_task = 'CALL `getTable`("'.$get_task.'");';
			$tasks = $this->ModelPs->getRequete($get_task);
			$task_items = '';

			foreach ($tasks as $task) {
				$task_items .= "- ".$task->DESC_TACHE . "\n";
			}

			$i++;
			$sheet->setCellValue('A' . $rows, $i);
			$sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
			$sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
			$sheet->setCellValue('D' . $rows, trim($task_items));
			$sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
			// $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
			$sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
			$sheet->setCellValue('F' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
			$sheet->setCellValue('G' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
			$sheet->setCellValue('H' . $rows, number_format($key->MONTANT_LIQUIDATION, $this->get_precision($key->MONTANT_LIQUIDATION), ",", " "));
			$sheet->setCellValue('I' . $rows, number_format($key->MONTANT_ORDONNANCEMENT, $this->get_precision($key->MONTANT_ORDONNANCEMENT), ",", " "));
			 
			$rows++;
		} 

		$code=date('YmdHis');
		$writer = new Xlsx($spreadsheet);
		$writer->save('world.xlsx');
		return $this->response->download('world.xlsx', null)->setFileName('Ordonancement'.$code.'.xlsx');

		return redirect('double_commande_new/Ordonnancement_Double_Commande/get_ordon_deja_fait');
	}
    //export PDF
	function exporter_deja_ordonnance($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

    	$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_user="";
		if ($prof_id!=1) 
		{
			$cond_user="AND USER_ID=".$user_id;
		}

		$critere=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		if(!empty($INSTITUTION_ID))
		{
			$critere.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$critere.=' AND execdet.DATE_ORDONNANCEMENT >= "'.$DATE_DEBUT.'" AND execdet.DATE_ORDONNANCEMENT <= "'.$DATE_FIN.'"';
		}

		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";
		$requetedebase= "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID, exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE ORDONNANCEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,ebtd.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID>14 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>15 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$critere;

		$query_secondaire = "CALL `getTable`('".$requetedebase."');";
		$fetch_data = $this->ModelPs->getRequete($query_secondaire);

		$dompdf = new Dompdf();
		$tableWidth = '100%';
		$html = "<html>";
		$html.= "<body>";
		$html.= "<div><strong>REPUBLIQUE DU BURUNDI</strong></div>";
		$html.= "<div><strong>MINISTERE DES FINANCES, DU BUDGET ET DA LA PLANIFICATION ECONOMIQUE</strong></div><br>";
		$html.= "<div><center><strong>EXECUTION DEJA ORDONNANCEE</strong></center></div><br>";
		$html.= '<table style="border-collapse: collapse;margin-left:-15px; width: '.$tableWidth.'"  font-size: 12px; border="1">';
		$html.='<tr>
		<th style="border: 1px solid #000; width: 5%;font-size:12px ">Nº</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">Nº&nbsp;BE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">IMPUTATION</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">TACHE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">DEVISE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">BUDGETAIRE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">JURIDIQUE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">LIQUIDATION</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">ORDONNANCEMENT</th>
		</tr>';

		$a=1;
		foreach ($fetch_data as $row)
		{
			//get les taches
			$get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
			$get_task = 'CALL `getTable`("'.$get_task.'");';
			$tasks = $this->ModelPs->getRequete($get_task);
			$task_items = '';

			foreach ($tasks as $task) {
				$task_items .= '<li style="margin-left:+10px">'.$task->DESC_TACHE.'</li>';
			}
			$TACHE=empty($task_items) ? '<center>-</center>' : $task_items;

			$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
			$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
			$MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION),0,',',' ');
			$MONTANT_ORDONNANCEMENT=number_format(floatval($row->MONTANT_ORDONNANCEMENT),0,',',' ');
			if($row->DEVISE_TYPE_ID!=1)
			{
			  $ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
			  $ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
			  $MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION_DEVISE),4,',',' ');
			  $MONTANT_ORDONNANCEMENT=number_format(floatval($row->MONTANT_ORDONNANCEMENT_DEVISE),'4',',',' ');
			}
			$html.='<tr>
			<td style="border: 1px solid #000; width: 5%;font-size:14px ">'.$a++.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$row->NUMERO_BON_ENGAGEMENT.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$TACHE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$row->DESC_DEVISE_TYPE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$ENG_BUDGETAIRE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$ENG_JURIDIQUE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$MONTANT_LIQUIDATION.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:14px ">'.$MONTANT_ORDONNANCEMENT.'</td>
			</tr>';
		}

		$html.='</table>';
		$html.= "</body>";		
		$html.='</html>';
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		// Envoyer le fichier PDF en tant que téléchargement
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="Executions deja ordonnance'.uniqid().'.pdf"');
		echo $dompdf->output();
	}

    // Ca retourne le Nombre de chiffres apres la virgule d'un entier donné
	function get_precision($value=0){

		$parts = explode('.', strval($value));
		return isset($parts[1]) ? strlen($parts[1]) : 0;

	}

	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	//fonction pour la correction
	function is_correction($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, $MONTANT_ORDONNANCEMENT)
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_detail_etape = $this->getBindParms('ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID,ebtd.EXECUTION_BUDGETAIRE_ID,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.DEVISE_TYPE_ID,exec.ORDONNANCEMENT, exec.ORDONNANCEMENT_DEVISE, det.MONTANT_ORDONNANCEMENT, det.MONTANT_ORDONNANCEMENT_DEVISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID', 'ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,'ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
		$donnees= $this->ModelPs->getRequeteOne($callpsreq, $bind_detail_etape);
		
		$MONTANT_ORDONNANCEMENT=$donnees['MONTANT_ORDONNANCEMENT'];

		//RESET LES MONTANTS DANS execution_budgetaire
		//if BIF
		if($donnees['DEVISE_TYPE_ID'] = 1){
			$ORDONNANCEMENT = $donnees['ORDONNANCEMENT'] - $MONTANT_ORDONNANCEMENT;
			$dataEB="ORDONNANCEMENT=".$ORDONNANCEMENT;
		}
		//if devise
		else
		{
			$ORDONNANCEMENT_DEVISE = $donnees['ORDONNANCEMENT_DEVISE'] - $MONTANT_ORDONNANCEMENT;
			$dataEB="ORDONNANCEMENT_DEVISE=".$ORDONNANCEMENT_DEVISE;
		}

		$tableEB='execution_budgetaire';
		$critereEB = " EXECUTION_BUDGETAIRE_ID=".$donnees['EXECUTION_BUDGETAIRE_ID'];
		$this->update_all_table($tableEB,$dataEB,$critereEB);

		//RESET LES MONTANTS DANS execution_budgetaire et execution_budgetaire_tache_detail
		//if BIF
		if($donnees['DEVISE_TYPE_ID'] = 1){
			$dataEBTache="MONTANT_ORDONNANCEMENT=0";
		}
		//if devise
		else
		{
			$dataEBTache="MONTANT_ORDONNANCEMENT_DEVISE=0";
		}

		$tableEBTache='execution_budgetaire_tache_detail';
		$critereEBTache = " EXECUTION_BUDGETAIRE_DETAIL_ID=".$donnees['EXECUTION_BUDGETAIRE_DETAIL_ID'];
		$this->update_all_table($tableEBTache,$dataEBTache,$critereEBTache);

		//changer l'etape
		$datatoupdate='';
		if($donnees['ETAPE_DOUBLE_COMMANDE_ID']== 16)
		{
			if($MONTANT_ORDONNANCEMENT >= 500000){
				$datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=15';
			}
			else{
				$datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=14';
			}
		}
		else{
			return redirect('Login_Ptba/homepage');
		}

		$updateTable='execution_budgetaire_titre_decaissement';
		$critere = " EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
		$this->update_all_table($updateTable,$datatoupdate,$critere);
		// $this->gestion_rejet_ptba($etape['EXECUTION_BUDGETAIRE_ID']);
		$url = 'double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire';
		return redirect($url);
	}

	//get les detail des taches
	function detail_task()
	{
		$session  = \Config\Services::session();

		$task_id = $this->request->getPost('task_id');

      //Filtres de la liste
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$critere1="";

		$critere3="";

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';

		// $requetedebase="SELECT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.EXECUTION_BUDGETAIRE_ID,	task.PTBA_TACHE_ID,	task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,	task.MONTANT_ENG_JURIDIQUE,	task.MONTANT_ENG_JURIDIQUE_DEVISE,task.MONTANT_LIQUIDATION,	task.MONTANT_LIQUIDATION_DEVISE,task.MONTANT_ORDONNANCEMENT,task.MONTANT_ORDONNANCEMENT_DEVISE,	ptba.DESC_TACHE,task.QTE,	dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,	ebtd.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$task_id." GROUP BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID";

		$requetedebase="SELECT DISTINCT(task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID),task.EXECUTION_BUDGETAIRE_ID,	task.PTBA_TACHE_ID,	task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,	task.MONTANT_ENG_JURIDIQUE,	task.MONTANT_ENG_JURIDIQUE_DEVISE,task.MONTANT_LIQUIDATION,	task.MONTANT_LIQUIDATION_DEVISE,task.MONTANT_ORDONNANCEMENT,task.MONTANT_ORDONNANCEMENT_DEVISE,	ptba.DESC_TACHE,task.QTE,	dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,	ebtd.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$task_id;

		// print_r($requetedebase);die();

		$order_column=array('ptba.DESC_TACHE','task.COMMENTAIRE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.COMMENTAIRE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

		$critaire = $critere1." ".$critere3;

      // print_r($critaire);die();
      //condition pour le query principale
		$conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;

      // condition pour le query filter
		$conditionsfilter=$critaire." ".$search;

		$requetedebases=$requetedebase." ".$conditions;

		$requetedebasefilter=$requetedebase." ".$conditionsfilter;

		$query_secondaire = 'CALL getTable("'.$requetedebases.'");';

		$fetch_actions = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u=1;
		foreach ($fetch_actions as $row)
		{
			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

			$MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
			$MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");

			$MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE);
			$MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->MONTANT_LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			$MONTANT_ORDONNANCEMENT=floatval($row->MONTANT_ORDONNANCEMENT);
			$MONTANT_ORDONNANCEMENT=number_format($MONTANT_ORDONNANCEMENT,0,","," ");
			if($row->DEVISE_TYPE_ID!=1)
			{
				$MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
				$MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");

				$MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE_DEVISE);
				$MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->MONTANT_LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");

				$MONTANT_ORDONNANCEMENT=floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
				$MONTANT_ORDONNANCEMENT=number_format($MONTANT_ORDONNANCEMENT,4,","," ");
			}

			$action='';
			$sub_array = array();
			$sub_array[] = $DESC_TACHE;
			$sub_array[] = $row->QTE;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $MONTANT_ENG_BUDGETAIRE;
			$sub_array[] = $MONTANT_ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			if($row->ETAPE_DOUBLE_COMMANDE_ID >= 16 ){$sub_array[] = $MONTANT_ORDONNANCEMENT;}
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
		$recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);

	    return $this->response->setJSON($output);//echo json_encode($output);
	}
}
?>