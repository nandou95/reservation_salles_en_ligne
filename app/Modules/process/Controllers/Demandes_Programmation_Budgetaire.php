<?php

/*
  *HAVYARIMANA Jean Thierry
  *Email: thierry.havyarimana@mediabox.bi
  *Date: 23 Novembre,2023
  *Titre: Gestion des demandes, details de demandes et actions
*/

namespace App\Modules\process\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Exception;

class Demandes_Programmation_Budgetaire extends BaseController
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

  /* Debut Gestion insertion */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }


  /* Fin Gestion insertion */

  //Update
  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }


  // pour uploader les documents
  public function uploadFile($fieldName = NULL, $folder = NULL, $prefix = NULL): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    if ($file->isValid() && !$file->hasMoved()) {
      $newName = uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'uploads/' . $folder . '/' . $newName;
    }
    return $newName;
  }

  function index()
  {
    
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data = $this->urichk();

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    //Sélectionner les processus
    $bindparams = $this->getBindParms('`PROCESS_ID`,`NOM_PROCESS`', 'proc_process', '1 AND STATUT=1', '`PROCESS_ID` ASC');
    $data['process'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    return view('App\Modules\process\Views\Demandes_Programmation_Budgetaire_List_View', $data);
  }

  //liste des demandes
  function listing()
  {
    $ID_PROCESSUS = $this->request->getPost('PROCESS_ID');
    $ID_ETAPE = $this->request->getPost('ID_ETAPE');

    $critere = "";
    $critere2 = "";

    if (!empty($ID_PROCESSUS)) {
      $critere = ' AND proc_demandes.PROCESS_ID=' . $ID_PROCESSUS;
    }
    if (!empty($ID_ETAPE)) {
      $critere2 = ' AND proc_demandes.ETAPE_ID=' . $ID_ETAPE;
    }



    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if (empty($USER_ID)) {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $profil = "SELECT `PROFIL_ID` FROM `user_users` WHERE 1 AND USER_ID=" . $USER_ID;
    $getProfil = 'CALL `getTable`("' . $profil . '");';
    $profil_id = $this->ModelPs->getRequeteOne($getProfil)['PROFIL_ID'];

    $query_principal = "SELECT proc_demandes.CODE_DEMANDE,proc_process.NOM_PROCESS,proc_process.LINK,proc_etape.DESCR_ETAPE,proc_demandes.PROCESS_ID,proc_demandes.ETAPE_ID,proc_demandes.DATE_INSERTION,proc_demandes.USER_ID,proc_demandes.ID_DEMANDE,inst_institutions.DESCRIPTION_INSTITUTION  FROM proc_demandes JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN user_affectaion ON user_affectaion.USER_ID=proc_demandes.USER_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID JOIN proc_profil_etape ON proc_etape.ETAPE_ID=proc_profil_etape.ETAPE_ID WHERE 1 AND proc_etape.PROFIL_ID=".$profil_id." " . $critere . " " . $critere2;

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit = 'LIMIT 0,10';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column = '';
    $order_column = array('proc_demandes.CODE_DEMANDE', 'proc_process.NOM_PROCESS', 'proc_etape.DESCR_ETAPE', 'inst_institutions.DESCRIPTION_INSTITUTION', 1, 1, 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (proc_demandes.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

    $critaire = '';
    $query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;

    $query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
    $requete = 'CALL `getList`("' . $query_secondaire . '")';
    $fetch_cov_frais = $this->ModelPs->datatable($requete);

    $ID_DEMANDE = '';

    $data = array();
    $u = 1;
    foreach ($fetch_cov_frais as $info) {
      $ID_DEMANDE = $info->ID_DEMANDE;

      $post = array();
      $post[] = $u++;
      $post[] = !empty($info->CODE_DEMANDE) ? $info->CODE_DEMANDE : 'N/A';
      $post[] = !empty($info->NOM_PROCESS) ? $info->NOM_PROCESS : 'N/A';
      $post[] = !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
      $post[] = !empty($info->DESCRIPTION_INSTITUTION) ? $info->DESCRIPTION_INSTITUTION : 'N/A';
      $post[] = !empty($info->DATE_INSERTION) ? $info->DATE_INSERTION : 'N/A';

      $action = '<div class="dropdown" style="color:#fff;">
      <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-left">';

      $action .= '<li>
      <a href="' . base_url("process/Demandes_Programmation_Budgetaire/Details/" . md5($ID_DEMANDE)) . '"><label>&nbsp;&nbsp;Détails</label></a>
      </li></ul>';

      $post[] = $action;

      $data[] = $post;
    }

    $requeteqp = 'CALL `getList`("' . $query_principal . '")';
    $recordsTotal = $this->ModelPs->datatable($requeteqp);
    $requeteqf = 'CALL `getList`("' . $query_filter . '")';
    $recordsFiltered = $this->ModelPs->datatable($requeteqf);

    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }
  //recuperation des etapes par rapport au processus
  public function get_etapes()
  {

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_PROCESSUS = $this->request->getPost('PROCESS_ID');

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    //Sélectionner les etapes
    $bindparams = $this->getBindParms('`ETAPE_ID`,`DESCR_ETAPE`,PROCESS_ID', 'proc_etape', '1 AND PROCESS_ID=' . $ID_PROCESSUS, '`ETAPE_ID` ASC');
    $get_etapes = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    $html = '<option value="0">'.lang('messages_lang.selection_message').'</option>';
    foreach ($get_etapes as $key_etapes) {
      $html .= '<option value="' . $key_etapes->ETAPE_ID . '">' . $key_etapes->DESCR_ETAPE . '</option>';
    }

    $output = array(
      'DATA_ETAPE' => $html
    );

    echo json_encode($output);
  }

  /**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilisE
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
   */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }


  //function to get details of demandes from database
  public function details_view($ID_DEMANDE)
  {

    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = '';
    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //get the demande with the id=DEMANDE_ID
    $getDemande = 'SELECT pd.ID_DEMANDE,pd.CODE_DEMANDE,pd.PROCESS_ID,pd.ETAPE_ID,pd.DATE_INSERTION,pd.USER_ID,u.NOM,u.PRENOM,proc_etape.DESCR_ETAPE,proc_process.NOM_PROCESS,user_profil.PROFIL_DESCR,pd.IS_END
    FROM proc_demandes pd
    JOIN proc_etape ON proc_etape.ETAPE_ID=pd.ETAPE_ID
    JOIN proc_process ON proc_process.PROCESS_ID=pd.PROCESS_ID
    JOIN user_users u ON pd.USER_ID=u.USER_ID
    JOIN proc_profil_etape ON proc_etape.ETAPE_ID=proc_profil_etape.ETAPE_ID 
    JOIN user_profil ON user_profil.PROFIL_ID=proc_profil_etape.PROFIL_ID
    WHERE md5(ID_DEMANDE)="' . $ID_DEMANDE . '"';

    $requeteDem = "CALL `getTable`('" . $getDemande . "')";
    $demande = $this->ModelPs->getRequeteOne($requeteDem);
    $data['infoAffiche'] = $demande;

    //get details of this demande
    $getDemDetails = 'SELECT pb.DATE_PROGRAMMATION,pb.PATH_PROJET_LOI_FINANCE,pb.DATE_PRORAMMATION_2,pb.LETTRE_CADRAGE 
    FROM progr_budg_infos_supp pb
    WHERE md5(ID_DEMANDE)="' . $ID_DEMANDE . '" ';
    $requeteDem = "CALL `getTable`('" . $getDemDetails . "')";
    $details = $this->ModelPs->getRequete($requeteDem);
    $data['details'] = $details;

    $getActions = 'SELECT pa.ACTION_ID,pa.ETAPE_ID,pa.DESCR_ACTION,pa.MOVETO,pa.IS_REQUIRED,pa.GET_FORM,pa.LINK_FORM FROM proc_actions pa JOIN proc_etape pe ON pa.ETAPE_ID=pe.ETAPE_ID  WHERE pa.ETAPE_ID=' . $data['infoAffiche']['ETAPE_ID'];
    $requeteAct = "CALL getTable('" . $getActions . "')";
    $actions = $this->ModelPs->getRequete($requeteAct);

    $data['actions'] = $actions;

    $note_cbmt = 'SELECT  ID_DEMANDE, PATH_NOTE_CADRAGE,etap.DESCR_ETAPE FROM planification_demande_cadrage_cbmt note JOIN proc_etape etap ON etap.ETAPE_ID=note.ETAPE_ID WHERE md5(ID_DEMANDE) = "'.$ID_DEMANDE.'"';

    $note_cbmt = "CALL `getTable`('" . $note_cbmt . "');";
    $data['get_note_cbmt']= $this->ModelPs->getRequete($note_cbmt);

    $getdoc='SELECT doc.DOCUMENT_ID FROM proc_demandes dem JOIN proc_etape et ON et.ETAPE_ID=dem.ETAPE_ID JOIN proc_actions act on act.ETAPE_ID=et.ETAPE_ID JOIN proc_action_document act_doc ON act_doc.ACTION_ID=act.ACTION_ID JOIN proc_document doc ON doc.DOCUMENT_ID=act_doc.DOCUMENT_ID WHERE MD5(dem.ID_DEMANDE)="' . $ID_DEMANDE . '" AND doc.DOCUMENT_ID IN(3,4,5) ';
    $doc= "CALL getTable('" . $getdoc . "')";
    $data['document'] = $this->ModelPs->getRequeteOne($doc);


    //get demandes historique
    $getDemandeshistorique = 'SELECT pdh.DATE_INSERTION, pdh.COMMENTAIRE, pe.DESCR_ETAPE, pd.CODE_DEMANDE, u.NOM, u.PRENOM,pa.DESCR_ACTION
      FROM proc_demandes_historique pdh
      JOIN proc_etape pe ON pe.ETAPE_ID=pdh.ETAPE_ID
      JOIN proc_demandes pd ON pd.ID_DEMANDE=pdh.ID_DEMANDE
      JOIN user_users u ON pdh.USER_ID=u.USER_ID
      JOIN proc_actions pa ON pa.ACTION_ID=pdh.ACTION_ID
      WHERE md5(pdh.ID_DEMANDE)="' . $ID_DEMANDE . '" Order BY pdh.ID_DEMANDE DESC';
    $requeteDH = "CALL `getTable`('" . $getDemandeshistorique . "')";
    $demandes_histos = $this->ModelPs->getRequete($requeteDH);
    $data['demandes_histos'] = $demandes_histos;
    $data['ID_DEMANDE'] = $ID_DEMANDE;
    
    return view('App\Modules\process\Views\Demandes_Programmation_Budgetaire_Details_View', $data);
  }

  //fonction pour envoyer les observations
  public function send_data()
  {
    
    $array_post = [];

    foreach ($this->request->getPost() as $r) {
      array_push($array_post, $r);
    }

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if (empty($USER_ID)) {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ACTION_ID = $this->request->getPost('ACTION_ID');
    //vérifier valeur de IS_INITIAL et IS_REQUIRED
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getAction = $this->getBindParms('ACTION_ID,IS_INITIAL,IS_REQUIRED', 'proc_actions', 'ACTION_ID = ' . $ACTION_ID, ' ACTION_ID DESC');
    $action = $this->ModelPs->getRequeteOne($callpsreq, $getAction);

    $is_initial = $action['IS_INITIAL'];

    if ($action != '' || $action != null) {
      //set form_validation rules if commentaire is required
      if ($action['IS_REQUIRED'] == 1) {
        $rule =  [
          'COMMENTAIRE' => [
            'label' => '',
            'rules' => 'required',
            'errors' => [
              'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
            ]
          ]
        ];

        $this->validation->setRules($rule);

        if (!$this->validation->withRequest($this->request)->run()) {
          return redirect('/process/Demandes_Program_Budget');
        }
      }
    } else {
      return redirect('Login_Ptba/do_logout');
    }
    $ID_DEMANDE = '';
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $MOVETO = $this->request->getPost('MOVETO_INPUT');
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    //récupère le nom de l'input type date de date s'il y en a
    $INFOS_SUPP = $this->request->getPost('infosSupp');
    //récupère le nom de l'input type file s'il y en a
    $FILE_UPLOAD = $this->request->getPost('fileUpload');
    $lettre = $this->request->getFile('LETTRE_CADRAGE') ?? null;


    $proc_demand_histo_ID = '';
    $progr_budg_infos_supp_ID = '';


    $array_replace = ["\t", "\r", "\n", "`", "\"", "\\", "&", "/", "?", "|", "\'"];
    $COMMENTAIRE = str_replace($array_replace, "", $COMMENTAIRE);

    //insertion in "proc_demandes_historique" table
    $insertInto = 'proc_demandes_historique';
    $colum = "ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID,COMMENTAIRE";
    $datacolums = $ID_DEMANDE . ',' . $ETAPE_ID . ',' . $USER_ID . ',' . $ACTION_ID . ',"' . $COMMENTAIRE . '"';

    $bindparms = [$insertInto, $colum, $datacolums];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $insert_historique = $this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
    $IdHisto = $insert_historique['id'];

    //update "proc_demandes" table's "ETAPE_ID" attribute with "MOVETO" attribute
    //"from proc_actions" table
    $where = "ID_DEMANDE = " . $ID_DEMANDE;
    $insertInto = 'proc_demandes';
    $colum = "ETAPE_ID = '" . $MOVETO . "'";
    $this->update_all_table($insertInto, $colum, $where);

    //insert in progr_budg_infos_supp
    if ($is_initial == 1) {
      $insertInto = 'progr_budg_infos_supp';
      $colum = "ID_DEMANDE";
      $datacolums = $ID_DEMANDE;
      $this->save_all_table($insertInto, $colum, $datacolums);
    }
    //modifier la colonne is_end si c'est le dernier etape du process
    if ($is_initial == 5) {
      $update_into = "proc_demandes";
      $condition = "ID_DEMANDE=".$ID_DEMANDE;
      $data="IS_END=1";
      $this->update_all_table($update_into, $data, $condition);
    }

    $new_name='';
    if (!empty($lettre)) {
      $new_name = $this->uploadFile('LETTRE_CADRAGE', 'programmation_budgetaire', 'progr_budg');

      //mettre à jour la table progr_budg_infos_supp
      $where = "ID_DEMANDE = " . $ID_DEMANDE;
      $insertInto = 'progr_budg_infos_supp';
      $colum = " LETTRE_CADRAGE ='".$new_name."'";
      $this->update_all_table($insertInto, $colum, $where);
    }

    //add to progr_budg_infos_supp
    if (array_key_exists('infosSupp', $this->request->getPost()) && $this->request->getPost('infosSupp') != '') {

      //vérifier si DATE_PROGRAMMATION a du contenu
      $progr_budg_query = "SELECT `DATE_PROGRAMMATION` FROM `progr_budg_infos_supp` WHERE 1 AND ID_DEMANDE=" . $ID_DEMANDE;
      $getProgr_budg = 'CALL `getTable`("' . $progr_budg_query . '");';
      $progr_budg = $this->ModelPs->getRequeteOne($getProgr_budg);

      if (empty($progr_budg['DATE_PROGRAMMATION'])) {
        //mettre à jour la table progr_budg_infos_supp
        $where = "ID_DEMANDE = " . $ID_DEMANDE;
        $insertInto = 'progr_budg_infos_supp';
        $colum = " DATE_PROGRAMMATION = '" . $this->request->getPost($INFOS_SUPP) . "'";
        $this->update_all_table($insertInto, $colum, $where);
      } else {
        //mettre à jour la table progr_budg_infos_supp
        $where = "ID_DEMANDE = " . $ID_DEMANDE;
        $insertInto = 'progr_budg_infos_supp';
        $colum = " DATE_PRORAMMATION_2 = '" . $this->request->getPost($INFOS_SUPP) . "'";
        $this->update_all_table($insertInto, $colum, $where);
      }
    } elseif (array_key_exists('fileUpload', $this->request->getPost()) && $this->request->getPost('fileUpload') != '') {

      //upload file
      $new_name = $this->uploadFile($FILE_UPLOAD, 'programmation_budgetaire', 'progr_budg');

      //mettre à jour la table progr_budg_infos_supp
      $where = "ID_DEMANDE = " . $ID_DEMANDE;
      $insertInto = 'progr_budg_infos_supp';
      $colum = " PATH_PROJET_LOI_FINANCE = '" . $new_name . "'";
      $this->update_all_table($insertInto, $colum, $where);
    }

    $data = [
      'message' => ''.lang('messages_lang.msg_demande_success').''
    ];
    session()->setFlashdata('alert', $data);
    return redirect('process/Demandes_Program_Budget');
  }


  //get infos and docs
  public function get_infos_docs($ACTION_ID)
  {

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $getInfosDocs = 'SELECT DISTINCT proc_infos_supp.DESCR_INFOS_SUPP,proc_infos_supp.INFOS_NAME,proc_infos_supp.TYPE_INFOS_NAME,pdoc.DESC_DOCUMENT, pdoc.DOCUMENT_TYPE_ID,pdoc.DOCUMENT_NAME,pa.GET_FORM FROM proc_actions pa LEFT JOIN proc_action_infos_supp ON proc_action_infos_supp.ACTION_ID=pa.ACTION_ID LEFT JOIN proc_infos_supp ON proc_infos_supp.ID_INFOS_SUPP=proc_action_infos_supp.ID_INFOS_SUPP JOIN proc_action_document padoc ON padoc.ACTION_ID=pa.ACTION_ID JOIN proc_document pdoc ON pdoc.DOCUMENT_ID=padoc.DOCUMENT_ID WHERE pa.ACTION_ID=' . $ACTION_ID;

    $requeteAct = "CALL `getTable`('" . $getInfosDocs . "')";
    $output = $this->ModelPs->getRequete($requeteAct);

    echo json_encode($output);
  }


  //filter demandes historique
  public function listing_demandes_historique()
  {

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if (empty($USER_ID)) {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $profil = "SELECT `PROFIL_ID` FROM `user_users` WHERE 1 AND USER_ID=" . $USER_ID;
    $getProfil = 'CALL `getTable`("' . $profil . '");';

    $query_principal = "SELECT pdh.ID_HISTORIQUE,pdh.DATE_INSERTION, pdh.COMMENTAIRE, pe.DESCR_ETAPE, pd.CODE_DEMANDE, u.NOM, u.PRENOM,pa.DESCR_ACTION
    FROM proc_demandes_historique pdh
    JOIN proc_etape pe ON pe.ETAPE_ID=pdh.ETAPE_ID
    JOIN proc_demandes pd ON pd.ID_DEMANDE=pdh.ID_DEMANDE
    JOIN user_users u ON pdh.USER_ID=u.USER_ID
    JOIN proc_actions pa ON pa.ACTION_ID=pdh.ACTION_ID
    WHERE pdh.ID_DEMANDE=" . $ID_DEMANDE;

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,10';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column = '';
    $order_column = array('pdh.ID_HISTORIQUE', 'pd.CODE_DEMANDE', 'u.NOM', 'u.PRENOM', 'pe.DESCR_ETAPE', 'pdh.COMMENTAIRE', 1, 1, 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND ( pd.CODE_DEMANDE LIKE '%$var_search%' OR u.NOM LIKE '%$var_search%' OR u.PRENOM LIKE '%$var_search%' OR pe.DESCR_ETAPE LIKE '%$var_search%' OR pdh.DATE_INSERTION LIKE '%$var_search%')") : '';

    $critaire = '';
    $query_secondaire = $query_principal . " " . $search . " " . $critaire . " " . $order_by . " " . $limit;

    $query_filter = $query_principal . " " . $search . " " . $critaire;
    $requete = 'CALL `getList`("' . $query_secondaire . '")';
    $fetch_cov_frais = $this->ModelPs->datatable($requete);

    $data = array();
    $u = 1;
    foreach ($fetch_cov_frais as $info) {


      //Déscription de l'étape
      if(!empty($info->DESCR_ETAPE))
      {
        if(strlen($info->DESCR_ETAPE) > 6)
        {
          $DESCR_ETAPE =  mb_substr($info->DESCR_ETAPE, 0, 6) .'...<a class="btn-sm" title="'.$info->DESCR_ETAPE.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $DESCR_ETAPE = $info->DESCR_ETAPE;
        }
      }
      else
      {
         $DESCR_ETAPE = 'N/A';
      }

      //Déscription de l'action
      if(!empty($info->DESCR_ACTION))
      {
        if(strlen($info->DESCR_ACTION) > 6)
        {
          $DESCR_ACTION =  mb_substr($info->DESCR_ACTION, 0, 6) .'...<a class="btn-sm" title="'.$info->DESCR_ACTION.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $DESCR_ACTION = $info->DESCR_ACTION;
        }
      }
      else
      {
         $DESCR_ACTION = 'N/A';
      }


      //Déscription de l'étape
      if(!empty($info->DESCR_ETAPE))
      {
        if(strlen($info->NOM.' '.$info->PRENOM) > 3)
        {
          $nom =  mb_substr($info->NOM.' '.$info->PRENOM, 0, 4) .'...<a class="btn-sm" title="'.$info->NOM.' '.$info->PRENOM.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $nom = $info->NOM.' '.$info->PRENOM;
        }
      }
      else
      {
         $nom = 'N/A';
      }

      
      //Déscription de l'institution
      if(!empty($info->COMMENTAIRE))
      {
        if(strlen($info->COMMENTAIRE) > 6)
        {
          $COMMENTAIRE =  mb_substr($info->COMMENTAIRE, 0, 6) .'...<a class="btn-sm" title="'.$info->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $COMMENTAIRE = $info->COMMENTAIRE;
        }
      }
      else
      {
         $COMMENTAIRE = 'N/A';
      }


      $post = array();
      $post[] = $u++;
      $post[] = !empty($info->CODE_DEMANDE) ? $info->CODE_DEMANDE : 'N/A';
      $post[] = $DESCR_ETAPE;
      $post[] = $DESCR_ACTION;
      $post[] = $nom;
      $post[] = $COMMENTAIRE;
      $post[] = !empty($info->DATE_INSERTION) ? date('d-m-Y',strtotime($info->DATE_INSERTION)) : 'N/A';

      $data[] = $post;
    }

    $requeteqp = 'CALL `getList`("' . $query_principal . '")';
    $recordsTotal = $this->ModelPs->datatable($requeteqp);
    $requeteqf = 'CALL `getList`("' . $query_filter . '")';
    $recordsFiltered = $this->ModelPs->datatable($requeteqf);

    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }



  public function programmation_costab()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $id='';
    if(!empty($ID_DEMANDE))
    {
      $id=' AND ID_DEMANDE= '.$ID_DEMANDE;
    }

    
    $query_principal='SELECT costab.ID_PLANS_DEMANDE_COSTAB,costab.ID_DEMANDE,enj.DESCR_ENJEUX,p.DESCR_PILIER,axe.DESCR_AXE_INTERVATION_PND,ob.DESCR_OBJECTIF_STRATEGIC,inst.INTITULE_PROGRAMME,costab.BUDGET_ANNE_1,costab.BUDGET_ANNE_2,costab.BUDGET_ANNE_3,costab.BUDGET_ANNE_4,costab.BUDGET_ANNE_5,costab.BUDGET_TOTAL FROM planification_demande_costab costab INNER JOIN enjeux enj ON enj.ID_ENJEUX=costab.ID_ENJEUX INNER JOIN pilier p ON p.ID_PILIER=costab.ID_PILIER INNER JOIN axe_intervention_pnd axe ON axe.ID_AXE_INTERVENTION_PND=costab.ID_AXE_INTERVENTION_PND INNER JOIN objectif_strategique ob ON ob.ID_OBJECT_STRATEGIQUE=costab.ID_OBJECT_STRATEGIQUE INNER JOIN inst_institutions_programmes inst ON inst.PROGRAMME_ID=costab.PROGRAMME_ID WHERE ID_DEMANDE ="'. $ID_DEMANDE.'"';


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    //$var_search = str_replace("'", "''", $var_search);
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array(1,'enj.DESCR_ENJEUX','p.DESCR_PILIER', 'axe.DESCR_AXE_INTERVATION_PND','ob.DESCR_OBJECTIF_STRATEGIC','inst.INTITULE_PROGRAMME',1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY costab.ID_PLANS_DEMANDE_COSTAB ASC";

    $search = !empty($_POST['search']['value']) ?  (' AND (enj.DESCR_ENJEUX LIKE "%'.$var_search.'%" OR p.DESCR_PILIER LIKE "%'.$var_search.'%" OR axe.DESCR_AXE_INTERVATION_PND LIKE "%'.$var_search.'%" OR ob.DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR inst.INTITULE_PROGRAMME LIKE "%'.$var_search.'%")'):"";
    $search = str_replace("'","\'",$search);
    $critaire = " ";

    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;

    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
   // print_r($fetch_cov_frais);die();
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $row)
    {
      $sub_array = array();

      if(strlen($row->DESCR_ENJEUX) > 3)
      {
        $DESCR_ENJEUX =mb_substr($row->DESCR_ENJEUX, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_ENJEUX.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_ENJEUX =  $row->DESCR_ENJEUX;
      }

      if(strlen($row->DESCR_PILIER) > 3)
      {
        $DESCR_PILIER =  mb_substr($row->DESCR_PILIER, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_PILIER =  $row->DESCR_PILIER;
      }

      if(strlen($row->DESCR_AXE_INTERVATION_PND) > 3)
      {
        $DESCR_AXE_INTERVATION_PND =  mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_AXE_INTERVATION_PND =  $row->DESCR_AXE_INTERVATION_PND;
      }

      if(strlen($row->DESCR_OBJECTIF_STRATEGIC) > 3)
      {
        $DESCR_OBJECTIF_STRATEGIC =  mb_substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_OBJECTIF_STRATEGIC =  $row->DESCR_OBJECTIF_STRATEGIC;
      }

      if(strlen($row->INTITULE_PROGRAMME) > 3)
      {
        $INTITULE_PROGRAMME =  mb_substr($row->INTITULE_PROGRAMME, 0, 3) .'...<a class="btn-sm" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $INTITULE_PROGRAMME =  $row->INTITULE_PROGRAMME;
      }

      $sub_array[]=$u++;
      $sub_array[]=$DESCR_ENJEUX;
      $sub_array[]=$DESCR_PILIER;
      $sub_array[]=$DESCR_AXE_INTERVATION_PND;
      $sub_array[]=$DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$INTITULE_PROGRAMME;
     // $sub_array[]=$NOM_PROJET;
      $sub_array[]=number_format($row->BUDGET_ANNE_1,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_2,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_3,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_4,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_5,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_TOTAL,0,',',' ');
      $sub_array[]='edit';

      $data[] = $sub_array;
    }

    $requeteqp="CALL `getList`('".$query_principal."')";
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf="CALL `getList`('".$query_filter."')";
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }
    

  public function dem_grogramm_Budg()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $id='';

    $query_principal='SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr cl_cmr JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE =cl_cmr.ID_CL_CMR_CATEGORIE JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE  JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE cl_cmr.ID_DEMANDE='.$ID_DEMANDE.' AND cl_cmr.ID_CL_CMR_CATEGORIE=1';

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array(1,'pilier.DESCR_PILIER','objectif_strategique.DESCR_OBJECTIF_STRATEGIC', 'objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE','cl_cmr.PRECISIONS','cl_cmr.REFERENCE','cl_cmr.CIBLE');

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_PLANS_DEMANDE_CL_CMR ASC";

    $search = !empty($_POST['search']['value']) ?  (' AND (pilier.DESCR_PILIER LIKE "%'.$var_search.'%" OR objectif_strategique.DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE LIKE "%'.$var_search.'%" OR cl_cmr.PRECISIONS LIKE "%'.$var_search.'%" OR cl_cmr.REFERENCE LIKE "%'.$var_search.'%" OR cl_cmr.CIBLE LIKE "%'.$var_search.'%")'):"";
    $search = str_replace("'","\'",$search);
    $critaire = " ";

    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;

    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
   
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $row)
    {
      $sub_array = array();

      if(strlen($row->DESCR_PILIER) > 3)
      {
        $DESCR_PILIER =  mb_substr($row->DESCR_PILIER, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_PILIER =  $row->DESCR_PILIER;
      }

      if(strlen($row->DESCR_OBJECTIF_STRATEGIC) > 3)
      {
        $DESCR_OBJECTIF_STRATEGIC =  mb_substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_OBJECTIF_STRATEGIC =  $row->DESCR_OBJECTIF_STRATEGIC;
      }

      if(strlen($row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE) > 3)
      {
        $DESC_INDICACTEUR_OBJECT_STRATEGIQUE =  mb_substr($row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE, 0, 3) .'...<a class="btn-sm" title="'.$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESC_INDICACTEUR_OBJECT_STRATEGIQUE =  $row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      }

      $sub_array[]=$u++;
      $sub_array[]=$DESCR_PILIER;
      $sub_array[]=$DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      $sub_array[]=$row->REFERENCE;
      $sub_array[]=$row->CIBLE;
      $sub_array[]=$row->PRECISIONS;

      $sub_array[]='edit';
      $data[] = $sub_array;
    }

    $requeteqp="CALL `getList`('".$query_principal."')";
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf="CALL `getList`('".$query_filter."')";
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }



}
?>