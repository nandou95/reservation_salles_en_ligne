<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-10">
                      <h3 class="header-title text-black"><?= lang('messages_lang.title_global') ?>:<?php echo $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?></h3>
                    </div>

                    <div class="col-md-2" style="float: right;">
                      <a href="<?= base_url('double_commande_new/Liste_transmission_bordereau_a_transmettre_brb') ?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-list text-light" aria-hidden="true"></i><?= lang('messages_lang.link_list') ?></a>
                    </div>
                  </div>
                </div>
                <hr>
                <?php
                if (session()->getFlashKeys('alert')) {
                ?>
                  <div class="col-md-12">
                    <div class="w-100 bg-danger text-white text-center" id="message">
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                  </div>
                <?php
                }
                ?>

                <form enctype='multipart/form-data' id="my_form" action="<?= base_url('double_commande_new/Transmission_borderau_brb/add') ?>" method="POST">
                  <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?= $etape['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                  <input type="hidden" name="STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID" id="STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID" value="<?= $statut_operation_bordereau['STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID'] ?>">
                  <input type="hidden" name="TYPE_DOCUMENT_ID" id="TYPE_DOCUMENT_ID" value="<?= $type_document_bordereau['TYPE_DOCUMENT_ID'] ?>">
                  <input type="hidden" name="STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID" id="STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID" value="<?= $statut_document_bordereau['STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID'] ?>">

                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">

                      <div class="col-md-6"><br>
                        <label for=""> <?= lang('messages_lang.label_date_reception') ?><span style="color: red;">*</span></label>
                        <input type="date" class="form-control" name="DATE_RECEPTION" value="<?= date('Y-m-d') ?>" id="DATE_RECEPTION" min="<?= $retVal = (!empty($date_transmission['DATE_TRANSMISSION'])) ? $date_transmission['DATE_TRANSMISSION'] : date('Y-m-d') ; ?>" max="<?= date("Y-m-d") ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                        <span class="error" id="error_DATE_RECEPTION" style="color: red; font-size: 13px"></span>
                      </div><br>
                      <div class="col-6"><br>
                        <label><?= lang('messages_lang.label_numero_bordereau') ?><span style="color: red;">*</span></label>
                        <input autocomplete="off" type="text" name="NUM_BORDEREAU_TRANSMISSION" id="NUM_BORDEREAU_TRANSMISSION" class="form-control">
                        <font color="red" id="error_NUM_BORDEREAU_TRANSMISSION"></font>
                      </div>

                      <div class="col-6"><br>
                        <label><?= lang('messages_lang.label_confirm_numero_bordereau') ?><span style="color: red;">*</span></label>
                        <input type="text" name="NUM_BORDEREAU_TRANSMISSION2" id="NUM_BORDEREAU_TRANSMISSION2" class="form-control">
                        <font color="red" id="error_NUM_BORDEREAU_TRANSMISSION2"></font>
                        <font color="red" id="error_NUM_BORDEREAU_TRANSMISSION_DIFFERENT"></font>
                      </div>


                      <div id="div_creance" class="col-6"><br>
                        <label><?= lang('messages_lang.label_telecharge_numero_bordereau') ?><span style="color: red;">*</span></label>
                        <input onchange="ValidationFile();" accept=".pdf" type="file" name="PATH_BORDEREAU_TRANSMISSION" id="PATH_BORDEREAU_TRANSMISSION" class="form-control">
                        <font color="red" id="error_PATH_BORDEREAU_TRANSMISSION"></font>
                        <font color="red" id="error_PATH_BORDEREAU_TRANSMISSION_VOLUMINEUX"></font>
                        <font color="red" id="error_PATH_BORDEREAU_TRANSMISSION_FORMAT"></font>
                      </div>

                      <div class="col-6"><br>
                        <label><?= lang('messages_lang.label_numero_titre_decaissement') ?><span style="color: red;">*</span></label>
                        <select class="form-control select2" multiple name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID">
                          <option value=""><?= lang('messages_lang.label_selecte') ?></option>
                          <?php
                          foreach ($exec as $key) {
                          ?>
                            <option value="<?= $key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>"><?= $key->TITRE_DECAISSEMENT ?></option>
                          <?php
                          }
                          ?>
                        </select>
                        <font color="red" id="error_EXECUTION_BUDGETAIRE_RACCROCHAGE_ID"></font>
                      </div>

                      <div class="col-6"><br>
                        <label><?= lang('messages_lang.label_orgine_destination') ?><span style="color: red;">*</span></label>
                        <select class="form-control" name="ID_ORIGINE_DESTINATION" id="ID_ORIGINE_DESTINATION">
                          <!-- <option value="">Sélectionner</option> -->
                          <?php
                          foreach ($origine_destination as $key) {
                          ?>
                            <option selected value="<?= $key->ID_ORIGINE_DESTINATION ?>"><?= $key->ORIGINE . ' ' . '->' . ' ' . $key->DESTINATION ?></option>
                          <?php
                          }
                          ?>
                        </select>
                        <font color="red" id="error_ID_ORIGINE_DESTINATION"></font>
                      </div>

                      <div class="col-md-6"> <br>
                        <label for=""><?= lang('messages_lang.label_date_transmission_BRB') ?> <font color="red">*</font>
                        </label>
                        <input type="date" name="DATE_TRANSMISSION" value="<?= date('Y-m-d') ?>" onkeypress="return false" max="<?= date('Y-m-d') ?>" id="DATE_TRANSMISSION" class="form-control">
                        <font color="red" id="error_DATE_TRANSMISSION"></font>
                      </div>
                    </div>


                    <div class="card-footer">
                      <div style="float:right;margin-top:-3%">
                        <button id="btnSave" type="button" onclick="send_data()" class="btn" style="float:right;background:#061e69;color:white"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?= lang('messages_lang.bouton_enregistrer') ?><span id="loading_btn"></span></button>
                      </div>
                    </div>

                  </div>
              </div>
              </form>

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

<div class='modal fade' id='detail_infos' data-backdrop="static">
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('messages_lang.label_confirmation') ?></h5>
      </div>
      <div class='modal-body'>
        <div id="infos_data"></div>
      </div>
      <div class='modal-footer'>
        <button id="mod" onclick="deleteFile();hideButton()" class='btn btn-primary btn-md' data-dismiss='modal'><i class="fa fa-pencil"></i><?= lang('messages_lang.bouton_modifier') ?></button>
        <button id="myElement" onclick="send_data2();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i><?= lang('messages_lang.bouton_confirmer') ?></button>
      </div>
    </div>
  </div>
</div>

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
  function get_min_trans() {
    $("#DATE_TRANSMISSION").prop('min', $("#DATE_RECEPTION").val());
  }
</script>

<script type="text/javascript">
  $('#NUM_BORDEREAU_TRANSMISSION').bind('paste', function(e) {
    e.preventDefault();
  });

  $('#NUM_BORDEREAU_TRANSMISSION2').bind('paste', function(e) {
    e.preventDefault();
  });
</script>

<script type="text/javascript">
  function ValidationFile() {
    var fileInput = document.getElementById('PATH_BORDEREAU_TRANSMISSION');
    var filePath = fileInput.value;
    // Allowing file type
    var allowedExtensions = /(\.pdf)$/i;

    if (!allowedExtensions.exec(filePath)) {
      $('#error_PATH_BORDEREAU_TRANSMISSION_FORMAT').text("<?= lang('messages_lang.bordereau_message') ?>");
      fileInput.value = '';
      return false;
    } else {
      $('#error_PATH_BORDEREAU_TRANSMISSION_FORMAT').text("");
      // Check if any file is selected. 
      if (fileInput.files.length > 0) {
        for (var i = 0; i <= fileInput.files.length - 1; i++) {
          var fsize = fileInput.files.item(i).size;
          var file = Math.round((fsize / 1024));
          // The size of the file. 
          if (file > 10*1024) {
            $('#error_PATH_BORDEREAU_TRANSMISSION_VOLUMINEUX').text('<?= lang('messages_lang.taille_bordereau_message') ?>');
            fileInput.value = '';
          } else {
            $('#error_PATH_BORDEREAU_TRANSMISSION_VOLUMINEUX').text('');
          }
        }
      }
    }
  }
</script>

<script type="text/javascript">
  function deleteFile() {
    $.ajax({
      url: "<?= base_url('double_commande_new/Transmission_borderau_brb/deleteFile') ?>",
      type: "POST",
      dataType: "JSON",
      data: {},
      beforeSend: function() {},
      success: function(data) {

      }
    });
  }
</script>


<script type="text/javascript">
  function send_data() {
    var statut = true;
    var NUM_BORDEREAU_TRANSMISSION = $('#NUM_BORDEREAU_TRANSMISSION').val()
    var NUM_BORDEREAU_TRANSMISSION2 = $('#NUM_BORDEREAU_TRANSMISSION2').val()
    var PATH_BORDEREAU_TRANSMISSION = $('#PATH_BORDEREAU_TRANSMISSION').val()
    var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();
    var ID_ORIGINE_DESTINATION = $('#ID_ORIGINE_DESTINATION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();

    if (DATE_RECEPTION == '') {
      $('#error_DATE_RECEPTION').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_DATE_RECEPTION').text('');
    }
    if (NUM_BORDEREAU_TRANSMISSION == '') {
      $('#error_NUM_BORDEREAU_TRANSMISSION').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_NUM_BORDEREAU_TRANSMISSION').text('');
    }

    if (NUM_BORDEREAU_TRANSMISSION2 == '') {
      $('#error_NUM_BORDEREAU_TRANSMISSION2').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_NUM_BORDEREAU_TRANSMISSION2').text('');
    }

    if (NUM_BORDEREAU_TRANSMISSION != NUM_BORDEREAU_TRANSMISSION2) {
      $('#error_NUM_BORDEREAU_TRANSMISSION_DIFFERENT').text('<?= lang('messages_lang.confirmation_message') ?>');
      return false;
    } else {
      $('#error_NUM_BORDEREAU_TRANSMISSION_DIFFERENT').text('');
    }

    if (PATH_BORDEREAU_TRANSMISSION == '') {
      $('#error_PATH_BORDEREAU_TRANSMISSION').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_PATH_BORDEREAU_TRANSMISSION').text('');
    }

    if (EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID == '') {
      $('#error_EXECUTION_BUDGETAIRE_RACCROCHAGE_ID').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_EXECUTION_BUDGETAIRE_RACCROCHAGE_ID').text('');
    }

    if (ID_ORIGINE_DESTINATION == '') {
      $('#error_ID_ORIGINE_DESTINATION').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_ID_ORIGINE_DESTINATION').text('');
    }
    if (DATE_TRANSMISSION == '') {
      $('#error_DATE_TRANSMISSION').text('<?= lang('messages_lang.validation_message') ?>');
      return false;
    } else {
      $('#error_DATE_TRANSMISSION').text('');
    }

    var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID option:selected').toArray().map(item => item.text).join();

    var form = new FormData();


    var PATH_BORDEREAU_TRANSMISSION = document.getElementById("PATH_BORDEREAU_TRANSMISSION").files[0];
    var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
    var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
    var result_date_rec = DATE_RECEPTION.format("DD/mm/YYYY");
    var result_date_trans = DATE_TRANSMISSION.format("DD/mm/YYYY");
    form.append("PATH_BORDEREAU_TRANSMISSION", PATH_BORDEREAU_TRANSMISSION);
    form.append("NUM_BORDEREAU_TRANSMISSION", NUM_BORDEREAU_TRANSMISSION);
    form.append("EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID", EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
    form.append("ID_ORIGINE_DESTINATION", ID_ORIGINE_DESTINATION);
    form.append("DATE_RECEPTION", result_date_rec);
    form.append("DATE_TRANSMISSION", result_date_trans);

    if (statut == true) {
      $.ajax({
        url: "<?= base_url('double_commande_new/Transmission_borderau_brb/getInfoDetail') ?>",
        type: "POST",
        dataType: "JSON",
        data: form,
        processData: false,
        contentType: false,
        beforeSend: function() {
          $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#detail_infos').modal('show'); // afficher bootstrap modal
          $('#infos_data').html(data.html)
          $('#loading_btn').html("");
        }
      });
    }
  }

  function send_data2(argument) {

    document.getElementById("my_form").submit();
  }
</script>