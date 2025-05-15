<?php
/**
 * Auteur: NIYONGABO CLAUDE
 * email: claude@mediabox.bi
 
 * téléphone: +257 69 64 13 75
 * date 13.01.2025 15:12
 */

namespace  App\Modules\double_commande_new\Controllers;  
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 20000);
ini_set('memory_limit','4048M');
class Liste_croisement_ptba_ptba_revise extends BaseController
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
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }
  
  /**
   * renvoie la vue qui va afficher la liste
   */
  public function index ()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }

    $count_data_croisement= $this->count_croisement();
    $data['nbre_tache']=$count_data_croisement['nbre_tache'];
    $data['nbre_tache_revise']=$count_data_croisement['nbre_tache_revise'];
    $data['nbre_tache_trouves']=$count_data_croisement['nbre_tache_trouves'];
    $data['nbre_tache_non_trouves']=$count_data_croisement['nbre_tache_non_trouves'];


    return view('App\Modules\double_commande_new\Views\Liste_croisement_ptba_ptba_revise_View', $data);
  }

  /**
   * afficher les donnees de la liste
   */
  public function listing ()
  {
    $session  = \Config\Services::session();
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) 
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column = ['','ptba_tache.`CODE_NOMENCLATURE_BUDGETAIRE`','ptba_tache.`DESC_TACHE`','ptba_tache_revise.DESC_TACHE'];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba_tache.`CODE_NOMENCLATURE_BUDGETAIRE` ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (ptba_tache.`CODE_NOMENCLATURE_BUDGETAIRE` LIKE '%$var_search%' OR ptba_tache.`DESC_TACHE` LIKE '%$var_search%' OR ptba_tache_revise.DESC_TACHE LIKE '%$var_search%' )") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase = "SELECT DISTINCT(ptba_tache.`CODE_NOMENCLATURE_BUDGETAIRE_ID`), ptba_tache.`CODE_NOMENCLATURE_BUDGETAIRE` FROM `ptba_tache` JOIN  ptba_tache_revise ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE=ptba_tache_revise.CODE_NOMENCLATURE_BUDGETAIRE WHERE ptba_tache_revise.PTBA_TACHE_ID =0";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = [];
    $u = 1;
    
    foreach ($fetch_actions as $row) 
    {
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT PTBA_TACHE_ID) AS taches FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID.", 1)'>".$nbre_task['taches']."</a></center>";

      //Nombre des tâches revise
      $count_task_rev = "SELECT COUNT(DISTINCT PTBA_TACHE_REVISE_ID) AS taches_rev FROM ptba_tache_revise WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID."' and PTBA_TACHE_ID = 0";
      $count_task_rev = 'CALL `getTable`("'.$count_task_rev.'");';
      $count_task_rev = $this->ModelPs->getRequeteOne($count_task_rev);
      $point_rev="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID.", 2)'>".$count_task_rev['taches_rev']."</a></center>";
      $dist = "";
      $sub_array = [];
      $sub_array[] = $u;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $point;
      $sub_array[] = $point_rev;
      $bouton = "<a class='btn btn-primary btn-sm' title='Modifier' href='".base_url("double_commande_new/Croisement_Tache/get_view/".MD5($row->CODE_NOMENCLATURE_BUDGETAIRE_ID))."' ><span class='fa fa-edit'></span></a>";
      $sub_array[] = $bouton;
      $data[] = $sub_array;
      $u++;
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

  function detail_task()
  {
    $session  = \Config\Services::session();

    $code = $this->request->getPost('code');
    $provenance = $this->request->getPost('provenance');//1=tache, 2=revise


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

    $requetedebase1="SELECT DESC_TACHE, CODE_NOMENCLATURE_BUDGETAIRE_ID, PTBA_TACHE_ID FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$code."'";
    $requetedebase2="SELECT DESC_TACHE, CODE_NOMENCLATURE_BUDGETAIRE_ID, PTBA_TACHE_REVISE_ID FROM ptba_tache_revise WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$code."' and PTBA_TACHE_ID = 0";

    $requetedebase = $provenance == 1 ? $requetedebase1 : $requetedebase2;

    $order_column=array('DESC_TACHE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_TACHE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_TACHE LIKE '%$var_search%')"):'';

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

      $action='';
      $sub_array = array();
      $sub_array[] = $row->DESC_TACHE;
      $bouton= $provenance == 1 ? "<a class='btn btn-primary btn-sm' title='Modifier' href='".base_url("double_commande_new/Modifier_Tache/".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID."/".$row->PTBA_TACHE_ID)."' ><span class='fa fa-edit'></span></a>" : "<a class='btn btn-primary btn-sm' title='Modifier' href='".base_url("double_commande_new/Modifier_Tache/revise/".$row->CODE_NOMENCLATURE_BUDGETAIRE_ID."/".$row->PTBA_TACHE_REVISE_ID)."' ><span class='fa fa-edit'></span></a>";
      $sub_array[] = $bouton;
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
}
?>