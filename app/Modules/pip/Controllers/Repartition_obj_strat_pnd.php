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

class Repartition_obj_strat_pnd extends BaseController
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
		return view('App\Modules\pip\Views\Repartition_obj_strat_pnd_list_view',$data); 
	}

	//Debut de la recuperation des projets par ministere 
	public function list_objectif_pnd()
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
		$order_column = array(1, 'DESCR_OBJECTIF_STRATEGIC_PND',1,1,1,1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_OBJECT_STRATEGIC_PND DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (DESCR_OBJECTIF_STRATEGIC_PND LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebase1= "SELECT DISTINCT obj_pnd.DESCR_OBJECTIF_STRATEGIC_PND, fina.ANNE_UN, fina.ANNE_DEUX, fina.ANNE_TROIS FROM objectif_strategique_pnd obj_pnd JOIN pip_demande_infos_supp info JOIN pip_demande_source_financement fina JOIN user_users user ON obj_pnd.ID_OBJECT_STRATEGIC_PND = info.ID_OBJECT_STRATEGIC_PND AND info.ID_DEMANDE_INFO_SUPP = fina.ID_DEMANDE_INFO_SUPP AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		$requetedebases1 = $requetedebase1 . ' ' . $conditions;
		$query_secondaire1 = "CALL `getTable`('" . $requetedebases1 . "');";
		$fetch_data1 = $this->ModelPs->datatable($query_secondaire1);
		
		$requetedebase2= "SELECT SUM(TOTAL_TRIENNAL) AS TOT_TOT_TRI FROM objectif_strategique obj JOIN pip_demande_infos_supp info JOIN pip_demande_source_financement fina JOIN user_users user ON obj.ID_OBJECT_STRATEGIQUE = info.ID_OBJECT_STRATEGIQUE AND info.ID_DEMANDE_INFO_SUPP = fina.ID_DEMANDE_INFO_SUPP AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		// $requetedebases2 = $requetedebase2 . ' ' . $conditions;
		$query_secondaire2 = "CALL `getTable`('" . $requetedebase2 . "')";
		$fetch_actions2 = $this->ModelPs->getRequeteOne($query_secondaire2);

		$requetedebasefilter1 = $requetedebase1 . ' ' . $conditionsfilter;
		$data = array();
		$ddd = 0;
		$u = 1;
		foreach ($fetch_data1 as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->DESCR_OBJECTIF_STRATEGIC_PND;
			$anne1= $row->ANNE_UN;
			$anne2= $row->ANNE_DEUX;
			$anne3= $row->ANNE_TROIS;
			//Calcul de la somme trienal
			$anne123 = array($anne1,$anne2,$anne3);
			$tot3ans = array_sum($anne123);
			//Calcul du pourcentage vertical
			$totTot3ans = !empty($fetch_actions2['TOT_TOT_TRI'])?$fetch_actions2['TOT_TOT_TRI']:$tot3ans; 
			$pour3ans = number_format(($tot3ans * 100 / $totTot3ans), 2, ',', '');

			$sub_array[] = $anne1;
			$sub_array[] = $anne2;
			$sub_array[] = $anne3;
			$sub_array[] = $tot3ans;
			$sub_array[] = $pour3ans; 

			$data[] = $sub_array;
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