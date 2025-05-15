<?php
/**
 * Alain Charbel NDERAGAKURA
 * interface de decaissement cas salaire
 * date: le 16/09/2024
 * charbel@mediabox.bi
 * tel:62003522
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;

class Decaissement_Salaire extends BaseController
{
	protected $ModelPs;
	protected $validation;

	function __construct()
	{
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	//fonction pour afficher l'interface de decaissement salaire
	function index_dec_salaire($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
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

		$id_etap = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.MONTANT_PAIEMENT,td.EXECUTION_BUDGETAIRE_DETAIL_ID,td.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,td.EXECUTION_BUDGETAIRE_ID','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_etape_double_commande etape ON etape.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID', 'MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
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
	    		if ($prof_id == $value->PROFIL_ID || $prof_id ==1)
	    		{
	    			$data['bif_decais']=empty($data['etape']['MONTANT_PAIEMENT']) ? 0 :$data['etape']['MONTANT_PAIEMENT']; 			

	    			$callpsreq = "CALL `getRequete`(?,?,?,?);";     
	    			$bind_date_histo = $this->getBindParms('DATE_TRANSMISSION,ETAPE_DOUBLE_COMMANDE_ID','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['etape']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
	    			$bind_date_histo = str_replace('\\','',$bind_date_histo);
	    			$data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);
	    			
							//Requete pour les operation
	    			$get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION=2','ID_OPERATION ASC');
	    			$get_oper = str_replace('\\', '', $get_oper);
	    			$data['operation'] = $this->ModelPs->getRequete($psgetrequete, $get_oper);

	    			//Récuperer les motifs
			          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
			          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

	    			return view('App\Modules\double_commande_new\Views\Decaissement_Salaire_Add_View', $data);
	    		}
	    		 
	    	}
	        return redirect('Login_Ptba/homepage'); 
	    }
    return redirect('Login_Ptba/homepage');
	}

	//fonction pour inserer dans la l'analyse et decaissement salaire
	function save_dec_salaire()
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
		$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
		$activite_new_request = $this->ModelPs->getRequeteOne($psgetrequete, $this->getBindParms('EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_titre_decaissement', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, '1'));
		$EXECUTION_BUDGETAIRE_ID = $activite_new_request['EXECUTION_BUDGETAIRE_ID'];
		
		$id_etape = $this->request->getPost('etape');
		$PAIEMENT_DECAISSEMENT = $this->request->getPost('PAIEMENT_DECAISSEMENT');
		$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
		$DATE_DECAISSEMENT = $this->request->getPost('DATE_DECAISSEMENT');
		$DATE_INSERTION = date('Y-m-d h:i:s');
		$MONTANT_DECAISSE = str_replace(' ', '', $this->request->getPost('MONTANT_DECAISSE'));

		//Operation et motif
		$ID_OPERATION = $this->request->getPost('ID_OPERATION');
		$selected_motif_id = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
		$DATE_COUR_DEVISE = $this->request->getPost('DATE_COUR_DEVISE');
		//get montant existant dans execution_budgetaire
	    $psgetrequete = "CALL getRequete(?,?,?,?);";
	    $mont_dec_exista = $this->getBindParms('DECAISSEMENT','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID,'1 DESC');
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

		$condition1 = 'EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		$table_exec = 'execution_budgetaire';
		$table_td = 'execution_budgetaire_titre_decaissement';
		$condition3='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
		$this->validation->setRules($rules);

		if ($this->validation->withRequest($this->request)->run())
		{
			if ($ID_OPERATION == 2)
			{
				//update dans execution 
        $nouveau_mont1=floatval($montant_dec['DECAISSEMENT'])+$MONTANT_DECAISSE;
        $table_exec1='execution_budgetaire';
        $datatomodifie_exec1= 'DECAISSEMENT="'.$nouveau_mont1.'"';
        $this->update_all_table($table_exec,$datatomodifie_exec1,$condition1);

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
				$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $montant = $MONTANT_DECAISSE;
				$data_td='MONTANT_DECAISSEMENT='.$MONTANT_DECAISSE.',DATE_DECAISSEMENT="'.$DATE_DECAISSEMENT.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
				$this->update_all_table($table_td, $data_td, $condition3);

				//récuperer les etapes et mouvements
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$id_etape,'ETAPE_DOUBLE_COMMANDE_ID ASC');
				$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

				$table_histo = 'execution_budgetaire_tache_detail_histo';
				$columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_TRANSMISSION";
				$data_histo = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . ',' . $user_id . ',' . $get_step['ETAPE_DOUBLE_COMMANDE_ID'] . ',"' . $DATE_INSERTION . '","' . $DATE_TRANSMISSION . '"';
				$this->save_all_table($table_histo, $columsinsert, $data_histo);

				$data = ['message' => lang('messages_lang.message_success')];
				session()->setFlashdata('alert', $data);
				return redirect('double_commande_new/Decaissement_Salaire_Liste/vue_decaiss_faire');
			}	
		}
		else
		{
			return $this->index_dec(md5($id_racc_detail));
		}
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

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
  /* Fin Gestion insertion */

  //Update
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
}