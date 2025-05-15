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
                <div class="card-body">
                  <div class="row">
                    <div class="col-9" style="float: left;">
                      <h1 class="header-title text-dark">
                        <?=$etape_descr['DESC_ETAPE_DOUBLE_COMMANDE']?>/<?=lang('messages_lang.labelle_liquidation_partielle')?>
                      </h1>
                    </div>
                    <div class="col-3" style="float: right;">
                      <a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire')?>" style="float:right;margin-right:90px;margin:5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.link_list')?></a>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12" id="myTableData"></div>
                  </div>

                  <form enctype='multipart/form-data' id="my_form" action="<?= base_url('double_commande_new/Liquidation/add_partiel') ?>" method="POST">
                    <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                      <div class="row" style="margin :  5px">
                        <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">

                        <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">

                        <input type="hidden" name="MONTANT_RACCROCHE_JURIDIQUE_VALUE" id="MONTANT_RACCROCHE_JURIDIQUE_VALUE" value="<?=!empty($info['MONTANT_RACCROCHE_JURIDIQUE'])?$info['MONTANT_RACCROCHE_JURIDIQUE']:'0'?>">

                        <input type="hidden" name="MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE" id="MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE" value="<?=$info['MONTANT_RACCROCHE_JURIDIQUE_DEVISE']?>">

                        <input type="hidden" name="MONTANT_RACCROCHE_LIQUIDATION" id="MONTANT_RACCROCHE_LIQUIDATION" value="<?=!empty($info['MONTANT_RACCROCHE_LIQUIDATION'])?$info['MONTANT_RACCROCHE_LIQUIDATION']:'0'?>">

                        <input type="hidden" name="MONTANT_DEVISE_LIQUIDATION" id="MONTANT_DEVISE_LIQUIDATION" value="<?=!empty($info['MONTANT_RACCROCHE_LIQUIDATION_DEVISE'])?$info['MONTANT_RACCROCHE_LIQUIDATION_DEVISE']:'0'?>">

                        <input hidden="" type="date" name="DATE_DEBUT_CONTRAT" id="DATE_DEBUT_CONTRAT" value="<?=$info['DATE_DEBUT_CONTRAT']?>">

                        <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$info['MARCHE_PUBLIQUE']?>">


                        <div class="col-6">
                          <label><?=lang('messages_lang.label_type_liquidation')?><span style="color: red;">*</span></label>
                          <input type="text" value="<?=$get_type_liquidation['DESCRIPTION_LIQUIDATION']?>"  class="form-control" readonly>

                          <input type="hidden" value="<?=$get_type_liquidation['ID_TYPE_LIQUIDATION']?>" name="ID_TYPE_LIQUIDATION" id="ID_TYPE_LIQUIDATION" class="form-control">
                        </div>

                        <div class="col-6">
                          <label for=""><?=lang('messages_lang.labelle_date_reception_demande')?>(CED)<span style="color: red;">*</span></label>
                          <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_transRecept(this.value)">
                          <font color="red" id="error_DATE_RECEPTION"></font>
                        </div>

                        <div id="DIV_TITRE_CREANCE" class="col-6">
                          <label><?=lang('messages_lang.label_number_titre_creance')?><span style="color: red;">*</span></label>
                          <input onkeyup="SetMaxLength(1)" autocomplete="off" type="text" name="TITRE_CREANCE" id="TITRE_CREANCE" class="form-control" >
                          <font color="red" id="error_TITRE_CREANCE"></font>
                          <span style="font-size: 10px;color: green" id="getNumberTITRE_CREANCE"></span>
                        </div>

                        <div id="DIV_DATE_CREANCE" class="col-6">
                          <label><?=lang('messages_lang.label_date_titre_creance')?><span style="color: red;">*</span></label>
                          <input type="date" max="<?=date('Y-m-d')?>" name="DATE_CREANCE" id="DATE_CREANCE" class="form-control">
                          <font color="red" id="error_DATE_CREANCE"></font>
                        </div>

                        <div id="div_creance" class="col-6">
                          <label><?=lang('messages_lang.label_montant_titre_creance')?><span style="color: red;">*</span></label>
                          <input onkeyup="getSubstring(1);SetMaxLength(3);getCalculMontant()" autocomplete="off" type="text" name="MONTANT_CREANCE" id="MONTANT_CREANCE" class="form-control" >
                          <font color="red" id="error_MONTANT_CREANCE"></font>
                          <font color="red" id="error_MONTANT_CREANCE_SUP"></font>
                          <span style="font-size: 10px;color: green" id="getNumberMONTANT_CREANCE"></span>
                        </div>

                        <div id="DIV_DATE_LIQUIDATION" class="col-6">
                          <label><?=lang('messages_lang.table_ate_liq')?><span style="color: red;">*</span></label>
                          <input type="date" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" min="<?=date('Y-m-d',strtotime((!empty($info['DATE_ENG_BUDGETAIRE'])) ? $info['DATE_ENG_JURIDIQUE'] : ''))?>" max="<?=date('Y-m-d')?>" name="DATE_LIQUIDATION" id="DATE_LIQUIDATION" class="form-control" >
                          <font color="red" id="error_DATE_LIQUIDATION"></font>
                        </div>

                        <div class="col-12">
                          <label><?=lang('messages_lang.table_moif')?><span style="color: red;">*</span></label>
                          <textarea name="MOTIF_LIQUIDATION" id="MOTIF_LIQUIDATION" class="form-control"><?= !empty($info['COMMENTAIRE']) ? $info['COMMENTAIRE'] : '' ?></textarea>
                          <font color="red" id="error_MOTIF_LIQUIDATION"></font>
                        </div>

                        <input type="hidden" value="<?=$TYPE_MONTANT_ID?>" name="TYPE_MONTANT_ID" id="TYPE_MONTANT_ID">

                        <input type="hidden" value="<?=$get_taux['TAUX']?>" name="COUT_DEVISE" id="COUT_DEVISE">

                        <div id="div_devise1" class="col-6">
                          <label><?=lang('messages_lang.label_montant_devise')?> <span style="color: red;">*</span></label>
                          <input onkeyup="getSubstring(3);SetMaxLength(4);getCalculMontant()" autocomplete="off" type="text" name="MONTANT_DEVISE" id="MONTANT_DEVISE" class="form-control">
                          <font color="red" id="error_MONTANT_DEVISE"></font>
                          <font color="red" id="error_MONTANT_DEVISE_SUP"></font>
                          <span style="font-size: 10px;color: green" id="getNumberMONTANT_DEVISE"></span>
                        </div>

                        <div id="div_devise3" class="col-6">
                          <label><?=lang('messages_lang.label_montant_titre_creance')?><span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text" name="LIQUIDATION" id="LIQUIDATION" class="form-control" >
                          <font color="red" id="error_LIQUIDATION"></font>
                          <span style="font-size: 10px;color: green" id="getNumberLIQUIDATION"></span>
                        </div>

                        <div class="col-6">
                          <label><?=lang('messages_lang.label_taux_tva')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="TAUX_TVA_ID" id="TAUX_TVA_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach ($get_taux_tva as $key)
                            {
                              ?>
                              <option value="<?=$key->TAUX_TVA_ID?>"><?=$key->DESCRIPTION_TAUX_TVA?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <font  color="red" id="error_TAUX_TVA_ID"></font>
                        </div>

                        <div class="col-6">
                          <label><?=lang('messages_lang.table_exo')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="EXONERATION" id="EXONERATION">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <option value="1"><?=lang('messages_lang.label_oui')?></option>
                            <option value="0"><?=lang('messages_lang.label_non')?></option>
                          </select>
                          <font color="red" id="error_EXONERATION"></font>
                        </div>

                        <div class="col-6">
                          <label><?=lang('messages_lang.label_date_trans_conf')?>(CED)<span style="color: red;">*</span></label>
                          <input onkeypress="return false" onblur="this.type='date'" type="date" max="<?=date('Y-m-d')?>" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION" class="form-control">
                          <font color="red" id="error_DATE_TRANSMISSION"></font>
                        </div>

                        <div class="col-6">
                          <input type="hidden" name="nbrverification" id="nbrverification" value="<?=$nbrverification?>">
                          <label><?=lang('messages_lang.label_verification')?><span style="color: red;">*</span></label>
                          <select class="form-control select2" multiple name="TYPE_ANALYSE_ID[]" id="TYPE_ANALYSE_ID">
                            <?php
                            foreach ($get_verification as $key)
                            {
                              ?>
                              <option value="<?=$key->TYPE_ANALYSE_ID?>"><?=$key->DESC_TYPE_ANALYSE?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <font color="red" id="error_TYPE_ANALYSE_ID"></font>
                        </div>

                        <div class="col-12">
                          <label><?=lang('messages_lang.labelle_observartion')?></label>
                          <textarea name="OBSERVATION" id="OBSERVATION" class="form-control"></textarea>
                          <font color="red" id="error_OBSERVATION"></font>
                        </div>

                        <?php
                        if ($info['MARCHE_PUBLIQUE']==1)
                        {
                          ?>
                          <div class="col-6">
                            <label><?=lang('messages_lang.label_pv_reception')?><span style="color: red;">*</span></label>
                            <input onchange="ValidationFile(1);" accept=".pdf" type="file" name="PATH_PV_RECEPTION_LIQUIDATION" id="PATH_PV_RECEPTION_LIQUIDATION" class="form-control" >
                            <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION"></font>
                            <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION_VOLUMINEUX"></font>
                            <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION_FORMAT"></font>
                          </div>

                          <div class="col-6">
                            <label><?=lang('messages_lang.label_facture')?><span style="color: red;">*</span></label>
                            <input onchange="ValidationFile(2);" accept=".pdf" type="file" name="PATH_FACTURE_LIQUIDATION" id="PATH_FACTURE_LIQUIDATION" class="form-control" >
                            <font color="red" id="error_PATH_FACTURE_LIQUIDATION"></font>
                            <font color="red" id="error_PATH_FACTURE_LIQUIDATION_VOLUMINEUX"></font>
                            <font color="red" id="error_PATH_FACTURE_LIQUIDATION_FORMAT"></font>
                          </div>

                          <div class="col-6">
                            <label><?=lang('messages_lang.label_date_livraison')?><span style="color: red;">*</span></label>
                            <input type="date" onchange="getNbrJrs()" min="<?=date('Y-m-d',strtotime((!empty($info['DATE_DEBUT_CONTRAT'])) ? $info['DATE_DEBUT_CONTRAT'] : ''))?>" name="DATE_LIVRAISON_CONTRAT" id="DATE_LIVRAISON_CONTRAT" class="form-control" >
                            <font color="red" id="error_DATE_LIVRAISON_CONTRAT"></font><br>
                            <font color="red" id="error_DATE_FIN_LIVRAISON2"></font>
                          </div>
                          <?php
                        }
                        ?>
                      </div>
                    </div>

                    <div class="col-12" id="mont_sup">
                      <button style="float: right;" id="btnSave" type="button" onclick="send_data_partielle()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?> <span id="loading_btn"></span></button>
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

<script type="text/javascript">
  function send_data_partielle()
  {
    var statut = true;
    var ID_TYPE_LIQUIDATION = $('#ID_TYPE_LIQUIDATION').val();
    var TITRE_CREANCE = $('#TITRE_CREANCE').val();
    var DATE_CREANCE = $('#DATE_CREANCE').val();
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val();
    var LIQUIDATION = $('#LIQUIDATION').val();
    var DATE_LIQUIDATION = $('#DATE_LIQUIDATION').val();
    var MOTIF_LIQUIDATION = $('#MOTIF_LIQUIDATION').val();
    var TAUX_TVA_ID = $('#TAUX_TVA_ID').val();
    var EXONERATION = $('#EXONERATION').val();
    var OBSERVATION = $('#OBSERVATION').val();
    var TYPE_ANALYSE_ID = $('#TYPE_ANALYSE_ID').val();
    var nbrverification = $('#nbrverification').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val();
    var MONTANT_RACCROCHE_LIQUIDATION = $('#MONTANT_RACCROCHE_LIQUIDATION').val();
    var MONTANT_DEVISE_LIQUIDATION = $('#MONTANT_DEVISE_LIQUIDATION').val();

    var MONTANT_RACCROCHE_JURIDIQUE = $('#MONTANT_RACCROCHE_JURIDIQUE').val();
    var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $('#MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();

    var PATH_PV_RECEPTION_LIQUIDATION = $('#PATH_PV_RECEPTION_LIQUIDATION').val();
    var PATH_FACTURE_LIQUIDATION = $('#PATH_FACTURE_LIQUIDATION').val();
    var DATE_LIVRAISON_CONTRAT = $('#DATE_LIVRAISON_CONTRAT').val();

    if (DATE_RECEPTION == '')
    {
      $('#error_DATE_RECEPTION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_DATE_RECEPTION').text('');
    }

    if (TITRE_CREANCE == '')
    {
      $('#error_TITRE_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_TITRE_CREANCE').text('');
    }

    if (DATE_CREANCE == '')
    {
      $('#error_DATE_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_DATE_CREANCE').text('');
    }

    if (DATE_LIQUIDATION == '')
    {
      $('#error_DATE_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_DATE_LIQUIDATION').text('');
    }

    if (MOTIF_LIQUIDATION == '')
    {
      $('#error_MOTIF_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_MOTIF_LIQUIDATION').text('');
    }

    if (TAUX_TVA_ID == '')
    {
      $('#error_TAUX_TVA_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_TAUX_TVA_ID').text('');
    }

    if (EXONERATION == '')
    {
      $('#error_EXONERATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_EXONERATION').text('');
    }

    if (DATE_TRANSMISSION == '')
    {
      $('#error_DATE_TRANSMISSION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_DATE_TRANSMISSION').text('');
    }

    if (TYPE_ANALYSE_ID == '')
    {
      $('#error_TYPE_ANALYSE_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = false;
    }
    else
    {
      $('#error_TYPE_ANALYSE_ID').text('');
    }

    if (TYPE_MONTANT_ID != 1)
    {
      if (MONTANT_DEVISE == '')
      {
        $('#error_MONTANT_DEVISE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_MONTANT_DEVISE').text('');
      }

      if (LIQUIDATION == '')
      {
        $('#error_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_LIQUIDATION').text('');
      }

      var MONTANT_DEVISE_TOTAL = parseInt(MONTANT_DEVISE) + parseInt(MONTANT_DEVISE_LIQUIDATION);

      var MONTANT_DEVISE_RESTANT = parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE) - parseInt(MONTANT_DEVISE_LIQUIDATION);

      if (parseInt(MONTANT_DEVISE_TOTAL) > parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE))
      {
        $('#error_MONTANT_DEVISE_SUP').html("<?=lang('messages_lang.labelle_montant_devise_liquidation_pas_supeirieur_engag_juridik')?> (" + MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE + "), <?=lang('messages_lang.labelle_restant_engag_juridik')?> " + MONTANT_DEVISE_RESTANT);
        $('#LIQUIDATION').val('');
        $('#mont_sup').attr('hidden',true);

        statut = false;
      }
      else
      {
        $('#mont_sup').attr('hidden',false);
        $('#error_MONTANT_DEVISE_SUP').text('');
      }
    }
    else
    {
      if (MONTANT_CREANCE == '')
      {
        $('#error_MONTANT_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        // var MONTANT_CREANCE = MONTANT_CREANCE.replace(/\D/g, "");
        var MONTANT_CREANCE_TOTAL = parseInt(MONTANT_CREANCE) + parseInt(MONTANT_RACCROCHE_LIQUIDATION);

        var MONTANT_CREANCE_RESTANT = parseInt(MONTANT_RACCROCHE_JURIDIQUE) - parseInt(MONTANT_RACCROCHE_LIQUIDATION);

        if (parseInt(MONTANT_CREANCE_TOTAL) > parseInt(MONTANT_RACCROCHE_JURIDIQUE))
        {
          $('#error_MONTANT_CREANCE_SUP').text("<?=lang('messages_lang.labelle_montant_titre_creance_pas_supeirieur_engag_juridik')?> (" + MONTANT_RACCROCHE_JURIDIQUE + "), <?=lang('messages_lang.labelle_restant_engag_juridik')?> " + MONTANT_CREANCE_RESTANT);
          $('#mont_sup').attr('hidden',true);

          statut = false;
        }
        else
        {
        $('#mont_sup').attr('hidden',false);
          $('#error_MONTANT_CREANCE_SUP').text('');
        }
      }
    }

    if (MARCHE_PUBLIQUE == 1)
    {
      if (PATH_PV_RECEPTION_LIQUIDATION == '')
      {
        $('#error_PATH_PV_RECEPTION_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_PATH_PV_RECEPTION_LIQUIDATION').text('');
      }

      if (PATH_FACTURE_LIQUIDATION == '')
      {
        $('#error_PATH_FACTURE_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_PATH_FACTURE_LIQUIDATION').text('');
      }

      if (DATE_LIVRAISON_CONTRAT == '')
      {
        $('#error_DATE_LIVRAISON_CONTRAT').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_DATE_LIVRAISON_CONTRAT').text('');
      }
    }


    var DESC_TYPE_ANALYSE = $('#TYPE_ANALYSE_ID option:selected').toArray().map(item => item.text).join();

    var form = new FormData();

    if (MARCHE_PUBLIQUE == 1)
    {
      var PATH_PV_RECEPTION_LIQUIDATION = document.getElementById("PATH_PV_RECEPTION_LIQUIDATION").files[0];
      form.append("PATH_PV_RECEPTION_LIQUIDATION", PATH_PV_RECEPTION_LIQUIDATION);

      var PATH_FACTURE_LIQUIDATION = document.getElementById("PATH_FACTURE_LIQUIDATION").files[0];
      form.append("PATH_FACTURE_LIQUIDATION", PATH_FACTURE_LIQUIDATION);

      form.append("DATE_LIVRAISON_CONTRAT", DATE_LIVRAISON_CONTRAT);
    }

    form.append("TITRE_CREANCE", TITRE_CREANCE);
    form.append("DATE_CREANCE", DATE_CREANCE);
    form.append("MONTANT_CREANCE", MONTANT_CREANCE);
    form.append("LIQUIDATION", LIQUIDATION);
    form.append("DATE_LIQUIDATION", DATE_LIQUIDATION);
    form.append("MOTIF_LIQUIDATION", MOTIF_LIQUIDATION);
    form.append("TAUX_TVA_ID", TAUX_TVA_ID);
    form.append("EXONERATION", EXONERATION);
    form.append("OBSERVATION", OBSERVATION);
    form.append("DESC_TYPE_ANALYSE", DESC_TYPE_ANALYSE);
    form.append("DATE_TRANSMISSION", DATE_TRANSMISSION);
    form.append("DATE_RECEPTION", DATE_RECEPTION);
    form.append("TYPE_MONTANT_ID", TYPE_MONTANT_ID);
    form.append("MONTANT_DEVISE", MONTANT_DEVISE);
    form.append("ID_TYPE_LIQUIDATION", ID_TYPE_LIQUIDATION);
    form.append("MARCHE_PUBLIQUE", MARCHE_PUBLIQUE);

    if (statut)
    {
      $.ajax(
      {
        url: "<?=base_url('/double_commande_new/Liquidation/getInfoDetail')?>",
        type: "POST",
        dataType: "JSON",
        data: form,
        processData: false,
        contentType: false,
        beforeSend: function()
        {
          $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#disabled_btn').attr('disabled', true);
        },
        success: function(data)
        { 
          $('#detail_infos').modal('show'); // afficher bootstrap modal
          $('#infos_data').html(data.html);
          $('#loading_btn').html("");
          $('#disabled_btn').attr('disabled', false);
        }
      });
    }
  }

  function send_data2(argument)
  {
    document.getElementById("my_form").submit();
  }
</script>

<script type="text/javascript">
  function get_min_transRecept()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
  }
</script>

<script type="text/javascript">
  function ValidationFile(id)
  {
    var fileInput;
    var errorFormatId;
    var errorSizeId;

    if (id == 1)
    {
      fileInput = document.getElementById('PATH_PV_RECEPTION_LIQUIDATION');
      errorFormatId = '#error_PATH_PV_RECEPTION_LIQUIDATION_FORMAT';
      errorSizeId = '#error_PATH_PV_RECEPTION_LIQUIDATION_VOLUMINEUX';
    }
    else if (id == 2)
    {
      fileInput = document.getElementById('PATH_FACTURE_LIQUIDATION');
      errorFormatId = '#error_PATH_FACTURE_LIQUIDATION_FORMAT';
      errorSizeId = '#error_PATH_FACTURE_LIQUIDATION_VOLUMINEUX';
    }

    var filePath = fileInput.value;
    var allowedExtensions = /(\.pdf)$/i;

    if (!allowedExtensions.exec(filePath))
    {
      $(errorFormatId).text("<?=lang('messages_lang.error_message_pdf')?>");
      fileInput.value = '';
      return false;
    }
    else
    {
      if (fileInput.files.length > 0)
      {
        for (var i = 0; i <= fileInput.files.length - 1; i++)
        {
          var fsize = fileInput.files.item(i).size;
          var fileSize = Math.round(fsize / 1024);

          if (fileSize > 200)
          {
            $(errorSizeId).text('<?=lang('messages_lang.error_message_taille_pdf')?>');
            fileInput.value = '';
          }
          else
          {
            $(errorSizeId).text('');
          }
        }
      }
    }
  }
</script>

<script>
  function getNbrJrs()
  {
    var DATE_LIVRAISON_CONTRAT = $("#DATE_LIVRAISON_CONTRAT").val();
    var DATE_DEBUT_CONTRAT = $("#DATE_DEBUT_CONTRAT").val();

    if (DATE_DEBUT_CONTRAT != '' && DATE_LIVRAISON_CONTRAT != '')
    {
      if (DATE_LIVRAISON_CONTRAT < DATE_DEBUT_CONTRAT)
      {
        $("#error_DATE_FIN_LIVRAISON2").html("<?=lang('messages_lang.labelle_date_livraison_pas_posterieur_date_contrat')?>(" + DATE_DEBUT_CONTRAT + ")");
        $("#DATE_LIVRAISON_CONTRAT").val('');
      }
    }
  }
</script>

<script type="text/javascript">
  $(document).ready(function()
  {
    liste_historique_liquidation() 
  });

  function liste_historique_liquidation()
  {
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();

    $.ajax
    ({
      url: "<?=base_url('/double_commande_new/Liquidation/liste_historique_liquidation')?>",
      type: "POST",
      dataType: "JSON",
      data: {
        EXECUTION_BUDGETAIRE_ID: EXECUTION_BUDGETAIRE_ID,
        TYPE_MONTANT_ID: TYPE_MONTANT_ID
      },
      success: function(data) {
        $('#myTableData').html(data.tabledata);
      }
    });
  }
</script>

<script type="text/javascript">

  function get_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_LIQUIDATION").val());
  }

  $(document).ready(function()
  {
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();

    if (TYPE_MONTANT_ID != 1)
    {
      $('#div_devise1').attr('hidden', false);
      $('#div_devise3').attr('hidden', false);
      $('#div_creance').attr('hidden', true);

      $('#DIV_TITRE_CREANCE').addClass('col-4').removeClass('col-6');
      $('#DIV_DATE_CREANCE').addClass('col-4').removeClass('col-6');
      $('#DIV_DATE_LIQUIDATION').addClass('col-4').removeClass('col-6');

    }
    else if(TYPE_MONTANT_ID == 1)
    {
      $('#div_devise1').attr('hidden', true);
      $('#div_devise3').attr('hidden', true);
      $('#div_creance').attr('hidden', false);

    }

    $('#MONTANT_CREANCE, #LIQUIDATION, #MONTANT_DEVISE').bind('paste', function(e)
    {
      e.preventDefault();
    });

    document.getElementById('LIQUIDATION').readOnly = true;
    document.getElementById('ID_TYPE_LIQUIDATION').readOnly = true;
  });

  function getSubstring(id)
  {
    if (id == 1)
    {
      var MONTANT_CREANCE = $('#MONTANT_CREANCE').val();
      if (MONTANT_CREANCE.startsWith('0'))
      {
        $('#MONTANT_CREANCE').val('');
      }
    }
    else if (id == 3)
    {
      var MONTANT_DEVISE = $('#MONTANT_DEVISE').val();
      if (MONTANT_DEVISE.startsWith('0'))
      {
        $('#MONTANT_DEVISE').val('');
      }
    }
  }
</script>

<script type="text/javascript">
  $("#TITRE_CREANCE").on('input', function()
  {
    var maxLength = 30;
    $(this).val($(this).val().substring(0, maxLength).toUpperCase());
  });

  $("#MONTANT_CREANCE, #MONTANT_DEVISE").on('input', function()
  {
    var maxLength = 20;
    $(this).val($(this).val().substring(0, maxLength));
  });
</script>

<!-- Bonfils de Jésus -->
<script type="text/javascript">
  function SetMaxLength(id)
  {
    var TITRE_CREANCE = $('#TITRE_CREANCE').val().length;
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val().length;
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val().length;

    if (id == 1)
    {
      $('#getNumberTITRE_CREANCE').text("");
      if (TITRE_CREANCE != 0)
      {
        $('#getNumberTITRE_CREANCE').text(TITRE_CREANCE + "/30");
      }
    }
    else if (id == 3)
    {
      $('#getNumberMONTANT_CREANCE').text("");
      if (MONTANT_CREANCE != 0)
      {
        $('#getNumberMONTANT_CREANCE').text(MONTANT_CREANCE + "/20");
      }
    }
    else if (id == 4)
    {
      $('#getNumberMONTANT_DEVISE').text("");
      if (MONTANT_DEVISE != 0)
      {
        $('#getNumberMONTANT_DEVISE').text(MONTANT_DEVISE + "/20");
      }
    }
  }

  function getCalculMontant(argument)
  {
    var MONTANT_RACCROCHE_JURIDIQUE = $('#MONTANT_RACCROCHE_JURIDIQUE_VALUE').val();
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val();
    var LIQUIDATION = $('#LIQUIDATION').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
    var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $('#MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE').val();
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val();
    var COUT_DEVISE = $('#COUT_DEVISE').val();
    var MONTANT_RACCROCHE_LIQUIDATION = $('#MONTANT_RACCROCHE_LIQUIDATION').val();

    if (TYPE_MONTANT_ID != 1)
    {
      MONTANT_DEVISE = parseInt(MONTANT_DEVISE) + parseInt(MONTANT_DEVISE_LIQUIDATION);

      var MONTANT_DEVISE_RESTANT = parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE) - parseInt(MONTANT_DEVISE_LIQUIDATION);

      if (parseInt(MONTANT_DEVISE) > parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE))
      {
        $('#error_MONTANT_DEVISE_SUP').html("<?=lang('messages_lang.labelle_montant_devise_liquidation_pas_supeirieur_engag_juridik')?> (" + MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE + "), <?=lang('messages_lang.labelle_restant_engag_juridik')?> " + MONTANT_DEVISE_RESTANT);
        $('#LIQUIDATION').val('');
        $('#mont_sup').attr('hidden',true);

        return false;
      }
      else
      {
        $('#mont_sup').attr('hidden',false);
        $('#error_MONTANT_DEVISE_SUP').text('');

        var MONTANT_DEVISE = $('#MONTANT_DEVISE').val();
        
        var LIQUIDATION = parseFloat(MONTANT_DEVISE) * parseFloat(COUT_DEVISE);
        var value = LIQUIDATION.toFixed(0);

        $('#LIQUIDATION').val(value);
      }
    }
    else
    {
      MONTANT_CREANCE = parseInt(MONTANT_CREANCE) + parseInt(MONTANT_RACCROCHE_LIQUIDATION);

      var MONTANT_CREANCE_RESTANT = parseInt(MONTANT_RACCROCHE_JURIDIQUE) - parseInt(MONTANT_RACCROCHE_LIQUIDATION);

      if (parseInt(MONTANT_CREANCE) > parseInt(MONTANT_RACCROCHE_JURIDIQUE))
      {
        $('#error_MONTANT_CREANCE_SUP').text("<?=lang('messages_lang.labelle_montant_titre_creance_pas_supeirieur_engag_juridik')?> (" + MONTANT_RACCROCHE_JURIDIQUE + "), <?=lang('messages_lang.labelle_restant_engag_juridik')?> " + MONTANT_CREANCE_RESTANT);
        $('#mont_sup').attr('hidden',true);

        return false;
      }
      else
      {
        $('#mont_sup').attr('hidden',false);
        $('#error_MONTANT_CREANCE_SUP').text('');
      }
    }
  }
