<!DOCTYPE html>
<html>
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
                      <h3> <?=lang('messages_lang.title_modif_obj_eng')?></h3>
                    </div>
                    <!-- <div class="col-md-3" style="float: right;">
                      <a href="<?php //echo base_url('double_commande_new/Liste_Trans_Deja_Fait_PC') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?//= lang('messages_lang.list_transmission_du_bordereau') ?> </a>
                    </div> -->
                   
                  </div>
                </div>
                <hr>
                <div class="card-body">
                  <?php
                      if(session()->getFlashKeys('alert'))
                      {
                        ?>
                        <center class="ml-5" style="height=100px;width:90%" >
                          <div class="w-100 bg-danger text-white text-center"  id="message">
                            <?php echo session()->getFlashdata('alert')['message']; ?>
                          </div>
                        </center>
                        <?php
                      } ?>
                      <font id="mess" color="red">
                      <?php
                        if (isset($mess)){
                          echo $mess;
                        }
                      ?></font>
                      <br>

                  <form id="my_form" action="<?= base_url('double_commande_new/Modifier_Objet_Engag/save') ?>" method="POST" enctype="multipart/form-data">
                    
                    <!-- <input type="hidden" name="id_etape" id="id_etape" value="<?//=$id_etape?>"> -->

                    <div class="row col-md-12">
                      <div class="col-6">
                        <label> <?= lang('messages_lang.Bon_engagement') ?> <span style="color: red;">*</span><b id="loading"></b></label>
                        <select class="form-control" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" onchange="get_info()">
                          <option value=""><?=lang('messages_lang.selectionner_transmission_du_bordereau')?></option>
                          <?php  
                          foreach ($BEs as $keys)
                          {
                            if($keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID==set_value('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'))
                            { 
                              echo '<option value="'.$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" selected>
                                '.$keys->NUMERO_BON_ENGAGEMENT.'</option>';
                            }
                            else
                            {
                              echo '<option value="'.$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID .'">
                                '.$keys->NUMERO_BON_ENGAGEMENT.'</option>';
                            }
                          }
                          ?>
                        </select>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'); ?>
                        <?php endif ?>
                        <span id="error_EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" class="text-danger"></span>
                      </div>

                      <div class="col-md-6">
                        <label><?= lang('messages_lang.label_number_titre_creance') ?></label>
                        <input type="text" name="TITRE_CREANCE" id="TITRE_CREANCE" class="form-control">
                        <span id="error_TITRE_CREANCE" class="text-danger"></span>
                      </div>

                      <div class="col-md-6">
                        <label><?= lang('messages_lang.label_obje') ?></label>
                        <textarea name="COMMENTAIRE" id="COMMENTAIRE" class="form-control"></textarea>
                        <span id="error_COMMENTAIRE" class="text-danger"></span>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('COMMENTAIRE'); ?>
                        <?php endif ?>
                      </div>

                      <div class="col-md-6">
                        <label><?= lang('messages_lang.table_moif') ?></label>
                        <textarea name="MOTIF_LIQUIDATION" id="MOTIF_LIQUIDATION" class="form-control"></textarea>
                        <span id="error_MOTIF_LIQUIDATION" class="text-danger"></span>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('MOTIF_LIQUIDATION'); ?>
                        <?php endif ?>
                      </div>
                    </div>
                  </form>
                  <div class="card-footer">
                    <div style="float:right;margin-bottom:5%">
                      <a onclick="save();" id="btn_save" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.enregistre_transmission_du_bordereau') ?></a>
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

    <div class="modal fade" id="modfi_objet" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <td id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.label_number_titre_creance') ?></strong></td>
                    <td id="TITRE_CREANCE_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.label_obje') ?></strong></td>
                    <td id="COMMENTAIRE_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-file-pdf"></i> &nbsp;<strong><?= lang('messages_lang.table_moif') ?></strong></td>
                    <td id="MOTIF_LIQUIDATION_VERIFY" class="text-dark"></td>
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
</body>
</html>
<script>
  function save()
  {
    var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();
    $('#error_EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val('');

    var TITRE_CREANCE=$('#TITRE_CREANCE').val();
    $('#error_TITRE_CREANCE').val('');

    var COMMENTAIRE=$('#COMMENTAIRE').val();
    $('#error_COMMENTAIRE').val('');

    var MOTIF_LIQUIDATION=$('#MOTIF_LIQUIDATION').val();

    var statut=1
    if (EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=='')
    {
      statut=2
      $('#error_EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    }

    if(TITRE_CREANCE=='' && COMMENTAIRE=='' && MOTIF_LIQUIDATION)
    {
      statut=2
      $('#mess').text("Titre de créance , Objet d'engagement ou Motif de la liquidation ne peut pas être vide");
    }
    else
    {
      $('#mess').text('');
    }

    // if(COMMENTAIRE=='')
    // {
    //   statut=2
    //   $('#error_COMMENTAIRE').val('');
    // }

    if(statut==1)
    {
      var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID_VERIFY=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID option:selected').text()
      $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID_VERIFY').text(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID_VERIFY);
      if (TITRE_CREANCE!='')
      {
        $('#TITRE_CREANCE_VERIFY').text(TITRE_CREANCE);
      }
      else
      {
        $('#TITRE_CREANCE_VERIFY').text('-');
      }
      
      if(COMMENTAIRE!='')
      {
        $('#COMMENTAIRE_VERIFY').text(COMMENTAIRE);
      }
      else
      {
        $('#COMMENTAIRE_VERIFY').text('-');
      }

      if(MOTIF_LIQUIDATION!='')
      {
        $('#MOTIF_LIQUIDATION_VERIFY').text(MOTIF_LIQUIDATION);
      }
      else
      {
        $('#MOTIF_LIQUIDATION_VERIFY').text('-');
      }
      
      $('#modfi_objet').modal('show')
    }
  }

  function submit()
  {
    $('#my_form').submit()
  }

  function get_info()
  {
    var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();

    $.ajax(
    {
      url: "<?=base_url()?>/double_commande_new/Modifier_Objet_Engag/get_info/" + EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
      type: "GET",
      dataType: "JSON",
      beforeSend:function()
      {
        $('#loading').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#TITRE_CREANCE').val(data.TITRE_CREANCE);
        $('#COMMENTAIRE').val(data.COMMENTAIRE);
        $('#MOTIF_LIQUIDATION').val(data.MOTIF_LIQUIDATION)

        if($('#TITRE_CREANCE').val()=='')
        {
          $('#TITRE_CREANCE').attr('disabled',true);
        }
        else
        {
          $('#TITRE_CREANCE').attr('disabled',false);
        }

        if($('#COMMENTAIRE').val()=='')
        {
          $('#COMMENTAIRE').attr('disabled',true);
        }
        else
        {
          $('#COMMENTAIRE').attr('disabled',false);
        }

        if($('#MOTIF_LIQUIDATION').val()=='')
        {
          $('#MOTIF_LIQUIDATION').attr('disabled',true);
        }
        else
        {
          $('#MOTIF_LIQUIDATION').attr('disabled',false);
        }
        
        $('#loading').html("")
      }
    });
  }
</script>