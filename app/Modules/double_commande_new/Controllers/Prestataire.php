<?php 

/**
 * christa
 * crud des prestati
 */

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Prestataire extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		// $this->load->library('Excel');
	}

	public function index($value='')
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		return view('App\Modules\double_commande_new\Views\Prestataire_View',$data);   
	}
	//liste des prestataires
	public function listing()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

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
		$order_column = array(1,'NOM_PRESTATAIRE', 'PRENOM_PRESTATAIRE', 'NIF_PRESTATAIRE','NOM_BANQUE','COMPTE_BANCAIRE','DESC_TYPE_BENEFICIAIRE','DESCR_INDIVINDU', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NOM_PRESTATAIRE LIKE "%' . $var_search . '%" OR PRENOM_PRESTATAIRE LIKE "%' . $var_search . '%" OR NIF_PRESTATAIRE LIKE "%' . $var_search . '%" OR NOM_BANQUE LIKE "%' . $var_search . '%" OR COMPTE_BANCAIRE LIKE "%' . $var_search . '%")') : '';


    // Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		
		$requetedebase= "SELECT prestataire.PRESTATAIRE_ID,prestataire.NOM_PRESTATAIRE,prestataire.PRENOM_PRESTATAIRE,prestataire.NIF_PRESTATAIRE,banque.NOM_BANQUE,prestataire.COMPTE_BANCAIRE,type_beneficiaire.DESC_TYPE_BENEFICIAIRE,type_indivindu.DESCR_INDIVINDU FROM `prestataire` LEFT JOIN banque ON banque.BANQUE_ID=prestataire.BANQUE_ID JOIN type_beneficiaire ON type_beneficiaire.TYPE_BENEFICIAIRE_ID=prestataire.TYPE_BENEFICIAIRE_ID LEFT JOIN type_indivindu ON type_indivindu.ID_TYPE_INDIVINDU=prestataire.ID_TYPE_INDIVINDU WHERE 1";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";

		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;

		foreach ($fetch_data as $row)
		{
			$sub_array = array();

			$individ = (!empty($row->DESCR_INDIVINDU)) ? $row->DESCR_INDIVINDU : 'N/A';
			$nif = (!empty($row->NIF_PRESTATAIRE)) ? $row->NIF_PRESTATAIRE : 'N/A';
			$u++;
			$sub_array[] = $u;
			$sub_array[] = $row->NOM_PRESTATAIRE.' '.$row->PRENOM_PRESTATAIRE;
			$sub_array[] = $nif;
			$sub_array[] = $row->NOM_BANQUE;
			$sub_array[] = $row->COMPTE_BANCAIRE;
			$sub_array[] = $row->DESC_TYPE_BENEFICIAIRE;
			$sub_array[] = $individ;

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
	//appel du form pour ajouter un prestataire
	function add()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		//banque
		$bank = $this->getBindParms('`BANQUE_ID`,`NOM_BANQUE`', 'banque', '1', '`NOM_BANQUE` ASC');
		$data['banque'] = $this->ModelPs->getRequete($psgetrequete, $bank);
		//type de beneficiaire
		$type_ben = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE', 'type_beneficiaire', '1', '`DESC_TYPE_BENEFICIAIRE` ASC');
		$data['type_beneficiaire'] = $this->ModelPs->getRequete($psgetrequete, $type_ben);

		//type d'individu
		$type_indiv = $this->getBindParms('ID_TYPE_INDIVINDU,DESCR_INDIVINDU', 'type_indivindu', '1', '`ID_TYPE_INDIVINDU` ASC');
		$data['type_individu'] = $this->ModelPs->getRequete($psgetrequete, $type_indiv);
					
		return view('App\Modules\double_commande_new\Views\Prestataire_Add_View',$data); 
	}

	//function pour enregistrer dans la base un prestataire
	function save()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$ID_TYPE_INDIVINDU=$this->request->getPost('ID_TYPE_INDIVINDU');
  	$TYPE_BENEFICIAIRE_ID=$this->request->getPost('TYPE_BENEFICIAIRE_ID');

		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		$rules = [
			  // 'BANQUE_ID' => [
     //      'label' => '',
     //      'rules' => 'required',
     //      'errors' => [
     //        'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
     //      ]
     //    ],
     //    'COMPTE_BANCAIRE' => [
	    //     'label' => '',
	    //     'rules' => 'required',
	    //     'errors' => [
	    //     'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
	    // 	]
    	// ],
        'TYPE_BENEFICIAIRE_ID' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
	    	]
    	]
        
		];
		if ($ID_TYPE_INDIVINDU==2)
		{
			$rules = [
				'NOM_PRESTATAIRE' => [
					'label' => '',
					'rules' => 'required',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
					]
				]
			];
		}else{
			$rules = [
				'NOM_PRESTATAIRE' => [
					'label' => '',
					'rules' => 'required',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
					]
				],
				// 'PRENOM_PRESTATAIRE' => [
    //       'label' => '',
    //       'rules' => 'required',
    //       'errors' => [
    //         'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
    //       ]
    //     ]
			];
		}

		if ($TYPE_BENEFICIAIRE_ID==1)
		{
			$rules = [
				'ID_TYPE_INDIVINDU' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
		    	]
	    	]
			];
		}

		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
    {
  		$NOM_PRESTATAIRE=$this->request->getPost('NOM_PRESTATAIRE');
  		$NOM_PRESTATAIRE=addslashes($NOM_PRESTATAIRE);
  		$PRENOM_PRESTATAIRE=$this->request->getPost('PRENOM_PRESTATAIRE');
  		$PRENOM_PRESTATAIRE=addslashes($PRENOM_PRESTATAIRE);
  		$NIF_PRESTATAIRE=$this->request->getPost('NIF_PRESTATAIRE');
  		$BANQUE_ID=$this->request->getPost('BANQUE_ID');
  		$COMPTE_BANCAIRE=$this->request->getPost('COMPTE_BANCAIRE');
  		$AUTRE_BANQUE=$this->request->getPost('AUTRE_BANQUE');

  		$id_banque='';
  		if (!empty($BANQUE_ID) || $BANQUE_ID!==0)
  		{ 
  			$id_banque=$BANQUE_ID; 		  			
  		}else {
  			$table='banque';
	  		$columsinsert='NOM_BANQUE';
	  		$datacolumsinsert='"'.$AUTRE_BANQUE.'"';

				$bindparms=[$table,$columsinsert,$datacolumsinsert];
				$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
				$banque=$this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
				$id_banque=$banque['id'];
  		}
  		// print_r($id_banque);die();
  		$table='prestataire';

  		if ($TYPE_BENEFICIAIRE_ID==1)
  		{
  			$columnselect="NOM_PRESTATAIRE,PRENOM_PRESTATAIRE,NIF_PRESTATAIRE,BANQUE_ID,COMPTE_BANCAIRE,TYPE_BENEFICIAIRE_ID,ID_TYPE_INDIVINDU";
				$datacolumsinsert='"'.$NOM_PRESTATAIRE.'","'.$PRENOM_PRESTATAIRE.'","'.$NIF_PRESTATAIRE.'","'.$id_banque.'","'.$COMPTE_BANCAIRE.'",'.$TYPE_BENEFICIAIRE_ID.','.$ID_TYPE_INDIVINDU;
  		}else{
  			$columnselect="NOM_PRESTATAIRE,PRENOM_PRESTATAIRE,NIF_PRESTATAIRE,BANQUE_ID,COMPTE_BANCAIRE,TYPE_BENEFICIAIRE_ID";
				$datacolumsinsert='"'.$NOM_PRESTATAIRE.'","'.$PRENOM_PRESTATAIRE.'","'.$NIF_PRESTATAIRE.'","'.$id_banque.'","'.$COMPTE_BANCAIRE.'",'.$TYPE_BENEFICIAIRE_ID;
  		}

			$bindparms=[$table,$columnselect,$datacolumsinsert];
  		// print_r($bindparms);die();
			$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
			$this->ModelPs->createUpdateDelete($insertReq,$bindparms);

			$data=['message' => "".lang('messages_lang.message_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Prestataire');

    }else{
    	return $this->add();
    }
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}
}

?>