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
                    <a href="<?php echo base_url('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
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
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$execution['EXECUTION_BUDGETAIRE_ID']?>">
                                <input type="hidden" name="ETAPE_ACTUELLE_ID" id="ETAPE_ACTUELLE_ID" value="<?=$etape_actuel?>">
                                
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
                                   

                                    <div class="col-md-6" id="racc_bif">
                                      <div class="form-froup">
                                        <label class="form-label">Montant ordonnancé </label>
                                        <input onpaste="return false;" type="text" readonly="" class="form-control " name="MONTANT_ORDONANCE" id="MONTANT_ORDONANCE" placeholder="" value="<?=number_format($execution['ORDONNANCEMENT'],0,'',' ')?>" onpaste="return false;" min="0"   >
                                        <font color="red" id="error_MONTANT_ORDONANCE"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('MONTANT_ORDONANCE'); ?>
                                        <?php endif ?>
                                        <br>
                                      </div>
                                      <input type="hidden" name="engagement_budget" id="engagement_budget">
                                    </div>                                    

                                    <div class="col-md-6" id="racc_bif">
                                      <div class="form-froup">
                                        <label class="form-label">Date Ordonnancement</label>
                                        <input onpaste="return false;" type="text" readonly="" class="form-control " name="DATE_ORDO" id="DATE_ORDO" placeholder="" value="<?=date("d-m-Y",strtotime($DATE_ORDONNANCEMENT))?>" onpaste="return false;" min="0"   >
                                        <font color="red" id="error_DATE_ORDO"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('DATE_ORDO'); ?>
                                        <?php endif ?>
                                        <br>
                                      </div>
                                      
                                    </div>

                                    <div class="col-md-6">
                                      <div class='form-froup'>
                                        <label for="">Contrôles du comptable <font color="red">*</font></label>
                                        <select name="ID_CONTROLE_COMPTABLE[]" id="ID_CONTROLE_COMPTABLE" multiple onchange="hide_mofif()"  class="form-control select2">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          
                                          <?php  foreach ($controles_comptables as $keys) { ?>
                                          <?php if($keys->ID_CONTROLE_COMPTABLE==set_value('ID_CONTROLE_COMPTABLE')) { ?>
                                          <option value="<?=$keys->ID_CONTROLE_COMPTABLE ?>" selected>
                                            <?=$keys->DESC_CONTROLE_COMPTABLE?></option>
                                            <?php }else{?>
                                            <option value="<?=$keys->ID_CONTROLE_COMPTABLE ?>">
                                              <?=$keys->DESC_CONTROLE_COMPTABLE?></option>
                                              <?php } }?>

                                            </select>
                                            <font color="red" id="error_ID_CONTROLE_COMPTABLE"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('ID_CONTROLE_COMPTABLE'); ?>
                                            <?php endif ?>
                                            <br>
                                          </div> 
                                        </div>


                                        <div class="col-md-6" id="motif_refus">

                                          <label for="">Motif Refus</label>
                                          <textarea class="form-control" name="MOTIF_REFUS" id="MOTIF_REFUS"><?=set_value('MOTIF_REFUS')?></textarea>
                                          <?php if (isset($validation)) : ?>
                                            <font color="red" id="error_MOTIF_REFUS"><?= $validation->getError('MOTIF_REFUS'); ?></font>
                                          <?php endif ?>

                                        </div>

                                  

                              </div>
                              </div>

                         

                             
                            </div>

                            <div style="float: right;" class="col-md-2 mt-5 " >
                              <div class="form-group " >
                                <a onclick="add_prise_charge()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><b id="loading_save"></b> <?= lang('messages_lang.label_enre') ?></a>
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
  $(document).ready(function () {

  });

</script>


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


  function hide_mofif(){
    if ($('#ID_CONTROLE_COMPTABLE').val()==''){
      $('#motif_refus').show();

    }else{
      $('#motif_refus').hide();
    }

  } 
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
</script>

<script type="text/javascript">

  function add_prise_charge(){

    let ID_CONTROLE_COMPTABLE=$('#ID_CONTROLE_COMPTABLE').val();
    let MOTIF_REFUS=$('#MOTIF_REFUS').val();
    let EXECUTION_BUDGETAIRE_ID=$('#EXECUTION_BUDGETAIRE_ID').val();
    let ETAPE_ACTUELLE_ID=$('#ETAPE_ACTUELLE_ID').val();
    
   // reset error message_error
   $('#error_MOTIF_REFUS').html('');
   $('#error_ID_CONTROLE_COMPTABLE').html('');

      //start validation
      let isFormValid = true;

      if(ID_CONTROLE_COMPTABLE== ""){
        $('#error_ID_CONTROLE_COMPTABLE').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }
  

      if(!isFormValid) return;
    //do the post request
    let form = new FormData();
    form.append("ID_CONTROLE_COMPTABLE",ID_CONTROLE_COMPTABLE);
    form.append("MOTIF_REFUS",MOTIF_REFUS);
    form.append("EXECUTION_BUDGETAIRE_ID",EXECUTION_BUDGETAIRE_ID);
    form.append("ETAPE_ACTUELLE_ID",ETAPE_ACTUELLE_ID);
   
      $.ajax(
     {
      url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/save_prise_Charge')?>",
      type:"POST",
      dataType:"JSON",
      data: form,
      processData: false,  
      contentType: false,
      beforeSend:function() {
        $('#btn_save').attr('disabled',true);
      },
      success: function(data)
      { 
        $('#btn_save').attr('disabled',false);
        if(data.message)
        {
          
          window.location.href = "<?=base_url('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge')?>";
        }

        if(data.errors)
        {
          let errors = data.errors;
          $('#error_ID_CONTROLE_COMPTABLE').html(data.errors.ID_CONTROLE_COMPTABLE);
          $('#error_MOTIF_REFUS').html(data.errors.MOTIF_REFUS);
          
        }
        }


      });



    }


</script>



