<?php
/**NIYONAHABONYE Pascal
*Titre:LISTE ET DETAIL DES PROGRAMME
*Numero de telephone: (+257) 68 045 482
*WhatsApp: (+257) 77531083
*Email: pascal@mediabox.IMPORT
*Date: 29 Août,2023
**/
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
class Programme extends BaseController
{
	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}
	function index()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data = $this->urichk();
		return view('App\Modules\ihm\Views\Programme_list_view',$data);   
	}
	function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $bindparams;
	}
	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'","\'",$var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT '.$_POST["start"].','.$_POST["length"];
		}
		$order_by = '';
		$order_column = array(1,'INTITULE_PROGRAMME','CODE_PROGRAMME', 'DESCRIPTION_INSTITUTION','OBJECTIF_DU_PROGRAMME',1,1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY INTITULE_PROGRAMME ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (CODE_PROGRAMME LIKE "%' . $var_search . '%" OR INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR OBJECTIF_DU_PROGRAMME LIKE "%' . $var_search . '%")') : '';
		// Condition pour la requête principale
		$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critaire.' '.$search.' '.$group;
		$requetedebase = "SELECT IS_ACTIVE,PROGRAMME_ID,CODE_PROGRAMME,OBJECTIF_DU_PROGRAMME,INTITULE_PROGRAMME,inst.INSTITUTION_ID,inst.DESCRIPTION_INSTITUTION FROM inst_institutions_programmes prog JOIN inst_institutions inst ON prog.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1";
		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;
		$stat ='';
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->INTITULE_PROGRAMME;
			$sub_array[] = $row->CODE_PROGRAMME;
			$sub_array[] = $row->DESCRIPTION_INSTITUTION;
			$sub_array[] = $row->OBJECTIF_DU_PROGRAMME;
			if($row->IS_ACTIVE==0)
			{
				$stat = '<center><span class="fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'. lang('messages_lang.desactive_action') .'">&nbsp;</span></center>';
			}
			else
			{
				$stat = '<center><span class="fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'. lang('messages_lang.active_action') .'">&nbsp;</span></center>';
			}
			$sub_array[]=$stat;

			if($row->IS_ACTIVE==1)
			{
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'. lang('messages_lang.dropdown_link_options') .'<span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';
				$action .="<li><a href='".base_url("ihm/Detail_Programme/".md5($row->PROGRAMME_ID))."' >
				<label>&nbsp;&nbsp;" . lang('messages_lang.detail') . "</label></a>
				</li>
				<li><a href='".base_url("ihm/Programme/getOne/".md5($row->PROGRAMME_ID))."' >
				<label>&nbsp;&nbsp;" . lang('messages_lang.bouton_modifier') . "</label></a>
				</li>";
				$action .="<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->PROGRAMME_ID.")'>
				<label class='text-danger'>&nbsp;&nbsp;". lang('messages_lang.desactive_action') ."</label>
				</a>
				</li>
				<div style='display:none;' id='message" . $row->PROGRAMME_ID . "'>
              <center>
                <h5><strong>" . lang('messages_lang.confimatation_desactive_action') . "<br><center><font color='green'>" . $row->PROGRAMME_ID . "&nbsp;&nbsp;" .$row->INTITULE_PROGRAMME. "</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer" . $row->PROGRAMME_ID . "'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              " . lang('messages_lang.quiter_action') . "
              </button>
              <a class='btn btn-danger btn-md' href='".base_url("ihm/Programme/is_active/".$row->PROGRAMME_ID)."'>". lang('messages_lang.desactive_action') ."</a>
            </div>";
				$action .= " </ul>
				</div>
				";
			}
			elseif($row->IS_ACTIVE==0)
			{
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'. lang('messages_lang.dropdown_link_options') .'<span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';
				$action .="<li><a href='".base_url("ihm/Detail_Programme/".$row->PROGRAMME_ID)."' >
				<label>&nbsp;&nbsp;". lang('messages_lang.detail') ."</label></a>
				</li>
				<li><a href='".base_url("ihm/Programme/getOne/".$row->PROGRAMME_ID)."' >
				<label>&nbsp;&nbsp;". lang('messages_lang.bouton_modifier') ."</label></a>
				</li>";
				$action .="<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->PROGRAMME_ID.")'>
				<label class='text-danger'>&nbsp;&nbsp;". lang('messages_lang.active_action') ."</label>
				</a>
				</li>
				<div style='display:none;' id='message" . $row->PROGRAMME_ID . "'>
              <center>
                <h5><strong>" . lang('messages_lang.confimatation_active_action') . "<br><center><font color='green'>" . $row->PROGRAMME_ID . "&nbsp;&nbsp;" .$row->INTITULE_PROGRAMME. "</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer" . $row->PROGRAMME_ID . "'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              " . lang('messages_lang.quiter_action') . "
              </button>
              <a class='btn btn-danger btn-md' href='".base_url("ihm/Programme/is_active/".$row->PROGRAMME_ID)."'>". lang('messages_lang.active_action') ."</a>
            </div>";

				$action .= " </ul>
				</div>";
			}
			$sub_array[]=$action;
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

	//fonction  pour afficher le formulaire
	public function ajout($value='')
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparam = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION' ,'inst_institutions', '1', 'INSTITUTION_ID ASC');
		$data['description']= $this->ModelPs->getRequete($callpsreq, $bindparam);
		return view('App\Modules\ihm\Views\Programme_add_view',$data);   
	}
	/**
	 * fonction pour retourner le tableau des parametre pour le PS pour les selection
	 * @param string  $columnselect //colone A selectionner
	 * @param string  $table        //table utilisE
	 * @param string  $where        //condition dans la clause where
	 * @param string  $orderby      //order by
	 * @return  mixed
	 */

	//Formulaire pour inserer les donnees dans la table
	public function insert()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();
		$rules = [
			'INSTITUTION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'CODE_PROGRAMME' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'INTITULE_PROGRAMME' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'OBJECTIF_DU_PROGRAMME' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			]
		];
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
			$CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
			$INTITULE_PROGRAMME = $this->request->getPost('INTITULE_PROGRAMME');
			$OBJECTIF_DU_PROGRAMME = $this->request->getPost('OBJECTIF_DU_PROGRAMME');
			$IS_ACTIVE=1;
			$insertIntoTable='inst_institutions_programmes';
			$datatoinsert = ''.$INSTITUTION_ID.',"'.$CODE_PROGRAMME.'","'.$INTITULE_PROGRAMME.'","'.$OBJECTIF_DU_PROGRAMME.'",'.$IS_ACTIVE.'';
			$bindparams =[$insertIntoTable,$datatoinsert];
			$insertRequete = "CALL `insertIntoTable`(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);;

			$data = ['message' => lang('messages_lang.labelle_et_succes_ok')];
			session()->setFlashdata('alert', $data);
		}
		else
		{
			return $this->ajout();
		}
	}

	//fonction get pour recuperer les données 
	public function getOne($id)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_id_exist = $this->getBindParms('PROGRAMME_ID','inst_institutions_programmes','MD5(PROGRAMME_ID)="'.$id.'"','1');
		$bind_id_exist=str_replace('\\','',$bind_id_exist);
		$id_exist = $this->ModelPs->getRequeteOne($callpsreq, $bind_id_exist);
		if(empty($id_exist)){
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$table="inst_institutions_programmes";
			$columnselect='PROGRAMME_ID,INSTITUTION_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_DU_PROGRAMME';
			$where="MD5(PROGRAMME_ID)='".$id."'";
			$orderby=' PROGRAMME_ID';
			$where=str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
			$bindparams=str_replace("\'", "'", $bindparams);
			$data['program']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

			$bind_intitule = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION','inst_institutions','1','INSTITUTION_ID DESC');
			$data['intitule']= $this->ModelPs->getRequete($callpsreq, $bind_intitule);
			return view('App\Modules\ihm\Views\Programme_update_view',$data);
		}
	}

	//Mise à jour des profil
	function update()
	{ 
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }  	
		$db = db_connect();
		$PROGRAMME_ID =$this->request->getPost('PROGRAMME_ID');
		$INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
		$CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
		$INTITULE_PROGRAMME = $this->request->getPost('INTITULE_PROGRAMME');
		$OBJECTIF_DU_PROGRAMME = $this->request->getPost('OBJECTIF_DU_PROGRAMME');

		$updateTable='inst_institutions_programmes';
		$critere = "PROGRAMME_ID=".$PROGRAMME_ID;
		$datatoupdate='INSTITUTION_ID='.$INSTITUTION_ID.',CODE_PROGRAMME="'.$CODE_PROGRAMME.'",INTITULE_PROGRAMME="'.$INTITULE_PROGRAMME.'",OBJECTIF_DU_PROGRAMME="'.$OBJECTIF_DU_PROGRAMME.'" ';
		$bindparams =[$updateTable,$datatoupdate,$critere];
		$insertRequete = 'CALL `updateData`(?,?,?);';
		$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
		$data=['message' => lang('messages_lang.labelle_message_update_success')];
		session()->setFlashdata('alert', $data);
		$output = array('status' => true);
		return $this->response->setJSON($output);
		echo json_encode($output);
	}
	//fonction pour l'activation/désactivation
	function is_active($PROGRAMME_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_user = $this->getBindParms('PROGRAMME_ID,IS_ACTIVE','inst_institutions_programmes','PROGRAMME_ID='.$PROGRAMME_ID,'PROGRAMME_ID ASC');
		$users= $this->ModelPs->getRequeteOne($callpsreq, $bind_user);
		if($users['IS_ACTIVE']==0)
		{
			$updateTable='inst_institutions_programmes';
			$critere = "PROGRAMME_ID=".$PROGRAMME_ID;
			$datatoupdate= 'IS_ACTIVE=1';
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data=['message' => "Activation effectuée avec succès"];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Programme');
		}
		elseif($users['IS_ACTIVE']==1)
		{
			$updateTable='inst_institutions_programmes';
			$critere = "PROGRAMME_ID=".$PROGRAMME_ID;
			$datatoupdate= 'IS_ACTIVE=0';
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data=['message' => "Désactivation effectuée avec succès"];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Programme');
		}
	}
}
?>