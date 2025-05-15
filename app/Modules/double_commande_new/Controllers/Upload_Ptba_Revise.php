<?php
/*
  * NDERAGAKURA Alain Charbel
  *Titre: Upload du fichier ptba revise
  *Numero de telephone: (+257)62003522
  *Email: charbel@mediabox.bi
  *Date: 19 decembre,2024
*/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Upload_Ptba_Revise extends BaseController
  {
    protected $session;
    protected $ModelPs;
    
    public function __construct()
    {
      $this->library = new CodePlayHelper();
      $this->ModelPs = new ModelPs();
      $this->session = \Config\Services::session();
      $this->validation = \Config\Services::validation();
    }

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table),
      $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
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

    //Update
    public function update_all_table($table,$datatomodifie,$conditions)
    {
      $bindparams =[$table,$datatomodifie,$conditions];
      $updateRequete = "CALL `updateData`(?,?,?);";
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
    }

    // affichage de l'interface de televersement
    function get_view($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      return view('App\Modules\double_commande_new\Views\Upload_Ptba_Revise_View',$data);          
    }

    public function save()
    {
      $UPLOAD_FILE=$_FILES["FICHIER_PTBA_REVISER"]["name"];

      $extension=pathinfo($UPLOAD_FILE,PATHINFO_EXTENSION);

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

      $spreadsheet=$reader->load($_FILES["FICHIER_PTBA_REVISER"]["tmp_name"]);
      $sheetdata=$spreadsheet->getActiveSheet()->toArray();
      $sheetcount=count($sheetdata);
      if($sheetcount>1)
      {
        for($i=1; $i < $sheetcount; $i++)
        {
          $CODE_MINISTERE = trim($sheetdata[$i][0]);
          $INTITULE_MINISTERE = trim($sheetdata[$i][1]);
          $PILIER = trim($sheetdata[$i][2]);
          $OBJECTIF_DE_LA_VISION = trim($sheetdata[$i][3]);
          $AXES_DU_PND_REVISE = trim($sheetdata[$i][4]);
          $CODE_PROGRAMME = trim($sheetdata[$i][5]);
          $INTITULE_PROGRAMME = trim($sheetdata[$i][6]);
          $OBJECTIF_PROGRAMME = trim($sheetdata[$i][7]);
          $CODE_ACTION = trim($sheetdata[$i][8]);
          $LIBELLE_ACTION = trim($sheetdata[$i][9]);
          $OBJECTIF_ACTION = trim($sheetdata[$i][10]);
          $PROGRAMME_PRIORITAIRE = trim($sheetdata[$i][11]);
          $CODE_NOMENCLATURE_BUDGETAIRE = trim($sheetdata[$i][12]);
          $ARTICLE_ECONOMIQUE = trim($sheetdata[$i][13]);
          $INTITULE_ARTICLE_ECONOMIQUE = trim($sheetdata[$i][14]);
          $NATURE_ECONOMIQUE = trim($sheetdata[$i][15]);
          $INTITULE_NATURE_ECONOMIQUE = trim($sheetdata[$i][16]);
          $DIVISION_FONCTIONNELLE = trim($sheetdata[$i][17]);
          $INTITULE_DIVISION_FONCTIONNELLE = trim($sheetdata[$i][18]);
          $GROUPE_FONCTIONNELLE = trim($sheetdata[$i][19]);
          $INTITULE_FONCTIONNELLE = trim($sheetdata[$i][20]);
          $CLASSE_FONCTIONNELLE = trim($sheetdata[$i][21]);
          $INTITULE_FONCTIONNELLE = trim($sheetdata[$i][22]);
          $ACTIVITES_PAP = trim($sheetdata[$i][23]);
          $RESULTATS_ATTENDUS_PAP = trim($sheetdata[$i][24]);
          $ACTIVITES_COSTAB = trim($sheetdata[$i][25]);
          $INDICATEURS_PND = trim($sheetdata[$i][26]);
          $CODES_PROGRAMMATIQUE = trim($sheetdata[$i][27]);
          $PTBA_TACHE_ID = trim($sheetdata[$i][28]);
          $TACHES = trim($sheetdata[$i][29]);
          $TACHES = str_replace("’","'", $TACHES);
          $TACHES = str_replace("'","\'", $TACHES);
          $RESULTATS_ATTENDUS_TACHES = trim($sheetdata[$i][30]);
          $RESULTATS_ATTENDUS_TACHES = str_replace("’","'", $RESULTATS_ATTENDUS_TACHES);
          $RESULTATS_ATTENDUS_TACHES = str_replace("'","\'", $RESULTATS_ATTENDUS_TACHES);
          $UNITE = trim($sheetdata[$i][31]);
          $UNITE = str_replace("’","'", $UNITE);
          $UNITE = str_replace("'","\'", $UNITE);
          $TOTAL_QUANTITE = trim($sheetdata[$i][32]);
          $QUANTITE_T1 = trim($sheetdata[$i][33]);
          $QUANTITE_T2 = trim($sheetdata[$i][34]);
          $QUANTITE_T3 = trim($sheetdata[$i][35]);
          $QUANTITE_T4 = trim($sheetdata[$i][36]);
          $COUT_UNITAIRE = trim($sheetdata[$i][37]);
          $BUDGET_T1 = trim($sheetdata[$i][38]);
          $BUDGET_T1=preg_replace('/[\.,\s]/', '', $BUDGET_T1);
          $BUDGET_T2 = trim($sheetdata[$i][39]);
          $BUDGET_T2=preg_replace('/[\.,\s]/', '', $BUDGET_T2);
          $BUDGET_T3 = trim($sheetdata[$i][40]);
          $BUDGET_T3=preg_replace('/[\.,\s]/', '', $BUDGET_T3);
          $BUDGET_T4 = trim($sheetdata[$i][41]);
          $BUDGET_T4=preg_replace('/[\.,\s]/', '', $BUDGET_T4);
          $BUDGET_ANNUEL = trim($sheetdata[$i][42]);
          $STRUCTURE_RESPONSABLE = trim($sheetdata[$i][43]);
          $CODE_GRANDE_MASSE = trim($sheetdata[$i][44]);
          $INTITULE_GRANDE_MASSE = trim($sheetdata[$i][45]);

          //get the INSTITUTION_ID from inst_institutions
          $requeteInst = "SELECT INSTITUTION_ID FROM inst_institutions WHERE CODE_INSTITUTION = '".$CODE_MINISTERE."'";
          $INSTITUTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteInst . '")')['INSTITUTION_ID'] ?? 0;

          //get the SOUS_TUTEL_ID from inst_institutions_ligne_budgetaire
          $requeteSousTut = "SELECT SOUS_TUTEL_ID FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE ='".$CODE_NOMENCLATURE_BUDGETAIRE."'";
          $SOUS_TUTEL_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteSousTut . '")')['SOUS_TUTEL_ID'] ?? 0;

          //get the ID_PILIER from pnd_pilier
          $PILIER = str_replace("’","'", $PILIER);
          $PILIER = str_replace("'","\'", $PILIER);
          $requetePilier = "SELECT ID_PILIER FROM pnd_pilier WHERE DESCR_PILIER = '".addslashes($PILIER)."'";
          $ID_PILIER = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePilier . '")')['ID_PILIER'] ?? 0;

          //get the OBJECTIF_VISION_ID from vision_objectif
          $OBJECTIF_DE_LA_VISION = str_replace("'","\'", $OBJECTIF_DE_LA_VISION);
          $requeteVision = "SELECT OBJECTIF_VISION_ID FROM vision_objectif WHERE DESC_OBJECTIF_VISION = '".addslashes($OBJECTIF_DE_LA_VISION)."'";
          $OBJECTIF_VISION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteVision . '")')['OBJECTIF_VISION_ID'] ?? 0;

          //get the AXE_PND_ID from vision_objectif
          $AXES_DU_PND_REVISE = str_replace("'","\'", $AXES_DU_PND_REVISE);
          $requeteAxe = "SELECT AXE_PND_ID FROM pnd_axe WHERE DESCR_AXE_PND = '".addslashes($AXES_DU_PND_REVISE)."'";
          $AXE_PND_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteAxe . '")')['AXE_PND_ID'] ?? 0;

          //get the PROGRAMME_ID from inst_institutions_programmes
          $requeteProg = "SELECT PROGRAMME_ID FROM inst_institutions_programmes WHERE CODE_PROGRAMME = '".addslashes($CODE_PROGRAMME)."'";
          $PROGRAMME_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteProg . '")')['PROGRAMME_ID'] ?? 0;

          //get the ACTION_ID from inst_institutions_actions
          $requeteAction = "SELECT ACTION_ID FROM inst_institutions_actions WHERE CODE_ACTION = '".addslashes($CODE_ACTION)."'";
          $ACTION_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteAction . '")')['ACTION_ID'] ?? 0;

          //get the PROGRAMME_PRIORITAIRE_ID from inst_institutions_programme_prioritaire
          $PROGRAMME_PRIORITAIRE = str_replace("'","\'", $PROGRAMME_PRIORITAIRE);
          $requeteProgPr = "SELECT PROGRAMME_PRIORITAIRE_ID FROM inst_institutions_programme_prioritaire WHERE DESC_PROGRAMME_PRIORITAIRE = '".addslashes($PROGRAMME_PRIORITAIRE)."'";
          $PROGRAMME_PRIORITAIRE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteProgPr . '")')['PROGRAMME_PRIORITAIRE_ID'] ?? 'NULL';

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

          //get the PAP_ACTIVITE_ID from pap_activites
          $ACTIVITES_PAP = str_replace("'","\'", $ACTIVITES_PAP);
          $requetePapActivite = "SELECT PAP_ACTIVITE_ID FROM pap_activites WHERE DESC_PAP_ACTIVITE = '".addslashes($ACTIVITES_PAP)."'";
          $PAP_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePapActivite . '")')['PAP_ACTIVITE_ID'] ?? 'NULL';

          //get the COSTAB_ACTIVITE_ID from costab_activites
          $ACTIVITES_COSTAB = str_replace("'","\'", $ACTIVITES_COSTAB);
          $requeteCostabActivite = "SELECT COSTAB_ACTIVITE_ID FROM costab_activites WHERE DESC_COSTAB_ACTIVITE = '".addslashes($ACTIVITES_COSTAB)."'";
          $COSTAB_ACTIVITE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteCostabActivite . '")')['COSTAB_ACTIVITE_ID'] ?? 'NULL';

          //get the INDICATEUR_PND_ID from pnd_indicateur
          $INDICATEURS_PND = str_replace("'","\'", $INDICATEURS_PND);
          $requetePnd = "SELECT INDICATEUR_PND_ID FROM pnd_indicateur WHERE DESC_INDICATEUR_PND = '".addslashes($INDICATEURS_PND)."'";
          $INDICATEUR_PND_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePnd . '")')['INDICATEUR_PND_ID'] ?? 'NULL';

          //get the STRUTURE_RESPONSABLE_TACHE_ID from struture_responsable_tache
          $STRUCTURE_RESPONSABLE = str_replace("'","\'", $STRUCTURE_RESPONSABLE);
          $requeteStrResp = "SELECT STRUTURE_RESPONSABLE_TACHE_ID FROM struture_responsable_tache WHERE DESC_STRUTURE_RESPONSABLE_TACHE = '".addslashes($STRUCTURE_RESPONSABLE)."'";
          $STRUTURE_RESPONSABLE_TACHE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteStrResp . '")')['STRUTURE_RESPONSABLE_TACHE_ID'] ?? 0;

          //get the STRUTURE_RESPONSABLE_TACHE_ID from struture_responsable_tache
          $requeteNomenclature = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE = '".addslashes($CODE_NOMENCLATURE_BUDGETAIRE)."'";
          $CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requeteNomenclature . '")')['CODE_NOMENCLATURE_BUDGETAIRE_ID'] ?? 0;

          $GRANDE_MASSE_ID  = $CODE_GRANDE_MASSE;

          $BUDGET_RESTANT_T1 = $BUDGET_T1;
          $BUDGET_RESTANT_T2 = $BUDGET_T2;
          $BUDGET_RESTANT_T3 = $BUDGET_T3;
          $BUDGET_RESTANT_T4 = $BUDGET_T4;

          $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();
          $IS_NOUVEAU=1;
          $IS_REVISE=1;

          $insertIntoTable='ptba_tache';
          $columsinsert="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID,PROGRAMME_PRIORITAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID,PAP_ACTIVITE_ID,COSTAB_ACTIVITE_ID,PND_INDICATEUR_ID,CODES_PROGRAMMATIQUE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_RESTANT_T1,BUDGET_T2,BUDGET_RESTANT_T2,BUDGET_T3,BUDGET_RESTANT_T3,BUDGET_T4,BUDGET_RESTANT_T4,BUDGET_ANNUEL,STRUTURE_RESPONSABLE_TACHE_ID,GRANDE_MASSE_ID,ANNEE_BUDGETAIRE_ID,IS_NOUVEAU,IS_REVISE";
          $datacolumsinsert = "{$INSTITUTION_ID},{$SOUS_TUTEL_ID},{$ID_PILIER},{$OBJECTIF_VISION_ID},{$AXE_PND_ID},{$PROGRAMME_ID},{$ACTION_ID},{$PROGRAMME_PRIORITAIRE_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID},'{$CODE_NOMENCLATURE_BUDGETAIRE}',{$ARTICLE_ID},{$SOUS_LITTERA_ID},{$DIVISION_ID},{$GROUPE_ID},{$CLASSE_ID},{$PAP_ACTIVITE_ID},{$COSTAB_ACTIVITE_ID},{$INDICATEUR_PND_ID},'{$CODES_PROGRAMMATIQUE}','{$TACHES}','{$RESULTATS_ATTENDUS_TACHES}','{$UNITE}','{$TOTAL_QUANTITE}','{$QUANTITE_T1}','{$QUANTITE_T2}','{$QUANTITE_T3}','{$QUANTITE_T4}','{$COUT_UNITAIRE}','{$BUDGET_T1}','{$BUDGET_RESTANT_T1}','{$BUDGET_T2}','{$BUDGET_RESTANT_T2}','{$BUDGET_T3}','{$BUDGET_RESTANT_T3}','{$BUDGET_T4}','{$BUDGET_RESTANT_T4}','{$BUDGET_ANNUEL}',{$STRUTURE_RESPONSABLE_TACHE_ID},{$GRANDE_MASSE_ID},{$ANNEE_BUDGETAIRE_ID},{$IS_NOUVEAU},{$IS_REVISE}";

          if ($PTBA_TACHE_ID>0)
          {
            $requetePtba = "SELECT PTBA_TACHE_ID FROM ptba_tache WHERE PTBA_TACHE_ID = ".$PTBA_TACHE_ID;
            $TacheExist = $this->ModelPs->getRequeteOne('CALL `getTable`("'.$requetePtba.'")');

            if(!empty($TacheExist['PTBA_TACHE_ID']))
            {
              $dataToUpdate="DESC_TACHE_AVANT_REVISION=DESC_TACHE, DESC_TACHE='".$TACHES."',QT1_AVANT_REVISION=QT1,QT2_AVANT_REVISION=QT2,QT3_AVANT_REVISION=QT3,QT4_AVANT_REVISION=QT4,QT1='".$QUANTITE_T1."',QT2='".$QUANTITE_T2."', QT3='".$QUANTITE_T3."', QT4='".$QUANTITE_T4."',BUDGET_RESTANT_T1_AVANT_REVISION=BUDGET_RESTANT_T1,BUDGET_RESTANT_T2_AVANT_REVISION=BUDGET_RESTANT_T2,BUDGET_RESTANT_T3_AVANT_REVISION=BUDGET_RESTANT_T3,BUDGET_RESTANT_T4_AVANT_REVISION=BUDGET_RESTANT_T4,BUDGET_RESTANT_T1='".$BUDGET_T1."',BUDGET_RESTANT_T2='".$BUDGET_T2."',BUDGET_RESTANT_T3='".$BUDGET_T3."',BUDGET_RESTANT_T4='".$BUDGET_T4."',BUDGET_T1_APRES_REVISION='".$BUDGET_T1."',BUDGET_T2_APRES_REVISION='".$BUDGET_T2."',BUDGET_T3_APRES_REVISION='".$BUDGET_T3."',BUDGET_T4_APRES_REVISION='".$BUDGET_T4."',IS_REVISE=1";
              $condition="PTBA_TACHE_ID=".$PTBA_TACHE_ID;
              $this->update_all_table($insertIntoTable,$dataToUpdate,$condition);
            }
            else
            {
              $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
            }
          }
          else
          {
            $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
          }
        }
      }

      return redirect('double_commande_new/Upload_Ptba_Revise/get_view');
    }
  }