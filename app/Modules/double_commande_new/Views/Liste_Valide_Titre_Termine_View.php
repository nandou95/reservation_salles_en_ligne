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
                    $valid_faire = "btn";
                    $valid_termnine = "btn active";

                    $get_nbr_av_obr1="btn";
                    $get_nbr_av_pc1="btn";
                    
                    $reception_obr = "btn";
                    $prise_charge_compt = "btn";
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
                    <?= lang('messages_lang.titre_termine') ?> 
                    </h1>
                  </div>
                </div>
                <div class="row col-md-12">
                  <div class="col-md-3">
                  <label for="Nom" class="form-label"> <?= lang('messages_lang.labelle_institution') ?> </label>
                    <select autofocus onchange="liste();sous_titre()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                      <option value=""><?=lang('messages_lang.selection_message')?></option>
                      <?php foreach ($institutions as $key) { ?>
                        <option value="<?=$key->INSTITUTION_ID?>" <?=$first_element_id == $key->INSTITUTION_ID ? "selected" : ""?>><?=$key->DESCRIPTION_INSTITUTION?></option>
                      <?php }?>
                    </select>
                    <font color="red" id="error_INSTITUTION_ID"></font>
                  </div>
                  <div class="col-md-3">
                  <label> <?= lang('messages_lang.sous_titre_decaisement') ?> </label>
                    <select class="form-control select2" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="liste()">
                    <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                    </select>
                  </div>
                  <div class="col-md-2" id="">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                        <select class="form-control" onchange="liste();" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                          <?php
                          foreach($annee_budgetaire as $key)
                          {
                            echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";                            
                          }
                          ?>
                        </select>
                    </div>
                  <div class="col-md-2">
                    <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                    <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange=";get_date();liste()" max="<?=date('Y-m-d')?>" class="form-control">
                    <font color="red" id="error_DATE_DEBUT"></font>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                    <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control" disabled>
                  </div>
                 
                </div>
           

                <div class="card-body">

                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>

                    <div style ="max-width: 15%;">
                      <a href="#" id="btnexportexcel" onclick="exporter_excel()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel"></span> Excel
                      </a>
                    </div>
                    <!-- <div style ="max-width: 15%;">
                      <a href="#" id="btnexportpdf" onclick="exporter_pdf()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-pdf"></span> Pdf</a>

                    </div> -->
                  </div>

                  <div class="table-responsive container ">

                    <table id="valid_termine" class=" table table-bordered table-striped">
                      <thead>
                      <tr class="text-uppercase text-nowrap">
                          
                          <th> <?= lang('messages_lang.titre_decaissement') ?> </th>
                          <th> <?= lang("messages_lang.labelle_devise") ?> </th>
                          <th> <?= lang('messages_lang.col_eng_budg') ?> </th>
                          <th> <?= lang('messages_lang.col_eng_jur') ?> </th>
                          <th> <?= lang('messages_lang.col_liquid') ?> </th>
                          <th> <?= lang('messages_lang.labelle_ordonan') ?> </th>
                          <th> <?= lang('messages_lang.labelle_paiement') ?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
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

  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }
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
    change_count()
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var row_count = "1000000";
    $("#valid_termine").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Validation_Titre/liste_validation_termine') ?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID: SOUS_TUTEL_ID,
          ANNEE_BUDGETAIRE_ID: ANNEE_BUDGETAIRE_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN
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

