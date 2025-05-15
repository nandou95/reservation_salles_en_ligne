<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <style type="text/css">
    .help-block {
      color: red;
    }

    .res_scol {

      height: 340px;
      overflow-y: visible;
    }
  </style>

  <style>
    .required-field {
      font-size: 12px;
      opacity: .5;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <!-- <h1 class="header-title text-white">
              CL CMR & COSTAB
            </h1> -->
          </div>
          <div class="row">
            <div class="col-md-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div style="display: flex;" class="card-header">
                  <div class="col-sm-6">
                    <h1 style="font-size: 20px" class="header-title text-black"><?=$categories['CL_CMR_COSTAB_CATEGORY']?></h1>
                  </div>

                  <div class="col-sm-6" style="float:right;">
                    <a href="<?= base_url("process/Demandes/")  ?>" class="btn btn-dark float-right"><i class="nav-icon fas fa-list"></i>
                    <?= lang('messages_lang.link_list')?></a>
                  </div>
                </div>
                <div class="card-body" style="overflow-x:auto;">
                  <form method="post" action="<?=base_url('process/Planification_demande_cl_cmr_costab/save_form_cl_cmr_costab/')?>" id="MyFormData" class="js-formSubmit" enctype="multipart/form-data">
                    <br>
                    <ul class="nav nav-tabs" role="tablist">
                      <li class="nav-item">
                        <a style="color: white;background: #523bbc" class="nav-link active" id="tab1" data-toggle="tab" href="#">CL & CMR</a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" id="tab2" data-toggle="tab" href="#"> <?=lang('messages_lang.title_costab')?> </a>
                      </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                      <!-- Bonheur Edite  -->
                      <div style="display: block;" id="cl_cmr" class="tab-pane active"><br>
                        <!-- ########################################################## -->
                        <input type="hidden" name="ID_CL_CMR_COSTAB_CATEGORIE" id="ID_CL_CMR_COSTAB_CATEGORIE" value="<?= $categories['ID_CL_CMR_COSTAB_CATEGORIE'] ?>">

                        <input type="hidden" name="ACTION_ID" id="ACTION_ID" value="<?= $getAction['ACTION_ID'] ?>">

                        <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?=$ID_DEMANDE?>">
                        <input type="hidden" name="ID_DEMANDE2" id="ID_DEMANDE2" value="<?=md5($ID_DEMANDE)?>">

                        <div class="row mb-3">
                          <div class="col-md-12">
                            <label class="form-label"><?= lang('messages_lang.labelle_institution')?> <span style="color: red">*</span></label>
                              <?php if(!empty($instit_id_select)){?>
                                <input type="hidden" name="instit_id" id="instit_id" value="<?=$instit_id_select?>">
                                <select class="form-control select2" name="INSTITUTION_ID" id="INSTITUTION_ID" disabled>
                                  <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                  <?php foreach ($institution as $keyinstitution) { ?>
                                    <?php if($keyinstitution->INSTITUTION_ID == $instit_id_select){ ?>
                                      <option value="<?=$keyinstitution->INSTITUTION_ID?>"  selected>
                                      <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                    <?php }else{?>
                                      <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                      <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                    <?php }?>
                                  <?php }?>
                                </select>

                              <?php }else{ ?>
                              <select class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                <?php foreach ($institution as $keyinstitution) { ?>
                                  <?php if($keyinstitution->INSTITUTION_ID == $instit_id_select){ ?>
                                    <option value="<?=$keyinstitution->INSTITUTION_ID?>"  selected readonly>
                                    <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                  <?php }else{?>
                                    <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                    <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                  <?php }?>
                                <?php }?>
                              </select>
                            <?php } ?>
                              <font color="red" id="erINSTITUTION_ID"></font>
                            </div>

                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_droit_pilier')?><font color="red">*</font></label>
                              <select onchange="getObjectif()" name="ID_PILIER" class="form-control select2" id="ID_PILIER">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                <?php foreach ($piliers as $pilier) : ?>
                                  <option value="<?= $pilier->ID_PILIER ?>">
                                    <?= $pilier->DESCR_PILIER ?>
                                  </option>
                                <?php endforeach ?>
                              </select>
                              <font color="red" id="erID_PILIER"></font>
                            </div>
                          
                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.th_objectif_programme')?><font color="red">*</font></label>
                              <select onchange="getIndicateur()" name="ID_OBJECT_STRATEGIQUE" class="form-control select2" id="ID_OBJECT_STRATEGIQUE">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                              </select>
                              <font color="red" id="erID_OBJECT_STRATEGIQUE"></font>
                            </div>

                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_indecateur')?><font color="red">*</font></label>
                              <select name="ID_PLANS_INDICATEUR" class="form-control select2" id="ID_PLANS_INDICATEUR">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                              </select>
                              <font color="red" id="erID_PLANS_INDICATEUR"></font>
                            </div>
                          
                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_precision')?></label>
                              <input type="text" name="PRECISIONS" class="form-control" id="PRECISIONS">
                              <font color="red" id="erPRECISIONS"></font>
                            </div>

                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_reference')?><font color="red">*</font></label>
                              <input type="number" name="REFERENCE" class="form-control" id="REFERENCE">
                              <font color="red" id="erREFERENCE"></font>
                            </div>
                         
                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_cible')?><font color="red">*</font></label>
                              <input type="number" name="CIBLE" class="form-control" id="CIBLE">
                              <font color="red" id="erCIBLE"></font>
                            </div>

                          <div class="col-md-12">
                            <button type="button" onclick="save_cl_cmr()" id="action_btn_cl_cmr" class="btn btn-primary btn-block mt-4" style="float: right;"><?= lang('messages_lang.bouton_enregistrer')?><span id="loading_cl_cmr"></span></button>
                          </div>
                        </div>

                        <input type="hidden" name="INPUT_DATA" id="INPUT_DATA">
                        <input type="hidden" name="ID_PLANS_DEMANDE_CL_CMR" id="ID_PLANS_DEMANDE_CL_CMR">

                        <div class="col-md-12" id="myTableData1">
                          <center><span id="loading_loading1"></span></center>
                        </div>

                       <div>
                          <button type="button" onclick="verify1()" id="btn_suivant" class="btn btn-primary" style="float: right;" disabled> <?= lang('messages_lang.boutton_suivante')?> </button>
                        </div>
                        <!-- ########################################################## -->
                      </div>

                      <div id="costab" class="tab-pane" style="display: none;">
                        <div class="row">

                          <div class="col-md-12">
                            <label class="form-label"><?= lang('messages_lang.labelle_institution')?> <span style="color: red">*</span></label>
                              <?php if(!empty($instit_id_select)){?>
                                <input type="hidden" name="INSTITUTION_ID2" id="INSTITUTION_ID2" value="<?=$instit_id_select?>">
                                <select class="form-control select2" disabled>
                                  <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                  <?php foreach ($institution as $keyinstitution) { ?>
                                    <?php if($keyinstitution->INSTITUTION_ID == $instit_id_select){ ?>
                                      <option value="<?=$keyinstitution->INSTITUTION_ID?>"  selected readonly>
                                      <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                    <?php }else{?>
                                      <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                      <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                    <?php }?>
                                  <?php }?>
                                </select>

                              <?php }else{ ?>
                              <select class="form-control select2" id="INSTITUTION_ID2" name="INSTITUTION_ID2">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                <?php foreach ($institution as $keyinstitution) { ?>
                                  <?php if($keyinstitution->INSTITUTION_ID == $instit_id_select){ ?>
                                    <option value="<?=$keyinstitution->INSTITUTION_ID?>"  selected>
                                    <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                  <?php }else{?>
                                    <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                    <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                  <?php }?>
                                <?php }?>
                              </select>
                            <?php } ?>
                              <font color="red" id="erINSTITUTION_ID2"></font>
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_droit_enjeux')?> <span style="color: red;">*</span></label>
                                <select class="form-control select2" name="ID_ENJEUX" id="ID_ENJEUX" onchange="getPilier();">
                                  <option value="" selected disabled><?= lang('messages_lang.label_selecte')?> </option>
                                  <?php foreach ($enjeux as $value) : ?>
                                    <option value="<?= $value->ID_ENJEUX ?>"><?= $value->DESCR_ENJEUX ?></option>
                                  <?php endforeach ?>
                                </select>
                                <font color="red" id="erID_ENJEUX"></font>
                              <!-- </div> -->
                            </div>
                            
                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_droit_pilier')?> <span style="color: red;">*</span></label>
                                <select onchange="getObjectif2()" class="form-control select2" name="ID_PILIER_TR" id="ID_PILIER_TR">
                                  <option value="" selected disabled><?= lang('messages_lang.label_selecte')?></option>
                                  <?php foreach ($piliers as $pilier) : ?>
                                    <option value="<?= $pilier->ID_PILIER ?>">
                                      <?= $pilier->DESCR_PILIER ?>
                                    </option>
                                  <?php endforeach ?>
                                </select>
                                <font color="red" id="erID_PILIER_TR"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_axe_intervention')?> <span style="color: red;">*</span></label>
                                <select class="form-control select2" name="ID_AXE_INTERVENTION_PND" id="ID_AXE_INTERVENTION_PND">
                                  <option value="" selected disabled><?= lang('messages_lang.label_selecte')?></option>
                                  <?php foreach ($axe_intervation as $axe_intervation) : ?>
                                    <option value="<?= $axe_intervation->ID_AXE_INTERVENTION_PND ?>">
                                      <?= $axe_intervation->DESCR_AXE_INTERVATION_PND ?>
                                    </option>
                                  <?php endforeach ?>
                                </select>
                                <font color="red" id="erID_AXE_INTERVENTION_PND"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.th_objectif_programme')?>  <span style="color: red;">*</span></label>
                                <select class="form-control select2" name="ID_OBJECTIF" id="ID_OBJECTIF">
                                  <option value="" selected disabled><?= lang('messages_lang.label_selecte')?></option>
                                </select>
                                <font color="red" id="erID_OBJECTIF"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.table_Programme')?> <span style="color: red;">*</span></label>
                                <select class="form-control select2" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="getPlanification()">
                                  <option value="" selected disabled><?= lang('messages_lang.label_selecte')?></option>
                                  <?php foreach ($programme as $value) : ?>
                                    <option value="<?= $value->ID_PROGRAMME_PND ?>"><?= $value->DESCR_PROGRAMME ?></option>
                                  <?php endforeach ?>
                                </select>
                                <font color="red" id="erPROGRAMME_ID"></font>
                              <!-- </div> -->
                            </div>
                          
                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_planification_projet')?> <span style="color: red;">*</span></label>
                                <input type="text" name="ID_PLANS_PROJET" class="form-control" id="ID_PLANS_PROJET">
                                <font color="red" id="erID_PLANS_PROJET"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_annee')?> 1 <span style="color: red;">*</span></label>
                                <input type="number" onkeyup="calculNumber()" name="BUDGET_ANNEE_1" class="form-control" id="BUDGET_ANNEE_1">
                                <font color="red" id="erBUDGET_ANNEE_1"></font>
                              <!-- </div> -->
                            </div>
                         
                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_annee')?> 2 <span style="color: red;">*</span></label>
                                <input type="number"  onkeyup="calculNumber()" name="BUDGET_ANNEE_2" class="form-control" id="BUDGET_ANNEE_2">
                                <font color="red" id="erBUDGET_ANNEE_2"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_annee')?> 3 <span style="color: red;">*</span></label>
                                <input type="number"  onkeyup="calculNumber()" name="BUDGET_ANNEE_3" class="form-control" id="BUDGET_ANNEE_3">
                                <font color="red" id="erBUDGET_ANNEE_3"></font>
                              <!-- </div> -->
                            </div>
                            
                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_annee')?> 4 <span style="color: red;">*</span></label>
                                <input type="number"  onkeyup="calculNumber()" name="BUDGET_ANNEE_4" class="form-control" id="BUDGET_ANNEE_4">
                                <font color="red" id="erBUDGET_ANNEE_4"></font>
                              <!-- </div> -->
                            </div>
                          
                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_annee')?> 5 <span style="color: red;">*</span></label>
                                <input type="number"  onkeyup="calculNumber()" name="BUDGET_ANNEE_5" class="form-control" id="BUDGET_ANNEE_5">
                                <font color="red" id="erBUDGET_ANNEE_5"></font>
                              <!-- </div> -->
                            </div>

                            <div class="col-md-4">
                              <!-- <div class="form-group"> -->
                                <label><?= lang('messages_lang.label_budget_total')?> <span style="color: red;">*</span></label>
                                <input readonly type="text" name="BUDGET_TOTAL" class="form-control" id="BUDGET_TOTAL">
                              </div>
                            <!-- </div> -->

                          <div class="col-md-12">
                            <button type="button" onclick="save_costab()" id="action_btn_costab" class="btn btn-primary btn-block mt-4" style="float: right;"><?= lang('messages_lang.bouton_enregistrer')?><span id="loading_costab"></span></button>
                          </div>


                        <input type="hidden" name="INPUT_DATA2" id="INPUT_DATA2">
                        <input type="hidden" name="ID_PLANS_DEMANDE_COSTAB" id="ID_PLANS_DEMANDE_COSTAB">

                        <div class="col-md-12" id="myTableData2">
                          <center><span id="loading_loading2"></span></center>
                        </div>

                          <br>
                          <div class="col-md-6">
                            <button type="button" style="float: left;" onclick="preced()" class="prev_button btn btn-primary"> <?= lang('messages_lang.boutton_precedent')?> </button>
                          </div>

                          <div class="col-6">
                            <button type="button" id="btn_send" onclick="verify2()" class="btn btn-primary" style="float: right;" disabled> <?= lang('messages_lang.bouton_enregistrer')?> </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- ================================================================================ -->
          </div>
        </div>
      </main>
    </div>
  </div>
</body>

</html>
<?php echo view('includesbackend/scripts_js.php'); ?>

<script type="text/javascript">
  $("#REFERENCE").on('input', function()
  {
    var maxLength;
    var minLength;
    if (this.id === "REFERENCE")
    {
      maxLength = 100;
      minLength = 0;
    }
    $(this).val(this.value.substring(0, maxLength));
  });

</script>

<script type="text/javascript">
  $("#CIBLE").on('input', function()
  {
    var maxLength;
    var minLength;
    if (this.id === "CIBLE")
    {
      maxLength = 100;
      minLength = 0;
    }
    $(this).val(this.value.substring(0, maxLength));
  });

</script>



<script type="text/javascript">

  $("#BUDGET_ANNEE_1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });
  $("#BUDGET_ANNEE_2").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });
  $("#BUDGET_ANNEE_3").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });
  $("#BUDGET_ANNEE_4").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });
  $("#BUDGET_ANNEE_5").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  function calculNumber()
  {
    var BUDGET_ANNEE_1 = $('#BUDGET_ANNEE_1').val()
    var BUDGET_ANNEE_2 = $('#BUDGET_ANNEE_2').val()
    var BUDGET_ANNEE_3 = $('#BUDGET_ANNEE_3').val()
    var BUDGET_ANNEE_4 = $('#BUDGET_ANNEE_4').val()
    var BUDGET_ANNEE_5 = $('#BUDGET_ANNEE_5').val()

    if (BUDGET_ANNEE_1=='') {
      var BUDGET_ANNEE_1 = 0;
    }

    if (BUDGET_ANNEE_2=='') {
      var BUDGET_ANNEE_2 = 0;
    }

    if (BUDGET_ANNEE_3=='') {
      var BUDGET_ANNEE_3 = 0;
    }

    if (BUDGET_ANNEE_4=='') {
      var BUDGET_ANNEE_4 = 0;
    }

    if (BUDGET_ANNEE_5=='') {
      var BUDGET_ANNEE_5 = 0;
    }

    var resultat = parseInt(BUDGET_ANNEE_1)+parseInt(BUDGET_ANNEE_2)+parseInt(BUDGET_ANNEE_3)+parseInt(BUDGET_ANNEE_4)+parseInt(BUDGET_ANNEE_5)

    $('#BUDGET_TOTAL').val(resultat)
  }
