<?php
/*
*MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Transfert d'Incrementation
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

class Transfert_Incrementation extends BaseController
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

  // Recuperation et envoie des données dans le vieu via le id code exec de la table execution_budgetaire
	public function getOne($id)
	{
		$data=$this->urichk();
    $session  = \Config\Services::session();

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";

		########### get data execution budgetaire #############
    $sql_exec='SELECT EXECUTION_BUDGETAIRE_ID, TRANSFERTS_CREDITS,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$id.' ';
    $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");
    $data['exec_budg'] = $exec_budg;

    //get data institution
    $institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID','inst_institutions','1','DESCRIPTION_INSTITUTION ASC');
    $data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);

		//get activite
    $getcodeactivite = $this->getBindParms('ACTIVITES,PTBA_ID','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$exec_budg['IMPUTATION'].' ', 'ACTIVITES  ASC');
    $data['activites'] = $this->ModelPs->getRequete($psgetrequete, $getcodeactivite);

    //tranches
    $gettranches = $this->getBindParms('DESCRIPTION_TRANCHE,TRANCHE_ID ', 'op_tranches', '1', 'TRANCHE_ID ASC');
    $data['tranches'] = $this->ModelPs->getRequete($psgetrequete, $gettranches);

    //observation
    $getobservation = $this->getBindParms('DESC_OBSERVATION_FINANCIER,OBSERVATION_FINANCIER_ID ', 'observation_transfert_financier', '1', 'OBSERVATION_FINANCIER_ID ASC');
    $data['observation'] = $this->ModelPs->getRequete($psgetrequete, $getobservation);
    return view('App\Modules\transfert_new\Views\Transfert_Incrementation_Add_View',$data);
  }

	//recuparation de code budgetaire a partir de id institution
  function get_code()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_proc = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION','execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','INSTITUTION_ID ='.$INSTITUTION_ID,'INSTITUTION_ID  ASC');
    $code_Buget= $this->ModelPs->getRequete($callpsreq, $bind_proc);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_Buget as $key)
    {
      $html.='<option value="'.$key->IMPUTATION.'">'.$key->IMPUTATION.'</option>';
    }

    $output = array("CODE_NOMENCLATURE_BUDGETAIRE"=>$html);
    return $this->response->setJSON($output);
  }

	///recuparation des activités a partir de code budgetaire
  function get_activitesByCode()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $CODE_NOMENCLATURE_BUDGETAIRE = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getcodeactivite = $this->getBindParms('ACTIVITES,PTBA_ID','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE="'.$CODE_NOMENCLATURE_BUDGETAIRE.'"','ACTIVITES  ASC');
    $getcodeactivite=str_replace('\"','"',$getcodeactivite);
    $code_activites = $this->ModelPs->getRequete($callpsreq,$getcodeactivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_activites as $key)
    {
      $html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
    }

    $output = array("PTBA_ID" => $html);
    return $this->response->setJSON($output);
  }

	///recuparation du montant voté a partir d'une activite
  function get_MontantVoteByActivite()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $PTBA_ID = $this->request->getPost('PTBA_ID');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $bind_proc = $this->getBindParms('PROGRAMMATION_FINANCIERE_BIF', 'ptba', 'PTBA_ID ='.$PTBA_ID,'PTBA_ID  ASC');
    $montant_vote= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    $output = array("MONTANT_VOTE" => $montant_vote['PROGRAMMATION_FINANCIERE_BIF']);
    return $this->response->setJSON($output);
  }

	// recuparation du montant voté a partir d'une activite
  function get_MontantVoteByActivite2()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);"; 

    $ACTIVITES = $this->request->getPost('ACTIVITES');

    $bind_proc = $this->getBindParms('PROGRAMMATION_FINANCIERE_BIF','ptba','PTBA_ID ='.$ACTIVITES,'PTBA_ID  ASC');
    $montant_vote= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    $output = array("MONTANT_VOTE" => $montant_vote['PROGRAMMATION_FINANCIERE_BIF']);
    return $this->response->setJSON($output);
  }

  //recuperation de montany by trimestre
  public function getMontantAnnuel($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $PTBA_ID = $this->request->getPost('PTBA_ID');
    $TRANCHE_ID = $this->request->getPost('TRANCHE_ID');

    $bind_proc = $this->getBindParms('MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF', 'ptba', 'PTBA_ID ='.$PTBA_ID,'PTBA_ID  ASC');
    $montant_info= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    if($TRANCHE_ID==5)
    {
      $output = array(
        "MONTANT_TRANSFERT" => $montant_info['PROGRAMMATION_FINANCIERE_BIF'],
        "MONTANT_VOTE" => $montant_info['PROGRAMMATION_FINANCIERE_BIF']
      );
    }
    else
    {
      if ($TRANCHE_ID==1)
      {
        $MONTANT_VOTE = $montant_info['T1'];
      }
      else if ($TRANCHE_ID==2)
      {
        $MONTANT_VOTE = $montant_info['T2'];
      }
      else if ($TRANCHE_ID==3)
      {
        $MONTANT_VOTE = $montant_info['T3'];
      }
      else if ($TRANCHE_ID==4)
      {
        $MONTANT_VOTE = $montant_info['T4'];
      }

      $output = array("MONTANT_VOTE" => $MONTANT_VOTE);
    }
    return $this->response->setJSON($output);
  }

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

    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $TRANSFERTS_CREDITS=$this->request->getPost('TRANSFERTS_CREDITS');

    ########### get data  tempo_transfert_reception #############
    $sql='SELECT TRANS_PTBA_ID, REC_PTBA_ID,OBSERVATION_FINANCIER_ID, REC_MONTANT_VOTE, CODE_BUDGETAIRE, TRANS_MONTANT_VOTE, MONTANT_TRANSFERT, MONTANT_RECEVOIR, TEMPO_TRANSFERT_RECEPTION_ID, INSTITUTION_ID, TRANCHE_ID FROM  tempo_transfert_reception WHERE 1 AND EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_RECEPTION_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");
    $count_data = count($tempo);

    $html='<br>
    <table class="table table-bordered">
    <thead>
    <tr>
    <th>Institution</th>
    <th>Code budgetaire</th>
    <th>Activité&nbsp;&nbsp;d\'origine</th>
    <th>Observation</th>
    <th>Tranche</th>
    <th>Montant&nbsp;&nbsp;voté</th>
    <th>Montant&nbsp;&nbsp;Transferer</th>
    <th>Activité&nbsp;&nbsp;de&nbsp;&nbsp;destination</th>
    <th>Montant&nbsp;&nbsp;voté&nbsp;&nbsp;de&nbsp;&nbsp;destination</th>
    <th>Montant&nbsp;&nbsp;à&nbsp;&nbsp;recevoir</th>
    <th>Option</th>
    </tr>
    </thead>
    <body>';

    $MONTANT_PLAFOND_APRES_TRANSFERT = '';
    $MONTANT_APRES_TRANSFERT1 = '';
    $MONTANT_APRES_TRANSFERT = '';
    $MONTANT_PLAFOND_APRES_TRANSFERT2 = '';

    $total = 0;
    $total2 = 0;

    if($count_data==0)
    {
      $status = 0;
    }
    else
    {
      $status = 1;
      foreach ($tempo as $key)
      {
        //institution
        $institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', 'INSTITUTION_ID = '.$key->INSTITUTION_ID.'', 'DESCRIPTION_INSTITUTION ASC');
        $data_institution = $this->ModelPs->getRequeteOne($psgetrequete, $institution);

        //activite
        $activite = $this->getBindParms('ACTIVITES,PTBA_ID', 'ptba', 'PTBA_ID = '.$key->TRANS_PTBA_ID.' ', 'ACTIVITES  ASC');
        $data_activite = $this->ModelPs->getRequeteOne($psgetrequete, $activite);

        //activite pour ligne réceptrice
        $activite2 = $this->getBindParms('ACTIVITES,PTBA_ID', 'ptba', 'PTBA_ID = '.$key->REC_PTBA_ID.' ', 'ACTIVITES  ASC');
        $data_activite2 = $this->ModelPs->getRequeteOne($psgetrequete, $activite2);

        //tranches
        $gettranches = $this->getBindParms('DESCRIPTION_TRANCHE,TRANCHE_ID ', 'op_tranches', 'TRANCHE_ID = '.$key->TRANCHE_ID.'', 'TRANCHE_ID ASC');
        $tranches = $this->ModelPs->getRequeteOne($psgetrequete, $gettranches);

        //observation
        $getobservation = $this->getBindParms('DESC_OBSERVATION_FINANCIER,OBSERVATION_FINANCIER_ID  ', 'observation_transfert_financier', 'OBSERVATION_FINANCIER_ID  = '.$key->OBSERVATION_FINANCIER_ID .'', 'OBSERVATION_FINANCIER_ID  ASC');
        $observation = $this->ModelPs->getRequeteOne($psgetrequete, $getobservation);

        ######################## pour le transfert
        $total+=$key->MONTANT_TRANSFERT;
        $MONTANT_PLAFOND_APRES_TRANSFERT = $TRANSFERTS_CREDITS-$total;
        $MONTANT_APRES_TRANSFERT1 = $key->TRANS_MONTANT_VOTE-$total;

        ########################## pour la ligne rceptrice#############################
        $total2+=$key->MONTANT_RECEVOIR;
        $MONTANT_APRES_TRANSFERT = $key->REC_MONTANT_VOTE+$total2;
        $MONTANT_PLAFOND_APRES_TRANSFERT2 = $TRANSFERTS_CREDITS-$total2;
        
        $html.='<tr>
        <td>'.$data_institution['DESCRIPTION_INSTITUTION'].'</td>
        <td>'.$key->CODE_BUDGETAIRE.'</td>
        <td>'.$data_activite['ACTIVITES'].'</td>
        <td>'.$observation['DESC_OBSERVATION_FINANCIER'].'</td>
        <td>'.$tranches['DESCRIPTION_TRANCHE'].'</td>
        <td>'.$key->TRANS_MONTANT_VOTE.'</td>
        <td>'.$key->MONTANT_TRANSFERT.'</td>

        <td>'.$data_activite2['ACTIVITES'].'</td>
        <td>'.$key->REC_MONTANT_VOTE.'</td>
        <td>'.$key->MONTANT_RECEVOIR.'</td>
        <td>
        <a onclick="removeToCart('.$key->TEMPO_TRANSFERT_RECEPTION_ID.')" href="javascript:;" style="color: red"><i class="fa fa-trash"></i> <span id="loading_delete'.$key->TEMPO_TRANSFERT_RECEPTION_ID.'"></span><span id="message'.$key->TEMPO_TRANSFERT_RECEPTION_ID.'"></span></a> 
        </td>
        </tr>';
      }
    }

    $html.='</body></table>';
    $output = array(
      'status'=>$status,
      'html'=>$html,
      'MONTANT_PLAFOND_APRES_TRANSFERT'=>$MONTANT_PLAFOND_APRES_TRANSFERT,
      'MONTANT_APRES_TRANSFERT1'=>$MONTANT_APRES_TRANSFERT1, 
      'total'=>$total,
      'MONTANT_APRES_TRANSFERT'=>$MONTANT_APRES_TRANSFERT,
      'total2'=>$total2,
      'MONTANT_PLAFOND_APRES_TRANSFERT2'=>$MONTANT_PLAFOND_APRES_TRANSFERT2);
    return $this->response->setJSON($output);
  }

  public function addToCart()
  {
    $session  = \Config\Services::session();

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();  

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if(empty($USER_ID))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //ligne qui envoie
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');
    $PTBA_ID=$this->request->getPost('PTBA_ID');
    $MONTANT_TRANSFERT=$this->request->getPost('MONTANT_TRANSFERT');
    $TRANSFERTS_CREDITS=$this->request->getPost('TRANSFERTS_CREDITS');
    $MONTANT_VOTE=$this->request->getPost('MONTANT_VOTE');
    $TRANCHE_ID=$this->request->getPost('TRANCHE_ID');
    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $OBSERVATION_FINANCIER_ID=$this->request->getPost('OBSERVATION_FINANCIER_ID');

    //ligne réceptrice 
    $PTBA_ID_RECEPTION=$this->request->getPost('ACTIVITES');
    $MONTANT_RECEVOIR=$this->request->getPost('MONTANT_RECEVOIR');
    $MONTANT_VOTE2=$this->request->getPost('MONTANT_VOTE2');
    $ACTIVITES=$PTBA_ID_RECEPTION;
    ####################################################################
    $insertIntoTable='tempo_transfert_reception';
    $columsinsert="INSTITUTION_ID,OBSERVATION_FINANCIER_ID,CODE_BUDGETAIRE,TRANS_PTBA_ID,TRANCHE_ID,TRANS_MONTANT_VOTE,MONTANT_TRANSFERT,EXECUTION_BUDGETAIRE_ID,REC_MONTANT_VOTE,MONTANT_RECEVOIR,REC_PTBA_ID,USER_ID";
    $datacolumsinsert=$INSTITUTION_ID.",".$OBSERVATION_FINANCIER_ID.",".$CODE_NOMENCLATURE_BUDGETAIRE.",".$PTBA_ID.",".$TRANCHE_ID.",".$MONTANT_VOTE.",".$MONTANT_TRANSFERT.",".$EXECUTION_BUDGETAIRE_ID.",".$MONTANT_VOTE2.",".$MONTANT_RECEVOIR.",".$PTBA_ID_RECEPTION.",".$USER_ID;
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

    $TEMPO_TRANSFERT_RECEPTION_ID=$this->request->getPost('TEMPO_TRANSFERT_RECEPTION_ID');

    $insertIntoTable='tempo_transfert_reception';
    $critere ="TEMPO_TRANSFERT_RECEPTION_ID=".$TEMPO_TRANSFERT_RECEPTION_ID;
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

  // Enregistrement des données dans la table historique_transfert
  public function send_data($value='')
  {
    $session  = \Config\Services::session();

    $data=$this->urichk();
    $db = db_connect(); 
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $TYPE_OPERATION_ID = 2;

    if(empty($USER_ID))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $sql='SELECT TRANS_PTBA_ID, REC_PTBA_ID, OBSERVATION_FINANCIER_ID, REC_MONTANT_VOTE, CODE_BUDGETAIRE, TRANS_MONTANT_VOTE, MONTANT_TRANSFERT, MONTANT_RECEVOIR, TEMPO_TRANSFERT_RECEPTION_ID, INSTITUTION_ID, TRANCHE_ID FROM  tempo_transfert_reception WHERE 1 AND EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_RECEPTION_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");

    $insertIntoTable='historique_transfert';
    foreach ($tempo as $key => $value)
    {
      $activite2 = $this->getBindParms('T1,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,PTBA_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'PTBA_ID ='.$value->REC_PTBA_ID.' ', 'PTBA_ID ASC');
      $data_activite2 = $this->ModelPs->getRequeteOne($psgetrequete, $activite2);
      $CODE_NOMENCLATURE_BUDGETAIRE = $data_activite2['CODE_NOMENCLATURE_BUDGETAIRE'];

      ############################################################################
      $inst = $this->getBindParms('INSTITUTION_ID', 'execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$CODE_NOMENCLATURE_BUDGETAIRE.' ', 'INSTITUTION_ID ASC');
      $data_inst = $this->ModelPs->getRequeteOne($psgetrequete, $inst);
      $INSTITUTION_ID_RECEPTION = $data_inst['INSTITUTION_ID'];

      //activite
      $activite = $this->getBindParms('T1,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,PTBA_ID', 'ptba', ' PTBA_ID = '.$value->TRANS_PTBA_ID.'','PTBA_ID ASC');
      $data_activite = $this->ModelPs->getRequeteOne($psgetrequete, $activite);

      $columsinsert="TYPE_OPERATION_ID,USER_ID,MONTANT_TRANSFERT,PTBA_ID_TRANSFERT,MONTANT_RECEPTION,PTBA_ID_RECEPTION,EXECUTION_BUDGETAIRE_ID,TRIMESTRE_ID,INSTITUTION_ID_TRANSFERT,INSTITUTION_ID_RECEPTION,OBSERVATION_FINANCIER_ID";
      $datacolumsinsert=$TYPE_OPERATION_ID.",".$USER_ID.",".$value->MONTANT_TRANSFERT.",".$value->TRANS_PTBA_ID.",".$value->MONTANT_RECEVOIR.",".$value->REC_PTBA_ID.",".$EXECUTION_BUDGETAIRE_ID.",".$value->TRANCHE_ID.",".$value->INSTITUTION_ID.",".$INSTITUTION_ID_RECEPTION.",".$value->OBSERVATION_FINANCIER_ID;
      $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

      #########################  ###############################################
      //mise a jour dans la table ptba
      $table = 'ptba';
      $where='PTBA_ID='.$value->TRANS_PTBA_ID.' ';
      $where2='PTBA_ID='.$value->REC_PTBA_ID.' ';

      #################################### delete from table tempo after insert data in the table histo
      $insertIntoTable2 = 'tempo_transfert_reception';
      $critere2 ="TEMPO_TRANSFERT_RECEPTION_ID=".$value->TEMPO_TRANSFERT_RECEPTION_ID;
      $deleteparams2 =[$db->escapeString($insertIntoTable2),$db->escapeString($critere2)];
      $deleteRequete2 = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete2, $deleteparams2);

      if($value->TRANCHE_ID==1)
      {
        //cas du transfert
        $MONTANT_RESTANT_T1 = $data_activite['MONTANT_RESTANT_T1']-$value->MONTANT_TRANSFERT;
        $data='MONTANT_RESTANT_T1 = '.$MONTANT_RESTANT_T1.' ';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        $MONTANT_RESTANT_T1_RECEVOIR = $data_activite2['MONTANT_RESTANT_T1']+$value->MONTANT_RECEVOIR;
        $data2='MONTANT_RESTANT_T1 = '.$MONTANT_RESTANT_T1_RECEVOIR.' ';
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRANCHE_ID==2)
      {
        //cas du transfert
        $MONTANT_RESTANT_T2 = $data_activite['MONTANT_RESTANT_T2']-$value->MONTANT_TRANSFERT;
        $data='MONTANT_RESTANT_T2 = '.$MONTANT_RESTANT_T2.' ';
        $this->update_all_table($table,$data,$where);

                //cas receptrice
        $MONTANT_RESTANT_T2_RECEVOIR = $data_activite2['MONTANT_RESTANT_T2']+$value->MONTANT_RECEVOIR;
        $data2='MONTANT_RESTANT_T1 = '.$MONTANT_RESTANT_T2_RECEVOIR.' ';
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRANCHE_ID==3)
      {
        //cas du transfert
        $MONTANT_RESTANT_T3 = $data_activite['MONTANT_RESTANT_T3']-$value->MONTANT_TRANSFERT;
        $data='MONTANT_RESTANT_T3 = '.$MONTANT_RESTANT_T3.' ';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        $MONTANT_RESTANT_T3_RECEVOIR = $data_activite2['MONTANT_RESTANT_T3']+$value->MONTANT_RECEVOIR;
        $data2='MONTANT_RESTANT_T1 = '.$MONTANT_RESTANT_T3_RECEVOIR.' ';
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRANCHE_ID==4)
      {
        //cas du transfert
        $MONTANT_RESTANT_T4 = $data_activite['MONTANT_RESTANT_T4']-$value->MONTANT_TRANSFERT;
        $data='MONTANT_RESTANT_T4 = '.$MONTANT_RESTANT_T4.' ';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        $MONTANT_RESTANT_T4_RECEVOIR = $data_activite2['MONTANT_RESTANT_T4']+$value->MONTANT_RECEVOIR;
        $data2='MONTANT_RESTANT_T1 = '.$MONTANT_RESTANT_T4_RECEVOIR.' ';
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRANCHE_ID==5)
      {
        //cas du transfert
        $data='MONTANT_RESTANT_T1 =0, MONTANT_RESTANT_T2=0,MONTANT_RESTANT_T3=0,MONTANT_RESTANT_T4=0';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        $MONTANT_RESTANT_T1_RECEVOIR = $data_activite2['MONTANT_RESTANT_T1']+$value->MONTANT_RECEVOIR;
        $data2='MONTANT_RESTANT_T1='.$MONTANT_RESTANT_T1_RECEVOIR.' ';
        $this->update_all_table($table,$data2,$where2);
      }
    }

  	##########################  update dans la table execution_budgetaire apres transfert 
    $table3 = 'execution_budgetaire_new';
    $where3='EXECUTION_BUDGETAIRE_ID="'.$EXECUTION_BUDGETAIRE_ID.'"';
    $data3='IS_TRANSFERTS = 2 ';
    $this->update_all_table($table3,$data3,$where3);

    $data=['message' => "".lang('messages_lang.message_success').""];
    session()->setFlashdata('alert', $data);
    return redirect('transfert_new/Transfert_incrim');
  }
}
?>