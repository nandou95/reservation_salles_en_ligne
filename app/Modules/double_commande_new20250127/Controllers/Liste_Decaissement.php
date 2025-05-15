<?php

/**
 * auteur: christa
 * tache: liste des décaissements selon les étapes
 * date: 09/11/2023
 * email: christa@mediabox.bi
 */

/**
 * modification
 * auteur  : Baleke Kahamire Bonheur
 * mail    : baleke.bonheur@mediabox.bi
 * date    : 01.02.2024 08:05
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Liste_Decaissement extends BaseController
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

  //Interface de la liste des decaissements
  function index()
  {
    $data = $this->urichk();
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['institutions'] = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?);", $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC'));
    $decais = $this->count_decaissement();
    $data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];

    $data_menu=$this->getDataMenuReception();
    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    return view('App\Modules\double_commande_new\Views\Liste_Decaissement_View', $data);
  }

  //fonction pour affichage d'une liste des activites
  public function listing()
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $critere2 = "";
    $crit_etap = " AND exec_detail.ETAPE_DOUBLE_COMMANDE_ID IN (SELECT DISTINCT exec_detail.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_etape_double_commande_profil WHERE PROFIL_ID=" . $profil_id . ")";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND budg.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('exec.NUMERO_BON_ENGAGEMENT', 'exec_detail.NUMERO_TITRE_DECAISSEMNT', 'activ.DESC_PAP_ACTIVITE', 'ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE', 'exec.ENG_BUDGETAIRE', 1, 'exec.ENG_JURIDIQUE', 1, 'exec_detail.MONTANT_LIQUIDATION', 1, 'exec_detail.MONTANT_ORDONNANCEMENT', 1, 1, 1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR activ.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec_detail.NUMERO_TITRE_DECAISSEMNT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $crit_etap;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID, exec.EXECUTION_BUDGETAIRE_ID,activ.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_PAIEMENT,exec_detail.MONTANT_DECAISSEMENT,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,exec_detail.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec_detail.NUMERO_TITRE_DECAISSEMNT,dc.DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_tache_detail exec_detail JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_detail.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites activ ON activ.PAP_ACTIVITE_ID = exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande_profil dcp ON dcp.ETAPE_DOUBLE_COMMANDE_ID = exec_detail.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = exec_detail.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 6 AND dcp.PROFIL_ID=".$profil_id." AND exec_detail.ETAPE_DOUBLE_COMMANDE_ID NOT IN(28,30)";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;

      $getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil WHERE ETAPE_DOUBLE_COMMANDE_ID ='.$row->ETAPE_DOUBLE_COMMANDE_ID;
      $getProf = "CALL getTable('" . $getProf . "');";
      $Profil_connect = $this->ModelPs->getRequeteOne($getProf);

      $prof_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $prof = (!empty($Profil_connect['PROFIL_ID'])) ? $Profil_connect['PROFIL_ID'] : 0;
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if ($prof_id == $prof || $prof_id == 1) 
      {
        $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";
        $bouton = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' ><span class='fa fa-arrow-up'></span></a>";
      }


      $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 6) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 6) . "...<a class='btn-sm' data-toggle='modal' ' title='" . $row->DESC_PAP_ACTIVITE . "'><i class='fa fa-eye'></i></a>") : $row->DESC_PAP_ACTIVITE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . "...<a class='btn-sm' data-toggle='modal' ' title='" . $row->COMMENTAIRE . "'><i class='fa fa-eye'></i></a>") : $row->COMMENTAIRE;
      $ETAPE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 4) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 4) . "...<a class='btn-sm' data-toggle='modal'  data-toggle='tooltip' title='" . $row->DESC_ETAPE_DOUBLE_COMMANDE . "'><i class='fa fa-eye'></i></a>") : $row->DESC_ETAPE_DOUBLE_COMMANDE;

      $MONTANT_BUDG = floatval($row->ENG_BUDGETAIRE);
      $MONTANT_JURIDIQUE = floatval($row->ENG_JURIDIQUE);
      $MONTANT_LIQUIDATION = floatval($row->MONTANT_LIQUIDATION);
      $MONTANT_ORDONNANCEMENT = floatval($row->MONTANT_ORDONNANCEMENT);
      $MONTANT_PAIEMENT = floatval($row->MONTANT_PAIEMENT);
      $MONTANT_DECAISSEMENT = floatval($row->MONTANT_DECAISSEMENT);

      $action = '';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->NUMERO_TITRE_DECAISSEMNT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $ACTIVITES;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = $ETAPE;
      $sub_array[] = number_format($MONTANT_BUDG, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_DECAISSEMENT, 2, ",", " ");
      //$action1 = '<div class="dropdown" style="color:#fff;"> "' . $bouton . '"';
      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";
      //$action1 = '<div class="dropdown" style="color:#fff;"> lorem </div>';
      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/Liste_activite/detail/' . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' ><span class='fa fa-plus'></span></a>";
      $sub_array[] = $action1;
      $sub_array[] = $action2;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //selectionner les etapes
  public function get_etape()
  {
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id = '';
    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $html = '<option value="">Sélectionner</option>';
    $MOUVEMENT_DEPENSE_ID = $this->request->getPost('MOUVEMENT_DEPENSE_ID');
    if (!empty($MOUVEMENT_DEPENSE_ID)) {
      $etape = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID ,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'PROFIL_ID=' . $profil_id . ' AND MOUVEMENT_DEPENSE_ID=' . $MOUVEMENT_DEPENSE_ID, 'DESC_ETAPE_DOUBLE_COMMANDE ASC');
      $get_etape = $this->ModelPs->getRequete($callpsreq, $etape);
    }

    foreach ($get_etape as $key) {
      $html .= "<option value='" . $key->ETAPE_DOUBLE_COMMANDE_ID . "'>" . $key->DESC_ETAPE_DOUBLE_COMMANDE . "</option>";
    }
    $output = array('status' => TRUE, 'html' => $html);
    return $this->response->setJSON($output); //echo json_encode($output);
  }

  function get_sous_titre($INSTITUTION_ID = 0)
  {    
    if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);
    $html = '<option value="">Sélectionner</option>';
    foreach ($sous_tutelle as $key) {
      $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
    }
    $output = ["sous_tutel" => $html];
    return $this->response->setJSON($output);
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
}
