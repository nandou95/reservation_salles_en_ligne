<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php
                    $decaissement_a_faire="btn"; 
                    $decaissement_deja_fait="btn";

                    $recepion_brb ="btn";
                    $deja_reception_brb ="btn"; 
                    ?>
                    <?php include  'includes/Menu_Decaissement.php'; ?> 
                  </div>
               </div>
               <div class="card-body" style="margin-top: -20px">
               </div>                       
             </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
               <br>
               <div class="col-12 d-flex">
                
                <div class="col-9" style="float: left;">
                  <h1 class="header-title text-dark">
                    <?= lang('messages_lang.controle_besd_titre') ?><br>
                  </h1>
                </div>

              </div>
              <div class="row col-md-12">
                <div class="col-md-3">
                    <label for="Nom" class="form-label"><?= lang('messages_lang.labelle_institution') ?></label>
                    <select onchange="liste();sous_titre()" class="form-control select2" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                      <?php
                      foreach($institutions as $key)
                      {
                        if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                        {
                          echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                <div class="col-md-3">
                  <label><?= lang('messages_lang.sous_titre_decaisement') ?></label>
                  <select class="form-control select2" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="liste()" >
                    <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
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
                  
              <div class="card-body">
                <div style="margin-left: 15px" class="row">
                  <?php if (session()->getFlashKeys('alert')) : ?>
                  <div class="w-100 bg-success text-white text-center" id="message" >
                    <?php echo session()->getFlashdata('alert')['message']; ?>
                  </div>
                  <?php endif; ?>
                  <div style ="max-width: 15%;">
                    <a href="#" id="btnexportexcel" onclick="exporter_excel()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel"></span> Excel
                    </a>
                  </div>
                </div>

                <div class="table-responsive container ">
                  <div></div>
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr class="text-uppercase text-nowrap">
                          <th> <?= lang('messages_lang.titre_decaissement_th') ?> </th>
                          <th> <?= lang('messages_lang.date_entre_th') ?> </th>
                          <th> <?= lang('messages_lang.titre_libelle') ?> </th>
                          <th> <?= lang('messages_lang.beneficaire_th') ?> </th>
                          <th> <?= lang('messages_lang.banque_th') ?> </th>
                          <th> <?= lang('messages_lang.imputation_decaissement') ?> </th>
                          <th> <?= lang('messages_lang.labelle_ministre') ?></th>
                          <th> <?= lang('messages_lang.credit_accorde_th') ?> </th>
                          <th> <?= lang('messages_lang.solde_ligne_th') ?> </th>
                          <th> <?= lang('messages_lang.montant_engage_th') ?> </th>
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
    </main>
  </div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function()
  {
    liste();
  });

  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }

</script >

<script>
  function sous_titre()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    if(INSTITUTION_ID=='')
    {
      $('#SOUS_TUTEL_ID').html('<option value="">Sélectionner</option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/double_commande_new/Liste_Decaissement/get_sous_titre/"+INSTITUTION_ID,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#SOUS_TUTEL_ID').html(data.sous_tutel);
        }
      });

    }
  }
</script>

<script type="text/javascript">
  function liste() 
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val(); 
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val(); 
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Controles_Decaissement/controle_besd_listing')?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN
        }
      },

      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
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

<script type="text/javascript">
  function exporter_excel() {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexportexcel").href = "<?=base_url('double_commande_new/Controles_Decaissement/exporter_Excel_BESD')?>/"+INSTITUTION_ID+"/"+SOUS_TUTEL_ID+"/"+DATE_DEBUT+"/"+DATE_FIN;
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
                <th><?= lang('messages_lang.labelle_paiement') ?> </th>
                <th><?= lang('messages_lang.decaissement_decaissement') ?> </th>
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
        url:"<?= base_url('/double_commande_new/Liste_Decaissement/detail_task_dec')?>",
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
