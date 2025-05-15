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
                    <a href="<?php echo base_url('double_commande_new/Liquidation_Salaire_Liste/index_A_Corr')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:7px"> <?=$etape1?></h4>
                    <br>                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myForm" id="myForm" action="<?=base_url('double_commande_new/Liquidation_Salaire/save_correction_salaire/')?>" method="post" >
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
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12" >
                              <div class="row">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$getdonnees['EXECUTION_BUDGETAIRE_ID']?>">
                                <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$getdonnees['ETAPE_DOUBLE_COMMANDE_ID']?>">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID" id="EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID" value="<?=$getdonnees['EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID']?>">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" id="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$getdonnees['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$getdonnees['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                                <div class="col-md-4">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_inst') ?><font color="red">*</font></label>
                                    <select onchange="" class="select2 form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  foreach ($institutions as $keys) { ?>
                                      <?php if($keys->INSTITUTION_ID==set_value('INSTITUTION_ID')) { ?>
                                      <option value="<?=$keys->INSTITUTION_ID ?>" selected>
                                        <?=$keys->CODE_INSTITUTION.'-'.$keys->DESCRIPTION_INSTITUTION?></option>
                                        <?php }else{?>
                                      <option value="<?=$keys->INSTITUTION_ID ?>" selected>
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
                                    <select class="select2 form-control" id="SOUS_TITRE_ID" value="<?=set_value('SOUS_TITRE_ID') ?>" name="SOUS_TITRE_ID" onchange="get_data()">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php
                                      foreach ($sousTutel as $key)
                                      {
                                        echo '<option value="'.$key->SOUS_TUTEL_ID.'" selected>'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
                                      }
                                      ?>
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
                                            echo "<option value='".$key->TYPE_SALAIRE_ID."' selected>".$key->DESC_TYPE_SALAIRE."</option>"; 
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
                                    <select name="CATEGORIE_SALAIRE_ID" id="CATEGORIE_SALAIRE_ID" onchange="" class="form-control">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php
                                        foreach($categorie as $key)
                                        { 
                                          if ($key->CATEGORIE_SALAIRE_ID==set_value('CATEGORIE_SALAIRE_ID')) { 
                                            echo "<option value='".$key->CATEGORIE_SALAIRE_ID."' selected>".$key->DESC_CATEGORIE_SALAIRE."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key->CATEGORIE_SALAIRE_ID."' selected>".$key->DESC_CATEGORIE_SALAIRE."</option>"; 
                                          } 
                                        }
                                      ?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_CATEGORIE_SALAIRE_ID"><?= $validation->getError('CATEGORIE_SALAIRE_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>

                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.label_mois') ?><font color="red">*</font></label>
                                    <select name="MOIS_ID" id="MOIS_ID" class="form-control">
                                      <?php 
                                        foreach($get_mois as $key) { 
                                          if ($key->MOIS_ID==set_value('MOIS_ID')) { 
                                            echo "<option value='".$key->MOIS_ID."' selected>".$key->DESC_MOIS."</option>";
                                          }else{
                                            echo "<option value='".$key->MOIS_ID."' selected>".$key->DESC_MOIS."</option>"; 
                                          } 
                                        }?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MOIS_ID"><?= $validation->getError('MOIS_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div> 
                                <div class="col-md-2">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.qte_fonct_puliq') ?><font color="red">*</font></label>
                                    <input type="text" name="QTE_FONCTION_PUBLIQUE" id="QTE_FONCTION_PUBLIQUE" class="form-control" value="<?=$getdonnees['QTE_FONCTION_PUBLIQUE']?>">
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_QTE_FONCTION_PUBLIQUE"><?= $validation->getError('QTE_FONCTION_PUBLIQUE'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>  

                                <div class="col-md-2">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.qte_ressource') ?><font color="red">*</font></label>
                                    <input type="text" name="QTE_RESSOURCES_HUMAINES" id="QTE_RESSOURCES_HUMAINES" class="form-control" value="<?=$getdonnees['QTE_RESSOURCES_HUMAINES']?>">
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_QTE_RESSOURCES_HUMAINES"><?= $validation->getError('QTE_RESSOURCES_HUMAINES'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>                          
                              </div><br>            
                            </div>
                            <div class="col-md-12" id="tableau">
                              <input type="hidden" name="getdata" id="getdata" value="">
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
                                  <?php
                                    function get_precision($value=0)
                                    {
                                      $string = strval($value);
                                      $number=explode('.',$string)[1] ?? '';
                                      $precision='';
                                      for($i=1;$i<=strlen($number);$i++)
                                      {
                                        $precision=$i;
                                      }
                                      if(!empty($precision)) 
                                      {
                                        return $precision;
                                      }
                                      else
                                      {
                                        return 0;
                                      }    
                                    }

                                    foreach($get_data as $key)
                                    {
                                      $MONTANT_RESTANT='';
                                      $MONTANT_VOTE='';

                                      if($TRIMESTRE_ID==1)
                                      {
                                        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T1;
                                        $MONTANT_VOTE=$key->BUDGET_T1;
                                      }
                                      elseif($TRIMESTRE_ID==2)
                                      {
                                        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T2;
                                        $MONTANT_VOTE=$key->BUDGET_T2;
                                      }
                                      elseif($TRIMESTRE_ID==3)
                                      {
                                        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T3;
                                        $MONTANT_VOTE=$key->BUDGET_T3;
                                      }
                                      elseif($TRIMESTRE_ID==4)
                                      {
                                        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T4;
                                        $MONTANT_VOTE=$key->BUDGET_T4;
                                      }

                                      $DESC_PAP_ACTIVITE='';
                                      if(!empty($key->DESC_PAP_ACTIVITE))
                                      {
                                        $DESC_PAP_ACTIVITE=$key->DESC_PAP_ACTIVITE;
                                      }
                                      else
                                      {
                                        $DESC_PAP_ACTIVITE="-";
                                      }
                                  ?>
                                      <tr>
                                        <td><?=$key->CODE_NOMENCLATURE_BUDGETAIRE ?></td>
                                        <td><?=$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE ?></td>
                                        <td><?=$key->DESC_TACHE ?></td>
                                        <td><?=number_format($MONTANT_VOTE,get_precision($MONTANT_VOTE),',',' ') ?></td>
                                        <td><?=number_format($MONTANT_RESTANT,get_precision($MONTANT_RESTANT),',',' ') ?>
                                          <input type="hidden" oninput="calculer(<?=$key->PTBA_TACHE_ID?>);formatInputValue(this)" name="MONTANT_RESTANT<?=$key->PTBA_TACHE_ID?>" id="MONTANT_RESTANT<?=$key->PTBA_TACHE_ID?>" class="form-control" value="<?=$key->MONTANT_LIQUIDATION+$MONTANT_RESTANT?>">
                                        </td>
                                        <td><input type="text" oninput="calculer(<?=$key->PTBA_TACHE_ID?>);formatInputValue(this)" name="MONTANT_LIQUIDE<?=$key->PTBA_TACHE_ID?>" id="MONTANT_LIQUIDE<?=$key->PTBA_TACHE_ID?>" class="form-control" value="<?=$key->MONTANT_LIQUIDATION?>">
                                        <font color="red" id="error_MONTANT_LIQUIDE<?=$key->PTBA_TACHE_ID?>"></font></td>
                                      <tr>
                                  <?php
                                    }
                                  ?>
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
                                    <td><input type="text" oninput="formatInputValue(this)" name="INSS_P" id="INSS_P" value="<?=$getdonnees['INSS_P']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>INSS RP</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="INSS_RP" id="INSS_RP" value="<?=$getdonnees['INSS_RP']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>ONPR</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="ONPR" id="ONPR" value="<?=$getdonnees['ONPR']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>MFP</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="MFP" id="MFP" value="<?=$getdonnees['MFP']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>IMPOT</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="IMPOT" id="IMPOT" value="<?=$getdonnees['IMPOT']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>AUTRE RETENU</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="AUTRES_RETENUS" id="AUTRES_RETENUS" value="<?=$getdonnees['AUTRES_RETENUS']?>"></td>
                                  </tr>
                                  <tr>
                                    <td>NET</td>
                                    <td><input type="text" oninput="formatInputValue(this)" name="NET" id="NET" value="<?=$getdonnees['NET']?>"></td>
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
    var data = <?php echo json_encode($get_data); ?>;
    $('#getdata').val(JSON.stringify(data));
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

    if (INSTITUTION_ID=='')
    {
      $('#error_INSTITUTION_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(MOIS_ID=='')
    {
      $('#error_MOIS_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var INSS_P=$('#INSS_P').val().replace(/\s/g, "");
    var INSS_RP=$('#INSS_RP').val().replace(/\s/g, "");
    var ONPR=$('#ONPR').val().replace(/\s/g, "");
    var MFP=$('#MFP').val().replace(/\s/g, "");
    var IMPOT=$('#IMPOT').val().replace(/\s/g, "");
    var AUTRES_RETENUS=$('#AUTRES_RETENUS').val().replace(/\s/g, "");
    var NET=$('#NET').val().replace(/\s/g, "");
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

    var total_rubrique=parseFloat(INSS_P)+parseFloat(INSS_RP)+parseFloat(ONPR)+parseFloat(MFP)+parseFloat(IMPOT)+parseFloat(AUTRES_RETENUS)+parseFloat(NET);

    var getdata=$('#getdata').val();
    var data = JSON.parse(getdata);
    var Total_liquide=0;
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

    if(Total_liquide!=total_rubrique)
    {
      statut=1;
      $('#message').html("<?=lang('messages_lang.mont_rubr_differ')?>");
    }

    if(statut == 2)
    {
      document.getElementById("myForm").submit();
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
