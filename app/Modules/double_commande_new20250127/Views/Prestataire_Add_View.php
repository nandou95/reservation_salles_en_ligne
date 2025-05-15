<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
    <?php $validation = \Config\Services::validation(); ?>
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
          <div class="header">
          </div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black"><?= lang('messages_lang.ajout_bene') ?></h1>
                    </div>
                    <div class="col-md-6" style="float: right;">
                      <a href="<?=base_url('double_commande_new/Prestataire')?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-list text-light" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>
                  </div>
                </div>
                <div class="car-body">
                  <form id='MyFormData' action="<?=base_url('double_commande_new/Prestataire/save') ?>" method="POST">
                    
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?= lang('messages_lang.label_type_benef') ?> <span style="color: red;">*</span></label>
                            <select class="form-control" name="TYPE_BENEFICIAIRE_ID" id="TYPE_BENEFICIAIRE_ID" onchange="show_nif()">
                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                              <?php 
                                foreach($type_beneficiaire as $key) { 
                                  if ($key->TYPE_BENEFICIAIRE_ID==set_value('TYPE_BENEFICIAIRE_ID')) { 
                                      echo "<option value='".$key->TYPE_BENEFICIAIRE_ID."' selected>".$key->DESC_TYPE_BENEFICIAIRE."</option>";
                                  } else{
                                      echo "<option value='".$key->TYPE_BENEFICIAIRE_ID."' >".$key->DESC_TYPE_BENEFICIAIRE."</option>"; 
                                  } }
                              ?>
                            </select>
                            <span id="error_ben" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('TYPE_BENEFICIAIRE_ID'); ?>
                            <?php endif ?>
                          </div>
                        </div>

                        <div class="col-md-4" id="type_individu">
                          <div class="form-group">
                            <label><?= lang('messages_lang.ajout_ind') ?><span style="color: red;">*</span></label>
                            <select class="form-control" name="ID_TYPE_INDIVINDU" id="ID_TYPE_INDIVINDU" onchange="get_nom()">
                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                              <?php 
                                foreach($type_individu as $key) { 
                                  if ($key->ID_TYPE_INDIVINDU==set_value('ID_TYPE_INDIVINDU')) { 
                                      echo "<option value='".$key->ID_TYPE_INDIVINDU."' selected>".$key->DESCR_INDIVINDU."</option>";
                                  } else{
                                      echo "<option value='".$key->ID_TYPE_INDIVINDU."' >".$key->DESCR_INDIVINDU."</option>"; 
                                  } }
                              ?>
                            </select>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ID_TYPE_INDIVINDU'); ?>
                            <?php endif ?>
                            <span id="error_indiv" class="text-danger"></span>
                          </div>
                        </div>

                        <div class="col-md-4" id="nom">
                          <div class="form-group">
                            <label id="label_nom"><span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="NOM_PRESTATAIRE" name="NOM_PRESTATAIRE" maxlength="100">
                            <span class="text-danger" id="error_nom"></span>
                            <?= $validation->getError('NOM_PRESTATAIRE'); ?>
                          </div>
                        </div>

                        <div class="col-md-4" id="prenom">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.labelle_prenom') ?><!-- <span style="color: red;">*</span> --></label>
                            <input type="text" class="form-control" id="PRENOM_PRESTATAIRE" name="PRENOM_PRESTATAIRE" maxlength="50">
                            <span class="text-danger" id="error_prenom"></span>
                            <?= $validation->getError('PRENOM_PRESTATAIRE'); ?>
                          </div>
                        </div>

                        <div class="col-md-4" id="div_nif">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.labelle_NIF') ?><span style="color: red;">*</span></label>
                            <input type="number" class="form-control" id="NIF_PRESTATAIRE" name="NIF_PRESTATAIRE" maxlength="20">
                            <span class="text-danger" id="error_nif"></span>
                          </div>
                        </div>
                        
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?= lang('messages_lang.table_banque') ?> </label>
                            <select class="form-control select2" name="BANQUE_ID" id="BANQUE_ID" onchange="autre()">
                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                              <option value="0"><?= lang('messages_lang.selection_autre') ?></option>
                              <?php 
                                foreach($banque as $key) { 
                                  if ($key->BANQUE_ID==set_value('BANQUE_ID')) { 
                                      echo "<option value='".$key->BANQUE_ID."' selected>".$key->NOM_BANQUE."</option>";
                                  } else{
                                      echo "<option value='".$key->BANQUE_ID."' >".$key->NOM_BANQUE."</option>"; 
                                  } }
                              ?>
                            </select>
                            <span id="error_bank" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('BANQUE_ID'); ?>
                            <?php endif ?>
                          </div>
                        </div>

                        <div class="col-md-4" id="autre_banque">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.label_otre_bak') ?></label>
                            <input type="text" class="form-control" id="AUTRE_BANQUE" name="AUTRE_BANQUE">
                            <span class="text-danger" id="error_autre_banque"></span>
                            <?= $validation->getError('AUTRE_BANQUE'); ?>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="form-group">
                            <label for=""><?= lang('messages_lang.label_cpte_bak') ?></label>
                            <input type="text" class="form-control" id="COMPTE_BANCAIRE" name="COMPTE_BANCAIRE">
                            <span class="text-danger" id="error_num_compte"></span>
                            <?= $validation->getError('COMPTE_BANCAIRE'); ?>
                          </div>
                        </div>  
                      </div>
                    </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistrer') ?></a>
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
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function () {
    $('#div_nif').hide();
    $('#prenom').show();
    $('#label_nom').html('<?= lang('messages_lang.Nom') ?> <span style="color: red;">*</span>');
    $('#span_bank').text('*');
    $('#span_compte').text('*');
    $('#autre_banque').hide();
  });