<script>
  function change_count()
  {
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    ANNEE_BUDGETAIRE_ID=(ANNEE_BUDGETAIRE_ID!='')?ANNEE_BUDGETAIRE_ID:0
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    INSTITUTION_ID=(INSTITUTION_ID!='')?INSTITUTION_ID:0
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    SOUS_TUTEL_ID=(SOUS_TUTEL_ID!='')?SOUS_TUTEL_ID:0
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    DATE_DEBUT=(DATE_DEBUT!='')?DATE_DEBUT:0
    var DATE_FIN=$('#DATE_FIN').val();
    DATE_FIN=(DATE_FIN!='')?DATE_FIN:0

    $.post('<?=base_url('double_commande_new/Liste_Paiement/change_count')?>',
    {
      ANNEE_BUDGETAIRE_ID,
      INSTITUTION_ID,
      SOUS_TUTEL_ID,
      DATE_DEBUT,
      DATE_FIN
    },
    function(data)
    {
      $('#recep_prise_charge').html(data.recep_prise_charge);
      $('#deja_recep_prise_charge').html(data.deja_recep_prise_charge);
      $('#get_nbr_av_obr').html(data.get_nbr_av_obr);      
      $('#get_recep_obr').html(data.get_recep_obr);      
      $('#get_nbr_av_pc').html(data.get_nbr_av_pc);      
      $('#get_prise_charge').html(data.get_prise_charge);      
      $('#get_prise_charge_corr').html(data.get_prise_charge_corr);     
      $('#get_etape_reject_pc').html(data.get_etape_reject_pc);      
      $('#get_etape_corr').html(data.get_etape_corr);      
      $('#get_etab_titre').html(data.get_etab_titre);      
      $('#get_recep_td_corriger').html(data.get_recep_td_corriger);     
      $('#get_etab_titre_corr').html(data.get_etab_titre_corr);      
      $('#get_bord_dc').html(data.get_bord_dc);      
      $('#get_bord_deja_dc').html(data.get_bord_deja_dc);      
      $('#recep_dir_comptable').html(data.recep_dir_comptable);      
      $('#deja_recep_dir_comptable').html(data.deja_recep_dir_comptable);      
      $('#get_sign_dir_compt').html(data.get_sign_dir_compt);      
      $('#get_sign_dir_dgfp').html(data.get_sign_dir_dgfp);      
      $('#get_sign_ministre').html(data.get_sign_ministre);      
      $('#get_titre_valide').html(data.get_titre_valide);      
      $('#get_titre_termine').html(data.get_titre_termine);      
      $('#get_bord_brb').html(data.get_bord_brb);      
      $('#get_bord_deja_trans_brb').html(data.get_bord_deja_trans_brb); 
    })
  }
</script>

 <!-- Claude -->
<script type="text/javascript">
  function exporter_excel() {

    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();
    
    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    INSTITUTION_ID == 0 ? 
      $('#error_INSTITUTION_ID').text("Le champ est obligatoire")
      : $('#error_INSTITUTION_ID').text("");

    DATE_DEBUT == 0 ? 
      $('#error_DATE_DEBUT').text("Le champ est obligatoire")
      : $('#error_DATE_DEBUT').text("");

    if(INSTITUTION_ID == 0 || DATE_DEBUT == 0) return

    document.getElementById("btnexportexcel").href = "<?=base_url('double_commande_new/Validation_Titre/exporter_Excel')?>/"+INSTITUTION_ID+"/"+SOUS_TUTEL_ID+"/"+ANNEE_BUDGETAIRE_ID+"/"+DATE_DEBUT+"/"+DATE_FIN;
  }

</script>

  <script type="text/javascript">
    function exporter_pdf() {

      var INSTITUTION_ID=$('#INSTITUTION_ID').val();
      var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
      var DATE_DEBUT=$('#DATE_DEBUT').val();
      var DATE_FIN=$('#DATE_FIN').val();
      
      if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
      if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
      if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
      if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

      INSTITUTION_ID == 0 ? 
      $('#error_INSTITUTION_ID').text("Le champ est obligatoire")
      : $('#error_INSTITUTION_ID').text("");

    DATE_DEBUT == 0 ? 
      $('#error_DATE_DEBUT').text("Le champ est obligatoire")
      : $('#error_DATE_DEBUT').text("");

    if(INSTITUTION_ID == 0 || DATE_DEBUT == 0) return


      document.getElementById("btnexportpdf").href = "<?=base_url('double_commande_new/Validation_Titre/generatePdf')?>/"+INSTITUTION_ID+"/"+SOUS_TUTEL_ID+"/"+DATE_DEBUT+"/"+DATE_FIN;
    }

  </script>