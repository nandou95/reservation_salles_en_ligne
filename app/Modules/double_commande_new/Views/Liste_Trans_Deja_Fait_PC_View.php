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
                    $bordereau_spe ="btn";
                    $bordereau_deja_spe ="btn active";
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
                <div class="row">
                  <div class="col-md-12">
                    <h1 class="header-title text-dark"> <?= lang("messages_lang.Transmission_vers_le_service_prise_en_charge_deja_fait") ?></h1>
                  </div>
                </div>

                <div class="card-body">

                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-3">
                        <label><?=lang('messages_lang.Lab_jur_instit')?></label>
                        <select autofocus onchange="getSousTutel();liste()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                          <option value=""><?=lang('messages_lang.selection_message')?></option>
                          <?php foreach ($institutions_user as $keyinstitution) { ?>
                            <option value="<?=$keyinstitution->INSTITUTION_ID?>" <?=$first_element_id == $keyinstitution->INSTITUTION_ID ? "selected" : ""?>>
                              <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                          <?php }?>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label><?=lang('messages_lang.Lab_jur_tutel')?><span id="loading_sous_tutel"></span></label>
                        <select onchange="liste()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                          <option value="">--<?=lang('messages_lang.selection_message')?>--</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                        <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange=";get_date();liste()" max="<?=date('Y-m-d')?>" class="form-control">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                        <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control" disabled>
                      </div>
                    </div>
                  </div>

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
                        <th><?=lang('messages_lang.list_num_bord')?></th>
                        <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
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
  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function() {
    liste();
  });

  function liste() {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    change_count();
    // var ETAPE_DOUBLE_COMMANDE_ID = $('#ETAPE_DOUBLE_COMMANDE_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_Trans_Deja_Fait_PC/listing') ?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID: SOUS_TUTEL_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN
          // ETAPE_DOUBLE_COMMANDE_ID: ETAPE_DOUBLE_COMMANDE_ID
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

  function getSousTutel()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    
    $.ajax(
    {
      url : "<?=base_url('/double_commande_new/Ordonnancement_Double_Commande/getSousTutel')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID
      },
      beforeSend:function() {
        $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {   
        $('#SOUS_TUTEL_ID').html(data.tutel);
        $('#loading_sous_tutel').html("");
      }
    });
  }
</script>

<script>
  function change_count()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();

    $.post('<?=base_url('double_commande_new/Ordonnancement_Double_Commande/change_count')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID,
      SOUS_TUTEL_ID:SOUS_TUTEL_ID,
      DATE_DEBUT:DATE_DEBUT,
      DATE_FIN:DATE_FIN
    },
    function(data)
    {
      $('#get_ordon_Afaire').html(data.get_ordon_Afaire);
      $('#get_ordon_Afaire_sup').html(data.get_ordon_Afaire_sup);
      $('#get_ordon_deja_fait').html(data.get_ordon_deja_fait);
      $('#get_bord_deja_spe').html(data.get_bord_deja_spe);
      $('#get_ordon_AuCabinet').html(data.get_ordon_AuCabinet);
      $('#get_ordon_BorderCabinet').html(data.get_ordon_BorderCabinet);
      $('#get_ordon_BonCED').html(data.get_ordon_BonCED);
      $('#get_etape_reject_ordo').html(data.get_etape_reject_ordo);
      
    })
  }
</script>
