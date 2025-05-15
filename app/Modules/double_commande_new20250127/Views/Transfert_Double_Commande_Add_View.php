<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>

  <style>
    .vl {
      border-left: 1px solid #ddd;
      height: 250px;
      position: absolute;
      left: 100%;
      margin-left: -3px;
      top: 0;
    }
  </style>

  <style>
    .vl2 {
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
        <div class="container-fluid overflow-auto">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">

                <div class="car-body">


                  <div class="row">
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('double_commande_new/Transfert_Double_Commande/send_data/')?>" method="post" >

                      <div class="col-12">
                        <h6 style="font-size: 18px" class="header-title text-black">
                          <?= lang('messages_lang.label_raccr_transfer') ?>
                        </h6>
                      </div>

                      <div class="col-12">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin :  5px">

                           <div class="col-12">
                            <div class="row">

                              <div class="col-12">
                                <div class="form-group">
                                  <label><?= lang('messages_lang.label_motif') ?><span style="color: red;">*</span></label>

                                  <select  class="form-control" name="MOTIF_TACHE_ID" id="MOTIF_TACHE_ID" onclick="hierarchie(this.value)">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php  foreach ($motif as $key) { ?>
                                      <?php  if ($key->MOTIF_TACHE_ID == set_value('MOTIF_TACHE_ID')) { ?>
                                       <option value="<?=$key->MOTIF_TACHE_ID ?>" selected>
                                        <?=$key->DESCR_MOTIF_ACTIVITE?></option>
                                      <?php }else{?>
                                       <option value="<?=$key->MOTIF_TACHE_ID ?>">
                                        <?=$key->DESCR_MOTIF_TACHE?></option>
                                      <?php }}?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('MOTIF_TACHE_ID'); ?>
                                    <?php endif ?>
                                    <font color="red" id="error_MOTIF_TACHE_ID"></font>
                                  </div>

                                </div>
                                <div class="col-4" id="respo1" style="display: none;">
                                  <div class="form-group">
                                    <label><?= lang('messages_lang.labelle_nom') ?><span style="color: red;">*</span></label>
                                    <input onkeyup="SetMaxLength(1)" autocomplete="off" type="text" name="NOM" id="NOM" class="form-control" value="<?= set_value('NOM')?>" >
                                    <font color="red" id="error_NOM"></font>
                                  </div>
                                </div>

                                <div class="col-4" id="respo2" style="display: none;">
                                  <div class="form-group">
                                    <label><?= lang('messages_lang.labelle_prenom') ?><span style="color: red;">*</span></label>
                                    <input onkeyup="SetMaxLength(2)" autocomplete="off" type="text" name="PRENOM" id="PRENOM" class="form-control" value="<?= set_value('PRENOM')?>" >
                                    <font color="red" id="error_PRENOM"></font>
                                  </div>
                                </div>

                                <div class="col-4" id="respo3" style="display: none;">
                                  <div class="form-group">
                                    <label><?= lang('messages_lang.poste') ?><span style="color: red;">*</span></label>
                                    <input onkeyup="SetMaxLength(3)" autocomplete="off" type="text" name="POSTE" id="POSTE" class="form-control" value="<?= set_value('POSTE')?>" >
                                    <font color="red" id="error_POSTE"></font>
                                  </div>
                                </div>

                              </div>
                            </div>
                          </div>

                          <!-- Ligne bidgetaire qui envoie -->
                          <div class="col-12">
                            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                              <div class="row" style="margin :  5px">

                                <div class="col-12">
                                  <h4><center> <i class="fa fa-certificate"></i><?= lang('messages_lang.label_trans_orig') ?></center></h4><br>
                                </div>

                                <div class="col-12">
                                  <div class="row">
                                    <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                                    <div class="col-6">
                                      <label class="form-label"><?= lang('messages_lang.labelle_inst_min') ?> <span style="color: red">*</span></label>
                                      <select autofocus onchange="get_sousTutel();get_inst();" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>
                                        <?php foreach ($institution as $keyinstitution) { ?>
                                          <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                            <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                          <?php }?>
                                        </select>
                                        <font color="red" id="error_INSTITUTION_ID"></font>
                                      </div>

                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <label id="label_sous_tutel"><?= lang('messages_lang.label_sousTitre') ?> <span style="color: red">*</span> <span id="loading_sous_tutel"></span></label>
                                          <select onchange="get_code()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                                            <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          </select>
                                          <font color="red" id="error_SOUS_TUTEL_ID"></font>
                                        </div>
                                      </div>

                                      <div class="col-6">
                                        <label class="form-label"><?= lang('messages_lang.labelle_code_budgetaire') ?> <span style="color: red">*</span> <b id="loading_budget"></b></label>
                                        <select class="form-control select2" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" onchange="get_activite();get_taches();" >
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                        </select>
                                        <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID"></font>
                                      </div>

                                      <div class="col-6" id="act_id" hidden="true" >
                                        <label class="form-label"><?= lang('messages_lang.labelle_activite') ?> <span style="color: red">*</span> <b id="loading_act"></b></label>
                                        <select onchange="get_taches()" class="form-control" id="PTBA_ID" name="PTBA_ID">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                        </select>
                                        <font color="red" id="error_PTBA_ID"></font>
                                      </div>

                                      <div class="col-6">
                                        <label class="form-label"><?= lang('messages_lang.label_taches') ?> <span style="color: red">*</span> <span id="loading_tache"></span></label>
                                        <select class="form-control" id="PTBA_TACHE_ID" name="PTBA_TACHE_ID">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                        </select>
                                        <font color="red" id="error_PTBA_TACHE_ID"></font>
                                      </div>

                                      <div class="col-6">
                                        <label class="form-label"><?= lang('messages_lang.labelle_tranche') ?> <span style="color: red">*</span></label>
                                        <select onchange="getMontantAnnuel()" class="form-control" id="TRIMESTRE_ID" name="TRIMESTRE_ID">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          <?php foreach ($tranches as $keytranches) { ?>
                                            <option value="<?=$keytranches->TRIMESTRE_ID?>">
                                              <?=$keytranches->DESC_TRIMESTRE?></option>
                                            <?php }?>
                                          </select>
                                          <font color="red" id="error_TRIMESTRE_ID"></font>
                                          <font color="red" id="error_TRIMESTRE_ID2"></font>
                                        </div>

                                        <div class="col-6">
                                          <label id="montant_vote_label" class="form-label"><?= lang('messages_lang.labelle_montant_vote') ?> <span id="loading_vote"></span></label>
                                          <input type="text" name="MONTANT_VOTE" id="MONTANT_VOTE" class="form-control">
                                        </div>

                                        <div class="col-6">
                                          <label class="form-label"><?= lang('messages_lang.label_Money_res') ?> <span id="loading_montant_restant"></span></label>
                                          <input type="text" name="MONTANT_RESTANT" id="MONTANT_RESTANT" class="form-control">
                                        </div>

                                        <div class="col-6">
                                          <label class="form-label"><?= lang('messages_lang.mont_a_transf') ?> <span style="color: red">*</span></label>
                                          <input onkeyup="get_MontantApresTransfert()" type="text" class="form-control" name="MONTANT_TRANSFERT" id="MONTANT_TRANSFERT">
                                          <font color="red" id="error_MONTANT_TRANSFERT"></font>
                                          <font color="red" id="error_MONTANT_TRANSFERT_SUP"></font>
                                          <font color="red" id="error_MONTANT_TRANSFERT_SUP2"></font>
                                        </div>

                                      </div>
                                    </div>

                                  </div>
                                </div>
                              </div>



                              <!-- Ligne bidgetaire qui recois -->
                              <div class="col-12">
                                <div style="border:1px solid #ddd;border-radius:5px">
                                  <div class="row" style="margin :  5px">

                                    <div class="col-12">
                                      <h4><center> <i class="fa fa-circle"></i> <?= lang('messages_lang.label_trans_destin') ?> </center></h4><br>
                                    </div>


                                    <div class="col-12">
                                      <div class="row">
                                        <input type="hidden" name="TYPE_INSTITUTION_ID2" id="TYPE_INSTITUTION_ID2">
                                        <div class="col-6">
                                          <label class="form-label"><?= lang('messages_lang.labelle_inst_min') ?> <span style="color: red">*</span></label>
                                          <select autofocus onchange="get_sousTutel2();get_inst2();" class="form-control select2" id="INSTITUTION_ID2" name="INSTITUTION_ID2">
                                            <option value=""><?= lang('messages_lang.label_select') ?></option>
                                            <?php foreach ($institution_rec as $keyinstitution) { ?>
                                              <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                                <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                              <?php }?>
                                            </select>
                                            <font color="red" id="error_INSTITUTION_ID2"></font>
                                          </div>

                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label id="label_sous_tutel"><?= lang('messages_lang.table_st') ?> <span style="color: red">*</span> <span id="loading_sous_tutel2"></span></label>
                                              <select onchange="get_code2()" class="form-control" name="SOUS_TUTEL_ID2" id="SOUS_TUTEL_ID2">
                                                <option value=""><?= lang('messages_lang.label_select') ?></option>
                                              </select>
                                              <font color="red" id="error_SOUS_TUTEL_ID2"></font>
                                            </div>
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.labelle_code_budgetaire') ?> <span style="color: red">*</span> <span id="loading_code2"></span></label>
                                            <select class="form-control select2" id="CODE_NOMENCLATURE_BUDGETAIRE_ID2" name="CODE_NOMENCLATURE_BUDGETAIRE_ID2" onchange="get_activite2();get_taches2();" >
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                            </select>
                                            <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID2"></font>
                                          </div>

                                          <div class="col-6"  id="act_id2" hidden="true"  >
                                            <label class="form-label"><?= lang('messages_lang.labelle_activite') ?> <span style="color: red">*</span> <span id="loading_act2"></span></label>
                                            <select onchange="get_taches2()" class="form-control" id="PTBA_ID2" name="PTBA_ID2">
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                            </select>
                                            <font color="red" id="error_PTBA_ID2"></font>
                                            <font color="red" id="error_MONTANT_TRANSFERT2"></font>
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.label_taches') ?> <span style="color: red">*</span> <span id="loading_tache2"></span></label>
                                            <select class="form-control" id="PTBA_TACHE_ID2" name="PTBA_TACHE_ID2">
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                            </select>
                                            <font color="red" id="error_PTBA_TACHE_ID2"></font>
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.label_term_dest') ?> <span style="color: red">*</span></label>
                                            <select onchange="get_MontantVoteByActivite();" class="form-control" id="TRIMESTRE_ID_DESTINATION" name="TRIMESTRE_ID_DESTINATION">
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                              <?php foreach ($trim_destination as $keytranches) { ?>
                                                <option value="<?=$keytranches->TRIMESTRE_ID?>">
                                                  <?=$keytranches->DESC_TRIMESTRE?></option>
                                                <?php }?>
                                            </select>
                                            <font color="red" id="error_TRIMESTRE_ID_DESTINATION"></font>
                                            <font color="red" id="error_TRIMESTRE_ID2_DESTINATION"></font>
                                          </div>

                                          <div class="col-6">
                                            <label id="montant_vote_label" class="form-label"><?= lang('messages_lang.labelle_montant_vote') ?> <span id="loading_vote2"></span></label>
                                            <input type="text" name="MONTANT_VOTE2" id="MONTANT_VOTE2" class="form-control">
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.label_Money_res') ?> <span id="loading_montant_restant2"></span></label>
                                            <input type="text" name="MONTANT_RESTANT2" id="MONTANT_RESTANT2" class="form-control">
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.montant_a_recevoir') ?> <span style="color: red">*</span></label>
                                            <input type="text" class="form-control" name="MONTANT_RECEVOIR" id="MONTANT_RECEVOIR">
                                            <font color="red" id="error_MONTANT_RECEVOIR"></font>
                                          </div>

                                          <div class="col-6">
                                            <label class="form-label"><?= lang('messages_lang.mont_tache_apr_trans') ?> <span style="color: red">*</span> <span id="loading_montant_apres_trans"></span></label>
                                            <input type="text" class="form-control" name="MONTANT_APRES_TRANSFERT" id="MONTANT_APRES_TRANSFERT">
                                          </div>

                                        </div>
                                      </div>



                                    </div>
                                  </div>
                                </div>

                                <br>
                                <div class="col-12">
                                  <div style="border:1px solid #ddd;border-radius:5px">
                                    <div class="row" style="margin :  5px">
                                      <div class="col-6">
                                        <div class="form-group">
                                          <label><?= lang('messages_lang.label_auto_trans') ?><span style="color: red;">*</span></label>
                                          <input onchange="ValidationFile()" accept=".pdf" type="file" name="AUTORISATION_TRANSFERT" id="AUTORISATION_TRANSFERT" class="form-control" >
                                          <font color="red" id="error_AUTORISATION_TRANSFERT"></font>
                                          <font color="red" id="error_AUTORISATION_TRANSFERT_VOLUMINEUX"></font>
                                          <font color="red" id="error_AUTORISATION_TRANSFERT_FORMAT"></font>
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
                                      <button  id="bouton_envoyer" onclick="addToCart()" type="button" class="btn btn-primary btn-block"><?= lang('messages_lang.bouton_ajouter') ?> <span id="loading_cart"></span> <span id="message"></span></button>
                                    </div>
                                  </div>
                                </div><br>

                              </form>

                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12"  id="div_btnSendData" hidden="">
                    <div style="border:1px solid #ddd;border-radius:5px">
                      <div class="row" style="margin :  5px">
                        <div id="mycart" class="col-12 table-responsive"></div>

                        <br>
                        <div class="col-12"><br>
                         <button  onclick="send_data()" type="button" class="btn btn-primary btn-block"><?= lang('messages_lang.bouton_enregistrer') ?></button>
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

          function ValidationFile()
          {
            var fileInput = document.getElementById('AUTORISATION_TRANSFERT');
            var filePath = fileInput.value;
            // Allowing file type
            var allowedExtensions = /(\.pdf)$/i;
            
            if (!allowedExtensions.exec(filePath))
            {
              $('#error_AUTORISATION_TRANSFERT_FORMAT').text("<?= lang('messages_lang.bordereau_message') ?>");
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
                  if (file > 8*1024)
                  { 
                    $('#error_AUTORISATION_TRANSFERT_VOLUMINEUX').text('<?= lang('messages_lang.taille_bordereau_message') ?>');
                    fileInput.value = '';
                  }else
                  {
                   $('#error_AUTORISATION_TRANSFERT_VOLUMINEUX').text(''); 
                 }
               } 
             }
           }
         }

         function show()
         { 

          if($("#FRAIS_OUI").prop("checked")) {   

            $('#error_MOTIF_TACHE_ID').html('');
            $('#MOTIF_TACHE_ID').val('');
            $("#rep").css("display", "none");

            $('#NOM').val('');
            $('#PRENOM').val('');
            $('#POSTE').val('');

            $('#error_NOM').html('');
            $('#error_PRENOM').html('');
            $('#error_POSTE').html(''); 
            $('#respo1').hide();
            $('#respo2').hide();
            $('#respo3').hide();
          } 

          if($("#FRAIS_NON").prop("checked")) {   

            var validationSet = <?php echo isset($validation) ? 'true' : 'false'; ?>;

            if (validationSet){

              $("#rep").css("display", "block");

            }else{

              $('#error_MOTIF_TACHE_ID').html('');
              $('#MOTIF_TACHE_ID').val('');
              $("#rep").css("display", "block");
            }
          }   
        }

        function hierarchie() {    

          var MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();

          if(MOTIF_TACHE_ID !='')
          {
            if (MOTIF_TACHE_ID == 2 || MOTIF_TACHE_ID == 3)
            {
              window.addEventListener('load', function() {

                var validationSet = <?php echo isset($validation) ? 'true' : 'false'; ?>;

                if (validationSet){

                  $('#NOM').val();
                  $('#PRENOM').val();
                  $('#POSTE').val();
                  $('#respo1').show();  
                  $('#respo2').show();  
                  $('#respo3').show();  

                }else{

                  $('#NOM').val('');
                  $('#PRENOM').val('');
                  $('#POSTE').val('');

                  $('#error_NOM').html('');
                  $('#error_PRENOM').html('');
                  $('#error_POSTE').html('');
                  $('#respo1').show();  
                  $('#respo2').show();  
                  $('#respo3').show();  

                }

              });

              $('#respo1').show();   
              $('#respo2').show();   
              $('#respo3').show();   

            }else {

              $('#NOM').val('');
              $('#PRENOM').val('');
              $('#POSTE').val('');

              $('#error_NOM').html('');
              $('#error_PRENOM').html('');
              $('#error_POSTE').html(''); 
              $('#respo1').hide();
              $('#respo2').hide();
              $('#respo3').hide();
            }   

          }else{

            $('#NOM').val('');
            $('#PRENOM').val('');
            $('#POSTE').val('');

            $('#error_NOM').html('');
            $('#error_PRENOM').html('');
            $('#error_POSTE').html(''); 
            $('#respo1').hide();
            $('#respo2').hide();
            $('#respo3').hide();
          }
        } 
      </script>

    <!---------------  Les dépendances  -------------------> 
    <script type="text/javascript">
      function get_sousTutel()
      {
        var INSTITUTION_ID = $('#INSTITUTION_ID').val();

        if(INSTITUTION_ID=='')
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#SOUS_TUTEL_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          if (INSTITUTION_ID==12)
          {
            $('#lettre_id').text("");
          }
          else
          {
            $('#lettre_id').text("*");
          }

          $('#SOUS_TUTEL_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#TRIMESTRE_ID').val('');
          var url = "<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_sousTutel/"+INSTITUTION_ID;

          $.ajax(
          {

            url:url,
            type:"GET",
            dataType:"JSON",
            beforeSend:function() {
              $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success:function(data)
            {   
              $('#SOUS_TUTEL_ID').html(data.SousTutel);
              $('#loading_sous_tutel').html("");
            }
          });
        }
      }

      function get_inst()
      {
        var INSTITUTION_ID = $('#INSTITUTION_ID').val();
        if(INSTITUTION_ID=='')
        {
          $('#TYPE_INSTITUTION_ID').val(0);
        }
        else
        {
          $.ajax(
          {
            url:"<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_inst/"+INSTITUTION_ID,
            type:"POST",
            dataType:"JSON",
            success: function(data)
            {
              $('#TYPE_INSTITUTION_ID').val(data.inst_activite);

              if(data.inst_activite == 2)
              {
                $('#act_id').attr('hidden', false);
              }
              else
              {
                $('#act_id').attr('hidden', true);
              }
            }
          });

         
        }
      }


      function get_code()
      {
        var INSTITUTION_ID = $('#INSTITUTION_ID').val();
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
        if(SOUS_TUTEL_ID=='')
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#TRIMESTRE_ID').val('');
          var url = "<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_code/"+SOUS_TUTEL_ID;

          $.ajax(
          {
            url:url,
            type:"POST",
            dataType:"JSON",
            data:{
              INSTITUTION_ID:INSTITUTION_ID,
            },
            beforeSend:function()
            {
              $('#loading_budget').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data)
            {
              $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html(data.codeBudgetaire);
              $('#loading_budget').html("");

            }
          });
        }
      }

      function get_activite()
      {
        var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
        if(CODE_NOMENCLATURE_BUDGETAIRE_ID=='')
        {
          $('#PTBA_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {

          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#TRIMESTRE_ID').val('');
          $.ajax(
          {
            url:"<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_activite1/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
            type:"GET",
            dataType:"JSON",         
            beforeSend:function()
            {
              $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data)
            {
              $('#PTBA_ID').html(data.activite);
              $('#loading_act').html("");
            }
          });

        }
      }

      function get_taches() 
      {
        var PTBA_ID = $('#PTBA_ID').val();
        var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
        var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
        //alert(TYPE_INSTITUTION_ID);
        var id = '';

        if (TYPE_INSTITUTION_ID == 1) {
          id = CODE_NOMENCLATURE_BUDGETAIRE_ID;
        } else if (TYPE_INSTITUTION_ID == 2) {
          id = PTBA_ID;
        } else {
          id = '';
        }

        if (id == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == '') {
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        } else {
          $('#TRIMESTRE_ID').val('');
          $.ajax({
            url: "<?=base_url('')?>/double_commande_new/Transfert_Double_Commande/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
            type: "GET",
            dataType: "JSON",
            data: {
              PTBA_ID: PTBA_ID,
              CODE_NOMENCLATURE_BUDGETAIRE_ID: CODE_NOMENCLATURE_BUDGETAIRE_ID,
              TYPE_INSTITUTION_ID: TYPE_INSTITUTION_ID,
            },
            beforeSend: function() {
              $('#loading_tache').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data) {
              $('#PTBA_TACHE_ID').html(data.tache_activite);
              $('#loading_tache').html("");
            }
          });
        }
      }


      function getMontantAnnuel(argument)
      {
        var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
        var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();

        if (TRIMESTRE_ID=='')
        {
          $('#MONTANT_VOTE').val('');
          $('#MONTANT_RESTANT').val('');
          $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote') ?>");
        }

        if (PTBA_TACHE_ID=='')
        {
          $('#error_TRIMESTRE_ID2').text("<?= lang('messages_lang.message_selection_activite') ?>");
          $('#TRIMESTRE_ID').val('')
        }else{

          $('#error_TRIMESTRE_ID2').text("");
          $('#error_MONTANT_TRANSFERT_SUP').text("");
          $.ajax(
          {
            url:"<?=base_url('/double_commande_new/Transfert_Double_Commande/getMontantAnnuel')?>",
            type:"POST",
            dataType:"JSON",
            data: {
              PTBA_TACHE_ID:PTBA_TACHE_ID,
              TRIMESTRE_ID:TRIMESTRE_ID
            },
            beforeSend:function() {
              $('#loading_vote').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
              $('#loading_montant_restant').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data)
            {
              if (TRIMESTRE_ID==5) {
                // $('#MONTANT_TRANSFERT').attr('disabled',true);
                document.getElementById('MONTANT_TRANSFERT').readOnly = true;
                $('#MONTANT_TRANSFERT').val(data.MONTANT_TRANSFERT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
                $('#MONTANT_RECEVOIR').val(data.MONTANT_TRANSFERT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
                $('#MONTANT_RECEVOIR').attr('disabled',true);
                $('#MONTANT_VOTE').val(data.MONTANT_VOTE.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
                $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote_annuel') ?>");
                $('#error_MONTANT_TRANSFERT_SUP2').text("");
              }else{
                if (TRIMESTRE_ID==1) {
                  var DESC_TRANCHE = 'première';
                }else if (TRIMESTRE_ID==2) {
                  var DESC_TRANCHE = 'deuxième';
                }else if (TRIMESTRE_ID==3) {
                  var DESC_TRANCHE = 'troisième';
                }else if (TRIMESTRE_ID==4) {
                  var DESC_TRANCHE = 'quatrième';
                }
                $('#MONTANT_TRANSFERT').val('');
                $('#error_MONTANT_TRANSFERT_SUP2').text("");
                // $('#MONTANT_TRANSFERT').attr('disabled',false);
                document.getElementById('MONTANT_TRANSFERT').readOnly = false;
                $('#MONTANT_VOTE').val(data.MONTANT_VOTE.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
                $('#MONTANT_RESTANT').val(data.MONTANT_RESTANT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
                $('#MONTANT_RECEVOIR').val('');
                $('#MONTANT_RECEVOIR').attr('disabled',false);
                $('#montant_vote_label').text("<?= lang('messages_lang.labelle_montant_vote') ?> "+DESC_TRANCHE+" <?= lang('messages_lang.trim') ?>");
              }
              $('#loading_vote').html("");
              $('#loading_montant_restant').html("");
            }
          });
        }

      }
    </script>

    <!--------------   Les dépendances 2 ------------------>
    <script type="text/javascript">
      function get_sousTutel2()
      {
        var INSTITUTION_ID2 = $('#INSTITUTION_ID2').val();

        if(INSTITUTION_ID2=='')
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
           $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#SOUS_TUTEL_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          if (INSTITUTION_ID2==12)
          {
            $('#lettre_id2').text("");
          }
          else
          {
            $('#lettre_id2').text("*");
          }

          $('#SOUS_TUTEL_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          var url = "<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_sousTutel2/"+INSTITUTION_ID2;

          $.ajax(
          {

            url:url,
            type:"GET",
            dataType:"JSON",
            beforeSend:function() {
              $('#loading_sous_tutel2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success:function(data)
            {   
              $('#SOUS_TUTEL_ID2').html(data.SousTutel);
              $('#loading_sous_tutel2').html("");
            }
          });
        }
      }

      function get_inst2()
      {
        var INSTITUTION_ID2 = $('#INSTITUTION_ID2').val();
        if(INSTITUTION_ID2=='')
        {
          $('#TYPE_INSTITUTION_ID2').val(0);
        }
        else
        {
          $.ajax(
          {
            url:"<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_inst2/"+INSTITUTION_ID2,
            type:"POST",
            dataType:"JSON",
            success: function(data)
            {
              $('#TYPE_INSTITUTION_ID2').val(data.inst_activite);

              if(data.inst_activite == 2)
              {
                $('#act_id2').attr('hidden', false);
              }
              else
              {
                $('#act_id2').attr('hidden', true);
              }
            }
          });

          
        }
      }


      function get_code2()
      {
        var INSTITUTION_ID2 = $('#INSTITUTION_ID2').val();
        var SOUS_TUTEL_ID2=$('#SOUS_TUTEL_ID2').val();
        if(SOUS_TUTEL_ID2=='')
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          var url = "<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_code2/"+SOUS_TUTEL_ID2;

          $.ajax(
          {
            url:url,
            type:"POST",
            dataType:"JSON",
            data:{
              INSTITUTION_ID2:INSTITUTION_ID2,
            },
            beforeSend:function()
            {
              $('#loading_budget2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data)
            {
              $('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').html(data.codeBudgetaire);
              $('#loading_budget2').html("");

            }
          });
        }
      }

      function get_activite2()
      {
        var CODE_NOMENCLATURE_BUDGETAIRE_ID2=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').val();
        if(CODE_NOMENCLATURE_BUDGETAIRE_ID2=='')
        {
          $('#PTBA_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $.ajax(
          {
            url:"<?=base_url()?>/double_commande_new/Transfert_Double_Commande/get_activite2/"+CODE_NOMENCLATURE_BUDGETAIRE_ID2,
            type:"GET",
            dataType:"JSON",         
            beforeSend:function()
            {
              $('#loading_act2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data)
            {
              $('#PTBA_ID2').html(data.activite);
              $('#loading_act2').html("");
            }
          });

        }
      }

      function get_taches2() 
      {
        var PTBA_ID2 = $('#PTBA_ID2').val();
        var CODE_NOMENCLATURE_BUDGETAIRE_ID2 = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').val();
        var TYPE_INSTITUTION_ID2 = $('#TYPE_INSTITUTION_ID2').val();
        //alert(TYPE_INSTITUTION_ID2);
        var id = '';

        if (TYPE_INSTITUTION_ID2 == 1) {
          id = CODE_NOMENCLATURE_BUDGETAIRE_ID2;
        } else if (TYPE_INSTITUTION_ID2 == 2) {
          id = PTBA_ID2;
        } else {
          id = '';
        }

        if (id == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID2 == '') {
          $('#PTBA_TACHE_ID2').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        } else {
          $.ajax({
            url: "<?=base_url('')?>/double_commande_new/Transfert_Double_Commande/get_taches2/" + id+"/"+TYPE_INSTITUTION_ID2,
            type: "GET",
            dataType: "JSON",
            data: {
              PTBA_ID2: PTBA_ID2,
              CODE_NOMENCLATURE_BUDGETAIRE_ID2: CODE_NOMENCLATURE_BUDGETAIRE_ID2,
              TYPE_INSTITUTION_ID2: TYPE_INSTITUTION_ID2,
            },
            beforeSend: function() {
              $('#loading_tache2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data) {
              $('#PTBA_TACHE_ID2').html(data.tache_activite);
              $('#loading_tache2').html("");
            }
          });
        }
      }


      function get_MontantVoteByActivite()
      { 
        var PTBA_TACHE_ID2=$('#PTBA_TACHE_ID2').val();
        var TRIMESTRE_ID_DESTINATION = $('#TRIMESTRE_ID_DESTINATION').val();
        var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val().replace(/\s/g, '');

        $('#error_MONTANT_TRANSFERT').text("");
        if (MONTANT_TRANSFERT=='') {
          $('#error_MONTANT_TRANSFERT').text("<?= lang('messages_lang.error_money_preciz') ?>");
          $('#error_MONTANT_TRANSFERT2').text("<?= lang('messages_lang.error_money_activ') ?>");
          $('#TRIMESTRE_ID_DESTINATION').val("");
          document.getElementById("MONTANT_TRANSFERT").focus();
        }else{

          $.ajax(
          {
            url:"<?=base_url('/double_commande_new/Transfert_Double_Commande/get_MontantVoteByActivite')?>",
            type:"POST",
            dataType:"JSON",
            data: {
              PTBA_TACHE_ID2:PTBA_TACHE_ID2,
              TRIMESTRE_ID_DESTINATION:TRIMESTRE_ID_DESTINATION
        },
        beforeSend:function() {
          $('#loading_montant_restant2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#loading_vote2').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#loading_montant_apres_trans').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          $('#MONTANT_VOTE2').val(data.MONTANT_VOTE.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
          $('#MONTANT_RESTANT2').val(data.MONTANT_RESTANT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));

          var MONT_APRES_TRANSF = parseFloat(data.MONTANT_RESTANT)+parseFloat(MONTANT_TRANSFERT);
          var mont_after = MONT_APRES_TRANSF.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

          $('#MONTANT_APRES_TRANSFERT').val(mont_after);

          $('#loading_montant_restant2').html("");
          $('#loading_vote2').html("");
          $('#loading_montant_apres_trans').html("");
        }
      });

        }
      }

    </script>


    <script type="text/javascript">

      function get_MontantApresTransfert()
      {

        var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val().replace(/\s/g, '');
        var MONTANT_RESTANT=$('#MONTANT_RESTANT').val().replace(/\s/g, '');  
        var MONTANT_RESTANT2=$('#MONTANT_RESTANT2').val().replace(/\s/g, '');  
        var PTBA_TACHE_ID2=$('#PTBA_TACHE_ID2').val();  

        if (MONTANT_TRANSFERT!='') {

          $('#error_MONTANT_TRANSFERT').text('');
          $('#error_MONTANT_TRANSFERT2').text('');

          var getNumber = MONTANT_TRANSFERT.substring(0, 1);
          if (getNumber==0) {
            $('#MONTANT_TRANSFERT').val('');
          }else{
            if (parseFloat(MONTANT_TRANSFERT)>parseFloat(MONTANT_RESTANT)) {
              $('#error_MONTANT_TRANSFERT_SUP').text("<?= lang('messages_lang.error_moneyTrans_sup') ?>");
              $('#MONTANT_RECEVOIR').val('');
              $('#MONTANT_APRES_TRANSFERT').val('');
            }else{
              $('#error_MONTANT_TRANSFERT_SUP').text("");
              $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
            }
          }
        }else{
          $('#MONTANT_RECEVOIR').val('');
        }

        if (PTBA_TACHE_ID2!='') {

          var MONT_APRES_TRANSF = parseFloat(MONTANT_RESTANT2)+parseFloat(MONTANT_TRANSFERT);
          var mont_after = MONT_APRES_TRANSF.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
          //alert(mont_after);
          $('#MONTANT_APRES_TRANSFERT').val(mont_after);
        }
      }

      $("#MONTANT_TRANSFERT").on('input', function()
      {
        var value = $(this).val();
        value = value.replace(/[^0-9.]/g, '');
        $(this).val(value.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
      });

    </script>


          <script type="text/javascript">
            $(document).ready(function() {

              liste_tempo();

              show();
              hierarchie();

              $('#MONTANT_TRANSFERT').bind('paste', function (e) {
               e.preventDefault();
             });

              document.getElementById('MONTANT_RESTANT').readOnly = true;
              document.getElementById('MONTANT_VOTE').readOnly = true;
              document.getElementById('MONTANT_TRANSFERT').readOnly = true;

              document.getElementById('MONTANT_VOTE2').readOnly = true;
              document.getElementById('MONTANT_RESTANT2').readOnly = true;
              document.getElementById('MONTANT_RECEVOIR').readOnly = true;
              document.getElementById('MONTANT_APRES_TRANSFERT').readOnly = true;


            });
          </script>

          <script type="text/javascript">
            function liste_tempo() {

              $.ajax(
              { 
                url: '<?=base_url('/double_commande_new/Transfert_Double_Commande/liste_tempo')?>',
                type:"POST",
                dataType:"JSON",
                data: { 

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

              var INSTITUTION_ID=$('#INSTITUTION_ID').val()
              var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val()
              var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
              var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val()
              var PTBA_ID=$('#PTBA_ID').val()
              var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val()
              var TRIMESTRE_ID=$('#TRIMESTRE_ID').val()
              var MONTANT_VOTE=$('#MONTANT_VOTE').val().replace(/\s/g, '')
              var MONTANT_RESTANT=$('#MONTANT_RESTANT').val().replace(/\s/g, '')
              var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val().replace(/\s/g, '')

              var INSTITUTION_ID2=$('#INSTITUTION_ID2').val()
              var SOUS_TUTEL_ID2=$('#SOUS_TUTEL_ID2').val()
              var CODE_NOMENCLATURE_BUDGETAIRE_ID2=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID2').val()
              var PTBA_ID2=$('#PTBA_ID2').val()
              var PTBA_TACHE_ID2=$('#PTBA_TACHE_ID2').val()
              var TRIMESTRE_ID_DESTINATION=$('#TRIMESTRE_ID_DESTINATION').val();
              var MONTANT_VOTE2=$('#MONTANT_VOTE2').val().replace(/\s/g, '')
              var MONTANT_RESTANT2=$('#MONTANT_RESTANT2').val().replace(/\s/g, '')
              var MONTANT_RECEVOIR=$('#MONTANT_RECEVOIR').val().replace(/\s/g, '')

              var MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();
              var NOM = $('#NOM').val();
              var PRENOM = $('#PRENOM').val();
              var POSTE = $('#POSTE').val();
              var AUTORISATION_TRANSFERT = $('#AUTORISATION_TRANSFERT').val();

              if ($("#FRAIS_NON").prop("checked"))
              {
                if (MOTIF_TACHE_ID=="")
                {
                  $('#error_MOTIF_TACHE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                  return false;
                }else{

                  $('#error_MOTIF_TACHE_ID').text('');

                  if(MOTIF_TACHE_ID==2 || MOTIF_TACHE_ID==3)
                  {
                    if(NOM=="")
                    {
                      $('#error_NOM').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                      return false;
                    }else{
                      $('#error_NOM').text(''); 
                    }

                    if(PRENOM=="")
                    {
                      $('#error_PRENOM').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                      return false;
                    }else{
                      $('#error_PRENOM').text('');
                    }

                    if(POSTE=="")
                    {
                      $('#error_POSTE').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                      return false;
                    }else{
                      $('#error_POSTE').text('');
                    }
                  }
                }
              }

              if (INSTITUTION_ID=='') 
              {
                $('#error_INSTITUTION_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_INSTITUTION_ID').text('');
              }

              if (SOUS_TUTEL_ID=='') 
              {
                $('#error_SOUS_TUTEL_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_SOUS_TUTEL_ID').text('');
              }

              if (CODE_NOMENCLATURE_BUDGETAIRE_ID=='') 
              {
                $('#error_CODE_NOMENCLATURE_BUDGETAIRE').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').text('');
              }

              
              if (TYPE_INSTITUTION_ID == 2)
              {
                if (PTBA_ID=='') 
                {
                  $('#error_PTBA_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                  return false;
                }else{
                  $('#error_PTBA_ID').text('');
                }
              }


              if (PTBA_TACHE_ID=='') 
              {
                $('#error_PTBA_TACHE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_PTBA_TACHE_ID').text('');
              }

              if (TRIMESTRE_ID=='') 
              {
                $('#error_TRIMESTRE_ID').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_TRIMESTRE_ID').text('');
              }

              if (MONTANT_TRANSFERT=='') 
              {
                $('#error_MONTANT_TRANSFERT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_MONTANT_TRANSFERT').text('');
              }

              if (INSTITUTION_ID2=='') 
              {
                $('#error_INSTITUTION_ID2').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_INSTITUTION_ID2').text('');
              }

              if (SOUS_TUTEL_ID2=='') 
              {
                $('#error_SOUS_TUTEL_ID2').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_SOUS_TUTEL_ID2').text('');
              }

              if (CODE_NOMENCLATURE_BUDGETAIRE_ID2=='') 
              {
                $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID2').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID2').text('');
              }

              if (TYPE_INSTITUTION_ID2 == 2)
              {
                if (PTBA_ID2=='') 
                {
                  $('#error_PTBA_ID2').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                  return false;
                }else{
                  $('#error_PTBA_ID2').text('');
                }
              }

              if (PTBA_TACHE_ID2=='') 
              {
                $('#error_PTBA_TACHE_ID2').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_PTBA_TACHE_ID2').text('');
              }

              if(TRIMESTRE_ID_DESTINATION=='')
              {
                $('#error_TRIMESTRE_ID_DESTINATION').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_TRIMESTRE_ID_DESTINATION').text('');
              }

              if (MONTANT_RECEVOIR=='') 
              {
                $('#error_MONTANT_RECEVOIR').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_MONTANT_RECEVOIR').text('');
              }

              if(AUTORISATION_TRANSFERT=="")
              {
                $('#error_AUTORISATION_TRANSFERT').text('<?= lang('messages_lang.message_champs_obligatoire') ?>');
                return false;
              }else{
                $('#error_AUTORISATION_TRANSFERT').text(''); 
              }

              if (parseInt(MONTANT_TRANSFERT)>parseInt(MONTANT_RESTANT)) {
                $('#error_MONTANT_TRANSFERT_SUP').text("<?= lang('messages_lang.error_moneyTrans_sup') ?>");
                $('#MONTANT_RECEVOIR').val('');
                return false;
              }else{
                $('#error_MONTANT_TRANSFERT_SUP').text("");
                $('#MONTANT_RECEVOIR').val(MONTANT_TRANSFERT);
              }

              var form = new FormData();

              var AUTORISATION_TRANSFERT=document.getElementById("AUTORISATION_TRANSFERT").files[0];
             
              form.append("AUTORISATION_TRANSFERT",AUTORISATION_TRANSFERT);
              form.append("MOTIF_TACHE_ID",MOTIF_TACHE_ID);
              form.append("NOM",NOM);
              form.append("PRENOM",PRENOM);
              form.append("POSTE",POSTE);

              form.append("INSTITUTION_ID",INSTITUTION_ID)
              form.append("TYPE_INSTITUTION_ID",TYPE_INSTITUTION_ID);
              form.append("SOUS_TUTEL_ID",SOUS_TUTEL_ID)
              form.append("CODE_NOMENCLATURE_BUDGETAIRE_ID",CODE_NOMENCLATURE_BUDGETAIRE_ID);
              form.append("PTBA_ID",PTBA_ID)
              form.append("PTBA_TACHE_ID",PTBA_TACHE_ID)
              form.append("TRIMESTRE_ID",TRIMESTRE_ID);
              
              form.append("MONTANT_RESTANT",MONTANT_RESTANT);
              form.append("MONTANT_TRANSFERT",MONTANT_TRANSFERT);

              form.append("INSTITUTION_ID2",INSTITUTION_ID2)
              form.append("SOUS_TUTEL_ID2",SOUS_TUTEL_ID2)
              form.append("CODE_NOMENCLATURE_BUDGETAIRE_ID2",CODE_NOMENCLATURE_BUDGETAIRE_ID2);
              form.append("PTBA_ID2",PTBA_ID2)
              form.append("PTBA_TACHE_ID2",PTBA_TACHE_ID2)
              form.append("TRIMESTRE_ID_DESTINATION",TRIMESTRE_ID_DESTINATION);
              form.append("MONTANT_VOTE2",MONTANT_VOTE2); 
              form.append("MONTANT_RESTANT2",MONTANT_RESTANT2);
              form.append("MONTANT_RECEVOIR",MONTANT_RECEVOIR);

              if (statut == true) 
              {
                $.ajax(
                { 
                  url: '<?=base_url('/double_commande_new/Transfert_Double_Commande/addToCart')?>',
                  type:"POST",
                  dataType:"JSON",
                  data: form,
                  processData: false,  
                  contentType: false,
                  beforeSend: function() {
                    $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
                    $('#bouton_envoyer').attr('disabled',true);
                  },
                  success:function(data)
                  {
                    if(data.status == false)
                    {
                      $('#loading_cart').html("");
                      $('#bouton_envoyer').attr('disabled',false);

                      $('#error_INSTITUTION_ID').html(data.msg.INSTITUTION_ID);
                      $('#error_SOUS_TUTEL_ID').html(data.msg.SOUS_TUTEL_ID);
                      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').html(data.msg.CODE_NOMENCLATURE_BUDGETAIRE);
                      $('#error_PTBA_TACHE_ID').html(data.msg.PTBA_TACHE_ID);
                      $('#error_TRIMESTRE_ID').html(data.msg.TRIMESTRE_ID);

                      $('#error_INSTITUTION_ID2').html(data.msg.INSTITUTION_ID2);
                      $('#error_SOUS_TUTEL_ID2').html(data.msg.SOUS_TUTEL_ID2);
                      $('#error_CODE_NOMENCLATURE_BUDGETAIRE2').html(data.msg.CODE_NOMENCLATURE_BUDGETAIRE2);
                      $('#error_PTBA_TACHE_ID2').html(data.msg.PTBA_TACHE_ID2);
                      $('#error_TRIMESTRE_ID_DESTINATION').html(data.msg.TRIMESTRE_ID_DESTINATION);
          
                    }
                    else
                    {
                      liste_tempo();

                      setTimeout(()=>{
                        $('#message').html('<i class="fa fa-check"></i>');
                        window.location.reload();

                        $('#loading_cart').html("");
                        $('#bouton_envoyer').attr('disabled',false);

                        $('#INSTITUTION_ID').val('')
                        $('#SOUS_TUTEL_ID').val('')
                        $('#CODE_NOMENCLATURE_BUDGETAIRE').val('')
                        $('#PTBA_ID').val('')
                        $('#TRIMESTRE_ID').val('')
                        $('#MONTANT_VOTE').val('')
                        $('#MONTANT_RESTANT').val('')
                        $('#MONTANT_TRANSFERT').val('')

                        $('#INSTITUTION_ID2').val('')
                        $('#SOUS_TUTEL_ID2').val('')
                        $('#CODE_NOMENCLATURE_BUDGETAIRE2').val('')
                        $('#PTBA_ID2').val('')
                        $('#TRIMESTRE_ID_DESTINATION').val('')
                        $('#MONTANT_VOTE2').val('')
                        $('#MONTANT_RESTANT2').val('')
                        $('#MONTANT_RECEVOIR').val('')
                        $('#AUTORISATION_TRANSFERT').val('')
                      },3000);
                    }

                     
                  }
                });
              }

            }

            function removeToCart(TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID)
            {
              var TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID = TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID
              $.ajax(
              { 
                url: '<?=base_url('/double_commande_new/Transfert_Double_Commande/removeToCart')?>',
                type:"POST",
                dataType:"JSON",
                data: { 
                  TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID:TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID,
                },
                beforeSend: function() {
                  $('#loading_delete').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
                },
                success:function(data)
                {
                  setTimeout(()=>{
                    $('#message'+TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID+'').html('<i class="fa fa-check"></i>');
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

