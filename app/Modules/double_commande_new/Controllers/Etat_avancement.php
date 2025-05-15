<?php
  /**Alain Charbel Nderagakura
    *Titre: Etat d'avancement double commande
    *Numero de telephone: (+257) 62003522
    *WhatsApp: (+257) 76887837
    *Email: charbel@mediabox.bi
    *Date: 24 juin,2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

  class Etat_avancement extends BaseController
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

    //Interface de la liste des activites
    function index($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      return view('App\Modules\double_commande_new\Views\Etat_avancement_View',$data);
    }

    //récupération du sous titre par rapport à l'institution
    function get_sous_titre($INSTITUTION_ID=0)
    {
      if($this->session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $db = db_connect();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID='.$INSTITUTION_ID.' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
      $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

      $html='<option value="">Sélectionner</option>';
      foreach ($sous_tutelle as $key)
      {
        $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
      }

      $output = array(
        "sous_tutel" => $html,
      );

      return $this->response->setJSON($output);
    }

    //fonction pour affichage d'une liste des activites
    public function listing()
    {
      $session  = \Config\Services::session();
      $callpsreq = "CALL `getRequete`(?,?,?,?)";
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }


      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID', 'user_affectaion', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';

      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');
      $NUMERO_BON_ENGAGEMENT = $this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";$critere2="";$crit_num_bon="";

      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID))
        {
          $critere1.=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
        }
      }
      if (!empty($NUMERO_BON_ENGAGEMENT))
      {
        $crit_num_bon=" AND exec.NUMERO_BON_ENGAGEMENT LIKE '%".$NUMERO_BON_ENGAGEMENT."%' ";
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere2.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere2.=" AND exec.DATE_DEMANDE >= ".$DATE_DEBUT." AND exec.DATE_DEMANDE <= ".$DATE_FIN."";
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "''", $var_search);
      $group = " GROUP BY titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';
      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','titre.TITRE_DECAISSEMENT','lign.CODE_NOMENCLATURE_BUDGETAIRE',1,'mvt.DESC_MOUVEMENT_DEPENSE','dc.DESC_ETAPE_DOUBLE_COMMANDE');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%".$var_search."%' OR titre.TITRE_DECAISSEMENT LIKE '%".$var_search."%' OR lign.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%".$var_search."%' OR mvt.DESC_MOUVEMENT_DEPENSE LIKE '%".$var_search."%' OR dc.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%".$var_search."%') "):'';

      $critaire = $critere1." ".$critere2." ".$crit_num_bon;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dc.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,titre.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,mvt.DESC_MOUVEMENT_DEPENSE,lign.CODE_NOMENCLATURE_BUDGETAIRE FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire lign ON lign.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = titre.ETAPE_DOUBLE_COMMANDE_ID JOIN budgetaire_mouvement_depense mvt ON dc.MOUVEMENT_DEPENSE_ID=mvt.MOUVEMENT_DEPENSE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;


      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);
      
      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }
        
        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number=$row->NUMERO_BON_ENGAGEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a  title='' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
            }
          }
        }

        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);
        
        $ETAPE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 9) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 8) . '...<a class="btn-sm" title="'.$row->DESC_ETAPE_DOUBLE_COMMANDE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_ETAPE_DOUBLE_COMMANDE;

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
        $sub_array[] = $row->TITRE_DECAISSEMENT;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;
        $sub_array[] = $row->DESC_MOUVEMENT_DEPENSE;
        $sub_array[] = $ETAPE;
        $action ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
        $data[] = $sub_array;
      }

      $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
      $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data,
      );

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    // Exporter la liste excel Etat d'avancement
    function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
      // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
     
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID', 'user_affectaion', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
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
         $critere1=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID;
      }

      if($SOUS_TUTEL_ID>0)
      {
        $critere3=" AND ptba.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      }

      $requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dc.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,titre.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,mvt.DESC_MOUVEMENT_DEPENSE,lign.CODE_NOMENCLATURE_BUDGETAIRE FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache tache ON tache.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON tache.PTBA_TACHE_ID=ptba.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire lign ON lign.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = titre.ETAPE_DOUBLE_COMMANDE_ID JOIN budgetaire_mouvement_depense mvt ON dc.MOUVEMENT_DEPENSE_ID=mvt.MOUVEMENT_DEPENSE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND ptba.INSTITUTION_ID IN (".$ID_INST.")".$critere1.$critere3.$critere4.$critere5;

        $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('C1', 'ETAT D\'AVANCEMENT');
        $sheet->setCellValue('A3', 'BON ENGAGEMENT');
        $sheet->setCellValue('B3', 'TITRE DECAISSEMENT');
        $sheet->setCellValue('C3', 'IMPUTATION');
        $sheet->setCellValue('D3', 'TACHE');
        $sheet->setCellValue('E3', 'ETAT D\'EXECUTION');
        $sheet->setCellValue('F3', 'ETAPE');
 
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

          $sheet->setCellValue('A' . $rows, $key->NUMERO_BON_ENGAGEMENT);
          $sheet->setCellValue('B' . $rows, $key->TITRE_DECAISSEMENT);
          $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
          $sheet->setCellValue('D' . $rows, trim($task_items));
          $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
          $sheet->setCellValue('E' . $rows, $key->DESC_MOUVEMENT_DEPENSE);
          $sheet->setCellValue('F' . $rows, $key->DESC_ETAPE_DOUBLE_COMMANDE);
         
          $rows++;
          $i++;
        } 

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('etat_avancement'.$code.'.xlsx');

      return redirect('double_commande_new/Etat_avancement');
    }

    //selectionner les etapes
    public function get_etape()
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
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

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $html='<option value="">Sélectionner</option>';
      $MOUVEMENT_DEPENSE_ID=$this->request->getPost('MOUVEMENT_DEPENSE_ID');
      if(!empty($MOUVEMENT_DEPENSE_ID))
      {
        $etape = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID ,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','PROFIL_ID='.$profil_id.' AND MOUVEMENT_DEPENSE_ID='.$MOUVEMENT_DEPENSE_ID,'DESC_ETAPE_DOUBLE_COMMANDE ASC');
        $get_etape = $this->ModelPs->getRequete($callpsreq, $etape);
      }
      
      foreach($get_etape as $key)
      {
        $html.= "<option value='".$key->ETAPE_DOUBLE_COMMANDE_ID ."'>".$key->DESC_ETAPE_DOUBLE_COMMANDE."</option>";
      }
      $output = array('status' => TRUE ,'html' => $html);
      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //get les detail des taches
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

    $requetedebase="SELECT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,
    task.EXECUTION_BUDGETAIRE_ID,
    task.PTBA_TACHE_ID,
    task.MONTANT_ENG_BUDGETAIRE,
    task.MONTANT_ENG_BUDGETAIRE_DEVISE,
    task.MONTANT_ENG_JURIDIQUE,
    task.MONTANT_ENG_JURIDIQUE_DEVISE,
    task.MONTANT_LIQUIDATION,
    task.MONTANT_LIQUIDATION_DEVISE,
    task.MONTANT_ORDONNANCEMENT,
    task.MONTANT_ORDONNANCEMENT_DEVISE,
    ptba.DESC_TACHE,
    task.QTE,
    dev.DEVISE_TYPE_ID,
    dev.DESC_DEVISE_TYPE,
    ebtd.ETAPE_DOUBLE_COMMANDE_ID 
    FROM execution_budgetaire_execution_tache task 
    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID 
    JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID 
    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
    JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID
    WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$task_id." GROUP BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID";

    // print_r($requetedebase);die();

    $order_column=array('ptba.DESC_TACHE','task.COMMENTAIRE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.COMMENTAIRE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

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

      $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
      $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");

      $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE);
      $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,0,","," ");

      $LIQUIDATION=floatval($row->MONTANT_LIQUIDATION);
      $LIQUIDATION=number_format($LIQUIDATION,0,","," ");

      $MONTANT_ORDONNANCEMENT=floatval($row->MONTANT_ORDONNANCEMENT);
      $MONTANT_ORDONNANCEMENT=number_format($MONTANT_ORDONNANCEMENT,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");

        $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE_DEVISE);
        $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,4,","," ");

        $LIQUIDATION=floatval($row->MONTANT_LIQUIDATION_DEVISE);
        $LIQUIDATION=number_format($LIQUIDATION,4,","," ");

        $MONTANT_ORDONNANCEMENT=floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
        $MONTANT_ORDONNANCEMENT=number_format($MONTANT_ORDONNANCEMENT,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
      $sub_array[] = $MONTANT_ENG_JURIDIQUE;
      $sub_array[] = $LIQUIDATION;
      if($row->ETAPE_DOUBLE_COMMANDE_ID >= 16 ){$sub_array[] = $MONTANT_ORDONNANCEMENT;}
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

?>