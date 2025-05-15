<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header"></div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black"><?= lang('messages_lang.modification_programme_ptba') ?></h1>
                    </div>
                    <div class="col-md-6" style="float: right;">
                      <a href="<?=base_url('ihm/Programme')?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-list text-light" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>
                  </div>
                </div>

                <div class="car-body">
                  <form name="myform" id="myform" action="<?=base_url('ihm/Programme/update')?>" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="row">
                        <input type="hidden" name="PROGRAMME_ID" id="PROGRAMME_ID" value="<?=$program['PROGRAMME_ID']?>">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?= lang('messages_lang.label_inst') ?> <span style="color: red;">*</span></label>
                            <select name="INSTITUTION_ID" id="INSTITUTION_ID" class="form-control">
                              <option value="">--- <?= lang('messages_lang.label_select') ?> ---</option>
                              <?php
                              foreach($intitule as  $value)
                              { 
                                if($value->INSTITUTION_ID== $program['INSTITUTION_ID'])
                                { 
                                  ?>
                                  <option value="<?= $value ->INSTITUTION_ID ?>" selected><?= $value->DESCRIPTION_INSTITUTION ?></option>
                                  <?php
                                }
                                else
                                {
                                  ?>
                                  <option value="<?= $value ->INSTITUTION_ID ?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                                  <?php
                                }
                              }
                              ?>
                            </select>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('INSTITUTION_ID'); ?>
                            <?php endif ?>
                            <span id="error_INSTITUTION_ID" class="text-danger"></span>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.code_ptba_programme') ?><span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="CODE_PROGRAMME" name="CODE_PROGRAMME" value="<?=$program['CODE_PROGRAMME']?>">
                            <span class="text-danger" id="error_CODE_PROGRAMME"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label for=""> <?= lang('messages_lang.intitule_programme_ptba') ?> <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="INTITULE_PROGRAMME" name="INTITULE_PROGRAMME" value="<?=$program['INTITULE_PROGRAMME']?>">
                            <span class="text-danger" id="error_INTITULE_PROGRAMME"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.th_objectif_programme') ?><span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="OBJECTIF_DU_PROGRAMME" name="OBJECTIF_DU_PROGRAMME" value="<?=$program['OBJECTIF_DU_PROGRAMME']?>">
                            <span class="text-danger" id="error_OBJECTIF_DU_PROGRAMME"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <button type='button' class="btn btn-primary" id="SAVEUN" onclick="save_educ()"> <i class="fa fa-sign-in" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier') ?> </button>
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
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  function save_educ()
  {
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var INSTITUTION_ID  = $('#INSTITUTION_ID').val();
    var CODE_PROGRAMME  = $('#CODE_PROGRAMME').val();
    var INTITULE_PROGRAMME  = $('#INTITULE_PROGRAMME').val();
    var OBJECTIF_DU_PROGRAMME  = $('#OBJECTIF_DU_PROGRAMME').val();

    $('#error_INSTITUTION_ID').html(''); 
    $('#error_CODE_PROGRAMME').html('');
    $('#error_INTITULE_PROGRAMME').html(''); 
    $('#error_OBJECTIF_DU_PROGRAMME').html('');

    var statut = 2;
    if(INSTITUTION_ID=='')
    {
      $('#error_INSTITUTION_ID').html('<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge_comptable') ?>');
      statut = 1;
    }

    if(CODE_PROGRAMME=='')
    {
      $('#error_CODE_PROGRAMME').html('<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge_comptable') ?>');
      statut = 1;
    }

    if(INTITULE_PROGRAMME=='')
    {
      $('#error_INTITULE_PROGRAMME').html('<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge_comptable') ?>');
      statut = 1;
    }

    if(OBJECTIF_DU_PROGRAMME  == '')
    {
      $('#error_OBJECTIF_DU_PROGRAMME').html('<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge_comptable') ?>');
      statut = 1;
    }

    if(statut==2)
    {
      url = "<?= base_url('ihm/Programme/update') ?>";
      $.post(url,
      {
        PROGRAMME_ID:PROGRAMME_ID,
        INSTITUTION_ID:INSTITUTION_ID,
        CODE_PROGRAMME:CODE_PROGRAMME,
        INTITULE_PROGRAMME:INTITULE_PROGRAMME,
        OBJECTIF_DU_PROGRAMME:OBJECTIF_DU_PROGRAMME
      },
      function(data)
      {
        if(data.status==true) 
        {
          window.location.href= "<?=base_url('ihm/Programme')?>";
        }
      });
    }
  }
</script>