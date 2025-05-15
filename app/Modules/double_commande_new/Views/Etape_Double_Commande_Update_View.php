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
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3> <?=$title?></h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Etape_Double_Commande') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
                <br>
                <div id="collapseThree" class="collapse col-md-12" aria-labelledby="headingThree" data-parent="#accordion">

                </div>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Etape_Double_Commande/update') ?>" method="POST">

                    <div class="row col-md-12">
                      <input type="hidden" class="form-control" id="ETAPE_DOUBLE_COMMANDE_ID" name="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$step['ETAPE_DOUBLE_COMMANDE_ID']?>" >
                      <div class="col-md-6">
                        <br>
                        <label for=""><?=lang('messages_lang.labelle_et_description_m')?><span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="DESC_ETAPE_DOUBLE_COMMANDE" name="DESC_ETAPE_DOUBLE_COMMANDE" value="<?=$step['DESC_ETAPE_DOUBLE_COMMANDE']?>" <?php if (isset($validation)) : ?> value="<?=set_value('DESC_ETAPE_DOUBLE_COMMANDE')?>" <?php endif ?>
                        autofocus>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('DESC_ETAPE_DOUBLE_COMMANDE'); ?>
                        <?php endif ?>
                        <span class="text-danger" id="error_DESC_ETAPE_DOUBLE_COMMANDE"></span> 
                      </div>
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.label_mouv_dep') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="MOUVEMENT_DEPENSE_ID" id="MOUVEMENT_DEPENSE_ID">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <?php  foreach ($mouvement as $keys) { ?>
                            <?php if($keys->MOUVEMENT_DEPENSE_ID ==set_value('MOUVEMENT_DEPENSE_ID') || $keys->MOUVEMENT_DEPENSE_ID == $step['MOUVEMENT_DEPENSE_ID']) { ?>
                              <option value="<?=$keys->MOUVEMENT_DEPENSE_ID ?>" selected><?=$keys->DESC_MOUVEMENT_DEPENSE?></option>
                            <?php }else{?>
                             <option value="<?=$keys->MOUVEMENT_DEPENSE_ID ?>"><?=$keys->DESC_MOUVEMENT_DEPENSE?></option>
                           <?php } }?>
                         </select>
                         <span id="error_MOUVEMENT_DEPENSE_ID" class="text-danger"></span>
                         <?php if (isset($validation)) : ?>
                          <?= $validation->getError('MOUVEMENT_DEPENSE_ID'); ?>
                        <?php endif ?>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.label_niveau_etape') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="NIVEAU_ETAPE_ID" id="NIVEAU_ETAPE_ID">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <option value="1" <?= ($step['A_FAIRE'] == 1) ? 'selected' : '' ?>><?= lang('messages_lang.select_a_faire') ?></option>
                          <option value="2" <?= ($step['DEJA_FAIT'] == 1) ? 'selected' : '' ?>><?= lang('messages_lang.select_deja_fait') ?></option>
                          <option value="3" <?= ($step['A_CORRIGER'] == 1) ? 'selected' : '' ?>><?= lang('messages_lang.select_a_corriger') ?></option>
                          <option value="4" <?= ($step['IS_TRANSMISSION'] == 1) ? 'selected' : '' ?>><?= lang('messages_lang.select_transmission') ?></option>
                          <option value="5" <?= ($step['IS_RECEPTION'] == 1) ? 'selected' : '' ?>><?= lang('messages_lang.select_reception') ?></option>
                        </select>
                        <span id="error_NIVEAU_ETAPE_ID" class="text-danger"></span>
                        <?php if (isset($validation) && $validation->hasError('NIVEAU_ETAPE_ID')) : ?>
                        <div class="text-danger"><?= $validation->getError('NIVEAU_ETAPE_ID') ?></div>
                      <?php endif ?>
                    </div>
                  </div>
                </form>

                <div class="card-footer">
                  <div style="float:right;margin-bottom:5%">
                    <a onclick="save();" id="btn_save" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_modifier') ?></a>
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


</div>
</body>

</html>


<script type="text/javascript">
  $(document).ready(function()
  {
    next_step();
  })
</script>


<script type="text/javascript">
  function save() {
    var statut = 2;

    var DESC_ETAPE_DOUBLE_COMMANDE = $('#DESC_ETAPE_DOUBLE_COMMANDE').val();
    var MOUVEMENT_DEPENSE_ID = $('#MOUVEMENT_DEPENSE_ID').val();
    var NIVEAU_ETAPE_ID = $('#NIVEAU_ETAPE_ID').val();

    $('#error_DESC_ETAPE_DOUBLE_COMMANDE').html('');
    $('#error_MOUVEMENT_DEPENSE_ID').html('');
    $('#error_NIVEAU_ETAPE_ID').html('');


    if (DESC_ETAPE_DOUBLE_COMMANDE === '') {
      statut = 1;
      $('#error_DESC_ETAPE_DOUBLE_COMMANDE').html("<?= lang('messages_lang.error_sms') ?>");
    } else {
      $('#error_DESC_ETAPE_DOUBLE_COMMANDE').html("");
    }

    if (MOUVEMENT_DEPENSE_ID === '') {
      $('#error_MOUVEMENT_DEPENSE_ID').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }

    if (NIVEAU_ETAPE_ID === '') {
      $('#error_NIVEAU_ETAPE_ID').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }


    if (statut === 2) {
      $('#my_form').submit()

    }
  }
</script>