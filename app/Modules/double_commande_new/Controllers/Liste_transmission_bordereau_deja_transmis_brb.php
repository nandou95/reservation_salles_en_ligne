<?php
/**Eric SINDAYIGAYA
 *Titre: Liste des bordereaux deja transmis a brb
 *Numero de telephone: +257 62 04 03 00
 *WhatsApp: +257 62 04 03 00
 *Email pro: sinda.eric@mediabox.bi
 *Email pers: ericjamesbarinako33@gmail.com
 *Date: 15 fev 2024
 **/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');
class Liste_transmission_bordereau_deja_transmis_brb extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function index()
	{
		$data = $this->urichk();
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) {
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $annee_budgetaire_en_cours=$this->get_annee_budgetaire();

    $ANNEE_BUDGETAIRE_ID=$annee_budgetaire_en_cours;
    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $DATE_DEBUT=0;
    $DATE_FIN=0;

    $paiement = $this->count_paiement($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
    
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];
    
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_budgetaire_en_cours,'ANNEE_BUDGETAIRE_ID ASC');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);
    $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'CODE_INSTITUTION ASC');
      $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);
      $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
      $user_inst_res = 'CALL getTable("'.$user_inst.'");';
      $institutions_user = $this->ModelPs->getRequete($user_inst_res);
      $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
      $data['first_element_id'] = $INSTITUTION_ID;

		return view('App\Modules\double_commande_new\Views\Liste_transmission_bordereau_deja_transmis_brb_view.php',$data); 
	}

	//récupération du sous titre par rapport à l'institution
  function get_sous_titre($INSTITUTION_ID = 0)
  {
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

    $html = '<option value="">' . lang('messages_lang.labelle_selecte') . '</option>';
    foreach ($sous_tutelle as $key)
    {
      $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
    }

    $output = array(
      "sous_tutel" => $html,
    );

    return $this->response->setJSON($output);
  }

	/** fonction pour lister les bordereaux a transmettre */
	public function listing()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $critere1 = "";
    $critere2 = "";
    $critere3 = "";
    $critere4 = "";
    $critere_annee = "";
    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
    	$critere_annee.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
    }

    if (!empty($INSTITUTION_ID)) {
      $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
      if (!empty($SOUS_TUTEL_ID)) {
        $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
      }
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere3=" AND td.DATE_ELABORATION_TD >= ".$DATE_DEBUT."";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere4=" AND td.DATE_ELABORATION_TD >= ".$DATE_DEBUT." AND td.DATE_ELABORATION_TD <= ".$DATE_FIN."";
    }

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
	
		$limit = 'LIMIT 0,1000';
		if ($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}
		$order_by = "";
		$order_column = array('NUMERO_DOCUMENT','ebbtn.NUMERO_BORDEREAU_TRANSMISSION','dev.DESC_DEVISE_TYPE','MONTANT_PAIEMENT','DESCRIPTION_INSTITUTION');
		$search = !empty($_POST['search']['value']) ? (' AND (bon_titre.NUMERO_DOCUMENT LIKE "%' . $var_search . '%" OR td.MONTANT_PAIEMENT LIKE "%' . $var_search . '%" OR inst.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR ebbtn.NUMERO_BORDEREAU_TRANSMISSION LIKE "%' . $var_search . '%" OR dev.DESC_DEVISE_TYPE LIKE "%' . $var_search . '%")') : '';
		
		$query_principal="SELECT DISTINCT(bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),bon_titre.NUMERO_DOCUMENT,dev.DESC_DEVISE_TYPE,td.MONTANT_PAIEMENT,inst.DESCRIPTION_INSTITUTION,ebbtn.NUMERO_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon_titre.TYPE_DOCUMENT_ID = 2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID IN (1,2) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere_annee."";

		//condition pour le query principale
		$conditions = $search.' '.$order_by.' '.$limit;
		  // condition pour le query filter
		$conditionsfilter = $search;
		$requetedebase=$query_principal.$conditions;
		$requetedebasefilter=$query_principal.$conditionsfilter;
		
		$query_secondaire = "CALL `getTable`('".$requetedebase."');";
		  // echo $query_secondaire;
		$fetch_intrants = $this->ModelPs->datatable($query_secondaire);
		$u = 0;
		$data = array();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		
		foreach ($fetch_intrants as $row)
		{
			$u++;
			$sub_array = array();
			$sub_array[] = $row->NUMERO_DOCUMENT;
			$sub_array[] = $row->NUMERO_BORDEREAU_TRANSMISSION;
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = !empty($row->MONTANT_PAIEMENT) ? number_format($row->MONTANT_PAIEMENT,'2',',',' '):0;
			$sub_array[] = $row->DESCRIPTION_INSTITUTION;		
			$data[] = $sub_array;
		}
		
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $query_principal . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count( $recordsTotal),
			"recordsFiltered" => count( $recordsFiltered),
			"data" => $data,
		);
		echo json_encode($output);
	}


	// Exporter la Liste des titres de décaissement à transmmettre à la BRB
  function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$ANNEE_BUDGETAIRE_ID=0,$DATE_FIN=0,$DATE_DEBUT=0)
  {

    // $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $session  = \Config\Services::session();
    $user_id=$session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
   
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);
    $critere1='';
    $critere2='';


      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere_annee = "";
      if(!empty($ANNEE_BUDGETAIRE_ID))
      {
      	$critere_annee.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
      }

      if (!empty($INSTITUTION_ID)) {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID)) {
          $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
        }
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere3.=" AND td.DATE_ELABORATION_TD >= ".$DATE_DEBUT."";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= ".$DATE_DEBUT." AND td.DATE_ELABORATION_TD <= ".$DATE_FIN."";
      }
      $critere=$critere1.''.$critere2.''.$critere3.''.$critere4;

      $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    	$query_principal="SELECT DISTINCT(bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),bon_titre.NUMERO_DOCUMENT,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,td.MONTANT_PAIEMENT,inst.DESCRIPTION_INSTITUTION,ebbtn.NUMERO_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon_titre.TYPE_DOCUMENT_ID = 2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID IN (1,2) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$critere." ".$critere2." ".$critere3." ".$critere4." ".$critere_annee."";

    $getData = $this->ModelPs->datatable('CALL getTable("' . $query_principal . '")'); 

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C1', 'TITRES DE DECAISSEMENT DEJA TRANSMIS A LA BRB');
    $sheet->setCellValue('A3', '#');
    $sheet->setCellValue('B3', 'NUMERO DU TD');
    $sheet->setCellValue('C3', 'NUMERO DU BORDEREAU');
    $sheet->setCellValue('D3', 'TYPE DEVISE');
    $sheet->setCellValue('E3', 'MONTANT PAYE');
    $sheet->setCellValue('F3', 'INSTITUTION');

    $rows = 4;
    $i=1;
    foreach ($getData as $key)
    {
      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_DOCUMENT);
      $sheet->setCellValue('C' . $rows, $key->NUMERO_BORDEREAU_TRANSMISSION);
      $sheet->setCellValue('D' . $rows, $key->DESC_DEVISE_TYPE);
      if ($key->DEVISE_TYPE_ID==1) {
				$sheet->setCellValue('E' . $rows, !empty($key->MONTANT_PAIEMENT) ? number_format($key->MONTANT_PAIEMENT,'2',',',' '):0);
			}else{
				$sheet->setCellValue('E' . $rows, !empty($key->MONTANT_PAIEMENT) ? number_format($key->MONTANT_PAIEMENT,'2',',',' '):0);
			}
      $sheet->setCellValue('F' . $rows, $key->DESCRIPTION_INSTITUTION);
     
      $rows++;
      $i++;
    } 

    $code=date('YmdHis');
    $writer = new Xlsx($spreadsheet);
    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('td_a_transmettre_brb'.$code.'.xlsx');

    return redirect('double_commande_new/Liste_transmission_bordereau_deja_transmis_brb');
  }	
}
?>