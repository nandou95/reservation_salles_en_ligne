  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
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

                  <div class="card-header">
                    <div class="row col-md-12">
                      <div class="col-md-9">
                        <h3><?=$get_step_title['DESC_ETAPE_DOUBLE_COMMANDE']?></h3>
                      </div>
                      <div class="col-md-3">
                        <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_corriger')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?=lang('messages_lang.liste_bouton')?></a>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <br>
                    <div class="container" style="width:100%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="float: left;">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?=lang('messages_lang.histo_btn')?>
                            </button>
                          </h5>
                        </div>  
                      </div><br><br>
                      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <?php include  'includes/Detail_View.php'; ?>
                      </div>
                    </div><br><br>
                    <div class=" container " style="width:100%">
                      <?php $validation = \Config\Services::validation(); ?>
                      <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('double_commande_new/Phase_Administrative/update_corriger_etape4/')?>" method="post" >
                        <div class="container">

                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                <div class="col-6">
                                  <label><b><?=lang('messages_lang.label_motif')?><hr></b></label>
                                  <ol>
                                    <?php
                                    foreach ($motif_rejet as $key) {
                                      ?>
                                      <li><?=$key->DESC_TYPE_ANALYSE_MOTIF?></li>
                                      <?php
                                    }
                                    ?>
                                  </ol>
                                  <br>
                                </div>
                                <div class="col-6">
                                  <label><b><?=lang('messages_lang.label_observ')?><hr></b></label>

                                  <p><?=$date_trans['OBSERVATION']?></p>
                              
                                  <br>
                                </div>
                              </div>
                              <div class="row">
                                <input type="hidden" name="EXEC_BUDGET_RAC_ID" id="EXEC_BUDGET_RAC_ID" value="<?=$EXEC_BUDGET_RAC_ID?>">
                                <input type="hidden" name="EXEC_BUDGET_RAC_DET_ID" id="EXEC_BUDGET_RAC_DET_ID" value="<?=$EXEC_BUDGET_RAC_DET_ID?>">
                                <input type="hidden" name="MONTANT_RACCROCHE" id="MONTANT_RACCROCHE" value="<?=$details['MONTANT_RACCROCHE']?>">
                                <input type="hidden" name="ID_JUR_DEVISE" id="ID_JUR_DEVISE" value="<?=$details['DEVISE_TYPE_HISTO_ENG_ID']?>">
                                <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">
                                <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$details['MARCHE_PUBLIQUE']?>">
                                <input type="hidden" name="MONNAIE" id="MONNAIE" value="<?=$details['TAUX_ECHANGE_ID']?>">
                                <input type="hidden" name="COUR_DEVISE" id="COUR_DEVISE" value="<?=$details['COUR_DEVISE']?>">

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_date_recep_visa')?><span style="color: red;">*</span></label>
                                  <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION" value="<?=set_value('DATE_RECEPTION') ?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_DATE_RECEPTION"><?= $validation->getError('DATE_RECEPTION'); ?></font>
                                  <?php endif ?>
                                  
                                </div>

                                <div class="col-md-6" id="mont_devise">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_mont_devise')?></label>
                                  <input type="hidden" name="test" id="test" value="<?=$details['MONTANT_RACCROCHE_JURIDIQUE_DEVISE']?>">
                                  <input type="hidden" name="MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE" id="MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE" value="<?=$details['MONTANT_RACCROCHE_DEVISE']?>">
                                  <input type="text" class="form-control" name="MONTANT_EN_DEVISE" id="MONTANT_EN_DEVISE" <?php if (!empty($juridique['MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE'])): ?> value="<?=$juridique['MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE']?>" <?php endif ?> value="<?=set_value('MONTANT_EN_DEVISE') ?>" readonly>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MONTANT_EN_DEVISE"><?= $validation->getError('MONTANT_EN_DEVISE'); ?></font>
                                  <?php endif ?>
                                  
                                </div>

                                <div class="col-md-6" id="mont_en_bif" >
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_mont_juridique')?><font color="red">*</font></label>
                                  <input type="text" class="form-control" name="MONTANT_EN_BIF" readonly id="MONTANT_EN_BIF" <?php if (!empty($juridique['MONTANT_RACCROCHE_JURIDIQUE'])): ?> value="<?=$juridique['MONTANT_RACCROCHE_JURIDIQUE']?>" <?php endif ?> value="<?=set_value('MONTANT_EN_BIF') ?>" readonly>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MONTANT_EN_BIF"><?= $validation->getError('MONTANT_EN_BIF'); ?></font>
                                  <?php endif ?>
                                  
                                </div>

                                <?php if($details['TYPE_MONTANT_ID'] ==1):?>
                                  <div class="col-md-6">
                                    <br>
                                    <label for="" id="date_eng"></label>
                                    <input type="date" class="form-control" id="DATE_HEURE_JURIDIQUE" name="DATE_HEURE_JURIDIQUE" onkeypress="return false" <?php if (!empty($juridique['DATE_ENGAGEMENT_JURIDIQUE'])): ?> value="<?=date('Y-m-d', strtotime($juridique['DATE_ENGAGEMENT_JURIDIQUE']))?>" <?php endif ?> value="<?=set_value('DATE_HEURE_JURIDIQUE') ?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                                    
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_DATE_HEURE_JURIDIQUE"><?= $validation->getError('DATE_HEURE_JURIDIQUE'); ?></font>
                                    <?php endif ?>
                                  </div>
                                <?php endif ?>
                                <?php if($details['TYPE_MONTANT_ID'] !=1):?>
                                  <div class="col-md-6">
                                    <br>
                                    <label for="" id="date_eng"></label>
                                    <input type="date" class="form-control" id="DATE_HEURE_JURIDIQUE" name="DATE_HEURE_JURIDIQUE" onkeypress="return false" <?php if (!empty($juridique['DATE_ENGAGEMENT_JURIDIQUE'])): ?> value="<?=date('Y-m-d', strtotime($juridique['DATE_ENGAGEMENT_JURIDIQUE']))?>" <?php endif ?> value="<?=set_value('DATE_ENGAGEMENT_JURIDIQUE') ?>" min="<?=date('Y-m-d', strtotime($juridique['DATE_ENGAGEMENT_JURIDIQUE']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                                    
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_DATE_HEURE_JURIDIQUE"><?= $validation->getError('DATE_HEURE_JURIDIQUE'); ?></font>
                                    <?php endif ?>
                                  </div>
                                <?php endif ?>

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_date_trans')?><span style="color: red;">*</span></label>
                                  <input type="date" class="form-control" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION" value="<?=set_value('DATE_TRANSMISSION') ?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_DATE_TRANSMISSION"><?= $validation->getError('DATE_TRANSMISSION'); ?></font>
                                  <?php endif ?>
                                  
                                </div>

                                <div class="col-md-6">
                                  <br>
                                  <label class="form-label"><?=lang('messages_lang.label_type_benef')?><font color="red">*</font></label>
                                  <select name="TYPE_BENEFICIARE" id="TYPE_BENEFICIARE" class="form-control" onchange="get_benef(this.value)">
                                    <option value=""><?=lang('messages_lang.selection_message')?></option>
                                    <?php 
                                    foreach($type_benef as $key) { 
                                      if ($key->TYPE_BENEFICIAIRE_ID==$juridique['TYPE_BENEFICIAIRE_ID']) { 
                                        echo "<option value='".$key->TYPE_BENEFICIAIRE_ID."' selected>".$key->DESC_TYPE_BENEFICIAIRE."</option>";
                                      }else{
                                        echo "<option value='".$key->TYPE_BENEFICIAIRE_ID."' >".$key->DESC_TYPE_BENEFICIAIRE."</option>"; 
                                      } 
                                    }?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TYPE_BENEFICIARE"><?= $validation->getError('TYPE_BENEFICIARE'); ?></font>
                                  <?php endif ?>
                                  
                                </div>

                                <div class="col-md-6" id="prest_select">
                                  <br>
                                  <label class="form-label" id="prest_label"><?=$prest_label?></label>
                                  <select name="FOURNISSEUR_ACQUEREUR" id="FOURNISSEUR_ACQUEREUR" class="form-control select2" onchange="get_presta(this.value)">
                                    <option value=""><?=lang('messages_lang.selection_message')?></option>
                                    <?php 
                                    foreach($prest as $key) { 
                                      if ($key->PRESTATAIRE_ID==$juridique['PRESTATAIRE_ID']) { 
                                        echo "<option value='".$key->PRESTATAIRE_ID."' selected>".$key->NOM_PRESTATAIRE."  ".$key->PRENOM_PRESTATAIRE."&nbsp;&nbsp;&nbsp;NIF : ".$key->NIF_PRESTATAIRE."</option>";
                                      }else{
                                        echo "<option value='".$key->PRESTATAIRE_ID."' >".$key->NOM_PRESTATAIRE."  ".$key->PRENOM_PRESTATAIRE."&nbsp;&nbsp;&nbsp;NIF : ".$key->NIF_PRESTATAIRE."</option>"; 
                                      } 
                                    }?>
                                    <option value="0">Autre</option>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_FOURNISSEUR_ACQUEREUR"><?= $validation->getError('FOURNISSEUR_ACQUEREUR'); ?></font>
                                  <?php endif ?>
                                  
                                </div>


                                <div class="col-md-6">
                                  <br>
                                  <label class="form-label"><?=lang('messages_lang.label_modele')?><font color="red">*</font></label>
                                  <select name="MODEL" id="MODEL" class="form-control">
                                    <option value=""><?=lang('messages_lang.selection_message')?></option>
                                    <?php 
                                    foreach($get_modele as $key) { 
                                      if ($key->MODELE_ID==$juridique['MODELE_ID']) { 
                                        echo "<option value='".$key->MODELE_ID."' selected>".$key->DESC_MODELE."</option>";
                                      }else{
                                        echo "<option value='".$key->MODELE_ID."' >".$key->DESC_MODELE."</option>"; 
                                      } 
                                    }?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MODEL"><?= $validation->getError('MODEL'); ?></font>
                                  <?php endif ?>

                                  
                                </div>

                                <div class="col-md-6" >
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_reference')?><font color="red">*</font></label>
                                  <input type="text" class="form-control" name="REFERENCE" id="REFERENCE" <?php if (!empty($juridique['REFERENCE'])): ?> value="<?=$juridique['REFERENCE']?>" <?php endif ?> value="<?=set_value('REFERENCE') ?>">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_REFERENCE">  <?= $validation->getError('REFERENCE'); ?></font>
                                  <?php endif ?>   
                                  
                                </div>

                                <?php if($details['MARCHE_PUBLIQUE'] == 1): ?>
                                  <div class="col-md-6">
                                    <br>
                                    <label><?=lang('messages_lang.label_contrat')?><?php if(!empty($juridique['PATH_CONTRAT'])): ?><a href="#" data-toggle="modal" data-target="#ppm_corrige"><span class="fa fa-file-pdf" style="color:red;"></span></a><?php endif ?></label>
                                    <input type="hidden" class="form-control " id="PATH_CONTRAT_OLD" name="PATH_CONTRAT_OLD" accept=".pdf" <?php if (!empty($juridique['PATH_CONTRAT'])): ?> value="<?=$juridique['PATH_CONTRAT']?>" <?php endif ?>>
                                    <input type="file" class="form-control " id="PATH_CONTRAT" name="PATH_CONTRAT" placeholder="...." accept=".pdf" value="<?=set_value('PATH_CONTRAT')?>">
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_PATH_CONTRAT"><?=$file_error?></font>
                                    <?php endif ?>
                                    
                                  </div>
                                <?php endif ?>

                                <?php if($details['MARCHE_PUBLIQUE'] == 1): ?>

                                  <div class="col-md-6">
                                    <br>
                                    <label><?=lang('messages_lang.label_date_debut_contrat')?><span style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="DATE_DEBUT" name="DATE_DEBUT"  <?php if (!empty($juridique['DATE_DEBUT_CONTRAT'])): ?> value="<?=date('Y-m-d', strtotime($juridique['DATE_DEBUT_CONTRAT']))?>" <?php endif ?> value="<?=set_value('DATE_DEBUT') ?>" min="<?=date('Y-m-d', strtotime($debut))?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_fin(this.value)">
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_DATE_DEBUT"><?= $validation->getError('DATE_DEBUT'); ?></font>
                                    <?php endif ?>
                                    
                                  </div>

                                  <div class="col-md-6">
                                    <br>
                                    <label><?=lang('messages_lang.label_date_fin_contrat')?><span style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="DATE_FIN" name="DATE_FIN"  <?php if (!empty($juridique['DATE_FIN_CONTRAT'])): ?> value="<?=date('Y-m-d', strtotime($juridique['DATE_FIN_CONTRAT']))?>" <?php endif ?> value="<?=set_value('DATE_FIN') ?>" onkeypress="return false" onblur="this.type='date'">
                                    <?php if (isset($validation)) : ?>
                                      <font color="red" id="error_DATE_FIN"><?= $validation->getError('DATE_FIN'); ?></font>
                                    <?php endif ?>
                                    
                                  </div>
                                <?php endif ?>

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_observ')?></label>
                                  <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"><?=set_value('COMMENTAIRE')?></textarea>
                                  <font color="red" id="error_COMMENTAIRE"></font>
                                </div> 

                              </div>

                              <br>
                              <div style="float: right;" class="col-md-2 mt-5 " >
                                <div class="form-group " >
                                  <a onclick="save()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?=lang('messages_lang.enregistrer_bouton')?></a>
                                </div>
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
    function get_min_trans()
    {
      $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
    }

    function get_min_fin()
    {
      $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    }
  </script>

  <script>
    $(document).ready(function ()
    {

      function DoPrevent(e)
      {
        e.preventDefault();
        e.stopPropagation();
      }

      var MONNAIE = $('#MONNAIE').val();
      if(MONNAIE == 1)
      {

        var bif = $('#MONTANT_EN_BIF').val().replace(/[^0-9.]/g, '');
        $('#MONTANT_EN_BIF').val(bif.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
        $('#mont_en_bif').show();
        $('#date_eng').html("<?=lang('messages_lang.label_date_juridique')?>");
        $('#mont_devise').hide();

        $('#MONTANT_EN_BIF').on('input', function(){

          var MONTANT_ENG = $('#MONTANT_RACCROCHE').val();

          var value = $(this).val();

          value = value.replace(/[^0-9.]/g, '');

          value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

          $(this).val(value);

          if (/^0\d/.test(value)) {
            value = value.replace(/^0\d/, '');
            $(this).val(value);
          }


          var MONTANT_EN_BIF = parseFloat(value.replace(/\s/g, ''));
          var MONTANT_ENG = parseFloat(MONTANT_ENG);
          if(MONTANT_EN_BIF > MONTANT_ENG)
          {
           $(this).on('keypress',DoPrevent);
           $('#error_MONTANT_EN_BIF').html("<?=lang('messages_lang.err_mont_inferieur')?>"+ MONTANT_ENG +".");

         }else{
           $(this).off('keypress',DoPrevent);
           $('#error_MONTANT_EN_BIF').html('');
         }

       })

      }
      else{
       var test = $('#test').val();
       $('#MONTANT_EN_DEVISE').val(test);
       $('#mont_en_bif').show();
       $('#mont_devise').show();
       $('#date_eng').html("<?=lang('messages_lang.label_date_devise')?>");

       var bif = $('#MONTANT_EN_BIF').val().replace(/[^0-9.]/g, '');
       $('#MONTANT_EN_BIF').val(bif.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));

       var devise = $('#MONTANT_EN_DEVISE').val().replace(/[^0-9.]/g, '');
       $('#MONTANT_EN_DEVISE').val(devise.replace(/\B(?=(\d{3})+(?!\d))/g, ' '));


       $('#MONTANT_EN_DEVISE').on('input', function(){

        var MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE = $('#MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE').val();

        var value = $(this).val();

        value = value.replace(/[^0-9.]/g, '');

        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        $(this).val(value);

        if (/^0\d/.test(value)) {
          value = value.replace(/^0\d/, '');
          $(this).val(value);
        }

        var MONTANT_EN_DEVISE = parseFloat($('#MONTANT_EN_DEVISE').val().replace(/\s/g, ''));
        var MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE = parseFloat(MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE);
          if(MONTANT_EN_DEVISE > MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE)
          {
           $(this).on('keypress',DoPrevent);
           $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.err_mont_inferieur')?>"+ MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE +".");

         }else{
           $(this).off('keypress',DoPrevent);
           $('#error_MONTANT_EN_DEVISE').html('');
         }

         var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
         var COUR_DEVISE = $('#COUR_DEVISE').val();
         var result = parseFloat(MONTANT_EN_DEVISE.replace(/\s/g, ''))*parseFloat(COUR_DEVISE.replace(/\s/g, '')) || 0;
         var resultat = result;
         $('#MONTANT_EN_BIF').val(resultat);
         $('#MONTANT_EN_BIF').val($('#MONTANT_EN_BIF').val().replace(/\B(?=(\d{3})+(?!\d))/g, ' '));


       })

     }



     $('#REFERENCE,#MONTANT_EN_DEVISE').on('input', function(){


      if(this.id === "REFERENCE")
      {
        $(this).val($(this).val().toUpperCase());
        $(this).val(this.value.substring(0,25));
      }


      if(this.id === "MONTANT_EN_DEVISE")
      {
        $(this).val(this.value.substring(0,50));
      }

    })

   });
 </script>

 <script type="text/javascript">
  function get_benef()
  {
    var TYPE_BENEFICIARE = $('#TYPE_BENEFICIARE').val();
    $('#FOURNISSEUR_ACQUEREUR').html('<option value ="">Sélectionner</option>');

    $.post('<?=base_url('double_commande_new/Phase_Administrative/get_benef')?>',
    {
      TYPE_BENEFICIARE:TYPE_BENEFICIARE
    },
    function(data)
    {
      $('#FOURNISSEUR_ACQUEREUR').html(data.benef);
      FOURNISSEUR_ACQUEREUR.InnerHtml=data.benef;

      if(TYPE_BENEFICIARE == 1)
      {
        $('#prest_select').show();
        $('#prest_label').html("<?=lang('messages_lang.label_fournisseur')?>"+"<font color=\'red\'>*</font>");

      }
      else if(TYPE_BENEFICIARE == 2){
        $('#prest_select').show();
        $('#prest_label').html("<?=lang('messages_lang.label_acquereur')?>"+"<font color=\'red\'>*</font>");

      }else{
        $('#prest_select').hide();

      }

    })
  }
