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

    .vl2
    {
      border-left: 1px solid #ddd;
      height: 150px;
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
                    <?=lang('messages_lang.titr_trns_entr_deux_act')?><br>
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
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('transfert_new/Transfert_Entre_Deux_Activite/send_data/')?>" method="post" >

                      <!-- Ligne bidgetaire qui envoie -->
                      <div class="col-12">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin :  5px">
                            <div class="col-12">
                              <h4><center> <i class="fa fa-certificate"></i> <?=lang('messages_lang.labelle_activie_origine')?></center></h4><br>
                            </div>

                            <div class="col-12">
                              <div class="row">
                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.label_ligne')?> <span style="color: red">*</span></label>
                                  <select autofocus onchange="get_activitesByCode();" class="form-control select2" id="IMPUTATION" name="IMPUTATION">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                    <?php
                                    foreach($exec_budg as $keyexec_budg)
                                    {
                                      ?>
                                      <option value="<?=$keyexec_budg->EXECUTION_BUDGETAIRE_ID?>"><?=$keyexec_budg->IMPUTATION?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_IMPUTATION"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.label_activite')?> <span style="color: red">*</span> <span id="loading_activite"></span></label>
                                  <select class="form-control select2" id="PTBA_ID" name="PTBA_ID" onchange="get_MontantVoteByActivite();">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                  </select>
                                  <font color="red" id="error_PTBA_ID"></font>
                                </div>

                                <div class="col-4">
                                  <label class="form-label"><?=lang('messages_lang.labelle_tranche')?> <span style="color: red">*</span></label>
                                  <select onchange="getMontantAnnuel()" class="form-control" id="TRANCHE_ID" name="TRANCHE_ID">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
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

                                <div class="col-4">
                                  <label id="montant_vote_label" class="form-label"><?=lang('messages_lang.labelle_montant_vote')?> <span id="loading_montant"></span> <span id="loading_trimestre"></span></label>
                                  <input type="number" name="MONTANT_VOTE" id="MONTANT_VOTE" class="form-control">
                                </div>

                                <div class="col-4">
                                  <label class="form-label"><?=lang('messages_lang.mont_a_transf')?> <span style="color: red">*</span> <span id="loading_montant_transfert"></span></label>
                                  <input onkeyup="get_MontantApresTransfert();this.value=this.value.replace(/[^\d]/,'')" type="number" class="form-control" name="MONTANT_TRANSFERT" id="MONTANT_TRANSFERT">
                                  <font color="red" id="error_MONTANT_TRANSFERT"></font>
                                  <font color="red" id="error_MONTANT_TRANSFERT_SUP"></font>
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
                              <h4><center> <i class="fa fa-circle"></i> <?=lang('messages_lang.act_dest')?></center></h4><br>
                            </div>

                            <div class="col-12">
                              <div class="row">
                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_activite')?> <span style="color: red">*</span> <span id="loading_activite2"></span></label>
                                  <select class="form-control select2" id="ACTIVITES" name="ACTIVITES" onchange="get_MontantVoteByActivite2();">
                                    <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                  </select>
                                  <font color="red" id="error_ACTIVITES"></font>
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_montant_vote')?>  <span id="loading_montant2"></span></label>
                                  <input type="number" name="MONTANT_VOTE2" id="MONTANT_VOTE2" class="form-control">
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.montant_a_recevoir')?></label>
                                  <input type="number" name="MONTANT_RECEVOIR" id="MONTANT_RECEVOIR" class="form-control">
                                </div>

                                <div class="col-6">
                                  <label class="form-label"><?=lang('messages_lang.labelle_montant_apres_transfert')?></label>
                                  <input type="number" name="MONTANT_APRES_TRANSFERT" id="MONTANT_APRES_TRANSFERT" class="form-control">
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
                            <button  id="bouton_envoyer" onclick="addToCart()" type="button" class="btn btn-primary btn-block"><?=lang('messages_lang.bouton_ajouter')?> <span id="loading_cart"></span> <span id="message_btn"></span></button>
                          </div>
                        </div>
                      </div>
                    </form>

                    <div class="col-12"  id="div_btnSendData" hidden=""><br>
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
  function get_activitesByCode()
  {
    var IMPUTATION=$('#IMPUTATION').val();
    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/get_activitesByCode')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        IMPUTATION:IMPUTATION,
      },
      beforeSend:function() {
        $('#loading_activite').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#PTBA_ID').html(data.PTBA_ID);
        $('#loading_activite').html("");
      }
    });
  }

  function get_MontantVoteByActivite()
  {
    var PTBA_ID=$('#PTBA_ID').val();
    var IMPUTATION=$('#IMPUTATION').val();
    $('#TRANCHE_ID').val('');
    $('#MONTANT_TRANSFERT').val('');
    $('#MONTANT_TRANSFERT').attr('disabled',false);
    $('#error_TRANCHE_ID2').text("");

    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/get_MontantVoteByActivite')?>",
      type:"POST",
      dataType:"JSON",
      data:
      {
        PTBA_ID:PTBA_ID,
        IMPUTATION:IMPUTATION,
      },
      beforeSend:function()
      {
        $('#loading_montant').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#loading_activite2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
        $('#ACTIVITES').html(data.PTBA_ID);
        $('#loading_montant').html("");
        $('#loading_activite2').html("");
        $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote_annuel') ?>");
      }
    });
  }

  function get_MontantVoteByActivite2()
  {
    var ACTIVITES=$('#ACTIVITES').val();
    var MONTANT_VOTE2=$('#MONTANT_VOTE2').val();
    var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val();

    if(MONTANT_TRANSFERT=='') 
    {
      $('#error_MONTANT_TRANSFERT').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      $('#ACTIVITES').val('');
      return false;
    }
    else
    {
      $('#error_MONTANT_TRANSFERT').text('');
    }

    $.ajax(
    {
      url:"<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/get_MontantVoteByActivite2')?>",
      type:"POST",
      dataType:"JSON",
      data:
      {
        ACTIVITES:ACTIVITES,
      },
      beforeSend:function()
      {
        $('#loading_montant2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#MONTANT_VOTE2').val(data.MONTANT_VOTE);
        var res = parseInt(MONTANT_TRANSFERT) + parseInt(data.MONTANT_VOTE)
        $('#MONTANT_APRES_TRANSFERT').val(res);
        $('#loading_montant2').html("");
      }
    });
  }

  $("#MONTANT_TRANSFERT").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
  });

  function get_MontantApresTransfert()
  {
    var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val();
    var MONTANT_VOTE=$('#MONTANT_VOTE').val();
    var ACTIVITES=$('#ACTIVITES').val();  
    var MONTANT_VOTE2=$('#MONTANT_VOTE2').val();  

    if(MONTANT_TRANSFERT!='')
    {
      $('#error_MONTANT_TRANSFERT').text('');
      var getNumber = MONTANT_TRANSFERT.substring(0, 1);
      if(getNumber==0)
      {
        $('#MONTANT_TRANSFERT').val('');
      }
      else
      { 
        if(parseInt(MONTANT_TRANSFERT)>parseInt(MONTANT_VOTE))
        {
          $('#error_MONTANT_TRANSFERT_SUP').text("<?=lang('messages_lang.message_montant_super_montant_vote_origine')?>");
          $('#MONTANT_APRES_TRANSFERT').val('');
          $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
        }
        else
        {
          $('#error_MONTANT_TRANSFERT_SUP').text("");
          $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
        }
      }
    }
    else
    {
      $('#MONTANT_RECEVOIR').val('');
    }

    if (ACTIVITES!='')
    {
      $('#MONTANT_APRES_TRANSFERT').val(parseInt(MONTANT_TRANSFERT) + parseInt(MONTANT_VOTE2));
    }
  }

  function getMontantAnnuel(argument)
  {

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
        url:"<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/getMontantAnnuel')?>",
        type:"POST",
        dataType:"JSON",
        data: {
          PTBA_ID:PTBA_ID,
          TRANCHE_ID:TRANCHE_ID
        },
        beforeSend:function() {
          if (TRANCHE_ID==5) {
            $('#loading_montant_transfert').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          }else{
            $('#loading_trimestre').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          } 
        },
        success: function(data)
        {
          if (TRANCHE_ID==5) {
          // $('#MONTANT_TRANSFERT').attr('disabled',true);
            document.getElementById('MONTANT_TRANSFERT').readOnly = true;
            $('#MONTANT_TRANSFERT').val(data.MONTANT_TRANSFERT);
            $('#MONTANT_RECEVOIR').val(data.MONTANT_TRANSFERT);
            $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
            $('#montant_vote_label').text("Montant voté annuel");
            $('#loading_montant_transfert').html("");
          }else{
            if (TRANCHE_ID==1) {
              var DESC_TRANCHE = '<?= lang('messages_lang.labelle_1') ?>';
            }else if (TRANCHE_ID==2) {
              var DESC_TRANCHE = '<?= lang('messages_lang.labelle_2') ?>';
            }else if (TRANCHE_ID==3) {
              var DESC_TRANCHE = '<?= lang('messages_lang.labelle_3') ?>';
            }else if (TRANCHE_ID==4) {
              var DESC_TRANCHE = '<?= lang('messages_lang.labelle_4') ?>';
            }
            $('#MONTANT_TRANSFERT').val('');
            $('#MONTANT_RECEVOIR').val('');
          // $('#MONTANT_TRANSFERT').attr('disabled',false);
            document.getElementById('MONTANT_TRANSFERT').readOnly = false;
            $('#MONTANT_VOTE').val(data.MONTANT_VOTE);
            $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote') ?> "+DESC_TRANCHE+" <?= lang('messages_lang.trim') ?>");
            $('#loading_trimestre').html("");
          }
        }
      });
    }
  }

