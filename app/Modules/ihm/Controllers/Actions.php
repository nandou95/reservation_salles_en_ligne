<?php

/**NIYONAHABONYE Pascal
 *Titre:liste des institutions actions
 *Numero de telephone: (+257) 68 045 482
 *WhatsApp: (+257) 77531083
 *Email: pascal@mediabox.bi
 *Date: 29 Août,2023
 **/
/**
 * Ameliore Par
 * Baleke kahamire Bonheur
 * Numero: (+257)67866283
 * mail: bonheur.baleke@mediabox.bi
 * 30.01.2024
 */

namespace  App\Modules\ihm\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;

class Actions extends BaseController
{
	public $library;
	public $ModelPs;
	public $session;
	public $validation;

	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function index($value = '')
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		  {
		return redirect('Login_Ptba/do_logout');
		  }
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		  {
		 return redirect('Login_Ptba/homepage');
		  }
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$processus = $this->getBindParms('NOM_PROCESS,PROCESS_ID','proc_process','1','NOM_PROCESS ASC');
		$data['processus'] = $this->ModelPs->getRequete($psgetrequete,$processus);
		$PROCESS_ID = $this->request->getPost('PROCESS_ID');
		$ETAPE_ID  = $this->request->getPost('ETAPE_ID ');
		$ACTION_ID  = $this->request->getPost('ACTION_ID ');

		if(!empty($PROCESS_ID)){
			$etape = $this->getBindParms('ETAPE_ID,DESCR_ETAPE','proc_etape','PROCESS_ID='.$PROCESS_ID,'DESCR_ETAPE DESC');
			$data['etape'] = $this->ModelPs->getRequete($psgetrequete,$etape);
		}
		if(!empty($ETAPE_ID)){
			$action = $this->getBindParms('ACTION_ID,DESCR_ACTION','proc_actions','ETAPE_ID='.$ETAPE_ID,'DESCR_ACTION DESC');
			$data['action'] = $this->ModelPs->getRequete($psgetrequete,$action);
		}else{
			$etape = [];
			$action = [];
		}