</script>
<script type="text/javascript">
  function get_presta()
  {

    var FOURNISSEUR_ACQUEREUR = $('#FOURNISSEUR_ACQUEREUR').val();

    if(FOURNISSEUR_ACQUEREUR === '0')
    {
      var EXEC_BUDGET_RAC_ID = '<?=md5($EXEC_BUDGET_RAC_ID)?>';

      window.location.href = '<?php echo base_url("double_commande_new/Prestataire/add")?>/'+EXEC_BUDGET_RAC_ID;
    }
  }
</script>

<script type="text/javascript">
  function save()
  {
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
    var TYPE_MONNAIE = $('#MONNAIE').val();
    var MONTANT_ENG = $('#MONTANT_RACCROCHE').val();

    var value_en_bif = $('#MONTANT_EN_BIF').val();
    var MONTANT_EN_BIF = value_en_bif.replace(/\s/g, '');

    var value_en_devise = $('#MONTANT_EN_DEVISE').val();
    var MONTANT_EN_DEVISE = value_en_devise.replace(/\s/g, '');

    var DATE_HEURE_JURIDIQUE = $('#DATE_HEURE_JURIDIQUE').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    
    var TYPE_BENEFICIARE = $('#TYPE_BENEFICIARE').val();
    var FOURNISSEUR_ACQUEREUR = $('#FOURNISSEUR_ACQUEREUR').val();
    var MODEL = $('#MODEL').val();
    var REFERENCE = $('#REFERENCE').val();
    var COMMENTAIRE = $('#COMMENTAIRE').val();
    var PATH_CONTRAT = document.getElementById('PATH_CONTRAT');
    var maxFileSize = 25*(1024*1024);

    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();

    var status = 2;

    $('#error_MONTANT_EN_BIF, #error_MONTANT_EN_DEVISE, #error_DATE_HEURE_JURIDIQUE, #error_DATE_TRANSMISSION, #error_DATE_RECEPTION, #error_TYPE_BENEFICIARE, #error_FOURNISSEUR_ACQUEREUR, #error_MODEL, #error_REFERENCE, #error_COMMENTAIRE, #error_PATH_CONTRAT, #error_DATE_DEBUT, #error_DATE_FIN').html('');

    
    if(MARCHE_PUBLIQUE == 1)
    {
      if(PATH_CONTRAT.files.length != 0)
      {
        if(PATH_CONTRAT.files[0].size > maxFileSize)
        {
          $('#error_PATH_CONTRAT').html("<?=lang('messages_lang.file_err_size')?>"+" 25 MB!");
          status = 1;
        }
      }

      if(DATE_DEBUT =='')
      {
        $('#error_DATE_DEBUT').html("<?=lang('messages_lang.champ_obligatoire')?>");
        status=1;
      }

      if(DATE_FIN =='')
      {
        $('#error_DATE_FIN').html("<?=lang('messages_lang.champ_obligatoire')?>");
        status=1;
      }

    }

    if(TYPE_BENEFICIARE == '')
    {
      $('#error_TYPE_BENEFICIARE').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }


    if(FOURNISSEUR_ACQUEREUR == '')
    {
      $('#error_FOURNISSEUR_ACQUEREUR').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }


    if(DATE_HEURE_JURIDIQUE =='')
    {
      $('#error_DATE_HEURE_JURIDIQUE').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status=1;
    }

    if(DATE_TRANSMISSION =='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status=1;
    }

    if(DATE_RECEPTION =='')
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status=1;
    }

    if(TYPE_MONNAIE == 1)
    {
      if(MONTANT_EN_BIF == '')
      {
        $('#error_MONTANT_EN_BIF').html("<?=lang('messages_lang.champ_obligatoire')?>");
        status = 1;
      }
      else
      {
        if(parseInt(MONTANT_EN_BIF) > parseInt(MONTANT_ENG))
        {
          $('#error_MONTANT_EN_BIF').html("<?=lang('messages_lang.err_mont_inferieur')?>"+ MONTANT_ENG +".");
          status = 1;
        }
        else if(parseInt(MONTANT_EN_BIF) == 0){

          $('#error_MONTANT_EN_BIF').html("<?=lang('messages_lang.mont_error_zero')?>");
          status = 1;

        }
      }

    }else{

      if(MONTANT_EN_BIF == '')
      {
        $('#error_MONTANT_EN_BIF').html("<?=lang('messages_lang.champ_obligatoire')?>");
        status = 1;
      }
      

      if(MONTANT_EN_DEVISE == '')
      {
        $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.champ_obligatoire')?>");
        status = 1;
      }
    }


    if(MODEL == '')
    {
      $('#error_MODEL').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }

    if(REFERENCE == '')
    {
      $('#error_REFERENCE').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }


    if(status == 2){

      $('#mont_eng_juridiq_verifie').html(value_en_bif);

      if(TYPE_MONNAIE != 1){

        $('#showing_devise').show();
        $('#mont_en_devise_verifie').html(value_en_devise);

      }
      else{

        $('#showing_devise').hide();
      }

      if(TYPE_BENEFICIARE == 2)
      {
        $('#showing_acquer').show();
        $('#showing_fourni').hide();
        $('#acquereur_verifie').html($('#FOURNISSEUR_ACQUEREUR option:selected').text());
      }
      else{

        $('#showing_acquer').hide();
        $('#showing_fourni').show();
        $('#fournisseur_verifie').html($('#FOURNISSEUR_ACQUEREUR option:selected').text());
      }

      const format_date_jurid = moment(DATE_HEURE_JURIDIQUE).format('DD/MM/YYYY');
      $('#date_juridiq_verifie').html(format_date_jurid);

      const format_date_recep = moment(DATE_RECEPTION).format('DD/MM/YYYY');
      $('#date_recep_verifie').html(format_date_recep);

      const format_date_trans = moment(DATE_TRANSMISSION).format('DD/MM/YYYY');
      $('#date_trans_verifie').html(format_date_trans);

      $('#type_benef_verifie').html($('#TYPE_BENEFICIARE option:selected').text());
      
      $('#modele_verifie').html($('#MODEL option:selected').text());

      $('#ref_verifie').html(REFERENCE);

      if(COMMENTAIRE =='')
      {
       $('#showing_observ').hide();
     }else{
      $('#showing_observ').show();
      $('#observ_verifie').html(COMMENTAIRE);

    }

    if(MARCHE_PUBLIQUE == 1)
    {

      if(PATH_CONTRAT.files.length != 0)
      {
        var CONTRAT_ID = document.getElementById('PATH_CONTRAT');
        var CONTRAT_NAME = CONTRAT_ID.files[0].name;
        $('#showing_contrat').show();
        $('#contrat_verifie').html(CONTRAT_NAME);  
      }else{

        $('#showing_contrat').hide();

      }


      $('#showing_date_debut').show();
      const format_date_debut = 
      moment(DATE_DEBUT).format('DD/MM/YYYY');
      $('#date_debut_verifie').html(format_date_debut);

      $('#showing_date_fin').show();
      const format_date_fin = moment(DATE_FIN).format('DD/MM/YYYY');
      $('#date_fin_verifie').html(format_date_fin);
    }
    else{

      $('#showing_contrat').hide();
      $('#showing_date_debut').hide();
      $('#showing_date_fin').hide();

    }

    $('#engager_juridique').modal('show');
  }
}

