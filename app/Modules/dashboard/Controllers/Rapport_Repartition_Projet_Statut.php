<?php
namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use App\Controllers\Login_Ptba;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/* Rapport Repartition des projets par ministère selon le statut des projets
* claude@mediabox.bi
* le 30/11/2023
*/ 
//Appel de l'espace de nom du Controllers Rapport_Repartition_Projet_Statut
class Rapport_Repartition_Projet_Statut extends BaseController
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
    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_STATUT_PROJET')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $requete_inst=("SELECT inst_institutions.`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION` FROM `inst_institutions` WHERE 1 ");
    $date="date_format(DATE_DEBUT_PROJET,'%Y')";
    $req='SELECT `ANNEE_BUDGETAIRE_ID`,`ANNEE_DESCRIPTION` FROM `annee_budgetaire` WHERE 1';
    $data['annees']=$this->ModelPs->getRequete('CALL getTable("'.$req.'")');
    $data['INSTITUTION_ID1']=0;
    $data['institutions']=$this->ModelPs->getRequete('CALL getTable("'.$requete_inst.'")');
    $data['INSTITUTION_ID']=$this->request->getPost('');
    $data['ANNEE_BUDGETAIRE_ID']=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
    return view('App\Modules\dashboard\Views\Rapport_Repartition_Projet_Statut_View',$data);
  }
 //Fonction pour appel des series et hichart & gestion des filtres
  public function get_rapport()
  {
   $data=$this->urichk();
   $db = db_connect(); 
   $session  = \Config\Services::session();
   $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
   $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
   $critere="";
   if (!empty($INSTITUTION_ID)) {
     $critere.=" AND pip.INSTITUTION_ID=".$INSTITUTION_ID;
   }
   if (!empty($ANNEE_BUDGETAIRE_ID))
   {
     $critere.=" AND cadre_mesure_resultat_valeur_cible.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
   }
   $projet_Statut="SELECT statut.`ID_STATUT_PROJET` AS ID,statut.`DESCR_STATUT_PROJET` AS NAME,COUNT(DISTINCT pip.ID_DEMANDE_INFO_SUPP) AS NBRE FROM pip_statut_projet statut LEFT JOIN pip_demande_infos_supp pip ON statut.ID_STATUT_PROJET=pip.ID_STATUT_PROJET LEFT JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=pip.ID_DEMANDE_INFO_SUPP LEFT JOIN cadre_mesure_resultat_valeur_cible ON cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE WHERE 1 ".$critere."  GROUP BY statut.`ID_STATUT_PROJET`,statut.`DESCR_STATUT_PROJET`";
   $projet_Statuts=$this->ModelPs->getRequete('CALL getTable("'.$projet_Statut.'")');
   // total utilisé pour le calcul du pourcentage 
   $tot="SELECT COUNT(DISTINCT pip.ID_DEMANDE_INFO_SUPP) AS NBRE FROM pip_statut_projet statut LEFT JOIN pip_demande_infos_supp pip ON statut.ID_STATUT_PROJET=pip.ID_STATUT_PROJET LEFT JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=pip.ID_DEMANDE_INFO_SUPP LEFT JOIN cadre_mesure_resultat_valeur_cible ON cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE WHERE 1 ".$critere."";
   $total=$this->ModelPs->getRequeteOne('CALL getTable("'.$tot.'")');
   $data_project="";
   $data_total=0;
 // print_r($projet_Statuts);die();
   foreach ($projet_Statuts as $key) {
    $get_exec=($key->NBRE)?$key->NBRE:'0';
    $px=0;
    if ($total['NBRE']) {
     $px=($get_exec/$total['NBRE'])*100;
        }
   $data_total=$data_total+$get_exec;
   $data_project.="{name:'".$this->str_replacecatego(trim($key->NAME))." : ".number_format($px,1,'.',' ')." % ',y:".$get_exec.",key:'".$key->ID."'},";
     }
     //Scripts js hight chart
 $rapp="<script type=\"text/javascript\">
 Highcharts.chart('container1', {

  chart: {
    type: 'column'
    },
    title: {
      text: '<b>".lang("messages_lang.pip_rapport_institutio_statut")." :: ".number_format($data_total,0,'.',' ')."</b>',
      },  
      subtitle: {
        text: '',
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
                            url:\"".base_url('dashboard/Rapport_Repartition_Projet_Statut/detail_statut/')."\",
                            type:\"POST\",
                            data:{
                              key:this.key,
                              INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                              annee:$('#annee').val()
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
                              format: '{point.y:,.0f}'
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
                                color:'#67F3CF',
                                data: [".$data_project."]
                              }
                              ]
                              });
                              </script>
                              ";
                              echo json_encode(array('rapp'=>$rapp));
                            }
                            public function listing($value = 0)
                            {
                              $critere="";
                              $critere1="";
                              $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
                              $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                              if (!empty($INSTITUTION_ID)) {
                               $critere.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
                             }
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
                            $order_column = array('inst_institutions.CODE_INSTITUTION','DESCR_AXE_INTERVATION_PND','NOM_PROJET','DESCRIPTION_INSTITUTION','DESCR_STATUT_PROJET','DESCR_PILIER','');
                            $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.CODE_INSTITUTION ASC';
                            $search = !empty($_POST['search']['value']) ? (' AND (NOM_PROJET LIKE "%' . $var_search . '%" OR ID_DEMANDE_INFO_SUPP LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR NOM_PROJET LIKE "%' . $var_search . '%" OR ID_DEMANDE_INFO_SUPP LIKE "%' . $var_search . '%" OR DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principaleDESCR_AXE_INTERVATION_PND
                            $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
                            $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
                            $requetedebase = 'SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND,pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,inst_institutions_programmes.INTITULE_PROGRAMME FROM `pip_demande_infos_supp`  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID   JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE  JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET  JOIN pilier on pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  '.$critere.' '.$critere1.'';
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
                              $callpsreq = "CALL `getRequete`(?,?,?,?);";
                              $table = "pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID";
                              $columnselect = " pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID, annee_budgetaire.ANNEE_DESCRIPTION, pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE";
                              $where = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
                              $orderby = 'ANNEE_BUDGETAIRE_ID ASC';
                              $where = str_replace("\'", "'", $where);
                              $db = db_connect();
                              $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
                              $bindparams34 = str_replace("\'", "'", $bindparamss);
                              $livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
                              $table_anne = " pip_demande_source_financement_valeur_cible JOIN pip_demande_source_financement ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT";
                              $columnselect_anne = "ANNEE_BUDGETAIRE_ID,SOURCE_FINANCEMENT_VALEUR_CIBLE,pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT";
                              $where_anne = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
                              $orderby_anne = 'pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT ASC';
                              $where_anne = str_replace("\'", "'", $where_anne);
                              $db = db_connect();
                              $bindparams34_anne= [$db->escapeString($columnselect_anne), $db->escapeString($table_anne), $db->escapeString($where_anne), $db->escapeString($orderby_anne)];
                              $bindparams3411 = str_replace("\'", "'", $bindparams34_anne);
                              $valeur_cible_anne = $this->ModelPs->getRequete($callpsreq, $bindparams34_anne);
                              if (strlen($row->DESCRIPTION_INSTITUTION) > 16) {
                                $sub_array[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 15) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
                              }else{
                                $sub_array[] ='<font color="#000000" ><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
                              }
                              if (strlen($row->NOM_PROJET) >15) {
                               $sub_array[] = mb_substr($row->NOM_PROJET, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
                             }else{
                              $sub_array[] ='<font color="#000000" ><label>'.$row->NOM_PROJET.'</label></font>';
                            }
                            if (strlen($row->DESCR_STATUT_PROJET) > 12) {
                              $sub_array[] = mb_substr($row->DESCR_STATUT_PROJET, 0, 11) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_STATUT_PROJET.'"><i class="fa fa-eye"></i></a>';
                            }else{
                             $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_STATUT_PROJET.'</label></font>';

                           }
                           $anne1 = 0;
                           $anne2 = 0;
                           $anne3 = 0;
                           $projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
                           $projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
                           $get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';

                           if (isset($valeur_cible_anne[0]))
                           {
                            $anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                          }
                          if (isset($valeur_cible_anne[1]))
                          {
                            $anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                          }
                          if (isset($valeur_cible_anne[2]))
                          {
                            $anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                          }
                          $sub_array[] = number_format($anne1, '0', ',', ' ');
                          $sub_array[] = number_format($anne2, '0', ',', ' ');
                          $sub_array[] = number_format($anne3, '0', ',', ' ');
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

   //function pour exporter le Rapport de suivie evaluation dans excel
                      function exporter($INSTITUTION_ID)
                      {
                        $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
                        if(empty($USER_IDD))
                        {
                          return redirect('Login_Ptba/do_logout');
                        }
                        $db = db_connect();
                        $callpsreq = "CALL getRequete(?,?,?,?);";
                        $criteres="";
                        if(!empty($INSTITUTION_ID))
                        {
                          $criteres.=" AND  pip_demande_infos_supp.INSTITUTION_ID= ".$INSTITUTION_ID;
                        }
                        $criteres1="";
                        if (!empty($ID_AXE_INTERVENTION_PND)){
                         $criteres.=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND=".$ID_AXE_INTERVENTION_PND;
                       }
                       $getRequete="SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND, pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION FROM `pip_demande_infos_supp`  LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  LEFT JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  ".$criteres." ";
                       $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
      // print_r($getData);die();
                       $spreadsheet = new Spreadsheet();
                       $sheet = $spreadsheet->getActiveSheet();
                       $sheet->setCellValue('A1', 'INSTITUTION');
                       $sheet->setCellValue('B1', 'PROJET');
                       $sheet->setCellValue('C1', 'STATUT DU PROJET');
                       $sheet->setCellValue('D1', '2024-2025');
                       $sheet->setCellValue('E1', '2025-2026');
                       $sheet->setCellValue('F1', '2026-2027');           
                       $rows = 3;
      //boucle pour les institutions 
                       foreach ($getData as $key)
                       {
                         $callpsreq = "CALL `getRequete`(?,?,?,?);";
                         $table = "pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID";
                         $columnselect = " pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID, annee_budgetaire.ANNEE_DESCRIPTION, pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE";
                         $where = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
                         $orderby = 'ANNEE_BUDGETAIRE_ID ASC';
                         $where = str_replace("\'", "'", $where);
                         $db = db_connect();
                         $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
                         $bindparams34 = str_replace("\'", "'", $bindparamss);
                         $livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
                         $table_anne = " pip_demande_source_financement_valeur_cible JOIN pip_demande_source_financement ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT";
                         $columnselect_anne = "ANNEE_BUDGETAIRE_ID,SOURCE_FINANCEMENT_VALEUR_CIBLE,pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT";
                         $where_anne = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
                         $orderby_anne = 'pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT ASC';
                         $where_anne = str_replace("\'", "'", $where_anne);
                         $db = db_connect();
                         $bindparams34_anne= [$db->escapeString($columnselect_anne), $db->escapeString($table_anne), $db->escapeString($where_anne), $db->escapeString($orderby_anne)];
                         $bindparams3411 = str_replace("\'", "'", $bindparams34_anne);
                         $valeur_cible_anne = $this->ModelPs->getRequete($callpsreq, $bindparams34_anne);
                         $anne1 = 0;
                         $anne2 = 0;
                         $anne3 = 0;
                         $projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                         $projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
                         $get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
                         $anne1 = 0;
                         $anne2 = 0;
                         $anne3 = 0;
                         $projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                         $projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
                         $get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';

                         if (isset($valeur_cible_anne[0]))
                         {
                          $anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                        }
                        if (isset($valeur_cible_anne[1]))
                        {
                          $anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                        }
                        if (isset($valeur_cible_anne[2]))
                        {
                          $anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                        }
                        $sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
                        $sheet->setCellValue('B' . $rows, $key->NOM_PROJET);
                        $sheet->setCellValue('C' . $rows, $key->DESCR_STATUT_PROJET);
                        $sheet->setCellValue('E' . $rows, $anne1);
                        $sheet->setCellValue('E' . $rows, $anne2);
                        $sheet->setCellValue('F' . $rows, $anne3);
                        $rows++;
                      } 
                      $writer = new Xlsx($spreadsheet);
                      $writer->save('world.xlsx');
                      return $this->response->download('world.xlsx', null)->setFileName('PIP par Ministère et institution.xlsx');
                      return redirect('dashboard/Rapport_Pip_Institution');
                    }

                    public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
                    {
                      $db = db_connect();
                      $columnselect = str_replace("\'", "'", $columnselect);
                      $table = str_replace("\'", "'", $table);
                      $where = str_replace("\'", "'", $where);
                      $orderby = str_replace("\'", "'", $orderby);
                      $Limit = str_replace("\'", "'", $Limit);
                      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
                      $bindparams = str_replace('\"', '"', $bindparams);
                      return $bindparams;
                    }




                    public function get_lieux()
                    {
                      $critere="";
                      $id=$this->request->getPost('id');

  // print_r($id);die();
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

    // $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY PROVINCE_NAME ASC';


                    $search = !empty($_POST['search']['value']) ? (' AND (PROVINCE_NAME LIKE "%' . $var_search . '%" OR COMMUNE_NAME LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principale
                    $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;

    // Condition pour la requête de filtre
                    $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

                    $requetedebase = 'SELECT provinces.PROVINCE_NAME,communes.COMMUNE_NAME, `ID_DEMANDE_INFO_SUPP` FROM `pip_lieu_intervention_projet` lieu JOIN provinces ON provinces.PROVINCE_ID= lieu.`ID_PROVINCE` JOIN communes ON communes.COMMUNE_ID=lieu.`ID_COMMUNE` WHERE 1 '.$critere.'';


                    $requetedebases = $requetedebase . ' ' . $conditions;
                    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
// print_r($requetedebases);die();
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

                  public function detail_statut()
                  {
                    $data=$this->urichk();
                    $db=db_connect(); 
                    $session  = \Config\Services::session();
                    $cond="";
                    $KEY=$this->request->getPost('key');
                    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');

                    if ($INSTITUTION_ID > 0) {
                     $cond.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
                   }
                   if (!empty($ANNEE_BUDGETAIRE_ID)) {
                    $critere.=" AND cadre_mesure_resultat_valeur_cible.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
                  }



                  $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 

                  $query_principal="SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,inst.DESCRIPTION_INSTITUTION,`NOM_PROJET`,statut.`DESCR_STATUT_PROJET` FROM `pip_demande_infos_supp`  JOIN inst_institutions inst ON inst.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET LEFT JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP LEFT JOIN cadre_mesure_resultat_valeur_cible ON cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE WHERE 1 ".$cond."  ";

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
                 $search = !empty($_POST['search']['value']) ? ("AND ( DESCR_STATUT_PROJET LIKE '%$var_search%' 
                  OR NOM_PROJET LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';


                 $critere=" AND pip_demande_infos_supp.ID_STATUT_PROJET=".$KEY;

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
                  if (strlen($row->NOM_PROJET) > 40){
                    $executio[] = mb_substr($row->NOM_PROJET, 0, 45) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
                  }else{
                    $executio[] ='<font color="#000000"><label>'.$row->NOM_PROJET.'</label></font>';
                  }


                  if (strlen($row->DESCR_STATUT_PROJET) > 15){
                    $executio[] = mb_substr($row->DESCR_STATUT_PROJET, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_STATUT_PROJET.'"><i class="fa fa-eye"></i></a>';
                  }else{
                    $executio[] ='<font color="#000000"><label>'.$row->DESCR_STATUT_PROJET.'</label></font>';
                  }


                  if (strlen($row->DESCRIPTION_INSTITUTION) > 15) {
                    $executio[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
                  }else{
                    $executio[] ='<font color="#000000" ><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
                  }

                  $data[] = $executio;        
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

