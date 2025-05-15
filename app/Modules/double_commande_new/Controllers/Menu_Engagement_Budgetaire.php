<?php
/*
*Alain Charbel Nderagakura
*Titre: Liste Engagement budgetaire deja fait
*Numero de telephone: (+257) 62003522
*WhatsApp: (+257) 76887837
*Email: charbel@mediabox.bi
*Date: 7 novembre,2023
* Ameliorer par SONIA Munezero
* +25765165772
* sonia@mediabox.bi
* le 22/01/2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');

class Menu_Engagement_Budgetaire extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();

    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT CONCAT(@@sql_mode,',ONLY_FULL_GROUP_BY'))";
    $db->query($sql);
  }

  //Interface de la liste des activites sans numero du bon d'engagement
  function get_sans_bon_engagement()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id ='';
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_SANS_BON')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $eng_budg=$this->count_engag_budg_new();
    $data['SBE']=$eng_budg['SBE'];
    $data['EBF']=$eng_budg['EBF'];
    $data['EBAV']=$eng_budg['EBAV'];
    $data['EBDV']=$eng_budg['EBDV'];
    $data['EBCorr']=$eng_budg['EBCorr'];
    $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
    $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
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
    return view('App\Modules\double_commande_new\Views\Eng_Budg_Sans_BE_View',$data);
  }

  function listing_sans_be()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_SANS_BON')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

      //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = 2;
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND exec.USER_ID='.$user_id;
    }
    $critere2=' AND titre.ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.$cond_user_id;
    $critere3="";
      //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else
    {
      $critere1=" AND exec.INSTITUTION_ID IN(".$ID_INST.")";
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3="AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

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
    $group = "";

    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND titre.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID;

    $order_column=array('ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);


    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_DEMANDE ASC';

      //$search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;

      // print_r($critaire);die();
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter=$critaire." ".$search." ". $group ." ". $order_by . " " . $limit;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }  
        }
      }

        //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE; 
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $bouton.'&nbsp;'."<a href='javascript:void(0)' class='btn btn-primary btn-sm' onclick='rejeter(".$row->EXECUTION_BUDGETAIRE_ID.",".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' style='background: #556B2F;' title='Rejeter'><span class='fa fa-trash'></span></a>";
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

      $requetedebase="SELECT DISTINCT(task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID),task.EXECUTION_BUDGETAIRE_ID,task.PTBA_TACHE_ID,task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,ptba.DESC_TACHE,task.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$task_id."";

      $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.DESC_TACHE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

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
        $DESC_TACHE=(mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 7) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
          $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $DESC_TACHE;
        $sub_array[] = $row->QTE;
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
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

    //Interface de la liste des engagements a corriger
    function index_A_Corr()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CORRECTION')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
      $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      return view('App\Modules\double_commande_new\Views\Eng_Budg_Corr_View',$data);
    }

    //fonction pour affichage d'une liste des engagements a corriger
    public function listing_A_Corrige()
    {
      $session  = \Config\Services::session();

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CORRECTION')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND exec.USER_ID='.$user_id;
      }
      $critere1="";
      $critere3="";
      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.',';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1="";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
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

      $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=4 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$cond_user_id."";  

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'devis.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter=$critaire." ".$search." ". $group;

      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton="<a class='btn btn-info btn-sm' title='Traiter'><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if(!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              if($row->EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID == 1)
              {
                $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

                $bouton= "<a style='color:#fbbf25;' style='background:#061e69;' class='btn btn-info btn-sm' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
              }
              else
              {
                $number = "<a title='Traiter' style='color:#fbbf25;' href='" . base_url('double_commande_new/Introduction_Budget_Multi_Taches/corrige_etape1/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'>" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

                $bouton= "<a style='color:#fbbf25;' style='background:#061e69;' class='btn btn-info btn-sm' title='Traiter' href='".base_url('double_commande_new/Introduction_Budget_Multi_Taches/corrige_etape1/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
              }
            }  
          }
        }


        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
          $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;               
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $ENG_BUDGETAIRE;
        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
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

    //annuler/rejeter les engagements sans bon
    public function annuler_sans_bon($EXECUTION_BUDGETAIRE_ID='',$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='')
    {
      if (!empty($EXECUTION_BUDGETAIRE_ID))
      {
        $table="execution_budgetaire_titre_decaissement";
        $datatomodifie="ETAPE_DOUBLE_COMMANDE_ID=42";
        $conditions="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $this->update_all_table($table,$datatomodifie,$conditions);

        $tableExec="execution_budgetaire";
        $datatomodifieExec="ENG_BUDGETAIRE=0,ENG_BUDGETAIRE_DEVISE=0";
        $conditionsExec="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
        $this->update_all_table($tableExec,$datatomodifieExec,$conditionsExec);

        $this->gestion_rejet_ptba($EXECUTION_BUDGETAIRE_ID);
        $data=['message' => "".lang('messages_lang.mess_rejet_effectue').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement');
      }
      else
      {
        $data=['message' => "".lang('messages_lang.mess_rejet_echoue').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement');
      }
    }
    
    //Interface de la liste des eng deja faits
    function index_deja_fait($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
      $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      //get les annee budgetaire
      $annee_budgetaire_en_cours=$this->get_annee_budgetaire();
      $psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_budgetaire_en_cours,'ANNEE_BUDGETAIRE_ID ASC');
      $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);

      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
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

      $data['annee_actuel'] = $annee_budgetaire_en_cours;

      return view('App\Modules\double_commande_new\Views\Eng_Budg_Deja_Fait_View',$data);
    }


    //fonction pour affichage d'une liste des activites
    public function listing_Deja_Fait()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');
      $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $critere1="";
      $cond_user_id='';
      if($profil_id!=1)
      {
        $cond_user_id=' AND exec.USER_ID='.$user_id;
      }
      $critere2=' AND titre.ETAPE_DOUBLE_COMMANDE_ID>2 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42'.$cond_user_id;
      $critere3="";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $criteretotal=$critere2." AND exec.INSTITUTION_ID IN(".$ID_INST.")";

      if(!empty($INSTITUTION_ID))
      {  
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1="  AND exec.INSTITUTION_ID IN(".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3="AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $critere4="";
      $critere5="";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      }

      if(!empty($ANNEE_BUDGETAIRE_ID))
      {
        $critere5.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
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

      $group ="";

      $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2)";

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3." ".$critere2." ".$critere4." ".$critere5;
       // print_r($critaire);die();
      //condition pour le query principale
      $conditions ='';
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search." ". $group;

      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $requetetotal=$requetedebase." ".$criteretotal;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);
      
      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
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
              $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
            }  
          }
        }


        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
          $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;//$number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;               
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $ENG_BUDGETAIRE;
        $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
        </li>
        ";

        $action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 3) ? "
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' >
        <label>&nbsp;&nbsp;<font color='green'>Correction</font></label>
        </a>
        </li>" : "";

        $action .="
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
        <center>
        <h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
        </h5>
        </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
        <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
        ".lang('messages_lang.quiter_action')."
        </button>
        <a href='".base_url("double_commande_new/Menu_Engagement_Budgetaire/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."</a>
        </div>";

        $sub_array[] = $action;
        $data[] = $sub_array;
      }

      $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetetotal. '")');
      $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data,
      );

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //Interface de la liste des engagements a valider
    function index_A_Valide($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
      $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
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

      return view('App\Modules\double_commande_new\Views\Eng_Budg_A_Valide_View',$data);
    }

    //fonction pour affichage d'une liste des engagements a valider
    public function listing_a_valide()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }

      $ID_INST = substr($ID_INST,0,-1);
      $critere1="";
      $critere2=' AND exec.INSTITUTION_ID IN('.$ID_INST.')';
      $critere3="";
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if($_POST['length'] != -1)
      {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }

      $group = "";

      $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=3 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";


      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if(!empty($getProfil))
        {
          foreach($getProfil as $value)
          {
            if($prof_id==$value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
            }
          }
        }

        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
          $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $ENG_BUDGETAIRE;

        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
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

    //Interface de la liste des engagements deja valide
    function index_Deja_Valide($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      $ced=$session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED');
      $gdc=$session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //get les annee budgetaire
      $annee_budgetaire_en_cours=$this->get_annee_budgetaire();
      $psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_budgetaire_en_cours,'ANNEE_BUDGETAIRE_ID ASC');
      $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);

      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
      $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      $data['annee_actuel'] = $annee_budgetaire_en_cours;
      return view('App\Modules\double_commande_new\Views\Eng_Budg_Deja_Valide_View',$data);
    }

    //fonction pour affichage d'une liste des engagements deja valider
    public function listing_Deja_Valide()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');
      $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }

      $ID_INST = substr($ID_INST,0,-1);

      $critere1="";
      $critere2=" ";
      $critere3="";
      $critere4="";
      $critere5="";
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      }

      if(!empty($ANNEE_BUDGETAIRE_ID))
      {
        $critere5.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if($_POST['length'] != -1)
      {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }

      $group = "";
      $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 5 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

      $order_by = '';
      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE  LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3." ".$critere4." ".$critere5;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebases=$requetedebase." ".$conditions;

      // print_r($requetedebases);die();

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {

        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
          $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;               
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $ENG_BUDGETAIRE;
        $action2 ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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




    


    // Exporter l aliste excel des engagements budgetaires déjà faits
    function exporter_Excel_deja_fait($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
    {
      // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
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

    if($ANNEE_BUDGETAIRE_ID > 0)
    {
      $critere5.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }

    $group = "";
    $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire exec JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.")".$critere1.$critere3.$critere4.$critere5.$group;

    $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C1', 'ENGAGEMENTS BUDGETAIRES DEJA FAITS');
    $sheet->setCellValue('A3', '#');
    $sheet->setCellValue('B3', 'BON ENGAGEMENT');
    $sheet->setCellValue('C3', 'IMPUTATION');
        // $sheet->setCellValue('D3', 'ACTIVITE');
    $sheet->setCellValue('D3', 'TACHE');
        // $sheet->setCellValue('F3', 'OBJET ENGAGEMENT');
    $sheet->setCellValue('E3', 'DEVISE');
    $sheet->setCellValue('F3', 'ENGAGEMENT BUDGETAIRE');

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

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
          // $sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
      $sheet->setCellValue('D' . $rows, trim($task_items));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
          // $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('F' . $rows, $key->ENG_BUDGETAIRE);

      $rows++;
      $i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Eng_bugdetaire_deja_fait'.$code.'.xlsx');

    return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait');
  }


  // Exporter l aliste excel des engagements budgetaires déjà valide
  function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
  {
    // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $session  = \Config\Services::session();
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
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

  if($ANNEE_BUDGETAIRE_ID > 0)
  {
    $critere5.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
  }

  $group = "";
  $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),
  titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
  dev.DEVISE_TYPE_ID,
  dev.DESC_DEVISE_TYPE,
  exec.ENG_BUDGETAIRE,
  exec.ENG_BUDGETAIRE_DEVISE,
  ligne.CODE_NOMENCLATURE_BUDGETAIRE,
  ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
  titre.ETAPE_DOUBLE_COMMANDE_ID,
  exec.NUMERO_BON_ENGAGEMENT 
  FROM execution_budgetaire exec 
  JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
  JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID 
  JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID
  JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
  JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID  
  WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 5 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.")".$critere1.$critere3.$critere4.$critere5.$group;

  $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('C1', 'ENGAGEMENTS BUDGETAIRES DEJA VALIDES');
  $sheet->setCellValue('A3', '#');
  $sheet->setCellValue('B3', 'BON ENGAGEMENT');
  $sheet->setCellValue('C3', 'IMPUTATION');
          // $sheet->setCellValue('D3', 'ACTIVITE');
  $sheet->setCellValue('D3', 'TACHE');
          // $sheet->setCellValue('F3', 'OBJET ENGAGEMENT');
  $sheet->setCellValue('E3', 'DEVISE');
  $sheet->setCellValue('F3', 'ENGAGEMENT BUDGETAIRE');
  $sheet->setCellValue('C1', ''.str_replace('&nbsp;', ' ', lang('messages_lang.liste_budg_dejval')).'');

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

    $sheet->setCellValue('A' . $rows, $i);
    $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
    $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
          // $sheet->setCellValue('D' . $rows, $key->DESC_PAP_ACTIVITE);
    $sheet->setCellValue('D' . $rows, trim($task_items));
    $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
          // $sheet->setCellValue('F' . $rows, $key->COMMENTAIRE);
    $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
    $sheet->setCellValue('F' . $rows, $key->ENG_BUDGETAIRE);

    $rows++;
    $i++;
  } 

  $code=date('YmdHis');
  $writer = new Xlsx($spreadsheet);
  $writer->save('world.xlsx');
  return $this->response->download('world.xlsx', null)->setFileName('Eng_bugdetaire'.$code.'.xlsx');

  return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide');
}

    // EXporter un pdf des engagements budgetaires deja valides
function generatePdf($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
{
  $dompdf = new Dompdf();
  $html="<html><body>";
  $html.="<center><b>".lang('messages_lang.liste_budg_dejval')."</b></center><br><br>";

  $db = db_connect();
  $session  = \Config\Services::session();
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if(empty($user_id))
  {
    return redirect('Login_Ptba/do_logout');
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
  $critere1="";
  $critere2=" ";
  $critere3="";
  $critere4="";
  $critere5="";
  $nom_institution="";
  $nom_sous_titre="";
  if($INSTITUTION_ID>0)
  {
    $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
    $inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
    $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

    $nom_institution=$instt['DESCRIPTION_INSTITUTION'];
    $html.="<div style='font-size:10px;'>".$nom_institution."</div>";
  }

  if($SOUS_TUTEL_ID>0)
  {
    $critere3=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
    $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

    $nom_sous_titre=$instt['DESCRIPTION_SOUS_TUTEL'];
    $html.="<div style='font-size:10px;'>".$nom_sous_titre."</div>";
  }

  if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
  {
    $critere4.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
  }

  if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
  {
    $critere5.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
  }

  if($ANNEE_BUDGETAIRE_ID > 0)
  {
    $critere5.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
  }

  $institution=" AND ptba_tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID=".$user_id.")";

  $html.="

  <table cellspacing='0'>
  <tr>
  <th style='font-size:8px; border: 1px solid black'>#</th>
  <th style='font-size:8px; border: 1px solid black'>BON D'ENGAGEMENT</th>
  <th style='font-size:8px; border: 1px solid black'>IMPUTATION</th>
  <th style='font-size:8px; border: 1px solid black'>TACHE</th>

  <th style='font-size:8px; border: 1px solid black'>DEVISE</th>
  <th style='font-size:8px; border: 1px solid black'>ENGAGEMENT BUDGETAIRE</th>

  </tr>";

  $group = "";
  $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),
  titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
  dev.DEVISE_TYPE_ID,
  dev.DESC_DEVISE_TYPE,
  exec.ENG_BUDGETAIRE,
  exec.ENG_BUDGETAIRE_DEVISE,
  ligne.CODE_NOMENCLATURE_BUDGETAIRE,
  ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
  titre.ETAPE_DOUBLE_COMMANDE_ID,
  exec.NUMERO_BON_ENGAGEMENT
  FROM execution_budgetaire_execution_tache exec_tache
  JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID
  JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID = exec_tache.PTBA_TACHE_ID
  JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID
  LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID = ptba_tache.PAP_ACTIVITE_ID
  JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID
  JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID
  JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID
  WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 5 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1.$critere3.$critere4.$critere5." AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.")".$group;

  $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 
  $i=1;

  foreach ($getData as $value) {
    $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$value->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $get_task = 'CALL `getTable`("'.$get_task.'");';
    $tasks = $this->ModelPs->getRequete($get_task);
    $task_items = '';

    foreach ($tasks as $task) {
      $task_items .= '<li style="margin-left:+10px">'.$task->DESC_TACHE.'</li>';
    }

    $ENG_BUDGETAIRE=number_format($value->ENG_BUDGETAIRE,'4',',',' ');

    $TACHE=empty($task_items) ? '<center>-</center>' : $task_items;

    $html.="
    <tr>
    <td style='font-size:8px; border: 1px solid black'>".$i."</td>
    <td style='font-size:8px; border: 1px solid black'>".$value->NUMERO_BON_ENGAGEMENT."</td>
    <td style='font-size:8px; border: 1px solid black'>".$value->CODE_NOMENCLATURE_BUDGETAIRE."</td>
    <td style='font-size:8px; border: 1px solid black'>".$TACHE."</td>
    <td style='font-size:8px; border: 1px solid black'>".$value->DESC_DEVISE_TYPE."</td>
    <td style='font-size:8px; border: 1px solid black'>".$ENG_BUDGETAIRE."</td>
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
  $name_file = 'Eng_bugdetaire'.uniqid().'.pdf';
          // $fichier='uploads/double_commande/PIECEJUSTIFICATIVE'.uniqid();
  $PATH_PIECE_JUSTIFICATIVE = 'uploads/double_commande/'.$name_file;

  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="Eng_bugdetaire'.uniqid().'.pdf"');

  echo $dompdf->output();
}

  //Interface de la liste des engagements REJETER
function rejete_interface()
{
  $data = $this->urichk();
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
  $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
  $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER');

  $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
  if(empty($user_id))
  {
    return redirect('Login_Ptba/do_logout');
  }

  if($gdc!=1 AND $ced!=1)
  {
    return redirect('Login_Ptba/homepage'); 
  }

  $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
  $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
  $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
  $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
  $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

  $ID_INST='';
  foreach ($getaffect as $value)
  {
    $ID_INST.=$value->INSTITUTION_ID.' ,';           
  }
  $ID_INST = substr($ID_INST,0,-1);

  $eng_budg=$this->count_engag_budg_new();
  $data['SBE']=$eng_budg['SBE'];
  $data['EBF']=$eng_budg['EBF'];
  $data['EBAV']=$eng_budg['EBAV'];
  $data['EBDV']=$eng_budg['EBDV'];
  $data['EBCorr']=$eng_budg['EBCorr'];
  $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
  $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

  $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
  $getInst = "CALL getTable('" .$getInst. "');";
  $data['institutions'] = $this->ModelPs->getRequete($getInst);

  return view('App\Modules\double_commande_new\Views\Eng_Budg_Rejet_List_View',$data);
}

  //fonction pour affichage d'une liste des engagements rejetter
public function listing_eng_rejette()
{
  $session  = \Config\Services::session();
  $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
  $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER');

  if($gdc!=1 AND $ced!=1)
  {
    return redirect('Login_Ptba/homepage'); 
  }

      //Filtres de la liste
  $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
  $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
  $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
  $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
  $critere1="";
  $critere3="";
  $callpsreq = "CALL getRequete(?,?,?,?);";
      //selection les institution de la personne connectee
  $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
  $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

  $ID_INST='';
  foreach ($getaffect as $value)
  {
    $ID_INST.=$value->INSTITUTION_ID.',';           
  }
  $ID_INST = substr($ID_INST,0,-1);

  if(!empty($INSTITUTION_ID))
  {
    $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
  }
  else
  {
    $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
  }

  if(!empty($SOUS_TUTEL_ID))
  {
    $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
  }

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

  $group = "";

  $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=5 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

  $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

  $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

  $critaire = $critere1." ".$critere3;
      //condition pour le query principale
  $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
  $conditionsfilter = $critaire . " ". $search ." " . $group;


  $requetedebases=$requetedebase." ".$conditions;

  $requetedebasefilter=$requetedebase." ".$conditionsfilter;

  $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

  $fetch_actions = $this->ModelPs->datatable($query_secondaire);

  $data = array();
  $u=1;
  foreach ($fetch_actions as $row)
  {
    $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
    $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
    $getEtape = "CALL getTable('" . $getEtape . "');";
    $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
    $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;


    $number=$row->NUMERO_BON_ENGAGEMENT;
    $bouton="<a class='btn btn-info btn-sm' title='Traiter'><span class='fa fa-arrow-up'></span></a>";

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if(!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."")."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

          $bouton= "<a style='color:#fbbf25;' style='background:#061e69;' class='btn btn-info btn-sm' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."")."' ><span class='fa fa-arrow-up'></span></a>";
        }  
      }
    }


        //Nombre des tâches
    $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
    $count_task = 'CALL `getTable`("'.$count_task.'");';
    $nbre_task = $this->ModelPs->getRequeteOne($count_task);

    $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
    $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
    if($row->DEVISE_TYPE_ID!=1)
    {
      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
    }

    $action='';
    $sub_array = array();
    $sub_array[] = $number;
    $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
    $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
    $sub_array[] = $point;               
    $sub_array[] = $row->DESC_DEVISE_TYPE;
    $sub_array[] = $ENG_BUDGETAIRE;
    $action1 ='<div class="row dropdown" style="color:#fff;">
    "'.$bouton.'"';
    $action ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
    $sub_array[] = $action;
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //Interface de la liste des engagements REJETER Fin
    function rejete_fin()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1 AND $ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];
      $data['nbr_fin_rej']=$eng_budg['nbr_fin_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      
      return view('App\Modules\double_commande_new\Views\Liste_Fin_Rejet_View',$data);
    }

    //fonction pour affichage d'une liste des engagements rejetter
    public function listing_fin_rejette()
    {
      $session  = \Config\Services::session();
      $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER');

      if($gdc!=1 AND $ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      $critere1="";
      $critere3="";
      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.',';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

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

      $group = "";
      $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";


      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;  

      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        //Nombre des tâches
        $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
        $count_task = 'CALL `getTable`("'.$count_task.'");';
        $nbre_task = $this->ModelPs->getRequeteOne($count_task);

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
        if($row->DEVISE_TYPE_ID!=1)
        {
          $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
          $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
        }

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
        $sub_array[] = $point;               
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = $ENG_BUDGETAIRE;
        $action ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;

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

      return $this->response->setJSON($output);//echo json_encode($output);
    }   

    public function get_soutut()
    {
      $callpsreq = "CALL getRequete(?,?,?,?);";
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

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $html='<option value="">Sélectionner</option>';
      $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
      if(!empty($INSTITUTION_ID))
      {
        $st = $this->getBindParms('SOUS_TUTEL_ID ,DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','INSTITUTION_ID='.$INSTITUTION_ID,'DESCRIPTION_SOUS_TUTEL ASC');
        $get_st = $this->ModelPs->getRequete($callpsreq, $st);
        foreach($get_st as $key)
        {
          $html.= "<option value='".$key->SOUS_TUTEL_ID ."'>".$key->DESCRIPTION_SOUS_TUTEL."</option>";
        }
      }
      
      $output = array('status' => TRUE ,'html' => $html);
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

    public function update_all_table($table,$datatomodifie,$conditions)
    {
      $bindparams =[$table,$datatomodifie,$conditions];
      $updateRequete = "CALL `updateData`(?,?,?);";
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
    }

    //fonction pour la correction
    function is_correctionold17092024($EXECUTION_BUDGETAIRE_DETAIL_ID)
    {
      $session  = \Config\Services::session();
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba');
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $bind_detail_etape = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail', 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID ASC');
      $etape= $this->ModelPs->getRequeteOne($callpsreq, $bind_detail_etape);

      $datatoupdate='';
      if($etape['ETAPE_DOUBLE_COMMANDE_ID']== 3)
      {
        $datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=4';
      }
      else{
        return redirect('Login_Ptba/homepage');
      }

      $updateTable='execution_budgetaire_tache_detail';
      $critere = " EXECUTION_BUDGETAIRE_DETAIL_ID=".$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $this->update_all_table($updateTable,$datatoupdate,$critere);
      $this->gestion_rejet_ptba($etape['EXECUTION_BUDGETAIRE_ID']);
      $url = 'double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr';
      return redirect($url);
    }

    function is_correction($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
    {
      $session  = \Config\Services::session();
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba');
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $bind_detail_etape = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_titre_decaissement', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,'EXECUTION_BUDGETAIRE_ID ASC');
      $etape= $this->ModelPs->getRequeteOne($callpsreq, $bind_detail_etape);

      $datatoupdate='';
      if($etape['ETAPE_DOUBLE_COMMANDE_ID']== 3)
      {
        $datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=4';
      }
      else{
        return redirect('Login_Ptba/homepage');
      }

      $updateTable='execution_budgetaire_titre_decaissement';
      $critere = " EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $this->update_all_table($updateTable,$datatoupdate,$critere);
      $this->gestion_rejet_ptba($etape['EXECUTION_BUDGETAIRE_ID']);
      $url = 'double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr';
      return redirect($url);
    }
  }
  ?>
