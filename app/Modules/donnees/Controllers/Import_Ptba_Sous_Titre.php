<?php

/**RUGAMBA Jean Vainqueur
*Titre: Importation des sous titres
*Numero de telephone: +257 66 33 43 25
*WhatsApp: +257 62 47 19 15
*Email pro: jean.vainqueur@mediabox.bi
*Date: 27 06 2024
**/


namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Import_Ptba_Sous_Titre extends BaseController
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
    return view('App\Modules\donnees\Views\Import_Ptba_Sous_Titre_View',$data);
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
        $CODE_INSTITUTION=trim($sheetdata[$i][0]);
        if(!empty($CODE_INSTITUTION))
        {
          //Récuperer les ids des institutions
           
          $getInstitutions = "SELECT INSTITUTION_ID,CODE_INSTITUTION FROM inst_institutions WHERE CODE_INSTITUTION = {$CODE_INSTITUTION} ORDER BY INSTITUTION_ID ASC";
    
          $getInstitutions = "CALL `getList`('{$getInstitutions}')";
          $Intitutions = $this->ModelPs->getRequeteOne($getInstitutions);
          if(!empty($Intitutions))
          {
            $INSTITUTION_ID = $Intitutions['INSTITUTION_ID'];

            $CODE_SOUS_TUTEL = trim($sheetdata[$i][1]);
            $parts = explode(' ', $CODE_SOUS_TUTEL, 2);
            $CODE_SOUS_TITRE = $parts[0];
            $DESCRIPTION_SOUS_TUTEL = $parts[1];

            $CODE_INSTITUTION_SOUS_TUTEL=$CODE_INSTITUTION.'00'.$CODE_SOUS_TITRE;
            // ----------------------------------------------------------------------------------
            $DESCRIPTION_SOUS_TUTEL=str_replace("'", "\\'",$DESCRIPTION_SOUS_TUTEL);
            $DESCRIPTION_SOUS_TUTEL=str_replace(["\n", "\r"],' ',$DESCRIPTION_SOUS_TUTEL);
            // ----------------------------------------------------------------------------------

            $insertIntoTable='inst_institutions_sous_tutel';
            $columsinserte="INSTITUTION_ID,CODE_INSTITUTION_SOUS_TUTEL,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL";
            $valuecolumsinserte = "{$INSTITUTION_ID}, '{$CODE_INSTITUTION_SOUS_TUTEL}', '{$CODE_SOUS_TITRE}', '{$DESCRIPTION_SOUS_TUTEL}'";
            if(!empty($CODE_INSTITUTION))
            {
              $SOUS_TUTEL_ID=$this->save_all_table($insertIntoTable,$columsinserte,$valuecolumsinserte);
            }
          } 
        }
      }
    }
    return redirect('donnees/Import_Ptba_Sous_Titre');
  }
}
?>