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
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div class="card-header">
                    <div class="row col-md-12">
                      <div class="col-md-10">
                        <h3 class="header-title text-black"><?= lang('messages_lang.labelle_phas') ?>:<?php echo $id['DESC_ETAPE_DOUBLE_COMMANDE'] ?></h3>
                      </div>
                      <div style="float: right;">
                        <a href="<?php echo base_url('double_commande_new/Paiement_Salaire_Liste/vue_sign_ministre') ?>" style="float: right; width: 100px; margin-right: 20px;margin-top:25px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.bouton_liste')?></a>
                      </div>                    
                    </div>
                  </div>
                  <div style="margin: 20px;margin-top: 0px"><hr></div>                  
                  <div class="car-body">
                    <form id='MyFormData' enctype='multipart/form-data' action="<?=base_url('double_commande_new/Phase_Comptable_Salaire/save_signature_titre_min') ?>" method="POST">
                      <input type="hidden" name="id_exec_titr_dec" id="id_exec_titr_dec" value="<?=$id['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                      <input type="hidden" name="id_raccrochage" id="id_raccrochage" value="<?=$id['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                      <input type="hidden" name="etape" id="etape" value="<?=$id['ETAPE_DOUBLE_COMMANDE_ID']?>">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-4"><br>
                            <label><?= lang('messages_lang.labelle_date_rech') ?><span style="color: red;">*</span></label>
                            <input type="date" class="form-control" name="DATE_RECEPTION" value="<?=date('Y-m-d')?>" min="<?php echo date('Y-m-d',strtotime($id['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" id="DATE_RECEPTION" onkeypress="return false" onblur="this.type='date'" onchange="get_date_min_trans()">
                            <span id="error_dat_rec" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('DATE_RECEPTION'); ?>
                            <?php endif ?>
                          </div>
                          <div class="col-md-4"><br>
                            <label> <?= lang('messages_lang.lab_dec')?><span style="color: red;">*</span></label>
                            <select class="form-control" name="ID_OPERATION" id="ID_OPERATION" onchange="get_rejet(this)">
                              <option value=""> <?= lang('messages_lang.labelle_select') ?></option>
                              <?php
                              foreach ($operation as $key) 
                              {
                                if ($key->ID_OPERATION == set_value('ID_OPERATION'))
                                {
                                  echo "<option value='" . $key->ID_OPERATION . "' >" . $key->DESCRIPTION . "</option>";
                                }
                                else
                                {
                                  echo "<option value='" . $key->ID_OPERATION . "' >" . $key->DESCRIPTION . "</option>";
                                }
                              }
                              ?>
                            </select>
                            <font color="red" id="error_ID_OPERATION"><?= $validation->getError('ID_OPERATION'); ?></font>
                          </div>
                          <div class="col-md-4" id="show_motif" style="display:none;"><br>
                            <label for=""><?= lang('messages_lang.labelle_mot') ?><font color="red">*</font><span id="loading_motif"></span></label>
                            <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)' >
                              <option value="-1"><?=lang('messages_lang.selection_autre')?></option>

                              <?php
                              foreach ($motif as $value)
                              {
                                if ($value->TYPE_ANALYSE_MOTIF_ID == set_value('TYPE_ANALYSE_MOTIF_ID'))
                                {
                                  echo '<option value="'.$value->TYPE_ANALYSE_ID.'" selected>'.$value->DESC_TYPE_ANALYSE_MOTIF.'</option>';
                                }
                                else
                                {
                                  echo '<option value="'.$value->TYPE_ANALYSE_MOTIF_ID.'">'.$value->DESC_TYPE_ANALYSE_MOTIF.'></option>';                           
                                }
                              }
                              ?>
                            </select>
                            <br>
                            <span id="autre_motif" class="col-md-12 row" style="display: none">
                              <div class="col-md-9">
                                <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_MOTIF">
                              </div>
                              <div class="col-md-2" style="margin-left: 5px;">
                                <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                              </div>
                            </span>
                            <?php if (isset($validation)) : ?>
                                <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                            <?php endif ?>
                          </div>                        
                          <div class="col-md-4" id="show_date_sign" style="display:none;"><br>
                            <label for=""><?= lang('messages_lang.labelle_date_sign') ?><span style="color: red;">*</span></label>
                            <input type="date" class="form-control" id="DATE_SIGNATURE" name="DATE_SIGNATURE" value="<?=date('Y-m-d')?>" max="<?=date('Y-m-d')?>">
                            <span class="text-danger" id="error_dat_sign"></span>
                            <?= $validation->getError('DATE_SIGNATURE'); ?>
                          </div>
                          <div class="col-md-4"><br>
                            <label><?= lang('messages_lang.labelle_date_tansmiss') ?><span style="color: red;">*</span></label>
                            <input type="date" class="form-control" name="DATE_TRANSMISSION" max="<?=date('Y-m-d')?>" id="DATE_TRANSMISSION" value="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('DATE_TRANSMISSION'); ?>
                            <?php endif ?>
                            <span id="error_dat_trans" class="text-danger"></span>
                          </div>                        
                        </div>
                      </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.btn_enr') ?></a>
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
    <!--******* Modal pour confirmer les infos saisies *********-->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_modal') ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="table-responsive  mt-3">
              <table class="table m-b-0 m-t-20">
                <tbody>

                  <tr>
                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_rech') ?></strong></td>
                    <td id="date_rec_verifie" class="text-dark"></td>
                  </tr>
                  <tr>
                      <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_operatio') ?></td>
                      <td id="operation_validation_modal"></td>
                  </tr>
                  
                  <tr id="motif_ret">
                    <td><i class="fa fa-list"></i> <?= lang('messages_lang.label_motif_dec')?></td>
                    <td id="motif_retour_id_modal"></td>
                  </tr>
                                  
                  <tr id="det_sing">
                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_sign') ?></strong></td>
                    <td id="date_sign_verifie" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_tansmiss') ?></strong></td>
                    <td id="date_trans_verifie" class="text-dark"></td>
                  </tr>
                  
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
                  <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
                  <a id="myElement" onclick="confirm();hideButton()"style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></a>
              </div>
          
        </div>
      </div>
    </div>
    <?php echo view('includesbackend/scripts_js.php');?>
  </body>
