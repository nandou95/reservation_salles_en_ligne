<?php
/**Nderagakura Alain Charbel
 *Titre:CRUD DE GESTION DES ETAPES DU PROCESSUS
 *Numero de telephone: (+257) 62 00 35 22
 *WhatsApp: (+257) 62 00 35 22
 *Email: charbel@mediabox.bi
 *Date: 15 Novembre,2023
 **/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
class Proc_Etape extends BaseController
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
	public function index($value = '')
	{
		$session  = \Config\Services::session();
  
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}

		  if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_process = $this->getBindParms('PROCESS_ID,NOM_PROCESS','proc_process','PROCESS_ID NOT IN(7,8)','NOM_PROCESS ASC');
		$data['process'] = $this->ModelPs->getRequete($callpsreq,$bind_process);
		return view('App\Modules\ihm\Views\Proc_Etape_List_View',$data);
	}

	//fonction pour affichage de la liste des etapes
	public function listing()
	{
		$PROCESS_ID = $this->request->getPost('PROCESS_ID');
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"].','.$_POST["length"];
		}

		$order_by = '';
		$order_column = array(1, 'ETAPE_ID ', 'DESCR_ETAPE', 'NOM_PROCESS ', 'PROFIL_DESCR', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCR_ETAPE ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (DESCR_ETAPE LIKE "%' . $var_search . '%" OR NOM_PROCESS LIKE "%' . $var_search . '%")') : '';
		$critere ='';
		if (!empty($PROCESS_ID)) 
		{
			if ($PROCESS_ID > 0) 
			{
				$critere = ' AND proc_etape.PROCESS_ID='.$PROCESS_ID;
			}
		}

		$conditions = $critere.' '.$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		$conditionsfilter = $critaire.' '.$search.' '.$group;
		$requetedebase = 'SELECT proc_etape.ETAPE_ID, DESCR_ETAPE, DELETED, proc_process.NOM_PROCESS
		FROM proc_etape 
		JOIN proc_process ON proc_process.PROCESS_ID = proc_etape.PROCESS_ID 
		WHERE 1';
		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;
		foreach ($fetch_actions as $row) {
			$requetedebase = 'SELECT user_profil.PROFIL_DESCR 
			FROM proc_profil_etape 
			JOIN user_profil ON proc_profil_etape.PROFIL_ID=user_profil.PROFIL_ID 
			WHERE proc_profil_etape.ETAPE_ID='.$row->ETAPE_ID;
			$query_profil_etape = "CALL `getTable`('".$requetedebase."');";
			$dat['profil_etape'] = $this->ModelPs->datatable($query_profil_etape);
			$nbre_profils = count($dat['profil_etape']);
			//get nombre de profils
			$action_2 =  '';
			$action_2 .= "
			<div style='cursor:pointer'>
				<button style='cursor:inherit' class='btn btn-primary profil_etape_button' data-toggle='modal' data-target='#affiche_profil' onclick='get_profil_etape(".$row->ETAPE_ID.")'>
				<label class='text-white' style='cursor:inherit'>".$nbre_profils."</label></button>
			</div>";
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->DESCR_ETAPE;
			$sub_array[] = $row->NOM_PROCESS;
			$sub_array[] = $action_2;
			$stat = '';
			if ($row->DELETED == 0) {
				$stat = '<center><span class=" fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title='. lang('messages_lang.title_desactiver').'>&nbsp;</span></center>';
			} else {
				$stat = '<center><span class=" fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title='. lang('messages_lang.title_activer').'>&nbsp;</span></center>';
			}
			$sub_array[] = $stat;

			if ($row->DELETED == 1) {
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .= "<li>
				<a href='" . base_url("ihm/Proc_Etape/getOne/" . $row->ETAPE_ID) . "'><label>&nbsp;&nbsp;". lang('messages_lang.labelle_et_modif')."</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(" . $row->ETAPE_ID . ")' title=". lang('messages_lang.labelle_et_desactiver')." ><label>&nbsp;&nbsp;<font color='red'>". lang('messages_lang.labelle_et_desactiver')."</font></label></a>

				</li>
				<div style='display:none;' id='message" . $row->ETAPE_ID . "'>
				<center>
				<h5><strong>". lang('messages_lang.labelle_et_mod_question_b')."<br><center><font color='green'>" . $row->DESCR_ETAPE . "&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
				</h5>
				</center>
				</div>
				<div style='display:none;' id='footer" . $row->ETAPE_ID . "'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				". lang('messages_lang.labelle_et_mod_quiter')."
			  </button>
				<a href='" . base_url("ihm/Proc_Etape/is_active/" . $row->ETAPE_ID) . "' class='btn btn-danger btn-md'>".lang('messages_lang.labelle_et_desactiver')."</a>
				</div>";
			} else {

				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .= "<li>
				<a href='" . base_url("ihm/Proc_Etape/getOne/" . $row->ETAPE_ID) . "'><label>&nbsp;&nbsp;". lang('messages_lang.bouton_modifier')."</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(" . $row->ETAPE_ID . ")' title='". lang('messages_lang.active_action')."' ><label>&nbsp;&nbsp;<font color='green'>". lang('messages_lang.active_action')."</font></label></a>

				</li>
				<div style='display:none;' id='message" . $row->ETAPE_ID . "'>
					<center>
					<h5><strong>". lang('messages_lang.labelle_et_mod_question_a')."<br><center><font color='green'>" . $row->DESCR_ETAPE . "&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
					</h5>
					</center>
				</div>
				<div style='display:none;' id='footer" . $row->ETAPE_ID . "'>
					<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
					". lang('messages_lang.labelle_et_mod_quiter')."
					</button>
					<a href='" . base_url("ihm/Proc_Etape/is_active/" . $row->ETAPE_ID) . "' class='btn btn-danger btn-md'>". lang('messages_lang.active_action')."</a>
				</div>";
			}
			$sub_array[] = $action;
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
		return $this->response->setJSON($output);
	}
	//fonction pour l'activation/désactivation
	function is_active($ETAPE_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_etape = $this->getBindParms('ETAPE_ID ,DELETED','proc_etape','ETAPE_ID='.$ETAPE_ID,'ETAPE_ID ASC');
		$etape = $this->ModelPs->getRequeteOne($callpsreq, $bind_etape);

		if ($etape['DELETED'] == 0) {
			$updateTable = 'proc_etape';
			$critere = "ETAPE_ID=".$ETAPE_ID;
			$datatoupdate = 'DELETED=1';
			$bindparams = [$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete,$bindparams);
			$data = ['message' => lang('messages_lang.labelle_et_mod_question_succes')];
			session()->setFlashdata('alert',$data);
			return redirect('ihm/Proc_Etape');
		} elseif ($etape['DELETED'] == 1) {
			$updateTable = 'proc_etape';
			$critere = "ETAPE_ID=".$ETAPE_ID;
			$datatoupdate = 'DELETED=0';
			$bindparams = [$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete,$bindparams);
			$data = ['message' => lang('messages_lang.labelle_et_mod_question_succes_d')];
			session()->setFlashdata('alert',$data);
			return redirect('ihm/Proc_Etape');
		}
	}
	//Fonction pour afficher le formulaire d'insertion
	public function ajout()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_profil = $this->getBindParms('PROFIL_ID , PROFIL_DESCR', 'user_profil', '1', 'PROFIL_DESCR ASC');
		$data['profil'] = $this->ModelPs->getRequete($callpsreq, $bind_profil);
		$bind_process = $this->getBindParms('PROCESS_ID , NOM_PROCESS', 'proc_process', '1', 'NOM_PROCESS ASC');
		$data['process'] = $this->ModelPs->getRequete($callpsreq, $bind_process);
		return view('App\Modules\ihm\Views\Proc_Etape_Add_View', $data);
	}
	//fonction pour l'insertion dans la table des users et des affectation
	public function insert()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$db = db_connect();
		$rules = [
			'ETAPE' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'PROFIL_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'PROCESS_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
		];
		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run()) {
			$ETAPE = $this->request->getPost('ETAPE');
			$DELETED = 0;
			$PROFILS = $this->request->getPost('PROFIL_ID[]');
			$PROCESS_ID = $this->request->getPost('PROCESS_ID');
			//insertion in "proc_etape" table
			$insertInto = 'proc_etape';
			$colum = "DESCR_ETAPE,DELETED,PROCESS_ID";
			$datacolums = "'".$ETAPE."',".$DELETED.",".$PROCESS_ID."";
			$bindparms = [$insertInto, $colum, $datacolums];
			$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
			$insert_etape = $this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
			$ETAPE_ID = $insert_etape['id'];

			foreach ($PROFILS as $p) {
				//save to proc_profil_etape
				$insertIntoTable1 = 'proc_profil_etape';
				$columsinsert1 = 'ETAPE_ID,PROFIL_ID';
				$datatoinsert1 = $ETAPE_ID . ",".$p;
				$this->save_all_table($insertIntoTable1, $columsinsert1, $datatoinsert1);
			}
			$data = ['message' => lang('messages_lang.message_success') ];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Proc_Etape');
		} else {
			return $this->ajout();
		}
	}
	public function getOne($ETAPE_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_profil = $this->getBindParms('PROFIL_ID , PROFIL_DESCR', 'user_profil', '1', 'PROFIL_DESCR ASC');
		$data['profil'] = $this->ModelPs->getRequete($callpsreq, $bind_profil);
		$bind_process = $this->getBindParms('PROCESS_ID , NOM_PROCESS', 'proc_process', '1', 'NOM_PROCESS ASC');
		$data['process'] = $this->ModelPs->getRequete($callpsreq, $bind_process);
		$bind_etape = $this->getBindParms('proc_etape.ETAPE_ID,proc_etape.DESCR_ETAPE,proc_process.PROCESS_ID,proc_process.NOM_PROCESS,user_profil.PROFIL_DESCR,proc_profil_etape.PROFIL_ID', 'proc_etape join proc_process on proc_process.PROCESS_ID=proc_etape.PROCESS_ID LEFT JOIN proc_profil_etape on proc_etape.ETAPE_ID=proc_profil_etape.ETAPE_ID LEFT JOIN user_profil on proc_profil_etape.PROFIL_ID=user_profil.PROFIL_ID', 'proc_etape.ETAPE_ID='.$ETAPE_ID, '1');

		$data['etape'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_etape);
		$bind_profil_etape = $this->getBindParms('ETAPE_ID,PROFIL_ID', 'proc_profil_etape', 'ETAPE_ID=' . $ETAPE_ID, 'PROFIL_ID ASC');
		$data['profil_etape'] = $this->ModelPs->getRequete($callpsreq, $bind_profil_etape);
		// dd($data['profil_etape']);
		$data['ETAPE_ID']=$ETAPE_ID;
		return view('App\Modules\ihm\Views\Proc_Etape_Update_View', $data);
	}
	//fonction pour l'insertion dans la table des users et des affectation
	public function update()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$db = db_connect();
		$rules = [
			'ETAPE' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'PROFIL_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'PROCESS_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
		];
		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run()) {
			$ETAPE_ID = $this->request->getPost('ETAPE_ID');
			$ETAPE = $this->request->getPost('ETAPE');
			$PROFILS = $this->request->getPost('PROFIL_ID');
			$PROCESS_ID = $this->request->getPost('PROCESS_ID');

			//update in proc_etape
			$updateIntoTable = 'proc_etape';
			$datatoupdate = "'".$ETAPE."',".$PROCESS_ID."";
			$columsupdate = 'DESCR_ETAPE="'.$ETAPE.'",PROCESS_ID='.$PROCESS_ID;
			$conditions = 'ETAPE_ID='.$ETAPE_ID;
			$this->update_all_table($updateIntoTable,$columsupdate,$conditions);

			//delete from proc_profil_etape
			$db = db_connect();
			$critere = "ETAPE_ID =" . $ETAPE_ID;
			$table = "proc_profil_etape";
			$bindparams = [$db->escapeString($table), $db->escapeString($critere)];
			$deleteRequete = "CALL `deleteData`(?,?);";
			$this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);

			foreach ($PROFILS as $p) {

				//save new profils to proc_profil_etape
				$insertIntoTable = 'proc_profil_etape';
				$columsinsert = 'ETAPE_ID,PROFIL_ID';
				$datatoinsert = $ETAPE_ID . "," . $p;
				$this->save_all_table($insertIntoTable, $columsinsert, $datatoinsert);
			}
			$data = ['message' => lang('messages_lang.message_success')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Proc_Etape');
		} else {
			return $this->getOne($ETAPE_ID);
		}
	}
	//fonction pour supprimer dans cart/table tempo
	public function delete($ETAPE_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();
		$critere = "ETAPE_ID=".$ETAPE_ID;
		$table = "proc_etape";
		$bindparams = [$db->escapeString($table), $db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
		$data = ['message' => lang('messages_lang.message_success_suppr')];
		session()->setFlashdata('alert',$data);
		return redirect('ihm/Proc_Etape');
	}
	//fonction pour inserer dans les colonnes souhaites
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
	/* update table */
	function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	//fonction pour retourner les profils d'une étape
	public function get_profil_etape()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} else {
			return redirect('Login_Ptba');
		}
		$ETAPE_ID = '';
		$ETAPE_ID = $this->request->getPost('ETAPE_ID');
		$requetedebase = 'SELECT DISTINCT user_profil.PROFIL_DESCR 
		FROM proc_profil_etape 
		JOIN user_profil ON proc_profil_etape.PROFIL_ID = user_profil.PROFIL_ID 
		WHERE proc_profil_etape.ETAPE_ID=' . $ETAPE_ID;
		$query_profil_etape = "CALL `getTable`('" . $requetedebase . "');";
		$data['profil_etape'] = $this->ModelPs->datatable($query_profil_etape);
		echo json_encode($data['profil_etape']);
	}

	//fonction pour associer etape et profil
	public function associate_profil_etape()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ETAPE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		//get etapes
		$get_etapes = 'SELECT ETAPE_ID, PROFIL_ID FROM proc_etape';
		$etapes = "CALL `getTable`('".$get_etapes."');";
		$fetch_etapes = $this->ModelPs->datatable($etapes);

		//get profil_etape
		$get_profil_etapes = 'SELECT ETAPE_ID,PROFIL_ID FROM proc_profil_etape';
		$profil_etapes = "CALL `getTable`('".$get_profil_etapes."');";
		$fetch_profil_etapes = $this->ModelPs->datatable($profil_etapes);

		$counter = 0;

		//insertion dans proc_profil_etape
		foreach ($fetch_etapes as $etape) {
			//vérifier si les valeurs ne sont pas nulles
			if ($etape->ETAPE_ID != NULL && $etape->PROFIL_ID != NULL) {
				//vérifier les doublons dans proc_profil_etape
				$callpsreq = "CALL `getRequete`(?,?,?,?);";
				$profil_etape = $this->getBindParms('ETAPE_ID,PROFIL_ID','proc_profil_etape','ETAPE_ID='.$etape->ETAPE_ID.' AND PROFIL_ID='.$etape->PROFIL_ID,'ETAPE_ID ASC');
				$bool = $this->ModelPs->getRequeteOne($callpsreq, $profil_etape);

				if (!$bool) {
					$insertIntoTable = 'proc_profil_etape';
					$columsinsert = 'ETAPE_ID,PROFIL_ID';
					$datatoinsert = $etape->ETAPE_ID . "," . $etape->PROFIL_ID;
					$this->save_all_table($insertIntoTable, $columsinsert, $datatoinsert);
					$counter++;
				}
			}
		}
		$data=['message' => lang('messages_lang.message_success')];
		session()->setFlashdata('alert', $data);
		return redirect('ihm/Proc_Etape');
	}
}
