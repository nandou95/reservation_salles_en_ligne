<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>

  <style>
    .vl
    {
      border-left: 1px solid #ddd;
      height: 250px;
      position: absolute;
      left: 100%;
      margin-left: -3px;
      top: 0;
    }
  </style>

  <style>
    .vl2
    {
      border-left: 1px solid #ddd;
      height: 185px;
      position: absolute;
      left: 100%;
      margin-left: -3px;
      top: 0;
    }
  </style>
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
              <div style="box-shadow:rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <h6 style="font-size: 18px" class="header-title text-black">
                    <?=lang('messages_lang.titre_transfert_alimentation')?>
                  </h6>

                  <div class="row">
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('transfert_new/Transfert_Incrementation/send_data/')?>" method="post">

                      <input type="hidden" value="<?=$exec_budg['EXECUTION_BUDGETAIRE_ID']?>" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID">
                      <!-- Ligne bidgetaire qui envoie -->
                      <div class="col-12">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin :  5px">
                            <div class="col-12">
                              <h4><center> <i class="fa fa-certificate"></i><?=lang('messages_lang.titre_ligne_budg_origine')?> </center></h4><br>
                            </div>

                            <div class="col-7">
                              <div class="row">
                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.table_institution')?> <span style="color: red">*</span></label>
                                  <select autofocus onchange="get_code();" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                    <?php
                                    foreach ($institution as $keyinstitution)
                                    {
                                      ?>
                                      <option value="<?=$keyinstitution->INSTITUTION_ID?>"><?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_INSTITUTION_ID"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_code_budgetaire')?> <span style="color: red">*</span> <span id="loading_code"></span></label>
                                  <select class="form-control select2" id="CODE_NOMENCLATURE_BUDGETAIRE" name="CODE_NOMENCLATURE_BUDGETAIRE" onchange="get_activitesByCode();" >
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                  </select>
                                  <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_activite')?> <span style="color: red">*</span> <span id="loading_activite"></span></label>
                                  <select class="form-control" id="PTBA_ID" name="PTBA_ID" onchange="get_MontantVoteByActivite();">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                  </select>
                                  <font color="red" id="error_PTBA_ID"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_tranche')?> <span style="color: red">*</span></label>
                                  <select onchange="getMontantAnnuel()" class="form-control" id="TRANCHE_ID" name="TRANCHE_ID">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                    <?php
                                    foreach ($tranches as $keytranches)
                                    {
                                      ?>
                                      <option value="<?=$keytranches->TRANCHE_ID?>"><?=$keytranches->DESCRIPTION_TRANCHE?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_TRANCHE_ID"></font>
                                  <font color="red" id="error_TRANCHE_ID2"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_observartion')?> <span style="color: red">*</span></label>
                                  <select class="form-control" id="OBSERVATION_FINANCIER_ID" name="OBSERVATION_FINANCIER_ID">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                    <?php
                                    foreach ($observation as $keyobservation)
                                    {
                                      ?>
                                      <option value="<?=$keyobservation->OBSERVATION_FINANCIER_ID?>"><?=$keyobservation->DESC_OBSERVATION_FINANCIER?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_OBSERVATION_FINANCIER_ID"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.mont_a_transf')?> <span style="color: red">*</span></label>
                                  <input onkeyup="get_MontantApresTransfert();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="MONTANT_TRANSFERT" id="MONTANT_TRANSFERT">
                                  <font color="red" id="error_MONTANT_TRANSFERT"></font>
                                  <font color="red" id="error_MONTANT_TRANSFERT_SUP"></font>
                                  <font color="red" id="error_MONTANT_TRANSFERT_SUP2"></font>
                                  <font color="red" id="error_MONTANT_TRANSFERT_SUP3"></font>
                                  <font color="red" id="error_MONTANT_TRANSFERT_SUP4"></font>
                                </div>

                                <input type="hidden" name="" id="total" name="total">
                                <span style="margin-top: 5px" class="vl"></span>
                              </div>
                            </div>

                            <div class="col-5">
                              <div class="row">
                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.titre_plafond_transfert')?></label>
                                  <input type="text" value="<?=$exec_budg['TRANSFERTS_CREDITS']?>" class="form-control" name="TRANSFERTS_CREDITS" id="TRANSFERTS_CREDITS">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.titre_plafond_transfert_restant')?></label>
                                  <input type="number" name="MONTANT_PLAFOND_APRES_TRANSFERT" id="MONTANT_PLAFOND_APRES_TRANSFERT" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label id="montant_vote_label" class="form-label"><?=lang('messages_lang.labelle_montant_vote')?> <span id="loading_montant"></span></label>
                                  <input type="number" name="MONTANT_VOTE" id="MONTANT_VOTE" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.mont_act_apr_trans')?></label>
                                  <input type="number" name="MONTANT_APRES_TRANSFERT1" id="MONTANT_APRES_TRANSFERT1" class="form-control">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Ligne bidgetaire qui recois -->
                      <br>
                      <div id="div_ligne_bubgetaire_recoit" class="col-12">
                        <div style="border:1px solid #ddd;border-radius:5px">
                          <div class="row" style="margin :  5px">
                            <div class="col-12">
                              <h4><center> <i class="fa fa-circle"></i><?=lang('messages_lang.titre_ligne_budg_receptrice')?> </center></h4><br>
                            </div>

                            <div class="col-7">
                              <div class="row">
                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.label_ligne')?></label>
                                  <input  type="text" value="<?=$exec_budg['IMPUTATION']?>" class="form-control" name="IMPUTATION" id="IMPUTATION">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.label_activite')?> <span style="color: red">*</span></label>
                                  <select class="form-control" id="ACTIVITES" name="ACTIVITES" onchange="get_MontantVoteByActivite2();">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                    <?php
                                    foreach ($activites as $keyactivites)
                                    {
                                      ?>
                                      <option value="<?=$keyactivites->PTBA_ID?>"><?=$keyactivites->ACTIVITES?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_ACTIVITES"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.montant_a_recevoir')?> <span style="color: red">*</span></label>
                                  <input  type="number" class="form-control" name="MONTANT_RECEVOIR" id="MONTANT_RECEVOIR">
                                  <font color="red" id="error_MONTANT_RECEVOIR"></font>
                                  <font color="red" id="error_MONTANT_RECEVOIR2"></font>
                                  <font color="red" id="error_MONTANT_RECEVOIR3"></font>
                                  <font color="red" id="error_MONTANT_RECEVOIR4"></font>
                                </div>

                                <input type="hidden" id="total2" name="total2">
                                <span style="margin-top: 5px" class="vl2"></span>
                              </div>
                            </div>

                            <div class="col-5">
                              <div class="row">
                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_montant_vote')?>  <span id="loading_montant2"></span></label>
                                  <input type="number"  class="form-control" id="MONTANT_VOTE2" name="MONTANT_VOTE2">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.titre_plafond_transfert_restant_reception')?></label>
                                  <input type="number" class="form-control" id="MONTANT_PLAFOND_APRES_TRANSFERT2" name="MONTANT_PLAFOND_APRES_TRANSFERT2">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.titre_montant_activite_apres_trans')?></label>
                                  <input type="number"  class="form-control" id="MONTANT_APRES_TRANSFERT" name="MONTANT_APRES_TRANSFERT">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Bouton send -->
                      <div class="col-12">
                        <div class="row">
                          <div class="col-4"></div>
                          <div class="col-4"><br>
                            <button  id="bouton_envoyer" onclick="addToCart()" type="button" class="btn btn-primary btn-block"><?=lang('messages_lang.bouton_ajouter')?> <span id="loading_cart"></span> <span id="message"></span></button>
                          </div>
                        </div>
                      </div>
                      <br>
                    </form>

                    <div class="col-12"  id="div_btnSendData" hidden="">
                      <div style="border:1px solid #ddd;border-radius:5px">
                        <div class="row" style="margin :  5px">
                          <div id="mycart" class="col-12 table-responsive"></div>
                          <br>
                          <div class="col-12">
                            <br>
                            <button  onclick="send_data()" type="button" class="btn btn-primary btn-block"><?=lang('messages_lang.bouton_enregistrer')?></button>
                          </div>
                        </div>
                      </div>
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
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  function get_code()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var url = "<?=base_url('/transfert_new/Transfert_Incrementation/get_code')?>";
    $.ajax(
    {
      url:url,
      type:"POST",
      dataType:"JSON",
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID,
      },
      beforeSend:function()
      {
        $('#loading_code').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#CODE_NOMENCLATURE_BUDGETAIRE').html(data.CODE_NOMENCLATURE_BUDGETAIRE);
        $('#loading_code').html("");
      }
    })
  }

  function get_activitesByCode()
  {
    var CODE_NOMENCLATURE_BUDGETAIRE=$('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Incrementation/get_activitesByCode')?>",
      type:"POST",
      dataType:"JSON",
      data:
      {
        CODE_NOMENCLATURE_BUDGETAIRE:CODE_NOMENCLATURE_BUDGETAIRE,
      },
      beforeSend:function()
      {
        $('#loading_activite').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#PTBA_ID').html(data.PTBA_ID);
        $('#loading_activite').html("");
      }
    })
  }

  function get_MontantVoteByActivite()
  {
    var PTBA_ID=$('#PTBA_ID').val();
    var ACTIVITES=$('#ACTIVITES').val();

    $('#TRANCHE_ID').val('');
    $('#MONTANT_TRANSFERT').val('');
    $('#MONTANT_TRANSFERT').attr('disabled',false);

    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Incrementation/get_MontantVoteByActivite')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        PTBA_ID:PTBA_ID,
      },
      beforeSend:function() {
        $('#loading_montant').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
        $('#loading_montant').html("");
        $('#montant_vote_label').text("Montant voté annuel");
      }
    })
  }

  function get_MontantVoteByActivite2()
  {
    var ACTIVITES=$('#ACTIVITES').val();

    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Incrementation/get_MontantVoteByActivite2')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        ACTIVITES:ACTIVITES,
      },
      beforeSend:function() {
        $('#loading_montant2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#MONTANT_VOTE2').val(data.MONTANT_VOTE);
        $('#loading_montant2').html("");
      }
    });
  }

  function get_MontantApresTransfert() {

    var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val();
    var MONTANT_VOTE=$('#MONTANT_VOTE').val();  

    if (MONTANT_TRANSFERT!='') {

      $('#error_MONTANT_TRANSFERT').text('');

      var getNumber = MONTANT_TRANSFERT.substring(0, 1);
      if (getNumber==0) {
        $('#MONTANT_TRANSFERT').val('');
      }else{
        if (parseInt(MONTANT_TRANSFERT)>parseInt(MONTANT_VOTE)) {
          $('#error_MONTANT_TRANSFERT_SUP').text("<?=lang('messages_lang.message_montant_super_montant_vote_origine')?>");
          $('#MONTANT_APRES_TRANSFERT').val('');
          $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
        }else{
          $('#error_MONTANT_TRANSFERT_SUP').text("");
          $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
        }
      }
    }else{
      $('#MONTANT_RECEVOIR').val('');
    }
  }

  function getMontantAnnuel(argument) {

    var PTBA_ID=$('#PTBA_ID').val();
    var TRANCHE_ID=$('#TRANCHE_ID').val();

        // if (TRANCHE_ID==5) {

    if (PTBA_ID=='') {
      $('#error_TRANCHE_ID2').text("<?=lang('messages_lang.message_selection_activite')?>");
      $('#TRANCHE_ID').val('')
    }else{

      $('#error_TRANCHE_ID2').text("");
      $('#error_MONTANT_TRANSFERT_SUP').text("");
      $.ajax(
      {
        url:"<?=base_url('/transfert_new/Transfert_Incrementation/getMontantAnnuel')?>",
        type:"POST",
        dataType:"JSON",
        data: {
          PTBA_ID:PTBA_ID,
          TRANCHE_ID:TRANCHE_ID
        },
        beforeSend:function() {
          $('#loading_montant_transfert').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          if (TRANCHE_ID==5) {
                  // $('#MONTANT_TRANSFERT').attr('disabled',true);
            document.getElementById('MONTANT_TRANSFERT').readOnly = true;
            $('#MONTANT_TRANSFERT').val(data.MONTANT_TRANSFERT);
            $('#MONTANT_RECEVOIR').val(data.MONTANT_TRANSFERT);
            $('#MONTANT_RECEVOIR').attr('disabled',true);
            $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
            $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote_annuel') ?>");
            $('#error_MONTANT_TRANSFERT_SUP2').text("");
          }else{
            if (TRANCHE_ID==1) {
              var DESC_TRANCHE = 'première';
            }else if (TRANCHE_ID==2) {
              var DESC_TRANCHE = 'deuxième';
            }else if (TRANCHE_ID==3) {
              var DESC_TRANCHE = 'troisième';
            }else if (TRANCHE_ID==4) {
              var DESC_TRANCHE = 'quatrième';
            }
            $('#MONTANT_TRANSFERT').val('');
            $('#error_MONTANT_TRANSFERT_SUP2').text("");
                  // $('#MONTANT_TRANSFERT').attr('disabled',false);
            document.getElementById('MONTANT_TRANSFERT').readOnly = false;
            $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
            $('#MONTANT_RECEVOIR').val('');
            $('#MONTANT_RECEVOIR').attr('disabled',false);
            $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote') ?> "+DESC_TRANCHE+" <?= lang('messages_lang.trim') ?>");
          }
          $('#loading_montant_transfert').html("");
        }
      })
    }
  }

  $("#MONTANT_TRANSFERT").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#MONTANT_RECEVOIR").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

