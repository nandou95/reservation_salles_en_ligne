<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>

  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title">
            </h1>
          </div>

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">

                </div>
                <div class="card-body" style="overflow-x:auto;">
                  <div style="
                  margin-top: -40px;
                  " class="card">
                  <div class="card-header">
                  </div>
                  <div class="card-body" style="">

                    <div class="col-md-12">
                        <br>
                        <div class="row">
                          <div class="col-md-8">
                            <h3><?= lang('messages_lang.lab_det_prag') ?></h3>
                          </div>
                          <div class="col-md-4">
                            <a href="<?=base_url('ptba/Ptba_Programme') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span><?=lang('messages_lang.liste_bouton')?></a>
                          </div>
                          <div class="row col-md-12">
                            <h5><i class="fa fa-language"></i> <?= $programme['CODE_PROGRAMME']?> , &nbsp; <i class="fa fa-tag"></i> <?= $programme['INTITULE_PROGRAMME']?></h5>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>  

                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                  <div class="card-header">
                    <input type="hidden" name="PROGRAMME_ID" id="PROGRAMME_ID" value="<?=$programme['PROGRAMME_ID']?>">
                    <br>
                    <div class="row col-md-12">
                      <h5><i class="fa fa-language"></i> <?= $programme['CODE_INSTITUTION']?> , &nbsp; <i class="fa fa-tag"></i> <?= $programme['DESCRIPTION_INSTITUTION']?></h5>
                    </div>

                    <p class="card-text"></p>
                  </div>
                  <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <center><h4><?=lang('messages_lang.label_vote')?></h4></center>
                            <table class="table table-bordered">
                                <tr>
                                    <th><?= lang('messages_lang.labelle_tranche') ?></th>
                                    <th><?= lang('messages_lang.labelle_montant') ?></th>
                                </tr>
                                <tr>
                                    <td>T1</td>
                                    <td><?=number_format($montant_vote['T1'],0,","," ").' BIF' ?></td>
                                </tr>
                                <tr>
                                    <td>T2</td>
                                    <td><?=number_format($montant_vote['T2'],0,","," ").' BIF' ?></td>
                                </tr>
                                <tr>
                                    <td>T3</td>
                                    <td><?=number_format($montant_vote['T3'],0,","," ").' BIF' ?></td>
                                </tr>
                                <tr>
                                    <td>T4</td>
                                    <td><?=number_format($montant_vote['T4'],0,","," ").' BIF' ?></td>
                                </tr>
                                <tr>
                                  <td>Total</td>
                                  <td><?=number_format($montant_total,0,","," ").' BIF' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                        <table class="table table-bordered">
                          <center><h4><?= lang('messages_lang.execute_intitution_detail') ?></h4></center>
                          <tr>
                            <th><?= lang('messages_lang.labelle_tranche') ?></th>
                            <th><?= lang('messages_lang.labelle_montant') ?></th>
                          </tr>
                          
                          <tr>
                            <td>T1</td>
                            <td><?=number_format($executeMoney1,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T2</td>
                            <td><?=number_format($executeMoney2,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T3</td>
                            <td><?=number_format($executeMoney3,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T4</td>
                            <td><?=number_format($executeMoney4,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>Total</td>
                            <td><?=number_format($tot_exe,0,","," ").' BIF' ?></td>
                          </tr>

                        </table>
                      </div>
                      <div class="col-md-4">
                        <table class="table table-bordered">
                          <center><h4><?=lang('messages_lang.label_restant')?></h4></center>
                          <tr>
                            <th><?= lang('messages_lang.labelle_tranche') ?></th>
                            <th><?= lang('messages_lang.labelle_montant') ?></th>
                          </tr>
                          <tr>
                            <td>T1</td>
                            <td><?=number_format($reste1,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T2</td>
                            <td><?=number_format($reste2,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T3</td>
                            <td><?=number_format($reste3,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>T4</td>
                            <td><?=number_format($reste4,0,","," ").' BIF' ?></td>
                          </tr>
                          <tr>
                            <td>Total</td>
                            <td><?=number_format($restant,0,","," ").' BIF' ?></td>
                          </tr>
                        </table>
                      </div>
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(1)" class="nav-link active" id="tab_action-tab" data-bs-toggle="tab" data-bs-target="#tab_action"  role="tab" aria-controls="tab_action" aria-selected="true"> <i class="fa fa-th-list" aria-hidden="true"></i>  <?=$action?>  <?= lang('messages_lang.actions_action') ?> </a>
                      </li>

                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(2)" class="nav-link" id="tab_activite-tab" data-bs-toggle="tab" data-bs-target="#tab_activite"  role="tab" aria-controls="tab_activite" aria-selected="false"> <i class="fa fa-history" aria-hidden="true"></i>  <?=$nbre_activite?> <?= lang('messages_lang.labelle_activites') ?></a>
                      </li>

                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(3)" class="nav-link" id="ligne-tab" data-bs-toggle="tab" data-bs-target="#bugdet"  role="tab" aria-controls="bugdet" aria-selected="false"> <i class="fa fa-cubes" aria-hidden="true"></i> <?=$nbre_ligne?> <?= lang('messages_lang.labelle_ligne_budgtaire') ?></a>
                      </li>
                    </ul>
                    
                    <div id="tab_action"><br>
                      <h3 class="text-black"><?= lang('messages_lang.liste_action_intitution_detail') ?></h3><br>
                      <div class="table-responsive">

                        <table id="mytable" class="table table-bordered" style="width:100%">
                          <thead>
                            <tr>
                              <tr>
                              <th><?= lang('messages_lang.th_code_action_new') ?></th>
                              <th><?= lang('messages_lang.th_action') ?></th>       
                              <th><?= lang('messages_lang.th_objectif') ?></th> 
                              <th><?= lang('messages_lang.detail_montant_vote') ?></th> 
                              <th><?= lang('messages_lang.lab_mont_exec') ?></th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>


                    <div id="tab_activite"><br>
                      <h3 class="text-black">Liste des activités</h3><br>
                      <div class="table-responsive container">
                        <table id="table_activite" class="table table-bordered">
                          <thead>
                            <tr>
                              <th><?= lang('messages_lang.titre_code_budg') ?></th>
                              <th><?= lang('messages_lang.code_prog') ?></th>
                              <th><?= lang('messages_lang.th_activite') ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                              <th><?= lang('messages_lang.detail_montant_vote') ?></th>
                              <th><?= lang('messages_lang.lab_mont_exec') ?></th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>

                    <div id="ligne_budget"><br>
                      <h3 class="text-black"><?= lang('messages_lang.lsite_budgetaire_institution_detail') ?></h3><br>
                      <div class="table-responsive container">
                      <table id="mytable4" class="table table-bordered">
                        <thead>
                          <tr>
                            <th><?= lang('messages_lang.th_ligne_budg') ?></th>  
                            <th><?= lang('messages_lang.detail_montant_vote') ?></th>
                            <th><?= lang('messages_lang.lab_mont_exec') ?></th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>

      <?php echo view('includesbackend/scripts_js.php');?>
    </body>
    </html>

    <script type="text/javascript">
      
      $(document).ready(function()
      {
        $('#tab_activite').prop('hidden',true);
        $('#ligne_budget').prop('hidden',true);
        
        liste(<?=$programme['PROGRAMME_ID']?>);
        liste_activite(<?=$programme['PROGRAMME_ID']?>);
        liste_ligne_budget();
      });

    </script> 


    <script type="text/javascript">
  // liste historique
      function liste() {
       var id = $('#PROGRAMME_ID').val()

       $("#mytable").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          //"oreder": [[5, 'desc']],
        "ajax": {
          url:"<?= base_url()?>/ptba/Detail_Programme/liste_action/"+id,
          type: "POST",
          data: {},
          beforeSend: function() {}
        },
        lengthMenu: [
          [5,10, 50, 100, -1],
          [5,10, 50, 100, "All"]
          ],
        pageLength: 5,
        "columnDefs": [{
          "targets": [],
          "orderable": false
        }],
        dom: 'Bfrtlip',
        order:[1,'desc'],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
          "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
          "sSearch":         "<?= lang('messages_lang.rechercher') ?>&nbsp;:",
          "sLengthMenu":     "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
          "sInfo":           "<?= lang('messages_lang.sInfo_enjeux') ?>",
          "sInfoEmpty":      "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
          "sInfoFiltered":   "<?= lang('messages_lang.filtre_max_total_enjeux') ?>",
          "sInfoPostFix":    "",
          "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
          "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
          "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
          "oPaginate": {
            "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
            "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
            "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
            "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
          },
          "oAria": {
            "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
            "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier') ?>"
          }
        }
      });


     }
   </script>

   <script>
     function liste_activite() {
       var id = $('#PROGRAMME_ID').val()
       
       $("#table_activite").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          //"oreder": [[5, 'desc']],
        "ajax": {
          url:"<?= base_url()?>/ptba/Detail_Programme/liste_activite/"+id,
          type: "POST",
          data: {},
          beforeSend: function() {}
        },
        lengthMenu: [
          [5,10, 50, 100, -1],
          [5,10, 50, 100, "All"]
          ],
        pageLength: 5,
        "columnDefs": [{
          "targets": [],
          "orderable": false
        }],
        dom: 'Bfrtlip',
        order:[1,'desc'],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
          "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
          "sSearch":         "<?= lang('messages_lang.rechercher') ?>&nbsp;:",
          "sLengthMenu":     "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
          "sInfo":           "<?= lang('messages_lang.sInfo_enjeux') ?>",
          "sInfoEmpty":      "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
          "sInfoFiltered":   "<?= lang('messages_lang.filtre_max_total_enjeux') ?>",
          "sInfoPostFix":    "",
          "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
          "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
          "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
          "oPaginate": {
            "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
            "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
            "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
            "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
          },
          "oAria": {
            "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
            "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier') ?>"
          }
        }
      });


     }


     function liste_ligne_budget(id) {
        var id = $('#PROGRAMME_ID').val();
  
      $("#mytable4").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          "ajax": {
            url:"<?= base_url()?>/ptba/Detail_Programme/liste_ligne_budget/"+id,
            type: "POST",
            data: {},
            beforeSend: function() {}
          },
          lengthMenu: [
          [5,10, 50, 100, -1],
          [5,10, 50, 100, "All"]
          ],
          pageLength: 5,
          "columnDefs": [{
            "targets": [],
            "orderable": false
          }],
          dom: 'Bfrtlip',
          order:[1,'desc'],
          buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
          language: {
            "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
            "sSearch":         "<?= lang('messages_lang.rechercher') ?>&nbsp;:",
            "sLengthMenu":     "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
            "sInfo":           "<?= lang('messages_lang.sInfo_enjeux') ?>",
            "sInfoEmpty":      "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
            "sInfoFiltered":   "<?= lang('messages_lang.filtre_max_total_enjeux') ?>",
            "sInfoPostFix":    "",
            "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
            "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
            "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
            "oPaginate": {
              "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
              "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
              "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
              "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
            },
            "oAria": {
              "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
              "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier') ?>"
            }
          }
        });


    }
   </script>

   <script>
    function show_nav(tab) {
        var id = $('#PROGRAMME_ID').val()
        
        if (tab==1)
        {
            $('#tab_activite').prop('hidden',true);
            $('#tab_action').prop('hidden',false);
            $('#ligne_budget').prop('hidden',true);

            document.getElementById("tab_activite-tab").className = "nav-link";
            document.getElementById("tab_action-tab").className = "nav-link active";
            document.getElementById("ligne-tab").className = "nav-link";
            liste(<?=$programme['PROGRAMME_ID']?>)

        }else if (tab==2) {

            $('#tab_activite').prop('hidden',false);
            $('#tab_action').prop('hidden',true);
            $('#ligne_budget').prop('hidden',true);
            document.getElementById("tab_activite-tab").className = "nav-link active";
            document.getElementById("tab_action-tab").className = "nav-link";
            document.getElementById("ligne-tab").className = "nav-link";
            
            liste_activite(<?=$programme['PROGRAMME_ID']?>)
        }else if(tab==3){

            $('#tab_activite').prop('hidden',true);
            $('#tab_action').prop('hidden',true);
            $('#ligne_budget').prop('hidden',false);
            document.getElementById("tab_activite-tab").className = "nav-link";
            document.getElementById("tab_action-tab").className = "nav-link";
            document.getElementById("ligne-tab").className = "nav-link active";
            liste_ligne_budget(<?=$programme['PROGRAMME_ID']?>)
        }

    }
  </script>

