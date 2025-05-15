<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>

</head>
<body>
 <div class="wrapper">
  <?php echo view('includesbackend/navybar_menu.php');?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
  <script src="/DataTables/datatables.js"></script>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <div class="main">
   <?php echo view('includesbackend/navybar_topbar.php');?>
   <main class="content">
    <div class="container-fluid">

     <div class="row">
      <div class="col-12">

       <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
        <br>
        <div class="col-12 d-flex">
          
          <div class="col-9" style="float: left;">
            <h1 class="header-title text-dark">
              Modification d'une activité
            </h1>
          </div>
          <div class="col-3" style="float: right;">
            <!-- <a href="<?=base_url('process/Dem_Liste_Activites')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;Liste</a> -->
          </div>
        </div>
        <!-- <br> -->
        <div class="card-body">

          <?php $validation = \Config\Services::validation(); ?>
          <form id="my_form" action="<?= base_url('process/modifier_activite') ?>" method="POST">
            <div class="card-body">

              <div class="col-12">
                <h3><?=lang('messages_lang.labelle_institution')?></h3>
                <input class="form-control text-center" type="text" value="<?=$descr_instit?>" readonly>
              </div>

            </div>
          </div>
          
          <div class="row" style="margin :  5px">

            <!-- <div class="row" style="border:1px solid #ddd;border-radius:5px;margin: 5px"> -->
              
              <div class="col-12">
                <h4><center> <i class="fa fa-info-circle"></i><?=lang('messages_lang.labelle_information_base')?></center></h4>
              </div>
              
              <input type="hidden" name="INSTITUTION_ID" id="INSTITUTION_ID" value="<?=$INSTITUTION_ID?>">
              <input type="hidden" value="<?=$Id_demande_up?>" name="DEMANDE_ID">
              
              <input type="hidden" name="CODE_INSTITUTION" id="CODE_INSTITUTION" value="<?=$code_instit?>">
              <input type="hidden" name="DESCR_INSTITUTION" id="DESCR_INSTITUTION" value="<?=$descr_instit?>">

              <div class="row">
                <div class="col-6">
                  <div class="form-group">
                    <label><?=lang('messages_lang.labelle_Soustitre')?><span style="color: red;">*</span></label>

                    <select  class="  form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="get_programs();">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      <?php  foreach ($inst_sous_tutel as $inst) { ?>
                        <?php  if ($inst->SOUS_TUTEL_ID == $activites['SOUS_TUTEL_ID']) { ?>
                          <option value="<?=$inst->SOUS_TUTEL_ID ?>" selected>
                            <?=$inst->DESCRIPTION_SOUS_TUTEL?></option>
                          <?php }else{?>
                            <option value="<?=$inst->SOUS_TUTEL_ID ?>">
                              <?=$inst->DESCRIPTION_SOUS_TUTEL?></option> 
                            <?php } }?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                          <?php endif ?>
                          <span id="error_DESCRIPTION_SOUS_TUTEL" class="text-danger"></span>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_programme')?><span style="color: red;">*</span></label>
                          <select class="  form-control" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="create_get_action()">
                            <option value=""><?=lang('messages_lang.label_selecte')?></option>
                            <?php  foreach ($inst_program as $progr) { ?>
                              <?php  if ($progr->PROGRAMME_ID ==$get_prgr['PROGRAMME_ID'] ) { ?>
                                <option value="<?=$progr->PROGRAMME_ID ?>" selected>
                                  <?=$progr->INTITULE_PROGRAMME?></option>
                                <?php }else{?>
                                  <option value="<?=$progr->PROGRAMME_ID ?>">
                                    <?=$progr->INTITULE_PROGRAMME?></option>
                                  <?php } }?>
                                </select>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('PROGRAMME_ID'); ?>
                                <?php endif ?>
                                <span id="error_INTITULE_PROGRAMME" class="text-danger"></span>
                              </div>
                            </div>
                          </div>

                          <div class="form-row col-12">
                            
                            <div class="form-group col-4">
                              <label for="ministère" class="Form-label text-muted"><?=lang('messages_lang.labelle_action')?> <font color="red" >*</font></label>
                              <!-- form-select -->
                              <select class=" form-control  bg-light" name="ACTION_ID" id="ACTION_ID" placeholder="Sélectionnez l'action" autocomplete="true"   onchange="get_action();">
                                <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                <?php  foreach ($inst_action as $key) { ?>
                                  <?php  if ($key->ACTION_ID ==$get_actio['ACTION_ID'] ) { ?>
                                    <option value="<?=$key->ACTION_ID ?>" selected>
                                      <?=$key->LIBELLE_ACTION?></option>
                                    <?php }else{?>
                                      <option value="<?=$key->ACTION_ID ?>">
                                        <?=$key->LIBELLE_ACTION?></option>
                                      <?php } }?>
                                    </select>
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('ACTION_ID'); ?>
                                    <?php endif ?>
                                    <div class="valid-feedback">
                                    </div>
                                    <span class="text-danger" id="error_ACTION_ID"></span>
                                  </div>
                                  
                                  <div class="form-group col-4">
                                    <label for="ministère" class="Form-label text-muted"><?=lang('messages_lang.labelle_code_budgetaire')?> <font color="red" >*</font></label>
                                    <input onkeyup="SetMaxLength(22)" autocomplete="off" type="text" name="CODE_NOMENCLATURE_BUDGETAIRE" id="CODE_NOMENCLATURE_BUDGETAIRE" class="form-control" value="<?= $activites['CODE_NOMENCLATURE_BUDGETAIRE']?>" >
                                    <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE'); ?>
                                    <?php endif ?>
                                    <div class="valid-feedback">
                                    </div>
                                    <span class="text-danger" id="error_code_budget"></span>
                                  </div>
                                  
                                  <div class="form-group col-4">
                                    <label><?=lang('messages_lang.labelle_code_programmatique')?><span style="color: red;">*</span></label>
                                    <input onkeyup="SetMaxLength(4)" autocomplete="off" type="text" name="CODES_PROGRAMMATIQUE" id="CODES_PROGRAMMATIQUE" class="form-control" value="<?=$activites['CODES_PROGRAMMATIQUE']?>" >
                                    <span style="font-size: 10px" id="getNumberCodeProgramme"></span>
                                    <span class="text-danger" id="error_codes_progr"></span>
                                  </div>
                                  
                                </div>

                                <div class="row col-12">
                                  <div class="col-4">
                                    <div class="form-group">
                                      <label><?=lang('messages_lang.labelle_activites')?><span style="color: red;">*</span></label>
                                      <textarea onkeyup="SetMaxLength(7)" name="ACTIVITES" id="ACTIVITES" class="form-control" rows="2"> <?=$activites['ACTIVITES']?></textarea>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('ACTIVITES'); ?>
                                      <?php endif ?>
                                      <span class="text-danger" id="error_Activites"></span>
                                      <span style="font-size: 10px" id="getNumberActivite"></span>
                                    </div>
                                  </div>
                                  
                                  <div class="col-4">
                                    <div class="form-group">
                                      <label><?=lang('messages_lang.labelle_resultant_attendus')?><span style="color: red;">*</span></label>
                                      <textarea onkeyup="SetMaxLength(8)" type="text" name="RESULTATS_ATTENDUS" id="RESULTATS_ATTENDUS" class="form-control" rows="2"><?=$activites['RESULTATS_ATTENDUS']?></textarea>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('RESULTATS_ATTENDUS'); ?>
                                      <?php endif ?>
                                      <span class="text-danger" id="error_Resultats_Attendus"></span>
                                      <span style="font-size: 10px" id="getNumberResultatAttendus"></span>
                                    </div>
                                  </div>
                                  <div class="col-4">
                                    <div class="form-group">
                                      <label> <?=lang('messages_lang.labelle_intitule_ligne')?><span style="color: red;">*</span></label>
                                      <textarea onkeyup="SetMaxLength(712)" name="INTITULE_LIGNE" id="INTITULE_LIGNE" class="form-control" rows="2"><?=$activites['INTITULE_LIGNE']?></textarea>
                                      <?php if (isset($validation)) : ?>
                                        <?= $validation->getError('INTITULE_LIGNE'); ?>
                                      <?php endif ?>
                                      <span class="text-danger" id="error_INTITULE_LIGNE"></span>
                                      <span style="font-size: 10px" id="getNumberINTITULE_LIGNE"></span>
                                    </div>
                                  </div>
                                </div>

                                

                                
                                
                              </div>
                            </div>
                          </div>
                          
                          <div style="border:1px solid #ddd;border-radius:5px;margin: 5px;background-color:#fff">
                            <div class="row" style="margin :  5px">
                              <div class="col-12">
                                <h4><center> <i class="fa fa-certificate"></i> <?=lang('messages_lang.labelle_economique')?></center></h4>
                              </div>
                              <div class="col-6">
                                <div class="form-group">
                                  <label> <?=lang('messages_lang.labelle_Chapitre')?><span style="color: red;">*</span></label>
                                  
                                  <select class=" form-control form-select bg-light" onchange="create_get_code_article()" name="CHAPITRES" id="CHAPITRES" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                    <option value=""><?=lang('messages_lang.label_selecte')?></option>

                                    <?php  foreach ($chapitres as $key) { ?>
                                      <?php  if ($key->CHAPITRE_ID ==$activites['CHAPITRES'] ) { ?>
                                        <option value="<?=$key->CHAPITRE_ID ?>" selected>
                                          <?=$key->LIBELLE_CHAPITRE?></option>
                                        <?php }else{?>
                                          <option value="<?=$key->CHAPITRE_ID ?>">
                                            <?=$key->LIBELLE_CHAPITRE?></option>
                                          <?php } }?>

                                        </select>

                                        <?php if (isset($validation)) : ?>
                                          <?= $validation->getError('CHAPITRES'); ?>
                                        <?php endif ?>
                                        <span class="text-danger" id="error_CHAPITRES"></span>
                                        <span style="font-size: 10px" id="getNumberIntituleArticle"></span>
                                      </div>
                                    </div>
                                    
                                    
                                    <div class="col-6">
                                      <div class="form-group">
                                        <label> <?=lang('messages_lang.labelle_intitule_economique')?><span style="color: red;">*</span></label>
                                        
                                        <select class=" form-control form-select bg-light code_articles" onchange="create_get_code_paragraphe()" name="INTITULE_ARTICLE_ECONOMIQUE" id="INTITUE_ARTICLE_ECONOMIQUE" autocomplete="off" aria-label=".form-select-lg example">

                                         <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                         <?php  foreach ($article as $key) { ?>
                                          <?php  if ($key->ARTICLE_ID ==$activites['INTITULE_ARTICLE_ECONOMIQUE'] ) { ?>
                                            <option value="<?=$key->ARTICLE_ID ?>" selected>
                                              <?=$key->LIBELLE_ARTICLE?></option>
                                            <?php }else{?>
                                              <option value="<?=$key->ARTICLE_ID ?>">
                                                <?=$key->LIBELLE_ARTICLE?></option>
                                              <?php } }?>
                                              
                                            </select>

                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('INTITULE_ARTICLE_ECONOMIQUE'); ?>
                                            <?php endif ?>
                                            <span class="text-danger" id="error_INTITULE_ARTICLE_ECONOMIQUE"></span>
                                            <span style="font-size: 10px" id="getNumberIntituleArticle"></span>
                                          </div>
                                        </div>

                                        <div class="col-6">
                                          <div class="form-group">
                                            <label><?=lang('messages_lang.labelle_article_economique')?><span style="color: red;">*</span></label>
                                            <input onkeyup="SetMaxLength(26)" autocomplete="off" type="text" name="ARTICLE_ECONOMIQUE" id="CODE_ARTICLES123" class="form-control" value="<?=$activites['ARTICLE_ECONOMIQUE']?>">
                                            <?php if (isset($validation)) : ?>
                                              <?= $validation->getError('ARTICLE_ECONOMIQUE'); ?>
                                            <?php endif ?>
                                            <span class="text-danger" id="error_ARTICLE_ECONOMIQUE"></span>
                                            <span style="font-size: 10px" id="getNumberArticleEconomique"></span>
                                          </div>
                                        </div>
                                        
                                        <div class="col-6">
                                          <div class="form-group">
                                            <label><?=lang('messages_lang.labelle_Paragraphe')?><span style="color: red;">*</span></label>
                                            
                                            <select class=" form-control form-select bg-light paragraphe_code" onchange="create_get_Code_littera()" name="PARAGRAPHE" id="PARAGRAPHE" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                              <option value=""><?=lang('messages_lang.label_selecte')?> </option>
                                              <?php  foreach ($get_paragraphe as $key) { ?>
                                                <?php  if ($key->PARAGRAPHE_ID ==$activites['PARAGRAPHE'] ) { ?>
                                                  <option value="<?=$key->PARAGRAPHE_ID ?>" selected>
                                                    <?=$key->LIBELLE_PARAGRAPHE?></option>
                                                  <?php }else{?>
                                                    <option value="<?=$key->PARAGRAPHE_ID ?>">
                                                      <?=$key->LIBELLE_PARAGRAPHE?></option>
                                                    <?php } }?>
                                                  </select>

                                                  <?php if (isset($validation)) : ?>
                                                    <?= $validation->getError('PARAGRAPHE'); ?>
                                                  <?php endif ?>
                                                  <span class="text-danger" id="error_PARAGRAPHE"></span>
                                                  <span style="font-size: 10px" id="getNumberIntituleArticle"></span>
                                                </div>
                                              </div>
                                              
                                              <div class="col-6">
                                                <div class="form-group">
                                                  <label><?=lang('messages_lang.labelle_Littera')?><span style="color: red;">*</span></label>
                                                  
                                                  <select class=" form-control form-select bg-light" onchange="create_get_sous_littera()" name="LITTERA" id="LITTERA" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                                    <option value=""><?=lang('messages_lang.label_selecte')?> </option>
                                                    <?php  foreach ($littera_get as $key) { ?>
                                                      <?php  if ($key->LITTERA_ID ==$activites['LITTERA'] ) { ?>
                                                        <option value="<?=$key->LITTERA_ID ?>" selected>
                                                          <?=$key->LIBELLE_LITTERA?></option>
                                                        <?php }else{?>
                                                          <option value="<?=$key->LITTERA_ID ?>">
                                                            <?=$key->LIBELLE_LITTERA?></option>
                                                          <?php } }?>
                                                        </select>

                                                        <?php if (isset($validation)) : ?>
                                                          <?= $validation->getError('LITTERA'); ?>
                                                        <?php endif ?>
                                                        <span class="text-danger" id="error_LITTERA"></span>
                                                        <span style="font-size: 10px" id="getNumberIntituleArticle"></span>
                                                      </div>
                                                    </div>
                                                    <div class="col-6">
                                                      <div class="form-group">
                                                        <label><?=lang('messages_lang.labelle_Sous_Littera')?><span style="color: red;">*</span></label>
                                                        <select class=" form-control form-select bg-light" name="INTITULE_NATURE_ECONOMIQUE" id="INTITULE_NATURE_ECONOMIQUE" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                                         <option value=""><?=lang('messages_lang.label_selecte')?></option>
                                                         <?php  foreach ($economie as $key) { ?>
                                                          <?php  if ($key->CODE_SOUS_LITTERA ==$activites['NATURE_ECONOMIQUE'] ) { ?>
                                                            <option value="<?=$key->SOUS_LITTERA_ID ?>" selected>
                                                              <?=$key->LIBELLE_SOUS_LITTERA?></option>
                                                            <?php }else{?>
                                                              <option value="<?=$key->SOUS_LITTERA_ID ?>">
                                                                <?=$key->LIBELLE_SOUS_LITTERA?></option>
                                                              <?php } }?>
                                                            </select>
                                                            <?php if (isset($validation)) : ?>
                                                              <?= $validation->getError('INTITULE_NATURE_ECONOMIQUE'); ?>
                                                            <?php endif ?>
                                                            <span class="text-danger" id="error_INTITULE_NATURE_ECONOMIQUE"></span>
                                                            <span style="font-size: 10px" id="getNumberIntituleNature"></span>
                                                          </div>
                                                        </div>
                                                        <div class="col-6">
                                                          <div class="form-group">
                                                            <label><?=lang('messages_lang.labelle_intitule_economique')?><span style="color: red;">*</span></label>
                                                            
                                                            <input onkeyup="SetMaxLength(26)" autocomplete="off" type="text" name="NATURE_ECONOMIQUE" id="code_natire" class="form-control" value="<?=$activites['NATURE_ECONOMIQUE']?>">
                                                            <?php if (isset($validation)) : ?>
                                                              <?= $validation->getError('NATURE_ECONOMIQUE'); ?>
                                                            <?php endif ?>
                                                            <span class="text-danger" id="error_NATURE_ECONOMIQUE"></span>
                                                            <span style="font-size: 10px" id="getNumberNatureEconomique"></span>
                                                          </div>
                                                        </div>


                                                      </div>
                                                    </div>

                                                    <div style="border:1px solid #ddd;border-radius:5px;margin: 5px;background-color:#fff">
                                                      <div class="row" style="margin :  5px">
                                                        <div class="col-12">
                                                          <h4><center> <i class="fa fa-circle"></i> <?=lang('messages_lang.labelle_information_fonctionnelle')?></center></h4>
                                                        </div>
                                                        <div class="col-4">
                                                          <div class="form-group">
                                                            <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_intitule_division')?><span style="color: red;">*</span></label>
                                                            
                                                            <select class=" form-control form-select bg-light" name="INTITULE_DIVISION" id="INTITULE_DIVISION" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example"  onchange="code_groupe();">
                                                              <option value=""><?=lang('messages_lang.label_selecte')?> </option>
                                                              <?php if (isset($validation)) : ?>
                                                                <?php
                                                                foreach($fonctionnelle_division as $key)
                                                                {
                                                                  if($key->CODE_DIVISION==$activites['DIVISION_FONCTIONNELLE'])
                                                                  {
                                                                    echo "<option value='".$key->DIVISION_ID."'  selected>".str_replace("\\", "", $key->LIBELLE_DIVISION 
                                                                  )."</option>";
                                                                  }
                                                                  else
                                                                  {
                                                                    echo "<option value='".$key->DIVISION_ID."' >".str_replace("\\", "", $key->LIBELLE_DIVISION 
                                                                  )."</option>";
                                                                  }
                                                                }
                                                                ?>
                                                              <?php endif ?>
                                                            </select>
                                                            
                                                            <?php if (isset($validation)) : ?>
                                                              <?= $validation->getError('INTITULE_DIVISION'); ?>
                                                            <?php endif ?>
                                                            <span id="error_INTITULE_DIVISION" class="text-danger"></span>
                                                          </div>
                                                        </div>
                                                        <div class="col-4">
                                                          <div class="form-group">
                                                            <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_intitule_groupe')?><span style="color: red;">*</span></label>
                                                            
                                                            <select class=" form-control form-select bg-light" onchange="code_classe()" name="INTITULE_GROUPE" id="INTITULE_GROUPE" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                                              <option value=""><?=lang('messages_lang.labelle_Séléctioner')?> </option>
                                                              <?php if (isset($validation)) : ?>
                                                                <?php
                                                                foreach($fonctionnelle_groupe as $key)
                                                                {
                                                                  if($key->CODE_GROUPE==$activites['GROUPE_FONCTIONNELLE'])
                                                                  {
                                                                    echo "<option value='".$key->GROUPE_ID."'  selected>".str_replace("\\", "", $key->LIBELLE_GROUPE 
                                                                  )."</option>";
                                                                  }
                                                                  else
                                                                  {
                                                                    echo "<option value='".$key->GROUPE_ID."' >".str_replace("\\", "", $key->LIBELLE_GROUPE 
                                                                  )."</option>";
                                                                  }
                                                                }
                                                                ?>
                                                              <?php endif ?>
                                                            </select>
                                                            <?php if (isset($validation)) : ?>
                                                              <?= $validation->getError('INTITULE_GROUPE'); ?>
                                                            <?php endif ?>
                                                            <span id="error_INTITULE_GROUPE" class="text-danger"></span>
                                                          </div>
                                                        </div>
                                                        <div class="col-4">
                                                          <div class="form-group">
                                                            <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_intitule_classe')?><span style="color: red;">*</span></label>
                                                            
                                                            <select class=" form-control form-select bg-light" name="INTITULE_CLASSE" id="INTITULE_CLASSE" placeholder="Sélectionnez l'action" autocomplete="off" aria-label=".form-select-lg example">
                                                             <option value=""><?=lang('messages_lang.label_selecte')?> </option>
                                                             <?php if (isset($validation)) : ?>
                                                              <?php
                                                              foreach($fonctionnelle_classe as $key)
                                                              {
                                                                if($key->CODE_CLASSE==$activites['CLASSE_FONCTIONNELLE'])
                                                                {
                                                                  echo "<option value='".$key->CLASSE_ID."'  selected>".str_replace("\\", "", $key->LIBELLE_CLASSE 
                                                                )."</option>";
                                                                }
                                                                else
                                                                {
                                                                  echo "<option value='".$key->CLASSE_ID."' >".str_replace("\\", "", $key->LIBELLE_CLASSE 
                                                                )."</option>";
                                                                }
                                                              }
                                                              ?>
                                                            <?php endif ?>
                                                            
                                                          </select>
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('INTITULE_CLASSE'); ?>
                                                          <?php endif ?>
                                                          <span id="error_INTITULE_CLASSE" class="text-danger"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-4">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_code_division')?><span style="color: red;">*</span></label>

                                                          <input onkeyup="SetMaxLength(26)" autocomplete="off" type="text" name="CODE_DIVISION" id="CODE_DIVISION" class="form-control" value="<?=$activites['DIVISION_FONCTIONNELLE']?>">
                                                          
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('CODE_DIVISION'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_CODE_DIVISION"></span>
                                                          <span style="font-size: 10px" id="getNumberCodeDivision"></span>
                                                        </div>
                                                      </div>
                                                      <div class="col-4">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_code_groupe')?><span style="color: red;">*</span></label>
                                                          
                                                          <input onkeyup="SetMaxLength(27)" autocomplete="off" type="text" name="CODE_GROUPE" id="CODE_GROUPE" class="form-control" value="<?=$activites['GROUPE_FONCTIONNELLE']?>"> 
                                                          
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('CODE_GROUPE'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_CODE_GROUPE"></span>
                                                          <span style="font-size: 10px" id="getNumberCodeGroupe"></span>
                                                        </div>
                                                      </div>
                                                      <div class="col-4">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_code_classe')?><span style="color: red;">*</span></label>
                                                          <input onkeyup="SetMaxLength(28)" autocomplete="off" type="text" name="CODE_CLASSE" id="CODE_CLASSE" class="form-control" value="<?=$activites['CLASSE_FONCTIONNELLE']?>">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('CODE_CLASSE'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_CODE_CLASSE"></span>
                                                          <span style="font-size: 10px" id="getNumberCodeClasse"></span>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>

                                                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px;background-color:#fff">
                                                    <div class="row" style="margin :  5px">
                                                      <div class="col-12">
                                                        <h4><center> <i class="fa fa-money-check"></i> <?=lang('messages_lang.labelle_montant_par_trimestre')?></center></h4>
                                                      </div>

                                                      <div class="col-6">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_cout_unitaire')?><span style="color: red;">*</span></label>
                                                          <input onkeyup="SetMaxLength(11)" autocomplete="off" type="text" name="COUT_UNITAIRE_BIF" id="COUT_UNITAIRE_BIF" class="form-control" value="<?=$activites['COUT_UNITAIRE_BIF']?>">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('COUT_UNITAIRE_BIF'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Cout_unitaire"></span>
                                                          <span style="font-size: 10px" id="getNumberCoutUnitaire"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-6">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_unite')?><span style="color: red;">*</span></label>
                                                          <input onkeyup="SetMaxLength(12)" autocomplete="off" type="text" name="UNITE" id="UNITE"  class="form-control" value="<?=$activites['UNITE'] ?>">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('UNITE'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Unite"></span>
                                                          <span style="font-size: 10px" id="getNumberUnite"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_quantite')?> T1<span style="color: red;">*</span></label>
                                                          <input autocomplete="off" type="text" onkeyup="getSubstring(1);SetMaxLength(13)" name="QT1" id="QT1" class="form-control" value="<?=$activites['QT1']?>">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('QT1'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Quantite1"></span>
                                                          <span style="font-size: 10px" id="getNumberQT1"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_quantite')?> T2<span style="color: red;">*</span></label>
                                                          <input autocomplete="off" type="text" name="QT2" onkeyup="getSubstring(2);SetMaxLength(14)" id="QT2"  value="<?=$activites['QT2']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('QT2'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Quantite2"></span>
                                                          <span style="font-size: 10px" id="getNumberQT2"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_quantite')?> T3<span style="color: red;">*</span></label>
                                                          <input autocomplete="off" type="text" onkeyup="getSubstring(3);SetMaxLength(15)" name="QT3" id="QT3" class="form-control"  value="<?=$activites['QT3']?>">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('QT3'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Quantite3"></span>
                                                          <span style="font-size: 10px" id="getNumberQT3"></span>
                                                        </div>
                                                      </div>


                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_quantite')?> T4<span style="color: red;">*</span></label>
                                                          <input autocomplete="off" type="text" name="QT4" onkeyup="getSubstring(4);SetMaxLength(16)"id="QT4"  value="<?=$activites['QT4']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('QT4'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Quantite4"></span>
                                                          <span style="font-size: 10px" id="getNumberQT4"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_montant')?> T1<span style="color: red;">*</span></label>
                                                          <input type="text" name="T1" id="T1" value="<?=$activites['T1']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('T1'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_T1"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_montant')?> T2<span style="color: red;">*</span></label>
                                                          <input type="text" name="T2" id="T2" value="<?=$activites['T2']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('T2'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_T2"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_montant')?> T3<span style="color: red;">*</span></label>
                                                          <input type="text" name="T3" id="T3" value="<?=$activites['T3']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('T3'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_T3"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-3">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_montant')?><span style="color: red;">*</span></label>
                                                          <input type="text" name="T4" id="T4" value="<?=$activites['T4']?>" class="form-control">
                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('T4'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_T4"></span>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>


                                                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px;background-color:#fff">
                                                    <div class="row" style="margin :  5px">
                                                      <div class="col-12">
                                                        <h4><center> <i class="fa fa-list"></i> <?=lang('messages_lang.labelle_grandes_masses')?></center></h4>
                                                      </div>
                                                      <br/>
                                                      <div class="col-12">
                                                        <div class="form-group">
                                                          <label><?=lang('messages_lang.labelle_intitule')?><span style="color: red;">*</span></label>
                                                          <select onchange="get_code_int();" class=" form-control" name="INTITULE_DES_GRANDES_MASSE" id="masse_grands">
                                                            <option value=""><?=lang('messages_lang.labelle_Séléctioner')?> </option>
                                                            <?php if (isset($validation)) : ?>
                                                              <?php
                                                              foreach($masse_classe as $key)
                                                              {
                                                                if($key->GRANDE_MASSE_ID==$activites['GRANDE_MASSE_BP'])
                                                                {
                                                                  echo "<option value='".$key->GRANDE_MASSE_ID."'  selected>".str_replace("\\", "", $key->DESCRIPTION_GRANDE_MASSE 
                                                                )."</option>";
                                                                }
                                                                else
                                                                {
                                                                  echo "<option value='".$key->GRANDE_MASSE_ID."' >".str_replace("\\", "", $key->DESCRIPTION_GRANDE_MASSE 
                                                                )."</option>";
                                                                }
                                                              }
                                                              ?>
                                                            <?php endif ?>
                                                          </select>



                                                          <?php if (isset($validation)) : ?>
                                                            <?= $validation->getError('INTITULE_DES_GRANDES_MASSES'); ?>
                                                          <?php endif ?>
                                                          <span class="text-danger" id="error_Institution_Grande_Masse"></span>
                                                          <span style="font-size: 10px" id="getNumberIntituleGrandeMasse"></span>
                                                        </div>
                                                      </div>

                                                      <div class="col-6">
                                                        <div class="form-group">
                                                         <label><?=lang('messages_lang.labelle_budget_programme')?><span style="color: red;">*</span></label>
                                                         <input onkeyup="SetMaxLength(18)" type="text" name="GRANDE_MASSE_BP" id="GRANDE_MASSE_BP" class="form-control" value="<?=$activites['GRANDE_MASSE_BP']?>">
                                                         <?php if (isset($validation)) : ?>
                                                          <?= $validation->getError('GRANDE_MASSE_BP'); ?>
                                                        <?php endif ?>
                                                        <span class="text-danger" id="error_Grande_Masse_Budget_Programme"></span>
                                                        <span style="font-size: 10px" id="getNumberGrandeMasse"></span>
                                                      </div>
                                                    </div>

                                                    <input onkeyup="SetMaxLength(20)" type="hidden" name="GRANDE_MASSE_BM" id="GRANDE_MASSE_BM" class="form-control" >
                                                    <input onkeyup="SetMaxLength(19)" type="hidden" name="GRANDE_MASSE_BM1" id="GRANDE_MASSE_BM1" class="form-control">

<div class="col-6">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_responsable')?><span style="color: red;">*</span></label>
   <input onkeyup="SetMaxLength(21)" type="text" name="RESPONSABLE" id="RESPONSABLE" value="<?=$activites['RESPONSABLE']?>" class="form-control">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('RESPONSABLE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Responsable"></span>
  <span style="font-size: 10px" id="getNumberResponsable"></span>
</div>
</div>

</div>
</div>

<input type="hidden" name="set_code_budg" id="set_code_budg" value="<?=$set_code_budg?>">
<input type="hidden" name="set_ACTION_ID" id="set_ACTION_ID" value="">
<input type="hidden" name="RowiD" value="<?=$activites['PTBA_PROGR_BUDG_ID_Tempo']?>">
<div class="col-12">
  <button style="float: right;" id="btnSave" type="button" onclick="insert()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;Modifier</button>

</div>
</div>
</div>
</form> 

<div class="row">
  <div class="table-responsive" style="width: 100%;">
    <table id="mytable" class=" table table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>                              
          <th><?=lang('messages_lang.th_instit')?></th>
          <th><?=lang('messages_lang.th_programme')?></th>
          <th><?=lang('messages_lang.th_action')?></th>
          <th><?=lang('messages_lang.th_code_budgetaire')?></th>
          <th><?=lang('messages_lang.code_prog')?></th>
          <th><?=lang('messages_lang.th_activite')?></th>
          <th><?=lang('messages_lang.result_attend')?></th>
          <th><?=lang('messages_lang.th_articl_eco')?></th>
          <th><?=lang('messages_lang.th_natur_eco')?></th>
          <th><?=lang('messages_lang.th_intit_art_eco')?></th>
          <th><?=lang('messages_lang.th_intit_nat_eco')?></th>
          <th><?=lang('messages_lang.th_code_division')?></th>
          <th><?=lang('messages_lang.th_intit_division')?></th>
          <th><?=lang('messages_lang.th_code_group')?></th>
          <th><?=lang('messages_lang.th_intit_group')?></th>
          <th><?=lang('messages_lang.th_code_class')?></th>
          <th><?=lang('messages_lang.th_intit_class')?></th>
          <th><?=lang('messages_lang.th_cout_unit')?></th>
          <th><?=lang('messages_lang.table_unite')?></th>
          <th><?=lang('messages_lang.th_quantite')?>&nbspT1</th>
          <th><?=lang('messages_lang.th_quantite')?>&nbspT2</th>
          <th><?=lang('messages_lang.th_quantite')?>&nbspT3</th>
          <th><?=lang('messages_lang.th_quantite')?>&nbspT4</th>
          <th><?=lang('messages_lang.th_montant')?>&nbspT1</th>
          <th><?=lang('messages_lang.th_montant')?>&nbspT2</th>
          <th><?=lang('messages_lang.th_montant')?>&nbspT3</th>
          <th><?=lang('messages_lang.th_montant')?>&nbspT4</th>
          <th><?=lang('messages_lang.th_intit_gm')?></th>
          <th><?=lang('messages_lang.th_budg_prog')?></th>
          <th><?=lang('messages_lang.th_respo')?></th>
          <th><?=lang('messages_lang.th_action')?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
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

<div class="modal fade" id="mydelete">
 <div class="modal-dialog">
  <div class="modal-content">
   <div class="modal-body">
     <input type="hidden" id="RowId_process" name="RowidOne">
   </div>
   <div class="modal-footer">
    <!-- <a class="btn btn-danger btn-md" href="' .base_url('ihm/delete_data/'.$row->ID_PILIER).'">Oui</a> -->
    
    <center>
      <h4><strong><?= lang('messages_lang.msg_del_activ')?></strong><br> <b style='background-color:prink;'> <a class="btn btn-secondary  dropdown" aria-hidden="true" data-toggle="dropdown" aria-expanded="false"  onclick="remove_data(this)"><?= lang('messages_lang.label_oui')?></a></b>
      </h4>
    </center>
    
    <div style="float:right; margin-right:115px;margin-top:-75px;">

     <button class="btn btn-secondary" class="close" data-dismiss="modal"><?=lang('messages_lang.bouttonNon')?></button>
     
   </div>
 </div>
</div>
</div>
</div>

<script type="text/javascript">

  $(function(){
    $('#INTITULE_CLASSE').change(function(){
      var CLASSE_ID=$('#INTITULE_CLASSE').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_class_id')?>",
          method: "POST",
          data: 'CLASSE_ID='+CLASSE_ID,
          async: true,
          dataType: 'json',
          success:function(data){
var i;
for(i=0;i<data.length;i++){

  $('#CODE_CLASSE').val(''+data[i].CODE_CLASSE);
}
}
});
      })
  });

  $(function(){
    $('#INTITULE_GROUPE').change(function(){
      var Groupe_ID=$('#INTITULE_GROUPE').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_groupes')?>",
          method: "POST",
          data: 'Groupe_ID='+Groupe_ID,
          async: true,
          dataType: 'json',
          success:function(data){
var i;
for(i=0;i<data.length;i++){
  $('#CODE_GROUPE').val(''+data[i].CODE_GROUPE);
}
}
});
      })
  });



  $(function(){
    $('#INTITULE_DIVISION').change(function(){
      var INTITULE_DIVISION=$('#INTITULE_DIVISION').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_divisions')?>",
          method: "POST",
          data: 'Division_ID='+INTITULE_DIVISION,
          async: true,
          dataType: 'json',
          success:function(data){
var i;
for(i=0;i<data.length;i++){
  $('#CODE_DIVISION').val(''+data[i].CODE_DIVISION);
}
}
});
      })
  });


// sous littera
$(function(){
  $('#ACTION_ID').change(function(){
        var Action_ID=$('#ACTION_ID').val();
        $.ajax({
          url: "<?=base_url('process/Dem_Liste_Activites/code_programmatique')?>",
          method: "POST",
          data: 'Action_ID='+Action_ID,
          async: true,
          dataType: 'json',
          success:function(data){
var i;
for(i=0;i<data.length;i++){

  $('#CODES_PROGRAMMATIQUE').val(''+data[i].CODE_ACTION);
}

}
});
      })
});

$(function(){
  $('#SOUS_TUTEL_ID').change(function(){
        
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_sous_tutels')?>",
          method: "POST",
          data: 'SOUS_TUTEL_ID='+SOUS_TUTEL_ID,
          async: true,
          dataType: 'json',
          success:function(data){
            alert(data);
            var i;
            for(i=0;i<data.length;i++){

              $('#ID_SOUS_TUTEL_ALL').val(''+data[i].CODE_SOUS_TUTEL);
            }

          }
        });
      })
});

$(function(){
  $('#INTITULE_NATURE_ECONOMIQUE').change(function(){
        var SOUS_LITTERA_ID=$('#INTITULE_NATURE_ECONOMIQUE').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_sous_littera')?>",
          method: "POST",
          data: 'SOUS_LITTERA_ID='+SOUS_LITTERA_ID,
          async: true,
          dataType: 'json',
          success:function(data)
          {
            $('#ID_SOUS_LITTERAS_ALL').val(data.pragraph);
            $('#code_natire').val(data.pragraph);
          }
        });
      })
});

