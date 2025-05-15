<?php

/**
 * auteur: christa
 * tache: liste des paiements selon les étapes
 * date: 08/11/2023
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


class Liste_Paiement_Deja_Fait extends BaseController
{

  protected $library;
  protected $ModelPs;
  protected $session;
  protected $validation;

  function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  function index()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['paie_a_faire'] = $paiement['get_paie_afaire'];
    $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

    $data_menu=$this->getDataMenuReception();
    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    $data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_brb']=$data_titre['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$data_titre['get_bord_dc'];
    $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

    $validee = $this->count_validation_titre();
    $data['get_titre_valide'] = $validee['get_titre_valide'];
    $data['get_titre_termine'] = $validee['get_titre_termine'];

    $data_av_obr=$this->getDataMenuAVOBR();
    $data['get_nbr_av_obr']=$data_av_obr['get_nbr_av_obr'];
    $data['get_nbr_deja_av_obr']=$data_av_obr['get_nbr_deja_av_obr'];

    $data_av_pc=$this->getDataMenuAVPC();
    $data['get_nbr_av_pc']=$data_av_pc['get_nbr_av_pc'];
    $data['get_nbr_deja_av_pc']=$data_av_pc['get_nbr_deja_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION DESC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);
    return view('App\Modules\double_commande_new\Views\Liste_Paiement_Deja_Fait_View', $data);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  //fonction pour affichage d'une liste de paiements
  public function listing()
  {
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');

    if($this->session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
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

    $order_column = array('NUMERO_BON_ENGAGEMENT', 'NUMERO_TITRE_DECAISSEMNT', '1', 'ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE', 'exec.ENG_BUDGETAIRE', 1, 'exec.ENG_JURIDIQUE', 1, 'exec_detail.MONTANT_LIQUIDATION', 1, 'exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR NUMERO_TITRE_DECAISSEMNT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $crit_etap;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID,pap_activites.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_PAIEMENT,exec_detail.MONTANT_DECAISSEMENT,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,exec_detail.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec_detail.NUMERO_TITRE_DECAISSEMNT,dc.DESC_ETAPE_DOUBLE_COMMANDE,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire exec JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = exec_detail.ETAPE_DOUBLE_COMMANDE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 AND exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID IN(SELECT EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo WHERE USER_ID = ".$user_id.")";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";      

      $ACTIVITES = (mb_strlen($row->DESC_PAP_ACTIVITE) > 6) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 6) . "...<a class='btn-sm' title='" . $row->DESC_PAP_ACTIVITE . "'><i class='fa fa-eye'></i></a>") : $row->DESC_PAP_ACTIVITE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . "...<a class='btn-sm' title='" . $row->COMMENTAIRE . "'><i class='fa fa-eye'></i></a>") : $row->COMMENTAIRE;

      $ETAPE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 4) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 4) . "...<a class='btn-sm' title='" . $row->DESC_ETAPE_DOUBLE_COMMANDE . "'><i class='fa fa-eye'></i></a>") : $row->DESC_ETAPE_DOUBLE_COMMANDE;

      $MONTANT_BUDG = floatval($row->ENG_BUDGETAIRE);
      $MONTANT_JURIDIQUE = floatval($row->ENG_JURIDIQUE);
      $MONTANT_LIQUIDATION = floatval($row->MONTANT_LIQUIDATION);
      $MONTANT_ORDONNANCEMENT = floatval($row->MONTANT_ORDONNANCEMENT);

      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT; //$number;
      $sub_array[] = $row->NUMERO_TITRE_DECAISSEMNT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $ACTIVITES;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = $ETAPE;
      $sub_array[] = number_format($MONTANT_BUDG, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");

      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' ><span class='fa fa-plus'></span></a>";

      $sub_array[] = $action2;
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

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //récupération du sous titre par rapport à l'institution
  function get_sous_titre($INSTITUTION_ID = 0)
  {
    
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

    $html = '<option value="">' . lang('messages_lang.labelle_selecte') . '</option>';
    foreach ($sous_tutelle as $key) {
      $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
    }

    $output = array(
      "sous_tutel" => $html,
    );

    return $this->response->setJSON($output);
  }
}
