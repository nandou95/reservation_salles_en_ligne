<?php 
/**Joa-Kevin Iradukunda
*Titre: Modifier le prestataire
*Numero de telephone: (+257) 62 63 65 35
*WhatsApp: (+27) 61 436 6546
*Email: joa-kevin.iradukunda@mediabox.bi
*Date: 17 Janvier, 2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Modifier_Tache extends BaseController
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
	public function get_view($CODE_NOMENCLATURE_BUDGETAIRE_ID=null, $PTBA_TACHE_ID=null)
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		// get code numenclature budgetaire
		$where_code_num_budg = $CODE_NOMENCLATURE_BUDGETAIRE_ID ? "CODE_NOMENCLATURE_BUDGETAIRE_ID =".$CODE_NOMENCLATURE_BUDGETAIRE_ID : '1';
		$bind_code_num_budg=$this->getBindParms('CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE','inst_institutions_ligne_budgetaire',$where_code_num_budg,'CODE_NOMENCLATURE_BUDGETAIRE ASC');
		$data['code_num_budgs']= $this->ModelPs->getRequete($callpsreq,$bind_code_num_budg);

		// get tache
		$where_tache = $PTBA_TACHE_ID && $CODE_NOMENCLATURE_BUDGETAIRE_ID ? "PTBA_TACHE_ID =".$PTBA_TACHE_ID." AND CODE_NOMENCLATURE_BUDGETAIRE_ID = ".$CODE_NOMENCLATURE_BUDGETAIRE_ID : '1';
		$bind_tache=$this->getBindParms('PTBA_TACHE_ID,DESC_TACHE','ptba_tache',$where_tache,'PTBA_TACHE_ID ASC');
		$data['code_taches']= $this->ModelPs->getRequete($callpsreq,$bind_tache);

		//set DESC_TACHE si PTBA_TACHE_ID is defini
		$data['DESC_TACHE'] = $PTBA_TACHE_ID && $CODE_NOMENCLATURE_BUDGETAIRE_ID && count($data['code_taches']) == 1 ? $data['code_taches'][0]->DESC_TACHE : '' ;

		$data['CODE_NOMENCLATURE_BUDGETAIRE_ID'] = $CODE_NOMENCLATURE_BUDGETAIRE_ID;
    	$data['PTBA_TACHE_ID'] = $PTBA_TACHE_ID;

		return view('App\Modules\double_commande_new\Views\Modifier_Tache_View',$data);
	}

	//modifier la tache ptba
	public function update()
	{
		$db = db_connect();
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		$rules = [			
			'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PTBA_TACHE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'MODIFIER_TACHE' => [
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
			$CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
			$PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
			$MODIFIER_TACHE = $this->request->getPost('MODIFIER_TACHE');

			$table = "ptba_tache";
			$where="PTBA_TACHE_ID=".$PTBA_TACHE_ID;
			$data="DESC_TACHE='".$MODIFIER_TACHE."'";
			$this->update_all_table($table,$data,$where);

			//get the PTBA_TACHE_REVISE_ID from ptba_tache_revise
			$requetePtba = "SELECT PTBA_TACHE_REVISE_ID FROM ptba_tache_revise WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND DESC_TACHE = '".$MODIFIER_TACHE."'";
			$PTBA_TACHE_REVISE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePtba . '")')['PTBA_TACHE_REVISE_ID'] ?? 0;

			$table = "ptba_tache_revise";
			$where="PTBA_TACHE_REVISE_ID=".$PTBA_TACHE_REVISE_ID;
			$data="PTBA_TACHE_ID=".$PTBA_TACHE_ID;
			$this->update_all_table($table,$data,$where);

			$data=['message' => "".lang('messages_lang.corriger_message').""];
            session()->setFlashdata('alert', $data);
            return redirect('double_commande_new/Liste_croisement_ptba_ptba_revise');
		}
		else
		{
			return $this->get_view();
		}		
	}

	//fonction pour le view de modification
	public function get_view_revise($CODE_NOMENCLATURE_BUDGETAIRE_ID=null, $PTBA_TACHE_REVISE_ID=null)
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		// get code numenclature budgetaire
		$where_code_num_budg = $CODE_NOMENCLATURE_BUDGETAIRE_ID ? "CODE_NOMENCLATURE_BUDGETAIRE_ID =".$CODE_NOMENCLATURE_BUDGETAIRE_ID : '1';
		$bind_code_num_budg=$this->getBindParms('CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE','inst_institutions_ligne_budgetaire',$where_code_num_budg,'CODE_NOMENCLATURE_BUDGETAIRE ASC');
		$data['code_num_budgs']= $this->ModelPs->getRequete($callpsreq,$bind_code_num_budg);

		// get tache
		$where_tache = $PTBA_TACHE_REVISE_ID && $CODE_NOMENCLATURE_BUDGETAIRE_ID ? "PTBA_TACHE_REVISE_ID =".$PTBA_TACHE_REVISE_ID." AND CODE_NOMENCLATURE_BUDGETAIRE_ID = ".$CODE_NOMENCLATURE_BUDGETAIRE_ID : '1';
		$bind_tache=$this->getBindParms('PTBA_TACHE_REVISE_ID,DESC_TACHE','ptba_tache_revise',$where_tache,'PTBA_TACHE_REVISE_ID ASC');
		$data['code_taches']= $this->ModelPs->getRequete($callpsreq,$bind_tache);

		//set DESC_TACHE si PTBA_TACHE_REVISE_ID is defini
		$data['DESC_TACHE'] = $PTBA_TACHE_REVISE_ID && $CODE_NOMENCLATURE_BUDGETAIRE_ID && count($data['code_taches']) == 1 ? $data['code_taches'][0]->DESC_TACHE : '' ;

		$data['CODE_NOMENCLATURE_BUDGETAIRE_ID'] = $CODE_NOMENCLATURE_BUDGETAIRE_ID;
    	$data['PTBA_TACHE_REVISE_ID'] = $PTBA_TACHE_REVISE_ID;

		return view('App\Modules\double_commande_new\Views\Modifier_Tache_Revise_View',$data);
	}

	//modifier la tache ptba revise
	public function update_revise()
	{
		$db = db_connect();
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		$rules = [			
			'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PTBA_TACHE_REVISE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'MODIFIER_TACHE' => [
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
			$CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
			$PTBA_TACHE_REVISE_ID = $this->request->getPost('PTBA_TACHE_REVISE_ID');
			$MODIFIER_TACHE = $this->request->getPost('MODIFIER_TACHE');

			//get the PTBA_TACHE_ID from ptba_tache
			$requetePtba = "SELECT PTBA_TACHE_ID FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = '".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND DESC_TACHE = '".$MODIFIER_TACHE."'";
			$PTBA_TACHE_ID = $this->ModelPs->getRequeteOne('CALL `getTable`("' . $requetePtba . '")')['PTBA_TACHE_ID'] ?? 0;

			$table = "ptba_tache_revise";
			$where="PTBA_TACHE_REVISE_ID=".$PTBA_TACHE_REVISE_ID;
			$data="DESC_TACHE='".$MODIFIER_TACHE."', PTBA_TACHE_ID=".$PTBA_TACHE_ID;
			$this->update_all_table($table,$data,$where);

			$data=['message' => "".lang('messages_lang.corriger_message').""];
            session()->setFlashdata('alert', $data);
            return redirect('double_commande_new/Liste_croisement_ptba_ptba_revise');
		}
		else
		{
			return $this->get_view();
		}		
	}

	//recupere les taches du code
	public function get_taches($CODE_NOMENCLATURE_BUDGETAIRE_ID)
	{
		$callpsreq = "CALL getRequete(?,?,?,?);";

		//get taches PTBA
		$getTaches = $this->getBindParms('PTBA_TACHE_ID,DESC_TACHE','ptba_tache','CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID,'PTBA_TACHE_ID  ASC');
		$getTaches = $this->ModelPs->getRequete($callpsreq, $getTaches);

		$html='<option value="">Sélectionner</option>';
		foreach ($getTaches as $key)
		{
			$html.="<option value ='".$key->PTBA_TACHE_ID."'>".$key->DESC_TACHE."</option>";
		}

		//get taches PTBA Revise
		$getTachesRevise = $this->getBindParms('PTBA_TACHE_REVISE_ID,DESC_TACHE','ptba_tache_revise','CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID,'PTBA_TACHE_REVISE_ID  ASC');
		$getTachesRevise = $this->ModelPs->getRequete($callpsreq, $getTachesRevise);

		$html2='<option value="">Sélectionner</option>';
		foreach ($getTachesRevise as $key)
		{
			$html2.="<option value ='".$key->PTBA_TACHE_REVISE_ID."'>".$key->DESC_TACHE."</option>";
		}

		$output = array(
			"PTBA_TACHE_ID" => $html,
			"PTBA_TACHE_REVISE_ID" => $html2
		);
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

