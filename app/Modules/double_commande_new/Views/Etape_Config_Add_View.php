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
                      <a href="<?php echo base_url('double_commande_new/Etape_Double_Commande_Config') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
               <br>
                <div id="collapseThree" class="collapse col-md-12" aria-labelledby="headingThree" data-parent="#accordion">
                 
                </div>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Etape_Double_Commande_Config/save') ?>" method="POST">

                    <div class="row col-md-12">
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.etape_actuel_action') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="ETAPE_DOUBLE_COMMANDE_ACTUEL_ID" id="ETAPE_DOUBLE_COMMANDE_ACTUEL_ID" onchange="next_step()">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <?php  foreach ($etapes as $keys) { ?>
                            <?php if($keys->ETAPE_DOUBLE_COMMANDE_ID ==set_value('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID')) { ?>
                              <option value="<?=$keys->ETAPE_DOUBLE_COMMANDE_ID ?>" selected><?=$keys->DESC_ETAPE_DOUBLE_COMMANDE?></option>
                              <?php }else{?>
                               <option value="<?=$keys->ETAPE_DOUBLE_COMMANDE_ID ?>"><?=$keys->DESC_ETAPE_DOUBLE_COMMANDE?></option>
                              <?php } }?>
                            </select>
                            <span id="error_ETAPE_DOUBLE_COMMANDE_ACTUEL_ID" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'); ?>
                            <?php endif ?>
                      </div>
                      <div class="col-md-6">
                        <br>
                        <label for=""><?=lang('messages_lang.lien_action')?></label>
                        <input type="text" class="form-control" id="LIEN" name="LIEN" value="<?=set_value('LIEN')?>">
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('LIEN'); ?>
                        <?php endif ?>
                        <span class="text-danger" id="error_LIEN"></span> 
                      </div>
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.label_contr_mont') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="CONTRAINTE_MONTANT_ID" id="CONTRAINTE_MONTANT_ID">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <?php  foreach ($contr_mont as $keys) { ?>
                            <?php if($keys->CONTRAINTE_MONTANT_ID ==set_value('CONTRAINTE_MONTANT_ID')) { ?>
                              <option value="<?=$keys->CONTRAINTE_MONTANT_ID ?>" selected><?=$keys->DESC_CONTRAINTE?></option>
                              <?php }else{?>
                               <option value="<?=$keys->CONTRAINTE_MONTANT_ID ?>"><?=$keys->DESC_CONTRAINTE?></option>
                              <?php } }?>
                            </select>
                            <span id="error_CONTRAINTE_MONTANT_ID" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('CONTRAINTE_MONTANT_ID'); ?>
                            <?php endif ?>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.label_contr_benef') ?> <span style="color: red;">*</span></label>
                        <select class="form-control" name="IS_BENEFICIAIRE" id="IS_BENEFICIAIRE">
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          <option value="1"><?= lang('messages_lang.label_oui') ?></option>
                          <option value="0"><?= lang('messages_lang.label_non') ?></option> 
                        </select>
                            <span id="error_IS_BENEFICIAIRE" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('IS_BENEFICIAIRE'); ?>
                            <?php endif ?>
                      </div>
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.etape_suivante_action') ?> <span style="color: red;">*</span><span id="loading_step"></span></label>
                        <select class="form-control" name="ETAPE_DOUBLE_COMMANDE_SUIVANT_ID" id="ETAPE_DOUBLE_COMMANDE_SUIVANT_ID" >
                          <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                          
                        </select>
                            <span id="error_ETAPE_DOUBLE_COMMANDE_SUIVANT_ID" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'); ?>
                            <?php endif ?>
                      </div>
                      </div>
                      <div id="cart_interne" style="padding-top: 10px;">
                        <a href="#" type="button" style="float:right;"  class="btn btn-success" onclick="add_to_cart()"  ><i class="fa fa-plus"></i>&nbsp;Ajouter</a>
                      </div>
                      <br>
                      <br>
                      <div class="col-md-12" id="CART_FILE"></div><br>
                    </form>

                    <div id="SAVE" class="card-footer" style="display: none;">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save" class="btn btn-success" style="float:right;"><?= lang('messages_lang.bouton_enregistrer') ?></a>
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
function add_to_cart()
{

  var statut = 2;
  var ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = $('#ETAPE_DOUBLE_COMMANDE_ACTUEL_ID').val();
  var LIEN = $('#LIEN').val();
  var CONTRAINTE_MONTANT_ID = $('#CONTRAINTE_MONTANT_ID').val();
  var IS_BENEFICIAIRE = $('#IS_BENEFICIAIRE').val();
  var ETAPE_DOUBLE_COMMANDE_SUIVANT_ID = $('#ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').val();

  $('#error_ETAPE_DOUBLE_COMMANDE_ACTUEL_ID').html('');
  $('#error_LIEN').html('');
  $('#error_CONTRAINTE_MONTANT_ID').html('');
  $('#error_IS_BENEFICIAIRE').html('');
  $('#error_ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').html('');


  if (ETAPE_DOUBLE_COMMANDE_ACTUEL_ID === '') {
    $('#error_ETAPE_DOUBLE_COMMANDE_ACTUEL_ID').html("<?= lang('messages_lang.error_sms') ?>");
    statut = 1;
  } else {
    $('#error_ETAPE_DOUBLE_COMMANDE_ACTUEL_ID').html("");
  }

  /*if (LIEN === '') {
    $('#error_LIEN').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }*/

  if (CONTRAINTE_MONTANT_ID === '') {
    $('#error_CONTRAINTE_MONTANT_ID').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }

  if (CONTRAINTE_MONTANT_ID === '') {
    $('#error_CONTRAINTE_MONTANT_ID').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }

  if (IS_BENEFICIAIRE === '') {
    $('#error_IS_BENEFICIAIRE').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }

  if (ETAPE_DOUBLE_COMMANDE_SUIVANT_ID === '') {
    $('#error_ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').html('<?= lang('messages_lang.error_sms') ?>');
    statut = 1;
  }


  if (statut ==2) 
  {
    let url="<?=base_url('double_commande_new/Etape_Double_Commande_Config/add_cart')?>";

    $.post(url,
    {
      ETAPE_DOUBLE_COMMANDE_ACTUEL_ID:ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,
      ETAPE_DOUBLE_COMMANDE_SUIVANT_ID:ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,
      LIEN:LIEN,
      CONTRAINTE_MONTANT_ID:CONTRAINTE_MONTANT_ID,
      IS_BENEFICIAIRE:IS_BENEFICIAIRE

    },
    function (response) {
    // body...
    if (response) 
    {

      $('#CART_FILE').html(response.cart);
      CART_FILE.innerHTML=response.cart;
      $('#SAVE').show();
      if(response.display_save ==1)

        $('#SHOW_FOOTER').show();
      
        $('#ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').val('');
        $('#CONTRAINTE_MONTANT_ID').val('');
        $('#IS_BENEFICIAIRE').val('');


    }else{

      $('#SHOW_FOOTER').show();
      $('#SAVE').show();
    }
    })


  }

}

  

