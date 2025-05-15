<?php 

/**
 *
 * auteur:Jemapess
 * tache:   list pour la transmission du bordereau
 * date: le 15/15/2023
 * email:douce@mediabox.bi
 */
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class List_Bordereau_Deja_Transmsis extends BaseController
{
	
	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}
	//appel du view 
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

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $annee = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','1','ANNEE_BUDGETAIRE_ID ASC');
    $data['annee'] = $this->ModelPs->getRequete($callpsreq, $annee);
    $data['annee_encours']=$this->get_annee_budgetaire();

		$data['title']= lang('messages_lang.labelle_et_titre_transmission_deja');
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

		return view('App\Modules\double_commande_new\Views\List_Bordereau_Deja_Transmsis_view',$data);
	}

	//listing
	public function listing($value = 0)
	{
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

    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');

    $critere1="";
    $critere2="";
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=' AND td.DATE_ELABORATION_TD >= "'.$DATE_DEBUT.'"';
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere2.=' AND td.DATE_ELABORATION_TD >= "'.$DATE_DEBUT.'" AND td.DATE_ELABORATION_TD <= "'.$DATE_FIN.'"';
    }

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = array(1,'NUMERO_DOCUMENT','ebbtn.NUMERO_BORDEREAU_TRANSMISSION','DESC_DEVISE_TYPE','MONTANT_PAIEMENT','DESCRIPTION_INSTITUTION');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_DOCUMENT LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR ebbtn.NUMERO_BORDEREAU_TRANSMISSION LIKE "%' . $var_search . '%")') : '';
		
		$critaire = $critere1.' '.$critere2;
		// $critaire = "";
		// Condition pour la requête principale
		$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;

		// Condition pour la requête de filtre
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

		$requetedebase = "SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_DETAIL_ID),bon.NUMERO_DOCUMENT,dev.DESC_DEVISE_TYPE,td.MONTANT_PAIEMENT,inst.DESCRIPTION_INSTITUTION,ebbtn.NUMERO_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon.TYPE_DOCUMENT_ID=2 AND bon.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ";

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$date="date";
		$select="select";
		$text="text";
		$u = 1;
		$stat ='';
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->NUMERO_DOCUMENT;
			$sub_array[] = $row->NUMERO_BORDEREAU_TRANSMISSION;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $row->MONTANT_PAIEMENT;
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

	// Exporter la liste excel Liste des titres de décaissement déjà faits
  function exporter_Excel($DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id=$session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE');
    if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
  
    $critere1=" ";
    $critere2=" ";

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere1.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere2.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
    }

    $requetedebase="SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_DETAIL_ID),bon.NUMERO_DOCUMENT,dev.DESC_DEVISE_TYPE,td.MONTANT_PAIEMENT,inst.DESCRIPTION_INSTITUTION,ebbtn.NUMERO_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon.TYPE_DOCUMENT_ID=2 AND bon.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1.$critere2;

    $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C1', 'LISTE DES TITRES DE DECAISSEMENT DEJA FAITS');
    $sheet->setCellValue('A3', '#');
    $sheet->setCellValue('B3', 'NUMERO DU TD');
    $sheet->setCellValue('C3', 'NUMERO DE BORDEREAU');
    $sheet->setCellValue('D3', 'DEVISE');
    $sheet->setCellValue('E3', 'MONTANT DECAISSE');
    $sheet->setCellValue('F3', 'INSTITUTION');

    $rows = 4;
    $i=1;
    foreach ($getData as $key)
    {
      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_DOCUMENT);
      $sheet->setCellValue('C' . $rows, $key->NUMERO_BORDEREAU_TRANSMISSION);
      $sheet->setCellValue('D' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('E' . $rows, $key->MONTANT_PAIEMENT);
      $sheet->setCellValue('F' . $rows, $key->DESCRIPTION_INSTITUTION);
      
      $rows++;
      $i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('td_deja_faits'.$code.'.xlsx');

    return redirect('double_commande_new/List_Bordereau_Deja_Transmsis');
  }

	/**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilisE
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
   */
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
    // code...
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>
