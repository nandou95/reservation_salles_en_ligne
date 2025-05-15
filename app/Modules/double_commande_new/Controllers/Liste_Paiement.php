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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Liste_Paiement extends BaseController
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

    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
  }

  public function change_count()
  {
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');

    $paiement=$this->count_paiement($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);

    $data['get_recep_obr']="<span>".$paiement['get_recep_obr']."</span>";
    $data['get_prise_charge']="<span>".$paiement['get_prise_charge']."</span>";
    $data['get_etab_titre']="<span>".$paiement['get_etab_titre']."</span>";
    $data['get_sign_dir_compt']="<span>".$paiement['get_sign_dir_compt']."</span>";
    $data['get_sign_dir_dgfp']="<span>".$paiement['get_sign_dir_dgfp']."</span>";
    $data['get_sign_ministre']="<span>".$paiement['get_sign_ministre']."</span>";
    $data['get_prise_charge_corr']="<span>".$paiement['get_prise_charge_corr']."</span>";
    $data['get_etab_titre_corr']="<span>".$paiement['get_etab_titre_corr']."</span>";
    $data['get_etape_corr']="<span>".$paiement['get_etape_corr']."</span>";
    $data['get_etape_reject_pc']="<span>".$paiement['get_etape_reject_pc']."</span>";
    $data['get_recep_td_corriger']="<span>".$paiement['get_recep_td_corriger']."</span>";
    $data['recep_prise_charge']="<span>".$paiement['recep_prise_charge']."</span>";
    $data['deja_recep_prise_charge']="<span>".$paiement['deja_recep_prise_charge']."</span>";
    $data['recep_dir_comptable']="<span>".$paiement['recep_dir_comptable']."</span>";
    $data['deja_recep_dir_comptable']="<span>".$paiement['deja_recep_dir_comptable']."</span>";
    $data['get_bord_brb']="<span>".$paiement['get_bord_brb']."</span>";
    $data['get_bord_deja_trans_brb']="<span>".$paiement['get_bord_deja_trans_brb']."</span>";
    $data['get_bord_dc']="<span>".$paiement['get_bord_dc']."</span>";
    $data['get_bord_deja_dc']="<span>".$paiement['get_bord_deja_dc']."</span>";
    $data['get_titre_valide'] = "<span>".$paiement['get_titre_valide']."</span>";
    $data['get_titre_termine'] = "<span>".$paiement['get_titre_termine']."</span>";
    $data['get_nbr_av_obr']="<span>".$paiement['get_nbr_av_obr']."</span>";
    $data['get_nbr_av_pc']="<span>".$paiement['get_nbr_av_pc']."</span>";
    return $this->response->setJSON($data);
  }

  function detail_task_ordo()
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

    $requetedebase="SELECT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.EXECUTION_BUDGETAIRE_ID,task.PTBA_TACHE_ID,task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,ptba.DESC_TACHE,task.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,task.MONTANT_ENG_JURIDIQUE,task.MONTANT_ENG_JURIDIQUE_DEVISE,task.MONTANT_LIQUIDATION,task.MONTANT_LIQUIDATION_DEVISE,task.MONTANT_ORDONNANCEMENT,task.MONTANT_ORDONNANCEMENT_DEVISE,task.MONTANT_PAIEMENT,task.MONTANT_PAIEMENT_DEVISE,task.MONTANT_DECAISSEMENT,task.MONTANT_DECAISSEMENT_DEVISE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$task_id."";

    $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE','task.MONTANT_ENG_JURIDIQUE','task.MONTANT_LIQUIDATION','task.MONTANT_ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3;

    // print_r($critaire);die();
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

      $action='';
      $sub_array = array();
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_ENG_BUDGETAIRE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ENG_JURIDIQUE, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
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

  function detail_task_pay()
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

    $requetedebase="SELECT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.EXECUTION_BUDGETAIRE_ID,task.PTBA_TACHE_ID,task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,ptba.DESC_TACHE,task.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,task.MONTANT_ENG_JURIDIQUE,task.MONTANT_ENG_JURIDIQUE_DEVISE,task.MONTANT_LIQUIDATION,task.MONTANT_LIQUIDATION_DEVISE,task.MONTANT_ORDONNANCEMENT,task.MONTANT_ORDONNANCEMENT_DEVISE,task.MONTANT_PAIEMENT,task.MONTANT_PAIEMENT_DEVISE,task.MONTANT_DECAISSEMENT,task.MONTANT_DECAISSEMENT_DEVISE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$task_id."";

    $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE','task.MONTANT_ENG_JURIDIQUE','task.MONTANT_LIQUIDATION','task.MONTANT_ORDONNANCEMENT','task.MONTANT_PAIEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR task.MONTANT_PAIEMENT LIKE '%$var_search%' OR task.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3;

    // print_r($critaire);die();
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

  //Redirections pour le sous-menu de Paiement
  function index()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') ==1)
    {
      return redirect('double_commande_new/Liste_Reception_Prise_Charge'); 
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_OBR')==1)
    {
      return redirect('double_commande_new/Liste_Avant_OBR');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_obr');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE')==1)
    {
      return redirect('double_commande_new/Liste_Avant_PC');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_prise_charge');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_etab_titre');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE')==1)
    {
      return redirect('double_commande_new/Transmission_Directeur_Comptable_List');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE')==1)
    {
      return redirect('double_commande_new/Receptio_Border_Dir_compt');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_sign_dir_compt');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_sign_dgfp');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE')==1)
    {
      return redirect('double_commande_new/Liste_Paiement/vue_sign_ministre');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1)
    {
      return redirect('double_commande_new/Validation_Titre/liste_valide_faire');
    }
    else if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB')==1)
    {
      return redirect('double_commande_new/Liste_transmission_bordereau_a_transmettre_brb');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  //vue obr
  function vue_obr()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if (empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    return view('App\Modules\double_commande_new\Views\Liste_Obr_View', $data);
  }

  //fonction pour affichage d'une liste des reception dans obr
  public function listing_obr()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 18;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

    if (!empty($INSTITUTION_ID))
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID))
      {
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','1','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = "";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
      }

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue liste prise en charge
  function vue_prise_charge()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    $etap = $this->getBindParms('dc.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=etp_prof.ETAPE_DOUBLE_COMMANDE_ID', 'etp_prof.PROFIL_ID=' . $profil_id, ' DESC_ETAPE_DOUBLE_COMMANDE ASC');
    $data['etapes'] = $this->ModelPs->getRequete($callpsreq, $etap);

    return view('App\Modules\double_commande_new\Views\Liste_Prise_Charge_Comptable_View', $data);
  }

  //fonction pour affichage d'une liste de prise en charge
  public function listing_prise_charge()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 19;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

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

    $order_column = array('NUMERO_BON_ENGAGEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','1','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND (exec_detail.USER_AFFECTE_ID=".$user_id." OR exec_detail.USER_AFFECTE_ID='' OR exec_detail.USER_AFFECTE_ID IS NULL)";

    $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
      }

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue correction prise en charge
  function vue_correct_pc()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];

    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    return view('App\Modules\double_commande_new\Views\Liste_Correct_Prise_Charge_View', $data);
  }

  //fonction pour affichage d'une liste des corrdections pour prise en charge
  public function listing_correct_pc()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 39;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND ptba.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND ptba.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','1','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail  ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue liste établissement du titre de décaissement
  function vue_etab_titre()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    $etap = $this->getBindParms('dc.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=etp_prof.ETAPE_DOUBLE_COMMANDE_ID', 'etp_prof.PROFIL_ID=' . $profil_id, ' DESC_ETAPE_DOUBLE_COMMANDE ASC');
    $data['etapes'] = $this->ModelPs->getRequete($callpsreq, $etap);

    return view('App\Modules\double_commande_new\Views\Liste_Etablissement_Titre_View', $data);
  }

  //fonction pour le listing etablissement du titre
  public function listing_etab_titre()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 20;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

    if (!empty($INSTITUTION_ID))
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID))
      {
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = "";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue correction prise en charge
  function vue_correct_etab_titre()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    return view('App\Modules\double_commande_new\Views\Liste_Correct_Etab_Titre_View', $data);
  }

  //fonction pour affichage d'une liste des corrdections pour prise en charge
  public function listing_correct_etab_titre()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 37;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

    if (!empty($INSTITUTION_ID))
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID))
      {
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue Decision Retour a la correction 
  function vue_correct_etape()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];

    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    return view('App\Modules\double_commande_new\Views\Liste_Correct_Etape_View', $data);
  }

  //fonction pour affichage d'une liste des corrdections pour prise en charge
  public function listing_correct_etape()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 38;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";

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

    $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  //vue liste signature par le directeur de la comptabilité
  function vue_sign_dir_compt()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'CODE_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    $ANNEE_BUDGETAIRE_ID=0;
    $INSTITUTION_ID=$data['institutions'][0]->INSTITUTION_ID;
    $SOUS_TUTEL_ID=0;
    $DATE_DEBUT=0;
    $DATE_FIN=0;

    $paiement = $this->count_paiement($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $etap = $this->getBindParms('dc.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=etp_prof.ETAPE_DOUBLE_COMMANDE_ID', 'etp_prof.PROFIL_ID=' . $profil_id, ' DESC_ETAPE_DOUBLE_COMMANDE ASC');
    $data['etapes'] = $this->ModelPs->getRequete($callpsreq, $etap);

    $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
      $user_inst_res = 'CALL getTable("'.$user_inst.'");';
      $institutions_user = $this->ModelPs->getRequete($user_inst_res);
      $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
      $data['first_element_id'] = $INSTITUTION_ID;

    return view('App\Modules\double_commande_new\Views\Liste_Sign_Dir_Compt_View', $data);
  }

  //fonction pour le listing signature directeur de la comptabilité
  public function listing_sign_dir_compt()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 23;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }


    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=' AND td.DATE_ELABORATION_TD >= "'.$DATE_DEBUT.'"';
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=' AND td.DATE_ELABORATION_TD >= "'.$DATE_DEBUT.'" AND td.DATE_ELABORATION_TD <= "'.$DATE_FIN.'"';
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
  
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1." ".$critere2." ".$critere3." ".$critere4." ";

    $group = " ";
    $critaire ="";
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
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
      }

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL getTable("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

    return $this->response->setJSON($output);
  }

  //vue liste signature par DGFP
  function vue_sign_dgfp()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    $etap = $this->getBindParms('dc.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=etp_prof.ETAPE_DOUBLE_COMMANDE_ID', 'etp_prof.PROFIL_ID=' . $profil_id, ' DESC_ETAPE_DOUBLE_COMMANDE ASC');
    $data['etapes'] = $this->ModelPs->getRequete($callpsreq, $etap);

    return view('App\Modules\double_commande_new\Views\Liste_Sign_DGFP_View', $data);
  }

  //fonction pour le listing signature dgfp
  public function listing_sign_dgfp()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 24;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT;','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = " ";

    $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  // Exporter la liste excel des Signature sur le titre de décaissement par le DGFP
  function exporter_Excel_sign_td_dgfp($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $session  = \Config\Services::session();
    $user_id=$session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP');
    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 24;
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1=" ";
    $critere2=" ";
    $critere3=" ";
    $critere4=" ";
    if($INSTITUTION_ID>0)
    {
       $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if($SOUS_TUTEL_ID>0)
    {
      $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
    }

    $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$critere1.$critere2.$critere3.$critere4.$group;

    $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C1', 'SIGNATURE SUR LE TITRE DE DECAISSEMENT PAR LE DGFP');
    $sheet->setCellValue('A3', '#');
    $sheet->setCellValue('B3', 'BON ENGAGEMENT');
    $sheet->setCellValue('C3', 'TITRE DECAISSEMENT');
    $sheet->setCellValue('D3', 'IMPUTATION');
    $sheet->setCellValue('E3', 'TACHE');
    $sheet->setCellValue('F3', 'DEVISE');
    $sheet->setCellValue('G3', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('H3', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('I3', 'LIQUIDATION');
    $sheet->setCellValue('J3', 'ORDONNANCEMENT');
    $sheet->setCellValue('K3', 'PAIEMENT');

    $rows = 4;
    $i=1;
    foreach ($getData as $key)
    {
      //get les taches
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_JURIDIQUE) : floatval($key->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_LIQUIDATION) : floatval($key->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_ORDONNANCEMENT) : floatval($key->MONTANT_ORDONNANCEMENT_DEVISE);
      $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->TITRE_DECAISSEMENT);
      $sheet->setCellValue('D' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
      $sheet->setCellValue('E' . $rows, trim($task_items));
      $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('F' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('G' . $rows, number_format($MONTANT_BUDG, 4, ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($MONTANT_JURIDIQUE, 4, ",", " "));
      $sheet->setCellValue('I' . $rows, number_format($MONTANT_LIQUIDATION, 4, ",", " "));
      $sheet->setCellValue('J' . $rows, number_format($MONTANT_ORDONNANCEMENT, 4, ",", " "));
      $sheet->setCellValue('K' . $rows, number_format($MONTANT_PAIEMENT, 4, ",", " "));
     
      $rows++;
      $i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('sign_td_dgfp'.$code.'.xlsx');

    return redirect('double_commande_new/Liste_Paiement/vue_sign_dgfp');
  }

  //vue liste signature par le ministre
  function vue_sign_ministre()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $paiement = $this->count_paiement();
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];    
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

    return view('App\Modules\double_commande_new\Views\Liste_Sign_Ministre_View', $data);
  }

  //fonction pour le listing signature ministre
  public function listing_sign_ministre()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 25;

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

    $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $group = " ";

    $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_actions as $row) {
      $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
      $dist = "";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
      $number = $row->NUMERO_BON_ENGAGEMENT;
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

            $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
          }
        }
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
      
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>" . $bouton . "</div>";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

      $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action1.$action2."</div>";
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

  // Exporter la liste excel des Signature sur le titre de décaissement par le ministre
  function exporter_Excel_sign_td_ministre($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id=$session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE');
    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ETAPE_DOUBLE_COMMANDE_ID = 25;
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $critere1=" ";
    $critere2=" ";
    $critere3=" ";
    $critere4=" ";
    if($INSTITUTION_ID>0)
    {
       $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if($SOUS_TUTEL_ID>0)
    {
      $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
    }

    $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$critere1.$critere2.$critere3.$critere4.$group;

    $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C1', 'SIGNATURE SUR LE TITRE DE DECAISSEMENT PAR LE MINISTRE');
    $sheet->setCellValue('A3', '#');
    $sheet->setCellValue('B3', 'BON ENGAGEMENT');
    $sheet->setCellValue('C3', 'TITRE DECAISSEMENT');
    $sheet->setCellValue('D3', 'IMPUTATION');
    $sheet->setCellValue('E3', 'TACHE');
    $sheet->setCellValue('F3', 'DEVISE');
    $sheet->setCellValue('G3', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('H3', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('I3', 'LIQUIDATION');
    $sheet->setCellValue('J3', 'ORDONNANCEMENT');
    $sheet->setCellValue('K3', 'PAIEMENT');

    $rows = 4;
    $i=1;
    foreach ($getData as $key)
    {
      //get les taches
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_JURIDIQUE) : floatval($key->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_LIQUIDATION) : floatval($key->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_ORDONNANCEMENT) : floatval($key->MONTANT_ORDONNANCEMENT_DEVISE);
      $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->TITRE_DECAISSEMENT);
      $sheet->setCellValue('D' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
      $sheet->setCellValue('E' . $rows, trim($task_items));
      $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('F' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('G' . $rows, number_format($MONTANT_BUDG, 4, ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($MONTANT_JURIDIQUE, 4, ",", " "));
      $sheet->setCellValue('I' . $rows, number_format($MONTANT_LIQUIDATION, 4, ",", " "));
      $sheet->setCellValue('J' . $rows, number_format($MONTANT_ORDONNANCEMENT, 4, ",", " "));
      $sheet->setCellValue('K' . $rows, number_format($MONTANT_PAIEMENT, 4, ",", " "));
     
      $rows++;
      $i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('sign_td_ministre'.$code.'.xlsx');

    return redirect('double_commande_new/Liste_Paiement/vue_sign_ministre');
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  //récupération du sous titre par rapport à l'institution
  function get_sous_titre($INSTITUTION_ID = 0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

    $html = '<option value="">' . lang('messages_lang.labelle_selecte') . '</option>';
    foreach ($sous_tutelle as $key)
    {
      $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
    }

    $output = array(
      "sous_tutel" => $html,
    );

    return $this->response->setJSON($output);
  }
}
?>