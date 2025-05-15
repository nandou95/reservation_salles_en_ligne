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
class Liste_tache_trouve extends BaseController
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


    return view('App\Modules\double_commande_new\Views\Liste_tache_trouve_View', $data);
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
    $order_column = ['','prog.INTITULE_PROGRAMME','actions.LIBELLE_ACTION','ligne.CODE_NOMENCLATURE_BUDGETAIRE','pba.DESC_TACHE','revise.DESC_TACHE','ptba_tache.`DESC_TACHE`','ptba_tache_revise.DESC_TACHE'];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.`CODE_NOMENCLATURE_BUDGETAIRE` LIKE '%$var_search%' OR ptba.`DESC_TACHE` LIKE '%$var_search%' OR ptba_tache_revise.DESC_TACHE LIKE '%$var_search%' )") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase = "SELECT
    prog.INTITULE_PROGRAMME,
    actions.LIBELLE_ACTION,
    ligne.CODE_NOMENCLATURE_BUDGETAIRE,
    activite.DESC_PAP_ACTIVITE ACTIVITE_ANCIEN,
    activite2.DESC_PAP_ACTIVITE ACTIVITE_REVISE,
    ptba.DESC_TACHE TACHE_ANCIENNE,
    revise.DESC_TACHE TACHE_REVISE,
    ptba.QT1 QT1_ANCIENNE,
    revise.QT1 QT1_REVISE,
    ptba.QT2 QT2_ANCIENNE,
    revise.QT2 QT2_REVISE,
    ptba.QT3 QT3_ANCIENNE,
    revise.QT3 QT3_REVISE,
    ptba.QT4 QT4_ANCIENNE,
    revise.QT4 QT4_REVISE,
    ptba.BUDGET_T1 T1_ANCIEN,
    revise.BUDGET_T1 T1_REVISE,
    ptba.BUDGET_T2 T2_ANCIEN,
    revise.BUDGET_T2 T2_REVISE,
    ptba.BUDGET_T3 T3_ANCIEN,
    revise.BUDGET_T3 T3_REVISE,
    ptba.BUDGET_T4 T4_ANCIEN,
    revise.BUDGET_T4 T4_REVISE
FROM
    ptba_tache ptba
JOIN ptba_tache_revise revise ON
    ptba.PTBA_TACHE_ID = revise.PTBA_TACHE_ID
LEFT JOIN inst_institutions_programmes prog ON
    prog.PROGRAMME_ID = ptba.PROGRAMME_ID
LEFT JOIN inst_institutions_actions actions ON
    actions.ACTION_ID = ptba.ACTION_ID
LEFT JOIN inst_institutions_ligne_budgetaire ligne ON
    ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID
LEFT JOIN pap_activites activite ON
    activite.PAP_ACTIVITE_ID = ptba.PAP_ACTIVITE_ID
LEFT JOIN pap_activites activite2 ON
    activite2.PAP_ACTIVITE_ID = revise.PAP_ACTIVITE_ID
WHERE revise.PTBA_TACHE_ID <> 0";
    $requetedebases = $requetedebase . " " . $conditions;
    
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = [];
    $u = 1;
    
    foreach ($fetch_actions as $row) 
    {
      $dist = "";
      $sub_array = [];
      $sub_array[] = $u;
      $sub_array[] = $row->INTITULE_PROGRAMME;
      $sub_array[] = $row->LIBELLE_ACTION;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = empty($row->ACTIVITE_ANCIEN) ? "-" : $row->ACTIVITE_ANCIEN ;
      $sub_array[] = empty($row->ACTIVITE_REVISE) ? "-" : $row->ACTIVITE_REVISE;
      
      $sub_array[] = $row->TACHE_ANCIENNE;
      $sub_array[] = $row->TACHE_REVISE;
      $sub_array[] = $row->QT1_ANCIENNE;
      $sub_array[] = $row->QT1_REVISE;
      $sub_array[] = $row->QT2_ANCIENNE;
      $sub_array[] = $row->QT2_REVISE;
      $sub_array[] = $row->QT3_ANCIENNE;
      $sub_array[] = $row->QT3_REVISE;
      $sub_array[] = $row->QT4_ANCIENNE;
      $sub_array[] = $row->QT4_REVISE;
      $sub_array[] = number_format($row->T1_ANCIEN,0,'',' ');
      $sub_array[] = number_format($row->T1_REVISE,0,'',' ');
      $sub_array[] = number_format($row->T2_ANCIEN,0,'',' ');
      $sub_array[] = number_format($row->T2_REVISE,0,'',' ');
      $sub_array[] = number_format($row->T3_ANCIEN,0,'',' ');
      $sub_array[] = number_format($row->T3_REVISE,0,'',' ');
      $sub_array[] = number_format($row->T4_ANCIEN,0,'',' ');
      $sub_array[] = number_format($row->T4_REVISE,0,'',' ');

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
}
?>