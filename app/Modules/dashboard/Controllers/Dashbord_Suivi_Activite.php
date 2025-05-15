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
/* Dashbord_Suivi_Activite suivi des activités
* claude@mediabox.bi
* le 18/12/2023
*/
//Appel de l'espace de nom du Controllers Dashbord_Suivi_Activite
class Dashbord_Suivi_Activite extends BaseController
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
    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_REALISATION_PHYSIQUE')!=1)
    {
     return redirect('Login_Ptba/homepage');
    }
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $requete_inst="SELECT `INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION` FROM `inst_institutions` WHERE 1 ";
    $data['institutions']=$this->ModelPs->getRequete('CALL getTable("'.$requete_inst.'")');
    $data['ann_actuel_id'] = $this->get_annee_budgetaire();
    //Selection de l'année budgétaire
    $get_anne_budget="SELECT ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN FROM `annee_budgetaire` WHERE 1 AND ANNEE_BUDGETAIRE_ID<=".$data['ann_actuel_id']." ORDER BY ANNEE_DEBUT ASC"; 
    $data['anne_budget'] = $this->ModelPs->getRequete('CALL getTable("'.$get_anne_budget.'")');
    return view('App\Modules\dashboard\Views\Dashbord_Suivi_Activite_View',$data);
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

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
        // code...
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }
 //Fonction pour appel des series et hichart & gestion des filtres
    public function get_rapport()
    {
     $requete_faire="";
     $requete_fait="";
     $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
     $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
   
     $critere_program="";
     $title="";
     $categorie="";
     $cond1="";
     $cond22="";
     // if(! empty($ANNEE_BUDGETAIRE_ID))
     //  {
     //   $cond1.=" AND execution_budgetaire.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
     //   $cond22.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
     // }
     if (empty($INSTITUTION_ID)) {
      $title=" Institution";
      $requete_faire="SELECT inst_institutions.INSTITUTION_ID AS CODE,inst_institutions.DESCRIPTION_INSTITUTION AS NAME,COUNT(PTBA_TACHE_ID) AS NBRE FROM `ptba_tache` RIGHT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE 1 ".$cond22." GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION";

      $requete_pourca="SELECT COUNT(PTBA_TACHE_ID) AS NBRE FROM `ptba_tache` RIGHT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID";

      $requete_fait="SELECT inst_institutions.INSTITUTION_ID AS CODE,inst_institutions.DESCRIPTION_INSTITUTION AS NAME,(SELECT COUNT(PTBA_TACHE_ID) FROM `ptba_tache` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE ptba_tache.INSTITUTION_ID=CODE AND  ptba_tache.PTBA_TACHE_ID IN (SELECT DISTINCT PTBA_TACHE_ID FROM execution_budgetaire WHERE 1 ".$cond1.")) AS NBRE,(SELECT COUNT(PTBA_TACHE_ID) FROM `ptba_tache` RIGHT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_tache.INSTITUTION_ID WHERE ptba_tache.INSTITUTION_ID=CODE ".$cond22.") AS NBRE1 FROM  inst_institutions WHERE 1  GROUP BY inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION";

    }else if (!empty($INSTITUTION_ID)) 
    {
    	if (!empty($PROGRAMME_ID)) {
    		$critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
     }
     $title=" Programme";
     $requete_faire="SELECT inst_institutions_programmes.PROGRAMME_ID AS CODE,inst_institutions_programmes.INTITULE_PROGRAMME AS NAME,COUNT(PTBA_TACHE_ID) AS NBRE FROM ptba_tache JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID WHERE inst_institutions_programmes.INSTITUTION_ID=".$INSTITUTION_ID." ".$critere_program." ".$cond22." GROUP BY inst_institutions_programmes.PROGRAMME_ID,inst_institutions_programmes.INTITULE_PROGRAMME";

     $requete_fait="SELECT inst_institutions_programmes.PROGRAMME_ID AS CODE,inst_institutions_programmes.INTITULE_PROGRAMME AS NAME,(SELECT COUNT(PTBA_TACHE_ID) FROM ptba_tache WHERE ptba_tache.PROGRAMME_ID=CODE AND ptba_tache.PTBA_TACHE_ID IN (SELECT DISTINCT PTBA_TACHE_ID FROM execution_budgetaire) ".$critere_program." ) as NBRE,( SELECT COUNT(PTBA_TACHE_ID) FROM ptba_tache WHERE ptba_tache.PROGRAMME_ID=CODE ".$critere_program." ".$cond22.") as NBRE1 FROM inst_institutions_programmes WHERE inst_institutions_programmes.INSTITUTION_ID=".$INSTITUTION_ID."   GROUP BY CODE_PROGRAMME,INTITULE_PROGRAMME";
   }
   $afaire=$this->ModelPs->getRequete(' CALL getTable("'.$requete_faire.'")');
   $data_activite_faire="";
   $total_activite_faire=0;
   foreach($afaire as $value)
     {
    $total_activite_faire=$total_activite_faire+$value->NBRE;
    $name = (!empty($value->NAME)) ? $value->NAME : "Autres";
    $rappel=$this->str_replacecatego($name);
    $categorie.= "'".$rappel."',";
    $data_activite_faire.="{name:'".$this->str_replacecatego($value->NAME)."', y:".$value->NBRE.",key:".$this->str_replacecatego($value->CODE).",key2:1},";
     }
   $data_activite_faite="";
   $total_activite_faite=0;
   $fait=$this->ModelPs->getRequete(' CALL getTable("'.$requete_fait.'")');
   $taux_activite=0;
   $data_activite_faite_taux="";
   $pourca="";
  foreach($fait as $value){
 ##028582
    $pourca="%";
    $total_activite_faite=$total_activite_faite+$value->NBRE;
    $REALI= ($value->NBRE1>0) ? $value->NBRE1: 1 ;
    $taux_activite=($value->NBRE/$REALI)*100;
    $name = (!empty($value->NAME)) ? $value->NAME : "Autres";
    $rappel=$this->str_replacecatego($name);
    $data_activite_faite.="{name:'".$this->str_replacecatego($value->NAME)."', y:".$value->NBRE.",key:".$this->str_replacecatego($value->CODE).",key2:2},";
    $data_activite_faite_taux.="{name:'".$this->str_replacecatego($value->NAME)."', y:".$taux_activite.",key:".$this->str_replacecatego($value->CODE).",key2:2},";
  }
  $rapp="<script type=\"text/javascript\">
  Highcharts.chart('container', {
    chart: {
      type: 'column'
      },
      title: {
        text: '<b>Nombre de tâches par ".$title." ',
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
                       $(\"#titre\").html(\" \" +this.series.name);
                       $(\"#myModal\").modal('show');
                       var row_count ='1000000';
                       $(\"#mytable\").DataTable({
                        \"processing\":true,
                        \"serverSide\":true,
                        \"bDestroy\": true,
                        \"order\":[],
                        \"ajax\":{
                          url:\"".base_url('dashboard/Dashbord_Suivi_Activite/detail_suivi')."\",
                          type:\"POST\",
                          data:{
                            key:this.key,
                            key2:this.key2,
                            INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                            PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                            IS_PRIVATE:$('#IS_PRIVATE').val(),
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


                            series: [
                            {
                              name:'Tâches déjà faites  :: ".number_format($total_activite_faite,0,',',' ')."',
                              data: [".$data_activite_faite."]
                              },
                              {
                                name:'Tâches  à faire:: ".number_format($total_activite_faire,0,',',' ')." ',
                                data: [".$data_activite_faire."]
                              }

                              ]
                              });
                              </script>
                              ";


                              $rapp1="<script type=\"text/javascript\">
                              Highcharts.chart('container1', {

                                chart: {
                                  type: 'column'
                                  },
                                  title: {
                                    text: '<b>Taux de réalisation physique',
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

                                                   $(\"#titre\").html(\" \" +this.series.name);
                                                   $(\"#myModal\").modal('show');
                                                   var row_count ='1000000';
                                                   $(\"#mytable\").DataTable({
                                                    \"processing\":true,
                                                    \"serverSide\":true,
                                                    \"bDestroy\": true,
                                                    \"order\":[],
                                                    \"ajax\":{
                                                      url:\"".base_url('dashboard/Dashbord_Suivi_Activite/detail_suivi')."\",
                                                      type:\"POST\",
                                                      data:{
                                                        key:this.key,
                                                        key2:this.key2,
                                                        INSTITUTION_ID:$('#INSTITUTION_ID').val(),
                                                        PROGRAMME_ID:$('#PROGRAMME_ID').val(),
                                                        IS_PRIVATE:$('#IS_PRIVATE').val(),
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
                                                        format: '{point.y:.1f}%'
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
                                                          name:'Tâches: ".number_format($total_activite_faite,0,',',' ')." ',
                                                          data: [".$data_activite_faite_taux."]
                                                        }

                                                        ]
                                                        });
                                                        </script>
                                                        ";
                                                $program= '<option selected="" disabled="">sélectionner</option>';
                                                 if (!empty($INSTITUTION_ID))
                                                        {
                                                          $program_req='SELECT inst_institutions_programmes.PROGRAMME_ID AS CODE_PROGRAMME,inst_institutions_programmes.INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE inst_institutions_programmes.INSTITUTION_ID='.$INSTITUTION_ID.' ORDER BY INTITULE_PROGRAMME ASC';

                                                          $programs = $this->ModelPs->getRequete('CALL getTable("'.$program_req.'")');
                                                          foreach ($programs as $key)
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
                                                        echo json_encode(array('rapp'=>$rapp,'rapp1'=>$rapp1,'program'=>$program,'total_activite_faire'=>$total_activite_faire,'total_activite_faite'=>$total_activite_faite));
                                                      }



 //listing activites vote
                                                      public function listing($value = 0)
                                                      {
                                                        $critere="";
                                                        $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                                                        $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
                                                       
                                                        $critere_program="";
                                                        if (!empty($INSTITUTION_ID)) {
                                                         $critere.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
                                                         if (!empty($PROGRAMME_ID)) {
                                                          $critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
                                                        }
                                                      }
                                                      $cond="";
                                                      // if(! empty($ANNEE_BUDGETAIRE_ID))
                                                      // {
                                                      //   $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
                                                      // }
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
                                                      $order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','DESC_TACHE','BUDGET_T1','BUDGET_T2','BUDGET_T3','BUDGET_T4');
                                                      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_INSTITUTION ASC';
                                                      $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions_actions.LIBELLE_ACTION LIKE "%' . $var_search . '%" OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%")') : '';
                                                      $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
                                                  $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
                                                  $requetedebase = 'SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 '.$cond.' '.$critere.''.$critere_program.' ';
                                                  $requetedebase = str_replace("'", "\'", $requetedebase);
                                                  $requetedebases = $requetedebase . ' ' . $conditions;
                                                  $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
      // print_r($requetedebases);die();
                                                  $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
                                                  $activites_faire = $this->ModelPs->datatable($query_secondaire);
                                                      $data = array();
                                                      $u = 1;
                                                      $stat ='';
                                                      foreach ($activites_faire as $row)
                                                      {
                                                        $sub_array = array();
                                                        $sub_array[] = $u++;
                                                        if (strlen($row->DESCRIPTION_INSTITUTION) > 20) {
                                                          $sub_array[] = mb_substr($this->str_replacecatego($row->DESCRIPTION_INSTITUTION), 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'"><i class="fa fa-eye"></i></a>';
                                                        }else{
                                                          $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'</label></font>';
                                                        }
                                                        if (strlen($row->INTITULE_PROGRAMME) >20) {
                                                          $sub_array[] = mb_substr($this->str_replacecatego($row->INTITULE_PROGRAMME), 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'"><i class="fa fa-eye"></i></a>';
                                                        }else{
                                                          $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'</label></font>';
                                                        }
                                                        if (strlen($row->LIBELLE_ACTION) > 23) {
                                                          $sub_array[] = mb_substr($this->str_replacecatego($row->LIBELLE_ACTION), 0, 20) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->LIBELLE_ACTION).'"><i class="fa fa-eye"></i></a>';
                                                        }else{
                                                          $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->LIBELLE_ACTION).'</label></font>';
                                                        }
                                                        if (strlen($row->DESC_TACHE) > 26) {
                                                          $sub_array[] = mb_substr($this->str_replacecatego($row->DESC_TACHE), 0, 25) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->DESC_TACHE).'"><i class="fa fa-eye"></i></a>';
                                                        }else{
                                                          $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESC_TACHE).'</label></font>';
                                                        }
                                                        $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T1,0,'',' ').'</label></font>';
                                                        $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T2,0,'',' ').'</label></font>';
                                                        $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T3,0,'',' ').'</label></font>';
                                                        $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T4,0,'',' ').'</label></font>';
                                                        $data[] = $sub_array;
                                                      }
    // print_r($data);die();
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
//listing 2 Activités déjàs faites
          public function listing_deux($value = 0)
                      {
                    $critere="";
                    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
                    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                    // $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
                    $critere_program="";
                    if (!empty($INSTITUTION_ID)) {
                    $critere.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
                          if (!empty($PROGRAMME_ID)) {
                    $critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
                            }
                          }
                    $cond="";
                    $cond2="";
                      // if(! empty($ANNEE_BUDGETAIRE_ID))
                      //   {
                      // $cond.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
                      // $cond2.=" AND execution_budgetaire.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
                      //       }
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
                             $order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','DESC_TACHE','BUDGET_T1','BUDGET_T2','BUDGET_T3','BUDGET_T4');

                             $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_INSTITUTION ASC';

                              $search = !empty($_POST['search']['value']) ? (' AND (DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR DESC_TACHE LIKE "%' . $var_search . '%"  OR inst_institutions_actions.LIBELLE_ACTION LIKE "%' . $var_search . '%"  OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE "%' . $var_search . '%")') : '';

                              $conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
                              $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
                              $requetedebase = 'SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID WHERE ptba_tache.PTBA_TACHE_ID IN (SELECT DISTINCT PTBA_TACHE_ID FROM execution_budgetaire WHERE 1 '.$cond2.' ) '.$critere.' '.$critere_program.' '.$cond.'';
                                $requetedebase = str_replace("'", "\'", $requetedebase);
                                $requetedebases = $requetedebase . ' ' . $conditions;
                                $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
                                $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
                                $activites_faites = $this->ModelPs->datatable($query_secondaire);
                                  $data = array();
                                    $u = 1;
                                    $stat ='';
                                        foreach ($activites_faites as $row)
                                                  {
                                            $sub_array = array();
                                            $sub_array[] = $u++;
                                            if (strlen($row->DESCRIPTION_INSTITUTION) > 20) {
                                            $sub_array[] = mb_substr($this->str_replacecatego($row->DESCRIPTION_INSTITUTION), 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'"><i class="fa fa-eye"></i></a>';
                                                  }else{
                                            $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'</label></font>';
                                                    }
                                                if (strlen($row->INTITULE_PROGRAMME) >20) {
                                              $sub_array[] = mb_substr($this->str_replacecatego($row->INTITULE_PROGRAMME), 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'"><i class="fa fa-eye"></i></a>';
                                                    }else{
                                              $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'</label></font>';
                                                  }
                                                if (strlen($row->LIBELLE_ACTION) > 23) {
                                            $sub_array[] = mb_substr($this->str_replacecatego($row->LIBELLE_ACTION), 0, 20) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->LIBELLE_ACTION).'"><i class="fa fa-eye"></i></a>';
                                                    }else{
                                                      $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->LIBELLE_ACTION).'</label></font>';
                                                    }
                                                  if (strlen($row->DESC_TACHE) > 26) {
                                                 $sub_array[] = mb_substr($this->str_replacecatego($row->DESC_TACHE), 0, 25) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->DESC_TACHE).'"><i class="fa fa-eye"></i></a>';
                                                    }else{
                                                $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESC_TACHE).'</label></font>';
                                                    }
                                                $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T1,0,'',' ').'</label></font>';
                                              $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T2,0,'',' ').'</label></font>';
                                                    $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T3,0,'',' ').'</label></font>';
                                                    $sub_array[] ='<font color="#000000" ><label>'.number_format($row->BUDGET_T4,0,'',' ').'</label></font>';
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
                                              public function detail_suivi()
                                              {
                                              $cond="";
                                              $KEY=$this->request->getPost('key');
                                              $KEY2=$this->request->getPost('key2');
                                              $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
                                              $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
                                              // $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
                                              $critere_program='';
                                              $cond="";
                                              $criteres="";
                                                if (empty($INSTITUTION_ID)) {
                                                $criteres=" AND ptba_tache.INSTITUTION_ID=".$KEY;
                                               }if (!empty($INSTITUTION_ID)) {
                                                $cond.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
                                                $criteres=" AND ptba_tache.PROGRAMME_ID=".$KEY;

                                                 if (!empty($PROGRAMME_ID)) {
                                                $critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
                                                }
                                              }

                                              $cond1="";
                                              $cond2="";
                                             //  if(! empty($ANNEE_BUDGETAIRE_ID))
                                             //  {
                                             //   $cond1.=" AND ptba_tache.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
                                             //   $cond2.=" AND execution_budgetaire.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID.""; 
                                             // }

                                      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
                                      $query_principal="";


                                             if ($KEY2==1){
                                               $query_principal = 'SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 '.$cond.' '.$cond1.' '.$critere_program.' '.$criteres.' ';
                                             }elseif($KEY2==2)
                                             {
                                          $query_principal = 'SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE  ptba_tache.PTBA_TACHE_ID IN (SELECT DISTINCT PTBA_TACHE_ID FROM execution_budgetaire where 1 '.$cond2.') '.$cond.''.$critere_program.' '.$criteres.' '.$cond1.' ';
                                             }
                                             $limit='LIMIT 0,10';
                                             if($_POST['length'] != -1)
                                             {
                                          $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
                                            }
                                          $order_by='';
                                          if($_POST['length'] != -1)
                                             {
                                          $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
                                             }
                                          $order_by = '';
                                          $order_column = array(1,'DESCRIPTION_INSTITUTION','inst_institutions_programmes.INTITULE_PROGRAMME','inst_institutions_actions.LIBELLE_ACTION','DESC_TACHE');
                                            $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions_programmes.INTITULE_PROGRAMME ASC';
                                            $search = !empty($_POST['search']['value']) ? ("AND ( DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR inst_institutions_programmes.INTITULE_PROGRAMME LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' )") : '';
                                            $critere="";
                                            $conditions=$query_principal.'  '.$search.' '.$order_by.'   '.$limit;
                                            $query_filter=$query_principal.' '.$critere.'  '.$search;
                                            $query_secondaire = 'CALL `getTable`("' . $conditions . '");';
                                            $fetch_data = $this->ModelPs->datatable($query_secondaire);
                                            $u=0;
                                            $data = array();
                                            foreach ($fetch_data as $row) 
                                            {
                                              $u++;
                                              $sub_array=array();
                                              $sub_array[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';
                                              if (strlen($row->DESCRIPTION_INSTITUTION) > 20) {
                                              $sub_array[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 18) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'"><i class="fa fa-eye"></i></a>';
                                              }else{
                                              $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESCRIPTION_INSTITUTION).'</label></font>';
                                              }
                                              if (strlen($row->INTITULE_PROGRAMME) >15) {
                                              $sub_array[] = mb_substr($row->INTITULE_PROGRAMME, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'"><i class="fa fa-eye"></i></a>';
                                              }else{
                                              $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->INTITULE_PROGRAMME).'</label></font>';

                                              }

                                              if (strlen($row->LIBELLE_ACTION) > 16) {
                                                $sub_array[] = mb_substr($row->LIBELLE_ACTION, 0, 15) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->LIBELLE_ACTION).'"><i class="fa fa-eye"></i></a>';
                                              }else{
                                                $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->LIBELLE_ACTION).'</label></font>';
                                              }
                                              if (strlen($row->DESC_TACHE) > 15) {
                                                $sub_array[] = mb_substr($row->DESC_TACHE, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$this->str_replacecatego($row->ACTIVITES).'"><i class="fa fa-eye"></i></a>';
                                              }else{
                                                $sub_array[] ='<font color="#000000" ><label>'.$this->str_replacecatego($row->DESC_TACHE).'</label></font>';
                                              }
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

                                          function exporter_activite_faire($PROGRAMME_ID,$INSTITUTION_ID)
                                          {
                                           $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
                                           if(empty($USER_IDD))
                                           {
                                            return redirect('Login_Ptba/do_logout');
                                          }
                                          $db = db_connect();
                                          $PROGRAMME_ID=$PROGRAMME_ID;
                                          $INSTITUTION_ID=$INSTITUTION_ID;
                                          $callpsreq = "CALL getRequete(?,?,?,?);";
                                          $critere="";
                                          $critere_program="";
                                          if ($INSTITUTION_ID>0)
                                          {
                                            $critere.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;

                                            if ($PROGRAMME_ID>0)
                                            {
                                              $critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
                                            }
                                          }
                                          $cond1="";
                                          $cond2="";
                                          
                                         $getRequete="SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID  WHERE 1 ".$critere." ".$critere_program." ".$cond1." ";
                                         $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
                                         $spreadsheet = new Spreadsheet();
                                         $sheet = $spreadsheet->getActiveSheet();
                                         $sheet->setCellValue('A1', 'INSTITUTION');
                                         $sheet->setCellValue('B1', 'PROGRAMME');
                                         $sheet->setCellValue('C1', 'ACTION');
                                         $sheet->setCellValue('E1', 'ACTIVITES');
                                         $sheet->setCellValue('F1', 'MONTANT VOTE BUDGET_T1');
                                         $sheet->setCellValue('G1', 'MONTANT VOTE BUDGET_T2');
                                         $sheet->setCellValue('H1', 'MONTANT VOTE BUDGET_T3');
                                         $sheet->setCellValue('I1', 'MONTANT VOTE BUDGET_T4');

                                         $rows = 3;

                                         foreach ($getData as $key)
                                         {
                                          $sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
                                          $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
                                          $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
                                          $sheet->setCellValue('E' . $rows, $key->DESC_TACHE);
                                          $sheet->setCellValue('F' . $rows, $key->BUDGET_T1);
                                          $sheet->setCellValue('G' . $rows, $key->BUDGET_T2);
                                          $sheet->setCellValue('H' . $rows, $key->BUDGET_T3);
                                          $sheet->setCellValue('I' . $rows, $key->BUDGET_T4);
                                          $rows++;
                                        } 
                                        $writer = new Xlsx($spreadsheet);
                                        $writer->save('world.xlsx');
                                        return $this->response->download('world.xlsx', null)->setFileName('Liste des activités à faire.xlsx');
                                        return redirect('dashboard/Dashbord_Suivi_Activite');
                                      }
                                      function exporter_activite_deja_fait($PROGRAMME_ID,$INSTITUTION_ID)
                                      {
                                        $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
                                        if(empty($USER_IDD))
                                        {
                                          return redirect('Login_Ptba/do_logout');
                                        }

                                        $db = db_connect();
                                        $PROGRAMME_ID=$PROGRAMME_ID;
                                        $INSTITUTION_ID=$INSTITUTION_ID;
                                       
                                        $callpsreq = "CALL getRequete(?,?,?,?);";
                                        $critere="";
                                        $critere_program="";
                                        if ($INSTITUTION_ID>0)
                                        {
                                          $critere.=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
                                          if ($PROGRAMME_ID>0)
                                          {
                                            $critere_program=" AND ptba_tache.PROGRAMME_ID=".$PROGRAMME_ID;
                                          }
                                        }
                                        $cond1="";
                                        $cond2="";
                                    
                                       $getRequete="SELECT `DESCRIPTION_INSTITUTION`,inst_institutions_programmes.INTITULE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,`DESC_TACHE`,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache JOIN  `inst_institutions` ON ptba_tache.INSTITUTION_ID=inst_institutions.INSTITUTION_ID JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions ON inst_institutions_actions.ACTION_ID=ptba_tache.ACTION_ID WHERE ptba_tache.PTBA_TACHE_ID IN (SELECT DISTINCT PTBA_TACHE_ID FROM execution_budgetaire WHERE 1 ".$cond1.") ".$critere." ".$critere_program." ".$cond1." ";
                                       $getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
                                       $spreadsheet = new Spreadsheet();
                                       $sheet = $spreadsheet->getActiveSheet();
                                       $sheet->setCellValue('A1', 'INSTITUTION');
                                       $sheet->setCellValue('B1', 'PROGRAMME');
                                       $sheet->setCellValue('C1', 'ACTION');
                                       $sheet->setCellValue('E1', 'ACTIVITES');
                                       $sheet->setCellValue('F1', 'MONTANT VOTE BUDGET_T1');
                                       $sheet->setCellValue('G1', 'MONTANT VOTE BUDGET_T2');
                                       $sheet->setCellValue('H1', 'MONTANT VOTE BUDGET_T3');
                                       $sheet->setCellValue('I1', 'MONTANT VOTE BUDGET_T4');
                                       $rows = 3;
                                       foreach ($getData as $key)
                                       {
                                        $sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
                                        $sheet->setCellValue('B' . $rows, $key->INTITULE_PROGRAMME);
                                        $sheet->setCellValue('C' . $rows, $key->LIBELLE_ACTION);
                                        $sheet->setCellValue('E' . $rows, $key->DESC_TACHE);
                                        $sheet->setCellValue('F' . $rows, $key->BUDGET_T1);
                                        $sheet->setCellValue('G' . $rows, $key->BUDGET_T2);
                                        $sheet->setCellValue('H' . $rows, $key->BUDGET_T3);
                                        $sheet->setCellValue('I' . $rows, $key->BUDGET_T4);
                                        $rows++;
                                      } 
                                      $writer = new Xlsx($spreadsheet);
                                      $writer->save('world.xlsx');
                                      return $this->response->download('world.xlsx', null)->setFileName('Liste des activités déjà faites.xlsx');
                                      return redirect('dashboard/Dashbord_Suivi_Activite');
                                    }
                                  }
                                  ?>