</script>


<script type="text/javascript">
  $(document).ready(function() {

    liste_tempo();

    $('#MONTANT_TRANSFERT').bind('paste', function (e) {
       e.preventDefault();
     });

      $('#MONTANT_RECEVOIR').bind('paste', function (e) {
       e.preventDefault();
     });

    document.getElementById('TRANSFERTS_CREDITS').readOnly = true;
    document.getElementById('MONTANT_PLAFOND_APRES_TRANSFERT').readOnly = true;
    document.getElementById('MONTANT_VOTE').readOnly = true;
    document.getElementById('MONTANT_APRES_TRANSFERT1').readOnly = true;
    document.getElementById('IMPUTATION').readOnly = true;
    document.getElementById('MONTANT_RECEVOIR').readOnly = true;
    document.getElementById('MONTANT_VOTE2').readOnly = true;
    document.getElementById('MONTANT_PLAFOND_APRES_TRANSFERT2').readOnly = true;
    document.getElementById('MONTANT_APRES_TRANSFERT').readOnly = true;
    document.getElementById('MONTANT_TRANSFERT').readOnly = true;

  });
</script>

<script type="text/javascript">
  function liste_tempo() {

    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val()
    var TRANSFERTS_CREDITS = $('#TRANSFERTS_CREDITS').val()
    var MONTANT_VOTE = $('#MONTANT_VOTE').val()
    var MONTANT_VOTE2 = $('#MONTANT_VOTE2').val()

    $.ajax(
    { 
      url: '<?=base_url('/transfert_new/Transfert_Incrementation/liste_tempo')?>',
      type:"POST",
      dataType:"JSON",
      data: { 
        EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
        TRANSFERTS_CREDITS:TRANSFERTS_CREDITS,
        MONTANT_VOTE:MONTANT_VOTE,
        MONTANT_VOTE2:MONTANT_VOTE2
      },
      beforeSend: function() {
            //$('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {
        $('#mycart').html(data.html);
        $('#total').val(data.total);
        $('#MONTANT_PLAFOND_APRES_TRANSFERT').val(data.MONTANT_PLAFOND_APRES_TRANSFERT);
        $('#MONTANT_APRES_TRANSFERT1').val(data.MONTANT_APRES_TRANSFERT1);

        $('#total2').val(data.total2);
        $('#MONTANT_APRES_TRANSFERT').val(data.MONTANT_APRES_TRANSFERT);
        $('#MONTANT_PLAFOND_APRES_TRANSFERT2').val(data.MONTANT_PLAFOND_APRES_TRANSFERT2);

        if (data.status==1) {
          $('#div_btnSendData').attr('hidden',false);
        }else{
          $('#div_btnSendData').attr('hidden',true);
          $('#mycart').html('');
        }
      }
    });
  }