$(function(){
  $('#INTITULE_CLASSE').change(function(){
        var classe_ID=$('#INTITULE_CLASSE').val();
       $.ajax({
        
        url: "<?=base_url('process/Dem_Liste_Activites/code_classes')?>",
        method: "POST",
        data: 'classe_ID='+classe_ID,
        async: true,
        dataType: 'json',
        success:function(data){
                  // alert(data);
                  var i;
                  for(i=0;i<data.length;i++){
                    $('#ID_SOUS_LITTERAS').val(''+data[i].CODE_CLASSE);
                  }

                }
              });
     })
});
  // get code paragraphe
  $(function(){
    $("#masse_grands").change(function(){
});

  });
  
  $(function(){
    $('#PARAGRAPHE').change(function(){
        var ID_PARAGRAPHE=$('#PARAGRAPHE').val();
       $.ajax({
        
        url: "<?=base_url('process/Dem_Liste_Activites/codeparagraphe')?>",
        method: "POST",
        data: 'PARAGRAPHE_ID='+ID_PARAGRAPHE,
        async: true,
        dataType: 'json',
        success:function(data){
                  // alert(data);
                  var i;
                  for(i=0;i<data.length;i++){

                    $('#ID_PARAGRAPHES').val(''+data[i].codeparagraphe);
                  }

                }
              });
     })
  });
  // get code division
  
  $(function(){
    $('#INTITULE_DIVISION').change(function(){
        var ID_DIVISION=$('#INTITULE_DIVISION').val();
        $.ajax({
          
          url: "<?=base_url('process/Dem_Liste_Activites/code_division')?>",
          method: "POST",
          data: 'DIVISION_ID='+ID_DIVISION,
          async: true,
          dataType: 'json',
          success:function(data){
                  var i;
                  for(i=0;i<data.length;i++){
                    $('#ID_DIVISIONS').val(''+data[i].CODE_DIVISION);
                  }

                }
              });
      })
  });
  // get Code article
  
  $(function(){
    $('.code_articles').change(function(){
        var ID_ARTicle=$('.code_articles').val();
        $.ajax({        
          url: "<?=base_url('process/Dem_Liste_Activites/code_article')?>",
          method: "POST",
          data: 'ID_ARTicle='+ID_ARTicle,
          async: true,
          dataType: 'json',
          success:function(data){
                  var i;
                  for(i=0;i<data.length;i++){
                    $('#CODE_ARTICLES123').val(''+data[i].CODE_ARTICLE); 
                    $('#CODE_ARTICLES').val(''+data[i].CODE_ARTICLE);
                  }
                }
              });
      })
  });

  function get_code_int()
  {
    var INTITULE_DES_GRANDES_MASSES = $('#masse_grands').val();
    if(INTITULE_DES_GRANDES_MASSES=='')
    {
      $('#gde_mas').val();
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/process/Dem_Liste_Activites/get_code_int/"+INTITULE_DES_GRANDES_MASSES,
        type:"POST",
        dataType:"JSON",
        success: function(data)
        {
          $('#GRANDE_MASSE_BP').val(data.code_gde);
        }
      });
    }
  }

  function code_classe(){
    var GROUPE_ID=$('#INTITULE_GROUPE').val();
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/code_classe')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    GROUPE_ID:GROUPE_ID
    
  },

  success:function(data){ 

    $('#INTITULE_CLASSE').html(data.tutel);
  },            

});  

}

