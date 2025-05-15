<?php
/**
 * @author NIYONGABO Emery
 *emery@mediabox.bi
 * Tableau de bord «dashbord des performances des racrochages»
 le 12/09/2023
 */
  //Appel de l'esp\ce de nom du Controllers
 namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
 ###declaration d'une classe controlleur
 class Dashboard_Performance_Decrochage extends BaseController
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
		if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$requete_type="SELECT `TYPE_OPERATION_ID`,`DESCRIPTION_OPERATION` FROM `type_operation` WHERE 1";
 		$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
 		$data['TYPE_OPERATION_ID']=$this->request->getPost('');

		$date_select='';
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
    return view('App\Modules\dashboard\Views\Dashboard_Performance_Decrochage_View',$data);
         }

  ##fonction get_rapport qui permet d'afficher le rapport et appel des filtres qui dependent des autres
  public function get_rapport()
    {
   $data=$this->urichk();
  	$db = db_connect();
  	$TYPE_OPERATION_ID=$this->request->getVar('TYPE_OPERATION_ID');
    $SOUS_TUTEL_ID=$this->request->getVar('SOUS_TUTEL_ID');
  	$PROGRAMME_ID=$this->request->getVar('PROGRAMME_ID');
  	$ACTION_ID=$this->request->getVar('ACTION_ID');
    $totaux_cond='';
  	$IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
  	if($IS_PRIVATE==1)
  	{
  	$totaux_cond=' AND ptba.T1>0';
  	}else
  	{
  	$totaux_cond=' AND ptba.T2>0';
  	}
  	$cond1='';
  	$cond='';
  	$cond2='';
  	$KEY2=1;
  	$cond_program='';
  
  	if(!empty($TYPE_OPERATION_ID))
  	  {
  	 $cond.=" AND historique_transfert.TYPE_OPERATION_ID=".$TYPE_OPERATION_ID."";
  	 }
  	$budget=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(PTBA_ID) FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID) nbr_activite_a_faire FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=2 ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");
  	$activites_exec=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(DISTINCT execution_budgetaire_raccrochage_activite_new.PTBA_ID) FROM execution_budgetaire_raccrochage_activite_new WHERE execution_budgetaire_raccrochage_activite_new.INSTITUTION_ID=inst_institutions.INSTITUTION_ID AND PTBA_ID  IN (SELECT PTBA_ID FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID ".$totaux_cond.")) AS nbr_execute FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=2  ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");
  	$budget_restant_min=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(PTBA_ID) FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID ) nbr_activite_a_faire,(SELECT COUNT(DISTINCT execution_budgetaire_raccrochage_activite_new.PTBA_ID) FROM execution_budgetaire_raccrochage_activite_new WHERE execution_budgetaire_raccrochage_activite_new.INSTITUTION_ID=inst_institutions.INSTITUTION_ID AND PTBA_ID  IN (SELECT PTBA_ID FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID )) AS nbr_execute FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=2   ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");
  	$budget_restant_min1=$this->ModelPs->getRequete(' CALL getTable("'.$budget_restant_min.'")');
  	$budget_req=$this->ModelPs->getRequete(' CALL getTable("'.$budget.'")');
  	$activite_req=$this->ModelPs->getRequete(' CALL getTable("'.$activites_exec.'")');
  	$data_budget_req='';
  	$data_total=0;
  	$data_total_resta_min=0;
  	$data_total_activite=0;
  	$data_activite_req='';
  	$categorie="";
  	$data_min_rest="";
  	foreach ($budget_restant_min1 as $value)
  	    {
  	    $categorie.="'";
  	    $execute = ($value->nbr_execute > 0) ? $value->nbr_execute:0;
  	    $vote = ($value->nbr_activite_a_faire > 0) ? $value->nbr_activite_a_faire:0;
  	    $restant=$vote-$execute;
  	    $restant1 = ($restant > 0) ? $restant:0;
  	     $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	      $rappel=$this->str_replacecatego($name);
  	      $categorie.= $rappel."',";
  	 $data_min_rest.="{y:".$restant1.",color:'#FF7F50',key:".$value->INSTITUTION_ID.",key2:3,key4:".$IS_PRIVATE.",},";
  		$data_total_resta_min=$data_total_resta_min+$restant1;
    	}
  	   
  	   foreach ($activite_req as $value)
  	    {
  	   $categorie.="'";
  	   $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	     $rappel=$this->str_replacecatego($name);
  	   $categorie.= $rappel."',";
  	 $data_activite_req.="{y:".$value->nbr_execute.",color:'green',key:'".$value->INSTITUTION_ID."',key2:2,key4:".$IS_PRIVATE."},";
  		$data_total_activite=$data_total_activite+$value->nbr_execute;
    	}

    foreach ($budget_req as $value)
  	    {
  	  $categorie.="'";
  	  $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	    $rappel=$this->str_replacecatego($name);
  	  $categorie.= $rappel."',";
  	  $data_budget_req.="{y:".$value->nbr_activite_a_faire.",color:'#000080',key:".$value->INSTITUTION_ID.",key2:1,key4:".$IS_PRIVATE."},";
  	  $data_total=$data_total+$value->nbr_activite_a_faire;
    	}
  	  
	//print_r($data_budget_req);die();
	$rapp="<script type=\"text/javascript\">
	Highcharts.chart('container', {
	chart: {
	type: 'column'
	},
	title: {
	text: '".lang('messages_lang.perform_minister')."',
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
        headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
        pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
            '<td style=\"padding:0\"><b>{point.y:.f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
	plotOptions: {
	column: {
	depth: 40,
	cursor:'pointer',
	point:{
	events: {
	click: function(){
	  
	if(this.key2==2){
	$(\"#idpro\").html(\" ".lang('messages_lang.Actions')." \");
	$(\"#idcod\").html(\" Liste&nbspdes&nbspactivités \");
	$(\"#idobj\").html(\"".lang('messages_lang.label_droit_program')."\");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités déjà raccrochées\");   
	}else if(this.key2==3){
	$(\"#idpro\").html(\" ".lang('messages_lang.labelle_activites')."\");
	$(\"#idcod\").html(\" ".lang('messages_lang.Actions')."\");
	$(\"#idobj\").html(\" ".lang('messages_lang.label_droit_program')." \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités restantes\");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" ".lang('messages_lang.labelle_activites')."\");  
	$(\"#idcod\").html(\" ".lang('messages_lang.Actions')."\");
	$(\"#idobj\").html(\" ".lang('messages_lang.label_droit_program')." \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités à raccrocher\");
	}else if(this.key2==6){
	$(\"#idpro\").html(\" ".lang('messages_lang.labelle_activites')."\");
	$(\"#idcod\").html(\" ".lang('messages_lang.Actions')."\");
	$(\"#idobj\").html(\" ".lang('messages_lang.label_droit_program')." \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" ".lang('messages_lang.labelle_activites')."\");
	$(\"#idcod\").html(\" ".lang('messages_lang.Actions')."\");
	$(\"#idobj\").html(\" ".lang('messages_lang.label_droit_program')." \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}else{
	$(\"#idpro\").html(\" ".lang('messages_lang.label_droit_program')."  \");
	$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
	$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
    $(\"#Budget\").html(\" Budget&nbsptotal\");
     $(\"#myModal\").modal('show');
	var row_count ='1000000';
	$(\"#mytable\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"ajax\":{
	url:\"".base_url('dashboard/Dashboard_Performance_Decrochage/detail_racrochage_ministere')."\",
	type:\"POST\",
	data:{
	key:this.key,
	key2:this.key2,
	key3:this.key3,
	key4:this.key4,
	TYPE_OPERATION_ID:$('#TYPE_OPERATION_ID').val(),
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
	'excel', 'print','pdf'
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
	format: '{point.y:,3f}'
	},
	showInLegend: true
	}
	}, 
	credits: {
	enabled: true,
	href: \"\",
	text: \"Mediabox\"
	},

    series:[
         {
        name: '".lang('messages_lang.activite_vote')." (".number_format($data_total,0,',',' ').")',
         color:'#000080',
        data: [".$data_budget_req."],
        },
         {
        name: '".lang('messages_lang.activite_dejaraccr')." (".number_format($data_total_activite,0,',',' ').")',
        color:'green',
        data: [".$data_activite_req."],
       },
         {
        name: '".lang('messages_lang.activite_restant')." (".number_format($data_total_resta_min,0,',',' ').")',
        color:'#FF7F50',
        data: [".$data_min_rest."],
       }
    ]

	});
	</script>
	";



	$budget_instution=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(PTBA_ID) FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID) nbr_activite_a_faire FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=1 ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");
  	$activites_institution=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(DISTINCT execution_budgetaire_raccrochage_activite_new.PTBA_ID) FROM execution_budgetaire_raccrochage_activite_new WHERE execution_budgetaire_raccrochage_activite_new.INSTITUTION_ID=inst_institutions.INSTITUTION_ID AND PTBA_ID  IN (SELECT PTBA_ID FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID)) AS nbr_execute FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=1 ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");

  	$budget_instution1=$this->ModelPs->getRequete(' CALL getTable("'.$budget_instution.'")');
  	$activites_institution1=$this->ModelPs->getRequete(' CALL getTable("'.$activites_institution.'")');
  	$data_budget_institution='';
  	$data_total_institution=0;
  	$data_total_activite_institution=0;
  	$data_activite_nstitution='';
  	$categorie_institution="";
  	foreach ($budget_instution1 as $value)
  	    {
  	     $categorie_institution.="'";
  	     $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	      $rappel1=$this->str_replacecatego($name);
  	      $categorie_institution.= $rappel1."',";
  	 $data_budget_institution.="{y:".$value->nbr_activite_a_faire.",color:'#000080',key:'".$value->INSTITUTION_ID."',key2:1,key4:".$IS_PRIVATE."},";
  		$data_total_institution=$data_total_institution+$value->nbr_activite_a_faire;
  		
    	}
  	   
  	   foreach ($activites_institution1 as $value)
  	    {
  	     $categorie_institution.="'";
  	     $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	      $rappel1=$this->str_replacecatego($name);
  	      $categorie_institution.= $rappel1."',";
  	 $data_activite_nstitution.="{y:".$value->nbr_execute.",color:'green',key:'".$value->INSTITUTION_ID."',key2:2,key4:".$IS_PRIVATE."},";
  		$data_total_activite_institution=$data_total_activite_institution+$value->nbr_execute;
    	}
    $budget_restant_institution=("SELECT INSTITUTION_ID,CODE_INSTITUTION,ABREVIATION,(SELECT COUNT(PTBA_ID) FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID ) nbr_activite_a_faire,(SELECT COUNT(DISTINCT execution_budgetaire_raccrochage_activite_new.PTBA_ID) FROM execution_budgetaire_raccrochage_activite_new WHERE execution_budgetaire_raccrochage_activite_new.INSTITUTION_ID=inst_institutions.INSTITUTION_ID AND PTBA_ID  IN (SELECT PTBA_ID FROM ptba WHERE ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID )) AS nbr_execute FROM inst_institutions WHERE inst_institutions.TYPE_INSTITUTION_ID=1   ORDER BY inst_institutions.CODE_INSTITUTION ASC LIMIT 27");

     $budget_restant_institution=$this->ModelPs->getRequete(' CALL getTable("'.$budget_restant_institution.'")');
     $data_institutio_rest="";
     $data_total_resta_institutio=0;
  	foreach ($budget_restant_institution as $value)
  	    {
  	  $categorie_institution.="'";
  	  $execute = ($value->nbr_execute > 0) ? $value->nbr_execute:0;
  	  $vote = ($value->nbr_activite_a_faire > 0) ? $value->nbr_activite_a_faire:0;
  	  $restant=$vote-$execute;
  	  $restant1 = ($restant > 0) ? $restant:0;
  	  $name = (!empty($value->ABREVIATION)) ? $value->ABREVIATION : "Autres";
	    $rappel1=$this->str_replacecatego($name);
  	     $categorie_institution.= $rappel1."',";
  	 $data_institutio_rest.="{y:".$restant1.",color:'#FF7F50',key:'".$value->INSTITUTION_ID."',key2:3,key4:".$IS_PRIVATE."},";
  	$data_total_resta_institutio=$data_total_resta_institutio+$restant1;
  		
    	}
  	
	//print_r($data_budget_req);die();
	$rapp1="<script type=\"text/javascript\">
	Highcharts.chart('container1', {
	chart: {
	type: 'column'
	},
	title: {
	text: '".lang('messages_lang.perform_institutio')."',
	    },  
	subtitle: {
	text: ''
	  },
	xAxis: {
	 categories: [".$categorie_institution."],
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
            '<td style=\"padding:0\"><b>{point.y:.f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
	plotOptions: {
	column: {
	depth: 20,
	cursor:'pointer',
	point:{
	events: {
	click: function(){

	  
	if(this.key2==2){

	$(\"#idpro\").html(\" Actions \");
	$(\"#idcod\").html(\" Liste&nbspdes&nbspactivités \");
	$(\"#idobj\").html(\"Programme\");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités déjà raccrochées\");
	}else if(this.key2==3){
	$(\"#idpro\").html(\" activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités restantes\");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités à raccrocher\");
	}else if(this.key2==6){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}else if(this.key2==1){
	$(\"#idpro\").html(\" Activités\");
	$(\"#idcod\").html(\" Actions\");
	$(\"#idobj\").html(\" Programme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}else{
	$(\"#idpro\").html(\" Programmes  \");
	$(\"#idcod\").html(\"Objctif&nbspdu&nbspprogramme \");
	$(\"#idobj\").html(\" Code&nbspdu&nbspprogramme \");
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
	}
	$(\"#titre\").html(\"Liste&nbspdes&nbspactivités\");
    $(\"#Budget\").html(\" Budget&nbsptotal\");
	$(\"#myModal\").modal('show');
	var row_count ='1000000';
	$(\"#mytable\").DataTable({
	\"processing\":true,
	\"serverSide\":true,
	\"bDestroy\": true,
	\"ajax\":{
	url:\"".base_url('dashboard/Dashboard_Performance_Decrochage/detail_racrochage_ministere')."\",
	type:\"POST\",
	data:{
	key:this.key,
	key2:this.key2,
	key4:this.key4,
	TYPE_OPERATION_ID:$('#TYPE_OPERATION_ID').val(),
	IS_PRIVATE:$('#IS_PRIVATE').val(),
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
	'excel', 'print','pdf'
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
	showInLegend: true
	}
	}, 
	credits: {
	enabled: true,
	href: \"\",
	text: \"Mediabox\"
	},

    series:[
          {
        name: '".lang('messages_lang.activite_raccr')." (".number_format($data_total_institution,0,',',' ').")',
         color:'#000080',
        data: [".$data_budget_institution."],
        },{
        name: '".lang('messages_lang.activite_dejaraccr')." (".number_format($data_total_activite_institution,0,',',' ').")',
        color:'green',
        data: [".$data_activite_nstitution."],
       },
       {
        name: '".lang('messages_lang.activite_restant')." (".number_format($data_total_resta_institutio,0,',',' ').")',
        color:'#FF7F50',
        data: [".$data_institutio_rest."],
       }
    ]

	});
	</script>
	";

	echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1));
 }
 
###detail du rapport des projets vs montant par axe stratégique 
function detail_racrochage_ministeres() 
{
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
	$KEY2=$this->request->getPost('key2');
	$KEY4=$this->request->getPost('key4');
	// $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');

	// $SOUS_TUTEL_ID=$this->request->getPost('INSTITUTION_ID');
	// $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	// $ACTION_ID=$this->request->getPost('ACTION_ID');
	// $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');

  // print_r($SOUS_TUTEL_ID);die();
	$totaux_cond='';
  $totaux='';
	if($KEY4==1)
	{
	$totaux_cond=' AND ptba.T1>0';
	$totaux='T1';
	}else
	{
	$totaux_cond=' AND ptba.T2>0';
	$totaux='T2';
	}
	$cond='';
	$cond11='';
	// if(! empty($TYPE_INSTITUTION_ID))
	// {
	// $KEY2=1;
	// $cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	// }
	// if(! empty($INSTITUTION_ID))
	// {
	// $KEY2=2;
	// $cond.=" AND ptba.CODE_MINISTERE='".$INSTITUTION_ID."'";
	// }
	// if(! empty($INSTITUTION_ID))
	// {
	// 	$KEY2=5;
	// 	$cond.=" AND ptba.CODE_MINISTERE='".$INSTITUTION_ID."'";
	// 	$cond11.=" AND inst_institutions.CODE_INSTITUTION='".$INSTITUTION_ID."'";
	// }
	// if(! empty($PROGRAMME_ID))
	// {
	// if ($TYPE_INSTITUTION_ID==2)
	// {
	// $cond.=" AND ptba.CODE_PROGRAMME='".$PROGRAMME_ID."'";
	// 	$KEY2=3;
	// 	}else{
	// 	$cond.=" AND ptba.CODE_PROGRAMME='".$PROGRAMME_ID."'";
	// 	$KEY2=4;
	// 	}
	// }
	// if(! empty($ACTION_ID))
	// {
	// 	$KEY2=4;
	// 	$cond.=" AND ptba.CODE_ACTION='".$ACTION_ID."'";  
	// }
	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

	if ($KEY2==1) 
	{
		$query_principal="SELECT ".$totaux." AS T1,SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) service, inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,`ACTIVITES`,`RESULTATS_ATTENDUS`,REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM `ptba` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID WHERE 1 ".$totaux_cond."";	


	}else if($KEY2==2){
    $query_principal="SELECT ".$totaux." AS T1,SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) service,inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE, inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,`ACTIVITES`,`RESULTATS_ATTENDUS`,REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM `ptba` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID WHERE 1 ".$totaux_cond." AND ptba.PTBA_ID IN (SELECT `PTBA_ID` FROM execution_budgetaire_raccrochage_activite_new WHERE 1)";

	}else{
    $query_principal="SELECT ptba.CODE_MINISTERE AS CODE, ".$totaux." AS T1,SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3) service, inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,`ACTIVITES`,`RESULTATS_ATTENDUS`,REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM `ptba` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_IDWHERE 1 ".$totaux_cond."  AND ptba.PTBA_ID NOT IN ((SELECT  execution_budgetaire_raccrochage_activite_new.PTBA_ID FROM execution_budgetaire_raccrochage_activite_new WHERE execution_budgetaire_raccrochage_activite_new.INSTITUTION_ID=inst_institutions.INSTITUTION_ID AND PTBA_ID  IN (SELECT PTBA_ID FROM ptba WHERE ptba.INSTITUTION_ID=".$KEY." ".$totaux_cond.")))";	
	     }



	$limit='LIMIT 0,10';
	if ($_POST['length'] != -1)
  {
    $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
  }
  $order_by = '';

  $order_column=array(1,'inst_institutions.DESCRIPTION_INSTITUTION',1,'inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','RESULTATS_ATTENDUS','ACTIVITES',1);
  // inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION

  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_MINISTERE ASC';

	$search = !empty($_POST['search']['value']) ? ("AND ( inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' )") : '';

	$critere=" AND inst_institutions.INSTITUTION_ID=".$KEY."";

	$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
	$query_filter=$query_principal.' '.$critere.'  '.$search;
   // print_r($conditions);die();
	$query_secondaire = 'CALL `getTable`("' .$conditions. '");';
	$fetch_data = $this->ModelPs->datatable($query_secondaire);
	$u=0;
	$data = array();
	foreach ($fetch_data as $row)
	{
		$u++;
		$racrochage=array();
		$retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;

		$sous = (! empty($row->service)) ? $row->service : 0 ;
		$Services=(" SELECT DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE CODE_SOUS_TUTEL=".$sous."");
		$Services_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$Services.'")');
		$retVal1 = (! empty($Services_req['DESCRIPTION_SOUS_TUTEL'])) ? $Services_req['DESCRIPTION_SOUS_TUTEL'] : 'N/A' ;
    $racrochage[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
		 if (strlen($row->INTITULE_MINISTERE) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
	   }else{
    $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($row->INTITULE_MINISTERE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		}
	 if (strlen($retVal1) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$retVal1.'</label></font> </center>';
	      }else{
    $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($retVal1, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal1.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		}
		 if (strlen($row->INTITULE_PROGRAMME) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
	   }else{
   $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
		}
		
		if (strlen($retVal) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
	   }else{
    $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($retVal, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a></label></font> </center>';

		}
		
         if (strlen($row->RESULTATS_ATTENDUS) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
	   }else{
    $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($row->RESULTATS_ATTENDUS, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTATS_ATTENDUS.'"><i class="fa fa-eye"></i></a></label></font> </center>';

		}

		 if (strlen($row->ACTIVITES) < 13){
		$racrochage[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
	   }else{
    $racrochage[] ='<center><font color="#000000" size=2><label>'.substr($row->ACTIVITES, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->ACTIVITES.'"><i class="fa fa-eye"></i></a></label></font> </center>';

		}
		$racrochage[] ='<center><font color="#000000" size=2><label>'.number_format($row->T1,0,',',' ').'</label></font> </center>';
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

function detail_comparaisons_execution()
    {
      $KEY=$this->request->getPost('key');
      $KEY2=$this->request->getPost('key2');
     $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';
	$IS_PRIVATE=$this->request->getPost('IS_PRIVATE');

	
	if(! empty($TYPE_INSTITUTION_ID))
	{
   $KEY2=1;
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
      $KEY2=2;

	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";

	}

   
   	if(! empty($PROGRAMME_ID))
	{
    if ($TYPE_INSTITUTION_ID==2) {

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	$KEY2=3;
	}else{
	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $KEY2=3;
   }
   }
	if(! empty($ACTION_ID))
	{
    $KEY2=4;
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}
     $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
         if ($KEY2==2) 
          {
       $query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.ABREVIATION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.INSTITUTION_ID=ptba.INSTITUTION_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID  WHERE 1 ".$cond." ";
         }else if ($KEY2==3) {
        $query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.ABREVIATION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.INSTITUTION_ID=ptba.INSTITUTION_ID LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ";
        }else if ($KEY2==4) {
        $query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions.ABREVIATION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,ptba_activite.DESCRIPTION_ACTIVITE,inst_institutions_actions.LIBELLE_ACTION,proc_exec_budgetaire_phase_administrative_detail.PROGRAMME_ID,MONTANT_ENGAGE FROM `proc_exec_budgetaire_phase_administrative_detail` LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=proc_exec_budgetaire_phase_administrative_detail.PROGRAMME_ID LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_exec_budgetaire_phase_administrative_detail.INSTITUTION_ID LEFT JOIN inst_institutions_actions ON `inst_institutions_actions`.`ACTION_ID`=proc_exec_budgetaire_phase_administrative_detail.ACTION_ID LEFT JOIN ptba_activite ON `ptba_activite`.`ACTIVITE_ID`=proc_exec_budgetaire_phase_administrative_detail.ACTIVITE_ID  WHERE 1 ".$cond." ";
        }else if ($KEY2==1) {
       $query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.ABREVIATION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.CODE_PROGRAMME=ptba.CODE_PROGRAMME LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ";
            }else{
        $query_principal="SELECT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.OBJECTIF_DU_PROGRAMME,inst_institutions.ABREVIATION,if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') AS type,inst_institutions_programmes.PROGRAMME_ID,proc_demande_exec_budgetaire_details.MONTANT_ENGAGE FROM `proc_demande_exec_budgetaire_details` LEFT JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.EXEC_BUDG_PHASE_ADMIN_ID=proc_demande_exec_budgetaire_details.EXEC_BUDG_PHASE_ADMIN_ID LEFT join ptba on proc_demande_exec_budgetaire.PTBA_ID=ptba.PTBA_ID LEFT JOIN  inst_institutions_programmes ON inst_institutions_programmes.CODE_PROGRAMME=ptba.CODE_PROGRAMME LEFT  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=proc_demande_exec_budgetaire_details.INSTITUTION_ID LEFT JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID=inst_institutions.TYPE_INSTITUTION_ID where 1 ".$cond." ";
         }
          
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
      

      if ($KEY2==1) {
   $add_search=" OR inst_institutions_programmes.CODE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_programmes.OBJECTIF_DU_PROGRAMME LIKE '%$var_search%'";
      }
      if ($KEY2==2){
     $add_search=" OR inst_institutions_actions.OBJECTIF_ACTION LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
       }
     if ($KEY2==3) { 
     $add_search=" OR ptba_activite.DESCRIPTION_ACTIVITE LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
         }if ($KEY2==4) {
     $add_search=" OR ptba_activite.DESCRIPTION_ACTIVITE LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%'";
       }
      

     $search = !empty($_POST['search']['value']) ? ("AND (
      inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions.ABREVIATION LIKE '%$var_search%' OR if(inst_institutions.TYPE_INSTITUTION_ID=1,'Institution','Ministere') LIKE '%$var_search%' ".$add_search." OR MONTANT_ENGAGE LIKE '%$var_search%')") : '';


     $critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
     if($KEY2==1)
        {
        $critere=" AND ptba.INSTITUTION_ID='".$KEY."'";
         }
       if ($KEY2==2)
          {
         $cond.=" AND ptba.PROGRAMME_ID='".$KEY."'";
         }
       if ($KEY2==3) 
       {
       $critere=" AND PTBA_ID='".$KEY."'";
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
           $engagement=array();
            if ($KEY2==0) {
        $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
        $engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
        $engagement[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->OBJECTIF_DU_PROGRAMME).'</label></font> </center>';

       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->CODE_PROGRAMME.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
        }
        if ($KEY2==1){
        $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
        $engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
        $engagement[] ='<center><font color="#000000" size=2><label>'.$this->str_replacecatego($row->OBJECTIF_DU_PROGRAMME).'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->CODE_PROGRAMME.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
        }else if($KEY2==2){
    	 $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->OBJECTIF_ACTION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
        }else if($KEY2==3){
    	 $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_ACTIVITE.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';

       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->type.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
       $engagement[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT_ENGAGE,0,',',' ').'</label></font> </center>';
        }
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


      ###detail du rapport des projets vs montant par axe stratégique 
  
  function detail_generals_phase()
       {
    $KEY=$this->request->getPost('key');
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

    $cond="";
    if(! empty($TYPE_INSTITUTION_ID))
    {
    	$KEY2=1;
    	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
    }

    if(! empty($INSTITUTION_ID))
    {
    	$KEY2=2;
    	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
    }
    if(! empty($PROGRAMME_ID))
    {
    	if ($TYPE_INSTITUTION_ID==2) {

    		$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    		$KEY2=3;
    	}else{
    		$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    		$KEY2=3;
    	}
    }
    if(! empty($ACTION_ID))
    {
    	$KEY2=4;
    	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="SELECT   detail.MONTANT_LIQUIDATION,detail.MONTANT_TITRE_DECAISSEMENT,detail.MONTANT_JURIDIQUE,detail.MONTANT_ORDONNANCE,detail.MONTANT_ENGAGE,inst_institutions_programmes.INTITULE_PROGRAMME,ptba.RESULTATS_ATTENDUS,ptba.LIBELLE_ACTION,ptba.ACTIVITES,`MONTANT_FACTURE`,`ABREVIATION`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution FROM proc_demande_exec_budgetaire as admini LEFT JOIN proc_demande_exec_budgetaire_details as detail ON detail.EXEC_BUDG_PHASE_ADMIN_ID=admini.DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID LEFT JOIN ptba ON ptba.PTBA_ID=admini.PTBA_ID LEFT JOIN  inst_institutions ON ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID LEFT JOIN  proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=admini.MOUVEMENT_DEPENSE_ID WHERE 1  ".$cond." ";

   
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
    `ABREVIATION` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ABREVIATION LIKE '%$var_search%')") : '';
    $search = str_replace("'","\'",$search);

     $critere=' ';
     if ($KEY==1) {
     $critere=' AND detail.MONTANT_ENGAGE > 0';
     }else if ($KEY==2) {
     $critere=' AND detail.MONTANT_LIQUIDATION > 0';
     }else if ($KEY==3) {
     $critere=' AND detail.MONTANT_ORDONNANCE > 0';
     }else if ($KEY==5) {
     $critere=' AND detail.MONTANT_JURIDIQUE > 0';
     }else{
      $critere=' AND detail.MONTANT_TITRE_DECAISSEMENT > 0';
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
    $ptba=array();
    $montant='';
     if ($KEY==1) {
     	$montant=number_format($row->MONTANT_ENGAGE,0,',',' ');
     }else if ($KEY==2) {
     	$montant=number_format($row->MONTANT_LIQUIDATION,0,',',' ');
     }else if ($KEY==3) {
     	$montant=number_format($row->MONTANT_ORDONNANCE,0,',',' ');
     }else if ($KEY==5) {
     $montant=number_format($row->MONTANT_JURIDIQUE,0,',',' ');
     }else{
     $montant=number_format($row->MONTANT_TITRE_DECAISSEMENT,0,',',' ');
     }
    $ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$montant.'</label></font> </center>';
    $data[] = $ptba;        
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

function detail_generals_vote() 
      {
    $data=$this->urichk();

    $db = db_connect(); 
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
    
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');

	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
	$cond='';

	$cond='';
	
	if(! empty($TYPE_INSTITUTION_ID))
	{
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}

   
   	if(! empty($PROGRAMME_ID))
	{
    

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	
   }

	if(! empty($ACTION_ID))
	{
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}
     
     if(! empty($LIGNE_BUDGETAIRE))
	{
	$cond.=" AND ptba.CODE_NOMENCLATURE_BUDGETAIRE='".$LIGNE_BUDGETAIRE."'";  
	}

	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 


   $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE, inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.UNITE,`ACTIVITES`,`RESULTATS_ATTENDUS`,REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM `ptba` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID  JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID WHERE 1 ".$cond." ";




	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0) {
	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}

	$search = !empty($_POST['search']['value']) ? ("AND (
	inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE LIKE '%$var_search%')") : '';
   $search = str_replace("'","\'",$search);

	$critere=" ";
	if ($KEY2==6) {
	$critere=" AND INSTITUTION_ID='".$KEY."'";
	
	} else if ($KEY2==5) {
	$critere=" AND SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";
	
	} else if ($KEY2==3) {
	$critere=" AND ACTION_ID='".$KEY."'";
	
	
	} else if ($KEY2==4) {
	 $critere=" AND PTBA_ID='".$KEY."'";
	
	} else if ($KEY2==2) {
	$critere=" AND PROGRAMME_ID='".$KEY."'";

	} else if ($KEY2==100 OR $KEY2==200 OR $KEY2==300 OR $KEY2==400 ) {
	$critere=" ";
	}else{
	$critere=" AND TYPE_INSTITUTION_ID='".$KEY."'";	
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
	$engagement=array();
    
    if ($KEY2==100) {
    $mona_de=number_format($row->T1,0,',',' ');
    }else if ($KEY2==200) {
    $mona_de=number_format($row->T2,0,',',' ');
    }else if ($KEY2==300) {
    $mona_de=number_format($row->T3,0,',',' ');
    }else if ($KEY2==400) {
    $mona_de=number_format($row->T4,0,',',' ');
    }else{
    $mona_de=number_format($row->name,0,',',' ');	
    }
    $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;

    $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.' '.$row->UNITE.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
	$engagement[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
	$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';

	$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';

	$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';

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


       #########


function detail_generals_activite_vote() 
      {
    $data=$this->urichk();

    $db = db_connect(); 
	$KEY=$this->request->getPost('key');
	$KEY2=$this->request->getPost('key2');
    
	$TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');

	$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

	$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
	$ACTION_ID=$this->request->getPost('ACTION_ID');
	$cond='';

	$cond='';
	
	if(! empty($TYPE_INSTITUTION_ID))
	{
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}

   
   	if(! empty($PROGRAMME_ID))
	{
    

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	
   }

	if(! empty($ACTION_ID))
	{
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}

	$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 


   $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.UNITE,`ACTIVITES`,`RESULTATS_ATTENDUS`,REPLACE(RTRIM(ptba.PROGRAMMATION_FINANCIERE_BIF),' ','') AS name FROM `ptba` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID  WHERE 1 ".$cond." ";

 


	$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0) {

	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}


	$search = !empty($_POST['search']['value']) ? ("AND (
	inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%' OR ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE LIKE '%$var_search%')") : '';



	$critere=" ";
	if ($KEY2==6) {
	$critere=" AND INSTITUTION_ID='".$KEY."'";
	
	} else if ($KEY2==5) {
	$critere=" AND SUBSTRING(ptba.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";
	
	} else if ($KEY2==3) {
	$critere=" AND ACTION_ID='".$KEY."'";
	
	
	} else if ($KEY2==4) {
	 $critere=" AND PTBA_ID='".$KEY."'";
	
	} else if ($KEY2==2) {
	$critere=" AND PROGRAMME_ID='".$KEY."'";

	} else if ($KEY2==100 OR $KEY2==200 OR $KEY2==300 OR $KEY2==400 ) {
	$critere=" ";
	}else{
	$critere=" AND TYPE_INSTITUTION_ID='".$KEY."'";	
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
	$engagement=array();
    if ($KEY2==100) {
    $mona_de=number_format($row->T1,0,',',' ');
    }else if ($KEY2==200) {
    $mona_de=number_format($row->T2,0,',',' ');
    }else if ($KEY2==300) {
    $mona_de=number_format($row->T3,0,',',' ');
    }else if ($KEY2==400) {
    $mona_de=number_format($row->T4,0,',',' ');
    }else{
    $mona_de=number_format($row->name,0,',',' ');	
    }
    $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;

    $engagement[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.' '.$row->UNITE.'</label></font> </center>';
    $engagement[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';
	$engagement[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
	$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';

	$engagement[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';

	$engagement[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';

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




 function detail_generals_decaissement()
       {
    $KEY=$this->request->getPost('key');
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

    $cond="";
    if(! empty($TYPE_INSTITUTION_ID))
	{
   $KEY2=1;
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
      $KEY2=2;
	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}
   	if(! empty($PROGRAMME_ID))
	{
    if ($TYPE_INSTITUTION_ID==2) {

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	$KEY2=3;
	}else{
	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $KEY2=3;
   }
   }
	if(! empty($ACTION_ID))
	{
    $KEY2=4;
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

    $query_principal="SELECT  detail.MONTANT_LIQUIDATION,detail.MONTANT_TITRE_DECAISSEMENT,detail.MONTANT_JURIDIQUE,detail.MONTANT_ORDONNANCE,detail.MONTANT_ENGAGE,inst_institutions_programmes.INTITULE_PROGRAMME,ptba.RESULTATS_ATTENDUS,inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES,`MONTANT_FACTURE`,`ABREVIATION`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution FROM proc_demande_exec_budgetaire as admini LEFT JOIN proc_demande_exec_budgetaire_details as detail ON detail.EXEC_BUDG_PHASE_ADMIN_ID=admini.DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID LEFT JOIN ptba ON ptba.PTBA_ID=admini.PTBA_ID LEFT JOIN  inst_institutions ON ptba.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID  LEFT JOIN  proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=admini.MOUVEMENT_DEPENSE_ID WHERE 1  ".$cond." ";


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
    `ABREVIATION` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ABREVIATION LIKE '%$var_search%')") : '';

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
    $ptba=array();

    $ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';

  $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ABREVIATION.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$MONTANT_TITRE_DECAISSEMENT.'</label></font> </center>';

    $data[] = $ptba;        
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
   



	function detail_ptba_Gdemasses(){

	    $KEY=$this->request->getPost('key');
		  $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
		  $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		  $LIGNE_BUDGETAIRE=$this->request->getPost('LIGNE_BUDGETAIRE');
      $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
      $ACTION_ID=$this->request->getPost('ACTION_ID');

	    $cond='';

	    if(!empty($TYPE_INSTITUTION_ID)){
	        $cond.=" AND inst_institutions.`TYPE_INSTITUTION_ID`=".$TYPE_INSTITUTION_ID; 
	      }
	    if(! empty($INSTITUTION_ID))
	      {
	       $cond.=' AND ptba.INSTITUTION_ID='.$INSTITUTION_ID;
	      }

	    if(! empty($PROGRAMME_ID))
	      {
	       $cond.=' AND ptba.PROGRAMME_ID='.$PROGRAMME_ID;
	      }

	     if(! empty($ACTION_ID))
	      {
	     $cond.=' AND ptba.ACTION_ID='.$ACTION_ID;
	      }

	       if(! empty($LIGNE_BUDGETAIRE))
	      {
	     $cond.=' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE='.$LIGNE_BUDGETAIRE;
	      }


	     $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

	    $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES, ptba.PROGRAMMATION_FINANCIERE_BIF,ptba.RESULTATS_ATTENDUS, ptba.INTITULE_DES_GRANDES_MASSES, inst_grande_masse.DESCRIPTION_GRANDE_MASSE FROM ptba LEFT JOIN inst_grande_masse ON inst_grande_masse.GRANDE_MASSE_ID=ptba.GRANDE_MASSE_BM LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID LEFT JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID LEFT JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID WHERE 1 ".$cond." "; 



	  
$limit='LIMIT 0,10';
	if($_POST['length'] != -1)
	{
	$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
	}
	$order_by='';
	if($_POST['order']['0']['column']!=0) {

	$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ID_PROJET  ASC'; 
	}


	$search = !empty($_POST['search']['value']) ? (' AND (inst_institutions_programmes.INTITULE_MINISTERE LIKE "%$var_search%")') : '';

      



		  $critere='';
		  if ($KEY==7){
		  	$critere=' AND inst_grande_masse.GRANDE_MASSE_ID in (6,7,8)';
		     }else{
		     $critere=' AND inst_grande_masse.GRANDE_MASSE_ID ='.$KEY;
		     }
		$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
        $query_filter=$query_principal.' '.$critere.'  '.$search;

		$query_secondaire="CALL `getTable`('".$conditions."')";
		$fetch_res= $this->ModelPs->datatable($query_secondaire);
		
		$data = array();		
		$u=1;
		foreach ($fetch_res as $row) {
			$sub_array = array();
			$sub_array[]=$u++;
			$sub_array[] = $row->INTITULE_MINISTERE;
			$sub_array[] = $row->INTITULE_PROGRAMME; 
			$sub_array[] = $row->RESULTATS_ATTENDUS;
			$sub_array[] = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
			$sub_array[] = $row->ACTIVITES;
			$sub_array[] = $row->PROGRAMMATION_FINANCIERE_BIF;
			$sub_array[] = $row->INTITULE_DES_GRANDES_MASSES;

			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$query_principal."')");
        $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$query_filter."')");

		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" =>count($recordsTotal),
			"recordsFiltered" =>count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

      #####detail du rappport du budget transfert

     

 function detail_ptba_transferts()
       {
    $KEY=$this->request->getPost('key');
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $cond="";
    if(! empty($TYPE_INSTITUTION_ID))
	{
   $KEY2=1;
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
      $KEY2=2;
	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}
   	if(! empty($PROGRAMME_ID))
	{
    if ($TYPE_INSTITUTION_ID==2) {

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	$KEY2=3;
	}else{
	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $KEY2=3;
   }
   }
	if(! empty($ACTION_ID))
	{
    $KEY2=4;
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

    $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION
,ptba.ACTIVITES,ptba.RESULTATS_ATTENDUS,`T1`,`T2`,`T3`,`T4`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution,proc_demande_exec_budgetaire.MONTANT_TRANSFERT FROM ptba JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.ACTIVITE_DE=ptba.PTBA_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID
 WHERE 1   ".$cond." ";



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
    `ACTIVITES` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%')") : '';

     $critere=' AND ptba.PTBA_ID ='.$KEY;
     
    $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
    $query_filter=$query_principal.' '.$critere.'  '.$search;

    $query_secondaire = 'CALL `getTable`("' . $conditions . '");';

    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $u=0;
    $data = array();
    foreach ($fetch_data as $row) 
    {
    $u++;
    $ptba=array();

    $ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';

  $ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_MINISTERE.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';

      $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';

     $ptba[] ='<center><font color="#000000" size=2><label>'.number_format($row->T1+$row->T2+$row->T3+$row->T4,0,',',' ').'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->MONTANT_TRANSFERT.'</label></font> </center>';

    $data[] = $ptba;        
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
   


	########

 function detail_ptba_recus()
       {
    $KEY=$this->request->getPost('key');
    $TYPE_INSTITUTION_ID=$this->request->getPost('TYPE_INSTITUTION_ID');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

    $cond="";
    if(! empty($TYPE_INSTITUTION_ID))
	{
   $KEY2=1;
	$cond.=' AND inst_institutions.TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID;
	}

	if(! empty($INSTITUTION_ID))
	{
      $KEY2=2;
	$cond.=" AND ptba.INSTITUTION_ID='".$INSTITUTION_ID."'";
	}
   	if(! empty($PROGRAMME_ID))
	{
    if ($TYPE_INSTITUTION_ID==2) {

	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
	$KEY2=3;
	}else{
	$cond.=" AND ptba.PROGRAMME_ID='".$PROGRAMME_ID."'";
    $KEY2=3;
   }
   }
	if(! empty($ACTION_ID))
	{
    $KEY2=4;
	$cond.=" AND ptba.ACTION_ID='".$ACTION_ID."'";  
	}


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

    $query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_actions.LIBELLE_ACTION,ptba.ACTIVITES,ptba.RESULTATS_ATTENDUS,`T1`,`T2`,`T3`,`T4`,if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') AS institution,proc_demande_exec_budgetaire.MONTANT_TRANSFERT FROM ptba JOIN proc_demande_exec_budgetaire ON proc_demande_exec_budgetaire.ACTIVITE_VERS=ptba.PTBA_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba.ACTION_ID WHERE 1  ".$cond." ";

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
    `ACTIVITES` LIKE '%$var_search%' OR if(`TYPE_INSTITUTION_ID`=1,'Institution','Ministere') LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%')") : '';



     $critere=' AND ptba.PTBA_ID ='.$KEY;
     
    $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
    $query_filter=$query_principal.' '.$critere.'  '.$search;

    $query_secondaire = 'CALL `getTable`("' . $conditions . '");';

    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $u=0;
    $data = array();
    foreach ($fetch_data as $row) 
    {
    $u++;
    $ptba=array();

    $ptba[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->institution.'</label></font> </center>';

  $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->LIBELLE_ACTION.'</label></font> </center>';
    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->RESULTATS_ATTENDUS.'</label></font> </center>';

      $ptba[] ='<center><font color="#000000" size=2><label>'.$row->ACTIVITES.'</label></font> </center>';

      $ptba[] ='<center><font color="#000000" size=2><label>'.number_format($row->T1+$row->T2+$row->T3+$row->T4,0,',',' ').'</label></font> </center>';

    $ptba[] ='<center><font color="#000000" size=2><label>'.$row->MONTANT_TRANSFERT.'</label></font> </center>';

    $data[] = $ptba;        
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
