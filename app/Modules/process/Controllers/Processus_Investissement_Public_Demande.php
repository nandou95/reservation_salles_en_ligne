<?php

/**
 * @author : 2ola
 * @modifier par deric@mediabox.bi
 * controlleur pour la fiche de demande d investissement public
 */

namespace App\Modules\pip\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '12048M');
class Processus_Investissement_Public_Demande extends BaseController
{
	protected $library;
	protected $ModelPs;
	protected $session;
	protected $validation;

	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	// Afficher le formulaire de préparation de la fiche de projet
	public function index()
	{
		$this->verifyUser();
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = '';
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['titre'] = lang('messages_lang.titre_objectif_strategique');
		$query_principal = 'SELECT  ID_OBJECT_STRATEGIQUE, DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique WHERE ID_OBJECT_STRATEGIQUE >0';
		$requete = "CALL `getTable`('" . $query_principal . "')";
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data['objectifs'] = $fetch_cov_frais;
		$data['titre'] = lang('messages_lang.titre_objectif_strategique');
		$query_principal_2 = 'SELECT  ID_OBJECT_GENERAL, OBJECTIF_GENERAL FROM pip_objectif_general WHERE ID_OBJECT_GENERAL >0';
		$requete = "CALL `getTable`('" . $query_principal_2 . "')";
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data['general'] = $fetch_cov_frais;
		$data['status'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_STATUT_PROJET,DESCR_STATUT_PROJET FROM pip_statut_projet WHERE 1 ORDER BY ID_STATUT_PROJET ASC')");
		$data['provinces'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT PROVINCE_ID, PROVINCE_NAME FROM provinces WHERE 1 ORDER BY PROVINCE_ID ASC')");
		$data['communes'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT COMMUNE_ID, COMMUNE_NAME FROM communes WHERE 1 ORDER BY COMMUNE_ID ASC')");
		$data['institutions'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT INSTITUTION_ID , CODE_INSTITUTION, DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID=" . $user_id . ") ORDER BY INSTITUTION_ID ASC')");
		$data['piliers'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER , DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");
		$data['objectif_strategiques'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE, DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique WHERE 1 ORDER BY ID_PILIER ASC')");
		$data['objectif_strategiques_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM objectif_strategique_pnd WHERE 1')");
		$data['axe_intervations_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd WHERE 1 ORDER BY ID_AXE_INTERVENTION_PND ASC')");
		$data['programmes'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT PROGRAMME_ID, CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE 1 ORDER BY PROGRAMME_ID ASC')");
		$data['actions'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ACTION_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE 1 ORDER BY ACTION_ID ASC')");
		$data['demandes'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT proc_demandes.ID_DEMANDE, user_users.NOM, user_users.PRENOM FROM proc_demandes INNER JOIN user_users ON proc_demandes.USER_ID = user_users.USER_ID WHERE 1 ORDER BY proc_demandes.ID_DEMANDE ASC')");
		$data['devises'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_taux_echange')");
		$data['nomenclatures'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_nomenclature_budgetaire')");
		$data['programme_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM programme_pnd WHERE 1')");
		$data['usd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_taux_echange WHERE DEVISE=\"USD\" AND IS_ACTIVE=\"1\"')");
		$data['euro'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_taux_echange WHERE DEVISE=\"EURO\" AND IS_ACTIVE=\"1\"')");
		$data['countries'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT COUNTRY_ID,CommonName FROM countries WHERE 1')");
		$data['title'] = lang('messages_lang.titre_demande');
		$data['cumulative'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_CUMULATIVE,DESCRIPTION_CUMULATIVE FROM `pip_cumulative` WHERE 1')");
		$data['annees'] = $this->get_annee_pip();
		return view('App\Modules\pip\Views\Processus_Investissement_Public_Demande_view', $data);
	}

	public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
	{
		
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$Limit = str_replace("\'", "'", $Limit);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	/**
	 * fonction pour recuperer les informations pour effectuer des calculs 
	 * @param int $id
	 * @return string
	 */
	public function get_info_livrable_cmr(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$query_principal = 'SELECT pip_nomenclature_budgetaire_pourcentage.POURCENTAGE_NOMENCLATURE,pip_nomenclature_budgetaire.DESCR_NOMENCLATURE,pip_nomenclature_budgetaire_pourcentage.ID_NOMENCLATURE FROM pip_nomenclature_budgetaire_pourcentage JOIN pip_nomenclature_budgetaire ON pip_nomenclature_budgetaire_pourcentage.ID_NOMENCLATURE = pip_nomenclature_budgetaire.ID_NOMENCLATURE WHERE 1 ';
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';
		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = '';
		$order_column = array('1,1,1');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCR_NOMENCLATURE ASC';
		$search = !empty($_POST['search']['value']) ?  (" AND (pip_nomenclature_budgetaire_pourcentage.ID_NOMENCLATURE LIKE '%$var_search%')") : '';
		$critaire = '';
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;
		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		foreach ($fetch_cov_frais as $info) 
		{
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table = "pip_valeur_nomenclature_livrable pvnl JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE = pvnl.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN annee_budgetaire anne ON pvnl.ANNEE_BUDGETAIRE_ID = anne.ANNEE_BUDGETAIRE_ID";
			$columnselect = " pvnl.ID_CADRE_MESURE_RESULTAT_LIVRABLE, MONTANT_NOMENCALTURE, pvnl.ANNEE_BUDGETAIRE_ID, pvnl.ID_NOMENCLATURE, POURCENTAGE_NOMCALTURE,anne.ANNEE_DESCRIPTION";
			$where = "ID_NOMENCLATURE='" . $info->ID_NOMENCLATURE . "' and pip_cadre_mesure_resultat_livrable.ID_LIVRABLE='" . $id . "'";
			$orderby = 'ANNEE_BUDGETAIRE_ID ASC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34 = str_replace("\'", "'", $bindparamss);
			$livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			$table = "cadre_mesure_resultat_valeur_cible cmvl join pip_cadre_mesure_resultat_livrable on cmvl.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE";
			$columnselect = "VALEUR_ANNEE_CIBLE,ANNEE_BUDGETAIRE_ID";
			$where = "ID_LIVRABLE='" . $id . "'";
			$orderby = 'ID_LIVRABLE DESC';
			$where = str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparamsss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
			$bindparams34s = str_replace("\'", "'", $bindparamsss);
			$valeurcible = $this->ModelPs->getRequete($callpsreq, $bindparams34s);
			$post = array();
			$anne1 = 0;
			$anne2 = 0;
			$anne3 = 0;
			$cible1 = 0;
			$cible2 = 0;
			$cible3 = 0;
			if (isset($livrable[0])) 
			{
				$anne1 = $livrable[0]->MONTANT_NOMENCALTURE;
			}
			if (isset($livrable[1])) 
			{
				$anne2 = $livrable[1]->MONTANT_NOMENCALTURE;
			}
			if (isset($livrable[2])) 
			{
				$anne3 = $livrable[2]->MONTANT_NOMENCALTURE;
			}
			if (isset($valeurcible[0])) 
			{
				$cible1 = $valeurcible[0]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeurcible[1])) 
			{
				$cible2 = $valeurcible[1]->VALEUR_ANNEE_CIBLE;
			}
			if (isset($valeurcible[2])) 
			{
				$cible3 = $valeurcible[2]->VALEUR_ANNEE_CIBLE;
			}
			$post[] = $info->DESCR_NOMENCLATURE;
			$post[] = $info->POURCENTAGE_NOMENCLATURE;
			$post[] = number_format($anne1, '0', ',', ' ');
			$post[] = number_format($anne2, '0', ',', ' ');
			$post[] = number_format($anne3, '0', ',', ' ');
			$data[] = $post;
		}
		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);
		$output = array
		(
			"cible1" => $cible1,
			"cible2" => $cible2,
			"cible3" => $cible3,
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/**
	 * Avoir des actions à partir des programmes budgétaires
	 * @param int $id
	 * @return string
	 */
	public function filtre(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT ACTION_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID = {$id} ORDER BY ACTION_ID ASC')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($data as $action) 
		{
			$html .= "<option value='{$action->ACTION_ID}'>{$action->CODE_ACTION} - {$action->LIBELLE_ACTION}</option>";
		}
		return json_encode($html);
	}

	/**
	 * Sélectionner les communes
	 * @param int $id
	 * @return string
	 */
	public function filtre_commune(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->ModelPs->getRequete("CALL `getTable`('SELECT COMMUNE_ID, COMMUNE_NAME FROM communes WHERE PROVINCE_ID = {$id} ORDER BY COMMUNE_ID ASC')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option disabled value=''>{$select}</option>";
		foreach ($data as $commune)
		{
			$html .= "<option value='{$commune->COMMUNE_ID}'>{$commune->COMMUNE_NAME}</option>";
		}
		return json_encode($html);
	}

	/**
	 * Séléctionner les programmes Budgetaire
	 * @param int $id
	 * @return string
	 */
	public function filtre_programme(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT CODE_PROGRAMME,INTITULE_PROGRAMME,PROGRAMME_ID,INSTITUTION_ID FROM inst_institutions_programmes WHERE INSTITUTION_ID=" . $id . "')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($data as $programme) 
		{
			$html .= "<option value='{$programme->PROGRAMME_ID}'>{$programme->CODE_PROGRAMME} - {$programme->INTITULE_PROGRAMME}</option>";
		}
		return json_encode($html);
	}

	/**
	 * Séléctionner les livrables
	 * @param int $id
	 * @return string
	 */
	public function filtre_livrable(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		if (empty($id)) 
		{
			$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
			$process = end($data['processus']);
			$id = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		}
		$data = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_livrable WHERE ID_DEMANDE_INFO_SUPP={$id}')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($data as $livrable) 
		{
			$html .= "<option value='{$livrable->ID_DEMANDE_LIVRABLE}'>{$livrable->DESCR_LIVRABLE}</option";
		}
		return json_encode($html);
	}

	/**
	 * Enregistrer le projet
	 * @return int
	 */
	public function storeProjet()
	{
		$db = db_connect();
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$session = \Config\Services::session();
			if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }

			$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
			$ID_PROGRAMME = $this->request->getPost('ID_PROGRAMME');
			$ID_ACTION = $this->request->getPost('ID_ACTION');
			$user_institution = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM inst_institutions WHERE INSTITUTION_ID={$INSTITUTION_ID}')");
			$programme = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM inst_institutions_programmes WHERE PROGRAMME_ID={$ID_PROGRAMME}')");
			$action = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM inst_institutions_actions WHERE ACTION_ID={$ID_ACTION}')");
			$data['numero'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT COUNT(*) number FROM pip_demande_infos_supp WHERE YEAR(DATE_PREPARATION_FICHE_PROJET) = YEAR(CURDATE())')");
			$num = str_pad($data['numero'][0]->number, 3, '0', STR_PAD_LEFT);
			$NUMERO_PROJET = $user_institution[0]->CODE_INSTITUTION . '-' . $programme[0]->CODE_PROGRAMME . '-' . $action[0]->CODE_ACTION . '-'. $num .'-' . date('Y');
			$ID_STATUT_PROJET = $this->request->getPost('ID_STATUT_PROJET');
			$NOM_PROJET = $this->request->getPost('NOM_PROJET');
			$NOM_PROJET = $db->escapeString($NOM_PROJET);
			$DATE_DEBUT_PROJET = $this->request->getPost('DATE_DEBUT_PROJET');
			$DATE_FIN_PROJET = $this->request->getPost('DATE_FIN_PROJET');
			$DUREE_PROJET = $this->request->getPost('DUREE_PROJET');
			$ID_AXE_INTERVENTION_PND = $this->request->getPost('ID_AXE_INTERVENTION_PND');
			$ID_PILIER = $this->request->getPost('ID_PILIER');
			$ID_OBJECT_STRATEGIQUE = $this->request->getPost('ID_OBJECT_STRATEGIQUE');
			$ID_OBJECT_STRATEGIC_PND = $this->request->getPost('ID_OBJECT_STRATEGIC_PND');
			$ID_PROGRAMME_PND = $this->request->getPost('ID_PROGRAMME_PND');
			$user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$query1 = "SELECT * FROM proc_process WHERE `NOM_PROCESS` LIKE '%PROCESSUS DE PROGRAMME D''INVESTISSEMENT PUBLIC%' LIMIT 1";
			$process = $this->ModelPs->getRequete('CALL `getTable`("' . $query1 . '")');
			$query2 = "SELECT * FROM proc_etape WHERE PROCESS_ID = {$process[0]->PROCESS_ID} ORDER BY ETAPE_ID ASC LIMIT 1 OFFSET 1";
			$etape = $this->ModelPs->getRequete("CALL `getTable`('{$query2}')");
			$code = "";
			$max = $this->ModelPs->getRequete("CALL `getTable`('SELECT MAX(`ID_DEMANDE`) ID FROM proc_demandes WHERE 1')");
			$id = $max[0]->ID + 1;
			$code = "PIP-" . $id;

			$insertId = $this->save_all_table
			(
				"proc_demandes",
				"CODE_DEMANDE,PROCESS_ID,ETAPE_ID,USER_ID",
				"'{$code}', '{$process[0]->PROCESS_ID}', '{$etape[0]->ETAPE_ID}', '{$user_id}'"
			);
			$ID_INFO_SUPP = $this->save_all_table
			(
				"pip_demande_infos_supp",
				"ID_DEMANDE,ID_STATUT_PROJET,NOM_PROJET,NUMERO_PROJET,DATE_DEBUT_PROJET,DATE_FIN_PROJET,
				DUREE_PROJET,ID_AXE_INTERVENTION_PND,INSTITUTION_ID,ID_PILIER,ID_OBJECT_STRATEGIQUE,
				ID_OBJECT_STRATEGIC_PND,ID_PROGRAMME_PND,ID_PROGRAMME,ID_ACTION
				",
				"
				'{$insertId}','{$ID_STATUT_PROJET}','{$NOM_PROJET}','{$NUMERO_PROJET}','{$DATE_DEBUT_PROJET}','{$DATE_FIN_PROJET}','{$DUREE_PROJET}',
				'{$ID_AXE_INTERVENTION_PND}','{$INSTITUTION_ID}','{$ID_PILIER}','{$ID_OBJECT_STRATEGIQUE}','{$ID_OBJECT_STRATEGIC_PND}',
				'{$ID_PROGRAMME_PND}','{$ID_PROGRAMME}','{$ID_ACTION}'
				"
			);
			
			return $ID_INFO_SUPP;
		}
	}

	/**
	 * Mettre à jour la première tab
	 * @return bool
	 */
	public function updateProjet()
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$session = \Config\Services::session();
			if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }

			$db = db_connect();
			$ID_STATUT_PROJET = $this->request->getPost('ID_STATUT_PROJET');
			$NOM_PROJET = $this->request->getPost('NOM_PROJET');
			$NOM_PROJET = $db->escapeString($NOM_PROJET);
			$DATE_DEBUT_PROJET = $this->request->getPost('DATE_DEBUT_PROJET');
			$DATE_FIN_PROJET = $this->request->getPost('DATE_FIN_PROJET');
			$DUREE_PROJET = $this->request->getPost('DUREE_PROJET');
			$ID_AXE_INTERVENTION_PND = $this->request->getPost('ID_AXE_INTERVENTION_PND');
			$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
			$ID_PILIER = $this->request->getPost('ID_PILIER');
			$ID_OBJECT_STRATEGIQUE = $this->request->getPost('ID_OBJECT_STRATEGIQUE');
			$ID_OBJECT_STRATEGIC_PND = $this->request->getPost('ID_OBJECT_STRATEGIC_PND');
			$ID_PROGRAMME_PND = $this->request->getPost('ID_PROGRAMME_PND');
			$ID_PROGRAMME = $this->request->getPost('ID_PROGRAMME');
			$ID_ACTION = $this->request->getPost('ID_ACTION');
			$ID_DEMANDE = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
			$critere = "ID_DEMANDE_INFO_SUPP=" . $ID_DEMANDE;
			$dataToUpdate = "ID_STATUT_PROJET='$ID_STATUT_PROJET',NOM_PROJET='$NOM_PROJET',DATE_DEBUT_PROJET='$DATE_DEBUT_PROJET',DATE_FIN_PROJET='$DATE_FIN_PROJET',DUREE_PROJET='$DUREE_PROJET',ID_AXE_INTERVENTION_PND='$ID_AXE_INTERVENTION_PND',INSTITUTION_ID='$INSTITUTION_ID',ID_PILIER='$ID_PILIER',ID_OBJECT_STRATEGIQUE='$ID_OBJECT_STRATEGIQUE',ID_OBJECT_STRATEGIC_PND='$ID_OBJECT_STRATEGIC_PND',ID_PROGRAMME_PND='$ID_PROGRAMME_PND',ID_PROGRAMME='$ID_PROGRAMME',ID_ACTION='$ID_ACTION'";
			$bindProjet = ["pip_demande_infos_supp", $dataToUpdate, $critere];
			$updateRequest = "CALL `updateData`(?,?,?);";
			$INFO_SUP_ID = $this->ModelPs->createUpdateDelete($updateRequest, $bindProjet);
			return $INFO_SUP_ID;
		}
	}

	/**
	 * Enregistrer les lieux d'intervention
	 * @return int
	 */
	public function storeIntervation()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$ID_PROVINCE = $this->request->getPost('ID_PROVINCE');
		$ID_COMMUNE = $this->request->getPost('commune');
		if (is_string($ID_COMMUNE)) 
		{
			$ID_COMMUNE = explode(',', $ID_COMMUNE);
		}
		foreach ($ID_COMMUNE as $commune)
		{
			$result = $this->save_all_table
			(
				"pip_lieu_intervention_projet",
				"ID_PROVINCE,ID_COMMUNE,ID_DEMANDE_INFO_SUPP",
				"'{$ID_PROVINCE}', '{$commune}', '{$ID_DEMANDE_INFO_SUPP}'"
			);
		}
		return $result;		
	}

	/**
	 * Enregistrer les etudes et documents
	 * @return string|false
	 */
	public function storeEtudeDocument()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$file_path = null;
		if ($this->request->getFile('files')) 
		{
			$file_path = $this->uploadFile('files', 'pip', 'pip');
		}
		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$db = db_connect();
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$titre = $this->request->getPost("titre");
		$titre = $db->escapeString($titre);
		$date = $this->request->getPost("date");
		$statut = $this->request->getPost("statut");
		$auteur = $this->request->getPost("auteur");
		$auteur = $db->escapeString($auteur);
		$observation = $this->request->getPost("observation");
		$observation = $db->escapeString($observation);
		$statut_etude = $this->request->getPost('statut_etude');
		$adresse = $this->request->getPost('adresse') ?? null;
		$nif = $this->request->getPost('nif') ?? null;
		$registre = $this->request->getPost('registre') ?? null;
		$NATIONALITE_AUTEUR = $this->request->getPost('NATIONALITE_AUTEUR') ?? null;
		$NATIONALITE_ORGANISME = $this->request->getPost('NATIONALITE_ORGANISME') ?? null;
		$PAYS_ORIGINE = $this->request->getPost('PAYS_ORIGINE') ?? null;
		$NATIONALITE = 0;
		if ($NATIONALITE_AUTEUR) 
		{
			$NATIONALITE = $NATIONALITE_AUTEUR;
		}
		if ($NATIONALITE_ORGANISME) 
		{
			$NATIONALITE = $NATIONALITE_ORGANISME;
		}
		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$result = $this->save_all_table
			(
				"pip_etude_document_reference",
				"ID_DEMANDE_INFO_SUPP,TITRE_ETUDE,DOC_REFERENCE,DATE_REFERENCE,STATUT_JURIDIQUE,AUTEUR_ORGANISME,NATIONALITE,NIF_AUTEUR,REGISTRE_COMMERCIALE,STATUT_ETUDE,ADRESSE,COUNTRY_ID,OBSERVATION",
				"'{$ID_DEMANDE_INFO_SUPP}', '{$titre}', '{$file_path}','{$date}','{$statut}','{$auteur}','{$NATIONALITE}','{$nif}','{$registre}','{$statut_etude}','{$adresse}','{$PAYS_ORIGINE}','{$observation}'"
			);
		} 
		else 
		{
			$result = $this->save_all_table
			(
				"pip_etude_document_reference",
				"ID_DEMANDE_INFO_SUPP,TITRE_ETUDE,DOC_REFERENCE,DATE_REFERENCE,STATUT_JURIDIQUE,AUTEUR_ORGANISME,NATIONALITE,NIF_AUTEUR,REGISTRE_COMMERCIALE,STATUT_ETUDE,ADRESSE,COUNTRY_ID,OBSERVATION",
				"'{$INFO_SUP_ID}', '{$titre}', '{$file_path}','{$date}','{$statut}','{$auteur}','{$NATIONALITE}','{$nif}','{$registre}','{$statut_etude}','{$adresse}','{$PAYS_ORIGINE}','{$observation}'"
			);
		}
		$etude = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_etude_document_reference WHERE ID_ETUDE_DOC_REF=\"$result\"')");
		return json_encode($etude);
	}

	/**
	 * Enregistrer les cadres mesures des résultats
	 * @param string
	 * @return int
	 */
	public function storeCmr($slug)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$db = db_connect();
		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$indicateur_mesure = $this->request->getPost("indicateur_mesure");
		$cumulative = $this->request->getPost("cumulative");
		$indicateur_mesure = $db->escapeString($indicateur_mesure);
		$unite_mesure = $this->request->getPost("unite_mesure");
		$total3 = preg_replace('/\s/', '', $this->request->getPost('total3'));
		$reference = $this->request->getPost("reference");
		$values = [];
		$values[] = preg_replace('/\s/', '', $this->request->getPost('CRM_an1'));
		$values[] = preg_replace('/\s/', '', $this->request->getPost('CRM_an2'));
		$values[] = preg_replace('/\s/', '', $this->request->getPost('CRM_an3'));
		$annees = [];
		$annees[] = $this->request->getPost("CRM_livrable_annee_1");
		$annees[] = $this->request->getPost("CRM_livrable_annee_2");
		$annees[] = $this->request->getPost("CRM_livrable_annee_3");
		$total_trinal = 0;
		foreach ($values as $value) 
		{
			$total_trinal += (int)$value;
		}
		$ID_DEMANDE_INFO_SUPP = $ID_DEMANDE_INFO_SUPP ? $ID_DEMANDE_INFO_SUPP : $INFO_SUP_ID;
		$ID_INDICATEUR_MESURE = $this->save_all_table("pip_indicateur_mesure", "INDICATEUR_MESURE", "'{$indicateur_mesure}'");
		if ($slug == 'livrable') 
		{
			$table = 'pip_cadre_mesure_resultat_livrable';
			$INSERT = $this->request->getPost('INSERT');
			$columns = "
			ID_UNITE_MESURE,
			ID_INDICATEUR_MESURE,
			ID_DEMANDE_INFO_SUPP,
			TOTAL_TRIENNAL,
			ID_LIVRABLE,
			CUMULATIVE_ID,
			VALEUR_REFERENCE
			";
			$data = "
			'{$unite_mesure}',
			'{$ID_INDICATEUR_MESURE}',
			'{$ID_DEMANDE_INFO_SUPP}', 
			'{$total3}',
			'{$INSERT}',
			'{$cumulative}',
			'{$reference}'";
		}

		$result = $this->save_all_table
		(
			$table,
			$columns,
			$data
		);
		foreach ($annees as $key => $annee) 
		{
			$this->save_all_table
			(
				"cadre_mesure_resultat_valeur_cible",
				"ANNEE_BUDGETAIRE_ID,VALEUR_ANNEE_CIBLE,ID_CADRE_MESURE_RESULTAT_LIVRABLE",
				"'{$annee}','{$values[$key]}','{$result}'"
			);
		}
		return $result;
	}

	/**
	 * Enregistrer le budget projet par livrable
	 * @return int
	 */
	public function storeBpl()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$BPL_livrable = $this->request->getPost("BPL_livrable");
		$cout_unitaire = $this->request->getPost("cout_unitaire");
		$nomenclature = $this->request->getPost("nom_menclature");
		$values = [];
		$values[] = $this->request->getPost("year_un");
		$values[] = $this->request->getPost("year_deux");
		$values[] = $this->request->getPost("year_trois");
		$annees = [];
		$annees[] = $this->request->getPost("annee_bpl_1");
		$annees[] = $this->request->getPost("annee_bpl_2");
		$annees[] = $this->request->getPost("annee_bpl_3");
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost("ID_DEMANDE_INFO_SUPP");
		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$projet_livrable = $this->save_all_table
			(
				"pip_budget_projet_livrable",
				"
				ID_DEMANDE_LIVRABLE,
				COUT_UNITAIRE_BIF,
				ID_DEMANDE_INFO_SUPP
				",
				"
				'{$BPL_livrable}',
				'{$cout_unitaire}',
				'{$ID_DEMANDE_INFO_SUPP}'
				"
			);
		} 
		else 
		{
			$projet_livrable = $this->save_all_table
			(
				"pip_budget_projet_livrable",
				"
				ID_DEMANDE_LIVRABLE,
				COUT_UNITAIRE_BIF,
				ID_DEMANDE_INFO_SUPP
				",
				"
				'{$BPL_livrable}',
				'{$cout_unitaire}',
				'{$INFO_SUP_ID}'
				"
			);
		}
		$total_triennal = 0;
		foreach ($values as $value) 
		{
			$total_triennal += $value;
		}
		$result = $this->save_all_table
		(
			"pip_budget_livrable_nomenclature_budgetaire",
			"
			ID_PROJET_LIVRABLE,
			ID_NOMENCLATURE,
			TOTAL_DUREE_PROJET,
			TOTAL_TRIENNAL
			",
			"
			'{$projet_livrable}',
			'{$nomenclature}',
			'0',
			'{$total_triennal}'
			"
		);
		foreach ($annees as $key => $annee) 
		{
			$this->save_all_table
			(
				"pip_budget_projet_livrable_valeur_cible",
				"
				ANNEE_BUDGETAIRE_ID,
				VALEUR_ANNEE_CIBLE,
				ID_BUDGET_LIVRABLE_NOMEN
				",
				"
				'{$annee}',
				'{$values[$key]}',
				'{$result}'
				"
			);
		}
		return $result;
	}

	/**
	 * fonction pour stocker les risque du projet 
	 * @param string $slug
	 * @return int
	 */
	public function save_risque_projet($slug)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$db = db_connect();
		$nom_risque = $this->request->getPost("nom_risque");
		$nom_mesure_mitigation = $this->request->getPost("nom_mesure_mitigation");
		$nom_risque = $db->escapeString($nom_risque);
		$nom_mesure_mitigation = $db->escapeString($nom_mesure_mitigation);
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost("ID_DEMANDE_INFO_SUPP");
		if ($slug == 'risque_projet') 
		{
			$table = 'pip_risques_projet';
			$result = $this->save_all_table($table, "RISQUE_PROJET,MESURE_RISQUE,ID_DEMANDE_INFO_SUPP", "'{$nom_risque}','{$nom_mesure_mitigation}','{$ID_DEMANDE_INFO_SUPP}'");
		}
		return $result;
	}

	/**
	 * Enregistrer les risques par impact
	 * @param string $slug
	 * @return int
	 */
	public function storeRisque(string $slug)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else
		{
			$INFO_SUP_ID = 1;
		}
		$db = db_connect();
		$nom_risque = $this->request->getPost("nom_risque");
		$nom_mesure_mitigation = $this->request->getPost("nom_mesure_mitigation");
		$nom_risque = $db->escapeString($nom_risque);
		$nom_mesure_mitigation = $db->escapeString($nom_mesure_mitigation);
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost("ID_DEMANDE_INFO_SUPP");
		if ($slug == 'environnement') 
		{
			$table = 'pip_risques_impact_environnement';
		} 
		else if ($slug == 'genre') 
		{
			$table = 'pip_risques_impact_genre';
		} 
		else if ($slug == 'risque_projet') 
		{
			$table = 'pip_risques_projet';
		}

		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$result = $this->save_all_table
			(
				$table,
				"
				NOM_RISQUE,
				MESURE_RISQUE,
				ID_DEMANDE_INFO_SUPP 
				",
				"
				'{$nom_risque}',
				'{$nom_mesure_mitigation}',
				'{$ID_DEMANDE_INFO_SUPP}'
				"
			);
		} 
		else 
		{
			$result = $this->save_all_table
			(
				$table,
				"
				NOM_RISQUE,
				MESURE_RISQUE,
				ID_DEMANDE_INFO_SUPP 
				",
				"
				'{$nom_risque}',
				'{$nom_mesure_mitigation}',
				'{$INFO_SUP_ID}'
				"
			);
		}
		return $result;
	}