</script>

<script type="text/javascript">

  function addToCart()
  {
  
    var statut = true;

    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE = $('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    var PTBA_ID = $('#PTBA_ID').val();
    var MONTANT_TRANSFERT = $('#MONTANT_TRANSFERT').val();
    var MONTANT_VOTE = $('#MONTANT_VOTE').val();
    var TRANCHE_ID = $('#TRANCHE_ID').val();
    var OBSERVATION_FINANCIER_ID = $('#OBSERVATION_FINANCIER_ID').val();

    // Ligne receptrice
    var ACTIVITES = $('#ACTIVITES').val(); // Activité de la ligne
    var MONTANT_RECEVOIR = $('#MONTANT_RECEVOIR').val();
    var MONTANT_VOTE2 = $('#MONTANT_VOTE2').val(); // Montant voté pour la ligne receptrice

    var TRANSFERTS_CREDITS = $('#TRANSFERTS_CREDITS').val();
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();

    if (INSTITUTION_ID == '') {
      $('#error_INSTITUTION_ID').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_INSTITUTION_ID').text('');
    }

    if (CODE_NOMENCLATURE_BUDGETAIRE == '') {
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').text('');
    }

    if (PTBA_ID == '') {
      $('#error_PTBA_ID').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_PTBA_ID').text('');
    }

    if (OBSERVATION_FINANCIER_ID == '') {
      $('#error_OBSERVATION_FINANCIER_ID').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_OBSERVATION_FINANCIER_ID').text('');
    }

    if (MONTANT_TRANSFERT == '') {
      $('#error_MONTANT_TRANSFERT').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_MONTANT_TRANSFERT').text('');
    }

    if (TRANCHE_ID == '') {
      $('#error_TRANCHE_ID').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_TRANCHE_ID').text('');
    }

    if (ACTIVITES == '') {
      $('#error_ACTIVITES').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_ACTIVITES').text('');
    }

    if (MONTANT_RECEVOIR == '') {
      $('#error_MONTANT_RECEVOIR').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    } else {
      $('#error_MONTANT_RECEVOIR').text('');
    }

    if (parseInt(MONTANT_TRANSFERT) > parseInt(MONTANT_VOTE)) {
      $('#error_MONTANT_TRANSFERT_SUP').text("<?=lang('messages_lang.message_montant_super_montant_vote_origine')?>");
      return false;
    } else {
      $('#error_MONTANT_TRANSFERT_SUP').text('');
    }

    if (parseInt(MONTANT_TRANSFERT) > parseInt(TRANSFERTS_CREDITS)) {
      $('#error_MONTANT_TRANSFERT_SUP2').text("<?=lang('messages_lang.message_montant_plafond_superieur_transfert')?>");
      return false;
    } else {
      $('#error_MONTANT_TRANSFERT_SUP2').text('');
    }

    var total = $('#total').val();
    var calcul = parseInt(total) + parseInt(MONTANT_TRANSFERT);
    if (parseInt(calcul) > parseInt(TRANSFERTS_CREDITS)) {
      $('#error_MONTANT_TRANSFERT_SUP3').text("<?=lang('messages_lang.message_montant_plafond_superieur_transfert')?>");
      return false;
    } else {
      $('#error_MONTANT_TRANSFERT_SUP3').text('');
    }

    if (parseInt(MONTANT_TRANSFERT) < 1) {
      $('#error_MONTANT_TRANSFERT_SUP4').text("<?=lang('messages_lang.message_montant_inferieur_1')?>");
      return false;
    } else {
      $('#error_MONTANT_TRANSFERT_SUP4').text('');
    }

    /////////////////////////////////////////////////////////////////////
    if (parseInt(MONTANT_RECEVOIR) > parseInt(TRANSFERTS_CREDITS)) {
      $('#error_MONTANT_RECEVOIR2').text("<?=lang('messages_lang.message_montant_recevoir_superieur_plafond')?>");
      return false;
    } else {
      $('#error_MONTANT_RECEVOIR2').text('');
    }

    if (parseInt(MONTANT_RECEVOIR) < 1) {
      $('#error_MONTANT_RECEVOIR_SUP4').text("<?=lang('messages_lang.message_montant_recevoir_inferieur_1')?>");
      return false;
    } else {
      $('#error_MONTANT_RECEVOIR_SUP4').text('');
    }

    var total2 = $('#total2').val();
    var calcul2 = parseInt(total2) + parseInt(MONTANT_RECEVOIR);
    if (parseInt(calcul2) > parseInt(TRANSFERTS_CREDITS)) {
     $('#error_MONTANT_RECEVOIR3').text("<?=lang('messages_lang.message_montant_total_recevoir_superieur_plafond')?>");
      return false;
    } else {
      $('#error_MONTANT_RECEVOIR3').text('');
    }

    if (statut == true) {
      $.ajax({
        url: '<?=base_url('/transfert_new/Transfert_Incrementation/addtocart')?>',
        type: 'POST',
        dataType: 'JSON',
        data: { 
          INSTITUTION_ID: INSTITUTION_ID,
          CODE_NOMENCLATURE_BUDGETAIRE: CODE_NOMENCLATURE_BUDGETAIRE,
          PTBA_ID: PTBA_ID,
          MONTANT_TRANSFERT: MONTANT_TRANSFERT,
          TRANSFERTS_CREDITS: TRANSFERTS_CREDITS,
          MONTANT_VOTE: MONTANT_VOTE,
          ACTIVITES: ACTIVITES,
          MONTANT_RECEVOIR: MONTANT_RECEVOIR,
          MONTANT_VOTE2: MONTANT_VOTE2,
          TRANCHE_ID: TRANCHE_ID,
          EXECUTION_BUDGETAIRE_ID: EXECUTION_BUDGETAIRE_ID,
          OBSERVATION_FINANCIER_ID: OBSERVATION_FINANCIER_ID
        },
        beforeSend: function() {
          $('#loading_cart').html('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');
          $('#bouton_envoyer').attr('disabled', true);
        },
        success: function(data) {
          liste_tempo();
          setTimeout(() => {
            $('#message').html('<i class="fa fa-check"></i>');
            window.location.reload();
            $('#MONTANT_TRANSFERT').val('');
            $('#MONTANT_RECEVOIR').val('');
            $('#TRANCHE_ID').val('');
            $('#OBSERVATION_FINANCIER_ID').val('');
            $('#loading_cart').html('');
            $('#bouton_envoyer').attr('disabled', false);
          }, 3000);
        }
      });
    }

  }

          function removeToCart(TEMPO_TRANSFERT_RECEPTION_ID)
          {
            var TEMPO_TRANSFERT_RECEPTION_ID = TEMPO_TRANSFERT_RECEPTION_ID
            $.ajax(
            { 
              url: '<?=base_url('/transfert_new/Transfert_Incrementation/removeToCart')?>',
              type:"POST",
              dataType:"JSON",
              data: { 
                TEMPO_TRANSFERT_RECEPTION_ID:TEMPO_TRANSFERT_RECEPTION_ID,
              },
              beforeSend: function() {
                $('#loading_delete'+TEMPO_TRANSFERT_RECEPTION_ID+'').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
              },
              success:function(data)
              {
                liste_tempo()
                setTimeout(()=>{
                  $('#message'+TEMPO_TRANSFERT_RECEPTION_ID+'').html('<i class="fa fa-check"></i>');
                  $('#loading_delete').html("");
                  liste_tempo()
                  window.location.reload();
                },3000); 
              }
            });
          }

        </script>

        <script type="text/javascript">
          function send_data(argument) {

            $('#MyFormData').submit();

          }
        </script>

