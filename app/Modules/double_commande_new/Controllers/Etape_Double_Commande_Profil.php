<?php
/**
 * Auteur: RUGAMBA Jean Vainqueur
 * Titre: Affectation des etapes aux profils
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

class Etape_Double_Commande_Profil extends BaseController
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
    
    return view('App\Modules\double_commande_new\Views\Etape_Profil_Liste_View', $data);
        
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

    $order_column = array(1,'DESC_ETAPE_DOUBLE_COMMANDE',1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ETAPE_DOUBLE_COMMANDE_ID ASC';
    $search = !empty($_POST['search']['value']) ? (" AND (DESC_ETAPE_DOUBLE_COMMANDE LIKE '%".$var_search."%')") : '';

    // Condition pour la requête principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // Condition pour la requête de filtre
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE AS ETAPE FROM execution_budgetaire_etape_double_commande WHERE IS_ACTIVE=1 AND ETAPE_DOUBLE_COMMANDE_ID IN (SELECT ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_etape_double_commande_profil WHERE 1) ";

    $requetedebases = $requetedebase . " " . $conditions;

    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;

    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;

    foreach ($fetch_actions as $row)
    {
      
      //Compter le nombre des profils par étape
      $count_prof = "SELECT COUNT(ETAPE_DOUBLE_COMMANDE_ID) AS nbre FROM execution_budgetaire_etape_double_commande_profil WHERE ETAPE_DOUBLE_COMMANDE_ID=".$row->ETAPE_DOUBLE_COMMANDE_ID;
      $count_prof = 'CALL `getTable`("'.$count_prof.'");';
      $nbre_profil = $this->ModelPs->getRequeteOne($count_prof);


      $ETAPE = (mb_strlen($row->ETAPE) > 4) ? (mb_substr($row->ETAPE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->ETAPE_DOUBLE_COMMANDE_ID . '" data-toggle="tooltip" title="'.$row->ETAPE.'"><i class="fa fa-eye"></i></a>') : $row->ETAPE;


      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $ETAPE;
      $point="<a href='javascript:void(0)'  class='btn btn-dark btn-md' onclick='get_profil(".$row->ETAPE_DOUBLE_COMMANDE_ID.")'>".$nbre_profil['nbre']."</a>";

      $sub_array[]=$point;

      $statut = lang('messages_lang.supprimer_action');
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Etape_Double_Commande_Profil/getOne/".md5($row->ETAPE_DOUBLE_COMMANDE_ID ))."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>

        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->ETAPE_DOUBLE_COMMANDE_ID .")' ><label>&nbsp;&nbsp;<font color='red'>".$statut."</font></label></a>
        </li>
        <div style='display:none;' id='message".$row->ETAPE_DOUBLE_COMMANDE_ID ."'>
        <center>
        <h5><strong>".lang('messages_lang.Voulez_vous_supprimer_config')."<br><center><font color='green'>".$row->ETAPE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
        </h5>
        </center></div>
        <div style='display:none;' id='footer".$row->ETAPE_DOUBLE_COMMANDE_ID ."'>
        <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
        ".lang('messages_lang.quiter_action')."
        </button>
        <a href='".base_url("double_commande_new/Etape_Double_Commande_Profil/delete/".$row->ETAPE_DOUBLE_COMMANDE_ID )."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
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


  //fonction pour affichage d'une liste
  public function detail_profil()
  {
    $session  = \Config\Services::session();
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $affect_etape_id = $this->request->getPost('affect_etape_id');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array(1,'PROFIL_DESCR');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY PROFIL_DESCR ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (PROFIL_DESCR LIKE "%'.$var_search.'%")') : '';

    $conditions=$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
    $requetedebase='SELECT ETAPE_DOUBLE_COMMANDE_PROFIL_ID,PROFIL_DESCR FROM execution_budgetaire_etape_double_commande_profil step_prof JOIN user_profil prof ON prof.PROFIL_ID=step_prof.PROFIL_ID WHERE ETAPE_DOUBLE_COMMANDE_ID='.$affect_etape_id;
    $requetedebases=$requetedebase .' '. $conditions;
    $requetedebasefilter=$requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach($fetch_actions as $row)
    {
      $sub_array=array();
      $sub_array[]= $u++;
      $sub_array[]=$row->PROFIL_DESCR;
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
    return $this->response->setJSON($output);
  }


  //fonction pour la suppression
  function delete($ETAPE_DOUBLE_COMMANDE_ID)
  {
    $session  = \Config\Services::session();

    $db = db_connect();     
    $critere ="ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID;
    $table="execution_budgetaire_etape_double_commande_profil";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";

    $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
    $data = ['message' => ''.lang('messages_lang.message_success_suppr').''];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/Etape_Double_Commande_Profil');
     
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
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','IS_ACTIVE=1','ETAPE_DOUBLE_COMMANDE_ID ASC');
    $data['etapes'] = $this->ModelPs->getRequete($psgetrequete, $bind_step);

    //recuperation des profils
    $bind_profil=$this->getBindParms('PROFIL_ID,PROFIL_DESCR','user_profil','IS_ACTIVE=1','PROFIL_DESCR ASC');
    $data['profil']= $this->ModelPs->getRequete($psgetrequete, $bind_profil);

    $data['title'] = lang('messages_lang.affect_prof_step');

    return view('App\Modules\double_commande_new\Views\Etape_Profil_Add_View', $data);
        
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
      'ETAPE_DOUBLE_COMMANDE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      'PROFIL_ID' => [
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
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $PROFIL_ID = $this->request->getPost('PROFIL_ID[]');
      
      foreach($PROFIL_ID as $value)
      {
        $table = 'execution_budgetaire_etape_double_commande_profil';
        $columsinsert = "ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID";
        $datacolumsinsert = "{$ETAPE_DOUBLE_COMMANDE_ID},{$value}";
        $this->save_all_table($table,$columsinsert,$datacolumsinsert);
      }

      $data = [
        'message' => lang('messages_lang.message_success')
      ];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Etape_Double_Commande_Profil');
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

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //Récupération des données de la configuration des étapes
    $bind_step_config = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','MD5(ETAPE_DOUBLE_COMMANDE_ID)="'.$ETAPE_DOUBLE_COMMANDE_ID.'"','ETAPE_DOUBLE_COMMANDE_PROFIL_ID ASC');
    $bind_step_config=str_replace('\"','"',$bind_step_config);
    $data['step_prof'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_config);


    //recuperation des étapes
    $bind_step = $this->getBindParms('`ETAPE_DOUBLE_COMMANDE_ID`,`DESC_ETAPE_DOUBLE_COMMANDE`','execution_budgetaire_etape_double_commande','IS_ACTIVE=1','ETAPE_DOUBLE_COMMANDE_ID ASC');
    $data['etapes'] = $this->ModelPs->getRequete($psgetrequete, $bind_step);

    //recuperation des profils
    $bind_profil=$this->getBindParms('PROFIL_ID,PROFIL_DESCR','user_profil','IS_ACTIVE=1','PROFIL_DESCR ASC');
    $data['profil']= $this->ModelPs->getRequete($psgetrequete, $bind_profil);

    //Récuperer les profils sélectionnés
    $bind_profil_select=$this->getBindParms('PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['step_prof']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $profil_select= $this->ModelPs->getRequete($psgetrequete, $bind_profil_select);

    $PROFIL_ID = '';
    foreach ($profil_select as $key)
    {
      $PROFIL_ID .= $key->PROFIL_ID . ',';
    }

    $PROFIL_ID .= ',';
    $PROFIL_ID = str_replace(',,', '', $PROFIL_ID);
    $profil_ids = explode(',', $PROFIL_ID);
    $data['prof_id'] = $profil_ids;
    
    $data['title'] = lang('messages_lang.titre_modif_config');

    return view('App\Modules\double_commande_new\Views\Etape_Profil_Update_View', $data);
        
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

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
   
    $rules = [
      'ETAPE_DOUBLE_COMMANDE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.error_sms').'</font>'
        ]
      ],
      'PROFIL_ID' => [
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
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $PROFIL_ID = $this->request->getPost('PROFIL_ID[]');

      //Suppression des affectations précedentes
      $db = db_connect();
      $critere ="ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID;
      $table="execution_budgetaire_etape_double_commande_profil";
      $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);


      foreach($PROFIL_ID as $value)
      {
        $table = 'execution_budgetaire_etape_double_commande_profil';
        $columsinsert = "ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID";
        $datacolumsinsert = "{$ETAPE_DOUBLE_COMMANDE_ID},{$value}";
        $this->save_all_table($table,$columsinsert,$datacolumsinsert);
      }
      

      $data = [
        'message' => lang('messages_lang.msg_modif')
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Etape_Double_Commande_Profil');
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