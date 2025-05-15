<?php
/**
* HABIMANA Nandou
* nandou@mediabox.bi
* +25769301985
* Gestion des donnees de classification
*/
namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Gestion_Data_Ptba extends BaseController
{
	protected $session;
  protected $ModelPs;
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db=db_connect();
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  // Affiche le view d'importation du ptba
  public function index()
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    $data=$this->urichk();
    return view('App\Modules\donnees\Views\Gestion_Data_Ptba_view',$data);
  }

  // Charge des donnees du PTBA
  public function charge_ptba()
  {
    $session = \Config\Services::session();
    $USER_ID=0;
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    $UPLOAD_DOCUMENT=$_FILES["UPLOAD_DOCUMENT"]["name"];
    $extension=pathinfo($UPLOAD_DOCUMENT,PATHINFO_EXTENSION);
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
    
    $spreadsheet=$reader->load($_FILES["UPLOAD_DOCUMENT"]["tmp_name"]);
    $sheetdata=$spreadsheet->getActiveSheet()->toArray();
    $sheetcount=count($sheetdata);
    if($sheetcount>1)
    {
      for($i=1; $i < $sheetcount; $i++)
      { 
        $CODE_MINISTERE=trim($sheetdata[$i][0]);
        $INTITULE_MINISTERE=trim($sheetdata[$i][1]);
        $CODE_PROGRAMME=trim($sheetdata[$i][2]);
        $INTITULE_PROGRAMME=trim($sheetdata[$i][3]);
        $OBJECTIF_PROGRAMME=trim($sheetdata[$i][4]);
        $CODE_ACTION=trim($sheetdata[$i][5]);
        $LIBELLE_ACTION=trim($sheetdata[$i][6]);
        $OBJECTIF_ACTION=trim($sheetdata[$i][7]);
        $CODE_NOMENCLATURE_BUDGETAIRE=trim($sheetdata[$i][8]);
        $ARTICLE_ECONOMIQUE=trim($sheetdata[$i][9]);
        $INTITULE_ARTICLE_ECONOMIQUE=trim($sheetdata[$i][10]);
        $NATURE_ECONOMIQUE=trim($sheetdata[$i][11]);
        $INTITULE_NATURE_ECONOMIQUE=trim($sheetdata[$i][12]);
        $DIVISION_FONCTIONNELLE=trim($sheetdata[$i][13]);
        $INTITULE_DIVISION_FONCTIONNELLE=trim($sheetdata[$i][14]);
        $GROUPE_FONCTIONNELLE=trim($sheetdata[$i][15]);
        $INTITULE_GROUPE_FONCTIONNELLE=trim($sheetdata[$i][16]);
        $CLASSE_FONCTIONNELLE=trim($sheetdata[$i][17]);
        $INTITULE_CLASSE_FONCTIONNELLE=trim($sheetdata[$i][18]);
        $CODES_PROGRAMMATIQUE=trim($sheetdata[$i][19]);
        $ACTIVITES=trim($sheetdata[$i][20]);
        $RESULTATS_ATTENDUS=trim($sheetdata[$i][21]);
        $UNITE=trim($sheetdata[$i][22]);
        $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE=trim($sheetdata[$i][23]);
        $QT1=trim($sheetdata[$i][24]);
        $QT2=trim($sheetdata[$i][25]);
        $QT3=trim($sheetdata[$i][26]);
        $QT4=trim($sheetdata[$i][27]);
        $COUT_UNITAIRE_BIF=trim($sheetdata[$i][28]);
        $T1=trim($sheetdata[$i][29]);
        $T2=trim($sheetdata[$i][30]);
        $T3=trim($sheetdata[$i][31]);
        $T4=trim($sheetdata[$i][32]);
        $PROGRAMMATION_FINANCIERE_BIF=trim($sheetdata[$i][33]);
        $RESPONSABLE=trim($sheetdata[$i][34]);
        $GRANDE_MASSE_BP=trim($sheetdata[$i][35]);
        $GRANDE_MASSE_BM=trim($sheetdata[$i][36]);
        $INTITULE_DES_GRANDES_MASSES=trim($sheetdata[$i][37]);
        $GRANDE_MASSE_BM1=trim($sheetdata[$i][38]);

        $CODE_MINISTERE=str_replace("'", "",$CODE_MINISTERE);
        $CODE_PROGRAMME=str_replace("'", "",$CODE_PROGRAMME);
        $CODE_ACTION=str_replace("'", "",$CODE_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'", "",$CODE_NOMENCLATURE_BUDGETAIRE);
        $ARTICLE_ECONOMIQUE=str_replace("'", "",$ARTICLE_ECONOMIQUE);
        $NATURE_ECONOMIQUE=str_replace("'", "",$NATURE_ECONOMIQUE);
        $DIVISION_FONCTIONNELLE=str_replace("'", "",$DIVISION_FONCTIONNELLE);
        $GROUPE_FONCTIONNELLE=str_replace("'", "",$GROUPE_FONCTIONNELLE);
        $CLASSE_FONCTIONNELLE=str_replace("'", "",$CLASSE_FONCTIONNELLE);
        $CODES_PROGRAMMATIQUE=str_replace("'", "",$CODES_PROGRAMMATIQUE);

        $INTITULE_MINISTERE=str_replace("'"," ",$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace("'"," ",$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace("'"," ",$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace("'"," ",$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace("'"," ",$OBJECTIF_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'"," ",$CODE_NOMENCLATURE_BUDGETAIRE);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace("'"," ",$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace("'"," ",$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace("'"," ",$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_CLASSE_FONCTIONNELLE);
        $ACTIVITES=str_replace("'"," ",$ACTIVITES);
        $RESULTATS_ATTENDUS=str_replace("'"," ",$RESULTATS_ATTENDUS);
        $UNITE=str_replace("'"," ",$UNITE);
        $RESPONSABLE=str_replace("'"," ",$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace("'"," ",$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace("'"," ",$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace("'"," ",$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace("'"," ",$GRANDE_MASSE_BM1);

        $INTITULE_MINISTERE=str_replace('"','',$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace('"','',$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace('"','',$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace('"','',$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace('"','',$OBJECTIF_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace('"','',$CODE_NOMENCLATURE_BUDGETAIRE);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace('"','',$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace('"','',$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace('"','',$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace('"','',$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace('"','',$INTITULE_CLASSE_FONCTIONNELLE);
        $ACTIVITES=str_replace('"','',$ACTIVITES);
        $RESULTATS_ATTENDUS=str_replace('"','',$RESULTATS_ATTENDUS);
        $UNITE=str_replace('"','',$UNITE);
        $RESPONSABLE=str_replace('"','',$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace('"','',$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace('"','',$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace('"','',$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace('"','',$GRANDE_MASSE_BM1);

        $insertIntoTable='ptba';
        $columsinserte="CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1";

        $valuecolumsinserte="'".$CODE_MINISTERE."','".$INTITULE_MINISTERE."','".$CODE_PROGRAMME."','".$INTITULE_PROGRAMME."','".$OBJECTIF_PROGRAMME."','".$CODE_ACTION."','".$LIBELLE_ACTION."','".$OBJECTIF_ACTION."','".$CODE_NOMENCLATURE_BUDGETAIRE."','".$ARTICLE_ECONOMIQUE."','".$INTITULE_ARTICLE_ECONOMIQUE."','".$NATURE_ECONOMIQUE."','".$INTITULE_NATURE_ECONOMIQUE."','".$DIVISION_FONCTIONNELLE."','".$INTITULE_DIVISION_FONCTIONNELLE."','".$GROUPE_FONCTIONNELLE."','".$INTITULE_GROUPE_FONCTIONNELLE."','".$CLASSE_FONCTIONNELLE."','".$INTITULE_CLASSE_FONCTIONNELLE."','".$CODES_PROGRAMMATIQUE."','".$ACTIVITES."','".$RESULTATS_ATTENDUS."','".$UNITE."','".$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."','".$QT1."','".$QT2."','".$QT3."','".$QT4."','".$COUT_UNITAIRE_BIF."','".$T1."','".$T2."','".$T3."','".$T4."','".$PROGRAMMATION_FINANCIERE_BIF."','".$RESPONSABLE."','".$GRANDE_MASSE_BP."','".$GRANDE_MASSE_BM."','".$INTITULE_DES_GRANDES_MASSES."','".$GRANDE_MASSE_BM1."'";
        if(!empty($CODE_MINISTERE))
        {
          $PTBA_ID=$this->save_all_table($insertIntoTable,$columsinserte,$valuecolumsinserte);
        }
      }
    }
    return redirect('donnees/Data_ptba');
  }

  // Afficher le view d'importation du pta en tenant compte les activites qui existe
  public function index_charge_compare()
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    $data=$this->urichk();
    return view('App\Modules\donnees\Views\Gestion_Data_Ptba_charge_compare_view',$data);
  }

  // Charge des donnees du PTBA en tenant compte les activites qui existe
  public function charge_compare_activite_ptba()
  {
    $table='ptba';
    $session = \Config\Services::session();
    $USER_ID=0;
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    $UPLOAD_DOCUMENT=$_FILES["UPLOAD_DOCUMENT"]["name"];
    $extension=pathinfo($UPLOAD_DOCUMENT,PATHINFO_EXTENSION);
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

    $callpsreq="CALL `getRequete`(?,?,?,?);";
    
    $spreadsheet=$reader->load($_FILES["UPLOAD_DOCUMENT"]["tmp_name"]);
    $sheetdata=$spreadsheet->getActiveSheet()->toArray();
    $sheetcount=count($sheetdata);
    $j=0;
    if($sheetcount>1)
    {
      for($i=1; $i < $sheetcount; $i++)
      {
        $CODE_MINISTERE=trim($sheetdata[$i][0]);
        $INTITULE_MINISTERE=trim($sheetdata[$i][1]);
        $CODE_PROGRAMME=trim($sheetdata[$i][2]);
        $INTITULE_PROGRAMME=trim($sheetdata[$i][3]);
        $OBJECTIF_PROGRAMME=trim($sheetdata[$i][4]);
        $CODE_ACTION=trim($sheetdata[$i][5]);
        $LIBELLE_ACTION=trim($sheetdata[$i][6]);
        $OBJECTIF_ACTION=trim($sheetdata[$i][7]);
        $CODE_NOMENCLATURE_BUDGETAIRE=trim($sheetdata[$i][8]);
        $ARTICLE_ECONOMIQUE=trim($sheetdata[$i][9]);
        $INTITULE_ARTICLE_ECONOMIQUE=trim($sheetdata[$i][10]);
        $NATURE_ECONOMIQUE=trim($sheetdata[$i][11]);
        $INTITULE_NATURE_ECONOMIQUE=trim($sheetdata[$i][12]);
        $DIVISION_FONCTIONNELLE=trim($sheetdata[$i][13]);
        $INTITULE_DIVISION_FONCTIONNELLE=trim($sheetdata[$i][14]);
        $GROUPE_FONCTIONNELLE=trim($sheetdata[$i][15]);
        $INTITULE_GROUPE_FONCTIONNELLE=trim($sheetdata[$i][16]);
        $CLASSE_FONCTIONNELLE=trim($sheetdata[$i][17]);
        $INTITULE_CLASSE_FONCTIONNELLE=trim($sheetdata[$i][18]);
        $CODES_PROGRAMMATIQUE=trim($sheetdata[$i][19]);
        $ACTIVITES=trim($sheetdata[$i][20]);
        $ACTIVITES_APOTROPHE=trim($sheetdata[$i][20]);
        $RESULTATS_ATTENDUS=trim($sheetdata[$i][21]);
        $UNITE=trim($sheetdata[$i][22]);
        $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE=trim($sheetdata[$i][23]);
        $QT1=trim($sheetdata[$i][24]);
        $QT2=trim($sheetdata[$i][25]);
        $QT3=trim($sheetdata[$i][26]);
        $QT4=trim($sheetdata[$i][27]);
        $COUT_UNITAIRE_BIF=trim($sheetdata[$i][28]);
        $T1=trim($sheetdata[$i][29]);
        $T2=trim($sheetdata[$i][30]);
        $T3=trim($sheetdata[$i][31]);
        $T4=trim($sheetdata[$i][32]);
        $PROGRAMMATION_FINANCIERE_BIF=trim($sheetdata[$i][33]);
        $RESPONSABLE=trim($sheetdata[$i][34]);
        $GRANDE_MASSE_BP=trim($sheetdata[$i][35]);
        $GRANDE_MASSE_BM=trim($sheetdata[$i][36]);
        $INTITULE_DES_GRANDES_MASSES=trim($sheetdata[$i][37]);
        $GRANDE_MASSE_BM1=trim($sheetdata[$i][38]);

        $CODE_MINISTERE=str_replace("'", "",$CODE_MINISTERE);
        $CODE_PROGRAMME=str_replace("'", "",$CODE_PROGRAMME);
        $CODE_ACTION=str_replace("'", "",$CODE_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'", "",$CODE_NOMENCLATURE_BUDGETAIRE);
        $ARTICLE_ECONOMIQUE=str_replace("'", "",$ARTICLE_ECONOMIQUE);
        $NATURE_ECONOMIQUE=str_replace("'", "",$NATURE_ECONOMIQUE);
        $DIVISION_FONCTIONNELLE=str_replace("'", "",$DIVISION_FONCTIONNELLE);
        $GROUPE_FONCTIONNELLE=str_replace("'", "",$GROUPE_FONCTIONNELLE);
        $CLASSE_FONCTIONNELLE=str_replace("'", "",$CLASSE_FONCTIONNELLE);
        $CODES_PROGRAMMATIQUE=str_replace("'", "",$CODES_PROGRAMMATIQUE);

        $INTITULE_MINISTERE=str_replace("'"," ",$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace("'"," ",$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace("'"," ",$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace("'"," ",$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace("'"," ",$OBJECTIF_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'"," ",$CODE_NOMENCLATURE_BUDGETAIRE);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace("'"," ",$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace("'"," ",$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace("'"," ",$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_CLASSE_FONCTIONNELLE);
        $ACTIVITES=str_replace("'"," ",$ACTIVITES);
        $RESULTATS_ATTENDUS=str_replace("'"," ",$RESULTATS_ATTENDUS);
        $UNITE=str_replace("'"," ",$UNITE);
        $RESPONSABLE=str_replace("'"," ",$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace("'"," ",$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace("'"," ",$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace("'"," ",$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace("'"," ",$GRANDE_MASSE_BM1);

        $INTITULE_MINISTERE=str_replace('"','',$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace('"','',$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace('"','',$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace('"','',$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace('"','',$OBJECTIF_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace('"','',$CODE_NOMENCLATURE_BUDGETAIRE);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace('"','',$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace('"','',$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace('"','',$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace('"','',$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace('"','',$INTITULE_CLASSE_FONCTIONNELLE);
        $ACTIVITES=str_replace('"','',$ACTIVITES);
        $ACTIVITES_APOTROPHE=str_replace('"','',$ACTIVITES_APOTROPHE);
        $ACTIVITES_APOTROPHE=str_replace('\n',' ',$ACTIVITES_APOTROPHE);
        $RESULTATS_ATTENDUS=str_replace('"','',$RESULTATS_ATTENDUS);
        $UNITE=str_replace('"','',$UNITE);
        $RESPONSABLE=str_replace('"','',$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace('"','',$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace('"','',$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace('"','',$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace('"','',$GRANDE_MASSE_BM1);

        if(!empty($CODE_MINISTERE))
        {
          $bindptba=$this->getBindParms('PTBA_ID','ptba','CODE_NOMENCLATURE_BUDGETAIRE="'.$CODE_NOMENCLATURE_BUDGETAIRE.'" AND (ACTIVITES="'.$ACTIVITES.'" OR ACTIVITES="'.$ACTIVITES_APOTROPHE.'")','PTBA_ID ASC');
          $bindptba=str_replace('\"','"',$bindptba);
          $bindptba=str_replace("\'","'",$bindptba);
          $ptba= $this->ModelPs->getRequeteOne($callpsreq,$bindptba);
          if(empty($ptba))
          {
            $j=$j+1;
            print_r($i." ".$CODE_NOMENCLATURE_BUDGETAIRE." ".$ACTIVITES."<br>");
          }

          $columsinserte="CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1";

          $valuecolumsinserte='"'.$CODE_MINISTERE.'","'.$INTITULE_MINISTERE.'","'.$CODE_PROGRAMME.'","'.$INTITULE_PROGRAMME.'","'.$OBJECTIF_PROGRAMME.'","'.$CODE_ACTION.'","'.$LIBELLE_ACTION.'","'.$OBJECTIF_ACTION.'","'.$CODE_NOMENCLATURE_BUDGETAIRE.'","'.$ARTICLE_ECONOMIQUE.'","'.$INTITULE_ARTICLE_ECONOMIQUE.'","'.$NATURE_ECONOMIQUE.'","'.$INTITULE_NATURE_ECONOMIQUE.'","'.$DIVISION_FONCTIONNELLE.'","'.$INTITULE_DIVISION_FONCTIONNELLE.'","'.$GROUPE_FONCTIONNELLE.'","'.$INTITULE_GROUPE_FONCTIONNELLE.'","'.$CLASSE_FONCTIONNELLE.'","'.$INTITULE_CLASSE_FONCTIONNELLE.'","'.$CODES_PROGRAMMATIQUE.'","'.$ACTIVITES_APOTROPHE.'","'.$RESULTATS_ATTENDUS.'","'.$UNITE.'","'.$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'","'.$QT1.'","'.$QT2.'","'.$QT3.'","'.$QT4.'","'.$COUT_UNITAIRE_BIF.'","'.$T1.'","'.$T2.'","'.$T3.'","'.$T4.'","'.$PROGRAMMATION_FINANCIERE_BIF.'","'.$RESPONSABLE.'","'.$GRANDE_MASSE_BP.'","'.$GRANDE_MASSE_BM.'","'.$INTITULE_DES_GRANDES_MASSES.'","'.$GRANDE_MASSE_BM1.'"';
          if(!empty($CODE_MINISTERE))
          {
            $PTBA_ID=$this->save_all_table('ptba_bdd13',$columsinserte,$valuecolumsinserte);
          }
        }
      }
    }
    print_r('<br>');
    print_r($j);
    print_r('ok');die();
  }

  public function compare_activite_ptba()
  {
    $i=0;
    $j=0;
    $table="ptba";
    $table_ptba_bdd="ptba_bdd13";
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind = $this->getBindParms('*','ptba_bdd13', 'TRAITE=0', 'PTBA_ID ASC');
    $activites= $this->ModelPs->getRequete($callpsreq, $bind);
    foreach($activites as $activite)
    {
      $ACTIVITES=str_replace("'"," ",$activite->ACTIVITES);
      $condiction='CODE_NOMENCLATURE_BUDGETAIRE="'.$activite->CODE_NOMENCLATURE_BUDGETAIRE.'" AND (ACTIVITES="'.$ACTIVITES.'" OR ACTIVITES="'.$activite->ACTIVITES.'")';
      $bindptba=$this->getBindParms('*','ptba',$condiction, 'PTBA_ID ASC');
      $bindptba=str_replace('\"','"',$bindptba);
      $bindptba=str_replace("\'","'",$bindptba);
      $ptba_activite= $this->ModelPs->getRequeteOne($callpsreq, $bindptba);
      if(!empty($ptba_activite))
      {
        $where_bdd='PTBA_ID='.$activite->PTBA_ID;
        $data_bdd='PTBA_AUTRE_ID='.$ptba_activite['PTBA_ID'].',TRAITE=1';

        $where='PTBA_ID='.$ptba_activite['PTBA_ID'];
        $data='PTBA_BDD_ID='.$activite->PTBA_ID.',TRAITE=1';
        $this->update_all_table($table,$data,$where);
        $this->update_all_table($table_ptba_bdd,$data_bdd,$where_bdd);
        $i++;
      }
      $j++;
    }
    $t=$j-$i;
    print_r("activite:".$j." et activite trouve:".$i." et activite non trouve:".$t);
  }

  // Afficher le view pour charge le ptba revise
  public function index_ptba_revise()
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    $data=$this->urichk();
    return view('App\Modules\donnees\Views\Gestion_Data_Ptba_Revise_view',$data);
  }

  // Charge les donnees du PTBA revise
  public function charge_ptba_revise()
  {
    $session = \Config\Services::session();
    $USER_ID=0;
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    $UPLOAD_DOCUMENT=$_FILES["UPLOAD_DOCUMENT"]["name"];
    $extension=pathinfo($UPLOAD_DOCUMENT,PATHINFO_EXTENSION);
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

    $insertIntoTable='ptba_revise';
    $columsinserte="CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1,TAUX_INFLATION_DEVALUATION_APPLIQUE,TAUX_INDEXE_AU_TAUX_INFLATION,IMPACT_BUDGETAIRE,OBSERVATION,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4";
    
    $spreadsheet=$reader->load($_FILES["UPLOAD_DOCUMENT"]["tmp_name"]);
    $sheetdata=$spreadsheet->getActiveSheet()->toArray();
    $sheetcount=count($sheetdata);
    if($sheetcount>1)
    {
      for($i=1; $i < $sheetcount; $i++)
      {
        $CODE_MINISTERE=trim($sheetdata[$i][0]);
        $INTITULE_MINISTERE=trim($sheetdata[$i][1]);
        $CODE_PROGRAMME=trim($sheetdata[$i][2]);
        $INTITULE_PROGRAMME=trim($sheetdata[$i][3]);
        $OBJECTIF_PROGRAMME=trim($sheetdata[$i][4]);
        $CODE_ACTION=trim($sheetdata[$i][5]);
        $LIBELLE_ACTION=trim($sheetdata[$i][6]);
        $OBJECTIF_ACTION=trim($sheetdata[$i][7]);
        if(empty($CODE_ACTION))
        {
          $CODE_ACTION=$CODE_PROGRAMME.'01';
          $LIBELLE_ACTION=$INTITULE_PROGRAMME;
        }
        $CODE_NOMENCLATURE_BUDGETAIRE=trim($sheetdata[$i][8]);
        $ARTICLE_ECONOMIQUE=trim($sheetdata[$i][9]);
        $INTITULE_ARTICLE_ECONOMIQUE=trim($sheetdata[$i][10]);
        $NATURE_ECONOMIQUE=trim($sheetdata[$i][11]);
        $INTITULE_NATURE_ECONOMIQUE=trim($sheetdata[$i][12]);
        $DIVISION_FONCTIONNELLE=trim($sheetdata[$i][13]);
        $INTITULE_DIVISION_FONCTIONNELLE=trim($sheetdata[$i][14]);
        $GROUPE_FONCTIONNELLE=trim($sheetdata[$i][15]);
        $INTITULE_GROUPE_FONCTIONNELLE=trim($sheetdata[$i][16]);
        $CLASSE_FONCTIONNELLE=trim($sheetdata[$i][17]);
        $INTITULE_CLASSE_FONCTIONNELLE=trim($sheetdata[$i][18]);
        $CODES_PROGRAMMATIQUE=trim($sheetdata[$i][19]);
        $ACTIVITES=trim($sheetdata[$i][20]);
        $RESULTATS_ATTENDUS=trim($sheetdata[$i][21]);
        $UNITE=trim($sheetdata[$i][22]);
        $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE=trim($sheetdata[$i][23]);
        $QT1=trim($sheetdata[$i][24]);
        $QT2=trim($sheetdata[$i][25]);
        $QT3=trim($sheetdata[$i][26]);
        $QT4=trim($sheetdata[$i][27]);
        $COUT_UNITAIRE_BIF=trim($sheetdata[$i][28]);
        $T1=trim($sheetdata[$i][29]);
        $T2=trim($sheetdata[$i][30]);
        $T3=trim($sheetdata[$i][31]);
        $T4=trim($sheetdata[$i][32]);
        $PROGRAMMATION_FINANCIERE_BIF=trim($sheetdata[$i][33]);
        $RESPONSABLE=trim($sheetdata[$i][34]);
        $GRANDE_MASSE_BP=trim($sheetdata[$i][35]);
        $GRANDE_MASSE_BM=trim($sheetdata[$i][36]);
        $INTITULE_DES_GRANDES_MASSES=trim($sheetdata[$i][37]);
        $GRANDE_MASSE_BM1=trim($sheetdata[$i][38]);
        $TAUX_INFLATION_DEVALUATION_APPLIQUE=trim($sheetdata[$i][39]);
        $TAUX_INDEXE_AU_TAUX_INFLATION=trim($sheetdata[$i][40]);
        $IMPACT_BUDGETAIRE=trim($sheetdata[$i][41]);
        $OBSERVATION=trim($sheetdata[$i][42]);

        $CODE_MINISTERE=str_replace("'", "",$CODE_MINISTERE);
        $CODE_PROGRAMME=str_replace("'", "",$CODE_PROGRAMME);
        $CODE_ACTION=str_replace("'", "",$CODE_ACTION);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'","",$CODE_NOMENCLATURE_BUDGETAIRE);
        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace('"','',$CODE_NOMENCLATURE_BUDGETAIRE);
        $ARTICLE_ECONOMIQUE=str_replace("'", "",$ARTICLE_ECONOMIQUE);
        $NATURE_ECONOMIQUE=str_replace("'", "",$NATURE_ECONOMIQUE);
        $DIVISION_FONCTIONNELLE=str_replace("'", "",$DIVISION_FONCTIONNELLE);
        $GROUPE_FONCTIONNELLE=str_replace("'", "",$GROUPE_FONCTIONNELLE);
        $CLASSE_FONCTIONNELLE=str_replace("'", "",$CLASSE_FONCTIONNELLE);
        $CODES_PROGRAMMATIQUE=str_replace("'", "",$CODES_PROGRAMMATIQUE);

        $INTITULE_MINISTERE=str_replace("'"," ",$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace("'"," ",$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace("'"," ",$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace("'"," ",$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace("'"," ",$OBJECTIF_ACTION);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace("'"," ",$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace("'"," ",$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace("'"," ",$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace("'"," ",$INTITULE_CLASSE_FONCTIONNELLE);
        $RESULTATS_ATTENDUS=str_replace("'"," ",$RESULTATS_ATTENDUS);
        $UNITE=str_replace("'"," ",$UNITE);
        $RESPONSABLE=str_replace("'"," ",$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace("'"," ",$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace("'"," ",$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace("'"," ",$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace("'"," ",$GRANDE_MASSE_BM1);
        $OBSERVATION=str_replace("'"," ",$OBSERVATION);

        $INTITULE_MINISTERE=str_replace('"','',$INTITULE_MINISTERE);
        $INTITULE_PROGRAMME=str_replace('"','',$INTITULE_PROGRAMME);
        $OBJECTIF_PROGRAMME=str_replace('"','',$OBJECTIF_PROGRAMME);
        $LIBELLE_ACTION=str_replace('"','',$LIBELLE_ACTION);
        $OBJECTIF_ACTION=str_replace('"','',$OBJECTIF_ACTION);
        $INTITULE_ARTICLE_ECONOMIQUE=str_replace('"','',$INTITULE_ARTICLE_ECONOMIQUE);
        $INTITULE_NATURE_ECONOMIQUE=str_replace('"','',$INTITULE_NATURE_ECONOMIQUE);
        $INTITULE_DIVISION_FONCTIONNELLE=str_replace('"','',$INTITULE_DIVISION_FONCTIONNELLE);
        $INTITULE_GROUPE_FONCTIONNELLE=str_replace('"','',$INTITULE_GROUPE_FONCTIONNELLE);
        $INTITULE_CLASSE_FONCTIONNELLE=str_replace('"','',$INTITULE_CLASSE_FONCTIONNELLE);
        $ACTIVITES=str_replace('"','',$ACTIVITES);
        $RESULTATS_ATTENDUS=str_replace('"','',$RESULTATS_ATTENDUS);
        $UNITE=str_replace('"','',$UNITE);
        $RESPONSABLE=str_replace('"','',$RESPONSABLE);
        $GRANDE_MASSE_BP=str_replace('"','',$GRANDE_MASSE_BP);
        $GRANDE_MASSE_BM=str_replace('"','',$GRANDE_MASSE_BM);
        $INTITULE_DES_GRANDES_MASSES=str_replace('"','',$INTITULE_DES_GRANDES_MASSES);
        $GRANDE_MASSE_BM1=str_replace('"','',$GRANDE_MASSE_BM1);
        $OBSERVATION=str_replace('"','',$OBSERVATION);

        // Debut Test si l'activite existe
        $IS_NEW_DATA_SORIDALITE=1;
        $valuecolumsinserte='"'.$CODE_MINISTERE.'","'.$INTITULE_MINISTERE.'","'.$CODE_PROGRAMME.'","'.$INTITULE_PROGRAMME.'","'.$OBJECTIF_PROGRAMME.'","'.$CODE_ACTION.'","'.$LIBELLE_ACTION.'","'.$OBJECTIF_ACTION.'","'.$CODE_NOMENCLATURE_BUDGETAIRE.'","'.$ARTICLE_ECONOMIQUE.'","'.$INTITULE_ARTICLE_ECONOMIQUE.'","'.$NATURE_ECONOMIQUE.'","'.$INTITULE_NATURE_ECONOMIQUE.'","'.$DIVISION_FONCTIONNELLE.'","'.$INTITULE_DIVISION_FONCTIONNELLE.'","'.$GROUPE_FONCTIONNELLE.'","'.$INTITULE_GROUPE_FONCTIONNELLE.'","'.$CLASSE_FONCTIONNELLE.'","'.$INTITULE_CLASSE_FONCTIONNELLE.'","'.$CODES_PROGRAMMATIQUE.'","'.$ACTIVITES.'","'.$RESULTATS_ATTENDUS.'","'.$UNITE.'","'.$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'","'.$QT1.'","'.$QT2.'","'.$QT3.'","'.$QT4.'","'.$COUT_UNITAIRE_BIF.'","'.$T1.'","'.$T2.'","'.$T3.'","'.$T4.'","'.$PROGRAMMATION_FINANCIERE_BIF.'","'.$RESPONSABLE.'","'.$GRANDE_MASSE_BP.'","'.$GRANDE_MASSE_BM.'","'.$INTITULE_DES_GRANDES_MASSES.'","'.$GRANDE_MASSE_BM1.'","'.$TAUX_INFLATION_DEVALUATION_APPLIQUE.'","'.$TAUX_INDEXE_AU_TAUX_INFLATION.'","'.$IMPACT_BUDGETAIRE.'","'.$OBSERVATION.'","'.$T1.'","'.$T2.'","'.$T3.'","'.$T4.'"';
        if(!empty($CODE_MINISTERE))
        {
          $PTBA_ID=$this->save_all_table($insertIntoTable,$columsinserte,$valuecolumsinserte);
        }
        // Fin Test si l'activite existe 
      }
    }
    print_r('OK');
    die();
  }

  // calculer le montant utilise
  public function calculer_montant_utilise()
  {
    $table='ptba';
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind=$this->getBindParms('PTBA_ID,T1,T2,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2',$table,'1','PTBA_ID ASC');
    $activites= $this->ModelPs->getRequete($callpsreq,$bind);
    foreach($activites as $activite)
    {
      $where='PTBA_ID='.$activite->PTBA_ID;
      $MONTAT_UTILISE_T1=floatval($activite->T1)-floatval($activite->MONTANT_RESTANT_T1);
      $MONTAT_UTILISE_T2=floatval($activite->T2)-floatval($activite->MONTANT_RESTANT_T2);
      $data='MONTAT_UTILISE_T1="'.$MONTAT_UTILISE_T1.'",MONTAT_UTILISE_T2="'.$MONTAT_UTILISE_T2.'"';
      $this->update_all_table($table,$data,$where);
      print_r("T1:".$activite->T1." && MR1:".$activite->MONTANT_RESTANT_T1." && R1:".$MONTAT_UTILISE_T1." et T2:".$activite->T2." && MR2:".$activite->MONTANT_RESTANT_T2." && R2:".$MONTAT_UTILISE_T2."<br><br>");
    }
    die();
  }

  // Mettre a jour les activites du ptba BDD13
  public function change_activite_ptba()
  {
    $table='ptba_bdd13';
    $table_ptba='ptba';
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind=$this->getBindParms('*',$table,'TRAITE=0','PTBA_ID ASC');
    $activites= $this->ModelPs->getRequete($callpsreq,$bind);
    $i=0;//nbr activites trouve dans ptba
    $j=0;//nbr activites non trouve dans ptba
    $PTBA_ID=0;
    foreach($activites as $activite)
    {
      $IS_NOUVEAU=0;
      $ACTIVITES=str_replace("'"," ", $activite->ACTIVITES);
      $condiction='CODE_NOMENCLATURE_BUDGETAIRE="'.$activite->CODE_NOMENCLATURE_BUDGETAIRE.'" AND (ACTIVITES="'.$activite->ACTIVITES.'" AND ACTIVITES="'.$ACTIVITES.'")';
      $bindptba=$this->getBindParms('*','ptba',$condiction,'PTBA_ID ASC');
      $bindptba=str_replace('\"','"',$bindptba);
      $ptba_activite= $this->ModelPs->getRequeteOne($callpsreq,$bindptba);
      if(!empty($ptba_activite))
      {
        $i++;
        $PTBA_ID=$ptba_activite['PTBA_ID'];
        $where='PTBA_ID='.$ptba_activite['PTBA_ID'];
        $data_update='ARTICLE_ECONOMIQUE="'.$activite->ARTICLE_ECONOMIQUE.'",INTITULE_ARTICLE_ECONOMIQUE="'.$activite->INTITULE_ARTICLE_ECONOMIQUE.'",NATURE_ECONOMIQUE="'.$activite->NATURE_ECONOMIQUE.'",INTITULE_NATURE_ECONOMIQUE="'.$activite->INTITULE_NATURE_ECONOMIQUE.'",DIVISION_FONCTIONNELLE="'.$activite->DIVISION_FONCTIONNELLE.'",INTITULE_DIVISION_FONCTIONNELLE="'.$activite->INTITULE_DIVISION_FONCTIONNELLE.'",GROUPE_FONCTIONNELLE="'.$activite->GROUPE_FONCTIONNELLE.'",INTITULE_GROUPE_FONCTIONNELLE="'.$activite->INTITULE_GROUPE_FONCTIONNELLE.'",CLASSE_FONCTIONNELLE="'.$activite->CLASSE_FONCTIONNELLE.'",INTITULE_CLASSE_FONCTIONNELLE="'.$activite->INTITULE_CLASSE_FONCTIONNELLE.'",CODES_PROGRAMMATIQUE="'.$activite->CODES_PROGRAMMATIQUE.'",RESULTATS_ATTENDUS="'.$activite->RESULTATS_ATTENDUS.'",UNITE="'.$activite->UNITE.'",QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE="'.$activite->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'",QT1="'.$activite->QT1.'",QT2="'.$activite->QT2.'",QT3="'.$activite->QT3.'",QT4="'.$activite->QT4.'",COUT_UNITAIRE_BIF="'.$activite->COUT_UNITAIRE_BIF.'",T1="'.$activite->T1.'",T2="'.$activite->T2.'",T3="'.$activite->T3.'",T4="'.$activite->T4.'",PROGRAMMATION_FINANCIERE_BIF="'.$activite->PROGRAMMATION_FINANCIERE_BIF.'",RESPONSABLE="'.$activite->RESPONSABLE.'",GRANDE_MASSE_BP="'.$activite->GRANDE_MASSE_BP.'",GRANDE_MASSE_BM="'.$activite->GRANDE_MASSE_BM.'",INTITULE_DES_GRANDES_MASSES="'.$activite->INTITULE_DES_GRANDES_MASSES.'",GRANDE_MASSE_BM1="'.$activite->GRANDE_MASSE_BM1.'",TRAITE=1,PTBA_AUTRE_ID='.$activite->PTBA_ID.',IS_NOUVEAU_ACIEN=1';
        $this->update_all_table('ptba',$data_update,$where);
      }
      else
      {
        $j++;
        $IS_NOUVEAU=1;
        // insert into ptba
        $columsinserte='CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,TRAITE,ANNEE_BUDGETAIRE_ID,IS_NOUVEAU_ACIEN';
        $data_insert='"'.$activite->CODE_MINISTERE.'","'.$activite->INTITULE_MINISTERE.'","'.$activite->CODE_PROGRAMME.'","'.$activite->INTITULE_PROGRAMME.'","'.$activite->OBJECTIF_PROGRAMME.'","'.$activite->CODE_ACTION.'","'.$activite->LIBELLE_ACTION.'","'.$activite->OBJECTIF_ACTION.'","'.$activite->CODE_NOMENCLATURE_BUDGETAIRE.'","'.$activite->ARTICLE_ECONOMIQUE.'","'.$activite->INTITULE_ARTICLE_ECONOMIQUE.'","'.$activite->NATURE_ECONOMIQUE.'","'.$activite->INTITULE_NATURE_ECONOMIQUE.'","'.$activite->DIVISION_FONCTIONNELLE.'","'.$activite->INTITULE_DIVISION_FONCTIONNELLE.'","'.$activite->GROUPE_FONCTIONNELLE.'","'.$activite->INTITULE_GROUPE_FONCTIONNELLE.'","'.$activite->CLASSE_FONCTIONNELLE.'","'.$activite->INTITULE_CLASSE_FONCTIONNELLE.'","'.$activite->CODES_PROGRAMMATIQUE.'","'.$activite->ACTIVITES.'","'.$activite->RESULTATS_ATTENDUS.'","'.$activite->UNITE.'","'.$activite->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'","'.$activite->QT1.'","'.$activite->QT2.'","'.$activite->QT3.'","'.$activite->QT4.'","'.$activite->COUT_UNITAIRE_BIF.'","'.$activite->T1.'","'.$activite->T2.'","'.$activite->T3.'","'.$activite->T4.'","'.$activite->PROGRAMMATION_FINANCIERE_BIF.'","'.$activite->RESPONSABLE.'","'.$activite->GRANDE_MASSE_BP.'","'.$activite->GRANDE_MASSE_BM.'","'.$activite->INTITULE_DES_GRANDES_MASSES.'","'.$activite->GRANDE_MASSE_BM1.'","'.$activite->T1.'","'.$activite->T2.'","'.$activite->T3.'","'.$activite->T4.'",1,1,2';
        $PTBA_ID=$this->save_all_table($table_ptba,$columsinserte,$data_insert);
      }
      $wher_bdd='PTBA_ID='.$activite->PTBA_ID;
      $data_bdd='TRAITE=1,PTBA_AUTRE_ID='.$PTBA_ID.',IS_NOUVEAU='.$IS_NOUVEAU;
      $this->update_all_table('ptba_bdd13',$data_bdd,$wher_bdd);
    }
    print_r("NBR activites trouve dans ptba:".$i." et NBR activites non trouve dans ptba:".$j);
    die();
  }

  // Amener les utilisateurs du v6 vers dev2 avec les memes mots de passe des utilisateurs
  public function chargement_user()
  {
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind=$this->getBindParms('*','user_users_v6','1','USER_ID ASC');
    $users= $this->ModelPs->getRequete($callpsreq,$bind);
    foreach($users as $key)
    {
      $USER_ID=0;
      // test si l'utilisateur existe
      $condiction='USER_NAME="'.$key->USER_NAME.'"';
      $binduser=$this->getBindParms('*','user_users',$condiction,'USER_ID ASC');
      $binduser=str_replace('\"','"',$binduser);
      $user= $this->ModelPs->getRequeteOne($callpsreq,$binduser);
      if(!empty($user))
      {
        // update
        $USER_ID=$user['USER_ID'];
        $whereupdate_user='USER_ID='.$USER_ID;
        $dataupdate='TELEPHONE1="'.$key->TELEPHONE1.'",PASSWORD="'.$key->PASSWORD.'",PROFIL_ID='.$key->PROFIL_ID;
        $this->update_all_table('user_users',$dataupdate,$whereupdate_user);
      }
      else
      {
        // insert
        $columsinserteuser='NOM,PRENOM,EMAIL,USER_NAME,TELEPHONE1,TELEPHONE2,PASSWORD,PROFIL_ID,IS_CONNECTED,IS_ACTIVE,DATE_ACTIVATION,DATE_INSERTION,REGISTER_USER_ID';
        $datainsertuser='"'.$key->NOM.'","'.$key->PRENOM.'","'.$key->EMAIL.'","'.$key->USER_NAME.'","'.$key->TELEPHONE1.'","'.$key->TELEPHONE2.'","'.$key->PASSWORD.'",'.$key->PROFIL_ID.','.$key->IS_CONNECTED.','.$key->IS_ACTIVE.',"'.$key->DATE_ACTIVATION.'","'.$key->DATE_INSERTION.'",'.$key->REGISTER_USER_ID;
        $USER_ID=$this->save_all_table('user_users',$columsinserteuser,$datainsertuser);
      }

      // recupere les affectations
      $bindaffection=$this->getBindParms('*','user_affectaion_v6','USER_ID='.$key->USER_ID,'USER_ID ASC');
      $affections= $this->ModelPs->getRequete($callpsreq,$bindaffection);
      if(!empty($affections))
      {
        $critere ="USER_ID=".$USER_ID;
        $deleteparams =['user_affectaion',$critere];
        $deleteRequete = "CALL `deleteData`(?,?);";
        $delete=$this->ModelPs->createUpdateDelete($deleteRequete,$deleteparams);
        foreach ($affections as $value)
        {
          // insert dans affectation
          $columsinserte='USER_ID,INSTITUTION_ID';
          $datainsert=$USER_ID.','.$value->INSTITUTION_ID;
          if(!empty($value->IS_SOUS_TUTEL))
          {
            $columsinserte='USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL';
            $datainsert=$USER_ID.','.$value->INSTITUTION_ID.','.$value->IS_SOUS_TUTEL;
            if(!empty($value->SOUS_TUTEL_ID))
            {
              $columsinserte='USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID';
              $datainsert=$USER_ID.','.$value->INSTITUTION_ID.','.$value->IS_SOUS_TUTEL.','.$value->SOUS_TUTEL_ID;
            }
          }
          $this->save_all_table('user_affectaion',$columsinserte,$datainsert);
        }
      }
    }

    print_r('OK');die();
  }

  // Gestion des activites du BDD13 et celle de l'ancien BDD13 du raccrochage
  public function save_activite_ptba()
  {
    $table='ptba';
    $tablebdd='ptba_bdd13';
    $tableracc='ptba_raccrochage';
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bindbdd=$this->getBindParms('*',$tablebdd,'TRAITE=0','PTBA_ID ASC');
    $bdd_activites= $this->ModelPs->getRequete($callpsreq,$bindbdd);
    $i=0;//nbr activites trouve dans ptba
    $j=0;//nbr activites non trouve dans ptba
    if(!empty($bdd_activites))
    {
      foreach($bdd_activites as $bdd_activite)
      {
        $PTBA_ID=0;
        $ACTIVITES=str_replace("'"," ", $bdd_activite->ACTIVITES);
        $condiction='TRAITE=0 AND CODE_NOMENCLATURE_BUDGETAIRE="'.$bdd_activite->CODE_NOMENCLATURE_BUDGETAIRE.'" AND (ACTIVITES="'.$bdd_activite->ACTIVITES.'" AND ACTIVITES="'.$ACTIVITES.'")';
        $bindptbaracc=$this->getBindParms('*',$tableracc,$condiction,'PTBA_ID ASC');
        $bindptbaracc=str_replace('\"','"',$bindptbaracc);
        $ptbaracc=$this->ModelPs->getRequeteOne($callpsreq,$bindptbaracc);
        if(!empty($ptbaracc))
        {
          // L'activite du nouveau BDD13 existe
          $i++;
          $columsinserte='PTBA_ID,PTBA_BDD_ID,CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,TRAITE';
          $data_insert=$ptbaracc['PTBA_ID'].','.$bdd_activite->PTBA_ID.',"'.$bdd_activite->CODE_MINISTERE.'","'.$bdd_activite->INTITULE_MINISTERE.'","'.$bdd_activite->CODE_PROGRAMME.'","'.$bdd_activite->INTITULE_PROGRAMME.'","'.$bdd_activite->OBJECTIF_PROGRAMME.'","'.$ptbaracc['CODE_ACTION'].'","'.$ptbaracc['LIBELLE_ACTION'].'","'.$bdd_activite->OBJECTIF_ACTION.'","'.$bdd_activite->CODE_NOMENCLATURE_BUDGETAIRE.'","'.$bdd_activite->ARTICLE_ECONOMIQUE.'","'.$bdd_activite->INTITULE_ARTICLE_ECONOMIQUE.'","'.$bdd_activite->NATURE_ECONOMIQUE.'","'.$bdd_activite->INTITULE_NATURE_ECONOMIQUE.'","'.$bdd_activite->DIVISION_FONCTIONNELLE.'","'.$bdd_activite->INTITULE_DIVISION_FONCTIONNELLE.'","'.$bdd_activite->GROUPE_FONCTIONNELLE.'","'.$bdd_activite->INTITULE_GROUPE_FONCTIONNELLE.'","'.$bdd_activite->CLASSE_FONCTIONNELLE.'","'.$bdd_activite->INTITULE_CLASSE_FONCTIONNELLE.'","'.$bdd_activite->CODES_PROGRAMMATIQUE.'","'.$bdd_activite->ACTIVITES.'","'.$bdd_activite->RESULTATS_ATTENDUS.'","'.$bdd_activite->UNITE.'","'.$bdd_activite->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'","'.$bdd_activite->QT1.'","'.$bdd_activite->QT2.'","'.$bdd_activite->QT3.'","'.$bdd_activite->QT4.'","'.$bdd_activite->COUT_UNITAIRE_BIF.'","'.$bdd_activite->T1.'","'.$bdd_activite->T2.'","'.$bdd_activite->T3.'","'.$bdd_activite->T4.'","'.$bdd_activite->PROGRAMMATION_FINANCIERE_BIF.'","'.$bdd_activite->RESPONSABLE.'","'.$bdd_activite->GRANDE_MASSE_BP.'","'.$bdd_activite->GRANDE_MASSE_BM.'","'.$bdd_activite->INTITULE_DES_GRANDES_MASSES.'","'.$bdd_activite->GRANDE_MASSE_BM1.'","'.$bdd_activite->MONTANT_RESTANT_T1.'","'.$bdd_activite->MONTANT_RESTANT_T2.'","'.$bdd_activite->MONTANT_RESTANT_T3.'","'.$bdd_activite->MONTANT_RESTANT_T4.'",1';
          $this->save_all_table($table,$columsinserte,$data_insert);

          // update ptba ptba_raccrochage
          $wher_racc='PTBA_ID='.$ptbaracc['PTBA_ID'];
          $data_raccrochage='PTBA_BDD_ID='.$bdd_activite->PTBA_ID.',TRAITE=1';
          $this->update_all_table('ptba_raccrochage',$data_raccrochage,$wher_racc);
          // update ptba ptba_bdd13
          $where_bdd='PTBA_ID='.$bdd_activite->PTBA_ID;
          $data_bdd='PTBA_RACCROCHAGE_ID='.$ptbaracc['PTBA_ID'].',TRAITE=1';
          $this->update_all_table('ptba_bdd13',$data_bdd,$where_bdd);
        }
        else
        {
          // L'activite du nouveau BDD13 n'existe pas
          $j++;
        }
      }
    }
    print_r("NBR des activites du nouveau BDD13 trouve:".$i." et non trouve:".$j);
    die();
  }

  // Affichage des donnees des activites ptba du raccrochage
  public function index_data_raccrochage()
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    $data=$this->urichk();
    $psgetrequete="CALL `getRequete`(?,?,?,?);";
    $bindparams=$this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions'] = $this->ModelPs->getRequete($psgetrequete,$bindparams);
    return view('App\Modules\donnees\Views\Gestion_Data_Ptba_Raccrochage_view',$data);
  }

  public function liste__data_raccrochage()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    $CODE_INSTITUTION=$this->request->getPost('CODE_INSTITUTION');
    $cri_CODE_INSTITUTION='';
    if(!empty($CODE_INSTITUTION))
    {
      $cri_CODE_INSTITUTION=' AND CODE_MINISTERE="'.$CODE_INSTITUTION.'"';
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critere = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array(1,'INTITULE_MINISTERE','INTITULE_PROGRAMME','LIBELLE_ACTION','CODE_NOMENCLATURE_BUDGETAIRE','ACTIVITES','QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE',1,'COUT_UNITAIRE_BIF',1,'PROGRAMMATION_FINANCIERE_BIF','RESPONSABLE',1);
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY PTBA_ID ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (INTITULE_MINISTERE LIKE "%'.$var_search.'%" OR INTITULE_PROGRAMME LIKE "%'.$var_search.'%" OR LIBELLE_ACTION LIKE "%'.$var_search.'%" OR CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR ACTIVITES LIKE "%'.$var_search.'%" OR  QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE LIKE "%'.$var_search.'%" OR COUT_UNITAIRE_BIF LIKE "%'.$var_search.'%" OR PROGRAMMATION_FINANCIERE_BIF LIKE "%'.$var_search.'%" OR RESPONSABLE LIKE "%'.$var_search.'%")') : '';

    // Condition pour la requête principale
    $conditions=$critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    // Condition pour la requête de filtre
    $conditionsfilter=$critere.' '.$search.' '.$group;
    $requetedebase='SELECT PTBA_ID,INTITULE_MINISTERE,INTITULE_PROGRAMME,LIBELLE_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE FROM ptba_raccrochage WHERE IS_NOUVEAU=0'.$cri_CODE_INSTITUTION;

    $requetedebases=$requetedebase.' '.$conditions;
    $requetedebasefilter=$requetedebase.' '.$conditionsfilter;

    $query_secondaire="CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data=array();
    $u=1;
    foreach ($fetch_data as $row)
    {
      $sub_array=array();
      $sub_array[]=$u++;
      $sub_array[]=$row->INTITULE_MINISTERE;
      $sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE ;
      $sub_array[]=$row->ACTIVITES;
      $sub_array[]=$row->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE;
      $sub_array[]="QT1: ".number_format($row->QT1,'2',',',' ').'<br>QT2: '.number_format($row->QT2,'2',',',' ').'<br>QT3: '.number_format($row->QT3,'2',',',' ').'<br>QT4: '.number_format($row->QT4,'2',',',' ');
      $sub_array[]=$row->COUT_UNITAIRE_BIF;
      $sub_array[]="T1: ".number_format($row->T1,'2',',',' ').'<br>T2: '.number_format($row->T2,'2',',',' ').'<br>T3: '.number_format($row->T3,'2',',',' ').'<br>T4: '.number_format($row->T4,'2',',',' ');
      $sub_array[]=number_format($row->PROGRAMMATION_FINANCIERE_BIF,'2',',',' ');
      $sub_array[]=$row->RESPONSABLE;
      $sub_array[]='<a href="'.base_url().'/donnees/Gestion_Data_Ptba/get_activite/'.$row->PTBA_ID.'" class="btn btn-primary">Changer</a>';
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
    $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  function get_activite($PTBA_ID)
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    $data=$this->urichk();
    $psgetrequete="CALL `getRequete`(?,?,?,?);";
    $bindparams=$this->getBindParms('*','ptba_raccrochage','PTBA_ID='.$PTBA_ID,'PTBA_ID ASC');
    $data['institution'] = $this->ModelPs->getRequeteOne($psgetrequete,$bindparams);
    return view('App\Modules\donnees\Views\Gestion_Data_Ptba_Raccrochage_activite_view',$data);
  }

  public function save_data_raccrochage($PTBA_BDD_ID,$PTBA_RACCROCHAGE_ID)
  {
    $PTBA_BDD_ID=$this->request->getPost('PTBA_BDD_ID');
    $PTBA_RACCHROCHAGE_ID=$this->request->getPost('PTBA_RACCHROCHAGE_ID');

    $table='ptba';
    $tablebdd='ptba_bdd13';
    $tableracc='ptba_raccrochage';

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bindbdd=$this->getBindParms('*',$tablebdd,'PTBA_ID='.$PTBA_BDD_ID,'PTBA_ID ASC');
    $bdd_activites= $this->ModelPs->getRequeteOne($callpsreq,$bindbdd);
    if(!empty($bdd_activites))
    {
      $condiction='PTBA_ID='.$PTBA_RACCHROCHAGE_ID;
      $bindptbaracc=$this->getBindParms('*',$tableracc,$condiction,'PTBA_ID ASC');
      $ptbaracc=$this->ModelPs->getRequeteOne($callpsreq,$bindptbaracc);

      if(!empty($ptbaracc))
      {
        // L'activite du nouveau BDD13 existe
        $data_insert='PTBA_ID='.$ptbaracc['PTBA_ID'].',PTBA_BDD_ID'.$bdd_activite['PTBA_ID'].',CODE_MINISTERE="'.$bdd_activite['CODE_MINISTERE'].'",INTITULE_MINISTERE="'.$bdd_activite['INTITULE_MINISTERE'].'",CODE_PROGRAMME="'.$bdd_activite['CODE_PROGRAMME'].'",INTITULE_PROGRAMME="'.$bdd_activite['INTITULE_PROGRAMME'].'",OBJECTIF_PROGRAMME="'.$bdd_activite['OBJECTIF_PROGRAMME'].'",CODE_ACTION="'.$ptbaracc['CODE_ACTION'].'",LIBELLE_ACTION="'.$ptbaracc['LIBELLE_ACTION'].'",OBJECTIF_ACTION="'.$bdd_activite['OBJECTIF_ACTION'].'",CODE_NOMENCLATURE_BUDGETAIRE="'.$bdd_activite['CODE_NOMENCLATURE_BUDGETAIRE'].'",ARTICLE_ECONOMIQUE="'.$bdd_activite['ARTICLE_ECONOMIQUE'].'",INTITULE_ARTICLE_ECONOMIQUE="'.$bdd_activite['INTITULE_ARTICLE_ECONOMIQUE'].'",NATURE_ECONOMIQUE="'.$bdd_activite['NATURE_ECONOMIQUE'].'",INTITULE_NATURE_ECONOMIQUE="'.$bdd_activite['INTITULE_NATURE_ECONOMIQUE'].'",DIVISION_FONCTIONNELLE="'.$bdd_activite['DIVISION_FONCTIONNELLE'].'",INTITULE_DIVISION_FONCTIONNELLE="'.$bdd_activite['INTITULE_DIVISION_FONCTIONNELLE'].'",GROUPE_FONCTIONNELLE="'.$bdd_activite['GROUPE_FONCTIONNELLE'].'",INTITULE_GROUPE_FONCTIONNELLE="'.$bdd_activite['INTITULE_GROUPE_FONCTIONNELLE'].'",CLASSE_FONCTIONNELLE="'.$bdd_activite['CLASSE_FONCTIONNELLE'].'",INTITULE_CLASSE_FONCTIONNELLE="'.$bdd_activite['INTITULE_CLASSE_FONCTIONNELLE'].'",CODES_PROGRAMMATIQUE="'.$bdd_activite['CODES_PROGRAMMATIQUE'].'",ACTIVITES="'.$bdd_activite['ACTIVITES'].'",RESULTATS_ATTENDUS="'.$bdd_activite['RESULTATS_ATTENDUS'].'",UNITE="'.$bdd_activite['UNITE'].'",QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE="'.$bdd_activite['QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE'].'",QT1="'.$bdd_activite['QT1'].'",QT2="'.$bdd_activite['QT2'].'",QT3="'.$bdd_activite['QT3'].'",QT4="'.$bdd_activite['QT4'].'",COUT_UNITAIRE_BIF="'.$bdd_activite['COUT_UNITAIRE_BIF'].'",T1="'.$bdd_activite['T1'].'",T2="'.$bdd_activite['T2'].'",T3="'.$bdd_activite['T3'].'",T4="'.$bdd_activite['T4'].'",PROGRAMMATION_FINANCIERE_BIF="'.$bdd_activite['PROGRAMMATION_FINANCIERE_BIF'].'",RESPONSABLE="'.$bdd_activite['RESPONSABLE'].'",GRANDE_MASSE_BP="'.$bdd_activite['GRANDE_MASSE_BP'].'",GRANDE_MASSE_BM="'.$bdd_activite['GRANDE_MASSE_BM'].'",INTITULE_DES_GRANDES_MASSES="'.$bdd_activite['INTITULE_DES_GRANDES_MASSES'].'",GRANDE_MASSE_BM1="'.$bdd_activite['GRANDE_MASSE_BM1'].'",MONTANT_RESTANT_T1="'.$bdd_activite['MONTANT_RESTANT_T1'].'",MONTANT_RESTANT_T2="'.$bdd_activite['MONTANT_RESTANT_T2'].'",MONTANT_RESTANT_T3="'.$bdd_activite['MONTANT_RESTANT_T3'].'",MONTANT_RESTANT_T4="'.$bdd_activite['MONTANT_RESTANT_T4'].'",TRAITE=1';
        $this->save_all_table($table,$columsinserte,$data_insert);

          // update ptba ptba_raccrochage
        $data_raccrochage='PTBA_BDD_ID='.$PTBA_BDD_ID.',TRAITE=1';
        $this->update_all_table('ptba_raccrochage',$data_raccrochage,$condiction);
          // update ptba ptba_bdd13
        $where_bdd='PTBA_ID='.$PTBA_BDD_ID;
        $data_bdd='PTBA_RACCROCHAGE_ID='.$PTBA_RACCHROCHAGE_ID.',TRAITE=1';
        $this->update_all_table('ptba_bdd13',$data_bdd,$where_bdd);
      }
    }
    return redirect('donnees/Gestion_Data_Ptba/index_data_raccrochage');
  }
}
?>