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
      color: #ddd;
    }
  </style>

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
                  <div style="float: right;">
                    <a href="<?php echo base_url('pip/Nomenclature_Pourcentage/liste_pourcentage_nomenclature')?>" style="float: right;margin-right: 80px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.bouton_liste') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$titre?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="pourcent" id="pourcent" action="<?=base_url('pip/Nomenclature_Pourcentage/edit_nomen_pourcent')?>" method="post" >
                        <div class="container">
                          <div class="row">
                            <input type="hidden" id="ID_NOMENCLATURE_BUDGET_POURCENT" name="ID_NOMENCLATURE_BUDGET_POURCENT" value="<?=$pourcent['ID_NOMENCLATURE_BUDGET_POURCENT']?>">
                            <div class="col-md-6">
                              <div class='form-froup'>
                                <label class="form-label"> <?= lang('messages_lang.th_nomenclature') ?>  <font color="red">*</font></label>
                                <select class="select2 form-control" name="ID_NOMENCLATURE" id="ID_NOMENCLATURE">
                                  <option value=""><?= lang('messages_lang.label_selecte') ?></option>
                                  <?php  foreach ($get_nomenclature as $keys) { ?>
                                    <?php if($keys->ID_NOMENCLATURE== $pourcent['ID_NOMENCLATURE']) { ?>
                                      <option value="<?=$keys->ID_NOMENCLATURE ?>" selected>
                                        <?=$keys->DESCR_NOMENCLATURE?></option>
                                      <?php }else{?>
                                       <option value="<?=$keys->ID_NOMENCLATURE ?>">
                                        <?=$keys->DESCR_NOMENCLATURE?></option>
                                      <?php } }?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('ID_NOMENCLATURE'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_ID_NOMENCLATURE"></font>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for=""><?= lang('messages_lang.th_pourcentage') ?><font color="red">*</font></label>
                                    <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="POURCENTAGE_NOMENCLATURE" id="POURCENTAGE_NOMENCLATURE" placeholder="" value="<?=$pourcent['POURCENTAGE_NOMENCLATURE']?>" onpaste="return false;" min="1" onkeydown="qte();" onkeyup="qte();">
                                    <font color="red" id="error_POURCENTAGE_NOMENCLATURE"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('POURCENTAGE_NOMENCLATURE'); ?>
                                    <?php endif ?>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2 mt-5 " style="float:right;" >
                                <div class="form-group " >
                                  <button onclick="save_pourcent()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_modifier') ?></button>
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

      <script type="text/javascript">
        function save_pourcent()
        {
          var ID_NOMENCLATURE = $('#ID_NOMENCLATURE').val();
          $('#error_ID_NOMENCLATURE').html('');

          var POURCENTAGE_NOMENCLATURE = $('#POURCENTAGE_NOMENCLATURE').val();
          $('#error_POURCENTAGE_NOMENCLATURE').html('');

          var statut=2;

          $champ_vide = lang('messages_lang.message_champs_obligatoire');
          if (ID_NOMENCLATURE=='')
          {
            $('#error_ID_NOMENCLATURE').html($champ_vide);
            statut=1;
          }


          if (POURCENTAGE_NOMENCLATURE=='')
          {
            $('#error_POURCENTAGE_NOMENCLATURE').html($champ_vide);
            statut=1;
          }

          var url;
          if(statut == 2)
          {
            document.getElementById("pourcent").submit();
          }
        }
      </script>

    <script type="text/javascript">
     $(".allownumericwithdecimal").on("keypress keyup blur",function (event) {
      $(this).val($(this).val().replace(/[^0-9\.|\,]/g,''));
      debugger;
      if(event.which == 44)
      {
        return true;
      }
      if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57  )) {

        event.preventDefault();
      }
    });
  </script>