function remove_cart(id)
{

  var rowid=$('#rowid'+id).val();
  console.log('id'+rowid);
  let url="<?=base_url('double_commande_new/Etape_Double_Commande_Config/delete_cart')?>";

  $.post(url,
  {
    rowid:rowid
  },function(data)
  {
    if (data) 
    {
      CART_FILE.innerHTML=data.cart;
      $('#CART_FILE').html(data.cart);
      
      if (data.cart === "") {
        $('#SAVE').hide();
      } else {
        $('#SAVE').show();
      }

     
      $('#SHOW_FOOTER').hide();

    }
    else
    {
      $('#SHOW_FOOTER').hide();
    }

  })
}
</script>

<script type="text/javascript">
  function save()
  {
     $('#my_form').submit();
  }
</script>

<script type="text/javascript">
  function next_step(id)
  {
    var ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = $('#ETAPE_DOUBLE_COMMANDE_ACTUEL_ID').val();
    $('#ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').html('<option value ="">Sélectionner</option>');

    $.ajax({
      url: "<?=base_url('')?>/double_commande_new/Etape_Double_Commande_Config/get_next_step",
      type: "POST",
      dataType: "JSON",
      data: {
        ETAPE_DOUBLE_COMMANDE_ACTUEL_ID:ETAPE_DOUBLE_COMMANDE_ACTUEL_ID
      },
      beforeSend: function() {
        $('#loading_step').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data) {
        $('#ETAPE_DOUBLE_COMMANDE_SUIVANT_ID').html(data.etap_suiv);
        ETAPE_DOUBLE_COMMANDE_SUIVANT_ID.InnerHtml=data.etap_suiv;
        $('#loading_step').html("");
      }
    });
  }
</script>