<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
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
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3> <?=$titre?></h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Ordonnancement_Ministre/liste') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.list_transmission_du_bordereau') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
               <br>
                <div id="collapseThree" class="collapse col-md-12" aria-labelledby="headingThree" data-parent="#accordion">
                 
                </div>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Ordonnancement_Ministre/save') ?>" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id_etape" id="id_etape" value="<?=$etape?>">

                    <div class="row col-md-12">
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.bon_engagement_transmission_du_bordereau') ?> <span style="color: red;">*</span></label>
                        <select class="form-control select2" multiple name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID">
                          <option value=""><?= lang('messages_lang.selectionner_transmission_du_bordereau') ?></option>
                          <?php  foreach ($get_info as $keys) { ?>
                            <?php if($keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID==set_value('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID')) { ?>
                              <option value="<?=$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>" selected>
                                <?=$keys->NUMERO_BON_ENGAGEMENT ?>)</option>
                              <?php }else{?>
                               <option value="<?=$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>">
                                <?=$keys->NUMERO_BON_ENGAGEMENT ?>)</option>
                              <?php } }?>
                            </select>
                            <span id="error_bon_engagement" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'); ?>
                            <?php endif ?>
                          </div>

                          <div class="col-md-6">
                            <br>
                            <div class="">
                              <label for=""> <?= lang('messages_lang.lab_d_rec') ?> <span style="color: red;">*</span></label>
                              <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION" value="<?=date('Y-m-d')?>" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onchange="get_date_min_trans()">
                              <span class="text-danger" id="error_rec"></span>
                              <?= $validation->getError('DATE_RECEPTION'); ?>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <br>
                            <label><?= lang('messages_lang.lab_d_trans') ?><span style="color: red;">*</span></label>
                            <input type="date" class="form-control" name="DATE_TRANSMISSION" value="<?=date('Y-m-d')?>" max="<?= date('Y-m-d') ?>" id="DATE_TRANSMISSION" onkeypress="return false" onblur="this.type='date'">
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('DATE_TRANSMISSION'); ?>
                            <?php endif ?>
                            <span id="error_dat_trans" class="text-danger"></span>
                          </div>
                      </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.enregistre_transmission_du_bordereau') ?></a>
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

    <div class="modal fade" id="prep_projet" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_transmission_du_bordereau') ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="table-responsive  mt-3">
              <table class="table m-b-0 m-t-20">
                <tbody>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.date_de_reception_transmission_du_bordereau') ?></strong></td>
                    <td id="DATE_RECEPTION_VERIFY" class="text-dark"></td>
                  </tr>

                  <tr>
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.bon_engagement_transmission_du_bordereau') ?></strong></td>
                    <td id="BON_ENGAGEMENT_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.date_transmission_transmission_du_bordereau') ?></strong></td>
                    <td id="dat_trans_VERIFY" class="text-dark"></td>
                  </tr>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <div class="row">
            <button id="mod" type="button" class="btn btn-primary" style="margin-top:10px" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?></button>
            <a id="myElement" onclick="save_etap2();hideButton()" style="float: right; margin-top:10px" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> <?= lang('messages_lang.confirmer_transmission_du_bordereau') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>

<script>
  //function pour donner la date minimum de transmission
  function get_date_min_trans() {
    $("#DATE_TRANSMISSION").prop('min', $("#DATE_RECEPTION").val());
  }
</script>

<script type="text/javascript">
  function save() {
  var statut = 2;

  var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
  var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();
  var DATE_RECEPTION = $('#DATE_RECEPTION').val();

  $('#error_rec').html('');
  $('#error_bon_engagement').html('');
  $('#error_dat_trans').html('');

  if (DATE_TRANSMISSION === '') {
    statut = 1;
    $('#error_dat_trans').html("<?= lang('messages_lang.identique_transmission_du_bordereau') ?>");
  } else {
    $('#error_dat_trans').html("");
  }

  if (DATE_RECEPTION === '') {
    $('#error_rec').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
    statut = 1;
  }

  if (EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID === '') {
    $('#error_bon_engagement').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
    statut = 1;
  }

  if (statut === 2) 
  {
    var date = moment(DATE_RECEPTION, "YYYY/MM/DD");
    var result = date.format("DD/MM/YYYY");

    $('#DATE_RECEPTION_VERIFY').html(result);
    $('#prep_projet').modal('show');

    var RACCROCHAGE = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID option:selected')
      .toArray()
      .map(item => `<li>${item.text}</li>`)
      .join('');
    var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${RACCROCHAGE}</ol>`;
    $('#BON_ENGAGEMENT_VERIFY').html(orderedList);

    var datetrans = moment(DATE_TRANSMISSION, "YYYY/MM/DD");
    var dte = datetrans.format("DD/MM/YYYY");
    $('#dat_trans_VERIFY').html(dte);
  }
}
</script>

<script type="text/javascript">
  function save_etap2() {
    $('#my_form').submit()
  }
</script>