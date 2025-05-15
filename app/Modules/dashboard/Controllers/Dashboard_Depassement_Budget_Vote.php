<?php
/**
 * @author NIYONGABO Emery
 *emery@mediabox.bi
 * Tableau de bord «dashbord des taux par phase»
 le 09/11/2023
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
 class Dashboard_Depassement_Budget_Vote extends BaseController
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
  }
  
  //fonction qui retourne les couleurs
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
         $user_id ='';
         $inst_connect ='';
         $prof_connect ='';
         $type_connect ='';
         if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
         {
          return redirect('Login_Ptba');
         }

        if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE')!=1)
          {
          return redirect('Login_Ptba/homepage');
          }

        if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
        {
          $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
          $user_affectation=("SELECT user_affectaion.`INSTITUTION_ID` FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
          $user_affectations=$this->ModelPs->getRequete(' CALL getTable("'.$user_affectation.'")');
          $nombre=count($user_affectations);
          $cond_affectations="";
          if ($nombre>0) {

           if ($nombre==1) {
            foreach ($user_affectations as $value) {
             $cond_affectations=" AND INSTITUTION_ID= ".$value->INSTITUTION_ID;
           }
         }else if ($nombre>1){
          $inst="(";
          foreach ($user_affectations as $value) {
           $inst.=$value->INSTITUTION_ID.",";

         }
           //Enlever la dernier virgule
         $inst = substr($inst, 0, -1);
         $inst=$inst.")";
         $cond_affectations.=" AND INSTITUTION_ID IN ".$inst;


       }
     }else{
       return redirect('Login_Ptba');

     }
   }   
   else
   {
    return redirect('Login_Ptba');
  }

  $requete_cat="SELECT  DISTINCT TYPE_INSTITUTION_ID,if(TYPE_INSTITUTION_ID=1,'".lang("messages_lang.admin_perso")."','".lang("messages_lang.minister")."') as Name FROM `inst_institutions` WHERE 1 ".$cond_affectations." "; 

    $data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_cat.'")');
    $inst_connexion='<input type="hidden" name="inst_conn" id="inst_conn" value="'.$user_id.'">';

    $data['TYPE_INSTITUTION_ID']=$this->request->getPost('');

    $data['inst_connexion']=$inst_connexion;

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
   $data['prof_connect']=$prof_connect;
   $data['type_connect']=$type_connect;
   $data['inst_connexion']=$inst_connexion;
   $data['ann_actuel_id'] = $this->get_annee_budgetaire();
   $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID>=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
   $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');

   return view('App\Modules\dashboard\Views\Dashboard_Depassement_Budget_Vote_View',$data);
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
  $LIGNE_BUDGETAIRE=$this->request->getVar('LIGNE_BUDGETAIRE');
  $IS_PRIVATE=$this->request->getVar('IS_PRIVATE');
  $inst_conn=$this->request->getVar('inst_conn');
  // $ANNEE_BUDGETAIRE_ID=$this->request->getVar('ANNEE_BUDGETAIRE_ID');

  $cond_pri='';
  $cond_pri1='';
  if ($inst_conn>0){
   $user_inst=("SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn."");
   $user_inst_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_inst.'")');
   $fonct_inst='';
   $fonct_key2='';
   $One_select=count($user_inst_req);
   if ($One_select==1){
    $One_code=(" SELECT CODE_INSTITUTION FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$inst_conn.") ");
    $One_code_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$One_code.'")');
    $INSTITUTION_ID=$One_code_req['CODE_INSTITUTION'];
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
$cond_pri.=' AND INSTITUTION_ID IN ('.$code_inst.')';
$cond_pri1.=' AND ptba_tache.INSTITUTION_ID IN ('.$code_inst.')';
$cond_trim='';
if ($IS_PRIVATE==1){
  $totaux='SUM(BUDGET_T1)';
  $cond_trim=" AND execution_budgetaire.TRIMESTRE_ID=1" ;
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
$titr_deux=' '.lang("messages_lang.par_categorie").''; 
$titr_deux2=' '.lang("messages_lang.par_categorie").'';
$id_decl= 'TYPE_INSTITUTION_ID'; 
$name_decl= "if(TYPE_INSTITUTION_ID=1,'".lang("messages_lang.admin_perso")."','".lang("messages_lang.minister")."')";
$format=" {point.y:.3f} %";
$type="column";

$name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
if(! empty($TYPE_INSTITUTION_ID))
{
  $titr_deux=' '.lang("messages_lang.par_institution").'';
  $titr_deux2=' '.lang("messages_lang.par_institution").'';
  $id_decl= 'inst_institutions.INSTITUTION_ID'; 
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
  $name_decl= "DESCRIPTION_SOUS_TUTEL"; 
  $id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
  $name_table1= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN (SELECT DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE inst_institutions_sous_tutel.INSTITUTION_ID in (SELECT INSTITUTION_ID FROM inst_institutions WHERE inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."')) as inst_institutions_sous_tutel ON SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)= inst_institutions_sous_tutel.CODE_SOUS_TUTEL";

  $format=" {point.y:.2f} %";
  $type="column";
  $titr_deux=' '.lang("messages_lang.par_service").'';
  $titr_deux2=' '.lang("messages_lang.par_service").'';
  $KEY2=5;
  $cond_sy=("SELECT `INSTITUTION_ID` FROM `inst_institutions` WHERE `INSTITUTION_ID`='".$INSTITUTION_ID."' ");
  $cond_sy_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$cond_sy.'")');
  if (! empty($cond_sy_req['INSTITUTION_ID'])) {
   $cond1=' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
   $cond_program=' AND `inst_institutions`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
   $cond2= ' AND `inst_institutions_sous_tutel`.`INSTITUTION_ID`='.$cond_sy_req['INSTITUTION_ID'];
 }
 $cond.=" AND ptba_tache.INSTITUTION_ID='".$INSTITUTION_ID."'";
}
if(! empty($SOUS_TUTEL_ID))
{
  $name_decl= "DESCRIPTION_SOUS_TUTEL"; 
  $id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
  $name_table1= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $name_table= "  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_sous_tutel ON inst_institutions_sous_tutel.CODE_SOUS_TUTEL=SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)";
  $cond.=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."'";
  $format=" {point.y:.2f} %";
  $type="column";
  $titr_deux=' '.lang("messages_lang.par_service").'';
  $titr_deux2=' '.lang("messages_lang.par_service").'';
  $KEY2=5;

}

$cond33='';
$cond333="";
$cond3333="";
if(! empty($PROGRAMME_ID))
{
  $id_decl= 'ptba_tache.ACTION_ID'; 
  $name_decl= "LIBELLE_ACTION"; 
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID";
  $cond.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $cond33.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $cond3333.=" AND ptba_tache.PROGRAMME_ID='".$PROGRAMME_ID."'";
  $type="column";
  $format=" {point.y:.3f} %";
  $titr_deux=' '.lang("messages_lang.par_action").'';
  $titr_deux2=' '.lang("messages_lang.par_action").'';
  $cond2='';
  $KEY2=3;
}

if(! empty($ACTION_ID))
{
  $id_decl= "ptba_tache.PTBA_TACHE_ID";  
  $name_decl= "DESC_TACHE";
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID";
  $cond.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
  $cond333.=" AND ptba_tache.ACTION_ID='".$ACTION_ID."'";
  $type="line";
  $titr_deux='par activités';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;  
}

if(!empty($LIGNE_BUDGETAIRE))
{
  $id_decl= "ptba_tache.PTBA_TACHE_ID";  
  $name_decl= "DESC_TACHE";
  $name_table= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
  $cond.=" AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE=".$LIGNE_BUDGETAIRE.""; 
  $type="line";
  $titr_deux='par activités';
  $titr_deux2='';
  $format=" {point.y:.3f} %";
  $KEY2=4;  
}

$engage11=("SELECT ".$name_decl." AS name,".$id_decl." as ID,COUNT(execution_budgetaire.EXECUTION_BUDGETAIRE_ID) as engage FROM `execution_budgetaire` JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=execution_budgetaire.EXECUTION_BUDGETAIRE_ID
JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID ".$name_table." WHERE ptba_tache.BUDGET_T1<execution_budgetaire.ENG_BUDGETAIRE ".$cond_trim." ".$cond." ".$cond_pri1." GROUP BY ".$name_decl.",".$id_decl." ORDER by inst_institutions.INSTITUTION_ID ASC");

  // print_r($engage11);
$engage_req11=$this->ModelPs->getRequete(' CALL getTable("'.$engage11.'")');
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
  $categorie.="'";
  $name = (!empty($value->name)) ? $value->name : "Autres";
  $rappel=$this->str_replacecatego($name);
  $categorie.= $rappel."',";       
  $data_engager_req.="{name:'".$this->str_replacecatego($value->name)."', y:".$value->engage.",key:".$value->ID.",key2:".$KEY2.",key3:1},";
  $data_engage_total=$data_engage_total+$value->engage;
}

$rapp1="<script type=\"text/javascript\">
Highcharts.chart('container1', { 
  chart: {
   type: 'column'
   },
   title: {
    text: '<b> ".lang("messages_lang.activite_depassant_budget_vote")." </b>'
    },
    subtitle: {
     text: ''
     },
     xAxis:{
      categories: [".$categorie."],
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
             $(\"#myModal\").modal('show');
             $(\"#titre\").html(\"".lang("messages_lang.activites_list")."\");
             var row_count ='1000000';
             $(\"#mytable\").DataTable({
              \"processing\":true,
              \"serverSide\":true,
              \"bDestroy\": true,
              \"ajax\":{
               url:\"".base_url('dashboard/Dashboard_Depassement_Budget_Vote/detail_depassement_budget_vote')."\",
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
              lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, \"All\"]],
              pageLength:5,
              \"columnDefs\":[{
                \"targets\":[],
                \"orderable\":false
                }],
                dom: 'Bfrtlip',
                buttons: ['excel', 'pdf'],
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
           dataLabels:{
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
               name:'Activites',
               data: [".$data_engager_req."],
               marker: {
                lineWidth: 2,
                lineColor: Highcharts.getOptions().colors[3],
                fillColor: 'white'
              }
            }
            ]
            })
            </script>";
        $inst= '<option selected="" disabled="">sélectionner</option>';
          if (!empty($TYPE_INSTITUTION_ID))
              {
          $inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID as CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' '.$cond_pri1.' group BY DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';

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
    $soustutel_sect="SELECT DISTINCT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel   WHERE 1 ".$cond1." ORDER BY inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL ASC ";
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
     $inst_sect='SELECT DISTINCT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID AS CODE_INSTITUTION FROM inst_institutions JOIN ptba_tache ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE TYPE_INSTITUTION_ID='.$TYPE_INSTITUTION_ID.' group BY DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID  ORDER BY DESCRIPTION_INSTITUTION ASC';
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
 $program_sect="SELECT DISTINCT inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_programmes.PROGRAMME_ID as  CODE_PROGRAMME FROM  inst_institutions_programmes JOIN inst_institutions ON  inst_institutions.INSTITUTION_ID=inst_institutions_programmes.INSTITUTION_ID JOIN ptba_tache ON ptba_tache.PROGRAMME_ID=inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$SOUS_TUTEL_ID."' ".$cond_program."  ORDER BY inst_institutions_programmes.CODE_PROGRAMME ASC";
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
 $actions_sect='SELECT DISTINCT inst_institutions_actions.ACTION_ID as CODE_ACTION,ptba_tache.LIBELLE_ACTION FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID where 1  '.$cond33.' ORDER BY CODE_ACTION ASC';
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
 $ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond333." ";
}else{
 $ligne_budgetaire_sect="SELECT DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE FROM  ptba_tache JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond3333."   ";  	
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
echo json_encode(array('rapp1'=>$rapp1,'inst'=>$inst,'soustutel'=>$soustutel,'program'=>$program,'actions'=>$actions,'ligne_budgetaires'=>$ligne_budgetaires));
}

   # fonction pour les details
function detail_depassement_budget_votes() 
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
$cond1="";
$name_table="";
if(!empty($INSTITUTION_ID))
{

 $name_decl= "DESCRIPTION_SOUS_TUTEL"; 
 $id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
 $name_table1= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
 $name_table= " JOIN (SELECT DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE inst_institutions_sous_tutel.INSTITUTION_ID in (SELECT INSTITUTION_ID FROM inst_institutions WHERE inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."')) as inst_institutions_sous_tutel ON SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)= inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
 $cond1=" AND inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'";
}

if ($KEY3==1) {
 $totaux='if(BUDGET_T1>0,BUDGET_T1,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=1" ;
}else if ($KEY3==2) {
 $totaux='if(BUDGET_T2>0,BUDGET_T2,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=2";
}else if ($KEY3==3) {
 $totaux='if(BUDGET_T3>0,BUDGET_T3,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=3" ;
}else if ($KEY3==4){
 $totaux='if(BUDGET_T4>0,BUDGET_T4,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=4" ;
}else {
 $totaux=' if(BUDGET_T1>0,BUDGET_T1,0)+if(BUDGET_T2>0,BUDGET_T2,0)+if(BUDGET_T3>0,BUDGET_T3,0)+if(BUDGET_T4>0,BUDGET_T4,0)';
 $cond_trim=" " ;
}
$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION,activite.EXECUTION_BUDGETAIRE_ID,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,`RESULTAT_ATTENDUS_TACHE`,BUDGET_T1,if(activite.`ENG_BUDGETAIRE`>0,activite.`ENG_BUDGETAIRE`,0) AS ENG_BUDGETAIRE FROM execution_budgetaire activite JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=activite.EXECUTION_BUDGETAIRE_ID   JOIN ptba_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID ".$name_table." WHERE ptba_tache.BUDGET_T1<activite.ENG_BUDGETAIRE ".$cond1."  ".$cond." ".$cond_trim." ";

$limit='LIMIT 0,10';
if ($_POST['length'] != -1)
{
 $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
}
$order_by = '';

$order_column=array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','RESULTAT_ATTENDUS_TACHE','DESC_TACHE',1,'activite.ENG_BUDGETAIRE',1);

$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY INTITULE_PROGRAMME ASC';
$search = !empty($_POST['search']['value']) ? ("AND (
 DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%')") : '';

$critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
if($KEY2==1)
{
 $critere=" AND inst_institutions.TYPE_INSTITUTION_ID=".$KEY;
}
if ($KEY2==2)
{
 $critere=" AND ptba_tache.INSTITUTION_ID=".$KEY;
}
if ($KEY2==5)
{
 $critere=" AND SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)='".$KEY."'";
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
$date_engage=("SELECT DATE_LIQUIDATION,DATE_ORDONNANCEMENT,DATE_PAIEMENT,execution_budgetaire_titre_decaissement.DATE_DECAISSEMENT FROM execution_budgetaire_tache_detail JOIN execution_budgetaire_titre_decaissement ON execution_budgetaire_titre_decaissement.EXECUTION_BUDGETAIRE_DETAIL_ID=execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID."");
 $date_engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$date_engage.'")');
 $retdate_engage ="";
 $intrant=array();
 $mona_vote=number_format($row->BUDGET_T1,0,',',' ');
 $mona_de=number_format($row->ENG_BUDGETAIRE,0,',',' ');
 $retdate_engage=(! empty($date_engage_req['DATE_DEMANDE'])) ? $date_engage_req['DATE_DEMANDE'] : 'N/A' ;
 $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
 $intrant[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
 if (strlen($row->DESCRIPTION_INSTITUTION) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}

if (strlen($row->INTITULE_PROGRAMME) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';

}
if (strlen($retVal) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($retVal, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a></label></font> </center>';

}
if (strlen($row->RESULTAT_ATTENDUS_TACHE) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->RESULTAT_ATTENDUS_TACHE.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->RESULTAT_ATTENDUS_TACHE, 0, 15).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_ATTENDUS_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
if (strlen($row->DESC_TACHE) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_TACHE, 0, 12).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';

}
$intrant[] ='<center><font color="#000000" size=2><label>'.$mona_vote.'</label></font> </center>';

$intrant[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
$intrant[] ='<center><font color="#000000" size=2><label>'.$retdate_engage.'</label></font> </center>';

$data[] = $intrant;        
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

    # fonction pour les details
function liste_depassement_budget_votes() 
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
  $cond11='';
  $IS_PRIVATE=$this->request->getPost('IS_PRIVATE');
  if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
  {
   $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

   $user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
   $user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');

   $nombre=count($user_connect_req);
   if ($nombre>1) {
    $cond11.=" AND ptba_tache.CODE_MINISTERE IN (SELECT INSTITUTION_ID FROM `inst_institutions` WHERE INSTITUTION_ID IN (SELECT `INSTITUTION_ID` FROM user_affectaion WHERE USER_ID=".$user_id.") )";

  }else{
    $cond11.='';	
  }
}
$cond1="";
$name_table="";

if(! empty($TYPE_INSTITUTION_ID))
{
 $cond1.=" AND inst_institutions.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID;
}

if(! empty($INSTITUTION_ID))
{
 $name_decl= "DESCRIPTION_SOUS_TUTEL"; 
 $id_decl= "inst_institutions_sous_tutel.CODE_SOUS_TUTEL";
 $name_table1= " JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";
 $name_table= " JOIN (SELECT DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE inst_institutions_sous_tutel.INSTITUTION_ID in (SELECT INSTITUTION_ID FROM inst_institutions WHERE inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."')) as inst_institutions_sous_tutel ON SUBSTRING(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE, 5,3)= inst_institutions_sous_tutel.CODE_SOUS_TUTEL";

 $cond1.=" AND inst_institutions.INSTITUTION_ID=".$INSTITUTION_ID;
}

// if(!empty($ANNEE_BUDGETAIRE_ID))
// {
//   $cond1.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID."";
// }



if ($IS_PRIVATE==1) {
 $totaux='if(BUDGET_T1>0,BUDGET_T1,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=1" ;
}else if ($IS_PRIVATE==2) {
 $totaux='if(BUDGET_T2>0,BUDGET_T2,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=2";
}else if ($IS_PRIVATE==3) {
 $totaux='if(BUDGET_T3>0,BUDGET_T3,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=3" ;
}else if ($IS_PRIVATE==4){
 $totaux='if(BUDGET_T4>0,BUDGET_T4,0)';
 $cond_trim=" AND activite.TRIMESTRE_ID=4" ;
}else {
 $totaux=' if(BUDGET_T1>0,BUDGET_T1,0)+if(BUDGET_T2>0,BUDGET_T2,0)+if(BUDGET_T3>0,BUDGET_T3,0)+if(BUDGET_T4>0,BUDGET_T4,0)';
 $cond_trim=" " ;
}
$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
$query_principal="SELECT inst_institutions.DESCRIPTION_INSTITUTION,activite.EXECUTION_BUDGETAIRE_ID,ptba_tache.PTBA_TACHE_ID,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,activite.DATE_ENG_JURIDIQUE,activite.DATE_DEMANDE,`RESULTAT_ATTENDUS_TACHE`,BUDGET_T1,if(activite.`ENG_BUDGETAIRE`>0,activite.`ENG_BUDGETAIRE`,0) AS ENG_BUDGETAIRE FROM execution_budgetaire activite JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=activite.EXECUTION_BUDGETAIRE_ID  JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=execution_budgetaire_execution_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID ".$name_table." WHERE ptba_tache.BUDGET_T1<activite.ENG_BUDGETAIRE ".$cond1."  ".$cond." ".$cond_trim." ";

$limit='LIMIT 0,10';
if ($_POST['length'] != -1)
{
 $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
}
$order_by = '';

$order_column=array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','RESULTAT_ATTENDUS_TACHE','ACTIVITES',1,'activite.ENG_BUDGETAIRE',1,1);

$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_MINISTERE ASC';

$search = !empty($_POST['search']['value']) ? ("AND (
 DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR inst_institutions_actions.LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTAT_ATTENDUS_TACHE LIKE '%$var_search%')") : '';

$critere="";
$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
$query_filter=$query_principal.' '.$critere.'  '.$search;

$query_secondaire = 'CALL `getTable`("' . $conditions . '");';

$fetch_data = $this->ModelPs->datatable($query_secondaire);
$u=0;
$data = array();
foreach ($fetch_data as $row) 
     {
  $u++;
 $racc = 'SELECT COUNT(HISTORIQUE_TRANSFERT_ID) as nbre FROM `transfert_historique_transfert` WHERE 1 AND PTBA_TACHE_ID_RECEPTION= '.$row->PTBA_TACHE_ID;
 $racc = "CALL `getTable`('" . $racc . "');";
 $raccrocher = $this->ModelPs->getRequeteOne($racc);
 $racrocha='<center><a onclick="get_detail_activite('.$row->PTBA_TACHE_ID.')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">'.$raccrocher['nbre'].'</b></button></a></center>';

 $date_engage=("SELECT DATE_LIQUIDATION,DATE_ORDONNANCEMENT,DATE_PAIEMENT,execution_budgetaire_titre_decaissement.DATE_DECAISSEMENT FROM execution_budgetaire_tache_detail JOIN execution_budgetaire_titre_decaissement ON execution_budgetaire_titre_decaissement.EXECUTION_BUDGETAIRE_DETAIL_ID=execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID."");
 $date_engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$date_engage.'")');
 $retdate_engage ="";
 $intrant=array();
 $mona_vote=number_format($row->BUDGET_T1,0,',',' ');
 $mona_de=number_format($row->ENG_BUDGETAIRE,0,',',' ');
 $retdate_engage=(! empty($row->DATE_DEMANDE)) ? $row->DATE_DEMANDE : 'N/A' ;
 $retVal = ($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A' ;
 $intrant[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
 if (strlen($row->DESCRIPTION_INSTITUTION) < 10){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESCRIPTION_INSTITUTION, 0, 10).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
if (strlen($row->INTITULE_PROGRAMME) < 10){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->INTITULE_PROGRAMME.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->INTITULE_PROGRAMME, 0, 10).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
if (strlen($retVal) < 8){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$retVal.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($retVal, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$retVal.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
if (strlen($row->RESULTAT_ATTENDUS_TACHE) < 13){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->RESULTAT_ATTENDUS_TACHE.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->RESULTAT_ATTENDUS_TACHE, 0, 15).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->RESULTAT_ATTENDUS_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
if (strlen($row->DESC_TACHE) < 8){
  $intrant[] ='<center><font color="#000000" size=2><label>'.$row->DESC_TACHE.'</label></font> </center>';
}else{
  $intrant[] ='<center><font color="#000000" size=2><label>'.mb_substr($row->DESC_TACHE, 0, 8).'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a></label></font> </center>';
}
$intrant[] ='<center><font color="#000000" size=2><label>'.$mona_vote.'</label></font> </center>';
$intrant[] ='<center><font color="#000000" size=2><label>'.$mona_de.'</label></font> </center>';
$intrant[] ='<center><font color="#000000" size=2><label>'.$retdate_engage.'</label></font> </center>';
$intrant[]= $racrocha;
$data[] = $intrant;        
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

function exporter($TYPE_INSTITUTION_ID,$INSTITUTION_ID,$IS_PRIVATE)
{
  $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
  if(empty($USER_IDD))
  {
   return redirect('Login_Ptba/do_logout');
 }

 $db = db_connect();
 $callpsreq = "CALL getRequete(?,?,?,?);";

 $cond='';
 $cond1="";
 $name_table="";
 if($TYPE_INSTITUTION_ID>0)
 {
   $cond.=" AND inst_institutions.TYPE_INSTITUTION_ID=".$TYPE_INSTITUTION_ID;
 }

 if($INSTITUTION_ID>0)
 {
  
   $cond.=" AND inst_institutions.INSTITUTION_ID='".$INSTITUTION_ID."'";
 }

 if ($IS_PRIVATE==1) {

  $cond_trim=" AND activite.TRIMESTRE_ID=1" ;
}else if ($IS_PRIVATE==2) {

  $cond_trim=" AND activite.TRIMESTRE_ID=2";
}else if ($IS_PRIVATE==3) {

  $cond_trim=" AND activite.TRIMESTRE_ID=3" ;
}else if ($IS_PRIVATE==4){

  $cond_trim=" AND activite.TRIMESTRE_ID=4" ;
}else {

  $cond_trim=" " ;
}

// if($ANNEE_BUDGETAIRE_ID>0)
// {
//   $cond1=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
// }
$getRequete="SELECT inst_institutions.DESCRIPTION_INSTITUTION,activite.EXECUTION_BUDGETAIRE_ID,ptba_tache.PTBA_TACHE_ID,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,activite.DATE_ENG_JURIDIQUE,activite.DATE_DEMANDE,`RESULTAT_ATTENDUS_TACHE`,BUDGET_T1,if(activite.`ENG_BUDGETAIRE`>0,activite.`ENG_BUDGETAIRE`,0) AS ENG_BUDGETAIRE FROM execution_budgetaire activite JOIN execution_budgetaire_execution_tache ON execution_budgetaire_execution_tache.EXECUTION_BUDGETAIRE_ID=activite.EXECUTION_BUDGETAIRE_ID  JOIN ptba_tache ON execution_budgetaire_execution_tache.PTBA_TACHE_ID=ptba_tache.PTBA_TACHE_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID WHERE ptba_tache.BUDGET_T1<activite.ENG_BUDGETAIRE ".$cond1."  ".$cond." ".$cond_trim." ";
$getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'INSTITUTION');
$sheet->setCellValue('B1', 'PROGRAMME');
$sheet->setCellValue('C1', 'ACTION');
$sheet->setCellValue('D1', 'RESULTATS ATTENDUS');
$sheet->setCellValue('E1', 'ACTIVITES');
$sheet->setCellValue('F1', 'BUDGET VOTE');
$sheet->setCellValue('G1', 'ENGAGEMENT BUDGETAIRE');
$sheet->setCellValue('H1', 'DATE ENGAGEMENT BUDGETAIRE');
$sheet->setCellValue('I1', 'MONTANT RECU COMME TRANSFERT');

$rows = 3;
foreach ($getData as $key)
{
 $racc ='SELECT SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT FROM transfert_historique_transfert WHERE 1 AND PTBA_TACHE_ID_RECEPTION='.$key->PTBA_TACHE_ID;
 $racc = "CALL `getTable`('" . $racc . "');";
 $raccrocher = $this->ModelPs->getRequeteOne($racc);
 $date_engage=("SELECT DATE_LIQUIDATION,DATE_ORDONNANCEMENT,DATE_PAIEMENT,execution_budgetaire_titre_decaissement.DATE_DECAISSEMENT FROM execution_budgetaire_tache_detail JOIN execution_budgetaire_titre_decaissement ON execution_budgetaire_titre_decaissement.EXECUTION_BUDGETAIRE_DETAIL_ID=execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE execution_budgetaire_tache_detail.EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID."");
 $date_engage_req=$this->ModelPs->getRequeteOne(' CALL getTable("'.$date_engage.'")');
 $retdate_engage=(!empty($key->DATE_DEMANDE)) ? $key->DATE_DEMANDE : 'N/A' ;

 $mona_vote = 0;
 $ENG_BUDGETAIRE = 0;
 if (!empty($key->T1))
 {
  $mona_vote=$key->T1;
}

if (!empty($key->ENG_BUDGETAIRE))
{
  $mona_de=$key->ENG_BUDGETAIRE;
}
$sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
$sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
$sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
$sheet->setCellValue('D' . $rows, $key->RESULTAT_ATTENDUS_TACHE);
$sheet->setCellValue('E' . $rows, $key->DESC_TACHE);
$sheet->setCellValue('F' . $rows, $mona_vote);
$sheet->setCellValue('G' . $rows, $mona_de);
$sheet->setCellValue('H' . $rows, $retdate_engage);
$sheet->setCellValue('I' . $rows, $raccrocher['MONTANT_TRANSFERT']);
$rows++;
} 
$writer = new Xlsx($spreadsheet);
$writer->save('world.xlsx');
return $this->response->download('world.xlsx', null)->setFileName('Rapport des activités ayant depassé leurs budgets votés.xlsx');
return redirect('dashboard/Dashboard_Depassement_Budget_Vote');
}
}
?>
