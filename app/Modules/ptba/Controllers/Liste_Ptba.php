<?php
/**
* RUGAMBA Jean Vainqueur
* liste des ptba
* le 18/09/2023
*/

namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liste_Ptba extends BaseController
{
	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

  //function qui appelle le view de la liste 
	function index()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIVITES')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		//Sélectionner les institutions
		$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`DESCRIPTION_INSTITUTION` ASC');
		$data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    //Sélectionner les tranches
		$bind_tranche = $this->getBindParms('TRANCHE_ID,CODE_TRANCHE,DESCRIPTION_TRANCHE','op_tranches','1','TRANCHE_ID ASC');
		$data['tranche'] = $this->ModelPs->getRequete($psgetrequete, $bind_tranche);

		return view('App\Modules\ptba\Views\Liste_Ptba_View',$data);   
	}

	//liste des ptba
	function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIVITES')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
		$ACTION_ID = $this->request->getPost('CODE_ACTION');
		$CODE_TRANCHE = $this->request->getPost('CODE_TRANCHE');

		$critere1='';
		$critere2='';
		$critere3='';
		$critere4='';
		$critere5='';

		//Filtre par institution
		if(!empty($INSTITUTION_ID))
		{
			$critere1 = ' AND ptba.`INSTITUTION_ID`='.$INSTITUTION_ID;


    	//Filtre par programme
			if(!empty($CODE_PROGRAMME))
			{
				$bindparams = $this->getBindParms('`PROGRAMME_ID`,`INTITULE_PROGRAMME`', 'inst_institutions_programmes', 'CODE_PROGRAMME='.$CODE_PROGRAMME, '1');
			  $prog = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
				$critere2=' AND ptba.PROGRAMME_ID = '.$prog['PROGRAMME_ID'];

    		//Filtre par action
				if(!empty($ACTION_ID))
				{
					$critere3=' AND ptba.ACTION_ID ='.$ACTION_ID;
				}
			}
		}

		$query_principal="SELECT ptba.PTBA_ID,prog.INTITULE_PROGRAMME AS INTITULE_PROGRAMME,act.LIBELLE_ACTION AS LIBELLE_ACTION,ptba.T1,ptba.T2,ptba.T3,ptba.T4,ptba.QT1,ptba.QT2,ptba.QT3,ptba.QT4,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES, ptba.RESULTATS_ATTENDUS,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.PROGRAMMATION_FINANCIERE_BIF FROM ptba JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions act ON act.ACTION_ID=ptba.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID =ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}

		$order_by='';
		$order_column='';
		$order_column= array('ptba.ACTIVITES','ptba.RESULTATS_ATTENDUS',1,'ligne.CODE_NOMENCLATURE_BUDGETAIRE',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba.ACTIVITES ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'. $var_search.'%" OR ptba.ACTIVITES LIKE "%'.$var_search.'%" OR ptba.RESULTATS_ATTENDUS LIKE "%'.$var_search.'%")') : '';

		$criteres = $critere1.' '.$critere2.' '.$critere3.' '.$critere4.''.$critere5;
		$query_secondaire = $query_principal.' '.$search.' '.$criteres.' '.$order_by.' '.$limit;
		$query_secondaire = str_replace('"', '\\"', $query_secondaire);
		
		$query_filter = $query_principal.' '.$search.' '.$criteres;
		$query_filter=str_replace('"', '\\"',$query_filter);
		$requete="CALL `getList`('".$query_secondaire."')";
		$fetch_cov_frais = $this->ModelPs->datatable( $requete);
		$data = array();
		$u=1;
		foreach($fetch_cov_frais as $info)
		{
			$quantite=0;
			$montant=0;

			if(!empty($CODE_TRANCHE))
			{
				if($CODE_TRANCHE == 'T1')
				{
					$quantite=floatval($info->QT1);
					$montant=floatval($info->T1);
				}
				elseif($CODE_TRANCHE == 'T2')
				{
					$quantite=floatval($info->QT2);
					$montant=floatval($info->T2);
				}
				elseif($CODE_TRANCHE == 'T3')
				{
					$quantite=floatval($info->QT3);
					$montant=floatval($info->T3);
				}
				elseif($CODE_TRANCHE == 'T4')
				{
					$quantite=floatval($info->QT4);
					$montant=floatval($info->T4);
				}
			}
			else
			{
				$quantite = floatval($info->QT1) + floatval($info->QT2) + floatval($info->QT3) + floatval($info->QT4);
				$montant = floatval($info->T1) + floatval($info->T2) + floatval($info->T3) + floatval($info->T4);
			}

			$post=array();
			$post[]=$info->ACTIVITES;
			$post[]=$info->RESULTATS_ATTENDUS;
			$post[]=number_format($quantite,2,","," ");
			$post[]=$info->CODE_NOMENCLATURE_BUDGETAIRE;
			$post[]=number_format($montant,2,","," ");

			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			//Declaration des labels pour l'internalisation
			$bouton_detail = lang("messages_lang.bouton_detail");
			$action .="<li>
			<a href='".base_url('ptba/Dem_Detail_Activite/'.md5($info->PTBA_ID))."'>
			<label>&nbsp;<span class='fa fa-edit'></span>&nbsp;$bouton_detail</label>
			</a>
			</li>
			</ul>
			</div>";
			$post[]=$action;
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

	// LES SELECTS DES ACTIVITES
	//Sélectionner les programmes à partir des sous tutelles
	function get_prog()
	{
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    //Sélectionner les programmes
    $bindprog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes', 'INSTITUTION_ID='.$INSTITUTION_ID, '`INTITULE_PROGRAMME` ASC');
    $prog = $this->ModelPs->getRequete($callpsreq, $bindprog);
    $html='<option value="">-Sélectionner-</option>';
    if(!empty($prog) )
    {
      foreach($prog as $key)
      {   
        if($key->CODE_PROGRAMME==set_value('CODE_PROGRAMME'))
        {
        $html.= "<option value='".$key->CODE_PROGRAMME."' selected>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
        else
        {
        $html.= "<option value='".$key->CODE_PROGRAMME."'>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
      }
    }else{
    $html='<option value="">-Sélectionner-</option>';
    }
    $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);
	}

	//Sélectionner les actions à partir des programmes
	function get_action()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIVITES')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$CODE_PROGRAMME =$this->request->getPost('CODE_PROGRAMME');
		$bindparams = $this->getBindParms('`PROGRAMME_ID`,`INTITULE_PROGRAMME`', 'inst_institutions_programmes', 'CODE_PROGRAMME='.$CODE_PROGRAMME, '1');
		$prog = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
		$PROGRAMME_ID=$prog['PROGRAMME_ID'];

		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$get_action = "SELECT DISTINCT ACTION_ID,LIBELLE_ACTION, CODE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID =".$PROGRAMME_ID." ORDER BY LIBELLE_ACTION ";

		$details='CALL `getTable`("'.$get_action.'")';
		$action = $this->ModelPs->getRequete( $details);

    //Declaration des labels pour l'internalisation
		$input_select = lang("messages_lang.labelle_selecte");
		$html='<option value="">'.$input_select.'</option>';

		if(!empty($action) )
		{
			foreach($action as $key)
			{   
				if($key->ACTION_ID==set_value('CODE_ACTION'))
				{
					$html.= "<option value='".$key->ACTION_ID."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
				}
				else
				{
					$html.= "<option value='".$key->ACTION_ID."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
				}
			}
		}
		else
		{
			$html='<option value="">'.$input_select.'</option>';
		}

		$output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);
  }


	/**
	* fonction pour retourner le tableau des parametre pour le PS pour les selection
  * @param string  $columnselect //colone A selectionner
  * @param string  $table        //table utilisE
  * @param string  $where        //condition dans la clause where
  * @param string  $orderby      //order by
  * @return  mixed
  */
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}
?>