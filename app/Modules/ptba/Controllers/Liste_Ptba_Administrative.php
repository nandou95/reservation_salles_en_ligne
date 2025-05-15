<?php 

/*
* RUGAMBA Jean Vainqueur
* liste des ptba
* le 18/09/2023
*/

namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liste_Ptba_Administrative extends BaseController
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
    
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ADMINISTRATIVE')!=1)
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
		return view('App\Modules\ptba\Views\Liste_Ptba_Administrative_View',$data);   
	}

	//liste des ptba
	function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ADMINISTRATIVE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
		$CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
		$CODE_ACTION = $this->request->getPost('CODE_ACTION');
		$CODE_TRANCHE = $this->request->getPost('CODE_TRANCHE');

		$critere1="";
		$critere2="";
		$critere3="";
		$critere4="";
		$critere5="";

		//Filtre par institution
		if(!empty($INSTITUTION_ID))
		{		
			$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
			$inst = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
			$critere1 = " AND inst.CODE_INSTITUTION LIKE '".$inst['CODE_INSTITUTION']."'";
			//Filtre par sous tutelle
			if(!empty($SOUS_TUTEL_ID))
			{
				$bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID , CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='.$SOUS_TUTEL_ID, 'CODE_SOUS_TUTEL ASC');
				$codestut= $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);
				$critere2=" AND SUBSTRING(ligne.CODE_NOMENCLATURE_BUDGETAIRE, 5, 4) LIKE '%".$codestut['CODE_SOUS_TUTEL']."%' ";

      	//Filtre par programme
				if(!empty($CODE_PROGRAMME))
				{
					$critere3=" AND prog.CODE_PROGRAMME = ".$CODE_PROGRAMME;

      		//Filtre par action
					if(!empty($CODE_ACTION))
					{
						$critere4=" AND act.CODE_ACTION =".$CODE_ACTION;
					}
				}
			}
		}

		$query_principal="SELECT ptba.PTBA_ID, inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ptba.T1,ptba.T2,ptba.T3,ptba.T4,ptba.QT1,ptba.QT2,ptba.QT3,ptba.QT4,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES, ptba.RESULTATS_ATTENDUS,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.PROGRAMMATION_FINANCIERE_BIF,ptba.ARTICLE_ECONOMIQUE,ptba.CODES_PROGRAMMATIQUE,ptba.UNITE FROM ptba JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions act ON act.ACTION_ID=ptba.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID =ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID  WHERE 1";

		$limit="LIMIT 2";
		if($_POST['length'] != -1)
		{
			$limit="LIMIT ".$_POST["start"].",".$_POST["length"];
		}

		$order_by="";
		$order_column="";
		$order_column= array('inst.DESCRIPTION_INSTITUTION','prog.INTITULE_PROGRAMME','act.LIBELLE_ACTION','ptba.ACTIVITES','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.RESULTATS_ATTENDUS',1,1);

		$order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ." ".$_POST['order']['0']['dir'] : " ORDER BY inst.CODE_INSTITUTION ASC";

		$search = !empty($_POST['search']['value']) ? (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%". $var_search."%' OR ptba.ACTIVITES LIKE '%".$var_search."%' OR ptba.RESULTATS_ATTENDUS LIKE '%".$var_search."%' OR inst.CODE_INSTITUTION LIKE '%".$var_search."%' OR inst.DESCRIPTION_INSTITUTION LIKE '%".$var_search."%' OR prog.CODE_PROGRAMME LIKE '%".$var_search."%' OR prog.INTITULE_PROGRAMME LIKE '%".$var_search."%' OR act.CODE_ACTION LIKE '%".$var_search."%' OR act.LIBELLE_ACTION LIKE '%".$var_search."%' OR ptba.CODES_PROGRAMMATIQUE LIKE '%".$var_search."%')") : "";

		$criteres = $critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere5;
		$query_secondaire = $query_principal." ".$search." ".$criteres." ".$order_by." ".$limit;
		$query_secondaire = str_replace("\\", "", $query_secondaire);	

		$query_filter = $query_principal." ".$search." ".$criteres;
		$query_filter=str_replace('"', '\\"',$query_filter);
		$requete='CALL `getList`("'.$query_secondaire.'")';
		//print_r($requete);exit();
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
				$quantite = floatval($info->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE);
				$montant = floatval($info->PROGRAMMATION_FINANCIERE_BIF);
			}

			$post=array();
			//Declaration des labels pour l'internalisation
			$icone_afficher = lang("messages_lang.icone_afficher");
			$INTITULE_MINISTERE = (mb_strlen($info->INTITULE_MINISTERE) > 9) ? (mb_substr($info->INTITULE_MINISTERE, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#inst" . $info->PTBA_ID . "' data-toggle='tooltip' title='$icone_afficher'><i class='fa fa-eye'></i></a>") : $info->INTITULE_MINISTERE;

			$INTITULE_PROGRAMME = (mb_strlen($info->INTITULE_PROGRAMME) > 9) ? (mb_substr($info->INTITULE_PROGRAMME, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#prog" . $info->PTBA_ID . "' data-toggle='tooltip' title='$icone_afficher'><i class='fa fa-eye'></i></a>") : $info->INTITULE_PROGRAMME;

			$LIBELLE_ACTION = (mb_strlen($info->LIBELLE_ACTION) > 9) ? (mb_substr($info->LIBELLE_ACTION, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#action" . $info->PTBA_ID . "' data-toggle='tooltip' title='$icone_afficher'><i class='fa fa-eye'></i></a>") : $info->LIBELLE_ACTION;

			$ACTIVITES = (mb_strlen($info->ACTIVITES) > 9) ? (mb_substr($info->ACTIVITES, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#activites" . $info->PTBA_ID . "' data-toggle='tooltip' title='$icone_afficher'><i class='fa fa-eye'></i></a>") : $info->ACTIVITES;

			$RESULTATS_ATTENDUS = (mb_strlen($info->RESULTATS_ATTENDUS) > 9) ? (mb_substr($info->RESULTATS_ATTENDUS, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#resultat" . $info->PTBA_ID . "' data-toggle='tooltip' title='$icone_afficher'><i class='fa fa-eye'></i></a>") : $info->RESULTATS_ATTENDUS;

			$post[]=$INTITULE_MINISTERE." (".$info->CODE_MINISTERE.")";
			$post[]=$INTITULE_PROGRAMME." (".$info->CODE_PROGRAMME.")";
			
			if($info->CODE_ACTION !="")
			{
				$post[]=$LIBELLE_ACTION." (".$info->CODE_ACTION.")";
			}
			elseif($info->CODE_ACTION =="")
			{
				$post[]="N/A";
			}
			
			$post[]=$ACTIVITES." (".$info->CODES_PROGRAMMATIQUE.")";
			$post[]=$info->CODE_NOMENCLATURE_BUDGETAIRE;			
			
			if($RESULTATS_ATTENDUS !="")
			{
				$post[]=$RESULTATS_ATTENDUS;
			}
			elseif($RESULTATS_ATTENDUS =="")
			{
				$post[]="N/A";
			}

			$post[]=number_format($quantite,2,","," ")." ".$info->UNITE;
			$post[]=number_format($montant,2,","," ")." BIF

			<div class='modal fade' id='inst".$info->PTBA_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<b style='font-size:13px;'> ".$info->INTITULE_MINISTERE." </b>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			Quitter
			</button>
			</div>
			</div>
			</div>
			</div>

			<div class='modal fade' id='prog".$info->PTBA_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<b style='font-size:13px;'> ".$info->INTITULE_PROGRAMME." </b>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			Quitter
			</button>
			</div>
			</div>
			</div>
			</div>

			<div class='modal fade' id='action".$info->PTBA_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<b style='font-size:13px;'> ".$info->LIBELLE_ACTION." </b>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			Quitter
			</button>
			</div>
			</div>
			</div>
			</div>

			<div class='modal fade' id='activites".$info->PTBA_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<b style='font-size:13px;'> ".$info->ACTIVITES." </b>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			Quitter
			</button>
			</div>
			</div>
			</div>
			</div>

			<div class='modal fade' id='resultat".$info->PTBA_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<b style='font-size:13px;'> ".$info->RESULTATS_ATTENDUS." </b>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
			Quitter
			</button>
			</div>
			</div>
			</div>
			</div>";
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
  //Sélectionner les sous tutelles à partir des institutions
	function get_soutut()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ADMINISTRATIVE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$INSTITUTION_ID, 'DESCRIPTION_SOUS_TUTEL ASC');
		$sous_tut = $this->ModelPs->getRequete($callpsreq, $bind_sous_tut);
		//Declaration des labels pour l'internalisation
		$input_select = lang("messages_lang.labelle_selecte");
		$html='<option value="">'.$input_select.'</option>';

		if(!empty($sous_tut) )
		{
			foreach($sous_tut as $key)
			{ 
				if($key->SOUS_TUTEL_ID==set_value('SOUS_TUTEL_ID'))
				{
					$html.= "<option value='".$key->SOUS_TUTEL_ID."' selected>".$key->CODE_SOUS_TUTEL."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_SOUS_TUTEL."</option>";
				}
				else
				{
					$html.= "<option value='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_SOUS_TUTEL."</option>";
				}
			}
		}
		else
		{
			//Declaration des labels pour l'internalisation
			$input_select = lang("messages_lang.labelle_selecte");
			$html='<option value="">'.$input_select.'</option>';
		}
		$output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Sélectionner les programmes à partir des sous tutelles
  function get_prog()
  {
  	$session  = \Config\Services::session();
  	if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ADMINISTRATIVE')!=1)
  	{
  		return redirect('Login_Ptba/homepage');
  	}
  	$INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');

  	$SOUS_TUTEL_ID =$this->request->getPost('SOUS_TUTEL_ID');

  	$callpsreq = "CALL `getRequete`(?,?,?,?);";

    //Sélectionner les intitutions
  	$bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
  	$instit = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

    //Sélectionner les sous tutelles
  	$bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID , CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='.$SOUS_TUTEL_ID, 'CODE_SOUS_TUTEL ASC');
  	$codestut= $this->ModelPs->getRequeteOne($callpsreq, $bind_sous_tut);

  	$get_prog = "SELECT DISTINCT prog.INTITULE_PROGRAMME, prog.CODE_PROGRAMME FROM inst_institutions_programmes prog JOIN ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND inst.CODE_INSTITUTION ='".$instit['CODE_INSTITUTION']."' AND SUBSTRING(ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE, 5, 4) LIKE '%".$codestut['CODE_SOUS_TUTEL']."%' ORDER BY INTITULE_PROGRAMME ";

  	$details='CALL `getTable`("'.$get_prog.'")';
  	$prog = $this->ModelPs->getRequete( $details);

		//Declaration des labels pour l'internalisation
  	$input_select = lang("messages_lang.labelle_selecte");
  	$html='<option value="">'.$input_select.'</option>';

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
  	}
  	else
  	{
    	//Declaration des labels pour l'internalisation
  		$input_select = lang("messages_lang.labelle_selecte");
  		$html='<option value="">'.$input_select.'</option>';
  	}
  	$output = array('status' => TRUE , 'html' => $html);
  	return $this->response->setJSON($output);
  }

  //Sélectionner les actions à partir des programmes
  function get_action()
  {
  	$session  = \Config\Services::session();
  	if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ADMINISTRATIVE')!=1)
  	{
  		return redirect('Login_Ptba/homepage');
  	}
  	$CODE_PROGRAMME =$this->request->getPost('CODE_PROGRAMME');

  	$callpsreq = "CALL `getRequete`(?,?,?,?);";

  	$get_action = "SELECT DISTINCT act.LIBELLE_ACTION, act.CODE_ACTION FROM inst_institutions_actions act JOIN ptba ON ptba.ACTION_ID=act.ACTION_ID  JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID WHERE prog.CODE_PROGRAMME LIKE '%".$CODE_PROGRAMME."%' ORDER BY act.LIBELLE_ACTION";

  	$details='CALL `getTable`("'.$get_action.'")';
  	$action = $this->ModelPs->getRequete( $details);

		//Declaration des labels pour l'internalisation
  	$input_select = lang("messages_lang.labelle_selecte");
  	$html='<option value="">'.$input_select.'</option>';

  	if(!empty($action) )
  	{
  		foreach($action as $key)
  		{   
  			if($key->CODE_ACTION==set_value('CODE_ACTION'))
  			{
  				$html.= "<option value='".$key->CODE_ACTION."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
  			}
  			else
  			{
  				$html.= "<option value='".$key->CODE_ACTION."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
  			}
  		}
  	}
  	else
  	{
			//Declaration des labels pour l'internalisation
  		$input_select = lang("messages_lang.labelle_selecte");
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