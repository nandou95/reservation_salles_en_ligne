<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation();
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if (empty($user_id)) {
    return redirect('Login_Ptba');
  }
  ?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <style type="text/css">
    .modal-signature {
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      border-bottom-right-radius: .3rem;
      border-bottom-left-radius: .3rem
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">

          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">

                </div>
                <div class="card-body">
                  <div style="margin-top: -25px;" class="card">
                  </div>
                  <div class="card-body" style="margin-top: -20px">
                    <br>
                    <div style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Transmission_Directeur_Comptable_List') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.labelle_liste')?></a>
                    </div>

                    <div>
                      <font style="font-size:18px;color:#333">
                        <h4><?= lang('messages_lang.labelle_phase_coptable')?>:
                          <?php if (!empty($titre_etape)) { ?>
                            <?= $titre_etape['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                          <?php } ?>
                        </h4>
                      </font>
                    </div>
                    <br>
                    <hr>
                  </br></br>


                  <form enctype='multipart/form-data' name="phase_comptabe" id="phase_comptabe" action="<?= base_url('double_commande_new/Phase_Comptable_Directeur_Comptable/save') ?>" method="post">
                   <input type="hidden" name="STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID" value="<?= $type_document_transmissions['STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID'] ?>">
                   <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" value="<?= $titre_etape['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                   <input type="hidden" name="TYPE_DOCUMENT_ID" value="<?= $type_documents['TYPE_DOCUMENT_ID'] ?>">

                   <div class="container">
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-md-6">
                          <label for=""> <?= lang('messages_lang.labelle_num_bord')?><font color="red">*</font></label>
                          <input type="text" oninput="this.value = this.value.toUpperCase()" class="form-control" name="NUM_BORDEREAU_TRANSMISSION" id="NUM_BORDEREAU_TRANSMISSION">
                          <font color="red" id="error_NUM_BORDEREAU_TRANSMISSION"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('NUM_BORDEREAU_TRANSMISSION'); ?>
                          <?php endif ?>
                        </div>
                        <div class="col-md-6">
                          <label for=""><?= lang('messages_lang.labelle_confirmation')?><font color="red">*</font></label>
                          <input type="text" class="form-control" name="VERIFICATION" oninput="this.value = this.value.toUpperCase()" id="VERIFICATION" onpaste="return false">
                          <font color="red" id="error_VERIFICATION"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('VERIFICATION'); ?>
                          <?php endif ?>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        
                        
                        <div class="col-md-6">
                          <label for=""><?= lang('messages_lang.labelle_pdf')?><font color="red">*</font></label>
                          <input type="file" class="form-control" name="PATH_BORDEREAU_TRANSMISSION" id="PATH_BORDEREAU_TRANSMISSION" onchange="valid_doc()" accept=".pdf">
                          <font color="red" id="error_PATH_BORDEREAU_TRANSMISSION"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PATH_BORDEREAU_TRANSMISSION'); ?>
                          <?php endif ?>
                        </div>
                        <div class="col-md-6">
                          <label for=""><?= lang('messages_lang.labelle_titre_dec')?><font color="red">*</font></label>

                          <select class="form-control select2" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" multiple>
                            <?php
                            foreach ($get_titre_decaissement as $value) {
                              if ($value->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID == set_value('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID')) { ?>
                                <option value="<?= $value->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>" selected><?= $value->TITRE_DECAISSEMENT ?></option>
                              <?php } else {
                                ?>
                                <option value="<?= $value->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>"><?= $value->TITRE_DECAISSEMENT ?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                          <font color="red" id="error_EXECUTION_BUDGETAIRE_RACCROCHAGE_NEW_ID"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('EXECUTION_BUDGETAIRE_RACCROCHAGE_ID'); ?>
                          <?php endif ?>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        
                        <div class="col-md-6">
                          <label for=""> <?= lang('messages_lang.labelle_origine')?><font color="red">*</font></label>

                          <input type="text" id="ID_ORIGINE_DESTINATION" readonly="on" class="form-control" value="<?= $get_origine_destination['ORIGINE'] . ' -> ' . $get_origine_destination['DESTINATION'] ?>">
                          <input type="hidden" name="ID_ORIGINE_DESTINATION" value="<?= $get_origine_destination['ID_ORIGINE_DESTINATION'] ?>">


                          <font color="red" id="error_ID_ORIGINE_DESTINATION"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('ID_ORIGINE_DESTINATION'); ?>
                          <?php endif ?>
                        </div>
                        <div class="col-md-6">
                          <label for=""> <?= lang('messages_lang.labelle_date_trans')?><font color="red">*</font></label>

                          <input type="date" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" class="form-control" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION" value="<?= date('Y-m-d') ?>" max="<?=Date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">

                          <span class="error" style="color: red; font-size: 13px"></span>
                          <font color="red" id="error_DATE_TRANSMISSION"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DATE_TRANSMISSION'); ?>
                          <?php endif ?>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        

                       
                      </div>
                    </div>
                  </div>
                </form>
                <div style="float:right" class="mt-4">
                  <div class="form-group ">
                    <a onclick="save_phase()" id="btn_save" class="btn" style="float:right;margin-right: 20px;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistre')?></a>
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
  function save_phase() {
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    $('#error_DATE_RECEPTION').html('');
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');
    var NUM_BORDEREAU_TRANSMISSION = $('#NUM_BORDEREAU_TRANSMISSION').val();
    $('#error_NUM_BORDEREAU_TRANSMISSION').html('');
    var VERIFICATION = $('#VERIFICATION').val();
    $('#error_VERIFICATION').html('');
    var PATH_BORDEREAU_TRANSMISSION = $('#PATH_BORDEREAU_TRANSMISSION').val();
    $('#error_PATH_BORDEREAU_TRANSMISSION').html('');
    var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();
    $('#error_EXECUTION_BUDGETAIRE_RACCROCHAGE_NEW_ID').html('');
    var ID_ORIGINE_DESTINATION = $('#ID_ORIGINE_DESTINATION').val();
    $('#error_ID_ORIGINE_DESTINATION').html('');

    var statut = 2;

    if (DATE_RECEPTION == '') {
      $('#error_DATE_RECEPTION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (DATE_TRANSMISSION == '') {
      $('#error_DATE_TRANSMISSION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (NUM_BORDEREAU_TRANSMISSION == '') {
      $('#error_NUM_BORDEREAU_TRANSMISSION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (VERIFICATION == '') {
      $('#error_VERIFICATION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (PATH_BORDEREAU_TRANSMISSION == '') {
      $('#error_PATH_BORDEREAU_TRANSMISSION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID == '') {
      $('#error_EXECUTION_BUDGETAIRE_RACCROCHAGE_NEW_ID').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (ID_ORIGINE_DESTINATION == '') {
      $('#error_ID_ORIGINE_DESTINATION').html('<?= lang('messages_lang.error_message')?>');
      statut = 1;
    }
    if (NUM_BORDEREAU_TRANSMISSION !== null && NUM_BORDEREAU_TRANSMISSION !== null && NUM_BORDEREAU_TRANSMISSION !== VERIFICATION) {
      $('#error_VERIFICATION').html('<?= lang('messages_lang.error_message_verification')?>');
      statut = 1;
    }


    var url;
    if (statut == 2) {
     var date=moment(DATE_RECEPTION, "YYYY/mm/DD")
     var DATE_RECEPTION= date.format('DD/mm/YYYY')
     $('#DATE_RECEPTION_MODAL').html(DATE_RECEPTION);

     var date=moment(DATE_TRANSMISSION, "YYYY/mm/DD")
     var DATE_TRANSMISSION= date.format('DD/mm/YYYY')
     $('#DATE_TRANSMISSION_MODAL').html(DATE_TRANSMISSION);
     $('#NUM_BORDEREAU_TRANSMISSION_MODAL').html(NUM_BORDEREAU_TRANSMISSION);
     $('#VERIFICATION_MODAL').html(VERIFICATION);
     var PATH = document.getElementById('PATH_BORDEREAU_TRANSMISSION');
     var PATH_BORDEREAU = PATH.files[0].name;
     $('#PATH_BORDEREAU_TRANSMISSION_MODAL').html(PATH_BORDEREAU);
     var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID option:selected').toArray().map(item => item.text).join();
     $('#EXECUTION_BUDGETAIRE_RACCROCHAGE_NEW_ID_MODAL').html(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
     $('#ID_ORIGINE_DESTINATION_MODAL').html(ID_ORIGINE_DESTINATION);

     $("#enjeux_modal").modal("show");
   }
 }
</script>

<script>
  function valid_doc() {
    var fileInput = document.getElementById('PATH_BORDEREAU_TRANSMISSION');
    var filePath = fileInput.value;
        // Allowing file type
        var allowedExtensions = /(\.pdf)$/i;

        if (!allowedExtensions.exec(filePath)) {
          $('#error_PATH_BORDEREAU_TRANSMISSION').text("<?= lang('messages_lang.error_message_pdf')?>");
          fileInput.value = '';
          return false;
        } else {
            // Check if any file is selected. 
            if (fileInput.files.length > 0) {
              for (const i = 0; i <= fileInput.files.length - 1; i++) {
                const fsize = fileInput.files.item(i).size;
                const file = Math.round((fsize / 1024));
                    // The size of the file. 
                    if (file > 10*1024) {
                      $('#error_PATH_BORDEREAU_TRANSMISSION').text('<?= lang('messages_lang.error_message_taille_pdf')?>');
                      fileInput.value = '';
                    } else {
                      $('#error_PATH_BORDEREAU_TRANSMISSION').text('');
                    }
                  }
                }
              }
            }
          </script>

          <script type="text/javascript">
            function finale_save() {
              document.getElementById("phase_comptabe").submit();
            }
          </script>

          <div class="modal fade" id="enjeux_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
              <div class="modal-content">

                <div class="modal-body">
                  <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.modal_titre')?></h5>
                  <div class="table-responsive  mt-3">
                    <table class="table m-b-0 m-t-20">
                      <tbody>
                        <tr>
                          <tr>
                            <td style="Width:60%"><i class="fa fa-archive"></i> &nbsp;<strong><?= lang('messages_lang.labelle_num_bord')?></strong></td>
                            <td id="NUM_BORDEREAU_TRANSMISSION_MODAL" class="text-dark"></td>
                          </tr>
                          <tr>
                            <td><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.labelle_pdf')?></strong></td>
                            <td id="PATH_BORDEREAU_TRANSMISSION_MODAL" class="text-dark"></td>
                          </tr>
                          <tr>
                            <td><i class="fa fa-bars"></i> &nbsp;<strong><?= lang('messages_lang.labelle_titre_dec')?></strong></td>
                            <td id="EXECUTION_BUDGETAIRE_RACCROCHAGE_NEW_ID_MODAL" class="text-dark"></td>
                          </tr>
                          <tr>
                            <td><i class="fa fa-exchange"></i> &nbsp;<strong><?= lang('messages_lang.labelle_origine')?></strong></td>
                            <td id="ID_ORIGINE_DESTINATION_MODAL" class="text-dark"></td>
                          </tr>
                          <tr>
                            <td style="Width:60%"><i class="fa fa-archive"></i> &nbsp;<strong><?= lang('messages_lang.labelle_date_trans')?></strong></td>
                            <td id="DATE_TRANSMISSION_MODAL" class="text-dark"></td>
                          </tr>
                          
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modal_modif')?></button>
                    <a id="myElement" onclick="finale_save();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_modal_conf')?></a>
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