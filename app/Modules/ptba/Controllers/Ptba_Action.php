<?php
/*
* RUGAMBA Jean Vainqueur
* Titre:liste des actions dans le module PTBA 
* Numero de telephone: (+257) 66 33 43 25
* WhatsApp: (+257) 62 47 19 15
* Email: jean.vainqueur@mediabox.bi
* Date: 30 Septembre,2023
*/

namespace  App\Modules\ptba\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Ptba_Action extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    		
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');

		if(!empty($INSTITUTION_ID))
		{
			$program = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes', '1', 'PROGRAMME_ID DESC');
			$data['program']= $this->ModelPs->getRequete($psgetrequete, $program);	
		}
		else
		{
			$program = [];
		}

		$data['program'] = $program;
		$data['INSTITUTION_ID'] = $INSTITUTION_ID;
		$data['PROGRAMME_ID'] = $PROGRAMME_ID;
		return view('App\Modules\ptba\Views\Ptba_Action_View',$data);   
	}

	public function indexdeux($value='')
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);
		$program = array('PROGRAMME_ID'=>NULL,'INTITULE_PROGRAMME'=>NULL,'INSTITUTION_ID'=>NULL); // Initialisez $program à null en dehors de la condition
		$PROGRAMME_ID = $this->request->getPost('');
		$INSTITUTION_ID = 0;
		$PROGRAMME_ID=0;
		if(!empty($this->request->getPost('INSTITUTION_ID')))
		{
			$INSTITUTION_ID= $this->request->getPost('INSTITUTION_ID');
		}

		if(!empty($this->request->getPost('PROGRAMME_ID')))
		{
			$PROGRAMME_ID= $this->request->getPost('PROGRAMME_ID');
		}

		$bindprog = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME,INSTITUTION_ID', 'inst_institutions_programmes',  'INSTITUTION_ID='.$INSTITUTION_ID, 'PROGRAMME_ID DESC');
		$program = $this->ModelPs->getRequete($psgetrequete, $bindprog);
		$data['program'] = $program;
		$data['INSTITUTION_ID'] = $INSTITUTION_ID;
		$data['PROGRAMME_ID'] = $PROGRAMME_ID;
		return view('App\Modules\ptba\Views\Ptba_Action_View', $data);
  }

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');

		$sqlmode = "SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));";
		$mont_sql = 'CALL `getTable`("'.$sqlmode.'");';
		$this->ModelPs->getRequete($mont_sql);

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere1 = "";
		$critere2 = "";
		$limit = "LIMIT 0,1000";
		if($_POST['length'] != -1)
		{
			$limit = "LIMIT " . $_POST['start'] . "," . $_POST['length'];
		}

		$order_by = "";
		$order_column = array(1,'act.CODE_ACTION','act.LIBELLE_ACTION','T1','T2','T3','T4',1, 1);
		$order_by = isset($_POST['order']) ? " ORDER BY " . $order_column[$_POST['order']['0']['column']] . "  " . $_POST['order']['0']['dir'] : " ORDER BY act.CODE_ACTION ASC";
		$search = !empty($_POST['search']['value']) ? (" AND (act.CODE_ACTION LIKE '%".$var_search."%' OR act.LIBELLE_ACTION LIKE '%".$var_search ."%')") : "";

		if(!empty($INSTITUTION_ID))
		{
			if($INSTITUTION_ID>0)
			{
				$psgetrequete = "CALL `getRequete`(?,?,?,?);";
				$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`','inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`CODE_INSTITUTION` ASC');
	    	$inst = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
				$critere1 = " AND ptba.INSTITUTION_ID LIKE '".$inst['INSTITUTION_ID']."'";

				$requetedebase= "SELECT act.ACTION_ID, act.CODE_ACTION, act.LIBELLE_ACTION, SUM(ptba.T1) AS T1, SUM(ptba.T2) AS T2, SUM(ptba.T3) AS T3, SUM(ptba.T4) AS T4 FROM inst_institutions_actions act  JOIN ptba ON act.ACTION_ID = ptba.ACTION_ID WHERE 1".$critere1."";

				$group = "GROUP BY act.CODE_ACTION,act.ACTION_ID,act.LIBELLE_ACTION";
				// Condition pour la requête principale
				$conditions = $search . " " . $group . " " . $order_by . " " . $limit;
			  // Condition pour la requête de filtre
				$conditionsfilter = $search . " " . $group;

				//Filtre des programmes
				if(!empty($PROGRAMME_ID))
				{
					if($PROGRAMME_ID>0)
					{
						$psgetrequete = "CALL `getRequete`(?,?,?,?);";
						$bindprog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME','inst_institutions_programmes', 'PROGRAMME_ID='.$PROGRAMME_ID, '`CODE_PROGRAMME` ASC');
			    	$prog = $this->ModelPs->getRequeteOne($psgetrequete, $bindprog);
						$critere2 = " AND ptba.PROGRAMME_ID LIKE '".$prog['PROGRAMME_ID']."'";

						$requetedebase= "SELECT act.ACTION_ID, act.CODE_ACTION, act.LIBELLE_ACTION, SUM(ptba.T1) AS T1, SUM(ptba.T2) AS T2, SUM(ptba.T3) AS T3, SUM(ptba.T4) AS T4 FROM inst_institutions_actions act  JOIN ptba ON act.ACTION_ID = ptba.ACTION_ID WHERE 1".$critere1." ".$critere2." " ;

						$group = "GROUP BY act.CODE_ACTION,act.ACTION_ID,act.LIBELLE_ACTION";
						// Condition pour la requête principale
						$conditions = $search . " " . $group . " " . $order_by . " " . $limit;
					  // Condition pour la requête de filtre
						$conditionsfilter = $search . " " . $group;
					}
				}
			}
		}
		else
		{
			$requetedebase= "SELECT act.ACTION_ID, act.CODE_ACTION, act.LIBELLE_ACTION, SUM(ptba.T1) AS T1, SUM(ptba.T2) AS T2, SUM(ptba.T3) AS T3, SUM(ptba.T4) AS T4 FROM inst_institutions_actions act  JOIN ptba ON act.ACTION_ID = ptba.ACTION_ID WHERE 1".$search." GROUP BY act.CODE_ACTION,act.ACTION_ID,act.LIBELLE_ACTION";
			// Condition pour la requête principale
			$conditions = $order_by . " " . $limit;
		  // Condition pour la requête de filtre
			$conditionsfilter = "";
		}

		$var_search=!empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$requetedebases = $requetedebase . " " . $conditions;
		$requetedebasefilter = $requetedebase . " " . $conditionsfilter;
		$query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';

		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u = 1;
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$sub_array[] = $u++;
			$sub_array[] = $row->CODE_ACTION;
			$sub_array[] = $row->LIBELLE_ACTION;
			$sub_array[] = number_format($row->T1,0,","," ");
			$sub_array[] = number_format($row->T2,0,","," ");
			$sub_array[] = number_format($row->T3,0,","," ");
			$sub_array[] = number_format($row->T4,0,","," ");
			$sub_array[] = number_format($row->T1+$row->T2+$row->T3+$row->T4,0,","," ");
			
			//Declaration des labels pour l'internalisation
			$bouton_detail = lang("messages_lang.bouton_detail");

			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			$action .="<li>
			<a href='".base_url("ptba/Detail_Action/".md5($row->ACTION_ID))."'><label>&nbsp;&nbsp;$bouton_detail</label></a>
			</li></ul>";
			
			$sub_array[]=$action;
			$data[] = $sub_array;
		}

		$query_total = "SELECT act.ACTION_ID, act.CODE_ACTION, act.LIBELLE_ACTION, SUM(ptba.T1) AS T1, SUM(ptba.T2) AS T2, SUM(ptba.T3) AS T3, SUM(ptba.T4) AS T4 FROM inst_institutions_actions act  JOIN ptba ON act.ACTION_ID = ptba.ACTION_ID WHERE 1 GROUP BY act.CODE_ACTION,act.ACTION_ID,act.LIBELLE_ACTION";
		$recordsTotal = $this->ModelPs->datatable('CALL `getTable`("'.$query_total.'")');
		$recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("'.$requetedebasefilter.'")');
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);//echo json_encode($output);
	}

	public function get_institution()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$bind_program = $this->getBindParms('COMMANDE_ID , INTITULE_PROGRAMME , INSTITUTION_ID', 'inst_institutions_programmes', 'INSTITUTION_ID='.$INSTITUTION_ID.'', 'ZONE_NAME DESC');
		$program= $this->ModelPs->getRequete($callpsreq, $bind_program);
		//Declaration des labels pour l'internalisation
		$input_select = lang("messages_lang.labelle_selecte");
		$html = '<option value="">'.$input_select.'</option>';

		if(!empty($program))
		{
			foreach($program as $key)
			{
				if($key->COMMANDE_ID == set_value('COMMANDE_ID'))
				{
					$html .= "<option value='" . $key->COMMANDE_ID . "' selected>" . $key->ZONE_NAME. "</option>";
				}
				else
				{
					$html .= "<option value='" . $key->COMMANDE_ID . "'>" . $key->ZONE_NAME. "</option>";
				}
			}
		}

		$output = array('status' => TRUE, 'cart'=>$html);
		return $this->response->setJSON($output);
	}
}
?>