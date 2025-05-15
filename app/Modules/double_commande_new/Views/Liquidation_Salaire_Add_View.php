<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation(); ?>
  </head>
  <style>
    hr.vertical {
      border:         none;
      border-left:    1px solid hsla(200, 2%, 12%,100);
      height:         55vh;
      width: 1px;
      color: #ddd
    }
  </style>
  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php'); ?>
      <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
      <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
      <div class="main">
        <?php echo view('includesbackend/navybar_topbar.php'); ?>
        
        <main class="content">
          <div class="container-fluid">
            <div class="header">
              <h1 class="header-title text-white"></h1>
            </div>
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div style="float: right;">
                    <a href="<?php echo base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:7px"> <?=$etape1?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myEtape1" id="myEtape1" action="<?=base_url('double_commande_new/Liquidation_Salaire/savesalaire/')?>" method="post" >
                        <div class="container">
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
                          }?>
                          <center class="ml-5" style="height=100px;width:90%" >
                            <div class="w-100 bg-danger text-white text-center" id="message2">
                            </div>
                          </center>
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12" >
                              <div class="row">
                                <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                                <div class="col-md-4">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_inst') ?><font color="red">*</font></label>
                                    <select onchange="get_sousTutel();get_categ_salarie()" class="select2 form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  foreach ($institutions as $keys) { ?>
                                      <?php if($keys->INSTITUTION_ID==set_value('INSTITUTION_ID')) { ?>
                                      <option value="<?=$keys->INSTITUTION_ID ?>" selected>
                                        <?=$keys->CODE_INSTITUTION.'-'.$keys->DESCRIPTION_INSTITUTION?></option>
                                        <?php }else{?>
                                      <option value="<?=$keys->INSTITUTION_ID ?>">
                                          <?=$keys->CODE_INSTITUTION.'-'.$keys->DESCRIPTION_INSTITUTION?></option>
                                          <?php } }?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('INSTITUTION_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_INSTITUTION_ID"></font>
                                  </div>
                                </div>

                                <div class="col-md-4">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_sousTitre') ?><font color="red">*</font></label><b id="loading_sous_tutel"></b>
                                    <select class="select2 form-control" id="SOUS_TITRE_ID" value="<?=set_value('SOUS_TITRE_ID') ?>" name="SOUS_TITRE_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>

                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('SOUS_TITRE_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_SOUS_TITRE_ID"></font>
                                    <br>
                                  </div>
                                </div>

                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.type_salarie') ?><font color="red">*</font></label>
                                    <select name="TYPE_SALAIRE_ID" id="TYPE_SALAIRE_ID" class="form-control">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php
                                        foreach($type as $key)
                                        { 
                                          if ($key->TYPE_SALAIRE_ID==set_value('TYPE_SALAIRE_ID')) { 
                                            echo "<option value='".$key->TYPE_SALAIRE_ID."' selected>".$key->DESC_TYPE_SALAIRE."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key->TYPE_SALAIRE_ID."' >".$key->DESC_TYPE_SALAIRE."</option>"; 
                                          } 
                                        }
                                      ?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TYPE_SALAIRE_ID"><?= $validation->getError('TYPE_SALAIRE_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>

                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.categorie_salarie') ?><font color="red">*</font></label>
                                    <select name="CATEGORIE_SALAIRE_ID" id="CATEGORIE_SALAIRE_ID"  class="form-control">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_CATEGORIE_SALAIRE_ID"><?= $validation->getError('CATEGORIE_SALAIRE_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>                                                      

                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.label_mois') ?><font color="red">*</font></label>
                                    <select name="MOIS_ID" id="MOIS_ID" class="form-control" onchange="get_data()">
                                      <?php
                                        foreach($get_mois as $key)
                                        { 
                                          if ($key->MOIS_ID==set_value('MOIS_ID')) { 
                                            echo "<option value='".$key->MOIS_ID."' selected>".$key->DESC_MOIS."</option>";
                                          }else{
                                            echo "<option value='".$key->MOIS_ID."' >".$key->DESC_MOIS."</option>"; 
                                          } 
                                        }
                                      ?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MOIS_ID"><?= $validation->getError('MOIS_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>  

                                <div class="col-md-2">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.qte_fonct_puliq') ?><font color="red">*</font></label>
                                    <input type="text" name="QTE_FONCTION_PUBLIQUE" id="QTE_FONCTION_PUBLIQUE" class="form-control">
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_QTE_FONCTION_PUBLIQUE"><?= $validation->getError('QTE_FONCTION_PUBLIQUE'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>  

                                <div class="col-md-2">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.qte_ressource') ?><font color="red">*</font></label>
                                    <input type="text" name="QTE_RESSOURCES_HUMAINES" id="QTE_RESSOURCES_HUMAINES" class="form-control">
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_QTE_RESSOURCES_HUMAINES"><?= $validation->getError('QTE_RESSOURCES_HUMAINES'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>                         
                              </div><br>       
                            </div>
                            <div class="col-md-12" id="tableau" hidden="true">
                              <input type="hidden" name="getdata" id="getdata">
                              <table class="table table-responsive">
                                <thead>
                                  <tr>
                                    <th><?=lang('messages_lang.imputation_decaissement')?></th>
                                    <th class="text-lowercase"><?=lang('messages_lang.libelle_imputation')?></th>
                                    <th class="text-lowercase"><?=lang('messages_lang.th_tache')?></th>
                                    <th class="text-lowercase"><?=lang('messages_lang.labelle_montant_vote')?></th>
                                    <th><?=lang('messages_lang.label_Money_res')?></th>
                                    <th><?=lang('messages_lang.table_monta-liq')?></th>
                                  </tr>
                                </thead>
                                <tbody id="donnee">
                                  <tr>
                                    <td colspan="6"><center id="loading_donnees"></center></td>
                                  </tr>
                                </tbody>
                              </table>
                              <table class="table table-responsive">
                                <thead>
                                  <tr>
                                    <th><?=lang('messages_lang.labelle_rubrique')?></th>
                                    <th><?=lang('messages_lang.labelle_montant')?></th>
                                  </tr>
                                </thead>
                                <tbody>                                  
                                  <tr>
                                    <td>INSS P</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="INSS_P" id="INSS_P" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>INSS RP</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="INSS_RP" id="INSS_RP" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>ONPR</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="ONPR" id="ONPR" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>MFP</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="MFP" id="MFP" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>IMPOT</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="IMPOT" id="IMPOT" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>AUTRE RETENU</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="AUTRES_RETENUS" id="AUTRES_RETENUS" value="0"></td>
                                  </tr>
                                  <tr>
                                    <td>NET</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="NET" id="NET" value="0"></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>                                                 
                          </div>
                          <div style="float: right;" class="col-md-2" >
                            <br>
                            <div class="form-group " >
                              <a onclick="savesalaire()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><b id="loading_save"></b> <?= lang('messages_lang.label_enre') ?></a>
                            </div>
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
  </body>
</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
</script>

<script>
  $(document).ready(function(){
    get_sousTutel()
  })
</script>

<script type="text/javascript">
  function get_sousTutel()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();

    if(INSTITUTION_ID=='')
    {
      $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
      $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
      $('#SOUS_TITRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
    }
    else
    {
      $('#SOUS_TITRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
      var url = "<?=base_url()?>/double_commande_new/Liquidation_Salaire/get_sousTutel/"+INSTITUTION_ID;

      $.ajax(
      {
        url:url,
        type:"GET",
        dataType:"JSON",
        beforeSend:function() 
        {
          $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          $('#SOUS_TITRE_ID').html(data.SousTutel);
          $('#loading_sous_tutel').html("");
        }
      });
    }
  }

  function get_categ_salarie()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var url = "<?=base_url()?>/double_commande_new/Liquidation_Salaire/get_salarie";

    $.ajax(
    {
      url:url,
      type:"POST",
      dataType:"JSON",
      data:{
        INSTITUTION_ID:INSTITUTION_ID
      },
      beforeSend:function() 
      {
      },
      success: function(data)
      {
        $('#CATEGORIE_SALAIRE_ID').html(data.html);
      }
    });    
  }

  function get_data()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TITRE_ID=$('#SOUS_TITRE_ID').val();
    var CATEGORIE_SALAIRE_ID=$('#CATEGORIE_SALAIRE_ID').val();
    var MOIS_ID=$('#MOIS_ID').val();
    
    var url = "<?=base_url()?>/double_commande_new/Liquidation_Salaire/get_data";

    $.ajax(
    {
      url:url,
      type:"POST",
      dataType:"JSON",
      data:{
        INSTITUTION_ID:INSTITUTION_ID,
        SOUS_TITRE_ID:SOUS_TITRE_ID,
        MOIS_ID:MOIS_ID,
        CATEGORIE_SALAIRE_ID:CATEGORIE_SALAIRE_ID
      },
      beforeSend:function() 
      {
        $('#tableau').attr('hidden',false)
        $('#donnee').html("");
        $('#loading_donnees').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#donnee').html(data.html);
        $('#getdata').val(JSON.stringify(data.getdata));
        if(data.html!='')
        {
          $('#tableau').attr('hidden',false)
          $('#loading_donnees').html("");
          $('#QTE_RESSOURCES_HUMAINES').val(data.qteRHR);
        }
        else
        {
          $('#tableau').attr('hidden',true)
          $('#QTE_RESSOURCES_HUMAINES').val("");
        }
        
      }
    });
  }
</script>

<script type="text/javascript">
  function calculer(TACHE_ID)
  {
    var MONTANT_RESTANT = $('#MONTANT_RESTANT'+TACHE_ID).val();
    var MONTANT_LIQUIDE = $('#MONTANT_LIQUIDE'+TACHE_ID).val();
    MONTANT_RESTANT=MONTANT_RESTANT.replace(/[^0-9.]/g, '');
    MONTANT_LIQUIDE=MONTANT_LIQUIDE.replace(/[^0-9]/g, '');
    $('#error_MONTANT_LIQUIDE'+TACHE_ID).val('');

    if(parseInt(MONTANT_LIQUIDE)>parseInt(MONTANT_RESTANT))
    {
      $('#error_MONTANT_LIQUIDE'+TACHE_ID).text('<?=lang('messages_lang.mount_sup')?>');
    }
    else
    {
      $('#error_MONTANT_LIQUIDE'+TACHE_ID).text('')
    }
  }
</script>

<script type="text/javascript">
  function savesalaire()
  {
    var statut=2;

    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    $('#error_INSTITUTION_ID').html('');

    var MOIS_ID = $('#MOIS_ID').val();
    $('#error_MOIS_ID').html('');

    var SOUS_TITRE_ID=$('#SOUS_TITRE_ID').val()
    $('#error_SOUS_TITRE_ID').html('');

    if (INSTITUTION_ID=='')
    {
      $('#error_INSTITUTION_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(SOUS_TITRE_ID=='')
    {
      $('#error_SOUS_TITRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(MOIS_ID=='')
    {
      $('#error_MOIS_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var CATEGORIE_SALAIRE_ID=$('#CATEGORIE_SALAIRE_ID').val();
    if(CATEGORIE_SALAIRE_ID=='')
    {
      $('#error_CATEGORIE_SALAIRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var TYPE_SALAIRE_ID=$('#TYPE_SALAIRE_ID').val();
    if(TYPE_SALAIRE_ID=='')
    {
      $('#error_TYPE_SALAIRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var QTE_FONCTION_PUBLIQUE=$('#QTE_FONCTION_PUBLIQUE').val();
    $('#error_QTE_FONCTION_PUBLIQUE').html("")
    $('#error_QTE_RESSOURCES_HUMAINES').html("")

    if (QTE_FONCTION_PUBLIQUE=='')
    {
      $('#error_QTE_FONCTION_PUBLIQUE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var QTE_RESSOURCES_HUMAINES=$('#QTE_RESSOURCES_HUMAINES').val();
    if (QTE_RESSOURCES_HUMAINES=='')
    {
      $('#error_QTE_RESSOURCES_HUMAINES').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var INSS_P=$('#INSS_P').val().replace(/\s/g, "");
    var INSS_RP=$('#INSS_RP').val().replace(/\s/g, "");
    var ONPR=$('#ONPR').val().replace(/\s/g, "");
    var MFP=$('#MFP').val().replace(/\s/g, "");
    var IMPOT=$('#IMPOT').val().replace(/\s/g, "");
    var AUTRES_RETENUS=$('#AUTRES_RETENUS').val().replace(/\s/g, "");
    var NET=$('#NET').val().replace(/\s/g, "");

    var total_rubrique=parseFloat(INSS_P)+parseFloat(INSS_RP)+parseFloat(ONPR)+parseFloat(MFP)+parseFloat(IMPOT)+parseFloat(AUTRES_RETENUS)+parseFloat(NET);

    var getdata=$('#getdata').val();
    var data = JSON.parse(getdata);
    var Total_liquide=0;
    if(data.length<=0)
    {
      statut=1;
      $('#error_CATEGORIE_SALAIRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
    }
    else
    {
      for (var i = 0; i < data.length; i++)
      {        
        var item = data[i];
        var MONTANT_LIQUIDE=$('#MONTANT_LIQUIDE'+item.PTBA_TACHE_ID).val();
        Total_liquide +=parseInt(MONTANT_LIQUIDE.replace(/[^0-9.]/g, ''));
        if(MONTANT_LIQUIDE=='')
        {
          $('#error_MONTANT_LIQUIDE'+item.PTBA_TACHE_ID).html("<?=lang('messages_lang.input_oblige')?>");
          statut=1;
        }
        else
        {
          var MONTANT_RESTANT=$('#MONTANT_RESTANT'+item.PTBA_TACHE_ID).val();
          MONTANT_RESTANT=MONTANT_RESTANT.replace(/[^0-9.]/g, '');
          MONTANT_LIQUIDE=MONTANT_LIQUIDE.replace(/[^0-9]/g, '');
          if(parseInt(MONTANT_LIQUIDE)>parseInt(MONTANT_RESTANT))
          {
            $('#error_MONTANT_LIQUIDE'+item.PTBA_TACHE_ID).text('<?=lang('messages_lang.mount_sup')?>');
            statut=1;
          }
          else
          {
            $('#error_MONTANT_LIQUIDE'+item.PTBA_TACHE_ID).html("");
          }
        }        
      }
    }

    if(Total_liquide!=total_rubrique)
    {
      statut=1;
      $('#message2').html("<?=lang('messages_lang.mont_rubr_differ')?>");
    }

    if(statut == 2)
    {
      document.getElementById("myEtape1").submit();
    }      
  }
</script>

<script type="text/javascript">
  function formatInputValue(input) 
  {
    numericValue = input.value.replace(/[^0-9]/g, '');    
    var formattedValue = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');              
    input.value = formattedValue;
  }
</script>

<script type="text/javascript">
  function DoPrevent(e)
  {
    e.preventDefault();
    e.stopPropagation();
  }
</script>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>
