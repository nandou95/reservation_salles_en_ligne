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
          <div class="header">
            <h1 class="header-title text-white">
            </h1>
          </div>
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black"><?=$title;?></h1>
                    </div>
                    <div class="col-md-6">
                      <a class="btn btn-primary" href="<?=base_url('ihm/Information_sup') ?>" style="float: right;"><i class="fa fa-list"></i><?= lang('messages_lang.labelle_et_list')?></a>
                    </div>
                  </div><br>
                  <div class="car-body">
                    
                    <form action="<?=base_url('ihm/Information_sup/save') ?>" method="POST" id="MyFormData">
                    <div class="row">
                      <div class="col-md-6">
                        <label><?= lang('messages_lang.labelle_et_description_m')?> </label>
                        <input type="text" class="form-control" name="DESCR_INFOS_SUPP" id="DESCR_INFOS_SUPP" maxlength="200">
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('DESCR_INFOS_SUPP'); ?>
                      </div>
                    <div class="col-md-6">
                        <label ><?= lang('messages_lang.labelle_et_type_info_supp')?><font color="red">*</font></label>
                        <select class="form-control" name="TYPE_INFOS_NAME" id="TYPE_INFOS_NAME" class="form-control ">
                          <option value="">--- <?= lang('messages_lang.selectionner_transmission_du_bordereau')?> --- </option>
                          <?php foreach ($infos_suppl as $value): ?> 
                            <option value="<?=$value['ID']?>"><?=$value['DES']?>
                            </option>                  <?php endforeach ?>   
                          </select>
                          <span id="error_info" color="red"></span>
                        <?= $validation->getError('TYPE_INFOS_NAME'); ?> 
                        </div>
                     
                    </div>
                    <div class="row">
                     

                      
                      
                      <div class="col-md-12" style="float: right;">
                        <a id="btnSave" onclick="save()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-save pull-right"></span> <?= lang('messages_lang.lab_enrg')?></a>
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
      <?php echo view('includesbackend/scripts_js.php');?>

    </body>
    </html>



<script>
  function save()
  {
    var DESCR_INFOS_SUPP = $('#DESCR_INFOS_SUPP').val();
    var TYPE_INFOS_NAME = $('#TYPE_INFOS_NAME').val(); 
    $('#MyFormData').submit();
  }
</script>
