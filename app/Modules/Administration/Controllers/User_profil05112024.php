<?php
/**NIYONAHABONYE Pascal
*Titre:CRUD DE PTBA DES PROFILES
*Numero de telephone: (+257) 68 045 482
*WhatsApp: (+257) 77531083
*Email: pascal@mediabox.IMPORT
*Date: 29 Août,2023
**/

/*Modfie par NDERAGAKURA Alain Charbel
	Ajout des form validation php
	Emailpro:charbel@mediabox.bi
	Date: 19/10/2023
	Tel:76887837/62003622
 */

/*
Modfie par HABIMANA Nandou
Ameriolation de Gestion des profils en tenant compte les niveau d'intervation et des processus
Emailpro:nandou@mediabox.bi
Date: 22/12/2023
Tel:71483905
*/

/**
*Amelioration
*MWENEMBUGA MUKUBWA Bonfils de Jésus
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 23 Déc 2023
**/

/**
*Amelioration de gestion des profils selon les modules
*HABIMANA Nandou
*Numero de telephone: +257 69 301 985
*Email pro: nandou@mediabox.bi
*Date: 25 Déc 2023
**/

/**
 * AMELIORATION DE GESTION DE PROFIL
 * Nom:		 Baleke kahamire Bonheur
 * Numero: +257 67 86 62 83
 * Email:		bonheur.baleke@mediabox.bi
 * Date:		19.01.2024
 */
/**
 * AMELIORATION DE GESTION DE PROFIL
 * Nom:		 NIYONGABO Claude
 * Numero: +257 69 64 13 75
 * Email:		claude@mediabox.bi
 * Date:		25.09.2024
 */


