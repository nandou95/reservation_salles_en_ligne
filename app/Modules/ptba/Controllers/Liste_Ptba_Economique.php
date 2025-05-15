<?php
/*
* RUGAMBA Jean Vainqueur
* liste des ptba - Classification économique
* le 26/09/2023
*/

namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liste_Ptba_Economique extends BaseController
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
    
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$data = $this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		//Sélectionner les chapîtres économiques
		$bindparams = $this->getBindParms('`ARTICLE_ID`,`CHAPITRE_ID`,`CODE_ARTICLE`,`LIBELLE_ARTICLE`', 'class_economique_article', '1', '`LIBELLE_ARTICLE` ASC');
		$data['articl'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    //Sélectionner les tranches
		$bind_tranche = $this->getBindParms('TRANCHE_ID,CODE_TRANCHE,DESCRIPTION_TRANCHE','op_tranches','1','TRANCHE_ID ASC');
		$data['tranche'] = $this->ModelPs->getRequete($psgetrequete, $bind_tranche);
		return view('App\Modules\ptba\Views\Liste_Ptba_Economique_View',$data);   
	}

	//liste des ptba
	function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);

		$psgetrequete = "CALL `getRequete`(?,?,?,?);";

		$ARTICLE_ID = $this->request->getPost('ARTICLE_ID');
		$CODE_PARAGRAPHE = $this->request->getPost('CODE_PARAGRAPHE');
		$LITTERA_ID = $this->request->getPost('LITTERA_ID');
		$CODE_TRANCHE = $this->request->getPost('CODE_TRANCHE');

		$critere1="";
		$critere2="";
		$critere3="";

		//Filtre par article
		if(!empty($ARTICLE_ID))
		{
			$bindparams = $this->getBindParms('`ARTICLE_ID`,`CHAPITRE_ID`,`CODE_ARTICLE`,`LIBELLE_ARTICLE`', 'class_economique_article', 'ARTICLE_ID='.$ARTICLE_ID, '`LIBELLE_ARTICLE` ASC');
			$articl = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
			$critere1 = " AND ptba.`ARTICLE_ECONOMIQUE`='".$articl['CODE_ARTICLE']."'";

			//Filtre par paragraphe
			if(!empty($CODE_PARAGRAPHE))
			{
				//$CODE_PARAGRAPHE représente en réalité PARAGRAPHE_ID.
				$bind_parag = $this->getBindParms('PARAGRAPHE_ID,ARTICLE_ID,CODE_PARAGRAPHE,LIBELLE_PARAGRAPHE', 'class_economique_paragraphe','PARAGRAPHE_ID='.$CODE_PARAGRAPHE, 'LIBELLE_PARAGRAPHE ASC');
				$parag = $this->ModelPs->getRequeteOne($psgetrequete, $bind_parag);

				$critere2=" AND SUBSTRING(NATURE_ECONOMIQUE, 1, 3) LIKE '%".$parag['CODE_PARAGRAPHE']."%'";



      	//Filtre par littera
				if(!empty($LITTERA_ID))
				{
					$bind_lit = $this->getBindParms('LITTERA_ID,PARAGRAPHE_ID,CODE_LITTERA,LIBELLE_LITTERA', 'class_economique_littera', 'LITTERA_ID='.$LITTERA_ID, '`LIBELLE_LITTERA` ASC');
					$littera= $this->ModelPs->getRequeteOne($psgetrequete, $bind_lit);
					$critere3=" AND SUBSTRING(NATURE_ECONOMIQUE, 1, 4) LIKE '%".$littera['CODE_LITTERA']."%'";
				}

			}
		}

		$query_principal="SELECT ptba.PTBA_ID, inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME AS CODE_PROGRAMME,prog.INTITULE_PROGRAMME AS INTITULE_PROGRAMME,act.CODE_ACTION AS CODE_ACTION,act.LIBELLE_ACTION AS LIBELLE_ACTION,ptba.T1,ptba.T2,ptba.T3,ptba.T4,ptba.QT1,ptba.QT2,ptba.QT3,ptba.QT4,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES, ptba.RESULTATS_ATTENDUS,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.PROGRAMMATION_FINANCIERE_BIF,ptba.ARTICLE_ECONOMIQUE,ptba.CODES_PROGRAMMATIQUE,ptba.UNITE FROM ptba JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions act ON act.ACTION_ID=ptba.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";

		$limit="LIMIT 0,10";
		if($_POST['length'] != -1)
		{
			$limit="LIMIT ".$_POST['start'].",".$_POST['length'];
		}

		$order_by="";
		$order_column="";
		$order_column= array('inst.DESCRIPTION_INSTITUTION','prog.INTITULE_PROGRAMME','act.LIBELLE_ACTION','ptba.ACTIVITES','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','ptba.RESULTATS_ATTENDUS',1,1);

		$order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY inst.CODE_INSTITUTION ASC";

		$search = !empty($_POST['search']['value']) ? (" AND (ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%". $var_search."%' OR ptba.ACTIVITES LIKE '%".$var_search."%' OR ptba.RESULTATS_ATTENDUS LIKE '%".$var_search."%' OR inst.CODE_INSTITUTION LIKE '%".$var_search."%' OR inst.DESCRIPTION_INSTITUTION LIKE '%".$var_search."%' OR prog.CODE_PROGRAMME LIKE '%".$var_search."%' OR prog.INTITULE_PROGRAMME LIKE '%".$var_search."%' OR act.CODE_ACTION LIKE '%".$var_search."%' OR act.LIBELLE_ACTION LIKE '%".$var_search."%' OR ptba.CODES_PROGRAMMATIQUE LIKE '%".$var_search."%')") : "";

		$criteres = $critere1." ".$critere2." ".$critere3;
		$query_secondaire = $query_principal." ".$search." ".$criteres." ".$order_by." ".$limit;
		$query_secondaire = str_replace('"', '\\"', $query_secondaire);
		
		$query_filter = $query_principal." ".$search." ".$criteres;
		$query_filter=str_replace('"', '\\"',$query_filter);
		$requete='CALL `getList`("'.$query_secondaire.'")';
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
				$post[]="Pas&nbsp;disponible";
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

	// LES SELECTS 
  //Sélectionner les paragraphes à partir des articles
	function get_parag()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$ARTICLE_ID =$this->request->getPost('ARTICLE_ID');

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_parag = $this->getBindParms('PARAGRAPHE_ID,ARTICLE_ID,CODE_PARAGRAPHE,LIBELLE_PARAGRAPHE', 'class_economique_paragraphe', 'ARTICLE_ID='.$ARTICLE_ID, 'LIBELLE_PARAGRAPHE ASC');
		$parag = $this->ModelPs->getRequete($callpsreq, $bind_parag);
		

		$input_select = lang("messages_lang.labelle_selecte");
		$html='<option value="">'.$input_select.'</option>';

		if(!empty($parag) )
		{
			foreach($parag as $key)
			{   
				if($key->PARAGRAPHE_ID==set_value('PARAGRAPHE_ID'))
				{
					$html.= "<option value='".$key->PARAGRAPHE_ID."' selected>".$key->CODE_PARAGRAPHE."&nbsp;&nbsp-&nbsp;&nbsp".$key->LIBELLE_PARAGRAPHE."</option>";
				}
				else
				{
					$html.= "<option value='".$key->PARAGRAPHE_ID."'>".$key->CODE_PARAGRAPHE."&nbsp;&nbsp-&nbsp;&nbsp".$key->LIBELLE_PARAGRAPHE."</option>";
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

  //Sélectionner les littéra à partir des paragraphes
	function get_litera()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_ECONOMIQUE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$CODE_PARAGRAPHE =$this->request->getPost('CODE_PARAGRAPHE');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparams = $this->getBindParms('LITTERA_ID,PARAGRAPHE_ID,CODE_LITTERA,LIBELLE_LITTERA', 'class_economique_littera', 'PARAGRAPHE_ID='.$CODE_PARAGRAPHE, '`LIBELLE_LITTERA` ASC');
		$littera = $this->ModelPs->getRequete($callpsreq, $bindparams);


    //Declaration des labels pour l'internalisation
		$input_select = lang("messages_lang.labelle_selecte");
		$html='<option value="">'.$input_select.'</option>';

		if(!empty($littera) )
		{
			foreach($littera as $key)
			{   
				if($key->LITTERA_ID==set_value('LITTERA_ID'))
				{
					$html.= "<option value='".$key->LITTERA_ID."' selected>".$key->CODE_LITTERA."&nbsp;&nbsp-&nbsp;&nbsp".$key->LIBELLE_LITTERA."</option>";
				}
				else
				{
					$html.= "<option value='".$key->LITTERA_ID."'>".$key->CODE_LITTERA."&nbsp;&nbsp-&nbsp;&nbsp".$key->LIBELLE_LITTERA."</option>";
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


	/*
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