<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation();?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <style type="text/css">
      .modal-signature {
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        border-bottom-right-radius: .3rem;
        border-bottom-left-radius: .3rem
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
            <div class="row" style="margin-top: -5px">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div class="card-body">
                    <div class="card-body" style="margin-top: -20px">
                      <div style="float: right;">
                        <a href="<?php echo base_url('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.lab_list')?></a>
                      </div>
                      <div>
                        <font style="font-size:18px,color:#333">
                          <h4>
                            <?= lang('messages_lang.lab_phase')?>: <?= $id['DESC_ETAPE_DOUBLE_COMMANDE']?>
                          </h4>
                        </font>
                      </div>
                      <hr>
                      <form action="<?= base_url('double_commande_new/Phase_comptable_Salaire/save_signature_titre_dir_compt') ?>" id="MyFormData" method="post">
                        <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                        <input type="hidden" name="id_exec_titr_dec" value="<?= $id['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'] ?>">
                        <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                        <div class="col-md-12 container " style="border-radius:10px">
                          <div class="row mt-3">
                            <div class="col-md-6"><br>
                              <label for=""> <?= lang('messages_lang.labelle_d_recept')?><font color="red">*</font>
                              </label>
                              <input type="date" name="date_reception" value="<?=date('Y-m-d')?>" min="<?= date('Y-m-d', strtotime($id['DATE_ELABORATION_TD'])) ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
                              <font color="red" id="date_reception_error"></font>
                              <font color="red" id="charCount1"></font>
                            </div>
                            
                            <div class="col-md-6"><br>
                              <label for=""><?= lang('messages_lang.lab_dec')?><font color="red">*</font></label>
                              <select type="" name="ID_OPERATION" id="ID_OPERATION" class="form-control" onchange="get_rejet()">
                                <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                <?php
                                foreach ($type as $values)
                                {
                                  if ($values->ID_OPERATION == set_value('ID_OPERATION')) 
                                  {
                                    echo '<option value="'.$values->ID_OPERATION .'">'. $values->DESCRIPTION.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'. $values->ID_OPERATION.'">'. $values->DESCRIPTION.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('ID_OPERATION'); ?>
                              <?php endif ?>
                              </select>
                              <font color="red" id="error_ID_OPERATION"></font>
                            </div>
                              
                            <div class="col-md-6" id="show_motif" style="display:none;"><br>
                              <label for=""><?= lang('messages_lang.lab_motif')?><font color="red">*</font><span id="loading_motif"></span></label>
                              <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                <?php
                                foreach ($motif as $value)
                                {
                                  if ($value->BUDGETAIRE_TYPE_ANALYSE_ID == set_value('BUDGETAIRE_TYPE_ANALYSE_ID'))
                                  { 
                                    echo '<option value="'.$value->BUDGETAIRE_TYPE_ANALYSE_ID.'" selected>'.$value->DESC_BUDGETAIRE_TYPE_ANALYSE.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'.$value->BUDGETAIRE_TYPE_ANALYSE_ID.'">'.$value->DESC_BUDGETAIRE_TYPE_ANALYSE.'</option>';
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
                            <div class="col-md-6" id="show_date_sign" style="display:none;"><br>
                              <label for=""><?= lang('messages_lang.lab_d_sign')?><font color="red">*</font></label>
                              <input type="date" name="date_signature_titre" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_signature_titre_id" class="form-control" value="<?=date('Y-m-d')?>">
                              <font color="red" id="date_signature_titre_error"></font>
                            </div>
                            <div class="col-md-6"><br>
                              <label for=""> <?= lang('messages_lang.labelle_d_transm')?><font color="red">*</font></label>
                              <input type="date" name="date_transmission" max="<?= date('Y-m-d')?>"  min="<?= date('Y-m-d')?>" id="date_transmission_id" class="form-control" value="<?=date('Y-m-d')?>"><font color="red" id="date_transmission_error"></font>
                              <font color="red" id="charCount1"></font>
                            </div>
                          </div>
                          <div style="float:right" class="mt-4">
                            <a class="btn btn-primary" onclick="save_dossier()" class="form-control"><?= lang('messages_lang.lab_enrg')?></a>
                          </div>
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
    <?php echo view('includesbackend/scripts_js.php'); ?>
    <div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_modal')?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="table-responsive overflow-auto mt-2">
              <table class=" table  m-b-0 m-t-20">
                <tbody>
                  <tr>
                    <td><i class="fa fa-calendar"></i><?= lang('messages_lang.labelle_d_recept')?></td>
                    <td id="date_reception_id_modal"></td>
                  </tr>
                  <tr>
                    <td><i class="fa fa-cogs"></i><?= lang('messages_lang.label_decision')?> </td>
                    <td id="decision"></td>
                  </tr>

                  <tr id="motif_ret">
                    <td><i class="fa fa-list"></i> <?= lang('messages_lang.label_motif_dec')?></td>
                    <td id="motif_retour_id_modal"></td>
                  </tr>
                  <tr id="det_sing">
                    <td><i class="fa fa-calendar"></i><?= lang('messages_lang.lab_d_sign')?></td>
                    <td id="date_signature_id_modal"></td>
                  </tr>
                  <tr>
                    <td><i class="fa fa-calendar"></i><?= lang('messages_lang.lab_d_trans')?></td>
                    <td id="date_transmission_id_modal"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
            <a id="myElement" onclick="save_info();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></a>
          </div>
        </div>
      </div>
    </div>
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
  function get_min_trans()
  {
    $("#date_transmission_id").prop('min', $("#date_reception_id").val());
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
      $("#date_signature_titre_error").html("");
    }
    else
    {
      if (OPERATION == 1 || OPERATION == 3)
      {
        $('#show_motif').show();
        $('#show_date_sign').hide();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $("#date_signature_titre_error").html("");
      }
      else
      {
        $('#show_motif').hide();
        $('#show_date_sign').show();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $("#date_signature_titre_error").html("");
      }
    }
  }
</script>

<script>
  function save_dossier()
  {
    var statut = true;
    var date_transmission_id = $('#date_transmission_id').val();
    var date_reception_id = $('#date_reception_id').val();
    var date_signature_titre_id = $('#date_signature_titre_id').val();
    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

    if (date_transmission_id == "")
    {
      statut = false;
      $("#date_transmission_error").html("<?= lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#date_transmission_error").html("");
    }

    if (date_reception_id == "")
    {
      statut = false;
      $("#date_reception_error").html("<?= lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#date_reception_error").html("");
    }

    if (ID_OPERATION == '')
    {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');
    }
    else
    {
      $('#error_ID_OPERATION').html('');
    }
    if (ID_OPERATION == 1 || ID_OPERATION == 3)
    {
      if (TYPE_ANALYSE_MOTIF_ID == '')
      {
        statut = false;
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('<?= lang('messages_lang.error_sms')?>');
      }
      else
      {
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      }
    }
    else
    {
      if (date_signature_titre_id == "")
      {
        statut = false;
        $("#date_signature_titre_error").html("<?= lang('messages_lang.error_sms')?>");
      }
      else
      {
        $("#date_signature_titre_error").html("");
      }
    }

    if (statut == true)
    {
      var date = moment(date_reception_id, "YYYY/mm/DD")
      var reception_date = date.format('DD/mm/YYYY')
      var date1 = moment(date_transmission_id, "YYYY/mm/DD")
      var transmission_date = date1.format('DD/mm/YYYY')
      var date2 = moment(date_signature_titre_id, "YYYY/mm/DD")
      var date_signature_titre = date2.format('DD/mm/YYYY')
      var operation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();

      $('#date_reception_id_modal').html(reception_date);
      $('#date_transmission_id_modal').html(transmission_date);
      $('#date_signature_id_modal').html(date_signature_titre);
      $('#decision').html(operation);
      
      if (ID_OPERATION == 1 || ID_OPERATION == 3)
      {
        var motif = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
        var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${motif}</ol>`;
        $('#motif_retour_id_modal').html(orderedList);

        $('#det_sing').hide();
        $('#date_signature_id_modal').hide();
      }
      else
      {
        $('#motif_ret').hide();
        $('#motif_retour_id_modal').hide();

        $('#det_sing').show();
        $('#date_signature_id_modal').show();
      }
      $('#detail').modal()
    }
  }
</script>

<script>
  function save_info()
  {
    $('#MyFormData').submit()
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