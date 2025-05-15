<?php

/**NIYONGABO Tresor
 *Numero de telephone (WhatsApp): (+257) 61001252
 *Email: tresor.niyongabp@mediabox.bi
 *Date: 30 Novembre,2023
 *Titre: Listes des demandes
 **/

namespace App\Modules\pip\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '12048M');
class Processus_Investissement_Public extends BaseController
{
	protected $session;
	protected $library;
	protected $ModelPs;
	protected $validation;

	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	function index()
	{
		$session = \Config\Services::session();
		if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();
		$data['titre'] = lang('messages_lang.liste_demande');
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		//Sélectionner les processus
		$bindparams = $this->getBindParms('`PROCESS_ID`,`NOM_PROCESS`', 'proc_process', '1 AND STATUT=1', '`PROCESS_ID` ASC');
		$data['process'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

		$psgetrequete = "CALL getRequete(?,?,?,?);";
		$step = $this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'INSTITUTION_ID ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $step);

		return view('App\Modules\pip\Views\Processus_Investissement_Public_List_View', $data);
	}

	//fonction  pour la liste des demandes
	function listing()
	{
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = $session->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		$ID_INSTITUTION = $session->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');

		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$critere1 = "";
		if (!empty($INSTITUTION_ID)) {
			$critere1 = " AND inst_institutions.INSTITUTION_ID=" . $INSTITUTION_ID;
		}

		$query_principal = "SELECT DISTINCT  ID_DEMANDE_INFO_SUPP,proc_demandes.CODE_DEMANDE,proc_process.NOM_PROCESS,proc_etape.DESCR_ETAPE,inst_institutions.DESCRIPTION_INSTITUTION,proc_demandes.PROCESS_ID,proc_demandes.ETAPE_ID,proc_demandes.DATE_INSERTION,proc_demandes.ID_DEMANDE,
		info.IS_FINISHED FROM proc_demandes JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN pip_demande_infos_supp info ON info.ID_DEMANDE = proc_demandes.ID_DEMANDE JOIN inst_institutions ON inst_institutions.INSTITUTION_ID = info.INSTITUTION_ID	JOIN proc_profil_etape ON proc_profil_etape.ETAPE_ID = proc_demandes.ETAPE_ID
		WHERE proc_profil_etape.PROFIL_ID=" . $PROFIL_ID . " OR proc_demandes.USER_ID=" . $USER_ID;

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array('proc_demandes.ID_DEMANDE', 'proc_demandes.CODE_DEMANDE', 'proc_process.NOM_PROCESS', 'proc_etape.DESCR_ETAPE', 'inst_institutions.DESCRIPTION_INSTITUTION', 1, 1, 1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE DESC';

		$search = !empty($_POST['search']['value']) ?  (" AND (proc_demandes.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

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

			if ($info->IS_FINISHED == '1') 
			{
				$detail = lang('messages_lang.detail');
				$fichier = lang('messages_lang.dropdown_link_fichier');
				$action .= "<li>
				<a href='" . base_url("pip/Processus_Investissement_Public/details/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$detail}</label></a>
				</li>
				<li><a href='" . base_url("pip/presentation_fichier_investisement_public/projet_detail/" . md5($info->ID_DEMANDE_INFO_SUPP)) . "'><label>&nbsp;&nbsp;{$fichier}</label></a></li>
				</ul>";
			} 
			else 
			{
				$continue = lang('messages_lang.dropdown_link_continue');
				$action .= "<li>
				<a href='" . base_url("pip/Processus_Investissement_Public/demande/update/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$continue}</label></a>
				</li>";
			}
			$post[] = $action;
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
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

	/**fonction pour avancer les etapes du fiches compiler */
	function avancement()
	{
		$data = $this->urichk();
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

		$id_demande = $this->request->getPost('ID_DEMANDE');
		$etape_act = $this->request->getPost('CURRENT_STEP');
		$id_action = $this->request->getPost('ACTION_ID');
		$is_correction_pip = $this->request->getPost('IS_CORRECTION_PIP');
		$commentaire = $this->request->getPost('FORM_COMMENTAIRE');
		$date_insertion = date('Y-m-d H:i:s');
		if (!empty($commentaire)) {
			$commentaire = $this->request->getPost('FORM_COMMENTAIRE');
		} else {
			$commentaire = "NULL";
		}

		if ($is_correction_pip == 1) 
		{
			$psgetrequete = "CALL getRequete(?,?,?,?);";
			$step = $this->getBindParms('ACTION_ID,proc_actions.ETAPE_ID,MOVETO', 'pip_document_compilation join proc_actions on pip_document_compilation.ETAPE_ID= proc_actions.ETAPE_ID', 'ACTION_ID=' . $id_action, ' proc_actions.ACTION_ID asc');
			$processus = $this->ModelPs->getRequeteOne($psgetrequete, $step);

			$table = "historique_compilation";
			$data = '"' . $id_demande . '","' . $USER_ID . '","' . $etape_act . '","' . $id_action . '","' . $commentaire . '","' . $date_insertion . '"';
			$bindparams = [$table, $data];
			$insertRequest = "CALL insertIntoTable(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

			$statut = 1;
			$table = 'pip_document_compilation';
			$conditions = "ID_DOC_COMPILATION =".$id_demande;
			$datatomodifie = 'ETAPE_ID=' . $processus['MOVETO'] . ',STATUT=' . $statut;
			$this->update_all_table($table, $datatomodifie, $conditions);
			return redirect('pip/Fiche_Pip_Proposer/liste_pip_proposer');
		} 
		elseif ($is_correction_pip == 2) 
		{
			$psgetrequete = "CALL getRequete(?,?,?,?);";
			$step = $this->getBindParms('ACTION_ID,proc_actions.ETAPE_ID,MOVETO', 'pip_document_compilation join proc_actions on pip_document_compilation.ETAPE_ID= proc_actions.ETAPE_ID', 'ACTION_ID=' . $id_action, ' proc_actions.ACTION_ID asc');
			$processus = $this->ModelPs->getRequeteOne($psgetrequete, $step);

			$table = "historique_compilation";
			$data = '"' . $id_demande . '","' . $USER_ID . '","' . $etape_act . '","' . $id_action . '","' . $commentaire . '","' . $date_insertion . '"';
			$bindparams = [$table, $data];
			$insertRequest = "CALL insertIntoTable(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

			$statut = 2;
			$table = 'pip_document_compilation';
			$conditions = "ID_DOC_COMPILATION =" . $id_demande;
			$datatomodifie = 'ETAPE_ID=' . $processus['MOVETO'] . ',STATUT=' . $statut;
			$this->update_all_table($table, $datatomodifie, $conditions);
			return redirect('pip/Fiche_Pip_Proposer/liste_pip_proposer');
		} 
		else 
		{
			$psgetrequete = "CALL getRequete(?,?,?,?);";
			$step = $this->getBindParms('ACTION_ID,proc_actions.ETAPE_ID,MOVETO', 'pip_document_compilation join proc_actions on pip_document_compilation.ETAPE_ID= proc_actions.ETAPE_ID', 'ACTION_ID=' . $id_action, ' proc_actions.ACTION_ID asc');
			$processus = $this->ModelPs->getRequeteOne($psgetrequete, $step);

			$table = "historique_compilation";
			$data = '"' . $id_demande . '","' . $USER_ID . '","' . $etape_act . '","' . $id_action . '","' . $commentaire . '","' . $date_insertion . '"';
			$bindparams = [$table, $data];
			$insertRequest = "CALL insertIntoTable(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

			$table = 'pip_document_compilation';
			$conditions = "ID_DOC_COMPILATION =" . $id_demande;
			$datatomodifie = 'ETAPE_ID=' . $processus['MOVETO'];
			$this->update_all_table($table, $datatomodifie, $conditions);
			return redirect('pip/Fiche_Pip_Proposer/liste_pip_proposer');
		}
	}

	public function details($id)
	{
		$data = $this->urichk();
		$ID_INSTITUTION = session()->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

		if(empty($USER_ID)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$infosData = 'SELECT proc_etape.DESCR_ETAPE,user_users.USER_NAME,user_users.NOM,user_users.PRENOM,ID_DEMANDE,proc_demandes.ETAPE_ID,proc_demandes.PROCESS_ID,proc_demandes.DATE_INSERTION FROM `proc_demandes` JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID  JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN user_users ON user_users.USER_ID=proc_demandes.USER_ID WHERE ID_DEMANDE ='.$id;
		$infosData = "CALL `getTable`('".$infosData."');";
		$resultat = $this->ModelPs->getRequeteOne($infosData);
		//recepuration des profil sur l etape
         // Vérifier si $resultat contient des données avant d'accéder à $resultat['ETAPE_ID']
		if (!empty($resultat) && isset($resultat['ETAPE_ID'])){
			$callpsreq = "CALL getRequete(?,?,?,?);";
			$user_profil = $this->getBindParms('ID_PROFIL_ETAPE,ETAPE_ID,PROFIL_ID', 'proc_profil_etape', 'ETAPE_ID=' . $resultat['ETAPE_ID'], 'ID_PROFIL_ETAPE DESC');
			$getProfil = $this->ModelPs->getRequete($callpsreq, $user_profil);
		     }
		$prof_id = session()->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ID_PROFIL_ETAPE,ETAPE_ID,PROFIL_ID', 'proc_profil_etape', 'ETAPE_ID=' . $resultat['ETAPE_ID'] . '', 'ID_PROFIL_ETAPE DESC');
		$getProfil = $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil)) {
			foreach ($getProfil as $value) {
				$getEtape  = 'SELECT proc_demandes.ETAPE_ID FROM proc_demandes WHERE proc_demandes.ID_DEMANDE='.$resultat['ID_DEMANDE'].' AND proc_demandes.PROCESS_ID= '.$resultat['PROCESS_ID'];
				$getEtape = "CALL `getTable`('" . $getEtape . "');";
				$getEtape = $this->ModelPs->getRequeteOne($getEtape);
				$ETAPE_ID = $getEtape['ETAPE_ID'];
				//fin recuperation profil sur etape
				$query_inst = "SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID=" . $USER_ID . ")";
				$requete_inst = 'CALL `getList`("' . $query_inst . '")';
				$data['inst_institutions'] = $this->ModelPs->datatable($requete_inst);

				$callpsreq = "CALL `getRequete`(?,?,?,?);";
				$table = "proc_profil_etape etape JOIN proc_actions ON proc_actions.ETAPE_ID = etape.ETAPE_ID JOIN proc_demandes ON proc_actions.ETAPE_ID = proc_demandes.ETAPE_ID JOIN proc_etape on etape.ETAPE_ID=proc_etape.ETAPE_ID JOIN user_users user ON etape.PROFIL_ID=user.PROFIL_ID";
				$columnselect = "DISTINCT proc_demandes.ID_DEMANDE,proc_actions.IS_INITIAL,proc_actions.GET_FORM,proc_actions.LINK_FORM,proc_actions.ACTION_ID,proc_actions.ETAPE_ID, proc_actions.MOVETO,proc_actions.IS_REQUIRED,proc_actions.DESCR_ACTION, proc_etape.DESCR_ETAPE,proc_demandes.PROCESS_ID,etape.PROFIL_ID,IS_CORRECTION_PIP";
				$where = "etape.ETAPE_ID='".$ETAPE_ID."' AND proc_demandes.PROCESS_ID= '".$resultat['PROCESS_ID']."' AND proc_demandes.ID_DEMANDE = '".$resultat['ID_DEMANDE']."' AND user.USER_ID = '".$USER_ID."'";
				$orderby = 'ID_DEMANDE DESC';
				$where = str_replace("\'", "'", $where);
				$db = db_connect();
				$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
				$bindparams34 = str_replace("\'", "'", $bindparamss);
				$data['etape'] = $this->ModelPs->getRequete($callpsreq, $bindparams34);

				$callpsreq = "CALL `getRequete`(?,?,?,?);";
				$table = "proc_profil_etape etape JOIN proc_actions ON proc_actions.ETAPE_ID = etape.ETAPE_ID JOIN proc_demandes ON proc_actions.ETAPE_ID = proc_demandes.ETAPE_ID JOIN proc_etape on etape.ETAPE_ID=proc_etape.ETAPE_ID JOIN user_users user ON etape.PROFIL_ID=user.PROFIL_ID";
				$columnselect = "DISTINCT(proc_demandes.ID_DEMANDE),proc_actions.IS_INITIAL,proc_actions.GET_FORM,proc_actions.LINK_FORM,proc_actions.ACTION_ID,proc_actions.ETAPE_ID, proc_actions.MOVETO,proc_actions.IS_REQUIRED,proc_actions.DESCR_ACTION,proc_etape.DESCR_ETAPE,proc_demandes.PROCESS_ID,etape.PROFIL_ID";
				$where = "etape.ETAPE_ID='" . $ETAPE_ID . "' AND proc_demandes.PROCESS_ID= '" . $resultat['PROCESS_ID'] . "' AND proc_demandes.ID_DEMANDE = '" . $resultat['ID_DEMANDE'] . "' AND user.USER_ID = '" . $USER_ID . "'";
				$orderby = 'ID_DEMANDE DESC';
				$where = str_replace("\'", "'", $where);
				$db = db_connect();
				$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
				$bindparams34 = str_replace("\'", "'", $bindparamss);
				$data['etape2'] = $this->ModelPs->getRequete($callpsreq, $bindparams34);

				$query3 = "SELECT DISTINCT info.ID_DEMANDE_INFO_SUPP,NUMERO_PROJET,NOM_PROJET,DATE_DEBUT_PROJET,DATE_FIN_PROJET,DUREE_PROJET,DESCR_STATUT_PROJET,EST_REALISE_NATIONAL,CODE_INSTITUTION,DESCRIPTION_INSTITUTION,DESCR_PILIER,DESCR_OBJECTIF_STRATEGIC,DESCR_OBJECTIF_STRATEGIC_PND,DESCR_AXE_INTERVATION_PND,INTITULE_PROGRAMME,LIBELLE_ACTION,PATH_CONTEXTE_JUSTIFICATION,info.OBJECTIF_GENERAL,BENEFICIAIRE_PROJET,IMPACT_ATTENDU_ENVIRONNEMENT,IMPACT_ATTENDU_GENRE,TAUX_CHANGE_EURO,TAUX_CHANGE_USD,OBSERVATION_COMPLEMENTAIRE,DATE_FORMAT(DATE_PREPARATION_FICHE_PROJET, '%d-%m-%Y') DATE_PREPARATION_FICHE_PROJET,programme_pnd.DESCR_PROGRAMME,EST_CO_FINANCE
				FROM pip_demande_infos_supp info JOIN pip_statut_projet p_status ON info.ID_STATUT_PROJET = p_status.ID_STATUT_PROJET
				LEFT JOIN inst_institutions inst1 ON info.INSTITUTION_ID = inst1.INSTITUTION_ID
				LEFT JOIN pilier ON info.ID_PILIER = pilier.ID_PILIER
				LEFT JOIN objectif_strategique ob1 ON info.ID_OBJECT_STRATEGIQUE = ob1.ID_OBJECT_STRATEGIQUE
				LEFT JOIN objectif_strategique_pnd ob2 ON info.ID_OBJECT_STRATEGIC_PND = ob2.ID_OBJECT_STRATEGIC_PND
				LEFT JOIN axe_intervention_pnd axe ON info.ID_AXE_INTERVENTION_PND = axe.ID_AXE_INTERVENTION_PND
				LEFT JOIN inst_institutions_programmes inst2 ON info.ID_PROGRAMME = inst2.PROGRAMME_ID
				LEFT JOIN inst_institutions_actions inst3 ON info.ID_ACTION = inst3.ACTION_ID
				LEFT JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND= info.ID_PROGRAMME_PND
				WHERE info.ID_DEMANDE=".$id;

				$requete3 = 'CALL `getList`("' . $query3 . '")';
				$data['details'] = $this->ModelPs->getRequeteOne($requete3);
				$debut = explode("-", $data['details']['DATE_DEBUT_PROJET']);
				if(count($debut) > 1){
					$data['date_debut'] = $debut[1] . ' - ' . $debut[0];
				}
				$fin = explode("-", $data['details']['DATE_FIN_PROJET']);
				if(count($fin) > 1){
					$data['date_fin'] = $fin[1] . ' - ' . $fin[0];
				}

				// Historique des actions
				$query4 = "SELECT DESCR_ETAPE,hist.DATE_INSERTION,USER_NAME,DESCR_ACTION,COMMENTAIRE 
				FROM proc_demandes_historique hist JOIN proc_etape etape ON hist.ETAPE_ID = etape.ETAPE_ID
				JOIN user_users user ON hist.USER_ID = user.USER_ID
				JOIN proc_actions act ON hist.ACTION_ID = act.ACTION_ID
				WHERE hist.ID_DEMANDE=" . $id . " ORDER BY hist.ID_HISTORIQUE DESC";

				$requete4 = 'CALL `getList`("' . $query4 . '")';
				$data['historics'] = $this->ModelPs->datatable($requete4);

				//objectifs spécifiques et livrables
				$objectifs = "SELECT ID_DEMANDE_LIVRABLE,DESCR_LIVRABLE,COUT_LIVRABLE,OBJECTIF_SPECIFIQUE FROM pip_demande_livrable WHERE ID_DEMANDE_INFO_SUPP=" . $data['details']['ID_DEMANDE_INFO_SUPP'];

				$objectifs_livr = 'CALL `getList`("' . $objectifs . '")';
				$data['obj_livrables'] = $this->ModelPs->datatable($objectifs_livr);

				//source de financement
				$source = "SELECT bailleur.NOM_SOURCE_FINANCE,taux.DEVISE,TOTAL_FINANCEMENT FROM `pip_demande_source_financement` financ JOIN pip_source_financement_bailleur bailleur ON bailleur.ID_SOURCE_FINANCE_BAILLEUR=financ.`ID_SOURCE_FINANCE_BAILLEUR` JOIN pip_taux_echange taux ON taux.TAUX_ECHANGE_ID=financ.`TAUX_ECHANGE_ID` WHERE `ID_DEMANDE_INFO_SUPP`=" . $data['details']['ID_DEMANDE_INFO_SUPP'];
				$source_fin = 'CALL `getList`("' . $source . '")';
				$data['source_financement'] = $this->ModelPs->datatable($source_fin);

				//etude
				$etude = "SELECT ID_ETUDE_DOC_REF,TITRE_ETUDE,DOC_REFERENCE,STATUT_ETUDE,DATE_FORMAT(DATE_REFERENCE,'%d-%m-%Y') DATE_REFERENCE,STATUT_JURIDIQUE,AUTEUR_ORGANISME,NATIONALITE,NIF_AUTEUR,REGISTRE_COMMERCIALE,ADRESSE,OBSERVATION,countries.CommonName FROM `pip_etude_document_reference` etude JOIN countries ON countries.COUNTRY_ID=etude.`COUNTRY_ID` WHERE `ID_DEMANDE_INFO_SUPP`=" . $data['details']['ID_DEMANDE_INFO_SUPP'];
				$get_etude = 'CALL `getList`("' . $etude . '")';
				$data['etudes'] = $this->ModelPs->datatable($get_etude);

				//cadre de mesure
				$cadre = "SELECT TOTAL_TRIENNAL,unite_mesure.UNITE_MESURE,pip_indicateur_mesure.INDICATEUR_MESURE,liv.DESCR_LIVRABLE FROM `pip_cadre_mesure_resultat_livrable` cadre JOIN unite_mesure ON unite_mesure.ID_UNITE_MESURE=cadre.`ID_UNITE_MESURE` JOIN pip_indicateur_mesure ON pip_indicateur_mesure.ID_INDICATEUR_MESURE=cadre.`ID_INDICATEUR_MESURE` JOIN pip_demande_livrable liv ON liv.ID_DEMANDE_LIVRABLE=cadre.`ID_LIVRABLE` WHERE cadre.`ID_DEMANDE_INFO_SUPP`=" . $data['details']['ID_DEMANDE_INFO_SUPP'];
				$get_cadre = 'CALL `getList`("' . $cadre . '")';
				$data['cadre_mesure'] = $this->ModelPs->datatable($get_cadre);

				//budget projet par livrable
				$budget = "SELECT liv.DESCR_LIVRABLE,pip_budget_projet_livrable.COUT_UNITAIRE_BIF,nom.DESCR_NOMENCLATURE FROM `pip_budget_projet_livrable` JOIN pip_demande_livrable liv ON liv.ID_DEMANDE_LIVRABLE=pip_budget_projet_livrable.ID_DEMANDE_LIVRABLE JOIN pip_budget_livrable_nomenclature_budgetaire liv_nom ON liv_nom.ID_PROJET_LIVRABLE=pip_budget_projet_livrable.ID_PROJET_LIVRABLE JOIN pip_nomenclature_budgetaire nom ON nom.ID_NOMENCLATURE=liv_nom.ID_NOMENCLATURE WHERE pip_budget_projet_livrable.ID_DEMANDE_INFO_SUPP=" . $data['details']['ID_DEMANDE_INFO_SUPP'];
				$get_budget = 'CALL `getList`("' . $budget . '")';
				$data['budget_projet'] = $this->ModelPs->datatable($get_budget);

				//Risques et mesure de mitigation
				$risque = "SELECT `NOM_RISQUE`,`MESURE_RISQUE` FROM `pip_risques_projet` WHERE `ID_DEMANDE_INFO_SUPP`=" . $data['details']['ID_DEMANDE_INFO_SUPP'];
				$get_risque = 'CALL `getList`("' . $risque . '")';
				$data['risques_mitigation'] = $this->ModelPs->datatable($get_risque);

				$query5 = "SELECT * FROM proc_etape WHERE PROCESS_ID=" . $resultat['PROCESS_ID'] . " ORDER BY ETAPE_ID ASC LIMIT 1";

				$requete5 = 'CALL `getList`("' . $query5 . '")';
				$data['first'] = $this->ModelPs->datatable($requete5);

				$query6 = "SELECT * FROM proc_etape WHERE PROCESS_ID=" . $resultat['PROCESS_ID'] . " ORDER BY ETAPE_ID DESC LIMIT 1";

				$requete6 = 'CALL `getList`("' . $query6 . '")';
				$data['last'] = $this->ModelPs->datatable($requete6);

				$data['reference'] = $this->ModelPs->datatable('CALL `getList`("SELECT DOC_REFERENCE FROM pip_etude_document_reference WHERE ID_DEMANDE_INFO_SUPP=' . $data['details']['ID_DEMANDE_INFO_SUPP'] . '")');
			}
		}

		$data['resultat'] = $resultat;
		return view('App\Modules\pip\Views\Processus_Investissement_Public_Detail_View', $data);
	}

	public function proceed()
	{
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

		$rules = [];
		$verifiable = ['ID_DEMANDE', 'CURRENT_STEP', 'ACTION_ID'];
		$msg = lang('messages_lang.error_sms');
		foreach ($verifiable as $verif) {
			$rules[$verif] = [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$msg.'</font>'
				]
			];
		}


		$this->validation->setRules($rules);

		if ($this->validation->withRequest($this->request)->run()) {
			$ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
			$CURRENT_STEP = $this->request->getPost('CURRENT_STEP');
			$ACTION_ID = $this->request->getPost('ACTION_ID');
			$FORM_COMMENTAIRE = $this->request->getPost('FORM_COMMENTAIRE') ?? "";
			$IS_CORRECTION = $this->request->getPost('IS_CORRECTION');

			if ($IS_CORRECTION == 1 || $IS_CORRECTION == 2) 
			{
				$table = "proc_demandes_historique";
				$d = Date("Y-m-d H:i:s");
				$data = '"' . $ID_DEMANDE . '","' . $CURRENT_STEP . '","' . $d . '","' . $USER_ID . '","' . $ACTION_ID . '","' . $FORM_COMMENTAIRE . '"';
				$bindparams = [$table, $data];

				$insertRequest = "CALL insertIntoTable(?,?);";
				$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

				$query = "SELECT MOVETO FROM proc_actions WHERE ACTION_ID='".$ACTION_ID."'";
				$requete = 'CALL getList("' . $query . '")';

				$next = $this->ModelPs->datatable($requete);
				$updateTable = 'proc_demandes';
				$critere = "ID_DEMANDE=".$ID_DEMANDE;
				$dataToUpdate = 'ETAPE_ID="'.$next[0]->MOVETO.'"';
				$bindparams = [$updateTable, $dataToUpdate, $critere];

				$insertRequete = 'CALL updateData(?,?,?);';
				$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);

				$updateTables = 'pip_demande_infos_supp';
				$criteres = "ID_DEMANDE=".$ID_DEMANDE;
				$dataToUpdates = 'IS_CORRECTION='.$IS_CORRECTION;
				$bindparamss = [$updateTables, $dataToUpdates, $criteres];

				$insertRequete = 'CALL updateData(?,?,?);';
				$this->ModelPs->createUpdateDelete($insertRequete, $bindparamss);

				$data = [
					'message' => lang('messages_lang.message_demande_succes'),
				];

				session()->setFlashdata('alert', $data);

				return redirect('pip/Projet_Pip_Fini/liste_pip_fini');
			} 
			else 
			{
				$table = "proc_demandes_historique";
				$d = Date("Y-m-d H:i:s");
				$data = '"' . $ID_DEMANDE . '","' . $CURRENT_STEP . '","' . $d . '","' . $USER_ID . '","' . $ACTION_ID . '","' . $FORM_COMMENTAIRE . '"';
				$bindparams = [$table, $data];

				$insertRequest = "CALL insertIntoTable(?,?);";
				$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

				$query = "SELECT MOVETO FROM proc_actions WHERE ACTION_ID='".$ACTION_ID."'";
				$requete = 'CALL getList("' . $query . '")';

				$next = $this->ModelPs->datatable($requete);
				$correction = 1;
				$updateTable = 'proc_demandes';
				$critere = " ID_DEMANDE=".$ID_DEMANDE;
				$dataToUpdate = 'ETAPE_ID="'.$next[0]->MOVETO.'"';
				$bindparams = [$updateTable, $dataToUpdate, $critere];

				$insertRequete = 'CALL updateData(?,?,?);';
				$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);

				$data = [
					'message' => lang('messages_lang.message_demande_succes'),
				];

				session()->setFlashdata('alert', $data);

				return redirect('pip/Projet_Pip_Fini/liste_pip_fini');
			}
		} else {
			return redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function proceed_old()
	{
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

		$rules = [];
		$verifiable = ['ID_DEMANDE', 'CURRENT_STEP', 'ACTION_ID'];
		$msg = lang('messages_lang.error_sms');
		foreach ($verifiable as $verif) {
			$rules[$verif] = [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.$msg.'</font>'
				]
			];
		}


		$this->validation->setRules($rules);

		if ($this->validation->withRequest($this->request)->run()) {
			$ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
			$CURRENT_STEP = $this->request->getPost('CURRENT_STEP');
			$ACTION_ID = $this->request->getPost('ACTION_ID');
			$FORM_COMMENTAIRE = $this->request->getPost('FORM_COMMENTAIRE') ?? "";

			$table = "proc_demandes_historique";

			$d = Date("Y-m-d H:i:s");

			$data = '"' . $ID_DEMANDE . '","' . $CURRENT_STEP . '","' . $d . '","' . $USER_ID . '","' . $ACTION_ID . '","' . $FORM_COMMENTAIRE . '"';

			$bindparams = [$table, $data];

			$insertRequest = "CALL `insertIntoTable`(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

			$query = "SELECT MOVETO FROM proc_actions WHERE ACTION_ID='" . $ACTION_ID . "'";
			$requete = 'CALL `getList`("' . $query . '")';

			$next = $this->ModelPs->datatable($requete);

			$updateTable = 'proc_demandes';

			$critere = " ID_DEMANDE='" . $ID_DEMANDE . "'";
			$dataToUpdate = ' ETAPE_ID="' . $next[0]->MOVETO . '"';
			$bindparams = [$updateTable, $dataToUpdate, $critere];

			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);

			$data = [
				'message' => lang('messages_lang.message_demande_succes'),
			];

			session()->setFlashdata('alert', $data);

			return redirect('pip/Projet_Pip_Fini/liste_pip_fini');
		} else {
			return redirect($_SERVER['HTTP_REFERER']);
		}
	}

