<?php

namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Data_ptba extends BaseController
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

  public function index()
  {
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    $data=$this->urichk();
    return view('App\Modules\donnees\Views\Data_ptba_view',$data);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
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

  public function importfile()
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
        if(!empty($CODE_MINISTERE))
        {
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

          // ----------------------------------------------------------------------------------
          $INTITULE_MINISTERE=str_replace('\n',' ',$INTITULE_MINISTERE);
          $INTITULE_PROGRAMME=str_replace('\n',' ',$INTITULE_PROGRAMME);
          $OBJECTIF_PROGRAMME=str_replace('\n',' ',$OBJECTIF_PROGRAMME);
          $LIBELLE_ACTION=str_replace('\n',' ',$LIBELLE_ACTION);
          $OBJECTIF_ACTION=str_replace('\n',' ',$OBJECTIF_ACTION);
          $INTITULE_ARTICLE_ECONOMIQUE=str_replace('\n',' ',$INTITULE_ARTICLE_ECONOMIQUE);
          $INTITULE_NATURE_ECONOMIQUE=str_replace('\n',' ',$INTITULE_NATURE_ECONOMIQUE);
          $INTITULE_DIVISION_FONCTIONNELLE=str_replace('\n',' ',$INTITULE_DIVISION_FONCTIONNELLE);
          $INTITULE_GROUPE_FONCTIONNELLE=str_replace('\n',' ',$INTITULE_GROUPE_FONCTIONNELLE);
          $INTITULE_CLASSE_FONCTIONNELLE=str_replace('\n',' ',$INTITULE_CLASSE_FONCTIONNELLE);
          $ACTIVITES=str_replace('\n',' ',$ACTIVITES);
          $RESULTATS_ATTENDUS=str_replace('\n',' ',$RESULTATS_ATTENDUS);
          $UNITE=str_replace('\n',' ',$UNITE);
          $RESPONSABLE=str_replace('\n',' ',$RESPONSABLE);
          $GRANDE_MASSE_BP=str_replace('\n',' ',$GRANDE_MASSE_BP);
          $GRANDE_MASSE_BM=str_replace('\n',' ',$GRANDE_MASSE_BM);
          $INTITULE_DES_GRANDES_MASSES=str_replace('\n',' ',$INTITULE_DES_GRANDES_MASSES);
          $GRANDE_MASSE_BM1=str_replace('\n',' ',$GRANDE_MASSE_BM1);
          // ----------------------------------------------------------------------------------

          $insertIntoTable='ptba_bdd13';
          $columsinserte="CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1";

          $valuecolumsinserte="'".$CODE_MINISTERE."','".$INTITULE_MINISTERE."','".$CODE_PROGRAMME."','".$INTITULE_PROGRAMME."','".$OBJECTIF_PROGRAMME."','".$CODE_ACTION."','".$LIBELLE_ACTION."','".$OBJECTIF_ACTION."','".$CODE_NOMENCLATURE_BUDGETAIRE."','".$ARTICLE_ECONOMIQUE."','".$INTITULE_ARTICLE_ECONOMIQUE."','".$NATURE_ECONOMIQUE."','".$INTITULE_NATURE_ECONOMIQUE."','".$DIVISION_FONCTIONNELLE."','".$INTITULE_DIVISION_FONCTIONNELLE."','".$GROUPE_FONCTIONNELLE."','".$INTITULE_GROUPE_FONCTIONNELLE."','".$CLASSE_FONCTIONNELLE."','".$INTITULE_CLASSE_FONCTIONNELLE."','".$CODES_PROGRAMMATIQUE."','".$ACTIVITES."','".$RESULTATS_ATTENDUS."','".$UNITE."','".$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."','".$QT1."','".$QT2."','".$QT3."','".$QT4."','".$COUT_UNITAIRE_BIF."','".$T1."','".$T2."','".$T3."','".$T4."','".$PROGRAMMATION_FINANCIERE_BIF."','".$RESPONSABLE."','".$GRANDE_MASSE_BP."','".$GRANDE_MASSE_BM."','".$INTITULE_DES_GRANDES_MASSES."','".$GRANDE_MASSE_BM1."'";
          if(!empty($CODE_MINISTERE))
          {
            $PTBA_ID=$this->save_all_table($insertIntoTable,$columsinserte,$valuecolumsinserte);
          }
        }
      }
    }
    return redirect('donnees/Data_ptba');
  }
}
?>