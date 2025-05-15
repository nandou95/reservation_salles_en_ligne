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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div style="float: right;">
                  <a href="<?php echo base_url('ihm/Secteur_Intervention/list_view') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list')?> </a>
                </div>
                <div class="car-body">

                  <h4 style="margin-left:4%;margin-top:10px"> <?= $titre ?></h4>
                  <br>

                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' action="<?= base_url('ihm/Secteur_Intervention/Enregistrer_secteur') ?>" method="post">
                      <div class="container">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for=""><?= lang('messages_lang.ajout_secteur_intervention_lib')?><?= lang('messages_lang.link_list')?><font color="red">*</font></label>
                              <input type="text" class="form-control" name="DESCR_SECTEUR" id="DESCR_SECTEUR">
                              <font color="red" id="error_DESCR_SECTEUR"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DESCR_SECTEUR'); ?>
                              <?php endif ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="form-group ">
                          <button type="submit" onclick="sfp()" class="btn btn-primary"><?= lang('messages_lang.bouton_enregistrer')?></button>
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
<script>
  function sfp() {
        let verifiable = ['#DESCR_SECTEUR']
        let number = 0

        $('div .invalid-feedback').remove()
        $('div .is-invalid').removeClass('is-invalid')

        verifiable.forEach(element => {
            if($(element).val() == '' || $(element).val() == null){
                $(element).addClass('is-invalid')
                $(element).after('<div class="invalid-feedback"><?= lang('messages_lang.labelle_et_error')?></div>')
                number++
            }
        })
      }
  </script>