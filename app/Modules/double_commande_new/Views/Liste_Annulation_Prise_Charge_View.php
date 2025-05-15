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
                    $reception_obr = "btn";
                    $prise_charge_compt = "btn";

                    //$get_etape_reject_pc = "btn aactive";
                    
                    $prise_charge_corr = "btn";
                    $etab_titre_corr = "btn";
                    $etape_corr = "btn";

                    $etab_titre = "btn";
                    $sign_dir_compt = "btn";
                    $sign_dir_dgfp = "btn";
                    $sign_dir_min = "btn";

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

                    $valid_faire = "btn";
                    $valid_termnine = "btn";

                    $get_nbr_av_obr1="btn";
                    $get_nbr_av_pc1="btn";
                    ?>
                    <?php include  'includes/Menu_Paiement.php'; ?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px"></div>
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <br>
                <div class="col-md-12">
                    <h3 class="header-title text-dark">
                    <?= lang('messages_lang.rejet_prise_charge') ?>
                    </h3>
                </div>
                <div class="row col-md-12">
                  <div class="col-md-4">

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
                  <div class="col-md-4">
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
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr class="text-uppercase" style="white-space: nowrap;">
                          <th> <?= lang('messages_lang.bon_engagement_transmission_du_bordereau') ?> </th>
                          <th> <?= lang('messages_lang.imputation_decaissement') ?> </th>
                          <th><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                          <th> <?= lang('messages_lang.labelle_devise') ?> </th>
                          <th> <?= lang('messages_lang.labelle_eng_budget') ?> </th>
                          <th> <?= lang('messages_lang.labelle_eng_jud') ?> </th>
                          <th> <?= lang('messages_lang.liquidation_decaissement') ?> </th>
                          <th> <?= lang('messages_lang.labelle_ordonan') ?> </th>
                          <th> <?= lang('messages_lang.option_action') ?> </th>
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
        url: "<?= base_url() ?>/double_commande_new/Liste_Annulation/get_sous_titre/" + INSTITUTION_ID,
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
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_Annulation/listing_annulation_pc') ?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID: SOUS_TUTEL_ID
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
</script>

<!--MODAL POUR LES TACHES ------>
<div class="modal" id="multi_tache" role="dialog">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title text-center" id="exampleModalLabel">
          <?=lang('messages_lang.list_task')?>
        </h3>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="task_id" id="task_id">
        <div class="table-responsive">
          <table id='mytable3' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
              <tr class="text-uppercase" >
                <th><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th><?=lang('messages_lang.list_quantite')?></th>
                <th><?=lang('messages_lang.labelle_devise')?></th>
                <th><?= lang('messages_lang.labelle_eng_budget') ?> </th>
                <th><?= lang('messages_lang.labelle_eng_jud') ?> </th>
                <th><?= lang("messages_lang.liquidation_decaissement") ?> </th>
                <th><?= lang('messages_lang.labelle_ordonan') ?> </th>
              </tr>
            </thead>
            <tbody id="table3">
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn mb-1 btn-secondary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!--SCRIPT POUR LES TACHES------>
<script type="text/javascript">
  function get_task(id)
  {
    $('#task_id').val(id);
    var task_id = $('#task_id').val();
    var row_count ="1000000";
    table=$("#mytable3").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "oreder":[],
      "ajax":{
        url:"<?= base_url('/double_commande_new/Liste_Paiement/detail_task_ordo')?>",
        type:"POST",
        data: {
          task_id:task_id
        }
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      language: {
        "sProcessing":     "Traitement en cours...",
        "sSearch":         "Rechercher&nbsp;:",
        "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
        "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
        "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
        "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "Chargement en cours...",
        "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
        "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
        "oPaginate": {
          "sFirst":      "Premier",
          "sPrevious":   "Pr&eacute;c&eacute;dent",
          "sNext":       "Suivant",
          "sLast":       "Dernier"
        },
        "oAria": {
          "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
          "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
        }
      }

    });
    $("#multi_tache").modal("show");
  } 
</script>