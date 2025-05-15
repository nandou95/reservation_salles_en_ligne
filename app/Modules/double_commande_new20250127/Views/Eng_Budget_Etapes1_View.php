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
                    <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape1?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myEtape1" id="myEtape1" action="<?=base_url('double_commande_new/Phase_Administrative_Budget/save_etape1/')?>" method="post" >
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
                            <div class="col-md-7 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_inst') ?><font color="red">*</font></label>
                                    <select onchange="get_sousTutel();get_inst();" class="select2 form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
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

                                    <div class="col-md-6">
                                      <div class='form-froup'>
                                        <label class="form-label"><?= lang('messages_lang.label_sousTitre') ?><font color="red">*</font></label><b id="loading_sous_tutel"></b>
                                        <select class="select2 form-control" id="SOUS_TUTEL_ID" value="<?=set_value('SOUS_TUTEL_ID') ?>" name="SOUS_TUTEL_ID" onchange="get_code()">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>

                                        </select>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                                        <?php endif ?>
                                        <font color="red" id="error_SOUS_TUTEL_ID"></font>
                                        <br>
                                      </div>
                                    </div>

                                    <div class="col-md-6">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_ligne') ?><font color="red">*</font></label><b id="loading_budget"></b>
                                        <select onchange="get_change();"  class="form-control form-select bg-light select2" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                          <option value="<?=set_value('CODE_NOMENCLATURE_BUDGETAIRE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                        </select>
                                        <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE_ID'); ?>
                                        <?php endif ?>
                                      </div>
                                    </div>

                                    <div class="col-md-6" id="act_id" hidden="true" >
                                      <div class="form-froup">
                                        <label for=""><?= lang('messages_lang.label_activite') ?> <font color="red">*</font></label><b id="loading_act"></b>
                                        <select onchange="get_taches()" class="form-control form-select bg-light select2" id="PAP_ACTIVITE_ID" name="PAP_ACTIVITE_ID" placeholder="Sélectionnez l'activité" autocomplete="off" aria-label=".form-select-lg example" >
                                          <option value="<?=set_value('PAP_ACTIVITE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                        </select>
                                        <font color="red" id="error_montant_transfert"></font>
                                        <font color="red" id="error_PAP_ACTIVITE_ID"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('PAP_ACTIVITE_ID'); ?>
                                        <?php endif ?>
                                         <br>
                                      </div>
                                    </div>

                                     <div class="col-md-6" id="num_montant_transfert">
                                      <div class="form-froup">
                                        <label for=""><?= lang('messages_lang.label_taches') ?> <font color="red">*</font></label><b id="loading_act"></b>
                                        <select onchange="get_TacheMoney()" class="form-control form-select bg-light select2" id="PTBA_TACHE_ID" name="PTBA_TACHE_ID" placeholder="Sélectionnez la tache" autocomplete="off" aria-label=".form-select-lg example" >
                                          <option value="<?=set_value('PTBA_TACHE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                        </select>
                                        <font color="red" id="error_montant_transfert"></font>
                                        <font color="red" id="error_PTBA_TACHE_ID"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('PTBA_TACHE_ID'); ?>
                                        <?php endif ?>
                                         <br>
                                      </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_monnaie') ?><font color="red">*</font></label>
                                        <select onchange="money_devise();" name="TYPE_MONTANT_ID" id="TYPE_MONTANT_ID" class="form-control">
                                          <?php 
                                          foreach($get_device as $key) { 
                                            if ($key->DEVISE_TYPE_ID==set_value('TYPE_MONTANT_ID')) { 
                                              echo "<option value='".$key->DEVISE_TYPE_ID."' selected>".$key->DESC_DEVISE_TYPE."</option>";
                                            }else{
                                              echo "<option value='".$key->DEVISE_TYPE_ID."' >".$key->DESC_DEVISE_TYPE."</option>"; 
                                            } 
                                          }?>

                                        </select>
                                        <?php if (isset($validation)) : ?>
                                          <font color="red" id="error_TYPE_MONTANT_ID"><?= $validation->getError('TYPE_MONTANT_ID'); ?></font>
                                        <?php endif ?>
                                      </div>
                                    </div>
                                    <div class="col-md-6" id="mon_dev" hidden="true">
                                      <label for=""><?= lang('messages_lang.label_devise') ?><font color="red">*</font></label>
                                      <input onpaste="return false;" onkeyup="fois();" oninput="formatInputValue(this);" onkeydown="moneyDevise();" type="text" class="form-control" name="MONTANT_EN_DEVISE" id="MONTANT_EN_DEVISE">
                                      <input type="hidden" name="engagement_devise" id="engagement_devise">
                                      <font color="red" id="error_MONTANT_EN_DEVISE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('MONTANT_EN_DEVISE'); ?>
                                      <?php endif ?>
                                       <br>
                                    </div>
                                     
                                    <div class="col-md-6" id="cou_chang" hidden="true">
                                      <label for=""><?= lang('messages_lang.label_echange') ?><font color="red">*</font> </label>
                                      <!-- <input onpaste="return false;" type="text" class="form-control" name="engagement_cous" id="engagement_cous" readonly> -->

                                      <input onpaste="return false;" oninput="formatInputValue(this);" onkeyup="fois();" onkeydown="moneyDevise();" type="text" class="form-control" name="engagement_cous" id="engagement_cous">
                                      
                                      <!-- <input type="hidden" name="COUS_ECHANGE" id="COUS_ECHANGE"> -->
                                      <!-- <input type="hidden" name="DEVISE_TYPE_HISTO_ID" id="DEVISE_TYPE_HISTO_ID"> -->

                                      <font color="red" id="error_COUS_ECHANGE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('COUS_ECHANGE'); ?>
                                      <?php endif ?>
                                    </div>


                                    <div class="col-md-6" id="racc_dev"  hidden="true">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_engage') ?>  <font color="red">*</font></label>
                                        <input type="text" class="form-control " name="MONTANT_RACCROCHE_devise" id="MONTANT_RACCROCHE_devise" readonly>
                                        <font color="red" id="error_MONTANT_RACCROCHE124"></font>
                                         <br>
                                      </div>
                                    </div>
                                    <div class="col-md-6" id="date_dev"  hidden="true">
                                      <label for=""><?= lang('messages_lang.label_date_cours') ?><font color="red">*</font></label>
                                      <input type="date" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_COUT_DEVISE" id="DATE_COUT_DEVISE">
                                      <font color="red" id="error_DATE_COUT_DEVISE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('DATE_COUT_DEVISE'); ?>
                                      <?php endif ?>

                                    </div>
                                    <div class="col-md-6" id="racc_bif">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_engage') ?> <font color="red">*</font></label>
                                        <input onpaste="return false;" type="text" class="form-control " name="MONTANT_RACCROCHE" id="MONTANT_RACCROCHE" placeholder="" value="<?=set_value('MONTANT_RACCROCHE')?>" onpaste="return false;" min="0" oninput="formatInputValue(this);" onkeyup="moneyRestant();calculer()" >
                                        <font color="red" id="error_MONTANT_RACCROCHE"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('MONTANT_RACCROCHE'); ?>
                                        <?php endif ?>
                                        <br>
                                      </div>
                                      <input type="hidden" name="engagement_budget" id="engagement_budget">
                                    </div>
                                    <div class="col-md-6">
                                      <div class='form-froup'>
                                        <label class="form-label"><?= lang('messages_lang.label_nature') ?> <font color="red">*</font></label>
                                        <select onchange="type_engage();salaire_doc();get_docs()" class="form-control" name="TYPE_ENGAGEMENT_ID" id="TYPE_ENGAGEMENT_ID">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          <?php  foreach ($grande as $value) { ?>
                                            <?php if($value->TYPE_ENGAGEMENT_ID==set_value('TYPE_ENGAGEMENT_ID')) { ?>
                                              <option value="<?=$value->TYPE_ENGAGEMENT_ID ?>" selected>
                                                <?=$value->DESC_TYPE_ENGAGEMENT?></option>
                                              <?php }else{?>
                                               <option value="<?=$value->TYPE_ENGAGEMENT_ID ?>">
                                                <?=$value->DESC_TYPE_ENGAGEMENT?></option>
                                              <?php } }?>
                                            </select>
                                            <font color="red" id="error_TYPE_ENGAGEMENT_ID"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('TYPE_ENGAGEMENT_ID'); ?>
                                            <?php endif ?>
                                          </div>
                                        </div>

                                        <div class="col-md-6">
                                        <label for=""><?= lang('messages_lang.th_type_document') ?><font color="red">*</font></label>
                                        <select name="BUDGETAIRE_TYPE_DOCUMENT_ID" id="BUDGETAIRE_TYPE_DOCUMENT_ID" onchange="change_lettre()" class="form-control">
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>
                                            <?php  foreach ($get_typ_docs as $keys) { ?>
                                              <?php if($keys->BUDGETAIRE_TYPE_DOCUMENT_ID==1) { ?>
                                                <option value="<?=$keys->BUDGETAIRE_TYPE_DOCUMENT_ID ?>" selected>
                                                <?=$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT ?></option>
                                              <?php }else{?>
                                              <option value="<?=$keys->BUDGETAIRE_TYPE_DOCUMENT_ID ?>">
                                              <?=$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT ?></option>
                                              <?php } }?>
                                        </select>
                                        <font color="red" id="error_BUDGETAIRE_TYPE_DOCUMENT_ID"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('BUDGETAIRE_TYPE_DOCUMENT_ID'); ?>
                                        <?php endif ?>
                                        <br>
                                      </div>

                                        <div class="col-md-6" id="otb">
                                          <label id="titre_lettre_dcp"><?= lang('messages_lang.label_note') ?></label><font color="red">*</font>
                                          <input type="file" accept=".pdf" class="form-control"  name="PATH_LETTRE_OTB" id="PATH_LETTRE_OTB" value="<?=set_value("PATH_LETTRE_OTB")?>" id="PATH_LETTRE_OTB">
                                          <font color="red" id="error_PATH_LETTRE_OTB"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('PATH_LETTRE_OTB'); ?>
                                          <?php endif ?>
                                          <br>
                                        </div>
                                        <div class="col-md-6" id="let_trans" hidden="true">
                                          <label for=""><?= lang('messages_lang.label_lettre') ?> <font color="red"><span id='lettre_id'></span></font></label>
                                          <input type="file" accept=".pdf" class="form-control"  name="PATH_LETTRE_TRANSMISSION" id="PATH_LETTRE_TRANSMISSION" value="<?=set_value("PATH_LETTRE_TRANSMISSION")?>" id="PATH_LETTRE_TRANSMISSION">
                                          <font color="red" id="error_PATH_LETTRE_TRANSMISSION"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('PATH_LETTRE_TRANSMISSION'); ?>
                                          <?php endif ?>
                                        </div>
                                        
                                        <div class="col-md-6" id="liste_paie" hidden="true">
                                          <label for=""><?= lang('messages_lang.label_liste') ?><font color="red">*</font></label>
                                          <input type="file" accept=".pdf" class="form-control"  name="PATH_LISTE_PAIE" id="PATH_LISTE_PAIE" value="<?=set_value("PATH_LISTE_PAIE")?>" id="PATH_LISTE_PAIE">
                                          <font color="red" id="error_PATH_LISTE_PAIE"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('PATH_LISTE_PAIE'); ?>
                                          <?php endif ?>
                                          <br>
                                        </div>
                                    
                                        <div class="col-md-6">
                                          <div class="form-froup">
                                            <label class="form-label"> <?= lang('messages_lang.label_marche') ?><font color="red">*</font></label>
                                            <select onchange="saissie_ppm();" class="form-control" id="MARCHE_PUBLIQUE" name="MARCHE_PUBLIQUE" >
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                              <option value="1">Oui</option>
                                              <option value="0">Non</option>
                                            </select>
                                            <font color="red" id="error_MARCHE_PUBLIQUE"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('MARCHE_PUBLIQUE'); ?>
                                            <?php endif ?>
                                            <br>
                                          </div>
                                        </div>  
                                  <div class="col-md-6" id="tip_march" hidden="true">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_type_marche') ?> <font color="red">*</font></label>
                                    <select class="select2 form-control" name="ID_TYPE_MARCHE" id="ID_TYPE_MARCHE">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  foreach ($type_marche as $keys) { ?>
                                        <?php if($keys->ID_TYPE_MARCHE==set_value('ID_TYPE_MARCHE')) { ?>
                                          <option value="<?=$keys->ID_TYPE_MARCHE ?>" selected>
                                            <?=$keys->DESCR_MARCHE?></option>
                                          <?php }else{?>
                                           <option value="<?=$keys->ID_TYPE_MARCHE ?>">
                                            <?=$keys->DESCR_MARCHE?></option>
                                          <?php } }?>
                                        </select>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('ID_TYPE_MARCHE'); ?>
                                        <?php endif ?>
                                        <font color="red" id="error_ID_TYPE_MARCHE"></font>
                                      </div>
                                    </div>
                                        <div class="col-md-6" id="docu_avis" hidden="true">
                                          <label for=""> <?= lang('messages_lang.label_avis') ?><font color="red">*</font></label>
                                          <input type="file" accept=".pdf" class="form-control" name="path_avis" id="path_avis" value="<?=set_value("path_avis")?>" id="path_avis">
                                          <font color="red" id="error_path_avis"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('path_avis'); ?>
                                          <?php endif ?>
                                          <br>
                                        </div> 
                                          <div class="col-md-6" id="docu_pv" hidden="true">
                                          <label for=""><?= lang('messages_lang.label_pv') ?><font color="red">*</font></label>
                                          <input type="file" accept=".pdf" class="form-control" name="path_pv" id="path_pv" value="<?=set_value("path_pv")?>" id="path_pv">
                                          <font color="red" id="error_path_pv"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('path_pv'); ?>
                                          <?php endif ?>
                                        </div>
                                          <div class="col-md-6" id="docu_ppm" hidden="true">
                                          <label for=""><?= lang('messages_lang.label_ppm') ?></label>
                                          <input type="file" accept=".pdf" class="form-control" name="PATH_PPM" id="PATH_PPM" value="<?=set_value("PATH_PPM")?>" id="PATH_PPM">
                                          <font color="red" id="error_PATH_PPM"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('PATH_PPM'); ?>
                                          <?php endif ?>
                                          <br>
                                        </div> 
                                        <div class="col-md-6">
                                          <label for=""><?= lang('messages_lang.label_obje') ?> <font color="red">*</font></label>
                                          <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"></textarea>
                                          <font color="red" id="error_COMMENTAIRE"></font>
                                          <?php if (isset($validation)) : ?>
                                            <?= $validation->getError('COMMENTAIRE'); ?>
                                          <?php endif ?>
                                        </div>
                                         <div class="col-md-6">
                                          <div class="form-froup">
                                            <label class="form-label"><?= lang('messages_lang.label_sous_act') ?> <font color="red">*</font></label>
                                            <select onchange="sous_acts();" class="form-control" id="sous_act" name="sous_act" >
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                              <option value="1">Oui</option>
                                              <option value="0">Non</option>
                                            </select>
                                            <font color="red" id="error_sous_act"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('sous_act'); ?>
                                            <?php endif ?>
                                            <br><br>
                                          </div>
                                        </div>  
                                            <div class="col-md-6" id="id_termine" hidden="true">
                                          <div class="form-froup">
                                            <label class="form-label"><?= lang('messages_lang.label_last_act') ?><font color="red">*</font></label>
                                            <select onchange="termine();" class="form-control" id="fini" name="fini" >
                                              <option value=""><?= lang('messages_lang.label_select') ?></option>
                                              <option value="1">Oui</option>
                                              <option value="0">Non</option>
                                            </select>
                                            <font color="red" id="error_fini"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('fini'); ?>
                                            <?php endif ?>
                                          </div>
                                        </div>
                                         <div class="col-md-6" id="id_qte" hidden="true">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_quantite') ?><font color="red">*</font></label>
                                        <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="QTE_RACCROCHE" id="QTE_RACCROCHE" placeholder="" value="<?=set_value('QTE_RACCROCHE')?>" onpaste="return false;" min="1" onkeydown="qte();" onkeyup="qte();">
                                        <font color="red" id="error_QTE_RACCROCHE"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('QTE_RACCROCHE  '); ?>
                                        <?php endif ?>
                                        <br>
                                      </div>
                                    </div> 
                                     <div class="col-md-6" id="id_res" hidden="true">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_result') ?><font color="red">*</font></label>
                                        <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="resultat_attend" id="resultat_attend" placeholder="" value="<?=set_value('resultat_attend')?>" onpaste="return false;" min="1" onkeydown="qte();" onkeyup="qte();">
                                        <font color="red" id="error_resultat_attend"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('resultat_attend  '); ?>
                                        <?php endif ?>
                                      </div>
                                    </div> 
                                     <div class="col-md-6" id="obs_res" hidden="true">
                                  <label for=""><?= lang('messages_lang.label_obser_res') ?><font color="red">*</font></label>
                                 <textarea maxlength="250" class="form-control" name="observ" id="observ"></textarea>
                                 <font color="red" id="error_resultat_observ"></font>
                                  <br>
                                </div>
                                
                                      </div>
                                    </div><hr class="vertical">

                                    <div class="col-md-4 mt-2" style="margin-bottom:50px;margin-left:-42px">
                                      <div class="row">
                                        <input type="hidden" name="TRIMESTRE_ID" id="TRIMESTRE_ID">
                                        <div class="col-md-12">
                                          <label class="form-label"><?= lang('messages_lang.label_prog') ?></label>
                                          <input type="hidden"  id="program_code" name="program_code" >
                                          <input type="text" class="form-control" name="programs" id="programs" readonly>   
                                        </div>
                                        <div class="col-md-12">
                                          <label class="form-label"><?= lang('messages_lang.label_action') ?></label>
                                          <input type="text" class="form-control" id="action" name="action" readonly>
                                          <input type="hidden" name="action_code" id="action_code"> 
                                          <input type="hidden" name="PROGRAMME_ID" id="PROGRAMME_ID"> 
                                          <input type="hidden" name="ACTION_ID" id="ACTION_ID"> 
                                        </div>

                                        <div class="col-md-12">
                                          <label class="form-label"> <?= lang('messages_lang.label_vote_annuel') ?></label>
                                          <input type="text" class="form-control" id="vote_annuel" name="vote_annuel" readonly>
                                          <input type="hidden" name="montant_vote_annuel" id="montant_vote_annuel"> 
                                        </div>

                                        <div class="col-md-12">
                                          <label class="form-label"> <?= lang('messages_lang.label_vote') ?></label>
                                          <input type="text" class="form-control" id="vote" name="vote" readonly>
                                          <input type="hidden" name="montant_vote" id="montant_vote"> 
                                        </div>
                                    
                                        <div class="col-md-12">

                                          <label for=""><?= lang('messages_lang.label_Money_res') ?></label>
                                          <input type="hidden" name="montant_restant" id="montant_restant">
                                          <input type="text" class="form-control"   id="restant" readonly>
                                        </div>

                                        <div class="col-md-12">
                                          <label class="form-label"><?= lang('messages_lang.label_qte_vot') ?> </label>
                                          <input type="text" class="form-control" id="qte_vote" name="qte_vote" readonly>  
                                        </div>
                                        <div class="col-md-12">
                                          <label class="form-label"><?= lang('messages_lang.label_unity') ?> </label>
                                          <input type="text" class="form-control" id="UNITE" name="UNITE" readonly>   
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div style="float: right;" class="col-md-2 mt-5 " >
                                    <div class="form-group " >
                                      <a onclick="saveEtape1()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.label_enre') ?></a>
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
            saissie_ppm();
            type_engage();
            salaire_doc();
            money_devise();
            get_sousTutel();
            get_code();
            get_change();
            get_TacheMoney();
            calculer();
            fois();
            sous_acts();
            fini();
          });
        </script>
        <script type="text/javascript">
         function termine()
         {
           var fini =$('#fini').val() ;

           if (fini==1)
           {
            $('#id_qte').attr('hidden',false);
            $('#id_res').attr('hidden',false);
            $('#obs_res').attr('hidden',false);
           }
           else
           {
            $('#id_qte').attr('hidden',false);
            $('#id_res').attr('hidden',true);
            $('#obs_res').attr('hidden',true);
           }
        }
      </script>
        <script type="text/javascript">
         function sous_acts()
         {
           var sous_act =$('#sous_act').val() ;

           if (sous_act==1)
          {
            $('#id_termine').attr('hidden',false);
            $('#id_qte').attr('hidden',true);
            $('#id_res').attr('hidden',true);
            $('#obs_res').attr('hidden',true);


          }
          else
          {
            $('#id_termine').attr('hidden',true);
            $('#id_qte').attr('hidden',false);
            $('#id_res').attr('hidden',true);
            $('#obs_res').attr('hidden',true);

          }
        }
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
          //fonction pour change le nom du titre du fichier 
          function change_lettre()
          {            
            var BUDGETAIRE_TYPE_DOCUMENT_ID=$('#BUDGETAIRE_TYPE_DOCUMENT_ID option:selected').text();
            if($('#BUDGETAIRE_TYPE_DOCUMENT_ID').val()!='') 
            {
              $('#titre_lettre_dcp').html(BUDGETAIRE_TYPE_DOCUMENT_ID);
              $('#typ_doc').html(BUDGETAIRE_TYPE_DOCUMENT_ID);
              $('#otb_verif_titre').html(BUDGETAIRE_TYPE_DOCUMENT_ID);
            }            
          }

         function saissie_ppm(){
          var MARCHE_PUBLIQUE =$('#MARCHE_PUBLIQUE').val() ;

          if (MARCHE_PUBLIQUE==1)
          {
            $('#docu_ppm').attr('hidden',false);
            $('#tip_march').attr('hidden',false);
            $('#docu_avis').attr('hidden',false);
            $('#docu_pv').attr('hidden',false);
          }
          else
          {
            $('#docu_ppm').attr('hidden',true);
            $('#tip_march').attr('hidden',true);
            $('#docu_avis').attr('hidden',true);
            $('#docu_pv').attr('hidden',true);            
          }

        }
      </script>
      <script type="text/javascript">
       function type_engage(){
        var TYPE_ENGAGEMENT_ID =$('#TYPE_ENGAGEMENT_ID').val() ;

        if (TYPE_ENGAGEMENT_ID==4)
        {
          $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');
          $('#docu_ppm').attr('hidden',true);
        }
        else if(TYPE_ENGAGEMENT_ID==1)
        {
          $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');
          $('#docu_ppm').attr('hidden',true);
        }
        else
        {
         $('#MARCHE_PUBLIQUE').html('<option value=""><?= lang('messages_lang.label_select') ?></option><br><option value="1">Oui</option><br><option value="0">Non</option>');
        }

      }

      function salaire_doc()
      {
        var TYPE_ENGAGEMENT_ID =$('#TYPE_ENGAGEMENT_ID').val() ;
        var INSTITUTION_ID =$('#INSTITUTION_ID').val() ;


        if (TYPE_ENGAGEMENT_ID==1)
        {
          // $('#otb').attr('hidden',false);
          $('#let_trans').attr('hidden',false);
          $('#liste_paie').attr('hidden',false);

          if (INSTITUTION_ID==12)
          {
            $('#lettre_id').text("");
          }
          else
          {
            $('#lettre_id').text("*");
          }
        }
        else
        {
          // $('#otb').attr('hidden',true);
          $('#let_trans').attr('hidden',true);
          $('#liste_paie').attr('hidden',true);

        }
      }
    </script>
    <script type="text/javascript">
      function money_devise()
      {
        var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();

        if (TYPE_MONTANT_ID=='')
        {
          var TYPE_MONTANT_ID = 1;
        }
        if (TYPE_MONTANT_ID != 1)
        {
          $('#mon_dev').attr('hidden',false);
          $('#cou_chang').attr('hidden',false);
          $('#date_dev').attr('hidden',false);
          $('#racc_dev').attr('hidden',false);
          $('#racc_bif').attr('hidden',true);

        }else{
          $('#mon_dev').attr('hidden',true);
          $('#cou_chang').attr('hidden',true);
          $('#date_dev').attr('hidden',true);
          $('#racc_dev').attr('hidden',true);
          $('#racc_bif').attr('hidden',false);

        }
      }
    </script>
    <script type="text/javascript">
      function get_sousTutel()
      {
        var INSTITUTION_ID = $('#INSTITUTION_ID').val();

        if(INSTITUTION_ID=='')
        {
          $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
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
          var url = "<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_sousTutel/"+INSTITUTION_ID;

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
              $('#SOUS_TUTEL_ID').html(data.SousTutel);
              $('#loading_sous_tutel').html("");
            }
          });
        }
      }

      // function get_taux()
      // {
      //   var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();

      //   if(TYPE_MONTANT_ID=='')
      //   {
      //     $('#COUS').val();
      //   }
      //   else
      //   {
      //     $.ajax(
      //     {
      //       url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_taux/"+TYPE_MONTANT_ID,
      //       type:"POST",
      //       dataType:"JSON",
      //       success: function(data)
      //       {
      //         $('#COUS_ECHANGE').val(data.devise);
      //         $('#engagement_cous').val(data.dev);
      //         $('#DEVISE_TYPE_HISTO_ID').val(data.id_taux);
      //       }
      //     });
      //   }
      // }

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
            url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_inst/"+INSTITUTION_ID,
            type:"POST",
            dataType:"JSON",
            success: function(data)
            {
              $('#TYPE_INSTITUTION_ID').val(data.inst_activite);
              if(data.inst_activite==1)
              {
                $('#act_id').attr('hidden',true);
              }
              else
              {
                $('#act_id').attr('hidden',false);
              }
              // alert(data.inst_activite);
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
          $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          var url = "<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_code/"+SOUS_TUTEL_ID;

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

      function get_change()
      {
        var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
        if(TYPE_INSTITUTION_ID=='')
        {
          $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          var id='';
          if(TYPE_INSTITUTION_ID==1)
          {
            var PAP_ACTIVITE_ID = $('#PAP_ACTIVITE_ID').val();
            var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
            id=CODE_NOMENCLATURE_BUDGETAIRE_ID;
            $.ajax(
            {
              url: "<?=base_url('')?>/double_commande_new/Phase_Administrative_Budget/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
              type: "GET",
              dataType: "JSON",
              data:
              {
                PAP_ACTIVITE_ID: PAP_ACTIVITE_ID,
                CODE_NOMENCLATURE_BUDGETAIRE_ID: CODE_NOMENCLATURE_BUDGETAIRE_ID,
                TYPE_INSTITUTION_ID: TYPE_INSTITUTION_ID,
              },
              beforeSend: function()
              {
                $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
              },
              success: function(data) {
                $('#PTBA_TACHE_ID').html(data.tache_activite);
                $('#loading_act').html("");
              }
            });
          }
          else
          {
            var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
            if(CODE_NOMENCLATURE_BUDGETAIRE_ID=='')
            {
              $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
              $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
            }
            else
            {
              var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
              if (TYPE_INSTITUTION_ID==2)
              {
                $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
                $.ajax(
                {
                  url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_activite/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
                  type:"GET",
                  dataType:"JSON",         
                  beforeSend:function()
                  {
                    $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
                  },
                  success: function(data)
                  {
                    $('#PAP_ACTIVITE_ID').html(data.activite);
                    $('#loading_act').html("");
                  }
                });
              }
              else
              {
                $('#act_id').attr('hidden',true);
                get_taches();
              }
            }
          }
        }
      }

      function get_activite()
      {
        var CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
        if(CODE_NOMENCLATURE_BUDGETAIRE_ID=='')
        {
          $('#PAP_ACTIVITE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
          if(TYPE_INSTITUTION_ID==2)
          {
            $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
            $.ajax(
            {
              url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_activite/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
              type:"GET",
              dataType:"JSON",         
              beforeSend:function()
              {
                $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
              },
              success: function(data)
              {
                $('#PAP_ACTIVITE_ID').html(data.activite);
                $('#loading_act').html("");
              }
            });
          }
          else
          {
            $('#act_id').attr('hidden',true);
            get_taches();
          }

        }
      } 

      function get_taches()
      {
        var PAP_ACTIVITE_ID = $('#PAP_ACTIVITE_ID').val();
        var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
        var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();
        // alert(TYPE_INSTITUTION_ID);
        var id = '';

        if (TYPE_INSTITUTION_ID == 1) {
          id = CODE_NOMENCLATURE_BUDGETAIRE_ID;
        } else if (TYPE_INSTITUTION_ID == 2) {
          id = PAP_ACTIVITE_ID;
        } else {
          id = '';
        }

        // alert(id);

        if (PAP_ACTIVITE_ID == '' || CODE_NOMENCLATURE_BUDGETAIRE_ID == '')
        {
          $('#PTBA_TACHE_ID').html('<option value=""><?= lang('messages_lang.label_select') ?></option>');
        }
        else
        {
          $.ajax(
          {
            url: "<?=base_url('')?>/double_commande_new/Phase_Administrative_Budget/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
            type: "GET",
            dataType: "JSON",
            data: {
              PAP_ACTIVITE_ID: PAP_ACTIVITE_ID,
              CODE_NOMENCLATURE_BUDGETAIRE_ID: CODE_NOMENCLATURE_BUDGETAIRE_ID,
              TYPE_INSTITUTION_ID: TYPE_INSTITUTION_ID,
            },
            beforeSend: function() {
              $('#loading_act').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data) {
              $('#PTBA_TACHE_ID').html(data.tache_activite);
              $('#loading_act').html("");
            }
          });
        }
      }


      function get_TacheMoney()
      {
        var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
        if(PTBA_TACHE_ID=='')
        {
          $('#montant_vote').val();
          $('#vote').val();
        }
        else
        {
          $.ajax(
          {
            url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_TacheMoney/"+PTBA_TACHE_ID,
            type:"POST",
            dataType:"JSON",
            success: function(data)
            {
              $('#montant_vote').val(data.montant_vote);
              $('#vote').val(data.vote);
              $('#montant_restant').val(data.montant_restant);
              $('#restant').val(data.restant);
              $('#program_code').val(data.program_code);
              $('#programs').val(data.programs);
              $('#action_code').val(data.action_code);
              $('#action').val(data.action);
              $('#TRIMESTRE_ID').val(data.TRIMESTRE_ID);
              $('#utilise').val(data.utilise);
              $('#Encours').val(data.Encours);
              $('#resteEng').val(data.resteEng);
              $('#UNITE').val(data.UNITE);
              $('#UNITE12').val(data.UNITE);
              $('#qte_vote').val(data.qte_vote);;
              $('#PROGRAMME_ID').val(data.PROGRAMME_ID);
              $('#ACTION_ID').val(data.ACTION_ID);
              $('#montant_vote_annuel').val(data.BUDGET_ANNUEL);
              $('#vote_annuel').val(data.BUDGET_ANNUEL_FORMAT);
              //vider les colonnes des montants
              $('#MONTANT_EN_DEVISE').val('');
              $('#engagement_cous').val('');
              $('#MONTANT_RACCROCHE_devise').val('');
              $('#MONTANT_RACCROCHE').val('');
            }
          });
        }
      }
    </script>
    <script type="text/javascript">
      function calculer()
      {
        var MONTANT_RACCROCHE = $('#MONTANT_RACCROCHE').val();
        var MONTANT_RACCROCHE = MONTANT_RACCROCHE.replace(/ /g, '');
        $('#engagement_budget').val(MONTANT_RACCROCHE);
        var resteEng = $('#montant_restant').val();
        if (MONTANT_RACCROCHE=='')
        {
          var MONTANT_RACCROCHE = 0;
        }
        if (resteEng=='')
        {
          var resteEng = 0;
        }
        var calcul = parseFloat(resteEng) - parseFloat(MONTANT_RACCROCHE);
        var calcul = calcul.toLocaleString('en-US', { useGrouping: true });
        var calcul = calcul.replace(/,/g, ' ');
        $('#restant').val(calcul);
      }

      function fois()
      { 
        var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
        var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/ /g, '');
        $('#engagement_devise').val(MONTANT_EN_DEVISE);

        // var COUS_ECHANGE = $('#COUS_ECHANGE').val();
        var COUS_ECHANGE = $('#engagement_cous').val();
        var COUS_ECHANGE = COUS_ECHANGE.replace(/ /g, '');
        // $('#engagement_cous').val(COUS_ECHANGE);

        var resteEng = $('#montant_restant').val();

        if (MONTANT_EN_DEVISE=='')
        {
          var MONTANT_EN_DEVISE = 0;
        }
        if (COUS_ECHANGE=='')
        {
          var COUS_ECHANGE = 0;
        }
        if (resteEng=='')
        {
          var resteEng = 0;
        }
        var dev = parseFloat(COUS_ECHANGE) * parseFloat(MONTANT_EN_DEVISE);
        var calcul=dev.toFixed(0);
        $('#engagement_budget').val(calcul);
        $('#MONTANT_RACCROCHE').val(calcul);

        var reste = parseFloat(resteEng) - parseFloat(calcul);
        var reste = reste.toLocaleString('en-US', { useGrouping: true });
        var reste = reste.replace(/,/g, ' ');
        $('#restant').val(reste);

        var calcul = calcul.toLocaleString('en-US', { useGrouping: true });
        var calcul = calcul.replace(/,/g, ' ');
        $('#MONTANT_RACCROCHE_devise').val(calcul);
      }
    </script>

    <script>
      function get_docs()
      {
        var TYPE_ENGAGEMENT_ID=$('#TYPE_ENGAGEMENT_ID').val();
        $.ajax(
        {
          url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_docs/"+TYPE_ENGAGEMENT_ID,
          type:"POST",
          dataType:"JSON",
          success: function(data)
          {
            $('#BUDGETAIRE_TYPE_DOCUMENT_ID').html(data.docs);
            change_lettre()
          }
        });    
      }
    </script>

    <script type="text/javascript">
      function saveEtape1()
      {
        var statut=2;

        var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();

        var COMMENTAIRE= $('#COMMENTAIRE').val();
        $('#error_COMMENTAIRE').html('');

        var INSTITUTION_ID = $('#INSTITUTION_ID').val();
        $('#error_INSTITUTION_ID').html('');

        var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
        $('#error_SOUS_TUTEL_ID').html('');

        var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
        $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html('');

        var PAP_ACTIVITE_ID = $('#PAP_ACTIVITE_ID').val();
        $('#error_PAP_ACTIVITE_ID').html('');

        var PTBA_TACHE_ID = $('#PTBA_TACHE_ID').val();
        $('#error_PTBA_TACHE_ID').html('')

        var MONTANT_RACCROCHE = $('#engagement_budget').val();
        var resteEng= $('#montant_restant').val(); 
        $('#error_MONTANT_RACCROCHE').html('');

        var MONTANT_EN_DEVISE = $('#engagement_devise').val();
        $('#error_MONTANT_EN_DEVISE').html('');

        // var COUS_ECHANGE = $('#COUS_ECHANGE').val();
        var COUS_ECHANGE = $('#engagement_cous').val();          
        
        $('#error_COUS_ECHANGE').html('');

        var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
        $('#error_DATE_COUT_DEVISE').html('');

        var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
        $('#error_TYPE_MONTANT_ID').html('');

        var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();
        $('#error_TYPE_ENGAGEMENT_ID').html('');

        var ID_TYPE_MARCHE = $('#ID_TYPE_MARCHE').val();
        $('#error_ID_TYPE_MARCHE').html('');

        var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
        $('#error_MARCHE_PUBLIQUE').html('');

        var PATH_LETTRE_OTB = document.getElementById('PATH_LETTRE_OTB');
        $('#error_PATH_LETTRE_OTB').html('');

        var PATH_LETTRE_TRANSMISSION = document.getElementById('PATH_LETTRE_TRANSMISSION');
        $('#error_PATH_LETTRE_TRANSMISSION').html('');

        var PATH_LISTE_PAIE = document.getElementById('PATH_LISTE_PAIE');
        $('#error_PATH_LISTE_PAIE').html('');

        var PATH_PPM = document.getElementById('PATH_PPM');
        $('#error_PATH_PPM').html('');
        var path_avis = document.getElementById('path_avis');
        $('#error_path_avis').html('');
        var path_pv = document.getElementById('path_pv');
        $('#error_path_pv').html('');
        var maxSize = 20000*1024;

        var BUDGETAIRE_TYPE_DOCUMENT_ID=$('#BUDGETAIRE_TYPE_DOCUMENT_ID').val();
        $('#error_BUDGETAIRE_TYPE_DOCUMENT_ID').html('');
        if(BUDGETAIRE_TYPE_DOCUMENT_ID=='')
        {
          $('#error_BUDGETAIRE_TYPE_DOCUMENT_ID').html('<?=lang('messages_lang.input_oblige')?>');
          statut=1;
        }

        var sous_act  = $('#sous_act').val();
        $('#error_sous_act').html('');

        if (sous_act =='') 
        {
          $('#error_sous_act').html('<?=lang('messages_lang.input_oblige')?>');
          statut=1;
        }

        if (sous_act == 1)
        {
          var fini  = $('#fini').val();
          $('#error_fini').html('');

          if (fini =='') 
          {
            $('#error_fini').html("<?=lang('messages_lang.input_oblige')?>");
            statut=1;
          }

          if (fini == 1)
          {
            
            var observ = $('#observ').val();
            $('error_resultat_observ').html('');

            if (observ == '')
            {
              $('#error_resultat_observ').html("<?=lang('messages_lang.input_oblige')?>");
              statut=1;
            }

            var resultat_attend  = $('#resultat_attend').val();
            $('#error_resultat_attend').html('');

            if (resultat_attend =='') 
            {
              $('#error_resultat_attend').html("<?=lang('messages_lang.input_oblige')?>");
              statut=1;
            }
            var QTE_RACCROCHE  = $('#QTE_RACCROCHE').val();
            $('#error_QTE_RACCROCHE').html('');

            if (QTE_RACCROCHE =='') 
            {
              $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
              statut=1;
            }

            if (QTE_RACCROCHE == 0) 
            {
              $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.qte_neg')?>");
              statut=1;
            }
          }
          else
          {
            var QTE_RACCROCHE  = $('#QTE_RACCROCHE').val();
            $('#error_QTE_RACCROCHE').html('');

            if (QTE_RACCROCHE =='') 
            {
              $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
              statut=1;
            }

            if (QTE_RACCROCHE == 0) 
            {
              $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.qte_neg')?>");
              statut=1;
            }
          }
        }
        else
        {
          var QTE_RACCROCHE  = $('#QTE_RACCROCHE').val();
          $('#error_QTE_RACCROCHE').html('');

          if (QTE_RACCROCHE =='') 
          {
            $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
            statut=1;
          }

          if (QTE_RACCROCHE == 0) 
          {
            $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.qte_neg')?>");
            statut=1;
          }
        }

        if (COMMENTAIRE =='') 
        {
          $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
          statut=1;
        }

        if (PATH_LETTRE_OTB.files.length === 0)
        {
          $('#error_PATH_LETTRE_OTB').html("<?=lang('messages_lang.input_oblige')?>");
          statut = 1;
        }else if (PATH_LETTRE_OTB.files[0].size > maxSize)
        {
          $('#error_PATH_LETTRE_OTB').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }

        if (MARCHE_PUBLIQUE==1)
        {

         if (PATH_PPM.files.length !== 0) 
         {
          if (PATH_PPM.files[0].size > maxSize)
          {
            $('#error_PATH_PPM').html("<?=lang('messages_lang.pdf_max')?>");
            statut = 1;
          }
        }

        if (ID_TYPE_MARCHE=='')
        {
           $('#error_ID_TYPE_MARCHE').html("<?=lang('messages_lang.input_oblige')?>");
           statut=1;
        }
        if(path_pv.files.length === 0)
        {
          $('#error_path_pv').html("<?=lang('messages_lang.input_oblige')?>");
          status=1;
        }else if (path_pv.files[0].size > maxSize)
        {
          $('#error_path_pv').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }

        if(path_avis.files.length === 0)
        {
          $('#error_path_avis').html("<?=lang('messages_lang.input_oblige')?>");
          status=1;
        }else if (path_avis.files[0].size > maxSize)
        {
          $('#error_path_avis').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }

        if (TYPE_ENGAGEMENT_ID == 1)
        {
          if (INSTITUTION_ID==12)
          {
            $('#lettre_id').text("");

            if (PATH_LETTRE_TRANSMISSION.files.length !== 0) 
            {
              if (PATH_LETTRE_TRANSMISSION.files[0].size > maxSize)
              {
                $('#error_PATH_LETTRE_TRANSMISSION').html("<?=lang('messages_lang.pdf_max')?>");
                statut = 1;
              }
            }

          }
          else
          {
            $('#lettre_id').text("*");
            if (PATH_LETTRE_TRANSMISSION.files.length === 0)
            {
              $('#error_PATH_LETTRE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
              statut = 1;
            }
            else if (PATH_LETTRE_TRANSMISSION.files[0].size > maxSize)
            {
              $('#error_PATH_LETTRE_TRANSMISSION').html("<?=lang('messages_lang.pdf_max')?>");
              statut = 1;
            }
          }

          if (PATH_LISTE_PAIE.files.length === 0)
          {

            $('#error_PATH_LISTE_PAIE').html("<?=lang('messages_lang.input_oblige')?>");
            statut = 1;
          }else if (PATH_LISTE_PAIE.files[0].size > maxSize)
          {
            $('#error_PATH_LISTE_PAIE').html("<?=lang('messages_lang.pdf_max')?>");
            statut = 1;
          }
        }

        if (MARCHE_PUBLIQUE=='')
        {
         $('#error_MARCHE_PUBLIQUE').html("<?=lang('messages_lang.input_oblige')?>");
         statut=1;
       }

       if (TYPE_ENGAGEMENT_ID=='')
       {
         $('#error_TYPE_ENGAGEMENT_ID').html("<?=lang('messages_lang.input_oblige')?>");
         statut=1;
       }

       if (TYPE_MONTANT_ID != 1)
       {
         if (MONTANT_EN_DEVISE=='')
         {
           $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.input_oblige')?>");
           statut=1;
         }

         if (COUS_ECHANGE=='')
         {
           $('#error_COUS_ECHANGE').html("<?=lang('messages_lang.input_oblige')?>");
           statut=1;
         }

         if (DATE_COUT_DEVISE=='')
         {
           $('#error_DATE_COUT_DEVISE').html("<?=lang('messages_lang.input_oblige')?>");
           statut=1;
         }


         if (parseFloat(MONTANT_EN_DEVISE) == 0)
         {
          $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.mont_neg')?>");
          statut=1;
        }


       if (MONTANT_EN_DEVISE < 0)
       {
        $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.devi_neg')?>");
        statut = 1;
      }

    }

    if (MONTANT_RACCROCHE=='')
    {
     $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
     statut=1;
   }

   if (parseFloat(MONTANT_RACCROCHE) == 0)
   {
    $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.money_neg')?>");
    statut=1;
  }

  if (parseFloat(MONTANT_RACCROCHE) > parseFloat(resteEng))
  {
    $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.credi_mont')?>");  
    statut=1;   
  }

  if (MONTANT_RACCROCHE < 0)
  {
    $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.mont_neg')?>");
    statut = 1;
  } 


  if (TYPE_INSTITUTION_ID==2)
  {
  if (PAP_ACTIVITE_ID=='')
  {
   $('#error_PAP_ACTIVITE_ID').html("<?=lang('messages_lang.input_oblige')?>");
   statut=1;
  }
 }
 if (PTBA_TACHE_ID=='')
  {
   $('#error_PTBA_TACHE_ID').html("<?=lang('messages_lang.input_oblige')?>");
   statut=1;
 }
 if (INSTITUTION_ID=='')
 {
   $('#error_INSTITUTION_ID').html("<?=lang('messages_lang.input_oblige')?>");
   statut=1;
 }

 if (SOUS_TUTEL_ID=='')
 {
   $('#error_SOUS_TUTEL_ID').html("<?=lang('messages_lang.input_oblige')?>");
   statut=1;
 }

 if (CODE_NOMENCLATURE_BUDGETAIRE_ID=='')
 {
   $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html("<?=lang('messages_lang.input_oblige')?>");
   statut=1;
 }



 var PATH_PPM = $('#PATH_PPM').val();
 var path_pv = $('#path_pv').val();
 var path_avis = $('#path_avis').val(); 
 var PATH_LETTRE_OTB = $('#PATH_LETTRE_OTB').val();
 var PATH_LETTRE_TRANSMISSION = $('#PATH_LETTRE_TRANSMISSION').val();

 var PATH_LISTE_PAIE = $('#PATH_LISTE_PAIE').val();

 var url;

 if (PATH_LETTRE_TRANSMISSION != '')
  {
   var path = PATH_LETTRE_TRANSMISSION;
   var trans = path.split("\\");
   var trans_lettre= trans[trans.length-1];
  }
  else
  {
    var trans_lettre = '<b>-</b>'
  }
  if (PATH_PPM !='')
  {
   var path = PATH_PPM;
   var doc = path.split("\\");
   var documen= doc[doc.length-1];
  }
  else
  {
    var documen = '<b>-</b>'
  }
 var path = path_pv;
 var pv = path.split("\\");
 var pv_attrib= pv[pv.length-1];

 var path = path_avis;
 var avis = path.split("\\");
 var avis_dfcn= avis[avis.length-1];

 var path = PATH_LETTRE_OTB;
 var otb = path.split("\\");
 var otb_lettre= otb[otb.length-1];

 var path = PATH_LISTE_PAIE;
 var paie = path.split("\\");
 var paie_liste= paie[paie.length-1];

 if(statut == 2)
 {

  var MONTANT_RACCROCHE = parseFloat(MONTANT_RACCROCHE);
  var MONTANT_RACCROCHE = MONTANT_RACCROCHE.toLocaleString('en-US', { useGrouping: true });
  var MONTANT_RACCROCHE = MONTANT_RACCROCHE.replace(/,/g, ' ');
  $('#INSTITUTION_ID_verifie').html($('#INSTITUTION_ID option:selected').text());
  $('#SOUS_TUTEL_ID_verifie').html($('#SOUS_TUTEL_ID option:selected').text());

  $('#CODE_NOMENCLATURE_BUDGETAIRE_ID_verifie').html($('#CODE_NOMENCLATURE_BUDGETAIRE_ID option:selected').text());
  
  if (PAP_ACTIVITE_ID!='')
  {
    $('#PAP_ACTIVITE_ID_verifie').html($('#PAP_ACTIVITE_ID option:selected').text());
  }
  else
  {
    $('#PAP_ACTIVITE_ID_verifie').html('-');
  }
  

  $('#PTBA_TACHE_ID_verifie').html($('#PTBA_TACHE_ID option:selected').text());

  $('#MONTANT_RACCROCHE_verifie').html(MONTANT_RACCROCHE);
  $('#TYPE_MONTANT_ID_verifie').html($('#TYPE_MONTANT_ID option:selected').text());
  var MONTANT_EN_DEVISE = parseFloat(MONTANT_EN_DEVISE);
  var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.toLocaleString('en-US', { useGrouping: true });
  var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/,/g, ' ');

  // var COUS_ECHANGE = parseFloat(COUS_ECHANGE);
  // var COUS_ECHANGE = COUS_ECHANGE.toLocaleString('en-US', { useGrouping: true });
  // var COUS_ECHANGE = COUS_ECHANGE.replace(/,/g, ' ');


  $('#MONTANT_EN_DEVISE_verifie').html(MONTANT_EN_DEVISE);
  $('#COUS_ECHANGE_verifie').html(COUS_ECHANGE);
  var DATE_COUT_DEVISE = moment(DATE_COUT_DEVISE, "YYYY/mm/DD");
  var DATE_COUT_DEVISE = DATE_COUT_DEVISE.format("DD/mm/YYYY");
  $('#DATE_COUT_DEVISE_verifie').html(DATE_COUT_DEVISE);


  $('#TYPE_ENGAGEMENT_ID_verifie').html($('#TYPE_ENGAGEMENT_ID option:selected').text());

  if (TYPE_ENGAGEMENT_ID == 5)
  {
    $('#autre_eng').show();
    $('#autre_eng234').show();
  }
  else
  {
    $('#autre_eng').hide();
    $('#autre_eng234').hide();
  }

  if (TYPE_MONTANT_ID !=1)
  {
    $('#mon_cha').show();
    $('#cous_cha').show();
    $('#date_sha').show();

  }
  else
  {
    $('#mon_cha').hide();
    $('#cous_cha').hide();   
    $('#date_sha').hide();
  }

  if (TYPE_ENGAGEMENT_ID == 1)
  {
    $('#paie_v').show();
    $('#trans_v').show();
    // $('#otb_v').show();

  }
  else
  {
    $('#paie_v').hide();
    $('#trans_v').hide();
    // $('#otb_v').hide();

  }

  $('#MARCHE_PUBLIQUE_verifie').html($('#MARCHE_PUBLIQUE option:selected').text());
  if (MARCHE_PUBLIQUE == 1)
  {
    $('#marche').show();
    $('#pv12').show();
    $('#avis12').show();
    $('#tipmarch12').show();
  }
  else
  {
    $('#marche').hide();
    $('#pv12').hide();
    $('#avis12').hide();
    $('#tipmarch12').hide();
  }

  $('#fini_verifie').html($('#fini option:selected').text()); 
  $('#sous_act_verifie').html($('#sous_act option:selected').text());
  $('#RESULTAT_ATTENDUS_verifie').html(resultat_attend);
  $('#QTE_RACCROCHE_verifie').html(QTE_RACCROCHE);
  $('#observ_verifie').html(observ);


  if (sous_act == 1)
  {
    $('#fini_123').show();
    $('#atta').hide();
    $('#observa12').hide();
    $('#qte23').hide();  
    if (fini == 1)
    {
      $('#atta').show();
      $('#observa12').show();      
      $('#qte23').show();  
    }else{
      $('#atta').hide();
      $('#observa12').hide();  
      $('#qte23').show();
    }
  }
  else
  {
    $('#fini_123').hide();
    $('#atta').hide();
    $('#observa12').hide();
    $('#qte23').show();  
  }

  $('#COMMENTAIRE_verifie').html(COMMENTAIRE);
  $('#ID_TYPE_MARCHE_verifie').html($('#ID_TYPE_MARCHE option:selected').text());  
  $('#PATH_LETTRE_OTB_verifie').html(otb_lettre);
  $('#PATH_LETTRE_TRANSMISSION_verifie').html(trans_lettre);
  $('#PATH_LISTE_PAIE_verifie').html(paie_liste);
  $('#PATH_PPM_verifie').html(documen);
  $('#path_pv_verifie').html(pv_attrib);
  $('#path_avis_verifie').html(avis_dfcn);



  $("#engaged").modal("show");
  

}

}
</script>
<script type="text/javascript">
  function qte()
  {
    var QTE_RACCROCHE = $('#QTE_RACCROCHE').val();  
    $('#error_QTE_RACCROCHE').html(''); 

    if (parseFloat(QTE_RACCROCHE) == 0)
    {
      $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.qte_neg')?>"); 
    }
    if(/^0\d/.test(QTE_RACCROCHE))
    {
      $('#QTE_RACCROCHE').val(QTE_RACCROCHE.replace(/^0\d/, ""));
    }           
  }
</script>
<script type="text/javascript">
  function formatInputValue(input) 
  {

    // Remove all non-digit characters from the input value
    var numericValue = input.value.replace(/[^0-9.]/g, '');

  // Format the numeric value with spaces as thousands separators
  var formattedValue = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  
  // Set the formatted value back to the input field
  input.value = formattedValue;
}
</script>
<script type="text/javascript">
  function DoPrevent(e)
  {
    e.preventDefault();
    e.stopPropagation();
  }
  function moneyDevise()
  {
   var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
   // var COUS_ECHANGE = $('#COUS_ECHANGE').val();
   var COUS_ECHANGE = $('#engagement_cous').val();

   var MONTANT_RACCROCHE = $('#engagement_budget').val();
   var engagement_devise = $('#engagement_devise').val();    
   // var engagement_cous = $('#COUS_ECHANGE').val();    
   var resteEng = $('#montant_restant').val();
   $('#error_MONTANT_EN_DEVISE').html('');
   $('#error_COUS_ECHANGE').html(''); 
   $('#error_MONTANT_RACCROCHE124').html(''); 


  if (parseFloat(engagement_devise) == 0)
  {
    $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.mont_neg')?>"); 
  }

  // if(/^0\d/.test(engagement_cous))
  // {
  //   $('#COUS_ECHANGE').val(engagement_cous.replace(/^0\d/, ""));
  // }

  // if(/^0\d/.test(engagement_devise))
  // {
  //   $('#MONTANT_EN_DEVISE').val(engagement_devise.replace(/^0\d/, ""));
  // }

  if (parseFloat(MONTANT_RACCROCHE) > parseFloat(resteEng))
  {
    $('#MONTANT_EN_DEVISE').on('keypress',DoPrevent);  
    // $('#COUS_ECHANGE').on('keypress',DoPrevent); 
    $('#engagement_cous').on('keypress',DoPrevent); 
    $('#error_MONTANT_RACCROCHE124').html("<?=lang('messages_lang.credi_mont')?>"); 
  }else{
    $('#MONTANT_EN_DEVISE').off('keypress',DoPrevent);
    // $('#COUS_ECHANGE').off('keypress',DoPrevent);
    $('#engagement_cous').off('keypress',DoPrevent);
  }

  if (parseFloat(engagement_devise) < 0)
  {
    $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.devi_neg')?>"); 
  }

}
</script>
<script type="text/javascript">
  function DoPrevent(e)
  {
    e.preventDefault();
    e.stopPropagation();
  }
  function moneyRestant()
  {
    var MONTANT_RACCROCHE = $('#engagement_budget').val();    
    var resteEng = $('#montant_restant').val();
    $('#error_MONTANT_RACCROCHE').html(''); 

    if (parseFloat(MONTANT_RACCROCHE) == 0)
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.money_neg')?>"); 
    }

    if(/^0\d/.test(MONTANT_RACCROCHE))
    {
      $('#MONTANT_RACCROCHE').val(MONTANT_RACCROCHE.replace(/^0\d/, ""));
    }

    if (parseFloat(MONTANT_RACCROCHE) > parseFloat(resteEng))
    {
      $('#MONTANT_RACCROCHE').on('keypress',DoPrevent);
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.credi_mont')?>");     
    }else{
      $('#MONTANT_RACCROCHE').off('keypress',DoPrevent);
    }

    if (parseFloat(MONTANT_RACCROCHE) < 0)
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.mont_neg')?>"); 
    }
  }
