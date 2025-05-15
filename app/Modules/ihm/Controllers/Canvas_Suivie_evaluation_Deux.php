<?php
/*
*MUNEZERO SONIA
*Titre: listes Canvas de Suivie evaluation feuilles deux
*Numero de telephone: (+257) 65165772
*Email: sonia@mediabox.bi
*Date: 25 Avril,2024
*/
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use Config\Database;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 4000);
ini_set('memory_limit','2048M'); 


class Canvas_Suivie_evaluation_Deux extends BaseController
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  // affiche le view du liste
  function canvas_liste()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $date_select=date('m');
    if ($date_select=='01' OR $date_select=='02' OR $date_select=='03') {
      $date_ch='';
      $date_ch1='';
      $date_ch2='checked';
      $date_ch3='';
    }else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06') {
      $date_ch='';
      $date_ch1='';
      $date_ch2='';
      $date_ch3='checked';
    }else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' ) {
      $date_ch='checked';
      $date_ch1='';
      $date_ch2='';
      $date_ch3='';
    }else{
      $date_ch='';
      $date_ch1='checked';
      $date_ch2='';
      $date_ch3=''; 
    }

    $data['ch']=$date_ch;       
    $data['ch1']=$date_ch1;
    $data['ch2']=$date_ch2;
    $data['ch3']=$date_ch3;

    $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY DESCRIPTION_INSTITUTION ASC';
    $getInst = "CALL `getTable`('" . $getInst . "');";
    $data['institutions'] = $this->ModelPs->getRequete($getInst);

    //L'id de l'année budgétaire actuelle
    $data['ann_actuel_id'] = $this->get_annee_budgetaire();

    //Selection de l'année budgétaire
    $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID<=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
    $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');

    $data['titre']=''.lang('messages_lang.caneva2').'';
    return view('App\Modules\ihm\Views\Canvas_Deux_Liste_View',$data);
  }

  public function listing_Canvas_deux($value = 0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $ACTION_ID=$this->request->getPost('ACTION_ID');
    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');


    $cond_trim=' ';
    if ($IS_PRIVATE==1)
    {
      $cond_trim=' AND racc.TRIMESTRE_ID=1' ;
    }
    else if ($IS_PRIVATE==2)
    { 
      $cond_trim=' AND racc.TRIMESTRE_ID=2' ;
    }
    else if ($IS_PRIVATE==3)
    {
      $cond_trim=' AND racc.TRIMESTRE_ID=3' ;
    }
    else if ($IS_PRIVATE==4)
    {
      $cond_trim=' AND racc.TRIMESTRE_ID=4' ;
    }
    else
    {
      $cond_trim=' ' ;
    }

    if (!empty($INSTITUTION_ID))
    {
      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID='.$INSTITUTION_ID.' ORDER BY DESCRIPTION_INSTITUTION ASC';
      $getInst = "CALL `getTable`('" . $getInst . "');";
      $code = $this->ModelPs->getRequeteOne($getInst);
      $cond_trim.=' AND ptba.INSTITUTION_ID="'.$code['INSTITUTION_ID'].'"' ;
    }

    if (!empty($PROGRAMME_ID))
    {
      $getprogram  = 'SELECT PROGRAMME_ID, INSTITUTION_ID, CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE PROGRAMME_ID='.$PROGRAMME_ID.' ORDER BY INTITULE_PROGRAMME  ASC';
      $getprogram = "CALL `getTable`('" . $getprogram . "');";
      $code = $this->ModelPs->getRequeteOne($getprogram);
      $cond_trim.=' AND ptba.PROGRAMME_ID="'.$code['PROGRAMME_ID'].'"' ;
    }

    if (!empty($ACTION_ID))
    {
      $getprogram  = 'SELECT ACTION_ID, PROGRAMME_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE ACTION_ID='.$ACTION_ID.' ORDER BY LIBELLE_ACTION  ASC';
      $getprogram = "CALL `getTable`('" . $getprogram . "');";
      $code = $this->ModelPs->getRequeteOne($getprogram);
      $cond_trim.=' AND ptba.ACTION_ID="'.$code['ACTION_ID'].'"' ;
    }

    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $cond_trim.=' AND racc.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critere = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column = array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','inst.CODE_INSTITUTION','prog.CODE_PROGRAMME','act.CODE_ACTION','ACTIVITES','RESULTATS_ATTENDUS','PROGRAMMATION_FINANCIERE_BIF','QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE','ptba.UNITE');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.PTBA_ID   DESC';

    $search=!empty($_POST['search']['value']) ? (' AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR inst.CODE_INSTITUTION LIKE "%'.$var_search.'%" OR prog.CODE_PROGRAMME LIKE "%'.$var_search.'%" OR act.CODE_ACTION LIKE "%'.$var_search.'%" OR ACTIVITES LIKE "%'.$var_search.'%" OR RESULTATS_ATTENDUS LIKE "%'.$var_search.'%" OR PROGRAMMATION_FINANCIERE_BIF LIKE "%'.$var_search.'%" OR QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE LIKE "%'.$var_search.'%" OR ptba.UNITE LIKE "%'.$var_search.'%")') : '';

    $conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    $conditionsfilter = $critere . ' ' . $search . ' ' . $group;

    // Get the database connection instance
    $db = Database::connect();

    // Store the original sql_mode value
    $result = $db->query("SELECT @@SESSION.sql_mode AS sql_mode");
    $rowx = $result->getRow();
    $originalSqlMode = $rowx->sql_mode;

    // Modify the sql_mode option to disable only_full_group_by
    $db->query("SET sql_mode=''");

    try
    {

      $requetedebase= 'SELECT racc.PTBA_ID,ptba.PTBA_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE, ACTIVITES, RESULTATS_ATTENDUS, SUM(PROGRAMMATION_FINANCIERE_BIF) AS total_fbu, SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) AS qte_total, SUM(QT1) AS QT1, SUM(QT2) AS QT2, SUM(QT3) AS QT3, SUM(QT4) AS QT4, SUM(T1) AS T1, SUM(T2) AS T2, SUM(T3) AS T3, SUM(T4) AS T4,ptba.UNITE,SUM(racc.MONTANT_RACCROCHE) as mont_real, SUM(racc.QTE_RACCROCHE) AS qte_real, SUM(racc.MONTANT_RACCROCHE_LIQUIDATION) AS budget_liquid, RESPONSABLE,racc.COMMENTAIRE FROM ptba LEFT JOIN execution_budgetaire_raccrochage_activite_new racc ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 '.$cond_trim.' GROUP BY racc.PTBA_ID';

      $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
      $limit = 'LIMIT 0,10';

      $requetedebases = $requetedebase . ' ' . $conditions;
      $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
      $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";

      $fetch_data = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u=1;

      foreach ($fetch_data as $row)
      {
        $T=0;
        $QT=0;
        if ($IS_PRIVATE==1) {
          $T=$row->T1;
          $QT=$row->QT1;

        }else if ($IS_PRIVATE==2) {

          $T=$row->T2;
          $QT=$row->QT2;

        }else if ($IS_PRIVATE==3) {

          $T=$row->T3;
          $QT=$row->QT3;
        }else if ($IS_PRIVATE==4){

          $T=$row->T4;
          $QT=$row->QT4;
        }else{
          $T = $row->total_fbu;
          $QT= $row->qte_total;
        }

        $taux_qte_real= (floatval($row->qte_real)*100)/floatval($QT);
        $taux_exec_finance= (floatval($row->mont_real)*100)/floatval($T);

        $cummule = 'SELECT PTBA_ID,SUM(MONTANT_RACCROCHE) AS mont_cum, SUM(QTE_RACCROCHE) AS qte_cum FROM execution_budgetaire_raccrochage_activite_new WHERE PTBA_ID='.$row->PTBA_ID.' GROUP BY PTBA_ID';
        $cummule="CALL `getList`('".$cummule."')";
        $get_cummule = $this->ModelPs->getRequeteOne($cummule);

        $taux_finance_cummule=(floatval($get_cummule['mont_cum'])*100)/floatval($row->total_fbu);
        $taux_qte_cummule=(floatval($get_cummule['qte_cum'])*100)/floatval($row->qte_total);

        $sub_array = array();
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $row->CODE_MINISTERE.' - '.$row->INTITULE_MINISTERE;
        $sub_array[] = $row->CODE_PROGRAMME.' - '.$row->INTITULE_PROGRAMME;
        $sub_array[] = $row->CODE_ACTION.' - '.$row->LIBELLE_ACTION;
        $sub_array[] = $row->ACTIVITES;
        $sub_array[] = $row->RESULTATS_ATTENDUS;
        $sub_array[] = $row->total_fbu;
        $sub_array[] = $row->qte_total;
        $sub_array[] = $QT;
        $sub_array[] = $row->UNITE;
        $sub_array[] = $row->qte_real;
        $sub_array[] = $taux_qte_real;
        $sub_array[] = $T;
        $sub_array[] = $row->budget_liquid;
        $sub_array[] = $taux_exec_finance;
        $sub_array[] = $get_cummule['mont_cum'];
        $sub_array[] = $taux_finance_cummule;
        $sub_array[] = $get_cummule['qte_cum'];
        $sub_array[] = $taux_qte_cummule;
        $sub_array[] = $row->RESPONSABLE;
        $sub_array[] = $row->COMMENTAIRE;

        $data[] = $sub_array;
      }
    }
    finally
    {
      //Reset the sql_mode back to the original value
      $db->query("SET sql_mode='{$originalSqlMode}'");
    }

    $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
    $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,

    );
    return $this->response->setJSON($output);

  }

  public function export_excel($IS_PRIVATE,$INSTITUTION_ID,$PROGRAMME_ID,$ACTION_ID,$ANNEE_BUDGETAIRE_ID)
  {
    $session  = \Config\Services::session();
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $critere='';
    if(!empty($IS_PRIVATE) && $IS_PRIVATE != 5)
    {
      $critere.=' AND racc.TRIMESTRE_ID='.$IS_PRIVATE;
    }

    if (!empty($INSTITUTION_ID))
    {
      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID='.$INSTITUTION_ID.' ORDER BY DESCRIPTION_INSTITUTION ASC';
      $getInst = "CALL `getTable`('" . $getInst . "');";
      $code = $this->ModelPs->getRequeteOne($getInst);
      $critere.=' AND ptba.INSTITUTION_ID="'.$code['INSTITUTION_ID'].'"' ;
    }

    if (!empty($PROGRAMME_ID))
    {
      $getprogram  = 'SELECT PROGRAMME_ID, INSTITUTION_ID, CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE PROGRAMME_ID='.$PROGRAMME_ID.' ORDER BY INTITULE_PROGRAMME  ASC';
      $getprogram = "CALL `getTable`('" . $getprogram . "');";
      $code = $this->ModelPs->getRequeteOne($getprogram);
      $critere.=' AND ptba.PROGRAMME_ID="'.$code['PROGRAMME_ID'].'"' ;
    }

    if (!empty($ACTION_ID))
    {
      $getprogram  = 'SELECT ACTION_ID, PROGRAMME_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE ACTION_ID='.$ACTION_ID.' ORDER BY LIBELLE_ACTION  ASC';
      $getprogram = "CALL `getTable`('" . $getprogram . "');";
      $code = $this->ModelPs->getRequeteOne($getprogram);
      $critere.=' AND ptba.ACTION_ID="'.$code['ACTION_ID'].'"' ;
    }

    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $critere.=' AND racc.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
    }

    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1',''.lang('messages_lang.th_code_budgetaire').'');
    $sheet->setCellValue('B1',''.lang('messages_lang.inat_progr_act').'');
    $sheet->setCellValue('C1',''.lang('messages_lang.result_annule').'');
    $sheet->setCellValue('D1',''.lang('messages_lang.budget_annule').'');
    $sheet->setCellValue('E1',''.lang('messages_lang.qte_annule').'');       
    $sheet->setCellValue('F1',''.lang('messages_lang.qte_trim').'');
    $sheet->setCellValue('G1',''.lang('messages_lang.labelle_unite').'');
    $sheet->setCellValue('H1',''.lang('messages_lang.qte_real').'');
    $sheet->setCellValue('I1',''.lang('messages_lang.taux_qte_real').'');
    $sheet->setCellValue('J1',''.lang('messages_lang.budget_trim').'');
    $sheet->setCellValue('K1',''.lang('messages_lang.liquide_trim').'');
    $sheet->setCellValue('L1',''.lang('messages_lang.taux_exec_finance').'');
    $sheet->setCellValue('M1',''.lang('messages_lang.budget_cummule').'');
    $sheet->setCellValue('N1',''.lang('messages_lang.taux_cummule').'');
    $sheet->setCellValue('O1',''.lang('messages_lang.result_cummule').'');
    $sheet->setCellValue('P1',''.lang('messages_lang.taux_real_cummule').'');
    $sheet->setCellValue('Q1',''.lang('messages_lang.respo_struct').'');
    $sheet->setCellValue('R1',''.lang('messages_lang.labelle_observartion').'');

    // Get the database connection instance
    $db = Database::connect();

    // Store the original sql_mode value
    $result = $db->query("SELECT @@SESSION.sql_mode AS sql_mode");
    $rowx = $result->getRow();
    $originalSqlMode = $rowx->sql_mode;

    // Modify the sql_mode option to disable only_full_group_by
    $db->query("SET sql_mode=''");

    try
    {
      $getdonnees = 'SELECT racc.PTBA_ID,ptba.PTBA_ID,inst.INSTITUTION_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE, ACTIVITES, RESULTATS_ATTENDUS, SUM(PROGRAMMATION_FINANCIERE_BIF) AS total_fbu, SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) AS qte_total, SUM(QT1) AS QT1, SUM(QT2) AS QT2, SUM(QT3) AS QT3, SUM(QT4) AS QT4, SUM(T1) AS T1, SUM(T2) AS T2, SUM(T3) AS T3, SUM(T4) AS T4,ptba.UNITE,SUM(racc.MONTANT_RACCROCHE) as mont_real, SUM(racc.QTE_RACCROCHE) AS qte_real, SUM(racc.MONTANT_RACCROCHE_LIQUIDATION) AS budget_liquid, RESPONSABLE,racc.COMMENTAIRE FROM ptba LEFT JOIN execution_budgetaire_raccrochage_activite_new racc ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 '.$critere.' GROUP BY inst.CODE_INSTITUTION';
      $getdonnees = "CALL `getTable`('" . $getdonnees . "');";
      $donnees= $this->ModelPs->getRequete($getdonnees);
      $rows=2;
      if(!empty($donnees))
      {
        $rows=$rows+1;
        foreach($donnees as $key)
        {
          $T=0;
          $QT=0;
          if ($IS_PRIVATE==1) {
            $T=$key->T1;
            $QT=$key->QT1;

          }else if ($IS_PRIVATE==2) {

            $T=$key->T2;
            $QT=$key->QT2;

          }else if ($IS_PRIVATE==3) {

            $T=$key->T3;
            $QT=$key->QT3;
          }else if ($IS_PRIVATE==4){

            $T=$key->T4;
            $QT=$key->QT4;
          }else{
            $T = $key->total_fbu;
            $QT= $key->qte_total;
          }

          $taux_qte_real= (floatval($key->qte_real)*100)/floatval($QT);
          $taux_exec_finance= (floatval($key->mont_real)*100)/floatval($T);

          $cummule = 'SELECT PTBA_ID,SUM(MONTANT_RACCROCHE) AS mont_cum, SUM(QTE_RACCROCHE) AS qte_cum, racc.INSTITUTION_ID,inst.CODE_INSTITUTION FROM execution_budgetaire_raccrochage_activite_new racc JOIN inst_institutions inst ON inst.INSTITUTION_ID=racc.INSTITUTION_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' GROUP BY CODE_INSTITUTION,racc.INSTITUTION_ID';
          $cummule="CALL `getList`('".$cummule."')";
          $get_cummule = $this->ModelPs->getRequeteOne($cummule);

          $taux_finance_cummule=(floatval($get_cummule['mont_cum'])*100)/floatval($key->total_fbu);
          $taux_qte_cummule=(floatval($get_cummule['qte_cum'])*100)/floatval($key->qte_total);

          $sheet->setCellValue('A' . $rows, ' '.$key->CODE_MINISTERE);
          $sheet->setCellValue('B' . $rows, ' '.$key->INTITULE_MINISTERE);
          $sheet->setCellValue('C' . $rows, ' '.' ');
          $sheet->setCellValue('D' . $rows, ' '.$key->total_fbu);
          $sheet->setCellValue('E' . $rows, ' '.' ');
          $sheet->setCellValue('F' . $rows, ' '.' ');
          $sheet->setCellValue('G' . $rows, ' '.' ');
          $sheet->setCellValue('H' . $rows, ' '.' ');
          $sheet->setCellValue('I' . $rows, ' '.' ');
          $sheet->setCellValue('J' . $rows, ' '.$T);
          $sheet->setCellValue('K' . $rows, ' '.$key->budget_liquid);
          $sheet->setCellValue('L' . $rows, ' '.$taux_exec_finance);
          $sheet->setCellValue('M' . $rows, ' '.$get_cummule['mont_cum']);
          $sheet->setCellValue('N' . $rows, ' '.$taux_finance_cummule);
          $sheet->setCellValue('O' . $rows, ' '.' ');
          $sheet->setCellValue('P' . $rows, ' '.' ');
          $sheet->setCellValue('Q' . $rows, ' '.' ');
          $sheet->setCellValue('R' . $rows, ' '.' ');

          $rows++;

          $getprogr  = 'SELECT racc.PTBA_ID,ptba.PTBA_ID,prog.PROGRAMME_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION, ligne.CODE_NOMENCLATURE_BUDGETAIRE, ACTIVITES, RESULTATS_ATTENDUS, SUM(PROGRAMMATION_FINANCIERE_BIF) AS total_fbu, SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) AS qte_total, SUM(QT1) AS QT1, SUM(QT2) AS QT2, SUM(QT3) AS QT3, SUM(QT4) AS QT4, SUM(T1) AS T1, SUM(T2) AS T2, SUM(T3) AS T3, SUM(T4) AS T4,ptba.UNITE,SUM(racc.MONTANT_RACCROCHE) as mont_real, SUM(racc.QTE_RACCROCHE) AS qte_real, SUM(racc.MONTANT_RACCROCHE_LIQUIDATION) AS budget_liquid, RESPONSABLE,racc.COMMENTAIRE FROM ptba LEFT JOIN execution_budgetaire_raccrochage_activite_new racc ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptna.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" '.$critere.' GROUP BY prog.CODE_PROGRAMME';
          $getprogr = "CALL `getTable`('" . $getprogr . "');";
          $programmes= $this->ModelPs->getRequete($getprogr);

          if(!empty($programmes))
          {
            $rows1=$rows+1;
            foreach($programmes as $progr)
            {
              $T=0;
              $QT=0;
              if ($IS_PRIVATE==1) {
                $T=$progr->T1;
                $QT=$progr->QT1;

              }else if ($IS_PRIVATE==2) {

                $T=$progr->T2;
                $QT=$progr->QT2;

              }else if ($IS_PRIVATE==3) {

                $T=$progr->T3;
                $QT=$progr->QT3;
              }else if ($IS_PRIVATE==4){

                $T=$progr->T4;
                $QT=$progr->QT4;
              }else{
                $T = $progr->total_fbu;
                $QT= $progr->qte_total;
              }

              $taux_qte_real= (floatval($progr->qte_real)*100)/floatval($QT);
              $taux_exec_finance= (floatval($progr->mont_real)*100)/floatval($T);

              $cummule = 'SELECT SUM(MONTANT_RACCROCHE) AS mont_cum, SUM(QTE_RACCROCHE) AS qte_cum,prog.CODE_PROGRAMME FROM execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE prog.PROGRAMME_ID="'.$progr->PROGRAMME_ID.'" GROUP BY prog.CODE_PROGRAMME';
              $cummule="CALL `getList`('".$cummule."')";
              $get_cummule = $this->ModelPs->getRequeteOne($cummule);

              $taux_finance_cummule=(floatval($get_cummule['mont_cum'])*100)/floatval($progr->total_fbu);
              $taux_qte_cummule=(floatval($get_cummule['qte_cum'])*100)/floatval($progr->qte_total);

              $sheet->setCellValue('A' . $rows1, $progr->CODE_PROGRAMME);
              $sheet->setCellValue('B' . $rows1, $progr->INTITULE_PROGRAMME);
              $sheet->setCellValue('C' . $rows1, ' '.' ');
              $sheet->setCellValue('D' . $rows1, ' '.$progr->total_fbu);
              $sheet->setCellValue('E' . $rows1, ' '.' ');
              $sheet->setCellValue('F' . $rows1, ' '.' ');
              $sheet->setCellValue('G' . $rows1, ' '.' ');
              $sheet->setCellValue('H' . $rows1, ' '.' ');
              $sheet->setCellValue('I' . $rows1, ' '.' ');
              $sheet->setCellValue('J' . $rows1, ' '.$T);
              $sheet->setCellValue('K' . $rows1, ' '.$progr->budget_liquid);
              $sheet->setCellValue('L' . $rows1, ' '.$taux_exec_finance);
              $sheet->setCellValue('M' . $rows1, ' '.$get_cummule['mont_cum']);
              $sheet->setCellValue('N' . $rows1, ' '.$taux_finance_cummule);
              $sheet->setCellValue('O' . $rows1, ' '.' ');
              $sheet->setCellValue('P' . $rows1, ' '.' ');
              $sheet->setCellValue('Q' . $rows1, ' '.' ');
              $sheet->setCellValue('R' . $rows1, ' '.' ');
              $rows1++;

              $getaction  = 'SELECT racc.PTBA_ID,ptba.PTBA_ID,act.ACTION_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE, ACTIVITES, RESULTATS_ATTENDUS,SUM(PROGRAMMATION_FINANCIERE_BIF) AS total_fbu, SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) AS qte_total, SUM(QT1) AS QT1, SUM(QT2) AS QT2, SUM(QT3) AS QT3, SUM(QT4) AS QT4, SUM(T1) AS T1, SUM(T2) AS T2, SUM(T3) AS T3, SUM(T4) AS T4,ptba.UNITE,SUM(racc.MONTANT_RACCROCHE) as mont_real, SUM(racc.QTE_RACCROCHE) AS qte_real, SUM(racc.MONTANT_RACCROCHE_LIQUIDATION) AS budget_liquid, RESPONSABLE,racc.COMMENTAIRE FROM ptba LEFT JOIN execution_budgetaire_raccrochage_activite_new racc ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" AND ptba.PROGRAMME_ID="'.$progr->PROGRAMME_ID.'" '.$critere.' GROUP BY act.CODE_ACTION';
              $getaction = "CALL `getTable`('" . $getaction . "');";
              $actions= $this->ModelPs->getRequete($getaction);

              if(!empty($actions))
              {
                $rows2=$rows1+1;
                foreach($actions as $act)
                {
                  $T=0;
                  $QT=0;
                  if ($IS_PRIVATE==1) {
                    $T=$act->T1;
                    $QT=$act->QT1;

                  }else if ($IS_PRIVATE==2) {

                    $T=$act->T2;
                    $QT=$act->QT2;

                  }else if ($IS_PRIVATE==3) {

                    $T=$act->T3;
                    $QT=$act->QT3;
                  }else if ($IS_PRIVATE==4){

                    $T=$act->T4;
                    $QT=$act->QT4;
                  }else{
                    $T = $act->total_fbu;
                    $QT= $act->qte_total;
                  }

                  $taux_qte_real= (floatval($act->qte_real)*100)/floatval($QT);
                  $taux_exec_finance= (floatval($act->mont_real)*100)/floatval($T);

                  $cummule = 'SELECT SUM(MONTANT_RACCROCHE) AS mont_cum, SUM(QTE_RACCROCHE) AS qte_cum,act.CODE_ACTION FROM execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE ptba.ACTION_ID="'.$act->ACTION_ID.'" GROUP BY act.CODE_ACTION';
                  $cummule="CALL `getList`('".$cummule."')";
                  $get_cummule = $this->ModelPs->getRequeteOne($cummule);

                  $taux_finance_cummule=(floatval($get_cummule['mont_cum'])*100)/floatval($act->total_fbu);
                  $taux_qte_cummule=(floatval($get_cummule['qte_cum'])*100)/floatval($act->qte_total);

                  $sheet->setCellValue('A' . $rows2, $act->CODE_ACTION);
                  $sheet->setCellValue('B' . $rows2, $act->LIBELLE_ACTION);
                  $sheet->setCellValue('C' . $rows2, ' '.' ');
                  $sheet->setCellValue('D' . $rows2, ' '.$act->total_fbu);
                  $sheet->setCellValue('E' . $rows2, ' '.' ');
                  $sheet->setCellValue('F' . $rows2, ' '.' ');
                  $sheet->setCellValue('G' . $rows2, ' '.' ');
                  $sheet->setCellValue('H' . $rows2, ' '.' ');
                  $sheet->setCellValue('I' . $rows2, ' '.' ');
                  $sheet->setCellValue('J' . $rows2, ' '.$T);
                  $sheet->setCellValue('K' . $rows2, ' '.$act->budget_liquid);
                  $sheet->setCellValue('L' . $rows2, ' '.$taux_exec_finance);
                  $sheet->setCellValue('M' . $rows2, ' '.$get_cummule['mont_cum']);
                  $sheet->setCellValue('N' . $rows2, ' '.$taux_finance_cummule);
                  $sheet->setCellValue('O' . $rows2, ' '.' ');
                  $sheet->setCellValue('P' . $rows2, ' '.' ');
                  $sheet->setCellValue('Q' . $rows2, ' '.' ');
                  $sheet->setCellValue('R' . $rows2, ' '.' ');

                  $rows2++;

                  $activties_infos  = 'SELECT racc.PTBA_ID,ptba.PTBA_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE, ACTIVITES, RESULTATS_ATTENDUS, SUM(PROGRAMMATION_FINANCIERE_BIF) AS total_fbu, SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) AS qte_total, SUM(QT1) AS QT1, SUM(QT2) AS QT2, SUM(QT3) AS QT3, SUM(QT4) AS QT4, SUM(T1) AS T1, SUM(T2) AS T2, SUM(T3) AS T3, SUM(T4) AS T4,ptba.UNITE,SUM(racc.MONTANT_RACCROCHE) as mont_real, SUM(racc.QTE_RACCROCHE) AS qte_real, SUM(racc.MONTANT_RACCROCHE_LIQUIDATION) AS budget_liquid, RESPONSABLE,racc.COMMENTAIRE FROM ptba LEFT JOIN execution_budgetaire_raccrochage_activite_new racc ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" AND ptba.PROGRAMME_ID="'.$progr->PROGRAMME_ID.'" AND ptba.ACTION_ID="'.$act->ACTION_ID.'" '.$critere.' GROUP BY racc.PTBA_ID';
                  $activties_infos = "CALL `getTable`('" . $activties_infos . "');";
                  $infos_activites= $this->ModelPs->getRequete($activties_infos);

                  if(!empty($infos_activites))
                  {
                    $rows3=$rows2+1;
                    foreach($infos_activites as $actites)
                    {
                      $T=0;
                      $QT=0;
                      if ($IS_PRIVATE==1) {
                        $T=$actites->T1;
                        $QT=$actites->QT1;

                      }else if ($IS_PRIVATE==2) {

                        $T=$actites->T2;
                        $QT=$actites->QT2;

                      }else if ($IS_PRIVATE==3) {

                        $T=$actites->T3;
                        $QT=$actites->QT3;
                      }else if ($IS_PRIVATE==4){

                        $T=$actites->T4;
                        $QT=$actites->QT4;
                      }else{
                        $T = $actites->total_fbu;
                        $QT= $actites->qte_total;
                      }

                      $taux_qte_real= (floatval($actites->qte_real)*100)/floatval($QT);
                      $taux_exec_finance= (floatval($actites->mont_real)*100)/floatval($T);

                      $cummule = 'SELECT PTBA_ID,SUM(MONTANT_RACCROCHE) AS mont_cum, SUM(QTE_RACCROCHE) AS qte_cum FROM execution_budgetaire_raccrochage_activite_new WHERE PTBA_ID='.$actites->PTBA_ID.' GROUP BY PTBA_ID';
                      $cummule="CALL `getList`('".$cummule."')";
                      $get_cummule = $this->ModelPs->getRequeteOne($cummule);

                      $taux_finance_cummule=(floatval($get_cummule['mont_cum'])*100)/floatval($actites->total_fbu);
                      $taux_qte_cummule=(floatval($get_cummule['qte_cum'])*100)/floatval($actites->qte_total);

                      $sheet->setCellValue('A' . $rows3, $actites->CODE_NOMENCLATURE_BUDGETAIRE);
                      $sheet->setCellValue('B' . $rows3, $actites->ACTIVITES);
                      $sheet->setCellValue('C' . $rows3, ' '.$act->RESULTATS_ATTENDUS);
                      $sheet->setCellValue('D' . $rows3, ' '.$act->total_fbu);
                      $sheet->setCellValue('E' . $rows3, ' '.$act->qte_total);
                      $sheet->setCellValue('F' . $rows3, ' '.$QT);
                      $sheet->setCellValue('G' . $rows3, ' '.$act->UNITE);
                      $sheet->setCellValue('H' . $rows3, ' '.$act->qte_real);
                      $sheet->setCellValue('I' . $rows3, ' '.$taux_qte_real);
                      $sheet->setCellValue('J' . $rows3, ' '.$T);
                      $sheet->setCellValue('K' . $rows3, ' '.$act->budget_liquid);
                      $sheet->setCellValue('L' . $rows3, ' '.$taux_exec_finance);
                      $sheet->setCellValue('M' . $rows3, ' '.$get_cummule['mont_cum']);
                      $sheet->setCellValue('N' . $rows3, ' '.$taux_finance_cummule);
                      $sheet->setCellValue('O' . $rows3, ' '.$get_cummule["qte_cum"]);
                      $sheet->setCellValue('P' . $rows3, ' '.$taux_qte_cummule);
                      $sheet->setCellValue('Q' . $rows3, ' '.$act->RESPONSABLE);
                      $sheet->setCellValue('R' . $rows3, ' '.$act->COMMENTAIRE);

                      $rows3++;
                    }
                    $rows2=$rows3+1;
                  }
                }
                $rows1=$rows2+1;
              }
            }
            $rows=$rows1+1;
          }
        }
      }

    }
    finally
    {
      //Reset the sql_mode back to the original value
      $db->query("SET sql_mode='{$originalSqlMode}'");
    }

    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(30);
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

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Canvas suivi d\'évaluation deux.xlsx');
    return redirect('ihm/Canvas_Suivie_evaluation_Deux/canvas_liste');
  }

    // trouver les program a partir de institution choisit
  function get_program($INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $getprogram  = 'SELECT PROGRAMME_ID, INSTITUTION_ID, CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE INSTITUTION_ID='.$INSTITUTION_ID.' ORDER BY INTITULE_PROGRAMME  ASC';
    $getprogram = "CALL `getTable`('" . $getprogram . "');";
    $programs = $this->ModelPs->getRequete($getprogram);

    $html='<option value="">'.lang('messages_lang.labelle_selecte').'</option>';
    foreach ($programs as $key)
    {
      $html.='<option value="'.$key->PROGRAMME_ID.'">'.$key->CODE_PROGRAMME.'-'.$key->INTITULE_PROGRAMME.'</option>';
    }

    $output = array(

      "programs" => $html,
    );

    return $this->response->setJSON($output);
  }

    // trouver les action a partir de program choisit
  function get_action($PROGRAMME_ID=0)
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $getaction  = 'SELECT ACTION_ID, PROGRAMME_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID='.$PROGRAMME_ID.' ORDER BY LIBELLE_ACTION  ASC';
    $getaction = "CALL `getTable`('" . $getaction . "');";
    $actions = $this->ModelPs->getRequete($getaction);

    $html='<option value="">'.lang('messages_lang.labelle_selecte').'</option>';
    foreach ($actions as $key)
    {
      $html.='<option value="'.$key->ACTION_ID.'">'.$key->CODE_ACTION.'-'.$key->LIBELLE_ACTION.'</option>';
    }

    $output = array(

      "actions" => $html,
    );

    return $this->response->setJSON($output);
  }
}
?>