function code_groupe(){

  var CODE_DIVISION=$('#INTITULE_DIVISION').val();
//alert(''+CODE_DIVISION);
//alert("Bojr");
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/code_groupe')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    CODE_DIVISION:CODE_DIVISION
    
  },

  success:function(data){ 

    $('#INTITULE_GROUPE').html(data.tutel);

  // var x= $("#CODE_ARTICLES").val(data.code_article);
  // alert(x);

    //alert(data);
  },            

});  

}

function create_get_sous_littera(){

  var LITTERA_ID=$('#LITTERA').val();
//alert(''+CODE_PARAGRAPHE);
//alert("Bojr");
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/create_get_sous_littera')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    LITTERA_ID:LITTERA_ID
    
  },

  success:function(data){   
    $('#INTITULE_NATURE_ECONOMIQUE').html(data.tutel);
  // var x= $("#CODE_ARTICLES").val(data.code_article);
  // alert(x);

    //alert(data);
  },            

});  

}


function create_get_Code_littera(){

  var PARAGRAPHE_ID=$('#PARAGRAPHE').val();
  $.ajax({
    
    url : "<?=base_url('process/Dem_Liste_Activites/create_get_Code_littera')?>",
    type : "POST",
    dataType: "JSON",
    cache:false,
    data:{
      PARAGRAPHE_ID:PARAGRAPHE_ID
      
    },

    success:function(data){   
      $('#LITTERA').html(data.tutel);
    
    },            

  });  

}



