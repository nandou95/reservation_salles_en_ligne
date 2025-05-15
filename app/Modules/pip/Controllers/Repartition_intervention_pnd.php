<?php
/**Eric SINDAYIGAYA
*Titre: Repartition des projets par ministere
*Numero de telephone: +257 62 04 03 00
*WhatsApp: +257 62 04 03 00
*Email pro: sinda.eric@mediabox.bi
*Email pers: ericjamesbarinako33@gmail.com
*Date: 04 dec 2023
**/

namespace  App\Modules\pip\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Repartition_intervention_pnd extends BaseController
{
	protected $session;
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->library = new CodePlayHelper();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	// Affichage de la liste des objectifs strategiques
	public function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		return view('App\Modules\pip\Views\Repartition_intervention_list_view',$data); 
	}

	//Debut de la recuperation des projets par ministere 
	public function list_intervention()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$user_connecte='AND user.USER_ID='.$user_id.'';

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
		$order_column = array(1, 'DESCR_AXE_INTERVATION_PND',1,1,1,1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_AXE_INTERVENTION_PND DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebase1= "SELECT DISTINCT axe_intv.DESCR_AXE_INTERVATION_PND, fina.ANNE_UN, fina.ANNE_DEUX, fina.ANNE_TROIS, fina.TOTAL_TRIENNAL FROM axe_intervention_pnd axe_intv JOIN pip_demande_infos_supp info JOIN pip_demande_source_financement fina JOIN user_users user ON axe_intv.ID_AXE_INTERVENTION_PND = info.ID_AXE_INTERVENTION_PND AND info.ID_DEMANDE_INFO_SUPP = fina.ID_DEMANDE_INFO_SUPP AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		$requetedebases1 = $requetedebase1 . ' ' . $conditions;
		$query_secondaire1 = "CALL `getTable`('" . $requetedebases1 . "');";
		$fetch_data1 = $this->ModelPs->datatable($query_secondaire1);

		$requetedebase3= "SELECT SUM(ANNE_UN) AS TOT_ANNE1, SUM(ANNE_DEUX) AS TOT_ANNE2,  SUM(ANNE_TROIS) AS TOT_ANNE3, SUM(TOTAL_TRIENNAL) AS TOT_TOT_TRI FROM axe_intervention_pnd axe_intv JOIN pip_demande_infos_supp info JOIN pip_demande_source_financement fina JOIN user_users user ON axe_intv.ID_AXE_INTERVENTION_PND = info.ID_AXE_INTERVENTION_PND AND info.ID_DEMANDE_INFO_SUPP = fina.ID_DEMANDE_INFO_SUPP AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		// $requetedebases3 = $requetedebase3 . ' ' . $conditions;
		$query_secondaire3 = "CALL `getTable`('" . $requetedebase3 . "')";
		$fetch_actions3 = $this->ModelPs->getRequeteOne($query_secondaire3);
		$requetedebasefilter1 = $requetedebase1 . ' ' . $conditionsfilter;
		$requetedebasefilter3 = $requetedebase3 . ' ' . $conditionsfilter;

		$data = array();
		$u = 1;
		foreach ($fetch_data1 as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->DESCR_AXE_INTERVATION_PND;
			$sub_array[] = $row->ANNE_UN;
			$sub_array[] = $row->ANNE_DEUX;
			$sub_array[] = $row->ANNE_TROIS;
			//Calcul du pourcentage vertical
			$tot3ans = !empty($row->TOTAL_TRIENNAL) ? $row->TOTAL_TRIENNAL:1;
			$totTot3ans = !empty($fetch_actions3['TOT_TOT_TRI']) ? $fetch_actions3['TOT_TOT_TRI']:1; 
			$pour3ans = number_format(($tot3ans * 100 / $totTot3ans), 2, ',', '');

			$sub_array[] = $tot3ans;
			$sub_array[] = $pour3ans; 
			$data[] = $sub_array;
		}

		$totanne1 = !empty($fetch_actions3['TOT_ANNE1'])?$fetch_actions3['TOT_ANNE1']:0;
		$totanne2 = !empty($fetch_actions3['TOT_ANNE2'])?$fetch_actions3['TOT_ANNE2']:0;
		$totanne3 = !empty($fetch_actions3['TOT_ANNE3'])?$fetch_actions3['TOT_ANNE3']:0;
		$tot_gen = !empty($fetch_actions3['TOT_TOT_TRI'])?$fetch_actions3['TOT_TOT_TRI']:0;

		$sub_array2 = array();
		$sub_array2[] = '';
		$sub_array2[] = lang('messages_lang.th_total');
		$sub_array2[] = $totanne1;
		$sub_array2[] = $totanne2;
		$sub_array2[] = $totanne3;
		$sub_array2[] = $tot_gen;
		if($tot_gen > 0)
		{
			$sub_array2[] = number_format(($tot_gen * 100 / $tot_gen), 2, ',', '');
			$data[] = $sub_array2;
			$pour1 = number_format(($totanne1 * 100 / $tot_gen), 2, ',', '');
			$pour2 = number_format(($totanne2 * 100 / $tot_gen), 2, ',', '');
			$pour3 = number_format(($totanne3 * 100 / $tot_gen), 2, ',', '');
			$pourtot = number_format(($tot_gen * 100 / $tot_gen), 2, ',', '');
			
			$sub_array3 = array();
			$sub_array3[] = '';
			$sub_array3[] = '%';
			$sub_array3[] = $pour1;
			$sub_array3[] = $pour2;
			$sub_array3[] = $pour3;
			$sub_array3[] = $pourtot;
			$sub_array3[] = '';
			$data[] = $sub_array3;
		}
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase1 . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter1 . "')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);	
	}
}
?>