	/**fiches a compiler */
	function compilation_projet()
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table = "proc_actions JOIN proc_etape etape on proc_actions.ETAPE_ID=etape.ETAPE_ID JOIN proc_demandes demande on demande.ETAPE_ID=etape.ETAPE_ID JOIN proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID  JOIN pip_demande_infos_supp on demande.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE JOIN inst_institutions on pip_demande_infos_supp.INSTITUTION_ID= inst_institutions.INSTITUTION_ID";
		$columnselect = '*';
		$where = "(demande.PROCESS_ID=1) and (GET_FORM =1) and (pip_demande_infos_supp.IS_COMPILE=0) and (pip_demande_infos_supp.IS_FINISHED=1)";
		$orderby = 'ID_DEMANDE_INFO_SUPP DESC';
		$where = str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams = str_replace("\'", "'", $bindparams);
		$data['compilation'] = $this->ModelPs->getRequete($callpsreq, $bindparams);

		return view('App\Modules\pip\Views\Processus_Investissement_Public_complilation_view', $data);
	}

	/** fonction pour aficher les fiches compiler */
	function fiches_compiler()
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
		$data = $this->urichk();
		$psgetrequete = "CALL getRequete(?,?,?,?);";
		$step = $this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'INSTITUTION_ID ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $step);
		return view('App\Modules\pip\Views\Processus_investissement_Public_fiches_compiler_view', $data);
	}

	/**listes pour les fichiers compiler */
	function listing_complilation()
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$critere1 = "";

		$query_principal = "SELECT distinct pip_document_compilation.ID_DOC_COMPILATION,proc_etape.DESCR_ETAPE,PATH_DOC_COMPILER FROM `pip_document_compilation` JOIN pip_projet_compiler ON pip_projet_compiler.ID_DOC_COMPILATION  = pip_document_compilation.ID_DOC_COMPILATION  join  proc_etape on proc_etape.ETAPE_ID=pip_document_compilation.ETAPE_ID WHERE 1";

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array('1,1,1');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY 1 ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (demande.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

		$critaire = $critere1;
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;

		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		foreach ($fetch_cov_frais as $info) {
			$bindparams = $this->getBindParms('COUNT(ID_DEMANDE) as nombre', 'pip_projet_compiler', 'ID_DOC_COMPILATION =' . $info->ID_DOC_COMPILATION, 'ID_DOC_COMPILATION  desc');
			$nombredoc = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
			$post = array();
			$post[] = '<a href="" style="border:none"data-toggle="modal" data-target="#exampleModal' . $u . '"> <i class="fa fa-file-pdf fa-3x text-primary"></i> </a>';
			$post[] = '<button class="btn btn-primary" onclick="modal_odd(' . $info->ID_DOC_COMPILATION . ')">' . $nombredoc['nombre'] . '</button>';
			$post[] = $info->DESCR_ETAPE;
			$detail = lang('messages_lang.detail');
			$detailDocument = lang('messages_lang.titre_document');
			$fermer = lang('messages_lang.label_ferm');
			$post[] = '<div class="dropdown">
			<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
			Action
			</button>
			<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1"> 

			<li><a  href="' . base_url('pip/Processus_Investissement_Public/detail_compiler/' . $info->ID_DOC_COMPILATION) . '">'.$detail.'</a></li>

			</ul>
			</div>


			<div class="modal fade" id="exampleModal' . $u . '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
			<div class="modal-content">
			<div class="modal-header">
			<h5 class="modal-title text-white" id="exampleModalLabel">'.$detailDocument.'</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
			</div>
			<div class="modal-body">
			<img  style="width:100% ;height:600px" src="' . base_url('uploads/file_compiler/' . $info->PATH_DOC_COMPILER) . '" type="">
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">'.$fermer.'</button>
			</div>
			</div>
			</div>
			</div>';
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/**fonction pour recuperer les projets se trouvant sur le fiche compiler */
	function listing_projet_sur_fiches_compil($id)
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$critere1 = "";
		$query_principal = "SELECT * FROM `pip_projet_compiler` JOIN pip_demande_infos_supp ON pip_projet_compiler.ID_DEMANDE = pip_demande_infos_supp.ID_DEMANDE WHERE 1  and pip_projet_compiler.ID_DOC_COMPILATION=" . $id;

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array('1,1,1');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY 1 ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (demande.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

		$critaire = $critere1;
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . '   ' . $limit;

		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		foreach ($fetch_cov_frais as $info) 
		{
			$post = array();
			$post[] = !empty($info->NOM_PROJET) ? $info->NOM_PROJET : 'N/A';
			$post[] = !empty($info->NUMERO_PROJET) ? $info->NUMERO_PROJET : 'N/A';
			$post[] = !empty($info->DATE_DEBUT_PROJET) ? $info->DATE_DEBUT_PROJET : 'N/A';
			$post[] = !empty($info->DATE_FIN_PROJET) ? $info->DATE_FIN_PROJET : 'N/A';
			$action = lang('messages_lang.table_Action');
			$suppr = lang('messages_lang.supprimer_action');
			$non = lang('messages_lang.label_non');
			$post[] = '<div class="dropdown">
			<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
			'.$action.'
			</button>
			<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">

			<li><a class="dropdown-item" data-toggle="modal" >'.$suppr.'</a></li>

			</ul>
			</div>
			<div class="modal fade" id="mydelete">
			<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-body">
			</div>
			<div class="modal-footer">
			<button class="btn mb-1 btn-dark" class="close" data-dismiss="modal">'.$non.'</button>
			</div>
			</div>
			</div>
			</div>';

			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/**fonction pour afficher l'historique */
	function listing_histo_compilation($id)
	{
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = $session->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		$ID_INSTITUTION = $session->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");

		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$query_principal = "SELECT ID_COMPILATION,proc_etape.DESCR_ETAPE,DATE_TRAITEMENT,COMMENTAIRE,user_users.EMAIL FROM `historique_compilation` histo join proc_etape on histo.ETAPE_ID=proc_etape.ETAPE_ID join user_users on user_users.USER_ID=histo.USER_ID WHERE ID_COMPILATION=".$id;
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array('proc_etape.DESCR_ETAPE', 'COMMENTAIRE', 'proc_etape.DESCR_ETAPE', 'EMAIL', 1, 1, 1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE DESC';

		$search = !empty($_POST['search']['value']) ?  (" AND (user_users.EMAIL LIKE '%$var_search%' OR COMMENTAIRE LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' )") : '';

		$critaire = '';
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
			$post[] = !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
			$post[] = !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
			$post[] = !empty($info->COMMENTAIRE) ? $info->COMMENTAIRE : 'N/A';
			$post[] = !empty($info->EMAIL) ? $info->EMAIL : 'N/A';
			$post[] = !empty($info->DATE_TRAITEMENT) ? $info->DATE_TRAITEMENT : 'N/A';
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/**save compilation  */
	public function save_compilation($columsinsert, $datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
		$table = 'pip_projet_compiler';
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$idcomp = $this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
		return $idcomp;
	}

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

	/* fonction pour mettre a jour la table pour les */
	public function save_doc_compilation()
	{
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

		$file_compiler = $this->request->getFile('file_compiler');
		$filepath = $this->uploadFiles('file_compiler', 'doc_compiler', '');
		if(empty($file_compiler))
		{
			$data = ['message' => lang('messages_lang.message_erreur_fichier')];
			session()->setFlashdata('alert', $data);
			return redirect('pip/Fiche_Pip_Proposer/liste_pip_proposer');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table = "pip_demande_infos_supp info_sup LEFT JOIN inst_institutions ON info_sup.INSTITUTION_ID = inst_institutions.INSTITUTION_ID LEFT JOIN proc_demandes demande ON demande.ID_DEMANDE = info_sup.ID_DEMANDE LEFT JOIN proc_etape etape ON demande.ETAPE_ID = etape.ETAPE_ID JOIN proc_actions ON proc_actions.ETAPE_ID = etape.ETAPE_ID LEFT JOIN proc_process ON demande.PROCESS_ID = proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET = statut.ID_STATUT_PROJET";
		$columnselect = "statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION,etape.DESCR_ETAPE";
		$where = "demande.PROCESS_ID = 1  AND info_sup.IS_COMPILE = 0 AND info_sup.IS_FINISHED = 1 AND proc_actions.IS_COMPILE=1 AND GET_FORM=1";
		$orderby = 'ID_DEMANDE DESC';
		$where = str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams34 = str_replace("\'", "'", $bindparamss);
		$projet = $this->ModelPs->getRequete($callpsreq, $bindparams34);

		$psgetrequete = "CALL getRequete(?,?,?,?);";
		$step = $this->getBindParms('ACTION_ID,ETAPE_ID,MOVETO', 'proc_actions', ' IS_COMPILE=1', ' proc_actions.ETAPE_ID asc limit 1');
		$processus = $this->ModelPs->getRequeteOne($psgetrequete, $step);

		$max = $this->ModelPs->getRequete("CALL `getTable`('SELECT MAX(`ID_DOC_COMPILATION`) ID FROM pip_document_compilation WHERE 1')");
		$id = $max[0]->ID + 1;
		$codepip = "PIP-" . $id;
		$STATUT = 0;
		$date_insertion = date('Y-m-d H:i:s');

		$tableinsert = "pip_document_compilation";
		$columsinsert="PATH_DOC_COMPILER,ETAPE_ID,CODE_PIP,DATE_COMPILATION,STATUT";
		$datatoinsertio = '"'.$filepath.'","'.$processus['MOVETO'].'","'.$codepip.'","'.$date_insertion.'","'.$STATUT.'"';
		$document_id=$this->save_all_table($tableinsert,$columsinsert,$datatoinsertio);

		// $bindparams = [$tableinsert, $datatoinsertio];
		// $insertRequete = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		// $document = $this->ModelPs->getRequeteOne($insertRequete, $bindparams);
		// $document_id = $document['id'];

		foreach ($projet  as $info) 
		{
			$columsinsert = "ID_DEMANDE,USER_ID,ID_DOC_COMPILATION";
			$datacolumsinsert = $info->ID_DEMANDE . "," . $USER_ID . "," . $document_id . "";
			$this->save_compilation($columsinsert, $datacolumsinsert);

			$table = 'pip_demande_infos_supp';
			$conditions = "ID_DEMANDE=" . $info->ID_DEMANDE;
			$datatomodifie = 'IS_COMPILE=1';
			$this->update_all_table($table, $datatomodifie, $conditions);

			$commentaire = "";
			$date_insertion = date('Y-m-d H:i:s');
			$table = "historique_compilation";
			$data = '"' . $document_id . '","' . $USER_ID . '","' . $processus['ETAPE_ID'] . '","' . $processus['ACTION_ID'] . '","' . $commentaire . '","' . $date_insertion . '"';
			$bindparams = [$table, $data];
			$insertRequest = "CALL `insertIntoTable`(?,?);";
			$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);
		}

		return redirect('pip/Projet_Pip_A_Compiler/liste_pip_compiler');
	}

	/**fonction pour enlever le projet du fiche pour faire la correction */
	public function enlever_projet($id)
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if (empty($USER_ID)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$table = 'pip_demande_infos_supp';
		$conditions = "ID_DEMANDE=" . $id;
		$datatomodifie = 'IS_COMPILE=0';
		$this->update_all_table($table, $datatomodifie, $conditions);

		$db = db_connect();
		$deleteRequete = "CALL `deleteData`(?,?);";
		$critere = $db->escapeString("ID_DEMANDE =" . $id);
		$table = $db->escapeString("pip_projet_compiler");
		$bindparams = [$db->escapeString($table), $db->escapeString($critere)];
		$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		
		return redirect('pip/Fiche_Pip_Corriger/liste_pip_corriger');
	}

	/**fonction pour corriger le pip */
	public function save_correction_pip()
	{
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$id_doc_compilation = $this->request->getPost('id_doc_compilation');
		$demande_id = $this->request->getPost('demande_id');
		$etape = $this->request->getPost('etape');
		$action = $this->request->getPost('action');
		$etape_suivante = $this->request->getPost('etape_suivante');
		$file_compiler = $this->request->getFile('file_compiler');
		if (empty($file_compiler)) {
			$data = ['message' => "Desolé il faut mettre un document"];
			session()->setFlashdata('alert', $data);
			return redirect('pip/Projet_Pip_A_Compiler/liste_pip_compiler');
		}
		$filepath = $this->uploadFiles('file_compiler', 'doc_compiler_corriger', '');

		$statut = 0;
		$table = 'pip_document_compilation';
		$conditions = "ID_DOC_COMPILATION=" . $id_doc_compilation;
		$datatomodifie = 'ETAPE_ID="' . $etape_suivante . '",STATUT="' . $statut . '",PATH_DOC_COMPILER="' . $filepath . '"';

		$this->update_all_table($table, $datatomodifie, $conditions);

		$commentaire = "";
		$date_insertion = date('Y-m-d H:i:s');
		$table = "historique_compilation";
		$data = '"' . $demande_id . '","' . $USER_ID . '","' . $etape . '","' . $action . '","' . $commentaire . '","' . $date_insertion . '"';
		$bindparams = [$table, $data];
		$insertRequest = "CALL `insertIntoTable`(?,?);";
		$this->ModelPs->createUpdateDelete($insertRequest, $bindparams);

		return redirect('pip/Projet_Pip_A_Compiler/liste_pip_compiler');
	}

	public function uploadFiles($fieldName, $folder, $prefix = ''): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';
        $newName='';
		$file = $this->request->getFile($fieldName);
		if($file->isValid() && !$file->hasMoved())
		{
			$newName = uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'double_commande/' . $folder . '/' . $newName;
		}
		// print_r($newName);
		// exit();
		return $newName;
	}

	/*fx qui permet d'afficher le detail d un fichier compiler*/
	public function detail_compiler($id)
	{
		$data = $this->urichk();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		// Select l'etape
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$query_principal = $this->getBindParms('ID_DOC_COMPILATION,CODE_PIP,DATE_COMPILATION,STATUT,proc_etape.DESCR_ETAPE,pip_document_compilation.ETAPE_ID','pip_document_compilation JOIN proc_etape on pip_document_compilation.ETAPE_ID= proc_etape.ETAPE_ID',' 1 AND pip_document_compilation.ID_DOC_COMPILATION='.$id,'1');
		$data['principal'] = $this->ModelPs->getRequeteOne($callpsreq,$query_principal);
		
		$user_profil = $this->getBindParms('ID_DOC_COMPILATION,CODE_PIP,DATE_COMPILATION,STATUT,proc_etape.DESCR_ETAPE,pip_document_compilation.ETAPE_ID', 'pip_document_compilation JOIN proc_etape on pip_document_compilation.ETAPE_ID= proc_etape.ETAPE_ID', 'pip_document_compilation.ID_DOC_COMPILATION='.$id.'', 'ID_DOC_COMPILATION DESC');
		$getProfil = $this->ModelPs->getRequeteOne($callpsreq, $user_profil);

		$query4 = "SELECT ID_COMPILATION,proc_etape.DESCR_ETAPE,DATE_TRAITEMENT AS DATE_INSERTION,COMMENTAIRE,proc_actions.DESCR_ACTION,user_users.USER_NAME FROM historique_compilation histo JOIN proc_etape ON histo.ETAPE_ID = proc_etape.ETAPE_ID join proc_actions on proc_actions.ETAPE_ID=histo.ETAPE_ID JOIN user_users ON user_users.USER_ID = histo.USER_ID WHERE 1 AND ID_COMPILATION=" . $id . " ORDER BY ID_COMPILATION DESC ";

		$requete4 = 'CALL getList("' . $query4 . '")';
		$data['historics'] = $this->ModelPs->datatable($requete4);

		$prof_id = session()->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ID_PROFIL_ETAPE,ETAPE_ID,PROFIL_ID', 'proc_profil_etape', 'ETAPE_ID=' . $getProfil['ETAPE_ID'], 'ID_PROFIL_ETAPE DESC');
		$getProfil = $this->ModelPs->getRequete($callpsreq, $user_profil);
		$data['ID_DOC_COMPILATION']=$id;
		if (!empty($getProfil)) {
			foreach ($getProfil as $value) {
				$callpsreq = "CALL `getRequete`(?,?,?,?);";
				$table = "proc_actions actions JOIN pip_document_compilation doc_pip ON doc_pip.ETAPE_ID = actions.ETAPE_ID JOIN proc_profil_etape prof_etapes ON prof_etapes.ETAPE_ID= actions.ETAPE_ID JOIN user_users on user_users.PROFIL_ID=prof_etapes.PROFIL_ID";
				$columnselect= "DISTINCT IS_CORRECTION_PIP,ACTION_ID,doc_pip.ETAPE_ID,DESCR_ACTION,GET_FORM,MOVETO,IS_COMPILE,prof_etapes.PROFIL_ID,doc_pip.ID_DOC_COMPILATION";
				$where = "doc_pip.ID_DOC_COMPILATION=".$id . " AND prof_etapes.PROFIL_ID = ".$prof_id."";
				$orderby = 'doc_pip.ID_DOC_COMPILATION DESC';
				$where = str_replace("\'", "'", $where);
				$db = db_connect();
				$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
				$bindparams34 = str_replace("\'", "'", $bindparamss);
				$data['etape'] = $this->ModelPs->getRequete($callpsreq, $bindparams34);
			}
		}
		return view('App\Modules\pip\Views\Processus_Investissement_Public_detail_compiler_view', $data);
	}

	/**fonction pour afficher l interface de corretion des projets compiler afin de compiler les projets a nouveaux */
	public function correction_compilation($id)
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
		$data['id'] = $id;
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table = "proc_actions actions JOIN pip_document_compilation doc_pip ON doc_pip.ETAPE_ID = actions.ETAPE_ID JOIN proc_profil_etape prof_etapes ON prof_etapes.ETAPE_ID= actions.ETAPE_ID JOIN user_users on user_users.PROFIL_ID=prof_etapes.PROFIL_ID";
		$columnselect = "IS_CORRECTION_PIP,ACTION_ID,doc_pip.ETAPE_ID,DESCR_ACTION,GET_FORM,MOVETO,IS_COMPILE,prof_etapes.PROFIL_ID";
		$where = "doc_pip.ID_DOC_COMPILATION=" . $id;
		$orderby = 'ID_DOC_COMPILATION DESC';
		$where = str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams34 = str_replace("\'", "'", $bindparamss);
		$data['etape'] = $this->ModelPs->getRequeteone($callpsreq, $bindparams34);

		$callpsreq = "CALL getRequete(?,?,?,?);";
		$demandeid = $this->getBindParms('ID_DEMANDE', 'pip_projet_compiler', 'ID_DOC_COMPILATION='.$id, 'ID_DOC_COMPILATION DESC');
		$data['getdemande_id'] = $this->ModelPs->getRequeteOne($callpsreq, $demandeid);

		return view('App\Modules\pip\Views\Processus_Investissement_Public_correction_pip_view', $data);
	}

	/**fonction qui affiche la liste des projet dont le pipmcontient */
	public function liste_projet_pip_correction($id)
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$query_principal = "SELECT pip_projet_compiler.ID_DEMANDE,ID_DOC_COMPILATION,infos_sup.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,statut.DESCRIPTION FROM 
		pip_projet_compiler  left JOIN pip_demande_infos_supp infos_sup on  infos_sup.ID_DEMANDE=pip_projet_compiler.ID_DEMANDE left  JOIN inst_institutions on inst_institutions.INSTITUTION_ID= infos_sup.INSTITUTION_ID left  JOIN statut on statut.ID_STATUT=infos_sup.ID_STATUT_PROJET WHERE  ID_DOC_COMPILATION=" . $id;

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array('DESCRIPTION_INSTITUTION', 'DESCRIPTION_INSTITUTION', 'DESCRIPTION_INSTITUTION', 'DESCRIPTION_INSTITUTION', 1, 1, 1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_DOC_COMPILATION ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';

		$critaire = '';
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
			$post[] = !empty($info->NOM_PROJET) ? $info->NOM_PROJET : 'N/A';
			$post[] = !empty($info->DESCRIPTION_INSTITUTION) ? $info->DESCRIPTION_INSTITUTION : 'N/A';
			$post[] = !empty($info->DESCRIPTION) ? $info->DESCRIPTION : 'N/A';
			$options = lang('messages_lang.dropdown_link_options');
			$enlever = lang('messages_lang.dropdown_link_enlever');
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> '.$options.'  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			$action .= "<li>
			<a href='" . base_url("pip/Processus_Investissement_Public/enlever_projet/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$enlever}</label></a>
			
			</li></ul>";

			$post[] = $action;
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	/* fonction qui permet d afficher  le detail d un fichier compiler */
	public function  detail_compiler_old($id)
	{
		$data = $this->urichk();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		// Select l'etape
		$query_principal = "SELECT DISTINCT proc_demandes.CODE_DEMANDE,proc_process.NOM_PROCESS,proc_etape.DESCR_ETAPE,inst_institutions.DESCRIPTION_INSTITUTION,proc_demandes.PROCESS_ID,proc_demandes.ETAPE_ID,proc_demandes.DATE_INSERTION,proc_demandes.USER_ID,proc_demandes.ID_DEMANDE, user_users.USER_NAME 
		FROM proc_demandes JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID 
		left  JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID 
		left  JOIN user_affectaion ON user_affectaion.USER_ID=proc_demandes.USER_ID 
		left  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID  
		left   JOIN user_users ON user_users.USER_ID=proc_demandes.USER_ID
		WHERE proc_demandes.ID_DEMANDE=" . $id;

		$requete = 'CALL `getList`("' . $query_principal . '")';
		$data['principal'] = $this->ModelPs->datatable($requete);

		// Select les actions

		$query_secondaire = "SELECT proc_actions.ACTION_ID,proc_actions.DESCR_ACTION,proc_etape.ETAPE_ID,proc_etape.DESCR_ETAPE,proc_etape.PROFIL_ID FROM proc_etape JOIN proc_actions ON proc_etape.ETAPE_ID = proc_actions.ETAPE_ID WHERE proc_etape.ETAPE_ID=" . $data['principal'][0]->ETAPE_ID;
		$requete2 = 'CALL `getList`("' . $query_secondaire . '")';
		$data['etape'] = $this->ModelPs->datatable($requete2);

		// Details de la demande
		$query3 = "SELECT DISTINCT info.ID_DEMANDE_INFO_SUPP,NUMERO_PROJET,NOM_PROJET,DATE_DEBUT_PROJET,DATE_FIN_PROJET,DESCR_STATUT_PROJET,EST_REALISE_NATIONAL,DESCR_SECTEUR,DESCRIPTION_INSTITUTION,DESCR_PILIER,DESCR_OBJECTIF_STRATEGIC,DESCR_OBJECTIF_STRATEGIC_PND,DESCR_AXE_INTERVATION_PND,INTITULE_PROGRAMME,LIBELLE_ACTION,PATH_CONTEXTE_JUSTIFICATION,info.OBJECTIF_GENERAL,BENEFICIAIRE_PROJET,IMPACT_ATTENDU_ENVIRONNEMENT,IMPACT_ATTENDU_GENRE,TAUX_CHANGE_EURO,TAUX_CHANGE_USD,OBSERVATION_COMPLEMENTAIRE,DATE_PREPARATION_FICHE_PROJET,PATH_DOC_COMPILER
		FROM pip_demande_infos_supp info JOIN pip_statut_projet p_status ON info.ID_STATUT_PROJET = p_status.ID_STATUT_PROJET
		left JOIN pip_secteur_intervention secteur ON info.ID_SECTEUR_INTERVENTION = secteur.ID_SECTEUR_INTERVENTION
		left JOIN inst_institutions inst1 ON info.INSTITUTION_ID = inst1.INSTITUTION_ID
		left JOIN pilier ON info.ID_PILIER = pilier.ID_PILIER
		left JOIN objectif_strategique ob1 ON info.ID_OBJECT_STRATEGIQUE = ob1.ID_OBJECT_STRATEGIQUE
		left JOIN objectif_strategique_pnd ob2 ON info.ID_OBJECT_STRATEGIC_PND = ob2.ID_OBJECT_STRATEGIC_PND
		left JOIN axe_intervention_pnd axe ON info.ID_AXE_INTERVENTION_PND = axe.ID_AXE_INTERVENTION_PND
		left JOIN inst_institutions_programmes inst2 ON info.ID_PROGRAMME = inst2.PROGRAMME_ID
		left JOIN inst_institutions_actions inst3 ON info.ID_ACTION = inst3.ACTION_ID
		WHERE info.ID_DEMANDE=" . $id;

		$requete3 = 'CALL `getList`("' . $query3 . '")';
		$data['details'] = $this->ModelPs->datatable($requete3);

		// print_r($data['details'] );die();
		// Historique des actions
		$query4 = "SELECT DESCR_ETAPE,hist.DATE_INSERTION,USER_NAME,DESCR_ACTION,COMMENTAIRE FROM proc_demandes_historique hist JOIN proc_etape etape ON hist.ETAPE_ID = etape.ETAPE_ID JOIN user_users user ON hist.USER_ID = user.USER_ID
		JOIN proc_actions act ON hist.ACTION_ID = act.ACTION_ID
		WHERE hist.ID_DEMANDE=" . $id . " ORDER BY hist.ID_HISTORIQUE DESC";

		$requete4 = 'CALL `getList`("' . $query4 . '")';
		$data['historics'] = $this->ModelPs->datatable($requete4);

		$query5 = "SELECT * FROM proc_etape WHERE PROCESS_ID=" . $data['principal'][0]->PROCESS_ID . " ORDER BY ETAPE_ID ASC LIMIT 1";

		$requete5 = 'CALL `getList`("' . $query5 . '")';
		$data['first'] = $this->ModelPs->datatable($requete5);

		$query6 = "SELECT * FROM proc_etape WHERE PROCESS_ID=" . $data['principal'][0]->PROCESS_ID . " ORDER BY ETAPE_ID DESC LIMIT 1";

		$requete6 = 'CALL `getList`("' . $query6 . '")';
		$data['last'] = $this->ModelPs->datatable($requete6);

		return view('App\Modules\pip\Views\Processus_Investissement_Public_detail_compiler_view', $data);
	}

	/**fonction pour afficher les projet aui n ont pas fini */
	function det_projet()
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

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

	/**fonction pour afficher la liste des projet non fini */
	function listing_unfished_project()
	{
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");

		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$session = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$critere1 = "";
		if(!empty($INSTITUTION_ID))
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

		if ($_POST['length'] != -1) {
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
			$options = lang('messages_lang.dropdown_link_options');
			$detail = lang('messages_lang.detail');
			$completer = lang('messages_lang.dropdown_link_completer');
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'.$options.'  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			$action .= "<li>
			<a href='" . base_url("pip/Processus_Investissement_Public/details/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$detail}</label></a>
			<a href='" . base_url("pip/Processus_Investissement_Public/demande/update/" . $info->ID_DEMANDE) . "'><label>&nbsp;&nbsp;{$completer}</label></a>
			</li></ul>";

			$post[] = $action;

			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	private function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	//liste des lieux d'intervention
	function lieu_intervention()
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$id = $this->request->getPost('ID_DEMANDE_INFO_SUPP');
		$query_principal = "SELECT DISTINCT lieu.ID_PROVINCE,provinces.PROVINCE_NAME FROM `pip_lieu_intervention_projet` lieu JOIN provinces ON provinces.PROVINCE_ID=lieu.`ID_PROVINCE` WHERE `ID_DEMANDE_INFO_SUPP`=" . $id;

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit = 'LIMIT 0,10';

		if ($_POST['length'] != -1) {
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = '';
		$order_column = array(1, 'PROVINCE_NAME');

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] : ' ORDER BY PROVINCE_NAME ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (PROVINCE_NAME LIKE '%$var_search%' )") : '';

		$critaire = "";
		$query_secondaire = $query_principal . ' ' . $search . ' ' . $critaire . ' ' . $order_by . ' ' . $limit;
		$query_filter = $query_principal . ' ' . $search . ' ' . $critaire;
		$requete = 'CALL `getList`("' . $query_secondaire . '")';
		$fetch_cov_frais = $this->ModelPs->datatable($requete);
		$data = array();
		$u = 1;

		foreach ($fetch_cov_frais as $info) {
			$communes = "SELECT COUNT(ID_COMMUNE) nbre_com FROM pip_lieu_intervention_projet WHERE ID_DEMANDE_INFO_SUPP=" . $id . " AND ID_PROVINCE =" . $info->ID_PROVINCE;
			$comm = 'CALL `getList`("' . $communes . '")';
			$data_communes = $this->ModelPs->getRequeteOne($comm);

			$post = array();
			$post[] = $u++;
			$post[] = !empty($info->PROVINCE_NAME) ? $info->PROVINCE_NAME : 'N/A';
			// $post[]=!empty($info->COMMUNE_NAME) ? $info->COMMUNE_NAME : 'N/A';
			$post[] = '<button class="btn btn-primary" onclick="modal_comm(' . $info->ID_PROVINCE . ',' . $id . ')" >' . $data_communes['nbre_com'] . '</button>';
			$data[] = $post;
		}

		$requeteqp = 'CALL `getList`("' . $query_principal . '")';
		$recordsTotal = $this->ModelPs->datatable($requeteqp);
		$requeteqf = 'CALL `getList`("' . $query_filter . '")';
		$recordsFiltered = $this->ModelPs->datatable($requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	//recuperation des communes par rapport a la province
	function getcommunes($id_prov, $demande)
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$communes = $this->ModelPs->getRequete("CALL getTable('SELECT * FROM pip_lieu_intervention_projet lieu JOIN communes ON lieu.ID_COMMUNE=communes.COMMUNE_ID JOIN provinces ON lieu.ID_PROVINCE=provinces.PROVINCE_ID WHERE lieu.ID_PROVINCE={$id_prov} AND ID_DEMANDE_INFO_SUPP={$demande}')");

		$u = 0;
		$html = "<table class='table table-bordered table-striped'>";
		foreach ($communes as $key => $value) {
			$u++;
			$html .= "<tr id='lieu_intervention_" . $key . "'>
			<td>" . $u . "</td>
			<td>" . $value->COMMUNE_NAME . "</td>
			<td><button class='btn btn-danger' onclick='supprimerLieu(" . $value->ID_LIEU_INTERVENTION_PROJET . ",\"" . $value->PROVINCE_NAME . "\",\"lieu_intervention_" . $key . "\")'><i class='fa fa-close'></i></button></td>
			</tr>";
		}
		$html .= "</table>";
		$output = array('html' => $html, 'PROVINCE' => $communes[0]->PROVINCE_NAME);

		return $this->response->setJSON($output);
	}

	//recupérer le doc de référence
	function get_doc_reference($id)
	{
		$session = \Config\Services::session();
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION') !=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$documents = $this->getBindParms('DOC_REFERENCE', 'pip_etude_document_reference', 'ID_ETUDE_DOC_REF=' . $id, '1 ASC');
		$get_doc = $this->ModelPs->getRequeteOne($psgetrequete, $documents);

		$output = array('DOC_REFERENCE' => $get_doc['DOC_REFERENCE']);
		return $this->response->setJSON($output);
	}
}
?>