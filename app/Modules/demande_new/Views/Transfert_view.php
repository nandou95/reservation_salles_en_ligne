<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>

</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <?php $validation = \Config\Services::validation(); ?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title text-white">
              
            </h1>
          </div>
          <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
            <div class="card-header">
              <h1 class="header-title text-black"><?=lang('messages_lang.transf_montant')?></h1>
            </div>
            <div class="card-body">
              <form method="post" id="MyFormData" class="form-horizontal" action="<?=base_url('demande_new/Transfert/transferer')?>">
                <div class="row">
                  <input type="hidden" name="MONTANT_h" id="MONTANT_h">
                  
                </div>
                <div class="row">
                  <div class="col-md-12"><h5><?=lang('messages_lang.label_de')?></h5></div>
                  <div class="col-md-6">
                    <label><?=lang('messages_lang.labelle_code_budgetaire')?></label>
                    <select class="form-control select2" onchange="get_activitesByCode();" name="CODE_NOMENCLATURE_BUDGETAIRE" id="CODE_NOMENCLATURE_BUDGETAIRE">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      <?php
                      foreach($code_budget as $key)
                      {
                        if($key->CODE_NOMENCLATURE_BUDGETAIRE==set_value('CODE_NOMENCLATURE_BUDGETAIRE'))
                        {
                          echo "<option value='".$key->CODE_NOMENCLATURE_BUDGETAIRE."'  selected>".$key->CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->CODE_NOMENCLATURE_BUDGETAIRE."' >".$key->CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                        }
                      }
                      ?>
                  </select>
                  </div>

                  <div class="col-md-6">
                    <label><?=lang('messages_lang.label_activite')?></label>
                    <select class="form-control" name="ACTIVITES" id="ACTIVITES" onchange="get_montant()">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      
                  </select>
                  <span class="text-danger" id="error_activite"></span>
                  <?php if (isset($validation)) : ?>
                  <?= $validation->getError('ACTIVITES'); ?>
                  <?php endif ?>
                  </div>
                </div>

                <div class="row col-md-12">
                  <label><?=lang('messages_lang.labelle_montant')?></label>
                  <input type="text" name="MONTANT" id="MONTANT" value="<?=set_value('MONTANT')?>" class="form-control">
                  <span class="text-danger" id="error_montant"></span>
                  <span class="text-danger" id="montant_sup"></span>
                  <?php if (isset($validation)) : ?>
                  <?= $validation->getError('MONTANT'); ?>
                  <?php endif ?>
                </div><br>

                <div class="row">
                  <label></label>
                  <div class="col-md-12"><h5><?=lang('messages_lang.label_vers')?></h5></div>
                  <div class="col-md-6">
                    <label><?=lang('messages_lang.labelle_code_budgetaire')?></label><?=lang('messages_lang.labelle_montant')?>
                    <select class="form-control select2" onchange="get_activitesByCode2();" name="CODE_NOMENCLATURE_BUDGETAIRE2" id="CODE_NOMENCLATURE_BUDGETAIRE2">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      <?php
                      foreach($code_budget as $key)
                      {
                        if($key->CODE_NOMENCLATURE_BUDGETAIRE==set_value('CODE_NOMENCLATURE_BUDGETAIRE'))
                        {
                          echo "<option value='".$key->CODE_NOMENCLATURE_BUDGETAIRE."'  selected>".$key->CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->CODE_NOMENCLATURE_BUDGETAIRE."' >".$key->CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                        }
                      }
                      ?>
                  </select>
                  </div>

                  <div class="col-md-6">
                    <label><?=lang('messages_lang.label_activite')?></label>
                    <select class="form-control" name="ACTIVITES2"  id="ACTIVITES2">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      
                  </select>
                  <span class="text-danger" id="error_activite2"></span>
                  <?php if (isset($validation)) : ?>
                  <?= $validation->getError('ACTIVITES2'); ?>
                  <?php endif ?>
                  </div>
                </div>

              
                <div class="row">
                  <div style="float:right" class="mt-4">
                    <button style="float: right;" id="btn_transfer" type="button" class="btn btn-primary float-end envoi"  onclick="transferer()"> &nbsp;<?=lang('messages_lang.btn_transfer')?></button>
                  </div>
                </div> 
              </form>                               
            </div>
          </div>
        </div>
      </main>
    </div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>


