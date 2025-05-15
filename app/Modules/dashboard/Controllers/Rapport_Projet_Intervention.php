<?php
namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use App\Controllers\Login_Ptba;
 /* Rapport Repartition des projets selon les axes d'intervention
 * claude@mediabox.bi
 * le 01/12/2023
 */ 
  //Appel de l'espace de nom du Controllers Rapport_Projet_Intervention
 class Rapport_Projet_Intervention extends BaseController
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

  // Fonction de départ
  public function index()
  { 
    $db=db_connect();
    $data=$this->urichk();
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_AXE_INTERVENTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $requete_inst=("SELECT inst_institutions.`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION` FROM `inst_institutions` WHERE 1 ");
    $date="date_format(DATE_DEBUT_PROJET,'%Y')";
    $req='SELECT DISTINCT '.$date.' AS annee  FROM `pip_demande_infos_supp` WHERE 1 ORDER BY annee DESC';
    $data['annees']=$this->ModelPs->getRequete('CALL getTable("'.$req.'")');
    $axes="SELECT `ID_AXE_INTERVENTION_PND`, `DESCR_AXE_INTERVATION_PND` FROM `axe_intervention_pnd` WHERE 1";
    $data['axe_intervations']=$this->ModelPs->getRequete('CALL getTable("'.$axes.'")');
    $data['INSTITUTION_ID1']=0;
    $data['institutions']=$this->ModelPs->getRequete('CALL getTable("'.$requete_inst.'")');
    $data['INSTITUTION_ID']=$this->request->getPost('');
    return view('App\Modules\dashboard\Views\Rapport_Projet_Intervention_View',$data);
  }
 //Fonction pour appel des series et hichart & gestion des filtres
  public function get_rapport()
  {
   $data=$this->urichk();
   $db = db_connect(); 
   $session  = \Config\Services::session();
   $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
   $ID_AXE_INTERVENTION_PND=$this->request->getPost('ID_AXE_INTERVENTION_PND');
   $critere="";
   if ($ID_AXE_INTERVENTION_PND>0) {
     $critere.=" AND pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=".$ID_AXE_INTERVENTION_PND."";
   }
   if (!empty($INSTITUTION_ID)) {
     $critere.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
   }
   if (!empty($annee)) {
     $critere.=" AND date_format(pip.DATE_DEBUT_PROJET,'%Y')='".$annee."'";
   }
   $pillier2="SELECT `ID_AXE_INTERVENTION_PND` AS ID,`DESCR_AXE_INTERVATION_PND` AS NAME,(SELECT SUM(MONTANT_NOMENCALTURE)  FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP WHERE pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=axe_intervention_pnd.ID_AXE_INTERVENTION_PND AND pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID=2 ".$critere." ) AS NBRE FROM axe_intervention_pnd WHERE 1   GROUP BY axe_intervention_pnd.ID_AXE_INTERVENTION_PND,axe_intervention_pnd.DESCR_AXE_INTERVATION_PND";  
   $nbr_pillier2=$this->ModelPs->getRequete('CALL getTable("'.$pillier2.'")'); 
   $pillier_tot="SELECT SUM(MONTANT_NOMENCALTURE) AS  TOT FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP WHERE 1 ".$critere." "; 
   $nbre_tot=$this->ModelPs->getRequeteOne('CALL getTable("'.$pillier_tot.'")'); 
   $data_project2="";
   $data_total2=0;
   foreach ($nbr_pillier2 as $key) {
    $get_exec=($key->NBRE)?$key->NBRE:'0';
    $px=0;
    if ($nbre_tot['TOT']>0) {
      $px=($get_exec/$nbre_tot['TOT'])*100;
    }
    $data_total2=$data_total2+$get_exec;
    $data_project2.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key2:2,key:'".$key->ID."'},";
  }
  $pillier3="SELECT `ID_AXE_INTERVENTION_PND` AS ID,`DESCR_AXE_INTERVATION_PND` AS NAME,(SELECT SUM(MONTANT_NOMENCALTURE)  FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP WHERE pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=axe_intervention_pnd.ID_AXE_INTERVENTION_PND AND pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID=3 ".$critere." ) AS NBRE FROM axe_intervention_pnd WHERE 1   GROUP BY axe_intervention_pnd.ID_AXE_INTERVENTION_PND,axe_intervention_pnd.DESCR_AXE_INTERVATION_PND";  
  $nbr_pillier3=$this->ModelPs->getRequete('CALL getTable("'.$pillier3.'")'); 
  $data_project3="";
  $data_total3=0;
  foreach ($nbr_pillier3 as $key) {
    $get_exec=($key->NBRE)?$key->NBRE:'0';
    $px=0;
    if ($nbre_tot['TOT']>0) {
      $px=($get_exec/$nbre_tot['TOT'])*100;
    }
    $data_total3=$data_total3+$get_exec;
    $data_project3.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key2:3,key:'".$key->ID."'},";
  }
  $pillier4="SELECT `ID_AXE_INTERVENTION_PND` AS ID,`DESCR_AXE_INTERVATION_PND` AS NAME,(SELECT SUM(MONTANT_NOMENCALTURE)  FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP WHERE pip_demande_infos_supp.ID_AXE_INTERVENTION_PND=axe_intervention_pnd.ID_AXE_INTERVENTION_PND AND pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID=4 ".$critere." ) AS NBRE FROM axe_intervention_pnd WHERE 1   GROUP BY axe_intervention_pnd.ID_AXE_INTERVENTION_PND,axe_intervention_pnd.DESCR_AXE_INTERVATION_PND";  
  $nbr_pillier4=$this->ModelPs->getRequete('CALL getTable("'.$pillier4.'")'); 
  $data_project4="";
  $data_total4=0;
  foreach ($nbr_pillier4 as $key) {
    $get_exec=($key->NBRE)?$key->NBRE:'0';
    $px=0;
    if ($nbre_tot['TOT']>0)
    {
      $px=($get_exec/$nbre_tot['TOT'])*100;
    }
    $data_total4=$data_total4+$get_exec;
    $data_project4.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key2:4,key:'".$key->ID."'},";
  }
  $data_total=$data_total2+$data_total3+$data_total4;
     //Scripts js hight chart
  $rapp="<script type=\"text/javascript\">
  Highcharts.chart('container1',{

    chart: {
      type: 'column'
      },
      title: {
        text: '<b>".lang("messages_lang.pip_rapport_axes_intervention")." :: ".number_format($data_total,0,'',' ')." BIF </b>',
        },  
        subtitle: {
          text: '<b></b>',
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
                '<td style=\"padding:0\"><b>{point.y:.f} </b></td></tr>',
                footerFormat: '</table>',
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
                          $(\"#titre\").html(\"".lang("messages_lang.pip_pilier_list")." \");
                          $(\"#myModal\").modal('show');
                          var row_count ='1000000';
                          $(\"#mytable\").DataTable({
                            \"processing\":true,
                            \"serverSide\":true,
                            \"bDestroy\": true,
                            \"oreder\":[],
                            \"ajax\":{
                              url:\"".base_url('dashboard/Rapport_Projet_Intervention/detail_intervention/')."\",
                              type:\"POST\",
                              data:{
                                key:this.key,
                                key2:this.key2,         
                                INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                ID_AXE_INTERVENTION_PND:$('#ID_AXE_INTERVENTION_PND').val(),
                              }
                              },
                              lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
                              pageLength: 10,
                              \"columnDefs\":[{
                                \"targets\":[],
                                \"orderable\":false
                                }],
                                dom: 'Bfrtlip',
                                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
                                format: '{point.y:,.1f} %'
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
                                  colorByPoint: false,
                                  name:'2024 - 2025 :: ".number_format($data_total2,0,'',' ')."',
                                  data: [".$data_project2."]
                                  },
                                  {
                                    colorByPoint: false,
                                    name:'2025 - 2026 :: ".number_format($data_total3,0,'',' ')."',
                                    data: [".$data_project3."]
                                    },
                                    {
                                      colorByPoint: false,
                                      name:'2026 - 2027 :: ".number_format($data_total4,0,'',' ')."',
                                      data: [".$data_project4."]
                                    }
                                    ]
                                    });
                                    </script>
                                    ";
                                    echo json_encode(array('rapp'=>$rapp));
                                  }
