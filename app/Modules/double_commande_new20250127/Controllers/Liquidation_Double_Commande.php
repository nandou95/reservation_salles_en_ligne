<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Liquidation Double Commande
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

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Liquidation_Double_Commande extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		// $this->load->library('Excel');
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

		$sql_institution='SELECT CODE_SOUS_TUTEL,inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
		$sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");


		$tutel="<option value=''>--Sous tutel--</option>";
		foreach ($sous_tutel as $key)
		{
			$tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."-".$key->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	//vieux liquidation a faire
	public function get_liquid_Afaire($value='')
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

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  		$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];


		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Afaire_List',$data);   
	}

	function listing_liquid_Afaire($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE' , 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID  WHERE det.ETAPE_DOUBLE_COMMANDE_ID=10 AND IS_FINISHED!=1 ".$institution."";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
			}

			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='' style='color:#fbbf25;' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}
			
			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->DESC_PAP_ACTIVITE."'><i class='fa fa-eye'></i></a>") : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->DESC_TACHE."'><i class='fa fa-eye'></i></a>") : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."'><i class='fa fa-eye'></i></a>") : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->COMMENTAIRE."'><i class='fa fa-eye'></i></a>") : $row->COMMENTAIRE;

			$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
      $sub_array[]=$DESC_PAP_ACTIVITE;       
      $sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";

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
		//echo json_encode($output);	
	}

	//vieux liquidation deja fait
	public function get_liquid_deja_fait($value='')
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


		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  		$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];


		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Deja_Fait_List',$data);   
	}

	function listing_liquid_deja_fait($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = $institution;
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT', 'ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE', 'det.MONTANT_LIQUIDATION', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_LIQUIDATION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID>10 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE USER_ID=".$user_id.")";


		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

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
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/3";
			}
		
			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}

			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      
      $ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
      $ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');
      $MONTANT_LIQUIDATION=number_format($row->MONTANT_LIQUIDATION,'2',',',' ');

      $sub_array[]=$number;
      $sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
      $sub_array[]=$DESC_PAP_ACTIVITE;       
      $sub_array[]=$DESC_TACHE;
      $sub_array[]=$COMMENTAIRE;
      $sub_array[]=$ENG_BUDGETAIRE;
      $sub_array[]=$ENG_JURIDIQUE;
      $sub_array[] = !empty($MONTANT_LIQUIDATION) ? $MONTANT_LIQUIDATION : 0;
      $sub_array[]="<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
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
		return $this->response->setJSON($output);//echo json_encode($output);	
	}

	//vieux liquidation à corriger
	public function get_liquid_Acorriger($value='')
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

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];

		
		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Acorriger_List',$data);   
	}

	function listing_liquid_Acorriger($value = 0)
	{	
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = $institution;
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec.MONTANT_LIQUIDATION',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? ('  AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_LIQUIDATION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=12 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE USER_ID=".$user_id.")";


		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

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
			$step=($EtapeActuel) ? 'Liquidation/getOne_corriger':0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/3";
			}
			
			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='' style='color:#fbbf25;' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}

			
			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;


			$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');
			$MONTANT_LIQUIDATION=number_format($row->MONTANT_LIQUIDATION,'2',',',' ');

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
			$sub_array[]=$DESC_PAP_ACTIVITE;       
			$sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[]=!empty($MONTANT_LIQUIDATION) ? $MONTANT_LIQUIDATION : 0;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";

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
		return $this->response->setJSON($output);//echo json_encode($output);	
	}

	//view liquidation deja validé
	public function get_liquid_valider($value='')
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

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1 AND $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];


		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Valider_List',$data);   
	}

	function listing_liquid_valider($value = 0)
	{	
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1 AND $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = $institution;
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec.MONTANT_LIQUIDATION',  1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_LIQUIDATION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID>12 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE USER_ID=".$user_id.")";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

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
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/3";
			}

			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}

			
			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;


			$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');
			$MONTANT_LIQUIDATION=number_format($row->MONTANT_LIQUIDATION,'2',',',' ');

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
			$sub_array[]=$DESC_PAP_ACTIVITE;       
			$sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[] = !empty($MONTANT_LIQUIDATION) ? $MONTANT_LIQUIDATION : 0;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a><br><a class='btn btn-secondary btn-sm' title='' href='".base_url('double_commande_new/Liquidation/generer_doc_liquidation/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."'><span class='fa fa-file-pdf'></span></a>";
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
		return $this->response->setJSON($output);//echo json_encode($output);	
	}

	//vieux liquidation à valider
	public function get_liquid_Avalider($value='')
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

		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Avalider_List',$data);   
	}

	function listing_liquid_Avalider($value = 0)
	{	
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array(1, 'exec.NUMERO_BON_ENGAGEMENT', 'ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_LIQUIDATION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID  WHERE det.ETAPE_DOUBLE_COMMANDE_ID=11 ".$institution."";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

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
			$step=($EtapeActuel) ? 'Liquidation/getOne_conf':0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
			}
			
			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='' style='color:#fbbf25;' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}
			
			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;


			$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');
			$MONTANT_LIQUIDATION=number_format($row->MONTANT_LIQUIDATION,'2',',',' ');

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
			$sub_array[]=$DESC_PAP_ACTIVITE;       
			$sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[] = !empty($MONTANT_LIQUIDATION) ? $MONTANT_LIQUIDATION : 0;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";

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
		return $this->response->setJSON($output);//echo json_encode($output);	
	}
}
?>