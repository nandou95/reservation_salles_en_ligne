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
                </div>
                <div class="card-body">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black"><?= lang('messages_lang.ajout_observation_financiere')?></h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?=base_url('ihm/Observation_Financiere')?>" style="float: right;margin-right: 80px;margin-top:15px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> Liste</a>
                    </div>
                  </div>
                  <div class="table-responsive container " style="margin-top:50px">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form name="myform" id="myform" action="<?=base_url('ihm/Observation_Financiere/insert')?>" method="POST" enctype="multipart/form-data">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for=""><?= lang('messages_lang.th_description_fin')?><span style="color: red;">*</span></label>
                              <textarea type="text" class="form-control" id="DESC_OBSERVATION_FINANCIER" name="DESC_OBSERVATION_FINANCIER" rows="3"><?=set_value('DESC_OBSERVATION_FINANCIER')?></textarea>
                              <span class="text-danger" id="error_OBSERVATION_FINANCIER"></span>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DESC_OBSERVATION_FINANCIER'); ?>
                              <?php endif ?>
                            </div>
                          </div>
                        </div>
                        <br>
                                        
                      </div>
                    </form>
                    <div class="form-group" style="float:right;" id="SAVE">
                      <button type="button" class="btn btn-primary float-end envoi" id="btnSave"  onclick="save()"> <i class="fa fa-save" aria-hidden="true"></i>&nbsp;<?= lang('messages_lang.bouton_enregistrer')?></button>
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
    var DESC_OBSERVATION_FINANCIER  = $('#DESC_OBSERVATION_FINANCIER').val();
    $('#error_OBSERVATION_FINANCIER').html('');
    var statut = 2;
    if(DESC_OBSERVATION_FINANCIER == '')
    {
      $('#error_OBSERVATION_FINANCIER').html('<?= lang('messages_lang.labelle_et_error')?>');
      statut = 1;
    }

    if(statut == 2)
    {
      $('#myform').submit();
    }
  }
</script>