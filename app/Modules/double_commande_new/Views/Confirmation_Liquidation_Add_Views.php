<!DOCTYPE html>
<html lang="en">
  <head>
   <?php echo view('includesbackend/header.php');?>
   <?php $validation = \Config\Services::validation(); ?>
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
                  <div class="card-body">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-9" style="float: left;">
                          <h1 class="header-title text-dark">
                            <?=$etape_descr['DESC_ETAPE_DOUBLE_COMMANDE']?>
                          </h1>
                        </div>
                        <div class="col-3" style="float: right;">
                          <a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.link_list')?></a>
                        </div>
                        <div class="col-3" style="float: left;">
                          <div id="accordion">
                            <div class="card-header" id="headingThree">
                              <h5 class="mb-0">
                                <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?=lang('messages_lang.histo_btn')?></button>
                              </h5>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="container" style="width:90%">
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                          <?php include  'includes/Detail_View.php'; ?>
                        </div>
                      </div>
                      <form id="my_form" action="<?= base_url('double_commande_new/Liquidation/insert') ?>" method="POST">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin :  5px">
                            <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">

                            <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" id="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">

                            <input type="hidden" name="idmd5" id="idmd5" value="<?=$idmd5?>">

                            <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">

                            <input type="hidden" name="MONTANT_RACCROCHE_LIQUIDATION" id="MONTANT_RACCROCHE_LIQUIDATION" value="<?=$info['MONTANT_RACCROCHE_LIQUIDATION']?>">

                            <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">
                            <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$info['MARCHE_PUBLIQUE']?>">
                            <input type="hidden" name="BUDGETAIRE_TYPE_DOCUMENT_ID" id="BUDGETAIRE_TYPE_DOCUMENT_ID" value="<?=$info['BUDGETAIRE_TYPE_DOCUMENT_ID']?>">

                            <div class="col-md-6">
                              <label for=""><?=lang('messages_lang.labelle_date_reception_demande')?><span style="color: red;">*</span></label>
                              <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION"  value="<?= date('Y-m-d')?>" min="<?=date('Y-m-d', strtotime($retVal = (!empty($date_trans['DATE_TRANSMISSION'])) ? $date_trans['DATE_TRANSMISSION'] : date('Y-m-d') ))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                              <font color="red" id="error_DATE_RECEPTION"><?= $validation->getError('DATE_RECEPTION'); ?></font>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.label_decision')?> <span style="color: red;">*</span></label>
                              <select onchange="getMotif()" class="form-control" name="ID_OPERATION" id="ID_OPERATION">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php
                                foreach ($get_type_operation_validation as $key) 
                                {
                                  if($key->ID_OPERATION==set_value("ID_OPERATION"))
                                  {
                                    echo '<option value="'.$key->ID_OPERATION.'" selected>'.$key->DESCRIPTION.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'.$key->ID_OPERATION.'">'.$key->DESCRIPTION.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <font  color="red" id="error_ID_OPERATION"><?= $validation->getError('ID_OPERATION'); ?></font>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.labelle_observartion')?> <span style="color: red;" id="signerequired"></span></label>
                              <textarea name="OBSERVATION" id="OBSERVATION" class="form-control"><?=set_value("OBSERVATION")?></textarea>
                              <font color="red" id="error_OBSERVATION"><?= $validation->getError('OBSERVATION'); ?></font>
                            </div>

                            <div class="col-md-6">
                              <label for=""><?=lang('messages_lang.label_date_trans_conf')?><span id="date_trans_label"></span><span style="color: red;">*</span></label>
                              <input type="date" class="form-control" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION"  value="<?= date('Y-m-d') ?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                              <?php if (isset($validation)) : ?>
                                <font color="red" id="error_DATE_TRANSMISSION"><?= $validation->getError('DATE_TRANSMISSION'); ?></font>
                              <?php endif ?>
                            </div>

                            <div class="col-6" id="div_motif" style="display: none;">
                              <label><?=lang('messages_lang.label_motif_dec')?><span style="color: red;">*</span><span id="loading_motif"></span></label>
                              <select class="form-control select2" name="MOTIF_REJET[]" id="MOTIF_REJET" multiple onchange='getAutreMotif(this.value)'>
                                <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                <?php
                                foreach($get_type_analyse_motif as $value)
                                {
                                  if($value->TYPE_ANALYSE_MOTIF_ID==set_value('MOTIF_REJET'))
                                  {
                                    echo '<option value="'.$value->TYPE_ANALYSE_MOTIF_ID .'" selected>'.$value->DESC_TYPE_ANALYSE_MOTIF.'</option>';
                                  }
                                  else                                
                                  {
                                    echo '<option value="'.$value->TYPE_ANALYSE_MOTIF_ID .'">'.$value->DESC_TYPE_ANALYSE_MOTIF.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <br>
                              <span id="autre_motif" class="col-md-12 row" style="display: none">
                                <div class="col-md-9">
                                  <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_SERIE">
                                </div>
                                <div class="col-md-2" style="margin-left: 5px;">
                                  <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                                </div>
                              </span>
                              <font color="red" id="error_MOTIF_REJET"><?= $validation->getError('MOTIF_REJET[]'); ?></font>
                            </div>

                            <div class="col-6" id="div_etape_retour" style="display: none;">
                              <label><?=lang('messages_lang.labelle_etape_retour_correction')?><span style="color: red;">*</span></label>
                              <select class="form-control" name="ETAPE_RETOUR_CORRECTION_ID" id="ETAPE_RETOUR_CORRECTION_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php
                                foreach ($get_etape_retour as $key)
                                {
                                  if($key->ETAPE_RETOUR_CORRECTION_ID==set_value("ETAPE_RETOUR_CORRECTION_ID"))
                                  {
                                    echo '<option value="'.$key->ETAPE_RETOUR_CORRECTION_ID.'" selected>'.$key->DESCRIPTION_ETAPE_RETOUR.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'.$key->ETAPE_RETOUR_CORRECTION_ID.'">'.$key->DESCRIPTION_ETAPE_RETOUR.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <font color="red" id="error_ETAPE_RETOUR_CORRECTION_ID"><?= $validation->getError('ETAPE_RETOUR_CORRECTION_ID'); ?></font>
                            </div>
                          </div>
                        </div>
                        <div class="col-12">
                          <button style="float: right;" id="disabled_btn" type="button" onclick="send_data()" class="btn btn-primary float-end envoi"><b id="loading_save"></b><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?> <span id="loading_btn"></span></button>
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
<script>
  function hideButton()
  {
    var element = document.getElementById("send_data2");
    element.style.display = "none";

    var elementmod = document.getElementById("btn_delete");
    elementmod.style.display = "none";
  }
</script>

<script type="text/javascript">
  function get_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
  }
</script>

<script type="text/javascript">
  function getMotif()
  {
    var ID_OPERATION = $('#ID_OPERATION').val();
    var MONTANT_RACCROCHE_LIQUIDATION = $('#MONTANT_RACCROCHE_LIQUIDATION').val();
    
    if(ID_OPERATION == 1)
    {
      $('#div_motif').show();
      $('#div_etape_retour').show();
      $('#signerequired').text('');
      $('#date_trans_label').text('');
    }
    else
    {
      $('#div_motif').hide();
      $('#div_etape_retour').hide();
      $('#MOTIF_REJET').val('')
      $('#ETAPE_RETOUR_CORRECTION_ID').val('')
      $('#signerequired').text('');
      $('#date_trans_label').text('');

      if (MONTANT_RACCROCHE_LIQUIDATION<500000000)
      {
        $('#date_trans_label').text('(DG)');
      }
      else
      {
        $('#date_trans_label').text('(Ministre)');
      }
    }

    if (ID_OPERATION==3)
    {
      $('#signerequired').text('*');
      $('#date_trans_label').text('');
    }

    $('#MOTIF_REJET').val([]).trigger('change');
    $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
    $('#autre_motif').delay(100).hide('show');    
  }
</script>

<script type="text/javascript">
  function send_data()
  {
    var statut = true;

    var OBSERVATION=$('#OBSERVATION').val()
    var ID_OPERATION=$('#ID_OPERATION').val()
    var MOTIF_REJET = $('#MOTIF_REJET').val()
    var ETAPE_RETOUR_CORRECTION_ID = $('#ETAPE_RETOUR_CORRECTION_ID').val()
    var DATE_RECEPTION = $('#DATE_RECEPTION').val()
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val()
    var EXECUTION_BUDGETAIRE_DETAIL_ID = $('#EXECUTION_BUDGETAIRE_DETAIL_ID').val();
    var BUDGETAIRE_TYPE_DOCUMENT_ID=$('#BUDGETAIRE_TYPE_DOCUMENT_ID').val();
    
    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_RECEPTION').text('');
    }

    if (ID_OPERATION=='') 
    {
      $('#error_ID_OPERATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_ID_OPERATION').text('');
    }

    if (DATE_TRANSMISSION=='') 
    {
      $('#error_DATE_TRANSMISSION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_TRANSMISSION').text('');
    }

    if (ID_OPERATION == 1)
    {
      if (MOTIF_REJET=='') 
      {
        $('#error_MOTIF_REJET').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_MOTIF_REJET').text('');
      }

      if (ETAPE_RETOUR_CORRECTION_ID=='') 
      {
        $('#error_ETAPE_RETOUR_CORRECTION_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_ETAPE_RETOUR_CORRECTION_ID').text('');
      }
      var MOTIF_REJET = $('#MOTIF_REJET option:selected').toArray().map(item => item.text).join();
    }

    if (ID_OPERATION==3)
    {
      if (OBSERVATION=='') 
      {
        $('#error_OBSERVATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_OBSERVATION').text('');
      }
    }

    if (statut == true) 
    {
      if (ID_OPERATION == 1)
      {
        $('#rej_eng').show();
      }
      else
      {
        $('#rej_eng').hide();
      }

      var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");

      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

      var TYPE_ANALYSE_MOTIF_ID = $('#MOTIF_REJET option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');

      var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${TYPE_ANALYSE_MOTIF_ID}</ol>`;

      $('#motif_verifie').html(orderedList);

      $('#ID_OPERATION_verifie').html($('#ID_OPERATION option:selected').text());
      $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);
      $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);

      if(OBSERVATION != '')
      {
        $('#OBSERVATION_verifie').html(OBSERVATION);
      }else{
        $('#OBSERVATION_verifie').html('<b>-</b>');
      }
      
      $("#etape2_modal").modal("show");
    }
  }

  function send_data2(argument)
  {
    document.getElementById("my_form").submit();
  }
</script>

<div class="modal fade" id="etape2_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.label_decision') ?></strong></td>
                <td id="ID_OPERATION_verifie" class="text-dark"></td>
              </tr>
              <tr id="rej_eng">
                <td><i class="fa fa-certificate"></i> &nbsp;<strong><?= lang('messages_lang.label_retour') ?></strong></td>
                <td id="motif_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?></strong></td>
                <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?></strong></td>
                <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.label_observ') ?></strong></td>
                <td id="OBSERVATION_verifie" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="edi" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
        <a onclick="send_data2();hideButton()" id="conf" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function ()
  {
    var GETDATA_DELETE = $('#GETDATA_DELETE').val();

    if (GETDATA_DELETE == 1)
    {
      window.location.href="<?= base_url('/double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>";
    }
    getMotif()
  });
</script>

<script type="text/javascript">
  function getAutreMotif(id = 0)
  {
    var selectElement = document.getElementById("MOTIF_REJET");
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
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
    var statut = 2;
    if(DESCRIPTION_MOTIF == "")
    {
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Liquidation/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF,
          MARCHE_PUBLIQUE:MARCHE_PUBLIQUE
        },
        beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#MOTIF_REJET').html(data.motifs);
          MOTIF_REJET.InnerHtml=data.motifs;
          $('#loading_motif').html("");
          $('#MOTIF_REJET').val([]).trigger('change');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          $('#autre_motif').delay(100).hide('show');
        }
      });
    }
  }
</script>