namespace  App\Modules\Administration\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class User_profil extends BaseController
{
	protected $ModelPs;
	protected $validation;

	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
		$user_id ='';
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		return view('App\Modules\Administration\Views\User_profil_list_view',$data);   
	}

	public function getVisualisation($PROFIL_NIVEAU_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		if($PROFIL_NIVEAU_ID==1 || $PROFIL_NIVEAU_ID==2)
		{
			$visualisation = $this->ModelPs->getRequete("CALL `getTable`('SELECT NIVEAU_VISUALISATION_ID, DESC_NIVEAU_VISUALISATION FROM user_profil_niveau_visualisation 
				WHERE 1 AND NIVEAU_VISUALISATION_ID=2')");
		}
		else
		{
			$visualisation = $this->ModelPs->getRequete("CALL `getTable`('SELECT NIVEAU_VISUALISATION_ID,DESC_NIVEAU_VISUALISATION FROM user_profil_niveau_visualisation 
				WHERE 1')");
		}

		$html='<option value="">--Sélectionner--</option>';
		foreach ($visualisation as $key)
		{
			$html.='<option value="'.$key->NIVEAU_VISUALISATION_ID.'">'.$key->DESC_NIVEAU_VISUALISATION.'</option>';
		}

		$output=array("NIVEAU_VISUALISATION_ID"=>$html);
		return $this->response->setJSON($output);
	}

	private function check_droit($profils)
	{
		$html = ($profils == 1) ? "<center><span class='fa fa-check badge badge-pill badge-success' style='font-size:20px;font-weight: bold;color: white;' data-toggle='tooltip' title='Activé'>&nbsp;</span></center>" : "<center><span class='fa fa-close badge badge-pill badge-danger' style='font-size:20px;font-weight: bold;color: white;' data-toggle='tooltip' title='Désactivé'>&nbsp;</span></center>" ;
		return $html;
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
		$user_id ='';
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = '';

		$order_column = array(1,'PROFIL_DESCR','DESC_PROFIL_NIVEAU','DESC_NIVEAU_VISUALISATION',1,1,1,1,1,1,1,1,1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY PROFIL_ID ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (DESC_PROFIL_NIVEAU LIKE "%' . $var_search . '%" OR PROFIL_DESCR LIKE "%' . $var_search . '%" OR DESC_NIVEAU_VISUALISATION LIKE "%' . $var_search . '%")') : '';

		// Condition pour la requête principale
		$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;

		// Condition pour la requête de filtre
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

		$requetedebase = "SELECT IS_ACTIVE, PROFIL_ID,PROFIL_CODE, PROFIL_DESCR, DESC_PROFIL_NIVEAU, DESC_NIVEAU_VISUALISATION, UTILISATEURS, PROFIL,MASQUE_SAISI_ENJEUX FROM user_profil left JOIN user_profil_niveau_visualisation visua ON visua.NIVEAU_VISUALISATION_ID =user_profil.NIVEAU_VISUALISATION_ID left JOIN  user_profil_niveau ON  user_profil_niveau.PROFIL_NIVEAU_ID=user_profil.PROFIL_NIVEAU_ID WHERE 1";

		$requetedebases = $requetedebase . ' ' . $conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";

		$fetch_actions = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;

		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->PROFIL_DESCR;
			$sub_array[] = $row->DESC_PROFIL_NIVEAU;
			$sub_array[] = $row->DESC_NIVEAU_VISUALISATION;
			$sub_array[] = $this->check_droit($row->UTILISATEURS);
			$sub_array[] = $this->check_droit($row->PROFIL);
			$sub_array[] = $this->check_droit($row->MASQUE_SAISI_ENJEUX);
			$sub_array[] = $this->check_droit($row->IS_ACTIVE);

			if($row->IS_ACTIVE==1)
			{
				$statut = 'Désactiver';
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .="<li>
				<a href='".base_url("Administration/User_profil/getOne/".md5($row->PROFIL_ID))."'><label>&nbsp;&nbsp;Modifier</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->PROFIL_ID.")' ><label>&nbsp;&nbsp;<font color='red'>".$statut."</font></label></a>
				</li>
				<div style='display:none;' id='message".$row->PROFIL_ID."'>
				<center>
				<h5><strong>".lang('messages_lang.confimatation_active_action')."<br><center><font color='green'>".$row->PROFIL_DESCR."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
				</h5>
				</center></div>
				<div style='display:none;' id='footer".$row->PROFIL_ID."'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				".lang('messages_lang.quiter_action')."
				</button>
				<a href='".base_url("Administration/User_profil/is_active/".$row->PROFIL_ID)."' class='btn btn-success btn-md'>".lang('messages_lang.desactive_action')."</a>
				</div>
				<li>
				<a href='#' onclick='getDetail(".$row->PROFIL_ID.")'>
				<label><font color='blue'>&nbsp;&nbsp;Détail</font></label></a>
				</li></ul>";			
			}
			else
			{
				$statut = 'Activer';
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .="<li>
				<a href='".base_url("Administration/User_profil/getOne/".md5($row->PROFIL_ID))."'><label>&nbsp;&nbsp;Modifier</label></a>
				</li>

				<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->PROFIL_ID.")' ><label>&nbsp;&nbsp;<font color='green'>".$statut."</font></label></a>
				</li>
				<div style='display:none;' id='message".$row->PROFIL_ID."'>
				<center>
				<h5><strong>".lang('messages_lang.confimatation_active_action')."<br><center><font color='green'>".$row->PROFIL_DESCR."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
				</h5>
				</center></div>
				<div style='display:none;' id='footer".$row->PROFIL_ID."'>
				<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
				".lang('messages_lang.quiter_action')."
				</button>
				<a href='".base_url("Administration/User_profil/is_active/".$row->PROFIL_ID)."' class='btn btn-success btn-md'>".lang('messages_lang.active_action')."</a>
				</div>
				<li>
				<a href='#' onclick='getDetail(".$row->PROFIL_ID.")'>
				<label><font color='blue'>&nbsp;&nbsp;Détail</font></label></a>
				</li>";			
			}
			$sub_array[]=$action;
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
		return $this->response->setJSON($output);
	}

	//fonction pour l'activation/désactivation
	function is_active($PROFIL_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_user = $this->getBindParms('PROFIL_ID , IS_ACTIVE', 'user_profil', 'PROFIL_ID='.$PROFIL_ID,'PROFIL_ID ASC');
		$users= $this->ModelPs->getRequeteOne($callpsreq, $bind_user);
		if($users['IS_ACTIVE']==0)
		{
			$IS_ACTIVE = 1;
			$updateTable='user_profil';
			$critere = "PROFIL_ID=".$PROFIL_ID;
			$datatoupdate= 'IS_ACTIVE='.$IS_ACTIVE;
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data = ['message' => ''.lang('messages_lang.labelle_et_mod_question_succes').''];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/User_profil');
		}
		else
		{
			$IS_ACTIVE = 0;
			$updateTable='user_profil';
			$critere = "PROFIL_ID=".$PROFIL_ID;
			$datatoupdate= 'IS_ACTIVE='.$IS_ACTIVE;
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data=['message' => ''.lang('messages_lang.labelle_et_mod_question_succes_d').''];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/User_profil');
		}	
	}

	//fonctin  pour afficher le formulaire
	public function ajout($value='')
	{
		$session  = \Config\Services::session();
		$user_id ='';
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		
		$bind_profil = $this->getBindParms('PROFIL_NIVEAU_ID,DESC_PROFIL_NIVEAU','user_profil_niveau','1','PROFIL_NIVEAU_ID ASC');
		$data['profil_niveau']= $this->ModelPs->getRequete($callpsreq, $bind_profil);

		$bind_visualisation=$this->getBindParms('NIVEAU_VISUALISATION_ID,DESC_NIVEAU_VISUALISATION','user_profil_niveau_visualisation','1','DESC_NIVEAU_VISUALISATION ASC');
		$data['niveau_visualisation']= $this->ModelPs->getRequete($callpsreq, $bind_visualisation);
		return view('App\Modules\Administration\Views\User_profil_add_view',$data);   
	}

	/**
	 * fonction pour retourner le tableau des parametre pour le PS pour les selection
	 * @param string  $columnselect //colone A selectionner
	 * @param string  $table        //table utilisE
	 * @param string  $where        //condition dans la clause where
	 * @param string  $orderby      //order by
	 * @return  mixed
	 */

	//Formulaire pour inserer les donnees dans la table
	public function insert()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$user_id ='';
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		// Génère un code aléatoire à 5 chiffres et tester s'il n'existe pas dans la BD
		$PROFIL_CODE = "";
		$statut = 1;
		while ($statut == 1)
		{
			$PROFIL_CODE = random_int(10000, 99999);
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$codeuser = $this->getBindParms('PROFIL_CODE', 'user_profil', 'PROFIL_CODE=' . $PROFIL_CODE, '1');
			$usercode = $this->ModelPs->getRequete($callpsreq, $codeuser);
   		// Si le code n'existe pas, sortir de la boucle
			if(empty($usercode))
			{
				break;
			}
		}

		$rules = [
			'PROFIL_DESCR' => [
				'rules' => 'required|max_length[100]|is_unique[user_profil.PROFIL_DESCR]',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
					'max_length' => '<font style="color:red;size:2px;">Vous ne pouvez pas saisir plus de 100 caractères</font>',
					'is_unique' => '<font style="color:red;size:2px;">Le profil existe déjà</font>',
				]
			],
			'PROFIL_NIVEAU_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
				]
			],
			'NIVEAU_VISUALISATION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
				]
			]
		];
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			$PROFIL_DESCR = $this->request->getPost('PROFIL_DESCR');
			$PROFIL_NIVEAU_ID = $this->request->getPost('PROFIL_NIVEAU_ID');
			$NIVEAU_VISUALISATION_ID = $this->request->getPost('NIVEAU_VISUALISATION_ID');
			$IS_ACTIVE=1;

			if($UTILISATEURS = $this->request->getPost('UTILISATEURS')!=null)
				{$UTILISATEURS=1;}else{$UTILISATEURS=0;}

			if($PROFIL = $this->request->getPost('PROFIL')!=null)
				{$PROFIL=1;}else{$PROFIL=0;}			

			if($IS_ENGAGEMENT_BUDGETAIRE = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE')!=null)
				{$IS_ENGAGEMENT_BUDGETAIRE=1;}else{$IS_ENGAGEMENT_BUDGETAIRE=0;}

			$IS_ENGAGEMENT_BUDGETAIRE_SANS_BON = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_SANS_BON') != null ? 1 : 0;
			$IS_ENGAGEMENT_BUDGETAIRE_CORRECTION = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_CORRECTION') != null ? 1 : 0;
			$IS_ENGAGEMENT_BUDGETAIRE_ANNULER = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_ANNULER') != null ? 1 : 0;

			if($IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED = $this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED=1;}else{$IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED=0;}

			if($IS_ENGAGEMENT_JURIDIQUE = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE')!=null)
				{$IS_ENGAGEMENT_JURIDIQUE=1;}else{$IS_ENGAGEMENT_JURIDIQUE=0;}

			if($IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=$this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=1;}else{$IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=0;}

			$IS_ENGAGEMENT_JURIDIQUE_CORRECTION = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE_CORRECTION') != null ? 1 : 0;
			$IS_ENGAGEMENT_JURIDIQUE_ANNULER = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE_ANNULER') != null ? 1 : 0;

			if($IS_ENGAGEMENT_LIQUIDATION = $this->request->getPost('IS_ENGAGEMENT_LIQUIDATION')!=null)
				{$IS_ENGAGEMENT_LIQUIDATION=1;}else{$IS_ENGAGEMENT_LIQUIDATION=0;}

			if($IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=$this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=1;}else{$IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=0;}

			$IS_LIQUIDATION_CORRECTION = $this->request->getPost('IS_LIQUIDATION_CORRECTION') != null ? 1 : 0;
			$IS_LIQUIDATION_ANNULER = $this->request->getPost('IS_LIQUIDATION_ANNULER') != null ? 1 : 0;
			$IS_LIQUIDATION_DECISION_CED = $this->request->getPost('IS_LIQUIDATION_DECISION_CED') != null ? 1 : 0;

			if($IS_ORDONNANCEMENT = $this->request->getPost('IS_ORDONNANCEMENT')!=null)
				{$IS_ORDONNANCEMENT=1;}else{$IS_ORDONNANCEMENT=0;}

			$IS_ORDONNANCEMENT_MINISTRE = $this->request->getPost('IS_ORDONNANCEMENT_MINISTRE') != null ? 1 : 0;

			if($IS_TRANSMISSION_SERVICE_PRISE_COMPTE=$this->request->getPost('IS_TRANSMISSION_SERVICE_PRISE_COMPTE')!=null)
				{$IS_TRANSMISSION_SERVICE_PRISE_COMPTE=1;}else{$IS_TRANSMISSION_SERVICE_PRISE_COMPTE=0;}

			$IS_TRANSMISSION_BON_CABINET = $this->request->getPost('IS_TRANSMISSION_BON_CABINET') != null ? 1 : 0;
			$IS_TRANSMISSION_CABINET_SPE = $this->request->getPost('IS_TRANSMISSION_CABINET_SPE') != null ? 1 : 0;
			$ORDONNANCEMENT_CORRECTION_CED = $this->request->getPost('ORDONNANCEMENT_CORRECTION_CED') != null ? 1 : 0;

			if($IS_RECEPTION_SERVICE_PRISE_COMPTE = $this->request->getPost('IS_RECEPTION_SERVICE_PRISE_COMPTE')!=null)
				{$IS_RECEPTION_SERVICE_PRISE_COMPTE=1;}else{$IS_RECEPTION_SERVICE_PRISE_COMPTE=0;}

			$RECEPTION_OBR = $this->request->getPost('RECEPTION_OBR') != null ? 1 : 0;
			$IS_PRISE_EN_CHARGE = $this->request->getPost('IS_PRISE_EN_CHARGE') != null ? 1 : 0;
			$IS_ETABLISSEMENT_TITRE_DECAISSEMENT = $this->request->getPost('IS_ETABLISSEMENT_TITRE_DECAISSEMENT') != null ? 1 : 0;
// CORRECTION_LIQUIDATION_SALAIRE

			if($IS_PAIEMENT = $this->request->getPost('IS_PAIEMENT')!=null)
				{$IS_PAIEMENT=1;}else{$IS_PAIEMENT=0;}

			if($IS_TRANSMISSION_DIRECTEUR_COMPTABLE = $this->request->getPost('IS_TRANSMISSION_DIRECTEUR_COMPTABLE')!=null)
				{$IS_TRANSMISSION_DIRECTEUR_COMPTABLE=1;}else{$IS_TRANSMISSION_DIRECTEUR_COMPTABLE=0;}

			if($IS_RECEPTION_DIRECTEUR_COMPTABLE = $this->request->getPost('IS_RECEPTION_DIRECTEUR_COMPTABLE')!=null)
				{$IS_RECEPTION_DIRECTEUR_COMPTABLE=1;}else{$IS_RECEPTION_DIRECTEUR_COMPTABLE=0;}

			$IS_TITRE_SIGNATURE_DIR_COMPTABILITE = $this->request->getPost('IS_TITRE_SIGNATURE_DIR_COMPTABILITE') != null ? 1 : 0;
			$IS_TITRE_SIGNATURE_DGFP = $this->request->getPost('IS_TITRE_SIGNATURE_DGFP') != null ? 1 : 0;
			$IS_TITRE_SIGNATURE_MINISTRE = $this->request->getPost('IS_TITRE_SIGNATURE_MINISTRE') != null ? 1 : 0;

			if($IS_TRANSMISSION_BRB = $this->request->getPost('IS_TRANSMISSION_BRB')!=null)
				{$IS_TRANSMISSION_BRB=1;}else{$IS_TRANSMISSION_BRB=0;}

			if($IS_RECEPTION_BRB = $this->request->getPost('IS_RECEPTION_BRB')!=null)
				{$IS_RECEPTION_BRB=1;}else{$IS_RECEPTION_BRB=0;}

			if($IS_DECAISSEMENT = $this->request->getPost('IS_DECAISSEMENT')!=null)
				{$IS_DECAISSEMENT=1;}else{$IS_DECAISSEMENT=0;}
			
			if($DOUBLE_COMMANDE_VALIDE_TD = $this->request->getPost('DOUBLE_COMMANDE_VALIDE_TD')!=null)
				{$DOUBLE_COMMANDE_VALIDE_TD=1;}else{$DOUBLE_COMMANDE_VALIDE_TD=0;}

			if($DOUBLE_COMMANDE_TRANSFERT = $this->request->getPost('DOUBLE_COMMANDE_TRANSFERT')!=null)
				{$DOUBLE_COMMANDE_TRANSFERT=1;}else{$DOUBLE_COMMANDE_TRANSFERT=0;}

			if($DOUBLE_COMMANDE_PRESTATAIRE = $this->request->getPost('DOUBLE_COMMANDE_PRESTATAIRE')!=null)
				{$DOUBLE_COMMANDE_PRESTATAIRE=1;}else{$DOUBLE_COMMANDE_PRESTATAIRE=0;}

			if($PARAMETRE_PROCESSUS = $this->request->getPost('PARAMETRE_PROCESSUS')!=null)
				{$PARAMETRE_PROCESSUS=1;}else{$PARAMETRE_PROCESSUS=0;}
			
			if($PARAMETRE_ETAPE = $this->request->getPost('PARAMETRE_ETAPE')!=null)
				{$PARAMETRE_ETAPE=1;}else{$PARAMETRE_ETAPE=0;}

			if($PARAMETRE_ACTION = $this->request->getPost('PARAMETRE_ACTION')!=null)
				{$PARAMETRE_ACTION=1;}else{$PARAMETRE_ACTION=0;}

			if($PARAMETRE_DOCUMENTS = $this->request->getPost('PARAMETRE_DOCUMENTS')!=null)
				{$PARAMETRE_DOCUMENTS=1;}else{$PARAMETRE_DOCUMENTS=0;}

			if($PARAMETRE_INFO_SUPPLEMENTAIRE = $this->request->getPost('PARAMETRE_INFO_SUPPLEMENTAIRE')!=null)
				{$PARAMETRE_INFO_SUPPLEMENTAIRE=1;}else{$PARAMETRE_INFO_SUPPLEMENTAIRE=0;}

			if($MASQUE_SAISI_ENJEUX = $this->request->getPost('MASQUE_SAISI_ENJEUX')!=null)
				{$MASQUE_SAISI_ENJEUX=1;}else{$MASQUE_SAISI_ENJEUX=0;}

			if($MASQUE_SAISI_INSTITUTION = $this->request->getPost('MASQUE_SAISI_INSTITUTION')!=null)
				{$MASQUE_SAISI_INSTITUTION=1;}else{$MASQUE_SAISI_INSTITUTION=0;}

			if($MASQUE_SAISI_PTBA_PROGRAMMES = $this->request->getPost('MASQUE_SAISI_PTBA_PROGRAMMES')!=null)
				{$MASQUE_SAISI_PTBA_PROGRAMMES=1;}else{$MASQUE_SAISI_PTBA_PROGRAMMES=0;}

			if($MASQUE_SAISI_PTBA_ACTIONS = $this->request->getPost('MASQUE_SAISI_PTBA_ACTIONS')!=null)
				{$MASQUE_SAISI_PTBA_ACTIONS=1;}else{$MASQUE_SAISI_PTBA_ACTIONS=0;}

			if($MASQUE_SAISI_PTBA_ACTIVITES = $this->request->getPost('MASQUE_SAISI_PTBA_ACTIVITES')!=null)
				{$MASQUE_SAISI_PTBA_ACTIVITES=1;}else{$MASQUE_SAISI_PTBA_ACTIVITES=0;}

			if($MASQUE_SAISI_OBSERVATION_FINANCIERES = $this->request->getPost('MASQUE_SAISI_OBSERVATION_FINANCIERES')!=null)
				{$MASQUE_SAISI_OBSERVATION_FINANCIERES=1;}else{$MASQUE_SAISI_OBSERVATION_FINANCIERES=0;}

			if($PTBA_INSTITUTION = $this->request->getPost('PTBA_INSTITUTION')!=null)
				{$PTBA_INSTITUTION=1;}else{$PTBA_INSTITUTION=0;}

			if($PTBA_PROGRAMMES = $this->request->getPost('PTBA_PROGRAMMES')!=null)
				{$PTBA_PROGRAMMES=1;}else{$PTBA_PROGRAMMES=0;}

			if($PTBA_ACTIONS = $this->request->getPost('PTBA_ACTIONS')!=null)
				{$PTBA_ACTIONS=1;}else{$PTBA_ACTIONS=0;}

			if($PTBA_ACTIVITES = $this->request->getPost('PTBA_ACTIVITES')!=null)
				{$PTBA_ACTIVITES=1;}else{$PTBA_ACTIVITES=0;}

			if($PTBA_CLASSIFICATION_ECONOMIQUE = $this->request->getPost('PTBA_CLASSIFICATION_ECONOMIQUE')!=null)
				{$PTBA_CLASSIFICATION_ECONOMIQUE=1;}else{$PTBA_CLASSIFICATION_ECONOMIQUE=0;}

			if($PTBA_CLASSIFICATION_ADMINISTRATIVE = $this->request->getPost('PTBA_CLASSIFICATION_ADMINISTRATIVE')!=null)
				{$PTBA_CLASSIFICATION_ADMINISTRATIVE=1;}else{$PTBA_CLASSIFICATION_ADMINISTRATIVE=0;}

			if($PTBA_CLASSIFICATION_FONCTIONNELLE = $this->request->getPost('PTBA_CLASSIFICATION_FONCTIONNELLE')!=null)
				{$PTBA_CLASSIFICATION_FONCTIONNELLE=1;}else{$PTBA_CLASSIFICATION_FONCTIONNELLE=0;}

			if($PIP_EXECUTION = $this->request->getPost('PIP_EXECUTION')!=null)
				{$PIP_EXECUTION=1;}else{$PIP_EXECUTION=0;}

			if($PIP_COMPILE = $this->request->getPost('PIP_COMPILE')!=null)
				{$PIP_COMPILE=1;}else{$PIP_COMPILE=0;}

			if($RAPPORTS_SUIVI_EVALUATION = $this->request->getPost('RAPPORTS_SUIVI_EVALUATION')!=null)
				{$RAPPORTS_SUIVI_EVALUATION=1;}else{$RAPPORTS_SUIVI_EVALUATION=0;}

			if($RAPPORTS_CLASSIFICATION_ECONOMIQUE = $this->request->getPost('RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=null)
				{$RAPPORTS_CLASSIFICATION_ECONOMIQUE=1;}else{$RAPPORTS_CLASSIFICATION_ECONOMIQUE=0;}

			if($RAPPORTS_CLASSIFICATION_ADMINISTRATIVE = $this->request->getPost('RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=null)
				{$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE=1;}else{$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE=0;}

			if($RAPPORTS_CLASSIFICATION_FONCTIONNEL = $this->request->getPost('RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=null)
				{$RAPPORTS_CLASSIFICATION_FONCTIONNEL=1;}else{$RAPPORTS_CLASSIFICATION_FONCTIONNEL=0;}

			if($GEOLOCALISATION_CARTE_INSTITUTION = $this->request->getPost('GEOLOCALISATION_CARTE_INSTITUTION')!=null)
				{$GEOLOCALISATION_CARTE_INSTITUTION=1;}else{$GEOLOCALISATION_CARTE_INSTITUTION=0;}

			if($DOUBLE_COMMANDE_ETAT_AVANCEMENT = $this->request->getPost('DOUBLE_COMMANDE_ETAT_AVANCEMENT')!=null)
				{$DOUBLE_COMMANDE_ETAT_AVANCEMENT=1;}else{$DOUBLE_COMMANDE_ETAT_AVANCEMENT=0;}

			$PIP_TAUX_ECHANGE = $this->request->getPost('PIP_TAUX_ECHANGE') != null ? 1 : 0;
			$PIP_POURCENTAGE_NOMENCLATURE = $this->request->getPost('PIP_POURCENTAGE_NOMENCLATURE') != null ? 1 : 0;
			$PIP_SOURCE_FINANCEMENT = $this->request->getPost('PIP_SOURCE_FINANCEMENT') != null ? 1 : 0;
			$DEMANDE_PLANIFICATION_STRATEGIQUE = $this->request->getPost('DEMANDE_PLANIFICATION_STRATEGIQUE') != null ? 1 : 0;
			$DEMANDE_PLANIFICATION_CDMT_CBMT = $this->request->getPost('DEMANDE_PLANIFICATION_CDMT_CBMT') != null ? 1 : 0;
			$DEMANDE_PROGRAMMATION_BUDGETAIRE = $this->request->getPost('DEMANDE_PROGRAMMATION_BUDGETAIRE') != null ? 1 : 0;
			$DEMANDE_ETAT_AVANCEMENT = $this->request->getPost('DEMANDE_ETAT_AVANCEMENT') != null ? 1 : 0;
			
			$TABLEAU_BORD_TAUX_TCD_ENGAGEMENT = $this->request->getPost('TABLEAU_BORD_TAUX_TCD_ENGAGEMENT') != null ? 1 : 0;
			$TABLEAU_BORD_TAUX_EXECUTION_PHASE = $this->request->getPost('TABLEAU_BORD_TAUX_EXECUTION_PHASE') != null ? 1 : 0;
			$TABLEAU_BORD_TCD_VALEUR_PHASE = $this->request->getPost('TABLEAU_BORD_TCD_VALEUR_PHASE') != null ? 1 : 0;
			$TABLEAU_BORD_TCD_VALEUR_INSTITUTION = $this->request->getPost('TABLEAU_BORD_TCD_VALEUR_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST = $this->request->getPost('TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST') != null ? 1 : 0;
			$TABLEAU_BORD_PERFORMANCE_EXECUTION = $this->request->getPost('TABLEAU_BORD_PERFORMANCE_EXECUTION') != null ? 1 : 0;
			$TABLEAU_BORD_BUDGET = $this->request->getPost('TABLEAU_BORD_BUDGET') != null ? 1 : 0;
			$TABLEAU_BORD_EXECUTION_BUDGETAIRE = $this->request->getPost('TABLEAU_BORD_EXECUTION_BUDGETAIRE') != null ? 1 : 0;
			$TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG = $this->request->getPost('TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG') != null ? 1 : 0;
			$TABLEAU_BORD_GRANDE_MASSE = $this->request->getPost('TABLEAU_BORD_GRANDE_MASSE') != null ? 1 : 0;
			$TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION = $this->request->getPost('TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE = $this->request->getPost('TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE') != null ? 1 : 0;
			$TABLEAU_BORD_TRANSFERT = $this->request->getPost('TABLEAU_BORD_TRANSFERT') != null ? 1 : 0;
			
			$TABLEAU_BORD_PIP_MINISTRE_INSTITUTION = $this->request->getPost('TABLEAU_BORD_PIP_MINISTRE_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_TDB_PIP = $this->request->getPost('TABLEAU_BORD_PIP_TDB_PIP') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_FINANCEMENT = $this->request->getPost('TABLEAU_BORD_PIP_FINANCEMENT') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE = $this->request->getPost('TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_STATUT_PROJET = $this->request->getPost('TABLEAU_BORD_PIP_STATUT_PROJET') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_PILIER = $this->request->getPost('TABLEAU_BORD_PIP_PILIER') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE = $this->request->getPost('TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_AXE_INTERVENTION = $this->request->getPost('TABLEAU_BORD_PIP_AXE_INTERVENTION') != null ? 1 : 0;

			$IS_ORDONNANCEMENT_DEJA_VALIDE = $this->request->getPost('IS_ORDONNANCEMENT_DEJA_VALIDE') != null ? 1 : 0;
			$IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU = $this->request->getPost('IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') != null ? 1 : 0;

			$SUIVI_EXECUTION = $this->request->getPost('SUIVI_EXECUTION') != null ? 1 : 0;
			$SUIVI_PTBA = $this->request->getPost('SUIVI_PTBA') != null ? 1 : 0;
			$TRANSMISSION_OBR = $this->request->getPost('TRANSMISSION_OBR') != null ? 1 : 0;
			$IS_AVANT_PRISE_CHARGE = $this->request->getPost('IS_AVANT_PRISE_CHARGE') != null ? 1 : 0;
			$IS_FIN_PROCESSUS = $this->request->getPost('IS_FIN_PROCESSUS') != null ? 1 : 0;
			$TAUX_DOUBLE_COMMANDE = $this->request->getPost('TAUX_DOUBLE_COMMANDE') != null ? 1 : 0;

			      // Cas des salaires
			// -------------------------------------------------------------
			$LIQUIDATION_SALAIRE = $this->request->getPost('LIQUIDATION_SALAIRE') != null ? 1 : 0;
			$CONFIRM_LIQUIDATION_SALAIRE = $this->request->getPost('CONFIRM_LIQUIDATION_SALAIRE') != null ? 1 : 0;
			$ORDONANCEMENT_SALAIRE = $this->request->getPost('ORDONANCEMENT_SALAIRE') != null ? 1 : 0;
			$PRISE_CHARGE_SALAIRE = $this->request->getPost('PRISE_CHARGE_SALAIRE') != null ? 1 : 0;
			$ETABLISSEMENT_TD_NET = $this->request->getPost('ETABLISSEMENT_TD_NET') != null ? 1 : 0;
			$ETABLISSEMENT_TD_RETENUS = $this->request->getPost('ETABLISSEMENT_TD_RETENUS') != null ? 1 : 0;
			$SIGNATURE_DIR_COMPT_SALAIRE = $this->request->getPost('SIGNATURE_DIR_COMPT_SALAIRE') != null ? 1 : 0;
			$SIGNATURE_DGFP_SALAIRE = $this->request->getPost('SIGNATURE_DGFP_SALAIRE') != null ? 1 : 0;
			$SIGNATURE_MIN_SALAIRE = $this->request->getPost('SIGNATURE_MIN_SALAIRE') != null ? 1 : 0;
			$VALIDATION_SALAIRE_NET = $this->request->getPost('VALIDATION_SALAIRE_NET') != null ? 1 : 0;
			$VALIDATION_RETENUS_SALAIRE = $this->request->getPost('VALIDATION_RETENUS_SALAIRE') != null ? 1 : 0;
			$DECAISSEMENT_SALAIRE = $this->request->getPost('DECAISSEMENT_SALAIRE') != null ? 1 : 0;

			$CORRECTION_LIQUIDATION_SALAIRE = $this->request->getPost('CORRECTION_LIQUIDATION_SALAIRE') != null ? 1 : 0;
         
			$table = 'user_profil';
			$columsinsert="PROFIL_DESCR, PROFIL_NIVEAU_ID, NIVEAU_VISUALISATION_ID, UTILISATEURS,PROFIL, MASQUE_SAISI_ENJEUX,  MASQUE_SAISI_INSTITUTION, MASQUE_SAISI_PTBA_PROGRAMMES, MASQUE_SAISI_PTBA_ACTIONS, MASQUE_SAISI_PTBA_ACTIVITES, MASQUE_SAISI_OBSERVATION_FINANCIERES,DOUBLE_COMMANDE_VALIDE_TD, DOUBLE_COMMANDE_TRANSFERT, DOUBLE_COMMANDE_PRESTATAIRE, PARAMETRE_PROCESSUS, PARAMETRE_ETAPE, PARAMETRE_ACTION, PARAMETRE_DOCUMENTS, PARAMETRE_INFO_SUPPLEMENTAIRE, IS_ACTIVE, RAPPORTS_SUIVI_EVALUATION, RAPPORTS_CLASSIFICATION_FONCTIONNEL, RAPPORTS_CLASSIFICATION_ECONOMIQUE, RAPPORTS_CLASSIFICATION_ADMINISTRATIVE, GEOLOCALISATION_CARTE_INSTITUTION, DOUBLE_COMMANDE_ETAT_AVANCEMENT, PTBA_INSTITUTION, PTBA_ACTIVITES, PTBA_CLASSIFICATION_FONCTIONNELLE, PTBA_CLASSIFICATION_ECONOMIQUE, PTBA_CLASSIFICATION_ADMINISTRATIVE, PTBA_PROGRAMMES, PTBA_ACTIONS, PIP_EXECUTION, PIP_COMPILE, PIP_TAUX_ECHANGE,PIP_POURCENTAGE_NOMENCLATURE, PIP_SOURCE_FINANCEMENT,IS_ENGAGEMENT_BUDGETAIRE,  IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED, IS_ENGAGEMENT_JURIDIQUE, IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE, IS_ENGAGEMENT_LIQUIDATION, IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION, IS_ORDONNANCEMENT,IS_ORDONNANCEMENT_DEJA_VALIDE,IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU, IS_TRANSMISSION_SERVICE_PRISE_COMPTE, IS_RECEPTION_SERVICE_PRISE_COMPTE, IS_PAIEMENT, IS_TRANSMISSION_DIRECTEUR_COMPTABLE, IS_RECEPTION_DIRECTEUR_COMPTABLE, IS_TRANSMISSION_BRB, IS_RECEPTION_BRB, IS_DECAISSEMENT, DEMANDE_PLANIFICATION_STRATEGIQUE, DEMANDE_PLANIFICATION_CDMT_CBMT, DEMANDE_PROGRAMMATION_BUDGETAIRE, DEMANDE_ETAT_AVANCEMENT, TABLEAU_BORD_TAUX_TCD_ENGAGEMENT ,TABLEAU_BORD_TAUX_EXECUTION_PHASE, TABLEAU_BORD_TCD_VALEUR_PHASE ,TABLEAU_BORD_TCD_VALEUR_INSTITUTION, TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST,TABLEAU_BORD_PERFORMANCE_EXECUTION, TABLEAU_BORD_BUDGET,TABLEAU_BORD_EXECUTION_BUDGETAIRE,TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG,TABLEAU_BORD_GRANDE_MASSE, TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION, TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE, TABLEAU_BORD_TRANSFERT, TABLEAU_BORD_PIP_MINISTRE_INSTITUTION, TABLEAU_BORD_PIP_TDB_PIP, TABLEAU_BORD_PIP_FINANCEMENT,TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE ,TABLEAU_BORD_PIP_STATUT_PROJET,TABLEAU_BORD_PIP_PILIER,TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE, TABLEAU_BORD_PIP_AXE_INTERVENTION,SUIVI_EXECUTION,SUIVI_PTBA,TRANSMISSION_OBR,TAUX_DOUBLE_COMMANDE,IS_ENGAGEMENT_BUDGETAIRE_SANS_BON,IS_ENGAGEMENT_BUDGETAIRE_CORRECTION,IS_ENGAGEMENT_BUDGETAIRE_ANNULER,IS_ENGAGEMENT_JURIDIQUE_CORRECTION,IS_ENGAGEMENT_JURIDIQUE_ANNULER,IS_LIQUIDATION_CORRECTION,IS_LIQUIDATION_ANNULER,IS_LIQUIDATION_DECISION_CED,IS_ORDONNANCEMENT_MINISTRE,IS_TRANSMISSION_BON_CABINET,IS_TRANSMISSION_CABINET_SPE,ORDONNANCEMENT_CORRECTION_CED,RECEPTION_OBR,IS_PRISE_EN_CHARGE,IS_ETABLISSEMENT_TITRE_DECAISSEMENT,IS_TITRE_SIGNATURE_DIR_COMPTABILITE,IS_TITRE_SIGNATURE_DGFP,IS_TITRE_SIGNATURE_MINISTRE,IS_AVANT_PRISE_CHARGE,IS_FIN_PROCESSUS,LIQUIDATION_SALAIRE,CONFIRM_LIQUIDATION_SALAIRE,ORDONANCEMENT_SALAIRE,PRISE_CHARGE_SALAIRE,ETABLISSEMENT_TD_NET,ETABLISSEMENT_TD_RETENUS,SIGNATURE_DIR_COMPT_SALAIRE,SIGNATURE_DGFP_SALAIRE,SIGNATURE_MIN_SALAIRE,VALIDATION_SALAIRE_NET,VALIDATION_RETENUS_SALAIRE,DECAISSEMENT_SALAIRE,CORRECTION_LIQUIDATION_SALAIRE";   

			$datacolumsinsert="'".$PROFIL_DESCR."', ".$PROFIL_NIVEAU_ID.", ".$NIVEAU_VISUALISATION_ID.", ".$UTILISATEURS.", ".$PROFIL.", ".$MASQUE_SAISI_ENJEUX.", ".$MASQUE_SAISI_INSTITUTION.", ".$MASQUE_SAISI_PTBA_PROGRAMMES.", ".$MASQUE_SAISI_PTBA_ACTIONS.", ".$MASQUE_SAISI_PTBA_ACTIVITES.", ".$MASQUE_SAISI_OBSERVATION_FINANCIERES.",".$DOUBLE_COMMANDE_VALIDE_TD.", ".$DOUBLE_COMMANDE_TRANSFERT.", ".$DOUBLE_COMMANDE_PRESTATAIRE.", ".$PARAMETRE_PROCESSUS.", ".$PARAMETRE_ETAPE.", ".$PARAMETRE_ACTION.", ".$PARAMETRE_DOCUMENTS.", ".$PARAMETRE_INFO_SUPPLEMENTAIRE.", ".$IS_ACTIVE.", ".$RAPPORTS_SUIVI_EVALUATION.", ".$RAPPORTS_CLASSIFICATION_FONCTIONNEL.", ".$RAPPORTS_CLASSIFICATION_ECONOMIQUE.", ".$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE.", ".$GEOLOCALISATION_CARTE_INSTITUTION.", ".$DOUBLE_COMMANDE_ETAT_AVANCEMENT.", ".$PTBA_INSTITUTION.", ".$PTBA_ACTIVITES.", ".$PTBA_CLASSIFICATION_FONCTIONNELLE.", ".$PTBA_CLASSIFICATION_ECONOMIQUE.", ".$PTBA_CLASSIFICATION_ADMINISTRATIVE.", ".$PTBA_PROGRAMMES.", ".$PTBA_ACTIONS.", ".$PIP_EXECUTION.", ".$PIP_COMPILE.", ". $PIP_TAUX_ECHANGE . ", " . $PIP_POURCENTAGE_NOMENCLATURE . " , " .$PIP_SOURCE_FINANCEMENT.",".$IS_ENGAGEMENT_BUDGETAIRE.",". $IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED.",". $IS_ENGAGEMENT_JURIDIQUE.",". $IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE.",". $IS_ENGAGEMENT_LIQUIDATION.",". $IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION.",". $IS_ORDONNANCEMENT.",".$IS_ORDONNANCEMENT_DEJA_VALIDE.",".$IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU.",". $IS_TRANSMISSION_SERVICE_PRISE_COMPTE.",". $IS_RECEPTION_SERVICE_PRISE_COMPTE.", ".$IS_PAIEMENT.", " .$IS_TRANSMISSION_DIRECTEUR_COMPTABLE.", ".$IS_RECEPTION_DIRECTEUR_COMPTABLE.", ".$IS_TRANSMISSION_BRB.", ".$IS_RECEPTION_BRB.", ".$IS_DECAISSEMENT.", ".$DEMANDE_PLANIFICATION_STRATEGIQUE.", ".$DEMANDE_PLANIFICATION_CDMT_CBMT.", ".$DEMANDE_PROGRAMMATION_BUDGETAIRE.", ".$DEMANDE_ETAT_AVANCEMENT.", ".$TABLEAU_BORD_TAUX_TCD_ENGAGEMENT.", ".$TABLEAU_BORD_TAUX_EXECUTION_PHASE.", ".$TABLEAU_BORD_TCD_VALEUR_PHASE.", ".$TABLEAU_BORD_TCD_VALEUR_INSTITUTION.", ".$TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST.",".$TABLEAU_BORD_PERFORMANCE_EXECUTION.",".$TABLEAU_BORD_BUDGET.",".$TABLEAU_BORD_EXECUTION_BUDGETAIRE.",".$TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG.",".$TABLEAU_BORD_GRANDE_MASSE.",".$TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION.",".$TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE.",".$TABLEAU_BORD_TRANSFERT.",".$TABLEAU_BORD_PIP_MINISTRE_INSTITUTION.",".$TABLEAU_BORD_PIP_TDB_PIP.",".$TABLEAU_BORD_PIP_FINANCEMENT.",".$TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE.",".$TABLEAU_BORD_PIP_STATUT_PROJET.",".$TABLEAU_BORD_PIP_PILIER.",".$TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE.",".$TABLEAU_BORD_PIP_AXE_INTERVENTION.",".$SUIVI_EXECUTION.",".$SUIVI_PTBA.",".$TRANSMISSION_OBR.",".$TAUX_DOUBLE_COMMANDE.",".$IS_ENGAGEMENT_BUDGETAIRE_SANS_BON.",".$IS_ENGAGEMENT_BUDGETAIRE_CORRECTION.",".$IS_ENGAGEMENT_BUDGETAIRE_ANNULER.",".$IS_ENGAGEMENT_JURIDIQUE_CORRECTION.",".$IS_ENGAGEMENT_JURIDIQUE_ANNULER.",".$IS_LIQUIDATION_CORRECTION.",".$IS_LIQUIDATION_ANNULER.",".$IS_LIQUIDATION_DECISION_CED.",".$IS_ORDONNANCEMENT_MINISTRE.",".$IS_TRANSMISSION_BON_CABINET.",".$IS_TRANSMISSION_CABINET_SPE.",".$ORDONNANCEMENT_CORRECTION_CED.",".$RECEPTION_OBR.",".$IS_PRISE_EN_CHARGE.",".$IS_ETABLISSEMENT_TITRE_DECAISSEMENT.",".$IS_TITRE_SIGNATURE_DIR_COMPTABILITE.",".$IS_TITRE_SIGNATURE_DGFP.",".$IS_TITRE_SIGNATURE_MINISTRE.",".$IS_AVANT_PRISE_CHARGE.",".$IS_FIN_PROCESSUS.",".$LIQUIDATION_SALAIRE.",".$CONFIRM_LIQUIDATION_SALAIRE.",".$ORDONANCEMENT_SALAIRE.",".$PRISE_CHARGE_SALAIRE.",".$ETABLISSEMENT_TD_NET.",".$ETABLISSEMENT_TD_RETENUS.",".$SIGNATURE_DIR_COMPT_SALAIRE.",".$SIGNATURE_DGFP_SALAIRE.",".$SIGNATURE_MIN_SALAIRE.",".$VALIDATION_SALAIRE_NET.",".$VALIDATION_RETENUS_SALAIRE.",".$DECAISSEMENT_SALAIRE.",".$CORRECTION_LIQUIDATION_SALAIRE."";

			$this->save_all_table($table,$columsinsert,$datacolumsinsert);
			$data = [
				'message' => lang('messages_lang.message_success')
			];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/User_profil');
		}
		else
		{
			return $this->ajout();
		}
	}

	//fonction get pour recuperer les données a modifier
	public function getOne($id)
	{
		$session  = \Config\Services::session();		
		$user_id ='';
		$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$IMPORTnd_proc = $this->getBindParms('*','user_profil','MD5(PROFIL_ID)="'.$id.'"','PROFIL_ID ASC');
		$IMPORTnd_proc=str_replace('\\', '', $IMPORTnd_proc);
		$data['profil']= $this->ModelPs->getRequeteOne($callpsreq, $IMPORTnd_proc);

		$bind_profil = $this->getBindParms('PROFIL_NIVEAU_ID,DESC_PROFIL_NIVEAU','user_profil_niveau','1','PROFIL_NIVEAU_ID ASC');
		$data['profil_niveau']= $this->ModelPs->getRequete($callpsreq, $bind_profil);

		$bind_visualisation=$this->getBindParms('NIVEAU_VISUALISATION_ID,DESC_NIVEAU_VISUALISATION','user_profil_niveau_visualisation','1','DESC_NIVEAU_VISUALISATION ASC');
		$data['niveau_visualisation']= $this->ModelPs->getRequete($callpsreq, $bind_visualisation);

		return view('App\Modules\Administration\Views\User_profil_update_view',$data);
	}

	//Mise à jour des profil
	public function update()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$PROFIL_RETENU="";
		$PROFIL_DESCR_SAISI =$this->request->getPost('PROFIL_DESCR');
		$PROFIL_DESCR_EXISTANT =$this->request->getPost('PROFIL_DES');
		
		$rules = [
			'PROFIL_DESCR' => [
				'rules' => 'required|max_length[100]',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
					'max_length' => '<font style="color:red;size:2px;">Vous ne pouvez pas saisir plus de 100 caractères</font>',
					'is_unique' => '<font style="color:red;size:2px;">Le profil existe déjà</font>'
				]
			],
			'PROFIL_NIVEAU_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
				]
			],
			'NIVEAU_VISUALISATION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
				]
			]
		];

		$this->validation->setRules($rules);
		$PROFIL_ID =$this->request->getPost('PROFIL_ID');
		if($this->validation->withRequest($this->request)->run())
		{
			$PROFIL_DESCR = $this->request->getPost('PROFIL_DESCR');
			$PROFIL_NIVEAU_ID = $this->request->getPost('PROFIL_NIVEAU_ID');
			$NIVEAU_VISUALISATION_ID = $this->request->getPost('NIVEAU_VISUALISATION_ID');
			$IS_ACTIVE=1;

			if($UTILISATEURS = $this->request->getPost('UTILISATEURS')!=null)
				{$UTILISATEURS=1;}else{$UTILISATEURS=0;}

			
			if($PROFIL = $this->request->getPost('PROFIL')!=null)
				{$PROFIL=1;}else{$PROFIL=0;}

			if($IS_ENGAGEMENT_BUDGETAIRE = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE')!=null)
			{$IS_ENGAGEMENT_BUDGETAIRE=1;}else{$IS_ENGAGEMENT_BUDGETAIRE=0;}

			if($IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED = $this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED=1;}else{$IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED=0;}

			if($IS_ENGAGEMENT_JURIDIQUE = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE')!=null)
				{$IS_ENGAGEMENT_JURIDIQUE=1;}else{$IS_ENGAGEMENT_JURIDIQUE=0;}

			if($IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=$this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=1;}else{$IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE=0;}

			if($IS_ENGAGEMENT_LIQUIDATION = $this->request->getPost('IS_ENGAGEMENT_LIQUIDATION')!=null)
				{$IS_ENGAGEMENT_LIQUIDATION=1;}else{$IS_ENGAGEMENT_LIQUIDATION=0;}

			if($IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=$this->request->getPost('IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION')!=null)
				{$IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=1;}else{$IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION=0;}

			if($IS_ORDONNANCEMENT = $this->request->getPost('IS_ORDONNANCEMENT')!=null)
				{$IS_ORDONNANCEMENT=1;}else{$IS_ORDONNANCEMENT=0;}

			if($IS_TRANSMISSION_SERVICE_PRISE_COMPTE=$this->request->getPost('IS_TRANSMISSION_SERVICE_PRISE_COMPTE')!=null)
				{$IS_TRANSMISSION_SERVICE_PRISE_COMPTE=1;}else{$IS_TRANSMISSION_SERVICE_PRISE_COMPTE=0;}

			if($IS_RECEPTION_SERVICE_PRISE_COMPTE = $this->request->getPost('IS_RECEPTION_SERVICE_PRISE_COMPTE')!=null)
				{$IS_RECEPTION_SERVICE_PRISE_COMPTE=1;}else{$IS_RECEPTION_SERVICE_PRISE_COMPTE=0;}

			if($IS_PAIEMENT = $this->request->getPost('IS_PAIEMENT')!=null)
				{$IS_PAIEMENT=1;}else{$IS_PAIEMENT=0;}

			if($IS_TRANSMISSION_DIRECTEUR_COMPTABLE = $this->request->getPost('IS_TRANSMISSION_DIRECTEUR_COMPTABLE')!=null)
				{$IS_TRANSMISSION_DIRECTEUR_COMPTABLE=1;}else{$IS_TRANSMISSION_DIRECTEUR_COMPTABLE=0;}

			if($IS_RECEPTION_DIRECTEUR_COMPTABLE = $this->request->getPost('IS_RECEPTION_DIRECTEUR_COMPTABLE')!=null)
				{$IS_RECEPTION_DIRECTEUR_COMPTABLE=1;}else{$IS_RECEPTION_DIRECTEUR_COMPTABLE=0;}

			if($IS_TRANSMISSION_BRB = $this->request->getPost('IS_TRANSMISSION_BRB')!=null)
				{$IS_TRANSMISSION_BRB=1;}else{$IS_TRANSMISSION_BRB=0;}

			if($IS_RECEPTION_BRB = $this->request->getPost('IS_RECEPTION_BRB')!=null)
				{$IS_RECEPTION_BRB=1;}else{$IS_RECEPTION_BRB=0;}

			if($IS_DECAISSEMENT = $this->request->getPost('IS_DECAISSEMENT')!=null)
				{$IS_DECAISSEMENT=1;}else{$IS_DECAISSEMENT=0;}

			if($DOUBLE_COMMANDE_VALIDE_TD = $this->request->getPost('DOUBLE_COMMANDE_VALIDE_TD')!=null)
				{$DOUBLE_COMMANDE_VALIDE_TD=1;}else{$DOUBLE_COMMANDE_VALIDE_TD=0;}

			if($DOUBLE_COMMANDE_TRANSFERT = $this->request->getPost('DOUBLE_COMMANDE_TRANSFERT')!=null)
				{$DOUBLE_COMMANDE_TRANSFERT=1;}else{$DOUBLE_COMMANDE_TRANSFERT=0;}

			if($DOUBLE_COMMANDE_PRESTATAIRE = $this->request->getPost('DOUBLE_COMMANDE_PRESTATAIRE')!=null)
				{$DOUBLE_COMMANDE_PRESTATAIRE=1;}else{$DOUBLE_COMMANDE_PRESTATAIRE=0;}

			if($PARAMETRE_PROCESSUS = $this->request->getPost('PARAMETRE_PROCESSUS')!=null)
				{$PARAMETRE_PROCESSUS=1;}else{$PARAMETRE_PROCESSUS=0;}
			
			if($PARAMETRE_ETAPE = $this->request->getPost('PARAMETRE_ETAPE')!=null)
				{$PARAMETRE_ETAPE=1;}else{$PARAMETRE_ETAPE=0;}

			if($PARAMETRE_ACTION = $this->request->getPost('PARAMETRE_ACTION')!=null)
				{$PARAMETRE_ACTION=1;}else{$PARAMETRE_ACTION=0;}

			if($PARAMETRE_DOCUMENTS = $this->request->getPost('PARAMETRE_DOCUMENTS')!=null)
				{$PARAMETRE_DOCUMENTS=1;}else{$PARAMETRE_DOCUMENTS=0;}

			if($PARAMETRE_INFO_SUPPLEMENTAIRE = $this->request->getPost('PARAMETRE_INFO_SUPPLEMENTAIRE')!=null)
				{$PARAMETRE_INFO_SUPPLEMENTAIRE=1;}else{$PARAMETRE_INFO_SUPPLEMENTAIRE=0;}

			if($MASQUE_SAISI_ENJEUX = $this->request->getPost('MASQUE_SAISI_ENJEUX')!=null)
				{$MASQUE_SAISI_ENJEUX=1;}else{$MASQUE_SAISI_ENJEUX=0;}

			if($MASQUE_SAISI_PILIERS = $this->request->getPost('MASQUE_SAISI_PILIERS')!=null)
				{$MASQUE_SAISI_PILIERS=1;}else{$MASQUE_SAISI_PILIERS=0;}

			if($MASQUE_SAISI_INSTITUTION = $this->request->getPost('MASQUE_SAISI_INSTITUTION')!=null)
				{$MASQUE_SAISI_INSTITUTION=1;}else{$MASQUE_SAISI_INSTITUTION=0;}

			if($MASQUE_SAISI_PTBA_PROGRAMMES = $this->request->getPost('MASQUE_SAISI_PTBA_PROGRAMMES')!=null)
				{$MASQUE_SAISI_PTBA_PROGRAMMES=1;}else{$MASQUE_SAISI_PTBA_PROGRAMMES=0;}

			if($MASQUE_SAISI_PTBA_ACTIONS = $this->request->getPost('MASQUE_SAISI_PTBA_ACTIONS')!=null)
				{$MASQUE_SAISI_PTBA_ACTIONS=1;}else{$MASQUE_SAISI_PTBA_ACTIONS=0;}

			if($MASQUE_SAISI_PTBA_ACTIVITES = $this->request->getPost('MASQUE_SAISI_PTBA_ACTIVITES')!=null)
				{$MASQUE_SAISI_PTBA_ACTIVITES=1;}else{$MASQUE_SAISI_PTBA_ACTIVITES=0;}

			if($MASQUE_SAISI_OBSERVATION_FINANCIERES = $this->request->getPost('MASQUE_SAISI_OBSERVATION_FINANCIERES')!=null)
				{$MASQUE_SAISI_OBSERVATION_FINANCIERES=1;}else{$MASQUE_SAISI_OBSERVATION_FINANCIERES=0;}

			if($PTBA_INSTITUTION = $this->request->getPost('PTBA_INSTITUTION')!=null)
				{$PTBA_INSTITUTION=1;}else{$PTBA_INSTITUTION=0;}

			if($PTBA_PROGRAMMES = $this->request->getPost('PTBA_PROGRAMMES')!=null)
				{$PTBA_PROGRAMMES=1;}else{$PTBA_PROGRAMMES=0;}

			if($PTBA_ACTIONS = $this->request->getPost('PTBA_ACTIONS')!=null)
				{$PTBA_ACTIONS=1;}else{$PTBA_ACTIONS=0;}

			if($PTBA_ACTIVITES = $this->request->getPost('PTBA_ACTIVITES')!=null)
				{$PTBA_ACTIVITES=1;}else{$PTBA_ACTIVITES=0;}

			if($PTBA_CLASSIFICATION_ECONOMIQUE = $this->request->getPost('PTBA_CLASSIFICATION_ECONOMIQUE')!=null)
				{$PTBA_CLASSIFICATION_ECONOMIQUE=1;}else{$PTBA_CLASSIFICATION_ECONOMIQUE=0;}

			if($PTBA_CLASSIFICATION_ADMINISTRATIVE = $this->request->getPost('PTBA_CLASSIFICATION_ADMINISTRATIVE')!=null)
				{$PTBA_CLASSIFICATION_ADMINISTRATIVE=1;}else{$PTBA_CLASSIFICATION_ADMINISTRATIVE=0;}

			if($PTBA_CLASSIFICATION_FONCTIONNELLE = $this->request->getPost('PTBA_CLASSIFICATION_FONCTIONNELLE')!=null)
				{$PTBA_CLASSIFICATION_FONCTIONNELLE=1;}else{$PTBA_CLASSIFICATION_FONCTIONNELLE=0;}

			if($PIP_EXECUTION = $this->request->getPost('PIP_EXECUTION')!=null)
				{$PIP_EXECUTION=1;}else{$PIP_EXECUTION=0;}

			if($PIP_COMPILE = $this->request->getPost('PIP_COMPILE')!=null)
				{$PIP_COMPILE=1;}else{$PIP_COMPILE=0;}

			if($RAPPORTS_SUIVI_EVALUATION = $this->request->getPost('RAPPORTS_SUIVI_EVALUATION')!=null)
				{$RAPPORTS_SUIVI_EVALUATION=1;}else{$RAPPORTS_SUIVI_EVALUATION=0;}

			if($RAPPORTS_CLASSIFICATION_ECONOMIQUE = $this->request->getPost('RAPPORTS_CLASSIFICATION_ECONOMIQUE')!=null)
				{$RAPPORTS_CLASSIFICATION_ECONOMIQUE=1;}else{$RAPPORTS_CLASSIFICATION_ECONOMIQUE=0;}

			if($RAPPORTS_CLASSIFICATION_ADMINISTRATIVE = $this->request->getPost('RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=null)
				{$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE=1;}else{$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE=0;}

			if($RAPPORTS_CLASSIFICATION_FONCTIONNEL = $this->request->getPost('RAPPORTS_CLASSIFICATION_FONCTIONNEL')!=null)
				{$RAPPORTS_CLASSIFICATION_FONCTIONNEL=1;}else{$RAPPORTS_CLASSIFICATION_FONCTIONNEL=0;}

			if($GEOLOCALISATION_CARTE_INSTITUTION = $this->request->getPost('GEOLOCALISATION_CARTE_INSTITUTION')!=null)
				{$GEOLOCALISATION_CARTE_INSTITUTION=1;}else{$GEOLOCALISATION_CARTE_INSTITUTION=0;}

			if($DOUBLE_COMMANDE_ETAT_AVANCEMENT = $this->request->getPost('DOUBLE_COMMANDE_ETAT_AVANCEMENT')!=null)
				{$DOUBLE_COMMANDE_ETAT_AVANCEMENT=1;}else{$DOUBLE_COMMANDE_ETAT_AVANCEMENT=0;}

			$PIP_TAUX_ECHANGE = $this->request->getPost('PIP_TAUX_ECHANGE') != null ? 1 : 0;
			$PIP_POURCENTAGE_NOMENCLATURE = $this->request->getPost('PIP_POURCENTAGE_NOMENCLATURE') != null ? 1 : 0;
			$PIP_SOURCE_FINANCEMENT = $this->request->getPost('PIP_SOURCE_FINANCEMENT') != null ? 1 : 0;

			$DEMANDE_PLANIFICATION_STRATEGIQUE = $this->request->getPost('DEMANDE_PLANIFICATION_STRATEGIQUE') != null ? 1 : 0;
			$DEMANDE_PLANIFICATION_CDMT_CBMT = $this->request->getPost('DEMANDE_PLANIFICATION_CDMT_CBMT') != null ? 1 : 0;
			$DEMANDE_PROGRAMMATION_BUDGETAIRE = $this->request->getPost('DEMANDE_PROGRAMMATION_BUDGETAIRE') != null ? 1 : 0;
			$DEMANDE_ETAT_AVANCEMENT = $this->request->getPost('DEMANDE_ETAT_AVANCEMENT') != null ? 1 : 0;

			$TABLEAU_BORD_TAUX_TCD_ENGAGEMENT = $this->request->getPost('TABLEAU_BORD_TAUX_TCD_ENGAGEMENT') != null ? 1 : 0;
			$TABLEAU_BORD_TAUX_EXECUTION_PHASE = $this->request->getPost('TABLEAU_BORD_TAUX_EXECUTION_PHASE') != null ? 1 : 0;
			$TABLEAU_BORD_TCD_VALEUR_PHASE = $this->request->getPost('TABLEAU_BORD_TCD_VALEUR_PHASE') != null ? 1 : 0;
			$TABLEAU_BORD_TCD_VALEUR_INSTITUTION = $this->request->getPost('TABLEAU_BORD_TCD_VALEUR_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST = $this->request->getPost('TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST') != null ? 1 : 0;
			$TABLEAU_BORD_PERFORMANCE_EXECUTION = $this->request->getPost('TABLEAU_BORD_PERFORMANCE_EXECUTION') != null ? 1 : 0;
			$TABLEAU_BORD_BUDGET = $this->request->getPost('TABLEAU_BORD_BUDGET') != null ? 1 : 0;
			$TABLEAU_BORD_EXECUTION_BUDGETAIRE = $this->request->getPost('TABLEAU_BORD_EXECUTION_BUDGETAIRE') != null ? 1 : 0;
			$TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG = $this->request->getPost('TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG') != null ? 1 : 0;
			$TABLEAU_BORD_GRANDE_MASSE = $this->request->getPost('TABLEAU_BORD_GRANDE_MASSE') != null ? 1 : 0;
			$TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION = $this->request->getPost('TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE = $this->request->getPost('TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE') != null ? 1 : 0;
			$TABLEAU_BORD_TRANSFERT = $this->request->getPost('TABLEAU_BORD_TRANSFERT') != null ? 1 : 0;

			$TABLEAU_BORD_PIP_MINISTRE_INSTITUTION = $this->request->getPost('TABLEAU_BORD_PIP_MINISTRE_INSTITUTION') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_TDB_PIP = $this->request->getPost('TABLEAU_BORD_PIP_TDB_PIP') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_FINANCEMENT = $this->request->getPost('TABLEAU_BORD_PIP_FINANCEMENT') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE = $this->request->getPost('TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_STATUT_PROJET = $this->request->getPost('TABLEAU_BORD_PIP_STATUT_PROJET') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_PILIER = $this->request->getPost('TABLEAU_BORD_PIP_PILIER') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE = $this->request->getPost('TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE') != null ? 1 : 0;
			$TABLEAU_BORD_PIP_AXE_INTERVENTION = $this->request->getPost('TABLEAU_BORD_PIP_AXE_INTERVENTION') != null ? 1 : 0;
			$IS_ORDONNANCEMENT_DEJA_VALIDE = $this->request->getPost('IS_ORDONNANCEMENT_DEJA_VALIDE') != null ? 1 : 0;
			$IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU = $this->request->getPost('IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') != null ? 1 : 0;

			$SUIVI_EXECUTION = $this->request->getPost('SUIVI_EXECUTION') != null ? 1 : 0;
			$SUIVI_PTBA = $this->request->getPost('SUIVI_PTBA') != null ? 1 : 0;
			$TRANSMISSION_OBR = $this->request->getPost('TRANSMISSION_OBR') != null ? 1 : 0;
			$IS_AVANT_PRISE_CHARGE = $this->request->getPost('IS_AVANT_PRISE_CHARGE') != null ? 1 : 0;
			$TAUX_DOUBLE_COMMANDE = $this->request->getPost('TAUX_DOUBLE_COMMANDE') != null ? 1 : 0;

			$IS_ENGAGEMENT_BUDGETAIRE_SANS_BON = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_SANS_BON') != null ? 1 : 0;
			$IS_ENGAGEMENT_BUDGETAIRE_CORRECTION = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_CORRECTION') != null ? 1 : 0;
			$IS_ENGAGEMENT_BUDGETAIRE_ANNULER = $this->request->getPost('IS_ENGAGEMENT_BUDGETAIRE_ANNULER') != null ? 1 : 0;
			$IS_ENGAGEMENT_JURIDIQUE_CORRECTION = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE_CORRECTION') != null ? 1 : 0;
			$IS_ENGAGEMENT_JURIDIQUE_ANNULER = $this->request->getPost('IS_ENGAGEMENT_JURIDIQUE_ANNULER') != null ? 1 : 0;
			$IS_LIQUIDATION_CORRECTION = $this->request->getPost('IS_LIQUIDATION_CORRECTION') != null ? 1 : 0;
			$IS_LIQUIDATION_ANNULER = $this->request->getPost('IS_LIQUIDATION_ANNULER') != null ? 1 : 0;
			$IS_LIQUIDATION_DECISION_CED = $this->request->getPost('IS_LIQUIDATION_DECISION_CED') != null ? 1 : 0;
			$IS_ORDONNANCEMENT_MINISTRE = $this->request->getPost('IS_ORDONNANCEMENT_MINISTRE') != null ? 1 : 0;
			$IS_TRANSMISSION_BON_CABINET = $this->request->getPost('IS_TRANSMISSION_BON_CABINET') != null ? 1 : 0;
			$IS_TRANSMISSION_CABINET_SPE = $this->request->getPost('IS_TRANSMISSION_CABINET_SPE') != null ? 1 : 0;
			$ORDONNANCEMENT_CORRECTION_CED = $this->request->getPost('ORDONNANCEMENT_CORRECTION_CED') != null ? 1 : 0;
			$RECEPTION_OBR = $this->request->getPost('RECEPTION_OBR') != null ? 1 : 0;
			$IS_PRISE_EN_CHARGE = $this->request->getPost('IS_PRISE_EN_CHARGE') != null ? 1 : 0;
			$IS_ETABLISSEMENT_TITRE_DECAISSEMENT = $this->request->getPost('IS_ETABLISSEMENT_TITRE_DECAISSEMENT') != null ? 1 : 0;
			$IS_TITRE_SIGNATURE_DIR_COMPTABILITE = $this->request->getPost('IS_TITRE_SIGNATURE_DIR_COMPTABILITE') != null ? 1 : 0;
			$IS_TITRE_SIGNATURE_DGFP = $this->request->getPost('IS_TITRE_SIGNATURE_DGFP') != null ? 1 : 0;
			$IS_TITRE_SIGNATURE_MINISTRE = $this->request->getPost('IS_TITRE_SIGNATURE_MINISTRE') != null ? 1 : 0;
			$IS_AVANT_PRISE_CHARGE = $this->request->getPost('IS_AVANT_PRISE_CHARGE') != null ? 1 : 0;
			$IS_FIN_PROCESSUS = $this->request->getPost('IS_FIN_PROCESSUS') != null ? 1 : 0;
						      // Cas des salaires
			// -------------------------------------------------------------
			$LIQUIDATION_SALAIRE = $this->request->getPost('LIQUIDATION_SALAIRE') != null ? 1 : 0;
			$CONFIRM_LIQUIDATION_SALAIRE = $this->request->getPost('CONFIRM_LIQUIDATION_SALAIRE') != null ? 1 : 0;
			$ORDONANCEMENT_SALAIRE = $this->request->getPost('ORDONANCEMENT_SALAIRE') != null ? 1 : 0;
			$PRISE_CHARGE_SALAIRE = $this->request->getPost('PRISE_CHARGE_SALAIRE') != null ? 1 : 0;
			$ETABLISSEMENT_TD_NET = $this->request->getPost('ETABLISSEMENT_TD_NET') != null ? 1 : 0;
			$ETABLISSEMENT_TD_RETENUS = $this->request->getPost('ETABLISSEMENT_TD_RETENUS') != null ? 1 : 0;
			$SIGNATURE_DIR_COMPT_SALAIRE = $this->request->getPost('SIGNATURE_DIR_COMPT_SALAIRE') != null ? 1 : 0;
			$SIGNATURE_DGFP_SALAIRE = $this->request->getPost('SIGNATURE_DGFP_SALAIRE') != null ? 1 : 0;
			$SIGNATURE_MIN_SALAIRE = $this->request->getPost('SIGNATURE_MIN_SALAIRE') != null ? 1 : 0;
			$VALIDATION_SALAIRE_NET = $this->request->getPost('VALIDATION_SALAIRE_NET') != null ? 1 : 0;
			$VALIDATION_RETENUS_SALAIRE = $this->request->getPost('VALIDATION_RETENUS_SALAIRE') != null ? 1 : 0;
			$DECAISSEMENT_SALAIRE = $this->request->getPost('DECAISSEMENT_SALAIRE') != null ? 1 : 0;
			$CORRECTION_LIQUIDATION_SALAIRE = $this->request->getPost('CORRECTION_LIQUIDATION_SALAIRE') != null ? 1 : 0;


			$table = 'user_profil';
			$where='PROFIL_ID='.$PROFIL_ID;
			$data='PROFIL_DESCR="'.$PROFIL_DESCR.'", PROFIL_NIVEAU_ID='.$PROFIL_NIVEAU_ID.', NIVEAU_VISUALISATION_ID='.$NIVEAU_VISUALISATION_ID.', UTILISATEURS='.$UTILISATEURS.',PROFIL='.$PROFIL.',MASQUE_SAISI_ENJEUX='.$MASQUE_SAISI_ENJEUX.',MASQUE_SAISI_INSTITUTION='.$MASQUE_SAISI_INSTITUTION.', MASQUE_SAISI_PTBA_PROGRAMMES='.$MASQUE_SAISI_PTBA_PROGRAMMES.', MASQUE_SAISI_PTBA_ACTIONS='.$MASQUE_SAISI_PTBA_ACTIONS.', MASQUE_SAISI_PTBA_ACTIVITES='.$MASQUE_SAISI_PTBA_ACTIVITES.', MASQUE_SAISI_OBSERVATION_FINANCIERES='.$MASQUE_SAISI_OBSERVATION_FINANCIERES.',DOUBLE_COMMANDE_VALIDE_TD='.$DOUBLE_COMMANDE_VALIDE_TD.',DOUBLE_COMMANDE_TRANSFERT='.$DOUBLE_COMMANDE_TRANSFERT.', DOUBLE_COMMANDE_PRESTATAIRE='.$DOUBLE_COMMANDE_PRESTATAIRE.', PARAMETRE_PROCESSUS='.$PARAMETRE_PROCESSUS.', PARAMETRE_ETAPE='.$PARAMETRE_ETAPE.', PARAMETRE_ACTION='.$PARAMETRE_ACTION.', PARAMETRE_DOCUMENTS='.$PARAMETRE_DOCUMENTS.', PARAMETRE_INFO_SUPPLEMENTAIRE='.$PARAMETRE_INFO_SUPPLEMENTAIRE.', RAPPORTS_SUIVI_EVALUATION='.$RAPPORTS_SUIVI_EVALUATION.', RAPPORTS_CLASSIFICATION_FONCTIONNEL='.$RAPPORTS_CLASSIFICATION_FONCTIONNEL.', RAPPORTS_CLASSIFICATION_ECONOMIQUE='.$RAPPORTS_CLASSIFICATION_ECONOMIQUE.', RAPPORTS_CLASSIFICATION_ADMINISTRATIVE='.$RAPPORTS_CLASSIFICATION_ADMINISTRATIVE.', GEOLOCALISATION_CARTE_INSTITUTION='.$GEOLOCALISATION_CARTE_INSTITUTION.', DOUBLE_COMMANDE_ETAT_AVANCEMENT='.$DOUBLE_COMMANDE_ETAT_AVANCEMENT.', PTBA_INSTITUTION='.$PTBA_INSTITUTION.', PTBA_ACTIVITES='.$PTBA_ACTIVITES.', PTBA_CLASSIFICATION_FONCTIONNELLE='.$PTBA_CLASSIFICATION_FONCTIONNELLE.', PTBA_CLASSIFICATION_ECONOMIQUE='.$PTBA_CLASSIFICATION_ECONOMIQUE.', PTBA_CLASSIFICATION_ADMINISTRATIVE='.$PTBA_CLASSIFICATION_ADMINISTRATIVE.', PTBA_PROGRAMMES='.$PTBA_PROGRAMMES.', PTBA_ACTIONS='.$PTBA_ACTIONS.', PIP_EXECUTION='.$PIP_EXECUTION.', PIP_COMPILE='.$PIP_COMPILE.', PIP_TAUX_ECHANGE='. $PIP_TAUX_ECHANGE . ', PIP_POURCENTAGE_NOMENCLATURE='.$PIP_POURCENTAGE_NOMENCLATURE . ', PIP_SOURCE_FINANCEMENT = ' . $PIP_SOURCE_FINANCEMENT . ',IS_ENGAGEMENT_BUDGETAIRE='.$IS_ENGAGEMENT_BUDGETAIRE.',IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED='.$IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED.',IS_ENGAGEMENT_JURIDIQUE='.$IS_ENGAGEMENT_JURIDIQUE.',IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE='.$IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE.',IS_ENGAGEMENT_LIQUIDATION='.$IS_ENGAGEMENT_LIQUIDATION.',IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION='.$IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION.',IS_ORDONNANCEMENT='.$IS_ORDONNANCEMENT.',IS_ORDONNANCEMENT_DEJA_VALIDE='.$IS_ORDONNANCEMENT_DEJA_VALIDE.',IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU='.$IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU.',IS_TRANSMISSION_SERVICE_PRISE_COMPTE='.$IS_TRANSMISSION_SERVICE_PRISE_COMPTE.',IS_RECEPTION_SERVICE_PRISE_COMPTE='.$IS_RECEPTION_SERVICE_PRISE_COMPTE.',IS_PAIEMENT='.$IS_PAIEMENT.',IS_TRANSMISSION_DIRECTEUR_COMPTABLE='.$IS_TRANSMISSION_DIRECTEUR_COMPTABLE.',IS_RECEPTION_DIRECTEUR_COMPTABLE='.$IS_RECEPTION_DIRECTEUR_COMPTABLE.',IS_TRANSMISSION_BRB='.$IS_TRANSMISSION_BRB.',IS_RECEPTION_BRB='.$IS_RECEPTION_BRB.',IS_DECAISSEMENT='.$IS_DECAISSEMENT.',DEMANDE_PLANIFICATION_STRATEGIQUE='.$DEMANDE_PLANIFICATION_STRATEGIQUE.',DEMANDE_PLANIFICATION_CDMT_CBMT='.$DEMANDE_PLANIFICATION_CDMT_CBMT.',DEMANDE_PROGRAMMATION_BUDGETAIRE='.$DEMANDE_PROGRAMMATION_BUDGETAIRE.',DEMANDE_ETAT_AVANCEMENT='.$DEMANDE_ETAT_AVANCEMENT.', TABLEAU_BORD_TAUX_TCD_ENGAGEMENT='.$TABLEAU_BORD_TAUX_TCD_ENGAGEMENT.',TABLEAU_BORD_TAUX_EXECUTION_PHASE='.$TABLEAU_BORD_TAUX_EXECUTION_PHASE.',TABLEAU_BORD_TCD_VALEUR_PHASE='.$TABLEAU_BORD_TCD_VALEUR_PHASE.' ,TABLEAU_BORD_TCD_VALEUR_INSTITUTION= '.$TABLEAU_BORD_TCD_VALEUR_INSTITUTION.', TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST='.$TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST.', TABLEAU_BORD_PERFORMANCE_EXECUTION='.$TABLEAU_BORD_PERFORMANCE_EXECUTION.', TABLEAU_BORD_BUDGET='.$TABLEAU_BORD_BUDGET.',TABLEAU_BORD_EXECUTION_BUDGETAIRE='.$TABLEAU_BORD_EXECUTION_BUDGETAIRE.',TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG='.$TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG.',TABLEAU_BORD_GRANDE_MASSE='.$TABLEAU_BORD_GRANDE_MASSE.', TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION='.$TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION.', TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE='.$TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE.', TABLEAU_BORD_TRANSFERT='.$TABLEAU_BORD_TRANSFERT.', TABLEAU_BORD_PIP_MINISTRE_INSTITUTION='.$TABLEAU_BORD_PIP_MINISTRE_INSTITUTION.', TABLEAU_BORD_PIP_TDB_PIP='.$TABLEAU_BORD_PIP_TDB_PIP.', TABLEAU_BORD_PIP_FINANCEMENT='.$TABLEAU_BORD_PIP_FINANCEMENT.',TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE='.$TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE.' ,TABLEAU_BORD_PIP_STATUT_PROJET='.$TABLEAU_BORD_PIP_STATUT_PROJET.',TABLEAU_BORD_PIP_PILIER='.$TABLEAU_BORD_PIP_PILIER.',TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE='.$TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE.', TABLEAU_BORD_PIP_AXE_INTERVENTION='.$TABLEAU_BORD_PIP_AXE_INTERVENTION.', SUIVI_EXECUTION='.$SUIVI_EXECUTION.', SUIVI_PTBA='.$SUIVI_PTBA.', TRANSMISSION_OBR='.$TRANSMISSION_OBR.', TAUX_DOUBLE_COMMANDE='.$TAUX_DOUBLE_COMMANDE.',IS_ENGAGEMENT_BUDGETAIRE_SANS_BON='.$IS_ENGAGEMENT_BUDGETAIRE_SANS_BON.',IS_ENGAGEMENT_BUDGETAIRE_CORRECTION='.$IS_ENGAGEMENT_BUDGETAIRE_CORRECTION.',IS_ENGAGEMENT_BUDGETAIRE_ANNULER='.$IS_ENGAGEMENT_BUDGETAIRE_ANNULER.',IS_ENGAGEMENT_JURIDIQUE_CORRECTION='.$IS_ENGAGEMENT_JURIDIQUE_CORRECTION.',IS_ENGAGEMENT_JURIDIQUE_ANNULER='.$IS_ENGAGEMENT_JURIDIQUE_ANNULER.',IS_LIQUIDATION_CORRECTION='.$IS_LIQUIDATION_CORRECTION.',IS_LIQUIDATION_ANNULER='.$IS_LIQUIDATION_ANNULER.',IS_LIQUIDATION_DECISION_CED='.$IS_LIQUIDATION_DECISION_CED.',IS_ORDONNANCEMENT_MINISTRE='.$IS_ORDONNANCEMENT_MINISTRE.',IS_TRANSMISSION_BON_CABINET='.$IS_TRANSMISSION_BON_CABINET.',IS_TRANSMISSION_CABINET_SPE='.$IS_TRANSMISSION_CABINET_SPE.',ORDONNANCEMENT_CORRECTION_CED='.$ORDONNANCEMENT_CORRECTION_CED.',RECEPTION_OBR='.$RECEPTION_OBR.',IS_PRISE_EN_CHARGE='.$IS_PRISE_EN_CHARGE.',IS_ETABLISSEMENT_TITRE_DECAISSEMENT='.$IS_ETABLISSEMENT_TITRE_DECAISSEMENT.',IS_TITRE_SIGNATURE_DIR_COMPTABILITE='.$IS_TITRE_SIGNATURE_DIR_COMPTABILITE.',IS_TITRE_SIGNATURE_DGFP='.$IS_TITRE_SIGNATURE_DGFP.',IS_TITRE_SIGNATURE_MINISTRE='.$IS_TITRE_SIGNATURE_MINISTRE.',IS_AVANT_PRISE_CHARGE='.$IS_AVANT_PRISE_CHARGE.',IS_FIN_PROCESSUS='.$IS_FIN_PROCESSUS.',LIQUIDATION_SALAIRE='.$LIQUIDATION_SALAIRE.',CONFIRM_LIQUIDATION_SALAIRE='.$CONFIRM_LIQUIDATION_SALAIRE.',ORDONANCEMENT_SALAIRE='.$ORDONANCEMENT_SALAIRE.',PRISE_CHARGE_SALAIRE='.$PRISE_CHARGE_SALAIRE.',ETABLISSEMENT_TD_NET='.$ETABLISSEMENT_TD_NET.',ETABLISSEMENT_TD_RETENUS='.$ETABLISSEMENT_TD_RETENUS.',SIGNATURE_DIR_COMPT_SALAIRE='.$SIGNATURE_DIR_COMPT_SALAIRE.',SIGNATURE_DGFP_SALAIRE='.$SIGNATURE_DGFP_SALAIRE.',SIGNATURE_MIN_SALAIRE='.$SIGNATURE_MIN_SALAIRE.',VALIDATION_SALAIRE_NET='.$VALIDATION_SALAIRE_NET.',VALIDATION_RETENUS_SALAIRE='.$VALIDATION_RETENUS_SALAIRE.',DECAISSEMENT_SALAIRE='.$DECAISSEMENT_SALAIRE.',CORRECTION_LIQUIDATION_SALAIRE='.$CORRECTION_LIQUIDATION_SALAIRE;

			

			$this->update_all_table($table,$data,$where);
			$data = [
				'message' => lang('messages_lang.labelle_message_update_success')
			];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/User_profil');
		}
		else
		{
			return $this->getOne($PROFIL);
		}
	}

	public function getDetail($PROFIL_ID='')
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PROFIL')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$IMPORTnd_proc = $this->getBindParms('*','user_profil JOIN user_profil_niveau_visualisation ON user_profil_niveau_visualisation.NIVEAU_VISUALISATION_ID=user_profil.NIVEAU_VISUALISATION_ID JOIN  user_profil_niveau ON  user_profil_niveau.PROFIL_NIVEAU_ID=user_profil.PROFIL_NIVEAU_ID','PROFIL_ID='.$PROFIL_ID,'PROFIL_ID ASC');
		$profil= $this->ModelPs->getRequeteOne($callpsreq, $IMPORTnd_proc);

		$table_detail = '';
		$description  = '';

		$table_detail ='<table class="table  table-bordered table-responsive table-striped"><thead><tr>
		<th>'.lang("messages_lang.label_descr_profil").'</th>
		<th>'.lang("messages_lang.label_niv_intervention").'</th>
		<th>'.lang("messages_lang.label_niv_visual").'</th>
		<th>'.lang("messages_lang.table_user").'</th>
		<th>'.lang("messages_lang.table_prof").'</th>
		</tr>
		<tr>
		<td  style="color:#ffce00">'.$profil['PROFIL_DESCR'].'</td>
		<td style="color:#ffce00">'.$profil['DESC_PROFIL_NIVEAU'].'</td>
		<td style="color:#ffce00">'.$profil['DESC_NIVEAU_VISUALISATION'].'</td>
		<td>'.$this->check_droit($profil['UTILISATEURS']).'</td>
		<td>'.$this->check_droit($profil['PROFIL']).'</td>
		</tr>

		<tr>
		<th>'.lang("messages_lang.menu_taux_TCD_engagement").'</th>
		<th>'.lang("messages_lang.menu_taux_execution_phase").'</th>
		<th>'.lang("messages_lang.menu_TCD_valeur_phase").'</th>
		<th>'.lang("messages_lang.menu_TCD_valeur_institution").'</th>
		<th>'.lang("messages_lang.menu_TCD_budget_vote_institution").'</th>
		</tr>
		<tr>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TAUX_TCD_ENGAGEMENT']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TAUX_EXECUTION_PHASE']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TCD_VALEUR_PHASE']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TCD_VALEUR_INSTITUTION']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST']).'</td>
		</tr>

		<tr>		
		<th>'.lang("messages_lang.menu_performance_execution").'</th>
		<th>'.lang("messages_lang.menu_budget").'</th>
		<th>'.lang("messages_lang.menu_execution_budgetaire").'</th>
		<th>'.lang("messages_lang.menu_vote_execution_budgetaire").'</th>
		<th>'.lang("messages_lang.menu_grande_masse").'</th>
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PERFORMANCE_EXECUTION']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_BUDGET']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_EXECUTION_BUDGETAIRE']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_GRANDE_MASSE']).'</td>
		</tr>

		<tr>		
		<th>'.lang("messages_lang.menu_allocation_budget_institution").'</th>
		<th>'.lang("messages_lang.menu_depassement_budget_vote").'</th>
		<th>'.lang("messages_lang.label_tb_trans").'</th>
		<th>TDB PIP</th>
		<th>'.lang("messages_lang.label_pip_min_inst").'</th>
		</tr>

		<tr>		
		<td>'.$this->check_droit($profil['TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_TRANSFERT']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_TDB_PIP']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_MINISTRE_INSTITUTION']).'</td>
		</tr>

		<tr>		
		<th>'.lang("messages_lang.label_pip_source_financement").'</th>
		<th>'.lang("messages_lang.label_pip_prog_budg").'</th>
		<th>'.lang("messages_lang.label_pip_statu_proj").'</th>
		<th>'.lang("messages_lang.label_pip_pilier").'</th>
		<th>'.lang("messages_lang.label_pip_obj_strateg").'</th>
		</tr>

		<tr>		
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_FINANCEMENT']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_STATUT_PROJET']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_PILIER']).'</td>
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE']).'</td>
		</tr>
		<tr>		
		<th>'.lang("messages_lang.label_pip_axe_interv").'</th>
		<th>'.lang("messages_lang.label_dc_trans").'</th>
		<th>'.lang("messages_lang.label_dc_prest").'</th>
		<th>'.lang("messages_lang.label_para_proc").'</th>
		<th>'.lang("messages_lang.label_para_etape").'</th>
		</tr>

		<tr>		
		<td>'.$this->check_droit($profil['TABLEAU_BORD_PIP_AXE_INTERVENTION']).'</td>
		<td>'.$this->check_droit($profil['DOUBLE_COMMANDE_TRANSFERT']).'</td>
		<td>'.$this->check_droit($profil['DOUBLE_COMMANDE_PRESTATAIRE']).'</td>
		<td>'.$this->check_droit($profil['PARAMETRE_PROCESSUS']).'</td>
		<td>'.$this->check_droit($profil['PARAMETRE_ETAPE']).'</td>
		</tr>

		<tr>		
		
		<th>'.lang("messages_lang.label_para_actions").'</th>
		<th>'.lang("messages_lang.label_para_doc").'</th>
		<th>'.lang("messages_lang.label_para_info_supp").'</th>
		<th>'.lang("messages_lang.label_mask_enjeux").'</th>
		<th>'.lang("messages_lang.label_mask_inst").'</th>
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['PARAMETRE_ACTION']).'</td>
		<td>'.$this->check_droit($profil['PARAMETRE_DOCUMENTS']).'</td>
		<td>'.$this->check_droit($profil['PARAMETRE_INFO_SUPPLEMENTAIRE']).'</td>
		<td>'.$this->check_droit($profil['MASQUE_SAISI_ENJEUX']).'</td>	
		<td>'.$this->check_droit($profil['MASQUE_SAISI_INSTITUTION']).'</td>
		</tr>

		<tr>
		
		<th>'.lang("messages_lang.label_mask_ptba_prog").'</th>
		<th>'.lang("messages_lang.label_mask_ptba_act").'</th>
		<th>'.lang("messages_lang.label_mask_ptba_activ").'</th>
		<th>'.lang("messages_lang.label_mask_obs_fin").'</th>
		<th>'.lang("messages_lang.label_ptba_inst").'</th>
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['MASQUE_SAISI_PTBA_PROGRAMMES']).'</td>
		<td>'.$this->check_droit($profil['MASQUE_SAISI_PTBA_ACTIONS']).'</td>
		<td>'.$this->check_droit($profil['MASQUE_SAISI_PTBA_ACTIVITES']).'</td>
		<td>'.$this->check_droit($profil['MASQUE_SAISI_OBSERVATION_FINANCIERES']).'</td>
		<td>'.$this->check_droit($profil['PTBA_INSTITUTION']).'</td>
		</tr>

		<tr>		
		
		<th>'.lang("messages_lang.label_droit_ptba_prog").'</th>
		<th>'.lang("messages_lang.label_droit_ptba_act").'</th>
		<th>'.lang("messages_lang.label_droit_ptba_activite").'</th>
		<th>PTBA&nbsp;'.lang("messages_lang.label_droit_class_eco").'</th>
		<th>PTBA&nbsp;'.lang("messages_lang.label_droit_class_admin").'</th>
		</tr>
		<tr>
		
		
		<td>'.$this->check_droit($profil['PTBA_PROGRAMMES']).'</td>
		<td>'.$this->check_droit($profil['PTBA_ACTIONS']).'</td>
		<td>'.$this->check_droit($profil['PTBA_ACTIVITES']).'</td>
		<td>'.$this->check_droit($profil['PTBA_CLASSIFICATION_ECONOMIQUE']).'</td>
		<td>'.$this->check_droit($profil['PTBA_CLASSIFICATION_ADMINISTRATIVE']).'</td>
		</tr>

		<tr>		
		
		<th>PTBA&nbsp;'.lang("messages_lang.label_droit_class_fonc").'</th>
		<th>'.lang("messages_lang.pip_execution").'</th>
		<th>PIP&nbsp;'.lang("messages_lang.label_droit_Compile").'</th>
		<th>PIP&nbsp;'.lang("messages_lang.label_droit_taux").'</th>
		<th>PIP&nbsp;'.lang("messages_lang.pourcentage_nomenclature").'</th>
		</tr>
		<tr>
		
		
		<td>'.$this->check_droit($profil['PTBA_CLASSIFICATION_FONCTIONNELLE']).'</td>
		<td>'.$this->check_droit($profil['PIP_EXECUTION']).'</td>
		<td>'.$this->check_droit($profil['PIP_COMPILE']).'</td>
		<td>'.$this->check_droit($profil['PIP_TAUX_ECHANGE']).'</td>
		<td>'.$this->check_droit($profil['PIP_POURCENTAGE_NOMENCLATURE']).'</td>
		</tr>

		<tr>
		
		
		<th>PIP&nbsp;'.lang("messages_lang.label_droit_source_fin").'</th>
		<th>'.lang("messages_lang.rapp_suiv_eval").'</th>
		<th>'.lang("messages_lang.label_rapp_class_eco").'</th>
		<th>'.lang("messages_lang.label_rapp_class_adm").'</th>
		<th>'.lang("messages_lang.label_rapp_class_fonc").'</th>
		</tr>
		<tr>
		
		
		<td>'.$this->check_droit($profil['PIP_SOURCE_FINANCEMENT']).'</td>
		<td>'.$this->check_droit($profil['RAPPORTS_SUIVI_EVALUATION']).'</td>
		<td>'.$this->check_droit($profil['RAPPORTS_CLASSIFICATION_ECONOMIQUE']).'</td>
		<td>'.$this->check_droit($profil['RAPPORTS_CLASSIFICATION_ADMINISTRATIVE']).'</td>
		<td>'.$this->check_droit($profil['RAPPORTS_CLASSIFICATION_FONCTIONNEL']).'</td>
		</tr>

		<tr>		
		
		<th>'.lang("messages_lang.labelle_eng_budget").'</th>
		<th>'.lang("messages_lang.conf_cd_eng").'</th>
		<th>'.lang("messages_lang.engagement_juridique").'</th>
		<th>'.lang("messages_lang.conf_jur").'</th>
		<th>'.lang("messages_lang.labelle_liquidation").'</th>
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['IS_ENGAGEMENT_BUDGETAIRE']).'</td>
		<td>'.$this->check_droit($profil['IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED']).'</td>
		<td>'.$this->check_droit($profil['IS_ENGAGEMENT_JURIDIQUE']).'</td>
		<td>'.$this->check_droit($profil['IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE']).'</td>
		<td>'.$this->check_droit($profil['IS_ENGAGEMENT_LIQUIDATION']).'</td>
		</tr>

		<tr>
		
		<th>'.lang("messages_lang.conf_liq").'</th>
		<th>'.lang("messages_lang.labelle_ordonan").'</th>
		<th>'.lang("messages_lang.ordo_deja_valid").'</th>
		<th>'.lang("messages_lang.ordo_trans_bord").'</th>
		<th>'.lang("messages_lang.trans_sp").'</th>		
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION']).'</td>
		<td>'.$this->check_droit($profil['IS_ORDONNANCEMENT']).'</td>
		<td>'.$this->check_droit($profil['IS_ORDONNANCEMENT_DEJA_VALIDE']).'</td>
		<td>'.$this->check_droit($profil['IS_ORDONNANCEMENT_TRANSMISSION_BORDEREAU']).'</td>
		<td>'.$this->check_droit($profil['IS_TRANSMISSION_SERVICE_PRISE_COMPTE']).'</td>
		
		</tr>

		<tr>
		
		<th>'.lang("messages_lang.rec_spe").'</th>
		<th>'.lang("messages_lang.labelle_paiement").'</th>
		<th>'.lang("messages_lang.trans_dc").'</th>
		<th>'.lang("messages_lang.rec_dc").'</th>
		<th>'.lang("messages_lang.trans_brb").'</th>
		<tr>
		
		<td>'.$this->check_droit($profil['IS_RECEPTION_SERVICE_PRISE_COMPTE']).'</td>
		<td>'.$this->check_droit($profil['IS_PAIEMENT']).'</td>
		<td>'.$this->check_droit($profil['IS_TRANSMISSION_DIRECTEUR_COMPTABLE']).'</td>
		<td>'.$this->check_droit($profil['IS_RECEPTION_DIRECTEUR_COMPTABLE']).'</td>
		<td>'.$this->check_droit($profil['IS_TRANSMISSION_BRB']).'</td>
		</tr>

		<tr>
		
		<th>'.lang("messages_lang.rec_brb").'</th>
		<th>'.lang("messages_lang.valid_td").'</th>
		<th>'.lang("messages_lang.decaissement_decaissement").'</th>
		<th>'.lang("messages_lang.label_plan_strat").'</th>
		<th>'.lang("messages_lang.suivi_execution").'</th>					
		<tr>
		
		<td>'.$this->check_droit($profil['IS_RECEPTION_BRB']).'</td>
		<td>'.$this->check_droit($profil['DOUBLE_COMMANDE_VALIDE_TD']).'</td>
		<td>'.$this->check_droit($profil['IS_DECAISSEMENT']).'</td>
		<td>'.$this->check_droit($profil['DEMANDE_PLANIFICATION_STRATEGIQUE']).'</td>
		<td>'.$this->check_droit($profil['SUIVI_EXECUTION']).'</td>		
		</tr>
		 <tr style="background-color:white">
          <th colspan="5" class="text-center">DOUBLE COMMANDE SALAIRE</th>
         </tr>

        <tr>
		
		<th>'.lang("messages_lang.labelle_liquidation").'</th>
		<th>'.lang("messages_lang.labelle_liquidation_confirm").'</th>
		<th>'.lang("messages_lang.labelle_ordonan").'</th>
		<th>'.lang("messages_lang.labelle_prise_en_charge").'</th>
		<th>'.lang("messages_lang.labelle_TD_cas_net").'</th>					
		<tr>
		
		<td>'.$this->check_droit($profil['LIQUIDATION_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['CONFIRM_LIQUIDATION_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['ORDONANCEMENT_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['PRISE_CHARGE_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['ETABLISSEMENT_TD_NET']).'</td>		
		</tr>
		 <tr>
		<th>'.lang("messages_lang.labelle_TD_cas_retenus").'</th>
		<th>'.lang("messages_lang.labelle_sign_dir_comptabilite").'</th>
		<th>'.lang("messages_lang.labelle_signature_DGFP").'</th>
		<th>'.lang("messages_lang.labelle_signature_min").'</th>
		<th>'.lang("messages_lang.validation_salaire_net").'</th>					
		</tr>
		<tr>
		
		<td>'.$this->check_droit($profil['ETABLISSEMENT_TD_RETENUS']).'</td>
		<td>'.$this->check_droit($profil['SIGNATURE_DIR_COMPT_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['SIGNATURE_DGFP_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['SIGNATURE_MIN_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['VALIDATION_SALAIRE_NET']).'</td>		
		</tr>
		<tr>
		<th>'.lang("messages_lang.validation_autre_retenus").'</th>
		<th>'.lang("messages_lang.labelle_decaisse").'</th>
		<th>Correction Liquidation</th>
		<th></th>
		<th></th>					
		</tr>

		<tr>
		<td>'.$this->check_droit($profil['VALIDATION_RETENUS_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['DECAISSEMENT_SALAIRE']).'</td>
		<td>'.$this->check_droit($profil['CORRECTION_LIQUIDATION_SALAIRE']).'</td>
		
		<td></td>
		<td></td>		
		</tr>
		 <tr style="background-color:white">
          <th colspan="5" class="text-center"></th>
         </tr>


		<tr>		
		
		<th>'.lang("messages_lang.suivi_ptba").'</th>
		<th>'.lang("messages_lang.label_trans_obr").'</th>
		<th>'.lang("messages_lang.label_droit_taux").'</th>
		<th>'.lang("messages_lang.label_plan_cdmt").'</th>
		</tr>

		<tr>		
		
		<td>'.$this->check_droit($profil['SUIVI_PTBA']).'</td>
		<td>'.$this->check_droit($profil['TRANSMISSION_OBR']).'</td>
		<td>'.$this->check_droit($profil['TAUX_DOUBLE_COMMANDE']).'</td>
		<td>'.$this->check_droit($profil['DEMANDE_PLANIFICATION_CDMT_CBMT']).'</td>	
		<tr>
		
		<th>'.lang("messages_lang.label_prog_budg").'</th>
		<th>'.lang("messages_lang.label_etat_av_dem").'</th>
		<th>'.lang("messages_lang.label_cart_inst").'</th>
		<th>'.lang("messages_lang.label_etat_av").'</th>
		</tr>
		<tr>
			
		<td>'.$this->check_droit($profil['DEMANDE_PROGRAMMATION_BUDGETAIRE']).'</td>
		<td>'.$this->check_droit($profil['DEMANDE_ETAT_AVANCEMENT']).'</td>
		<td>'.$this->check_droit($profil['GEOLOCALISATION_CARTE_INSTITUTION']).'</td>
		<td>'.$this->check_droit($profil['DOUBLE_COMMANDE_ETAT_AVANCEMENT']).'</td>
		<td colspan="4">
		<center> <a href="'.base_url("Administration/User_profil/getOne/".md5($profil['PROFIL_ID'])).'" class="btn btn-danger">
		<span class="glyphicon glyphicon-edit"></span>Modifier</a></center>
		</td>
		</tr>
		</table>';

		$description .= $profil['PROFIL_DESCR'];

		echo json_encode(array('status'=>TRUE,'table_detail'=>$table_detail,'description'=>$description));
	}
	
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

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
}
?>