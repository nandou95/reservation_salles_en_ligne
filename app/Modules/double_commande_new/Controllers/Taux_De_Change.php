<?php 

/**
 * auteur: RUGAMBA Jean Vainquer
 * tache : crud de taux de change
 * date: 03/07/2024
 * email: jean.vainqueur@mediabox.bi
 * tel:66334325/62471915
 */
namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Taux_De_Change extends BaseController
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

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		return view('App\Modules\double_commande_new\Views\Taux_De_Change_List_View',$data);  
	}

	//liste des taux d'échange qui sont activés
	function listing()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
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
		$order_column = array(1, 'DESC_DEVISE_TYPE','TAUX',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_DEVISE_TYPE ASC';
		
		$search = !empty($_POST['search']['value']) ? (' AND (DESC_DEVISE_TYPE LIKE "%' . $var_search . '%" OR TAUX LIKE "%' . $var_search . '%")') : '';


    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase = "SELECT devise.DEVISE_TYPE_ID ,DESC_DEVISE_TYPE,TAUX, IS_ACTIVE FROM devise_type devise JOIN devise_type_hist ON devise_type_hist.DEVISE_TYPE_ID=devise.DEVISE_TYPE_ID WHERE IS_ACTIVE=1";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$data_taux = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		$action ='';
		
		$modif = lang('messages_lang.bouton_modifier');
		
		foreach ($data_taux as $row)
		{
			$sub_array = array();
		  $sub_array[] = $u++;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $row->TAUX;
						
			$link_modif = "double_commande_new/Taux_De_Change/getOne/";

			
			$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .="<li>
				<a href='".base_url($link_modif.$row->DEVISE_TYPE_ID)."'><label>&nbsp;&nbsp;{$modif}</label></a>
				</li>
				</li>
				</ul>";
			
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
	function add()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
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
  	return view('App\Modules\double_commande_new\Views\Taux_De_Change_Add_View',$data);
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

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
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

			$table='devise_type';
			$columns="DESC_DEVISE_TYPE";
			$datacolums="'".$DEVISE."'";
			$devise_id=$this->save_all_table($table,$columns,$datacolums);


			$table_hist='devise_type_hist';
			$columns_histo="DEVISE_TYPE_ID,TAUX";
			$datacolums_histo="".$devise_id.",".$TAUX."";
			$this->save_all_table($table_hist,$columns_histo,$datacolums_histo);

			$data = ['message' => lang('messages_lang.labelle_et_succes_ok')];
			session()->setFlashdata('alert', $data);
			$output = array('status' => TRUE);
			return redirect('double_commande_new/Taux_De_Change');

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

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";     
	  $desc_devise = $this->getBindParms('DEVISE_TYPE_ID,DESC_DEVISE_TYPE','devise_type', 'DEVISE_TYPE_ID= '.$id,' DEVISE_TYPE_ID ASC');
	   $data['devise']= $this->ModelPs->getRequeteOne($callpsreq, $desc_devise);

	   $taux = $this->getBindParms('DEVISE_TYPE_HISTO_ID ,DEVISE_TYPE_ID,TAUX','devise_type_hist', 'DEVISE_TYPE_ID= '.$id,' DEVISE_TYPE_HISTO_ID DESC');
	   $data['taux']= $this->ModelPs->getRequeteOne($callpsreq, $taux);

		$data['title'] = lang('messages_lang.message_modif_taux');

		return view('App\Modules\double_commande_new\Views\Taux_De_Change_Update_View',$data);
	}

	//modification des infos
	function update()
	{
		$DEVISE_TYPE_ID = $this->request->getPost('DEVISE_TYPE_ID');
		$db = db_connect();
		$this->validation = \Config\Services::validation();
		$session  = \Config\Services::session();
		
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TAUX')!=1)
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

			$tab_hist_update='devise_type_hist';
			$cond_hist_update='DEVISE_TYPE_ID='.$DEVISE_TYPE_ID;
			$datatomodifie_hist= 'IS_ACTIVE=0';
			$this->update_all_table($tab_hist_update,$datatomodifie_hist,$cond_hist_update);

			$tab_update='devise_type';
			$cond_update='DEVISE_TYPE_ID='.$DEVISE_TYPE_ID;
			$datatomodifie='DESC_DEVISE_TYPE="'.$DEVISE.'"';
			$this->update_all_table($tab_update,$datatomodifie,$cond_update);

			$table_hist='devise_type_hist';
			$columns_histo="DEVISE_TYPE_ID,TAUX";
			$datacolums_histo="".$DEVISE_TYPE_ID.",".$TAUX."";
			$this->save_all_table($table_hist,$columns_histo,$datacolums_histo);
			
			$message_succes=lang('messages_lang.labelle_message_update_success');
			$data = ['message'=>$message_succes];
			session()->setFlashdata('alert', $data);
			$output = array('status' => TRUE);
			return redirect('double_commande_new/Taux_De_Change');
		}
		else
		{
			return $this->getOne($DEVISE_TYPE_ID);
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

}
?>