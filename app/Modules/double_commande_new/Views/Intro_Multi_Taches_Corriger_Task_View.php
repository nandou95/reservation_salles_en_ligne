<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo view('includesbackend/header.php');?>
    <?php $validation = \Config\Services::validation();?>
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
                    <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn')?></a>
                  </div>
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape1?></h4>
                    <br>
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myEtape1" id="myEtape1" action="<?=base_url('double_commande_new/Introduction_Budget_Multi_Taches/save_etape1/')?>" method="post" >
                        <div class="container">
                          <center class="ml-5" style="height=100px;width:90%" >
                            <div class="w-100 bg-danger text-white text-center"  id="message">
                            </div>
                          </center>
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-7 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                <input type="hidden" name="id_exec_titr_dec" id="id_exec_titr_dec" value="<?=$id_exec_titr_dec?>">
                                <input type="hidden" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?= !empty($task['EXECUTION_BUDGETAIRE_ID']) ? $task['EXECUTION_BUDGETAIRE_ID'] : '' ?>">
                                <input type="hidden" name="EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID" id="EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID" value="<?= !empty($task['EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID']) ? $task['EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID'] : '' ?>">
                                <input type="hidden" id="get_taux_id" value="<?=$task['DEVISE_TYPE_ID']?>">
                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label"><?= lang('messages_lang.label_inst') ?><font color="red">*</font></label>
                                    <select onchange="get_sousTutel();get_inst();" class="select2 form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                      <option value=""><?= lang('messages_lang.label_select') ?></option>
                                      <?php  foreach ($institutions as $keys) { ?>
                                        <?php if($keys->INSTITUTION_ID==$task['INSTITUTION_ID']) { ?>
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
                                  <label for=""><?= lang('messages_lang.label_Note') ?><font color="red">*</font></label>
                                  <input type="text" class="form-control" id="NOTE_REFERENCE" name="NOTE_REFERENCE" value="<?= !empty($task['NOTE_REFERENCE']) ? $task['NOTE_REFERENCE'] : '' ?>">
                                  <font color="red" id="error_NOTE_REFERENCE"></font>
                                  <br>  
                                </div>
                              </div>

                                <div class="row">
                                    <div class="col-md-6">
                                      <div class='form-froup'>
                                        <label class="form-label"><?= lang('messages_lang.label_sousTitre') ?><font color="red">*</font></label><b id="loading_sous_tutel"></b>
                                        <select class="select2 form-control" id="SOUS_TUTEL_ID" value="<?=set_value('SOUS_TUTEL_ID') ?>" name="SOUS_TUTEL_ID" onchange="get_code()">
                                          <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          <?php foreach($sous_titre as $keys) { 
                                            if ($keys->SOUS_TUTEL_ID==$task['SOUS_TUTEL_ID']) { 
                                              echo "<option value='".$keys->SOUS_TUTEL_ID."' selected>".$keys->CODE_SOUS_TUTEL."-".$keys->DESCRIPTION_SOUS_TUTEL."</option>";
                                            } else{
                                              echo "<option value='".$keys->SOUS_TUTEL_ID."' >".$keys->CODE_SOUS_TUTEL."-".$keys->DESCRIPTION_SOUS_TUTEL."</option>"; 
                                            } }?>
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
                                          <?php foreach($get_ligne as $ligne) { 
                                            if ($ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID==$task['CODE_NOMENCLATURE_BUDGETAIRE_ID']) { 
                                              echo "<option value='".$ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID."' selected>".$ligne->CODE_NOMENCLATURE_BUDGETAIRE."-".$ligne->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</option>";
                                            } else{
                                              echo "<option value='".$ligne->CODE_NOMENCLATURE_BUDGETAIRE_ID."' >".$ligne->CODE_NOMENCLATURE_BUDGETAIRE."-".$ligne->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</option>"; 
                                            } }?>
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
                                          <?php foreach($get_activite as $activite)
                                          {
                                            if ($activite->PAP_ACTIVITE_ID==$task['PAP_ACTIVITE_ID'])
                                            { 
                                              echo "<option value='".$activite->PAP_ACTIVITE_ID."' selected>".$activite->DESC_PAP_ACTIVITE."</option>";
                                            }
                                            else
                                            {
                                              echo "<option value='".$activite->PAP_ACTIVITE_ID."' >".$activite->DESC_PAP_ACTIVITE."</option>"; 
                                            } 
                                          }?>
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
                                      <input type="hidden" id="task_id" value="<?=$task['PTBA_TACHE_ID']?>">
                                      <div class="form-froup">
                                        <label for=""><?= lang('messages_lang.label_taches') ?> <font color="red">*</font></label><b id="loading_task"></b>
                                        <select onchange="get_TacheMoney()" class="form-control form-select bg-light select2" id="PTBA_TACHE_ID" name="PTBA_TACHE_ID" placeholder="Sélectionnez la tache" autocomplete="off" aria-label=".form-select-lg example" >
                                          <option value="<?=set_value('PTBA_TACHE_ID')?>"><?= lang('messages_lang.label_select') ?> </option>
                                          <?php foreach($get_taches as $tache)
                                          { 
                                            if ($tache->PTBA_TACHE_ID==$task['PTBA_TACHE_ID'])
                                            { 
                                              echo "<option value='".$tache->PTBA_TACHE_ID."' selected>".$tache->DESC_TACHE."</option>";
                                            }
                                            else
                                            {
                                              echo "<option value='".$tache->PTBA_TACHE_ID."' >".$tache->DESC_TACHE."</option>"; 
                                            }
                                          }?>
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
                                      <div class='form-froup'>
                                        <label class="form-label"><?= lang('messages_lang.label_nature') ?> <font color="red">*</font></label>
                                        <select class="form-control" name="TYPE_ENGAGEMENT_ID" id="TYPE_ENGAGEMENT_ID">
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>
                                          <?php foreach($grande as $key)
                                          { 
                                            if ($key->TYPE_ENGAGEMENT_ID==$task['TYPE_ENGAGEMENT_ID'])
                                            { 
                                              echo "<option value='".$key->TYPE_ENGAGEMENT_ID."' selected>".$key->DESC_TYPE_ENGAGEMENT."</option>";
                                            }
                                            else
                                            {
                                              echo "<option value='".$key->TYPE_ENGAGEMENT_ID."' >".$key->DESC_TYPE_ENGAGEMENT."</option>"; 
                                            }
                                          }?> 
                                        </select>
                                            <font color="red" id="error_TYPE_ENGAGEMENT_ID"></font>
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('TYPE_ENGAGEMENT_ID'); ?>
                                            <?php endif ?>
                                      </div>
                                    </div>

                                    <div class="col-md-6">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_monnaie') ?><font color="red">*</font></label>
                                        <select onchange="money_devise();" name="TYPE_MONTANT_ID" id="TYPE_MONTANT_ID" class="form-control">
                                          <?php foreach($get_device as $dev)
                                          {
                                            if ($dev->DEVISE_TYPE_ID==$task['DEVISE_TYPE_ID'])
                                            { 
                                              echo "<option value='".$dev->DEVISE_TYPE_ID."' selected>".$dev->DESC_DEVISE_TYPE."</option>";
                                            }
                                            else
                                            {
                                              echo "<option value='".$dev->DEVISE_TYPE_ID."' >".$dev->DESC_DEVISE_TYPE."</option>"; 
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
                                      <input onpaste="return false;" onkeyup="fois();" oninput="formatInputValue(this);" onkeydown="moneyDevise();" value="<?=number_format($task['MONTANT_ENG_BUDGETAIRE_DEVISE'],0,',',' ')?>" type="text" class="form-control" name="MONTANT_EN_DEVISE" id="MONTANT_EN_DEVISE">
                                      <input type="hidden" name="engagement_devise" id="engagement_devise" value="<?=$task['MONTANT_ENG_BUDGETAIRE_DEVISE']?>">
                                      <font color="red" id="error_MONTANT_EN_DEVISE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('MONTANT_EN_DEVISE'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div>
                                     
                                    <div class="col-md-6" id="cou_chang" hidden="true">
                                      <label for=""> <?= lang('messages_lang.label_echange') ?> <font color="red">*</font> </label>
                                      <input value="<?=number_format($task['TAUX'],4,'.',' ')?>"  type="text" class="form-control" name="COUS_ECHANGE" id="COUS_ECHANGE" oninput="formatInputValue(this);" onkeyup="fois();" onkeydown="moneyDevise();">
                                      <input type="hidden" name="engagement_cous" id="engagement_cous" value="<?=$task['TAUX']?>">

                                      <font color="red" id="error_COUS_ECHANGE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('COUS_ECHANGE'); ?>
                                      <?php endif ?>
                                    </div>

                                    <div class="col-md-6" id="racc_dev"  hidden="true">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_engage') ?> <font color="red">*</font></label>
                                        <input type="text" class="form-control " name="ENG_BUDGETAIRE_devise" id="ENG_BUDGETAIRE_devise" value="<?=$task['MONTANT_ENG_BUDGETAIRE']?>" readonly>
                                        <font color="red" id="error_ENG_BUDGETAIRE124"></font>
                                        <br>
                                      </div>
                                    </div>
                                    <div class="col-md-6" id="date_dev"  hidden="true">
                                      <label for=""><?= lang('messages_lang.label_date_cours') ?><font color="red">*</font></label>
                                      <input type="date" value="<?= $retVal = (!empty($task['DATE_COUR_DEVISE'])) ? $task['DATE_COUR_DEVISE'] : '' ;  ?>"  max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_COUT_DEVISE" id="DATE_COUT_DEVISE">
                                      <font color="red" id="error_DATE_COUT_DEVISE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('DATE_COUT_DEVISE'); ?>
                                      <?php endif ?>
                                    </div>
                                    <div class="col-md-6" id="racc_bif">
                                      <div class="form-froup">
                                        <label class="form-label"><?= lang('messages_lang.label_engage') ?> <font color="red">*</font></label>
                                        <input type="hidden" id="mont_budget" value="<?=$task['MONTANT_ENG_BUDGETAIRE']?>">
                                        <input onpaste="return false;" value="<?=number_format($task['MONTANT_ENG_BUDGETAIRE'],4,',',' ')?>" type="text" class="form-control " name="ENG_BUDGETAIRE" id="ENG_BUDGETAIRE" placeholder="0" value="<?= date('Y-m-d') ?>" onpaste="return false;" min="0" oninput="formatInputValue(this);" onkeydown="moneyRestant()" onkeyup="moneyRestant();calculer()" >
                                        <font color="red" id="error_ENG_BUDGETAIRE"></font>
                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('ENG_BUDGETAIRE'); ?>
                                        <?php endif ?>
                                        <br>
                                        <input type="hidden" name="engagement_budget" id="engagement_budget" value="<?=$task['MONTANT_ENG_BUDGETAIRE']?>">
                                      </div>
                                    </div>                                                                          
                                        
                                    <div class="col-md-6">
                                    <label for=""><?= lang('messages_lang.label_obje') ?><font color="red">*</font></label>
                                    <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"><?= $retVal = (!empty($task['COMMENTAIRE'])) ? $task['COMMENTAIRE']:'';?></textarea>
                                    <font color="red" id="error_COMMENTAIRE"></font>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('COMMENTAIRE'); ?>
                                    <?php endif ?>
                                    <br>
                                  </div>

                                  <div class="col-md-6">
                                    <div class="form-froup">
                                      <label class="form-label"><?= lang('messages_lang.label_sous_act') ?> <font color="red">*</font></label>
                                      <select onchange="sous_acts();" class="form-control" id="sous_act" name="sous_act" >
                                        <option value=""><?= lang('messages_lang.label_select') ?></option>
                                        <?php  $s_act=array(0=>'Non',1=>'Oui');?>
                                        <?php 
                                        foreach($s_act as $key => $value)
                                        { 
                                          if ($key==$task['EST_SOUS_TACHE'])
                                          { 
                                            echo "<option value='".$key."' selected>".$value."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key."' >".$value."</option>"; 
                                          }
                                        }?>
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
                                      <input type="hidden" name="fini123" value="<?=$task['EST_FINI_TACHE']?>">
                                      <select onchange="termine();" class="form-control" id="fini" name="fini" >
                                        <?php  $act_fin=array(0=>'Non',1=>'Oui');?>

                                        <?php 
                                        foreach($s_act as $key => $value)
                                        { 
                                          if ($key==$task['EST_FINI_TACHE'])
                                          { 
                                            echo "<option value='".$key."' selected>".$value."</option>";
                                          }
                                          else
                                          {
                                            echo "<option value='".$key."' >".$value."</option>"; 
                                          }
                                        }?>
                                      </select>
                                      <font color="red" id="error_fini"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('fini'); ?>
                                      <?php endif ?>
                                      <br>
                                    </div>
                                  </div>

                                  <div class="col-md-6" id="id_qte" hidden="false">
                                    <div class="form-froup">
                                      <label class="form-label"><?= lang('messages_lang.label_quantite') ?> <font color="red">*</font></label>
                                      <input type="hidden" name="qte123" value="<?=$task['QTE']?>">
                                      <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="QTE" id="QTE" placeholder="0" value="<?=$task['QTE']?>" onkeydown="qte();" onkeyup="qte();" onpaste="return false;" min="1">
                                      <font color="red" id="error_QTE"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('QTE'); ?>
                                      <?php endif ?>
                                    </div>
                                  </div> 

                                  <div class="col-md-6" id="id_res" hidden="true">
                                    <div class="form-froup">
                                      <label class="form-label"><?= lang('messages_lang.label_result') ?><font color="red">*</font></label>
                                      <input type="hidden" name="attend" value="<?=$task['RESULTAT_ATTENDUS']?>">
                                      <input onpaste="return false;" type="text" maxlength="20" class="form-control allownumericwithdecimal" name="resultat_attend" id="resultat_attend" placeholder="" value="<?=$task['RESULTAT_ATTENDUS']?>" onpaste="return false;" min="1" onkeydown="qte();" onkeyup="qte();">
                                      <font color="red" id="error_resultat_attend"></font>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('resultat_attend'); ?>
                                      <?php endif ?>
                                    </div>
                                  </div> 

                                  <div class="col-md-6" id="obs_res" hidden="true">
                                    <label for=""><?= lang('messages_lang.label_obser_res') ?> <font color="red">*</font></label>
                                    <input type="hidden" name="obser11" id="obser11" value="<?=$task['OBSERVATION_RESULTAT']?>">
                                    <textarea maxlength="250" class="form-control" name="observ" id="observ"><?=$task['OBSERVATION_RESULTAT']?></textarea>
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
                              <a onclick="saveEtape1()" id="btn_save"  class="btn btn-primary" ><b id="loading_save"></b> <?= lang('messages_lang.bouton_modifier') ?></a>
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
  $(document).ready(function(){
    get_inst();
    get_TacheMoney();

    var type_mont = $('#get_taux_id').val() ;
    if(type_mont!=1)
    {
      //alert(type_mont);
      moneyDevise();
      money_devise();
      calculer();
      fois();
    }
    moneyRestant()

    var sous_act =$('#sous_act').val() ;

    if (sous_act==1)
    {
      $('#id_termine').attr('hidden',false);
      $('#id_qte').attr('hidden',false);
      $('#obs_res').attr('hidden',true);
      $('#id_res').attr('hidden',true);
    }
    else
    {
      $('#id_termine').attr('hidden',true);
      $('#id_qte').attr('hidden',false);
      $('#obs_res').attr('hidden',true);
      $('#id_res').attr('hidden',true);
    }

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
      var url = "<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_sousTutel/"+INSTITUTION_ID;

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
        url:"<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_inst/"+INSTITUTION_ID,
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
      var url = "<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_code/"+SOUS_TUTEL_ID;

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
          url: "<?=base_url('')?>/double_commande_new/Introduction_Budget_Multi_Taches/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
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
              url:"<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_activite/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
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
          url:"<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_activite/"+CODE_NOMENCLATURE_BUDGETAIRE_ID,
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
        url: "<?=base_url('')?>/double_commande_new/Introduction_Budget_Multi_Taches/get_taches/" + id+"/"+TYPE_INSTITUTION_ID,
        type: "GET",
        dataType: "JSON",
        data: {
          PAP_ACTIVITE_ID: PAP_ACTIVITE_ID,
          CODE_NOMENCLATURE_BUDGETAIRE_ID: CODE_NOMENCLATURE_BUDGETAIRE_ID,
          TYPE_INSTITUTION_ID: TYPE_INSTITUTION_ID,
        },
        beforeSend: function() {
          $('#loading_task').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#PTBA_TACHE_ID').html(data.tache_activite);
          $('#loading_task').html("");
        }
      });
    }
  }

  function get_TacheMoney()
  {
    var PTBA_TACHE_ID=$('#PTBA_TACHE_ID').val();
    var mont_budget = $('#mont_budget').val();
    var task_id = $('#task_id').val();

    //alert(mont_budget);

    if(PTBA_TACHE_ID=='')
    {
      $('#montant_vote').val();
      $('#vote').val();
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_TacheMoney/"+PTBA_TACHE_ID,
        type:"POST",
        dataType:"JSON",
        data:{
          mont_budget:mont_budget,
          task_id:task_id
        },
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
          
        }
      });
    }
  }
