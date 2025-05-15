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

        <!-- <br> -->
        <div class="card-body">


          <div class="card-body">

            <div class="row">
              <div class="col-9" style="float: left;">
                <h1 class="header-title text-dark">
                  <!-- Confirmation de la demande de liquidation -->
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
                      <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?=lang('messages_lang.histo_btn')?>
                    </button>
                  </h5>
                </div>  
              </div>
            </div>

          </div>

          <!-- debut -->
          <div class="container" style="width:90%">
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
              <?php include  'includes/Detail_View.php'; ?>
            </div>
          </div>
          <!-- fin <?= base_url('double_commande/Liquidation/insert') ?>-->

          <form id="my_form" action="" method="POST">

            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">

                <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" id="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">

                <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">

                <input type="hidden" name="MONTANT_RACCROCHE_LIQUIDATION" id="MONTANT_RACCROCHE_LIQUIDATION" value="<?=$info['MONTANT_RACCROCHE_LIQUIDATION']?>">

                <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">
                <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$info['MARCHE_PUBLIQUE']?>">

                <div class="col-md-6">
                  <label for=""><?=lang('messages_lang.labelle_date_reception_demande')?> (GDC)<span style="color: red;">*</span></label>
                  <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION"  value="" min="<?=date('Y-m-d', strtotime($retVal = (!empty($date_trans['DATE_TRANSMISSION'])) ? $date_trans['DATE_TRANSMISSION'] : date('Y-m-d') ))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                  <font color="red" id="error_DATE_RECEPTION"></font>
                </div>

                <div class="col-6">
                  <label><?=lang('messages_lang.label_decision')?> <span style="color: red;">*</span></label>
                  <select onchange="getMotif()" class="form-control" name="ID_OPERATION" id="ID_OPERATION">
                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                    <?php
                    foreach ($get_type_operation_validation as $key) {
                      ?>
                      <option value="<?=$key->ID_OPERATION?>"><?=$key->DESCRIPTION?></option>
                      <?php
                    }
                    ?>
                  </select>

                  <font  color="red" id="error_ID_OPERATION"></font>
                </div>

                <div class="col-6">
                  <label><?=lang('messages_lang.labelle_observartion')?> <span style="color: red;" id="signerequired"></span></label>
                  <textarea name="OBSERVATION" id="OBSERVATION" class="form-control"></textarea>
                  <font color="red" id="error_OBSERVATION"></font>
                </div>

                <div class="col-md-6">
                  <label for=""><?=lang('messages_lang.label_date_trans_conf')?><span id="date_trans_label"></span><span style="color: red;">*</span></label>
                  <input type="date" class="form-control" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION"  value="<?=set_value('DATE_TRANSMISSION')?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
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
                    if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                    <?php }else                                
                    {
                      ?>
                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>"><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                      <?php
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
                <font color="red" id="error_MOTIF_REJET"></font>
              </div>

              <div class="col-6" id="div_etape_retour" style="display: none;">
                <label><?=lang('messages_lang.labelle_etape_retour_correction')?><span style="color: red;">*</span></label>
                <select class="form-control" name="ETAPE_RETOUR_CORRECTION_ID" id="ETAPE_RETOUR_CORRECTION_ID">
                  <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                  <?php
                  foreach ($get_etape_retour as $key) {
                    ?>
                    <option value="<?=$key->ETAPE_RETOUR_CORRECTION_ID?>"><?=$key->DESCRIPTION_ETAPE_RETOUR?></option>
                    <?php
                  }
                  ?>
                </select>
                <font color="red" id="error_ETAPE_RETOUR_CORRECTION_ID"></font>
              </div>

            </div>
          </div>


          <div class="col-12">
            <button style="float: right;" id="disabled_btn" type="button" onclick="send_data()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?> <span id="loading_btn"></span></button>
          </div>
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


<div class='modal fade' id='detail_infos' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 style="display: block;" id="modal-title" class="modal-title"><?=lang('messages_lang.titre_modal')?> </h5>
        <div id="message"></div>
      </div>
      <div class='modal-body'>
        <div style="display: block;" id="infos_data"></div>
        <!-- generate pdf -->
        <br>
        <div class="col-md-12" id="div_pdf_generate" style="display:none;"> 
          <input type="hidden" name="GETDATA_DELETE" id="GETDATA_DELETE">
          <div id="data_pdf"></div>
        </div>
      </div>
      <div class='modal-footer'>
        <button id="btn_delete" style="display: block;"  onclick="deleteFile()" class='btn btn-primary btn-md' data-dismiss='modal'><i class="fa fa-pencil"></i> <?=lang('messages_lang.labelle_mod')?> <span id="loading_delete"></span></button>

        <a href="<?=base_url('/double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>" style="display: none;" id="btn_liste" class="btn btn-info"><i class="fa fa-list"></i> Liquidation à valider</a>

        <button style="display: block;" id="send_data2" onclick="send_data2();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i> <?=lang('messages_lang.labelle_conf')?> <span id="loading_confirme"></span></button>
      </div>
    </div>
  </div>
</div>

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
      $('#date_trans_label').text('(GDC)');
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

    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }else{
      $('#error_DATE_RECEPTION').text('');
    }

    if (ID_OPERATION=='') 
    {
      $('#error_ID_OPERATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }else{
      $('#error_ID_OPERATION').text('');
    }

    if (DATE_TRANSMISSION=='') 
    {
      $('#error_DATE_TRANSMISSION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }else{
      $('#error_DATE_TRANSMISSION').text('');
    }

    if (ID_OPERATION == 1)
    {
      if (MOTIF_REJET=='') 
      {
        $('#error_MOTIF_REJET').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }else{
        $('#error_MOTIF_REJET').text('');
      }

      if (ETAPE_RETOUR_CORRECTION_ID=='') 
      {
        $('#error_ETAPE_RETOUR_CORRECTION_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }else{
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
      }else{
        $('#error_OBSERVATION').text('');
      }
    }

    if (statut == true) 
    {
      $.ajax(
      {
        url:"<?=base_url('/double_commande_new/Liquidation/getInfoDetail2')?>",
        type:"POST",
        dataType:"JSON",
        data: {
          OBSERVATION:OBSERVATION,
          ID_OPERATION:ID_OPERATION,
          MOTIF_REJET:MOTIF_REJET,
          ETAPE_RETOUR_CORRECTION_ID:ETAPE_RETOUR_CORRECTION_ID,
          DATE_RECEPTION:DATE_RECEPTION,
          DATE_TRANSMISSION:DATE_TRANSMISSION,
        },
        beforeSend:function() {
        $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");//
        $('#disabled_btn').attr('disabled',true);
      },
      success: function(data)
      { 
        $('#detail_infos').modal('show'); // afficher bootstrap modal
        $('#infos_data').html(data.html)
        $('#loading_btn').html("");
        $('#disabled_btn').attr('disabled',false);
      }
    });
    }
  }

  function send_data2(argument)
  {
    var EXECUTION_BUDGETAIRE_DETAIL_ID = $('#EXECUTION_BUDGETAIRE_DETAIL_ID').val();
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
    var OBSERVATION = $('#OBSERVATION').val();
    var TYPE_OPERATION_ID = $('#ID_OPERATION').val();
    var MOTIF_REJET = $('#MOTIF_REJET').val();
    var ETAPE_ID = $('#ETAPE_ID').val();
    var MONTANT_RACCROCHE_LIQUIDATION = $('#MONTANT_RACCROCHE_LIQUIDATION').val();
    var ETAPE_RETOUR_CORRECTION_ID = $('#ETAPE_RETOUR_CORRECTION_ID').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();

    $.ajax({
      url: "<?= base_url('/double_commande_new/Liquidation/insert')?>",
      type: 'POST',
      dataType:'JSON',
      data: {
        EXECUTION_BUDGETAIRE_DETAIL_ID:EXECUTION_BUDGETAIRE_DETAIL_ID,
        EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
        OBSERVATION:OBSERVATION,
        TYPE_OPERATION_ID:TYPE_OPERATION_ID,
        MOTIF_REJET:MOTIF_REJET,
        ETAPE_ID:ETAPE_ID,
        MONTANT_RACCROCHE_LIQUIDATION:MONTANT_RACCROCHE_LIQUIDATION,
        ETAPE_RETOUR_CORRECTION_ID:ETAPE_RETOUR_CORRECTION_ID,
        DATE_RECEPTION:DATE_RECEPTION,
        DATE_TRANSMISSION:DATE_TRANSMISSION,
      },
      beforeSend:function() {
        $('#loading_confirme').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#send_data2').attr('disabled',true);
      },
      success: function(data)
      {
        if (data) 
        { 
          if (TYPE_OPERATION_ID == 2)
          {
            $('#div_pdf_generate').prop('style','');
            $('#data_pdf').html(data.fichier);
            $('#message').html(data.message);

            document.getElementById('modal-title').style.display="none";
            document.getElementById('infos_data').style.display="none";
            document.getElementById('send_data2').style.display="none";
            document.getElementById('btn_delete').style.display="none";
            document.getElementById('btn_liste').style.display="block";
            $('#loading_confirme').html("");
            $('#send_data2').attr('disabled',false);

            $('#GETDATA_DELETE').val(1);
          }
          else
          {
            window.location.href="<?= base_url('/double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>";
          }

        }                       
      }
    });

  }

</script>

<script type="text/javascript">


  function deleteFile()
  {
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
    var GETDATA_DELETE = $('#GETDATA_DELETE').val();

    $.ajax({
      url: "<?= base_url('/double_commande_new/Liquidation/deleteFile')?>",
      type: 'POST',
      dataType:'JSON',
      data: {
        EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
        GETDATA_DELETE:GETDATA_DELETE
      },
      beforeSend:function() {
        $('#loading_delete').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        if (data) 
        {                          
          document.getElementById('btn_pdf').style.display="block";
          document.getElementById('btn_send').style.display="none";
          document.getElementById('div_pdf_generate').style.display="none";
          $('#loading_delete').html("");
        }                       
      }
    });
  }
</script>

<script type="text/javascript">
  $(document).ready(function ()
  {
    var GETDATA_DELETE = $('#GETDATA_DELETE').val();

    if (GETDATA_DELETE == 1)
    {
      window.location.href="<?= base_url('/double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>";
    }
  });
</script>

<script type="text/javascript">
  function getAutreMotif(id = 0)
  {
    var selectElement = document.getElementById("MOTIF_REJET");
    if (id.includes("-1")) {
      $('#autre_motif').delay(100).show('hide');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      disableOptions(selectElement);

    }else{
      $('#autre_motif').delay(100).hide('show');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      enableOptions(selectElement);
    }

  }

  function disableOptions(selectElement) {
    for (var i = 0; i < selectElement.options.length; i++) {
      if (selectElement.options[i].value !== "-1") {
        selectElement.options[i].disabled = true;
      }
    }
  }

  function enableOptions(selectElement) {
    for (var i = 0; i < selectElement.options.length; i++) {
      selectElement.options[i].disabled = false;
    }
  }

  function save_newMotif()
  {
    var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
    //alert(MARCHE_PUBLIQUE);
    var statut = 2;
    if (DESCRIPTION_MOTIF == "") {
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
