<?php 
/*
  @jemapess MUGISHA
 * +25768001621
 * jemapess.mugisha@mediabox.bi
 * 22/11/2023
 * CRUD secteur intervention
*/
  namespace App\Modules\ihm\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  class Secteur_Intervention extends BaseController
  {
  	protected $session;
    protected $ModelPs;
    public function __construct()
    {
      $this->library = new CodePlayHelper();
      $this->ModelPs = new ModelPs();
      $this->session = \Config\Services::session();
      $this->validation = \Config\Services::validation();
    }
    public function getBindParms($columnselect,$table,$where,$orderby)
    {
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
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
    
    function liste_view()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      $data['titre']="Liste des secteurs d'interventions";
      return view('App\Modules\ihm\Views\Secteur_Intervention_List_view',$data);
    }

    function liste_secteur()
    {

      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba');
      }
      $query_principal='SELECT 	ID_SECTEUR_INTERVENTION, DESCR_SECTEUR FROM pip_secteur_intervention WHERE 1';
      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $limit="LIMIT 0,10";
      if($_POST['length'] != -1)
      {
        $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
      }
      $order_by="";
      $order_column="";
      $order_column= array(1,'DESCR_SECTEUR',1);
      $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_SECTEUR_INTERVENTION ASC";
      $search = !empty($_POST['search']['value']) ?  (' AND ( DESCR_SECTEUR LIKE "%'.$var_search.'%")'):"";
      $search = str_replace("'","\'",$search);
      $critaire = " ";
      $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;
      $query_filter = $query_principal." ".$search." ".$critaire;
      $requete="CALL `getTable`('".$query_secondaire."')";
      $fetch_cov_frais = $this->ModelPs->datatable( $requete);
      $data = array();
      $u=1;
      foreach($fetch_cov_frais as $info)
      {
        $post = array();
        $post[]=$u++;
        $post[]= $info->DESCR_SECTEUR;
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';
        $action .= "<li>
          <a href='" . base_url("ihm/Secteur_Intervention/modification_secteur/" . $info->ID_SECTEUR_INTERVENTION) . "'><label>&nbsp;&nbsp;Modifier</label></a>
          </li>
          <li>
          <a  data-toggle='modal' data-target='#mydelete" . $info->ID_SECTEUR_INTERVENTION . "'>
          <label class='text-danger'>&nbsp;&nbsp;Supprimer</label></a>
		    </li>";

        $action .= "</ul>

          </div>
          <div class='modal fade' id='mydelete" . $info->ID_SECTEUR_INTERVENTION . "'>
          <div class='modal-dialog'>
          <div class='modal-content'>

          <div class='modal-body'>
          <center>
          <h5><strong>Voulez-vous supprimer? </strong><br><b style='background-color:prink;color:green;'>
          <i> " . $info->DESCR_SECTEUR . "</i></b>
          </h5>
          </center>
          </div>

          <div class='modal-footer'>
          <a class='btn btn-danger btn-md' href='" . base_url("ihm/Secteur_Intervention/supprimer_secteur/" . $info->ID_SECTEUR_INTERVENTION) . "'>supprimer</a>
          <button class='btn btn-primary btn-md' data-dismiss='modal'>
          Quitter
          </button>
          </div>

          </div>
          </div>
          </div>";
          
        $post[]=$action;

        $data[]=$post;  
      }

      $requeteqp="CALL `getTable`('".$query_principal."')";
      $recordsTotal = $this->ModelPs->datatable( $requeteqp);
      $requeteqf="CALL `getTable`('".$query_filter."')";
      $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" =>count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data
      );
      echo json_encode($output);
    }

    
    //ajout
    public function add_secteur()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      $data['titre']="Nouveau Secteur d'intervention";
      return view('App\Modules\ihm\Views\Secteur_Intervention_Add_View',$data);
    }

    public function Enregistrer_secteur()
    {
      $db = db_connect();
      $session  = \Config\Services::session();
      $user_id = '';
      if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      } else {
      return redirect('Login_Ptba/do_logout');
      }

      $rules = [
        'DESCR_SECTEUR' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]
      ];
      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {

        $DESCR_SECTEUR = $this->request->getPost('DESCR_SECTEUR');
        $insertInto = 'pip_secteur_intervention';
        $colum = "DESCR_SECTEUR";
        $datacolums = "'{$DESCR_SECTEUR}'";
        $this->save_all_table($insertInto, $colum, $datacolums);
        $data=['message' => lang('messages_lang.message_success')];
        session()->setFlashdata('alert', $data);
        return redirect('ihm/Secteur_Intervention/list_view');
      }else{
        return $this->Enregistrer_secteur();
      }
    }
    //supprimer
    function supprimer_secteur($id=0)
    {
      $db = db_connect();     
      $critere ="ID_SECTEUR_INTERVENTION =" .$id  ;
      $table='pip_secteur_intervention';
      $deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
      $data = ['message' => lang('messages_lang.message_success_suppr')];
      session()->setFlashdata('alert',$data);
      return redirect('ihm/Secteur_Intervention/list_view');
    }

    //--------------modification------------------------------------
    public function modification_secteur($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $sect= $this->getBindParms('ID_SECTEUR_INTERVENTION,DESCR_SECTEUR','pip_secteur_intervention','ID_SECTEUR_INTERVENTION ='.$id,' ID_SECTEUR_INTERVENTION DESC');
      $data['enjeux']= $this->ModelPs->getRequeteOne($callpsreq, $sect);
      $data['titre'] = 'Modification du secteur d\' intervention';
      return view('App\Modules\ihm\Views\Secteur_Intervention_Edit_View',$data);
    }

    public function edit_secteur()
    {
      $db = db_connect();
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      $rules = [
        'DESCR_SECTEUR' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]
      ];
      $ID_SECTEUR_INTERVENTION = $this->request->getPost('ID_SECTEUR_INTERVENTION');
      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        $DESCR_SECTEUR = $this->request->getPost('DESCR_SECTEUR');
        $ID_SECTEUR_INTERVENTION= $this->request->getPost('ID_SECTEUR_INTERVENTION');
        $DESCR_SECTEUR = str_replace("\n","",$DESCR_SECTEUR);
        $DESCR_SECTEUR = str_replace("\r","",$DESCR_SECTEUR);
        $DESCR_SECTEUR = str_replace("\t","",$DESCR_SECTEUR);
        $DESCR_SECTEUR = str_replace('"','',$DESCR_SECTEUR);
        $DESCR_SECTEUR = str_replace("'",'',$DESCR_SECTEUR);
        $where ="ID_SECTEUR_INTERVENTION = ".$ID_SECTEUR_INTERVENTION;
        $insertInto='pip_secteur_intervention';
        $colum="DESCR_SECTEUR = '".$DESCR_SECTEUR."'";
        $this->update_all_table($insertInto,$colum,$where);       
        $data = ['message' => lang('messages_lang.modification_reussi')];
        session()->setFlashdata('alert',$data);
        return redirect('ihm/Secteur_Intervention/list_view');
      }
      else
      {
        return $this->modification_secteur($ID_SECTEUR_INTERVENTION);
      }
    }
}
?>