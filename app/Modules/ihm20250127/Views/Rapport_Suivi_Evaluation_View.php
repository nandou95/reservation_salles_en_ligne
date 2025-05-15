<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-8">
                      <h1 class="header-title text-black"><?=lang('messages_lang.title_suivi_evaluation')?>                     
                    </h1>
                  </div>
                </div>
              </div><br>
              <div class="car-body">
                <div class="row">
                  <div class="col-md-3">
                    <label><?=lang('messages_lang.label_trimestre')?></label>
                    <select class="form-control" name="TRIMESTRE_ID" id="TRIMESTRE_ID" onchange="liste(this.value);test()" >
                      <option value="5"><?=lang('messages_lang.labelle_selecte')?></option>
                      <?php
                      foreach($tranches as $key_trimestre)
                      {
                        if($key_trimestre->TRIMESTRE_ID==set_value('TRIMESTRE_ID'))
                        {
                          echo "<option value='".$key_trimestre->TRIMESTRE_ID."'  selected>".$key_trimestre->DESC_TRIMESTRE."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key_trimestre->TRIMESTRE_ID."' >".$key_trimestre->DESC_TRIMESTRE."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label><?=lang('messages_lang.labelle_responsable')?></label>

                    <select class="form-control select2" name="RESPONSABLE" id="RESPONSABLE" onchange="liste(this.value);get_programme();" >
                       <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                      <?php
                      foreach($structure_responsable as $key)
                      {
                        if($key->STRUTURE_RESPONSABLE_TACHE_ID==$RESPO)
                        {
                          echo "<option value='".$key->STRUTURE_RESPONSABLE_TACHE_ID."'  selected>".$key->DESC_STRUTURE_RESPONSABLE_TACHE."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->STRUTURE_RESPONSABLE_TACHE_ID."' >".$key->DESC_STRUTURE_RESPONSABLE_TACHE."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label id="id_pgm"><?=lang('messages_lang.labelle_programme')?></label>
                    <select class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="liste(this.value);get_action();" >
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                    </select>
                  </div>
                  <div class="col-md-3" id="div_action">
                    <label><?=lang('messages_lang.labelle_action')?></label>
                    <select class="form-control" name="ACTION_ID" id="ACTION_ID" onchange="liste(this.value);get_imputation();" >
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label><?=lang('messages_lang.labelle_code_budgetaire')?></label>
                    <select class="form-control select2" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" onchange="liste(this.value);" >
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label><?=lang('messages_lang.select_anne_budget')?></label>
                    <select class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID" onchange="liste(this.value);" >
                      <?php
                      foreach($anne_budgetaire as $key_anne)
                      {
                        if($key_anne->ANNEE_BUDGETAIRE_ID==$ANNEE_BUDGETAIRE_ID)
                        {
                          echo "<option value='".$key_anne->ANNEE_BUDGETAIRE_ID."'  selected>".$key_anne->ANNEE_DESCRIPTION."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key_anne->ANNEE_BUDGETAIRE_ID."' >".$key_anne->ANNEE_DESCRIPTION."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                    <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange="liste();get_date()" max="<?=date('Y-m-d')?>" class="form-control">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                    <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control">
                  </div>
                  <div class="col-md-12">
                    <a href="#" id="btnexport" onclick="test()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>Excel</a>
                    <!-- <a href="#" id="btnexport_pdf" onclick="exporter_pdf()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>PDF</a>
                    <a href="#" id="btnexport_wrd" onclick="exporter_word()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>Word</a> -->
                  </div>

                </div><br>

                <div class="table-responsive" style="width: 100%;">
                  <table id="mytable" class=" table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th class="text-uppercase" style="white-space: nowrap;" >RESPONSABLE</th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_programme')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_action')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_code_budgetaire')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_tache_prevu')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_intitule_resultat_attendu')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_budget_vote')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_transfert_credit')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_credit_transfert')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_engag_budg')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_engag_jurd')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_liquidation')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ordonancement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.titre_paiement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_decaissement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_budg')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_jurid')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_liquid')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_ordona')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.ecart_paiement_emd')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_decaiss')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_budgetaire')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_jurdique')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_juridique')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_ordonnancement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_paiement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_taux_decaissement')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_resultat_realise')?></th>
                        <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ecart_physique')?></th>
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
<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function ()
  {
    $("#div_action").show();
    liste();
    get_programme()
  });
</script>
<script>
  function liste()
  {
    var RESPONSABLE=$('#RESPONSABLE').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[],
      "oreder":[[ 1, 'asc' ]],
      "ajax":
      {
        url:"<?= base_url('ihm/Rapport_Suivi_Evaluation/liste')?>",
        type:"POST", 
        data:
        {
          RESPONSABLE:RESPONSABLE,
          PROGRAMME_ID:PROGRAMME_ID,
          ACTION_ID:ACTION_ID,
          CODE_NOMENCLATURE_BUDGETAIRE_ID:CODE_NOMENCLATURE_BUDGETAIRE_ID,
          TRIMESTRE_ID:TRIMESTRE_ID,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN
        } 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      order: [0,'asc'],
      dom: 'Bfrtlip',
      buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
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
  function get_imputation()
  {
    var RESPONSABLE=$('#RESPONSABLE').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    if(ACTION_ID=='')
    {
      $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/ihm/Rapport_Suivi_Evaluation/get_imputation/"+RESPONSABLE+"/"+PROGRAMME_ID+"/"+ACTION_ID,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html(data.imputation);
        }
      });
    }
  }

  function get_programme()
  {
    var RESPONSABLE=$('#RESPONSABLE').val();
    if(RESPONSABLE=='')
    {
      $('#PROGRAMME_ID').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
      $('#ACTION_ID').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/ihm/Rapport_Suivi_Evaluation/get_programme/"+RESPONSABLE,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#PROGRAMME_ID').html(data.programs);
        }
      });
    }
  }

  function get_action()
  {
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    if(PROGRAMME_ID=='')
    {
      $('#ACTION_ID').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/ihm/Rapport_Suivi_Evaluation/get_action/"+PROGRAMME_ID,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#ACTION_ID').html(data.actions);
        }
      });
    }
  }
