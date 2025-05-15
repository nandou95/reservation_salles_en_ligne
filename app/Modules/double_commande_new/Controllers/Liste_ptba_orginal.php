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
class Liste_ptba_orginal extends BaseController
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



    return view('App\Modules\double_commande_new\Views\Liste_ptba_orginal_View', $data);
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
    $order_column = ['','prog.INTITULE_PROGRAMME','actions.LIBELLE_ACTION','ligne.CODE_NOMENCLATURE_BUDGETAIRE','activite.DESC_PAP_ACTIVITE','ptba_tache.`DESC_TACHE`',1,1,1,1,1,1,1,1];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.`CODE_NOMENCLATURE_BUDGETAIRE` LIKE '%$var_search%' OR ptba.`DESC_TACHE` LIKE '%$var_search%' OR prog.INTITULE_PROGRAMME LIKE '%$var_search%' OR actions.LIBELLE_ACTION LIKE '%$var_search%' )") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase = "SELECT ptba.PTBA_TACHE_ID,ptba.DESC_TACHE, prog.INTITULE_PROGRAMME, actions.LIBELLE_ACTION, ligne.CODE_NOMENCLATURE_BUDGETAIRE, activite.DESC_PAP_ACTIVITE, ptba.QT1,ptba.QT2, ptba.QT3, ptba.QT4,ptba.BUDGET_T1,ptba.BUDGET_T2,ptba.BUDGET_T3,ptba.BUDGET_T4 FROM ptba_tache ptba LEFT JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID LEFT JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID LEFT JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites activite ON activite.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID WHERE 1";
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
      
      $sub_array[] = empty($row->DESC_PAP_ACTIVITE) ? "-" : $row->DESC_PAP_ACTIVITE ;
      $sub_array[] = $row->DESC_TACHE;
      $sub_array[] = $row->QT1;
      $sub_array[] = $row->QT2;
      $sub_array[] = $row->QT3;
      $sub_array[] = $row->QT4;
      $sub_array[] = number_format($row->BUDGET_T1,$this->get_precision($row->BUDGET_T1),'.',' ');
      $sub_array[] = number_format($row->BUDGET_T2,$this->get_precision($row->BUDGET_T2),'.',' ');
      $sub_array[] = number_format($row->BUDGET_T3,$this->get_precision($row->BUDGET_T3),'.',' ');
      $sub_array[] = number_format($row->BUDGET_T4,$this->get_precision($row->BUDGET_T4),'.',' ');
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


    private function get_precision($value=0)
  {
    $string = strval($value);
    $number=explode('.',$string)[1] ?? '';
    $precision='';
    for($i=1;$i<=strlen($number);$i++)
    {
      $precision=$i;
    }
    if(!empty($precision)) 
    {
      return $precision;
    }
    else
    {
      return 0;
    }    
  }

}
?>