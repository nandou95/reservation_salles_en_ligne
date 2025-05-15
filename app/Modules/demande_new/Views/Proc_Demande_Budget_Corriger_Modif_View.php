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
                    <a href="<?php echo base_url('demande_new/Proc_Demande_Budget_Corriger/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> Liste</a>
                  </div>
                  <div class="car-body">
                    <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <center class="ml-5" style="height=100px;width:90%" >
                        <div class="alert alert-danger"  id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                      </center>
                      <?php
                    } ?>
                    <h4 style="margin-left:4%;margin-top:10px"> <?=lang('messages_lang.titre_proc')?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('demande_new/Proc_Demande_Budget_Corriger/modifier_activite/')?>" method="post" >
                        <div class="container">
                          <div class="row">
                            <?php
                              $min=lang('messages_lang.minister');
                              if ($resultatinst['TYPE_INSTITUTION_ID']==1) {
                                $min=lang('messages_lang.labelle_institution');
                              }
                            ?>
                            <div class="col-md-3"><i class=" fa fa-home"></i>&nbsp;&nbsp;<?=$min; ?></div>
                            <div class="col-md-9"><?= $resultatinst['CODE_INSTITUTION'].'&nbsp;&nbsp;'.$resultatinst['DESCRIPTION_INSTITUTION']?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-building"></i>&nbsp;&nbsp;<?=lang('messages_lang.table_st')?></div>
                            <div class="col-md-9"><?= $sous_tutel['CODE_SOUS_TUTEL'].'&nbsp;&nbsp;'.$sous_tutel['DESCRIPTION_SOUS_TUTEL']?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-cubes"></i>&nbsp;&nbsp;<?=lang('messages_lang.label_ligne')?></div>
                            <div class="col-md-9"><?= $info['IMPUTATION']?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-file-text"></i>&nbsp;&nbsp;<?=lang('messages_lang.labelle_libelle')?></div>
                            <div class="col-md-9"><?= $info['LIBELLE'] ?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-cogs"></i>&nbsp;&nbsp;<?=lang('messages_lang.credit_accorde')?></div>
                            <div class="col-md-9"><?= number_format($montant_total['T1'],'0',',',' ')?> BIF</div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-credit-card"></i>&nbsp;&nbsp;<?=lang('messages_lang.credit_restant')?></div>
                            <div class="col-md-9"><?= number_format($montant_total['total'],'0',',',' ')?> BIF</div>
                            <input type="hidden" name="total_vote_ligne" id="total_vote_ligne" value="<?=$montant_total['total'] ?>">
                            <input type="hidden" name="total_ligne" id="total_ligne" value="<?=$total_ligne ?>">
                          </div><hr>
                          
                          <input type="hidden" class="form-control" value="<?=$info['LIQUIDATION']?>" name="LIQUIDATION" id="LIQUIDATION" readonly>
                          <input type="hidden"id="ENG_BUDGETAIRE_ID" value="<?=$info['ENG_BUDGETAIRE']?>">
                          <input type="hidden"id="PAIEMENT_ID" value="<?=$info['PAIEMENT']?>">
                          <input type="hidden"id="ENG_JURIDIQUE_ID"value="<?=$info['ENG_JURIDIQUE']?>">
                          <input type="hidden"id="DECAISSEMENT_ID" value="<?=$info['PAIEMENT']?>">
                          <input type="hidden"id="ORDONNANCEMENT_ID" value="<?=$info['ORDONNANCEMENT']?>">
                          
                          
                          <div class="card shadow">
                            <div class="table-responsive  mt-3">
                              <table class="table table-bordered">
                                <thead class="bg-dark text-white">
                                  <th><?=lang('messages_lang.engage_budget')?></th>
                                  <th><?=lang('messages_lang.engage_jurid')?></th>
                                  <th><?=lang('messages_lang.labelle_liquidation')?></th>
                                  <th><?=lang('messages_lang.labelle_ordonan')?></th>
                                  <th><?=lang('messages_lang.labelle_paiement')?></th>
                                  <th><?=lang('messages_lang.labelle_decaisse')?></th>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td><?=number_format($info['ENG_BUDGETAIRE'],'0',',',' ')?></td>
                                    <td><?=number_format($info['ENG_JURIDIQUE'],'0',',',' ')?></td>
                                    <td><?=number_format($info['LIQUIDATION'],'0',',',' ')?></td>
                                    <td><?=number_format($info['ORDONNANCEMENT'],'0',',',' ')?></td>
                                    <td><?=number_format($info['PAIEMENT'],'0',',',' ')?></td>
                                    <td><?=number_format($info['DECAISSEMENT'],'0',',',' ')?></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>

                          <input type="hidden" class="form-control" value="<?=$id_demande?>" name="demande">
                          <input type="hidden" class="form-control" value="<?=$demande['GRANDE_MASSE_BM']?>" name="GRANDE_MASSE_ID">
                          
                          
                          <input type="hidden" name="mont_realise"  value="<?=!empty($mouvement_montant['DESC_MOUVEMENT_DEPENSE']) ? $mouvement_montant['DESC_MOUVEMENT_DEPENSE'] : 'N/A' ?>" id="mont_realise"  class="form-control">
                          <input type="hidden" name="mont_realise"  value="<?=!empty($mouvement_montant['mont_realise']) ? $mouvement_montant['mont_realise'] : 0 ?>" id="mont_realise_sommation"  class="form-control">
                          <input type="hidden" name="mont_realise_jurid"  value="<?=!empty($mouvement_montant['jurd']) ? $mouvement_montant['jurd'] : 0 ?>" id="mont_realise_sommation_jurd"  class="form-control">
                          <input type="hidden" name="mont_realise_liq"  value="<?=!empty($mouvement_montant['liq']) ? $mouvement_montant['liq'] : 0 ?>" id="mont_realise_sommation_liq"  class="form-control">
                          <input type="hidden" name="mont_realise_ord"  value="<?=!empty($mouvement_montant['ord']) ? $mouvement_montant['ord'] : 0 ?>" id="mont_realise_sommation_ord"  class="form-control">
                          <input type="hidden" name="mont_realise_paie"  value="<?=!empty($mouvement_montant['paie']) ? $mouvement_montant['paie'] : 0 ?>" id="mont_realise_sommation_paie"  class="form-control">
                          <input type="hidden" name="mont_realise_decais"  value="<?=!empty($mouvement_montant['decais']) ? $mouvement_montant['decais'] : 0 ?>" id="mont_realise_sommation_decais"  class="form-control">
                         
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-7 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label"> <?=lang('messages_lang.labelle_activite')?>  <font color="red">*</font></label>
                                    <select class="select2 form-control" id="PTBA_ID" value="<?=set_value('PTBA_ID') ?>" name="PTBA_ID" onchange="get_mont()">
                                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                       <?php 
                                        foreach($activite as $activi) { 
                                          if ($activi->PTBA_ID==$info_modif['ID_PTBA']) { 
                                              echo "<option value='".$activi->PTBA_ID."' selected>".$activi->ACTIVITES."</option>";
                                          } else{
                                              echo "<option value='".$activi->PTBA_ID."' >".$activi->ACTIVITES."</option>"; 
                                          } }?>
                                    </select>
                                    <font color="red" id="error_PTBA_ID"></font>
                                    <?= $validation->getError('PTBA_ID'); ?>
                                  </div>
                                </div>

                                <input type="hidden" name="new_montant_ligne" id="new_montant_ligne" value="<?=$tot_ligne_transfert; ?>">
                                <div class="col-md-6">
                                  <div class='form-froup'>
                                    <label class="form-label"> <?=lang('messages_lang.montant_autre_trim')?>  <font color="red">*</font></label>
                                    <select class="form-control" id="IS_TRANSFERT_ACTIVITE" onchange="is_transfert()" name="IS_TRANSFERT_ACTIVITE" >
                                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                       <?php  $transfert=array(0=>'Non',1=>'Oui');?>

                                        <?php 
                                        foreach($transfert as $key => $value) { 
                                          if ($key==$info_modif['IS_TRANSFERT_ACTIVITE']) { 
                                              echo "<option value='".$key."' selected>".$value."</option>";
                                          } else{
                                              echo "<option value='".$key."' >".$value."</option>"; 
                                          } }?>
                                    </select>
                                    <font color="red" id="error_is_transfert"></font>
                                    <?= $validation->getError('IS_TRANSFERT_ACTIVITE'); ?>
                                  </div>
                                </div>
                                
                                <div class="col-md-6" id="div_tranche">
                                  <div class="form-group">
                                    <label class="form-label"><?=lang('messages_lang.labelle_tranche')?> <font color="red">*</font></label>
                                    <select name="TRANCHE_ID" id="TRANCHE_ID" onchange="get_mont_transfert()" class=" select2 form-control">
                                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                      <?php 
                                        foreach($tranches as $key_tranche) { 
                                          if ($key_tranche->TRANCHE_ID==$info_modif['TRIMESTRE_ID']) { 
                                              echo "<option value='".$key_tranche->TRANCHE_ID."' selected>".$key_tranche->DESCRIPTION_TRANCHE."</option>";
                                          } else{
                                              echo "<option value='".$key_tranche->TRANCHE_ID."' >".$key_tranche->DESCRIPTION_TRANCHE."</option>"; 
                                          } }?>
                                    </select>
                                    <font color="red" id="error_tranche"></font>
                                    <?= $validation->getError('TRANCHE_ID'); ?>
                                  </div>
                                </div>
                               
                                <div class="col-md-6" id="num_montant_transfert">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.mont_transf')?> <font color="red">*</font></label>
                                    <input type="number" onkeyup="valid_transfert()" min="0" class="form-control" name="MONTANT_TRANSFERT" id="MONTANT_TRANSFERT" value="<?=$info_modif['MONTANT_TRANSFERT']?>">
                                    <font color="red" id="error_montant_transfert"></font>
                                    <font color="red" id="error_mont_transfert"></font>
                                    <?= $validation->getError('MONTANT_TRANSFERT'); ?>
                                  </div>
                                  <input type="hidden" name="montant_tranche" id="montant_tranche">
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label"><?=lang('messages_lang.etat_exec')?> <font color="red">*</font></label>
                                    <select name="Mouvement_code" id="Mouvement_id" value="<?=set_value('PTBA_ID') ?>" class=" select2 form-control" onchange="change_label();active_mouvement()">
                                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                       <?php 
                                        foreach($mvt_depense as $moumvent) { 
                                          if ($moumvent->MOUVEMENT_DEPENSE_ID==$info_modif['MOUVEMENT_DEPENSE_ID']) { 
                                              echo "<option value='".$moumvent->MOUVEMENT_DEPENSE_ID."' selected>".$moumvent->DESC_MOUVEMENT_DEPENSE."</option>";
                                          } else{
                                              echo "<option value='".$moumvent->MOUVEMENT_DEPENSE_ID."' >".$moumvent->DESC_MOUVEMENT_DEPENSE."</option>"; 
                                          } }?>
                                    </select>
                                    <font color="red" id="error_Mouvement"></font>
                                    <?= $validation->getError('Mouvement_code'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="num_bon_eng">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.num_bon_engagement')?> <font color="red">*</font></label>
                                    <input type="text" class="form-control" name="numero_bon" id="numero_bon_id" value="<?=$info_modif['NUMERO_BON_ENGAGEMENT'] ?>" minlength="5" maxlength="20"  onclick="get_mont_transfert()">
                                    <font color="red" id="error_num_bon"></font>
                                    <?= $validation->getError('numero_bon'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="date_bon_eng">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.date_bon_engagement')?> <font color="red">*</font></label>
                                    <input type="date" max="<?= date('Y-m-d')?>" name="date_bon" id="date_bon_id" class="form-control" value="<?=$info_modif['DATE_BON_ENGAGEMENT']?>">
                                    <font color="red" id="error_date_bon"></font>
                                    <?= $validation->getError('date_bon'); ?>
                                  </div>
                                </div>

                                <div class="col-md-6" id="titre_decaiss">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.label_numero_titre_decaissement')?> <font color="red">*</font></label>
                                    <input type="text" class="form-control" name="numero_decaiss" id="numero_decaiss_id" value="<?=$info_modif['NUMERO_TITRE_DECAISSEMNT']?>" minlength="5" maxlength="20" onclick="get_mont_transfert()">
                                    <font color="red" id="error_num_decaissement"></font>
                                    <?= $validation->getError('numero_decaiss'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="date_titre">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.date_titre_decaissement')?> <font color="red">*</font></label>
                                    <input type="date" max="<?= date('Y-m-d')?>" name="date_decais" id="date_decais_id" class="form-control" value="<?=$info_modif['DATE_TITRE_DECAISSEMENT']?>">
                                    <font color="red" id="error_date_decaissement"></font>
                                    <?= $validation->getError('date_decais'); ?>
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.engagement_budgetaire')?><font color="red">*</font> <span id="loading_activite"></span></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_id" onKeyup="valide_montant()" name="montant_realise" value="<?=$info_modif['MONTANT_REALISE']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont"></font>
                                    <?= $validation->getError('montant_realise'); ?>
                                  </div>
                                </div>
                                <!-- new -->
                                <div class="col-md-6" id="mont_jurid">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.engagement_juridique')?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_jurid_id" onKeyup="valide_montant()" name="montant_realise_jurid" value="<?=$info_modif['MONTANT_REALISE_JURIDIQUE']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_jurd"></font>
                                    <?= $validation->getError('montant_realise_jurid'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_liq">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.labelle_liquidation')?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_liq_id" onKeyup="valide_montant()" name="montant_realise_liq" value="<?=$info_modif['MONTANT_REALISE_LIQUIDATION']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_liq"></font>
                                    <?= $validation->getError('montant_realise_liq'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_ordon">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_ordonan')?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_ord_id" onKeyup="valide_montant()" name="montant_realise_ord" value="<?=$info_modif['MONTANT_REALISE_ORDONNANCEMENT']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_ord"></font>
                                    <?= $validation->getError('montant_realise_ord'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_paiemt">
                                  <div class="form-group">
                                    <label for=""> <?=lang('labelle_paiement')?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_paie_id" onKeyup="valide_montant()" name="montant_realise_paie" value="<?=$info_modif['MONTANT_REALISE_PAIEMENT']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_paie"></font>
                                    <?= $validation->getError('montant_realise_paie'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_decaiss">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_decaisse')?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_decais_id" onKeyup="valide_montant()" name="montant_realise_decais" value="<?=$info_modif['MONTANT_REALISE_DECAISSEMENT']?>">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_decais"></font>
                                    <?= $validation->getError('montant_realise_decais'); ?>
                                  </div>
                                </div>
                                <!-- fin new -->
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for="" id="identifiant"> </label>
                                    <input type="file" class="form-control" id="doc_raccroche"  onchange="Valid_preuve(1)"  name="doc_raccroche" accept=".pdf">
                                    <input type="hidden" class="form-control" value="<?=$info_modif['PREUVE']?>" id="doc_raccroche23" name="doc_raccroche">
                                    <font color="red" id="doc_error"></font>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.tel_preuve')?></label>
                                    <input type="file" class="form-control"  onchange="Valid_preuve(2)" id="PREUVE" name="PREUVE" accept=".pdf">
                                    <input type="hidden" class="form-control" value="<?=$info_modif['PREUVE']?>" id="PREUVE123" name="PREUVE123">
                                    <font color="red" id="preuve_error"></font>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label"> <?=lang('messages_lang.necessite_marche_public')?>  <font color="red">*</font></label>
                                    <select class="form-control" id="MARCHE_PUBLIC" name="MARCHE_PUBLIC" >
                                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                     <?php  $marche=array(0=>'Non',1=>'Oui');?>

                                        <?php 
                                        foreach($marche as $key => $value) { 
                                          if ($key==$info_modif['MARCHE_PUBLIQUE']) { 
                                              echo "<option value='".$key."' selected>".$value."</option>";
                                          } else{
                                              echo "<option value='".$key."' >".$value."</option>"; 
                                          } }?>
                                    </select>
                                    <font color="red" id="error_marche"></font>
                                    <?= $validation->getError('MARCHE_PUBLIC'); ?>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group" hidden>
                                  <label class="form-label"> <?=lang('messages_lang.labelle_institution')?> <font color="red">*</font></label>
                                  <input type="" class="form-control"  id="Institutions_code_id" name="Institutions_code" readonly>
                                  <input type="hidden" class="form-control" name="Institutions" id="Institutions_id" value="<?=$info_modif['INSTITUTION_ID']?>" readonly>
                                  <font color="red" id="error_Institutions_id"></font>
                                </div>
                              </div>
                              <div class="col-md-12">
                                <label for=""> <?=lang('messages_lang.labelle_observartion') ?></label>
                                <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"><?php if (!empty($info_modif['COMMENTAIRE'])){?><?=$info_modif['COMMENTAIRE']?><?php }else{ ?> <?php } ?></textarea>
                              </div>
                            </div>
                            <input type="hidden" name="EXECUTION_ID_TEMPO" id="EXECUTION_ID_TEMPO" value="<?=$tempo_id?>">
                            <hr class="vertical">

                            <div class="col-md-4 mt-2" style="margin-bottom:50px;margin-left:-42px">
                              <div class="row">
                                <div class="col-md-12">
                                  <label class="form-label"> <?=lang('messages_lang.table_Programme')?></label>
                                  <input type="hidden" class="form-control" id="programes_code_id"name="programes_code"  value="<?=$info_modif['CODE_PROGRAMME']?>">
                                  <input type="text" class="form-control" id="programes_desc_id"name="programes_desc" value="<?=$get_prog['INTITULE_PROGRAMME']?>" readonly>
                                  <font color="red" id="error_programes_id"></font>
                                  
                                </div>
                                
                                <div class="col-md-12">
                                  <label class="form-label"> <?=lang('messages_lang.table_Action')?> </label>
                                  <input type="hidden" class="form-control" name="actions" id="actions_id" value="<?=$info_modif['CODE_ACTION']?>">
                                  <input type="text" class="form-control" name="action_descr" id="actions_desc_id" readonly value="<?=$get_action['LIBELLE_ACTION']?>">
                                  <font color="red" id="error_Actions_id"></font>
                                  
                                </div>
                                <div class="col-md-12">
                                  
                                  <label for=""> <?=lang('messages_lang.labelle_montant_vote')?></label>
                                  <input type="text" class="form-control" value="<?=$format_vote?>" id="mont" name="mont" readonly>
                                  <input type="hidden" class="form-control"  id="montant_vote_id" value="<?=$format_reste?>" name="montant_vote" readonly>
                                  <font color="red" id="montant_error"></font>
                                </div>                             <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.montant_rest_activite')?></label>
                                  <input type="text" id="montant_restant_actite1" class="form-control" value="<?=$format_reste?>" readonly>
                                </div>
                                <div class="col-md-12" id="div_trnsf">
                                  <label for=""> <?=lang('messages_lang.labelle_montant_apres_transfert')?></label>
                                  <input type="number" class="form-control"  id="new_montant_vote_id" name="new_montant_vote" value="<?=$MONTANT_APRES_TRANSFERT?>" readonly>
                                  <font color="red" id="new_montant_error"></font>
                                </div>
                                <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.mont_restant_ligne')?></label>
                                  <!-- <input type="number" class="form-control" id="montant_restant" name="montant_restant" value="<?=$ligne_reste;?>"> -->
                                  <input type="text" class="form-control" value="<?=$ligne_reste?>" id="montant_restant" name="montant_restant" readonly>
                                  <font color="red" id="error_montant_restant"></font>
                                </div>
                                <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.mont_rest_engag_budgetaire')?> </label>
                                  <input type="number" id="montant_restant_mouvent" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_jurid">
                                  <label for=""> <?=lang('messages_lang.mont_rest_engag_jur')?></label>
                                  <input type="number" id="montant_restant_mouvent_jurid" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_liquid">
                                  <label for=""> <?=lang('messages_lang.mont_rest_liquidation')?> </label>
                                  <input type="number" id="montant_restant_mouvent_liq" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_ord">
                                  <label for=""> <?=lang('messages_lang.mont_rest_ord')?></label>
                                  <input type="number" id="montant_restant_mouvent_ord" class="form-control" readonly>
                                </div>

                                <div class="col-md-12" id="rest_paie">
                                  <label for=""> <?=lang('messages_lang.mont_rest_paiemt')?></label>
                                  <input type="number" id="montant_restant_mouvent_paie" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_decais">
                                  <label for=""> <?=lang('messages_lang.mont_rest_decais')?> </label>
                                  <input type="number" id="montant_restant_mouvent_decais" class="form-control" readonly>
                                </div>

                              </div>
                            </div>
                          </div>
                          
                          <div class="col-md-12 mt-5 " >
                            <div class="form-group " >
                              <a onclick="savetemp()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?=lang('messages_lang.modifier')?></a>
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

  <script>
    $(document).ready(function ()
    {
      //debut execution budgetaire recupere dans la table execution budgetaire
    
      var MONTANT_ENGAGE=$('#ENG_BUDGETAIRE_ID').val();
      var MONTANT_JURDIQUE=$('#ENG_JURIDIQUE_ID').val();
      var LIQUIDATION  = $('#LIQUIDATION').val();
      var MONTANT_ORDONANCEMENT=$('#ORDONNANCEMENT_ID').val();
      var MONTANT_PAIEMENT=$('#PAIEMENT_ID').val();
      var MONTANT_DECAISSEMENT=$('#DECAISSEMENT_ID').val();

      if (MONTANT_ENGAGE=='')
      {
        MONTANT_ENGAGE=0;
      }

      if (MONTANT_JURDIQUE=='')
      {
        MONTANT_JURDIQUE=0;
      }

      if (LIQUIDATION=='')
      {
        LIQUIDATION=0;
      }

      if (MONTANT_ORDONANCEMENT=='')
      {
        MONTANT_ORDONANCEMENT=0;
      }

      if (MONTANT_PAIEMENT=='')
      {
        MONTANT_PAIEMENT=0;
      }

      if (MONTANT_DECAISSEMENT=='')
      {
        MONTANT_DECAISSEMENT=0;
      }
      //fin execution budgetaire recupere dans la table execution budgetaire

      //debut input montant realise sur chaque etat d'execution
      var montant_realise_id  = $('#montant_realise_id').val();//montant_realise_id:input sur montant realise budgetaire
      var montant_realise_jurid_id  = $('#montant_realise_jurid_id').val();
      var montant_realise_liq_id  = $('#montant_realise_liq_id').val();
      var montant_realise_ord_id  = $('#montant_realise_ord_id').val();
      var montant_realise_paie_id  = $('#montant_realise_paie_id').val();
      var montant_realise_decais_id  = $('#montant_realise_decais_id').val();

      if (montant_realise_id=='')
      {
        montant_realise_id=0;
      }

      if (montant_realise_jurid_id=='')
      {
        montant_realise_jurid_id=0;
      }

      if (montant_realise_liq_id=='')
      {
        montant_realise_liq_id=0;
      }

      if (montant_realise_ord_id=='')
      {
        montant_realise_ord_id=0;
      }

      if (montant_realise_paie_id=='')
      {
        montant_realise_paie_id=0;
      }

      if (montant_realise_decais_id=='')
      {
        montant_realise_decais_id=0;
      }
      //fin input montant realise sur chaque etat d'execution

      // debut somme sur chaque etat d'execution
      var mont_realise_sommation = $('#mont_realise_sommation').val();
      var mont_realise_sommation_jurd = $('#mont_realise_sommation_jurd').val();
      var mont_realise_sommation_liq = $('#mont_realise_sommation_liq').val();
      var mont_realise_sommation_ord = $('#mont_realise_sommation_ord').val();
      var mont_realise_sommation_paie = $('#mont_realise_sommation_paie').val();
      var mont_realise_sommation_decais = $('#mont_realise_sommation_decais').val();
      // fin somme sur chaque etat d'execution

      var result_by_Mouvement1= parseInt(MONTANT_ENGAGE)- parseInt(mont_realise_sommation)
      var result_by_Mouvement2= parseInt(MONTANT_JURDIQUE)- parseInt(mont_realise_sommation_jurd)
      var result_by_Mouvement3= parseInt(MONTANT_PAIEMENT)- parseInt(mont_realise_sommation_paie)
      var result_by_Mouvement4= parseInt(MONTANT_DECAISSEMENT)- parseInt(mont_realise_sommation_decais)
      var result_by_Mouvement5= parseInt(MONTANT_ORDONANCEMENT)- parseInt(mont_realise_sommation_ord)
      var result_by_Mouvement6= parseInt(LIQUIDATION)- parseInt(mont_realise_sommation_liq)
      
      var reste_budget=parseInt(result_by_Mouvement1)-parseInt(montant_realise_id)
      var reste_juridique=parseInt(result_by_Mouvement2)-parseInt(montant_realise_jurid_id)
      var reste_liquidation=parseInt(result_by_Mouvement6)-parseInt(montant_realise_liq_id)
      var reste_ordon=parseInt(result_by_Mouvement5)-parseInt(montant_realise_ord_id)
      var reste_paie=parseInt(result_by_Mouvement3)-parseInt(montant_realise_paie_id)
      var reste_decais=parseInt(result_by_Mouvement4)-parseInt(montant_realise_decais_id)

      $('#montant_restant_mouvent').val(reste_budget);
      $('#montant_restant_mouvent_jurid').val(reste_juridique)
      $('#montant_restant_mouvent_liq').val(reste_liquidation)
      $('#montant_restant_mouvent_ord').val(reste_ordon)
      $('#montant_restant_mouvent_paie').val(reste_paie)
      $('#montant_restant_mouvent_decais').val(reste_decais)

      var mouvement= $('#Mouvement_id').val();
      if(mouvement==1)
      {
        $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");
        $('#num_bon_eng').show();
        $('#date_bon_eng').show();
        $('#titre_decaiss').hide();
        $('#date_titre').hide();

        $('#mont_jurid').hide();
        $('#mont_liq').hide();
        $('#mont_ordon').hide();
        $('#mont_paiemt').hide();
        $('#mont_decaiss').hide();

        $('#rest_jurid').hide();
        $('#rest_liquid').hide();
        $('#rest_ord').hide();
        $('#rest_paie').hide();
        $('#rest_decais').hide();
      }
      else if(mouvement==2)
      { 
        $('#num_bon_eng').show();
        $('#date_bon_eng').show();
        $('#titre_decaiss').hide();
        $('#date_titre').hide();
        $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

        $('#mont_jurid').show();
        $('#mont_liq').hide();
        $('#mont_ordon').hide();
        $('#mont_paiemt').hide();
        $('#mont_decaiss').hide();

        $('#rest_jurid').show();
        $('#rest_liquid').hide();
        $('#rest_ord').hide();
        $('#rest_paie').hide();
        $('#rest_decais').hide();
      }
      else if(mouvement==3)
      {
        $('#num_bon_eng').show();
        $('#date_bon_eng').show();
        $('#titre_decaiss').hide();
        $('#date_titre').hide();
        $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

        $('#mont_jurid').show();
        $('#mont_liq').show();
        $('#mont_ordon').hide();
        $('#mont_paiemt').hide();
        $('#mont_decaiss').hide();

        $('#rest_jurid').show();
        $('#rest_liquid').show();
        $('#rest_ord').hide();
        $('#rest_paie').hide();
        $('#rest_decais').hide();
      }
      else if(mouvement==4)
      {
        $('#num_bon_eng').show();
        $('#date_bon_eng').show();
        $('#titre_decaiss').hide();
        $('#date_titre').hide();
        $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

        $('#mont_jurid').show();
        $('#mont_liq').show();
        $('#mont_ordon').show();
        $('#mont_paiemt').hide();
        $('#mont_decaiss').hide();

        $('#rest_jurid').show();
        $('#rest_liquid').show();
        $('#rest_ord').show();
        $('#rest_paie').hide();
        $('#rest_decais').hide();
      }
      else if(mouvement==5)
      {
        $('#titre_decaiss').show();
        $('#date_titre').show();
        $('#num_bon_eng').hide();
        $('#date_bon_eng').hide();
        $('#identifiant').html("<?=lang('messages_lang.labelle_titre_decaissement') ?>");

        $('#mont_jurid').show();
        $('#mont_liq').show();
        $('#mont_ordon').show();
        $('#mont_paiemt').show();
        $('#mont_decaiss').show();

        $('#rest_jurid').show();
        $('#rest_liquid').show();
        $('#rest_ord').show();
        $('#rest_paie').show();
        $('#rest_decais').show();

      }else if ( mouvement==7)
      {
        $('#titre_decaiss').show();
        $('#date_titre').show();
        $('#num_bon_eng').hide();
        $('#date_bon_eng').hide();
        $('#identifiant').html("<?=lang('messages_lang.labelle_titre_decaissement') ?>");

        $('#mont_jurid').show();
        $('#mont_liq').show();
        $('#mont_ordon').show();
        $('#mont_paiemt').show();
        $('#mont_decaiss').hide();

        $('#rest_jurid').show();
        $('#rest_liquid').show();
        $('#rest_ord').show();
        $('#rest_paie').show();
        $('#rest_decais').hide();
      }

      var IS_TRANSFERT_ACTIVITE = $('#IS_TRANSFERT_ACTIVITE').val();
      if(IS_TRANSFERT_ACTIVITE==1)
      {
        $('#num_montant_transfert').show()
        $('#div_tranche').show()
        $('#div_trnsf').show()   
      }
      else
      {
        $('#num_montant_transfert').hide()
        $('#div_tranche').hide()
        $('#div_trnsf').hide()
      }

      $('#montant_realise_jurid_id').bind('paste', function (e) {
         e.preventDefault();
      });

      $('#montant_realise_liq_id').bind("paste",function(e) {
        e.preventDefault();
      });
      $('#montant_realise_ord_id').bind("paste",function(e) {
        e.preventDefault();
      });
      $('#montant_realise_paie_id').bind("paste",function(e) {
        e.preventDefault();
      });
      $('#montant_realise_decais_id').bind("paste",function(e) {
        e.preventDefault();
      });
     
      if(mouvement=='')
      {
        $('#montant_realise_id').attr('disabled',true)
      }
      
    });
  </script>
     
  <script>
    function is_transfert()
    {
      var IS_TRANSFERT_ACTIVITE=$('#IS_TRANSFERT_ACTIVITE').val();
      if (IS_TRANSFERT_ACTIVITE==1)
      {
        $('#num_montant_transfert').show();
        $('#div_tranche').show();
        $('#div_trnsf').show();
      }else
      {
        $('#num_montant_transfert').hide();
        $('#div_tranche').hide();
        $('#div_trnsf').hide();
      }
    }
  </script>

  <script>
    function valid_transfert()
    {
      statut = true;
      var montant_vote_id  = $('#montant_vote_id').val();
      var MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val();
      var total_vote_ligne=$('#total_vote_ligne').val();
      var montant_restant=$('#montant_restant').val();
      var montant_tranche=$('#montant_tranche').val();

      if (MONTANT_TRANSFERT=='')
      {
        MONTANT_TRANSFERT=0;
      }else
      {
        MONTANT_TRANSFERT=$('#MONTANT_TRANSFERT').val();
      }
      // var new_montant_ligne=parseInt(total_vote_ligne)+parseInt(MONTANT_TRANSFERT);
      var new_montant_ligne=parseInt(montant_restant)+parseInt(MONTANT_TRANSFERT);

      $('#new_montant_ligne').val(new_montant_ligne)
      var new_montant_vote_id=parseInt(montant_vote_id)+parseInt(MONTANT_TRANSFERT);
      $('#new_montant_vote_id').val(new_montant_vote_id)

      var montant_tr=parseInt(MONTANT_TRANSFERT)>parseInt(montant_tranche)
      if (montant_tr)
      {
        statut = false;
        $('#error_mont_transfert').html("<?=lang('messages_lang.limite_montant_transferer')?>");
      }else
      {
        $('#error_mont_transfert').html("");
      }
      
    }
  </script>

  <script>
    function get_mont_transfert()
    {
      var TRANCHE_ID=$('#TRANCHE_ID').val();
      var PTBA_ID=$('#PTBA_ID').val();
      var montant_vote_id=$('#montant_vote_id').val();
      var montant_restant=$('#montant_restant').val();

      if(TRANCHE_ID==5)
      {
        $('#MONTANT_TRANSFERT').prop("readonly", true);
      }else
      {
        $('#MONTANT_TRANSFERT').prop("readonly", false);
      }
    
      $.ajax(
      {
        url:"<?=base_url()?>/demande_new/Proc_Demande_Budget_Corriger/get_mont_transfert/"+TRANCHE_ID+'/'+PTBA_ID,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {          
          $('#MONTANT_TRANSFERT').val(data.montant_trim); 
          $('#montant_tranche').val(data.montant_trim); 

          var apres_transfert=$('#montant_tranche').val();

          var montant_transfert=0;
          montant_transfert=parseInt(montant_vote_id)+parseInt(apres_transfert);

          $('#new_montant_vote_id').val(montant_transfert);

          var new_montant_ligne=0;
          new_montant_ligne=parseInt(montant_restant)+parseInt(apres_transfert);
          $('#new_montant_ligne').val(new_montant_ligne);
        }
      });
    }
  </script>

  <script>
/**fonction pour activer la zone a sasit le montant realisé */
    function active_mouvement()
    {
      var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();
      if(MOUVEMENT_DEPENSE_ID==''){;
      $('#montant_realise_id').attr('disabled',true)
    }else{
      $('#montant_realise_id').attr('disabled',false)

    }
    
  }
</script>

<script>
  /**fonction pour recuper les montqnt selon id ptba */
  function get_mont()
  {
    statut=true
    var PTBA_ID=$('#PTBA_ID').val();
    $.ajax(
    {
      url:"<?=base_url()?>/demande_new/Proc_Demande_Budget_Corriger/get_montantss/"+PTBA_ID,
      type:"GET",
      dataType:"JSON",
      success: function(data)
      {
        var LIQUIDATION=$('#LIQUIDATION').val();
        
        $('#montant_vote_id').val(data.MONTANT);
        $('#mont').val(data.mont);
        $('#quantite_vote_id').val(data.QUANTITE);
        $('#actions_id').val(data.CODE_ACTION);
        $('#programes_code_id').val(data.CODE_PROGRAMME);
        $('#actions_desc_id').val(data.ACTION);
        $('#programes_desc_id').val(data.PROGRAMME);
        $('#programes_desc_id').val(data.PROGRAMME);
        $('#Institutions_id').val(data.INSTITUTION_ID);
        $('#Institutions_code_id').val(data.DESCRIPTION_INSTITUTION);
        $('#montant_restant_actite').val(data.MONTANT_RESTANT);
        $('#Mouvement_id').val(data.MOUVEMENT_DEPENSE_ID);
        $('#Mouvement_des_id').val(data.DESC_MOUVEMENT_DEPENSE);
        
      }
    });
    
  }
</script>

<script type="text/javascript">
  function save()
  {   
    $('#MyFormDatatable').submit();
  }
</script>

<script>
  const numberInput = document.getElementById('montant_realise_id');
  numberInput.addEventListener('input', function() {
    const value = numberInput.value;
    if (isNaN(value)) {
      numberInput.value = ''; 
    }
  });
  numberInput.addEventListener('keydown', function(event) {
    if (
      !/[0-9.]/.test(event.key) &&
      !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(event.key)
      ) {
      event.preventDefault();
  }
});
</script>

  <script>
    $('#message').delay('slow').fadeOut(60000);

  </script>

  <script>
    function savetemp()
    {
      $('#MyFormData').submit();
    }    
  </script>
  <script>
    /**afichhager des labels selon le mouvent selectionner */
    function change_label()
    {
      var mouvement= $('#Mouvement_id').val();
      if(mouvement==1){
        $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");
        $('#num_bon_eng').show();
        $('#date_bon_eng').show();
        $('#titre_decaiss').hide();
        $('#date_titre').hide();

        $('#mont_jurid').hide();
        $('#mont_liq').hide();
        $('#mont_ordon').hide();
        $('#mont_paiemt').hide();
        $('#mont_decaiss').hide();

        $('#rest_jurid').hide();
        $('#rest_liquid').hide();
        $('#rest_ord').hide();
        $('#rest_paie').hide();
        $('#rest_decais').hide();
      }
      else if(mouvement==2)
      { $('#num_bon_eng').show();
      $('#date_bon_eng').show();
      $('#titre_decaiss').hide();
      $('#date_titre').hide();
      $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

      $('#mont_jurid').show();
      $('#mont_liq').hide();
      $('#mont_ordon').hide();
      $('#mont_paiemt').hide();
      $('#mont_decaiss').hide();

      $('#rest_jurid').show();
      $('#rest_liquid').hide();
      $('#rest_ord').hide();
      $('#rest_paie').hide();
      $('#rest_decais').hide();
    }
    else if(mouvement==3)
    {
      $('#num_bon_eng').show();
      $('#date_bon_eng').show();
      $('#titre_decaiss').hide();
      $('#date_titre').hide();
      $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

      $('#mont_jurid').show();
      $('#mont_liq').show();
      $('#mont_ordon').hide();
      $('#mont_paiemt').hide();
      $('#mont_decaiss').hide();

      $('#rest_jurid').show();
      $('#rest_liquid').show();
      $('#rest_ord').hide();
      $('#rest_paie').hide();
      $('#rest_decais').hide();
    }
    else if(mouvement==4)
    {
      $('#num_bon_eng').show();
      $('#date_bon_eng').show();
      $('#titre_decaiss').hide();
      $('#date_titre').hide();
      $('#identifiant').html("<?=lang('messages_lang.Bon_engagement') ?>");

      $('#mont_jurid').show();
      $('#mont_liq').show();
      $('#mont_ordon').show();
      $('#mont_paiemt').hide();
      $('#mont_decaiss').hide();

      $('#rest_jurid').show();
      $('#rest_liquid').show();
      $('#rest_ord').show();
      $('#rest_paie').hide();
      $('#rest_decais').hide();
    }
    else if(mouvement==5)
    {
      $('#titre_decaiss').show();
      $('#date_titre').show();
      $('#num_bon_eng').hide();
      $('#date_bon_eng').hide();
      $('#identifiant').html("<?=lang('messages_lang.labelle_titre_decaissement') ?>");

      $('#mont_jurid').show();
      $('#mont_liq').show();
      $('#mont_ordon').show();
      $('#mont_paiemt').show();
      $('#mont_decaiss').show();

      $('#rest_jurid').show();
      $('#rest_liquid').show();
      $('#rest_ord').show();
      $('#rest_paie').show();
      $('#rest_decais').show();

    }else if ( mouvement==7)
    {
      $('#titre_decaiss').show();
      $('#date_titre').show();
      $('#num_bon_eng').hide();
      $('#date_bon_eng').hide();
      $('#identifiant').html("<?=lang('messages_lang.labelle_titre_decaissement') ?>");

      $('#mont_jurid').show();
      $('#mont_liq').show();
      $('#mont_ordon').show();
      $('#mont_paiemt').show();
      $('#mont_decaiss').hide();

      $('#rest_jurid').show();
      $('#rest_liquid').show();
      $('#rest_ord').show();
      $('#rest_paie').show();
      $('#rest_decais').hide();
    }

  }
</script>

<script>
    /**validation des montants selon le montant realise saisit */
  function valide_montant()
  {
    statut = true;
    var montant_vote_id  = $('#montant_vote_id').val();
    var IS_TRANSFERT_ACTIVITE  = $('#IS_TRANSFERT_ACTIVITE').val();
    var zero = 0;

    //debut execution budgetaire recupere dans la table execution budgetaire
    
    var MONTANT_ENGAGE=$('#ENG_BUDGETAIRE_ID').val();
    var MONTANT_JURDIQUE=$('#ENG_JURIDIQUE_ID').val();
    var LIQUIDATION  = $('#LIQUIDATION').val();
    var MONTANT_ORDONANCEMENT=$('#ORDONNANCEMENT_ID').val();
    var MONTANT_PAIEMENT=$('#PAIEMENT_ID').val();
    var MONTANT_DECAISSEMENT=$('#DECAISSEMENT_ID').val();
    var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();

    if (MONTANT_ENGAGE=='')
    {
      MONTANT_ENGAGE=0;
    }

    if (MONTANT_JURDIQUE=='')
    {
      MONTANT_JURDIQUE=0;
    }

    if (LIQUIDATION=='')
    {
      LIQUIDATION=0;
    }

    if (MONTANT_ORDONANCEMENT=='')
    {
      MONTANT_ORDONANCEMENT=0;
    }

    if (MONTANT_PAIEMENT=='')
    {
      MONTANT_PAIEMENT=0;
    }

    if (MONTANT_DECAISSEMENT=='')
    {
      MONTANT_DECAISSEMENT=0;
    }
    //fin execution budgetaire recupere dans la table execution budgetaire

    //debut input montant realise sur chaque etat d'execution
    var montant_realise_id  = $('#montant_realise_id').val();//montant_realise_id:input sur montant realise budgetaire
    var montant_realise_jurid_id  = $('#montant_realise_jurid_id').val();
    var montant_realise_liq_id  = $('#montant_realise_liq_id').val();
    var montant_realise_ord_id  = $('#montant_realise_ord_id').val();
    var montant_realise_paie_id  = $('#montant_realise_paie_id').val();
    var montant_realise_decais_id  = $('#montant_realise_decais_id').val();

    if (montant_realise_id=='')
    {
      montant_realise_id=0;
    }

    if (montant_realise_jurid_id=='')
    {
      montant_realise_jurid_id=0;
    }

    if (montant_realise_liq_id=='')
    {
      montant_realise_liq_id=0;
    }

    if (montant_realise_ord_id=='')
    {
      montant_realise_ord_id=0;
    }

    if (montant_realise_paie_id=='')
    {
      montant_realise_paie_id=0;
    }

    if (montant_realise_decais_id=='')
    {
      montant_realise_decais_id=0;
    }
    //fin input montant realise sur chaque etat d'execution

    // debut somme sur chaque etat d'execution
    var mont_realise_sommation = $('#mont_realise_sommation').val();
    var mont_realise_sommation_jurd = $('#mont_realise_sommation_jurd').val();
    var mont_realise_sommation_liq = $('#mont_realise_sommation_liq').val();
    var mont_realise_sommation_ord = $('#mont_realise_sommation_ord').val();
    var mont_realise_sommation_paie = $('#mont_realise_sommation_paie').val();
    var mont_realise_sommation_decais = $('#mont_realise_sommation_decais').val();
    // fin somme sur chaque etat d'execution

    var montant_restant_actite=$('#montant_restant_actite1').val();
    var montant_rest=$('#montant_vote_id').val();
    
    var verifmont_engament= parseInt(montant_realise_id) > parseInt(MONTANT_ENGAGE)
    var verifmont_jurdique= parseInt(montant_realise_jurid_id) > parseInt(MONTANT_JURDIQUE)
    var mount= parseInt(montant_realise_liq_id)> parseInt(LIQUIDATION)
    var verifmont_ordonancement= parseInt(montant_realise_ord_id) > parseInt(MONTANT_ORDONANCEMENT)
    var verifmont_paiement= parseInt(montant_realise_paie_id) > parseInt(MONTANT_PAIEMENT)
    var verifmont_decaissement= parseInt(montant_realise_decais_id) > parseInt(MONTANT_DECAISSEMENT)

    var result_by_Mouvement1= parseInt(MONTANT_ENGAGE)- parseInt(mont_realise_sommation)
    var result_by_Mouvement2= parseInt(MONTANT_JURDIQUE)- parseInt(mont_realise_sommation_jurd)
    var result_by_Mouvement3= parseInt(MONTANT_PAIEMENT)- parseInt(mont_realise_sommation_paie)
    var result_by_Mouvement4= parseInt(MONTANT_DECAISSEMENT)- parseInt(mont_realise_sommation_decais)
    var result_by_Mouvement5= parseInt(MONTANT_ORDONANCEMENT)- parseInt(mont_realise_sommation_ord)
    var result_by_Mouvement6= parseInt(LIQUIDATION)- parseInt(mont_realise_sommation_liq)
    

    var reste_budget=parseInt(result_by_Mouvement1)-parseInt(montant_realise_id)
    var reste_juridique=parseInt(result_by_Mouvement2)-parseInt(montant_realise_jurid_id)
    var reste_liquidation=parseInt(result_by_Mouvement6)-parseInt(montant_realise_liq_id)
    var reste_ordon=parseInt(result_by_Mouvement5)-parseInt(montant_realise_ord_id)
    var reste_paie=parseInt(result_by_Mouvement3)-parseInt(montant_realise_paie_id)
    var reste_decais=parseInt(result_by_Mouvement4)-parseInt(montant_realise_decais_id)

    var new_montant_vote_id=$('#new_montant_vote_id').val();

    if (reste_budget<0)
    {
      $('#montant_restant_mouvent').val(zero);
    }else{
      $('#montant_restant_mouvent').val(reste_budget);
    }

    if (reste_juridique<0)
    {
      $('#montant_restant_mouvent_jurid').val(zero);
    }else{
      $('#montant_restant_mouvent_jurid').val(reste_juridique);
    }
    if (reste_liquidation<0)
    {
      $('#montant_restant_mouvent_liq').val(zero);
    }else{
      $('#montant_restant_mouvent_liq').val(reste_liquidation);
    }
    if (reste_ordon<0)
    {
      $('#montant_restant_mouvent_ord').val(zero);
    }else{
      $('#montant_restant_mouvent_ord').val(reste_ordon);
    }

    if (reste_paie<0)
    {
      $('#montant_restant_mouvent_paie').val(zero);
    }else{
      $('#montant_restant_mouvent_paie').val(reste_paie);
    }
    if (reste_decais<0)
    {
      $('#montant_restant_mouvent_decais').val(zero);
    }else{
      $('#montant_restant_mouvent_decais').val(reste_decais);
    }

    var restant_activite=0;
    var montant_etap_exec=0;
    if (MOUVEMENT_DEPENSE_ID==1)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_id);
      montant_etap_exec=parseInt(montant_realise_id);
    }else if (MOUVEMENT_DEPENSE_ID==2)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_jurid_id);
      montant_etap_exec=parseInt(montant_realise_jurid_id);
    }else if (MOUVEMENT_DEPENSE_ID==3)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_liq_id);
      montant_etap_exec=parseInt(montant_realise_liq_id);
    }else if (MOUVEMENT_DEPENSE_ID==4)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_ord_id);
      montant_etap_exec=parseInt(montant_realise_ord_id);
    }else if (MOUVEMENT_DEPENSE_ID==5)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_decais_id);
      montant_etap_exec=parseInt(montant_realise_decais_id);

    }else if (MOUVEMENT_DEPENSE_ID==7)
    {
      restant_activite=parseInt(montant_rest) - parseInt(montant_realise_paie_id);
      montant_etap_exec=parseInt(montant_realise_paie_id);
    }

    $('#montant_restant_actite1').val(restant_activite);

    if (montant_etap_exec=='')
    {
      montant_etap_exec=0;
    }
    var restant_act=0;
    var mountvotjurid=0;
    var mountvotliq=0;
    var mountvotord=0;
    var mountvotpaie=0;
    var mountvotdecais=0;
    var mountvot=0;

    if (IS_TRANSFERT_ACTIVITE==1)
    {
      restant_act=parseInt(new_montant_vote_id)-parseInt(montant_etap_exec);

      restant_act=parseInt(montant_vote_id) > parseInt(montant_etap_exec);
      mountvot= parseInt(montant_realise_id) > parseInt(new_montant_vote_id)

      mountvotjurid= parseInt(montant_realise_jurid_id) > parseInt(new_montant_vote_id)
      mountvotliq= parseInt(montant_realise_liq_id) > parseInt(new_montant_vote_id)
      mountvotord= parseInt(montant_realise_ord_id) > parseInt(new_montant_vote_id)
      mountvotpaie= parseInt(montant_realise_paie_id) > parseInt(new_montant_vote_id)
      mountvotdecais= parseInt(montant_realise_decais_id) > parseInt(new_montant_vote_id)
      
      if((mountvot) || (mountvotjurid) || (mountvotliq) || (mountvotord) || (mountvotpaie) || (mountvotdecais))
      {
        statut = false;
        $('#new_montant_error').html("<?=lang('messages_lang.msg_montant_vote')?>")
      }else{
        $('#new_montant_error').html("")
      }
    }else
    {

      restant_act=parseInt(montant_vote_id)-parseInt(montant_etap_exec);

      mountvot= parseInt(montant_realise_id) > parseInt(montant_vote_id)
      mountvotjurid= parseInt(montant_realise_jurid_id) > parseInt(montant_vote_id)
      mountvotliq= parseInt(montant_realise_liq_id) > parseInt(montant_vote_id)
      mountvotord= parseInt(montant_realise_ord_id) > parseInt(montant_vote_id)
      mountvotpaie= parseInt(montant_realise_paie_id) > parseInt(montant_vote_id)
      mountvotdecais= parseInt(montant_realise_decais_id) > parseInt(montant_vote_id)

      if((mountvot) || (mountvotjurid) || (mountvotliq) || (mountvotord) || (mountvotpaie) || (mountvotdecais))
      {
        statut = false;
        $('#montant_error').html("<?=lang('messages_lang.msg_montant_vote')?>")
      }else{
        
        $('#montant_error').html("")
      }
    }

    if (restant_act<0)
    {
      $('#montant_restant_actite').val(zero);
    }else{
      $('#montant_restant_actite').val(restant_act);
    }

    
    //debut gestion montant restant par ligne budgetaire
    var total_vote_ligne=$('#total_vote_ligne').val();
    var total_ligne=$('#total_ligne').val();

    var new_montant_ligne=$('#new_montant_ligne').val()

    var restant=0;
    if (IS_TRANSFERT_ACTIVITE==1)
    {
      restant=parseInt(new_montant_ligne) - parseInt(total_ligne);
    }else{
      restant=parseInt(total_vote_ligne) - parseInt(total_ligne);
    }
    

    $('#montant_restant').val(restant);

    var rest_ligne=restant-montant_etap_exec;
    
    if (rest_ligne<0)
    {
      $('#montant_restant').val(zero);
    }else{
      $('#montant_restant').val(rest_ligne);
    }
    

    var montant_restant_id = $('#montant_restant').val();

    
    if(montant_restant_id <0 )
    {
      $('#error_montant_restant').html('<?=lang('messages_lang.plus_mont_raccrocher')?> ');
      statut = false;
    }else
    {
      $('#error_montant_restant').html('');
    }
    //debut gestion montant resta,t par ligne budgetaire

    $('#montant_errorreal').html('');
    

    if (parseInt(montant_realise_id) > result_by_Mouvement1)
    {
      statut = false;
      $('#montant_error_mont').html("<?=lang('messages_lang.mess_sup_rest_eng_budg')?>")
    }else
    {
      if(verifmont_engament)
      {
        statut = false;
        $('#montant_error_mont').html("<?=lang('messages_lang.mess_sup_Mont_eng')?> ")
        
      }
    }
    
    if(verifmont_engament==0)
    {
      $('#montant_error_mont').html('');
    }

    if (parseInt(montant_realise_jurid_id) > result_by_Mouvement2)
    {
      $('#montant_error_mont_jurd').html("<?=lang('messages_lang.mess_mont_sup_eng_jur')?>")
    }else
    {
      
      if(verifmont_jurdique)
      {
        statut = false;
        $('#montant_error_mont_jurd').html("<?=lang('messages_lang.mess_sup_mont_jur')?>")
      }
    }
      
    if(verifmont_jurdique==0)
    {
      $('#montant_error_mont_jurd').html('');
    }

    if (parseInt(montant_realise_liq_id) > result_by_Mouvement6) {
        statut = false;
        $('#montant_error_mont_liq').html("<?=lang('messages_lang.mess_sup_mont_liq_rest')?>")
      }else
      {
        if(mount){
          statut = false;
          $('#montant_error_mont_liq').html("<?=lang('messages_lang.mess_sup_mont_liq')?>")
        }
        if(mount==0){
          $('#montant_error_mont_liq').html('');
        }
      }

      if (parseInt(montant_realise_ord_id) > result_by_Mouvement5) {
        statut = false;
        $('#montant_error_mont_ord').html("<?=lang('messages_lang.mess_ord_rest_sup')?>")
      }else
      {
        if(verifmont_ordonancement)
        {
          statut = false;
          $('#montant_error_mont_ord').html("<?=lang('messages_lang.mess_sup_ord')?>")
        }
        if(verifmont_ordonancement==0)
        {
          $('#montant_error_mont_ord').html('');
        }
      }

      if (parseInt(montant_realise_paie_id) > result_by_Mouvement3) {
        statut = false;
        $('#montant_error_mont_paie').html("<?=lang('messages_lang.mess_sup_pai_rest')?> ")
      }else
      {
        if(verifmont_paiement)
        {
          statut = false;
          $('#montant_error_mont_paie').html("<?=lang('messages_lang.mess_sup_pai')?> ")
        }
        if(verifmont_paiement==0){
          $('#montant_error_mont_paie').html('');
        }
      }

      if (parseInt(montant_realise_decais_id) > result_by_Mouvement4) {
        statut = false;
        $('#montant_error_mont_decais').html("<?=lang('messages_lang.mess_mont_rest_dec')?>")
      }else
      {
        if(verifmont_decaissement)
        {
          statut = false;
          $('#montant_error_mont_decais').html("<?=lang('messages_lang.mess_mont_dec')?>")
        }
        if(verifmont_decaissement==0)
        {
          $('#montant_error_mont_decais').html('');
        }
      }
      
    
  }