//listing 
                                  public function listing($value = 0)
                                  {
                                    $critere="";
                                    $ID_AXE_INTERVENTION_PND=$this->request->getPost('ID_AXE_INTERVENTION_PND');
                                    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                                    if (!empty($INSTITUTION_ID)) {
                                     $critere.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
                                   }
                                   if (!empty($ID_AXE_INTERVENTION_PND)) {
                                     $critere.=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND='".$ID_AXE_INTERVENTION_PND."'";
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
                                  $order_by = '';
                                  $order_column = array('','DESCR_AXE_INTERVATION_PND','NOM_PROJET','DESCRIPTION_INSTITUTION','DESCR_STATUT_PROJET','DESCR_PILIER','MONTANT_NOMENCALTURE');
                                  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ANNEE_DESCRIPTION ASC';
                                  $search = !empty($_POST['search']['value']) ? (' AND (NOM_PROJET LIKE "%' . $var_search . '%"  OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%" OR DESCR_OBJECTIF_STRATEGIC LIKE "%' . $var_search . '%" OR DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%")') : '';
                                  // Condition pour la requête principaleDESCR_AXE_INTERVATION_PND
                                  $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
                                 // Condition pour la requête de filtre
                                $conditionsfilter = $critaire .' ' . $search . ' ' . $group;
                                $requetedebase = 'SELECT pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,pip_cadre_mesure_resultat_livrable.TOTAL_DURE_PROJET, pip_cadre_mesure_resultat_livrable.TOTAL_TRIENNAL,DESCR_AXE_INTERVATION_PND,annee_budgetaire.ANNEE_DESCRIPTION, pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID LEFT JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND WHERE 1 '.$critere.'';
                                  $requetedebase = str_replace("'", "\'", $requetedebase);
                                  $requetedebases = $requetedebase . ' ' . $conditions;
                                  $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
                                  $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
                                  $fetch_projets = $this->ModelPs->datatable($query_secondaire);
                                  $data = array();
                                  $u = 1;
                                  $stat ='';
                                  foreach ($fetch_projets as $row)
                                  {
                                    $sub_array = array();
                                    $sub_array[] = $u++;
                                    if (strlen($row->DESCR_AXE_INTERVATION_PND) > 12) {
                                      $sub_array[] = mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a>';
                                    }else{
                                      $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_AXE_INTERVATION_PND.'</label></font>';
                                    }
                                    if (strlen($row->NOM_PROJET) >10)
                                    {
                                      $sub_array[] = mb_substr($row->NOM_PROJET, 0, 10) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
                                    }else{
                                      $sub_array[] ='<font color="#000000" ><label>'.$row->NOM_PROJET.'</label></font>';
                                    }
                                    if (strlen($row->DESCRIPTION_INSTITUTION) > 12) {
                                      $sub_array[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
                                    }else{
                                      $sub_array[] ='<font color="#000000" ><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
                                    }
                                    $sub_array[] ='<font color="#000000" ><label>'.$row->ANNEE_DESCRIPTION.'</label></font>';
                                    $sub_array[] ='<center><font color="#000000" ><label>'.number_format($row->MONTANT_NOMENCALTURE,0,'',' ').' BIF</label></font> </center>';
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
                                  return $this->response->setJSON($output);
                                }
                                public function get_lieux()
                                {
                                  $critere="";
                                  $id=$this->request->getPost('id');
                                  if ($id > 0) {
                                   $critere=" AND ID_DEMANDE_INFO_SUPP=".$id;
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
                                $order_by = '';
                                $order_column = array('','PROVINCE_NAME','COMMUNE_NAME');
                                $search = !empty($_POST['search']['value']) ? (' AND (PROVINCE_NAME LIKE "%' . $var_search . '%" OR COMMUNE_NAME LIKE "%' . $var_search . '%")') : '';
                                // Condition pour la requête principale
                                $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
                            // Condition pour la requête de filtre
                                $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
                                $requetedebase = 'SELECT provinces.PROVINCE_NAME,communes.COMMUNE_NAME, `ID_DEMANDE_INFO_SUPP` FROM `pip_lieu_intervention_projet` lieu JOIN provinces ON provinces.PROVINCE_ID= lieu.`ID_PROVINCE` JOIN communes ON communes.COMMUNE_ID=lieu.`ID_COMMUNE` WHERE 1 '.$critere.'';
                                $requetedebases = $requetedebase . ' ' . $conditions;
                                $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
                                $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
                                $fetch_projets = $this->ModelPs->datatable($query_secondaire);
                                $data = array();
                                $u = 1;
                                $stat ='';
                                foreach ($fetch_projets as $row)
                                {
                                  $sub_array = array();
                                  $sub_array[] = $u++;
                                  $sub_array[] = $row->PROVINCE_NAME;
                                  $sub_array[] = $row->COMMUNE_NAME;
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
                                return $this->response->setJSON($output);
                              }
                             //Details du rapport graphique
                              public function detail_intervention()
                              {
                                $data=$this->urichk();
                                $db=db_connect(); 
                                $session  = \Config\Services::session();
                                $cond="";
                                $KEY=$this->request->getPost('key');
                                $KEY2=$this->request->getPost('key2');
                                $ID_AXE_INTERVENTION_PND=$this->request->getPost('ID_AXE_INTERVENTION_PND');
                                $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                                $cond=" AND annee_budgetaire.ANNEE_BUDGETAIRE_ID=".$KEY2;
                                if (!empty($INSTITUTION_ID)) {
                                 $cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
                               }
                               if (!empty($ID_AXE_INTERVENTION_PND)) {
                                 $cond.=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND='".$ID_AXE_INTERVENTION_PND."'";
                               }
                               $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
                               $query_principal="SELECT pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,pip_cadre_mesure_resultat_livrable.TOTAL_DURE_PROJET, pip_cadre_mesure_resultat_livrable.TOTAL_TRIENNAL,DESCR_AXE_INTERVATION_PND,annee_budgetaire.ANNEE_DESCRIPTION, pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID LEFT JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND WHERE 1 ".$cond."  ";
                               $limit='LIMIT 0,10';
                               if($_POST['length'] != -1)
                               {
                                $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
                              }
                              $order_by='';
                              if($_POST['order']['0']['column']!=0)
                              {
                               $order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY NOM_PROJET  ASC'; 
                             }
                             $search = !empty($_POST['search']['value']) ? ("AND ( NOM_PROJET LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESCR_AXE_INTERVATION_PND LIKE '%$var_search%')") : '';
                             $critere=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND=".$KEY;
                             $conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
                             $query_filter=$query_principal.' '.$critere.'  '.$search;
                             $query_secondaire = 'CALL `getTable`("' . $conditions . '");';
                             $fetch_data = $this->ModelPs->datatable($query_secondaire);
                             $u=0;
                             $data = array();
                             foreach ($fetch_data as $row) 
                             {
                              $u++;
                              $executio=array();
                              $executio[] ='<font color="#000000" size=2><label>'.$u.'</label></font>';
                              if (strlen($row->DESCR_AXE_INTERVATION_PND) > 30){
                                $executio[] = mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 28) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a>';
                              }else{
                                $executio[] ='<font color="#000000"><label>'.$row->DESCR_AXE_INTERVATION_PND.'</label></font>';
                              }
                              if (strlen($row->NOM_PROJET) > 30){
                                $executio[] = mb_substr($row->NOM_PROJET, 0, 28) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
                              }else{
                                $executio[] ='<font color="#000000"><label>'.$row->NOM_PROJET.'</label></font>';
                              }
                              if (strlen($row->DESCRIPTION_INSTITUTION) > 30){
                                $executio[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 28) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
                              }else{
                                $executio[] ='<font color="#000000"><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
                              }
                              $executio[]='<font color="#000000"><label>'.$row->ANNEE_DESCRIPTION.'</label></font>';
                              $executio[]='<font color="#000000"><label>'.number_format($row->MONTANT_NOMENCALTURE,0,'',' ').' BIF</label></font>';
                              $data[]=$executio;        
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
                         //Fonction pour gérer les caractères speciaux
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

