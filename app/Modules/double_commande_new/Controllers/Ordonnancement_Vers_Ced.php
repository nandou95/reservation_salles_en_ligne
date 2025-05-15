<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Liquidation
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 25 oct 2023
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use Dompdf\Dompdf;

class Ordonnancement_Vers_Ced extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		define("DOMPDF_ENABLE_REMOTE", true);
	}

	public function index($value='')
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
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
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Vers_Ced_List',$data);   
	}

	public function add($id=0)
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";		

		//get info
		$info = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,LIQUIDATION_TYPE_ID,exec.EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', '1');
		$info=str_replace("\\", "", $info);
		$data['info'] = $this->ModelPs->getRequeteOne($psgetrequete, $info);

		$getliqui="SELECT COUNT(det.EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbr FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE det.EXECUTION_BUDGETAIRE_ID =".$data['info']['EXECUTION_BUDGETAIRE_ID']." AND td.ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13,40,41,42)";
		$getliqui = "CALL `getTable`('".$getliqui."');";
		$getliqui= $this->ModelPs->getRequeteOne($getliqui);

		$etape ="";
		if($data['info']['LIQUIDATION_TYPE_ID']==1 && $getliqui['nbr']>1)
		{
			$etape = $this->getBindParms('DESCRIPTION_ETAPE_RETOUR,ETAPE_RETOUR_CORRECTION_ID', 'budgetaire_etape_retour_correction', 'ETAPE_RETOUR_CORRECTION_ID=4', 'ETAPE_RETOUR_CORRECTION_ID ASC');
		}
		else
		{
			$etape = $this->getBindParms('DESCRIPTION_ETAPE_RETOUR,ETAPE_RETOUR_CORRECTION_ID', 'budgetaire_etape_retour_correction', 'ETAPE_RETOUR_CORRECTION_ID>1 AND ETAPE_RETOUR_CORRECTION_ID<5', 'ETAPE_RETOUR_CORRECTION_ID ASC');
		}

		$data['etape'] = $this->ModelPs->getRequete($psgetrequete, $etape);

		//get historique
		$histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'DATE_INSERTION DESC');
		$histo=str_replace("\\", "", $histo);
		$data['histo'] = $this->ModelPs->getRequeteOne($psgetrequete, $histo);

		$detail = $this->detail_new($id);
		$data['get_info']=$detail['get_info'];
        $data['montantvote']=$detail['montantvote'];
        $data['get_infoEBET']=$detail['get_infoEBET']; 
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Vers_Ced_View',$data);   
	}

	public function listing($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

		$critere1="";
		$critere2=" ";
		if(!empty($INSTITUTION_ID))
		{
			$critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
		}

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = " GROUP BY titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$critere = $critere1." ".$critere2;
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE' , 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR dev.DESC_DEVISE_TYPE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION LIQUIDATION,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=31 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution;

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
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			//$step='Ordonnancement_Vers_Ced/Corrige_From_Ordo';
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
			}

			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='Transmettre au GDC' style='color:#fbbf25;' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
					$bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/Ordonnancement_Vers_Ced/Corrige_From_Ordo/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
				}
			}
			
			//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
			$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
			$LIQUIDATION=number_format(floatval($row->LIQUIDATION),0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
			  $ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
			  $ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
			  $LIQUIDATION=number_format(floatval($row->LIQUIDATION_DEVISE),4,","," ");
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

	public function listing_old($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

    $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

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
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE' , 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR dev.DESC_DEVISE_TYPE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,det.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=31 ".$institution."";

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
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
			$dist="";
			if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
			{
				if($row->ETAPE_DOUBLE_COMMANDE_ID==10) $dist="/1";
			}

			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE `ETAPE_DOUBLE_COMMANDE_ID`='.$row->ETAPE_DOUBLE_COMMANDE_ID;
			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  title='Transmettre au GDC' style='color:#fbbf25;' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}
			
			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->DESC_PAP_ACTIVITE."'><i class='fa fa-eye'></i></a>") : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->DESC_TACHE."'><i class='fa fa-eye'></i></a>") : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."'><i class='fa fa-eye'></i></a>") : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 3) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->COMMENTAIRE."'><i class='fa fa-eye'></i></a>") : $row->COMMENTAIRE;

			$ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE),0,","," ");
			$ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE),0,","," ");
			if($row->DEVISE_TYPE_ID!=1)
			{
			  $ENG_BUDGETAIRE=number_format(floatval($row->ENG_BUDGETAIRE_DEVISE),4,","," ");
			  $ENG_JURIDIQUE=number_format(floatval($row->ENG_JURIDIQUE_DEVISE),4,","," ");
			} 

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
      $sub_array[]=$DESC_PAP_ACTIVITE;       
      $sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$row->DESC_DEVISE_TYPE;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='detail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>".' '."<a class='btn btn-primary btn-sm' title='corriger' href='".base_url('double_commande_new/Ordonnancement_Vers_Ced/Corrige_From_Ordo/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-edit'></span></a>";

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

	public function save()
	{
		$db = db_connect();
    $session  = \Config\Services::session();
    $USER_ID ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $rules = [
      'DATE_RECEPTION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
        ]
      ],
      'ETAPE_CORRIGE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
        ]
      ],
      'DATE_TRANSMISSION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
        ]
      ]
    ];
    $this->validation->setRules($rules);

    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
    $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
		$ETAPE_CORRIGE=$this->request->getPost('ETAPE_CORRIGE');
		$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
		$ETAPE_ACTUELLE=$this->request->getPost('ETAPE_ACTUELLE');
		$EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    if($this->validation->withRequest($this->request)->run())
    {			
			$ETAPE_SUIVANTE='';
			//get etape suivante
			$psgetrequete= "CALL `getRequete`(?,?,?,?);";
			if($ETAPE_CORRIGE == 2)
      {
      	$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      }
      elseif($ETAPE_CORRIGE == 3)
      {
      	$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      }
      elseif($ETAPE_CORRIGE == 4)
      {
      	//récuperer les etapes et mouvements
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=3','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $get_exec = $this->getBindParms('LIQUIDATION_TYPE_ID,LIQUIDATION,LIQUIDATION_DEVISE,DEVISE_TYPE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID ASC');
		    $get_exec = $this->ModelPs->getRequeteOne($psgetrequete, $get_exec);


			  $MONT_DEVISE=$get_exec['LIQUIDATION_DEVISE']-$get_exec['MONTANT_LIQUIDATION_DEVISE'];
    		$MONT=floatval($get_exec['LIQUIDATION'])-floatval($get_exec['MONTANT_LIQUIDATION']);
    		$table = 'execution_budgetaire';
  			$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
  			$data='LIQUIDATION='.$MONT.',LIQUIDATION_DEVISE='.$MONT_DEVISE;
  			$this->update_all_table($table,$data,$where);
      }

      $table3='execution_budgetaire_titre_decaissement';
      $conditions3='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $datatomodifie3= 'ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_SUIVANTE;
      $result=$this->update_all_table($table3,$datatomodifie3,$conditions3);

      if($ETAPE_CORRIGE == 2) 
      {
      	$this->gestion_rejet_ptba($EXECUTION_BUDGETAIRE_ID);
      }    
      //recuperation des dernieres observations 
			$bindparams=$this->getBindParms('OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'','EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
			$observ = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
			$OBSERVATION =$observ['OBSERVATION'];

      //Enregistrement dans historique
      $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION,OBSERVATION";
      $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ACTUELLE.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".addslashes($OBSERVATION)."'";
      $table_histo='execution_budgetaire_tache_detail_histo';
      $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);  

      //recuperation des derniers motif de retour a la correction
      $motif_rejet  = 'SELECT DISTINCT TYPE_ANALYSE_MOTIF_ID FROM execution_budgetaire_histo_operation_verification_motif motif  WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.' AND ETAPE_DOUBLE_COMMANDE_ID='.$observ['ETAPE_ID'].'';
			$motif_rejetRqt = "CALL getTable('" . $motif_rejet . "');";
			$motif_rejet= $this->ModelPs->getRequete($motif_rejetRqt);

			//insertion des derniers motifs
			foreach ($motif_rejet as $value) 
			{
				$TYPE_ANALYSE_MOTIF_ID=$value->TYPE_ANALYSE_MOTIF_ID;

				$columsinsert="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
	      $datatoinsert="".$TYPE_ANALYSE_MOTIF_ID.",".$ETAPE_ACTUELLE.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
	      $this->save_all_table('execution_budgetaire_histo_operation_verification_motif',$columsinsert,$datatoinsert); 
			}

      $data=['message' => "".lang('messages_lang.eng_succ').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Ordonnancement_Vers_Ced/liste');      
    }
    else
    {
    	return $this->add($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
    }
	}

	//appel au view pour corriger ced
	public function corrige_ced($titre_dec_id=0)
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		//get data etape
		$etape = $this->getBindParms('DESCRIPTION_ETAPE_RETOUR,ETAPE_RETOUR_CORRECTION_ID', 'budgetaire_etape_retour_correction', 'ETAPE_RETOUR_CORRECTION_ID>1', 'ETAPE_RETOUR_CORRECTION_ID ASC');
		$data['etape'] = $this->ModelPs->getRequete($psgetrequete, $etape);

		//get info
		$info = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_titre_decaissement', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$titre_dec_id.'"', '1');
		$info=str_replace("\\", "", $info);
		$data['info'] = $this->ModelPs->getRequeteOne($psgetrequete, $info);

		//get historique
		$histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$titre_dec_id.'"', 'DATE_INSERTION DESC');
		$histo=str_replace("\\", "", $histo);
		$data['histo'] = $this->ModelPs->getRequeteOne($psgetrequete, $histo);

		$detail = $this->detail_new(MD5($data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID']));
    $data['get_info']=$detail['get_info'];
    $data['montantvote']=$detail['montantvote'];
    $data['creditVote']=$detail['creditVote'];
    $data['montant_reserve']=$detail['montant_reserve'];
		return view('App\Modules\double_commande_new\Views\Corrige_From_Ordo',$data);   
	}

	public function corrige_ced_old($detail_id=0)
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";

		//get data etape
		$etape = $this->getBindParms('DESCRIPTION_ETAPE_RETOUR,ETAPE_RETOUR_CORRECTION_ID', 'budgetaire_etape_retour_correction', 'ETAPE_RETOUR_CORRECTION_ID>1', 'ETAPE_RETOUR_CORRECTION_ID ASC');
		$data['etape'] = $this->ModelPs->getRequete($psgetrequete, $etape);

		//get info
		$info = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$detail_id.'"', '1');
		$info=str_replace("\\", "", $info);
		$data['info'] = $this->ModelPs->getRequeteOne($psgetrequete, $info);

		//get historique
		$histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$detail_id.'"', 'DATE_INSERTION DESC');
		$histo=str_replace("\\", "", $histo);
		$data['histo'] = $this->ModelPs->getRequeteOne($psgetrequete, $histo);

		$detail = $this->detail_new($detail_id);
    $data['get_info']=$detail['get_info'];
    $data['montantvote']=$detail['montantvote'];
    $data['creditVote']=$detail['creditVote'];
    $data['montant_reserve']=$detail['montant_reserve'];
		return view('App\Modules\double_commande_new\Views\Corrige_From_Ordo',$data);   
	}

	public function save_corrige_ced()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$USER_ID ='';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			],
			'OBSERVATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			]
		];
		$this->validation->setRules($rules);

		$DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
		$OBSERVATION=$this->request->getPost('OBSERVATION');
		$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
		$ETAPE_ACTUELLE=$this->request->getPost('ETAPE_ACTUELLE');
		$EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
		$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
		if($this->validation->withRequest($this->request)->run())
		{
			//get etape precedente
			$psgetrequete= "CALL `getRequete`(?,?,?,?);";
			$Montant_liqui = $this->getBindParms('MONTANT_LIQUIDATION','execution_budgetaire_tache_detail','EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
			$Montant_liqui = $this->ModelPs->getRequeteOne($psgetrequete, $Montant_liqui);
			$Montant_liqui = $Montant_liqui['MONTANT_LIQUIDATION'];

			$ETAPE_SUIVANTE="";
			if ($Montant_liqui<500000000)
			{
    		// print_r($ETAPE_ACTUELLE);die();
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ACTUELLE.' AND CONTRAINTE_MONTANT_ID=0 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
		    	$ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
		    }
		    else
		    {
		  		//récuperer les etapes et mouvements
		    	$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ACTUELLE.' AND EST_SUPERIEUR_CENT_MILLION=2 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
		    	$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
	        	$ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
	        }

	        $table3='execution_budgetaire_titre_decaissement';
	        $conditions3='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	        $datatomodifie3= 'ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_SUIVANTE;
	        $result=$this->update_all_table($table3,$datatomodifie3,$conditions3);

      //Enregistrement dans historique
	        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION,OBSERVATION";
	        $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ACTUELLE.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".addslashes($OBSERVATION)."'";
	        $table_histo='execution_budgetaire_tache_detail_histo';
	        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);  

	        $data=['message' => "".lang('messages_lang.eng_succ').""];
	        session()->setFlashdata('alert', $data);

	        return redirect('double_commande_new/Ordonnancement_Vers_Ced/liste');      
	    }
	    else
	    {
	    	return $this->corrige_ced(MD5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
	    }
	}

	public function save_corrige_ced_old()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$USER_ID ='';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			],
			'OBSERVATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.validation_message').'</font>'
				]
			]
		];
		$this->validation->setRules($rules);

		$DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
		$OBSERVATION=$this->request->getPost('OBSERVATION');
		$DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
		$ETAPE_ACTUELLE=$this->request->getPost('ETAPE_ACTUELLE');
		$EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
		if($this->validation->withRequest($this->request)->run())
		{
			//get etape precedente
			$psgetrequete= "CALL `getRequete`(?,?,?,?);";
			$Montant_liqui = $this->getBindParms('MONTANT_LIQUIDATION','execution_budgetaire_tache_detail','EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
			$Montant_liqui = $this->ModelPs->getRequeteOne($psgetrequete, $Montant_liqui);
			$Montant_liqui = $Montant_liqui['MONTANT_LIQUIDATION'];

			$ETAPE_SUIVANTE="";
			if ($Montant_liqui<500000000)
			{
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ACTUELLE.' AND CONTRAINTE_MONTANT_ID=0 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
		    	$ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
		    }
		    else
		    {
		  		//récuperer les etapes et mouvements
		    	$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ACTUELLE.' AND EST_SUPERIEUR_CENT_MILLION=2 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
			    	$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
		        $ETAPE_SUIVANTE = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
		    }
	    
		    	// $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"EXECUTION_BUDGETAIRE_DETAIL_ID='".$EXECUTION_BUDGETAIRE_DETAIL_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
		      // $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
		      // $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

		    $table3='execution_budgetaire_tache_detail';
		    $conditions3='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
		    $datatomodifie3= 'ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_SUIVANTE;
		    $result=$this->update_all_table($table3,$datatomodifie3,$conditions3);
		    
		      //Enregistrement dans historique
		    $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION,OBSERVATION";
		    $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$USER_ID.",".$ETAPE_ACTUELLE.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".addslashes($OBSERVATION)."'";
		    $table_histo='execution_budgetaire_tache_detail_histo';
		    $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);  

		    $data=['message' => "".lang('messages_lang.eng_succ').""];
		    session()->setFlashdata('alert', $data);

		    return redirect('double_commande_new/Ordonnancement_Vers_Ced/liste');      
		}
		else
		{
			return $this->corrige_ced($EXECUTION_BUDGETAIRE_DETAIL_ID);
		}
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
	        // $columsinsert: Nom des colonnes separe par,
	        // $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}
}