<?php
/**
 * Auteur: Baleke kahamire Bonheur
 * email: bonheur.baleke@mediabox.bi
 * whatsapp: +257 67 86 62 83
 * date 15.02.2024 15:12
 */

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liste_Trans_PC extends BaseController
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
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuOrdonnancement();
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];

    $data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_spe']=$data_titre['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_titre['get_bord_deja_spe'];
    return view('App\Modules\double_commande_new\Views\Liste_Trans_PC_View', $data);
  }

  /**
   * afficher les donnees de la liste
   */
  public function listing ()
  {
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
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
    $order_column = ['exec.NUMERO_BON_ENGAGEMENT','dev.DESC_DEVISE_TYPE','det.MONTANT_ORDONNANCEMENT','inst.DESCRIPTION_INSTITUTION'];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR det.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase = "SELECT dev.DEVISE_TYPE_ID, dev.DESC_DEVISE_TYPE,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID = det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID = 16";
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
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $row->DEVISE_TYPE_ID == 1 ? number_format($row->MONTANT_ORDONNANCEMENT, 4, ',', ' ') : number_format($row->MONTANT_ORDONNANCEMENT_DEVISE, 4, ',', ' ');
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
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
}
?>