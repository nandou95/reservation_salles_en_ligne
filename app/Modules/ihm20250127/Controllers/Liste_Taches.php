<?php
/**
* Joa-Kevin Iradukunda
* liste des ptba taches
* Date: 05/09/2024
*/

namespace App\Modules\ihm\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time',20000);
// ini_set('memory_limit','5000MB');
class Liste_Taches extends BaseController
{
	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

  //function qui appelle le view de la liste 
	public function index()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')!=1)
		{
		 return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		//Sélectionner les institutions
		$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`DESCRIPTION_INSTITUTION` ASC');
		$data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

		return view('App\Modules\ihm\Views\Dem_Liste_Taches_View',$data);   
	}

	//liste des ptba taches
	public function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')!=1)
		{
		 return redirect('Login_Ptba/homepage');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
		$ACTION_ID = $this->request->getPost('ACTION_ID');
		$PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');

		$critere1='';
		$critere2='';
		$critere3='';
		$critere4='';

		//Filtre par institution
		if(!empty($INSTITUTION_ID))
		{
			$critere1 = ' AND ptba.`INSTITUTION_ID`='.$INSTITUTION_ID;
		}
		//Filtre par programme
		if(!empty($CODE_PROGRAMME))
		{
			$critere2=' AND ptba.PROGRAMME_ID = '.$PROGRAMME_ID;
		}
  	//Filtre par action
		if(!empty($ACTION_ID))
		{
			$critere3=' AND ptba.ACTION_ID ='.$ACTION_ID;
		}
		//filtre par activite
		if(!empty($PAP_ACTIVITE_ID))
		{
			$critere4=' AND ptba.PAP_ACTIVITE_ID ='.$PAP_ACTIVITE_ID;
		}

		$query_principal= "SELECT ptba.PTBA_TACHE_ID,
															ptba.DESC_TACHE, 
															ptba.RESULTAT_ATTENDUS_TACHE, 
														  ptba.BUDGET_T1, 
														  ptba.BUDGET_T2,
														  ptba.BUDGET_T3,
														  ptba.BUDGET_T4,
														  ptba.BUDGET_ANNUEL,
														  inst.DESCRIPTION_INSTITUTION,
														  prog.INTITULE_PROGRAMME,
														  action.LIBELLE_ACTION,
														  activite.DESC_PAP_ACTIVITE
											 FROM ptba_tache ptba
											 LEFT JOIN inst_institutions inst ON inst.INSTITUTION_ID = ptba.INSTITUTION_ID
											 LEFT JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID = ptba.PROGRAMME_ID
											 LEFT JOIN inst_institutions_actions action ON action.ACTION_ID = ptba.ACTION_ID
											 LEFT JOIN pap_activites activite ON activite.PAP_ACTIVITE_ID = ptba.PAP_ACTIVITE_ID
											 WHERE 1";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}

		$order_by='';
		$order_column='';
		$order_column= array('ptba.PTBA_TACHE_ID','ptba.DESC_TACHE','ptba.RESULTAT_ATTENDUS_TACHE','inst.DESCRIPTION_INSTITUTION','prog.INTITULE_PROGRAMME','action.LIBELLE_ACTION','activite.DESC_PAP_ACTIVITE','ptba.BUDGET_T1','ptba.BUDGET_T2','ptba.BUDGET_T3', 'ptba.BUDGET_T4','ptba.BUDGET_ANNUEL');

		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba.PTBA_TACHE_ID DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (ptba.DESC_TACHE LIKE "%'. $var_search.'%" OR ptba.RESULTAT_ATTENDUS_TACHE  LIKE "%'.$var_search.'%" OR inst.DESCRIPTION_INSTITUTION LIKE "%'.$var_search.'%" OR prog.INTITULE_PROGRAMME LIKE "%'.$var_search.'%" OR action.LIBELLE_ACTION LIKE "%'.$var_search.'%" OR activite.DESC_PAP_ACTIVITE LIKE "%'.$var_search.'%" OR ptba.BUDGET_T1 LIKE "%'.$var_search.'%" OR ptba.BUDGET_T2 LIKE "%'.$var_search.'%" OR ptba.BUDGET_T3 LIKE "%'.$var_search.'%" OR ptba.BUDGET_T4 LIKE "%'.$var_search.'%" OR ptba.BUDGET_ANNUEL LIKE "%'.$var_search.'%")') : '';

		$criteres = $critere1.' '.$critere2.' '.$critere3.' '.$critere4;
		$query_secondaire = $query_principal.' '.$search.' '.$criteres.' '.$order_by.' '.$limit;

		$query_secondaire = str_replace('"', '\\"', $query_secondaire);
		
		$query_filter = $query_principal.' '.$search.' '.$criteres;
		$query_filter=str_replace('"', '\\"',$query_filter);
		$requete="CALL `getList`('".$query_secondaire."')";
		$fetch_cov_frais = $this->ModelPs->datatable( $requete);
		$data = array();
		$u=1;
		foreach($fetch_cov_frais as $info)
		{
			$post=array();
			$post[]=$u++;
			$post[]=$info->DESC_TACHE;
			$post[]=$info->RESULTAT_ATTENDUS_TACHE;
			$post[]=$info->DESCRIPTION_INSTITUTION;
			$post[]=$info->INTITULE_PROGRAMME;
			$post[]=$info->LIBELLE_ACTION;
			$post[]=$info->DESC_PAP_ACTIVITE;
			$post[] = number_format($info->BUDGET_T1,0,","," ");
			$post[] = number_format($info->BUDGET_T2,0,","," ");
			$post[] = number_format($info->BUDGET_T3,0,","," ");
			$post[] = number_format($info->BUDGET_T4,0,","," ");
			$post[] = number_format($info->BUDGET_ANNUEL,0,","," ");

			$data[]=$post;  
		}
		
		$requeteqp='CALL `getList`("'.$query_principal.'")';
		$recordsTotal = $this->ModelPs->datatable( $requeteqp);
		$requeteqf='CALL `getList`("'.$query_filter.'")';
		$recordsFiltered = $this->ModelPs->datatable( $requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" =>count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);

		// echo json_encode($output);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les programmes
	public function get_programme()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$resProgrammes=array();
		if($INSTITUTION_ID != "")
		{
			$sql_programme='SELECT PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE 1 AND INSTITUTION_ID ='.$INSTITUTION_ID.' ORDER BY INTITULE_PROGRAMME';
			$resProgrammes = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_programme . "')");
		}
		
		$programme="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resProgrammes as $resProgramme)
		{
			$programme.= "<option value ='".$resProgramme->PROGRAMME_ID."'>".$resProgramme->CODE_PROGRAMME."-".$resProgramme->INTITULE_PROGRAMME."</option>";
		}
		$output = array("programme"=>$programme);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les actions
	public function get_action()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$resActions=array();
		if($PROGRAMME_ID != "")
		{
			$sql_action='SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions WHERE 1 AND PROGRAMME_ID ='.$PROGRAMME_ID.' ORDER BY LIBELLE_ACTION';
			$resActions = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_action . "')");
		}
		
		$action="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resActions as $resAction)
		{
			$action.= "<option value ='".$resAction->ACTION_ID."'>".$resAction->CODE_ACTION."-".$resAction->LIBELLE_ACTION."</option>";
		}
		$output = array("action"=>$action);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les pap activites
	public function get_pap_activite()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		$resPapActivites=array();
		if($ACTION_ID != "")
		{
			$sql_code_budg='SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE 1 AND ACTION_ID ='.$ACTION_ID.' ORDER BY DESC_PAP_ACTIVITE';
			$resPapActivites = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_code_budg . "')");
		}

		$pap_activite="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resPapActivites as $resPapActivite)
		{
			$pap_activite.= "<option value ='".$resPapActivite->PAP_ACTIVITE_ID."'>".$resPapActivite->DESC_PAP_ACTIVITE."</option>";
		}
		$output = array("pap_activite"=>$pap_activite);
		return $this->response->setJSON($output);
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
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>