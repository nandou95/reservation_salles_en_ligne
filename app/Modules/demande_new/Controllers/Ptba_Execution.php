<?php
/**RUGAMBA Jean-Vainqueur
*Titre:Liste de ptba exécution
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 04 octobre,2023
**/
namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Ptba_Execution extends BaseController
{
	function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs($db);
		$this->my_Model = new ModelPs($db);
    $this->validation = \Config\Services::validation();
    $this->session 	= \Config\Services::session();
    $table = new \CodeIgniter\View\Table();
  }

  //Liste view
  public function index($value='')
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    return view('App\Modules\demande_new\Views\Ptba_Execution_View',$data);
  }

  //fonction pour affichage d'une liste
  public function listing()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //Filtres de la liste
    $critere1="";
    $critere2="";
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
    $order_column=array(1,'ACTIVITES','RESULTATS_ATTENDUS','UNITE','QT1','T1','RESULTATS_REALISES_T1','ECART_PHYSIQUE_T1','MOYENS_VERIFICATION_T1','OBSERVATIONS_T1','ENG_BUDGETAIRE','ENG_JURIDIQUE','LIQUIDATION','ORDONNANCEMENT','PAIEMENT','DECAISSEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ACTIVITES ASC';

     $search = !empty($_POST['search']['value']) ?  (" AND (ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR UNITE LIKE '%$var_search%' OR QT1 LIKE '%$var_search%' OR T1 LIKE '%$var_search%' OR RESULTATS_REALISES_T1 LIKE '%$var_search%' OR ECART_PHYSIQUE_T1 LIKE '%$var_search%' OR MOYENS_VERIFICATION_T1 LIKE '%$var_search%' OR OBSERVATIONS_T1 LIKE '%$var_search%'  OR ENG_BUDGETAIRE LIKE '%$var_search%' OR ENG_JURIDIQUE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%' OR ORDONNANCEMENT LIKE '%$var_search%' OR PAIEMENT LIKE '%$var_search%' OR DECAISSEMENT LIKE '%$var_search%')"):'';

    $critaire= $critere1 ." ". $critere2 ." ". $critere3;

    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
    
    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;


    $requetedebase="SELECT PTBA_EXECUTION_ID,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QT1,T1,RESULTATS_REALISES_T1,ECART_PHYSIQUE_T1,MOYENS_VERIFICATION_T1,OBSERVATIONS_T1,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT FROM ptba_execution WHERE 1";

    $requetedebases=$requetedebase." ".$conditions;
    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = !empty($row->ACTIVITES) ? $row->ACTIVITES : 'N/A';
      $sub_array[] = !empty($row->RESULTATS_ATTENDUS) ? $row->ACTIVITES : 'N/A';
      $sub_array[] = !empty($row->UNITE) ? $row->UNITE : 'N/A';
      $sub_array[] = number_format($row->QT1,0,","," ");
      $sub_array[] = number_format($row->T1,0,","," ");
      $sub_array[] = !empty($row->RESULTATS_REALISES_T1) ? $row->RESULTATS_REALISES_T1 : 'N/A';
      $sub_array[] = !empty($row->ECART_PHYSIQUE_T1) ? $row->ECART_PHYSIQUE_T1 : 'N/A';
      $sub_array[] = !empty($row->MOYENS_VERIFICATION_T1) ? $row->MOYENS_VERIFICATION_T1 : 'N/A';
      $sub_array[] = !empty($row->OBSERVATIONS_T1) ? $row->OBSERVATIONS_T1 : 'N/A';
      $sub_array[] = number_format($row->ENG_BUDGETAIRE,0,","," ");
      $sub_array[] = number_format($row->ENG_JURIDIQUE,0,","," ");
      $sub_array[] = number_format($row->LIQUIDATION,0,","," ");
      $sub_array[] = number_format($row->ORDONNANCEMENT,0,","," ");
      $sub_array[] = number_format($row->PAIEMENT,0,","," ");
      $sub_array[] = number_format($row->DECAISSEMENT,0,","," ");
     
      $action = '<div class="dropdown" style="color:#fff;">
      <a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url('demande/Ptba_Execution/detail/'.$row->PTBA_EXECUTION_ID)."'>
          <label>&nbsp;&nbsp;Détails</label>
        </a>
      </li>
      </ul>
      </div>";
      
      $sub_array[]=$action;
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
  

  //Fonction pour afficher le détail des ptbas exécutés
  public function detail($id=0)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    //Detail des ptbas exécutés 
    $exec = "SELECT `PTBA_EXECUTION_ID`,`CODE_MINISTERE`,`INTITULE_MINISTERE`,`CODE_PROGRAMME`,`INTITULE_PROGRAMME`,`OBJECTIF_PROGRAMME`,`CODE_ACTION`,`LIBELLE_ACTION`,`OBJECTIF_ACTION`,`CODE_NOMENCLATURE_BUDGETAIRE`,`ARTICLE_ECONOMIQUE`,`INTITULE_ARTICLE_ECONOMIQUE`,`NATURE_ECONOMIQUE`,`INTITULE_NATURE_ECONOMIQUE`,`DIVISION_FONCTIONNELLE`,`INTITULE_DIVISION_FONCTIONNELLE`,`GROUPE_FONCTIONNELLE`,`INTITULE_GROUPE_FONCTIONNELLE`,`CLASSE_FONCTIONNELLE`,`INTITULE_CLASSE_FONCTIONNELLE` ,`CODES_PROGRAMMATIQUE`,`ACTIVITES`,`RESULTATS_ATTENDUS`,`UNITE`,`QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE`,`QT1`,`QT2`,`QT3`,`QT4`,`COUT_UNITAIRE_BIF`,`T1`,`T2`,`T3`,`T4`,`PROGRAMMATION_FINANCIERE_BIF`,`RESPONSABLE`,`GRANDE_MASSE_BP`,`GRANDE_MASSE_BM`,`INTITULE_DES_GRANDES_MASSES`,`GRANDE_MASSE_BM1`,`TRANSFERTS_CREDITS`,`CREDIT_APRES_TRANSFERT`,`IS_MARCHE_PUBLIC`,`ENG_BUDGETAIRE`,`ENG_JURIDIQUE`,`LIQUIDATION`,`ORDONNANCEMENT`,`PAIEMENT`,`DECAISSEMENT`,`RESULTATS_REALISES_T1`,`ECART_PHYSIQUE_T1`,`MOYENS_VERIFICATION_T1`,`OBSERVATIONS_T1` FROM ptba_execution WHERE 1 AND PTBA_EXECUTION_ID = ".$id." ";
    $exec = 'CALL `getTable`("'.$exec.'");';
    $data['ptba_execute'] = $this->ModelPs->getRequeteOne($exec);
    return view('App\Modules\demande_new\Views\Ptba_Execution_Detail_View',$data);
  }

  public function getBindParms($columnselect,$table,$where,$orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }
}	
?>