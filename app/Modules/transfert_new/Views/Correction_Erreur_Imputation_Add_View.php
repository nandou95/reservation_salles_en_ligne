<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>

  <style>
    .vl {
      border-left: 1px solid #ddd;
      height: 445px;
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
                    <?=lang('messages_lang.titre_correction_erreur_imputation')?>
                    <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="alert alert-success" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                      <?php
                    }
                    ?>
                  </h6>

                  <div class="row">
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('transfert_new/Correction_Erreur_Imputation/send_data/')?>" method="post" >
                      <!-- Ligne bidgetaire qui envoie -->
                      <div class="col-12">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin : 5px">
                            <div class="col-7">
                              <div class="row">
                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_ligne_exec')?><span style="color: red">*</span></label>
                                  <select autofocus onchange="getMontantRecevoirByEtatExecution();" class="form-control select2" id="IMPUTATION" name="IMPUTATION">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                    <?php
                                    foreach ($exec_budg as $keyexec_budg)
                                    {
                                      ?>
                                      <option value="<?=$keyexec_budg->EXECUTION_BUDGETAIRE_ID?>"><?=$keyexec_budg->IMPUTATION?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_IMPUTATION"></font>
                                  <font color="red" id="error_IMPUTATION2"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_ligne_ptba')?><span style="color: red">*</span> <span id="loading_ptba"></span></label>
                                  <select onchange="getLibelle()" class="form-control select2" id="CODE_NOMENCLATURE_BUDGETAIRE" name="CODE_NOMENCLATURE_BUDGETAIRE">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                  </select>
                                  <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_eng_budget')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="ENG_BUDGETAIRE1" id="ENG_BUDGETAIRE1">
                                  <font color="red" id="error_ENG_BUDGETAIRE"></font>
                                  <font color="red" id="error_ENG_BUDGETAIRE2"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_eng_jud')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="ENG_JURIDIQUE1" id="ENG_JURIDIQUE1">
                                  <font color="red" id="error_ENG_JURIDIQUE"></font>
                                  <font color="red" id="error_ENG_JURIDIQUE2"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_liquidation')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="LIQUIDATION1" id="LIQUIDATION1">
                                  <font color="red" id="error_LIQUIDATION"></font>
                                  <font color="red" id="error_LIQUIDATION2"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_ordonan')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="ORDONNANCEMENT1" id="ORDONNANCEMENT1">
                                  <font color="red" id="error_ORDONNANCEMENT"></font>
                                  <font color="red" id="error_ORDONNANCEMENT2"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_paiement')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="PAIEMENT1" id="PAIEMENT1">
                                  <font color="red" id="error_PAIEMENT"></font>
                                  <font color="red" id="error_PAIEMENT2"></font>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=leng('messages_lang.labelle_decaisse')?> <span style="color: red">*</span></label>
                                  <input onkeyup="getMontantRestant();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="DECAISSEMENT1" id="DECAISSEMENT1">
                                  <font color="red" id="error_DECAISSEMENT"></font>
                                  <font color="red" id="error_DECAISSEMENT2"></font>
                                </div>

                                <input type="hidden" name="DATA_LABELLE" id="DATA_LABELLE">
                                <div id="div_libelle" class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_libelle')?> <span style="color: red">*</span></label>
                                  <input type="text" name="LIBELLE" id="LIBELLE" class="form-control">
                                  <font color="red" id="error_LIBELLE"></font>
                                </div>

                                <span style="margin-top: 5px" class="vl"></span>
                              </div>
                            </div>

                            <div class="col-5">
                              <div class="row">
                                <div class="col-12">
                                  <h4><center> <i class="fa fa-"></i> <?=lang('messages_lang.labelle_montant_restant_ligne_exec')?> </center></h4>
                                  <br>
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_eng_budget')?> <span id="loading_montant1"></span></label>
                                  <input type="number" name="ENG_BUDGETAIRE" id="ENG_BUDGETAIRE" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_eng_jud')?><span id="loading_montant2"></span></label>
                                  <input type="number" name="ENG_JURIDIQUE" id="ENG_JURIDIQUE" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_liquidation')?> <span id="loading_montant3"></span></label>
                                  <input type="number" name="LIQUIDATION" id="LIQUIDATION" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_ordonan')?> <span id="loading_montant4"></span></label>
                                  <input type="number" name="ORDONNANCEMENT" id="ORDONNANCEMENT" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_paiement')?> <span id="loading_montant5"></span></label>
                                  <input type="number" name="PAIEMENT" id="PAIEMENT" class="form-control">
                                </div>

                                <div class="col-12">
                                  <label class="form-label"><?=lang('messages_lang.labelle_decaisse')?> <span id="loading_montant6"></span></label>
                                  <input type="number" name="DECAISSEMENT" id="DECAISSEMENT" class="form-control">
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
                          <div class="col-4">
                            <br>
                            <button  id="bouton_envoyer" onclick="addToCart()" type="button" class="btn btn-primary btn-block"><?=lang('messages_lang.bouton_ajouter')?> <span id="loading_cart"></span> <span id="message_btn"></span></button>
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
  function getMontantRecevoirByEtatExecution()
  {
    var IMPUTATION=$('#IMPUTATION').val();
    if(IMPUTATION!='')
    {
      $('#error_IMPUTATION2').html("");
    }

    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Correction_Erreur_Imputation/getMontantRecevoirByEtatExecution')?>",
      type:"POST",
      dataType:"JSON",
      data:
      {
        IMPUTATION:IMPUTATION,
      },
      beforeSend:function()
      {
        $('#loading_montant1').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_montant2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_montant3').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_montant4').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_montant5').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_montant6').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_ptba').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#ENG_BUDGETAIRE').val(data.ENG_BUDGETAIRE);
        $('#ENG_JURIDIQUE').val(data.ENG_JURIDIQUE);
        $('#LIQUIDATION').val(data.LIQUIDATION);
        $('#ORDONNANCEMENT').val(data.ORDONNANCEMENT);
        $('#PAIEMENT').val(data.PAIEMENT);
        $('#DECAISSEMENT').val(data.DECAISSEMENT);
        $('#CODE_NOMENCLATURE_BUDGETAIRE').html(data.CODE_NOMENCLATURE_BUDGETAIRE);

        $('#loading_montant1').html("");
        $('#loading_montant2').html("");
        $('#loading_montant3').html("");
        $('#loading_montant4').html("");
        $('#loading_montant5').html("");
        $('#loading_montant6').html("");
        $('#loading_ptba').html("");
      }
    });
  }


  function getMontantRestant()
  {
    var IMPUTATION=$('#IMPUTATION').val();
    var ENG_BUDGETAIRE = $('#ENG_BUDGETAIRE').val();
    var ENG_JURIDIQUE = $('#ENG_JURIDIQUE').val();
    var LIQUIDATION = $('#LIQUIDATION').val();
    var ORDONNANCEMENT = $('#ORDONNANCEMENT').val();
    var PAIEMENT = $('#PAIEMENT').val();
    var DECAISSEMENT = $('#DECAISSEMENT').val();

    var ENG_BUDGETAIRE1 = $('#ENG_BUDGETAIRE1').val();
    var ENG_JURIDIQUE1 = $('#ENG_JURIDIQUE1').val();
    var LIQUIDATION1 = $('#LIQUIDATION1').val();
    var ORDONNANCEMENT1 = $('#ORDONNANCEMENT1').val();
    var PAIEMENT1 = $('#PAIEMENT1').val();
    var DECAISSEMENT1 = $('#DECAISSEMENT1').val();

    if (IMPUTATION=="")
    {
      $('#error_IMPUTATION2').text("<?=lang('messages_lang.message_selection_ligne_budg_exec')?>");
      $('#ENG_BUDGETAIRE1').val('');
      $('#ENG_JURIDIQUE1').val('');
      $('#LIQUIDATION1').val('');
      $('#ORDONNANCEMENT1').val('');
      $('#PAIEMENT1').val('');
      $('#DECAISSEMENT1').val('');
    }
    else
    {
      $('#error_IMPUTATION2').text("");
      if(ENG_BUDGETAIRE1!='')
      {
        var getNumber = ENG_BUDGETAIRE1.substring(0, 1);
        if(getNumber==0)
        {
          $('#ENG_BUDGETAIRE1').val('');
        }
        else
        {
          if(parseInt(ENG_BUDGETAIRE1) > parseInt(ENG_BUDGETAIRE))
          {
            $('#error_ENG_BUDGETAIRE2').text("<?=lang('messages_lang.message_montant_super_eng_budg')?>");
          }
          else
          {
            $('#error_ENG_BUDGETAIRE2').text("");
          }
        }
        $('#error_ENG_BUDGETAIRE').text("");
      }
      else
      {
        $('#error_ENG_BUDGETAIRE').text("<?=lang('messages_lang.validation_message')?>");
      }

      if(ENG_JURIDIQUE1!='')
      {
        var getNumber = ENG_JURIDIQUE1.substring(0, 1);
        if(getNumber==0)
        {
          $('#ENG_JURIDIQUE1').val('');
        }
        else
        {
          if(parseInt(ENG_JURIDIQUE1) > parseInt(ENG_JURIDIQUE))
          {
            $('#error_ENG_JURIDIQUE2').text("<?=lang('messages_lang.message_montant_super_eng_juridik')?>");
          }
          else
          {
            $('#error_ENG_JURIDIQUE2').text("");
          }
        }
        $('#error_ENG_JURIDIQUE').text("");
      }
      else
      {
        $('#error_ENG_JURIDIQUE').text("<?=lang('messages_lang.validation_message')?>");
      }

      if(LIQUIDATION1!='')
      {
        var getNumber = LIQUIDATION1.substring(0, 1);
        if(getNumber==0)
        {
          $('#LIQUIDATION1').val('');
        }
        else
        {
          if(parseInt(LIQUIDATION1) > parseInt(LIQUIDATION))
          {
            $('#error_LIQUIDATION2').text("<?=lang('messages_lang.message_montant_super_liquidation')?>");
          }
          else
          {
            $('#error_LIQUIDATION2').text("");
          }
        }
        $('#error_LIQUIDATION').text("");
      }
      else
      {
        $('#error_LIQUIDATION').text("<?=lang('messages_lang.validation_message')?>");
      }

      if(ORDONNANCEMENT1!='')
      {
        var getNumber = ORDONNANCEMENT1.substring(0, 1);
        if(getNumber==0)
        {
          $('#ORDONNANCEMENT1').val('');
        }
        else
        {
          if(parseInt(ORDONNANCEMENT1) > parseInt(ORDONNANCEMENT))
          {
            $('#error_ORDONNANCEMENT2').text("<?=lang('messages_lang.message_montant_super_ordonn')?>");
          }
          else
          {
            $('#error_ORDONNANCEMENT2').text("");
          }
        }
        $('#error_ORDONNANCEMENT').text("");
      }
      else
      {
        $('#error_ORDONNANCEMENT').text("<?=lang('messages_lang.validation_message')?>");
      }

      if(PAIEMENT1!='')
      {
        var getNumber = PAIEMENT1.substring(0, 1);
        if(getNumber==0)
        {
          $('#PAIEMENT1').val('');
        }
        else
        {
          if(parseInt(PAIEMENT1) > parseInt(PAIEMENT))
          {
            $('#error_PAIEMENT2').text("<?=lang('messages_lang.message_montant_super_paiement')?>");
          }
          else
          {
            $('#error_PAIEMENT2').text("");
          }
        }
        $('#error_PAIEMENT').text("");
      }
      else
      {
        $('#error_PAIEMENT').text("<?=lang('messages_lang.validation_message')?>");
      }

      if(DECAISSEMENT1!='')
      {
        var getNumber = DECAISSEMENT1.substring(0, 1);
        if(getNumber==0)
        {
          $('#DECAISSEMENT1').val('');
        }
        else
        {
          if(parseInt(DECAISSEMENT1) > parseInt(DECAISSEMENT))
          {
            $('#error_DECAISSEMENT2').text("<?=lang('messages_lang.message_montant_super_decaiss')?>");
          }
          else
          {
            $('#error_DECAISSEMENT2').text("");
          }
        }
        $('#error_DECAISSEMENT').text("");
      }
      else
      {
        $('#error_DECAISSEMENT').text("<?=lang('messages_lang.validation_message')?>");
      }
    }
  }

  function getLibelle()
  {
    var CODE_NOMENCLATURE_BUDGETAIRE=$('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Correction_Erreur_Imputation/getLibelle')?>",
      type:"POST",
      dataType:"JSON",
      data:
      {
        CODE_NOMENCLATURE_BUDGETAIRE:CODE_NOMENCLATURE_BUDGETAIRE,
      },
      beforeSend:function()
      {
      },
      success: function(data)
      {
        if(data.status==0)
        {
          $('#div_libelle').attr('hidden',false);
          $('#DATA_LABELLE').val(0);
        }
        else
        {
          $('#div_libelle').attr('hidden',true);
          $('#DATA_LABELLE').val(1);
        }
      }
    });
  }

  $("#ENG_BUDGETAIRE1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#ENG_JURIDIQUE1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#LIQUIDATION1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#ORDONNANCEMENT1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#PAIEMENT1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  $("#DECAISSEMENT1").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });
</script>

<script type="text/javascript">
  $(document).ready(function()
  {
    liste_tempo();

    $('#ENG_BUDGETAIRE1').bind('paste', function (e) {
      e.preventDefault();
    });

    $('#ENG_JURIDIQUE1').bind('paste', function (e) {
      e.preventDefault();
    });

    $('#LIQUIDATION1').bind('paste', function (e) {
      e.preventDefault();
    });

    $('#ORDONNANCEMENT1').bind('paste', function (e) {
      e.preventDefault();
    });

    $('#PAIEMENT1').bind('paste', function (e) {
      e.preventDefault();
    });

    $('#DECAISSEMENT1').bind('paste', function (e) {
      e.preventDefault();
    });

    document.getElementById('ENG_BUDGETAIRE').readOnly = true;
    document.getElementById('ENG_JURIDIQUE').readOnly = true;
    document.getElementById('LIQUIDATION').readOnly = true;
    document.getElementById('ORDONNANCEMENT').readOnly = true;
    document.getElementById('PAIEMENT').readOnly = true;
    document.getElementById('DECAISSEMENT').readOnly = true;
    $("#message").delay(5000).hide('slow');
  });
</script>

<script type="text/javascript">
  function liste_tempo()
  {
    $.ajax(
    { 
      url: '<?=base_url('/transfert_new/Correction_Erreur_Imputation/liste_tempo')?>',
      type:"POST",
      dataType:"JSON",
      data: {},
      beforeSend: function()
      {
      },
      success:function(data)
      {
        $('#mycart').html(data.html);
        if(data.status==1)
        {
          $('#div_btnSendData').attr('hidden',false);
        }
        else
        {
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
    var IMPUTATION=$('#IMPUTATION').val()
    var CODE_NOMENCLATURE_BUDGETAIRE=$('#CODE_NOMENCLATURE_BUDGETAIRE').val()

    var ENG_BUDGETAIRE = $('#ENG_BUDGETAIRE').val();
    var ENG_JURIDIQUE = $('#ENG_JURIDIQUE').val();
    var LIQUIDATION = $('#LIQUIDATION').val();
    var ORDONNANCEMENT = $('#ORDONNANCEMENT').val();
    var PAIEMENT = $('#PAIEMENT').val();
    var DECAISSEMENT = $('#DECAISSEMENT').val();

    var ENG_BUDGETAIRE1 = $('#ENG_BUDGETAIRE1').val();
    var ENG_JURIDIQUE1 = $('#ENG_JURIDIQUE1').val();
    var LIQUIDATION1 = $('#LIQUIDATION1').val();
    var ORDONNANCEMENT1 = $('#ORDONNANCEMENT1').val();
    var PAIEMENT1 = $('#PAIEMENT1').val();
    var DECAISSEMENT1 = $('#DECAISSEMENT1').val();
    var LIBELLE = $('#LIBELLE').val();
    var DATA_LABELLE = $('#DATA_LABELLE').val();

    if(IMPUTATION=='') 
    {
      $('#error_IMPUTATION').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    }
    else
    {
      $('#error_IMPUTATION').text('');
    }

    if(CODE_NOMENCLATURE_BUDGETAIRE=='') 
    {
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').text('<?=lang('messages_lang.validation_message')?>');
      return false;
    }
    else
    {
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').text('');
    }

    if(ENG_BUDGETAIRE1!='')
    {
      if(parseInt(ENG_BUDGETAIRE1) > parseInt(ENG_BUDGETAIRE))
      {
        $('#error_ENG_BUDGETAIRE2').text("<?=lang('messages_lang.message_montant_super_eng_budg')?>")
        return false;
      }
      $('#error_ENG_BUDGETAIRE').text("");
    }
    else
    {
      $('#error_ENG_BUDGETAIRE').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(ENG_JURIDIQUE1!='')
    {
      if(parseInt(ENG_JURIDIQUE1) > parseInt(ENG_JURIDIQUE))
      {
        $('#error_ENG_JURIDIQUE2').text("<?=lang('messages_lang.message_montant_super_eng_juridik')?>")
        return false;
      }
      $('#error_ENG_JURIDIQUE').text("");
    }
    else
    {
      $('#error_ENG_JURIDIQUE').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(LIQUIDATION1!='')
    {
      if(parseInt(LIQUIDATION1)>parseInt(LIQUIDATION))
      {
        $('#error_LIQUIDATION2').text("<?=lang('messages_lang.message_montant_super_liquidation')?>")
        return false;
      }
      $('#error_LIQUIDATION').text("");
    }
    else
    {
      $('#error_LIQUIDATION').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(ORDONNANCEMENT1!='')
    {
      if(parseInt(ORDONNANCEMENT1) > parseInt(ORDONNANCEMENT))
      {
        $('#error_ORDONNANCEMENT2').text("<?=lang('messages_lang.message_montant_super_ordonn')?>");
        return false;
      }
      $('#error_ORDONNANCEMENT').text("");
    }
    else
    {
      $('#error_ORDONNANCEMENT').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(PAIEMENT1!='')
    {
      if(parseInt(PAIEMENT1) > parseInt(PAIEMENT))
      {
        $('#error_PAIEMENT2').text("<?=lang('messages_lang.message_montant_super_paiement')?>")
        return false;
      }
      $('#error_PAIEMENT').text("");
    }
    else
    {
      $('#error_PAIEMENT').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(DECAISSEMENT1!='')
    {
      if(parseInt(DECAISSEMENT1) > parseInt(DECAISSEMENT))
      {
        $('#error_DECAISSEMENT2').text("<?=lang('messages_lang.message_montant_super_decaiss')?>")
        return false;
      }
      $('#error_DECAISSEMENT').text("");
    }
    else
    {
      $('#error_DECAISSEMENT').text("<?=lang('messages_lang.validation_message')?>");
      return false;
    }

    if(DATA_LABELLE==0)
    {
      if(LIBELLE=='') 
      {
        $('#error_LIBELLE').text('<?=lang('messages_lang.validation_message')?>');
        return false;
      }
      else
      {
        $('#error_LIBELLE').text('');
      }
    }

    if(statut == true) 
    {
      $.ajax(
      { 
        url: '<?=base_url('/transfert_new/Correction_Erreur_Imputation/addtocart')?>',
        type:"POST",
        dataType:"JSON",
        data:
        { 
          IMPUTATION:IMPUTATION,
          CODE_NOMENCLATURE_BUDGETAIRE:CODE_NOMENCLATURE_BUDGETAIRE,
          ENG_BUDGETAIRE1:ENG_BUDGETAIRE1,
          ENG_JURIDIQUE1:ENG_JURIDIQUE1,
          LIQUIDATION1:LIQUIDATION1,
          ORDONNANCEMENT1:ORDONNANCEMENT1,
          PAIEMENT1:PAIEMENT1,
          DECAISSEMENT1:DECAISSEMENT1,
          LIBELLE:LIBELLE
        },
        beforeSend: function()
        {
          $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#bouton_envoyer').attr('disabled',true);
        },
        success:function(data)
        {
          liste_tempo();
          setTimeout(()=>
          {
            $('#message_btn').html('<i class="fa fa-check"></i>');
            window.location.reload();

            $('#loading_cart').html("");
            $('#bouton_envoyer').attr('disabled',false);

            $('#ENG_BUDGETAIRE1').val('');
            $('#ENG_JURIDIQUE1').val('');
            $('#LIQUIDATION1').val('');
            $('#ORDONNANCEMENT1').val('');
            $('#PAIEMENT1').val('');
            $('#DECAISSEMENT1').val('');
            $('#LIBELLE').val('');

            $('#ENG_BUDGETAIRE').val('');
            $('#ENG_JURIDIQUE').val('');
            $('#LIQUIDATION').val('');
            $('#ORDONNANCEMENT').val('');
            $('#PAIEMENT').val('');
            $('#DECAISSEMENT').val('');
          },3000); 
        }
      });
    }
  }

  function removeToCart(TEMPO_CORRECTION_IMPUTATION_ID)
  {
    var TEMPO_CORRECTION_IMPUTATION_ID=TEMPO_CORRECTION_IMPUTATION_ID
    $.ajax(
    { 
      url: '<?=base_url('/transfert_new/Correction_Erreur_Imputation/removeToCart')?>',
      type:"POST",
      dataType:"JSON",
      data:
      { 
        TEMPO_CORRECTION_IMPUTATION_ID:TEMPO_CORRECTION_IMPUTATION_ID,
      },
      beforeSend: function()
      {
        $('#loading_delete'+TEMPO_CORRECTION_IMPUTATION_ID+'').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {
        liste_tempo()
        setTimeout(()=>
        {
          $('#loading_delete'+TEMPO_CORRECTION_IMPUTATION_ID+'').html("");
          $('#message'+TEMPO_CORRECTION_IMPUTATION_ID+'').html('<i class="fa fa-check"></i>');
          window.location.reload();
        },3000); 
      }
    });
  }
</script>

<script type="text/javascript">
  function send_data(argument)
  {
    $('#MyFormData').submit();
  }
</script>