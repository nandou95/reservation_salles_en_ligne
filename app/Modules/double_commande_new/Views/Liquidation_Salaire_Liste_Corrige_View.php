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
                        $corrige_liqu_salaire = "btn active";
                        $liq_deja_valide = "btn";
                        $liq_a_valide="btn";
                        $liq_deja_fait = "btn";
                        include  'includes/Menu_Liquidation_Salaire.php';
                      ?>
                    </div>
                  </div>
                  <div class="card-body" style="margin-top: -20px">
                    <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">

                    <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>
                    <b style="font-size:30px" class="header-title text-black"><?=lang('messages_lang.liquid_salaire_corr')?></b>
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-md-3">
                          <label> <?= lang('messages_lang.label_mois') ?> </label>
                          <select onchange="liste();" class="form-control select2" name="MOIS_ID" id="MOIS_ID">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php
                            foreach ($mois as $key)
                            {
                              if ($key->MOIS_ID == set_value('MOIS_ID'))
                              {
                                echo "<option value='".$key->MOIS_ID."' selected>".$key->DESC_MOIS."</option>";
                              }
                              else
                              {
                                echo "<option value='".$key->MOIS_ID."'>".$key->DESC_MOIS."</option>";
                              }
                            }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label> <?= lang('messages_lang.categorie_salarie') ?> </label>
                          <select onchange="liste();" class="form-control select2" name="CATEGORIE_SALAIRE_ID" id="CATEGORIE_SALAIRE_ID">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php
                            foreach ($categorie as $key)
                            {
                              if ($key->CATEGORIE_SALAIRE_ID == set_value('CATEGORIE_SALAIRE_ID'))
                              {
                                echo "<option value='".$key->CATEGORIE_SALAIRE_ID."' selected>".$key->DESC_CATEGORIE_SALAIRE."</option>";
                              }
                              else
                              {
                                echo "<option value='".$key->CATEGORIE_SALAIRE_ID."'>".$key->DESC_CATEGORIE_SALAIRE."</option>";
                              }
                            }
                            ?>
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
                      <div class="row">
                        <div class="col-md-3">
                          <a href="#" id="btnexportexcel" onclick="exporter_excel()" type="button" style="float: center;margin-top: 2px;" class="btn btn-primary"><span class="fa fa-file-excel"></span>Excel
                          </a>
                        </div>
                      </div>

                      <div class="row">
                        <div class="table-responsive container ">
                          <table id="mytable" class="table table-bordered table-striped">
                            <thead>
                              <tr class="text-uppercase text-nowrap">
                                <th> <?= lang('messages_lang.th_instit') ?> </th>
                                <th> <?= lang('messages_lang.th_sous_tut') ?> </th>
                                <th> <?= lang('messages_lang.categorie_salarie') ?> </th>
                                <th> <?= lang('messages_lang.nbre_tach') ?> </th>
                                <th> <?= lang("messages_lang.liquidation_decaissement") ?> </th>
                                <th> <?= lang('messages_lang.labelle_option') ?> </th>
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

<script type="text/javascript">
  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }

  function liste() 
  {
    var CATEGORIE_SALAIRE_ID = $('#CATEGORIE_SALAIRE_ID').val();
    var DATE_FIN=$("#DATE_FIN").val();
    var DATE_DEBUT=$("#DATE_DEBUT").val();
    var MOIS_ID=$('#MOIS_ID').val()
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liquidation_Salaire_Liste/listing') ?>",
        type: "POST",
        data: {
          CATEGORIE_SALAIRE_ID,
          DATE_FIN,
          DATE_DEBUT,
          MOIS_ID
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

  function exporter_excel()
  {
    var CATEGORIE_SALAIRE_ID = $('#CATEGORIE_SALAIRE_ID').val();
    var DATE_FIN=$("#DATE_FIN").val();
    var DATE_DEBUT=$("#DATE_DEBUT").val();
    var MOIS_ID=$('#MOIS_ID').val()
    if (CATEGORIE_SALAIRE_ID == '' || CATEGORIE_SALAIRE_ID == null) {CATEGORIE_SALAIRE_ID = 0}
    if (MOIS_ID == '' || MOIS_ID == null) {MOIS_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexportexcel").href = "<?=base_url('double_commande_new/Liquidation_Salaire_Liste/exporter_Excel_A_corriger/')?>"+'/'+CATEGORIE_SALAIRE_ID+'/'+MOIS_ID+'/'+DATE_DEBUT+'/'+DATE_FIN;
  }
</script>

<script type="text/javascript">
  function get_tache(id,EXECUTION_BUDGETAIRE_ID)
  {
    var SOUS_TUTEL_ID = id;
    var EXECUTION_BUDGETAIRE_ID=EXECUTION_BUDGETAIRE_ID;

    $("#tache").modal("show");

    var row_count ="1000000";
    table=$("#mytable3").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "oreder":[],
      "ajax":{
        url:"<?= base_url('/double_commande_new/Liquidation_Salaire_Liste/listing_tache')?>",
        type:"POST",
        data: {
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID
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
  } 
</script>

<div class="modal" id="tache" role="dialog">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title text-center" id="exampleModalLabel">
          <?=lang('messages_lang.liste_tache')?>
        </h3>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id='mytable3' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
             <tr class="text-uppercase" >
              <th><?=lang('messages_lang.th_tache')?></th>
              <th><?=lang('messages_lang.labelle_montant')?></th>
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