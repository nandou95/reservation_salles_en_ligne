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
                    <div style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Liste_Paiement') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.lab_list')?> </a>
                    </div>
                    <div>
                      <font style="font-size:18px,color:#333">
                        <h4> <?= lang('messages_lang.lab_phase')?>: <?php if (!empty($etapes)) { ?>
                            <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                          <?php    } ?>
                        </h4>
                      </font>
                    </div>
                    <hr>
                    
                    <!-- debut -->
                    <div style="width:100%">
                          <div id="accordion">
                            <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                              <h5 class="mb-0">
                                <button style="background:#061e69; color:#fff; font-weight: 500; margin-left: 30px; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?= lang('messages_lang.lab_hist') ?>
                                </button>
                              </h5>
                            </div>
                          </div>
                          <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                          <?php include  'includes/Detail_View.php'; ?> 
                      </div>
                    </div>
                    <!--fin-->

                    <form action="<?= base_url('double_commande_new/Phase_comptable/save_reception_et_signature_titre') ?>" id="MyFormData" method="post">
                      <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                      <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                      <div class="col-md-12 container " style="border-radius:10px">
                        <div class="row mt-3">
                          <div class="col-md-6"><br>
                            <label for=""> <?= lang('messages_lang.labelle_d_recept')?><font color="red">*</font>
                            </label>
                            <input type="date" name="date_reception" value="<?= set_value('DATE_RECEPTION') ?>" min="<?= date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION'])) ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
                            <font color="red" id="date_reception_error"></font>
                            <font color="red" id="charCount1"></font>
                          </div>

                          <div class="col-md-6"><br>
                            <label for=""> <?= lang('messages_lang.lab_d_sign')?><font color="red">*</font></label>
                            <input type="date" name="date_signature_titre" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_signature_titre_id" class="form-control">
                            <font color="red" id="date_signature_titre_error"></font>
                          </div>
                          
                          <div class="col-md-6"><br>
                            <label for=""><?= lang('messages_lang.lab_dec')?><font color="red">*</font></label>
                            <select type="" name="ID_OPERATION" id="ID_OPERATION" class="form-control" onchange="get_rejet()">
                              <option value=""><?= lang('messages_lang.label_selecte')?></option>
                              <?php
                              foreach ($type as $values) {
                                if ($values->ID_OPERATION == set_value('ID_OPERATION')) { ?>
                                  <option value="<?= $values->ID_OPERATION ?>"><?= $values->DESCRIPTION ?></option>
                                <?php
                                } else { ?>
                                  <option value="<?= $values->ID_OPERATION ?>"><?= $values->DESCRIPTION ?></option>
                              <?php }
                              }
                              ?>
                            </select>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ID_OPERATION'); ?>
                            <?php endif ?>
                            </select>
                            <font color="red" id="error_ID_OPERATION"></font>
                          </div>
                            
                          <div class="col-md-6" id="show_motif" style="display:none;"><br>
                            <label for=""><?= lang('messages_lang.lab_motif')?><font color="red">*</font></label>
                            <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple>
                              <option value=""><?= lang('messages_lang.lab_select')?></option>
                              <?php
                              foreach ($motif as $value) {
                                if ($value->BUDGETAIRE_TYPE_ANALYSE_ID == set_value('BUDGETAIRE_TYPE_ANALYSE_ID')) { ?>
                                  <option value="<?= $value->BUDGETAIRE_TYPE_ANALYSE_ID ?>" selected><?= $value->DESC_BUDGETAIRE_TYPE_ANALYSE ?></option>
                                <?php } else {
                                ?>
                                  <option value="<?= $value->BUDGETAIRE_TYPE_ANALYSE_ID ?>"><?= $value->DESC_BUDGETAIRE_TYPE_ANALYSE ?></option>
                              <?php
                                }
                              }
                              ?>
                            </select>
                            <?php if (isset($validation)) : ?>
                              <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                            <?php endif ?>
                          </div>
                          <div class="col-md-6"><br>
                            <label for=""> <?= lang('messages_lang.labelle_d_transm')?><font color="red">*</font></label>
                            <input type="date" name="date_transmission" max="<?= date('Y-m-d')?>"  min="<?= date('Y-m-d')?>" id="date_transmission_id" class="form-control">         <font color="red" id="date_transmission_error"></font>
                            <font color="red" id="charCount1"></font>
                          </div>
                        </div>
                    </form>
                    <div style="float:right" class="mt-4">
                      <a class="btn btn-primary" onclick="save_dossier()" class="form-control"><?= lang('messages_lang.lab_enrg')?></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>
  </div>
  </div>
  </main>
  <?php echo view('includesbackend/scripts_js.php'); ?>
  <div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_modal')?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="table-responsive overflow-auto mt-2">
            <table class=" table  m-b-0 m-t-20">
              <tbody>

                <tr>
                  <td><i class="fa fa-calendar"></i><?= lang('messages_lang.labelle_d_recept')?></td>
                  <td id="date_reception_id_modal"></td>
                </tr>
                </tr>


                <tr>
                  <td><i class="fa fa-calendar"></i><?= lang('messages_lang.lab_d_trans')?></td>
                  <td id="date_transmission_id_modal"></td>
                </tr>
                </tr>

                <tr>
                  <td><i class="fa fa-calendar"></i><?= lang('messages_lang.lab_d_sign')?></td>
                  <td id="date_signature_id_modal"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-cogs"></i><?= lang('messages_lang.label_decision')?> </td>
                  <td id="decision"></td>
                </tr>
                
                <tr id="motif_ret">
                  <td><i class="fa fa-list"></i> <?= lang('messages_lang.label_motif_dec')?></td>
                  <td id="motif_retour_id_modal"></td>
                </tr>

              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
                <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
                <a id="myElement" onclick="save_info();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></a>
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
  function get_min_trans() {
    $("#date_transmission_id").prop('min', $("#date_reception_id").val());
  }
