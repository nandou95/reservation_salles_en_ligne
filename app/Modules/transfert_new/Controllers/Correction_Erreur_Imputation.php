<?php
/*
*MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Correction d'Erreur d'Imputation
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 09 10 2023
*/

namespace  App\Modules\transfert_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M'); 

class Correction_Erreur_Imputation extends BaseController
{
	protected $session;
	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

  //fait appel au formule insertion du correction dune mauvaise ligne budgataire
	public function add()
	{
		$data=$this->urichk();
    $session  = \Config\Services::session();

    $INSTITUTION_ID = session()->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		########### envoie de data dans le select du Ligne budgétaire d'exécution #############
    $sql_exec='SELECT EXECUTION_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ligne.INSTITUTION_ID ='.$INSTITUTION_ID.' AND IS_RACCROCHE=0 ';
    $data['exec_budg'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_exec . "')");

    return view('App\Modules\transfert_new\Views\Correction_Erreur_Imputation_Add_View',$data);
  }

  ///recuparation du montant voté a partir du id execution
  function getMontantRecevoirByEtatExecution()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $EXECUTION_BUDGETAIRE_ID  = $this->request->getPost('IMPUTATION');
    $bind_proc = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT', 'execution_budgetaire_new', 'EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID,'EXECUTION_BUDGETAIRE_ID ASC');
    $getMontant= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);
    ##################################################################################
    $sql_exec='SELECT ligne.INSTITUTION_ID, ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID.' ';
    $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");

    $CODE_MINISTERE = substr($exec_budg['IMPUTATION'], 0, 2);
    $INSTITUTION_ID = $exec_budg['INSTITUTION_ID'];
    $getcod = $this->getBindParms('DISTINCT ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID, ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','INSTITUTION_ID='.$INSTITUTION_ID.' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE!='.$exec_budg['IMPUTATION'].' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE NOT IN(SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID FROM execution_budgetaire_new WHERE IS_RACCROCHE=1) ', 'ligne.CODE_NOMENCLATURE_BUDGETAIRE  ASC');
    $code = $this->ModelPs->getRequete($callpsreq, $getcod);

    $html='<option value="">Sélectionner</option>';
    foreach ($code as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
    }
    ###################################################################################
    // sommet des données a partir du ligne budgetaire d'execution
    $sql_exec_tempo='SELECT SUM(MONTANT_REALISE) AS MONTANT_REALISE,SUM(MONTANT_REALISE_JURIDIQUE) AS MONTANT_REALISE_JURIDIQUE,SUM(MONTANT_REALISE_LIQUIDATION) AS MONTANT_REALISE_LIQUIDATION,SUM(MONTANT_REALISE_ORDONNANCEMENT) AS MONTANT_REALISE_ORDONNANCEMENT,SUM(MONTANT_REALISE_PAIEMENT) AS MONTANT_REALISE_PAIEMENT,SUM(MONTANT_REALISE_DECAISSEMENT) AS MONTANT_REALISE_DECAISSEMENT FROM execution_budgetaire_tempo WHERE 1 AND EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND USER_ID='.$USER_ID.' ';
    $exec_tempo = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec_tempo . "')");

    $output = array(
      "ENG_BUDGETAIRE" => $getMontant['ENG_BUDGETAIRE']-$exec_tempo['MONTANT_REALISE'],
      "ENG_JURIDIQUE" => $getMontant['ENG_JURIDIQUE']-$exec_tempo['MONTANT_REALISE_JURIDIQUE'],
      "LIQUIDATION" => $getMontant['LIQUIDATION']-$exec_tempo['MONTANT_REALISE_LIQUIDATION'],
      "ORDONNANCEMENT" => $getMontant['ORDONNANCEMENT']-$exec_tempo['MONTANT_REALISE_ORDONNANCEMENT'],
      "PAIEMENT" => $getMontant['PAIEMENT']-$exec_tempo['MONTANT_REALISE_PAIEMENT'],
      "DECAISSEMENT" => $getMontant['DECAISSEMENT']-$exec_tempo['MONTANT_REALISE_DECAISSEMENT'],
      'CODE_NOMENCLATURE_BUDGETAIRE'=> $html
    );
    return $this->response->setJSON($output);
  }

  //verification de l'existance dune ligne budgetaire pour afficher ou non l'input libelle
  public function getLibelle($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $CODE_NOMENCLATURE_BUDGETAIRE  = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');

    $sql='SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ligne.CODE_NOMENCLATURE_BUDGETAIRE="'.$CODE_NOMENCLATURE_BUDGETAIRE.'" AND IS_RACCROCHE=0 ORDER BY EXECUTION_BUDGETAIRE_ID  DESC  ';
    $data_sql = $this->ModelPs->getRequeteOne("CALL `getTable`('".$sql."')");

    if (!empty($data_sql))
    {
      $status = 1;
    }
    else
    {
      $status = 0;
    }

    $output = array("status" => $status);
    return $this->response->setJSON($output);
  }

  //afficher des information enregistrer emporeraiment
  public function liste_tempo($value='')
  {
    $session  = \Config\Services::session();
    $data=$this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if(empty($USER_ID))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    ########### get data  tempo_correction_imputation #############
    $sql='SELECT LIGNE_BUDGETAIRE_EXECUTION, LIGNE_BUDGETAIRE_PTBA, ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT, LIBELLE, TEMPO_CORRECTION_IMPUTATION_ID FROM tempo_correction_imputation WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_CORRECTION_IMPUTATION_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");
    $count_data = count($tempo);

    $html = '';
    $html.='<br>
    <table class="table table-bordered">
    <thead>
    <tr>
    <th>Ligne&nbsp;&nbsp;budgétaire&nbsp;&nbsp;d\'exécution</th>
    <th>Ligne&nbsp;&nbsp;budgétaire&nbsp;&nbsp;PTBA</th>
    <th>Engagement&nbsp;&nbsp;budgétaire</th>
    <th>Engagement&nbsp;&nbsp;juridique</th>
    <th>Liquidation</th>
    <th>Ordonnancement</th>
    <th>Paiement</th>
    <th>Décaissement</th>
    <th>Libelle</th>
    <th>Option</th>
    </tr>
    </thead>
    <body>';

    if($count_data==0)
    {
      $status = 0;
    }
    else
    {
      $status = 1;

      foreach ($tempo as $key) {

        $sql_exec='SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$key->LIGNE_BUDGETAIRE_EXECUTION.' ';
        $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");

        $html.='<tr>
        <td>'.$exec_budg['IMPUTATION'].'</td>
        <td>'.$key->LIGNE_BUDGETAIRE_PTBA.'</td>
        <td>'.$key->ENG_BUDGETAIRE.'</td>
        <td>'.$key->ENG_JURIDIQUE.'</td>
        <td>'.$key->LIQUIDATION.'</td>
        <td>'.$key->ORDONNANCEMENT.'</td>
        <td>'.$key->PAIEMENT.'</td>
        <td>'.$key->DECAISSEMENT.'</td>
        <td>'.$key->LIBELLE.'</td>
        <td>
        <a onclick="removeToCart('.$key->TEMPO_CORRECTION_IMPUTATION_ID.')" href="javascript:;" style="color: red"><i class="fa fa-trash"></i> <span id="loading_delete'.$key->TEMPO_CORRECTION_IMPUTATION_ID.'"></span> <span id="message'.$key->TEMPO_CORRECTION_IMPUTATION_ID.'"></span></a> 
        </td>
        </tr>';
      }
    }
    $html.='</body>
    </table>';

    $output = array(
      'html'=>$html,
      'status'=>$status
    );
    return $this->response->setJSON($output);
  }

  //insert data in the table tempo de correction d'imputation 
  public function addToCart()
  {
    $session  = \Config\Services::session();

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if (empty($USER_ID))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    //ligne qui envoie
    $IMPUTATION=$this->request->getPost('IMPUTATION');
    $CODE_NOMENCLATURE_BUDGETAIRE=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');
    $ENG_BUDGETAIRE1=$this->request->getPost('ENG_BUDGETAIRE1');
    $ENG_JURIDIQUE1=$this->request->getPost('ENG_JURIDIQUE1');
    $LIQUIDATION1=$this->request->getPost('LIQUIDATION1');
    $ORDONNANCEMENT1=$this->request->getPost('ORDONNANCEMENT1');
    $PAIEMENT1=$this->request->getPost('PAIEMENT1');
    $DECAISSEMENT1=$this->request->getPost('DECAISSEMENT1');
    $LIBELLE=trim($this->request->getPost('LIBELLE'));

    $insertIntoTable='tempo_correction_imputation';

    #######################################
    $columsinsert="LIGNE_BUDGETAIRE_EXECUTION,LIGNE_BUDGETAIRE_PTBA,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,USER_ID,LIBELLE";
    $datacolumsinsert=$IMPUTATION.",".$CODE_NOMENCLATURE_BUDGETAIRE.",".$ENG_BUDGETAIRE1.",".$ENG_JURIDIQUE1.",".$LIQUIDATION1.",".$ORDONNANCEMENT1.",".$PAIEMENT1.",".$DECAISSEMENT1.",".$USER_ID.",'".$LIBELLE."' ";
    $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    $output = array('status'=>true);
    return $this->response->setJSON($output);
  }

  function removeToCart()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();  

    $TEMPO_CORRECTION_IMPUTATION_ID=$this->request->getPost('TEMPO_CORRECTION_IMPUTATION_ID');

    $insertIntoTable='tempo_correction_imputation';
    $critere ="TEMPO_CORRECTION_IMPUTATION_ID=".$TEMPO_CORRECTION_IMPUTATION_ID;
    $deleteparams =[$db->escapeString($insertIntoTable),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);

    $output = array('status'=>true);
    return $this->response->setJSON($output);
  }

  /* Debut Gestion update table de la demande detail*/
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /* Fin Gestion update table de la demande detail*/

  /* Debut Gestion insertion */
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

  // enregistrement des donnes dans la table histo transfert
  public function send_data($value='')
  {
  	$data=$this->urichk();
    $db = db_connect();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $INSTITUTION_ID = session()->get("SESSION_SUIVIE_PTBA_INSTITUTION_ID");

    if (empty($USER_ID))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    $sql='SELECT ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT, LIGNE_BUDGETAIRE_EXECUTION, LIGNE_BUDGETAIRE_PTBA, USER_ID, LIBELLE, TEMPO_CORRECTION_IMPUTATION_ID FROM  tempo_correction_imputation WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_CORRECTION_IMPUTATION_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");

    $table='execution_budgetaire_new';
    $insertIntoTable4='execution_budgetaire_brut_new';

    foreach ($tempo as $key => $value)
    {
      // get id dans la table execution via code budgetaire d'execution 
      $sql5='SELECT ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT, EXECUTION_BUDGETAIRE_ID FROM  execution_budgetaire_new WHERE 1 AND IMPUTATION="'.$value->LIGNE_BUDGETAIRE_PTBA.'" AND IS_RACCROCHE=0 ORDER BY EXECUTION_BUDGETAIRE_ID  DESC  ';
      $data_sql = $this->ModelPs->getRequeteOne("CALL `getTable`('".$sql5."')");

      //si une fois id de la ligne budgetaire est trouvé on fait update dans la table 
      if (!empty($data_sql))
      {
        $ENG_BUDGETAIRE = $data_sql['ENG_BUDGETAIRE']+$value->ENG_BUDGETAIRE;
        $ENG_JURIDIQUE = $data_sql['ENG_JURIDIQUE']+$value->ENG_JURIDIQUE;
        $LIQUIDATION = $data_sql['LIQUIDATION']+$value->LIQUIDATION;
        $ORDONNANCEMENT = $data_sql['ORDONNANCEMENT']+$value->ORDONNANCEMENT;
        $PAIEMENT = $data_sql['PAIEMENT']+$value->PAIEMENT;
        $DECAISSEMENT = $data_sql['DECAISSEMENT']+$value->DECAISSEMENT;

        $where='EXECUTION_BUDGETAIRE_ID='.$data_sql['EXECUTION_BUDGETAIRE_ID'].' ';
        $data='ENG_BUDGETAIRE = '.$ENG_BUDGETAIRE.', ENG_JURIDIQUE = '.$ENG_JURIDIQUE.', LIQUIDATION = '.$LIQUIDATION.', ORDONNANCEMENT = '.$ORDONNANCEMENT.', PAIEMENT = '.$PAIEMENT.', DECAISSEMENT = '.$DECAISSEMENT.', IS_NEW=2 ';
        $this->update_all_table($table,$data,$where);
      }
      else
      {
        $sql_ptba='SELECT SUM(PROGRAMMATION_FINANCIERE_BIF) AS PROGRAMMATION_FINANCIERE_BIF,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID,ptba.PROGRAMME_ID,ptba.ACTION_ID,ptba.INSTITUTION_ID FROM ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$value->LIGNE_BUDGETAIRE_PTBA.'';
        $ptba = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_ptba . "')");

        $TRANSFERTS_CREDITS = 0;
        $EXERCICE_ID = 1;

        // insert in the table brute 
        $columsinsert4="IMPUTATION,LIBELLE,CREDIT_VOTE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,EXERCICE_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,INSTITUTION_ID,PROGRAMME_ID,ACTION_ID";
        $datacolumsinsert4= $value->LIGNE_BUDGETAIRE_PTBA.",'".$value->LIBELLE."',".$ptba['PROGRAMMATION_FINANCIERE_BIF'].",".$TRANSFERTS_CREDITS.",".$ptba['PROGRAMMATION_FINANCIERE_BIF'].",".$value->ENG_BUDGETAIRE.",".$value->ENG_JURIDIQUE.",".$value->LIQUIDATION.",".$value->ORDONNANCEMENT.",".$value->PAIEMENT.",".$value->DECAISSEMENT.",".$EXERCICE_ID.",".$ptba['CODE_NOMENCLATURE_BUDGETAIRE_ID'].",".$ptba['INSTITUTION_ID'].",".$ptba['PROGRAMME_ID'].",".$ptba['ACTION_ID']."";
        $EXECUTION_BUDGETAIRE_BRUT_ID = $this->save_all_table($insertIntoTable4,$columsinsert4,$datacolumsinsert4);
        ################################################################################
        if ($EXECUTION_BUDGETAIRE_BRUT_ID>0)
        {
          // insert dans la table execution budgetaire si c'est nouveau
          $columsinsert="EXECUTION_BUDGETAIRE_BRUT_ID,IMPUTATION,LIBELLE,CREDIT_VOTE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,EXERCICE_ID,IS_RACCROCHE,IS_TRANSFERTS,INSTITUTION_ID,IS_NEW";
          $datacolumsinsert= $EXECUTION_BUDGETAIRE_BRUT_ID.",".$value->LIGNE_BUDGETAIRE_PTBA.",'".$value->LIBELLE."',".$ptba['PROGRAMMATION_FINANCIERE_BIF'].",".$TRANSFERTS_CREDITS.",".$ptba['PROGRAMMATION_FINANCIERE_BIF'].",".$value->ENG_BUDGETAIRE.",".$value->ENG_JURIDIQUE.",".$value->LIQUIDATION.",".$value->ORDONNANCEMENT.",".$value->PAIEMENT.",".$value->DECAISSEMENT.",".$EXERCICE_ID.",0,0,".$INSTITUTION_ID.",1";
          $this->save_all_table($table,$columsinsert,$datacolumsinsert);
        }
      }

      $sql2='SELECT ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT FROM  execution_budgetaire_new WHERE 1 AND EXECUTION_BUDGETAIRE_ID='.$value->LIGNE_BUDGETAIRE_EXECUTION.' ORDER BY EXECUTION_BUDGETAIRE_ID  DESC  ';
      $data_sql2 = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql2 . "')");

      $ENG_BUDGETAIRE1 = $data_sql2['ENG_BUDGETAIRE']-$value->ENG_BUDGETAIRE;
      $ENG_JURIDIQUE1 = $data_sql2['ENG_JURIDIQUE']-$value->ENG_JURIDIQUE;
      $LIQUIDATION1 = $data_sql2['LIQUIDATION']-$value->LIQUIDATION;
      $ORDONNANCEMENT1 = $data_sql2['ORDONNANCEMENT']-$value->ORDONNANCEMENT;
      $PAIEMENT1 = $data_sql2['PAIEMENT']-$value->PAIEMENT;
      $DECAISSEMENT1 = $data_sql2['DECAISSEMENT']-$value->DECAISSEMENT;

      $where2='EXECUTION_BUDGETAIRE_ID='.$value->LIGNE_BUDGETAIRE_EXECUTION.' ';
      $data2='ENG_BUDGETAIRE = '.$ENG_BUDGETAIRE1.', ENG_JURIDIQUE = '.$ENG_JURIDIQUE1.', LIQUIDATION = '.$LIQUIDATION1.', ORDONNANCEMENT = '.$ORDONNANCEMENT1.', PAIEMENT = '.$PAIEMENT1.', DECAISSEMENT = '.$DECAISSEMENT1.' ';
      $this->update_all_table($table,$data2,$where2);
      ######################################################################################

      //insertion dans la table historique apres avoir faire insert dans la table exec
      $insertIntoTable2='historique_correction_imputation';

      $columsinsert2="LIGNE_BUDGETAIRE_EXECUTION,LIGNE_BUDGETAIRE_PTBA,USER_ID,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,LIBELLE";
      $datacolumsinsert2=$value->LIGNE_BUDGETAIRE_EXECUTION.",".$value->LIGNE_BUDGETAIRE_PTBA.",".$USER_ID.",".$value->ENG_BUDGETAIRE.",".$value->ENG_JURIDIQUE.",".$value->LIQUIDATION.",".$value->ORDONNANCEMENT.",".$value->PAIEMENT.",".$value->DECAISSEMENT.",'".$value->LIBELLE."' ";
      $this->save_all_table($insertIntoTable2,$columsinsert2,$datacolumsinsert2);

      #################################### delete from table tempo after insert data in the table histo
      $insertIntoTable3 = 'tempo_correction_imputation';
      $critere3 ="TEMPO_CORRECTION_IMPUTATION_ID=".$value->TEMPO_CORRECTION_IMPUTATION_ID;
      $deleteparams3 =[$db->escapeString($insertIntoTable3),$db->escapeString($critere3)];
      $deleteRequete3 = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete3, $deleteparams3);
      #################################################################################


      #################################### delete from table execution_budgetaire_tempo
      $sqlDelete='DELETE FROM  execution_budgetaire_tempo WHERE 1 AND EXECUTION_BUDGETAIRE_ID='.$value->LIGNE_BUDGETAIRE_EXECUTION.' AND USER_ID='.$value->USER_ID.'  ';
      $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sqlDelete . "')");
      #################################################################################
    }
    $data=['message' => "".lang('messages_lang.message_success').""];
    session()->setFlashdata('alert', $data);
    return redirect('transfert_new/Correction_Erreur_Imputation/add');
  }
}
?>