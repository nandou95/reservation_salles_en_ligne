<!DOCTYPE html>
<html lang="en">
  <head>
   <?php echo view('includesbackend/header.php');?>
   <?php $validation = \Config\Services::validation(); ?>
  </head>
  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php');?>
      <div class="main">
        <?php echo view('includesbackend/navybar_topbar.php');?>
        <main class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div class="card-body">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-9" style="float: left;">
                          <h1 class="header-title text-dark">
                            <?=$etape_descr['DESC_ETAPE_DOUBLE_COMMANDE']?>
                          </h1>
                        </div>
                        <div class="col-3" style="float: right;">
                          <a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.link_list')?></a> 
                        </div>
                        <div class="col-3" style="float: left;">
                          <div id="accordion">
                            <div class="card-header" id="headingThree">
                              <h5 class="mb-0">
                                <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?=lang('messages_lang.histo_btn')?>
                                </button>
                              </h5>
                            </div>  
                          </div>
                        </div>
                      </div>

                      <div class="container" style="width:90%">
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                          <?php include  'includes/Detail_View.php'; ?>
                        </div>
                      </div>

                      <form enctype='multipart/form-data' id="my_form" action="<?= base_url('double_commande_new/Liquidation/update') ?>" method="POST">
                        <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                          <div class="row" style="margin :  5px">
                            <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                          
                            <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" id="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">

                            <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" id="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">

                            <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">


                            <input type="hidden" name="MONTANT_RACCROCHE_JURIDIQUE_VALUE" id="MONTANT_RACCROCHE_JURIDIQUE_VALUE" value="<?=!empty($info['MONTANT_RACCROCHE_JURIDIQUE'])?$info['MONTANT_RACCROCHE_JURIDIQUE']:'0'?>">


                            <input type="hidden" name="MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE" id="MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE" value="<?=$info['MONTANT_RACCROCHE_JURIDIQUE_DEVISE']?>">

                            <input hidden="" type="date" name="DATE_DEBUT_CONTRAT" id="DATE_DEBUT_CONTRAT" value="<?=$info['DATE_DEBUT_CONTRAT']?>">

                            <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$info['MARCHE_PUBLIQUE']?>">
                            <input type="hidden" name="BUDGETAIRE_TYPE_DOCUMENT_ID" id="BUDGETAIRE_TYPE_DOCUMENT_ID" value="<?=$info['BUDGETAIRE_TYPE_DOCUMENT_ID']?>">

                            <div class="col-6">
                              <label><b><?=lang('messages_lang.label_motif_dec')?><hr></b></label>
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
                              <label><b><?=lang('messages_lang.label_observ')?><hr></b></label><p><?=!empty($data_correction['OBSERVATION']) ? $data_correction['OBSERVATION'] : '-'?></p><br>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.label_type_liquidation')?><span style="color: red;">*</span></label>
                              <select onchange="getTypeLiquidationMontant()" class="form-control" name="ID_TYPE_LIQUIDATION" id="ID_TYPE_LIQUIDATION">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php
                                foreach ($get_type_liquidation as $key)
                                {
                                  if ($key->ID_TYPE_LIQUIDATION==set_value("ID_TYPE_LIQUIDATION")) 
                                  {
                                    echo '<option selected value="'.$key->ID_TYPE_LIQUIDATION.'">'.$key->DESCRIPTION_LIQUIDATION.'</option>';
                                  }
                                  elseif($key->ID_TYPE_LIQUIDATION==$info["ID_TYPE_LIQUIDATION"])
                                  {
                                    echo '<option value="'.$key->ID_TYPE_LIQUIDATION.'" selected>'.$key->DESCRIPTION_LIQUIDATION.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'.$key->ID_TYPE_LIQUIDATION.'">'.$key->DESCRIPTION_LIQUIDATION.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <font  color="red" id="error_ID_TYPE_LIQUIDATION"><?=$validation->getError('ID_TYPE_LIQUIDATION');?></font>
                            </div>

                            <div class="col-6">
                              <label for=""><?=lang('messages_lang.labelle_date_reception_demande')?>(CED)<span style="color: red;">*</span></label>
                              <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_transRecept(this.value)" value="<?=set_value("DATE_RECEPTION")?>">
                              <font color="red" id="error_DATE_RECEPTION"><?=$validation->getError('DATE_RECEPTION');?></font>
                            </div>

                            <div id="DIV_TITRE_CREANCE" class="col-6">
                              <label><?=lang('messages_lang.label_number_titre_creance')?><span style="color: red;">*</span></label>
                              <input onkeyup="SetMaxLength(1)" autocomplete="off" value="<?=!empty(set_value("TITRE_CREANCE"))?set_value("TITRE_CREANCE"):$info['TITRE_CREANCE']?>" type="text" name="TITRE_CREANCE" id="TITRE_CREANCE" class="form-control" >
                              <font color="red" id="error_TITRE_CREANCE"><?=$validation->getError('TITRE_CREANCE');?></font>
                              <span style="font-size: 10px;color: green" id="getNumberTITRE_CREANCE"></span>
                            </div>

                            <div id="DIV_DATE_CREANCE" class="col-6">
                              <label><?=lang('messages_lang.label_date_titre_creance')?><span style="color: red;">*</span></label>
                              <input type="date" value="<?=date('Y-m-d',strtotime($info['DATE_CREANCE']))?>" max="<?=date('Y-m-d')?>" name="DATE_CREANCE" id="DATE_CREANCE" class="form-control">
                              <font color="red" id="error_DATE_CREANCE"><?=$validation->getError('DATE_CREANCE');?></font>
                            </div>

                            <div id="div_creance" class="col-6">
                              <label><?=lang('messages_lang.label_montant_titre_creance')?><span style="color: red;">*</span></label>
                              <input value="<?=!empty($info['MONTANT_CREANCE']) ? $info['MONTANT_CREANCE'] : ''?>" onkeyup="getSubstring(1);SetMaxLength(3);getCalculMontant()" autocomplete="off" type="text" name="MONTANT_CREANCE" id="MONTANT_CREANCE" class="form-control" >
                              <font color="red" id="error_MONTANT_CREANCE"><?=$validation->getError('MONTANT_CREANCE');?></font>
                              <font color="red" id="error_MONTANT_CREANCE_SUP"></font>
                              <span style="font-size: 10px;color: green" id="getNumberMONTANT_CREANCE"></span>
                            </div>

                            <div id="DIV_DATE_LIQUIDATION" class="col-6">
                              <label><?=lang('messages_lang.table_ate_liq')?><span style="color: red;">*</span></label>
                              <input onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" value="<?=date('Y-m-d',strtotime($info['DATE_LIQUIDATION']))?>" type="date" min="<?=date('Y-m-d',strtotime((!empty($info['DATE_ENG_BUDGETAIRE'])) ? $info['DATE_ENG_JURIDIQUE'] : ''))?>" max="<?=date('Y-m-d')?>" name="DATE_LIQUIDATION" id="DATE_LIQUIDATION" class="form-control" >
                              <font color="red" id="error_DATE_LIQUIDATION"><?=$validation->getError('DATE_LIQUIDATION');?></font>
                            </div>

                            <div class="col-12">
                              <label><?=lang('messages_lang.table_moif')?><span style="color: red;">*</span></label>
                              <textarea name="MOTIF_LIQUIDATION" id="MOTIF_LIQUIDATION" class="form-control"><?=$info['MOTIF_LIQUIDATION']?></textarea>
                              <font color="red" id="error_MOTIF_LIQUIDATION"><?=$validation->getError('MOTIF_LIQUIDATION');?></font>
                            </div>          

                            <input type="hidden" value="<?=$TYPE_MONTANT_ID?>" name="TYPE_MONTANT_ID" id="TYPE_MONTANT_ID">

                            <div id="div_devise1" class="col-6">
                              <label><?=lang('messages_lang.label_montant_devise')?><span style="color: red;">*</span></label>
                              <input value="<?=!empty($info['MONTANT_RACCROCHE_LIQUIDATION_DEVISE']) ? $info['MONTANT_RACCROCHE_LIQUIDATION_DEVISE'] : ''?>" onkeyup="getSubstring(3);SetMaxLength(4);getCalculMontant()" autocomplete="off" type="text" name="MONTANT_DEVISE" id="MONTANT_DEVISE" class="form-control">
                              <font color="red" id="error_MONTANT_DEVISE"><?=$validation->getError('MONTANT_DEVISE');?></font>
                              <font color="red" id="error_MONTANT_DEVISE_SUP"></font>
                              <span style="font-size: 10px;color: green" id="getNumberMONTANT_DEVISE"></span>
                            </div>

                            <div id="div_devise2" class="col-6">
                              <label for=""><?= lang('messages_lang.label_echange') ?><font color="red">*</font> </label>
                              <input type="text" onkeyup="getCalculMontant()" oninput="formatInputValue(this);" name="COUT_DEVISE" id="COUT_DEVISE" class="form-control" value="<?=$cour_devise?>">     
                              <font color="red" id="error_COUT_DEVISE"><?=$validation->getError('COUT_DEVISE');?></font>                     
                            </div>

                            <div class="col-md-6" id="date_dev"  hidden="true">
                              <label for=""><?= lang('messages_lang.label_date_cours') ?><font color="red">*</font></label>
                              <input type="date" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_COUT_DEVISE" id="DATE_COUT_DEVISE">
                              <font color="red" id="error_DATE_COUT_DEVISE"><?=$validation->getError('DATE_COUT_DEVISE');?></font>
                            </div>

                            <div id="div_devise3" class="col-6">
                              <label><?=lang('messages_lang.label_montant_titre_creance')?><span style="color: red;">*</span></label>
                              <input value="<?=!empty($info['MONTANT_RACCROCHE_LIQUIDATION']) ? $info['MONTANT_RACCROCHE_LIQUIDATION'] : ''?>" autocomplete="off" type="text" name="LIQUIDATION" id="LIQUIDATION" class="form-control" >
                              <font color="red" id="error_LIQUIDATION"><?=$validation->getError('LIQUIDATION');?></font>
                              <span style="font-size: 10px;color: green" id="getNumberLIQUIDATION"></span>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.label_taux_tva')?><span style="color: red;">*</span></label>
                              <select class="form-control" name="TAUX_TVA_ID" id="TAUX_TVA_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php
                                foreach ($get_taux_tva as $key)
                                {
                                  if($key->TAUX_TVA_ID==set_value("TAUX_TVA_ID"))
                                  {
                                     echo '<option selected value="'.$key->TAUX_TVA_ID.'">'.$key->DESCRIPTION_TAUX_TVA.'</option>';
                                  }
                                  elseif ($key->TAUX_TVA_ID==$info['TAUX_TVA_ID'])
                                  {
                                    echo '<option selected value="'.$key->TAUX_TVA_ID.'">'.$key->DESCRIPTION_TAUX_TVA.'</option>';
                                  }
                                  else
                                  {
                                    echo '<option value="'.$key->TAUX_TVA_ID.'">'.$key->DESCRIPTION_TAUX_TVA.'</option>';
                                  }
                                }
                                ?>
                              </select>
                              <font  color="red" id="error_TAUX_TVA_ID"><?=$validation->getError('TAUX_TVA_ID');?></font>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.table_exo')?><span style="color: red;">*</span></label>
                              <select class="form-control" name="EXONERATION" id="EXONERATION">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php
                                if(set_value("EXONERATION")==1)
                                {
                                  echo '<option selected="" value="1">'.lang('messages_lang.label_oui').'</option>
                                   <option value="0">'.lang('messages_lang.label_non').'</option>';
                                }
                                elseif(set_value("EXONERATION")==0)
                                {
                                  echo '<option value="1">'.lang('messages_lang.label_oui').'</option>
                                  <option selected="" value="0">'.lang('messages_lang.label_non').'</option>';
                                }
                                elseif ($info['EXONERATION']==1) {
                                ?>
                                <option selected="" value="1"><?=lang('messages_lang.label_oui')?></option>
                                <option value="0"><?=lang('messages_lang.label_non')?></option>
                                <?php
                                }elseif ($info['EXONERATION']==0) {
                                ?>
                                <option value="1"><?=lang('messages_lang.label_oui')?></option>
                                <option selected="" value="0"><?=lang('messages_lang.label_non')?></option>
                                <?php
                                }else{
                                ?>
                                <option value="1"><?=lang('messages_lang.label_oui')?></option>
                                <option value="0"><?=lang('messages_lang.label_non')?></option>
                                <?php
                                }
                                ?>
                              </select>
                              <font color="red" id="error_EXONERATION"><?=$validation->getError('EXONERATION');?></font>
                            </div>

                            <div class="col-6">
                              <label><?=lang('messages_lang.label_date_trans_conf')?>(CED)<span style="color: red;">*</span></label>
                              <input onkeypress="return false" onblur="this.type='date'" type="date" max="<?=date('Y-m-d')?>" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION" class="form-control" value="<?=set_value("DATE_TRANSMISSION")?>">
                              <font color="red" id="error_DATE_TRANSMISSION"><?=$validation->getError('DATE_TRANSMISSION');?></font>
                            </div>

                            <div class="col-6">
                              <input type="hidden" name="nbrverification" id="nbrverification" value="<?=$nbrverification?>">
                              <label><?=lang('messages_lang.label_verification')?><span style="color: red;">*</span></label>
                              <select class="form-control select2" multiple  id="TYPE_ANALYSE_ID" name="TYPE_ANALYSE_ID[]">
                                <?php foreach ($get_verification2 as $key)
                                {
                                  if($key->EXECUTION_BUDGETAIRE_ID==set_value("TYPE_ANALYSE_ID[]"))
                                  {
                                    echo '<option selected value="'.$key->TYPE_ANALYSE_ID.'" selected>'.$key->DESC_TYPE_ANALYSE.'</option>';
                                  }
                                  elseif ($key->EXECUTION_BUDGETAIRE_ID==$info['EXECUTION_BUDGETAIRE_ID']) 
                                  {
                                    echo '<option selected value="'.$key->TYPE_ANALYSE_ID.'">'.$key->DESC_TYPE_ANALYSE.'</option>';
                                  }
                                }
                               foreach ($get_verification as $key1) {?>
                                <option value="<?=$key1->TYPE_ANALYSE_ID?>"><?=$key1->DESC_TYPE_ANALYSE?></option>
                                <?php }
                                 ?>
                              </select>
                              <font color="red" id="error_TYPE_ANALYSE_ID"><?=$validation->getError('TYPE_ANALYSE_ID');?></font>
                            </div>

                            <div class="col-12">
                              <label><?=lang('messages_lang.labelle_observartion')?></label>
                              <textarea name="OBSERVATION" id="OBSERVATION" class="form-control"></textarea>
                              <font color="red" id="error_OBSERVATION"></font>
                            </div>

                            <?php
                            if ($info['MARCHE_PUBLIQUE']==1)
                            {?>                  
                              <input type="hidden" id="MONTANT_ENLEVE" name="MONTANT_ENLEVE" value="<?=$mont_enleve?>">
                              <?php
                                if($TYPE_MONTANT_ID!=1)
                                {?>
                                  <input type="hidden" id="MONTANT_ENLEVE_DEVISE" name="MONTANT_ENLEVE_DEVISE" value="<?=$mont_enleve_devise?>">
                                  <?php
                                }
                                ?>
                              <div class="col-6">
                                <input type="hidden" name="PATH_PV_RECEPTION_LIQUIDATION_OUP" value="<?=$info['PATH_PV_RECEPTION_LIQUIDATION']?>" id="PATH_PV_RECEPTION_LIQUIDATION_OUP" class="form-control">
                                <label><?=lang('messages_lang.label_pv_reception')?><span style="color: red;">*</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?=base_url($info['PATH_PV_RECEPTION_LIQUIDATION'])?>" title="Voir PV de réception" target="_blank"><span class="fa fa-file-pdf" style="color:red;font-size: 120%;"></span></a></label>
                                <input onchange="ValidationFile(1);" accept=".pdf" type="file" name="PATH_PV_RECEPTION_LIQUIDATION" id="PATH_PV_RECEPTION_LIQUIDATION" class="form-control" >
                                <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION"></font>
                                <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION_VOLUMINEUX"></font>
                                <font color="red" id="error_PATH_PV_RECEPTION_LIQUIDATION_FORMAT"></font>
                              </div>

                              <div class="col-6">
                                <label><?=lang('messages_lang.label_date_livraison')?><span style="color: red;">*</span></label>
                                <input value="<?=date('Y-m-d',strtotime($info['DATE_LIVRAISON_CONTRAT']))?>" type="date" onchange="getNbrJrs()" min="<?=date('Y-m-d',strtotime((!empty($info['DATE_DEBUT_CONTRAT'])) ? $info['DATE_DEBUT_CONTRAT'] : ''))?>" name="DATE_LIVRAISON_CONTRAT" id="DATE_LIVRAISON_CONTRAT" class="form-control" >
                                <font color="red" id="error_DATE_LIVRAISON_CONTRAT"><?=$validation->getError('ID_TYPE_LIQUIDATION');?></font><br>
                                <font color="red" id="error_DATE_FIN_LIVRAISON2"></font>
                              </div>     
                              <?php
                            }
                            ?>

                            <?php 
                            if($info['BUDGETAIRE_TYPE_DOCUMENT_ID']==2)
                            {?>
                              <div class="col-6">
                                <input type="hidden" name="PATH_FACTURE_LIQUIDATION_OUP" value="<?=$info['PATH_FACTURE_LIQUIDATION']?>" id="PATH_FACTURE_LIQUIDATION_OUP" class="form-control">
                                <label><?=lang('messages_lang.label_facture')?><span style="color: red;">*</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?=base_url($info['PATH_FACTURE_LIQUIDATION'])?>" title="Voir la Facture" target="_blank"><span class="fa fa-file-pdf" style="color:red;font-size: 120%;"></span></a></label>
                                <input onchange="ValidationFile(2);" accept=".pdf" type="file" name="PATH_FACTURE_LIQUIDATION" id="PATH_FACTURE_LIQUIDATION" class="form-control" >
                                <font color="red" id="error_PATH_FACTURE_LIQUIDATION"></font>
                                <font color="red" id="error_PATH_FACTURE_LIQUIDATION_VOLUMINEUX"></font>
                                <font color="red" id="error_PATH_FACTURE_LIQUIDATION_FORMAT"></font>
                              </div>

                              <div class="col-md-12" id="intro_note_div">
                                <label for=""> <?= lang('messages_lang.label_intro_note') ?> <font color="red">*</font></label>
                                <textarea class="form-control" name="intro_note" id="intro_note"><?=$info['INTRODUCTION_NOTE']?></textarea>
                                <font color="red" id="error_intro_note"><?=$validation->getError('ID_TYPE_LIQUIDATION');?></font>
                              </div>
                              <?php 
                            }
                            ?>
                          </div>
                        </div>
                        <div class="col-12">
                          <button id="disabled_btn" style="float: right;" id="btnSave" type="button" onclick="send_data()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_modifier')?> <span id="loading_btn"></span></button>
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
    <?php echo view('includesbackend/scripts_js.php');?>
  </body>
</html>

<div class='modal fade' id='detail_infos' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?=lang('messages_lang.titre_modal')?></h5>
      </div>
      <div class='modal-body'>
        <div id="infos_data"></div>
      </div>
      <div class='modal-footer'>
        <button id="modif123" onclick="deleteFile();hideButton()" class='btn btn-primary btn-md' data-dismiss='modal'><i class="fa fa-pencil"></i>  <?=lang('messages_lang.labelle_mod')?> <span id="loading_delete"></span></button>
        <button id="confi123" onclick="send_data2();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i> <?=lang('messages_lang.labelle_conf')?></button>
      </div>
    </div>
  </div>
</div>

<script>
  function hideButton()
  {
    var element = document.getElementById("modif123");
    element.style.display = "none";

    var elementmod = document.getElementById("confi123");
    elementmod.style.display = "none";
  }
</script>

<script type="text/javascript">
  function get_min_transRecept()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
  }
</script>

<script type="text/javascript">
  function ValidationFile($id)
  {
    if ($id==1)
    {
      var fileInput = document.getElementById('PATH_PV_RECEPTION_LIQUIDATION');
      var filePath = fileInput.value;
      // Allowing file type
      var allowedExtensions = /(\.pdf)$/i;
      
      if (!allowedExtensions.exec(filePath))
      {
        $('#error_PATH_PV_RECEPTION_LIQUIDATION_FORMAT').text("<?=lang('messages_lang.error_message_pdf')?>");
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
              $('#error_PATH_PV_RECEPTION_LIQUIDATION_VOLUMINEUX').text('<?=lang('messages_lang.error_message_taille_pdf')?>');
              fileInput.value = '';
            }else
            {
             $('#error_PATH_PV_RECEPTION_LIQUIDATION_VOLUMINEUX').text(''); 
            }
          } 
        }
      }
    }
    else if ($id==2)
    {
      var fileInput = document.getElementById('PATH_FACTURE_LIQUIDATION');
      var filePath = fileInput.value;
      // Allowing file type
      var allowedExtensions = /(\.pdf)$/i;
      
      if (!allowedExtensions.exec(filePath))
      {
        $('#error_PATH_FACTURE_LIQUIDATION_FORMAT').text("<?=lang('messages_lang.error_message_pdf')?>");
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
              $('#error_PATH_FACTURE_LIQUIDATION_VOLUMINEUX').text('<?=lang('messages_lang.error_message_taille_pdf')?>');
              fileInput.value = '';
            }else
            {
             $('#error_PATH_FACTURE_LIQUIDATION_VOLUMINEUX').text(''); 
            }
          } 
        }
      }
    }
  }
