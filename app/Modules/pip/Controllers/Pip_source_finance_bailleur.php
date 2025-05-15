<?php

/**NIYONGERE James
 *Titre:CRUD DE GESTION  Pip source Finance Bailleur
 *Numero de telephone: (+257) 61171608
 *WhatsApp: (+257) 61171608
 *Email: James.niyongere@mediabox.bi
 *Date: 18 janvier,2024
 **/

namespace  App\Modules\pip\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Pip_source_finance_bailleur extends BaseController
{
	protected $session;

	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->library = new CodePlayHelper();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function index($value = '')
	{
		$session  = \Config\Services::session();
		
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
		return view('App\Modules\pip\Views\Pip_source_finance_bailleur_Liste_View', $data);
	}

	//Fonction pour afficher le formulaire d'insertion
	public function Add_pipFinance($value = '')
	{
		$session  = \Config\Services::session();
		
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data = $this->urichk();
		return view('App\Modules\pip\Views\Pip_source_finance_bailleur_View', $data);
	}

	//fonction pour inserer dans les colonnes souhaites
	public function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $id = $result['id'];
	}

	// function of insert data
	public function Ajout_donnees()
	{
		$session  = \Config\Services::session();
		
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$validation =  \Config\Services::validation();
		$err = lang('messages_lang.message_champs_obligatoire');
		$rules = [
			'CODE' => [
				'rules' => 'required|trim|is_unique[pip_source_financement_bailleur.CODE_BAILLEUR]',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">' . $err . '</font>'
				]
			],
			'Names' => [
				'rules' => 'required|trim|is_unique[pip_source_financement_bailleur.NOM_SOURCE_FINANCE]',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">' . $err . '</font>'
				]
			],

		];
		$this->validation->setRules($rules);
		if ($validation->withRequest($this->request)->run()) 
		{
			$codeBailleur = $this->request->getPost('CODE');
			$NomFinance = $this->request->getPost('Names');
			$insertIntoTable = 'pip_source_financement_bailleur';
			$columsinsert = 'NOM_SOURCE_FINANCE,CODE_BAILLEUR';
			$datatoinsert_Pip = "'" . str_replace("'", "\'", $NomFinance) . "','" . str_replace("'", "\'", $codeBailleur) . "'";
			$datamessage = $this->save_all_table($insertIntoTable, $columsinsert, $datatoinsert_Pip);

			if ($datamessage) 
			{
				$data = ['message' => lang('messages_lang.message_success')];
				session()->setFlashdata('alert', $data);
				return redirect('pip/Source_finance_bailleur');
			}
		}
		else
		{
			return $this->Add_pipFinance();
		}
	}

	//fonction pour affichage d'une liste
	public function Liste_donnees($value = 0)
	{
		$session  = \Config\Services::session();
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$requetedebase = "SELECT DISTINCT CODE_BAILLEUR,NOM_SOURCE_FINANCE,ID_SOURCE_FINANCE_BAILLEUR FROM pip_source_financement_bailleur where 1";
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$group = "";
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1) 
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = "";
		$order_column = array('CODE_BAILLEUR', 'NOM_SOURCE_FINANCE');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ID_SOURCE_FINANCE_BAILLEUR ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (CODE_BAILLEUR LIKE "%' . $var_search . '%" OR NOM_SOURCE_FINANCE LIKE "%' . $var_search . '%")') : "";
		$search = str_replace("'", "\'", $search);
		$critaire = " ";
		$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_All = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$i = 1;
		$suppr = lang('messages_lang.supprimer_action');
		$modif = lang('messages_lang.bouton_modifier');
		foreach ($fetch_All as $row) 
		{
			$sub_array = array();
			$sub_array[] = $i++;
			$sub_array[] = $row->CODE_BAILLEUR;
			$sub_array[] = $row->NOM_SOURCE_FINANCE;

			$action = '<div class="dropdown">
		<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
		<i class="fa fa-cog"></i>   Action
		</button>
		<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
		  
		  <li><a class="dropdown-item" data-toggle="modal" onclick="deleteData(' . $row->ID_SOURCE_FINANCE_BAILLEUR . ')"> <label class="text-danger"> '.$suppr.' </label></a></li>

		  <li><a class="dropdown-item" href="' . base_url('pip/get_data/' . $row->ID_SOURCE_FINANCE_BAILLEUR) . '">'.$modif.'</a></li>
		</ul>
	  </div>';

			$sub_array[] = $action;
			$data[] = $sub_array;
		}
		// fin du boucle
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

	// recuperation des donnees pour la modification
	public function Recuperation_donnee($Rowid)
	{
		$session  = \Config\Services::session();
		
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();
		// recuperation des donnees pour les combobox
		$appelprocedure = "CALL `getRequete`(?,?,?,?);";
		$FinanceBailleur = $this->getBindParms('CODE_BAILLEUR,NOM_SOURCE_FINANCE,ID_SOURCE_FINANCE_BAILLEUR', 'pip_source_financement_bailleur', 'ID_SOURCE_FINANCE_BAILLEUR=' . $Rowid, 'ID_SOURCE_FINANCE_BAILLEUR ASC');
		$data['bailleur'] = $this->ModelPs->getRequeteOne($appelprocedure, $FinanceBailleur);

		return view('App\Modules\pip\Views\Pip_source_finance_bailleur_update_View', $data);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	//fonction pour la suppression
	public function delete()
	{
		$session  = \Config\Services::session();
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		} 
		else 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$db = db_connect();
		$RowId = $this->request->getPost("id");
		$condition = "ID_SOURCE_FINANCE_BAILLEUR=" . $RowId;
		$table = 'pip_source_financement_bailleur';
		$deleteparams = [$db->escapeString($table), $db->escapeString($condition)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$delete = $this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
		$data = ['message' => "Suppression effectuée avec succès"];
		session()->setFlashdata('alert', $data);
		return json_encode($data);
	}

	/* update table */
	function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	//Mise à jour des Piliers
	public function update_data()
	{
		$session  = \Config\Services::session();
		
		if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		
		if($this->request->getMethod() == "post")
		{
			$validation =  \Config\Services::validation();
			$champ_vide = lang('messages_lang.message_champs_obligatoire');
			$rules = [
				'CODE' => [
					'rules' => 'required|trim|is_unique[pip_source_financement_bailleur.CODE_BAILLEUR]',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
					]
				],
				'Names' => [
					'rules' => 'required|trim|is_unique[pip_source_financement_bailleur.NOM_SOURCE_FINANCE]',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.$champ_vide.'</font>'
					]
				],

			];
			$this->validation->setRules($rules);
			if ($validation->withRequest($this->request)->run()) 
			{
				$Id_Row = $this->request->getPost('RowId');
				$codeBailleur = $this->request->getPost('CODE');
				$NomFinance = $this->request->getPost('Names');
				$UpdateTable = 'pip_source_financement_bailleur';
				$columsupdate = 'CODE_BAILLEUR="' . str_replace("'", "\'", $codeBailleur) . '",NOM_SOURCE_FINANCE="' . str_replace("'", "\'", $NomFinance) . '"';
		
				$conditions = 'ID_SOURCE_FINANCE_BAILLEUR=' . $Id_Row;
				$this->update_all_table($UpdateTable, $columsupdate, $conditions);

				$data = ['message' => lang('messages_lang.labelle_et_succes_ok')];
				session()->setFlashdata('alert', $data);
				return redirect('pip/Source_finance_bailleur');
			} 
			else 
			{
				return $this->Add_pipFinance();
			}
		} 
		else 
		{
		}
	}
}
?>