</script>

<script type="text/javascript">
  function test()
  {
    var RESPONSABLE = $('#RESPONSABLE').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
    var ANNEE_BUDGETAIRE_ID = $('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();

    if (RESPONSABLE == '' || RESPONSABLE == null) {RESPONSABLE = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (CODE_NOMENCLATURE_BUDGETAIRE_ID == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == null) {CODE_NOMENCLATURE_BUDGETAIRE_ID = 0}
    if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexport").href = "<?=base_url('ihm/Rapport_Suivi_Evaluation/exporter_filtre/')?>"+'/'+RESPONSABLE+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+CODE_NOMENCLATURE_BUDGETAIRE_ID+'/'+TRIMESTRE_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN;
  }
</script>

<script type="text/javascript">
  ///export dans un word
  function exporter_word()
  {
    var RESPONSABLE = $('#RESPONSABLE').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
    var ANNEE_BUDGETAIRE_ID = $('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();

    if (RESPONSABLE == '' || RESPONSABLE == null) {RESPONSABLE = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (CODE_NOMENCLATURE_BUDGETAIRE_ID == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == null) {CODE_NOMENCLATURE_BUDGETAIRE_ID = 0}
    if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexport_wrd").href = "<?=base_url('ihm/Rapport_Suivi_Evaluation/exporter_word/')?>"+'/'+RESPONSABLE+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+CODE_NOMENCLATURE_BUDGETAIRE_ID+'/'+TRIMESTRE_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN;
  }
</script>

<script type="text/javascript">
  ///export dans un pdf
  function exporter_pdf()
  {
    var RESPONSABLE = $('#RESPONSABLE').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
    var ANNEE_BUDGETAIRE_ID = $('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    if (RESPONSABLE == '' || RESPONSABLE == null) {RESPONSABLE = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (CODE_NOMENCLATURE_BUDGETAIRE_ID == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == null) {CODE_NOMENCLATURE_BUDGETAIRE_ID = 0}
    if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexport_pdf").href = "<?=base_url('ihm/Rapport_Suivi_Evaluation/exporter_pdf/')?>"+'/'+RESPONSABLE+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+CODE_NOMENCLATURE_BUDGETAIRE_ID+'/'+TRIMESTRE_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN;
  }
</script>
<script type="text/javascript">
  function get_date()
  {
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
  }
</script>