		$data['etape'] = $etape;
		$data['action'] = $action;
		$data['PROCESS_ID'] = $PROCESS_ID;
		return view('App\Modules\ihm\Views\Actions_View', $data);
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$PROCESS_ID = $this->request->getPost('PROCESS_ID');
		$ETAPE_ID = $this->request->getPost('ETAPE_ID');
		$ACTION_ID = $this->request->getPost('ACTION_ID');
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'",$var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array(1,'NOM_PROCESS','etape_actuel','DESCR_ACTION','suivante',1,1,1,'CL_CMR_COSTAB_CATEGORY',1,1,'LINK_FORM',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NOM_PROCESS ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NOM_PROCESS LIKE "%' . $var_search . '%" OR proc_etape.DESCR_ETAPE LIKE "%' . $var_search . '%" OR proc_actions.DESCR_ACTION LIKE "%' . $var_search . '%" OR etape_suivante.DESCR_ETAPE LIKE "%' . $var_search . '%"
			OR proc_actions.DELETED LIKE "%' . $var_search . '%"  OR costa.CL_CMR_COSTAB_CATEGORY LIKE "%' . $var_search . '%" OR IS_INITIAL LIKE "%' . $var_search . '%"OR GET_FORM LIKE "%' . $var_search . '%" OR LINK_FORM LIKE "%' . $var_search . '%")') : '';

		if(!empty($PROCESS_ID)){
			if($PROCESS_ID > 0){
				$critere = ' AND proce.PROCESS_ID='.$PROCESS_ID;
			}
		}

		if(!empty($ETAPE_ID)){
			if($ETAPE_ID > 0){
				$critere .= ' AND proc_etape.ETAPE_ID='.$ETAPE_ID;
			}
		}

		if(!empty($ACTION_ID)){
			if($ACTION_ID > 0){
				$critere .= ' AND proc_actions.ACTION_ID='.$ACTION_ID;
			}
		}

		// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by. ' '.$limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critere.' '.$search.' '.$group;

		$requetedebase = "SELECT proc_actions.ACTION_ID,proc_actions.DESCR_ACTION,proc_actions.LINK_FORM,proce.NOM_PROCESS,proc_etape.DESCR_ETAPE AS etape_actuel,etape_suivante.DESCR_ETAPE AS suivante,proc_actions.DELETED,costa.CL_CMR_COSTAB_CATEGORY,proc_actions.IS_INITIAL,proc_actions.GET_FORM FROM proc_actions JOIN proc_etape ON proc_etape.ETAPE_ID=proc_actions.ETAPE_ID JOIN proc_process proce ON proce.PROCESS_ID=proc_etape.PROCESS_ID LEFT JOIN cl_cmr_costab_categorie costa ON costa.ID_CL_CMR_COSTAB_CATEGORIE=proc_actions.ID_CL_CMR_COSTAB_CATEGORIE JOIN proc_etape etape_suivante ON etape_suivante.ETAPE_ID=proc_actions.MOVETO WHERE 1";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$stat = '';
		$action = '';
		foreach ($fetch_actions as $row) {
			//GET DOCUMENT FOR THAT ACTION
			$requetedebase2 = 'SELECT COUNT(DOCUMENT_ID) AS NBRDOC FROM proc_action_document  WHERE ACTION_ID='.$row->ACTION_ID;
			$query_secondaire2 = "CALL `getTable`('".$requetedebase2."');";
			$fetch_nbrDoc = $this->ModelPs->getRequeteOne($query_secondaire2);

			//GET INFORMATIONS SUPPL FOR THAT ACTION
			$requetedebase3 = 'SELECT COUNT(ID_INFOS_SUPP) AS NBRINFO FROM proc_action_infos_supp  WHERE ACTION_ID='.$row->ACTION_ID;

			$query_secondaire3 = "CALL `getTable`('".$requetedebase3."');";
			$fetch_nbrInfo = $this->ModelPs->getRequeteOne($query_secondaire3);

			$sub_array = array();
			//    $sub_array[] = $u++;
			$sub_array[] = $row->NOM_PROCESS;
			$sub_array[] = $row->etape_actuel;
			$sub_array[] = $row->DESCR_ACTION;
			$sub_array[] = $row->suivante;

			if ($row->DELETED == 1) 
			{
				$statut = '<center><span class="fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'.lang('messages_lang.desactive_action').'">&nbsp;</span></center>';
			}
			else
			{
				$statut = '<center><span class="fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'.lang('messages_lang.active_action').'">&nbsp;</span></center>';
			}
			$sub_array[] = '<b style="width: 80px;height: 50px;border-radius:90px;">'.$statut.'</b>';
			$requetedebase5 = 'SELECT doc.DOCUMENT_ID,DESC_DOCUMENT FROM proc_document doc JOIN proc_action_document act_doc ON act_doc.DOCUMENT_ID=doc.DOCUMENT_ID WHERE act_doc.ACTION_ID='.$row->ACTION_ID;
			$query_secondaire5 = "CALL `getTable`('".$requetedebase5."');";
			$fetch_Documents[] = $this->ModelPs->datatable($query_secondaire5);

			$sub_array[] = "
			<a href='javascript:void(0)' onclick='show_modal_get_doc(".$row->ACTION_ID.")' title='".$fetch_nbrDoc['NBRDOC']."'>
				<button class='btn btn-primary'>".$fetch_nbrDoc['NBRDOC']."</button>
			</a>
			<div style='display:none;' id='header_get_document".$row->ACTION_ID."'>
				<center><h3>".lang('messages_lang.document_action')."</h3></center>
			</div>
			<div style='display:none;' id='message_get_document".$row->ACTION_ID."'>
				<center>
					<table id='document' class=' table table-striped table-bordered'>
						<thead>
							<tr>
								<th>#</th>
								<th> ".lang('messages_lang.document_action')." </th>
							</tr>
						</thead>
					</table>
				</center>
			</div>
			<div style='display:none;' id='footer_get_document".$row->ACTION_ID."'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
					".lang('messages_lang.quiter_action')."
				</button>
			</div>
			";

			$sub_array[] = "
			<a href='javascript:void(0)' onclick='show_modal_get_info(".$row->ACTION_ID.")' title='".$fetch_nbrDoc['NBRDOC']."'>
				<button class='btn btn-primary'>" . $fetch_nbrDoc['NBRDOC'] . "</button>
			</a>

			<div style='display:none;' id='header_get_info".$row->ACTION_ID."'>
				<center><h3>".lang('messages_lang.information_action')."</h3></center>
			</div>
			<div style='display:none;' id='message_get_info".$row->ACTION_ID."'>
				<center>
					<label> ".lang('messages_lang.les_information_action')." </label>
				</center>
			</div>
			<div style='display:none;' id='footer_get_info".$row->ACTION_ID."'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				".lang('messages_lang.quiter_action')."
				</button>
			</div>";

			$sub_array[] = $row->CL_CMR_COSTAB_CATEGORY;
			$etatInit = $row->IS_INITIAL;
			if ($etatInit == 1) {
				$sub_array[] = 'Oui';
			} else {
				$sub_array[] = 'Non';
			}
			$form = $row->GET_FORM;
			if ($form == 1) {
				$sub_array[] = 'Oui';
			} else {
				$sub_array[] = 'Non';
			}
			$sub_array[] = $row->LINK_FORM;

			if ($row->DELETED == 0)
			{
				$action = "
				<div class='dropdown' style='color:#fff;'>
					<a class='btn btn-primary btn-sm dropdown-toggle' data-toggle='dropdown'><i class='fa fa-cog'></i> Options  <span class='caret'></span></a>
					<ul class='dropdown-menu dropdown-menu-left'>
						<li> 
							<a href='".base_url('ihm/Actions/getOne/'.md5($row->ACTION_ID))."'> <label>&nbsp;&nbsp;".lang('messages_lang.modifier')."</label></a> </a>
						</li>
						<li>
							<a href='javascript:void(0)' onclick='show_modal(".$row->ACTION_ID.")' title='".lang('messages_lang.desactive_action')."' >
								<label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.desactive_action')."</font></label>
							</a>
						</li>

						<div style='display:none;' id='message".$row->ACTION_ID."'>
							<center>
								<h5><strong>".lang('messages_lang.confimatation_desactive_action') . "<br><center><font color='green'>".$row->ACTION_ID."&nbsp;&nbsp;".$row->DESCR_ACTION."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
								</h5>
							</center>
						</div>
						<div style='display:none;' id='footer".$row->ACTION_ID."'>
							<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
							".lang('messages_lang.quiter_action')."
							</button>
							<a href='".base_url("ihm/Actions/is_active/".$row->ACTION_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.desactive_action')."</a>
						</div>
					</ul>
				</div>";
			}
			else
			{
				$action = "
				<div class='dropdown' style='color:#fff;'>
					<a class='btn btn-primary btn-sm dropdown-toggle' data-toggle='dropdown'><i class='fa fa-cog'></i> Options  <span class='caret'></span></a>
					<ul class='dropdown-menu dropdown-menu-left'>
						<li>
							<a href='".base_url("ihm/Actions/getOne/".md5($row->ACTION_ID))."' >
								<label>&nbsp;&nbsp;".lang('messages_lang.modifier')."</label>
							</a>
						</li>
						<li>
				            <a href='javascript:void(0)' onclick='show_modal(\"" . $row->ACTION_ID . "\")' title='" . lang('messages_lang.desactive_action') . "'>
				                <label>&nbsp;&nbsp;<font color='red'>" . lang('messages_lang.desactive_action') . "</font></label>
				            </a>
				        </li>

						<div style='display:none;' id='message".$row->ACTION_ID."'>
							<center>
								<h5><strong>".lang('messages_lang.confimatation_active_action')."<br><center><font color='green'>&nbsp;&nbsp;".$row->DESCR_ACTION."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
								</h5>
							</center>
						</div>
						<div style='display:none;' id='footer".$row->ACTION_ID."'>
							<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
							".lang('messages_lang.quiter_action')."
							</button>
							<a href='".base_url("ihm/Actions/is_active/".$row->ACTION_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.active_action')."</a>
						</div>
					</ul>
				</div>";
			}
			$sub_array[] = $action;
			$data[] = $sub_array;
		}
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$requetedebase."')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$requetedebasefilter."')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output); //echo json_encode($output);
	}

	//liste des documents par action
	function getDocument($id)
	{		
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$user_id = '';
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$query_principal = 'SELECT DESC_DOCUMENT FROM proc_document doc JOIN proc_action_document act_doc ON act_doc.DOCUMENT_ID=doc.DOCUMENT_ID WHERE act_doc.ACTION_ID='.$id;
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = "LIMIT 0,10";
		if ($_POST['length'] != -1) {
			$limit = "LIMIT " . $_POST['start'] . "," . $_POST['length'];
		}
		$order_by = "";
		$order_column = "";
		$order_column = array(1, 'DESC_DOCUMENT');
		$order_by = isset($_POST['order']) ? " ORDER BY " . $order_column[$_POST['order']['0']['column']] . "  " . $_POST['order']['0']['dir'] : " ORDER BY DESC_DOCUMENT ASC";
		$search = !empty($_POST['search']['value']) ?  (' AND ( DESC_DOCUMENT LIKE "%' . $var_search . '%")') : "";
		$search = str_replace("'", "\'", $search);
		$critaire = " ";
		$query_secondaire = $query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;
		$query_filter = $query_principal." ".$search." ".$critaire;
		$requete = "CALL `getTable`('".$query_secondaire."')";
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;
		foreach ($fetch_cov_frais as $info) {
			$post = array();
			$post[] = $u++;
			$post[] = $info->DESC_DOCUMENT;
			$data[] = $post;
		}

		$requeteqp = "CALL `getTable`('".$query_principal."')";
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = "CALL `getTable`('".$query_filter."')";
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	//Filtre sur les etapes
	public function get_etape()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$user_id = '';
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}

		$html = '<option value="">Sélectionner</option>';
		$PROCESS_ID = $this->request->getPost('PROCESS_ID');
		if (!empty($PROCESS_ID)) {
			$etape = $this->getBindParms('ETAPE_ID,DESCR_ETAPE','proc_etape','PROCESS_ID='.$PROCESS_ID,'DESCR_ETAPE DESC');
			$get_etape = $this->ModelPs->getRequete($callpsreq,$etape);

			foreach ($get_etape as $key) {
				$html .="<option value='".$key->ETAPE_ID."'>".$key->DESCR_ETAPE."</option>";
			}
			$output = array('status' => TRUE, 'html' => $html);
			return $this->response->setJSON($output); //echo json_encode($output);
		}
	}

	//Filtre sur les actions
	public function get_action()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$user_id = '';
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}

		$html = '<option value="">Sélectionner</option>';
		$ETAPE_ID = $this->request->getPost('ETAPE_ID');
		if (!empty($ETAPE_ID)) {
			$action = $this->getBindParms('ACTION_ID,DESCR_ACTION','proc_actions','ETAPE_ID='.$ETAPE_ID,'DESCR_ACTION DESC');
			$get_action = $this->ModelPs->getRequete($callpsreq,$action);
			foreach ($get_action as $key) {
				$html .= "<option value='".$key->ACTION_ID."'>".$key->DESCR_ACTION."</option>";
			}
			$output = array('status' => TRUE, 'html' => $html);
			return $this->response->setJSON($output); //echo json_encode($output);
		}
	}

	//fonction pour l'activation/désactivation
	function is_active($ACTION_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_process = $this->getBindParms('ACTION_ID,ETAPE_ID,DESCR_ACTION,MOVETO,DELETED','proc_actions','ACTION_ID='.$ACTION_ID,'ACTION_ID ASC');
		$process = $this->ModelPs->getRequeteOne($callpsreq,$bind_process);

		if($process['DELETED'] == 0){
			$table = 'proc_actions';
			$conditions = 'ACTION_ID ='.$ACTION_ID;
			$datatomodifie = 'DELETED=1';
			$this->update_all_table($table,$datatomodifie,$conditions);
			$data = [
				'message' =>lang('messages_lang.labelle_et_mod_question_succes')
			];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Actions');
		}elseif($process['DELETED'] == 1){
			$table = 'proc_actions';
			$conditions = 'ACTION_ID ='.$ACTION_ID;
			$datatomodifie = 'DELETED=0';
			$this->update_all_table($table,$datatomodifie,$conditions);
			$data = [
				'message' =>lang('messages_lang.labelle_et_mod_question_succes_d')
			];
			session()->setFlashdata('alert',$data);
			return redirect('ihm/Actions');
		}
	}

	/*  insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $id = $result['id'];
	}
	//Update
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	//fonction pour aficher le formulaire de la modification
	public function getOne($id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();		
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_respon = 'SELECT ACTION_ID,ETAPE_ID,DESCR_ACTION,MOVETO FROM `proc_actions` WHERE md5(ACTION_ID)="'.$id.'" ORDER BY ACTION_ID ASC';
		$bind_respon = "CALL `getTable`('".$bind_respon."');";
		$data['actions'] = $this->ModelPs->getRequeteOne($bind_respon);
		$id_decrypt = $data['actions']['ACTION_ID'] ?? null;

		if ($id_decrypt === null) {
			return $this->index();
		}

		$bind_respona = $this->getBindParms('proc_actions.ACTION_ID ,ETAPE_ID,	DESCR_ACTION,DOCUMENT_ID,ID_INFOS_SUPP,MOVETO,IS_INITIAL,LINK_FORM,GET_FORM,IS_REQUIRED,ID_CL_CMR_COSTAB_CATEGORIE', 'proc_actions LEFT JOIN proc_action_document on proc_action_document.ACTION_ID=proc_actions.ACTION_ID LEFT JOIN proc_action_infos_supp ON proc_action_infos_supp.ACTION_ID=proc_actions.ACTION_ID','proc_actions.ACTION_ID='.$id_decrypt,'ACTION_ID ASC');
		$data['action'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_respona);

		$bind_documents = $this->getBindParms('ID_ACTION_DOC,ACTION_ID,DOCUMENT_ID,DOCUMENT_ID','proc_action_document','proc_action_document.ACTION_ID='.$id_decrypt,'ID_ACTION_DOC ASC');
		$data['docs'] = $this->ModelPs->getRequete($callpsreq,$bind_documents);

		$bind_document = $this->getBindParms('ID_ACTION_DOC,ACTION_ID,DOCUMENT_ID,DOCUMENT_ID','proc_action_document','proc_action_document.ACTION_ID='.$id_decrypt,'ID_ACTION_DOC ASC');
		$doc = $this->ModelPs->getRequete($callpsreq,$bind_document);

		$doc_array[] = '';
		foreach ($doc as $key) {
			$doc_array[] = $key->DOCUMENT_ID;
		}
		$data['exist'] = $doc_array;

		$bind_info = $this->getBindParms('ID_ACTION_INFOS_SUPP,ID_INFOS_SUPP,ACTION_ID','proc_action_infos_supp','proc_action_infos_supp.ACTION_ID='.$id_decrypt,'ID_ACTION_INFOS_SUPP ASC');
		$info = $this->ModelPs->getRequete($callpsreq,$bind_info);

		$infos_array[] = '';
		foreach ($info as $value) {
			$infos_array[] = $value->ID_INFOS_SUPP;
		}
		$data['info_exist'] = $infos_array;

		$etap = $this->getBindParms('`ETAPE_ID`,`DESCR_ETAPE`','proc_etape','DELETED=0','DESCR_ETAPE ASC');
		$data['etapes'] = $this->ModelPs->getRequete($callpsreq,$etap);

		$docs = $this->getBindParms('`DOCUMENT_ID`,`DESC_DOCUMENT`','proc_document','1','DESC_DOCUMENT ASC');
		$data['documents'] = $this->ModelPs->getRequete($callpsreq,$docs);

		$infos = $this->getBindParms('ID_INFOS_SUPP,DESCR_INFOS_SUPP','proc_infos_supp','1','DESCR_INFOS_SUPP ASC');
		$data['infos_suppl'] = $this->ModelPs->getRequete($callpsreq,$infos);

		$categorie = $this->getBindParms('`ID_CL_CMR_COSTAB_CATEGORIE`,`CL_CMR_COSTAB_CATEGORY`','cl_cmr_costab_categorie','1', 'ID_CL_CMR_COSTAB_CATEGORIE ASC');
		$data['categories'] = $this->ModelPs->getRequete($callpsreq,$categorie);

		$process = $this->getBindParms('PROCESS_ID,NOM_PROCESS','proc_process','1','PROCESS_ID ASC');
		$data['processus'] = $this->ModelPs->getRequeteOne($callpsreq,$process);

		$processus = $this->getBindParms('PROCESS_ID,NOM_PROCESS','proc_process','1','PROCESS_ID ASC');
		$data['process'] = $this->ModelPs->getRequete($callpsreq,$processus);

		$select = array(
			array('ID' => 1, 'DES' => 'Oui'),
			array('ID' => 2, 'DES' => 'Non'),
		);
		$data['select'] = $select;
		$data['title'] = lang('messages_lang.modifier_action');
		return view('App\Modules\ihm\Views\Actions_Update_View', $data);
	}

	//fonction pour la modification
	public function update()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DESCR_ACTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ETAPE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'MOVETO' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

			'PROCESS_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

			'IS_INITIAL' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ID_CL_CMR_COSTAB_CATEGORIE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'IS_REQUIRED' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'GET_FORM' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
		];

		if (isset($LINK_FORM) && $LINK_FORM === '1') {
			$rules = [
				'LINK_FORM' => [
					'label' => '',
					'rules' => 'required',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
					]
				]
			];
		}

		$ACTION_ID = $this->request->getPost('ACTION_ID');
		$PROCESS_ID = $this->request->getPost('PROCESS_ID');
		$ETAPE_ID = $this->request->getPost('ETAPE_ID');
		$DESCR_ACTION = $this->request->getPost('DESCR_ACTION');
		$MOVETO = $this->request->getPost('MOVETO');
		$DOCUMENT_ID = $this->request->getPost('DOCUMENT_ID[]');
		$ID_INFOS_SUPP = $this->request->getPost('ID_INFOS_SUPP[]');
		$LINK_FORM = $this->request->getPost('LINK_FORM');
		$ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost('ID_CL_CMR_COSTAB_CATEGORIE');
		$GET_FORM = $this->request->getPost('GET_FORM');
		$LINK_FORM = $this->request->getPost('LINK_FORM');
		$IS_REQUIRED = $this->request->getPost('IS_REQUIRED');
		$IS_INITIAL = $this->request->getPost('IS_INITIAL');

		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run()) 
		{
			$db = db_connect();
			$tableEtape = 'proc_etape';
			$where = 'ETAPE_ID=' . $ETAPE_ID;
			$dataUpdate = 'PROCESS_ID=' . $PROCESS_ID;
			$bindparams = [$tableEtape, $dataUpdate, $where];
			$insertRequete = 'CALL `updateData`(?,?,?)';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);

			if (!empty($ACTION_ID)) 
			{
				$updateTable = 'proc_actions';
				$critere = 'ACTION_ID ='.$ACTION_ID;
				$datatoupdate ='ETAPE_ID='.$ETAPE_ID.',DESCR_ACTION="'.$DESCR_ACTION.'",MOVETO="'.$MOVETO.'",IS_REQUIRED='.$IS_REQUIRED.',GET_FORM='.$GET_FORM.',LINK_FORM="'.$LINK_FORM.'",ID_CL_CMR_COSTAB_CATEGORIE='.$ID_CL_CMR_COSTAB_CATEGORIE.',IS_INITIAL='.$IS_INITIAL;
				$bindparams = [$updateTable,$datatoupdate,$critere];
				$insertRequete = 'CALL `updateData`(?,?,?)';
				$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
				$critere = "ACTION_ID =".$ACTION_ID;
				$table = 'proc_action_document';
				$deleteparams = [$db->escapeString($table), $db->escapeString($critere)];
				$deleteRequete = "CALL `deleteData`(?,?);";
				$delete = $this->ModelPs->createUpdateDelete($deleteRequete,$deleteparams);
				if (!empty($DOCUMENT_ID)) 
				{
					$count = count($DOCUMENT_ID);
					for ($i=0;$i<$count;$i++) 
					{
						$columns_doc = "ACTION_ID,DOCUMENT_ID";
						$data_doc = $ACTION_ID . "," . $DOCUMENT_ID[$i];
						$this->save_all_table($table, $columns_doc, $data_doc);
					}
				}

				$critere ="ACTION_ID =".$ACTION_ID;
				$table = 'proc_action_infos_supp';
				$deleteparams = [$db->escapeString($table), $db->escapeString($critere)];
				$deleteRequete = "CALL `deleteData`(?,?);";
				$delete = $this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
				if (!empty($ID_INFOS_SUPP)) 
				{
					$count = count($ID_INFOS_SUPP);
					for ($i=0;$i<$count;$i++)
					{
						$columns_doc = "ACTION_ID,ID_INFOS_SUPP";
						$data_doc = $ACTION_ID.",".$ID_INFOS_SUPP[$i];
						$this->save_all_table($table,$columns_doc,$data_doc);
					}
				}
			}

			$data = ['message' => lang('messages_lang.labelle_message_update_success')];
			session()->setFlashdata('alert', $data);
			$output = array('status' => TRUE);
			return redirect('ihm/Actions');
		} else {
			return $this->getOne($ACTION_ID);
		}
	}

	//appel du form d'enregistrement
	function new()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$processus = $this->getBindParms('PROCESS_ID,NOM_PROCESS','proc_process','1','PROCESS_ID ASC');
		$data['process'] = $this->ModelPs->getRequete($callpsreq,$processus);

		$PROCESS_ID = $this->request->getPost('PROCESS_ID');

		if (!empty($PROCESS_ID)) {
			$etape = $this->getBindParms('ETAPE_ID,DESCR_ETAPE','proc_etape','PROCESS_ID='.$PROCESS_ID,'DESCR_ETAPE DESC');
			$data['etape'] = $this->ModelPs->getRequete($callpsreq,$etape);
		} else {
			$etape = [];
		}

		$data['etape'] = $etape;

		$etap = $this->getBindParms('`ETAPE_ID`,`DESCR_ETAPE`','proc_etape','PROCESS_ID=DELETED=0','DESCR_ETAPE ASC');
		$data['etapes'] = $this->ModelPs->getRequete($callpsreq,$etap);

		$docs = $this->getBindParms('`DOCUMENT_ID`,`DESC_DOCUMENT`','proc_document','DOCUMENT_TYPE_ID=1','DESC_DOCUMENT ASC');
		$data['documents'] = $this->ModelPs->getRequete($callpsreq, $docs);
		$categorie = $this->getBindParms('`ID_CL_CMR_COSTAB_CATEGORIE`,`CL_CMR_COSTAB_CATEGORY`','cl_cmr_costab_categorie','1','ID_CL_CMR_COSTAB_CATEGORIE ASC');
		$data['categories'] = $this->ModelPs->getRequete($callpsreq, $categorie);


		$infos = $this->getBindParms('ID_INFOS_SUPP,DESCR_INFOS_SUPP','proc_infos_supp','1','DESCR_INFOS_SUPP ASC');
		$data['infos_suppl'] = $this->ModelPs->getRequete($callpsreq, $infos);
		$select = array(
			array('ID' => 1,'DES' => 'Oui'),
			array('ID' => 2,'DES' => 'Non'),
		);
		$data['select'] = $select;
		$data['title'] = lang('messages_lang.ajoute_action');
		return view('App\Modules\ihm\Views\Actions_New_View', $data);
	}

	//recuperation de l'etape suivante différente de l'étape sélectionnée
	function get_etape_suivante($ETAPE_ID, $PROCESS_ID) //
	{
		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();
		$user_id = '';
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$html = '<option value="">Sélectionner</option>';
		if (!empty($ETAPE_ID)) {
			$etape_suivante = $this->getBindParms('ETAPE_ID,DESCR_ETAPE','proc_etape','PROCESS_ID='.$PROCESS_ID.' AND DELETED=0 AND ETAPE_ID!='.$ETAPE_ID,'DESCR_ETAPE DESC');
			$get_etape_suivante = $this->ModelPs->getRequete($callpsreq,$etape_suivante);

			foreach ($get_etape_suivante as $key) {
				$html .= "<option value='".$key->ETAPE_ID."'>".$key->DESCR_ETAPE."</option>";
			}
			$output = array('status' => TRUE, 'html' => $html);
			return $this->response->setJSON($output);
		}
	}

	function save()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$db = db_connect();		
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DESCR_ACTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ETAPE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'MOVETO' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

			'PROCESS_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

			'IS_INITIAL' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ID_CL_CMR_COSTAB_CATEGORIE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'IS_REQUIRED' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

			'GET_FORM' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],

		];

		if (isset($LINK_FORM) && $LINK_FORM === '1') {
			$rules = [
				'LINK_FORM' => [
					'label' => '',
					'rules' => 'required',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
					]
				]
			];
		}

		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run()) {
			$DESCR_ACTION = $this->request->getPost('DESCR_ACTION');
			$ETAPE_ID = $this->request->getPost('ETAPE_ID');
			$MOVETO = $this->request->getPost('MOVETO');
			$ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost('ID_CL_CMR_COSTAB_CATEGORIE');
			$GET_FORM = $this->request->getPost('GET_FORM');
			$IS_REQUIRED = $this->request->getPost('IS_REQUIRED');
			$IS_INITIAL = $this->request->getPost('IS_INITIAL');
			$DOCUMENTS = $this->request->getPost('DOCUMENT_ID[]');
			$INFOS_SUPP = $this->request->getPost('ID_INFOS_SUPP[]');
			$LINK_FORM = $GET_FORM === '1' ? $this->request->getPost('LINK_FORM') : null;

			$DESCR_ACTION = str_replace("\n", "", $DESCR_ACTION);
			$DESCR_ACTION = str_replace("\r", "", $DESCR_ACTION);
			$DESCR_ACTION = str_replace("\t", "", $DESCR_ACTION);
			$DESCR_ACTION = str_replace('"', '', $DESCR_ACTION);
			$DESCR_ACTION = str_replace("'", '', $DESCR_ACTION);

			$DELETED = 0;
			$insertInto = 'proc_actions';
			$columns = "ETAPE_ID,DESCR_ACTION,MOVETO,DELETED,IS_REQUIRED,GET_FORM,LINK_FORM,ID_CL_CMR_COSTAB_CATEGORIE,IS_INITIAL";
			$datacolums = $ETAPE_ID.",'".$DESCR_ACTION."',".$MOVETO.",".$DELETED.",".$IS_REQUIRED.",'".$GET_FORM."','".$LINK_FORM."',".$ID_CL_CMR_COSTAB_CATEGORIE.",".$IS_INITIAL."";

			$id_action = $this->save_all_table($insertInto,$columns,$datacolums);

			if (!empty($DOCUMENTS)) {
				$table_doc = 'proc_action_document';
				foreach ($DOCUMENTS as $key => $value) {
					$columns_doc = "ACTION_ID,DOCUMENT_ID";
					$data_doc = $id_action.",".$value;
					$this->save_all_table($table_doc,$columns_doc,$data_doc);
				}
			}
			//insertion des information supplementaire
			if (!empty($INFOS_SUPP)) {
				$table_infos = 'proc_action_infos_supp';

				foreach ($INFOS_SUPP as $key => $value) {
					$columns_infos = "ACTION_ID,ID_INFOS_SUPP";
					$data_infos = $id_action.",".$value;
					$this->save_all_table($table_infos,$columns_infos,$data_infos);
				}
			}

			$data = ['message' => lang('messages_lang.message_success_suppr')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Actions');
		} else {
			return $this->new();
		}
	}
}
