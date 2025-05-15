<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title text-white"></h1>
          </div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="row col-md-12">
                  <div class="col-md-6">
                    <h1 class="header-title text-black"><?= $title; ?></h1>
                  </div>
                  <div class="col-md-6">
                    <a class="btn btn-primary" href="<?= base_url('ihm/Actions') ?>" style="float: right;"><i class="fa fa-list"></i> <?= lang('messages_lang.list_action') ?> </a>
                  </div>
                </div><br>
                <div class="car-body">
                  <form action="<?= base_url('ihm/Actions/save') ?>" method="POST" id="MyFormData">
                    <div class="row">
                      <div class="col-md-6">
                        <label> <?= lang('messages_lang.actions_action') ?></label>
                        <input type="text" class="form-control" name="DESCR_ACTION" id="DESCR_ACTION" maxlength="200">
                        <span id="error_action" color="red"></span>
                        <?= $validation->getError('DESCR_ACTION'); ?>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label"><?= lang('messages_lang.categorie_action') ?></label>
                        <select name="ID_CL_CMR_COSTAB_CATEGORIE" id="ID_CL_CMR_COSTAB_CATEGORIE" class="form-control">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php foreach ($categories as $value) : ?>
                            <option value="<?= $value->ID_CL_CMR_COSTAB_CATEGORIE ?>"><?= $value->CL_CMR_COSTAB_CATEGORY ?></option>
                          <?php endforeach ?>
                        </select>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('ID_CL_CMR_COSTAB_CATEGORIE'); ?>
                      </div>

                      <div class="col-md-4">
                        <label for="PROCESS_ID" class="form-label"> <?= lang('messages_lang.processus_action') ?> </label>
                        <select onchange="get_etape();" class="form-control" name="PROCESS_ID" id="PROCESS_ID">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php foreach ($process as $key) { ?>
                            <option value="<?= $key->PROCESS_ID; ?>"><?= $key->NOM_PROCESS; ?></option>
                          <?php } ?>
                        </select>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.etape_action') ?></label>
                        <select onchange="get_etape_suivante();" class="form-control select2" name="ETAPE_ID" id="ETAPE_ID">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php
                          foreach ($etapes as $key) {
                            if ($key->ETAPE_ID == set_value('ETAPE_ID')) { ?>
                              <option value="<?= $key->ETAPE_ID ?>" selected><?= $key->DESCR_ETAPE ?></option>
                            <?php
                            } else { ?>
                              <option value="<?= $key->ETAPE_ID ?>"><?= $key->DESCR_ETAPE ?></option>
                          <?php
                            }
                          }
                          ?>
                        </select>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('ETAPE_ID'); ?>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.etape_suivante_action') ?></label>
                        <select class="form-control select2" name="MOVETO" id="MOVETO">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php
                          foreach ($etapes as $key) {
                            if ($key->ETAPE_ID == set_value('ETAPE_ID')) { ?>
                              <option value="<?= $key->ETAPE_ID ?>" selected><?= $key->DESCR_ETAPE ?></option>
                            <?php
                            } else { ?>
                              <option value="<?= $key->ETAPE_ID ?>"><?= $key->DESCR_ETAPE ?></option>
                          <?php
                            }
                          }
                          ?>
                        </select>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('ETAPE_ID'); ?>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.document_action') ?></label>
                        <select name="DOCUMENT_ID[]" id="DOCUMENT_ID" class="form-control select2" multiple>
                          <?php foreach ($documents as $value) : ?>
                            <option value="<?= $value->DOCUMENT_ID ?>"><?= $value->DESC_DOCUMENT ?></option>
                          <?php endforeach ?>
                        </select>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('MOVETO'); ?>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.info_sup_action') ?></label>
                        <select name="ID_INFOS_SUPP[]" id="ID_INFOS_SUPP" class="form-control select2" multiple>
                          <?php foreach ($infos_suppl as $value) : ?>
                            <option value="<?= $value->ID_INFOS_SUPP ?>"><?= $value->DESCR_INFOS_SUPP ?></option>
                          <?php endforeach ?>
                        </select>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('MOVETO'); ?>
                      </div>
                      <!--  -->
                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.commentaire_obligatoire_action') ?></label>
                        <select class="form-control" name="IS_REQUIRED" id="IS_REQUIRED" class="form-control ">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php foreach ($select as $value) : ?>
                            <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?></option>
                          <?php endforeach ?>
                        </select>
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('IS_REQUIRED'); ?>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label"> <?= lang('messages_lang.appele_formulaire_action') ?> </label>
                        <select class="form-control" name="GET_FORM" id="GET_FORM" class="form-control " onchange="appel_formulaire()">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php foreach ($select as $value) : ?>
                            <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?></option>
                          <?php endforeach ?>
                        </select>
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('GET_FORM'); ?>
                      </div>

                      <div class="col-md-4 d-none">
                        <label> <?= lang('messages_lang.lien_action') ?> </label>
                        <input type="text" class="form-control" name="LINK_FORM" id="LINK_FORM" maxlength="200">
                        <span id="error_action" color="red"></span>
                        <?= $validation->getError('LINK_FORM'); ?>
                        <span id="error_etape" color="red"></span>
                        <?= $validation->getError('LINK_FORM'); ?>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label"><?= lang('messages_lang.formulaire_initial_action') ?></label>
                        <select class="form-control" name="IS_INITIAL" id="IS_INITIAL" class="form-control ">
                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                          <?php foreach ($select as $value) : ?>
                            <option value="<?= $value['ID'] ?>"><?= $value['DES'] ?>
                            </option> <?php endforeach ?>
                        </select>
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('IS_INITIAL'); ?>
                      </div>
                      <div class="col-md-12" style="float: right;">
                        <a id="btnSave" onclick="save()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-save pull-right"></span> <?= lang('messages_lang.enregistre_action') ?> </a>
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

<script>

  function appel_formulaire() {
    let appelForm = document.querySelector('#GET_FORM')
    let linkForm = document.querySelector('#LINK_FORM')

    if(appelForm.value === '1') {
      linkForm.parentElement.classList.remove('d-none')
    } else if(appelForm.value === '2') {
      linkForm.parentElement.classList.add('d-none')
    }
  }

  function get_etape() {
    var PROCESS_ID = $('#PROCESS_ID').val();
    $.post('<?= base_url('ihm/Actions/get_etape') ?>', {
        PROCESS_ID: PROCESS_ID
      },
      function(data) {
        $('#ETAPE_ID').html(data.html);
        ETAPE_ID.InnerHtml = data;
      })
  }
</script>

<script>
  function get_etape_suivante() {
    var ETAPE_ID = $('#ETAPE_ID').val();
    $.post('<?= base_url('ihm/Actions/get_etape_suivante') ?>', {
        ETAPE_ID: ETAPE_ID
    },
    function(data) {
      $('#MOVETO').html(data.html);
      MOVETO.InnerHtml = data;
    })
  }
</script>

<script>
  function save() {
    var ETAPE_ID = $('#ETAPE_ID').val();
    $('#MyFormData').submit();
  }
</script>