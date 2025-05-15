<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Planification Strategique Sectorielle
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 22 11 2023
**/

namespace  App\Modules\process\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M'); 

class Planification_Strategique_Sectorielle extends BaseController
{
	protected $session;

	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

  //fonction get pour recuperer les données idmd5
  public function getOne($ID_DEMANDE='')
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $infosData = 'SELECT ID_DEMANDE FROM `proc_demandes` WHERE md5(ID_DEMANDE)="'.$ID_DEMANDE.'"';
    $infosData = "CALL `getTable`('" . $infosData . "');";
    $resultat= $this->ModelPs->getRequeteOne($infosData);
    if(empty($resultat))
    {
     return redirect('Login_Ptba/do_logout');
    }

    $id=$resultat['ID_DEMANDE'];
    if($id=='')
    {
      return redirect('Login_Ptba/do_logout');
    }

    //get ETAPE_ID
    $getEtape  = 'SELECT proc_demandes.ETAPE_ID FROM proc_demandes WHERE proc_demandes.ID_DEMANDE='.$id.'';
    $getEtape = "CALL `getTable`('" . $getEtape . "');";
    $getEtape = $this->ModelPs->getRequeteOne($getEtape);
    $ETAPE_ID = $getEtape['ETAPE_ID'];
    #############################################################################  #####################

    //Informations pour detailler les informations d'une demande
    $infoAffiche = 'SELECT proc_demandes.ID_DEMANDE,proc_demandes.CODE_DEMANDE,proc_demandes.DATE_INSERTION,proc_demandes.IS_END,proc_process.NOM_PROCESS,proc_etape.DESCR_ETAPE,user_users.NOM,user_users.PRENOM,user_profil.PROFIL_DESCR FROM proc_demandes JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID JOIN user_users ON user_users.USER_ID=proc_demandes.USER_ID JOIN proc_profil_etape ON proc_etape.ETAPE_ID=proc_profil_etape.ETAPE_ID JOIN user_profil ON user_profil.PROFIL_ID=proc_profil_etape.PROFIL_ID WHERE proc_demandes.ID_DEMANDE='.$id.'';
    $infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
    $data['infoAffiche'] = $this->ModelPs->getRequeteOne($infoAffiche);
    ###########################################################           ###############################  
        
