<?php
/*
*Eric SINDAYIGAYA
*Titre: Repartition des projets par ministere
*Numero de telephone: +257 62 04 03 00
*WhatsApp: +257 62 04 03 00
*Email pro: -
*Email pers: ericjamesbarinako33@gmail.com
*Date: 29 nov 2023
*/

namespace  App\Modules\pip\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Repartition_projet extends BaseController
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

	// Affichage de la liste des projets
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
		return view('App\Modules\pip\Views\Repartition_projet_list_view',$data); 
	}

	function list_projet()
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
		$order_column = array(1, 'NOM_PROJET', 'DESCRIPTION_INSTITUTION',1,1,1,1,1,1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_DEMANDE_INFO_SUPP DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (NOM_PROJET LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		
		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebase= "SELECT DISTINCT projet.ID_DEMANDE_INFO_SUPP, projet.NOM_PROJET,instit.DESCRIPTION_INSTITUTION, projet.ID_STATUT_PROJET FROM pip_demande_infos_supp projet JOIN inst_institutions instit JOIN user_users user ON instit.INSTITUTION_ID = projet.INSTITUTION_ID AND user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE 1 ".$user_connecte."";
		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$requetedebase1= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_ENCO_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE user.USER_ID = $user_id AND projet.ID_STATUT_PROJET = 1"; //Projets en cours
		$requetedebase2= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_PREP_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE user.USER_ID = $user_id AND projet.ID_STATUT_PROJET = 2"; //Projets en preparation
		$requetedebase3= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_APPR_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE user.USER_ID = $user_id AND projet.ID_STATUT_PROJET = 3"; //Projets approuve/Nouveau
		$requetedebase4= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_IDEE_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE user.USER_ID = $user_id AND projet.ID_STATUT_PROJET = 4"; //Projets en idee du projet
		$requetedebase5= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_TERM_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE user.USER_ID = $user_id AND projet.ID_STATUT_PROJET = 5"; //Termine
		$requetedebase6= "SELECT COUNT(ID_DEMANDE_INFO_SUPP) AS TOT_PAR_MIN FROM pip_demande_infos_supp projet  JOIN user_users user ON  user.INSTITUTION_ID = projet.INSTITUTION_ID WHERE 1 ".$user_connecte.""; //Totalite des projets par ministere
		
		$requetedebase7= "SELECT COUNT(NOM_PROJET) AS PROJET_TOT FROM pip_demande_infos_supp projet"; //Projets en totalite
		
		$query_secondaire1 = "CALL `getTable`('" . $requetedebase1 . "');";
		$fetch_actions1 = $this->ModelPs->getRequeteOne($query_secondaire1);
		$query_secondaire2 = "CALL `getTable`('" . $requetedebase2 . "');";
		$fetch_actions2 = $this->ModelPs->getRequeteOne($query_secondaire2);
		$query_secondaire3 = "CALL `getTable`('" . $requetedebase3 . "');";
		$fetch_actions3 = $this->ModelPs->getRequeteOne($query_secondaire3);
		$query_secondaire4 = "CALL `getTable`('" . $requetedebase4 . "');";
		$fetch_actions4 = $this->ModelPs->getRequeteOne($query_secondaire4);
		$query_secondaire5 = "CALL `getTable`('" . $requetedebase5 . "');";
		$fetch_actions5 = $this->ModelPs->getRequeteOne($query_secondaire5);
		$query_secondaire6 = "CALL `getTable`('" . $requetedebase6 . "');";
		$fetch_actions6 = $this->ModelPs->getRequeteOne($query_secondaire6);
		$query_secondaire7 = "CALL `getTable`('" . $requetedebase7 . "');";
		$fetch_actions7 = $this->ModelPs->getRequeteOne($query_secondaire7);
		 
		$data = array();
		$sub_array = array();
		$sub_array[] = '';
		$sub_array[] = '';
		$sub_array[] = '';
		$sub_array[] = $fetch_actions1['TOT_ENCO_PAR_MIN'];
		$sub_array[] = $fetch_actions2['TOT_PREP_PAR_MIN'];
		$sub_array[] = $fetch_actions3['TOT_APPR_PAR_MIN'];
		$sub_array[] = $fetch_actions4['TOT_IDEE_PAR_MIN'];
		$sub_array[] = $fetch_actions5['TOT_TERM_PAR_MIN'];
		$sub_array[] = $fetch_actions6['TOT_PAR_MIN'];
		//Calcul du pourcentage
		$nbrTousProjet = !empty($fetch_actions7['PROJET_TOT'])?$fetch_actions7['PROJET_TOT']:1;
		$sub_array[] = number_format(($fetch_actions6['TOT_PAR_MIN'] * 100 / $nbrTousProjet), 2, ',', '');
		$data[] = $sub_array;

		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array1 = array();
			//Conversion en INT
			$enCours = intval($row->ID_STATUT_PROJET);
			$enPrepa = intval($row->ID_STATUT_PROJET);
			$nouAppr = intval($row->ID_STATUT_PROJET);
			$ideeProjet = intval($row->ID_STATUT_PROJET);
			$projetTerm = intval($row->ID_STATUT_PROJET);

			if($enCours == 1)
			{
				$cours = 1;
			}			
			else{
				$cours = '';
			}
			if($enPrepa == 2)
			{
				$prep = 1;
			}
			else
			{
				$prep = '';
			}
			if($nouAppr == 3)
			{
				$appr = 1;
			}else
			{
				$appr = '';
			}
			if($ideeProjet == 4)
			{
				$ideepro = 1;
			}
			else
			{
				$ideepro = '';
			}
			if($projetTerm == 5)
			{
				$term = 1;
			}
			else
			{
				$term = '';
			}
			// Totalisation horizontale des projets selon son statut
			$projet = array($cours,$prep,$appr,$ideepro,$term);
			$totHorizProjet = array_sum($projet);
			// Calcul du pourcentage selon les statut
			$nbrTousProjet = !empty($fetch_actions7['PROJET_TOT'])?$fetch_actions7['PROJET_TOT']:1;
			$pource = number_format(($totHorizProjet * 100 / $nbrTousProjet), 2, ',', '');
			$sub_array1[] = $u++;
			$sub_array1[] = $row->DESCRIPTION_INSTITUTION;
			$sub_array1[] = $row->NOM_PROJET;
			$sub_array1[] = $cours; 
			$sub_array1[] = $prep; 
			$sub_array1[] = $appr; 
			$sub_array1[] = $ideepro; 
			$sub_array1[] = $term;
			$sub_array1[] = $totHorizProjet;
			$sub_array1[] = $pource; 
			$data[] = $sub_array1;
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
}
?>