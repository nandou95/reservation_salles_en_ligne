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
/* Rapport Repartition des projets selon les axes d'intervention detail
* emery@mediabox.bi
* le 04/02/2024
*/ 
##Appel de l'espace de nom du Controllers Rapport_Pip_Institution
class Rapport_Pip_Programme_Budgetaire extends BaseController
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

    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE')!=1)
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
    $data['annees'] = $this->get_annee_pip();
    return view('App\Modules\dashboard\Views\Rapport_Pip_Programme_Budgetaire_View',$data);
  }

  //listing
  public function listing($value = 0)
  {
    $critere="";
    $critere1="";
    $ID_AXE_INTERVENTION_PND=$this->request->getPost('ID_AXE_INTERVENTION_PND');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    if (!empty($INSTITUTION_ID))
    {
      $critere.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($ID_AXE_INTERVENTION_PND)) 
    {
      $critere1.=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND=".$ID_AXE_INTERVENTION_PND;
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

    $search = !empty($_POST['search']['value']) ? (' AND (NOM_PROJET LIKE "%' . $var_search . '%" OR DESCR_PILIER LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR NOM_PROJET LIKE "%' . $var_search . '%" OR DESCR_PILIER LIKE "%' . $var_search . '%" OR DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principaleDESCR_AXE_INTERVATION_PND
    $conditions = $critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;

    // Condition pour la requête de filtre
    $conditionsfilter = $critaire.' '.$search.' '.$group;

    $requetedebase = "SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,inst_institutions.CODE_INSTITUTION,programme_pnd.DESCR_PROGRAMME,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND,pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,inst_institutions_programmes.INTITULE_PROGRAMME FROM `pip_demande_infos_supp`  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID   JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE  JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET  JOIN pilier on pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=pip_demande_infos_supp.ID_PROGRAMME_PND  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$critere." ".$critere1."";

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
      $where = "ID_DEMANDE_INFO_SUPP='" .$row->ID_DEMANDE_INFO_SUPP. "'";
      $orderby = 'ANNEE_BUDGETAIRE_ID ASC';
      $where = str_replace("\'", "'", $where);
      $db = db_connect();
      $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      $bindparams34 = str_replace("\'", "'", $bindparamss);
      $livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
      $bailleur_one="SELECT pip_source_financement_bailleur.NOM_SOURCE_FINANCE,`TOTAl_BIF` FROM `pip_demande_source_financement` JOIN pip_source_financement_bailleur on pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR=pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
      $bailleurs=$this->ModelPs->getRequete('CALL getTable("'.$bailleur_one.'")'); 
      $baille=array();
      foreach ($bailleurs as $baill) 
      {
        $baille[] ='-'.$baill->NOM_SOURCE_FINANCE."</br>";
      }
      $baillee = implode(" ", $baille);
      $callpsreq= "CALL getRequete(?,?,?,?);";
      $table1 = "pip_demande_livrable";
      $columnselect1 = "DESCR_LIVRABLE";
      $where1 = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
      $orderby1 = 'ID_DEMANDE_INFO_SUPP ASC';
      $where1 = str_replace("\'", "'", $where1);
      $db= db_connect();
      $bindparamss1 = [$db->escapeString($columnselect1), $db->escapeString($table1), $db->escapeString($where1), $db->escapeString($orderby1)];
      $bindparams341 = str_replace("\'", "'", $bindparamss1);
      $livrable1 = $this->ModelPs->getRequete($callpsreq, $bindparams341);
      $live = array();
      foreach ($livrable1 as $liv) 
      {
        $live[] ='-'.$liv->DESCR_LIVRABLE ."</br>";
      }
      $lives = implode(" ", $live);
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $table_anne = " pip_demande_source_financement_valeur_cible JOIN pip_demande_source_financement ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT";
      $columnselect_anne = "ANNEE_BUDGETAIRE_ID,SOURCE_FINANCEMENT_VALEUR_CIBLE,pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT";
      $where_anne = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
      $orderby_anne = 'pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT ASC';
      $where_anne = str_replace("\'", "'", $where_anne);
      $db = db_connect();
      $bindparams34_anne= [$db->escapeString($columnselect_anne), $db->escapeString($table_anne), $db->escapeString($where_anne), $db->escapeString($orderby_anne)];
      $bindparams3411 = str_replace("\'", "'", $bindparams34_anne);
      $valeur_cible_anne = $this->ModelPs->getRequete($callpsreq, $bindparams34_anne);
      if(strlen($row->DESCRIPTION_INSTITUTION) > 16)
      {
        $sub_array[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 15) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
      }
      if(strlen($row->NOM_PROJET) >15)
      {
        $sub_array[] = mb_substr($row->NOM_PROJET, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->NOM_PROJET.'</label></font>';
      }
      if(strlen($row->DESCR_STATUT_PROJET) > 12)
      {
        $sub_array[] = mb_substr($row->DESCR_STATUT_PROJET, 0, 11) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_STATUT_PROJET.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_STATUT_PROJET.'</label></font>';
      } 
      if(strlen($row->DESCR_PILIER) >15)
      {
        $sub_array[] = mb_substr($row->DESCR_PILIER, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_PILIER.'</label></font>';
      }
      if(strlen($row->INTITULE_PROGRAMME) >15)
      {
        $sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->INTITULE_PROGRAMME.'</label></font>';
      }
      if(strlen($row->DESCR_PROGRAMME) >15)
      {
        $sub_array[] = mb_substr($row->DESCR_PROGRAMME, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_PROGRAMME.'</label></font>';
      }
      if(strlen($row->DESCR_AXE_INTERVATION_PND) >15)
      {
        $sub_array[] = mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_AXE_INTERVATION_PND.'</label></font>';
      }
      if(strlen($row->DESCR_OBJECTIF_STRATEGIC) >15)
      {
        $sub_array[] = mb_substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_OBJECTIF_STRATEGIC.'</label></font>';
      }
      $anne1 = 0;
      $anne2 = 0;
      $anne3 = 0;
      $anne11 = 0;
      $anne21 = 0;
      $anne31 = 0;
      $projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
      $projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
      $get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
      $projet_source="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID JOIN pip_source_financement_bailleur on pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR=pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
      $projets_source=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_source.'")');
      $get_finance = !empty($projets_source['TAUX']) ? $projets_source['TAUX'] : '0';
      $mesure_one="SELECT unite_mesure.`UNITE_MESURE`,pip_indicateur_mesure.INDICATEUR_MESURE FROM `unite_mesure` JOIN pip_cadre_mesure_resultat_livrable ON  pip_cadre_mesure_resultat_livrable.ID_UNITE_MESURE=unite_mesure.ID_UNITE_MESURE JOIN pip_indicateur_mesure ON pip_indicateur_mesure.ID_INDICATEUR_MESURE=pip_cadre_mesure_resultat_livrable.ID_INDICATEUR_MESURE  WHERE  pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
      $mesure=$this->ModelPs->getRequeteOne('CALL getTable("'.$mesure_one.'")'); 
      if (isset($valeur_cible_anne[0]))
      {
        $anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
        $anne11 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_finance;
      }
      if (isset($valeur_cible_anne[1]))
      {
        $anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
        $anne21 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_finance;
      }
      if (isset($valeur_cible_anne[2]))
      {
        $anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
        $anne31 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_finance;
      }
       ####### financement apports par annes
      $sub_array[] = number_format($anne1, '0', ',', ' ');
      $sub_array[] = number_format($anne2, '0', ',', ' ');
      $sub_array[] = number_format($anne3, '0', ',', ' ');
      $sub_array[] = number_format($anne11, '0', ',', ' ');
      $sub_array[] = number_format($anne21, '0', ',', ' ');
      $sub_array[] = number_format($anne31, '0', ',', ' ');
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
   $pillier="SELECT inst_institutions.INSTITUTION_ID AS ID,inst_institutions.DESCRIPTION_INSTITUTION AS NAME,inst_institutions.CODE_INSTITUTION,SUM(`TOTAl_BIF`) AS NBRE  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1  AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  AND pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID!=1 ".$critere."  GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION ORDER BY inst_institutions.CODE_INSTITUTION ASC"; 
   $nbr_pillier=$this->ModelPs->getRequete('CALL getTable("'.$pillier.'")'); 
   $pillier_tot="SELECT SUM(`TOTAl_BIF`) AS TOT  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID WHERE 1  AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  AND pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID!=1 ".$critere." ";  
   $nbre_tot=$this->ModelPs->getRequeteOne('CALL getTable("'.$pillier_tot.'")'); 
   $data_project="";
   $data_total=0;
   foreach ($nbr_pillier as $key) {
     $get_exec=($key->NBRE)?$key->NBRE:'0';
     $px=0;
     if ($nbre_tot['TOT']>0) {
      $px=($get_exec/$nbre_tot['TOT'])*100;
    }
    $data_total=$data_total+$get_exec;
    $data_project.="{name:'".$this->str_replacecatego(trim($key->NAME))." : ".number_format($get_exec,0,'.',' ')."  ',y:".$px.",key:'".$key->ID."'},";
  }
  //Scripts js hight chart
  $rapp="<script type=\"text/javascript\">
  Highcharts.chart('container1', {
    chart: {
      type: 'column'
      },
      title: {
        text: '<b>".lang("messages_lang.pip_rapport_programme_budge")." :: ".number_format($data_total,0,'',' ')." BIF</b>',
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
                pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name} soit </td>' +
                '<td style=\"padding:0\"><b>{point.y:.3f} %</b></td></tr>',
                footerFormat: '</table>',
                shared: false,
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
                         $(\"#titre\").html(\"".lang("messages_lang.pip_pilier_list")." \"+this.name);

                         var row_count ='1000000';
                         $(\"...#mytable\").DataTable({
                          \"processing\":true,
                          \"serverSide\":true,
                          \"bDestroy\": true,
                          \"oreder\":[],
                          \"ajax\":{
                            url:\"".base_url('dashboard/Rapport_Pip_Institution/detail_inst')."\",
                            type:\"POST\",
                            data:{
                              key:this.key,         
                              INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                              ID_AXE_INTERVENTION_PND:$('#ID_AXE_INTERVENTION_PND').val()
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
                              format: '{point.y:,.3f} %'
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
                                name:'Répartition',
                                data: [".$data_project."]
                              }
                              ]
                              });
                              </script>
                              ";
                           echo json_encode(array('rapp'=>$rapp));
                            }
   //function pour exporter le Rapport de suivie evaluation dans excel
                            function exporter($INSTITUTION_ID,$ID_AXE_INTERVENTION_PND)
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
                          $getRequete="SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,programme_pnd.DESCR_PROGRAMME,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND,pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,inst_institutions_programmes.INTITULE_PROGRAMME FROM `pip_demande_infos_supp`  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID   JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE  JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET  JOIN pilier on pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=pip_demande_infos_supp.ID_PROGRAMME_PND  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$criteres."  ";
                          $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");

                          $spreadsheet = new Spreadsheet();
                          $sheet = $spreadsheet->getActiveSheet();
                          $sheet->setCellValue('A1', 'INSTITUTION');
                          $sheet->setCellValue('B1', 'PROJET');
                          $sheet->setCellValue('C1', 'STATUT DU PROJET');
                          $sheet->setCellValue('D1', 'PILIER');
                          $sheet->setCellValue('E1', 'PROGRAMME BUDGETAIRE');
                          $sheet->setCellValue('F1', 'PROGRAMMES PRIORITAIRES DU PND');
                          $sheet->setCellValue('G1', 'AXES INTERVENTIONS');
                          $sheet->setCellValue('H1', 'OBJECTIFS STRATEGIQUE');
                          $sheet->setCellValue('I1', 'BUDGET 2024-2025');
                          $sheet->setCellValue('J1', 'BUDGET 2025-2026');
                          $sheet->setCellValue('K1', 'BUDGET 2026-2027');
                          $sheet->setCellValue('L1', 'FINANCEMENT APPORTE 2024-2025');
                          $sheet->setCellValue('M1', 'FINANCEMENT APPORTE 2025-2026');
                          $sheet->setCellValue('N1', 'FINANCEMENT APPORTE 2026-2027');
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
                            $bailleur_one="SELECT pip_source_financement_bailleur.NOM_SOURCE_FINANCE,`TOTAl_BIF` FROM `pip_demande_source_financement` JOIN pip_source_financement_bailleur on pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR=pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                            $bailleurs=$this->ModelPs->getRequete('CALL getTable("'.$bailleur_one.'")'); 

                            $baille=array();

                            foreach ($bailleurs as $baill) 
                            {
                              $baille[] =$baill->NOM_SOURCE_FINANCE." ".",";
                            }
                            $baillee = implode(" ", $baille);
                            $callpsreq= "CALL getRequete(?,?,?,?);";
                            $table1 = "pip_demande_livrable";
                            $columnselect1 = "DESCR_LIVRABLE";
                            $where1 = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
                            $orderby1 = 'ID_DEMANDE_INFO_SUPP ASC';
                            $where1 = str_replace("\'", "'", $where1);
                            $db= db_connect();
                            $bindparamss1 = [$db->escapeString($columnselect1), $db->escapeString($table1), $db->escapeString($where1), $db->escapeString($orderby1)];
                            $bindparams341 = str_replace("\'", "'", $bindparamss1);
                            $livrable1 = $this->ModelPs->getRequete($callpsreq, $bindparams341);
                            $live = array();
                            foreach ($livrable1 as $liv) 
                            {
                              $live[] =$liv->DESCR_LIVRABLE." ".",";
                            }
                            $lives = implode(" ", $live);
                            $projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                            $projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
                            $get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
                            $projet_source="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID JOIN pip_source_financement_bailleur on pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR=pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                            $projets_source=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_source.'")');
                            $get_finance = !empty($projets_source['TAUX']) ? $projets_source['TAUX'] : '0';
                            $mesure_one="SELECT unite_mesure.`UNITE_MESURE`,pip_indicateur_mesure.INDICATEUR_MESURE FROM `unite_mesure` JOIN pip_cadre_mesure_resultat_livrable ON  pip_cadre_mesure_resultat_livrable.ID_UNITE_MESURE=unite_mesure.ID_UNITE_MESURE JOIN pip_indicateur_mesure ON pip_indicateur_mesure.ID_INDICATEUR_MESURE=pip_cadre_mesure_resultat_livrable.ID_INDICATEUR_MESURE  WHERE  pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
                            $mesure=$this->ModelPs->getRequeteOne('CALL getTable("'.$mesure_one.'")'); 
                            $anne1 = 0;
                            $anne2 = 0;
                            $anne3 = 0;
                            $anne11 = 0;
                            $anne21 = 0;
                            $anne31 = 0;
                            if (isset($valeur_cible_anne[0]))
                            {
                             $anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                             $anne11 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_finance;
                           }

                           if (isset($valeur_cible_anne[1]))
                           {
                             $anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                             $anne21 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_finance;
                           }
                           if (isset($valeur_cible_anne[2]))
                           {
                             $anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
                             $anne31 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE *  $get_finance;
                           }
                           $sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
                           $sheet->setCellValue('B' . $rows, $key->NOM_PROJET);
                           $sheet->setCellValue('C' . $rows, $key->DESCR_STATUT_PROJET);
                           $sheet->setCellValue('D' . $rows, $key->DESCR_PILIER);
                           $sheet->setCellValue('E' . $rows, $key->INTITULE_PROGRAMME);
                           $sheet->setCellValue('F' . $rows, $key->DESCR_PROGRAMME);
                           $sheet->setCellValue('G' . $rows, $key->DESCR_AXE_INTERVATION_PND);
                           $sheet->setCellValue('H' . $rows, $key->DESCR_OBJECTIF_STRATEGIC);
                           $sheet->setCellValue('I' . $rows, $anne1);
                           $sheet->setCellValue('J' . $rows, $anne2);
                           $sheet->setCellValue('K' . $rows, $anne3);
                           $sheet->setCellValue('L' . $rows, $anne11);
                           $sheet->setCellValue('M' . $rows, $anne21);
                           $sheet->setCellValue('N' . $rows, $anne31);
                           $rows++;
                         } 
                         $writer = new Xlsx($spreadsheet);
                         $writer->save('world.xlsx');
                         return $this->response->download('world.xlsx', null)->setFileName('PIP par programmes budgetaire.xlsx');
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

