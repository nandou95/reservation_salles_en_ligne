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
  .verybigmodal
  {
    width:100% !important;
  }
</style>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                  <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                </div>

                <div class="car-body">
                  <h4 style="margin-left:4%;margin-top:10px"> <?=$etape_correction?></h4>
                  <br>
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="myEtape1_corrige" id="myEtape1_corrige" action="<?=base_url('double_commande_new/Phase_Administrative_Budget/etape1_correction/')?>" method="post" >
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
                        }
                        ?>
                        <br>

                        <input type="hidden"  id="num_be" name="num_be" value="<?=$get_date_eng['NUMERO_BON_ENGAGEMENT']?>">
                        <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" name="EXECUTION_BUDGETAIRE_ID" value="<?= $get_info['EXECUTION_BUDGETAIRE_ID']?>">
                        <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?= $get_info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                        <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$get_info['ETAPE_DOUBLE_COMMANDE_ID']?>">
                        <input type="hidden" name="PTBA_TACHE_ID_eng" value="<?=$get_info['PTBA_TACHE_ID']?>" id="PTBA_TACHE_ID_eng">
                        <input type="hidden" name="mont_eng1" value="<?=$get_info['ENG_BUDGETAIRE']?>">
                        <input type="hidden" name="DEVISE_TYPE_HISTO_ID" id="DEVISE_TYPE_HISTO_ID" value="<?=$get_info['DEVISE_TYPE_HISTO_ENG_ID']?>">
                        <input type="hidden" id="get_taux_id" value="<?=$get_date_eng['TAUX_ECHANGE_ID']?>">

                        <div class="row">
                          <div class="col-6">
                            <label><b><?=lang('messages_lang.label_motif')?><hr></b></label>
                            <ol>
                              <?php
                              foreach($get_histmotif as $key)
                              {
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
                            <p><?=$date_trans['OBSERVATION']?></p><br>
                          </div>
                        </div>
                        <div class="row" style="border:1px solid #ddd;border-radius:5px">
                          <div class="col-md-7 mt-3 ml-2"style="margin-bottom:50px">
                            <div class="row">
                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.label_date_rec') ?> <b color="red" id="person"></b> <font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                                <font color="red" id="error_DATE_RECEPTION"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_RECEPTION'); ?>
                                <?php endif ?>
                              </div>

                              <div class="col-md-6">
                                <div class='form-froup'>
                                  <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID" value="<?=$get_info['TYPE_INSTITUTION_ID']?>">
                                  <label class="form-label"><?= lang('messages_lang.label_inst') ?><font color="red">*</font></label>
                                  <select onchange="get_sousTutel();get_inst();" class="select2 form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php
                                    foreach($institutions as $inst)
                                    {
                                      if($inst->INSTITUTION_ID==$get_info['INSTITUTION_ID'])
                                      { 
                                        echo "<option value='".$inst->INSTITUTION_ID."' selected>".$inst->CODE_INSTITUTION."-".$inst->DESCRIPTION_INSTITUTION."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$inst->INSTITUTION_ID."' >".$inst->CODE_INSTITUTION."-".$inst->DESCRIPTION_INSTITUTION."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_INSTITUTION_ID"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('INSTITUTION_ID'); ?>
                                  <?php endif ?>
                                </div>
                                <br> 
                              </div>

                              <div class="col-md-6">
                                <div class='form-froup'>
                                  <label class="form-label"> <?= lang('messages_lang.label_sousTitre') ?> <font color="red">*</font></label><b id="loading_sous_tutel"></b>
                                  <select value="<?=$get_info['SOUS_TUTEL_ID']?>" class="select2 form-control" id="SOUS_TUTEL_ID" name="SOUS_TUTEL_ID" onchange="get_code()">
                                    <option><?= lang('messages_lang.label_select') ?></option>
                                    <?php
                                    foreach($sous_titre as $keys)
                                    { 
                                      if($keys->SOUS_TUTEL_ID==$get_info['SOUS_TUTEL_ID'])
                                      { 
                                        echo "<option value='".$keys->SOUS_TUTEL_ID."' selected>".$keys->CODE_SOUS_TUTEL."-".$keys->DESCRIPTION_SOUS_TUTEL."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$keys->SOUS_TUTEL_ID."' >".$keys->CODE_SOUS_TUTEL."-".$keys->DESCRIPTION_SOUS_TUTEL."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_SOUS_TUTEL_ID"></font><br>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="div_tranche">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_ligne') ?> <font color="red">*</font></label><b id="loading_budget"></b>
                                  <select onchange="get_change();"  class="form-control form-select bg-light select2" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                    <option value=""><?= lang('messages_lang.label_select') ?> </option>
                                    <?php
                                    foreach($get_ligne as $ligne)
                                    { 
                                      if($ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID==$get_info['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
                                      { 
                                        echo "<option value='".$ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID."' selected>".$ligne->CODE_NOMENCLATURE_BUDGETAIRE."-".$ligne->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID."' >".$ligne->CODE_NOMENCLATURE_BUDGETAIRE."-".$ligne->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE_ID'); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="act_id"<?=($get_info['TYPE_INSTITUTION_ID']==1)?' hidden="true"':''?>>
                                <div class="form-froup">
                                  <label for=""><?= lang('messages_lang.label_activite') ?> <font color="red">*</font></label><b id="loading_act"></b>
                                  <select onchange="get_taches()" class="form-control form-select bg-light select2" id="PAP_ACTIVITE_ID" name="PAP_ACTIVITE_ID" placeholder="Sélectionnez l'activité" autocomplete="off" aria-label=".form-select-lg example" >
                                    <option value="<?=set_value('PAP_ACTIVITE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                    <?php
                                    foreach($get_activite as $activite)
                                    {
                                      if($activite->PAP_ACTIVITE_ID==$get_info['PAP_ACTIVITE_ID'])
                                      { 
                                        echo "<option value='".$activite->PAP_ACTIVITE_ID."' selected>".$activite->DESC_PAP_ACTIVITE."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$activite->PAP_ACTIVITE_ID."' >".$activite->DESC_PAP_ACTIVITE."</option>"; 
                                      }
                                    }?>
                                  </select>

                                  <font color="red" id="error_PAP_ACTIVITE_ID"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('PAP_ACTIVITE_ID'); ?>
                                  <?php endif ?>
                                  <br>
                                </div>
                              </div>

                              <div class="col-md-6">
                                <div class="form-froup">
                                  <label for=""><?= lang('messages_lang.label_taches') ?> <font color="red">*</font></label><b id="loading_act"></b>
                                  <select onchange="get_TacheMoney()" class="form-control form-select bg-light select2" id="PTBA_TACHE_ID" name="PTBA_TACHE_ID" placeholder="Sélectionnez l'activité" autocomplete="off" aria-label=".form-select-lg example" >
                                    <option value="<?=set_value('PTBA_TACHE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                    <?php
                                    foreach($get_taches as $tache)
                                    { 
                                      if($tache->PTBA_TACHE_ID==$get_info['PTBA_TACHE_ID'])
                                      { 
                                        echo "<option value='".$tache->PTBA_TACHE_ID."' selected>".$tache->DESC_TACHE."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$tache->PTBA_TACHE_ID."' >".$tache->DESC_TACHE."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>

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
                                  <select onchange="money_devise();" class="select2 form-control" name="TYPE_MONTANT_ID" id="TYPE_MONTANT_ID">
                                    <?php
                                    foreach($get_device as $dev)
                                    {
                                      if ($dev->DEVISE_TYPE_ID==$get_date_eng['TAUX_ECHANGE_ID'])
                                      { 
                                        echo "<option value='".$dev->DEVISE_TYPE_ID."' selected>".$dev->DESC_DEVISE_TYPE."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$dev->DEVISE_TYPE_ID."' >".$dev->DESC_DEVISE_TYPE."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TYPE_MONTANT_ID"><?= $validation->getError('TYPE_MONTANT_ID'); ?></font>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="mon_dev" hidden="true">
                                <label for=""><?= lang('messages_lang.label_devise') ?><font color="red">*</font></label>
                                <input onpaste="return false;" onkeyup="fois();" oninput="formatInputValue(this);" onkeydown="moneyDevise();" value="<?=number_format($get_info['ENG_BUDGETAIRE_DEVISE'],0,',',' ')?>" type="text" class="form-control" name="MONTANT_EN_DEVISE" id="MONTANT_EN_DEVISE">
                                <input type="hidden" name="engagement_devise" id="engagement_devise" value="<?=$get_info['ENG_BUDGETAIRE_DEVISE']?>">
                                <font color="red" id="error_MONTANT_EN_DEVISE"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('MONTANT_EN_DEVISE'); ?>
                                <?php endif ?>
                                <br>
                              </div>

                              <div class="col-md-6" id="cou_chang" hidden="true">
                                <label for=""> <?= lang('messages_lang.label_echange') ?> <font color="red">*</font> </label>
                                <input value="<?=number_format($get_date_eng['COUR_DEVISE'],4,'.',' ')?>"  type="text" class="form-control" name="COUS_ECHANGE" id="COUS_ECHANGE" oninput="formatInputValue(this);" onkeyup="fois();" onkeydown="moneyDevise();">
                                <input type="hidden" name="engagement_cous" id="engagement_cous" value="<?=$get_date_eng['COUR_DEVISE']?>">

                                <font color="red" id="error_COUS_ECHANGE"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('COUS_ECHANGE'); ?>
                                <?php endif ?>
                              </div>

                              <div class="col-md-6" id="racc_dev"  hidden="true">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_engage') ?> <font color="red">*</font></label>
                                  <input type="text" class="form-control " name="MONTANT_RACCROCHE_devise" id="MONTANT_RACCROCHE_devise" value="<?=$get_info['ENG_BUDGETAIRE']?>" readonly>
                                  <font color="red" id="error_MONTANT_RACCROCHE124"></font>
                                  <br>
                                </div>
                              </div>

                              <div class="col-md-6" id="date_dev"  hidden="true">
                                <label for=""><?= lang('messages_lang.label_date_cours') ?><font color="red">*</font></label>
                                <input type="date" value="<?= $retVal = (!empty($get_date_eng['DATE_COUR_DEVISE'])) ? $get_date_eng['DATE_COUR_DEVISE'] : '' ;  ?>"  max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_COUT_DEVISE" id="DATE_COUT_DEVISE">
                                <font color="red" id="error_DATE_COUT_DEVISE"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_COUT_DEVISE'); ?>
                                <?php endif ?>
                              </div>

                              <div class="col-md-6" id="racc_bif">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_engage') ?> <font color="red">*</font></label>
                                  <input onpaste="return false;" value="<?=number_format($get_info['ENG_BUDGETAIRE'],4,',',' ')?>" type="text" class="form-control " name="MONTANT_RACCROCHE" id="MONTANT_RACCROCHE" placeholder="0" value="<?=set_value('MONTANT_RACCROCHE')?>" onpaste="return false;" min="0" oninput="formatInputValue(this);" onkeydown="moneyRestant()" onkeyup="moneyRestant();calculer()" >
                                  <font color="red" id="error_MONTANT_RACCROCHE"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('MONTANT_RACCROCHE'); ?>
                                  <?php endif ?>
                                  <br>
                                  <input type="hidden" name="engagement_budget" id="engagement_budget" value="<?=$get_info['ENG_BUDGETAIRE']?>">
                                </div>
                              </div>

                              <div class="col-md-6" id="bon123" style="display: none;">
                                <div class="form-froup">
                                  <label for=""><?= lang('messages_lang.label_num') ?> <font color="red">*</font></label>
                                  <input onpaste="return false;" oninput="this.value = this.value.toUpperCase()" maxlength="20" onkeyup="number()" onkeydown="number()"  value="<?=$get_date_eng['NUMERO_BON_ENGAGEMENT']?>" type="text" class="form-control" name="NUMERO_BON_ENGAGEMENT" id="NUMERO_BON_ENGAGEMENT">
                                  <font color="red" id="error_NUMERO_BON_ENGAGEMENT"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('NUMERO_BON_ENGAGEMENT'); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6">
                                <div class='form-froup'>
                                  <label class="form-label"><?= lang('messages_lang.label_nature') ?> <font color="red">*</font></label>
                                  <select onchange="type_engage();salaire_doc();get_docs()" class="form-control" name="TYPE_ENGAGEMENT_ID" id="TYPE_ENGAGEMENT_ID">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php
                                    foreach($grande as $key)
                                    { 
                                      if($key->TYPE_ENGAGEMENT_ID==$get_date_eng['TYPE_ENGAGEMENT_ID'])
                                      { 
                                        echo "<option value='".$key->TYPE_ENGAGEMENT_ID."' selected>".$key->DESC_TYPE_ENGAGEMENT."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$key->TYPE_ENGAGEMENT_ID."' >".$key->DESC_TYPE_ENGAGEMENT."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_TYPE_ENGAGEMENT_ID"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('TYPE_ENGAGEMENT_ID'); ?>
                                  <?php endif ?>
                                  <br>
                                </div>
                              </div>

                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.th_type_document') ?><font color="red">*</font></label>
                                <select name="BUDGETAIRE_TYPE_DOCUMENT_ID" id="BUDGETAIRE_TYPE_DOCUMENT_ID" onchange="change_lettre()" class="form-control">
                                  <option value=""><?= lang('messages_lang.label_select') ?></option>
                                  <?php
                                  foreach($get_typ_docs as $keys)
                                  {
                                    if($keys->BUDGETAIRE_TYPE_DOCUMENT_ID==$get_date_eng['BUDGETAIRE_TYPE_DOCUMENT_ID'])
                                    {
                                      ?>
                                      <option value="<?=$keys->BUDGETAIRE_TYPE_DOCUMENT_ID ?>" selected><?=$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT ?></option>
                                      <?php
                                    }
                                    else
                                    {
                                      ?>
                                      <option value="<?=$keys->BUDGETAIRE_TYPE_DOCUMENT_ID ?>"><?=$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT ?></option>
                                      <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <font color="red" id="error_BUDGETAIRE_TYPE_DOCUMENT_ID"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('BUDGETAIRE_TYPE_DOCUMENT_ID'); ?>
                                <?php endif ?>
                                <br>
                              </div>

                              <div class="col-md-6" id="otb">
                                <label id="titre_lettre_dcp"><?= lang('messages_lang.label_note') ?></label>  <a href="#" data-toggle="modal" data-target="#otb_val"><span class="fa fa-file-pdf" style="color:red;"></span></a>
                                <input type="hidden" name="PATH_LETTRE_OTB_old" id="PATH_LETTRE_OTB_old" value="<?=$get_date_eng['PATH_LETTRE_OTB']?>">
                                <input type="file" accept=".pdf" class="form-control"  name="PATH_LETTRE_OTB_edit" id="PATH_LETTRE_OTB_edit" value="<?=set_value("PATH_LETTRE_OTB_edit")?>">
                                <font color="red" id="error_PATH_LETTRE_OTB_edit"></font><br>
                              </div>

                              <div class="col-md-6" id="let_trans" hidden="true">
                                <label for=""> <?= lang('messages_lang.label_lettre') ?> </label> 
                                <?php if(!empty($get_date_eng['PATH_LETTRE_TRANSMISSION'])){ ?><a href="#" data-toggle="modal" data-target="#tans_val"><span class="fa fa-file-pdf" style="color:red;"></span></a><?php } ?>
                                <input type="hidden" name="PATH_LETTRE_TRANSMISSION_old" id="PATH_LETTRE_TRANSMISSION_old" value="<?=$get_date_eng['PATH_LETTRE_TRANSMISSION']?>">
                                <input type="file" accept=".pdf" class="form-control"  name="PATH_LETTRE_TRANSMISSION_edit" id="PATH_LETTRE_TRANSMISSION_edit" value="<?=set_value("PATH_LETTRE_TRANSMISSION_edit")?>">
                                <font color="red" id="error_PATH_LETTRE_TRANSMISSION_edit"></font>
                                <br>
                              </div>

                              <div class="col-md-6" id="liste_paie" hidden="true">
                                <label for=""> <?= lang('messages_lang.label_liste') ?></label>
                                <a href="#" data-toggle="modal" data-target="#paie_val"><span class="fa fa-file-pdf" style="color:red;"></span></a>
                                <input type="hidden" name="PATH_LISTE_PAIE_old" id="PATH_LISTE_PAIE_old" value="<?=$get_date_eng['PATH_LISTE_PAIE']?>">
                                <input type="file" accept=".pdf" class="form-control"  name="PATH_LISTE_PAIE_edit" id="PATH_LISTE_PAIE_edit" value="<?=set_value("PATH_LISTE_PAIE_edit")?>">
                                <font color="red" id="error_PATH_LISTE_PAIE_edit"></font>
                                <br>                                   
                              </div>

                              <div class="col-md-6" id="eng123" style="display: none;">
                                <div class='form-froup'>
                                  <label for=""><?= lang('messages_lang.label_date_eng') ?> <font color="red">*</font></label>  
                                  <input type="date" min="<?=date('Y-m-d', strtotime($debut))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();"  class="form-control" onkeypress="return false" name="DATE_ENG_BUDGETAIRE" id="DATE_ENG_BUDGETAIRE" value="<?=$get_date_eng['DATE_ENGAGEMENT_BUDGETAIRE']?>">
                                  <font color="red" id="error_DATE_ENG_BUDGETAIRE"></font>
                                  <?php
                                  if (isset($validation))
                                  {
                                    ?>
                                    <?= $validation->getError('DATE_ENG_BUDGETAIRE'); ?>
                                    <?php
                                  }
                                  ?>
                                  <br>
                                </div>
                              </div>

                              <?php
                              if($get_info['MARCHE_PUBLIQUE']==1)
                              {
                                ?>
                                <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="1">
                                <div class="col-md-6">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.label_marche') ?> <font color="red">*</font></label>
                                    <select onchange="saissie_ppm();" class="form-control" id="MARCHE_PUBLIQUE" name="MARCHE_PUBLIQUE">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  $marche=array(0=>'Non',1=>'Oui');?>

                                      <?php 
                                      foreach($marche as $key => $value)
                                      {
                                        if($key==$get_info['MARCHE_PUBLIQUE'])
                                        { 
                                          echo "<option value='".$key."' selected>".$value."</option>";
                                        }
                                        else
                                        {
                                          echo "<option value='".$key."' >".$value."</option>"; 
                                        }
                                      }
                                      ?>
                                    </select>
                                    <font color="red" id="error_MARCHE_PUBLIQUE"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('MARCHE_PUBLIQUE'); ?>
                                    <?php endif ?>
                                    <br> 
                                  </div>
                                </div>
                                <?php
                              }
                              else
                              {
                                ?>
                                <div class="col-md-6">
                                  <div class="form-froup">
                                    <label class="form-label"><?= lang('messages_lang.label_marche') ?><font color="red">*</font></label>
                                    <select onchange="saissie_ppm();" class="form-control" id="MARCHE_PUBLIQUE" name="MARCHE_PUBLIQUE">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  $marche=array(0=>'Non',1=>'Oui');?>
                                      <?php 
                                      foreach($marche as $key => $value)
                                      { 
                                        if($key==$get_info['MARCHE_PUBLIQUE'])
                                        { 
                                          echo "<option value='".$key."' selected>".$value."</option>";
                                        }
                                        else
                                        {
                                          echo "<option value='".$key."' >".$value."</option>"; 
                                        }
                                      }
                                      ?>
                                    </select>
                                    <font color="red" id="error_MARCHE_PUBLIQUE"></font>
                                    <?php
                                    if(isset($validation))
                                    {
                                      ?>
                                      <?= $validation->getError('MARCHE_PUBLIQUE'); ?>
                                      <?php
                                    }
                                    ?>
                                    <br> 
                                  </div>
                                </div>
                                <?php
                              }
                              ?>

                              <div class="col-md-6" id="tip_march" hidden="true">
                                <div class='form-froup'>
                                  <label class="form-label"><?= lang('messages_lang.label_type_marche') ?><font color="red">*</font></label>
                                  <select class="form-control" name="ID_TYPE_MARCHE" id="ID_TYPE_MARCHE">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php
                                    foreach($type_marche as $key)
                                    { 
                                      if($key->ID_TYPE_MARCHE==$get_date_eng['ID_TYPE_MARCHE'])
                                      { 
                                        echo "<option value='".$key->ID_TYPE_MARCHE."' selected>".$key->DESCR_MARCHE."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$key->ID_TYPE_MARCHE."' >".$key->DESCR_MARCHE."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_ID_TYPE_MARCHE"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('ID_TYPE_MARCHE'); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="docu_avis" hidden="true">
                                <input type="hidden" name="path_avis_old" id="path_avis_old" value="<?=$get_date_eng['PATH_AVIS_DNCMP']?>">
                                <label for=""> <?= lang('messages_lang.label_avis') ?> </label><?php if(!empty($get_date_eng['PATH_AVIS_DNCMP'])){ ?><a href="#" data-toggle="modal" data-target="#avis_corrige"><span class="fa fa-file-pdf" style="color:red;"></span></a><?php } ?> 
                                <input type="file" accept=".pdf" class="form-control" name="path_avis_edit" id="path_avis_edit" value="<?=set_value("path_avis_edit")?>">
                                <font color="red" id="error_path_avis_edit"></font>
                              </div>

                              <div class="col-md-6" id="docu_pv" hidden="true">
                                <input type="hidden" name="path_pv_old" id="path_pv_old" value="<?=$get_date_eng['PATH_PV_ATTRIBUTION']?>">
                                <label for=""> <?= lang('messages_lang.label_pv') ?> </label><?php if(!empty($get_date_eng['PATH_PV_ATTRIBUTION'])){ ?><a href="#" data-toggle="modal" data-target="#pv_corrige"><span class="fa fa-file-pdf" style="color:red;"></span></a><?php } ?> 
                                <input type="file" accept=".pdf" class="form-control" name="path_pv_edit" id="path_pv_edit" value="<?=set_value("path_pv_edit")?>">
                                <font color="red" id="error_path_pv_edit"></font>
                                <br>
                              </div>

                              <div class="col-md-6" id="docu_ppm" hidden="true">
                                <input type="hidden" name="PATH_PPM_old" id="PATH_PPM_old" value="<?=$get_date_eng['PATH_PPM']?>">
                                <label for=""> <?= lang('messages_lang.label_ppm') ?> </label>   <?php if(!empty($get_date_eng['PATH_PPM'])){ ?><a href="#" data-toggle="modal" data-target="#ppm_corrige"><span class="fa fa-file-pdf" style="color:red;"></span></a><?php } ?> 
                                <input type="file" accept=".pdf" class="form-control" name="PATH_PPM_edit" id="PATH_PPM_edit" value="<?=set_value("PATH_PPM")?>">
                                <font color="red" id="error_PATH_PPM_edit"></font>
                              </div>

                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.label_obje') ?><font color="red">*</font></label>
                                <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"><?= $retVal = (!empty($get_info['COMMENTAIRE'])) ? $get_info['COMMENTAIRE'] : '' ;?></textarea>
                                <font color="red" id="error_COMMENTAIRE"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('COMMENTAIRE'); ?>
                                <?php endif ?>
                                <br>
                              </div>

                              <div class="col-md-6">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_sous_act') ?><font color="red">*</font></label>
                                  <select onchange="sous_acts();" class="form-control" id="sous_act" name="sous_act" >
                                    <?php  $s_act=array(0=>'Non',1=>'Oui');?>

                                    <?php 
                                    foreach($s_act as $key => $value)
                                    { 
                                      if ($key==$get_date_eng['EST_SOUS_TACHE'])
                                      { 
                                        echo "<option value='".$key."' selected>".$value."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$key."' >".$value."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_sous_act"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('sous_act'); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="id_termine" hidden="true">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_last_act') ?><font color="red">*</font></label>
                                  <input type="hidden" name="fini123" value="<?=$get_date_eng['EST_FINI_TACHE']?>">
                                  <select onchange="termine();" class="form-control" id="fini" name="fini" >
                                    <?php  $act_fin=array(0=>'Non',1=>'Oui');?>
                                    <?php 
                                    foreach($s_act as $key => $value)
                                    { 
                                      if($key==$get_date_eng['EST_FINI_TACHE'])
                                      { 
                                        echo "<option value='".$key."' selected>".$value."</option>";
                                      }
                                      else
                                      {
                                        echo "<option value='".$key."' >".$value."</option>"; 
                                      }
                                    }
                                    ?>
                                  </select>
                                  <font color="red" id="error_fini"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('fini'); ?>
                                  <?php endif ?>
                                  <br>
                                </div>
                              </div>

                              <div class="col-md-6" id="id_qte">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_quantite') ?> <font color="red">*</font></label>
                                  <input type="hidden" name="qte123" value="<?=$get_info['QTE_RACCROCHE']?>">
                                  <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="QTE_RACCROCHE" id="QTE_RACCROCHE" placeholder="0" value="<?=$get_info['QTE_RACCROCHE']?>" onkeydown="qte();" onkeyup="qte();" onpaste="return false;" min="1">
                                  <font color="red" id="error_QTE_RACCROCHE"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('QTE_RACCROCHE  '); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="id_res" hidden="true">
                                <div class="form-froup">
                                  <label class="form-label"><?= lang('messages_lang.label_result') ?><font color="red">*</font></label>
                                  <input type="hidden" name="attend" value="<?=$get_date_eng['RESULTAT_ATTENDUS']?>">
                                  <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="resultat_attend" id="resultat_attend" placeholder="" value="<?=$get_date_eng['RESULTAT_ATTENDUS']?>" onpaste="return false;" min="1" onkeydown="qte();" onkeyup="qte();">
                                  <font color="red" id="error_resultat_attend"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('resultat_attend  '); ?>
                                  <?php endif ?>
                                </div>
                              </div>

                              <div class="col-md-6" id="obs_res" hidden="true">
                                <label for=""><?= lang('messages_lang.label_obser_res') ?> <font color="red">*</font></label>
                                <input type="hidden" name="obser11" id="obser11" value="<?=$get_date_eng['OBSERVATION_RESULTAT']?>">
                                <textarea maxlength="250" class="form-control" name="observ" id="observ"><?=$get_date_eng['OBSERVATION_RESULTAT']?></textarea>
                                <font color="red" id="error_resultat_observ"></font>
                                <br>
                              </div>

                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.label_date_tra') ?> <b color="red" id="persTran"></b><font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION">
                                <font color="red" id="error_DATE_TRANSMISSION"></font>
                                <?php
                                if(isset($validation))
                                {
                                  ?>
                                  <?= $validation->getError('DATE_TRANSMISSION'); ?>
                                  <?php
                                }
                                ?>
                                <br>
                              </div>
                            </div>
                          </div>

                          <hr class="vertical">

                          <div class="col-md-4 mt-2" style="margin-bottom:50px;margin-left:-42px">
                            <div class="row">
                              <input type="hidden" name="TRIMESTRE_ID" id="TRIMESTRE_ID">
                              <div class="col-md-12">
                                <label class="form-label"> <?= lang('messages_lang.label_prog') ?></label>
                                <input type="hidden"  id="program_code" name="program_code" >
                                <input type="text" class="form-control" name="programs" id="programs" readonly>   
                              </div>

                              <div class="col-md-12">
                                <label class="form-label"> <?= lang('messages_lang.label_action') ?></label>
                                <input type="text" class="form-control" id="action" name="action" readonly>
                                <input type="hidden" name="action_code" id="action_code"> 
                                <input type="hidden" name="PROGRAMME_ID" id="PROGRAMME_ID"> 
                                <input type="hidden" name="ACTION_ID" id="ACTION_ID">   
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
                                <label class="form-label"> <?= lang('messages_lang.label_qte_vot') ?></label>
                                <input type="text" class="form-control" id="qte_vote" name="qte_vote" readonly>  
                              </div>

                              <div class="col-md-12">
                                <label class="form-label"> <?= lang('messages_lang.label_unity') ?></label>
                                <input type="text" class="form-control" id="UNITE" name="UNITE" readonly>   
                              </div>
                            </div>
                          </div>
                        </div>

                        <div style="float: right;" class="col-md-2 mt-5 " >
                          <div class="form-group " >
                            <a onclick="saveEtape1_corrige()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.label_enre') ?></a>
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
  function changeDate()
  {
    $('#DATE_TRANSMISSION').prop('min', $('#DATE_ENG_BUDGETAIRE').val());
    $('#DATE_TRANSMISSION').prop('min', $('#DATE_RECEPTION').val());
  }
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
</script>
<script type="text/javascript">
  $(document).ready(function ()
  {
    get_TacheMoney();
    var TYPE_ENGAGEMENT_ID = <?= $get_date_eng['TYPE_ENGAGEMENT_ID'] ?>;
    if (TYPE_ENGAGEMENT_ID == 1) 
    {
      get_docs();
    }

    change_lettre();
    salaire_doc();

    // sous_acts();
    // get_inst();

    var type_mont =$('#get_taux_id').val() ;
    if(type_mont!=1)
    {
      moneyDevise();
      money_devise();
      // get_taux();
      calculer();
      fois();
    }

    var num_be = document.getElementById('num_be').value;
    if(num_be == '')
    {
      document.getElementById('bon123').style.display = 'none';
      document.getElementById('image123').style.display = 'none';
      document.getElementById('eng123').style.display = 'none';
      $('#person').text("(SE)");
      $('#persTran').text("(SE)");
    }
    else
    {
      document.getElementById('bon123').style.display = 'block';
      document.getElementById('image123').style.display = 'block';
      document.getElementById('eng123').style.display = 'block';
      $('#person').text("(CED)");
      $('#persTran').text("(CED)");
    }

    var sous_act =$('#sous_act').val() ;
    if(sous_act==1)
    {
      $('#id_termine').attr('hidden',false);
      // $('#id_qte').attr('hidden',true);
      $('#obs_res').attr('hidden',true);
      $('#id_res').attr('hidden',true);
    }
    else
    {
      $('#id_termine').attr('hidden',true);
      // $('#id_qte').attr('hidden',false);
      $('#obs_res').attr('hidden',true);
      $('#id_res').attr('hidden',true);
    }

    var fini =$('#fini').val() ;

    if(fini==1)
    {
      // $('#id_qte').attr('hidden',false);
      $('#id_res').attr('hidden',false);
      $('#obs_res').attr('hidden',false);
    }
    else
    {
      // $('#id_qte').attr('hidden',false);
      $('#id_res').attr('hidden',true);
      $('#obs_res').attr('hidden',true);
    }

    $('#DATE_TRANSMISSION').prop('min', $('#DATE_ENG_BUDGETAIRE').val());

    var TYPE_ENGAGEMENT_ID =$('#TYPE_ENGAGEMENT_ID').val() ;
    if(TYPE_ENGAGEMENT_ID==4)
    {
      $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');          
    }
    else if(TYPE_ENGAGEMENT_ID==1)
    {
      $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');
    }

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
  });
</script>
<script type="text/javascript">
  function termine()
  {
    var fini =$('#fini').val() ;

    if (fini==1)
    {
      // $('#id_qte').attr('hidden',false);
      $('#id_res').attr('hidden',false);
      $('#obs_res').attr('hidden',false);
    }
    else
    {
      // $('#id_qte').attr('hidden',false);
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
      // $('#id_qte').attr('hidden',true);
      $('#obs_res').attr('hidden',true);
      $('#id_res').attr('hidden',true);
    }
    else
    {
      $('#id_res').attr('hidden',true);
      $('#id_termine').attr('hidden',true);
      // $('#id_qte').attr('hidden',false);
      $('#obs_res').attr('hidden',true);
    }
  }
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
      $('#titre_modal').html(BUDGETAIRE_TYPE_DOCUMENT_ID);
    }            
  }

  function saissie_ppm()
  {
    var MARCHE_PUBLIQUE =$('#MARCHE_PUBLIQUE').val() ;
    if(MARCHE_PUBLIQUE==1)
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
  function type_engage()
  {
    var TYPE_ENGAGEMENT_ID =$('#TYPE_ENGAGEMENT_ID').val();
    if (TYPE_ENGAGEMENT_ID==4)
    {
      $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');
      $('#docu_ppm').attr('hidden',true);
      $('#tip_march').attr('hidden',true);
      $('#docu_avis').attr('hidden',true);
      $('#docu_pv').attr('hidden',true);         
    }
    else if(TYPE_ENGAGEMENT_ID==1)
    {
      $('#MARCHE_PUBLIQUE').html('<option value="0">Non</option>');
      $('#docu_ppm').attr('hidden',true);
      $('#tip_march').attr('hidden',true);
      $('#docu_avis').attr('hidden',true);
      $('#docu_pv').attr('hidden',true);
    }
    else
    {
      $('#MARCHE_PUBLIQUE').html('<option value=""><?= lang('messages_lang.label_select') ?></option><br><option value="1">Oui</option><br><option value="0">Non</option>');
    }
  }

  function salaire_doc()
  {
    var TYPE_ENGAGEMENT_ID =$('#TYPE_ENGAGEMENT_ID').val() ;

    if (TYPE_ENGAGEMENT_ID==1)
    {
      // $('#otb').attr('hidden',false);
      $('#let_trans').attr('hidden',false);
      $('#liste_paie').attr('hidden',false);
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
  //   var DEVISE_TYPE_HISTO_ENG_ID = $('#DEVISE_TYPE_HISTO_ENG_ID').val();
  //   if(DEVISE_TYPE_HISTO_ENG_ID=='')
  //   {
  //     $('#COUS').val();
  //   }
  //   else
  //   {
  //     $.ajax(
  //     {
  //       url:"<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/get_taux/"+DEVISE_TYPE_HISTO_ENG_ID,
  //       type:"POST",
  //       dataType:"JSON",
  //       success: function(data)
  //       {
  //         $('#COUS_ECHANGE').val(data.devise);
  //         $('#engagement_cous').val(data.dev);
  //         // $('#DEVISE_TYPE_HISTO_ID').val(data.id_taux);
  //         if(data.id_taux!=1)
  //         {
  //           multiplication()
  //         }              
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
        }
      });

      var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();

      if(TYPE_INSTITUTION_ID==2)
      {
        $('#act_id').attr('hidden',true);
      }
      else
      {
        $('#act_id').attr('hidden',false);
      }
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
        }
      });
    }
  }
</script>


<script type="text/javascript">
  function number()
  {
    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT').val();
    $('#error_NUMERO_BON_ENGAGEMENT').html('');
    if (NUMERO_BON_ENGAGEMENT.length > 20)
    {
     $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.numer_eng')?>");
     statut=1;
   }
 }
</script>
<script type="text/javascript">
  function calculer()
  {
    var MONTANT_RACCROCHE = $('#MONTANT_RACCROCHE').val();
    var MONTANT_RACCROCHE = MONTANT_RACCROCHE.replace(/ /g, '');
    $('#engagement_budget').val(MONTANT_RACCROCHE);
    var reste_mon = $('#montant_restant').val();

    if (MONTANT_RACCROCHE=='')
    {
      var MONTANT_RACCROCHE = 0;
    }
    if (reste_mon=='')
    {
      var reste_mon = 0;
    }
    var calcul = parseFloat(reste_mon) - parseFloat(MONTANT_RACCROCHE);
    var calcul = calcul.toLocaleString('en-US', { useGrouping: true });
    var calcul = calcul.replace(/,/g, ' ');
    $('#restant').val(calcul);
  }

  //multiplier directement si on change le type montant
  function multiplication()
  {     
    var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
    if (MONTANT_EN_DEVISE!='') 
    {
      fois();
    }
  }

  //multiplier si on tape dans montant devise
  function fois()
  { 
    var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
    var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/ /g, '');
    $('#engagement_devise').val(MONTANT_EN_DEVISE);

    var COUS_ECHANGE = $('#COUS_ECHANGE').val();
    var COUS_ECHANGE = COUS_ECHANGE.replace(/ /g, '');
    $('#engagement_cous').val(COUS_ECHANGE);

    var reste_mon = $('#montant_restant').val();

    // alert(MONTANT_EN_DEVISE);

    if (MONTANT_EN_DEVISE=='')
    {
      var MONTANT_EN_DEVISE = 0;
    }
    if (COUS_ECHANGE=='')
    {
      var COUS_ECHANGE = 0;
    }
    if (reste_mon=='')
    {
      var reste_mon = 0;
    }
    var dev = parseFloat(COUS_ECHANGE) * parseFloat(MONTANT_EN_DEVISE);
    var calcul=dev.toFixed(0);
    $('#engagement_budget').val(calcul);
    $('#MONTANT_RACCROCHE').val(calcul);

    var reste = parseFloat(reste_mon) - parseFloat(calcul);
    var reste = reste.toLocaleString('en-US', { useGrouping: true });
    var reste = reste.replace(/,/g, ' ');
    $('#restant').val(reste);

    var calcul = calcul.toLocaleString('en-US', { useGrouping: true });
    var calcul = calcul.replace(/,/g, ' ');
    $('#MONTANT_RACCROCHE_devise').val(calcul);
  }
</script>
<script type="text/javascript">
  function saveEtape1_corrige()
  {
    var COMMENTAIRE= $('#COMMENTAIRE').val();
    var num_be = $('#num_be').val();
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    $('#error_INSTITUTION_ID').html('');

    var TYPE_INSTITUTION_ID = $('#TYPE_INSTITUTION_ID').val();

    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    $('#error_SOUS_TUTEL_ID').html('');

    var CODE_NOMENCLATURE_BUDGETAIRE = $('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    $('#error_CODE_NOMENCLATURE_BUDGETAIRE').html('');

    var PTBA_ID = $('#PTBA_ID').val();
    $('#error_PTBA_ID').html('');

    var MONTANT_RACCROCHE = $('#engagement_budget').val();
    var reste_mon= $('#montant_restant').val(); 
    $('#error_MONTANT_RACCROCHE').html('');

    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT').val();
    $('#error_NUMERO_BON_ENGAGEMENT').html('');

    var GRANDE_MASSE_ID = $('#GRANDE_MASSE_ID').val();
    $('#error_GRANDE_MASSE_ID').html('');

    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
    $('#error_MARCHE_PUBLIQUE').html('');

    var PATH_LETTRE_OTB_edit = document.getElementById('PATH_LETTRE_OTB_edit');
    $('#error_PATH_LETTRE_OTB_edit').html('');

    // var PATH_BON_ENGAGEMENT_edit = document.getElementById('PATH_BON_ENGAGEMENT_edit');
    // $('#error_PATH_BON_ENGAGEMENT_edit').html('');

    var PATH_LETTRE_TRANSMISSION_edit = document.getElementById('PATH_LETTRE_TRANSMISSION_edit');
    $('#error_PATH_LETTRE_TRANSMISSION_edit').html('');

    var PATH_LISTE_PAIE_edit = document.getElementById('PATH_LISTE_PAIE_edit');
    $('#error_PATH_LISTE_PAIE_edit').html('');

    var PATH_PPM_edit = document.getElementById('PATH_PPM_edit');
    $('#error_PATH_PPM_edit').html('');
    var path_avis_edit = document.getElementById('path_avis_edit');
    $('#error_path_avis_edit').html('');
    var path_pv_edit = document.getElementById('path_pv_edit');
    $('#error_path_pv_edit').html('');

    var maxSize = 20000*1024;

    var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();
    $('#error_TYPE_ENGAGEMENT_ID').html('');

    var MONTANT_EN_DEVISE = $('#engagement_devise').val();
    $('#error_MONTANT_EN_DEVISE').html('');

    var COUS_ECHANGE = $('#engagement_cous').val(); 
    $('#error_COUS_ECHANGE').html('');

    var ID_TYPE_MARCHE = $('#ID_TYPE_MARCHE').val();
    $('#error_ID_TYPE_MARCHE').html('');

    var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
    $('#error_DATE_COUT_DEVISE').html('');

    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();

    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    $('#error_DATE_RECEPTION').html('');

    var statut=2;

    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var BUDGETAIRE_TYPE_DOCUMENT_ID=$('#BUDGETAIRE_TYPE_DOCUMENT_ID').val();
    $('#error_BUDGETAIRE_TYPE_DOCUMENT_ID').html('');
    if(BUDGETAIRE_TYPE_DOCUMENT_ID=='')
    {
      $('#error_BUDGETAIRE_TYPE_DOCUMENT_ID').html('<?=lang('messages_lang.input_oblige')?>');
      statut=1;
    }

    // if (PATH_BON_ENGAGEMENT_edit.files.length !== 0) 
    // {
    //   if (PATH_BON_ENGAGEMENT_edit.files[0].size > maxSize)
    //   {
    //     $('#error_PATH_BON_ENGAGEMENT_edit').html("<?=lang('messages_lang.pdf_max')?>");
    //     statut = 1;
    //   }
    // }

    if(MARCHE_PUBLIQUE==1)
    { 
      if (ID_TYPE_MARCHE=='')
      {
        $('#error_ID_TYPE_MARCHE').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }

      if(path_avis_edit.files.length !== 0) 
      {
        if (path_avis_edit.files[0].size > maxSize)
        {
          $('#error_path_avis_edit').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }

      if(path_pv_edit.files.length !== 0) 
      {
        if (path_pv_edit.files[0].size > maxSize)
        {
          $('#error_path_pv_edit').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }

      if(PATH_PPM_edit.files.length !== 0) 
      {
        if (PATH_PPM_edit.files[0].size > maxSize)
        {
          $('#error_PATH_PPM_edit').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }
    }

    if(TYPE_ENGAGEMENT_ID==1)
    { 

      if (PATH_LISTE_PAIE_edit.files.length !== 0) 
      {
        if (PATH_LISTE_PAIE_edit.files[0].size > maxSize)
        {
          $('#error_PATH_LISTE_PAIE_edit').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }
      // if (PATH_LETTRE_OTB_edit.files.length !== 0) 
      // {
      //   if (PATH_LETTRE_OTB_edit.files[0].size > maxSize)
      //   {
      //     $('#error_PATH_LETTRE_OTB_edit').html("<?=lang('messages_lang.pdf_max')?>");
      //     statut = 1;
      //   }
      // }
      if(PATH_LETTRE_TRANSMISSION_edit.files.length !== 0) 
      {
        if (PATH_LETTRE_TRANSMISSION_edit.files[0].size > maxSize)
        {
          $('#error_PATH_LETTRE_TRANSMISSION_edit').html("<?=lang('messages_lang.pdf_max')?>");
          statut = 1;
        }
      }
    }

    if (PATH_LETTRE_OTB_edit.files.length !== 0) 
    {
      if (PATH_LETTRE_OTB_edit.files[0].size > maxSize)
      {
        $('#error_PATH_LETTRE_OTB_edit').html("<?=lang('messages_lang.pdf_max')?>");
        statut = 1;
      }
    }

    if(MARCHE_PUBLIQUE=='')
    {
      $('#error_MARCHE_PUBLIQUE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(TYPE_ENGAGEMENT_ID=='')
    {
      $('#error_TYPE_ENGAGEMENT_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(TYPE_MONTANT_ID != 1)
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

      if(DATE_COUT_DEVISE=='')
      {
        $('#error_DATE_COUT_DEVISE').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }

      if(parseFloat(MONTANT_EN_DEVISE) == 0)
      {
        $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.mont_neg')?>");
        statut=1;
      }

      if(MONTANT_EN_DEVISE < 0)
      {
        $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.devi_neg')?>");
        statut = 1;
      }
    }

    var DATE_ENG_BUDGETAIRE = $('#DATE_ENG_BUDGETAIRE').val();
    $('#error_DATE_ENG_BUDGETAIRE').html('');

    var sous_act  = $('#sous_act').val();
    $('#error_sous_act').html('');

    if(sous_act =='') 
    {
      $('#error_sous_act').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var QTE_RACCROCHE=$('#QTE_RACCROCHE').val();
    $('#error_QTE_RACCROCHE').html('');

    if (QTE_RACCROCHE =='')
    {
      $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(QTE_RACCROCHE == 0) 
    {
      $('#error_QTE_RACCROCHE').html("<?=lang('messages_lang.qte_neg')?>");
      statut=1;
    }

    if(sous_act == 1)
    {
      var fini  = $('#fini').val();
      $('#error_fini').html('');

      if (fini =='') 
      {
        $('#error_fini').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }

      if(fini == 1)
      {
        var observ = $('#observ').val();
        $('error_resultat_observ').html('');

        if(observ == '')
        {
          $('#error_resultat_observ').html("<?=lang('messages_lang.input_oblige')?>");
          statut=1;
        }

        var resultat_attend  = $('#resultat_attend').val();
        $('#error_resultat_attend').html('');

        if(resultat_attend =='') 
        {
          $('#error_resultat_attend').html("<?=lang('messages_lang.input_oblige')?>");
          statut=1;
        }
      }
    }

    if(COMMENTAIRE =='')
    {
      $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }


    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');

    if(num_be !=='')
    {
      if (DATE_ENG_BUDGETAIRE=='') 
      {
        $('#error_DATE_ENG_BUDGETAIRE').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
    }

    if(DATE_TRANSMISSION=='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(GRANDE_MASSE_ID=='')
    {
      $('#error_GRANDE_MASSE_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(num_be !=='')
    {
      if(NUMERO_BON_ENGAGEMENT=='')
      {
        $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }

      if(NUMERO_BON_ENGAGEMENT.length > 20)
      {
        $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.numer_eng')?>");
        statut=1;
      }
    }

    if(MONTANT_RACCROCHE=='')
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(parseFloat(MONTANT_RACCROCHE) == 0)
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.money_neg')?>");
      statut=1;
    }

    if(parseFloat(MONTANT_RACCROCHE) > parseFloat(reste_mon))
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.credi_mont')?>");  
      statut=1;   
    }

    if(MONTANT_RACCROCHE < 0)
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.mont_neg')?>");
      statut = 1;
    }

    if(PTBA_ID=='')
    {
      $('#error_PTBA_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(INSTITUTION_ID=='')
    {
      $('#error_INSTITUTION_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(SOUS_TUTEL_ID=='')
    {
      $('#error_SOUS_TUTEL_ID').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(CODE_NOMENCLATURE_BUDGETAIRE=='')
    {
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    var url;
    if(statut == 2)
    {
      var PATH_PPM_edit = $('#PATH_PPM_edit').val();
      var path_pv_edit = $('#path_pv_edit').val();
      var path_avis_edit = $('#path_avis_edit').val();
      // var PATH_BON_ENGAGEMENT_edit = $('#PATH_BON_ENGAGEMENT_edit').val();
      var PATH_LETTRE_OTB_edit = $('#PATH_LETTRE_OTB_edit').val();
      var PATH_LETTRE_TRANSMISSION_edit = $('#PATH_LETTRE_TRANSMISSION_edit').val();
      var PATH_LISTE_PAIE_edit = $('#PATH_LISTE_PAIE_edit').val();

      if(PATH_PPM_edit !='')
      {
        var path = PATH_PPM_edit;
        var doc = path.split("\\");
        var documen= doc[doc.length-1];
      }
      else
      {
        var documen= '<a href="#" data-toggle="modal" data-target="#ppm_corrige"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      if(path_pv_edit !='')
      {
        var path = path_pv_edit;
        var pv = path.split("\\");
        var pv_edit= pv[pv.length-1];
      }
      else
      {
        var pv_edit= '<a href="#" data-toggle="modal" data-target="#pv_corrige"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      if(path_avis_edit !='')
      {
        var path = path_avis_edit;
        var avis = path.split("\\");
        var avis_edit= avis[avis.length-1];
      }
      else
      {
        var avis_edit= '<a href="#" data-toggle="modal" data-target="#avis_corrige"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      if(PATH_LETTRE_OTB_edit !='')
      {
        var path = PATH_LETTRE_OTB_edit;
        var doc = path.split("\\");
        var PATH_LETTRE_OTB_edit= doc[doc.length-1];
      }
      else
      {
        var PATH_LETTRE_OTB_edit= '<a href="#" data-toggle="modal" data-target="#otb_val"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      if(PATH_LETTRE_TRANSMISSION_edit !='')
      {
        var path = PATH_LETTRE_TRANSMISSION_edit;
        var doc = path.split("\\");
        var PATH_LETTRE_TRANSMISSION_edit= doc[doc.length-1];
      }
      else
      {
        var PATH_LETTRE_TRANSMISSION_edit='<a href="#" data-toggle="modal" data-target="#tans_val"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      if(PATH_LISTE_PAIE_edit !='')
      {
        var path = PATH_LISTE_PAIE_edit;
        var doc = path.split("\\");
        var PATH_LISTE_PAIE_edit= doc[doc.length-1];
      }
      else
      {
        var PATH_LISTE_PAIE_edit='<a href="#" data-toggle="modal" data-target="#paie_val"><span class="fa fa-file-pdf" style="color:red;font-size: 150%;"></span></a>';
      }

      var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");
      var MONTANT_RACCROCHE = parseFloat(MONTANT_RACCROCHE);
      var MONTANT_RACCROCHE = MONTANT_RACCROCHE.toLocaleString('en-US', { useGrouping: true });
      var MONTANT_RACCROCHE = MONTANT_RACCROCHE.replace(/,/g, ' ');

      $('#INSTITUTION_ID_verifie').html($('#INSTITUTION_ID option:selected').text());
      $('#SOUS_TUTEL_ID_verifie').html($('#SOUS_TUTEL_ID option:selected').text());
      $('#CODE_NOMENCLATURE_BUDGETAIRE_ID_verifie').html($('#CODE_NOMENCLATURE_BUDGETAIRE_ID option:selected').text());
      $('#PAP_ACTIVITE_ID_verifie').html($('#PAP_ACTIVITE_ID option:selected').text());
      $('#PTBA_TACHE_ID_verifie').html($('#PTBA_TACHE_ID option:selected').text());
      $('#MONTANT_RACCROCHE_verifie').html(MONTANT_RACCROCHE);
      $('#NUMERO_BON_ENGAGEMENT_verifie').html(NUMERO_BON_ENGAGEMENT);
      $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);
      $('#TYPE_ENGAGEMENT_ID_verifie').html($('#TYPE_ENGAGEMENT_ID option:selected').text());
      var num_be = $('#num_be').val();

      if (num_be=='')
      {
        $('#be').hide();
        $('#dat').hide();
        // $('#imj').hide();
      }
      else
      {
        $('#be').show();
        $('#dat').show();
        // $('#imj').show();
      }

      if(TYPE_ENGAGEMENT_ID == 5)
      {
        $('#autre_eng').show();
        $('#autre_eng234').show();
      }
      else
      {
        $('#autre_eng').hide();
        $('#autre_eng234').hide();
      }

      var DATE_ENG_BUDGETAIRE = moment(DATE_ENG_BUDGETAIRE, "YYYY/mm/DD");
      var DATE_ENG_BUDGETAIRE = DATE_ENG_BUDGETAIRE.format("DD/mm/YYYY");

      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

      $('#DATE_ENG_BUDGETAIRE_verifie').html(DATE_ENG_BUDGETAIRE);
      $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);

      $('#MARCHE_PUBLIQUE_verifie').html($('#MARCHE_PUBLIQUE option:selected').text());

      if (MARCHE_PUBLIQUE == 1)
      {
        $('#marche').show();
        $('#pv12').show();
        $('#avis12').show();
        $('#tipmarch12').show();
        $('#MARCHE_PUBLIQUE_verifie').html('Oui');
      }
      else
      {
        $('#marche').hide();
        $('#pv12').hide();
        $('#avis12').hide();
        $('#tipmarch12').hide();
        $('#MARCHE_PUBLIQUE_verifie').html('Non');   
      }

      $('#fini_verifie').html($('#fini option:selected').text()); 
      $('#sous_act_verifie').html($('#sous_act option:selected').text());
      $('#RESULTAT_ATTENDUS_verifie').html(resultat_attend);
      $('#QTE_RACCROCHE_verifie').html(QTE_RACCROCHE);
      var observ = $('#observ').val();
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
        }
        else
        {
          $('#atta').hide();
          $('#observa12').hide();
          $('#qte23').show();
        }
      }
      else
      {
        $('#fini_123').hide();
        $('#atta').show();
        $('#observa12').hide();
        $('#qte23').show();  
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

      $('#ID_TYPE_MARCHE_verifie').html($('#ID_TYPE_MARCHE option:selected').text());  
      $('#PATH_PPM_verifie').html(documen);
      $('#path_pv_verifie').html(pv_edit);
      $('#path_avis_verifie').html(avis_edit);
      // $('#PATH_BON_ENGAGEMENT_verifie').html(PATH_BON_ENGAGEMENT_edit);
      $('#PATH_LETTRE_OTB_verifie').html(PATH_LETTRE_OTB_edit);
      $('#PATH_LETTRE_TRANSMISSION_verifie').html(PATH_LETTRE_TRANSMISSION_edit);
      $('#PATH_LISTE_PAIE_verifie').html(PATH_LISTE_PAIE_edit);
      $('#COMMENTAIRE_verifie').html(COMMENTAIRE);

      var MONTANT_EN_DEVISE = parseFloat(MONTANT_EN_DEVISE);
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.toLocaleString('en-US', { useGrouping: true });
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/,/g, ' ');

      var COUS_ECHANGE = parseFloat(COUS_ECHANGE);
      var COUS_ECHANGE = COUS_ECHANGE.toLocaleString('en-US', { useGrouping: true });
      var COUS_ECHANGE = COUS_ECHANGE.replace(/,/g, ' ');

      $('#TYPE_MONTANT_ID_verifie').html($('#TYPE_MONTANT_ID option:selected').text());
      $('#MONTANT_EN_DEVISE_verifie').html(MONTANT_EN_DEVISE);
      $('#COUS_ECHANGE_verifie').html(COUS_ECHANGE);
      var DATE_COUT_DEVISE = moment(DATE_COUT_DEVISE, "YYYY/mm/DD");
      var DATE_COUT_DEVISE = DATE_COUT_DEVISE.format("DD/mm/YYYY");

      $('#DATE_COUT_DEVISE_verifie').html(DATE_COUT_DEVISE);

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
      $("#engaged_corrige").modal("show");
    }
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
  function DoPrevent(e)
  {
    e.preventDefault();
    e.stopPropagation();
  }
  function moneyDevise()
  {
    var MONTANT_EN_DEVISE = $('#MONTANT_EN_DEVISE').val();
    var COUS_ECHANGE = $('#COUS_ECHANGE').val();

    var MONTANT_RACCROCHE = $('#engagement_budget').val();
    var engagement_devise = $('#engagement_devise').val();    
    var engagement_cous = $('#engagement_cous').val();    
    var resteEng = $('#montant_restant').val();
    $('#error_MONTANT_EN_DEVISE').html('');
    $('#error_COUS_ECHANGE').html(''); 
    $('#error_MONTANT_RACCROCHE124').html(''); 

    if (parseFloat(engagement_devise) == 0)
    {
      $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.mont_neg')?>"); 
    }

    if(/^0\d/.test(engagement_cous))
    {
      $('#COUS_ECHANGE').val(engagement_cous.replace(/^0\d/, ""));
    }

    if(/^0\d/.test(engagement_devise))
    {
      $('#MONTANT_EN_DEVISE').val(engagement_devise.replace(/^0\d/, ""));
    }

    if (parseFloat(MONTANT_RACCROCHE) > parseFloat(resteEng))
    {
      $('#MONTANT_EN_DEVISE').on('keypress',DoPrevent);  
      $('#COUS_ECHANGE').on('keypress',DoPrevent); 
      $('#error_MONTANT_RACCROCHE124').html("<?=lang('messages_lang.credi_mont')?>"); 
    }else{
      $('#MONTANT_EN_DEVISE').off('keypress',DoPrevent);
      $('#COUS_ECHANGE').off('keypress',DoPrevent);      
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
    var reste_mon = $('#montant_restant').val();
    $('#error_MONTANT_RACCROCHE').html('');

    if (parseFloat(MONTANT_RACCROCHE) == 0)
    {
      $('#error_MONTANT_RACCROCHE').html("<?=lang('messages_lang.money_neg')?>");
    }

    if(/^0\d/.test(MONTANT_RACCROCHE))
    {
      $('#MONTANT_RACCROCHE').val(MONTANT_RACCROCHE.replace(/^0\d/, ""));
    }

    if (parseFloat(MONTANT_RACCROCHE) > parseFloat(reste_mon))
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

<div class="modal fade" id="engaged_corrige" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body overflow-auto" style="max-height: 400px">
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-home"></i> &nbsp;<strong><?= lang('messages_lang.label_inst') ?></strong></td>
                <td id="INSTITUTION_ID_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-building"></i> &nbsp;<strong><?= lang('messages_lang.label_sousTitre') ?></strong></td>
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
              <tr id="dat">
                <td><i class="fa fa-calendar"></i>&nbsp;<strong><?= lang('messages_lang.label_date_eng') ?></strong></td>
                <td id="DATE_ENG_BUDGETAIRE_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?></strong></td>
                <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i>&nbsp;<strong><?= lang('messages_lang.label_date_tra') ?></strong></td>
                <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
              </tr>
              <tr id="be">
                <td style="width:300px ;"><i class="fa fa-certificate"></i>&nbsp;<strong><?= lang('messages_lang.label_num') ?></strong></td>
                <td id="NUMERO_BON_ENGAGEMENT_verifie" class="text-dark"></td>
              </tr>
              <!-- <tr id="imj">
                <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?//= lang('messages_lang.label_bon') ?></strong></td>
                <td id="PATH_BON_ENGAGEMENT_verifie" class="text-dark"></td>
              </tr> -->
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
                <td><i class="fa fa-address-book"></i>&nbsp;<strong><?= lang('messages_lang.label_marche') ?></strong></td>
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
      <button type="button" id="mode1" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
      <a onclick="Etap1_corrige();hideButton()" id="corr" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
    </div>
  </div>
</div>
</div>
<script type="text/javascript">
  function Etap1_corrige()
  {
    document.getElementById("myEtape1_corrige").submit();
  }
</script>

<script>
  function hideButton()
  {
    var element = document.getElementById("corr");
    element.style.display = "none";

    var elementmod = document.getElementById("mode1");
    elementmod.style.display = "none";
  }
</script>

<div class='modal fade' id='avis_corrige'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class="modal-header">
        <center> <?= lang('messages_lang.label_avis') ?></center>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class='modal-body'>
        <center>
          <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_AVIS_DNCMP'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
          </center>
        </div>
        <div class='modal-footer'>
          <button class='btn btn-primary btn-md' data-dismiss='modal'>
            <?= lang('messages_lang.label_ferm') ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class='modal fade' id='pv_corrige'>
    <div class='modal-dialog'>
      <div class='modal-content'>
        <div class="modal-header">
          <center> <?= lang('messages_lang.label_pv') ?></center>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class='modal-body'>
          <center>
            <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_PV_ATTRIBUTION'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
            </center>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-primary btn-md' data-dismiss='modal'>
              <?= lang('messages_lang.label_ferm') ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class='modal fade' id='ppm_corrige'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class="modal-header">
            <center><?= lang('messages_lang.label_ppm') ?></center>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class='modal-body'>
            <center>
              <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_PPM'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
              </center>
            </div>
            <div class='modal-footer'>
              <button class='btn btn-primary btn-md' data-dismiss='modal'>
                <?= lang('messages_lang.label_ferm') ?>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class='modal fade' id='otb_val'>
        <div class='modal-dialog'>
          <div class='modal-content'>
            <div class="modal-header">
              <center id="titre_modal"><?= lang('messages_lang.label_note') ?></center>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class='modal-body'>
              <center>
                <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LETTRE_OTB'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                </center>
              </div>
              <div class='modal-footer'>
                <button class='btn btn-primary btn-md' data-dismiss='modal'>
                  <?= lang('messages_lang.label_ferm') ?>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class='modal fade' id='bon_eng'>
          <div class='modal-dialog'>
            <div class='modal-content'>
              <div class="modal-header">
                <center><?= lang('messages_lang.label_bon') ?></center>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class='modal-body'>
                <center>
                  <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_BON_ENGAGEMENT'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                  </center>
                </div>
                <div class='modal-footer'>
                  <button class='btn btn-primary btn-md' data-dismiss='modal'>
                    <?= lang('messages_lang.label_ferm') ?>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class='modal fade' id='tans_val'>
            <div class='modal-dialog'>
              <div class='modal-content'>
                <div class="modal-header">
                  <center><?= lang('messages_lang.label_lettre') ?></center>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class='modal-body'>
                  <center>
                    <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LETTRE_TRANSMISSION'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                    </center>
                  </div>
                  <div class='modal-footer'>
                    <button class='btn btn-primary btn-md' data-dismiss='modal'>
                      <?= lang('messages_lang.label_ferm') ?>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class='modal fade' id='paie_val'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <div class="modal-header">
                    <center><?= lang('messages_lang.label_liste') ?></center>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class='modal-body'>
                    <center>
                      <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LISTE_PAIE'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                      </center>
                    </div>
                    <div class='modal-footer'>
                      <button class='btn btn-primary btn-md' data-dismiss='modal'>
                        <?= lang('messages_lang.label_ferm') ?>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
