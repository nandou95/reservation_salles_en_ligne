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
                        <?= lang('messages_lang.titre_compilation') ?>

                      </h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <div class="row">

                  </div>
                  <div class="row">
                    <?php
                    if (session()->getFlashKeys('alert')) {
                    ?>
                      <div class="col-md-12">
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                      </div>
                    <?php
                    }
                    ?>

                    <div class="table-responsive" style="width: 100%;">
                      <div style="border:0.5px solid #ddd; margin-bottom:50px;">
                        <form syle="float:right" action="<?= base_url('pip/Processus_Investissement_Public/save_doc_compilation') ?>" id="compilation-form" method="post" enctype="multipart/form-data" class="d-flex">
                          <div>

                          </div>
                          <div class="form-group col-md-6">
                            <input type="file" name="file_compiler" accept='.pdf' class="form-control mt-4">
                          </div>
                          <button type="submit" class="btn btn-primary mt-3" style="height:40px" href=""><?= lang('messages_lang.btn_televerser') ?></button>
                      </div>
                      <table id="mytable2" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th><?= lang('messages_lang.code') ?></th>
                            <th><?= lang('messages_lang.proc') ?></th>
                            <th><?= lang('messages_lang.step') ?></th>
                            <th><?= lang('messages_lang.th_instit') ?></th>
                            <th><?= lang('messages_lang.date') ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($compilation)) { ?>
                            <?php

                            foreach ($compilation as $information) :

                              $NOM_PROCESS = (mb_strlen($information->NOM_PROCESS) > 8) ? (mb_substr($information->NOM_PROCESS, 0, 8) . "...<a class='btn-sm' data-toggle='modal' data-target='#activite" . $information->ID_DEMANDE_INFO_SUPP . "' data-toggle='tooltip' title='" . $information->NOM_PROCESS . "'><i class='fa fa-eye'></i></a>") : $information->NOM_PROCESS;
                              $DESCR_ETAPE = (mb_strlen($information->DESCR_ETAPE) > 6) ? (mb_substr($information->DESCR_ETAPE, 0, 6) . "...<a class='btn-sm' data-toggle='modal' data-target='#activite" . $information->ID_DEMANDE_INFO_SUPP . "' data-toggle='tooltip' title='" . $information->DESCR_ETAPE . "'><i class='fa fa-eye'></i></a>") : $information->DESCR_ETAPE;

                            ?>

                              <tr>
                                <td><input type="checkbox" name="projet[]" value="<?= $information->ID_DEMANDE ?>"></td>
                                <td><?= $information->CODE_DEMANDE ?></td>
                                <td><?= $NOM_PROCESS ?></td>
                                <td><?= $DESCR_ETAPE ?></td>
                                <td><?= $information->DESCRIPTION_INSTITUTION ?></td>
                                <td><?= $information->DATE_INSERTION ?></td>
                              </tr>
                            <?php endforeach
                            ?>
                          <?php } else { ?>
                            <td colspan="6">
                              <center><?= lang('messages_lang.message_erreur_compilation') ?> </center>
                            </td>
                          <?php } ?>
                        </tbody>
                      </table>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>

</html>

<script type="text/javascript">
  $(document).ready(function() {
    // list_projet();
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable12").DataTable({
      language: {
        "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch": "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo": "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious": "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext": "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  });
</script>

<script>
  function save_compilation() {
    alert()
    var form_data = new FormData($("#compilation-form")[0]);
    // MyFormData.submit();
    url = "<?= base_url('pip/Processus_Investissement_Public/save_doc_compilation') ?>";
    $.ajax({
      url: url,
      type: 'POST',
      dataType: 'JSON',
      data: form_data,
      contentType: false,
      cache: false,
      processData: false,
      success: function(data) {
        if (data == true) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= lang('messages_lang.message_succes_compilation') ?>',
            timer: 1500,
          }).then(() => {

            window.location.href = '<?= base_url('pip/Processus_Investissement_Public/pip_add_form') ?>';

          });
        }
      }
    });
  }
</script>
<script>
  function list_projet() {
    var row_count = "1000000";
    $("#mytable").DataTable({
      "processing": true,
      "destroy": true,
      "serverSide": true,
      "targets": [0, 4],
      "oreder": [
        [0, 'desc']
      ],
      "ajax": {
        url: "<?= base_url('pip/Processus_Investissement_Public/liste_projet_acompiler') ?>",
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

      order: [0, 'desc'],
      dom: 'Bfrtlip',

      language: {
        "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch": "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo": "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious": "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext": "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
</script>