function create_get_code_paragraphe(){
  var ARTICLE_ID=$(".code_articles").val();
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/create_get_Code_paragraphe')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    ARTICLE_ID:ARTICLE_ID
    
  },

  success:function(data){   
    $('.paragraphe_code').html(data.tutel);
  
  },            

});  

}

function create_get_code_article(){

  var CHAPITRE_ID=$('#CHAPITRES').val();
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/create_get_Code_article')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    CHAPITRE_ID:CHAPITRE_ID
    
  },

  success:function(data){   
    $('#INTITUE_ARTICLE_ECONOMIQUE').html(data.tutel);
  },            

});  

}

 // generation code Budgetaire
 $(document).ready(function()
 {
  document.getElementById('CODES_PROGRAMMATIQUE').readOnly = true;
//document.getElementById('CODE_NOMENCLATURE_BUDGETAIRE').readOnly = true;
document.getElementById('CODE_DIVISION').readOnly = true;
document.getElementById('CODE_GROUPE').readOnly = true;
document.getElementById('CODE_CLASSE').readOnly = true;
    //,#masse_grands,#CODE_ARTICLES,#CHAPITRES,#INTITULE_GROUPE,#ID_PARAGRAPHES,#ID_SOUS_LITTERAS
    $("#CODE_INSTITUTION,#ID_SOUS_TUTEL_ALL,#ID_SOUS_LITTERAS_ALL,#ID_SOUS_LITTERAS,#masse_grands").on('input', function()
    {

      $(this).val(this.value.substring(0,50));
      
      $(this).val($(this).val().replace(/[^0-9.]*$/gi, ''));
var myElement = document.getElementById('SOUS_TUTEL_ID'),
myElementValue =myElement.value;
var SOUS_TUTEL_ID=$("#ID_SOUS_TUTEL_ALL").val();
var CODE_INSTITUTION=$("#CODE_INSTITUTION").val();
var SOUS_LITTERA=$("#ID_SOUS_LITTERAS").val();
var ID_SOUS_LITTERAS_ALL=$("#ID_SOUS_LITTERAS_ALL").val();
});
  });

