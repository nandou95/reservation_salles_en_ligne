<?php
/**NDERAGAKURA Alain Charbel
*Titre: Script d'adaptation des donnees du prod dans la nouvelles modelisation
*Numero de telephone: +257 62 003 522
*Email pro: charbel@mediabox.bi
*Date: 25 nov 2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Script_Migration_V3_Vers_Prod extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	/* Debut Gestion insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	public function traitement()
	{
		// $session  = \Config\Services::session();
    // $user_id ='';
		// if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    // {
    //   $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    // }
    // else
    // {
    //   return redirect('Login_Ptba/do_logout');
    // }
		$db = db_connect();
		// $db->transStart();
		//get data from execution_budgetaire execution_budgetaire_tache_info_suppl
		$exec="SELECT *,(exec.USER_ID) FROM execution_budgetaire exec JOIN execution_budgetaire_tache_info_suppl supp ON supp.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE 1";
		$exec=$this->ModelPs->getRequete("CALL `getTable`('".$exec."')");

		foreach ($exec as $key)
		{
			//save dans execution_budgetaire_backup
			$str_exec_budg='';
					$value_exec_budg="";
			// $NUMERO_BON_ENGAGEMENT=!empty($key->NUMERO_BON_ENGAGEMENT)?$key->NUMERO_BON_ENGAGEMENT:NULL;
			if(!empty($key->NUMERO_BON_ENGAGEMENT))
			{
				$str_exec_budg.=',NUMERO_BON_ENGAGEMENT';
				$value_exec_budg.=",'{$key->NUMERO_BON_ENGAGEMENT}'";
			}
			$ENG_BUDGETAIRE_DEVISE=!empty($key->ENG_BUDGETAIRE_DEVISE)?$key->ENG_BUDGETAIRE_DEVISE:0;
			// $PATH_BON_ENGAGEMENT=!empty($key->PATH_BON_ENGAGEMENT)?$key->PATH_BON_ENGAGEMENT:NULL;
			// $DATE_BON_ENGAGEMENT=!empty($key->DATE_BON_ENGAGEMENT)?$key->DATE_BON_ENGAGEMENT:NULL;
			if(!empty($key->DATE_BON_ENGAGEMENT))
			{
				$str_exec_budg.=',DATE_BON_ENGAGEMENT';
				$value_exec_budg.=",'{$key->DATE_BON_ENGAGEMENT}'";
			}
			$ENG_JURIDIQUE=!empty($key->ENG_JURIDIQUE)?$key->ENG_JURIDIQUE:0;
			$ENG_JURIDIQUE_DEVISE=!empty($key->ENG_JURIDIQUE_DEVISE)?$key->ENG_JURIDIQUE_DEVISE:0;
			// $DATE_ENG_JURIDIQUE=!empty($key->DATE_ENG_JURIDIQUE)?$key->DATE_ENG_JURIDIQUE:NULL;
			if(!empty($key->DATE_ENG_JURIDIQUE))
			{
				$str_exec_budg.=',DATE_ENG_JURIDIQUE';
				$value_exec_budg.=",'{$key->DATE_ENG_JURIDIQUE}'";
			}
			$DEVISE_TYPE_HISTO_JURD_ID=!empty($key->DEVISE_TYPE_HISTO_JURD_ID)?$key->DEVISE_TYPE_HISTO_JURD_ID:NULL;
			if(!empty($key->DEVISE_TYPE_HISTO_JURD_ID))
			{
				$str_exec_budg.=',DEVISE_TYPE_HISTO_JURD_ID';
				$value_exec_budg.=",'{$key->DEVISE_TYPE_HISTO_JURD_ID}'";
			}
			$LIQUIDATION_TYPE_ID=!empty($key->LIQUIDATION_TYPE_ID)?$key->LIQUIDATION_TYPE_ID:NULL;
			if(!empty($key->LIQUIDATION_TYPE_ID))
			{
				$str_exec_budg.=',LIQUIDATION_TYPE_ID';
				$value_exec_budg.=",{$key->LIQUIDATION_TYPE_ID}";
			}
			$LIQUIDATION=!empty($key->LIQUIDATION)?$key->LIQUIDATION:0;
			$LIQUIDATION_DEVISE=!empty($key->LIQUIDATION_DEVISE)?$key->LIQUIDATION_DEVISE:0;
			$ORDONNANCEMENT=!empty($key->ORDONNANCEMENT)?$key->ORDONNANCEMENT:0;
			$ORDONNANCEMENT_DEVISE=!empty($key->ORDONNANCEMENT_DEVISE)?$key->ORDONNANCEMENT_DEVISE:0;
			$PAIEMENT=!empty($key->PAIEMENT)?$key->PAIEMENT:0;
			$PAIEMENT_DEVISE=!empty($key->PAIEMENT_DEVISE)?$key->PAIEMENT_DEVISE:0;
			$DECAISSEMENT=!empty($key->DECAISSEMENT)?$key->DECAISSEMENT:0;
			$DECAISSEMENT_DEVISE=!empty($key->DECAISSEMENT_DEVISE)?$key->DECAISSEMENT_DEVISE:0;
			// $PREUVE=!empty($key->PREUVE)?$key->PREUVE:NULL;
			if(!empty($key->PREUVE))
			{
				$str_exec_budg.=',PREUVE';
				$value_exec_budg.=",'{$key->PREUVE}'";
			}
			$IS_FINISHED=!empty($key->IS_FINISHED)?$key->IS_FINISHED:0;
			$COMMENTAIRE=addslashes($key->COMMENTAIRE);

			$table_exec="v3_execution_budgetaire";
			$columsinsert_exec="EXECUTION_BUDGETAIRE_ID,ANNEE_BUDGETAIRE_ID,INSTITUTION_ID,SOUS_TUTEL_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_HISTO_ENG_ID,ENG_JURIDIQUE,ENG_JURIDIQUE_DEVISE,LIQUIDATION,LIQUIDATION_DEVISE,ORDONNANCEMENT,ORDONNANCEMENT_DEVISE,PAIEMENT,PAIEMENT_DEVISE,DECAISSEMENT,DECAISSEMENT_DEVISE,DEVISE_TYPE_ID,USER_ID,MARCHE_PUBLIQUE,IS_FINISHED,COMMENTAIRE,TYPE_ENGAGEMENT_ID,EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID,DATE_DEMANDE".$str_exec_budg;
			  
			$data_exec="{$key->EXECUTION_BUDGETAIRE_ID},{$key->ANNEE_BUDGETAIRE_ID},{$key->INSTITUTION_ID},{$key->SOUS_TUTEL_ID},{$key->CODE_NOMENCLATURE_BUDGETAIRE_ID},{$key->TRIMESTRE_ID},'{$key->ENG_BUDGETAIRE}','{$ENG_BUDGETAIRE_DEVISE}',{$key->DEVISE_TYPE_HISTO_ENG_ID},'{$ENG_JURIDIQUE}','{$ENG_JURIDIQUE_DEVISE}','{$LIQUIDATION}','{$LIQUIDATION_DEVISE}','{$ORDONNANCEMENT}','{$ORDONNANCEMENT_DEVISE}','{$PAIEMENT}','{$PAIEMENT_DEVISE}','{$DECAISSEMENT}','{$DECAISSEMENT_DEVISE}',{$key->DEVISE_TYPE_ID},{$key->USER_ID},{$key->MARCHE_PUBLIQUE},{$IS_FINISHED},'{$COMMENTAIRE}',{$key->TYPE_ENGAGEMENT_ID},1,'{$key->DATE_DEMANDE}'".$value_exec_budg;
			$this->save_all_table($table_exec,$columsinsert_exec,$data_exec);

			//save dans execution_budgetaire_tache_info_suppl_backup
			$str_info_suppl='';
			$value_info_suppl="";
			// $MODELE_ID=!empty($key->MODELE_ID)?$key->MODELE_ID:NULL;
			if(!empty($key->MODELE_ID))
			{
				$str_info_suppl.=',MODELE_ID';
				$value_info_suppl.=",{$key->MODELE_ID}";
			}
			// $REFERENCE=!empty($key->REFERENCE)?$key->REFERENCE:NULL;
			if(!empty($key->REFERENCE))
			{
				$REFERENCE=addslashes($key->REFERENCE);
				$str_info_suppl.=',REFERENCE';
				$value_info_suppl.=",'{$REFERENCE}'";
			}
			// $TYPE_BENEFICIAIRE_ID=!empty($key->TYPE_BENEFICIAIRE_ID)?$key->TYPE_BENEFICIAIRE_ID:NULL;
			if(!empty($key->TYPE_BENEFICIAIRE_ID))
			{
				$str_info_suppl.=',TYPE_BENEFICIAIRE_ID';
				$value_info_suppl.=",'{$key->TYPE_BENEFICIAIRE_ID}'";
			}
			// $PRESTATAIRE_ID=!empty($key->PRESTATAIRE_ID)?$key->PRESTATAIRE_ID:NULL;
			if(!empty($key->PRESTATAIRE_ID))
			{
				$str_info_suppl.=',PRESTATAIRE_ID';
				$value_info_suppl.=",{$key->PRESTATAIRE_ID}";
			}
			// $PATH_PPM=!empty($key->PATH_PPM)?$key->PATH_PPM:NULL;
			if(!empty($key->PATH_PPM))
			{
				$str_info_suppl.=',PATH_PPM';
				$value_info_suppl.=",'{$key->PATH_PPM}'";
			}
			// $PATH_PV_ATTRIBUTION=!empty($key->PATH_PV_ATTRIBUTION)?$key->PATH_PV_ATTRIBUTION:NULL;
			if(!empty($key->PATH_PV_ATTRIBUTION))
			{
				$str_info_suppl.=',PATH_PV_ATTRIBUTION';
				$value_info_suppl.=",'{$key->PATH_PV_ATTRIBUTION}'";
			}
			// $DATE_APPROBATION_CONTRAT=!empty($key->DATE_APPROBATION_CONTRAT)?$key->DATE_APPROBATION_CONTRAT:NULL;
			if(!empty($key->DATE_APPROBATION_CONTRAT))
			{
				$str_info_suppl.=',DATE_APPROBATION_CONTRAT';
				$value_info_suppl.=",'{$key->DATE_APPROBATION_CONTRAT}'";
			}
			$STATUT_CONTRAT_APPROUVE=!empty($key->STATUT_CONTRAT_APPROUVE)?$key->STATUT_CONTRAT_APPROUVE:0;
			$PATH_CONTRAT=!empty($key->PATH_CONTRAT)?$key->PATH_CONTRAT:NULL;
			if(!empty($key->PATH_CONTRAT))
			{
				$str_info_suppl.=',PATH_CONTRAT';
				$value_info_suppl.=",'{$key->PATH_CONTRAT}'";
			}
			// $BUDGETAIRE_TYPE_DOCUMENT_ID=!empty($key->BUDGETAIRE_TYPE_DOCUMENT_ID)?$key->BUDGETAIRE_TYPE_DOCUMENT_ID:NULL;
			if(!empty($key->BUDGETAIRE_TYPE_DOCUMENT_ID))
			{
				$str_info_suppl.=',BUDGETAIRE_TYPE_DOCUMENT_ID';
				$value_info_suppl.=",{$key->BUDGETAIRE_TYPE_DOCUMENT_ID}";
			}
			// $PATH_LETTRE_TRANSMISSION=!empty($key->PATH_LETTRE_TRANSMISSION)?$key->PATH_LETTRE_TRANSMISSION:NULL;
			if(!empty($key->PATH_LETTRE_TRANSMISSION))
			{
				$str_info_suppl.=',PATH_LETTRE_TRANSMISSION';
				$value_info_suppl.=",'{$key->PATH_LETTRE_TRANSMISSION}'";
			}
			// $PATH_LISTE_PAIE=!empty($key->PATH_LISTE_PAIE)?$key->PATH_LISTE_PAIE:NULL;
			if(!empty($key->PATH_LISTE_PAIE))
			{
				$str_info_suppl.=',PATH_LISTE_PAIE';
				$value_info_suppl.=",'{$key->PATH_LISTE_PAIE}'";
			}
			// $DATE_DEBUT_CONTRAT=!empty($key->DATE_DEBUT_CONTRAT)?$key->DATE_DEBUT_CONTRAT:NULL;
			if(!empty($key->DATE_DEBUT_CONTRAT))
			{
				$str_info_suppl.=',DATE_DEBUT_CONTRAT';
				$value_info_suppl.=",'{$key->DATE_DEBUT_CONTRAT}'";
			}
			// $DATE_FIN_CONTRAT=!empty($key->DATE_FIN_CONTRAT)?$key->DATE_FIN_CONTRAT:NULL;
			if(!empty($key->DATE_FIN_CONTRAT))
			{
				$str_info_suppl.=',DATE_FIN_CONTRAT';
				$value_info_suppl.=",'{$key->DATE_FIN_CONTRAT}'";
			}
			// $NBRE_JR_CONTRAT=!empty($key->NBRE_JR_CONTRAT)?$key->NBRE_JR_CONTRAT:NULL;
			if(!empty($key->NBRE_JR_CONTRAT))
			{
				$str_info_suppl.=',NBRE_JR_CONTRAT';
				$value_info_suppl.=",{$key->NBRE_JR_CONTRAT}";
			}
			// $NBRE_JR_RETARD=!empty($key->NBRE_JR_RETARD)?$key->NBRE_JR_RETARD:NULL;
			if(!empty($key->NBRE_JR_RETARD))
			{
				$str_info_suppl.=',NBRE_JR_RETARD';
				$value_info_suppl.=",{$key->NBRE_JR_RETARD}";
			}
			// $MONTANT_AMENDE_PAYER=!empty($key->MONTANT_AMENDE_PAYER)?$key->MONTANT_AMENDE_PAYER:NULL;
			if(!empty($key->MONTANT_AMENDE_PAYER))
			{
				$str_info_suppl.=',MONTANT_AMENDE_PAYER';
				$value_info_suppl.=",'{$key->MONTANT_AMENDE_PAYER}'";
			}
			// $PATH_AVIS_DNCMP=!empty($key->PATH_AVIS_DNCMP)?$key->PATH_AVIS_DNCMP:NULL;
			if(!empty($key->PATH_AVIS_DNCMP))
			{
				$str_info_suppl.=',PATH_AVIS_DNCMP';
				$value_info_suppl.=",'{$key->PATH_AVIS_DNCMP}'";
			}
			// $ID_TYPE_MARCHE=!empty($key->ID_TYPE_MARCHE)?$key->ID_TYPE_MARCHE:NULL;
			if(!empty($key->ID_TYPE_MARCHE))
			{
				$str_info_suppl.=',ID_TYPE_MARCHE';
				$value_info_suppl.=",{$key->ID_TYPE_MARCHE}";
			}

			$table_supp="v3_execution_budgetaire_tache_info_suppl";
			$columsinsert_supp="EXECUTION_BUDGETAIRE_INFO_SUPPL_ID,EXECUTION_BUDGETAIRE_ID,STATUT_CONTRAT_APPROUVE".$str_info_suppl;
			$data_supp="{$key->EXECUTION_BUDGETAIRE_INFO_SUPPL_ID},{$key->EXECUTION_BUDGETAIRE_ID},{$STATUT_CONTRAT_APPROUVE}".$value_info_suppl;
			$this->save_all_table($table_supp,$columsinsert_supp,$data_supp);

			//save dans execution_budgetaire_execution_tache
			$UNITE=addslashes($key->UNITE);
			$QTE_RACCROCHE=addslashes($key->QTE_RACCROCHE);
			$OBSERVATION_RESULTAT=addslashes($key->OBSERVATION_RESULTAT);
			$RESULTAT_ATTENDUS=addslashes($key->RESULTAT_ATTENDUS);
			$table_exec_tache='v3_execution_budgetaire_execution_tache';
			$columns_exec_tache='EXECUTION_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,MONTANT_ENG_JURIDIQUE,MONTANT_ENG_JURIDIQUE_DEVISE,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE,MONTANT_ORDONNANCEMENT,MONTANT_ORDONNANCEMENT_DEVISE,MONTANT_PAIEMENT,MONTANT_PAIEMENT_DEVISE,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE,UNITE,QTE,COMMENTAIRE,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,USER_ID,DATE_DEMANDE';

			$data_exec_tache="{$key->EXECUTION_BUDGETAIRE_ID},{$key->PTBA_TACHE_ID},{$key->ENG_BUDGETAIRE},{$key->ENG_BUDGETAIRE_DEVISE},{$ENG_JURIDIQUE},{$ENG_JURIDIQUE_DEVISE},{$LIQUIDATION},{$LIQUIDATION_DEVISE},{$ORDONNANCEMENT},{$ORDONNANCEMENT_DEVISE},{$PAIEMENT},{$PAIEMENT_DEVISE},{$DECAISSEMENT},{$DECAISSEMENT_DEVISE},'{$UNITE}','{$QTE_RACCROCHE}','{$COMMENTAIRE}',{$key->EST_SOUS_TACHE},{$key->EST_FINI_TACHE},'{$RESULTAT_ATTENDUS}','{$OBSERVATION_RESULTAT}',{$key->USER_ID},'{$key->DATE_DEMANDE}'";
			$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=$this->save_all_table($table_exec_tache,$columns_exec_tache,$data_exec_tache);

			//insertion dans execution_budgetaire_histo_budget_marche_public			
			if($key->MARCHE_PUBLIQUE==1)
			{
				$table_marche='v3_execution_budgetaire_histo_budget_marche_public';			
				$budget='';

				if ($key->TRIMESTRE_ID==1)
				{
					$budget=',BUDGET_ENLEVE_T1,';
				}
				elseif ($key->TRIMESTRE_ID==2)
				{
					$budget=',BUDGET_ENLEVE_T2,';
				}
				$columns_marche= 'EXECUTION_BUDGETAIRE_ID'.$budget.'ANNEE_BUDGETAIRE_ID';
				$data_marche="{$key->EXECUTION_BUDGETAIRE_ID},'{$key->ENG_BUDGETAIRE}',{$key->ANNEE_BUDGETAIRE_ID}";
				$this->save_all_table($table_marche,$columns_marche,$data_marche);
			}

			//get data from execution_budgetaire_tache_detail
			$det="SELECT * FROM execution_budgetaire_tache_detail WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID;
			$det=$this->ModelPs->getRequete("CALL `getTable`('".$det."')");
			foreach ($det as $val)
			{
				//save dans execution_budgetaire_tache_detail_backup
				$str_tache_detail='';
				$value_tache_detail='';
				$MONTANT_LIQUIDATION=$val->MONTANT_LIQUIDATION?$val->MONTANT_LIQUIDATION:0;
				$MONTANT_LIQUIDATION_DEVISE=$val->MONTANT_LIQUIDATION_DEVISE?$val->MONTANT_LIQUIDATION_DEVISE:0;
				// $DEVISE_TYPE_HISTO_LIQUI_ID=$val->DEVISE_TYPE_HISTO_LIQUI_ID?$val->DEVISE_TYPE_HISTO_LIQUI_ID:NULL;
				if(!empty($val->DEVISE_TYPE_HISTO_LIQUI_ID))
				{
					$str_tache_detail=',DEVISE_TYPE_HISTO_LIQUI_ID';
					$value_tache_detail=",{$val->DEVISE_TYPE_HISTO_LIQUI_ID}";
				}

				// $MOTIF_LIQUIDATION=$val->MOTIF_LIQUIDATION?$val->MOTIF_LIQUIDATION:NULL;
				if(!empty($val->MOTIF_LIQUIDATION))
				{
					$MOTIF_LIQUIDATION=addslashes($val->MOTIF_LIQUIDATION);
					$str_tache_detail.=',MOTIF_LIQUIDATION';
					$value_tache_detail.=",'{$MOTIF_LIQUIDATION}'";
				}

				// $DATE_LIQUIDATION=$val->DATE_LIQUIDATION?$val->DATE_LIQUIDATION:NULL;
				if(!empty($val->DATE_LIQUIDATION))
				{
					$str_tache_detail.=',DATE_LIQUIDATION';
					$value_tache_detail.=",'{$val->DATE_LIQUIDATION}'";
				}

				$MONTANT_ORDONNANCEMENT=$val->MONTANT_ORDONNANCEMENT?$val->MONTANT_ORDONNANCEMENT:0;
				$MONTANT_ORDONNANCEMENT_DEVISE=$val->MONTANT_ORDONNANCEMENT_DEVISE?$val->MONTANT_ORDONNANCEMENT_DEVISE:0;
				// $DATE_ORDONNANCEMENT=$val->DATE_ORDONNANCEMENT?$val->DATE_ORDONNANCEMENT:NULL;
				if(!empty($val->DATE_ORDONNANCEMENT))
				{
					$str_tache_detail.=',DATE_ORDONNANCEMENT';
					$value_tache_detail.=",'{$val->DATE_ORDONNANCEMENT}'";
				}

				// $TITRE_CREANCE=$val->TITRE_CREANCE?$val->TITRE_CREANCE:NULL;
				if(!empty($val->TITRE_CREANCE))
				{
					$str_tache_detail.=',TITRE_CREANCE';
					$value_tache_detail.=",'{$val->TITRE_CREANCE}'";
				}

				$MONTANT_CREANCE=$val->MONTANT_CREANCE?$val->MONTANT_CREANCE:0;
				// $DATE_CREANCE=$val->DATE_CREANCE?$val->DATE_CREANCE:NULL;
				if(!empty($val->DATE_CREANCE))
				{
					$str_tache_detail.=',DATE_CREANCE';
					$value_tache_detail.=",'{$val->DATE_CREANCE}'";
				}

				// $DATE_LIVRAISON_CONTRAT=$key->DATE_LIVRAISON_CONTRAT?$key->DATE_LIVRAISON_CONTRAT:NULL;
				if(!empty($key->DATE_LIVRAISON_CONTRAT))
				{
					$str_tache_detail.=',DATE_LIVRAISON_CONTRAT';
					$value_tache_detail.=",'{$key->DATE_LIVRAISON_CONTRAT}'";
				}

				// $COUR_DEVISE=$val->COUR_DEVISE?$val->COUR_DEVISE:NULL;
				if(!empty($val->COUR_DEVISE))
				{
					$str_tache_detail.=',COUR_DEVISE';
					$value_tache_detail.=",'{$val->COUR_DEVISE}'";
				}

				// $DATE_COUR_DEVISE=$val->DATE_COUR_DEVISE?$val->DATE_COUR_DEVISE:NULL;
				if(!empty($val->DATE_COUR_DEVISE))
				{
					$str_tache_detail.=',DATE_COUR_DEVISE';
					$value_tache_detail.=",'{$val->DATE_COUR_DEVISE}'";
				}

				$MOTANT_FACTURE=$val->MOTANT_FACTURE?$val->MOTANT_FACTURE:0;
				// $PATH_FACTURE_LIQUIDATION=$val->PATH_FACTURE_LIQUIDATION?$val->PATH_FACTURE_LIQUIDATION:NULL;
				if(!empty($val->PATH_FACTURE_LIQUIDATION))
				{
					$str_tache_detail.=',PATH_FACTURE_LIQUIDATION';
					$value_tache_detail.=",'{$val->PATH_FACTURE_LIQUIDATION}'";
				}

				// $DATE_PRISE_CHARGE=$val->DATE_PRISE_CHARGE?$val->DATE_PRISE_CHARGE:NULL;
				if(!empty($val->DATE_PRISE_CHARGE))
				{
					$str_tache_detail.=',DATE_PRISE_CHARGE';
					$value_tache_detail.=",'{$val->DATE_PRISE_CHARGE}'";
				}

				// $DATE_ENVOIE_OBR=$val->DATE_ENVOIE_OBR?$val->DATE_ENVOIE_OBR:NULL;
				if(!empty($val->DATE_ENVOIE_OBR))
				{
					$str_tache_detail.=',DATE_ENVOIE_OBR';
					$value_tache_detail.=",'{$val->DATE_ENVOIE_OBR}'";
				}

				// $RESULTAT_ANALYSE_ID=$val->RESULTAT_ANALYSE_ID?$val->RESULTAT_ANALYSE_ID:NULL;
				if(!empty($val->RESULTAT_ANALYSE_ID))
				{
					$str_tache_detail.=',RESULTAT_ANALYSE_ID';
					$value_tache_detail.=",{$val->RESULTAT_ANALYSE_ID}";
				}

				// $TAUX_TVA_ID=$key->TAUX_TVA_ID?$key->TAUX_TVA_ID:NULL;
				if(!empty($key->TAUX_TVA_ID))
				{
					$str_tache_detail.=',TAUX_TVA_ID';
					$value_tache_detail.=",{$key->TAUX_TVA_ID}";
				}

				// $EXONERATION=$key->EXONERATION?$key->EXONERATION:NULL;
				if(!empty($key->EXONERATION))
				{
					$str_tache_detail.=',EXONERATION';
					$value_tache_detail.=",{$key->EXONERATION}";
				}
				$MONTANT_PRELEVEMENT_FISCALES=$key->MONTANT_PRELEVEMENT_FISCALES?$key->MONTANT_PRELEVEMENT_FISCALES:0;

				// $RESULTANT_TYPE_ID=$key->RESULTANT_TYPE_ID?$key->RESULTANT_TYPE_ID:NULL;
				if(!empty($key->RESULTANT_TYPE_ID))
				{
					$str_tache_detail.=',RESULTANT_TYPE_ID';
					$value_tache_detail.=",{$key->RESULTANT_TYPE_ID}";
				}

				// $PATH_PV_RECEPTION_LIQUIDATION=$val->PATH_PV_RECEPTION_LIQUIDATION?$val->PATH_PV_RECEPTION_LIQUIDATION:NULL;
				if(!empty($val->PATH_PV_RECEPTION_LIQUIDATION))
				{
					$str_tache_detail.=',PATH_PV_RECEPTION_LIQUIDATION';
					$value_tache_detail.=",'{$val->PATH_PV_RECEPTION_LIQUIDATION}'";
				}

				// $PATH_LETTRE_OTB=$key->PATH_LETTRE_OTB?$key->PATH_LETTRE_OTB:NULL;
				if(!empty($key->PATH_LETTRE_OTB))
				{
					$str_tache_detail.=',PATH_NOTE_A_LA_DCP';
					$value_tache_detail.=",'{$key->PATH_LETTRE_OTB}'";
				}

				$PATH_BON_ENGAGEMENT = NULL;
				if(!empty($key->PATH_BORDEREAU_ENGAGEMENT))
				{
					$PATH_BON_ENGAGEMENT=$key->PATH_BORDEREAU_ENGAGEMENT;
				}
				elseif(!empty($key->PATH_BON_ENGAGEMENT))
				{
					$PATH_BON_ENGAGEMENT=$key->PATH_BON_ENGAGEMENT;
				}

				if(!empty($PATH_BON_ENGAGEMENT))
				{
					$str_tache_detail.=',PATH_BON_ENGAGEMENT';
					$value_tache_detail.=",'{$PATH_BON_ENGAGEMENT}'";
				}

				// $COMMENTAIRE_DET=$val->COMMENTAIRE?$val->COMMENTAIRE:NULL;
				if(!empty($val->COMMENTAIRE))
				{
					$COMMENTAIRE_DET=addslashes($val->COMMENTAIRE);
					$str_tache_detail.=',COMMENTAIRE';
					$value_tache_detail.=",'{$COMMENTAIRE_DET}'";
				}
				
				// $USER_AFFECTE_ID=$val->USER_AFFECTE_ID?$val->USER_AFFECTE_ID:NULL;
				if(!empty($val->USER_AFFECTE_ID))
				{
					$str_tache_detail.=',USER_AFFECTE_ID';
					$value_tache_detail.=",{$val->USER_AFFECTE_ID}";
				}

				// $DATE_INSERTION=$val->DATE_INSERTION?$val->DATE_INSERTION:NULL;
				if(!empty($val->DATE_INSERTION))
				{
					$str_tache_detail.=',DATE_INSERTION';
					$value_tache_detail.=",'{$val->DATE_INSERTION}'";
				}

				$table_det='v3_execution_budgetaire_tache_detail';
				$columns_det='EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE,MONTANT_ORDONNANCEMENT,MONTANT_ORDONNANCEMENT_DEVISE,MONTANT_CREANCE,MOTANT_FACTURE,MONTANT_PRELEVEMENT_FISCALES'.$str_tache_detail;

				$data_det="{$val->EXECUTION_BUDGETAIRE_DETAIL_ID},{$val->EXECUTION_BUDGETAIRE_ID},'{$MONTANT_LIQUIDATION}','{$MONTANT_LIQUIDATION_DEVISE}','{$MONTANT_ORDONNANCEMENT}','{$MONTANT_ORDONNANCEMENT_DEVISE}','{$MONTANT_CREANCE}','{$MOTANT_FACTURE}','{$MONTANT_PRELEVEMENT_FISCALES}'".$value_tache_detail;
				$this->save_all_table($table_det,$columns_det,$data_det);

				//save dans execution_budgetaire_titre_decaissement

				$str_titre_dec='';
				$value_titre_dec="";

				// $BANQUE_ID=$key->BANQUE_ID?$key->BANQUE_ID:NULL;
				if(!empty($key->BANQUE_ID))
				{
					$str_titre_dec.=',BANQUE_ID';
					$value_titre_dec.=",{$key->BANQUE_ID}";
				}

				$MONTANT_PAIEMENT=$val->MONTANT_PAIEMENT?$val->MONTANT_PAIEMENT:0;
				$MONTANT_PAIEMENT_DEVISE=$val->MONTANT_PAIEMENT_DEVISE?$val->MONTANT_PAIEMENT_DEVISE:0;
				// $DATE_PAIEMENT=$val->DATE_PAIEMENT?$val->DATE_PAIEMENT:NULL;
				if(!empty($val->DATE_PAIEMENT))
				{
					$str_titre_dec.=',DATE_PAIEMENT';
					$value_titre_dec.=",'{$val->DATE_PAIEMENT}'";
				}
				// $MOTIF_PAIEMENT=$val->MOTIF_PAIEMENT?$val->MOTIF_PAIEMENT:NULL;
				if(!empty($val->MOTIF_PAIEMENT))
				{
					$MOTIF_PAIEMENT=addslashes($val->MOTIF_PAIEMENT);
					$str_titre_dec.=',MOTIF_PAIEMENT';
					$value_titre_dec.=",'{$MOTIF_PAIEMENT}'";
				}

				$MONTANT_DECAISSEMENT=$val->MONTANT_DECAISSEMENT?$val->MONTANT_DECAISSEMENT:0;
				$MONTANT_DECAISSEMENT_DEVISE=$val->MONTANT_DECAISSEMENT_DEVISE?$val->MONTANT_DECAISSEMENT_DEVISE:0;
				// $NUMERO_TITRE_DECAISSEMNT=$val->NUMERO_TITRE_DECAISSEMNT?$val->NUMERO_TITRE_DECAISSEMNT:NULL;
				if(!empty($val->NUMERO_TITRE_DECAISSEMNT))
				{
					$str_titre_dec.=',TITRE_DECAISSEMENT';
					$value_titre_dec.=",'{$val->NUMERO_TITRE_DECAISSEMNT}'";
				}
				// $ETAPE_DOUBLE_COMMANDE_ID=$val->ETAPE_DOUBLE_COMMANDE_ID?$val->ETAPE_DOUBLE_COMMANDE_ID:NULL;
				if(!empty($val->ETAPE_DOUBLE_COMMANDE_ID))
				{
					$str_titre_dec.=',ETAPE_DOUBLE_COMMANDE_ID';
					$value_titre_dec.=",{$val->ETAPE_DOUBLE_COMMANDE_ID}";
				}
				// $DEVISE_TYPE_HISTO_DEC_ID=$val->DEVISE_TYPE_HISTO_DEC_ID?$val->DEVISE_TYPE_HISTO_DEC_ID:NULL;
				if(!empty($val->DEVISE_TYPE_HISTO_DEC_ID))
				{
					$str_titre_dec.=',DEVISE_TYPE_HISTO_DEC_ID';
					$value_titre_dec.=",{$val->DEVISE_TYPE_HISTO_DEC_ID}";
				}
				// $DATE_DECAISSEMENT=$val->DATE_DECAISSENMENT?$val->DATE_DECAISSENMENT:NULL;
				if(!empty($val->DATE_DECAISSENMENT))
				{
					$str_titre_dec.=',DATE_DECAISSEMENT';
					$value_titre_dec.=",'{$val->DATE_DECAISSENMENT}'";
				}
				// $PATH_TITRE_DECAISSEMENT=$val->PATH_TITRE_DECAISSEMENT?$val->PATH_TITRE_DECAISSEMENT:NULL;
				if(!empty($val->PATH_TITRE_DECAISSEMENT))
				{
					$str_titre_dec.=',PATH_TITRE_DECAISSEMENT';
					$value_titre_dec.=",'{$val->PATH_TITRE_DECAISSEMENT}'";
				}
				// $NOM_PERSONNE_RETRAIT=$val->NOM_PERSONNE_RETRAT?$val->NOM_PERSONNE_RETRAT:NULL;
				if(!empty($val->NOM_PERSONNE_RETRAT))
				{
					$NOM_PERSONNE_RETRAIT=addslashes($val->NOM_PERSONNE_RETRAT);
					$str_titre_dec.=',NOM_PERSONNE_RETRAIT';
					$value_titre_dec.=",'{$NOM_PERSONNE_RETRAIT}'";
				}
				// $DEVISE_TYPE_ID_RETRAIT=$val->DEVISE_TYPE_ID_RETRAIT?$val->DEVISE_TYPE_ID_RETRAIT:NULL;
				if(!empty($val->DEVISE_TYPE_ID_RETRAIT))
				{
					$str_titre_dec.=',DEVISE_TYPE_ID_RETRAIT';
					$value_titre_dec.=",{$val->DEVISE_TYPE_ID_RETRAIT}";
				}
				// $COMPTE_CREDIT=$key->COMPTE_CREDIT?$key->COMPTE_CREDIT:NULL;
				if(!empty($key->COMPTE_CREDIT))
				{
					$str_titre_dec.=',COMPTE_CREDIT';
					$value_titre_dec.=",'{$key->COMPTE_CREDIT}'";
				}
				// $DATE_ELABORATION_TD=$val->DATE_ELABORATION_TD?$val->DATE_ELABORATION_TD:NULL;
				if(!empty($val->DATE_ELABORATION_TD))
				{
					$str_titre_dec.=',DATE_ELABORATION_TD';
					$value_titre_dec.=",'{$val->DATE_ELABORATION_TD}'";
				}
				// $DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR=$val->DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR?$val->DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR:NULL;
				if(!empty($val->DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR))
				{
					$str_titre_dec.=',DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR';
					$value_titre_dec.=",'{$val->DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR}'";
				}
				// $DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE=$val->DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE?$val->DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE:NULL;
				if(!empty($val->DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE))
				{
					$str_titre_dec.=',DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE';
					$value_titre_dec.=",'{$val->DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE}'";
				}
				// $DATE_SIGNATURE_TD_MINISTRE=$val->DATE_SIGNATURE_TD_MINISTRE?$val->DATE_SIGNATURE_TD_MINISTRE:NULL;
				if(!empty($val->DATE_SIGNATURE_TD_MINISTRE))
				{
					$str_titre_dec.=',DATE_SIGNATURE_TD_MINISTRE';
					$value_titre_dec.=",'{$val->DATE_SIGNATURE_TD_MINISTRE}'";
				}
				// $DATE_VALIDE_TITRE=$val->DATE_VALIDE_TITRE?$val->DATE_VALIDE_TITRE:NULL;
				if(!empty($val->DATE_VALIDE_TITRE))
				{
					$str_titre_dec.=',DATE_VALIDE_TITRE';
					$value_titre_dec.=",'{$val->DATE_VALIDE_TITRE}'";
				}
				$table_titre='v3_execution_budgetaire_titre_decaissement';
				$columns_titre='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_PAIEMENT,MONTANT_PAIEMENT_DEVISE,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE'.$str_titre_dec;

				$data_titre="{$val->EXECUTION_BUDGETAIRE_DETAIL_ID},{$val->EXECUTION_BUDGETAIRE_ID},{$val->EXECUTION_BUDGETAIRE_DETAIL_ID},'{$MONTANT_PAIEMENT}','{$MONTANT_PAIEMENT_DEVISE}','{$MONTANT_DECAISSEMENT}','{$MONTANT_DECAISSEMENT_DEVISE}'".$value_titre_dec;
				$this->save_all_table($table_titre,$columns_titre,$data_titre);
 
				//save dans v3_execution_budgetaire_execution_tache_detail
				if($MONTANT_LIQUIDATION > 0){
					$table_exec_tache_det='v3_execution_budgetaire_execution_tache_detail';
					$columns_exec_tache_det='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, MONTANT_LIQUIDATION, MONTANT_LIQUIDATION_DEVISE, MONTANT_ORDONNANCEMENT, MONTANT_ORDONNANCEMENT_DEVISE, MONTANT_PAIEMENT, MONTANT_PAIEMENT_DEVISE, MONTANT_DECAISSEMENT, MONTANT_DECAISSEMENT_DEVISE';
					$data_exec_tache_det = "{$val->EXECUTION_BUDGETAIRE_DETAIL_ID}, {$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID}, '{$MONTANT_LIQUIDATION}', '{$MONTANT_LIQUIDATION_DEVISE}','{$MONTANT_ORDONNANCEMENT}','{$MONTANT_ORDONNANCEMENT_DEVISE}', '{$MONTANT_PAIEMENT}','{$MONTANT_PAIEMENT_DEVISE}','{$MONTANT_DECAISSEMENT}','{$MONTANT_DECAISSEMENT_DEVISE}'";
					$this->save_all_table($table_exec_tache_det,$columns_exec_tache_det,$data_exec_tache_det);
				}
			}
		}
		// $db->transComplete();
		return 'Migration effectuée avec succès';
	}
}