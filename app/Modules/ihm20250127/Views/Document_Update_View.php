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
                        <?= lang('messages_lang.link_modification_doc')?>
                      </h1>
                    </div>
                    <div class="col-md-6" style="float: right;">
                      <a href="<?=base_url('ihm/Document')?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> <?= lang('messages_lang.link_list')?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive container " style="margin-top:50px">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form action="<?= base_url('ihm/Document/update') ?>" method="POST" id="Myform">
                      <div class="row">
                       <input type="hidden" name="DOCUMENT_ID" id="DOCUMENT_ID" value="<?=$doc['DOCUMENT_ID'] ?>">


                     </div>
                     <div class="row">

                      <div class="col-md-6">
                        <label><?= lang('messages_lang.labelle_et_description_m')?></label>
                        <input type="text" class="form-control" name="DESC_DOCUMENT" id="DESC_DOCUMENT" maxlength="200" value="<?=$doc['DESC_DOCUMENT'] ?>">
                        <span id="error_info" color="red"></span>
                        <?= $validation->getError('error_DESC_DOCUMENT'); ?>
                      </div>
                      <div class="col-md-6">
                        <label> <?= lang('messages_lang.type_docummentt')?></label>
                      <select class="form-control" name="DOCUMENT_TYPE_ID" id="DOCUMENT_TYPE_ID" >
                          <option value=""><?= lang('messages_lang.labelle_select')?></option>
                            <?php foreach ($document as $value) {
                          if ($value['ID'] == $doc['DOCUMENT_TYPE_ID']) { ?>
                          <option value="<?=$value['ID']?>" selected><?=$value['DES']?>
                          <?php } else { ?>
                         <option value="<?=$value['ID']?>" ><?=$value['DES']?>
                          <?php }
                        } ?>
                          </select>
                          <span id="error_info" color="red"></span>
                        <?= $validation->getError('error_DOCUMENT_TYPE_ID'); ?> 
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

  var DESC_DOCUMENT  = $('#DESC_DOCUMENT').val();
  var DOCUMENT_TYPE_ID  = $('#DOCUMENT_TYPE_ID').val();
  $('#error_info').html('');
  $('#error_info_name').html('');

  var statut = 2;
  if(DESC_DOCUMENT  == '')
  {
    $('#error_DESC_DOCUMENT').html(' <?= lang('messages_lang.error_message')?>');
    statut = 1;
  }
  
  if( DOCUMENT_TYPE_ID == '')
  {
    $('#error_DOCUMENT_TYPE_ID').html('<?= lang('messages_lang.error_message')?>');
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