</script>


<script type="text/javascript">

  function getObjectif() {

    let ID_PILIER = $('#ID_PILIER').val()
    if (ID_PILIER != '') {
      $.ajax({
        url: `/process/Planification_demande_cl_cmr_costab/getObjectif/${ID_PILIER}`,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
          $('#ID_OBJECT_STRATEGIQUE').html(data.objectif)
        }
      })
    }
  }

  function getIndicateur() {

    let ID_OBJECT_STRATEGIQUE = $('#ID_OBJECT_STRATEGIQUE').val()
    if (ID_OBJECT_STRATEGIQUE != '') {
      $.ajax({
        url: `/process/Planification_demande_cl_cmr_costab/getIndicateur/${ID_OBJECT_STRATEGIQUE}`,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
          $('#ID_PLANS_INDICATEUR').html(data.indicateur)
        }
      })
    }
  }

  function getObjectif2() {

    let ID_PILIER_TR = $('#ID_PILIER_TR').val();
    if (ID_PILIER_TR != '') {
      $.ajax({
        url: `/process/Planification_demande_cl_cmr_costab/getObjectif/${ID_PILIER_TR}`,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
          $('#ID_OBJECTIF').html(data.objectif)
        }
      })
    }
  }
</script>


<script type="text/javascript">

function verify1()
{
   var statut = true;

   if ($("#INPUT_DATA").val()<1) {

      if ($("#INSTITUTION_ID").val()=='') 
      {
        $('#erINSTITUTION_ID').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erINSTITUTION_ID').text('');
      }

      if ($("#ID_PILIER").val()=='') 
      {
        $('#erID_PILIER').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_PILIER').text('');
      }

      if ($("#ID_OBJECT_STRATEGIQUE").val()=='') 
      {
        $('#erID_OBJECT_STRATEGIQUE').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_OBJECT_STRATEGIQUE').text('');
      }

      if($("#ID_PLANS_INDICATEUR").val()=='')
      {
        $('#erID_PLANS_INDICATEUR').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_PLANS_INDICATEUR').text('');
      }


      if ($("#REFERENCE").val()=='') 
      {
        $('#erREFERENCE').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
          $('#erREFERENCE').text('');
      }

      if ($("#CIBLE").val()=='') 
      {
        $('#erCIBLE').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erCIBLE').text('');
      }
   }

   if (statut==true)
   { 
      $('#tab1').removeClass('active');
      $('#tab2').addClass('active');
     document.getElementById('cl_cmr').style.display="none";
     document.getElementById('costab').style.display="block";

     document.getElementById('tab1').style.background="";
      document.getElementById('tab1').style.color="";

      document.getElementById('tab2').style.background="#523bbc";
      document.getElementById('tab2').style.color="white";
   }

}

