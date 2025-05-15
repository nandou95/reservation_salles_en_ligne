<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
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
          <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
            <div class="col-12 d-flex">
              <div class="col-9" style="float: left;">
                <h1 class="header-title text-dark">PTBA</h1>
              </div>
              <div class="col-3" style="float: right;">
                <a href="<?=base_url('ihm/Liste_Taches')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary">
                  <span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.list_action')?>
                </a>   
              </div>
            </div>
            <div style="margin-left: 15px" id="SUCCESS_MESSAGE" class="row">
            </div>
            <div class="card-body">
              <?php $validation = \Config\Services::validation(); ?>
              <form id="my_form" action="<?= base_url('double_commande_new/PTBA_Upload/save_upload') ?>" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-6">
                        <div class="form-group">
                          <label>Fichier Excel PTBA<span style="color: red;">*</span></label>
                          <input type="file" accept=".xls,.xlsx,.xlsm,.csv" class="form-control"  name="FICHIER_PTBA" id="FICHIER_PTBA">
                          <font color="red" id="error_FICHIER_PTBA"></font>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12">
                    <button onclick="save_upload()" style="float: right;" id="btnSave" type="button" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.enregistre_action')?></button>
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
  function save_upload() 
  {
    $('#my_form').submit()
  }
</script>
