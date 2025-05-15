<?php

/**  Developpe par
 *Alain charbel Nderagakura
 *Titre: Transmission des bons
 *Numero de telephone: +257 62 003 522
 *WhatsApp: +257 62 04 03 00
 *Email pro: charbel@mediabox.bi
 *Date: 11 juillet 2024
 **/

namespace  App\Modules\double_commande_new\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Ordonnancement_Ministre extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function index()
	{
		$data=$this->urichk();
		$session=\Config\Services::session();
		$callpsreq="CALL `getRequete`(?,?,?,?);";

		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		//------------Recuperation de detail id
		$etape_actuel=34;
		$prof_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_profil=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach($getProfil as $value)
      {
        if($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data_menu=$this->getDataMenuOrdonnancement();
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
          return view('App\Modules\double_commande_new\Views\Transmission_Au_Ministre_Liste_View',$data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function listing ()
  {
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    // $group = " GROUP BY ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    $group = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) 
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $etape_actuel=34;

    $order_by='';
    $order_column=['exec.NUMERO_BON_ENGAGEMENT','det.MONTANT_LIQUIDATION','inst.DESCRIPTION_INSTITUTION','dev.DESC_DEVISE_TYPE',1];
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';
    $conditions =$search. " " . $group." ".$order_by." ".$limit;
    $conditionsfilter =$search. " " . $group;
    $requetedebase="SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           exec.NUMERO_BON_ENGAGEMENT,
                           det.MONTANT_LIQUIDATION,
                           det.MONTANT_LIQUIDATION_DEVISE,
                           inst.DESCRIPTION_INSTITUTION 
                    FROM execution_budgetaire_titre_decaissement ebtd 
                    JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID  
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID 
                    LEFT JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID = det.DEVISE_TYPE_HISTO_LIQUI_ID 
                    JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID 
                    WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=".$etape_actuel;
    $requetedebases=$requetedebase." ".$conditions;
    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire='CALL `getTable`("'.$requetedebases.'");';
    $fetch_actions=$this->ModelPs->datatable($query_secondaire);
    $data=[];
    $u=1;
    foreach ($fetch_actions as $row) 
    {
      $dist = "";
      $sub_array = [];
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $row->DEVISE_TYPE_ID == 1 ? number_format($row->MONTANT_LIQUIDATION, 4, ',', ' ') : number_format($row->MONTANT_LIQUIDATION_DEVISE, 4, ',', ' ');
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
      $data[] = $sub_array;
    }
    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  /* Debut Gestion update table de la demande detail*/
  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /* Fin Gestion update table de la demande detail*/

  /* Debut Gestion insertion */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }

  public function add()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $etape_actuel=34;
    $user_profil=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if(!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
         $psgetrequete = "CALL `getRequete`(?,?,?,?);";
         $info = $this->getBindParms('ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID,NUMERO_BON_ENGAGEMENT,ebtd.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande etape ON etape.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID', 'ebtd.ETAPE_DOUBLE_COMMANDE_ID ='. $etape_actuel, 'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
         $data['get_info'] = $this->ModelPs->getRequete($psgetrequete, $info);
         $data['titre']="Transmission des bons d'engagement au Cabinet";
         $data['etape']=$etape_actuel;
         return view('App\Modules\double_commande_new\Views\Transmission_Au_Ministre_Add_View', $data);
       }
     }
     return redirect('Login_Ptba/homepage');
   }
   else
   {
     return redirect('Login_Ptba/homepage');
   }	
  }

	//fonction pour inserer
  function save()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    /*if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }*/
    
    
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
    	$callpsreq = "CALL `getRequete`(?,?,?,?)";
      $id_etape = $this->request->getPost('id_etape');
      $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape,'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
      $etape_request = str_replace('\"', '"',$etape_request);
      $next_etape_data = $this->ModelPs->getRequeteOne($callpsreq,$etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
     
      if (!empty($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))
      {
        foreach ($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID as $value)
        {
          //GET EXECUTION_BUDGETAIRE_DETAIL_ID
          $req = "CALL getRequete(?,?,?,?);";
          $bind_det = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID','execution_budgetaire_titre_decaissement','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$value,'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $bind_det = str_replace('\"', '"',$bind_det);
          $res= $this->ModelPs->getRequeteOne($req, $bind_det);
          $EXECUTION_BUDGETAIRE_DETAIL_ID=$res['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          
          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$value;
          $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data;
          $this->update_all_table($table,$datatomodifie,$conditions);

            //insertion dans l'historique
          $column_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $data_histo=$value.','.$id_etape.','.$user_id.',"'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
          $tablehist="execution_budgetaire_tache_detail_histo";
          $this->save_all_table($tablehist,$column_histo, $data_histo);
        }
      }
      return redirect('double_commande_new/Ordonnancement_Ministre/liste');
    }
    else
    {
      return $this->add();
    }
  }
}
?>