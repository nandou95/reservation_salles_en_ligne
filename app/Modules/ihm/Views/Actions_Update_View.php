<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
</head>
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
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black">
                        <?= $title ?>
                      </h1>
                    </div>
                    <div class="col-md-6" style="float: right;">
                      <a href="<?= base_url('ihm/Actions') ?>" style="float: right;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.list_action') ?> </a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive container " style="margin-top:50px">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form action="<?= base_url('ihm/Actions/update') ?>" method="POST" id="Myform">
                      <div class="row">
                        <input type="hidden" name="ACTION_ID" id="ACTION_ID" value="<?= $action['ACTION_ID'] ?>">
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <label for="PROCESS_ID" class="form-label"><?= lang('messages_lang.processus_action') ?> </label>
                          <select name="PROCESS_ID" id="PROCESS_ID" class="form-control ">
                            <?php foreach ($process as $value) {
                              if ($value->PROCESS_ID) { ?>
                                <option value="<?= $value->PROCESS_ID ?>" selected><?= $value->NOM_PROCESS ?></option>
                              <?php } else { ?>
                                <option value="<?= $value->PROCESS_ID ?>"><?= $value->NOM_PROCESS ?></option>
                            <?php }
                            } ?>
                          </select>
                          <span id="error_processus" class="text-danger"></span>
                          <?= $validation->getError('PROCESS_ID'); ?>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label"><?= lang('messages_lang.categorie_action') ?></label>
                          <select name="ID_CL_CMR_COSTAB_CATEGORIE" id="ID_CL_CMR_COSTAB_CATEGORIE" class="form-control ">
                            <?php foreach ($categories as $value) {
                              if ($value->ID_CL_CMR_COSTAB_CATEGORIE == $action['ID_CL_CMR_COSTAB_CATEGORIE']) { ?>
                                <option value="<?= $value->ID_CL_CMR_COSTAB_CATEGORIE ?>" selected><?= $value->CL_CMR_COSTAB_CATEGORY ?></option>
                              <?php } else { ?>
                                <option value="<?= $value->ID_CL_CMR_COSTAB_CATEGORIE ?>"><?= $value->CL_CMR_COSTAB_CATEGORY ?></option>
                            <?php }
                            } ?>
                          </select>
                          <span id="error_etape" class="text-danger"></span>
                          <?= $validation->getError('ID_CL_CMR_COSTAB_CATEGORIE'); ?>
                        </div>
                        <div class="col-md-4">
                          <label> <?= lang('messages_lang.actions_action') ?></label>
                          <input type="text" class="form-control" name="DESCR_ACTION" id="DESCR_ACTION" maxlength="200" value="<?= $action['DESCR_ACTION'] ?>">
                          <span id="error_action" class="text-danger"></span>
                          <?= $validation->getError('DESCR_ACTION'); ?>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.etape_action') ?></label>
                          <select class="form-control" name="ETAPE_ID" id="ETAPE_ID" onchange="get_etape_suivante();">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php foreach ($etapes as $value) {
                              if ($value->ETAPE_ID == $action['ETAPE_ID']) { ?>
                                <option value="<?= $value->ETAPE_ID ?>" selected><?= $value->DESCR_ETAPE ?></option>
                              <?php } else { ?>
                                <option value="<?= $value->ETAPE_ID ?>"><?= $value->DESCR_ETAPE ?></option>
                            <?php }
                            } ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <div class="text-danger"><?= $validation->getError('ETAPE_ID'); ?></div>
                          <?php endif ?>
                          <span id="error_ETAPE_ID" class="text-danger"></span>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.etape_suivante_action') ?></label>
                          <select class="form-control" name="MOVETO" id="MOVETO">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php foreach ($etapes as $value) {

                              if ($value->ETAPE_ID == $action['MOVETO']) { ?>
                                <option value="<?= $value->ETAPE_ID ?>" selected><?= $value->DESCR_ETAPE ?></option>
                            <?php }
                            } ?>
                          </select>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.document_action') ?></label>
                          <select name="DOCUMENT_ID[]" id="DOCUMENT_ID" class="form-control select2" multiple>
                            <?php
                            foreach ($documents as $key) {
                              if (in_array($key->DOCUMENT_ID, $exist)) {  ?>
                                <option value="<?= $key->DOCUMENT_ID ?>" selected><?= $key->DESC_DOCUMENT ?></option>
                            <?php
                              } else {
                                echo "<option value=" . $key->DOCUMENT_ID . " >" . $key->DESC_DOCUMENT . " </option>";
                              }
                            }
                            ?>
                          </select>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.info_sup_action') ?></label>
                          <select name="ID_INFOS_SUPP[]" id="ID_INFOS_SUPP" class="form-control select2" multiple>
                            <?php
                            foreach ($infos_suppl as $value) {
                              if (in_array($value->ID_INFOS_SUPP, $info_exist)) {  ?>
                                <option value="<?= $value->ID_INFOS_SUPP ?>" selected><?= $value->DESCR_INFOS_SUPP ?></option>
                            <?php
                              } else {
                                echo "<option value=" . $value->ID_INFOS_SUPP . " >" . $value->DESCR_INFOS_SUPP . " </option>";
                              }
                            }
                            ?>

                          </select>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.commentaire_obligatoire_action') ?></label>
                          <select class="form-control" name="IS_REQUIRED" id="IS_REQUIRED" class="form-control ">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php foreach ($select as $value) {
                              if ($value['ID'] == $action['IS_REQUIRED']) { ?>
                                <option value="<?= $value['ID'] ?>" selected><?= $value['DES'] ?>
                                <?php } else { ?>
                                <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?>
                              <?php }
                            } ?>
                          </select>
                          <span id="error_IS_REQUIRED" class="text-danger"></span>
                          <?= $validation->getError('IS_REQUIRED'); ?>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label"> <?= lang('messages_lang.appele_formulaire_action') ?> </label>
                          <select class="form-control" name="GET_FORM" id="GET_FORM" class="form-control" onchange="putLink()">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php foreach ($select as $value) {
                              if ($value['ID'] == $action['GET_FORM']) { ?>
                                <option value="<?= $value['ID'] ?>" selected><?= $value['DES'] ?>
                                <?php } else { ?>
                                <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?>
                              <?php }
                            } ?>
                          </select>
                          <span id="error_info" class="text-danger"></span>
                          <?= $validation->getError('GET_FORM'); ?>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label"><?= lang('messages_lang.formulaire_initial_action') ?></label>
                          <select name="IS_INITIAL" id="IS_INITIAL" class="form-control ">
                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                            <?php foreach ($select as $value) {
                              if ($value['ID'] == $action['IS_INITIAL']) { ?>
                                <option value="<?= $value['ID'] ?>" selected><?= $value['DES'] ?>
                                <?php } else { ?>
                                <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?>
                              <?php }
                            } ?>
                          </select>
                          <span id="error_info" class="text-danger"></span>
                          <?= $validation->getError('IS_INITIAL'); ?>
                        </div>

                        <div class="col-md-4" id="LINK_FORM1">
                          <label> <?= lang('messages_lang.lien_action') ?> </label>
                          <input type="text" class="form-control" name="LINK_FORM" maxlength="200" value="<?= $action['LINK_FORM'] ?>" readonly>
                        </div>
                        <div class="col-md-4" id="LINK_FORM2">
                          <label> <?= lang('messages_lang.lien_action') ?> </label>
                          <input type="text" class="form-control" name="LINK_FORM" id="LINK_FORM" maxlength="200" value="<?= $action['LINK_FORM'] ?>">
                          <span id="error_action" color="red"></span>
                          <?= $validation->getError('LINK_FORM'); ?>
                          <span id="error_LINK_FORM" class="text-danger"></span>
                        </div>

                      </div>
                    </form>

                    <div id="SAVE" class="card-footer">
                      <button type="button" style="float: right;" id="btnSave" class="btn btn-primary float-end envoi" onclick="update_action()"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?= lang('messages_lang.modifier') ?></button>
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
  $('#LINK_FORM1').hide();
  $('#LINK_FORM2').show();
  var get_form = $('#GET_FORM').val();
  if (get_form == 1) {
    $('#LINK_FORM1').hide();
    $('#LINK_FORM2').show();
  } else {
    $('#LINK_FORM1').hide();
    $('#LINK_FORM2').hide();
  }

  function putLink(id) {
    var get_form = $('#GET_FORM').val();
    if (get_form == 1 || get_form == 0) {
      $('#LINK_FORM1').hide();
      $('#LINK_FORM2').show();
    } else {
      $('#LINK_FORM1').hide();
      $('#LINK_FORM2').hide();
    }
  }