</script>

<script>
  function getNbrJrs()
  {      
    var DATE_LIVRAISON_CONTRAT = $("#DATE_LIVRAISON_CONTRAT").val();
    var DATE_DEBUT_CONTRAT = $("#DATE_DEBUT_CONTRAT").val();

    if (DATE_DEBUT_CONTRAT!='' && DATE_LIVRAISON_CONTRAT!='')
    {
      if (DATE_LIVRAISON_CONTRAT < DATE_DEBUT_CONTRAT)
      {
        $("#error_DATE_FIN_LIVRAISON2").html("<?=lang('messages_lang.labelle_date_livraison_pas_posterieur_date_contrat')?>("+DATE_DEBUT_CONTRAT+")");
        $("#DATE_LIVRAISON_CONTRAT").val('');
      }
    }
  }
</script>

<script type="text/javascript">
  function getTypeLiquidationMontant()
  {
    var ID_TYPE_LIQUIDATION=$('#ID_TYPE_LIQUIDATION').val();
    var TYPE_MONTANT_ID=$('#TYPE_MONTANT_ID').val();
    var MONTANT_RACCROCHE_JURIDIQUE=$('#MONTANT_RACCROCHE_JURIDIQUE_VALUE').val();
    var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE=$('#MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE').val();
    var COUT_DEVISE=$('#COUT_DEVISE').val();
    COUT_DEVISE =COUT_DEVISE.replace(/[^0-9.]/g, '');
    
    //cas ou le type montant c'est BIF
    if (TYPE_MONTANT_ID==1)
    {
      var MONTANT_RACCROCHE_JURIDIQUE = parseInt(MONTANT_RACCROCHE_JURIDIQUE.replace(/[^\d.]/g,''),10);
      var MONTANT_RACCROCHE_JURIDIQUE = MONTANT_RACCROCHE_JURIDIQUE.toLocaleString("en-US",{useGrouping: true});
      var MONTANT_RACCROCHE_JURIDIQUE = MONTANT_RACCROCHE_JURIDIQUE.replace(/,/g, ' ');

      if (ID_TYPE_LIQUIDATION==2)
      {
        $('#MONTANT_CREANCE').val(MONTANT_RACCROCHE_JURIDIQUE);
        document.getElementById('MONTANT_CREANCE').readOnly = true;
      }
      else if (ID_TYPE_LIQUIDATION==1)
      {
        $('#MONTANT_CREANCE').val('');
        document.getElementById('MONTANT_CREANCE').readOnly = false;
      }
      else if (ID_TYPE_LIQUIDATION==3)
      {
        $('#MONTANT_CREANCE').val('');
        document.getElementById('MONTANT_CREANCE').readOnly = false;
      }
    }
    else
    {
      var mont_dev_eng_jur = parseFloat(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE);
      var mont_dev_eng_jur = mont_dev_eng_jur.toLocaleString("en-US",{useGrouping: true});

      var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = mont_dev_eng_jur.replace(/,/g, ' ');
      var INT_MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE.replace(/[^\d.]/g, "");

      var mont_liquidation =  parseFloat(INT_MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE)*parseFloat(COUT_DEVISE);
      var mont_liquid = mont_liquidation.toFixed(0);
      var LIQUIDATION = mont_liquid.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

      //cas total 
      if (ID_TYPE_LIQUIDATION==2)
      {
        $('#MONTANT_DEVISE').val(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE);
        document.getElementById('MONTANT_DEVISE').readOnly = true;
        $('#LIQUIDATION').val(LIQUIDATION);
      }
      else if (ID_TYPE_LIQUIDATION==1)
      {
        $('#MONTANT_DEVISE').val('');
        $('#LIQUIDATION').val('');
        document.getElementById('MONTANT_DEVISE').readOnly = false;
      }
      else if (ID_TYPE_LIQUIDATION==3)
      {
        $('#MONTANT_DEVISE').val('');
        $('#LIQUIDATION').val('');
        document.getElementById('MONTANT_DEVISE').readOnly = false;
      }
    }
  }
