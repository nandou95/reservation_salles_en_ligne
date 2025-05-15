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
                    <a href="<?php echo base_url('demande_new/Raccrochage_Deuxieme_Trim/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?=lang('messages_lang.link_list') ?></a>
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
                    <h4 style="margin-left:4%;margin-top:10px"> <?=lang('messages_lang.titre_raccrochage') ?></h4>
                    <br>

                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('demande_new/Raccrochage_Deuxieme_Trim/saveinfo_activite/')?>" method="post" >
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
                            <div class="col-md-3"><i class="fa fa-building"></i>&nbsp;&nbsp;<?=lang('messages_lang.table_st') ?></div>
                            <div class="col-md-9"><?= $resultatinsttut['CODE_SOUS_TUTEL'].'&nbsp;&nbsp;'.$resultatinsttut['DESCRIPTION_SOUS_TUTEL']?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-cubes"></i>&nbsp;&nbsp;<?=lang('messages_lang.label_ligne') ?> </div>
                            <div class="col-md-9"><?= $info['IMPUTATION']?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-file-text"></i>&nbsp;&nbsp;<?=lang('messages_lang.labelle_libelle') ?></div>
                            <div class="col-md-9"><?= $info['LIBELLE'] ?></div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-cogs"></i>&nbsp;&nbsp;<?=lang('messages_lang.credit_accorde') ?></div>
                            <div class="col-md-9"><?= number_format($montant_total['T2'],'0',',',' ')?> BIF</div>
                          </div><hr>
                          <div class="row">
                            <div class="col-md-3"><i class="fa fa-credit-card"></i>&nbsp;&nbsp;<?=lang('messages_lang.credit_restant') ?></div>
                            <div class="col-md-9"><?= number_format($montant_total['total'],'0',',',' ')?> BIF</div>
                            <input type="hidden" name="total_vote_ligne" id="total_vote_ligne" value="<?=$montant_total['total'] ?>">
                            <input type="hidden" name="total_ligne" id="total_ligne" value="<?=$total_ligne ?>">
                          </div><hr>

                          <input type="hidden" class="form-control" value="<?=$info['LIQUIDATION']?>" name="LIQUIDATION" id="LIQUIDATION" readonly>
                          <input type="hidden"id="ENG_BUDGETAIRE_ID" value="<?=$info['ENG_BUDGETAIRE']?>">
                          <input type="hidden"id="PAIEMENT_ID" value="<?=$info['PAIEMENT']?>">
                          <input type="hidden"id="ENG_JURIDIQUE_ID"value="<?=$info['ENG_JURIDIQUE']?>">
                          <input type="hidden"id="DECAISSEMENT_ID" value="<?=$info['DECAISSEMENT']?>">
                          <input type="hidden"id="ORDONNANCEMENT_ID" value="<?=$info['ORDONNANCEMENT']?>">
                          
                          <div class="card shadow">
                            <div class="table-responsive  mt-3">
                              <table class="table table-striped table-bordered">
                                <thead>
                                  <th><?=lang('messages_lang.labelle_eng_budget') ?></th>
                                  <th><?=lang('messages_lang.labelle_eng_jud') ?></th>
                                  <th><?=lang('messages_lang.labelle_liquidation') ?></th>
                                  <th><?=lang('messages_lang.labelle_ordonan') ?></th>
                                  <th><?=lang('messages_lang.labelle_paiement') ?></th>
                                  <th><?=lang('messages_lang.labelle_decaisse') ?></th>
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

                          <input type="hidden" class="form-control" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>" name="demande">
                          <input type="hidden" class="form-control" value="<?=$demande_exec['GRANDE_MASSE_BM']?>" name="GRANDE_MASSE_ID">
                          
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
                                    <label class="form-label"> <?=lang('messages_lang.labelle_activite') ?>  <font color="red">*</font></label>
                                    <select class="select2 form-control" id="PTBA_ID" value="<?=set_value('PTBA_ID') ?>" name="PTBA_ID" onchange="get_mont()" autofocus >
                                      <option value=""><?=lang('messages_lang.selection_message') ?></option>
                                      <?php foreach($activite as $activi):?>
                                        <option value="<?= $activi->PTBA_ID?>"><?= $activi->ACTIVITES?></option>
                                      <?php endforeach ?>
                                      
                                    </select>
                                    <font color="red" id="error_PTBA_ID"></font>
                                    <?= $validation->getError('PTBA_ID'); ?>
                                  </div>
                                </div>
                                <input type="hidden" name="new_montant_ligne" id="new_montant_ligne">
                                
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label"><?=lang('messages_lang.etat_exec') ?> <font color="red">*</font></label>
                                    <select name="Mouvement_code" id="Mouvement_id" class=" select2 form-control" onchange="change_label();active_mouvement()">
                                      <option value=""><?=lang('messages_lang.selection_message') ?></option>

                                      <?php foreach($mvt_depense as $moumvent):?>
                                        <option value="<?= $moumvent->MOUVEMENT_DEPENSE_ID?>"><?= $moumvent->DESC_MOUVEMENT_DEPENSE?></option>
                                      <?php endforeach ?>
                                     
                                    </select>
                                    <font color="red" id="error_Mouvement"></font>
                                    <?= $validation->getError('Mouvement_code'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="num_bon_eng">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.table_num_bon') ?> <font color="red">*</font></label>
                                    <input type="text" class="form-control" name="numero_bon" id="numero_bon_id" minlength="5" maxlength="20">
                                    <font color="red" id="error_num_bon"></font>
                                    <?= $validation->getError('numero_bon'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="date_bon_eng">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.date_bon_engag') ?><font color="red">*</font></label>
                                    <input type="date" max="<?= date('Y-m-d')?>" name="date_bon" id="date_bon_id" class="form-control" onblur="checkDate(this.value)">
                                    <font color="red" id="error_date_bon"></font>
                                    <?= $validation->getError('date_bon'); ?>
                                  </div>
                                </div>

                                <div class="col-md-6" id="titre_decaiss">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.table_num_titre') ?> <font color="red">*</font></label>
                                    <input type="text" class="form-control" minlength="5" maxlength="20" name="numero_decaiss" id="numero_decaiss_id">
                                    <font color="red" id="error_num_decaissement"></font>
                                    <?= $validation->getError('numero_decaiss'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="date_titre">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.date_titre_decais') ?> <font color="red">*</font></label>
                                    <input type="date" max="<?= date('Y-m-d')?>" name="date_decais" id="date_decais_id" class="form-control">
                                    <font color="red" id="error_date_decaissement"></font>
                                    <?= $validation->getError('date_decais'); ?>
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_eng_budget') ?><font color="red">*</font> <span id="loading_activite"></span></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_id" onKeyup="valide_montant();" name="montant_realise" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont"></font>
                                    <?= $validation->getError('montant_realise'); ?>
                                  </div>
                                </div>
                                <!-- new -->
                                <div class="col-md-6" id="mont_jurid">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_eng_jud') ?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_jurid_id" onKeyup="valide_montant()" name="montant_realise_jurid" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_jurd"></font>
                                    <?= $validation->getError('montant_realise_jurid'); ?>
                                  </div>

                                </div>
                                <div class="col-md-6" id="mont_liq">
                                  <div class="form-group">
                                    <label for=""><?=lang('messages_lang.labelle_liquidation') ?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_liq_id" onKeyup="valide_montant()" name="montant_realise_liq" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_liq"></font>
                                    <?= $validation->getError('montant_realise_liq'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_ordon">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_ordonan') ?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_ord_id" onKeyup="valide_montant()" name="montant_realise_ord" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_ord"></font>
                                    <?= $validation->getError('montant_realise_ord'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_paiemt">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_paiement') ?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_paie_id" onKeyup="valide_montant()" name="montant_realise_paie" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_paie"></font>
                                    <?= $validation->getError('montant_realise_paie'); ?>
                                  </div>
                                </div>
                                <div class="col-md-6" id="mont_decaiss">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.labelle_decaisse') ?><font color="red">*</font></label>
                                    <input type="number" class="form-control" min="0"   id="montant_realise_decais_id" onKeyup="valide_montant()" name="montant_realise_decais" value="0">
                                    <font color="red" id="montant_errorreal"></font>
                                    <font color="red" id="montant_error_mont_decais"></font>
                                    <?= $validation->getError('montant_realise_decais'); ?>
                                  </div>
                                </div>
                                <!-- fin new -->
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for="" id="identifiant"> </label>
                                    <input type="file" class="form-control" onchange="Valid_preuve(1)" id="doc_raccroche"  name="doc_raccroche" accept=".pdf">
                                    <font color="red" id="doc_error"></font>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for=""> <?=lang('messages_lang.tel_preuve') ?></label>
                                    <input type="file" class="form-control" onchange="Valid_preuve(2)" id="PREUVE" name="PREUVE" accept=".pdf">
                                    <font color="red" id="preuve_error"></font>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label"> <?=lang('messages_lang.label_marche') ?>  <font color="red">*</font></label>
                                    <select class="form-control" id="MARCHE_PUBLIC" name="MARCHE_PUBLIC" >
                                      <option value=""><?=lang('messages_lang.selection_message') ?></option>
                                      <option value="1"><?=lang('messages_lang.label_oui') ?></option>
                                      <option value="0"><?=lang('messages_lang.label_non') ?></option>
                                    </select>
                                    <font color="red" id="error_marche"></font>
                                    <?= $validation->getError('MARCHE_PUBLIC'); ?>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group" hidden>
                                  <label class="form-label"> <?=lang('messages_lang.labelle_institution') ?> <font color="red">*</font></label>
                                  <input type="" class="form-control"  id="Institutions_code_id" name="Institutions_code" readonly>
                                  <input type="hidden" class="form-control" name="Institutions" id="Institutions_id" readonly>
                                  <font color="red" id="error_Institutions_id"></font>
                                </div>
                              </div>
                              <div class="col-md-12">
                                <label for=""> <?=lang('messages_lang.labelle_observartion') ?></label>
                                <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"></textarea>
                              </div>
                            </div><hr class="vertical">

                            <div class="col-md-4 mt-2" style="margin-bottom:50px;margin-left:-42px">
                              <div class="row">
                                <div class="col-md-12">
                                  <label class="form-label"> <?=lang('messages_lang.table_Programme') ?></label>
                                  <input type="hidden" class="form-control" id="programes_code_id"name="programes_code" >
                                  <input type="text" class="form-control" id="programes_desc_id"name="programes_desc" readonly>
                                  <font color="red" id="error_programes_id"></font>
                                  
                                </div>
                                
                                <div class="col-md-12">
                                  <label class="form-label"> <?=lang('messages_lang.table_Action') ?> </label>
                                  <input type="hidden" class="form-control" name="actions" id="actions_id">
                                  <input type="text" class="form-control" name="action_descr" id="actions_desc_id" readonly>
                                  <font color="red" id="error_Actions_id"></font>
                                  
                                </div>
                                <div class="col-md-12">

                                  <label for=""> <?=lang('messages_lang.labelle_montant_vote') ?></label>
                                  <input type="text" class="form-control"  id="mont" name="mont">
                                  <input type="hidden" class="form-control"  id="montant_vote_id" name="montant_vote" readonly>
                                  <font color="red" id="montant_error"></font>
                                </div>                                <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.montant_rest_activite') ?></label>
                                  <input type="text" id="montant_restant_actite" class="form-control" readonly>
                                </div>
                                <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.mont_restant_ligne') ?></label>
                                  <input type="number" class="form-control"   id="montant_restant" name="montant_restant" readonly>
                                  <font color="red" id="error_montant_restant"></font>
                                </div>
                                <div class="col-md-12">
                                  <label for=""> <?=lang('messages_lang.mont_rest_engag_budgetaire') ?> </label>
                                  <input type="number" id="montant_restant_mouvent" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_jurid">
                                  <label for=""> <?=lang('messages_lang.mont_rest_engag_jur') ?></label>
                                  <input type="number" id="montant_restant_mouvent_jurid" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_liquid">
                                  <label for=""> <?=lang('messages_lang.mont_rest_liquidation') ?> </label>
                                  <input type="number" id="montant_restant_mouvent_liq" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_ord">
                                  <label for=""> <?=lang('messages_lang.mont_rest_ord') ?></label>
                                  <input type="number" id="montant_restant_mouvent_ord" class="form-control" readonly>
                                </div>

                                <div class="col-md-12" id="rest_paie">
                                  <label for=""> <?=lang('messages_lang.mont_rest_paiemt') ?></label>
                                  <input type="number" id="montant_restant_mouvent_paie" class="form-control" readonly>
                                </div>
                                <div class="col-md-12" id="rest_decais">
                                  <label for=""> <?=lang('messages_lang.mont_rest_decais') ?> </label>
                                  <input type="number" id="montant_restant_mouvent_decais" class="form-control" readonly>
                                </div>

                              </div>
                            </div>
                          </div>
                          
                          <div class="col-md-12 mt-5 " >
                            <div class="form-group " >
                              <a onclick="savetemp();" id="btn_save"  class="btn btn-primary" style="float:right;color:white"><?=lang('messages_lang.bouton_ajouter') ?></a>
                            </div>
                          </div>
                        </div>
                      </form><br><br>


                      <div class="shadow" style="width:100%;">
                        <?php if(!empty($info_tableau)):?>

                          <form id="MyFormDatatable" action="<?=base_url('demande_new/Raccrochage_Deuxieme_Trim/enregister/')?>" method="post">
                            <input type="hidden" class="form-control" value="<?=$id?>" name="demande_id">
                            <input type="hidden" class="form-control" value="<?=$info['LIQUIDATION']?>" name="LIQUIDATION_RACC" id="LIQUIDATION_RACC" readonly>    
                            <input type="hidden" class="form-control" value="<?=$demande_exec['PTBA_ID']?>" name="PTBA_ID" id="PTBA_ID" readonly>
                            
                            <div class="table-responsive">
                              <table class="table table-striped" id="info" >
                                <thead>
                                  <th><?=lang('messages_lang.labelle_activite') ?></th>
                                  <th><?=lang('messages_lang.labelle_eng_budget') ?></th>
                                  <th><?=lang('messages_lang.labelle_eng_jud') ?></th>
                                  <th><?=lang('messages_lang.labelle_liquidation') ?></th>
                                  <th><?=lang('messages_lang.labelle_ordonan') ?></th>
                                  <th><?=lang('messages_lang.labelle_paiement') ?></th>
                                  <th><?=lang('messages_lang.labelle_decaisse') ?></th>
                                  <th><?=lang('messages_lang.label_bon') ?></th>
                                  <!-- <th>Numéro</th>
                                    <th>Date</th> -->
                                    <th><?=lang('messages_lang.labelle_observartion') ?></th>
                                    <th><?=lang('messages_lang.labelle_institution') ?></th>
                                    <th><?=lang('messages_lang.table_Programme') ?></th>
                                    <th><?=lang('messages_lang.table_Action') ?></th>
                                    <th><?=lang('messages_lang.etat_exec') ?></th>
                                    <th><?=lang('messages_lang.preuve') ?></th>
                                    <th><?=lang('messages_lang.labelle_marche_publique') ?></th>
                                    <th colspan="2"><?=lang('messages_lang.table_Action') ?></th>
                                  </thead>
                                  <tbody>
                                    <?php 
                                    $doc_racc='';$doc_preuve='';
                                    $total_budget=0;$total_jurid=0;$total_liq=0;$total_ord=0;
                                    $total_paie=0;$total_decais=0;
                                    foreach($info_tableau as $info)
                                    {
                                      if (!empty($info->DOC_RACCROCHE))
                                      {
                                        $doc_racc="<span class='fa fa-file-pdf' style='color:red;font-size: 200%;' onclick='bon_engagement(".$info->EXECUTION_ID_TEMPO.")'></span>";
                                      }else
                                      {
                                        $doc_racc='N/A';
                                      }

                                      if (!empty($info->PREUVE))
                                      {
                                        $doc_preuve="<span class='fa fa-file-pdf' style='color:red;font-size: 200%;' onclick='get_preuve(".$info->EXECUTION_ID_TEMPO.")'></span>";
                                      }else
                                      {
                                        $doc_preuve='N/A';
                                      }
                                      ?>
                                      <tr>
                                        <!-- <input type="hidden" name="<?= $info->EXECUTION_ID_TEMPO  ?>" name="id" -->
                                        <td><?= $info->ACTIVITES ?></td>

                                        <td><?= !empty(number_format($info->MONTANT_REALISE,'0',',',' ')) ? number_format($info->MONTANT_REALISE,'0',',',' ') : 0 ?></td>
                                        <td><?= !empty(number_format($info->MONTANT_REALISE_JURIDIQUE,'0',',',' ')) ? number_format($info->MONTANT_REALISE_JURIDIQUE,'0',',',' ') : 0 ?></td>
                                        <td><?= !empty(number_format($info->MONTANT_REALISE_LIQUIDATION,'0',',',' ')) ? number_format($info->MONTANT_REALISE_LIQUIDATION,'0',',',' ') : 0 ?></td>
                                        <td><?= !empty(number_format($info->MONTANT_REALISE_ORDONNANCEMENT,'0',',',' ')) ? number_format($info->MONTANT_REALISE_ORDONNANCEMENT,'0',',',' ') : 0 ?></td>
                                        <td><?= !empty(number_format($info->MONTANT_REALISE_PAIEMENT,'0',',',' ')) ? number_format($info->MONTANT_REALISE_PAIEMENT,'0',',',' ') : 0 ?></td>
                                        <td><?= !empty(number_format($info->MONTANT_REALISE_DECAISSEMENT,'0',',',' ')) ? number_format($info->MONTANT_REALISE_DECAISSEMENT,'0',',',' ') : 0 ?></td>

                                        <td><?=$doc_racc; ?></td>

                                        <td><?= $info->COMMENTAIRE ?></td>
                                        <td><?= $info->DESCRIPTION_INSTITUTION ?></td>
                                        <td><?= $info->INTITULE_PROGRAMME ?></td>
                                        <td><?= $info->LIBELLE_ACTION ?></td>
                                        <td><?= $info->DESC_MOUVEMENT_DEPENSE ?></td>
                                        <td><?=$doc_preuve; ?></td>

                                        <div>
                                          <?php if($info->MARCHE_PUBLIQUE){?>
                                            <td>Oui</td>
                                          <?php }else{ ?>
                                            <td>Non</td>
                                          <?php } ?>
                                        </div>

                                        <td><a  href="<?php echo base_url('demande_new/Raccrochage_Deuxieme_Trim/modifier/'.$info->EXECUTION_ID_TEMPO.'/'.$id )?>" class="btn btn-danger"><i class="fa fa-edit "></i></a></td>

                                        <td><a  href="<?php echo base_url('demande_new/Raccrochage_Deuxieme_Trim/deleteData/'.$info->EXECUTION_ID_TEMPO.'/'.$id )?>" class="btn btn-danger"><i class="fa fa-close "></i></a></td>


                                      </tr>

                                          <?php 
                                          $total_budget +=$info->MONTANT_REALISE;
                                          $total_jurid +=$info->MONTANT_REALISE_JURIDIQUE;
                                          $total_liq +=$info->MONTANT_REALISE_LIQUIDATION;
                                          $total_ord +=$info->MONTANT_REALISE_ORDONNANCEMENT;
                                          $total_paie +=$info->MONTANT_REALISE_PAIEMENT;
                                          $total_decais +=$info->MONTANT_REALISE_DECAISSEMENT;
                                        } ?>

                                      </tbody>
                                      <tfoot>
                                        <th>Total</th>
                                        <th><?=number_format($total_budget,'0',',',' ') ?></th>
                                        <th><?=number_format($total_jurid,'0',',',' ') ?></th>
                                        <th><?=number_format($total_liq,'0',',',' ') ?></th>
                                        <th><?=number_format($total_ord,'0',',',' ') ?></th>
                                        <th><?=number_format($total_paie,'0',',',' ') ?></th>
                                        <th><?=number_format($total_decais,'0',',',' ') ?></th>
                                      </tfoot>
                                    </table>
                                  </div>
                                  <div class="col-12 mt-4"style="margin-bottom:100px" id="btn_save1">
                                    <a onclick="save()" style="float: right;margin: 4px;color:white" class="btn btn-primary"><i class="fa fa-sign-in" aria-hidden="true"></i> <?=lang('messages_lang.bouton_enregistrer') ?> </a>

                                  </div>
                                </form>
                                <div class="col-12 mt-4"style="margin-bottom:100px;display:none;" id="btn_save_depas">
                                  
                                  <div class="col-12 mt-4"style="margin-bottom:100px">
                                  <?php endif  ?>
                                  </div>

                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </main>
                </div>
              </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="doc_mvt_depense"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <embed id="doc_bon" scrolling="auto" height="500px" width="100%" frameborder="0">
                  </div>
                  <div class="modal-footer">
                    <button class='btn btn-primary btn-md' data-dismiss='modal'> <?=lang('messages_lang.quiter_action')?> </button>
                  </div>
                </div>
              </div>
            </div>

            <div class='modal fade' id='preuve_modal'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <div class="modal-header">
                    <center><h5 id="preuve_title"></h5></center>
                  </div>
                  <div class='modal-body'>
                    <center>
                      <embed id="embed_src" scrolling="auto" height="500px" width="100%" frameborder="0">
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

              <?php echo view('includesbackend/scripts_js.php'); ?>
            </body>
            </html>
        <script>
          function bon_engagement(id)
          {
            $.ajax(
            {
              url:"<?=base_url()?>/demande_new/Raccrochage_Deuxieme_Trim/get_path_bon/"+id,
              type:"GET",
              dataType:"JSON",
              success: function(data)
              {   
                if (data.MOUVEMENT_DEPENSE_ID==5 || data.MOUVEMENT_DEPENSE_ID==7)
                {
                  $('#doc_mvt_depense').text('<?=lang('messages_lang.titre_decaissement')?>');
                }else{
                  $('#doc_mvt_depense').text(data.DESC_MOUVEMENT_DEPENSE);
                }
                $('#exampleModal').modal('show');
                const embed = document.getElementById('doc_bon');
                embed.setAttribute("src", "<?= base_url('uploads/doc_raccroches') ?>/"+data.DOC_RACCROCHE);
              }
            });
          }
        </script>

        <script type="text/javascript">
          function get_preuve(id)
          {
            $.ajax(
            {
              url:"<?=base_url()?>/demande_new/Raccrochage_Deuxieme_Trim/get_path_preuve/"+id,
              type:"GET",
              dataType:"JSON",
              success: function(data)
              {  
                $('#preuve_title').text('<?=lang('messages_lang.preuve')?>'); 

                $('#preuve_modal').modal('show');
                const embed_p = document.getElementById('embed_src');
                embed_p.setAttribute("src", "<?= base_url('uploads/doc_preuves') ?>/"+data.PREUVE);
                
              }
            });
          }
        </script>

<script type="text/javascript">
  function format_montant()
  {
    var LIQUIDATION=$('#LIQUIDATION').val()

    var originalString = LIQUIDATION;
    var newString = originalString.replace(/\D/g, "");

    var value = (newString).toLocaleString(undefined,{ minimumFractionDigits: 0 });
    $('#LIQUIDATION').val(value)

  }
</script>

<script>
// controle de l'extension et de la taille de la preuve 
function Valid_preuve(id)
{
  if (id==2) {
    var fileInput = document.getElementById('PREUVE');
    var filePath = fileInput.value;
// Allowing file type
    var allowedExtensions = /(\.pdf)$/i;
    
    if (!allowedExtensions.exec(filePath))
    {
     $('#preuve_error').text("<?=lang('messages_lang.bordereau_message') ?>");
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
          $('#preuve_error').text('<?=lang('messages_lang.taille_bordereau_message') ?>');
          fileInput.value = '';
        } else{
         $('#preuve_error').text(''); 
       }
     } 
   }
 }
}else if (id==1) {
var fileInput = document.getElementById('doc_raccroche');
var filePath = fileInput.value;
// Allowing file type
var allowedExtensions = /(\.pdf)$/i;

if (!allowedExtensions.exec(filePath))
{
 $('#doc_error').text("<?=lang('messages_lang.bordereau_message') ?>");
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
      $('#doc_error').text('<?=lang('messages_lang.taille_bordereau_message') ?>');
      fileInput.value = '';
    } else{
     $('#doc_error').text(''); 
   }
 } 
}
}
}

}
</script>

  <script>
    // controle de l'extension et de la taille du bon d'engagement 
      function Valid_bon_engag()
      {
        var bon_engag = document.getElementById('doc_raccroche');
        var filePath = bon_engag.value;
      // Allowing file type

        var allowedExtensions = /(\.pdf)$/i;
        
        if (!allowedExtensions.exec(filePath))
        {
         $('#doc_error').text("<?=lang('messages_lang.bordereau_message') ?>");
         bon_engag.value = '';
         return false;
       }else
       {
        // Check if any file is selected. 
        if (bon_engag.files.length > 0)
        {
          for (const i = 0; i <= bon_engag.files.length - 1; i++)
          { 
            const fsize = bon_engag.files.item(i).size; 
            const file = Math.round((fsize1 / 1024)); 
            // The size of the file. 
            if (file > 200)
            {
              $('#doc_error').text('<?=lang('messages_lang.taille_bordereau_message') ?>');
              bon_engag.value = '';
            } else{
             $('#doc_error').text(''); 
           }
         } 
       }
     }
   }
 </script>

 

