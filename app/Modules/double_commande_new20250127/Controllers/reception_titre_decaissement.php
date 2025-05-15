<?php

/**
 * jemapess MUGISHA
 * Titre: titre de decaissement
 * Numero de telephone: (+257) 68001621
 * WhatsApp: (+257) 68001621 
 * Email: jemapess.mugisha@mediabox.bi
 * Date: 08 jan 2024
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class reception_titre_decaissement extends BaseController
{
  protected $session;
  protected $ModelPs;
  protected $library;
  protected $validation;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  /**  page d'acceuil  */
  public function index(string $id_crypts)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //-- id detail --
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_etape = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = bon.EXECUTION_BUDGETAIRE_DETAIL_ID','MD5(bon.BORDEREAU_TRANSMISSION_ID)="'.$id_crypts.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
    $get_etape = str_replace('\\', '', $get_etape);
    $etap_borderaux_transmission_id = $this->ModelPs->getRequeteOne($callpsreq, $get_etape);
    $id_crypt=$etap_borderaux_transmission_id['EXECUTION_BUDGETAIRE_DETAIL_ID'];
    $id_crypt=md5($id_crypt);

    //--etape actuel --
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_etape = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id_crypt.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
    $get_etape = str_replace('\\', '', $get_etape);
    $etap_borderaux_transmission_id = $this->ModelPs->getRequeteOne($callpsreq, $get_etape);
    $etap_borderaux_transmission_id = 22;
    $data['etap_borderaux_transmission_id'] = $etap_borderaux_transmission_id;
    //print_r($etap_borderaux_transmission_id);exit();

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etap_borderaux_transmission_id,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if(!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          //Recuperation des titre de decaissements
          $requetebase3 = "SELECT DISTINCT bon_titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID,bon_titre.NUMERO_DOCUMENT,bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID FROM 
          execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN
          execution_budgetaire_tache_detail racc_detail  ON racc_detail.EXECUTION_BUDGETAIRE_DETAIL_ID = bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID
          WHERE md5(bon_titre.BORDEREAU_TRANSMISSION_ID) =\'".$id_crypts."\' AND  ETAPE_DOUBLE_COMMANDE_ID=\'".$etap_borderaux_transmission_id."\'";
          $fetch_data3 = $this->ModelPs->getRequete("CALL getTable ('".$requetebase3 ."')");
          $data['titre_decaissement'] = $fetch_data3;

          //- id transmission  num bord --
          $get_ids = $this->getBindParms('BORDEREAU_TRANSMISSION_ID,NUMERO_BORDEREAU_TRANSMISSION',' execution_budgetaire_bordereau_transmission','MD5(BORDEREAU_TRANSMISSION_ID)="'.$id_crypts.'"','BORDEREAU_TRANSMISSION_ID  DESC');
          $get_ids= str_replace('\\','',$get_ids);
          $borderaux_transmission=$this->ModelPs->getRequeteOne($callpsreq,$get_ids);
          $data['BORDEREAU_TRANSMISSION_ID']=$borderaux_transmission['BORDEREAU_TRANSMISSION_ID'];
          $data['NUMERO_BORDEREAU_TRANSMISSION']=$borderaux_transmission['NUMERO_BORDEREAU_TRANSMISSION'];

          //--titre etape
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID ='.$etap_borderaux_transmission_id,' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre = $this->ModelPs->getRequeteOne($callpsreq, $titre);
          $data['titre_etape'] = $titre;

          //--date transmission
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $get_hist = $this->getBindParms(' EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5( EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id_crypt.'"', ' DATE_INSERTION DESC');
          $get_hist = str_replace('\\', '', $get_hist);
          $data['hist'] = $this->ModelPs->getRequeteOne($callpsreq, $get_hist);

          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $get_hist = $this->getBindParms('EXECUTION_BUDGETAIRE_ID','execution_budgetaire_tache_detail','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id_crypts.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $get_hist=str_replace('\\', '', $get_hist);
          $EXECUTION_BUDGETAIREE_ID=$this->ModelPs->getRequeteOne($callpsreq, $get_hist);

          $detail = $this->detail_new($id_crypt);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\reception_titre_decaissement_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }    
  }

  /*fonction d'insertion*/
  public function store()
  {
    $session  = \Config\Services::session();  
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $etape_actuel = $this->request->getPost("etap_borderaux_transmission_id");
    $BORDEREAU_TRANSMISSION_ID = $this->request->getPost("BORDEREAU_TRANSMISSION_ID");
    $date_reception_transmission = $this->request->getPost("date_insertion_check");
    $date_reception = $this->request->getPost('DATE_RECEPTION');
    $NUMERO_BORDEREAU_TRANSMISSION = $this->request->getPost("NUMERO_BORDEREAU_TRANSMISSION");
    $titre_decaissement = $this->request->getPost("titre_decaissement[]");
    $date_transmission = $this->request->getPost("DATE_TRANSMISSION");

    $success = false;

    //--update-execution budgetaire bordereau transmission new-
    $conditions_2 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_TRANSMISSION_ID;
    $datatomodifie_2 = 'STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2';
    $this->update_all_table('execution_budgetaire_bordereau_transmission',$datatomodifie_2,$conditions_2);

    //-etape suivante-
    $id_etape_suivante = 23;
    //-mettre a jour tous les titres a 3 (pas reception)-
    $conditions_2 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_TRANSMISSION_ID;
    $datatomodifie_2 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=3';
    $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre',$datatomodifie_2,$conditions_2);

    //update  execution_budgetaire_bordereau_transmission_bon_titre-
    $etape_en_cour_id=22;
    foreach ($titre_decaissement as $value)
    {
      $conditions_3='BORDEREAU_TRANSMISSION_ID='.$BORDEREAU_TRANSMISSION_ID.' AND EXECUTION_BUDGETAIRE_DETAIL_ID='.$value;
      $datatomodifie_3 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
      $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre',$datatomodifie_3,$conditions_3);

      //insertion dans l'historique
      $column_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
      $data_histo=$value.','.$etape_en_cour_id.','.$user_id.',"'.$date_reception.'","'.$date_transmission.'"';
      $this->save_histo_racrochage($column_histo, $data_histo);

      //--update dans la table 'execution_budgetaire_raccrochage_activite_detail' --
      $updateTable = 'execution_budgetaire_tache_detail';
      $critere = "EXECUTION_BUDGETAIRE_DETAIL_ID=".$value;
      $datatoupdate = 'ETAPE_DOUBLE_COMMANDE_ID='. $id_etape_suivante;
      $bindparams = [$updateTable, $datatoupdate, $critere]; 
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
    }
    $data = ['message' => lang('messages_lang.message_success')];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/Bordereau_Recu_Dir_Comptabilite');
  }
  /**  * faire le live search  */

  public function search_engagement()
  {
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $search = $this->request->getPost("search_element") ?? "";
    $etape_id = $this->request->getPost("id_etap");
    $numero_bordereau = $this->request->getPost("numero_borderaux");
    $request = "SELECT DISTINCT execution_budgetaire_raccrochage_activite_detail.NUMERO_TITRE_DECAISSEMNT, execution_budgetaire_raccrochage_activite_detail.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM execution_raccrochage_bordereau_transmission_new JOIN execution_bord_transmission ON execution_raccrochage_bordereau_transmission_new.EXECUTION_BORD_TRANSMISSION_ID = execution_bord_transmission.EXECUTION_BORD_TRANSMISSION_ID JOIN execution_budgetaire_raccrochage_activite_detail ON execution_raccrochage_bordereau_transmission_new.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID = execution_budgetaire_raccrochage_activite_detail.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID WHERE
    execution_budgetaire_raccrochage_activite_detail.ETAPE_DOUBLE_COMMANDE_ID =" . $etape_id . " AND execution_bord_transmission.NUM_BORDEREAU_TRANSMISSION = \'" . $numero_bordereau . "\'
    AND execution_budgetaire_raccrochage_activite_detail.NUMERO_TITRE_DECAISSEMNT LIKE  \'%" . $search . "%\'";
   $search_element = $this->ModelPs->getRequete("CALL `getTable` ('" . $request . "')");

    if (empty($search_element)) {
      //Declaration des labels pour l'internalisation
      $no_data_found = lang("messages_lang.no_data_found");
      //Envoie du message au view
      return $this->response->setJSON(["data" => $no_data_found])->setStatusCode(500, "Aucun element n'a ete trouve");
    } else {
      $element_search_element = "";
      foreach ($search_element as $se) {
        $element_search_element .= "<li class='list-group-item' style='padding-left: 40px;'> <input class='form-check-input me-2' type='checkbox' name='bon_engagement[]' value='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "' id='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "_" . $se->NUMERO_TITRE_DECAISSEMNT . "'> <label class='form-check-label stretched-link' for='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "_" . $se->NUMERO_TITRE_DECAISSEMNT . "'> " . $se->NUMERO_TITRE_DECAISSEMNT . " </label> </li>";
      }
      echo json_encode($element_search_element);
    }
  }
  public function search_engagement3()
  {
    $session  = \Config\Services::session();   
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $search = $this->request->getPost("search_element") ?? "";
    $etape_id = $this->request->getPost("id_etap");
    $numero_bordereau = $this->request->getPost("numero_borderaux");
    $request5 = "SELECT DISTINCT execution_budgetaire_raccrochage_activite_detail.NUMERO_TITRE_DECAISSEMNT, execution_budgetaire_raccrochage_activite_detail.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM execution_raccrochage_bordereau_transmission_new JOIN execution_bord_transmission ON execution_raccrochage_bordereau_transmission_new.EXECUTION_BORD_TRANSMISSION_ID = execution_bord_transmission.EXECUTION_BORD_TRANSMISSION_ID JOIN execution_budgetaire_raccrochage_activite_detail ON execution_raccrochage_bordereau_transmission_new.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID = execution_budgetaire_raccrochage_activite_detail.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID WHERE
    execution_budgetaire_raccrochage_activite_detail.ETAPE_DOUBLE_COMMANDE_ID =" . $etape_id . " AND execution_bord_transmission.NUM_BORDEREAU_TRANSMISSION = \'" . $numero_bordereau . "\'
    AND execution_budgetaire_raccrochage_activite_detail.NUMERO_TITRE_DECAISSEMNT LIKE  \'%" . $search . "%\'";
   $search_element5 = $this->ModelPs->getRequete("CALL `getTable` ('" . $request5 . "')");

    $element_search_element = "";
    foreach ($search_element5 as $se) {
      $element_search_element .= "<li class='list-group-item' style='padding-left: 40px;'> <input class='form-check-input me-2' type='checkbox' name='bon_engagement[]' value='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "' id='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "_" . $se->NUMERO_TITRE_DECAISSEMNT . "'> <label class='form-check-label stretched-link' for='" . $se->EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID . "_" . $se->NUMERO_TITRE_DECAISSEMNT . "'> " . $se->NUMERO_TITRE_DECAISSEMNT . " </label> </li>";
    }
    echo json_encode($element_search_element);
  }

  /**
   * @param $columnselect   : le colone qu'on veux selectionner
   * @param $table          : la table au quelle on veux recupere les donnees
   * @param $where          : les condition a faire
   * @param $orederby       : ordonner par
   * requeter pour recuper les donne dans les table
   */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  /**
   * @param $table            : table dans le quelle on va insert
   * @param $columsinsert     : les colomn das les quelle on va insert
   * @param $dataculuminsert  : les donnees a insert
   * methode d'insertion dans la base de donne
   */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $result['id'];
  }
  public function save_histo_racrochage($columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table = ' execution_budgetaire_tache_detail_histo';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }
  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }  
}
?>