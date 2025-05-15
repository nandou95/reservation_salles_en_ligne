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

	function index_td($id = 0)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		$get_hist=$this->getBindParms('DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"' ,'DATE_INSERTION DESC');
		$get_hist=str_replace('\\','',$get_hist);
		$data['hist']=$this->ModelPs->getRequeteOne($psgetrequete,$get_hist);
		
		$id_etap = $this->getBindParms('td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dc.ETAPE_DOUBLE_COMMANDE_ID,act.EXECUTION_BUDGETAIRE_ID,DESC_ETAPE_DOUBLE_COMMANDE,act.EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail act JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=act.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'dc.ETAPE_DOUBLE_COMMANDE_ID ASC');
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
					//Requete pour les operation
          $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', '1', 'ID_OPERATION ASC');
          $get_oper = str_replace('\\', '', $get_oper);
          $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);
          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);
					$detail=$this->detail_new($id);
					$data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];	
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


	function insert_td()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
		
		$id_exec_titr_dec=$this->request->getPost('id_exec_titr_dec');
		$id_racc=$this->request->getPost('id_raccrochage');
		$id_etape=$this->request->getPost('etape');
		$DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
		$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
		$DATE_SIGNATURE=$this->request->getPost('DATE_SIGNATURE');
		$DATE_INSERTION=date('Y-m-d h:i:s');
		$ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

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
			'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
		];

		if($ID_OPERATION == 1 || $ID_OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }
    else if ($ID_OPERATION == 2)
    {
    	$rules['DATE_SIGNATURE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			if($ID_OPERATION == 2)
			{
				//récuperer les etapes et mouvements
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_SALAIRE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

				//modification dans la table titre_decaissement
				$table='execution_budgetaire_titre_decaissement';
				$data_racc='DATE_SIGNATURE_TD_MINISTRE="'.$DATE_SIGNATURE.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
				$conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec ;
				$this->update_all_table($table,$data_racc,$conditions);

				//insertion dans l'historique_detail
				$table_histo='execution_budgetaire_tache_detail_histo';
				$columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
				$data_histo=$id_exec_titr_dec.','.$user_id.','.$id_etape.',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
				$this->save_all_table($table_histo,$columsinsert,$data_histo);
			}
			elseif ($ID_OPERATION == 1)
			{
				//récuperer les etapes et mouvements
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_CORRECTION=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

				//modification dans la table titre_decaissement
				$table='execution_budgetaire_titre_decaissement';
				$data_racc='ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
				$conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec ;
				$this->update_all_table($table,$data_racc,$conditions);

				//insertion dans l'historique_detail
				$table_histo='execution_budgetaire_tache_detail_histo';
				$columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
				$data_histo=$id_exec_titr_dec.','.$user_id.','.$id_etape.',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
				$this->save_all_table($table_histo,$columsinsert,$data_histo);

				//Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $id_etape . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
			}
			elseif ($ID_OPERATION == 3)
			{
				//récuperer les etapes et mouvements
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_CORRECTION=2 AND IS_SALAIRE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

				$callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,DECAISSEMENT,DECAISSEMENT_DEVISE,exec.DEVISE_TYPE_ID,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $EXEC_BUDGET_RAC_ID = $get_mont_pay['EXECUTION_BUDGETAIRE_ID'];

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;

        if($get_mont_pay['DECAISSEMENT'] > 0)
        {
          //print_r($get_mont_pay);exit();
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $update_dec_mont_devise = floatval($get_mont_pay['DECAISSEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT_DEVISE']);

            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont.', DECAISSEMENT_DEVISE='.$update_dec_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);

            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
            
          }
        }

				//modification dans la table raccrochage
				$table='execution_budgetaire_titre_decaissement';
				$data_racc='ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_DECAISSEMENT=0, MONTANT_DECAISSEMENT_DEVISE=0';
				$conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ='.$id_exec_titr_dec.'';
				$this->update_all_table($table,$data_racc,$conditions);

				//insertion dans l'historique_detail
				$table_histo='execution_budgetaire_tache_detail_histo';
				$columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
				$data_histo=$id_exec_titr_dec.','.$user_id.','.$id_etape.',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
				$this->save_all_table($table_histo,$columsinsert,$data_histo);

				//Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $id_etape . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
			}
			
			$data=['message' => "".lang('messages_lang.message_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Liste_Paiement/vue_sign_ministre');
		}
		else
		{
			return $this->index_td($id_exec_titr_dec);
		}
	}


	//fonction pour afficher l'interface de decaissement etape 29
	function index_dec($id = 0)
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

		$get_hist = $this->getBindParms('DATE_TRANSMISSION, EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="' . $id . '"', 'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
		$get_hist = str_replace('\\', '', $get_hist);
		$data['hist'] = $this->ModelPs->getRequeteOne($psgetrequete, $get_hist);

		$id_etap = $this->getBindParms('td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.MONTANT_DECAISSEMENT,td.MONTANT_DECAISSEMENT_DEVISE,det.EXECUTION_BUDGETAIRE_DETAIL_ID, td.MONTANT_PAIEMENT ,td.ETAPE_DOUBLE_COMMANDE_ID,exec.DEVISE_TYPE_ID,DESC_ETAPE_DOUBLE_COMMANDE,td.MONTANT_PAIEMENT_DEVISE,exec.EXECUTION_BUDGETAIRE_ID, exec.ENG_BUDGETAIRE_DEVISE', 'execution_budgetaire exec JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande comm ON comm.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID', 'MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'td.ETAPE_DOUBLE_COMMANDE_ID ASC');
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

    			$data['bif_decais']=empty($data['etape']['MONTANT_PAIEMENT']) ? 0 :$data['etape']['MONTANT_PAIEMENT'];
    			$data['devise_decais']=empty($data['etape']['MONTANT_PAIEMENT_DEVISE']) ? 0 :$data['etape']['MONTANT_PAIEMENT_DEVISE'];    			

    			$callpsreq = "CALL `getRequete`(?,?,?,?);";     
    			$bind_date_histo = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['etape']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
    			$bind_date_histo = str_replace('\\','',$bind_date_histo);
    			$data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);
    			$etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

    			$data['detai_taux_echange_id'] = $data['etape']['DEVISE_TYPE_ID'];
    			$EXECUTION_BUDGETAIRE_ID = $data['etape']['EXECUTION_BUDGETAIRE_ID'];
    			
					//Requete pour les operation
    			$get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','1','ID_OPERATION ASC');
    			$get_oper = str_replace('\\', '', $get_oper);
    			$data['operation'] = $this->ModelPs->getRequete($psgetrequete, $get_oper);

    			//Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

					$detail = $this->detail_new($id);
    			$data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
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
		$id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
		$id_detail = $this->request->getPost('id_detail');
		$activite_new_request = $this->ModelPs->getRequeteOne($psgetrequete, $this->getBindParms('EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail', 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_detail, '1'));
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
			'ID_OPERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			]
		];

		if ($ID_OPERATION == 1 || $ID_OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    } else {

      $rules['DATE_DECAISSEMENT'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

		$condition2 = 'EXECUTION_BUDGETAIRE_ID='.$id_racc;
		$table_exec = 'execution_budgetaire';
		$condition3 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_detail;
		$table_activite3 = 'execution_budgetaire_tache_detail';
		$cond_titr_dec = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
		$table_titr_dec = 'execution_budgetaire_titre_decaissement';
		$this->validation->setRules($rules);

		if ($this->validation->withRequest($this->request)->run())
		{
			if ($ID_OPERATION == 2)
			{		
				$montant ='';
				if ($TAUX_ECHANGE_ID == 1)
				{
					$montant = $MONTANT_DECAISSE;
					$data_titr_dec='MONTANT_DECAISSEMENT='.$MONTANT_DECAISSE.',DATE_DECAISSEMENT="'.$DATE_DECAISSEMENT.'"';
					$this->update_all_table($table_titr_dec, $data_titr_dec, $cond_titr_dec);		
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
					
					$data_titr_dec='MONTANT_DECAISSEMENT='.$montant.',MONTANT_DECAISSEMENT_DEVISE='.$MONTANT_DECAISSE_DEVISE.',DATE_DECAISSENMENT="'.$DATE_DECAISSEMENT.'",DEVISE_TYPE_HISTO_DEC_ID='.$id_dev_jour;
					$this->update_all_table($table_titr_dec, $data_titr_dec, $cond_titr_dec);

					//update dans execution
          $nouveau_mont1=floatval($montant_dec['DECAISSEMENT_DEVISE'])+$MONTANT_DECAISSE_DEVISE;
          $nouveau_bif=floatval($montant_dec['DECAISSEMENT'])+$montant;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id_racc;
          $datatomodifie_exec1= 'DECAISSEMENT_DEVISE="'.$nouveau_mont1.'",DECAISSEMENT="'.$nouveau_bif.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
				}

				//récuperer les etapes
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
				
				//Insertion dans historique
				$table_histo = 'execution_budgetaire_tache_detail_histo';
				$columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_TRANSMISSION";
				$data_histo = $id_exec_titr_dec.','.$user_id.','.$id_etape.',"'. $DATE_INSERTION.'","'.$DATE_TRANSMISSION.'"';
				$this->save_all_table($table_histo, $columsinsert, $data_histo);

				//modification dans la table raccrochage
				$table = 'execution_budgetaire_titre_decaissement';
				$data_dec = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
				$conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
				$this->update_all_table($table, $data_dec, $conditions);

				$data = ['message' => lang('messages_lang.message_success')];
				session()->setFlashdata('alert', $data);
				return redirect('double_commande_new/Liste_Decaissement');
			}
			elseif($ID_OPERATION == 1)
			{
				$psgetrequete = "CALL getRequete(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config config JOIN execution_budgetaire_etape_double_commande step ON step.ETAPE_DOUBLE_COMMANDE_ID=config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='.$id_etape.' AND IS_CORRECTION=1 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $update_table_details = "execution_budgetaire_titre_decaissement";
        $conditions5 = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec;
        $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

       	//insertion des motifs
	      if (!empty($selected_motif_id)) {
	        foreach ($selected_motif_id as $an) {
	          $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
	          $datacolumsinsert = $an . "," . $id_etape . "," . $id_exec_titr_dec . "";
	          $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
	        }
	      }
	      $historique_table = "execution_budgetaire_tache_detail_histo";
	      $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_TRANSMISSION";
	      $datatoinsert_histo_op = "'" . $id_exec_titr_dec . "','" . $user_id . "','" . $id_etape . "','" . $DATE_INSERTION . "','".$DATE_TRANSMISSION."'";
	      $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);

	      $data = ['message' => lang('messages_lang.message_success')];
				session()->setFlashdata('alert', $data);
				return redirect('double_commande_new/Liste_Decaissement'); 
			}
			elseif($ID_OPERATION == 3)
			{
				$psgetrequete = "CALL getRequete(?,?,?,?);";
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config config JOIN execution_budgetaire_etape_double_commande step ON step.ETAPE_DOUBLE_COMMANDE_ID=config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='.$id_etape.' AND IS_CORRECTION=2 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,DECAISSEMENT,DECAISSEMENT_DEVISE,exec.DEVISE_TYPE_ID,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $EXEC_BUDGET_RAC_ID = $get_mont_pay['EXECUTION_BUDGETAIRE_ID'];

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;

        if($get_mont_pay['DECAISSEMENT'] > 0)
        {
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $update_dec_mont_devise = floatval($get_mont_pay['DECAISSEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT_DEVISE']);

            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont.', DECAISSEMENT_DEVISE='.$update_dec_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
            
          }
        }
        

        $update_table_details = "execution_budgetaire_titre_decaissement";
        $conditions5 = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec;
        $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_DECAISSEMENT=0, MONTANT_DECAISSEMENT_DEVISE=0';
        $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
       	//insertion des motifs
	      if (!empty($selected_motif_id)) {
	        foreach ($selected_motif_id as $an) {
	          $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
	          $datacolumsinsert = $an . "," . $id_etape . "," . $id_exec_titr_dec . "";
	          $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
	        }
	      }
	      $historique_table = "execution_budgetaire_tache_detail_histo";
	      $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_TRANSMISSION";
	      $datatoinsert_histo_op = "'" . $id_exec_titr_dec . "','" . $user_id . "','" . $id_etape . "','" . $DATE_INSERTION . "','".$DATE_TRANSMISSION."'";
	      $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);

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