function preced() {
    $('#tab2').removeClass('active');
    $('#tab1').addClass('active');
    document.getElementById('cl_cmr').style.display="block";
    document.getElementById('costab').style.display="none";

    document.getElementById('tab2').style.background="";
    document.getElementById('tab2').style.color="";

    document.getElementById('tab1').style.background="#523bbc";
    document.getElementById('tab1').style.color="white";
}

function verify2() {

    var statut = true;

    if ($("#INPUT_DATA2").val()<1) {

      if ($("#INSTITUTION_ID2").val()=='') 
      {
        $('#erINSTITUTION_ID2').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erINSTITUTION_ID2').text('');
      }

      if ($("#ID_ENJEUX").val()=='') 
      {
        $('#erID_ENJEUX').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_ENJEUX').text('');
      }

      if ($("#ID_PILIER_TR").val()=='') 
      {
        $('#erID_PILIER_TR').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_PILIER_TR').text('');
      }

      if ($("#ID_AXE_INTERVENTION_PND").val()=='') 
      {
        $('#erID_AXE_INTERVENTION_PND').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_AXE_INTERVENTION_PND').text('');
      }

      if ($("#ID_OBJECTIF").val()=='') 
      {
        $('#erID_OBJECTIF').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_OBJECTIF').text('');
      }

      if($("#PROGRAMME_ID").val()=='')
      {
        $('#erPROGRAMME_ID').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erPROGRAMME_ID').text('');
      }

      if($("#ID_PLANS_PROJET").val()=='')
      {
        $('#erID_PLANS_PROJET').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erID_PLANS_PROJET').text('');
      }

     if ($("#BUDGET_ANNEE_1").val()=='') 
      {
        $('#erBUDGET_ANNEE_1').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erBUDGET_ANNEE_1').text('');
      }

      if ($("#BUDGET_ANNEE_2").val()=='') 
      {
        $('#erBUDGET_ANNEE_2').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
          $('#erBUDGET_ANNEE_2').text('');
      }

      if ($("#BUDGET_ANNEE_3").val()=='') 
      {
        $('#erBUDGET_ANNEE_3').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erBUDGET_ANNEE_3').text('');
      }

      if ($("#BUDGET_ANNEE_4").val()=='') 
      {
        $('#erBUDGET_ANNEE_4').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erBUDGET_ANNEE_4').text('');
      }

      if ($("#BUDGET_ANNEE_5").val()=='') 
      {
        $('#erBUDGET_ANNEE_5').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }else{
        $('#erBUDGET_ANNEE_5').text('');
      }
    }

    if (statut == true) {
      $('#MyFormData').submit();
    }

}
</script>