    $PROFIL_ID = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    //get etape suivant et les actions AND proc_etape.PROFIL_ID='.$PROFIL_ID.'
    $getAction = 'SELECT proc_actions.ACTION_ID, proc_actions.ETAPE_ID, proc_actions.MOVETO,proc_actions.IS_REQUIRED, proc_actions.LINK_FORM, proc_actions.GET_FORM, proc_actions.DESCR_ACTION, proc_etape.DESCR_ETAPE, proc_demandes.PROCESS_ID FROM proc_actions JOIN proc_etape ON proc_etape.ETAPE_ID=proc_actions.ETAPE_ID JOIN proc_demandes ON proc_actions.ETAPE_ID=proc_demandes.ETAPE_ID WHERE proc_actions.ETAPE_ID='.$ETAPE_ID.' AND proc_demandes.ID_DEMANDE='.$id.'';//
      $getAction = "CALL `getTable`('" . $getAction . "');";
      $data['getAction'] = $this->ModelPs->getRequete($getAction);
    ##############################################################################
    return view('App\Modules\process\Views\Planification_Strategique_Sectorielle_Detail_View',$data);
  }

  /*Debut Gestion update table de la demande detail*/
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /*Fin Gestion update table de la demande detail*/

  /*Debut Gestion insertion */
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

  public function getDescriptionAction($value='')
  {  
    $session = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $ACTION_ID = $this->request->getPost('ACTION_ID');  
    //get etape suivant et les actions
    $getAction  = 'SELECT DESCR_ACTION FROM proc_actions WHERE ACTION_ID='.$ACTION_ID.'';
    $getAction = "CALL `getTable`('" . $getAction . "');";
    $getActionData = $this->ModelPs->getRequeteOne($getAction);
    $DESCR_ACTION = $getActionData['DESCR_ACTION'];
    $output = array('DESCR_ACTION'=>$DESCR_ACTION);
    return $this->response->setJSON($output);
  }

  //liste des historiques de traitement
  public function liste_historique()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND proc_demandes_historique.ID_DEMANDE='.$ID_DEMANDE;
    }
  
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CODE_DEMANDE','DESCR_ETAPE','DESCR_ACTION', 'COMMENTAIRE','NOM','PRENOM');
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_HISTORIQUE DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CODE_DEMANDE LIKE "%'.$var_search.'%" OR COMMENTAIRE LIKE "%'.$var_search.'%" OR NOM LIKE "%'.$var_search.'%" OR DESCR_ETAPE LIKE "%'.$var_search.'%" OR DESCR_ACTION LIKE "%'.$var_search.'%" OR PRENOM LIKE "%'.$var_search.'%" OR proc_demandes.DATE_INSERTION LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale
    $conditions = $search.' '.$order_by.' '.$limit;
    //Condition pour la requête de filtre
    $conditionsfilter = $search;

    $requetedebase= "SELECT proc_demandes.CODE_DEMANDE, proc_etape.DESCR_ETAPE, proc_actions.DESCR_ACTION, proc_demandes_historique.COMMENTAIRE, user_users.NOM, user_users.PRENOM, proc_demandes_historique.DATE_INSERTION, proc_demandes_historique.ID_HISTORIQUE,user_profil.PROFIL_DESCR FROM proc_demandes_historique JOIN proc_demandes ON proc_demandes.ID_DEMANDE=proc_demandes_historique.ID_DEMANDE JOIN proc_actions ON proc_actions.ACTION_ID=proc_demandes_historique.ACTION_ID JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes_historique.ETAPE_ID JOIN user_users ON user_users.USER_ID=proc_demandes_historique.USER_ID JOIN user_profil ON user_profil.PROFIL_ID=user_users.PROFIL_ID WHERE 1 ".$ID_DEMANDE."";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase.' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u = 1;
      
    foreach($fetch_data as $row)
    {
      $sub_array = array();
      if(strlen($row->DESCR_ETAPE) > 8)
      {
        $DESCR_ETAPE =  substr($row->DESCR_ETAPE, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#etape'.$row->ID_HISTORIQUE.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_ETAPE =  $row->DESCR_ETAPE;
      }
      if(strlen($row->COMMENTAIRE) > 8)
      {
        $COMMENTAIRE =  substr($row->COMMENTAIRE, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#commentaire'.$row->ID_HISTORIQUE.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $COMMENTAIRE =  $row->COMMENTAIRE;
      }

      $sub_array[]=$u++;
      $sub_array[]=$row->CODE_DEMANDE;
      $sub_array[]=$DESCR_ETAPE;
      $sub_array[]=$row->DESCR_ACTION;
      $sub_array[]=$row->COMMENTAIRE;
      $sub_array[]=$row->PROFIL_DESCR;
      $sub_array[]=$row->NOM.' '.$row->PRENOM;
      $sub_array[]=date('d-m-Y',strtotime($row->DATE_INSERTION))."
      <div class='modal fade' id='etape".$row->ID_HISTORIQUE."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <h5><b> ".$row->DESCR_ETAPE." </b></h5>
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

      <div class='modal fade' id='commentaire".$row->ID_HISTORIQUE."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <h5><b> ".$row->COMMENTAIRE." </b></h5>
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

  // liste de cl & cmr de la vision
  public function liste_cl_cmr_vision()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND cl_cmr.ID_DEMANDE='.$ID_DEMANDE;
    }
  
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_PILIER','DESCR_OBJECTIF_STRATEGIC', 'DESC_INDICACTEUR_OBJECT_STRATEGIQUE','PRECISIONS','REFERENCE','CIBLE');
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_CL_CMR DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY LIKE "%'.$var_search.'%" OR DESC_INDICACTEUR_OBJECT_STRATEGIQUE LIKE "%'.$var_search.'%" OR PRECISIONS LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR REFERENCE LIKE "%'.$var_search.'%" OR CIBLE LIKE "%'.$var_search.'%")') : '';

    // Condition pour la requête principale
    $conditions = $search.' '.$order_by.' '.$limit;
    // Condition pour la requête de filtre
    $conditionsfilter = $search;

    $requetedebase= "SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr cl_cmr JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE =cl_cmr.ID_CL_CMR_CATEGORIE JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE  JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE 1 ".$ID_DEMANDE." AND cl_cmr.ID_CL_CMR_CATEGORIE=2";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase .' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      $sub_array[]=$row->PRECISIONS;
      $sub_array[]=$row->REFERENCE;
      $sub_array[]=$row->CIBLE;
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

  //liste de cl & cmr du pap
  public function liste_cl_cmr_pap()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND cl_cmr.ID_DEMANDE='.$ID_DEMANDE;
    }
  
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_PILIER','DESCR_OBJECTIF_STRATEGIC', 'DESC_INDICACTEUR_OBJECT_STRATEGIQUE','PRECISIONS','REFERENCE','CIBLE');
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_CL_CMR DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY LIKE "%'.$var_search.'%" OR DESC_INDICACTEUR_OBJECT_STRATEGIQUE LIKE "%'.$var_search.'%" OR PRECISIONS LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR REFERENCE LIKE "%'.$var_search.'%" OR CIBLE LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale
    $conditions = $search.' '.$order_by.' '.$limit;
    //Condition pour la requête de filtre
    $conditionsfilter = $search;
    $requetedebase= "SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr cl_cmr JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE =cl_cmr.ID_CL_CMR_CATEGORIE JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE  JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE 1 ".$ID_DEMANDE." AND cl_cmr.ID_CL_CMR_CATEGORIE=3";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase.' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      $sub_array[]=$row->PRECISIONS;
      $sub_array[]=$row->REFERENCE;
      $sub_array[]=$row->CIBLE;
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

  //liste de cl & cmr de la politique sectorielle
  public function liste_cl_cmr_politique_sectorielle()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND cl_cmr.ID_DEMANDE='.$ID_DEMANDE;
    }
    
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_PILIER','DESCR_OBJECTIF_STRATEGIC', 'DESC_INDICACTEUR_OBJECT_STRATEGIQUE','PRECISIONS','REFERENCE','CIBLE');
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_CL_CMR DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY LIKE "%'.$var_search.'%" OR DESC_INDICACTEUR_OBJECT_STRATEGIQUE LIKE "%'.$var_search.'%" OR PRECISIONS LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR REFERENCE LIKE "%'.$var_search.'%" OR CIBLE LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale
    $conditions = $search.' '.$order_by.' '.$limit;
    //Condition pour la requête de filtre
    $conditionsfilter = $search;

    $requetedebase= "SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr cl_cmr JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE =cl_cmr.ID_CL_CMR_CATEGORIE JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE  JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE 1 ".$ID_DEMANDE." AND cl_cmr.ID_CL_CMR_CATEGORIE=4";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase.' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      $sub_array[]=$row->PRECISIONS;
      $sub_array[]=$row->REFERENCE;
      $sub_array[]=$row->CIBLE;
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

  //liste de costab de la vision
  public function liste_costab_vision()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND costab.ID_DEMANDE='.$ID_DEMANDE;
    }
  
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_ENJEUX','DESCR_PILIER','DESCR_AXE_INTERVATION_PND','DESCR_OBJECTIF_STRATEGIC','DESCR_PROGRAMME','ID_PLANS_PROJET','BUDGET_ANNE_1','BUDGET_ANNE_2','BUDGET_ANNE_3','BUDGET_ANNE_4','BUDGET_ANNE_5','BUDGET_TOTAL');
      $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_COSTAB DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY  LIKE "%'.$var_search.'%" OR DESCR_ENJEUX LIKE "%'.$var_search.'%" OR DESCR_AXE_INTERVATION_PND LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR DESCR_PROGRAMME LIKE "%'.$var_search.'%" OR ID_PLANS_PROJET LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale   
    $conditions = $search.' '.$order_by.' '.$limit;
    //Condition pour la requête de filtre
    $conditionsfilter = $search;
    //$DESCR_PROGRAMME
    $requetedebase= "SELECT costab.BUDGET_ANNE_1, costab.BUDGET_ANNE_2, costab.BUDGET_ANNE_3, costab.BUDGET_ANNE_4, costab.BUDGET_ANNE_5, costab.BUDGET_TOTAL, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, enjeux.DESCR_ENJEUX, pilier.DESCR_PILIER, axe_intervention_pnd.DESCR_AXE_INTERVATION_PND, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, programme_pnd.DESCR_PROGRAMME, ID_PLANS_PROJET FROM planification_demande_costab costab JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE=costab.ID_COSTAB_CATEGORIE JOIN enjeux ON enjeux.ID_ENJEUX =costab.ID_ENJEUX JOIN pilier ON pilier.ID_PILIER=costab.ID_PILIER JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND =costab.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =costab.ID_OBJECT_STRATEGIQUE JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=costab.PROGRAMME_ID WHERE 1 ".$ID_DEMANDE." AND costab.ID_COSTAB_CATEGORIE=2";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase .' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_ENJEUX;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_AXE_INTERVATION_PND;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESCR_PROGRAMME;
      $sub_array[]=$row->ID_PLANS_PROJET;
      $sub_array[]=$row->BUDGET_ANNE_1;
      $sub_array[]=$row->BUDGET_ANNE_2;
      $sub_array[]=$row->BUDGET_ANNE_3;
      $sub_array[]=$row->BUDGET_ANNE_4;
      $sub_array[]=$row->BUDGET_ANNE_5;
      $sub_array[]=$row->BUDGET_TOTAL;
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

  //liste de costab du pap
  public function liste_costab_pap()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    if(!empty($ID_DEMANDE))
    {
        $ID_DEMANDE=' AND costab.ID_DEMANDE='.$ID_DEMANDE;
    }
    
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_ENJEUX','DESCR_PILIER','DESCR_AXE_INTERVATION_PND','DESCR_OBJECTIF_STRATEGIC','DESCR_PROGRAMME','ID_PLANS_PROJET','BUDGET_ANNE_1','BUDGET_ANNE_2','BUDGET_ANNE_3','BUDGET_ANNE_4','BUDGET_ANNE_5','BUDGET_TOTAL');
      $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_COSTAB DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY  LIKE "%'.$var_search.'%" OR DESCR_ENJEUX LIKE "%'.$var_search.'%" OR DESCR_AXE_INTERVATION_PND LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR DESCR_PROGRAMME LIKE "%'.$var_search.'%" OR ID_PLANS_PROJET LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale   
    $conditions = $search.' '.$order_by.' '.$limit;
    //Condition pour la requête de filtre
    $conditionsfilter = $search;
    $requetedebase= "SELECT costab.BUDGET_ANNE_1, costab.BUDGET_ANNE_2, costab.BUDGET_ANNE_3, costab.BUDGET_ANNE_4, costab.BUDGET_ANNE_5, costab.BUDGET_TOTAL, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, enjeux.DESCR_ENJEUX, pilier.DESCR_PILIER, axe_intervention_pnd.DESCR_AXE_INTERVATION_PND, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, programme_pnd.DESCR_PROGRAMME, ID_PLANS_PROJET FROM planification_demande_costab costab JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE=costab.ID_COSTAB_CATEGORIE JOIN enjeux ON enjeux.ID_ENJEUX =costab.ID_ENJEUX JOIN pilier ON pilier.ID_PILIER=costab.ID_PILIER JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND =costab.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =costab.ID_OBJECT_STRATEGIQUE JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=costab.PROGRAMME_ID WHERE 1 ".$ID_DEMANDE." AND costab.ID_COSTAB_CATEGORIE=3";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase.' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_ENJEUX;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_AXE_INTERVATION_PND;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESCR_PROGRAMME;
      $sub_array[]=$row->ID_PLANS_PROJET;
      $sub_array[]=$row->BUDGET_ANNE_1;
      $sub_array[]=$row->BUDGET_ANNE_2;
      $sub_array[]=$row->BUDGET_ANNE_3;
      $sub_array[]=$row->BUDGET_ANNE_4;
      $sub_array[]=$row->BUDGET_ANNE_5;
      $sub_array[]=$row->BUDGET_TOTAL;
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

  //liste de costab de la politique sectorielle
  public function liste_costab_politique_sectorielle()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    if(!empty($ID_DEMANDE))
    {
      $ID_DEMANDE=' AND costab.ID_DEMANDE='.$ID_DEMANDE;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column=array('CL_CMR_COSTAB_CATEGORY','DESCR_ENJEUX','DESCR_PILIER','DESCR_AXE_INTERVATION_PND','DESCR_OBJECTIF_STRATEGIC','DESCR_PROGRAMME','ID_PLANS_PROJET','BUDGET_ANNE_1','BUDGET_ANNE_2','BUDGET_ANNE_3','BUDGET_ANNE_4','BUDGET_ANNE_5','BUDGET_TOTAL');
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PLANS_DEMANDE_COSTAB DESC';

    $search = !empty($_POST['search']['value']) ? (' AND (CL_CMR_COSTAB_CATEGORY  LIKE "%'.$var_search.'%" OR DESCR_ENJEUX LIKE "%'.$var_search.'%" OR DESCR_AXE_INTERVATION_PND LIKE "%'.$var_search.'%" OR DESCR_PILIER LIKE "%'.$var_search.'%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR DESCR_PROGRAMME LIKE "%'.$var_search.'%" OR ID_PLANS_PROJET LIKE "%'.$var_search.'%")') : '';

    //Condition pour la requête principale   
    $conditions = $search.' '.$order_by.' '.$limit;
    // Condition pour la requête de filtre
    $conditionsfilter = $search;
    $requetedebase= "SELECT costab.BUDGET_ANNE_1, costab.BUDGET_ANNE_2, costab.BUDGET_ANNE_3, costab.BUDGET_ANNE_4, costab.BUDGET_ANNE_5, costab.BUDGET_TOTAL, cl_cmr_costab_categorie.CL_CMR_COSTAB_CATEGORY, enjeux.DESCR_ENJEUX, pilier.DESCR_PILIER, axe_intervention_pnd.DESCR_AXE_INTERVATION_PND, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, programme_pnd.DESCR_PROGRAMME, ID_PLANS_PROJET FROM planification_demande_costab costab JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE=costab.ID_COSTAB_CATEGORIE JOIN enjeux ON enjeux.ID_ENJEUX =costab.ID_ENJEUX JOIN pilier ON pilier.ID_PILIER=costab.ID_PILIER JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND =costab.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =costab.ID_OBJECT_STRATEGIQUE JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=costab.PROGRAMME_ID WHERE 1 ".$ID_DEMANDE." AND costab.ID_COSTAB_CATEGORIE=4";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';
    $requetedebases = $requetedebase.' '.$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$row->CL_CMR_COSTAB_CATEGORY;
      $sub_array[]=$row->DESCR_ENJEUX;
      $sub_array[]=$row->DESCR_PILIER;
      $sub_array[]=$row->DESCR_AXE_INTERVATION_PND;
      $sub_array[]=$row->DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]=$row->DESCR_PROGRAMME;
      $sub_array[]=$row->ID_PLANS_PROJET;
      $sub_array[]=$row->BUDGET_ANNE_1;
      $sub_array[]=$row->BUDGET_ANNE_2;
      $sub_array[]=$row->BUDGET_ANNE_3;
      $sub_array[]=$row->BUDGET_ANNE_4;
      $sub_array[]=$row->BUDGET_ANNE_5;
      $sub_array[]=$row->BUDGET_TOTAL;
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
    
  public function send_data($value='')
  {    
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $USER_ID  = session()->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $MOVETO = $this->request->getPost('MOVETO');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $IS_REQUIRED = $this->request->getPost('IS_REQUIRED');

    ##################################################################################################
    //mise à jour dans la table proc_demandes / on recupere Next étape
    $table = 'proc_demandes';
    $where='ID_DEMANDE='.$ID_DEMANDE.'';
    $data='ETAPE_ID='.$MOVETO.'';
    $this->update_all_table($table,$data,$where);
    ################################################################################################

    $fin_process = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT IS_INITIAL FROM proc_actions 
      WHERE 1 AND ETAPE_ID = {$ETAPE_ID}')");

    if ($fin_process['IS_INITIAL'] == 5)
    {
      $IS_END = 1;
      $table1 = 'proc_demandes';
      $where1='ID_DEMANDE='.$ID_DEMANDE.'';
      $data1='IS_END='.$IS_END.'';
      $this->update_all_table($table1,$data1,$where1);
    }

    //insertion dans la table commentaire
    $insertIntoTable='proc_demandes_historique';
    $columsinsert="ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID,COMMENTAIRE";
    $datacolumsinsert=$ID_DEMANDE.','.$ETAPE_ID.','.$USER_ID.','.$ACTION_ID.',"'.$COMMENTAIRE.'"';
    $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    $output = array('status'=>true);
    return $this->response->setJSON($output);
  }

  public function getObjectif(int $id_pilier)
  { 
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE, DESCR_OBJECTIF_STRATEGIC, ID_PILIER FROM objectif_strategique 
      WHERE 1 AND ID_PILIER = {$id_pilier} ORDER BY DESCR_OBJECTIF_STRATEGIC ASC')");

    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $html_objectif .= '<option value="'.$key->ID_OBJECT_STRATEGIQUE.'">' . $key->DESCR_OBJECTIF_STRATEGIC . '</option>';
    }

    $output = array(
      "objectif" => $html_objectif
    );
    return $this->response->setJSON($output);
  }

  public function getIndicateur(int $id_objectif)
  {  
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $objectif_strategique_indicateur = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_INDICACTEUR_OBJECT_STRATEGIQUE , DESC_INDICACTEUR_OBJECT_STRATEGIQUE, ID_OBJECT_STRATEGIQUE FROM objectif_strategique_indicateur 
      WHERE 1 AND ID_OBJECT_STRATEGIQUE = {$id_objectif} ORDER BY DESC_INDICACTEUR_OBJECT_STRATEGIQUE ASC')");

    $html_indicateur = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique_indicateur as $key)
    {
      $html_indicateur .= '<option value="'.$key->ID_INDICACTEUR_OBJECT_STRATEGIQUE .'">' . $key->DESC_INDICACTEUR_OBJECT_STRATEGIQUE . '</option>';
    }
    $output = array(
      "indicateur" => $html_indicateur
    );
    return $this->response->setJSON($output);
  }

  //appel du view de la liste des actions
  function index($ACTION_ID='',$ID_DEMANDE='',$getForm='')
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $getAction = 'SELECT IS_INITIAL FROM proc_actions WHERE proc_actions.ACTION_ID='.$ACTION_ID.'';
    $getAction = "CALL `getTable`('" . $getAction . "');";
    $getAction = $this->ModelPs->getRequeteOne($getAction);

    if ($getAction['IS_INITIAL']==1)
    {
      if($ACTION_ID=='')
      {
        return redirect('Login_Ptba/do_logout');
      }
    }
    else
    {
      $infosDemande = 'SELECT ID_DEMANDE FROM `proc_demandes` WHERE md5(ID_DEMANDE)="'.$ID_DEMANDE.'"';
      $infosDemande = "CALL `getTable`('" . $infosDemande . "');";
      $resultatDemande= $this->ModelPs->getRequeteOne($infosDemande);
      if(empty($resultatDemande))
      {
        return redirect('Login_Ptba/do_logout');
      }

      $ID_DEMANDE = $resultatDemande['ID_DEMANDE'];
      if($ACTION_ID=='' || $ID_DEMANDE=='')
      {
        return redirect('Login_Ptba/do_logout');
      }
    }

    ################################################################################
    $bindAction = $this->getBindParms('ID_CL_CMR_COSTAB_CATEGORIE,ACTION_ID', 'proc_actions', 'proc_actions.ACTION_ID='.$ACTION_ID.'', '1');
    $data['getAction'] = $this->ModelPs->getRequeteOne($callpsreq, $bindAction);
    $data['ID_DEMANDE'] = $ID_DEMANDE;
    $data['categories'] = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_CL_CMR_COSTAB_CATEGORIE,CL_CMR_COSTAB_CATEGORY FROM cl_cmr_costab_categorie WHERE 1 AND ID_CL_CMR_COSTAB_CATEGORIE=".$data['getAction']['ID_CL_CMR_COSTAB_CATEGORIE']."')");
    #################################################################################

    //get piliers
    $data['piliers'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");

    //get enjeux
    $bindenjeux = $this->getBindParms('ID_ENJEUX, DESCR_ENJEUX', 'enjeux', '1', 'ID_ENJEUX ASC');
    $data['enjeux'] = $this->ModelPs->getRequete($callpsreq, $bindenjeux);

    //axe_intervention_pnd
    $data['axe_intervation'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd 
          WHERE 1 ORDER BY DESCR_AXE_INTERVATION_PND ASC')");

    //get programme
    $bindProgramme = $this->getBindParms('ID_PROGRAMME_PND, DESCR_PROGRAMME', 'programme_pnd', '1', 'ID_PROGRAMME_PND ASC');
    $data['programme'] = $this->ModelPs->getRequete($callpsreq, $bindProgramme);
    ####################################################################################

    //get instition d'affectation de personne connecté
    $user = $this->getBindParms('aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION','user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID','USER_ID='.$user_id.'','INSTITUTION_ID');
    $data['institution'] = $this->ModelPs->getRequete($callpsreq, $user);
    $data['getForm'] = $getForm;
    return view('App\Modules\process\Views\Planification_demande_ci_cmr_update_views', $data);
  }

  function liste_cl_cmr($value='')
  {
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost("ID_DEMANDE");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");

    $data ="SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE, cl_cmr.ID_PLANS_DEMANDE_CL_CMR, inst_institutions.DESCRIPTION_INSTITUTION FROM planification_demande_cl_cmr cl_cmr JOIN inst_institutions ON inst_institutions.INSTITUTION_ID =cl_cmr.INSTITUTION_ID JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE 1 AND cl_cmr.ID_DEMANDE=".$ID_DEMANDE." AND cl_cmr.ID_CL_CMR_CATEGORIE=".$ID_CL_CMR_COSTAB_CATEGORIE."";
    $data = "CALL `getTable`('".$data."');";
    $rqt = $this->ModelPs->getRequete($data);
    $count_data = count($rqt);

    $table = '';
    $table = '<div class="table-responsive">
                 <table  id="tables_cl_cmr" class="table table-bordered table-hover table-striped table-condesed">
                    <thead>
                    <tr>
                        <th>INSTITUTION</th>
                        <th>PILIER</th>
                        <th>OBJECTIF</th>
                        <th>INDICATEUR</th>
                        <th>PRECISION</th>
                        <th>REFERENCE</th>
                        <th>CIBLE</th>
                        <th>ACTION</th>
                    </tr>
                    <thead><tbody>';
        
    foreach($rqt as $row)
    {
      $table.="<tr>
                <td>".$row->DESCRIPTION_INSTITUTION."</td>
                <td>".$row->DESCR_PILIER."</td>
                <td>".$row->DESCR_OBJECTIF_STRATEGIC."</td>
                <td>".$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE."</td> 
                <td>".$row->PRECISIONS."</td> 
                <td>".$row->REFERENCE."</td>
                <td>".$row->CIBLE."</td>
                <td>
                  <a onclick='editercl_cmr(".$row->ID_PLANS_DEMANDE_CL_CMR.")' href='javascript:;' style='color: green'><i class='fa fa-pencil'></i> </a>&nbsp;&nbsp;
                  <a onclick='supprimer_cl_cmr(".$row->ID_PLANS_DEMANDE_CL_CMR.")' href='javascript:;' style='color: red'><i class='fa fa-trash'></i> </a>
                </td>
              </tr>";
    }
    $table.='</tbody><table/></div>';
    $table.='<script>
                $(document).ready(function(){
         
                $("#tables_cl_cmr").DataTable({
                    lengthMenu: [[2,10, 20,-1], [2,10, 20, "All"]],
                pageLength: 2,
                  "columnDefs":[{
                      "targets":[],
                      "orderable":false
                  }],
  
                language: {
                            "sProcessing":     "Traitement en cours...",
                            "sSearch":         "Rechercher&nbsp;:",
                            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
                            "sInfo":           "Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                            "sInfoEmpty":      "Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
                            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                            "sInfoPostFix":    "",
                            "sLoadingRecords": "Chargement en cours...",
                            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
                            "oPaginate": {
                              "sFirst":      "Premier",
                              "sPrevious":   "Pr&eacute;c&eacute;dent",
                              "sNext":       "Suivant",
                              "sLast":       "Dernier"
                            },
                            "oAria": {
                              "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                              "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                            }
                        }
                          
                });
            }); 
        </script>';

    $output = array('tabledata'=>$table, 'count_data'=>$count_data);
    echo json_encode($output);
  }

  public function supprimer_cl_cmr()
  {
      
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $db = db_connect();
    $id = $this->request->getPost('id');
    $critere = "ID_PLANS_DEMANDE_CL_CMR= {$id}";
    $table = "planification_demande_cl_cmr";
    $bindparams = [$db->escapeString($table), $db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    echo json_encode(array('status'=>true));
  }

  public function editercl_cmr()
  {
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $id = $this->request->getPost("id");

    $cl_cmr_data = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_PILIER,ID_OBJECT_STRATEGIQUE,ID_PLANS_INDICATEUR,PRECISIONS,REFERENCE,CIBLE,ID_PLANS_DEMANDE_CL_CMR, INSTITUTION_ID FROM planification_demande_cl_cmr WHERE 1 AND ID_PLANS_DEMANDE_CL_CMR  = {$id} ORDER BY ID_PLANS_DEMANDE_CL_CMR ASC')");
    ###########################################################################################

    $institution_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT INSTITUTION_ID ,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY INSTITUTION_ID ASC')");

    $html_institution='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($institution_data as $key)
    {
      $selected='';
      if($key->INSTITUTION_ID==$cl_cmr_data['INSTITUTION_ID'])
      {
          $selected=' selected';
      }
      $html_institution.='<option value="'.$key->INSTITUTION_ID.'"'.$selected.'>'.$key->DESCRIPTION_INSTITUTION.'</option>';
    }
    ##################################################################################################

    $indicateur_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_INDICACTEUR_OBJECT_STRATEGIQUE ,DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM objectif_strategique_indicateur WHERE 1 ORDER BY ID_INDICACTEUR_OBJECT_STRATEGIQUE ASC')");

    $html_indicateur='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($indicateur_data as $key)
    {
      $selected='';
      if($key->ID_INDICACTEUR_OBJECT_STRATEGIQUE==$cl_cmr_data['ID_PLANS_INDICATEUR'])
      {
          $selected=' selected';
      }
      $html_indicateur.='<option value="'.$key->ID_INDICACTEUR_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESC_INDICACTEUR_OBJECT_STRATEGIQUE.'</option>';
    }
    #############################################################################################

    $piliers_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");

    $html_pilier='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($piliers_data as $key)
    {
      $selected='';
      if($key->ID_PILIER==$cl_cmr_data['ID_PILIER'])
      {
        $selected=' selected';
      }
      $html_pilier.='<option value="'.$key->ID_PILIER.'"'.$selected.'>'.$key->DESCR_PILIER.'</option>';
    }
    ##############################################################################################

    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE,DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique 
        WHERE 1')");

    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $selected='';
      if($key->ID_OBJECT_STRATEGIQUE==$cl_cmr_data['ID_OBJECT_STRATEGIQUE'])
      {
          $selected=' selected';
      }
      $html_objectif.='<option value="'.$key->ID_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESCR_OBJECTIF_STRATEGIC.'</option>';
    }

    $output = array(
        'status'=>true,
        "cl_cmr_data" => $cl_cmr_data,
        "html_institution" => $html_institution,
        "html_indicateur" => $html_indicateur,
        "html_pilier" => $html_pilier,
        "html_objectif" => $html_objectif,
    );
    return $this->response->setJSON($output);
  }

  public function update_save_cl_cmr($value='')
  {      
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $SOURCE = $this->request->getPost("SOURCE");
    $ID_PLANS_DEMANDE_CL_CMR = $this->request->getPost("ID_PLANS_DEMANDE_CL_CMR");
    $PRECISIONS = $this->request->getPost("PRECISIONS");
    $REFERENCE = $this->request->getPost("REFERENCE");
    $CIBLE = $this->request->getPost("CIBLE");
    $ID_PILIER = $this->request->getPost("ID_PILIER");
    $ID_OBJECT_STRATEGIQUE = $this->request->getPost("ID_OBJECT_STRATEGIQUE");
    $ID_PLANS_INDICATEUR = $this->request->getPost("ID_PLANS_INDICATEUR");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");
    $ID_DEMANDE = $this->request->getPost("ID_DEMANDE");
    $INSTITUTION_ID = $this->request->getPost("INSTITUTION_ID");

    // add
    if ($SOURCE==1)
    {
      $this->save_all_table("planification_demande_cl_cmr",
        "ID_DEMANDE, ID_CL_CMR_CATEGORIE, ID_PILIER, ID_OBJECT_STRATEGIQUE, ID_PLANS_INDICATEUR, PRECISIONS, REFERENCE, CIBLE, INSTITUTION_ID", "'{$ID_DEMANDE}','{$ID_CL_CMR_COSTAB_CATEGORIE}','{$ID_PILIER}','{$ID_OBJECT_STRATEGIQUE}','{$ID_PLANS_INDICATEUR}' ,'{$PRECISIONS}','{$REFERENCE}','{$CIBLE}','{$INSTITUTION_ID}'" );
    }
    else
    {
      $table = 'planification_demande_cl_cmr';
      $where='ID_PLANS_DEMANDE_CL_CMR='.$ID_PLANS_DEMANDE_CL_CMR.'';
      $data='ID_PILIER='.$ID_PILIER.', ID_OBJECT_STRATEGIQUE='.$ID_OBJECT_STRATEGIQUE.', ID_PLANS_INDICATEUR='.$ID_PLANS_INDICATEUR.', PRECISIONS="'.$PRECISIONS.'", REFERENCE='.$REFERENCE.', CIBLE='.$CIBLE.', INSTITUTION_ID='.$INSTITUTION_ID.' ';
      $this->update_all_table($table,$data,$where);
    }

    echo json_encode(array('status'=>true));
  }

  function liste_costab($value='')
  {    
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost("ID_DEMANDE");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");

    $data ="SELECT costab.BUDGET_ANNE_1, costab.BUDGET_ANNE_2, costab.BUDGET_ANNE_3, costab.BUDGET_ANNE_4, costab.BUDGET_ANNE_5, costab.BUDGET_TOTAL, inst_institutions.DESCRIPTION_INSTITUTION, enjeux.DESCR_ENJEUX, pilier.DESCR_PILIER, axe_intervention_pnd.DESCR_AXE_INTERVATION_PND, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, programme_pnd.DESCR_PROGRAMME, costab.ID_PLANS_PROJET,costab.ID_PLANS_DEMANDE_COSTAB FROM planification_demande_costab costab JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=costab.INSTITUTION_ID JOIN enjeux ON enjeux.ID_ENJEUX =costab.ID_ENJEUX JOIN pilier ON pilier.ID_PILIER=costab.ID_PILIER JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND =costab.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =costab.ID_OBJECT_STRATEGIQUE JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=costab.PROGRAMME_ID WHERE 1 AND costab.ID_DEMANDE=".$ID_DEMANDE." AND costab.ID_COSTAB_CATEGORIE=".$ID_CL_CMR_COSTAB_CATEGORIE."";
    $data = "CALL `getTable`('".$data."');";
    $rqt = $this->ModelPs->getRequete($data);
    $count_data = count($rqt);

    $table = '';
    $table = '<div class="table-responsive">
               <table  id="tables_costab" class="table table-bordered table-hover table-striped table-condesed">
                  <thead>
                  <tr>
                      <th>INSTITUTION</th>
                      <th>ENJEUX</th>
                      <th>PILIER</th>
                      <th>AXE&nbsp;INTERVENTION</th>
                      <th>OBJECTIF</th>
                      <th>PROGRAMME</th>
                      <th>PLANIFICATION</th>
                      <th>BUDGET&nbsp;ANNEE&nbsp;1</th>
                      <th>BUDGET&nbsp;ANNEE&nbsp;2</th>
                      <th>BUDGET&nbsp;ANNEE&nbsp;3</th>
                      <th>BUDGET&nbsp;ANNEE&nbsp;4</th>
                      <th>BUDGET&nbsp;ANNEE&nbsp;5</th>
                      <th>BUDGET&nbsp;TOTAL</th>
                      <th>ACTION</th>
                  </tr>
                  <thead><tbody>';

    foreach($rqt as $row)
    {
      $table.="<tr>
                <td>".$row->DESCRIPTION_INSTITUTION."</td>
                <td>".$row->DESCR_ENJEUX."</td>
                <td>".$row->DESCR_PILIER."</td>
                <td>".$row->DESCR_AXE_INTERVATION_PND."</td> 
                <td>".$row->DESCR_OBJECTIF_STRATEGIC."</td> 
                <td>".$row->DESCR_PROGRAMME."</td>
                <td>".$row->ID_PLANS_PROJET."</td>
                <td>".$row->BUDGET_ANNE_1."</td>
                <td>".$row->BUDGET_ANNE_2."</td>
                <td>".$row->BUDGET_ANNE_3."</td> 
                <td>".$row->BUDGET_ANNE_4."</td> 
                <td>".$row->BUDGET_ANNE_5."</td>
                <td>".$row->BUDGET_TOTAL."</td>
                <td>
                  <a onclick='editercostab(".$row->ID_PLANS_DEMANDE_COSTAB.")' href='javascript:;' style='color: green'><i class='fa fa-pencil'></i> </a>&nbsp;&nbsp;
                  <a onclick='supprimer_costab(".$row->ID_PLANS_DEMANDE_COSTAB.")' href='javascript:;' style='color: red'><i class='fa fa-trash'></i> </a>
                </td>
              </tr>";
    }
    $table.='</tbody><table/></div>';
    $table.='<script>
                   $(document).ready(function(){
             
                   $("#tables_costab").DataTable({
                        lengthMenu: [[2,10, 20,-1], [2,10, 20, "All"]],
                    pageLength: 2,
                      "columnDefs":[{
                          "targets":[],
                          "orderable":false
                      }],
  
                   language: {
                            "sProcessing":     "Traitement en cours...",
                            "sSearch":         "Rechercher&nbsp;:",
                            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
                            "sInfo":           "Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                            "sInfoEmpty":      "Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
                            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                            "sInfoPostFix":    "",
                            "sLoadingRecords": "Chargement en cours...",
                            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
                            "oPaginate": {
                              "sFirst":      "Premier",
                              "sPrevious":   "Pr&eacute;c&eacute;dent",
                              "sNext":       "Suivant",
                              "sLast":       "Dernier"
                            },
                            "oAria": {
                              "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                              "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                            }
                        }
                          
                });
            }); 
        </script>';

    $output = array('tabledata'=>$table, 'count_data'=>$count_data);
    echo json_encode($output);
  }
 
  public function supprimer_costab()
  {     
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $db = db_connect();
    $id = $this->request->getPost('id');
    $critere = "ID_PLANS_DEMANDE_COSTAB= {$id}";
    $table = "planification_demande_costab";
    $bindparams = [$db->escapeString($table), $db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    echo json_encode(array('status'=>true));
  }

  public function editercostab()
  {  
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $id = $this->request->getPost("id");

    $costab_data = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,ID_PLANS_DEMANDE_COSTAB,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,ID_COSTAB_CATEGORIE,INSTITUTION_ID FROM planification_demande_costab WHERE 1 AND ID_PLANS_DEMANDE_COSTAB = {$id} ORDER BY ID_PLANS_DEMANDE_COSTAB ASC')");
    ###########################################################################################

    $institution_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT INSTITUTION_ID ,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY INSTITUTION_ID ASC')");
    $html_institution='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($institution_data as $key)
    {
      $selected='';
      if($key->INSTITUTION_ID==$costab_data['INSTITUTION_ID'])
      {
          $selected=' selected';
      }
      $html_institution.='<option value="'.$key->INSTITUTION_ID.'"'.$selected.'>'.$key->DESCRIPTION_INSTITUTION.'</option>';
    }
    ##################################################################################################

    $bindenjeux = $this->getBindParms('ID_ENJEUX, DESCR_ENJEUX', 'enjeux', '1', 'ID_ENJEUX ASC');
    $enjeux = $this->ModelPs->getRequete($callpsreq, $bindenjeux);

    $html_enjeux='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($enjeux as $key)
    {
      $selected='';
      if($key->ID_ENJEUX==$costab_data['ID_ENJEUX'])
      {
          $selected=' selected';
      }
      $html_enjeux.='<option value="'.$key->ID_ENJEUX.'"'.$selected.'>'.$key->DESCR_ENJEUX.'</option>';
    }
    #############################################################################################
    $piliers_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY DESCR_PILIER ASC')");
    $html_pilier='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($piliers_data as $key)
    {
      $selected='';
      if($key->ID_PILIER==$costab_data['ID_PILIER'])
      {
        $selected=' selected';
      }
      $html_pilier.='<option value="'.$key->ID_PILIER.'"'.$selected.'>'.$key->DESCR_PILIER.'</option>';
    }
    ##############################################################################################

    $axe_intervation = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd 
          WHERE 1 ORDER BY DESCR_AXE_INTERVATION_PND ASC')");
    $html_axe = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($axe_intervation as $key)
    {
      $selected='';
        if($key->ID_AXE_INTERVENTION_PND==$costab_data['ID_AXE_INTERVENTION_PND'])
        {
            $selected=' selected';
        }
        $html_axe.='<option value="'.$key->ID_AXE_INTERVENTION_PND.'"'.$selected.'>'.$key->DESCR_AXE_INTERVATION_PND.'</option>';
    }
    ########################################################################################

    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE,DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique 
        WHERE 1')");
    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $selected='';
      if($key->ID_OBJECT_STRATEGIQUE==$costab_data['ID_OBJECT_STRATEGIQUE'])
      {
          $selected=' selected';
      }
      $html_objectif.='<option value="'.$key->ID_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESCR_OBJECTIF_STRATEGIC.'</option>';
    }
    #########################################################################################
    $bindProgramme = $this->getBindParms('ID_PROGRAMME_PND, DESCR_PROGRAMME', 'programme_pnd', '1', 'ID_PROGRAMME_PND ASC');
    $programme = $this->ModelPs->getRequete($callpsreq, $bindProgramme);
    $html_programme = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($programme as $key)
    {
      $selected='';
      if($key->ID_PROGRAMME_PND==$costab_data['PROGRAMME_ID'])
      {
          $selected=' selected';
      }
      $html_programme.='<option value="'.$key->ID_PROGRAMME_PND.'"'.$selected.'>'.$key->DESCR_PROGRAMME.'</option>';
    }
    #############################################################################################

    $output = array(
      'status'=>true,
      "costab_data" => $costab_data,
      "html_institution" => $html_institution,
      "html_enjeux" => $html_enjeux,
      "html_pilier" => $html_pilier,
      'html_axe'=>$html_axe,
      "html_objectif" => $html_objectif,
      "html_programme" => $html_programme,
    );
    return $this->response->setJSON($output);
  }
 
  public function update_save_costab($value='')
  {
        
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $SOURCE = $this->request->getPost("SOURCE");
    $ID_PLANS_DEMANDE_COSTAB = $this->request->getPost("ID_PLANS_DEMANDE_COSTAB");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");
    $ID_DEMANDE = $this->request->getPost("ID_DEMANDE");
    $ID_ENJEUX = $this->request->getPost("ID_ENJEUX");
    $ID_PILIER = $this->request->getPost("ID_PILIER");
    $ID_AXE_INTERVENTION_PND = $this->request->getPost("ID_AXE_INTERVENTION_PND");
    $ID_OBJECTIF = $this->request->getPost("ID_OBJECTIF");
    $PROGRAMME_ID = $this->request->getPost("PROGRAMME_ID");
    $ID_PLANS_PROJET = $this->request->getPost("ID_PLANS_PROJET");
    $BUDGET_ANNE_1 = $this->request->getPost("BUDGET_ANNEE_1");
    $BUDGET_ANNE_2 = $this->request->getPost("BUDGET_ANNEE_2");
    $BUDGET_ANNE_3 = $this->request->getPost("BUDGET_ANNEE_3");
    $BUDGET_ANNE_4 = $this->request->getPost("BUDGET_ANNEE_4");
    $BUDGET_ANNE_5 = $this->request->getPost("BUDGET_ANNEE_5");
    $BUDGET_TOTAL = $this->request->getPost("BUDGET_TOTAL");
    $INSTITUTION_ID2 = $this->request->getPost("INSTITUTION_ID2");

    // add
    if ($SOURCE==1)
    {
      $this->save_all_table("planification_demande_costab","ID_DEMANDE,ID_COSTAB_CATEGORIE,ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,INSTITUTION_ID","'{$ID_DEMANDE}','{$ID_CL_CMR_COSTAB_CATEGORIE}','{$ID_ENJEUX}','{$ID_PILIER}','{$ID_AXE_INTERVENTION_PND}','{$ID_OBJECTIF}','{$PROGRAMME_ID}','{$ID_PLANS_PROJET}','{$BUDGET_ANNE_1}','{$BUDGET_ANNE_2}','{$BUDGET_ANNE_3}','{$BUDGET_ANNE_4}','{$BUDGET_ANNE_5}','{$BUDGET_TOTAL}','{$INSTITUTION_ID2}'");
    }
    else
    {
      $table = 'planification_demande_costab';
      $where='ID_PLANS_DEMANDE_COSTAB='.$ID_PLANS_DEMANDE_COSTAB.'';
      $data='ID_ENJEUX='.$ID_ENJEUX.',ID_PILIER='.$ID_PILIER.',ID_AXE_INTERVENTION_PND='.$ID_AXE_INTERVENTION_PND.', ID_OBJECT_STRATEGIQUE='.$ID_OBJECTIF.', PROGRAMME_ID='.$PROGRAMME_ID.', ID_PLANS_PROJET="'.$ID_PLANS_PROJET.'", BUDGET_ANNE_1='.$BUDGET_ANNE_1.', BUDGET_ANNE_2='.$BUDGET_ANNE_2.',BUDGET_ANNE_3='.$BUDGET_ANNE_3.',BUDGET_ANNE_4='.$BUDGET_ANNE_4.',BUDGET_ANNE_5='.$BUDGET_ANNE_5.',BUDGET_TOTAL='.$BUDGET_TOTAL.',INSTITUTION_ID='.$INSTITUTION_ID2.'';
      $this->update_all_table($table,$data,$where);
    }

    echo json_encode(array('status'=>true));
  }

  public function update_form_cl_cmr_costab()
  {
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');

    $USER_ID = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if($USER_ID=='')
    {
      return redirect('Login_Ptba/do_logout');
    }

    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    ### recuperation des ACTION_ID, ETAPE_ID, MOVETO, IS_INITIAL a partir d'une action qui vient en parametre
    $etap_data="SELECT ACTION_ID, ETAPE_ID, MOVETO, IS_INITIAL FROM proc_actions WHERE proc_actions.ACTION_ID = ".$ACTION_ID."";
    $etap_data = "CALL `getTable`('" . $etap_data . "');";
    $etap = $this->ModelPs->getRequeteOne($etap_data);
    $MOVETO = $etap['MOVETO'];
    $ETAPE_ACTUEL = $etap['ETAPE_ID'];
    $ACTION_ID = $etap['ACTION_ID'];
    #################################################################################################

    //mise à jour dans la table proc_demandes / on recupere Next étape
    $table = 'proc_demandes';
    $where='ID_DEMANDE='.$ID_DEMANDE.'';
    $data='ETAPE_ID='.$MOVETO.'';
    $this->update_all_table($table,$data,$where);
    ############################################################################################

    //insertion dans la table historique apres une demande ou une action quelquonque
    $this->save_all_table("proc_demandes_historique","ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID","'{$ID_DEMANDE}', '{$ETAPE_ACTUEL}', '{$USER_ID}', '{$ACTION_ID}'");
    #######################################################################################

    $data = ['message' => lang('messages_lang.modification_reussi')];
    session()->setFlashdata('alert', $data);
    return redirect('process/Demandes');
  }
}
?>