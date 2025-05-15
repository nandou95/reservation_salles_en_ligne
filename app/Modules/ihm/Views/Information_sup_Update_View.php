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
                        <?= lang('messages_lang.labelle_et_mod_action_info')?>
                      </h1>
                    </div>
                    <div class="col-md-6" style="float: right;">
                      <a href="<?=base_url('ihm/Information_sup')?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.labelle_et_list')?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive container " style="margin-top:50px">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form action="<?= base_url('ihm/Information_sup/update') ?>" method="POST" id="Myform">
                      <div class="row">
                       <input type="hidden" name="ID_INFOS_SUPP" id="ID_INFOS_SUPP" value="<?= $info['ID_INFOS_SUPP'] ?>">


                     </div>
                     <div class="row">

                      <div class="col-md-6">
                        <label><?= lang('messages_lang.labelle_et_description_m')?></label>
                        <input type="text" class="form-control" name="DESCR_INFOS_SUPP" id="DESCR_INFOS_SUPP" maxlength="200" value="<?=$info['DESCR_INFOS_SUPP'] ?>">
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('error_DESCR_INFOS_SUPP'); ?>
                      </div>
                      <div class="col-md-6">
                        <label><?= lang('messages_lang.labelle_et_type_info_supp')?></label>
                        <select  name="TYPE_INFOS_NAME" id="TYPE_INFOS_NAME" class="form-control ">
                          <option value="">---<?= lang('messages_lang.selectionner_transmission_du_bordereau')?>---</option>
                            <?php foreach ($infos_suppl as $value) {
                          if ($value['ID'] == $info['TYPE_INFOS_NAME']) { ?>
                          <option value="<?=$value['ID']?>" selected><?=$value['DES']?>
                          <?php } else { ?>
                         <option value="<?=$value['ID']?>" ><?=$value['DES']?>
                          <?php }
                        } ?>
                          </select>
                          <span id="error_info_name" color="red"></span>
                        <?= $validation->getError('error_TYPE_INFOS_NAME'); ?> 
                      </div>
                  </div>
                  <div class="row">
            </div>
          </form>

          <div id="SAVE" class="card-footer">
            <button type="button" style="float: right;" id="btnSave" class="btn btn-primary float-end envoi" onclick="update_action()"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?= lang('messages_lang.bouton_modifier')?></button>
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
 function update_action()
 {   
  var DESCR_INFOS_SUPP  = $('#DESCR_INFOS_SUPP').val();
  var TYPE_INFOS_NAME  = $('#TYPE_INFOS_NAME').val();
  $('#error_info').html('');
  $('#error_info_name').html('');
  var statut = 2;

  if(DESCR_INFOS_SUPP  == '')
  {
    $('#error_DESCR_INFOS_SUPP').html('<?= lang('messages_lang.error_message')?>');
    statut = 1;
  }

  if( TYPE_INFOS_NAME == '')
  {
    $('#error_TYPE_INFOS_NAME').html('<?= lang('messages_lang.error_message')?>');
    statut = 1;
  }

  if(statut == 2)
  {
    document.getElementById("Myform").submit();
  }
}
</script>

<script>
  function save()
  {
    var ETAPE_ID=$('#ETAPE_ID').val();
    $('#MyFormData').submit();
  }
</script>