</script>

<script type="text/javascript">
  function get_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_LIQUIDATION").val());
  }

  $(document).ready(function()
  {
    getTypeLiquidationMontant();
    getCalculMontant();

    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val()

    if (TYPE_MONTANT_ID!=1) {

      $('#div_devise1').attr('hidden',false);
      $('#div_devise3').attr('hidden',false);

      $('#div_creance').attr('hidden',true);
      $('#div_devise2').attr('hidden', false);
      $('#date_dev').attr('hidden',false);

      $('#DIV_TITRE_CREANCE').addClass('col-4').removeClass('col-6');
      $('#DIV_DATE_CREANCE').addClass('col-4').removeClass('col-6');
      $('#DIV_DATE_LIQUIDATION').addClass('col-4').removeClass('col-6');

      var Amount = document.getElementById("MONTANT_DEVISE");
      Amount.addEventListener('keyup', function(evt) {
        if (this.value !== '') {
          var n = parseFloat(this.value.replace(/[^0-9.]/g,''),10);
          var dev = n.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            Amount.value = dev;
        } else {
          Amount.value = '';
        }
      }, false);

    }else if (TYPE_MONTANT_ID==1){

      $('#div_devise1').attr('hidden',true);
      $('#div_devise3').attr('hidden',true);

      $('#div_creance').attr('hidden',false);
      $('#div_devise2').attr('hidden', true);
      $('#date_dev').attr('hidden',true);    

      var Amount = document.getElementById("MONTANT_CREANCE");
      Amount.addEventListener('keyup', function(evt){

      if (this.value!='') {
        var n = parseFloat(this.value.replace(/[^0-9.]/g,''),10);
        var dev = n.toLocaleString('en-US', { useGrouping: true });
        Amount.value = dev.replace(/,/g, ' ');
      }else{
        $("#MONTANT_CREANCE").val('')
      } 
    }, false);
    }

    $('#MONTANT_CREANCE').bind('paste', function (e) {
       e.preventDefault();
     });

    $('#LIQUIDATION').bind('paste', function (e) {
       e.preventDefault();
     });

    $('#MONTANT_DEVISE').bind('paste', function (e) {
       e.preventDefault();
     });

    document.getElementById('LIQUIDATION').readOnly = true;
  });

  $("#MONTANT_CREANCE").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9.]*$/gi, ''));
  });

  $("#LIQUIDATION").on('input', function()
  {
    $(this).val($(this).val().replace(/[^0-9.]*$/gi, ''));
  });

  $("#MONTANT_DEVISE").on('input', function() {
    $(this).val($(this).val().replace(/[^0-9.]/g, ''));
  });

  function getSubstring(id)
  {
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val();
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val();

    if (id==1)
    {
      var getNumber = MONTANT_CREANCE.substring(0, 1);
      if (getNumber==0)
      {
        $('#MONTANT_CREANCE').val('');
      }
    }
    else if (id==3)
    {
      var getNumber = MONTANT_DEVISE.substring(0, 1);
      if (getNumber==0) {
        $('#MONTANT_DEVISE').val('');
      }
    }
  }
