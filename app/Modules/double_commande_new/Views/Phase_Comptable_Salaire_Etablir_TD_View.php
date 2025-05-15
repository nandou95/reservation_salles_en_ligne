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
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
      <script src="/DataTables/datatables.js"></script>
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
                    <a href="<?php echo base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Salaire_Net')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape1?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myFormpc" id="myFormpc" action="<?=base_url('double_commande_new/Phase_Comptable_Salaire/save_prise_Charge/')?>" method="post" >
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
                          } ?>
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$execution['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                                 <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$execution['EXECUTION_BUDGETAIRE_ID']?>">
                                <input type="hidden" name="ETAPE_ACTUELLE_ID" id="ETAPE_ACTUELLE_ID" value="<?=$etape_actuel?>">
                                <input type="hidden" name="NET" id="NET" value="<?=$execution['NET']?>">
                                
                                
                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label">Mois</label>
                                    <input type="text" readonly="" value="<?=$mois['DESC_MOIS']?>" class=" form-control" name="MOIS_ID" id="MOIS_ID">
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label">Catégorie salariés</label>
                                    <input type="text" readonly="" class="form-control" id="CATEGORIE_SALAIRE_ID" value="<?=$categ_salaire['DESC_CATEGORIE_SALAIRE']?>" name="CATEGORIE_SALAIRE_ID">
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label for="">Type salarié</label>
                                    <input type="text" readonly=""  name="TYPE_SALAIRE_ID" id="TYPE_SALAIRE_ID" value="<?=$types_salaire['DESC_TYPE_SALAIRE']?>" class="form-control">
                                  </div> 
                                </div>

                                <div class="col-md-6" id="">
                                  <div class="form-froup">
                                    <label class="form-label">Montant ordonancé</label>
                                    <input onpaste="return false;" type="text" readonly="" class="form-control " name="MONTANT_ORDONANCE" id="MONTANT_ORDONANCE" placeholder=""  value="<?=number_format($execution['ORDONNANCEMENT'],0,'',' ')?>" onpaste="return false;" min="0"   >
                                    <font color="red" id="error_MONTANT_ORDONANCE"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('MONTANT_ORDONANCE'); ?>
                                    <?php endif ?>
                                    <br>
                                  </div>
                                  <input type="hidden" name="engagement_budget" id="engagement_budget">
                                </div>

                                <div class="col-md-6" id="">
                                  <div class="form-froup">
                                    <label class="form-label">Salaire net</label>
                                    <input onpaste="return false;" type="text" readonly="" class="form-control " name="SALAIRE_NET" id="SALAIRE_NET" placeholder=""  value="<?=number_format($execution['NET'],0,'',' ')?>" onpaste="return false;" min="0"   >
                                  </div>
                                  <input type="hidden" name="engagement_budget" id="engagement_budget">
                                </div>

                                <div class="col-md-6" id="racc_bif">
                                  <div class="form-froup">
                                    <label class="form-label">Date Ordo</label>
                                    <input onpaste="return false;" type="text" readonly="" class="form-control " name="DATE_ORDO" id="DATE_ORDO" placeholder="" value="<?=date("d-m-Y",strtotime($execution['DATE_ORDONNANCEMENT']))?>" onpaste="return false;" min="0">
                                    <font color="red" id="error_DATE_ORDO"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('DATE_ORDO'); ?>
                                    <?php endif ?>
                                    <br>
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label for="">Numéro TD <font color="red">*</font></label>
                                    <input type="text" oninput="formatInputValue(this)" name="NUMERO_TD" id="NUMERO_TD"  class="form-control">
                                    <font color="red" id="error_NUMERO_TD"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('NUMERO_TD'); ?>
                                    <?php endif ?>
                                    <br>
                                  </div> 
                                </div>

                                <div class="col-md-6">
                                  <label for="">Upload TD <font color="red">*</font></label>
                                  <input onchange="ValidationFile();" accept=".pdf" type="file" name="PATH_NUMERO_TD" id="PATH_NUMERO_TD" class="form-control" >
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_PATH_NUMERO_TD"><?= $validation->getError('PATH_NUMERO_TD'); ?></font>
                                  <?php endif ?>
                                </div>

                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label for="">Benéficaire <font color="red">*</font></label>
                                    <select name="BENEFICIAIRE_TITRE_ID" id="BENEFICIAIRE_TITRE_ID" class="form-control">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>                                        
                                      <?php
                                      foreach ($beneficiaires as $keys)
                                      { 
                                      if($keys->BENEFICIAIRE_TITRE_ID==set_value('BENEFICIAIRE_TITRE_ID')) { ?>
                                      <option value="<?=$keys->BENEFICIAIRE_TITRE_ID ?>" selected>
                                        <?=$keys->DESC_BENEFICIAIRE?></option>
                                        <?php }else{?>
                                        <option value="<?=$keys->BENEFICIAIRE_TITRE_ID ?>">
                                          <?=$keys->DESC_BENEFICIAIRE?></option>
                                          <?php } }?>

                                            </select>
                                            <font color="red" id="error_BENEFICIAIRE_TITRE_ID"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('BENEFICIAIRE_TITRE_ID'); ?>
                                            <?php endif ?>
                                            <br>
                                    </div> 
                                  </div>

                                  <div class="col-md-6" >
                                    <label for="">Motif Décaissement <font color="red">*</font></label>
                                    <textarea class="form-control" name="MOTIF_DECAISS" id="MOTIF_DECAISS"><?=set_value('MOTIF_DECAISS')?></textarea>
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_MOTIF_DECAISS"><?= $validation->getError('MOTIF_DECAISS'); ?></font>
                                    <?php endif ?>
                                  </div>
                                </div>
                              </div>
                              <div style="float: right;" class="col-md-2">
                                <div class="form-group">
                                  <a onclick="add_edition_titre()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><b id="loading_save"></b> <?= lang('messages_lang.label_enre') ?></a>
                                </div>
                              </div>
                            </div>                            
                          </div>
                        </form><br><br>
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
  $(".allownumericwithdecimal").on("keypress keyup blur",function (event) {
    $(this).val($(this).val().replace(/[^0-9\.|\,]/g,''));
    debugger;
    if(event.which == 44)
    {
      return true;
    }
    if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57  )) {

      event.preventDefault();
    }
  });
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
</script>

