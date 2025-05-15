<?php 
/**
 *
 * auteur:Douce
 * tache:   crud information supplementare
 * date: le 23/11/2023
 * email:douce@mediabox.bi
 */
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Information_sup extends BaseController
{
	
	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}
	//appel du view de la liste des actions
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
		 if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }
		$data['title']= lang('messages_lang.labelle_et_titre_info_supp');
		return view('App\Modules\ihm\Views\Information_sup_View',$data);
	}
	//listing
	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
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
		$order_by = '';

		$order_column = array(1,'DESCR_INFOS_SUPP','INFOS_NAME', 'TYPE_INFOS_NAME',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCR_ACTION ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (DESCR_INFOS_SUPP LIKE "%' . $var_search . '%" OR INFOS_NAME LIKE "%' . $var_search . '%" OR TYPE_INFOS_NAME LIKE "%'.$var_search.'%")') : '';

		// Condition pour la requête principale
		$conditions = $critaire .' '.$search.' '.$group.' ' . $order_by.' '.$limit;

		// Condition pour la requête de filtre
		$conditionsfilter = $critaire.' '.$search.' '.$group;

		$requetedebase = "SELECT `ID_INFOS_SUPP`,`DESCR_INFOS_SUPP`,`INFOS_NAME`,`TYPE_INFOS_NAME` FROM `proc_infos_supp` WHERE 1";

		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
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
			$sub_array[] = $row->DESCR_INFOS_SUPP;
			if ($row->TYPE_INFOS_NAME==1) {
				$sub_array[] = $date;
			}elseif($row->TYPE_INFOS_NAME==2){
				$sub_array[] = $select;
			}
			else{
				$sub_array[] = $text;
			}
			// $sub_array[] = $row->TYPE_INFOS_NAME;
			$stat = '
				<div class="dropdown" style="color:#fff;">
					<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

					$stat .= "
						<li>
							<a href='".base_url("ihm/Information_sup/getOne/".$row->ID_INFOS_SUPP)."'><label>&nbsp;&nbsp;".lang('messages_lang.labelle_et_modif')."</label></a>
						</li>";
					$stat .= "  
						<li>
							<a href='javascript:void(0)' onclick='show_modal_2(".$row->ID_INFOS_SUPP.")' title=".lang('messages_lang.supprimer_action')." >
							<label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.supprimer_action')."</font></label></a>
						</li>";
					$stat .= " 
				</ul>
			</div>


			<div style='display:none;' id='message".$row->ID_INFOS_SUPP."'>
						<center>
							<h5><strong>".lang('messages_lang.labelle_et_mod_question_b')."<br><center><font color='green'>".$row->DESCR_INFOS_SUPP."&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
							</h5>
						</center>
					</div>
					<div style='display:none;' id='footer".$row->ID_INFOS_SUPP."'>
						<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
						".lang('messages_lang.labelle_et_mod_quiter')."
					  </button>
						<a href='".base_url("ihm/Information_sup/delete/".$row->ID_INFOS_SUPP)."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
				</div>";
			$sub_array[]=$stat;
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

	//appel du form d'enregistrement
	function new()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
   
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		 if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$infos_suppl = array(
			array('ID' => 1, 'DES' => 'Date'),
			array('ID' => 2, 'DES' => 'Select'),
			array('ID' => 3, 'DES' => 'Text')
		);
		$data['infos_suppl']=$infos_suppl;
		$data['title']=lang('messages_lang.labelle_et_titre_info_supp_new');
		return view('App\Modules\ihm\Views\Information_sup_New_View',$data);
	}

	//foncton pour enregistrer le donnees 
	function save()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
   
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		 if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$rules = [
			'DESCR_INFOS_SUPP' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'TYPE_INFOS_NAME' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],		
		];
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			$DESCR_INFOS_SUPP = $this->request->getPost('DESCR_INFOS_SUPP');
			$TYPE_INFOS_NAME = $this->request->getPost('TYPE_INFOS_NAME');
			$INFOS_NAME = $this->request->getPost('INFOS_NAME');
			$DESCR_INFOS_SUPP = str_replace("\n","",$DESCR_INFOS_SUPP);
			$DESCR_INFOS_SUPP = str_replace("\r","",$DESCR_INFOS_SUPP);
			$DESCR_INFOS_SUPP = str_replace("\t","",$DESCR_INFOS_SUPP);
			$DESCR_INFOS_SUPP = str_replace('"','',$DESCR_INFOS_SUPP);
			$DESCR_INFOS_SUPP = str_replace("'",'',$DESCR_INFOS_SUPP);
			$INFOS_NAME = str_replace(' ', '', $DESCR_INFOS_SUPP);
			$insertRequete = "CALL `insertIntoTable`(?,?);";
			$insertIntoTable='proc_infos_supp';
			$datatoinsert = '"'.$DESCR_INFOS_SUPP.'","'.$INFOS_NAME.'","'.$TYPE_INFOS_NAME.'"';
			$bindparams =[$insertIntoTable,$datatoinsert];
			$insertRequete = "CALL `insertIntoTable`(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data=['message' => lang('messages_lang.message_success')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Information_sup');
		}
		else
		{
			return $this->new();
		}
	}
	//fonction pour amener le formulaire de la modification
	public function getOne($id)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
   
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		
		 if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$bind_respona= $this->getBindParms('ID_INFOS_SUPP,DESCR_INFOS_SUPP,INFOS_NAME,TYPE_INFOS_NAME','proc_infos_supp','ID_INFOS_SUPP='.$id,'ID_INFOS_SUPP ASC');
		$data['info']= $this->ModelPs->getRequeteOne($callpsreq, $bind_respona);	

		$infos_suppl = array(
			array('ID' => 1, 'DES' => 'Date'),
			array('ID' => 2, 'DES' => 'Select'),
			array('ID' => 3, 'DES' => 'Text')
		);
		$data['infos_suppl']=$infos_suppl;
		$data['title']="Modification de l' information supplementaire";
		
		return view('App\Modules\ihm\Views\Information_sup_Update_View',$data);
	}
	//fonction pour la modification
	public function update()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }    	
		$db = db_connect();
		$ID_INFOS_SUPP = $this->request->getPost('ID_INFOS_SUPP');

		$DESCR_INFOS_SUPP = $this->request->getPost('DESCR_INFOS_SUPP');
		$INFOS_NAME = $this->request->getPost('INFOS_NAME');
		$TYPE_INFOS_NAME = $this->request->getPost('TYPE_INFOS_NAME');
		
		$updateTable='proc_infos_supp';
		$critere = "ID_INFOS_SUPP=".$ID_INFOS_SUPP;
		$datatoupdate = 'DESCR_INFOS_SUPP="'. $DESCR_INFOS_SUPP . '", INFOS_NAME="' . $INFOS_NAME . '", TYPE_INFOS_NAME="' . $TYPE_INFOS_NAME . '"';
		$bindparams =[$updateTable,$datatoupdate,$critere];
		$insertRequete = 'CALL `updateData`(?,?,?);';
		$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
		$data = ['message' => "Modification effectuée avec succès"];
		session()->setFlashdata('alert', $data);
		$output = array('status' => TRUE);
		return redirect('ihm/Information_sup');
	}
	//fonction pour la suppression
	public function delete($ID_INFOS_SUPP)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_INFO_SUPPLEMENTAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();     
		$critere ="ID_INFOS_SUPP=".$ID_INFOS_SUPP  ;
		$table='proc_infos_supp';
		$deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
		$data = ['message' => "Suppression effectuée avec succès"];
		session()->setFlashdata('alert', $data);
		return redirect('ihm/Information_sup');
	}

 	//Update
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Debut Gestion insertion */
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
	/* Fin Gestion insertion */

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