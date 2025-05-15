<?php

/**
 * controller pour retourner exporter les projet selon institution livrables indicateur de mesure
 * @author :derick@mediabox.bi
 * tel :77432485
 */

namespace App\Modules\pip\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\HTTP\Escaper;

class Pip_projet_par_ministere_libvrable_projet extends BaseController
{
	protected $library;
	protected $ModelPs;
	protected $session;
	protected $validation;

	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
	{
		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$Limit = str_replace("\'", "'", $Limit);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	public function index()
	{
		$session  = \Config\Services::session();
		
		$data = $this->urichk();
		$data['annees'] = $this->get_annee_pip();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		return view('App\Modules\pip\Views\Pip_projet_par_ministere_libvrable_projet_list_view', $data);
	}

	// fonction pour faire la liste des projet par ministere
	public function listing_project_livrable()
	{
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = $session->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		$ID_INSTITUTION = $session->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$critere1 = "";
		$query_principal = "SELECT infosup.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,infosup.ID_DEMANDE_INFO_SUPP,mesureind.INDICATEUR_MESURE,unite_mesure.UNITE_MESURE FROM pip_demande_infos_supp infosup JOIN inst_institutions ON infosup.INSTITUTION_ID = inst_institutions.INSTITUTION_ID JOIN pip_cadre_mesure_resultat_livrable liv ON liv.ID_DEMANDE_INFO_SUPP = infosup.ID_DEMANDE_INFO_SUPP JOIN pip_indicateur_mesure mesureind ON liv.ID_INDICATEUR_MESURE = mesureind.ID_INDICATEUR_MESURE JOIN unite_mesure ON unite_mesure.ID_UNITE_MESURE = liv.ID_UNITE_MESURE WHERE 1 AND IS_FINISHED = 1";
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';
		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = '';
		$order_column = array(1, 'infosup.NOM_PROJET','inst_institutions.DESCRIPTION_INSTITUTION', 1, 'unite_mesure.UNITE_MESURE', 1, 1, 1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION DESC';

		$search = !empty($_POST['search']['value']) ?  (" AND (inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

		$critaire = $critere1;
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;

		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;

		foreach ($fetch_cov_frais as $info) 
		{
			$post = array();
			$post[] = $u++;
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table = "pip_demande_livrable";
			$columnselect = "DESCR_LIVRABLE";
			$where = "ID_DEMANDE_INFO_SUPP=" . $info->ID_DEMANDE_INFO_SUPP;
			$orderby = 'ID_DEMANDE_INFO_SUPP ASC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34 = str_replace("\'", "'", $bindparamss);
			$livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			$live = array();

			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table = " cadre_mesure_resultat_valeur_cible join  pip_cadre_mesure_resultat_livrable on pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE";
			$columnselect = "ANNEE_BUDGETAIRE_ID,VALEUR_ANNEE_CIBLE";
			$where = "ID_DEMANDE_INFO_SUPP=" . $info->ID_DEMANDE_INFO_SUPP;
			$orderby = 'ID_DEMANDE_INFO_SUPP ASC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34 = str_replace("\'", "'", $bindparamss);
			$valeur_cible = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			$anne1 = 0;
			$anne2 = 0;
			$anne3 = 0;

			if (isset($valeur_cible[0])) {
				$anne1 = $valeur_cible[0]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeur_cible[1])) {
				$anne2 = $valeur_cible[1]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeur_cible[2])) {
				$anne3 = $valeur_cible[2]->VALEUR_ANNEE_CIBLE;
			}

			if (strlen($info->DESCRIPTION_INSTITUTION) > 6) 
			{
				$post[] = mb_substr($info->DESCRIPTION_INSTITUTION, 0,5) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$info->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
			}
			 else
			 {
				$post[] ='<font color="#000000" ><label>'.$info->DESCRIPTION_INSTITUTION.'</label></font>';
			}
			if (strlen($info->NOM_PROJET) > 6) {
				$post[] = mb_substr($info->NOM_PROJET, 0, 5) . '...<a class="btn-sm" data-toggle="tooltip" title="' . $info->NOM_PROJET . '"><i class="fa fa-eye"></i></a>';
			} else {
				$post[] = '<font color="#000000" ><label>' . $info->NOM_PROJET . '</label></font>';
			}

			foreach ($livrable as $liv)
			{
				$live[] = '-' . $liv->DESCR_LIVRABLE . "</br>";				
			}
			$lives = implode("", $live);
			$post[] = !empty($lives) ? $lives : 'N/A';
			$post[] = !empty($info->INDICATEUR_MESURE) ? $info->INDICATEUR_MESURE : 'N/A';
			$post[] = !empty($info->UNITE_MESURE) ? $info->UNITE_MESURE : 'N/A';
			$post[] = number_format($anne1, '0', ',', ' ');
			$post[] = number_format($anne2, '0', ',', ' ');
			$post[] = number_format($anne3, '0', ',', ' ');
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	public function exporter()
	{
		$session  = \Config\Services::session();
		$USER_IDD = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_IDD)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$getRequete = "SELECT infosup.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,infosup.ID_DEMANDE_INFO_SUPP,mesureind.INDICATEUR_MESURE,unite_mesure.UNITE_MESURE FROM pip_demande_infos_supp infosup JOIN inst_institutions ON infosup.INSTITUTION_ID = inst_institutions.INSTITUTION_ID JOIN pip_cadre_mesure_resultat_livrable liv ON liv.ID_DEMANDE_INFO_SUPP = infosup.ID_DEMANDE_INFO_SUPP JOIN pip_indicateur_mesure mesureind ON liv.ID_INDICATEUR_MESURE = mesureind.ID_INDICATEUR_MESURE JOIN unite_mesure ON unite_mesure.ID_UNITE_MESURE = liv.ID_UNITE_MESURE WHERE 1 AND IS_FINISHED = 1 ";
		$getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', lang('messages_lang.th_projet'));
		$sheet->setCellValue('B1', lang('messages_lang.th_instit'));
		$sheet->setCellValue('C1', lang('messages_lang.pip_rapport_institutio_livrable'));
		$sheet->setCellValue('D1', lang('messages_lang.indicateur_mesure'));
		$sheet->setCellValue('E1', lang('messages_lang.unite_mesure'));
		$sheet->setCellValue('F1', '2024-2025');
		$sheet->setCellValue('G1', '2025-2026');
		$sheet->setCellValue('H1', '2026-2027');
		$rows = 3;
		//boucle pour les institutions 
		foreach ($getData as $key)
		{
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table = "pip_demande_livrable";
			$columnselect = "DESCR_LIVRABLE";
			$where = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
			$orderby = 'ID_DEMANDE_INFO_SUPP ASC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34 = str_replace("\'", "'", $bindparamss);
			$livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			$live = array();

			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table = " cadre_mesure_resultat_valeur_cible join  pip_cadre_mesure_resultat_livrable on pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE";
			$columnselect = "ANNEE_BUDGETAIRE_ID,VALEUR_ANNEE_CIBLE";
			$where = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
			$orderby = 'ID_DEMANDE_INFO_SUPP ASC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34 = str_replace("\'", "'", $bindparamss);
			$valeur_cible = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			$anne1 = 0;
			$anne2 = 0;
			$anne3 = 0;

			if (isset($valeur_cible[0])) 
			{
				$anne1 = $valeur_cible[0]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeur_cible[1])) 
			{
				$anne2 = $valeur_cible[1]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeur_cible[2])) 
			{
				$anne3 = $valeur_cible[2]->VALEUR_ANNEE_CIBLE;
			}
			foreach ($livrable as $liv) 
			{
				$live[] = $liv->DESCR_LIVRABLE . ' ' . ',';
			}
			$lives = implode("", $live);
			$sheet->setCellValue('A' . $rows, $key->NOM_PROJET);
			$sheet->setCellValue('B' . $rows, $key->DESCRIPTION_INSTITUTION);
			$sheet->setCellValue('C' . $rows, $lives);
			$sheet->setCellValue('D' . $rows, $key->INDICATEUR_MESURE);
			$sheet->setCellValue('E' . $rows, $key->UNITE_MESURE);
			$sheet->setCellValue('F' . $rows, "" . number_format($anne1, 0, '.', ' ') . "");
			$sheet->setCellValue('G' . $rows, "" . number_format($anne2, 0, '.', ' ') . "");
			$sheet->setCellValue('H' . $rows, "" . number_format($anne3, 0, '.', ' ') . "");
			$rows++;
		}
		$writer = new Xlsx($spreadsheet);
		$writer->save('world.xlsx');
		return $this->response->download('world.xlsx', null)->setFileName('PIP par Ministère et institution.xlsx');
	}
}
?>