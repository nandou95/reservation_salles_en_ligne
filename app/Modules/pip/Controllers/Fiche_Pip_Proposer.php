<?php
/*
* @author MUNEZERO Sonia
* +25765165772
* sonia@mediabox.bi
* 4/01/2024
* Liste des fiches des pip proposer
*/

namespace App\Modules\pip\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
ini_set('max_execution_time', 0);
ini_set('memory_limit', '12048M');

class Fiche_Pip_Proposer extends BaseController
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

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}

	function liste_pip_proposer()
	{
		$session  = \Config\Services::session();

		$data = $this->urichk();
		$psgetrequete = "CALL getRequete(?,?,?,?);";

		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");

		if (empty($USER_ID)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$compilerMenu = $this->pip_compile();
		$data['compilation'] = $compilerMenu['compilation'];
		$data['pip_proposer'] = $compilerMenu['pip_proposer'];
		$data['pip_corriger'] = $compilerMenu['pip_corriger'];
		$data['pip_valider'] = $compilerMenu['pip_valider'];

		return view('App\Modules\pip\Views\Fiche_Pip_Proposer_List_View', $data);
	}

	function liste_projet_proposer()
	{
		$session  = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");

		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$critere1 = "";

		$query_principal = "SELECT ID_DOC_COMPILATION,PATH_DOC_COMPILER,proc_etape.DESCR_ETAPE,CODE_PIP,DATE_COMPILATION,STATUT FROM pip_document_compilation fiche JOIN proc_etape ON proc_etape.ETAPE_ID=fiche.ETAPE_ID WHERE STATUT=0";

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = '';
		$order_column = array(1, 'CODE_PIP', 'PATH_DOC_COMPILER', 'proc_etape.DESCR_ETAPE', 'DATE_COMPILATION', 1);

		$order_by = isset($_POST['order']) ? 'ORDER BY ' . $order_column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] : 'ORDER BY ID_DOC_COMPILATION ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (CODE_PIP LIKE '%$var_search%' OR PATH_DOC_COMPILER LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR DATE_COMPILATION LIKE '%$var_search%')") : '';

		$query_secondaire = $query_principal . ' ' . $search . ' ' . $order_by . ' ' . $limit;

		$query_filter = $query_principal . ' ' . $search;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;

		foreach ($fetch_cov_frais as $info) 
		{
			$nbre_proj = 'SELECT COUNT(ID_PRO_COMPILER) as nbre FROM pip_projet_compiler WHERE 1 AND ID_DOC_COMPILATION = ' . $info->ID_DOC_COMPILATION;
			$nbre_proj = "CALL `getTable`('" . $nbre_proj . "');";
			$get_nbre_proj = $this->ModelPs->getRequeteOne($nbre_proj);

			$projets = '<center><a onclick="get_pip_proposer(' . $info->ID_DOC_COMPILATION . ')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">' . $get_nbre_proj['nbre'] . '</b></button></a></center>';

			$documa = (!empty($info->PATH_DOC_COMPILER)) ? '<a onclick="get_doc(' . $info->ID_DOC_COMPILATION . ')" href="javascript:;"><span style="font-size: 30px;color:red;" class="fa fa-file-pdf"></span></a>' : 'N/A';

			$DESCR_ETAPE = '';
			if(strlen($info->DESCR_ETAPE) > 4) 
			{
				$DESCR_ETAPE =  mb_substr($info->DESCR_ETAPE, 0, 3) . '...<a class="btn-sm" title="' . $info->DESCR_ETAPE . '"><i class="fa fa-eye"></i></a>';
			}
			else
			{
				$DESCR_ETAPE =  !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
			}

			$DATE_COMPILATION = date('d/m/Y H:i', strtotime($info->DATE_COMPILATION));

			$post = array();
			$post[] = $u++;
			$post[] = !empty($info->CODE_PIP) ? $info->CODE_PIP : 'N/A';
			$post[] = $documa;
			$post[] = $DESCR_ETAPE;
			$post[] = $DATE_COMPILATION;
			$detail = lang('messages_lang.detail');
			$action = "<a class='btn btn-primary btn-sm' title='{$detail}' href='" . base_url("pip/Processus_Investissement_Public/detail_compiler/" . $info->ID_DOC_COMPILATION) . "' ><i class='fa fa-list'></a>";
			$post[] = $action;
			$data[] = $post;
		}

		$requeteqp='CALL `getList`("' . $query_principal . '")';
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

	function get_path_pip($id = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		
		$user_id = '';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}
		else
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$getpath = $this->getBindParms('ID_DOC_COMPILATION,PATH_DOC_COMPILER', 'pip_document_compilation', ' ID_DOC_COMPILATION = ' . $id . ' ', 'ID_DOC_COMPILATION  ASC');
		$path_pip = $this->ModelPs->getRequeteOne($callpsreq, $getpath);

		$html = "<embed  src='" . base_url("uploads/doc_compiler/" . $path_pip['PATH_DOC_COMPILER']) . "' scrolling='auto' height='500px' width='100%' frameborder='0'>";

		$output = array("documentPIP" => $html);
		return $this->response->setJSON($output);
	}

	function projet_propose($id)
	{
		$session  = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");

		if(empty($USER_ID)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$critere1 = "";
		$query_principal = "SELECT ID_PRO_COMPILER,supp.NOM_PROJET,supp.NUMERO_PROJET FROM pip_projet_compiler comp JOIN pip_demande_infos_supp supp ON comp.ID_DEMANDE=supp.ID_DEMANDE WHERE ID_DOC_COMPILATION = " . $id;

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array(1, 'NUMERO_PROJET', 'NOM_PROJET');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_PRO_COMPILER ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_PROJET LIKE '%$var_search%' OR NOM_PROJET LIKE '%$var_search%')") : '';

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
			$post[] = !empty($info->NUMERO_PROJET) ? $info->NUMERO_PROJET : 'N/A';
			$post[] = !empty($info->NOM_PROJET) ? $info->NOM_PROJET : 'N/A';
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
}
?>