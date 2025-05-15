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
class Execution extends BaseController
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
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIVITES')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		//Sélectionner les institutions
		$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`DESCRIPTION_INSTITUTION` ASC');
		$data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

		return view('App\Modules\ihm\Views\Execution_View',$data);   
	}

	//liste des ptba taches
	public function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIVITES')!=1)
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

		$query_principal= "SELECT DISTINCT  exec.EXECUTION_BUDGETAIRE_ID, exec.ENG_BUDGETAIRE, exec.ENG_JURIDIQUE, exec.LIQUIDATION, exec.ORDONNANCEMENT, exec.PAIEMENT, exec.DECAISSEMENT, exec.NUMERO_BON_ENGAGEMENT, exec_decaiss.TITRE_DECAISSEMENT,exec_decaiss.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire exec LEFT JOIN execution_budgetaire_titre_decaissement exec_decaiss ON exec.EXECUTION_BUDGETAIRE_ID = exec_decaiss.EXECUTION_BUDGETAIRE_ID LEFT JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID = exec_tache.PTBA_TACHE_ID WHERE exec_decaiss.ETAPE_DOUBLE_COMMANDE_ID>25";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}

		$order_by='';
		$order_column='';
		$order_column= array('exec.EXECUTION_BUDGETAIRE_ID','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec.LIQUIDATION','exec.ORDONNANCEMENT','exec.PAIEMENT','exec.DECAISSEMENT','exec.NUMERO_BON_ENGAGEMENT');

		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec_decaiss.TITRE_DECAISSEMENT LIKE "%'. $var_search.'%")') : '';

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
			$post[]=$info->TITRE_DECAISSEMENT;
			$post[]="<a href='".base_url("ihm/Execution/detail/".md5($info->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' class='btn btn-info btn-sm'><i class='fa fa-list'></i></a>";
			$post[]=$info->NUMERO_BON_ENGAGEMENT;
			$post[]=number_format($info->ENG_BUDGETAIRE,0,","," ");
			$post[]=number_format($info->ENG_JURIDIQUE,0,","," ");
			$post[]=number_format($info->LIQUIDATION,0,","," ");
			$post[]=number_format($info->ORDONNANCEMENT,0,","," ");
			$post[]=number_format($info->PAIEMENT,0,","," ");
			$post[] = number_format($info->DECAISSEMENT,0,","," ");
			

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

	//fonction pour aficher le formulaire de la modification
	public function detail($id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();		
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_tache = 'SELECT DISTINCT
	exec.EXECUTION_BUDGETAIRE_ID,
    exec.ENG_BUDGETAIRE,
    exec.ENG_JURIDIQUE,
    exec.LIQUIDATION,
    exec.ORDONNANCEMENT,
    exec.PAIEMENT,
    exec.DECAISSEMENT,
    exec.NUMERO_BON_ENGAGEMENT,
    exec_decaiss.TITRE_DECAISSEMENT, 
    ptba.PTBA_TACHE_ID,
    ptba.CODE_NOMENCLATURE_BUDGETAIRE,
    ptba.CODES_PROGRAMMATIQUE,
    ptba.DESC_TACHE,
    ptba.BUDGET_T1,
    ptba.BUDGET_T2,
    ptba.BUDGET_T3,
    ptba.BUDGET_T4,
   
    SUM(ptba.BUDGET_T1+ptba.BUDGET_T2+ptba.BUDGET_T3+ptba.BUDGET_T4) AS MONTANT_VOTE,
    SUM(BUDGET_RESTANT_T1+BUDGET_RESTANT_T2+BUDGET_RESTANT_T3+BUDGET_RESTANT_T4) AS MONTANT_RESTANT,
    ((SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4))-(SUM(BUDGET_RESTANT_T1+BUDGET_RESTANT_T2+BUDGET_RESTANT_T3+BUDGET_RESTANT_T4))) AS MONTANT_EXECUTE,
    ptba.BUDGET_ANNUEL,
    inst.DESCRIPTION_INSTITUTION,
    sous_t.DESCRIPTION_SOUS_TUTEL,
    pilier.DESCR_PILIER,
    vision.DESC_OBJECTIF_VISION,
    pnd_axe.DESCR_AXE_PND,
    prog.INTITULE_PROGRAMME,
    actions.LIBELLE_ACTION,
    propriete.DESC_PROGRAMME_PRIORITAIRE,
    code_budget.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
    article.LIBELLE_ARTICLE,
    sous_littera.LIBELLE_SOUS_LITTERA,
    division.LIBELLE_DIVISION,
    groupe.LIBELLE_GROUPE,
    classe.LIBELLE_CLASSE,
    activite.DESC_PAP_ACTIVITE,
    costab.DESC_COSTAB_ACTIVITE,
    indicateur.DESC_INDICATEUR_PND,
    respo.DESC_STRUTURE_RESPONSABLE_TACHE,
    masse.DESCRIPTION_GRANDE_MASSE,
    annee.ANNEE_DESCRIPTION
FROM 
execution_budgetaire exec
LEFT JOIN execution_budgetaire_titre_decaissement exec_decaiss ON
    exec.EXECUTION_BUDGETAIRE_ID = exec_decaiss.EXECUTION_BUDGETAIRE_ID
LEFT JOIN execution_budgetaire_execution_tache exec_tache ON
    exec.EXECUTION_BUDGETAIRE_ID = exec_tache.EXECUTION_BUDGETAIRE_ID
LEFT JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID = exec_tache.PTBA_TACHE_ID
LEFT JOIN inst_institutions inst ON
    inst.INSTITUTION_ID = ptba.INSTITUTION_ID
LEFT JOIN inst_institutions_sous_tutel sous_t ON
    sous_t.SOUS_TUTEL_ID = ptba.SOUS_TUTEL_ID
LEFT JOIN pilier pilier ON
    pilier.ID_PILIER = ptba.ID_PILIER
LEFT JOIN vision_objectif vision ON
    vision.OBJECTIF_VISION_ID = ptba.OBJECTIF_VISION_ID
LEFT JOIN pnd_axe pnd_axe ON
    pnd_axe.AXE_PND_ID = ptba.AXE_PND_ID
LEFT JOIN inst_institutions_programmes prog ON
    prog.PROGRAMME_ID = ptba.PROGRAMME_ID
LEFT JOIN inst_institutions_actions actions ON actions.ACTION_ID = ptba.ACTION_ID
LEFT JOIN inst_institutions_programme_prioritaire propriete ON
    propriete.PROGRAMME_PRIORITAIRE_ID = ptba.PROGRAMME_PRIORITAIRE_ID
LEFT JOIN inst_institutions_ligne_budgetaire code_budget ON
    code_budget.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID
LEFT JOIN class_economique_article article ON
    article.ARTICLE_ID = ptba.ARTICLE_ID
LEFT JOIN class_economique_sous_littera sous_littera ON
    sous_littera.SOUS_LITTERA_ID = ptba.SOUS_LITTERA_ID
LEFT JOIN class_fonctionnelle_division division ON
    division.DIVISION_ID = ptba.DIVISION_ID
LEFT JOIN class_fonctionnelle_groupe groupe ON
    groupe.GROUPE_ID = ptba.GROUPE_ID
LEFT JOIN class_fonctionnelle_classe classe ON
    classe.CLASSE_ID = ptba.CLASSE_ID
LEFT JOIN pap_activites activite ON
    activite.PAP_ACTIVITE_ID = ptba.PAP_ACTIVITE_ID
LEFT JOIN costab_activites costab ON
    costab.COSTAB_ACTIVITE_ID = ptba.COSTAB_ACTIVITE_ID
LEFT JOIN pnd_indicateur indicateur ON
    indicateur.INDICATEUR_PND_ID = ptba.PND_INDICATEUR_ID
LEFT JOIN struture_responsable_tache respo ON
    respo.STRUTURE_RESPONSABLE_TACHE_ID = ptba.STRUTURE_RESPONSABLE_TACHE_ID
LEFT JOIN inst_grande_masse masse ON
    masse.GRANDE_MASSE_ID = ptba.GRANDE_MASSE_ID
LEFT JOIN annee_budgetaire annee ON
    annee.ANNEE_BUDGETAIRE_ID = ptba.ANNEE_BUDGETAIRE_ID
WHERE 1 AND exec_decaiss.TITRE_DECAISSEMENT IS NOT NULL AND md5(exec_decaiss.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'" ORDER BY EXECUTION_BUDGETAIRE_ID ASC';

		$bind_tache = "CALL `getTable`('".$bind_tache."');";
		$data['data'] = $this->ModelPs->getRequeteOne($bind_tache);

		$data['title'] = 'Detail';
		return view('App\Modules\ihm\Views\Detail_Execution_View', $data);
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
}
?>