<script type="text/javascript">
  $(document).ready(function(){
      liste_cl_cmr()
      liste_costab() 

      var getForm = '<?=$getForm?>';
      if (getForm==1)
      {
        $('#tab1').removeClass('active');
        $('#tab2').addClass('active');
        document.getElementById('cl_cmr').style.display="none";
        document.getElementById('costab').style.display="block";

        document.getElementById('tab1').style.background="";
        document.getElementById('tab1').style.color="";

        document.getElementById('tab2').style.background="#523bbc";
        document.getElementById('tab2').style.color="white";
      }
  });
</script>

 <script>

  function save_cl_cmr()
  {
    var statut = true;

    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var ID_PILIER=$('#ID_PILIER').val();
    var ID_OBJECT_STRATEGIQUE=$('#ID_OBJECT_STRATEGIQUE').val();
    var ID_PLANS_INDICATEUR=$('#ID_PLANS_INDICATEUR').val();
    var PRECISIONS=$('#PRECISIONS').val();
    var REFERENCE=$('#REFERENCE').val();
    var CIBLE=$('#CIBLE').val();
    var ID_PLANS_DEMANDE_CL_CMR=$('#ID_PLANS_DEMANDE_CL_CMR').val();
    var ID_CL_CMR_COSTAB_CATEGORIE=$('#ID_CL_CMR_COSTAB_CATEGORIE').val();
    var ID_DEMANDE=$('#ID_DEMANDE').val();

    if ($('#action_btn_cl_cmr').text() == 'Enregistrer' || $('#action_btn_cl_cmr').text() == 'Save')
    {
      var SOURCE = 1;// Add
    } else {
      var SOURCE = 2;// update
    }

    url = '<?=base_url('process/Planification_demande_cl_cmr_costab/save_cl_cmr')?>';

    if ($("#INSTITUTION_ID").val()=='') 
    {
      $('#erINSTITUTION_ID').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erINSTITUTION_ID').text('');
    }

    if ($("#ID_PILIER").val()=='') 
    {
      $('#erID_PILIER').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_PILIER').text('');
    }

    if ($("#ID_OBJECT_STRATEGIQUE").val()=='') 
    {
      $('#erID_OBJECT_STRATEGIQUE').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_OBJECT_STRATEGIQUE').text('');
    }

    if($("#ID_PLANS_INDICATEUR").val()=='')
    {
      $('#erID_PLANS_INDICATEUR').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_PLANS_INDICATEUR').text('');
    }

    if ($("#REFERENCE").val()=='') 
    {
      $('#erREFERENCE').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
        $('#erREFERENCE').text('');
    }

    if ($("#CIBLE").val()=='') 
    {
      $('#erCIBLE').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erCIBLE').text('');
    }


    if (statut == true)
    {
      $.ajax(
      { 
        url:url,
        type:"POST",
        dataType:"JSON",
        cache:false,
        data:{
          INSTITUTION_ID:INSTITUTION_ID,
          ID_PILIER:ID_PILIER,
          ID_OBJECT_STRATEGIQUE:ID_OBJECT_STRATEGIQUE,
          ID_PLANS_INDICATEUR:ID_PLANS_INDICATEUR,
          PRECISIONS:PRECISIONS,
          REFERENCE:REFERENCE,
          CIBLE:CIBLE,
          ID_PLANS_DEMANDE_CL_CMR:ID_PLANS_DEMANDE_CL_CMR,
          SOURCE:SOURCE,
          ID_CL_CMR_COSTAB_CATEGORIE:ID_CL_CMR_COSTAB_CATEGORIE,
          ID_DEMANDE:ID_DEMANDE
        },
        beforeSend:function() {
          $('#loading_cl_cmr').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#action_btn_cl_cmr').attr('disabled',true);
      },
        success:function(data)
        {
          if (data.status)
          {
            setTimeout(()=>{

              if (SOURCE==1)
              {
                $('#action_btn_cl_cmr').html('<b><?= lang('messages_lang.enregistrement_reussi')?> <i class="fa fa-check"></i></b>');
              }
              else
              {
                $('#action_btn_cl_cmr').html('<b><?= lang('messages_lang.modification_reussi')?> <i class="fa fa-check"></i></b>');
              }
              
              $('#loading_cl_cmr').html("");
             $('#action_btn_cl_cmr').attr('disabled',false);

            },3000);

           setTimeout(()=>{
              window.location.reload();
            },5000);
          }
        }
    });
  }
}