</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>
<script>
  //function pour donner la date minimum de transmission
  function get_date_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
    $("#DATE_SIGNATURE").prop('min',$("#DATE_RECEPTION").val());
  }
</script>
<script type="text/javascript">
  function save()
  {
    var DATE_RECEPTION  = $('#DATE_RECEPTION').val();
    var DATE_TRANSMISSION  = $('#DATE_TRANSMISSION').val();
    var DATE_SIGNATURE  = $('#DATE_SIGNATURE').val();

    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

    $('#error_dat_rec').html(''); 
    $('#error_dat_trans').html('');
    $('#error_dat_sign').html('');
    $("#error_ID_OPERATION").html('');
    $("#error_TYPE_ANALYSE_MOTIF_ID").html('');

    var statut = 2;


    if (ID_OPERATION == '') {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');

    }

    if (ID_OPERATION == 1 || ID_OPERATION == 3) {
      if (TYPE_ANALYSE_MOTIF_ID == '') {
        statut = false;
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('<?= lang('messages_lang.error_sms')?>');

      } else {
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      }
    }else{

      if(DATE_SIGNATURE  == '')
      {
        $('#error_dat_sign').html('<?= lang('messages_lang.error_sms') ?>');
        statut = 1;
      }
    }

    if(DATE_RECEPTION=='')
    {
      $('#error_dat_rec').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }

    if(DATE_TRANSMISSION  == '')
    {
      $('#error_dat_trans').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }
    

    if(statut == 2)
    {
      var date_rec = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var result_rec = date_rec.format("DD/mm/YYYY");
      $('#date_rec_verifie').html(result_rec);

      var date_trans = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var result_trans = date_trans.format("DD/mm/YYYY");
      $('#date_trans_verifie').html(result_trans);

      var date_sign = moment(DATE_SIGNATURE, "YYYY/mm/DD");
      var result_sign = date_trans.format("DD/mm/YYYY");
      $('#date_sign_verifie').html(result_sign);

      var operation_validation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();
      $('#operation_validation_modal').html(operation_validation);

      if(ID_OPERATION == 1 || ID_OPERATION == 3) {
        var motif = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
        var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${motif}</ol>`;
        $('#motif_retour_id_modal').html(orderedList);

        $('#det_sing').hide();
        $('#date_sign_verifie').hide();
      }else{
        $('#motif_ret').hide();
        $('#motif_retour_id_modal').hide();
        $('#det_sing').show();
        $('#date_sign_verifie').show();

      }
      $('#myModal').modal('show');
    }
  }
</script>
<script>
  function confirm()
  {
    $('#MyFormData').submit();
  }
</script>

<script>
  function get_rejet()
  {
    var OPERATION = $('#ID_OPERATION').val();

    if (OPERATION == '')
    {
      $('#show_motif').hide();
      $('#show_date_sign').hide();
      $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      $('#error_dat_sign').html('');

    }
    else
    {
      if (OPERATION == 1 || OPERATION == 3)
      {
        $('#show_motif').show();
        $('#show_date_sign').hide();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $('#error_dat_sign').html('');
      }
      else
      {
        $('#show_motif').hide();
        $('#show_date_sign').show();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $('#error_dat_sign').html('');
      }
    }
  }
</script>

<script type="text/javascript">
  function getAutreMotif(id = 0)
  {
    var selectElement = document.getElementById("TYPE_ANALYSE_MOTIF_ID");
    if (id.includes("-1"))
    {
      $('#autre_motif').delay(100).show('hide');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      disableOptions(selectElement);

    }
    else
    {
      $('#autre_motif').delay(100).hide('show');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      enableOptions(selectElement);
    }
  }

  function disableOptions(selectElement) 
  {
    for (var i = 0; i < selectElement.options.length; i++) 
    {
      if (selectElement.options[i].value !== "-1") 
      {
        selectElement.options[i].disabled = true;
      }
    }
  }

  function enableOptions(selectElement) 
  {
    for (var i = 0; i < selectElement.options.length; i++) 
    {
      selectElement.options[i].disabled = false;
    }
  }

  function save_newMotif()
  {
    var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
    var statut = 2;
    
    if (DESCRIPTION_MOTIF == "")
    {
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Phase_comptable/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF
        },
        beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#TYPE_ANALYSE_MOTIF_ID').html(data.motifs);
          TYPE_ANALYSE_MOTIF_ID.InnerHtml=data.motifs;
          $('#loading_motif').html("");
          $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          $('#autre_motif').delay(100).hide('show');
        }
      });
    }
  }
</script>