<script>
  $(document).ready(function ()
  {

    change_label();
    
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

    document.getElementById('mont').readOnly = true;

    

    var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();
    if(MOUVEMENT_DEPENSE_ID==''){
      $('#montant_realise_id').attr('disabled',true)
    }


  });
</script> 

<script>
        /**fonction pour activer la zone a sasit le montant realisé */
  function active_mouvement()
  {
    var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();
    if(MOUVEMENT_DEPENSE_ID=='')
    {
      $('#montant_realise_id').attr('disabled',true)
    }else
    {
      $('#montant_realise_id').attr('disabled',false)
    }

  }
</script>

<script>
      /**fonction pour recuper les montqnt selon id ptba */
  function get_mont()
  {
    statut=true;
    var PTBA_ID=$('#PTBA_ID').val();
    $.ajax(
    {
      url:"<?=base_url()?>/demande_new/Raccrochage_Deuxieme_Trim/get_montant/"+PTBA_ID,
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
      !/[0-9]/.test(event.key) &&
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
    var statut = true;

        //debut récupération des données du formulaire
    var PTBA_ID= $('#PTBA_ID').val();
    var montant_realise_id  = $('#montant_realise_id').val();
    var montant_vote_id  = $('#montant_vote_id').val();
    var doc_raccroche  = $('#doc_raccroche').val();
    var Mouvement_id  = $('#Mouvement_id').val();
    var MARCHE_PUBLIC  = $('#MARCHE_PUBLIC').val();
    var PREUVE  = $('#PREUVE').val();
    var profil  = $('#profil').val();
    var COMMENTAIRE = $('#COMMENTAIRE').val();
    var LIQUIDATION  = $('#LIQUIDATION').val();
    var numero_bon_id  = $('#numero_bon_id').val();
    var date_bon_id  = $('#date_bon_id').val();
    var numero_decaiss_id  = $('#numero_decaiss_id').val();
    var date_decais_id  = $('#date_decais_id').val();


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
        //fin récupération des données du formulaire

        // debut somme sur chaque etat d'execution
    var mont_realise_sommation = $('#mont_realise_sommation').val();
    var mont_realise_sommation_jurd = $('#mont_realise_sommation_jurd').val();
    var mont_realise_sommation_liq = $('#mont_realise_sommation_liq').val();
    var mont_realise_sommation_ord = $('#mont_realise_sommation_ord').val();
    var mont_realise_sommation_paie = $('#mont_realise_sommation_paie').val();
    var mont_realise_sommation_decais = $('#mont_realise_sommation_decais').val();

    if (mont_realise_sommation=='')
    {
      mont_realise_sommation=0;
    }

    if (mont_realise_sommation_jurd=='')
    {
      mont_realise_sommation_jurd=0;
    }

    if (mont_realise_sommation_liq=='')
    {
      mont_realise_sommation_liq=0;
    }

    if (mont_realise_sommation_ord=='')
    {
      mont_realise_sommation_ord=0;
    }

    if (mont_realise_sommation_paie=='')
    {
      mont_realise_sommation_paie=0;
    }

    if (mont_realise_sommation_decais=='')
    {
      mont_realise_sommation_decais=0;
    }
        // fin somme sur chaque etat d'execution
    var montant_restant_actite=$('#montant_restant_actite').val();
    if(montant_restant_actite=='')
    {
      montant_restant_actite=0;
    }
    var mount= parseInt(montant_realise_id) > parseInt(LIQUIDATION)
    var mountvot= 0;

    var mountvotjurid= 0;
    var mountvotliq= 0;
    var mountvotord= 0;
    var mountvotpaie= 0;
    var mountvotdecais=0;

    // Debut calcul des montants restants par rapport aux activites deja ajoutes par chaque etat d'execution
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

    var result_by_Mouvement1= parseInt(MONTANT_ENGAGE)- parseInt(mont_realise_sommation)
    var result_by_Mouvement2= parseInt(MONTANT_JURDIQUE)- parseInt(mont_realise_sommation_jurd)
    var result_by_Mouvement3= parseInt(MONTANT_PAIEMENT)- parseInt(mont_realise_sommation_paie)
    var result_by_Mouvement4= parseInt(MONTANT_DECAISSEMENT)- parseInt(mont_realise_sommation_decais)
    var result_by_Mouvement5= parseInt(MONTANT_ORDONANCEMENT)- parseInt(mont_realise_sommation_ord)
    var result_by_Mouvement6= parseInt(LIQUIDATION)- parseInt(mont_realise_sommation_liq)
        // Fin calcul des montants restants par rapport aux activites deja ajoutes par chaque etat d'execution
    
        // Debut calcul des montants qui reste en cours de saisi par chaque etat d'execution
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
        // Debut calcul des montants qui reste en cours de saisi par chaque etat d'execution

        // Debut gestion des transferts par activite
    var restant_act=0;
    var mountvot=0;
    var mountvotjurid=0;
    var mountvotliq=0;
    var mountvotord=0;
    var mountvotpaie=0;
    var mountvotdecais=0;

    $('#new_montant_error').html("")

      restant_act=parseInt(montant_vote_id)-parseInt(montant_etap_exec);
      mountvot= parseInt(montant_realise_id) > parseInt(montant_vote_id);
      mountvotjurid= parseInt(montant_realise_jurid_id) > parseInt(montant_vote_id);
      mountvotliq= parseInt(montant_realise_liq_id) > parseInt(montant_vote_id);
      mountvotord= parseInt(montant_realise_ord_id) > parseInt(montant_vote_id);
      mountvotpaie= parseInt(montant_realise_paie_id) > parseInt(montant_vote_id);
      mountvotdecais= parseInt(montant_realise_decais_id) > parseInt(montant_vote_id);

      if(mountvot || mountvotjurid || mountvotliq || mountvotord || mountvotpaie || mountvotdecais)
      {
        statut1 = false;
        $('#montant_error').html("<?=lang('messages_lang.msg_montant_vote') ?>");
      }else
      {
        statut1 = true;
        $('#montant_error').html("")
      }
    

    $('#montant_restant').val(restant_act);

    var montant_restant_actite=$('#montant_restant_actite').val();
    var restant_activite=0;
    var montant_etap_exec=0;
    if (Mouvement_id==1)
    {
      restant_activite=montant_restant_actite - montant_realise_id;
      montant_etap_exec=montant_realise_id;
    }else if (Mouvement_id==2)
    {
      restant_activite=montant_restant_actite - montant_realise_jurid_id;
      montant_etap_exec=montant_realise_jurid_id;
    }else if (Mouvement_id==3)
    {
      restant_activite=montant_restant_actite - montant_realise_liq_id;
      montant_etap_exec=montant_realise_liq_id;
    }else if (Mouvement_id==4)
    {
      restant_activite=montant_restant_actite - montant_realise_ord_id;
      montant_etap_exec=montant_realise_ord_id;
    }else if (Mouvement_id==5)
    {
      restant_activite=montant_restant_actite - montant_realise_decais_id;
      montant_etap_exec=montant_realise_decais_id;
    }else if (Mouvement_id==7)
    {
      restant_activite=montant_restant_actite - montant_realise_paie_id;
      montant_etap_exec=montant_realise_paie_id;
    }

    date_engagement = new Date(date_bon_id);
    date_decaissement = new Date(date_decais_id);
    var currentDate = new Date();

    $('#montant_errorreal').html('');
    $('#error_PTBA_ID').html('');

    if (Mouvement_id==1 || Mouvement_id==2 || Mouvement_id==3 || Mouvement_id==4)
    {
      if(numero_bon_id=='')
      {
        $('#error_num_bon').html('<?=lang('messages_lang.error_sms') ?>');
        statut = false;
      }else
      {
        $('#error_num_bon').html('');
      }
      if(date_bon_id=='')
      {
        $('#error_date_bon').html('<?=lang('messages_lang.error_sms') ?>');
        statut = false;
      }else
      {
        $('#error_date_bon').html('');
      }
      if (date_engagement < currentDate)
      { 
        statut=true;
        $('#error_date_bon').html("");
      }else
      {
        statut=false;
        $('#error_date_bon').html("<?=lang('messages_lang.date_non_sup_today') ?>");
      }

    }else if (Mouvement_id==5 || Mouvement_id==7)
    {
      if(numero_decaiss_id=='')
      {
        $('#error_num_decaissement').html('<?=lang('messages_lang.error_sms') ?>');
        statut = false;
      }else
      {
        $('#error_num_decaissement').html('');
      }
      if(date_decais_id=='')
      {
        $('#error_date_decaissement').html('<?=lang('messages_lang.error_sms') ?>');
        statut = false;
      }else
      {
        $('#error_date_decaissement').html('');
      }

      if (date_decaissement <= currentDate)
      {
        statut=true;
        $('#error_date_decaissement').html("");
      }else
      {
        statut=false;
        $('#error_date_decaissement').html("<?=lang('messages_lang.date_non_sup_today') ?>");
      }

    }

    if(MARCHE_PUBLIC=='')
    {
      $('#error_marche').html('<?=lang('messages_lang.error_sms') ?>');
      statut = false;
    }else
    {
      $('#error_marche').html('');
    }
    if(montant_realise_id=='')
    {
      $('#montant_errorreal').html('<?=lang('messages_lang.error_sms') ?>');
      statut = false;
    }else
    {
      $('#montant_errorreal').html('');

    }
    if(Mouvement_id=='')
    {
      $('#error_Mouvement').html('<?=lang('messages_lang.error_sms') ?>');
      statut = false;
    }else
    {
      $('#error_Mouvement').html('');
    }

    if(PTBA_ID=='')
    {
      $('#error_PTBA_ID').html('<?=lang('messages_lang.error_sms') ?>');
      statut = false;
    }else
    {
      $('#error_PTBA_ID').html('');

    }



      //debut gestion montant restant par ligne budgetaire
    var total_vote_ligne=$('#total_vote_ligne').val();
    var total_ligne=$('#total_ligne').val();

    var new_montant_ligne=$('#new_montant_ligne').val()
    
    var restant=0;
    restant=parseInt(total_vote_ligne) - parseInt(total_ligne);

    $('#montant_restant').val(restant);

    var rest_ligne=restant-montant_etap_exec;
    $('#montant_restant').val(rest_ligne);

      //fin

      if(statut == true)
      {
        if (statut1 == true)
        {
          $('#MyFormData').submit();
          $('#btn_save').hide()
        }
      }
  }
</script>


<script>
      /**afichhager des labels selon le mouvent selectionner */
  function change_label()
  {        
    var mouvement= $('#Mouvement_id').val();
    if(mouvement==1){
      $('#identifiant').html("<?=lang('messages_lang.label_bon') ?>");
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
    $('#identifiant').html("<?=lang('messages_lang.label_bon') ?>");

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
    $('#identifiant').html("<?=lang('messages_lang.label_bon') ?>");

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
    $('#identifiant').html("<?=lang('messages_lang.label_bon') ?>");

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
    $('#identifiant').html("<?=lang('messages_lang.labelle_titre_dec') ?>");

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
    $('#identifiant').html("<?=lang('messages_lang.labelle_titre_dec') ?>");

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
  }else if (mouvement=='')
  {
    $('#num_bon_eng').hide();
    $('#date_bon_eng').hide();
    $('#titre_decaiss').hide();
    $('#date_titre').hide();
    $('#identifiant').html("<?=lang('messages_lang.label_bon') ?>");

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

}
</script>

<script>
        /**validation des montants selon le montant realise saisit */
  function valide_montant()
  {
    statut = true;
    var montant_vote_id  = $('#montant_vote_id').val();
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

    if (mont_realise_sommation=='')
    {
      mont_realise_sommation=0;
    }

    if (mont_realise_sommation_jurd=='')
    {
      mont_realise_sommation_jurd=0;
    }

    if (mont_realise_sommation_liq=='')
    {
      mont_realise_sommation_liq=0;
    }

    if (mont_realise_sommation_ord=='')
    {
      mont_realise_sommation_ord=0;
    }

    if (mont_realise_sommation_paie=='')
    {
      mont_realise_sommation_paie=0;
    }

    if (mont_realise_sommation_decais=='')
    {
      mont_realise_sommation_decais=0;
    }
    // fin somme sur chaque etat d'execution

    
    var montant_restant_actite=$('#montant_restant_actite').val();
    if(montant_restant_actite=='')
    {
      montant_restant_actite=0;
    }

    // Debut verification des montants saisies par etat d'execution
    var verifmont_engament= parseInt(montant_realise_id) > parseInt(MONTANT_ENGAGE)
    var verifmont_jurdique= parseInt(montant_realise_jurid_id) > parseInt(MONTANT_JURDIQUE)
    var mount= parseInt(montant_realise_liq_id)> parseInt(LIQUIDATION)
    var verifmont_ordonancement= parseInt(montant_realise_ord_id) > parseInt(MONTANT_ORDONANCEMENT)
    var verifmont_paiement= parseInt(montant_realise_paie_id) > parseInt(MONTANT_PAIEMENT)
    var verifmont_decaissement= parseInt(montant_realise_decais_id) > parseInt(MONTANT_DECAISSEMENT)
    // Fin verification des montants saisies par etat d'execution

    // Debut calcul des montants restants par rapport aux activites deja ajoutes par chaque etat d'execution
    var result_by_Mouvement1= parseInt(MONTANT_ENGAGE)- parseInt(mont_realise_sommation)
    var result_by_Mouvement2= parseInt(MONTANT_JURDIQUE)- parseInt(mont_realise_sommation_jurd)
    var result_by_Mouvement3= parseInt(MONTANT_PAIEMENT)- parseInt(mont_realise_sommation_paie)
    var result_by_Mouvement4= parseInt(MONTANT_DECAISSEMENT)- parseInt(mont_realise_sommation_decais)
    var result_by_Mouvement5= parseInt(MONTANT_ORDONANCEMENT)- parseInt(mont_realise_sommation_ord)
    var result_by_Mouvement6= parseInt(LIQUIDATION)- parseInt(mont_realise_sommation_liq)
    // Fin calcul des montants restants par rapport aux activites deja ajoutes par chaque etat d'execution
    
    // Debut calcul des montants qui reste en cours de saisi par chaque etat d'execution
    var reste_budget=parseInt(result_by_Mouvement1)-parseInt(montant_realise_id)
    var reste_juridique=parseInt(result_by_Mouvement2)-parseInt(montant_realise_jurid_id)
    var reste_liquidation=parseInt(result_by_Mouvement6)-parseInt(montant_realise_liq_id)
    var reste_ordon=parseInt(result_by_Mouvement5)-parseInt(montant_realise_ord_id)
    var reste_paie=parseInt(result_by_Mouvement3)-parseInt(montant_realise_paie_id)
    var reste_decais=parseInt(result_by_Mouvement4)-parseInt(montant_realise_decais_id)

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

    // Debut calcul des montants qui reste en cours de saisi par chaque etat d'execution

    var restant_activite=0;
    var montant_etap_exec=0;
    if(MOUVEMENT_DEPENSE_ID==1)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_id);
      montant_etap_exec=parseInt(montant_realise_id);
    }
    else if (MOUVEMENT_DEPENSE_ID==2)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_jurid_id);
      montant_etap_exec=parseInt(montant_realise_jurid_id);
    }
    else if (MOUVEMENT_DEPENSE_ID==3)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_liq_id);
      montant_etap_exec=parseInt(montant_realise_liq_id);
    }
    else if (MOUVEMENT_DEPENSE_ID==4)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_ord_id);
      montant_etap_exec=parseInt(montant_realise_ord_id);
    }
    else if (MOUVEMENT_DEPENSE_ID==5)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_decais_id);
      montant_etap_exec=parseInt(montant_realise_decais_id);
    }
    else if (MOUVEMENT_DEPENSE_ID==7)
    {
      restant_activite=parseInt(montant_restant_actite) - parseInt(montant_realise_paie_id);
      montant_etap_exec=parseInt(montant_realise_paie_id);
    }

    if(montant_etap_exec=='')
    {
      montant_etap_exec=0;
    }

    // Debut gestion des transferts par activite
    var restant_act=0;
    var mountvot=0;
    var mountvotjurid=0;
    var mountvotliq=0;
    var mountvotord=0;
    var mountvotpaie=0;
    var mountvotdecais=0;

    
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
        $('#montant_error').html("<?=lang('messages_lang.msg_montant_vote') ?>")
      }
      else
      {
        $('#montant_error').html("")
      }
    
    // Fin gestion des transferts par activite

    $('#montant_restant_actite').val(restant_act);
    //debut gestion montant restant par ligne budgetaire
    var total_vote_ligne=$('#total_vote_ligne').val();
    var total_ligne=$('#total_ligne').val();
    var new_montant_ligne=$('#new_montant_ligne').val()

    var restant=0;
    
    restant=parseInt(total_vote_ligne) - parseInt(total_ligne);
    
    
    //Debut gestion montant restant par ligne budgetaire
    if (restant<0)
    {
      $('#montant_restant').val(zero);
    }else{
      $('#montant_restant').val(restant);
    }
    

    var rest_ligne=parseInt(restant)-montant_etap_exec;

    if (rest_ligne<0)
    {
      $('#montant_restant').val(zero);
    }else{
      $('#montant_restant').val(rest_ligne);
    }
    

    var montant_restant_id = $('#montant_restant').val();
    if(montant_restant_id <0 )
    {
      $('#error_montant_restant').html('<?=lang('messages_lang.mont_depasse_ligne') ?>');
      statut = false;
    }
    else
    {
      $('#error_montant_restant').html('');
    }
    //Fin gestion montant restant par ligne budgetaire

    $('#montant_errorreal').html('');
    $('#montant_error_mont').html('');
    if (parseInt(montant_realise_id) > result_by_Mouvement1)
    {
      statut = false;
      $('#montant_error_mont').html("<?=lang('messages_lang.mont_depasse_engag_budg') ?>")
      $('#btn_save').hide()
    }
    else
    {
      if(verifmont_engament)
      {
        statut = false;
        $('#montant_error_mont').html("<?=lang('messages_lang.mont_depasse_engag_budg') ?>")
        $('#btn_save').hide()
      }else{
        $('#btn_save').show()
      }
    }

    $('#montant_error_mont_jurd').html('');
    if (parseInt(montant_realise_jurid_id) > result_by_Mouvement2)
    {
      $('#montant_error_mont_jurd').html("<?=lang('messages_lang.mont_depasse_engag_jur') ?> ")
      $('#btn_save').hide()
    }
    else
    {
      if(verifmont_jurdique)
      {
        statut = false;
        $('#montant_error_mont_jurd').html("<?=lang('messages_lang.mont_depasse_engag_jur') ?>")
        $('#btn_save').hide()
      }else{
        $('#btn_save').show()
      }
    }

    $('#montant_error_mont_liq').html('');
    if (parseInt(montant_realise_liq_id) > result_by_Mouvement6)
    {
      statut = false;
      $('#montant_error_mont_liq').html("<?=lang('messages_lang.mont_depasse_liquidation') ?>")
      $('#btn_save').hide()
    }
    else
    {
      if(mount)
      {
        statut = false;
        $('#montant_error_mont_liq').html("<?=lang('messages_lang.mont_depasse_liquidation') ?>")
        $('#btn_save').hide()
      }else{
        $('#btn_save').show()
      }

      if(mount==0)
      {
        $('#montant_error_mont_liq').html('');
      }
    }

    $('#montant_error_mont_ord').html('');
    if (parseInt(montant_realise_ord_id) > result_by_Mouvement5)
    {
      statut = false;
      $('#montant_error_mont_ord').html("<?=lang('messages_lang.mont_depasse_ord') ?>");
      $('#btn_save').hide()
    }
    else
    {
      if(verifmont_ordonancement)
      {
        statut = false;
        $('#montant_error_mont_ord').html("<?=lang('messages_lang.mont_depasse_ord') ?>")
        $('#btn_save').hide()
      }else{
        $('#btn_save').show()
      }

      if(verifmont_ordonancement==0)
      {
        $('#montant_error_mont_ord').html('');
      }
    }

    $('#montant_error_mont_paie').html('');
    if (parseInt(montant_realise_paie_id) > result_by_Mouvement3)
    {
      statut = false;
      $('#montant_error_mont_paie').html("<?=lang('messages_lang.mont_depasse_paiement') ?> ")
      $('#btn_save').hide()
    }
    else
    {
      if(verifmont_paiement)
      {
        statut = false;
        $('#montant_error_mont_paie').html("<?=lang('messages_lang.mont_depasse_paiement') ?> ")
        $('#btn_save').hide()
      }else{
        $('#btn_save').hide()
      }

      if(verifmont_paiement==0)
      {
        $('#montant_error_mont_paie').html('');
      }
    }

    $('#montant_error_mont_decais').html('');
    if (parseInt(montant_realise_decais_id) > result_by_Mouvement4)
    {
      statut = false;
      $('#montant_error_mont_decais').html("<?=lang('messages_lang.mont_depasse_decais') ?> ")
      $('#btn_save').hide()
    }
    else
    {
      if(verifmont_decaissement)
      {
        statut = false;
        $('#montant_error_mont_decais').html("<?=lang('messages_lang.mont_depasse_decais') ?> ")
        $('#btn_save').hide()
      }else{
        $('#btn_save').show()
      }

      if(verifmont_decaissement==0)
      {
        $('#montant_error_mont_decais').html('');
      }
    }
  }
</script>


<script>
  function modal()
  {
    $('#confirm').modal('show');
  }

  function fermer_modal()
  {
    $('#confirm').modal('hide');
  }
</script>