function supprimer_cl_cmr(id)
{
    $.ajax({
      url : "<?=base_url('process/Planification_demande_cl_cmr_costab/supprimer_cl_cmr')?>",
      type: "POST",
      dataType: "JSON",
      data:{
        id:id
      },
      success: function(data)
      {
        if (data.status) {
          liste_cl_cmr()
          window.location.reload();
        }
      }
  });
}

function editercl_cmr(id)
{

    $('#action_btn_cl_cmr').html('Modifier');

    $.ajax({
        url : "<?=base_url('process/Planification_demande_cl_cmr_costab/editercl_cmr')?>",
        type: "POST",
        dataType: "JSON",
        data:{
          id:id
        },
        success: function(data)
        {
          if (data.status) {
            $('[name="ID_PLANS_DEMANDE_CL_CMR"]').val(data.cl_cmr_data.ID_PLANS_DEMANDE_CL_CMR );
            $('[name="PRECISIONS"]').val(data.cl_cmr_data.PRECISIONS);
            $('[name="REFERENCE"]').val(data.cl_cmr_data.REFERENCE);
            $('[name="CIBLE"]').val(data.cl_cmr_data.CIBLE);

            $('#INSTITUTION_ID').prop('disabled',false);
            //$('#INSTITUTION_ID').html(data.html_institution);
            $('#ID_PLANS_INDICATEUR').html(data.html_indicateur);
            $('#ID_PILIER').html(data.html_pilier);
            $('#ID_OBJECT_STRATEGIQUE').html(data.html_objectif);
          }
        }
    });
}

