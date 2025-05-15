<?php 
/*
  @jemapess MUGISHA
 * +25768001621
 * jemapess.mugisha@mediabox.bi
 * 23/11/2023
 * CRUD Categorie_libelle
*/
  namespace App\Modules\ihm\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  class Categorie_Libelle extends BaseController
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
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete,$bindparams);
    }
    // pour le view
  	function add_categorie()
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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data['titre']= lang('messages_lang.titre_nouv_categ_libel');
      return view('App\Modules\ihm\Views\Categorie_Libelle_Add_View',$data);
  	}

    function ajouter_Categorie()
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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $rules = [
        'CATEGORIE_LIBELLE' => [
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
        $CATEGORIE_LIBELLE = $this->request->getPost('CATEGORIE_LIBELLE');
        $CATEGORIE_LIBELLE = str_replace("\n","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("\r","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("\t","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace('"','',$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("'",'',$CATEGORIE_LIBELLE);

        $insertInto='pip_categorie_libelle';
        $colum="CATEGORIE_LIBELLE";
        $datacolums="'".$CATEGORIE_LIBELLE."'";
        $this->save_all_table($insertInto,$colum,$datacolums); 
        $data=['message' => lang('messages_lang.message_success')];
        session()->setFlashdata('alert', $data);
        return redirect('ihm/Categorie_Libelle/liste_view');
      }
      else
      {
        return $this->ajouter_categorie();
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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data['titre']= lang('messages_lang.titre_list_categ_libel');
      return view('App\Modules\ihm\Views\Categorie_Libelle_Liste_View',$data);
    }

    function liste_categorie()
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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $query_principal='SELECT ID_CATEGORIE_LIBELLE,CATEGORIE_LIBELLE FROM pip_categorie_libelle WHERE 1';

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $limit="LIMIT 0,10";
      if($_POST['length'] != -1)
      {
        $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
      }

      $order_by="";
      $order_column="";
      $order_column= array(1,'CATEGORIE_LIBELLE',1);

      $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY CATEGORIE_LIBELLE ASC";
      $search = !empty($_POST['search']['value']) ?  (' AND ( CATEGORIE_LIBELLE LIKE "%'.$var_search.'%")'):"";
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
        $post[]= $info->CATEGORIE_LIBELLE;
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .= "<li>
        <a href='".base_url("ihm/Categorie_Libelle/edit_view/".$info->ID_CATEGORIE_LIBELLE)."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$info->ID_CATEGORIE_LIBELLE.")'>
        <label class='text-danger'>&nbsp;&nbsp;Supprimer</label></a>
		    </li>
        <div style='display:none;' id='message".$info->ID_CATEGORIE_LIBELLE."'>
              <center>
                <h5><strong>".lang('messages_lang.question_supprimer_sfp')."<br><center><font color='green'>&nbsp;&nbsp;".$info->CATEGORIE_LIBELLE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
                </h5>
              </center>
            </div>
            <div style='display:none;' id='footer".$info->ID_CATEGORIE_LIBELLE."'>
              <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
              ".lang('messages_lang.quiter_action')."
              </button>
              <a href='".base_url("ihm/Categorie_Libelle/supprimer_categorie/".$info->ID_CATEGORIE_LIBELLE)."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
            </div>";

        $action .= "</ul>

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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
       return redirect('Login_Ptba/homepage');
      }
      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $categ = $this->getBindParms('ID_CATEGORIE_LIBELLE,CATEGORIE_LIBELLE','pip_categorie_libelle','ID_CATEGORIE_LIBELLE = '.$id,' ID_CATEGORIE_LIBELLE DESC');
      $data['enjeux']= $this->ModelPs->getRequeteOne($callpsreq, $categ);
      $data['titre']= lang('messages_lang.titre_modif_categ_libel');
      return view('App\Modules\ihm\Views\Categorie_Libelle_Edit_View',$data);
    }

    function edit_categorie()
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
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $rules = [
        'CATEGORIE_LIBELLE1' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]
      ];
      $ID_CATEGORIE_LIBELLE = $this->request->getPost('ID_CATEGORIE_LIBELLE');

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        $CATEGORIE_LIBELLE = $this->request->getPost('CATEGORIE_LIBELLE1');
        $ID_CATEGORIE_LIBELLE = $this->request->getPost('ID_CATEGORIE_LIBELLE');

        $CATEGORIE_LIBELLE = str_replace("\n","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("\r","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("\t","",$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace('"','',$CATEGORIE_LIBELLE);
        $CATEGORIE_LIBELLE = str_replace("'",'',$CATEGORIE_LIBELLE);
        $where ="ID_CATEGORIE_LIBELLE = ".$ID_CATEGORIE_LIBELLE;
        $insertInto='pip_categorie_libelle';
        $colum="CATEGORIE_LIBELLE = '".$CATEGORIE_LIBELLE."'";
        $this->update_all_table($insertInto,$colum,$where);
        $data = ['message' => lang('messages_lang.modification_reussi')];
        session()->setFlashdata('alert', $data);
        return redirect('ihm/Categorie_Libelle/liste_view');
      }
      else
      {
        return $this->edit_view($ID_CATEGORIE_LIBELLE);
      }
    }

    function supprimer_categorie($id=0)
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $db = db_connect();     
      $critere ="ID_CATEGORIE_LIBELLE=".$id;
      $table='pip_categorie_libelle';
      $deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $delete=$this->ModelPs->createUpdateDelete($deleteRequete,$deleteparams);
      $data = ['message' => lang('messages_lang.message_success_suppr')];
      session()->setFlashdata('alert',$data);

      return redirect('ihm/Categorie_Libelle/liste_view');
    }
  }
?>