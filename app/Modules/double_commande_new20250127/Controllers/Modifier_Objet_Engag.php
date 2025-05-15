<?php
/**
 * fais par: charbel
 * tache: Modifier objet d'engegement en cas d'erreur
 * date: 24/20/2024
 * email: charbel@mediabox.bi
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Modifier_Objet_Engag extends BaseController
{
	protected $session;
	protected $ModelPs;
	protected $library;
	protected $validation;

	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $bindparams;
	}

	public function index($value='')
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL getRequete(?,?,?,?);";
		// get BEs
		$bind_BEs=$this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID ,NUMERO_BON_ENGAGEMENT','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','NUMERO_BON_ENGAGEMENT IS NOT NULL AND INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.') AND (exec.COMMENTAIRE IS NOT NULL OR det.TITRE_CREANCE IS NOT NULL)','NUMERO_BON_ENGAGEMENT ASC');
		$data['BEs']= $this->ModelPs->getRequete($callpsreq,$bind_BEs);
		return view('App\Modules\double_commande_new\Views\Modifier_Objet_Engag_View',$data);
	}

	public function save()
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$rules = [			
			'EXECUTION_BUDGETAIRE_DETAIL_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
		];

		$TITRE_CREANCE=$this->request->getPost('TITRE_CREANCE');
		$COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
		$COMMENTAIRE=addslashes($COMMENTAIRE);
		$MOTIF_LIQUIDATION=$this->request->getPost('MOTIF_LIQUIDATION');
		$MOTIF_LIQUIDATION=addslashes($MOTIF_LIQUIDATION);
		if($TITRE_CREANCE=='' && $COMMENTAIRE=='' && $MOTIF_LIQUIDATION=='')
		{
		    $data=['message' => "Titre de créance , Objet d'engagement ou Motif de la liquidation ne peut pas être vide"];
            session()->setFlashdata('alert', $data);
            return $this->index();
		}

		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
			//update dans detail
			$table1='execution_budgetaire_tache_detail';
			$conditions1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
			
			if (!empty($TITRE_CREANCE)) 
			{
				$datatomodifie1='TITRE_CREANCE="'.$TITRE_CREANCE.'"';
				$this->update_all_table($table1,$datatomodifie1,$conditions1);
			}

			if(!empty($COMMENTAIRE))
			{
				$datatomodifie1='COMMENTAIRE="'.$COMMENTAIRE.'",MOTIF_LIQUIDATION="'.$COMMENTAIRE.'"';
				$this->update_all_table($table1,$datatomodifie1,$conditions1);
			}

			if(!empty($MOTIF_LIQUIDATION))
			{
				$datatomodifie1='MOTIF_LIQUIDATION="'.$MOTIF_LIQUIDATION.'"';
				$this->update_all_table($table1,$datatomodifie1,$conditions1);
			}

			$callpsreq = "CALL getRequete(?,?,?,?);";
			$exec=$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'exec.EXECUTION_BUDGETAIRE_ID ASC');
			$exec= $this->ModelPs->getRequeteOne($callpsreq,$exec);

			//update dans execution budgetaire
			$table2='execution_budgetaire';
			$datatomodifie2='COMMENTAIRE="'.$COMMENTAIRE.'"';
			$conditions2='EXECUTION_BUDGETAIRE_ID='.$exec['EXECUTION_BUDGETAIRE_ID'];
			$this->update_all_table($table2,$datatomodifie2,$conditions2);

			$data=['message' => "".lang('messages_lang.corriger_message').""];
            session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait');
		}
		else
		{
			return $this->index();
		}
	}

	//recupere les infos du BE
	public function get_info($EXECUTION_BUDGETAIRE_DETAIL_ID)
	{
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$getdata = $this->getBindParms('execution_budgetaire.COMMENTAIRE,TITRE_CREANCE,MOTIF_LIQUIDATION','execution_budgetaire JOIN execution_budgetaire_tache_detail ON execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID','execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'execution_budgetaire.EXECUTION_BUDGETAIRE_ID ASC');
		$getdata = $this->ModelPs->getRequeteOne($callpsreq, $getdata);

		$output = array("TITRE_CREANCE"=>$getdata['TITRE_CREANCE'],"COMMENTAIRE" => $getdata['COMMENTAIRE'],"MOTIF_LIQUIDATION"=>$getdata['MOTIF_LIQUIDATION']);
		return $this->response->setJSON($output);
	}

	public function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
}