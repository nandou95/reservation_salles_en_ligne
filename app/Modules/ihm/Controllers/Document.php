<?php 

/**
 * auteur:christa
 * tache: crud des actions
 * date: le 15/11/2023
 * email:christa@mediabox.bi
 */
/**
 * auteur:Douce
 * tache: amelioration du crud des actions
 * date: le 22/11/2023
 * email:douce@mediabox.bi
 */
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Document extends BaseController
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
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		$data['title']= lang('messages_lang.labelle_titre_liste');
		return view('App\Modules\ihm\Views\Document_View',$data);
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
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
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = array(1,'DESC_DOCUMENT','DOCUMENT_TYPE_ID',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY DESCR_ACTION ASC';
		$search = !empty($_POST['search']['value']) ?  (' AND (DESC_DOCUMENT LIKE "%'.$var_search.'%" OR DOCUMENT_TYPE_ID LIKE "%'.$var_search.'%" )'):'';
		// Condition pour la requête principale
		$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critaire.' '.$search.' '.$group;
		$requetedebase = "SELECT `DOCUMENT_ID`,`DESC_DOCUMENT`,`DOCUMENT_TYPE_ID` FROM `proc_document` WHERE 1";
		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;
		$Televerser="Téléverser";
		$Generer="Générer";
		$stat ='';
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->DESC_DOCUMENT;
			if ($row->DOCUMENT_TYPE_ID==1) {
				$sub_array[] = $Televerser;
			}else{
				$sub_array[] = $Generer;
			}
			$stat = '
				<div class="dropdown" style="color:#fff;">
					<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

			$stat .= "
						<li>
							<a href='".base_url("ihm/Document/getOne/".$row->DOCUMENT_ID)."'><label>&nbsp;&nbsp;".lang('messages_lang.labelle_et_modif')."</label></a>
						</li>";
			$stat.= "  
						<li>
							<a href='javascript:void(0)' onclick='show_modal(".$row->DOCUMENT_ID.")' title=".lang('messages_lang.supprimer_action')." >
							<label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.supprimer_action')."</font></label></a>
						</li>";
			$stat.= " 
				</ul>
			</div>
			<div style='display:none;' id='message".$row->DOCUMENT_ID."'>
				<center>
					<h5><strong>".lang('messages_lang.labelle_et_mod_question_b')."<br><center><font color='green'>".$row->DESC_DOCUMENT."&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
					</h5>
				</center>
			</div>
			<div style='display:none;' id='footer".$row->DOCUMENT_ID."'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				".lang('messages_lang.labelle_et_mod_quiter')."
			  </button>
				<a href='".base_url("ihm/Document/delete/".$row->DOCUMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
			</div>";
			$sub_array[]=$stat;
			$data[] = $sub_array;
		}
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
		$output= array(
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
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		$document = array(
			array('ID' => 1, 'DES' => 'Téléverser'),
			array('ID' => 2, 'DES' => 'Générer')
		);
		$data['document']=$document;
		$data['title']= lang('messages_lang.labelle_et_titre_ajout_documment');
		return view('App\Modules\ihm\Views\Document_New_View',$data);
	}
	
	//fonction pour l insertion des socuments
	function save()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$user_id =$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DESC_DOCUMENT' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'DOCUMENT_TYPE_ID' => [
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
			$DESC_DOCUMENT = $this->request->getPost('DESC_DOCUMENT');
			$DOCUMENT_TYPE_ID = $this->request->getPost('DOCUMENT_TYPE_ID');
			$DESC_DOCUMENT = str_replace("\n","",$DESC_DOCUMENT);
			$DESC_DOCUMENT = str_replace("\r","",$DESC_DOCUMENT);
			$DESC_DOCUMENT = str_replace("\t","",$DESC_DOCUMENT);
			$DESC_DOCUMENT = str_replace('"','',$DESC_DOCUMENT);
			$DOCUMENT_NAME = str_replace(" ","_",$DESC_DOCUMENT);
			$DOCUMENT_NAME = str_replace("'","_",$DOCUMENT_NAME);

			$insertRequete = "CALL `insertIntoTable`(?,?);";
			$insertIntoTable='proc_document';
			$columns="DESC_DOCUMENT,DOCUMENT_TYPE_ID,DOCUMENT_NAME";
			$datacolums=' "'.$DESC_DOCUMENT.'",'.$DOCUMENT_TYPE_ID.',"'.$DOCUMENT_NAME.'"';
			$this->save_all_table($insertIntoTable,$columns,$datacolums); 

			$data=['message' => lang('messages_lang.message_success')];
			session()->setFlashdata('alert',$data);
			return redirect('ihm/Document');
		}
		else
		{
			return $this->new();
		}
	}

	
	//fonction pour afficher le formulaire de la modification
	public function getOne($id)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_respona= $this->getBindParms('DOCUMENT_ID,DESC_DOCUMENT,DOCUMENT_TYPE_ID','proc_document','DOCUMENT_ID='.$id,'DOCUMENT_ID ASC');
		$data['doc']= $this->ModelPs->getRequeteOne($callpsreq,$bind_respona);	

			$document = array(
			array('ID'=>1,'DES'=>'Téléverser'),
			array('ID'=>2,'DES'=>'Générer')
		);
		$data['document']=$document;
		$data['title']=lang('messages_lang.labelle_message_update_success');
		return view('App\Modules\ihm\Views\Document_Update_View',$data);
	}
	//fonction pour la modification
	public function update()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }  	
		$db = db_connect();
		$DOCUMENT_ID = $this->request->getPost('DOCUMENT_ID');
		$DESC_DOCUMENT = $this->request->getPost('DESC_DOCUMENT');
		$DOCUMENT_TYPE_ID = $this->request->getPost('DOCUMENT_TYPE_ID');
		$DESC_DOCUMENT = str_replace("\n","",$DESC_DOCUMENT);
		$DESC_DOCUMENT = str_replace("\r","",$DESC_DOCUMENT);
		$DESC_DOCUMENT = str_replace("\t","",$DESC_DOCUMENT);
		$DESC_DOCUMENT = str_replace('"','',$DESC_DOCUMENT);

		$updateTable='proc_document';
		$critere = "DOCUMENT_ID=".$DOCUMENT_ID;
		$datatoupdate = 'DESC_DOCUMENT="'.$DESC_DOCUMENT.'",DOCUMENT_TYPE_ID="'.$DOCUMENT_TYPE_ID.'"';
		$bindparams =[$updateTable,$datatoupdate,$critere];
		$insertRequete = 'CALL `updateData`(?,?,?);';
		$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
		$data = ['message' => lang('messages_lang.labelle_message_update_success')];
		session()->setFlashdata('alert', $data);
		$output = array('status' => TRUE);
		return redirect('ihm/Document');
	}

	//fonction pour la suppression
	public function delete($DOCUMENT_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_DOCUMENTS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$db = db_connect();     
		$critere ="DOCUMENT_ID=".$DOCUMENT_ID  ;
		$table='proc_document';
		$deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
		$data = ['message' => lang('messages_lang.labelle_message_suprimmer_success')];
		session()->setFlashdata('alert', $data);
		return redirect('ihm/Document');
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
	public function getBindParms($columnselect,$table,$where,$orderby)
	{
    // code...
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>