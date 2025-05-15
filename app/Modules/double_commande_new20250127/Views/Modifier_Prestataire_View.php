<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
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
          <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
            <div class="col-12 d-flex">
              <div class="col-9" style="float: left;">
                <h1 class="header-title text-dark">Modifier Bénéficiaire</h1>
              </div>
            </div>
            <div style="margin-left: 15px" id="SUCCESS_MESSAGE" class="row">
            </div>
            <div class="card-body">
              <?php $validation = \Config\Services::validation(); ?>
              <form id="my_form" method="POST" action="<?= base_url('double_commande_new/Modifier_Prestataire/update') ?>" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <h4><center><i class="fa fa-info-circle"></i> <?=lang('messages_lang.labelle_information_base')?></center></h4>
                      </div>
                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.Bon_engagement')?><span style="color: red;">*</span></label>
                          <select class="form-control select2" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" onchange="checkSelect(this);get_info()">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($BEs as $BE)
                            {
                              ?>
                              <option value="<?=$BE->EXECUTION_BUDGETAIRE_ID?>"><?=$BE->NUMERO_BON_ENGAGEMENT?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('EXECUTION_BUDGETAIRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_EXECUTION_BUDGETAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.label_type_benef')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="TYPE_BENEFICIAIRE_ID" id="TYPE_BENEFICIAIRE_ID" onchange="get_prestataires(); checkSelect(this); ">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($Type_Beneficiaires as $Type_Beneficiaire)
                            {
                              ?>
                              <option value="<?=$Type_Beneficiaire->TYPE_BENEFICIAIRE_ID?>"><?=$Type_Beneficiaire->DESC_TYPE_BENEFICIAIRE?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('TYPE_BENEFICIAIRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_TYPE_BENEFICIAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.soumen_prestat')?><span style="color: red;">*</span></label><span hidden id="PRESTATAIRE_ID_LOADING"><i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i></span>
                          <select class="form-control select2" name="PRESTATAIRE_ID" id="PRESTATAIRE_ID" onchange="checkSelect(this)" >
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PRESTATAIRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_PRESTATAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-12">
                    <div id="message"></div>
                  </div>

                  <div class="col-12">
                    <button style="float: right;" id="btnSave" type="button" onclick="save_data()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.enregistre_action')?></button>
                  </div>
                </div>
              </form>
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

<div class="modal fade" id="modfi_prest" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_transmission_du_bordereau') ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="table-responsive  mt-3">
              <table class="table m-b-0 m-t-20">
                <tbody>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.Bon_engagement') ?></strong></td>
                    <td id="EXECUTION_BUDGETAIRE_ID_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.label_type_benef') ?></strong></td>
                    <td id="TYPE_BENEFICIAIRE_ID_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.soumen_prestat') ?></strong></td>
                    <td id="PRESTATAIRE_ID_VERIFY" class="text-dark"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <div class="row">
              <button id="mod" type="button" class="btn btn-primary" style="margin-top:10px" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?></button>
              <a id="myElement" onclick="submit();hideButton()" style="float: right; margin-top:10px" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> <?= lang('messages_lang.confirmer_transmission_du_bordereau') ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>

<script>
  function get_prestataires()
  {
    var TYPE_BENEFICIAIRE_ID = $('#TYPE_BENEFICIAIRE_ID').val();

    if (TYPE_BENEFICIAIRE_ID == "") return

    $.ajax(
    {
      url: "<?=base_url()?>/double_commande_new/Modifier_Prestataire/get_prestataire/" + TYPE_BENEFICIAIRE_ID,
      type: "POST",
      dataType: "JSON",
      beforeSend:function() {
        $('#PRESTATAIRE_ID_LOADING').attr('hidden',false);
      },
      success: function(data)
      {
        $('#PRESTATAIRE_ID_LOADING').attr('hidden',true);
        $('#PRESTATAIRE_ID').html(data.PRESTATAIRE_ID);
      }
    });
  }
</script>

<script>
  function checkSelect(ref)
  {
    const value = ref.value;
    const name = ref.name;
    const errorFieldId = '#error_'+name;

    const errorField = $(errorFieldId);

    if(value == ""){
      errorField.html('<font style="color:red;size:2px;">Le champs est obligatoire</font>')
    }
    else{
      errorField.html('')
    }
  }
</script>

<script type="text/javascript">
function save_data()
{
  var  EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
  var  TYPE_BENEFICIAIRE_ID=$('#TYPE_BENEFICIAIRE_ID').val();
  var  PRESTATAIRE_ID=$('#PRESTATAIRE_ID').val();

  //start validation
  var  isFormValid = true;

  if(EXECUTION_BUDGETAIRE_ID == ""){
    $('#error_EXECUTION_BUDGETAIRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
    isFormValid =  false;
  }
  if(TYPE_BENEFICIAIRE_ID == ""){
    $('#error_TYPE_BENEFICIAIRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
    isFormValid =  false;
  }
  else{
    if(PRESTATAIRE_ID == ""){
      $('#error_PRESTATAIRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
  }

  if(isFormValid)
  {
    $('#EXECUTION_BUDGETAIRE_ID_VERIFY').text($('#EXECUTION_BUDGETAIRE_ID  option:selected').text())
    $('#TYPE_BENEFICIAIRE_ID_VERIFY').text($('#TYPE_BENEFICIAIRE_ID  option:selected').text())
    $('#PRESTATAIRE_ID_VERIFY').text($('#PRESTATAIRE_ID  option:selected').text())
    $('#modfi_prest').modal("show")
  }  
}

function submit()
{
  $('#my_form').submit()
}

function get_info()
{
  var EXECUTION_BUDGETAIRE_ID=$('#EXECUTION_BUDGETAIRE_ID').val();

  $.ajax(
  {
    url: "<?=base_url()?>/double_commande_new/Modifier_Prestataire/get_info/" + EXECUTION_BUDGETAIRE_ID,
    type: "GET",
    dataType: "JSON",
    beforeSend:function()
    {
      $('#PRESTATAIRE_ID_LOADING').attr("hidden",false);

    },
    success: function(data)
    {
      $('#TYPE_BENEFICIAIRE_ID').val(data.TYPE_BENEFICIAIRE_ID)
      $('#PRESTATAIRE_ID').html(data.PRESTATAIRE_ID)
      $('#PRESTATAIRE_ID_LOADING').attr("hidden",true);
    }
  });
}
</script>