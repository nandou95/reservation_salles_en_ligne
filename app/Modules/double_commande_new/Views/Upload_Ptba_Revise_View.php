  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation(); ?>
  </head>
  <style>
    hr.vertical {
      border:         none;
      border-left:    1px solid hsla(200, 2%, 12%,100);
      height:         55vh;
      width: 1px;
      color: #ddd
    }
  </style>

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
                    <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> Téléversement du fichier ptba revisé</h4>
                    <br>
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="myForm" id="myForm" action="<?=base_url('double_commande_new/Upload_Ptba_Revise/save/')?>" method="post" >
                      <div class="container">                        
                        <?php
                        if(session()->getFlashKeys('alert'))
                        {
                          ?>
                          <center class="ml-5" style="height=100px;width:90%" >
                            <div class="w-100 bg-danger text-white text-center"  id="message">
                              <?php echo session()->getFlashdata('alert')['message']; ?>
                            </div>
                          </center>
                          <?php
                        } ?>

                        <div class="row" style="border:1px solid #ddd;border-radius:5px">
                          <div class="col-md-12 mt-2" style="margin-bottom:50px">
                            <div class="row">
                              <div class="col-md-6">
                                <label for="">Fichier <font color="red">*</font></label>
                                <input type="file" value="<?= date('Y-m-d') ?>" class="form-control" accept=".xlsx, .xls, .csv" onchange="valider_fichier()" name="FICHIER_PTBA_REVISER" id="FICHIER_PTBA_REVISER">
                                <font color="red" id="error_FICHIER_PTBA_REVISER"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('FICHIER_PTBA_REVISER'); ?>
                                <?php endif ?>
                              </div>
                            
                              <div style="float: right;" class="col-md-2 mt-5" >
                                <div class="form-group " >
                                  <a onclick="save()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"> <?= lang('messages_lang.btn_televerser') ?></a>
                                </div>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div><br><br>
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
    var fileInput = document.getElementById('FICHIER_PTBA_REVISER');
    var file = fileInput.files[0];
    var errorSpan = document.getElementById('error_FICHIER_PTBA_REVISER');
    var statut=true
    if (!file)
    {
      errorSpan.textContent = 'Ce champs est obligatoire!';
    }
    // Check if the file type is excel
    if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      errorSpan.textContent = 'Choisissez un fichier excel!';
      statut=false
    }
    if (statut)
    {
      $('#myForm').submit();
    }
  }

  function valider_fichier() 
  {
    var fileInput = document.getElementById('FICHIER_PTBA_REVISER');
    var file = fileInput.files[0];
    var errorSpan = document.getElementById('error_FICHIER_PTBA_REVISER');
   
    if (!file)
    {
      errorSpan.textContent = 'Ce champs est obligatoire!';
    }
    // Check if the file type is excel
    if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      errorSpan.textContent = 'Choisissez un fichier excel!';
      $('#btn_save').attr('disabled',true);
    }
    else
    {
      errorSpan.textContent = '';
      $('#btn_save').attr('disabled',false);
    }
  }
</script>

