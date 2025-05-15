<?php
/**RUGAMBA Jean Vainqueur
*Titre:CRUD DE PROCESSUS
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 15 Novembre,2023
**/
/*NDERAGAKURA ALAIN CHARBEL
Modification
*Email: charbel@mediabox.bi
tel:62005522/76887837
*Date: 1 Decembre,2023
*/
/*mugisha jemapess
*internationalisation
*@jemapess.mugisha@mediabox.bi
*tel:68 001 621
*date: 8 fevrier,2024
*/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
class Processus extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
	}

	// fonction pour aller a la page de la liste
	public function index($value='')
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		return view('App\Modules\ihm\Views\Processus_List_View',$data);   
	}

	// fonction pour la liste
	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
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
		$order_column = array(1,'NOM_PROCESS','TABLE_NAME','LINK',1, 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NOM_PROCESS ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (NOM_PROCESS LIKE "%' . $var_search . '%" OR LINK LIKE "%' . $var_search . '%" OR TABLE_NAME LIKE "%' . $var_search . '%")') : '';
		// Condition pour la requête principale
		$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critaire.' '.$search.' '.$group;
		$requetedebase = "SELECT PROCESS_ID,NOM_PROCESS,TABLE_NAME,STATUT,LINK FROM proc_process WHERE 1";
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
			$sub_array[] = $row->NOM_PROCESS;
			$sub_array[] = $row->TABLE_NAME;
			$sub_array[] = $row->LINK;
			$stat ='';

			if ($row->STATUT == 0) {
				$stat = '<center><span class=" fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title='. lang('messages_lang.title_desactiver').'>&nbsp;</span></center>';
			} else {
				$stat = '<center><span class=" fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title='. lang('messages_lang.title_activer').'>&nbsp;</span></center>';
			}
			$sub_array[]=$stat;
			
			if ($row->STATUT == 1) {
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .= "<li>
				<a href='" . base_url("ihm/Processus/getOne/".$row->PROCESS_ID) . "'><label>&nbsp;&nbsp;". lang('messages_lang.labelle_et_modif')."</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(" . $row->PROCESS_ID . ")' title=". lang('messages_lang.labelle_et_desactiver')." ><label>&nbsp;&nbsp;<font color='red'>". lang('messages_lang.labelle_et_desactiver')."</font></label></a>

				</li>
				<div style='display:none;' id='message" . $row->PROCESS_ID . "'>
				<center>
				<h5><strong>". lang('messages_lang.labelle_et_mod_question_b')."<br><center><font color='green'>" . $row->NOM_PROCESS . "&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
				</h5>
				</center>
				</div>
				<div style='display:none;' id='footer" . $row->PROCESS_ID . "'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				". lang('messages_lang.labelle_et_mod_quiter')."
			  </button>
				<a href='" . base_url("ihm/Processus/is_active/".$row->PROCESS_ID) . "' class='btn btn-danger btn-md'>".lang('messages_lang.labelle_et_desactiver')."</a>
				</div>";
			}
			else {

				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .= "<li>
				<a href='" . base_url("ihm/Processus/getOne/".$row->PROCESS_ID) . "'><label>&nbsp;&nbsp;". lang('messages_lang.bouton_modifier')."</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(" . $row->PROCESS_ID . ")' title='". lang('messages_lang.active_action')."' ><label>&nbsp;&nbsp;<font color='green'>". lang('messages_lang.active_action')."</font></label></a>

				</li>
				<div style='display:none;' id='message" . $row->PROCESS_ID . "'>
					<center>
					<h5><strong>". lang('messages_lang.labelle_et_mod_question_a')."<br><center><font color='green'>" . $row->NOM_PROCESS . "&nbsp;&nbsp;</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
					</h5>
					</center>
				</div>
				<div style='display:none;' id='footer" . $row->PROCESS_ID . "'>
					<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
					". lang('messages_lang.labelle_et_mod_quiter')."
					</button>
					<a href='" . base_url("ihm/Processus/is_active/".$row->PROCESS_ID) . "' class='btn btn-danger btn-md'>". lang('messages_lang.active_action')."</a>
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

	//fonction pour l'activation/désactivation
	function is_active($PROCESS_ID)
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_process = $this->getBindParms('PROCESS_ID,NOM_PROCESS,STATUT,TABLE_NAME', 'proc_process', 'PROCESS_ID='.$PROCESS_ID,'PROCESS_ID ASC');
		$process= $this->ModelPs->getRequeteOne($callpsreq, $bind_process);

		if($process['STATUT']==0)
		{
			$table='proc_process';
      $conditions='PROCESS_ID ='.$PROCESS_ID;
      $datatomodifie= 'STATUT=1';
      $this->update_all_table($table,$datatomodifie,$conditions);
			$data = ['message' => lang('messages_lang.labelle_et_mod_question_succes')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Processus');
		}
		elseif($process['STATUT']==1)
		{
			$table='proc_process';
      $conditions='PROCESS_ID ='.$PROCESS_ID;
      $datatomodifie= 'STATUT=0';
      $this->update_all_table($table,$datatomodifie,$conditions);
			$data = ['message' => lang('messages_lang.labelle_et_mod_question_succes_d')];
			session()->setFlashdata('alert', $data);
			return redirect('ihm/Processus');
		}
	}

	//fonctin  pour afficher le formulaire
	public function ajout($value='')
	{
		$session  = \Config\Services::session();
   
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return  redirect('Login_Ptba/do_logout');
    }

     if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		return view('App\Modules\ihm\Views\Processus_Add_View',$data);   
	}

	//Formulaire pour inserer les donnees dans la table
	public function insert()
	{
		$session  = \Config\Services::session();
   
    $USER_ID_ENG='';
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return  redirect('Login_Ptba/do_logout');
    }
     if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
      {
      return redirect('Login_Ptba/homepage');
       }
    $db = db_connect();
    //Récupération des inputs
    $PROCESS_NOM = $this->request->getPost('PROCESS_NOM');
    $TABLE = $this->request->getPost('TABLE');
    $LINK = $this->request->getPost('LINK');
    $STATUT = 1; 
    //Form validation
    $rules = [
      'PROCESS_NOM' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'TABLE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'LINK' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ]
    ];
    $this->validation->setRules($rules);
    if($this->validation->withRequest($this->request)->run())
    {
      $columsinsert="NOM_PROCESS,TABLE_NAME,STATUT,LINK";
      $datatoinsert="'".str_replace("'","\'",$PROCESS_NOM)."','".$TABLE."',".$STATUT.",'".$LINK."'";
      $table='proc_process';
      $this->save_all_table($table,$columsinsert,$datatoinsert);
			$data = ['message' => lang('messages_lang.message_success') ];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Processus');
    }
    else
    {
    	return $this->ajout();
    }	
	}
	//fonction get pour recuperer les données 
	public function getOne($PROCESS_ID)
	{
		$session  = \Config\Services::session();
   
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_process = $this->getBindParms('PROCESS_ID,NOM_PROCESS,STATUT,TABLE_NAME,LINK','proc_process','PROCESS_ID='.$PROCESS_ID,'PROCESS_ID ASC');
		$data['process'] = $this->ModelPs->getRequeteOne($callpsreq,$bind_process);
		return view('App\Modules\ihm\Views\Processus_Update_View',$data); 
	}

	//Mise à jour du processus
	public function update()
	{
		$session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return  redirect('Login_Ptba/do_logout');
    }

     if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_PROCESSUS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    //Récupération des inputs
    $PROCESS_ID = $this->request->getPost('PROCESS_ID');
    $PROCESS_NOM = $this->request->getPost('PROCESS_NOM');
    $TABLE = $this->request->getPost('TABLE');
    $LINK = $this->request->getPost('LINK');
    $STATUT = 1; 
    //Form validation
    $rules = [
      'PROCESS_NOM' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'TABLE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'LINK' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ]

    ];

    $this->validation->setRules($rules);
    if($this->validation->withRequest($this->request)->run())
    {
      $table='proc_process';
      $conditions='PROCESS_ID ='.$PROCESS_ID;
      $datatomodifie= 'NOM_PROCESS="'.str_replace("'","\'",$PROCESS_NOM).'",TABLE_NAME="'.$TABLE.'",LINK="'.$LINK.'"';
      $this->update_all_table($table,$datatomodifie,$conditions);

      $data = ['message' => lang('messages_lang.labelle_message_update_success')];
      session()->setFlashdata('alert', $data);

      return redirect('ihm/Processus');
    }
    else{

    	return $this->getOne($PROCESS_ID);
    }
	}

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
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
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

  //Update
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }	
}
?>