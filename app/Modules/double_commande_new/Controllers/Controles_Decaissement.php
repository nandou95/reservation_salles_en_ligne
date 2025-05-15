<?php
/**
 * auteur: joa-kevin iradukunda
 * tache: liste des controles de decaissement par brb et besd
 * date: 31/10/2024
 * email: joa-kevin.iradukunda@mediabox.bi
 * phone: +257 62 63 65 35
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Controles_Decaissement extends BaseController
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

  //Interface de la liste des controles BRB
  function controle_brb()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['institutions'] = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?);", $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC'));
    $decais = $this->count_decaissement();
    $data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger'];

    return view('App\Modules\double_commande_new\Views\Controle_BRB_View', $data);
  }

  //fonction pour affichage la liste de controle BRB
  public function controle_brb_listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";
    $crit_etap = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
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

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=43 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $order_column = array('td.TITRE_DECAISSEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION','execdet.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT','td.MONTANT_DECAISSEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.DATE_VALIDE_TITRE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4 . " " . $crit_etap;
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
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm. ' AND IS_CORRECTION=0';
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;

      $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";
      $bouton = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-arrow-up'></span></a>";
      
      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_DECAISSEMENT) : floatval($row->MONTANT_DECAISSEMENT_DEVISE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);
  
      $action = '';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " ");
      $sub_array[] = number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " ");

      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";
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

  //fonction pour exporter en EXCEL Liste de Contrôle BRB
  function exporter_excel_controle_brb($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $critere1='';
    $critere2='';
    $critere3="";
    $critere4="";

    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID, exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=43 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1.$critere2.$critere3.$critere4;

    $query_secondaire = 'CALL `getTable`("'.$requetedebase.'");';

    $getData = $this->ModelPs->datatable($query_secondaire);

      //GET CURRENT INSTITUTIONS
    $requeteInst = "SELECT DESCRIPTION_INSTITUTION 
    FROM inst_institutions
    WHERE INSTITUTION_ID = $INSTITUTION_ID";
    $requeteInst = 'CALL `getTable`("'.$requeteInst.'");';
    $getInst = $this->ModelPs->datatable($requeteInst);

      //GET CURRENT SOUS TUTEL
    $requeteSousTut = "SELECT DESCRIPTION_SOUS_TUTEL 
    FROM inst_institutions_sous_tutel
    WHERE SOUS_TUTEL_ID = $SOUS_TUTEL_ID";
    $requeteSousTut = 'CALL `getTable`("'.$requeteSousTut.'");';
    $getSousTut = $this->ModelPs->datatable($requeteSousTut);

    $DESCRIPTION_INSTITUTION = $getInst ? $getInst[0]->DESCRIPTION_INSTITUTION : '';
    $DESCRIPTION_INSTITUTION = !empty($DESCRIPTION_INSTITUTION) ? $DESCRIPTION_INSTITUTION : '-';

    $DESCRIPTION_SOUS_TUTEL = $getSousTut ? $getSousTut[0]->DESCRIPTION_SOUS_TUTEL : '';
    $DESCRIPTION_SOUS_TUTEL = !empty($DESCRIPTION_SOUS_TUTEL) ? $DESCRIPTION_SOUS_TUTEL : '-';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A2', 'LISTE DE CONTROLE BRB');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'TITRE DECAISSEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    // $sheet->setCellValue('D7', 'ACTIVITE');
    $sheet->setCellValue('D7', 'TACHE');
    // $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('E7', 'DEVISE');
    $sheet->setCellValue('F7', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G7', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('H7', 'LIQUIDATION');
    $sheet->setCellValue('I7', 'ORDONNANCEMENT');
    $sheet->setCellValue('J7', 'PAIEMENT');
    $sheet->setCellValue('K7', 'DECAISSEMENT');
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
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
      $MONTANT_DECAISSEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_DECAISSEMENT) : floatval($key->MONTANT_DECAISSEMENT_DEVISE);

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->TITRE_DECAISSEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $DESC_PAP_ACTIVITE = !empty($key->DESC_PAP_ACTIVITE) ? $key->DESC_PAP_ACTIVITE : '-';
      // $sheet->setCellValue('D' . $rows, $DESC_PAP_ACTIVITE);
      $sheet->setCellValue('D' . $rows, trim($task_items));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('F' . $rows, number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " "));
      $sheet->setCellValue('G' . $rows, number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " "));
      $sheet->setCellValue('I' . $rows, number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " "));
      $sheet->setCellValue('J' . $rows, number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " "));
      $sheet->setCellValue('K' . $rows, number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " "));
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('controle_brb.xlsx');
    return $this->response->download('controle_brb.xlsx', null)->setFileName('Liste de Contrôle BRB '.uniqid().'.xlsx');
    return redirect('double_commande_new/Controles_Decaissement/controle_brb');
  }

  //Interface de la liste des controles BESD
  function controle_besd()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['institutions'] = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?);", $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC'));
    $decais = $this->count_decaissement();$data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger']; 

    return view('App\Modules\double_commande_new\Views\Controle_BESD_View', $data);
  }

  //fonction pour affichage la liste de controle BESD
  public function controle_besd_listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";
    $crit_etap = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
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

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID,exec.DATE_DEMANDE,inst.DESCRIPTION_INSTITUTION,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,prest.NOM_PRESTATAIRE,prest.PRENOM_PRESTATAIRE,banque.NOM_BANQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN prestataire prest ON prest.PRESTATAIRE_ID=info.PRESTATAIRE_ID JOIN banque ON banque.BANQUE_ID=td.BANQUE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=44 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $order_column = array('td.TITRE_DECAISSEMENT','exec.DATE_DEMANDE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','prest.NOM_PRESTATAIRE','banque.NOM_BANQUE','ligne.CODE_NOMENCLATURE_BUDGETAIRE','inst.DESCRIPTION_INSTITUTION','td.MONTANT_PAIEMENT',1,'exec.ENG_BUDGETAIRE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.DATE_VALIDE_TITRE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.DATE_DEMANDE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR prest.NOM_PRESTATAIRE LIKE '%$var_search%' OR prest.PRENOM_PRESTATAIRE LIKE '%$var_search%' OR banque.NOM_BANQUE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%'OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%'  OR td.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4 . " " . $crit_etap;
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
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm. ' AND IS_CORRECTION=0';
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;

      $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";
      $bouton = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-arrow-up'></span></a>";
      
      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_DECAISSEMENT) : floatval($row->MONTANT_DECAISSEMENT_DEVISE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $PRESTATAIRE = $row->NOM_PRESTATAIRE . " " . $row->PRENOM_PRESTATAIRE;

      //solde par ligne
      $count_solde = "SELECT SUM(DISTINCT BUDGET_RESTANT_T1) AS solde_T1, SUM(DISTINCT BUDGET_RESTANT_T2) AS solde_T2, SUM(DISTINCT BUDGET_RESTANT_T3) AS solde_T3, SUM(DISTINCT BUDGET_RESTANT_T4) AS solde_T4 FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID=".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID;
      $count_solde = 'CALL `getTable`("'.$count_solde.'");';
      $soldeT = $this->ModelPs->getRequeteOne($count_solde);

      $SOLDE = $soldeT['solde_T1'] + $soldeT['solde_T2'] + $soldeT['solde_T3'] + $soldeT['solde_T4'];
  
      $action = '';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->DATE_DEMANDE;
      $sub_array[] = $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $PRESTATAIRE;
      $sub_array[] = $row->NOM_BANQUE;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      // $sub_array[] = $point;
      $sub_array[] = number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_BUDG), ",", " ");
      $sub_array[] = number_format($SOLDE, $this->get_precision($SOLDE), ",", " ");
      $sub_array[] = number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ");
      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";
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

  //Interface de la liste des TD non valide BRB/BESD a transmettre
  function correction_a_transmettre()
  {
    $data = $this->urichk();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB_MINFIN') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['institutions'] = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?);", $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC'));
    $decais = $this->count_decaissement();
    $data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger']; 

    return view('App\Modules\double_commande_new\Views\Controle_a_corriger_View', $data);
  }

  //fonction pour affichage la liste des TD non valide BRB/BESD a transmettre
  public function correction_a_transmettre_listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB_MINFIN') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";
    $crit_etap = "";

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
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
    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID IN (45,46) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $order_column = array('td.TITRE_DECAISSEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','ptba.DESC_TACHE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION','execdet.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT','td.MONTANT_DECAISSEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.DATE_VALIDE_TITRE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1 . " " . $critere2. " " . $critere3. " " . $critere4 . " " . $crit_etap;
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
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $et_db_comm. ' AND IS_CORRECTION=0';
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
      $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;

      $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";
      $bouton = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande_new/' . $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-arrow-up'></span></a>";
      
      $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);

      $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_DECAISSEMENT) : floatval($row->MONTANT_DECAISSEMENT_DEVISE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);
  
      $action = '';
      $sub_array = array();
      $sub_array[] = $row->TITRE_DECAISSEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ");
      $sub_array[] = number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " ");
      $sub_array[] = number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " ");
      $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ");
      $sub_array[] = number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " ");
      $sub_array[] = number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " ");

      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";
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

  //fonction pour exporter en EXCEL liste des TD non valide BRB/BESD a transmettre
  function exporter_excel_controle_a_transmettre($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $critere1='';
    $critere2='';
    $critere3="";
    $critere4="";

    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

 
    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID  JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID IN(45,46) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1.$critere2.$critere3.$critere4;

    $query_secondaire = 'CALL `getTable`("'.$requetedebase.'");';

    $getData = $this->ModelPs->datatable($query_secondaire);

    //GET CURRENT INSTITUTIONS
    $requeteInst = "SELECT DESCRIPTION_INSTITUTION 
    FROM inst_institutions
    WHERE INSTITUTION_ID = $INSTITUTION_ID";
    $requeteInst = 'CALL `getTable`("'.$requeteInst.'");';
    $getInst = $this->ModelPs->datatable($requeteInst);

      //GET CURRENT SOUS TUTEL
    $requeteSousTut = "SELECT DESCRIPTION_SOUS_TUTEL 
    FROM inst_institutions_sous_tutel
    WHERE SOUS_TUTEL_ID = $SOUS_TUTEL_ID";
    $requeteSousTut = 'CALL `getTable`("'.$requeteSousTut.'");';
    $getSousTut = $this->ModelPs->datatable($requeteSousTut);

    $DESCRIPTION_INSTITUTION = $getInst ? $getInst[0]->DESCRIPTION_INSTITUTION : '';
    $DESCRIPTION_INSTITUTION = !empty($DESCRIPTION_INSTITUTION) ? $DESCRIPTION_INSTITUTION : '-';

    $DESCRIPTION_SOUS_TUTEL = $getSousTut ? $getSousTut[0]->DESCRIPTION_SOUS_TUTEL : '';
    $DESCRIPTION_SOUS_TUTEL = !empty($DESCRIPTION_SOUS_TUTEL) ? $DESCRIPTION_SOUS_TUTEL : '-';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A2', 'LISTE DES CORRECTIONS A TRANSMETTRE');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'TITRE DECAISSEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    // $sheet->setCellValue('D7', 'ACTIVITE');
    $sheet->setCellValue('D7', 'TACHE');
    // $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('E7', 'DEVISE');
    $sheet->setCellValue('F7', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G7', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('H7', 'LIQUIDATION');
    $sheet->setCellValue('I7', 'ORDONNANCEMENT');
    $sheet->setCellValue('J7', 'PAIEMENT');
    $sheet->setCellValue('K7', 'DECAISSEMENT');
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
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
      $MONTANT_DECAISSEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_DECAISSEMENT) : floatval($key->MONTANT_DECAISSEMENT_DEVISE);

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->TITRE_DECAISSEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $DESC_PAP_ACTIVITE = !empty($key->DESC_PAP_ACTIVITE) ? $key->DESC_PAP_ACTIVITE : '-';
      // $sheet->setCellValue('D' . $rows, $DESC_PAP_ACTIVITE);
      $sheet->setCellValue('D' . $rows, trim($task_items));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('F' . $rows, number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " "));
      $sheet->setCellValue('G' . $rows, number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " "));
      $sheet->setCellValue('I' . $rows, number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " "));
      $sheet->setCellValue('J' . $rows, number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " "));
      $sheet->setCellValue('K' . $rows, number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " "));
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('correction_a_transmettre.xlsx');
    return $this->response->download('correction_a_transmettre.xlsx', null)->setFileName('Liste des corrections à transmettre '.uniqid().'.xlsx');
    return redirect('double_commande_new/Controles_Decaissement/correction_a_transmettre');
  }

    
  // Exporter la liste de controle BESD
  function exporter_Excel_BESD($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";
    $crit_etap = "";
    $nom_institution='';
    $nom_sous_titre="";
    $callpsreq = "CALL getRequete(?,?,?,?);";
    if($INSTITUTION_ID>0)
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;

      if($SOUS_TUTEL_ID>0)
      {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.MOTIF_PAIEMENT,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID,exec.DATE_DEMANDE,inst.DESCRIPTION_INSTITUTION,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,prest.NOM_PRESTATAIRE,prest.PRENOM_PRESTATAIRE,banque.NOM_BANQUE,td.COMPTE_CREDIT,ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID,det_ret.MONTANT_RETENU FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID  JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID  JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN prestataire prest ON prest.PRESTATAIRE_ID=info.PRESTATAIRE_ID JOIN banque ON banque.BANQUE_ID=td.BANQUE_ID LEFT JOIN exec_budget_tache_detail_retenu_prise_charge det_ret ON det_ret.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=44 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$critere1.$critere2.$critere3.$critere4." ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC";

    $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.titre_decaissement_th')).'');

    $sheet->setCellValue('B1', ''.strtoupper(str_replace('&nbsp;', ' ', lang('messages_lang.Bon_engagement'))).'');
    $sheet->setCellValue('C1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.labelle_ministre')).'');

    $sheet->setCellValue('D1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.col_imputation')).'');
    $sheet->setCellValue('E1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.titre_libelle')).' '.strtoupper(str_replace('&nbsp;', ' ', lang('messages_lang.labelle_ligne_budgtaire'))).'');
    $sheet->setCellValue('F1', 'LIBELLE ACTIVITE');
    $sheet->setCellValue('G1', 'TACHE');
    $sheet->setCellValue('H1', 'MOTIF DECAISSEMENT');
    $sheet->setCellValue('I1', 'CREDIT ACCORDE LIGNE');
    $sheet->setCellValue('J1', 'CREDIT ACCORDE TACHE');
    $sheet->setCellValue('K1', 'CREDIT RESTANT LIGNE');
    $sheet->setCellValue('L1', 'CREDIT RESTANT TACHE');

    $sheet->setCellValue('M1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.montant_engage_th')).'');
    $sheet->setCellValue('N1', 'RETENU');
    $sheet->setCellValue('O1', 'NET');
    $sheet->setCellValue('P1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.beneficaire_th')).'');

    $sheet->setCellValue('Q1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.banque_th')).'');
    $sheet->setCellValue('R1', 'NUMERO COMPTE');
    $sheet->setCellValue('S1', 'DATE DE TRANSMISSION');
    $sheet->setCellValue('T1', 'DATE RECEPTION');
    
    $rows = 2;
    $i=1;
    foreach ($getData as $key)
    {
      $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);

      $PRESTATAIRE = $key->NOM_PRESTATAIRE . " " . $key->PRENOM_PRESTATAIRE;

      //solde par ligne
      $count_solde = "SELECT SUM(DISTINCT BUDGET_RESTANT_T1) AS solde_T1, SUM(DISTINCT BUDGET_RESTANT_T2) AS solde_T2, SUM(DISTINCT BUDGET_RESTANT_T3) AS solde_T3, SUM(DISTINCT BUDGET_RESTANT_T4) AS solde_T4,SUM(DISTINCT BUDGET_T1) AS budget_T1,SUM(DISTINCT BUDGET_T2) AS budget_T2,SUM(DISTINCT BUDGET_T3) AS budget_T3,SUM(DISTINCT BUDGET_T4) AS budget_T4 FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID=".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID;
      $count_solde = 'CALL `getTable`("'.$count_solde.'");';
      $soldeT = $this->ModelPs->getRequeteOne($count_solde);

      $CREDIT_LIGNE = $soldeT['budget_T1'] + $soldeT['budget_T2'] + $soldeT['budget_T3'] + $soldeT['budget_T4'];
      $SOLDE = $soldeT['solde_T1'] + $soldeT['solde_T2'] + $soldeT['solde_T3'] + $soldeT['solde_T4'];

        //get les taches
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL getTable("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items='';
      $credit_accorde_tache='';
      $credit_restant_tache='';
      foreach ($tasks as $task) {
        $task_items .= "-".$task->DESC_TACHE . "\n";
        $montantvote = 0;
        $montantvote = $task->BUDGET_T1+$task->BUDGET_T2+$task->BUDGET_T3+$task->BUDGET_T4;
        $montantRestant = $task->BUDGET_RESTANT_T1+$task->BUDGET_RESTANT_T2+$task->BUDGET_RESTANT_T3+$task->BUDGET_RESTANT_T4;
        $credit_accorde_tache.="".number_format($montantvote ,$this->get_precision($montantvote), ",", " "). "\n";
        $credit_restant_tache.="".number_format($montantRestant,$this->get_precision($montantRestant), ",", " "). "\n";
      }

      // get les activites 
       $get_activities = "SELECT  DISTINCT pap_activites.PAP_ACTIVITE_ID, DESC_PAP_ACTIVITE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_activit = 'CALL getTable("'.$get_activities.'");';
      $activities = $this->ModelPs->getRequete($get_activit);
      $activities_items='';
      foreach ($activities as $val) {
        $activities_items .= "-".$val->DESC_PAP_ACTIVITE . "\n";
      }

      //DATE DE TRANSMISSION ET RECEPTION
      $get_date="SELECT EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID,DATE_RECEPTION,DATE_TRANSMISSION FROM execution_budgetaire_tache_detail_histo WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID." AND ETAPE_DOUBLE_COMMANDE_ID=28 ORDER BY EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC LIMIT 1";

      $get_date = 'CALL getTable("'.$get_date.'");';
      $get_dates = $this->ModelPs->getRequeteOne($get_date);

      $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('D' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      $sheet->setCellValue('E' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
      $sheet->setCellValue('F' . $rows, trim($activities_items));
      $sheet->getStyle('F' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('G' . $rows, trim($task_items));
      $sheet->getStyle('G' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('H' . $rows, $key->MOTIF_PAIEMENT);
      $sheet->setCellValue('I' . $rows, number_format($CREDIT_LIGNE, $this->get_precision($CREDIT_LIGNE), ",", " "));
      $sheet->setCellValue('J' . $rows, trim($credit_accorde_tache));
      $sheet->getStyle('J' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('K' . $rows, number_format($SOLDE, $this->get_precision($SOLDE), ",", " "));
      $sheet->setCellValue('L' . $rows, trim($credit_restant_tache));
      $sheet->getStyle('L' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('M' . $rows, number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " "));
      // RETENU
      $sheet->setCellValue('N' . $rows, number_format($key->MONTANT_RETENU, $this->get_precision($key->MONTANT_RETENU), ",", " "));
      // NET
      $sheet->setCellValue('O' . $rows, number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " "));
      $sheet->setCellValue('P' . $rows, $PRESTATAIRE);
      $sheet->setCellValue('Q' . $rows, $key->NOM_BANQUE);
      $sheet->setCellValue('R' . $rows, $key->COMPTE_CREDIT);
      $sheet->setCellValue('S' . $rows, date('d-m-Y',strtotime($get_dates['DATE_TRANSMISSION'])));
      $sheet->setCellValue('T' . $rows, date('d-m-Y',strtotime($get_dates['DATE_RECEPTION'])));
      $rows++;
      $i++;
    }

    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(50);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(30);
    $sheet->getColumnDimension('G')->setWidth(30);
    $sheet->getColumnDimension('H')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(30);
    $sheet->getColumnDimension('J')->setWidth(30);
    $sheet->getColumnDimension('K')->setWidth(30);
    $sheet->getColumnDimension('L')->setWidth(30);
    $sheet->getColumnDimension('M')->setWidth(30);
    $sheet->getColumnDimension('N')->setWidth(30);
    $sheet->getColumnDimension('O')->setWidth(30);
    $sheet->getColumnDimension('P')->setWidth(30);
    $sheet->getColumnDimension('Q')->setWidth(30);
    $sheet->getColumnDimension('R')->setWidth(30);
    $sheet->getColumnDimension('S')->setWidth(30);
    $sheet->getColumnDimension('T')->setWidth(30);

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('controle_besd.xlsx');
    return $this->response->download('controle_besd.xlsx', null)->setFileName('controle_besd'.$code.'.xlsx');

    return redirect('double_commande_new/Controles_Decaissement/controle_besd');
  }
  
  //fonction pour afficher l'interface de controle pour brb
  function interface_brb($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
  {
    $data = $this->urichk();
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['hashed_EBTD_ID'] = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //get ETAPE_DOUBLE_COMMANDE_ID et IS_MARCHE_PUBLIQUE
    $request = $this->getBindParms('ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.MARCHE_PUBLIQUE', 'execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . '"', 'ebtd.ETAPE_DOUBLE_COMMANDE_ID ASC');
    $request = str_replace('\"', '"', $request);
    $result = $this->ModelPs->getRequeteOne($psgetrequete, $request);
    $ETAPE_DOUBLE_COMMANDE_ID = $result['ETAPE_DOUBLE_COMMANDE_ID'];
    $data['IS_MARCHE'] =  $result['MARCHE_PUBLIQUE'];

    //get les motifs de retour
    $requestMotif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF', 'budgetaire_type_analyse_motif', 'MOUVEMENT_DEPENSE_ID=6', 'DESC_TYPE_ANALYSE_MOTIF ASC');
    $requestMotif = str_replace('\"', '"', $requestMotif);
    $data['motif_retour'] = $this->ModelPs->getRequete($psgetrequete, $requestMotif);

    //récuperer les operations de validation
    $benef = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION IN (1,2)','DESCRIPTION ASC');
    $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $benef);

    //bloquer l'action si l'etape est incorrect
    if($ETAPE_DOUBLE_COMMANDE_ID != 43)
    {
      return redirect('Login_Ptba/homepage');
    }

    $detail = $this->detail_new($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
    $data['get_info']=$detail['get_info'];
    $data['montantvote']=$detail['montantvote'];
    $data['get_infoEBET']=$detail['get_infoEBET'];
    return view('App\Modules\double_commande_new\Views\Controle_Interface_BRB_View', $data);
  }

  //Les nouveaux motifs
  function save_newMotif()
  {
    $session  = \Config\Services::session();
    $DESCRIPTION_MOTIF = $this->request->getPost('DESCRIPTION_MOTIF');
    $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
    $MOUVEMENT_DEPENSE_ID=6;

    $table="budgetaire_type_analyse_motif";
    $columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
    $datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',{$MARCHE_PUBLIQUE}";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);

    $callpsreq = "CALL getRequete(?,?,?,?);";

    //récuperer les motifs
    $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','MOUVEMENT_DEPENSE_ID=6 AND IS_MARCHE='.$MARCHE_PUBLIQUE,'DESC_TYPE_ANALYSE_MOTIF ASC');
    $motif = $this->ModelPs->getRequete($callpsreq, $bind_motif);

    $html='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';

    if(!empty($motif))
    {
      foreach($motif as $key)
      { 
        $html.= "<option value='".$key->TYPE_ANALYSE_MOTIF_ID."'>".$key->DESC_TYPE_ANALYSE_MOTIF."</option>";
      }
    }
    $output = array('status' => TRUE ,'motifs' => $html);
    return $this->response->setJSON($output);
  }

  //save controle brb
  function save_brb_old()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $TYPE_DECISION = $this->request->getPost('TYPE_DECISION');
    $hashed_EBTD_ID = $this->request->getPost('hashed_EBTD_ID');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');
    $MOTIF_RETOUR_AUTRE = $this->request->getPost('MOTIF_RETOUR_AUTRE');
    $IS_MARCHE = $this->request->getPost('IS_MARCHE');

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //GET etape suivante

    $id_etape = 43;
    $IS_CORRECTION = '';
    //si valide
    if($TYPE_DECISION == 1){
      $IS_CORRECTION = 0;
    }
    // non valide
    else{
      $IS_CORRECTION = 1;
    }
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '" AND IS_CORRECTION="'.$IS_CORRECTION.'"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    //get EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID
    $request = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_titre_decaissement', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $hashed_EBTD_ID . '"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    $request = str_replace('\"', '"', $request);
    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->ModelPs->getRequeteOne($psgetrequete, $request)['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];

    $MOUVEMENT_DEPENSE_ID = 6;

    //cas autre motif
    if($TYPE_ANALYSE_MOTIF_ID == -1)
    {
      $colums="MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";

      $datatoinsert="".$MOUVEMENT_DEPENSE_ID.",'".$MOTIF_RETOUR_AUTRE."',".$IS_MARCHE;

      $table='budgetaire_type_analyse_motif';
      $TYPE_ANALYSE_MOTIF_ID=$this->save_all_table($table,$colums,$datatoinsert);
    }

    //en cas de retour
    if($TYPE_DECISION == 0){
      //save motif de retour 
      $columsMotif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
      $datatoinsertMotif="".$TYPE_ANALYSE_MOTIF_ID.",".$next_etape_data.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $tableMotif='execution_budgetaire_histo_operation_verification_motif';
      $this->save_all_table($tableMotif,$columsMotif,$datatoinsertMotif);

      //reset le STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID
      $ID_ORIGINE_DESTINATION = 3;
      $conditions_3 = 'bord.ID_ORIGINE_DESTINATION='.$ID_ORIGINE_DESTINATION.' AND bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $datatomodifie_3 = 'bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord.BORDEREAU_TRANSMISSION_ID',$datatomodifie_3, $conditions_3);
    }

    //UPDATE L'ETAPE
    $table = 'execution_budgetaire_titre_decaissement';
    $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data.'';
    $this->update_all_table($table, $datatomodifie, $conditions);

    //insertion dans l'historique
    $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID";
    $data_histo = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . ',' . $id_etape . ',' . $user_id;
    $tablehist="execution_budgetaire_tache_detail_histo";
    $this->save_all_table($tablehist,$column_histo, $data_histo);

    return redirect('double_commande_new/Controles_Decaissement/controle_brb');
  }

  function save_brb()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $TYPE_DECISION = $this->request->getPost('TYPE_DECISION');
    $hashed_EBTD_ID = $this->request->getPost('hashed_EBTD_ID');
    $IS_MARCHE = $this->request->getPost('IS_MARCHE');
    $OPERATION = $this->request->getPost('OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');


    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //GET etape suivante

    $id_etape = 43;
    $IS_CORRECTION = '';
    //si valide
    if($OPERATION == 2){
      $IS_CORRECTION = 0;
    }
    // non valide
    else{
      $IS_CORRECTION = 1;
    }
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '" AND IS_CORRECTION="'.$IS_CORRECTION.'"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    //get EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID
    $request = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_titre_decaissement', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $hashed_EBTD_ID . '"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    $request = str_replace('\"', '"', $request);
    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->ModelPs->getRequeteOne($psgetrequete, $request)['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];

    //en cas de retour
    if($OPERATION == 1){
      //save motif de retour 
      //Enregistrement dans historique vérification des motifs
      foreach($TYPE_ANALYSE_MOTIF_ID as $value)
      {
        $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
        $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
        $datatoinsert_histo_motif = "".$value.",".$id_etape.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
        $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
      }

      //reset le STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID
      //$ID_ORIGINE_DESTINATION = 3;
      //$conditions_3 = 'bord.ID_ORIGINE_DESTINATION='.$ID_ORIGINE_DESTINATION.' AND bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      //$datatomodifie_3 = 'bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      //$this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord.BORDEREAU_TRANSMISSION_ID',$datatomodifie_3, $conditions_3);
    }
    //UPDATE L'ETAPE
    $table = 'execution_budgetaire_titre_decaissement';
    $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data.'';
    $this->update_all_table($table, $datatomodifie, $conditions);

    //insertion dans l'historique
    $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,OBSERVATION,DATE_TRANSMISSION";
    $data_histo = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . ',' . $id_etape . ',' . $user_id . ',"' . $COMMENTAIRE . '",'.$DATE_TRANSMISSION;
    $tablehist="execution_budgetaire_tache_detail_histo";
    $this->save_all_table($tablehist,$column_histo, $data_histo);

    return redirect('double_commande_new/Controles_Decaissement/controle_brb');
  }
  
  //fonction pour afficher l'interface de controle pour besd
  function interface_besd($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
  {
    $data = $this->urichk();
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data['hashed_EBTD_ID'] = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //get ETAPE_DOUBLE_COMMANDE_ID et IS_MARCHE_PUBLIQUE
    $request = $this->getBindParms('ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.MARCHE_PUBLIQUE', 'execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . '"', 'ebtd.ETAPE_DOUBLE_COMMANDE_ID ASC');
    $request = str_replace('\"', '"', $request);
    $result = $this->ModelPs->getRequeteOne($psgetrequete, $request);
    $ETAPE_DOUBLE_COMMANDE_ID = $result['ETAPE_DOUBLE_COMMANDE_ID'];
    $data['IS_MARCHE'] =  $result['MARCHE_PUBLIQUE'];

    //get les motifs de retour
    $requestMotif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF', 'budgetaire_type_analyse_motif', 'MOUVEMENT_DEPENSE_ID=6', 'DESC_TYPE_ANALYSE_MOTIF ASC');
    $requestMotif = str_replace('\"', '"', $requestMotif);
    $data['motif_retour'] = $this->ModelPs->getRequete($psgetrequete, $requestMotif);

    //récuperer les operations de validation
    $benef = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION IN (1,2)','DESCRIPTION ASC');
    $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $benef);

    //bloquer l'action si l'etape est incorrect
    if($ETAPE_DOUBLE_COMMANDE_ID != 44)
    {
      return redirect('Login_Ptba/homepage');
    }

    $detail = $this->detail_new($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
    $data['get_info']=$detail['get_info'];
    $data['montantvote']=$detail['montantvote'];
    $data['get_infoEBET']=$detail['get_infoEBET'];
    return view('App\Modules\double_commande_new\Views\Controle_Interface_BESD_View', $data);
  }

  //save controle besd
  function save_besd_old()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $TYPE_DECISION = $this->request->getPost('TYPE_DECISION');
    $hashed_EBTD_ID = $this->request->getPost('hashed_EBTD_ID');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');
    $MOTIF_RETOUR_AUTRE = $this->request->getPost('MOTIF_RETOUR_AUTRE');
    $IS_MARCHE = $this->request->getPost('IS_MARCHE');

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //GET etape suivante

    $id_etape = 44;
    $IS_CORRECTION = '';
    //si valide
    if($TYPE_DECISION == 1){
      $IS_CORRECTION = 0;
    }
    // non valide
    else{
      $IS_CORRECTION = 1;
    }
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '" AND IS_CORRECTION="'.$IS_CORRECTION.'"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    //get EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID
    $request = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_titre_decaissement', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $hashed_EBTD_ID . '"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    $request = str_replace('\"', '"', $request);
    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->ModelPs->getRequeteOne($psgetrequete, $request)['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];

    $MOUVEMENT_DEPENSE_ID = 6;

    //cas autre motif
    if($TYPE_ANALYSE_MOTIF_ID == -1)
    {
      $colums="MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
      $datatoinsert="".$MOUVEMENT_DEPENSE_ID.",'".$MOTIF_RETOUR_AUTRE."',".$IS_MARCHE;
      $table='budgetaire_type_analyse_motif';
      $TYPE_ANALYSE_MOTIF_ID=$this->save_all_table($table,$colums,$datatoinsert);
        
    }

    //en cas de retour
    if($TYPE_DECISION == 0){
      //save motif de retour 
      $columsMotif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
      $datatoinsertMotif="".$TYPE_ANALYSE_MOTIF_ID.",".$next_etape_data.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $tableMotif='execution_budgetaire_histo_operation_verification_motif';
      $this->save_all_table($tableMotif,$columsMotif,$datatoinsertMotif);

      //reset le STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID
      $ID_ORIGINE_DESTINATION = 3;
      $conditions_3 = 'bord.ID_ORIGINE_DESTINATION='.$ID_ORIGINE_DESTINATION.' AND bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $datatomodifie_3 = 'bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord.BORDEREAU_TRANSMISSION_ID',$datatomodifie_3, $conditions_3);
    }

    //UPDATE L'ETAPE
    $table = 'execution_budgetaire_titre_decaissement';
    $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data.'';
    $this->update_all_table($table, $datatomodifie, $conditions);

    //insertion dans l'historique
    $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID";
    $data_histo = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . ',' . $id_etape . ',' . $user_id;
    $tablehist="execution_budgetaire_tache_detail_histo";
    $this->save_all_table($tablehist,$column_histo, $data_histo);


    return redirect('double_commande_new/Controles_Decaissement/controle_besd');
  }

  function save_besd()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $TYPE_DECISION = $this->request->getPost('TYPE_DECISION');
    $hashed_EBTD_ID = $this->request->getPost('hashed_EBTD_ID');
    $IS_MARCHE = $this->request->getPost('IS_MARCHE');
    $OPERATION = $this->request->getPost('OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    //GET etape suivante

    $id_etape = 44;
    $IS_CORRECTION = '';
    //si valide
    if($OPERATION == 2){
      $IS_CORRECTION = 0;
    }
    // non valide
    else{
      $IS_CORRECTION = 1;
    }
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '" AND IS_CORRECTION="'.$IS_CORRECTION.'"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    //get EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID
    $request = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_titre_decaissement', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $hashed_EBTD_ID . '"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    $request = str_replace('\"', '"', $request);
    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->ModelPs->getRequeteOne($psgetrequete, $request)['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];

    //en cas de retour
    if($OPERATION == 1)
    {
      //save motif de retour 
      //Enregistrement dans historique vérification des motifs
      foreach($TYPE_ANALYSE_MOTIF_ID as $value)
      {
        $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
        $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
        $datatoinsert_histo_motif = "".$value.",".$id_etape.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
        $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
      }

      //reset le STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID
      //$ID_ORIGINE_DESTINATION = 3;
      //$conditions_3 = 'bord.ID_ORIGINE_DESTINATION='.$ID_ORIGINE_DESTINATION.' AND bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      //$datatomodifie_3 = 'bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      //$this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord.BORDEREAU_TRANSMISSION_ID',$datatomodifie_3, $conditions_3);
    }
    //UPDATE L'ETAPE
    $table = 'execution_budgetaire_titre_decaissement';
    $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data.'';
    $this->update_all_table($table, $datatomodifie, $conditions);

    //insertion dans l'historique
    $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,OBSERVATION,DATE_TRANSMISSION";
    $data_histo = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . ',' . $id_etape . ',' . $user_id . ',"' . $COMMENTAIRE . '",'.$DATE_TRANSMISSION;
    $tablehist="execution_budgetaire_tache_detail_histo";
    $this->save_all_table($tablehist,$column_histo, $data_histo);

    return redirect('double_commande_new/Controles_Decaissement/controle_besd');
  }

  function detail_task()
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

    $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE','task.MONTANT_ENG_JURIDIQUE','task.MONTANT_LIQUIDATION','task.MONTANT_ORDONNANCEMENT','task.MONTANT_PAIEMENT','task.MONTANT_DECAISSEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE LIKE '%$var_search%' OR task.MONTANT_ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION LIKE '%$var_search%' OR task.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR task.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR task.MONTANT_PAIEMENT LIKE '%$var_search%' OR task.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR task.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR task.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%')"):'';

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
  // cette fonction permet de recuperer le nombre de chiffres apres la virgule d un  nombre passé en paramettre
  function get_precision($value=0)
  {
    $parts = explode('.', strval($value));
    return isset($parts[1]) ? strlen($parts[1]) : 0;
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
  
  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }
}