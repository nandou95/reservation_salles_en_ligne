<?php
/**
 * Auteur: RUGAMBA Jean Vainqueur
 * Titre: Configuration des étapes de Double commande 
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

class Etape_Double_Commande_Config extends BaseController
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
    
    return view('App\Modules\double_commande_new\Views\Etape_Config_Liste_View', $data);
        
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

    $order_column = array(1,'step_actu.DESC_ETAPE_DOUBLE_COMMANDE','step_next.DESC_ETAPE_DOUBLE_COMMANDE','LINK_ETAPE_DOUBLE_COMMANDE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ETAPE_DOUBLE_COMMANDE_CONFIG_ID ASC';
    $search = !empty($_POST['search']['value']) ? (" AND (step_actu.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%".$var_search."%' OR step_next.DESC_ETAPE_DOUBLE_COMMANDE LIKE %".$var_search."%')") : '';

    // Condition pour la requête principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // Condition pour la requête de filtre
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT ETAPE_DOUBLE_COMMANDE_CONFIG_ID,step_actu.DESC_ETAPE_DOUBLE_COMMANDE AS ETAPE_ACTU,step_next.DESC_ETAPE_DOUBLE_COMMANDE AS ETAPE_SUIVANT,LINK_ETAPE_DOUBLE_COMMANDE AS LINK FROM execution_budgetaire_etape_double_commande_config config LEFT JOIN execution_budgetaire_etape_double_commande step_actu ON step_actu.ETAPE_DOUBLE_COMMANDE_ID=config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID LEFT JOIN execution_budgetaire_etape_double_commande step_next ON step_next.ETAPE_DOUBLE_COMMANDE_ID=config.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID WHERE 1";

    $requetedebases = $requetedebase . " " . $conditions;

    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;

    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;

    foreach ($fetch_actions as $row)
    {
      
      $ETAPE_ACTU = (mb_strlen($row->ETAPE_ACTU) > 4) ? (mb_substr($row->ETAPE_ACTU, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID . '" data-toggle="tooltip" title="'.$row->ETAPE_ACTU.'"><i class="fa fa-eye"></i></a>') : $row->ETAPE_ACTU;

      $ETAPE_SUIVANT = (mb_strlen($row->ETAPE_SUIVANT) > 4) ? (mb_substr($row->ETAPE_SUIVANT, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID . '" data-toggle="tooltip" title="'.$row->ETAPE_SUIVANT.'"><i class="fa fa-eye"></i></a>') : $row->ETAPE_SUIVANT;

      $LINK = (mb_strlen($row->LINK) > 8) ? (mb_substr($row->LINK, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID . '" data-toggle="tooltip" title="'.$row->LINK.'"><i class="fa fa-eye"></i></a>') : $row->LINK;


      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $ETAPE_ACTU;
      $sub_array[] = $ETAPE_SUIVANT;
      $sub_array[] = $LINK;


      $statut = lang('messages_lang.supprimer_action');
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Etape_Double_Commande_Config/getOne/".md5($row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID ))."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>

        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID .")' ><label>&nbsp;&nbsp;<font color='red'>".$statut."</font></label></a>
        </li>
        <div style='display:none;' id='message".$row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID ."'>
        <center>
        <h5><strong>".lang('messages_lang.Voulez_vous_supprimer_config')."<br><center><font color='green'>".$row->ETAPE_ACTU."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
        </h5>
        </center></div>
        <div style='display:none;' id='footer".$row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID ."'>
        <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
        ".lang('messages_lang.quiter_action')."
        </button>
        <a href='".base_url("double_commande_new/Etape_Double_Commande_Config/delete/".$row->ETAPE_DOUBLE_COMMANDE_CONFIG_ID )."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
        </div>
        </ul>";
      
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
  function delete($ETAPE_DOUBLE_COMMANDE_CONFIG_ID)
  {
    $session  = \Config\Services::session();

    $db = db_connect();     
    $critere ="ETAPE_DOUBLE_COMMANDE_CONFIG_ID =" .$ETAPE_DOUBLE_COMMANDE_CONFIG_ID;
    $table="execution_budgetaire_etape_double_commande_config";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";

    $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
    $data = ['message' => ''.lang('messages_lang.message_success_suppr').''];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/Etape_Double_Commande_Config');
     
  }

  
  /**
   * renvoie la vue qui va afficher la page d'ajout
   */
  public function add()
  {
    $data = $this->urichk();
    $cart = \Config\Services::cart();
    $cart->destroy();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }
    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //recuperation des étapes actuelles
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','1','ETAPE_DOUBLE_COMMANDE_ID ASC');
    $data['etapes'] = $this->ModelPs->getRequete($psgetrequete, $bind_step);

    //recuperation des contraintes montant
    $bind_contr_mont = $this->getBindParms('`CONTRAINTE_MONTANT_ID`,`DESC_CONTRAINTE`','double_commande_contrainte_montant','1','CONTRAINTE_MONTANT_ID ASC');
    $data['contr_mont'] = $this->ModelPs->getRequete($psgetrequete, $bind_contr_mont);


    $data['title'] = lang('messages_lang.titre_add_config');

    return view('App\Modules\double_commande_new\Views\Etape_Config_Add_View', $data);
        
  }

 
  //traitement et enregistrement dans la BD
  function save()
  {
    $data = $this->urichk();
    $cart = \Config\Services::cart();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
   
    $rules = [
      'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID');
      $LIEN = $this->request->getPost('LIEN');
      
      foreach($cart->contents() as $value)
      {
        //recuperation des étapes actuelles
        $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`A_CORRIGER`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$value['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $etapes = $this->ModelPs->getRequeteOne($psgetrequete,$bind_step);
        $IS_END = 0;

        $table = 'execution_budgetaire_etape_double_commande_config';
        
        $columsinsert = "ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,LINK_ETAPE_DOUBLE_COMMANDE,IS_END,IS_CORRECTION,CONTRAINTE_MONTANT_ID,IS_FOURNISSEUR";
        $datacolumsinsert = "{$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID},{$value['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']},' ',{$IS_END},{$etapes['A_CORRIGER']},{$value['CONTRAINTE_MONTANT_ID']},{$value['IS_BENEFICIAIRE']}";

        if(!empty($LIEN))
        {
          $columsinsert = "ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,LINK_ETAPE_DOUBLE_COMMANDE,IS_END,IS_CORRECTION,CONTRAINTE_MONTANT_ID,IS_FOURNISSEUR";
          $datacolumsinsert = "{$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID},{$value['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']},'{$LIEN}',{$IS_END},{$etapes['A_CORRIGER']},{$value['CONTRAINTE_MONTANT_ID']},{$value['IS_BENEFICIAIRE']}";
        }

        
        $this->save_all_table($table,$columsinsert,$datacolumsinsert);
      }
      $cart->destroy();

      $data = [
        'message' => lang('messages_lang.message_success')
      ];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Etape_Double_Commande_Config');
    }
    else
    {
      return $this->add();
    }
  }

  /**
   * renvoie la vue qui va afficher la page d'ajout
   */
  public function getOne($ETAPE_DOUBLE_COMMANDE_CONFIG_ID)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //Récupération des données de la configuration des étapes
    $bind_step_config = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_CONFIG_ID,ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,LINK_ETAPE_DOUBLE_COMMANDE,IS_END,IS_CORRECTION,CONTRAINTE_MONTANT_ID,IS_FOURNISSEUR','execution_budgetaire_etape_double_commande_config','MD5(ETAPE_DOUBLE_COMMANDE_CONFIG_ID)="'.$ETAPE_DOUBLE_COMMANDE_CONFIG_ID.'"','ETAPE_DOUBLE_COMMANDE_CONFIG_ID ASC');
    $bind_step_config=str_replace('\"','"',$bind_step_config);
    $data['step_config'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_config);


    //recuperation des étapes actuelles
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','1','ETAPE_DOUBLE_COMMANDE_ID ASC');
    $data['etapes'] = $this->ModelPs->getRequete($psgetrequete, $bind_step);

    //recuperation des étapes suivantes
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID!='.$data['step_config']['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $data['etap_suiv'] = $this->ModelPs->getRequete($psgetrequete, $bind_step);

    //recuperation des contraintes montant
    $bind_contr_mont = $this->getBindParms('`CONTRAINTE_MONTANT_ID`,`DESC_CONTRAINTE`','double_commande_contrainte_montant','1','CONTRAINTE_MONTANT_ID ASC');
    $data['contr_mont'] = $this->ModelPs->getRequete($psgetrequete, $bind_contr_mont);

    $data['title'] = lang('messages_lang.titre_modif_config');

    return view('App\Modules\double_commande_new\Views\Etape_Config_Update_View', $data);
        
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

    $ETAPE_DOUBLE_COMMANDE_CONFIG_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_CONFIG_ID');

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
   
    $rules = [
      'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      'CONTRAINTE_MONTANT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      'IS_BENEFICIAIRE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ]
      
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {
      $ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID');
      $LIEN = $this->request->getPost('LIEN');
      $CONTRAINTE_MONTANT_ID = $this->request->getPost('CONTRAINTE_MONTANT_ID');
      $IS_BENEFICIAIRE = $this->request->getPost('IS_BENEFICIAIRE');
      $ETAPE_DOUBLE_COMMANDE_SUIVANT_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID');

      //recuperation des étapes actuelles
      $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`A_CORRIGER`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
      $etapes = $this->ModelPs->getRequeteOne($psgetrequete,$bind_step);
      
      
      $table = 'execution_budgetaire_etape_double_commande_config';
      $conditions="ETAPE_DOUBLE_COMMANDE_CONFIG_ID =".$ETAPE_DOUBLE_COMMANDE_CONFIG_ID;
      
      $datatomodifie= "ETAPE_DOUBLE_COMMANDE_ACTUEL_ID={$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID}, ETAPE_DOUBLE_COMMANDE_SUIVANT_ID={$ETAPE_DOUBLE_COMMANDE_SUIVANT_ID},IS_CORRECTION={$etapes['A_CORRIGER']},CONTRAINTE_MONTANT_ID={$CONTRAINTE_MONTANT_ID},IS_FOURNISSEUR={$IS_BENEFICIAIRE}";

      if(!empty($LIEN))
      {
        $datatomodifie= "ETAPE_DOUBLE_COMMANDE_ACTUEL_ID={$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID}, ETAPE_DOUBLE_COMMANDE_SUIVANT_ID={$ETAPE_DOUBLE_COMMANDE_SUIVANT_ID},LINK_ETAPE_DOUBLE_COMMANDE='{$LIEN}',IS_CORRECTION={$etapes['A_CORRIGER']},CONTRAINTE_MONTANT_ID={$CONTRAINTE_MONTANT_ID},IS_FOURNISSEUR={$IS_BENEFICIAIRE}";
      }

      $this->update_all_table($table,$datatomodifie,$conditions);

      $data = [
        'message' => lang('messages_lang.msg_modif')
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Etape_Double_Commande_Config');
    }
    else
    {
      return $this->getOne(md5($ETAPE_DOUBLE_COMMANDE_ID));
    }
  }


  //Les étapes suivantes
  function get_next_step()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }


    $ETAPE_DOUBLE_COMMANDE_ACTUEL_ID =$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    //récuperer les étapes suivantes
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID!='.$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $etapes = $this->ModelPs->getRequete($callpsreq, $bind_step);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';


    if(!empty($etapes))
    {
      foreach($etapes as $key)
      { 
        $html.= "<option value='".$key->ETAPE_DOUBLE_COMMANDE_ID."'>".$key->DESC_ETAPE_DOUBLE_COMMANDE."</option>";
      }
    }
    $output = array('status' => TRUE ,'etap_suiv' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);
  }


  public function add_cart()
  {
    $cart = \Config\Services::cart();
    $ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'); 
    $ETAPE_DOUBLE_COMMANDE_SUIVANT_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID');
    $LIEN=$this->request->getPost('LIEN');
    $CONTRAINTE_MONTANT_ID=$this->request->getPost('CONTRAINTE_MONTANT_ID');
    $IS_BENEFICIAIRE=$this->request->getPost('IS_BENEFICIAIRE');
      
    $file_data=array(
      'id'=>uniqid(),
      'qty'=>1,
      'price'=>1,
      'name'=>'CI',
      'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'=>$ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,
      'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'=>$ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,
      'LIEN'=>$LIEN,
      'CONTRAINTE_MONTANT_ID'=>$CONTRAINTE_MONTANT_ID,
      'IS_BENEFICIAIRE'=>$IS_BENEFICIAIRE,
      'typecartitem'=>'FILECI'
    );
     
    $cart->insert($file_data);

    $html="";
    $j=1;
    $i=0;

    $html.='
    <table class="table">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.etape_actuel_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.etape_suivante_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.lien_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_contr_mont').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_contr_benef').'</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());


    foreach ($cart->contents() as $items):
      if (preg_match('/FILECI/',$items['typecartitem']))
      {
        $i++;

        $psgetrequete = "CALL `getRequete`(?,?,?,?)";
        //recuperation des étapes actuelles
        $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$items['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $etapes = $this->ModelPs->getRequeteONe($psgetrequete, $bind_step);

        //recuperation des étapes suivantes
        $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$items['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $step = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step);

        //recuperation des contraintes montant
        $bind_contr_mont = $this->getBindParms('`CONTRAINTE_MONTANT_ID`,`DESC_CONTRAINTE`','double_commande_contrainte_montant','CONTRAINTE_MONTANT_ID='.$items['CONTRAINTE_MONTANT_ID'],'CONTRAINTE_MONTANT_ID ASC');
        $contr_mont = $this->ModelPs->getRequeteOne($psgetrequete, $bind_contr_mont);

        $is_benef= ($items['IS_BENEFICIAIRE'] == 1) ? lang('messages_lang.label_oui') : lang('messages_lang.label_non');


        $html.='<tr>
        <td>'.$j.'</td>
        <td>'.$etapes['DESC_ETAPE_DOUBLE_COMMANDE'].'</td>
        <td>'.$step['DESC_ETAPE_DOUBLE_COMMANDE'].'</td>
        <td>'.$items['LIEN'].'</td>
        <td>'.$contr_mont['DESC_CONTRAINTE'].'</td>
        <td>'.$is_benef.'</td>

        <td style="width: 5px;">
        <input type="hidden" id="rowid'.$j.'" value='.$items['rowid'].'>
        <button  class="btn btn-danger btn-xs" type="button" onclick="remove_cart('.$j.')">
        x
        </button>
        </tr>';
      }

      $j++;
      $i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if ($i>0) {
      # code...
      $display_save=1;
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }else{
      $display_save=0;
      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }
  }


  function delete_cart()
  {
    $cart = \Config\Services::cart();
    $rowid=$this->request->getPost('rowid');

    //print_r($rowid);die();

    $cart->remove($rowid);      

    $html="";
    $j=1;
    $i=0;
    $html.='
    <table class="table">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.etape_actuel_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.etape_suivante_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.lien_action').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_contr_mont').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_contr_benef').'</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';

    foreach ($cart->contents() as $item):
      if (preg_match('/FILECI/',$item['typecartitem'])) {

        $i++;

        $psgetrequete = "CALL `getRequete`(?,?,?,?)";
        //recuperation des étapes actuelles
        $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$item['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $etapes = $this->ModelPs->getRequeteONe($psgetrequete, $bind_step);

        //recuperation des étapes suivantes
        $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$item['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $step = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step);

        //recuperation des contraintes montant
        $bind_contr_mont = $this->getBindParms('`CONTRAINTE_MONTANT_ID`,`DESC_CONTRAINTE`','double_commande_contrainte_montant','CONTRAINTE_MONTANT_ID='.$item['CONTRAINTE_MONTANT_ID'],'CONTRAINTE_MONTANT_ID ASC');
        $contr_mont = $this->ModelPs->getRequeteOne($psgetrequete, $bind_contr_mont);

        $is_benef= ($item['IS_BENEFICIAIRE'] == 1) ? lang('messages_lang.label_oui') : lang('messages_lang.label_non');


        $html.='<tr>
        <td>'.$j.'</td>
        <td>'.$etapes['DESC_ETAPE_DOUBLE_COMMANDE'].'</td>
        <td>'.$step['DESC_ETAPE_DOUBLE_COMMANDE'].'</td>
        <td>'.$item['LIEN'].'</td>
        <td>'.$contr_mont['DESC_CONTRAINTE'].'</td>
        <td>'.$is_benef.'</td>
        <td style="width: 5px;">
        <input type="hidden" id="rowid'.$j.'" value='.$item['rowid'].'>
        <button  class="btn btn-danger btn-xs" type="button" onclick="remove_cart('.$j.')">
        x
        </button>
        </tr>' ;
      }

      $j++;
      $i++;
    endforeach;

    $html.=' </tbody>
    </table>';

    if ($i>0) {
      # code...
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);//echo json_encode($output);
    }else{

      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);//echo json_encode($output);
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