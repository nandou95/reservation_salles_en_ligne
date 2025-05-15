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

class Liste_Trans_Deja_Fait_PC extends BaseController
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

    if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');

    $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
      $user_inst_res = 'CALL getTable("'.$user_inst.'");';
      $institutions_user = $this->ModelPs->getRequete($user_inst_res);

    $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
    $SOUS_TUTEL_ID = 0;
    $DU = 0;
    $AU = 0;

    $data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU);
    $data['institutions_user']=$data_menu['institutions_user'];
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
    $data['get_bord_spe']=$data_menu['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
    $data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
    $data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
    $data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];
    $data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];

    $data['first_element_id'] = $INSTITUTION_ID;

    return view('App\Modules\double_commande_new\Views\Liste_Trans_Deja_Fait_PC_View', $data);
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

    if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    // $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';
    $order_column=['ebbtbt.NUMERO_DOCUMENT','det.MONTANT_ORDONNANCEMENT','inst_institutions.DESCRIPTION_INSTITUTION','typ.DESC_DEVISE_TYPE',1];
    $order_by=isset($_POST['order'])?' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir']:' ORDER BY ebbtn.BORDEREAU_TRANSMISSION_ID ASC';

    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');

    if(!empty($INSTITUTION_ID))
    {
      $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.')';
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.')';
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $institution.=" AND det.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $institution.=" AND det.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND det.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
    }

    $search = !empty($_POST['search']['value']) ?  (" AND (ebbtbt.NUMERO_DOCUMENT LIKE '%$var_search%' OR det.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR typ.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase = "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),ebbtn.NUMERO_BORDEREAU_TRANSMISSION,
                             ebbtbt.BORDEREAU_TRANSMISSION_ID,
                             ebbtbt.NUMERO_DOCUMENT,
                             det.MONTANT_ORDONNANCEMENT,
                             det.MONTANT_ORDONNANCEMENT_DEVISE,
                             inst_institutions.DESCRIPTION_INSTITUTION,
                             typ.DESC_DEVISE_TYPE,
                             typ.DEVISE_TYPE_ID
                      FROM execution_budgetaire_bordereau_transmission ebbtn 
                      JOIN execution_budgetaire_bordereau_transmission_bon_titre ebbtbt ON ebbtbt.BORDEREAU_TRANSMISSION_ID = ebbtn.BORDEREAU_TRANSMISSION_ID 
                      JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebbtbt.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID 
                      JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
                      JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = ebtd.EXECUTION_BUDGETAIRE_ID 
                      LEFT JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
                      JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID
                      JOIN inst_institutions ON tache.INSTITUTION_ID = inst_institutions.INSTITUTION_ID 
                      LEFT JOIN devise_type_hist ON det.DEVISE_TYPE_HISTO_LIQUI_ID = devise_type_hist.DEVISE_TYPE_HISTO_ID 
                      JOIN devise_type typ ON typ.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                      WHERE ebbtbt.TYPE_DOCUMENT_ID = 1 AND ebbtbt.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID = 1".$institution;
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
      $sub_array[] = $row->NUMERO_DOCUMENT;
      $sub_array[] = $row->NUMERO_BORDEREAU_TRANSMISSION;
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