</script>
<div class="modal fade" id="engaged" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
     <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body overflow-auto" style="max-height: 400px">
      <div class="table-responsive mt-3">
        <table class="table m-b-0 m-t-20">
          <tbody>
            <tr>
              <td><i class="fa fa-home"></i> &nbsp;<strong><?= lang('messages_lang.label_inst') ?></strong></td>
              <td id="INSTITUTION_ID_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td style="width:250px ;"><i class="fa fa-building"></i> &nbsp;<strong><?= lang('messages_lang.label_sousTitre') ?></strong></td>
              <td id="SOUS_TUTEL_ID_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-cubes"></i> &nbsp;<strong><?= lang('messages_lang.label_ligne') ?></strong></td>
              <td id="CODE_NOMENCLATURE_BUDGETAIRE_ID_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td style="width:300px ;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.label_activite') ?></strong></td>
              <td id="PAP_ACTIVITE_ID_verifie" class="text-dark"></td>
            </tr>
            <tr> 
              <td style="width:300px ;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.label_taches') ?></strong></td>
              <td id="PTBA_TACHE_ID_verifie" class="text-dark"></td>
            </tr>
              <tr>
              <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?= lang('messages_lang.label_monnaie') ?></strong></td>
              <td>
                <span id="TYPE_MONTANT_ID_verifie" class="text-dark"></span>
              </td>
            </tr>
              <tr id="mon_cha">
              <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?= lang('messages_lang.label_devise') ?></strong></td>
              <td>
                <span id="MONTANT_EN_DEVISE_verifie" class="text-dark"></span>
              </td>
            </tr>
              <tr id="cous_cha">
              <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?= lang('messages_lang.label_echange') ?></strong></td>
              <td>
                <span id="COUS_ECHANGE_verifie" class="text-dark"></span>
              </td>
            </tr>
              <tr id="date_sha">
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_cours') ?></strong></td>
              <td>
                <span id="DATE_COUT_DEVISE_verifie" class="text-dark"></span>
              </td>
            </tr>
            <tr>
              <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?= lang('messages_lang.label_engage') ?></strong></td>
              <td>
                <span id="MONTANT_RACCROCHE_verifie" class="text-dark"></span>
              </td>
            </tr>

            <tr>
              <td><i class="fa fa-file-import"></i>&nbsp;<strong><?= lang('messages_lang.label_nature') ?></strong></td>
              <td id="TYPE_ENGAGEMENT_ID_verifie" class="text-dark"></td>
            </tr>
            <tr id="paie_v">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?= lang('messages_lang.label_liste') ?></strong></td>
              <td id="PATH_LISTE_PAIE_verifie" class="text-dark"></td>
            </tr>
            <tr id="trans_v">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?= lang('messages_lang.label_lettre') ?></strong></td>
              <td id="PATH_LETTRE_TRANSMISSION_verifie" class="text-dark"></td>
            </tr>
            <tr id="otb_v">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong id="otb_verif_titre"><?= lang('messages_lang.label_note') ?></strong></td>
              <td id="PATH_LETTRE_OTB_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-address-book">&nbsp;</i><strong><?= lang('messages_lang.label_marche') ?></strong></td>
              <td id="MARCHE_PUBLIQUE_verifie" class="text-dark"></td>
            </tr>
            <tr id="tipmarch12">
              <td><i class="fa fa-certificate"></i>&nbsp;<strong><?= lang('messages_lang.label_type_marche') ?></strong></td>
              <td id="ID_TYPE_MARCHE_verifie" class="text-dark"></td>
            </tr>
            <tr id="pv12">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?= lang('messages_lang.label_pv') ?></strong></td>
              <td id="path_pv_verifie" class="text-dark"></td>
            </tr>
            <tr id="avis12">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?= lang('messages_lang.label_avis') ?></strong></td>
              <td id="path_avis_verifie" class="text-dark"></td>
            </tr>
            <tr id="marche">
              <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?= lang('messages_lang.label_ppm') ?></strong></td>
              <td id="PATH_PPM_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-list"></i>&nbsp;<strong><?= lang('messages_lang.label_obje') ?></strong></td>
              <td id="COMMENTAIRE_verifie" class="text-dark"></td>
            </tr>
              <tr>
              <td><i class="fa fa-clock"></i>&nbsp;<strong><?= lang('messages_lang.label_sous_act') ?></strong></td>
              <td id="sous_act_verifie" class="text-dark"></td>
            </tr>
              <tr id="fini_123">
              <td><i class="fa fa-cogs"></i>&nbsp;<strong><?= lang('messages_lang.label_last_act') ?></strong></td>
              <td id="fini_verifie" class="text-dark"></td>
            </tr>
              <tr id="qte23">
              <td><i class="fa fa-certificate"></i>&nbsp;<strong><?= lang('messages_lang.label_quantite') ?></strong></td>
              <td id="QTE_RACCROCHE_verifie" class="text-dark"></td>
            </tr>
              <tr id="atta">
              <td><i class="fa fa-history"></i>&nbsp;<strong><?= lang('messages_lang.label_result') ?></strong></td>
              <td id="RESULTAT_ATTENDUS_verifie" class="text-dark"></td>
            </tr>
            <tr id="observa12">
              <td><i class="fa fa-list"></i>&nbsp;<strong><?= lang('messages_lang.label_obser_res') ?></strong></td>
              <td id="observ_verifie" class="text-dark"></td>
            </tr>
             
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
    <a onclick="Etap1();hideButton()" id="myElement" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
  </div>
</div>
</div>
</div>
<script type="text/javascript">
  function Etap1()
  {
   document.getElementById("myEtape1").submit();
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