</script>

<script>
// controle de l'extension et de la taille de la preuve 
  function Valid_preuve(id)
  {
    if (id==2)
    {
      var fileInput = document.getElementById('PREUVE');
      var filePath = fileInput.value;
      // Allowing file type
      var allowedExtensions = /(\.pdf)$/i;
      
      if (!allowedExtensions.exec(filePath))
      {
        $('#preuve_error').text("<?=lang('messages_lang.error_message_pdf')?>");
        fileInput.value = '';
        return false;
      }
      else
      {
        // Check if any file is selected. 
        if (fileInput.files.length > 0)
        { 
          for (const i = 0; i <= fileInput.files.length - 1; i++)
          { 
            const fsize = fileInput.files.item(i).size; 
            const file = Math.round((fsize / 1024)); 
            // The size of the file. 
            if (file > 200)
            { 
              $('#preuve_error').text("<?=lang('messages_lang.error_message_taille_pdf')?>");
              fileInput.value = '';
            } else{
              $('#preuve_error').text(''); 
            }
          } 
        }
      }
    }else if (id==1)
    {
      var fileInput = document.getElementById('doc_raccroche');
      var filePath = fileInput.value;
      // Allowing file type
      var allowedExtensions = /(\.pdf)$/i;
  
      if (!allowedExtensions.exec(filePath))
      {
        $('#doc_error').text("<?=lang('messages_lang.error_message_pdf')?>");
        fileInput.value = '';
        return false;
      }
      else
      {
      // Check if any file is selected. 
        if (fileInput.files.length > 0)
        { 
          for (const i = 0; i <= fileInput.files.length - 1; i++)
          { 
            const fsize = fileInput.files.item(i).size; 
            const file = Math.round((fsize / 1024)); 
            // The size of the file. 
            if (file > 200)
            { 
              $('#doc_error').text("<?=lang('messages_lang.error_message_taille_pdf')?>");
              fileInput.value = '';
            }else
            {
             $('#doc_error').text(''); 
            }
          } 
        }
      }
    }

  }
</script>