</script>

<script type="text/javascript">
  function deleteFile()
  {
    var GETDATA_DELETE = 0;;

    $.ajax(
    {
      url: "<?= base_url('/double_commande_new/Liquidation/deleteFile')?>",
      type: 'POST',
      dataType:'JSON',
      data:
      {
        GETDATA_DELETE:GETDATA_DELETE
      },
      beforeSend:function()
      {
        $('#loading_delete').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        if (data) 
        {                          
          $('#loading_delete').html("");
        }                       
      }
    });
  }
</script>

<div class="modal fade" id="detail_infos" data-backdrop="static">
  <div class="modal-dialog modal-lg" style="max-width: 60%">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?=lang('messages_lang.titre_modal')?></h5>
      </div>
      <div class="modal-body">
        <div id="infos_data"></div>
      </div>
      <div class="modal-footer">
        <button id="mod" onclick="deleteFile();hideButton()" class="btn btn-primary btn-md" data-dismiss="modal">
          <i class="fa fa-pencil"></i> <?=lang('messages_lang.labelle_mod')?>
          <span id="loading_delete"></span>
        </button>
        <button id="sedn" onclick="send_data2();hideButton()" type="button" class="btn btn-info">
          <i class="fa fa-check"></i> <?=lang('messages_lang.labelle_conf')?>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  function hideButton()
  {
    var element = document.getElementById("mod");
    element.style.display = "none";

    var elementmod = document.getElementById("sedn");
    elementmod.style.display = "none";
  }
</script>