</script>
<script type="text/javascript">
  function calculer()
  {
    var ENG_BUDGETAIRE = $('#ENG_BUDGETAIRE').val();
    var ENG_BUDGETAIRE = ENG_BUDGETAIRE.replace(/ /g, '');
    $('#engagement_budget').val(ENG_BUDGETAIRE);
    var resteEng = $('#montant_restant').val();

    if (ENG_BUDGETAIRE=='')
    {
      var ENG_BUDGETAIRE = 0;
    }
    if (resteEng=='')
    {
      var resteEng = 0;
    }
    var calcul = parseFloat(resteEng) - parseFloat(ENG_BUDGETAIRE);     
    // Remove all non-digit characters from the input value
    var calcul = calcul.toString();
    var calcul = calcul.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
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
    $('#ENG_BUDGETAIRE').val(calcul);

    var reste = parseFloat(resteEng) - parseFloat(calcul);
    var reste = reste.toString();
    var reste = reste.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    $('#restant').val(reste);

    var calcul = calcul.toLocaleString('en-US', { useGrouping: true });
    var calcul = calcul.replace(/,/g, ' ');
    $('#ENG_BUDGETAIRE_devise').val(calcul);
  }
</script>
<script>
  function get_docs()
  {
    var TYPE_ENGAGEMENT_ID=$('#TYPE_ENGAGEMENT_ID').val();
    $.ajax(
    {
      url:"<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/get_docs/"+TYPE_ENGAGEMENT_ID,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
        $('#BUDGETAIRE_TYPE_DOCUMENT_ID').html(data.docs);
        // change_lettre()
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
    var ENG_BUDGETAIRE = $('#engagement_budget').val();
    var resteEng= $('#montant_restant').val(); 
    $('#error_ENG_BUDGETAIRE').html('');

    var MONTANT_EN_DEVISE = $('#engagement_devise').val();
    $('#error_MONTANT_EN_DEVISE').html('');

    var COUS_ECHANGE = $('#engagement_cous').val();            
    $('#error_COUS_ECHANGE').html('');

    var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
    $('#error_DATE_COUT_DEVISE').html('');

    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
    $('#error_TYPE_MONTANT_ID').html('');

    var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();
    $('#error_TYPE_ENGAGEMENT_ID').html('');

    var QTE  = $('#QTE').val();
    $('#error_QTE').html('');

    var NOTE_REFERENCE = $('#NOTE_REFERENCE').val();
    $('#error_NOTE_REFERENCE').html('');

    var sous_act  = $('#sous_act').val();
    var fini  = $('#fini').val();
    var observ = $('#observ').val();
    var resultat_attend  = $('#resultat_attend').val();

    if(NOTE_REFERENCE =='') 
    {
      $('#error_NOTE_REFERENCE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (QTE =='') 
    {
      $('#error_QTE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (QTE == 0) 
    {
      $('#error_QTE').html("<?=lang('messages_lang.qte_neg')?>");
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
      }
    }

    if (COMMENTAIRE =='') 
    {
      $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
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

    if (ENG_BUDGETAIRE=='')
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (parseFloat(ENG_BUDGETAIRE) == 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.money_neg')?>");
      statut=1;
    }

    if (parseFloat(ENG_BUDGETAIRE) > parseFloat(resteEng))
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.credi_mont')?>");  
      statut=1;   
    }

    if (ENG_BUDGETAIRE < 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.mont_neg')?>");
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
    

    var url;

    

    if(statut == 2)
    {
      var ENG_BUDGETAIRE = parseFloat(ENG_BUDGETAIRE);
      var ENG_BUDGETAIRE = ENG_BUDGETAIRE.toLocaleString('en-US', { useGrouping: true });
      var ENG_BUDGETAIRE = ENG_BUDGETAIRE.replace(/,/g, ' ');
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

      $('#ENG_BUDGETAIRE_verifie').html(ENG_BUDGETAIRE);
      $('#TYPE_MONTANT_ID_verifie').html($('#TYPE_MONTANT_ID option:selected').text());
      var MONTANT_EN_DEVISE = parseFloat(MONTANT_EN_DEVISE);
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.toLocaleString('en-US', { useGrouping: true });
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/,/g, ' ');

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

      $('#note_ref_verifie').html(NOTE_REFERENCE);

      
      $('#fini_verifie').html($('#fini option:selected').text()); 
      $('#sous_act_verifie').html($('#sous_act option:selected').text());
      $('#RESULTAT_ATTENDUS_verifie').html(resultat_attend);
      $('#QTE_verifie').html(QTE);
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
      $("#engaged").modal("show");
    }
  }

  function saveEtape13()
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
    var ENG_BUDGETAIRE = $('#engagement_budget').val();
    var resteEng= $('#montant_restant').val(); 
    $('#error_ENG_BUDGETAIRE').html('');

    var MONTANT_EN_DEVISE = $('#engagement_devise').val();
    $('#error_MONTANT_EN_DEVISE').html('');

    var COUS_ECHANGE = $('#engagement_cous').val();          
    
    $('#error_COUS_ECHANGE').html('');

    var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
    $('#error_DATE_COUT_DEVISE').html('');

    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
    $('#error_TYPE_MONTANT_ID').html('');

    var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();
    $('#error_TYPE_ENGAGEMENT_ID').html('');
    

    var QTE  = $('#QTE').val();
    $('#error_QTE').html('');

    if (QTE =='') 
    {
      $('#error_QTE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (QTE == 0) 
    {
      $('#error_QTE').html("<?=lang('messages_lang.qte_neg')?>");
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
      }
    }

    if (COMMENTAIRE =='') 
    {
      $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (MARCHE_PUBLIQUE==1)
    {
      if (ID_TYPE_MARCHE=='')
      {
         $('#error_ID_TYPE_MARCHE').html("<?=lang('messages_lang.input_oblige')?>");
         statut=1;
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
        else
        {
          $('#error_PATH_LETTRE_TRANSMISSION').html("");
          statut = 2;
        }
      }

      if (PATH_LISTE_PAIE.files.length === 0)
      {
        $('#error_PATH_LISTE_PAIE').html("<?=lang('messages_lang.input_oblige')?>");
        statut = 1;
      }
      else if (PATH_LISTE_PAIE.files[0].size > maxSize)
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

    if (ENG_BUDGETAIRE=='')
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (parseFloat(ENG_BUDGETAIRE) == 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.money_neg')?>");
      statut=1;
    }

    if (parseFloat(ENG_BUDGETAIRE) > parseFloat(resteEng))
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.credi_mont')?>");  
      statut=1;   
    }

    if (ENG_BUDGETAIRE < 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.mont_neg')?>");
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

    var path = PATH_LISTE_PAIE;
    var paie = path.split("\\");
    var paie_liste= paie[paie.length-1];

    if(statut == 2)
    {
      var ENG_BUDGETAIRE = parseFloat(ENG_BUDGETAIRE);
      var ENG_BUDGETAIRE = ENG_BUDGETAIRE.toLocaleString('en-US', { useGrouping: true });
      var ENG_BUDGETAIRE = ENG_BUDGETAIRE.replace(/,/g, ' ');
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

      $('#ENG_BUDGETAIRE_verifie').html(ENG_BUDGETAIRE);
      $('#TYPE_MONTANT_ID_verifie').html($('#TYPE_MONTANT_ID option:selected').text());
      var MONTANT_EN_DEVISE = parseFloat(MONTANT_EN_DEVISE);
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.toLocaleString('en-US', { useGrouping: true });
      var MONTANT_EN_DEVISE = MONTANT_EN_DEVISE.replace(/,/g, ' ');

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
  


      $('#fini_verifie').html($('#fini option:selected').text()); 
      $('#sous_act_verifie').html($('#sous_act option:selected').text());
      $('#RESULTAT_ATTENDUS_verifie').html(resultat_attend);
      $('#QTE_verifie').html(QTE);
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
      $('#PATH_LETTRE_TRANSMISSION_verifie').html(trans_lettre);
      $('#PATH_LISTE_PAIE_verifie').html(paie_liste);
      $("#engaged").modal("show");
    }
  }
</script>
<script type="text/javascript">
  function qte()
  {
    var QTE = $('#QTE').val();  
    $('#error_QTE').html(''); 

    if (parseFloat(QTE) == 0)
    {
      $('#error_QTE').html("<?=lang('messages_lang.qte_neg')?>"); 
    }
    if(/^0\d/.test(QTE))
    {
      $('#QTE').val(QTE.replace(/^0\d/, ""));
    }           
  }
</script>
<script type="text/javascript">
  function formatInputValue(input) 
  {
    // Remove all non-digit characters from the input value
    var numericValue = "";

    if(input.id=="ENG_BUDGETAIRE")
    {
      numericValue = input.value.replace(/[^0-9]/g, '');
    }else
    {
      numericValue = input.value.replace(/[^0-9.]/g, '');
    }

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
    var COUS_ECHANGE = $('#engagement_cous').val();

    var ENG_BUDGETAIRE = $('#engagement_budget').val();
    var engagement_devise = $('#engagement_devise').val();    
    var resteEng = $('#montant_restant').val();
    $('#error_MONTANT_EN_DEVISE').html('');
    $('#error_COUS_ECHANGE').html(''); 
    $('#error_ENG_BUDGETAIRE124').html(''); 


    if (parseFloat(engagement_devise) == 0)
    {
      $('#error_MONTANT_EN_DEVISE').html("<?=lang('messages_lang.mont_neg')?>"); 
    }

    if (parseFloat(ENG_BUDGETAIRE) > parseFloat(resteEng))
    {
      $('#MONTANT_EN_DEVISE').on('keypress',DoPrevent);  
      $('#engagement_cous').on('keypress',DoPrevent); 
      $('#error_ENG_BUDGETAIRE124').html("<?=lang('messages_lang.credi_mont')?>"); 
    }else{
      $('#MONTANT_EN_DEVISE').off('keypress',DoPrevent);
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
    var ENG_BUDGETAIRE = $('#engagement_budget').val();    
    var resteEng = $('#montant_restant').val();
    $('#error_ENG_BUDGETAIRE').html(''); 

    if (parseFloat(ENG_BUDGETAIRE) == 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.money_neg')?>"); 
    }

    if(/^0\d/.test(ENG_BUDGETAIRE))
    {
      $('#ENG_BUDGETAIRE').val(ENG_BUDGETAIRE.replace(/^0\d/, ""));
    }

    if (parseFloat(ENG_BUDGETAIRE) > parseFloat(resteEng))
    {
      $('#ENG_BUDGETAIRE').on('keypress',DoPrevent);
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.credi_mont')?>");     
    }else{
      $('#ENG_BUDGETAIRE').off('keypress',DoPrevent);
    }

    if (parseFloat(ENG_BUDGETAIRE) < 0)
    {
      $('#error_ENG_BUDGETAIRE').html("<?=lang('messages_lang.mont_neg')?>"); 
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
                <span id="ENG_BUDGETAIRE_verifie" class="text-dark"></span>
              </td>
            </tr>

            <tr>
              <td><i class="fa fa-file-import"></i>&nbsp;<strong><?= lang('messages_lang.label_nature') ?></strong></td>
              <td id="TYPE_ENGAGEMENT_ID_verifie" class="text-dark"></td>
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
              <td id="QTE_verifie" class="text-dark"></td>
            </tr>
              <tr id="atta">
              <td><i class="fa fa-history"></i>&nbsp;<strong><?= lang('messages_lang.label_result') ?></strong></td>
              <td id="RESULTAT_ATTENDUS_verifie" class="text-dark"></td>
            </tr>
            <tr id="observa12">
              <td><i class="fa fa-list"></i>&nbsp;<strong><?= lang('messages_lang.label_obser_res') ?></strong></td>
              <td id="observ_verifie" class="text-dark"></td>
            </tr>

            <tr>
              <td><i class="fa fa-list"></i>&nbsp;<strong><?= lang('messages_lang.label_Note') ?></strong></td>
              <td id="note_ref_verifie" class="text-dark"></td>
            </tr>
             
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
    <a onclick="save_tempo();hideButton()" id="myElement" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
  </div>
</div>
</div>
</div>
<script type="text/javascript">
  function save_tasks()
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

<script type="text/javascript">
  function save_tempo()
  {
    var id_exec_titr_dec = $('#id_exec_titr_dec').val();
    var EXECUTION_BUDGETAIRE_ID = $('#EXECUTION_BUDGETAIRE_ID').val();
    var EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID = $('#EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID').val();
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE_ID = $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    var PAP_ACTIVITE_ID = $('#PAP_ACTIVITE_ID').val();
    var PTBA_TACHE_ID = $('#PTBA_TACHE_ID').val();
    var ENG_BUDGETAIRE = $('#engagement_budget').val();
    var MONTANT_EN_DEVISE = $('#engagement_devise').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val();
    var COUS_ECHANGE = $('#engagement_cous').val();          
    var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
    var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();
    var sous_act = $('#sous_act').val();
    var fini = $('#fini').val();
    var QTE = $('#QTE').val();
    var resultat_attend = $('#resultat_attend').val();
    var observ = $('#observ').val();
    var COMMENTAIRE= $('#COMMENTAIRE').val();
    var NOTE_REFERENCE = $('#NOTE_REFERENCE').val();
    var UNITE = $('#UNITE').val();

    let url="<?=base_url('double_commande_new/Introduction_Budget_Multi_Taches/save_correct_task')?>";
    $.post(url,
    {
      EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID,
      EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID:EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,
      INSTITUTION_ID:INSTITUTION_ID,
      SOUS_TUTEL_ID:SOUS_TUTEL_ID,
      PROGRAMME_ID:PROGRAMME_ID,
      ACTION_ID:ACTION_ID,
      CODE_NOMENCLATURE_BUDGETAIRE_ID:CODE_NOMENCLATURE_BUDGETAIRE_ID,
      PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
      PTBA_TACHE_ID:PTBA_TACHE_ID,
      ENG_BUDGETAIRE:ENG_BUDGETAIRE,
      MONTANT_EN_DEVISE:MONTANT_EN_DEVISE,
      TYPE_MONTANT_ID:TYPE_MONTANT_ID,
      COUS_ECHANGE:COUS_ECHANGE,
      DATE_COUT_DEVISE:DATE_COUT_DEVISE,
      TYPE_ENGAGEMENT_ID:TYPE_ENGAGEMENT_ID,
      sous_act:sous_act,
      fini:fini,
      QTE:QTE,
      resultat_attend:resultat_attend,
      observ:observ,
      COMMENTAIRE:COMMENTAIRE,
      NOTE_REFERENCE:NOTE_REFERENCE,
      UNITE:UNITE
    },
    function (data) {
      
      if(data.status == true) 
      {   
        $("#engaged").modal("hide") 
        window.location.href = "<?=base_url('double_commande_new/Introduction_Budget_Multi_Taches/corrige_etape1/')?>"+'/'+id_exec_titr_dec;   
      }
      else{

        if(data.valeur == 1)
        {
          $('#error_INSTITUTION_ID').html(data.msg.INSTITUTION_ID);
          $('#error_SOUS_TUTEL_ID').html(data.msg.SOUS_TUTEL_ID);
          $('#error_NOTE_REFERENCE').html(data.msg.NOTE_REFERENCE);
          $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html(data.msg.CODE_NOMENCLATURE_BUDGETAIRE_ID);
          $('#error_PAP_ACTIVITE_ID').html(data.msg.PAP_ACTIVITE_ID);
          $('#error_PTBA_TACHE_ID').html(data.msg.PTBA_TACHE_ID);

          $('#error_ENG_BUDGETAIRE').html(data.msg.ENG_BUDGETAIRE);
          $('#error_MONTANT_EN_DEVISE').html(data.msg.MONTANT_EN_DEVISE);
          $('#error_TYPE_MONTANT_ID').html(data.msg.TYPE_MONTANT_ID);
          $('#error_COUS_ECHANGE').html(data.msg.COUS_ECHANGE);
          $('#error_DATE_COUT_DEVISE').html(data.msg.DATE_COUT_DEVISE);
          $('#error_TYPE_ENGAGEMENT_ID').html(data.msg.TYPE_ENGAGEMENT_ID);

          $('#error_sous_act').html(data.msg.sous_act);
          $('#error_fini').html(data.msg.fini);
          $('#error_QTE').html(data.msg.QTE);
          $('#error_resultat_attend').html(data.msg.resultat_attend);

          $('#error_resultat_observ').html(data.msg.observ);
          $('#error_COMMENTAIRE').html(data.msg.COMMENTAIRE);
          $("#engaged").modal("hide");
        }
        else
        {
          $("#engaged").modal("hide");
          $('#message').html(data.msg_error).fadeIn('slow').delay(3000).fadeOut('slow');
        }
      }
    })
  }
</script>
