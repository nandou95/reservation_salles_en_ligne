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
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:7px"> Ajout des bénéficiaires de salaire</h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myEtape1" id="myEtape1" action="<?=base_url('double_commande_new/Liquidation_Salaire/save_benef/')?>" method="post" >
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
                              <input type="hidden" name="ANNEE_BUDGETAIRE_ID" value="<?=$ANNEE_BUDGETAIRE_ID?>">
                              <div class="row">
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                                    <input type="text" name="ANNEE_DESCRIPTION" value="<?=$ANNEE_DESCRIPTION?>" class="form-control">
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.categorie_salarie') ?><font color="red">*</font></label>
                                    <select name="CATEGORIE_SALAIRE_ID" id="CATEGORIE_SALAIRE_ID" onchange="get_data()" class="form-control">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php
                                        foreach($categorie as $key)
                                        { 
                                          if ($key->CATEGORIE_SALAIRE_ID==set_value('CATEGORIE_SALAIRE_ID')) { 
                                            echo "<option value='".$key->CATEGORIE_SALAIRE_ID."' selected>".$key->DESC_CATEGORIE_SALAIRE."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key->CATEGORIE_SALAIRE_ID."' >".$key->DESC_CATEGORIE_SALAIRE."</option>"; 
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
                                    <label class="form-label"><?= lang('messages_lang.label_mois') ?><font color="red">*</font></label>
                                    <select name="MOIS_ID" id="MOIS_ID" class="form-control">
                                      <?php
                                        foreach($get_mois as $key)
                                        { 
                                          if ($key->MOIS_ID==set_value('MOIS_ID')) { 
                                            echo "<option value='".$key->MOIS_ID."' selected>".$key->DESC_MOIS."</option>";
                                          }
                                          else
                                          {
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

                                <div class="col-md-4">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.labelle_beneficiaire_salary') ?><font color="red">*</font></label>
                                    <select name="BENEFICIAIRE_TITRE_ID" id="BENEFICIAIRE_TITRE_ID" class="form-control" onchange='getAutreBenef(this.value)'>
                                      <option value=""><?=lang('messages_lang.selection_message')?></option>
                                      <?php
                                        foreach($beneficiaire as $key)
                                        { 
                                          if ($key->BENEFICIAIRE_TITRE_ID==set_value('BENEFICIAIRE_TITRE_ID')) { 
                                            echo "<option value='".$key->BENEFICIAIRE_TITRE_ID."' selected>".$key->DESC_BENEFICIAIRE."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key->BENEFICIAIRE_TITRE_ID."' >".$key->DESC_BENEFICIAIRE."</option>"; 
                                          } 
                                        }
                                      ?>
                                      <option value="-1">Autre</option>
                                    </select>
                                    <br>
                                    <span id="autre_benef" class="col-md-12 row" style="display: none">
                                      <div class="col-md-9">
                                        <input type="text" class="form-control" id="DESCRIPTION_BENEF" placeholder="Autre bénéficiaire" name="DESCRIPTION_SERIE">
                                      </div>
                                      <div class="col-md-2" style="margin-left: 5px;">
                                        <button type="button" class="btn btn-success" onclick="save_newBenef()"><i class="fa fa-plus"></i></button>
                                      </div>
                                    </span>
                                    <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_BENEFICIAIRE_TITRE_ID"><?= $validation->getError('BENEFICIAIRE_TITRE_ID'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>                              
                              </div><br>       
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

<script type="text/javascript">
  function getAutreBenef(id = 0)
  {
    var selectElement = document.getElementById("BENEFICIAIRE_TITRE_ID");
    if (id.includes("-1"))
    {
      $('#autre_benef').delay(100).show('hide');
      $('#DESCRIPTION_BENEF').val('');
      $('#DESCRIPTION_BENEF').attr('placeholder','Autre bénéficiaire');
      // disableOptions(selectElement);
    }
    else
    {
      $('#autre_benef').delay(100).hide('show');
      $('#DESCRIPTION_BENEF').val('');
      $('#DESCRIPTION_BENEF').attr('placeholder','Autre bénéficiaire');
      // enableOptions(selectElement);
    }
  }

  function save_newBenef()
  {
    var DESCRIPTION_BENEF = $('#DESCRIPTION_BENEF').val();
    var statut = 2;
    if(DESCRIPTION_BENEF == "")
    {
      $('#DESCRIPTION_BENEF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Liquidation_Salaire/save_newBenef",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_BENEF:DESCRIPTION_BENEF
        },
        beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#BENEFICIAIRE_TITRE_ID').html(data.benef);
          BENEFICIAIRE_TITRE_ID.InnerHtml=data.benef;
          $('#loading_motif').html("");
          $('#BENEFICIAIRE_TITRE_ID').val([]).trigger('change');
          $('#DESCRIPTION_BENEF').attr('placeholder','Autre bénéficiaire');
          $('#autre_benef').delay(100).hide('show');
        }
      });
    }
  }

  function savesalaire()
  {
    var statut=2;

    var MOIS_ID = $('#MOIS_ID').val();
    $('#error_MOIS_ID').html('');

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

    var BENEFICIAIRE_TITRE_ID=$('#BENEFICIAIRE_TITRE_ID').val();
    if(BENEFICIAIRE_TITRE_ID=='')
    {
      $('#error_BENEFICIAIRE_TITRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
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
