<?php
namespace App\Modules\dashboard\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/* @author KWIZERA.edmond@mediabox.bi (71407706)
* Dashbord des evolution global
le 29/08/2023 au 19 
*/
//Appel de l'espace de nom du Controllers
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
class Dashboard_TCD_Valeur_Engagement_Vote extends BaseController
{
	protected $session;
	protected $ModelPs;
	public function __construct()
	{ 
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->ModelS = new ModelS();
		$this->session = \Config\Services::session();
	}

	public function index()
	{  
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id ='';
		$inst_connect ='';
		$prof_connect ='';
		$type_connect ='';
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
	    {
	      return redirect('Login_Ptba');
	    }

		    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TCD_VALEUR_PHASE')!=1)
		    {
		     return redirect('Login_Ptba/homepage');
		    }
    
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
			$profil_user=("SELECT `PROFIL_ID` FROM `user_users` WHERE `USER_ID`=".$user_id." ");
			$profil_user_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$profil_user.'")');
			$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
			$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
			$nombre=count($user_connect_req);
			$condition2='';
			$fonct_key2='';
			$inst_connexion='';
			if ($profil_user_req['PROFIL_ID']!=1){
				if ($nombre==1) {
					foreach ($user_connect_req as $value){
						$vision_sel=$value->inst_institutions;
						$inst_connect =$value->INSTITUTION_ID;
						$inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';
						$type_inst1=("SELECT  TYPE_INSTITUTION_ID FROM inst_institutions WHERE  INSTITUTION_ID =".$inst_connect."");
						$type_inst_req1=$this->ModelPs->getRequeteOne('CALL getTable("'.$type_inst1.'")');
						$type_connect=$type_inst_req1['TYPE_INSTITUTION_ID'];
						$type_cond=' AND TYPE_INSTITUTION_ID='.$type_connect;
					}
				}else if ($nombre>1) {
					foreach ($user_connect_req as  $value) {  
						$fonct_key2.=$value->INSTITUTION_ID.',';
					}
					$condition2 =  substr($fonct_key2,0,-1);
					$inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';
					$type_inst=("SELECT DISTINCT TYPE_INSTITUTION_ID FROM inst_institutions WHERE  INSTITUTION_ID IN (".$condition2.") GROUP BY TYPE_INSTITUTION_ID");
					$type_inst_req=$this->ModelPs->getRequete('CALL getTable("'.$type_inst.'")');
					$nombreinst=count($type_inst_req);
					if ($nombreinst==1) {
						$type_cond=' AND TYPE_INSTITUTION_ID IN (SELECT DISTINCT TYPE_INSTITUTION_ID FROM inst_institutions WHERE  INSTITUTION_ID IN ('.$condition2.'))';
						$type_instcon=("SELECT DISTINCT TYPE_INSTITUTION_ID FROM inst_institutions WHERE  INSTITUTION_ID IN (".$condition2.") GROUP BY TYPE_INSTITUTION_ID");
						$type_instcon_req=$this->ModelPs->getRequeteOne('CALL getTable("'.$type_instcon.'")');
						$type_connect=$type_instcon_req['TYPE_INSTITUTION_ID'];
					}else{
						$type_cond='';
					}
				}
			}else{
				$inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value=" ">';
				$type_connect='$TYPE_INSTITUTION_ID';
				$type_cond='';	
			}
		}else{
			return redirect('Login_Ptba');
		}
		$requete_type="SELECT  TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'".lang("messages_lang.admin_perso")."','".lang("messages_lang.minister")."') as Name FROM `inst_institutions` WHERE 1 ".$type_cond." group by TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'".lang("messages_lang.admin_perso")."','".lang("messages_lang.minister")."')";

			$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
			$data['TYPE_INSTITUTION_ID']=$this->request->getPost('');
			$data['prof_connect']=$prof_connect;
			$data['type_connect']=$type_connect;
			$data['inst_connexion']=$inst_connexion;
			$date_select=date('m');
			if ($date_select=='01' OR $date_select=='02' OR $date_select=='03') {
				$date_ch='';
				$date_ch1='';
				$date_ch2='checked';
				$date_ch3='';
			}else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06') {
				$date_ch='';
				$date_ch1='';
				$date_ch2='';
				$date_ch3='checked';
			}else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' ) {
				$date_ch='checked';
				$date_ch1='';
				$date_ch2='';
				$date_ch3='';
			}else{
				$date_ch='';
				$date_ch1='checked';
				$date_ch2='';
				$date_ch3='';	
			}
			$data['ch']=$date_ch;				
			$data['ch1']=$date_ch1;
			$data['ch2']=$date_ch2;
			$data['ch3']=$date_ch3;
			$data['ann_actuel_id'] = $this->get_annee_budgetaire();
        //Selection de l'année budgétaire
			$get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1  ORDER BY ANNEE_DEBUT ASC"; 
			$data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
			return view('App\Modules\dashboard\Views\Dashboard_TCD_Valeur_Engagement_Vote_View',$data);
		}
	# fonction pour les details
		function detail_tcd_engagement_votes() 
		{
			$data=$this->urichk();
			$db = db_connect(); 
			$session  = \Config\Services::session();
			$KEY=$this->request->getPost('key');
			$KEY2=$this->request->getPost('key2');
			$KEY3=$this->request->getPost('key3');
			$KEY4=$this->request->getPost('key4');
			$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
			$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
			$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
			$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
			$ACTION_ID=$this->request->getPost('ACTION_ID');
			$ACTIVITE=$this->request->getPost('ACTIVITE');
			$cond='';
			$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
			$IS_DOUBLE_COMMANDE=$this->request->getPost('IS_DOUBLE_COMMANDE');

			if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
			{
				$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
				$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
				$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
				$nombre=count($user_connect_req);
				if ($nombre>1) {
					$cond.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";
				}else{
					$cond.='';	
				}
			}

			if ($KEY3==1) {
				$totaux='BUDGET_T1';
				$cond_trim=" AND execution.TRIMESTRE_ID=1" ;
			}else if ($KEY3==2) {
				$totaux='BUDGET_T2';
				$cond_trim=" AND execution.TRIMESTRE_ID=2";
			}else if ($KEY3==3) {
				$totaux='BUDGET_T3';
				$cond_trim=" AND execution.TRIMESTRE_ID=3" ;
			}else if ($KEY3==4){
				$totaux='BUDGET_T4';
				$cond_trim=" AND execution.TRIMESTRE_ID=4" ;
			}else {
				$totaux='BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4';
				$cond_trim=" " ;
			}
			if(! empty($TYPE_INSTITUTION_ID))
			{
				$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
			}
			if(! empty($INSTITUTION_ID))
			{
				$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
			}
			if (! empty($SOUS_TUTEL_ID)) {
				$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
			}
			if(! empty($PROGRAMME_ID))
			{
				if ($TYPE_INSTITUTION_ID==2) {
					$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
				}
			}
			if(! empty($ACTION_ID))
			{
				$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'"; 
			}
			
			// if(! empty($ANNEE_BUDGETAIRE_ID))
			// {
			// 	$cond.=" AND execution.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
			// }
			
			
$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
$var_search = str_replace("'", "\'", $var_search);
$var_search=$this->str_replacecatego($var_search);
$group = "";
$critaire = "";
$limit = 'LIMIT 0,1000';
if ($_POST['length'] != -1)
{
  $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
}
$order_by = '';
$order_column = array('','execution.EXECUTION_BUDGETAIRE_ID','');
$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY execution.EXECUTION_BUDGETAIRE_ID DESC';

$search = !empty($_POST['search']['value']) ? (' AND (execution.PTBA_TACHE_ID LIKE "%' . $var_search . '%" OR inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR inst_institutions.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR pap_activites.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%" OR pap_activites.RESULTAT_PAP_ACTIVITE LIKE "%' . $var_search . '%" )') : '';

$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
$conditionsfilter = $critaire.' '.$search.' '.$group;
$requetedebase = "SELECT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE service,".$totaux." as vote,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,ptba_tache.DESC_TACHE,execution.PTBA_TACHE_ID, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ON  inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=execution.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond."";


    $requetedebase = str_replace("'", "\'", $requetedebase);
    $requetedebases = $requetedebase . ' ' . $conditions;
    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
    $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
    $fetch_projets = $this->ModelPs->datatable($query_secondaire);
    $u=0;
    $data = array();
			foreach ($fetch_data as $row) 
			{
				$u++;
				$engagement=array();
				$Services=(" SELECT DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE CODE_SOUS_TUTEL='".$row->service."'");
				$Services_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$Services.'")');
				$retVal = (! empty($Services_req['DESCRIPTION_SOUS_TUTEL'])) ? $Services_req['DESCRIPTION_SOUS_TUTEL'] : 'N/A' ;
				$date_engage=("SELECT DATE_LIQUIDATION,DATE_ORDONNANCEMENT,DATE_PAIEMENT,DATE_DECAISSENMENT FROM execution_budgetaire_tache_detail WHERE EXECUTION_BUDGETAIRE_ID =".$row->EXECUTION_BUDGETAIRE_ID."");
				$date_engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$date_engage.'")');
				$retdate_engage ="";

				if ($KEY2==1) {
					$mona_de=number_format($row->ENG_BUDGETAIRE,0,',',' ');
					$retdate_engage = !empty($row->DATE_DEMANDE) ? $row->DATE_DEMANDE : 'N/A' ;
					
				}else if ($KEY2==2) {
					$mona_de=number_format($row->LIQUIDATION,0,',',' ');
					$retdate_engage = (! empty($date_engage_req['DATE_LIQUIDATION'])) ? $date_engage_req['DATE_LIQUIDATION'] : 'N/A' ;
				}else if ($KEY2==3) {
					$mona_de=number_format($row->DECAISSEMENT,0,',',' ');
					$retdate_engage = (! empty($date_engage_req['DATE_DECAISSEMENT'])) ? $date_engage_req['DATE_DECAISSEMENT'] : 'N/A' ;
				}else if ($KEY2==4) {
					$mona_de=number_format($row->ENG_JURIDIQUE,0,',',' ');
					
						$retdate_engage = !empty($row->DATE_ENG_JURIDIQUE) ? $row->DATE_ENG_JURIDIQUE : 'N/A' ;
				}else if ($KEY2==5) {
					$mona_de=number_format($row->ORDONNANCEMENT,0,',',' ');
					$retdate_engage = (! empty($date_engage_req['DATE_ORDONNANCEMENT'])) ? $date_engage_req['DATE_ORDONNANCEMENT'] : 'N/A' ;	 
				}else {
					$mona_de=number_format($row->PAIEMENT,0,',',' ');	
					$retdate_engage = (! empty($date_engage_req['DATE_PAIMENT'])) ? $date_engage_req['DATE_PAIMENT'] : 'N/A' ;  
				}
				$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';

				if (strlen($row->INTITULE_MINISTERE) < 13){
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_MINISTERE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a></label></font> </center>';

				}

				if (strlen($row->INTITULE_PROGRAMME) < 13){
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}
				if (strlen($retVal) < 13)
				{
					$engagement[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($retVal, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}
				if (strlen($row->RESULTAT_PAP_ACTIVITE) < 13){
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->RESULTAT_PAP_ACTIVITE.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->RESULTAT_PAP_ACTIVITE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}

				if (strlen($row->DESC_PAP_ACTIVITE) < 13)
				{
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESC_PAP_ACTIVITE.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_PAP_ACTIVITE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';

				$engagement[] ='<center><font color="#000000" size=2><label>'.$retdate_engage.'</label></font> </center>';

				$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->total,0,',',' ').'</label></font> </center>';


				$data[] = $engagement;        
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
	  ######liste des entites responsable
		function liste_institution_engage_votes() 
		     {
			$data=$this->urichk();
			$db = db_connect(); 
			$session  = \Config\Services::session();
			$KEY=$this->request->getPost('key');
			$KEY2=$this->request->getPost('key2');
			$KEY3=$this->request->getPost('key3');
			$KEY4=$this->request->getPost('key4');
			$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
			$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
			$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
			$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
			$ACTION_ID=$this->request->getPost('ACTION_ID');
			$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
			$PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
			$inst_conn=$this->request->getVar('inst_conn');
			$cond_pri="";
			$cond_pri1="";
			$cond="";
			if ($inst_conn>0){
				$user_inst=("SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn."");
				$user_inst_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_inst.'")');
				$fonct_inst='';
				$fonct_key2='';
				$One_select=count($user_inst_req);
				if ($One_select==1){
					$One_code=(" SELECT CODE_INSTITUTION ,INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
					$One_code_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$One_code.'")');
					$INSTITUTION_ID=$One_code_req['INSTITUTION_ID'];
				}else{
					$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');	
				}
				foreach ($user_inst_req as  $value) {  
					$fonct_key2.=$value->INSTITUTION_ID.',';
				}
				$condition = " and inst_institutions.INSTITUTION_ID IN (".substr($fonct_key2,0,-1).") " ;
			}else{
				$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
				$condition ='';	
			}
			$inst_code=(" SELECT INSTITUTION_ID FROM `inst_institutions` WHERE 1 ".$condition." ");
			$inst_code_req=$this->ModelPs->getRequete(' CALL getTable("'.$inst_code.'")');
			$code_inst='';
			$code_key2='';
			foreach ($inst_code_req as $key) {
				$code_key2.=$key->INSTITUTION_ID.',';
			}
			$code_inst =  substr($code_key2,0,-1);
			$cond_pri.=' AND INSTITUTION_ID IN ('.$code_inst.')';
			$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';

			$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');



			if ($IS_PRIVATE==1) {
				$totaux='BUDGET_T1';
				$cond_trim=" AND execution.TRIMESTRE_ID=1" ;

			}else if ($IS_PRIVATE==2) {
				$totaux='BUDGET_T2';
				$cond_trim=" AND execution.TRIMESTRE_ID=2";

			}else if ($IS_PRIVATE==3) {
				$totaux='BUDGET_T3';
				$cond_trim=" AND execution.TRIMESTRE_ID=3" ;
			}else if ($IS_PRIVATE==4){
				$totaux='BUDGET_T4';
				$cond_trim=" AND execution.TRIMESTRE_ID=4" ;
			}else {
				$totaux='BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4';
				$cond_trim=" " ;
			}
			$cond1="";
			if(! empty($TYPE_INSTITUTION_ID))
			{
				$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
			}
			if(! empty($INSTITUTION_ID))
			{
				$cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");
				$cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
				if (! empty($cond_sy_req['INSTITUTION_ID'])) {
					$cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
					$cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
					$cond2= ' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
				}
				$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
				$cond.=" AND inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'";
			}

			if (! empty($SOUS_TUTEL_ID)) {
				$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
			}

			
			 if(! empty($PROGRAMME_ID))
			    {
				$cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID.""; 
			    }
			 if(! empty($ACTION_ID))
			    {
			 	$cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID; 
			     }

			   if(! empty($LIGNE_BUDGETAIRE))
			    {
			 	$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE; 
			     }

			   if(! empty($PAP_ACTIVITE_ID))
			    {
			 	$cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID; 
			     }

			


			$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
			$query_principal="SELECT SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) vote,".$totaux." as vote,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,execution.PTBA_TACHE_ID, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,ptba_tache.DESC_TACHE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,execution.EXECUTION_BUDGETAIRE_ID,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID join inst_institutions_ligne_budgetaire ON  inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=execution.CODE_NOMENCLATURE_BUDGETAIRE_ID  WHERE 1 ".$cond." ".$cond_trim."";

			$limit='LIMIT 0,10';
			if($_POST['length'] != -1)
			{
				$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
			}
			$order_by='';
			if($_POST['order']['0']['column']!=0) {
				$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY execution.EXECUTION_BUDGETAIRE_ID  DESC'; 
			}
			$search = !empty($_POST['search']['value']) ? ("AND (
				DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%' OR inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%')") : '';
			$critere=' ';
			$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
			$query_filter=$query_principal.' '.$critere.'  '.$search;

			$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

			$fetch_data = $this->ModelPs->datatable($query_secondaire);
			$u=0;
			$data = array();
			foreach ($fetch_data as $row) 
			{
				$u++;
				$engagement=array();
				$taux_eng=0;
				$taux_juridique=0;
				$taux_liquida=0;
				$taux_ordo=0;
				$taux_paiement=0;
				$taux_decaiss=0;
				$pourc_vote = ($row->vote > 0) ? $row->vote : 1 ;
				$taux_eng=($row->ENG_BUDGETAIRE/$pourc_vote)*100;

				$obs_enga="";
				if ($taux_eng<50){
					$obs_enga="Sous consommation";
				}elseif($taux_eng>=100.1){
					$obs_enga="<strong style='color:red;'>Dépassement</strong>";
				}else{
					$obs_enga="<strong style='color:green;'>Normal</strong>";	
				}
				$obs_juridiq="";
				$taux_juridique=($row->ENG_JURIDIQUE/$pourc_vote)*100;
				if ($taux_juridique<50){
					$obs_juridiq="Sous consommation";
				}elseif($taux_juridique>=100.1){
					$obs_juridiq="<strong style='color:red;'>Dépassement</strong>";
				}else{
					$obs_juridiq="<strong style='color:green;'>Normal</strong>";	
				}
				$taux_liquida=($row->LIQUIDATION/$pourc_vote)*100;
				$obs_liquida="";
				if ($taux_liquida<50){
					$obs_liquida="Sous consommation";
				}elseif($taux_liquida>=100.1){
					$obs_liquida="<strong style='color:red;'>Dépassement</strong>";
				}else{

					$obs_liquida="<strong style='color:green;'>Normal</strong>";		
				}
				$taux_ordo=($row->ORDONNANCEMENT/$pourc_vote)*100;
				$obs_ordo="";
				if ($taux_ordo<50){
					$obs_ordo="Sous consommation";
				}elseif($taux_ordo>=100.1){
					$obs_ordo="<strong style='color:red;'>Dépassement</strong>";
				}else{
					$obs_ordo="<strong style='color:green;'>Normal</strong>";	
				}
				$taux_paiement=($row->PAIEMENT/$pourc_vote)*100;
				$obs_paiement="";
				if ($taux_paiement<50){
					$obs_paiement="Sous consommation";
				}elseif($taux_paiement>=100.1){
					$obs_paiement="<strong style='color:red;'>Dépassement</strong>";
				}else{
					$obs_paiement="<strong style='color:green;'>Normal</strong>";
				}
				$obs_decaiss="";
				$taux_decaiss=($row->DECAISSEMENT/$pourc_vote)*100;
				if ($taux_decaiss<50){
					$obs_decaiss="Sous consommation";
				}elseif($taux_decaiss>=100.1){
					$obs_decaiss="<strong style='color:red;'>Dépassement</strong>";
				}else{
					$obs_decaiss="<strong style='color:green;'>Normal</strong>";	
				}
				$mona_vote=number_format($row->vote,0,',',' ');
				$mona_engage=number_format($row->ENG_BUDGETAIRE,0,',',' ');
				$mona_liquide=number_format($row->LIQUIDATION,0,',',' ');
				$mona_decaisse=number_format($row->DECAISSEMENT,0,',',' ');
				$mona_juridaire=number_format($row->ENG_JURIDIQUE,0,',',' ');
				$mona_ordonancement=number_format($row->ORDONNANCEMENT,0,',',' ');
				$mona_paiment=number_format($row->PAIEMENT,0,',',' ');	 
				$engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';

				if (strlen($row->INTITULE_MINISTERE) < 13){
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_MINISTERE, 0, 20).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}
				if (strlen($row->INTITULE_PROGRAMME) < 5)
				{
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
				}else{
					$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 5).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
				}
				if (strlen($row->LIBELLE_ACTION) < 5){
					$engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
				}else{
				$engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 5).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font> </center>';

				}
				  if (strlen($row->CODE_NOMENCLATURE_BUDGETAIRE) < 30){
                  $engagement[] ='<center><font color="#000000" size=2><label>'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'</label></font> </center>';
                   }else{
                 $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 20).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
                     }
                   if (strlen($row->DESC_PAP_ACTIVITE) < 13){
                   $engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESC_PAP_ACTIVITE.'</label></font> </center>';
                         }else{
                 $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_PAP_ACTIVITE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
                        }
                    if (strlen($row->DESC_TACHE) < 13){
                  $engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
                        }else{
                $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_TACHE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
                        }
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_engage.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_juridaire.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_liquide.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_ordonancement.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_paiment.'</label></font> </center>';
				$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_decaisse.'</label></font> </center>';
				$data[] = $engagement;        
			}
			$recordsTotal ="CALL `getTable`('" . $query_principal . "');";
			$recordsFiltered ="CALL `getTable`('" . $query_filter . "');";
			$output = array(
				"draw" => intval($_POST['draw']),
				"recordsTotal" =>count($this->ModelPs->datatable($query_principal)),
				"recordsFiltered" =>count($this->ModelPs->datatable($query_filter)),
				"data" => $data
			);
			echo json_encode($output);
		}
		function exporter($TYPE_INSTITUTION_ID='',$INSTITUTION_ID='',$SOUS_TUTEL_ID='',$IS_PRIVATE='',$PROGRAMME_ID='',$ACTION_ID='',$PAP_ACTIVITE_ID='',$LIGNE_BUDGETAIRE='')
		{
			$TYPE_INSTITUTION_ID=$TYPE_INSTITUTION_ID;
			$INSTITUTION_ID=$INSTITUTION_ID;
			$SOUS_TUTEL_ID=$SOUS_TUTEL_ID;
			$IS_PRIVATE=$IS_PRIVATE;
			$PROGRAMME_ID=$PROGRAMME_ID;
			$ACTION_ID=$ACTION_ID;
			$PAP_ACTIVITE_ID=$PAP_ACTIVITE_ID;
			$LIGNE_BUDGETAIRE=$LIGNE_BUDGETAIRE;
			
			$cond='';
			$inst_conn=$this->request->getVar('inst_conn');
			$cond_pri='';
			$cond_pri1='';
			if ($inst_conn>0){
				$user_inst=("SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn."");
				$user_inst_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_inst.'")');
				$fonct_inst='';
				$fonct_key2='';
				$One_select=count($user_inst_req);
				if ($One_select==1){
					$One_code=(" SELECT CODE_INSTITUTION,inst_institutions.INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
					$One_code_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$One_code.'")');
					$INSTITUTION_ID=$One_code_req['INSTITUTION_ID'];
				}else{
					$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');	
				}
				foreach ($user_inst_req as  $value)
				{  
					$fonct_key2.=$value->INSTITUTION_ID.',';
				}
				$condition = " and inst_institutions.INSTITUTION_ID IN (".substr($fonct_key2,0,-1).") " ;
			}else{
				$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
				$condition ='';	
			}
			$inst_code=(" SELECT INSTITUTION_ID FROM `inst_institutions` WHERE 1 ".$condition." ");
			$inst_code_req=$this->ModelPs->getRequete(' CALL getTable("'.$inst_code.'")');
			$code_inst='';
			$code_key2='';
			foreach ($inst_code_req as $key) 
			{
				$code_key2.=$key->INSTITUTION_ID.',';
			}
			$code_inst =  substr($code_key2,0,-1);
			$cond_pri.=' AND inst_institutions.INSTITUTION_ID IN ('.$code_inst.')';
			$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';
			if ($IS_PRIVATE==1)
			{
				$totaux='BUDGET_T1';
				$cond_trim=" AND execution.TRIMESTRE_ID=1" ;
			}else if ($IS_PRIVATE==2) {
				$totaux='BUDGET_T2';
				$cond_trim=" AND execution.TRIMESTRE_ID=2";
			}else if ($IS_PRIVATE==3) {
				$totaux='BUDGET_T3';
				$cond_trim=" AND execution.TRIMESTRE_ID=3" ;
			}else if ($IS_PRIVATE==4)
			{
				$totaux='BUDGET_T4';
				$cond_trim=" AND execution.TRIMESTRE_ID=4" ;
			}else{
				$totaux='BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4';
				$cond_trim=" " ;
			}
			if(! empty($TYPE_INSTITUTION_ID))
			{
				$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
			}
			if(! empty($INSTITUTION_ID))
			{
				$cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");
				$cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
				if (! empty($cond_sy_req['INSTITUTION_ID']))
				{
					$cond1=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
					$cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
					$cond2= ' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
				 }
				$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
			   }
			// if (! empty($SOUS_TUTEL_ID))
			//     {
			// 	$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
			//    }
			 if(! empty($PROGRAMME_ID))
			    {
				$cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID.""; 
			    }
			 if(! empty($ACTION_ID))
			    {
			 	$cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID; 
			     }

			   if(! empty($LIGNE_BUDGETAIRE))
			    {
			 	$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE; 
			     }

			   if(! empty($PAP_ACTIVITE_ID))
			    {
			 	$cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID; 
			     }

			$getRequete="SELECT SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) service,".$totaux." as vote,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,execution.PTBA_TACHE_ID, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 ".$cond." ".$cond_trim." ";



			$getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')"); 
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue('A1', 'INSTITUTIONS');
			$sheet->setCellValue('B1', 'PROGRAMMES');
			$sheet->setCellValue('C1', 'ACTION');
			$sheet->setCellValue('D1', 'RESULTAT ATTENDUS');
			$sheet->setCellValue('E1', 'ACTIVITES');
			$sheet->setCellValue('F1', 'ENGAGEMENT BUDGETAIRE');
			$sheet->setCellValue('G1', 'ENGAGEMENT JURIDIQUE');
			$sheet->setCellValue('H1', 'LIQUDATION');
			$sheet->setCellValue('I1', 'ORDONNANCEMENT');
			$sheet->setCellValue('J1', 'PAIEMENT');
			$sheet->setCellValue('K1', 'DECAISSEMENT');
			$rows = 3;
			foreach ($getData as $key)
			{
				$sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
				$sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
				$sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
				$sheet->setCellValue('D' . $rows, $key->RESULTAT_PAP_ACTIVITE);
				$sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
				$sheet->setCellValue('F' . $rows, $key->ENG_BUDGETAIRE);
				$sheet->setCellValue('G' . $rows, $key->ENG_JURIDIQUE);
				$sheet->setCellValue('H' . $rows, $key->LIQUIDATION);
				$sheet->setCellValue('I' . $rows, $key->ORDONNANCEMENT);
				$sheet->setCellValue('J' . $rows, $key->PAIEMENT);
				$sheet->setCellValue('K' . $rows, $key->DECAISSEMENT);
				$rows++;
			} 
			$writer = new Xlsx($spreadsheet);
			$writer->save('world.xlsx');
			return $this->response->download('world.xlsx', null)->setFileName('Tableau croisé dynamique en valeur.xlsx');
			return redirect('dashboard/Dashboard_TCD_Valeur_Engagement_Vote');
		}

		public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
		{
			$db = db_connect();
			$columnselect = str_replace("\'", "'", $columnselect);
			$table = str_replace("\'", "'", $table);
			$where = str_replace("\'", "'", $where);
			$orderby = str_replace("\'", "'", $orderby);
			$Limit = str_replace("\'", "'", $Limit);
			$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), 
				$db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
				$bindparams = str_replace('\"', '"', $bindparams);
				return $bindparams;
			}

                    /**
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

	//Fonction pou appel des series et hichart
        public function get_rapport()
        {
        	$data=$this->urichk();
        	$db = db_connect(); 
        	$TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
        	$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
        	$SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
        	$ACTION_ID=$this->request->getVar('ACTION_ID');
        	$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
        	$LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
            $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');

        	$IS_DOUBLE_COMMANDE=$this->request->getVar('IS_DOUBLE_COMMANDE');
        	$inst_conn=$this->request->getVar('inst_conn');
        	$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
        	$cond_pri='';
        	$cond_pri1='';
        	if ($inst_conn>0){
        		$user_inst=("SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn."");
        		$user_inst_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_inst.'")');
        		$fonct_inst='';
        		$fonct_key2='';
        		$One_select=count($user_inst_req);
        		if ($One_select==1){
        			$One_code=(" SELECT CODE_INSTITUTION, INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
        			$One_code_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$One_code.'")');
        			$INSTITUTION_ID=$One_code_req['INSTITUTION_ID'];
        		}else{
        			$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');	
        		}
        		foreach ($user_inst_req as  $value) {  
        			$fonct_key2.=$value->INSTITUTION_ID.',';
        		}
        		$condition = " and INSTITUTION_ID IN (".substr($fonct_key2,0,-1).") " ;
        	}else{
        		$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
        		$condition ='';	
        	}
        	$inst_code=(" SELECT CODE_INSTITUTION,INSTITUTION_ID FROM `inst_institutions` WHERE 1 ".$condition." ");
        	$inst_code_req=$this->ModelPs->getRequete(' CALL getTable("'.$inst_code.'")');
        	$code_inst='';
        	$code_key2='';
        	foreach ($inst_code_req as $key) {
        		$code_key2.=$key->INSTITUTION_ID.',';
        	}
        	$code_inst =  substr($code_key2,0,-1);
        	$cond_pri.=' AND inst_institutions.INSTITUTION_ID IN ('.$code_inst.')';
        	$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';
        	if ($IS_PRIVATE==1) {
        		$totaux='SUM(BUDGET_T1)';
        		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;

        		
        	}else if ($IS_PRIVATE==2) {
        		$totaux='SUM(BUDGET_T2)';
        		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
        	}else if ($IS_PRIVATE==3) {
        		$totaux='SUM(BUDGET_T3)';
        		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
        	}else if ($IS_PRIVATE==4){
        		$totaux='SUM(BUDGET_T4)';
        		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4";
        	}else {
        		$totaux='SUM( BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
        		$cond_trim=" " ;
        	}
        	$name_table= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.`INSTITUTION_ID`";
        	$cond='';
        	// print_r($TYPE_INSTITUTION_ID);die();
        	if(! empty($TYPE_INSTITUTION_ID))
        	{
        		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
        	}

        	if(! empty($SOUS_TUTEL_ID))
        	{
        		$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
        	}

        	if(! empty($INSTITUTION_ID))
        	{
        		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
        	}

        	if(! empty($PROGRAMME_ID))
		    {
		      $cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
		    }
			  if(! empty($ACTION_ID))
			    {
			      $cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID."";
			   }
			  if(! empty($LIGNE_BUDGETAIRE))
			    {
			      $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
			   }
			  if(! empty($PAP_ACTIVITE_ID))
			    {
			      $cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
			   }


        	$cond1="";
        	$cond22="";
        	$data_engage='';
        	$data_liquide='';
        	$data_decaissement='';
        	$data_jurdique='';
        	$data_ordonence=''; 
        	$data_paie='';

        	$votes=("SELECT ".$totaux." as vote FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID  ".$cond." ".$cond22." ".$cond_pri1." ");

        	$requete_phases_votes=$this->ModelPs->getRequeteOne(' CALL getTable("'.$votes.'")');
        	$total_vote=0;
        	$vote= (! empty($requete_phases_votes['vote'])) ? $requete_phases_votes['vote'] : 0 ;
        	$total_vote=$total_vote+$vote;
        	if ($vote<=0){
        		$vote=1;
        		$total_vote=0;
        	}

        	$engage=("SELECT SUM(ENG_BUDGETAIRE) as engage FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_trim." ".$cond." ".$cond1." ".$cond_pri." ");

        	// print_r($engage);die();
        	$liquide=("SELECT SUM(LIQUIDATION) as liquide FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_trim." ".$cond."");
        	$decaissement=("SELECT SUM(DECAISSEMENT) as decaissement FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_trim." ".$cond." ".$cond1." ".$cond_pri." ");
        	$jurdique=("SELECT SUM(ENG_JURIDIQUE) as jurdique FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_trim." ".$cond." ".$cond1." ".$cond_pri."");
        	$ordonence=("SELECT SUM(ORDONNANCEMENT) as ordonence FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1  ".$cond_trim." ".$cond." ".$cond1." ".$cond_pri."");
        	$paiement=("SELECT SUM(PAIEMENT) as paie FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." WHERE 1 ".$cond_trim." ".$cond." ".$cond1." ".$cond_pri."");


        	$engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$engage.'")');
        	$liquide_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$liquide.'")');
        	$decaissement_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$decaissement.'")');
        	$jurdique_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$jurdique.'")');
        	$ordonence_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$ordonence.'")');
        	$paiement_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$paiement.'")');
        	$engage_sum= ($engage_req['engage']>0) ? $engage_req['engage'] : 0 ;
        	$liquide_sum= ($liquide_req['liquide']>0) ? $liquide_req['liquide'] : 0 ;
        	$decaissement_sum= ($decaissement_req['decaissement']>0) ? $decaissement_req['decaissement'] : 0 ;
        	$jurdique_sum= ($jurdique_req['jurdique']>0) ? $jurdique_req['jurdique'] : 0 ;
        	$paie_sum= ($paiement_req['paie']>0) ? $paiement_req['paie'] : 0 ;
        	$ordonence_sum= ($ordonence_req['ordonence']>0) ? $ordonence_req['ordonence'] : 0 ;

        	$pourc_taux = ($engage_req['engage'] > 0) ? $engage_req['engage'] : 1 ;
        	$data_engage="{name:'Engagement budgétaire::".number_format($engage_sum,0,',',' ')." BIF', y:(".$engage_sum/$pourc_taux.")*100,key2:1,key3:".$IS_PRIVATE."},";
        	$data_liquide="{name:'Liquidation:".number_format($liquide_sum,0,',',' ')." BIF)', y:(".$liquide_sum/$pourc_taux.")*100,key2:2,key3:".$IS_PRIVATE."},";
        	$data_decaissement="{name:'Décaissement (".number_format($decaissement_sum,0,',',' ')." BIF)', y:(".$decaissement_sum/$pourc_taux.")*100,key2:3,key3:".$IS_PRIVATE."},";
        	$data_jurdique="{name:'Engagement juridique (".number_format($jurdique_sum,0,',',' ')." BIF)', y:(".$jurdique_sum/$pourc_taux.")*100,key2:4,key3:".$IS_PRIVATE."},";
        	$data_ordonence="{name:'Ordonnancement (".number_format($ordonence_sum,0,',',' ')." BIF) ',color:'#a33558', y:(".$ordonence_sum/$pourc_taux.")*100,key2:5,key3:".$IS_PRIVATE."},";
        	$data_paie="{name:'Paiement (".number_format($paie_sum,0,',',' ')." BIF) ', y:(".$paie_sum/$pourc_taux.")*100,key2:6,key3:".$IS_PRIVATE."},";

        	$pourc_taux11= ($engage_req['engage'] > 0) ? $engage_req['engage'] : 1 ;

        	$data_engage11="{name:'".lang('messages_lang.labelle_eng_budget')."', y:".$engage_sum.",key2:1,key3:".$IS_PRIVATE."},";

        	$data_liquide11="{name:'".lang('messages_lang.labelle_liquidation')."', y:".$liquide_sum.",key2:2,key3:".$IS_PRIVATE."},";

        	$data_decaissement11="{name:'".lang('messages_lang.labelle_decaisse')."', y:".$decaissement_sum.",key2:3,key3:".$IS_PRIVATE."},";

        	$data_jurdique11="{name:'".lang('messages_lang.labelle_eng_jud')."', y:".$jurdique_sum.",key2:4,key3:".$IS_PRIVATE."},";

        	$data_ordonence11="{name:'".lang('messages_lang.labelle_ordonan')."',color:'#a33558', y:".$ordonence_sum.",key2:5,key3:".$IS_PRIVATE."},";

        	$data_paie11="{name:'".lang('messages_lang.labelle_paiement')."', y:".$paie_sum.",key2:6,key3:".$IS_PRIVATE."},";

        	$rapp2="<script type=\"text/javascript\">
        	Highcharts.chart('container2', {

        		chart: {
        			type: 'pie'
        			}, 
        			title: {
        				text: '".lang('messages_lang.dashboard_dynamiq')."',
        				},  
        				subtitle: {
        					text: ''
        					},
        					xAxis: {
        						type: 'category',
        						crosshair: true
        						},
        						yAxis: {
        							min: 0,
        							title: {
        								text: ''
        							}
        							},
        							tooltip: {
        								headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
        								pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
        								'<td style=\"padding:0\"><b>{point.y:,3f} </b></td></tr>',
        								footerFormat: '</table>',
        								shared: true,
        								useHTML: true
        								},
        								plotOptions: {
        									pie: {
        										pointPadding: 0.2,
        										borderWidth: 0,
        										depth: 40,
        										cursor:'pointer',
        										point:{
        											events: {
        												click: function(){

        													if(this.key2==1){
        														$(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_budget')."\");
        														}else if(this.key2==2){
        															$(\"#idpro\").html(\"".lang('messages_lang.labelle_liquidation')."\");
        															}else if(this.key2==3){
        																$(\"#idpro\").html(\" ".lang('messages_lang.labelle_decaisse')."\");
        																}else if(this.key2==4){
        																	$(\"#idpro\").html(\"".lang('messages_lang.labelle_eng_jud')."\");
        																	}else if(this.key2==6){
								$(\"#idpro\").html(\"".lang('messages_lang.labelle_paiement')."\");
								}else{
				$(\"#idpro\").html(\"".lang('messages_lang.labelle_ordonan')."\");	
        																		}
																if(this.key3==1){
							$(\"#trim\").html(\"".lang('messages_lang.budget_premier')."\");
							}else if(this.key3==2){
					$(\"#trim\").html(\"".lang('messages_lang.budget_deuxieme')."\");
						}else if(this.key3==3){
						$(\"#trim\").html(\"".lang('messages_lang.budget_troisieme')."\");
					}else if(this.key3==5){
			$(\"#trim\").html(\"".lang('messages_lang.budget_quatrieme')."\");
						}else{
						$(\"#trim\").html(\"".lang('messages_lang.budget_tous')."\");
        																						}
					$(\"#titre\").html(\"".lang('messages_lang.list_activites')." \" +this.name);
					$(\"#myModal\").modal('show');
					var row_count ='1000000';
					$(\"#mytable\").DataTable({
					\"processing\":true,
					\"serverSide\":true,
					\"bDestroy\": true,
					\"oreder\":[],
					\"ajax\":{
					url:\"".base_url('dashboard/Dashboard_TCD_Valeur_Engagement_Vote/detail_tcd_engagement_vote')."\",
						type:\"POST\",
						data:{
							key:this.key,
								key2:this.key2,
							key3:this.key3,
							INSTITUTION_ID:$('#INSTITUTION_ID').val(),
							TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
							PROGRAMME_ID:$('#PROGRAMME_ID').val(),
        				ACTION_ID:$('#ACTION_ID').val(),
        				SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
        				IS_PRIVATE:$('#IS_PRIVATE').val(),
        				ACTIVITE:$('#ACTIVITE').val(),
        				IS_DOUBLE_COMMANDE:$('#IS_DOUBLE_COMMANDE').val(),
        					}
        						},
        					lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
        						pageLength: 5,
        						\"columnDefs\":[{
        						\"targets\":[],
							\"orderable\":false
							}],
							dom: 'Bfrtlip',
							buttons: [
							'excel','pdf'
							],
							language: {
								\"sProcessing\":     \"".lang('messages_lang.labelle_et_traitement')."...\",
								\"sSearch\":         \"".lang('messages_lang.search_button')."&nbsp;:\",
								\"sLengthMenu\":     \"".lang('messages_lang.labelle_et_afficher')." _MENU_ ".lang('messages_lang.labelle_et_element')."\",
								\"sInfo\":           \"".lang('messages_lang.labelle_et_affichage_element')." _START_ ".lang('messages_lang.labelle_et_a')." _END_ sur _TOTAL_ ".lang('messages_lang.labelle_et_element')."\",
								\"sInfoEmpty\":      \"".lang('messages_lang.labelle_et_affichage_element')." 0 ".lang('messages_lang.labelle_et_a')." 0 sur 0 ".lang('messages_lang.labelle_et_element')."\",
					\"sInfoFiltered\":   \"(".lang('messages_lang.labelle_et_affichage_filtre')." _MAX_ ".lang('messages_lang.labelle_et_elementtotal').")\",
					\"sInfoPostFix\":    \"\",
					\"sLoadingRecords\": \"".lang('messages_lang.labelle_et_chargement')."...\",
					\"sZeroRecords\":    \"".lang('messages_lang.labelle_et_aucun_element')."\",
					\"sEmptyTable\":     \"".lang('messages_lang.labelle_et_aucun_donnee_disponible')."\",
					\"oPaginate\": {
					\"sFirst\":      \"".lang('messages_lang.labelle_et_premier')."\",
					\"sPrevious\":   \"".lang('messages_lang.labelle_et_precedent')."\",
					\"sNext\":       \"".lang('messages_lang.labelle_et_suivant')."\",
					\"sLast\":       \"".lang('messages_lang.labelle_et_dernier')."\"
					},
					\"oAria\": {
					\"sSortAscending\":  \": ".lang('messages_lang.labelle_et_trier_colone')."\",
					\"sSortDescending\": \": ".lang('messages_lang.labelle_et_trier_activer_trier')."\"
					}
					}

					});
					}
					}
					},
					dataLabels: {
					enabled: true,
					format: '{point.name} : {point.y:,3f} BIF'
					},
					showInLegend: false
					}
					}, 
					credits: {
					enabled: true,
					href: \"\",
					text: \"Mediabox\"
					},

					series: [
					{

					colorByPoint: true,
			name:'budget',
			data: [".$data_engage11.$data_jurdique11.$data_liquide11.$data_ordonence11.$data_paie11.$data_decaissement11."]
		}
		]
		});
		</script>
		";



		$inst= '<option selected="" disabled="">'.lang('messages_lang.labelle_selecte').'</option>';
		if (!empty($TYPE_INSTITUTION_ID))
		{
			$inst_sect='SELECT `CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`,INSTITUTION_ID FROM `inst_institutions` WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_pri.' ORDER BY DESCRIPTION_INSTITUTION ASC ';
          // print_r($INSTITUTION_ID);die();

			$inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
			foreach ($inst_sect_req as $key)
			{
				if (!empty($INSTITUTION_ID))
				{ 

	                    if ($INSTITUTION_ID==$key->INSTITUTION_ID) 
							{
								$inst.= "<option value ='".$key->INSTITUTION_ID."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
        			} else
								{
									$inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
								}
								 }
									else
											{
								$inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                         }
                       }
					}    

			$soustutel= '<option selected="" disabled="">'.lang('messages_lang.labelle_selecte').'</option>';
				if ($INSTITUTION_ID != ''){

			$inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE INSTITUTION_ID='".$INSTITUTION_ID."'  ");
				$inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');
			$soustutel_sect="SELECT `CODE_SOUS_TUTEL`,`DESCRIPTION_SOUS_TUTEL` FROM `inst_institutions_sous_tutel` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY DESCRIPTION_SOUS_TUTEL ASC ";

			$soustutel_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$soustutel_sect.'")');

			foreach ($soustutel_sect_req as $key)
			{
				if (!empty($SOUS_TUTEL_ID))
				{  
					if ($SOUS_TUTEL_ID==$key->CODE_SOUS_TUTEL) 
					{
						$soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."' selected>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
					}
					else
					{
						$soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."'>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
					}
				}
				else
				{
					$soustutel.= "<option value ='".$key->CODE_SOUS_TUTEL."'>".trim($key->DESCRIPTION_SOUS_TUTEL)."</option>";
				}
			}
		}

		       $program= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
               if (!empty($PROGRAMME_ID))
               {
                 $inst_sect='SELECT DISTINCT inst_institutions.INSTITUTION_ID, inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION  ORDER BY DESCRIPTION_INSTITUTION ASC ';
                 $inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
                 foreach ($inst_sect_req as $key)
                 {
                   if (!empty($INSTITUTION_ID))
                   { 
                     if ($INSTITUTION_ID==$key->INSTITUTION_ID) 
                     {
                       $inst.= "<option value ='".$key->INSTITUTION_ID."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                     }
                     else
                     {
                       $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                     }
                   }
                   else
                   {
                     $inst.= "<option value ='".$key->INSTITUTION_ID."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
                   }
                 }
               }

		     $program= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
               if ($INSTITUTION_ID != '')
               {
                $program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME ,inst_institutions_programmes.PROGRAMME_ID FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.PROGRAMME_ID=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND inst_institutions.INSTITUTION_ID=".$INSTITUTION_ID."   ORDER BY inst_institutions_programmes.PROGRAMME_ID ASC";

                $program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
                foreach ($program_sect_req as $key)
                {
                 if (!empty($PROGRAMME_ID))
                 {  
                   if ($PROGRAMME_ID==$key->PROGRAMME_ID) 
                   {
                     $program.= "<option value ='".$key->PROGRAMME_ID."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
                   }
                   else
                   {
                     $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                   }
                 }
                 else
                 {
                   $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
                 }
               }
             }
							
		
        $actions= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
                 if ($PROGRAMME_ID != '')
                 {
                   $actions_sect='SELECT `ACTION_ID`, `PROGRAMME_ID`, `CODE_ACTION`, `LIBELLE_ACTION` FROM `inst_institutions_actions` WHERE 1 AND PROGRAMME_ID='.$PROGRAMME_ID;
                   $actions_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$actions_sect.'")');
                   foreach ($actions_sect_req as $key)
                   {
                     if (!empty($ACTION_ID))
                     {  
                       if ($ACTION_ID==$key->ACTION_ID) 
                       {
                         $actions.= "<option value ='".$key->ACTION_ID."' selected>".trim($key->LIBELLE_ACTION)."</option>";
                       }
                       else
                       {
                         $actions.= "<option value ='".$key->ACTION_ID."'>".trim($key->LIBELLE_ACTION)."</option>";
                       }
                     }
                     else
                     {
                       $actions.= "<option value ='".$key->ACTION_ID."'>".trim($key->LIBELLE_ACTION)."</option>";
                     }
                   }
                 }

           $ligne_budgetaires= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
               if ($ACTION_ID != '')
               {
                $ligne_budgetaire_sect='SELECT DISTINCT inst_institutions_ligne_budgetaire.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID FROM  ptba_tache RIGHT JOIN inst_institutions_ligne_budgetaire ON inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ptba_tache.ACTION_ID='.$ACTION_ID;

                  $ligne_budgetaire_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$ligne_budgetaire_sect.'")');
                     foreach ($ligne_budgetaire_sect_req as $key)
                     {
                    if (!empty($LIGNE_BUDGETAIRE))
                      {  
                  if ($LIGNE_BUDGETAIRE==$key->CODE_NOMENCLATURE_BUDGETAIRE_ID) 
                        {
                  $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."' selected>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                        }
                      else
                        {
                  $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."'>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                         }
                       }
                       else
                       {
                  $ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE_ID."'>".trim($key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
                       }
                     }
               }
               $ligne_activite= '<option selected="" disabled="">'.lang("messages_lang.selection_message").'</option>';
               if ($LIGNE_BUDGETAIRE != '')
               {
                $ligne_activites_sect='SELECT DISTINCT  pap_activites.PAP_ACTIVITE_ID,pap_activites.DESC_PAP_ACTIVITE FROM  ptba_tache RIGHT JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE 1 AND pap_activites.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$LIGNE_BUDGETAIRE;

                $ligne_activites_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$ligne_activites_sect.'")');
                     foreach ($ligne_activites_sect_req as $key)
                     {
                    if (!empty($PAP_ACTIVITE_ID))
                      {  
                  if ($PAP_ACTIVITE_ID==$key->PAP_ACTIVITE_ID) 
                        {
                  $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."' selected>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                        }
                      else
                        {
                  $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."'>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                         }
                       }
                       else
                       {
                  $ligne_activite.= "<option value ='".$key->PAP_ACTIVITE_ID."'>".trim($key->DESC_PAP_ACTIVITE)."</option>";
                       }
                     }
               }

 //     $activites= '<option selected="" disabled="">sélectionner</option>';

	// if (!empty($ACTION_ID))
	// {
	// 	$activite_sect="SELECT DISTINCT CODE_NOMENCLATURE_BUDGETAIRE AS ID,CODE_NOMENCLATURE_BUDGETAIRE AS NAME FROM `ptba` WHERE  `CODE_ACTION`='".$ACTION_ID."' ORDER BY ACTIVITES ASC";
	// 	$activite_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$activite_sect.'")');
	// 	foreach ($activite_sect_req as $key)
	// 	{
	// 		if ($ACTIVITE !='')
	// 		{  
	// 			if ($ACTIVITE==$key->ID) 
	// 			{
	// 				$activites.= "<option value ='".$key->ID."' selected>".trim($key->NAME)."</option>";
	// 			}
	// 			else
	// 			{
	// 				$activites.= "<option value ='".$key->ID."'>".trim($key->NAME)."</option>";
	// 			}
	// 		}
	// 		else
	// 		{
	// 			$activites.= "<option value ='".$key->ID."'>".trim($key->NAME)."</option>";
	// 		}
	// 	}
	// }

// echo json_encode(array('rapp2'=>$rapp2,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'activite'=>$activites));
        echo json_encode(array('rapp2'=>$rapp2,'inst'=>$inst,'soustutel'=>$soustutel, 'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires, 'ligne_activite'=>$ligne_activite));
}

	//Fonction pour stringer
		function str_replacecatego($name)
		{
		$catego=str_replace("'"," ",$name);
		$catego=str_replace("  "," ",$catego);
		$catego=str_replace("\n"," ",$catego);
		$catego=str_replace("\t"," ",$catego);
		$catego=str_replace("\r"," ",$catego);
		$catego=str_replace("@"," ",$catego);
		$catego=str_replace("&"," ",$catego);
		$catego=str_replace(">"," ",$catego);
		$catego=str_replace("   "," ",$catego);
		$catego=str_replace("?"," ",$catego);
		$catego=str_replace("#"," ",$catego);
		$catego=str_replace(""," ",$catego);
		return $catego;
		}

	}
    ?>