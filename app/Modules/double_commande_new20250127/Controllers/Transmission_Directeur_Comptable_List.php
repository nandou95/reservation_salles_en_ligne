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
    $data['paie_a_faire'] = $paiement['get_paie_afaire'];
    $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

		$data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_brb']=$data_titre['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$data_titre['get_bord_dc'];
    $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

    $data_menu=$this->getDataMenuReception();
    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    $validee = $this->count_validation_titre();
    $data['get_titre_valide'] = $validee['get_titre_valide'];
    $data['get_titre_termine'] = $validee['get_titre_termine'];

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

		$order_column = array(1,'NUMERO_TITRE_DECAISSEMNT','MONTANT_ORDONNANCEMENT','DESC_DEVISE_TYPE', 'DESCRIPTION_INSTITUTION',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_TITRE_DECAISSEMNT LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%")') : '';

		// Condition pour la requête principale
		$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;

		// Condition pour la requête de filtre
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

		$requetedebase = "SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.NUMERO_TITRE_DECAISSEMNT,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID  JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID = 21";

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
			$sub_array[] = $row->NUMERO_TITRE_DECAISSEMNT;
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