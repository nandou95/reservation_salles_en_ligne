<?php 
/*
  @author MUNEZERO Sonia
 * +25765165772
 * sonia@mediabox.bi
 * 15/11/2023
 * CRUD Enjeux
*/
  namespace App\Modules\ihm\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  class Enjeux extends BaseController
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

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      // code...
      $db = db_connect();
      // print_r($db->lastQuery);die();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }
    /*Debut Gestion insertion */
    public function save_all_table($table,$columsinsert,$datacolumsinsert)
    {
      //$columsinsert: Nom des colonnes separe par,
      //$datacolumsinsert : les donnees a inserer dans les colonnes
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
    //pour le view
  	function add_enjeux()
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

      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data['titre']= lang('messages_lang.nouveau_enjeux');
      return view('App\Modules\ihm\Views\Enjeux_Add_View',$data);
  	}

    function ajouter_enjeux()
    {
      $db = db_connect();
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

       if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $rules = [
        'DESCR_ENJEUX' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'. lang('messages_lang.message_champs_obligatoire') .'</font>'
          ]
        ]
      ];
      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        $DESCR_ENJEUX = $this->request->getPost('DESCR_ENJEUX');
        $DESCR_ENJEUX = str_replace("\n","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("\r","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("\t","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace('"','',$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("'",'',$DESCR_ENJEUX);
        $insertInto='enjeux';
        $colum="DESCR_ENJEUX";
        $datacolums="'".$DESCR_ENJEUX."'";
        $this->save_all_table($insertInto,$colum,$datacolums); 
       
        $data=['message' => lang('messages_lang.message_success')];
        session()->setFlashdata('alert',$data);
        return redirect('ihm/Enjeux/liste_view');
      }
      else
      {
        return $this->ajouter_enjeux();
      }
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

      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $data['titre'] = lang('messages_lang.titre_en_jeux');
      return view('App\Modules\ihm\Views\Enjeux_Liste_View',$data);
    }

    function liste_enjeux()
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

      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $query_principal='SELECT ID_ENJEUX,DESCR_ENJEUX FROM enjeux WHERE 1';

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $limit="LIMIT 0,10";
      if($_POST['length'] != -1)
      {
        $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
      }

      $order_by="";
      $order_column="";
      $order_column= array(1,'DESCR_ENJEUX',1);

      $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_ENJEUX ASC";
      $search = !empty($_POST['search']['value']) ?  (' AND ( DESCR_ENJEUX LIKE "%'.$var_search.'%")'):"";
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
        $post[]= $info->DESCR_ENJEUX;
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> '.lang('messages_lang.labelle_option').'  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("ihm/Enjeux/edit_view/".$info->ID_ENJEUX)."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$info->ID_ENJEUX.")'>
        <label class='text-danger'>&nbsp;&nbsp;".lang('messages_lang.supprimer_action')."</label></a>

        </li>
        <div style='display:none;' id='message".$info->ID_ENJEUX."'>
              <center>
                <h5><strong>".lang('messages_lang.Voulez_vous_supprimer?')."<br><center><font color='green'>".$info->DESCR_ENJEUX."</font>  </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer".$info->ID_ENJEUX."'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              ".lang('messages_lang.quiter_action')."
              </button>
              <a class='btn btn-danger btn-md' href='".base_url("ihm/Enjeux/supprimer_enjeux/".$info->ID_ENJEUX)."'>".lang('messages_lang.supprimer_action')."</a>
            </div>";
        $action .="</ul>
        </div>
        ";
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

    function edit_view($id=0)
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

      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $enjeux = $this->getBindParms('ID_ENJEUX,DESCR_ENJEUX', 'enjeux', 'ID_ENJEUX = '.$id,' ID_ENJEUX DESC');
      $data['enjeux']= $this->ModelPs->getRequeteOne($callpsreq, $enjeux);
      $data['titre'] = 'Modification d\'Enjeux';
      return view('App\Modules\ihm\Views\Enjeux_Edit_View',$data);
    }

    function edit_enjeux()
    {
      $db = db_connect();
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

       if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $rules = [
        'DESCR_ENJEUX' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]
      ];
      $ID_ENJEUX = $this->request->getPost('ID_ENJEUX');

      $this->validation->setRules($rules);

      if($this->validation->withRequest($this->request)->run())
      {
        $DESCR_ENJEUX = $this->request->getPost('DESCR_ENJEUX');
        $ID_ENJEUX = $this->request->getPost('ID_ENJEUX');
        $DESCR_ENJEUX = str_replace("\n","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("\r","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("\t","",$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace('"','',$DESCR_ENJEUX);
        $DESCR_ENJEUX = str_replace("'",'',$DESCR_ENJEUX);
        $where ="ID_ENJEUX = ".$ID_ENJEUX;
        $insertInto='enjeux';
        $colum="DESCR_ENJEUX = '".$DESCR_ENJEUX."'";
        $this->update_all_table($insertInto,$colum,$where);
        $data = ['message' => lang('messages_lang.modification_reussi')];
        session()->setFlashdata('alert', $data);
        return redirect('ihm/Enjeux/liste_view');
      }
      else
      {
        return $this->edit_view($ID_ENJEUX);
      }
    }

    function supprimer_enjeux($id=0)
    {
      $db = db_connect();
      $session  = \Config\Services::session(); 
      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_ENJEUX')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }    
      $critere ="ID_ENJEUX =" .$id  ;
      $table='enjeux';
      $deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
      $data = ['message' => lang('messages_lang.message_success_suppr')];
      session()->setFlashdata('alert', $data);
      return redirect('ihm/Enjeux/liste_view');
    }
  }
?>