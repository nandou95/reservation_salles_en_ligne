<?php
/*
* @ permet de gerer la liste des transfert
*/
namespace  App\Modules\transfert_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Transfert_list extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function index()
	{
		$session  = \Config\Services::session();

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		//nombre de transfert par type de transfert
		$transfert=$this->count_transfert_new();
		$data['nbre_tr_hist']=$transfert['nbre_tr_hist'];
		$data['nbre_incrim']=$transfert['nbre_incrim'];
		$data['nbre_imput']=$transfert['nbre_imput'];
		$data['nbre_activite']=$transfert['nbre_activite'];
		//fin nombre de transfert par type de transfert
		return view('App\Modules\transfert_new\Views\Transfert_liste_view',$data);   
	}

	/* fonction pour faire la liste */
	public function liste()
	{
		$session  = \Config\Services::session();

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$institution=' AND execution_budgetaire_new.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		
		$db = db_connect();
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array(1, 'MONTANT_TRANSFERT', 'MONTANT_RECEPTION','DESCRIPTION_OPERATION', 'activite_donatrice', 'activite_receptrice', 'DESCRIPTION_TRANCHE','institut_donnatrice','inst_recept','DESC_OBSERVATION_FINANCIER', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (MONTANT_TRANSFERT LIKE "%' . $var_search . '%" OR MONTANT_RECEPTION LIKE "%' . $var_search . '%" OR DESCRIPTION_OPERATION LIKE "%' . $var_search . '%" OR ptba.ACTIVITES LIKE "%' . $var_search . '%" OR inst_institutions.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR  ptbaa.ACTIVITES LIKE "%' . $var_search . '%" OR  DESCRIPTION_TRANCHE LIKE "%' . $var_search . '%" OR DESC_OBSERVATION_FINANCIER LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		$requetedebase='SELECT HISTORIQUE_TRANSFERT_ID,MONTANT_TRANSFERT,MONTANT_RECEPTION,type_operation.DESCRIPTION_OPERATION,ptba.ACTIVITES activite_donatrice,ptbaa.ACTIVITES as activite_receptrice,op_tranches.DESCRIPTION_TRANCHE,inst_institutions.DESCRIPTION_INSTITUTION as institut_donnatrice,inst_institutionss.DESCRIPTION_INSTITUTION as inst_recept,observation_transfert_financier.DESC_OBSERVATION_FINANCIER FROM historique_transfert join type_operation on historique_transfert.TYPE_OPERATION_ID=type_operation.TYPE_OPERATION_ID join ptba on historique_transfert.PTBA_ID_TRANSFERT=ptba.PTBA_ID join ptba as ptbaa on historique_transfert.PTBA_ID_RECEPTION=ptbaa.PTBA_ID join op_tranches on historique_transfert.TRIMESTRE_ID=op_tranches.TRANCHE_ID join inst_institutions on historique_transfert.INSTITUTION_ID_TRANSFERT=inst_institutions.INSTITUTION_ID join inst_institutions as inst_institutionss on historique_transfert.INSTITUTION_ID_RECEPTION=inst_institutionss.INSTITUTION_ID join observation_transfert_financier on historique_transfert.OBSERVATION_FINANCIER_ID=observation_transfert_financier.OBSERVATION_FINANCIER_ID join execution_budgetaire_new on historique_transfert.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_new.EXECUTION_BUDGETAIRE_ID WHERE 1'.$institution;

		$var_search=!empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data=array();
		$u=1;
		$profil=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$psgetrequete="CALL `getRequete`(?,?,?,?);";
		foreach($fetch_data as $row)
		{
			$sub_array=array();
			$bindparams=$this->getBindParms('COUNT(*) as nombre','transfert_preuve','TRANSFERT_HISTORIQUE_ID='.$row->HISTORIQUE_TRANSFERT_ID, 'TRANSFERT_HISTORIQUE_ID desc');
			$nombredoc=$this->ModelPs->getRequeteOne($psgetrequete, $bindparams);

			$MONTANT_TRANSFERT=number_format($row->MONTANT_TRANSFERT,'2',',',' ');
			$MONTANT_RECEPTION=number_format($row->MONTANT_RECEPTION,'2',',',' ');

			$sub_array[]=$row->activite_donatrice ;
			$sub_array[]=!empty($MONTANT_TRANSFERT) ? $MONTANT_TRANSFERT : 0 ;
			$sub_array[]=$row->activite_receptrice ;
			$sub_array[]=!empty($MONTANT_RECEPTION) ? $MONTANT_RECEPTION : 0 ;
			$sub_array[]=$row->institut_donnatrice ;
			$sub_array[]=$row->inst_recept ;
			$sub_array[]=$row->DESCRIPTION_TRANCHE ;
			$sub_array[]=$row->DESCRIPTION_OPERATION ;
			$sub_array[]=$row->DESC_OBSERVATION_FINANCIER ;
			$sub_array[]='<button class="btn btn-primary" onclick="modal_odd('.$row->HISTORIQUE_TRANSFERT_ID.')">'.$nombredoc['nombre'].'</button>';

			$sub_array[] = '<div class="dropdown">
			<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
			Action
			</button>
			<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
			<li><a class="dropdown-item" href="'.base_url('transfert_new/Transfert_list/add_preuve/'.$row->HISTORIQUE_TRANSFERT_ID).'">Preuve</a></li>
			</ul>
			</div>';
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

	public function liste_preuve($id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('DOC_PREUVE');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (DOC_PREUVE LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		$requetedebase='SELECT TRANSFERT_HISTORIQUE_ID,DOC_PREUVE FROM transfert_preuve where TRANSFERT_HISTORIQUE_ID='.$id;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data=array();
		$u=0;
		foreach($fetch_data as $row)
		{
			$u++;
			$sub_array[] ="<a href='".base_url('upload/preuve/'.$row->DOC_PREUVE)."' target='_blank'><span class='fa fa-file-pdf' style='color:red;font-size: 30px;'></a>";  
			
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

	public function add_preuve($id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data=$this->urichk();
		$data['id']=$id;
		return view('App\Modules\transfert_new\Views\Transfert_preuve_view',$data); 
	}

	public function save_preuve()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$id= $this->request->getPost('id');
		foreach ($_FILES['file']['name'] as $key => $value)
		{
			$file=$this->uploadFile($_FILES['file']['tmp_name'][$key],$_FILES['file']['name'][$key]);
			$columsinsert="TRANSFERT_HISTORIQUE_ID,DOC_PREUVE";
			$datacolumsinsert=$id.",'".$file."'";
			$this->save_preuves($columsinsert,$datacolumsinsert);
		}
		return redirect('transfert_new/Transfert_list');
	}

	public function save_preuves($columsinsert,$datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
		$table='transfert_preuve';
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);
	}

	/* fonction pour uploader les preuves justifivcatifs */
	public function uploadFile($nom_file,$nom_champ): string
	{
		$repertoire_fichier = FCPATH . 'upload/preuve/';
		$code=uniqid();
		$config['allowed_types'] = 'pdf';
		$ext = 'pdf';
		$name=basename($code .$nom_champ );
		$file_link = $repertoire_fichier . $name;

		if(!is_dir($repertoire_fichier))
		{
			mkdir($repertoire_fichier, 0777, TRUE);
		}
		move_uploaded_file($nom_file, $file_link);
		return $name;
	}
}
?>