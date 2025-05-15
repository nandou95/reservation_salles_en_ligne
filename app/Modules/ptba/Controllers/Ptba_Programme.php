<?php 
/*
* @author MUNEZERO Sonia
* 65165772 
* +989397728740
* sonia@mediabox.bi
* 30/09/2023
* Liste des ptba des programmes
*/
namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Ptba_Programme extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  function index()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $institution = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION' ,'inst_institutions', '1', 'CODE_INSTITUTION ASC');
    $data['get_institution']= $this->ModelPs->getRequete($callpsreq, $institution);
    //Declaration des labels pour l'internalisation
    $titre_programme = lang("messages_lang.titre_programme");
    $data['titre']="$titre_programme";
    return view('App\Modules\ptba\Views\Ptba_Programme_List_View',$data);
  }

  //liste des institutions
  function liste_ptba_programme()
  {
    $session = \Config\Services::session();
    if ($session->get('SESSION_SUIVIE_PTBA_PTBA_PROGRAMMES') != 1) {
      return redirect('Login_Ptba/homepage');
    }

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $critaire = "";
    if (!empty($INSTITUTION_ID)) {
      $critaire = " AND inst.INSTITUTION_ID=" . $INSTITUTION_ID;
    }

    $var_search = $this->request->getPost('search')['value'];
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search, "'");
    $limit = 'LIMIT 0,10';

    if ($this->request->getPost('length') != -1) {
      $limit = 'LIMIT ' . $this->request->getPost("start") . ',' . $this->request->getPost("length");
    }

    $order_column = array(1, 'prog.CODE_PROGRAMME', 'prog.INTITULE_PROGRAMME', 'prog.OBJECTIF_DU_PROGRAMME', 'inst.DESCRIPTION_INSTITUTION', 1, 1, 1, 1, 1, 1);
    $order_by = isset($this->request->getPost('order')[0]['column']) ? ' ORDER BY ' . $order_column[$this->request->getPost('order')[0]['column']] . ' ' . $this->request->getPost('order')[0]['dir'] : ' ORDER BY prog.CODE_PROGRAMME ASC';

    $search = !empty($var_search) ? " AND (prog.CODE_PROGRAMME LIKE '%$var_search%' OR prog.INTITULE_PROGRAMME LIKE '%$var_search%' OR prog.OBJECTIF_DU_PROGRAMME LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%')" : '';

    $conditions = $search . $critaire . ' ' . $order_by . ' ' . $limit;

    // Condition for the query filter
    $conditionsfilter = $search . $critaire;

    $requetedebase = "SELECT prog.PROGRAMME_ID, prog.CODE_PROGRAMME, prog.OBJECTIF_DU_PROGRAMME, prog.INTITULE_PROGRAMME, inst.INSTITUTION_ID, inst.DESCRIPTION_INSTITUTION, SUM(ptba.T1) AS T1, SUM(ptba.T2) AS T2, SUM(ptba.T3) AS T3, SUM(ptba.T4) AS T4, SUM(ptba.PROGRAMMATION_FINANCIERE_BIF) AS total FROM inst_institutions_programmes prog JOIN inst_institutions inst ON prog.INSTITUTION_ID = inst.INSTITUTION_ID JOIN ptba ON ptba.PROGRAMME_ID = prog.PROGRAMME_ID WHERE 1 $conditionsfilter GROUP BY prog.PROGRAMME_ID, prog.OBJECTIF_DU_PROGRAMME, prog.CODE_PROGRAMME, prog.INTITULE_PROGRAMME, inst.INSTITUTION_ID, inst.DESCRIPTION_INSTITUTION";

    $requetedebases = $requetedebase . ' ' . $conditions;

    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';

    $fetch_cov_frais = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u = 1;
    foreach ($fetch_cov_frais as $row) {
      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $row->CODE_PROGRAMME;
      $sub_array[] = $row->INTITULE_PROGRAMME;
      $sub_array[] = !empty($row->OBJECTIF_DU_PROGRAMME) ? $row->OBJECTIF_DU_PROGRAMME : 'N/A';
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
      $sub_array[] = number_format($row->T1, 0, ',', ' ') . ' BIF';
      $sub_array[] = number_format($row->T2, 0, ',', ' ') . ' BIF';
      $sub_array[] = number_format($row->T3, 0, ',', ' ') . ' BIF';
      $sub_array[] = number_format($row->T4, 0, ',', ' ') . ' BIF';
      $sub_array[] = number_format($row->total, 0, ',', ' ') . ' BIF';

      //Declaration des labels pour l'internalisation
      $bouton_detail = lang("messages_lang.bouton_detail");
      $sub_array[] = "<center><a href='".base_url("ptba/Detail_Programme/".md5($row->PROGRAMME_ID))."' >
      <label class='btn btn-primary'>&nbsp;&nbsp;$bouton_detail</label></a></center>";
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }

  /*
  * fonction pour retourner le tableau des parametre pour le PS pour les selection
  * @param string  $columnselect //colone A selectionner
  * @param string  $table        //table utilisE
  * @param string  $where        //condition dans la clause where
  * @param string  $orderby      //order by
  * @return  mixed
  */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>