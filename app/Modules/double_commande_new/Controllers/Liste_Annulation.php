<?php

/**
 * auteur: RUGAMBA Jean Vainqueur
 * tache: listes des annulations
 * date: 27/08/2024
 * Téléphone: (+257) 66 33 43 25 / (+257) 62 47 19 15
 * email: jean.vainqueur@mediabox.bi
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Liste_Annulation extends BaseController
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

  //vue annulation prise en charge
  function annulation_pc()
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

    return view('App\Modules\double_commande_new\Views\Liste_Annulation_Prise_Charge_View', $data);
  }

  //fonction pour affichage d'une liste des annulations pour prise en charge
  public function listing_annulation_pc()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 41;

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

    $order_column = array('NUMERO_BON_ENGAGEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

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

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' title='' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";

      $sub_array[] = $action1;
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

  //vue annulation ordonnancement
  function annulation_ordo()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuOrdonnancement();
    $data['institutions_user']=$data_menu['institutions_user'];
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
    $data['get_bord_spe']=$data_menu['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
    $data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
    $data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
    $data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];
    $data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];

    return view('App\Modules\double_commande_new\Views\Liste_Annulation_Ordo_View', $data);
  }

  //fonction pour affichage d'une liste des annulations de l'ordonnancement
  public function listing_annulation_ordo()
  {
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $ETAPE_DOUBLE_COMMANDE_ID = 40;

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
    $group = " ";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('NUMERO_BON_ENGAGEMENT', 'NUMERO_TITRE_DECAISSEMNT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE',1,'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ebtd.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,ebtd.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 4 ".$cond_prof." AND ebtd.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

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

      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      
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

      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";
      $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' title='' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)) . "' ><span class='fa fa-plus'></span></a>";

      $sub_array[] = $action1;
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