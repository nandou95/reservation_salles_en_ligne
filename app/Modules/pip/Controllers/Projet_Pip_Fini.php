<?php
/*
* @author MUNEZERO Sonia
* +25765165772
* sonia@mediabox.bi
* 02/01/2024
* Liste des projet PIP complet
*/

namespace App\Modules\pip\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');
class Projet_Pip_Fini extends BaseController
{
	protected $library;
	protected $ModelPs;
	protected $session;
	protected $validation;

	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}

	function liste_pip_fini()
	{
		$session  = \Config\Services::session();
		
		$data = $this->urichk();
		$psgetrequete = "CALL getRequete(?,?,?,?);";

		$USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");

		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$institution=' AND inst_institutions.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$USER_ID.')';

		$step = $this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION,INSTITUTION_ID','inst_institutions','1'.$institution,'INSTITUTION_ID ASC');
		$data['institution']= $this->ModelPs->getRequete($psgetrequete, $step);

		$pipMenu=$this->menu_pip();
		$data['nbre_incomplet']=$pipMenu['nbre_incomplet'];
		$data['nbre_Complet']=$pipMenu['nbre_Complet'];
		$data['nbre_corriger'] = $pipMenu['nbre_corriger'];
		$data['nbre_valide'] = $pipMenu['nbre_valide'];
		return view('App\Modules\pip\Views\Projet_Pip_Fini_List_View',$data);
	}

	function liste_projet_fini()
	{
		$session  = \Config\Services::session();
		
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$PROFIL_ID = session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
		$NIVEAU_VISUALISATION_ID=session()->get("SESSION_SUIVIE_PTBA_NIVEAU_VISUALISATION_ID");

		if(empty($USER_ID))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$institution=' AND info_sup.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$USER_ID.')';

		if($NIVEAU_VISUALISATION_ID==1)
		{
			$institution='';
		}

		$critere1="";
		if(!empty($INSTITUTION_ID))
		{
			$critere1=" AND inst_institutions.INSTITUTION_ID=".$INSTITUTION_ID;
		}

		$query_principal = "SELECT info_sup.ID_DEMANDE_INFO_SUPP,statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,proc_etape.DESCR_ETAPE FROM pip_demande_infos_supp info_sup left join inst_institutions on  info_sup.INSTITUTION_ID=inst_institutions.INSTITUTION_ID left join proc_demandes demande on demande.ID_DEMANDE=info_sup.ID_DEMANDE left join proc_etape on  demande.ETAPE_ID=proc_etape.ETAPE_ID left join proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET=statut.ID_STATUT_PROJET WHERE info_sup.IS_ANNULER=0 AND  proc_process.PROCESS_ID=1 and  IS_FINISHED = 1 AND IS_COMPILE=0 ".$institution;

		$var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit='LIMIT 0,10';

		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}

		$order_by='';
		$order_column='';
		$order_column= array(1,'info_sup.NUMERO_PROJET','info_sup.NOM_PROJET','proc_etape.DESCR_ETAPE','statut.DESCR_STATUT_PROJET','demande.DATE_INSERTION',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY demande.ID_DEMANDE ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (info_sup.NUMERO_PROJET LIKE '%$var_search%' OR info_sup.NOM_PROJET LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR statut.DESCR_STATUT_PROJET LIKE '%$var_search%' OR demande.DATE_INSERTION LIKE '%$var_search%')"):'';

		$critaire = $critere1;
		$query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.'   '.$limit;

		$query_filter = $query_principal.' '.$search.' '.$critaire;
		$requete='CALL `getList`("'.$query_secondaire.'")';
		$fetch_cov_frais = $this->ModelPs->datatable( $requete);
		$data = array();
		$u=1;

		foreach($fetch_cov_frais as $info)
		{
			$post=array();

			$NOM_PROJET='';
			if(strlen($info->NOM_PROJET) > 4)
			{
				$NOM_PROJET =  mb_substr($info->NOM_PROJET, 0, 3).'...<a class="btn-sm" title="'.$info->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
			}
			else
			{
				$NOM_PROJET =  !empty($info->NOM_PROJET) ? $info->NOM_PROJET : 'N/A';
			}

			$DESCR_ETAPE='';
			if(strlen($info->DESCR_ETAPE) > 4)
			{
				$DESCR_ETAPE =  mb_substr($info->DESCR_ETAPE, 0, 3).'...<a class="btn-sm" title="'.$info->DESCR_ETAPE.'"><i class="fa fa-eye"></i></a>';
			}
			else
			{
				$DESCR_ETAPE =  !empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
			}

			$DATE_INSERTION = date('d/m/Y H:i',strtotime($info->DATE_INSERTION));

			$post[]=$u++;
			$post[]=!empty($info->NUMERO_PROJET) ? $info->NUMERO_PROJET : 'N/A';
			$post[]=$NOM_PROJET;
			$post[]=$DESCR_ETAPE;
			$post[]=!empty($info->DESCR_STATUT_PROJET) ? $info->DESCR_STATUT_PROJET : 'N/A';
			$post[]=$DATE_INSERTION;

			$detail = lang('messages_lang.detail');
			$suppr = lang('messages_lang.supprimer_action');
			$action = '<div class="dropdown">
			<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
			'.lang('messages_lang.table_Action').'
			</button>
			<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
			<li><a class="dropdown-item" href="'.base_url('pip/Processus_Investissement_Public/details/'.$info->ID_DEMANDE).'">'.$detail.'</a></li>
			<li><a class="dropdown-item"  onclick="confirmation('.$info->ID_DEMANDE_INFO_SUPP.')">'.$suppr.'</a></li>

			</ul>
			</div>';

			$post[]= $action;
			$data[]=$post;  
		}

		$requeteqp='CALL `getList`("'.$query_principal.'")';
		$recordsTotal = $this->ModelPs->datatable( $requeteqp);
		$requeteqf='CALL `getList`("'.$query_filter.'")';
		$recordsFiltered = $this->ModelPs->datatable( $requeteqf);

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" =>count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

	private function update_all_table($table, $datatomodifie, $conditions)
	{
		$bindparams = [$table, $datatomodifie, $conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	
	/**fonction pour supprimer les projet qui sont infini*/
	function annuler_projet_complet($id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}	
		$statut=1;
		$projet_incomplet=1;
		$table = 'pip_demande_infos_supp';
		$conditions = "ID_DEMANDE_INFO_SUPP  =" . $id;
		$datatomodifie = 'IS_ANNULER=' . $projet_incomplet;
		$statut = 1;
		$this->update_all_table($table, $datatomodifie, $conditions);
		$data = [
			'message' => lang('messages_lang.message_succes_compilation'),
			'statut'=>$statut
		];
		session()->setFlashdata('alert', $data);
		return json_encode($statut);
	}	
}
?>
