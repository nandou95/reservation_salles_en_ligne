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

class PTBA_Upload extends BaseController
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

		return view('App\Modules\double_commande_new\Views\PTBA_Upload_View',$data);
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

		$insertIntoTable='ptba_tache_1';
		
		$spreadsheet=$reader->load($_FILES["FICHIER_PTBA"]["tmp_name"]);
		$sheetdata=$spreadsheet->getActiveSheet()->toArray();
		$sheetcount=count($sheetdata);
		

		if($sheetcount>1)
		{
			for($i=1; $i < $sheetcount; $i++)
			{
				$CODE_MINISTERE = trim($spreadsheet->getActiveSheet()->getCell('A' . ($i + 1))->getValue());
				//$INTITULE_MINISTERE = trim($spreadsheet->getActiveSheet()->getCell('B' . ($i + 1))->getValue());
				$PILIER = trim($spreadsheet->getActiveSheet()->getCell('C' . ($i + 1))->getValue());
				$ID_PILIER=substr($PILIER,7,1);
	          	
				$OBJECTIF_DE_LA_VISION = trim($spreadsheet->getActiveSheet()->getCell('D' . ($i + 1))->getValue());
				$CODE_OBJECTIF_VISION=substr($OBJECTIF_DE_LA_VISION,0,2);

				// $OBJECTIF_DE_LA_VISION = str_replace("'","’", $OBJECTIF_DE_LA_VISION);
				// $OBJECTIF_DE_LA_VISION = str_replace('\n',' ', $OBJECTIF_DE_LA_VISION);
				// $OBJECTIF_DE_LA_VISION = str_replace('"','', $OBJECTIF_DE_LA_VISION);

				$AXES_DU_PND_REVISE = trim($spreadsheet->getActiveSheet()->getCell('E' . ($i + 1))->getValue());
				$AXES_DU_PND_REVISE = str_replace("'","’", $AXES_DU_PND_REVISE);
				$AXES_DU_PND_REVISE = str_replace('\n',' ', $AXES_DU_PND_REVISE);
				$AXES_DU_PND_REVISE = str_replace('"','', $AXES_DU_PND_REVISE);
				$AXE_PND_ID=substr($AXES_DU_PND_REVISE,4,1);

				$CODE_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('F' . ($i + 1))->getValue());
				$INTITULE_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('G' . ($i + 1))->getValue());
				$INTITULE_PROGRAMME = str_replace("'","’", $INTITULE_PROGRAMME);
				$INTITULE_PROGRAMME = str_replace('\n',' ', $INTITULE_PROGRAMME);
				$INTITULE_PROGRAMME = str_replace('"','', $INTITULE_PROGRAMME);

				$OBJECTIF_PROGRAMME = trim($spreadsheet->getActiveSheet()->getCell('H' . ($i + 1))->getValue());
				$OBJECTIF_PROGRAMME = str_replace("'","’", $OBJECTIF_PROGRAMME);
				$OBJECTIF_PROGRAMME = str_replace('\n',' ', $OBJECTIF_PROGRAMME);
				$OBJECTIF_PROGRAMME = str_replace('"','', $OBJECTIF_PROGRAMME);
				$CODE_ACTION = trim($spreadsheet->getActiveSheet()->getCell('I' . ($i + 1))->getValue());				
				if(empty($CODE_ACTION))
				{
					$CODE_ACTION=$CODE_PROGRAMME."01";
				}
				$LIBELLE_ACTION = trim($spreadsheet->getActiveSheet()->getCell('J' . ($i + 1))->getValue());
				$LIBELLE_ACTION = str_replace("'","’", $LIBELLE_ACTION);
				$LIBELLE_ACTION = str_replace('\n',' ', $LIBELLE_ACTION);
				$LIBELLE_ACTION = str_replace('"','', $LIBELLE_ACTION);

				$OBJECTIF_ACTION = trim($spreadsheet->getActiveSheet()->getCell('K' . ($i + 1))->getValue());
				$OBJECTIF_ACTION = str_replace("'","’", $OBJECTIF_ACTION);
				$OBJECTIF_ACTION = str_replace('\n',' ', $OBJECTIF_ACTION);
				$OBJECTIF_ACTION = str_replace('"','', $OBJECTIF_ACTION);

				$PROGRAMME_PRIORITAIRE = trim($spreadsheet->getActiveSheet()->getCell('L' . ($i + 1))->getValue());
				$PROGRAMME_PRIORITAIRE = str_replace("'","’", $PROGRAMME_PRIORITAIRE);
				$PROGRAMME_PRIORITAIRE = str_replace('\n',' ', $PROGRAMME_PRIORITAIRE);
				$PROGRAMME_PRIORITAIRE = str_replace('"','', $PROGRAMME_PRIORITAIRE);
				$CODE_NOMENCLATURE_BUDGETAIRE = trim($spreadsheet->getActiveSheet()->getCell('M' . ($i + 1))->getValue());
				$ARTICLE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('N' . ($i + 1))->getValue());
				//$INTITULE_ARTICLE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('O' . ($i + 1))->getValue());
				$NATURE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('P' . ($i + 1))->getValue());
				//$INTITULE_NATURE_ECONOMIQUE = trim($spreadsheet->getActiveSheet()->getCell('Q' . ($i + 1))->getValue());
				$DIVISION_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('R' . ($i + 1))->getValue());
				//$INTITULE_DIVISION_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('S' . ($i + 1))->getValue());
				$GROUPE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('T' . ($i + 1))->getValue());
				//$INTITULE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('U' . ($i + 1))->getValue());
				$CLASSE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('V' . ($i + 1))->getValue());
				//$INTITULE_CLASSE_FONCTIONNELLE = trim($spreadsheet->getActiveSheet()->getCell('W' . ($i + 1))->getValue());
				$ACTIVITES_PAP = trim($spreadsheet->getActiveSheet()->getCell('X' . ($i + 1))->getValue());
				$ACTIVITES_PAP = str_replace("'","’", $ACTIVITES_PAP);
				$ACTIVITES_PAP = str_replace('\n',' ', $ACTIVITES_PAP);
				$ACTIVITES_PAP = str_replace('"','', $ACTIVITES_PAP);
				$RESULTATS_ATTENDUS_PAP = trim($spreadsheet->getActiveSheet()->getCell('Y' . ($i + 1))->getValue());
				$RESULTATS_ATTENDUS_PAP = str_replace("'","’", $RESULTATS_ATTENDUS_PAP);
				$RESULTATS_ATTENDUS_PAP = str_replace('\n',' ', $RESULTATS_ATTENDUS_PAP);
				$RESULTATS_ATTENDUS_PAP = str_replace('"','', $RESULTATS_ATTENDUS_PAP);
				$ACTIVITES_COSTAB = trim($spreadsheet->getActiveSheet()->getCell('Z' . ($i + 1))->getValue());
				$ACTIVITES_COSTAB = str_replace("'","’", $ACTIVITES_COSTAB);
				$ACTIVITES_COSTAB = str_replace('\n',' ', $ACTIVITES_COSTAB);
				$ACTIVITES_COSTAB = str_replace('"','', $ACTIVITES_COSTAB);
				$INDICATEURS_PND = trim($spreadsheet->getActiveSheet()->getCell('AA' . ($i + 1))->getValue());
				$INDICATEURS_PND = str_replace("'","’", $INDICATEURS_PND);
				$INDICATEURS_PND = str_replace('\n',' ', $INDICATEURS_PND);
				$INDICATEURS_PND = str_replace('"','', $INDICATEURS_PND);
				$CODES_PROGRAMMATIQUE = trim($spreadsheet->getActiveSheet()->getCell('AB' . ($i + 1))->getValue());
				$TACHES = trim($spreadsheet->getActiveSheet()->getCell('AC' . ($i + 1))->getValue());
				$TACHES = str_replace("'","’", $TACHES);
				$TACHES = str_replace("  "," ", $TACHES);
				$TACHES = str_replace('\n',' ', $TACHES);
				$TACHES = str_replace('"','', $TACHES);

				$RESULTATS_ATTENDUS_TACHES = trim($spreadsheet->getActiveSheet()->getCell('AD' . ($i + 1))->getValue());
				$RESULTATS_ATTENDUS_TACHES = str_replace("'","’", $RESULTATS_ATTENDUS_TACHES);
				$RESULTATS_ATTENDUS_TACHES = str_replace("'","\'", $RESULTATS_ATTENDUS_TACHES);
				$RESULTATS_ATTENDUS_TACHES = str_replace('\n',' ', $RESULTATS_ATTENDUS_TACHES);
				$RESULTATS_ATTENDUS_TACHES = str_replace('"','', $RESULTATS_ATTENDUS_TACHES);
				$UNITE = trim($spreadsheet->getActiveSheet()->getCell('AE' . ($i + 1))->getValue());
				$UNITE = str_replace("’","'", $UNITE);
				$UNITE = str_replace("'","\'", $UNITE);
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
				$STRUCTURE_RESPONSABLE = str_replace("'","’", $STRUCTURE_RESPONSABLE);
				$STRUCTURE_RESPONSABLE = str_replace('\n',' ', $STRUCTURE_RESPONSABLE);
				$STRUCTURE_RESPONSABLE = str_replace('"','', $STRUCTURE_RESPONSABLE);
				$CODE_GRANDE_MASSE = trim($spreadsheet->getActiveSheet()->getCell('AR' . ($i + 1))->getValue());
				// $INTITULE_GRANDE_MASSE = trim($spreadsheet->getActiveSheet()->getCell('AS' . ($i + 1))->getValue());

				//get the INSTITUTION_ID from inst_institutions
				$requeteInst = "SELECT INSTITUTION_ID FROM inst_institutions WHERE CODE_INSTITUTION = '".$CODE_MINISTERE."'";
				$INSTITUTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteInst . '")')['INSTITUTION_ID'];

				//get the SOUS_TUTEL_ID from inst_institutions_ligne_budgetaire
				$CODE_SOUS_TUTEL=substr($CODE_NOMENCLATURE_BUDGETAIRE,4,3);
				$requeteSousTut = "SELECT SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."' AND INSTITUTION_ID=".$INSTITUTION_ID;
				$SOUS_TUTEL_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteSousTut . '")')['SOUS_TUTEL_ID'];

				//get the OBJECTIF_VISION_ID from vision_objectif
				$CODE_OBJECTIF_VISION=substr($OBJECTIF_DE_LA_VISION,0,2);
				$requeteVision = "SELECT OBJECTIF_VISION_ID FROM vision_objectif WHERE CODE_OBJECTIF_VISION = '".addslashes($CODE_OBJECTIF_VISION)."'";
				$colums_objet_v="CODE_OBJECTIF_VISION,DESC_OBJECTIF_VISION";
				$value_objet_v='"'.$CODE_OBJECTIF_VISION.'","'.$OBJECTIF_VISION.'"';
				$OBJECTIF_VISION = $this->ModelPs->getRequeteOne('CALL getTable("' . $requeteVision . '")');
				
				$OBJECTIF_VISION_ID=0;
				if ($OBJECTIF_VISION)
				{
					$OBJECTIF_VISION_ID = $OBJECTIF_VISION['OBJECTIF_VISION_ID'];
				}
				else
				{
					$OBJECTIF_VISION_ID = $this->save_all_table('vision_objectif',$colums_objet_v,$value_objet_v);
				}

				//get the PROGRAMME_ID from inst_institutions_programmes
				$requeteProg = "SELECT PROGRAMME_ID FROM inst_institutions_programmes WHERE CODE_PROGRAMME = '".addslashes($CODE_PROGRAMME)."'";
				$PROGRAMME_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteProg . '")')['PROGRAMME_ID']??0;
				if($PROGRAMME_ID==0)
				{
					$data_prog=$INSTITUTION_ID.',"'.$CODE_PROGRAMME.'","'.$INTITULE_PROGRAMME.'","'.$OBJECTIF_PROGRAMME.'"';
					$columns='INSTITUTION_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_DU_PROGRAMME';
					$PROGRAMME_ID=$this->save_all_table('inst_institutions_programmes',$columns,$data_prog);
				}

				//get the ACTION_ID from inst_institutions_actions			
				$requeteAction = "SELECT ACTION_ID FROM inst_institutions_actions WHERE CODE_ACTION = '".addslashes($CODE_ACTION)."'";
				$ACTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteAction . '")')['ACTION_ID']??0;
				if($ACTION_ID==0)
				{
					$columns_actions='PROGRAMME_ID, CODE_ACTION, LIBELLE_ACTION, OBJECTIF_ACTION';
					$data_actions=$PROGRAMME_ID.',"'.$CODE_ACTION.'","'.$LIBELLE_ACTION.'","'.$OBJECTIF_ACTION.'"';
					$ACTION_ID=$this->save_all_table('inst_institutions_actions',$columns_actions,$data_actions);
				}

				$str_column='';
				$str_value='';
				//get the PROGRAMME_PRIORITAIRE_ID from inst_institutions_programme_prioritaire
				$requeteProgPr = "SELECT PROGRAMME_PRIORITAIRE_ID FROM inst_institutions_programme_prioritaire WHERE DESC_PROGRAMME_PRIORITAIRE = '".addslashes($PROGRAMME_PRIORITAIRE)."'";
				$PROGRAMME_PRIORITAIRE_ID = $this->ModelPs->getRequeteOne('CALL getTable("' . $requeteProgPr . '")')['PROGRAMME_PRIORITAIRE_ID'] ?? null;
				if( $PROGRAMME_PRIORITAIRE_ID != null)
				{
					$str_column .= ",PROGRAMME_PRIORITAIRE_ID";
					$str_value .= ",{$PROGRAMME_PRIORITAIRE_ID}";
				}
				else
				{
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
				$ARTICLE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteArticle . '")')['ARTICLE_ID'];
				
				//get the SOUS_LITTERA_ID from class_economique_sous_littera				
				$requeteSousLittera = "SELECT SOUS_LITTERA_ID FROM class_economique_sous_littera WHERE CODE_SOUS_LITTERA = '".addslashes($NATURE_ECONOMIQUE)."'";
				$SOUS_LITTERA_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteSousLittera . '")');
				$SOUS_LITTERA_ID=$SOUS_LITTERA_ID['SOUS_LITTERA_ID'];

				//get the DIVISION_ID from class_economique_sous_littera
				$requeteDivision = "SELECT DIVISION_ID FROM class_fonctionnelle_division WHERE CODE_DIVISION = '".addslashes($DIVISION_FONCTIONNELLE)."'";
				$DIVISION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteDivision . '")')['DIVISION_ID'];
				// if($DIVISION_ID==0)
				// {
				// 	$data_division='CODE_DIVISION, LIBELLE_DIVISION';
				// 	$columns_division='"'$CODE_DIVISION.'","'.$LIBELLE_DIVISION.'"';
				// 	$DIVISION_ID=$this->save_all_table('class_fonctionnelle_division',$columns_division,$columns_division);
				// }

				//get the GROUPE_ID from class_economique_sous_littera
				$requeteGroup = "SELECT GROUPE_ID FROM class_fonctionnelle_groupe WHERE CODE_GROUPE = '".addslashes($GROUPE_FONCTIONNELLE)."'";
				$GROUPE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteGroup . '")')['GROUPE_ID'];

				// if($GROUPE_ID==0)
				// {
				// 	$data_groupe='DIVISION_ID, CODE_GROUPE, LIBELLE_GROUPE';
				// 	$columns_groupe=$DIVISION_ID.',"'$CODE_GROUPE.'","'.$LIBELLE_GROUPE.'"';
				// 	$DIVISION_ID=$this->save_all_table('class_fonctionnelle_division',$columns_groupe,$data_groupe);
				// }

				//get the CLASSE_ID from class_economique_sous_littera
				$requeteClasse = "SELECT CLASSE_ID FROM class_fonctionnelle_classe WHERE CODE_CLASSE = '".addslashes($CLASSE_FONCTIONNELLE)."'";
				$CLASSE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteClasse . '")')['CLASSE_ID'] ;

				//get the CODE_NOMENCLATURE_BUDGETAIRE_ID from inst_institutions_ligne_budgetaire
				$requeteNomenclature = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE = '".addslashes($CODE_NOMENCLATURE_BUDGETAIRE)."'";
				$CODE_NOMENCLATURE_BUDGETAIRE_ONE = $this->ModelPs->getRequeteOne('CALL getTable("' . $requeteNomenclature . '")');
				$CODE_NOMENCLATURE_BUDGETAIRE_ID = 0;
				if ($CODE_NOMENCLATURE_BUDGETAIRE_ONE)
				{
					$CODE_NOMENCLATURE_BUDGETAIRE_ID = $CODE_NOMENCLATURE_BUDGETAIRE_ONE['CODE_NOMENCLATURE_BUDGETAIRE_ID'];
				}
				else
				{
					$colums="INSTITUTION_ID,SOUS_TUTEL_ID,CODE_NOMENCLATURE_BUDGETAIRE";
					$value=$INSTITUTION_ID.','.$SOUS_TUTEL_ID.',"'.$CODE_NOMENCLATURE_BUDGETAIRE.'"';
					$CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->save_all_table('inst_institutions_ligne_budgetaire',$colums,$value);
				}

				//get the PAP_ACTIVITE_ID from pap_activites
				$requetePapActivite = "SELECT PAP_ACTIVITE_ID FROM pap_activites WHERE DESC_PAP_ACTIVITE = '".addslashes($ACTIVITES_PAP)."' AND CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
				$PAP_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL getTable("' . $requetePapActivite . '")')['PAP_ACTIVITE_ID'] ?? null;
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

				//get the COSTAB_ACTIVITE_ID from costab_activites
				$requeteCostabActivite = "SELECT COSTAB_ACTIVITE_ID FROM costab_activites WHERE DESC_COSTAB_ACTIVITE = '".addslashes($ACTIVITES_COSTAB)."'";
				$COSTAB_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL getTable("' . $requeteCostabActivite . '")')['COSTAB_ACTIVITE_ID'] ?? null;
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
				$requetePnd = "SELECT INDICATEUR_PND_ID FROM pnd_indicateur WHERE DESC_INDICATEUR_PND = '".addslashes($INDICATEURS_PND)."'";
				$INDICATEUR_PND_ID = $this->ModelPs->getRequeteOne('CALL getTable("' . $requetePnd . '")')['INDICATEUR_PND_ID'] ?? null;
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
					}
				}

				//get the STRUTURE_RESPONSABLE_TACHE_ID from struture_responsable_tache
				$requeteStrResp = "SELECT STRUTURE_RESPONSABLE_TACHE_ID FROM struture_responsable_tache WHERE DESC_STRUTURE_RESPONSABLE_TACHE = '".addslashes($STRUCTURE_RESPONSABLE)."'";
				$STRUTURE_RESPONSABLE_TACHE = $this->ModelPs->getRequeteOne('CALL getTable("' . $requeteStrResp . '")');

				$STRUTURE_RESPONSABLE_TACHE_ID = 0;
				if ($STRUTURE_RESPONSABLE_TACHE){
					$STRUTURE_RESPONSABLE_TACHE_ID = $STRUTURE_RESPONSABLE_TACHE['STRUTURE_RESPONSABLE_TACHE_ID'];
				}
				else{
					$colums="DESC_STRUTURE_RESPONSABLE_TACHE";
					$value='"'.$STRUCTURE_RESPONSABLE.'"';
					$STRUTURE_RESPONSABLE_TACHE_ID = $this->save_all_table('struture_responsable_tache',$colums,$value);
				}

				$GRANDE_MASSE_ID  = $CODE_GRANDE_MASSE;

				$BUDGET_RESTANT_T1 = $BUDGET_T1;
				$BUDGET_RESTANT_T2 = $BUDGET_T2;
				$BUDGET_RESTANT_T3 = $BUDGET_T3;
				$BUDGET_RESTANT_T4 = $BUDGET_T4;

				$ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

				$columsinsert="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID,CODES_PROGRAMMATIQUE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_RESTANT_T1,BUDGET_T2,BUDGET_RESTANT_T2,BUDGET_T3,BUDGET_RESTANT_T3,BUDGET_T4,BUDGET_RESTANT_T4,BUDGET_ANNUEL,STRUTURE_RESPONSABLE_TACHE_ID,GRANDE_MASSE_ID,ANNEE_BUDGETAIRE_ID".$str_column;
				//,PROGRAMME_PRIORITAIRE_ID,PAP_ACTIVITE_ID,COSTAB_ACTIVITE_ID,PND_INDICATEUR_ID

				$datacolumsinsert = "{$INSTITUTION_ID},{$SOUS_TUTEL_ID},{$ID_PILIER},{$OBJECTIF_VISION_ID},{$AXE_PND_ID},{$PROGRAMME_ID},{$ACTION_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID},'{$CODE_NOMENCLATURE_BUDGETAIRE}',{$ARTICLE_ID},{$SOUS_LITTERA_ID},{$DIVISION_ID},{$GROUPE_ID},{$CLASSE_ID},'{$CODES_PROGRAMMATIQUE}','{$TACHES}','{$RESULTATS_ATTENDUS_TACHES}','{$UNITE}','{$TOTAL_QUANTITE}','{$QUANTITE_T1}','{$QUANTITE_T2}','{$QUANTITE_T3}','{$QUANTITE_T4}','{$COUT_UNITAIRE}','{$BUDGET_T1}','{$BUDGET_RESTANT_T1}','{$BUDGET_T2}','{$BUDGET_RESTANT_T2}','{$BUDGET_T3}','{$BUDGET_RESTANT_T3}','{$BUDGET_T4}','{$BUDGET_RESTANT_T4}','{$BUDGET_ANNUEL}',{$STRUTURE_RESPONSABLE_TACHE_ID},{$GRANDE_MASSE_ID},{$ANNEE_BUDGETAIRE_ID}".$str_value;
				//,{$PROGRAMME_PRIORITAIRE_ID},{$PAP_ACTIVITE_ID},{$COSTAB_ACTIVITE_ID},{$INDICATEUR_PND_ID}

				$this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
			}
		}

		return 'Le fichier a été chargé avec succès';
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
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
	    $db = db_connect();
	    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
	    return $bindparams;
	}
}

?>