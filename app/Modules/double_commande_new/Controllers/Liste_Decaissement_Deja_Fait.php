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
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
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
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger'];

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

    if(!empty($INSTITUTION_ID)) 
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

    $group = "";
    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $order_column = array('td.TITRE_DECAISSEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION','execdet.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT','td.MONTANT_DECAISSEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR execdet.MONTANT_ORDONNANCEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT_DEVISE LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT LIKE '%$var_search%' OR td.MONTANT_DECAISSEMENT_DEVISE LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')") : '';

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
     
      $number = $row->NUMERO_BON_ENGAGEMENT;
      $bouton = "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
      
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

      $action1 = '<div class="dropdown" style="color:#fff;">
        "' . $bouton . '"';
      $action2 = "<a class='btn btn-primary btn-sm' title='' href='" . base_url('double_commande/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-plus'></span></a>";
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
       

  // Exporter l aliste excel TD valides
  function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0)
  {
    // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $critere1 = "";
    $critere2 = "";
    $crit_etap = "";
    $nom_institution='';
    $nom_sous_titre="";
    $callpsreq = "CALL getRequete(?,?,?,?);";
    if($INSTITUTION_ID>0)
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      $inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
      $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

      $nom_institution=$instt['DESCRIPTION_INSTITUTION'];
      if($SOUS_TUTEL_ID>0)
      {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
        $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
        $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

        $nom_sous_titre=" -".$instt['DESCRIPTION_SOUS_TUTEL'];
      }
    }


    $requetedebase = "SELECT DISTINCT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID,exec.COMMENTAIRE FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1." ".$critere2." ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC";

    $getData = $this->ModelPs->datatable("CALL getTable('" . $requetedebase . "')"); 
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('C1', ''.$nom_institution.$nom_sous_titre.'');
    $sheet->setCellValue('C2', ''.str_replace('&nbsp;', ' ', lang('messages_lang.header_titre_decaisement')).'');

    $sheet->setCellValue('A4', '#');
    $sheet->setCellValue('B4', ''.lang('messages_lang.titre_decaissement').'');
    $sheet->setCellValue('C4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.imputation_decaissement')).'');
    $sheet->setCellValue('D4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.th_tache')).'');
    $sheet->setCellValue('E4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.table_objet')).'');
    $sheet->setCellValue('F4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.th_devise')).'');
    $sheet->setCellValue('G4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.labelle_eng_budget')).'');

    $sheet->setCellValue('H4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.labelle_eng_jud')).'');

    $sheet->setCellValue('I4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.liquidation_decaissement')).'');

    $sheet->setCellValue('J4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.labelle_ordonan')).'');

    $sheet->setCellValue('K4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.labelle_paiement')).'');

    $sheet->setCellValue('L4', ''.str_replace('&nbsp;', ' ', lang('messages_lang.decaissement_decaissement')).'');

    $rows = 5;
    $i=1;
    foreach ($getData as $key)
    {
      
      //Les tâches de chaque exécution
      $count_task = "SELECT ptba.DESC_TACHE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID." ORDER BY ptba.DESC_TACHE ASC" ;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $desc_task = $this->ModelPs->getRequete($count_task);

      $res = '';
      $count=0;
      foreach ($desc_task as $result) {
          $count++;
          $res .= $count.".  ".$result->DESC_TACHE . "\n";
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
      $sheet->setCellValue('D' . $rows, trim($res));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('F' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('G' . $rows, number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " "));
      $sheet->setCellValue('I' . $rows, number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " "));
      $sheet->setCellValue('J' . $rows, number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " "));
      $sheet->setCellValue('K' . $rows, number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " "));
      $sheet->setCellValue('L' . $rows, number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " "));

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

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Decaiss'.$code.'.xlsx');

    return redirect('double_commande_new/Liste_Decaissement_Deja_Fait');
  }

  //Exportation des exécutions dans un pdf 
  function generatePdf($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $critere1 = "";
    $critere2 = "";
    $crit_etap = "";
    $nom_institution='';
    $nom_sous_titre="";
    $callpsreq = "CALL getRequete(?,?,?,?);";
    if ($INSTITUTION_ID>0)
    {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      $inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
      $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

      $nom_institution=$instt['DESCRIPTION_INSTITUTION'];
      if($SOUS_TUTEL_ID>0)
      {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
        $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
        $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);
        $nom_sous_titre=" -".$instt['DESCRIPTION_SOUS_TUTEL'];
      }
    }

    $requetedebase = "SELECT DISTINCT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE,execdet.MONTANT_ORDONNANCEMENT,execdet.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,inst.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE, dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID,exec.COMMENTAIRE FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID  JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=execdet.EXECUTION_BUDGETAIRE_DETAIL_ID  JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1." ".$critere2." ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC";

    $getData = $this->ModelPs->datatable("CALL getTable('" . $requetedebase . "')"); 

    $dompdf = new Dompdf();
    $html="<html><body>";     

    $html.="<center><b>".lang('messages_lang.header_titre_decaisement')."</b></center><br><br>";
    $html.="
     <p style='font-size:9px;'>".$nom_institution."</p>
     <p style='font-size:9px;'>".$nom_sous_titre."</p>

    <table cellspacing='0'>
    <tr class='text-uppercase'> 
    <th style='font-size:8px; border: 1px solid black'>#</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.titre_decaissement'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.imputation_decaissement'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.th_tache'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.label_obje'))."</th>
     <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.th_devise'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.labelle_eng_budget'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.labelle_eng_jud'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.liquidation_decaissement'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.labelle_ordonan'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.labelle_paiement'))."</th>
    <th style='font-size:8px; border: 1px solid black'>".str_replace('&nbsp;', ' ', lang('messages_lang.decaissement_decaissement'))."</th>

    </tr>";

    $i=1;
    foreach ($getData as $key)
    {
      //Les tâches de chaque exécution
      $count_task = "SELECT ptba.DESC_TACHE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID." ORDER BY ptba.DESC_TACHE ASC" ;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $desc_task = $this->ModelPs->getRequete($count_task);

      $res = '';
      $count=0;
      foreach ($desc_task as $result) {
          $count++;
          $res .= "<li style='list-style-type: none;'>".$count.".  ".$result->DESC_TACHE . "</li>";
      }
      

      $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
      $MONTANT_JURIDIQUE = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_JURIDIQUE) : floatval($key->ENG_JURIDIQUE_DEVISE);
      $MONTANT_LIQUIDATION = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_LIQUIDATION) : floatval($key->MONTANT_LIQUIDATION_DEVISE);
      $MONTANT_ORDONNANCEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_ORDONNANCEMENT) : floatval($key->MONTANT_ORDONNANCEMENT_DEVISE);
      $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);
      $MONTANT_DECAISSEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_DECAISSEMENT) : floatval($key->MONTANT_DECAISSEMENT_DEVISE);

      $html.="
        <tr>
        <td style='font-size:8px; border: 1px solid black'>".$i."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->TITRE_DECAISSEMENT."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->CODE_NOMENCLATURE_BUDGETAIRE."</td>
        <td style='font-size:8px; border: 1px solid black'>".$res."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->COMMENTAIRE."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->DESC_DEVISE_TYPE."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_DECAISSEMENT, $this->get_precision($MONTANT_DECAISSEMENT), ",", " ")."</td>
        </tr>

        ";
        $i++;
    }

    $html.="</table></body></html>";

        // Charger le contenu HTML
    $dompdf->loadHtml($html);
      // Définir la taille et l'orientation du papier
    $dompdf->setPaper('A4', 'portrait');

      // Rendre le HTML en PDF
    $dompdf->render();
    $name_file = 'Decaiss'.uniqid().'.pdf';
   
    $PATH_PIECE_JUSTIFICATIVE = 'uploads/double_commande/'.$name_file;

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Decaiss'.uniqid().'.pdf"');

    echo $dompdf->output();

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

   // cette fonction permet de recuperer le nombre de chiffres apres la virgule d un  nombre passé en paramettre
    function get_precision($value=0){
      
      $parts = explode('.', strval($value));
      return isset($parts[1]) ? strlen($parts[1]) : 0;
     
    }

    //Retourne les sous titres
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
