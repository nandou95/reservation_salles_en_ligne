<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
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
            <div class="col-12 d-flex">
              <div class="col-9" style="float: left;">
                <h1 class="header-title text-dark">Ajout d'une tâche</h1>
              </div>
              <div class="col-3" style="float: right;">
                <a href="<?=base_url('ihm/Liste_Taches')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary">
                  <span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.list_action')?>
                </a>   
              </div>
            </div>
            <div style="margin-left: 15px" id="SUCCESS_MESSAGE" class="row">
            </div>
            <div class="card-body">
              <?php $validation = \Config\Services::validation(); ?>
              <form id="my_form" action="<?= base_url('ihm/Taches/save_tache') ?>" method="POST">
                <div class="card-body">
                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-6" id="rep">
                        <div class="form-group">
                          <label><?=lang('messages_lang.label_motif_dec')?><span style="color: red;">*</span></label>
                          <select  class="form-control" name="MOTIF_TACHE_ID" id="MOTIF_TACHE_ID" onclick="hierarchie()" onchange="checkSelect(this)" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.selection_message')?></option>
                            <?php
                            foreach($motif as $key)
                            {
                              if($key->MOTIF_TACHE_ID == set_value('MOTIF_TACHE_ID'))
                              {
                                ?>
                                <option value="<?=$key->MOTIF_TACHE_ID ?>" selected><?=$key->DESCR_MOTIF_TACHE?></option>
                                <?php
                              }
                              else
                              {
                                ?>
                                <option value="<?=$key->MOTIF_TACHE_ID ?>"><?=$key->DESCR_MOTIF_TACHE?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('MOTIF_TACHE_ID'); ?>
                          <?php endif ?>
                          <span id="error_MOTIF_TACHE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="form-group">
                          <label>Lettre d'autorisation<span style="color: red;">*</span></label>
                          <input type="file" accept=".pdf" class="form-control"  name="PATH_LETTRE_AUTORISATION" id="PATH_LETTRE_AUTORISATION" onchange="valid_doc(this)">
                          <font color="red" id="error_PATH_LETTRE_AUTORISATION"></font>
                        </div>
                      </div>

                      <div id="MORE_INFO" class="row" style="display: none;">

                        <div class="col-4">
                          <div class="form-group">
                            <label><?=lang('messages_lang.labelle_nom')?><span style="color: red;">*</span></label>
                            <input autocomplete="off" type="text" name="NOM" id="NOM" class="form-control" value="<?= set_value('NOM')?>" >
                            <?php if (isset($validation)) : ?>
                              <span id="error_NOM" class="text-danger"><?= $validation->getError('NOM'); ?></span>
                            <?php endif ?>
                            <span style="font-size: 10px" id="getNumberNom"></span>
                          </div>
                        </div>

                        <div class="col-4" >
                          <div class="form-group">
                            <label><?=lang('messages_lang.labelle_prenom')?><span style="color: red;">*</span></label>
                            <input autocomplete="off" type="text" name="PRENOM" id="PRENOM" class="form-control" value="<?= set_value('PRENOM')?>" >
                            <?php if (isset($validation)) : ?>
                              <span id="error_PRENOM" class="text-danger"><?= $validation->getError('PRENOM'); ?></span>
                            <?php endif ?>
                            <span style="font-size: 10px" id="getNumberPrenom"></span>
                          </div>
                        </div>

                        <div class="col-4">
                          <div class="form-group">
                            <label><?=lang('messages_lang.poste')?><span style="color: red;">*</span></label>
                            <input autocomplete="off" type="text" name="POSTE" id="POSTE" class="form-control" value="<?= set_value('POSTE')?>" >
                            <?php if (isset($validation)) : ?>
                              <span id="error_POSTE" class="text-danger"><?= $validation->getError('POSTE'); ?></span>
                            <?php endif ?>
                            <span style="font-size: 10px" id="getNumberPoste"></span>
                          </div>
                        </div>
                        
                      </div>
                    </div>
                  </div>

                  <input type="hidden" id="TYPE_INSTITUTION_ID">

                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <h4><center><i class="fa fa-info-circle"></i> <?=lang('messages_lang.labelle_information_base')?></center></h4>
                      </div>
                      <div class="col-4">
                        <div class="form-group">
                          <label>Institution<span style="color: red;">*</span></label>
                          <select class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID" onchange="get_soutut(); get_programme(); get_institution_info(); checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($institutions as $institution)
                            {
                              ?>
                              <option value="<?=$institution->INSTITUTION_ID?>"><?=$institution->CODE_INSTITUTION.' '.$institution->DESCRIPTION_INSTITUTION?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <span id="error_INSTITUTION_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.table_st')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="get_code_budgetaire(); checkSelect(this)" >
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                          <?php endif ?>
                          <span id="error_SOUS_TUTEL_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Pilier<span style="color: red;">*</span></label>
                          <select class="form-control" name="ID_PILIER" id="ID_PILIER" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($piliers as $pilier)
                            {
                              ?>
                              <option value="<?=$pilier->ID_PILIER?>"><?=$pilier->DESCR_PILIER?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('ID_PILIER'); ?>
                          <?php endif ?>
                          <span id="error_ID_PILIER" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Objectif vision<span style="color: red;">*</span></label>
                          <select class="form-control" name="OBJECTIF_VISION_ID" id="OBJECTIF_VISION_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($vision_objectifs as $objectif)
                            {
                              ?>
                              <option value="<?=$objectif->OBJECTIF_VISION_ID?>"><?=$objectif->DESC_OBJECTIF_VISION?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('OBJECTIF_VISION_ID');?>
                          <?php endif ?>
                          <span id="error_OBJECTIF_VISION_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Axe PND<span style="color: red;">*</span></label>
                          <select class="form-control" name="AXE_PND_ID" id="AXE_PND_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($axes as $axe)
                            {
                              ?>
                              <option value="<?=$axe->AXE_PND_ID?>"><?=$axe->DESCR_AXE_PND?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('AXE_PND_ID');?>
                          <?php endif ?>
                          <span id="error_AXE_PND_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Programme prioritaire</label>
                          <select class="form-control" name="PROGRAMME_PRIORITAIRE_ID" id="PROGRAMME_PRIORITAIRE_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($programme_prioritaires as $programme_prioritaire)
                            {
                              ?>
                              <option value="<?=$programme_prioritaire->PROGRAMME_PRIORITAIRE_ID?>"><?=$programme_prioritaire->DESC_PROGRAMME_PRIORITAIRE?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PROGRAMME_PRIORITAIRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_PROGRAMME_PRIORITAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <h4><center> <i class="fa fa-certificate"></i> <?=lang('messages_lang.labelle_economique')?></center></h4>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Chapitre<span style="color: red;">*</span></label>
                          <select class="form-control" name="CHAPITRE_ID" id="CHAPITRE_ID" onchange="get_article(); checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($chapitres as $chapitre)
                            {
                              ?>
                              <option value="<?=$chapitre->CHAPITRE_ID?>"><?=$chapitre->CODE_CHAPITRE.' '.$chapitre->LIBELLE_CHAPITRE?></option> 
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('CHAPITRE_ID'); ?>
                          <?php endif ?>
                          <span id="error_CHAPITRE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_article_economique')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="ARTICLE_ID" id="ARTICLE_ID" onchange="get_paragraphe();checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('ARTICLE_ID'); ?>
                          <?php endif ?>
                          <span id="error_ARTICLE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Paragraphe<span style="color: red;">*</span></label>
                          <select class="form-control" name="PARAGRAPHE_ID" id="PARAGRAPHE_ID" onchange="get_littera();checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PARAGRAPHE_ID'); ?>
                          <?php endif ?>
                          <span id="error_PARAGRAPHE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Littera<span style="color: red;">*</span></label>
                          <select class="form-control" name="LITTERA_ID" id="LITTERA_ID" onchange="get_sous_littera();checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('LITTERA_ID'); ?>
                          <?php endif ?>
                          <span id="error_LITTERA_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Sous littera<span style="color: red;">*</span></label>
                          <select class="form-control" name="SOUS_LITTERA_ID" id="SOUS_LITTERA_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('SOUS_LITTERA_ID'); ?>
                          <?php endif ?>
                          <span id="error_SOUS_LITTERA_ID" class="text-danger"></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <h4><center> <i class="fa fa-circle"></i> <?=lang('messages_lang.labelle_information_fonctionnelle')?></center></h4>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label for="Nom" class="form-label">Division<span style="color: red;">*</span></label>
                          <select onchange="get_groupes();checkSelect(this)" class="form-control" name="DIVISION_ID" id="DIVISION_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($get_division as $key)
                            {
                              if($key->DIVISION_ID==set_value('DIVISION_ID'))
                              {
                                echo "<option value='".$key->DIVISION_ID."'  selected>".$key->CODE_DIVISION." - ".$key->LIBELLE_DIVISION."</option>";
                              }
                              else
                              {
                                echo "<option value='".$key->DIVISION_ID."' >".$key->CODE_DIVISION." - ".$key->LIBELLE_DIVISION."</option>";
                              }
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DIVISION_ID'); ?>
                          <?php endif ?>
                          <span id="error_DIVISION_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label for="Nom" class="form-label">Groupe<span style="color: red;">*</span> <span id="loading_groupe"></span></label>
                          <select onchange="get_classes();checkSelect(this)" class="form-control" name="GROUPE_ID" id="GROUPE_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('GROUPE_ID'); ?>
                          <?php endif ?>
                          <span id="error_GROUPE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label for="Nom" class="form-label">Classe<span style="color: red;">*</span></label>
                          <select class="form-control" name="CLASSE_ID" id="CLASSE_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('CLASSE_ID'); ?>
                          <?php endif ?>
                          <span id="error_CLASSE_ID" class="text-danger"></span>
                        </div>
                      </div>

                    </div>
                  </div>

                  <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <h4><center> <i class="fa fa-circle"></i>Information d'une tache</center></h4>
                      </div>
                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.table_Programme')?><span style="color: red;">*</span></label>
                          <select class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="get_action();checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PROGRAMME_ID'); ?>
                          <?php endif ?>
                          <span id="error_PROGRAMME_ID" class="text-danger"></span>
                        </div>
                      </div> 

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.table_Action')?> <font color="red" >*</font></label>
                          <select class="form-control" id="ACTION_ID" name="ACTION_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?> </option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('ACTION_ID'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_ACTION_ID"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Costab activités</label>
                          <select  class="form-control" name="COSTAB_ACTIVITE_ID" id="COSTAB_ACTIVITE_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($costabs as $costab)
                            {
                              ?>
                              <option value="<?=$costab->COSTAB_ACTIVITE_ID?>"><?=$costab->DESC_COSTAB_ACTIVITE?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('COSTAB_ACTIVITE_ID'); ?>
                          <?php endif ?>
                          <span id="error_COSTAB_ACTIVITE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_code_budgetaire')?> <font color="red" >*</font></label>
                          <select class="form-control" id="CODE_NOMENCLATURE_BUDGETAIRE_ID" name="CODE_NOMENCLATURE_BUDGETAIRE_ID" onchange="get_pap_activite();checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?> </option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE_ID'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_CODE_NOMENCLATURE_BUDGETAIRE_ID"></span>
                        </div>
                      </div>

                      <div class="col-4" id="PAP_ACTIVITE_ID_DIV">
                        <div class="form-group">
                          <label>Pap activités<span style="color: red;">*</span></label>
                          <select class="form-control" name="PAP_ACTIVITE_ID" id="PAP_ACTIVITE_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('PAP_ACTIVITE_ID'); ?>
                          <?php endif ?>
                          <span id="error_PAP_ACTIVITE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>PND Indicateur</label>
                          <select  class="form-control" name="INDICATEUR_PND_ID" id="INDICATEUR_PND_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($pnds as $pnd)
                            {
                              ?>
                              <option value="<?=$pnd->INDICATEUR_PND_ID?>"><?=$pnd->DESC_INDICATEUR_PND?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('INDICATEUR_PND_ID'); ?>
                          <?php endif ?>
                          <span id="error_INDICATEUR_PND_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="form-group">
                          <label>Déscription tâche<span style="color: red;">*</span></label>
                          <textarea oninput="checkSelect(this)" name="TACHE" id="TACHE" class="form-control" rows="2"><?= set_value('TACHE')?></textarea>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('TACHE'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_TACHE"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_resultant_attendus')?></label>
                          <textarea type="text" name="RESULTATS_ATTENDUS" id="RESULTATS_ATTENDUS" class="form-control"></textarea>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('RESULTATS_ATTENDUS'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_RESULTATS_ATTENDUS"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Structure responsable tâche</label>
                          <select  class="form-control" name="STRUTURE_RESPONSABLE_TACHE_ID" id="STRUTURE_RESPONSABLE_TACHE_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($structs as $struct)
                            {
                              ?>
                              <option value="<?=$struct->STRUTURE_RESPONSABLE_TACHE_ID?>"><?=$struct->DESC_STRUTURE_RESPONSABLE_TACHE?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('STRUTURE_RESPONSABLE_TACHE_ID'); ?>
                          <?php endif ?>
                          <span id="error_STRUTURE_RESPONSABLE_TACHE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Grande masse<span style="color: red;">*</span></label>
                          <select  class="form-control" name="GRANDE_MASSE_ID" id="GRANDE_MASSE_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($gd_masses as $gd_masse)
                            {
                              ?>
                              <option value="<?=$gd_masse->GRANDE_MASSE_ID?>"><?=$gd_masse->DESCRIPTION_GRANDE_MASSE?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('GRANDE_MASSE_ID'); ?>
                          <?php endif ?>
                          <span id="error_GRANDE_MASSE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-4">
                        <div class="form-group">
                          <label>Année budgétaire<span style="color: red;">*</span></label>
                          <select  class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID" onchange="checkSelect(this)">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($annees as $annee)
                            {
                              ?>
                              <option value="<?=$annee->ANNEE_BUDGETAIRE_ID?>"><?=$annee->ANNEE_DESCRIPTION?></option>
                              <?php
                            }
                            ?>
                          </select>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('ANNEE_BUDGETAIRE_ID '); ?>
                          <?php endif ?>
                          <span id="error_ANNEE_BUDGETAIRE_ID" class="text-danger"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_cout_unitaire')?><span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text" name="COUT_UNITAIRE_BIF" id="COUT_UNITAIRE_BIF" class="form-control" oninput='checkNumeric(this)'>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('COUT_UNITAIRE_BIF'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_COUT_UNITAIRE_BIF"></span>
                        </div>
                      </div>

                      <div class="col-6">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_unite')?></label>
                          <input autocomplete="off" type="text" name="UNITE" id="UNITE"  class="form-control">
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('UNITE'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_UNITE"></span>
                        </div>
                      </div>

                      <div class="col-3">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_quantite')?> T1<span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text" name="QT1" id="QT1" class="form-control" oninput='checkNumeric(this)'>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('QT1'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_QT1"></span>
                        </div>
                      </div>

                      <div class="col-3">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_quantite')?> T2<span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text" name="QT2" id="QT2" class="form-control" oninput='checkNumeric(this)'>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('QT2'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_QT2"></span>
                        </div>
                      </div>

                      <div class="col-3">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_quantite')?> T3<span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text"name="QT3" id="QT3" class="form-control" oninput='checkNumeric(this)'>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('QT3'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_QT3"></span>
                        </div>
                      </div>

                      <div class="col-3">
                        <div class="form-group">
                          <label><?=lang('messages_lang.labelle_quantite')?> T4<span style="color: red;">*</span></label>
                          <input autocomplete="off" type="text" name="QT4" id="QT4" class="form-control" oninput='checkNumeric(this)'>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('QT4'); ?>
                          <?php endif ?>
                          <span class="text-danger" id="error_QT4"></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-12">
                    <button style="float: right;" id="btnSave" type="button" onclick="insert()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.enregistre_action')?></button>
                  </div>
                </div>
              </form>
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

<script>
  $(document).ready(function() {
    $('#PAP_ACTIVITE_ID_DIV').hide();
  })
</script>

<script>
  function hierarchie() 
  {
    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html('');
    var MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();
    if (MOTIF_TACHE_ID == 2 || MOTIF_TACHE_ID == 3)
    {
      $('#MORE_INFO').show();
    }
    else{
      $('#MORE_INFO').hide();
    }
  }
</script>

<script>
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

      $('#MORE_INFO').hide();

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

  function insert()
  {
    //get form values
    let FRAIS;
    if($("#FRAIS_OUI").prop("checked"))
    {
      FRAIS = 'oui';
    }

    if($("#FRAIS_NON").prop("checked"))
    {
      FRAIS = 'non';
    }

    let PATH_LETTRE_AUTORISATION =  document.getElementById('PATH_LETTRE_AUTORISATION').files[0];
    let MOTIF_TACHE_ID = $('#MOTIF_TACHE_ID').val();
    let NOM=$('#NOM').val();
    let PRENOM=$('#PRENOM').val();
    let POSTE=$('#POSTE').val();
    let TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    let INSTITUTION_ID=$('#INSTITUTION_ID').val();
    let SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    let ID_PILIER=$('#ID_PILIER').val();
    let OBJECTIF_VISION_ID=$('#OBJECTIF_VISION_ID').val();
    let AXE_PND_ID=$('#AXE_PND_ID').val();
    let PROGRAMME_PRIORITAIRE_ID=$('#PROGRAMME_PRIORITAIRE_ID').val();
    let CHAPITRE_ID=$('#CHAPITRE_ID').val();
    let ARTICLE_ID=$('#ARTICLE_ID').val();
    let PARAGRAPHE_ID=$('#PARAGRAPHE_ID').val();
    let LITTERA_ID=$('#LITTERA_ID').val();
    let SOUS_LITTERA_ID=$('#SOUS_LITTERA_ID').val();
    let DIVISION_ID=$('#DIVISION_ID').val();
    let GROUPE_ID=$('#GROUPE_ID').val();
    let CLASSE_ID=$('#CLASSE_ID').val();
    let PROGRAMME_ID=$('#PROGRAMME_ID').val();
    let ACTION_ID=$('#ACTION_ID').val();
    let COSTAB_ACTIVITE_ID=$('#COSTAB_ACTIVITE_ID').val();
    let CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();
    let PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
    let INDICATEUR_PND_ID=$('#INDICATEUR_PND_ID').val();
    let TACHE=$('#TACHE').val();
    let RESULTATS_ATTENDUS=$('#RESULTATS_ATTENDUS').val();
    let STRUTURE_RESPONSABLE_TACHE_ID=$('#STRUTURE_RESPONSABLE_TACHE_ID').val();
    let GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();
    let ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    let COUT_UNITAIRE_BIF=$('#COUT_UNITAIRE_BIF').val();
    let UNITE=$('#UNITE').val();
    let QT1=$('#QT1').val();
    let QT2=$('#QT2').val();
    let QT3=$('#QT3').val();
    let QT4=$('#QT4').val();

    //reset the error messages
    $('#error_MOTIF_TACHE_ID').html('');
    $('#error_PATH_LETTRE_AUTORISATION').html('');
    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html('');
    $('#SUCCESS_MESSAGE').html('');
    $('#error_INSTITUTION_ID').html('');
    $('#error_SOUS_TUTEL_ID').html('');
    $('#error_ID_PILIER').html('');
    $('#error_OBJECTIF_VISION_ID').html('');
    $('#error_AXE_PND_ID').html('');
    $('#error_PROGRAMME_PRIORITAIRE_ID').html('');
    $('#error_CHAPITRE_ID').html('');
    $('#error_ARTICLE_ID').html('');
    $('#error_PARAGRAPHE_ID').html('');
    $('#error_LITTERA_ID').html('');
    $('#error_SOUS_LITTERA_ID').html('');
    $('#error_DIVISION_ID').html('');
    $('#error_GROUPE_ID').html('');
    $('#error_CLASSE_ID').html('');
    $('#error_PROGRAMME_ID').html('');
    $('#error_ACTION_ID').html('');
    $('#error_COSTAB_ACTIVITE_ID').html('');
    $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html('');
    $('#error_PAP_ACTIVITE_ID').html('');
    $('#error_INDICATEUR_PND_ID').html('');
    $('#error_TACHE').html('');
    $('#error_RESULTATS_ATTENDUS').html('');
    $('#error_STRUTURE_RESPONSABLE_TACHE_ID').html('');
    $('#error_GRANDE_MASSE_ID').html('');
    $('#error_ANNEE_BUDGETAIRE_ID').html('');
    $('#error_COUT_UNITAIRE_BIF').html('');
    $('#error_UNITE').html('');
    $('#error_QT1').html('');
    $('#error_QT2').html('');
    $('#error_QT3').html('');
    $('#error_QT4').html('');

    //start validation
    let isFormValid = true;

    if(!PATH_LETTRE_AUTORISATION){
      $('#error_PATH_LETTRE_AUTORISATION').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(MOTIF_TACHE_ID == ""){
      $('#error_MOTIF_TACHE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(INSTITUTION_ID == ""){
      $('#error_INSTITUTION_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(SOUS_TUTEL_ID == ""){
      $('#error_SOUS_TUTEL_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(ID_PILIER == ""){
      $('#error_ID_PILIER').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(OBJECTIF_VISION_ID == ""){
      $('#error_OBJECTIF_VISION_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(AXE_PND_ID == ""){
      $('#error_AXE_PND_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(CHAPITRE_ID == ""){
      $('#error_CHAPITRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(ARTICLE_ID == ""){
      $('#error_ARTICLE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(PARAGRAPHE_ID == ""){
      $('#error_PARAGRAPHE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(LITTERA_ID == ""){
      $('#error_LITTERA_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(SOUS_LITTERA_ID == ""){
      $('#error_SOUS_LITTERA_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(DIVISION_ID == ""){
      $('#error_DIVISION_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(GROUPE_ID == ""){
      $('#error_GROUPE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(CLASSE_ID == ""){
      $('#error_CLASSE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(PROGRAMME_ID == ""){
      $('#error_PROGRAMME_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(ACTION_ID == ""){
      $('#error_ACTION_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(CODE_NOMENCLATURE_BUDGETAIRE_ID == ""){
      $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html('<font style="color:red;size:2px;">Le champs est pbligatoire</font>');
      isFormValid =  false;
    }
    if(TACHE == ""){
      $('#error_TACHE').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(GRANDE_MASSE_ID == ""){
      $('#error_GRANDE_MASSE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(ANNEE_BUDGETAIRE_ID == ""){
      $('#error_ANNEE_BUDGETAIRE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(COUT_UNITAIRE_BIF == ""){
      $('#error_COUT_UNITAIRE_BIF').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(QT1 == ""){
      $('#error_QT1').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(QT2 == ""){
      $('#error_QT2').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(QT3 == ""){
      $('#error_QT3').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }
    if(QT4 == ""){
      $('#error_QT4').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      isFormValid =  false;
    }

    if(TYPE_INSTITUTION_ID==2)
    {
      if(PAP_ACTIVITE_ID == ""){
        $('#error_PAP_ACTIVITE_ID').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
        isFormValid =  false;
      }
    }

    if(MOTIF_TACHE_ID != "")
    {
      if(MOTIF_TACHE_ID == 2 || MOTIF_TACHE_ID == 3)
      {
        if(NOM == ""){
          $('#error_NOM').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
          isFormValid =  false;
        }
        if(PRENOM == ""){
          $('#error_PRENOM').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
          isFormValid =  false;
        }
        if(POSTE == ""){
          $('#error_POSTE').html('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
          isFormValid =  false;
        }
      }
    }

    if(!isFormValid) return;

    //do the post request
    let form = new FormData();

    form.append("PATH_LETTRE_AUTORISATION",PATH_LETTRE_AUTORISATION);
    form.append("FRAIS",FRAIS);
    form.append("MOTIF_TACHE_ID",MOTIF_TACHE_ID);
    form.append("NOM",NOM);
    form.append("PRENOM",PRENOM);
    form.append("POSTE",POSTE);
    form.append("INSTITUTION_ID",INSTITUTION_ID);
    form.append("SOUS_TUTEL_ID",SOUS_TUTEL_ID);
    form.append("ID_PILIER",ID_PILIER);
    form.append("OBJECTIF_VISION_ID",OBJECTIF_VISION_ID);
    form.append("AXE_PND_ID",AXE_PND_ID);
    form.append("PROGRAMME_PRIORITAIRE_ID",PROGRAMME_PRIORITAIRE_ID);
    form.append("CHAPITRE_ID",CHAPITRE_ID);
    form.append("ARTICLE_ID",ARTICLE_ID);
    form.append("PARAGRAPHE_ID",PARAGRAPHE_ID);
    form.append("LITTERA_ID",LITTERA_ID);
    form.append("SOUS_LITTERA_ID",SOUS_LITTERA_ID);
    form.append("DIVISION_ID",DIVISION_ID);
    form.append("GROUPE_ID",GROUPE_ID);
    form.append("CLASSE_ID",CLASSE_ID);
    form.append("PROGRAMME_ID",PROGRAMME_ID);
    form.append("ACTION_ID",ACTION_ID);
    form.append("COSTAB_ACTIVITE_ID",COSTAB_ACTIVITE_ID);
    form.append("CODE_NOMENCLATURE_BUDGETAIRE_ID",CODE_NOMENCLATURE_BUDGETAIRE_ID);
    form.append("PAP_ACTIVITE_ID",PAP_ACTIVITE_ID);
    form.append("INDICATEUR_PND_ID",INDICATEUR_PND_ID);
    form.append("TACHE",TACHE);
    form.append("RESULTATS_ATTENDUS",RESULTATS_ATTENDUS);
    form.append("STRUTURE_RESPONSABLE_TACHE_ID",STRUTURE_RESPONSABLE_TACHE_ID);
    form.append("GRANDE_MASSE_ID",GRANDE_MASSE_ID);
    form.append("ANNEE_BUDGETAIRE_ID",ANNEE_BUDGETAIRE_ID);
    form.append("COUT_UNITAIRE_BIF",COUT_UNITAIRE_BIF);
    form.append("UNITE",UNITE);
    form.append("QT1",QT1);
    form.append("QT2",QT2);
    form.append("QT3",QT3);
    form.append("QT4",QT4);

    $.ajax(
    {
      url:"<?=base_url('ihm/Taches/save_tache')?>",
      type:"POST",
      dataType:"JSON",
      data: form,
      processData: false,  
      contentType: false,
      beforeSend:function() {
        $('#btnSave').attr('disabled',true);
      },
      success: function(data)
      { 
        $('#btnSave').attr('disabled',false);
        if(data.message)
        {
          $('#error_MOTIF_TACHE_ID').html('');
          $('#error_NOM').html('');
          $('#error_PRENOM').html('');
          $('#error_POSTE').html('');
          $('#SUCCESS_MESSAGE').html('');
          $('#error_INSTITUTION_ID').html('');
          $('#error_SOUS_TUTEL_ID').html('');
          $('#error_ID_PILIER').html('');
          $('#error_OBJECTIF_VISION_ID').html('');
          $('#error_AXE_PND_ID').html('');
          $('#error_PROGRAMME_PRIORITAIRE_ID').html('');
          $('#error_CHAPITRE_ID').html('');
          $('#error_ARTICLE_ID').html('');
          $('#error_PARAGRAPHE_ID').html('');
          $('#error_LITTERA_ID').html('');
          $('#error_SOUS_LITTERA_ID').html('');
          $('#error_DIVISION_ID').html('');
          $('#error_GROUPE_ID').html('');
          $('#error_CLASSE_ID').html('');
          $('#error_PROGRAMME_ID').html('');
          $('#error_ACTION_ID').html('');
          $('#error_COSTAB_ACTIVITE_ID').html('');
          $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html('');
          $('#error_PAP_ACTIVITE_ID').html('');
          $('#error_INDICATEUR_PND_ID').html('');
          $('#error_TACHE').html('');
          $('#error_RESULTATS_ATTENDUS').html('');
          $('#error_STRUTURE_RESPONSABLE_TACHE_ID').html('');
          $('#error_GRANDE_MASSE_ID').html('');
          $('#error_ANNEE_BUDGETAIRE_ID').html('');
          $('#error_COUT_UNITAIRE_BIF').html('');
          $('#error_UNITE').html('');
          $('#error_QT1').html('');
          $('#error_QT2').html('');
          $('#error_QT3').html('');
          $('#error_QT4').html('');

          window.location.href = '<?php echo site_url('ihm/Liste_Taches'); ?>';
        }
        if(data.errors)
        {
          let errors = data.errors;
          $('#error_PATH_LETTRE_AUTORISATION').html(data.errors.PATH_LETTRE_AUTORISATION);
          $('#error_MOTIF_TACHE_ID').html(data.errors.MOTIF_TACHE_ID);
          $('#error_NOM').html(data.errors.NOM);
          $('#error_PRENOM').html(data.errors.PRENOM);
          $('#error_POSTE').html(data.errors.POSTE);
          $('#error_INSTITUTION_ID').html(errors.INSTITUTION_ID);
          $('#error_SOUS_TUTEL_ID').html(errors.SOUS_TUTEL_ID);
          $('#error_ID_PILIER').html(errors.ID_PILIER);
          $('#error_OBJECTIF_VISION_ID').html(errors.OBJECTIF_VISION_ID);
          $('#error_AXE_PND_ID').html(errors.AXE_PND_ID);
          $('#error_PROGRAMME_PRIORITAIRE_ID').html(errors.PROGRAMME_PRIORITAIRE_ID);
          $('#error_CHAPITRE_ID').html(errors.CHAPITRE_ID);
          $('#error_ARTICLE_ID').html(errors.ARTICLE_ID);
          $('#error_PARAGRAPHE_ID').html(errors.PARAGRAPHE_ID);
          $('#error_LITTERA_ID').html(errors.LITTERA_ID);
          $('#error_SOUS_LITTERA_ID').html(errors.SOUS_LITTERA_ID);
          $('#error_DIVISION_ID').html(errors.DIVISION_ID);
          $('#error_GROUPE_ID').html(errors.GROUPE_ID);
          $('#error_CLASSE_ID').html(errors.CLASSE_ID);
          $('#error_PROGRAMME_ID').html(errors.PROGRAMME_ID);
          $('#error_ACTION_ID').html(errors.ACTION_ID);
          $('#error_COSTAB_ACTIVITE_ID').html(errors.COSTAB_ACTIVITE_ID);
          $('#error_CODE_NOMENCLATURE_BUDGETAIRE_ID').html(errors.CODE_NOMENCLATURE_BUDGETAIRE_ID);
          $('#error_PAP_ACTIVITE_ID').html(errors.PAP_ACTIVITE_ID);
          $('#error_INDICATEUR_PND_ID').html(errors.INDICATEUR_PND_ID);
          $('#error_TACHE').html(errors.TACHE);
          $('#error_RESULTATS_ATTENDUS').html(errors.RESULTATS_ATTENDUS);
          $('#error_STRUTURE_RESPONSABLE_TACHE_ID').html(errors.STRUTURE_RESPONSABLE_TACHE_ID);
          $('#error_GRANDE_MASSE_ID').html(errors.GRANDE_MASSE_ID);
          $('#error_ANNEE_BUDGETAIRE_ID').html(errors.ANNEE_BUDGETAIRE_ID);
          $('#error_COUT_UNITAIRE_BIF').html(errors.COUT_UNITAIRE_BIF);
          $('#error_UNITE').html(errors.UNITE);
          $('#error_QT1').html(errors.QT1);
          $('#error_QT2').html(errors.QT2);
          $('#error_QT3').html(errors.QT3);
          $('#error_QT4').html(errors.QT4);
        }
      }
    });
  }
</script>

<script>
  function get_groupes()
  {
    let DIVISION_ID=$('#DIVISION_ID').val();

    $.post('<?=base_url('ihm/Taches/get_groupe')?>',
    {
      DIVISION_ID : DIVISION_ID
    },
    function(data)
    {
      $('#GROUPE_ID').html(data.groupe);
    })
  }

  function get_classes()
  {
    let GROUPE_ID=$('#GROUPE_ID').val();
    $.post('<?=base_url('ihm/Taches/get_classe')?>',
    {
      GROUPE_ID:GROUPE_ID
    },
    function(data)
    {
      $('#CLASSE_ID').html(data.classe);

    })
  }
</script>

<script>
  function get_soutut(){
    let INSTITUTION_ID = $('#INSTITUTION_ID').val();

    $.post('<?=base_url('ihm/Taches/get_sous_titre')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#SOUS_TUTEL_ID').html(data.titre);
    })
  }
</script>

<script>
  function get_article(){
    let CHAPITRE_ID = $('#CHAPITRE_ID').val();

    $.post('<?=base_url('ihm/Taches/get_article')?>',
    {
      CHAPITRE_ID:CHAPITRE_ID
    },
    function(data)
    {
      $('#ARTICLE_ID').html(data.article);
    })
  }
</script>

<script>
  function get_paragraphe(){
   let ARTICLE_ID = $('#ARTICLE_ID').val();

   $.post('<?=base_url('ihm/Taches/get_paragraphe')?>',
   {
    ARTICLE_ID:ARTICLE_ID
  },
  function(data)
  {
    $('#PARAGRAPHE_ID').html(data.paragraphe);
  })
 }
</script>

<script>
  function get_littera(){
    let PARAGRAPHE_ID = $('#PARAGRAPHE_ID').val();

    $.post('<?=base_url('ihm/Taches/get_littera')?>',
    {
      PARAGRAPHE_ID:PARAGRAPHE_ID
    },
    function(data)
    {
      $('#LITTERA_ID').html(data.littera);
    })
  }
</script>

<script>
  function get_sous_littera(){
    let LITTERA_ID = $('#LITTERA_ID').val();

    $.post('<?=base_url('ihm/Taches/get_sous_littera')?>',
    {
      LITTERA_ID:LITTERA_ID
    },
    function(data)
    {
      $('#SOUS_LITTERA_ID').html(data.sous_littera);
    })
  }
</script>

<script>
  function get_programme(){
    let INSTITUTION_ID = $('#INSTITUTION_ID').val();

    $.post('<?=base_url('ihm/Taches/get_programme')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#PROGRAMME_ID').html(data.programme);
    })
  }
</script>

<script>
  function get_action(){
    let PROGRAMME_ID=$('#PROGRAMME_ID').val();

    $.post('<?=base_url('ihm/Taches/get_action')?>',
    {
      PROGRAMME_ID:PROGRAMME_ID
    },
    function(data)
    {
      $('#ACTION_ID').html(data.action);
    })
  }
</script>

<script>
  function get_code_budgetaire(){
    let SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

    $.post('<?=base_url('ihm/Taches/get_code_budgetaire')?>',
    {
      SOUS_TUTEL_ID:SOUS_TUTEL_ID
    },
    function(data)
    {
      $('#CODE_NOMENCLATURE_BUDGETAIRE_ID').html(data.code_budgetaire);
    })
  }
</script>

<script>
  function get_pap_activite(){
    let CODE_NOMENCLATURE_BUDGETAIRE_ID=$('#CODE_NOMENCLATURE_BUDGETAIRE_ID').val();

    $.post('<?=base_url('ihm/Taches/get_pap_activite')?>',
    {
      CODE_NOMENCLATURE_BUDGETAIRE_ID:CODE_NOMENCLATURE_BUDGETAIRE_ID
    },
    function(data)
    {
      $('#PAP_ACTIVITE_ID').html(data.pap_activite);
    })
  }
</script>

<script>
  function get_institution_info(){
    let INSTITUTION_ID=$('#INSTITUTION_ID').val();

    $.post('<?=base_url('ihm/Taches/get_institution_info')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#TYPE_INSTITUTION_ID').val(data.institution_type);
      if(data.institution_type == 2){
        $('#PAP_ACTIVITE_ID_DIV').show();
      }
      else{
        $('#PAP_ACTIVITE_ID_DIV').hide();
      }
    })
  }
</script>
<script>
  function checkNumeric(ref) 
  {
    let value = parseFloat(ref.value);
    
    if(isNaN(value)){
      if(ref.name == 'COUT_UNITAIRE_BIF')
      {
        $('#error_COUT_UNITAIRE_BIF').html('<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>');
      }
      if(ref.name == 'QT1')
      {
        $('#error_QT1').html('<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>');
      }
      if(ref.name == 'QT2')
      {
        $('#error_QT2').html('<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>');
      }
      if(ref.name == 'QT3')
      {
        $('#error_QT3').html('<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>');
      }
      if(ref.name == 'QT4')
      {
        $('#error_QT4').html('<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>');
      }
    }
    else{
      if(ref.name == 'COUT_UNITAIRE_BIF')
      {
        $('#error_COUT_UNITAIRE_BIF').html('');
      }
      if(ref.name == 'QT1')
      {
        $('#error_QT1').html('');
      }
      if(ref.name == 'QT2')
      {
        $('#error_QT2').html('');
      }
      if(ref.name == 'QT3')
      {
        $('#error_QT3').html('');
      }
      if(ref.name == 'QT4')
      {
        $('#error_QT4').html('');
      }
    }
  }
  function checkSelect(ref)
  {
    const value = ref.value;
    const name = ref.name;
    const errorFieldId = '#error_'+name;

    const errorField = $(errorFieldId);

    if(value == ""){
      errorField.html('<font style="color:red;size:2px;">Le champs est obligatoire</font>')
    }
    else{
      errorField.html('')
    }
  }
</script>

<script>
  function valid_doc(input) 
  {    
    var filePath = input.value;
    var id=input.id;
    // Allowing file type
    var allowedExtensions = /(\.pdf)$/i;

    if (!allowedExtensions.exec(filePath))
    {
      $('#error_'+id).text("<?= lang('messages_lang.pdf_champ_obligatoire_transmission_du_bordereau') ?>");
      value = '';
      return false;
    }
    else 
    {
      // Check if any file is selected. 
      if (input.files.length > 0) 
      {
        $('#error_'+id).text('');
        for (const i = 0; i <= input.files.length - 1; i++) 
        {
          const fsize = input.files.item(i).size;
          const file = Math.round((fsize / 1024));
          // The size of the file. 
          if (file > 10*1024)
          {
            $('#error_'+id).text('Fichier trop volumineux, veuillez sélectionner un fichier de moins de 10Mb');
            input.value = '';
          }
          else
          {
            $('#error_'+id).text('');
          }
        }
      }
      else{
        $('#error_'+id).text('<font style="color:red;size:2px;">Le champs est obligatoire</font>');
      }
    }
  }
</script>