</script>

<script type="text/javascript">
  $("#TITRE_CREANCE").on('input', function()
  {
    var maxLength;
    if (this.id === "TITRE_CREANCE")
    {
      maxLength = 30;
    }
    $(this).val(this.value.substring(0, maxLength));
    $(this).val($(this).val().toUpperCase());
  });

  $("#MONTANT_CREANCE,#MONTANT_DEVISE").on('input', function()
  {
    var maxLength;
    if (this.id === "MONTANT_CREANCE" || this.id === "MONTANT_DEVISE")
    {
      maxLength = 20;
    }
    $(this).val(this.value.substring(0, maxLength));
  });
</script>

<!-- Bonfils de Jésus -->
<script type="text/javascript">
  function SetMaxLength (id)
  {
    var TITRE_CREANCE = $('#TITRE_CREANCE').val().length; 
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val().length; 
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val().length; 

    if(id==1)
    {
      $('#getNumberTITRE_CREANCE').text("");
      if (TITRE_CREANCE!=0)
      {
        $('#getNumberTITRE_CREANCE').text(""+TITRE_CREANCE+"/30");
      }
    }
    else if (id==3)
    {
      $('#getNumberMONTANT_CREANCE').text("");
      if(MONTANT_CREANCE!=0)
      {
        $('#getNumberMONTANT_CREANCE').text(""+MONTANT_CREANCE+"/20");
      }
    }
    else if (id==4)
    {
      $('#getNumberMONTANT_DEVISE').text("");
      if(MONTANT_DEVISE!=0)
      {
        $('#getNumberMONTANT_DEVISE').text(""+MONTANT_DEVISE+"/20");
      }
    }
  } 

  function getCalculMontant(argument) 
  {    
    var MONTANT_RACCROCHE_JURIDIQUE=$('#MONTANT_RACCROCHE_JURIDIQUE_VALUE').val();
    var MONTANT_CREANCE=$('#MONTANT_CREANCE').val();
    var LIQUIDATION=$('#LIQUIDATION').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val()
    var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $('#MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE').val()
    var MONTANT_DEVISE=$('#MONTANT_DEVISE').val()
    var COUT_DEVISE = $('#COUT_DEVISE').val();
    COUT_DEVISE =COUT_DEVISE.replace(/[^0-9.]/g, '');
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val()
    if (TYPE_MONTANT_ID!=1)
    { 
      var MONTANT_DEVISE = MONTANT_DEVISE.replace(/[^0-9.]/g, '');
      if(MARCHE_PUBLIQUE==1)
      {
        var MONTANT_ENLEVE_DEVISE=$('#MONTANT_ENLEVE_DEVISE').val()
        MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE=MONTANT_ENLEVE_DEVISE;
      }
      if(parseInt(MONTANT_DEVISE)>parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE))
      {
        $('#error_MONTANT_DEVISE_SUP').html("<?=lang("messages_lang.labelle_montant_devise_liquidation_pas_supeirieur_engag_juridik")?>("+MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE+")");
        $('#LIQUIDATION').val('')
        return false;
      }
      else
      {
        $('#error_MONTANT_DEVISE_SUP').text('');

        if (MONTANT_DEVISE=='') {
          var MONTANT_DEVISE = 0;
        }

        if (COUT_DEVISE=='') {
          var COUT_DEVISE = 0;
        }
  
        var LIQUIDATION = parseFloat(MONTANT_DEVISE)*parseFloat(COUT_DEVISE);
        var value = LIQUIDATION.toFixed(0);
        var liquid = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        
        $('#LIQUIDATION').val(liquid);
      }
    }
    else
    {
      if(MARCHE_PUBLIQUE==1)
      {
        MONTANT_ENLEVE=$('#MONTANT_ENLEVE').val()
        MONTANT_RACCROCHE_JURIDIQUE = MONTANT_ENLEVE;
      }
      var MONTANT_CREANCE = MONTANT_CREANCE.replace(/[^0-9.]/g, "");
      if(parseInt(MONTANT_CREANCE)>parseInt(MONTANT_RACCROCHE_JURIDIQUE))
      {
        $('#error_MONTANT_CREANCE_SUP').text("<?=lang("messages_lang.labelle_montant_titre_creance_pas_supeirieur_engag_juridik")?>("+MONTANT_RACCROCHE_JURIDIQUE+")");
        return false;
      }
      else
      {
        $('#error_MONTANT_CREANCE_SUP').text('');
      }
    }
  }