</script>
<!-- Bonfils de Jésus -->
<script type="text/javascript">


  // supprimer
  function delete_row(Rowid){
    $("#RowId_process").val(Rowid);
    $("#mydelete").modal("show");

  }
//boutton supprimer
function remove_data(){
 
 var id=$("#RowId_process").val();
$.post('<?=base_url('process/supprimer')?>',
{
  id:id,
  
},function(data){
 if(data){
   window.location.href="<?= base_url('process/Demandes')?>";
 }
 });

}
function SetMaxLength (id) {
  var NOM = $('#NOM').val().length;  
  var PRENOM = $('#PRENOM').val().length;  
  var POSTE = $('#POSTE').val().length; 
  var CODES_PROGRAMMATIQUE = $('#CODES_PROGRAMMATIQUE').val().length; 
  var ARTICLE_ECONOMIQUE = $('#ARTICLE_ECONOMIQUE').val().length; 
  var NATURE_ECONOMIQUE = $('#NATURE_ECONOMIQUE').val().length; 
  var ACTIVITES = $('#ACTIVITES').val().length; 
  var RESULTATS_ATTENDUS = $('#RESULTATS_ATTENDUS').val().length; 
  var INTITULE_ARTICLE_ECONOMIQUE = $('#INTITULE_ARTICLE_ECONOMIQUE').val().length; 
  var INTITULE_NATURE_ECONOMIQUE = $('#INTITULE_NATURE_ECONOMIQUE').val().length; 
  var COUT_UNITAIRE_BIF = $('#COUT_UNITAIRE_BIF').val().length; 
  var UNITE = $('#UNITE').val().length; 
  var QT1 = $('#QT1').val().length; 
  var QT2 = $('#QT2').val().length; 
  var QT3 = $('#QT3').val().length; 
  var QT4 = $('#QT4').val().length; 
  var INTITULE_DES_GRANDES_MASSES = $('#INTITULE_DES_GRANDES_MASSES').val().length; 
  var GRANDE_MASSE_BP = $('#GRANDE_MASSE_BP').val().length; 
  var GRANDE_MASSE_BM1 = $('#GRANDE_MASSE_BM1').val().length; 
  var GRANDE_MASSE_BM = $('#GRANDE_MASSE_BM').val().length; 
  var RESPONSABLE = $('#RESPONSABLE').val().length;
  var CODE_NOMENCLATURE_BUDGETAIRE = $('#CODE_NOMENCLATURE_BUDGETAIRE').val().length;
  var CODE_DIVISION = $('#CODE_DIVISION').val().length;
  var CODE_GROUPE = $('#CODE_GROUPE').val().length;
  var CODE_CLASSE = $('#CODE_CLASSE').val().length;

  if (id==1) {
    $('#getNumberNom').text("");
    if (NOM!=0) {
      $('#getNumberNom').text(""+NOM+"/50");
    }
  }else if (id==2) {
    $('#getNumberPrenom').text("");
    if (PRENOM!=0) {
      $('#getNumberPrenom').text(""+PRENOM+"/50");
    }
  }else if (id==3) {
    $('#getNumberPoste').text("");
    if (POSTE!=0) {
      $('#getNumberPoste').text(""+POSTE+"/50");
    }
  }else if (id==4) {
    console.log('code_programmatique');
    $('#getNumberCodeProgramme').text("");
    if (CODES_PROGRAMMATIQUE!=0) {
      $('#getNumberCodeProgramme').text(""+CODES_PROGRAMMATIQUE+"/7");
    }
  }else if (id==5) {
    $('#getNumberArticleEconomique').text("");
    if (ARTICLE_ECONOMIQUE!=0) {
      $('#getNumberArticleEconomique').text(""+ARTICLE_ECONOMIQUE+"/2");
    }
  }else if (id==6) {
    $('#getNumberNatureEconomique').text("");
    if (NATURE_ECONOMIQUE!=0) {
      $('#getNumberNatureEconomique').text(""+NATURE_ECONOMIQUE+"/5");
    }
  }else if (id==7) {
    $('#getNumberActivite').text("");
    if (ACTIVITES!=0) {
      $('#getNumberActivite').text(""+ACTIVITES+"/2000");
    }
  }else if (id==8) {
    $('#getNumberResultatAttendus').text("");
    if (RESULTATS_ATTENDUS!=0) {
      $('#getNumberResultatAttendus').text(""+RESULTATS_ATTENDUS+"/3000");
    }
  }else if (id==9) {
    $('#getNumberIntituleArticle').text("");
    if (INTITULE_ARTICLE_ECONOMIQUE!=0) {
      $('#getNumberIntituleArticle').text(""+INTITULE_ARTICLE_ECONOMIQUE+"/500");
    }
  }else if (id==10) {
    $('#getNumberIntituleNature').text("");
    if (INTITULE_NATURE_ECONOMIQUE!=0) {
      $('#getNumberIntituleNature').text(""+INTITULE_NATURE_ECONOMIQUE+"/500");
    }
  }else if (id==11) {
    $('#getNumberCoutUnitaire').text("");
    if (COUT_UNITAIRE_BIF!=0) {
      $('#getNumberCoutUnitaire').text(""+COUT_UNITAIRE_BIF+"/50");
    }
  }else if (id==12) {
    $('#getNumberUnite').text("");
    if (UNITE!=0) {
      $('#getNumberUnite').text(""+UNITE+"/100");
    }
  }else if (id==13) {
    $('#getNumberQT1').text("");
    if (QT1!=0) {
      $('#getNumberQT1').text(""+QT1+"/50");
    }
  }else if (id==14) {
    $('#getNumberQT2').text("");
    if (QT2!=0) {
      $('#getNumberQT2').text(""+QT2+"/50");
    }
  }else if (id==15) {
    $('#getNumberQT3').text("");
    if (QT3!=0) {
      $('#getNumberQT3').text(""+QT3+"/50");
    }
  }else if (id==16) {
    $('#getNumberQT4').text("");
    if (QT4!=0) {
      $('#getNumberQT4').text(""+QT4+"/50");
    }
  }else if (id==17) {
    $('#getNumberIntituleGrandeMasse').text("");
    if (INTITULE_DES_GRANDES_MASSES!=0) {
      $('#getNumberIntituleGrandeMasse').text(""+INTITULE_DES_GRANDES_MASSES+"");
    }
  }else if (id==18) {
    $('#getNumberGrandeMasse').text("");
    if (GRANDE_MASSE_BP!=0) {
      $('#getNumberGrandeMasse').text(""+GRANDE_MASSE_BP+"/50");
    }
  }else if (id==19) {
    $('#getNumberGrandeMasseM1').text("");
    if (GRANDE_MASSE_BM1!=0) {
      $('#getNumberGrandeMasseM1').text(""+GRANDE_MASSE_BM1+"/50");
    }
  }else if (id==20) {
    $('#getNumberGrandeMasseM').text("");
    if (GRANDE_MASSE_BM!=0) {
      $('#getNumberGrandeMasseM').text(""+GRANDE_MASSE_BM+"/50");
    }
  }else if (id==21) {
    $('#getNumberResponsable').text("");
    if (RESPONSABLE!=0) {
      $('#getNumberResponsable').text(""+RESPONSABLE+"/500");
    }
  }
  else if (id==22) {
    $('#error_code_budget').text("");
    if (CODE_NOMENCLATURE_BUDGETAIRE!=0) {
      $('#error_code_budget').text(""+CODE_NOMENCLATURE_BUDGETAIRE+"/30");
    }
  }
  else if (id==23) {
    $('#error_CODE_DIVISION').text("");
    if (CODE_DIVISION!=0) {
      $('#error_CODE_DIVISION').text(""+CODE_DIVISION+"");
    }
  }
  else if (id==24) {
    $('#error_CODE_GROUPE').text("");
    if (CODE_GROUPE!=0) {
      $('#error_CODE_GROUPE').text(""+CODE_GROUPE+"");
    }
  }
  else if (id==25) {
    $('#error_CODE_CLASSE').text("");
    if (CODE_CLASSE!=0) {
      $('#error_CODE_CLASSE').text(""+CODE_CLASSE+"");
    }
  }
  else if (id==26) {
    $('#error_CODE_CLASSE').text("");
    if (CODE_CLASSE!=0) {
      $('#error_CODE_CLASSE').text(""+CODE_CLASSE+"");
    }
  }
  else if (id==27) {
    $('#error_CODE_CLASSE').text("");
    if (CODE_CLASSE!=0) {
      $('#error_CODE_CLASSE').text(""+CODE_CLASSE+"");
    }
  }
  else if (id==28) {
    $('#error_CODE_CLASSE').text("");
    if (CODE_CLASSE!=0) {
      $('#error_CODE_CLASSE').text(""+CODE_CLASSE+"");
    }
  }
}


