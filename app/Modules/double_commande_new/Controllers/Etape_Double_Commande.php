<?php
/**
 * Auteur: RUGAMBA Jean Vainqueur
 * Titre: Etape Double commande
 * email: jean.vainqueur@mediabox.bi
 * whatsapp: +257 62 47 19 15
 * téléphone: +257 66 33 43 25
 * date 15.02.2024 15:12
 */

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Etape_Double_Commande extends BaseController
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
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  
  /**
   * renvoie la vue qui va afficher la page d'ajout
   */
  public function index()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }
    
    return view('App\Modules\double_commande_new\Views\Etape_Double_Commande_Liste_View', $data);
        
  }


  private function check_droit($active)
  {
    $html = ($active == 1) ? "<center><span class='fa fa-check badge badge-pill badge-success' style='font-size:20px;font-weight: bold;color: white;' data-toggle='tooltip' title='Activé'>&nbsp;</span></center>" : "<center><span class='fa fa-close badge badge-pill badge-danger' style='font-size:20px;font-weight: bold;color: white;' data-toggle='tooltip' title='Désactivé'>&nbsp;</span></center>" ;
    return $html;
  }


  public function listing($value = 0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba');
    }


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "''", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) 
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array(1,'DESC_ETAPE_DOUBLE_COMMANDE','DESC_MOUVEMENT_DEPENSE','IS_ACTIVE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ETAPE_DOUBLE_COMMANDE_ID ASC';
    $search = !empty($_POST['search']['value']) ? (" AND (DESC_ETAPE_DOUBLE_COMMANDE LIKE '%".$var_search."%' OR DESC_MOUVEMENT_DEPENSE LIKE %".$var_search."%')") : '';

    // Condition pour la requête principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // Condition pour la requête de filtre
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT IS_ACTIVE,ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,DESC_MOUVEMENT_DEPENSE FROM execution_budgetaire_etape_double_commande step left JOIN budgetaire_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=step.MOUVEMENT_DEPENSE_ID WHERE 1";

    $requetedebases = $requetedebase . " " . $conditions;

    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;

    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;

    foreach ($fetch_actions as $row)
    {
      

      $DESC_ETAPE_DOUBLE_COMMANDE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 4) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_ID . '" data-toggle="tooltip" title="'.$row->DESC_ETAPE_DOUBLE_COMMANDE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_ETAPE_DOUBLE_COMMANDE;

      /*$DESC_MOUVEMENT_DEPENSE = (mb_strlen($row->DESC_MOUVEMENT_DEPENSE) > 4) ? (mb_substr($row->DESC_MOUVEMENT_DEPENSE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_ID . '" data-toggle="tooltip" title="'.$row->DESC_MOUVEMENT_DEPENSE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_MOUVEMENT_DEPENSE;*/


      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $DESC_ETAPE_DOUBLE_COMMANDE;
      $sub_array[] = $row->DESC_MOUVEMENT_DEPENSE;
      $sub_array[] = $this->check_droit($row->IS_ACTIVE);

      if($row->IS_ACTIVE==1)
      {
        $statut = 'Désactiver';
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Etape_Double_Commande/getOne/".md5($row->ETAPE_DOUBLE_COMMANDE_ID))."'><label>&nbsp;&nbsp;Modifier</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->ETAPE_DOUBLE_COMMANDE_ID.")' ><label>&nbsp;&nbsp;<font color='red'>".$statut."</font></label></a>
        </li>
        <div style='display:none;' id='message".$row->ETAPE_DOUBLE_COMMANDE_ID."'>
        <center>
        <h5><strong>".lang('messages_lang.confimatation_desactive_action')."<br><center><font color='green'>".$row->DESC_ETAPE_DOUBLE_COMMANDE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
        </h5>
        </center></div>
        <div style='display:none;' id='footer".$row->ETAPE_DOUBLE_COMMANDE_ID."'>
        <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
        ".lang('messages_lang.quiter_action')."
        </button>
        <a href='".base_url("double_commande_new/Etape_Double_Commande/is_active/".$row->ETAPE_DOUBLE_COMMANDE_ID)."' class='btn btn-success btn-md'>".lang('messages_lang.desactive_action')."</a>
        </div>
        </ul>";      
      }
      else
      {
        $statut = 'Activer';
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Etape_Double_Commande/getOne/".md5($row->ETAPE_DOUBLE_COMMANDE_ID))."'><label>&nbsp;&nbsp;Modifier</label></a>
        </li>

        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->ETAPE_DOUBLE_COMMANDE_ID.")' ><label>&nbsp;&nbsp;<font color='green'>".$statut."</font></label></a>
        </li>
        <div style='display:none;' id='message".$row->ETAPE_DOUBLE_COMMANDE_ID."'>
        <center>
        <h5><strong>".lang('messages_lang.confimatation_active_action')."<br><center><font color='green'>".$row->DESC_ETAPE_DOUBLE_COMMANDE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
        </h5>
        </center></div>
        <div style='display:none;' id='footer".$row->ETAPE_DOUBLE_COMMANDE_ID."'>
        <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
        ".lang('messages_lang.quiter_action')."
        </button>
        <a href='".base_url("double_commande_new/Etape_Double_Commande/is_active/".$row->ETAPE_DOUBLE_COMMANDE_ID)."' class='btn btn-success btn-md'>".lang('messages_lang.active_action')."</a>
        </div>
        </ul>";     
      }
      $sub_array[]=$action;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }



  //fonction pour l'activation/désactivation
  function is_active($ETAPE_DOUBLE_COMMANDE_ID)
  {
    $session  = \Config\Services::session();

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_etape = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID  , IS_ACTIVE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID ='.$ETAPE_DOUBLE_COMMANDE_ID ,'ETAPE_DOUBLE_COMMANDE_ID  ASC');
    $step= $this->ModelPs->getRequeteOne($callpsreq, $bind_etape);
    if($step['IS_ACTIVE']==0)
    {
      $IS_ACTIVE = 1;
      $updateTable='execution_budgetaire_etape_double_commande';
      $critere = "ETAPE_DOUBLE_COMMANDE_ID =".$ETAPE_DOUBLE_COMMANDE_ID ;
      $datatoupdate= 'IS_ACTIVE='.$IS_ACTIVE;
      $bindparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
      $data = ['message' => ''.lang('messages_lang.labelle_et_mod_question_succes').''];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Etape_Double_Commande');
    }
    else
    {
      $IS_ACTIVE = 0;
      $updateTable='execution_budgetaire_etape_double_commande';
      $critere = "ETAPE_DOUBLE_COMMANDE_ID =".$ETAPE_DOUBLE_COMMANDE_ID ;
      $datatoupdate= 'IS_ACTIVE='.$IS_ACTIVE;
      $bindparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
      $data=['message' => ''.lang('messages_lang.labelle_et_mod_question_succes_d').''];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Etape_Double_Commande');
    } 
  }

  
  /**
   * renvoie la vue qui va afficher la page d'ajout
   */
  public function add()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }
    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //recuperation origine et destination
    $bind_mouvent = $this->getBindParms('`MOUVEMENT_DEPENSE_ID`,`DESC_MOUVEMENT_DEPENSE`,`BUDGETAIRE_PHASE_ID`', 'budgetaire_mouvement_depense', '1', 'MOUVEMENT_DEPENSE_ID ASC');
    $data['mouvement'] = $this->ModelPs->getRequete($psgetrequete, $bind_mouvent);


    $data['title'] = lang('messages_lang.labelle_et_ajout');

    return view('App\Modules\double_commande_new\Views\Etape_Double_Commande_Add_View', $data);
        
  }

 
  //traitement et enregistrement dans la BD
  function save()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
   

    $rules = [
      'DESC_ETAPE_DOUBLE_COMMANDE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      
      'MOUVEMENT_DEPENSE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      
      'NIVEAU_ETAPE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ]
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $DESC_ETAPE_DOUBLE_COMMANDE = $this->request->getPost('DESC_ETAPE_DOUBLE_COMMANDE');
      $MOUVEMENT_DEPENSE_ID = $this->request->getPost('MOUVEMENT_DEPENSE_ID[]');
      $NIVEAU_ETAPE_ID = $this->request->getPost('NIVEAU_ETAPE_ID');
      
      $A_FAIRE = $NIVEAU_ETAPE_ID == 1 ? 1 : 0;
      $DEJA_FAIT = $NIVEAU_ETAPE_ID == 2 ? 1 : 0;
      $A_CORRIGER = $NIVEAU_ETAPE_ID == 3 ? 1 : 0;
      $IS_TRANSMISSION = $NIVEAU_ETAPE_ID == 4 ? 1 : 0;
      $IS_RECEPTION = $NIVEAU_ETAPE_ID == 5 ? 1 : 0;

      $IS_ACTIVE=1;

      $table = 'execution_budgetaire_etape_double_commande';
      $columsinsert = "DESC_ETAPE_DOUBLE_COMMANDE,MOUVEMENT_DEPENSE_ID,IS_ACTIVE,A_FAIRE,DEJA_FAIT,A_CORRIGER,IS_TRANSMISSION,IS_RECEPTION";
      $datacolumsinsert = "'{$DESC_ETAPE_DOUBLE_COMMANDE}', {$MOUVEMENT_DEPENSE_ID}, {$IS_ACTIVE}, {$A_FAIRE}, {$DEJA_FAIT}, {$A_CORRIGER}, {$IS_TRANSMISSION}, {$IS_RECEPTION}";
      $this->save_all_table($table,$columsinsert,$datacolumsinsert);
      $data = [
        'message' => lang('messages_lang.message_success')
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Etape_Double_Commande');
    }
    else
    {
      return $this->add();
    }
  }

  /**
   * renvoie la vue qui va afficher la page d'ajout
   */
  public function getOne($ETAPE_DOUBLE_COMMANDE_ID)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_etape = $this->getBindParms('*', 'execution_budgetaire_etape_double_commande', 'MD5(ETAPE_DOUBLE_COMMANDE_ID) ="'.$ETAPE_DOUBLE_COMMANDE_ID.'"' ,'ETAPE_DOUBLE_COMMANDE_ID  ASC');
    $bind_etape=str_replace('\"','"',$bind_etape);
    $data['step']= $this->ModelPs->getRequeteOne($callpsreq, $bind_etape);
    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //recuperation origine et destination
    $bind_mouvent = $this->getBindParms('`MOUVEMENT_DEPENSE_ID`,`DESC_MOUVEMENT_DEPENSE`,`BUDGETAIRE_PHASE_ID`', 'budgetaire_mouvement_depense', '1', 'MOUVEMENT_DEPENSE_ID ASC');
    $data['mouvement'] = $this->ModelPs->getRequete($psgetrequete, $bind_mouvent);

    //print_r($data['step']);exit();

    $data['title'] = lang('messages_lang.labelle_et_mod');

    return view('App\Modules\double_commande_new\Views\Etape_Double_Commande_Update_View', $data);
        
  }

 
  //traitement et mise à jour dans la BD
  function update()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
   

    $rules = [
      'DESC_ETAPE_DOUBLE_COMMANDE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      
      'MOUVEMENT_DEPENSE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      
      'NIVEAU_ETAPE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ]
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $DESC_ETAPE_DOUBLE_COMMANDE = $this->request->getPost('DESC_ETAPE_DOUBLE_COMMANDE');
      $MOUVEMENT_DEPENSE_ID = $this->request->getPost('MOUVEMENT_DEPENSE_ID[]');
      $NIVEAU_ETAPE_ID = $this->request->getPost('NIVEAU_ETAPE_ID');
      
      $A_FAIRE = $NIVEAU_ETAPE_ID == 1 ? 1 : 0;
      $DEJA_FAIT = $NIVEAU_ETAPE_ID == 2 ? 1 : 0;
      $A_CORRIGER = $NIVEAU_ETAPE_ID == 3 ? 1 : 0;
      $IS_TRANSMISSION = $NIVEAU_ETAPE_ID == 4 ? 1 : 0;
      $IS_RECEPTION = $NIVEAU_ETAPE_ID == 5 ? 1 : 0;

      $table = 'execution_budgetaire_etape_double_commande';
      $conditions="ETAPE_DOUBLE_COMMANDE_ID =".$ETAPE_DOUBLE_COMMANDE_ID;
      $datatomodifie= "DESC_ETAPE_DOUBLE_COMMANDE='{$DESC_ETAPE_DOUBLE_COMMANDE}', MOUVEMENT_DEPENSE_ID={$MOUVEMENT_DEPENSE_ID},A_FAIRE={$A_FAIRE},DEJA_FAIT={$DEJA_FAIT},A_CORRIGER={$A_CORRIGER},IS_TRANSMISSION={$IS_TRANSMISSION},IS_RECEPTION={$IS_RECEPTION}";

      $this->update_all_table($table,$datatomodifie,$conditions);

      $data = [
        'message' => lang('messages_lang.msg_modif')
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Etape_Double_Commande');
    }
    else
    {
      return $this->getOne(md5($ETAPE_DOUBLE_COMMANDE_ID));
    }
  }


  public function save_histo_racrochage($columsinsert, $datacolumsinsert)
  {
    $table = 'execution_budgetaire_tache_detail_histo';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  /* Debut Gestion insertion */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }

  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }


 
}
?>