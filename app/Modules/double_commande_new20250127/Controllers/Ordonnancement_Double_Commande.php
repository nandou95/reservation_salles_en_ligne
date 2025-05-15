<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Ordonnancement Double Commande
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 14 sept 2023
**/

/*
*Updated by :Alain Charbel NDERAGTAKURA
*Tel: +257 62 003 522
*Email pro: charbel@mediabox.bi
*Titre: Amelioration Liste des Ordonnancements
*/

 namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Ordonnancement_Double_Commande extends BaseController
{
  protected $session;
  protected $ModelPs;
  
  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//récupération du sous tutelle par rapport à l'institution
	public function getSousTutel()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

		$sql_institution='SELECT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel JOIN user_affectaion ON inst_institutions_sous_tutel.SOUS_TUTEL_ID=user_affectaion.SOUS_TUTEL_ID WHERE 1 AND user_affectaion.INSTITUTION_ID ='.$INSTITUTION_ID.' AND IS_SOUS_TUTEL=1 ';
		$sous_tutel_data = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");

		if (!empty($sous_tutel_data)) {
			$sous_tutel = $sous_tutel_data;
		}else{
			$sql_institution='SELECT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
			$sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");
		}

		$tutel="<option value=''>--".lang('messages_lang.selection_message')."--</option>";
		foreach ($sous_tutel as $key)
		{
			$tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	//vieux liquidation a faire
	public function get_ordon_Afaire($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }


		$data_menu=$this->getDataMenuOrdonnancement();
		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
		$data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];

		//Compter - transmission vers la prise en charge
		$data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_spe']=$data_titre['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_titre['get_bord_deja_spe'];

		return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Afaire_List',$data);   
	}

	function listing_ordon_Afaire($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

		// $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$institution='';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.')';
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.' AND INSTITUTION_ID='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.')';
		}
		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

	  $var_search = str_replace("'", "\'", $var_search);
	  $group = "";
	  $critaire = "";
	  $limit = 'LIMIT 0,1000';
	  if ($_POST['length'] != -1) {
	    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
	  }

	  $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,inst.CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,execdet.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=execdet.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=execdet.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE prof.PROFIL_ID=".$profil_id." AND dc.MOUVEMENT_DEPENSE_ID=4 AND execdet.ETAPE_DOUBLE_COMMANDE_ID IN(14,15) ".$institution."";
	  $order_by = '';

	  $order_column=array('NUMERO_BON_ENGAGEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','act.DESC_PAP_ACTIVITE', 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);

	  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

	  // $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')"):'';

	  $search = !empty($_POST['search']['value']) ? (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')") : '';

	  //condition pour le query principale
	  $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
	  
	  // condition pour le query filter
	  $conditionsfilter = $critaire . " ". $search ." " . $group;

	  $requetedebases=$requetedebase." ".$conditions;	  

	  $requetedebasefilter=$requetedebase." ".$conditionsfilter;

	  $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

	  $fetch_actions = $this->ModelPs->datatable($query_secondaire);
	  
	  $data = array();
	  $u=1;
		foreach ($fetch_actions as $row)
		{
			$sub_array = array();
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
		 $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
			}

			$number=$row->NUMERO_BON_ENGAGEMENT;
			$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$callpsreq = "CALL getRequete(?,?,?,?);";
			$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
			$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

			if (!empty($getProfil))
			{
				foreach ($getProfil as $value)
				{

					if ($prof_id == $value->PROFIL_ID || $prof_id==1)
					{
						$number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

				        $bouton= "<a class='btn btn-primary btn-sm' title='Ordonnancement' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
					}  
				} 
			}

			if (strlen($row->DESC_PAP_ACTIVITE) > 4)
			{
				$ACTIVITES =  substr($row->DESC_PAP_ACTIVITE, 0, 4) .'...<a class="btn-sm"  title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
			}
			else
			{
				$ACTIVITES =  $row->DESC_PAP_ACTIVITE;
			}

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 10) . '...<a class="btn-sm" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></center></a>') : $row->COMMENTAIRE;

			// $MONTANT_RACCROCHE=number_format($row->MONTANT_RACCROCHE,'2',',',' ');
			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$ACTIVITES;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=number_format($row->ENG_BUDGETAIRE,4,","," ");
			$sub_array[]=number_format($row->ENG_JURIDIQUE,4,","," ");
			$sub_array[]=number_format($row->MONTANT_LIQUIDATION,4,","," ");
			$action1 ='<div class="row dropdown" style="color:#fff;">
			"'.$bouton.'"';
			$action =$action1." "."<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a></div>";
			$sub_array[] = $action;
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );	
    return $this->response->setJSON($output);
	}

	//vieux liquidation deja fait
	public function get_ordon_deja_fait($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}else{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuOrdonnancement();

		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
		$data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];

		//Compter - transmission vers la prise en charge
		$data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_spe']=$data_titre['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_titre['get_bord_deja_spe'];

		return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Deja_Fait_List',$data);   
	}

	function listing_ordon_deja_fait($value = 0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
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
		$order_column=array('NUMERO_BON_ENGAGEMENT','inst.CODE_NOMENCLATURE_BUDGETAIRE','act.DESC_PAP_ACTIVITE', 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION','execdet.MONTANT_ORDONNANCEMENT',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR inst.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,execdet.MONTANT_ORDONNANCEMENT,inst.CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,execdet.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_tache_detail execdet JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE execdet.ETAPE_DOUBLE_COMMANDE_ID>14 AND execdet.ETAPE_DOUBLE_COMMANDE_ID<>15";

			$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
			$limit = 'LIMIT 0,10';

			$requetedebases = $requetedebase . ' ' . $conditions;

			$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

			$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
			$fetch_data = $this->ModelPs->datatable($query_secondaire);

			$data = array();
			$u = 1;
			foreach ($fetch_data as $row)
			{
				$sub_array = array();

				$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
				$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
				$getEtape = "CALL getTable('" . $getEtape . "');";
				$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
				$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
				$dist="";
				if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
				{
					if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
					if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
					if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
				}
				
				$number=$row->NUMERO_BON_ENGAGEMENT;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

				$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
				$callpsreq = "CALL getRequete(?,?,?,?);";
				$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
				$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

				if (!empty($getProfil))
				{
					foreach ($getProfil as $value)
					{

						if ($prof_id == $value->PROFIL_ID || $prof_id==1)
						{
					        $number= "<a  title='' >".$row->NUMERO_BON_ENGAGEMENT."</a>";					
						}  
					} 
				}

				if(strlen($row->DESC_PAP_ACTIVITE) > 4)
				{
					$ACTIVITES =  substr($row->DESC_PAP_ACTIVITE, 0, 4) .'...<a class="btn-sm"data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>';
				}
				else
				{
					$ACTIVITES =  $row->DESC_PAP_ACTIVITE;
				}
				$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 10) . '...<a class="btn-sm" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></center></a>') : $row->COMMENTAIRE;


				$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'4',',',' ');
				$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'4',',',' ');
				$MONTANT_LIQUIDATION=number_format($row->MONTANT_LIQUIDATION,'4',',',' ');
				$MONTANT_ORDONNANCEMENT=number_format($row->MONTANT_ORDONNANCEMENT,'4',',',' ');
				
				$sub_array[]=$number;
				$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
				$sub_array[]=$ACTIVITES;
				$sub_array[]=$COMMENTAIRE;
				$sub_array[]=!empty($ENG_BUDGETAIRE) ? $ENG_BUDGETAIRE : 0;
				$sub_array[]=!empty($ENG_JURIDIQUE) ? $ENG_JURIDIQUE : 0;
				$sub_array[]=!empty($MONTANT_LIQUIDATION) ? $MONTANT_LIQUIDATION : 0;
				$sub_array[] = !empty($MONTANT_ORDONNANCEMENT) ? $MONTANT_ORDONNANCEMENT : 0;
				$action2 ="<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
				$sub_array[] = $action2;
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
		return $this->response->setJSON($output);//echo json_encode($output);	
	}
}
?>