<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>

</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php
                    $valid_faire = "btn active";
                    $valid_termnine = "btn";

                    $paiement_a_faire = "btn";
                    $paiement_deja_fait = "btn";

                    $recep_prise_en_charge ="btn";
                    $deja_recep_prise_en_charge ="btn";
                    $recep_dir ="btn";
                    $deja_recep_dir ="btn";
                    $recepion_brb ="btn";
                    $deja_reception_brb ="btn";

                    $bordereau_dc ="btn";
                    $bordereau_deja_dc ="btn";
                    $bordereau_brb ="btn";
                    $bordereau_deja_brb="btn";
                    ?>
                    <?php include  'includes/Menu_Paiement.php'; ?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px"></div>
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <br>
                <div class="col-12 d-flex">
                  <div class="col-9" style="float: left;">
                    <h1 class="header-title text-dark">
                    <?= lang('messages_lang.titre_faire') ?> 
                    </h1>
                  </div>
                </div>
                <div class="row col-md-12">
                  <div class="col-md-6">

                  <label for="Nom" class="form-label"> <?= lang('messages_lang.labelle_institution') ?> </label>
                    <select onchange="liste();sous_titre()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                    <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                      <?php
                      foreach ($institutions as $key) {
                        if ($key->INSTITUTION_ID == set_value('INSTITUTION_ID')) {
                          echo "<option value='" . $key->INSTITUTION_ID . "'  selected>" . $key->CODE_INSTITUTION . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $key->DESCRIPTION_INSTITUTION . "</option>";
                        } else {
                          echo "<option value='" . $key->INSTITUTION_ID . "' >" . $key->CODE_INSTITUTION . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $key->DESCRIPTION_INSTITUTION . "</option>";
                        }
                      }
                      ?>
                    </select>

                  </div>
                  <div class="col-md-6">
                  <label> <?= lang('messages_lang.sous_titre_decaisement') ?> </label>
                    <select class="form-control select2" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="liste()">
                    <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                    </select>
                  </div>
                 
                </div>
                <br>

                <div class="card-body">

                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="table-responsive container ">

                    <div></div>
                    <table id="valid_faire" class=" table table-bordered table-striped">
                      <thead>
                      <tr class="text-uppercase">
                          
                          <th> <?= lang('messages_lang.titre_decaissement') ?> </th>
                          <th> <?= lang('messages_lang.col_eng_budg') ?> </th>
                          <th> <?= lang('messages_lang.col_eng_jur') ?> </th>
                          <th> <?= lang('messages_lang.col_liquid') ?> </th>
                          <th> <?= lang('messages_lang.labelle_ordonan') ?> </th>
                          <th> <?= lang('messages_lang.labelle_paiement') ?> </th>
                          <th> <?= lang('messages_lang.option_action') ?> </th>
                        </tr>
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
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>


</body>

</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function() {
    liste();
  });
</script>

<script>
  function sous_titre() {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();

    if (INSTITUTION_ID == '') {
      $('#SOUS_TUTEL_ID').html('<option value="">Sélectionner</option>');
    } else {
      $.ajax({
        url: "<?= base_url() ?>/double_commande_new/Validation_Titre/get_sous_titre/" + INSTITUTION_ID,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
          $('#SOUS_TUTEL_ID').html(data.sous_tutel);
        }
      });

    }
  }
</script>

<script type="text/javascript">
  function liste() {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var row_count = "1000000";
    $("#valid_faire").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Validation_Titre/liste_validation') ?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID: SOUS_TUTEL_ID,
        }
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
      order: [],
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