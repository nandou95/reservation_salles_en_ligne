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
                      <h3> <?=$title?></h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Etape_Double_Commande_Profil') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
               <br>
                <div id="collapseThree" class="collapse col-md-12" aria-labelledby="headingThree" data-parent="#accordion">
                 
                </div>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Etape_Double_Commande_Profil/save') ?>" method="POST">

                    <div class="row col-md-12">
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.label_etape') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" onchange="next_step()">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <?php  foreach ($etapes as $keys) { ?>
                            <?php if($keys->ETAPE_DOUBLE_COMMANDE_ID ==set_value('ETAPE_DOUBLE_COMMANDE_ID')) { ?>
                              <option value="<?=$keys->ETAPE_DOUBLE_COMMANDE_ID ?>" selected><?=$keys->DESC_ETAPE_DOUBLE_COMMANDE?></option>
                              <?php }else{?>
                               <option value="<?=$keys->ETAPE_DOUBLE_COMMANDE_ID ?>"><?=$keys->DESC_ETAPE_DOUBLE_COMMANDE?></option>
                              <?php } }?>
                            </select>
                            <span id="error_ETAPE_DOUBLE_COMMANDE_ID" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ETAPE_DOUBLE_COMMANDE_ID'); ?>
                            <?php endif ?>
                      </div>
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.labelle_et_prof') ?> <span style="color: red;">*</span></label>
                        <select class="form-control select2" multiple name="PROFIL_ID[]" id="PROFIL_ID">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <?php  foreach ($profil as $keys) { ?>
                            <?php if($keys->PROFIL_ID ==set_value('PROFIL_ID')) { ?>
                              <option value="<?=$keys->PROFIL_ID ?>" selected><?=$keys->PROFIL_DESCR?></option>
                              <?php }else{?>
                               <option value="<?=$keys->PROFIL_ID ?>"><?=$keys->PROFIL_DESCR?></option>
                              <?php } }?>
                            </select>
                            <span id="error_PROFIL_ID" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('PROFIL_ID'); ?>
                            <?php endif ?>
                      </div>
                      </div>
                    </form>

                    <div id="SAVE" class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save" class="btn btn-success" style="float:right;"><?= lang('messages_lang.bouton_enregistrer') ?></a>
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

    
  </div>
</body>

</html>


<script type="text/javascript">
function save()
{

  var statut = 2;
  var ETAPE_DOUBLE_COMMANDE_ID = $('#ETAPE_DOUBLE_COMMANDE_ID').val();
  var PROFIL_ID = $('#PROFIL_ID').val();
 

  $('#error_ETAPE_DOUBLE_COMMANDE_ID').html('');
  $('#error_PROFIL_ID').html('');
  

  if (ETAPE_DOUBLE_COMMANDE_ID === '') {
    $('#error_ETAPE_DOUBLE_COMMANDE_ID').html("<?= lang('messages_lang.error_sms') ?>");
    statut = 1;
  } else {
    $('#error_ETAPE_DOUBLE_COMMANDE_ID').html("");
  }

  if (PROFIL_ID === '') {
    $('#error_PROFIL_ID').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }


  if (statut ==2) 
  {
    $('#my_form').submit();
  }

}

</script>