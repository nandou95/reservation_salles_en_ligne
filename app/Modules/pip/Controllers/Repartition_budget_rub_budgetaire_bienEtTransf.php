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

class Repartition_budget_rub_budgetaire_bienEtTransf extends BaseController
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

	// Affichage de la liste des gap des financement selon les pilier
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
		return view('App\Modules\pip\Views\Repartition_budget_rub_budgetaire_bienEtTransf_list_view',$data); 
	}

	//Debut de la recuperation des projets par ministere 
  public function list_budgt_bienEtTransf()
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
		$order_column = array(1, 'DESCRIPTION_INSTITUTION',1,1,1,1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY INSTITUTION_ID DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

      // info supp,budget livra,nomenc budgat,institution,projet livrable,user  //tables concernees
		$requetedebase1= "SELECT DISTINCT instit.DESCRIPTION_INSTITUTION FROM inst_institutions instit JOIN pip_demande_infos_supp info JOIN  pip_budget_projet_livrable pro_livra JOIN pip_nomenclature_budgetaire nom_budg JOIN pip_budget_livrable_nomenclature_budgetaire livra JOIN user_users user ON info.INSTITUTION_ID = instit.INSTITUTION_ID AND info.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE  AND nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		$requetedebases1 = $requetedebase1 . ' ' . $conditions;
		$query_secondaire1 = "CALL `getTable`('" . $requetedebases1 . "');";
		$fetch_data1 = $this->ModelPs->datatable($query_secondaire1);

		$requetedebase3= "SELECT SUM(livra.ANNE_UN) AS LIVRA_ANNE1, SUM(livra.ANNE_DEUX) AS LIVRA_ANNE2,SUM(livra.ANNE_TROIS) AS LIVRA_ANNE3 FROM inst_institutions instit JOIN pip_demande_infos_supp info JOIN  pip_budget_projet_livrable pro_livra JOIN pip_nomenclature_budgetaire nom_budg JOIN pip_budget_livrable_nomenclature_budgetaire livra JOIN user_users user ON info.INSTITUTION_ID = instit.INSTITUTION_ID AND info.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE  AND nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE user.USER_ID = $user_id AND nom_budg.ID_NOMENCLATURE = 2";
		$requetedebases3 = $requetedebase3 . ' ' . $conditions;
		$query_secondaire3 = "CALL `getTable`('" . $requetedebases3 . "')";
		$fetch_actions3 = $this->ModelPs->getRequeteOne($query_secondaire3);

    $requetedebase4= "SELECT SUM(livra.ANNE_UN) AS LIVRA_ANNE1, SUM(livra.ANNE_DEUX) AS LIVRA_ANNE2,SUM(livra.ANNE_TROIS) AS LIVRA_ANNE3 FROM inst_institutions instit JOIN pip_demande_infos_supp info JOIN  pip_budget_projet_livrable pro_livra JOIN pip_nomenclature_budgetaire nom_budg JOIN pip_budget_livrable_nomenclature_budgetaire livra JOIN user_users user ON info.INSTITUTION_ID = instit.INSTITUTION_ID AND info.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE  AND nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE AND user.INSTITUTION_ID = info.INSTITUTION_ID WHERE user.USER_ID = $user_id AND nom_budg.ID_NOMENCLATURE = 5";
		$requetedebases4 = $requetedebase4 . ' ' . $conditions;
		$query_secondaire4 = "CALL `getTable`('" . $requetedebases4 . "')";
		$fetch_actions4 = $this->ModelPs->getRequeteOne($query_secondaire4);
        
		$requetedebasefilter1 = $requetedebase1 . ' ' . $conditionsfilter;
        
		$data = array();
		foreach ($fetch_data1 as $row)
		{
			$sub_array = array();
            //Budget totat Investissement
			$sub_array[] = $row->DESCRIPTION_INSTITUTION;
			$sub_array[] = !empty($fetch_actions3['LIVRA_ANNE1'])?$fetch_actions3['LIVRA_ANNE1']:'-';
			$sub_array[] = !empty($fetch_actions3['LIVRA_ANNE2'])?$fetch_actions3['LIVRA_ANNE2']:'-';
			$sub_array[] = !empty($fetch_actions3['LIVRA_ANNE3'])?$fetch_actions3['LIVRA_ANNE3']:'-';
            //Budget tota Personnel
			$sub_array[] = !empty($fetch_actions4['LIVRA_ANNE1'])?$fetch_actions4['LIVRA_ANNE1']:'-';
			$sub_array[] = !empty($fetch_actions4['LIVRA_ANNE2'])?$fetch_actions4['LIVRA_ANNE2']:'-';
			$sub_array[] = !empty($fetch_actions4['LIVRA_ANNE3'])?$fetch_actions4['LIVRA_ANNE3']:'-';
			$data[] = $sub_array;
		}
        //Totalisation des budget par investissement
		$tot_invest_anne1 = 0;
		$tot_invest_anne2 = 0;
		$tot_invest_anne3 = 0;
		$tot_invest_anne1 += $fetch_actions3['LIVRA_ANNE1'];
		$tot_invest_anne2 += $fetch_actions3['LIVRA_ANNE2'];
		$tot_invest_anne3 += $fetch_actions3['LIVRA_ANNE3'];

		$sub_array2 = array();
		$sub_array2[] = lang('messages_lang.th_total');
		$sub_array2[] = $tot_invest_anne1;
		$sub_array2[] = $tot_invest_anne2;
		$sub_array2[] = $tot_invest_anne3;
		// $data[] = $sub_array2;

        //Totalisation ddes budget par personnel
		$tot_perso_anne1 = 0;
		$tot_perso_anne2 = 0;
		$tot_perso_anne3 = 0;
		$tot_perso_anne1 += $fetch_actions4['LIVRA_ANNE1'];
		$tot_perso_anne2 += $fetch_actions4['LIVRA_ANNE2'];
		$tot_perso_anne3 += $fetch_actions4['LIVRA_ANNE3'];

		$sub_array2[] = $tot_perso_anne1;
		$sub_array2[] = $tot_perso_anne2;
		$sub_array2[] = $tot_perso_anne3;
		$data[] = $sub_array2;

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