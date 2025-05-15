<?php
/** Emery & Claude
*Titre: Script permettant de faire la mise à jour de la table des taches (ptba_tache) selon la révision budgétaire tout en gardant l'historique des taches avant révision 
*Numero de telephone: +257 69 641 375
*Email pro: claude@mediabox.bi 
*Date: 21 janv 2025
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Script_Faire_Historique_Ptba extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	 public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
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

	 /* update table */
  function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  //Faire l'historique

  public function inserer_historique(){

    	 //les anciennes taches
  	$ANNEE_ACTUEL = $this->get_annee_budgetaire();
  	$ptba="SELECT
  	`PTBA_TACHE_ID`,
  	`PROGRAMME_PRIORITAIRE_ID`,
  	`PAP_ACTIVITE_ID`,
  	`COSTAB_ACTIVITE_ID`,
  	`PND_INDICATEUR_ID`,
  	`DESC_TACHE`,
  	`RESULTAT_ATTENDUS_TACHE`,
  	`UNITE`,
  	`Q_TOTAL`,
  	`QT1`,
  	`QT2`,
  	`QT3`,
  	`QT4`,
  	`COUT_UNITAIRE`,
  	`BUDGET_T1`,
  	`BUDGET_UTILISE_T1`,
  	`BUDGET_RESTANT_T1`,
  	`BUDGET_T2`,
  	`BUDGET_UTILISE_T2`,
  	`BUDGET_RESTANT_T2`,
  	`BUDGET_T3`,
  	`BUDGET_UTILISE_T3`,
  	`BUDGET_RESTANT_T3`,
  	`BUDGET_T4`,
  	`BUDGET_UTILISE_T4`,
  	`BUDGET_RESTANT_T4`,
  	`BUDGET_ANNUEL`,
  	`STRUTURE_RESPONSABLE_TACHE_ID`,
  	`ANNEE_BUDGETAIRE_ID`
  	
  	FROM
  	`ptba_tache`
  	WHERE ANNEE_BUDGETAIRE_ID = ".$ANNEE_ACTUEL;
  	
  	$ptbas=$this->ModelPs->getRequete("CALL `getTable`('".$ptba."')");
		//Insertion dans la table historique "ptba_tache_revision_historique"
  	foreach($ptbas as $value){

  		$PAP_ACTIVITE_ID=(!empty($value->PAP_ACTIVITE_ID)) ? $value->PAP_ACTIVITE_ID : ''; 
  		$COSTAB_ACTIVITE_ID=(!empty($value->COSTAB_ACTIVITE_ID)) ? $value->COSTAB_ACTIVITE_ID : ''; 
  		$PROGRAMME_PRIORITAIRE_ID=(!empty($value->PROGRAMME_PRIORITAIRE_ID)) ? $value->PROGRAMME_PRIORITAIRE_ID : '';
  		$STRUTURE_RESPONSABLE_TACHE_ID=(!empty($value->STRUTURE_RESPONSABLE_TACHE_ID)) ? $value->STRUTURE_RESPONSABLE_TACHE_ID : '';
  		$ANNEE_BUDGETAIRE_ID=(!empty($value->ANNEE_BUDGETAIRE_ID)) ? $value->ANNEE_BUDGETAIRE_ID : '';
  		$DESC_TACHE=str_replace("'","\'",$value->DESC_TACHE);
  		$RESULTAT_ATTENDUS_TACHE=str_replace("'","\'",$value->RESULTAT_ATTENDUS_TACHE);
  		$RESULTAT_ATTENDUS_TACHE=str_replace("'","\'",$RESULTAT_ATTENDUS_TACHE);
  		
  		$RESULTAT_ATTENDUS_TACHE=(!empty($RESULTAT_ATTENDUS_TACHE)) ? $value->RESULTAT_ATTENDUS_TACHE : '';
  		$UNITE=str_replace("'","\'",$value->UNITE);
  		$UNITE=(!empty($UNITE)) ? $UNITE : '';
  		$COUT_UNITAIRE=(!empty($value->COUT_UNITAIRE)) ? $value->COUT_UNITAIRE : '';


  		$table_histo_ptba="ptba_tache_revision_historique";
  		$columsinsert_ptba_histo="PTBA_TACHE_ID,PAP_ACTIVITE_ID,COSTAB_ACTIVITE_ID,PROGRAMME_PRIORITAIRE_ID,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE, Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_RESTANT_T1,BUDGET_UTILISE_T1,BUDGET_T2, BUDGET_RESTANT_T2,BUDGET_UTILISE_T2,BUDGET_T3,BUDGET_RESTANT_T3,BUDGET_UTILISE_T3,BUDGET_T4,BUDGET_RESTANT_T4,BUDGET_UTILISE_T4,BUDGET_ANNUEL,STRUTURE_RESPONSABLE_TACHE_ID,ANNEE_BUDGETAIRE_ID";


  		$data_ptba_histo="{$value->PTBA_TACHE_ID},'{$PAP_ACTIVITE_ID}','{$COSTAB_ACTIVITE_ID}','{$PROGRAMME_PRIORITAIRE_ID}','{$DESC_TACHE}','{$RESULTAT_ATTENDUS_TACHE}','{$UNITE}','{$value->Q_TOTAL}','{$value->QT1}','{$value->QT2}','{$value->QT3}','{$value->QT4}','{$COUT_UNITAIRE}','{$value->BUDGET_T1}','{$value->BUDGET_RESTANT_T1}','{$value->BUDGET_UTILISE_T1}','{$value->BUDGET_T2}', '{$value->BUDGET_RESTANT_T2}','{$value->BUDGET_UTILISE_T2}','{$value->BUDGET_T3}','{$value->BUDGET_RESTANT_T3}','{$value->BUDGET_UTILISE_T3}','{$value->BUDGET_T4}','{$value->BUDGET_RESTANT_T4}','{$value->BUDGET_UTILISE_T4}','{$value->BUDGET_ANNUEL}','{$STRUTURE_RESPONSABLE_TACHE_ID}','{$ANNEE_BUDGETAIRE_ID}'";
  	

  		 $this->save_all_table($table_histo_ptba,$columsinsert_ptba_histo,$data_ptba_histo);

  		$tache_execute="SELECT PTBA_TACHE_ID FROM `execution_budgetaire_execution_tache` WHERE PTBA_TACHE_ID=".$value->PTBA_TACHE_ID;
  		$tache_executes=$this->ModelPs->getRequeteOne("CALL `getTable`('".$tache_execute."')");

  			$columsupdate1="Q_TOTAL='0',QT1='0', QT2='0',QT3='0',QT4='0',COUT_UNITAIRE='0',BUDGET_T1='0',BUDGET_UTILISE_T1='0',BUDGET_RESTANT_T1='0',BUDGET_T2='0',BUDGET_UTILISE_T2='0',BUDGET_RESTANT_T2='0',BUDGET_T3='0',BUDGET_UTILISE_T3='0',BUDGET_RESTANT_T3='0',BUDGET_T4='0',BUDGET_UTILISE_T4='0',BUDGET_RESTANT_T4='0',BUDGET_ANNUEL='0',COUT_UNITAIRE='0'";

  		if(!empty($tache_executes)){
  			
  			$columsupdate1="Q_TOTAL='0',	QT1='0', QT2='0',QT3='0', QT4='0', COUT_UNITAIRE='0', BUDGET_T1='0',BUDGET_UTILISE_T1='0',BUDGET_RESTANT_T1='0',BUDGET_T2='0',BUDGET_UTILISE_T2='0',BUDGET_RESTANT_T2='0',BUDGET_T3='0',BUDGET_UTILISE_T3='0',BUDGET_RESTANT_T3='0',BUDGET_T4='0',BUDGET_UTILISE_T4='0',BUDGET_RESTANT_T4='0',BUDGET_ANNUEL='0',COUT_UNITAIRE='0', PTBA_TACHE_STATUT_APRES_REVISION_ID=3";
  		}

      $table='ptba_tache';
      $where="PTBA_TACHE_ID=".$value->PTBA_TACHE_ID;
  		$this->update_all_table($table,$columsupdate1,$where);


  	}
 
   return 'Insertion effectuée avec succès';
  }

   // Début  de la fonction traitement
	public function traitement()
	{
	   
	// LISTE des taches revise
	$ANNEE_ACTUEL = $this->get_annee_budgetaire();
	$ptba_revise="SELECT
    `PTBA_TACHE_REVISE_ID`,
    `PTBA_TACHE_ID`,
    `INSTITUTION_ID`,
    `SOUS_TUTEL_ID`,
    `ID_PILIER`,
    `OBJECTIF_VISION_ID`,
    `AXE_PND_ID`,
    `PROGRAMME_ID`,
    `ACTION_ID`,
    `PROGRAMME_PRIORITAIRE_ID`,
    `CODE_NOMENCLATURE_BUDGETAIRE_ID`,
    `CODE_NOMENCLATURE_BUDGETAIRE`,
    `ARTICLE_ID`,
    `SOUS_LITTERA_ID`,
    `DIVISION_ID`,
    `GROUPE_ID`,
    `CLASSE_ID`,
    `PAP_ACTIVITE_ID`,
    `COSTAB_ACTIVITE_ID`,
    `PND_INDICATEUR_ID`,
    `CODES_PROGRAMMATIQUE`, 
    `DESC_TACHE`,
    `RESULTAT_ATTENDUS_TACHE`,
    `UNITE`,
    `Q_TOTAL`,
    `QT1`,
    `QT2`,
    `QT3`,
    `QT4`,
    `BUDGET_T1`,
    `BUDGET_T2`,
    `BUDGET_T3`,
    `BUDGET_T4`,
    `BUDGET_ANNUEL`,
    `STRUTURE_RESPONSABLE_TACHE_ID`,
    `GRANDE_MASSE_ID`,
    `ANNEE_BUDGETAIRE_ID`,
    `COUT_UNITAIRE`,
    `IS_NOUVEAU`
    FROM
    `ptba_tache_revise`
     WHERE ANNEE_BUDGETAIRE_ID=".$ANNEE_ACTUEL;

     $ptba_tache_revisee=$this->ModelPs->getRequete("CALL `getTable`('".$ptba_revise."')");

     foreach($ptba_tache_revisee as $key )
     {
   
     	$PROGRAMME_PRIORITAIRE_ID =!empty($key->PROGRAMME_PRIORITAIRE_ID) ? $key->PROGRAMME_PRIORITAIRE_ID:0;
     	$INSTITUTION_ID =!empty($key->INSTITUTION_ID) ? $key->INSTITUTION_ID:0;
     	$SOUS_TUTEL_ID =!empty($key->SOUS_TUTEL_ID) ? $key->SOUS_TUTEL_ID:0;
     	$PROGRAMME_ID =!empty($key->PROGRAMME_ID) ? $key->PROGRAMME_ID:0;
     	$ID_PILIER =!empty($key->ID_PILIER) ? $key->ID_PILIER:0;
     	$GRANDE_MASSE_ID =!empty($key->GRANDE_MASSE_ID) ? $key->GRANDE_MASSE_ID:0;
     	$OBJECTIF_VISION_ID =!empty($key->OBJECTIF_VISION_ID) ? $key->OBJECTIF_VISION_ID:0;
     	$AXE_PND_ID =!empty($key->AXE_PND_ID) ? $key->AXE_PND_ID:0;
     	$CODE_NOMENCLATURE_BUDGETAIRE_ID =!empty($key->CODE_NOMENCLATURE_BUDGETAIRE_ID) ? $key->CODE_NOMENCLATURE_BUDGETAIRE_ID:0;
     	$CODE_NOMENCLATURE_BUDGETAIRE =!empty($key->CODE_NOMENCLATURE_BUDGETAIRE) ? $key->CODE_NOMENCLATURE_BUDGETAIRE:0;
     	$ARTICLE_ID = $ARTICLE_ID =!empty($key->ARTICLE_ID) ? $key->ARTICLE_ID:0;
     	$ACTION_ID =!empty($key->ACTION_ID) ? $key->ACTION_ID:0;
     	$PAP_ACTIVITE_ID = !empty($key->PAP_ACTIVITE_ID) ? $key->PAP_ACTIVITE_ID:0;
     	$COSTAB_ACTIVITE_ID = !empty($key->COSTAB_ACTIVITE_ID) ? $key->COSTAB_ACTIVITE_ID:0;
     	$PND_INDICATEUR_ID = !empty($key->PND_INDICATEUR_ID) ? $key->PND_INDICATEUR_ID:0;
     	$CLASSE_ID = !empty($key->CLASSE_ID) ? $key->CLASSE_ID:0;
     	$GROUPE_ID = !empty($key->GROUPE_ID) ? $key->GROUPE_ID:0;
     	$DIVISION_ID = !empty($key->DIVISION_ID) ? $key->DIVISION_ID:0;
     	$SOUS_LITTERA_ID = !empty($key->SOUS_LITTERA_ID) ? $key->SOUS_LITTERA_ID:0;
     	$PAP_ACTIVITE_ID = !empty($key->PAP_ACTIVITE_ID) ? $key->PAP_ACTIVITE_ID:0;
     	$COSTAB_ACTIVITE_ID = !empty($key->COSTAB_ACTIVITE_ID) ? $key->COSTAB_ACTIVITE_ID:0;
     	$CODES_PROGRAMMATIQUE = !empty($key->CODES_PROGRAMMATIQUE) ? $key->CODES_PROGRAMMATIQUE:'';
     	$RESULTAT_ATTENDUS_TACHE=str_replace("'","\'",$key->RESULTAT_ATTENDUS_TACHE);
     	$UNITE=str_replace("'","\'",$key->UNITE);
     	$UNITE = !empty($UNITE) ? $UNITE:'';
     	$DESC_TACHE=str_replace("'","\'",$key->DESC_TACHE);
     	$STRUTURE_RESPONSABLE_TACHE_ID =!empty($key->STRUTURE_RESPONSABLE_TACHE_ID) ? $key->STRUTURE_RESPONSABLE_TACHE_ID:0;

     $COUT_UNITAIRE = !empty($key->COUT_UNITAIRE) ? $key->COUT_UNITAIRE:'';
     	
     	$ANNEE_BUDGETAIRE_ID =!empty($key->ANNEE_BUDGETAIRE_ID) ? $key->ANNEE_BUDGETAIRE_ID:0;

     	if($key->PTBA_TACHE_ID>0){
     	// Si la tache correspondante est trouvée dans le ptba, on fait la mise à jour
     		$where="PTBA_TACHE_ID=".$key->PTBA_TACHE_ID;
     		$columsupdate="PROGRAMME_PRIORITAIRE_ID=".$PROGRAMME_PRIORITAIRE_ID.",PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID.",COSTAB_ACTIVITE_ID=".$COSTAB_ACTIVITE_ID.",PND_INDICATEUR_ID=".$PND_INDICATEUR_ID.",DESC_TACHE='".$DESC_TACHE."',RESULTAT_ATTENDUS_TACHE='".$RESULTAT_ATTENDUS_TACHE."',UNITE='".$UNITE."', Q_TOTAL='".$key->Q_TOTAL."',QT1='".$key->QT1."',QT2='".$key->QT2."',QT3='".$key->QT3."',QT4='".$key->QT4."',COUT_UNITAIRE='".$COUT_UNITAIRE."', BUDGET_T1='".$key->BUDGET_T1."',BUDGET_UTILISE_T1='".$key->BUDGET_T1."',BUDGET_RESTANT_T1='0',BUDGET_T2='".$key->BUDGET_T2."',BUDGET_UTILISE_T2='".$key->BUDGET_T2."',BUDGET_RESTANT_T2=0,BUDGET_T3='".$key->BUDGET_T3."',BUDGET_UTILISE_T3=0,BUDGET_RESTANT_T3='".$key->BUDGET_T3."',BUDGET_T4='".$key->BUDGET_T4."',BUDGET_UTILISE_T4='0',BUDGET_RESTANT_T4='".$key->BUDGET_T4."',BUDGET_ANNUEL='".$key->BUDGET_ANNUEL."',STRUTURE_RESPONSABLE_TACHE_ID=".$STRUTURE_RESPONSABLE_TACHE_ID.",ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.",PTBA_TACHE_STATUT_APRES_REVISION_ID=1";

     		$table='ptba_tache';
     		$this->update_all_table($table,$columsupdate,$where);
     	}else{

     	// Si c 'est pas trouvée dans la table des ptba avant révision on l'ajoute dans la table'
     		$table="ptba_tache";
     		$columsinsert_ptba="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID,PROGRAMME_PRIORITAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID,PAP_ACTIVITE_ID,COSTAB_ACTIVITE_ID,PND_INDICATEUR_ID,CODES_PROGRAMMATIQUE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_UTILISE_T1,BUDGET_RESTANT_T1,BUDGET_T2,BUDGET_UTILISE_T2,BUDGET_RESTANT_T2,BUDGET_T3,BUDGET_UTILISE_T3,BUDGET_RESTANT_T3,BUDGET_T4,BUDGET_UTILISE_T4,BUDGET_RESTANT_T4,BUDGET_ANNUEL,STRUTURE_RESPONSABLE_TACHE_ID,GRANDE_MASSE_ID,ANNEE_BUDGETAIRE_ID,PTBA_TACHE_STATUT_APRES_REVISION_ID";

     		$data_ptba_insert="{$INSTITUTION_ID},'{$SOUS_TUTEL_ID}','$ID_PILIER','{$OBJECTIF_VISION_ID}',{$AXE_PND_ID},{$PROGRAMME_ID},{$ACTION_ID},{$PROGRAMME_PRIORITAIRE_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID},'{$CODE_NOMENCLATURE_BUDGETAIRE}',{$ARTICLE_ID},{$SOUS_LITTERA_ID},{$DIVISION_ID},{$GROUPE_ID},{$CLASSE_ID},{$PAP_ACTIVITE_ID},{$COSTAB_ACTIVITE_ID},{$PND_INDICATEUR_ID},'{$CODES_PROGRAMMATIQUE}','{$DESC_TACHE}','{$RESULTAT_ATTENDUS_TACHE}','{$UNITE}','{$key->Q_TOTAL}','{$key->QT1}','{$key->QT2}','{$key->QT3}','{$key->QT4}','{$key->COUT_UNITAIRE}','{$key->BUDGET_T1}','{$key->BUDGET_T1}','0','{$key->BUDGET_T2}','{$key->BUDGET_T2}','0','{$key->BUDGET_T3}','0','{$key->BUDGET_T3}','{$key->BUDGET_T4}','0','{$key->BUDGET_T4}','{$key->BUDGET_ANNUEL}',{$STRUTURE_RESPONSABLE_TACHE_ID},{$GRANDE_MASSE_ID},{$ANNEE_BUDGETAIRE_ID},2";
     		
     		$this->save_all_table($table,$columsinsert_ptba,$data_ptba_insert);

     	}
     }

		return 'Mise à jour effectuée avec succès';

	} // Fin  de la fonction Traitement
	
}