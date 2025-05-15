<?php
/**NDERAGAKURA Alain Charbel
*Titre: ordonnancement
*Numero de telephone: +257 62 003 522/+257 76 887 837
*Email pro: charbel@mediabox.bi
*Date: 15-juillet-2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use Config\Services;

class Ordonna_Dir_Budg_Vers_Ced extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function index($value='')
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id ='';
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($psgetrequete, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
    $getInst = "CALL getTable('" .$getInst. "');";
    $data['institutions_user'] = $this->ModelPs->getRequete($getInst);

    $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
	    $user_inst_res = 'CALL getTable("'.$user_inst.'");';
	    $institutions_user = $this->ModelPs->getRequete($user_inst_res);

	    $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
	    $SOUS_TUTEL_ID = 0;
	    $DU = 0;
	    $AU = 0;

		$data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU);
    $data['institutions_user']=$data_menu['institutions_user'];
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
    $data['get_bord_spe']=$data_menu['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
    $data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
    $data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
    $data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];
    $data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];

    $data['institutions_user']=$institutions_user;
		$data['first_element_id'] = $INSTITUTION_ID;
		return view('App\Modules\double_commande_new\Views\Ordonna_Dir_Budg_Vers_Ced_List',$data);   
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

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

		$cond_profil="";
    $cond_user="";
    if ($profil_id!=1) 
    {
      $cond_profil=" AND prof.PROFIL_ID=".$profil_id;
      $cond_user=" AND histo.USER_ID=".$user_id;
    }

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		// $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		$group = "";
		$critere = "";
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
		if(!empty($INSTITUTION_ID))
    {
      $critere=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID))
      {
        $critere.=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }
    }
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE' , 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_DETAIL_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),
														exec.EXECUTION_BUDGETAIRE_ID,
														exec.NUMERO_BON_ENGAGEMENT,
														exec.ENG_BUDGETAIRE,
														exec.ENG_BUDGETAIRE_DEVISE,
														exec.ENG_JURIDIQUE,
														exec.ENG_JURIDIQUE_DEVISE,
														exec.LIQUIDATION,
														exec.LIQUIDATION_DEVISE,
														exec.ORDONNANCEMENT,
														exec.ORDONNANCEMENT_DEVISE,
														ligne.CODE_NOMENCLATURE_BUDGETAIRE,
														ebtd.ETAPE_DOUBLE_COMMANDE_ID,
														det.EXECUTION_BUDGETAIRE_DETAIL_ID,
														dev.DESC_DEVISE_TYPE,
														dev.DEVISE_TYPE_ID 
										 FROM execution_budgetaire_titre_decaissement ebtd 
                     JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID 
										 JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID 
										 LEFT JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
										 JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID 
										 LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID 
										 JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID 
										 JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID 
										 JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
										 JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
										 WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=36 ".$cond_profil;

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

			$ORDONNANCEMENT=floatval($row->ORDONNANCEMENT);
			$ORDONNANCEMENT=number_format($ORDONNANCEMENT,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");

				$ORDONNANCEMENT=floatval($row->ORDONNANCEMENT_DEVISE);
				$ORDONNANCEMENT=number_format($ORDONNANCEMENT,4,","," ");
			}
			
			$sub_array = array();
			$sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$sub_array[] = $ORDONNANCEMENT;
			$sub_array[]="<a class='btn btn-primary btn-sm' title='detail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";

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

	public function add()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $etape_actuel=36;
  	$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
  	$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
  	{
  		foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
        	$psgetrequete = "CALL `getRequete`(?,?,?,?);";
					$info = $this->getBindParms('ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID,NUMERO_BON_ENGAGEMENT,ebtd.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID  JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande etape ON etape.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID', 'ebtd.ETAPE_DOUBLE_COMMANDE_ID ='. $etape_actuel, 'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
					$data['get_info'] = $this->ModelPs->getRequete($psgetrequete, $info);
					$data['titre']="Transmission des bons d'engagement à corriger";
					$data['etape']=$etape_actuel;
					return view('App\Modules\double_commande_new\Views\Ordonna_Dir_Budg_Vers_Ced_Add_View',$data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }		
	}	

	public function save()
	{
		$data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) 
    {
      return redirect('Login_Ptba/do_logout');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $id_etape = $this->request->getPost('id_etape');
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="'.$id_etape.'"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
    $rules = [
      'DATE_RECEPTION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      
      'DATE_TRANSMISSION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      
      'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      
      if (!empty($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))
      {
        foreach ($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID as $value)
        {
          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $value;
          $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID="'.$next_etape_data.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);

          //insertion dans l'historique
          $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $data_histo = $value . ',' . $id_etape . ',' . $user_id . ',"' . $DATE_RECEPTION . '","' . $DATE_TRANSMISSION . '"';
          $tablehist="execution_budgetaire_tache_detail_histo";
          $this->save_all_table($tablehist,$column_histo, $data_histo);

          //Soustraction du montant d'ordonnancement avant le retour
		    	$callpsreq = "CALL getRequete(?,?,?,?);";          
		      $bindparamss =$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID','ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $value .'','det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
		      $bindparams = str_replace("\\", "", $bindparamss);
		      $get_mont_ordo = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		      $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$get_mont_ordo['EXECUTION_BUDGETAIRE_ID'];

          if($get_mont_ordo['EXEC_ORDONNANCEMENT'] > 0)
          {
            //print_r($get_mont_ordo);exit();
            if($get_mont_ordo['DEVISE_TYPE_ID'] != 1)
            {
              $update_pay_mont = floatval($get_mont_ordo['EXEC_ORDONNANCEMENT']) - floatval($get_mont_ordo['MONTANT_ORDONNANCEMENT']);
              $update_pay_mont_devise = floatval($get_mont_ordo['EXEC_ORDONNANCEMENT_DEVISE']) - floatval($get_mont_ordo['MONTANT_ORDONNANCEMENT_DEVISE']);

              $datatomodifie_exec = 'ORDONNANCEMENT='.$update_pay_mont.', ORDONNANCEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              $update_pay_mont = $get_mont_ordo['EXEC_ORDONNANCEMENT'] - $get_mont_ordo['MONTANT_ORDONNANCEMENT'];
              $datatomodifie_exec = 'ORDONNANCEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
              
            }
          }
        }
      }
      return redirect('double_commande_new/Ordonna_Dir_Budg_Vers_Ced/liste');
    }
    else
    {
      return $this->add();
    }
	}

	//récupération du sous titre par rapport à l'institution
  function get_sous_titre($INSTITUTION_ID=0)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID='.$INSTITUTION_ID.' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

    $html='<option value="">Sélectionner</option>';
    foreach ($sous_tutelle as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output = array(
      "sous_tutel" => $html,
    );

    return $this->response->setJSON($output);
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