<?php
/**Eric SINDAYIGAYA
 *Titre: Liste des bordereaux deja transmis a brb
 *Numero de telephone: +257 62 04 03 00
 *WhatsApp: +257 62 04 03 00
 *Email pro: sinda.eric@mediabox.bi
 *Email pers: ericjamesbarinako33@gmail.com
 *Date: 15 fev 2024
 **/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');
class Liste_transmission_bordereau_deja_transmis_brb extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function index()
	{
		$data = $this->urichk();
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

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

		return view('App\Modules\double_commande_new\Views\Liste_transmission_bordereau_deja_transmis_brb_view.php',$data); 
	}

	/**
	 * fonction pour lister les bordereaux a transmettre
	*/
	public function listing()
	{
		
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = "";
		$order_column = array('NUMERO_DOCUMENT','MONTANT_PAIEMENT','DESCRIPTION_INSTITUTION',1);
		$search = !empty($_POST['search']['value']) ? (' AND (bon_titre.NUMERO_DOCUMENT LIKE "%' . $var_search . '%" OR det.MONTANT_PAIEMENT LIKE "%' . $var_search . '%" OR inst.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%")') : '';	
		
		$query_principal="SELECT DISTINCT bon_titre.NUMERO_DOCUMENT,dev.DESC_DEVISE_TYPE,det.MONTANT_PAIEMENT,inst.DESCRIPTION_INSTITUTION,det.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon_titre.TYPE_DOCUMENT_ID = 2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID = 1";

		//condition pour le query principale
		$conditions = $search.' '.$order_by.' '.$limit;
		  // condition pour le query filter
		$conditionsfilter = $search;
		$requetedebase=$query_principal.$conditions;
		$requetedebasefilter=$query_principal.$conditionsfilter;
		
		$query_secondaire = "CALL `getTable`('".$requetedebase."');";
		  // echo $query_secondaire;
		$fetch_intrants = $this->ModelPs->datatable($query_secondaire);
		$u = 0;
		$data = array();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		
		foreach ($fetch_intrants as $row)
		{
			$u++;
			$sub_array = array();
			$sub_array[] = $row->NUMERO_DOCUMENT;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = !empty($row->MONTANT_PAIEMENT) ? number_format($row->MONTANT_PAIEMENT,'2',',',' '):0;
			$sub_array[] = $row->DESCRIPTION_INSTITUTION;		
			$data[] = $sub_array;
		}
		
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $query_principal . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count( $recordsTotal),
			"recordsFiltered" => count( $recordsFiltered),
			"data" => $data,
		);
		echo json_encode($output);
	}	
}
?>