</script>

<script type="text/javascript">
  function get_nom()
  {
    var ID_TYPE_INDIVINDU=$('#ID_TYPE_INDIVINDU').val();

    if (ID_TYPE_INDIVINDU==2)
    {
      $('#label_nom').html('<?= lang('messages_lang.label_org') ?> <span style="color: red;">*</span>');
      $('#prenom').hide();
    }else{
      $('#label_nom').html('<?= lang('messages_lang.Nom') ?> <span style="color: red;">*</span>');
      $('#prenom').show();
    }
  }
</script>

<script type="text/javascript">
  function save()
  {
    var NOM_PRESTATAIRE  = $('#NOM_PRESTATAIRE').val();
    var PRENOM_PRESTATAIRE  = $('#PRENOM_PRESTATAIRE').val();
    var NIF_PRESTATAIRE  = $('#NIF_PRESTATAIRE').val();
    // var BANQUE_ID  = $('#BANQUE_ID').val();
    // var COMPTE_BANCAIRE  = $('#COMPTE_BANCAIRE').val();
    var TYPE_BENEFICIAIRE_ID  = $('#TYPE_BENEFICIAIRE_ID').val();
    var ID_TYPE_INDIVINDU  = $('#ID_TYPE_INDIVINDU').val();
    var AUTRE_BANQUE  = $('#AUTRE_BANQUE').val();

    $('#error_nom').html(''); 
    $('#error_prenom').html('');
    $('#error_bank').html(''); 
    $('#error_nif').html('');
    $('#error_num_compte').html('');
    $('#error_ben').html('');
    $('#error_indiv').html('');
    $('#error_autre_banque').html('');

    var statut = 2;

    
    if (ID_TYPE_INDIVINDU == 2)
    {
      if(NOM_PRESTATAIRE=='')
      {
        $('#error_nom').html('<?= lang('messages_lang.error_message') ?>');
        statut = 1;
      }
    }else{
      if(NOM_PRESTATAIRE=='')
      {
        $('#error_nom').html('<?= lang('messages_lang.error_message') ?>');
        statut = 1;
      }
      // if(PRENOM_PRESTATAIRE  == '')
      // {
      //   $('#error_prenom').html('<?= lang('messages_lang.error_message') ?>');
      //   statut = 1;
      // }
    }

    // if(BANQUE_ID  == '')
    // {
    //   $('#error_bank').html('<?= lang('messages_lang.error_message') ?>');
    //   statut = 1;
    // }
    // if(COMPTE_BANCAIRE  == '')
    // {
    //   $('#error_num_compte').html('<?= lang('messages_lang.error_message') ?>');
    //   statut = 1;
    // }
    if(TYPE_BENEFICIAIRE_ID  == '')
    {
      $('#error_ben').html('<?= lang('messages_lang.error_message') ?>');
      statut = 1;
    }


    if (TYPE_BENEFICIAIRE_ID==1) 
    {
      if(NIF_PRESTATAIRE  == '')
      {
        $('#error_nif').html('<?= lang('messages_lang.error_message') ?>');
        statut = 1;
      }
      if(ID_TYPE_INDIVINDU  == '')
      {
        $('#error_indiv').html('<?= lang('messages_lang.error_message') ?>');
        statut = 1;
      }
    }

    // if (BANQUE_ID=='autre')
    // {
    //   if(AUTRE_BANQUE  == '')
    //   {
    //     $('#error_autre_banque').html('<?= lang('messages_lang.error_message') ?>');
    //     statut = 1;
    //   }
    // }

    if(statut == 2)
    {
      $('#MyFormData').submit()
    }
  }
</script>

<script>
  $('#COMPTE_BANCAIRE').on('input', function()
  {
    $(this).val($(this).val().toUpperCase());
    $(this).val(this.value.substring(0,99));      
  });
  $('#NIF_PRESTATAIRE').on('input', function()
  {
    $(this).val(this.value.substring(0,10));      
  });
</script>

<script>
  function show_nif()
  {
    var TYPE_BENEFICIAIRE_ID  = $('#TYPE_BENEFICIAIRE_ID').val();

    var ID_TYPE_INDIVINDU=$('#ID_TYPE_INDIVINDU').val();

    if (TYPE_BENEFICIAIRE_ID==1)
    {
      $('#div_nif').show();
      $('#span_bank').text('*')
      $('#span_compte').text('*')
      $('#type_individu').show()

      if (ID_TYPE_INDIVINDU==2)
      {
        $('#label_nom').html('<?= lang('messages_lang.label_org') ?> <span style="color: red;">*</span>');
        $('#prenom').hide();
      }else{
        $('#label_nom').html('<?= lang('messages_lang.Nom') ?> <span style="color: red;">*</span>');
        $('#prenom').show();
      }

    }else{
      $('#div_nif').hide();
      $('#span_bank').text('')
      $('#span_compte').text('')
      $('#type_individu').hide()
      $('#label_nom').html('<?= lang('messages_lang.Nom') ?> <span style="color: red;">*</span>');
      $('#prenom').show();
    }
  }
</script>

<script>
  function autre()
  {
    var BANQUE_ID = $('#BANQUE_ID').val();
    if (BANQUE_ID==0)
    {
      $('#autre_banque').show();
    }else{
      $('#autre_banque').hide();
    }
  }
</script>
