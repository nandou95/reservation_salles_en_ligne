<?php
/**
* HABIMANA NANDOU
* Titre: Gestion de raccrochage deja fait
* WhatsApp: +257 71 48 39 05
* Email pro: nandou@mediabox.bi
* Date: 09 octobre 2023
**/

namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Gestion_raccrochage_deja extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	function historique_raccrochage($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$OBSERVATION)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$OBSERVATION=str_replace("'"," ",$OBSERVATION);
		$OBSERVATION=str_replace('"','',$OBSERVATION);
		$table="historique_raccrochage";
		$columnselect="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,USER_ID,MOUVEMENT_DEPENSE_ID,TYPE_RACCROCHAGE_ID,OBSERVATION";
		$datacolumsinsert=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.",".$USER_ID.",".$MOUVEMENT_DEPENSE_ID.",".$TYPE_RACCROCHAGE_ID.",'".$OBSERVATION."'";
		$bindparms=[$table,$columnselect,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReq,$bindparms);
	}

	function historique_detail($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$NUMERO_DOCUMENT,$PATH_DOCUMENT,$DATE_DOCUMENT,$TYPE_DOCUMENT_ID)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$table="raccrochage_detail";
		$columnselect="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,NUMERO_DOCUMENT,PATH_DOCUMENT,DATE_DOCUMENT,TYPE_DOCUMENT_ID";
		$datacolumsinsert=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.",'".$NUMERO_DOCUMENT."','".$PATH_DOCUMENT."','".$DATE_DOCUMENT."',".$TYPE_DOCUMENT_ID;
		$bindparms=[$table,$columnselect,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReq,$bindparms);
	}

	function change_raccrochage()
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		//permet de modifier les raccrochage deja fait au nouveau maniere de gerer les raccrochage
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparms=$this->getBindParms('EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,USER_ID,USER_ID_QUANTITE,MOUVEMENT_DEPENSE_ID,COMMENTAIRE,DOC_RACCROCHE,NUMERO_BON_ENGAGEMENT,DATE_BON_ENGAGEMENT,NUMERO_TITRE_DECAISSEMNT,DATE_TITRE_DECAISSEMENT,execution_budgetaire_raccrochage_activite.MONTANT_RACCROCHE_DECAISSEMENT,ptba.PTBA_ID,ptba.MONTANT_RESTANT_T1','execution_budgetaire_raccrochage_activite JOIN ptba ON ptba.PTBA_ID=execution_budgetaire_raccrochage_activite.PTBA_ID','execution_budgetaire_raccrochage_activite.TRAITE=0','EXECUTION_BUDGETAIRE_RACCROCHAGE_ID ASC');
		$resultatss= $this->ModelPs->getRequete($callpsreq,$bindparms);
		if(!empty($resultatss))
		{
			foreach ($resultatss as $resultats)
			{
				$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=$resultats->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID;
				$USER_ID=$resultats->USER_ID;
				$USER_ID_QUANTITE=$resultats->USER_ID_QUANTITE;
				$MOUVEMENT_DEPENSE_ID=$resultats->MOUVEMENT_DEPENSE_ID;
				$OBSERVATION=$resultats->COMMENTAIRE;

				// Debut getion detail document
				$TYPE_DOCUMENT_ID=1;
				$PATH_DOCUMENT=$resultats->DOC_RACCROCHE;
				$NUMERO_DOCUMENT=$resultats->NUMERO_BON_ENGAGEMENT;
				$DATE_DOCUMENT=$resultats->DATE_BON_ENGAGEMENT;
				if(empty($NUMERO_DOCUMENT))
				{
					$TYPE_DOCUMENT_ID=2;
					$NUMERO_DOCUMENT=$resultats->NUMERO_TITRE_DECAISSEMNT;
					$DATE_DOCUMENT=$resultats->DATE_TITRE_DECAISSEMENT;
				}

				$this->historique_detail($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$NUMERO_DOCUMENT,$PATH_DOCUMENT,$DATE_DOCUMENT,$TYPE_DOCUMENT_ID);
				// Fin getion detail document

				// Debut gestion historique
				if(!empty($USER_ID))
				{
					$TYPE_RACCROCHAGE_ID=1;
					$this->historique_raccrochage($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$OBSERVATION);
				}

				if(!empty($USER_ID_QUANTITE))
				{
					$TYPE_RACCROCHAGE_ID=2;
					$this->historique_raccrochage($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$USER_ID_QUANTITE,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$OBSERVATION);
				}
				// Fin gestion historique
				// Debut gerer le decaissement
				if($MOUVEMENT_DEPENSE_ID==5)
				{
					$str_montant_restant=!empty(trim($resultats->MONTANT_RESTANT_T1))?trim($resultats->MONTANT_RESTANT_T1):0;
					$str_montant_decaissement=!empty(trim($resultats->MONTANT_RACCROCHE_DECAISSEMENT))?trim($resultats->MONTANT_RACCROCHE_DECAISSEMENT):0;
					$montant_restant=floatval($str_montant_restant);
					$montant_decaissement=floatval($str_montant_decaissement);
					$montant_rest=$montant_restant-$montant_decaissement;
					$tabel_ptba="ptba";
					$datatomodifie_ptba='MONTANT_RESTANT_T1="'.$montant_rest.'"';
					$where='PTBA_ID="'.$resultats->PTBA_ID.'"';
					$bindparamsptba =[$tabel_ptba,$datatomodifie_ptba,$where];
					$updateRequeteptba = "CALL `updateData`(?,?,?);";
					$resultatptba=$this->ModelPs->createUpdateDelete($updateRequeteptba,$bindparamsptba);
				}

				// Fin gerer le decaissement
				$table="execution_budgetaire_raccrochage_activite";
				$datatomodifie="TRAITE=1";
				$conditions='EXECUTION_BUDGETAIRE_RACCROCHAGE_ID="'.$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.'"';
				$bindparams =[$table,$datatomodifie,$conditions];
				$updateRequete = "CALL `updateData`(?,?,?);";
				$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
			}
		}
	}

	function active_raccrochage()
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparms=$this->getBindParms('DISTINCT EXECUTION_BUDGETAIRE_ID','execution_budgetaire_raccrochage_activite','1','EXECUTION_BUDGETAIRE_ID ASC');
		$resultatss= $this->ModelPs->getRequete($callpsreq,$bindparms);
		if(!empty($resultatss))
		{
			foreach ($resultatss as $resultats)
			{
				$table="execution_budgetaire";
				$datatomodifie="IS_RACCROCHE=1";
				$conditions='EXECUTION_BUDGETAIRE_ID="'.$resultats->EXECUTION_BUDGETAIRE_ID.'"';
				$bindparams =[$table,$datatomodifie,$conditions];
				$updateRequete = "CALL `updateData`(?,?,?);";
				$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
			}
		}
	}
}
?>