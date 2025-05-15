<?php
/**
 * @author NIYONGABO Emery
 *emery@mediabox.bi
 * Tableau de bord «dashbord des execution budgetaire»
 le 03/10/2023
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
###declaration d'une classe controlleur
class Dashboard_Transfert_budgetaire extends BaseController
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

    //fonction index
 	public function index($value='')
 	{
 		$data=$this->urichk();
 		$session  = \Config\Services::session();
 		// $requete_type="SELECT `TYPE_OPERATION_ID`,`DESCRIPTION_OPERATION` FROM `type_operation` WHERE 1";
 		// $data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
 		
 		$data['ann_actuel_id'] = $this->get_annee_budgetaire();
	    //Selection de l'année budgétaire
 		$get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID>=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
 		$data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
 		return view('App\Modules\dashboard\Views\Dashboard_Transfert_budgetaire_View',$data);
 	}
 			 ##fonction get_rapport qui permet d'afficher le rapport et appel des filtres qui dependent des autres
 	public function get_rapport()
 	{
 		$data=$this->urichk();
 		$db = db_connect();
 		$session  = \Config\Services::session();
 		$SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
 		$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
 		$ACTION_ID=$this->request->getVar('ACTION_ID');
 		$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
 		$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
 		$inst_conn=$this->request->getVar('inst_conn');
 		$ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');


 		if($IS_PRIVATE==1)
 		{
 			$totaux='SUM(T1)';
 			$cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=1" ;
 		}else if($IS_PRIVATE==2)
 		{
 			$totaux='SUM(T2)';
 			$cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=2" ;
 		}else if ($IS_PRIVATE==3)
 		{
 			$totaux='SUM(T3)';
 			$cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=3" ;
 		}else if ($IS_PRIVATE==4)
 		{
 			$totaux='SUM(T4)';
 			$cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=4" ;
 		}else
 		{
 			$totaux='SUM(T1+T2+T3+T4)';
 			$cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID IN(1,2,3,4)";
 		}
 		$cond1='';
 		$cond='';
 		$cond2='';
 		$KEY2=1;
 		$cond_program='';

 		$cond_budg="";

 		$budget=("SELECT inst_institutions.DESCRIPTION_INSTITUTION AS Name,inst_institutions.INSTITUTION_ID AS ID,SUM(`MONTANT_TRANSFERT`) AS enga FROM `transfert_historique_transfert` JOIN ptba_tache on transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond." GROUP BY inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID ORDER BY inst_institutions.CODE_INSTITUTION ASC");

 		$activites_exec=("SELECT inst_institutions.DESCRIPTION_INSTITUTION AS Name,inst_institutions.INSTITUTION_ID AS ID,COUNT(`HISTORIQUE_TRANSFERT_ID`) AS enga FROM `transfert_historique_transfert` JOIN ptba_tache on transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond."   GROUP BY inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID ORDER BY inst_institutions.CODE_INSTITUTION ASC");

 		$budget_req=$this->ModelPs->getRequete(' CALL getTable("'.$budget.'")');
 		$activite_req=$this->ModelPs->getRequete(' CALL getTable("'.$activites_exec.'")');
 		$data_budget_req='';
 		$data_total=0;
 		foreach ($budget_req as $value)
 		{
 			$pourcent=0;
 			$taux=("SELECT SUM(`MONTANT_TRANSFERT`) AS taux FROM `transfert_historique_transfert` WHERE 1 ");
 			$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');
 			if ($taux1['taux']>0)
 			{
 				$pourcent=($value->enga/$taux1['taux'])*100;
 			}
 			$data_budget_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ')." BIF)', y:".$value->enga.",color:'#000080',key:".$value->ID.",key2:".$KEY2."},";
 			$data_total=$data_total+$value->enga;
 		}
 		$data_activite_req='';
 		$data_activite_total=0;
 		foreach ($activite_req as $value)
 		{
 			$pourcent=0;
 			$taux=("SELECT SUM(`MONTANT_TRANSFERT`) AS taux FROM `transfert_historique_transfert` WHERE 1 ");
 			$taux1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$taux.'")');if ($taux1['taux']>0)
 			{
 				$pourcent=($value->enga/$taux1['taux'])*100;
 			}
 			$data_activite_req.="{name:'".$this->str_replacecatego($value->Name)." (".number_format($value->enga,0,',',' ').")', y:".$value->enga.",color:'#FF7F50',key:".$value->ID.",key2:".$KEY2."},";
 			$data_activite_total=$data_activite_total+$value->enga;
 		}
  	
	//print_r($data_budget_req);die();
	$rapp="<script type=\"text/javascript\">
	Highcharts.chart('container', {

	chart: {
	type: 'column'
	},
	title: {
	text: 'Budget transferé par institution<br> ".number_format($data_total,0,',',' ')." BIF',
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
	pointFormat: '<tr><td style=\"color:{series.color};padding:0\">',
	shared: true,
	useHTML: true
	},
	plotOptions: {
	column: {
	pointPadding: 0.2,
	borderWidth: 0,
	depth: 40,
	cursor:'pointer',
	point:{
	events: {
	click: function(){
	  
	if(this.key2==2){

		$(\"#idpro\").html(\" Actions \");
		$(\"#idcod\").html(\" Détails transferts budgetaire\");
		$(\"#idobj\").html(\"Programme\");
		$(\"#titre\").html(\"Liste des actions\");
	}else if(this.key2==3){
		$(\"#idpro\").html(\" activités\");
		$(\"#idcod\").html(\" Actions\");
		$(\"#idobj\").html(\" Programme \");
		$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==5){
		$(\"#idpro\").html(\" Activités\");
		$(\"#idcod\").html(\" Actions\");
		$(\"#idobj\").html(\" Programme \");
		$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==6){
		$(\"#idpro\").html(\" Activités\");
		$(\"#idcod\").html(\" Actions\");
		$(\"#idobj\").html(\" Programme \");
		$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==1){
		$(\"#idpro\").html(\" Activités\");
		$(\"#idcod\").html(\" Actions\");
		$(\"#idobj\").html(\" Programme \");
		$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else{
		$(\"#idpro\").html(\" Programmes  \");
		$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
		$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
		$(\"#titre\").html(\"Détails transferts budgetaire\");
	}
    $(\"#Budget\").html(\" Détails transferts budgetaire\");
	$(\"#myModal\").modal('show');
	var row_count ='1000000';
	$(\"#mytable\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"ajax\":{
	url:\"".base_url('dashboard/Dashboard_Transfert_budgetaire/detail_transfert_budgetaire')."\",
	type:\"POST\",
	data:{
		key:this.key,
		key2:this.key2,
	  }
	},
	lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
	pageLength:5,
	\"columnDefs\":[{
	\"targets\":[],
	\"orderable\":false
	}],
	dom: 'Bfrtlip',
	buttons: [
	'copy', 'csv', 'excel', 'pdf', 'print'
	],
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
	format: '{point.y:,3f} BIF'
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
	name:'Budget',
	data: [".$data_budget_req."]
	}
	]
	});
	</script>
	";

      
      //print_r($data_budget_req);die();
	$rapp1="<script type=\"text/javascript\">
	Highcharts.chart('container1', {

	chart: {
	type: 'column'
	},
	title: {
	text: 'Nombre de transfert  par institution: ".number_format($data_activite_total,0,',',' ')." ',
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
	pointFormat: '<tr><td style=\"color:{series.color};padding:0\">',
	shared: true,
	useHTML: true
	},
	plotOptions: {
	column: {
	pointPadding: 0.2,
	borderWidth: 0,
	depth: 40,
	cursor:'pointer',
	point:{
	events: {
	click: function(){
	if(this.key2==2){
	$(\"#idpro\").html(\" Actions \");
	$(\"#idcod\").html(\" Détails transferts budgetaire\");
	$(\"#idobj\").html(\"Programme\");
	$(\"#titre\").html(\"Liste des actions\");
	}else if(this.key2==3){
	$(\"#idpro\").html(\" activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==5){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==6){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Détails transferts budgetaire\");
	}else{
	$(\"#idpro\").html(\" Programmes\");
	$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
	$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
	$(\"#titre\").html(\"Détails transferts budgetaire\");
	}
    $(\"#Budget\").html(\" Détails transferts budgetaire\");
	$(\"#myModal\").modal('show');
	var row_count ='1000000';
	$(\"#mytable\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"ajax\":{
	url:\"".base_url('dashboard/Dashboard_Transfert_budgetaire/detail_transfert_budgetaire')."\",
	type:\"POST\",
	data:{
	key:this.key,
	key2:this.key2,
	}
	},
	lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
	pageLength:5,
	\"columnDefs\":[{
	\"targets\":[],
	\"orderable\":false
	}],
	dom: 'Bfrtlip',
	buttons: [
	'copy', 'csv', 'excel', 'pdf', 'print'
	],
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
	format: '{point.y:,3f} '
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
	name:'Transfert',
	data: [".$data_activite_req."]
	}
	]
	});
	</script>
	";

	$inst= '<option selected="" disabled="">sélectionner</option>';
	if (!empty($TYPE_INSTITUTION_ID))
	    {
	$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID as CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION  ORDER BY DESCRIPTION_INSTITUTION ASC';

		$inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
		foreach ($inst_sect_req as $key)
		{
			if (!empty($INSTITUTION_ID))
			{ 

				if ($INSTITUTION_ID==$key->CODE_INSTITUTION) 
				{
					$inst.= "<option value ='".$key->CODE_INSTITUTION."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
				else
				{
					$inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
			}
			else
			{
				$inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
			}
		}
	}



	$soustutel= '<option selected="" disabled="">sélectionner</option>';
	if ($INSTITUTION_ID != '')
	{
		$soustutel_sect="SELECT DISTINCT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE 1 ".$cond1." ORDER BY inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL ASC ";
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
		$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID as CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,CODE_INSTITUTION  ORDER BY DESCRIPTION_INSTITUTION ASC ';
		$inst_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$inst_sect.'")');
		foreach ($inst_sect_req as $key)
		 {

			if (!empty($INSTITUTION_ID))
			{ 
				if ($INSTITUTION_ID==$key->CODE_INSTITUTION) 
				{
					$inst.= "<option value ='".$key->CODE_INSTITUTION."' selected>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
				else
				{
					$inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
				}
			}
			else
			{
				$inst.= "<option value ='".$key->CODE_INSTITUTION."'>".trim($key->DESCRIPTION_INSTITUTION)."</option>";
			}
		}
	}

   
	$program= '<option selected="" disabled="">sélectionner</option>';
	if ($SOUS_TUTEL_ID != '')
	{
		$program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.CODE_PROGRAMME=inst_institutions_programmes.CODE_PROGRAMME WHERE 1 AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."' ".$cond_program."  ORDER BY inst_institutions_programmes.CODE_PROGRAMME ASC";

		$program_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$program_sect.'")');
		foreach ($program_sect_req as $key)
		{
			if (!empty($PROGRAMME_ID))
			{  
				if ($PROGRAMME_ID==$key->CODE_PROGRAMME) 
				{
					$program.= "<option value ='".$key->CODE_PROGRAMME."' selected>".trim($key->INTITULE_PROGRAMME)."</option>";
				}
				else
				{
					$program.= "<option value ='".$key->CODE_PROGRAMME."'>".trim($key->INTITULE_PROGRAMME)."</option>";
				}
			}
			else
			{
				$program.= "<option value ='".$key->CODE_PROGRAMME."'>".trim($key->INTITULE_PROGRAMME)."</option>";
			}
		}
	}

	$actions= '<option selected="" disabled="">sélectionner</option>';
	if ($PROGRAMME_ID != '')
	{
		$actions_sect='SELECT DISTINCT ptba_tache.CODE_ACTION,ptba_tache.LIBELLE_ACTION FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID where 1  '.$cond33.' ORDER BY CODE_ACTION ASC';
		$actions_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$actions_sect.'")');
		foreach ($actions_sect_req as $key)
		{
			if (!empty($ACTION_ID))
			{  
				if ($ACTION_ID==$key->CODE_ACTION) 
				{
					$actions.= "<option value ='".$key->CODE_ACTION."' selected>".trim($key->LIBELLE_ACTION)."</option>";
				}
				else
				{
					$actions.= "<option value ='".$key->CODE_ACTION."'>".trim($key->LIBELLE_ACTION)."</option>";
				}
			}
			else
			{
				$actions.= "<option value ='".$key->CODE_ACTION."'>".trim($key->LIBELLE_ACTION)."</option>";
			}
		}
	}


	$ligne_budgetaires= '<option selected="" disabled="">sélectionner</option>';
	if ($ACTION_ID!= '')
	{
		$ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ";
	}else{
		$ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ";  	
	}
	$ligne_budgetaire_sect_req = $this->ModelPs->getRequete('CALL getTable("'.$ligne_budgetaire_sect.'")');
	foreach ($ligne_budgetaire_sect_req as $key)
	{
		if (!empty($LIGNE_BUDGETAIRE))
		{  
			if ($LIGNE_BUDGETAIRE==$key->CODE_NOMENCLATURE_BUDGETAIRE) 
			{
				$ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE."' selected>".trim($key->CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
			}
			else
			{
				$ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE."'>".trim($key->CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
			}
		}
		else
		{
			$ligne_budgetaires.= "<option value ='".$key->CODE_NOMENCLATURE_BUDGETAIRE."'>".trim($key->CODE_NOMENCLATURE_BUDGETAIRE)."</option>";
		}
	}
	
	echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires));
}
  ###detail du rapport des projets vs montant par axe stratégique 
  function detail_transfert_budgetaires() 
      {
    $data=$this->urichk();
    $db=db_connect(); 
    $session  = \Config\Services::session();
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
		$cond1='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
      $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
      $nombre=count($user_connect_req);
      if ($nombre>1) {
         $cond1.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";
      }else{
      $cond1.='';  
      }
      }
      else{
        return redirect('Login_Ptba');
      }
	  $cond='';
    $cond_trim='';
	if ($IS_PRIVATE==1){
    $totaux='SUM(T1)';
   $cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=1" ;
    }else if ($IS_PRIVATE==2){
    $totaux='SUM(T2)';
    $cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=2" ;
    }else if ($IS_PRIVATE==3){
    $totaux='SUM(T3)';
    $cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=3" ;
    }else if ($IS_PRIVATE==4){
    $totaux='SUM(T4)';
    $cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID=4" ;
    }else{
    $totaux='SUM(T1+T2+T3+T4)';
    $cond_trim=" AND transfert_historique_transfert.TRIMESTRE_ID IN(1,2,3,4)" ;
     }
	
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="SELECT `PTBA_TACHE_ID_RECEPTION`,trimestre.DESC_TRIMESTRE,inst_institutions.DESCRIPTION_INSTITUTION as INTITULE_MINISTERE, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,ptba_tache.RESULTAT_ATTENDUS_TACHE as RESULTATS_ATTENDUS,REPLACE(RTRIM(transfert_historique_transfert.`MONTANT_TRANSFERT`),' ','') AS MONTANT_A_TRANSFERE, date_format(transfert_historique_transfert.DATE_ACTION,'%d-%m-%Y') as dat FROM `transfert_historique_transfert` LEFT JOIN trimestre ON trimestre.TRIMESTRE_ID=transfert_historique_transfert.TRIMESTRE_ID LEFT JOIN ptba_tache on transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT=ptba_tache.PTBA_TACHE_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN execution_budgetaire ON execution_budgetaire.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire_tache_detail sup ON sup.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 ";
    
	$limit='LIMIT 0,10';
	if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';

  $order_column=array(1,'DESCRIPTION_INSTITUTION',1,'MONTANT_TRANSFERT',1,'op_tranches.DESC_TRIMESTRE','inst_institutions_actions.LIBELLE_ACTION','RESULTAT_ATTENDUS_TACHE','inst_institutions_programmes.INTITULE_PROGRAMME','transfert_historique_transfert.DATE_ACTION');

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_INSTITUTION ASC';

	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%' OR date_format(transfert_historique_transfert.DATE_ACTION,'%d-%m-%Y') LIKE '%$var_search%' OR MONTANT_TRANSFERT LIKE '%$var_search%')") : '';

	$critere=" ";
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
	 $u++;
     $activite_recu=("SELECT DESC_TACHE  FROM ptba_tache WHERE PTBA_TACHE_ID=".$row->PTBA_TACHE_ID_RECEPTION." ");
     $activite_recu1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$activite_recu.'")');
     $activite_recu1 = !empty($activite_recu1['DESC_TACHE']) ? $activite_recu1['DESC_TACHE'] : 'N/A' ;
	   $sub_array=array();
    $retVal = !empty($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
		 $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		 $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';	
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
    $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_A_TRANSFERE,0,',',' ').'</label></font> </center>';
     $sub_array[] ='<center><font color="#000000" size=2><label>'.$activite_recu1.'</label></font> </center>';
     $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TRIMESTRE.'</label></font> </center>';
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
	 $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
	$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->dat.'</label></font> </center>';
	$data[] = $sub_array;        
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
	return $catego;
}

function listing()
{
    $data=$this->urichk();
    $db=db_connect(); 
    $session  = \Config\Services::session();
	// $TYPE_OPERATION_ID=$this->request->getVar('TYPE_OPERATION_ID');
	$cond1='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
	    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
	    $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
	    $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
	    $nombre=count($user_connect_req);
	    if ($nombre>1)
	    {
      $cond1.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";
      	}
      	else
      	{
      		$cond1.='';  
      	}
    }
    else
    {
    return redirect('Login_Ptba');
    }
	$cond='';
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
   	$query_principal="SELECT `PTBA_TACHE_ID_RECEPTION`,trimestre.DESC_TRIMESTRE,inst_institutions.DESCRIPTION_INSTITUTION as INTITULE_MINISTERE, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,ptba_tache.RESULTAT_ATTENDUS_TACHE as RESULTATS_ATTENDUS,REPLACE(RTRIM(transfert_historique_transfert.`MONTANT_TRANSFERT`),' ','') AS MONTANT_A_TRANSFERE, date_format(transfert_historique_transfert.DATE_ACTION,'%d-%m-%Y') as dat FROM `transfert_historique_transfert` LEFT JOIN trimestre ON trimestre.TRIMESTRE_ID=transfert_historique_transfert.TRIMESTRE_ID JOIN ptba_tache on transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID LEFT JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire ON execution_budgetaire.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN execution_budgetaire_tache_detail sup ON sup.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 ".$cond1."  ";
	$limit='LIMIT 0,10';
	if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';
  $order_column=array(1,'DESCRIPTION_INSTITUTION',1,'MONTANT_TRANSFERT',1,'op_tranches.DESC_TRIMESTRE','inst_institutions_actions.LIBELLE_ACTION','RESULTAT_ATTENDUS_TACHE','inst_institutions_programmes.INTITULE_PROGRAMME','transfert_historique_transfert.DATE_ACTION');

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_INSTITUTION ASC';

	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%' OR MONTANT_TRANSFERT LIKE '%$var_search%' OR  date_format(transfert_historique_transfert.DATE_ACTION,'%d-%m-%Y') LIKE '%$var_search%')") : '';

	$critere= '';
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	// print_r(count($fetch_data));die();
	$u = 0;
	$data = array();
	foreach ($fetch_data as $row) 
	{
	 $u++;
     $activite_recu=("SELECT DESC_TACHE  FROM ptba_tache WHERE PTBA_TACHE_ID=".$row->PTBA_TACHE_ID_RECEPTION." ");
     $activite_recu1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$activite_recu.'")');
     $activite_recu1 = !empty($activite_recu1['DESC_TACHE']) ? $activite_recu1['DESC_TACHE'] : 'N/A' ;
	   $sub_array=array();
    $retVal = !empty($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
		 $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		 if (strlen($row->INTITULE_MINISTERE) < 13){
		$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
	      }else{
       $sub_array[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_MINISTERE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		 }
		 if (strlen($row->DESC_TACHE) < 13){
		$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
	      }else{
       $sub_array[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_TACHE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		 }
    $sub_array[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_A_TRANSFERE,0,',',' ').'</label></font> </center>';
    if (strlen($activite_recu1) < 13){
	  $sub_array[] ='<center><font color="#000000" size=2><label>'.$activite_recu1.'</label></font> </center>';
	      }else{
     $sub_array[] ='<center><font color="#000000" size=2><label>'.mb_substr($activite_recu1, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$activite_recu1.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		 }
     $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TRIMESTRE.'</label></font> </center>';
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
    $sub_array[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
    if (strlen($row->INTITULE_PROGRAMME) < 13){
		$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
	      }else{
       $sub_array[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		 }
	$sub_array[] ='<center><font color="#000000" size=2><label>'.$row->dat.'</label></font> </center>';
	$data[] = $sub_array;        
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
function exporter($ANNEE_BUDGETAIRE_ID)
{
	$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
	if(empty($USER_IDD))
	{
		return redirect('Login_Ptba/do_logout');
	}
	$db = db_connect();
	$callpsreq = "CALL getRequete(?,?,?,?);";
	 $cond1='';
    if(!empty(session()->get("SESSION_SUIVIE_PTBA_USER_ID")))
    {
	    $user_id = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
	    $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
	    $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
	    $nombre=count($user_connect_req);
	    if ($nombre>1)
	    {
      $cond1.=" AND ptba_tache.INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";
      	}
      	else
      	{
      		$cond1.='';  
      	}
    }
    else
    {
     return redirect('Login_Ptba');
    }
	$cond='';

	// if($TYPE_OPERATION_ID>0)
	// {
	// 	$cond.=' AND transfert_historique_transfert.TYPE_OPERATION_ID='.$TYPE_OPERATION_ID;
	// }

	// if($ANNEE_BUDGETAIRE_ID>0)
	// {
	// 	$cond.=' AND ptba_tache.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
	// }
	$getRequete="SELECT `PTBA_TACHE_ID_RECEPTION`,trimestre.DESC_TRIMESTRE,inst_institutions.DESCRIPTION_INSTITUTION as INTITULE_MINISTERE, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,ptba_tache.RESULTAT_ATTENDUS_TACHE as RESULTATS_ATTENDUS,REPLACE(RTRIM(transfert_historique_transfert.`MONTANT_TRANSFERT`),' ','') AS MONTANT_A_TRANSFERE, date_format(transfert_historique_transfert.DATE_ACTION,'%d-%m-%Y') as dat FROM `transfert_historique_transfert` LEFT JOIN trimestre ON trimestre.TRIMESTRE_ID=transfert_historique_transfert.TRIMESTRE_ID JOIN ptba_tache on transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID LEFT JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID LEFT JOIN execution_budgetaire ON execution_budgetaire.EXECUTION_BUDGETAIRE_ID=execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN execution_budgetaire_tache_detail sup ON sup.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 ".$cond1." ";
	$query_secondaire = 'CALL `getTable`("' . $getRequete . '");';
	$getData = $this->ModelPs->datatable($query_secondaire);
	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setCellValue('A1', 'INSTITUTION');
	$sheet->setCellValue('B1', 'ACTIVITES ORIGINE');
	$sheet->setCellValue('C1', 'MONTANT TRANSFERT');
	$sheet->setCellValue('D1', 'ACTIVITES DESTINATION');
	$sheet->setCellValue('E1', 'TRIMESTRE ORIGINE');
	$sheet->setCellValue('F1', 'ACTIONS');
	$sheet->setCellValue('G1', 'RESULTATS ATTENDUS');
	$sheet->setCellValue('H1', 'PROGRAMME');
	$sheet->setCellValue('I1', 'DATE');
	$rows = 3;
	foreach ($getData as $key)
	{
	  $activite_recu=("SELECT DESC_TACHE  FROM ptba_tache WHERE PTBA_TACHE_ID=".$key->PTBA_TACHE_ID_RECEPTION." ");
	  $activite_recu1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$activite_recu.'")');
	  $activite_recu1 = !empty($activite_recu1['DESC_TACHE']) ? $activite_recu1['DESC_TACHE'] : 'N/A' ;
		$sheet->setCellValue('A' . $rows, $key->INTITULE_MINISTERE);
		$sheet->setCellValue('B' . $rows, $key->DESC_TACHE);
		$sheet->setCellValue('C' . $rows, $key->MONTANT_A_TRANSFERE);
		$sheet->setCellValue('D' . $rows, $activite_recu1);
		$sheet->setCellValue('E' . $rows, $key->DESC_TRIMESTRE);
		$sheet->setCellValue('F' . $rows, !empty($key->LIBELLE_ACTION) ? $key->LIBELLE_ACTION : 'N/A');
		$sheet->setCellValue('G' . $rows, $key->RESULTATS_ATTENDUS);
		$sheet->setCellValue('H' . $rows, $key->INTITULE_PROGRAMME);
		$sheet->setCellValue('I' . $rows, date('d-m-Y',strtotime($key->dat)));
		$rows++;
	} 
	$writer = new Xlsx($spreadsheet);
	$writer->save('world.xlsx');
	return $this->response->download('world.xlsx', null)->setFileName('Liste des transferts budgetaire.xlsx');
	return redirect('dashboard/Dashboard_Transfert_budgetaire');
}

}
?>

