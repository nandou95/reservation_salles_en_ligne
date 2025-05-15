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
                       <?= lang('messages_lang.ajout_une_action')?>
                     </h1>
                   </div>
                   <div class="col-md-6" style="float: right;">

                    <a href="<?=base_url('ihm/Institutions_action')?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span><?= lang('messages_lang.link_list')?></a>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive container " style="margin-top:50px">
                  <?php $validation = \Config\Services::validation(); ?>
                  <form id="Myform" action="<?= base_url('ihm/Institutions_action/insert/') ?>" method="post">
                    <div class="row container">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="" class="form-label"><?= lang('messages_lang.menu_programme')?> <font color="red">*</font></label>
                          <select name="PROGRAMME_ID" id="PROGRAMME_ID" class="form-control">
                            <option value="">Séléctioner</option>
                            <?php foreach ($description as $value) {
                              if ($value->PROGRAMME_ID == set_value('PROGRAMME_ID')) { ?>
                                <option value="<?= $value->PROGRAMME_ID ?>" selected>
                                  <?= $value->INTITULE_PROGRAMME ?></option>
                                <?php } else { ?>
                                  <option value="<?= $value->PROGRAMME_ID ?>">
                                    <?= $value->INTITULE_PROGRAMME ?></option>
                                  <?php }
                                } ?>
                              </select>
                              <?php if ($validation->getError('PROGRAMME_ID')) : ?>
                                <span class="text-danger"><?= $validation->getError('PROGRAMME_ID') ?></span>
                              <?php endif ?>
                              <span class="text-danger" id="error_PROGRAMME_ID"></span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="CODE_ACTION" class="form-label"><?= lang('messages_lang.code_programm')?><font color="red">*</font></label>
                              <div class="input-group has-validation">
                                <input type="text" class="form-control" name="CODE_ACTION" id="CODE_ACTION" value="<?= set_value('CODE_ACTION') ?>" autofocus>
                              </div>
                              <?php if ($validation->getError('CODE_ACTION')) : ?>
                                <span class="text-danger"><?= $validation->getError('CODE_ACTION') ?></span>
                              <?php endif ?>
                              <span class="text-danger" id="error_CODE_ACTION"></span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="LIBELLE_ACTION" class="form-label"><?= lang('messages_lang.labelle_libelle')?> <font color="red">*</font></label>
                              <div class="input-group has-validation">
                                <input type="text" class="form-control" name="LIBELLE_ACTION" id="LIBELLE_ACTION" value="<?= set_value('LIBELLE_ACTION') ?>" autofocus>
                              </div>
                              <?php if ($validation->getError('LIBELLE_ACTION')) : ?>
                                <span class="text-danger"><?= $validation->getError('LIBELLE_ACTION') ?></span>
                              <?php endif ?>
                              <span class="text-danger" id="error_LIBELLE_ACTION"></span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="OBJECTIF_ACTION" class="form-label"><?= lang('messages_lang.th_objectif_programme')?> <font color="red">*</font></label>
                              <div class="input-group has-validation">
                                <input type="text" class="form-control" name="OBJECTIF_ACTION" id="OBJECTIF_ACTION" value="<?= set_value('OBJECTIF_ACTION') ?>" autofocus>
                              </div>
                              <?php if ($validation->getError('OBJECTIF_ACTION')) : ?>
                                <span class="text-danger"><?= $validation->getError('OBJECTIF_ACTION') ?></span>
                              <?php endif ?>
                              <span class="text-danger" id="error_OBJECTIF_ACTION"></span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="STRUTURE_IMPLIQUEES" class="form-label"><?= lang('messages_lang.structure_une_impliquees')?> <font color="red">*</font></label>
                              <div class="input-group has-validation">
                                <input type="text" class="form-control" name="STRUTURE_IMPLIQUEES" id="STRUTURE_IMPLIQUEES" value="<?= set_value('STRUTURE_IMPLIQUEES') ?>" autofocus>
                              </div>
                              <?php if ($validation->getError('STRUTURE_IMPLIQUEES')) : ?>
                                <span class="text-danger"><?= $validation->getError('STRUTURE_IMPLIQUEES') ?></span>
                              <?php endif ?>
                              <span class="text-danger" id="error_STRUTURE_IMPLIQUEES"></span>
                            </div>
                          </div>
                        </div>
                      </form>
                       <div id="SAVE" class="card-footer">
                      <button type="button" style="float: right;" id="btnSave" class="btn btn-primary float-end envoi" onclick="save()"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp; <?= lang('messages_lang.bouton_enregistrer')?></button>
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
    function save()
    {
      var PROGRAMME_ID = $('#PROGRAMME_ID').val();
      var CODE_ACTION = $('#CODE_ACTION').val();
      var LIBELLE_ACTION = $('#LIBELLE_ACTION').val();
      var OBJECTIF_ACTION = $('#OBJECTIF_ACTION').val();
      var STRUTURE_IMPLIQUEES = $('#STRUTURE_IMPLIQUEES').val();

      $('#error_PROGRAMME_ID').html('');
      $('#error_CODE_ACTION').html('');
      $('#error_LIBELLE_ACTION').html('');
      $('#error_OBJECTIF_ACTION').html('');
      $('#error_STRUTURE_IMPLIQUEES').html('');

      var statut = 2;

      if (PROGRAMME_ID === '') {
        $('#error_PROGRAMME_ID').html('<?= lang('messages_lang.labelle_et_error')?>');
        statut = 1;
      }

      if (CODE_ACTION === '') {
        $('#error_CODE_ACTION').html('<?= lang('messages_lang.labelle_et_error')?>');
        statut = 1;
      }
      if (LIBELLE_ACTION === '') {
        $('#error_LIBELLE_ACTION').html('<?= lang('messages_lang.labelle_et_error')?>');
        statut = 1;
      }
      if (OBJECTIF_ACTION === '') {
        $('#error_OBJECTIF_ACTION').html('<?= lang('messages_lang.labelle_et_error')?>');
        statut = 1;
      }

      if (STRUTURE_IMPLIQUEES === '') {
        $('#error_STRUTURE_IMPLIQUEES').html('<?= lang('messages_lang.labelle_et_error')?>');
        statut = 1;
      }

      if (statut === 2) {
        $('#Myform').submit();
      }
    }
  </script>