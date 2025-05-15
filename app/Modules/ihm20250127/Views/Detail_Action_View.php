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
                        <div class="row">
                          <div class="col-md-8">
                            <h3><?= lang('messages_lang.detail_Action') ?></h3>
                          </div>
                          <div class="col-md-4">
                            <a href="<?=base_url('ihm/Institutions_action') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.link_list') ?> </a>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>  
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                  <div class="card-header">
                    <input type="hidden" name="ACTION_ID" id="ACTION_ID" value="<?=$action['ACTION_ID']?>">
                    <br>
                      <div class="row col-md-12">
                          <h5><i class="fa fa-language"></i> <?= $instit['CODE_INSTITUTION']?> , &nbsp; <i class="fa fa-tag"></i> <?= $instit['DESCRIPTION_INSTITUTION']?></h5>
                      </div>
                      <div class="row col-md-12">
                          <h5><i class="fa fa-language"></i> <?= $prog['CODE_PROGRAMME']?> , &nbsp; <i class="fa fa-tag"></i> <?= $prog['INTITULE_PROGRAMME']?></h5>
                      </div>
                      <div class="row col-md-12">
                          <h5><i class="fa fa-language"></i> <?= $action['CODE_ACTION']?> , &nbsp; <i class="fa fa-tag"></i> <?= $action['LIBELLE_ACTION']?></h5>
                      </div>

                    <p class="card-text"></p>
                  </div>
                  <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <center><h4>Voté</h4></center>
                            <table class="table table-bordered">
                                <tr class="text-uppercase text-nowrap">
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
                                  <td><?= lang('messages_lang.total_annuel') ?></td>
                                  <td><?=number_format($montant_total,0,","," ").' BIF' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                          <center><h4><?= lang('messages_lang.execute_intitution_detail') ?></h4></center>
                             <table class="table table-bordered">
                                <tr class="text-uppercase text-nowrap">
                                    <th><?= lang('messages_lang.labelle_tranche') ?></th>
                                    <th><?= lang('messages_lang.labelle_montant') ?></th>
                                    <th><?= lang('messages_lang.activite_institution_detail') ?></th>
                                </tr>
                                <tr>
                                    <td>T1</td>
                                    <td><?=number_format($executeMoney1,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($activites1,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T2</td>
                                    <td><?=number_format($executeMoney2,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($activites2,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T3</td>
                                    <td><?=number_format($executeMoney3,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($activites3,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T4</td>
                                    <td><?=number_format($executeMoney4,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($activites4,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                  <td><?= lang('messages_lang.total_annuel') ?></td>
                                  <td><?=number_format($tot_exe,0,","," ").' BIF' ?></td>
                                  <td><?=number_format($totAct,0,","," ").'' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-bordered">
                                <center><h4>Restant</h4></center>
                                <tr class="text-uppercase text-nowrap">
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
                                    <td><?= lang('messages_lang.total_annuel') ?></td>
                                    <td><?=number_format($restant,0,","," ").' BIF' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <a onclick="show_nav(1)" class="nav-link active" id="tab_activite-tab" data-bs-toggle="tab" data-bs-target="#tab_activite"  role="tab" aria-controls="tab_activite" aria-selected="false"> <i class="fa fa-history" aria-hidden="true"></i>&nbsp;&nbsp;<?=$total_activ?>&nbsp;&nbsp;Activité(s)&nbsp;&nbsp;</a>
                      </li>
                    </ul>
                    
                    <div id="tab_activite"><br>
                      <h3 class="text-black"><?= lang('messages_lang.activite_institution_detail') ?>(s)</h3><br>
                      <div class="table-responsive container">
                        <table id="table_activite" class="table table-bordered" style="width: 100%;">
                          <thead>
                            <tr class="text-uppercase text-nowrap">
                              <th><?= lang('messages_lang.labelle_code_budgetaire') ?></th>
                              <th><?= lang('messages_lang.code_budgetaire_nouveau_intitution_detail') ?></th>
                              <th><?= lang('messages_lang.labelle_code_programmatique') ?></th>
                              <th><?= lang('messages_lang.labelle_activites') ?></th>
                              <th><?= lang('messages_lang.labelle_montant_vote') ?></th>
                              <th><?= lang('messages_lang.montant_execute') ?></th>
                              <th><?= lang('messages_lang.details_prog_budg') ?></th>
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
        $('#tab_activite').prop('hidden',false);
        liste_activite(<?=$action['ACTION_ID']?>)
      });

    </script> 
   <script>
     function liste_activite() {
       var id = $('#ACTION_ID').val()  
       $("#table_activite").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
          //"oreder": [[5, 'desc']],
        "ajax": {
          url:"<?= base_url()?>/ihm/Detail_Action/liste_activite/"+id,
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
          "targets": [6],
          "orderable": false
        }],
        dom: 'Bfrtlip',
        order:[1,'desc'],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
          "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
          "sSearch": "<?= lang('messages_lang.search_button') ?>&nbsp;:",
          "sLengthMenu": "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
          "sInfo": "<?= lang('messages_lang.sInfo_enjeux') ?>",
          "sInfoEmpty": "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
          "sInfoFiltered": "(<?= lang('messages_lang.filtre_max_total_enjeux') ?>)",
          "sInfoPostFix": "",
          "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
          "sZeroRecords": "<?= lang('messages_lang.aucun_element_afficher_enjeux') ?>",
          "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
          "oPaginate": {
            "sFirst": "<?= lang('messages_lang.labelle_1') ?>",
            "sPrevious": "<?= lang('messages_lang.btn_precedent') ?>",
            "sNext": "<?= lang('messages_lang.btn_suivant') ?>",
            "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
          },
          "oAria": {
            "sSortAscending": "<?= lang('messages_lang.sSortAscending_enjeux') ?>",
            "sSortDescending": "<?= lang('messages_lang.sSortDescending_enjeux') ?>"
          }
        }
      });


     }
   </script>

   <script>
    function show_nav(tab) {
        var id = $('#ACTION_ID').val()
        if (tab==1)
        {
          $('#tab_activite').prop('hidden',false);
          document.getElementById("tab_activite-tab").className = "nav-link active"; 
          liste_activite(<?=$action['ACTION_ID']?>)
        }
    }
  </script>

