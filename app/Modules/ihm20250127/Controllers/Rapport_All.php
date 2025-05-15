<?php

/**NIYONAHABONYE Pascal
 *Titre:liste des institutions actions
 *Numero de telephone: (+257) 68 045 482
 *WhatsApp: (+257) 77531083
 *Email: pascal@mediabox.bi
 *Date: 29 Août,2023
 **/
/**
 * Ameliore Par
 * Baleke kahamire Bonheur
 * Numero: (+257)67866283
 * mail: bonheur.baleke@mediabox.bi
 * 30.01.2024
 */

namespace  App\Modules\ihm\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;

class Rapport_All extends BaseController
{
	public $library;
	public $ModelPs;
	public $session;
	public $validation;

	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function index($value = '')
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_PARAMETRE_ACTION')!=1)
		{
		 	return redirect('Login_Ptba/homepage');
		}

		$INSTITUTION_ID=0;
	    $SOUS_TUTEL_ID=0;
	    $menu_suivi_exec=$this->count_suivi_execution($INSTITUTION_ID,$SOUS_TUTEL_ID);


	    $data['EBCORRIGE']=$menu_suivi_exec['EBCORRIGE'];
	    $data['EBAVALIDE']=$menu_suivi_exec['EBAVALIDE'];
	    $data['EJFAIRE']=$menu_suivi_exec['EJFAIRE'];
	    $data['EJCORRIGER']=$menu_suivi_exec['EJCORRIGER'];
	    $data['EJVALIDER']=$menu_suivi_exec['EJVALIDER'];
	    $data['LIQFAIRE']=$menu_suivi_exec['LIQFAIRE'];
	    $data['LIQCORRIGER']=$menu_suivi_exec['LIQCORRIGER'];
	    $data['LIQVALIDE']=$menu_suivi_exec['LIQVALIDE'];
	    $data['ORDVALIDE']=$menu_suivi_exec['ORDVALIDE'];

	    $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
	    $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
	    $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
	    $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
	    $data['obr_recep']=$menu_suivi_exec['obr_recep'];
	    $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
	    $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
	    // $data['execution_budgetaire']=array();

		

		return view('App\Modules\ihm\Views\Rapport_All_View', $data);
	}

	public function get_statut()
	{
		$bind_tache = 'SELECT
	    exec.EXECUTION_BUDGETAIRE_ID,
	    exec.ENG_BUDGETAIRE,
	    exec.ENG_JURIDIQUE,
	    exec.LIQUIDATION,
	    exec.ORDONNANCEMENT,
	    exec.PAIEMENT,
	    exec.DECAISSEMENT,
	    inst.DESCRIPTION_INSTITUTION
	FROM
	    execution_budgetaire exec
	LEFT JOIN inst_institutions inst ON
	    inst.INSTITUTION_ID = exec.INSTITUTION_ID
	LEFT JOIN execution_budgetaire_tache_detail det ON
	    exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID
	LEFT JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID
	WHERE
	    exec.STATUT_ENGAGEMENT_NOUVEAU=0 AND det.ETAPE_DOUBLE_COMMANDE_ID=3 ';
		$bind_tache = "CALL `getTable`('".$bind_tache."');";
		$execution_budgetaire = $this->ModelPs->getRequete($bind_tache);

		$nbr = count($execution_budgetaire);
		$html = '';

		foreach ($execution_budgetaire as $key) 
		{
			$EXECUTION_BUDGETAIRE_ID=$key->EXECUTION_BUDGETAIRE_ID;

			$STATUT_ENGAGEMENT_NOUVEAU=1;
			$table='execution_budgetaire';
			$conditions='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$datatomodifie='STATUT_ENGAGEMENT_NOUVEAU="'.$STATUT_ENGAGEMENT_NOUVEAU.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);
		}

		$output = array('status'=>true);
    	return $this->response->setJSON($output);
	}

	public function get_engag_nouveau()
	{
		$bind_tache = 'SELECT
	    exec.EXECUTION_BUDGETAIRE_ID,
	    exec.ENG_BUDGETAIRE,
	    exec.ENG_JURIDIQUE,
	    exec.LIQUIDATION,
	    exec.ORDONNANCEMENT,
	    exec.PAIEMENT,
	    exec.DECAISSEMENT,
	    inst.DESCRIPTION_INSTITUTION
	FROM
	    execution_budgetaire exec
	LEFT JOIN inst_institutions inst ON
	    inst.INSTITUTION_ID = exec.INSTITUTION_ID
	LEFT JOIN execution_budgetaire_tache_detail det ON
	    exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID
	LEFT JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID
	WHERE
	    exec.STATUT_ENGAGEMENT_NOUVEAU=0 AND det.ETAPE_DOUBLE_COMMANDE_ID=3 ';
		$bind_tache = "CALL `getTable`('".$bind_tache."');";
		$execution_budgetaire = $this->ModelPs->getRequete($bind_tache);

		$nbr = count($execution_budgetaire);
		$html = '';

		foreach ($execution_budgetaire as $key) 
		{
			$DESCRIPTION_INSTITUTION = (mb_strlen($key->DESCRIPTION_INSTITUTION) > 60) ? (mb_substr($key->DESCRIPTION_INSTITUTION, 0, 60) . '...<span title="'.$key->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></span>') : $key->DESCRIPTION_INSTITUTION;

			$html.='<a class="dropdown-item dropdown-notifications-item" href="#!"> <div class="dropdown-notifications-item-content"> <div class="dropdown-notifications-item-content-details">'.$DESCRIPTION_INSTITUTION.'</div> </div> </a>';
			//onclick="get_statut('.$key->EXECUTION_BUDGETAIRE_ID.')"
		}

		$output = array('nbr'=>$nbr,'html'=>$html);
    	return $this->response->setJSON($output);
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
        // $columsinsert: Nom des colonnes separe par,
        // $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}
}
