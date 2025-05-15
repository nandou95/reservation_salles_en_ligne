<?php
/**RUGAMBA Jean Vainqueur
*Titre:CRUD observation financière
*Numero de telephone: (+257) 66 33 43 25
 *WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 09 octobre,2023
**/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
class Observation_Financiere extends BaseController
{
	function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs($db);
		$this->my_Model = new ModelPs($db);
    $this->session 	= \Config\Services::session();
    $table = new \CodeIgniter\View\Table();
    $this->validation = \Config\Services::validation();
  }
  //Liste view
  public function index($value='')
     {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
     {
      return redirect('Login_Ptba/homepage');
     }
    $data=$this->urichk();
    return view('App\Modules\ihm\Views\Observation_Financiere_Liste_View',$data);
  }
  //fonction pour affichage d'une liste
  public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'","\'",$var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column=array(1,'DESC_OBSERVATION_FINANCIER',1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_OBSERVATION_FINANCIER ASC';
     $search = !empty($_POST['search']['value']) ?  (" AND (DESC_OBSERVATION_FINANCIER LIKE '%$var_search%')"):'';
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by." ".$limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;
    $requetedebase="SELECT OBSERVATION_FINANCIER_ID,DESC_OBSERVATION_FINANCIER,IS_ACTIVE FROM observation_transfert_financier WHERE 1";
    $requetedebases=$requetedebase." ".$conditions;
    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $row->DESC_OBSERVATION_FINANCIER;
      $stat ='';

      if($row->IS_ACTIVE==0)
      {
        $stat = '<center><span class=" fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="Désactivé">&nbsp;</span></center>';
      }else{
        $stat = '<center><span class=" fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="Activé">&nbsp;</span></center>';
      }
     
      $sub_array[]=$stat;
      if($row->IS_ACTIVE==1)
      {
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'. lang('messages_lang.dropdown_link_options') .'<span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("ihm/Observation_Financiere/getOne/".$row->OBSERVATION_FINANCIER_ID)."'><label>&nbsp;&nbsp;". lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->OBSERVATION_FINANCIER_ID.")'>
        <label class='text-danger'>&nbsp;&nbsp;". lang('messages_lang.desactive_action') ."</label></a>
        </li>
        <div style='display:none;' id='message" . $row->OBSERVATION_FINANCIER_ID . "'>
              <center>
                <h5><strong>" . lang('messages_lang.confimatation_desactive_action') . "<br><center><font color='green'>" . $row->OBSERVATION_FINANCIER_ID . "&nbsp;&nbsp;" .$row->DESC_OBSERVATION_FINANCIER. "</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer" . $row->OBSERVATION_FINANCIER_ID . "'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              " . lang('messages_lang.quiter_action') . "
              </button>
              <a class='btn btn-danger btn-md' href='".base_url("ihm/Observation_Financiere/is_active/".$row->OBSERVATION_FINANCIER_ID)."'>". lang('messages_lang.desactive_action') ."</a>
            </div>";
        $action .="</ul>
        </div>";
      }
      else
      {
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'. lang('messages_lang.dropdown_link_options') .'<span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("Administration/User_profil/getOne/".$row->OBSERVATION_FINANCIER_ID)."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a  href='javascript:void(0)' onclick='show_modal(".$row->OBSERVATION_FINANCIER_ID.")'>
        <label class='text-success'>&nbsp;&nbsp;".lang('messages_lang.active_action')."</label></a>
        </li>
        <div style='display:none;' id='message" . $row->OBSERVATION_FINANCIER_ID . "'>
              <center>
                <h5><strong>" . lang('messages_lang.confimatation_active_action') . "<br><center><font color='green'>" . $row->OBSERVATION_FINANCIER_ID . "&nbsp;&nbsp;" .$row->DESC_OBSERVATION_FINANCIER. "</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer" . $row->OBSERVATION_FINANCIER_ID . "'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              " . lang('messages_lang.quiter_action') . "
              </button>
              <a class='btn btn-danger btn-md' href='".base_url("ihm/Observation_Financiere/is_active/".$row->OBSERVATION_FINANCIER_ID)."'>". lang('messages_lang.active_action') ."</a>
            </div>";
        $action .="</ul>
        </div>";
      }     
      $sub_array[]=$action;
      $data[] = $sub_array;
    }
    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //fonction pour l'activation/désactivation
  function is_active($ID)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_user = $this->getBindParms('OBSERVATION_FINANCIER_ID,IS_ACTIVE', 'observation_transfert_financier','OBSERVATION_FINANCIER_ID='.$ID,'OBSERVATION_FINANCIER_ID ASC');
    $users= $this->ModelPs->getRequeteOne($callpsreq, $bind_user);

    if($users['IS_ACTIVE']==0)
    {
      $updateTable='observation_transfert_financier';
      $critere = "OBSERVATION_FINANCIER_ID=".$ID;
      $datatoupdate= 'IS_ACTIVE=1';
      $bindparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
      $data = ['message' => lang('messages_lang.labelle_et_mod_question_succes')];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Observation_Financiere');
    }
    elseif($users['IS_ACTIVE']==1)
    {
      $updateTable='observation_transfert_financier';
      $critere = "OBSERVATION_FINANCIER_ID=".$ID;
      $datatoupdate= 'IS_ACTIVE=0';
      $bindparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
      $data = ['message' => lang('messages_lang.labelle_et_mod_question_succes_d')];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Observation_Financiere');
    }
  }  

  //fonctin  pour afficher le formulaire
  public function ajout($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data=$this->urichk();
    return view('App\Modules\ihm\Views\Observation_Financiere_Add_View',$data);   
  }

  //Formulaire pour inserer les donnees dans la table
  public function insert()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $rule =  [
      'DESC_OBSERVATION_FINANCIER' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ]
    ];

    $this->validation->setRules($rule);
    
    if($this->validation->withRequest($this->request)->run())
    {
      $DESC_OBSERVATION_FINANCIER = $this->request->getPost('DESC_OBSERVATION_FINANCIER');
      $IS_ACTIVE = 1;
      $insertIntoTable='observation_transfert_financier';
      $datatoinsert = '"'.$DESC_OBSERVATION_FINANCIER.'",'.$IS_ACTIVE;
      $bindparams =[$insertIntoTable,$datatoinsert];
      $insertRequete = "CALL `insertLastIdIntoTable`(?,?);";
      $this->ModelPs->createUpdateDelete($insertRequete,$bindparams);;
      $data=['message' => lang('messages_lang.message_success')];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Observation_Financiere');
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
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data=$this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $IMPORTnd_proc = $this->getBindParms('OBSERVATION_FINANCIER_ID,DESC_OBSERVATION_FINANCIER','observation_transfert_financier','OBSERVATION_FINANCIER_ID='.$id,'OBSERVATION_FINANCIER_ID ASC');
    $data['financ']= $this->ModelPs->getRequeteOne($callpsreq, $IMPORTnd_proc);
    return view('App\Modules\ihm\Views\Observation_Financiere_Update_View',$data);
  }

  //Mise à jour des observations de transfert financiers
  public function update()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_OBSERVATION_FINANCIERES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $OBSERVATION_FINANCIER_ID  =$this->request->getPost('OBSERVATION_FINANCIER_ID');
    $rule =  [
      'DESC_OBSERVATION_FINANCIER' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ]
    ];

    $this->validation->setRules($rule);
    
    if($this->validation->withRequest($this->request)->run())
    {
      $DESC_OBSERVATION_FINANCIER = $this->request->getPost('DESC_OBSERVATION_FINANCIER');
      $updateTable='observation_transfert_financier';
      $critere = "OBSERVATION_FINANCIER_ID=".$OBSERVATION_FINANCIER_ID;
      $datatoupdate= 'DESC_OBSERVATION_FINANCIER="'.$DESC_OBSERVATION_FINANCIER.'"';
      $IMPORTndparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $IMPORTndparams);
      $data = ['message' => lang('messages_lang.modification_reussi')];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Observation_Financiere');
    }
    else
    {
      return $this->getOne($OBSERVATION_FINANCIER_ID);
    }
  }

  public function getBindParms($columnselect,$table,$where,$orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }
}	
?>