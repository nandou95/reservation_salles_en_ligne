<?php

/** 
 * controller pour le processus dinvestissement public
	@author: jemapess MUGISHA
	email: jemapess.mugigisha@mediabox.bi
	tel:68001621
    date :le 29 nov 2023
 */

namespace App\Modules\pip\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpParser\Internal\Differ;
use PhpParser\Node\Expr\Print_;

class Fichier_Pip_Exel extends BaseController
{
	protected $session;
	protected $ModelPs;

	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->ModelS = new ModelS();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function afficher($id = 1)
	{
		$session  = \Config\Services::session();
		$user_id = '';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}
		else
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$excel = $this->getBindParms('infos.NOM_PROJET,infos.NUMERO_PROJET,infos.OBJECTIF_GENERAL,infos.DUREE_PROJET,infos.DATE_DEBUT_PROJET,infos.EST_REALISE_NATIONAL,infos.DATE_FIN_PROJET,infos.PATH_CONTEXTE_JUSTIFICATION,infos.ID_DEMANDE_INFO_SUPP,infos.BENEFICIAIRE_PROJET,infos.IMPACT_ATTENDU_ENVIRONNEMENT,infos.IMPACT_ATTENDU_GENRE,infos.TAUX_CHANGE_EURO,infos.TAUX_CHANGE_USD,infos.OBSERVATION_COMPLEMENTAIRE, infos.DATE_PREPARATION_FICHE_PROJET,sec.DESCR_SECTEUR,inst.DESCRIPTION_INSTITUTION,pil.DESCR_PILIER,pil.NUMERO_PILIER,objs.DESCR_OBJECTIF_ST
		jnb RATEGIC,objs.NUMERO_OBJECT_STRATEGIC,objs.DESCR_OBJECTIF_STRATEGIC,objs.NUMERO_OBJECT_STRATEGIC,objp.DESCR_OBJECTIF_STRATEGIC_PND,objp.NUMERO_OBJCTIF_STRATEGIC_PND,axe.DESCR_AXE_INTERVATION_PND,axe.NUM_AXE_INTERVENTION_PND,prog.INTITULE_PROGRAMME,prog.CODE_PROGRAMME,act.LIBELLE_ACTION,act.CODE_ACTION,st.DESCR_STATUT_PROJET', 'pip_demande_infos_supp infos JOIN pip_secteur_intervention sec ON infos.ID_SECTEUR_INTERVENTION=sec.ID_SECTEUR_INTERVENTION JOIN inst_institutions inst ON inst.INSTITUTION_ID=infos.INSTITUTION_ID JOIN pilier pil ON pil.ID_PILIER=infos.ID_PILIER JOIN objectif_strategique objs ON infos.ID_OBJECT_STRATEGIQUE=objs.ID_OBJECT_STRATEGIQUE JOIN objectif_strategique_pnd objp ON objp.ID_OBJECT_STRATEGIC_PND=infos.ID_OBJECT_STRATEGIC_PND JOIN axe_intervention_pnd axe ON axe.ID_AXE_INTERVENTION_PND=infos.ID_AXE_INTERVENTION_PND JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=infos.ID_PROGRAMME JOIN inst_institutions_actions act ON act.ACTION_ID =infos.ID_ACTION JOIN pip_statut_projet st ON st.ID_STATUT_PROJET=infos.ID_STATUT_PROJET', 'infos.ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['donne_excel'] = $this->ModelPs->getRequeteOne($callpsreq, $excel);

		//-------------commune province----------------------------------------
		$data['province_commune_lie'] = [];
		if ($data["donne_excel"]["EST_REALISE_NATIONAL"] == 0) 
		{
			$data['province_commune_lie'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT COMMUNE_NAME, ID_DEMANDE_INFO_SUPP, PROVINCE_NAME FROM `pip_lieu_intervention_projet` LEFT JOIN provinces ON pip_lieu_intervention_projet.ID_PROVINCE = provinces.PROVINCE_ID LEFT JOIN communes ON pip_lieu_intervention_projet.ID_COMMUNE = communes.COMMUNE_ID WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = '.$id.')");
		}
		//--------ETUDE ET DOCUMENT DE REFERENCE pip_etude_document_ref--------------

		$document_ref = $this->getBindParms('TITRE_ETUDE,DOC_REFERENCE,DATE_REFERENCE,AUTEUR_ORGANISME,OBSERVATION', 'pip_etude_document_reference', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['document_reference'] = $this->ModelPs->getRequete($callpsreq, $document_ref);
		$data['count_document_reference'] = count($data['document_reference']);

		//--------------OBJECTIF SPECIFIQUE pip_demande_objectif_specifique-------------
		$objectif_spec = $this->getBindParms('DESCR_OBJECTIF', 'pip_demande_objectif_specifique', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['specifique'] = $this->ModelPs->getRequete($callpsreq, $objectif_spec);
		$data['count_objectif'] = count($data['specifique']);

		//---------------LIVRABLE pip_demande_livrable-------------
		$liv = $this->getBindParms('DESCR_LIVRABLE', 'pip_demande_livrable', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['livrable'] = $this->ModelPs->getRequete($callpsreq, $liv);
		$data['count_livrable'] = count($data['livrable']);

		//----------------RISQUE pip_risques---------------------------------------
		$risque_req = $this->getBindParms('NOM_RISQUE,MESURE_RISQUE', 'pip_risques', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['risque'] = $this->ModelPs->getRequete($callpsreq, $risque_req);
		$data['count_risque'] = count($data['risque']);

		//--------------------CADRE DE MESURE pip_cadre_mesure_resultat----
		$data['general_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,gen.OBJECTIF_GENERAL ,mes.UNITE_MESURE,cad.VALEUR_REFERENCE_ANNE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat cad JOIN pip_categorie_libelle cat ON cat.ID_CATEGORIE_LIBELLE=cad.ID_CATEGORIE_LIBELLE JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP JOIN pip_objectif_general gen ON gen.ID_OBJECT_GENERAL=dis.ID_OBJECT_GENERAL WHERE dis.ID_DEMANDE_INFO_SUPP=1 AND cat.ID_CATEGORIE_LIBELLE=$id')");
		$data['count_general'] = count($data['general_req']);

		$data['specifique_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,dos.DESCR_OBJECTIF,mes.UNITE_MESURE,cad.VALEUR_REFERENCE_ANNE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat cad JOIN pip_categorie_libelle cat ON cat.ID_CATEGORIE_LIBELLE=cad.ID_CATEGORIE_LIBELLE JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_objectif_specifique dos ON dos.ID_DEMANDE_OBJECTIF=cad.ID_OBJECTIF_SPECIFIQUE JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP WHERE dis.ID_DEMANDE_INFO_SUPP=$id')");
		$data['count_specifique'] = count($data['specifique_req']);
		$data['livrable_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,liv.DESCR_LIVRABLE,mes.UNITE_MESURE,cad.VALEUR_REFERENCE_ANNE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat cad JOIN pip_categorie_libelle cat ON cat.ID_CATEGORIE_LIBELLE=cad.ID_CATEGORIE_LIBELLE JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_livrable liv ON liv.ID_DEMANDE_LIVRABLE= cad.ID_LIVRABLE JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP WHERE dis.ID_DEMANDE_INFO_SUPP=$id')");
		$data['count_livrable'] = count($data['livrable_req']);

		//-------------------BUDJET----pip_budget_livrable_nomenclature_budgetaire------
		$data['budget_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT liv.DESCR_LIVRABLE,bipro.COUT_UNITAIRE_BIF,nom.DESCR_NOMENCLATURE,nom.CODE_NOMENCLATURE,COUNT(bln.ANNE_UN),bln.ANNE_UN,COUNT(bln.ANNE_DEUX),bln.ANNE_DEUX,COUNT(bln.ANNE_TROIS),bln.ANNE_TROIS,bln.TOTAL_DUREE_PROJET,bln.TOTAL_TRIENNAL FROM pip_budget_livrable_nomenclature_budgetaire bln JOIN pip_budget_projet_livrable bipro ON bipro.ID_PROJET_LIVRABLE=bln.ID_PROJET_LIVRABLE JOIN pip_demande_livrable liv ON liv.ID_DEMANDE_LIVRABLE=bipro.ID_DEMANDE_LIVRABLE JOIN pip_demande_infos_supp dem ON bipro.ID_DEMANDE_INFO_SUPP=dem.ID_DEMANDE_INFO_SUPP JOIN pip_nomenclature_budgetaire nom ON nom.ID_NOMENCLATURE=bln.ID_NOMENCLATURE WHERE dem.ID_DEMANDE_INFO_SUPP=$id')");
		$data['count_budget'] = count($data['budget_req']);

		//----------SOURCE FINANCIERE---------------------------------------------------
		$source_req = $this->getBindParms('li.DESCR_LIVRABLE,sf.NOM_SOURCE_FINANCE,sf.CODE_BAILLEUR,psf.ANNE_UN,psf.ANNE_DEUX,psf.ANNE_TROIS,psf.TOTAL_DUREE_PROJET,psf.TOTAL_TRIENNAL', 'pip_demande_source_financement psf JOIN pip_source_financement_bailleur sf ON sf.ID_SOURCE_FINANCE_BAILLEUR=psf.ID_SOURCE_FINANCE_BAILLEUR JOIN pip_demande_livrable li ON li.ID_DEMANDE_LIVRABLE=psf.ID_LIVRABLE JOIN pip_demande_infos_supp pd ON pd.ID_DEMANDE_INFO_SUPP=psf.ID_DEMANDE_INFO_SUPP ', 'pd.ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['source'] = $this->ModelPs->getRequete($callpsreq, $source_req);
		$data['count_source'] = count($data['source']);

		return view('App\Modules\pip\Views\Fichier_Pip_Exel_View', $data);
	}

	// la fonction qui exporte dans excel--------------
	function action(int $id)
	{
		$db = db_connect();
		$data = $this->urichk();
		$session  = \Config\Services::session();
		
		if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$excel = $this->getBindParms('infos.NOM_PROJET,infos.NUMERO_PROJET,infos.DUREE_PROJET,infos.OBJECTIF_GENERAL,infos.DATE_DEBUT_PROJET,infos.EST_REALISE_NATIONAL,infos.DATE_FIN_PROJET,infos.PATH_CONTEXTE_JUSTIFICATION,infos.BENEFICIAIRE_PROJET,infos.IMPACT_ATTENDU_ENVIRONNEMENT,infos.IMPACT_ATTENDU_GENRE,infos.TAUX_CHANGE_EURO,infos.TAUX_CHANGE_USD,infos.OBSERVATION_COMPLEMENTAIRE, infos.DATE_PREPARATION_FICHE_PROJET,infos.A_UNE_IMPACT_ENV,infos.A_UNE_IMPACT_GENRE,sec.DESCR_SECTEUR,inst.DESCRIPTION_INSTITUTION,pil.DESCR_PILIER,pil.NUMERO_PILIER,objs.DESCR_OBJECTIF_STRATEGIC,objs.NUMERO_OBJECT_STRATEGIC,objs.DESCR_OBJECTIF_STRATEGIC,objs.NUMERO_OBJECT_STRATEGIC,objp.DESCR_OBJECTIF_STRATEGIC_PND,objp.NUMERO_OBJCTIF_STRATEGIC_PND,axe.DESCR_AXE_INTERVATION_PND,axe.NUM_AXE_INTERVENTION_PND,prog.INTITULE_PROGRAMME,prog.CODE_PROGRAMME,act.LIBELLE_ACTION,act.CODE_ACTION,st.DESCR_STATUT_PROJET', 'pip_demande_infos_supp infos JOIN pip_secteur_intervention sec ON infos.ID_SECTEUR_INTERVENTION=sec.ID_SECTEUR_INTERVENTION JOIN inst_institutions inst ON inst.INSTITUTION_ID=infos.INSTITUTION_ID JOIN pilier pil ON pil.ID_PILIER=infos.ID_PILIER JOIN objectif_strategique objs ON infos.ID_OBJECT_STRATEGIQUE=objs.ID_OBJECT_STRATEGIQUE JOIN objectif_strategique_pnd objp ON objp.ID_OBJECT_STRATEGIC_PND=infos.ID_OBJECT_STRATEGIC_PND JOIN axe_intervention_pnd axe ON axe.ID_AXE_INTERVENTION_PND=infos.ID_AXE_INTERVENTION_PND JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=infos.ID_PROGRAMME JOIN inst_institutions_actions act ON act.ACTION_ID =infos.ID_ACTION JOIN pip_statut_projet st ON st.ID_STATUT_PROJET=infos.ID_STATUT_PROJET ', 'infos.ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
		$data['donne_excel'] = $this->ModelPs->getRequeteOne($callpsreq, $excel);

		$data['province_commune_lie'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT communes.COMMUNE_NAME, ID_DEMANDE_INFO_SUPP, provinces.PROVINCE_NAME FROM `pip_lieu_intervention_projet` LEFT JOIN provinces ON pip_lieu_intervention_projet.ID_PROVINCE = provinces.PROVINCE_ID LEFT JOIN communes ON pip_lieu_intervention_projet.ID_COMMUNE = communes.COMMUNE_ID WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = $id')");

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A2', 'FICHE POUR LA PREPARATION DES PROJETS D\'INVESTISSEMENT PUBLIC AXEE SUR LES RESULTATS');

		$sheet->setCellValue('A4', lang('messages_lang.labelle_rubrique'));
		$sheet->setCellValue('B4', lang('messages_lang.labelle_description_projet'));
		$sheet->setCellValue('A5', lang('messages_lang.labelle_statut_du_projet'));
		$sheet->setCellValue('A6', lang('messages_lang.labelle_nom_du_projet'));
		$sheet->setCellValue('A7', lang('messages_lang.labelle_duree_projet'));
		$sheet->setCellValue('A8', lang('messages_lang.question_lieu_intervention'));
		$sheet->setCellValue('A9', lang('messages_lang.Lieu_intervention_projet'));
		$sheet->setCellValue('A10', lang('messages_lang.ministere_tutelle'));
		$sheet->setCellValue('A11', lang('messages_lang.pilier_vision_auquel_se_ratache_projet'));
		$sheet->setCellValue('A12', lang('messages_lang.objectif_strategique_auquel_se_ratache_projet'));
		$sheet->setCellValue('A13', lang('messages_lang.labelle_objectif_pnd'));
		$sheet->setCellValue('A14', lang('messages_lang.labelle_axe_projet'));
		$sheet->setCellValue('A15', lang('messages_lang.labelle_program_budget'));
		$sheet->setCellValue('A16', lang('messages_lang.action_laquelle_ratache_projet'));


		if (isset($data['donne_excel'])) 
		{
			$sheet->setCellValue('B5',  $data['donne_excel']['DESCR_STATUT_PROJET'] ?? "");
			$sheet->setCellValue('B6',  lang('messages_lang.Titre_projet').': '.$data['donne_excel']['NOM_PROJET'] ?? "");
			$sheet->setCellValue('B7',  lang('messages_lang.labelle_date_de_debut').': '.$data['donne_excel']['DATE_DEBUT_PROJET'] ?? "");
			$sheet->setCellValue('C7',  lang('messages_lang.labelle_date_de_fin').': ' . $data['donne_excel']['DATE_FIN_PROJET'] ?? "");
			$sheet->setCellValue('B8',  $data['donne_excel']['EST_REALISE_NATIONAL'] == 1 ? "oui" : "non");

			$sheet->setCellValue('B9',  lang('messages_lang.labelle_provinces'));
			if ($data['donne_excel']['EST_REALISE_NATIONAL'] == 1) 
			{
				$sheet->setCellValue('C9', lang('messages_lang.Toutes_provinces'));
			} 
			else 
			{
				foreach ($data['province_commune_lie'] as $province) 
				{
					$sheet->setCellValue('C9', $province->PROVINCE_NAME);
				}
			}

			$sheet->setCellValue('E9',  lang('messages_lang.labelle_communes'));
			if ($data['donne_excel']['EST_REALISE_NATIONAL'] == 1) 
			{
				$sheet->setCellValue('F9', lang('messages_lang.Toutes_communes'));
			}
			else 
			{
				foreach ($data['province_commune_lie'] as $province) 
				{
					$sheet->setCellValue('C9', $province->COMMUNE_NAME);
				}
			}

			$sheet->setCellValue('B10',  $data['donne_excel']['DESCRIPTION_INSTITUTION'] ?? "");
			$sheet->setCellValue('B11',  $data['donne_excel']['DESCR_PILIER'] ?? "");
			$sheet->setCellValue('B12',  $data['donne_excel']['DESCR_OBJECTIF_STRATEGIC'] ?? "");
			$sheet->setCellValue('B13',  $data['donne_excel']['DESCR_OBJECTIF_STRATEGIC_PND'] ?? "");
			$sheet->setCellValue('B14',  $data['donne_excel']['DESCR_AXE_INTERVATION_PND'] ?? "");
			$sheet->setCellValue('B15',  $data['donne_excel']['INTITULE_PROGRAMME'] ?? "");
			$sheet->setCellValue('B16',  $data['donne_excel']['LIBELLE_ACTION'] ?? "");

			// -----------------------E-----------------------------------------

			$sheet->setCellValue('E6', lang('messages_lang.labelle_numero_projet'));
			$sheet->setCellValue('E7', lang('messages_lang.labelle_duree'));
			$sheet->setCellValue('E11', lang('messages_lang.labelle_n_pilier'));
			$sheet->setCellValue('E12', lang('messages_lang.labelle_num_objectif_str'));
			$sheet->setCellValue('E13', lang('messages_lang.No_de_l_OS'));
			$sheet->setCellValue('E14', lang('messages_lang.No_de_AI'));
			$sheet->setCellValue('E15', lang('messages_lang.labelle_num_program_budget'));
			$sheet->setCellValue('E16', lang('messages_lang.labelle_num_action_projet'));

			$sheet->setCellValue('F6',  $data['donne_excel']['NUMERO_PROJET'] ?? "");
			$sheet->setCellValue('F7',  $data['donne_excel']['DUREE_PROJET'] ?? "");
			$sheet->setCellValue('F11',  $data['donne_excel']['NUMERO_PILIER'] ?? "");
			$sheet->setCellValue('F12',  $data['donne_excel']['NUMERO_OBJECT_STRATEGIC'] ?? "");
			$sheet->setCellValue('F13',  $data['donne_excel']['NUMERO_OBJCTIF_STRATEGIC_PND'] ?? "");
			$sheet->setCellValue('F14',  $data['donne_excel']['NUM_AXE_INTERVENTION_PND'] ?? "");
			$sheet->setCellValue('F15',  $data['donne_excel']['CODE_PROGRAMME'] ?? "");
			$sheet->setCellValue('F16',  $data['donne_excel']['CODE_ACTION'] ?? "");

			//--------ETUDE ET DOCUMENT DE REFERENCE pip_etude_document_ref-----------

			$document_ref = $this->getBindParms('TITRE_ETUDE,DOC_REFERENCE,DATE_REFERENCE,AUTEUR_ORGANISME,OBSERVATION', 'pip_etude_document_reference', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
			$data['document_reference'] = $this->ModelPs->getRequete($callpsreq, $document_ref);

			$rows = 17;
			$sheet->setCellValue('A17', lang('messages_lang.labelle_etude_document'));
			foreach ($data['document_reference'] as $key) 
			{
				$sheet->setCellValue('B' . $rows, $key->TITRE_ETUDE);
				$sheet->setCellValue('C' . $rows, $key->DOC_REFERENCE);
				$sheet->setCellValue('D' . $rows, $key->DATE_REFERENCE);
				$sheet->setCellValue('E' . $rows, $key->AUTEUR_ORGANISME);
				$sheet->setCellValue('F' . $rows, $key->OBSERVATION);
				$rows++;
			}

			$rows = $rows++;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.labelle_contexte_justification'));
			$sheet->setCellValue('B' . $rows,  $data['donne_excel']['PATH_CONTEXTE_JUSTIFICATION']);
			$sheet->setCellValue('A' . ($rows + 1), lang('messages_lang.labelle_obj_general'));
			$sheet->setCellValue('B' . ($rows + 1),  $data['donne_excel']['OBJECTIF_GENERAL']);

			//--------------OBJECTIF SPECIFIQUE pip_demande_objectif_specifique-------
			$rows = $rows + 1;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.labelle_obj_specific'));

			$objectif_spec = $this->getBindParms('DESCR_OBJECTIF', 'pip_demande_objectif_specifique', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
			$data['specifique'] = $this->ModelPs->getRequete($callpsreq, $objectif_spec);
			$data['count_objectif'] = count($data['specifique']);

			foreach ($data['specifique'] as $key) 
			{
				$sheet->setCellValue('B' . $rows, $key->DESCR_OBJECTIF);
				$rows++;
			}

			//------------------------LIVRABLE pip_demande_livrable--------------
			$rows = $rows++;
			$sheet->setCellValue('A' . $rows, 'Livrables/Extrants/Produits:');
			$liv = $this->getBindParms('DESCR_LIVRABLE', 'pip_demande_livrable', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
			$data['livrable'] = $this->ModelPs->getRequete($callpsreq, $liv);
			$data['count_livrable'] = count($data['livrable']);

			foreach ($data['livrable'] as $key) 
			{
				$sheet->setCellValue('B' . $rows, $key->DESCR_LIVRABLE);
				$rows++;
			}
			$rows = $rows++;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.labelle_beneficiaire'));
			$sheet->setCellValue('A' . ($rows + 1), lang('messages_lang.labelle_impact_env_min'));
			$sheet->setCellValue('A' . ($rows + 2), lang('messages_lang.labelle_impact_genre_min'));

			$sheet->setCellValue('B' . $rows,  $data['donne_excel']['BENEFICIAIRE_PROJET']);
			$sheet->setCellValue('B' . ($rows + 1),  $data['donne_excel']['A_UNE_IMPACT_GENRE'] == 1 ? "oui" : "non");
			$sheet->setCellValue('B' . ($rows + 2),  $data['donne_excel']['A_UNE_IMPACT_ENV']== 1 ? "oui" : "non");


			//-------------------------RISQUE pip_risques sur le genre----------
			$rows = $rows + 3;
			if ($data["donne_excel"]["A_UNE_IMPACT_GENRE"] == 1) 
			{
				$risque_req = $this->getBindParms('NOM_RISQUE,MESURE_RISQUE', 'pip_risques_impact_genre', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
				$data['risque'] = $this->ModelPs->getRequete($callpsreq, $risque_req);
				$data['count_risque'] = count($data['risque']);
				$sheet->setCellValue('A' . $rows, lang('messages_lang.Principaux_risques_pouraient_affecter_genre'));

				foreach ($data['risque'] as $key) 
				{
					$sheet->setCellValue('B' . $rows, $key->NOM_RISQUE);
					$sheet->setCellValue('C' . $rows, $key->MESURE_RISQUE);
					$rows++;
				}
			}

			//----------------------RISQUE pip_risques sur l'envirronement'----------
			$rows = $rows++;
			if ($data["donne_excel"]["A_UNE_IMPACT_ENV"] == 1) 
			{
				$risque_req = $this->getBindParms('NOM_RISQUE,MESURE_RISQUE', 'pip_risques_impact_environnement', 'ID_DEMANDE_INFO_SUPP=' . $id . '', '1');
				$data['risque'] = $this->ModelPs->getRequete($callpsreq, $risque_req);
				$data['count_risque'] = count($data['risque']);
				$sheet->setCellValue('A' . $rows, lang('messages_lang.Principaux_risques_pouraient_affecter_envirronnement'));

				foreach ($data['risque'] as $key) 
				{
					$sheet->setCellValue('B' . $rows, $key->NOM_RISQUE);
					$sheet->setCellValue('C' . $rows, $key->MESURE_RISQUE);
					$rows++;
				}
			}

			//---------------CADRE DE MESURE pip_cadre_mesure_resultat-------------------

			//----------------------objectif generale------------------------------------
			$rows = $rows++;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.tab_cmr'));
			$sheet->setCellValue('B' . $rows, lang('messages_lang.Libelle_objectif_generale'));
			$sheet->setCellValue('C' . $rows, lang('messages_lang.Libelle_unite_de_mesure'));
			$sheet->setCellValue('D' . $rows, lang('messages_lang.labelle_valeur_reference').'('.lang('messages_lang.label_annee').')');
			$sheet->setCellValue('F' . $rows, lang('messages_lang.valeur_cible'));
			$sheet->setCellValue('E' . ($rows + 1), 'An1');
			$sheet->setCellValue('F' . ($rows + 1), 'An2');
			$sheet->setCellValue('G' . ($rows + 1), 'An3');
			$sheet->setCellValue('H' . ($rows + 1), lang('messages_lang.Total_sur_durree_projet'));

			$data['general_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,dis.OBJECTIF_GENERAL ,mes.UNITE_MESURE,cad.VALEUR_REFERENCE_ANNE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat_objectif_general cad JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP  WHERE dis.ID_DEMANDE_INFO_SUPP=$id ')");
			$data['count_general'] = count($data['general_req']);

			$rows = $rows + 2;
			$sheet->setCellValue('B' . $rows,  $data['donne_excel']['OBJECTIF_GENERAL']);
			foreach ($data['general_req'] as $key) {
				$sheet->setCellValue('C' . $rows, $key->UNITE_MESURE);
				$sheet->setCellValue('D' . $rows, $key->VALEUR_REFERENCE_ANNE);
				$sheet->setCellValue('E' . $rows, $key->ANNE_UN);
				$sheet->setCellValue('F' . $rows, $key->ANNE_DEUX);
				$sheet->setCellValue('G' . $rows, $key->ANNE_TROIS);
				$sheet->setCellValue('H' . $rows, $key->TOTAL_DURE_PROJET);
				$rows++;
			}
			//---------------------objectif specifique---------------------
			$data['specifique_req1'] = $this->ModelPs->datatable("CALL `getTable`('SELECT DISTINCT dos.DESCR_OBJECTIF FROM pip_cadre_mesure_resultat_objectif_specifique cad  JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_objectif_specifique dos ON dos.ID_DEMANDE_OBJECTIF=cad.ID_DEMANDE_OBJECTIF JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP WHERE dis.ID_DEMANDE_INFO_SUPP=$id')");

			$data['specifique_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,dos.DESCR_OBJECTIF,mes.UNITE_MESURE,cad.VALEUR_REFERENCE_ANNE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat_objectif_specifique cad JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_objectif_specifique dos ON dos.ID_DEMANDE_OBJECTIF=cad.ID_DEMANDE_OBJECTIF JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP WHERE dis.ID_DEMANDE_INFO_SUPP=$id')");
			$data['count_specifique'] = count($data['specifique_req']);
			$rows = $rows++;
			$sheet->setCellValue('B' . $rows, lang('messages_lang.Libelle_objectif_specifique'));
			$sheet->setCellValue('C' . $rows, lang('messages_lang.Libelle_unite_de_mesure'));
			$sheet->setCellValue('D' . $rows, lang('messages_lang.labelle_valeur_reference').'('.lang('messages_lang.label_annee').')');
			$sheet->setCellValue('F' . $rows, lang('messages_lang.valeur_cible'));
			$sheet->setCellValue('E' . ($rows + 1), 'An1');
			$sheet->setCellValue('F' . ($rows + 1), 'An2');
			$sheet->setCellValue('G' . ($rows + 1), 'An3');
			$sheet->setCellValue('H' . ($rows + 1), lang('messages_lang.Total_sur_durree_projet'));

			$rows = $rows + 2;

			foreach ($data['specifique_req1'] as $specif) {
				$sheet->setCellValue('B' . $rows, $specif->DESCR_OBJECTIF);

				foreach ($data['specifique_req'] as $key) 
				{
					$sheet->setCellValue('C' . $rows, $key->UNITE_MESURE);
					$sheet->setCellValue('D' . $rows, $key->VALEUR_REFERENCE_ANNE);
					$sheet->setCellValue('E' . $rows, $key->ANNE_UN);
					$sheet->setCellValue('F' . $rows, $key->ANNE_DEUX);
					$sheet->setCellValue('G' . $rows, $key->ANNE_TROIS);
					$sheet->setCellValue('H' . $rows, $key->TOTAL_DURE_PROJET);
					$rows++;
				}
			}
			//--------------les livrables----------------------------
			$data['livrable_req'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ind.INDICATEUR_MESURE,liv.DESCR_LIVRABLE,mes.UNITE_MESURE,cad.ANNE_UN,cad.ANNE_DEUX,cad.ANNE_TROIS,cad.TOTAL_DURE_PROJET,cad.TOTAL_TRIENNAL FROM pip_cadre_mesure_resultat_livrable cad JOIN unite_mesure mes ON mes.ID_UNITE_MESURE=cad.ID_UNITE_MESURE JOIN pip_indicateur_mesure ind ON ind.ID_INDICATEUR_MESURE=cad.ID_INDICATEUR_MESURE JOIN pip_demande_livrable liv ON liv.ID_DEMANDE_LIVRABLE= cad.ID_LIVRABLE JOIN pip_demande_infos_supp dis ON dis.ID_DEMANDE_INFO_SUPP=cad.ID_DEMANDE_INFO_SUPP WHERE dis.ID_DEMANDE_INFO_SUPP=$id')");
			$data['count_livrable'] = count($data['livrable_req']);
			$rows = $rows++;
			$sheet->setCellValue('B' . $rows, lang('messages_lang.labelle_nom_livrable'));
			$sheet->setCellValue('C' . $rows, lang('messages_lang.Libelle_unite_de_mesure'));
			$sheet->setCellValue('F' . $rows, lang('messages_lang.valeur_cible'));
			$sheet->setCellValue('E' . ($rows + 1), 'An1');
			$sheet->setCellValue('F' . ($rows + 1), 'An2');
			$sheet->setCellValue('G' . ($rows + 1), 'An3');
			$sheet->setCellValue('H' . ($rows + 1), lang('messages_lang.Total_sur_durree_projet'));
			$sheet->setCellValue('I' . ($rows + 1), lang('messages_lang.labelle_total_triennal'));

			$rows = $rows + 2;
			foreach ($data['livrable_req'] as $key)
			{
				$sheet->setCellValue('B' . $rows, $key->DESCR_LIVRABLE);
				$sheet->setCellValue('C' . $rows, $key->UNITE_MESURE);
				$sheet->setCellValue('E' . $rows, $key->ANNE_UN);
				$sheet->setCellValue('F' . $rows, $key->ANNE_DEUX);
				$sheet->setCellValue('G' . $rows, $key->ANNE_TROIS);
				$sheet->setCellValue('H' . $rows, $key->TOTAL_DURE_PROJET);
				$sheet->setCellValue('I' . $rows, $key->TOTAL_TRIENNAL);
				$rows++;
			}


			//----------budget du projet par livrable----------------

			$rows = $rows++;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.tab_bpl'));
			$sheet->setCellValue('B' . $rows, lang('messages_lang.Nom_livrables_Extrants'));
			$sheet->setCellValue('C' . $rows, lang('messages_lang.Cout_unitaire_livrable'));
			$sheet->setCellValue('D' . $rows, lang('messages_lang.labelle_nomenclature'));
			$sheet->setCellValue('D' . ($rows + 1), lang('messages_lang.labelle_nom'));
			$sheet->setCellValue('D' . ($rows + 2), lang('messages_lang.labelle_cout_cible'));
			$sheet->setCellValue('E' . ($rows + 1), lang('messages_lang.code'));
			$sheet->setCellValue('F' . $rows, lang('messages_lang.budget_en_franc_burundais'));
			$sheet->setCellValue('F' . ($rows + 1), 'An 1');
			$sheet->setCellValue('G' . ($rows + 1), 'An 2');
			$sheet->setCellValue('H' . ($rows + 1), 'An 3');
			$sheet->setCellValue('I' . ($rows + 1), lang('messages_lang.Total_sur_durree_projet'));
			$sheet->setCellValue('J' . ($rows + 1), lang('messages_lang.labelle_total_triennal'));

			$requetedebase2 = "SELECT SUM(livra.ANNE_UN) AS TOT1, SUM(livra.ANNE_DEUX) AS TOT2,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO, SUM(livra.ANNE_TROIS) AS TOT3, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id";

			$query_secondaire2 = "CALL `getTable`('" . $requetedebase2 . "');";
			$fetch_data2 = $this->ModelPs->getRequeteOne($query_secondaire2);

			//-------Cout d'atteindre l'objectif
			$totTot = array($fetch_data2['TOT1'], $fetch_data2['TOT2'], $fetch_data2['TOT3']);
			$totTriGen = array_sum($totTot);
			$sheet->setCellValue('F' . ($rows + 2),  $fetch_data2['TOT1']);
			$sheet->setCellValue('G' . ($rows + 2),  $fetch_data2['TOT2']);
			$sheet->setCellValue('H' . ($rows + 2),  $fetch_data2['TOT3']);
			$sheet->setCellValue('I' . ($rows + 2),  $fetch_data2['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 2),  $totTriGen);

			$rows = $rows + 3;

			//-----requette pour la selection des livrables et les couts unitaire pip_cadre_mesure_resultat_livrable
			$requetelivr = "SELECT DISTINCT dem_livra.DESCR_LIVRABLE,dem_livra.ID_DEMANDE_LIVRABLE,dem_livra.COUT_LIVRABLE FROM pip_budget_projet_livrable pro_livra JOIN pip_demande_livrable dem_livra ON dem_livra.ID_DEMANDE_LIVRABLE = pro_livra.ID_DEMANDE_LIVRABLE JOIN pip_demande_infos_supp dem ON dem.ID_DEMANDE_INFO_SUPP=pro_livra.ID_DEMANDE_INFO_SUPP WHERE pro_livra.ID_DEMANDE_INFO_SUPP=$id";

			$query_livra = "CALL `getTable`('" . $requetelivr . "');";
			$fetch_livra = $this->ModelPs->datatable($query_livra);

			$id_demande_livrables = [];
			foreach ($fetch_livra as $key) {
				$sheet->setCellValue('B' . $rows, $key->DESCR_LIVRABLE);
				$sheet->setCellValue('C' . $rows, $key->COUT_LIVRABLE);

				$id_demande_livrables[$key->ID_DEMANDE_LIVRABLE] = $this->ModelPs->getRequete("CALL `getTable` ('SELECT dem_livra.DESCR_LIVRABLE, nom_budg.DESCR_NOMENCLATURE,nom_budg.CODE_NOMENCLATURE, livra.ANNE_UN, livra.ANNE_DEUX,livra.ANNE_TROIS,livra.TOTAL_DUREE_PROJET, livra.TOTAL_TRIENNAL FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN  pip_nomenclature_budgetaire nom_budg ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE JOIN pip_budget_projet_livrable pro_livra ON pro_livra.ID_PROJET_LIVRABLE = livra.ID_PROJET_LIVRABLE JOIN pip_demande_livrable dem_livra ON dem_livra.ID_DEMANDE_LIVRABLE=pro_livra.ID_DEMANDE_LIVRABLE WHERE dem_livra.ID_DEMANDE_LIVRABLE={$key->ID_DEMANDE_LIVRABLE}')");

				foreach ($id_demande_livrables[$key->ID_DEMANDE_LIVRABLE] as $dd) 
				{
					$donneTot = array($dd->ANNE_UN, $dd->ANNE_DEUX, $dd->ANNE_TROIS);
					$totTri = array_sum($donneTot);
					$sheet->setCellValue('D' . $rows, $dd->DESCR_NOMENCLATURE);
					$sheet->setCellValue('E' . $rows, $dd->CODE_NOMENCLATURE);
					$sheet->setCellValue('F' . $rows, $dd->ANNE_UN);
					$sheet->setCellValue('G' . $rows, $dd->ANNE_DEUX);
					$sheet->setCellValue('H' . $rows, $dd->ANNE_TROIS);
					$sheet->setCellValue('I' . $rows, $dd->TOTAL_DUREE_PROJET);
					$sheet->setCellValue('J' . $rows, $totTri);
					$rows++;
				}
			}


			//-------COUT TOTALE DU PROJET ---------------------------
			//#########################################################################

			//---requette pour la somme des livra du personnel
			$requetedebase3 = "SELECT SUM(livra.ANNE_UN) AS TOT1,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO, SUM(livra.ANNE_DEUX) AS TOT2, SUM(livra.ANNE_TROIS) AS TOT3, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id AND  nom_budg.ID_NOMENCLATURE = 1 ";

			$query_secondaire3 = "CALL `getTable`('" . $requetedebase3 . "');";
			$fetch_data3 = $this->ModelPs->getRequeteOne($query_secondaire3);

			//---requette pour la somme des livra des biens de service
			$requetedebase4 = "SELECT SUM(livra.ANNE_UN) AS TOT1,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO, SUM(livra.ANNE_DEUX) AS TOT2, SUM(livra.ANNE_TROIS) AS TOT3, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id AND  nom_budg.ID_NOMENCLATURE = 2 ";

			$query_secondaire4 = "CALL `getTable`('" . $requetedebase4 . "');";
			$fetch_data4 = $this->ModelPs->getRequeteOne($query_secondaire4);

			//---requette pour la somme des livra des transactions en cours
			$requetedebase5 = "SELECT SUM(livra.ANNE_UN) AS TOT1,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO, SUM(livra.ANNE_DEUX) AS TOT2, SUM(livra.ANNE_TROIS) AS TOT3, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id AND  nom_budg.ID_NOMENCLATURE = 3 ";

			$query_secondaire5 = "CALL `getTable`('" . $requetedebase5 . "');";
			$fetch_data5 = $this->ModelPs->getRequeteOne($query_secondaire5);

			//---requette pour la somme des livra des investissements---------
			$requetedebase6 = "SELECT SUM(livra.ANNE_UN) AS TOT1, SUM(livra.ANNE_DEUX) AS TOT2, SUM(livra.ANNE_TROIS) AS TOT3,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id AND  nom_budg.ID_NOMENCLATURE = 4 ";

			$query_secondaire6 = "CALL `getTable`('" . $requetedebase6 . "');";
			$fetch_data6 = $this->ModelPs->getRequeteOne($query_secondaire6);

			//---requette pour la somme des livra transfert de capital-------------
			$requetedebase7 = "SELECT SUM(livra.ANNE_UN) AS TOT1, SUM(livra.ANNE_DEUX) AS TOT2, SUM(livra.ANNE_TROIS) AS TOT3, SUM(livra.TOTAL_TRIENNAL) AS TOTGEN,SUM(livra.TOTAL_DUREE_PROJET) AS DUREPRO FROM pip_budget_livrable_nomenclature_budgetaire livra JOIN pip_nomenclature_budgetaire nom_budg JOIN  pip_budget_projet_livrable pro_livra JOIN pip_demande_infos_supp dem JOIN 	pip_demande_livrable dem_livra ON nom_budg.ID_NOMENCLATURE = livra.ID_NOMENCLATURE 	AND pro_livra.ID_PROJET_LIVRABLE  = livra.ID_PROJET_LIVRABLE AND dem.ID_DEMANDE_INFO_SUPP = pro_livra.ID_DEMANDE_INFO_SUPP AND pro_livra.ID_DEMANDE_INFO_SUPP = dem_livra.ID_DEMANDE_LIVRABLE WHERE  dem.ID_DEMANDE_INFO_SUPP=$id AND  nom_budg.ID_NOMENCLATURE = 5";

			$query_secondaire7 = "CALL `getTable`('" . $requetedebase7 . "');";
			$fetch_data7 = $this->ModelPs->getRequeteOne($query_secondaire7);
			$rows = $rows++;
			//---cout total d'ateindre les cibles------------------
			$sommeGrob1 = 0;
			$sommeGrob2 = 0;
			$sommeGrob3 = 0;
			$durepro = $fetch_data2['DUREPRO'] ?? 0;
			$sommeGrob1 += $fetch_data2['TOT1'];
			$sommeGrob2 += $fetch_data2['TOT2'];
			$sommeGrob3 += $fetch_data2['TOT3'];
			$sheet->setCellValue('B' . $rows, 'COUT TOTAL PROJET');
			$sheet->setCellValue('D' . $rows, lang('messages_lang.cout_total_atteindre_cibles'));
			$sheet->setCellValue('F' . $rows, $sommeGrob1);
			$sheet->setCellValue('G' . $rows, $sommeGrob2);
			$sheet->setCellValue('H' . $rows, $sommeGrob3);
			$sheet->setCellValue('I' . $rows, $durepro);
			$totgGrob = array($sommeGrob1, $sommeGrob2, $sommeGrob3);
			$totTriGen = array_sum($totgGrob);
			$sheet->setCellValue('J' . $rows, $totTriGen);

			//---requette pour la somme des livra du personnel
			$totPers1  = 0;
			$totPers2  = 0;
			$totPers3  = 0;
			$totdur = $fetch_data3['DUREPRO'] ?? 0;
			$totPers1 += $fetch_data3['TOT1'];
			$totPers2 += $fetch_data3['TOT2'];
			$totPers3 += $fetch_data3['TOT3'];
			$totTotPers = array($totPers1, $totPers2, $totPers3);
			$totTriPers = array_sum($totTotPers);
			$sheet->setCellValue('D' . ($rows + 1), lang('messages_lang.personnel'));
			$sheet->setCellValue('F' . ($rows + 1), $totPers1);
			$sheet->setCellValue('G' . ($rows + 1), $totPers2);
			$sheet->setCellValue('H' . ($rows + 1), $totPers3);
			$sheet->setCellValue('I' . ($rows + 1), $totdur);
			$sheet->setCellValue('J' . ($rows + 1), $totTriPers);

			//---requette pour la somme des livra des biens de service
			$totBien1  = 0;
			$totBien2  = 0;
			$totBien3  = 0;
			$totBien1 += $fetch_data4['TOT1'];
			$totBien2 += $fetch_data4['TOT2'];
			$totBien3 += $fetch_data4['TOT3'];
			$totTotBien = array($totBien1, $totBien2, $totBien3);
			$totTriBien = array_sum($totTotBien);
			$sheet->setCellValue('D' . ($rows + 2), lang('messages_lang.Biens_services'));
			$sheet->setCellValue('F' . ($rows + 2), $totBien1);
			$sheet->setCellValue('G' . ($rows + 2), $totBien2);
			$sheet->setCellValue('H' . ($rows + 2), $totBien3);
			$sheet->setCellValue('I' . ($rows + 2), $fetch_data4['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 2), $totTriBien);

			//---requette pour la somme des livra des transactions en cours
			$totTransCour1  = 0;
			$totTransCour2  = 0;
			$totTransCour3  = 0;
			$totTransCour1 += $fetch_data5['TOT1'];
			$totTransCour2 += $fetch_data5['TOT2'];
			$totTransCour3 += $fetch_data5['TOT3'];
			$totTotTransCour = array($totTransCour1, $totTransCour2, $totTransCour3);
			$totTriTransCour = array_sum($totTotTransCour);
			$sheet->setCellValue('D' . ($rows + 3), lang('messages_lang.Transfert_cours'));
			$sheet->setCellValue('F' . ($rows + 3), $totTransCour1);
			$sheet->setCellValue('G' . ($rows + 3), $totTransCour2);
			$sheet->setCellValue('H' . ($rows + 3), $totTransCour3);
			$sheet->setCellValue('I' . ($rows + 3), $fetch_data5['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 3), $totTriTransCour);

			//---requette pour la somme des livra des investissements---------
			$totInvest1  = 0;
			$totInvest2  = 0;
			$totInvest3  = 0;
			$totInvest1 += $fetch_data6['TOT1'];
			$totInvest2 += $fetch_data6['TOT2'];
			$totInvest3 += $fetch_data6['TOT3'];
			$totTotInvest = array($totInvest1, $totInvest2, $totInvest3);
			$totTriInvest = array_sum($totTotInvest);
			$sheet->setCellValue('D' . ($rows + 4), lang('messages_lang.investissement'));
			$sheet->setCellValue('F' . ($rows + 4), $totInvest1);
			$sheet->setCellValue('G' . ($rows + 4), $totInvest2);
			$sheet->setCellValue('H' . ($rows + 4), $totInvest3);
			$sheet->setCellValue('I' . ($rows + 4), $fetch_data6['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 4), $totTriInvest);

			//---requette pour la somme des livra transfert de capital-------------
			$totTransCap1  = 0;
			$totTransCap2  = 0;
			$totTransCap3  = 0;
			$totTransCap1 += $fetch_data7['TOT1'];
			$totTransCap2 += $fetch_data7['TOT2'];
			$totTransCap3 += $fetch_data7['TOT3'];
			$totTotTransCap = array($totTransCap1, $totTransCap2, $totTransCap3);
			$totTriTransCap = array_sum($totTotTransCap);
			$sheet->setCellValue('D' . ($rows + 5), lang('messages_lang.Transfert_capital'));
			$sheet->setCellValue('F' . ($rows + 5), $totTransCap1);
			$sheet->setCellValue('G' . ($rows + 5), $totTransCap2);
			$sheet->setCellValue('H' . ($rows + 5), $totTransCap3);
			$sheet->setCellValue('I' . ($rows + 5), $fetch_data7['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 5), $totTriTransCap);

			// taux de change en BIF
			$taux_req = "SELECT TAUX_CHANGE_EURO,infos.TAUX_CHANGE_USD,infos.OBSERVATION_COMPLEMENTAIRE, infos.DATE_PREPARATION_FICHE_PROJET FROM pip_demande_infos_supp infos WHERE infos.ID_DEMANDE_INFO_SUPP=$id ";

			$query_taux = "CALL `getTable`('" . $taux_req . "');";
			$fetch_data8 = $this->ModelPs->getRequeteOne($query_taux);
			$rows = $rows + 6;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.label_droit_taux'));
			$sheet->setCellValue('B' . $rows, lang('messages_lang.Indiquer_taux_change_pour_pricipales_devises'));
			$sheet->setCellValue('C' . $rows, 'Euro=');
			$sheet->setCellValue('D' . $rows, $fetch_data8['TAUX_CHANGE_EURO']);
			$sheet->setCellValue('E' . $rows, 'USD=');
			$sheet->setCellValue('F' . $rows, $fetch_data8['TAUX_CHANGE_USD']);

			//---------source de financement------------------------

			$liv_req = "SELECT DISTINCT *
	  		FROM pip_demande_source_financement dem_source
	  		JOIN pip_demande_infos_supp dem_info ON dem_info.ID_DEMANDE_INFO_SUPP =dem_source.ID_DEMANDE_INFO_SUPP WHERE dem_info.ID_DEMANDE_INFO_SUPP=$id ";

			$query_liv = "CALL `getTable`('" . $liv_req . "');";
			$fetch_liv = $this->ModelPs->getRequete($query_liv);

			$data["demande_source_financements"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT pip_demande_source_financement.ANNE_UN, pip_demande_source_financement.ANNE_DEUX, pip_demande_source_financement.ANNE_TROIS, pip_demande_source_financement.TOTAL_DUREE_PROJET, pip_demande_source_financement.TOTAL_TRIENNAL, pip_source_financement_bailleur.NOM_SOURCE_FINANCE, pip_source_financement_bailleur.CODE_BAILLEUR FROM pip_demande_source_financement LEFT JOIN pip_source_financement_bailleur ON pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR = pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR WHERE pip_demande_source_financement.ID_DEMANDE_INFO_SUPP = " . $id . "')");

			//requette pour les sous_totals-----------------------------
			$req1 = "SELECT SUM(dem_source.ANNE_UN) as STOT1, SUM(dem_source.ANNE_DEUX) as STOT2,SUM(dem_source.ANNE_TROIS) as STOT3,SUM(dem_source.TOTAL_DUREE_PROJET) AS DUREPRO,SUM(dem_source.TOTAL_TRIENNAL) as TOTTRI1 FROM pip_demande_source_financement dem_source JOIN pip_source_financement_bailleur s_fina ON s_fina.ID_SOURCE_FINANCE_BAILLEUR = dem_source.ID_SOURCE_FINANCE_BAILLEUR JOIN pip_demande_infos_supp dem_info ON dem_info.ID_DEMANDE_INFO_SUPP =dem_source.ID_DEMANDE_INFO_SUPP WHERE dem_info.ID_DEMANDE_INFO_SUPP=$id ";

			$query_1 = "CALL `getTable`('" . $req1 . "');";
			$fetch_d1 = $this->ModelPs->getRequeteOne($query_1);
			$rows = $rows + 1;
			$sheet->setCellValue('A' . $rows, lang('messages_lang.Source_financement_projet_toutes_valeurs_doivent_etre_convertis_en_bif'));
			$sheet->setCellValue('C' . $rows, lang('messages_lang.labelle_nom_source_finance'));
			$sheet->setCellValue('E' . $rows, lang('messages_lang.Code_bailleur'));
			$sheet->setCellValue('F' . $rows, lang('messages_lang.valeur_nominale'));
			$sheet->setCellValue('F' . ($rows + 1), 'An1');
			$sheet->setCellValue('G' . ($rows + 1), 'An2');
			$sheet->setCellValue('H' . ($rows + 1), 'An3');
			$sheet->setCellValue('I' . ($rows + 1), lang('messages_lang.Total_sur_durree_projet'));
			$sheet->setCellValue('J' . ($rows + 1), lang('messages_lang.labelle_total_triennal'));
			$rows = $rows + 2;

			foreach ($data["demande_source_financements"] as $dd) 
			{
				$donneTot = array($dd->ANNE_UN, $dd->ANNE_DEUX, $dd->ANNE_TROIS);
				$totTri = array_sum($donneTot);
				$sheet->setCellValue('C' . $rows, $dd->NOM_SOURCE_FINANCE);
				$sheet->setCellValue('E' . $rows, $dd->CODE_BAILLEUR);
				$sheet->setCellValue('F' . $rows, $dd->ANNE_UN);
				$sheet->setCellValue('G' . $rows, $dd->ANNE_DEUX);
				$sheet->setCellValue('H' . $rows, $dd->ANNE_TROIS);
				$sheet->setCellValue('I' . $rows, $dd->TOTAL_DUREE_PROJET);
				$sheet->setCellValue('J' . $rows, $totTri);
				$rows++;
			}
			$rows2 = $rows++;

			//------------les totos 
			$rows = $rows2 + 1;
			$sheet->setCellValue('B' . $rows, lang('messages_lang.Totale_financement_projet'));
			$sheet->setCellValue('B' . ($rows + 1), lang('messages_lang.Cout_total_projet'));
			$sheet->setCellValue('B' . ($rows + 2),lang('messages_lang.labelle_gap_financement').'('.lang('a_chercher').')');

			//----total source des finances----
			$totfinance1  = 0;
			$totfinance2  = 0;
			$totfinance3  = 0;
			$totfinance1 += $fetch_d1['STOT1'];
			$totfinance2 += $fetch_d1['STOT2'];
			$totfinance3 += $fetch_d1['STOT3'];
			$totTotFin = array($totfinance1, $totfinance2, $totfinance3);
			$totTriFinance = array_sum($totTotFin);
			$sheet->setCellValue('F' . $rows, $totfinance1);
			$sheet->setCellValue('G' . $rows, $totfinance2);
			$sheet->setCellValue('H' . $rows, $totfinance3);
			$sheet->setCellValue('I' . $rows, $fetch_d1['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . $rows, $totTriFinance);

			//---total cout du projet------- somme global vient du demande budget livrable
			$sheet->setCellValue('F' . ($rows + 1), $sommeGrob1);
			$sheet->setCellValue('G' . ($rows + 1), $sommeGrob2);
			$sheet->setCellValue('H' . ($rows + 1), $sommeGrob3);
			$sheet->setCellValue('I' . ($rows + 1), $fetch_d1['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 1), $totTriGen);

			//---gap ---------------------
			$gap1 = 0;
			$gap2 = 0;
			$gap3 = 0;
			$gap1 = $totfinance1 - $sommeGrob1;
			$gap2 = $totfinance2 - $sommeGrob2;
			$gap3 = $totfinance3 - $sommeGrob3;
			$totogap = array($gap1, $gap2, $gap3);
			$totGap = array_sum($totogap);
			$sheet->setCellValue('F' . ($rows + 2), $gap1);
			$sheet->setCellValue('G' . ($rows + 2), $gap2);
			$sheet->setCellValue('H' . ($rows + 2), $gap3);
			$sheet->setCellValue('I' . ($rows + 2), $fetch_d1['DUREPRO'] ?? 0);
			$sheet->setCellValue('J' . ($rows + 2), $totGap);

			//--------responsable du projet
			$rows = $rows + 3;
			$session  = \Config\Services::session();
			$session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$user_req = "SELECT us.EMAIL, us.TELEPHONE1, us.TELEPHONE2,inst.DESCRIPTION_INSTITUTION FROM user_users us JOIN inst_institutions inst ON inst.INSTITUTION_ID=us.INSTITUTION_ID WHERE 1 AND us.USER_ID = $user_id";

			$query_user = "CALL `getTable`('" . $user_req . "');";
			$fetch_user = $this->ModelPs->getRequeteOne($query_user);


			$sheet->setCellValue('A' . $rows, lang('messages_lang.observation_complementaire'));
			$sheet->setCellValue('A' . ($rows + 1), lang('messages_lang.date_préparation_fiche_projet'));
			$sheet->setCellValue('A' . ($rows + 2), lang('messages_lang.labelle_responsable_projet'));

			$sheet->setCellValue('B' . $rows, $data['donne_excel']['OBSERVATION_COMPLEMENTAIRE']);
			$sheet->setCellValue('B' . ($rows + 1), $data['donne_excel']['DATE_PREPARATION_FICHE_PROJET']);

			$sheet->setCellValue('B' . ($rows + 2), $fetch_user['DESCRIPTION_INSTITUTION']);
			$sheet->setCellValue('D' . ($rows + 2), $fetch_user['EMAIL']);
			$sheet->setCellValue('F' . ($rows + 2), $fetch_user['TELEPHONE1'] . ' / ' . $fetch_user['TELEPHONE2']);

			$writer = new Xlsx($spreadsheet);
			$writer->save('suivi.xlsx');
			return $this->response->download('suivi.xlsx', null)->setFileName('Fichier Pip.xlsx');
		} 
		else 
		{
			echo (''.lang('dossier_vide').'');
		}
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>