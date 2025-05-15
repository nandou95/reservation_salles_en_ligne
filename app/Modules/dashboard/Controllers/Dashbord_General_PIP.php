<?php
/**
 * @author NIYONGABO Emery
 *emery@mediabox.bi
 * Tableau de bord «dashbord general»
 le 09/02/2024
 */ //Appel de l'esp\ce de nom du Controllers
 namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
 ###declaration d'une classe controlleur
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
class Dashbord_General_PIP extends BaseController
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
 		  	$db=db_connect();
 		  	$data=$this->urichk();
 		  	$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
 		  	$requete_inst=("SELECT inst_institutions.`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION` FROM `inst_institutions` WHERE 1 ");
 		  	$date="date_format(DATE_DEBUT_PROJET,'%Y')";
 		  	$req='SELECT DISTINCT '.$date.' AS annee  FROM `pip_demande_infos_supp` WHERE 1 ORDER BY annee DESC';
 		  	$data['annees']=$this->ModelPs->getRequete('CALL getTable("'.$req.'")');
 		  	$axes="SELECT `ID_AXE_INTERVENTION_PND`, `DESCR_AXE_INTERVATION_PND` FROM `axe_intervention_pnd` WHERE 1";
 		  	$requete_type="SELECT  TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Institution','Ministère') as Name FROM `inst_institutions` WHERE 1  group by TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'Institution','Ministère')";
 		  	$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
 		  	$data['TYPE_INSTITUTION_ID']=$this->request->getPost('');
 		  	$data['axe_intervations']=$this->ModelPs->getRequete('CALL getTable("'.$axes.'")');
 		  	$data['INSTITUTION_ID1']=0;
 		  	$data['institutions']=$this->ModelPs->getRequete('CALL getTable("'.$requete_inst.'")');
 		  	$data['INSTITUTION_ID']=$this->request->getPost('');
 		  	return view('App\Modules\dashboard\Views\Dashbord_General_PIP_View',$data);
 		  }

        ##fonction get_rapport qui permet d'afficher le rapport et appel des filtres qui dependent des autres
  public function get_rapport()
    {

    	ini_set('max_execution_time', 2000);
    	ini_set('memory_limit','2048M');
    	$data=$this->urichk();
    	$db = db_connect(); 
    	$TYPE_INSTITUTION_ID=$this->request->getVar('TYPE_INSTITUTION_ID');
    	$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
    	$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
    	$SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
    	$ACTION_ID=$this->request->getVar('ACTION_ID');
    	$LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
    	$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
    	$inst_conn=$this->request->getVar('inst_conn');
    	if (!empty($inst_conn)) {
    		$code_inst=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE INSTITUTION_ID=".$inst_conn."");
    		$code_inst_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$code_inst.'")');
    		if (! empty($code_inst_req['INSTITUTION_ID'])){
    			$code_inst_connect =$code_inst_req['INSTITUTION_ID'];
    			$cond_inst=" AND INSTITUTION_ID='".$code_inst_connect."'";
    			$INSTITUTION_ID=$code_inst_req['INSTITUTION_ID'];
    		}
    	}else{
    		$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
    		$cond_inst='';
    	}
    	if ($IS_PRIVATE==1){
    		$totaux='SUM(T1)';
    		$cond_trim=" AND date_format(demande.DATE_INSERTION,'%m') Between '01' AND '03'" ;
    	}else if ($IS_PRIVATE==2){
    		$totaux='SUM(T2)';
    		$cond_trim=" AND date_format(demande.DATE_INSERTION,'%m') Between '04' AND '06'" ;
    	}else if ($IS_PRIVATE==3){
    		$totaux='SUM(T3)';
    		$cond_trim=" AND date_format(demande.DATE_INSERTION,'%m') Between '07' AND '09'" ;
    	}else if ($IS_PRIVATE==4){
    		$totaux='SUM(T4)';
    		$cond_trim=" AND date_format(demande.DATE_INSERTION,'%m') Between '07' AND '09'" ;
    	}else{
    		$totaux='SUM(T1+T2+T3+T4)';
    		$cond_trim=" AND date_format(demande.DATE_INSERTION,'%m') Between '10' AND '12'" ;
    	}
    	$cond1='';
    	$cond='';
    	$cond2='';
    	$KEY2=1;
    	$cond_program='';
    	$titr_deux=' par catégorie';
    	$titr_deux2=' par catégorie';
    	$id_decl= 'TYPE_INSTITUTION_ID'; 
    	$name_decl= "if(TYPE_INSTITUTION_ID=1,'Institution','Ministère')";
    	$format=" {point.y:.3f} %";
    	$type="column";
    	$name_table1="";
    	$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID";
    	$cond='';
    	if(! empty($TYPE_INSTITUTION_ID))
    	{
    		$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID";
    		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    	}
    	if(! empty($INSTITUTION_ID))
    	{
    	$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
    	}
    	if(! empty($PROGRAMME_ID))
    	{
    	$cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
    	}
    	if(! empty($ACTION_ID))
    	{
    	$cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
    	}
    	$pip_pilier=("SELECT pilier.DESCR_PILIER AS Name,pilier.ID_PILIER AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp` LEFT JOIN pilier ON pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." GROUP BY ID,Name");
    	$pip_pilier_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_pilier.'")');
    	$data_pip_pilier_req='';
    	$data_pip_pilier=0;
    	foreach ($pip_pilier_req as $value)
    	{
    		$color=$this->getcolor();
    		$name = (!empty($value->Name)) ? $value->Name : "Autres" ;
    		$data_pip_pilier_req.="{name:'".$this->str_replacecatego($name)."',y:".$value->enga.",key:'".$value->ID."',color:'".$color."'},";
    		$data_pip_pilier=$data_pip_pilier+$value->enga;
    	}
	$rapp="<script type=\"text/javascript\">
	Highcharts.chart('container', {
	chart: {
	type: 'bar'
	    },
	 title: {
   text: '".lang("messages_lang.pip_pilier").":::: ".number_format($data_pip_pilier,0,',',' ')."',
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
	bar: {
	pointPadding: 0.2,
	borderWidth: 0,
	depth: 40,
	cursor:'pointer',
	point:{
	events: {
	click: function(){
	if(this.key2==2){
	$(\"#idpro\").html(\" Actions \");
	$(\"#idcod\").html(\" Objctif&nbspde&nbspl\'action \");
	$(\"#idobj\").html(\"Programme\");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivites\");
	}else if(this.key2==3){
	$(\"#idpro\").html(\" activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivites\");
	}else if(this.key2==5){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else if(this.key2==6){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else{
	$(\"#idpro\").html(\" Programmes  \");
	$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
	$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
	}
    $(\"#titre\").html(\"".lang("messages_lang.pip_pilier_list").":::: \" +this.name);
	$(\"#myModal\").modal('show');
	var row_count ='1000000';
	$(\"#mytable\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"oreder\":[],
	\"ajax\":{
	url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_pilier')."\",
	type:\"POST\",
	data:{
	key:this.key,
	key2:this.key2,
	INSTITUTION_ID:$('#INSTITUTION_ID').val(),
	TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
	PROGRAMME_ID:$('#PROGRAMME_ID').val(),
	ACTION_ID:$('#ACTION_ID').val(),
	LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
	}
	},
	lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
	format: '{point.y:,3f}'
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
	name:'PIP',
	data: [".$data_pip_pilier_req."]
	}
	]
	});
	</script>
	";
$pip_axe_pnd=("SELECT axe_intervention_pnd.DESCR_AXE_INTERVATION_PND AS Name,axe_intervention_pnd.ID_AXE_INTERVENTION_PND AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp`  LEFT JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0".$cond." GROUP BY axe_intervention_pnd.ID_AXE_INTERVENTION_PND,axe_intervention_pnd.ID_AXE_INTERVENTION_PND ORDER BY enga DESC");
	$pip_axe_pnd_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_axe_pnd.'")');
	$data_pip_axe_pnd_req='';
	$data_pip_axe_pnd=0;
	foreach ($pip_axe_pnd_req as $value)
	{
	$color=$this->getcolor();
	$name = (!empty($value->Name)) ? $value->Name : "Autres" ;
	$data_pip_axe_pnd_req.="{name:'".$this->str_replacecatego($name)."', y:".$value->enga.",key:'".$this->str_replacecatego($value->ID)."',color:'".$color."'},";
	$data_pip_axe_pnd=$data_pip_axe_pnd+$value->enga;
	}
	$rapp2="<script type=\"text/javascript\">
	Highcharts.chart('container2', {
	chart: {
	type: 'bar'
	    },
	 title: {
   text: '".lang("messages_lang.pip_pilier_axes_intervention").":::: ".number_format($data_pip_axe_pnd,0,',',' ')."',
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
	bar: {
	pointPadding: 0.2,
	borderWidth: 0,
	depth: 40,
	cursor:'pointer',
	point:{
	events: {
	click: function(){
	if(this.key2==2){
	$(\"#idpro\").html(\" Actions \");
	$(\"#idcod\").html(\" Objctif&nbspde&nbspl\'action \");
	$(\"#idobj\").html(\"Programme\");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivites\");
	}else if(this.key2==3){
	$(\"#idpro\").html(\" activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivites\");
	}else if(this.key2==5){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else if(this.key2==6){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	}else{
	$(\"#idpro\").html(\" Programmes  \");
	$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
	$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
	}
    $(\"#titre1\").html(\"".lang("messages_lang.pip_pilier_list")." :::: \" +this.name);
	$(\"#myModal1\").modal('show');
	var row_count ='1000000';
	$(\"#mytable1\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"oreder\":[],
	\"ajax\":{
	url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_axe')."\",
	type:\"POST\",
	data:{
	key:this.key,
	key2:this.key2,
	INSTITUTION_ID:$('#INSTITUTION_ID').val(),
	TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
	PROGRAMME_ID:$('#PROGRAMME_ID').val(),
	ACTION_ID:$('#ACTION_ID').val(),
	LIGNE_BUDGETAIRE:$('#LIGNE_BUDGETAIRE').val(),
	}
	},
	lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
	format: '{point.y:,3f}'
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
	name:'PIP',
	data: [".$data_pip_axe_pnd_req."]
	}
	]
	});
	</script>
	";

	  ###### rapport des budgets vote par grande masse
	$pip_bailleur="SELECT pip_source_financement_bailleur.NOM_SOURCE_FINANCE AS Name,pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp` LEFT JOIN pip_demande_source_financement ON pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP LEFT JOIN pip_source_financement_bailleur ON pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR=pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." GROUP BY pip_source_financement_bailleur.NOM_SOURCE_FINANCE,pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR ORDER BY enga DESC";
		$pip_bailleur_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_bailleur.'")');
        $donnees="";
        $total=0;
     foreach ($pip_bailleur_req as $value)
	    {
	$color=$this->getcolor();
	$name = (!empty($value->Name)) ? $value->Name : "Autres" ;
	$donnees.="{name:'".$this->str_replacecatego($name)."', y:".$value->enga.",key:'".$this->str_replacecatego($value->ID)."',color:'".$color."'},";
	$total=$total+$value->enga;
	   }
  $rapp3="<script type=\"text/javascript\">
   Highcharts.chart('container3',{ 
	    chart: {
	  	type: 'column'
		    },
	    title:{
	   	text: '<b> ".lang("messages_lang.pip_pilier_source_financement")." :::: ".number_format($total,0,',',' ')."</b>'
		   },
	    subtitle:{
		    text: ''
	    	  },
	 xAxis: {
		 type: 'category',
		
		},
	 yAxis: {
		min: 0,
		title: {
			text: ''
		  }
		  },
		tooltip: {
			headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
			pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
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
				$(\"#myModal2\").modal('show');
				$(\"#titre2\").html(\"".lang("messages_lang.pip_pilier_list").":::: \" +this.name);
				var row_count ='1000000';
				$(\"#mytable2\").DataTable({
					\"processing\":true,
					\"serverSide\":true,
					\"bDestroy\": true,
					\"oreder\":[],
					\"ajax\":{
						url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_bailleur')."\",
						type:\"POST\",
						data:{
					key:this.key,
				   INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                   TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                   PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                   ACTION_ID:$('#ACTION_ID').val(),
						   }
						   },
						lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
						dataLabels:{
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
							labels: {
								items: [{
									html: '',
									style: {
										left: '50px',
										top: '18px',
										color: ( // theme
										Highcharts.defaultOptions.title.style &&
										Highcharts.defaultOptions.title.style.color
										) || 'black'
									}
									}]
									},
									series: [
								    	{
										name:'PIP',
										data: [".$donnees."],
									}
								]
							})
						</script>
								";

		 ###### rapport des PIP par statut projet
	$pip_statut_projet=("SELECT pip_statut_projet.DESCR_STATUT_PROJET AS Name,pip_statut_projet.ID_STATUT_PROJET AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp`  LEFT JOIN pip_statut_projet ON pip_statut_projet.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." GROUP BY pip_statut_projet.ID_STATUT_PROJET,pip_statut_projet.DESCR_STATUT_PROJET ORDER BY enga DESC");
	$pip_statut_projet_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_statut_projet.'")');
	$data_pip_statut_req='';
	$data_pip_statut=0;
	foreach ($pip_statut_projet_req as $value)
	  {
	 $color=$this->getcolor();
	 $name = (!empty($value->Name)) ? $value->Name : "Autres";
	 $data_pip_statut_req.="{name:'".$this->str_replacecatego($name)."', y:".$value->enga.",key:'".$this->str_replacecatego($value->ID)."',color:'".$color."'},";
	  $data_pip_statut=$data_pip_statut+$value->enga;
	  }
   //script du rapport de cinq plus grands bilatéraux
   $rapp4="<script type=\"text/javascript\">
		Highcharts.chart('container4',{
		  chart: {
		  type: 'pie'
		     }, 
		title: {
		text: '<b>".lang("messages_lang.pip_pilier_statut")." :::: ".number_format($data_pip_statut,0,',',' ')."',
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
		// alert($('#SOUS_TUTEL_ID').val());
		if(this.key2==1){
		$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
		}else if(this.key2==2){
		$(\"#idpro\").html(\"Liquidation\");
		}else if(this.key2==3){
		$(\"#idpro\").html(\" Décaissement\");
		}else if(this.key2==4){
		$(\"#idpro\").html(\"Engagement&nbspjurdique\");
		}else if(this.key2==6){
		$(\"#idpro\").html(\"Paiement\");
		}else{
		$(\"#idpro\").html(\"Ordonnancement\");	
		}
		if(this.key3==1){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp1er&nbsptrimestre\");
		}else if(this.key3==2){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp2ème&nbsptrimestre\");
		}else if(this.key3==3){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp3ème&nbsptrimestre\");
		}else if(this.key3==5){
		$(\"#trim\").html(\"Budget&nbsp&nbspannuel\");
		}else{
		$(\"#trim\").html(\"Budget&nbspdu&nbsp4ème&nbsptrimestre\");
		}
		$(\"#titre3\").html(\"".lang("messages_lang.pip_pilier_list")." :::: \" +this.name);
		$(\"#myModal3\").modal('show');
		var row_count ='1000000';
		$(\"#mytable3\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"oreder\":[],
		\"ajax\":{
		url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_statut_projet')."\",
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
		pageLength: 10,
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
		format: '{point.name} : {point.y:,1f}'
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
		name:'PIP',
		data: [".$data_pip_statut_req."]
		}
		]
		});
		</script>
		";
   ###### rapport des PIP par statut projet
	$pip_secteur_interva=("SELECT objectif_strategique_pnd.DESCR_OBJECTIF_STRATEGIC_PND AS Name,objectif_strategique_pnd.ID_OBJECT_STRATEGIC_PND AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp`  LEFT JOIN objectif_strategique_pnd ON objectif_strategique_pnd.ID_OBJECT_STRATEGIC_PND=pip_demande_infos_supp.ID_OBJECT_STRATEGIC_PND JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." GROUP BY objectif_strategique_pnd.ID_OBJECT_STRATEGIC_PND,objectif_strategique_pnd.DESCR_OBJECTIF_STRATEGIC_PND ORDER BY enga DESC");
	$pip_secteur_interva_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_secteur_interva.'")');
	 $data_secteur_interva='';
	 $data_total_interva=0;
	foreach ($pip_secteur_interva_req as $value)
	   {
	$color=$this->getcolor();
	$name = (!empty($value->Name)) ? $value->Name : "Autres";
	$data_secteur_interva.="{name:'".$this->str_replacecatego($name)."', y:".$value->enga.",key:'".$this->str_replacecatego($value->ID)."',color:'".$color."'},";
	$data_total_interva=$data_total_interva+$value->enga;
	   }
      //script du rapport de cinq plus grands bilatéraux
   $rapp5="<script type=\"text/javascript\">
   Highcharts.chart('container5',{ 
	    chart: {
	  	type: 'column'
		    },
	    title:{
	   	text: '<b> ".lang("messages_lang.pip_pilier_object_strat").":::: ".number_format($data_total_interva,0,',',' ')."</b>'
		   },
	    subtitle:{
		    text: ''
	    	  },
	 xAxis: {
		 type: 'category',
		
		},
	 yAxis: {
		min: 0,
		title: {
			text: ''
		  }
		  },
		tooltip: {
			headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
			pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
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
				$(\"#myModal4\").modal('show');
				$(\"#titre4\").html(\"".lang("messages_lang.pip_pilier_list").":::: \" +this.name);
				var row_count ='1000000';
				$(\"#mytable4\").DataTable({
					\"processing\":true,
					\"serverSide\":true,
					\"bDestroy\": true,
					\"oreder\":[],
					\"ajax\":{
						url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_secteur')."\",
						type:\"POST\",
						data:{
				       key:this.key,
				       INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                       TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                       PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                       ACTION_ID:$('#ACTION_ID').val(),
						   }
						   },
						lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
						dataLabels:{
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
							labels: {
								items: [{
									html: '',
									style: {
										left: '50px',
										top: '18px',
										color: ( // theme
										Highcharts.defaultOptions.title.style &&
										Highcharts.defaultOptions.title.style.color
										) || 'black'
									}
									}]
									},
									series: [
								    	{
										name:'PIP',
										data: [".$data_secteur_interva."],
										marker: {
											lineWidth: 2,
											lineColor: Highcharts.getOptions().colors[3],
											fillColor: 'white'
										}
									}
								]
							})
						</script>";

    ############ projets par lieu d'interventions
	$pip_lieu_intervention="SELECT provinces.PROVINCE_NAME AS Name,provinces.PROVINCE_ID AS ID,COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp` LEFT JOIN pip_lieu_intervention_projet ON pip_lieu_intervention_projet.ID_DEMANDE_INFO_SUPP=pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP LEFT JOIN provinces ON provinces.PROVINCE_ID=pip_lieu_intervention_projet.ID_PROVINCE JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  WHERE 1  AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." GROUP BY provinces.PROVINCE_NAME,provinces.PROVINCE_ID ORDER BY enga DESC";
		$pip_lieu_intervention_req=$this->ModelPs->getRequete(' CALL getTable("'.$pip_lieu_intervention.'")');
        $donnees_lieu="";
        $total_lieu=0;
      foreach ($pip_lieu_intervention_req as $value)
	    {
	$color=$this->getcolor();
	$name = (!empty($value->Name)) ? $value->Name : "Autres" ;
	$donnees_lieu.="{name:'".$this->str_replacecatego($name)."', y:".$value->enga.",key:'".$this->str_replacecatego($value->ID)."',color:'".$color."'},";
	$total_lieu=$total_lieu+$value->enga;
	    }
  $rapp6="<script type=\"text/javascript\">
   Highcharts.chart('container6',{ 
	    chart: {
	  	type: 'column'
		    },
	    title:{
	   	text: '<b> ".lang("messages_lang.pip_pilier_lieu_intervention")." :::: ".number_format($total_lieu,0,',',' ')."</b>'
		   },
	    subtitle:{
		    text: ''
	    	  },
	 xAxis: {
		 type: 'category',
		
		},
	 yAxis: {
		min: 0,
		title: {
			text: ''
		  }
		  },
		tooltip: {
			headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
			pointFormat: '<tr><td style=\"color:{series.color};padding:0\"></td>',
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
				$(\"#myModal6\").modal('show');
				$(\"#titre6\").html(\"".lang("messages_lang.pip_pilier_list")." :::: \" +this.name);
				var row_count ='1000000';
				$(\"#mytable6\").DataTable({
					\"processing\":true,
					\"serverSide\":true,
					\"bDestroy\": true,
					\"oreder\":[],
					\"ajax\":{
						url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_lieu_intervention')."\",
						type:\"POST\",
						data:{
				       key:this.key,
				       INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                       TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
                       PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                       ACTION_ID:$('#ACTION_ID').val(),
						   }
						   },
						lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
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
						dataLabels:{
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
							labels: {
								items: [{
									html: '',
									style: {
										left: '50px',
										top: '18px',
										color: ( // theme
										Highcharts.defaultOptions.title.style &&
										Highcharts.defaultOptions.title.style.color
										) || 'black'
									}
									}]
									},
									series: [
								    	{
										name:'PIP',
										data: [".$donnees_lieu."],
										marker: {
											lineWidth: 2,
											lineColor: Highcharts.getOptions().colors[3],
											fillColor: 'white'
										}
									}
								]
							})
						</script>";
	$etude_fait=("SELECT COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  WHERE pip_demande_infos_supp.A_UNE_ETUDE=1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." ");
	$etude_non_fait=("SELECT COUNT(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP) AS enga FROM `pip_demande_infos_supp` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE pip_demande_infos_supp.A_UNE_ETUDE=0 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond." ");
		$etude_fait1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$etude_fait.'")');
		$etude_fait11= ($etude_fait1['enga']>0) ? $etude_fait1['enga'] : 0 ;
		$etude_non_fait1=$this->ModelPs->getRequeteOne(' CALL getTable("'.$etude_non_fait.'")');
	    $etude_non_fait11= ($etude_non_fait1['enga']>0) ? $etude_non_fait1['enga'] : 0 ;
         $total_etude=$etude_fait11+$etude_non_fait11;
		$data_etude="{name:'".lang("messages_lang.pip_etude_avec_etude")." (".number_format($etude_fait11,0,',',' ')." )', y:(".$etude_fait11."),key2:1},";
		$data_sans_etude="{name:'".lang("messages_lang.pip_etude_sans_etude")." (".number_format($etude_non_fait11,0,',',' ').")', y:(".$etude_non_fait11."),key2:2},";
	$rapp_etude="<script type=\"text/javascript\">
		Highcharts.chart('container_etude',{

		chart: {
		type: 'pie'
		}, 
		title: {
		text: '".lang("messages_lang.pip_pilier_etude_sans_etude")." :::".number_format($total_etude,0,',',' ')."',
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
		// alert($('#SOUS_TUTEL_ID').val());
		if(this.key2==1){
		$(\"#idpro\").html(\"Engagement&nbspbudgétaire\");
		}else if(this.key2==2){
		$(\"#idpro\").html(\"Liquidation\");
		}else if(this.key2==3){
		$(\"#idpro\").html(\" Décaissement\");
		}else if(this.key2==4){
		$(\"#idpro\").html(\"Engagement&nbspjurdique\");
		}else if(this.key2==6){
		$(\"#idpro\").html(\"Paiement\");
		}else{
		$(\"#idpro\").html(\"Ordonnancement\");	
		}
		if(this.key3==1){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp1er&nbsptrimestre\");
		}else if(this.key3==2){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp2ème&nbsptrimestre\");
		}else if(this.key3==3){
		$(\"#trim\").html(\"Budget&nbspdu&nbsp3ème&nbsptrimestre\");
		}else if(this.key3==5){
		$(\"#trim\").html(\"Budget&nbsp&nbspannuel\");
		}else{
		$(\"#trim\").html(\"Budget&nbspdu&nbsp4ème&nbsptrimestre\");
		}
		$(\"#titre_etude\").html(\"".lang("messages_lang.pip_pilier_list")."::::\" +this.name);
		$(\"#myModal_etude\").modal('show');
		var row_count ='1000000';
		$(\"#mytable_etude\").DataTable({
		\"processing\":true,
		\"serverSide\":true,
		\"bDestroy\": true,
		\"oreder\":[],
		\"ajax\":{
		url:\"".base_url('dashboard/Dashbord_General_PIP/detail_pip_etude')."\",
		type:\"POST\",
		data:
		 {
		key:this.key,
		key2:this.key2,
		key3:this.key3,
		INSTITUTION_ID:$('#INSTITUTION_ID').val(),
		TYPE_INSTITUTION_ID:$('#TYPE_INSTITUTION_ID').val(),
		PROGRAMME_ID:$('#PROGRAMME_ID').val(),
		ACTION_ID:$('#ACTION_ID').val(),
		SOUS_TUTEL_ID:$('#SOUS_TUTEL_ID').val(),
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
		format: '{point.name} : {point.y:,3f}'
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
		name:'PIP',
		data: [".$data_etude.$data_sans_etude."]
		}
		]
		});
		</script>
		";
		$inst= '<option selected="" disabled="">'.lang("messages_lang.label_selecte").'</option>';
		if (!empty($TYPE_INSTITUTION_ID))
		{
			$inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID FROM inst_institutions LEFT JOIN pip_demande_infos_supp ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

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

		$program= '<option selected="" disabled="">'.lang("messages_lang.label_selecte").'</option>';
		if ($INSTITUTION_ID != '')
		{
			$program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID LEFT JOIN pip_demande_infos_supp ON pip_demande_infos_supp.ID_PROGRAMME=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND inst_institutions_programmes.INSTITUTION_ID=".$INSTITUTION_ID."  ORDER BY inst_institutions_programmes.PROGRAMME_ID ASC";

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
		$actions= '<option selected="" disabled="">'.lang("messages_lang.label_selecte").'</option>';
		if ($PROGRAMME_ID != '')
		{
			$actions_sect='SELECT DISTINCT inst_institutions_actions.ACTION_ID,inst_institutions_actions.LIBELLE_ACTION FROM `inst_institutions_actions` LEFT JOIN pip_demande_infos_supp ON inst_institutions_actions.ACTION_ID=pip_demande_infos_supp.ID_ACTION where inst_institutions_actions.PROGRAMME_ID='.$PROGRAMME_ID.'  ORDER BY ACTION_ID ASC';
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
		$ligne_budgetaires= '<option selected="" disabled="">'.lang("messages_lang.label_selecte").'</option>';
		if ($ACTION_ID!= '')
		{
			$ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1  ";
		}else{
			$ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1    ";  	
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
		echo json_encode(array('rapp'=>$rapp,'rapp2'=>$rapp2,'rapp3'=>$rapp3,'rapp4'=>$rapp4,'rapp5'=>$rapp5,'rapp6'=>$rapp6,'rapp_etude'=>$rapp_etude,'inst'=>$inst,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires));
	}
###detail du rapport des projets vs montant par axe stratégique 
	function detail_pip_piliers() 
	{
		ini_set('max_execution_time', 2000);
		ini_set('memory_limit','2048M');
		$KEY=$this->request->getPost('key');
		$KEY2=$this->request->getPost('key2');
		$KEY2=$this->request->getPost('key2');
		$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$ACTION_ID=$this->request->getPost('ACTION_ID');
		$cond='';
		$cond11='';
		$cond='';
		if(! empty($TYPE_INSTITUTION_ID))
		{
			$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
		}
		if(! empty($INSTITUTION_ID))
		{
			$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
		}
		if(! empty($PROGRAMME_ID))
		{
			$cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
		}
		if(! empty($ACTION_ID))
		{
			$cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
		}
		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

		$query_principal="SELECT pilier.DESCR_PILIER,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pilier.ID_PILIER,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN pilier ON pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";

		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}
		$order_by='';
		if($_POST['order']['0']['column']!=0)
		{
			$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
		}

		$search = !empty($_POST['search']['value']) ? ("AND (
			DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_PILIER LIKE '%$var_search%' OR DESCR_PILIER LIKE '%$var_search%')") : '';

		$critere = ($KEY<>null)? ' AND  pip_demande_infos_supp.ID_PILIER='.$KEY.'' :' AND (pip_demande_infos_supp.ID_PILIER IS NULL OR pip_demande_infos_supp.ID_PILIER=0)';

		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
		$query_filter=$query_principal.' '.$critere.'  '.$search;
		$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$u=0;
		$data = array();
		foreach ($fetch_data as $row)
		{
			$u++;
			$racrochage=array();
			$racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font>';
			if (strlen($row->DESCR_PILIER) < 10){
				$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCR_PILIER.'</label></font>';
			}else{
				$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCR_PILIER, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a></label></font>';
			}

			if (strlen($row->NOM_PROJET) < 10){
				$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
			}else{
				$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
			}
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font>';

			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font> ';

			if (strlen($row->DESCRIPTION_INSTITUTION) < 10){
				$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
			}else{
				$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
			}

			if (strlen($row->INTITULE_PROGRAMME) < 10){
				$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
			}else{
				$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
			}
			if (strlen($row->LIBELLE_ACTION) < 10){
				$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
			}else{
				$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font>';
			}
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
			$data[] = $racrochage;
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
  #########  
function detail_pip_axes() 
      {
      ini_set('max_execution_time', 2000);
      ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	$cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	}
	if(! empty($PROGRAMME_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
	}
	if(! empty($ACTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	}
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $query_principal="SELECT axe_intervention_pnd.DESCR_AXE_INTERVATION_PND,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_AXE_INTERVATION_PND LIKE '%$var_search%' OR DESCR_AXE_INTERVATION_PND LIKE '%$var_search%')") : '';
     $critere = ($KEY<>null)? " AND  pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=".$KEY."" :" AND (pip_demande_infos_supp.ID_AXE_INTERVENTION_PND IS NULL OR pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=0)" ;
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
	$u++;
	$racrochage=array();
    $racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font> ';
     if (strlen($row->DESCR_AXE_INTERVATION_PND) < 13){
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCR_AXE_INTERVATION_PND.'</label></font>';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a></label></font>';
		    }
		  if (strlen($row->NOM_PROJET) < 10){
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		    }
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font> ';
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font>';
		    if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> ';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		     }
    if (strlen($row->INTITULE_PROGRAMME) < 13){
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
	      }else{
      $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		   }
		if (strlen($row->LIBELLE_ACTION) < 13){
	$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font> ';
		   }
	  $racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';
	 $racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
	 $racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
	 $data[] = $racrochage;
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
    ###########bailleurs
function detail_pip_bailleurs() 
      {
      ini_set('max_execution_time', 2000);
      ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	  $cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
	 $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	    {
	 $cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	   }
   if(! empty($PROGRAMME_ID))
	      {
	 $cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
     }
	if(! empty($ACTION_ID))
	  {
	 $cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	  }
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $query_principal="SELECT pip_source_financement_bailleur.NOM_SOURCE_FINANCE,inst_institutions.DESCRIPTION_INSTITUTION,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND LEFT JOIN pip_demande_source_financement ON pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP LEFT JOIN pip_source_financement_bailleur ON pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR=pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR NOM_SOURCE_FINANCE LIKE '%$var_search%' OR NOM_SOURCE_FINANCE LIKE '%$var_search%')") : '';
    $critere = ($KEY<>null)? ' AND  pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR='.$KEY.'' :' AND (pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR IS NULL OR pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR=0)';
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
		$racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font> ';
		if (strlen($row->NOM_SOURCE_FINANCE) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_SOURCE_FINANCE.'</label></font> ';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_SOURCE_FINANCE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_SOURCE_FINANCE.'"><i class="fa fa-eye"></i></a></label></font> ';
		}
		if (strlen($row->NOM_PROJET) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font>';

		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font>';

		if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->INTITULE_PROGRAMME) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		if (strlen($row->LIBELLE_ACTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
		}else{
		$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font> ';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
		$data[] = $racrochage;
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




function detail_pip_statut_projets() 
      {
      	ini_set('max_execution_time', 2000);
        ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	  $cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
	 $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	    {
	 $cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	   }
   if(! empty($PROGRAMME_ID))
	      {
	 $cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
     }
	if(! empty($ACTION_ID))
	  {
	 $cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	  }
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
	     
    $query_principal="SELECT pip_statut_projet.DESCR_STATUT_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,user_users.NOM,user_users.PRENOM,proc_demandes.DATE_INSERTION,user_users.TELEPHONE1,user_users.TELEPHONE2,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN pip_statut_projet ON pip_statut_projet.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";

	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_STATUT_PROJET LIKE '%$var_search%' OR DESCR_STATUT_PROJET LIKE '%$var_search%')") : '';

	$critere = ($KEY<>null)? ' AND  pip_demande_infos_supp.ID_STATUT_PROJET='.$KEY.'' :' AND (pip_demande_infos_supp.ID_STATUT_PROJET IS NULL OR pip_demande_infos_supp.ID_STATUT_PROJET=0)';

	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
    $racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font> ';
     if (strlen($row->DESCR_STATUT_PROJET) < 10){
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCR_STATUT_PROJET.'</label></font>';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCR_STATUT_PROJET, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_STATUT_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		    }

		  if (strlen($row->NOM_PROJET) < 10){
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font> ';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		    }
		 $racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font>';

		 $racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font>';

		    if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
	       }else{
       $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		     }
	
		 if (strlen($row->INTITULE_PROGRAMME) < 13){
		  $racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
	      }else{
      $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		   }
		if (strlen($row->LIBELLE_ACTION) < 13){
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
	       }else{
    $racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font>';
		    }

	  $racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';

	   $racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
	   $racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
		$data[] = $racrochage;
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



function detail_pip_secteurs() 
      {
      ini_set('max_execution_time', 2000);
     ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	$cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
	 $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	    {
	 $cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	   }
   if(! empty($PROGRAMME_ID))
	      {
	 $cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
     }
	if(! empty($ACTION_ID))
	  {
	 $cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	  }
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="SELECT pip_secteur_intervention.DESCR_SECTEUR,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions_actions.LIBELLE_ACTION,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN pip_secteur_intervention ON pip_secteur_intervention.ID_SECTEUR_INTERVENTION=pip_demande_infos_supp.ID_SECTEUR_INTERVENTION LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";

	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
	DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_SECTEUR LIKE '%$var_search%' OR DESCR_SECTEUR LIKE '%$var_search%')") : '';

    $critere = ($KEY<>null)? ' AND  pip_demande_infos_supp.ID_OBJECT_STRATEGIC_PND='.$KEY.'' :' AND (pip_demande_infos_supp.ID_OBJECT_STRATEGIC_PND IS NULL OR pip_demande_infos_supp.ID_OBJECT_STRATEGIC_PND=0)';

	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
		$racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font>';

		if (strlen($row->NOM_PROJET) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0,9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font>';

		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font>';

		if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->INTITULE_PROGRAMME) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		if (strlen($row->LIBELLE_ACTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';

		$racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
		$data[] = $racrochage;
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




function detail_pip_lieu_interventions() 
{

	ini_set('max_execution_time', 2000);
	ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	$cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	}
	if(! empty($PROGRAMME_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
	}
	if(! empty($ACTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	}
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
	$query_principal="SELECT provinces.PROVINCE_NAME,communes.COMMUNE_NAME,pip_secteur_intervention.DESCR_SECTEUR,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN pip_secteur_intervention ON pip_secteur_intervention.ID_SECTEUR_INTERVENTION=pip_demande_infos_supp.ID_SECTEUR_INTERVENTION LEFT JOIN pip_lieu_intervention_projet ON pip_lieu_intervention_projet.ID_DEMANDE_INFO_SUPP=pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP LEFT JOIN provinces ON pip_lieu_intervention_projet.ID_PROVINCE=provinces.PROVINCE_ID LEFT JOIN communes ON communes.COMMUNE_ID=pip_lieu_intervention_projet.ID_COMMUNE LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";

	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
		$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
		DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_SECTEUR LIKE '%$var_search%' OR DESCR_SECTEUR LIKE '%$var_search%')") : '';

	$critere = ($KEY<>null)? ' AND  pip_lieu_intervention_projet.ID_PROVINCE='.$KEY.'' :' AND (pip_lieu_intervention_projet.ID_PROVINCE IS NULL OR pip_lieu_intervention_projet.ID_PROVINCE=0)';


	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
		$racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font>';

		if (strlen($row->PROVINCE_NAME) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->PROVINCE_NAME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->PROVINCE_NAME, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->PROVINCE_NAME.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->COMMUNE_NAME) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->COMMUNE_NAME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->COMMUNE_NAME, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->COMMUNE_NAME.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->NOM_PROJET) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font> ';

		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font>';

		if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> ';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->INTITULE_PROGRAMME) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		if (strlen($row->LIBELLE_ACTION) < 13){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
		$data[] = $racrochage;
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
    ###detail du rapport des projets vs montant par axe stratégique 
function detail_pip_etudes() 
{
	ini_set('max_execution_time', 2000);
	ini_set('memory_limit','2048M');
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$cond11='';
	$cond='';
	if(! empty($TYPE_INSTITUTION_ID))
	{
		$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}
	if(! empty($INSTITUTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
	}
	if(! empty($PROGRAMME_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_PROGRAMME =".$PROGRAMME_ID;
	}
	if(! empty($ACTION_ID))
	{
		$cond.=" AND pip_demande_infos_supp.ID_ACTION=".$ACTION_ID;
	}
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

	$query_principal="SELECT pilier.DESCR_PILIER,user_users.NOM,user_users.PRENOM,user_users.TELEPHONE1,user_users.TELEPHONE2,proc_demandes.DATE_INSERTION,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,pilier.ID_PILIER,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.DATE_FIN_PROJET FROM `pip_demande_infos_supp` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME LEFT JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION=inst_institutions_actions.ACTION_ID LEFT JOIN pilier ON pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER LEFT JOIN proc_demandes ON proc_demandes.ID_DEMANDE=pip_demande_infos_supp.ID_DEMANDE LEFT JOIN user_users ON proc_demandes.USER_ID=user_users.USER_ID  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."";
	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0)
	{
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}
	$search = !empty($_POST['search']['value']) ? ("AND (
		DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR DESCR_PILIER LIKE '%$var_search%' OR DESCR_PILIER LIKE '%$var_search%')") : '';

	if($KEY2==1){
		$critere=" AND pip_demande_infos_supp.A_UNE_ETUDE=1";
	}elseif($KEY2==2) {
		$critere=" AND pip_demande_infos_supp.A_UNE_ETUDE=0";
	}
	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
	$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
		$racrochage[] ='<font color="#000000" size=2><label>'.$u.'</label></font>';
		if (strlen($row->DESCR_PILIER) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCR_PILIER.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCR_PILIER, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		if (strlen($row->NOM_PROJET) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->NOM_PROJET, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_DEBUT_PROJET.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_FIN_PROJET.'</label></font> ';
		if (strlen($row->DESCRIPTION_INSTITUTION) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}

		if (strlen($row->INTITULE_PROGRAMME) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		if (strlen($row->LIBELLE_ACTION) < 10){
			$racrochage[] ='<font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font>';
		}else{
			$racrochage[] ='<font color="#000000" size=2><label>'.mb_substr($row->LIBELLE_ACTION, 0, 9).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a></label></font>';
		}
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->NOM.' '.$row->PRENOM.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->TELEPHONE1.'</label></font>';
		$racrochage[] ='<font color="#000000" size=2><label>'.$row->DATE_INSERTION.'</label></font>';
		$data[] = $racrochage;
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

}
?>
 