function getSubstring(id) {

  var QT1 = $('#QT1').val();
  var QT2 = $('#QT2').val();
  var QT3 = $('#QT3').val();
  var QT4 = $('#QT4').val();

  if (id==1) {
    var getNumber = QT1.substring(0, 1);
    if (getNumber==0) {
      $('#QT1').val('');
    }
  }else if (id==2) {
    var getNumber = QT2.substring(0, 1);
    if (getNumber==0) {
      $('#QT2').val('');
    }
  }else if (id==3) {
    var getNumber = QT3.substring(0, 1);
    if (getNumber==0) {
      $('#QT3').val('');
    }
  }else if (id==4) {
    var getNumber = QT4.substring(0, 1);
    if (getNumber==0) {
      $('#QT4').val('');
    }
  }
}
</script>

<script type="text/javascript">
  $(document).ready(function()
  {
    document.getElementById('T1').readOnly = true;
    document.getElementById('T2').readOnly = true;
    document.getElementById('T3').readOnly = true;
    document.getElementById('T4').readOnly = true;
    

    $("#COUT_UNITAIRE_BIF, #QT1, #QT2, #QT3, #QT4").on('input', function()
    {

      $(this).val(this.value.substring(0,50));

      $(this).val($(this).val().replace(/[^0-9.]*$/gi, ''));

      var COUT_UNITAIRE_BIF = $('#COUT_UNITAIRE_BIF').val();

      var quantites = ['#QT1', '#QT2', '#QT3', '#QT4'];
      var resultats = ['#T1', '#T2', '#T3', '#T4'];

      for (var i = 0; i < quantites.length; i++) {
        var inputVal = $(quantites[i]).val();
        var resultVal = parseFloat(inputVal) * parseFloat(COUT_UNITAIRE_BIF) || 0;
        $(resultats[i]).val(resultVal);
      }       
    });
  });

