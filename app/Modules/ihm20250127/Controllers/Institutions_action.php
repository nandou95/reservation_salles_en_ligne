<?php
/**NIYONAHABONYE Pascal
*Titre:liste des institutions actions
*Numero de telephone: (+257) 68 045 482
*WhatsApp: (+257) 77531083
*Email: pascal@mediabox.bi
*Date: 29 Août,2023
**/
/**Alain charbel NDERAGAKURA
*Titre: Quelques modification sur la liste des actions
*Numero de telephone: (+257) 68 045 482
*WhatsApp: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 17 novembre,2023
**/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Institutions_action extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID','inst_institutions','1','DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');

		if(!empty($INSTITUTION_ID))
		{
			$program = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes','1','PROGRAMME_ID DESC');
			$data['program']= $this->ModelPs->getRequete($psgetrequete, $program);	
		}
		else
		{
			$program = [];
		}

		$data['program'] = $program;
		$data['INSTITUTION_ID'] = $INSTITUTION_ID;
		$data['PROGRAMME_ID'] = $PROGRAMME_ID;
		return view('App\Modules\ihm\Views\Institutions_action_list_view',$data);   
	}

	public function indexdeux($value='')
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID','inst_institutions','1','DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);
		$program = array('PROGRAMME_ID'=>NULL,'INTITULE_PROGRAMME'=>NULL,'INSTITUTION_ID'=>NULL); // Initialisez $program à null en dehors de la condition
		$PROGRAMME_ID = $this->request->getPost('');
		$INSTITUTION_ID = 0;
		$PROGRAMME_ID=0;
		if(!empty($this->request->getPost('INSTITUTION_ID')))
		{
			$INSTITUTION_ID= $this->request->getPost('INSTITUTION_ID');
		}

		if(!empty($this->request->getPost('PROGRAMME_ID')))
		{
			$PROGRAMME_ID= $this->request->getPost('PROGRAMME_ID');
		}
		$program = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes','INSTITUTION_ID='.$INSTITUTION_ID,'PROGRAMME_ID DESC');
		$program = $this->ModelPs->getRequete($psgetrequete,$program);
		$data['program'] = $program;
		$data['INSTITUTION_ID'] = $INSTITUTION_ID;
		$data['PROGRAMME_ID'] = $PROGRAMME_ID;
		return view('App\Modules\ihm\Views\Institutions_action_list_view',$data);
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
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
		$order_column = array(1, 'CODE_ACTION','LIBELLE_ACTION','OBJECTIF_ACTION', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_ACTION ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (CODE_ACTION LIKE "%' . $var_search . '%")') : '';
		if(!empty($INSTITUTION_ID))
		{
			if($INSTITUTION_ID>0)
			{
				$critere = ' AND inst_institutions.INSTITUTION_ID=' . $INSTITUTION_ID;
			}
		}
		if(!empty($PROGRAMME_ID))
		{
			if($PROGRAMME_ID>0)
			{
				$critere .= ' AND inst_institutions_programmes.PROGRAMME_ID='.$PROGRAMME_ID;
			}
		}
    // Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere.' '.$search.' '.$group;
		$requetedebase= "SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,STRUTURE_IMPLIQUEES FROM inst_institutions_actions LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=inst_institutions_actions.PROGRAMME_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID WHERE 1";
		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';
		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;

		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->CODE_ACTION;
			$sub_array[] = $row->LIBELLE_ACTION;
			$sub_array[] = $row->OBJECTIF_ACTION;
			$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'.lang('messages_lang.dropdown_link_options').'<span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';
			$action .="<li>
				<a href='".base_url("ihm/Detail_Action/".MD5($row->ACTION_ID))."'><label>&nbsp;&nbsp;".lang('messages_lang.detail')."</label></a>
				</li></ul>";
			$sub_array[]=$action;
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
		return $this->response->setJSON($output);//echo json_encode($output);
	}

	public function get_institution()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$bind_program = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME, INSTITUTION_ID', 'inst_institutions_programmes','INSTITUTION_ID='.$INSTITUTION_ID.'', 'ZONE_NAME DESC');
		$program= $this->ModelPs->getRequete($callpsreq, $bind_program);
		$html = '<option value="">'. lang('messages_lang.label_selecte') .'</option>';

		if(!empty($program))
		{
			foreach($program as $key)
			{
				if($key->COMMANDE_ID == set_value('COMMANDE_ID'))
				{
					$html .= "<option value='".$key->COMMANDE_ID."' selected>".$key->ZONE_NAME."</option>";
				}
				else
				{
					$html .= "<option value='".$key->COMMANDE_ID."'>".$key->ZONE_NAME."</option>";
				}
			}
		}
		$output = array('status' => TRUE, 'cart'=>$html);
		return $this->response->setJSON($output);
	}

  //fonctin  pour afficher le formulaire
	public function ajout($value='')
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparam = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME','inst_institutions_programmes','1','PROGRAMME_ID ASC');
		$data['description']= $this->ModelPs->getRequete($callpsreq,$bindparam);
		return view('App\Modules\ihm\Views\Institutions_action_add_view',$data);   
	}

	//Formulaire pour inserer les donnees dans la table
	public function insert()
	 {
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();
		$rules = [
			'PROGRAMME_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'CODE_ACTION' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'LIBELLE_ACTION' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'OBJECTIF_ACTION' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'STRUTURE_IMPLIQUEES' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			]

		];
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		   {
			$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
			$CODE_ACTION = $this->request->getPost('CODE_ACTION');
			$LIBELLE_ACTION = $this->request->getPost('LIBELLE_ACTION');
			$OBJECTIF_ACTION = $this->request->getPost('OBJECTIF_ACTION');
			$STRUTURE_IMPLIQUEES = $this->request->getPost('STRUTURE_IMPLIQUEES');
			$insertIntoTable='inst_institutions_actions';
			$datatoinsert = ''.$PROGRAMME_ID.',"'.$CODE_ACTION.'","'.$LIBELLE_ACTION.'","'.$OBJECTIF_ACTION.'","'.$STRUTURE_IMPLIQUEES.'"';
			$bindparams =[$insertIntoTable,$datatoinsert];
			$insertRequete = "CALL `insertLastIdIntoTable`(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);;

			$data = ['message' => lang('messages_lang.message_success')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Institutions_action'); 
		}
		else
		{
			return $this->ajout();
		}
	}

	// fonction get pour recuperer les données a modifier
	public function getOne($id)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_respon= $this->getBindParms('ACTION_ID,PROGRAMME_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,STRUTURE_IMPLIQUEES','inst_institutions_actions','ACTION_ID='.$id,'ACTION_ID ASC');
		$data['action_inst']= $this->ModelPs->getRequeteOne($callpsreq, $bind_respon);
		$bind_intitule = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME','inst_institutions_programmes','1','PROGRAMME_ID DESC');
		$data['intitule']= $this->ModelPs->getRequete($callpsreq, $bind_intitule);
		return view('App\Modules\ihm\Views\Institutions_action_update_view',$data);
	}

	public function update()
	   {
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }    	
		$db = db_connect();
		$ACTION_ID = $this->request->getPost('ACTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
		$CODE_ACTION = $this->request->getPost('CODE_ACTION');
		$LIBELLE_ACTION = $this->request->getPost('LIBELLE_ACTION');
		$OBJECTIF_ACTION = $this->request->getPost('OBJECTIF_ACTION');
		$STRUTURE_IMPLIQUEES = $this->request->getPost('STRUTURE_IMPLIQUEES');
		$updateTable='inst_institutions_actions';
		$critere = "ACTION_ID=".$ACTION_ID;
		$datatoupdate = 'PROGRAMME_ID='.$PROGRAMME_ID.',CODE_ACTION="'.$CODE_ACTION.'",LIBELLE_ACTION="'.$LIBELLE_ACTION.'", OBJECTIF_ACTION="'.$OBJECTIF_ACTION.'",STRUTURE_IMPLIQUEES="'.$STRUTURE_IMPLIQUEES.'"';
		$bindparams =[$updateTable,$datatoupdate,$critere];
		$insertRequete = 'CALL `updateData`(?,?,?);';

		$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
		$data = ['message' => lang('messages_lang.labelle_message_update_success')];
		session()->setFlashdata('alert', $data);
		$output = array('status' => TRUE);
		return redirect('ihm/Institutions_action');
	}

	public function delete($ACTION_ID )
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();     
		$critere ="ACTION_ID =".$ACTION_ID;
		$table='inst_institutions_actions';
		$deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
		$data = ['message' => lang('messages_lang.supprimer_success')];
		session()->setFlashdata('alert', $data);
		return redirect('ihm/Institutions_action');
	}
}
?>