	// Enregistrer les livrables (CMR)
	public function storeLivrable()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$db = db_connect();
		$DESCR_LIVRABLE = $this->request->getPost('DESCR_LIVRABLE');
		$COUT_LIVRABLE = preg_replace('/\s/', '', $this->request->getPost('COUT_LIVRABLE'));
		$DESCR_OBJECTIF = $this->request->getPost('DESCR_OBJECTIF');
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$DESCR_LIVRABLE = $db->escapeString($DESCR_LIVRABLE);
		$DESCR_OBJECTIF = $db->escapeString($DESCR_OBJECTIF);
		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$result = $this->save_all_table
			(
				"pip_demande_livrable",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_LIVRABLE,
				COUT_LIVRABLE,
				OBJECTIF_SPECIFIQUE
				",
				"
				'{$ID_DEMANDE_INFO_SUPP}',
				'{$DESCR_LIVRABLE}',
				'{$COUT_LIVRABLE}',
				'{$DESCR_OBJECTIF}'
				"
			);
		} 
		else 
		{
			$result = $this->save_all_table
			(
				"pip_demande_livrable",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_LIVRABLE,
				COUT_LIVRABLE,
				OBJECTIF_SPECIFIQUE
				",
				"
				'{$INFO_SUP_ID}',
				'{$DESCR_LIVRABLE}',
				'{$COUT_LIVRABLE}',
				'{$DESCR_OBJECTIF}'
				"
			);
		}
		return $result;
	}

	// Enregistrer les objectifs (CMR)
	public function storeObjectif()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/do_logout');
    }

		$db = db_connect();
		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$DESCR_OBJECTIF = $this->request->getPost('DESCR_OBJECTIF');
		$DESCR_LIVRABLE = $this->request->getPost('DESCR_LIVRABLE');
		$COUT_LIVRABLE = $this->request->getPost('COUT_LIVRABLE');
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$DESCR_LIVRABLE = $db->escapeString($DESCR_LIVRABLE);
		$DESCR_OBJECTIF = $db->escapeString($DESCR_OBJECTIF);
		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$this->save_all_table
			(
				"pip_demande_objectif_specifique",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_OBJECTIF
				",
				"
				'{$ID_DEMANDE_INFO_SUPP}',
				'{$DESCR_OBJECTIF}'
				"
			);
			$this->save_all_table
			(
				"pip_demande_livrable",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_LIVRABLE,
				COUT_LIVRABLE
				",
				"
				'{$ID_DEMANDE_INFO_SUPP}',
				'{$DESCR_LIVRABLE}',
				'{$COUT_LIVRABLE}'
				"
			);
			$result = $ID_DEMANDE_INFO_SUPP;
		} 
		else 
		{
			$this->save_all_table
			(
				"pip_demande_objectif_specifique",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_OBJECTIF
				",
				"
				'{$INFO_SUP_ID}',
				'{$DESCR_OBJECTIF}'
				"
			);
			$this->save_all_table
			(
				"pip_demande_livrable",
				"
				ID_DEMANDE_INFO_SUPP,
				DESCR_LIVRABLE,
				COUT_LIVRABLE
				",
				"
				'{$INFO_SUP_ID}',
				'{$DESCR_LIVRABLE}',
				'{$COUT_LIVRABLE}'
				"
			);
			$result = $INFO_SUP_ID;
		}
		return $result;
	}

	// Enregistrer les sources de financement
	public function storeSFP()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data['processus'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE 1')");
		$process = end($data['processus']);
		if ($process) 
		{
			$INFO_SUP_ID = (int)$process->ID_DEMANDE_INFO_SUPP + 1;
		} 
		else 
		{
			$INFO_SUP_ID = 1;
		}
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$bailleur = $this->request->getPost('SFP_bailleur');
		$total_financement = $this->request->getPost('total_financement');
		$total_financement = preg_replace('/\s/', '', $this->request->getPost('total_financement'));
		$totalBIF = preg_replace('/\s/', '', $this->request->getPost('totalBIF'));
		
		$values = [];
		$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an1'));
		$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an2'));
		$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an3'));
		$annees = [];
		$annees[] = $this->request->getPost('annee_sfp_1');
		$annees[] = $this->request->getPost('annee_sfp_2');
		$annees[] = $this->request->getPost('annee_sfp_3');
		$devise = $this->request->getPost('DEVISE');
		$total = 0;
		foreach ($values as $value) 
		{
			$total += $value;
		}

		if ($ID_DEMANDE_INFO_SUPP) 
		{
			$result = $this->save_all_table
			(
				'pip_demande_source_financement',
				'
				ID_DEMANDE_INFO_SUPP,
				ID_SOURCE_FINANCE_BAILLEUR,
				TOTAL_DUREE_PROJET,
				TOTAL_FINANCEMENT,
				TOTAL_BIF,
				TOTAL_TRIENNAL,
				TAUX_ECHANGE_ID
				',
				"
				'{$ID_DEMANDE_INFO_SUPP}',
				'{$bailleur}',
				'0',
				'{$total_financement}',
				'{$totalBIF}',
				'{$total}',
				'{$devise}'
				"
			);
		} 
		else 
		{
			$result = $this->save_all_table
			(
				'pip_demande_source_financement',
				'
				ID_DEMANDE_INFO_SUPP,
				ID_SOURCE_FINANCE_BAILLEUR,
				TOTAL_DUREE_PROJET,
				TOTAL_FINANCEMENT,
				TOTAL_BIF,
				TOTAL_TRIENNAL,
				TAUX_ECHANGE_ID
				',
				"
				'{$INFO_SUP_ID}',
				'{$bailleur}',
				'0',
				'{$total_financement}',
				'{$totalBIF}',
				'{$total}',
				'{$devise}'
				"
			);
		}
		foreach ($annees as $key => $annee) 
		{
			$this->save_all_table
			(
				'pip_demande_source_financement_valeur_cible',
				'
				ANNEE_BUDGETAIRE_ID,
				SOURCE_FINANCEMENT_VALEUR_CIBLE,
				ID_DEMANDE_SOURCE_FINANCEMENT	
				',
				"
				'{$annee}',
				'{$values[$key]}',
				'{$result}'
				"
			);
		}
		return $result;
	}

	// Enregistrer l'étape en cours
	public function storeStep($slug)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$db = db_connect();
		if ($slug == 'intervention') 
		{
			$EST_REALISE_NATIONAL = $this->request->getPost('EST_REALISE_NATIONAL');
			if ($EST_REALISE_NATIONAL == '1')
			{
				$table = "pip_lieu_intervention_projet";
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
			}
			$dataToUpdate = "EST_REALISE_NATIONAL='" . $EST_REALISE_NATIONAL . "'";
		} 
		else if ($slug == 'etude_document') 
		{
			$A_UNE_ETUDE = $this->request->getPost('A_UNE_ETUDE');
			if ($A_UNE_ETUDE == '0') 
			{
				$table = "pip_etude_document_reference";
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
			}
			$dataToUpdate = "A_UNE_ETUDE='" . $A_UNE_ETUDE . "'";
		} 
		else if ($slug == 'contexte') 
		{
			$PATH_CONTEXTE_JUSTIFICATION = $this->request->getPost('PATH_CONTEXTE_JUSTIFICATION');
			$OBJECTIF_GENERAL = $this->request->getPost('OBJECTIF_GENERAL');
			$BENEFICIAIRE_PROJET = $this->request->getPost('BENEFICIAIRE_PROJET');
			$PATH_CONTEXTE_JUSTIFICATION = $db->escapeString($PATH_CONTEXTE_JUSTIFICATION);
			$OBJECTIF_GENERAL = $db->escapeString($OBJECTIF_GENERAL);
			$BENEFICIAIRE_PROJET = $db->escapeString($BENEFICIAIRE_PROJET);
			$critere = "ID_DEMANDE_INFO_SUPP=" . $ID_DEMANDE_INFO_SUPP;
			$dataToUpdate = "PATH_CONTEXTE_JUSTIFICATION='" . $PATH_CONTEXTE_JUSTIFICATION . "',OBJECTIF_GENERAL='" . $OBJECTIF_GENERAL . "',BENEFICIAIRE_PROJET='" . $BENEFICIAIRE_PROJET . "'";
		} 
		else if ($slug == 'impact') 
		{
			$A_UNE_IMPACT_ENV = $this->request->getPost('A_UNE_IMPACT_ENV');
			$A_UNE_IMPACT_GENRE = $this->request->getPost('A_UNE_IMPACT_GENRE');
			$dataToUpdate = "A_UNE_IMPACT_ENV='$A_UNE_IMPACT_ENV',A_UNE_IMPACT_GENRE='$A_UNE_IMPACT_GENRE'";
			if ($A_UNE_IMPACT_ENV == '1') 
			{
				$IMPACT_ATTENDU_ENVIRONNEMENT = $this->request->getPost('IMPACT_ATTENDU_ENVIRONNEMENT');
				$IMPACT_ATTENDU_ENVIRONNEMENT = $db->escapeString($IMPACT_ATTENDU_ENVIRONNEMENT);
				$dataToUpdate .= ",IMPACT_ATTENDU_ENVIRONNEMENT='$IMPACT_ATTENDU_ENVIRONNEMENT'";
			} 
			else 
			{
				$table = 'pip_risques_impact_environnement';
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
			}
			if ($A_UNE_IMPACT_GENRE == '1') 
			{
				$IMPACT_ATTENDU_GENRE = $this->request->getPost('IMPACT_ATTENDU_GENRE');
				$IMPACT_ATTENDU_GENRE = $db->escapeString($IMPACT_ATTENDU_GENRE);
				$dataToUpdate .= ",IMPACT_ATTENDU_GENRE='$IMPACT_ATTENDU_GENRE'";
			} 
			else 
			{
				$table = 'pip_risques_impact_genre';
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
			}
		} 
		else if ($slug == 'source_financement') 
		{
			$EST_CO_FINANCE = $this->request->getPost('EST_CO_FINANCE');
			$dataToUpdate = "EST_CO_FINANCE='" . $EST_CO_FINANCE . "'";
			if ($EST_CO_FINANCE == '0') 
			{
				$table = "pip_demande_source_financement";
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
				$bailleur = $this->request->getPost('bailleur');
				$devise_financement = $this->request->getPost('devise_financement');
				$total_financement= preg_replace('/\s/', '', $this->request->getPost('total_financement'));
				$total_financement_bif= preg_replace('/\s/', '', $this->request->getPost('total_financement_bif'));
				$values = [];
				$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an1'));
				$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an2'));
				$values[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an3'));
				$annees = [];
				$annees[] = $this->request->getPost('annee_sfp_1');
				$annees[] = $this->request->getPost('annee_sfp_2');
				$annees[] = $this->request->getPost('annee_sfp_3');
				$devise = $this->request->getPost('devise_financement');
				$total = 0;
				foreach($values as $value)
				{
					$total +=$value;
				}
				$dataToUpdateSource = "ID_DEMANDE_INFO_SUPP='$ID_DEMANDE_INFO_SUPP',ID_SOURCE_FINANCE_BAILLEUR='$bailleur',TOTAL_TRIENNAL='$total_financement',TAUX_ECHANGE_ID='$devise_financement'";
				$max = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_source_financement WHERE ID_DEMANDE_INFO_SUPP=\"$ID_DEMANDE_INFO_SUPP\"')");
				if ($max) 
				{
					$critere = "ID_DEMANDE_INFO_SUPP=" . $ID_DEMANDE_INFO_SUPP;
					$bindProjetSource = ["pip_demande_source_financement", $dataToUpdateSource, $critere];
					$updateRequest = "CALL `updateData`(?,?,?);";
					$result = $this->ModelPs->createUpdateDelete($updateRequest, $bindProjetSource);
					// Mettre à jour les valeurs cibles
					foreach ($annees as $key => $annee) 
					{
						$anneeCibleCritere = "ID_DEMANDE_SOURCE_FINANCEMENT=" . $result . " AND ANNEE_BUDGETAIRE_ID=" . $annee;
						$valeurCible = "SOURCE_FINANCEMENT_VALEUR_CIBLE=" . $values[$key];

						$bindProjetSource = ["pip_demande_source_financement_valeur_cible", $valeurCible, $anneeCibleCritere];

						$this->ModelPs->createUpdateDelete($updateRequest, $bindProjetSource);
					}
				} 
				else 
				{
					$result = $this->save_all_table(
						'pip_demande_source_financement',
						'
						ID_DEMANDE_INFO_SUPP,
						ID_SOURCE_FINANCE_BAILLEUR,
						TOTAL_DUREE_PROJET,
						TOTAL_FINANCEMENT,
						TOTAL_BIF,
						TOTAL_TRIENNAL,
						TAUX_ECHANGE_ID
						',
						"
						'{$ID_DEMANDE_INFO_SUPP}',
						'{$bailleur}',
						'0',
						'{$total_financement}',
						'{$total_financement_bif}',
						'{$total}',
						'{$devise}'
						"
					);
					foreach ($annees as $key => $annee) 
					{
						$this->save_all_table
						(
							"pip_demande_source_financement_valeur_cible",
							"
							ANNEE_BUDGETAIRE_ID,
							SOURCE_FINANCEMENT_VALEUR_CIBLE,
							ID_DEMANDE_SOURCE_FINANCEMENT
							",
							"
							'{$annee}',
							'{$values[$key]}',
							'{$result}'
							"
						);
					}
				}
			}
		} 
		else if ($slug == 'risque_projet') 
		{
			$risque = $this->request->getPost('RISQUE_PROJET');
			$dataToUpdate = "RISQUE_PROJET='" . $risque . "'";
			if ($risque == '0') 
			{
				$table = "pip_risques_projet";
				$id = $ID_DEMANDE_INFO_SUPP;
				$this->deleteStored($table, $id);
			}
		}
		$critere = "ID_DEMANDE_INFO_SUPP=" . $ID_DEMANDE_INFO_SUPP;
		$bindProjet = ["pip_demande_infos_supp", $dataToUpdate, $critere];
		$updateRequest = "CALL `updateData`(?,?,?);";
		$this->ModelPs->createUpdateDelete($updateRequest, $bindProjet);
	}

	// Sélectionner le coût du livrable
	public function cout_livrable(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_livrable WHERE ID_DEMANDE_LIVRABLE=\'$id\'')");
		return $data[0]->COUT_LIVRABLE;
	}

	// Sélectionner l'objectif spécifique
	public function objectif_specifique(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$objectifs = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_objectif_specifique WHERE ID_DEMANDE_INFO_SUPP={$id}')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($objectifs as $objectif) 
		{
			$html .= "<option value='{$objectif->ID_DEMANDE_OBJECTIF}'>{$objectif->DESCR_OBJECTIF}</option>";
		}
		return json_encode($html);
	}

	// Récuperer les pays 
	public function pays()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$pays = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM countries')");
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($pays as $p) 
		{
			$html .= "<option value='{$p->COUNTRY_ID}'>{$p->CommonName}</option>";
		}
		return json_encode($html);
	}

	// Vérifier si le lieu existe déjà
	public function lieuIntervention()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$province = $this->request->getPost('province');
		$commune = $this->request->getPost('commune');
		$ID_DEMANDE_INFO_SUPP = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		if (is_string($commune)) 
		{
			$commune = explode(',', $commune);
		}
		foreach ($commune as $c) 
		{
			$query = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_lieu_intervention_projet WHERE ID_COMMUNE={$c} AND ID_PROVINCE={$province} AND ID_DEMANDE_INFO_SUPP={$ID_DEMANDE_INFO_SUPP}')");
			if (count($query)) 
			{
				return $this->response->setJSON([$query[0]->ID_PROVINCE, $query[0]->ID_COMMUNE]);
			}
		}
	}

	/**
	 * Récuperer les livrables et enregistrer les montants par nomenclature et livrables
	 * @param int $id
	 * @param string $slug
	 * @return JSON
	 */
	public function livrable(int $id, string $slug = "")
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$livrables = $this->ModelPs->getRequete("CALL getTable('SELECT * FROM pip_demande_livrable WHERE ID_DEMANDE_INFO_SUPP={$id}')");
		if ($slug == "cmr") 
		{
			foreach ($livrables as $key => $livrable) 
			{
				$resultat = $this->ModelPs->getRequete("CALL getTable('SELECT * FROM pip_cadre_mesure_resultat_livrable WHERE ID_LIVRABLE={$livrable->ID_DEMANDE_LIVRABLE}')");
				if (count($resultat)) 
				{
					unset($livrables[$key]);
				}
			}
		}
		$select = lang('messages_lang.label_selecte');
		$html = "<option selected disabled value=''>{$select}</option>";
		foreach ($livrables as $livrable) 
		{
			$html .= "<option value='{$livrable->ID_DEMANDE_LIVRABLE}'>{$livrable->DESCR_LIVRABLE}</option>";
		}
		if ($slug == 'bpl') 
		{
			$recuperation_livrable = $this->ModelPs->getRequete("CALL getTable('SELECT pip_demande_livrable.COUT_LIVRABLE,ID_CADRE_MESURE_RESULTAT_LIVRABLE FROM pip_cadre_mesure_resultat_livrable JOIN pip_demande_livrable on pip_demande_livrable.ID_DEMANDE_LIVRABLE = pip_cadre_mesure_resultat_livrable.ID_LIVRABLE WHERE  pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP={$id}')");
			if (!empty($recuperation_livrable)) 
			{
				foreach ($recuperation_livrable as $recup)
				{
					$ID_CADRE_MESURE_RESULTAT_LIVRABLE = $recup->ID_CADRE_MESURE_RESULTAT_LIVRABLE;
					$COUT_LIVRABLE = $recup->COUT_LIVRABLE;
					$livrableinfo = $this->ModelPs->getRequete("CALL getTable('SELECT ANNEE_BUDGETAIRE_ID,VALEUR_ANNEE_CIBLE FROM cadre_mesure_resultat_valeur_cible WHERE ID_CADRE_MESURE_RESULTAT_LIVRABLE={$recup->ID_CADRE_MESURE_RESULTAT_LIVRABLE}  order by ANNEE_BUDGETAIRE_ID desc')");
					if (!empty($livrableinfo)) 
					{
						foreach ($livrableinfo as $infoliv) 
						{
							$ANNEE_BUDGETAIRE_ID = $infoliv->ANNEE_BUDGETAIRE_ID;
							$VALEUR_ANNEE_CIBLE = $infoliv->VALEUR_ANNEE_CIBLE;
							$this->get_nomenclature($ID_CADRE_MESURE_RESULTAT_LIVRABLE, $ANNEE_BUDGETAIRE_ID, $VALEUR_ANNEE_CIBLE, $COUT_LIVRABLE);
						}
					}
				}
			}
		}
		return json_encode($html);
	}

	/**
	 * Enregistrer les montants par nomenclature et livrables
	 * @param int $ID_CADRE_MESURE_RESULTAT_LIVRABLE
	 * @param int $ANNEE_BUDGETAIRE_ID
	 * @param $VALEUR_ANNEE_CIBLE
	 * @param $COUT_LIVRABLE
	 * @return void
	 */
	public function get_nomenclature($ID_CADRE_MESURE_RESULTAT_LIVRABLE, $ANNEE_BUDGETAIRE_ID, $VALEUR_ANNEE_CIBLE, $COUT_LIVRABLE)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$nomenclatures = $this->ModelPs->getRequete("CALL getTable('SELECT pip_nomenclature_budgetaire.ID_NOMENCLATURE,POURCENTAGE_NOMENCLATURE,DESCR_NOMENCLATURE FROM `pip_nomenclature_budgetaire`join pip_nomenclature_budgetaire_pourcentage on  pip_nomenclature_budgetaire_pourcentage.ID_NOMENCLATURE=pip_nomenclature_budgetaire.ID_NOMENCLATURE WHERE 1')");
		if (!empty($nomenclatures)) 
		{
			foreach ($nomenclatures as $nomen) 
			{
				$callpsreq = "CALL `getRequete`(?,?,?,?);";
				$table = "pip_valeur_nomenclature_livrable";
				$columnselect = "*";
				$where = "ID_CADRE_MESURE_RESULTAT_LIVRABLE='" . $ID_CADRE_MESURE_RESULTAT_LIVRABLE . "' and ANNEE_BUDGETAIRE_ID='" . $ANNEE_BUDGETAIRE_ID . "' and ID_NOMENCLATURE='" . $nomen->ID_NOMENCLATURE . "'";
				$orderby = 'ID_CADRE_MESURE_RESULTAT_LIVRABLE DESC';
				$where = str_replace("\'", "'", $where);
				$db = db_connect();
				$bindparamsss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
				$bindparams34s = str_replace("\'", "'", $bindparamsss);
				$nomenclature_livrables = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34s);
				if (empty($nomenclature_livrables)) 
				{
					$valeur_nomenclature = ($COUT_LIVRABLE * $VALEUR_ANNEE_CIBLE * floatval($nomen->POURCENTAGE_NOMENCLATURE)) / 100;
					$this->save_all_table
					(
						"pip_valeur_nomenclature_livrable",
						"ID_CADRE_MESURE_RESULTAT_LIVRABLE,ANNEE_BUDGETAIRE_ID,ID_NOMENCLATURE,MONTANT_NOMENCALTURE,POURCENTAGE_NOMCALTURE",
						"'{$ID_CADRE_MESURE_RESULTAT_LIVRABLE}', '{$ANNEE_BUDGETAIRE_ID}', '{$nomen->ID_NOMENCLATURE}', '{$valeur_nomenclature}','{$nomen->POURCENTAGE_NOMENCLATURE}'"
					);
				}
			}
		}
	}

	/**
	 * Enregistrement des informations saisies et redirection
	 * @return void
	 */
	public function store()
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$session = \Config\Services::session();
			if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }
			$db = db_connect();
			$titreEtudeDocument = $this->request->getPost('TITRE_ETUDE');
			$OBJECTIF_GENERAL = $this->request->getPost('OBJECTIF_GENERAL');
			$OBJECTIF_GENERAL = $db->escapeString($OBJECTIF_GENERAL);
			$titreEtudeDocument = $db->escapeString($titreEtudeDocument);
			$ID_DEMANDE = $this->request->getPost('demande_id');
			$observation = $this->request->getPost('OBSERVATION_COMPLEMENTAIRE');
			$observation = $db->escapeString($observation);
			$critere = "ID_DEMANDE_INFO_SUPP=" . $ID_DEMANDE;
			$dataToUpdate = "OBSERVATION_COMPLEMENTAIRE='$observation' ,IS_FINISHED='1'";
			$bindProjet = ["pip_demande_infos_supp", $dataToUpdate, $critere];
			$updateRequest = "CALL `updateData`(?,?,?);";
			$INFO_SUP_ID = $this->ModelPs->createUpdateDelete($updateRequest, $bindProjet);
			$verifiable = ['projet', 'libelle', 'indicateur_mesure', 'unite_mesure', 'CRM_an1', 'CRM_an2', 'CRM_an3', 'BPL_livrable', 'cout_unitaire', 'SFP_livrable', 'bailleur', 'SFP_an1', 'SFP_an2', 'SFP_an3'];
			$rules = [];
			$msg = lang('messages_lang.error_sms');
			foreach ($verifiable as $v) 
			{
				$rules[$v] = [
					'rules' => 'required',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.$msg.'</font>'
					]
				];
			}
			$this->validation->setRules($rules);
			if ($this->validation->withRequest($this->request)->run()) 
			{
				$libelle = $this->request->getPost('libelle');
				$indicateur_mesure = $this->request->getPost('indicateur_mesure');
				$unite_mesure = $this->request->getPost('unite_mesure');
				$reference = $this->request->getPost('reference') ?? 0;
				$CRM_an1 = $this->request->getPost('CRM_an1');
				$CRM_an2 = $this->request->getPost('CRM_an2');
				$CRM_an3 = $this->request->getPost('CRM_an3');
				$BPL_livrable = $this->request->getPost('BPL_livrable');
				$cout_unitaire = $this->request->getPost('cout_unitaire');
				$SFP_livrable = $this->request->getPost('SFP_livrable');
				$bailleur = $this->request->getPost('bailleur');
				$SFP_an1 = $this->request->getPost('SFP_an1');
				$SFP_an2 = $this->request->getPost('SFP_an2');
				$SFP_an3 = $this->request->getPost('SFP_an3');

				$tables = ['pip_cadre_mesure_resultat', 'pip_budget_projet_livrable', 'pip_demande_infos_supp', 'pip_demande_source_financement'];
				$insertRequest = "CALL `insertIntoTable`(?,?);";
				$updateRequest = "CALL `updateData`(?,?,?);";
				$critere = "ID_DEMANDE_INFO_SUPP=" . $INFO_SUP_ID;
			}
			$query1 = "SELECT * FROM proc_process WHERE `NOM_PROCESS` LIKE '%PROCESSUS DE PROGRAMME D''INVESTISSEMENT PUBLIC%' LIMIT 1";
			$process = $this->ModelPs->getRequete('CALL `getTable`("' . $query1 . '")');
			$etape_id = $this->ModelPs->getRequete('CALL `getTable`("SELECT etape.ETAPE_ID,action.ACTION_ID FROM proc_etape etape JOIN proc_actions action ON etape.ETAPE_ID=action.ETAPE_ID WHERE etape.PROCESS_ID=' . $process[0]->PROCESS_ID . ' ORDER BY etape.ETAPE_ID ASC LIMIT 1")');
			$info = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_infos_supp WHERE ID_DEMANDE_INFO_SUPP=\"$ID_DEMANDE\"')");
			$insertId = $info[0]->ID_DEMANDE;
			$user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$this->save_all_table
			(
				"proc_demandes_historique",
				"ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID,COMMENTAIRE",
				"'{$insertId}','{$etape_id[0]->ETAPE_ID}','{$user_id}','{$etape_id[0]->ACTION_ID}','Enregistrement de la demande'"
			);
		}
	}

	/**
	 * Récuperer les cadres de mesure des résultats des livrables
	 * @param int $id
	 * @return array
	 */
	public function cmr_livrable(int $id)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$livrables = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_budget_projet_livrable WHERE ID_DEMANDE_LIVRABLE=\"$id\"')");
		return $livrables;
	}

	// Afficher les informations déjà enregistrer sur le formulaire pour la mise à jour
	public function update($id)
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$session = \Config\Services::session();
			if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }

			$data = $this->urichk();
			$data['title'] = "Correction de la fiche de projet";
			$query = "SELECT info.ID_DEMANDE_INFO_SUPP,info.NOM_PROJET,info.NUMERO_PROJET,info.DATE_DEBUT_PROJET,info.DATE_FIN_PROJET,info.DUREE_PROJET,info.EST_REALISE_NATIONAL,info.PATH_CONTEXTE_JUSTIFICATION,info.OBJECTIF_GENERAL,info.BENEFICIAIRE_PROJET,info.IMPACT_ATTENDU_ENVIRONNEMENT,info.IMPACT_ATTENDU_GENRE,info.OBSERVATION_COMPLEMENTAIRE,info.DATE_PREPARATION_FICHE_PROJET,info.A_UNE_ETUDE,info.EST_CO_FINANCE,info.A_UNE_IMPACT_ENV,info.A_UNE_IMPACT_GENRE,info.RISQUE_PROJET,info.IS_FINISHED,
				demande.CODE_DEMANDE,demande.ID_DEMANDE,
				stat.ID_STATUT_PROJET,stat.DESCR_STATUT_PROJET,
				inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,
				pilier.ID_PILIER,pilier.DESCR_PILIER,
				obj.ID_OBJECT_STRATEGIQUE,obj.DESCR_OBJECTIF_STRATEGIC,
				pnd.ID_OBJECT_STRATEGIC_PND,pnd.DESCR_OBJECTIF_STRATEGIC_PND,
				axe.ID_AXE_INTERVENTION_PND,axe.DESCR_AXE_INTERVATION_PND,
				prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,
				act.ACTION_ID,act.LIBELLE_ACTION,act.CODE_ACTION,
				info.ID_PROGRAMME_PND,programme.DESCR_PROGRAMME
				FROM pip_demande_infos_supp info JOIN proc_demandes demande ON info.ID_DEMANDE = demande.ID_DEMANDE
				JOIN pip_statut_projet stat ON info.ID_STATUT_PROJET = stat.ID_STATUT_PROJET
				JOIN inst_institutions inst ON info.INSTITUTION_ID = inst.INSTITUTION_ID
				JOIN pilier ON info.ID_PILIER = pilier.ID_PILIER
				JOIN objectif_strategique obj ON info.ID_OBJECT_STRATEGIQUE = obj.ID_OBJECT_STRATEGIQUE
				JOIN objectif_strategique_pnd pnd ON info.ID_OBJECT_STRATEGIC_PND = pnd.ID_OBJECT_STRATEGIC_PND
				JOIN axe_intervention_pnd axe ON info.ID_AXE_INTERVENTION_PND = axe.ID_AXE_INTERVENTION_PND
				JOIN inst_institutions_programmes prog ON info.ID_PROGRAMME = prog.PROGRAMME_ID
				JOIN inst_institutions_actions act ON info.ID_ACTION = act.ACTION_ID
				JOIN programme_pnd programme ON info.ID_PROGRAMME_PND = programme.ID_PROGRAMME_PND
				WHERE info.ID_DEMANDE = " . $id;
			$data['oldValues'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query . '")');
			$query5 = "SELECT * FROM proc_etape WHERE PROCESS_ID='1' ORDER BY ETAPE_ID ASC LIMIT 1";
			$requete5 = 'CALL `getList`("' . $query5 . '")';
			$data['first'] = $this->ModelPs->datatable($requete5);
			$data['demandes'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT proc_demandes.ID_DEMANDE, user_users.NOM, user_users.PRENOM, proc_demandes.ETAPE_ID FROM proc_demandes INNER JOIN user_users ON proc_demandes.USER_ID = user_users.USER_ID WHERE proc_demandes.ID_DEMANDE={$id}')");
			
			if(count($data['oldValues']) && count($data['demandes']) && count($data['first']))
			{
				if (($data['oldValues'][0]->IS_FINISHED == '0' || $data['demandes'][0]->ETAPE_ID == $data['first'][0]->ETAPE_ID) || ($data['oldValues'][0]->IS_FINISHED == '1')) 
				{
					$oldLieux = $this->ModelPs->getRequete('CALL getTable("SELECT * FROM pip_lieu_intervention_projet lieu JOIN provinces ON lieu.ID_PROVINCE=provinces.PROVINCE_ID JOIN communes ON lieu.ID_COMMUNE=communes.COMMUNE_ID WHERE lieu.ID_DEMANDE_INFO_SUPP = ' . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP . '")');
					$provinces = $this->ModelPs->getRequete('CALL getTable("SELECT DISTINCT lieu.ID_PROVINCE,provinces.PROVINCE_NAME,COUNT(lieu.ID_COMMUNE) nbr_communes FROM pip_lieu_intervention_projet lieu JOIN provinces ON provinces.PROVINCE_ID=lieu.ID_PROVINCE WHERE lieu.ID_DEMANDE_INFO_SUPP=' . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP.' GROUP BY lieu.ID_PROVINCE")');
					$lieux = [];
					
					foreach ($provinces as $province) 
					{
						$lieux[$province->PROVINCE_NAME] = $province;
					}
					$data['lieux'] = $lieux;
					$query2 = "SELECT * FROM pip_etude_document_reference WHERE ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['references'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query2 . '")');
					$query3 = "SELECT * FROM pip_cadre_mesure_resultat crm WHERE ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['crm'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query3 . '")');
					$query4 = "SELECT * FROM pip_demande_infos_supp";
					$data['infos'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query4 . '")');
					$query5 = "SELECT * FROM pip_categorie_libelle";
					$data['categories'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query5 . '")');
					$crm_objectif_general_query = "SELECT * FROM pip_cadre_mesure_resultat_objectif_general cmr 
						JOIN pip_indicateur_mesure ON cmr.ID_INDICATEUR_MESURE = pip_indicateur_mesure.ID_INDICATEUR_MESURE 
						JOIN unite_mesure ON cmr.ID_UNITE_MESURE=unite_mesure.ID_UNITE_MESURE
						WHERE ID_DEMANDE_INFO_SUPP=\'{$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}\'";
					$data['cmr_objectif_general'] = $this->ModelPs->getRequete("CALL `getTable`('{$crm_objectif_general_query}')");
					$crm_objectif_specifique_query = "SELECT * FROM pip_cadre_mesure_resultat_objectif_specifique cmr 
						JOIN pip_indicateur_mesure ON cmr.ID_INDICATEUR_MESURE = pip_indicateur_mesure.ID_INDICATEUR_MESURE 
						JOIN unite_mesure ON cmr.ID_UNITE_MESURE=unite_mesure.ID_UNITE_MESURE
						JOIN pip_demande_objectif_specifique objectif ON cmr.ID_DEMANDE_OBJECTIF=objectif.ID_DEMANDE_OBJECTIF
						WHERE cmr.ID_DEMANDE_INFO_SUPP=\'{$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}\'";
					$data['cmr_objectif_specifique'] = $this->ModelPs->getRequete("CALL `getTable`('{$crm_objectif_specifique_query}')");
					$crm_livrable_query = "SELECT * FROM pip_cadre_mesure_resultat_livrable cmr 
						JOIN pip_indicateur_mesure ON cmr.ID_INDICATEUR_MESURE = pip_indicateur_mesure.ID_INDICATEUR_MESURE 
						JOIN unite_mesure ON cmr.ID_UNITE_MESURE=unite_mesure.ID_UNITE_MESURE
						JOIN pip_demande_livrable livrable ON cmr.ID_LIVRABLE=livrable.ID_DEMANDE_LIVRABLE
						JOIN pip_cumulative cumulative ON cmr.CUMULATIVE_ID=cumulative.ID_CUMULATIVE 
						WHERE cmr.ID_DEMANDE_INFO_SUPP=\'{$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}\'";
					$data['cmr_livrable'] = $this->ModelPs->getRequete("CALL `getTable`('{$crm_livrable_query}')");
					$data['cmr_cible'] = [];
					foreach ($data['cmr_livrable'] as $livrable) 
					{
						$data['cmr_cible'][] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM cadre_mesure_resultat_valeur_cible WHERE ID_CADRE_MESURE_RESULTAT_LIVRABLE = \"{$livrable->ID_CADRE_MESURE_RESULTAT_LIVRABLE}\"')");
					}
					$query7 = "SELECT * FROM unite_mesure";
					$data['unites'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query7 . '")');
					$query9 = "SELECT * FROM pip_demande_livrable";
					$data['d_livrables'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query9 . '")');
					$query8 = "SELECT livrable.COUT_UNITAIRE_BIF,nomenclature.ID_BUDGET_LIVRABLE_NOMEN,nomenclature.TOTAL_TRIENNAL,nom.ID_NOMENCLATURE,nom.DESCR_NOMENCLATURE,demande.ID_DEMANDE_LIVRABLE,demande.DESCR_LIVRABLE FROM pip_budget_projet_livrable livrable JOIN pip_budget_livrable_nomenclature_budgetaire nomenclature ON livrable.ID_PROJET_LIVRABLE = nomenclature.ID_PROJET_LIVRABLE JOIN pip_nomenclature_budgetaire nom ON nom.ID_NOMENCLATURE = nomenclature.ID_NOMENCLATURE JOIN pip_demande_livrable demande ON livrable.ID_DEMANDE_LIVRABLE = demande.ID_DEMANDE_LIVRABLE WHERE livrable.ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['livrables'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query8 . '")');
					foreach ($data['livrables'] as $livrable) 
					{
						$data['livrables_cible'] = $this->ModelPs->getRequete('CALL `getTable`("SELECT * FROM pip_budget_projet_livrable_valeur_cible WHERE ID_BUDGET_LIVRABLE_NOMEN = \'' . $livrable->ID_BUDGET_LIVRABLE_NOMEN . '\'")');
					}
					$query10 = "SELECT * FROM pip_risques_impact_environnement WHERE ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['risques_env'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query10 . '")');
					$query10 = "SELECT * FROM pip_risques_impact_genre WHERE ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['risques_genre'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query10 . '")');
					$data['risquesProjet'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_risques_projet WHERE ID_DEMANDE_INFO_SUPP = \"{$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}\"')");
					$query11 = "SELECT * FROM pip_demande_source_financement demande JOIN pip_source_financement_bailleur bailleur ON bailleur.ID_SOURCE_FINANCE_BAILLEUR=demande.ID_SOURCE_FINANCE_BAILLEUR JOIN pip_taux_echange taux ON demande.TAUX_ECHANGE_ID=taux.TAUX_ECHANGE_ID WHERE demande.ID_DEMANDE_INFO_SUPP = " . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['sfp'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query11 . '")');
					
					$data['cibles'] = [];
					foreach ($data['sfp'] as $sfp) 
					{
						$data['cibles'][] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_source_financement_valeur_cible WHERE ID_DEMANDE_SOURCE_FINANCEMENT=\"{$sfp->ID_DEMANDE_SOURCE_FINANCEMENT}\"')");
					}
					$query12 = "SELECT * FROM pip_source_financement_bailleur";
					$data['bailleurs'] = $this->ModelPs->getRequete('CALL `getTable`("' . $query12 . '")');
					$data['status'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_STATUT_PROJET,DESCR_STATUT_PROJET FROM pip_statut_projet WHERE 1 ORDER BY ID_STATUT_PROJET ASC')");
					$data['provinces'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT PROVINCE_ID, PROVINCE_NAME FROM provinces WHERE 1 ORDER BY PROVINCE_ID ASC')");
					if (count($oldLieux)) 
					{
						$data['s_communes'] = $this->ModelPs->getRequete("CALL getTable('SELECT COMMUNE_ID, COMMUNE_NAME FROM communes WHERE PROVINCE_ID=" . $oldLieux[0]->ID_PROVINCE . " ORDER BY COMMUNE_ID ')");
					}
					$data['institutions'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT INSTITUTION_ID , CODE_INSTITUTION, DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY INSTITUTION_ID ASC')");
					$data['piliers'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER , DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");
					$data['objectif_strategiques'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE, DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique WHERE 1 ORDER BY ID_PILIER ASC')");
					$data['objectif_strategiques_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM objectif_strategique_pnd WHERE 1')");
					$data['axe_intervations_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd WHERE 1 ORDER BY ID_AXE_INTERVENTION_PND ASC')");
					$data['programme_pnd'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM programme_pnd WHERE 1')");
					$data['s_programmes'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT PROGRAMME_ID, CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE INSTITUTION_ID=" . $data['oldValues'][0]->INSTITUTION_ID . " ORDER BY PROGRAMME_ID ASC')");
					$data['proc_actions'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ACTION_ID, CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID=" . $data['oldValues'][0]->PROGRAMME_ID . " ORDER BY ACTION_ID ASC')");
					$data['nomenclatures'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_nomenclature_budgetaire')");
					$query_principal_2 = 'SELECT  ID_OBJECT_GENERAL, OBJECTIF_GENERAL FROM pip_objectif_general WHERE ID_OBJECT_GENERAL >0';
					$requete = "CALL `getTable`('" . $query_principal_2 . "')";
					$fetch_cov_frais = $this->ModelPs->datatable($requete);
					$data['general'] = $fetch_cov_frais;
					$data['annees'] = $this->get_annee_pip(); 
					$data['devises'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_taux_echange')");
					$data['countries'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT COUNTRY_ID,CommonName FROM countries WHERE 1')");
					$data['demande_objectif'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_DEMANDE_OBJECTIF,ID_DEMANDE_LIVRABLE,livrable.ID_DEMANDE_INFO_SUPP,DESCR_OBJECTIF,DESCR_LIVRABLE,COUT_LIVRABLE FROM pip_demande_objectif_specifique objectif JOIN pip_demande_livrable livrable ON objectif.ID_DEMANDE_INFO_SUPP=livrable.ID_DEMANDE_INFO_SUPP WHERE livrable.ID_DEMANDE_INFO_SUPP=" . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP . "')");
					$data['demande_livrable'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_demande_livrable WHERE ID_DEMANDE_INFO_SUPP=" . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP . "')");
					$query13 = "SELECT crm.ID_CADRE_MESURE_RESULTAT,crm.ANNE_UN,crm.ANNE_DEUX,crm.ANNE_TROIS,unite.UNITE_MESURE,crm.TOTAL_TRIENNAL,crm.VALEUR_REFERENCE_ANNE,indicateur.INDICATEUR_MESURE,categorie.ID_CATEGORIE_LIBELLE,categorie.CATEGORIE_LIBELLE
					FROM pip_cadre_mesure_resultat crm JOIN unite_mesure unite ON crm.ID_UNITE_MESURE=unite.ID_UNITE_MESURE 
					JOIN pip_indicateur_mesure indicateur ON crm.ID_INDICATEUR_MESURE=indicateur.ID_INDICATEUR_MESURE
					JOIN pip_categorie_libelle categorie ON crm.ID_CATEGORIE_LIBELLE=categorie.ID_CATEGORIE_LIBELLE
					WHERE crm.ID_DEMANDE_INFO_SUPP=" . $data['oldValues'][0]->ID_DEMANDE_INFO_SUPP;
					$data['crm1'] = $this->ModelPs->getRequete("CALL `getTable`('" . $query13 . "')");
					$data['crm_selected'] = [];
					foreach ($data['crm1'] as $key => $crm1) 
					{
						if ($crm1->ID_CATEGORIE_LIBELLE == 1) 
						{
							$q = "SELECT * FROM pip_demande_infos_supp WHERE ID_DEMANDE_INFO_SUPP = {$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}";
							$data['crm_selected'][$key] = $this->ModelPs->getRequete("CALL `getTable`('" . $q . "')");
						} else if ($crm1->ID_CATEGORIE_LIBELLE == 2) 
						{
							$q = "SELECT * FROM pip_demande_objectif_specifique objectif JOIN pip_cadre_mesure_resultat info ON info.ID_DEMANDE_OBJECTIF = objectif.ID_DEMANDE_OBJECTIF WHERE objectif.ID_DEMANDE_INFO_SUPP = {$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}";
							$data['crm_selected'][$key] = $this->ModelPs->getRequete("CALL `getTable`('" . $q . "')");
						} else if ($crm1->ID_CATEGORIE_LIBELLE == 3) 
						{
							$q = "SELECT * FROM pip_demande_livrable livrable JOIN pip_cadre_mesure_resultat info ON info.ID_LIVRABLE = livrable.ID_DEMANDE_LIVRABLE WHERE info.ID_DEMANDE_INFO_SUPP = {$data['oldValues'][0]->ID_DEMANDE_INFO_SUPP}";
							$data['crm_selected'][$key] = $this->ModelPs->getRequete("CALL `getTable`('" . $q . "')");
						}
					}
					
					$data['cumulative'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_CUMULATIVE,DESCRIPTION_CUMULATIVE FROM `pip_cumulative` WHERE 1')");
					return view('App\Modules\pip\Views\Processus_Investissement_Public_Demande_view', $data);
				} 
			}

			return redirect($_SERVER['HTTP_REFERER']);
		}
	}

	/**
	 * Suppression des informations d'une table
	 * @return void
	 */
	public function deleteInfo()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$table = $this->request->getPost('table');
		$id = $this->request->getPost('id');
		$callpsreq = "CALL `deleteData`(?,?);";
		$condition = " ID_DEMANDE_INFO_SUPP='$id'";
		$where = str_replace("\'", "'", $condition);
		$db = db_connect();
		$bindparams = [$db->escapeString($table), $db->escapeString($where)];
		$bindparams = str_replace("\'", "'", $bindparams);
		$this->ModelPs->createUpdateDelete($callpsreq, $bindparams);
	}

	/**
	 * Mise à jour de la fiche de projet
	 * @return void
	 */
	public function updateInfo()
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$session = \Config\Services::session();
			if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
	    {
	      return redirect('Login_Ptba/homepage');
	    }

			$query1 = "SELECT * FROM proc_process WHERE `NOM_PROCESS` LIKE '%PROCESSUS DE PROGRAMME D''INVESTISSEMENT PUBLIC%' LIMIT 1";
			$process = $this->ModelPs->getRequete('CALL `getTable`("' . $query1 . '")');
			$query2 = "SELECT * FROM proc_etape WHERE PROCESS_ID=\'{$process[0]->PROCESS_ID}\' ORDER BY ETAPE_ID ASC LIMIT 1 OFFSET 1";
			$etape = $this->ModelPs->getRequete("CALL `getTable`('" . $query2 . "')");
			$demande_id = $this->request->getPost('demande_id');
			$query3 = "SELECT DISTINCT info.ID_ACTION,info.ID_PROGRAMME,info.ID_DEMANDE_INFO_SUPP,info.NOM_PROJET,info.NUMERO_PROJET,info.DATE_DEBUT_PROJET,info.DATE_FIN_PROJET,info.DUREE_PROJET,info.EST_REALISE_NATIONAL,info.PATH_CONTEXTE_JUSTIFICATION,info.OBJECTIF_GENERAL,info.BENEFICIAIRE_PROJET,info.IMPACT_ATTENDU_ENVIRONNEMENT,info.IMPACT_ATTENDU_GENRE,info.TAUX_CHANGE_EURO,info.TAUX_CHANGE_USD,info.OBSERVATION_COMPLEMENTAIRE,info.DATE_PREPARATION_FICHE_PROJET,info.A_UNE_ETUDE,info.EST_CO_FINANCE,info.A_UNE_IMPACT_ENV,info.A_UNE_IMPACT_GENRE,
				demande.CODE_DEMANDE,demande.ID_DEMANDE,
				stat.ID_STATUT_PROJET,stat.DESCR_STATUT_PROJET,
				inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,
				pilier.ID_PILIER,pilier.DESCR_PILIER,
				obj.ID_OBJECT_STRATEGIQUE,obj.DESCR_OBJECTIF_STRATEGIC,
				pnd.ID_OBJECT_STRATEGIC_PND,pnd.DESCR_OBJECTIF_STRATEGIC_PND,
				axe.ID_AXE_INTERVENTION_PND,axe.DESCR_AXE_INTERVATION_PND,
				prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,
				act.ACTION_ID,act.LIBELLE_ACTION,act.CODE_ACTION
				FROM pip_demande_infos_supp info JOIN proc_demandes demande ON info.ID_DEMANDE = demande.ID_DEMANDE
				JOIN pip_statut_projet stat ON info.ID_STATUT_PROJET = stat.ID_STATUT_PROJET
				JOIN inst_institutions inst ON info.INSTITUTION_ID = inst.INSTITUTION_ID
				JOIN pilier ON info.ID_PILIER = pilier.ID_PILIER
				JOIN objectif_strategique obj ON info.ID_OBJECT_STRATEGIQUE = obj.ID_OBJECT_STRATEGIQUE
				JOIN objectif_strategique_pnd pnd ON info.ID_OBJECT_STRATEGIC_PND = pnd.ID_OBJECT_STRATEGIC_PND
				JOIN axe_intervention_pnd axe ON info.ID_AXE_INTERVENTION_PND = axe.ID_AXE_INTERVENTION_PND
				JOIN inst_institutions_programmes prog ON info.ID_PROGRAMME = prog.PROGRAMME_ID
				JOIN inst_institutions_actions act ON info.ID_ACTION = act.ACTION_ID
				WHERE info.ID_DEMANDE_INFO_SUPP = " . $demande_id;
			$demande = $this->ModelPs->getRequete("CALL `getTable`('{$query3}')");
			$this->update_all_table('proc_demandes', ' ETAPE_ID="' . $etape[0]->ETAPE_ID . '" ', ' ID_DEMANDE=' . $demande[0]->ID_DEMANDE);

			// ============ modification dans info sup tab principale ===============
			$demande_columns = ['OBSERVATION_COMPLEMENTAIRE'];

			// INFORMATION EN PROVENANCE DE LA TABLE pip_demande_infos_supp
			$demande_table = [];
			$demande_table[] = $demande[0]->OBSERVATION_COMPLEMENTAIRE;
			
			// INFORMATION DU FORMULAIRE
			$db = db_connect();
			$modifiable = [];
			$modifiable[] = $db->escapeString($this->request->getPost('OBSERVATION_COMPLEMENTAIRE'));

			$dataToModify = "";
			foreach ($demande_table as $key => $demande) 
			{
				if ($demande != $modifiable[$key] || empty($modifiable[$key])) 
				{
					if ($key == 15 && empty($modifiable[$key])) 
					{
						continue;
					}
					$dataToModify .= " $demande_columns[$key]='$modifiable[$key]',";
				}
			}
			$dataToModify .= " IS_FINISHED='1',IS_CORRECTION='0'";
			if (!empty($dataToModify)) 
			{
				$this->update_all_table('pip_demande_infos_supp', $dataToModify, " ID_DEMANDE_INFO_SUPP='$demande_id'");
			}
			// =============== FIN MODIFICATION DEMANDE INFO SUPP ==============

			// ===============MODIFICATION SOURCE FINANCEMENT ==================
			$EST_CO_FINANCE = $this->request->getPost('EST_CO_FINANCE');
			if ($EST_CO_FINANCE == '0') 
			{
				$bailleur = $this->request->getPost('bailleur');
				$devise_financement = $this->request->getPost('devise_financement');
				$total_financement = preg_replace('/\s/', '', $this->request->getPost('total_financement'));
				$SFP_an = [];
				$SFP_an[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an1'));
				$SFP_an[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an2'));
				$SFP_an[] = preg_replace('/\s/', '', $this->request->getPost('SFP_an3'));

				$sfp = $this->ModelPs->getRequete('CALL `getTable`("SELECT * FROM pip_demande_source_financement source LEFT JOIN pip_demande_source_financement_valeur_cible cible ON source.ID_DEMANDE_SOURCE_FINANCEMENT = cible.ID_DEMANDE_SOURCE_FINANCEMENT WHERE ID_DEMANDE_INFO_SUPP = ' . $demande_id . ' LIMIT 3")');
				$dataToModify = [];
				if ($sfp[0]->ID_SOURCE_FINANCE_BAILLEUR != $bailleur) 
				{
					$dataToModify[] = " ID_SOURCE_FINANCE_BAILLEUR='" . $bailleur . "'";
				}
				if ($sfp[0]->TAUX_ECHANGE_ID != $devise_financement) 
				{
					$dataToModify[] = " TAUX_ECHANGE_ID='" . $devise_financement . "'";
				}
				if ($sfp[0]->TOTAL_TRIENNAL != $total_financement) 
				{
					$dataToModify[] = " TOTAL_TRIENNAL='" . $total_financement . "'";
				}
				foreach ($sfp as $key => $annee) 
				{
					$this->update_all_table('pip_demande_source_financement_valeur_cible', "SOURCE_FINANCEMENT_VALEUR_CIBLE = '$SFP_an[$key]'", "ID_SOURCE_FINANCEMENT_VALEUR_CIBLE_ID='$annee->ID_SOURCE_FINANCEMENT_VALEUR_CIBLE_ID'");
				}
				if (count($dataToModify)) 
				{
					$this->update_all_table('pip_demande_source_financement', implode(',', $dataToModify), "ID_DEMANDE_INFO_SUPP='{$demande_id}'");
				}
			}
			// ============ FIN MODIFICATION SOURCE FINANCEMENT ====================
		}
	}

	/**
	 * Suppression des informations de l'étape précedente
	 * @param string $table
	 * @param int $id
	 * @return void
	 */
	public function deleteStored(string $table, int $id): void
	{
		$callpsreq = "CALL `deleteData`(?,?);";
		$condition = " ID_DEMANDE_INFO_SUPP='$id'";
		$where = str_replace("\'", "'", $condition);
		$db = db_connect();
		$bindparams = [$db->escapeString($table), $db->escapeString($where)];
		$bindparams = str_replace("\'", "'", $bindparams);
		$this->ModelPs->createUpdateDelete($callpsreq, $bindparams);
	}

	/**
	 * fonction pour afficher les select sur le formulaire
	 * @return string
	 */
	public function crm()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$category = $this->getBindParms('ID_CATEGORIE_LIBELLE , CATEGORIE_LIBELLE', 'pip_categorie_libelle', '1', 'ID_CATEGORIE_LIBELLE ASC');
		$data['category'] = $this->ModelPs->getRequete($callpsreq, $category);
		$indicateur = $this->getBindParms('ID_INDICATEUR_MESURE , INDICATEUR_MESURE', 'pip_indicateur_mesure', '1', 'ID_INDICATEUR_MESURE ASC');
		$data['indicateur'] = $this->ModelPs->getRequete($callpsreq, $indicateur);
		$unite = $this->getBindParms('ID_UNITE_MESURE , UNITE_MESURE', 'unite_mesure', '1', 'ID_UNITE_MESURE ASC');
		$data['unite'] = $this->ModelPs->getRequete($callpsreq, $unite);
		$demande = $this->getBindParms('ID_DEMANDE_INFO_SUPP, NOM_PROJET', 'pip_demande_infos_supp', '1', 'ID_DEMANDE_INFO_SUPP ASC');
		$data['demande'] = $this->ModelPs->getRequete($callpsreq, $demande);
		$livrable = $this->getBindParms('ID_DEMANDE_LIVRABLE,DESCR_LIVRABLE', 'pip_demande_livrable', '1', 'ID_DEMANDE_LIVRABLE ASC');
		$data['livrable'] = $this->ModelPs->getRequete($callpsreq, $livrable);
		$source_financement = $this->getBindParms('ID_SOURCE_FINANCE_BAILLEUR,NOM_SOURCE_FINANCE,CODE_BAILLEUR', 'pip_source_financement_bailleur', '1', 'ID_SOURCE_FINANCE_BAILLEUR ASC');
		$data['source_financement'] = $this->ModelPs->getRequete($callpsreq, $source_financement);
		return $this->response->setJSON($data);
	}

	/**fonction pour afficher les projet aui n ont pas fini */
	function det_projet()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$data = $this->urichk();
		$psgetrequete = "CALL getRequete(?,?,?,?);";
		$step = $this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'INSTITUTION_ID ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $step);
		return view('App\Modules\pip\Views\Processus_investissement_projet_non_fini_view', $data);
	}

	/*fonction pour afficher la liste des projet non fini*/
	function listing_unfished_project()
	{
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$critere1 = "";
		if (!empty($INSTITUTION_ID)) 
		{
			$critere1 = " AND inst_institutions.INSTITUTION_ID=" . $INSTITUTION_ID;
		}
		$query_principal = "SELECT  demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION, NOM_PROJET,demande.CODE_DEMANDE,proc_etape.DESCR_ETAPE,proc_process.NOM_PROCESS FROM `pip_demande_infos_supp` info_sup
		left join inst_institutions on  info_sup.INSTITUTION_ID=inst_institutions.INSTITUTION_ID
		left join proc_demandes demande on demande.ID_DEMANDE=info_sup.ID_DEMANDE
		left join proc_etape on  demande.ETAPE_ID=proc_etape.ETAPE_ID
		left join proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID WHERE  proc_process.PROCESS_ID=1 and  IS_FINISHED = 0 AND IS_COMPILE=0";
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';
		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';
		$order_column = '';
		$order_column = array('demande.CODE_DEMANDE', 'proc_process.NOM_PROCESS', 'proc_etape.DESCR_ETAPE', 'inst_institutions.DESCRIPTION_INSTITUTION', 1, 1, 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY demande.ID_DEMANDE ASC';
		$search = !empty($_POST['search']['value']) ?  (" AND (demande.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';
		$critaire = $critere1;
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;
		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;
		foreach ($fetch_cov_frais as $info) 
		{
			$post = array();
			$post[] = $u++;
			$post[] = !empty($info->CODE_DEMANDE) ? $info->CODE_DEMANDE : 'N/A';
			$post[] = !empty($info->NOM_PROCESS) ? $info->NOM_PROCESS : 'N/A';
			$post[] = !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
			$post[] = !empty($info->DESCRIPTION_INSTITUTION) ? $info->DESCRIPTION_INSTITUTION : 'N/A';
			$post[] = !empty($info->DATE_INSERTION) ? $info->DATE_INSERTION : 'N/A';
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';
			$detail = lang('messages_lang.detail');
			$update = lang('messages_lang.dropdown_link');
			$action .= "<li>
			<a href='" . base_url("pip/Processus_Investissement_Public/details/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$detail}</label></a>
			<a href='" . base_url("pip/Processus_Investissement_Public/demande/update/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$update}</label></a>
			</li></ul>";
			$post[] = $action;
			$data[] = $post;
		}
		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);
		$output = array
		(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/**
	 * Fonction pour télécharger un fichier
	 * @param string $fieldName
	 * @param string $folder
	 * @param string $prefix
	 * @return string
	 */
	public function uploadFile($fieldName, $folder, $prefix = ''): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';
		$file = $this->request->getFile($fieldName);
		$folderPath = ROOTPATH . 'public/uploads/' . $folder;
		if (!is_dir($folderPath)) 
		{
			mkdir($folderPath, 0777, true);
		}
		if ($file->isValid() && !$file->hasMoved()) 
		{
			$newName = $prefix . '_' . uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
		}
		return $path;
	}

	/**
	 * Fonction pour supprimer selon la tab
	 * @param string $type
	 * @param string $slug
	 * @return void
	 */

	public function delete($type, $slug = "")
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$id = $this->request->getPost('id');
		switch ($type) 
		{
			case 'lieu':
				$table = "pip_lieu_intervention_projet";
				$condition = " ID_LIEU_INTERVENTION_PROJET=$id";
				break;
			case 'etude':
				$table = "pip_etude_document_reference";
				$condition = " ID_ETUDE_DOC_REF=$id";
				$request = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_etude_document_reference WHERE ID_ETUDE_DOC_REF=$id')");
				$this->deleteFile($request[0]->DOC_REFERENCE);
				break;
			case 'objectif':
				$table = [];
				$condition = [];
				$table[] = "pip_demande_objectif_specifique";
				$table[] = "pip_demande_livrable";
				$condition[] = " ID_DEMANDE_OBJECTIF=$id";
				$condition[] = " ID_DEMANDE_LIVRABLE=$id";
				break;
			case 'livrable':
				$table = [];
				$condition = [];
				$table[] = "pip_demande_livrable";
				$condition[] = "ID_DEMANDE_LIVRABLE=$id";

				$cmr = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_CADRE_MESURE_RESULTAT_LIVRABLE,ID_LIVRABLE FROM pip_cadre_mesure_resultat_livrable WHERE ID_LIVRABLE={$id}')");
				foreach($cmr as $c){
					$ID_CMD = $c->ID_CADRE_MESURE_RESULTAT_LIVRABLE;
					$table[] = "pip_cadre_mesure_resultat_livrable";
					$condition[] = " ID_CADRE_MESURE_RESULTAT_LIVRABLE=$ID_CMD";
					$cibles = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_CADRE_MESURE_RESULTAT_CIBLE_ID FROM cadre_mesure_resultat_valeur_cible WHERE ID_CADRE_MESURE_RESULTAT_LIVRABLE={$ID_CMD}')");

					foreach($cibles as $cible){
						$ID_CIBLE = $cible->ID_CADRE_MESURE_RESULTAT_CIBLE_ID;
						$table[] = "cadre_mesure_resultat_valeur_cible";
						$condition[] = " ID_CADRE_MESURE_RESULTAT_CIBLE_ID=$ID_CIBLE";
					}
				}
				break;
			case 'crm':
				if ($slug == 'objectif_general') 
				{
					$table = "pip_cadre_mesure_resultat_$slug";
					$condition = " ID_CADRE_MESURE_RESULTAT_OBJECTIF_GENERAL=$id";
				} else if ($slug == 'objectif_specifique') 
				{
					$table = "pip_cadre_mesure_resultat_$slug";
					$condition = " ID_CADRE_MESURE_RESULTAT_OBJECTIF_SPECIFIQUE=$id";
				} else if ($slug == 'livrable') 
				{
					$table = "pip_cadre_mesure_resultat_$slug";
					$condition = " ID_CADRE_MESURE_RESULTAT_LIVRABLE=$id";
				}
				break;
			case 'nomenclature':
				$table = "pip_budget_livrable_nomenclature_budgetaire";
				$condition = " ID_BUDGET_LIVRABLE_NOMEN=$id";
				break;
			case 'risque':
				if ($slug == 'risque_projet') 
				{
					$table = 'pip_risques_projet';
					$condition = 'ID_RISQUE_PROJET=' . $id;
				}
				break;
			case 'sfp':
				$table = "pip_demande_source_financement";
				$condition = " ID_DEMANDE_SOURCE_FINANCEMENT=$id";
				break;
		}
		$callpsreq = "CALL `deleteData`(?,?);";
		$db = db_connect();
		if (is_array($table) && is_array($condition)) 
		{
			foreach ($table as $key => $t) 
			{
				$where = str_replace("\'", "'", $condition[$key]);
				$bindparams = [$db->escapeString($t), $db->escapeString($where)];
				$bindparams = str_replace("\'", "'", $bindparams);
				$this->ModelPs->createUpdateDelete($callpsreq, $bindparams);
			}
		} else 
		{
			$where = str_replace("\'", "'", $condition);
			$bindparams = [$db->escapeString($table), $db->escapeString($where)];
			$bindparams = str_replace("\'", "'", $bindparams);
			$this->ModelPs->createUpdateDelete($callpsreq, $bindparams);
		}
	}

	/**
	 * Supprimer un fichier
	 * @param string $file
	 * @param bool
	 */
	public function deleteFile($file)
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$file = ROOTPATH . 'public/' . $file;
		if (is_file($file)) 
		{
			if (unlink($file)) 
			{
				return true;
			} 
			else 
			{
				return false;
			}
		} 
		else 
		{
			return false;
		}
	}

	//  Vérifier la session de l'utilisateur
	private function verifyUser()
	{
		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id) || $user_id === null) 
		{
			return redirect('Login_Ptba/do_logout');
		}
	}
	
	/**
	 * Insertion dans une table donnée
	 * @param string $table
	 * @param string $columsinsert
	 * @param string $datacolumsinsert
	 * @return int
	 */
	private function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $result['id'];
	}

	private function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	/**
	 * Mettre à jour une table donnée
	 * @param string $table
	 * @param string $datatomodifie
	 * @param string $conditions
	 * @return void
	 */
	private function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
}
