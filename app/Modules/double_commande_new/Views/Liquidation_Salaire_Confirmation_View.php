  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation(); ?>
  </head>
  <style>
    hr.vertical {
      border:         none;
      border-left:    1px solid hsla(200, 2%, 12%,100);
      height:         55vh;
      width: 1px;
      color: #ddd
    }
  </style>

  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php'); ?>
      <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
      <script src="/DataTables/datatables.js"></script>
      <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
      <div class="main">
        <?php echo view('includesbackend/navybar_topbar.php'); ?>
        
        <main class="content">
          <div class="container-fluid">
            <div class="header">
              <h1 class="header-title text-white"></h1>
            </div>
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div style="float: right;">
                    <a href="<?php echo base_url('double_commande_new/Liquidation_Salaire_Liste/index_A_valider')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape2?></h4>
                    <br>
                  </div>
                  <br><br>
                  <!-- fin -->
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="myEtape2" id="myEtape2" action="<?=base_url('double_commande_new/Liquidation_Salaire/save_confirm')?>" method="post" >
                      <div class="container">
                        
                        <?php
                        if(session()->getFlashKeys('alert'))
                        {
                          ?>
                          <center class="ml-5" style="height=100px;width:90%" >
                            <div class="w-100 bg-danger text-white text-center"  id="message">
                              <?php echo session()->getFlashdata('alert')['message']; ?>
                            </div>
                          </center>
                          <?php
                        } ?>

                        <div class="row" style="border:1px solid #ddd;border-radius:5px">
                          <div class="col-md-12 mt-2" style="margin-bottom:50px">
                            <div class="col-md-12" id="tableau">
                              <input type="hidden" name="getdata" id="getdata">
                              <table class="table table-responsive" id="mytable">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th><?=lang('messages_lang.th_instit')?></th>
                                    <th class="text-uppercase"><?=lang('messages_lang.categorie_salarie')?></th>
                                    <th class="text-uppercase"><?=lang('messages_lang.th_sous_tut')?></th>
                                    <th class="text-uppercase"><?=lang('messages_lang.liquidation_decaissement')?></th>                                    
                                  </tr>
                                </thead>
                                <tbody>                                
                                </tbody>
                              </table>
                            </div>

                            <div class="row">

                              <input type="hidden" id="EXECUTION_BUDGETAIRE_ID" name="EXECUTION_BUDGETAIRE_ID" value="<?=$EXECUTION_BUDGETAIRE_ID?>">
                              <input type="hidden" id="EXECUTION_BUDGETAIRE_DETAIL_ID" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                              <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">
                              <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                            </div>
                          <div class="row">
                            <!-- <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_date_rec') ?> <font color="red">*</font></label>
                              <input type="date" value="<?= date('Y-m-d')?>" min="<?//=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?//= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                              <font color="red" id="error_DATE_RECEPTION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_RECEPTION'); ?>
                              <?php endif ?>
                            </div> -->
                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_date_rec') ?> <font color="red">*</font></label>
                              <input type="date" value="<?= date('Y-m-d')?>" min="<?//=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                              <font color="red" id="error_DATE_RECEPTION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_RECEPTION'); ?>
                              <?php endif ?>
                            </div>
                            <div class="col-md-6">
                              <div class='form-froup'>
                                <label class="form-label"><?= lang('messages_lang.label_decision') ?> <font color="red">*</font></label>
                                <select onchange="rejet();" class="form-control" name="ID_OPERATION" id="ID_OPERATION">
                                  <option value=""><?= lang('messages_lang.label_select') ?></option>
                                  <?php  foreach ($get_operation as $keys) { ?>
                                    <?php if($keys->ID_OPERATION==set_value('ID_OPERATION')) { ?>
                                      <option value="<?=$keys->ID_OPERATION ?>" selected>
                                        <?=$keys->DESCRIPTION?></option>
                                      <?php }else{?>
                                       <option value="<?=$keys->ID_OPERATION ?>">
                                        <?=$keys->DESCRIPTION?></option>
                                      <?php } }?>
                                    </select>
                                    <font color="red" id="error_ID_OPERATION"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('ID_OPERATION'); ?>
                                    <?php endif ?>
                                  </div>
                                  <br>
                                </div>
                                <div class="col-md-6" id="motive" hidden="true">
                                  <label for=""> <?= lang('messages_lang.label_retour') ?></label><font color="red">*</font><span id="loading_motif"></span>
                                  <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                   
                                   <?php
                                   foreach($get_motif as $value)
                                   { 
                                    if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                    <?php }else                                
                                    {
                                      ?>
                                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>"><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                      <?php
                                    }
                                  }
                                  ?>
                                  <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                </select>
                                <br>
                                <span id="autre_motif" class="col-md-12 row" style="display: none">
                                  <div class="col-md-9">
                                    <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_SERIE">
                                  </div>
                                  <div class="col-md-2" style="margin-left: 5px;">
                                    <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                                  </div>
                                </span>
                                <font color="red" id="error_motif"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('motive'); ?>
                                <?php endif ?>
                              </div>
                              <div class="col-md-6" id="st" hidden="true">
                                <label for=""> <?= lang('messages_lang.st_a_corrige') ?></label><font color="red">*</font>
                                <select class="form-control select2" name="SOUS_TUTEL_ID[]" id="SOUS_TUTEL_ID" multiple>
                                 <option value="-1"><?=lang('messages_lang.label_select')?></option>
                                 <?php
                                 foreach($get_st_a_corriger as $value)
                                 { 
                                  if($value->SOUS_TUTEL_ID==set_value('SOUS_TUTEL_ID')){?>
                                    <option value="<?=$value->SOUS_TUTEL_ID ?>" selected><?=$value->CODE_INSTITUTION_SOUS_TUTEL.' -'.$value->DESCRIPTION_SOUS_TUTEL?></option>
                                  <?php }else                                
                                  {
                                    ?>
                                    <option value="<?=$value->SOUS_TUTEL_ID ?>"><?=$value->CODE_INSTITUTION_SOUS_TUTEL.' -'.$value->DESCRIPTION_SOUS_TUTEL?></option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <font color="red" id="error_SOUS_TUTEL_ID"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                              <?php endif ?>
                            </div>
                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.label_date_tra') ?><font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION">
                                <font color="red" id="error_DATE_TRANSMISSION"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_TRANSMISSION'); ?>
                                <?php endif ?>
                                <br>
                              </div>

                              <div class="col-md-6">
                                <label for=""> <?= lang('messages_lang.label_observ') ?> <font color="red" id="required"></font></label>
                                <textarea maxlength="255" class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"></textarea>
                                <font color="red" id="error_COMMENTAIRE"></font>
                              </div>                             
                            </div>
                          <div style="float: right;" class="col-md-2 mt-5 " >
                            <div class="form-group " >
                              <a onclick="saveEtape2()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"> <?= lang('messages_lang.label_enre') ?></a>
                            </div>
                          </div>
                        </div>                         
                      </div>
                    </form><br><br>
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
  $(document).ready(function () {
    liste();
  });
</script>
<script type="text/javascript">
  function changeDate()
  {
    $('#DATE_TRANSMISSION').prop('min', $('#DATE_RECEPTION').val());
  }
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
</script >
<script type="text/javascript">
 function rejet(){
  var ID_OPERATION =$('#ID_OPERATION').val() ;  

  if (ID_OPERATION==1)
  {
    $('#motive').attr('hidden',false);
    $('#st').attr('hidden',false);
  }
  else
  {
    $('#motive').attr('hidden',true);
    $('#st').attr('hidden',true);
  }

  if (ID_OPERATION==3)
  {
    $('#required').text("*");
  }else{
    $('#required').text("");
  }

  $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
  $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
  $('#autre_motif').delay(100).hide('show');
}
</script>

<script type="text/javascript">
  function saveEtape2()
  {
    var COMMENTAIRE= $('#COMMENTAIRE').val();
    var ID_OPERATION = $('#ID_OPERATION').val();

    $('#error_ID_OPERATION').html('');
    $('#error_COMMENTAIRE').html('');

    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();
    $('#error_motif').html('');

    var TYPE_ANALYSE_ID = $('#TYPE_ANALYSE_ID').val();
    $('#error_TYPE_ANALYSE_ID').html('');

    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    $('#error_DATE_RECEPTION').html('');   
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    $('#error_SOUS_TUTEL_ID').html('');

    var statut=2;

    if (ID_OPERATION==1)
    {
      if (TYPE_ANALYSE_MOTIF_ID=='')
      {
        $('#error_motif').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
      if (SOUS_TUTEL_ID=='')
      {
        $('#error_SOUS_TUTEL_ID').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
    }

    if (ID_OPERATION==3)
    {
      if (COMMENTAIRE=='')
      {
        $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
    }

    if (TYPE_ANALYSE_ID=='')
    {
      $('#error_TYPE_ANALYSE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (DATE_TRANSMISSION=='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (ID_OPERATION=='')
    {
      $('#error_ID_OPERATION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var url;
    if(statut == 2)
    {
      if (ID_OPERATION == 1)
      {
        $('#rej_eng').show();
        $('#st_corr').show();
      }
      else
      {
        $('#rej_eng').hide();
        $('#st_corr').hide();
      }

      var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");

      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

      var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');

      var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${TYPE_ANALYSE_MOTIF_ID}</ol>`;
      var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');

      SOUS_TUTEL_ID = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${SOUS_TUTEL_ID}</ol>`;

      $('#motif_verifie').html(orderedList);
      $('#SOUS_TUTEL_ID_verifie').html(SOUS_TUTEL_ID);

      $('#ID_OPERATION_verifie').html($('#ID_OPERATION option:selected').text());
      $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);
      $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);

      if(COMMENTAIRE != '')
      {
        $('#COMMENTAIRE_verifie').html(COMMENTAIRE);
      }else{
        $('#COMMENTAIRE_verifie').html('<b>-</b>');
      }
      $("#etape2_modal").modal("show");
    }
  }

</script>

<div class="modal fade" id="etape2_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
     <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <div class="table-responsive  mt-3">
        <table class="table m-b-0 m-t-20">
          <tbody>
            <tr>
              <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.label_decision') ?></strong></td>
              <td id="ID_OPERATION_verifie" class="text-dark"></td>
            </tr>
            <tr id="rej_eng">
              <td><i class="fa fa-certificate"></i> &nbsp;<strong><?= lang('messages_lang.label_retour') ?></strong></td>
              <td id="motif_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?></strong></td>
              <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?></strong></td>
              <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.label_observ') ?></strong></td>
              <td id="COMMENTAIRE_verifie" class="text-dark"></td>
            </tr>
            <tr id="st_corr">
              <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.st_a_corrige') ?></strong></td>
              <td id="SOUS_TUTEL_ID_verifie" class="text-dark"></td>
            </tr>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" id="edi" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
    <a onclick="save_etap2();hideButton()" id="conf" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
  </div>
</div>
</div>
</div>


<script type="text/javascript">
  function save_etap2()
  {
    document.getElementById("myEtape2").submit();
  }
</script>

<script>
  function hideButton()
  {
    var element = document.getElementById("conf");
    element.style.display = "none";

    var elementmod = document.getElementById("edi");
    elementmod.style.display = "none";
  }
</script>
<script type="text/javascript">
  function liste() 
  {
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liquidation_Salaire/listing_st') ?>",
        type: "POST",
        data: {
          EXECUTION_BUDGETAIRE_ID: EXECUTION_BUDGETAIRE_ID,
        }
      },

      lengthMenu: [
        [5,10, 50, 100, row_count],
        [5,10, 50, 100, "All"]
      ],
      pageLength: 5,
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
  function listing_par_sous_titre(EXECUTION_BUDGETAIRE_ID,SOUS_TUTEL_ID)
  {
    var EXECUTION_BUDGETAIRE_ID=EXECUTION_BUDGETAIRE_ID;
    var SOUS_TUTEL_ID=SOUS_TUTEL_ID;

    $("#tache").modal("show");

    var row_count ="1000000";
    table=$("#mytable3").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "oreder":[],
      "ajax":{
        url:"<?= base_url('/double_commande_new/Liquidation_Salaire/listing_par_sous_titre')?>",
        type:"POST",
        data: {
          EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID
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
<script type="text/javascript">
  function getAutreMotif(id = 0)
  {
    var selectElement = document.getElementById("TYPE_ANALYSE_MOTIF_ID");
    if (id.includes("-1"))
    {
      $('#autre_motif').delay(100).show('hide');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      disableOptions(selectElement);
    }
    else
    {
      $('#autre_motif').delay(100).hide('show');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      enableOptions(selectElement);
    }
  }

  function disableOptions(selectElement)
  {
    for (var i = 0; i < selectElement.options.length; i++)
    {
      if (selectElement.options[i].value !== "-1")
      {
        selectElement.options[i].disabled = true;
      }
    }
  }

  function enableOptions(selectElement)
  {
    for (var i = 0; i < selectElement.options.length; i++)
    {
      selectElement.options[i].disabled = false;
    }
  }

  function save_newMotif()
  {
    var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
    var statut = 2;
    if(DESCRIPTION_MOTIF == "")
    {
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Liquidation_Salaire/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF
        },
        beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#TYPE_ANALYSE_MOTIF_ID').html(data.motifs);
          TYPE_ANALYSE_MOTIF_ID.InnerHtml=data.motifs;
          $('#loading_motif').html("");
          $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          $('#autre_motif').delay(100).hide('show');
        }
      });
    }
  }
</script>