<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo view('includesbackend/header.php');?>
    <?php $validation = \Config\Services::validation(); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>

    <style>
      .vl {
        border-left: 1px solid #ddd;
        height: 250px;
        position: absolute;
        left: 100%;
        margin-left: -3px;
        top: 0;
      }
    </style>

    <style>
      .vl2 {
        border-left: 1px solid #ddd;
        height: 185px;
        position: absolute;
        left: 100%;
        margin-left: -3px;
        top: 0;
      }
    </style>
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
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="car-body">
                    <div class="row">
                      <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('double_commande_new/Transfert_Meme_Activite/send_data/')?>" method="post" >
                        <div class="col-12">
                          <h6 style="font-size: 18px" class="header-title text-black text-uppercase">
                            <?= lang('messages_lang.transfert_meme_tache') ?>
                            <?php
                            if(session()->getFlashKeys('alert'))
                            {
                              ?>
                              <div class="alert alert-success" id="message">
                                <?php echo session()->getFlashdata('alert')['message']; ?>
                              </div>
                              <?php
                            }
                            ?>
                          </h6>
                        </div>
                        <div class="col-12">
                          <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                            <div class="row" style="margin :  5px">
                              <div class="col-12" >
                                <div class="form-group">
                                  <label><?= lang('messages_lang.label_motif') ?><span style="color: red;">*</span></label>
                                  <select  class="form-control" name="MOTIF_TACHE_ID" id="MOTIF_TACHE_ID" onclick="hierarchie(this.value)">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php
                                    foreach ($motif as $key) 
                                    {
                                      if($key->MOTIF_TACHE_ID == set_value('MOTIF_TACHE_ID'))
                                      {
                                        echo '<option value="'.$key->MOTIF_TACHE_ID .'" selected>
                                        '.$key->DESCR_MOTIF_TACHE.'</option>';
                                      }
                                      else
                                      {
                                        echo '<option value="'.$key->MOTIF_TACHE_ID .'">
                                        '.$key->DESCR_MOTIF_TACHE.'</option>';
                                      }
                                    }
                                    ?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('MOTIF_TACHE_ID'); ?>
                                  <?php endif ?>
                                  <font color="red" id="error_MOTIF_TACHE_ID"></font>
                                </div>
                              </div>
                              <div class="col-4" id="respo1" style="display: none;">
                                <div class="form-group">
                                  <label><?= lang('messages_lang.labelle_nom') ?><span style="color: red;">*</span></label>
                                  <input onkeyup="SetMaxLength(1)" autocomplete="off" type="text" name="NOM" id="NOM" class="form-control" value="<?= set_value('NOM')?>" >
                                  <font color="red" id="error_NOM"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('NOM'); ?>
                                  <?php endif ?>
                                </div>
                              </div>
                              <div class="col-4" id="respo2" style="display: none;">
                                <div class="form-group">
                                  <label><?= lang('messages_lang.labelle_prenom') ?><span style="color: red;">*</span></label>
                                  <input onkeyup="SetMaxLength(2)" autocomplete="off" type="text" name="PRENOM" id="PRENOM" class="form-control" value="<?= set_value('PRENOM')?>" >
                                  <font color="red" id="error_PRENOM"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('PRENOM'); ?>
                                  <?php endif ?>
                                </div>
                              </div>
                              <div class="col-4" id="respo3" style="display: none;">
                                <div class="form-group">
                                  <label><?= lang('messages_lang.poste') ?><span style="color: red;">*</span></label>
                                  <input onkeyup="SetMaxLength(3)" autocomplete="off" type="text" name="POSTE" id="POSTE" class="form-control" value="<?= set_value('POSTE')?>" >
                                  <font color="red" id="error_POSTE"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('POSTE'); ?>
                                  <?php endif ?>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Ligne bidgetaire qui envoie -->
                        <div class="col-12">
                          <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                            <div class="row" style="margin :  5px">
                              <div class="col-12">
                                <div class="row">
                                  <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                                  <div class="col-6">
                                    <label class="form-label"><?= lang('messages_lang.labelle_inst_min') ?> <span style="color: red">*</span></label>
                                    <select autofocus onchange="get_sousTutel();get_inst();" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php 
                                      foreach ($institution as $keyinstitution) 
                                      {
                                        echo '<option value="'.$keyinstitution->INSTITUTION_ID.'">
                                        '.$keyinstitution->DESCRIPTION_INSTITUTION.'</option>';
                                      }
                                      ?>
                                    </select>
                                    <font color="red" id="error_INSTITUTION_ID"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('INSTITUTION_ID'); ?>
                                    <?php endif ?>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="form-group">
                                      <label id="label_sous_tutel"><?= lang('messages_lang.label_sousTitre') ?><span style="color: red">*</span> <span id="loading_sous_tutel"></span></label>
                                      <select onchange="get_code()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      </select>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                                      <?php endif ?>
                                      <font color="red" id="error_SOUS_TUTEL_ID"></font>
                                    </div>
                                  </div>
                                  <div class="col-6">
                                    <label class="form-label"><?= lang('messages_lang.labelle_code_budgetaire') ?><span style="color: red">*</span> <b id="loading_budget"></b></label>
                                    <select class="form-control select2" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" onchange="get_activite();get_taches();" >
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID"></font>
                                  </div>
                                  <div class="col-6"  id="act_id" hidden="true" >
                                    <label class="form-label"><?= lang('messages_lang.labelle_activite') ?> <span style="color: red">*</span> <b id="loading_act"></b></label>
                                    <select onchange="get_taches()"  class="form-control" id="PTBA_ID" name="PTBA_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('PTBA_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_PTBA_ID"></font>
                                  </div>
                                  <div class="col-6">
                                    <label class="form-label"><?= lang('messages_lang.label_taches') ?> <span style="color: red">*</span> <span id="loading_tache"></span></label>
                                    <select onchange="get_MontantVoteByActivite()" class="form-control" id="PTBA_TACHE_ID" name="PTBA_TACHE_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('PTBA_TACHE_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_PTBA_TACHE_ID"></font>
                                  </div>
                                  <div class="col-4">
                                    <label class="form-label"><?= lang('messages_lang.labelle_tranche') ?> <span style="color: red">*</span></label>
                                    <select onchange="getMontantAnnuel()" class="form-control" id="TRIMESTRE_ID" name="TRIMESTRE_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php foreach ($tranches as $keytranches)
                                      {
                                        echo '<option value="'.$keytranches->TRIMESTRE_ID.'">
                                          '.$keytranches->DESC_TRIMESTRE.'</option>';
                                      }
                                      ?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('TRIMESTRE_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_TRIMESTRE_ID"></font>
                                    <font color="red" id="error_TRIMESTRE_ID2"></font>
                                  </div>
                                  <div class="col-4">
                                    <label class="form-label"><?= lang('messages_lang.label_mont_select') ?> <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" name="MONTANT_TRIMESTRE_SELECTION" id="MONTANT_TRIMESTRE_SELECTION">
                                    <font color="red" id="error_MONTANT_TRIMESTRE_SELECTION"></font>
                                  </div>
                                  <div class="col-4">
                                    <label class="form-label"><?= lang('messages_lang.mont_a_transf') ?> <span style="color: red">*</span></label>
                                    <input onkeyup="get_MontantApresTransfert();" type="text" class="form-control" name="MONTANT_TRANSFERT" id="MONTANT_TRANSFERT">
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('MONTANT_TRANSFERT'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_MONTANT_TRANSFERT"></font>
                                    <font color="red" id="error_MONTANT_TRANSFERT_SUP"></font>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div style="border:1px solid #ddd;border-radius:5px">
                            <div class="row" style="margin :  5px">
                              <div class="col-12">
                                <h4><center> <i class="fa fa-circle"></i>  <?= lang('messages_lang.label_destin') ?></center></h4><br>
                              </div>

                              <div class="col-4">
                                <label class="form-label"><?= lang('messages_lang.label_term_dest') ?> <span style="color: red">*</span></label>
                                <select onchange="getMontantDest()" class="form-control" id="TRIMESTRE_ID_DESTINATION" name="TRIMESTRE_ID_DESTINATION">
                                  <option value=""><?= lang('messages_lang.label_select') ?></option>
                                  <?php 
                                  foreach ($gettrimdest as $keytranches)
                                  { 
                                    echo '<option value="'.$keytranches->TRIMESTRE_ID.'">
                                      '.$keytranches->DESC_TRIMESTRE.'</option>';
                                  }
                                  ?>
                                </select>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('TRIMESTRE_ID_DESTINATION'); ?>
                                <?php endif ?>
                                <font color="red" id="error_TRIMESTRE_ID_DESTINATION"></font>
                              </div>

                              <div class="col-4">
                                <label id="montant_vote_label" class="form-label"> <?= lang('messages_lang.labelle_montant_vote') ?><span id="loading_vote"></span></label>
                                <input type="text" name="MONTANT_VOTE" id="MONTANT_VOTE" class="form-control">
                                <font color="red" id="error_MONTANT_VOTE"></font>
                              </div>

                              <div class="col-4">
                                <label class="form-label"><?= lang('messages_lang.label_Money_res') ?> <span id="loading_montant_restant"></span></label>
                                <input type="text" name="MONTANT_RESTANT" id="MONTANT_RESTANT" class="form-control">
                                <font color="red" id="error_MONTANT_RESTANT"></font>
                              </div>

                              <div class="col-4">
                                <label class="form-label"><?= lang('messages_lang.labelle_montant_apres_transfert') ?> <span id="loading_montant_restant"></span></label>
                                <input type="text" name="MONTANT_APRES_TRANSFERT" id="MONTANT_APRES_TRANSFERT" class="form-control">
                                <font color="red" id="error_MONTANT_APRES_TRANSFERT"></font>
                              </div>
                            </div>
                          </div>
                        </div>
                        <br>
                        <div class="col-12">
                          <div style="border:1px solid #ddd;border-radius:5px">
                            <div class="row" style="margin :  5px">
                              <div class="col-6">
                                <div class="form-group">
                                  <label><?= lang('messages_lang.label_auto_trans') ?><span style="color: red;">*</span></label>
                                  <input onchange="ValidationFile();" accept=".pdf" type="file" name="AUTORISATION_TRANSFERT" id="AUTORISATION_TRANSFERT" class="form-control" >
                                  <font color="red" id="error_AUTORISATION_TRANSFERT"></font>
                                  <font color="red" id="error_AUTORISATION_TRANSFERT_VOLUMINEUX"></font>
                                  <font color="red" id="error_AUTORISATION_TRANSFERT_FORMAT"></font>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-12"><br>
                          <button onclick="send_data()" type="button" class="btn btn-primary btn-block"><?= lang('messages_lang.bouton_confirmer') ?> <span id="loading_btn"></span></button>
                        </div>
                      </form>
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

<div class='modal fade' id='detail_infos' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_confirmation') ?></h5>
      </div>
      <div class='modal-body'>
        <div id="infos_data"></div>
      </div>
      <div class='modal-footer'>
        <button onclick="deleteFile()" class='btn btn-primary btn-md' data-dismiss='modal'><i class="fa fa-pencil"></i> <?= lang('messages_lang.bouton_modifier') ?></button>
        <button onclick="send_data2()" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.bouton_confirmer') ?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function ValidationFile()
{
  var fileInput = document.getElementById('AUTORISATION_TRANSFERT');
  var filePath = fileInput.value;
  // Allowing file type
  var allowedExtensions = /(\.pdf)$/i;
  
  if (!allowedExtensions.exec(filePath))
  {
    $('#error_AUTORISATION_TRANSFERT_FORMAT').text("<?= lang('messages_lang.bordereau_message') ?>");
    fileInput.value = '';
    return false;
  }
  else
  {
    // Check if any file is selected. 
    if (fileInput.files.length > 0)
    { 
      for (var i = 0; i <= fileInput.files.length - 1; i++)
      { 
        var fsize = fileInput.files.item(i).size; 
        var file = Math.round((fsize / 1024)); 
        // The size of the file. 
        if (file >8* 1024)
        { 
          $('#error_AUTORISATION_TRANSFERT_VOLUMINEUX').text('<?= lang('messages_lang.taille_bordereau_message') ?>');
          fileInput.value = '';
        }else
        {
         $('#error_AUTORISATION_TRANSFERT_VOLUMINEUX').text(''); 
        }
      } 
    }
  }
}

function show()
{
  if($("#FRAIS_OUI").prop("checked")) {   

    $('#error_MOTIF_TACHE_ID').html('');
    $('#MOTIF_TACHE_ID').val('');
    $("#rep").css("display", "none");

    $('#NOM').val('');
    $('#PRENOM').val('');
    $('#POSTE').val('');

    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html(''); 
    $('#respo1').hide();
    $('#respo2').hide();
    $('#respo3').hide();
  } 

  if($("#FRAIS_NON").prop("checked")) {   

    var validationSet = <?php echo isset($validation) ? 'true' : 'false'; ?>;

    if (validationSet){

      $("#rep").css("display", "block");

    }else{

      $('#error_MOTIF_TACHE_ID').html('');
      $('#MOTIF_TACHE_ID').val('');
      $("#rep").css("display", "block");
    }
  }   
}

function hierarchie() 
{
  var MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();

  if(MOTIF_TACHE_ID !='')
  {
    if (MOTIF_TACHE_ID == 2 || MOTIF_TACHE_ID == 3)
    {
      window.addEventListener('load', function() {

      var validationSet = <?php echo isset($validation) ? 'true' : 'false'; ?>;

      if (validationSet){

        $('#NOM').val();
        $('#PRENOM').val();
        $('#POSTE').val();
        $('#respo1').show();  
        $('#respo2').show();  
        $('#respo3').show();  

      }else{

        $('#NOM').val('');
        $('#PRENOM').val('');
        $('#POSTE').val('');

        $('#error_NOM').html('');
        $('#error_PRENOM').html('');
        $('#error_POSTE').html('');
        $('#respo1').show();  
        $('#respo2').show();  
        $('#respo3').show();  

      }

      });

        $('#respo1').show();   
        $('#respo2').show();   
        $('#respo3').show();   

    }else {

      $('#NOM').val('');
      $('#PRENOM').val('');
      $('#POSTE').val('');

      $('#error_NOM').html('');
      $('#error_PRENOM').html('');
      $('#error_POSTE').html(''); 
      $('#respo1').hide();
      $('#respo2').hide();
      $('#respo3').hide();
    }   

  }else{

    $('#NOM').val('');
    $('#PRENOM').val('');
    $('#POSTE').val('');

    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html(''); 
    $('#respo1').hide();
    $('#respo2').hide();
    $('#respo3').hide();
  }
} 
</script>
<!----------------------- Les dépendances ---------------------------------------->
<script type="text/javascript">
function get_sousTutel()
{
  var INSTITUTION_ID = $('#INSTITUTION_ID').val();

  if(INSTITUTION_ID=='')
  {
    $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#SOUS_TUTEL_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
  }
  else
  {
    if (INSTITUTION_ID==12)
    {
      $('#lettre_id').text("");
    }
    else
    {
      $('#lettre_id').text("*");
    }

    $('#SOUS_TUTEL_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    var url = "<?=base_url()?>/double_commande_new/Transfert_Meme_Activite/get_sousTutel/"+INSTITUTION_ID;

    $.ajax(
    {

      url:url,
      type:"GET",
      dataType:"JSON",
      beforeSend:function() {
        $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {   
        $('#SOUS_TUTEL_ID').html(data.SousTutel);
        $('#loading_sous_tutel').html("");
      }
    });
  }
}

function get_inst()
{
  var INSTITUTION_ID = $('#INSTITUTION_ID').val();
  if(INSTITUTION_ID=='')
  {
    $('#TYPE_INSTITUTION_ID').val(0);
  }
  else
  {
    $.ajax(
    {
      url:"<?=base_url()?>/double_commande_new/Transfert_Meme_Activite/get_inst/"+INSTITUTION_ID,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
        $('#TYPE_INSTITUTION_ID').val(data.inst_activite);
    
        
        if(data.inst_activite == 2)
        {
          $('#act_id').attr('hidden', false);
        }
        else
        {
          $('#act_id').attr('hidden', true);
        }

      }
    });

    
  }
}

function get_code()
{
  var INSTITUTION_ID = $('#INSTITUTION_ID').val();
  var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
  if(SOUS_TUTEL_ID=='')
  {
    $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
  }
  else
  {
    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    var url = "<?=base_url()?>/double_commande_new/Transfert_Meme_Activite/get_code/"+SOUS_TUTEL_ID;

    $.ajax(
    {
      url:url,
      type:"POST",
      dataType:"JSON",
      data:{
        INSTITUTION_ID:INSTITUTION_ID,
      },
      beforeSend:function()
      {
        $('#loading_budget').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html(data.codeBudgetaire);
        $('#loading_budget').html("");

      }
    });
  }
}

function get_activite()
{
  var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
  if(CODE_NOMENCLATURE_BUDGETAIRE_ID=='')
  {
    $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
  }
  else
  {

    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    $.ajax(
    {
      url:"<?=base_url()?>/double_commande_new/Transfert_Meme_Activite/get_activite1/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
      type:"GET",
      dataType:"JSON",         
      beforeSend:function()
      {
        $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#PTBA_ID').html(data.activite);
        $('#loading_act').html("");
      }
    });

  }
}

function get_taches() 
{
  var PTBA_ID = $('#PTBA_ID').val();
  var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
  var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
  //alert(TYPE_INSTITUTION_ID);
  var id = '';

  if (TYPE_INSTITUTION_ID == 1) {
    id = CODE_NOMENCLATURE_BUDGETAIRE_ID;
  } else if (TYPE_INSTITUTION_ID == 2) {
    id = PTBA_ID;
  } else {
    id = '';
  }

  if (id == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == '') {
    $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
  } else {
    $.ajax({
      url: "<?=base_url('')?>/double_commande_new/Transfert_Meme_Activite/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
      type: "GET",
      dataType: "JSON",
      data: {
        PTBA_ID: PTBA_ID,
        CODE_NOMENCLATURE_BUDGETAIRE_ID: CODE_NOMENCLATURE_BUDGETAIRE_ID,
        TYPE_INSTITUTION_ID: TYPE_INSTITUTION_ID,
      },
      beforeSend: function() {
        $('#loading_tache').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data) {
        $('#PTBA_TACHE_ID').html(data.tache_activite);
        $('#loading_tache').html("");
      }
    });
  }
}  
</script>

<script type="text/javascript">
function getMontantAnnuel(argument)
{
  var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
  var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();

  if (PTBA_TACHE_ID=='') 
  {
    $('#error_TRIMESTRE_ID2').text("<?= lang('messages_lang.message_selection_activite') ?>");
    $('#TRIMESTRE_ID').val('')
  }
  else
  {
    $('#error_TRIMESTRE_ID2').text("");
    $.ajax(
    {
      url:"<?=base_url('/double_commande_new/Transfert_Meme_Activite/getMontantAnnuel')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        PTBA_TACHE_ID:PTBA_TACHE_ID,
        TRIMESTRE_ID:TRIMESTRE_ID
      },
      beforeSend:function() {
      },
      success: function(data)
      {
        var valsel = data.MONTANT_TRIMESTRE_SELECTION;
        var trimSel = valsel.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        $('#MONTANT_TRIMESTRE_SELECTION').val(trimSel);
        $('#TRIMESTRE_ID_DESTINATION').html(data.html);
      }
    });
  }
}

function getMontantDest(argument)
{
  var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
  var TRIMESTRE_ID_DESTINATION=$('#TRIMESTRE_ID_DESTINATION').val();

  if (PTBA_TACHE_ID=='')
  {
    $('#error_TRIMESTRE_ID2').text("<?= lang('messages_lang.message_selection_activite') ?>");
    $('#TRIMESTRE_ID').val('')
  }
  else
  {
    $('#error_TRIMESTRE_ID2').text("");
    $.ajax(
    {
      url:"<?=base_url('/double_commande_new/Transfert_Meme_Activite/getMontantDest')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        PTBA_TACHE_ID:PTBA_TACHE_ID,
        TRIMESTRE_ID_DESTINATION:TRIMESTRE_ID_DESTINATION
      },
      beforeSend:function() {
      },
      success: function(data)
      {
        $('#MONTANT_VOTE').val(data.MONTANT_VOTE.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
        $('#MONTANT_RESTANT').val(data.MONTANT_RESTANT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
      }
    });
  }
}

function get_MontantVoteByActivite()
{
  var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
  var MONTANT_RESTANT=$('#MONTANT_RESTANT').val();

  $('#MONTANT_TRANSFERT').val('');
  $('#MONTANT_APRES_TRANSFERT').val('');
  $('#TRIMESTRE_ID').val('');
  $('#MONTANT_VOTE').val('');
  $('#MONTANT_RESTANT').val('');
  $('#MONTANT_TRIMESTRE_SELECTION').val('');

  if (PTBA_TACHE_ID!='') 
  {
    $.ajax(
    {
      url:"<?=base_url('/double_commande_new/Transfert_Meme_Activite/get_MontantVoteByActivite')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        PTBA_TACHE_ID:PTBA_TACHE_ID,
      },
      beforeSend:function() {
        $('#loading_montant_restant').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_vote').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#loading_montant_restant').html("");
        $('#loading_vote').html("");
      }
    });
  }           
}

function get_MontantApresTransfert()
{
  var MONTANT_TRANSFERT = $('#MONTANT_TRANSFERT').val().replace(/\s/g, '');
  var MONTANT_RESTANT = $('#MONTANT_RESTANT').val().replace(/\s/g, '');
  var MONTANT_TRIMESTRE_SELECTION = $('#MONTANT_TRIMESTRE_SELECTION').val().replace(/\s/g, '');
  var PTBA_TACHE_ID = $('#PTBA_TACHE_ID').val();

  if (MONTANT_TRANSFERT !== '') {
    $('#error_MONTANT_TRANSFERT').text('');
    var getNumber = MONTANT_TRANSFERT.substring(0, 1);
    if (getNumber === '0') {
      $('#MONTANT_TRANSFERT').val('');
    } else {
      if (parseFloat(MONTANT_TRANSFERT) > parseFloat(MONTANT_TRIMESTRE_SELECTION)) {
        $('#error_MONTANT_TRANSFERT_SUP').text("<?= lang('messages_lang.error_money_sup') ?>");
        $('#MONTANT_APRES_TRANSFERT').val('');
        return false;
      } else {
        $('#error_MONTANT_TRANSFERT_SUP').text('');
      }
    }
  }

  if (PTBA_TACHE_ID !== '') {
    
    var MONT_APRES_TRANSF = parseFloat(MONTANT_RESTANT) + parseFloat(MONTANT_TRANSFERT);
    var mont_after = MONT_APRES_TRANSF.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    //alert(mont_after);

    $('#MONTANT_APRES_TRANSFERT').val(mont_after);
  }
}

$("#MONTANT_TRANSFERT").on('input', function() {
  var value = $(this).val();
  value = value.replace(/[^0-9.]/g, '');
  $(this).val(value.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
});
</script>

<script type="text/javascript">
$(document).ready(function() 
{
  show();
  hierarchie();

  $("#message").delay(5000).hide('slow');

  $('#MONTANT_TRANSFERT').bind('paste', function (e) {
   e.preventDefault();
 });

  document.getElementById('MONTANT_RESTANT').readOnly = true;
  document.getElementById('MONTANT_VOTE').readOnly = true;
  document.getElementById('MONTANT_APRES_TRANSFERT').readOnly = true;
  document.getElementById('MONTANT_TRIMESTRE_SELECTION').readOnly = true;
});
</script>

<script type="text/javascript">
function send_data(argument)
{
  var statut = true;
  var MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();
  var NOM = $('#NOM').val();
  var PRENOM = $('#PRENOM').val();
  var POSTE = $('#POSTE').val();

  var INSTITUTION_ID=$('#INSTITUTION_ID').val()
  var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val()
  var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
  var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val()
  var PTBA_ID=$('#PTBA_ID').val()
  var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val()
  var TRIMESTRE_ID=$('#TRIMESTRE_ID').val()
  var MONTANT_VOTE=$('#MONTANT_VOTE').val()
  var MONTANT_RESTANT=$('#MONTANT_RESTANT').val()
  var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val()
  var MONTANT_APRES_TRANSFERT=$('#MONTANT_APRES_TRANSFERT').val()
  var MONTANT_TRIMESTRE_SELECTION=$('#MONTANT_TRIMESTRE_SELECTION').val()
  var AUTORISATION_TRANSFERT=$('#AUTORISATION_TRANSFERT').val()
  var TRIMESTRE_ID_DESTINATION=$('#TRIMESTRE_ID_DESTINATION').val();
  
  if (MOTIF_TACHE_ID=="")
  {
    $('#error_MOTIF_TACHE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MOTIF_TACHE_ID').text('');

    if(MOTIF_TACHE_ID==2 || MOTIF_TACHE_ID==3)
    {
      if(NOM=="")
      {
        $('#error_NOM').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
        return false;
      }
      else
      {
        $('#error_NOM').text(''); 
      }

      if(PRENOM=="")
      {
        $('#error_PRENOM').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
        return false;
      }
      else
      {
        $('#error_PRENOM').text('');
      }

      if(POSTE=="")
      {
        $('#error_POSTE').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
        return false;
      }
      else
      {
        $('#error_POSTE').text('');
      }
    }
  }

  if(TRIMESTRE_ID_DESTINATION=="")
  {
    $('#error_TRIMESTRE_ID_DESTINATION').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }      

  if (INSTITUTION_ID=='') 
  {
    $('#error_INSTITUTION_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_INSTITUTION_ID').text('');
  }

  if (SOUS_TUTEL_ID=='') 
  {
    $('#error_SOUS_TUTEL_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_SOUS_TUTEL_ID').text('');
  }

  if (CODE_NOMENCLATURE_BUDGETAIRE_ID=='') 
  {
    $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').text('');
  }

  if (TYPE_INSTITUTION_ID == 2)
  {
    if (PTBA_ID=='') 
    {
      $('#error_PTBA_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
      return false;
    }
    else
    {
      $('#error_PTBA_ID').text('');
    }
  }

  if (PTBA_TACHE_ID=='') 
  {
    $('#error_PTBA_TACHE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_PTBA_TACHE_ID').text('');
  }

  if (TRIMESTRE_ID=='') 
  {
    $('#error_TRIMESTRE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_TRIMESTRE_ID').text('');
  }

  if (MONTANT_TRANSFERT=='') 
  {
    $('#error_MONTANT_TRANSFERT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MONTANT_TRANSFERT').text('');
  }

  if (MONTANT_VOTE=='') 
  {
    $('#error_MONTANT_VOTE').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MONTANT_VOTE').text('');
  }

  if (MONTANT_RESTANT=='') 
  {
    $('#error_MONTANT_RESTANT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MONTANT_RESTANT').text('');
  }

  if (MONTANT_APRES_TRANSFERT=='') 
  {
    $('#error_MONTANT_APRES_TRANSFERT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MONTANT_APRES_TRANSFERT').text('');
  }

  if (MONTANT_TRIMESTRE_SELECTION=='') 
  {
    $('#error_MONTANT_TRIMESTRE_SELECTION').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_MONTANT_TRIMESTRE_SELECTION').text('');
  }

  if(AUTORISATION_TRANSFERT=="")
  {
    $('#error_AUTORISATION_TRANSFERT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
    return false;
  }
  else
  {
    $('#error_AUTORISATION_TRANSFERT').text(''); 
  }

  var MONTANT_TRANSFERT_FORMAT = $('#MONTANT_TRANSFERT').val().replace(/\s/g, '');
  var MONTANT_TRIMESTRE_SELECTION_FORM = $('#MONTANT_TRIMESTRE_SELECTION').val().replace(/\s/g, '')


  if (parseFloat(MONTANT_TRANSFERT_FORMAT)>parseFloat(MONTANT_TRIMESTRE_SELECTION_FORM))
  {
    $('#error_MONTANT_TRANSFERT_SUP').text("<?= lang('messages_lang.error_money_sup') ?>");
    $('#MONTANT_APRES_TRANSFERT').val('')
    return false;
  }
  else
  {
    $('#error_MONTANT_TRANSFERT_SUP').text("");
  }

  var form = new FormData();

  var AUTORISATION_TRANSFERT=document.getElementById("AUTORISATION_TRANSFERT").files[0];
  form.append("AUTORISATION_TRANSFERT",AUTORISATION_TRANSFERT);
  form.append("MOTIF_TACHE_ID",MOTIF_TACHE_ID);
  form.append("NOM",NOM);
  form.append("PRENOM",PRENOM);
  form.append("POSTE",POSTE);
  form.append("INSTITUTION_ID",INSTITUTION_ID); 
  form.append("SOUS_TUTEL_ID",SOUS_TUTEL_ID);
  form.append("CODE_NOMENCLATURE_BUDGETAIRE_ID",CODE_NOMENCLATURE_BUDGETAIRE_ID);
  form.append("PTBA_ID",PTBA_ID);
  form.append("PTBA_TACHE_ID",PTBA_TACHE_ID);
  form.append("TRIMESTRE_ID",TRIMESTRE_ID);
  form.append("MONTANT_VOTE",MONTANT_VOTE); 
  form.append("MONTANT_RESTANT",MONTANT_RESTANT);
  form.append("MONTANT_TRANSFERT",MONTANT_TRANSFERT);
  form.append("MONTANT_APRES_TRANSFERT",MONTANT_APRES_TRANSFERT);
  form.append("MONTANT_TRIMESTRE_SELECTION",MONTANT_TRIMESTRE_SELECTION);
  form.append("TRIMESTRE_ID_DESTINATION",TRIMESTRE_ID_DESTINATION);

  if (statut == true) 
  {
    $.ajax(
    {
      url:"<?=base_url('/double_commande_new/Transfert_Meme_Activite/getInfoDetail')?>",
      type:"POST",
      dataType:"JSON",
      data: form,
      processData: false,  
      contentType: false,
      beforeSend:function() {
        $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      { 
        $('#detail_infos').modal('show'); // afficher bootstrap modal
        $('#infos_data').html(data.html)
        $('#loading_btn').html("");
      }
    });
  }
}

function send_data2(argument) 
{
  
  $('#MyFormData').submit();
}

function deleteFile()
{
  $.ajax(
  {
    url:"<?=base_url('/double_commande_new/Transfert_Meme_Activite/deleteFile')?>",
    type:"POST",
    dataType:"JSON",
    data: { },
    beforeSend:function() {
    },
    success: function(data)
    {
        
    }
  });
}
</script>
