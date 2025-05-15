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
                    <a href="<?php echo base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Autres_Retenus')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape1?> - Autres retenues</h4>
                    <br>

                    <div class=" container " style="width:100%">
                      <form enctype='multipart/form-data' name="myFormpc" id="myFormpc" action="" method="post" >
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
                            <div class="col-md-12"style="margin-bottom:20px" >
                              <div class="row">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$execution['EXECUTION_BUDGETAIRE_ID']?>">                      
                                  <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$execution['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                                  <input type="hidden" name="ETAPE_ACTUELLE_ID" id="ETAPE_ACTUELLE_ID" value="<?=$etape_actuel?>">
                                  <input type="hidden" name="MONTANT_DECAISSE" id="MONTANT_DECAISSE">
                                  <input type="hidden" name="MONTANT_RESTANT" id="MONTANT_RESTANT" value="<?=$execution['AUTRES_RETENUS']?>">
                                  <input type="hidden" name="rest_autre" id="rest_autre" value="<?=!empty($autres_ret['MONTANT_PAIEMENT'])?$autres_ret['MONTANT_PAIEMENT']:0?>">

                                  <div class="col-md-6">
                                    <div class='form-froup'>
                                      <label class="form-label">Mois</label>
                                      <input type="text" readonly="" value="<?=$mois['DESC_MOIS']?>" class="form-control" name="MOIS_ID" id="MOIS_ID">
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
                                      <label class="form-label">Montant Autres retenus</label>
                                      <input onpaste="return false;" type="text" readonly="" class="form-control " name="AUTRES_RETENUS" id="AUTRES_RETENUS" placeholder=""  value="<?=number_format($execution['MONTANT_PAIEMENT'],0,'',' ')?>" onpaste="return false;" min="0"   >
                                      <font color="red" id="error_AUTRES_RETENUS"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('AUTRES_RETENUS'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div>
                                    <input type="hidden" name="engagement_budget" id="engagement_budget">
                                  </div>

                                  <!-- <div class="col-md-6" id="racc_bif">
                                    <div class="form-froup">
                                      <label class="form-label">Date Ordonnancement</label>
                                      <input onpaste="return false;" type="text" readonly="" class="form-control " name="DATE_ORDO" id="DATE_ORDO" placeholder="" value="<?//=date("d-m-Y",strtotime($DATE_ORDONNANCEMENT))?>" onpaste="return false;" min="0"   >
                                      <font color="red" id="error_DATE_ORDO"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?//= $validation->getError('DATE_ORDO'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div>
                                  </div> -->

                                  <!-- <div class="col-md-6">
                                    <div class='form-froup'>
                                      <label for="">Montant decaissé <font color="red">*</font></label>
                                      <input type="text" oninput="formatInputValue(this);calculer_montant_restant()" name="MONTANT_DECAISS" id="MONTANT_DECAISS"  class="form-control">
                                      <font color="red" id="error_MONTANT_DECAISS"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?//= $validation->getError('MONTANT_DECAISS'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div> 
                                  </div> -->

                                  <div class="col-md-6">
                                    <div class='form-froup'>
                                      <label for="">Numéro TD <font color="red">*</font></label>
                                      <input type="text" oninput="entiersselement(this)" name="NUMERO_TD" id="NUMERO_TD" class="form-control">
                                      <font color="red" id="error_NUMERO_TD"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('NUMERO_TD'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div> 
                                  </div>

                                  <div class="col-md-6">
                                    <label for="">Téléverser le TD<font color="red">*</font></label>
                                    <input onchange="ValidationFile();" accept=".pdf" type="file" name="PATH_NUMERO_TD" id="PATH_NUMERO_TD" class="form-control" >
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_PATH_NUMERO_TD"><?= $validation->getError('PATH_NUMERO_TD'); ?></font>
                                    <?php endif ?>
                                  </div>

                                  <div class="col-md-6">
                                    <div class='form-froup'>
                                      <label for="">Benéficaire</label>
                                      <input type="text" name="BENEFICIAIRE_TITRE_ID" id="BENEFICIAIRE_TITRE_ID" class="form-control" value="<?=$execution['DESC_BENEFICIAIRE']?>" readonly>
                                      <font color="red" id="error_BENEFICIAIRE_TITRE_ID"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('BENEFICIAIRE_TITRE_ID'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div> 
                                  </div>

                                  <div class="col-md-6" >
                                    <label for=""><?=lang('messages_lang.label_cpte_bak')?></label>
                                    <input type="text" class="form-control" name="COMPTE_CREDIT" id="COMPTE_CREDIT"><?=set_value('COMPTE_CREDIT')?>
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_COMPTE_CREDIT"><?= $validation->getError('COMPTE_CREDIT'); ?></font>
                                    <?php endif ?>
                                  </div>

                                  <div class="col-md-6">
                                    <div class='form-froup'>
                                      <label for=""><?=lang('messages_lang.banque_phase_comptable_prise_en_charge')?> <font color="red">*</font></label>
                                      <select name="BANQUE_ID" id="BANQUE_ID" class="form-control select2">
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>

                                        <?php 
                                        foreach ($banque as $keys)
                                        { 
                                          if($keys->BANQUE_ID==set_value('BANQUE_ID')) { ?>
                                            <option value="<?=$keys->BANQUE_ID ?>" selected>
                                              <?=$keys->NOM_BANQUE?>
                                            </option>
                                            <?php }else{?>
                                            <option value="<?=$keys->BANQUE_ID ?>">
                                              <?=$keys->NOM_BANQUE?></option>
                                            <?php 
                                          } 
                                        }?>
                                      </select>
                                      <font color="red" id="error_BANQUE_ID"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('BANQUE_ID'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div> 
                                  </div>

                                  <div class="col-md-6" >
                                    <label for="">Motif Décaissement <font color="red">*</font></label>
                                    <textarea class="form-control" name="MOTIF_DECAISS" id="MOTIF_DECAISS"><?=!empty(set_value('MOTIF_DECAISS'))?set_value('MOTIF_DECAISS'):$execution['MOTIF_PAIEMENT']?></textarea>
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_MOTIF_DECAISS"><?= $validation->getError('MOTIF_DECAISS'); ?></font>
                                    <?php endif ?>
                                  </div>
                                  
                              </div>
                              <!-- <div id="bouton_cart" class="col-md-12">
                                <br>
                                <button id="btn_add_Cart" onclick="add_inCart()" type="button" class="btn btn-primary float-end" style="float: right;" ><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;<?//=lang('messages_lang.bouton_ajouter')?> <span id="loading_cart"></span></button>
                              </div>
                              <br>-->
                              <br> 
                              <!-- <div class="col-md-12 table table-responsive" id="CART_FILE"></div> -->
                              <div style="float: right;" class="col-md-2">
                                <a onclick="add_edition_titre()" id="btn_save" class="btn" style="float:right;background:#061e69;color:white"><b id="loading_save"></b> <?= lang('messages_lang.label_enre') ?></a>
                              </div>
                            </div>
                          </div><br>
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
    $(document).ready(function () {
       // $('#btn_save').hide();
       // afficher();
   

    });

  // function afficher(){
  //  var EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();

  //     $.ajax(
  //     {
  //       url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/afficher_cart')?>/"+EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
  //       type:"POST",
  //       dataType:"JSON",
  //       data: {},
  //       processData: false,  
  //       contentType: false,
  //       beforeSend:function() {
  //        // $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
  //        // $('#btn_add_Cart').attr('disabled',true);
  //      },
  //      success: function(data)
  //      { 
  //        $('#loading_cart').html("");
  //        $('#btn_add_Cart').attr('disabled',false);
  //        $('#CART_FILE').html(data.cart);
  //        CART_FILE.innerHTML=data.cart;
  //        $('#SHOW_FOOTER').show();
  //        $('#btn_save').show();

  //        $('#MONTANT_DECAISSE').val(data.MONTANT_DECAISSE);
  //      }


  //    });

  // }

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
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
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

<script type="text/javascript">

  // function add_inCart(){

  //   let NUMERO_TD=$('#NUMERO_TD').val();
  //   var PATH_NUMERO_TD=document.getElementById("PATH_NUMERO_TD").files[0];
  //   let BENEFICIAIRE_TITRE_ID=$('#BENEFICIAIRE_TITRE_ID').val();
  //   let MOTIF_DECAISS=$('#MOTIF_DECAISS').val();
  //   let MONTANT_DECAISS=$('#MONTANT_DECAISS').val();
  //   let EXECUTION_BUDGETAIRE_ID=$('#EXECUTION_BUDGETAIRE_ID').val();
    
  //   let EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();

  //  // reset error message_error
  //  $('#error_PATH_NUMERO_TD').html('');
  //  $('#error_NUMERO_TD').html('');
  //  $('#error_BENEFICIAIRE_TITRE_ID').html('');
  //  $('#error_MOTIF_DECAISS').html('');
  //  $('#error_MONTANT_DECAISS').html('');

  //  //start validation
  //  let isFormValid = true;

  //     if(NUMERO_TD== ""){
  //       $('#error_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
  //       isFormValid =  false;
  //     }
  //     if($('#PATH_NUMERO_TD').val() == ""){
  //       $('#error_PATH_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
  //       isFormValid =  false;
  //     }
  //     if(BENEFICIAIRE_TITRE_ID== ""){
  //       $('#error_BENEFICIAIRE_TITRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
  //       isFormValid =  false;
  //     }
  //     if(MOTIF_DECAISS== ""){
  //       $('#error_MOTIF_DECAISS').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
  //       isFormValid =  false;
  //     }
  //     if(MONTANT_DECAISS== ""){
  //       $('#error_MONTANT_DECAISS').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
  //       isFormValid =  false;
  //     }

  //     if(!isFormValid) return;

  //     //do the post request
  //     let form = new FormData();
  //     form.append("NUMERO_TD",NUMERO_TD);
  //     form.append("PATH_NUMERO_TD",PATH_NUMERO_TD); 
  //     form.append("MONTANT_DECAISS",MONTANT_DECAISS);
  //     form.append("MOTIF_DECAISS",MOTIF_DECAISS);
  //     form.append("BENEFICIAIRE_TITRE_ID",BENEFICIAIRE_TITRE_ID);
  //     form.append("EXECUTION_BUDGETAIRE_ID",EXECUTION_BUDGETAIRE_ID);
  //     form.append("EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID",EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
      
  //       $.ajax(
  //     {
  //       url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/insert_tab_tempo')?>",
  //       type:"POST",
  //       dataType:"JSON",
  //       data: form,
  //       processData: false,  
  //       contentType: false,
  //       beforeSend:function() {
  //         $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
  //        $('#btn_add_Cart').attr('disabled',true);
  //      },
  //      success: function(data)
  //      { 
  //        $('#loading_cart').html("");
  //        $('#btn_add_Cart').attr('disabled',false);
  //        // $('#CART_FILE').html(data.cart);
  //        // CART_FILE.innerHTML=data.cart;
  //         afficher();
  //        $('#SHOW_FOOTER').show();
  //        $('#btn_save').show();
  //      }


  //    });

 
// }

//     //supprimer un element dans le panier
//      function delete_Cart(id){
//       $.ajax({ 
//        url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/delete_InCart')?>/"+id,
//        type:"POST",
//        dataType:"JSON",
//        data: {},
//       processData: false,  
//       contentType: false,
//       success: function(data)
//       { 
//        $('#loading_cart').html("");
//        $('#btn_add_Cart').attr('disabled',false);

//        afficher();

//        $('#SHOW_FOOTER').show();
//        $('#btn_save').show();
//      }

//      });
    
//      }


     //Enregistrer les TD
    function add_edition_titre()
    {
      let ETAPE_ACTUELLE_ID=$('#ETAPE_ACTUELLE_ID').val();
      let EXECUTION_BUDGETAIRE_ID=$('#EXECUTION_BUDGETAIRE_ID').val();
      let EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$('#EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID').val();

      let NUMERO_TD=$('#NUMERO_TD').val();
      var PATH_NUMERO_TD= $('#PATH_NUMERO_TD').val();
      var MOTIF_DECAISS=$('#MOTIF_DECAISS').val()
      var BANQUE_ID=$('#BANQUE_ID').val()
      var COMPTE_CREDIT=$('#COMPTE_CREDIT').val()
      var isFormValid =  true;
      $('#error_NUMERO_TD,error_PATH_NUMERO_TD,error_MOTIF_DECAISS,error_BANQUE_ID').html('')
      if(NUMERO_TD== "")
      {
        $('#error_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }
      if(PATH_NUMERO_TD == "")
      {
        $('#error_PATH_NUMERO_TD').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }
      if(MOTIF_DECAISS== "")
      {
        $('#error_MOTIF_DECAISS').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }
      if (BANQUE_ID=="")
      {
        $('#error_BANQUE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }

      if (isFormValid)
      {
        var PATH_NUMERO_TD1=document.getElementById("PATH_NUMERO_TD").files[0];
        let form = new FormData();
        form.append("ETAPE_ACTUELLE_ID",ETAPE_ACTUELLE_ID);
        form.append("EXECUTION_BUDGETAIRE_ID",EXECUTION_BUDGETAIRE_ID);
        form.append("EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID",EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID);
        form.append("NUMERO_TD",NUMERO_TD);
        form.append("PATH_NUMERO_TD",PATH_NUMERO_TD1); 
        form.append("MOTIF_DECAISS",MOTIF_DECAISS);
        form.append("BANQUE_ID",BANQUE_ID);
        form.append("COMPTE_CREDIT",COMPTE_CREDIT);

        $.ajax({
          url:"<?=base_url('double_commande_new/Phase_Comptable_Salaire/save_edition_TD')?>/"+2,
          type:"POST",
          dataType:"JSON",
          data: form,
          processData: false,  
          contentType: false,
          beforeSend:function() {
            $('#loading_save').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            $('#btn_save').attr('disabled',true);
          },
          success: function(data)
          { 
            $('#loading_save').html("");
            $('#btn_save').attr('disabled',true);

            window.location.href = "<?=base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Autres_Retenus')?>";            
          }
        });
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
//   function calculer_montant_restant(){
//     $('#btn_add_Cart').attr('disabled',false);
//     $('#error_MONTANT_DECAISS').text('');
//     var MONTANT_DECAISS=$('#MONTANT_DECAISS').val();
//     MONTANT_DECAISS= parseInt(MONTANT_DECAISS.replace(/[^0-9.]/g, ''));
//   //montant deja decaisse dans cart 
//   var MONTANT_DECAISSEE=$('#MONTANT_DECAISSE').val();
//   if (MONTANT_DECAISSEE!='' || MONTANT_DECAISSEE>0)
//   {
//     MONTANT_DECAISS=parseFloat(MONTANT_DECAISS)+parseFloat(MONTANT_DECAISSEE);
//   }
  
//   var MONTANT_RESTANT=$('#MONTANT_RESTANT').val();
//    MONTANT_RESTANT=MONTANT_RESTANT.replace(/[^0-9.]/g, '');
//    var rest_autre=$('#rest_autre').val()
//    MONTANT_RESTANT=parseFloat(MONTANT_RESTANT)-parseFloat(rest_autre)

//   if (MONTANT_DECAISS >MONTANT_RESTANT) {
//     $('#error_MONTANT_DECAISS').text('<?=lang('messages_lang.mount_sup')?>');
//     $('#btn_add_Cart').attr('disabled',true);
//   }else{
//     $('#error_MONTANT_DECAISS').text('');
//     $('#btn_add_Cart').attr('disabled',false);

//   }
// }
</script>

 <!-- Permettre les entiers et les - & / seulement  -->
<script type="text/javascript">
  function entiersselement(input) {
 
    var numericValue = input.value.replace(/[^0-9/-]/g, '');

    input.value = numericValue;
}
</script>