</script>

<script type="text/javascript">

  $("#ARTICLE_ECONOMIQUE, #NATURE_ECONOMIQUE, #INTITULE_ARTICLE_ECONOMIQUE, #INTITULE_NATURE_ECONOMIQUE").on('input', function()
  {
    var maxLength;

    if(this.id === "ARTICLE_ECONOMIQUE"){

      maxLength = 2;
      $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));

    }else if(this.id === "INTITULE_ARTICLE_ECONOMIQUE" || this.id === "INTITULE_NATURE_ECONOMIQUE"){

      maxLength = 500;
    }else if(this.id === "NATURE_ECONOMIQUE"){

      maxLength = 5;
      $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
    }

    $(this).val(this.value.substring(0, maxLength));


  });


  $("#CODES_PROGRAMMATIQUE, #GRANDE_MASSE_BM, #GRANDE_MASSE_BM1, #GRANDE_MASSE_BP,#CODE_NOMENCLATURE_BUDGETAIRE,#CODE_DIVISION,#CODE_GROUPE,#CODE_CLASSE").on('input', function()
  {
    var maxLength;

    if (this.id === "CODES_PROGRAMMATIQUE") {
      maxLength = 7;
    } else if (this.id === "GRANDE_MASSE_BM" || this.id === "GRANDE_MASSE_BM1" || this.id === "GRANDE_MASSE_BP") {
      maxLength = 50;
    }
    else if(this.id === "CODE_NOMENCLATURE_BUDGETAIRE"){
      maxlength = 30;
    }
    else if(this.id === "CODE_GROUPE" || this.id === "CODE_CLASSE" || this.id === "CODE_DIVISION"){
      maxlength = 20;
    }

    $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
    $(this).val(this.value.substring(0, maxLength));
  });


  $("#NOM, #PRENOM, #RESPONSABLE, #POSTE, #INTITULE_DES_GRANDES_MASSES").on('input', function()
  {
    var maxLength;

    if (this.id === "RESPONSABLE" || this.id === "INTITULE_DES_GRANDES_MASSES") {
      maxLength = 500;
    } else if (this.id === "NOM" || this.id === "PRENOM" || this.id === "POSTE") {
      maxLength = 50;
    }

    $(this).val($(this).val().replace(/[^a-z|A-Z ]*$/gi, '').toUpperCase());
    $(this).val(this.value.substring(0, maxLength));

  });


  $("#ACTIVITES, #RESULTATS_ATTENDUS, #UNITE, #QT1, #QT2, #QT3, #QT4, #COUT_UNITAIRE_BIF").on('input', function()
  {
    var maxLength;

    if (this.id === "ACTIVITES") {
      maxLength = 2000;

    } else if (this.id === "RESULTATS_ATTENDUS") {
      maxLength = 3000;
    } else if (this.id === "UNITE"){
      maxLength = 100;
    } else if (this.id === "COUT_UNITAIRE_BIF" || this.id === "QT1" || this.id === "QT2" || this.id === "QT3" || this.id === "QT4"){
      maxLength = 50;
    }

    $(this).val(this.value.substring(0,maxLength));

  });




</script>


<script type="text/javascript">

  function get_programs(){

    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    $.ajax({
      url : "<?=base_url('process/Dem_Liste_Activites/get_programs')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:{
        INSTITUTION_ID:INSTITUTION_ID
        
      },

      success:function(data){   
        
        $('#PROGRAM_ID').html(data.tutel);

      },            

    });  

  }

  function create_get_action(){

    var PROGRAMME_ID=$('#PROGRAMME_ID').val();

    $.ajax({
      url : "<?=base_url('process/Dem_Liste_Activites/create_get_action')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:{
        PROGRAMME_ID:PROGRAMME_ID
        
      },

      success:function(data){   
        $('#ACTION_ID').html(data.tutel);

      },            

    });  

  }


  //selectionner action
  function get_action(){
    var action_id = $('#ACTION_ID').val();

    $('#set_ACTION_ID').val(action_id);

  }

</script>


