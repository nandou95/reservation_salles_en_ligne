<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Liquidation Double Commande
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 14 sept 2023
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Liquidation_Double_Commande extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		define("DOMPDF_ENABLE_REMOTE", true);
		// $this->load->library('Excel');
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

		$sql_institution='SELECT CODE_SOUS_TUTEL,inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
		$sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");


		$tutel="<option value=''>--Sous tutel--</option>";
		foreach ($sous_tutel as $key)
		{
			$tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."-".$key->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	//vieux liquidation a faire
	public function get_liquid_Afaire($value='')
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

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
	  $data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
	  $data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Afaire_List',$data);   
	}

	function listing_liquid_Afaire($value = 0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 10 AND IS_FINISHED != 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1, 2) AND exec.INSTITUTION_ID IN (".$ID_INST.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
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
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=0';
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
      $dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
			}

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
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

	//view liquidation deja fait
	public function get_liquid_deja_fait($value='')
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


		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
  	$data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Deja_Fait_List',$data);   
	}

	function listing_liquid_deja_fait()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

    //Filtres de la liste
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
		$DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
		$DATE_FIN=$this->request->getPost('DATE_FIN');
		$ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
		$getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

		$ID_INST='';
		foreach ($getaffect as $value)
		{
			$ID_INST.=$value->INSTITUTION_ID.' ,';           
		}

		$ID_INST = substr($ID_INST,0,-1);

		$critere1="";
		$critere2=" ";
		$critere3="";
		$critere4="";
		$critere5="";
		if(!empty($INSTITUTION_ID))
		{
			$critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere4.=" AND execdet.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere5.=" AND execdet.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND execdet.DATE_LIQUIDATION <= '".$DATE_FIN."'";
    }

		$str_condiction_user=" AND histo.USER_ID=".$user_id;
		if($profil_id==1)
		{
			$str_condiction_user="";
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$group = "";
		$requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID = titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>10 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.  EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$str_condiction_user.")";


		$order_by = '';
		$order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%')"):'';

		$critaire = $critere1." ".$critere3." ".$critere2." ".$critere4." ".$critere5;
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
      //Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");
			}

			$action='';
			$sub_array = array();
			$sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$action = '<div class="dropdown" style="color:#fff;">
			<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-left">';

			$action .="<li>
			<a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
			</li>";

			$action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 11) ? "
			<li>
			<a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='green'>Correction</font></label></a>
			</li>" : "";

			$action .="
			<div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
			<center>
			<h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
			</h5>
			</center>
			</div>
			<div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
			<button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
			".lang('messages_lang.quiter_action')."
			</button>
			<a href='".base_url("double_commande_new/Liquidation_Double_Commande/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."
			</a>
			</div>";

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

	//vieux liquidation à corriger
	public function get_liquid_Acorriger($value='')
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

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
  	$data['nbr_from_ord']=$data_menu['nbr_from_ord'];
		
		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Acorriger_List',$data);   
	}

	function listing_liquid_Acorriger()
	{
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND exec.USER_ID='.$user_id;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 12 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','execdet.MONTANT_LIQUIDATION',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE  LIKE '%$var_search%' OR exec.ENG_JURIDIQUE  LIKE '%$var_search%' OR exec.ENG_JURIDIQUE  LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION  LIKE '%$var_search%' OR execdet.MONTANT_LIQUIDATION_DEVISE  LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
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
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;

      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      // $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
      $step=($EtapeActuel) ? 'Liquidation/getOne_corriger':0;

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      $dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
				if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/3";
			}

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
          	$number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      $LIQUIDATION=floatval($row->LIQUIDATION);
      $LIQUIDATION=number_format($LIQUIDATION,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

        $LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
        $LIQUIDATION=number_format($LIQUIDATION,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $sub_array[] = $LIQUIDATION;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
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

	//vieux liquidation à valider
	public function get_liquid_Avalider($value='')
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

		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];
		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
  	$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
  	$data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Avalider_List',$data);   
	}

	function listing_liquid_Avalider($value = 0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT DISTINCT(titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','det.MONTANT_LIQUIDATION',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
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
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      // $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
      $step=($EtapeActuel) ? 'Liquidation/getOne_conf':0;
      $dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==11) $dist="/2";
			}
			
      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      $LIQUIDATION=floatval($row->LIQUIDATION);
      $LIQUIDATION=number_format($LIQUIDATION,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

        $LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
        $LIQUIDATION=number_format($LIQUIDATION,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $sub_array[] = $LIQUIDATION;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
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

	//view liquidation deja validé
	public function get_liquid_valider($value='')
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

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1 AND $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','1','ANNEE_BUDGETAIRE_ID ASC');
		$data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);
		$data['annee_budgetaire_en_cours']=$this->get_annee_budgetaire();

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
	  $data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
	  $data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Double_Commande_Valider_List',$data);   
	}

	function listing_liquid_valider($value = 0)
	{
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1 AND $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
		$DATE_FIN=$this->request->getPost('DATE_FIN');
		$ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    $critere4="";
    $critere5="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$critere3.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
		}

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere4.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere5.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
    }

    $str_condiction_user=" AND USER_ID=".$user_id;
    if($profil_id==1)
    {
      $str_condiction_user="";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_DEMANDE FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 12 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$str_condiction_user.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','det.MONTANT_LIQUIDATION','det.MONTANT_LIQUIDATION_DEVISE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere5;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    // print($requetedebases);die();

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);


    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      $LIQUIDATION=floatval($row->LIQUIDATION);
      $LIQUIDATION=number_format($LIQUIDATION,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

        $LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
        $LIQUIDATION=number_format($LIQUIDATION,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $sub_array[] = $LIQUIDATION;
      $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

      $action .="<li>
                  <a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
                 </li>";

      $action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 7) ? "
        <li>
          <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='green'>Correction</font></label></a>
        </li>" : "";

      $action .="
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <center>
            <h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
            </h5>
          </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                              ".lang('messages_lang.quiter_action')."
          </button>
          <a href='".base_url("double_commande_new/Menu_Engagement_Juridique/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."
          </a>
        </div>";

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

  //exporter Liste EXCEL des liquidations déja faites
	function exporter_Excel_deja_fait($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
	{

		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_user_id='';
		if($profil_id!=1)
		{
			$cond_user_id=' AND histo.USER_ID='.$user_id;
		}

		// $critere=' AND ptba_tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		$critere='';

		if(!empty($INSTITUTION_ID))
		{
			$critere.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere.=' AND execdet.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$critere.=' AND execdet.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'" AND execdet.DATE_LIQUIDATION <= "'.$DATE_FIN.'"';
		}
		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

		$group = "";
		$requetedebase= "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION LIQUIDATION,execdet.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID = titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>10 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.  EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1  ".$cond_user_id.") ".$critere.$group;

	  $query_secondaire = "CALL `getTable`('" . $requetedebase . "');";
	  $fetch_data = $this->ModelPs->getRequete($query_secondaire);

	  $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('B1', 'REPUBLIQUE DU BURUNDI');
    $sheet->setCellValue('B2', 'MINISTERE DES FINANCES, DU BUDGET ET DA LA PLANIFICATION ECONOMIQUE');
    $sheet->setCellValue('B3', 'EXECUTION DEJA LIQUIDEE');

    $sheet->setCellValue('A5', '#');
    $sheet->setCellValue('B5', 'BON ENGAGEMENT');
    $sheet->setCellValue('C5', 'IMPUTATION');
    // $sheet->setCellValue('D5', 'ACTIVITE');
    $sheet->setCellValue('D5', 'TACHE');
    // $sheet->setCellValue('F5', 'OBJET ENGAGEMENT');
    $sheet->setCellValue('E5', 'DEVISE');
    $sheet->setCellValue('F5', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G5', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('H5', 'LIQUIDATION');
          
    $rows = 6;
    $i=1;
    foreach ($fetch_data as $row)
    {
    	//get les taches
    	$get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    	$get_task = 'CALL `getTable`("'.$get_task.'");';
    	$tasks = $this->ModelPs->getRequete($get_task);
    	$task_items = '';

    	foreach ($tasks as $task) {
    		$task_items .= "- ".$task->DESC_TACHE . "\n";
    	}

    	$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
    	$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
    	$MONTANT_LIQUIDATION=number_format(floatval($row->LIQUIDATION),0,',',' ');
    	if($row->DEVISE_TYPE_ID!=1)
    	{
    		$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
    		$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
    		$MONTANT_LIQUIDATION=number_format(floatval($row->LIQUIDATION_DEVISE),4,',',' ');
    	}
    	$sheet->setCellValue('A' . $rows, $i);
    	$sheet->setCellValue('B' . $rows, $row->NUMERO_BON_ENGAGEMENT);
    	$sheet->setCellValue('C' . $rows, $row->CODE_NOMENCLATURE_BUDGETAIRE);
    	// $sheet->setCellValue('D' . $rows, $row->DESC_PAP_ACTIVITE);
    	$sheet->setCellValue('D' . $rows, trim($task_items));
    	$sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
    	// $sheet->setCellValue('F' . $rows, $row->COMMENTAIRE);
    	$sheet->setCellValue('E' . $rows, $row->DESC_DEVISE_TYPE);
    	$sheet->setCellValue('F' . $rows, $ENG_BUDGETAIRE);
    	$sheet->setCellValue('G' . $rows, $ENG_JURIDIQUE);
    	$sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION);

    	$rows++;
    	$i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Liqu_deja_faits'.$code.'.xlsx');

    return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_deja_fait');
	}

  //exporter Liste EXCEL des liquidations validées
	function exporter_deja_valider_excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
	{

		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_user_id='';
		if($profil_id!=1)
		{
			$cond_user_id=' AND histo.USER_ID='.$user_id;
		}

		// $critere=' AND ptba_tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		$critere='';

		if(!empty($INSTITUTION_ID))
		{
			$critere.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere.=' AND det.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$critere.=' AND det.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'" AND det.DATE_LIQUIDATION <= "'.$DATE_FIN.'"';
		}
		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

		$group = "";
		$requetedebase= "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_DEMANDE FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 12 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.") ".$critere.$group;

		  $query_secondaire = "CALL `getTable`('" . $requetedebase . "');";
		  $fetch_data = $this->ModelPs->getRequete($query_secondaire);

		  $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('B1', 'REPUBLIQUE DU BURUNDI');
			$sheet->setCellValue('B2', 'MINISTERE DES FINANCES, DU BUDGET ET DA LA PLANIFICATION ECONOMIQUE');
			$sheet->setCellValue('B3', 'EXECUTION DEJA LIQUIDEE');

	          $sheet->setCellValue('A5', '#');
	          $sheet->setCellValue('B5', 'BON ENGAGEMENT');
	          $sheet->setCellValue('C5', 'IMPUTATION');
	          // $sheet->setCellValue('D5', 'ACTIVITE');
	          $sheet->setCellValue('D5', 'TACHE');
	          // $sheet->setCellValue('F5', 'OBJET ENGAGEMENT');
	          $sheet->setCellValue('E5', 'DEVISE');
	          $sheet->setCellValue('F5', 'ENGAGEMENT BUDGETAIRE');
	          $sheet->setCellValue('G5', 'ENGAGEMENT JURIDIQUE');
	          $sheet->setCellValue('H5', 'LIQUIDATION');
          
	        $rows = 6;
	        $i=1;
	        foreach ($fetch_data as $row)
	        {
	        	//get les taches
	        	$get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	        	$get_task = 'CALL `getTable`("'.$get_task.'");';
	        	$tasks = $this->ModelPs->getRequete($get_task);
	        	$task_items = '';

	        	foreach ($tasks as $task) {
	        		$task_items .= "- ".$task->DESC_TACHE . "\n";
	        	}

	        	$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
	        	$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
	        	$MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION),0,',',' ');
	        	if($row->DEVISE_TYPE_ID!=1)
	        	{
	        		$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
	        		$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
	        		$MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION_DEVISE),4,',',' ');
	        	}
	        	$sheet->setCellValue('A' . $rows, $i);
	        	$sheet->setCellValue('B' . $rows, $row->NUMERO_BON_ENGAGEMENT);
	        	$sheet->setCellValue('C' . $rows, $row->CODE_NOMENCLATURE_BUDGETAIRE);
	        	// $sheet->setCellValue('D' . $rows, $row->DESC_PAP_ACTIVITE);
	        	$sheet->setCellValue('D' . $rows, trim($task_items));
	        	$sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
	        	// $sheet->setCellValue('F' . $rows, $row->COMMENTAIRE);
	        	$sheet->setCellValue('E' . $rows, $row->DESC_DEVISE_TYPE);
	        	$sheet->setCellValue('F' . $rows, $ENG_BUDGETAIRE);
	        	$sheet->setCellValue('G' . $rows, $ENG_JURIDIQUE);
	        	$sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION);

	        	$rows++;
	        	$i++;
	        } 

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('Liqu_deja_valid'.$code.'.xlsx');

      return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_valider');
	}

	// Exporter pdf
	function exporter_deja_valider($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$cond_user_id='';
		if($profil_id!=1)
		{
			$cond_user_id=' AND histo.USER_ID='.$user_id;
		}

		// $critere=' AND ptba_tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		$critere='';

		if(!empty($INSTITUTION_ID))
		{
			$critere.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		if(!empty($ANNEE_BUDGETAIRE_ID))
		{
			$critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
		}

		if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
		{
			$critere.=' AND det.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'"';
		}

		if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
		{
			$critere.=' AND det.DATE_LIQUIDATION >= "'.$DATE_DEBUT.'" AND det.DATE_LIQUIDATION <= "'.$DATE_FIN.'"';
		}

		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
		$getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

		$ID_INST='';
		foreach ($getaffect as $value)
		{
			$ID_INST.=$value->INSTITUTION_ID.' ,';           
		}
		$ID_INST = substr($ID_INST,0,-1);

		$group = "";
		$requetedebase= "SELECT DISTINCT(titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,titre.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_LIQUIDATION,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 12 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.") ".$critere.$group;

		$query_secondaire = "CALL `getTable`('" . $requetedebase . "');";
		$fetch_data = $this->ModelPs->getRequete($query_secondaire);

		$dompdf = new Dompdf();
		$tableWidth = '100%';
		$html = "<html>";
		$html.= "<body>";
		$html.= "<div><strong>REPUBLIQUE DU BURUNDI</strong></div>";
		$html.= "<div><strong>MINISTERE DES FINANCES, DU BUDGET ET DA LA PLANIFICATION ECONOMIQUE</strong></div><br>";
		$html.= "<div><center><strong>EXECUTION DEJA LIQUIDEE</strong></center></div><br>";
		$html.= '<table style="border-collapse: collapse;margin-left:-15px; width: '.$tableWidth.'"  font-size: 12px; border="1">';
		$html.='<tr>
		<th style="border: 1px solid #000; width: 5%;font-size:12px ">Nº</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">Nº&nbsp;BE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">IMPUTATION</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">TACHE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">DEVISE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">ENGAGEMENT BUDGETAIRE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">ENGAGEMENT JURIDIQUE</th>
		<th style="border: 1px solid #000; width: 10%;font-size:12px ">LIQUIDATION</th>
		</tr>';

		$a=1;		
		foreach ($fetch_data as $row)
		{
			//get les taches
			$get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
			$get_task = 'CALL `getTable`("'.$get_task.'");';
			$tasks = $this->ModelPs->getRequete($get_task);
			$task_items = '';

			foreach ($tasks as $task) {
				$task_items .= '<li style="margin-left:+10px">'.$task->DESC_TACHE.'</li>';
			}
			$TACHE=empty($task_items) ? '<center>-</center>' : $task_items;

			$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
			$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
			$MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION),0,',',' ');
			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
				$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
				$MONTANT_LIQUIDATION=number_format(floatval($row->MONTANT_LIQUIDATION_DEVISE),4,',',' ');
			}
			$html.='<tr>
			<td style="border: 1px solid #000; width: 5%;font-size:12px ">'.$a++.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$row->NUMERO_BON_ENGAGEMENT.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$TACHE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$row->DESC_DEVISE_TYPE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$ENG_BUDGETAIRE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$ENG_JURIDIQUE.'</td>
			<td style="border: 1px solid #000; width: 10%;font-size:12px ">'.$MONTANT_LIQUIDATION.'</td>
			</tr>';
		}

		$html.='</table>';
		$html.= "</body>";		
		$html.='</html>';
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		// Envoyer le fichier PDF en tant que téléchargement
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="Executions deja liquide.pdf"');
		echo $dompdf->output();
	}

	//fonction pour la correction
  function is_correction($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
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

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_detail_etape = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_titre_decaissement', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    $etape= $this->ModelPs->getRequeteOne($callpsreq, $bind_detail_etape);

   $datatoupdate='';

    if($etape['ETAPE_DOUBLE_COMMANDE_ID']== 11)
    {
      $datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=12';
    }
    else{
      return redirect('Login_Ptba/homepage');
    }

    $updateTable='execution_budgetaire_titre_decaissement';
    $critere = " EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
     $this->update_all_table($updateTable,$datatoupdate,$critere);
    $url = 'double_commande_new/Liquidation_Double_Commande/get_liquid_Acorriger';
      return redirect($url);
  }

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  //get les detail des taches
  function detail_task()
  {
    $session  = \Config\Services::session();

    $task_id = $this->request->getPost('task_id');

      //Filtres de la liste
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";

    $critere3="";

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';

    $requetedebase="SELECT DISTINCT(task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID),
                           task.EXECUTION_BUDGETAIRE_ID,
                           task.PTBA_TACHE_ID,
                           task.MONTANT_ENG_BUDGETAIRE,
                           task.MONTANT_ENG_BUDGETAIRE_DEVISE,
                           task.MONTANT_ENG_JURIDIQUE,
                           task.MONTANT_ENG_JURIDIQUE_DEVISE,
                           task.MONTANT_LIQUIDATION,
                           task.MONTANT_LIQUIDATION_DEVISE,
                           ptba.DESC_TACHE,
                           task.QTE,
                           dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    FROM execution_budgetaire_execution_tache task 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID 
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID
                    WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$task_id."";

    $order_column=array('ptba.DESC_TACHE','task.COMMENTAIRE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.COMMENTAIRE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3;

      // print_r($critaire);die();
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter=$critaire." ".$search;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    { 

      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
      $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");

      $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE);
      $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,0,","," ");

      $LIQUIDATION=floatval($row->MONTANT_LIQUIDATION);
      $LIQUIDATION=number_format($LIQUIDATION,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");

        $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE_DEVISE);
        $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,4,","," ");

        $LIQUIDATION=floatval($row->MONTANT_LIQUIDATION_DEVISE);
        $LIQUIDATION=number_format($LIQUIDATION,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
      $sub_array[] = $MONTANT_ENG_JURIDIQUE;
      if($row->ETAPE_DOUBLE_COMMANDE_ID > 10){$sub_array[] = $LIQUIDATION;}
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
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
