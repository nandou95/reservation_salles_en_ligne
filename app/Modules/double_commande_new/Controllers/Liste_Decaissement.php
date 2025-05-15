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

    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
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
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger'];    

    return view('App\Modules\double_commande_new\Views\Liste_Decaissement_View', $data);
  }

  //fonction pour affichage d'une liste des décaissements à faire
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
    $cond_profil="";
    if ($profil_id!=1) 
    {
      $cond_profil=" AND etp_prof.PROFIL_ID=".$profil_id;
    }
    $critere1 = "";
    $critere2 = "";
    $crit_etap = " AND td.ETAPE_DOUBLE_COMMANDE_ID IN (SELECT DISTINCT td.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_etape_double_commande_profil etp_prof WHERE 1 ".$cond_profil.")";

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

    $group = "";
    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 6 ".$cond_profil." AND td.ETAPE_DOUBLE_COMMANDE_ID=29 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $order_column = array('exec.NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT', 'inst.CODE_NOMENCLATURE_BUDGETAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT','td.MONTANT_DECAISSEMENT',1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.DATE_VALIDE_TITRE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%'  OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR exec_detail.MONTANT_LIQUIDATION LIKE '%$var_search%' OR exec_detail.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR exec_detail.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR exec_detail.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $crit_etap;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

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
        $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";
        $bouton = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-arrow-up'></span></a>";
      }

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_DECAISSEMENT) : floatval($row->MONTANT_DECAISSEMENT_DEVISE);

      $action = '';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
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
      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/Liste_activite/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";
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


  function detail_task_dec()
  {
    $session  = \Config\Services::session();

    $task_id = $this->request->getPost('task_id');

    //Filtres de la liste
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";
    
    $critere3="";

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

    $requetedebase="SELECT DISTINCT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.EXECUTION_BUDGETAIRE_ID,task.PTBA_TACHE_ID,task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,task.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,task.MONTANT_ENG_JURIDIQUE,task.MONTANT_ENG_JURIDIQUE_DEVISE,task.MONTANT_LIQUIDATION,task.MONTANT_LIQUIDATION_DEVISE,task.MONTANT_ORDONNANCEMENT,task.MONTANT_ORDONNANCEMENT_DEVISE,task.MONTANT_PAIEMENT,task.MONTANT_PAIEMENT_DEVISE,task.MONTANT_DECAISSEMENT,task.MONTANT_DECAISSEMENT_DEVISE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$task_id."";

    $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE','task.MONTANT_ENG_JURIDIQUE','task.MONTANT_LIQUIDATION','task.MONTANT_ORDONNANCEMENT','task.MONTANT_PAIEMENT','task.MONTANT_DECAISSEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AN OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR task.MONTANT_PAIEMENT LIKE '%$var_search%' OR task.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR task.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR task.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3;

    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;
    
    // condition pour le query filter
    $conditionsfilter=$critaire." ".$search;
    
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    { 

      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;


      $MONTANT_ENG_BUDGETAIRE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ENG_BUDGETAIRE) : floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
      $MONTANT_ENG_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ENG_JURIDIQUE) : floatval($row->MONTANT_ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_DECAISSEMENT) : floatval($row->MONTANT_DECAISSEMENT_DEVISE);

      $action='';
      $sub_array = array();
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_ENG_BUDGETAIRE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ENG_JURIDIQUE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_DECAISSEMENT, 2, ",", " ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
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
