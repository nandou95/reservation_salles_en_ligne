<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="card-header">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black">
                        <?= lang('messages_lang.liste_des_actions')?>
                      </h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="Actions/new" style="float: right;margin: 40px" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?= lang('messages_lang.nouvelle_action')?> </a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive" style="width: 100%;">
                    <div class="card-body">
                      <!-- DEBUT DES FILTRES -->
                      <div class="row">
                        <div class="col-md-4">
                          <label for="PROCESS_ID" class="form-label"><?= lang('messages_lang.processus_action') ?></label>
                          <select onchange="liste(), get_etape();" class="form-control" name="PROCESS_ID" id="PROCESS_ID">
                            <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                            <?php
                            foreach ($processus as $key) {
                              if ($key->PROCESS_ID == $PROCESS_ID) {
                                ?>
                                <option value="<?= $key->PROCESS_ID; ?>" selected><?= $key->NOM_PROCESS; ?></option>
                                <?php
                              } else {
                                ?>
                                <option value="<?= $key->PROCESS_ID; ?>"><?= $key->NOM_PROCESS; ?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="ETAPE_ID" class="form-label"><?= lang('messages_lang.etape_action') ?></label>
                          <select onchange="liste(), get_action();" class="form-control" name="ETAPE_ID" id="ETAPE_ID">
                            <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                            <?php
                            foreach ($etape as $key) {
                              if ($key->ETAPE_ID == $ETAPE_ID) {
                                ?>
                                <option value="<?= $key->ETAPE_ID; ?>" selected><?= $key->DESCR_ETAPE; ?></option>
                                <?php
                              } else {
                                ?>
                                <option value="<?= $key->ETAPE_ID; ?>"><?= $key->DESCR_ETAPE; ?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="ACTION_ID" class="form-label"><?= lang('messages_lang.actions_action') ?></label>
                          <select onchange="liste();" class="form-control" name="ACTION_ID" id="ACTION_ID">
                            <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                            <?php
                            foreach ($action as $key) {
                              if ($key->ACTION_ID == $ACTION_ID) {
                                ?>
                                <option value="<?= $key->ACTION_ID; ?>" selected><?= $key->DESCR_ACTION; ?></option>
                                <?php
                              } else {
                                ?>
                                <option value="<?= $key->ACTION_ID; ?>"><?= $key->DESCR_ACTION; ?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                      <!-- FIN DES FILTRES -->

                      <?php
                      if (session()->getFlashKeys('alert')) {
                        ?>
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                        <?php
                      }
                      ?>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable" class=" table table-striped table-bordered">
                          <thead>
                            <tr class="text-uppercase">
                              <th><?= lang('messages_lang.processus_action') ?> </th>
                              <th><?= lang('messages_lang.etape_actuel_action') ?></th>
                              <th><?= lang('messages_lang.actions_action') ?></th>
                              <th><?= lang('messages_lang.etape_suivante_action') ?></th>
                              <th><?= lang('messages_lang.status_action') ?></th>
                              <th><?= lang('messages_lang.document_action') ?></th>
                              <th><?= lang('messages_lang.info_sup_action') ?></th>
                              <th><?= lang('messages_lang.categorie_action') ?></th>
                              <th><?= lang('messages_lang.initial_action') ?></th>
                              <th><?= lang('messages_lang.formulaire_action') ?></th>
                              <th><?= lang('messages_lang.lien_action') ?></th>
                              <th><?= lang('messages_lang.option_action') ?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
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
      <div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div id="mess" class="modal-body"></div>
            <div id="foot" class="modal-footer"></div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="get_info" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div id="head_get_info" class="modal-header"></div>
            <div id="mess_get_info" class="modal-body"></div>
            <div id="foot_get_info" class="modal-footer"></div>
          </div>
        </div>
      </div>
      <!-- Modal Document -->
      <div class="modal fade" id="get_document" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div id="head_get_document" class="modal-header"></div>
            <div id="mess_get_document" class="modal-body"></div>
            <div id="foot_get_document" class="modal-footer"></div>
          </div>
        </div>
      </div>
      <!-- ############################### -->
      <?php echo view('includesbackend/scripts_js.php'); ?>
    </body>
    </html>
   <script type="text/javascript">
      function show_modal(id)
      {
        var message = $('#message' + id).html();
        $('#mess').html(message);
        var footer = $('#footer' + id).html();
        $('#foot').html(footer);
        $('#mydelete').modal('show');
      }
      function show_modal_get_info(id)
      {
        var header_get_info = $('#header_get_info' + id).html();
        $('#head_get_info').html(header_get_info);
        var message = $('#message_get_info' + id).html();
        $('#mess_get_info').html(message);
        var footer = $('#footer_get_info' + id).html();
        $('#foot_get_info').html(footer);
        $('#get_info').modal('show');
      }
      function show_modal_get_doc(id) 
      {
        var header_get_document = $('#header_get_document' + id).html();
        $('#head_get_document').html(header_get_document);
        var message_document = $('#message_get_document' + id).html();
        $('#mess_get_document').html(message_document);
        var footer_document = $('#footer_get_document' + id).html();
        $('#foot_get_document').html(footer_document);
        $('#get_document').modal('show');
        $("#get_document").modal("show");
        var row_count = "1000000";
        $("#document").DataTable({
          "processing": true,
          "destroy": true,
          "serverSide": true,
          "targets": [0, 4],
          "oreder": [
          [0, 'desc']
          ],
          "ajax": {
            url: "<?php echo base_url('ihm/Actions/getDocument/'); ?>/" + id,
            type: "POST",
            data: {}
          },
          lengthMenu: [
          [10, 50, 100, row_count],
          [10, 50, 100, "All"]
          ],

          pageLength: 10,
          "columnDefs": [{
            "targets": [],
            "orderable": false
          }],

          dom: 'Bfrtlip',
          order:[1,'asc'],
          buttons: [
          'excel', 'pdf'
          ],

        });
      }
</script>

<script>
  $(document).ready(function() {
    liste();
  });
</script>

<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);

  function liste() {
    var PROCESS_ID = $('#PROCESS_ID').val();
    var ETAPE_ID = $('#ETAPE_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "processing": true,
      "destroy": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('ihm/Actions/listing') ?>",
        type: "POST",
        data: {
          PROCESS_ID: PROCESS_ID,
          ETAPE_ID: ETAPE_ID,
          ACTION_ID: ACTION_ID,
        },
      },
      lengthMenu: [
      [10, 50, 100, row_count],
      [10, 50, 100, "All"]
      ],
      pageLength: 10,
      "columnDefs": [{
        "targets": [0, 4],
        "orderable": false
      }],
      dom: 'Bfrtlip',
      //order:[1,'desc'],
      buttons: [
      'excel', 'pdf'
      ],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
        "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }
    });
  }

  function get_etape() {
    var PROCESS_ID = $('#PROCESS_ID').val();
    $.post('<?= base_url('ihm/Actions/get_etape') ?>', {
      PROCESS_ID: PROCESS_ID
    },
    function(data) {
      $('#ETAPE_ID').html(data.html);
      ETAPE_ID.InnerHtml = data;
    })
  }

  function get_action() {
    var ETAPE_ID = $('#ETAPE_ID').val();
    $.post('<?= base_url('ihm/Actions/get_action') ?>', {
      ETAPE_ID: ETAPE_ID
    },
    function(data) {
      $('#ACTION_ID').html(data.html);
      ACTION_ID.InnerHtml = data;
    })
  }
</script>