<?php
namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Executionbudget_tdeux extends BaseController
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
    return view('App\Modules\donnees\Views\Executionbudget_tdeux_view',$data);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  public function getBindParmsLimit($columnselect, $table, $where, $orderby,$Limit)
  {
    $db = db_connect();
    $columnselect=str_replace("\'", "'", $columnselect);
    $table=str_replace("\'", "'", $table);
    $where=str_replace("\'", "'", $where);
    $orderby=str_replace("\'", "'", $orderby);
    $Limit=str_replace("\'", "'", $Limit);
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby),$db->escapeString($Limit)];
    $bindparams=str_replace('\"', '"', $bindparams);
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
    
    $annee=date('Y');
    $DATE_DEMANDE=date('Y-m-d H:i:s');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();
    $TRIMESTRE_ID=2;
    $spreadsheet=$reader->load($_FILES["UPLOAD_DOCUMENT"]["tmp_name"]);
    $sheetdata=$spreadsheet->getActiveSheet()->toArray();
    $sheetcount=count($sheetdata);
    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      if($sheetcount>1)
      {
        for($i=1; $i < $sheetcount; $i++)
        { 
          $IMPUTATION=trim($sheetdata[$i][0]);
          $LIBELLE=trim($sheetdata[$i][1]);
          $CREDIT_VOTE=trim($sheetdata[$i][2]);
          $TRANSFERTS_CREDITS=trim($sheetdata[$i][3]);
          $CREDIT_APRES_TRANSFERT=trim($sheetdata[$i][4]);
          $ENG_BUDGETAIRE=trim($sheetdata[$i][5]);
          $ENG_JURIDIQUE=trim($sheetdata[$i][6]);
          $LIQUIDATION=trim($sheetdata[$i][7]);
          $ORDONNANCEMENT=trim($sheetdata[$i][8]);
          $PAIEMENT=trim($sheetdata[$i][9]);
          $DECAISSEMENT=trim($sheetdata[$i][10]);

          $IMPUTATION=str_replace(" ","", $IMPUTATION);
          $IMPUTATION_NUMBER = strlen($IMPUTATION);
          $LIBELLE=str_replace("'"," ", $LIBELLE);
          if($IMPUTATION_NUMBER==26)
          {
            $CODE_INSTITUTION=substr($IMPUTATION,0,2);
            $CODE_SOUS_TUTEL=substr($IMPUTATION,4,3);
            $bind_parmsinst=$this->getBindParms("INSTITUTION_ID","inst_institutions","CODE_INSTITUTION='".$CODE_INSTITUTION."'",'INSTITUTION_ID ASC');
            $bind_parmsinst=str_replace("\'","'",$bind_parmsinst);
            $resultatinst=$this->ModelPs->getRequeteOne($callpsreq, $bind_parmsinst);
            if(!empty($resultatinst) && !empty($LIBELLE))
            {
              $CREDIT_VOTE=str_replace(" ","", $CREDIT_VOTE);
              $TRANSFERTS_CREDITS=str_replace(" ","", $TRANSFERTS_CREDITS);
              $CREDIT_APRES_TRANSFERT=str_replace(" ","", $CREDIT_APRES_TRANSFERT);
              $ENG_BUDGETAIRE=str_replace(" ","", $ENG_BUDGETAIRE);
              $ENG_JURIDIQUE=str_replace(" ","", $ENG_JURIDIQUE);
              $LIQUIDATION=str_replace(" ","", $LIQUIDATION);
              $ORDONNANCEMENT=str_replace(" ","", $ORDONNANCEMENT);
              $PAIEMENT=str_replace(" ","", $PAIEMENT);
              $DECAISSEMENT=str_replace(" ","", $DECAISSEMENT);
              $CREDIT_VOTE=str_replace("-","0", $CREDIT_VOTE);
              $TRANSFERTS_CREDITS=str_replace("-","0", $TRANSFERTS_CREDITS);
              $CREDIT_APRES_TRANSFERT=str_replace("-","0", $CREDIT_APRES_TRANSFERT);
              $ENG_BUDGETAIRE=str_replace("-","0", $ENG_BUDGETAIRE);
              $ENG_JURIDIQUE=str_replace("-","0", $ENG_JURIDIQUE);
              $LIQUIDATION=str_replace("-","0", $LIQUIDATION);
              $ORDONNANCEMENT=str_replace("-","0", $ORDONNANCEMENT);
              $PAIEMENT=str_replace("-","0", $PAIEMENT);
              $DECAISSEMENT=str_replace("-","0", $DECAISSEMENT);
              $CREDIT_VOTE=str_replace(",","", $CREDIT_VOTE);
              $TRANSFERTS_CREDITS=str_replace(",","", $TRANSFERTS_CREDITS);
              $CREDIT_APRES_TRANSFERT=str_replace(",","", $CREDIT_APRES_TRANSFERT);
              $ENG_BUDGETAIRE=str_replace(",","", $ENG_BUDGETAIRE);
              $ENG_JURIDIQUE=str_replace(",","", $ENG_JURIDIQUE);
              $LIQUIDATION=str_replace(",","", $LIQUIDATION);
              $ORDONNANCEMENT=str_replace(",","", $ORDONNANCEMENT);
              $PAIEMENT=str_replace(",","", $PAIEMENT);
              $DECAISSEMENT=str_replace(",","", $DECAISSEMENT);
              $IS_TRANSFERTS=0;
              if($TRANSFERTS_CREDITS>0)
              {
                $IS_TRANSFERTS=1;
              }
              $INSTITUTION_ID=$resultatinst['INSTITUTION_ID'];
              $bind_parmsinsttut=$this->getBindParms("SOUS_TUTEL_ID","inst_institutions_sous_tutel","INSTITUTION_ID=".$INSTITUTION_ID." AND CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."'",'SOUS_TUTEL_ID ASC');
              $bind_parmsinsttut=str_replace("\'","'",$bind_parmsinsttut);
              $resultatinsttut=$this->ModelPs->getRequeteOne($callpsreq, $bind_parmsinsttut);
              $SOUS_TUTEL_ID=!empty($resultatinsttut)?$resultatinsttut['SOUS_TUTEL_ID']:0;

              // Debut enregistrement execution budgetaire brut
              $insertIntoTable='execution_budgetaire_brut';
              $columsinsertbrut="IMPUTATION,LIBELLE,CREDIT_VOTE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,ANNEE_BUDGETAIRE_ID,DATE_DEMANDE,IS_TRANSFERTS,INSTITUTION_ID,SOUS_TUTEL_ID,TRIMESTRE_ID";
              $valuecolumsinsertbrut="'".$IMPUTATION."','".$LIBELLE."',".$CREDIT_VOTE.",".$TRANSFERTS_CREDITS.",".$CREDIT_APRES_TRANSFERT.",".$ENG_BUDGETAIRE.",".$ENG_JURIDIQUE.",".$LIQUIDATION.",".$ORDONNANCEMENT.",".$PAIEMENT.",".$DECAISSEMENT.",".$ANNEE_BUDGETAIRE_ID.",'".$DATE_DEMANDE."',".$IS_TRANSFERTS.",".$INSTITUTION_ID.",".$SOUS_TUTEL_ID.",".$TRIMESTRE_ID;
              $EXECUTION_BUDGETAIRE_BRUT_ID=$this->save_all_table($insertIntoTable,$columsinsertbrut,$valuecolumsinsertbrut);
              // Fin enregistrement execution budgetaire brut

              // Debut enregistrement execution budgetaire
              if(!empty($EXECUTION_BUDGETAIRE_BRUT_ID))
              {
                if($EXECUTION_BUDGETAIRE_BRUT_ID>0)
                {
                  $insertIntoTablee='execution_budgetaire';
                  $columsinserte="EXECUTION_BUDGETAIRE_BRUT_ID,IMPUTATION,LIBELLE,CREDIT_VOTE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,ANNEE_BUDGETAIRE_ID,DATE_DEMANDE,IS_TRANSFERTS,INSTITUTION_ID,SOUS_TUTEL_ID,TRIMESTRE_ID";
                  $valuecolumsinserte=$EXECUTION_BUDGETAIRE_BRUT_ID.",'".$IMPUTATION."','".$LIBELLE."',".$CREDIT_VOTE.",".$TRANSFERTS_CREDITS.",".$CREDIT_APRES_TRANSFERT.",".$ENG_BUDGETAIRE.",".$ENG_JURIDIQUE.",".$LIQUIDATION.",".$ORDONNANCEMENT.",".$PAIEMENT.",".$DECAISSEMENT.",".$ANNEE_BUDGETAIRE_ID.",'".$DATE_DEMANDE."',".$IS_TRANSFERTS.",".$INSTITUTION_ID.",".$SOUS_TUTEL_ID.",".$TRIMESTRE_ID;
                  $EXECUTION_BUDGETAIRE_ID=$this->save_all_table($insertIntoTablee,$columsinserte,$valuecolumsinserte);
                }
              }
              // Fin enregistrement execution budgetaire
            }
          }
        }
      }
    }
    return redirect('donnees/Executionbudget_tdeux');
  }
}
?>