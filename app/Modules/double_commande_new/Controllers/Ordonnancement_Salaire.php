<?php
/*
*NDERAGAKURA ALAIN CHARBEL
*Titre: Ordonnancement cas salaire
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 9 septembre,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Ordonnancement_Salaire extends BaseController
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

	//interface de l'ordonnancement cas salaire
	function add($id=0)
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $infoAffiche  = 'SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,LIQUIDATION,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire exec JOIN execution_budgetaire_titre_decaissement det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(exec.EXECUTION_BUDGETAIRE_ID) = "'.$id.'"';
    $infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
    $data['info']= $this->ModelPs->getRequeteOne($infoAffiche);

    $get_data='SELECT CODE_SOUS_LITTERA,SUM(MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache exec JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].' AND CODE_SOUS_LITTERA IN (61110,61160,61140,61610,61210,61240,61260,61620) GROUP BY CODE_SOUS_LITTERA';
    $get_data= "CALL `getTable`('" . $get_data . "');";
    $data['get_data']= $this->ModelPs->getRequete($get_data);
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";     
    $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
    $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
    $data['etape'] =$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

    $data['EXECUTION_BUDGETAIRE_ID'] = $data['info']['EXECUTION_BUDGETAIRE_ID'];

    $operation  = 'SELECT ID_OPERATION,DESCRIPTION FROM budgetaire_type_operation_validation WHERE ID_OPERATION=2 ORDER BY DESCRIPTION ASC';
    $operation = "CALL `getTable`('" . $operation . "');";
    $data['get_operation'] = $this->ModelPs->getRequete($operation);

    return view('App\Modules\double_commande_new\Views\Ordonnancement_Salaire_Add_View',$data);
  }

  public function save($value='')
  {
  	$session  = \Config\Services::session();
    $USER_ID='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $rules = [
      'DATE_RECEPTION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],      
      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],   
      'DATE_TRANSMISSION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]     
    ];

    $this->validation->setRules($rules);

    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    if($this->validation->withRequest($this->request)->run())
    {
    	$EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
			$ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
			$DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
			$ID_OPERATION=$this->request->getPost('ID_OPERATION');
			$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
      $LIQUIDATION=$this->request->getPost('LIQUIDATION');

			$psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_DOUBLE_COMMANDE_ID.' AND IS_SALAIRE=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
      $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      //modification dans execution_budgetaire_titre_decaissement
      $conditions="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;    
      $updateIntoTD='execution_budgetaire_titre_decaissement';
      $datacolumsTDupdate="ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
      $this->update_all_table($updateIntoTD,$datacolumsTDupdate,$conditions);

      //modification dans execution_budgetaire
      $conditions="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
      $updateIntoexec='execution_budgetaire';
      $datacolumseExecupdate="ORDONNANCEMENT=".$LIQUIDATION."";
      $this->update_all_table($updateIntoexec,$datacolumseExecupdate,$conditions);

      //modification dans execution_budgetaire tache detail
      $conditions="EXECUTION_BUDGETAIRE_DETAIL_ID=".$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $updateIntodet='execution_budgetaire_tache_detail';
      $datacolumsdetupdate="MONTANT_ORDONNANCEMENT=".$LIQUIDATION.",DATE_ORDONNANCEMENT='".$DATE_TRANSMISSION."'";
      $this->update_all_table($updateIntodet,$datacolumsdetupdate,$conditions);

      //modification dans execution_budgetaire_execution_tache
      $cond_tache="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
      $updateIntoexectach='execution_budgetaire_execution_tache';
      $datacolumsexecupdate="MONTANT_ORDONNANCEMENT=MONTANT_LIQUIDATION";
      $this->update_all_table($updateIntoexectach,$datacolumsexecupdate,$cond_tache);

      //insertion dans historique
      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_DOUBLE_COMMANDE_ID.",".$USER_ID.",".$DATE_RECEPTION.",".$DATE_TRANSMISSION."";
      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Ordonnancement_Salaire_Liste/index_A_Faire');
    }
    else
    {
    	return $this->add(md5($EXECUTION_BUDGETAIRE_ID));
    }
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  /* Debut Gestion insertion */
  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }
  /* Fin Gestion insertion */

  //Update
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
}