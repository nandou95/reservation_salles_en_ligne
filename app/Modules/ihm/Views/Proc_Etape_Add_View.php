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
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black"><?= lang('messages_lang.labelle_et_ajout')?></h1></div>
                      <div class="col-md-3" style="float: right;">
                        <a href="<?=base_url('ihm/Proc_Etape')?>" style="float: right;margin-right: 80px;margin-top:15px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.labelle_et_list')?></a>
                      </div>
                    </div><hr>
                  </div>
                  <form action="<?=base_url('ihm/Proc_Etape/insert')?>" id="Myform" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                            <label for=""><?= lang('messages_lang.labelle_et_etapes')?><span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="ETAPE" name="ETAPE"  value="" autofocus>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ETAPE'); ?>
                            <?php endif ?>
                            <span class="text-danger" id="error_ETAPE"></span>
                        </div>
                        <div class="col-md-6">
                            <label for=""><?= lang('messages_lang.labelle_processus')?><span style="color: red;">*</span></label>
                            <select class="form-control" id="PROCESS_ID" name="PROCESS_ID" value="<?=set_value('PROCESS_ID')?>">
                              <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                <?php
                              foreach($process as $value)
                              { 
                                if($value->PROCESS_ID==set_value('PROCESS_ID')){?>
                                  <option value="<?=$value->PROCESS_ID ?>" selected><?=$value->NOM_PROCESS?></option>
                                <?php }
                                else
                                {
                                  ?>
                                  <option value="<?=$value->PROCESS_ID ?>"><?=$value->NOM_PROCESS?></option>
                                  <?php
                                }
                              }
                              ?>
                            </select>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('PROCESS_ID'); ?>
                            <?php endif ?>
                            <span class="text-danger" id="error_PROCESS_ID"></span>
                          </div>
                          
                        <div class="col-md-6">
                          <br>
                            <label for=""><?= lang('messages_lang.labelle_et_prof')?><span style="color: red;">*</span></label>
                            <div style="display:block;">
                              <select class="form-control select2 PROFIL_ID" name="PROFIL_ID[]" id="inscompSelected" multiple>
                                <option value=""></option>
                                <?php foreach($profil as $p): ?>
                                  <option value="<?= $p->PROFIL_ID ?>"><?= $p->PROFIL_DESCR ?></option>
                                <?php endforeach; ?>
                              </select>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('PROFIL_ID'); ?>
                              <?php endif ?>
                              <span id="error_PROFIL_ID" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="col-md-8"></div>
                          <div class="col-md-4" id="SAVE">
                            <button type="button"  style="float:right;" class="btn btn-primary float-end envoi" id="btnSave"  onclick="save_user()"> <i class="fa fa-save" aria-hidden="true"></i>&nbsp;<?= lang('messages_lang.labelle_et_enre')?></button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
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
  function save_user()
  { 
    var statut = true;
    var ETAPE  = $('#ETAPE').val();
    var PROCESS_ID  = $('#PROCESS_ID').val();
    var PROFIL_ID  = $('.PROFIL_ID').val();

    if (ETAPE=='') 
    {
      $('#error_ETAPE').text('<?= lang('messages_lang.labelle_et_error')?>');
      return false;
    }else{
      $('#error_ETAPE').text('');
    }
    if (PROFIL_ID=='') 
    {
      $('#error_PROFIL_ID').text('<?= lang('messages_lang.labelle_et_error')?>');
      return false;
    }else{
      $('#error_PROFIL_ID').text('');
    }
    if (PROCESS_ID=='') 
    {
      $('#error_PROCESS_ID').text('<?= lang('messages_lang.labelle_et_error')?>');
      return false;
    }else{
      $('#error_PROCESS_ID').text('');
    }      
    if(statut==true)
    {
      document.getElementById("Myform").submit();
    }       
  }    
</script>