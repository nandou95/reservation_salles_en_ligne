<?php
/*
*HABIMANA Nandou
*Titre: Suivi des executions
*Numero de telephone: (+257) 69301985
*Email: nandou@mediabox.bi
*Date: 06/07/2024

*Iradukunda Joa-Kevin
*Titre: Suivi des executions
*Numero de telephone: (+257) 62636535
*Email: joa-kevin.iradukunda@mediabox.bi
*Date de modification: 05/08/2024

*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 2000);
ini_set('memory_limit','12048M');

class Suivi_Execution extends BaseController
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
    $db=db_connect();
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function get_soutut()
  {
    $callpsreq = "CALL getRequete(?,?,?,?);";

    $html='<option value="">'.lang('messages_lang.labelle_selecte').'</option>';
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    if(!empty($INSTITUTION_ID))
    {
      $st = $this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','INSTITUTION_ID='.$INSTITUTION_ID,'CODE_SOUS_TUTEL ASC');
      $get_st = $this->ModelPs->getRequete($callpsreq, $st);

      foreach($get_st as $key)
      {
        $html.="<option value='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL." ".$key->DESCRIPTION_SOUS_TUTEL."</option>";
      }
    }


    $output = array('status' => TRUE ,'html' => $html);
    return $this->response->setJSON($output);
  }

  public function change_count()
  {
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $output['EBCORRIGE']="<span>".$menu_suivi_exec['EBCORRIGE']."</span>";
    $output['EBAVALIDE']="<span>".$menu_suivi_exec['EBAVALIDE']."</span>";
    $output['EJFAIRE']="<span>".$menu_suivi_exec['EJFAIRE']."</span>";
    $output['EJCORRIGER']="<span>".$menu_suivi_exec['EJCORRIGER']."</span>";
    $output['EJVALIDER']="<span>".$menu_suivi_exec['EJVALIDER']."</span>";
    $output['LIQFAIRE']="<span>".$menu_suivi_exec['LIQFAIRE']."</span>";
    $output['LIQCORRIGER']="<span>".$menu_suivi_exec['LIQCORRIGER']."</span>";
    $output['LIQVALIDE']="<span>".$menu_suivi_exec['LIQVALIDE']."</span>";
    $output['ORDVALIDE']="<span>".$menu_suivi_exec['ORDVALIDE']."</span>";


    $output['prise_charge_a_recep']="<span>".$menu_suivi_exec['prise_charge_a_recep']."</span>";
    $output['titre_attente_etab']="<span>".$menu_suivi_exec['titre_attente_etab']."</span>";
    $output['titre_attente_corr']="<span>".$menu_suivi_exec['titre_attente_corr']."</span>";
    $output['dir_compt_recep']="<span>".$menu_suivi_exec['dir_compt_recep']."</span>";
    $output['obr_recep']="<span>".$menu_suivi_exec['obr_recep']."</span>";
    $output['dec_att_trait']="<span>".$menu_suivi_exec['dec_att_trait']."</span>";
    $output['dec_att_recep_brb']="<span>".$menu_suivi_exec['dec_att_recep_brb']."</span>";
    return $this->response->setJSON($output);
  }

  // Debut Engagement budgétaire en attente de correction
  public function engag_budj_corriger()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);


    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Eng_Budg_suivi_exec_a_corriger_view',$data);
  }

  public function listing_engag_budj_corriger()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere4=" ";
    $critere5=" ";
    $critere1="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

 if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere5." ".$critere4." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere5." ".$critere4." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=4";   
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;

    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point; 
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Engagement budgétaire en attente de correction

  // Fonction pour exporter un fichier Excel des engagements à corriger
    public function exporter_Excel_Eng_Budg_Corr($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
       $session  = \Config\Services::session();
       $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
       
        if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
     $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);
    $critere1=" ";
    $critere2=" ";
    $critere3=" ";
    $critere4=" ";
    $critere5=" ";
      if($INSTITUTION_ID>0)
      {
       $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
       }

     if($SOUS_TUTEL_ID>0)
     {
      $critere3=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
     }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

  $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
  $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=4 ".$critere1.$critere3.$critere4.$critere5."";

  $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('C1', 'ENGAGEMENTS BUDGETAIRES EN ATTENTE DE CORRECTION');
  $sheet->setCellValue('A3', '#');
  $sheet->setCellValue('B3', 'BON ENGAGEMENT');
  $sheet->setCellValue('C3', 'IMPUTATION');
  $sheet->setCellValue('D3', 'INTITULE IMPUTATION');
  $sheet->setCellValue('E3', 'TACHE');
  $sheet->setCellValue('F3', 'OBJET D\'ENGAGEMENT');
  $sheet->setCellValue('G3', 'ENGAGEMENT BUDGETAIRE');



  $rows = 4;
  $i=1;
  foreach ($getData as $key)
  {
          //get les taches
    // $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;

           //get les taches
    $get_task = "SELECT  DESC_TACHE FROM  ptba_tache tache JOIN execution_budgetaire_execution_tache exec_tache  ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID WHERE exec_tache.EXECUTION_BUDGETAIRE_ID = ".$key->EXECUTION_BUDGETAIRE_ID;
    $get_task = 'CALL `getTable`("'.$get_task.'");';
    $tasks = $this->ModelPs->getRequete($get_task);
    $task_items = '';

    foreach ($tasks as $task) {
      $task_items .= "- ".$task->DESC_TACHE . "\n";
    }

    $sheet->setCellValue('A' . $rows, $i);
    $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
    $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
     $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

    $sheet->setCellValue('E' . $rows, trim($task_items));
    $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
    $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
    $sheet->setCellValue('F' . $rows, $key->ENG_BUDGETAIRE);

    $rows++;
    $i++;
  } 

  $code=date('YmdHis');
  $writer = new Xlsx($spreadsheet);
  $writer->save('engag_budj_corriger.xlsx');
  return $this->response->download('engag_budj_corriger.xlsx', null)->setFileName('Eng_bugd_acorriger'.$code.'.xlsx');

  return redirect('double_commande_new/Suivi_Execution/engag_budj_corriger');

}

  // Debut Engagement budgétaire en attente de validation
  public function engag_budj_valide()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);


    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Eng_Budg_suivi_exec_a_valider_view',$data);
  }

  public function listing_engag_budj_valider()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

     if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=3";   
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point; 
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Engagement budgétaire en attente de validation

  // Fonction pour exporter un fichier Excel des engagements à valider
    public function exporter_Excel_Eng_Budg_valider($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
       $session  = \Config\Services::session();
       $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
       
        if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
     $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);
    $critere1=" ";
    $critere2=" ";
    $critere3=" ";
    $critere4=" ";
    $critere5=" ";
      if($INSTITUTION_ID>0)
      {
       $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
       }

     if($SOUS_TUTEL_ID>0)
     {
      $critere3=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
     }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

  $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
  $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=3 ".$critere1.$critere3.$critere4.$critere5."";

  $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('C1', 'ENGAGEMENTS BUDGETAIRES EN ATTENTE DE CORRECTION');
  $sheet->setCellValue('A3', '#');
  $sheet->setCellValue('B3', 'BON ENGAGEMENT');
  $sheet->setCellValue('C3', 'IMPUTATION');
  $sheet->setCellValue('D3', 'INTITULE IMPUTATION');
  $sheet->setCellValue('E3', 'TACHE');

  $sheet->setCellValue('F3', 'OBJET ENGAGEMENT');
  $sheet->setCellValue('G3', 'ENGAGEMENT BUDGETAIRE');


  $rows = 4;
  $i=1;
  foreach ($getData as $key)
  {
          //get les taches
    $get_task = "SELECT  DESC_TACHE FROM  ptba_tache tache JOIN execution_budgetaire_execution_tache exec_tache  ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID WHERE exec_tache.EXECUTION_BUDGETAIRE_ID = ".$key->EXECUTION_BUDGETAIRE_ID;
    $get_task = 'CALL `getTable`("'.$get_task.'");';
    $tasks = $this->ModelPs->getRequete($get_task);
    $task_items = '';

    foreach ($tasks as $task) {
      $task_items .= "- ".$task->DESC_TACHE . "\n";
    }

    $sheet->setCellValue('A' . $rows, $i);
    $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
    $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
    
    $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

    $sheet->setCellValue('E' . $rows, trim($task_items));
    $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
     $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
    $sheet->setCellValue('G' . $rows, $key->ENG_BUDGETAIRE);

    $rows++;
    $i++;
  } 

  $code=date('YmdHis');
  $writer = new Xlsx($spreadsheet);
  $writer->save('engag_budj_valider.xlsx');
  return $this->response->download('engag_budj_valider.xlsx', null)->setFileName('engag_budj_valider'.$code.'.xlsx');

  return redirect('double_commande_new/Suivi_Execution/engag_budj_valide');

}
  // Debut Engagement juridique en attente d'engagement
  public function engag_jurd_faire()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Eng_jurd_suivi_exec_a_faire_view',$data);
  }

  public function listing_engag_jurd_a_faire()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";



    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }
      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      }


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=6"; 
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    // print_r($fetch_actions);die();
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire  ON ebet.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID WHERE execution_budgetaire.EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;             
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Engagement juridique en attente d'engagement


  // Exporter la liste des engagements juridiques à faire
  public function exporter_Excel_Eng_Jur_Faire($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    $critere1='';
    $critere2='';
    $critere4="";
    $critere5="";

    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND histo.USER_ID='.$user_id;
    }


    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else{
      $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=6 ".$critere2.$critere1.$critere4.$critere5.$group;

    // print_r($requetedebase);die();

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
    $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES A FAIRE');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
    $sheet->setCellValue('E7', 'TACHE');
    $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
   
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
      $get_task = "SELECT  DESC_TACHE FROM execution_budgetaire_execution_tache ebet  JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
    
      $sheet->setCellValue('E' . $rows, trim($task_items));
      $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
  
      $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
     
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('engagements_juridiques_afaire.xlsx');
    return $this->response->download('engagements_juridiques_afaire.xlsx', null)->setFileName('Engagements juridiques a faire'.uniqid().'.xlsx');
    return redirect('double_commande_new/Menu_Engagement_Juridique');
  }

 

  // Debut Engagement juridique en attente de correction
  public function engag_jurd_corriger()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Eng_jurd_suivi_exec_a_corriger_view',$data);
  }

  public function listing_engag_jurd_a_corriger()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

       if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere5." ".$critere4." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere5." ".$critere4." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=8";   
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;             
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Engagement juridique en attente de correction

  //Exporter un fichier Excel des engagement juridiques à corriger 

  public function exporter_Excel_Eng_Jur_corriger($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
   
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
 
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
   

    $ID_INST='';
    $critere1='';
    $critere2='';
    $critere4="";
    $critere5="";

  
    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=8 ".$critere2.$critere4.$critere5.$group;

    // print_r($requetedebase);die();
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
    $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES A CORRIGER');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
    $sheet->setCellValue('E7', 'TACHE');
    $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
   
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
      $get_task = "SELECT  DESC_TACHE FROM execution_budgetaire_execution_tache ebet  JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);
    
      $sheet->setCellValue('E' . $rows, trim($task_items));
      $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
      $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
  
      $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
      $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
      
     
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('engagements_juridiques_acorriger.xlsx');
    return $this->response->download('engagements_juridiques_acorriger.xlsx', null)->setFileName('Engagements juridiques a corriger'.uniqid().'.xlsx');
    return redirect('double_commande_new/Suivi_Execution/engag_jurd_corriger');
  }

  // Debut Engagement juridique en attente de confirmation
  public function engag_jurd_valide()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Eng_jurd_suivi_exec_a_valider_view',$data);
  }

  public function listing_engag_jurd_a_valider()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=7";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
 
   //Excel des engagement juridiques à valider
   public function excel_Eng_jurd_Valider($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

    $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=7 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES A VALIDER');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
     
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM  execution_budgetaire_execution_tache JOIN ptba_tache ON  execution_budgetaire_execution_tache.PTBA_TACHE_ID = ptba_tache.PTBA_TACHE_ID WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
       
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('engag_jurd_valide.xlsx');
      return $this->response->download('engag_jurd_valide.xlsx', null)->setFileName('Engagements juridiques à valider'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/engag_jurd_valide');


  }

  // Debut Liquidation en attente de traitement
  public function liquidation_faire()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Liquidation_suivi_exec_a_faire_view',$data);
  }

  public function listing_liquidation_faire()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');

    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT)  AND empty($DATE_FIN))
      {
        $critere4.=" AND date_format(exec.DATE_ENG_JURIDIQUE,'%Y-%m-%d') >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND date_format(exec.DATE_ENG_JURIDIQUE,'%Y-%m-%d') >= '".$DATE_DEBUT."' AND date_format( exec.DATE_ENG_JURIDIQUE,'%Y-%m-%d') <= '".$DATE_FIN."'";
      }
      // print_r($critere4);die();
      

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=10";   
    $requetedebases=$requetedebase." ".$conditions;
    // print_r($requetedebases);die();
    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;              
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Liquidation en attente de traitement

    // Excel des liquidations à faire (en attente de traitement)
   public function excel_liquidation_faire($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=10 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'LISTE DES LIQUIDATIONS A TRAITER');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
     
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM  execution_budgetaire_execution_tache JOIN ptba_tache ON  execution_budgetaire_execution_tache.PTBA_TACHE_ID = ptba_tache.PTBA_TACHE_ID WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
       
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('liquidation_faire.xlsx');
      return $this->response->download('liquidation_faire.xlsx', null)->setFileName('Liquidations à traiter'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/liquidation_faire');


  }


  // Debut Liquidation en attente de correction
  public function liquidation_corrige()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Liquidation_suivi_exec_a_corriger_view',$data);
  }

  public function listing_liquidation_corrige()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=12"; 
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Liquidation en attente de correction

  // Excel  des liquidation a corriger
   public function excel_liquidation_corriger($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=12 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'LISTE DES LIQUIDATIONS A CORRIGER');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
     
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM  execution_budgetaire_execution_tache JOIN ptba_tache ON  execution_budgetaire_execution_tache.PTBA_TACHE_ID = ptba_tache.PTBA_TACHE_ID WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
       
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('liquidation_corriger.xlsx');
      return $this->response->download('liquidation_corriger.xlsx', null)->setFileName('Liquidations à corriger'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/liquidation_corrige');
  }

  // Debut Liquidation en attente de confirmation
  public function liquidation_valide()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Liquidation_suivi_exec_a_valider_view',$data);
  }

  public function listing_liquidation_valide()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
     $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

       if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=11";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);


      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // FIn Liquidation en attente de confirmation

  // EXPORT en excel des liquiodation en attente de confirmation
   

   public function excel_liquidation_valider($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=11 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'LISTE DES LIQUIDATIONS EN ATTENTE DE CONFIRMATION');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
     
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM  execution_budgetaire_execution_tache JOIN ptba_tache ON  execution_budgetaire_execution_tache.PTBA_TACHE_ID = ptba_tache.PTBA_TACHE_ID WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
       
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('liquidation_valider.xlsx');
      return $this->response->download('liquidation_valider.xlsx', null)->setFileName('Liquidations à valider'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/liquidation_valide');
  }



  // Debut Ordonnancement en attente de confirmation
  public function ordonnance_valide()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Ordonnancement_suivi_exec_view',$data);
  }

  public function listing_ordonnance_valide()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere5="";
    $critere4='';
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }
       if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND exec.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION,exec.ORDONNANCEMENT FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID IN (14,15)";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);


      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  // Fin Ordonnancement en attente de confirmation

  //Exporter Liste des ordonnancements en attente de confirmation 
  
  public function exporter_ordo_entente($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.LIQUIDATION,exec.ORDONNANCEMENT FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID IN (14,15) ".$critere2.$critere1.$critere4.$critere5.$group;
   
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
      $sheet->setCellValue('A2', 'LISTE ORDONNANCEMENT EN ATTENTE DE TRAITEMENT');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE ');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
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

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('ordonancement.xlsx');
      return $this->response->download('ordonancement.xlsx', null)->setFileName('ordonancement a Traiter'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/ordonnance_valide');



  }


  //debut prise en charge en attente de reception
  public function prise_charge_attente_reception()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];


    return view('App\Modules\double_commande_new\Views\prise_charge_attente_reception_view',$data);
  }


  public function listing_prise_charge_attente_reception()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5 = "";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

        if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND exec.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=17 AND bon_titre.TYPE_DOCUMENT_ID=1 ";   
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }

  //fin prise en charge en attente de reception


  //EXPORTER SOUS FORMAT EXCEL
  public function exporter_prise_attente_reception($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

    $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=17 AND bon_titre.TYPE_DOCUMENT_ID=1 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'PRISE EN CHARGE EN ATTENTE DE RECEPTION');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
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

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('prise_charge_attente_recep.xlsx');
      return $this->response->download('prise_charge_attente_recep.xlsx', null)->setFileName('Prise en charge a receptionner'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/prise_charge_attente_reception');
  }
  //debut titre en attente d' etablissement
  public function titre_attente_etablissement()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\titre_attente_etablissement_view',$data);
  }

  public function listing_titre_attente_etablissement()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

   

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    // print_r($DATE_DEBUT);die();
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

   
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;
 
    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=20 AND dc.MOUVEMENT_DEPENSE_ID = 5 "; 
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $DESC_TACHE;               
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin titre en attente d' etablissement

  // EXCEL DE TD EN ATTENTE DE DECAISSEMENT

  public function exporter_td_attente_etablissemnt($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

    $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=20 AND dc.MOUVEMENT_DEPENSE_ID = 5  ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'PRISE EN CHARGE EN ATTENTE DE RECEPTION');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT DISTINCT tache.PTBA_TACHE_ID,DESC_TACHE FROM execution_budgetaire_execution_tache ebet  JOIN  ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('titre_attente_etablissement.xlsx');
      return $this->response->download('titre_attente_etablissement.xlsx', null)->setFileName('TD en attente d\'établissement'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/titre_attente_etablissement');
  }


  //debut titre en attente de correction
  public function titre_attente_correction()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\titre_attente_correction_view',$data);
  }

  public function listing_titre_attente_correction()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=37 AND dc.MOUVEMENT_DEPENSE_ID = 5 ";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin titre en attente de correction

  //EXPORTER SOUS FORMAT EXCEL :: Titres de décaissement en atten,te de correction
  public function Excel_titre_attente_correction($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

    $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=37 AND dc.MOUVEMENT_DEPENSE_ID = 5 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES DEJA VALIDES');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT DISTINCT tache.PTBA_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet JOIN  ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
     
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('titre_attente_correction.xlsx');
      return $this->response->download('titre_attente_correction.xlsx', null)->setFileName('Titre de décaissement en attente de correction'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/titre_attente_correction');


  }

  //debut en attente de reception dir compt
  public function titre_attente_reception_dir_compt()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\titre_attente_reception_dir_compt_view',$data);
  }

  public function listing_titre_attente_reception_dir_compt()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

       if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID  
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=22 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1"; 

                     
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin titre en attente de reception dir compt

  //Exporter Excel Titre de décaissement en attente de réception par la direction de la comptabilité
  public function Excel_titre_attente_recep_dir_compt($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID  
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=22 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1  ".$critere2.$critere1.$critere4.$critere5.$group;



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
      $sheet->setCellValue('A2', 'Titre de décaissement en attente de réception par la direction de la comptabilité ');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
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

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('titre_attente_reception_dir_compt.xlsx');
      return $this->response->download('titre_attente_reception_dir_compt.xlsx', null)->setFileName('Titre en attente de reception Direction comptablité'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/titre_attente_reception_dir_compt');


  }

  //debut en attente de reception obr
  public function titre_attente_reception_obr()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\titre_attente_reception_obr_view',$data);
  }

  public function listing_titre_attente_reception_obr()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

        if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere5." ".$critere4." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere5." ".$critere4." ".$search." ".$group;

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=18 AND dc.MOUVEMENT_DEPENSE_ID=5 ";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);


      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;            
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin titre en attente de reception obr
  //Excel titre de decaissement en attente de réception obr
   public function Excel_titre_attente_reception_obr($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0){

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

    $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

    $requetedebase="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=18 AND dc.MOUVEMENT_DEPENSE_ID=5 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'Titre de décaissement en attente de réception OBR ');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
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

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('titre_attente_reception_obr.xlsx');
      return $this->response->download('titre_attente_reception_obr.xlsx', null)->setFileName('Titre en attente de reception OBR'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/titre_attente_reception_obr');


  }

  //debut decaissement en attente de traitement
  public function decais_attente_traitement()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\decais_attente_traitement_view',$data);
  }

  public function listing_decais_attente_traitement()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4="";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5."  ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID IN(29) AND dc.MOUVEMENT_DEPENSE_ID = 6";
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin decaissement en attente de traitement

   //Excel  Decaissement en attente de traitement

      public function excel_decais_attente_traitement($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                    JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID IN(29) AND dc.MOUVEMENT_DEPENSE_ID = 6 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'Titre de décaissement en attente de traitement');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT DISTINCT ebet.PTBA_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('Decaiss_attente_traitement.xlsx');
      return $this->response->download('Decaiss_attente_traitement.xlsx', null)->setFileName('Decaisement en attente de traitement'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/decais_attente_traitement');


  }



  //debut decaissement en attente de recep brb
  public function decais_attente_recep_brb()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);
    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];

    return view('App\Modules\double_commande_new\Views\decais_attente_recep_brb_view',$data);
  }

  public function listing_decais_attente_recep_brb()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1="";
    $critere4 = "";
    $critere5="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($SOUS_TUTEL_ID))
      {
        $critere1.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }
        if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND exec.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache.DESC_TACHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',',exec.LIQUIDATION','exec.ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%')"):'';

    $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$critere4." ".$critere5." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$critere4." ".$critere5." ".$search." ".$group;

    $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=28 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 "; 
                     
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 12) ? (mb_substr($row->DESC_TACHE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 14) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 14) ? (mb_substr($row->COMMENTAIRE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $LIQUIDATION=floatval($row->LIQUIDATION);
      $ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $DESC_TACHE;               
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
      $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
      $sub_array[] = number_format($LIQUIDATION,2,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,2,","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }
  //fin decaissement en attente de recep brb

   // function excel_decais_attente_recep_brb()
    public function excel_decais_attente_recep_brb($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {

      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION');

      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      $critere1='';
      $critere2='';
      $critere4="";
      $critere5="";

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND histo.USER_ID='.$user_id;
      }


      if($INSTITUTION_ID != 0)
      {
        $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{
        $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if($SOUS_TUTEL_ID != 0)
      {
        $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND ebtd.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND ebtd.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           ptba_tache.DESC_TACHE,
                           exec.COMMENTAIRE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_JURIDIQUE,
                           exec.LIQUIDATION,
                           exec.ORDONNANCEMENT 
                    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=28 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 ".$critere2.$critere1.$critere4.$critere5.$group;

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
      $sheet->setCellValue('A2', 'Titre de décaissement en attente de réception BRB');

      $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('A7', '#');
      $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
      $sheet->setCellValue('C7', 'IMPUTATION');
      $sheet->setCellValue('D7', 'INTITULE IMPUTATION');
      $sheet->setCellValue('E7', 'TACHE');
      $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
  
      $sheet->setCellValue('G7', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('H7', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('I7', 'LIQUIDATION');
      $sheet->setCellValue('J7', 'ORDONNANCEMENT');
      $rows = 9;
      $i=1;
      foreach ($getData as $key)
      {
        //Nombre des tâches
        $get_task = "SELECT tache.PTBA_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet JOIN  ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
        $get_task = 'CALL `getTable`("'.$get_task.'");';
        $tasks = $this->ModelPs->getRequete($get_task);
        $task_items = '';

        foreach ($tasks as $task) {
          $task_items .= "- ".$task->DESC_TACHE . "\n";
        }

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
        $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
        $sheet->setCellValue('D' . $rows, $key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE);

        $sheet->setCellValue('E' . $rows, trim($task_items));
        $sheet->getStyle('E' . $rows)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
        // $sheet->setCellValue('G' . $rows, $key->DESC_DEVISE_TYPE);
        $sheet->setCellValue('G' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
        $sheet->setCellValue('I' . $rows, number_format($key->LIQUIDATION, $this->get_precision($key->LIQUIDATION), ",", " "));
        $sheet->setCellValue('J' . $rows, number_format($key->ORDONNANCEMENT, $this->get_precision($key->ORDONNANCEMENT), ",", " "));
        $rows++;
        $i++;
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save('Decaiss_attente_recep_brb.xlsx');
      return $this->response->download('Decaiss_attente_recep_brb.xlsx', null)->setFileName('Decaisement en attente de reception brb'.uniqid().'.xlsx');
      return redirect('double_commande_new/Suivi_Execution/decais_attente_recep_brb');


  }


        //Cette fonction retourne le nombre des chiffres d un nombre ($value) passé en paramètre
      function get_precision($value=0){

        $parts = explode('.', strval($value));
        return isset($parts[1]) ? strlen($parts[1]) : 0; 
      }
}
?>