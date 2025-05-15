<?php
/**
 * @author NIYONGABO Emery
 *emery@mediabox.bi
 * Tableau de bord «dashbord taux phase engagement»
 le 27/02/2023
 */
  //Appel de l'esp\ce de nom du Controllers
 namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
 ###declaration d'une classe controlleur
 class Dashboard_Taux_Phase_Engagement extends BaseController
   {
 	protected $session;
 	protected $ModelPs;
   ###fonction constructeur
 	public function __construct()
 	    {
 		$this->library = new CodePlayHelper();
 		$this->ModelPs = new ModelPs();
 		$this->ModelS = new ModelS();
 		$this->session = \Config\Services::session();
 		 }//fonction qui retourne les couleurs
         public function getcolor() 
         {
         	$chars = 'ABCDEF0123456789';
         	$color = '#';
         	for ( $i= 0; $i < 6; $i++ )
         	{
         		$color.= $chars[rand(0, strlen($chars) -1)];
         	}
         	return $color;
         }
      //fonction index
         public function index($value='')
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
    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_TCD_ENGAGEMENT')!=1)
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
		if ($nombreinst==1)
		   {
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


		$requete_type="SELECT  TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Administrations personnalisées','Ministères') as Name FROM `inst_institutions` WHERE 1 ".$type_cond." group by TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Institution','Ministère')";
		$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
		$data['TYPE_INSTITUTION_ID']=$this->request->getPost('');

		$date_select=date('m');
		if($date_select=='01' OR $date_select=='02' OR $date_select=='03'){
		   $date_ch='';
		   $date_ch1='';
		   $date_ch2='checked';
		   $date_ch3='';
		   $date_ch4='';
		}else if ($date_select=='04' OR $date_select=='05' OR $date_select=='06') {
		$date_ch='';
		$date_ch1='';
		$date_ch2='';
		$date_ch3='checked';
		$date_ch4='';
		}else if ($date_select=='07' OR $date_select=='08' OR $date_select=='09' ){
		$date_ch='checked';
		$date_ch1='';
		$date_ch2='';
		$date_ch3='';
		$date_ch4='';
		}else if ($date_select=='10' OR $date_select=='11' OR $date_select=='12' ){
		$date_ch='';
		$date_ch1='checked';
		$date_ch2='';
		$date_ch3='';
		$date_ch4='';
		}else{
		$date_ch='';
		$date_ch1='';
		$date_ch2='';
		$date_ch3='';
		$date_ch4='checked';	
		}
		$data['ch']=$date_ch;				
		$data['ch1']=$date_ch1;
		$data['ch2']=$date_ch2;
		$data['ch3']=$date_ch3;
		$data['ch4']=$date_ch4;
    $data['prof_connect']=$prof_connect;
    $data['type_connect']=$type_connect;
    $data['inst_connexion']=$inst_connexion;
    $data['ann_actuel_id'] = $this->get_annee_budgetaire();
    //Selection de l'année budgétaire
    $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1  ORDER BY ANNEE_DEBUT ASC"; 
    $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
       return view('App\Modules\dashboard\Views\Dashboard_Taux_Phase_Engagement_View',$data);
         }

        ##fonction get_rapport qui permet d'afficher le rapport et appel des filtres qui dependent des autres
  public function get_rapport()
    {
    	$data=$this->urichk();
    	$db = db_connect(); 
    	$TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
    	$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
    	$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
    	$SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
    	$ACTION_ID=$this->request->getVar('ACTION_ID');
        $PAP_ACTIVITE_ID=$this->request->getVar('PAP_ACTIVITE_ID');
    	$LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
    	$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
    	$inst_conn=$this->request->getVar('inst_conn');
    	// $ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');

    	$IS_DOUBLE_COMMANDE=$this->request->getVar('IS_DOUBLE_COMMANDE');
    	$cond_pri='';
    	$cond_pri1='';
    	if ($inst_conn>0){
    		$user_inst=("SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn."");
    		$user_inst_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_inst.'")');
    		$fonct_inst='';
    		$fonct_key2='';
    		$One_select=count($user_inst_req);
    		if ($One_select==1){
    			$One_code=(" SELECT CODE_INSTITUTION,INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
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
    	$inst_code=(" SELECT CODE_INSTITUTION,INSTITUTION_ID FROM `inst_institutions` WHERE 1 ".$condition." ");
    	$inst_code_req=$this->ModelPs->getRequete(' CALL getTable("'.$inst_code.'")');
    	$code_inst='';
    	$code_key2='';
    	foreach ($inst_code_req as $key) {
    		$code_key2.=$key->INSTITUTION_ID.',';
    	}
    	$code_inst =  substr($code_key2,0,-1);
    	$cond_pri.=' AND inst_institutions.INSTITUTION_ID IN ('.$code_inst.')';
    	$cond_pri1.=' AND  ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';
    	$cond_trim='';
    	if ($IS_PRIVATE==1){
    		$totaux='SUM(BUDGET_T1)';
    		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1 " ;
    	}else if ($IS_PRIVATE==2){
    		$totaux='SUM(BUDGET_T2)';
    		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=2" ;
    	}else if ($IS_PRIVATE==3){
    		$totaux='SUM(BUDGET_T3)';
    		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=3" ;
    	}else if ($IS_PRIVATE==4){
    		$totaux='SUM(BUDGET_T4)';
    		$cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=4" ;
    	}else{
    		$totaux='SUM(BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4)';
    		$cond_trim=" ";
    	}
    	$cond1='';
    	$cond='';
    	$cond2='';
    	$KEY2=1;
    	$cond_program='';
    	$titr_deux=' par categories';
    	$titr_deux2=' par categories';
    	$id_decl= 'TYPE_INSTITUTION_ID'; 
    	$name_decl= "if(TYPE_INSTITUTION_ID=1,'Administrations personnalisees','Ministeres')";
    	$format=" {point.y:.3f} %";
    	$type="column";
    	$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    	if(! empty($TYPE_INSTITUTION_ID))
    	{
    		$titr_deux='par institutions';
    		$titr_deux2='par institutions';
    		$id_decl= ' inst_institutions.INSTITUTION_ID'; 
    		$name_decl= "DESCRIPTION_INSTITUTION";
    		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    		$cond2='';
    		$type="column";
    		$format=" {point.y:.3f} %";
    		$KEY2=2;
    	}
    	if(! empty($INSTITUTION_ID))
    	{
    		$name_decl= "inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL"; 
    		$id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
    		$name_table1= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN  inst_institutions_sous_tutel ON inst_institutions_sous_tutel.INSTITUTION_ID=inst_institutions.INSTITUTION_ID WHERE inst_institutions_sous_tutel.INSTITUTION_ID=".$INSTITUTION_ID." ";
    		$format=" {point.y:.2f} %";
    		$type="column";
    		$titr_deux=' par services';
    		$titr_deux2=' par services';
    		$KEY2=5;
    		$cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");
    		$cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
    		if (! empty($cond_sy_req['INSTITUTION_ID'])) {
    			$cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
    			$cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
    			$cond2= ' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
    		}
    		$cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
    	}


    	if(! empty($SOUS_TUTEL_ID))
    	{
    		$name_decl= "DESCRIPTION_SOUS_TUTEL"; 
    		$id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
    		$name_table1= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
    		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_sous_tutel ON inst_institutions_sous_tutel.CODE_SOUS_TUTEL=SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)";
    		$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
    		$format=" {point.y:.2f} %";
    		$type="column";
    		$titr_deux=' par services';
    		$titr_deux2=' par services';
    		$KEY2=5;
    	}
    	$cond33='';
    	$cond333="";
    	$cond3333="";
    	if(! empty($PROGRAMME_ID))
    	{
    		$id_decl= 'inst_institutions_actions.ACTION_ID'; 
    		$name_decl= "inst_institutions_actions.LIBELLE_ACTION"; 
    			$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID";

    		$cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    		$cond33.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    		$cond3333.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
    		$type="column";
    		$format=" {point.y:.3f} %";
    		$titr_deux=' par actions';
    		$titr_deux2=' par actions';
    		$cond2='';
    		$KEY2=3;
    	}
    	if(! empty($ACTION_ID))
    	{
    		$id_decl= "inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";  
    		$name_decl= "LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE";
    		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID =inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID";
    		$cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
    		$cond333.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
    		$type="column";
    		$titr_deux='par activités';
    		$titr_deux2='';
    		$format=" {point.y:.3f} %";
    		$KEY2=4;  
    	}
        if(!empty($LIGNE_BUDGETAIRE))
          {
              $id_decl= "pap_activites.PAP_ACTIVITE_ID";
              $name_decl= "pap_activites.DESC_PAP_ACTIVITE";
              $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID";
              $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
              $cond333.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='".$LIGNE_BUDGETAIRE."'";
              $type="column";
              $titr_deux=''.lang("messages_lang.par_activite").'';
              $titr_deux2='';
              $format=" {point.y:.3f} %";
              $KEY2=4;
        }
        if(!empty($PAP_ACTIVITE_ID))
          {
              $id_decl= "ptba_tache.PTBA_TACHE_ID";
              $name_decl= "ptba_tache.DESC_TACHE";
              $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
              $cond.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
              $cond333.=" AND ptba_tache.PAP_ACTIVITE_ID='".$PAP_ACTIVITE_ID."'";
              $type="column";
              $titr_deux=''.lang("messages_lang.par_activite").'';
              $titr_deux2='';
              $format=" {point.y:.3f} %";
              $KEY2=4;
        }
            	
    	$cond1="";
    	$cond22='';
    	// if(!empty($ANNEE_BUDGETAIRE_ID))
    	// {
    	// 	$cond1.=" AND execution_budgetaire.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
    	// 	// $cond22.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
    	// }
    
    	$engage11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,SUM(execution_budgetaire.ENG_BUDGETAIRE) as engage  FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  ".$name_table."   ".$cond_trim." ".$cond." GROUP BY ".$name_decl.",".$id_decl." ");

        
        // print_r($engage11);die();  ".$cond." ".$cond1." ".$cond_pri1."
    	$liquide11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,SUM(execution_budgetaire.LIQUIDATION) as liquide FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table."   ".$cond_trim." ".$cond." GROUP BY ".$name_decl.",".$id_decl."");
    	$decaissement11=("SELECT ".$name_decl." AS name,".$id_decl." as ID, SUM(execution_budgetaire.DECAISSEMENT) as decaissement FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table."   ".$cond_trim." ".$cond." GROUP BY ".$name_decl.",".$id_decl."");
    	$jurdique11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,SUM(execution_budgetaire.ENG_JURIDIQUE) as jurdique FROM  `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table." ".$cond_trim."   GROUP BY ".$name_decl.",".$id_decl."");
    	$ordonence11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,SUM(execution_budgetaire.ORDONNANCEMENT) as ordonence FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table."  ".$cond_trim." ".$cond."   GROUP BY ".$name_decl.",".$id_decl."");
    	$paiement11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,SUM(execution_budgetaire.PAIEMENT) as paie FROM `execution_budgetaire` JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID ".$name_table."  ".$cond_trim." ".$cond."   GROUP BY ".$name_decl.",".$id_decl."");
    	$votes=("SELECT ".$totaux." as vote FROM  ptba_tache ".$name_table."  ".$cond." ".$cond_pri1." ");
    	$requete_phases_votes=$this->ModelPs->getRequete(' CALL getTable("'.$votes.'")');
    	$engage_req11=$this->ModelPs->getRequete(' CALL getTable("'.$engage11.'")');
    	$liquide_req11=$this->ModelPs->getRequete(' CALL getTable("'.$liquide11.'")');
    	$decaissement_req11=$this->ModelPs->getRequete(' CALL getTable("'.$decaissement11.'")');
    	$jurdique_req11=$this->ModelPs->getRequete(' CALL getTable("'.$jurdique11.'")');
    	$ordonence_req11=$this->ModelPs->getRequete(' CALL getTable("'.$ordonence11.'")');
    	$paiement_req11=$this->ModelPs->getRequete(' CALL getTable("'.$paiement11.'")');

    	$categorie_institution='';
    	$data_engager_req='';
    	$data_engager_req1='';
    	$data_engage_total=0;
    	$total_vote=0;
    	$categorie="";
    	foreach ($engage_req11 as $value)
    	{
    		$pourcent=0;
    		$pourcent2=0;
    		$taux=("SELECT ".$totaux." AS taux FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond."  ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ");
    		$categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    	
            $rappel=$this->str_replacecatego($name);
            $categorie.= $rappel."',";
            ;
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->engage/$pourc_taux1)*100;
    		$pourcent2=($value->engage/$pourc_taux2)*100;         
    		$data_engager_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:1},";
    		$data_engager_req1.="{name:'".$this->str_replacecatego($value->name)."(".number_format($value->engage,0,',',' ').")', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:1},";
    		$data_engage_total=$data_engage_total+$value->engage;
    	}
    	$data_juridique_req='';
    	$data_juridique_req1='';
    	$data_juridique_total=0;
    	foreach ($jurdique_req11 as $value)
    	{
    		$pourcent=0;
    		$taux=("SELECT ".$totaux." AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond2." ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ".$cond_pri1." ");
    		// $categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    		$rappel=$this->str_replacecatego($name);
    		// $categorie.= $rappel."',";
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->jurdique/$pourc_taux1)*100;
    		$pourcent2=($value->jurdique/$pourc_taux2)*100;    
    		$data_juridique_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:2},";
    		$data_juridique_req1.="{name:'".$this->str_replacecatego($value->name)."(".number_format($value->jurdique,0,',',' ').")', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:2},";
    		$data_juridique_total=$data_juridique_total+$value->jurdique;
    	}
    	$data_liquidation_req='';
    	$data_liquidation_req1='';
    	$data_liquidation_total=0;
    	foreach ($liquide_req11 as $value)
    	{
    		$pourcent=0;
    		$taux=("SELECT ".$totaux." AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond2." ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_pri1." ".$cond_trim." ");
    		// $categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    		$rappel=$this->str_replacecatego($name);
    		// $categorie.= $rappel."',";
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->liquide/$pourc_taux1)*100;
    		$pourcent2=($value->liquide/$pourc_taux2)*100;  
    		$data_liquidation_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",color:'#a33558',key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:3},";
    		$data_liquidation_req1.="{name:'".$this->str_replacecatego($value->name)."(".number_format($value->liquide,0,',',' ').")', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:3},";
    		$data_liquidation_total=$data_liquidation_total+$value->liquide;
    	}
    	$data_ordonancement_req='';
    	$data_ordonancement_req1='';
    	$data_ordonancement_total=0;
    	foreach ($ordonence_req11 as $value)
    	{
    		$pourcent=0;
    		$taux=("SELECT ".$totaux." AS taux FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_pri1."  ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ".$cond_pri1."");
    		// $categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    		$rappel=$this->str_replacecatego($name);
    		// $categorie.= $rappel."',";
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->ordonence/$pourc_taux1)*100;
    		$pourcent2=($value->ordonence/$pourc_taux2)*100; 
    		$data_ordonancement_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:4},";
    		$data_ordonancement_req1.="{name:'".$this->str_replacecatego($value->name)."(".number_format($value->ordonence,0,',',' ').")', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:4},";
    		$data_ordonancement_total=$data_ordonancement_total+$value->ordonence;
    	}
    	$data_paiement_req='';
    	$data_paiement_req1='';
    	$data_paiement_total=0;
    	foreach ($paiement_req11 as $value)
    	{
    		$pourcent=0;
    		$taux=("SELECT ".$totaux." AS taux FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ".$cond_pri1." ");
    		// $categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    		$rappel=$this->str_replacecatego($name);
    		// $categorie.= $rappel."',";
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->paie/$pourc_taux1)*100;
    		$pourcent2=($value->paie/$pourc_taux2)*100; 

    		$data_paiement_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:5},";

    		$data_paiement_req1.="{name:'".$this->str_replacecatego($value->name)."(".number_format($value->paie,0,',',' ').")', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key2:1,key3:5},";

    		$data_paiement_total=$data_paiement_total+$value->paie;
    	}

        // print_r($decaissement_req11);die();
    	$data_decaissement_req='';
    	$data_decaissement_req1='';
    	$data_decaissement_total=0;
    	foreach ($decaissement_req11 as $value)
    	{
    		$pourcent=0;
    		$taux=("SELECT ".$totaux." AS taux FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond."  ");
    		$taux2=("SELECT SUM(execution_budgetaire.ENG_BUDGETAIRE) as taux FROM `execution_budgetaire`  JOIN  ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire.PTBA_TACHE_ID  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond1." ".$cond_trim." ".$cond_pri1." ");
    		$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    		$taux21=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux2.'")');
    		$pourc_taux1 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourc_taux2 = ($taux21['taux'] > 0) ? $taux21['taux'] : 1 ;
    		$pourcent=($value->decaissement/$pourc_taux1)*100;
    		$pourcent2=($value->decaissement/$pourc_taux2)*100; 
    		// $categorie.="'";
    		$name = (!empty($value->name)) ? $value->name : "Autres";
    		$rappel=$this->str_replacecatego($name);
    		// $categorie.= $rappel."',";
    		$data_decaissement_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$pourcent.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:6},";
    		$data_decaissement_req1.="{name:'".$this->str_replacecatego($value->name)." ', y:".$pourcent2.",key:'".$this->str_replacecatego($value->ID)."',key2:".$KEY2.",key3:6},";
    		$data_decaissement_total=$data_decaissement_total+$value->decaissement;
    	}
    	$taux=("SELECT ".$totaux." AS vote FROM ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." ".$cond_pri1."  ");
    	$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
    	$total_vote=$total_vote+$taux1['vote'];


        
	$rapp1="<script type=\"text/javascript\">
		Highcharts.chart('container1', {

		chart: {
		type: 'column'
		},
		title: {
	text: '<b> Taux d\'exécution par phase en référence au budget engagé</b>',
	    	},  
		subtitle: {
		text: ''
		},
		xAxis: {
		 categories: [".$categorie."],
		crosshair: true
		},
		yAxis: {
		min: 0,
		title: {
		text: ''
		}
		},
		tooltip: {
		pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name} soit </td>' +
		'<td style=\"padding:0\"><b>{point.y:.1f} %</b></td></tr>',
		footerFormat: '</table>',
		shared: false,
		useHTML: true
		},
		plotOptions: {
		column: {
		pointPadding: 0.10,
		borderWidth: 0,
		stacking:'normal',
		depth: 40,
		cursor:'pointer',
		point:{
		events: {
		click: function(){
		if(this.key3==1){
		$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
		}else if(this.key3==3){
		$(\"#idpro\").html(\"Liquidation\");
		}else if(this.key3==6){
		$(\"#idpro\").html(\" Décaissement\");
		}else if(this.key3==2){
		$(\"#idpro\").html(\"Engagement&nbspjurdique\");
		}else if(this.key3==5){
		$(\"#idpro\").html(\"Paiement\");
		}else{
		$(\"#idpro\").html(\"Ordonnancement\");	
		}
		
		 $(\"#titre\").html(\"Liste des activités sur \" +this.series.name);
		$(\"#myModal\").modal('show');
		var row_count ='1000000';
		$(\"#mytable\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"oreder\":[],
		\"ajax\":{
		url:\"".base_url('dashboard/Dashboard_Taux_Phase_Engagement/detail_croise_dynamique')."\",
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
		}
		},
		lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
		pageLength: 5,
		\"columnDefs\":[{
		\"targets\":[],
		\"orderable\":false
		}],
		dom: 'Bfrtlip',
		buttons: [ 'excel', 'pdf'],
		language: {
		\"sProcessing\":     \"Traitement en cours...\",
		\"sSearch\":         \"Rechercher&nbsp;:\",
		\"sLengthMenu\":     \"Afficher _MENU_ &eacute;l&eacute;ments\",
		\"sInfo\":           \"Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments\",
		\"sInfoEmpty\":      \"Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment\",
		\"sInfoFiltered\":   \"(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)\",
		\"sInfoPostFix\":    \"\",
		\"sLoadingRecords\": \"Chargement en cours...\",
		\"sZeroRecords\":    \"Aucun &eacute;l&eacute;ment &agrave; afficher\",
		\"sEmptyTable\":     \"Aucune donn&eacute;e disponible dans le tableau\",
		\"oPaginate\": {
		\"sFirst\":      \"Premier\",
		\"sPrevious\":   \"Pr&eacute;c&eacute;dent\",
		\"sNext\":       \"Suivant\",
		\"sLast\":       \"Dernier\"
		},
		\"oAria\": {
		\"sSortAscending\":  \": activer pour trier la colonne par ordre croissant\",
		\"sSortDescending\": \": activer pour trier la colonne par ordre d&eacute;croissant\"
		}
		}

		});
		}
		}
		},
		dataLabels: {
		enabled: true,
		format: '{point.y:,.3f} %'
		},
		showInLegend: true
		}
		}, 
		credits: {
		enabled: true,
		href: \"\",
		text: \"Mediabox\"
		},

		series: [
		{
		name:'Décaissement ::".number_format($data_decaissement_total,0,',',' ')." BIF', 
		data: [".$data_decaissement_req."]
		},
              {
        name:'Paiement ::".number_format($data_paiement_total,0,',',' ')." BIF', 
        data: [".$data_paiement_req."]
        },
        {
        name:'Ordonnancement ::".number_format($data_ordonancement_total,0,',',' ')." BIF', 
        data: [".$data_ordonancement_req."]
        },
        {
            name:'Liquidation ::".number_format($data_liquidation_total,0,',',' ')." BIF', 
            data: [".$data_liquidation_req."]
            },

        {
            name:'Engagement&nbspjurdique ::".number_format($data_juridique_total,0,',',' ')." BIF', 
            data: [".$data_juridique_req."]
            },
        {
            name:'Engagement&nbspbudgétaire ::".number_format($data_engage_total,0,',',' ')." BIF', 
            data: [".$data_engager_req."]
        }
		]
		});
		</script>
		";


	$inst= '<option selected="" disabled="">sélectionner</option>';
	if (!empty($TYPE_INSTITUTION_ID))
	{
	$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION , inst_institutions.INSTITUTION_ID FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_pri1.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION,INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

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

	$soustutel= '<option selected="" disabled="">sélectionner</option>';
	if ($INSTITUTION_ID != '')
	{
	$soustutel_sect="SELECT DISTINCT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel   WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID=".$INSTITUTION_ID."  ORDER BY inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL ASC ";
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



	$program= '<option selected="" disabled="">sélectionner</option>';
	if (!empty($PROGRAMME_ID))
	{
	$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION,inst_institutions.INSTITUTION_ID FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION,INSTITUTION_ID ORDER BY DESCRIPTION_INSTITUTION ASC ';

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

          $inst_id=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE INSTITUTION_ID='".$INSTITUTION_ID."'  ");

          $inst_id_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$inst_id.'")');
          $program_sect="SELECT PROGRAMME_ID, `CODE_PROGRAMME`,`INTITULE_PROGRAMME` FROM `inst_institutions_programmes` WHERE `INSTITUTION_ID`=".$inst_id_req['INSTITUTION_ID']." ORDER BY INTITULE_PROGRAMME ASC";
          $program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
          foreach ($program_sect_req as $key)
          {
            if (!empty($PROGRAMME_ID))
            {  
             if ($PROGRAMME_ID==$key->PROGRAMME_ID) 
             {
              $program.= "<option value ='".$key->PROGRAMME_ID."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
            }
            else	{
              $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
            }
          }
          else
          {
           $program.= "<option value ='".$key->PROGRAMME_ID."'>".trim($key->INTITULE_PROGRAMME)."</option>";
         }
       }
     }
	$actions= '<option selected="" disabled="">sélectionner</option>';
	 if ($PROGRAMME_ID != '')
	      {
	// $actions_sect='SELECT DISTINCT ptba_tache.CODE_ACTION,ptba_tache.LIBELLE_ACTION FROM ptba_tache JOIN inst_institutions ON inst_institutions.CODE_INSTITUTION=ptba_tache.CODE_MINISTERE where 1  '.$cond33.' ORDER BY CODE_ACTION ASC';

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

    // echo json_encode(array('rapp1'=>$rapp1,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires));
         echo json_encode(array('rapp1'=>$rapp1,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires, 'ligne_activite'=>$ligne_activite ));
}

   

   # fonction pour les details
	function detail_croise_dynamiques() 
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
    $IS_DOUBLE_COMMANDE=$this->request->getPost('IS_DOUBLE_COMMANDE');
		$cond='';
		$cond11='';
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
        if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
		$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');

		$nombre=count($user_connect_req);
		if ($nombre>1) {
	     $cond11.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";

		}else{
		$cond11.='';	
		}
	   }
		if ($IS_PRIVATE==1){
    $totaux='BUDGET_T1';
    $cond_trim=" AND execution.TRIMESTRE_ID=1" ;
    }elseif ($IS_PRIVATE==2){
    $totaux='BUDGET_T2';
   $cond_trim=" AND execution.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3){
    $totaux='BUDGET_T3';
    $cond_trim=" AND execution.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
    $totaux='BUDGET_T4';
    $cond_trim=" AND execution.TRIMESTRE_ID=4" ;
    }else{
   $totaux=' BUDGET_T1+BUDGET_T2+BUDGET_T3+BUDGET_T4';
   $cond_trim=" " ;
    }

		
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
		$query_principal="SELECT SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) service,".$totaux." as total,ptba_tache.PTBA_TACHE_ID,execution.EXECUTION_BUDGETAIRE_ID,execution.DATE_ENG_JURIDIQUE,execution.DATE_DEMANDE,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE, execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION  AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT,execution.DATE_DEMANDE,execution.DATE_ENG_JURIDIQUE FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID   WHERE 1 ".$cond." ".$cond_trim." ";

     $critere="";  
		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}
		$order_by='';
		if($_POST['order']['0']['column']!=0) {

		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY activite.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID  DESC'; 
		}
		$search = !empty($_POST['search']['value']) ? ("AND (
		inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%'  OR RESULTAT_PAP_ACTIVITE LIKE '%$var_search%')") : '';

		$critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
       if($KEY2==1)
         {
        $critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
         }
       if ($KEY2==2)
          {
        $critere=" AND ptba_tache.INSTITUTION_ID='".$KEY."'";
         }
       //  if ($KEY2==5)
       //    {
	     // $critere=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";
       //   }
	
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
		$query_filter=$query_principal.' '.$critere.'  '.$search;

		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row) 
		{

		$date_engage=("SELECT MOTIF_LIQUIDATION,DATE_ORDONNANCEMENT,DATE_PAIEMENT,DATE_DECAISSENMENT FROM execution_budgetaire_tache_detail WHERE EXECUTION_BUDGETAIRE_ID =".$row->EXECUTION_BUDGETAIRE_ID."");
		$date_engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$date_engage.'")');


		$retdate_engage ="";
		$u++;
		$engagement=array();
		if ($KEY2==1) {
			
            $retdate_engage = !empty($row->DATE_DEMANDE) ? $row->DATE_DEMANDE : 'N/A' ;
		$mona_de=number_format($row->ENG_BUDGETAIRE,0,',',' ');
		}else if ($KEY2==2) {
		$retdate_engage = (! empty($date_engage_req['DATE_LIQUIDATION'])) ? $date_engage_req['DATE_LIQUIDATION'] : 'N/A' ;
		$mona_de=number_format($row->LIQUIDATION,0,',',' ');
		}else if ($KEY2==3) {
		$retdate_engage = (! empty($date_engage_req['DATE_DECAISSEMENT'])) ? $date_engage_req['DATE_DECAISSEMENT'] : 'N/A' ;
		$mona_de=number_format($row->DECAISSEMENT,0,',',' ');
		}else if ($KEY2==4) {
		// $retdate_engage = (! empty($date_engage_req['DATE_ENG_JURIDIQUE'])) ? $date_engage_req['DATE_ENG_JURIDIQUE'] : 'N/A' ;
          $retdate_engage = !empty($row->DATE_ENG_JURIDIQUE) ? $row->DATE_ENG_JURIDIQUE : 'N/A' ;
		$mona_de=number_format($row->ENG_JURIDIQUE,0,',',' ');
		}else if ($KEY2==5) {
		$retdate_engage = (! empty($date_engage_req['DATE_ORDONNANCEMENT'])) ? $date_engage_req['DATE_ORDONNANCEMENT'] : 'N/A' ;	
		$mona_de=number_format($row->ORDONNANCEMENT,0,',',' ');	 
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



		if (strlen($retVal) < 13){
		$engagement[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
	   }else{
       $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($retVal, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a></label></font> </center>';

		 }
			if (strlen($row->RESULTAT_PAP_ACTIVITE) < 13){
		$engagement[] ='<center><font color="#000000" size=2><label>'.$row->RESULTAT_PAP_ACTIVITE.'</label></font> </center>';
	      }else{
       $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->RESULTAT_PAP_ACTIVITE, 0, 15).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		  }
		if (strlen($row->DESC_PAP_ACTIVITE) < 13){
		$engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESC_PAP_ACTIVITE.'</label></font> </center>';
	   }else{
       $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_PAP_ACTIVITE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a></label></font> </center>';

		}

	 $engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';

	  $engagement[] ='<center><font color="#000000" size=2><label>'.$retdate_engage.'</label></font> </center>';
		
		$engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->total,0,',',' ').'</label></font> </center>';

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
   ######liste des entites responsable
    function liste_institution_croise_dynamique() 
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
	  $inst_conn=$this->request->getVar('inst_conn');
	
	  $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	  $ACTION_ID=$this->request->getPost('ACTION_ID');
	  $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	  $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
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
		$One_code=(" SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
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
		$inst_code=(" SELECT INSTITUTION_ID FROM `inst_institutions` WHERE 1 ".$condition." ");
		$inst_code_req=$this->ModelPs->getRequete(' CALL getTable("'.$inst_code.'")');
		$code_inst='';
		$code_key2='';
		foreach ($inst_code_req as $key) {
		$code_key2.=$key->INSTITUTION_ID.',';
		}
		$code_inst =  substr($code_key2,0,-1);
		$cond_pri.=' AND inst_institutions.INSTITUTION_ID IN ('.$code_inst.')';
		$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';

		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		if ($IS_PRIVATE==1) {
		$totaux='BUDGET_T1';
		$cond_trim=" AND execution.TRIMESTRE_ID=1" ;

		}else if ($IS_PRIVATE==2) {
		$totaux='BUDGET_T2';
		$cond_trim=" AND execution.TRIMESTRE_ID=2";

		}else if ($IS_PRIVATE==3){
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


		if (! empty($SOUS_TUTEL_ID)) 
		{
		 $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
		}
	

       if(!empty($PROGRAMME_ID))
          {
      $cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID; 
          }

        if(!empty($ACTION_ID))
          {
      $cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID; 
          }

       if(!empty($LIGNE_BUDGETAIRE))
          {
      $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE; 
          }

       if(!empty($PAP_ACTIVITE_ID))
          {
      $cond.=" AND ptba_tache.PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID; 
          }
		////////////
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
$order_column = array(1,1,1,1,1,1,1,1,1,1,1,1,1);

$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY execution.EXECUTION_BUDGETAIRE_ID DESC';

$search = !empty($_POST['search']['value']) ? (' AND (execution.PTBA_TACHE_ID LIKE "%' . $var_search . '%" OR inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR inst_institutions.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR pap_activites.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%" OR ptba_tache.DESC_TACHE LIKE "%' . $var_search . '%" )') : '';

$conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
$conditionsfilter = $critaire.' '.$search.' '.$group;

$requetedebase = "SELECT execution.EXECUTION_BUDGETAIRE_ID,ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE service,".$totaux." as vote,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,ptba_tache.DESC_TACHE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,execution.PTBA_TACHE_ID, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID join inst_institutions_ligne_budgetaire ON  inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=execution.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond_trim."";


$requetedebase = str_replace("'", "\'", $requetedebase);
$requetedebases = $requetedebase . ' ' . $conditions;
$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
$fetch_projets = $this->ModelPs->datatable($query_secondaire);
$u=0;
$data = array();
		foreach ($fetch_projets as $row) 
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
    $engagement[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->CODE_NOMENCLATURE_BUDGETAIRE, 0, 30).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
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
	 #####fonction pour exporter les donnees en excel

	 function exporter($TYPE_INSTITUTION_ID='',$INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='',$LIGNE_BUDGETAIRE='',$IS_PRIVATE='' ,$PAP_ACTIVITE_ID='')
	{
    $TYPE_INSTITUTION_ID=$TYPE_INSTITUTION_ID;
		$INSTITUTION_ID=$INSTITUTION_ID;
		$SOUS_TUTEL_ID=$SOUS_TUTEL_ID;
		$PROGRAMME_ID=$PROGRAMME_ID;
		$ACTION_ID=$ACTION_ID;
		$PAP_ACTIVITE_ID=$PAP_ACTIVITE_ID;
		$IS_PRIVATE=$IS_PRIVATE;
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
				$One_code=(" SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
				$One_code_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$One_code.'")');
				$INSTITUTION_ID=$One_code_req['INSTITUTION_ID'];
			}else{
				$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');	
			}
		foreach ($user_inst_req as  $value)
		   {  
		$fonct_key2.=$value->INSTITUTION_ID.',';
			}
		$condition = " and INSTITUTION_ID IN (".substr($fonct_key2,0,-1).") " ;
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
		$cond_pri.=' AND inst_institutions.INSTITUTION_ID IN ('.$code_inst.')';
		$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';

    
		

		if(! empty($TYPE_INSTITUTION_ID))
		{
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
			$cond.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
		}
		// if(! empty($SOUS_TUTEL_ID))
		// {
		// 	$cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
		// }
		if(! empty($PROGRAMME_ID))
		{
			if ($TYPE_INSTITUTION_ID==2) {

				$cond.=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
			}
		}
		if(! empty($ACTION_ID))
		{
			$cond.=" AND ptba_tache.ACTION_ID=".$ACTION_ID; 
		}
		if($LIGNE_BUDGETAIRE !='')
		{
			$cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$LIGNE_BUDGETAIRE;
		}
		
		$totaux='';
	 	if ($IS_PRIVATE==1) {
	 	$cond_trim=" AND BUDGET_T1>0" ;
	 	$totaux='COALESCE(BUDGET_T1, 0)';
	    }else if ($IS_PRIVATE==2) {
	 	$cond_trim=" AND BUDGET_T2>0" ;
	 	$totaux='COALESCE(BUDGET_T2, 0)';

	 	}else if ($IS_PRIVATE==3) {

	 	$cond_trim=" AND BUDGET_T3>0" ;
	 	$totaux='COALESCE(BUDGET_T3, 0)';
	 	}else if ($IS_PRIVATE==4){
	 	 	$cond_trim=" AND BUDGET_T4>0" ;
	 	$totaux='COALESCE(BUDGET_T4, 0)';
	 	}else{
	 	$cond_trim=" " ;
	 	$totaux='COALESCE(BUDGET_T1, 0)+COALESCE(BUDGET_T2, 0)+COALESCE(BUDGET_T3, 0)+COALESCE(BUDGET_T4, 0)';
	 	}
		if(!empty($PAP_ACTIVITE_ID))
		{
			$cond.=' AND ptba_tache.PAP_ACTIVITE_ID='.$PAP_ACTIVITE_ID;
		}
        $getRequete="SELECT execution.EXECUTION_BUDGETAIRE_ID,ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE service,".$totaux." as vote,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,ptba_tache.DESC_TACHE,inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE,execution.PTBA_TACHE_ID, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pap_activites.DESC_PAP_ACTIVITE,pap_activites.RESULTAT_PAP_ACTIVITE,execution.ENG_BUDGETAIRE AS ENG_BUDGETAIRE,execution.LIQUIDATION AS LIQUIDATION, execution.ENG_JURIDIQUE AS ENG_JURIDIQUE, execution.DECAISSEMENT AS DECAISSEMENT,execution.ORDONNANCEMENT AS ORDONNANCEMENT,execution.PAIEMENT AS PAIEMENT FROM execution_budgetaire execution JOIN ptba_tache ON execution.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID JOIN pap_activites ON pap_activites.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID join inst_institutions_ligne_budgetaire ON  inst_institutions_ligne_budgetaire.CODE_NOMENCLATURE_BUDGETAIRE_ID=execution.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond." ".$cond_trim." ";

         
               $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')"); 
               $spreadsheet = new Spreadsheet();
               $sheet = $spreadsheet->getActiveSheet();
               $sheet->setCellValue('A1', 'INSTITUTIONS');
               $sheet->setCellValue('B1', 'PROGRAMMES');
               $sheet->setCellValue('C1', 'ACTION');
               $sheet->setCellValue('D1', 'LIGNE BUDGETAIRE');
               $sheet->setCellValue('E1', 'ACTIVITES');
               $sheet->setCellValue('F1', 'TACHES');
               $sheet->setCellValue('G1', 'ENGAGEMENT BUDGETAIRE');
               $sheet->setCellValue('H1', 'ENGAGEMENT JURIDIQUE');
               $sheet->setCellValue('I1', 'LIQUDATION');
               $sheet->setCellValue('J1', 'ORDONNANCEMENT');
               $sheet->setCellValue('K1', 'PAIEMENT');
               $sheet->setCellValue('L1', 'DECAISSEMENT');
               $rows = 3;
               foreach ($getData as $key)
               {
               	$sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
               	$sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
               	$sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
               	$sheet->setCellValue('D' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
               	$sheet->setCellValue('E' . $rows, $key->DESC_PAP_ACTIVITE);
               	$sheet->setCellValue('F' . $rows, $key->DESC_TACHE);
               	$sheet->setCellValue('G' . $rows, $key->ENG_BUDGETAIRE);
               	$sheet->setCellValue('H' . $rows, $key->ENG_JURIDIQUE);
               	$sheet->setCellValue('I' . $rows, $key->LIQUIDATION);
               	$sheet->setCellValue('J' . $rows, $key->ORDONNANCEMENT);
               	$sheet->setCellValue('K' . $rows, $key->PAIEMENT);
               	$sheet->setCellValue('L' . $rows, $key->DECAISSEMENT);
               	$rows++;
               } 
               $writer = new Xlsx($spreadsheet);
               $writer->save('world.xlsx');
               return $this->response->download('world.xlsx', null)->setFileName('Fichiers d\' execution par phases.xlsx');
               return redirect('dashboard/Dashboard_TCD_Taux_Engagement');
             }
              #####fonction pour appeler les librerie d'export
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
                      #####fonction pour appeler les librerie d'export
                 public function getBindParms($columnselect, $table, $where, $orderby)
                         {
                         	$db = db_connect();
                         	$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
                         	return $bindparams;
                         }
                      #####fonction pour supprimer les caracteres speciaux dans highchart
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
                         	$catego=str_replace("%"," ",$catego);
                         	$catego=str_replace("%!"," ",$catego);
                         	$catego=str_replace(""," ",$catego);
//                              $catego = str_replace(
//     ["é", "è", "ê", "ë", "à", "â", "ä", "ù", "û", "ü", "î", "ï", "ô", "ö", "ç", "’", "'"],
//     ["&eacute;", "&egrave;", "&ecirc;", "&euml;", "&agrave;", "&acirc;", "&auml;", "&ugrave;", "&ucirc;", "&uuml;", "&icirc;", "&iuml;", "&ocirc;", "&ouml;", "&ccedil;", "&rsquo;", "&#39;"],
//     $catego
// );

$catego= preg_replace('/[^a-zA-Z0-9À-ÿ ]/u', '',$catego);
                         	return $catego;
                         }
}
?>