<script type="text/javascript">

  function insert()
  {  
    var MOTIF_ACTIVITE_ID = $('#MOTIF_ACTIVITE_ID').val();
    var NOM = $('#NOM').val();
    var PRENOM = $('#PRENOM').val();
    var POSTE = $('#POSTE').val();
    var SOUS_TUTEL_ID  = $('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID  = $('#PROGRAMME_ID').val();
    var ACTION_ID  = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE  = $('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    var CODE_DIVISION  = $('#CODE_DIVISION').val();
    var CODE_GROUPE  = $('#CODE_GROUPE').val();
    var CODE_CLASSE  = $('#CODE_CLASSE').val();
    var INTITULE_DIVISION  = $('#INTITULE_DIVISION').val();
    var INTITULE_GROUPE  = $('#INTITULE_GROUPE').val();
    var INTITULE_CLASSE  = $('#INTITULE_CLASSE').val();
    var CODES_PROGRAMMATIQUE = $('#CODES_PROGRAMMATIQUE').val().length; 
    var ACTIVITES  = $('#ACTIVITES').val();
    var RESULTATS_ATTENDUS  = $('#RESULTATS_ATTENDUS').val();
    var UNITE = $('#UNITE').val();
    var COUT_UNITAIRE_BIF  = $('#COUT_UNITAIRE_BIF').val();
    var QT1  = $('#QT1').val();
    var QT2  = $('#QT2').val();
    var QT3  = $('#QT3').val();
    var QT4  = $('#QT4').val();
    var T1  = $('#T1').val();
    var T2  = $('#T2').val();
    var T3  = $('#T3').val();
    var T4  = $('#T4').val();
    var INTITULE_DES_GRANDES_MASSES  = $('#INTITULE_DES_GRANDES_MASSES').val();
    var GRANDE_MASSE_BP  = $('#GRANDE_MASSE_BP').val();
    var GRANDE_MASSE_BM1 = $('#GRANDE_MASSE_BM1').val();
    var GRANDE_MASSE_BM  = $('#GRANDE_MASSE_BM').val();
    var RESPONSABLE  = $('#RESPONSABLE').val();

    var ARTICLE_ECONOMIQUE = $('#ARTICLE_ECONOMIQUE').val();
    var INTITULE_ARTICLE_ECONOMIQUE = $('#INTITULE_ARTICLE_ECONOMIQUE').val();
    var NATURE_ECONOMIQUE = $('#NATURE_ECONOMIQUE').val();
    var INTITULE_NATURE_ECONOMIQUE = $('#INTITULE_NATURE_ECONOMIQUE').val();
    var GROUPE_ID = $('#GROUPE_ID').val();
    var CLASSE_ID = $('#CLASSE_ID').val();

    $('#error_MOTIF_ACTIVITE_ID').html('');
    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html('');
    $('#error_DESCRIPTION_SOUS_TUTEL').html('');
    $('#error_INTITULE_PROGRAMME').html('');
    $('#error_ACTION_ID').html('');
    $('#error_code_budget').html('');
    $('#error_Activites').html('');
    $('#error_Resultats_Attendus').html('');
    $('#error_Unite').html('');
    $('#error_Cout_unitaire').html('');
    $('#error_Quantite1').html('');
    $('#error_Quantite2').html('');
    $('#error_Quantite3').html('');
    $('#error_Quantite4').html('');
    $('#error_T1').html('');
    $('#error_T2').html('');
    $('#error_T3').html('');
    $('#error_T4').html('');
    $('#error_Institution_Grande_Masse').html('');
    $('#error_Grande_Masse_Budget_Programme').html('');
    $('#error_Grande_Masse_Budget_Moyen_1').html('');
    $('#error_Masse_Budget_Moyen').html('');
    $('#error_Responsable').html('');

    $('#error_ARTICLE_ECONOMIQUE').html('');
    $('#error_INTITULE_ARTICLE_ECONOMIQUE').html('');

    $('#error_NATURE_ECONOMIQUE').html('');
    $('#error_INTITULE_NATURE_ECONOMIQUE').html('');

    $('#error_CODE_DIVISION').text('');
    $('#error_CODE_GROUPE').text('');
    $('#error_CODE_CLASSE').text('');
    $('#error_INTITULE_DIVISION').text('');
    $('#error_INTITULE_GROUPE').text('');
    $('#error_INTITULE_CLASSE').text('');

    $('#error_CODES_PROGRAMMATIQUE').text("");

    var statut = 2;


    if(SOUS_TUTEL_ID  == '')
    {
      $('#error_DESCRIPTION_SOUS_TUTEL').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;

    }

    if(PROGRAMME_ID  == '')
    {
      $('#error_INTITULE_PROGRAMME').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(ACTION_ID == '')
    {
      $('#error_ACTION_ID').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(CODE_NOMENCLATURE_BUDGETAIRE == '')
    {
      $('#error_code_budget').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(CODES_PROGRAMMATIQUE == '')
    {
      $('#error_codes_progr').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    
    if(CODE_DIVISION == '')
    {
      $('#error_CODE_DIVISION').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(CODE_GROUPE == '')
    {
      $('#error_CODE_GROUPE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(CODE_CLASSE == '')
    {
      $('#error_CODE_CLASSE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(INTITULE_DIVISION == '')
    {
      $('#error_INTITULE_DIVISION').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(INTITULE_GROUPE == '')
    {
      $('#error_INTITULE_GROUPE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(INTITULE_CLASSE == '')
    {
      $('#error_INTITULE_CLASSE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(ACTIVITES == '')
    {
      $('#error_Activites').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    
    if(RESULTATS_ATTENDUS == '')
    {
      $('#error_Resultats_Attendus').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    
    if(ARTICLE_ECONOMIQUE == '')
    {
      $('#error_ARTICLE_ECONOMIQUE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(INTITULE_ARTICLE_ECONOMIQUE == '')
    {
      $('#error_INTITULE_ARTICLE_ECONOMIQUE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(NATURE_ECONOMIQUE == '')
    {
      $('#error_NATURE_ECONOMIQUE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(INTITULE_NATURE_ECONOMIQUE == '')
    {
      $('#error_INTITULE_NATURE_ECONOMIQUE').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }


    if(GROUPE_ID == '')
    {
      $('#error_GROUPE_ID').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(CLASSE_ID == '')
    {
      $('#error_CLASSE_ID').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    if(UNITE == '')
    {
      $('#error_Unite').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(COUT_UNITAIRE_BIF == '')
    {
      $('#error_Cout_unitaire').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(QT1 == '')
    {
      $('#error_Quantite1').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(QT2 == '')
    {
      $('#error_Quantite2').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(QT3 == '')
    {
      $('#error_Quantite3').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(QT4 == '')
    {
      $('#error_Quantite4').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(T1 == '')
    {
      $('#error_T1').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(T2 == '')
    {
      $('#error_T2').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(T3 == '')
    {
      $('#error_T3').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(T4 == '')
    {
      $('#error_T4').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(INTITULE_DES_GRANDES_MASSES == '')
    {
      $('#error_Institution_Grande_Masse').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if(GRANDE_MASSE_BP == '')
    {
      $('#error_Grande_Masse_Budget_Programme').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;

    }
    if(GRANDE_MASSE_BM1 == '')
    {
      $('#error_Grande_Masse_Budget_Moyen_1').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      // statut = 1;

    }
    if(GRANDE_MASSE_BM == '')
    {
      $('#error_Masse_Budget_Moyen').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      // statut = 1;

    }
    if(RESPONSABLE == '')
    {
      $('#error_Responsable').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;

    }

    if(statut == 2)
    {
      document.getElementById("my_form").submit();

    }
  }


</script>


<script>
// supprimer
function deleteData(Rowid){
  $("#deleterowId").val(Rowid);
  $("#mydelete").modal("show");

}
//boutton supprimer
function remove(){
 
 var id=$("#deleterowId").val();
  $.post('<?=base_url('Piliers/supprimer')?>',
  {
    id:id,
    
  },function(data){
   if(data){
     window.location.href="<?= base_url('Piliers/Pilier')?>";
   }
   });

}
//fin fonction

$(document).ready(function()
{
  var row_count ="1000000";
  $('#message').delay('slow').fadeOut(3000);
  $("#mytable").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "ajax":
    {
      url:"<?= base_url('process/Liste_Activites')?>",
      type:"POST", 
    },
    lengthMenu: [[5,50,100, row_count], [5,50, 100, "All"]],
    pageLength: 5,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],
    dom: 'Bfrtlip',
    order:[1,'asc'],
    buttons: [
    'excel', 'pdf'
    ],
    language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
        "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
        },        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
    }

  });
});
</script>