</script>

<script type="text/javascript">
  function confirm()
  {
    $("#MyFormData").submit();
  }
</script>


<!--* Modal pour confirmer les infos saisies **-->
<div class="modal fade" id="engager_juridique" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?=lang('messages_lang.vouloir_confirmer')?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td style="width:350px;"><i class="fa fa-credit-card"></i> &nbsp;<strong><?=lang('messages_lang.label_mont_juridique')?></strong></td>
                <td>
                  <span id="mont_eng_juridiq_verifie" class="text-dark"></span>
                </td>
              </tr>
              <tr id="showing_devise">
                <td style="width:350px;"><i class="fa fa-credit-card"></i>&nbsp;<strong><?=lang('messages_lang.label_mont_devise')?></strong></td>
                <td>
                  <span id="mont_en_devise_verifie" class="text-dark"></span>
                </td>
              </tr>
              <tr>
                <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_juridique')?></strong></td>
                <td id="date_juridiq_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_recep_visa')?></strong></td>
                <td id="date_recep_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_trans')?></strong></td>
                <td id="date_trans_verifie" class="text-dark"></td>
              </tr>

              <tr id="showing_benef">
                <td style="width:350px;"><i class="fa fa-certificate"></i>&nbsp;<strong><?=lang('messages_lang.label_type_benef')?></strong></td>
                <td id="type_benef_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_acquer">
                <td style="width:350px ;"><i class="fa fa-users"></i>&nbsp;<strong><?=lang('messages_lang.label_acquereur')?></strong></td>
                <td id="acquereur_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_fourni">
                <td style="width:350px;"><i class="fa fa-users"></i>&nbsp;<strong><?=lang('messages_lang.label_fournisseur')?></strong></td>
                <td id="fournisseur_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px;"><i class="fa fa-cubes"></i>&nbsp;<strong><?=lang('messages_lang.label_modele')?></strong></td>
                <td id="modele_verifie" class="text-dark"></td>
              </tr>

              <tr>
                <td style="width:350px;"><i class="fa fa-list"></i>&nbsp;<strong><?=lang('messages_lang.label_reference')?></strong></td>
                <td id="ref_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_observ">
                <td style="width:350px;"><i class="fa fa-folder-open"></i>&nbsp;<strong><?=lang('messages_lang.label_observ')?></strong></td>
                <td id="observ_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_contrat">
                <td style="width:350px;"><i class="fa fa-file-pdf"></i>&nbsp;<strong><?=lang('messages_lang.label_contrat')?></strong></td>
                <td id="contrat_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_date_debut">
                <td style="width:350px;"><i class="fa fa-calendar"></i>&nbsp;<strong><?=lang('messages_lang.label_date_debut_contrat')?></strong></td>
                <td id="date_debut_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_date_fin">
                <td style="width:350px;"><i class="fa fa-calendar"></i>&nbsp;<strong><?=lang('messages_lang.label_date_fin_contrat')?></strong></td>
                <td id="date_fin_verifie" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="mode1" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?=lang('messages_lang.bouton_modifier')?></button>
        <a onclick="confirm();hideButton()" id="corrig" style="float: right;" id="corrig" class="btn btn-info"><i class="fa fa-check"></i><?=lang('messages_lang.bouton_confirmer')?></a>
      </div>
    </div>
  </div>
</div>
<!--* Modal pour confirmer les infos saisies **-->


<script>
  function hideButton()
  {
    var element = document.getElementById("corrig");
    element.style.display = "none";

    var elementmod = document.getElementById("mode1");
    elementmod.style.display = "none";
  }
</script>

<div class='modal fade' id='ppm_corrige'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class="modal-header">
        <center><?=lang('messages_lang.label_contrat')?></center>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class='modal-body'>
        <center>
          <embed  src="<?=base_url('uploads/double_commande_new/'.$juridique['PATH_CONTRAT'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
          </center>
        </div>
        <div class='modal-footer'>
          <button class='btn btn-primary btn-md' data-dismiss='modal'>
            <?=lang('messages_lang.quiter_action')?>
          </button>
        </div>
      </div>
    </div>
  </div>








