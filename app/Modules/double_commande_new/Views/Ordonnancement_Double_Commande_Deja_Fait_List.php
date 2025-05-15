<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php 
  $session  = \Config\Services::session();
  $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
  $userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php 
                    $get_ordon_Afaire1="btn";
                    $get_ordon_Afaire_sup1="btn";
                    $get_ordon_deja_fait1="btn active";
                    $bordereau_spe ="btn";
                    $bordereau_deja_spe ="btn";
                    $get_ordon_AuCabinet1="btn";
                    $get_ordon_BorderCabinet1="btn";
                    $get_ordon_BonCED1="btn";
                    ?>
                    <?php include  'includes/Menu_Ordonnancement.php'; ?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px">
                </div>                       
              </div>
              <div style="box-shadow:rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <b style="font-size:20px" class="header-title text-black"><?=lang('messages_lang.titre_ordo_faits')?></b><br><br>
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-4">
                        <label><?=lang('messages_lang.Lab_jur_instit')?></label>
                        <select autofocus onchange="getSousTutel();listing_ordon_deja_fait()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                          <option value=""><?=lang('messages_lang.selection_message')?></option>
                          <?php foreach ($institutions_user as $keyinstitution) { ?>
                            <option value="<?=$keyinstitution->INSTITUTION_ID?>" <?=$first_element_id == $keyinstitution->INSTITUTION_ID ? "selected" : ""?>><?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                          <?php }?>
                        </select>
                        <font color="red" id="error_INSTITUTION_ID"></font>
                      </div>
                      <div class="col-md-4">
                        <label><?=lang('messages_lang.Lab_jur_tutel')?><span id="loading_sous_tutel"></span></label>
                        <select onchange="listing_ordon_deja_fait()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                          <option value="">--<?=lang('messages_lang.selection_message')?>--</option>
                        </select>
                      </div>
                      <div class="col-md-4" id="">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                        <select class="form-control" onchange="listing_ordon_deja_fait();" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                          <?php
                          foreach($annee_budgetaire as $key)
                          {
                            if($key->ANNEE_BUDGETAIRE_ID==$annee_actuel){
                              echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";

                            }else{
                              echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."' >".$key->ANNEE_DESCRIPTION."</option>";
                            }
                                                        
                          }
                          ?>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                        <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange=";get_date();listing_ordon_deja_fait()" max="<?=date('Y-m-d')?>" class="form-control">
                        <font color="red" id="error_DATE_DEBUT"></font>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                        <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="listing_ordon_deja_fait()" max="<?=date('Y-m-d')?>" class="form-control" disabled>
                      </div>
                    </div><br>
                    <div class="row">
                      <div style ="max-width: 15%;">
                        <a href="#" id="btnexportexcel" onclick="exporter_excel()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel"></span> Excel
                        </a>
                      </div>
                      <!-- <div class="col-md-6">
                        <a href="#" id="btnexport" onclick="exporter()" type="button" class="btn btn-primary"><span class="fa fa-file-pdf"></span> PDF</a>
                      </div> -->  
                    </div>
                  </div>
                  <div style="margin-left: 15px" class="row">
                      <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message" >
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="row">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th><?=lang('messages_lang.col_bon_eng')?></th>
                            <th><?=lang('messages_lang.col_imputation')?></th>
                            <th class="text-uppercase"><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
                            <th><?=lang('messages_lang.col_eng_budg')?></th>
                            <th><?=lang('messages_lang.col_eng_jur')?></th>
                            <th><?=lang('messages_lang.col_liquid')?></th>
                            <th><?=lang('messages_lang.titre_ordon')?></th>
                            <th><?=lang('messages_lang.col_option')?></th>
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
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function ()
  {
    listing_ordon_deja_fait();
  });
</script>
<script>
  function listing_ordon_deja_fait()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    change_count();
    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "oreder":[[ 0, 'desc' ]],
      "ajax":
      {
        url:"<?= base_url('double_commande_new/Ordonnancement_Double_Commande/listing_ordon_deja_fait')?>",
        type:"POST", 
        data:
        {
          INSTITUTION_ID:INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID
        } 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      order: [0,'desc'],
      dom: 'Bfrtlip',
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
  function exporter()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

    document.getElementById("btnexport").href = "<?=base_url('double_commande_new/Ordonnancement_Double_Commande/exporter_deja_ordonnance/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID;
  }
</script>
<script>
  function exporter_excel()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

    INSTITUTION_ID == 0 ? 
      $('#error_INSTITUTION_ID').text("Le champ est obligatoire")
      : $('#error_INSTITUTION_ID').text("");

    DATE_DEBUT == 0 ? 
      $('#error_DATE_DEBUT').text("Le champ est obligatoire")
      : $('#error_DATE_DEBUT').text("");

    if(INSTITUTION_ID == 0 || DATE_DEBUT == 0) return

    document.getElementById("btnexportexcel").href = "<?=base_url('double_commande_new/Ordonnancement_Double_Commande/exporter_Excel/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID;
  }
</script>
<script type="text/javascript">
  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }
</script>
<script type="text/javascript">
  function show_modal(id)
  {
    var message=$('#message'+id).html();
    $('#mess').html(message);
    var footer=$('#footer'+id).html();
    $('#foot').html(footer);
    $('#mydelete').modal('show');
  }
</script>
<!-- modal pour confirmer la correction -->
<div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div id="mess" class="modal-body">

      </div>
      <div id="foot" class="modal-footer">

      </div>
    </div>
  </div>
</div>
<!-- modal pour confirmer la correction -->



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
                <th class="text-uppercase"><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th class="text-uppercase"><?=lang('messages_lang.list_quantite')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.col_eng_budg')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.col_eng_jur')?></th>
                <th><?=lang('messages_lang.col_liquid')?></th>
                <th><?=lang('messages_lang.titre_ordon')?></th>
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
<script>
  function change_count()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

    $.post('<?=base_url('double_commande_new/Ordonnancement_Double_Commande/change_count')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID,
      SOUS_TUTEL_ID:SOUS_TUTEL_ID,
      DATE_DEBUT:DATE_DEBUT,
      DATE_FIN:DATE_FIN,
      ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
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

  <!--SCRIPT POUR LES TACHES------>
<script type="text/javascript">
  function get_task(id)
  {
   $("#multi_tache").modal("show");

   var row_count ="1000000";
   table=$("#mytable3").DataTable({
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "oreder":[],
    "ajax":{
      url:"<?= base_url('/double_commande_new/Ordonnancement_Double_Commande/detail_task')?>",
      type:"POST",
      data: {
        task_id:id
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