
<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
<?php $validation = \Config\Services::validation(); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">

                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black">
                        <?=$title; ?>
                     </h1>
                   </div>
                   <div class="col-md-6" style="float: right;">

                    <a href="<?=base_url('pip/Taux_Echange')?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.link_list') ?></a>
                  </div>
                </div>
              </div>

              <div class="card-body">
                <div class="table-responsive" style="width: 100%;">
                  <div class="card-body">
                    <form action="<?=base_url('pip/Taux_Echange/update') ?>" method="POST" id="MyFormData">

                      <input type="hidden" name="TAUX_ECHANGE_ID" id="TAUX_ECHANGE_ID" value="<?=$taux_echange['TAUX_ECHANGE_ID']?>">
                      <div class="row col-md-12">
                        <div class="col-md-6">
                          <label><?= lang('messages_lang.labelle_devise') ?></label>
                          <input type="text" name="DEVISE" id="DEVISE" class="form-control" maxlength="50" value="<?=$taux_echange['DEVISE']?>">
                          <span id="error_devise" color="red"></span>
                          <?= $validation->getError('DEVISE'); ?>
                        </div>
                        <div class="col-md-6">
                          <label><?= lang('messages_lang.label_droit_taux') ?></label>
                          <input type="text" name="TAUX" id="TAUX" class="form-control" value="<?=$taux_echange['TAUX']?>">
                          <span id="error_taux" color="red"></span>
                          <?= $validation->getError('TAUX'); ?>
                        </div>
                      </div>
                    </form> 
                    <div class="row col-md-12">
                      <div class="col-md-12" style="float: right;">
                        <a id="btnSave" onclick="modify()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-save pull-right"></span> <?= lang('messages_lang.bouton_modifier') ?></a>
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
    <?php echo view('includesbackend/scripts_js.php');?>

  </body>
</html>

<script>
  function modify()
  {
    var DEVISE = $('#DEVISE').val();
    var TAUX = $('#TAUX').val();
    var statut=1;

    if (DEVISE == '')
    {
      statut=2;
      $('#error_devise').html('<?= lang('messages_lang.error_sms') ?>');
    }else{
      $('#error_devise').html('');
    }
    if (TAUX == '')
    {
      statut=2;
      $('#error_taux').html('<?= lang('messages_lang.error_sms') ?>');
    }else{
      $('#error_taux').html('');
    }

    if (statut==1)
    {
      $('#MyFormData').submit();
    }

  }
</script>
