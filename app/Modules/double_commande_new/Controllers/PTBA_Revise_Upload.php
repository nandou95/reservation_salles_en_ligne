<?php
/*
*Joa-Kevin IRADUKUNDA
*Titre: Controller pour identifier les doublants dans les tables
*Numero de telephone: (+257) 62 636 535
*WhatsApp: (+27) 61 436 6546
*Email: joa-kevin.iradukunda@mediabox.bi
*Date: 25 novembre,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class PTBA_Revise_Upload extends BaseController
{
	protected $session;
  	protected $ModelPs;

  	public function __construct()
	{
	    $this->ModelPs = new ModelPs();
	    $this->session = \Config\Services::session();
	    $this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	//appel de la view pour l'interface d' upload
	function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		return view('App\Modules\double_commande_new\Views\PTBA_Revise_Upload_View',$data);
	}

	function nettoyage_ptba()
	{
		$req = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache";
		$taches = $this->ModelPs->getRequete('CALL `getTable`("' . $req . '")');

		$i = 0;
		foreach($taches as $tache)
		{
			$DESC_TACHE = $tache->DESC_TACHE;
			do{
				$count = count(explode('  ', $DESC_TACHE));
				$DESC_TACHE = str_replace('  ',' ',$DESC_TACHE);
				$i++;
			}
			while($count > 1);

			$table = "ptba_tache";
			$where="PTBA_TACHE_ID=".$tache->PTBA_TACHE_ID;
			$data="DESC_TACHE='".$DESC_TACHE."'";
			$this->update_all_table($table,$data,$where);

		}

		print('No Tache: ');
		print(($i));
	}

	function nettoyage_activite_pap()
	{
		$req = "SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites";
		$donnees = $this->ModelPs->getRequete('CALL `getTable`("' . $req . '")');

		$i = 0;
		foreach($donnees as $donnee)
		{
			$DESC_PAP_ACTIVITE = $donnee->DESC_PAP_ACTIVITE;
			do{
				$count = count(explode('  ', $DESC_PAP_ACTIVITE));
				$DESC_PAP_ACTIVITE = str_replace('  ',' ',$DESC_PAP_ACTIVITE);
				$i++;
			}
			while($count > 1);

			$table = "pap_activites";
			$where="PAP_ACTIVITE_ID=".$donnee->PAP_ACTIVITE_ID;
			$data="DESC_PAP_ACTIVITE='".$DESC_PAP_ACTIVITE."'";
			$this->update_all_table($table,$data,$where);

		}

		print('Nombre Activites: ');
		print(($i));
	}

	function nettoyage_activite_costab()
	{
		$req = "SELECT COSTAB_ACTIVITE_ID,DESC_COSTAB_ACTIVITE FROM costab_activites";
		$donnees = $this->ModelPs->getRequete('CALL `getTable`("' . $req . '")');

		$i = 0;
		foreach($donnees as $donnee)
		{
			$DESC_COSTAB_ACTIVITE = $donnee->DESC_COSTAB_ACTIVITE;
			do{
				$count = count(explode('  ', $DESC_COSTAB_ACTIVITE));
				$DESC_COSTAB_ACTIVITE = str_replace('  ',' ',$DESC_COSTAB_ACTIVITE);
				$i++;
			}
			while($count > 1);

			$table = "costab_activites";
			$where="COSTAB_ACTIVITE_ID=".$donnee->COSTAB_ACTIVITE_ID;
			$data="DESC_COSTAB_ACTIVITE='".$DESC_COSTAB_ACTIVITE."'";
			$this->update_all_table($table,$data,$where);

		}

		print('Nombre Activites: ');
		print(($i));
	}

	function nettoyage_pnd_indicateur()
	{
		$req = "SELECT INDICATEUR_PND_ID,DESC_INDICATEUR_PND FROM pnd_indicateur";
		$donnees = $this->ModelPs->getRequete('CALL `getTable`("' . $req . '")');

		$i = 0;
		foreach($donnees as $donnee)
		{
			$DESC_INDICATEUR_PND = $donnee->DESC_INDICATEUR_PND;
			do{
				$count = count(explode('  ', $DESC_INDICATEUR_PND));
				$DESC_INDICATEUR_PND = str_replace('  ',' ',$DESC_INDICATEUR_PND);
				$i++;
			}
			while($count > 1);

			$table = "pnd_indicateur";
			$where="INDICATEUR_PND_ID=".$donnee->INDICATEUR_PND_ID;
			$data="DESC_INDICATEUR_PND='".$DESC_INDICATEUR_PND."'";
			$this->update_all_table($table,$data,$where);

		}

		print('Nombre Pnd Indicateur: ');
		print(($i));
	}

	function save_upload()
	{
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$FICHIER_PTBA=$_FILES["FICHIER_PTBA"]["name"];

		$extension=pathinfo($FICHIER_PTBA,PATHINFO_EXTENSION);

		if($extension=='csv')
		{
			$reader=new \PhpOffice\PhpSpreadsheet\Reader\Csv();
		}
		else if($extension=='xls')
		{
			$reader=new \PhpOffice\PhpSpreadsheet\Reader\Xls();
		}
		else
		{
			$reader=new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		}
		
		$spreadsheet=$reader->load($_FILES["FICHIER_PTBA"]["tmp_name"]);
		$sheetdata=$spreadsheet->getActiveSheet()->toArray();
		$sheetcount=count($sheetdata);

		if($sheetcount>1)
		{
			for($i=1; $i < $sheetcount; $i++)
			{
				$CODE_MINISTERE = trim($spreadsheet->getActiveSheet()->getCell('A' . ($i + 1))->getValue());
				$INTITULE_MINISTERE = trim($spreadsheet->getActiveSheet()->getCell('B' . ($i + 1))->getValue());
				$PILIER = trim($spreadsheet->getActiveSheet()->getCell('C' . ($i + 1))->getValue());
				$OBJECTIF_DE_LA_VISION = trim($spreadsheet->getActiveSheet()->getCell('D' . ($i + 1))->getValue());
				$AXES_DU_PND_REVISE = trim($spreadsheet->getActiveSheet()->getCell('E' . ($i + 1))->getValue());
				$CODE_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('F' . ($i + 1))->getValue());
				$INTITULE_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('G' . ($i + 1))->getValue());
				$OBJECTIF_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('H' . ($i + 1))->getValue());
				$CODE_ACTION = trim($spreadsheet->getActiveSheet()->getCell('I' . ($i + 1))->getValue());
				$LIBELLE_ACTION = trim($spreadsheet->getActiveSheet()->getCell('J' . ($i + 1))->getValue());
				$OBJECTIF_ACTION = trim($spreadsheet->getActiveSheet()->getCell('K' . ($i + 1))->getValue());
				$PROGRAMME_PRIORITAIRE = trim($spreadsheet->getActiveSheet()->getCell('L' . ($i + 1))->getValue());
				$CODE_NOMENCLATURE_BUDGETAIRE = trim($spreadsheet->getActiveSheet()->getCell('M' . ($i + 1))->getValue());
				$ARTICLE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('N' . ($i + 1))->getValue());
				$INTITULE_ARTICLE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('O' . ($i + 1))->getValue());
				$NATURE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('P' . ($i + 1))->getValue());
				$INTITULE_NATURE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('Q' . ($i + 1))->getValue());
				$DIVISION_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('R' . ($i + 1))->getValue());
				$INTITULE_DIVISION_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('S' . ($i + 1))->getValue());
				$GROUPE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('T' . ($i + 1))->getValue());
				$INTITULE_GROUP_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('U' . ($i + 1))->getValue());
				$CLASSE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('V' . ($i + 1))->getValue());
				$INTITULE_CLASSE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('W' . ($i + 1))->getValue());
				$ACTIVITES_PAP = trim($spreadsheet->getActiveSheet()->getCell('X' . ($i + 1))->getValue());
				$RESULTATS_ATTENDUS_PAP = trim($spreadsheet->getActiveSheet()->getCell('Y' . ($i + 1))->getValue());
				$ACTIVITES_COSTAB = trim($spreadsheet->getActiveSheet()->getCell('Z' . ($i + 1))->getValue());
				$INDICATEURS_PND = trim($spreadsheet->getActiveSheet()->getCell('AA' . ($i + 1))->getValue());
				$CODES_PROGRAMMATIQUE = trim($spreadsheet->getActiveSheet()->getCell('AB' . ($i + 1))->getValue());
				$TACHES = trim($spreadsheet->getActiveSheet()->getCell('AC' . ($i + 1))->getValue());
				$TACHES = str_replace("'","’", $TACHES);
				$TACHES = str_replace('\n',' ', $TACHES);
				$TACHES = str_replace('"','', $TACHES);
				do{
					$count = count(explode('  ', $TACHES));
					$TACHES = str_replace('  ',' ',$TACHES);
				}
				while($count > 1);

				$RESULTATS_ATTENDUS_TACHES = trim($spreadsheet->getActiveSheet()->getCell('AD' . ($i + 1))->getValue());
				$RESULTATS_ATTENDUS_TACHES = str_replace("'","’", $RESULTATS_ATTENDUS_TACHES);
				$RESULTATS_ATTENDUS_TACHES = str_replace('\n',' ', $RESULTATS_ATTENDUS_TACHES);
				$RESULTATS_ATTENDUS_TACHES = str_replace('"','', $RESULTATS_ATTENDUS_TACHES);
				$UNITE = trim($spreadsheet->getActiveSheet()->getCell('AE' . ($i + 1))->getValue());
				$UNITE = str_replace("'","’", $UNITE);
				$UNITE = str_replace('\n',' ', $UNITE);
				$UNITE = str_replace('"','', $UNITE);
				$TOTAL_QUANTITE = trim($spreadsheet->getActiveSheet()->getCell('AF' . ($i + 1))->getValue());
				if($TOTAL_QUANTITE=='-')
				{
					$TOTAL_QUANTITE=str_replace('-', 0, $TOTAL_QUANTITE);
				}
				
				$TOTAL_QUANTITE=str_replace('', 0, $TOTAL_QUANTITE);
				$TOTAL_QUANTITE=str_replace('-0', 0, $TOTAL_QUANTITE);
				$QUANTITE_T1 = trim($spreadsheet->getActiveSheet()->getCell('AG' . ($i + 1))->getValue());
				if($QUANTITE_T1=='-')
				{
					$QUANTITE_T1=str_replace('-', 0, $QUANTITE_T1);
				}
				
				$QUANTITE_T1=str_replace('', 0, $QUANTITE_T1);
				$QUANTITE_T1=str_replace('-0', 0, $QUANTITE_T1);
				$QUANTITE_T2 = trim($spreadsheet->getActiveSheet()->getCell('AH' . ($i + 1))->getValue());
				if($QUANTITE_T2=='-')
				{
					$QUANTITE_T2=str_replace('-', 0, $QUANTITE_T2);
				}
				
				$QUANTITE_T2=str_replace('', 0, $QUANTITE_T2);
				$QUANTITE_T2=str_replace('-0', 0, $QUANTITE_T2);
				$QUANTITE_T3 = trim($spreadsheet->getActiveSheet()->getCell('AI' . ($i + 1))->getValue());
				if($QUANTITE_T3=='-')
				{
					$QUANTITE_T3=str_replace('-', 0, $QUANTITE_T3);
				}
				
				$QUANTITE_T3=str_replace('', 0, $QUANTITE_T3);
				$QUANTITE_T3=str_replace('-0', 0, $QUANTITE_T3);
				$QUANTITE_T4 = trim($spreadsheet->getActiveSheet()->getCell('AJ' . ($i + 1))->getValue());
				if($QUANTITE_T4=='-')
				{
					$QUANTITE_T4=str_replace('-', 0, $QUANTITE_T4);
				}
				
				$QUANTITE_T4=str_replace('', 0, $QUANTITE_T4);
				$QUANTITE_T4=str_replace('-0', 0, $QUANTITE_T4);
				$COUT_UNITAIRE = trim($spreadsheet->getActiveSheet()->getCell('AK' . ($i + 1))->getValue());
				if($COUT_UNITAIRE=='-')
				{
					$COUT_UNITAIRE=str_replace('-', 0, $COUT_UNITAIRE);
				}
				
				$COUT_UNITAIRE=str_replace('', 0, $COUT_UNITAIRE);
				$COUT_UNITAIRE=str_replace('-0', 0, $COUT_UNITAIRE);
				$BUDGET_T1 = trim($spreadsheet->getActiveSheet()->getCell('AL' . ($i + 1))->getValue());
				if($BUDGET_T1=='-')
				{
					$BUDGET_T1=str_replace('-', 0, $BUDGET_T1);
				}
				
				$BUDGET_T1=str_replace('', 0, $BUDGET_T1);
				$BUDGET_T1=str_replace('-0', 0, $BUDGET_T1);
				$BUDGET_T2 = trim($spreadsheet->getActiveSheet()->getCell('AM' . ($i + 1))->getValue());
				if($BUDGET_T2=='-')
				{
					$BUDGET_T2=str_replace('-', 0, $BUDGET_T2);
				}
				
				$BUDGET_T2=str_replace('', 0, $BUDGET_T2);
				$BUDGET_T2=str_replace('-0', 0, $BUDGET_T2);
				$BUDGET_T3 = trim($spreadsheet->getActiveSheet()->getCell('AN' . ($i + 1))->getValue());
				if($BUDGET_T3=='-')
				{
					$BUDGET_T3=str_replace('-', 0, $BUDGET_T3);
				}
				
				$BUDGET_T3=str_replace('', 0, $BUDGET_T3);
				$BUDGET_T3=str_replace('-0', 0, $BUDGET_T3);
				$BUDGET_T4 = trim($spreadsheet->getActiveSheet()->getCell('AO' . ($i + 1))->getValue());
				if($BUDGET_T4=='-')
				{
					$BUDGET_T4=str_replace('-', 0, $BUDGET_T4);
				}
				
				$BUDGET_T4=str_replace('', 0, $BUDGET_T4);
				$BUDGET_T4=str_replace('-0', 0, $BUDGET_T4);
				$BUDGET_ANNUEL = trim($spreadsheet->getActiveSheet()->getCell('AP' . ($i + 1))->getValue());
				if($BUDGET_ANNUEL=='-')
				{
					$BUDGET_ANNUEL=str_replace('-', 0, $BUDGET_ANNUEL);
				}
				
				$BUDGET_ANNUEL=str_replace('', 0, $BUDGET_ANNUEL);
				$BUDGET_ANNUEL=str_replace('-0', 0, $BUDGET_ANNUEL);
				$STRUCTURE_RESPONSABLE = trim($spreadsheet->getActiveSheet()->getCell('AQ' . ($i + 1))->getValue());
				$CODE_GRANDE_MASSE = trim($spreadsheet->getActiveSheet()->getCell('AR' . ($i + 1))->getValue());
				$INTITULE_GRANDE_MASSE = trim($spreadsheet->getActiveSheet()->getCell('AS' . ($i + 1))->getValue());

				//var pour les valeurs null potentielles
				$str_column = '';
				$str_value = '';

				//get the PTBA_TACHE_ID from ptba_tache
				$requetePtba = "SELECT PTBA_TACHE_ID FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE = '".$CODE_NOMENCLATURE_BUDGETAIRE."' AND DESC_TACHE = '".$TACHES."'";
				$PTBA_TACHE = $this->ModelPs->getRequete('CALL `getTable`("' . $requetePtba . '")');
				$PTBA_TACHE_ID = count($PTBA_TACHE) == 1 ? $PTBA_TACHE[0]->PTBA_TACHE_ID : 0;
				
				//get the INSTITUTION_ID from inst_institutions
				$requeteInst = "SELECT INSTITUTION_ID FROM inst_institutions WHERE CODE_INSTITUTION = '".$CODE_MINISTERE."'";
				$INSTITUTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteInst . '")')['INSTITUTION_ID'] ?? 0;

				//get the SOUS_TUTEL_ID from inst_institutions_sous_tutel
				$CODE_SOUS_TUTEL=substr($CODE_NOMENCLATURE_BUDGETAIRE,4,3);
				$requeteSousTut = "SELECT SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE CODE_SOUS_TUTEL ='".$CODE_SOUS_TUTEL."' AND INSTITUTION_ID=".$INSTITUTION_ID;
				$SOUS_TUTEL_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteSousTut . '")')['SOUS_TUTEL_ID'] ?? 0;

				//get the ID_PILIER from pnd_pilier
				$ID_PILIER=substr($PILIER,7,1);

				//get the OBJECTIF_VISION_ID from vision_objectif
				$CODE_OBJECTIF_VISION=substr($OBJECTIF_DE_LA_VISION,0,2);
				$requeteVision = "SELECT OBJECTIF_VISION_ID FROM vision_objectif WHERE CODE_OBJECTIF_VISION = '".addslashes($CODE_OBJECTIF_VISION)."'";
				$OBJECTIF_VISION = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteVision . '")');

				$OBJECTIF_VISION_ID = 0;
				if ($OBJECTIF_VISION){
					$OBJECTIF_VISION_ID = $OBJECTIF_VISION['OBJECTIF_VISION_ID'];
				}
				else{
					$colums_objet_v="CODE_OBJECTIF_VISION,DESC_OBJECTIF_VISION";
					$value_objet_v='"'.$CODE_OBJECTIF_VISION.'","'.$OBJECTIF_VISION.'"';
					$OBJECTIF_VISION_ID = $this->save_all_table('vision_objectif',$colums_objet_v,$value_objet_v);
				}

				//get the AXE_PND_ID from vision_objectif
				$AXE_PND_ID=substr($AXES_DU_PND_REVISE,4,1);

				//get the PROGRAMME_ID from inst_institutions_programmes
				$requeteProg = "SELECT PROGRAMME_ID FROM inst_institutions_programmes WHERE CODE_PROGRAMME = '".addslashes($CODE_PROGRAMME)."'";
				$PROGRAMME_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteProg . '")')['PROGRAMME_ID'] ?? 0;

				//get the ACTION_ID from inst_institutions_actions
				if(empty($CODE_ACTION))
              	{
                	$CODE_ACTION=$CODE_PROGRAMME."01";
              	}
				$requeteAction = "SELECT ACTION_ID FROM inst_institutions_actions WHERE CODE_ACTION = '".addslashes($CODE_ACTION)."'";
				$ACTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteAction . '")')['ACTION_ID'] ?? 0;

				//get the PROGRAMME_PRIORITAIRE_ID from inst_institutions_programme_prioritaire
				$PROGRAMME_PRIORITAIRE = str_replace("'","’", $PROGRAMME_PRIORITAIRE);
				$PROGRAMME_PRIORITAIRE = str_replace('\n',' ', $PROGRAMME_PRIORITAIRE);
				$PROGRAMME_PRIORITAIRE = str_replace('"','', $PROGRAMME_PRIORITAIRE);
				$requeteProgPr = "SELECT PROGRAMME_PRIORITAIRE_ID FROM inst_institutions_programme_prioritaire WHERE DESC_PROGRAMME_PRIORITAIRE = '".addslashes($PROGRAMME_PRIORITAIRE)."'";
				$PROGRAMME_PRIORITAIRE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteProgPr . '")')['PROGRAMME_PRIORITAIRE_ID'] ?? null;
				if( $PROGRAMME_PRIORITAIRE_ID != null)
				{
					$str_column .= ",PROGRAMME_PRIORITAIRE_ID";
					$str_value .= ",{$PROGRAMME_PRIORITAIRE_ID}";
				}
				else{
					if (!empty($PROGRAMME_PRIORITAIRE))
					{
						$CODE_PROGRAMME_PRIORITAIRE = substr($PROGRAMME_PRIORITAIRE,0,5);
						$colums="CODE_PROGRAMME_PRIORITAIRE,DESC_PROGRAMME_PRIORITAIRE";
						$value='"'.$CODE_PROGRAMME_PRIORITAIRE.'","'.$PROGRAMME_PRIORITAIRE.'"';
						$PROGRAMME_PRIORITAIRE_ID = $this->save_all_table('inst_institutions_programme_prioritaire',$colums,$value);

						$str_column .= ",PROGRAMME_PRIORITAIRE_ID";
						$str_value .= ",{$PROGRAMME_PRIORITAIRE_ID}";
					}
				}

				//get the ARTICLE_ID from inst_institutions_programme_prioritaire
				$requeteArticle = "SELECT ARTICLE_ID FROM class_economique_article WHERE CODE_ARTICLE = '".addslashes($ARTICLE_ECONOMIQUE)."'";
				$ARTICLE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteArticle . '")')['ARTICLE_ID'] ?? 0;

				//get the SOUS_LITTERA_ID from class_economique_sous_littera
				$requeteSousLittera = "SELECT SOUS_LITTERA_ID FROM class_economique_sous_littera WHERE CODE_SOUS_LITTERA = '".addslashes($NATURE_ECONOMIQUE)."'";
				$SOUS_LITTERA_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteSousLittera . '")')['SOUS_LITTERA_ID'] ?? 0;

				//get the DIVISION_ID from class_economique_sous_littera
				$requeteDivision = "SELECT DIVISION_ID FROM class_fonctionnelle_division WHERE CODE_DIVISION = '".addslashes($DIVISION_FONCTIONNELLE)."'";
				$DIVISION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteDivision . '")')['DIVISION_ID'] ?? 0;

				//get the GROUPE_ID from class_economique_sous_littera
				$requeteGroup = "SELECT GROUPE_ID FROM class_fonctionnelle_groupe WHERE CODE_GROUPE = '".addslashes($GROUPE_FONCTIONNELLE)."'";
				$GROUPE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteGroup . '")')['GROUPE_ID'] ?? 0;

				//get the CLASSE_ID from class_economique_sous_littera
				$requeteClasse = "SELECT CLASSE_ID FROM class_fonctionnelle_classe WHERE CODE_CLASSE = '".addslashes($CLASSE_FONCTIONNELLE)."'";
				$CLASSE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteClasse . '")')['CLASSE_ID'] ?? 0;

				//get the COSTAB_ACTIVITE_ID from costab_activites
				$ACTIVITES_COSTAB = str_replace("'","’", $ACTIVITES_COSTAB);
				$ACTIVITES_COSTAB = str_replace('\n',' ', $ACTIVITES_COSTAB);
				$ACTIVITES_COSTAB = str_replace('"','', $ACTIVITES_COSTAB);
				do{
					$count = count(explode('  ', $ACTIVITES_COSTAB));
					$ACTIVITES_COSTAB = str_replace('  ',' ',$ACTIVITES_COSTAB);
				}
				while($count > 1);
				$requeteCostabActivite = "SELECT COSTAB_ACTIVITE_ID FROM costab_activites WHERE DESC_COSTAB_ACTIVITE = '".addslashes($ACTIVITES_COSTAB)."'";
				$COSTAB_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteCostabActivite . '")')['COSTAB_ACTIVITE_ID'] ?? null;
				if( $COSTAB_ACTIVITE_ID != null)
				{
					$str_column .= ",COSTAB_ACTIVITE_ID";
					$str_value .= ",{$COSTAB_ACTIVITE_ID}";
				}
				else{
					if (!empty($ACTIVITES_COSTAB))
					{
						$colums="DESC_COSTAB_ACTIVITE";
						$value='"'.$ACTIVITES_COSTAB.'"';
						$COSTAB_ACTIVITE_ID = $this->save_all_table('costab_activites',$colums,$value);
						$str_column .= ",COSTAB_ACTIVITE_ID";
						$str_value .= ",{$COSTAB_ACTIVITE_ID}";
					}
				}

				//get the INDICATEUR_PND_ID from pnd_indicateur
				$INDICATEURS_PND = str_replace("'","’", $INDICATEURS_PND);
				$INDICATEURS_PND = str_replace('\n',' ', $INDICATEURS_PND);
				$INDICATEURS_PND = str_replace('"','', $INDICATEURS_PND);
				do{
					$count = count(explode('  ', $INDICATEURS_PND));
					$INDICATEURS_PND = str_replace('  ',' ',$INDICATEURS_PND);
				}
				while($count > 1);
				$requetePnd = "SELECT INDICATEUR_PND_ID FROM pnd_indicateur WHERE DESC_INDICATEUR_PND = '".addslashes($INDICATEURS_PND)."'";
				$INDICATEUR_PND_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePnd . '")')['INDICATEUR_PND_ID'] ?? null;
				if( $INDICATEUR_PND_ID != null)
				{
					$str_column .= ",PND_INDICATEUR_ID";
					$str_value .= ",{$INDICATEUR_PND_ID}";
				}
				else{
					if (!empty($INDICATEURS_PND))
					{
						$colums="DESC_INDICATEUR_PND";
						$value='"'.$INDICATEURS_PND.'"';
						$INDICATEUR_PND_ID = $this->save_all_table('pnd_indicateur',$colums,$value);
						$str_column .= ",PND_INDICATEUR_ID";
						$str_value .= ",{$INDICATEUR_PND_ID}";
					}
				}

				//get the STRUTURE_RESPONSABLE_TACHE_ID from struture_responsable_tache
				$STRUCTURE_RESPONSABLE = str_replace("'","’", $STRUCTURE_RESPONSABLE);
				$STRUCTURE_RESPONSABLE = str_replace('\n',' ', $STRUCTURE_RESPONSABLE);
				$STRUCTURE_RESPONSABLE = str_replace('"','', $STRUCTURE_RESPONSABLE);
				$requeteStrResp = "SELECT STRUTURE_RESPONSABLE_TACHE_ID FROM struture_responsable_tache WHERE DESC_STRUTURE_RESPONSABLE_TACHE = '".addslashes($STRUCTURE_RESPONSABLE)."'";
				$STRUTURE_RESPONSABLE_TACHE = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteStrResp . '")');

				$STRUTURE_RESPONSABLE_TACHE_ID = 0;
				if ($STRUTURE_RESPONSABLE_TACHE){
					$STRUTURE_RESPONSABLE_TACHE_ID = $STRUTURE_RESPONSABLE_TACHE['STRUTURE_RESPONSABLE_TACHE_ID'];
				}
				else{
					$colums="DESC_STRUTURE_RESPONSABLE_TACHE";
					$value='"'.$STRUCTURE_RESPONSABLE.'"';
					$STRUTURE_RESPONSABLE_TACHE_ID = $this->save_all_table('struture_responsable_tache',$colums,$value);
				}

				//get the CODE_NOMENCLATURE_BUDGETAIRE_ID from inst_institutions_ligne_budgetaire
				$requeteNomenclature = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE = '".addslashes($CODE_NOMENCLATURE_BUDGETAIRE)."'";
				$CODE_NOMENCLATURE_BUDGETAIRE_ONE = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteNomenclature . '")');
				$CODE_NOMENCLATURE_BUDGETAIRE_ID = 0;
				if ($CODE_NOMENCLATURE_BUDGETAIRE_ONE){
					$CODE_NOMENCLATURE_BUDGETAIRE_ID = $CODE_NOMENCLATURE_BUDGETAIRE_ONE['CODE_NOMENCLATURE_BUDGETAIRE_ID'];
				}
				else{
					$colums="INSTITUTION_ID,SOUS_TUTEL_ID,CODE_NOMENCLATURE_BUDGETAIRE";
					$value=$INSTITUTION_ID.','.$SOUS_TUTEL_ID.',"'.$CODE_NOMENCLATURE_BUDGETAIRE.'"';
					$CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->save_all_table('inst_institutions_ligne_budgetaire',$colums,$value);
				}

				//get the PAP_ACTIVITE_ID from pap_activites
				$ACTIVITES_PAP = str_replace("'","’", $ACTIVITES_PAP);
				$ACTIVITES_PAP = str_replace('\n',' ', $ACTIVITES_PAP);
				$ACTIVITES_PAP = str_replace('"','', $ACTIVITES_PAP);
				do{
					$count = count(explode('  ', $ACTIVITES_PAP));
					$ACTIVITES_PAP = str_replace('  ',' ',$ACTIVITES_PAP);
				}
				while($count > 1);
				$requetePapActivite = "SELECT PAP_ACTIVITE_ID FROM pap_activites WHERE DESC_PAP_ACTIVITE = '".addslashes($ACTIVITES_PAP)."' AND CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
				$PAP_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePapActivite . '")')['PAP_ACTIVITE_ID'] ?? null;
				if( $PAP_ACTIVITE_ID != null)
				{
					$str_column .= ",PAP_ACTIVITE_ID";
					$str_value .= ",{$PAP_ACTIVITE_ID}";
				}
				else{
					if (!empty($ACTIVITES_PAP))
					{
						$colums="DESC_PAP_ACTIVITE";
						$value='"'.$ACTIVITES_PAP.'"';
						$PAP_ACTIVITE_ID = $this->save_all_table('pap_activites',$colums,$value);
						$str_column .= ",PAP_ACTIVITE_ID";
						$str_value .= ",{$PAP_ACTIVITE_ID}";
					}
				}

				$GRANDE_MASSE_ID  = $CODE_GRANDE_MASSE;

				$BUDGET_RESTANT_T1 = $BUDGET_T1;
				$BUDGET_RESTANT_T2 = $BUDGET_T2;
				$BUDGET_RESTANT_T3 = $BUDGET_T3;
				$BUDGET_RESTANT_T4 = $BUDGET_T4;

				$ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

				$insertIntoTable='ptba_tache_revise';
				$columsinsert="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID,CODES_PROGRAMMATIQUE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_RESTANT_T1,BUDGET_T2,BUDGET_RESTANT_T2,BUDGET_T3,BUDGET_RESTANT_T3,BUDGET_T4,BUDGET_RESTANT_T4,BUDGET_ANNUEL,STRUTURE_RESPONSABLE_TACHE_ID,GRANDE_MASSE_ID,ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID".$str_column;
				$datacolumsinsert = "{$INSTITUTION_ID},{$SOUS_TUTEL_ID},{$ID_PILIER},{$OBJECTIF_VISION_ID},{$AXE_PND_ID},{$PROGRAMME_ID},{$ACTION_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID},'{$CODE_NOMENCLATURE_BUDGETAIRE}',{$ARTICLE_ID},{$SOUS_LITTERA_ID},{$DIVISION_ID},{$GROUPE_ID},{$CLASSE_ID},'{$CODES_PROGRAMMATIQUE}','{$TACHES}','{$RESULTATS_ATTENDUS_TACHES}','{$UNITE}','{$TOTAL_QUANTITE}','{$QUANTITE_T1}','{$QUANTITE_T2}','{$QUANTITE_T3}','{$QUANTITE_T4}','{$COUT_UNITAIRE}','{$BUDGET_T1}','{$BUDGET_RESTANT_T1}','{$BUDGET_T2}','{$BUDGET_RESTANT_T2}','{$BUDGET_T3}','{$BUDGET_RESTANT_T3}','{$BUDGET_T4}','{$BUDGET_RESTANT_T4}','{$BUDGET_ANNUEL}',{$STRUTURE_RESPONSABLE_TACHE_ID},{$GRANDE_MASSE_ID},{$ANNEE_BUDGETAIRE_ID},{$PTBA_TACHE_ID}".$str_value;

				$this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
			}
		}

		return redirect('double_commande_new/Liste_ptba_revise');
	}

	public function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
	    // $columsinsert: Nom des colonnes separe par,
	    // $datacolumsinsert : les donnees a inserer dans les colonnes
	    $bindparms = [$table, $columsinsert, $datacolumsinsert];
	    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
	    $tableparams = [$table, $columsinsert, $datacolumsinsert];
	    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
	    return $id = $result['id'];
	}

	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
	    $db = db_connect();
	    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
	    return $bindparams;
	}
}

?>