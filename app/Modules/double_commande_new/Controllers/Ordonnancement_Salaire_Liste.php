<?php
/*
*NDERAGAKURA ALAIN CHARBEL
*Titre: Liste des ordonnancement cas salaire
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 9 septembre,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Ordonnancement_Salaire_Liste extends BaseController
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

	//get liste ordonnancement salaire a faire
	function index_A_Faire()
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


		if($session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE')!=1)
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

		$gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE 1 ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getCateg = "CALL `getTable`('" . $getCateg . "');";
    $data['getCateg'] = $this->ModelPs->getRequete($getCateg);

		$nbr_liqu=$this->count_ordonnancement_salaire();
		$data['nbr_ordo_salaire']=$nbr_liqu['nbr_ordo_salaire'];
    $data['nbr_ordo_deja_fait']=$nbr_liqu['nbr_ordo_deja_fait'];
		return view('App\Modules\double_commande_new\Views\Ordonnancement_Salaire_Liste_A_Faire_View',$data);
	}

	//listing ordonnancement salaire a faire
	public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $bouton = '';
    //Filtres de la liste
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1 = "";
    $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
    $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
    $MOIS_ID=$this->request->getPost('MOIS_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    if(!empty($TYPE_SALAIRE_ID))
    {
       $critere1 .=' AND exec.TYPE_SALAIRE_ID='.$TYPE_SALAIRE_ID;
    }

    if(!empty($CATEGORIE_SALAIRE_ID))
    {
      $critere1 .=' AND exec.CATEGORIE_SALAIRE_ID='.$CATEGORIE_SALAIRE_ID;
    }

    if(!empty($MOIS_ID))
    {
      $critere1 .=' AND exec.MOIS_ID='.$MOIS_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
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

    $order_column = array('mois.DESC_MOIS', 'annee.ANNEE_DESCRIPTION','categ.DESC_CATEGORIE_SALAIRE','LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY mois.MOIS_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (mois.DESC_MOIS LIKE '%$var_search%' OR annee.ANNEE_DESCRIPTION LIKE '%$var_search%' OR categ.DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID,annee.ANNEE_DESCRIPTION,mois.DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,salarie.DESC_TYPE_SALAIRE,LIQUIDATION,det.DATE_LIQUIDATION FROM execution_budgetaire_titre_decaissement exec_td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID=14 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      $bouton = "<a class='btn btn-primary btn-sm' title='' href='".base_url('double_commande_new/Ordonnancement_Salaire/add'."/".md5($row->EXECUTION_BUDGETAIRE_ID))."' ><span class='fa fa-arrow-up'></span></a>";

      //Nombre d'institution
      $count_tache = "SELECT INSTITUTION_ID FROM execution_budgetaire_salaire_sous_titre WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);
      $nbre_tache = count($nbre_tache);

      $action = '';
      $sub_array = array();
      $sub_array[] = $row->ANNEE_DESCRIPTION;
      $sub_array[] =$row->DESC_TYPE_SALAIRE;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $sub_array[] = $row->DESC_MOIS;
      $nbr="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_inst(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_tache."</a></center>";
      $sub_array[] = $nbr;
      $sub_array[] = number_format($row->LIQUIDATION,$this->get_precision($row->LIQUIDATION),'.',' ');
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

  //export liste ordonnancement salaire a faire
  public function exporter_Excel_Ordo_a_faire($TYPE_SALAIRE_ID,$CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $TYPE_SALAIRE_ID!=$this->request->getPost('TYPE_SALAIRE_ID');
    $CATEGORIE_SALAIRE_ID = $this->request->getPost('CATEGORIE_SALAIRE_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $critere1 = "";

    if($TYPE_SALAIRE_ID!=0)
    {
       $critere1 .=' AND exec.TYPE_SALAIRE_ID='.$TYPE_SALAIRE_ID;
    }

    if($CATEGORIE_SALAIRE_ID!=0)
    {
      $critere1 .=' AND exec.CATEGORIE_SALAIRE_ID='.$CATEGORIE_SALAIRE_ID;
    }

    if($MOIS_ID!=0)
    {
      $critere1 .=' AND exec.MOIS_ID='.$MOIS_ID;
    }
    
    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID,annee.ANNEE_DESCRIPTION,mois.DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,salarie.DESC_TYPE_SALAIRE,LIQUIDATION, det.DATE_LIQUIDATION FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement exec_td ON exec_td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID=14 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Ordonnancement salaire à faire'.$code);

    // Fusionner les cellules de A1 à F1
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A2', 'Année budgétaire');
    $sheet->setCellValue('B2', 'Mois');
    $sheet->setCellValue('C2', 'Type Salarié');    
    $sheet->setCellValue('D2', 'Catégorie');
    $sheet->setCellValue('E2', 'Institutions');
    $sheet->setCellValue('F2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    { 
      $rows++;

      $count_tache = "SELECT inst.INSTITUTION_ID ,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_salaire_sous_titre st JOIN inst_institutions inst ON inst.INSTITUTION_ID=st.INSTITUTION_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID." GROUP BY inst.INSTITUTION_ID";
      $count_tache = 'CALL `getTable`("'.$count_tache.'");';
      $nbre_tache = $this->ModelPs->getRequete($count_tache);

      $sheet->setCellValue('A' . $rows, $row->ANNEE_DESCRIPTION);
      $sheet->setCellValue('B' . $rows, $row->DESC_MOIS);
      $sheet->setCellValue('C' . $rows, $row->DESC_TYPE_SALAIRE);
      $sheet->setCellValue('D' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      foreach ($nbre_tache as $key)
      {
        $sheet->setCellValue('E' . $rows, $key->DESCRIPTION_INSTITUTION);
      }
      $sheet->setCellValue('F' . $rows, $row->LIQUIDATION);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Ordonancement Salaire a faire'.$code.'.xlsx');
  }

  //get liste ordonnancement salaire deja fait
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

    if($session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE')!=1)
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

    $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE 1 ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getCateg = "CALL `getTable`('" . $getCateg . "');";
    $data['getCateg'] = $this->ModelPs->getRequete($getCateg);

    $nbr_liqu=$this->count_ordonnancement_salaire();
    $data['nbr_ordo_salaire']=$nbr_liqu['nbr_ordo_salaire'];
    $data['nbr_ordo_deja_fait']=$nbr_liqu['nbr_ordo_deja_fait'];
    return view('App\Modules\double_commande_new\Views\Ordonnancement_Salaire_Liste_Deja_Fait_View',$data);
  }

  //listing liquidation salaire deja fait
  public function listing_Deja_Fait()
  {
    if($this->session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bouton = '';
    //Filtres de la liste
    $critere1 = "";
    $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
    $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
    $MOIS_ID=$this->request->getPost('MOIS_ID');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    if(!empty($TYPE_SALAIRE_ID))
    {
       $critere1 .=' AND exec.TYPE_SALAIRE_ID='.$TYPE_SALAIRE_ID;
    }

    if(!empty($CATEGORIE_SALAIRE_ID))
    {
      $critere1 .=' AND exec.CATEGORIE_SALAIRE_ID='.$CATEGORIE_SALAIRE_ID;
    }

    if(!empty($MOIS_ID))
    {
      $critere1 .=' AND exec.MOIS_ID='.$MOIS_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere1.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere1.=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
    }

    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('annee.ANNEE_DESCRIPTION','mois.DESC_MOIS','DESC_TYPE_SALAIRE','categ.DESC_CATEGORIE_SALAIRE','LIQUIDATION');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY mois.MOIS_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND ( DESC_TYPE_SALAIRE LIKE '%$var_search%' OR mois.DESC_MOIS LIKE '%$var_search%' OR annee.ANNEE_DESCRIPTION LIKE '%$var_search%' OR categ.DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR LIQUIDATION LIKE '%$var_search%')") : '';

    $critaire = $critere1;
    //condition pour le query principale
    $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $critaire . " " . $search . " " . $group;

    $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID ,salarie.DESC_TYPE_SALAIRE,annee.ANNEE_DESCRIPTION,mois.DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,LIQUIDATION, det.DATE_LIQUIDATION FROM execution_budgetaire_titre_decaissement exec_td JOIN execution_budgetaire exec ON exec_td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID>14 AND exec_td.ETAPE_DOUBLE_COMMANDE_ID<>15 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3 AND IS_TD_NET=0";

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
      $sub_array[] = $row->ANNEE_DESCRIPTION;
      $sub_array[] = $row->DESC_MOIS;      
      $sub_array[] = $row->DESC_TYPE_SALAIRE;
      $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
      $sub_array[] = number_format($row->LIQUIDATION,$this->get_precision($row->LIQUIDATION),'.',' ');
      // $sub_array[] = $bouton;
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

  //export liste ordonnancement salaire a faire
  public function exporter_Excel_Ordo_deja_fait($TYPE_SALAIRE_ID,$CATEGORIE_SALAIRE_ID,$MOIS_ID,$DATE_DEBUT,$DATE_FIN)
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

    if($TYPE_SALAIRE_ID!=0)
    {
       $critere1 .=' AND exec.TYPE_SALAIRE_ID='.$TYPE_SALAIRE_ID;
    }

    if($CATEGORIE_SALAIRE_ID!=0)
    {
      $critere1 .=' AND exec.CATEGORIE_SALAIRE_ID='.$CATEGORIE_SALAIRE_ID;
    }

    if($MOIS_ID!=0)
    {
      $critere1 .=' AND exec.MOIS_ID='.$MOIS_ID;
    }
    
    $code='';
    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $critere1 .=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.date('d-m-Y');
    }

    if ($DATE_FIN!=0 && $DATE_DEBUT!=0)
    {
      $critere1 .=" AND det.DATE_LIQUIDATION >= '".$DATE_DEBUT."' AND det.DATE_LIQUIDATION <= '".$DATE_FIN."'";
      $code=' Du '.date('d-m-Y',strtotime($DATE_DEBUT)).' Au '.$DATE_FIN;
    }

    $critaire = $critere1;

    $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID ,salarie.DESC_TYPE_SALAIRE,annee.ANNEE_DESCRIPTION,mois.DESC_MOIS,categ.DESC_CATEGORIE_SALAIRE,LIQUIDATION, det.DATE_LIQUIDATION FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement exec_td ON exec_td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID>14 AND exec_td.ETAPE_DOUBLE_COMMANDE_ID<>15 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";

    $requetedebases = $requetedebase . " " . $critaire;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Définir le titre dans la cellule A1
    $sheet->setCellValue('A1', 'Ordonnancement salaire déjà fait'.$code);

    // Fusionner les cellules de A1 à H1
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', 'Année budgétaire');
    $sheet->setCellValue('B2', 'Mois');
    $sheet->setCellValue('C2', 'Type Salarié');    
    $sheet->setCellValue('D2', 'Catégorie');
    $sheet->setCellValue('E2', 'Liquidation');

    $rows=2;
    foreach($fetch_actions as $row)
    { 
      $rows++;
      $sheet->setCellValue('B' . $rows, $row->ANNEE_DESCRIPTION);
      $sheet->setCellValue('B' . $rows, $row->DESC_MOIS);
      $sheet->setCellValue('C' . $rows, $row->DESC_TYPE_SALAIRE);
      $sheet->setCellValue('D' . $rows, $row->DESC_CATEGORIE_SALAIRE);
      $sheet->setCellValue('E' . $rows, $row->LIQUIDATION);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('Ordonancement Salaire deja fait'.$code.'.xlsx');
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