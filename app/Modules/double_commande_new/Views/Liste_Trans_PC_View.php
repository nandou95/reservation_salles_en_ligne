<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url("template/css") ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php
                    $get_ordon_Afaire1="btn";
                    $get_ordon_Afaire_sup1="btn";
                    $get_ordon_deja_fait1="btn";
                    $bordereau_spe ="btn active";
                    $bordereau_deja_spe ="btn";
                    $get_ordon_AuCabinet1="btn";
                    $get_ordon_BorderCabinet1="btn";
                    $get_ordon_BonCED1="btn";
                    ?>
                    <?php include  'includes/Menu_Ordonnancement.php';?>
                  </div>
                </div>
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <br>
                <div>
                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                    <div class="card-body" style="margin-top: -20px"></div>
                  </div>
                  <h1 class="header-title text-dark"><?=lang("messages_lang.Transmission_vers_le_service_prise_en_charge")?></h1>
                </div>

                <div style="margin-right: 20px; float: right;">
                  <a href="<?= base_url("double_commande_new/Reception_First_Bord_Transmission/reception") ?>" class='btn btn-primary' style="float: right;"> <?= lang("messages_lang.Transmission_TD") ?></h1> </a>
                </div>

                <div class="card-body">
                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert')) : ?>
                    <div class="w-100 bg-success text-white text-center" id="message">
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="table-responsive container ">
                  <table id="mytable" class=" table table-bordered table-striped">
                    <thead>
                      <tr class="text-uppercase text-nowrap">
                        <th> <?= lang("messages_lang.bon_engagement_transmission_du_bordereau") ?> </th>
                        <th> <?= lang("messages_lang.menu_taux_change") ?> </th>
                        <th> <?= lang("messages_lang.table_mont_ord") ?> </th>
                        <th> <?= lang("messages_lang.label_inst") ?> </th>
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

  function liste() {
    // var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    // var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    // var ETAPE_DOUBLE_COMMANDE_ID = $('#ETAPE_DOUBLE_COMMANDE_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_Trans_PC/listing') ?>",
        type: "POST",
        // data: {
        //   INSTITUTION_ID: INSTITUTION_ID,
        //   SOUS_TUTEL_ID: SOUS_TUTEL_ID,
        //   ETAPE_DOUBLE_COMMANDE_ID: ETAPE_DOUBLE_COMMANDE_ID
        // }
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
      buttons: [],
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