</script>


<script type="text/javascript">
  function update_action() {
    var ETAPE_ID = $('#ETAPE_ID').val();
    var DESCR_ACTION = $('#DESCR_ACTION').val();
    var MOVETO = $('#MOVETO').val();
    var DOCUMENT_ID = $('#DOCUMENT_ID').val();
    var ID_INFOS_SUPP = $('#ID_INFOS_SUPP').val();
    var IS_INITIAL = $('#IS_INITIAL').val();
    var ID_CL_CMR_COSTAB_CATEGORIE = $('#ID_CL_CMR_COSTAB_CATEGORIE').val();
    var GET_FORM = $('#GET_FORM').val();
    var LINK_FORM = $('#LINK_FORM').val();
    var IS_REQUIRED = $('#IS_REQUIRED').val();
    var DOCUMENT_ID = $('#DOCUMENT_ID').val();
    var PROCESS_ID = $('#PROCESS_ID').val();
    $('#error_ETAPE_ID').html('');
    $('#error_DESCR_ACTION').html('');
    $('#error_MOVETO').html('');
    $('#error_action').html('');
    $('#error_LINK_FORM').html('');
    $('#error_IS_REQUIRED').html('');
    $('#error_PROCESS').html('');

    var statut = 2;
    if (ETAPE_ID == '') {
      $('#error_ETAPE_ID').html('Le champ est obligatoire');
      statut = 1;
    }
    if (DESCR_ACTION == '') {
      $('#error_action').html('Le champ est obligatoire');
      statut = 1;
    }

    if (MOVETO == '') {
      $('#error_MOVETO').html('Le champ est obligatoire');
      statut = 1;
    }
    if (PROCESS_ID == '') {
      $('#error_PROCESS').html('Le champ est obligatoire');
      statut = 1;
    }

    if (LINK_FORM == '') {
      $('#error_LINK_FORM').html('Le champ est obligatoire');
      statut = 1;
    }

    if (IS_REQUIRED == '') {
      $('#error_IS_REQUIRED').html('Le champ est obligatoire');
      statut = 1;
    }
    if (statut == 2) {
      document.getElementById("Myform").submit();
    }
  }
</script>
<script type="text/javascript">
  function get_etape_suivante() {
    var ETAPE_ID = $('#ETAPE_ID').val();
    var PROCESS_ID = $('#PROCESS_ID').val();
    $.ajax({
      url: "<?= base_url('/ihm/Actions/get_etape_suivante') ?>/" + ETAPE_ID + "/" + PROCESS_ID,
      type: "GET",
      dataType: "JSON",
      cache: false,
      success: function(data) {
        $('#MOVETO').html(data.html);
      }
    });
  }
</script>