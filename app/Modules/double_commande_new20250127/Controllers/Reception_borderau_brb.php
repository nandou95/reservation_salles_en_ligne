<?php

/**Eric SINDAYIGAYA
 *Titre: Reception du bordereau de transmission brb
 *Numero de telephone: +257 62 04 03 00
 *WhatsApp: +257 62 04 03 00
 *Email pro: sinda.eric@mediabox.bi
 *Email pers: ericjamesbarinako33@gmail.com
 *Date: 22 jan 2024
 **/

namespace  App\Modules\double_commande_new\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;

class Reception_borderau_brb extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function uploadFile($fieldName = NULL, $folder = NULL, $prefix = NULL): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';

		$file = $this->request->getFile($fieldName);

		if ($file->isValid() && !$file->hasMoved()) {
			$newName = uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
		}
		return $path;
	}

	public function index($value = '')
	{
		$data = $this->urichk();
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		return view('App\Modules\double_commande_new\Views\Reception_bordereau_brb_list_view', $data);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
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

	//Debut de la liste du numero de bordereau de transmission
	public function list_bordereau_transmission(string $bordereau_transmission_id)
	{
		$data = $this->urichk();
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
		// print_r($bordereau_transmission_id);die();
		//-id detail -
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$get_id_detail = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = bon.EXECUTION_BUDGETAIRE_DETAIL_ID', 'MD5(bon.BORDEREAU_TRANSMISSION_ID)="'.$bordereau_transmission_id.'"', 'bon.EXECUTION_BUDGETAIRE_DETAIL_ID');
		$get_id_detail = str_replace('\\', '', $get_id_detail);
		$id_detail = $this->ModelPs->getRequeteOne($callpsreq, $get_id_detail);
		$exe_budg_racc_id = md5($id_detail['EXECUTION_BUDGETAIRE_DETAIL_ID']);
		$EXECUTION_BUDGETAIRE_ID = md5($id_detail['EXECUTION_BUDGETAIRE_ID']);

		//Recuperation du statut operation bordereau transmission
		$requetebase3 = "SELECT STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID,DESC_STATUT_OPERATION_BORDEREAU_TRANSMISSION
		FROM statut_operation_bordereau_transmission WHERE 
		STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = 2";
		$fetch_data3 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase3 . "')");
		$data['statut_operation_bordereau'] = $fetch_data3;

		// //Titre de la page
		$bindparamsetap = $this->getBindParms('det.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT','execution_budgetaire_etape_double_commande commande JOIN execution_budgetaire_tache_detail det ON det.ETAPE_DOUBLE_COMMANDE_ID=commande.ETAPE_DOUBLE_COMMANDE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$exe_budg_racc_id.'"','1 DESC');
		$bindparamsetapes = str_replace("\\", "", $bindparamsetap);
		$data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['etapes']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
        	if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
          	//Recuperation de l'id de l'etape et le numero de bordereau
          	$requetebase1 = "SELECT trans.BORDEREAU_TRANSMISSION_ID, trans.NUMERO_BORDEREAU_TRANSMISSION,det.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_bordereau_transmission trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.BORDEREAU_TRANSMISSION_ID =trans.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID  WHERE md5(trans.BORDEREAU_TRANSMISSION_ID) = \'" . $bordereau_transmission_id . "\'";
          	$fetch_data1 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase1 . "')");

          	$ID_ETAPE_COMMANDE = $fetch_data1['ETAPE_DOUBLE_COMMANDE_ID'];
          	$NUM_BORDEREAU = $fetch_data1["NUMERO_BORDEREAU_TRANSMISSION"];
          	$data['etape_commande'] = $fetch_data1;

          	$data['id_etape_commande'] = $ID_ETAPE_COMMANDE;
          	$data['numero_bordereau'] = $NUM_BORDEREAU;
          	$data['RACCROCHAGE_ID'] = $id_detail;

						//Recuperation de la date transmission du bordereau
          	$EXECUTION_BUDGETAIRE_DETAIL_ID = md5($id_detail['EXECUTION_BUDGETAIRE_DETAIL_ID']);
          	$requetebase2 = "SELECT DATE_TRANSMISSION,EXECUTION_BUDGETAIRE_DETAIL_ID
          	FROM execution_budgetaire_tache_detail_histo WHERE 
          	md5(EXECUTION_BUDGETAIRE_DETAIL_ID) = \'" . $EXECUTION_BUDGETAIRE_DETAIL_ID . "\' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC";

          	$fetch_data2 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase2 . "')");
          	$data['date_transmission'] = $fetch_data2;

						//Recuperation des titre de decaissements
          	$requetebase3 = "SELECT bon_titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID,bon_titre.NUMERO_DOCUMENT,bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_tache_detail detail ON detail.EXECUTION_BUDGETAIRE_DETAIL_ID = bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID	WHERE md5(bon_titre.BORDEREAU_TRANSMISSION_ID) =\'" . $bordereau_transmission_id . "\' AND  ETAPE_DOUBLE_COMMANDE_ID=\'" . $ID_ETAPE_COMMANDE . "\'";

          	$fetch_data3 = $this->ModelPs->getRequete("CALL `getTable` ('" . $requetebase3 . "')");
          	$data['titre_decaissement'] = $fetch_data3;
						//Details
          	$EXECUTION_BUDGETAIRE_ID = $id_detail['EXECUTION_BUDGETAIRE_ID'];

          	return view('App\Modules\double_commande_new\Views\Reception_bordereau_brb_list_view', $data);
		  		}  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }       
	}

	public function insertion_histo()
	{
		$session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$date_reception = $this->request->getPost("DATE_RECEPTION");
		$titre_decaissement = $this->request->getPost("titre_decaissement[]");
		$date_transmission = $this->request->getPost("DATE_TRANSMISSION");
		$date_reception_transmission = $this->request->getPost("date_insertion_check");
		$etape_actuel = $this->request->getPost("ID_ETAPE_COMMANDE");
		$BORDEREAU_ID = $this->request->getPost("BORDEREAU_TRANSMISSION_ID");
		$raccrochage_id = $this->request->getPost("RACCROCHAGE_ID");
		$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = $this->request->getPost('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID');
		$success = false;
		// dd($_POST);

		//update-execution budgetaire bordereau transmission new-

		$conditions_1 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_ID;
		$datatomodifie_1 = 'STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2';
		$this->update_all_table('execution_budgetaire_bordereau_transmission', $datatomodifie_1, $conditions_1);		

		//-etape en cours-
		$etape_en_cour_id = 28;
		//-etape suivante-
		$id_etape_suivante = 29;

		//-mettre a jour tous les titres a 3 (pas reception)-
		$conditions_2 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_ID;
		$datatomodifie_2 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=3';
		$this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $datatomodifie_2, $conditions_2);

		//-update  execution_budgetaire_bordereau_transmission_bon_titre-

		foreach ($titre_decaissement as $value) {
			$conditions_3 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_ID . ' AND EXECUTION_BUDGETAIRE_DETAIL_ID=' . $value;
			$datatomodifie_3 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
			$this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $datatomodifie_3, $conditions_3);

			//insertion dans l'historique
			$column_histo = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
			$data_histo = $value . ',' . $etape_en_cour_id . ',' . $user_id . ',"' . $date_reception . '","' . $date_transmission . '"';
			$this->save_histo($column_histo, $data_histo);
			//update dans la table 'execution_budgetaire_raccrochage_activite_detail' -

			$updateTable = 'execution_budgetaire_tache_detail';
			$critere = "EXECUTION_BUDGETAIRE_DETAIL_ID=" . $value;
			$datatoupdate = 'ETAPE_DOUBLE_COMMANDE_ID=' . $id_etape_suivante;
			$bindparams = [$updateTable, $datatoupdate, $critere];
			$insertRequete = 'CALL updateData(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
						
		}
		$data = ['message' => "".lang('messages_lang.message_success').""];
		session()->setFlashdata('alert', $data);
		return redirect('double_commande_new/Transmission_Deja_Reception_BRB/liste_trans_rec_vue');
	}

	public function save_histo($columsinsert, $datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
		$table = ' execution_budgetaire_tache_detail_histo';
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReqAgence = "CALL insertLastIdIntoTableColonnes(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
	}
}
