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

class Liste_Decaissement_Deja_Fait extends BaseController
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

  //Interface de la liste des activites
  function index()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['institutions'] = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?);", $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC'));
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

    return view('App\Modules\double_commande_new\Views\Liste_Decaissement_Deja_Fait_View', $data);
  }

  //fonction pour affichage d'une liste des activites
  public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $critere2 = "";
    $crit_etap = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
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

    $order_column = array('exec.NUMERO_BON_ENGAGEMENT', 'execdet.NUMERO_TITRE_DECAISSEMNT', 'activ.DESC_PAP_ACTIVITE', 'inst.CODE_NOMENCLATURE_BUDGETAIRE', 'exec.ENG_BUDGETAIRE', 1, 'exec.ENG_JURIDIQUE', 1, 'execdet.MONTANT_LIQUIDATION', 1, 'execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_DECAISSEMENT', 1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR execdet.NUMERO_TITRE_DECAISSEMNT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR execdet.MONTANT_PAIEMENT LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION LIKE '%$var_search%' OR execdet.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $crit_etap;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT execdet.MONTANT_DECAISSEMENT_DEVISE,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID, exec.EXECUTION_BUDGETAIRE_ID,activ.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_PAIEMENT,execdet.MONTANT_DECAISSEMENT,inst.CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,execdet.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,execdet.NUMERO_TITRE_DECAISSEMNT,dc.DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites activ ON activ.PAP_ACTIVITE_ID = exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = execdet.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND execdet.ETAPE_DOUBLE_COMMANDE_ID=30";

    $requetedebases = $requetedebase . " " . $conditions;

    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;

    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';

    //print_r($query_secondaire);exit();

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
     
      $number = $row->NUMERO_BON_ENGAGEMENT;
      $bouton = "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 8) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 8) . "...<a class='btn-sm'  title='" . $row->DESC_PAP_ACTIVITE . "'><i class='fa fa-eye'></i></a>") : $row->DESC_PAP_ACTIVITE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 8) ? (mb_substr($row->COMMENTAIRE, 0, 8) . "...<a class='btn-sm' title='" . $row->COMMENTAIRE . "'><center><i class='fa fa-eye'></i></center></a>") : $row->COMMENTAIRE;
      $ETAPE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 8) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 8) . "...<a class='btn-sm' title='" . $row->DESC_ETAPE_DOUBLE_COMMANDE . "'><center><i class='fa fa-eye'></i></center></a>") : $row->DESC_ETAPE_DOUBLE_COMMANDE;

      $MONTANT_BUDG = floatval($row->ENG_BUDGETAIRE);
      $MONTANT_JURIDIQUE = floatval($row->ENG_JURIDIQUE);
      $MONTANT_LIQUIDATION = floatval($row->MONTANT_LIQUIDATION);
      $MONTANT_ORDONNANCEMENT = floatval($row->MONTANT_ORDONNANCEMENT);
      $MONTANT_PAIEMENT = floatval($row->MONTANT_PAIEMENT);
  
      $action = '';
      $sub_array = array();
      // $sub_array[] = $number;
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
      $sub_array[] = number_format(floatval($row->MONTANT_DECAISSEMENT), 2, ",", " ");

      $action1 = '<div class="dropdown" style="color:#fff;">
        "' . $bouton . '"';
      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande/detail/' . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' ><span class='fa fa-plus'></span></a>";
      // $sub_array[] = $action1;
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