</script>

<script type="text/javascript">
  function send_data()
  {
    var statut = true;
    var ID_TYPE_LIQUIDATION = $('#ID_TYPE_LIQUIDATION').val()
    var TITRE_CREANCE=$('#TITRE_CREANCE').val()
    var DATE_CREANCE=$('#DATE_CREANCE').val()
    var MONTANT_CREANCE = $('#MONTANT_CREANCE').val();
    var LIQUIDATION = $('#LIQUIDATION').val();
    var DATE_LIQUIDATION = $('#DATE_LIQUIDATION').val();
    var MOTIF_LIQUIDATION = $('#MOTIF_LIQUIDATION').val();
    var TAUX_TVA_ID = $('#TAUX_TVA_ID').val();
    var EXONERATION = $('#EXONERATION').val();
    var OBSERVATION = $('#OBSERVATION').val();
    var TYPE_ANALYSE_ID = $('#TYPE_ANALYSE_ID').val();
    var nbrverification = $('#nbrverification').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    var MONTANT_DEVISE = $('#MONTANT_DEVISE').val()

    var MONTANT_RACCROCHE_JURIDIQUE=$('#MONTANT_RACCROCHE_JURIDIQUE_VALUE').val();
    var MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE=$('#MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE').val();
    var TYPE_MONTANT_ID = $('#TYPE_MONTANT_ID').val()
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val()

    var PATH_PV_RECEPTION_LIQUIDATION = $('#PATH_PV_RECEPTION_LIQUIDATION').val()
    var PATH_FACTURE_LIQUIDATION = $('#PATH_FACTURE_LIQUIDATION').val()
    var DATE_LIVRAISON_CONTRAT = $('#DATE_LIVRAISON_CONTRAT').val()

    var COUT_DEVISE = $('#COUT_DEVISE').val();       
    $('#error_COUT_DEVISE').text('');

    var DATE_COUT_DEVISE = $('#DATE_COUT_DEVISE').val();
    $('#error_DATE_COUT_DEVISE').text('');

    var BUDGETAIRE_TYPE_DOCUMENT_ID=$('#BUDGETAIRE_TYPE_DOCUMENT_ID').val();
    var intro_note=$('#intro_note').val();
    $('#error_intro_note').html('');

    var DESC_TYPE_ANALYSE = $('#TYPE_ANALYSE_ID option:selected').toArray().map(item => item.text).join();

    if(ID_TYPE_LIQUIDATION=='') 
    {
      $('#error_ID_TYPE_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_ID_TYPE_LIQUIDATION').text('');
    }

    if(DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_RECEPTION').text('');
    }

    if(TITRE_CREANCE=='') 
    {
      $('#error_TITRE_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_TITRE_CREANCE').text('');
    }

    if(DATE_CREANCE=='') 
    {
      $('#error_DATE_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_CREANCE').text('');
    }

    if(DATE_LIQUIDATION=='') 
    {
      $('#error_DATE_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_LIQUIDATION').text('');
    }

    if(MOTIF_LIQUIDATION=='') 
    {
      $('#error_MOTIF_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_MOTIF_LIQUIDATION').text('');
    }

    if (TYPE_MONTANT_ID!=1)
    {
      if(MONTANT_DEVISE=='') 
      {
        $('#error_MONTANT_DEVISE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_MONTANT_DEVISE').text('');
      }

      if(LIQUIDATION=='') 
      {
        $('#error_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_LIQUIDATION').text('');
      }

      if(COUT_DEVISE=='')
      {
        $('#error_COUT_DEVISE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_COUT_DEVISE').text('');
      }

      if(DATE_COUT_DEVISE=='')
      {
        $('#error_DATE_COUT_DEVISE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        statut = false;
      }
      else
      {
        $('#error_DATE_COUT_DEVISE').text('');
      }

      var MONTANT_DEVISE = MONTANT_DEVISE.replace(/[^0-9.]/g, '');

      if(MARCHE_PUBLIQUE==1)
      {
        var MONTANT_ENLEVE_DEVISE=$('#MONTANT_ENLEVE_DEVISE').val()
        MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE=MONTANT_ENLEVE_DEVISE;
      }
      if(parseInt(MONTANT_DEVISE)>parseInt(MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE))
      {
        $('#error_MONTANT_DEVISE_SUP').html("<?=lang("messages_lang.labelle_montant_devise_liquidation_pas_supeirieur_engag_juridik")?>("+MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE+")");
        return false;
      }
      else
      {
        $('#error_MONTANT_DEVISE_SUP').text('');
      }
    }
    else
    {
      if(MONTANT_CREANCE=='') 
      {
        $('#error_MONTANT_CREANCE').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        var MONTANT_CREANCE = MONTANT_CREANCE.replace(/[^0-9.]/g, "");
        if(MARCHE_PUBLIQUE==1)
        {
          MONTANT_ENLEVE=$('#MONTANT_ENLEVE').val()
          MONTANT_RACCROCHE_JURIDIQUE = MONTANT_ENLEVE;
        }
        $('#error_MONTANT_CREANCE').text('');
        if(parseInt(MONTANT_CREANCE)>parseInt(MONTANT_RACCROCHE_JURIDIQUE))
        {
          $('#error_MONTANT_CREANCE_SUP').text("<?=lang("messages_lang.labelle_montant_titre_creance_pas_supeirieur_engag_juridik")?>("+MONTANT_RACCROCHE_JURIDIQUE+")");
          return false;
        }
        else
        {
          $('#error_MONTANT_CREANCE_SUP').text('');
        }
      }
    }

    if(TAUX_TVA_ID=='') 
    {
      $('#error_TAUX_TVA_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_TAUX_TVA_ID').text('');
    }

    if (EXONERATION=='') 
    {
      $('#error_EXONERATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else{
      $('#error_EXONERATION').text('');
    }

    if(DATE_TRANSMISSION=='') 
    {
      $('#error_DATE_TRANSMISSION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_DATE_TRANSMISSION').text('');
    }

    var length = $('#TYPE_ANALYSE_ID option:selected').length;

    if (TYPE_ANALYSE_ID=='')
    {
      $('#error_TYPE_ANALYSE_ID').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
      return false;
    }
    else
    {
      $('#error_TYPE_ANALYSE_ID').text('');
    }    

    if (MARCHE_PUBLIQUE==1)
    {
      if ($("#PATH_PV_RECEPTION_LIQUIDATION_OUP").val()=='')
      {
        if(PATH_PV_RECEPTION_LIQUIDATION=='') 
        {
          $('#error_PATH_PV_RECEPTION_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
          return false;
        }
        else
        {
          $('#error_PATH_PV_RECEPTION_LIQUIDATION').text('');
        }
      }
      if(DATE_LIVRAISON_CONTRAT=='') 
      {
        $('#error_DATE_LIVRAISON_CONTRAT').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#error_DATE_LIVRAISON_CONTRAT').text('');
      }      
    }
    
    if(BUDGETAIRE_TYPE_DOCUMENT_ID==2)
    {
      if ($("#PATH_FACTURE_LIQUIDATION_OUP").val()=='')
      {
        if (PATH_FACTURE_LIQUIDATION=='') 
        {
          $('#error_PATH_FACTURE_LIQUIDATION').text('<?=lang('messages_lang.message_champs_obligatoire')?>');
          return false;
        }else{
          $('#error_PATH_FACTURE_LIQUIDATION').text('');
        }
      }      

      if(intro_note=='')
      {
        $('#error_intro_note').html('<?=lang('messages_lang.input_oblige')?>');
        return false;
      }else
      {
        $('#error_intro_note').html('');
      }
    }

    var form = new FormData();

    if (MARCHE_PUBLIQUE==1)
    {
      var PATH_PV_RECEPTION_LIQUIDATION=document.getElementById("PATH_PV_RECEPTION_LIQUIDATION").files[0];
      form.append("PATH_PV_RECEPTION_LIQUIDATION",PATH_PV_RECEPTION_LIQUIDATION);     
      form.append("DATE_LIVRAISON_CONTRAT",DATE_LIVRAISON_CONTRAT); 
    }

    if(BUDGETAIRE_TYPE_DOCUMENT_ID==2)
    {
      var PATH_FACTURE_LIQUIDATION=document.getElementById("PATH_FACTURE_LIQUIDATION").files[0];
      form.append("PATH_FACTURE_LIQUIDATION",PATH_FACTURE_LIQUIDATION);      
    }

    form.append("TITRE_CREANCE",TITRE_CREANCE);
    form.append("DATE_CREANCE",DATE_CREANCE);
    form.append("MONTANT_CREANCE",MONTANT_CREANCE);
    form.append("LIQUIDATION",LIQUIDATION);
    form.append("DATE_LIQUIDATION",DATE_LIQUIDATION); 
    form.append("MOTIF_LIQUIDATION",MOTIF_LIQUIDATION);
    form.append("TAUX_TVA_ID",TAUX_TVA_ID);
    form.append("EXONERATION",EXONERATION);
    form.append("OBSERVATION",OBSERVATION);
    form.append("DESC_TYPE_ANALYSE",DESC_TYPE_ANALYSE); 
    form.append("DATE_TRANSMISSION",DATE_TRANSMISSION);
    form.append("DATE_RECEPTION",DATE_RECEPTION);
    form.append("TYPE_MONTANT_ID",TYPE_MONTANT_ID);
    form.append("MONTANT_DEVISE",MONTANT_DEVISE);
    form.append("ID_TYPE_LIQUIDATION",ID_TYPE_LIQUIDATION);
    form.append("MARCHE_PUBLIQUE",MARCHE_PUBLIQUE);
    form.append("COUT_DEVISE",COUT_DEVISE);
    form.append("DATE_COUT_DEVISE",DATE_COUT_DEVISE);

    if(statut == true) 
    {
      $.ajax(
      {
        url:"<?=base_url('/double_commande_new/Liquidation/getInfoDetail')?>",
        type:"POST",
        dataType:"JSON",
        data: form,
        processData: false,  
        contentType: false,
        beforeSend:function() {
          $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#loading_btn').attr('disabled',true);
        },
        success: function(data)
        { 
          $('#detail_infos').modal('show'); // afficher bootstrap modal
          $('#infos_data').html(data.html)
          $('#loading_btn').html("");
          $('#loading_btn').attr('disabled',false);
        }
      });
    }
  }

  function send_data2(argument)
  {          
    document.getElementById("my_form").submit();
  }
</script>

<script type="text/javascript">
  function deleteFile()
  {
    var GETDATA_DELETE = 0;;

    $.ajax({
      url: "<?= base_url('/double_commande_new/Liquidation/deleteFile')?>",
      type: 'POST',
      dataType:'JSON',
      data: {
        GETDATA_DELETE:GETDATA_DELETE
      },
      beforeSend:function() {
        $('#loading_delete').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        if (data) 
        {                          
          $('#loading_delete').html("");
        }                       
      }
    });
  }
</script>

