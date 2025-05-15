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
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">

                </div>
                <div class="card-body">
                  <div style="margin-top: -25px;" class="card">
                  </div>
                  <div class="card-body" style="margin-top: -20px">
                    <div style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Liste_Paiement') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.link_list') ?></a>
                    </div>
                    <div>
                      <font style="font-size:18px,color:#333">
                        <h2> <?= lang('messages_lang.title_global') ?> : <?php if (!empty($etapes)) { ?>
                            <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                          <?php    } ?> </h2>
                      </font>
                    </div>
                    <br>
                    <hr>

                    <div class="table-responsive container ">
                      <form action="<?= base_url("double_commande_new/Reception_borderau_brb/insertion_histo") ?>" method="post" id="push_bon">

                        <input type="hidden" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION" value="<?= $date_transmission['DATE_TRANSMISSION'] ?>">
                        <input type="hidden" id="ID_ETAPE_COMMANDE" name="ID_ETAPE_COMMANDE" value="<?= $etape_commande['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                        <input type="hidden" id="RACCROCHAGE_ID" name="RACCROCHAGE_ID" value="<?= $RACCROCHAGE_ID['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                        <input type="hidden" id="BORDEREAU_TRANSMISSION_ID" name="BORDEREAU_TRANSMISSION_ID" value="<?= $etape_commande['BORDEREAU_TRANSMISSION_ID'] ?>">
                        <input type="hidden" name="STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID" id="STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID" value="<?= $statut_operation_bordereau['STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID'] ?>">


                        <?php $validation = \Config\Services::validation(); ?>

                        <div class="row">
                          <div class="col-6">
                            <label for=""> <?= lang('messages_lang.label_numero_bordereau') ?> </label>
                            <input type="text" class="form-control" name="NUM_BORD_TRANS" id="NUM_BORD_TRANS" value=<?= $numero_bordereau ?> readonly>
                            <span class="error" style="color: red; font-size: 13px"></span>
                          </div>
                          <br>

                          <div class="col-md-6">
                            <label for=""> <?= lang('messages_lang.label_date_reception') ?></label>
                            <input type="date" class="form-control" name="DATE_RECEPTION" id="DATE_RECEPTION" min=<?= $date_transmission['DATE_TRANSMISSION'] ?> max="<?= date("Y-m-d") ?>">
                            <span class="error" id="error_DATE_RECEPTION" style="color: red; font-size: 13px"></span>
                          </div>
                          <br>

                          <div class="col-6" style="display: none;"><br>
                            <label><?= lang('messages_lang.label_numero_titre_decaissement') ?><span style="color: red;">*</span></label>
                            <select class="form-control select2" multiple name="titre_decaissement[]" id="titre_decaissement">
                              <option value=""><?= lang('messages_lang.label_selecte') ?></option>
                              <?php foreach ($titre_decaissement as $row) { ?>
                              <option value="<?= $row->EXECUTION_BUDGETAIRE_DETAIL_ID ?>" selected><?= $row->NUMERO_DOCUMENT ?></option>
                              <?php } ?>
                            </select>
                            <font color="red" id="error_TITRE_DECAISSEMENT"></font>
                          </div>

                          <div class="col-6"><br>
                            <label><?= lang('messages_lang.label_numero_titre_decaissement') ?><span style="color: red;">*</span></label>
                            <select class="form-control select2" multiple name="titre_decaissement1[]" id="titre_decaissement1" disabled>
                              <option value=""><?= lang('messages_lang.label_selecte') ?></option>
                              <?php foreach ($titre_decaissement as $row) { ?>
                              <option value="<?= $row->EXECUTION_BUDGETAIRE_DETAIL_ID ?>" selected><?= $row->NUMERO_DOCUMENT ?></option>
                              <?php } ?>
                            </select>
                            <font color="red" id="error_TITRE_DECAISSEMENT"></font>
                          </div>
                        </div>
                        <br>

                        <div style="float:right" class="mt-4">
                          <a class="btn btn-primary" onclick="save_titre();" class="form-control"><?= lang('messages_lang.bouton_enregistrer') ?></a>
                        </div>
                      </form>
                    </div>
                  </div>

                  <div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.bouton_enregistrer') ?></h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <div class="table-responsive overflow-auto mt-2">
                            <table class=" table  m-b-0 m-t-20">
                              <tbody>
                                <tr>
                                  <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.label_date_reception') ?></td>
                                  <td id="date_reception_modal"></td>
                                </tr>

                                <tr>
                                  <td> <i class="fa fa-file-text"></i> <?= lang('messages_lang.label_bordereau_reception') ?></td>
                                  <td id="numero_bordereau_modal"></td>
                                </tr>

                                <tr>
                                  <td> <i class="fa fa-list"></i> <?= lang('messages_lang.label_numero_titre_decaissement') ?></td>
                                  <td id="titre_decaissement_modal"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier') ?></button>
                          <a id="myElement" onclick="save_info();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer') ?></a>
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
  function save_titre()
  {
    var statut = true;
    var date_reception = $('#DATE_RECEPTION').val();
    var numero_bordereau = $('#NUM_BORD_TRANS').val();
    var titre_decaissement = $('#titre_decaissement').val();

    if (date_reception == "") {
      statut = false;
      $("#error_DATE_RECEPTION").html("<?= lang('messages_lang.validation_message') ?>");
    } else {
      $("#error_DATE_RECEPTION").html("");
    }

    if (titre_decaissement == "") {
      statut = false;
      $("#error_TITRE_DECAISSEMENT").html("<?= lang('messages_lang.validation_message') ?>");
    } else {
      $("#error_TITRE_DECAISSEMENT").html("");
    }

    if (statut == true) {
      var date = moment(date_reception, "YYYY/mm/DD")
      var reception_date = date.format('DD/mm/YYYY')

      $("#date_reception_modal").html(reception_date);
      $("#numero_bordereau_modal").html(numero_bordereau);
      var titre_decaissement = $('#titre_decaissement option:selected').toArray().map(item => '<li>' + item.text + '</li>').join("");
      $('#titre_decaissement_modal').html('<ul>' + titre_decaissement + '</ul>');

      $('#detail').modal('show');
    }
  }
</script>

<script>
  function save_info() {
    $('#push_bon').submit()
  }
</script>