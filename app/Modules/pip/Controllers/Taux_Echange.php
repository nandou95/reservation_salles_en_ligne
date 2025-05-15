<?php 

/**
 * auteur: christa
 * tache : crud de taux d'echange
 * date: 26/12/2023
 * email: christa@mediabox.bi
 */
namespace  App\Modules\pip\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Taux_Echange extends BaseController
{
	
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		return view('App\Modules\pip\Views\Taux_Echange_View',$data);  
	}

	//liste des taux d'échange qui sont activés
	function listing()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
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
		$order_column = array(1, 'DEVISE','TAUX',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DEVISE ASC';
		
		$search = !empty($_POST['search']['value']) ? (' AND (DEVISE LIKE "%' . $var_search . '%" OR TAUX LIKE "%' . $var_search . '%")') : '';


    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase = "SELECT TAUX_ECHANGE_ID,DEVISE,TAUX FROM pip_taux_echange WHERE IS_ACTIVE=1";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$data_taux = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		$action ='';
		$suppr = lang('messages_lang.supprimer_action');
		$modif = lang('messages_lang.bouton_modifier');
		$qu = lang('messages_lang.question_suppr_taux');
		$quitter = lang('messages_lang.quiter_action');
		foreach ($data_taux as $row)
		{
			$sub_array = array();
		  $sub_array[] = $u++;
			$sub_array[] = $row->DEVISE;
			$sub_array[] = $row->TAUX;
						
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';
			$action .="<li><a href='".base_url("pip/Taux_Echange/getOne/".$row->TAUX_ECHANGE_ID)."' >
			<label>{$modif}</label></a>
			</li>";
			$action .="<li>
			<a onclick='modal(".$row->TAUX_ECHANGE_ID.")'>
			<label class='text-danger'>{$suppr}</label>
			</a>
			</li>";
			$action .= " </ul>
			</div>
			<div class='modal fade' id='mydelete".$row->TAUX_ECHANGE_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<h5><strong>{$qu}? </strong><br> <b style:'background-color:prink';><i style='color:green;'>".$row->DEVISE."</i></b>
			</h5>
			</center>
			</div>
			<div class='modal-footer'>
			<a class='btn btn-danger btn-md' href='".base_url("pip/Taux_Echange/delete/".$row->TAUX_ECHANGE_ID)."'>{$suppr}</a>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			{$quitter}
			</button>
			</div>
			</div>
			</div>
			</div>";
			
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

	//appel du formulaire d'enregistrement d'un nouveau taux
	function new()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
  	$session  = \Config\Services::session();
  	$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
  	if(empty($user_id))
  	{
    	return redirect('Login_Ptba/do_logout');
  	}
      
  	$data['title']=lang('messages_lang.Nouveau_Taux');
  	return view('App\Modules\pip\Views\Taux_Echange_New_View',$data);
	}

	//enregistrement dans la base
	function save()
	{
		$db = db_connect();
		$this->validation = \Config\Services::validation();
		$session  = \Config\Services::session();
		
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$champ_vide = lang('messages_lang.message_champs_obligatoire');
		$rules = [
			'DEVISE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
				]
			],
			'TAUX' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
				]
			]
		];

		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$DEVISE = $this->request->getPost('DEVISE');
			$TAUX = preg_replace('/[^0-9]/', '', $this->request->getPost('TAUX'));

			$DEVISE = str_replace("\n","",$DEVISE);
			$DEVISE = str_replace("\r","",$DEVISE);
			$DEVISE = str_replace("\t","",$DEVISE);
			$DEVISE = str_replace('"','',$DEVISE);
			$DEVISE = str_replace("'",'',$DEVISE);

			$table='pip_taux_echange';
			$columns="DEVISE,TAUX";
			$datacolums=" '".$DEVISE."','".$TAUX."'";
			$this->save_all_table($table,$columns,$datacolums);

			$data = ['message' => lang('messages_lang.labelle_et_succes_ok')];
			session()->setFlashdata('alert', $data);
			$output = array('status' => TRUE);
			return redirect('pip/Taux_Echange');

		}
		else
		{
			return $this->new();
		}
	}

	//fonction pour aficher le formulaire de la modification
	public function getOne($id)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";     
	    $taux = $this->getBindParms('TAUX_ECHANGE_ID,DEVISE,TAUX', 'pip_taux_echange', 'TAUX_ECHANGE_ID= '.$id,' TAUX_ECHANGE_ID ASC');
	    $data['taux_echange']= $this->ModelPs->getRequeteOne($callpsreq, $taux);

		$data['title'] = lang('messages_lang.message_modif_taux');

		return view('App\Modules\pip\Views\Taux_Echange_Update_View',$data);
	}

	//modification des infos
	function update()
	{
		$TAUX_ECHANGE_ID = $this->request->getPost('TAUX_ECHANGE_ID');
		$db = db_connect();
		$this->validation = \Config\Services::validation();
		$session  = \Config\Services::session();
		
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$champ_vide = lang('messages_lang.message_champs_obligatoire');
		$rules = [
			'DEVISE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
				]
			],
			'TAUX' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
				]
			]
		];

		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$DEVISE = $this->request->getPost('DEVISE');
			$TAUX = $this->request->getPost('TAUX');

			$DEVISE = str_replace("\n","",$DEVISE);
			$DEVISE = str_replace("\r","",$DEVISE);
			$DEVISE = str_replace("\t","",$DEVISE);
			$DEVISE = str_replace('"','',$DEVISE);
			$DEVISE = str_replace("'",'',$DEVISE);

			$table='pip_taux_echange';
			$conditions='TAUX_ECHANGE_ID='.$TAUX_ECHANGE_ID;
			$datatomodifie= 'IS_ACTIVE=0';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$table='pip_taux_echange';
			$columns="DEVISE,TAUX";
			$datacolums=" '".$DEVISE."','".$TAUX."'";
			$this->save_all_table($table,$columns,$datacolums);
			
			$message_succes=lang('messages_lang.labelle_message_update_success');
			$data = ['message'=>$message_succes];
			session()->setFlashdata('alert', $data);
			$output = array('status' => TRUE);
			return redirect('pip/Taux_Echange');
		}
		else
		{
			return $this->getOne($TAUX_ECHANGE_ID);
		}
	}

	/*  insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
	  // $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//Update
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

	//fonction pour l'activation/désactivation
	function delete()
	{
		$session  = \Config\Services::session();
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$id=$this->request->getPost('TAUX_ECHANGE_ID');
		$table='pip_taux_echange';
		$conditions='TAUX_ECHANGE_ID='.$id;
		$datatomodifie= 'IS_ACTIVE=0';
		$this->update_all_table($table,$datatomodifie,$conditions);
		$message_suppression=lang('messages_lang.message_modif_taux');
		$data = ['message' => $message_suppression];
		session()->setFlashdata('alert', $data);
		return redirect('pip/Taux_Echange');	
	}
}
?>