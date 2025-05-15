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
                <h1 class="header-title text-dark">Modifier Tâche/PTBA Revisé</h1>
              </div>
            </div>
            <div style="margin-left: 15px" id="SUCCESS_MESSAGE" class="row">
            </div>
            <div class="card-body">
              <?php $validation = \Config\Services::validation(); ?>
              <form id="my_form" method="POST" action="<?= base_url('double_commande_new/Modifier_Tache/update_revise') ?>" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-6">
                        <div class="">
                          <label>Code nomenclature budgétaire<span style="color: red;">*</span></label>
                          <select class="form-control select2" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" onchange="checkSelect(this);get_taches()" oninput="get_taches()">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($code_num_budgs as $code_num_budg)
                            {
                              ?>
                              <option value="<?=$code_num_budg->CODE_NOMENCLATURE_BUDGETAIRE_ID?>"<?= $code_num_budg->CODE_NOMENCLATURE_BUDGETAIRE_ID == $CODE_NOMENCLATURE_BUDGETAIRE_ID ? 'selected' : '' ?>><?=$code_num_budg->CODE_NOMENCLATURE_BUDGETAIRE?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="">
                          <label>Tâches PTBA Revisé<span style="color: red;">*</span></label><span hidden id="PTBA_TACHE_REVISE_ID_LOADING"><i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i></span>
                          <select class="form-control select2" name="PTBA_TACHE_REVISE_ID" id="PTBA_TACHE_REVISE_ID" onchange="checkSelect(this); setTacheAModifier(this)" >
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($code_taches as $code_tache)
                            {
                              ?>
                              <option value="<?=$code_tache->PTBA_TACHE_REVISE_ID?>"<?= $code_tache->PTBA_TACHE_REVISE_ID == $PTBA_TACHE_REVISE_ID ? 'selected' : '' ?>><?=$code_tache->DESC_TACHE?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PTBA_TACHE_REVISE_ID'); ?>
                          <?php endif ?>
                          <span id="error_PTBA_TACHE_REVISE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-6 mt-3">
                        <div class="">
                          <label>Modifier Tâche<span style="color: red;">*</span></label>
                          <textarea class="form-control" name="MODIFIER_TACHE" id="MODIFIER_TACHE" onchange="checkSelect(this)" ><?=$DESC_TACHE?></textarea>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('MODIFIER_TACHE'); ?>
                          <?php endif ?>
                          <span id="error_MODIFIER_TACHE" class="text-danger"></span>
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

<div class="modal fade" id="confirm_modification" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Veuillez confirmer les informations</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td style="width:350px;"><strong>Code nomenclature budgétaire</strong></td>
                <td id="CODE_NOMENCLATURE_BUDGETAIRE_ID_VERIFY" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px;"><strong>Tâches PTBA</strong></td>
                <td id="PTBA_TACHE_REVISE_ID_VERIFY" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px;"><strong>Tâche modifiée</strong></td>
                <td id="MODIFIER_TACHE_VERIFY" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <div class="row" id="buttons">
          <button id="mod" type="button" class="btn btn-primary" style="margin-top:10px" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?></button>
          <a id="myElement" onclick="submit();" style="float: right; margin-top:10px" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> <?= lang('messages_lang.confirmer_transmission_du_bordereau') ?></a>
        </div>
      </div>
    </div>
  </div>
</div>

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
  var  CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
  var  PTBA_TACHE_REVISE_ID=$('#PTBA_TACHE_REVISE_ID').val();
  var  MODIFIER_TACHE=$('#MODIFIER_TACHE').val();

  //start validation
  var  isFormValid = true;

  if(CODE_NOMENCLATURE_BUDGETAIRE_ID == ""){
    $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
    isFormValid =  false;
  }
  if(PTBA_TACHE_REVISE_ID == ""){
    $('#error_PTBA_TACHE_REVISE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
    isFormValid =  false;
  }
  else{
    if(MODIFIER_TACHE == ""){
      $('#error_MODIFIER_TACHE').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
  }

  if(isFormValid)
  {
    $('#CODE_NOMENCLATURE_BUDGETAIRE_ID_VERIFY').text($('#CODE_NOMENCLATURE_BUDGETAIRE_ID  option:selected').text())
    $('#PTBA_TACHE_REVISE_ID_VERIFY').text($('#PTBA_TACHE_REVISE_ID  option:selected').text())
    $('#MODIFIER_TACHE_VERIFY').text($('#MODIFIER_TACHE').val())
    $('#confirm_modification').modal("show")
  }
}

function submit()
{
  $('#buttons').attr('hidden', true)
  $('#my_form').submit()
}

function get_taches()
{
  var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();

  $.ajax(
  {
    url: "<?=base_url()?>/double_commande_new/Modifier_Tache/get_taches/" + CODE_NOMENCLATURE_BUDGETAIRE_ID,
    type: "GET",
    dataType: "JSON",
    beforeSend:function()
    {
      $('#PTBA_TACHE_REVISE_ID_LOADING').attr("hidden",false);
      $('#MODIFIER_TACHE').val('');

    },
    success: function(data)
    {
      console.log(data.PTBA_TACHE_REVISE_ID)
      $('#PTBA_TACHE_REVISE_ID').html(data.PTBA_TACHE_REVISE_ID)
      $('#PTBA_TACHE_REVISE_ID_LOADING').attr("hidden",true);
    }
  });
}

function setTacheAModifier(item) 
{
  const selectedOption = item.options[item.selectedIndex];
  const tacheText = selectedOption.text;

  $('#MODIFIER_TACHE').val(tacheText);
}
</script>