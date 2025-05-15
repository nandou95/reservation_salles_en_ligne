<?php 
/**Joa-Kevin Iradukunda
*Titre: Modifier le prestataire
*Numero de telephone: (+257) 62 63 65 35
*WhatsApp: (+27) 61 436 6546
*Email: joa-kevin.iradukunda@mediabox.bi
*Date: 24 Octobre,2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Modifier_Prestataire extends BaseController
{
	function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs($db);
		$this->my_Model = new ModelPs($db);
		$this->validation = \Config\Services::validation();
		$this->session 	= \Config\Services::session();
		$table = new \CodeIgniter\View\Table();
	}

	//fonction pour le view de modification
	public function get_view()
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		// get BEs
		$bind_BEs=$this->getBindParms('execution_budgetaire.EXECUTION_BUDGETAIRE_ID,NUMERO_BON_ENGAGEMENT','execution_budgetaire JOIN execution_budgetaire_tache_info_suppl ON execution_budgetaire_tache_info_suppl.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID','NUMERO_BON_ENGAGEMENT IS NOT NULL AND INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.') AND PRESTATAIRE_ID IS NOT NULL AND TYPE_BENEFICIAIRE_ID IS NOT NULL','NUMERO_BON_ENGAGEMENT ASC');
		$data['BEs']= $this->ModelPs->getRequete($callpsreq,$bind_BEs);

		// get type beneficiaire
		$bind_Type_Beneficiaire=$this->getBindParms('TYPE_BENEFICIAIRE_ID ,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','TYPE_BENEFICIAIRE_ID ASC');
		$data['Type_Beneficiaires']= $this->ModelPs->getRequete($callpsreq,$bind_Type_Beneficiaire);

		return view('App\Modules\double_commande_new\Views\Modifier_Prestataire_View',$data);
	}

	//modifier le prestataire
	public function update()
	{
		$db = db_connect();
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		$rules = [			
			'EXECUTION_BUDGETAIRE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'TYPE_BENEFICIAIRE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PRESTATAIRE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			]
		];

		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$TYPE_BENEFICIAIRE_ID = $this->request->getPost('TYPE_BENEFICIAIRE_ID');
			$PRESTATAIRE_ID = $this->request->getPost('PRESTATAIRE_ID');

			$table = 'execution_budgetaire_tache_info_suppl';
			$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$data='TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIAIRE_ID.', PRESTATAIRE_ID='.$PRESTATAIRE_ID.'';
			$this->update_all_table($table,$data,$where);

			$data=['message' => "".lang('messages_lang.corriger_message').""];
            session()->setFlashdata('alert', $data);
            return redirect('double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_fait');
		}
		else
		{
			return $this->get_view();
		}		
	}

	//fonctions pour recupperer les prestataires
	public function get_prestataire($TYPE_BENEFICIAIRE_ID)
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$getdata = $this->getBindParms('PRESTATAIRE_ID,NOM_PRESTATAIRE,PRENOM_PRESTATAIRE','prestataire','TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIAIRE_ID,'PRESTATAIRE_ID  ASC');
		$getdata = $this->ModelPs->getRequete($callpsreq, $getdata);

		$html='<option value="">Sélectionner</option>';
		foreach ($getdata as $key)
		{
			$html.="<option value ='".$key->PRESTATAIRE_ID."'>".$key->NOM_PRESTATAIRE." ".$key->PRENOM_PRESTATAIRE."</option>";
		}

		$output = array("PRESTATAIRE_ID" => $html);
		return $this->response->setJSON($output);
	}

	//recupere les infos du BE
	public function get_info($EXECUTION_BUDGETAIRE_ID)
	{
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$getdata = $this->getBindParms('TYPE_BENEFICIAIRE_ID,PRESTATAIRE_ID','execution_budgetaire JOIN execution_budgetaire_tache_info_suppl ON execution_budgetaire_tache_info_suppl.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID','execution_budgetaire_tache_info_suppl.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID,'execution_budgetaire.EXECUTION_BUDGETAIRE_ID ASC');
		$getdata = $this->ModelPs->getRequeteOne($callpsreq, $getdata);

		$getprest = $this->getBindParms('PRESTATAIRE_ID,NOM_PRESTATAIRE,PRENOM_PRESTATAIRE','prestataire','TYPE_BENEFICIAIRE_ID='.$getdata['TYPE_BENEFICIAIRE_ID'],'PRESTATAIRE_ID  ASC');
		$getprest = $this->ModelPs->getRequete($callpsreq, $getprest);

		$html='<option value="">Sélectionner</option>';
		foreach ($getprest as $key)
		{
			if($getdata['PRESTATAIRE_ID']==$key->PRESTATAIRE_ID)
			{
				$html.="<option value ='".$key->PRESTATAIRE_ID."' selected>".$key->NOM_PRESTATAIRE." ".$key->PRENOM_PRESTATAIRE."</option>";
			}
			else
			{
				$html.="<option value ='".$key->PRESTATAIRE_ID."'>".$key->NOM_PRESTATAIRE." ".$key->PRENOM_PRESTATAIRE."</option>";
			}
			
		}

		$output = array("TYPE_BENEFICIAIRE_ID"=>$getdata['TYPE_BENEFICIAIRE_ID'],"PRESTATAIRE_ID" => $html);
		return $this->response->setJSON($output);
	}


	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $bindparams;
	}

	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
}
?>

