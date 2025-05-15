<?php
/*
*NDERAGAKURA ALAIN CHARBEL
*Titre: Liste des liquidation cas salaire
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 5 septembre,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Liquidation_Salaire_Liste extends BaseController
{
	protected $session;
	protected $ModelPs;

	public function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

  //get liste autre retenu
  public function liste_autre_retenu($value='')
  {
    $data = $this->urichk();
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

    $callpsreq = "CALL getRequete(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getcat  = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getcat = "CALL getTable('" .$getcat. "');";
    $data['categorie'] = $this->ModelPs->getRequete($getcat);
    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Liste_Autre_Retenue_View',$data);
  }

  //listing autre retenu
  public function listing_autre_retenu($value='')
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";

    if (!empty($CATEGORIE_SALAIRE_ID)) 
    {
      $critere1 = " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESC_MOIS','DESC_CATEGORIE_SALAIRE','DESC_TYPE_SALAIRE','MOTIF_PAIEMENT','DESC_BENEFICIAIRE','MONTANT_PAIEMENT','TITRE_DECAISSEMENT');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_MOIS LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR DESC_TYPE_SALAIRE LIKE '%$var_search%' OR MOTIF_PAIEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR MONTANT_PAIEMENT LIKE '%$var_search%' OR TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION,typ.DESC_TYPE_SALAIRE,td.MONTANT_PAIEMENT,td.MOTIF_PAIEMENT,benef.DESC_BENEFICIAIRE,TITRE_DECAISSEMENT FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN type_salairie typ ON typ.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID WHERE 1 AND td.IS_TD_NET=1";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      $action = '';
      $sub_array = array();
      $sub_array[] = $row->DESC_MOIS;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $sub_array[] = $row->DESC_TYPE_SALAIRE;
      $sub_array[] = $row->MOTIF_PAIEMENT;
      $sub_array[] = $row->DESC_BENEFICIAIRE;
      $sub_array[] = number_format($row->MONTANT_PAIEMENT,$this->get_precision($row->MONTANT_PAIEMENT),'.',' ');
      $sub_array[] = $row->TITRE_DECAISSEMENT?$row->TITRE_DECAISSEMENT:'-';
      // $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_inst(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_tache."</a></center>";
      // $sub_array[] = $nbr;
      
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //get liste liquidation salaire deja fait
  function index_Deja_Fait()
  {
    $data = $this->urichk();
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

    if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')!=1)
    {
     return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getcat  = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getcat = "CALL getTable('" .$getcat. "');";
    $data['categorie'] = $this->ModelPs->getRequete($getcat);

    $getmois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois ORDER BY MOIS_ID ASC';
    $getmois = "CALL getTable('" .$getmois. "');";
    $data['mois'] = $this->ModelPs->getRequete($getmois);

    $nbr_liqu=$this->count_liquidation_salaire();
    $data['nbr_liqu_salaire']=$nbr_liqu['nbr_liqu_salaire'];
    $data['nbr_liq_a_valide']=$nbr_liqu['nbr_liq_a_valide'];
    $data['nbr_liq_deja_valide']=$nbr_liqu['nbr_liq_deja_valide'];
    $data['nbr_liq_deja_fait']=$nbr_liqu['nbr_liq_deja_fait'];
    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Liste_Deja_Fait_View',$data);
  }

  //listing liquidation salaire deja fait
  public function listing_Deja_Fait()
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";

    if (!empty($MOIS_ID))
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if (!empty($CATEGORIE_SALAIRE_ID)) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESC_MOIS', 1,1, 'LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_MOIS LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>10 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      //Nombre d'institution
      $count_tache = "SELECT INSTITUTION_ID FROM execution_budgetaire_salaire_sous_titre WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);
      $nbre_tache = count($nbre_tache);

      $action = '';
      $sub_array = array();
      $sub_array[] = $row->DESC_MOIS;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_inst(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_tache."</a></center>";
      $sub_array[] = $nbr;
      $sub_array[] = number_format($row->LIQUIDATION,$this->get_precision($row->LIQUIDATION),'.',' ');
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

	//get liste liquidation salaire a corriger
	function index_A_Corr()
	{
		$data = $this->urichk();
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

		if($session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE')!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$callpsreq = "CALL getRequete(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
		$getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

		$ID_INST='';
		foreach ($getaffect as $value)
		{
			$ID_INST.=$value->INSTITUTION_ID.' ,';           
		}
		$ID_INST = substr($ID_INST,0,-1);

		$getcat  = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
		$getcat = "CALL getTable('" .$getcat. "');";
		$data['categorie'] = $this->ModelPs->getRequete($getcat);

    $getmois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois ORDER BY MOIS_ID ASC';
    $getmois = "CALL getTable('" .$getmois. "');";
    $data['mois'] = $this->ModelPs->getRequete($getmois);

		$nbr_liqu=$this->count_liquidation_salaire();
		$data['nbr_liqu_salaire']=$nbr_liqu['nbr_liqu_salaire'];
    $data['nbr_liq_a_valide']=$nbr_liqu['nbr_liq_a_valide'];
    $data['nbr_liq_deja_valide']=$nbr_liqu['nbr_liq_deja_valide'];
    $data['nbr_liq_deja_fait']=$nbr_liqu['nbr_liq_deja_fait'];
		return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Liste_Corrige_View',$data);
	}

	//listing liquidation salaire a corrige
	public function listing()
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";

    if (!empty($CATEGORIE_SALAIRE_ID)) 
    {
      $critere1 = " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }
    if (!empty($MOIS_ID))
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }
    
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESCRIPTION_INSTITUTION', 'DESCRIPTION_SOUS_TUTEL',1, 'TOTAL_SALAIRE');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY st.CODE_SOUS_TUTEL ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESCRIPTION_SOUS_TUTEL LIKE '%$var_search%' OR TOTAL_SALAIRE LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID,exec.EXECUTION_BUDGETAIRE_ID,st.SOUS_TUTEL_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL,categ.DESC_CATEGORIE_SALAIRE,TOTAL_SALAIRE FROM execution_budgetaire exec JOIN execution_budgetaire_salaire_sous_titre sous_titre ON exec.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=sous_titre.INSTITUTION_ID JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sous_titre.SOUS_TUTEL_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE 1 AND A_CORRIGER=1";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
     
        $bouton = "<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/Liquidation_Salaire/add_correction_view'."/".md5($row->EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID))."' ><span class='fa fa-arrow-up'></span></a>";
 
      //Nombre des bon d'engagement
      $count_tache = "SELECT exec.PTBA_TACHE_ID FROM execution_budgetaire_execution_tache exec JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE tache.SOUS_TUTEL_ID=".$row->SOUS_TUTEL_ID." AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);
      $nbre_tache = (!empty($nbre_tache)) ? count($nbre_tache) : 0 ;

      $INSTITUTION= (mb_strlen($row->DESCRIPTION_INSTITUTION) > 6) ? (mb_substr($row->DESCRIPTION_INSTITUTION, 0, 6) . '...<a class="btn-sm" data-toggle="modal"  title="' . $row->DESCRIPTION_INSTITUTION . '"><i class="fa fa-eye"></i></a>') : $row->DESCRIPTION_INSTITUTION;

      $action = '';
      $sub_array = array();
      $sub_array[] = $row->CODE_INSTITUTION."-".$INSTITUTION;
      $sub_array[] = $row->CODE_SOUS_TUTEL."-".$row->DESCRIPTION_SOUS_TUTEL;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_tache(".$row->SOUS_TUTEL_ID.",".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_tache."</a></center>";
      $sub_array[] = $nbr;
      $sub_array[] = number_format($row->TOTAL_SALAIRE,$this->get_precision($row->TOTAL_SALAIRE),'.',' ');
      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";
      $sub_array[] = $action1;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //listing tache par sous titre
	public function listing_tache()
  {
    // if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $bouton = '';
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESC_TACHE', 'MONTANT_LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_TACHE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_TACHE LIKE '%$var_search%' OR MONTANT_LIQUIDATION LIKE '%$var_search%')") : '';

    //condition pour le query principale
    $conditions = $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $search . " " . $group;

    $requetedebase = "SELECT DESC_TACHE,MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache exec JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID." AND EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      $sub_array = array();
      $sub_array[] = $row->DESC_TACHE;
      $sub_array[] = $row->MONTANT_LIQUIDATION;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);
  }

  //get liste liquidation salaire a valider
  function index_A_valider()
  {
    $data = $this->urichk();
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

    if($session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE')!=1)
    {
     return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getcat  = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getcat = "CALL getTable('" .$getcat. "');";
    $data['categorie'] = $this->ModelPs->getRequete($getcat);

    $getmois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois ORDER BY MOIS_ID ASC';
    $getmois = "CALL getTable('" .$getmois. "');";
    $data['mois'] = $this->ModelPs->getRequete($getmois);

    $nbr_liqu=$this->count_liquidation_salaire();
    $data['nbr_liqu_salaire']=$nbr_liqu['nbr_liqu_salaire'];
    $data['nbr_liq_a_valide']=$nbr_liqu['nbr_liq_a_valide'];
    $data['nbr_liq_deja_valide']=$nbr_liqu['nbr_liq_deja_valide'];
    $data['nbr_liq_deja_fait']=$nbr_liqu['nbr_liq_deja_fait'];
    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Liste_A_Valider_View',$data);
  }

  //listing liquidation salaire a valider
  public function listing_A_Valide()
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    
    if (!empty($CATEGORIE_SALAIRE_ID)) 
    {
      $critere1 = " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    if (!empty($MOIS_ID))
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }
    
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESC_MOIS', 1,1, 'LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_MOIS LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      $bouton = "<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/Liquidation_Salaire/add_confirm'."/".md5($row->EXECUTION_BUDGETAIRE_ID))."' ><span class='fa fa-arrow-up'></span></a>";

      //Nombre d'institution
      $count_inst = "SELECT INSTITUTION_ID FROM execution_budgetaire_salaire_sous_titre WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
      $count_inst = 'CALL `getTable`("'.$count_inst.'");';
      $nbre_inst = $this->ModelPs->getRequete($count_inst);
      $nbre_inst = count($nbre_inst);

      $action = '';
      $sub_array = array();
      $sub_array[] = $row->DESC_MOIS;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_inst(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_inst."</a></center>";
      $sub_array[] = $nbr;
      $sub_array[] = number_format($row->LIQUIDATION,$this->get_precision($row->LIQUIDATION),'.',' ');
      $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";
      $sub_array[] = $bouton;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //listing tache par inst
  public function listing_inst($EXECUTION_BUDGETAIRE_ID)
  {
    $bouton = '';
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $EXECUTION_BUDGETAIRE_ID=$EXECUTION_BUDGETAIRE_ID;

    $requetedebase = "SELECT execution_budgetaire_salaire_sous_titre.EXECUTION_BUDGETAIRE_ID,inst.INSTITUTION_ID,inst.DESCRIPTION_INSTITUTION,typ.DESC_TYPE_INSTITUTION,exec.CATEGORIE_SALAIRE_ID,CODE_INSTITUTION FROM execution_budgetaire_salaire_sous_titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_salaire_sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=execution_budgetaire_salaire_sous_titre.INSTITUTION_ID JOIN inst_types_institution typ ON typ.TYPE_INSTITUTION_ID=inst.TYPE_INSTITUTION_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
    $query_secondaire = 'CALL getTable("' . $requetedebase . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $html='';
    $u=0;
    $total_emp_end1=0;
    $total_code_1_end1=0;
    $total_code_2_end1=0;
    $total_code_3_end1=0;
    $total_code_4_end1=0;
    $total_INSS_P_end1=0;
    $total_INSS_RP_end1=0;
    $total_ONPR_end1=0;
    $total_MFP_end1=0;
    $total_IMPOT_end1=0;
    $total_RETENUES_end1=0;
    $total_NET_end1=0;
    foreach($fetch_actions as $row)
    {
      $u+=1;
      $html.='<div class="row">
          <div class="col-md-12" style="font-size:20px"><a onclick="hidetable('.$u.')"><i id="chevron'.$u.'" class="fa fa-chevron-down"></i></a>'.$row->DESC_TYPE_INSTITUTION.' : '.'<strong>'.$row->CODE_INSTITUTION.' '.$row->DESCRIPTION_INSTITUTION.'</strong></div>';
      
      $html.='<table class="table table-striped" id="table'.$u.'">
        <br>        
        <tr>
          <th>Dep</th>
          <th>Emp</th>';
          $CODE_SOUS_LITTERA_1='';
          $CODE_SOUS_LITTERA_2='';
          $CODE_SOUS_LITTERA_3='';
          $CODE_SOUS_LITTERA_4='';

          if ($row->CATEGORIE_SALAIRE_ID==1) 
          {
            $CODE_SOUS_LITTERA_1='61110';
            $CODE_SOUS_LITTERA_2='61160';
            $CODE_SOUS_LITTERA_3='61140';
            $CODE_SOUS_LITTERA_4='61610';

            $html.='<th>61110</th>
            <th>61160</th>
            <th>61140</th>
            <th>61610</th>';
          }
          elseif ($row->CATEGORIE_SALAIRE_ID==2)
          {
            $CODE_SOUS_LITTERA_1='61210';
            $CODE_SOUS_LITTERA_2='61240';
            $CODE_SOUS_LITTERA_3='61260';
            $CODE_SOUS_LITTERA_4='61620';
            $html.='<th>61210</th>
            <th>61240</th>
            <th>61260</th>
            <th>61620</th>';
          }
      $html.='<th>INSS_P</th>
        <th>INSS_RP</th>
        <th>ONPR</th>
        <th>MFP</th>
        <th>IMPOT</th>
        <th>RETENUES</th>
        <th>NET</th>
      </tr>
      ';
      
      $st="SELECT st.SOUS_TUTEL_ID,inst_st.CODE_SOUS_TUTEL,st.INSS_P,st.INSS_RP,st.ONPR,st.MFP,st.IMPOT,st.AUTRES_RETENUS,st.NET,st.QTE_FONCTION_PUBLIQUE FROM execution_budgetaire_salaire_sous_titre st JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=st.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_sous_tutel inst_st ON st.SOUS_TUTEL_ID=inst_st.SOUS_TUTEL_ID WHERE 1 AND st.EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." AND st.INSTITUTION_ID=".$row->INSTITUTION_ID." GROUP BY st.SOUS_TUTEL_ID";
      $st = 'CALL getTable("' . $st . '");';
      $st = $this->ModelPs->datatable($st);

      $total_emp=0;
      $total_code_1=0;
      $total_code_2=0;
      $total_code_3=0;
      $total_code_4=0;
      $total_INSS_P=0;
      $total_INSS_RP=0;
      $total_ONPR=0;
      $total_MFP=0;
      $total_IMPOT=0;
      $total_RETENUES=0;
      $total_NET=0;

      $total_emp_end=0;
      $total_code_1_end=0;
      $total_code_2_end=0;
      $total_code_3_end=0;
      $total_code_4_end=0;
      $total_INSS_P_end=0;
      $total_INSS_RP_end=0;
      $total_ONPR_end=0;
      $total_MFP_end=0;
      $total_IMPOT_end=0;
      $total_RETENUES_end=0;
      $total_NET_end=0;

      foreach($st as $value)
      {
        //get montant par code economique
        $montant_1="SELECT SUM(MONTANT_LIQUIDATION) AS total_1 FROM execution_budgetaire_execution_tache exec_tache JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." AND tache.SOUS_TUTEL_ID=".$value->SOUS_TUTEL_ID." AND CODE_SOUS_LITTERA =".$CODE_SOUS_LITTERA_1;
        $montant_1 = 'CALL getTable("' . $montant_1 . '");';
        $montant_1 = $this->ModelPs->getRequeteOne($montant_1);

        $montant_2="SELECT SUM(MONTANT_LIQUIDATION) AS total_2 FROM execution_budgetaire_execution_tache exec_tache JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." AND tache.SOUS_TUTEL_ID=".$value->SOUS_TUTEL_ID." AND CODE_SOUS_LITTERA =".$CODE_SOUS_LITTERA_2;
        $montant_2 = 'CALL getTable("' . $montant_2 . '");';
        $montant_2 = $this->ModelPs->getRequeteOne($montant_2);

        $montant_3="SELECT SUM(MONTANT_LIQUIDATION) AS total_3 FROM execution_budgetaire_execution_tache exec_tache JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." AND tache.SOUS_TUTEL_ID=".$value->SOUS_TUTEL_ID." AND CODE_SOUS_LITTERA =".$CODE_SOUS_LITTERA_3;
        $montant_3 = 'CALL getTable("' . $montant_3 . '");';
        $montant_3 = $this->ModelPs->getRequeteOne($montant_3);

        $montant_4="SELECT SUM(MONTANT_LIQUIDATION) AS total_4 FROM execution_budgetaire_execution_tache exec_tache JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." AND tache.SOUS_TUTEL_ID=".$value->SOUS_TUTEL_ID." AND CODE_SOUS_LITTERA =".$CODE_SOUS_LITTERA_4;
        $montant_4 = 'CALL getTable("' . $montant_4 . '");';
        $montant_4 = $this->ModelPs->getRequeteOne($montant_4);

        $html.='<tr>
            <td style="color:#333">'.$row->CODE_INSTITUTION.$value->CODE_SOUS_TUTEL.'</td>
            <td style="color:#333">'.$value->QTE_FONCTION_PUBLIQUE.'</td>
            <td style="color:#333">'.$montant_1['total_1'].'</td>
            <td style="color:#333">'.$montant_2['total_2'].'</td> 
            <td style="color:#333">'.$montant_3['total_3'].'</td>
            <td style="color:#333">'.$montant_4['total_4'].'</td>
            <td style="color:#333">'.$value->INSS_P.'</td>
            <td style="color:#333">'.$value->INSS_RP.'</td>
            <td style="color:#333">'.$value->ONPR.'</td>
            <td style="color:#333">'.$value->MFP.'</td>
            <td style="color:#333">'.$value->IMPOT.'</td>
            <td style="color:#333">'.$value->AUTRES_RETENUS.'</td>
            <td>'.$value->NET.'</td>
          </tr>';
        $total_emp +=$value->QTE_FONCTION_PUBLIQUE;
        $total_code_1 +=$montant_1['total_1'];
        $total_code_2 +=$montant_2['total_2'];
        $total_code_3 +=$montant_3['total_3'];
        $total_code_4 +=$montant_4['total_4'];
        $total_INSS_P +=$value->INSS_P;
        $total_INSS_RP +=$value->INSS_RP;
        $total_ONPR +=$value->ONPR;
        $total_MFP +=$value->MFP;
        $total_IMPOT +=$value->IMPOT;
        $total_RETENUES +=$value->AUTRES_RETENUS;
        $total_NET +=$value->NET;

        $total_emp_end +=$value->QTE_FONCTION_PUBLIQUE;
        $total_code_1_end +=$montant_1['total_1'];
        $total_code_2_end +=$montant_2['total_2'];
        $total_code_3_end +=$montant_3['total_3'];
        $total_code_4_end +=$montant_4['total_4'];
        $total_INSS_P_end +=$value->INSS_P;
        $total_INSS_RP_end +=$value->INSS_RP;
        $total_ONPR_end +=$value->ONPR;
        $total_MFP_end +=$value->MFP;
        $total_IMPOT_end +=$value->IMPOT;
        $total_RETENUES_end +=$value->AUTRES_RETENUS;
        $total_NET_end +=$value->NET;
      }

      $html.='<tr>
          <td style="color:green">TOTAL</td>
          <td style="color:green">'.$total_emp.'</td>
          <td style="color:green">'.$total_code_1.'</td>
          <td style="color:green">'.$total_code_2.'</td>
          <td style="color:green">'.$total_code_3.'</td> 
          <td style="color:green">'.$total_code_4.'</td>
          <td style="color:green">'.$total_INSS_P.'</td>
          <td style="color:green">'.$total_INSS_RP.'</td>
          <td style="color:green">'.$total_ONPR.'</td>
          <td style="color:green">'.$total_MFP.'</td>
          <td style="color:green">'.$total_IMPOT.'</td>
          <td style="color:green">'.$total_RETENUES.'</td>
          <td style="color:green">'.$total_NET.'</td>
        </tr></table></div>';  
      $total_emp_end1 +=$total_emp_end;
      $total_code_1_end1 +=$total_code_1_end;
      $total_code_2_end1 +=$total_code_2_end;
      $total_code_3_end1 +=$total_code_3_end;
      $total_code_4_end1 +=$total_code_4_end;
      $total_INSS_P_end1 +=$total_INSS_P_end;
      $total_INSS_RP_end1 +=$total_INSS_RP_end;
      $total_ONPR_end1 +=$total_ONPR_end;
      $total_MFP_end1 +=$total_MFP_end;
      $total_IMPOT_end1 +=$total_IMPOT_end;
      $total_RETENUES_end1 +=$total_RETENUES_end;
      $total_NET_end1 +=$total_NET_end;
    }

    $tot1=$total_code_1_end1+$total_code_2_end1+$total_code_3_end1+$total_code_4_end1;
    $tot2=$total_INSS_P_end1+$total_INSS_RP_end1+$total_ONPR_end1+$total_MFP_end1+$total_IMPOT_end1+$total_RETENUES_end1+$total_NET_end1;
    $html.='
    <div class="row">
      <table class="table table-striped" border="1">
        <tr style="position: sticky;top: 0;background-color: #f9f9f9;">
        <td style="color:#ccc">GROUPE</td>
        <td style="color:#ccc">'.$total_emp_end1.'</td>
        <td style="color:#ccc">'.$total_code_1_end1.'</td>
        <td style="color:#ccc">'.$total_code_2_end1.'</td>
        <td style="color:#ccc">'.$total_code_3_end1.'</td> 
        <td style="color:#ccc">'.$total_code_4_end1.'</td>
        <td style="color:#ccc">'.$total_INSS_P_end1.'</td>
        <td style="color:#ccc">'.$total_INSS_RP_end1.'</td>
        <td style="color:#ccc">'.$total_ONPR_end1.'</td>
        <td style="color:#ccc">'.$total_MFP_end1.'</td>
        <td style="color:#ccc">'.$total_IMPOT_end1.'</td>
        <td style="color:#ccc">'.$total_RETENUES_end1.'</td>
        <td style="color:#ccc">'.$total_NET_end1.'</td>
      </tr>
      <tr style="position: sticky;top: 0;background-color: #f9f9f9;">
        <td></td>
        <td>TOTAL</td>
        <td colspan="4"><center>'.$tot1.'</center></td>
        <td colspan="7"><center>'.$tot2.'</center></td>
      </tr>';   
    $html.='
      </table>
      </div>
      ';

    $output = array(
    "html" => $html,
    );

    return $this->response->setJSON($output);
  }

  //get liste liquidation salaire deja valider
  function index_Deja_valider()
  {
    $data = $this->urichk();
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

    if($session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE')!=1)
    {
     return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getcat  = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getcat = "CALL getTable('" .$getcat. "');";
    $data['categorie'] = $this->ModelPs->getRequete($getcat);

    $getmois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois ORDER BY MOIS_ID ASC';
    $getmois = "CALL getTable('" .$getmois. "');";
    $data['mois'] = $this->ModelPs->getRequete($getmois);

    $nbr_liqu=$this->count_liquidation_salaire();
    $data['nbr_liqu_salaire']=$nbr_liqu['nbr_liqu_salaire'];
    $data['nbr_liq_a_valide']=$nbr_liqu['nbr_liq_a_valide'];
    $data['nbr_liq_deja_valide']=$nbr_liqu['nbr_liq_deja_valide'];
    $data['nbr_liq_deja_fait']=$nbr_liqu['nbr_liq_deja_fait'];
    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Liste_Deja_Valider_View',$data);
  }

  //listing liquidation salaire deja valider
  public function listing_Deja_Valide()
  {
    if(!empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id =$this->session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($this->session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE')!=1)
    {
     return redirect('Login_Ptba/homepage'); 
    }

    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $bouton = '';
    //Filtres de la liste
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";

    if (!empty($CATEGORIE_SALAIRE_ID)) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    if (!empty($MOIS_ID))
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESC_MOIS', 1,1, 'LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_MOIS LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      //Nombre d'institution
      $count_tache = "SELECT INSTITUTION_ID AS nbr FROM execution_budgetaire_salaire_sous_titre WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);
      $nbre_tache = count($nbre_tache);

      $action = '';
      $sub_array = array();
      $sub_array[] = $row->DESC_MOIS;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_inst(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_tache."</a></center>";
      $sub_array[] = $nbr;
      $sub_array[] = number_format($row->LIQUIDATION,$this->get_precision($row->LIQUIDATION),'.',' ');
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output); //echo json_encode($output);
  }

  //exporter la liste des liquidations salaire deja fait
  public function exporter_Excel_deja_fait($CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";

    if ($MOIS_ID!=0)
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if ($CATEGORIE_SALAIRE_ID!=0) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>10 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Liquidation salaire déjà fait'.$code);

    // Fusionner les cellules de A1 à H1
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', 'Mois');
    $sheet->setCellValue('B2', 'Catégorie');
    $sheet->setCellValue('C2', 'Institutions');
    $sheet->setCellValue('D2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    {
      $rows++;
      //Nombre d'institution
      $count_tache = "SELECT inst.INSTITUTION_ID,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_salaire_sous_titre st JOIN inst_institutions inst ON inst.INSTITUTION_ID=st.INSTITUTION_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);

      $sheet->setCellValue('A' . $rows, $row->DESC_MOIS);
      $sheet->setCellValue('B' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      foreach ($nbre_tache as $key)
      {
        $sheet->setCellValue('C' . $rows, $key->DESCRIPTION_INSTITUTION);
      }
      $sheet->setCellValue('D' . $rows, $row->LIQUIDATION);
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Liquidation Salaire déjà Fait'.$code.'.xlsx');
  }

  //export liste liquidation salaire a corrige
  public function exporter_Excel_A_corriger($CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $critere1 = "";

    if ($MOIS_ID!=0)
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if ($CATEGORIE_SALAIRE_ID!=0) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID,exec.EXECUTION_BUDGETAIRE_ID,st.SOUS_TUTEL_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL,categ.DESC_CATEGORIE_SALAIRE,TOTAL_SALAIRE FROM execution_budgetaire exec JOIN execution_budgetaire_salaire_sous_titre sous_titre ON exec.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=sous_titre.INSTITUTION_ID JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sous_titre.SOUS_TUTEL_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE 1 AND A_CORRIGER=1";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Liquidation salaire à corriger'.$code);

    // Fusionner les cellules de A1 à H1
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', 'Institution');
    $sheet->setCellValue('B2', 'Sous titre');
    $sheet->setCellValue('C2', 'Catégorie');
    $sheet->setCellValue('D2', 'Tâche');
    $sheet->setCellValue('E2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    { 
      $rows++;
      //Nombre des bon d'engagement
      $count_tache = "SELECT exec.PTBA_TACHE_ID,DESC_TACHE FROM execution_budgetaire_execution_tache exec JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE tache.SOUS_TUTEL_ID=".$row->SOUS_TUTEL_ID." AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);

      $sheet->setCellValue('A' . $rows, $row->CODE_INSTITUTION."-".$row->DESCRIPTION_INSTITUTION);
      ;
      $sheet->setCellValue('B' . $rows, $row->DESCRIPTION_SOUS_TUTEL);
      $sheet->setCellValue('C' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      foreach ($nbre_tache as $key)
      {
        $sheet->setCellValue('D' . $rows, $key->DESC_TACHE);
      }
      $sheet->setCellValue('E' . $rows, $row->TOTAL_SALAIRE);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Liquidation Salaire à corriger'.$code.'.xlsx');
  }

  //export liste liquidation salaire a valider
  public function exporter_Excel_A_valider($CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $critere1 = "";

    if ($MOIS_ID!=0)
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if ($CATEGORIE_SALAIRE_ID!=0) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Liquidation salaire à valider'.$code);

    // Fusionner les cellules de A1 à H1
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', 'Mois');
    $sheet->setCellValue('B2', 'Catégorie');
    $sheet->setCellValue('C2', 'Institutions');
    $sheet->setCellValue('D2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    { 
      $rows++;

      $count_inst = "SELECT st.INSTITUTION_ID,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_salaire_sous_titre st JOIN inst_institutions inst ON inst.INSTITUTION_ID=st.INSTITUTION_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY inst.INSTITUTION_ID";
      $count_inst = 'CALL `getTable`("'.$count_inst.'");';
      $nbre_inst = $this->ModelPs->getRequete($count_inst);

      $sheet->setCellValue('A' . $rows, $row->DESC_MOIS);
      $sheet->setCellValue('B' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      foreach ($nbre_inst as $key)
      {
        $sheet->setCellValue('C' . $rows, $key->DESCRIPTION_INSTITUTION);
      }
      $sheet->setCellValue('D' . $rows, $row->LIQUIDATION);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Liquidation Salaire à valider'.$code.'.xlsx');
  }

  //export liste liquidation salaire deja valider
  public function exporter_Excel_deja_valider($CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $critere1 = "";

    if ($MOIS_ID!=0)
    {
      $critere1 .= " AND exec.MOIS_ID=".$MOIS_ID;
    }

    if ($CATEGORIE_SALAIRE_ID!=0) 
    {
      $critere1 .= " AND exec.CATEGORIE_SALAIRE_ID=" . $CATEGORIE_SALAIRE_ID;
    }

    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND exec.DATE_DEMANDE >= '".$DATE_DEBUT."' AND exec.DATE_DEMANDE <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.LIQUIDATION FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Liquidation salaire déjà valider'.$code);

    // Fusionner les cellules de A1 à H1
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', 'Mois');
    $sheet->setCellValue('B2', 'Catégorie');
    $sheet->setCellValue('C2', 'Institutions');
    $sheet->setCellValue('D2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    { 
      $rows++;

      $count_tache = "SELECT inst.INSTITUTION_ID ,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_salaire_sous_titre st JOIN inst_institutions inst ON inst.INSTITUTION_ID=st.INSTITUTION_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY st.INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);

      $sheet->setCellValue('A' . $rows, $row->DESC_MOIS);
      $sheet->setCellValue('B' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      foreach ($nbre_tache as $key)
      {
        $sheet->setCellValue('C' . $rows, $key->DESCRIPTION_INSTITUTION);
      }
      $sheet->setCellValue('D' . $rows, $row->LIQUIDATION);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Liquidation Salaire deja valider'.$code.'.xlsx');
  }

	public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  private function get_precision($value=0)
  {
    $string = strval($value);
    $number=explode('.',$string)[1] ?? '';
    $precision='';
    for($i=1;$i<=strlen($number);$i++)
    {
      $precision=$i;
    }
    if(!empty($precision)) 
    {
      return $precision;
    }
    else
    {
      return 0;
    }    
  }
}
?>