<?php 
/*
  @author MUNEZERO Sonia
 * +25765165772
 * sonia@mediabox.bi
 * 26/12/2023
 * CRUD Pourcentage des nomenclature budgetaire
*/

  namespace App\Modules\pip\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Nomenclature_Pourcentage extends BaseController
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
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
    }

    // pour le view
  	function add_pourcentage()
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data['titre']=lang('messages_lang.titre_pourcentage_nomen');

      $callpsreq = "CALL `getRequete`(?,?,?,?);"; 
      $nomenclature = $this->getBindParms('ID_NOMENCLATURE,DESCR_NOMENCLATURE', 'pip_nomenclature_budgetaire', '1' , ' DESCR_NOMENCLATURE DESC');
      $data['get_nomenclature']= $this->ModelPs->getRequete($callpsreq, $nomenclature);

      return view('App\Modules\pip\Views\Nomenclature_Pourcentage_Add_View',$data);
  	}

    function ajouter_pourcentage()
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      
      $champ_vide = lang('messages_lang.message_champs_obligatoire');
      $rules = [
        'ID_NOMENCLATURE' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
          ]
        ],
        'POURCENTAGE_NOMENCLATURE' => [
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
        $ID_NOMENCLATURE = $this->request->getPost('ID_NOMENCLATURE');
        $POURCENTAGE_NOMENCLATURE = $this->request->getPost('POURCENTAGE_NOMENCLATURE');
        $POURCENTAGE_NOMENCLATURE = str_replace(",",".",$POURCENTAGE_NOMENCLATURE);

        $insertInto='pip_nomenclature_budgetaire_pourcentage';
        $colum="ID_NOMENCLATURE,POURCENTAGE_NOMENCLATURE";
        $datacolums=$ID_NOMENCLATURE.",'".$POURCENTAGE_NOMENCLATURE."'";
        $this->save_all_table($insertInto,$colum,$datacolums); 
       
        $data=['message' => lang('messages_lang.labelle_et_succes_ok')];
        session()->setFlashdata('alert', $data);

        return redirect('pip/Nomenclature_Pourcentage/liste_pourcentage_nomenclature');
      }
      else
      {
        return $this->ajouter_pourcentage();
      }
    }

    function liste_pourcentage_nomenclature()
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data['titre']=lang('messages_lang.titre_pourcentage_nomen_list');
      return view('App\Modules\pip\Views\Nomenclature_Pourcentage_List_View',$data);
    }

    function liste_nomen_pourcent()
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $query_principal='SELECT ID_NOMENCLATURE_BUDGET_POURCENT,pnb.DESCR_NOMENCLATURE, POURCENTAGE_NOMENCLATURE FROM pip_nomenclature_budgetaire_pourcentage pourcent JOIN pip_nomenclature_budgetaire pnb ON pnb.ID_NOMENCLATURE=pourcent.ID_NOMENCLATURE WHERE 1';

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $limit="LIMIT 0,10";
      if($_POST['length'] != -1)
      {
        $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
      }

      $order_by="";
      $order_column="";
      $order_column= array(1,'pnb.DESCR_NOMENCLATURE','POURCENTAGE_NOMENCLATURE',1);

      $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_NOMENCLATURE_BUDGET_POURCENT DESC";
      $search = !empty($_POST['search']['value']) ?  (' AND ( pnb.DESCR_NOMENCLATURE LIKE "%'.$var_search.'%" OR POURCENTAGE_NOMENCLATURE LIKE "%'.$var_search.'%")'):"";
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
        $post[]= $info->DESCR_NOMENCLATURE;
        $post[]= $info->POURCENTAGE_NOMENCLATURE.' %';

        $suppr = lang('messages_lang.supprimer_action');
        $modif = lang('messages_lang.bouton_modifier');

        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("pip/Nomenclature_Pourcentage/edit_pourcentage/".$info->ID_NOMENCLATURE_BUDGET_POURCENT)."'><label>{$modif}</label></a>
        </li>
        <li>

        <a class='btn-sm' onclick='supprimer(".$info->ID_NOMENCLATURE_BUDGET_POURCENT.")'>
        <label class='text-danger'>{$suppr}</label></a>
        </li>";
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

    function edit_pourcentage($id=0)
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $data['titre'] = lang('messages_lang.titre_pourcentage_nomen_modif');

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $get_pourcent = $this->getBindParms('ID_NOMENCLATURE_BUDGET_POURCENT,POURCENTAGE_NOMENCLATURE,ID_NOMENCLATURE', 'pip_nomenclature_budgetaire_pourcentage', 'ID_NOMENCLATURE_BUDGET_POURCENT = '.$id,' ID_NOMENCLATURE_BUDGET_POURCENT DESC');
      $data['pourcent']= $this->ModelPs->getRequeteOne($callpsreq, $get_pourcent);

      $nomenclature = $this->getBindParms('ID_NOMENCLATURE,DESCR_NOMENCLATURE', 'pip_nomenclature_budgetaire', '1' , ' DESCR_NOMENCLATURE DESC');
      $data['get_nomenclature']= $this->ModelPs->getRequete($callpsreq, $nomenclature);
      return view('App\Modules\pip\Views\Nomenclature_Pourcentage_Edit_View',$data);
    }

    function edit_nomen_pourcent()
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      
      $champ_vide = lang('messages_lang.message_champs_obligatoire');
        $rules = [
        'ID_NOMENCLATURE' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
          ]
        ],
        'POURCENTAGE_NOMENCLATURE' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
          ]
        ]
      ];

      $ID_NOMENCLATURE_BUDGET_POURCENT = $this->request->getPost('ID_NOMENCLATURE_BUDGET_POURCENT');

      $this->validation->setRules($rules);

      if($this->validation->withRequest($this->request)->run())
      {
        $ID_NOMENCLATURE = $this->request->getPost('ID_NOMENCLATURE');
        $POURCENTAGE_NOMENCLATURE = $this->request->getPost('POURCENTAGE_NOMENCLATURE');
        $POURCENTAGE_NOMENCLATURE = str_replace(",",".",$POURCENTAGE_NOMENCLATURE);
        $ID_NOMENCLATURE_BUDGET_POURCENT = $this->request->getPost('ID_NOMENCLATURE_BUDGET_POURCENT');

        
        $where ="ID_NOMENCLATURE_BUDGET_POURCENT = ".$ID_NOMENCLATURE_BUDGET_POURCENT;
        $insertInto='pip_nomenclature_budgetaire_pourcentage';
        $colum="ID_NOMENCLATURE = ".$ID_NOMENCLATURE.", POURCENTAGE_NOMENCLATURE='".$POURCENTAGE_NOMENCLATURE."'";
        $this->update_all_table($insertInto,$colum,$where);
       
        $data=['message' => lang('messages_lang.labelle_message_update_success')];
        session()->setFlashdata('alert', $data);

        return redirect('pip/Nomenclature_Pourcentage/liste_pourcentage_nomenclature');
      }
      else
      {
        return $this->edit_pourcentage($ID_NOMENCLATURE_BUDGET_POURCENT);
      }
    }

    function suppresion($id=0)
    {
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

      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $data=$this->urichk();
      $get_infos="SELECT ID_NOMENCLATURE_BUDGET_POURCENT,pnb.DESCR_NOMENCLATURE, POURCENTAGE_NOMENCLATURE FROM pip_nomenclature_budgetaire_pourcentage pourcent JOIN pip_nomenclature_budgetaire pnb ON pnb.ID_NOMENCLATURE=pourcent.ID_NOMENCLATURE WHERE ID_NOMENCLATURE_BUDGET_POURCENT = ".$id;
      $get_infos='CALL `getList`("'.$get_infos.'")';
      $pourcent_sup = $this->ModelPs->getRequeteOne( $get_infos);

      $output = array(
      "data123" => $pourcent_sup['DESCR_NOMENCLATURE'],
      "id123"=> $pourcent_sup['ID_NOMENCLATURE_BUDGET_POURCENT'],
        
     );
     return $this->response->setJSON($output);
    }

    function supprimer_pourcentage()
    {
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
      
      if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }
      $db = db_connect();  
      $id = $this->request->getPost('ID_NOMENCLATURE_BUDGET_POURCENT');   
      $critere ="ID_NOMENCLATURE_BUDGET_POURCENT =" .$id  ;
      $table='pip_nomenclature_budgetaire_pourcentage';
      $deleteparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);

      $data=['message' => lang('messages_lang.message_success_suppr')];
      session()->setFlashdata('alert', $data);
      return redirect('pip/Nomenclature_Pourcentage/liste_pourcentage_nomenclature');
    }
  }
?>