</script>
<script>
  function get_rejet() {
    var ID_OPERATION = $('#ID_OPERATION').val();

    if (ID_OPERATION == '') {
      $('#show_motif').hide();

    } else {

      if (ID_OPERATION == 1 || ID_OPERATION == 3) {
        $('#show_motif').show();
      } else {

        $('#show_motif').hide();
      }

    }

  }
</script>

<script>
  function save_dossier() {
    var statut = true;
    var date_transmission_id = $('#date_transmission_id').val();
    var date_reception_id = $('#date_reception_id').val();
    var date_signature_titre_id = $('#date_signature_titre_id').val();
    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

    if (date_transmission_id == "") {
      statut = false;
      $("#date_transmission_error").html("<?= lang('messages_lang.error_sms')?>");
    } else {
      $("#date_transmission_error").html("");
    }

    if (date_reception_id == "") {
      statut = false;
      $("#date_reception_error").html("<?= lang('messages_lang.error_sms')?>");
    } else {
      $("#date_reception_error").html("");
    }


    if (date_signature_titre_id == "") {
      statut = false;
      $("#date_signature_titre_error").html("<?= lang('messages_lang.error_sms')?>");
    } else {
      $("#date_signature_titre_error").html("");
    }
    if (ID_OPERATION == '') {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');
    } else {
      $('#error_ID_OPERATION').html('');
    }
    if (ID_OPERATION == 1) {
      if (TYPE_ANALYSE_MOTIF_ID == '') {
        statut = false;
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('<?= lang('messages_lang.error_sms')?>');

      } else {
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      }
    }


    if (statut == true) {
      var date = moment(date_reception_id, "YYYY/mm/DD")
      var reception_date = date.format('DD/mm/YYYY')
      var date1 = moment(date_transmission_id, "YYYY/mm/DD")
      var transmission_date = date1.format('DD/mm/YYYY')
      var date2 = moment(date_signature_titre_id, "YYYY/mm/DD")
      var date_signature_titre = date2.format('DD/mm/YYYY')
      var operation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();

      $('#date_reception_id_modal').html(reception_date);
      $('#date_transmission_id_modal').html(transmission_date);
      $('#date_signature_id_modal').html(date_signature_titre);
      $('#decision').html(operation);
      
    if (ID_OPERATION == 1) {
      var motif = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
      var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${motif}</ol>`;
      $('#motif_retour_id_modal').html(orderedList);
    }else{
      $('#motif_ret').hide();
      $('#motif_retour_id_modal').hide();
    }
      $('#detail').modal()
    }
  }
</script>

<script>
  function save_info() {
    $('#MyFormData').submit()

  }
</script>