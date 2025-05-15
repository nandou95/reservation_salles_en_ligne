<?php

/**
 * Alain Charbel NDERAGAKURA
 * interface du TD signé par le ministre
 * date: le 31/10/2023
 * charbel@mediabox.bi
 * tel:62003522
 */

namespace  App\Modules\double_commande_new\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;

class Phase_comptable_etape789 extends BaseController
{
	protected $ModelPs;
	protected $validation;

	function __construct()
	{
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}
	//fonction pour afficher l'interface de TD signe par le ministre etape 7 modifier par jemapess MUGISHA
	function index_td($EXECUTION_BUDGETAIRE_DETAIL_ID)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		$get_hist=$this->getBindParms('DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID )="'.$EXECUTION_BUDGETAIRE_DETAIL_ID.'"' ,'DATE_INSERTION DESC');
		$get_hist=str_replace('\\','',$get_hist);
		$data['hist']=$this->ModelPs->getRequeteOne($psgetrequete,$get_hist);
		
		$id_etap = $this->getBindParms('dc.ETAPE_DOUBLE_COMMANDE_ID,act.EXECUTION_BUDGETAIRE_ID,DESC_ETAPE_DOUBLE_COMMANDE,EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail act JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=act.ETAPE_DOUBLE_COMMANDE_ID', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXECUTION_BUDGETAIRE_DETAIL_ID.'"', 'dc.ETAPE_DOUBLE_COMMANDE_ID ASC');
		$id_etap=str_replace('\\','',$id_etap);
		$data['etape']=$this->ModelPs->getRequeteOne($psgetrequete, $id_etap);
		$EXECUTION_BUDGETAIRE_ID=$data['etape']['EXECUTION_BUDGETAIRE_ID'];

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['etape']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
		$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
		{
			foreach ($getProfil as $value)
			{
				if ($prof_id == $value->PROFIL_ID || $prof_id==1)
				{
					$detail=$this->detail_new($EXECUTION_BUDGETAIRE_DETAIL_ID);
					$data['get_info']=$detail['get_info'];
					$data['montantvote']=$detail['montantvote'];
					$data['creditVote']=$detail['creditVote'];
					$data['montant_reserve']=$detail['montant_reserve'];	
					return view('App\Modules\double_commande_new\Views\Td_Signe_Ministre_View',$data);
				}
			}
			return redirect('Login_Ptba/homepage');
		}
		else
		{
			return redirect('Login_Ptba/homepage');
		}
	}

	//fonction pour inserer Td signe par le ministre etape 7
	function insert_td()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
		
		$id_racc=$this->request->getPost('id_raccrochage');
		$id_etape=$this->request->getPost('etape');

		$rules = [
			'DATE_RECEPTION' => [
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
			],
			'DATE_SIGNATURE' => [
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
			$DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
			$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
			$DATE_SIGNATURE=$this->request->getPost('DATE_SIGNATURE');
			$DATE_INSERTION=date('Y-m-d h:i:s');
					//récuperer les etapes et mouvements
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$id_etape,'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
			
			// $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

			$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
			$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

			// $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
			// $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

			// $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
			$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

			//insertion dans l'historique_detail
			$table_histo='execution_budgetaire_tache_detail_histo';
			$columsinsert="EXECUTION_BUDGETAIRE_DETAIL_ID ,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
			$data_histo=$id_racc.','.$user_id.','.$get_step['ETAPE_DOUBLE_COMMANDE_ID'].',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
			$this->save_all_table($table_histo,$columsinsert,$data_histo);

			//modification dans la table raccrochage
			$table='execution_budgetaire_tache_detail';
			$data_racc='DATE_SIGNATURE_TD_MINISTRE="'.$DATE_SIGNATURE.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
			$conditions='EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_racc ;
			$this->update_all_table($table,$data_racc,$conditions);

			$data=['message' => "".lang('messages_lang.message_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Liste_Paiement');
		}else
		{
			return $this->index_td($id_racc);
		}
	}

	//fonction pour afficher l'interface de decaissement etape 9
	function index_dec($EXECUTION_BUDGETAIRE_DETAIL_ID)
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		$get_hist = $this->getBindParms('DATE_TRANSMISSION, EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="' . $EXECUTION_BUDGETAIRE_DETAIL_ID . '"', 'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
		$get_hist = str_replace('\\', '', $get_hist);
		$data['hist'] = $this->ModelPs->getRequeteOne($psgetrequete, $get_hist);

		$id_etap = $this->getBindParms('det.MONTANT_DECAISSEMENT,det.MONTANT_DECAISSEMENT_DEVISE,det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.MONTANT_PAIEMENT ,det.ETAPE_DOUBLE_COMMANDE_ID,exec.DEVISE_TYPE_ID,DESC_ETAPE_DOUBLE_COMMANDE,det.MONTANT_PAIEMENT_DEVISE,exec.EXECUTION_BUDGETAIRE_ID, exec.ENG_BUDGETAIRE_DEVISE', 'execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande comm ON comm.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID', 'MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXECUTION_BUDGETAIRE_DETAIL_ID.'"', 'det.ETAPE_DOUBLE_COMMANDE_ID ASC');
		$id_etap = str_replace('\\', '', $id_etap);
		$data['etape'] = $this->ModelPs->getRequeteOne($psgetrequete, $id_etap);

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['etape']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
    	foreach ($getProfil as $value)
    	{
    		if ($prof_id == $value->PROFIL_ID)
    		{
    			// $bif_decais = (floatval($data['etape']['MONTANT_PAIEMENT']) < floatval($data['etape']['MONTANT_DECAISSEMENT'])) ? 0 : floatval($data['etape']['MONTANT_PAIEMENT']) - floatval($data['etape']['MONTANT_DECAISSEMENT']); 

    			// $devise_decais = (floatval($data['etape']['MONTANT_PAIEMENT_DEVISE']) < floatval($data['etape']['MONTANT_DECAISSEMENT_DEVISE'])) ? 0 : floatval($data['etape']['MONTANT_PAIEMENT_DEVISE']) - floatval($data['etape']['MONTANT_DECAISSEMENT_DEVISE']) ;

					//$data['mont_dec'] = ($data['etape']['DEVISE_TYPE_ID'] == 1) ? $bif_decais : $devise_decais ;

    			$data['bif_decais']=empty($data['etape']['MONTANT_PAIEMENT']) ? 0 :$data['etape']['MONTANT_PAIEMENT'];
    			$data['devise_decais']=empty($data['etape']['MONTANT_PAIEMENT_DEVISE']) ? 0 :$data['etape']['MONTANT_PAIEMENT_DEVISE'];    			

    			$callpsreq = "CALL `getRequete`(?,?,?,?);";     
    			$bind_date_histo = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['etape']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
    			$bind_date_histo = str_replace('\\','',$bind_date_histo);
    			$data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

    			$data['detai_taux_echange_id'] = $data['etape']['DEVISE_TYPE_ID'];
    			$EXECUTION_BUDGETAIRE_ID = $data['etape']['EXECUTION_BUDGETAIRE_ID'];
    			
						//Requete pour les operation
    			$get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', 'ID_OPERATION NOT IN(1,3)', 'ID_OPERATION ASC');
    			$get_oper = str_replace('\\', '', $get_oper);
    			$data['operation'] = $this->ModelPs->getRequete($psgetrequete, $get_oper);

						//Récuperation de l'étape précedent
    			$bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXECUTION_BUDGETAIRE_DETAIL_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
    			$bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
    			$etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

						//get type_analyse_motif
    			$motif_rejet  = "SELECT DISTINCT verif.TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif type_motif JOIN execution_budgetaire_histo_operation_verification_motif verif ON type_motif.TYPE_ANALYSE_MOTIF_ID=verif.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXECUTION_BUDGETAIRE_DETAIL_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_DOUBLE_COMMANDE_ID']."";
    			$motif_rejetRqt = 'CALL getTable("' . $motif_rejet . '");';
    			$data['motif'] = $this->ModelPs->getRequete($motif_rejetRqt);

					$detail = $this->detail_new($EXECUTION_BUDGETAIRE_DETAIL_ID);
    			$data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
    			return view('App\Modules\double_commande_new\Views\Rec_Analyse_Decaisse_Transmettre_View', $data);
    		}
    		 
    	}
        return redirect('Login_Ptba/homepage'); 
    }
    return redirect('Login_Ptba/homepage');
	}

	//fonction pour inserer dans la l'analyse et decaissement etape 9
	function save_dec()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
		
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$id_racc_detail = $this->request->getPost('id_raccrochage');
		$activite_new_request = $this->ModelPs->getRequeteOne($psgetrequete, $this->getBindParms('EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail', 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_racc_detail, '1'));
		$id_racc = $activite_new_request['EXECUTION_BUDGETAIRE_ID'];
		
		$id_etape = $this->request->getPost('etape');
		$TAUX_ECHANGE_ID = $this->request->getPost('TAUX_ECHANGE_ID');
		$MONTANT_DEVISE_PAIEMENT = $this->request->getPost('MONTANT_DEVISE_PAIEMENT');
		$PAIEMENT_DECAISSEMENT = $this->request->getPost('PAIEMENT_DECAISSEMENT');
		$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
		$DATE_DECAISSEMENT = $this->request->getPost('DATE_DECAISSEMENT');
		$DATE_INSERTION = date('Y-m-d h:i:s');
		$COUR_DECAISSEMENT_DEVISE = $this->request->getPost('COUR_DECAISSEMENT_DEVISE');
		$MONTANT_DECAISSE = str_replace(' ', '', $this->request->getPost('MONTANT_DECAISSE'));

		$MONTANT_DECAISSE_DEVISE = str_replace(' ','',$this->request->getPost('MONTANT_DECAISSE_ID'));

		//Operation et motif
		$ID_OPERATION = $this->request->getPost('ID_OPERATION');
		$selected_motif_id = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
		$DATE_COUR_DEVISE = $this->request->getPost('DATE_COUR_DEVISE');
		//get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_dec_exista = $this->getBindParms('DECAISSEMENT,DECAISSEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$id_racc,'1 DESC');
    $montant_dec = $this->ModelPs->getRequeteOne($psgetrequete, $mont_dec_exista);

		$rules = [
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'DATE_DECAISSEMENT' => [
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
			]
		];

		$condition2 = 'EXECUTION_BUDGETAIRE_ID='.$id_racc;
		$table_exec = 'execution_budgetaire';
		$condition3 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_racc_detail;
		$table_activite3 = 'execution_budgetaire_tache_detail';
		$this->validation->setRules($rules);

		if ($this->validation->withRequest($this->request)->run())
		{
			if ($ID_OPERATION == 2)
			{		
				$montant ='';
				if ($TAUX_ECHANGE_ID == 1)
				{
					$montant = $MONTANT_DECAISSE;
					$data_activite3='MONTANT_DECAISSEMENT='.$MONTANT_DECAISSE.',DATE_DECAISSENMENT="'.$DATE_DECAISSEMENT.'"';
					$this->update_all_table($table_activite3, $data_activite3, $condition3);		
					//update dans execution
          $nouveau_mont1=floatval($montant_dec['DECAISSEMENT'])+$MONTANT_DECAISSE;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id_racc;
          $datatomodifie_exec1= 'DECAISSEMENT="'.$nouveau_mont1.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);			
				}
				else
				{					
					//Calcule du montant en bif
					$taux_echange_request = $this->getBindParms('DEVISE_TYPE_HISTO_ID ,TAUX', 'devise_type_hist', 'DEVISE_TYPE_ID='.$TAUX_ECHANGE_ID.' AND IS_ACTIVE=1', 'TAUX ASC');
					$gettaux=$this->ModelPs->getRequeteOne($psgetrequete, $taux_echange_request);
					
    			$taux = $gettaux['TAUX'];
    			$id_dev_jour=$gettaux['DEVISE_TYPE_HISTO_ID'];
    			$montant = floatval($montant_dec['DECAISSEMENT'])+floatval($MONTANT_DECAISSE);
					$data_activite3='MONTANT_DECAISSEMENT='.$montant.',MONTANT_DECAISSEMENT_DEVISE='.$MONTANT_DECAISSE_DEVISE.',DATE_DECAISSENMENT="'.$DATE_DECAISSEMENT.'",DEVISE_TYPE_HISTO_DEC_ID='.$id_dev_jour;
					$this->update_all_table($table_activite3, $data_activite3, $condition3);

					//update dans execution
          $nouveau_mont1=floatval($montant_dec['DECAISSEMENT_DEVISE'])+$MONTANT_DECAISSE_DEVISE;
          $nouveau_bif=floatval($montant_dec['DECAISSEMENT'])+$montant;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id_racc;
          $datatomodifie_exec1= 'DECAISSEMENT_DEVISE="'.$nouveau_mont1.'",DECAISSEMENT="'.$nouveau_bif.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
				}

				$this->gestion_retour_ptba($id_racc_detail,$montant);

				//récuperer les etapes et mouvements
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$id_etape,'ETAPE_DOUBLE_COMMANDE_ID ASC');
				$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $id_etape, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
				$table_histo = 'execution_budgetaire_tache_detail_histo';
				$columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_TRANSMISSION";
				$data_histo = $id_racc_detail . ',' . $user_id . ',' . $get_step['ETAPE_DOUBLE_COMMANDE_ID'] . ',"' . $DATE_INSERTION . '","' . $DATE_TRANSMISSION . '"';
				$this->save_all_table($table_histo, $columsinsert, $data_histo);

				//modification dans la table raccrochage
				$table = ' execution_budgetaire_tache_detail ';
				$data_racc = 'ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID . ' ';
				$conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_racc_detail;
				$this->update_all_table($table, $data_racc, $conditions);

				$data = ['message' => lang('messages_lang.message_success')];
				session()->setFlashdata('alert', $data);
				return redirect('double_commande_new/Liste_Decaissement');
			} 			
		}
		else
		{
			return $this->index_dec(md5($id_racc_detail));
		}
	}

	//fonction pour inserer dans les colonnes souhaites
	public function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $id = $result['id'];
	}
	/* update table */
	function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	// pour uploader les documents
	public function uploadFile($fieldName, $folder, $prefix = ''): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';

		$file = $this->request->getFile($fieldName);

		$folderPath = ROOTPATH . 'public/uploads/' . $folder;
		if (!is_dir($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		if ($file->isValid() && !$file->hasMoved()) {
			$newName = $prefix . '_' . uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
		}
		return $path;
	}
}