</script>


<script type="text/javascript">
  $(document).ready(function() {

    liste_tempo()

    $('#MONTANT_TRANSFERT').bind('paste', function (e) {
     e.preventDefault();
   });

    document.getElementById('MONTANT_VOTE').readOnly = true;
    document.getElementById('MONTANT_TRANSFERT').readOnly = true;
    document.getElementById('MONTANT_VOTE2').readOnly = true;
    document.getElementById('MONTANT_RECEVOIR').readOnly = true;
    document.getElementById('MONTANT_APRES_TRANSFERT').readOnly = true;

    $("#message").delay(5000).hide('slow');

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
      url: '<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/liste_tempo')?>',
      type:"POST",
      dataType:"JSON",
      data: { 
        EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
        TRANSFERTS_CREDITS:TRANSFERTS_CREDITS,
        MONTANT_VOTE:MONTANT_VOTE,
        MONTANT_VOTE2:MONTANT_VOTE2
      },
      beforeSend: function() {
  // $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {
        $('#mycart').html(data.html);

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

    var IMPUTATION=$('#IMPUTATION').val()
    var PTBA_ID=$('#PTBA_ID').val()
    var TRANCHE_ID=$('#TRANCHE_ID').val()
    var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val()
    var MONTANT_VOTE=$('#MONTANT_VOTE').val()

  // alert($('#MONTANT_APRES_TRANSFERT').val())

  //ligne receptrice 
  var ACTIVITES=$('#ACTIVITES').val()//activite ligne
  var MONTANT_RECEVOIR=$('#MONTANT_RECEVOIR').val()
  var MONTANT_VOTE2=$('#MONTANT_VOTE2').val()//montant voté pour ligne receptrice

  if (IMPUTATION=='') 
  {
    $('#error_IMPUTATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    return false;
  }else{
    $('#error_IMPUTATION').text('');
  }

  if (PTBA_ID=='') 
  {
    $('#error_PTBA_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    return false;
  }else{
    $('#error_PTBA_ID').text('');
  }

  if (ACTIVITES=='') 
  {
    $('#error_ACTIVITES').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    return false;
  }else{
    $('#error_ACTIVITES').text('');
  }

  if (MONTANT_TRANSFERT=='') 
  {
    $('#error_MONTANT_TRANSFERT').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    return false;
  }else{
    $('#error_MONTANT_TRANSFERT').text('');
  }

  if (TRANCHE_ID=='') 
  {
    $('#error_TRANCHE_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
    return false;
  }else{
    $('#error_TRANCHE_ID').text('');
  }

  if (parseInt(MONTANT_TRANSFERT)>parseInt(MONTANT_VOTE)) {
    $('#error_MONTANT_TRANSFERT_SUP').text("<?= lang('messages_lang.lab_mont_orig_trans') ?>");
    $('#MONTANT_APRES_TRANSFERT').val('');
    $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
    return false;
  }else{
    $('#error_MONTANT_TRANSFERT_SUP').text("");
    $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
  }

  if (statut == true) 
  {
    $.ajax(
    { 
      url: '<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/addtocart')?>',
      type:"POST",
      dataType:"JSON",
      data: { 
        IMPUTATION:IMPUTATION,
        PTBA_ID:PTBA_ID,
        MONTANT_TRANSFERT:MONTANT_TRANSFERT,
        MONTANT_VOTE:MONTANT_VOTE,
        ACTIVITES:ACTIVITES,
        MONTANT_RECEVOIR:MONTANT_RECEVOIR,
        MONTANT_VOTE2:MONTANT_VOTE2,
        TRANCHE_ID:TRANCHE_ID
      },
      beforeSend: function() {
        $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        $('#bouton_envoyer').attr('disabled',true);
      },
      success:function(data)
      {
        liste_tempo();
        setTimeout(()=>{
          $('#message_btn').html('<i class="fa fa-check"></i>');
          window.location.reload();
          
          $('#PTBA_ID').val('');
          $('#MONTANT_VOTE').val('');
          $('#MONTANT_VOTE2').val('');
          $('#loading_cart').html("");
          $('#TRANCHE_ID').val('');
          $('#MONTANT_TRANSFERT').val('');
          $('#ACTIVITES').val('');
          $('#MONTANT_RECEVOIR').val('');
          $('#MONTANT_APRES_TRANSFERT').val('');
          $('#MONTANT_TRANSFERT').attr('disabled',false);
          $('#bouton_envoyer').attr('disabled',false);

        },3000);
      }
    });
  }

}

function removeToCart(TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID)
{
  var TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID = TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID
  $.ajax(
  { 
    url: '<?=base_url('/transfert_new/Transfert_Entre_Deux_Activite/removeToCart')?>',
    type:"POST",
    dataType:"JSON",
    data: { 
      TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID:TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID,
    },
    beforeSend: function() {
      $('#loading_delete'+TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID+'').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
    },
    success:function(data)
    {
      liste_tempo()
      setTimeout(()=>{
        $('#loading_delete'+TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID+'').html("");
        $('#message'+TEMPO_TRANSFERT_ENTRE_ACTIVITE_ID+'').html('<i class="fa fa-check"></i>');
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

