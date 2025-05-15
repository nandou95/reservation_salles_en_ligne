<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
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
                      <div class="row">
                        <div class="col-md-8">
                          <h3><?= lang('messages_lang.detail_institution') ?></h3>
                        </div>
                        <div class="col-md-4">
                          <a href="<?=base_url('ptba/Institution') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.link_list') ?> </a>
                        </div>
                        <div class="col-md-12">
                          <h5><i class="fa fa-language"></i> <?php if (!empty($get_institution['CODE_INSTITUTION'])){  echo $get_institution['CODE_INSTITUTION']; }else{ echo 'N/A';} ?> , &nbsp; <i class="fa fa-tag"></i> <?php if (!empty($get_institution['DESCRIPTION_INSTITUTION'])){  echo $get_institution['DESCRIPTION_INSTITUTION']; }else{ echo 'N/A';} ?></h5>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>  

                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                  <div class="card-header">
                    <input type="hidden" name="INSTITUTION_ID" id="INSTITUTION_ID" value="<?php if (!empty($get_institution['INSTITUTION_ID'])){  echo $get_institution['INSTITUTION_ID']; }else{ echo '0';} ?>">
                    <br>
                    <div class="row col-md-12"></div>
                    <p class="card-text"></p>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-4">
                        <center><h4><?= lang('messages_lang.vote_institution_detail') ?></h4></center>
                        <table class="table table-bordered">
                          <tr class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.tranche_institution_detail') ?></th>
                            <th><?= lang('messages_lang.montant_intitution_detail') ?></th>
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
                          <tr class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.tranche_institution_detail') ?></th>
                            <th><?= lang('messages_lang.montant_intitution_detail') ?></th>
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
                          <center><h4><?= lang('messages_lang.restant_intitution_detail') ?></h4></center>
                          <tr class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.tranche_institution_detail') ?></th>
                            <th><?= lang('messages_lang.montant_intitution_detail') ?></th>
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

                    <!--   Début rapport graphique -->

                    <div class="row">
                      <div id="container" class="col-md-12">
                        <label class="text-success"> <?= lang('messages_lang.rapport_graphique_intitution_detail') ?></label>
                      </div>
                    </div>
                    <!-- Modal detail rapport -->
                    <div class="row">
                      <div class="modal fade" id="myModal" role="dialog">
                        <div class="modal-dialog" style ="max-width: 70%;">
                          <div class="modal-content  ">
                            <div class="modal-header">
                              <h4 class="modal-title"><span id="titre" style="color: black"></span></h4>
                            </div>
                            <div class="modal-body">
                              <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                                <thead>
                                 <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                                 <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a id="id_inst"></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                                 <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspACTIVITE<a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                                  <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp<a id="id_budget"></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                                </thead>
                              </table>  
                            </div>
                            <div class="modal-footer">
                              <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?= lang('messages_lang.label_ferm') ?></button>
                            </div>
                          </div>
                        </div>
                      </div>  
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(1)" class="nav-link active" id="programme-tab" data-bs-toggle="tab" data-bs-target="#detailleformation"  role="tab" aria-controls="detailleformation" aria-selected="true"> <i class="fa fa-cogs" aria-hidden="true"></i> <?=$nbre_program?> <?= lang('messages_lang.labelle_programme') ?>(s)  </a>
                      </li>

                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(2)" class="nav-link" id="action-tab" data-bs-toggle="tab" data-bs-target="#histotraitement"  role="tab" aria-controls="histotraitement" aria-selected="false"> <i class="fa fa-th-list" aria-hidden="true"></i> <?=$action?> <?= lang('messages_lang.labelle_action') ?>(s)  </a>
                      </li>

                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(3)" class="nav-link" id="activite-tab" data-bs-toggle="tab" data-bs-target="#document"  role="tab" aria-controls="document" aria-selected="false"> <i class="fa fa-history" aria-hidden="true"></i> <?=$activite?> <?= lang('messages_lang.activite_institution_detail') ?>(s)</a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(4)" class="nav-link" id="ligne-tab" data-bs-toggle="tab" data-bs-target="#bugdet"  role="tab" aria-controls="bugdet" aria-selected="false"> <i class="fa fa-cubes" aria-hidden="true"></i> 
                          <?=$ligne?> 
                          <?= lang('messages_lang.ligne_intitution_budgetaire') ?>(s) 
                          <?= lang('messages_lang.budgetaire_intitution_detail') ?>(s)
                        </a>
                      </li>
                    </ul>
                    <div id="programme"><br>
                      <h3 class="text-black"><?= lang('messages_lang.liste_des_programmes') ?></h3><br>
                      <div class="table-responsive container">
                      <table id="mytable1" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr  class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.labelle_code_programmatique') ?></th>
                            <th><?= lang('messages_lang.th_programme')?></th>
                            <th><?= lang('messages_lang.th_objectif_programme') ?></th>       
                            <th><?= lang('messages_lang.labelle_montant_vote') ?></th>
                            <th><?= lang('messages_lang.montant_execute') ?></th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                    </div>
                    <div id="action"><br>
                      <h3 class="text-black"><?= lang('messages_lang.liste_action_intitution_detail') ?></h3><br>
                      <div class="table-responsive container">
                      <table id="mytable2" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr  class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.code_action_intitution_detail') ?></th>
                            <th><?= lang('messages_lang.labelle_action') ?></th>  
                            <th><?= lang('messages_lang.th_objectif_programme') ?></th>     
                            <th><?= lang('messages_lang.labelle_montant_vote') ?></th>
                            <th><?= lang('messages_lang.montant_execute') ?></th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                    </div>
                    <div id="activite"><br>
                      <h3 class="text-black"><?= lang('messages_lang.liste_des_activite_intitution_detail') ?></h3><br>
                      <div class="table-responsive container">
                      <table id="mytable3" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr  class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.labelle_code_budgetaire') ?></th>
                            <th><?= lang('messages_lang.code_budgetaire_nouveau_intitution_detail') ?></th>
                            <th><?= lang('messages_lang.labelle_code_programmatique') ?></th>
                            <th><?= lang('messages_lang.code_programmatique_institution_detail') ?></th>
                            <th><?= lang('messages_lang.labelle_activite') ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>     
                            <th><?= lang('messages_lang.labelle_montant_vote') ?></th>
                            <th><?= lang('messages_lang.montant_execute') ?></th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                    </div>
                    <div id="ligne_budget"><br>
                      <h3 class="text-black"><?= lang('messages_lang.lsite_budgetaire_institution_detail') ?></h3><br>
                      <div class="table-responsive container">
                      <table id="mytable4" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr  class="text-uppercase text-nowrap">
                            <th><?= lang('messages_lang.ligne_budgetaire_institution_detail') ?></th>
                            <th><?= lang('messages_lang.labelle_montant_vote') ?></th>
                            <th><?= lang('messages_lang.montant_execute') ?></th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                    </div>
                    <br>
                  </div>
                </div>
              </div>
            </main>
          </div>
        </div>
        <?php echo view('includesbackend/scripts_js.php');?>
      </body>
      </html>
      <script>
        $(document).ready(function()
        {
          $('#activite').prop('hidden',true);
          $('#action').prop('hidden',true);
          $('#ligne_budget').prop('hidden',true);
          liste_programme(<?=$get_institution['INSTITUTION_ID']?>);
          liste_action();
          liste_activite();
          liste_ligne_budget();
        });
      </script>
      <script>
        function show_nav(tab) { 
          if (tab==1) {
            $('#programme').prop('hidden',false);
            $('#action').prop('hidden',true);
            $('#activite').prop('hidden',true);
            $('#ligne_budget').prop('hidden',true);
            $('#programme-tab').prop('class',"nav-link active");
            $('#action-tab').prop('class',"nav-link");
            $('#activite-tab').prop('class',"nav-link");
            $('#ligne-tab').prop('class',"nav-link");
          }
          else if (tab==2)
          {
            $('#programme').prop('hidden',true);
            $('#action').prop('hidden',false);
            $('#activite').prop('hidden',true);
            $('#ligne_budget').prop('hidden',true);
            $('#programme-tab').prop('class',"nav-link");
            $('#action-tab').prop('class',"nav-link active");
            $('#activite-tab').prop('class',"nav-link");
            $('#ligne-tab').prop('class',"nav-link");
          }else if(tab==3)
          {
            $('#programme').prop('hidden',true);
            $('#action').prop('hidden',true);
            $('#activite').prop('hidden',false);
            $('#ligne_budget').prop('hidden',true);
            $('#programme-tab').prop('class',"nav-link");
            $('#action-tab').prop('class',"nav-link");
            $('#activite-tab').prop('class',"nav-link active");
            $('#ligne-tab').prop('class',"nav-link");
          }else if(tab==4)
          {
            $('#programme').prop('hidden',true);
            $('#action').prop('hidden',true);
            $('#activite').prop('hidden',true);
            $('#ligne_budget').prop('hidden',false);
            $('#programme-tab').prop('class',"nav-link");
            $('#action-tab').prop('class',"nav-link");
            $('#activite-tab').prop('class',"nav-link");
            $('#ligne-tab').prop('class',"nav-link active");
          }
        }
      </script>
      <script type="text/javascript">
        function liste_programme(id) {
          var id = $('#INSTITUTION_ID').val();
          $("#mytable1").DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
          //"oreder": [[5, 'desc']],
          "ajax": {
            url:"<?= base_url()?>/ptba/Detail_Institution/liste_programme/"+id,
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
        function liste_action(id) {
          var id = $('#INSTITUTION_ID').val();
          $("#mytable2").DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
              url:"<?= base_url()?>/ptba/Detail_Institution/liste_action/"+id,
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
        function liste_activite(id) {
          var id = $('#INSTITUTION_ID').val();
      //alert(id);
      $("#mytable3").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          //"oreder": [[5, 'desc']],
          "ajax": {
            url:"<?= base_url()?>/ptba/Detail_Institution/liste_activite/"+id,
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
      var INSTITUTION_ID = $('#INSTITUTION_ID').val();

      $("#mytable4").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          //"oreder": [[5, 'desc']],
          "ajax": {
            url:"<?= base_url()?>/ptba/Detail_Institution/liste_ligne_budget/"+INSTITUTION_ID,
            type: "POST",
            data: {

              INSTITUTION_ID:INSTITUTION_ID

            },
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

  <script type="text/javascript">

    Highcharts.chart('container', {
      chart: {
        type: 'column'  
      },

      title: {
        text: '<b><?= lang('messages_lang.lab_vote_mont') ?> (<?=number_format($data_total,0,'',' ')?>) <br/> <?= lang('messages_lang.montant_execute') ?> (<?=number_format($data_total1,0,'',' ')?>)</b>'
      },
      subtitle: {
        text: ' '
      },

      xAxis: {
        type: 'category'
      },

      yAxis: {
        allowDecimals: false,
        min: 0,
        title: {
          text: ''
        }
      },

      plotOptions: {
        column: {
         cursor:'pointer',
         point:{
          events: {
           click: function()
           {

             if(this.key2==1){
              $("#id_budget").html("MONTANT&nbspVOTE");
              $("#id_inst").html("SOUS&nbspTITRE");
            }else{
              $("#id_budget").html("MONTANT&nbspEXECUTE");
               $("#id_inst").html("INSTITUTION");
            }
            $("#titre").html("<?= lang('messages_lang.liste_activités') ?>");
            $("#myModal").modal();
            var row_count ="1000000";
            $("#mytable").DataTable({
              "processing":true,
              "serverSide":true,
              "bDestroy": true,
              "oreder":[],
              "ajax":{
                url:"<?=base_url('ptba/Detail_Institution/detail_rapport')?>",
                type:"POST",
                data:{
                  key:this.key,
                  key2:this.key2

                }
              },
              lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
              pageLength: 10,
              "columnDefs":[{
                "targets":[],
                "orderable":false
              }],

              dom: 'Bfrtlip',
              buttons: [
              'excel', 'print','pdf'
              ],
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
        }
      },
      dataLabels: {
       enabled: true,
       format: '{point.y:,f} '
     },
     showInLegend: true
   }
 },
 credits: {
  enabled: true,
  href: "",
  text: "MEDIABOX"
},
series: [<?=$data_budget?>]
});
</script>
<script>
  function saveData()
  {

   $('#myModal').modal('hide');
 }
</script>