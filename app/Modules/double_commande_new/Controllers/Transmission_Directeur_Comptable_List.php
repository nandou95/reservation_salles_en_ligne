<?php 

/**
 * auteur:Jemapess
 * tache: list des titres de decaissment  a transmettre
 * date: le 15/15/2023
 * email:jemapess.mugisha@mediabox.bi
 */
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Transmission_Directeur_Comptable_List extends BaseController
{
	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();

		$db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
	}

	//appel du view de la liste des actions
	function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$data['title']= lang('messages_lang.labelle_et_titre_transmission');
		$paiement = $this->count_paiement();
		$data['get_recep_obr'] = $paiement['get_recep_obr'];
		$data['get_prise_charge'] = $paiement['get_prise_charge'];
		$data['get_etab_titre'] = $paiement['get_etab_titre'];
		$data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
		$data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
		$data['get_sign_ministre'] = $paiement['get_sign_ministre'];

		$data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
		$data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
		$data['get_etape_corr'] = $paiement['get_etape_corr'];
		$data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
		$data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
		$data['get_bord_brb']=$paiement['get_bord_brb'];
		$data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
		$data['get_bord_dc']=$paiement['get_bord_dc'];
		$data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
		$data['recep_prise_charge']=$paiement['recep_prise_charge'];
		$data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
		$data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
		$data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
		$data['get_titre_valide'] = $paiement['get_titre_valide'];
		$data['get_titre_termine'] = $paiement['get_titre_termine'];
		$data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
		
		$data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];
	

		return view('App\Modules\double_commande_new\Views\Transmission_Directeur_Comptable_List_View',$data);
	}

	public function listing($value = 0)
	{
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';

		$group= ' ';

		$requetedebase = "SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID), det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.TITRE_DECAISSEMENT,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON exec.DEVISE_TYPE_ID=dev.DEVISE_TYPE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=21 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

		$order_column = array(1,'TITRE_DECAISSEMENT','DESC_DEVISE_TYPE','MONTANT_ORDONNANCEMENT','DESCRIPTION_INSTITUTION');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (TITRE_DECAISSEMENT LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESC_DEVISE_TYPE LIKE "%' . $var_search . '%")') : '';

		// Condition pour la requête principale
		$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;

		// Condition pour la requête de filtre
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();

		$date="date";
		$select="select";
		$text="text";
		$u = 1;
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->TITRE_DECAISSEMENT;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			if ($row->DEVISE_TYPE_ID==1) {
				$sub_array[] = $row->MONTANT_ORDONNANCEMENT;
			}else{
				$sub_array[] = $row->MONTANT_ORDONNANCEMENT_DEVISE;
			}
						
			$sub_array[] = $row->DESCRIPTION_INSTITUTION;
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
	
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
    // code...
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>