<div class="modal" id='modal_recoie'>
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title"><?=lang('messages_lang.label_confirmation')?> </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-5"><p style="float:right;"><?=lang('messages_lang.label_activite')?> 1 </p></div>
              <div class="col-md-1"> : </div>
              <div class="col-md-6" id="activite1"> </div>
            </div><hr>
            
            <div class="row">
              <div class="col-md-5"><p style="float:right;"><?=lang('messages_lang.labelle_montant')?> </p></div>
              <div class="col-md-1"> : </div>
              <div class="col-md-6" id="montant_t"> </div>
            </div><hr>

            <div class="row">
              <div class="col-md-5"><p style="float:right;"><?=lang('messages_lang.label_activite')?> 2 </p></div>
              <div class="col-md-1"> : </div>
              <div class="col-md-6" id="activite2"> </div>
            </div><br>
                         
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=lang('messages_lang.bouton_modifier')?></button>
              <button type="button" onclick="save()" class="btn btn-primary"><?=lang('messages_lang.bouton_confirmer')?></button>
          </div>
      </div>

  </div>
</div>



</body>

<script>
  function get_montant()
  {
    var ACTIVITES=$('#ACTIVITES').val();

    if(ACTIVITES=='')
    {
      $('#MONTANT').val('0');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/demande_new/Transfert/get_montant/"+ACTIVITES,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#MONTANT').val(data.mont);
          $('#MONTANT_h').val(data.mont);

        }
      });

    }
  }
</script>

<script>
  function save() 
  {
    $("#MyFormData").submit();
  }
  function transferer(){
    var statut = 1;
    var ACTIVITES = $('#ACTIVITES').val();
    var MONTANT = $('#MONTANT').val();
    var ACTIVITES2 = $('#ACTIVITES2').val();
    var MONTANT_h = $('#MONTANT_h').val();

    var mount = parseInt(MONTANT);
    var mount_h = parseInt(MONTANT_h);

    
    if(ACTIVITES=='')
    {
      $('#error_activite').html("<?=lang('messages_lang.champ_obligatoire')?>");
      statut = 0;
    }else{
      $('#error_activite').html('');
      statut = 1;
    }
    if(MONTANT=='')
    {
      $('#error_montant').html("<?=lang('messages_lang.champ_obligatoire')?>");
      statut = 0;
    }else{
      $('#error_montant').html('');
      statut = 1;
    }

    if(ACTIVITES2=='')
    {
      $('#error_activite2').html("<?=lang('messages_lang.champ_obligatoire')?>");
      statut = 0;
    }else{
      $('#error_activite2').html('');
      statut = 1;
    }

    if(mount > mount_h) 
    {
      statut = 0;
      $('#montant_sup').html('<?=lang('messages_lang.err_mont_inf_activ')?>');
    }else{

      statut = 1;
      $('#montant_sup').html('');
      
    }

           
    if (statut == 1) {

      $('#modal_recoie').modal('show');
      $('#activite1').html(ACTIVITES)
      $('#montant_t').html(mount)
      $('#activite2').html(ACTIVITES2)
    }
      
  }
</script>
 

<script>
  function get_activitesByCode()
  {
    var CODE_NOMENCLATURE_BUDGETAIRE=$('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    if(CODE_NOMENCLATURE_BUDGETAIRE=='')
    {
      $('#ACTIVITES').html('<option value=""><?=lang('messages_lang.label_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/demande_new/Transfert/get_activitesByCode/"+CODE_NOMENCLATURE_BUDGETAIRE,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#ACTIVITES').html(data.activite_by_code);
        }
      });

    }
  }
</script>

<script>
  function get_activitesByCode2()
  {
    var CODE_NOMENCLATURE_BUDGETAIRE2=$('#CODE_NOMENCLATURE_BUDGETAIRE2').val();
    if(CODE_NOMENCLATURE_BUDGETAIRE2=='')
    {
      $('#ACTIVITES').html('<option value=""><?=lang('messages_lang.label_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/demande_new/Transfert/get_activitesByCode2/"+CODE_NOMENCLATURE_BUDGETAIRE2,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#ACTIVITES2').html(data.activite_by_code);
        }
      });

    }
  }
</script>
</html>