<?php

/**  Developpe par
 *Eric SINDAYIGAYA
 *Titre: Transmission du bordereau a la brb
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

class Transmission_borderau_brb extends BaseController
{

	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		// $this->load->library('Excel');
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

	public function index()
	{
		$data = $this->urichk();
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		//------------etape actuel------------------	
		$etape_actuel = 27;
		//------------Recuperation de detail id
		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
  	$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
  	$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
  	{
  		foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
        	$psgetrequete = "CALL `getRequete`(?,?,?,?);";
					$step = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail', 'ETAPE_DOUBLE_COMMANDE_ID ='. $etape_actuel, 'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
					$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
					$EXECUTION_BUDGETAIRE_DETAIL_ID =  $get_step['EXECUTION_BUDGETAIRE_DETAIL_ID'];

					// titre etape
					$callpsreq = "CALL `getRequete`(?,?,?,?);";
					$titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID =' . $etape_actuel, ' DESC_ETAPE_DOUBLE_COMMANDE DESC');
					$titre = $this->ModelPs->getRequeteOne($callpsreq, $titre);
					$data['etapes'] = $titre;
							
					//Etape du processus Double commande
					$id_etap = $this->getBindParms('det.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail det', 'det.ETAPE_DOUBLE_COMMANDE_ID="'.$etape_actuel.'"', 'det.ETAPE_DOUBLE_COMMANDE_ID ASC');
					$id_etap = str_replace('\\', '', $id_etap);
					$data['etape'] = $this->ModelPs->getRequeteOne($psgetrequete, $id_etap);
					$ETAPE_DOUBLE_COMMANDE_ID = $data['etape']['ETAPE_DOUBLE_COMMANDE_ID'];
					$EXECUTION_BUDGETAIRE_ID = $data['etape']['EXECUTION_BUDGETAIRE_ID'];

					//Recuperation de la date transmission du bordereau
					$requetebase2 = "SELECT DATE_TRANSMISSION
					FROM execution_budgetaire_tache_detail_histo WHERE 
					EXECUTION_BUDGETAIRE_DETAIL_ID =" .$EXECUTION_BUDGETAIRE_DETAIL_ID . " ORDER BY DATE_INSERTION DESC";
					$fetch_data2 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase2 . "')");
					$data['date_transmission'] = $fetch_data2;
					
					// echo json_encode($fetch_data2);die();

					//Recuperation du statut operation bordereau transmission
					$requetebase3 = "SELECT STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID,DESC_STATUT_OPERATION_BORDEREAU_TRANSMISSION
					FROM statut_operation_bordereau_transmission WHERE 
					STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = 1";
					$fetch_data3 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase3 . "')");
					$data['statut_operation_bordereau'] = $fetch_data3;

					//Recuperation du type de document boredereau de transmission
					$requetebase4 = "SELECT TYPE_DOCUMENT_ID,DESCR_DOCUMENT
					FROM type_document WHERE 
					TYPE_DOCUMENT_ID = 2";
					$fetch_data4 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase4 . "')");
					$data['type_document_bordereau'] = $fetch_data4;

					//Recuperation du statut du document boredereau de transmission
					$requetebase5 = "SELECT STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID ,DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION
					FROM statut_document_bordereau_transmission WHERE 
					STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID = 1";
					$fetch_data5 = $this->ModelPs->getRequeteOne("CALL `getTable` ('" . $requetebase5 . "')");
					$data['statut_document_bordereau'] = $fetch_data5;
		
					//Recuperation des titre de decaissements
					$requetebase3 = "SELECT DISTINCT bon_titre.NUMERO_DOCUMENT,bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID FROM 
					execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN
					execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID
					WHERE ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND bon_titre.TYPE_DOCUMENT_ID =2";
					$fetch_data3 = $this->ModelPs->getRequete("CALL `getTable` ('" . $requetebase3 . "')");
					$data['exec'] = $fetch_data3;
					
					// echo json_encode($fetch_data3);die();

					//origine-destination
					$origine_destination = 'SELECT ID_ORIGINE_DESTINATION, ORIGINE, DESTINATION FROM `origine_destination` WHERE ID_ORIGINE_DESTINATION=3';
					$origine_destination = "CALL `getTable`('" . $origine_destination . "');";
					$data['origine_destination'] = $this->ModelPs->getRequete($origine_destination);

					return view('App\Modules\double_commande_new\Views\Transmission_bordereau_brb_add_view', $data);

        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }		
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

	public function getInfoDetail($value = '')
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");

		if ($USER_ID == '') {
			return  redirect('Login_Ptba/do_logout');
		}


		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
		$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
		$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');

		######################## upload file autorisation de transfert ##############################
		$PATH = $this->request->getPost('PATH_BORDEREAU_TRANSMISSION');
		$PATH_BORDEREAU_TRANSMISSION = $this->uploadFile('PATH_BORDEREAU_TRANSMISSION', 'file_autorisation_tempo', $PATH);

		$insertIntoTable = 'execution_budgetaire_tempo_path_bordereau';
		$columsinsert = "USER_ID,PATH_BORDEREAU_TRANSMISSION";
		$datacolumsinsert = $USER_ID . ",'" . $PATH_BORDEREAU_TRANSMISSION . "'";
		$PATH_BORDEREAU_TRANSMISSION_ID   = $this->save_all_table($insertIntoTable, $columsinsert, $datacolumsinsert);

		$sql_file = 'SELECT PATH_BORDEREAU_TRANSMISSION FROM execution_budgetaire_tempo_path_bordereau WHERE PATH_BORDEREAU_TRANSMISSION_ID=' . $PATH_BORDEREAU_TRANSMISSION_ID . ' AND USER_ID=' . $USER_ID . '';
		$file = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
		$show_file = '<a href="' . base_url($file['PATH_BORDEREAU_TRANSMISSION']) . '" target="_blank"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a>';
		#####################################      ################################################

		//get data origine-destination
		$origine_destination = 'SELECT ID_ORIGINE_DESTINATION, ORIGINE, DESTINATION FROM `origine_destination` WHERE ID_ORIGINE_DESTINATION=' . $ID_ORIGINE_DESTINATION . ' ';
		$origine_destination = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $origine_destination . "')");


		//Declaration des labels pour l'internalisation
		$label_date_reception = lang("messages_lang.label_date_reception");
		$label_numero_bordereau = lang("messages_lang.label_numero_bordereau");
		$label_numero_titre_decaissement = lang("messages_lang.label_numero_titre_decaissement");
		$label_orgine_destination = lang("messages_lang.label_orgine_destination");
		$label_bordereau_reception = lang("messages_lang.label_bordereau_reception");
		$label_date_transmission_BRB = lang("messages_lang.label_date_transmission_BRB");

		$html = '<div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
	              <div class="row" style="margin :  5px">
	              <div class="col-12">
	                <div class=" table-responsive ">
	                  <table class="table m-b-0 m-t-20">
	                    <tbody>
							<tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-calendar"> </i>&nbsp;' . $label_date_reception . '</font></td>
								<td><strong><font style="float:left;">' . $DATE_RECEPTION . '</font></strong></td>
							</tr>
	                       <tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-list"> </i>&nbsp;' . $label_numero_bordereau . '</font></td>
								<td><strong><font style="float:left;">' . $NUM_BORDEREAU_TRANSMISSION . '</font></strong></td>
							</tr>
							<tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-certificate"> </i>&nbsp;' . $label_numero_titre_decaissement . '</font></td>
								<td><strong><font style="float:left;">' . $EXECUTION_BUDGETAIRE_DETAIL_ID . '</font></strong></td>
							</tr>
							<tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-cogs"> </i>&nbsp;' . $label_orgine_destination . '</font></td>
								<td><strong><font style="float:left;">' . $origine_destination['ORIGINE'] . ' ' . '->' . ' ' . $origine_destination['DESTINATION'] . '</font></strong></td>
							</tr>

							<tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-file"></i>&nbsp;' . $label_bordereau_reception . '</font></td>
								<td><strong><font style="float:left;">' . $show_file . '</font></strong></td>
							</tr>
							<tr>
								<td style="width:300px ;"><font style="float:left;"><i class="fa fa-calendar"> </i>&nbsp;' . $label_date_transmission_BRB . '</font></td>
								<td><strong><font style="float:left;">' . $DATE_TRANSMISSION . '</font></strong></td>
							</tr>
	                    </tbody>
	                  </table>        
	                </div>
	              </div>
	            </div>
	          </div>';

		$output = array(
			"html" => $html
		);
		return $this->response->setJSON($output);
	}

	public function deleteFile($value = '')
	{
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");

		if ($USER_ID == '') {
			return  redirect('Login_Ptba/do_logout');
		}


		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		//Delete file dans la table tempo autorisation transfert
		$delete_file_folder = 'SELECT PATH_BORDEREAU_TRANSMISSION FROM tempo_path_bordereau WHERE USER_ID=' . $USER_ID . ' ORDER BY PATH_BORDEREAU_TRANSMISSION_ID DESC  ';
		$tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $delete_file_folder . "')");

		foreach ($tempo as $item) {
			$PATH_BORDEREAU_TRANSMISSION = $item->PATH_BORDEREAU_TRANSMISSION;
		}
		unlink($PATH_BORDEREAU_TRANSMISSION);

		$sql_file = 'DELETE FROM tempo_path_bordereau WHERE USER_ID=' . $USER_ID . '';
		$this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
		#####################################      ################################################
	}

	// insert data liquidation
	public function add($value = '')
	{
		$session  = \Config\Services::session();
		$USER_ID = $session->get("SESSION_SUIVIE_PTBA_USER_ID");

		if ($USER_ID == '') {
			return  redirect('Login_Ptba/do_logout');
		}


		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
		$ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
		$PATH_BORDEREAU_TRANSMISSION = $this->request->getPost('PATH_BORDEREAU_TRANSMISSION');
		$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
		$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
		$PATH_BORDEREAU_TRANSMISSION = $this->uploadFile('PATH_BORDEREAU_TRANSMISSION', 'double_commande_new', $PATH_BORDEREAU_TRANSMISSION);
		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID[]');
		$ETAPE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
		$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = $this->request->getPost('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID');
		$TYPE_DOCUMENT_ID = $this->request->getPost('TYPE_DOCUMENT_ID');
		$STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID = $this->request->getPost('STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID');

		// insert dans la table execution_bord_transmission
		$table1 = 'execution_budgetaire_bordereau_transmission';
		$columsinsert1 = "NUMERO_BORDEREAU_TRANSMISSION,PATH_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID,DATE_RECEPTION_BD,DATE_TRANSMISSION_BD, STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID";
		$datacolumsinsert1 = "'".$NUM_BORDEREAU_TRANSMISSION."', '".$PATH_BORDEREAU_TRANSMISSION."', ".$ID_ORIGINE_DESTINATION.", ".$USER_ID.", '".$DATE_RECEPTION."', '".$DATE_TRANSMISSION."', ".$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID."";
		$EXECUTION_BORD_TRANSMISSION_ID = $this->save_all_table($table1, $columsinsert1, $datacolumsinsert1);

		// insert dans la table execution_budgetaire_bordereau_transmission_bon_titre
		foreach ($EXECUTION_BUDGETAIRE_DETAIL_ID as $key) {			
			//Recuperation racc_detail_id
			$maRequete5 = 'SELECT EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail
			WHERE EXECUTION_BUDGETAIRE_DETAIL_ID ='.$key .' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID DESC';
			$fetch_data5 = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $maRequete5 . "')");
			
			$DETAIL_ID = $fetch_data5['EXECUTION_BUDGETAIRE_DETAIL_ID'];
			//Recuperation NUMERO_DOCUMENT
			$maRequete6 = 'SELECT NUMERO_TITRE_DECAISSEMNT FROM execution_budgetaire_tache_detail
			WHERE EXECUTION_BUDGETAIRE_DETAIL_ID =' . $DETAIL_ID . ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID DESC';
			$fetch_data6 = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $maRequete6 . "')");
			$NUMERO_DOCUMENT = $fetch_data6['NUMERO_TITRE_DECAISSEMNT'];
			//Enregistre execution_budgetaire_bordereau_transmission_bon_titre
			$table2 = 'execution_budgetaire_bordereau_transmission_bon_titre';
			$columsinsert2 = "BORDEREAU_TRANSMISSION_ID, TYPE_DOCUMENT_ID, EXECUTION_BUDGETAIRE_DETAIL_ID, NUMERO_DOCUMENT, USER_ID, STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID";

			$datacolumsinsert2 = $EXECUTION_BORD_TRANSMISSION_ID . ", " . $TYPE_DOCUMENT_ID . ", " . $DETAIL_ID . ", '" . $NUMERO_DOCUMENT . "', " . $USER_ID . ", " . $STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID . " ";
			$this->save_all_table($table2, $columsinsert2, $datacolumsinsert2);
		}

		##########################################################################

		//récuperer les etapes
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID ,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID =' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID  ASC');
		$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
		// $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID']; // MOUVEMENT_DEPENSE_ID quité

		//insert dans la table historique
		foreach ($EXECUTION_BUDGETAIRE_DETAIL_ID as $EXEC_ID) {
			$maRequete = 'SELECT hist_act_det.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo hist_act_det JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = hist_act_det.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE det.EXECUTION_BUDGETAIRE_DETAIL_ID =' . $EXEC_ID;
			$fetch_data2 = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $maRequete . "')");

			$table5 = 'execution_budgetaire_tache_detail_histo';
			$columsinsert5 = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";
			$datacolumsinsert5 = $fetch_data2['EXECUTION_BUDGETAIRE_DETAIL_ID'] . ", " . $USER_ID . ", " . $ETAPE_ID . ",'" . $DATE_RECEPTION . "', '" . $DATE_TRANSMISSION . "'";
			$this->save_all_table($table5, $columsinsert5, $datacolumsinsert5);
		}

		//mise à jour dans la table execution_budgetaire_tache_detail
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='. $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
		$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
		$NEXT_ETAPE_ID =  $get_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']; // Etape suivante
		// echo json_encode($NEXT_ETAPE_ID);die();

		// $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID =' . $get_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'], 'ETAPE_DOUBLE_COMMANDE_ID  ASC');
		// $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
		// $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID']; // mouvement suivant

		$table6 = 'execution_budgetaire_tache_detail';
		foreach ($EXECUTION_BUDGETAIRE_DETAIL_ID as $key) {
			$where6 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$key;
			$data6 = 'ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID;
			$this->update_all_table($table6, $data6, $where6);
		}

		##################################################################################

		//Delete file dans la table tempo autorisation transfert
		$delete_file_folder = 'SELECT PATH_BORDEREAU_TRANSMISSION FROM execution_budgetaire_tempo_path_bordereau WHERE USER_ID=' . $USER_ID . ' ORDER BY PATH_BORDEREAU_TRANSMISSION_ID DESC  ';
		$tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $delete_file_folder . "')");

		foreach ($tempo as $item) {
			$PATH_BORDEREAU_TRANSMISSION = $item->PATH_BORDEREAU_TRANSMISSION;
		}
		unlink($PATH_BORDEREAU_TRANSMISSION);

		$sql_file = 'DELETE FROM execution_budgetaire_tempo_path_bordereau WHERE USER_ID='.$USER_ID.'';
		$this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
		#####################################      ################################################

		$data = ['message' => "".lang('messages_lang.message_success').""];
		session()->setFlashdata('alert', $data);
		return redirect('double_commande_new/Liste_transmission_bordereau_deja_transmis_brb');
	}

	public function checkbord()
  {
    $NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
    $ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
    $get_bord = "SELECT NUMERO_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION FROM execution_budgetaire_bordereau_transmission WHERE NUMERO_BORDEREAU_TRANSMISSION='".$NUM_BORDEREAU_TRANSMISSION."' AND ID_ORIGINE_DESTINATION=".$ID_ORIGINE_DESTINATION;
    $get_bord='CALL `getTable`("'.$get_bord.'")';
    $get_bord= $this->ModelPs->getRequeteOne($get_bord);
    $status=0;
    if(!empty($get_bord))
    {
      $status=0;
    }
    else
    {
      $status=1;
    }
    return json_encode(array('status'=>$status));
  }
}
