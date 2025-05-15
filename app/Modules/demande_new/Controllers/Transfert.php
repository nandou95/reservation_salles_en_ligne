<?php 

/**
 * christa
 * transfert du montant d'une activité
 * 08/09/2023
 * christa@mediabox.bi
 */

namespace App\Modules\demande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Transfert extends BaseController
{
	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	function index()
	{
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

    $data = $this->urichk();

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$bindparams = $this->getBindParms('`CODE_NOMENCLATURE_BUDGETAIRE`','inst_institutions_ligne_budgetaire', '1', 'CODE_NOMENCLATURE_BUDGETAIRE_ID ASC');
		$data['code_budget'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
		return view('App\Modules\demande_new\Views\Transfert_view',$data);  
	}

	function getInfos()
	{
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

    $ACTIVITES = $this->request->getPost('ACTIVITES');
		$MONTANT = $this->request->getPost('MONTANT');
		$ACTIVITES2 = $this->request->getPost('ACTIVITES2');

		$db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getcodeactivite = $this->getBindParms('ACTIVITES', 'ptba', 'PTBA_ID= '.$ACTIVITES.' ', 'ACTIVITES  ASC');
    $activites1 = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

    $getactivite2 = $this->getBindParms('ACTIVITES', 'ptba', 'PTBA_ID= '.$ACTIVITES2.' ', 'ACTIVITES  ASC');
    $activites2 = $this->ModelPs->getRequete($callpsreq, $getactivite2);

    $statut = 1;
    echo json_encode(array('statut'=>$statut,'ACTIVITES'=>$activites1['ACTIVITES'],'ACTIVITES2'=>$activites2['ACTIVITES'],'MONTANT'=>$MONTANT));
	}

	// gestion de la dependance code  budgetaire et activites
  function get_activitesByCode($CODE_NOMENCLATURE_BUDGETAIRE=0)
  {
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

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getcodeactivite = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID', 'ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ligne.CODE_NOMENCLATURE_BUDGETAIRE = '.$CODE_NOMENCLATURE_BUDGETAIRE.' ', 'ACTIVITES  ASC');
    $code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($code_activites as $key)
    {
      $html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
    }

    $output = array(

      "activite_by_code" => $html,
    );

    return $this->response->setJSON($output);
  }
  //gestion de la dependance code  budgetaire et activites
  function get_activitesByCode2($CODE_NOMENCLATURE_BUDGETAIRE2=0)
  {
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

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getcodeactivite = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,CODES_PROGRAMMATIQUE', 'ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ligne.CODE_NOMENCLATURE_BUDGETAIRE = '.$CODE_NOMENCLATURE_BUDGETAIRE2.' ', 'ACTIVITES  ASC');;
    $code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($code_activites as $key)
    {
      $html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
    }

    $output = array(
      "activite_by_code" => $html,
    );
    return $this->response->setJSON($output);
  }

  //recupération du montant par rapport à l activité
  function get_montant($ACTIVITES=0)
  {
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

    $dataa=$this->converdate();
      $debut=$dataa['debut'];
      $fin=$dataa['fin'];
      $CODE_TRANCHE=$dataa['CODE_TRANCHE'];
      $CODE_NOMENCLATURE_BUDGETAIRE='01000040076611011000041301';

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $bindparams = $this->getBindParms('`PTBA_ID`, `T1`,`T2`,`T3`,`T4`', 'ptba', 'PTBA_ID='.$ACTIVITES, 'T1');

      $montant_vote=$this->ModelPs->getRequeteOne($callpsreq,$bindparams);

      $params_mont = $this->getBindParms('det.DATE_DEMANDE,det.MONTANT_TITRE_DECAISSEMENT', 'proc_demande_exec_budgetaire dmd JOIN proc_demande_exec_budgetaire_details det ON det.EXEC_BUDG_PHASE_ADMIN_ID=dmd.EXEC_BUDG_PHASE_ADMIN_ID', 'dmd.PTBA_ID='.$ACTIVITES.' AND det.DATE_DEMANDE BETWEEN "'.$debut.'" AND "'.$fin.'"', 'det.MONTANT_TITRE_DECAISSEMENT');

      $params_mont=str_replace('\"', "'", $params_mont);

      $montant_decaisse=$this->ModelPs->getRequeteOne($callpsreq,$params_mont);

      $montant_decaissement=!empty($montant_decaisse['MONTANT_TITRE_DECAISSEMENT']) ? $montant_decaisse['MONTANT_TITRE_DECAISSEMENT'] : 0;

      $mont = 0;
      if ($CODE_TRANCHE == 'T1') {

      	$mont=$montant_vote['T1']-$montant_decaissement;

      }elseif ($CODE_TRANCHE == 'T2') {

      	$mont=$montant_vote['T2']-$montant_decaissement;
      }elseif ($CODE_TRANCHE == 'T3') {

      	$mont=$montant_vote['T3']-$montant_decaissement;

      }elseif ($CODE_TRANCHE == 'T4') {
      	
      	$mont=$montant_vote['T4']-$montant_decaissement;
      }
      $statut=1;
    echo json_encode(array('mont'=>$mont));
  }


  //enregistrement dans la base du montnat transferé
  function transferer()
  {
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

    $MONTANT = $this->request->getPost('MONTANT');
  	$ACTIVITES_DE = $this->request->getPost('ACTIVITES');
  	$ACTIVITES_VERS = $this->request->getPost('ACTIVITES2');

  	$PROCESS_ID=4;
  	$ETAPE_ID=34;
  	$MOUVEMENT_DEPENSE_ID=6;

  	$rules=[
			'MONTANT' => [
	    		'label' => '',
	        'rules' => 'required',
	        'errors' => [
	            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	        ]
			],
			'ACTIVITES' => [
	    		'label' => '',
	        'rules' => 'required',
	        'errors' => [
	            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	        ]
			],
			'ACTIVITES2' => [
	    		'label' => '',
	        'rules' => 'required',
	        'errors' => [
	            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	        ]
			],

		];

		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			

	    	$columsinsert="PROCESS_ID,ETAPE_ID,MOUVEMENT_DEPENSE_ID,MONTANT_TRANSFERT,ACTIVITE_DE,ACTIVITE_VERS";
	    	$datacolumsinsert=$PROCESS_ID.",".$ETAPE_ID.",".$MOUVEMENT_DEPENSE_ID.",".$MONTANT.",".$ACTIVITES_DE.",".$ACTIVITES_VERS;

	    	$table='proc_demande_exec_budgetaire';
		    $bindparms=[$table,$columsinsert,$datacolumsinsert];
		    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		    $this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);

		    // $status=1;
		    return redirect('demande/Transfert');
		    // echo json_encode(array('status'=>$status));
		}else{
			$data = $this->urichk();

			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$bindparams = $this->getBindParms('`CODE_NOMENCLATURE_BUDGETAIRE`', 'ptba', '1', 'CODE_NOMENCLATURE_BUDGETAIRE');
			$data['code_budget'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
			return view('App\Modules\demande_new\Views\Transfert_view',$data); 
		}
  }


	/**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilisE
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
   */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>