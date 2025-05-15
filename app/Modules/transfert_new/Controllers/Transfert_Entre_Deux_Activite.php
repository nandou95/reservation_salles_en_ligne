<?php
/*
*MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Transfert entre deux activité
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Date: 11 10 2023
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

class Transfert_Entre_Deux_Activite extends BaseController
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

  // fait appel au formule insertion entre des activités
	public function add()
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
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

    //recuperer les institution de la personne connecte
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($psgetrequete, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $INSTITUTION_ID = substr($ID_INST,0,-1);



		########### get data execution budgetaire #############
    $sql_exec='SELECT EXECUTION_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ligne.CODE_NOMENCLATURE_BUDGETAIRE as id,(SELECT COUNT(ptba.PTBA_ID) FROM `ptba` JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ligne.CODE_NOMENCLATURE_BUDGETAIRE=id) AS nbr FROM execution_budgetaire_new  WHERE 1 AND INSTITUTION_ID IN ('.$INSTITUTION_ID.') AND IS_RACCROCHE=0 HAVING nbr>1 ';
    $data['exec_budg'] = $this->ModelPs->getRequete("CALL `getTable`('".$sql_exec."')");

    //tranches
    $gettranches = $this->getBindParms('DESCRIPTION_TRANCHE,TRANCHE_ID ', 'op_tranches', '1', 'TRANCHE_ID ASC');
    $data['tranches'] = $this->ModelPs->getRequete($psgetrequete, $gettranches);

    return view('App\Modules\transfert_new\Views\Transfert_Entre_Deux_Activite_Add_View',$data);
  }

	// recuparation des activités a partir de code budgetaire/ id execution
  function get_activitesByCode()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('IMPUTATION');

    $sql_exec='SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID.' ';
    $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");

    $getcodeactivite = $this->getBindParms('ACTIVITES,PTBA_ID', 'ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$exec_budg['IMPUTATION'].' ', 'ACTIVITES  ASC');
    $code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_activites as $key)
    {
      $html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
    }

    $output = array("PTBA_ID" => $html);

    return $this->response->setJSON($output);
  }

	///recuparation du montant voté a partir d'une activite de destination
  function get_MontantVoteByActivite()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $PTBA_ID = $this->request->getPost('PTBA_ID');
    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('IMPUTATION');

    $sql_exec='SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID.' ';
    $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");

    $bind_proc = $this->getBindParms('PROGRAMMATION_FINANCIERE_BIF','ptba', 'PTBA_ID ='.$PTBA_ID,'PTBA_ID  ASC');
    $montant_vote= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);
    ################################# get activite ########################

    $getcodeactivite = $this->getBindParms('ACTIVITES,PTBA_ID','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$exec_budg['IMPUTATION'].' AND PTBA_ID!='.$PTBA_ID.' ', 'ACTIVITES  ASC');
    $code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_activites as $key)
    {
      $html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
    }

    $output = array(
      "MONTANT_VOTE" => $montant_vote['PROGRAMMATION_FINANCIERE_BIF'],
      "PTBA_ID" => $html
    );
    return $this->response->setJSON($output);
  }

	// recuparation du montant voté a partir d'une activite receptrice
  function get_MontantVoteByActivite2()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $ACTIVITES = $this->request->getPost('ACTIVITES');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $bind_proc = $this->getBindParms('PROGRAMMATION_FINANCIERE_BIF', 'ptba', 'PTBA_ID ='.$ACTIVITES,'PTBA_ID  ASC');
    $montant_vote= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    $output = array(
      "MONTANT_VOTE" => $montant_vote['PROGRAMMATION_FINANCIERE_BIF']
    );

    return $this->response->setJSON($output);
  }

  // get data selon le trimestre selectionner 
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
      if($TRANCHE_ID==1)
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

  //lister les infortion stocker temporairement
  public function liste_tempo($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    ########### get data  tempo_transfert_reception #############
    $sql='SELECT TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID, TRANS_PTBA_ID, REC_PTBA_ID, MONTANT_TRANSFERT, MONTANT_RECEVOIR, CODE_BUDGETAIRE, TRANCHE_ID, REC_MONTANT_VOTE, TRANS_MONTANT_VOTE FROM  tempo_transfert_entre_activite WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID   DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");
    $count_data = count($tempo);

    $html='<br>
    <table class="table table-bordered">
    <thead>
    <tr>
    <th>Code budgetaire</th>
    <th>Activité&nbsp;&nbsp;d\'origine</th>
    <th>Montant&nbsp;&nbsp;voté&nbsp;&nbsp;d\'origine</th>
    <th>Tranche</th>
    <th>Montant&nbsp;&nbsp;Transferer</th>
    <th>Activité&nbsp;&nbsp;destinataire</th>
    <th>Montant&nbsp;&nbsp;voté&nbsp;&nbsp;destinataire</th>
    <th>Montant&nbsp;&nbsp;à&nbsp;&nbsp;recevoir</th>
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
      $status=1;
      foreach($tempo as $key)
      {
        //activite
        $activite=$this->getBindParms('ACTIVITES,PTBA_ID', 'ptba', 'PTBA_ID = '.$key->TRANS_PTBA_ID.' ', 'ACTIVITES  ASC');
        $data_activite = $this->ModelPs->getRequeteOne($psgetrequete, $activite);

        //activite pour ligne réceptrice
        $activite2 = $this->getBindParms('ACTIVITES,PTBA_ID', 'ptba', 'PTBA_ID = '.$key->REC_PTBA_ID.' ', 'ACTIVITES  ASC');
        $data_activite2 = $this->ModelPs->getRequeteOne($psgetrequete, $activite2);

        //tranches
        $gettranches = $this->getBindParms('DESCRIPTION_TRANCHE,TRANCHE_ID ', 'op_tranches', 'TRANCHE_ID = '.$key->TRANCHE_ID.'', 'TRANCHE_ID ASC');
        $tranches = $this->ModelPs->getRequeteOne($psgetrequete, $gettranches);

        // code budgetaire
        $sql_exec='SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID ='.$key->CODE_BUDGETAIRE.' ';
        $exec_budg = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_exec . "')");
        
        $html.='<tr>
        <td>'.$exec_budg['IMPUTATION'].'</td>
        <td>'.$data_activite['ACTIVITES'].'</td>
        <td>'.$key->TRANS_MONTANT_VOTE.'</td>
        <td>'.$tranches['DESCRIPTION_TRANCHE'].'</td>
        <td>'.$key->MONTANT_TRANSFERT.'</td>

        <td>'.$data_activite2['ACTIVITES'].'</td>
        <td>'.$key->REC_MONTANT_VOTE.'</td>
        <td>'.$key->MONTANT_RECEVOIR.'</td>
        <td>
        <a onclick="removeToCart('.$key->TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID.')" href="javascript:;" style="color: red"><i class="fa fa-trash"></i> <span id="loading_delete'.$key->TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID.'"></span> <span id="message'.$key->TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID.'"></span></a> 
        </td>
        </tr>';
      }
    }

    $html.='</body></table>';
    $output = array(
      'status'=>$status,
      'html'=>$html
    );
    return $this->response->setJSON($output);
  }

  //ajouter les données dans la table temporaire
  public function addToCart()
  {
    $session  = \Config\Services::session();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect(); 
    $user_id = session()->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //ligne qui envoie
    $IMPUTATION=$this->request->getPost('IMPUTATION');
    $PTBA_ID=$this->request->getPost('PTBA_ID');
    $MONTANT_VOTE=$this->request->getPost('MONTANT_VOTE');
    $TRANCHE_ID=$this->request->getPost('TRANCHE_ID');
    $MONTANT_TRANSFERT=$this->request->getPost('MONTANT_TRANSFERT');
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    $MONTANT_VOTE2=$this->request->getPost('MONTANT_VOTE2');
    $MONTANT_RECEVOIR=$this->request->getPost('MONTANT_RECEVOIR');
    $PTBA_ID_RECEPTION=$this->request->getPost('ACTIVITES');

    #################################################################### 
    $insertIntoTable='tempo_transfert_entre_activite';
    $columsinsert="CODE_BUDGETAIRE,TRANS_PTBA_ID,TRANS_MONTANT_VOTE,MONTANT_TRANSFERT,USER_ID,REC_MONTANT_VOTE,MONTANT_RECEVOIR,REC_PTBA_ID,TRANCHE_ID";
    $datacolumsinsert=$IMPUTATION.",".$PTBA_ID.",".$MONTANT_VOTE.",".$MONTANT_TRANSFERT.",".$USER_ID.",".$MONTANT_VOTE2.",".$MONTANT_TRANSFERT.",".$PTBA_ID_RECEPTION.",".$TRANCHE_ID;
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

    $TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID =$this->request->getPost('TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID');

    $insertIntoTable='tempo_transfert_entre_activite';
    $critere ="TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID =".$TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID;
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
    $session  = \Config\Services::session();

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_ID))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    
    $TYPE_OPERATION_ID = 3;
    $IMPUTATION = $this->request->getPost('IMPUTATION');

    $MONTANT_TRANSFERT = $this->request->getPost('MONTANT_TRANSFERT');
    $PTBA_ID = $this->request->getPost('PTBA_ID');
    $ACTIVITES = $this->request->getPost('ACTIVITES');


    $sql='SELECT TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID, TRANS_PTBA_ID, REC_PTBA_ID, MONTANT_TRANSFERT, MONTANT_RECEVOIR, CODE_BUDGETAIRE, TRANCHE_ID FROM  tempo_transfert_entre_activite WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");

    $insertIntoTable='historique_transfert';

    foreach ($tempo as $key => $value) 
    {
      // get institution origine
      $activite = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'PTBA_ID ='.$value->TRANS_PTBA_ID.' ', 'PTBA_ID ASC');
      $data_activite = $this->ModelPs->getRequeteOne($psgetrequete, $activite);
      $CODE_NOMENCLATURE_BUDGETAIRE = $data_activite['CODE_NOMENCLATURE_BUDGETAIRE'];
      
      $inst = $this->getBindParms('INSTITUTION_ID', 'execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$CODE_NOMENCLATURE_BUDGETAIRE.' ', 'INSTITUTION_ID ASC');
      $data_inst = $this->ModelPs->getRequeteOne($psgetrequete, $inst);
      $INSTITUTION_ID_TRANSFERT = $data_inst['INSTITUTION_ID'];
      ######################################################################

      //get activite destinataire
      $activite2 = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','PTBA_ID ='.$value->REC_PTBA_ID.' ', 'PTBA_ID ASC');
      $data_activite2 = $this->ModelPs->getRequeteOne($psgetrequete, $activite2);
      $CODE_NOMENCLATURE_BUDGETAIRE2 = $data_activite2['CODE_NOMENCLATURE_BUDGETAIRE'];

      $inst2 = $this->getBindParms('INSTITUTION_ID', 'execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$CODE_NOMENCLATURE_BUDGETAIRE2.' ', 'INSTITUTION_ID ASC');
      $data_inst2 = $this->ModelPs->getRequeteOne($psgetrequete, $inst2);
      $INSTITUTION_ID_RECEPTION = $data_inst2['INSTITUTION_ID'];

      $columsinsert="TYPE_OPERATION_ID,USER_ID,MONTANT_TRANSFERT,PTBA_ID_TRANSFERT,MONTANT_RECEPTION,PTBA_ID_RECEPTION,EXECUTION_BUDGETAIRE_ID,TRIMESTRE_ID,INSTITUTION_ID_TRANSFERT,INSTITUTION_ID_RECEPTION";
      $datacolumsinsert=$TYPE_OPERATION_ID.",".$USER_ID.",".$value->MONTANT_TRANSFERT.",".$value->TRANS_PTBA_ID.",".$value->MONTANT_RECEVOIR.",".$value->REC_PTBA_ID.",".$value->CODE_BUDGETAIRE.",".$value->TRANCHE_ID.",".$INSTITUTION_ID_TRANSFERT.",".$INSTITUTION_ID_RECEPTION;
      $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

      #################################### delete from table tempo after insert data in the table histo
      $insertIntoTable2 = 'tempo_transfert_entre_activite';
      $critere2 ="TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID=".$value->TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID;
      $deleteparams2 =[$db->escapeString($insertIntoTable2),$db->escapeString($critere2)];
      $deleteRequete2 = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete2, $deleteparams2);
      #################################################################################

      //mise a jour dans la table ptba
      $table = 'ptba';
      $where='PTBA_ID='.$value->TRANS_PTBA_ID.' ';
      $where2='PTBA_ID='.$value->REC_PTBA_ID.' ';

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

    $data=['message'=> "".lang('messages_lang.message_success').""];
    session()->setFlashdata('alert', $data);
    return redirect('transfert_new/Transfert_Entre_Deux_Activite/add');
  }
}
?>