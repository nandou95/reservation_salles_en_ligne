<?php
/**
 * auteur: HABIMANA Nandoi
 * tache: formulaire de transmission du bordereau de transmission du cabinet vers service prise en charge
 * date: 20/12/2023
 * email: christa@mediabox.bi
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Transmission_Bon_Cabinet_prise_charge extends BaseController
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

	/* Fin Gestion insertion */
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}

  public function save_histo_racrochage($columsinsert, $datacolumsinsert)
	{
		$table = 'execution_budgetaire_tache_detail_histo';
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
	}

	/* Debut Gestion insertion */
	public function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $id = $result['id'];
	}

	public function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	// pour uploader les documents
	public function uploadFile($fieldName = NULL, $folder = NULL, $prefix = NULL): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';
		$file = $this->request->getFile($fieldName);
		if ($file->isValid() && !$file->hasMoved())
		{
			$newName = "BORDEREAU_TRANSMISSION_" . uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
			return $newName;
		}
	}

	/* renvoie la vue qui va afficher la liste */
  public function index ()
  {
    $data = $this->urichk();
    if(empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
    	return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
    {
    	return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuOrdonnancement();
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];

    $data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_spe']=$data_titre['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_titre['get_bord_deja_spe'];
    return view('App\Modules\double_commande_new\Views\Transmission_Bon_Cabinet_prise_charge_Liste_View', $data);
  }

  /* afficher les donnees de la liste */
  public function listing()
  {
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
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
    $order_column = ['exec.NUMERO_BON_ENGAGEMENT','det.MONTANT_ORDONNANCEMENT','inst.DESCRIPTION_INSTITUTION','typ.DESC_DEVISE_TYPE ', 1];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR det.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR typ.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    $conditionsfilter = $critaire . " " . $search . " " . $group;
    $requetedebase="SELECT dev.DEVISE_TYPE_ID, dev.DESC_DEVISE_TYPE,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID=det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=34";
    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = [];
    $u = 1;
    
    foreach ($fetch_actions as $row) 
    {
      $dist = "";
      $sub_array = [];
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $row->DEVISE_TYPE_ID == 1 ? number_format($row->MONTANT_ORDONNANCEMENT, 4, ',', ' ') : number_format($row->MONTANT_ORDONNANCEMENT_DEVISE, 4, ',', ' ');
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }

  function transmission()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU')!=1)
    {
    	return redirect('Login_Ptba/homepage'); 
    }

		$etape_actuel_id=34;
		$data['id_etape']=$etape_actuel_id;
		$prof_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq="CALL getRequete(?,?,?,?);";
  	$user_profil=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel_id,'PROFIL_ID DESC');
  	$getProfil= $this->ModelPs->getRequete($callpsreq,$user_profil);

		if(!empty($getProfil))
  	{
  		foreach($getProfil as $value)
      {
      	if($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
        	//recuperation origine et destination
					$bind_origine=$this->getBindParms('`ID_ORIGINE_DESTINATION`,`ORIGINE`,`DESTINATION`', 'origine_destination', '`IS_ACTIVE`=1 AND `ID_ORIGINE_DESTINATION`=1', 'ID_ORIGINE_DESTINATION ASC');
					$data['origine']=$this->ModelPs->getRequeteOne($callpsreq,$bind_origine);

					$titre=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel_id,' ETAPE_DOUBLE_COMMANDE_ID DESC');
					$titre=$this->ModelPs->getRequeteOne($callpsreq, $titre);
					$data['etapes']=$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

					//recuperation bn d'engagement
					$bind_bon_engagement = $this->getBindParms('devise.DEVISE_TYPE_ID, devise.DESC_DEVISE_TYPE,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION', 'execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN devise_type devise ON exec.DEVISE_TYPE_ID = devise.DEVISE_TYPE_ID', 'det.ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel_id, 1);
					$data['bon_engagement'] = $this->ModelPs->getRequete($callpsreq,$bind_bon_engagement);
					return view('App\Modules\double_commande_new\Views\Transmission_Bon_Cabinet_prise_charge_Transmission_View', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
  	}
	 	else
    {
      return redirect('Login_Ptba/homepage');
    }	
	}

	function insert_in_execution_budgetaire_bordereau_transmission($column, $data) 
	{
		$insertReq="CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		return $this->save_all_table('execution_budgetaire_bordereau_transmission', $column, $data);
	}

	function insert_in_execution_budgetaire_bordereau_transmission_bon_titre ($column, $data) 
	{
		$insertReq="CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		return $this->save_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $column, $data);
	}

	//traitement et enregistrement dans la BD
	function save()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		// if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }
		
		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'NUM_BORDEREAU_TRANSMISSION' => [
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
			'PATH_BORDEREAU_TRANSMISSION' => [
				'label' => '',
				'rules' => 'uploaded[PATH_BORDEREAU_TRANSMISSION]',
				'errors' => [
					'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'EXECUTION_BUDGETAIRE_DETAIL_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			]
		];

		$this->validation->setRules($rules);
		if ($this->validation->withRequest($this->request)->run())
		{
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$id_etape = $this->request->getPost('id_etape');
			$etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
			$etape_request = str_replace('\"', '"', $etape_request);
			$next_etape_data = $this->ModelPs->getRequeteOne($callpsreq, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

			$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
			$NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
			$ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
			$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID[]');
			$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
			$PATH_BORDEREAU_TRANSMISSION = $this->request->getPost('PATH_BORDEREAU_TRANSMISSION');
			$BORDEREAU = $this->uploadFile('PATH_BORDEREAU_TRANSMISSION', 'double_commande_new', $PATH_BORDEREAU_TRANSMISSION);
			$data_bord='"'.$NUM_BORDEREAU_TRANSMISSION.'","'.$BORDEREAU.'",'.$ID_ORIGINE_DESTINATION.','.$user_id.',"'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'", 1';
			$columsinsert="NUMERO_BORDEREAU_TRANSMISSION,PATH_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID, DATE_RECEPTION_BD, DATE_TRANSMISSION_BD, STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID";

			$EXECUTION_BORD_TRANSMISSION_ID = $this->insert_in_execution_budgetaire_bordereau_transmission($columsinsert, $data_bord);
			if (!empty($EXECUTION_BUDGETAIRE_DETAIL_ID))
			{
				foreach ($EXECUTION_BUDGETAIRE_DETAIL_ID as $value)
				{
					$table='execution_budgetaire_tache_detail';
					$conditions='EXECUTION_BUDGETAIRE_DETAIL_ID='.$value;
					$datatomodifie='ETAPE_DOUBLE_COMMANDE_ID="'.$next_etape_data.'"';
			    $this->update_all_table($table,$datatomodifie,$conditions);

					$num=$this->getBindParms('exec.NUMERO_BON_ENGAGEMENT','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON det.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID='.$value,'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
			    $numero_titre= $this->ModelPs->getRequeteOne($callpsreq, $num);
			    $numero_titre_decaissement=$numero_titre['NUMERO_BON_ENGAGEMENT'];
					$bon_titre=$EXECUTION_BORD_TRANSMISSION_ID.',1,'.$value.',"'.$numero_titre_decaissement.'",'.$user_id.',1';
					$columsinsert_bon_titre = "BORDEREAU_TRANSMISSION_ID,TYPE_DOCUMENT_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,NUMERO_DOCUMENT,USER_ID,STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID";
					$this->insert_in_execution_budgetaire_bordereau_transmission_bon_titre($columsinsert_bon_titre, $bon_titre);

					//insertion dans l'historique
					$column_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
					$data_histo=$value.','.$id_etape.','.$user_id.',"'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
					$this->save_histo_racrochage($column_histo,$data_histo);
				}
			}
			return redirect('double_commande_new/Liste_Trans_Deja_Fait_PC');
		}
		else
		{
			return $this->transmission();
		}
	}
}
?>