<script type="text/javascript">
  function add_edition_titre()
  {
    let NUMERO_TD=$('#NUMERO_TD').val();   

    var PATH_NUMERO_TD=document.getElementById("PATH_NUMERO_TD").files[0];
         
    let EXECUTION_BUDGETAIRE_ID=$('#EXECUTION_BUDGETAIRE_ID').val();
    let EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();
    let ETAPE_ACTUELLE_ID=$('#ETAPE_ACTUELLE_ID').val();
    
    let NET = $('#NET').val();
   
    let BENEFICIAIRE_TITRE_ID=$('#BENEFICIAIRE_TITRE_ID').val();
    let MOTIF_DECAISS=$('#MOTIF_DECAISS').val();
    
    // reset error message_error
    $('#error_PATH_NUMERO_TD').html('');
    $('#error_NUMERO_TD').html('');
    $('#error_BENEFICIAIRE_TITRE_ID').html('');
    $('#error_MOTIF_DECAISS').html('');

    //start validation
    let isFormValid = true;

    if(NUMERO_TD== ""){
      $('#error_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if($('#PATH_NUMERO_TD').val() == ""){
      $('#error_PATH_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(BENEFICIAIRE_TITRE_ID== "")
    {
      $('#error_BENEFICIAIRE_TITRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(MOTIF_DECAISS== "")
    {
      $('#error_MOTIF_DECAISS').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }

    if(!isFormValid) return;
    //do the post request
    let form = new FormData();
    form.append("NUMERO_TD",NUMERO_TD);
    form.append("PATH_NUMERO_TD",PATH_NUMERO_TD); 
    form.append("EXECUTION_BUDGETAIRE_ID",EXECUTION_BUDGETAIRE_ID);
    form.append("EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID",EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
    form.append("ETAPE_ACTUELLE_ID",ETAPE_ACTUELLE_ID);
    form.append("NET",NET);
    form.append("MOTIF_DECAISS",MOTIF_DECAISS);
    form.append("BENEFICIAIRE_TITRE_ID",BENEFICIAIRE_TITRE_ID);

    $.ajax(
    {
      url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/save_edition_TD')?>/"+1,
      type:"POST",
      dataType:"JSON",
      data: form,
      processData: false,  
      contentType: false,
      beforeSend:function() {
        $('#btn_save').attr('disabled',true);
        $('loading_save').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      { 
        $('#btn_save').attr('disabled',false);
        if(data.message)
        {
          window.location.href = "<?=base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Salaire_Net')?>";
        }

        if(data.errors)
        {
          let errors = data.errors;
          $('#error_NUMERO_TD').html(data.errors.NUMERO_TD);
          $('#error_PATH_NUMERO_TD').html(data.errors.PATH_NUMERO_TD);
          
        }
      }
    });
  }
</script>

<script type="text/javascript">
  function ValidationFile()
  {
    var fileInput = document.getElementById('PATH_NUMERO_TD');
    var filePath = fileInput.value;
    // Allowing file type
    var allowedExtensions = /(\.pdf)$/i;
      
    if (!allowedExtensions.exec(filePath))
    {
      $('#error_PATH_NUMERO_TD').text("<?=lang('messages_lang.error_message_pdf')?>");
      fileInput.value = '';
      return false;
    }
    else
    {
      // Check if any file is selected. 
      if (fileInput.files.length > 0)
      { 
        for (var i = 0; i <= fileInput.files.length - 1; i++)
        { 
          var fsize = fileInput.files.item(i).size; 
          var file = Math.round((fsize / 1024)); 
          // The size of the file. 
          if (file > 2500)
          { 
            $('#error_PATH_NUMERO_TD').text('<?=lang('messages_lang.error_message_taille_pdf')?>');
            fileInput.value = '';
          }else
          {
           $('#error_PATH_NUMERO_TD').text(''); 
          }
        } 
      }
    }
  }
</script>

 <!-- Permettre les entiers et les - & / seulement  -->
<script type="text/javascript">
  function formatInputValue(input) 
  { 
    var numericValue = input.value.replace(/[^0-9/-]/g, '');
    input.value = numericValue;
  }
</script>