function liste_cl_cmr()
{
    var ID_DEMANDE=$('#ID_DEMANDE').val();
    var ID_CL_CMR_COSTAB_CATEGORIE=$('#ID_CL_CMR_COSTAB_CATEGORIE').val();
  
      $.ajax(
      { 
        url:"<?=base_url('process/Planification_demande_cl_cmr_costab/liste_cl_cmr')?>",
        type:"POST",
        dataType:"JSON",
        cache:false,
        data:{
          ID_DEMANDE:ID_DEMANDE,
          ID_CL_CMR_COSTAB_CATEGORIE:ID_CL_CMR_COSTAB_CATEGORIE
        },
        beforeSend:function() {
          $('#loading_loading1').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success:function(data)
        {
          $('#loading_loading1').html("");
          if (data.count_data>0)
          {
            $('#btn_suivant').attr('disabled',false);
            $('#INPUT_DATA').val(data.count_data);
            $('#myTableData1').html(data.tabledata);
            $('#loading_loading1').html("");
          }
        }
    });
}
 </script>


 <script>

  function save_costab()
  {
    var statut = true;

    var INSTITUTION_ID2=$('#INSTITUTION_ID2').val();
    var ID_ENJEUX=$('#ID_ENJEUX').val();
    var ID_PILIER=$('#ID_PILIER_TR').val();
    var ID_AXE_INTERVENTION_PND=$('#ID_AXE_INTERVENTION_PND').val();
    var ID_OBJECTIF=$('#ID_OBJECTIF').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ID_PLANS_PROJET=$('#ID_PLANS_PROJET').val();
    var BUDGET_ANNEE_1=$('#BUDGET_ANNEE_1').val();
    var BUDGET_ANNEE_2=$('#BUDGET_ANNEE_2').val();
    var BUDGET_ANNEE_3=$('#BUDGET_ANNEE_3').val();
    var BUDGET_ANNEE_4=$('#BUDGET_ANNEE_4').val();
    var BUDGET_ANNEE_5=$('#BUDGET_ANNEE_5').val();
    var BUDGET_TOTAL=$('#BUDGET_TOTAL').val();

    var ID_PLANS_DEMANDE_COSTAB=$('#ID_PLANS_DEMANDE_COSTAB').val();
    var ID_CL_CMR_COSTAB_CATEGORIE=$('#ID_CL_CMR_COSTAB_CATEGORIE').val();

    var ID_DEMANDE=$('#ID_DEMANDE').val();
    var ACTION_ID=$('#ACTION_ID').val();

    var getForm = 1;

    if ($('#action_btn_costab').text() == 'Enregistrer' || $('#action_btn_costab').text() == 'Save')
    {
      var SOURCE = 1;// Add
    } else {
      var SOURCE = 2;// update
    }

    url = '<?=base_url('process/Planification_demande_cl_cmr_costab/save_costab')?>';

    if ($("#INSTITUTION_ID2").val()=='') 
    {
      $('#erINSTITUTION_ID2').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erINSTITUTION_ID2').text('');
    }

    if ($("#ID_ENJEUX").val()=='') 
    {
      $('#erID_ENJEUX').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_ENJEUX').text('');
    }

    if ($("#ID_PILIER_TR").val()=='') 
    {
      $('#erID_PILIER_TR').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_PILIER_TR').text('');
    }

    if ($("#ID_AXE_INTERVENTION_PND").val()=='') 
    {
      $('#erID_AXE_INTERVENTION_PND').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_AXE_INTERVENTION_PND').text('');
    }

    if ($("#ID_OBJECTIF").val()=='') 
    {
      $('#erID_OBJECTIF').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_OBJECTIF').text('');
    }

    if($("#PROGRAMME_ID").val()=='')
    {
      $('#erPROGRAMME_ID').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erPROGRAMME_ID').text('');
    }

    if($("#ID_PLANS_PROJET").val()=='')
    {
      $('#erID_PLANS_PROJET').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erID_PLANS_PROJET').text('');
    }

   if ($("#BUDGET_ANNEE_1").val()=='') 
    {
      $('#erBUDGET_ANNEE_1').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erBUDGET_ANNEE_1').text('');
    }

    if ($("#BUDGET_ANNEE_2").val()=='') 
    {
      $('#erBUDGET_ANNEE_2').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
        $('#erBUDGET_ANNEE_2').text('');
    }

    if ($("#BUDGET_ANNEE_3").val()=='') 
    {
      $('#erBUDGET_ANNEE_3').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erBUDGET_ANNEE_3').text('');
    }

    if ($("#BUDGET_ANNEE_4").val()=='') 
    {
      $('#erBUDGET_ANNEE_4').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erBUDGET_ANNEE_4').text('');
    }

    if ($("#BUDGET_ANNEE_5").val()=='') 
    {
      $('#erBUDGET_ANNEE_5').text('<?= lang('messages_lang.validation_message')?>');
      return false;
    }else{
      $('#erBUDGET_ANNEE_5').text('');
    }


    if (statut == true)
    {
      $.ajax(
      { 
        url:url,
        type:"POST",
        dataType:"JSON",
        cache:false,
        data:{
          INSTITUTION_ID2:INSTITUTION_ID2,
          ID_ENJEUX:ID_ENJEUX,
          ID_PILIER:ID_PILIER,
          ID_AXE_INTERVENTION_PND:ID_AXE_INTERVENTION_PND,
          ID_OBJECTIF:ID_OBJECTIF,
          PROGRAMME_ID:PROGRAMME_ID,
          ID_PLANS_PROJET:ID_PLANS_PROJET,
          BUDGET_ANNEE_1:BUDGET_ANNEE_1,
          BUDGET_ANNEE_2:BUDGET_ANNEE_2,
          BUDGET_ANNEE_3:BUDGET_ANNEE_3,
          BUDGET_ANNEE_4:BUDGET_ANNEE_4,
          BUDGET_ANNEE_5:BUDGET_ANNEE_5,
          BUDGET_TOTAL:BUDGET_TOTAL,
          ID_PLANS_DEMANDE_COSTAB:ID_PLANS_DEMANDE_COSTAB,
          SOURCE:SOURCE,
          ID_CL_CMR_COSTAB_CATEGORIE:ID_CL_CMR_COSTAB_CATEGORIE,
          ID_DEMANDE:ID_DEMANDE
        },
        beforeSend:function() {
          $('#loading_costab').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#action_btn_costab').attr('disabled',true);
        },
        success:function(data)
        {
          var ID_DEMANDE2=$('#ID_DEMANDE2').val();

          if (data.status)
          {
            setTimeout(()=>{

              if (SOURCE==1)
              {
                $('#action_btn_costab').html('<b><?= lang('messages_lang.enregistrement_reussi')?><i class="fa fa-check"></i></b>');
              }
              else
              {
                $('#action_btn_costab').html('<b><?= lang('messages_lang.modification_reussi')?> <i class="fa fa-check"></i></b>');
              }

              $('#loading_costab').html("");
              $('#action_btn_costab').attr('disabled',false);

            },3000);

           setTimeout(()=>{
              liste_costab()
              window.location.href="<?= base_url('process/Planification_demande_cl_cmr_costab/index')?>/"+ACTION_ID+'/'+ID_DEMANDE2+'/'+getForm;
            },5000);
          }
        }
    });
  }
}

function supprimer_costab(id)
{
    $.ajax({
      url : "<?=base_url('process/Planification_demande_cl_cmr_costab/supprimer_costab')?>",
      type: "POST",
      dataType: "JSON",
      data:{
        id:id
      },
      success: function(data)
      {
        if (data.status) {
          liste_costab()
          window.location.reload();
        }
      }
  });
}

function editercostab(id)
{
    $('#action_btn_costab').html('Modifier');

    $.ajax({
        url : "<?=base_url('process/Planification_demande_cl_cmr_costab/editercostab')?>",
        type: "POST",
        dataType: "JSON",
        data:{
          id:id
        },
        success: function(data)
        {
          if (data.status) {
            $('[name="ID_PLANS_DEMANDE_COSTAB"]').val(data.costab_data.ID_PLANS_DEMANDE_COSTAB);
            $('[name="BUDGET_ANNEE_1"]').val(data.costab_data.BUDGET_ANNE_1);
            $('[name="BUDGET_ANNEE_2"]').val(data.costab_data.BUDGET_ANNE_2);
            $('[name="BUDGET_ANNEE_3"]').val(data.costab_data.BUDGET_ANNE_3);
            $('[name="BUDGET_ANNEE_4"]').val(data.costab_data.BUDGET_ANNE_4);
            $('[name="BUDGET_ANNEE_5"]').val(data.costab_data.BUDGET_ANNE_5);
            $('[name="BUDGET_TOTAL"]').val(data.costab_data.BUDGET_TOTAL);
            $('[name="ID_PLANS_PROJET"]').val(data.costab_data.ID_PLANS_PROJET);

            $('#INSTITUTION_ID2').html(data.html_institution);
            $('#ID_ENJEUX').html(data.html_enjeux);
            $('#ID_PILIER_TR').html(data.html_pilier);
            $('#ID_AXE_INTERVENTION_PND').html(data.html_axe);
            $('#ID_OBJECTIF').html(data.html_objectif);
            $('#PROGRAMME_ID').html(data.html_programme);
          }
        }
    });
}

function liste_costab()
{
    var ID_DEMANDE=$('#ID_DEMANDE').val();
    var ID_CL_CMR_COSTAB_CATEGORIE=$('#ID_CL_CMR_COSTAB_CATEGORIE').val();

    $.ajax(
    { 
      url:"<?=base_url('process/Planification_demande_cl_cmr_costab/liste_costab')?>/"+ID_CL_CMR_COSTAB_CATEGORIE,
      type:"POST",
      dataType:"JSON",
      cache:false,
      data:{
        ID_DEMANDE:ID_DEMANDE,
        ID_CL_CMR_COSTAB_CATEGORIE:ID_CL_CMR_COSTAB_CATEGORIE
      },
      beforeSend:function() {
        $('#loading_loading2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {
        $('#loading_loading2').html("");
        if (data.count_data>0)
        {
          $('#btn_send').attr('disabled',false);
          $('#INPUT_DATA2').val(data.count_data);
          $('#myTableData2').html(data.tabledata);
          $('#loading_loading2').html("");
        }
      }
  });
}
 </script>
